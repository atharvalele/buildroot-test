<?php
include(dirname(__FILE__) . "/../web/config.inc.php");
include(dirname(__FILE__) . "/../web/db.inc.php");

function import_result($buildid, $filename)
{
    global $maindir;
    $buildresultdir = $maindir . "/results/";
    $tmpbuildresultdir = $buildresultdir . "tmp/";

    echo "Importing $buildid from $filename\n";

    $finalbuildresultdir = $buildresultdir . "/" . substr($buildid, 0, 3) . "/";

    /* Check that we don't have a build result with the same SHA1 */
    if (file_exists($finalbuildresultdir . $buildid)) {
      echo "We already have a build result with the same SHA1, sorry.\n";
      return;
    }

    /* Create the temporary directory where the tarball will be
       extracted */
    $thisbuildtmpdir = $tmpbuildresultdir . $buildid . "/";
    if (! mkdir($thisbuildtmpdir)) {
      echo "Cannot create temporary directory.\n";
      return;
    }

    /* Extract the tarball into the temporary directory */
    $tarcmd = "tar -C " . $thisbuildtmpdir . " --strip-components=1 -xf " . $filename;
    system($tarcmd, $retval);
    if ($retval != 0) {
      echo "Unable to uncompress build report file\n";
      return;
    }

    /* Perform some tests */
    if (! file_exists($thisbuildtmpdir . "status") ||
	! file_exists($thisbuildtmpdir . "gitid")  ||
	! file_exists($thisbuildtmpdir . "build-end.log") ||
	! file_exists($thisbuildtmpdir . "config") ||
	! file_exists($thisbuildtmpdir . "submitter")) {
      system("rm -rf " . $thisbuildtmpdir);
      echo "Invalid contents of the build report file\n";
      return;
    }

    /* Remove the build.log.bz2 file if it's in there */
    system("rm -f " . $thisbuildtmpdir . "build.log.bz2", $retval);

    /* Create the 'results/xyz/' directory if it doesn't already
       exists */
    if (! file_exists($finalbuildresultdir)) {
      if (! mkdir($finalbuildresultdir)) {
	system("rm -rf " . $thisbuildtmpdir);
	echo "Cannot create final output directory.\n";
	return;
      }
    }

    /* Move to the final location */
    echo "mv " . $thisbuildtmpdir . " " . $finalbuildresultdir . "\n";
    system("mv " . $thisbuildtmpdir . " " . $finalbuildresultdir, $retval);
    if ($retval != 0) {
      system("rm -rf " . $thisbuildtmpdir);
      echo "Unable to move build results to the final location";
      return;
    }

    $thisbuildfinaldir = $finalbuildresultdir . "/" . $buildid . "/";

    /* Get the status */
    $status_str = trim(file_get_contents($thisbuildfinaldir . "status", "r"));
    if ($status_str == "OK")
      $status = 0;
    else if ($status_str == "NOK")
      $status = 1;
    else if ($status_str == "TIMEOUT")
      $status = 2;

    /* Get the build date (use the mtime of the status file */
    $status_stat = stat($thisbuildfinaldir . "status");
    $builddate = strftime("%Y-%m-%d %H:%M:%S", $status_stat['mtime']);

    /* Get submitter and commitid */
    $submitter  = trim(file_get_contents($thisbuildfinaldir . "submitter", "r"));
    $commitid  = trim(file_get_contents($thisbuildfinaldir . "gitid", "r"));

    /* Get the architecture from the configuration file */
    $archarray = array();
    exec("grep ^BR2_ARCH= " . $thisbuildfinaldir . "config | sed 's,BR2_ARCH=\"\(.*\)\",\\1,'", $archarray);
    $arch = $archarray[0];

    if ($status == 0)
      $reason = "none";
    else {
	$tmp = Array();
	exec("tail -3 " . $thisbuildfinaldir . "build-end.log | grep '\*\*\*' | sed 's,make: .*/\(build\|toolchain\)/\([^/]*\)/.*,\\2,'", $tmp);
	if (trim($tmp[0]))
	  $reason = $tmp[0];
	else
	  $reason = "unknown";
    }

    $db = new db();

    /* Insert into the database */
    $sql = "insert into results (status, builddate, submitter, commitid, identifier, arch, reason) values (" .
      $db->quote_smart($status) . "," .
      $db->quote_smart($builddate) . "," .
      $db->quote_smart($submitter) . "," .
      $db->quote_smart($commitid) . "," .
      $db->quote_smart($buildid) . "," .
      $db->quote_smart($arch) . "," .
      $db->quote_smart($reason) .
      ")";

    $ret = $db->query($sql);
    if ($ret == FALSE) {
      echo "Couldn't register result in DB\n";
      system("rm -rf " . $thisbuildfinaldir);
      return;
    }

    $resultdbid = $db->insertid();

    $configf = fopen($thisbuildfinaldir . "config", "r");

    $sql = "insert into results_config (resultid, isset, name, value) values\n";

    while (!feof($configf)) {
      $line = trim(fgets($configf));
      if (!strncmp($line, "BR2_", strlen("BR2_"))) {
	preg_match("/(BR2_[a-zA-Z0-9_]*)=(.*)/", $line, $matches);
	$name = $matches[1];
	$value = str_replace('"', '', $matches[2]);
	$sql .= "(" .
	  $db->quote_smart($resultdbid) . "," .
	  "1," .
	  $db->quote_smart($name) . "," .
	  $db->quote_smart($value) .
	  "),\n";
      }
      else if (!strncmp($line, "# BR2_", strlen("# BR2_"))) {
	preg_match("/# (BR2_[a-zA-Z0-9_]*) is not set/", $line, $matches);
	$name = $matches[1];
	$sql .= "(" .
	  $db->quote_smart($resultdbid) . "," .
	  "0," .
	  $db->quote_smart($name) . "," .
	  "''" .
	  "),\n";
      }
    }

    $sql[strlen($sql)-2] = ';';

    $ret = $db->query($sql);
    if ($ret == FALSE) {
      echo "Couldn't register result config line $line in DB\n";
      $db->query("delete from results where id=$resultdbid");
      $db->query("delete from results_config where resultid=$resultdbid");
      return;
    }

    fclose($configf);

    echo "Build result accepted. Thanks!";
}

?>
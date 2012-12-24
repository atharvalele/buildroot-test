<?php
include("config.inc.php");
$buildresultdir = $maindir . "/results/";

/*
 * Returns the total number of results.
 */
function bab_total_results_count()
{
  global $buildresultdir;
  exec("ls -1 ${buildresultdir} | grep -v tmp | wc -l",
       $rawresults);
  return $rawresults[0];
}

/*
 * Internal function, which, given a list of build ids (SHA1), creates
 * an array with one entry per build, containing various informations
 * about this build result.
 */
function _bab_build_result_array($buildidlist)
{
  global $buildresultdir;

  $results = array();

  for ($i = 0; $i < count($buildidlist); $i++)
    {
      /* Split up the informations */
      $buildinfo = explode(" ", $buildidlist[$i]);
      $buildid = $buildinfo[8];
      $builddate = $buildinfo[5] . " " . substr($buildinfo[6], 0, 8);
      $thisdir = $buildresultdir . $buildid;

      $status = trim(file_get_contents($thisdir . "/status"));
      $gitid = trim(file_get_contents($thisdir . "/gitid"));
      $submitter = trim(file_get_contents($thisdir . "/submitter"));

      /* Get the architecture from the configuration file */
      $archarray = array();
      exec("grep ^BR2_ARCH= " . $thisdir . "/config | sed 's,BR2_ARCH=\"\(.*\)\",\\1,'", $archarray);
      $arch = $archarray[0];

      /* Try to get the failure reason from the last line of the build
	 log, if the build failed */
      if ($status == "OK")
	$reason = "none";
      else {
	$tmp = Array();
	exec("tail -1 " . $thisdir . "/build-end.log | sed 's,make: .*/\(build\|toolchain\)/\([^/]*\)/.*,\\2,'", $tmp);
	if (trim($tmp[0]))
	  $reason = $tmp[0];
	else
	  $reason = "unknown";
      }

      $results[] = array(
			 "id" => $buildid,
			 "date" => $builddate,
			 "status" => $status,
			 "gitid" => $gitid,
			 "submitter" => $submitter,
			 "arch" => $arch,
			 "reason" => $reason,
			 );
    }

  return $results;
}

/*
 * Returns an array containing the build results starting from $start,
 * and limited to $count items. The items starting with $start=0 are
 * the most recent build results.
 */
function bab_get_results($start=0, $count=100)
{
  global $buildresultdir;

  /*
   * So, we list all files, order by mtime (-t) and show all details
   * (-l) and format the date with good format (full-iso).
   *
   * Then we filter the first line that gives the total number of
   * files (which start by total) and we filter the tmp directory
   * which is used to temporarly store build results while they are
   * being uploaded and verified.
   *
   * Finally, with tail and head we select only the ones that are of
   * interest.
   *
   * And with sed, we replace multiple spaces by single spaces so that
   * the PHP explode() function works well on this
   */

  exec("ls -lt --time-style=full-iso ${buildresultdir} | grep -v ^total | grep -v tmp | tail -n +${start} | head -${count} | sed 's/ \+/ /g'",
       $buildidlist);

  return _bab_build_result_array($buildidlist);
}

function bab_get_last_day_results()
{
  global $buildresultdir;

  exec("(cd ${buildresultdir} ; ls -dltr --time-style=full-iso \$(find . -type d -daystart -ctime 1 | sed 's,^\./,,' | tr '\\n' ' '))",
       $buildidlist);

  return _bab_build_result_array($buildidlist);
}

?>

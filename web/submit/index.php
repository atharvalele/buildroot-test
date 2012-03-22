<?php
include("../config.inc.php");

function handleUpload()
{
    global $maindir;
    $buildresultdir = $maindir . "/results/";
    $tmpbuildresultdir = $buildresultdir . "tmp/";

    /* Check the MIME type */
    $mime = mime_content_type($_FILES['uploadedfile']['tmp_name']);
    if ($mime != "application/x-gzip" && $mime != "application/x-bzip2") {
      echo "Invalid file type, rejected";
      return;
    }

    /* Compute build id */
    $buildid = sha1_file($_FILES['uploadedfile']['tmp_name']);

    /* Check that we don't have a build result with the same SHA1 */
    if (file_exists($buildresultdir . $buildid)) {
      echo "We already have a build result with the same SHA1, sorry.";
      return;
    }

    /* Create the temporary directory where the tarball will be
       extracted */
    $thisbuildtmpdir = $tmpbuildresultdir . $buildid . "/";
    if (! mkdir($thisbuildtmpdir)) {
      echo "Cannot create temporary directory.";
      return;
    }

    /* Extract the tarball into the temporary directory */
    $tarcmd = "tar -C " . $thisbuildtmpdir . " --strip-components=1 -xf " . $_FILES['uploadedfile']['tmp_name'];
    system($tarcmd, $retval);
    if ($retval != 0) {
      echo "Unable to uncompress build report file";
      return;
    }

    /* Perform some tests */
    if (! file_exists($thisbuildtmpdir . "status") ||
	! file_exists($thisbuildtmpdir . "gitid")  ||
	! file_exists($thisbuildtmpdir . "build.log.bz2") ||
	! file_exists($thisbuildtmpdir . "build-end.log") ||
	! file_exists($thisbuildtmpdir . "config") ||
	! file_exists($thisbuildtmpdir . "submitter")) {
      system("rm -rf " . $thisbuildtmpdir);
      echo "Invalid contents of the build report file";
      return;
    }

    /* Move to the final location */
    system("mv " . $thisbuildtmpdir . " " . $buildresultdir, $retval);
    if ($retval != 0) {
      echo "Unable to move build results to the final location";
      return;
    }

    echo "Build result accepted. Thanks!";
}

if ($_POST['uploadsubmit'])
  {
    handleUpload();
    exit;
  }
?>

<html>
  <head>
    <title>Submit Buildroot test</title>
  </head>
  <body>
    <h1>Submit Buildroot test</h1>
    <form action="index.php" method="POST" enctype="multipart/form-data">
      <input name="uploadedfile" type="file" /><br />
      <input type="submit" name="uploadsubmit" value="Upload File" />
    </form>
  </body>
</html>

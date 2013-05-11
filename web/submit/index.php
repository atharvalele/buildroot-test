<?php
include("../import.inc.php");

if ($_POST['uploadsubmit']) {
  /* Check the MIME type */
  $mime = mime_content_type($_FILES['uploadedfile']['tmp_name']);
  if ($mime != "application/x-gzip" && $mime != "application/x-bzip2") {
    echo "Invalid file type, rejected";
    return;
  }

  /* Compute build id */
  $buildid = sha1_file($_FILES['uploadedfile']['tmp_name']);

  import_result($buildid, $_FILES['uploadedfile']['tmp_name']);

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

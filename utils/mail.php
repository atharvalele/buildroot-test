<?php
include(dirname(__FILE__) . "/../web/funcs.inc.php");

/*
 * This script is used through a contrab to send a daily e-mail to the
 * Buildroot mailing list with the results of the builds of the last
 * day.
 */

$results = bab_get_last_day_results();

$success = 0;
$failed = 0;

while ($current = mysql_fetch_object($results)) {
  if ($current->status == 0)
    $success++;
  else
    $failed++;
}

mysql_data_seek($results, 0);

$buildsdate = strftime("%Y-%m-%d", strtotime("yesterday"));

$contents = "Hello,\n\n";
$contents .= "On " . $buildsdate . ", " . count($results) . " random build tests have been done and\nsubmitted on autobuild.buildroot.net.\n";
$contents .= " " . $success . " builds have been successful\n";
$contents .= " " . $failed  . " builds have failed\n\n";
$contents .= "Below the results of the failed builds. Successful builds are omitted.\n\n";

while ($current = mysql_fetch_object($results)) {
  if ($current->status == 0)
    continue;
  else if ($current->status == 1)
    $status = "NOK";
  else if ($current->status == 2)
    $status = "TIMEOUT";

  $contents .= "Build ". $current->identifier . "\n";
  $contents .= "==============================================\n\n";
  $contents .= "Status         : " . $status . "\n";
  $contents .= "Failure reason : " . $current->reason . "\n";
  $contents .= "Architecture   : " . $current->arch . "\n";
  $contents .= "Submitted by   : " . $current->submitter . "\n";
  $contents .= "Submitted at   : " . $current->builddate . "\n";
  $contents .= "Git commit ID  : http://git.buildroot.net/buildroot/commit/?id=" . $current->commitid . "\n";
  $contents .= "End of log     : http://autobuild.buildroot.net/results/" . $current->identifier . "/build-end.log\n";
  $contents .= "Complete log   : http://autobuild.buildroot.net/results/" . $current->identifier . "/build.log.bz2\n";
  $contents .= "Configuration  : http://autobuild.buildroot.net/results/" . $current->identifier . "/config\n";
  $contents .= "Defconfig      : http://autobuild.buildroot.net/results/" . $current->identifier . "/defconfig\n";
  $contents .= "\n";
}

$contents .= "\n\n";
$contents .= "-- \n";
$contents .= "http://autobuild.buildroot.net\n";

mail("buildroot@uclibc.org",
     "[autobuild.buildroot.net] Build results for " . $buildsdate,
     $contents,
     "From: Thomas Petazzoni <thomas.petazzoni@free-electrons.com>\r\n");

?>

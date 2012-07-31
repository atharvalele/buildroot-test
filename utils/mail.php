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

for ($i = 0; $i < count($results); $i++)
  {
    if ($results[$i]['status'] == "OK")
      $success++;
    else
      $failed++;
  }

$buildsdate = strftime("%Y-%m-%d", strtotime("yesterday"));

$contents = "Hello,\n\n";
$contents .= "On " . $buildsdate . ", " . count($results) . " random build tests have been done and\nsubmitted on autobuild.buildroot.net.\n";
$contents .= " " . $success . " builds have been successful\n";
$contents .= " " . $failed  . " builds have failed\n\n";
$contents .= "Below the results of the failed builds. Successful builds are omitted.\n\n";

for ($i = 0; $i < count($results); $i++)
  {
    $b = $results[$i];
    if ($b['status'] == "OK")
      continue;
    $contents .= "Build ". $b['id'] . "\n";
    $contents .= "==============================================\n\n";
    $contents .= "Status         : " . $b['status'] . "\n";
    $contents .= "Failure reason : " . $b['reason'] . "\n";
    $contents .= "Architecture   : " . $b['arch'] . "\n";
    $contents .= "Submitted by   : " . $b['submitter'] . "\n";
    $contents .= "Submitted at   : " . $b['date'] . "\n";
    $contents .= "Git commit ID  : http://git.buildroot.net/buildroot/commit/?id=" . $b['gitid'] . "\n";
    $contents .= "End of log     : http://autobuild.buildroot.net/results/" . $b['id'] . "/build-end.log\n";
    $contents .= "Complete log   : http://autobuild.buildroot.net/results/" . $b['id'] . "/build.log.bz2\n";
    $contents .= "Configuration  : http://autobuild.buildroot.net/results/" . $b['id'] . "/config\n";
    $contents .= "Defconfig      : http://autobuild.buildroot.net/results/" . $b['id'] . "/defconfig\n";
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

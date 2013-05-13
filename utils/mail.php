<?php
include(dirname(__FILE__) . "/../web/funcs.inc.php");

if (count($argv) != 2) {
  echo "Usage: mail.php email-address\n";
  echo "email-address can be stdout\n";
  exit;
}

$emailaddr = $argv[1];

/*
 * This script is used through a contrab to send a daily e-mail to the
 * Buildroot mailing list with the results of the builds of the last
 * day.
 */

$db = new db();

$sql = "select status,count(id) as count from results " .
  "where date(builddate) = date(now() - interval 1 day) group by status;";

$success = 0;
$failures = 0;
$timeouts = 0;
$total = 0;

$ret = $db->query($sql);
if ($ret == FALSE) {
  echo "Error while getting MySQL results\n";
  exit;
}

while ($current = mysql_fetch_object($ret)) {
  if ($current->status == 0)
    $success = $current->count;
  else if ($current->status == 1)
    $failures = $current->count;
  else if ($current->status == 2)
    $timeouts = $current->count;
  $total += $current->count;
}

$buildsdate = strftime("%Y-%m-%d", strtotime("yesterday"));

$contents = "";

$contents .= sprintf("Build statistics for %s\n", $buildsdate);
$contents .= sprintf("===============================\n\n");
$contents .= sprintf("%15s : %-3d\n", "success", $success);
$contents .= sprintf("%15s : %-3d\n", "failures", $failures);
$contents .= sprintf("%15s : %-3d\n", "timeouts", $failures);
$contents .= sprintf("%15s : %-3d\n", "TOTAL", $total);

$sql = "select reason,count(id) as reason_count from results " .
  "where date(builddate) = date(now() - interval 1 day) and " .
  "status != 0 group by reason order by reason_count desc;";

$ret = $db->query($sql);
if ($ret == FALSE) {
  echo "Error while getting MySQL results\n";
  exit;
}

$contents .= sprintf("\nClassification of failures by reason\n");
$contents .= sprintf("====================================\n\n");

while ($current = mysql_fetch_object($ret)) {
  if (strlen($current->reason) >= 30)
    $reason = substr($current->reason, 0, 27) . "...";
  else
    $reason = $current->reason;

  $contents .= sprintf("%30s | %-2d\n", $reason, $current->reason_count);
}

$sql = "select * from results " .
  "where date(builddate) = date(now() - interval 1 day) order by reason";

$ret = $db->query($sql);
if ($ret == FALSE) {
  echo "Error while getting MySQL results\n";
  exit;
}

$contents .= sprintf("\nDetail of failures\n");
$contents .= sprintf("===================\n\n");

while ($current = mysql_fetch_object($ret)) {
  if ($current->status == 0)
    continue;
  else if ($current->status == 1)
    $status = "NOK";
  else if ($current->status == 2)
    $status = "TIM";

  if (strlen($current->reason) >= 30)
    $reason = substr($current->reason, 0, 27) . "...";
  else
    $reason = $current->reason;

  $url = "http://autobuild.buildroot.net/results/" . $current->identifier . "/";

  $contents .= sprintf("%10s | %30s | %3s | %40s\n",
		       $current->arch,
		       $reason,
		       $status,
		       $url);
}

$contents .= "\n\n";
$contents .= "-- \n";
$contents .= "http://autobuild.buildroot.net\n";

if ($emailaddr == "stdout")
  echo $contents;
else
  mail($emailaddr,
       "[autobuild.buildroot.net] Build results for " . $buildsdate,
       $contents,
       "From: Thomas Petazzoni <thomas.petazzoni@free-electrons.com>\r\n");

?>

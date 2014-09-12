<?php
include(dirname(__FILE__) . "/../web/funcs.inc.php");

$longopts = array("arch:", "to:", "cc:");

$options = getopt("", $longopts);

if (array_key_exists("to", $options))
  $emailaddr = $options["to"];
else {
  echo "Usage: mail.php --to email-address [--arch ARCH] [--cc CC]\n";
  echo "email-address can be stdout\n";
  exit;
}

/*
 * This script is used through a contrab to send a daily e-mail to the
 * Buildroot mailing list with the results of the builds of the last
 * day.
 */

$db = new db();

if (array_key_exists("arch", $options))
  $condition = "and arch=" . $db->quote_smart($options["arch"]);
else
  $condition = "";

$sql = "select status,count(id) as count from results " .
  "where date(builddate) = date(now() - interval 1 day) " .
  $condition .
  "group by status;";

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

/* Only send arch-specific reports when there are failures to
   report */
if (array_key_exists("arch", $options) && $failures == 0)
  exit;

$buildsdate = strftime("%Y-%m-%d", strtotime("yesterday"));

$contents = "";

if (array_key_exists("arch", $options))
  $contents .= "Those results are limited to the " . $options["arch"] . " architecture.\n\n";

$contents .= sprintf("Build statistics for %s\n", $buildsdate);
$contents .= sprintf("===============================\n\n");
$contents .= sprintf("%15s : %-3d\n", "success", $success);
$contents .= sprintf("%15s : %-3d\n", "failures", $failures);
$contents .= sprintf("%15s : %-3d\n", "timeouts", $timeouts);
$contents .= sprintf("%15s : %-3d\n", "TOTAL", $total);

$sql = "select reason,count(id) as reason_count from results " .
  "where date(builddate) = date(now() - interval 1 day) and status != 0 " .
  $condition .
  "group by reason order by reason_count desc;";

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
  "where date(builddate) = date(now() - interval 1 day) " .
  $condition .
  "order by reason";

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

  $contents .= sprintf("%12s | %30s | %3s | %40s\n",
		       $current->arch,
		       $reason,
		       $status,
		       $url);
}

$contents .= "\n\n";
$contents .= "-- \n";
$contents .= "http://autobuild.buildroot.net\n";

$headers = "From: Thomas Petazzoni <thomas.petazzoni@free-electrons.com>\r\n";
if (array_key_exists("cc", $options))
  $headers .= "Cc: " . $options["cc"] . "\r\n";

if (array_key_exists("arch", $options))
  $title = "[autobuild.buildroot.net] " . $options["arch"] . " build results for " . $buildsdate;
else
  $title = "[autobuild.buildroot.net] Build results for " . $buildsdate;

if ($emailaddr == "stdout") {
  echo $headers;
  echo "\n\n";
  echo $title;
  echo "\n\n";
  echo $contents;
}
else
  mail($emailaddr, $title, $contents, $headers);

?>

#!/usr/bin/env php
<?php
/*
 * Remove one build failure from the database
 */

include(dirname(__FILE__) . "/../web/funcs.inc.php");

if (count($argv) != 2) {
  echo "Wrong number of arguments\n";
  exit(1);
}

$hash = $argv[1];

$db = new db();

echo "Getting id from results...";

$sql = "select id from results where identifier='" . $hash . "'";
$ret = $db->query($sql);
if ($ret == FALSE) {
  echo "FAILED\n";
  exit(1);
}
if (mysqli_num_rows($ret) != 1) {
  echo "NOT FOUND\n";
  exit(1);
}

$c = mysqli_fetch_object($ret);
$id = $c->id;

echo " $id\n";

$path = $maindir . "/results/" . substr($hash, 0, 3) . "/" . $hash;

if (!is_writable($path)) {
  echo "ERROR: you don't have write permission on the result directory\n";
  exit(1);
}

echo "Removing from results_config...";

$sql = "delete from symbol_per_result where result_id=" . $id;
$ret = $db->query($sql);
if ($ret == FALSE) {
  echo "FAILED\n";
  exit(1);
}
echo "DONE\n";

echo "Removing from results...";

$sql = "delete from results where id=" . $id;
$ret = $db->query($sql);
if ($ret == FALSE) {
  echo "FAILED\n";
  exit(1);
}
echo "DONE\n";

echo "Removing build results...";
system("rm -rf " . $path);
echo "DONE\n";

?>

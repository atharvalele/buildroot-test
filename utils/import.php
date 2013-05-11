<?php
include(dirname(__FILE__) . "/../web/import.inc.php");

if (count($argv) != 3) {
  echo "Usage: import.php build-id build-result.tar.bz2\n";
  exit;
}

$buildid = $argv[1];
$buildtarball = $argv[2];

import_result($buildid, $buildtarball);
?>

<?php
include("funcs.inc.php");

/* When no start is given, or start is a crazy value (not an integer),
   just default to start=0 */
if (! isset($_GET['start']) || ! ereg("^[0-9]*$", $_GET['start']))
  $start = 0;
else
  $start = $_GET['start'];

if (! isset($_GET['step']) || ! ereg("^[0-9]*$", $_GET['step']))
  $step = 50;
else
  $step = $_GET['step'];

if ($step > 250)
  $step = 250;

if ($_GET['status'] == 'OK')
  $filter_status = 0;
else if ($_GET['status'] == 'NOK')
  $filter_status = 1;
else if ($_GET['status'] == 'TIMEOUT')
  $filter_status = 2;
else
  $filter_status = -1;

if (isset($_GET['arch']) && ereg("^[a-z0-9_]*$", $_GET['arch']))
  $filter_arch = $_GET['arch'];
else
  $filter_arch = "";

if (isset($_GET['reason']) && ereg("^[A-Za-z0-9_\.\-]*$", $_GET['reason']))
  $filter_reason = $_GET['reason'];
else
  $filter_reason = "";

if (isset ($_GET['submitter']))
  $filter_submitter = urldecode($_GET['submitter']);
else
  $filter_submitter = "";

bab_header("Buildroot tests");

echo "<table>\n";

echo "<tr class=\"header\">";
echo "<td>Date</td><td>Status</td><td>Commit ID</td><td>Submitter</td><td>Arch</td><td>Failure reason</td><td>Data</td>";
echo "</tr>";

$results = bab_get_results($start, $step, $filter_status, $filter_arch, $filter_reason, $filter_submitter);

while ($current = mysql_fetch_object($results)) {

  /* Beautify a bit the name of the host that has been used for the build */
  $submitter = preg_replace("/(\w+) (\([^)]*\))/", "$1<br/><span style=\"font-size: 80%;\"><i>$2</i></font>", $current->submitter);

  if ($current->status == 0)
    echo "<tr class=\"ok\">\n";
  else if ($current->status == 1)
    echo "<tr class=\"nok\">\n";
  else if ($current->status == 2)
    echo "<tr class=\"timeout\">\n";

  echo "<td>" . $current->builddate . "</td>";

  if ($current->status == 0)
    echo "<td><a href=\"?status=OK\">OK</a></td>";
  else if ($current->status == 1)
    echo "<td><a href=\"?status=NOK\">NOK</a></td>";
  else if ($current->status == 2)
    echo "<td><a href=\"?status=TIMEOUT\">TIMEOUT</a></td>";

  echo "<td><a href=\"http://git.buildroot.net/buildroot/commit/?id=" . $current->commitid . "\">" . substr($current->commitid, 0, 8) . "</a></td>";
  echo "<td><a href=\"?submitter=" . urlencode($current->submitter) . "\">" . $submitter . "</a></td>";
  echo "<td><a href=\"?arch=" . $current->arch . "\">" . $current->arch . "</a></td>";
  if ($current->reason == "none")
    echo "<td>none</td>";
  else
    echo "<td><a href=\"?reason=" . $current->reason . "\">" . $current->reason . "</td>";

  echo "<td>";
  echo "<a href=\"" . bab_get_path($current->identifier) . "/\">dir</a>, ";
  echo "<a href=\"" . bab_get_path($current->identifier, "build-end.log") . "\">end log</a>, ";
  echo "<a href=\"" . bab_get_path($current->identifier, "config") . "\">config</a>";
  if (file_exists(bab_get_path($current->identifier, "defconfig")))
    echo ", <a href=\"" . bab_get_path($current->identifier, "defconfig") . "\">defconfig</a>";
  echo "</td>";

  echo "</tr>\n";
}

echo "</table>\n";

echo "<p style=\"text-align: center;\">";

$total = bab_total_results_count();

if ($start != 0)
  echo "<a href=\"?start=" . ($start - $step) . "\">Previous results</a>&nbsp;-&nbsp;";

echo "(" . $start . " - " . ($start + $step) . " / " . $total . " results)&nbsp;-&nbsp;";

if (($start + $step) < $total)
  echo "<a href=\"?start=" . ($start + $step) . "\">Next results</a>";

echo "</p>";

bab_footer();
?>

<?php
include("funcs.inc.php");

function format_duration($seconds_count)
{
        $delimiter  = ':';
        $seconds = $seconds_count % 60;
        $minutes = floor($seconds_count/60) % 60;
        $hours   = floor($seconds_count/3600);

        $seconds = str_pad($seconds, 2, "0", STR_PAD_LEFT);
        $minutes = str_pad($minutes, 2, "0", STR_PAD_LEFT).$delimiter;

        if($hours > 0)
        {
		$hours = str_pad($hours, 2, "0", STR_PAD_LEFT).$delimiter;
        }
        else
        {
		$hours = '';
        }

        return "$hours$minutes$seconds";
}

$filters = array();

/* When no start is given, or start is a crazy value (not an integer),
   just default to start=0 */
if (! isset($_GET['start']) || ! preg_match("/^[0-9]*$/", $_GET['start']))
  $start = 0;
else
  $start = $_GET['start'];

if (! isset($_GET['step']) || ! preg_match("/^[0-9]*$/", $_GET['step']))
  $step = 50;
else
  $step = $_GET['step'];

if ($step > 250)
  $step = 250;

$valid_status = array("OK", "NOK", "TIMEOUT");

if (isset ($_GET['status']) && in_array($_GET['status'], $valid_status))
  $filters["status"] = $_GET['status'];

if (isset($_GET['arch']) && preg_match("/^[a-z0-9_]*$/", $_GET['arch']))
  $filters["arch"] = $_GET['arch'];

if (isset($_GET['branch']) && preg_match("/^[a-z0-9_\.]*$/", $_GET['branch']))
  $filters["branch"] = $_GET['branch'];

if (isset($_GET['reason']) && preg_match("/^[A-Za-z0-9_%\+\.\-]*$/", $_GET['reason']))
  $filters["reason"] = $_GET['reason'];

if (isset($_GET['libc']) && preg_match("/^[a-z]*$/", $_GET['libc']))
  $filters["libc"] = $_GET['libc'];

if (isset($_GET['static']) && preg_match("/^[0-1]$/", $_GET['static']))
  $filters["static"] = $_GET['static'];

if (isset($_GET['subarch']) && preg_match("/^[A-Za-z0-9_\+\.\-]*$/", $_GET['subarch']))
  $filters["subarch"] = $_GET['subarch'];

if (isset ($_GET['submitter']))
  $filters["submitter"] = urldecode($_GET['submitter']);

bab_header("Buildroot tests");

echo "<table>\n";

echo "<tr class=\"header\">";
echo "<td>Date</td><td>Duration</td><td>Status</td><td>Commit ID</td><td>Submitter</td><td>Arch/Subarch</td><td>Failure reason</td><td>Libc</td><td>Static?</td><td>Data</td>";
echo "</tr>";

$results = bab_get_results($start, $step, $filters);

while ($current = mysqli_fetch_object($results)) {

  /* Beautify a bit the name of the host that has been used for the build */
  $submitter = preg_replace("/(\w+) (\([^)]*\))/", "$1<br/><span style=\"font-size: 80%;\"><i>$2</i></font>", $current->submitter);

  if ($current->status == 0)
    echo "<tr class=\"ok\">\n";
  else if ($current->status == 1)
    echo "<tr class=\"nok\">\n";
  else if ($current->status == 2)
    echo "<tr class=\"timeout\">\n";

  echo "<td>" . $current->builddate . "</td>";

  if ($current->duration)
	  echo "<td>" . format_duration($current->duration) . "</td>";
  else
	  echo "<td>N/A</td>";

  if ($current->status == 0)
    echo "<td><a href=\"?status=OK\">OK</a></td>";
  else if ($current->status == 1)
    echo "<td><a href=\"?status=NOK\">NOK</a></td>";
  else if ($current->status == 2)
    echo "<td><a href=\"?status=TIMEOUT\">TIMEOUT</a></td>";

  echo "<td style=\"font-size: 80%\"><a href=\"?branch=" . urlencode($current->branch) . "\">" . $current->branch . "</a><br/><a href=\"http://git.buildroot.net/buildroot/commit/?id=" . $current->commitid . "\">" . substr($current->commitid, 0, 8) . "</a></td>";
  echo "<td><a href=\"?submitter=" . urlencode($current->submitter) . "\">" . $submitter . "</a></td>";
  echo "<td><a href=\"?arch=" . $current->arch . "\">" . $current->arch . "</a>";
  if ($current->subarch != "")
    echo " / <a href=\"?subarch=" . $current->subarch . "\">" . $current->subarch . "</a>";
  echo "</td>";
  if ($current->reason == "none")
    echo "<td>none</td>";
  else {
     $display_reason = $current->reason;
     if (strlen($display_reason) > 30) {
         $display_reason = substr($display_reason, 0, 27) . "...";
     }
     echo "<td><a href=\"?reason=" . urlencode($current->reason) . "\">" . $display_reason . "</td>";
  }

  echo "<td><a href=\"?libc=" . $current->libc . "\">" . $current->libc . "</a></td>";

  if (is_null($current->static)) {
	  $display_static = "";
  } else if ($current->static == 0) {
	  $display_static = "N";
  } else if ($current->static == 1) {
	  $display_static = "Y";
  }

  echo "<td><a href=\"?static=" . $current->static . "\">" . $display_static . "</a></td>";

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

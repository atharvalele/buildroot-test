<?php
include("funcs.inc.php");

if (isset($_GET['branch']) && preg_match("/^[a-z0-9_\.]*$/", $_GET['branch']))
  $branch = $_GET['branch'];
else
  $branch = "master";

bab_header("Buildroot '$branch' tests statistics");

echo "<h2>Branches: ";

$csv = fopen("branches", "r");
if ($csv !== FALSE) {
    $first = true;
    while (($data = fgetcsv($csv, 128, ",")) !== FALSE) {
      if (!$first) {
         echo "&nbsp;|&nbsp;";
      }
      if ($data[0] == $branch) {
         echo "<i><a href=\"?branch=$data[0]\">$data[0]</a></i>";
      } else {
         echo "<a href=\"?branch=$data[0]\">$data[0]</a>";
      }
      $first = false;
    }
} else {
  echo "master";
}

echo "</h2>\n";

$db = new db();

$sql = "select sum(status=0) as success,sum(status=1) as failures,sum(status=2) as timeouts,count(*) as total,date(builddate) as day from results where branch='$branch' group by date(builddate) order by date(builddate) desc limit 30;";

$ret = $db->query($sql);
if ($ret == FALSE) {
  echo "Cannot retrieve statistics<br/>";
  bab_footer();
  exit;
}

echo "<p style=\"text-align: center;\">Results of the last 30 days</p>";
echo "<table style=\"width: 50%; border: 2px solid black;\">\n";
echo "<tr style=\"border-bottom: 1px solid black;\">";
echo "  <td>Date</td>\n";
echo "  <td colspan=\"2\">Success</td>\n";
echo "  <td colspan=\"2\">Failure</td>\n";
echo "  <td colspan=\"2\">Timeouts</td>\n";
echo "  <td>Total</td>\n";
echo "</tr>\n";
$successtotal = 0;
$failuretotal = 0;
$timeouttotal = 0;
$total        = 0;
while ($current = mysqli_fetch_object($ret)) {
  $successtotal += $current->success;
  $failuretotal += $current->failures;
  $timeouttotal += $current->timeouts;
  $total        += $current->total;
  $successrate = sprintf("%2.2f", $current->success / $current->total * 100);
  $failurerate = sprintf("%2.2f", $current->failures / $current->total * 100);
  $timeoutrate = sprintf("%2.2f", $current->timeouts / $current->total * 100);
  echo " <tr>\n";
  echo "  <td>$current->day</td>\n";
  echo "  <td>$current->success</td>\n";
  echo "  <td>$successrate%</td>\n";
  echo "  <td>$current->failures</td>\n";
  echo "  <td>$failurerate%</td>\n";
  echo "  <td>$current->timeouts</td>\n";
  echo "  <td>$timeoutrate%</td>\n";
  echo "  <td>$current->total</td>\n";
  echo " </tr>\n";
}

$successrate = sprintf("%2.2f", $successtotal / $total * 100);
$failurerate = sprintf("%2.2f", $failuretotal / $total * 100);
$timeoutrate = sprintf("%2.2f", $timeouttotal / $total * 100);

echo " <tr style=\"border-top: 1px solid black;\">\n";
echo "  <td>Total last 30 days</td>\n";
echo "  <td>$successtotal</td>\n";
echo "  <td>$successrate%</td>\n";
echo "  <td>$failuretotal</td>\n";
echo "  <td>$failurerate%</td>\n";
echo "  <td>$timeouttotal</td>\n";
echo "  <td>$timeoutrate%</td>\n";
echo "  <td>$total</td>\n";
echo " </tr>\n";

$sql = "select sum(status=0) as success,sum(status=1) as failures,sum(status=2) as timeouts,count(*) as total from results where branch='$branch';";

$ret = $db->query($sql);
if ($ret == FALSE) {
  echo "Cannot retrieve statistics<br/>";
  bab_footer();
  exit;
}

$result = mysqli_fetch_object($ret);

$successrate = sprintf("%2.2f", $result->success / $result->total * 100);
$failurerate = sprintf("%2.2f", $result->failures / $result->total * 100);
$timeoutrate = sprintf("%2.2f", $result->timeouts / $result->total * 100);

echo " <tr style=\"border-top: 1px solid black;\">\n";
echo "  <td>Total in history</td>\n";
echo "  <td>$result->success</td>\n";
echo "  <td>$successrate%</td>\n";
echo "  <td>$result->failures</td>\n";
echo "  <td>$failurerate%</td>\n";
echo "  <td>$result->timeouts</td>\n";
echo "  <td>$timeoutrate%</td>\n";
echo "  <td>$result->total</td>\n";
echo " </tr>\n";

echo "</table>\n";
echo "<p></p>";

echo "<center><img src=\"graph.php?branch=$branch\"/></center>";

bab_footer();
?>

<html>
  <head>
    <title>Buildroot build tests</title>
    <link rel="stylesheet" type="text/css" href="stylesheet.css"/>
   <link rel="alternate" href="rss.php" title="Autobuild Buildroot results" type="application/rss+xml" />
  </head>
  <body>
    <h1>Buildroot build tests</h1>

<?php
   include("funcs.inc.php");

echo $_SERVER['REQUEST_URI'] . "<br/>";

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

echo "<table>\n";

echo "<tr class=\"header\">";
echo "<td>Date</td><td>Status</td><td>Commit ID</td><td>Submitter</td><td>Arch</td><td>Failure reason</td><td>Data</td>";
echo "</tr>";

$results = bab_get_results($start, $step, $filter_status);

while ($current = mysql_fetch_object($results)) {

  /* Beautify a bit the name of the host that has been used for the build */
  $submitter = preg_replace("/(\w+) (\([^)]*\))/", "$1<br/><span style=\"font-size: 80%;\"><i>$2</i></font>", $current->submitter);

  if ($current->status == 0)
    echo "<tr class=\"ok\">\n";
  else
    echo "<tr class=\"nok\">\n";

  echo "<td>" . $current->builddate . "</td>";

  if ($current->status == 0)
    echo "<td><a href=\"?status=OK\">OK</a></td>";
  else if ($current->status == 1)
    echo "<td><a href=\"?status=NOK\">NOK</a></td>";
  else if ($current->status == 2)
    echo "<td><a href=\"?status=TIMEOUT\">TIMEOUT</a></td>";

  echo "<td><a href=\"http://git.buildroot.net/buildroot/commit/?id=" . $current->commitid . "\">" . substr($current->commitid, 0, 8) . "</a></td>";
  echo "<td>" . $submitter . "</td>";
  echo "<td>" . $current->arch . "</td>";
  echo "<td>" . $current->reason . "</td>";

  echo "<td>";
  echo "<a href=\"results/" . $current->identifier . "/\">dir</a>, ";
  echo "<a href=\"results/" . $current->identifier . "/build-end.log\">end log</a>, ";
  echo "<a href=\"results/" . $current->identifier . "/build.log.bz2\">full log</a>, ";
  echo "<a href=\"results/" . $current->identifier . "/config\">config</a>";
  if (file_exists("results/" . $current->identifier . "/defconfig"))
    echo ", <a href=\"results/" . $current->identifier . "/defconfig\">defconfig</a>";
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

?>

<p style="width: 90%; margin: auto; text-align: center; font-size: 60%; border-top: 1px solid black; padding-top: 5px;">
  <a href="http://buildroot.org">About Buildroot</a>&nbsp;-&nbsp;<a href="rss.php">RSS feed of build results</a>&nbsp;-&nbsp;<a href="http://git.buildroot.net/buildroot-test/plain/utils/br-reproduce-build">Script to reproduce a build</a>
</p>

  </body>
</html>

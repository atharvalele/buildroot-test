<?php
include(dirname(__FILE__) . "/../web/config.inc.php");
include(dirname(__FILE__) . "/../web/db.inc.php");

$buildresultdir = $maindir . "/results/";

function bab_header($title)
{
  echo "<html>\n";
  echo "<head>\n";
  echo "  <title>$title</title>\n";
  echo "  <link rel=\"stylesheet\" type=\"text/css\" href=\"stylesheet.css\"/>\n";
  echo "  <link rel=\"alternate\" href=\"rss.php\" title=\"Autobuild Buildroot results\" type=\"application/rss+xml\" />\n";
  echo "</head>\n";
  echo "<body>\n";
  echo " <h1>$title</h1>\n";
}

function bab_footer()
{
  echo "<p style=\"width: 90%; margin: auto; text-align: center; font-size: 60%; border-top: 1px solid black; padding-top: 5px;\">\n";
  echo "<a href=\"http://buildroot.org\">About Buildroot</a>&nbsp;-&nbsp;";
  echo "<a href=\"rss.php\">RSS feed of build results</a>&nbsp;-&nbsp;";
  echo "<a href=\"stats.php\">build stats</a>&nbsp;-&nbsp;";
  echo "<a href=\"stats/\">package stats</a>&nbsp;-&nbsp;";
  echo "<a href=\"toolchains/\">toolchain configs</a>&nbsp;-&nbsp;";
  echo "<a href=\"http://git.buildroot.net/buildroot-test/plain/utils/br-reproduce-build\">Script to reproduce a build</a>\n";
  echo "</p>\n";
  echo "</body>\n";
  echo "</html>\n";
}

function bab_format_sql_filter($db, $filters)
{
	$status_map = array(
		"OK" => 0,
		"NOK" => 1,
		"TIMEOUT" => 2,
	);

	$sql_filters = implode(' and ', array_map(
		function ($v, $k) use ($db, $status_map) {
			if ($k == "reason")
				return sprintf("%s like %s", $k, $db->quote_smart($v));
			else if ($k == "status")
				return sprintf("%s=%s", $k, $db->quote_smart($status_map[$v]));
			else
				return sprintf("%s=%s", $k, $db->quote_smart($v));
		},
		$filters,
		array_keys($filters)
	));

	if (count($filters))
		return "where " . $sql_filters;
	else
		return "";
}

/*
 * Returns the total number of results.
 */
function bab_total_results_count()
{
  $db = new db();
  $sql = "select count(*) from results;";
  $ret = $db->query($sql);
  if ($ret == FALSE) {
    echo "Something's wrong in here\n";
    return;
  }

  $ret = mysqli_fetch_array($ret);
  return $ret[0];
}

/*
 * Returns an array containing the build results starting from $start,
 * and limited to $count items. The items starting with $start=0 are
 * the most recent build results.
 */
function bab_get_results($start=0, $count=100, $filters = array())
{
  global $status_map;
  $db = new db();

  $condition = bab_format_sql_filter($db, $filters);
  $sql = "select * from results $condition order by builddate desc limit $start, $count;";
  $ret = $db->query($sql);
  if ($ret == FALSE) {
    echo "Something's wrong with the SQL query\n";
    return;
  }

  return $ret;
}

function bab_get_path($identifier, $file="") {
  return "results/" . substr($identifier, 0, 3) . "/" . $identifier . "/" . $file;
}

?>

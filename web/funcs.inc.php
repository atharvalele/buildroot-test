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

  $ret = mysql_fetch_array($ret);
  return $ret[0];
}

/*
 * Returns an array containing the build results starting from $start,
 * and limited to $count items. The items starting with $start=0 are
 * the most recent build results.
 */
function bab_get_results($start=0, $count=100, $filter_status=-1, $filter_arch="", $filter_reason="", $filter_submitter="", $filter_libc="", $filter_static="", $filter_subarch="", $filter_branch="")
{
  $db = new db();
  $where_parts = array();
  if ($filter_status != -1)
    $where_parts[] = " status=" . $db->quote_smart($filter_status) . " ";
  if ($filter_arch != "")
    $where_parts[] = " arch=" . $db->quote_smart($filter_arch) . " ";
  if ($filter_reason != '')
    $where_parts[] = " reason=" . $db->quote_smart($filter_reason) . " ";
  if ($filter_submitter != '')
    $where_parts[] = " submitter=" . $db->quote_smart($filter_submitter) . " ";
  if ($filter_libc != '')
    $where_parts[] = " libc=" . $db->quote_smart($filter_libc) . " ";
  if ($filter_static != '')
    $where_parts[] = " static=" . $db->quote_smart($filter_static) . " ";
  if ($filter_subarch != '')
    $where_parts[] = " subarch=" . $db->quote_smart($filter_subarch) . " ";
  if ($filter_branch != '')
    $where_parts[] = " branch=" . $db->quote_smart($filter_branch) . " ";
  if (count($where_parts)) {
    $condition = "where " . implode("and", $where_parts);
  } else {
    $condition = "";
  }
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

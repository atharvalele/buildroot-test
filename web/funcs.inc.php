<?php
include(dirname(__FILE__) . "/../web/config.inc.php");
include(dirname(__FILE__) . "/../web/db.inc.php");

$buildresultdir = $maindir . "/results/";

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
function bab_get_results($start=0, $count=100, $filter_status=-1)
{
  $db = new db();
  if ($filter_status != -1)
    $condition = "where status=" . $db->quote_smart($filter_status) . " ";
  $sql = "select * from results $condition order by builddate desc limit $start, $count;";
  $ret = $db->query($sql);
  if ($ret == FALSE) {
    echo "Something's wrong with the SQL query\n";
    return;
  }

  return $ret;
}

/*
 * Returns the results of the last day
 */
function bab_get_last_day_results()
{
  $db = new db();
  $sql = "select * from results where date(builddate) = date(now() - interval 1 day) order by builddate";

  $ret = $db->query($sql);
  if ($ret == FALSE) {
    echo "Something's wrong with the SQL query\n";
    return;
  }

  return $ret;
}

?>

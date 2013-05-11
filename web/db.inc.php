<?php
include("config.inc.php");

class db
{
  function db()
  {
    global $db_host;
    global $db_user;
    global $db_pass;
    global $db_name;

    if(mysql_connect($db_host,$db_user,$db_pass)==FALSE)
      {
	echo "Issue connecting to DB on host $db_host.\n";
	return 0;
      }

    if(mysql_select_db($db_name) == FALSE)
      {
	echo "Issue connecting to DB $db_name on host $db_host.\n";
	return 0;
      }

    mysql_query("set names 'utf8'");
  }

  function query ($query)
  {
    if( ($result = mysql_query($query)) == FALSE)
      {
	echo "Syntax problem in '$query' : " . mysql_error() . "\n";
	return 0;
      }

    return $result;
  }

  function insertid ()
  {
    return mysql_insert_id ();
  }

  /**
   * Converts the argument of an SQL request in a format accepted by MySQL.
   *
   * @param[in] value String or integer to use as argument
   *
   * @return The string to use in the request
   */
  function quote_smart($value)
  {
    if (get_magic_quotes_gpc())
      $value = stripslashes($value);

    if (!is_numeric($value))
      $value = "'" . mysql_real_escape_string($value) . "'";

    return $value;
  }
}

?>
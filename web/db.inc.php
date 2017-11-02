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

    $this->conn = mysqli_connect($db_host,$db_user,$db_pass,$db_name);
    if (!$this->conn)
      {
	echo "Issue connecting to DB on host $db_host.\n";
	return 0;
      }

    $this->conn->query("set names 'utf8'");
  }

  function query ($query)
  {
    $result = $this->conn->query($query);
    if (!$result)
      {
	echo "Syntax problem in '$query'\n";
	return 0;
      }

    return $result;
  }

  function insertid ()
  {
    return $this->conn->insert_id;
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
      $value = "'" . $this->conn->real_escape_string($value) . "'";

    return $value;
  }
}

?>
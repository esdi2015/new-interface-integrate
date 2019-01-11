<?php
/*
UserCake Version: 2.0.2
http://usercake.com
*/

//Database Information
$db_host = "localhost"; //Host address (most likely localhost)
$db_name = "integrate_db"; //Name of Database
$db_user = "integrate"; //Name of database user
$db_pass = "%leads2019"; //Password for database user
$db_table_prefix = "uc_";

GLOBAL $errors;
GLOBAL $successes;

$errors = array();
$successes = array();

/* Create a new mysqli object with database connection parameters */
$mysqli = new mysqli($db_host, $db_user, $db_pass, $db_name);
$mysqli2 = new mysqli($db_host, $db_user, $db_pass, $db_name);
$mysqli3 = new mysqli($db_host, $db_user, $db_pass, $db_name);
GLOBAL $mysqli;
GLOBAL $mysqli2;
GLOBAL $mysqli3;

if(mysqli_connect_errno()) {
	echo "Connection Failed: " . mysqli_connect_errno();
	exit();
}

//Direct to install directory, if it exists
if(is_dir("install/"))
{
	header("Location: install/");
	die();

}

?>
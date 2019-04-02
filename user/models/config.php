<?php
/*
UserCake Version: 2.0.2
http://usercake.com
*/
define('SITE_PATH', $_SERVER['DOCUMENT_ROOT']);
define('UC_PATH', $_SERVER['DOCUMENT_ROOT'] . "/user/models/");

require_once(UC_PATH . 'db-settings.php'); //Require DB connection

//Retrieve settings
$stmt = $mysqli->prepare("SELECT id, name, value
	FROM ".$db_table_prefix."configuration");	
$stmt->execute();
$stmt->bind_result($id, $name, $value);

while ($stmt->fetch()){
	$settings[$name] = array('id' => $id, 'name' => $name, 'value' => $value);
}
$stmt->close();

//Set Settings
$emailActivation = $settings['activation']['value'];
$mail_templates_dir = "models/mail-templates/";
$websiteName = $settings['website_name']['value'];
$websiteUrl = $settings['website_url']['value'];
$emailAddress = $settings['email']['value'];
$resend_activation_threshold = $settings['resend_activation_threshold']['value'];
$emailDate = date('dmy');
$language = $settings['language']['value'];
$template = $settings['template']['value'];

$master_account = -1;

$default_hooks = array("#WEBSITENAME#","#WEBSITEURL#","#DATE#");
$default_replace = array($websiteName,$websiteUrl,$emailDate);

if (!file_exists($language)) {
	$language = UC_PATH . "languages/en.php";
}

if(!isset($language)) $language = UC_PATH . "languages/en.php";

//Pages to require
require_once($language);
require_once(UC_PATH . "class.mail.php");
require_once(UC_PATH . "class.user.php");
require_once(UC_PATH . "class.newuser.php");
require_once(UC_PATH . "funcs.php");

session_start();

//Global User Object Var
//loggedInUser can be used globally if constructed
$IS_SUPER_ADMIN = false;
$IS_ADMIN = false;
$IS_CAMPAIGN_MANAGER = false;

$CAMPAIGN_STATUSES = array('active', 'disabled');

if(isset($_SESSION["userCakeUser"]) && is_object($_SESSION["userCakeUser"]))
{
	$loggedInUser = $_SESSION["userCakeUser"];

    $IS_SUPER_ADMIN = $loggedInUser->checkPermission(array(2));
    $IS_ADMIN = $loggedInUser->checkPermission(array(3));
    $IS_CAMPAIGN_MANAGER = $loggedInUser->checkPermission(array(1));
}

?>
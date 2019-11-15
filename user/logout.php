<?php
/*
UserCake Version: 2.0.2
http://usercake.com
*/

require_once("models/config.php");
if (!securePage($_SERVER['PHP_SELF'])){die();}

//Log the user out
if(isUserLoggedIn())
{
	$loggedInUser->userLogOut();
}

//var_dump($_SERVER['HTTP_REFERER'], $websiteUrl); die();

if (strpos($_SERVER['HTTP_REFERER'], 'https://') === false) {
    $http_s = 'http://';
} else {
    $http_s = 'https://';
}

if(!empty($websiteUrl)) 
{
	$add_http = "";
    //$http_s = 'http'.(empty($_SERVER['HTTPS'])?'':'s').'://';
    //$http_s = 'https://';
	if(strpos($websiteUrl, $http_s) === false)
	{
		$add_http = $http_s;
	}
//	var_dump($http_s, $websiteUrl, $_SERVER['HTTP_HOST'], strpos($websiteUrl, $http_s),
//        $_SERVER['HTTPS'], empty($_SERVER['HTTPS'])); die();
	header("Location: ".$add_http.$websiteUrl);
	die();
}
else
{
    //$http_s = 'http'.(empty($_SERVER['HTTPS'])?'':'s').'://';
    //$http_s = 'http://';
	header("Location: ".$http_s.$_SERVER['HTTP_HOST']);
	die();
}	

?>


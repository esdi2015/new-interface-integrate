<?php
require_once ('user/models/db-settings.php');
require_once ('config.php');
require_once ('user/models/funcs.php');

$rows = array();
$content = "";
$count = 0;
$query = "SELECT campaignid, email FROM csv_accepted_leads";
$from;
$to;

if (isset($_GET["from"])) 
	$from = $_GET["from"];
if (isset($_GET["to"])) 
	$to = $_GET["to"];

if (!empty($from)){
	$from = date('Y-m-d', strtotime(str_replace('-', '/', $from)));
	$query.= " WHERE uploadedat BETWEEN '" . $from;
}

if (!empty($from) && !empty($to)) {
	$to = date('Y-m-d', strtotime(str_replace('-', '/', $to)));
	$query.= "' AND '" . $to . " 23:59:59.999'";
}
else if (!empty($from)) {
	$query.= "' AND '" . date("Y-m-d") . " 23:59:59.999'";
}

$result = $mysqli->prepare($query);
$result->execute();
$result->store_result();
$count = $result->num_rows;
$result->close();
$result = $mysqli->prepare($query);
$result->execute();
$result->bind_result($campaignid, $email);

// Add CSV Header
$content .= "cid,email\n";

while ($result->fetch())
{
	//$user = fetchUserDetailsNew($userid);
	//$rows[] = ["cid" => $campaignid, "email" => $email];
	// Add CSV row
	$content .= $campaignid . "," . $email . "\n";
}

$result->close();

// Download file
header('Content-Description: File Transfer');
header('Content-Type: application/csv');
header('Content-Disposition: attachment; filename=AcceptedLeads.' . date("Y_m_d_H_i_s") . '.csv');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . strlen($content));
ob_clean();
flush();
echo $content;

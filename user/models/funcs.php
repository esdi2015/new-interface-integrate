<?php
/*
UserCake Version: 2.0.2
http://usercake.com
*/

//Functions that do not interact with DB
//------------------------------------------------------------------------------
function getSiteURL() {
    //$siteURL='http'.(empty($_SERVER['HTTPS'])?'':'s').'://'.$_SERVER['HTTP_HOST'].'/';

    if (strpos($_SERVER['HTTP_HOST'], 'integrate.infusemedia.com') !== false) {
        $siteURL='https://'.$_SERVER['HTTP_HOST'].'/';
    } else if (strpos($_SERVER['HTTP_HOST'], 'insegment-leads.loc') !== false) {
        $siteURL='http://'.$_SERVER['HTTP_HOST'].'/';
    } else {
        $siteURL='http://'.$_SERVER['HTTP_HOST'].'/';
    }

    return $siteURL;
}



function isLeadIdExists($leadId, $with_upload_no=false){
    global $mysqli;
    $sql = $mysqli->prepare("SELECT id, upload_no
		FROM csv_status_leads
		WHERE
		lead_id = ?
		ORDER BY id DESC
		LIMIT 1");
    $sql->bind_param("s", $leadId);
    $sql->execute();

	$sql->bind_result($id, $upload_no);

	while ($sql->fetch()){
		$row = array($id, $upload_no);
	}
//    $sql->store_result();
    $num_returns = $sql->num_rows;
    $sql->close();

    if ($num_returns > 0) {
		if ($with_upload_no == true) {
			return $row;
		}
        return true;
    } else {
        return false;
    }
}

//Retrieve a list of all .php files in models/languages
function getLanguageFiles()
{
	$directory = "models/languages/";
	$languages = glob($directory . "*.php");
	//print each file name
	return $languages;
}

//Retrieve a list of all .css files in models/site-templates 
function getTemplateFiles()
{
	$directory = "models/site-templates/";
	$languages = glob($directory . "*.css");
	//print each file name
	return $languages;
}

//Retrieve a list of all .php files in root files folder
function getPageFiles()
{
	$directory = "";
	$pages = glob($directory . "*.php");
	//print each file name
	foreach ($pages as $page){
		$row[$page] = $page;
	}
	return $row;
}

//Destroys a session as part of logout
function destroySession($name)
{
	if(isset($_SESSION[$name]))
	{
		$_SESSION[$name] = NULL;
		unset($_SESSION[$name]);
	}
}

//Generate a unique code
function getUniqueCode($length = "")
{	
	$code = md5(uniqid(rand(), true));
	if ($length != "") return substr($code, 0, $length);
	else return $code;
}

//Generate an activation key
function generateActivationToken($gen = null)
{
	do
	{
		$gen = md5(uniqid(mt_rand(), false));
	}
	while(validateActivationToken($gen));
	return $gen;
}

//@ Thanks to - http://phpsec.org
function generateHash($plainText, $salt = null)
{
	if ($salt === null)
	{
		$salt = substr(md5(uniqid(rand(), true)), 0, 25);
	}
	else
	{
		$salt = substr($salt, 0, 25);
	}

	return $salt . sha1($salt . $plainText);
}

//Checks if an email is valid
function isValidEmail($email)
{
	if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
		return true;
	}
	else {
		return false;
	}
}

//Inputs language strings from selected language.
function lang($key,$markers = NULL)
{
	global $lang;
	if($markers == NULL)
	{
		$str = $lang[$key];
	}
	else
	{
		//Replace any dyamic markers
		$str = $lang[$key];
		$iteration = 1;
		foreach($markers as $marker)
		{
			$str = str_replace("%m".$iteration."%",$marker,$str);
			$iteration++;
		}
	}
	//Ensure we have something to return
	if($str == "")
	{
		return ("No language key found");
	}
	else
	{
		return $str;
	}
}

//Checks if a string is within a min and max length
function minMaxRange($min, $max, $what)
{
	if(strlen(trim($what)) < $min)
		return true;
	else if(strlen(trim($what)) > $max)
		return true;
	else
	return false;
}

//Replaces hooks with specified text
function replaceDefaultHook($str)
{
	global $default_hooks,$default_replace;	
	return (str_replace($default_hooks,$default_replace,$str));
}

//Displays error and success messages
function resultBlock($errors,$successes){
	//Error block
	if(count($errors) > 0)
	{
		echo "<div id='error'>
		<a href='#' onclick=\"showHide('error');\">[X]</a>
		<ul>";
		foreach($errors as $error)
		{
			echo "<li>".$error."</li>";
		}
		echo "</ul>";
		echo "</div>";
	}
	//Success block
	if(count($successes) > 0)
	{
		echo "<div id='success'>
		<a href='#' onclick=\"showHide('success');\">[X]</a>
		<ul>";
		foreach($successes as $success)
		{
			echo "<li>".$success."</li>";
		}
		echo "</ul>";
		echo "</div>";
	}
}

//Completely sanitizes text
function sanitize($str)
{
	return strtolower(strip_tags(trim(($str))));
}

// ===============================
// functions accounts, campaigns

function getAllAccounts()
{
    global $mysqli,$db_table_prefix;
    $stmt = $mysqli->prepare("SELECT
		id,
		title
		FROM ".$db_table_prefix."accounts");
    $stmt->execute();
    $stmt->bind_result($id, $title);
    while ($stmt->fetch()){
        $row[] = array('id' => $id, 'title' => $title);
    }
    $stmt->close();
    return ($row);
}

function getUserAttachedAccounts($user_id)
{
    global $mysqli,$db_table_prefix;
    $obj_type = 'account';
    $stmt = $mysqli->prepare("SELECT
		obj_id
		FROM ".$db_table_prefix."user_attached
		WHERE type = ?
		AND user_id = ?");
    $stmt->bind_param("si", $obj_type, $user_id);
    $stmt->execute();
    $stmt->bind_result($obj_id);
    while ($stmt->fetch()){
        $row[] = array('obj_id' => $obj_id);
    }
    $stmt->close();
    return ($row);
}


function insertUserAttachedAccounts($user_id, $accounts)
{
    global $mysqli,$db_table_prefix;
    $obj_type = 'account';
    $i = 0;
    //var_dump($accounts[0], $user_id, $obj_type); die();
    $stmt = $mysqli->prepare("INSERT INTO ".$db_table_prefix."user_attached
            (user_id, obj_id, type)
            VALUES
            (?, ?, ?)");
    if (is_array($accounts)) {
        foreach($accounts as $k=>$account) {
            $stmt->bind_param("iis", $user_id, $account, $obj_type);
            $stmt->execute();
            $i++;
        }
    }

    $stmt->close();
    return ($i);
}


function removeUserAttachedAccounts($user_id)
{
    global $mysqli,$db_table_prefix;
    $obj_type = 'account';
    $stmt = $mysqli->prepare("DELETE FROM ".$db_table_prefix."user_attached
        WHERE user_id = ?
        AND type = ?");
    $stmt->bind_param("is", $user_id, $obj_type);
    $result = $stmt->execute();
    $stmt->close();
    return ($result);
}


function getAccountCampaigns($account_id=null)
{
    global $mysqli,$db_table_prefix;
    if (is_null($account_id)) {
        $stmt = $mysqli->prepare("SELECT
		id,
		source_id,
		source_alias,
		title,
		post_url
		FROM ".$db_table_prefix."campaigns
		WHERE status = 'active'");
    } else {
        $stmt = $mysqli->prepare("SELECT
		id,
		source_id,
		source_alias,
		title,
		post_url
		FROM ".$db_table_prefix."campaigns
        WHERE status = 'active'
        AND account_id = ?" );
        $stmt->bind_param("i", $account_id);
    }

    $stmt->execute();
    $stmt->bind_result($id, $source_id, $source_alias, $title, $post_url);
    while ($stmt->fetch()){
        $row[] = array('id' => $id, 'source_id' => $source_id, 'source_alias' => $source_alias,
                        'title' => $title, 'post_url' => $post_url);
    }
    $stmt->close();
    return ($row);
}



function insertRemoteCampaignStatsBatch($stats) {
    global $mysqli;
    $i = 0;
    $stmt = $mysqli->prepare("REPLACE INTO api_leads_stats
            (campaign_id, source_id, status, goal, accepted, rejected)
            VALUES
            (?, ?, ?, ?, ?, ?)");
    if (is_array($stats)) {
        foreach($stats as $k=>$stat) {
            if (!empty($stat['short_id'])) {
                $stmt->bind_param("issiii", $k, $stat['short_id'], $stat['status'], $stat['goal'], $stat['accepted'], $stat['rejected']);
                $stmt->execute();
                $i++;
            }
        }
    }

    $stmt->close();
    return ($i);
}



function fetchAllCampaignsStats($offset, $records_limit, $user_id=null)
{
    global $mysqli;
    $result = array();

    $sql = $mysqli->prepare("SELECT uc.id, -- f.user_id, f.uploaded_at, f.filename, f.ip,
                                           COALESCE(SUM(f.sent), '') AS sent_count,
                                           COALESCE(SUM(lsp.passCount), '') AS pass_count,
                                           COALESCE(SUM(ls.errCount), '') AS errors_count,
                                           -- f.errors_count,
                                           uc.source_alias,
                                           uc.title,
                                           COALESCE(ua.title, '') as acc_title,
                                           uc.source_id,
                                           uc.status as campaign_status,
                                           uc.leads_goal,
                                           ua.organization_id,
                                           ua.auth_string,
                                           uc.id as campaign_id
                                    FROM uc_campaigns uc
                                    LEFT JOIN csv_uploaded_files f ON uc.id = f.campaign_id
                                    LEFT JOIN uc_accounts ua ON uc.account_id = ua.id
                                    LEFT JOIN (
                                        SELECT COUNT(1) AS errCount, file_id
                                        FROM csv_status_leads ls
                                        WHERE ls.status = 'Rejected'
                                        GROUP BY file_id
                                        ) AS ls ON ls.file_id = f.id
                                    LEFT JOIN (
                                        SELECT COUNT(1) AS passCount, file_id
                                        FROM csv_status_leads ls
                                        WHERE ls.status = 'Accepted'
                                        GROUP BY file_id
                                        ) AS lsp ON lsp.file_id = f.id
                                    GROUP BY uc.id
                                    ORDER BY uc.id DESC
                                    LIMIT $offset, $records_limit");


    $sql->execute();
    $sql->bind_result($id, $sent_count, $pass_count, $errors_count, $source_alias,
        $campaign_title, $account_title, $source_id, $campaign_status, $leads_goal, $organization_id, $auth_string,
        $campaign_id);
    $current_count = 0;
    while ($sql->fetch()) {
        $current_count++;
        if (isset($_POST['count'])) {
            if ($current_count == $_POST['count']) {
                break;
            }
        }

//        $user = fetchUserDetailsNew($user_id);
        $row = array(
            'id' => $id,
            'sent_count' => $sent_count,
            'pass_count' => $pass_count,
            'errors_count' => $errors_count,
            'source_alias' => $source_alias,
            'campaign_title' => $campaign_title,
            'account_title' => $account_title,
            'source_id' => $source_id,
            'campaign_status' => $campaign_status,
            'leads_goal' => (!is_null($leads_goal)) ? $leads_goal : "",
            'organization_id' => $organization_id,
            'auth_string' => $auth_string,
            'campaign_id' => $campaign_id
        );
        array_push($result, $row);

    }
    $sql->close();
    return $result;
}


function fetchCachedCampaignIds($campaign_ids) {
    global $mysqli;
    $result = array();

    $sql = $mysqli->prepare("SELECT als.campaign_id
                             FROM api_leads_stats als
                             WHERE als.last_update > DATE_SUB(NOW(),INTERVAL 5 MINUTE)
                             AND als.campaign_id in ($campaign_ids)");

    $sql->execute();
    $sql->bind_result($campaign_id);
    while ($sql->fetch()) {
        $row = $campaign_id;
        array_push($result, $row);

    }
    $sql->close();
    return $result;
}


function fetchCachedCampaignStatsByCampaignIds($campaign_ids) {
    global $mysqli;
    $result = array();

    $sql = $mysqli->prepare("SELECT als.campaign_id, als.goal, als.accepted, als.rejected
                             FROM api_leads_stats als
                             WHERE als.campaign_id in ($campaign_ids)");

//    print_r($sql);
    $sql->execute();
    $sql->bind_result($campaign_id, $goal, $accepted, $rejected);
    while ($sql->fetch()) {
        $row[$campaign_id] = array(
            'campaign_id' => $campaign_id,
            'goal' => $goal,
            'accepted' => $accepted,
            'rejected' => $rejected
        );
//        array_push($result, $row);
    }
    $sql->close();
    $result = $row;
    return $result;
}


function fetchAllCampaigns()
{
    global $mysqli,$db_table_prefix;
    $stmt = $mysqli->prepare("SELECT
		c.id,
		-- c.account_id,
		a.title as acc_name,
		c.source_id,
		c.source_alias,
		c.title,
		c.post_url,
		c.status,
		c.leads_goal
		FROM ".$db_table_prefix."campaigns c
		LEFT JOIN ".$db_table_prefix."accounts a ON c.account_id = a.id
		ORDER BY c.id DESC");

    $stmt->execute();
    $stmt->bind_result($id, $acc_name, $source_id, $source_alias, $title, $post_url, $status, $leads_goal);
    while ($stmt->fetch()){
        $row[] = array('id' => $id, 'acc_name' => $acc_name, 'source_id' => $source_id,
            'source_alias' => $source_alias, 'title' => $title, 'post_url' => $post_url, 'campaign_status' => $status,
            'leads_goal' => $leads_goal);
    }
    $stmt->close();
    return ($row);
}


function fetchCampaignDetails($id)
{
    global $mysqli,$db_table_prefix;
    $stmt = $mysqli->prepare("SELECT
		c.id,
		c.account_id,
		-- a.title as acc_name,
		c.source_id,
		c.source_alias,
		c.title,
		c.post_url,
		c.email_field,
		c.status,
		c.leads_goal
		FROM ".$db_table_prefix."campaigns c
		WHERE c.id = ?");

    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($id, $account_id, $source_id, $source_alias, $title, $post_url, $email_field, $status, $leads_goal);
    while ($stmt->fetch()){
        $row = array('id' => $id, 'account_id' => $account_id, 'source_id' => $source_id,
            'source_alias' => $source_alias, 'title' => $title, 'post_url' => $post_url, 'email_field' => $email_field,
            'campaign_status' => $status, 'leads_goal' => $leads_goal);
    }
    $stmt->close();
    return ($row);
}


//Check if a campaign ID exists in the DB
function campaignIdExists($id)
{
    global $mysqli,$db_table_prefix;
    $stmt = $mysqli->prepare("SELECT id
		FROM ".$db_table_prefix."campaigns
		WHERE
		id = ?
		LIMIT 1");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->store_result();
    $num_returns = $stmt->num_rows;
    $stmt->close();

    if ($num_returns > 0)
    {
        return true;
    }
    else
    {
        return false;
    }
}

// add new campaign
function createCampaign($campaign)
{
    global $mysqli,$db_table_prefix;
    $stmt = $mysqli->prepare("INSERT INTO ".$db_table_prefix."campaigns (
		account_id,
        source_id,
        source_alias,
        title,
        post_url,
        email_field,
        leads_goal
		)
		VALUES (
		?, ?, ?, ?, ?, ?, ?
		)");
    $stmt->bind_param("isssssi", $campaign["account_id"], $campaign["source_id"], $campaign["source_alias"],
                      $campaign["title"], $campaign["post_url"], $campaign["email_field"], $campaign["leads_goal"]);
    $result = $stmt->execute();
    $stmt->close();
    return $result;
}


// update campaign
function updateCampaign($campaign)
{
    global $mysqli,$db_table_prefix;
    $stmt = $mysqli->prepare("UPDATE ".$db_table_prefix."campaigns
        SET
		account_id = ?,
        source_id = ?,
        source_alias = ?,
        title = ?,
        post_url = ?,
        email_field = ?,
        status = ?,
        leads_goal = ?
		WHERE id = ?");
    $stmt->bind_param("issssssii", $campaign["account_id"], $campaign["source_id"], $campaign["source_alias"],
        $campaign["title"], $campaign["post_url"], $campaign["email_field"], $campaign["campaign_status"],
        $campaign["leads_goal"], $campaign["id"]);
    $result = $stmt->execute();
    $stmt->close();
    return $result;
}

//Functions that interact mainly with .users table
//------------------------------------------------------------------------------

//Delete a defined array of users
function deleteUsers($users) {
	global $mysqli,$db_table_prefix; 
	$i = 0;
	$stmt = $mysqli->prepare("DELETE FROM ".$db_table_prefix."users 
		WHERE id = ?");
	$stmt2 = $mysqli->prepare("DELETE FROM ".$db_table_prefix."user_permission_matches 
		WHERE user_id = ?");
	$sql = $mysqli->prepare("DELETE FROM csv_uploaded_files WHERE user_id = ?");
	foreach($users as $id){
		$stmt->bind_param("i", $id);
		$stmt->execute();
		$stmt2->bind_param("i", $id);
		$stmt2->execute();
		$sql->bind_param("i", $id);
		$sql->execute();
		$i++;
	}
	$stmt->close();
	$stmt2->close();
	$sql->close();

	return $i;
}

//Check if a display name exists in the DB
function displayNameExists($displayname)
{
	global $mysqli,$db_table_prefix;
	$stmt = $mysqli->prepare("SELECT active
		FROM ".$db_table_prefix."users
		WHERE
		display_name = ?
		LIMIT 1");
	$stmt->bind_param("s", $displayname);	
	$stmt->execute();
	$stmt->store_result();
	$num_returns = $stmt->num_rows;
	$stmt->close();
	
	if ($num_returns > 0)
	{
		return true;
	}
	else
	{
		return false;	
	}
}

//Check if an email exists in the DB
function emailExists($email)
{
	global $mysqli,$db_table_prefix;
	$stmt = $mysqli->prepare("SELECT active
		FROM ".$db_table_prefix."users
		WHERE
		email = ?
		LIMIT 1");
	$stmt->bind_param("s", $email);	
	$stmt->execute();
	$stmt->store_result();
	$num_returns = $stmt->num_rows;
	$stmt->close();
	
	if ($num_returns > 0)
	{
		return true;
	}
	else
	{
		return false;	
	}
}

//Check if a user name and email belong to the same user
function emailUsernameLinked($email,$username)
{
	global $mysqli,$db_table_prefix;
	$stmt = $mysqli->prepare("SELECT active
		FROM ".$db_table_prefix."users
		WHERE user_name = ?
		AND
		email = ?
		LIMIT 1
		");
	$stmt->bind_param("ss", $username, $email);	
	$stmt->execute();
	$stmt->store_result();
	$num_returns = $stmt->num_rows;
	$stmt->close();
	
	if ($num_returns > 0)
	{
		return true;
	}
	else
	{
		return false;	
	}
}

//Retrieve information for all users
function fetchAllUsers()
{
	global $mysqli,$db_table_prefix; 
	$stmt = $mysqli->prepare("SELECT 
		u.id,
		user_name,
		display_name,
		password,
		email,
		activation_token,
		last_activation_request,
		lost_password_request,
		active,
		u.title,
		sign_up_stamp,
		last_sign_in_stamp,
		GROUP_CONCAT(a.title) as title
		FROM ".$db_table_prefix."users u
		LEFT JOIN
		    (SELECT t1.user_id, obj_id FROM ".$db_table_prefix."user_attached t1 WHERE t1.type='account') ua
		    ON u.id=ua.user_id
        LEFT JOIN ".$db_table_prefix."accounts a ON ua.obj_id = a.id
		-- WHERE ua.type='account'
		GROUP BY u.id
		");
	$stmt->execute();
	$stmt->bind_result($id, $user, $display, $password, $email, $token, $activationRequest,
        $passwordRequest, $active, $title, $signUp, $signIn, $obj_id);
	
	while ($stmt->fetch()){
		$row[] = array('id' => $id, 'user_name' => $user, 'display_name' => $display,
            'password' => $password, 'email' => $email, 'activation_token' => $token,
            'last_activation_request' => $activationRequest, 'lost_password_request' => $passwordRequest,
            'active' => $active, 'title' => $title, 'sign_up_stamp' => $signUp,
            'last_sign_in_stamp' => $signIn, 'obj_id' => $obj_id);
	}
	$stmt->close();
	return ($row);
}

//Retrieve complete user information by username, token or ID
function fetchUserDetailsNew($id)
{
	$column = "id";
	$data = $id;

	global $mysqli3,$db_table_prefix; 
	$stmt = $mysqli3->prepare("SELECT 
		id,
		user_name,
		display_name,
		password,
		email,
		activation_token,
		last_activation_request,
		lost_password_request,
		active,
		title,
		sign_up_stamp,
		last_sign_in_stamp
		FROM ".$db_table_prefix."users
		WHERE
		$column = ?
		LIMIT 1");
		$stmt->bind_param("s", $data);
	
	$stmt->execute();
	$stmt->bind_result($id, $user, $display, $password, $email, $token, $activationRequest, $passwordRequest, $active, $title, $signUp, $signIn);
	while ($stmt->fetch()){
		$row = array('id' => $id, 'user_name' => $user, 'display_name' => $display, 'password' => $password, 'email' => $email, 'activation_token' => $token, 'last_activation_request' => $activationRequest, 'lost_password_request' => $passwordRequest, 'active' => $active, 'title' => $title, 'sign_up_stamp' => $signUp, 'last_sign_in_stamp' => $signIn);
	}
	$stmt->close();
	return ($row);
}

//Retrieve complete user information by username, token or ID
function fetchUserDetails($username=NULL,$token=NULL, $id=NULL)
{
	if($username!=NULL) {
		$column = "user_name";
		$data = $username;
	}
	elseif($token!=NULL) {
		$column = "activation_token";
		$data = $token;
	}
	elseif($id!=NULL) {
		$column = "id";
		$data = $id;
	}
	global $mysqli,$db_table_prefix; 
	$stmt = $mysqli->prepare("SELECT 
		id,
		user_name,
		display_name,
		password,
		email,
		activation_token,
		last_activation_request,
		lost_password_request,
		active,
		title,
		sign_up_stamp,
		last_sign_in_stamp
		FROM ".$db_table_prefix."users
		WHERE
		$column = ?
		LIMIT 1");
		$stmt->bind_param("s", $data);
	
	$stmt->execute();
	$stmt->bind_result($id, $user, $display, $password, $email, $token, $activationRequest, $passwordRequest, $active, $title, $signUp, $signIn);
	while ($stmt->fetch()){
		$row = array('id' => $id, 'user_name' => $user, 'display_name' => $display, 'password' => $password, 'email' => $email, 'activation_token' => $token, 'last_activation_request' => $activationRequest, 'lost_password_request' => $passwordRequest, 'active' => $active, 'title' => $title, 'sign_up_stamp' => $signUp, 'last_sign_in_stamp' => $signIn);
	}
	$stmt->close();
	return ($row);
}

//Toggle if lost password request flag on or off
function flagLostPasswordRequest($username,$value)
{
	global $mysqli,$db_table_prefix;
	$stmt = $mysqli->prepare("UPDATE ".$db_table_prefix."users
		SET lost_password_request = ?
		WHERE
		user_name = ?
		LIMIT 1
		");
	$stmt->bind_param("ss", $value, $username);
	$result = $stmt->execute();
	$stmt->close();
	return $result;
}

//Check if a user is logged in
function isUserLoggedIn()
{
	global $loggedInUser,$mysqli,$db_table_prefix;
	if(is_null($loggedInUser))
	{
		return false;
	}
	else
	{
        $stmt = $mysqli->prepare("SELECT
		id,
		password
		FROM ".$db_table_prefix."users
		WHERE
		id = ?
		AND
		password = ?
		AND
		active = 1
		LIMIT 1");
        $stmt->bind_param("is", $loggedInUser->user_id, $loggedInUser->hash_pw);
        $stmt->execute();
        $stmt->store_result();
        $num_returns = $stmt->num_rows;
        $stmt->close();

		if ($num_returns > 0)
		{
			return true;
		}
		else
		{
			destroySession("userCakeUser");
			return false;	
		}
	}
}

//Change a user from inactive to active
function setUserActive($token)
{
	global $mysqli,$db_table_prefix;
	$stmt = $mysqli->prepare("UPDATE ".$db_table_prefix."users
		SET active = 1
		WHERE
		activation_token = ?
		LIMIT 1");
	$stmt->bind_param("s", $token);
	$result = $stmt->execute();
	$stmt->close();	
	return $result;
}
function setUserInactive($id)
{
	global $mysqli,$db_table_prefix;
	$stmt = $mysqli->prepare("UPDATE ".$db_table_prefix."users
		SET active = 0
		WHERE
		id = '$id'
		LIMIT 1");
	$result = $stmt->execute();
	$stmt->close();	
	return $result;
}

//Change a user's display name
function updateDisplayName($id, $display)
{
	global $mysqli,$db_table_prefix;
	$stmt = $mysqli->prepare("UPDATE ".$db_table_prefix."users
		SET display_name = ?
		WHERE
		id = ?
		LIMIT 1");
	$stmt->bind_param("si", $display, $id);
	$result = $stmt->execute();
	$stmt->close();
	return $result;
}

//Update a user's email
function updateEmail($id, $email)
{
	global $mysqli,$db_table_prefix;
	$stmt = $mysqli->prepare("UPDATE ".$db_table_prefix."users
		SET 
		email = ?
		WHERE
		id = ?");
	$stmt->bind_param("si", $email, $id);
	$result = $stmt->execute();
	$stmt->close();	
	return $result;
}

//Input new activation token, and update the time of the most recent activation request
function updateLastActivationRequest($new_activation_token,$username,$email)
{
	global $mysqli,$db_table_prefix; 	
	$stmt = $mysqli->prepare("UPDATE ".$db_table_prefix."users
		SET activation_token = ?,
		last_activation_request = ?
		WHERE email = ?
		AND
		user_name = ?");
	$stmt->bind_param("ssss", $new_activation_token, time(), $email, $username);
	$result = $stmt->execute();
	$stmt->close();	
	return $result;
}

//Generate a random password, and new token
function updatePasswordFromToken($pass,$token)
{
	global $mysqli,$db_table_prefix;
	$new_activation_token = generateActivationToken();
	$stmt = $mysqli->prepare("UPDATE ".$db_table_prefix."users
		SET password = ?,
		activation_token = ?
		WHERE
		activation_token = ?");
	$stmt->bind_param("sss", $pass, $new_activation_token, $token);
	$result = $stmt->execute();
	$stmt->close();	
	return $result;
}

//Update a user's title
function updateTitle($id, $title)
{
	global $mysqli,$db_table_prefix;
	$stmt = $mysqli->prepare("UPDATE ".$db_table_prefix."users
		SET 
		title = ?
		WHERE
		id = ?");
	$stmt->bind_param("si", $title, $id);
	$result = $stmt->execute();
	$stmt->close();	
	return $result;	
}

//Check if a user ID exists in the DB
function userIdExists($id)
{
	global $mysqli,$db_table_prefix;
	$stmt = $mysqli->prepare("SELECT active
		FROM ".$db_table_prefix."users
		WHERE
		id = ?
		LIMIT 1");
	$stmt->bind_param("i", $id);	
	$stmt->execute();
	$stmt->store_result();
	$num_returns = $stmt->num_rows;
	$stmt->close();
	
	if ($num_returns > 0)
	{
		return true;
	}
	else
	{
		return false;	
	}
}

//Checks if a username exists in the DB
function usernameExists($username)
{
	global $mysqli,$db_table_prefix;
	$stmt = $mysqli->prepare("SELECT active
		FROM ".$db_table_prefix."users
		WHERE
		user_name = ?
		LIMIT 1");
	$stmt->bind_param("s", $username);	
	$stmt->execute();
	$stmt->store_result();
	$num_returns = $stmt->num_rows;
	$stmt->close();
	
	if ($num_returns > 0)
	{
		return true;
	}
	else
	{
		return false;	
	}
}

//Check if activation token exists in DB
function validateActivationToken($token,$lostpass=NULL)
{
	global $mysqli,$db_table_prefix;
	if($lostpass == NULL) 
	{	
		$stmt = $mysqli->prepare("SELECT active
			FROM ".$db_table_prefix."users
			WHERE active = 0
			AND
			activation_token = ?
			LIMIT 1");
	}
	else 
	{
		$stmt = $mysqli->prepare("SELECT active
			FROM ".$db_table_prefix."users
			WHERE active = 1
			AND
			activation_token = ?
			AND
			lost_password_request = 1 
			LIMIT 1");
	}
	$stmt->bind_param("s", $token);
	$stmt->execute();
	$stmt->store_result();
		$num_returns = $stmt->num_rows;
	$stmt->close();
	
	if ($num_returns > 0)
	{
		return true;
	}
	else
	{
		return false;	
	}
}

//Functions that interact mainly with .permissions table
//------------------------------------------------------------------------------

//Create a permission level in DB
function createPermission($permission) {
	global $mysqli,$db_table_prefix; 
	$stmt = $mysqli->prepare("INSERT INTO ".$db_table_prefix."permissions (
		name
		)
		VALUES (
		?
		)");
	$stmt->bind_param("s", $permission);
	$result = $stmt->execute();
	$stmt->close();	
	return $result;
}

//Delete a permission level from the DB
function deletePermission($permission) {
	global $mysqli,$db_table_prefix,$errors; 
	$i = 0;
	$stmt = $mysqli->prepare("DELETE FROM ".$db_table_prefix."permissions 
		WHERE id = ?");
	$stmt2 = $mysqli->prepare("DELETE FROM ".$db_table_prefix."user_permission_matches 
		WHERE permission_id = ?");
	$stmt3 = $mysqli->prepare("DELETE FROM ".$db_table_prefix."permission_page_matches 
		WHERE permission_id = ?");
	foreach($permission as $id){
		if ($id == 1){
			$errors[] = lang("CANNOT_DELETE_NEWUSERS");
		}
		elseif ($id == 2){
			$errors[] = lang("CANNOT_DELETE_ADMIN");
		}
		else{
			$stmt->bind_param("i", $id);
			$stmt->execute();
			$stmt2->bind_param("i", $id);
			$stmt2->execute();
			$stmt3->bind_param("i", $id);
			$stmt3->execute();
			$i++;
		}
	}
	$stmt->close();
	$stmt2->close();
	$stmt3->close();
	return $i;
}

//Retrieve information for all permission levels
function fetchAllPermissions()
{
	global $mysqli,$db_table_prefix; 
	$stmt = $mysqli->prepare("SELECT 
		id,
		name
		FROM ".$db_table_prefix."permissions");
	$stmt->execute();
	$stmt->bind_result($id, $name);
	while ($stmt->fetch()){
		$row[] = array('id' => $id, 'name' => $name);
	}
	$stmt->close();
	return ($row);
}

//Retrieve information for a single permission level
function fetchPermissionDetails($id)
{
	global $mysqli,$db_table_prefix; 
	$stmt = $mysqli->prepare("SELECT 
		id,
		name
		FROM ".$db_table_prefix."permissions
		WHERE
		id = ?
		LIMIT 1");
	$stmt->bind_param("i", $id);
	$stmt->execute();
	$stmt->bind_result($id, $name);
	while ($stmt->fetch()){
		$row = array('id' => $id, 'name' => $name);
	}
	$stmt->close();
	return ($row);
}

//Check if a permission level ID exists in the DB
function permissionIdExists($id)
{
	global $mysqli,$db_table_prefix;
	$stmt = $mysqli->prepare("SELECT id
		FROM ".$db_table_prefix."permissions
		WHERE
		id = ?
		LIMIT 1");
	$stmt->bind_param("i", $id);	
	$stmt->execute();
	$stmt->store_result();
	$num_returns = $stmt->num_rows;
	$stmt->close();
	
	if ($num_returns > 0)
	{
		return true;
	}
	else
	{
		return false;	
	}
}

//Check if a permission level name exists in the DB
function permissionNameExists($permission)
{
	global $mysqli,$db_table_prefix;
	$stmt = $mysqli->prepare("SELECT id
		FROM ".$db_table_prefix."permissions
		WHERE
		name = ?
		LIMIT 1");
	$stmt->bind_param("s", $permission);	
	$stmt->execute();
	$stmt->store_result();
	$num_returns = $stmt->num_rows;
	$stmt->close();
	
	if ($num_returns > 0)
	{
		return true;
	}
	else
	{
		return false;	
	}
}

//Change a permission level's name
function updatePermissionName($id, $name)
{
	global $mysqli,$db_table_prefix;
	$stmt = $mysqli->prepare("UPDATE ".$db_table_prefix."permissions
		SET name = ?
		WHERE
		id = ?
		LIMIT 1");
	$stmt->bind_param("si", $name, $id);
	$result = $stmt->execute();
	$stmt->close();	
	return $result;	
}

//Functions that interact mainly with .user_permission_matches table
//------------------------------------------------------------------------------

//Match permission level(s) with user(s)
function addPermission($permission, $user) {
	global $mysqli,$db_table_prefix; 
	$i = 0;
	$stmt = $mysqli->prepare("INSERT INTO ".$db_table_prefix."user_permission_matches (
		permission_id,
		user_id
		)
		VALUES (
		?,
		?
		)");
	if (is_array($permission)){
		foreach($permission as $id){
			$stmt->bind_param("ii", $id, $user);
			$stmt->execute();
			$i++;
		}
	}
	elseif (is_array($user)){
		foreach($user as $id){
			$stmt->bind_param("ii", $permission, $id);
			$stmt->execute();
			$i++;
		}
	}
	else {
		$stmt->bind_param("ii", $permission, $user);
		$stmt->execute();
		$i++;
	}
	$stmt->close();
	return $i;
}

//Retrieve information for all user/permission level matches
function fetchAllMatches()
{
	global $mysqli,$db_table_prefix; 
	$stmt = $mysqli->prepare("SELECT 
		id,
		user_id,
		permission_id
		FROM ".$db_table_prefix."user_permission_matches");
	$stmt->execute();
	$stmt->bind_result($id, $user, $permission);
	while ($stmt->fetch()){
		$row[] = array('id' => $id, 'user_id' => $user, 'permission_id' => $permission);
	}
	$stmt->close();
	return ($row);	
}

//Retrieve list of permission levels a user has
function fetchUserPermissions($user_id)
{
	global $mysqli,$db_table_prefix; 
	$stmt = $mysqli->prepare("SELECT
		id,
		permission_id
		FROM ".$db_table_prefix."user_permission_matches
		WHERE user_id = ?
		");
	$stmt->bind_param("i", $user_id);	
	$stmt->execute();
	$stmt->bind_result($id, $permission);
	while ($stmt->fetch()){
		$row[$permission] = array('id' => $id, 'permission_id' => $permission);
	}
	$stmt->close();
	if (isset($row)){
		return ($row);
	}
}

//Retrieve list of users who have a permission level
function fetchPermissionUsers($permission_id)
{
	global $mysqli,$db_table_prefix; 
	$stmt = $mysqli->prepare("SELECT id, user_id
		FROM ".$db_table_prefix."user_permission_matches
		WHERE permission_id = ?
		");
	$stmt->bind_param("i", $permission_id);	
	$stmt->execute();
	$stmt->bind_result($id, $user);
	while ($stmt->fetch()){
		$row[$user] = array('id' => $id, 'user_id' => $user);
	}
	$stmt->close();
	if (isset($row)){
		return ($row);
	}
}

//Unmatch permission level(s) from user(s)
function removePermission($permission, $user) {
	global $mysqli,$db_table_prefix; 
	$i = 0;
	$stmt = $mysqli->prepare("DELETE FROM ".$db_table_prefix."user_permission_matches 
		WHERE permission_id = ?
		AND user_id =?");
	if (is_array($permission)){
		foreach($permission as $id){
			$stmt->bind_param("ii", $id, $user);
			$stmt->execute();
			$i++;
		}
	}
	elseif (is_array($user)){
		foreach($user as $id){
			$stmt->bind_param("ii", $permission, $id);
			$stmt->execute();
			$i++;
		}
	}
	else {
		$stmt->bind_param("ii", $permission, $user);
		$stmt->execute();
		$i++;
	}
	$stmt->close();
	return $i;
}

//Functions that interact mainly with .configuration table
//------------------------------------------------------------------------------

//Update configuration table
function updateConfig($id, $value)
{
	global $mysqli,$db_table_prefix;
	$stmt = $mysqli->prepare("UPDATE ".$db_table_prefix."configuration
		SET 
		value = ?
		WHERE
		id = ?");
	foreach ($id as $cfg){
		$stmt->bind_param("si", $value[$cfg], $cfg);
		$stmt->execute();
	}
	$stmt->close();	
}

//Functions that interact mainly with .pages table
//------------------------------------------------------------------------------

//Add a page to the DB
function createPages($pages) {
	global $mysqli,$db_table_prefix; 
	$stmt = $mysqli->prepare("INSERT INTO ".$db_table_prefix."pages (
		page
		)
		VALUES (
		?
		)");
	foreach($pages as $page){
		$stmt->bind_param("s", $page);
		$stmt->execute();
	}
	$stmt->close();
}

//Delete a page from the DB
function deletePages($pages) {
	global $mysqli,$db_table_prefix; 
	$stmt = $mysqli->prepare("DELETE FROM ".$db_table_prefix."pages 
		WHERE id = ?");
	$stmt2 = $mysqli->prepare("DELETE FROM ".$db_table_prefix."permission_page_matches 
		WHERE page_id = ?");
	foreach($pages as $id){
		$stmt->bind_param("i", $id);
		$stmt->execute();
		$stmt2->bind_param("i", $id);
		$stmt2->execute();
	}
	$stmt->close();
	$stmt2->close();
}

//Fetch information on all pages
function fetchAllPages()
{
	global $mysqli,$db_table_prefix; 
	$stmt = $mysqli->prepare("SELECT 
		id,
		page,
		private
		FROM ".$db_table_prefix."pages");
	$stmt->execute();
	$stmt->bind_result($id, $page, $private);
	while ($stmt->fetch()){
		$row[$page] = array('id' => $id, 'page' => $page, 'private' => $private);
	}
	$stmt->close();
	if (isset($row)){
		return ($row);
	}
}

//Fetch information for a specific page
function fetchPageDetails($id)
{
	global $mysqli,$db_table_prefix; 
	$stmt = $mysqli->prepare("SELECT 
		id,
		page,
		private
		FROM ".$db_table_prefix."pages
		WHERE
		id = ?
		LIMIT 1");
	$stmt->bind_param("i", $id);
	$stmt->execute();
	$stmt->bind_result($id, $page, $private);
	while ($stmt->fetch()){
		$row = array('id' => $id, 'page' => $page, 'private' => $private);
	}
	$stmt->close();
	return ($row);
}

//Check if a page ID exists
function pageIdExists($id)
{
	global $mysqli,$db_table_prefix;
	$stmt = $mysqli->prepare("SELECT private
		FROM ".$db_table_prefix."pages
		WHERE
		id = ?
		LIMIT 1");
	$stmt->bind_param("i", $id);	
	$stmt->execute();
	$stmt->store_result();	
	$num_returns = $stmt->num_rows;
	$stmt->close();
	
	if ($num_returns > 0)
	{
		return true;
	}
	else
	{
		return false;	
	}
}

//Toggle private/public setting of a page
function updatePrivate($id, $private)
{
	global $mysqli,$db_table_prefix;
	$stmt = $mysqli->prepare("UPDATE ".$db_table_prefix."pages
		SET 
		private = ?
		WHERE
		id = ?");
	$stmt->bind_param("ii", $private, $id);
	$result = $stmt->execute();
	$stmt->close();	
	return $result;	
}

//Functions that interact mainly with .permission_page_matches table
//------------------------------------------------------------------------------

//Match permission level(s) with page(s)
function addPage($page, $permission) {
	global $mysqli,$db_table_prefix; 
	$i = 0;
	$stmt = $mysqli->prepare("INSERT INTO ".$db_table_prefix."permission_page_matches (
		permission_id,
		page_id
		)
		VALUES (
		?,
		?
		)");
	if (is_array($permission)){
		foreach($permission as $id){
			$stmt->bind_param("ii", $id, $page);
			$stmt->execute();
			$i++;
		}
	}
	elseif (is_array($page)){
		foreach($page as $id){
			$stmt->bind_param("ii", $permission, $id);
			$stmt->execute();
			$i++;
		}
	}
	else {
		$stmt->bind_param("ii", $permission, $page);
		$stmt->execute();
		$i++;
	}
	$stmt->close();
	return $i;
}

//Retrieve list of permission levels that can access a page
function fetchPagePermissions($page_id)
{
	global $mysqli,$db_table_prefix; 
	$stmt = $mysqli->prepare("SELECT
		id,
		permission_id
		FROM ".$db_table_prefix."permission_page_matches
		WHERE page_id = ?
		");
	$stmt->bind_param("i", $page_id);	
	$stmt->execute();
	$stmt->bind_result($id, $permission);
	while ($stmt->fetch()){
		$row[$permission] = array('id' => $id, 'permission_id' => $permission);
	}
	$stmt->close();
	if (isset($row)){
		return ($row);
	}
}

//Retrieve list of pages that a permission level can access
function fetchPermissionPages($permission_id)
{
	global $mysqli,$db_table_prefix; 
	$stmt = $mysqli->prepare("SELECT
		id,
		page_id
		FROM ".$db_table_prefix."permission_page_matches
		WHERE permission_id = ?
		");
	$stmt->bind_param("i", $permission_id);	
	$stmt->execute();
	$stmt->bind_result($id, $page);
	while ($stmt->fetch()){
		$row[$page] = array('id' => $id, 'permission_id' => $page);
	}
	$stmt->close();
	if (isset($row)){
		return ($row);
	}
}

//Unmatched permission and page
function removePage($page, $permission) {
	global $mysqli,$db_table_prefix; 
	$i = 0;
	$stmt = $mysqli->prepare("DELETE FROM ".$db_table_prefix."permission_page_matches 
		WHERE page_id = ?
		AND permission_id =?");
	if (is_array($page)){
		foreach($page as $id){
			$stmt->bind_param("ii", $id, $permission);
			$stmt->execute();
			$i++;
		}
	}
	elseif (is_array($permission)){
		foreach($permission as $id){
			$stmt->bind_param("ii", $page, $id);
			$stmt->execute();
			$i++;
		}
	}
	else {
		$stmt->bind_param("ii", $permission, $user);
		$stmt->execute();
		$i++;
	}
	$stmt->close();
	return $i;
}

//Check if a user has access to a page
function securePage($uri){
	
	//Separate document name from uri
	$tokens = explode('/', $uri);
	$page = $tokens[sizeof($tokens)-1];
	global $mysqli,$db_table_prefix,$loggedInUser,$master_account;
	//retrieve page details
	$stmt = $mysqli->prepare("SELECT 
		id,
		page,
		private
		FROM ".$db_table_prefix."pages
		WHERE
		page = ?
		LIMIT 1");
	$stmt->bind_param("s", $page);
	$stmt->execute();
	$stmt->bind_result($id, $page, $private);
	while ($stmt->fetch()){
		$pageDetails = array('id' => $id, 'page' => $page, 'private' => $private);
	}
	$stmt->close();
	//If page does not exist in DB, allow access
	if (empty($pageDetails)){
		return true;
	}
	//If page is public, allow access
	elseif ($pageDetails['private'] == 0) {
		return true;	
	}
	//If user is not logged in, deny access
	elseif(!isUserLoggedIn()) 
	{
		header("Location: login.php");
		return false;
	}
	else {
		//Retrieve list of permission levels with access to page
		$stmt = $mysqli->prepare("SELECT
			permission_id
			FROM ".$db_table_prefix."permission_page_matches
			WHERE page_id = ?
			");
		$stmt->bind_param("i", $pageDetails['id']);	
		$stmt->execute();
		$stmt->bind_result($permission);
		while ($stmt->fetch()){
			$pagePermissions[] = $permission;
		}
		$stmt->close();
		//Check if user's permission levels allow access to page
		if ($loggedInUser->checkPermission($pagePermissions)){ 
			return true;
		}
		//Grant access if master user
		elseif ($loggedInUser->user_id == $master_account){
			return true;
		}
		else {
			header("Location: account.php");
			return false;	
		}
	}
}

?>

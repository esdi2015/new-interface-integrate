<?php
require_once("models/config.php");
if (!securePage($_SERVER['PHP_SELF'])){die();}
if(isUserLoggedIn()) {
if ($loggedInUser->checkPermission(array(2,3))){
?>
<!DOCTYPE html>
<html lang="en">
  
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>
      Administration panel
    </title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css" rel="stylesheet">
    <script src="http://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js">
    </script>
    <script src="js/jquery.bootstrap.pagination.js" type="text/javascript">
    </script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js" type="text/javascript">
    </script>
    <script src="models/funcs.js" type="text/javascript">
    </script>
    <link href="../css/admin-sidebar.css" rel="stylesheet">
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>
  
  <body>
    <div id="wrapper">
      <div id="sidebar-wrapper">
          <?php
          require_once(SITE_PATH . '/user/left_nav_admin.php');
          ?>
      </div>
      
      <div id="page-content-wrapper">
        <div class="container-fluid">
          <div class="row">
            <div class="col-lg-12">
           
<?php
$campaignId = $_GET['id'];

//Check if selected campaign exists
if(!campaignIdExists($campaignId)){
	header("Location: admin_campaigns.php"); die();
}

$campaign_details = fetchCampaignDetails($campaignId); //Fetch campaign details
$accounts = getAllAccounts();
//var_dump($campaign_details);
$errors = array();
$successes = array();
//Forms posted
if(!empty($_POST))
{
    $campaign_updates = $campaign_details;
    if ($campaign_details['title'] != $_POST['title']) {
        $campaign_updates['title'] = trim($_POST['title']);
    }

    if ($campaign_details['account_id'] != $_POST['account_select']) {
        $campaign_updates['account_id'] = trim($_POST['account_select']);
    }

    if ($campaign_details['source_id'] != $_POST['source_id']) {
        $campaign_updates['source_id'] = trim($_POST['source_id']);
    }

    if ($campaign_details['source_alias'] != $_POST['source_alias']) {
        $campaign_updates['source_alias'] = trim($_POST['source_alias']);
    }

    if ($campaign_details['post_url'] != $_POST['post_url']) {
        $campaign_updates['post_url'] = trim($_POST['post_url']);
    }

    if ($campaign_details['email_field'] != $_POST['email_field']) {
        $campaign_updates['email_field'] = trim($_POST['email_field']);
    }

    if ($campaign_details['campaign_status'] != $_POST['campaign_status']) {
        $campaign_updates['campaign_status'] = trim($_POST['campaign_status']);
    }

    $campaign_updates['id'] = $campaignId;
    // $campaign_details = $campaign_updates;

    $update_campaign = updateCampaign($campaign_updates);
    if ($update_campaign == true) {
        $campaign_details = $campaign_updates;
        $successes[] = lang("CAMPAIGN_UPDATED");
    } else {
        $errors[] = lang("SQL_ERROR");
    }

}

$userPermission = fetchUserPermissions($userId);
$permissionData = fetchAllPermissions();

echo "<div id='main'>";

echo resultBlock($errors,$successes);

echo "
<form name='adminCampaign' action='".$_SERVER['PHP_SELF']."?id=".$campaignId."' method='post'>
<table class='table table-condensed'><tr><td>
<h3>Campaign Information</h3>
<div id='regbox'>
<p>
<label>ID:</label>
".$campaign_details['id']."
</p>
<p>
<label>Campaign Name:</label>
<input type='text' name='title' value='".$campaign_details['title']."' class='form-control' />
</p>
<p>
<label>Source ID:</label>
<input type='text' name='source_id' value='".$campaign_details['source_id']."' class='form-control' />
</p>
<p>
<label>Source Alias:</label>
<input type='text' name='source_alias' value='".$campaign_details['source_alias']."' class='form-control' />
</p>
<p>
<label>POST URL:</label>
<input type='text' name='post_url' value='".$campaign_details['post_url']."' class='form-control' />
</p>
<p>
<label>Email field name:</label>
<input type='text' name='email_field' value='".$campaign_details['email_field']."' class='form-control' />
</p>
<p>
<label>Account:</label>
<!--<input type='text' name='account_select' value='".$campaign_details['account_id']."' class='form-control' />-->
<select id='account_select' name='account_select' class='form-control'>";
foreach ($accounts as $acc){
    if ($campaign_details['account_id'] == $acc['id']) {
        echo "<option value='".$acc['id']."' selected='selected'>".$acc['title']."</option>";
    } else {
        echo "<option value='".$acc['id']."'>".$acc['title']."</option>";
    }
}
echo "
</select>
</p>
<label>Campaign status:</label>
<select id='campaign_status' name='campaign_status' class='form-control'>";
foreach ($CAMPAIGN_STATUSES as $status){
    if ($campaign_details['campaign_status'] == $status) {
        echo "<option value='".$status."' selected='selected'>".$status."</option>";
    } else {
        echo "<option value='".$status."'>".$status."</option>";
    }
}
echo "
</select>
</p>
";

echo "
<p>
<input type='submit' value='Update' class='btn btn-default' />
</p>
</div>
</td>
</tr>
</table>
</form>
</div>";

?>

 </div>
          </div>
        </div>
      </div>
    </div>
    <?php
    }}else{
    ?>
    <div class="container">
      <form class="form-signin" name="login" action="login.php" method="post">
        <h2 class="form-signin-heading">
          Please sign in
        </h2>
        <label for="inputEmail" class="sr-only">
          Username
        </label>
        <input type="text" id="username" name="username" class="form-control" placeholder="Your username" required="" autofocus="">
        <label for="inputPassword" class="sr-only">
          Password
        </label>
        <input type="password" id="password" name="password" class="form-control" placeholder="Your password" required="">
        <button class="btn btn-lg btn-primary btn-block" type="submit">
          Sign in
        </button>
      </form>
    </div>
    <?php
    }
    ?>
  </body> 
</html>
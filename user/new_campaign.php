<?php
ob_start();
?>
<html>
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
    <![endif]-->  </head>
    <body>
    <?php
    require_once("models/config.php");

    ob_flush();

if (!securePage($_SERVER['PHP_SELF'])){die();}
if(isUserLoggedIn()) {
if ($loggedInUser->checkPermission(array(2,3))){
    $accounts = getAllAccounts();
	?>
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
$errors = array();
$successes = array();

//Forms posted
if(!empty($_POST))
{

    $campaign["title"] = trim($_POST["campaign_name"]);
    $campaign["source_id"] = trim($_POST["source_id"]);
    $campaign["source_alias"] = trim($_POST["source_alias"]);
    $campaign["post_url"] = trim($_POST["post_url"]);
    $campaign["email_field"] = trim($_POST["email_field"]);
    $campaign["account_id"] = trim($_POST["account_select"]);
    $campaign["leads_goal"] = trim($_POST["leads_goal"]);

    $new_campaign = createCampaign($campaign);
    if ($new_campaign == true) {
        $successes[] = lang("CAMPAIGN_ADDED");
    } else {
        $errors[] = lang("SQL_ERROR");
    }

}

echo resultBlock($errors,$successes);

echo "
<form name='newUser' action='".$_SERVER['PHP_SELF']."' method='post' class='form-signin'>
<h2 class='form-signin-heading'>New campaign</h2>
<p>
<label>Campaign Name:</label>
<input type='text' name='campaign_name' class='form-control' value='".$_POST["campaign_name"]."' />
</p>
<p>
<label>Leads goal:</label>
<input type='text' name='leads_goal' class='form-control' value='".$_POST["leads_goal"]."' />
</p>
<p>
<label>Source ID:</label>
<input type='text' name='source_id' class='form-control' value='".$_POST["source_id"]."' />
</p>
<p>
<label>Source Alias:</label>
<input type='text' name='source_alias' class='form-control' value='".$_POST["source_alias"]."' />
</p>
<p>
<label>POST URL:</label>
<input type='text' name='post_url' class='form-control' value='".$_POST["post_url"]."' />
</p>
<p>
<label>Email field name:</label>
<input type='text' name='email_field' value='".$_POST['email_field']."' class='form-control' />
</p>
<p>
<label>Account:</label>
<select id='account_select' name='account_select' class='form-control'>";
foreach ($accounts as $acc){
    if ($_POST['account_select'] == $acc['id']) {
        echo "<option value='".$acc['id']."' selected='selected'>".$acc['title']."</option>";
    } else {
        echo "<option value='".$acc['id']."'>".$acc['title']."</option>";
    }
}
echo "
</select>
</p>
<p>";

if (count($successes) == 0) {
echo "
<label>&nbsp;<br>
<input type='submit' value='Create' class='btn btn-lg btn-primary btn-block' />";
} else {
echo "<a href='admin_campaigns.php'>Manage campaigns</a>";
}

?>
</p>
</form>
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

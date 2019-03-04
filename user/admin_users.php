<?php
require_once("models/config.php");
if (!securePage($_SERVER['PHP_SELF'])){die();}
if(isUserLoggedIn()) {
if ($loggedInUser->
checkPermission(array(2))){
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
        <ul class="sidebar-nav">
        <li class="sidebar-brand" style="color: silver;">
              Index
          </li>
           <li>
            <a href='index_admin.php'>
              Dashboard
            </a>
          </li>
<!--            <li>-->
<!--                <a href='leads_admin.php'>-->
<!--                    Accepted leads-->
<!--                </a>-->
<!--            </li>-->
            <li>
                <a href='leads_statuses.php'>
                    Leads
                </a>
            </li>
          <li class="sidebar-brand" style="color: silver;">
              Administration
          </li>
          <li>
            <a href='admin_configuration.php'>
              Configuration
            </a>
          </li>
                    <li>
            <a href='register.php'>
              Create new user
            </a>
          </li>
          <li>
            <a href='admin_users.php'>
              Manage users
            </a>
          </li>
<!--<li>
            <a href='admin_permissions.php'>
              Manage permissions
            </a>
          </li>-->
          <li class="sidebar-brand" style="color: silver;">
              Current user
          </li>
          <li>
            <a href='user_settings.php'>
              User Settings
            </a>
          </li>
          <li>
            <a href='<?php echo $websiteUrl; ?>'>
              My history
            </a>
          </li>
          <li>
            <a href='logout.php'>
              Logout
            </a>
          </li>
        </ul>
      </div>
      
      <div id="page-content-wrapper">
        <div class="container-fluid">
          <div class="row">
            <div class="col-lg-12">
            
<?php
//Forms posted
if(!empty($_POST))
{
	$deletions = $_POST['delete'];
	if ($deletion_count = deleteUsers($deletions)){
		$successes[] = lang("ACCOUNT_DELETIONS_SUCCESSFUL", array($deletion_count));
	}
	else {
		$errors[] = lang("SQL_ERROR");
	}
}

$userData = fetchAllUsers(); //Fetch information for all users
echo "<div id='main'>";

echo resultBlock($errors,$successes);

echo "
<form name='adminUsers' action='".$_SERVER['PHP_SELF']."' method='post'>
<table class='table table-condensed'>
<tr>
<th>Delete</th><th>Username</th><th>Display Name</th><th>Title</th><th>Last Sign In</th><th></th>
</tr>";

//Cycle through users
foreach ($userData as $v1) {
	echo "
	<tr>
	<td><input type='checkbox' name='delete[".$v1['id']."]' id='delete[".$v1['id']."]' value='".$v1['id']."'></td>
	<td><a href='admin_user.php?id=".$v1['id']."'>".$v1['user_name']."</a></td>
	<td>".$v1['display_name']."</td>
	<td>".$v1['title']."</td>
	<td>
	";
	
	//Interprety last login
	if ($v1['last_sign_in_stamp'] == '0'){
		echo "Never";	
	}
	else {
		echo date("j M, Y", $v1['last_sign_in_stamp']);
	}
	echo "
	</td>
  <td>
  <a href='admin_user_uploads.php?uid=".$v1['id']."'>Show upload history</a>
  </td>
	</tr>";
}

echo "
</table>
<input type='submit' name='Submit' value='Delete' class='btn btn-default' />
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
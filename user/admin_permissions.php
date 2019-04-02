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
          <?php
          require_once(SITE_PATH . '/user/left_nav_admin.php');
          ?>
      </div>
      
      <div id="page-content-wrapper">
        <div class="container-fluid">
          <div class="row">
            <div class="col-lg-12">
            
<?php
//Forms posted
if(!empty($_POST))
{
	//Delete permission levels
	if(!empty($_POST['delete'])){
		$deletions = $_POST['delete'];
		if ($deletion_count = deletePermission($deletions)){
		$successes[] = lang("PERMISSION_DELETIONS_SUCCESSFUL", array($deletion_count));
		}
	}
	
	//Create new permission level
	if(!empty($_POST['newPermission'])) {
		$permission = trim($_POST['newPermission']);
		
		//Validate request
		if (permissionNameExists($permission)){
			$errors[] = lang("PERMISSION_NAME_IN_USE", array($permission));
		}
		elseif (minMaxRange(1, 50, $permission)){
			$errors[] = lang("PERMISSION_CHAR_LIMIT", array(1, 50));	
		}
		else{
			if (createPermission($permission)) {
			$successes[] = lang("PERMISSION_CREATION_SUCCESSFUL", array($permission));
		}
			else {
				$errors[] = lang("SQL_ERROR");
			}
		}
	}
}

$permissionData = fetchAllPermissions(); //Retrieve list of all permission levels

require_once("models/header.php");
echo "<div id='main'>";

echo resultBlock($errors,$successes);

echo "
<form name='adminPermissions' action='".$_SERVER['PHP_SELF']."' method='post'>
<table class='table table-condensed'>
<tr>
<th>Delete</th><th>Permission Name</th>
</tr>";

//List each permission level
foreach ($permissionData as $v1) {
	echo "
	<tr>
	<td><input type='checkbox' name='delete[".$v1['id']."]' id='delete[".$v1['id']."]' value='".$v1['id']."'></td>
	<td><a href='admin_permission.php?id=".$v1['id']."'>".$v1['name']."</a></td>
	</tr>";
}

echo "
</table>
<p>
<label>Permission Name:</label>
<input type='text' name='newPermission' class='form-control' />
</p>                                
<input type='submit' name='Submit' value='Submit' class='btn btn-default' />
</form>
</div>
";

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
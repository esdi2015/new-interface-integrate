<?php
require_once("models/config.php");
if (!securePage($_SERVER['PHP_SELF'])){die();}
$permissionId = $_GET['id'];

//Check if selected permission level exists
if(!permissionIdExists($permissionId)){
	header("Location: admin_permissions.php"); die();	
}

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
$permissionDetails = fetchPermissionDetails($permissionId); //Fetch information specific to permission level

//Forms posted
if(!empty($_POST)){
	
	//Delete selected permission level
	if(!empty($_POST['delete'])){
		$deletions = $_POST['delete'];
		if ($deletion_count = deletePermission($deletions)){
		$successes[] = lang("PERMISSION_DELETIONS_SUCCESSFUL", array($deletion_count));
		}
		else {
			$errors[] = lang("SQL_ERROR");	
		}
	}
	else
	{
		//Update permission level name
		if($permissionDetails['name'] != $_POST['name']) {
			$permission = trim($_POST['name']);
			
			//Validate new name
			if (permissionNameExists($permission)){
				$errors[] = lang("ACCOUNT_PERMISSIONNAME_IN_USE", array($permission));
			}
			elseif (minMaxRange(1, 50, $permission)){
				$errors[] = lang("ACCOUNT_PERMISSION_CHAR_LIMIT", array(1, 50));	
			}
			else {
				if (updatePermissionName($permissionId, $permission)){
					$successes[] = lang("PERMISSION_NAME_UPDATE", array($permission));
				}
				else {
					$errors[] = lang("SQL_ERROR");
				}
			}
		}
		
		//Remove access to pages
		if(!empty($_POST['removePermission'])){
			$remove = $_POST['removePermission'];
			if ($deletion_count = removePermission($permissionId, $remove)) {
				$successes[] = lang("PERMISSION_REMOVE_USERS", array($deletion_count));
			}
			else {
				$errors[] = lang("SQL_ERROR");
			}
		}
		
		//Add access to pages
		if(!empty($_POST['addPermission'])){
			$add = $_POST['addPermission'];
			if ($addition_count = addPermission($permissionId, $add)) {
				$successes[] = lang("PERMISSION_ADD_USERS", array($addition_count));
			}
			else {
				$errors[] = lang("SQL_ERROR");
			}
		}
		
		//Remove access to pages
		if(!empty($_POST['removePage'])){
			$remove = $_POST['removePage'];
			if ($deletion_count = removePage($remove, $permissionId)) {
				$successes[] = lang("PERMISSION_REMOVE_PAGES", array($deletion_count));
			}
			else {
				$errors[] = lang("SQL_ERROR");
			}
		}
		
		//Add access to pages
		if(!empty($_POST['addPage'])){
			$add = $_POST['addPage'];
			if ($addition_count = addPage($add, $permissionId)) {
				$successes[] = lang("PERMISSION_ADD_PAGES", array($addition_count));
			}
			else {
				$errors[] = lang("SQL_ERROR");
			}
		}
			$permissionDetails = fetchPermissionDetails($permissionId);
	}
}

$pagePermissions = fetchPermissionPages($permissionId); //Retrieve list of accessible pages
$permissionUsers = fetchPermissionUsers($permissionId); //Retrieve list of users with membership
$userData = fetchAllUsers(); //Fetch all users
$pageData = fetchAllPages(); //Fetch all pages

echo resultBlock($errors,$successes);

echo "
<form name='adminPermission' action='".$_SERVER['PHP_SELF']."?id=".$permissionId."' method='post'>
<table class='table table-condensed'>
<tr><td>
<h3>Permission Information</h3>
<div id='regbox'>
<p>
<label>ID:</label>
".$permissionDetails['id']."
</p>
<p>
<label>Name:</label>
<input type='text' name='name' value='".$permissionDetails['name']."' class='form-control' />
</p>
<label>Delete:</label>
<input type='checkbox' name='delete[".$permissionDetails['id']."]' id='delete[".$permissionDetails['id']."]' value='".$permissionDetails['id']."'>
</p>
</div></td><td>
<h3>Permission Membership</h3>
<div id='regbox'>
<p>
Remove Members:";

//List users with permission level
foreach ($userData as $v1) {
	if(isset($permissionUsers[$v1['id']])){
		echo "<br><input type='checkbox' name='removePermission[".$v1['id']."]' id='removePermission[".$v1['id']."]' value='".$v1['id']."'> ".$v1['display_name'];
	}
}

echo"
</p><p>Add Members:";

//List users without permission level
foreach ($userData as $v1) {
	if(!isset($permissionUsers[$v1['id']])){
		echo "<br><input type='checkbox' name='addPermission[".$v1['id']."]' id='addPermission[".$v1['id']."]' value='".$v1['id']."'> ".$v1['display_name'];
	}
}

echo"
</p>
</div>
</td>
<td>
<h3>Permission Access</h3>
<div id='regbox'>
<p>
Public Access:";

//List public pages
foreach ($pageData as $v1) {
	if($v1['private'] != 1){
		echo "<br>".$v1['page'];
	}
}

echo"
</p>
<p>
Remove Access:";

//List pages accessible to permission level
foreach ($pageData as $v1) {
	if(isset($pagePermissions[$v1['id']]) AND $v1['private'] == 1){
		echo "<br><input type='checkbox' name='removePage[".$v1['id']."]' id='removePage[".$v1['id']."]' value='".$v1['id']."'> ".$v1['page'];
	}
}

echo"
</p><p>Add Access:";

//List pages inaccessible to permission level
foreach ($pageData as $v1) {
	if(!isset($pagePermissions[$v1['id']]) AND $v1['private'] == 1){
		echo "<br><input type='checkbox' name='addPage[".$v1['id']."]' id='addPage[".$v1['id']."]' value='".$v1['id']."'> ".$v1['page'];
	}
}

echo"
</p>
</div>
</td>
</tr>
</table>
<p>
<label>&nbsp;</label>
<input type='submit' value='Update' class='btn btn-success' />
</p>
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
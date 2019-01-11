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
if (!securePage($_SERVER['PHP_SELF'])){die();}
if(isUserLoggedIn()) {
if ($loggedInUser->
checkPermission(array(2))){
	
	?>
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
          </li><li>
                <a href='leads_admin.php'>
                    Accepted leads
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
	$errors = array();
	$email = trim($_POST["email"]);
	$username = trim($_POST["username"]);
	$displayname = trim($_POST["displayname"]);
	$password = trim($_POST["password"]);
	$confirm_pass = trim($_POST["passwordc"]);
	$captcha = md5($_POST["captcha"]);
	
	
	/*if ($captcha != $_SESSION['captcha'])
	{
		$errors[] = lang("CAPTCHA_FAIL");
	}*/
	if(minMaxRange(5,25,$username))
	{
		$errors[] = lang("ACCOUNT_USER_CHAR_LIMIT",array(5,25));
	}
	if(!ctype_alnum($username)){
		$errors[] = lang("ACCOUNT_USER_INVALID_CHARACTERS");
	}
	if(minMaxRange(5,25,$displayname))
	{
		$errors[] = lang("ACCOUNT_DISPLAY_CHAR_LIMIT",array(5,25));
	}
	if(!ctype_alnum($displayname)){
		$errors[] = lang("ACCOUNT_DISPLAY_INVALID_CHARACTERS");
	}
	if(minMaxRange(8,50,$password) && minMaxRange(8,50,$confirm_pass))
	{
		$errors[] = lang("ACCOUNT_PASS_CHAR_LIMIT",array(8,50));
	}
	else if($password != $confirm_pass)
	{
		$errors[] = lang("ACCOUNT_PASS_MISMATCH");
	}
	if(!isValidEmail($email))
	{
		$errors[] = lang("ACCOUNT_INVALID_EMAIL");
	}
	//End data validation
	if(count($errors) == 0)
	{	
		//Construct a user object
		$user = new User($username,$displayname,$password,$email);
		
		//Checking this flag tells us whether there were any errors such as possible data duplication occured
		if(!$user->status)
		{
			if($user->username_taken) $errors[] = lang("ACCOUNT_USERNAME_IN_USE",array($username));
			if($user->displayname_taken) $errors[] = lang("ACCOUNT_DISPLAYNAME_IN_USE",array($displayname));
			if($user->email_taken) 	  $errors[] = lang("ACCOUNT_EMAIL_IN_USE",array($email));		
		}
		else
		{
			//Attempt to add the user to the database, carry out finishing  tasks like emailing the user (if required)
			if(!$user->userCakeAddUser())
			{
				if($user->mail_failure) $errors[] = lang("MAIL_ERROR");
				if($user->sql_failure)  $errors[] = lang("SQL_ERROR");
			}
		}
	}
	if(count($errors) == 0) {
		$successes[] = $user->success;
	}
}

echo resultBlock($errors,$successes);

echo "
<form name='newUser' action='".$_SERVER['PHP_SELF']."' method='post' class='form-signin'>
<h2 class='form-signin-heading'>New user registration</h2>
<p>
<label>User Name:</label>
<input type='text' name='username' class='form-control' value='".$_POST["username"]."' />
</p>
<p>
<label>Display Name:</label>
<input type='text' name='displayname' class='form-control' value='".$_POST["displayname"]."' />
</p>
<p>
<label>Password:</label>
<input type='password' name='password' class='form-control' value='".$_POST["password"]."' />
</p>
<p>
<label>Confirm:</label>
<input type='password' name='passwordc' class='form-control' value='".$_POST["passwordc"]."' />
</p>
<p>
<label>Email:</label>
<input type='text' name='email' class='form-control' value='".$_POST["email"]."' />
</p>
<!--<p>
<label>Security Code:</label>
<img src='models/captcha.php'>
</p>
<label>Enter Security Code:</label>
<input name='captcha' type='text' class='form-control'>
</p>-->
<p>
<label>&nbsp;<br>
<input type='submit' value='Register' class='btn btn-lg btn-primary btn-block'/>
</p>

</form>";
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

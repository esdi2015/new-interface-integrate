<html>

<head>
    <meta charset="UTF-8" />
    <title>File uploading system</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css" rel="stylesheet">
    <script src="http://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
    <script src="js/jquery.bootstrap.pagination.js" type="text/javascript"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js" type="text/javascript"></script>
</head>

<body>
    <style>
        .btn-file {
            position: relative;
            overflow: hidden;
        }
        
        .btn-file input[type=file] {
            position: absolute;
            top: 0;
            right: 0;
            min-width: 100%;
            min-height: 100%;
            font-size: 100px;
            text-align: right;
            filter: alpha(opacity=0);
            opacity: 0;
            outline: none;
            background: white;
            cursor: inherit;
            display: block;
        }
        
        .form-signin {
            max-width: 330px;
            padding: 15px;
            margin: 0 auto;
        }
        
        .form-signin .form-signin-heading,
        .form-signin .checkbox {
            margin-bottom: 10px;
        }
        
        .form-signin .checkbox {
            font-weight: normal;
        }
        
        .form-signin .form-control {
            position: relative;
            height: auto;
            -webkit-box-sizing: border-box;
            -moz-box-sizing: border-box;
            box-sizing: border-box;
            padding: 10px;
            font-size: 16px;
        }
        
        .form-signin .form-control:focus {
            z-index: 2;
        }
        
        .form-signin input[type="email"] {
            margin-bottom: -1px;
            border-bottom-right-radius: 0;
            border-bottom-left-radius: 0;
        }
        
        .form-signin input[type="password"] {
            margin-bottom: 10px;
            border-top-left-radius: 0;
            border-top-right-radius: 0;
        }
    </style>
    <input type="hidden" id="hidden_page" value="1" />
    <?php 
		require_once("user/models/config.php");
		require_once("config.php");
        //echo "<div style='text-align: center; font-size: 36px;'><b>SITE IS IN TEST MODE !!!!!</b></div>";
        //echo "<div style='text-align: center; font-size: 24px;'><b>Code updating ....</b></div>";
        //die();
	if (!isUserLoggedIn()){
		echo '<nav class="navbar navbar-default">
		<div class="container-fluid"><div><p class="nav navbar-text">Hello, guest ! <b>Please sign in.</b></p></div>
		</div>
		</nav>
		<div class="container">
		<form class="form-signin" name="login" action="user/login.php" method="post">
		<h2 class="form-signin-heading" style="text-align: center;"><span class="glyphicon glyphicon-user"></span></h2>
		<label for="inputEmail" class="sr-only">Username</label>
		<input type="text" id="username" name="username" class="form-control" placeholder="Your username">
		<label for="inputPassword" class="sr-only">Password</label>
		<input type="password" id="password" name="password" class="form-control" placeholder="Your password">
		<button class="btn btn-lg btn-primary btn-block" type="submit">Sign in</button>
		</form>
		</div>';
		die();
	}
	echo '<nav class="navbar navbar-default">
          <div class="container-fluid">
          <div>
          <p class="nav navbar-text">
          You are signed in as <b>'.$loggedInUser->username;
          if ($loggedInUser->checkPermission(array(2))){
         	echo '(<a href="user/index_admin.php" class = "navbar-link">Administration panel</a>)';
          }
    echo '</b>
          </p>
          <p class="nav navbar-text">
          <a href="javascript:changePage(1, null, null)" style="text-decoration: none;" class="navbar-link"><span class="glyphicon glyphicon-list"></span> My History (<i id="history_count"></i>)</a>
          </p>
          <p class="nav navbar-text">
          <a href="javascript:changePage(2, null, null)" style="text-decoration: none;" class="navbar-link"><span class="glyphicon glyphicon-upload"></span> New Upload</a>
          </p>
          </div>
          <div>
          <p class="nav navbar-text navbar-right" style="padding-right: 25px;">
          <b><a href="user/logout.php" class = "navbar-link"><span class="glyphicon glyphicon-log-out"></span> Sign out</a></b>
          </p>
          </div>
          </div>
          </nav>
          <div class="container" id="result_div"></div>
          <div id="loadingDiv" class="container text-center"><img src="images/loading.gif" width="72" height="72" /></div>';
		?>
        <script>
            var showAll=false;
        	function setCurrentPage(page){
        		$('#hidden_page').val(page);
        	}
        	function getCurrentPage(){
        		return $('#hidden_page').val();
        	}
            function getHistoryCount() {
                var form_data = new FormData();
                form_data.append('user_id', <?php echo $loggedInUser->user_id; ?>);
                form_data.append('get_count', 'get');
                $.ajax({
                    url: 'get.php',
                    dataType: 'json',
                    cache: false,
                    contentType: false,
                    processData: false,
                    data: form_data,
                    type: 'post',
                    success: function(response) {
                        $('#history_count').html(response.count);
                    }
                });
            }
            getHistoryCount();

            function changePage(page, xdata, xpage) {
                if(page == 1) {
                	var qdata = "";
                	if(xpage != null){
                		qdata = "page="+xpage;
                	}
                    $.ajax({
                        url: 'user/dashboard_history.php',
                        cache: false,
                        contentType: false,
                        processData: false,
                        data: qdata,
                        type: 'get',
                        success: function(response) {
                            $('#result_div').html(response);
                        }
                    });
                } else if(page == 2) {
                    $.ajax({
                        url: 'user/dashboard_upload.php',
                        cache: false,
                        contentType: false,
                        processData: false,
                        type: 'get',
                        success: function(response) {
                            $('#result_div').html(response);
                        }
                    });
                } else if(page == 3) {
                    $expandedPanel = $('#hrow'+xdata);
                    if($expandedPanel.attr('aria-expanded') == "true"){
                        $('#hrow'+xdata).html('<img src="images/loading.gif" width="72" height="72" />');
                        $.ajax({
                            url: 'user/dashboard_details.php',
                            cache: false,
                            contentType: false,
                            processData: false,
                            data: 'parent_id='+xdata+'&page='+xpage,
                            type: 'get',
                            success: function(response) {
                                $('#hrow'+xdata).html(response);
                            }
                        });
                    }
                }
            }

            changePage(1, null);
        </script>
</body>
</html>
<?php
ob_start();
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
    <script src="../js/jquery.bootstrap.pagination.js" type="text/javascript">
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
<?php
require_once("models/config.php");
if (!securePage($_SERVER['PHP_SELF'])) {
//    echo "!securePage";
    die();
}
if(isUserLoggedIn()) {
if ($loggedInUser->checkPermission(array(1,2,3))){
?>
  <input type="hidden" id="hidden_page" value="1" />
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
                <div id="result_div">
                </div>
            </div>
            </div>
          </div>
        </div>
    </div>
    <?php
    } else {
        ?>
        <div class="container">
            <div id="wrapper">
                <div id="sidebar-wrapper">
                </div>
                <div id="page-content-wrapper">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-lg-12">
                                <p>The user does not have any permissions. Please contact the administrator</p>
                                <p>
                                    <a href='logout.php'>
                                        Logout
                                    </a>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?
    }
    }else{
    header("Location: /");
    exit;
    ?>

    <?php
    }
    ?>
        <script>
          var showAll = true;
          $('#hidden_uid').val('<?php if(isset($_GET["uid"])){echo $_GET["uid"];} ?>');
          var currentUserId = getCurrentUser();
          function setCurrentPage(page){
            $('#hidden_page').val(page);
          }
          function getCurrentPage(){
            return $('#hidden_page').val();
          }
          function getCurrentUser(){
            return $('#hidden_uid').val();
          }

          function getHistoryCount() {
                var form_data = new FormData();
                form_data.append('get_count', 'get');
                $.ajax({
                    url: '<?php echo $websiteUrl; ?>get.php',
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
                    var qdata = "user_id=admin";
                    if(xpage != null){
                      qdata += "&page="+xpage;
                    }
                    $.ajax({
                        url: '<?php echo $websiteUrl; ?>user/dashboard_history.php',
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
                        url: '<?php echo $websiteUrl; ?>user/dashboard_upload.php',
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
                    $('#hrow'+xdata).html('<img src="../images/loading.gif" width="72" height="72" />');
                    $.ajax({
                        url: '<?php echo $websiteUrl; ?>user/dashboard_details.php',
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

            changePage(1, null, 1);
        </script>
  </body> 
</html>
<?php
ob_flush();
?>
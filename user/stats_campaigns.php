<?php
require_once("models/config.php");


function fetchCampaignsStats()
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
		c.status
		FROM uc_campaigns c, uc_accounts a
		WHERE c.account_id = a.id");

    $stmt->execute();
    $stmt->bind_result($id, $acc_name, $source_id, $source_alias, $title, $post_url, $status);
    while ($stmt->fetch()){
        $row[] = array('id' => $id, 'acc_name' => $acc_name, 'source_id' => $source_id,
            'source_alias' => $source_alias, 'title' => $title, 'post_url' => $post_url, 'campaign_status' => $status);
    }
    $stmt->close();
    return ($row);
}


function fetchSingleCampaignStats()
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
		c.status
		FROM uc_campaigns c, uc_accounts a
		WHERE c.account_id = a.id");

    $stmt->execute();
    $stmt->bind_result($id, $acc_name, $source_id, $source_alias, $title, $post_url, $status);
    while ($stmt->fetch()){
        $row[] = array('id' => $id, 'acc_name' => $acc_name, 'source_id' => $source_id,
            'source_alias' => $source_alias, 'title' => $title, 'post_url' => $post_url, 'campaign_status' => $status);
    }
    $stmt->close();
    return ($row);
}



/*

SELECT
    c.id,
    a.title as acc_name,
    c.source_id,
    c.source_alias,
    c.title,
    SUM(uf.sent),
    c.post_url,
    c.status
FROM uc_campaigns c
JOIN uc_accounts a ON c.account_id = a.id
LEFT JOIN csv_uploaded_files uf ON c.id = uf.campaign_id
WHERE uf.campaign_id IS NOT NULL
GROUP BY uf.campaign_id
ORDER BY c.id DESC

=======================

SELECT
    c.id,
    a.title as acc_name,
    c.source_id,
    c.source_alias,
    c.title,
    SUM(uf.sent),
    -- uf.sent,
    SUM(lsp.passCount) AS pass_count,
    SUM(ls.errCount) AS errors_count,
    c.post_url,
    c.status
FROM uc_campaigns c
JOIN uc_accounts a ON c.account_id = a.id
LEFT JOIN csv_uploaded_files uf ON c.id = uf.campaign_id
LEFT JOIN (
    SELECT COUNT(1) AS errCount, file_id
    FROM csv_status_leads ls
    WHERE ls.status = 'Rejected'
    GROUP BY file_id
) AS ls ON ls.file_id = uf.id
LEFT JOIN (
    SELECT COUNT(1) AS passCount, file_id
    FROM csv_status_leads ls
    WHERE ls.status = 'Accepted'
    GROUP BY file_id
) AS lsp ON lsp.file_id = uf.id
WHERE uf.campaign_id IS NOT NULL
GROUP BY uf.campaign_id
ORDER BY c.id DESC
-- ORDER BY uf.id DESC


*/



if (!securePage($_SERVER['PHP_SELF'])){die();}
if(isUserLoggedIn()) {
if ($loggedInUser->checkPermission(array(2,3))){
$campaignData = fetchCampaignsStats(); //Fetch information for all Campaigns

//print_r($campaignData);

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
                    <div class="table-responsive result_content">
                        <table id="records_table" class="table table-hover table-condensed table-bordered" style="table-layout:auto;">
                            <tr>
                                <?php if (!isset($_GET["campaign_id"])){ ?>
                                    <th>Campaign</th>
                                    <th>Goal</th>
                                    <th>Sent</th>
                                    <th>Accepted</th>
                                    <th>Rejected</th>
                                    <th>Account</th>
                                    <th>Source ID</th>
                                    <th>Source Alias</th>
                                    <th>Status</th>
                                <?php } ?>
                                <?php if (isset($_GET["campaign_id"])){ ?>
                                    <th>Filename</th>
                                    <th>Username</th>
                                    <th>Sent</th>
                                    <th>Accepted</th>
                                    <th>Rejected</th>
                                    <th>Account</th>
                                    <th>Source ID</th>
                                    <th>Source Alias</th>
                                    <th>Uploaded at</th>
                                <?php } ?>
                            </tr>
                        </table>
                    </div>
                    <p class="pagination_bottom">
                        <ul class="pagination bootpag">
                        </ul>
                    </p>
                </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <?php
    }} else {
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
          </script>

          <?php if (!isset($_GET["campaign_id"])){ ?>
          <script type="text/javascript">
              function create_table(page, count) {
                  var form_data2 = new FormData();
                  <?php
                  if ($IS_CAMPAIGN_MANAGER == true && $IS_SUPER_ADMIN == false && $IS_ADMIN == false) {echo "showAll = false;";}
                  ?>
                  if(!showAll){
                      form_data2.append('user_id', <?php if(isset($_GET["uid"])){echo $_GET["uid"];}else{echo $loggedInUser->user_id;}?>);
                  }
                  if(page != 0) {
                      form_data2.append('page', page);
                  }
                  if(count != 0) {
                      form_data2.append('count', count);
                  }
                  //console.log(showAll);
                  $.ajax({
                      url: '<?php echo $websiteUrl; ?>get_stats.php<?php if(isset($_GET["campaign_id"])){echo "?campaign_id=".$_GET["campaign_id"];} ?>',
                      dataType: 'json',
                      cache: false,
                      contentType: false,
                      processData: false,
                      data: form_data2,
                      type: 'post',
                      complete: function() {
                          $('#loadingDiv')
                              .hide();
                      },
                      success: function(response) {
                          var headHtml = '<tr>' +
                              '<th>Campaign</th>' +
                              '<th>Goal</th>' +
                              '<th>Sent</th>' +
                              '<th>Accepted</th>' +
                              '<th>Rejected</th>' +
                              '<th>Account</th>' +
                              '<th>Source ID</th>' +
                              '<th>Source Alias</th>' +
                              '<th>Status</th>' +
                          '</tr>';


                          $('#records_table').html(headHtml);

                          var trHTML = '';
                          $.each(response, function(i, item) {
                              if(item.errors_count > 0){
                                  trHTML += '<tr class="danger">';
                              }else{
                                  trHTML += '<tr class="success">';
                              }
                              trHTML += '<td><a href="/user/stats_campaigns.php?campaign_id=' + item.id + '" target="_blank">' + item.campaign_title + '</a></td>';
                              trHTML += '<td>' + item.leads_goal + '</td>';
                              trHTML += '<td>' + item.sent_count + '</td>';
                              trHTML += '<td>' + item.pass_count + '</td>';
                              trHTML += '<td>' + item.errors_count + '</td>';
                              trHTML += '<td>' + item.account_title + '</td>';
                              trHTML += '<td>' + item.source_id + '</td>';
                              trHTML += '<td>' + item.source_alias + '</td>';
                              trHTML += '<td>' + item.campaign_status + '</td>';

                              trHTML += '</tr>';

                              trHTML += '<tr>';
                              trHTML += '<td colspan="7" class="hiddenRow "><div class="accordian-body collapse" id="hrow'+item.id+'">';
                              trHTML += '</div></td>';
                              trHTML += '</tr>';

                              trHTML += '</tr>';
                          });
                          $('#records_table')
                              .append(trHTML);
                          $('#loadingDiv')
                              .hide();
                          $('#header_text')
                              .html("Results");
                          $('#result_div')
                              .show();
                      }
                  });
              }

              var tempPage = <?php if(isset($_GET['page'])){ echo $_GET['page']; } else { echo "1"; } ?>;
              create_table(tempPage, 0);
              var form_data = new FormData();
              if(!showAll){
                  form_data.append('user_id', <?php if(isset($_GET["uid"])){echo $_GET["uid"];}else{echo $loggedInUser->user_id;}?>);
                  form_data.append('get_count', 'get');
              }else{
                  form_data.append('user_id', <?php if(isset($_GET["uid"])){echo $_GET["uid"];}else{echo $loggedInUser->user_id;}?>);
                  form_data.append('get_count', 'get');
              }
              $.ajax({
                  url: '<?php echo $websiteUrl; ?>get_stats.php',
                  dataType: 'json',
                  cache: false,
                  contentType: false,
                  processData: false,
                  data: form_data,
                  type: 'post',
                  success: function(response) {
                      $('.pagination_top,.pagination_bottom')
                          .bootpag({
                              total: response.total,
                              page: tempPage,
                              leaps: true,
                              maxVisible: 10,
                              firstLastUse: true,
                              first: '←',
                              last: '→',
                              wrapClass: 'pagination pagination-sm',
                              activeClass: 'active',
                              disabledClass: 'disabled',
                              nextClass: 'next',
                              prevClass: 'prev',
                              lastClass: 'last',
                              firstClass: 'first'
                          })
                          .on("page", function(event, num) {
                              create_table(num);
                              setCurrentPage(num);
                          });
                  }
              });
          </script>
          <?php } ?>

          <?php if (isset($_GET["campaign_id"])){ ?>
              <script type="text/javascript">
                  function create_table(page, count) {
                      var form_data2 = new FormData();
                      <?php
                      if ($IS_CAMPAIGN_MANAGER == true && $IS_SUPER_ADMIN == false && $IS_ADMIN == false) {echo "showAll = false;";}
                      ?>
                      if(!showAll){
                          form_data2.append('user_id', <?php if(isset($_GET["uid"])){echo $_GET["uid"];}else{echo $loggedInUser->user_id;}?>);
                      }
                      if(page != 0) {
                          form_data2.append('page', page);
                      }
                      if(count != 0) {
                          form_data2.append('count', count);
                      }
                      //console.log(showAll);
                      $.ajax({
                          url: '<?php echo $websiteUrl; ?>get_stats.php<?php if(isset($_GET["campaign_id"])){echo "?campaign_id=".$_GET["campaign_id"];} ?>',
                          dataType: 'json',
                          cache: false,
                          contentType: false,
                          processData: false,
                          data: form_data2,
                          type: 'post',
                          complete: function() {
                              $('#loadingDiv')
                                  .hide();
                          },
                          success: function(response) {
                              var headHtml = '<tr>' +
                                  '<th>Filename</th>' +
                                  '<th>Username</th>' +
                                  '<th>Sent</th>' +
                                  '<th>Accepted</th>' +
                                  '<th>Rejected</th>' +
                                  '<th>Account</th>' +
                                  '<th>Source ID</th>' +
                                  '<th>Source Alias</th>' +
                                  '<th>Uploaded at</th>' +
                                  '</tr>';


                              $('#records_table').html(headHtml);

                              var trHTML = '';
                              $.each(response, function(i, item) {
                                  if(item.errors_count > 0){
                                      trHTML += '<tr class="danger">';
                                  }else{
                                      trHTML += '<tr class="success">';
                                  }
                                  trHTML += '<td>' + item.filename + '</td>';
                                  trHTML += '<td>' + item.user_name + '</td>';
                                  trHTML += '<td>' + item.sent_count + '</td>';
                                  trHTML += '<td>' + item.pass_count + '</td>';
                                  trHTML += '<td>' + item.errors_count + '</td>';
                                  trHTML += '<td>' + item.account_title + '</td>';
                                  trHTML += '<td>' + item.source_id + '</td>';
                                  trHTML += '<td>' + item.source_alias + '</td>';
                                  trHTML += '<td>' + item.file_uploaded_at + '</td>';

                                  trHTML += '</tr>';

                                  trHTML += '<tr>';
                                  trHTML += '<td colspan="7" class="hiddenRow "><div class="accordian-body collapse" id="hrow'+item.id+'">';
                                  trHTML += '</div></td>';
                                  trHTML += '</tr>';

                                  trHTML += '</tr>';
                              });
                              $('#records_table')
                                  .append(trHTML);
                              $('#loadingDiv')
                                  .hide();
                              $('#header_text')
                                  .html("Results");
                              $('#result_div')
                                  .show();
                          }
                      });
                  }

                  var tempPage = <?php if(isset($_GET['page'])){ echo $_GET['page']; } else { echo "1"; } ?>;
                  create_table(tempPage, 0);
                  var form_data = new FormData();
                  if(!showAll){
                      form_data.append('user_id', <?php if(isset($_GET["uid"])){echo $_GET["uid"];}else{echo $loggedInUser->user_id;}?>);
                      form_data.append('get_count', 'get');
                  }else{
                      form_data.append('user_id', <?php if(isset($_GET["uid"])){echo $_GET["uid"];}else{echo $loggedInUser->user_id;}?>);
                      form_data.append('get_count', 'get');
                  }
//                  $.ajax({
//                      url: '<?php //echo $websiteUrl; ?>//get_stats.php',
//                      dataType: 'json',
//                      cache: false,
//                      contentType: false,
//                      processData: false,
//                      data: form_data,
//                      type: 'post',
//                      success: function(response) {
//                          $('.pagination_top,.pagination_bottom')
//                              .bootpag({
//                                  total: response.total,
//                                  page: tempPage,
//                                  leaps: true,
//                                  maxVisible: 10,
//                                  firstLastUse: true,
//                                  first: '←',
//                                  last: '→',
//                                  wrapClass: 'pagination pagination-sm',
//                                  activeClass: 'active',
//                                  disabledClass: 'disabled',
//                                  nextClass: 'next',
//                                  prevClass: 'prev',
//                                  lastClass: 'last',
//                                  firstClass: 'first'
//                              })
//                              .on("page", function(event, num) {
//                                  create_table(num);
//                                  setCurrentPage(num);
//                              });
//                      }
//                  });
              </script>
          <?php } ?>


  </body> 
</html>
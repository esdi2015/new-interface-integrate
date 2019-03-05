<?php
require_once("models/config.php");
if (!securePage($_SERVER['PHP_SELF'])) {
    die();
}
if (isUserLoggedIn()) {
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
    <link href="//maxcdn.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.min.css" rel="stylesheet">
    <script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
    <link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">

    <link href="jtable.2.4.0/themes/lightcolor/blue/jtable.css" rel="stylesheet" type="text/css"/>
    <script src="jtable.2.4.0/jquery.jtable.js" type="text/javascript"></script>
    <style>
        .scroll-content {
            overflow-y: auto;
            width: 100%;
        }

        div.jtable-main-container {
            height: 100%;
        }
    </style>
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
                    <div id="result_div">
                        <input type="hidden" size="100" id="jtable_sorting_info">
                        <div id="CampaignsTableContainer"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
}
} else {
    ?>
    <div class="container">
        <form class="form-signin" name="login" action="login.php" method="post">
            <h2 class="form-signin-heading">
                Please sign in
            </h2>
            <label for="inputEmail" class="sr-only">
                Username
            </label>
            <input type="text" id="username" name="username" class="form-control" placeholder="Your username"
                   required="" autofocus="">
            <label for="inputPassword" class="sr-only">
                Password
            </label>
            <input type="password" id="password" name="password" class="form-control" placeholder="Your password"
                   required="">
            <button class="btn btn-lg btn-primary btn-block" type="submit">
                Sign in
            </button>
        </form>
    </div>
    <?php
}
?>
<script type="text/javascript">

    $(document).ready(function () {

        //Prepare jTable
        $('#CampaignsTableContainer').jtable({
            title: 'Leads statuses',
            paging: true,
            pageSize: 50,
            sorting: true,
            defaultSorting: 'id DESC',
            columnResizable: true,
            selecting: true, //Enable selecting
            multiselect: true, //Allow multiple selecting
            selectingCheckboxes: true, //Show checkboxes on first column
            actions: {
                listAction: 'leads_status_actions.php?action=list',
                deleteAction: 'leads_status_actions.php?action=delete'
            },
            toolbar: {
                items: [{
                    Tooltip: 'Click here to download whole leads list',
                    //icon: '/images/paginate.gif',
                    text: 'Download whole list',
                    click: function () {
                        window.location = 'leads_status_actions.php?action=download';
                        e.preventDefault();
                    }
                },
                    {
                        Tooltip: 'Click here to download filtered leads list',
                        //icon: '/images/paginate.gif',
                        text: 'Download filtered list',
                        click: function () {
                            var query = $('#jtable_sorting_info').val();
                            window.location = 'leads_status_actions.php?action=downloadfiltered&'+query;
                            e.preventDefault();
                        }
                    }
                    ,{
                        Tooltip: 'Click here to delete selected rows',
                        //icon: '/images/paginate.gif',
                        text: 'Delete selected rows',
                        click: function(){
                            var $selectedRows = $('#CampaignsTableContainer').jtable('selectedRows');
                            $('#CampaignsTableContainer').jtable('deleteRows', $selectedRows);
                        }
                    }]
            },
            fields: {
                id: {
                    key: true,
                    create: false,
                    edit: false,
                    list: false
                },
                lead_id:{title: "GUID", width: 'auto', list: true, isDropDown: false, dropDownUrl: 'leads_status_actions.php?action=dropdown&name=lead_id'},
                source_id:{title: "Source ID", width: 'auto', list: true, isDropDown: false, dropDownUrl: 'leads_status_actions.php?action=dropdown&name=source_id'},
                source_alias:{title: "Source Alias", width: 'auto', list: true, isDropDown: false, dropDownUrl: 'leads_status_actions.php?action=dropdown&name=source_alias'},
                email: {title: "Email", width: 'auto', list: true, isDropDown: false, dropDownUrl: 'leads_status_actions.php?action=dropdown&name=email'},
                status: {title: "Status", width: 'auto', list: true, isDropDown: false, dropDownUrl: 'leads_status_actions.php?action=dropdown&name=status'},
                user_id: {title: "User", width: 'auto', isDropDown: true, isId: true, dropDownUrl: 'leads_status_actions.php?action=dropdown&name=user_id&isid=true'},
                filename: {title: "Filename", width: 'auto', isDropDown: false, dropDownUrl: 'leads_status_actions.php?action=dropdown&name=filename'},
                reason: {title: "Reason", width: 'auto', isDropDown: false, dropDownUrl: 'leads_status_actions.php?action=dropdown&name=reason'},
                uploaded_at: {title: "Updated at", width: 'auto', isDropDown: false, dropDownUrl: 'leads_status_actions.php?action=dropdown&name=uploaded_at'}
                
            },
            selectionChanged: function () {
                //Get all selected rows
                var $selectedRows = $('#CampaignsTableContainer').jtable('selectedRows');
 
                $('#SelectedRowList').empty();
                //Show selected rows
                $selectedRows.each(function () {
                    var record = $(this).data('record');
                    $('#SelectedRowList').html($selectedRows.length);
                });
            }
        });

        $('.jtable').wrap('<div class="jtable-main-container scroll-content" />');


        //Load person list from server
        $('#CampaignsTableContainer').jtable('load');

    });

</script>
<iframe id="download_zip" name="download_zip" width="0" height="0" scrolling="no" frameborder="0"></iframe>
</body>
</html>
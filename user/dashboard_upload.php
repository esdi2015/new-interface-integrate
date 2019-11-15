<?php
	require_once("models/config.php");
	require_once("../config.php");
//$loggedInUser = null;
if ($loggedInUser->checkPermission(array(2))) {
    $accounts = getAllAccounts();
} else {
    $accounts = getAllAccounts();
    $user_accounts = getUserAttachedAccounts($loggedInUser->user_id);
    $user_accounts_ids = array();
    foreach($user_accounts as $ua) {
        $user_accounts_ids[] = $ua['obj_id'];
    }
    foreach($accounts as $k=>$acc) {
        if (!in_array($acc['id'], $user_accounts_ids)) {
            unset($accounts[$k]);
        }
    }
}
?>
<div class="container kv-main" id="upload_file_div">
    <div class="page-header">
        <h1 id="header_text">Please configure your upload</h1>
    </div>
    <div id="page_result"></div>
    <form id="file_submit_form" class="form-horizontal" role="form">
        <div class="form-group">
            <div class="col-sm-12">
                <label>Account:</label>
                <select id='account_select' name='account_select' class='form-control'>
                    <option value='0'>- Not set -</option>
                    <?php
                        foreach ($accounts as $acc){
                            echo "<option value='".$acc['id']."'>".$acc['title']."</option>";
                        }
                    ?>
                </select>
            </div>
        </div>
        <div class="form-group">
            <div class="col-sm-8">
                <label>Campaign:</label>
                <select id='campaign_select' name='campaign_select' class='form-control' data-live-search="true">
                    <option value='0'>- Not set -</option>
                </select>
            </div>
            <div class="col-sm-4">
                <label>Campaign filter (by any symbols):</label>
                <input id="campaign_filter" class="form-control" type="text">
            </div>
        </div>
        <div class="form-group">
            <div class="col-sm-12">
                <label>File to upload:</label>
                <input id="file_to_upload" class="form-control" type="file">
            </div>
        </div>
        <div class="form-group">
            <div class="col-sm-8">
            </div>
            <div class="col-sm-4 text-right">
                <input id="file_to_upload" class="btn btn-success" type="submit" value="Upload!">
            </div>
        </div>
    </form>
</div>
<script type="text/javascript">
    $(function() {
        $('#loadingDiv').hide();
        var select_option = null;
        var not_found = false;

        $("#campaign_filter").on("keyup", function() {
            var value = $(this).val().toLowerCase();

            if (not_found == true) {
                $('#campaign_select').html(select_option);
            }
            $("#campaign_select option").filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) !== -1)
            });

            if ($('#campaign_select option:not([style*="display: none"])').length !== 0) {
                select_option = $("#campaign_select").html();
            } else {
                $('#campaign_select').html("<option value='-1'>- Not found -</option>");
                not_found = true;
            }
            $('#campaign_select option:not([style*="display: none"]):first').prop('selected', true);
        });

        $('#account_select').on('change', function(e){
            $("#campaign_filter").val('');
            var form_data = new FormData();
            form_data.append('account_id', this.value);
            form_data.append('get_account_campaigns', 'get');
            $.ajax({
                url: 'get.php',
                dataType: 'json',
                cache: false,
                contentType: false,
                processData: false,
                data: form_data,
                type: 'post',
                beforeSend: function() {
                    $('#campaign_select').prop('disabled', true);
                },
                success: function(response) {
                    if (response != null) {
                        var s = "";
                        for(var r in response) {
                            s = s + "<option value='" + response[r].id + "'>" + response[r].title + "</option>";
                        }
                    } else {
                        s = "<option value='0'>- Not set -</option>";
                    }
                    $('#campaign_select').html(s);
                    select_option = $("#campaign_select").html();
                },
                complete: function() {
                    $('#campaign_select').prop('disabled', false);
                }
            });
            e.preventDefault();
        });

        $('#file_submit_form').on('submit', function(e){
            var file_data = $('#file_to_upload').prop('files')[0];
            var campaign_id = $('#campaign_select').val();
            var error_msg = [];

            if (file_data == undefined) {
                error_msg.push('file is required');
            }
            if (campaign_id <= 0) {
                error_msg.push('campaign is required');
            }

            if (error_msg.length == 0) {
                var form_data = new FormData();
                form_data.append('file', file_data);
                form_data.append('user_id', <?php echo $loggedInUser->user_id; ?>);
                form_data.append('campaign_id', campaign_id);
                $.ajax({
                    url: 'upload.php',
                    dataType: 'json',
                    cache: false,
                    contentType: false,
                    processData: false,
                    data: form_data,
                    type: 'post',
                    beforeSend: function() {
                        $('#file_submit_form').hide();
                        $('#header_text').html("Upload is in progress, please wait...");
                        $('#loadingDiv').show();

                    },
                    complete: function(){
                        $('#loadingDiv').hide();
                        $('#header_text').html("Upload done.");
                        $('#page_result').html('Upload done, <a href="javascript:changePage(1, null)">show results</a> or <a href="javascript:changePage(2, null)">upload more</a>');
                    },
                    success: function(response){

                    }
                });
            } else {
                $('#page_result').html('<p style="color:red;">' + error_msg.join('<br />') + '</p>');
            }

            e.preventDefault();
        });
    });
</script>
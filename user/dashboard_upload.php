<?php
	require_once("models/config.php");
	require_once("../config.php");
    $accounts = getAllAccounts();
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
            <div class="col-sm-12">
                <label>Campaign:</label>
                <select id='campaign_select' name='campaign_select' class='form-control'>
                    <option value='0'>- Not set -</option>
                </select>
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
        $('#account_select').on('change', function(e){
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
                }
            });
            e.preventDefault();
        });

        $('#file_submit_form').on('submit', function(e){
            var file_data = $('#file_to_upload').prop('files')[0];   
            var form_data = new FormData();                  
            form_data.append('file', file_data);   
            form_data.append('user_id', <?php echo $loggedInUser->user_id; ?>);
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
            e.preventDefault();
        });
</script>
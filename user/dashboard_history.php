<?php
	require_once("models/config.php");
	require_once("../config.php");
?>
<div class="table-responsive result_content">
    <table id="records_table" class="table table-hover table-condensed table-bordered" style="table-layout:auto;">
        <tr>
            <th></th>
<!--            <th>ID</th>-->
<!--            <th>Uploaded at</th>-->
<!--            <th>File name</th>-->
<!--            <th>Errors</th>-->
<!--            <th>Download</th>-->
        </tr>
    </table>
</div>
<p class="pagination_bottom">
    <ul class="pagination bootpag">
    </ul>
</p>
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
            url: '<?php echo $websiteUrl; ?>get.php',
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
            	var headHtml = '<tr><th>ID</th><th>Uploaded at</th><th>File name</th><th>IP</th><th>Lead goal</th><th>Sent</th><th>Accepted</th><th>Rejected</th>';
                
                if(showAll){
                	headHtml += '<th>User</th>';
                }
                headHtml += '<th>Download</th></tr>';
                
				$('#records_table').html(headHtml);

                var trHTML = '';
                $.each(response, function(i, item) {
                    if(item.errors_count > 0){
                        trHTML += '<tr class="danger">';
                    }else{
                        trHTML += '<tr class="success">';
                    }
                    trHTML += '<td>' + item.source_alias + '</td>';
                    trHTML += '<td>' + item.uploaded_at + '</td>';
                    trHTML += '<td>' + item.filename + '</td>';
                    trHTML += '<td>' + item.ip + '</td>';
                    trHTML += '<td>' + '' + '</td>';
                    trHTML += '<td>' + item.sent_count + '</td>';
                    trHTML += '<td>' + item.pass_count + '</td>';
                    trHTML += '<td>' + item.errors_count + '</td>';
                    if(showAll){
                    	trHTML += '<td>' + item.user_id + '</td>';
                    }
                    if(item.errors_count > 0){
                        // data-toggle="collapse" data-target="#hrow'+item.id+'" class="accordion-toggle"
                        //javascript:changePage(3, '+item.id+', getCurrentPage())
	                   trHTML += '<td><a href="<?php echo $websiteUrl; ?>download.php?id='+item.id+'">Download file</a></td>';
                    }else{
                        trHTML += '<td></td>';
                    }
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
        form_data.append('get_count_all', 'get');
    }  
    $.ajax({
        url: '<?php echo $websiteUrl; ?>get.php',
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
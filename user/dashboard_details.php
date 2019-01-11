<?php
	require_once("models/config.php");
	require_once("../config.php");
?>
<!--<p>
<input type="button" class="btn btn-success" value="Go back" onclick="changePage(1, null, getCurrentPage())" />
</p>-->
<!--<p class="pagination_top_2 text-center">
    <ul class="pagination pagination-sm bootpag" style="margin: 0px; padding: 0px;">
    </ul>
</p>-->
<div class="table-responsive result_content_<?php echo $_GET['parent_id']; ?>">
    <table id="records_table_<?php echo $_GET['parent_id']; ?>" class="table table-hover table-condensed" style="table-layout:auto;">
        <tr>
            <th>Result</th>
            <th>Error line</th>
        </tr>
    </table>
</div>
<!--<p class="pagination_bottom_2 text-center">
    <ul class="pagination pagination-sm bootpag">
    </ul>
</p>-->
<script type="text/javascript">
    function create_table(page, count) {
        var form_data2 = new FormData();
        form_data2.append('parent_id', <?php echo $_GET['parent_id']; ?>);
        form_data2.append('action', 'get_results');
        if(page != 0) {
            form_data2.append('page', page);
        }
        if(count != 0) {
            form_data2.append('count', count);
        }
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
                $('#records_table_<?php echo $_GET['parent_id']; ?>')
                    .html('<tr><th>Result</th><th>Error line</th><th></th></tr>');

                var trHTML = '';
                $.each(response, function(i, item) {
                    xmlDoc = $.parseXML(item.result);
                    $xml = $(xmlDoc);
                    $xml_errors = $xml.find("error");
                    $xml_status = $xml.find("success");
                    
                    if($xml_status.text() == '1'){
                        trHTML += '<tr class="success">';
                    }else{
                        trHTML += '<tr class="danger">';
                    }
                    var arr_errors = [];
                    $xml_errors.each(function(j) {arr_errors.push($(this).text());});
                    trHTML += '<td>';
                    if($xml_status.text() == '1'){
                        trHTML += 'No errors';
                    }else{
                        trHTML += arr_errors.join(',');
                    }
                    trHTML += '</td>';
                    trHTML += '<td>' + item.error_line + '</td>';
                    trHTML += '</tr>';
                    trHTML += '</tr>';
                });
                $('#records_table_<?php echo $_GET['parent_id']; ?>')
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


    create_table(0, 0);
    var form_data = new FormData();
    form_data.append('parent_id', <?php echo $_GET['parent_id']; ?>);
    form_data.append('get_count_errors', 'get');
    $.ajax({
        url: '<?php echo $websiteUrl; ?>get.php',
        dataType: 'json',
        cache: false,
        contentType: false,
        processData: false,
        data: form_data,
        type: 'post',
        success: function(response) {
            $('.pagination_top_2,.pagination_bottom_2')
                .bootpag({
                    total: response.total,
                    page: 1,
                    leaps: true,
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
                });
        }
    });
</script>
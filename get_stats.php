<?php
require_once('user/models/db-settings.php');
require_once('config.php');
require_once('user/models/funcs.php');

if(!isset($_POST['action'])){
    $action = "";
}else{
    $action = $_POST['action'];
}


if (isset($_POST['page'])) {
    $page_number = $_POST['page'] - 1;
    $offset      = $records_limit * $page_number;
} else if (isset($_GET['page'])) {
    $page_number = $_GET['page'] - 1;
    $offset      = $records_limit * $page_number;
} else {
    $page_number = 0;
    $offset      = 0;
}

$result = array();

if (isset($_POST['get_count'])) {

    $sql = $mysqli->prepare("SELECT f.id, f.user_id, f.uploaded_at, f.filename, f.ip,
                                       uc.source_alias,
                                       uc.title,
                                       uc.source_id,
                                       uc.status as campaign_status,
                                       uc.leads_goal
                                FROM uc_campaigns uc
                                LEFT JOIN csv_uploaded_files f ON uc.id = f.campaign_id");

    $sql->execute();
    $sql->store_result();
    $num_returns = $sql->num_rows;
    $sql->close();

    $pages_count = ceil($num_returns / $records_limit);

    echo json_encode(array(
        'total' => $pages_count,
        'count' => $num_returns
    ));
    die();
}

if (isset($_GET['campaign_id'])) {

    $campaign_id = (int)$_GET['campaign_id'];

    $sql = $mysqli->prepare("SELECT uc.id, -- f.user_id, f.uploaded_at, f.filename, f.ip,
                                   COALESCE(SUM(f.sent), '') AS sent_count,
                                   COALESCE(SUM(lsp.passCount), '') AS pass_count,
                                   COALESCE(SUM(ls.errCount), '') AS errors_count,
                                   -- f.errors_count,
                                   uc.source_alias,
                                   f.filename,
                                   COALESCE(ua.title, '') as acc_title,
                                   uc.source_id,
                                   f.uploaded_at as file_uploaded_at,
                                   uu.user_name
                            FROM uc_campaigns uc
                            LEFT JOIN csv_uploaded_files f ON uc.id = f.campaign_id
                            LEFT JOIN uc_accounts ua ON uc.account_id = ua.id
                            LEFT JOIN uc_users uu ON f.user_id = uu.id
                            LEFT JOIN (
                            SELECT COUNT(1) AS errCount, file_id
                                FROM csv_status_leads ls
                                WHERE ls.status = 'Rejected'
                                GROUP BY file_id
                            ) AS ls ON ls.file_id = f.id
                            LEFT JOIN (
                            SELECT COUNT(1) AS passCount, file_id
                                FROM csv_status_leads ls
                                WHERE ls.status = 'Accepted'
                                GROUP BY file_id
                            ) AS lsp ON lsp.file_id = f.id
                            WHERE uc.id = $campaign_id
                            GROUP BY f.id
                            ORDER BY f.id DESC
                            -- LIMIT 0, 20");

    $sql->execute();
    $sql->bind_result($id, $sent_count, $pass_count, $errors_count, $source_alias,
        $filename, $account_title, $source_id, $file_uploaded_at, $user_name);
    $current_count = 0;
    while ($sql->fetch()) {
        $current_count++;
        if (isset($_POST['count'])) {
            if ($current_count == $_POST['count']) {
                break;
            }
        }

        // GET USER INFORMATION
        $user = fetchUserDetailsNew($user_id);
        $row = array(
            'id' => $id,
            'sent_count' => $sent_count,
            'pass_count' => $pass_count,
            'errors_count' => $errors_count,
            'source_alias' => $source_alias,
            'filename' => $filename,
            'account_title' => $account_title,
            'source_id' => $source_id,
            'file_uploaded_at' => $file_uploaded_at,
            'user_name' => (!is_null($user_name)) ? $user_name : ""
        );
        array_push($result, $row);

    }
    $sql->close();
    echo json_encode($result);

    die();
}


$result_all = fetchAllCampaignsStats($offset, $records_limit);
//print_r($result_all);
echo json_encode($result_all);
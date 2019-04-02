<?php
	require_once('../../user/models/db-settings.php');
	require_once('../../config.php');
    require_once('../../user/models/funcs.php');

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        $post_data = file_get_contents('php://input');
//        $post_data = (isset($post_data) && (is_json($post_data) == true)
//            && (strlen($post_data) > 0)) ? $post_data : null;
        //$_return = $post_data;
        date_default_timezone_set('Europe/Kiev');
        $date_now = date('Y-m-d H:i:s');
        $log_file = 'log_status.txt';

        file_put_contents($log_file, "\n=== ".$date_now." ===\n", FILE_APPEND | LOCK_EX);
        file_put_contents($log_file, "\npost_data\n", FILE_APPEND | LOCK_EX);
        file_put_contents($log_file, $post_data, FILE_APPEND | LOCK_EX);
        file_put_contents($log_file, "\nHTTP_RAW_POST_DATA\n", FILE_APPEND | LOCK_EX);
        file_put_contents($log_file, $HTTP_RAW_POST_DATA, FILE_APPEND | LOCK_EX);
        file_put_contents($log_file, "\nPOST_DATA\n", FILE_APPEND | LOCK_EX);
        file_put_contents($log_file, $_POST, FILE_APPEND | LOCK_EX);
        file_put_contents($log_file, "\nGET_DATA\n", FILE_APPEND | LOCK_EX);
        file_put_contents($log_file, $_GET, FILE_APPEND | LOCK_EX);
        file_put_contents($log_file, "\nREQUEST_URI_DATA\n", FILE_APPEND | LOCK_EX);
        file_put_contents($log_file, $_SERVER['REQUEST_URI']."\n\n", FILE_APPEND | LOCK_EX);

//        var_dump($post_data);
//        var_dump($HTTP_RAW_POST_DATA);
//        var_dump($_POST);
//        die();

        $dataLead =  prepareDataToPush($HTTP_RAW_POST_DATA);
        $replaceLead = pushToDatabaseLeadStatus($dataLead, $mysqli);
        $result = array();
        if ($replaceLead == 1) {
            $result['Result'] = "OK";
        } else {
            $result['Result'] = "ERROR";
            $result['Message'] = $replaceLead;
        }
        echo json_encode($result);
    } elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
        echo "accepted POST only";
    }


    function prepareDataToPush($data) {
        $arr = json_decode($data, true);
        return $arr;
    }

    function pushToDatabaseLeadStatus($data, $context){
        $isLeadIdExists = isLeadIdExists($data['id']);

        if ($isLeadIdExists == false) {
            $sql = $context->prepare("INSERT INTO csv_status_leads (lead_id, status, reason, uploaded_at)
                                            VALUES (?, ?, ?, ?)");
            try {
                $sql->bind_param('ssss', $data['id'], $data['status'], $data['reason'],$data['timestamp']);
                $result = $sql->execute();
            } catch (Exception $ex) {
                $result = $ex->getMessage();
            }
            $sql->close();
            return $result;
        } else {
            $sql = $context->prepare("UPDATE csv_status_leads
                                         SET status = ?,
                                             reason = ?,
                                             uploaded_at = ?
                                       WHERE lead_id = ?");
            try {
                $sql->bind_param('ssss', $data['status'], $data['reason'], $data['timestamp'], $data['id']);
                $result = $sql->execute();
            } catch (Exception $ex) {
                $result = $ex->getMessage();
            }
            $sql->close();
            return $result;
        }

    }
?>
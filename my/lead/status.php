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

        $file_id = getFileIdByLeadUid($dataLead['Id'], $mysqli);
        $error_count = getLeadsErrorCountByFileId($file_id, $mysqli);

        if ($dataLead['Status'] == 'Rejected') {
//            updateErrorCounter($file_id, $mysqli, $error_count);
            updateErrorCounter($file_id, $mysqli, 1);
        }

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
        global $log_file;
        $arr = json_decode($data, true);
        file_put_contents($log_file, "\n$arr\n", FILE_APPEND | LOCK_EX);
        file_put_contents($log_file, $arr."\n\n", FILE_APPEND | LOCK_EX);
        foreach ($arr as $a) {
            file_put_contents($log_file, $a."\n", FILE_APPEND | LOCK_EX);
        }
        file_put_contents($log_file, "\n\n", FILE_APPEND | LOCK_EX);
        return $arr;
    }


    function getLeadsErrorCountByFileId($file_id, $context) {
        $error_count = 0;
        $sql = $context->prepare("SELECT COUNT(1) as error_count
                                    FROM csv_status_leads
                                  WHERE
                                  file_id = ?
                                  AND status = 'Rejected'");
        $sql->bind_param("i", $file_id);
        $sql->execute();
        $sql->bind_result($error_count);

        while ($sql->fetch()){
            $row = array('error_count' => $error_count);
        }
        $sql->close();
        return $row['error_count'];
    }

    function updateErrorCounter($file_id, $context, $errors_count=1) {
        $sql = $context->prepare("UPDATE csv_uploaded_files
		                             SET errors_count = errors_count + ?
                                  WHERE
                                  id = ?
                                  LIMIT 1");
        $sql->bind_param("ii", $errors_count, $file_id);
        $result = $sql->execute();
        $sql->close();
        return $result;
    }

    function getFileIdByLeadUid($lead_uid, $context) {
        $file_id = NULL;
        $sql = $context->prepare("SELECT file_id
                                    FROM csv_status_leads
                                  WHERE
                                  lead_id = ?
                                  ORDER BY id DESC
                                  LIMIT 1");
        $sql->bind_param("s", $lead_uid);
        $sql->execute();
        $sql->bind_result($file_id);

        while ($sql->fetch()){
            $row = array('file_id' => $file_id);
        }
        $sql->close();
        return $row['file_id'];
    }


    function getMaxUploadNoLeadUid($lead_uid, $context) {
        $max_upload_no = null;
        $sql = $context->prepare("SELECT upload_no
                                        FROM csv_status_leads
                                      WHERE
                                      lead_id = ?
                                      ORDER BY id DESC
                                      LIMIT 1");
        $sql->bind_param("s", $lead_uid);
        $sql->execute();
        $sql->bind_result($max_upload_no);

        while ($sql->fetch()){
            $row = array('max_upload_no' => $max_upload_no);
        }
        $sql->close();
        return $row['max_upload_no'];
    }


    function pushToDatabaseLeadStatus($data, $context){
        $isLeadIdExists = isLeadIdExists($data['Id']);

        if ($isLeadIdExists == false) {
            $sql = $context->prepare("INSERT INTO csv_status_leads (lead_id, status, reason, uploaded_at)
                                            VALUES (?, ?, ?, ?)");
            try {
                $sql->bind_param('ssss', $data['Id'], $data['Status'], $data['Reason'],$data['Timestamp']);
                $result = $sql->execute();
            } catch (Exception $ex) {
                $result = $ex->getMessage();
            }
            $sql->close();
            return $result;
        } else {
            $max_upload_no = getMaxUploadNoLeadUid($data['Id'], $context);
            var_dump($max_upload_no);
            var_dump($data);
            $sql = $context->prepare("UPDATE csv_status_leads
                                         SET status = ?,
                                             reason = ?,
                                             uploaded_at = ?
                                       WHERE lead_id = ? AND upload_no = ?");
            try {
                $sql->bind_param('ssssi', $data['Status'], $data['Reason'], $data['Timestamp'], $data['Id'], $max_upload_no);
                $result = $sql->execute();
            } catch (Exception $ex) {
                $result = $ex->getMessage();
            }
            $sql->close();
            return $result;
        }

    }
?>
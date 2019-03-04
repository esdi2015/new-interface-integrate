<?php
	require_once('user/models/db-settings.php');
	require_once('config.php');
    require_once('user/models/funcs.php');

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
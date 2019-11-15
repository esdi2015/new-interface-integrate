<?php
require_once('models/db-settings.php');
require_once('../config.php');
require_once('models/config.php');
require_once('models/funcs.php');

try {
    $filtersArray = [];
    $mfilters = [];
    if (isset($_GET['jtFilters']))
        if(!empty($_GET['jtFilters']))
            $mfilters = explode(",", $_GET['jtFilters']);
    if (isset($_GET["jtSorting"]))
        $jtSorting = $_GET["jtSorting"];
    else
        $jtSorting = "id";
    //Getting records (listAction)
    if ($_GET["action"] == "list") {
        // FILTERS
        $filters;
        if(count($mfilters)>0){
            $dateFrom;
            $dateTo;
            foreach ($mfilters as $value) {
                $filter = explode("*", $value);
                if(startsWith($filter[0], 'uploaded_at') == false){
                    if($filter[1] != "nofilter"){
                        if(is_numeric($filter[1])){
                            $filtersArray[] = "$filter[0]=$filter[1]";
                        }else{
                            $filtersArray[] = "$filter[0]='$filter[1]'";
                        }
                    }
                }else if($filter[0] == 'uploadedat_from'){
                    $dateFrom = "$filter[1]";
                }else if($filter[0] == 'uploadedat_to'){
                    $dateTo = "$filter[1]";
                }
            }

            $dateBetween = "uploaded_at BETWEEN '".$dateFrom."' AND '".$dateTo." 23:59:59.999'";
            if(!empty($dateFrom) && !empty($dateTo))
                $filtersArray[] = $dateBetween;
            $filters = implode(" AND ", $filtersArray);
            if(count($filtersArray) > 0)
                $filters = "WHERE ".$filters;
        }
        //Get records from database
        
        
        # $query = "SELECT id, campaignid, email, userid, filename, uploadedat FROM csv_accepted_leads ".$filters." ORDER BY " . $jtSorting . " LIMIT " . $_GET["jtStartIndex"] . "," . $_GET["jtPageSize"];
        if ($IS_SUPER_ADMIN) {
            $query = "SELECT id, lead_id, source_id, source_alias, email, status,
                    user_id, filename, reason, uploaded_at
                    FROM csv_status_leads ".$filters."
                    -- GROUP BY lead_id
                    -- HAVING (MAX(upload_no))
                    ORDER BY " . $jtSorting . " LIMIT " . $_GET["jtStartIndex"] . "," . $_GET["jtPageSize"];
        } elseif ($IS_CAMPAIGN_MANAGER) {
            $query = "SELECT id, lead_id, source_id, source_alias, email, status,
                    user_id, filename, reason, uploaded_at
                    FROM csv_status_leads ".$filters."
                    WHERE user_id = ".$loggedInUser->user_id."
                    -- GROUP BY lead_id
                    -- HAVING (MAX(upload_no))
                    ORDER BY " . $jtSorting . " LIMIT " . $_GET["jtStartIndex"] . "," . $_GET["jtPageSize"];
        } elseif ($IS_ADMIN) {
            $query = "SELECT id, lead_id, source_id, source_alias, email, status,
                    user_id, filename, reason, uploaded_at
                    FROM csv_status_leads ".$filters."
                    WHERE user_id = ".$loggedInUser->user_id."
                    -- GROUP BY lead_id
                    -- HAVING (MAX(upload_no))
                    ORDER BY " . $jtSorting . " LIMIT " . $_GET["jtStartIndex"] . "," . $_GET["jtPageSize"];
        }

//        var_dump($query);
        //var_dump(strstr($query, 'LIMIT', true));
        //Get record count
        $result = $mysqli->prepare(strstr($query, 'LIMIT', true));
        //echo strstr($query, 'LIMIT', true);
        $result->execute();
        $result->store_result();
        $num_returns = $result->num_rows;
        $result->close();

        $recordCount = $num_returns;

        //echo $query;
        $result = $mysqli->prepare($query);
        $result->execute();
        $result->bind_result($id, $lead_id, $source_id, $source_alias, $email, $status, $user_id, $filename,
            $reason, $uploaded_at);
        
        
        //Add all records to an array
        $rows = array();
        while ($result->fetch()) {
            $user = fetchUserDetailsNew($user_id);
            $rows[] = ["id"=>$id, "lead_id"=>$lead_id, "source_id"=>$source_id, "source_alias"=>$source_alias,
                "email"=>$email, "status"=>$status, "user_id"=>$user['user_name'], "filename"=>$filename,
                "reason"=>$reason, "uploaded_at"=>$uploaded_at];
        }
        $result->close();

        //Return result to jTable
        $jTableResult = array();
        $jTableResult['Result'] = "OK";
        $jTableResult['TotalRecordCount'] = $recordCount;
        $jTableResult['Records'] = $rows;
        print json_encode($jTableResult);
    } else if ($_GET["action"] == "dropdown") {
        if(startsWith($_GET['name'], 'uploaded_at') == false){
            //Get records from database
            $result = $mysqli->prepare("SELECT ".$_GET['name']." FROM csv_status_leads GROUP BY ".$_GET['name']);
            $result->execute();
            $result->bind_result($col);
            
            //Add all records to an array
            $rows = array();
            while ($result->fetch()) {
                if($_GET['name'] == "user_id"){
                    $user = fetchUserDetailsNew($col);
                    $rows[] = ["id"=>$col, "value"=>$user['user_name']];
                }else{
//                    $rows[] = ["id"=>fetchUserDetails(),"value"=>$col];
                    $rows[] = ["id"=>$col,"value"=>$col];
                }
            }
            $result->close();

            //Return result
            print json_encode($rows);
        }
    } else if ($_GET["action"] == "delete") {
        //Delete from database
//        $result = $mysqli->prepare("DELETE FROM csv_status_leads WHERE id = " . $_POST["id"]);
//        $result->execute();
//        $result->close();
        //Return result to jTable
        $jTableResult = array();
        $jTableResult['Result'] = "OK";
        print json_encode($jTableResult);
    } else if ($_GET["action"] == "download"){
            // GET ALL ROWS
            $result = $mysqli->prepare("SELECT id, lead_id, source_id, source_alias, email, status,
                    user_id, filename, reason, uploaded_at FROM csv_status_leads");
            $result->execute();
            $result->bind_result($id, $lead_id, $source_id, $source_alias, $email, $status, $user_id, $filename,
                $reason, $uploaded_at);
             
            //Add all records to an array
            $rows = array();
            while ($result->fetch()) {
                $rows[] = [
                    "lead_id"=>$lead_id,
                    "source_id"=>$source_id,
                    "source_alias"=>$source_alias,
                    "email"=>$email,
                    "status"=>$status,
                    "user_id"=>$user_id,
                    "filename"=>'"'.$filename.'"',
                    "reason"=>$reason,
                    "uploaded_at"=>'"'.$uploaded_at.'"'
                ];
            }
            $result->close();

            if(count($rows) > 0){
                $content;
                $content .= implode(",", array_keys($rows[0]))."\n";
                foreach ($rows as $row) {
                    $content .= implode(",", array_values($row))."\n";
                }

                header('Content-Description: File Transfer');
                header('Content-Type: application/csv');
                header('Content-Disposition: attachment; filename=AllLeads_'.date('Y_m_d_H_i_s').'.csv');
                header('Expires: 0');
                header('Cache-Control: must-revalidate');
                header('Pragma: public');
                header('Content-Length: ' . strlen($content));
                ob_clean();
                flush();
                echo $content;
            }
    } else if ($_GET["action"] == "downloadfiltered"){
                // FILTERS
        $filters;
        if(count($mfilters)>0){
            $dateFrom;
            $dateTo;
            foreach ($mfilters as $value) {
                $filter = explode("*", $value);
                if(startsWith($filter[0], 'uploaded_at') == false){
                    if($filter[1] != "nofilter"){
                        if(is_numeric($filter[1])){
                            $filtersArray[] = "$filter[0]=$filter[1]";
                        }else{
                            $filtersArray[] = "$filter[0]='$filter[1]'";
                        }
                    }
                }else if($filter[0] == 'uploadedat_from'){
                    $dateFrom = "$filter[1]";
                }else if($filter[0] == 'uploadedat_to'){
                    $dateTo = "$filter[1]";
                }
            }

            $dateBetween = "uploaded_at BETWEEN '".$dateFrom."' AND '".$dateTo." 23:59:59.999'";
            if(!empty($dateFrom) && !empty($dateTo))
                $filtersArray[] = $dateBetween;
            $filters = implode(" AND ", $filtersArray);
            if(count($filtersArray) > 0)
                $filters = "WHERE ".$filters;
        }

        // GET ALL ROWS
        $query = "SELECT id, lead_id, source_id, source_alias, email, status,
                    user_id, filename, reason, uploaded_at
                    FROM csv_status_leads ".$filters."
                    ORDER BY " . $jtSorting; // . " LIMIT " . $_GET["jtStartIndex"] . "," . $_GET["jtPageSize"];
            $result = $mysqli->prepare($query);
            $result->execute();
            $result->bind_result($id, $lead_id, $source_id, $source_alias, $email, $status, $user_id, $filename,
                $reason, $uploaded_at);
             
            //Add all records to an array
            $rows = array();
            while ($result->fetch()) {
                $rows[] = [
                    "lead_id"=>$lead_id,
                    "source_id"=>$source_id,
                    "source_alias"=>$source_alias,
                    "email"=>$email,
                    "status"=>$status,
                    "user_id"=>$user_id,
                    "filename"=>'"'.$filename.'"',
                    "reason"=>$reason,
                    "uploaded_at"=>'"'.$uploaded_at.'"'
                ];
            }
            $result->close();

            if(count($rows) > 0){
                $content;
                $content .= implode(",", array_keys($rows[0]))."\n";
                foreach ($rows as $row) {
                    $content .= implode(",", array_values($row))."\n";
                }

                header('Content-Description: File Transfer');
                header('Content-Type: application/csv');
                header('Content-Disposition: attachment; filename=FilteredLeads_'.date('Y_m_d_H_i_s').'.csv');
                header('Expires: 0');
                header('Cache-Control: must-revalidate');
                header('Pragma: public');
                header('Content-Length: ' . strlen($content));
                ob_clean();
                flush();
                echo $content;
            }    
    } else if ($_GET["action"] == "downloadselected"){
        require_once('../download.php');
        $delimiter=";";
        $rows = process_send('leads_ids');

        $content;
        $content .= implode(",", array_keys($rows[0]))."\n";
        foreach ($rows as $row) {
            $content .= implode(",", array_values($row))."\n";
        }
        $filename = date('Y_m_d_H_i_s').'.csv';

        header('Content-Description: File Transfer');
        header('Content-Type: application/csv');
        header('Content-Disposition: attachment; filename="SelectedLeads_'.$filename.'";');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . strlen($content));

        ob_clean();
        flush();
        echo $content;
    }
} catch (Exception $ex) {
    //Return error message
    $jTableResult = array();
    $jTableResult['Result'] = "ERROR";
    $jTableResult['Message'] = $ex->getMessage();
    print json_encode($jTableResult);
}

function startsWith($haystack, $needle)
{
     $length = strlen($needle);
     return (substr($haystack, 0, $length) === $needle);
}

?>
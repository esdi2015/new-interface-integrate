<?php
require_once('models/db-settings.php');
require_once('../config.php');
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
                if(startsWith($filter[0], 'uploadedat') == false){    
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

            $dateBetween = "uploadedat BETWEEN '".$dateFrom."' AND '".$dateTo." 23:59:59.999'";
            if(!empty($dateFrom) && !empty($dateTo))
                $filtersArray[] = $dateBetween;
            $filters = implode(" AND ", $filtersArray);
            if(count($filtersArray) > 0)
                $filters = "WHERE ".$filters;
        }
        //Get records from database
        
        
        $query = "SELECT id, campaignid, email, userid, filename, uploadedat FROM csv_accepted_leads ".$filters." ORDER BY " . $jtSorting . " LIMIT " . $_GET["jtStartIndex"] . "," . $_GET["jtPageSize"];

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
        $result->bind_result($id, $campaignid, $email, $userid, $filename, $uploadedat);
        
        
        //Add all records to an array
        $rows = array();
        while ($result->fetch()) {
            $user = fetchUserDetailsNew($userid);
            $rows[] = ["id"=>$id, "campaignid"=>$campaignid, "email"=>$email, "userid"=>$user['user_name'], "filename"=>$filename, "uploadedat"=>$uploadedat];
        }
        $result->close();

        //Return result to jTable
        $jTableResult = array();
        $jTableResult['Result'] = "OK";
        $jTableResult['TotalRecordCount'] = $recordCount;
        $jTableResult['Records'] = $rows;
        print json_encode($jTableResult);
    } else if ($_GET["action"] == "dropdown") {
        if(startsWith($_GET['name'], 'uploadedat') == false){    
            //Get records from database
            $result = $mysqli->prepare("SELECT ".$_GET['name']." FROM csv_accepted_leads GROUP BY ".$_GET['name']);
            $result->execute();
            $result->bind_result($col);
            
            //Add all records to an array
            $rows = array();
            while ($result->fetch()) {
                if($_GET['name'] == "userid"){
                    $user = fetchUserDetailsNew($col);
                    $rows[] = ["id"=>$col, "value"=>$user['user_name']];
                }else{
                   $rows[] = ["id"=>fetchUserDetailsNew(),"value"=>$col]; 
                }
            }
            $result->close();

            //Return result
            print json_encode($rows);
        }
    } else if ($_GET["action"] == "delete") {
        //Delete from database
        $result = $mysqli->prepare("DELETE FROM csv_accepted_leads WHERE id = " . $_POST["id"]);
        $result->execute();
        $result->close();
        //Return result to jTable
        $jTableResult = array();
        $jTableResult['Result'] = "OK";
        print json_encode($jTableResult);
    } else if ($_GET["action"] == "download"){
            // GET ALL ROWS
            $result = $mysqli->prepare("SELECT id, campaignid, email, userid, filename, uploadedat FROM csv_accepted_leads");
            $result->execute();
            $result->bind_result($id, $campaignid, $email, $userid, $filename, $uploadedat);
             
            //Add all records to an array
            $rows = array();
            while ($result->fetch()) {
                $rows[] = [
                "campaignid"=>$campaignid, 
                "email"=>$email, 
                "filename"=>'"'.$filename.'"', 
                "uploadedat"=>'"'.$uploadedat.'"'
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
                header('Content-Disposition: attachment; filename=AcceptedLeads_'.date('Y_m_d H_i_s').'.csv');
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
                if(startsWith($filter[0], 'uploadedat') == false){    
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

            $dateBetween = "uploadedat BETWEEN '".$dateFrom."' AND '".$dateTo." 23:59:59.999'";
            if(!empty($dateFrom) && !empty($dateTo))
                $filtersArray[] = $dateBetween;
            $filters = implode(" AND ", $filtersArray);
            if(count($filtersArray) > 0)
                $filters = "WHERE ".$filters;
        }

        // GET ALL ROWS
        $query = "SELECT id, campaignid, email, userid, filename, uploadedat FROM csv_accepted_leads ".$filters." ORDER BY " . $jtSorting; // . " LIMIT " . $_GET["jtStartIndex"] . "," . $_GET["jtPageSize"];
            $result = $mysqli->prepare($query);
            $result->execute();
            $result->bind_result($id, $campaignid, $email, $userid, $filename, $uploadedat);
             
            //Add all records to an array
            $rows = array();
            while ($result->fetch()) {
                $rows[] = [
                "campaignid"=>$campaignid, 
                "email"=>$email, 
                "filename"=>'"'.$filename.'"', 
                "uploadedat"=>'"'.$uploadedat.'"'
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
                header('Content-Disposition: attachment; filename=AcceptedLeads_'.date('Y_m_d H_i_s').'.csv');
                header('Expires: 0');
                header('Cache-Control: must-revalidate');
                header('Pragma: public');
                header('Content-Length: ' . strlen($content));
                ob_clean();
                flush();
                echo $content;
            }    
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
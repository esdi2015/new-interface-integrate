<?php
require_once('user/models/db-settings.php');
//require_once("user/models/config.php");
require_once('config.php');


if ( 1 == 1 /* isUserLoggedIn()*/ ){
    process_send();
} else{
	echo "You must be signed in to download files!";
}


function process_send() {
    if(isset($_GET['id'])) {
        return send_leads_csv('file_id');
    }
    if(isset($_GET['ids'])) {
        return send_leads_csv('leads_ids', $_GET['ids']);
    }
}


function send_leads_csv($get_leads_type, $leads_ids='') {
    global $mysqli;

    if ($get_leads_type == 'file_id') {
        $error_status = 'Rejected';
        $sql = $mysqli->prepare("SELECT ls.lead_id, ls.source_id, ls.source_alias, ls.email, ls.filename, ls.reason, ls.upload_no,
                                        u.user_name
                                       FROM csv_status_leads ls
                                       LEFT JOIN uc_users u ON (ls.user_id = u.id)
                                      WHERE
                                      ls.file_id=?
                                      AND
                                      ls.status=? ");
        $sql->bind_param("is", $_GET['id'], $error_status);
        $sql->execute();
        $sql->bind_result($lead_id, $source_id, $source_alias, $email, $filename, $reason, $upload_no, $user_name);
//        $sql->store_result();

        $rows = array();
        $rows[] = array('lead_id', 'source_id', 'source_alias', 'email', 'filename', 'reason', 'upload_no', 'user_name');

        while ($sql->fetch()){
            $rows[] = array($lead_id, $source_id, $source_alias, $email, $filename, $reason, $upload_no, $user_name);
        }
        $sql->close();
    } else {
        $sql = $mysqli->prepare("SELECT ls.lead_id, ls.source_id, ls.source_alias, ls.email,
                                        ls.status, ls.uploaded_at,
                                        ls.filename, ls.reason, ls.upload_no,
                                        u.user_name
                                       FROM csv_status_leads ls
                                       LEFT JOIN uc_users u ON (ls.user_id = u.id)
                                      WHERE ls.id in (".$leads_ids.") ");
        $sql->execute();
        $sql->bind_result($lead_id, $source_id, $source_alias, $email, $status, $uploaded_at,
            $filename, $reason, $upload_no, $user_name);
//        $sql->store_result();
        $rows = array();
        while ($sql->fetch()){
            $rows[] = [
                "lead_id"=>$lead_id,
                "source_id"=>$source_id,
                "source_alias"=>$source_alias,
                "email"=>$email,
                "status"=>$status,
                "user_name"=>$user_name,
                "filename"=>'"'.$filename.'"',
                "reason"=>$reason,
                "uploaded_at"=>'"'.$uploaded_at.'"',
                "upload_no"=>'"'.(intval($upload_no)+1).'"'
            ];
        }
        $sql->close();
    }

    if ($get_leads_type == 'file_id') {
        array_to_csv_download_file_id($rows, "Errors_".$filename);
    } else {
        return $rows;
    }

}

function array_to_csv_download_file_id($array, $filename = "export.csv", $delimiter=";") {
    // open raw memory as file so no temp files needed, you might run out of memory though
    $f = fopen('php://memory', 'w');
    // loop over the input array
    foreach ($array as $key=>$line) {
        // generate csv lines from the inner arrays
        fputcsv($f, $line, $delimiter);
    }
    // reset the file pointer to the start of the file
    fseek($f, 0);
    header('Content-Description: File Transfer');
    // tell the browser it's going to be a csv file
    header('Content-Type: application/csv');
    // tell the browser we want to save it instead of displaying it
    header('Content-Disposition: attachment; filename="'.$filename.'";');
    // make php send the generated csv lines to the browser
    fpassthru($f);
}

?>
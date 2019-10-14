<?php
require_once('user/models/db-settings.php');
require_once('config.php');
require_once('user/models/funcs.php');

if(!isset($_POST['action'])){
	$action = "";
}else{
	$action = $_POST['action'];
}

if ($action == "get_results") {
        if (isset($_POST['page'])) {
            $page_number = $_POST['page'] - 1;
            $offset      = $records_limit * $page_number;
        } else {
            $page_number = 0;
            $offset      = 0;
        }
        
        $result = array();
        $sql    = $mysqli->prepare("SELECT * FROM csv_uploaded_files_results
                                    WHERE parent_id='" . $_POST['parent_id'] . "'
                                    AND result LIKE '%<success>0</success>%'
                                    ORDER BY id"); //  DESC LIMIT $offset, $records_limit
        $sql->execute();
        $sql->bind_result($id, $parent_id, $resultxml, $error_line);
        $current_count = 0;
        while ($sql->fetch()) {
            $current_count++;
            if (isset($_POST['count'])) {
                if ($current_count == $_POST['count']) {
                    break;
                }
            }
            $row = array(
                'id' => $id,
                'parent_id' => $parent_id,
                'result' => $resultxml,
                'error_line' => $error_line
            );
            array_push($result, $row);  
        }
        $sql->close();
        echo json_encode($result);
} else {
    if (isset($_POST['get_count_all'])) {
        $sql = $mysqli->prepare("SELECT * FROM csv_uploaded_files");
        //var_dump($sql);
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
    if (isset($_POST['get_count'])) {
        $sql = $mysqli->prepare("SELECT * FROM csv_uploaded_files WHERE user_id='" . $_POST['user_id'] . "'");
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

    if (isset($_POST['get_count_errors'])) {
        $sql = $mysqli->prepare("SELECT * FROM csv_uploaded_files_results WHERE parent_id='" . $_POST['parent_id'] . "'");
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

    if (isset($_POST['get_account_campaigns'])) {
        $account_id = $_POST['account_id'];
        $campaigns = getAccountCampaigns($account_id);
        echo json_encode($campaigns);
        die();
    }
    
    if (isset($_POST['user_id'])) {
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
        $sql    = $mysqli->prepare("SELECT f.id, f.user_id, f.uploaded_at, f.filename, f.ip,
                                           f.errors_count, uc.source_alias
                                    FROM csv_uploaded_files f
                                    LEFT JOIN uc_campaigns uc ON f.campaign_id = uc.id
                                    WHERE f.user_id='" . $_POST['user_id'] . "'
                                    ORDER BY f.uploaded_at DESC
                                    LIMIT $offset, $records_limit");

        //var_dump($sql);
        $sql->execute();
        $sql->bind_result($id, $user_id, $uploaded_at, $filename, $ip, $errors_count, $source_alias);
        $current_count = 0;
        while ($sql->fetch()) {
            $current_count++;
            if (isset($_POST['count'])) {
                if ($current_count == $_POST['count']) {
                    break;
                }
            }
            /*$sql_cerr = $mysqli2->prepare("SELECT * FROM csv_uploaded_files_results WHERE parent_id='" . $id . "' AND result LIKE '%<success>0</success>%'");
        	$sql_cerr->execute();
        	$sql_cerr->store_result();
        	$errors_count = $sql_cerr->num_rows;
        	$sql_cerr->close();*/
            $row = array(
                'id' => $id,
                'user_id' => $user_id,
                'uploaded_at' => $uploaded_at,
                'filename' => $filename,
                'ip' => $ip,
                'errors_count' => $errors_count,
                'source_alias' => $source_alias
            );
            array_push($result, $row);
            
        }
        $sql->close();
        echo json_encode($result);
    }else{
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
//        $sql    = $mysqli->prepare("SELECT id, user_id, uploaded_at, filename, ip, errors_count
//                                    FROM csv_uploaded_files
//                                    ORDER BY uploaded_at
//                                    DESC LIMIT $offset, $records_limit");

        $sql    = $mysqli->prepare("SELECT f.id, f.user_id, f.uploaded_at, f.filename, f.ip,
                                           f.errors_count, uc.source_alias
                                    FROM csv_uploaded_files f
                                    LEFT JOIN uc_campaigns uc ON f.campaign_id = uc.id
                                    ORDER BY f.uploaded_at DESC
                                    LIMIT $offset, $records_limit");

        //var_dump($sql);

        $sql->execute();
        $sql->bind_result($id, $user_id, $uploaded_at, $filename, $ip, $errors_count, $source_alias);
        $current_count = 0;
        while ($sql->fetch()) {
            $current_count++;
            if (isset($_POST['count'])) {
                if ($current_count == $_POST['count']) {
                    break;
                }
            }
            /*$sql_cerr = $mysqli2->prepare("SELECT * FROM csv_uploaded_files_results WHERE parent_id='" . $id . "' AND result LIKE '%<success>0</success>%'");
            $sql_cerr->execute();
            $sql_cerr->store_result();
            $errors_count = $sql_cerr->num_rows;
            $sql_cerr->close();*/

            // GET USER INFORMATION
            $user = fetchUserDetailsNew($user_id);
            $row = array(
                'id' => $id,
                'user_id' => $user['user_name'],
                'uploaded_at' => $uploaded_at,
                'filename' => $filename,
                'ip' => $ip,
                'errors_count' => $errors_count,
                'source_alias' => $source_alias
            );
            array_push($result, $row);
            
        }
        $sql->close();
        echo json_encode($result);
    }
    
    if (isset($_POST['act'])) {
        
        if (isset($_POST['page'])) {
            $page_number = $_POST['page'] - 1;
            $offset      = $records_limit * $page_number;
        } else {
            $page_number = 0;
            $offset      = 0;
        }
        
        
        $result = array();
//        $sql    = $mysqli->prepare("SELECT id, user_id, uploaded_at, filename, ip, errors_count
//                                    FROM csv_uploaded_files
//                                    ORDER BY uploaded_at DESC
//                                    LIMIT $offset, $records_limit");

        $sql    = $mysqli->prepare("SELECT f.id, f.user_id, f.uploaded_at, f.filename, f.ip,
                                           f.errors_count, uc.source_alias
                                    FROM csv_uploaded_files f
                                    LEFT JOIN uc_campaigns uc ON f.campaign_id = uc.id
                                    ORDER BY f.uploaded_at DESC
                                    LIMIT $offset, $records_limit");

        $sql->execute();
        $sql->bind_result($id, $user_id, $uploaded_at, $filename, $ip, $errors_count, $source_alias);
        $current_count = 0;
        while ($sql->fetch()) {
            $current_count++;
            if (isset($_POST['count'])) {
                if ($current_count == $_POST['count']) {
                    break;
                }
            }
            $row = array(
                'id' => $id,
                'user_id' => $user_id,
                'uploaded_at' => $uploaded_at,
                'filename' => $filename,
                'ip' => $ip,
                'errors_count' => $errors_count,
                'source_alias' => $source_alias
            );
            array_push($result, $row);
            
        }
        $sql->close();
        echo json_encode($result);
    }
}

?>
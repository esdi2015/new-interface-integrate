<?php
	require_once('user/models/db-settings.php');
	require_once('config.php');
    require_once('user/models/funcs.php');

    $SOURCE_ID = "AF070B";
    $source_id = "";
    $source_alias = "";
    $integrate_api_live_url = "";

    $leadStatus = "";
    $leadReason = "";

    $siteURL = getSiteURL();
    $callbackString = "callback=".$siteURL."my/lead/status.php";

    if ( 0 < $_FILES['file']['error'] ) {
		echo 'Oops ! Something gone wrong !<br/>';
        echo 'Error: ' . $_FILES['file']['error'] . '<br>';
    } else {
        $campaign_id = $_POST['campaign_id'];
        $campaign = fetchCampaignDetails($campaign_id);
        $source_id = $campaign['source_id'];
        $source_alias = $campaign['source_alias'];
        $integrate_api_live_url = $campaign['post_url'];
        $email_field = $campaign['email_field'];

		$user_id = $_POST['user_id'];
		$uploaded_at_datetime = date('Y-m-d H:i:s');
		// Move temp file
		$destinationFileName = tempnam($upload_path, $upload_file_prefix);	
		$destinationFileNameDownload = $download_path.basename($_FILES['file']['name'], ".csv")."_".$uploaded_at_datetime;	
		$fileExtension = '.' . pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
		$newFileName = $destinationFileName . $fileExtension;
		$newFileNameDownload = $destinationFileNameDownload . $fileExtension;
		if(move_uploaded_file($_FILES['file']['tmp_name'], $destinationFileName)){	
			rename($destinationFileName, $newFileName);
		}else{
			unlink($destinationFileName);
		}
		
		// Get client IP
		if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else {
			$ip = $_SERVER['REMOTE_ADDR'];
		}

		// Get & Parse CSV file
		// $url = $integrate_api_test_url;
        $url = $integrate_api_live_url."?".$callbackString;
        //var_dump($url); die();
		$encfile = remove_utf8_bom(file($newFileName));
		$csv = array_map('str_getcsv', $encfile);
//        var_dump($csv);
		// Insert master
		$array_items = array();


		$pushData = array("user_id" => $user_id,
			"uploaded_at" => $uploaded_at_datetime,
			"filename" => $_FILES['file']['name'],
			"ip" => $ip,
			"result" => $newFileNameDownload,
			"errors_count" => count($array_items),
			"campaign_id" => $campaign_id);

		$uploaded_file_id = pushToDatabase($pushData, $mysqli, true);

		foreach ($csv as $index => &$row) {
			$item = array_combine($csv[0], $row);
            $_lead_id = null;
            if (!empty($item['LeadID'])) {
                $_lead_id = $item['LeadID'];
            }
			if($index > 0){
				$emptyValues = 0;
				foreach ($row as $key=>$value) {
					if(empty($value)){
						$emptyValues++;
					}
				}
				if($emptyValues != count($row)){
					$query = http_build_query($item);

                    date_default_timezone_set('Europe/Kiev');
                    $date_now = date('Y-m-d H:i:s');
                    $log_file = 'log_result.txt';

                    file_put_contents($log_file, "\n === ".$date_now." === \n", FILE_APPEND | LOCK_EX);
                    file_put_contents($log_file, "\nurl\n", FILE_APPEND | LOCK_EX);
                    file_put_contents($log_file, $url, FILE_APPEND | LOCK_EX);

					$result = null;

                    try {
                        $result = file_get_contents($url, false, stream_context_create(array(
                            'http' => array(
                                'method'  => 'POST',
                                'header'  => 'Content-type: application/x-www-form-urlencoded',
                                'content' => $query
                            )
                        )));

                    } catch (Exception $e) {

                    }

//                    $result = file_get_contents($url, false, stream_context_create(array(
//                        'http' => array(
//                            'method'  => 'POST',
//                            'header'  => 'Content-type: application/x-www-form-urlencoded',
//                            'content' => $query
//                        )
//                    )));

                    file_put_contents($log_file, "\nresult\n", FILE_APPEND | LOCK_EX);
                    file_put_contents($log_file, $result, FILE_APPEND | LOCK_EX);

                    $result = json_decode($result, true);
//					$result['result'][0] = '';
                    file_put_contents($log_file, "\nresult (json_decode)\n", FILE_APPEND | LOCK_EX);
                    file_put_contents($log_file, print_r($result)."\n\n", FILE_APPEND | LOCK_EX);


                    $pushDataLead = array(
                        'lead_id' => (!is_null($_lead_id) ? $_lead_id : ((!is_null($result)) ? $result['result'][0] : 'upload - '.time())),
                        'source_id' => $source_id,
                        'source_alias' => $source_alias,
                        'email' => $item[$email_field],
                        'status' => $leadStatus,
                        'user_id' => $user_id,
                        'filename' => $_FILES['file']['name'],
                        'reason' => $leadReason,
                        'uploaded_at' => $uploaded_at_datetime,
                        'ip' => $ip,
                        'campaign_id' => $campaign_id,
						'file_id' => $uploaded_file_id);

                    pushLeadsToDatabase($pushDataLead, $mysqli);
                    echo $index." is success\n";
				}else{
					echo $index." is empty\n";
				}
			}
		}



		if(count($array_items) > 0){
			$fp = fopen($newFileNameDownload, 'w');
			$csv[0][] = "ErrorResult";
			fputcsv($fp, $csv[0]);
			foreach ($array_items as $fields) {
	    		fputcsv($fp, $fields);
			}
			fclose($fp);
		}

		unlink($newFileName); // Remove uploaded file
    }

    function pushToDatabaseAcceptedLead($data, $context){
		$sql = $context->prepare("INSERT INTO csv_accepted_leads (campaignid, email, userid, filename, uploadedat)
										VALUES (?, ?, ?, ?, ?)");
		$sql->bind_param('ssiss', $data['campaign_id'], $data['email'], $data['user_id'],$data['filename'], $data['uploaded_at']);
		$sql->execute();
		$insert_id = $sql->insert_id;
		$sql->close();
		return $insert_id;
	}
	
	function pushToDatabase($data, $context, $top){
		if($top){
			$sql = $context->prepare("INSERT INTO csv_uploaded_files (user_id, uploaded_at, filename, ip,
                                      result, errors_count, campaign_id)
										VALUES (?, ?, ?, ?, ?, ?, ?)");
			$sql->bind_param('issssii', $data['user_id'], $data['uploaded_at'], $data['filename'], $data['ip'],
                                        $data['result'], $data['errors_count'], $data['campaign_id']);
			$sql->execute();
			$insert_id = $sql->insert_id;
			$sql->close();
			return $insert_id;
		}else{
			$sql = $context->prepare("INSERT INTO csv_uploaded_files_results (parent_id, result, error_line)
				VALUES (".$data['parent_id'].", '".$data['result']."', ".$data['error_line'].")");
			$sql->execute();
			$sql->close();
		}
	}

    function pushLeadsToDatabase($data, $context){
        $isLeadIdExists = isLeadIdExists($data['lead_id'], true);

        if ($isLeadIdExists == false) {
            $sql = $context->prepare("INSERT INTO csv_status_leads (lead_id, source_id, source_alias, email, status,
                                              user_id, filename, reason, uploaded_at, ip, campaign_id, file_id)
										VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $sql->bind_param('sssssissssii', $data['lead_id'], $data['source_id'], $data['source_alias'], $data['email'],
                $data['status'], $data['user_id'], $data['filename'], $data['reason'],
                $data['uploaded_at'], $data['ip'], $data['campaign_id'], $data['file_id']);
            $sql->execute();
            $insert_id = $sql->insert_id;
            $sql->close();
            return $insert_id;
        } else {
            $new_upload_no = ($isLeadIdExists[1] + 1);

//            $sql = ;$context->prepare("UPDATE csv_status_leads
//                                         SET status = ?,
//                                             user_id = ?,
//                                             filename = ?,
//                                             reason = ?,
//                                             uploaded_at = ?,
//                                             ip = ?
//                                       WHERE lead_id = ?");
//            $sql->bind_param('sisssss', $data['status'], $data['user_id'], $data['filename'], $data['reason'],
//                $data['uploaded_at'], $data['ip'], $data['lead_id']);

			$sql = $context->prepare("INSERT INTO csv_status_leads (lead_id, source_id, source_alias, email, status,
                                              user_id, filename, reason, uploaded_at, ip, campaign_id, file_id, upload_no)
										VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
			$sql->bind_param('sssssissssiii', $data['lead_id'], $data['source_id'], $data['source_alias'], $data['email'],
				$data['status'], $data['user_id'], $data['filename'], $data['reason'],
				$data['uploaded_at'], $data['ip'], $data['campaign_id'], $data['file_id'], $new_upload_no );

            $sql->execute();
            $insert_id = $sql->insert_id;
            $sql->close();
            return $insert_id;
        }
    }

	function remove_utf8_bom($text)
	{
	    $bom = pack('H*','EFBBBF');
	    $text = preg_replace("/^$bom/", '', $text);
	    return $text;
	}
?>
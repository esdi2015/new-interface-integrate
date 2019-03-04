<?php
	require_once('user/models/db-settings.php');
	require_once('config.php');
    require_once('user/models/funcs.php');

    $SOURCE_ID = "AF070B";

    $leadStatus = "";
    $leadReason = "";

    $siteURL = getSiteURL();
    $callbackString = "callback=".$siteURL."my/lead/status.php";

    //var_dump($siteURL);
    //var_dump($callbackString); die();

    if ( 0 < $_FILES['file']['error'] ) {
		echo 'Oops ! Something gone wrong !<br/>';
        echo 'Error: ' . $_FILES['file']['error'] . '<br>';
    } else {
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
		// Insert master
		$array_items = array();

		foreach ($csv as $index => &$row) {
			$item = array_combine($csv[0], $row);

			if($index > 0){
				$emptyValues = 0;
				foreach ($row as $value) {
					if(empty($value)){
						$emptyValues++;
					}
				}
				if($emptyValues != count($row)){
					$query = http_build_query($item);

                    $result = file_get_contents($url, false, stream_context_create(array(
                        'http' => array(
                            'method'  => 'POST',
                            'header'  => 'Content-type: application/x-www-form-urlencoded',
                            'content' => $query
                        )
                    )));

                    $result = json_decode($result, true);

                    $pushDataLead = array(
                        'lead_id' => $result['result'][0],
                        'source_id' => $SOURCE_ID,
                        'source_alias' => $SOURCE_ID,
                        'email' => $item['Email1'],
                        'status' => $leadStatus,
                        'user_id' => $user_id,
                        'filename' => $_FILES['file']['name'],
                        'reason' => $leadReason,
                        'uploaded_at' => $uploaded_at_datetime,
                        'ip' => $ip);
                    pushLeadsToDatabase($pushDataLead, $mysqli);
                    echo $index." is success\n";
				}else{
					echo $index." is empty\n";
				}
			}
		}

		$pushData = array("user_id" => $user_id,
				"uploaded_at" => $uploaded_at_datetime,
				"filename" => $_FILES['file']['name'],
				"ip" => $ip,
				"result" => $newFileNameDownload,
				"errors_count" => count($array_items));
		pushToDatabase($pushData, $mysqli, true);
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
		$sql = $context->prepare("INSERT INTO csv_accepted_leads (campaignid,email,userid,filename,uploadedat)
										VALUES (?, ?, ?, ?, ?)");
		$sql->bind_param('ssiss', $data['campaign_id'], $data['email'], $data['user_id'],$data['filename'], $data['uploaded_at']);
		$sql->execute();
		$insert_id = $sql->insert_id;
		$sql->close();
		return $insert_id;
	}
	
	function pushToDatabase($data, $context, $top){
		if($top){
			$sql = $context->prepare("INSERT INTO csv_uploaded_files (user_id,uploaded_at,filename,ip,result,errors_count)
										VALUES (?, ?, ?, ?, ?, ?)");
			$sql->bind_param('issssi', $data['user_id'],$data['uploaded_at'],$data['filename'],$data['ip'],$data['result'],$data['errors_count']);
			$sql->execute();
			$insert_id = $sql->insert_id;
			$sql->close();
			return $insert_id;
		}else{
			$sql = $context->prepare("INSERT INTO csv_uploaded_files_results (parent_id,result,error_line) 
				VALUES (".$data['parent_id'].", '".$data['result']."', ".$data['error_line'].")");
			$sql->execute();
			$sql->close();
		}
	}

    function pushLeadsToDatabase($data, $context){
        $isLeadIdExists = isLeadIdExists($data['lead_id']);

        if ($isLeadIdExists == false) {
            $sql = $context->prepare("INSERT INTO csv_status_leads (lead_id, source_id, source_alias, email, status,
                                              user_id, filename, reason, uploaded_at, ip)
										VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $sql->bind_param('sssssissss', $data['lead_id'], $data['source_id'], $data['source_alias'], $data['email'],
                $data['status'], $data['user_id'], $data['filename'], $data['reason'],
                $data['uploaded_at'], $data['ip']);
            $sql->execute();
            $insert_id = $sql->insert_id;
            $sql->close();
            return $insert_id;
        } else {
            $sql = $context->prepare("UPDATE csv_status_leads
                                         SET status = ?,
                                             user_id = ?,
                                             filename = ?,
                                             reason = ?,
                                             uploaded_at = ?,
                                             ip = ?
                                       WHERE lead_id = ?");
            $sql->bind_param('sisssss', $data['status'], $data['user_id'], $data['filename'], $data['reason'],
                $data['uploaded_at'], $data['ip'], $data['lead_id']);
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
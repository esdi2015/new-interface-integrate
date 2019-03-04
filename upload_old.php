<?php
	require_once('user/models/db-settings.php');
	require_once('config.php');
	
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
		$url = $integrate_api_live_url;
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

					if(isset($item['Zip']) && strlen($item['Zip']) < 5)
					{
						$row[] = "<?xml version='1.0' encoding='utf-8' ?><result><success>0</success><leadid/><errors><error>Invalid Length: Zip (must equal 5)</error></errors></result>";
						array_push($array_items, $row);
						echo $index." is error\n";
					}else{
						$result = file_get_contents($url, false, stream_context_create(array(
							'http' => array(
								'method'  => 'POST',
								'header'  => 'Content-type: application/x-www-form-urlencoded',
								'content' => $query
							)
						)));
						
						$xml = new SimpleXMLElement($result);	
						if($xml->success == "0"){
							$row[] = $result;
							array_push($array_items, $row);
							echo $index." is error\n";
						}else{
							$pushDataAcceptedLead = array("campaign_id" => $item['AppID'],
								"email" => $item['Email'],
								"user_id" => $user_id,
								"uploaded_at" => $uploaded_at_datetime,
								"filename" => $_FILES['file']['name']);
							pushToDatabaseAcceptedLead($pushDataAcceptedLead, $mysqli);
							echo $index." is success\n";
						}
					}
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
    }

	function remove_utf8_bom($text)
	{
	    $bom = pack('H*','EFBBBF');
	    $text = preg_replace("/^$bom/", '', $text);
	    return $text;
	}

?>
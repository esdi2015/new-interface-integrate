<?php
require_once('user/models/db-settings.php');
require_once('config.php');

//if (isUserLoggedIn()){
	if(isset($_GET['id'])){
		$sql = $mysqli->prepare("SELECT id, user_id, filename, result FROM csv_uploaded_files WHERE id='" . $_GET['id'] . "'  LIMIT 1");
		$sql->execute();
		$sql->bind_result($id, $user_id, $filename, $result);
		$sql->store_result();
		$sql->fetch();
		if($sql->num_rows != 0){
			$file = $result;
	    	if (file_exists($file)) {
		        header('Content-Description: File Transfer');
		        header('Content-Type: application/csv');
		        header('Content-Disposition: attachment; filename='.basename($file));
		        header('Expires: 0');
		        header('Cache-Control: must-revalidate');
		        header('Pragma: public');
		        header('Content-Length: ' . filesize($file));
		        ob_clean();
		        flush();
		        readfile($file);
	    	}
		}
		$sql->close();
	}
//}else{
//	echo "You must be signed in to download files!";
//}

?>
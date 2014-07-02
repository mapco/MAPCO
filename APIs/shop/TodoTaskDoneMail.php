<?php
	
	check_man_params(array(	"mail_ids" 	=>	"numeric",
							"subject"	=>	"text",
							"message"	=>	"text"));
	
	//get receiver email-addresses
	$receiver_array=array();

	$results=q("SELECT mail FROM cms_contacts WHERE id_contact IN (".implode(",", $_POST["mail_ids"]).");", $dbweb, __FILE__, __LINE__);
	while($row=mysqli_fetch_array($results))
	{
		$receiver_array[]=$row["mail"];
	}
	
	$receiver=implode(",", $receiver_array);
	
	//get sender email-address
	$results=q("SELECT * FROM cms_users WHERE id_user=".$_SESSION["id_user"].";", $dbweb, __FILE__, __LINE__);
	$row=mysqli_fetch_array($results);
	$sender='FROM: '.$row["usermail"]."\r\n".'Content-Type: text/html; charset=utf-8' ."\n\n";
	
	$mail_status=mail($receiver, $_POST["subject"], $_POST["message"], $sender);
	//echo 'mail("'.$receiver.'","'.$_POST["subject"].'","'.$_POST["message"].'","'.$sender.'")';

?>						 
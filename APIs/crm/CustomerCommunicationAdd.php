<?php

	/*************************
	********** SOA 2 *********
	*************************/

	//check_man_params(array("customer_id" => "numeric", "contacttype" => "text"));

	if (isset($_POST["reminder_date"]) && $_POST["reminder_date"]!="")
	{
		$data['reminder'] = mktime($_POST["reminder_time"]*1, 0,0, substr($_POST["reminder_date"], 3,2), substr($_POST["reminder_date"], 0,2), substr($_POST["reminder_date"], 6));
	}
	else $data['reminder'] = 0;

	$data['customer_id'] = $_POST["customer_id"];
	$data['communtication_type'] = $_POST["contacttype"];
	$data['communication_text'] = $_POST["note"];
	$data['firstmod'] =time();
	$data['firstmod_user'] = $_SESSION["id_user"];
	$data['lastmod'] = time();
	$data['lastmod_user'] = $_SESSION["id_user"];

	q_insert('crm_communications', $data, $dbweb, __FILE__, __LINE__);
	$insert_id = mysqli_insert_id($dbweb);
	
	print '<insert_id>'.$insert_id.'</insert_id>';
?>
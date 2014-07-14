<?php 
	
	/*********************/
	/********SOA2*********/
	/*********************/
	
	mb_internal_encoding("UTF-8");
	
	check_man_params(array("folder" => "numericNN", "name" => "textNN"));
	
	$where = "WHERE id_folder=".$_POST['folder'];
	$data = array();
	$data['name'] = $_POST['name'];
	q_update('cms_mail_accounts_folders',$data, $where, $dbweb, __FILE__, __LINE__);
	
?>
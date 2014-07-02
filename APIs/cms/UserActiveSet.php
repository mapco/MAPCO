<?php
	include("../functions/cms_t.php");
	
	check_man_params(array("id_user" => "numericNN", "is_active" => "numeric"));

	$data = array();
	if ( $_POST['is_active'] == 1 )
	{
		$data['active'] = 0;
	}
	else
	{
		$data['active'] = 1;
	}
	
	$where = 'WHERE id_user='.$_POST['id_user'];

	q_update('cms_users', $data, $where, $dbweb, __FILE__, __LINE__)
?>
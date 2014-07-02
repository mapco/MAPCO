<?php

	$data=array();
	$data["postdata"]=$_POST["test"];
	$data["postdata"]=print_r($_POST, true);
	$data["getdata"]=print_r($_POST, true);
	q_insert("idims_users", $data, $dbshop, __FILE__, __LINE__);
	
?>
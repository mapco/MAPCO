<?php

	check_man_params(array("user_id"	=> "numeric",
						   "order_id"	=> "numeric",
						   "article_id"	=> "numeric",
						   "type_id"	=> "numeric"));
						   
	$con_from='';
	$con_to='';
	$con_cc='';
	$con_bcc='';
	

	if(isset($_POST["con_from"]))
	{
		check_man_params(array("con_from" => "text"));
		$con_from=$_POST["con_from"];
	}
	
	
	if(isset($_POST["con_to"]))
	{
		check_man_params(array("con_to" => "text"));
		$con_to=$_POST["con_to"];
	}
	
	if(isset($_POST["con_cc"]))
	{
		check_man_params(array("con_cc" => "text"));
		$con_cc=$_POST["con_cc"];
	}
	
	if(isset($_POST["con_bcc"]))
	{
		check_man_params(array("con_bcc" => "text"));
		$con_bcc=$_POST["con_bcc"];
	}
	
	$insert_data=array();
	$insert_data["user_id"]=$_POST["user_id"];
	$insert_data["order_id"]=$_POST["order_id"];
	$insert_data["article_id"]=$_POST["article_id"];
	$insert_data["type_id"]=$_POST["type_id"];
	$insert_data["con_from"]=$con_from;
	$insert_data["con_to"]=$con_to;
	$insert_data["con_cc"]=$con_cc;
	$insert_data["con_bcc"]=$con_bcc;
	$insert_data["firstmod"]=time();
	$insert_data["firstmod_user"]=$_POST["user_id"];
	$insert_data["lastmod"]=time();
	$insert_data["lastmod_user"]=$_POST["user_id"];
		
	$results=q_insert("crm_conversations", $insert_data, $dbweb, __FILE__, __LINE__);					   
	//$results=q("INSERT INTO crm_conversations (user_id, order_id, article_id, type_id, firstmod, firstmod_user, lastmod, lastmod_user) VALUES (".$_POST["user_id"].", ".$_POST["order_id"].", ".$_POST["article_id"].", ".$_POST["type_id"].", ".time().", ".$_POST["user_id"].", ".time().", ".$_POST["user_id"].");", $dbweb, __FILE__, __LINE__);

?>
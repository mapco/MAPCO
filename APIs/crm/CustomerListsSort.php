<?php
	/*************************
	********** SOA 2 *********
	*************************/
	
	//$xml = '';
	
	$required = array( "type"	=>	"text");
	check_man_params($required);
	
	switch($_POST['type'])
	{
		case "types": $table = "crm_customer_list_types";
			$label = 'type_';	
			break;
		case "lists": $table = "crm_customer_lists";
			$label = 'list_';
			break;
	}
	
	if($_POST['list'][0] != '')
	{
		for($i=0; $i<sizeof($_POST["list"]); $i++)
		{
			$id_file=str_replace($label, "", $_POST["list"][$i]);
		
			q("UPDATE ".$table." SET ordering=".($i+1)." WHERE id=".$id_file.";", $dbweb, __FILE__, __LINE__);
		}
	}
	
//	print $xml;
?>
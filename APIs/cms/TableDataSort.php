<?php
	/*************************
	********** SOA 2 *********
	*************************/	

	$required = array(
					"table" => "textNN",
					"label" => "textNN",
					"column" => "textNN"
					);
	check_man_params($required);

	if($_POST['list'][0] != '')
	{
		for($i=0; $i<sizeof($_POST["list"]); $i++)
		{
			$id=str_replace($_POST['label'], "", $_POST["list"][$i]);
	
			q("UPDATE ".$_POST['table']." SET ordering=".($i+1)." WHERE ".$_POST['column']."=".$id.";", $dbweb, __FILE__, __LINE__);
		}
	}
	
?>
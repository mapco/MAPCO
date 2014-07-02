<?php

/**
*
*
*/


$required = array("table" => "textNN", "db" => "resource", "where" => "textNN");
check_man_params($required);

if ($_POST["db"] == "dbweb") $db = $dbweb;
if ($_POST["db"] == "dbshop") $db = $dbshop;

	$affected_rows=0;
	$datafield = array();

	$res_struct=q("SHOW COLUMNS FROM ".$_POST["table"], $db, __FILE__, __LINE__);
	while($struct=mysqli_fetch_assoc($res_struct))
	{
		if ($struct["Extra"]!="auto_increment")
		{
			if (isset($_POST[$struct["Field"]]))
			{
				$datafield[$struct["Field"]] = $_POST[$struct["Field"]];
			}
		}
	}
	
	if (sizeof($datafield)>0)
	{
		q_update($_POST["table"], $datafield, $_POST["where"], $db, __FILE__, __LINE__);
		$affected_rows=mysqli_affected_rows($db);
	}
	else
	{
		$affected_rows=0;	
	}

echo $xml . '<affected_rows>' . $affected_rows . '</affected_rows>';
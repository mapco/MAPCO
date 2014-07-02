<?php

	$required=array("auf_id" => "numericNN", "order_id" => "numericNN", "reason_id" => "numericNN", "gross" => "numericNN");
	check_man_params($required);
	
	//AUFID ALREADY SET?
	$res_check = q("SELECT * FROM shop_orders_credits WHERE auf_id = ".$_POST["auf_id"], $dbshop, __FILE__, __LINE__);
	if (mysqli_num_rows($res_check)==0)
	{

			//WENN REASON "SONSTIGE" -> NEED REASON DESCRIPTION
			if($_POST["reason_id"]==2)
			{
				if ($_POST["reason_description"]=="")
				{
					//show_error();
					echo "Gutschrift-Erläuterung benötigt";
					exit;
				}
			}
			
			//WENN REASON "RÜCKGABE/UMTAUSCH" -> NEED REASON DESCRIPTION
			if($_POST["reason_id"]==1)
			{
				check_man_params(array("return_id" => "numericNN"));	
			}
			
			$datafield = array();
			$datafield["auf_id"] = $_POST["auf_id"];
			$datafield["order_id"] = $_POST["order_id"];
			$datafield["netto"] = $_POST["net"];
			$datafield["brutto"] = $_POST["gross"];
			$datafield["reason_id"] = $_POST["reason_id"];
			$datafield["reason_description"] = $_POST["reason_description"];
			$datafield["return_id"] = $_POST["return_id"];
			$datafield["firstmod"] = time();
			$datafield["firstmod_user"] = $_SESSION["id_user"];
			
			q_insert("shop_orders_credits", $datafield, $dbshop, __FILE__, __LINE__);
	}

?>
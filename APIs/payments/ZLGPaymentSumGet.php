<?php

	$required=array("payment_type_id" => "numericNN");
	check_man_params($required);

	if (isset($_POST["flag_as_transfered"]) && $_POST["flag_as_transfered"]==1)
	{
		$required=array("exchangerate_from_EUR" => "numericNN");
		check_man_params($required);
	}
	
	//KONVERT TIME TO TIMESTAMP if needed
	if (isset($_POST["response_from"]) && strpos($_POST["response_from"], "."))
	{
		$_POST["response_from"] = mktime(0,0,1,substr($_POST["response_from"], 3,2),substr($_POST["response_from"], 0,2),substr($_POST["response_from"], 6));
	}
	if (isset($_POST["response_to"]) && strpos($_POST["response_to"], "."))
	{
		$_POST["response_to"] = mktime(23,59,59,substr($_POST["response_to"], 3,2),substr($_POST["response_to"], 0,2),substr($_POST["response_to"], 6));
	}
	if (isset($_POST["transfered_from"]) && strpos($_POST["transfered_from"], "."))
	{
		$_POST["transfered_from"] = mktime(0,0,1,substr($_POST["transfered_from"], 3,2),substr($_POST["transfered_from"], 0,2),substr($_POST["transfered_from"], 6));
	}
	if (isset($_POST["transfered_to"]) && strpos($_POST["transfered_to"], "."))
	{
		$_POST["transfered_to"] = mktime(23,59,59,substr($_POST["transfered_to"], 3,2),substr($_POST["transfered_to"], 0,2),substr($_POST["transfered_to"], 6));
	}
	if (isset($_POST["created_from"]) && strpos($_POST["created_from"], "."))
	{
		$_POST["created_from"] = mktime(0,0,1,substr($_POST["created_from"], 3,2),substr($_POST["created_from"], 0,2),substr($_POST["created_from"], 6));
	}
	if (isset($_POST["created_to"]) && strpos($_POST["created_to"], "."))
	{
		$_POST["created_to"] = mktime(23,59,59,substr($_POST["created_to"], 3,2),substr($_POST["created_to"], 0,2),substr($_POST["created_to"], 6));
	}
	if (isset($_POST["invoice_from"]) && strpos($_POST["invoice_from"], "."))
	{
		$_POST["invoice_from"] = mktime(0,0,1,substr($_POST["invoice_from"], 3,2),substr($_POST["invoice_from"], 0,2),substr($_POST["invoice_from"], 6));
	}
	if (isset($_POST["invoice_to"]) && strpos($_POST["invoice_to"], "."))
	{
		$_POST["invoice_to"] = mktime(23,59,59,substr($_POST["invoice_to"], 3,2),substr($_POST["invoice_to"], 0,2),substr($_POST["invoice_to"], 6));
	}
	
	//CREATE QUERY
	$query = "SELECT * FROM idims_zlg_log WHERE payment_type_id = ".$_POST["payment_type_id"];

	if (isset($_POST["response_from"]) && $_POST["response_from"]!=0 && $_POST["response_from"]!="" && isset($_POST["response_to"]) && $_POST["response_to"]!=0 && $_POST["response_to"]!="" )
	{
		$query.= " AND response_time > ".$_POST["response_from"]." AND response_time < ".$_POST["response_to"];
	}
	if (isset($_POST["created_from"]) && $_POST["created_from"]!=0 && $_POST["created_from"]!="" && isset($_POST["created_to"]) && $_POST["created_to"]!=0 && $_POST["created_to"]!="" )
	{
		$query.= " AND creation_time > ".$_POST["created_from"]." AND creation_time < ".$_POST["created_to"];
	}
	if (isset($_POST["invoice_from"]) && $_POST["invoice_from"]!=0 && $_POST["invoice_from"]!="" && isset($_POST["invoice_to"]) && $_POST["invoice_to"]!=0 && $_POST["invoice_to"]!="" )
	{
		$query.= " AND invoice_time > ".$_POST["invoice_from"]." AND invoice_time < ".$_POST["invoice_to"];
	}

	if (isset($_POST["not_transfered"]) && $_POST["not_transfered"]==1)
	{
		$query.=" AND amount_transfer_time = 0";
	}
	elseif (isset($_POST["transfered_from"]) && $_POST["transfered_from"]!=0 && $_POST["transfered_from"]!="" && isset($_POST["transfered_to"]) && $_POST["transfered_to"]!=0 && $_POST["transfered_to"]!="" )
	{
		$query.=" AND amount_transfer_time > ".$_POST["transfered_from"]." AND amount_transfer_time < ".$_POST["transfered_to"];
	}
	if (isset($_POST["shop_id"]) && $_POST["shop_id"]!=0 && $_POST["shop_id"]!="")
	{
		$query.=" AND shop_id = ".$_POST["shop_id"];
	}

	//ALS ÜBERWIESEN MARKIEREN 
	$amount_transfer_time=time();
	if (isset($_POST["flag_as_transfered"]) && $_POST["flag_as_transfered"]==1 && $_POST["payment_type_id"]==4 && isset($_POST["shop_id"]))
	{
		$res = q($query, $dbshop, __FILE__, __LINE__);
		while ($row = mysqli_fetch_assoc($res))
		{
//			if ($row["amount_transfered_EUR"]==0)
			{
				//GET TRANSFERED EUR
				$amount_transfered = round(($row["order_total_FC"]-$row["fee_FC"]) / $_POST["exchangerate_from_EUR"], 2);
				
				$datafield = array();
				$datafield["amount_transfer_time"] = $amount_transfer_time;
				$datafield["transfer_exchange_rate_from_EUR"] = $_POST["exchangerate_from_EUR"];
				$datafield["amount_transfered_EUR"] = $amount_transfered;
				q_update("idims_zlg_log", $datafield, " WHERE id_log = ".$row["id_log"], $dbshop, __FILE__, __LINE__);
			}
		}
	}

	
	$res = q($query, $dbshop, __FILE__, __LINE__);
	
	$xml='';
	$totalEUR=0;
	$feeEUR=0;
	$totalFC=0;
	$feeFC=0;

	$amount_transfer_time=time();
	while ($row = mysqli_fetch_assoc($res))
	{
		$xml.= '<zlg_log>'."\n";
		while (list($key, $val) = each ($row))
		{
			$xml.= 	'<'.$key.'><![CDATA['.$val.']]></'.$key.'>'."\n";
		}
		$totalEUR+=$row["order_total_EUR"];
		$feeEUR+=$row["fee_EUR"];
		$totalFC+=$row["order_total_FC"];
		$feeFC+=$row["fee_FC"];

		$xml.= '</zlg_log>'."\n";
	/*	
		if (isset($_POST["flag_as_transfered"]) && $_POST["flag_as_transfered"]==1 && $row["amount_transfered_EUR"]==0)
		{
			//GET TRANSFERED EUR
			$amount_transfered = round(($row["order_total_EUR"]-$row["fee_FC"]) / $_POST["exchangerate_from_EUR"], 2);
			
			$datafield = array();
			$datafield["amount_transfer_time"] = $amount_transfer_time;
			$datafield["transfer_exchange_rate_from_EUR"] = $_POST["exchangerate_from_EUR"];
			$datafield["amount_transfered_EUR"] = $amount_transfered;
			q_update("idims_zlg_log_test", $datafield, " WHERE id_log = ".$row["id_log"], $dbshop, __FILE__, __LINE__);
		}
	*/
	}

//SERVICE RESPONSE

echo '<zlg_logs>'."\n";
echo $xml;
echo '<zlg_logs_total_EUR>'.$totalEUR.'</zlg_logs_total_EUR>'."\n";
echo '<zlg_logs_fee_EUR>'.$feeEUR.'</zlg_logs_fee_EUR>'."\n";
echo '<zlg_logs_total_FC>'.$totalFC.'</zlg_logs_total_FC>'."\n";
echo '<zlg_logs_fee_FC>'.$feeFC.'</zlg_logs_fee_FC>'."\n";

echo '</zlg_logs>'."\n";

?>
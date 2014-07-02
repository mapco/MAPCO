<?php

	if (!isset($_POST["quantity"])) $_POST["quantity"] =5;
	
	if (isset($_POST["invoice_id"]) && is_numeric($_POST["invoice_id"]) && $_POST["invoice_id"]!=0)
	{
		$res_zlg_msg = q("SELECT * FROM idims_zlg_log WHERE invoice_id = ".$_POST["invoice_id"]." LIMIT 1", $dbshop, __FILE__, __LINE__);
	}
	elseif (isset($_POST["order_id"]) && is_numeric($_POST["order_id"]) && $_POST["order_id"]!=0)
	{
		$res_zlg_msg = q("SELECT * FROM idims_zlg_log WHERE invoice_id = ".$_POST["invoice_id"]." LIMIT 1", $dbshop, __FILE__, __LINE__);
	}
	else
	{
		//$res_zlg_msg = q("SELECT * FROM idims_zlg_log WHERE response_time = 0 AND shop_id IN (1,2,3,4) AND difference=0 ORDER BY creation_time LIMIT ".$_POST["quantity"], $dbshop, __FILE__, __LINE__);
		
		//RECHNUNGEN AB 03.05.2014
		$res_zlg_msg = q("SELECT * FROM idims_zlg_log WHERE response_time = 0 AND shop_id IN (1,2,3,4,5,7) AND difference>-0.03 AND difference<0.03 AND invoice_time > 1399075201 ORDER BY creation_time LIMIT ".$_POST["quantity"], $dbshop, __FILE__, __LINE__);
		//$res_zlg_msg = q("SELECT * FROM idims_zlg_log WHERE response_time = 0 AND shop_id IN (3) AND difference=0 ORDER BY creation_time LIMIT ".$_POST["quantity"], $dbshop, __FILE__, __LINE__);
//	$res_zlg_msg = q("SELECT * FROM idims_zlg_log WHERE response_time = 0 AND shop_id IN (7) ORDER BY creation_time LIMIT ".$_POST["quantity"], $dbshop, __FILE__, __LINE__);
	}
	if (mysqli_num_rows($res_zlg_msg)>0)
	{
		$zlg_msg = array();
		$zlg = '';
		while ($row_zlg_msg = mysqli_fetch_assoc($res_zlg_msg))
		{
			//if ($row_zlg_msg["difference"]>-0.2 && $row_zlg_msg["difference"]<0.2)
			{
				$zlg_msg[$row_zlg_msg["invoice_id"]]=$row_zlg_msg;
				
				$zlg.=$row_zlg_msg["idims_call"];
			}
		}
	
		
		$xml='<WEB_ZLG_OP>'."\n";
		$xml.=$zlg;
		$xml .= '</WEB_ZLG_OP>'."\n";
	echo $xml;
		$xml = str_replace("\n", "", $xml);
		$xml = str_replace("\t", "", $xml);
	//exit;
	
		$serverUrl='http://80.146.160.154/idims/service1.asmx/WEB_ZLG_OP';
		$fields = array(
							'Token' => "it@mapco.de",
							'opXML' => urlencode($xml),
						);
	
		foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
		rtrim($fields_string, '&');
	
		$connection = curl_init();
		curl_setopt($connection, CURLOPT_FORBID_REUSE, true); 
		curl_setopt($connection, CURLOPT_FRESH_CONNECT, true);
		curl_setopt($connection, CURLOPT_URL, $serverUrl);
		curl_setopt($connection, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($connection, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($connection, CURLOPT_POST, true);
		curl_setopt($connection, CURLOPT_POSTFIELDS, $fields_string);
		curl_setopt($connection, CURLOPT_RETURNTRANSFER, true);
		$responseXml = curl_exec($connection);
		curl_close($connection);
	
		//xml validation fix
		$responseXml=str_replace('&lt;', '<', $responseXml);
		$responseXml=str_replace('&gt;', '>', $responseXml);
	//echo $responseXml;
	
		//read response
		$use_errors = libxml_use_internal_errors(true);
		try
		{
			$response = new SimpleXMLElement($responseXml);
		}
		catch(Exception $e)
		{
			echo '<OPTranferResponse>'."\n";
			echo '	<Ack>Failure</Ack>'."\n";
			echo '	<Error>'."\n";
			echo '		<Code>'.__LINE__.'</Code>'."\n";
			echo '		<shortMsg>XML nicht valide.</shortMsg>'."\n";
			echo '		<longMsg>Beim Auswerten der XML-Daten trat ein Fehler auf.</longMsg>'."\n";
			echo '	</Error>'."\n";
			echo '  <Response><![CDATA['.$responseXml.']]></Response>';
			echo '</OPTransferResponse>'."\n";
			exit;
		}
		libxml_clear_errors();
		libxml_use_internal_errors($use_errors);
	
		if (strpos($responseXml, "<ERROR>")>0)
		{
			echo '<OPTransferResponse>'."\n";
			echo '	<Ack>Failure</Ack>'."\n";
			echo '	<Error>'."\n";
			echo '		<Code>'.__LINE__.'</Code>'."\n";
			echo '		<shortMsg>XML nicht valide.</shortMsg>'."\n";
			echo '		<longMsg>Beim Auswerten der XML-Daten trat ein Fehler auf.</longMsg>'."\n";
			echo '	</Error>'."\n";
			echo '  <Response><![CDATA['.$responseXml.']]></Response>';
			echo '</OPTransferResponse>'."\n";
			exit;
		}
		else
		
		{
			$i=0;
			while (isset($response->VORGANG[$i]))
			{
				//UPDATE dims zlg log
				$datafield = array();
				$datafield["idims_response"] = (string)$response->VORGANG[$i]->INFO[0];
				$datafield["response_time"] = time();
				
				if (strpos((string)$response->VORGANG[$i]->INFO[0], "Zahlung OK")!== false )
				{
					$datafield["acknowledgment"] = "Succes";
					//SET FLAG IN shop_orders
					$datafield2 = array();
					$datafield2["zlg_accounted"] = 1;
					$res = q_update("shop_orders", $datafield2, "WHERE invoice_id = ".$zlg_msg[(int)$response->VORGANG[$i]->AUFID[0]]["invoice_id"], $dbshop, __FILE__, __LINE__);

				}
				else
				{
					$datafield["acknowledgment"] = "Failure";
				}
				
				$res = q_update("idims_zlg_log", $datafield, "WHERE id_log = ".$zlg_msg[(int)$response->VORGANG[$i]->AUFID[0]]["id_log"], $dbshop, __FILE__, __LINE__);
				
				$i++;	
			}
		}
	
	}
/*	
foreach ($zlg_msg as $invoice_id => $zlg)
{
	$datafield = array();
	$datafield["idims_response"] = "Alles Supi";
	$datafield["response_time"] = time();
	$datafield["acknowledgment"] = "Success";
	$res = q_update("idims_zlg_log", $datafield, "WHERE id_log = ".$zlg["id_log"], $dbshop, __FILE__, __LINE__);
	
	$datafield2 = array();
	$datafield2["zlg_accounted"] = 1;
	$res = q_update("shop_orders", $datafield2, "WHERE invoice_id = ".$zlg["invoice_id"], $dbshop, __FILE__, __LINE__);
}
*/
echo $responseXml;
?>
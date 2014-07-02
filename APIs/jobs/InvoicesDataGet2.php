<?php

	$starttime = time()+microtime();

	//FETCH AUF_IDS
	$results = q("SELECT a.id_order, a.combined_with, b.AUF_ID FROM shop_orders AS a, shop_orders_auf_id AS b WHERE a.id_order=b.order_id AND (a.has_invoice_data = 0 AND NOT a.AUF_ID = 0 AND a.firstmod>1391209200) and b.AUF_ID=b.parent_auf_id ORDER BY a.id_order DESC LIMIT 60;", $dbshop, __FILE__, __LINE__);

	$order_id=array();
	$combined=array();
	$statusXml  = '<WEB_AUF_STATUS>'."\n";
	//$results=q("SELECT a.id_order, a.combined_with, b.AUF_ID FROM shop_orders AS a, shop_orders_auf_id AS b WHERE a.id_order=b.order_id AND (a.invoice_date=0 OR a.invoice_id=0) and b.AUF_ID=b.parent_auf_id order by b.AUF_ID LIMIT 60;", $dbshop, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		$statusXml .= '	<AUFID>'.$row["AUF_ID"].'</AUFID>';
		$order_id[$row["AUF_ID"]]=$row["id_order"];
		$combined[$row["AUF_ID"]]=$row["combined_with"];
	}
	$statusXml .= '</WEB_AUF_STATUS>'."\n";

	$statusXml = str_replace("\n", "", $statusXml);
	$statusXml = str_replace("\t", "", $statusXml);

	//it@mapco.de
	//it@mapco.de<TESTDB/>
	
//	echo $statusXml;
//	exit;

	$serverUrl = 'http://80.146.160.154/idims/service1.asmx/WEB_AUF_STATUS';
	$fields = array(
						'Token' => "it@mapco.de",
						'aufXML' => urlencode($statusXml),
						'booleanPDF' => "TRUE",
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
	unset($fields);
	unset($fields_string);

	//xml validation fix
	$responseXml=str_replace('&lt;', '<', $responseXml);
	$responseXml=str_replace('&gt;', '>', $responseXml);

	//read response
	$use_errors = libxml_use_internal_errors(true);
	try
	{
		$response = new SimpleXMLElement($responseXml);
	}
	catch(Exception $e)
	{
		echo '<InvoicesDataGetResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>XML nicht valide.</shortMsg>'."\n";
		echo '		<longMsg>Beim Auswerten der XML-Daten trat ein Fehler auf.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '  <Response><![CDATA['.$responseXml.']]></Response>';
		echo '</InvoicesDataGetResponse>'."\n";
		exit;
	}
	libxml_clear_errors();
	libxml_use_internal_errors($use_errors);

	$xml = new SimpleXMLElement($responseXml);
	
	$j=0;
	$msg  = "\n";
	$msg .= '<InvoicesDataGet>'."\n";
	$msg .= '	<Ack>Success</Ack>'."\n";
	for($i=0; isset($xml->AUFID[$i]); $i++)
	{
		$inserted=false;
		$auf_id = (int)$xml->AUFID[$i]->AUF_ID[0];
		//CHECK IF DATA ALREADY KNOWN
		$res_check = q("SELECT * FROM idims_auf_status WHERE auf_id = ".$auf_id, $dbshop, __FILE__, __LINE__);
		if (mysqli_num_rows($res_check)==0)
		{
			if ((string)$xml->AUFID[$i]->AUF_STATUS[0]=="GEPACKT")
			{
				$d=(string)$xml->AUFID[$i]->RNG_TIMESTAMP[0];
				$rng_time = mktime(substr($d, 11, 2), substr($d, 14, 2), substr($d, 17, 2), substr($d, 3, 2), substr($d, 0, 2), substr($d, 6, 4));
				
				$data=array();
				$data["auf_id"]=		$auf_id;
				$data["auf_nr"]=		(string)$xml->AUFID[$i]->AUF_NR[0];
				$data["auf_vom"]=		(string)$xml->AUFID[$i]->AUF_VOM[0];
				$data["auf_art"]=		(string)$xml->AUFID[$i]->AUF_ART[0];
				$data["auf_status"]=	(string)$xml->AUFID[$i]->AUF_STATUS[0];
				$data["auf_netto"]=		str_replace(",",".",(string)$xml->AUFID[$i]->AUF_NETTO[0]);
				$data["auf_ust"]=		str_replace(",",".",(string)$xml->AUFID[$i]->AUF_UST[0]);
				$data["auf_brutto"]=	str_replace(",",".",(string)$xml->AUFID[$i]->AUF_BRUTTO[0]);
				$data["rng_id"]=		(int)$xml->AUFID[$i]->RNG_ID[0];
				$data["rng_nr"]=		(string)$xml->AUFID[$i]->RNG_NR[0];
				$data["rng_vom"]=		(string)$xml->AUFID[$i]->RNG_VOM[0];
				$data["rng_art"]=		(string)$xml->AUFID[$i]->RNG_ART[0];
				$data["rng_netto"]=		str_replace(",",".",(string)$xml->AUFID[$i]->RNG_NETTO[0]);
				$data["rng_ust"]= 		str_replace(",",".",(string)$xml->AUFID[$i]->RNG_UST[0]);
				$data["rng_brutto"]=	str_replace(",",".",(string)$xml->AUFID[$i]->RNG_BRUTTO[0]);
				$data["rng_timestamp"]=	(string)$xml->AUFID[$i]->RNG_TIMESTAMP[0];
				$data["rng_time"]=   	$rng_time;
				$data["firstmod"]=		time();
				$data["firstmod_user"]=	$_SESSION["id_user"];
				$data["lastmod"]=		time();
				$data["lastmod_user"]=	$_SESSION["id_user"];
		
				if($data["rng_id"]>0 and isset($order_id[$auf_id]) and $order_id[$auf_id]>0)
				{

					//SAVE INVOICE PDF
					$file = base64_decode((string)$xml->AUFID[$i]->PDF[0]);
					q("INSERT INTO cms_files (filename, extension, filesize, description, firstmod, firstmod_user, lastmod, lastmod_user) VALUES('".$data["rng_nr"]."', 'pdf', ".strlen($file).", 'Rechnung', 10, ".time().", 10, ".time().");", $dbweb, __FILE__, __LINE__);
					$data["rng_file_id"]=mysqli_insert_id($dbweb);
					$directory='../../mapco_shop_de/files/'.bcdiv($data["rng_file_id"], 1000);
					if( !is_dir($directory) ) mkdir($directory);
					$path='../../mapco_shop_de/files/'.bcdiv($data["rng_file_id"], 1000).'/'.$data["rng_file_id"].'.pdf';
					$LabelURLLocal=PATH.'files/'.bcdiv($data["rng_file_id"], 1000).'/'.$data["rng_file_id"].'.pdf';
					$LabelPath='files/'.bcdiv($data["rng_file_id"], 1000).'/'.$data["rng_file_id"].'.pdf';
					$handle=fopen($path, "w");
					fwrite($handle, $file);
					fclose($handle);

					//WRITE AUF_ID DATA
					q_insert("idims_auf_status", $data, $dbshop, __FILE__, __LINE__);
					$msg .= '	<OrderUpdated>'.$order_id[$auf_id].'</OrderUpdated>'."\n";
					$inserted=true;
					$j++;
				}
			}
		}
		if ($inserted)
		{
			if($combined[$data["auf_id"]]>0) $condition = 'combined_with='.$order_id[$auf_id];
			else $condition = 'id_order='.$order_id[$auf_id];
			if (isset($order_id[$auf_id]))
			{
				q_update("shop_orders", array("has_invoice_data" => 1), "WHERE ".$condition, $dbshop, __FILE__, __LINE__);
			}
			else
			{
				echo '<Error_AUF_ID>'.$auf_id.'</Error_AUF_ID>'."\n";
			}
		}
	}
	$msg .= '	<OrdersUpdated>'.$j.'</OrdersUpdated>'."\n";
	$msg .= '</InvoicesDataGet>'."\n";
	echo $msg;
	$stoptime = time()+microtime();
	echo "Dauer: ".number_format($stoptime-$starttime, 2)." Sekunden";

	//stop job if time limit is reached
//	$stoptime = time()+microtime();
//	if ($stoptime-$starttime>60) break;
	
?>
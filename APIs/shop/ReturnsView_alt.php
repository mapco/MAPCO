<?php

	//AUSLESEN TABELLENZUORDNUNGEN
	$results=q("SELECT * FROM shop_returns_platform;", $dbshop, __FILE__, __LINE__);
	while( $row=mysql_fetch_array($results) ) {
		$r_platform[$row["ID"]]=$row["platform"];
	}
	
	$results=q("SELECT * FROM shop_returns_rAction;", $dbshop, __FILE__, __LINE__);
	while( $row=mysql_fetch_array($results) ) {
		$r_rAction[$row["ID"]]=$row["rAction"];
	}
	
	$results=q("SELECT * FROM shop_returns_rReason;", $dbshop, __FILE__, __LINE__);
	while( $row=mysql_fetch_array($results) ) {
		$r_rReason[$row["ID"]]=$row["rReason"];
	}


	// DATEN ZUR AUSGABE
	switch ($_POST["returnsViewFilterState"])
	{
		case "": $sql="SELECT * FROM shop_returns;"; break;
		case "all": $sql="SELECT * FROM shop_returns;"; break;
		case "open": $sql="SELECT * FROM shop_returns WHERE state = 'open';"; break;
		case "closed": $sql="SELECT * FROM shop_returns WHERE state = 'closed';"; break;
	}
		
	$results=q($sql, $dbshop, __FILE__, __LINE__);
	
	
		
		$i=0;
	
		// VIEW RÜCKGABEN
		
		echo '<table>';
		echo '<tr>';
			echo '<th> Status </th>';
			echo '<th> Käufer Name </th>';
			echo '<th> Käufer ID </th>';
			echo '<th> Plattform </th>';
			echo '<th> Kaufdatum </th>';
			echo '<th> Artikel </th>';
			echo '<th> Artikelgruppe </th>';
			echo '<th> Anzahl </th>';
			echo '<th> Vorgang </th>';
			echo '<th> Grund </th>';
			echo '<th> Gemeldet </th>';
			echo '<th> Erhalten </th>';
			echo '<th> Erstattung </th>';
			echo '<th> Gutschrift IDIMS </th>';
			echo '<th> Provisions-gutschrift </th>';
			echo '<th> <img style="margin:0px 5px 0px 0px; border:0; padding:0; cursor:pointer; float:right;" src="images/icons/32x32/page_add.png" alt="Neue Rückgabe / Neuen Umtausch eingeben" title="Neue Rückgabe / Neuen Umtausch eingeben" onclick="ReturnAdd()" /></th>';
		echo '</tr>';

	if (mysql_affected_rows()>0)
	{
		// DATEN AUS DATENBANK ZUR ANZEIGE
		while( $row=mysql_fetch_array($results) ) {
		
			$returns["state"][$i]=$row["state"];
			$returns["buyerName"][$i]=$row["buyername"];
			$returns["userid"][$i]=$row["userid"];
			$returns["platform"][$i]=$row["platform"];
			$returns["date_order"][$i]=$row["date_order"];
			$returns["MPU"][$i]=$row["MPU"];
			$returns["articlegroup"][$i]=$row["articlegroup"];
			$returns["quantity"][$i]=$row["quantity"];
			$returns["rAction"][$i]=$row["rAction"];
			$returns["rReason"][$i]=$row["rReason"];
			$returns["date_announced"][$i]=$row["date_announced"];
			$returns["date_return"][$i]=$row["date_return"];
			$returns["date_refund"][$i]=$row["date_refund"];
			$returns["date_r_IDIMS"][$i]=$row["date_r_IDIMS"];

			
		//DATUM VORBEREITUNG ZUR ÜBERGABE
			$date_order=$date_announced=$date_return=$date_refund=$date_refund_reshipment=$date_r_IDIMS=$date_case_closed=$date_exchange_sent="";
			If ($row["date_order"]!=0) { $date_order = date("d.m.Y",$row["date_order"]);}
			If ($row["date_announced"]!=0) { $date_announced = date("d.m.Y",$row["date_announced"]);}
			If ($row["date_return"]!=0) { $date_return = date("d.m.Y",$row["date_return"]);}
			If ($row["date_refund"]!=0) { $date_refund = date("d.m.Y",$row["date_refund"]);}
			If ($row["date_refund_reshipment"]!=0) { $date_refund_reshipment = date("d.m.Y",$row["date_refund_reshipment"]);}
			If ($row["date_r_IDIMS"]!=0) { $date_r_IDIMS = date("d.m.Y",$row["date_r_IDIMS"]);}
			$date_demandEbayClosing1="";
			If ($row["ebay_demand_closing1"]!=0) { $date_demandEbayClosing1 = date("d.m.Y",$row["ebay_demand_closing1"]);}
			$date_demandEbayClosing2="";
			If ($row["ebay_demand_closing2"]!=0) { $date_demandEbayClosing2 = date("d.m.Y",$row["ebay_demand_closing2"]);}
			If ($row["date_exchange_sent"]!=0) { $date_exchange_sent = date("d.m.Y",$row["date_exchange_sent"]);}
	
			echo '<tr onmouseover="style.backgroundColor=\'#B0C4DE\'" onmouseout="style.backgroundColor=\'#ffffff\'">'; 
               if ($returns["state"][$i]=="open" ) 
			   { echo '<td><img style="margin:0px 5px 0px 0px; border:0; padding:0; float:right;" src="'.PATH.'images/icons/24x24/warning.png" alt="Fall offen" title="Fall offen" /></td>';
			   }
               if ($returns["state"][$i]=="closed" ) 
			   { echo '<td><img style="margin:0px 5px 0px 0px; border:0; padding:0; float:right;" src="'.PATH.'images/icons/24x24/accept.png" alt="Fall geschlossen" title="Fall geschlossen" /></td>';
			   }			   
				echo '<td>'.$returns["buyerName"][$i].'</td>';
				echo '<td>'.$returns["userid"][$i].'</td>';
				echo '<td>'.$r_platform[$returns["platform"][$i]].'</td>';
				echo '<td>'.date("d.m.Y", $returns["date_order"][$i]).'</td>';
				echo '<td>'.$returns["MPU"][$i].'</td>';
				echo '<td>'.$returns["articlegroup"][$i].'</td>';
				echo '<td>'.$returns["quantity"][$i].'</td>';
				echo '<td>'.$r_rAction[$returns["rAction"][$i]].'</td>';
				if ( !isset($r_rReason[$returns["rReason"][$i]]) ) $r_rReason[$returns["rReason"][$i]]='';
				echo '<td>'.$r_rReason[$returns["rReason"][$i]].'</td>';

				if ($returns["date_announced"][$i]<>0) {echo '<td>'.date("d.m.Y", $returns["date_announced"][$i]).'</td>';} else { echo '<td> </td>';}
				if ($returns["date_return"][$i]<>0) {echo '<td>'.date("d.m.Y", $returns["date_return"][$i]).'</td>';} else { echo '<td> </td>';}
				if ($returns["date_refund"][$i]<>0) {echo '<td>'.date("d.m.Y", $returns["date_refund"][$i]).'</td>';} else { echo '<td> </td>';}
				if ($returns["date_r_IDIMS"][$i]<>0) {echo '<td>'.date("d.m.Y", $returns["date_r_IDIMS"][$i]).'</td>';} else { echo '<td> </td>';}

				if ($row["ebay_fee_refund"]=="1") {
					if ($row["platform"]=="ebay_AP" || $row["platform"]=="ebay_MAPCO")
					{
						echo '<td><img style="margin:0px 5px 0px 0px; border:0; padding:0; cursor:pointer; float:right;" src="images/icons/24x24/accept.png" alt="Ebay Provision gutgeschrieben/Rückgabe wurde eingeleitet" title="Ebay Provision gutgeschrieben/Rückgabe wurde eingeleitet"></td>';
					}
					else
					{
						echo '<td style="background-color:#c8c8c8"></td>';
					}
				}
				else {
					if ($row["platform"]=="ebay_AP" || $row["platform"]=="ebay_MAPCO")
					{
						echo '<td><img style="margin:0px 5px 0px 0px; border:0; padding:0; cursor:pointer; float:right;" src="images/icons/24x24/info.png" alt="Ebay Provision noch nicht zurückbekommen/Rückgabe wurde noch nichteingeleitet" title="Ebay Provision noch nicht zurückbekommen/Rückgabe wurde noch nichteingeleitet"></td>';
					}
					else
					{
						echo '<td style="background-color:#c8c8c8"></td>';
					}
				}

				
				echo '<td>';
				//for new version
//				echo '<img style="margin:0px 5px 0px 0px; border:0; padding:0; cursor:pointer; float:right;" src="images/icons/24x24/page_search.png" alt="Fall aufrufen/Details" title="Fall aufrufen/Details" onclick="ReturnUpdate('.$row["id"].');" />';
				//ie bugfix
				$row["rReason_detail"]=str_replace("\n", "", $row["rReason_detail"]);
				$row["rReason_detail"]=str_replace("\r", "", $row["rReason_detail"]);
				//$row["rReason_detail"]=htmlentities($row["rReason_detail"], "ISO8859-1");
				$row["rReason_detail"]=htmlentities($row["rReason_detail"]);
				echo '<img style="margin:0px 5px 0px 0px; border:0; padding:0; cursor:pointer; float:right;" src="images/icons/24x24/page_search.png" alt="Fall aufrufen/Details" title="Fall aufrufen/Details" onclick="ReturnUpdate( { id:\''.addslashes($row["id"]).'\', state:\''.addslashes($row["state"]).'\', platform:\''.addslashes($row["platform"]).'\', userid:\''.$row["userid"].'\', buyerName:\''.addslashes($row["buyername"]).'\', MPU:\''.addslashes($row["MPU"]).'\', article_group:\''.addslashes($row["articlegroup"]).'\', quantity:\''.$row["quantity"].'\', transactionID:\''.$row["transactionID"].'\', invoiceID:\''.$row["invoiceID"].'\', rAction:\''.$row["rAction"].'\', rReason:\''.$row["rReason"].'\', rReason_detail:\''.$row["rReason_detail"].'\', exchange_MPU:\''.$row["exchange_MPU"].'\', exchange_quantity:\''.$row["exchange_quantity"].'\', date_exchange_sent:\''.$date_exchange_sent.'\', date_order:\''.$date_order.'\', date_announced:\''.$date_announced.'\', date_return:\''.$date_return.'\', date_refund:\''.$date_refund.'\', date_refund_reshipment:\''.$date_refund_reshipment.'\', date_r_IDIMS:\''.$date_r_IDIMS.'\', date_demandEbayClosing1:\''.$date_demandEbayClosing1.'\', date_demandEbayClosing2:\''.$date_demandEbayClosing2.'\', ebayFeeRefundOK:\''.$row["ebay_fee_refund"].'\', refund:\''.number_format($row["refund"], 2, ',', '').'\', refund_reshipment:\''.number_format($row["refund_reshipment"], 2, ',', '').'\'} );" />';
			echo '	</td>';
			echo '</tr>';
			
		} // WHILE ROW
		
	
	} // IF MYSQL_AFFECTED_ROWS
		echo '</table>';
		
?>
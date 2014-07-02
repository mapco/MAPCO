<?php
	// KONVERTIERUNG DATUM IN TIMESTAMP
	function dateConverter($date) {
		
//		echo $date."+";
			if ($date!="") 
			{
				return mktime(0,0,0,number_format(substr($date,3,2)), number_format(substr($date,0,2)), number_format(substr($date,8)));
			}
			else 
			{
				return 0;
			}
			
	}

// EINGABEPRÜFUNG PFLICHTFELDER
		if ( $_POST["platform"]=="") { echo 'Bitte eine Verkaufsplattform auswählen.'; exit; }
		if ( $_POST["rAction"]=="") { echo 'Bitte eine Vorgangsart auswählen.'; exit; }
		if ( $_POST["userid"]=="" and !($_POST["platform"]=="ooe" || $_POST["platform"]=="amazon") ) { echo 'Bitte eine UserID eingeben.'; exit; }
		if ( $_POST["buyerName"]=="") { echo 'Bitte einen Käufernamen eingeben.'; exit; }

		if ( $_POST["MPU"]=="") { echo 'Bitte eine Artikelnummer eingeben.'; exit; }
//		if ( $_POST["article_group"]=="") { echo 'Bitte eine Artikelgruppe eingeben.'; exit; }
//		if ( $_POST["transactionID"]=="") { echo 'Bitte eine Transaction ID eingeben.'; exit; }
		if ( $_POST["quantity"]=="" || !is_numeric($_POST["quantity"]) ) { echo 'Bitte eine gültige Stückzahl eingeben.'; exit; }
		if ( $_POST["rReason"]=="") { echo 'Bitte einen Rückgabe-/Umtauschgrund auswählen.'; exit; }
		if ( $_POST["rReason"]==100 && $_POST["rReason_detail"]=="" ) { echo 'Bitte eine nähere Erläuterung in den Rückgabenotizen angeben.'; exit;} 

// EINGABEPRÜFUNG OPTIONAL
		//Rechnungsnummer prüfen
		if ($_POST["invoiceID"]!="") {
			if ( !is_numeric(substr($_POST["invoiceID"],0,6)) || substr($_POST["invoiceID"],6,1)!="-" || !is_numeric(substr($_POST["invoiceID"],7,4)) )
			{
				echo 'Bitte eine gültige Rechnungsnummer eingeben ( Bsp.: 123456-2012 )'; exit;
			}
		}

		$refund = str_replace(",", ".",$_POST["refund"]);
		if ( $refund!="" && !is_numeric($refund) ) { echo 'Bitte einen gültigen Betrag für die Erstattungssumme (bsp.: 9,99) eingeben.'; exit; }	

		$refund_reshipment = str_replace(",", ".",$_POST["refund_reshipment"]);
		if ( $refund_reshipment!="" && !is_numeric($refund_reshipment) ) { echo 'Bitte einen gültigen Betrag für die Erstattungssumme (Rücksendekosten) (bsp.: 9,99) eingeben.'; exit; }
		

// EINGABEN LEEREN
		$userid=$_POST["userid"];
		if ( $_POST["platform"]=="ooe" || $_POST["platform"]=="amazon" ) { $userid="";}
		
		$exchange_MPU=$_POST["exchange_MPU"];
		$exchange_quantity=$_POST["exchange_quantity"];
		$date_exchange_sent=$_POST["date_exchange_sent"];
		if ( $_POST["rAction"]=="return" )  
			{ $exchange_MPU=""; $exchange_quantity=""; $date_exchange_sent="";}
		else { $date_exchange_sent=dateConverter($date_exchange_sent);}

	// ERSTELLUNG SQL-QUERY STRING
	$sql ="UPDATE shop_returns set ";
	$sql.="state='".$_POST["state"]."', platform='".$_POST["platform"]."', userid='".$userid."', buyername='".$_POST["buyerName"]."', transactionID='".$_POST["transactionID"]."', MPU='".$_POST["MPU"]."', quantity='".$_POST["quantity"]."',  invoiceID='".$_POST["invoiceID"]."', rAction='".$_POST["rAction"]."', rReason='".$_POST["rReason"]."', rReason_detail='".$_POST["rReason_detail"]."', exchange_MPU='".$exchange_MPU."', exchange_quantity='".$exchange_quantity."', date_exchange_sent='".$date_exchange_sent."',date_order=".dateConverter($_POST["date_order"]).", date_announced='".dateConverter($_POST["date_announced"])."', date_return='".dateConverter($_POST["date_return"])."', date_refund='".dateConverter($_POST["date_refund"])."', date_refund_reshipment='".dateConverter($_POST["date_refund_reshipment"])."', ebay_demand_closing1='".dateConverter($_POST["date_demandEbayClosing1"])."', ebay_demand_closing2='".dateConverter($_POST["date_demandEbayClosing2"])."', ebay_fee_refund='".$_POST["ebayFeeRefundOK"]."', refund='".$refund."', refund_reshipment='".$refund_reshipment."', lastmod='".time()."', lastmod_user='".$_SESSION["id_user"]."'";
	$sql.=" WHERE id = ".$_POST["id"];
	
	//echo $sql; exit;
	
q($sql, $dbshop, __FILE__, __LINE__);


?>
<?
	//security check
	session_start();
	if ( !isset($_SESSION["id_user"]) or !($_SESSION["id_user"]>0) ) exit;

	// KONVERTIERUNG DATUM IN TIMESTAMP
	function dateConverter($date) {
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
				echo 'Bitte eine gültige Rechnungsnummer eingeben ( Bsp.: 123456-2013 )'; exit;
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
		if ( $_POST["rAction"]=="return" ) { $exchange_MPU=""; $exchange_quantity=""; $date_exchange_sent="";}


	// ERSTELLUNG SQL-QUERY STRING
	$sql ="INSERT INTO shop_returns (";
	$sql.="state, platform, userid, buyername, MPU, quantity, transactionID, invoiceID, rAction, rReason, rReason_detail, exchange_MPU, exchange_quantity, date_exchange_sent, date_order, date_announced, date_return, date_refund, date_refund_reshipment, ebay_demand_closing1, ebay_demand_closing2, ebay_fee_refund, refund, refund_reshipment, firstmod, firstmod_user, lastmod, lastmod_user";
	$sql.=") VALUES(";
	$sql.="'".$_POST["state"]."',  '".$_POST["platform"]."', '".$userid."', '".$_POST["buyerName"]."', '".$_POST["MPU"]."', '".$_POST["quantity"]."', '".$_POST["transactionID"]."', '".$_POST["invoiceID"]."', '".$_POST["rAction"]."', '".$_POST["rReason"]."', '".$_POST["rReason_detail"]."' , '".$_POST["exchange_MPU"]."', '".$_POST["exchange_quantity"]."', '".dateConverter($_POST["date_exchange_sent"])."', '".dateConverter($_POST["date_order"])."', '".dateConverter($_POST["date_announced"])."', '".dateConverter($_POST["date_return"])."', '".dateConverter($_POST["date_refund"])."', '".dateConverter($_POST["date_refund_reshipment"])."', '".dateConverter($_POST["date_demandEbayClosing1"])."', '".dateConverter($_POST["date_demandEbayClosing2"])."', '".$_POST["ebayFeeRefundOK"]."', '".$refund."', '".$refund_reshipment."' ,'".time()."', '".$_SESSION["id_user"]."', '".time()."', '".$_SESSION["id_user"]."'";
	$sql.=");";


q($sql, $dbshop, __FILE__, __LINE__);


?>
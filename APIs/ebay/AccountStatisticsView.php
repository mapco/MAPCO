<?php
/*	if ( !isset($_POST["id_account"]) )
	{
		echo '<EndItemResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Auktions-ID nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine Auktions-ID übermittelt werden, damit der Service weiß, welche Auktion aktualisiert werden soll.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</EndItemResponse>'."\n";
		exit;
	}

	$results=q("SELECT * FROM ebay_accounts WHERE id_account=".$_POST["id_account"].";", $dbshop, __FILE__, __LINE__);
	if ( mysqli_num_rows($results)==0 )
	{
		echo '<EndItemResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Ebay-Account nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Der angegebene Ebay-Account konnte nicht gefunden werden. Die Account-ID scheint es nicht zu geben.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</EndItemResponse>'."\n";
		exit;
	}
	$account=mysqli_fetch_array($results);
	*/
	
	$from=strtotime($_POST["from"]);
	$to=strtotime($_POST["to"])+(3600*24)-1;
	
//get SUMME From MAPCO
$mapco_subtotal=0;
$res=q("select TransactionPrice, QuantityPurchased from ebay_orders_items where account_id = '1' AND CreatedDateTimestamp>='".$from."' AND CreatedDateTimestamp<='".$to."';",$dbshop, __FILE__, __LINE__);
	//$res=q("select SUM(Subtotal) from ebay_orders where account_id = '1' AND CreatedTimeTimestamp>='".$from."' AND CreatedTimeTimestamp<='".$to."';",$dbshop, __FILE__, __LINE__);
	//$row=mysqli_fetch_array($res);
	while ($row=mysqli_fetch_array($res))
	{
		$mapco_subtotal+=$row["TransactionPrice"]*$row["QuantityPurchased"];
	}
	//$mapco_subtotal=$row[0];
//get ANZAHL ARTIKEL from MAPCO
	$res=q("select SUM(QuantityPurchased) from ebay_orders_items where account_id = '1' AND CreatedDateTimestamp>='".$from."' AND CreatedDateTimestamp<='".$to."';",$dbshop, __FILE__, __LINE__);
	$row=mysqli_fetch_array($res);
	$mapco_quantity=$row[0];

//get SUMME From AP
$ap_subtotal=0;
$res=q("select TransactionPrice, QuantityPurchased from ebay_orders_items where account_id = '2' AND CreatedDateTimestamp>='".$from."' AND CreatedDateTimestamp<='".$to."';",$dbshop, __FILE__, __LINE__);
	//$res=q("select SUM(Subtotal) from ebay_orders where account_id = '2' AND CreatedTimeTimestamp>='".$from."' AND CreatedTimeTimestamp<='".$to."';",$dbshop, __FILE__, __LINE__);
//	$row=mysqli_fetch_array($res);
//	$ap_subtotal=$row[0];
	while ($row=mysqli_fetch_array($res))
	{
		$ap_subtotal+=$row["TransactionPrice"]*$row["QuantityPurchased"];
	}

//get ANZAHL ARTIKEL from AP
	$res=q("select SUM(QuantityPurchased) from ebay_orders_items where account_id = '2' AND CreatedDateTimestamp>='".$from."' AND CreatedDateTimestamp<='".$to."';",$dbshop, __FILE__, __LINE__);
	$row=mysqli_fetch_array($res);
	$ap_quantity=$row[0];
	
	echo '<table>';
//	echo '<colgroup><col width="150px"><col width="150px"></colgroup>';
	echo '<tr>';
	echo '	<th style="width:300px">Zusammenfassung für den Zeitraum <br />'.$_POST["from"].' - '.$_POST["to"].'</th>';
	echo '	<th style="width:140px">';
	echo '	</th>';
	echo '</tr>';
	echo '<tr>';
	echo '	<td><b>Summe Verkäufe MAPCO</b>';
		if (isset($_SESSION["userrole_id"]) && $_SESSION["userrole_id"]=="1") echo '<img alt="Bestellungen von eBay (MAPCO) herunterladen" title="Bestellungen von eBay (MAPCO) herunterladen" src="images/icons/16x16/repeat.png" onclick="orders_update2(\'1\', 0);" style="cursor:pointer; float:right;" />';
	echo '</td><td> € '.number_format($mapco_subtotal,2).'</td></tr>';
	echo '	<td><b>Anzahl verk. Artikel MAPCO</b></td><td>'.$mapco_quantity.'</td></tr>';	
	echo '	<td><b>Summe Verkäufe AP</b>';
		if (isset($_SESSION["userrole_id"]) && $_SESSION["userrole_id"]=="1") echo '<img alt="Bestellungen von eBay (AUTOPARTNER) herunterladen" title="Bestellungen von eBay (AUTOPARTNER) herunterladen" src="images/icons/16x16/repeat.png" onclick="orders_update2(\'2\', 0);" style="cursor:pointer; float:right;" />';
	echo '</td><td> € '.number_format($ap_subtotal,2).'</td></tr>';
	echo '	<td><b>Anzahl verk. Artikel AP</b></td><td>'.$ap_quantity.'</td></tr>';	
	echo '</table>';
	
?>
<?php
	if ( !isset($_POST["id_account"]) )
	{
		echo '<AuctionAddResponse>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Account-ID nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine Auktions-ID oder Auktionsnummer übergeben werden, damit der Service weiß, welche Auktion abgerufen werden soll.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</AuctionAddResponse>'."\n";
		exit;
	}

	if ( !isset($_POST["SKU"]) )
	{
		echo '<AuctionAddResponse>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Artikelnummer nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine Artikelnummer übergeben werden, damit der Service weiß, zu welchem Shopartikel die Auktion gehört.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</AuctionAddResponse>'."\n";
		exit;
	}
	
	if ( !isset($_POST["ItemID"]) )
	{
		echo '<AuctionAddResponse>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Auktionsnummer nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine Auktions-ID oder Auktionsnummer übergeben werden, damit der Service weiß, welche Auktion abgerufen werden soll.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</AuctionAddResponse>'."\n";
		exit;
	}
	
	$results=q("SELECT * FROM ebay_auctions WHERE ItemID=".$_POST["ItemID"].";", $dbshop, __FILE__, __LINE__);
	if( mysqli_num_rows($results)>0 )
	{
		echo '<AuctionAddResponse>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Auktion bereits vorhanden.</shortMsg>'."\n";
		echo '		<longMsg>Zu der angegebenen Auktionsnummer exisitiert bereits eine Auktion in der Datenbank.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</AuctionAddResponse>'."\n";
		exit;
	}

	$results=q("SELECT id_item FROM shop_items WHERE MPN='".$_POST["SKU"]."';", $dbshop, __FILE__, __LINE__);
	if( mysqli_num_rows($results)==0 )
	{
		echo '<AuctionAddResponse>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Artikelnummer unbekannt.</shortMsg>'."\n";
		echo '		<longMsg>Zu der angegebenen Artikelnummer konnte kein gültiger Artikel gefunden werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '	<SKU>'.$_POST["SKU"].'</SKU>'."\n";
		echo '</AuctionAddResponse>'."\n";
		exit;
	}
	$row=mysqli_fetch_array($results);
	$id_item=$row["id_item"];

	q("INSERT INTO ebay_auctions (account_id, shopitem_id, ItemID, SKU, firstmod, firstmod_user, lastmod, lastmod_user) VALUES(".$_POST["id_account"].", ".$id_item.", ".$_POST["ItemID"].", '".$_POST["SKU"]."', ".time().", ".$_SESSION["id_user"].", ".time().", ".$_SESSION["id_user"].");", $dbshop, __FILE__, __LINE__);
	$id_auction=mysqli_insert_id($dbshop);
	
	echo '<AuctionAddResponse>';
	echo '	<Ack>Success</Ack>'."\n";
	echo '	<AuctionID>'.$id_auction.'</AuctionID>';
	echo '</AuctionAddResponse>';

?>
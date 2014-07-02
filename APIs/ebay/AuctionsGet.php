<?php
	if ( !isset($_POST["id_account"]) )
	{
		echo '<AuctionsGetResponse>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Account-ID nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss einen Account-ID übergeben werden, damit der Service weiß, mit welchem Account die Verbindung aufgebaut werden soll.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</AuctionsGetResponse>'."\n";
		exit;
	}

	if ( !isset($_POST["page"]) ) $_POST["page"]=1;

	echo '<AuctionsGetResponse>';
	$results=q("SELECT id_auction FROM ebay_auctions WHERE account_id=".$_POST["id_account"].";", $dbshop, __FILE__, __LINE__);
	echo '	<AuctionCount>'.mysqli_num_rows($results).'</AuctionCount>';
	$results=q("SELECT ItemID, shopitem_id, SKU, StartPrice, Title FROM ebay_auctions WHERE account_id=".$_POST["id_account"]." LIMIT ".(($_POST["page"]-1)*10000).", ".(($_POST["page"]*10000)+1).";", $dbshop, __FILE__, __LINE__);
	$i=0;
	while( $row=mysqli_fetch_array($results) )
	{
		if ( $i==10000 )
		{
			echo '<NextPage>true</NextPage>';
			break;
		}
		echo '<Auction>';
		echo '	<ItemID>'.$row["ItemID"].'</ItemID>';
//		echo '	<ShopitemID>'.$row["shopitem_id"].'</ShopitemID>';
		echo '	<SKU>'.$row["SKU"].'</SKU>';
//		echo '	<StartPrice>'.$row["StartPrice"].'</StartPrice>';
//		echo '	<Title>'.$row["Title"].'</Title>';
		echo '</Auction>';
		$i++;
	}
	if ( $i<10000 ) echo '<NextPage>false</NextPage>';
	echo '</AuctionsGetResponse>';

?>
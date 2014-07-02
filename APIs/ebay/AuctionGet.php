<?php
	if ( !isset($_POST["id_auction"]) and !isset($_POST["ItemID"]) )
	{
		echo '<AuctionGetResponse>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Auktions-ID oder Auktionsnummer nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine Auktions-ID oder Auktionsnummer übergeben werden, damit der Service weiß, welche Auktion abgerufen werden soll.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</AuctionGetResponse>'."\n";
		exit;
	}

	if( isset($_POST["id_auction"]) )
	{
		$results=q("SELECT * FROM ebay_auctions WHERE id_auction=".$_POST["id_auction"].";", $dbshop, __FILE__, __LINE__);
	}
	elseif( isset($_POST["ItemID"]) )
	{
		$results=q("SELECT * FROM ebay_auctions WHERE ItemID=".$_POST["ItemID"]." LIMIT 1;", $dbshop, __FILE__, __LINE__);
	}
	if( mysqli_num_rows($results)==0 )
	{
		echo '<AuctionGetResponse>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Auktion nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Zu der angegebenen Auktions-ID konnte keine Auktion gefunden werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '	<ItemID>'.$_POST["ItemID"].'</ItemID>';
		echo '</AuctionGetResponse>'."\n";
		exit;
	}
	
	$row=mysqli_fetch_array($results);
	$keys=array_keys($row);
	
	echo '<AuctionGetResponse>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	for($i=0; $i<sizeof($keys); $i++)
	{
		if( $keys[$i]!="Description" )
		{
			if( !is_numeric($keys[$i]) )
			{
				echo '	<'.$keys[$i].'><![CDATA['.$row[$keys[$i]].']]></'.$keys[$i].'>'."\n";
			}
		}
	}
	echo '</AuctionGetResponse>'."\n";

?>
<?php
	include("../config.php");
	
	//deactivated
	exit;

	$results=q("SELECT * FROM shop_items WHERE active>0 ORDER BY lastupdate LIMIT 5;", $dbshop, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		//ItemCreateAuction
		$response=post(PATH."soa/", array("API" => "ebay", "Action" => "ItemCreateAuctions", "id_account" => 1, "id_item" => $row["id_item"], "bestoffer" => 1, "id_article" => 199, "ShippingServiceCost" => "5.90"));
		//ItemCreateAuction - read todo from xml
		$todo=array();
		$xml = new SimpleXMLElement($response);
		for($i=0; isset($xml->AuctionID[$i]); $i++)
		{
			$todo[$i]["Action"]=(string)$xml->AuctionID[$i]->attributes()->action;
			$todo[$i]["id_auction"]=(int)$xml->AuctionID[$i];
		}
		
		for($i=0; $i<sizeof($todo); $i++)
		{
			$response=post(PATH."soa/", array("API" => "ebay", "Action" => $todo[$i]["Action"], "id_account" => 1, "id_auction" => $todo[$i]["id_auction"]));
			$xml = new SimpleXMLElement($response);
			if ( $xml->Ack[0]=="Success" )
			{
				if ( $todo[$i]["Action"]=="AddItem" ) echo "Auktion ".$todo[$i]["id_auction"]." erfolgreich erstellt.<br />";
				elseif ( $todo[$i]["Action"]=="ReviseItem" ) echo "Auktion ".$todo[$i]["id_auction"]." erfolgreich aktualisiert.<br />";
				else echo "Auktion ".$todo[$i]["id_auction"]." erfolgreich beendet.<br />";
				$xml2 = new SimpleXMLElement($xml->Response[0]);
				for($j=0; isset($xml2->Fees[0]->Fee[$j]); $j++)
				{
					$name=$xml2->Fees[0]->Fee[$j]->Name[0];
					$fee=$xml2->Fees[0]->Fee[$j]->Fee[0];
					if ( $fee>0 )
					{
						error(__FILE__, __LINE__, "Auktion nicht kostenlos: ".htmlentities($response));
					}
				}
			}
		}
		q("Update shop_items SET lastupdate=".time()." WHERE id_item=".$row["id_item"].";", $dbshop, __FILE__, __LINE__);
	}
?>
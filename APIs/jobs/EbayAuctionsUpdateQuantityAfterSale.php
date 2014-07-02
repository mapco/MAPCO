<?php

	$starttime=time()+microtime();

	$start=time();

	//get all GARTs
	$gart=array();
	$results=q("SELECT MPN, GART FROM shop_items;", $dbshop, __FILE__, __LINE__);
	while( $row=mysqli_fetch_assoc($results) )
	{
		$gart[$row["MPN"]]=$row["GART"]*1;
	}
	
	//GET EBAY_ACCOUNT_SITES WITHOUT MOCOM
	$account_sites = array();
	$res_account_sites = q("SELECT id_accountsite, account_id FROM ebay_accounts_sites WHERE NOT account_id = 11", $dbshop, __FILE__, __LINE__);
	while ( $row_account_sites = mysqli_fetch_assoc( $res_account_sites ) )
	{
		if ( $row_account_sites["account_id"] == 11 )
		{
			$account_sites["mocom"][] = $row_account_sites["id_accountsite"];
		}
		else
		{
			$account_sites["other"][] = $row_account_sites["id_accountsite"];
		}
	}
	unset( $res_account_sites );

	//GET SOLD EBAYITEMS from last 24h
	$sold_items=array();
	$res_sold_ebayitems = q("SELECT * FROM ebay_orders_items WHERE CreatedDateTimestamp > ".(time()-12*3600), $dbshop, __FILE__, LINE__);
	while ($row_sold_ebayitems = mysqli_fetch_assoc($res_sold_ebayitems))
	{
		$sold_items[]=$row_sold_ebayitems["ItemItemID"];
	}
	
	//GET MPN FROM ItemIDs
	$update_items=array();
	$SKU = array();
	$res_auctions = q("SELECT id_auction, SKU, account_id FROM ebay_auctions WHERE NOT upload = 1 AND ItemID IN (".implode(", ", $sold_items).");", $dbshop, __FILE__, __LINE__);
	while ($row_auctions = mysqli_fetch_assoc($res_auctions))
	{
		$update_items[$row_auctions["id_auction"]]["SKU"]=$row_auctions["SKU"];
		$update_items[$row_auctions["id_auction"]]["account_id"]=$row_auctions["account_id"];
		$SKU[]=$row_auctions["SKU"];
	}
	
	//GET LAGERBESTAND
	$SKU_Bestand=array();
	$SKU_Bestand_mocom=array();
	$res_lager = q("SELECT * FROM lager WHERE ArtNr IN ('".implode("', '", $SKU)."')", $dbshop, __FILE__, __LINE__);
	while ($row_lager = mysqli_fetch_assoc($res_lager))
	{
		$SKU_Bestand[$row_lager["ArtNr"]] = ($row_lager["ISTBESTAND"]*1)+($row_lager["MOCOMBESTAND"]*1)+($row_lager["ONLINEBESTAND"]*1);
		$SKU_Bestand_mocom[$row_lager["ArtNr"]] = $row_lager["MOCOMBESTAND"]*1;
	}
	
	$JobResponse="";
	$auction_update = array();
	foreach ($update_items as $id_auction => $data)
	{
		
		if (isset($SKU_Bestand[$data["SKU"]]))
		{
			
			if ( $data["account_id"] == 11 ) // EBAY MOCOM
			{
				$update_account_sites = $account_sites["mocom"];
				$bestand = $SKU_Bestand_mocom[$data["SKU"]];
			}
			else
			{
				$update_account_sites = $account_sites["other"];
				$bestand = $SKU_Bestand[$data["SKU"]];
			}
		
			if ( $bestand > 14)
			{
				$auction_update[$id_auction] = 20;
				q_update("ebay_auctions", array("Call" => "ReviseItem", "upload" => 1, "Quantity" => 15), "WHERE id_auction = ".$id_auction, $dbshop, __FILE__, __LINE__);
				$JobResponse.="Ebay Auction ".$id_auction." (SKU ".$data["SKU"].") auf Bestand 15 gesetzt (update ebay_auctions -> affected rows = ".mysqli_affected_rows($dbshop)."\n";
				
			}
			elseif ( $bestand > 2 )
			{
				//$auction_update[$id_auction]=$SKU_Bestand[$sku];
				//ALLE ARTIKEL AUF AKTUELLEN BESTAND SETZEN
				if ($data["SKU"] != "")
				{
							//	q_update("ebay_auctions", array("Call" => "ReviseItem", "upload" => 1, "Quantity" => $SKU_Bestand[$data["SKU"]]), "WHERE SKU = '".$data["SKU"]."'", $dbshop, __FILE__, __LINE__);
					q_update("ebay_auctions", array("Call" => "ReviseItem", "upload" => 1, "Quantity" => $bestand), "WHERE SKU = '".$data["SKU"]."' AND accountsite_id IN (".implode(", ", $update_account_sites ).")", $dbshop, __FILE__, __LINE__);

					$JobResponse.="Ebay Auctions mit SKU ".$data["SKU"]." auf Bestand ".$bestand." gesetzt für ebay_accountsite_ids ".implode(", ", $update_account_sites )." (update ebay_auctions -> affected rows = ".mysqli_affected_rows($dbshop)."\n";

				}
			}
			elseif ( $bestand < 3 )
			{
				if ( $gart[$data["SKU"]] == 286) 
				{
					if ( $bestand > 0 ) 
					{
						//$auction_update[$id_auction]=$SKU_Bestand[$sku];
						if ( $data["SKU"] != "")
						{
					//		q_update("ebay_auctions", array("Call" => "ReviseItem", "upload" => 1, "Quantity" => $SKU_Bestand[$data["SKU"]]), "WHERE SKU = '".$data["SKU"]."'" , $dbshop, __FILE__, __LINE__);
							q_update("ebay_auctions", array("Call" => "ReviseItem", "upload" => 1, "Quantity" => $bestand), "WHERE SKU = '".$data["SKU"]."' AND accountsite_id IN (".implode(", ", $update_account_sites ).")" , $dbshop, __FILE__, __LINE__);
							$JobResponse.="Ebay Auctions mit SKU ".$data["SKU"]." für ebay_accountsite_ids ".implode(", ", $update_account_sites )." auf Bestand ".$bestand." gesetzt (AUSNAHMEREGEL FÜR GART 286)(update ebay_auctions -> affected rows = ".mysqli_affected_rows($dbshop)."\n";
						}
					}
					else
					{
						//ALLE BEENDEN AUCH MOCOM
						if ( $data["SKU"] != "")
						{
							q_update("ebay_auctions", array("Call" => "EndItem", "upload" => 1), "WHERE SKU = '".$data["SKU"]."'", $dbshop, __FILE__, __LINE__);
							$JobResponse.="Ebay Auctions mit SKU ".$data["SKU"]." auf Beenden gesetzt wegen Bestand ".$bestand."  (update ebay_auctions -> affected rows = ".mysqli_affected_rows($dbshop)."\n";
						}
					}
				}
				else
				{
					//$auction_update[$id_auction]=0;
					if ($data["SKU"] != "")
					{
						//q_update("ebay_auctions", array("Call" => "EndItem", "upload" => 1), "WHERE SKU = '".$data["SKU"]."'", $dbshop, __FILE__, __LINE__);
						q_update("ebay_auctions", array("Call" => "EndItem", "upload" => 1), "WHERE SKU = '".$data["SKU"]."' AND accountsite_id IN (".implode(", ", $update_account_sites ).")", $dbshop, __FILE__, __LINE__);
						$JobResponse.="Ebay Auctions mit SKU ".$data["SKU"]." für ebay_accountsite_ids ".implode(", ", $update_account_sites )." auf Beenden gesetzt wegen Bestand kleiner 3  (update ebay_auctions -> affected rows = ".mysqli_affected_rows($dbshop)."\n";

					}
				}
			}
		}
	}
	
//	print_r($auction_update);
echo 'JobResponse: '.$JobResponse.'<br />';
echo '<b>SCRIPTLAUFZEIT: '.(time()-$start).' Sekunden</b><br />';
?>
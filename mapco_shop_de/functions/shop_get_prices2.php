<?php
	///äöüÄÖÜ UTF-8
	if (!function_exists("get_prices"))
	{
		include("mapco_gewerblich.php");
		function get_prices($id_item, $amount=1, $customer_id=0)
		{
			global $dbweb;
			global $dbshop;
			$price = array();
			$price["total"] = 0;
			$price["VAT"] = 19;
						
			if ( $customer_id==0 and isset($_SESSION["id_user"]) ) $customer_id=$_SESSION["id_user"];
			
			//get artnr
			$results=q("SELECT * FROM shop_items WHERE id_item='".$id_item."';", $dbshop, __FILE__, __LINE__);
			$row=mysqli_fetch_array($results);
			$artnr=$row["MPN"];
	
/*
			//get discount
			$price["discount"]=0;
			$results=q("SELECT * FROM shop_offers WHERE item_id='".$id_item."' AND `from`<".time()." AND `until`>".time().";", $dbshop, __FILE__, __LINE__);
			if (mysqli_num_rows($results))
			{
				$row=mysqli_fetch_array($results);
				$price["discount"]=$row["discount"];
			}
*/
	
			//Altteilpfand?
			$price["collateral"]=0;
			$results=q("SELECT * FROM t_200 WHERE ArtNr='".$artnr."';", $dbshop, __FILE__, __LINE__);
			$row=mysqli_fetch_array($results);
			$price["collateral"]=$row["ATWERT"];
	
			//Gewerbskunde?
			$gewerblich=false;
//			if( $customer_id>0 ) $gewerblich=gewerblich($customer_id);
	
			//Preislisten auslesen
			if ($customer_id>0)
			{
				$results=q("SELECT * FROM cms_users WHERE id_user=".$customer_id.";", $dbweb, __FILE__, __LINE__);
				$row=mysqli_fetch_array($results);
				$results=q("SELECT * FROM kunde WHERE ADR_ID='".$row["idims_adr_id"]."';", $dbshop, __FILE__, __LINE__);
				if( mysqli_num_rows($results)>0 )
				{
					$row=mysqli_fetch_array($results);
					if ($row["GEWERBE"]>0) $gewerblich=true;
					if ($row["PL1"]<4) $row["PL1"]=4;
					$kun_id=$row["IDIMS_ID"];
				}
			}
			
			//Preis bestimmen
			$pl=array();

			$price["brutto"] = 0;
			$price["percent"] = 0;
			$price["total_fr"] = 9999;

			//Bruttopreisliste
			$results2=q("SELECT * FROM prpos WHERE ARTNR='".$artnr."' AND LST_NR='0';", $dbshop, __FILE__, __LINE__);
			if (mysqli_num_rows($results2)>0)
			{
				$row2=mysqli_fetch_array($results2);
				if ($row2["POS_0_WERT"]>0)
				{
					$price["brutto"] = $row2["POS_0_WERT"];
				}
			}

			//Rote Liste
			$red_price=0;
			$results2=q("SELECT * FROM prpos WHERE ARTNR='".$artnr."' AND LST_NR='7';", $dbshop, __FILE__, __LINE__);
			if (mysqli_num_rows($results2)>0)
			{
				$row2=mysqli_fetch_array($results2);
				if ($row2["POS_0_WERT"]>0)
				{
					$red_price = $row2["POS_0_WERT"];
				}
			}
			
			//Rabatt-Daten
			$percentage=0;
			//$act_lists=array();
			$res=q("SELECT * FROM shop_offers WHERE offer_start<=".time()." AND offer_end>=".time().";", $dbshop, __FILE__, __LINE__);
			if(mysqli_num_rows($res)>0)
			{
				while($shop_offers=mysqli_fetch_assoc($res))
				{
					$res2=q("SELECT * FROM shop_lists_items WHERE list_id=".$shop_offers["list_id"]." AND item_id=".$id_item.";", $dbshop, __FILE__, __LINE__);
					if(mysqli_num_rows($res2)>0)
					{
						$percentage=$shop_offers["percentage"];
						break;
					}
				}
			}
			
			//get Ebay Price for DE, AT and CH
			$ebay_price = 9999;
/*
			if ($_SESSION["origin"]=="DE" or $_SESSION["origin"]=="AT" or $_SESSION["origin"]=="CH")
//			if ($_SESSION["origin"]=="DE")
			{
				$results_ebay=q("SELECT POS_0_WERT FROM prpos WHERE ARTNR='".$artnr."' AND LST_NR='16815';", $dbshop, __FILE__, __LINE__);
				if (mysqli_num_rows($results_ebay)>0)
				{
					$row_ebay=mysqli_fetch_array($results_ebay);
					if ($row_ebay["POS_0_WERT"]>0)
					{
						//get yellow Price
						$results_yellow=q("SELECT POS_0_WERT FROM prpos WHERE ARTNR='".$artnr."' AND LST_NR='5';", $dbshop, __FILE__, __LINE__);
						if (mysqli_num_rows($results_yellow)>0)
						{
							$row_yellow=mysqli_fetch_array($results_yellow);
							if ($row_yellow["POS_0_WERT"]>0)
							{
								if ($row_ebay["POS_0_WERT"]<$row_yellow["POS_0_WERT"]) $ebay_price = $row_ebay["POS_0_WERT"];
							}
							else $ebay_price = $row_ebay["POS_0_WERT"]; //yellow price 0 Euro
						}
						else $ebay_price = $row_ebay["POS_0_WERT"]; //no yellow price
					}
				}
			}
			
			//Ebay Price exeptions
			if (isset($kun_id) and $kun_id>0)
			{
				if ($kun_id==19359) $ebay_price = 9999; //Kunden von ATC Herne
			}			
*/

			//Shop Preisliste oder Grüne Liste
			$results_pl=q("SELECT pricelist FROM shop_shops WHERE id_shop=".$_SESSION["id_shop"].";", $dbshop, __FILE__, __LINE__);
			$row_pl=mysqli_fetch_array($results_pl);
			$results2=q("SELECT * FROM prpos WHERE ARTNR='".$artnr."' AND LST_NR IN ('".$row_pl["pricelist"]."', 4) ORDER BY POS_0_PE, POS_0_WERT;", $dbshop, __FILE__, __LINE__);
			if (mysqli_num_rows($results2)>0)
			{
				while($row2=mysqli_fetch_array($results2))
				{
					if ($row2["POS_0_WERT"]>0)
					{
						//Rabatt
						if($percentage>0 and $red_price>0)
						{
							$price_perc=$row2["POS_0_WERT"]*(1-($percentage/100));
							if($price_perc>=$red_price) $row2["POS_0_WERT"]=$price_perc;
						}
						
						if ($amount>=$row2["POS_0_PE"])
						{
							$price["net"] = $row2["POS_0_WERT"];
							if ($ebay_price<$price["net"])
							{
	//							$price["offline_net"] = $price["net"];
								$price["net"] = $ebay_price;
							}
							$price["gross"] = $price["net"]*((100+$price["VAT"])/100);
							$price["total"] = $price["gross"];
							if ($price["brutto"]>0)
							{
								$price["total_fr"] = $price["brutto"];
								$price["percent"] = (100/$price["brutto"])*($price["brutto"]-$price["total"]);
							}
							if($price["percent"]<0) $price["percent"]=0;
							$price["collateral_total"] = $price["collateral"]*((100+$price["VAT"])/100);
						}
					}
					if ($price["total"]>0) break;
				}
			}
			
			//Bruttopreisliste
			if ($price["total"]<=0 and $price["brutto"]>0)
			{
				$price["net"] = $price["brutto"]/((100+$price["VAT"])/100);
				
				//Rabatt
				if($percentage>0 and $red_price>0)
				{
					$price_perc=$price["net"]*(1-($percentage/100));
					if($price_perc>=$red_price) $price["net"]=$price_perc;
				}
				
				if ($ebay_price<$price["net"])
				{
					$price["offline_net"] = $price["net"];
					$price["net"] = $ebay_price;
				}
				$price["gross"] = $price["brutto"];
				$price["total"] = $price["net"];
				$price["total_fr"] = $price["net"];
				if ($price["brutto"]>0)
				{
					$price["percent"] = (100/$price["brutto"])*($price["brutto"]-$price["total"]);
				}
				if($price["percent"]<0) $price["percent"]=0;
				$price["collateral_total"] = $price["collateral"];
			}
			elseif ($price["total"]<=0)
			{
				//return best price
				$price["net"] = 9999;
				$price["gross"] = $price["net"]*((100+$price["VAT"])/100);
				$price["total"] = $price["gross"];
				$price["percent"] = 0;
				$price["collateral_total"] = $price["collateral"]*((100+$price["VAT"])/100);
			}

			if ($gewerblich)
			{
				//Preisliste 1 + Preisliste 2 + Standardliste
				$results2=q("SELECT * FROM prpos WHERE ARTNR='".$artnr."' AND LST_NR IN '".$row["PL1"]."', '".$row["PL2"]."', '".$row["PREISGR"]."') ORDER BY POS_0_PE, POS_0_WERT;", $dbshop, __FILE__, __LINE__);
				if (mysqli_num_rows($results2)>0)
				{
					while($row2=mysqli_fetch_array($results2))
					{
						if ($row2["POS_0_WERT"]>0)
						{
							if ($amount>=$row2["POS_0_PE"])
							{
								if($price["net"]>$row2["POS_0_WERT"]) $price["net"] = $row2["POS_0_WERT"];
								if ($ebay_price<$price["net"])
								{
									$price["offline_net"] = $price["net"];
									$price["net"] = $ebay_price;
								}
								$price["gross"] = $price["net"]*((100+$price["VAT"])/100);
								$price["total"] = $price["net"];
								$price["total_fr"] = $price["net"];
								if ($price["brutto"]>0)
								{
									$price["percent"] = (100/$price["brutto"])*($price["brutto"]-$price["total"]);
								}
								if($price["percent"]<0) $price["percent"]=0;
								$price["collateral_total"] = $price["collateral"];
								break;
							}
							else
							{
								$price["season_price"][] = $row2["POS_0_WERT"];
								$price["season_amount"][] = $row2["POS_0_PE"];
							}
						}
					}
				}
			} // end if gewerblich

			return($price);

		} // end function
	} // end exist
?>
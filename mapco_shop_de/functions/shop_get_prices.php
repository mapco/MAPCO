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
	
			//Preislisten auslesen wenn Gewerbekunde
			$gewerblich=false;
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
			
			//IM GÜLTIGKEITSZEITRAUM VON AKTIONSLISTEN?
			$time=0;
			$res4=q("SELECT * FROM shop_offers WHERE offer_start<=".time()." AND offer_end>=".time().";", $dbshop, __FILE__, __LINE__);
			if(mysqli_num_rows($res4)>0)
			{
				while($shop_offers=mysqli_fetch_assoc($res4))
				{
					$queryShopLists = "SELECT * FROM shop_lists_items WHERE list_id = '" . $shop_offers["list_id"] . "' AND item_id = '".$id_item."';";
					$res5 = q($queryShopLists, $dbshop, __FILE__, __LINE__);
					if (mysqli_num_rows($res5) > 0) {
						$time = 1;
						break;
					}
				}
			}
			
			//RABATT-TYP AUSWÄHLEN 1=HÄNDLERPREISLISTE(20214) 2=WERKSTATTPREISLISTE(20215) 3=AKTIONSPREISLISTE(N)
			$percentage=0;
			$pl_type=3;
			$price_dealer=0;
			$price_garage=0;
			if( (($_SESSION["id_site"]>=8 and $_SESSION["id_site"]<=15) or $_SESSION["id_site"]==17) and isset($_SESSION["id_user"]) and $time==1)
			{
				//if($_SESSION["id_user"]==49352)
				{
					if(isset($row["PREISGR"]) and ($row["PREISGR"]==6 or $row["PREISGR"]==7)) //DEALER
					{
						//$res3=q("SELECT * FROM prpos WHERE LST_NR=20214 AND ARTNR='".$artnr."' AND GUELTIG_AB<='".date("Y-m-d", time())."' AND GUELTIG_BIS>='".date("Y-m-d", time())."';", $dbshop, __FILE__, __LINE__);
						$res3=q("SELECT * FROM prpos WHERE LST_NR=20214 AND ARTNR='".$artnr."';", $dbshop, __FILE__, __LINE__);
						if(mysqli_num_rows($res3)>0)
						{
							$prpos=mysqli_fetch_assoc($res3);
							$price_dealer=$prpos["POS_0_WERT"];
							$pl_type=1;
						}
					}
					elseif(isset($row["PREISGR"]) and ($row["PREISGR"]==3 or $row["PREISGR"]==4 or $row["PREISGR"]==5)) //GARAGE
					{
						//$res3=q("SELECT * FROM prpos WHERE LST_NR=20215 AND ARTNR='".$artnr."' AND GUELTIG_AB<='".date("Y-m-d", time())."' AND GUELTIG_BIS>='".date("Y-m-d", time())."';", $dbshop, __FILE__, __LINE__);
						$res3=q("SELECT * FROM prpos WHERE LST_NR=20215 AND ARTNR='".$artnr."';", $dbshop, __FILE__, __LINE__);
						if(mysqli_num_rows($res3)>0)
						{
							$prpos=mysqli_fetch_assoc($res3);
							$price_garage=$prpos["POS_0_WERT"];
							$pl_type=2;
						}
					}
				}
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
			}
			
			//GET COUNTRY DATA AND SET VAT
			$eu=1;
			if(isset($_SESSION["bill_country_id"]))
			{
				$res6=q("SELECT * FROM shop_countries WHERE id_country=".$_SESSION["bill_country_id"], $dbshop, __FILE__, __LINE__);
				if(mysqli_num_rows($res6)==1)
				{
					$shop_countries=mysqli_fetch_assoc($res6);
					$eu=$shop_countries["EU"];
					$price["VAT"]=$shop_countries["VAT"];
				}
			}
			elseif(isset($_SESSION["origin"]))
			{
				$res7=q("SELECT * FROM shop_countries WHERE country_code='".$_SESSION["origin"]."'", $dbshop, __FILE__, __LINE__);
				if(mysqli_num_rows($res7)==1)
				{
					$shop_countries=mysqli_fetch_assoc($res7);
					$eu=$shop_countries["EU"];
					$price["VAT"]=$shop_countries["VAT"];
				}
			}
			//autopartner und franchise Korrektur
			if( $_SESSION['id_shop'] == 2 or $_SESSION['id_shop'] == 4 or $_SESSION['id_shop'] == 6 or $_SESSION['id_shop'] == 20 or $_SESSION['id_shop'] == 21 ) {
				$price['VAT'] = 19;
			}
			if ( $_SESSION[ 'id_shop' ] == 19 ) {
				$price[ 'VAT' ] = 21;
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

			if ($gewerblich)
			{
				//Preisliste 1
				$results2=q("SELECT * FROM prpos WHERE ARTNR='".$artnr."' AND LST_NR='".$row["PL1"]."' ORDER BY POS_0_PE;", $dbshop, __FILE__, __LINE__);
				if (mysqli_num_rows($results2)>0)
				{
					while($row2=mysqli_fetch_array($results2))
					{
						if ($row2["POS_0_WERT"]>0)
						{
							//Rabatt
							$price["percentage"]=0;
							if($pl_type==1)
							{
								if($row2["POS_0_WERT"]>$price_dealer)
								{
									$price["percentage"]=($row2["POS_0_WERT"]-$price_dealer)/($row2["POS_0_WERT"]/100);
									$row2["POS_0_WERT"]=$price_dealer;
								}
							}
							if($pl_type==2)
							{
								if($row2["POS_0_WERT"]>$price_garage)
								{
									$price["percentage"]=($row2["POS_0_WERT"]-$price_garage)/($row2["POS_0_WERT"]/100);
									$row2["POS_0_WERT"]=$price_garage;
								}
							}
							if($pl_type==3 and $percentage>0 and $red_price>0)
							{
								if($row2["POS_0_WERT"]>$red_price)
								{
									$price["percentage"]=$percentage;
									$price_perc=$row2["POS_0_WERT"]*(1-($percentage/100));
									if($price_perc>=$red_price) $row2["POS_0_WERT"]=$price_perc;
									if($price_perc<$red_price)
									{
										$price["percentage"]=($row2["POS_0_WERT"]-$red_price)/($row2["POS_0_WERT"]/100);
										$row2["POS_0_WERT"]=$red_price;
									}
								}
							}
							
							if ($amount>=$row2["POS_0_PE"])
							{
								$price["net"] = $row2["POS_0_WERT"];
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
								return($price);
							}
							else
							{
								$price["season_price"][] = $row2["POS_0_WERT"];
								$price["season_amount"][] = $row2["POS_0_PE"];
							}
						}
					}
				}
	//			echo '1: '.$pl[0].'<br />';
				
				//Preisliste 2
				$results2=q("SELECT * FROM prpos WHERE ARTNR='".$artnr."' AND LST_NR='".$row["PL2"]."';", $dbshop, __FILE__, __LINE__);
				if (mysqli_num_rows($results2)>0)
				{
					while($row2=mysqli_fetch_array($results2))
					{
						if ($row2["POS_0_WERT"]>0)
						{
							//Rabatt
							$price["percentage"]=0;
							if($pl_type==1)
							{
								if($row2["POS_0_WERT"]>$price_dealer)
								{
									$price["percentage"]=($row2["POS_0_WERT"]-$price_dealer)/($row2["POS_0_WERT"]/100);
									$row2["POS_0_WERT"]=$price_dealer;
								}
							}
							if($pl_type==2)
							{
								if($row2["POS_0_WERT"]>$price_garage)
								{
									$price["percentage"]=($row2["POS_0_WERT"]-$price_garage)/($row2["POS_0_WERT"]/100);
									$row2["POS_0_WERT"]=$price_garage;
								}
							}
							if($pl_type==3 and $percentage>0 and $red_price>0)
							{
								if($row2["POS_0_WERT"]>$red_price)
								{
									$price["percentage"]=$percentage;
									$price_perc=$row2["POS_0_WERT"]*(1-($percentage/100));
									if($price_perc>=$red_price) $row2["POS_0_WERT"]=$price_perc;
									if($price_perc<$red_price)
									{
										$price["percentage"]=($row2["POS_0_WERT"]-$red_price)/($row2["POS_0_WERT"]/100);
										$row2["POS_0_WERT"]=$red_price;
									}
								}
							}
							if ($amount>=$row2["POS_0_PE"])
							{
								$price["net"] = $row2["POS_0_WERT"];
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
								return($price);
							}
						}
					}
				}
	//			echo '2:'.$pl[1].'<br />';
				
				//Preisgruppe (Listenpreis)
				$results2=q("SELECT * FROM prpos WHERE ARTNR='".$artnr."' AND LST_NR='".$row["PREISGR"]."';", $dbshop, __FILE__, __LINE__);
				if (mysqli_num_rows($results2)>0)
				{
					$row2=mysqli_fetch_array($results2);
					if ($row2["POS_0_WERT"]>0)
					{
						
						//Rabatt
						$price["percentage"]=0;
						
						if($pl_type==1)
						{
							if($row2["POS_0_WERT"]>$price_dealer)
							{
								$price["percentage"]=($row2["POS_0_WERT"]-$price_dealer)/($row2["POS_0_WERT"]/100);
								$row2["POS_0_WERT"]=$price_dealer;
							}
						}
						if($pl_type==2)
						{
							if($row2["POS_0_WERT"]>$price_garage)
							{
								$price["percentage"]=($row2["POS_0_WERT"]-$price_garage)/($row2["POS_0_WERT"]/100);
								$row2["POS_0_WERT"]=$price_garage;
							}
						}
						if($pl_type==3 and $percentage>0 and $red_price>0)
						{
							if($row2["POS_0_WERT"]>$red_price)
							{
								$price["percentage"]=$percentage;
								$price_perc=$row2["POS_0_WERT"]*(1-($percentage/100));
								if($price_perc>=$red_price) $row2["POS_0_WERT"]=$price_perc;
								if($price_perc<$red_price)
								{
									$price["percentage"]=($row2["POS_0_WERT"]-$red_price)/($row2["POS_0_WERT"]/100);
									$row2["POS_0_WERT"]=$red_price;
								}
							}
						}
						
						if ($amount>=$row2["POS_0_PE"])
						{
							$price["net"] = $row2["POS_0_WERT"];
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
							return($price);
						}
					}
				}
	//			echo '3: '.$pl[2].'<br />';
				
				//Bruttopreisliste
				if ($price["brutto"]>0)
				{
					$price["net"] = $price["brutto"]/((100+$price["VAT"])/100);
					
					//Rabatt
					$price["percentage"]=0;
					if($pl_type==1)
					{
						if($price["net"]>$price_dealer)
						{
							$price["percentage"]=($price["net"]-$price_dealer)/($price["net"]/100);
							$price["net"]=$price_dealer;
						}
					}
					if($pl_type==2)
					{
						if($price["net"]>$price_garage)
						{
							$price["percentage"]=($price["net"]-$price_garage)/($price["net"]/100);
							$price["net"]=$price_garage;
						}
					}
					if($pl_type==3 and $percentage>0 and $red_price>0)
					{
						if($price["net"]>$red_price)
						{
							$price["percentage"]=$percentage;
							$price_perc=$price["net"]*(1-($percentage/100));
							if($price_perc>=$red_price) $price["net"]=$price_perc;
							if($price_perc<$red_price)
							{
								$price["percentage"]=($price["net"]-$red_price)/($price["net"]/100);
								$price["net"]=$red_price;
							}
						}
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
					return($price);
				}
	//			echo '4: '.$pl[3].'<br />';
				
				//return best price
				$price["net"] = 9999;
				$price["gross"] = $price["net"]*((100+$price["VAT"])/100);
				$price["total"] = $price["net"];
				$price["total_fr"] = $price["net"];
				$price["percent"] = 0;
				$price["collateral_total"] = $price["collateral"];
				return($price);

			} // end if gewerblich
			else
			{
				//Shop Preisliste
				$results_pl=q("SELECT pricelist FROM shop_shops WHERE id_shop=".$_SESSION["id_shop"].";", $dbshop, __FILE__, __LINE__);
				$row_pl=mysqli_fetch_array($results_pl);
				$results2=q("SELECT * FROM prpos WHERE ARTNR='".$artnr."' AND LST_NR='".$row_pl["pricelist"]."';", $dbshop, __FILE__, __LINE__);
				if (mysqli_num_rows($results2)>0)
				{
					$row2=mysqli_fetch_array($results2);
					if ($row2["POS_0_WERT"]>0)
					{
						//Rabatt
						$price["percentage"]=0;
						if($pl_type==1)
						{
							if($row2["POS_0_WERT"]>$price_dealer)
							{
								$price["percentage"]=($row2["POS_0_WERT"]-$price_dealer)/($row2["POS_0_WERT"]/100);
								$row2["POS_0_WERT"]=$price_dealer;
							}
						}
						if($pl_type==2)
						{
							if($row2["POS_0_WERT"]>$price_garage)
							{
								$price["percentage"]=($row2["POS_0_WERT"]-$price_garage)/($row2["POS_0_WERT"]/100);
								$row2["POS_0_WERT"]=$price_garage;
							}
						}
						if($pl_type==3 and $percentage>0 and $red_price>0)
						{
							if($row2["POS_0_WERT"]>$red_price)
							{
								$price["percentage"]=$percentage;
								$price_perc=$row2["POS_0_WERT"]*(1-($percentage/100));
								if($price_perc>=$red_price) $row2["POS_0_WERT"]=$price_perc;
								if($price_perc<$red_price)
								{
									$price["percentage"]=($row2["POS_0_WERT"]-$red_price)/($row2["POS_0_WERT"]/100);
									$row2["POS_0_WERT"]=$red_price;
								}
							}
						}
						
						if ($amount>=$row2["POS_0_PE"])
						{
							$price["net"] = $row2["POS_0_WERT"];
							if ($ebay_price<$price["net"])
							{
//								$price["offline_net"] = $price["net"];
								$price["net"] = $ebay_price;
							}
							$price["gross"] = $price["net"]*((100+$price["VAT"])/100);
							$price["total"] = $price["gross"];
							if($eu==0) $price["total"]=$price["net"];
							if ($price["brutto"]>0)
							{
								$price["total_fr"] = $price["brutto"];
								$price["percent"] = (100/$price["brutto"])*($price["brutto"]-$price["total"]);
							}
							if($price["percent"]<0) $price["percent"]=0;
							if($eu==0)
								$price["collateral_total"] = $price["collateral"];
							else
								$price["collateral_total"] = $price["collateral"]*((100+$price["VAT"])/100);
							return($price);
						}
					}
				}

				//grüne Liste
				$results2=q("SELECT * FROM prpos WHERE ARTNR='".$artnr."' AND LST_NR='4';", $dbshop, __FILE__, __LINE__);
				if (mysqli_num_rows($results2)>0)
				{
					$row2=mysqli_fetch_array($results2);
					if ($row2["POS_0_WERT"]>0)
					{
						//Rabatt
						$price["percentage"]=0;
						if($pl_type==1)
						{
							if($row2["POS_0_WERT"]>$price_dealer)
							{
								$price["percentage"]=($row2["POS_0_WERT"]-$price_dealer)/($row2["POS_0_WERT"]/100);
								$row2["POS_0_WERT"]=$price_dealer;
							}
						}
						if($pl_type==2)
						{
							if($row2["POS_0_WERT"]>$price_garage)
							{
								$price["percentage"]=($row2["POS_0_WERT"]-$price_garage)/($row2["POS_0_WERT"]/100);
								$row2["POS_0_WERT"]=$price_garage;
							}
						}
						if($pl_type==3 and $percentage>0 and $red_price>0)
						{
							if($row2["POS_0_WERT"]>$red_price)
							{
								$price["percentage"]=$percentage;
								$price_perc=$row2["POS_0_WERT"]*(1-($percentage/100));
								if($price_perc>=$red_price) $row2["POS_0_WERT"]=$price_perc;
								if($price_perc<$red_price)
								{
									$price["percentage"]=($row2["POS_0_WERT"]-$red_price)/($row2["POS_0_WERT"]/100);
									$row2["POS_0_WERT"]=$red_price;
								}
							}
						}
						
						if ($amount>=$row2["POS_0_PE"])
						{
							$price["net"] = $row2["POS_0_WERT"];
							if ($ebay_price<$price["net"])
							{
//								$price["offline_net"] = $price["net"];
								$price["net"] = $ebay_price;
							}
							$price["gross"] = $price["net"]*((100+$price["VAT"])/100);
							$price["total"] = $price["gross"];
							if($eu==0) $price["total"]=$price["net"];
							if ($price["brutto"]>0)
							{
								$price["total_fr"] = $price["brutto"];
								$price["percent"] = (100/$price["brutto"])*($price["brutto"]-$price["total"]);
							}
							if($price["percent"]<0) $price["percent"]=0;
							if($eu==0)
								$price["collateral_total"] = $price["collateral"];
							else
								$price["collateral_total"] = $price["collateral"]*((100+$price["VAT"])/100);
							return($price);
						}
					}
				}
				
				//Bruttopreisliste
				if ($price["brutto"]>0)
				{
					$price["net"] = $price["brutto"]/((100+$price["VAT"])/100);
					
					//Rabatt
					$price["percentage"]=0;
					if($pl_type==1)
					{
						if($price["net"]>$price_dealer)
						{
							$price["percentage"]=($price["net"]-$price_dealer)/($price["net"]/100);
							$price["net"]=$price_dealer;
						}
					}
					if($pl_type==2)
					{
						if($price["net"]>$price_garage)
						{
							$price["percentage"]=($price["net"]-$price_garage)/($price["net"]/100);
							$price["net"]=$price_garage;
						}
					}
					if($pl_type==3 and $percentage>0 and $red_price>0)
					{
						if($price["net"]>$red_price)
						{
							$price["percentage"]=$percentage;
							$price_perc=$price["net"]*(1-($percentage/100));
							if($price_perc>=$red_price) $price["net"]=$price_perc;
							if($price_perc<$red_price)
							{
								$price["percentage"]=($price["net"]-$red_price)/($price["net"]/100);
								$price["net"]=$red_price;
							}
						}
					}
					
					if ($ebay_price<$price["net"])
					{
//						$price["offline_net"] = $price["net"];
						$price["net"] = $ebay_price;
					}
					$price["gross"] = $price["net"]*((100+$price["VAT"])/100);
					$price["total"] = $price["gross"];
					if($eu==0) $price["total"]=$price["net"];
					$price["total_fr"] = $price["gross"];
					$price["percent"] = (100/$price["brutto"])*($price["brutto"]-$price["total"]);
					if($price["percent"]<0) $price["percent"]=0;
					if($eu==0)
						$price["collateral_total"] = $price["collateral"];
					else
						$price["collateral_total"] = $price["collateral"]*((100+$price["VAT"])/100);
					return($price);
				}
				
				//return best price
				$price["net"] = 9999;
				$price["gross"] = $price["net"]*((100+$price["VAT"])/100);
				$price["total"] = $price["gross"];
				if($eu==0) $price["total"]=$price["net"];
				$price["percent"] = 0;
				if($eu==0)
					$price["collateral_total"] = $price["collateral"];
				else
					$price["collateral_total"] = $price["collateral"]*((100+$price["VAT"])/100);
				return($price);
			} // end if nicht gewerblich
		} // end function
	} // end exist
?>
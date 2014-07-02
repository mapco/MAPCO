<?php
	if (!function_exists("get_price"))
	{
		function get_price($id_item, $amount=1, $customer_id=0)
		{
			global $dbweb;
			global $dbshop;
			
			if (!($customer_id>0)) $customer_id=$_SESSION["id_user"];
			
			//get artnr
			$results=q("SELECT * FROM shop_items WHERE id_item='".$id_item."';", $dbshop, __FILE__, __LINE__);
			$row=mysql_fetch_array($results);
			$artnr=$row["MPN"];
	
			//get discount
			$discount=0;
			$results=q("SELECT * FROM shop_offers WHERE item_id='".$id_item."' AND `from`<".time()." AND `until`>".time().";", $dbshop, __FILE__, __LINE__);
			if (mysql_num_rows($results))
			{
				$row=mysql_fetch_array($results);
				$discount=$row["percent"];
			}
	
			//Altteilpfand?
			$atwert=0;
			$results=q("SELECT * FROM t_200 WHERE ArtNr='".$artnr."';", $dbshop, __FILE__, __LINE__);
			$row=mysql_fetch_array($results);
			$atwert=$row["ATWERT"];
	
			//Gewerbskunde?
			$gewerblich=gewerblich($customer_id);
	
			//Preislisten auslesen
			if ($customer_id>0)
			{
				$results=q("SELECT * FROM cms_users WHERE id_user=".$customer_id.";", $dbweb, __FILE__, __LINE__);
				$row=mysql_fetch_array($results);
				$results=q("SELECT * FROM kunde WHERE KUND_NR='".$row["username"]."';", $dbshop, __FILE__, __LINE__);
				$row=mysql_fetch_array($results);
				if ($row["GEWERBE"]>0) $gewerblich=true;
			}
			
			//Preis bestimmen
			$pl=array();
			if ($gewerblich)
			{
				//Preisliste 1
				$results2=q("SELECT * FROM prpos WHERE ARTNR='".$artnr."' AND LST_NR='".$row["PL1"]."';", $dbshop, __FILE__, __LINE__);
				if (mysql_num_rows($results2)>0)
				{
					$row2=mysql_fetch_array($results2);
					if ($row2["POS_0_WERT"]>0)
					{
						if ($amount>=$row2["POS_0_PE"])
						{
							$pl[0]=$row2["POS_0_WERT"];
//							if ($discount>0) $pl[0]*=(100-$discount)/100;
							if ($pl[0]<=0) $pl[0]=9999;
							return($pl[0]+$atwert);
						} else $pl[0]=9999;
					} else $pl[0]=9999;
				} else $pl[0]=9999;
	//			echo '1: '.$pl[0].'<br />';
				
				//Preisliste 2
				$results2=q("SELECT * FROM prpos WHERE ARTNR='".$artnr."' AND LST_NR='".$row["PL2"]."';", $dbshop, __FILE__, __LINE__);
				if (mysql_num_rows($results2)>0)
				{
					$row2=mysql_fetch_array($results2);
					if ($row2["POS_0_WERT"]>0)
					{
						if ($amount>=$row2["POS_0_PE"])
						{
							$pl[1]=$row2["POS_0_WERT"];
//							if ($discount>0) $pl[1]*=(100-$discount)/100;
							if ($pl[1]<=0) $pl[1]=9999;
							return($pl[1]+$atwert);
						}
					} else $pl[1]=9999;
				} else $pl[1]=9999;
	//			echo '2:'.$pl[1].'<br />';
				
				//Preisgruppe (Listenpreis)
				$results2=q("SELECT * FROM prpos WHERE ARTNR='".$artnr."' AND LST_NR='".$row["PREISGR"]."';", $dbshop, __FILE__, __LINE__);
				if (mysql_num_rows($results2)>0)
				{
					$row2=mysql_fetch_array($results2);
					if ($row2["POS_0_WERT"]>0)
					{
						if ($amount>=$row2["POS_0_PE"])
						{
							$pl[2]=$row2["POS_0_WERT"];
							if ($discount>0)
							{
								if ($row["PREISGR"]>=3 and $row["PREISGR"]<=7)
								{
									$pl[2]*=(100-$discount)/100;
								}
							}
							if ($pl[2]<=0) $pl[2]=9999;
							return($pl[2]+$atwert);
						} else $pl[2]=9999;
					} else $pl[2]=9999;
				} else $pl[2]=9999;
	//			echo '3: '.$pl[2].'<br />';
				
				//Bruttopreisliste
				$results2=q("SELECT * FROM prpos WHERE ARTNR='".$artnr."' AND LST_NR='0';", $dbshop, __FILE__, __LINE__);
				if (mysql_num_rows($results2)>0)
				{
					$row2=mysql_fetch_array($results2);
					if ($row2["POS_0_WERT"]>0)
					{
						$pl[3]=$row2["POS_0_WERT"];
						if ($discount>0) $pl[3]*=(100-$discount)/100;
						if ($pl[3]<=0) $pl[3]=9999;
						return($pl[3]+$atwert);
					} else $pl[3]=9999;
				} else $pl[3]=9999;
	//			echo '4: '.$pl[3].'<br />';
				
				//return best price
				sort($pl);
				return($pl[0]+$atwert);
			}
			else
			{
				//ehem. Werksverkaufsliste jetzt blaue Liste
				$results2=q("SELECT * FROM prpos WHERE ARTNR='".$artnr."' AND LST_NR='3';", $dbshop, __FILE__, __LINE__);
				if (mysql_num_rows($results2)>0)
				{
					$row2=mysql_fetch_array($results2);
					if ($row2["POS_0_WERT"]>0)
					{
						if ($amount>=$row2["POS_0_PE"])
						{
							$pl[0]=$row2["POS_0_WERT"];
							if ($discount>0) $pl[0]*=(100-$discount)/100;
							if ($pl[0]<=0) $pl[0]=9999;
							return(($pl[0]+$atwert)*((100+UST)/100));
						}
					} else $pl[0]=9999;
				} else $pl[0]=9999;
				
				//Bruttopreisliste
				$results2=q("SELECT * FROM prpos WHERE ARTNR='".$artnr."' AND LST_NR='0';", $dbshop, __FILE__, __LINE__);
				if (mysql_num_rows($results2)>0)
				{
					$row2=mysql_fetch_array($results2);
					if ($row2["POS_0_WERT"]>0)
					{
						$pl[1]=$row2["POS_0_WERT"];
						if ($discount>0) $pl[1]*=(100-$discount)/100;
						if ($pl[1]<=0) $pl[1]=9999;
						return($pl[1]+$atwert);
					} else $pl[1]=9999;
				} else $pl[1]=9999;
				
				//return best price
				sort($pl);
				return(($pl[0]+$atwert)*((100+UST)/100));
			}
			return(9999);
		}
	}
?>
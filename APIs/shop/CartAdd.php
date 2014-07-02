<?php
	
	include("../functions/cms_t.php");
	
	if ( !isset($_POST["id_item"]) or !($_POST["id_item"]>0) )
	{
		echo '<ItemExportResponse>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Artikel nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es wurde kein gültiger Artikel angegeben.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ItemExportResponse>'."\n";
		exit;
	}


	$responseXml = post(PATH."soa/", array("API" => "shop", "Action" => "StockGet", "id_item" => $_POST["id_item"]));
	$use_errors = libxml_use_internal_errors(true);
	try
	{
		$response = new SimpleXMLElement($responseXml);
	}
	catch(Exception $e)
	{
		echo '<CartAddResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Istbestand-Abfrage fehlgeschlagen.</shortMsg>'."\n";
		echo '		<longMsg>Bei der Abfrage des Istbestands ist ein Fehler aufgetreten.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</CartAddResponse>'."\n";
		exit;
	}
	libxml_clear_errors();
	libxml_use_internal_errors($use_errors);
	$stock=$response->Stock[0];

	//vehicle_id get
	if(isset($_SESSION["id_user"]) and $_SESSION["id_user"]>0 and isset ($_POST["vehicle_save"]) and $_POST["vehicle_save"]==1)
	{
		$results=q("SELECT * FROM vehicles_".$_SESSION["lang"]." WHERE Exclude=0 AND KTypNr=".$_SESSION["ktypnr"].";", $dbshop, __FILE__, __LINE__);
		$row=mysqli_fetch_array($results);
		//$vehicle_id=$row["id_vehicle"];
		$results2=q("SELECT * FROM shop_carfleet WHERE active=1 AND vehicle_id=".$row["id_vehicle"]." AND user_id=".$_SESSION["id_user"].";", $dbshop, __FILE__, __LINE__);
		if(mysqli_num_rows($results2)>0)
		{
			$row2=mysqli_fetch_array($results2);
			$vehicle_id=$row2["id"];
		}
		else $vehicle_id=0;
	}
	
	if(!isset($vehicle_id) or !($vehicle_id>0)) $vehicle_id=0;
	
	//Anzahl in der Nachbestellliste
	if(isset($_SESSION["id_user"]))
	{
		$results=q("SELECT * FROM shop_lists WHERE firstmod_user=".$_SESSION["id_user"]." AND listtype_id=5;", $dbshop, __FILE__, __LINE__);
		if(mysqli_num_rows($results)>0)
		{
			$row=mysqli_fetch_array($results);
			$results2=q("SELECT * FROM shop_lists_items WHERE list_id=".$row["id_list"]." AND item_id=".$_POST["id_item"].";", $dbshop, __FILE__, __LINE__);
			if(mysqli_num_rows($results2)>0)
			{
				$row2=mysqli_fetch_array($results2);
				$in_reorder=$row2["amount"];
			}
			else
				$in_reorder=0;
		}
		else
		{
			$responseXml = post(PATH."soa/", array("API" => "shop", "Action" => "ListAdd", "id_listtype" => "5", "title" => $_SESSION["id_user"]));
			$use_errors = libxml_use_internal_errors(true);
			try
			{
				$response = new SimpleXMLElement($responseXml);
			}
			catch(Exception $e)
			{
				echo '<CartAddResponse>'."\n";
				echo '	<Ack>Failure</Ack>'."\n";
				echo '	<Error>'."\n";
				echo '		<Code>'.__LINE__.'</Code>'."\n";
				echo '		<shortMsg>Liste anlegen fehlgeschlagen.</shortMsg>'."\n";
				echo '		<longMsg>Beim Anlegen der Liste ist ein Fehler aufgetreten.</longMsg>'."\n";
				echo '	</Error>'."\n";
				echo '</CartAddResponse>'."\n";
				exit;
			}
			libxml_clear_errors();
			libxml_use_internal_errors($use_errors);
			
			$in_reorder=0;
		}
	}
	else
		$in_reorder=0;
	
	//Anzahl im cart
	if (isset($_SESSION["id_user"]))
	{
		$results=q("SELECT * FROM shop_carts WHERE shop_id=".$_SESSION["id_shop"]." AND item_id=".$_POST["id_item"]." AND user_id='".$_SESSION["id_user"]."';", $dbshop, __FILE__, __LINE__);
	}
	else
	{
		$results=q("SELECT * FROM shop_carts WHERE shop_id=".$_SESSION["id_shop"]." AND item_id=".$_POST["id_item"]." AND session_id='".session_id()."';", $dbshop, __FILE__, __LINE__);
	}
	if(mysqli_num_rows($results)>0)
	{
		$row=mysqli_fetch_array($results);
		$in_cart=$row["amount"];
	}
	else
		$in_cart=0;
	
	if($stock>=$_POST["amount"])
	{
		if ( !isset($_POST["amount"]) or !($_POST["amount"]>0) )
		{
			echo t("Bitte geben Sie eine gültige Menge an").'!';
			exit;
		}
		
		if(($stock-$in_cart)>=$_POST["amount"])
		{
			$to_cart=$_POST["amount"];
			$to_reorder=0;
		}
		else if(($stock-$in_cart)<$_POST["amount"] and ($stock-$in_cart)>=0)
		{
			$to_cart=$stock-$in_cart;
			$to_reorder=$_POST["amount"]-($stock-$in_cart);
		}
		else if(($stock-$in_cart)<0)
		{
			$to_cart=0;
			$to_reorder=$_POST["amount"];
		}
		
		if($in_reorder>=$to_cart)
			$update_amount=$in_reorder-$to_cart+$to_reorder;
		else
			$update_amount=$to_reorder;
		
		if (isset($_SESSION["id_user"]))
		{
			$results=q("SELECT * FROM shop_carts WHERE shop_id=".$_SESSION["id_shop"]." AND item_id=".$_POST["id_item"]." AND user_id='".$_SESSION["id_user"]."';", $dbshop, __FILE__, __LINE__);
		}
		else
		{
			$results=q("SELECT * FROM shop_carts WHERE shop_id=".$_SESSION["id_shop"]." AND item_id=".$_POST["id_item"]." AND session_id='".session_id()."';", $dbshop, __FILE__, __LINE__);
		}
		if (mysqli_num_rows($results)>0) //item schon im cart vorhanden
		{
			$row=mysqli_fetch_array($results);
			$results=q("UPDATE shop_carts SET amount='".($row["amount"]+$to_cart)."', lastmod=".time()." WHERE id_carts='".$row["id_carts"]."';", $dbshop, __FILE__, __LINE__);
			if(isset($_SESSION["id_user"])) // Nachbestellliste
			{
				$results=q("SELECT * FROM shop_lists WHERE firstmod_user=".$_SESSION["id_user"]." AND listtype_id=5;", $dbshop, __FILE__, __LINE__);
				if(mysqli_num_rows($results)>0)
				{
					$row=mysqli_fetch_array($results);
					$results2=q("SELECT * FROM shop_lists_items WHERE list_id=".$row["id_list"]." AND item_id=".$_POST["id_item"].";", $dbshop, __FILE__, __LINE__);
					if(mysqli_num_rows($results2)==0)
					{
						if($update_amount>0)
						{
							$responseXml = post(PATH."soa/", array("API" => "shop", "Action" => "ListAddItem", "list_id" => $row["id_list"], "item_id" => $_POST["id_item"], "amount" => $update_amount));
							$use_errors = libxml_use_internal_errors(true);
							try
							{
								$response = new SimpleXMLElement($responseXml);
							}
							catch(Exception $e)
							{
								echo '<CartAddResponse>'."\n";
								echo '	<Ack>Failure</Ack>'."\n";
								echo '	<Error>'."\n";
								echo '		<Code>'.__LINE__.'</Code>'."\n";
								echo '		<shortMsg>Item anlegen fehlgeschlagen.</shortMsg>'."\n";
								echo '		<longMsg>Beim Anlegen des Items ist ein Fehler aufgetreten.</longMsg>'."\n";
								echo '	</Error>'."\n";
								echo '</CartAddResponse>'."\n";
								exit;
							}
							libxml_clear_errors();
							libxml_use_internal_errors($use_errors);
						}
					}
					else
					{
						if($update_amount>0)
						{
							//echo ($to_reorder-$to_cart);
							$responseXml = post(PATH."soa/", array("API" => "shop", "Action" => "ListItemUpdate", "list_id" => $row["id_list"], "item_id" => $_POST["id_item"], "amount" => ($update_amount-$in_reorder)));
							$use_errors = libxml_use_internal_errors(true);
							try
							{
								$response = new SimpleXMLElement($responseXml);
							}
							catch(Exception $e)
							{
								echo '<CartAddResponse>'."\n";
								echo '	<Ack>Failure</Ack>'."\n";
								echo '	<Error>'."\n";
								echo '		<Code>'.__LINE__.'</Code>'."\n";
								echo '		<shortMsg>Item Update fehlgeschlagen.</shortMsg>'."\n";
								echo '		<longMsg>Beim Update des Items ist ein Fehler aufgetreten.</longMsg>'."\n";
								echo '	</Error>'."\n";
								echo '</CartAddResponse>'."\n";
								exit;
							}
							libxml_clear_errors();
							libxml_use_internal_errors($use_errors);
						}
						else if($update_amount<=0)
						{
							$row2=mysqli_fetch_array($results2);
							
							$responseXml = post(PATH."soa/", array("API" => "shop", "Action" => "ListItemRemove", "id" => $row2["id"]));
							$use_errors = libxml_use_internal_errors(true);
							try
							{
								$response = new SimpleXMLElement($responseXml);
							}
							catch(Exception $e)
							{
								echo '<CartAddResponse>'."\n";
								echo '	<Ack>Failure</Ack>'."\n";
								echo '	<Error>'."\n";
								echo '		<Code>'.__LINE__.'</Code>'."\n";
								echo '		<shortMsg>Item Löschen fehlgeschlagen.</shortMsg>'."\n";
								echo '		<longMsg>Beim Löschen des Items ist ein Fehler aufgetreten.</longMsg>'."\n";
								echo '	</Error>'."\n";
								echo '</CartAddResponse>'."\n";
								exit;
							}
							libxml_clear_errors();
							libxml_use_internal_errors($use_errors);	
						}
					}
				}
				/*if(($update_amount-$in_reorder)>0)
					echo t("Die Menge wurde erfolgreich um ").$to_cart.t(" Teile erhöht. Es wurden ").($update_amount-$in_reorder).t(" Teile auf Ihre Nachbestellliste gesetzt").'..';
				else if(($update_amount-$in_reorder)<0)
					echo t("Die Menge wurde erfolgreich um ").$to_cart.t(" Teile erhöht. Es wurden ").(($update_amount-$in_reorder)*(-1)).t(" Teile aus Ihrer Nachbestellliste entfernt").'..';
				else if(($update_amount-$in_reorder)==0)
					echo t("Die Menge wurde erfolgreich um ").$to_cart.t(" Teile erhöht.").'..';*/
				if(($update_amount-$in_reorder)>0)
					echo t("Die Menge wurde erfolgreich um ").$to_cart.t(" Teile erhöht. Die restlichen Teile wurden auf Ihre Nachbestellliste gesetzt").'..';
				else if(($update_amount-$in_reorder)<0)
					echo t("Die Menge wurde erfolgreich um ").$to_cart.t(" Teile erhöht.").'..';
				else if(($update_amount-$in_reorder)==0)
					echo t("Die Menge wurde erfolgreich um ").$to_cart.t(" Teile erhöht.").'..';
			}
			else
				echo t("Die Menge wurde erfolgreich aktualisiert").'..';
		}
		else
		{
			if(isset ($_POST["vehicle_save"]) and $_POST["vehicle_save"]==1)
			{
				$results=q("INSERT INTO shop_carts (item_id, amount, shop_id, session_id, user_id, customer_vehicle_id, lastmod) VALUES('".$_POST["id_item"]."', '".$to_cart."', ".$_SESSION["id_shop"].", '".session_id()."', '".$_SESSION["id_user"]."', ".$vehicle_id.", ".time().");", $dbshop, __FILE__, __LINE__);
			}
			else
			{
				$results=q("INSERT INTO shop_carts (item_id, amount, shop_id, session_id, user_id, lastmod) VALUES('".$_POST["id_item"]."', '".$to_cart."', ".$_SESSION["id_shop"].", '".session_id()."', '".$_SESSION["id_user"]."', ".time().");", $dbshop, __FILE__, __LINE__);
			}
			
			if(isset($_SESSION["id_user"]))
			{
				$results=q("SELECT * FROM shop_lists WHERE firstmod_user=".$_SESSION["id_user"]." AND listtype_id=5;", $dbshop, __FILE__, __LINE__);
				if(mysqli_num_rows($results)>0)
				{
					$row=mysqli_fetch_array($results);
					$results2=q("SELECT * FROM shop_lists_items WHERE list_id=".$row["id_list"]." AND item_id=".$_POST["id_item"].";", $dbshop, __FILE__, __LINE__);
					if(mysqli_num_rows($results2)==0)
					{
						if($update_amount>0)
						{
							$responseXml = post(PATH."soa/", array("API" => "shop", "Action" => "ListAddItem", "list_id" => $row["id_list"], "item_id" => $_POST["id_item"], "amount" => $update_amount));
							$use_errors = libxml_use_internal_errors(true);
							try
							{
								$response = new SimpleXMLElement($responseXml);
							}
							catch(Exception $e)
							{
								echo '<CartAddResponse>'."\n";
								echo '	<Ack>Failure</Ack>'."\n";
								echo '	<Error>'."\n";
								echo '		<Code>'.__LINE__.'</Code>'."\n";
								echo '		<shortMsg>Item anlegen fehlgeschlagen.</shortMsg>'."\n";
								echo '		<longMsg>Beim Anlegen des Items ist ein Fehler aufgetreten.</longMsg>'."\n";
								echo '	</Error>'."\n";
								echo '</CartAddResponse>'."\n";
								exit;
							}
							libxml_clear_errors();
							libxml_use_internal_errors($use_errors);
						}
					}
					else
					{
						if($update_amount>0)
						{
							//echo ($to_reorder-$to_cart);
							$responseXml = post(PATH."soa/", array("API" => "shop", "Action" => "ListItemUpdate", "list_id" => $row["id_list"], "item_id" => $_POST["id_item"], "amount" => ($update_amount-$in_reorder)));
							$use_errors = libxml_use_internal_errors(true);
							try
							{
								$response = new SimpleXMLElement($responseXml);
							}
							catch(Exception $e)
							{
								echo '<CartAddResponse>'."\n";
								echo '	<Ack>Failure</Ack>'."\n";
								echo '	<Error>'."\n";
								echo '		<Code>'.__LINE__.'</Code>'."\n";
								echo '		<shortMsg>Item Update fehlgeschlagen.</shortMsg>'."\n";
								echo '		<longMsg>Beim Update des Items ist ein Fehler aufgetreten.</longMsg>'."\n";
								echo '	</Error>'."\n";
								echo '</CartAddResponse>'."\n";
								exit;
							}
							libxml_clear_errors();
							libxml_use_internal_errors($use_errors);
						}
						else if($update_amount<=0)
						{
							$row2=mysqli_fetch_array($results2);
							
							$responseXml = post(PATH."soa/", array("API" => "shop", "Action" => "ListItemRemove", "id" => $row2["id"]));
							$use_errors = libxml_use_internal_errors(true);
							try
							{
								$response = new SimpleXMLElement($responseXml);
							}
							catch(Exception $e)
							{
								echo '<CartAddResponse>'."\n";
								echo '	<Ack>Failure</Ack>'."\n";
								echo '	<Error>'."\n";
								echo '		<Code>'.__LINE__.'</Code>'."\n";
								echo '		<shortMsg>Item Löschen fehlgeschlagen.</shortMsg>'."\n";
								echo '		<longMsg>Beim Löschen des Items ist ein Fehler aufgetreten.</longMsg>'."\n";
								echo '	</Error>'."\n";
								echo '</CartAddResponse>'."\n";
								exit;
							}
							libxml_clear_errors();
							libxml_use_internal_errors($use_errors);	
						}
					}
				}
				if(($update_amount-$in_reorder)>0)
					echo t("Es wurden ").$to_cart.t(" Teile in den Warenkorb gelegt. Die restlichen Teile wurden auf Ihre Nachbestellliste gesetzt").'..';
				else if(($update_amount-$in_reorder)<0)
					echo t("Es wurden ").$to_cart.t(" Teile in den Warenkorb gelegt.").'..';
				else if(($update_amount-$in_reorder)==0)
					echo t("Die Ware wurde erfolgreich in den Warenkorb gelegt").'..';
			}
			else
				echo t("Die Ware wurde erfolgreich in den Warenkorb gelegt").'..';
		}
	}
	elseif($stock==0)
	{
		if ( !isset($_POST["amount"]) or !($_POST["amount"]>0) )
		{
			echo t("Bitte geben Sie eine gültige Menge an").'!';
			exit;
		}
		
		if(isset($_SESSION["id_user"]))
		{
			$results=q("SELECT * FROM shop_lists WHERE firstmod_user=".$_SESSION["id_user"]." AND listtype_id=5;", $dbshop, __FILE__, __LINE__);
			if(mysqli_num_rows($results)>0)
			{
				$row=mysqli_fetch_array($results);
				$results2=q("SELECT * FROM shop_lists_items WHERE list_id=".$row["id_list"]." AND item_id=".$_POST["id_item"].";", $dbshop, __FILE__, __LINE__);
				if(mysqli_num_rows($results2)==0)
				{
					$responseXml = post(PATH."soa/", array("API" => "shop", "Action" => "ListAddItem", "list_id" => $row["id_list"], "item_id" => $_POST["id_item"], "amount" => $_POST["amount"]));
					$use_errors = libxml_use_internal_errors(true);
					try
					{
						$response = new SimpleXMLElement($responseXml);
					}
					catch(Exception $e)
					{
						echo '<CartAddResponse>'."\n";
						echo '	<Ack>Failure</Ack>'."\n";
						echo '	<Error>'."\n";
						echo '		<Code>'.__LINE__.'</Code>'."\n";
						echo '		<shortMsg>Item anlegen fehlgeschlagen.</shortMsg>'."\n";
						echo '		<longMsg>Beim Anlegen des Items ist ein Fehler aufgetreten.</longMsg>'."\n";
						echo '	</Error>'."\n";
						echo '</CartAddResponse>'."\n";
						exit;
					}
					libxml_clear_errors();
					libxml_use_internal_errors($use_errors);
				}
				else
				{
					$responseXml = post(PATH."soa/", array("API" => "shop", "Action" => "ListItemUpdate", "list_id" => $row["id_list"], "item_id" => $_POST["id_item"], "amount" => $_POST["amount"]));
					$use_errors = libxml_use_internal_errors(true);
					try
					{
						$response = new SimpleXMLElement($responseXml);
					}
					catch(Exception $e)
					{
						echo '<CartAddResponse>'."\n";
						echo '	<Ack>Failure</Ack>'."\n";
						echo '	<Error>'."\n";
						echo '		<Code>'.__LINE__.'</Code>'."\n";
						echo '		<shortMsg>Item Update fehlgeschlagen.</shortMsg>'."\n";
						echo '		<longMsg>Beim Update des Items ist ein Fehler aufgetreten.</longMsg>'."\n";
						echo '	</Error>'."\n";
						echo '</CartAddResponse>'."\n";
						exit;
					}
					libxml_clear_errors();
					libxml_use_internal_errors($use_errors);
				}
			}
			echo t("Der Artikel ist momentan nicht verfügbar. Er wurde in Ihrer Nachbestellliste hinterlegt").'..';
		}
		else
			echo t("Der Artikel ist momentan nicht verfügbar.").'..';
	}
	elseif($stock>0 and $stock<$_POST["amount"])
	{
		if ( !isset($_POST["amount"]) or !($_POST["amount"]>0) )
		{
			echo t("Bitte geben Sie eine gültige Menge an").'!';
			exit;
		}
		
		//$miss=$_POST["amount"]-$stock;
		
		if(($in_cart)==0)
		{
			$to_cart=$stock;
			$to_reorder=($_POST["amount"]-$stock);
		}
		else if(($stock>=$in_cart) and $in_cart>0)
		{
			$to_cart=$stock-$in_cart;
			$to_reorder=$_POST["amount"]-($stock-$in_cart);
		}
		else if($stock<$in_cart)
		{
			$to_cart=0;
			$to_reorder=$_POST["amount"];
		}
		
		if($in_reorder>=$to_cart)
			$update_amount=$in_reorder-$to_cart+$to_reorder;
		else
			$update_amount=$to_reorder;
	
		if (isset($_SESSION["id_user"]))
		{
			$results=q("SELECT * FROM shop_carts WHERE shop_id=".$_SESSION["id_shop"]." AND item_id=".$_POST["id_item"]." AND user_id='".$_SESSION["id_user"]."';", $dbshop, __FILE__, __LINE__);
		}
		else
		{
			$results=q("SELECT * FROM shop_carts WHERE shop_id=".$_SESSION["id_shop"]." AND item_id=".$_POST["id_item"]." AND session_id='".session_id()."';", $dbshop, __FILE__, __LINE__);
		}
		if (mysqli_num_rows($results)>0)
		{
			$row=mysqli_fetch_array($results);
			$results=q("UPDATE shop_carts SET amount='".($row["amount"]+$to_cart)."', lastmod=".time()." WHERE id_carts='".$row["id_carts"]."';", $dbshop, __FILE__, __LINE__);
			if(isset($_SESSION["id_user"]))
			{
				$results=q("SELECT * FROM shop_lists WHERE firstmod_user=".$_SESSION["id_user"]." AND listtype_id=5;", $dbshop, __FILE__, __LINE__);
				if(mysqli_num_rows($results)>0)
				{
					$row=mysqli_fetch_array($results);
					$results2=q("SELECT * FROM shop_lists_items WHERE list_id=".$row["id_list"]." AND item_id=".$_POST["id_item"].";", $dbshop, __FILE__, __LINE__);
					if(mysqli_num_rows($results2)==0)
					{
						if($update_amount>0)
						{
							$responseXml = post(PATH."soa/", array("API" => "shop", "Action" => "ListAddItem", "list_id" => $row["id_list"], "item_id" => $_POST["id_item"], "amount" => $update_amount));
							$use_errors = libxml_use_internal_errors(true);
							try
							{
								$response = new SimpleXMLElement($responseXml);
							}
							catch(Exception $e)
							{
								echo '<CartAddResponse>'."\n";
								echo '	<Ack>Failure</Ack>'."\n";
								echo '	<Error>'."\n";
								echo '		<Code>'.__LINE__.'</Code>'."\n";
								echo '		<shortMsg>Item anlegen fehlgeschlagen.</shortMsg>'."\n";
								echo '		<longMsg>Beim Anlegen des Items ist ein Fehler aufgetreten.</longMsg>'."\n";
								echo '	</Error>'."\n";
								echo '</CartAddResponse>'."\n";
								exit;
							}
							libxml_clear_errors();
							libxml_use_internal_errors($use_errors);
						}
					}
					else
					{
						if($update_amount>0)
						{
							$responseXml = post(PATH."soa/", array("API" => "shop", "Action" => "ListItemUpdate", "list_id" => $row["id_list"], "item_id" => $_POST["id_item"], "amount" => ($update_amount-$in_reorder)));
							$use_errors = libxml_use_internal_errors(true);
							try
							{
								$response = new SimpleXMLElement($responseXml);
							}
							catch(Exception $e)
							{
								echo '<CartAddResponse>'."\n";
								echo '	<Ack>Failure</Ack>'."\n";
								echo '	<Error>'."\n";
								echo '		<Code>'.__LINE__.'</Code>'."\n";
								echo '		<shortMsg>Item Update fehlgeschlagen.</shortMsg>'."\n";
								echo '		<longMsg>Beim Update des Items ist ein Fehler aufgetreten.</longMsg>'."\n";
								echo '	</Error>'."\n";
								echo '</CartAddResponse>'."\n";
								exit;
							}
							libxml_clear_errors();
							libxml_use_internal_errors($use_errors);
						}
						else if($update_amount<=0)
						{
							$row2=mysqli_fetch_array($results2);
							
							$responseXml = post(PATH."soa/", array("API" => "shop", "Action" => "ListItemRemove", "id" => $row2["id"]));
							$use_errors = libxml_use_internal_errors(true);
							try
							{
								$response = new SimpleXMLElement($responseXml);
							}
							catch(Exception $e)
							{
								echo '<CartAddResponse>'."\n";
								echo '	<Ack>Failure</Ack>'."\n";
								echo '	<Error>'."\n";
								echo '		<Code>'.__LINE__.'</Code>'."\n";
								echo '		<shortMsg>Item Löschen fehlgeschlagen.</shortMsg>'."\n";
								echo '		<longMsg>Beim Löschen des Items ist ein Fehler aufgetreten.</longMsg>'."\n";
								echo '	</Error>'."\n";
								echo '</CartAddResponse>'."\n";
								exit;
							}
							libxml_clear_errors();
							libxml_use_internal_errors($use_errors);	
						}
					}
				}
				if(($update_amount-$in_reorder)>0)
					echo t("Die Menge wurde erfolgreich um ").$to_cart.t(" Teile erhöht. Die restlichen Teile wurden auf Ihre Nachbestellliste gesetzt").'..';
				else if(($update_amount-$in_reorder)<0)
					echo t("Die Menge wurde erfolgreich um ").$to_cart.t(" Teile erhöht.").'..';
				else if(($update_amount-$in_reorder)==0)
					echo t("Die Menge wurde erfolgreich um ").$to_cart.t(" Teile erhöht.").'..';
			}
			else
			{
				echo t("Die Menge wurde erfolgreich um ").$stock.t(" Teile erhöht. Es sind momentan nicht mehr verfügbar.").'..';
			}
		}
		else
		{
			if(isset ($_POST["vehicle_save"]) and $_POST["vehicle_save"]==1)
			{
				$results=q("INSERT INTO shop_carts (item_id, amount, shop_id, session_id, user_id, customer_vehicle_id, lastmod) VALUES('".$_POST["id_item"]."', '".$to_cart."', ".$_SESSION["id_shop"].", '".session_id()."', '".$_SESSION["id_user"]."', ".$vehicle_id.", ".time().");", $dbshop, __FILE__, __LINE__);
			}
			else
			{
				$results=q("INSERT INTO shop_carts (item_id, amount, shop_id, session_id, user_id, lastmod) VALUES('".$_POST["id_item"]."', '".$to_cart."', '".session_id()."', ".$_SESSION["id_shop"].", '".$_SESSION["id_user"]."', ".time().");", $dbshop, __FILE__, __LINE__);
			}
			if(isset($_SESSION["id_user"]))
			{
				$results=q("SELECT * FROM shop_lists WHERE firstmod_user=".$_SESSION["id_user"]." AND listtype_id=5;", $dbshop, __FILE__, __LINE__);
				if(mysqli_num_rows($results)>0)
				{
					$row=mysqli_fetch_array($results);
					$results2=q("SELECT * FROM shop_lists_items WHERE list_id=".$row["id_list"]." AND item_id=".$_POST["id_item"].";", $dbshop, __FILE__, __LINE__);
					if(mysqli_num_rows($results2)==0)
					{
						if($update_amount>0)
						{
							$responseXml = post(PATH."soa/", array("API" => "shop", "Action" => "ListAddItem", "list_id" => $row["id_list"], "item_id" => $_POST["id_item"], "amount" => $update_amount));
							$use_errors = libxml_use_internal_errors(true);
							try
							{
								$response = new SimpleXMLElement($responseXml);
							}
							catch(Exception $e)
							{
								echo '<CartAddResponse>'."\n";
								echo '	<Ack>Failure</Ack>'."\n";
								echo '	<Error>'."\n";
								echo '		<Code>'.__LINE__.'</Code>'."\n";
								echo '		<shortMsg>Item anlegen fehlgeschlagen.</shortMsg>'."\n";
								echo '		<longMsg>Beim Anlegen des Items ist ein Fehler aufgetreten.</longMsg>'."\n";
								echo '	</Error>'."\n";
								echo '</CartAddResponse>'."\n";
								exit;
							}
							libxml_clear_errors();
							libxml_use_internal_errors($use_errors);
						}
					}
					else
					{
						if($update_amount>0)
						{
							$responseXml = post(PATH."soa/", array("API" => "shop", "Action" => "ListItemUpdate", "list_id" => $row["id_list"], "item_id" => $_POST["id_item"], "amount" => ($update_amount-$in_reorder)));
							$use_errors = libxml_use_internal_errors(true);
							try
							{
								$response = new SimpleXMLElement($responseXml);
							}
							catch(Exception $e)
							{
								echo '<CartAddResponse>'."\n";
								echo '	<Ack>Failure</Ack>'."\n";
								echo '	<Error>'."\n";
								echo '		<Code>'.__LINE__.'</Code>'."\n";
								echo '		<shortMsg>Item Update fehlgeschlagen.</shortMsg>'."\n";
								echo '		<longMsg>Beim Update des Items ist ein Fehler aufgetreten.</longMsg>'."\n";
								echo '	</Error>'."\n";
								echo '</CartAddResponse>'."\n";
								exit;
							}
							libxml_clear_errors();
							libxml_use_internal_errors($use_errors);
						}
						else if($update_amount<=0)
						{
							$row2=mysqli_fetch_array($results2);
							
							$responseXml = post(PATH."soa/", array("API" => "shop", "Action" => "ListItemRemove", "id" => $row2["id"]));
							$use_errors = libxml_use_internal_errors(true);
							try
							{
								$response = new SimpleXMLElement($responseXml);
							}
							catch(Exception $e)
							{
								echo '<CartAddResponse>'."\n";
								echo '	<Ack>Failure</Ack>'."\n";
								echo '	<Error>'."\n";
								echo '		<Code>'.__LINE__.'</Code>'."\n";
								echo '		<shortMsg>Item Löschen fehlgeschlagen.</shortMsg>'."\n";
								echo '		<longMsg>Beim Löschen des Items ist ein Fehler aufgetreten.</longMsg>'."\n";
								echo '	</Error>'."\n";
								echo '</CartAddResponse>'."\n";
								exit;
							}
							libxml_clear_errors();
							libxml_use_internal_errors($use_errors);	
						}
					}
				}
				if(($update_amount-$in_reorder)>0)
					echo t("Es wurden ").$to_cart.t(" Teile in den Warenkorb gelegt. Die restlichen Teile wurden auf Ihre Nachbestellliste gesetzt").'..';
				else if(($update_amount-$in_reorder)<0)
					echo t("Es wurden ").$to_cart.t(" Teile in den Warenkorb gelegt.").'..';
				else if(($update_amount-$in_reorder)==0)
					echo t("Die Ware wurde erfolgreich in den Warenkorb gelegt").'..';
			}
			else
			{
				echo t("Es wurden ").$stock.t(" Teile in den Warenkorb gelegt. Es sind momentan nicht mehr verfügbar.").'..';
			}
		}
	}

?>
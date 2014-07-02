<?php
	session_start();
	include("../config.php");
	include("../functions/cms_t.php");
	include("../functions/shop_itemstatus.php");
	include("../functions/shop_get_prices.php");
	include("../functions/mapco_gewerblich.php");
	include("../functions/mapco_frachtpauschale.php");
	include("../functions/cms_url_encode.php");
	include("../functions/cms_send_html_mail.php");

	include("../functions/shop_checkOnlinePayment.php");

	
	//ADDITIONAL SAVE
	if ($_POST["action"]=="additional_save")
	{
		session_start();
		$_SESSION["ordernr"]=$_POST["ordernr"];
		$_SESSION["comment"]=$_POST["comment"];
	}
	
	//ADDITIONAL EDIT
	if ($_POST["action"]=="additional_edit")
	{
		session_start();
		if ( !isset($_SESSION["ordernr"]) ) $_SESSION["ordernr"]='';
		if ( !isset($_SESSION["comment"]) ) $_SESSION["comment"]='';
		echo '<table>';
		echo '<tr>';
		echo '	<td>';
		echo '		<b>'.t("Eigene Bestellnummer").'</b>';
		echo '		<br /><input type="text" style="width:350px;" id="additional_ordernr" value="'.$_SESSION["ordernr"].'" />';
		echo '	</td>';
		echo '</tr>';
		echo '<tr>';
		echo '	<td colspan="3">';
		echo '		<b>'.t("Anmerkung zur Bestellung").'</b>';
		echo '		<br /><textarea style="width:350px; height:75px; resize: none;" id="additional_comment">'.$_SESSION["comment"].'</textarea>';
		echo '	</td>';
		echo '</tr>';
		echo '</table>';
	}


	//AVAILIBILITY EDIT
	if ($_POST["action"]=="availability_edit")
	{
		session_start();
		if ( !isset($_SESSION["usermail"]) ) $_SESSION["usermail"]='';
		if ( !isset($_SESSION["userphone"]) ) $_SESSION["userphone"]='';
		if ( !isset($_SESSION["userfax"]) ) $_SESSION["userfax"]='';
		if ( !isset($_SESSION["usermobile"]) ) $_SESSION["usermobile"]='';
		echo '<table>';
		echo '	<tr>';
		echo '		<td>'.t("E-Mail").'</td>';
		echo '		<td><input type="text" style="width:240px;" id="availability_usermail" value="'.$_SESSION["usermail"].'" /></td>';
		echo '	</tr>';
		echo '	<tr>';
		echo '		<td>'.t("Telefon").'</td>';
		echo '		<td><input type="text" style="width:240px;" id="availability_userphone" value="'.$_SESSION["userphone"].'" /></td>';
		echo '	</tr>';
		echo '	<tr>';
		echo '		<td>'.t("Telefax").'</td>';
		echo '		<td><input type="text" style="width:240px;" id="availability_userfax" value="'.$_SESSION["userfax"].'" /></td>';
		echo '	</tr>';
		echo '	<tr>';
		echo '		<td>'.t("Mobiltelefon").'</td>';
		echo '		<td><input type="text" style="width:240px;" id="availability_usermobile" value="'.$_SESSION["usermobile"].'" /></td>';
		echo '	</tr>';
		echo '</table>';
	}


	//AVAILABILITY SAVE
	if ($_POST["action"]=="availability_save")
	{
		session_start();
		$_SESSION["usermail"]=$_POST["usermail"];
		$_SESSION["userphone"]=$_POST["userphone"];
		$_SESSION["userfax"]=$_POST["userfax"];
		$_SESSION["usermobile"]=$_POST["usermobile"];
	}

	//PAYMENT SAVE
	if ($_POST["action"]=="payment_save")
	{
		session_start();
		$_SESSION["id_payment"]=$_POST["id_payment"];
		$_SESSION["id_shipping"]=$_POST["id_shipping"];
		$_SESSION["shipping_net"]=$_POST["shipping_net"];
		$_SESSION["shipping_costs"]=$_POST["shipping_costs"];
		$_SESSION["shipping_details"]=$_POST["shipping_details"];
		$_SESSION["payment_memo"]=$_POST["payment_memo"];
		$_SESSION["shipping_memo"]=$_POST["shipping_memo"];
	}
	
	//PAYMENT EDIT
	if ($_POST["action"]=="payment_edit")
	{
		session_start();
		if (isset($_POST["id_payment_select"])) 
		{
			$payment_option["id_payment"]=$_POST["id_payment_select"];
			$payment_option["id_shipping"]=$_POST["id_shipping_select"];
			$results=q("SELECT * FROM shop_shipping WHERE payment_id=".$payment_option["id_payment"]." and id_shipping=".$payment_option["id_shipping"]." LIMIT 1;", $dbshop, __FILE__, __LINE__);
			if(!(mysqli_num_rows($results)>0)) {$payment_option["id_shipping"]="";}
		}
		else
		{
			$payment_option["id_payment"]=$_SESSION["id_payment"];
			$payment_option["id_shipping"]=$_SESSION["id_shipping"];
			if(isset($_SESSION["shipping_net"])) $payment_option["shipping_net"]=$_SESSION["shipping_net"];
			else $payment_option["shipping_net"]=0;
			if(isset($_SESSION["shipping_costs"])) $payment_option["shipping_costs"]=$_SESSION["shipping_costs"];
			else $payment_option["shipping_costs"]=0;
			if(isset($_SESSION["shipping_details"])) $payment_option["shipping_details"]=$_SESSION["shipping_details"];
			else $payment_option["shipping_details"]="";
			if(isset($_SESSION["payment_memo"])) $payment_option["payment_memo"]=$_SESSION["payment_memo"];
			else $payment_option["payment_memo"]="";
			if(isset($_SESSION["shipping_memo"])) $payment_option["shipping_memo"]=$_SESSION["shipping_memo"];
			else $payment_option["shipping_memo"]="";
		}
		
		//Gewerbekunde?
		$gewerblich=gewerblich($_SESSION["id_user"]);
		
		//Frachtpauschale?
		if($gewerblich)
		{
			$frachtpauschale=frachtpauschale($_SESSION["id_user"]);
		}

		if ($gewerblich) $results=q("SELECT * FROM shop_payment WHERE shop_id=".$_SESSION["id_shop"]." AND country_id=".$_SESSION["ship_country_id"]." ORDER BY ordering;", $dbshop, __FILE__, __LINE__);
		else $results=q("SELECT * FROM shop_payment WHERE shop_id=".$_SESSION["id_shop"]." AND NOT payment='Rechnung' AND country_id=".$_SESSION["ship_country_id"]." ORDER BY ordering;", $dbshop, __FILE__, __LINE__);
		echo '<table>';
		echo '<tr>';
		if (mysqli_num_rows($results)>0)
		{
			echo '<td>'.t("Zahlungsart").':</td>';
			echo '<td><select id="id_payment_select" style="width:450px;" onchange="payment_select();">';
			while($row=mysqli_fetch_array($results))
			{
				if ($payment_option["id_payment"]=="")
				{
					$payment_option["id_payment"]=$row["id_payment"];
				}
				if ($row["id_payment"]==$payment_option["id_payment"])
				{
					$selected=' selected="selected"';
					$payment_option["payment_memo"]=$row["payment_memo"];
					$payment_option["shipping_details"]=$row["payment"];
				}
				else $selected='';
				echo '<option'.$selected.' value="'.$row["id_payment"].'">';
				echo $row["payment"];
				echo '</option>';
			}
			echo '		</select></td>';
			echo '</tr>';
						
			//shipping costs
			echo '<tr>';
			echo '<td>'.t("Versandart").':</td>';
			echo '<td><select id="id_shipping_select" style="width:450px;" onchange="payment_select()">';
			if ($gewerblich)
			{
				if($_SESSION["rcid"]!="" and $_SESSION["rcid"]!=9999 and $_SESSION["rcid"]>0 and $payment_option["id_payment"]==9)
				{
					$results=q("SELECT * FROM shop_shipping WHERE id_shipping IN (".$_SESSION["rc_shipping"].", 20) AND payment_id=".$payment_option["id_payment"]." ORDER BY ordering;", $dbshop, __FILE__, __LINE__);
				}
				else
				{
					$results=q("SELECT * FROM shop_shipping WHERE payment_id=".$payment_option["id_payment"]." ORDER BY ordering;", $dbshop, __FILE__, __LINE__);
				}
			}
			else
			{
				if($_SESSION["rcid"]!="" and $_SESSION["rcid"]!=9999 and $_SESSION["rcid"]>0 and $payment_option["id_payment"]==9)
				{
					$results=q("SELECT * FROM shop_shipping WHERE NOT shipping LIKE '%Lieferservice%' AND id_shipping=".$_SESSION["rc_shipping"]." AND payment_id=".$payment_option["id_payment"]." ORDER BY ordering;", $dbshop, __FILE__, __LINE__);
					$_SESSION["id_shipping"]=$_SESSION["rc_shipping"];
				}
				else
				{
					$results=q("SELECT * FROM shop_shipping WHERE NOT shipping LIKE '%Lieferservice%' AND payment_id=".$payment_option["id_payment"]." ORDER BY ordering;", $dbshop, __FILE__, __LINE__);
				}
			}
			while($row=mysqli_fetch_array($results))
			{
				if ($payment_option["id_shipping"]=="") $payment_option["id_shipping"]=$row["id_shipping"];
				if ($row["id_shipping"]==$payment_option["id_shipping"])
				{
					$shipping_id = $row["id_shipping"];
					$selected=' selected="selected"';
					$payment_option["shipping_net"]=$row["price"];
					if ($gewerblich) $payment_option["shipping_costs"]=$row["price"];
					else $payment_option["shipping_costs"]=((100+UST)/100)*$row["price"];
					$payment_option["shipping_details"].=', '.$row["shipping"];
					$payment_option["shipping_memo"]=$row["shipping_memo"];
				}
				else $selected='';
				
				if($frachtpauschale)
				{
					$payment_option["shipping_net"]=0;
					$payment_option["shipping_costs"]=0;
					$row["price"]=0;
				}

				if ($row["id_shipping"]==27) echo '<option'.$selected.' value="'.$row["id_shipping"].'">'.$row["shipping"].'</option>';
				else
				{
					if ($gewerblich) echo '<option'.$selected.' value="'.$row["id_shipping"].'">'.$row["shipping"].' (€ '.number_format($row["price"], 2).')</option>';
					else echo '<option'.$selected.' value="'.$row["id_shipping"].'">'.$row["shipping"].' (€ '.number_format(((100+UST)/100)*$row["price"], 2).')</option>';
				}
			}
			echo '		</select></td>';
			echo '</tr>';
			echo '<tr>';
			echo '<td colspan="2">';
			if ($payment_option["payment_memo"]!="") echo '<br /><i>'.$payment_option["payment_memo"].'</i>';
			if ($payment_option["shipping_memo"]!="") echo '<br /><i>'.$payment_option["shipping_memo"].'</i>';
			echo '<input type="hidden" id="payment_shipping_net" value="'.$payment_option["shipping_net"].'" />';
			echo '<input type="hidden" id="payment_shipping_costs" value="'.$payment_option["shipping_costs"].'" />';
			echo '<input type="hidden" id="payment_shipping_details" value="'.$payment_option["shipping_details"].'" />';
			echo '<input type="hidden" id="payment_payment_memo" value="'.$payment_option["payment_memo"].'" />';
			echo '<input type="hidden" id="payment_shipping_memo" value="'.$payment_option["shipping_memo"].'" />';
			echo '</td>';
			echo '</tr>';
			echo '</table>';
		}
		else 
		{
			echo '<div colspan="4" style="color:#ff0000"; align="center">';
			echo t("Zahlungsart und Versandkosten bitte vor Bestellung schriftlich oder telefonisch klären!");
			echo '</div>';
		}
	}


	//VIEW
	if (isset($_POST["action"]) and $_POST["action"]=="view")
	{
		session_start();

		//WARTUNGSARBEITEN
		if (date("d.m.Y")=="18.12.2013")
		{
			echo '<div class="warning">Wegen Wartungsarbeiten an unserem System können momentan keine Bestellungen per Kreditkarte bezahlt werden! Die Funktion steht in Kürze wieder zur Verfügung. Vielen Dank für Ihr Verständnis</div>';
		}

		//E-Mail Adresse auslesen
		if ( !isset($_SESSION["usermail"]) or $_SESSION["usermail"]=="") 
		{
			$results=q("SELECT usermail FROM cms_users WHERE id_user=".$_SESSION["id_user"]." AND usermail LIKE '%@%';", $dbweb, __FILE__, __LINE__);
			if (mysqli_num_rows($results)>0)
			{
				$row=mysqli_fetch_array($results);
				$_SESSION["usermail"]=$row["usermail"];
			}
			else $_SESSION["usermail"]="";
		}
		
		//CHECK FÜR PAYGENIC ob Pflichtfelder ausgefüllt
		$mandatoryfields=true;
		if ($_SESSION["bill_firstname"]=="") {echo '<div class="warning">'.t("Füllen Sie vor der ersten Bestellung alle rot umrandeten Felder aus (siehe rechts)").'!</div>'; $mandatoryfields=false;}
		elseif ($_SESSION["bill_lastname"]=="") {echo '<div class="warning">'.t("Füllen Sie vor der ersten Bestellung alle rot umrandeten Felder aus (siehe rechts)").'!</div>'; $mandatoryfields=false;}
		elseif ($_SESSION["bill_zip"]=="") {echo '<div class="warning">'.t("Füllen Sie vor der ersten Bestellung alle rot umrandeten Felder aus (siehe rechts)").'!</div>'; $mandatoryfields=false;}
		elseif ($_SESSION["bill_city"]=="") {echo '<div class="warning">'.t("Füllen Sie vor der ersten Bestellung alle rot umrandeten Felder aus (siehe rechts)").'!</div>'; $mandatoryfields=false;}
		elseif ($_SESSION["bill_street"]=="") {echo '<div class="warning">'.t("Füllen Sie vor der ersten Bestellung alle rot umrandeten Felder aus (siehe rechts)").'!</div>'; $mandatoryfields=false;}
		elseif ($_SESSION["bill_number"]=="") {echo '<div class="warning">'.t("Füllen Sie vor der ersten Bestellung alle rot umrandeten Felder aus (siehe rechts)").'!</div>'; $mandatoryfields=false;}
		elseif ($_SESSION["bill_country"]=="") {echo '<div class="warning">'.t("Füllen Sie vor der ersten Bestellung alle rot umrandeten Felder aus (siehe rechts)").'!</div>';	$mandatoryfields=false;}
		elseif ($_SESSION["usermail"]=="") {echo '<div class="warning">'.t("Füllen Sie vor der ersten Bestellung alle rot umrandeten Felder aus (siehe rechts)").'!</div>'; $mandatoryfields=false;}

		
		//Gewerbekunde?
		$gewerblich=gewerblich($_SESSION["id_user"]);
		
		//Frachtpauschale?
		if($gewerblich)
		{
			$frachtpauschale=frachtpauschale($_SESSION["id_user"]);
		}
		
		//Shipping Address
		if(!isset($_SESSION["ship_adr_id"]))
		{
			$results=q("SELECT * FROM shop_bill_adr WHERE user_id=".$_SESSION["id_user"]." and active_ship_adr=1 and standard_ship_adr=1 LIMIT 1;", $dbshop, __FILE__, __LINE__);
			if (mysqli_num_rows($results)>0)
			{
				$row=mysqli_fetch_array($results);
				$_SESSION["ship_adr_id"]=$row["adr_id"];
				$_SESSION["ship_company"]=$row["company"];
				$_SESSION["ship_gender"]=$row["gender"];
				$_SESSION["ship_title"]=$row["title"];
				$_SESSION["ship_firstname"]=$row["firstname"];
				$_SESSION["ship_lastname"]=$row["lastname"];
				$_SESSION["ship_street"]=$row["street"];
				$_SESSION["ship_number"]=$row["number"];
				$_SESSION["ship_additional"]=$row["additional"];
				$_SESSION["ship_zip"]=$row["zip"];
				$_SESSION["ship_city"]=$row["city"];
				$_SESSION["ship_country_id"]=$row["country_id"];
				$_SESSION["ship_country"]=$row["country"];
				$_SESSION["ship_standard"]=$row["standard_ship_adr"];
			}
		}

		//Shipping Country
		if ( !isset($_SESSION["ship_country_id"]) || $_SESSION["ship_country_id"]==0 )
		{
			if ( isset($_SESSION["bill_country_id"]) && $_SESSION["bill_country_id"]>0)
			{
				$_SESSION["ship_country_id"]=$_SESSION["bill_country_id"];
				$_SESSION["ship_country"]=$_SESSION["bill_country"];
			}
			else 
			{
				$results=q("SELECT * FROM shop_countries WHERE country_code='".$_SESSION["origin"]."' LIMIT 1;", $dbshop, __FILE__, __LINE__);
				if (mysqli_num_rows($results)>0)
				{
					$row=mysqli_fetch_array($results);
					$_SESSION["ship_country_id"]=$row["id_country"];
					$_SESSION["ship_country"]=$row["country"];
					$_SESSION["bill_country_id"]=$row["id_country"];
					$_SESSION["bill_country"]=$row["country"];
				}
				else
					$_SESSION["ship_country_id"]=1;
					$_SESSION["ship_country"]="Deutschland";
					$_SESSION["bill_country_id"]=1;
					$_SESSION["bill_country"]="Deutschland";
			}
		}

		if (isset($_SESSION["ship_country_id"]) and isset($_SESSION["bill_country_id"]) and $_SESSION["bill_country_id"]>0 and $_SESSION["ship_country_id"]!=$_SESSION["bill_country_id"] and ( !isset($_SESSION["ship_adr_id"]) or !($_SESSION["ship_adr_id"]>0) ))
		{
			$results=q("SELECT * FROM shop_countries WHERE id_country=".$_SESSION["bill_country_id"]." ;", $dbshop, __FILE__, __LINE__);
			if ( mysqli_num_rows($results)>0 )
			{
				$_SESSION["ship_country_id"]=$_SESSION["bill_country_id"];
				$_SESSION["ship_country"]=$_SESSION["bill_country"];
			}
		}

		//check for correct id_payment - if not correct then unset
		if ( isset($_SESSION["id_payment"]) and !($_SESSION["id_payment"]>0) ) unset($_SESSION["id_payment"]);
		if ( isset($_SESSION["id_payment"]) )
		{
			if ($gewerblich) $results=q("SELECT * FROM shop_payment WHERE id_payment=".$_SESSION["id_payment"]." AND shop_id=".$_SESSION["id_shop"]." AND country_id=".$_SESSION["ship_country_id"]." ORDER BY ordering LIMIT 1;", $dbshop, __FILE__, __LINE__);
			else $results=q("SELECT * FROM shop_payment WHERE id_payment=".$_SESSION["id_payment"]." AND shop_id=".$_SESSION["id_shop"]." AND NOT payment = 'Rechnung' AND country_id=".$_SESSION["ship_country_id"]." ORDER BY ordering LIMIT 1;", $dbshop, __FILE__, __LINE__);
			if( mysqli_num_rows($results)==0 )
			{
				unset($_SESSION["id_payment"]);
			}
		}
		//Zahlungsart auslesen
		if (!isset($_SESSION["id_payment"]) or !($_SESSION["id_payment"]>0) or $_SESSION["id_payment"]==9999)
		{
			if ($gewerblich) $results=q("SELECT * FROM shop_payment WHERE shop_id=".$_SESSION["id_shop"]." AND country_id=".$_SESSION["ship_country_id"]." ORDER BY ordering LIMIT 1;", $dbshop, __FILE__, __LINE__);
			else $results=q("SELECT * FROM shop_payment WHERE shop_id=".$_SESSION["id_shop"]." AND NOT payment = 'Rechnung' AND country_id=".$_SESSION["ship_country_id"]." ORDER BY ordering LIMIT 1;", $dbshop, __FILE__, __LINE__);

			if ( mysqli_num_rows($results)==0 )
			{
				$_SESSION["id_payment"]=9999;
				$_SESSION["payment_memo"]="";
				$_SESSION["shipping_details"]='<br /><span style="color:#ff0000";>'.t("Zahlungsart und Versandkosten bitte schriftlich oder telefonisch klären!").'</span>';
			}
			else
			{
				$row=mysqli_fetch_array($results);
				$_SESSION["id_payment"]=$row["id_payment"];
				$_SESSION["payment_memo"]=$row["payment_memo"];
				$_SESSION["shipping_details"]=$row["payment"];
			}
		}
		
		$onlinePayment=checkOnlinePayment($_SESSION["id_payment"]);
	
		//check for correct id_shipping - if not correct then unset
		if ( isset($_SESSION["id_shipping"]) and !($_SESSION["id_shipping"]>0) ) unset($_SESSION["id_shipping"]);
		if ( isset($_SESSION["id_shipping"]) )
		{
			if ($gewerblich) $results=q("SELECT * FROM shop_shipping WHERE id_shipping=".$_SESSION["id_shipping"]." AND payment_id=".$_SESSION["id_payment"]." ORDER BY ordering LIMIT 1;", $dbshop, __FILE__, __LINE__);
			else $results=q("SELECT * FROM shop_shipping WHERE id_shipping=".$_SESSION["id_shipping"]." AND NOT id_shipping=20 AND payment_id=".$_SESSION["id_payment"]." ORDER BY ordering LIMIT 1;", $dbshop, __FILE__, __LINE__);
			if( mysqli_num_rows($results)==0 )
			{
				unset($_SESSION["id_shipping"]);
			}
		}
		//Versandart auslesen
		if (!isset($_SESSION["id_shipping"]) or !($_SESSION["id_shipping"]>0))
		{
			if ($gewerblich) $results=q("SELECT * FROM shop_shipping WHERE payment_id=".$_SESSION["id_payment"]." ORDER BY ordering LIMIT 1;", $dbshop, __FILE__, __LINE__);
			else $results=q("SELECT * FROM shop_shipping WHERE NOT id_shipping=20 AND payment_id=".$_SESSION["id_payment"]." ORDER BY ordering LIMIT 1;", $dbshop, __FILE__, __LINE__);
			if (mysqli_num_rows($results)>0)
			{
				$row=mysqli_fetch_array($results);
				$_SESSION["id_shipping"]=$row["id_shipping"];
				$_SESSION["shipping_net"]=$row["price"];
				if ($gewerblich) $_SESSION["shipping_costs"]=$row["price"];
				else $_SESSION["shipping_costs"]=((100+UST)/100)*$row["price"];
				$_SESSION["shipping_details"].=', '.$row["shipping"];
				$_SESSION["shipping_memo"]=$row["shipping_memo"];
			}
			else
			{
				$_SESSION["id_shipping"]=9999;
				$_SESSION["shipping_net"]=0;
				$_SESSION["shipping_costs"]=0;
			}
		}


		if($frachtpauschale)
		{
			$_SESSION["shipping_net"]=0;
			$_SESSION["shipping_costs"]=0;
		}

		echo '<form action="'.PATHLANG.'/online-shop/kasse/" method="post" name="cart">';

		//Warenkorb
		$results=q("SELECT * FROM shop_carts WHERE shop_id=".$_SESSION["id_shop"]." AND user_id=".$_SESSION["id_user"].";", $dbshop, __FILE__, __LINE__);
		$total=0;
		$collateral_count=0;
		$collateral_sum=0;
		$pos_check=0;
		echo '<table style="width:730px; float:left;" class="hover" id="cart_table">';
		echo '<tr>';
		echo '	<th>'.t("Artikelbeschreibung").'</th>';
		echo '	<th>'.t("Menge").'</th>';
		echo '	';
		echo '	<th>'.t("Einzelpreis").'</th>';
		echo '	<th>'.t("Gesamtpreis").'</th>';
		echo '</tr>';
		unset($_SESSION["bulb_set"]);//Herbstaktion
		if (mysqli_num_rows($results)>0)
		{
			$pos_check=1;
			while($row=mysqli_fetch_array($results))
			{
				if($row["item_id"]==30781 or $row["item_id"]==30702)//Herbstaktion
				{
					$_SESSION["bulb_set"]=1;
					//echo'xxxxxxxxxxxxxxxxxxxxxxx';
				}
				
				$results2=q("SELECT * FROM shop_items_".$_GET["lang"]." WHERE id_item=".$row["item_id"].";", $dbshop, __FILE__, __LINE__);
				$row2=mysqli_fetch_array($results2);
				
				//IMAGE
				$results3=q("SELECT * FROM shop_items WHERE id_item=".$row["item_id"].";", $dbshop, __FILE__, __LINE__);
				$shop_items=mysqli_fetch_array($results3);
				$results3=q("SELECT * FROM cms_articles_images WHERE article_id=".$shop_items["article_id"]." ORDER BY ordering LIMIT 1;", $dbweb, __FILE__, __LINE__);
				$row3=mysqli_fetch_array($results3);
				$results4=q("SELECT * FROM cms_files WHERE original_id='".$row3["file_id"]."' AND imageformat_id=8 LIMIT 1;", $dbweb, __FILE__, __LINE__);
				$row4=mysqli_fetch_array($results4);
				
				//PREIS
				$price=get_prices($row["item_id"], $row["amount"]);

				echo '<tr>';
				echo '	<td style="width:430px;">';
				echo '		<a href="'.PATHLANG.'online-shop/autoteile/'.$row["item_id"].'/'.url_encode($row2["title"]).'/">';
				echo '		<img alt="'.$row4["id_file"].'" class="lazyimage" style="float:left;" src="'.PATH.'images/icons/loaderb64.gif" title="'.$row2["title"].'" />';
				echo '		<p style="margin:0px 10px; width:270px; float:left; font-size:14px; font-weight:bold;">'.$row2["title"].'</p></a>';
				echo '		<p style="margin:10px; float:left;">';
				if($_SESSION["rcid"]!=9999 and $_SESSION["rcid"]>0)
				{
					$results5=q("SELECT * FROM lagerrc AS a, shop_items AS b WHERE b.id_item='".$row["item_id"]."' AND b.MPN=a.ARTNR AND a.RCNR='".$_SESSION["rcid"]."' LIMIT 1;", $dbshop, __FILE__, __LINE__);
					$row5=mysqli_fetch_array($results5);
					if ($row5["ISTBESTAND"]>0) echo '<span style="color:#008000;">'.t("in").' '.$_SESSION["rcbez"].' '.t("vorrätig").'</span>';
					else 
					{
						$results5=q("SELECT * FROM lager AS a, shop_items AS b WHERE b.id_item='".$row["item_id"]."' AND b.MPN=a.ArtNr LIMIT 1;", $dbshop, __FILE__, __LINE__);
						$row5=mysqli_fetch_array($results5);
						if ($row5["ISTBESTAND"]>0) echo '<span style="color:#DE9800;">'.t("in der Zentrale vorrätig").'</span>';
						else echo '<span style="color:#000080;">'.t("Liefertermin auf Anfrage").'</span>';
					}
				}
				else
				{
					$results5=q("SELECT * FROM lager AS a, shop_items AS b WHERE b.id_item='".$row["item_id"]."' AND b.MPN=a.ArtNr LIMIT 1;", $dbshop, __FILE__, __LINE__);
					$row5=mysqli_fetch_array($results5);
					if ($row5["ISTBESTAND"]>10) echo '<span style="color:#008000;">'.t("sofort lieferbar").'</span>';
					elseif ($row5["ISTBESTAND"]>0) echo '<span style="color:#000080;">'.t("Nur noch wenige lieferbar").'</span>';
					else echo '<span style="color:#800000;">'.t("z.Z nicht lieferbar").'</span>';
				}
				if ($price["collateral_total"]>0)
				{
					echo '<br /><span style="font-size:10px; font-weight:bold; color:#ff0000; float:left;">';
					echo '<br />zzgl. € '.number_format($price["collateral_total"], 2).' '.t("Altteilpfand");
					echo '</span>';
					$collateral_count=$collateral_count+$row["amount"];
					$collateral_sum=$collateral_sum+(number_format($price["collateral_total"]*$row["amount"], 2));
					
				}
				echo '	</p>';
				echo '	</td>';
				if($row["item_id"]!=30781 and $row["item_id"]!=30702)//Herbstaktion
				{
					echo '	<td style="width:80px">';
					echo '		<input type="hidden" value="'.$row["item_id"].'" name="item_id[]">';
					echo '		<input type="text" style="width:30px; float:left; text-align:center;" value="'.$row["amount"].'" name="amount[]">';
					echo '		<input type="image" src="'.PATH.'images/icons/16x16/accept.png" style="margin:4px 3px; float:left;" name="cartupdate" value="cartupdate" alt="'.t("Warenkorb aktualisieren").'" title="'.t("Warenkorb aktualisieren").'" />';
					echo '		<input type="image" src="'.PATH.'images/icons/16x16/remove.png" style="margin:4px 0px; float:left;" name="removeitem'.$row["item_id"].'" alt="Artikel entfernen" title="Artikel entfernen" onclick="return confirm(\'Artikel wirklich entfernen?\')" />';
					echo '	</td>';
					echo '	<td style="text-align:right;">€ '.number_format($price["total"], 2).'</td>';
					echo '	<td style="text-align:right;">€ '.number_format($price["total"]*$row["amount"], 2).'</td>';
					echo '</tr>';
					$total+=$price["total"]*$row["amount"];
				}
				else if($row["item_id"]==30781 or $row["item_id"]==30702)//Herbstaktion
				{
					echo '	<td style="width:80px">';
					//echo '		<input type="hidden" value="'.$row["item_id"].'" name="item_id[]">';
					echo '		1';
					echo '	</td>';
					echo '	<td style="text-align:right;">€ '.number_format(0, 2).'</td>';
					echo '	<td style="text-align:right;">€ '.number_format(0, 2).'</td>';
					echo '</tr>';
					//$total+=$price["total"]*$row["amount"];
				}
			}
		}

		//Herbstaktion 2013
		//if(time()>=1382306400 and time()<=1385333999 and $_SESSION["origin"]=="DE")
		if($_SESSION["origin"]=="DE")
		{
			if(!$gewerblich)
			{
				$user_deposit=0;
				$results2=q("SELECT * FROM shop_user_deposit WHERE user_id=".$_SESSION["id_user"].";", $dbshop, __FILE__, __LINE__);
				if(mysqli_num_rows($results2)>0)
				{
					$row2=mysqli_fetch_array($results2);
					$user_deposit=$row2["deposit"];
				}
				round($user_deposit, 2);
				if($user_deposit>0)
				{	
					if($user_deposit>0 and $user_deposit<=$total)
					{
						$total=$total-$user_deposit;	
					}
					else if($user_deposit>$total)
					{
						$user_deposit=$total;
						$total=0;
					}
					
					$_SESSION["user_deposit"]=$user_deposit;
					
					echo '<tr>';
						echo '	<td colspan="3">';
						echo '<span style="font-size:14px; font-weight:bold;">'.t("Guthaben");
						echo '</span>';
						echo '</td>';
					echo '	<td style="text-align:right;">';
					echo '€ -'.number_format($user_deposit, 2);
					echo '	</td>';
					echo '</tr>';
				}
			}
/*
			else if($gewerblich and $total>=150 and !isset($_SESSION["bulb_set"]))
			{
				echo '<script type="text/javascript"> bulb_set_add_dialog(); </script>';
			}
			else if($gewerblich and $total<150 and isset($_SESSION["bulb_set"]))
			{
				$result=q("DELETE FROM shop_carts WHERE shop_id=".$_SESSION["id_shop"]." AND user_id=".$_SESSION["id_user"]." AND (item_id=30702 OR item_id=30781);", $dbshop, __FILE__, __LINE__);
				echo '<script type="text/javascript"> view_cart(); </script>';
				echo '<script type="text/javascript"> cart_update(); </script>';
			}
*/			
		}
		//Ende Herbstaktion 2013


//20€ AKTION AUTOPARTNER
	
		if ($_SESSION["id_shop"]==2 && date("d.m.Y")<="31.12.2013")
		{
			if(isset($_SESSION["ship_country_id"]) )
			{
				if ($_SESSION["ship_country_id"]==1) $ship_germany=true; else $ship_germany=false;
			}
			elseif(isset($_SESSION["bill_country_id"]) )
			{
				if ($_SESSION["bill_country_id"]==1) $ship_germany=true; else $ship_germany=false;
			}
			else 
			{
				$ship_germany=false;
			}
			
			if ($total>=20 && $ship_germany && ($_SESSION["id_shipping"]==150 || $_SESSION["id_shipping"]==151) )
			{
				$_SESSION["shipping_costs"]=0;
				$_SESSION["shipping_net"]=0;

			}
			else
			{
				$resultsx=q("SELECT * FROM shop_shipping WHERE id_shipping=".$_SESSION["id_shipping"].";", $dbshop, __FILE__, __LINE__);
				$rowx=mysqli_fetch_array($resultsx);
				$_SESSION["shipping_net"]=$rowx["price"];
				$_SESSION["shipping_costs"]=((100+UST)/100)*$rowx["price"];
				
			}
		}

//ENDE 20€ AKTION

		//Altteilpfand
		if ( $collateral_count>0 and $collateral_sum>0 )
		{
			$total+=$collateral_sum;

			echo '<tr>';
				echo '	<td colspan="3">';
				echo '<span style="font-size:14px; font-weight:bold;">'.t("Altteilpfand für").' '.$collateral_count.' '.t("Artikel");
				echo '</span>';
				if ( $collateral_count==1 ) echo '<br />'.t("Dieser wird Ihnen nach Rücksendung des Alteils zurück erstattet").'.';
				else echo '<br />'.t("Dieser wird Ihnen nach Rücksendung der Alteile zurück erstattet").'.';
				echo '<br />'.t("Bitte achten Sie bei Altteilen darauf dass diese vollständig und nicht beschädigt sind").'.';
				echo '</td>';
			echo '	<td style="text-align:right;">';
			echo '€ '.number_format($collateral_sum, 2);
			echo '	</td>';
			echo '</tr>';
		}

		//payment options
		if ( isset($_SESSION["ship_country_id"]) and $_SESSION["ship_country_id"]>0)
		{
			$total+=round($_SESSION["shipping_costs"], 2);

			echo '<tr>';
				echo '	<td colspan="3">';
				echo '<a style="font-size:14px; font-weight:bold; float:left;" href="javascript:payment_edit();">'.t("Zahlung und Versand").': ';
				if(strpos($_SESSION["shipping_details"], ',')===false and $_SESSION["id_shipping"]!=9999) echo '<script> payment_edit(); </script>';
				echo $_SESSION["shipping_details"];
				echo '</a>';
				if ($_SESSION["id_shipping"] != 9999) echo '<input style="display:inline; float:right; margin:-5px 0px; background-color:#ff3300; color:white;	cursor:pointer;" type="button" value="'.t("Ändern").'" onclick="payment_edit();" />';
				echo '<span style="float:left">';
				if($frachtpauschale) echo '('.t("Frachtpauschale").')<br />';
				echo '<br />';
				if ($_SESSION["payment_memo"]!="") echo '<i>'.$_SESSION["payment_memo"].'</i><br />';
				if ($_SESSION["shipping_memo"]!="") echo '<i>'.$_SESSION["shipping_memo"].'</i>';
				echo '</span>';
				echo '</a>';
				echo '</td>';
			echo '	<td style="text-align:right;">';
			if ($_SESSION["id_shipping"] != 9999)
				{
				echo '€ '.number_format($_SESSION["shipping_costs"], 2);
				}
			echo '	</td>';
			echo '</tr>';
		}
	

		//Coupon
		/*
		echo '<tr>';
		echo '	<td colspan="3">Gutschein Code Nr. <input type="text" name="coupon_code" value="'.$_POST["coupon_code"].'" /><input type="submit" name="coupon_button" value="OK" /></td>';
		if ($coupon_value>0)
		{
			echo '<td>€ '.number_format($coupon_value, 2).'</td>';
			$total-=$coupon_value;
		}
		else echo '<td></td>';
		echo '</tr>';
		*/
		
	
		//Nettogesamtwert
		if ($gewerblich)
		{
/*
			if ( $_SESSION["ship_country_id"]==1)
			{
				echo '<tr>';
				$total-=$_SESSION["shipping_costs"];
				$discount=$total*0.04;
				$total=$total-$discount;
				$total+=$_SESSION["shipping_costs"];
				echo '	<td colspan="3" style="font-weight:bold;">'.t("Nettogesamtwert").' <span style="color:red;">'.t("abzgl.").' 4% (€ '.number_format($discount, 2).') '.t("Online-Rabatt").'</span></td>';
				echo '	<td style="text-align:right; font-weight:bold; color:red;">€ '.number_format($total, 2).'</td>';
				echo '</tr>';
			}
			else
			{
*/
				echo '<tr>';
				echo '	<td colspan="3" style="font-weight:bold;">'.t("Nettogesamtwert").'</td>';
				echo '	<td style="text-align:right; font-weight:bold;">€ '.number_format($total, 2).'</td>';
				echo '</tr>';
//			}

			//Sonderrabatt
			if ($_SESSION["rcid"]==16 and time()>mktime(0,0,0,8,1,2012) and time()<mktime(0,0,0,10,1,2012))
			{
				if($shipping_id==8 or $shipping_id==50) $special=10;
				else $special=5;
				$total-=$_SESSION["shipping_costs"];
				$total=$total*((100-$special)/100);
				$total+=$_SESSION["shipping_costs"];
				echo '<tr>';
				echo '	<td colspan="3" style="font-weight:bold;>'.t("Sonderrabatt").' '.$special.'%</td>';
				echo '	<td style="font-weight:bold; text-align:right;">€ '.number_format($total, 2).'</td>';
				echo '</tr>';
			}

			$ust=(UST/100)*$total;
			$total=((100+UST)/100)*$total;
			$ship_check='onclick="submit_cart(\''.$_SESSION["shipping_details"].'\');"';
		}
		else 
		{
			$ust=$total/(100+UST)*UST;
			$ship_check='onclick="submit_cart(\'\');"';	
		}

		//total
		echo '<tr>';
		echo '	<td colspan="3"><span style="font-size:14px; font-weight:bold;">'.t("Gesamtpreis").'</span>';
		echo '	<br />'.t("inklusive").' '.UST.'% '.t("Mehrwertsteuer");
		echo '	</td>';
		echo '	<td style="text-align:right;"><span style="font-size:14px; font-weight:bold;">€ '.number_format($total, 2).'</span>';
		echo '	<br />€ '.number_format($ust, 2);
		echo '	</td>';
		echo '</tr>';
		echo '<tr><td colspan="4" id="agb_td"><input type="checkbox" id="form_agbs" name="formagbs" onclick="agbs_accepted();" /><a href="'.PATHLANG.'online-shop/allgemeine-geschaeftsbedingungen/">'.t("Hiermit erkläre ich mich mit den Allgemeinen Geschäftsbedingungen einverstanden.").'</a></td></tr>';
		echo '<tr>';
		echo '	<td colspan="4">';
		echo '		<input style="display:inline; float:left; margin:4px 0px; cursor:pointer;" type="submit" name="cartupdate" value="'.t("Warenkorb aktualisieren").'" />';
		echo '		<input style="display:inline; float:left; margin:4px 0px; cursor:pointer;" type="submit" name="cartclear" value="'.t("Warenkorb leeren").'" onclick="return confirm(\''.t("Warenkorb wirklich leeren").'?\')" />';
		//echo '		<input style="display:inline; float:left; margin:4px 0px; cursor:pointer;" type="submit" name="logout" value="'.t("Abmelden").'" onclick="return confirm(\''.t("Aus dem Shop abmelden").'?\')"/>';	
		echo '		<input type="hidden" name="pos_check" value="'.$pos_check.'" />';
		if ($pos_check>0)
		{
			if ((!$onlinePayment["selected"]) || ($onlinePayment["selected"] && $onlinePayment["method"]=="PayPal") || !$mandatoryfields)
			{
				echo '		<input id="cart_submit_button" type="button" value="'.t("Kostenpflichtig bestellen").'" '.$ship_check.' />';
				echo '		<input type="hidden" name="form_button" value="" />';
			}
			echo '		</form>';
			//FORMULARDATEN FÜR PAYGENIC
			if ($_SESSION["id_user"]!=30234)	// DEMO Kunde
			{
				if ($onlinePayment["selected"] && $onlinePayment["method"]!="PayPal" && $mandatoryfields)
				{
				//	echo PATH;
					$response=post(PATH."soa/", array("API" => "payments", "Action" => "paygenicDoPayment", "PaymentMethod" => $onlinePayment["method"], "id_user" => $_SESSION["id_user"]) );
					//echo $response;
					if ( strpos($response, '<Ack>Success</Ack>') >0 )
					{
						$xml = new SimpleXMLElement($response);
						echo '		<form method="post" action="'.(string)$xml->PaymentData[0]->paygenicurl[0].'">';
						echo '		<input type="hidden" name="Data" value="'.(string)$xml->PaymentData[0]->data[0].'" />';
						echo '		<input type="hidden" name="MerchantID" value="'.(string)$xml->PaymentData[0]->MerchantID[0].'" />';
						echo '		<input type="hidden" name="Len" value="'.(int)$xml->PaymentData[0]->len[0].'" />';
						echo '		<input type="submit" id="cart_submit_button2" value="'.t("Kostenpflichtig bestellen").'" style="display:none"/>';
					//	echo '		<input type="submit" id="cart_submit_button2" value="'.t("Kostenpflichtig bestellen").'" />';
						echo '		</form>';
					}
					echo ' <button id="cart_agb_button" onclick="alert(\'Bitte die AGBs akzeptieren\');">'.t("Kostenpflichtig bestellen").'</button>';
				}
			}
		}
		echo '	</td>';
		echo '</tr>';
		echo '</table>';
	//	echo '</form>';

		echo '<div style="width:250px; border:0; float:right;">';

		
		//bill address
		echo '<table style="width:250px; float:right; text-align:left;" class="hover">';
		echo '<tr>';
		echo '	<th colspan="2">'.t("Rechnungsanschrift").' <a style="float:right; color:lightblue;" href="javascript:bill_edit();">'.t("Ändern").'</a></th>';
		echo '</tr>';
		echo '<tr>';
		if (!isset($_SESSION["bill_adr_id"]) or !($_SESSION["bill_adr_id"]>0)) echo '<td colspan="2" id="empty">';
		else echo ' <td colspan="2">';
		echo '<a href="javascript:bill_edit();">';
		if (isset($_SESSION["bill_company"]) and $_SESSION["bill_company"]!="") echo $_SESSION["bill_company"].'<br />';
		if (isset($_SESSION["bill_gender"]))
		{
			if ($_SESSION["bill_gender"]==0) echo t("Herr").' ';
			elseif($_SESSION["bill_gender"]==1) echo t("Frau").' ';
		}
		if(isset($_SESSION["bill_title"]) and $_SESSION["bill_title"]!="") echo $_SESSION["bill_title"].'<br />';
		if(isset($_SESSION["bill_firstname"])) echo $_SESSION["bill_firstname"].' ';
		if(isset($_SESSION["bill_lastname"])) echo $_SESSION["bill_lastname"];
		echo '<br />';
		if(isset($_SESSION["bill_street"])) echo $_SESSION["bill_street"].' ';
		if(isset($_SESSION["bill_number"])) echo $_SESSION["bill_number"];
		echo '<br />';
		if(isset($_SESSION["bill_additional"]) and $_SESSION["bill_additional"]!="") echo $_SESSION["bill_additional"].'<br />';
		if(isset($_SESSION["bill_zip"])) echo $_SESSION["bill_zip"].' ';
		if(isset($_SESSION["bill_city"])) echo $_SESSION["bill_city"];
		echo '<br /> ';
		if(isset($_SESSION["bill_country"])) echo $_SESSION["bill_country"];
		echo '<br /> ';
		echo '</a>';
		echo '	</td>';
		echo '</tr>';
		echo '</table>';

		//availability
		echo '<table class="hover" style="text-align:left; float:right;">';
		echo '<tr><th>'.t("Erreichbarkeit").' <a style="float:right; color:lightblue;" href="javascript:availability_edit();">'.t("Ändern").'</a></th></tr>';
		echo '<tr>';
		if ($_SESSION["usermail"]=="") echo '<td id="empty">';
		else echo '<td>';
		echo '	<a href="javascript:availability_edit();">';
		echo '	'.t("E-Mail").': ';
		if ( isset($_SESSION["usermail"]) and $_SESSION["usermail"]!="" )
		{
			echo $_SESSION["usermail"];
		}
		echo '<br />';
		echo '	'.t("Telefon").': ';
		if ( isset($_SESSION["userphone"]) and $_SESSION["userphone"]!="" )
		{
			echo $_SESSION["userphone"];
		}
		echo '<br />';
		if ( isset($_SESSION["userfax"]) and $_SESSION["userfax"]!="" )
		{
			echo '	'.t("Telefax").': '.$_SESSION["userfax"].'<br />';
		}
		if ( isset($_SESSION["usermobile"]) and $_SESSION["usermobile"]!="" )
		{
			echo '	'.t("Mobiltelefon").': '.$_SESSION["usermobile"].'<br />';
		}
		echo '	</a>';
		echo '</td>';
		echo '</tr>';
		echo '</table>';

		//ship address
		echo '<table class="hover" style="text-align:left; float:right;">';
		echo '<tr>';
		echo '	<th colspan="2">'.t("Lieferanschrift").' <a style="float:right; color:lightblue;" href="javascript:ship_edit();">'.t("Ändern").'</a></th>';
		echo '</tr>';
		echo '<tr>';
		
		echo '	<td>';
		echo '<a href="javascript:ship_edit();">';
		if ( isset($_SESSION["ship_company"]) and $_SESSION["ship_company"]!="") echo $_SESSION["ship_company"].'<br />';
		if ( isset($_SESSION["ship_gender"]) and $_SESSION["ship_gender"]!="" )
		{
			if ($_SESSION["ship_gender"]==0) echo t("Herr").' ';
			elseif($_SESSION["ship_gender"]==1) echo t("Frau").' ';
		}
		if ( isset($_SESSION["ship_title"]) and $_SESSION["ship_title"]!="" )
		{
			echo $_SESSION["ship_title"];
			$br=true;
		}
		if ( isset($br) and $br ) echo '<br />';

		$br=false;
		if ( isset($_SESSION["ship_firstname"]) and $_SESSION["ship_firstname"]!="" )
		{
			echo $_SESSION["ship_firstname"].' ';
			$br=true;
		}
		if ( isset($_SESSION["ship_lastname"]) and $_SESSION["ship_lastname"]!="" )
		{
			echo $_SESSION["ship_lastname"];
			$br=true;
		}
		if ( isset($br) and $br ) echo '<br />';

		$br=false;
		if ( isset($_SESSION["ship_street"]) and $_SESSION["ship_street"]!="" )
		{
			echo $_SESSION["ship_street"].' ';
			$br=true;
		}
		if ( isset($_SESSION["ship_number"]) and $_SESSION["ship_number"]!="" )
		{
			echo $_SESSION["ship_number"];
			$br=true;
		}
		if ( isset($br) and $br ) echo '<br />';
			
		$br=false;
		if ( isset($_SESSION["ship_additional"]) and $_SESSION["ship_additional"]!="" )
		{
			echo $_SESSION["ship_additional"];
			$br=true;
		}
		if ( isset($br) and $br ) echo '<br />';

		$br=false;
		if ( isset($_SESSION["ship_zip"]) and $_SESSION["ship_zip"]!="" )
		{
			echo $_SESSION["ship_zip"].' ';
			$br=true;
		}
		if ( isset($_SESSION["ship_city"]) and $_SESSION["ship_city"]!="" )
		{
			echo $_SESSION["ship_city"];
			$br=true;
		}
		if ( isset($br) and $br ) echo '<br />';

		$br=false;
		if ( isset($_SESSION["ship_country"]) and $_SESSION["ship_country"]!="" )
		{
			echo $_SESSION["ship_country"];
			$br=true;
		}
		if ( isset($br) and $br ) echo '<br />';
		echo '		</a>';
		echo '	</td>';
		echo '</tr>';
		echo '</table>';
	
		echo '<input type="hidden" name="lang" value="'.$_POST["lang"].'" />';

		//additional information
		echo '<table style="width:250px; margin:0; text-align:left; float:right;" class="hover">';
		echo '<tr>';
		echo '	<th>'.t("Zusätzliche Informationen").' <a style="float:right; color:lightblue;" href="javascript:additional_edit();">'.t("Ändern").'</a></th>';
		echo '</tr>';
		echo '<tr>';
		echo '	<td>';
		echo '		<a href="javascript:additional_edit();">';
		echo 		t("Eigene Bestellnummer").':<br />';
		if ( isset($_SESSION["ordernr"]) and ($_SESSION["ordernr"]!="") )
		{
			echo $_SESSION["ordernr"].'<br /><br />';
		}
		echo		t("Anmerkung zur Bestellung").':<br />';
		if ( isset($_SESSION["comment"]) and ($_SESSION["comment"]!="") )
		{
			echo nl2br($_SESSION["comment"]);
		}
		echo '		</a>';
		echo '	</td>';
		echo '</tr>';
		echo '</table>';

		echo '</div>';
	}


?>
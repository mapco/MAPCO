<?php
include_once("../../mapco_shop_de/functions/cms_send_html_mail.php");
//include_once("../../mapco_shop_de/config.php");

	if (!isset($_POST["mode"])) $_POST["mode"]="first";


	if ( !isset($_POST["OrderID"]) )
	{
		echo '<send_fin_mailResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>OrderID nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine OrderID angegeben werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</send_fin_mailResponse>'."\n";
		exit;
	}

	//GET ORDERdata
	$res_order=q("SELECT * FROM shop_orders WHERE id_order = ".$_POST["OrderID"].";", $dbshop, __FILE__, __LINE__);
	if (mysqli_num_rows($res_order)==0)
	{
		echo '<send_fin_mailResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>OrderID nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Zur OrderID konnte keine Bestellung gefunden werden</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</send_fin_mailResponse>'."\n";
		exit;
	}

	$row_order=mysqli_fetch_array($res_order);
	$response=post(PATH."soa/index.php", array("API" => "crm", "Action" => "crm_get_order_detail", "OrderID" => $_POST["OrderID"]) );
//	echo "#".$response."#";
	if ( strpos($response, '<Ack>Success</Ack>') >0 ) 
	{
		$xml = new SimpleXMLElement($response);
		
	}
	else
	{
		echo '<send_fin_mailResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Order nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es konnten keine Bestelldaten zur ID gefunden werden</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</send_fin_mailResponse>'."\n";
		exit;
	}
	
	//CREATE MAIL
		//get Items
		$item=array();

		//while(isset($xml->Order[0]->Item[$i]))
		for ($i=0; $i<sizeof($xml->Order[0]->OrderItems[0]->Item); $i++)
		{
			$item[]=$xml->Order[0]->OrderItems[0]->Item[$i]->OrderItemAmount[0]."x ".$xml->Order[0]->OrderItems[0]->Item[$i]->OrderItemDesc[0];
		}

		//get Mail-Address
		$mail=$xml->Order[0]->usermail[0];
		if ($mail=="" || $mail=="Invalid Request")
		{
			echo '<send_fin_mailResponse>'."\n";
			echo '	<Ack>Failure</Ack>'."\n";
			echo '	<Error>'."\n";
			echo '		<Code>'.__LINE__.'</Code>'."\n";
			echo '		<shortMsg>Keine valide E-Mail Adresse gefunden</shortMsg>'."\n";
			echo '		<longMsg>Es konnten keine valide E-Mail Adresse zur Bestellung gefunden werden</longMsg>'."\n";
			echo '	</Error>'."\n";
			echo '</send_fin_mailResponse>'."\n";
			exit;
		}
		//Get BuyerUserID
		$BuyerUserID=$xml->Order[0]->buyerUserID[0];
		//Get Name
		$Name="";
		$Name=$xml->Order[0]->bill_company[0];
		if ($Name!="") $Name.=", ";
		$Name.=$xml->Order[0]->bill_firstname[0]." ".$xml->Order[0]->bill_lastname[0];
		
		$shop_id=$xml->Order[0]->shop_id[0];
		
		//GET CMS_USER_ID
		$customer_id=$xml->Order[0]->customer_id[0];
		
		//GET USER_TOKEN
		$res_token=q("SELECT * FROM cms_users WHERE id_user = ".$customer_id.";", $dbweb, __FILE__, __LINE__);
		if (mysqli_num_rows($res_token)==1)
		{
			$row_token=mysqli_fetch_array($res_token);
			$user_token=$row_token["user_token"];
		}
			
		//getSender
		/*
		$res_sender=q("SELECT * FROM cms_contacts WHERE idCmsUser = ".$_SESSION["id_user"].";", $dbweb, __FILE__, __LINE__);
		if (mysqli_num_rows($res_sender)==1)
		{
			$row_sender=mysqli_fetch_array($res_sender);
			$sender=$row_sender["firstname"]." ".$row_sender["lastname"];
			$sender.="<br /><small>".$row_sender["position"]."</small>";
			$sender.="<br />Telefon: ".$row_sender["phone"];
		}
		else
		*/
		{
			if ($shop_id==3)
			{
				$sender="Ihr MAPCO-Team";
				$sender.="<br />Telefon: 033844/758236";
			}
			if ($shop_id==4)
			{
				$sender="Ihr Autopartner-Team";
				$sender.="<br />Telefon: 033844/758239";
			}
			if ($shop_id==5)
			{
				$sender="Ihr MAPCO-Team";
				$sender.="<br />Telefon: 033844/758227";
			}

		}
		
		
		$sendermail="";
		
		//ACCOUNT spezifische Daten
		if ($shop_id==1)
		{
			
			$sendermail="info@mapco.de";
			
			//BETREFF
			if ($_POST["mode"] == "first")
			{
				$subject="Fahrzeugdaten für Ihre Bestellung im MAPCO Onlineshop";
			}
			elseif ($_POST["mode"] == "second")
			{
				$subject="Erinnerung: Fahrzeugdaten für Ihre Bestellung im MAPCO Onlineshop";
			}
			elseif ($_POST["mode"] == "third")
			{
				$subject="Ihre Bestellung bei MAPCO wird in Kürze versendet";
			}

			
			$mail_header='<img style="margin:0px 0px 0px 0px; border:0; padding:0; float:right;" src="'.PATH.'images/newsletter_header.jpg" alt="MAPCO" title="MAPCO">';
			
			/*
			$res_item=q("SELECT a.title FROM shop_items_de as a, shop_orders_items as b WHERE b.order_id = ".$_POST["OrderID"]." AND b.item_id=a.id_item;", $dbshop, __FILE__, __LINE__);
		//	$res_item=q("SELECT a.ItemTitle FROM ebay_orders_items as a, ebay_orders as b WHERE a.OrderID=b.OrderID and b.OrderID = ".$row_order["foreign_OrderID"].";", $dbshop, __FILE__, __LINE__);
			while($row_item=mysqli_fetch_array($res_item))
			{
				$item[sizeof($item)]=$row_item["title"];
			}
			*/
			
		}

		
		
		//ACCOUNT spezifische Daten
		if ($shop_id==3)
		{
			
			$sendermail="ebay@mapco.de";
			
			//BETREFF
			if ($_POST["mode"] == "first")
			{
				$subject="Fahrzeugdaten für Ihre Bestellung bei MAPCO (eBay)";
			}
			elseif ($_POST["mode"] == "second")
			{
				$subject="Erinnerung: Fahrzeugdaten für Ihre Bestellung bei MAPCO (eBay)";
			}
			elseif ($_POST["mode"] == "third")
			{
				$subject="Ihre Bestellung bei MAPCO (eBay) wird in Kürze versendet";
			}
			
			$mail_header='<img style="margin:0px 0px 0px 0px; border:0; padding:0; float:right;" src="'.PATH.'images/newsletter_header.jpg" alt="MAPCO" title="MAPCO">';
			
			/*
			$res_item=q("SELECT a.ItemTitle FROM ebay_orders_items as a, ebay_orders as b WHERE a.OrderID=b.OrderID and b.OrderID = ".$row_order["foreign_OrderID"].";", $dbshop, __FILE__, __LINE__);
			while($row_item=mysqli_fetch_array($res_item))
			{
				$item[sizeof($item)]=$row_item["ItemTitle"];
			}
			*/
			
		}
		
		//ACCOUNT spezifische Daten
		if ($shop_id==4)
		{
	
			$sendermail="ebay@ihr-autopartner.com";

			//BETREFF
			if ($_POST["mode"] == "first")
			{
				$subject="Fahrzeugdaten für Ihre Bestellung bei Ihr-Autopartner (eBay)";
			}
			elseif ($_POST["mode"] == "second")
			{
				$subject="Erinnerung: Fahrzeugdaten für Ihre Bestellung bei Ihr-Autopartner (eBay)";
			}
			elseif ($_POST["mode"] == "third")
			{
				$subject="Ihre Bestellung bei Ihr-Autopartner (eBay) wird in Kürze versendet";
			}
			
			$mail_header='<img style="margin:0px 0px 0px 0px; border:0; padding:0; float:right;" src="http://www.ihr-autopartner.de/images/mail_header.jpg" alt="Ihr Autopartner" title="Ihr Autopartner">';
			/*
			$res_item=q("SELECT a.ItemTitle FROM ebay_orders_items as a, ebay_orders as b WHERE a.OrderID=b.OrderID and b.OrderID = ".$row_order["foreign_OrderID"].";", $dbshop, __FILE__, __LINE__);
			while($row_item=mysqli_fetch_array($res_item))
			{
				$item[sizeof($item)]=$row_item["ItemTitle"];
			}
			*/
		}
		
		
		//MAIL TEXT
		if ($shop_id==1 || $shop_id==4)
		{
			if ($_POST["mode"] == "first")
			{

				$text='<table style="width:600px; border:none; font-family:Arial; line-height:1.5em; "><tr><td>';
				$text.=$mail_header;
				$text.='</td></tr><tr><td><br/>';
				//create Mailtext
				if (trim($Name)=="")
				{
					$text.='<p><b>Hallo';
					if ($BuyerUserID!="") $text.=' '.$BuyerUserID; else $text.='Sehr geehrter Käufer';
					$text.=',</b></p>';
				}
				else
				{
					$text.='<p><b>Hallo '.$Name;
					if ($BuyerUserID!="") $text.=' ('.$BuyerUserID.')';
					$text.=',</b></p>';
				}
				
				if (sizeof($item)==1 && $item[0]!="")
				{
					$text.='<p>Vielen Dank für Ihren Kauf des Artikels <b>'.$item[0].'</b>.</p>';
				}
				elseif (sizeof($item)>1 && !in_array("", $item))
				{
					$text.='<p>Vielen Dank für Ihren Kauf folgender Artikel:<br />';
					for ($i=0; $i<sizeof($item); $i++)
					{
						$text.= '<b>'.$item[$i].'</b><br />';
					}
					$text.= '</p>';
				}
				else
				{
					$text.='<p>Vielen Dank für Ihre Bestellung.</p>';
				}
				$text.='<p>Hinweis: Sofern Sie uns bereits Ihre Fahrzeugdaten mitgeteilt haben, brauchen Sie auf diese Nachricht nicht zu antworten.</p>';
				$text.='<p>Bitte übermitteln Sie uns noch die Schlüsselnummern zu 2.1 und 2.2 und DAS DATUM DER ERSTZULASSUNG sowie die komplette Fahrgestellnummer (17-stellig) Ihres Fahrzeugs, sodass wir Ihnen die passenden Teile zusenden können.<br />';
				$text.='Sollten Sie zu Ihrem Fahrzeug keine Schlüsselnummern haben, senden Sie uns stattdessen die allgemeinen Fahrzeugdaten (Hersteller, Modell, Motorisierung, Sonderausstattungen, etc.)</p>';
				$text.='<br /><p>Vielen Dank!<br />';
				$text.='<br />'.$sender.'</p>';
				$text.='<br /><br />';
				
				$text.='</td></tr><tr><td>';
				$text.='<img style="margin:0px 0px 0px 0px; border:0; padding:0; float:right;" src="'.PATH.'images/schrauber.png" alt="Der Schrauber" title="Der Schrauber">';
				$text.='</td></tr></table>';
			}

			if ($_POST["mode"] == "second")
			{

				$text='<table style="width:600px; border:none; font-family:Arial; line-height:1.5em; "><tr><td>';
				$text.=$mail_header;
				$text.='</td></tr><tr><td><br/>';
				//create Mailtext
				if (trim($Name)=="")
				{
					$text.='<p><b>Hallo';
					if ($BuyerUserID!="") $text.=' '.$BuyerUserID; else $text.='Sehr geehrter Käufer';
					$text.=',</b></p>';
				}
				else
				{
					$text.='<p><b>Hallo '.$Name;
					if ($BuyerUserID!="") $text.=' ('.$BuyerUserID.')';
					$text.=',</b></p>';
				}
				
				$text.='<p><b>Wir möchten Sie daran erinnern uns Ihre Fahrzeugdaten zu Ihrer Bestellung mitzuteilen.</b></p>';
				
				$text.='<p>Hinweis: Sofern Sie uns bereits Ihre Fahrzeugdaten mitgeteilt haben, brauchen Sie auf diese Nachricht nicht zu antworten.</p>';

				$text.='<p>Bitte übermitteln Sie die Schlüsselnummern zu 2.1 und 2.2 und DAS DATUM DER ERSTZULASSUNG sowie die komplette Fahrgestellnummer (17-stellig) Ihres Fahrzeugs, sodass wir Ihnen die passenden Teile zusenden können.<br />';
				$text.='Sollten Sie zu Ihrem Fahrzeug keine Schlüsselnummern haben, senden Sie uns stattdessen die allgemeinen Fahrzeugdaten (Hersteller, Modell, Motorisierung, Sonderausstattungen, etc.)</p>';
				
				$text.='<br /><p>Vielen Dank!<br />';
				$text.='<br />'.$sender.'</p>';
				$text.='<br /><br />';
				
				$text.='</td></tr><tr><td>';
				$text.='<img style="margin:0px 0px 0px 0px; border:0; padding:0; float:right;" src="'.PATH.'images/schrauber.png" alt="Der Schrauber" title="Der Schrauber">';
				$text.='</td></tr></table>';
			}
			
			if ($_POST["mode"] == "third")
			{
				$text='<table style="width:600px; border:none; font-family:Arial; line-height:1.5em; "><tr><td>';
				$text.=$mail_header;
				$text.='</td></tr><tr><td><br/>';
				//create Mailtext
				if (trim($Name)=="")
				{
					$text.='<p><b>Hallo';
					if ($BuyerUserID!="") $text.=' '.$BuyerUserID; else $text.='Sehr geehrter Käufer';
					$text.=',</b></p>';
				}
				else
				{
					$text.='<p><b>Hallo '.$Name;
					if ($BuyerUserID!="") $text.=' ('.$BuyerUserID.')';
					$text.=',</b></p>';
				}

				$text.='<p>Leider haben wir von Ihnen keine Informationen über das/die Fahrzeug(e) erhalten, für welche(s) Sie den/die Artikel bestellt haben. Daher konnten wir vorab nicht prüfen, ob Ihre Bestellung passend ist.<br />';
				$text.='Den Versand Ihrer Bestellung werden wir in Kürze vornehmen.</p>';

				$text.='<br /><p>Mit freundlichen Grüßen<br />';
				$text.='<br />'.$sender.'</p>';
				$text.='<br /><br />';
				
				
				$text.='</td></tr><tr><td>';
				$text.='<img style="margin:0px 0px 0px 0px; border:0; padding:0; float:right;" src="'.PATH.'images/schrauber.png" alt="Der Schrauber" title="Der Schrauber">';
				$text.='</td></tr></table>';

			}

			
		}
		
		//MAIL TEXT
		if ($shop_id==3)
		{
			
			if ($_POST["mode"] == "first")
			{
				$text='<table style="width:600px; border:none; font-family:Arial; line-height:1.5em; "><tr><td>';
				$text.=$mail_header;
				$text.='</td></tr><tr><td><br/>';
				//create Mailtext
				if (trim($Name)=="")
				{
					$text.='<p><b>Hallo';
					if ($BuyerUserID!="") $text.=' '.$BuyerUserID; else $text.='Sehr geehrter Käufer';
					$text.=',</b></p>';
				}
				else
				{
					$text.='<p><b>Hallo '.$Name;
					if ($BuyerUserID!="") $text.=' ('.$BuyerUserID.')';
					$text.=',</b></p>';
				}
				
				if (sizeof($item)==1 && $item[0]!="")
				{
					$text.='<p>Vielen Dank für Ihren Kauf des Artikels <b>'.$item[0].'</b>.</p>';
				}
				elseif (sizeof($item)>1 && !in_array("", $item))
				{
					$text.='<p>Vielen Dank für Ihren Kauf folgender Artikel:<br />';
					for ($i=0; $i<sizeof($item); $i++)
					{
						$text.= '<b>'.$item[$i].'</b><br />';
					}
					$text.= '</p>';
				}
				else
				{
					$text.='<p>Vielen Dank für Ihre Bestellung.</p>';
				}
				
				
				$text.='<p>Hinweis: Sofern Sie uns bereits Ihre Fahrzeugdaten mitgeteilt haben, brauchen Sie auf diese Nachricht nicht zu antworten.</p>';
				
				if (isset($user_token))
				{
					$text.='<p>Um Ihnen die passenden Teile senden zu können, benötigen wir die Fahrzeugdaten. Für eine schnellstmögliche Bearbeitung Ihrer Bestellung teilen Sie uns die Fahrzeugdaten über folgenden Link mit: <a href="http://mapco.de/de/autologin/'.$user_token.'/online-shop/bestellung/'.$row_order["id_order"].'/" target="_blank">Fahrzeugdateneingabe</a></p>';
				
					$text.='<p>Alternativ können Sie uns die Schlüsselnummern zu 2.1 und 2.2 und DAS DATUM DER ERSTZULASSUNG sowie die komplette Fahrgestellnummer (17-stellig) Ihres Fahrzeugs per E-Mail mitteilen (auf diese Nachricht antworten).<br />';
				}
				
				else
				
				{
					$text.='<p>Bitte übermitteln Sie uns noch die Schlüsselnummern zu 2.1 und 2.2 und DAS DATUM DER ERSTZULASSUNG sowie die komplette Fahrgestellnummer (17-stellig) Ihres Fahrzeugs, sodass wir Ihnen die passenden Teile zusenden können.<br />';
				}
				$text.='Sollten Sie zu Ihrem Fahrzeug keine Schlüsselnummern haben, senden Sie uns stattdessen die allgemeinen Fahrzeugdaten (Hersteller, Modell, Motorisierung, Sonderausstattungen, etc.)</p>';
			
				$text.='<br /><p>Vielen Dank!<br />';
				$text.='<br />'.$sender.'</p>';
				$text.='<br /><br />';
				
				
				$text.='</td></tr><tr><td>';
				$text.='<img style="margin:0px 0px 0px 0px; border:0; padding:0; float:right;" src="'.PATH.'images/schrauber.png" alt="Der Schrauber" title="Der Schrauber">';
				$text.='</td></tr></table>';
			}
			
				
			if ($_POST["mode"] == "second")
			{
				$text='<table style="width:600px; border:none; font-family:Arial; line-height:1.5em; "><tr><td>';
				$text.=$mail_header;
				$text.='</td></tr><tr><td><br/>';
				//create Mailtext
				if (trim($Name)=="")
				{
					$text.='<p><b>Hallo';
					if ($BuyerUserID!="") $text.=' '.$BuyerUserID; else $text.='Sehr geehrter Käufer';
					$text.=',</b></p>';
				}
				else
				{
					$text.='<p><b>Hallo '.$Name;
					if ($BuyerUserID!="") $text.=' ('.$BuyerUserID.')';
					$text.=',</b></p>';
				}
				
				$text.='<p><b>Wir möchten Sie daran erinnern uns Ihre Fahrzeugdaten zu Ihrer Bestellung mitzuteilen.</b></p>';
				
				$text.='<p>Hinweis: Sofern Sie uns bereits Ihre Fahrzeugdaten mitgeteilt haben, brauchen Sie auf diese Nachricht nicht zu antworten.</p>';
				
				if (isset($user_token))
				{
					$text.='<p>Die Daten benötigen wir, um Ihnen die passenden Teile senden zu können. Für eine schnellstmögliche Bearbeitung Ihrer Bestellung teilen Sie uns die Fahrzeugdaten über folgenden Link mit: <a href="http://mapco.de/de/autologin/'.$user_token.'/online-shop/bestellung/'.$row_order["id_order"].'/" target="_blank">Fahrzeugdateneingabe</a></p>';
				
					$text.='<p>Alternativ können Sie uns die Schlüsselnummern zu 2.1 und 2.2 und DAS DATUM DER ERSTZULASSUNG sowie die komplette Fahrgestellnummer (17-stellig) Ihres Fahrzeugs per E-Mail mitteilen (auf diese Nachricht antworten).<br />';
				}
				else
				
				{
					$text.='<p>Bitte übermitteln Sie uns noch die Schlüsselnummern zu 2.1 und 2.2 und DAS DATUM DER ERSTZULASSUNG sowie die komplette Fahrgestellnummer (17-stellig) Ihres Fahrzeugs, sodass wir Ihnen die passenden Teile zusenden können.<br />';
				}
				$text.='Sollten Sie zu Ihrem Fahrzeug keine Schlüsselnummern haben, senden Sie uns stattdessen die allgemeinen Fahrzeugdaten (Hersteller, Modell, Motorisierung, Sonderausstattungen, etc.)</p>';
			
				$text.='<br /><p>Vielen Dank!<br />';
				$text.='<br />'.$sender.'</p>';
				$text.='<br /><br />';
				
				
				$text.='</td></tr><tr><td>';
				$text.='<img style="margin:0px 0px 0px 0px; border:0; padding:0; float:right;" src="'.PATH.'images/schrauber.png" alt="Der Schrauber" title="Der Schrauber">';
				$text.='</td></tr></table>';
			}


			if ($_POST["mode"] == "third")
			{
				$text='<table style="width:600px; border:none; font-family:Arial; line-height:1.5em; "><tr><td>';
				$text.=$mail_header;
				$text.='</td></tr><tr><td><br/>';
				//create Mailtext
				if (trim($Name)=="")
				{
					$text.='<p><b>Hallo';
					if ($BuyerUserID!="") $text.=' '.$BuyerUserID; else $text.='Sehr geehrter Käufer';
					$text.=',</b></p>';
				}
				else
				{
					$text.='<p><b>Hallo '.$Name;
					if ($BuyerUserID!="") $text.=' ('.$BuyerUserID.')';
					$text.=',</b></p>';
				}

				$text.='<p>Leider haben wir von Ihnen keine Informationen über das/die Fahrzeug(e) erhalten, für welche(s) Sie den/die Artikel bestellt haben. Daher konnten wir vorab nicht prüfen, ob Ihre Bestellung passend ist.<br />';
				$text.='Den Versand Ihrer Bestellung werden wir in Kürze vornehmen.</p>';

				$text.='<br /><p>Mit freundlichen Grüßen!<br />';
				$text.='<br />'.$sender.'</p>';
				$text.='<br /><br />';
				
				
				$text.='</td></tr><tr><td>';
				$text.='<img style="margin:0px 0px 0px 0px; border:0; padding:0; float:right;" src="'.PATH.'images/schrauber.png" alt="Der Schrauber" title="Der Schrauber">';
				$text.='</td></tr></table>';

			}
		}
		
		SendMail($mail, $sendermail, $subject, $text);
		//SendMail("nputzing@mapco.de", $sendermail, $subject, $text);
		//SendMail("mwosgien@mapco.de", $sendermail, $subject, $text);
	
	//UPDATE FINMAIL IN SHOP ORDERS
//	$mailcount=$row_order["fz_fin_mail_count"];
//	$mailcount++;
	if (isset($_POST["manual"]) && $_POST["manual"]==1)
	{
		q("UPDATE shop_orders SET fz_fin_mail_count = 1, fz_fin_mail_lastsent = ".time()." WHERE id_order = ".$_POST["OrderID"].";", $dbshop, __FILE__, __LINE__);
	}
	

	echo '<send_fin_mailResponse>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	if (isset($_POST["manual"]) && $_POST["manual"]==1)
	{
		echo '	<fz_fin_mail_count>1</fz_fin_mail_count>'."\n";
		echo '	<fz_fin_mail_lastsent>'.time().'</fz_fin_mail_lastsent>'."\n";
	}
	echo '</send_fin_mailResponse>'."\n";
?>
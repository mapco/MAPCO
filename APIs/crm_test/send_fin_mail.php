<?php
include_once("../../mapco_shop_de/functions/cms_send_html_mail.php");
//include_once("../../mapco_shop_de/config.php");

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
	if (mysql_num_rows($res_order)==0)
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

	$row_order=mysql_fetch_array($res_order);
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
		$items=array();

		//get Mail-Address
		$mail=$xml->Order[0]->usermail[0];
		//Get BuyerUserID
		$BuyerUserID=$xml->Order[0]->buyerUserID[0];
		//Get Name
		$Name="";
		$Name=$xml->Order[0]->bill_company[0];
		if ($Name!="") $Name.=", ";
		$Name.=$xml->Order[0]->bill_firstname[0]." ".$xml->Order[0]->bill_lastname[0];
		
		$shop_id=$xml->Order[0]->shop_id[0];
		
		//getSender
		$res_sender=q("SELECT * FROM cms_contacts WHERE idCmsUser = ".$_SESSION["id_user"].";", $dbweb, __FILE__, __LINE__);
		if (mysql_num_rows($res_sender)==1)
		{
			$row_sender=mysql_fetch_array($res_sender);
			$sender=$row_sender["firstname"]." ".$row_sender["lastname"];
			$sender.="<br /><small>".$row_sender["position"]."</small>";
			$sender.="<br />Telefon: ".$row_sender["phone"];
		}
		else
		{
			if ($shop_id==3)
			{
				$sender="Ihr MAPCO-Team";
				$sender.="<br />Telefon: 033844/758227";
			}
			if ($shop_id==4)
			{
				$sender="Ihr Autopartner-Team";
				$sender.="<br />Telefon: 033844/758239";
			}
		}
		
		
		$sendermail="";
		if ($shop_id==3)
		{
			
			$sendermail="ebay@mapco.de";
			
			$subject="Fahrzeugdaten für Ihre Bestellung bei MAPCO (eBay)";
			
			$mail_header='<img style="margin:0px 0px 0px 0px; border:0; padding:0; float:right;" src="'.PATH.'images/newsletter_header.jpg" alt="MAPCO" title="MAPCO">';
			
			$res_item=q("SELECT a.ItemTitle FROM ebay_orders_items as a, ebay_orders as b WHERE a.OrderID=b.OrderID and b.OrderID = ".$row_order["foreign_OrderID"].";", $dbshop, __FILE__, __LINE__);
			while($row_item=mysql_fetch_array($res_item))
			{
				$item[sizeof($item)]=$row_item["ItemTitle"];
			}
			
			
		}
		if ($shop_id==4)
		{
	
			$sendermail="ebay@ihr-autopartner.de";

			$subject="Fahrzeugdaten für Ihre Bestellung bei Ihr-Autopartner (eBay)";
			
			$mail_header='<img style="margin:0px 0px 0px 0px; border:0; padding:0; float:right;" src="http://www.ihr-autopartner.de/images/mail_header.jpg" alt="Ihr Autopartner" title="Ihr Autopartner">';
			
			$res_item=q("SELECT a.ItemTitle FROM ebay_orders_items as a, ebay_orders as b WHERE a.OrderID=b.OrderID and b.OrderID = ".$row_order["foreign_OrderID"].";", $dbshop, __FILE__, __LINE__);
			while($row_item=mysql_fetch_array($res_item))
			{
				$item[sizeof($item)]=$row_item["ItemTitle"];
			}

		}
		
		$text='<table style="width:600px; border:none; font-family:Arial; line-height:1.5em; "><tr><td>';
		$text.=$mail_header;
		$text.='</td></tr><tr><td><br/>';
		//create Mailtext
		if (trim($Name)=="")
		{
			$text.='<p><b>Hallo '.$BuyerUserID.',</b></p>';
		}
		else
		{
			$text.='<p><b>Hallo '.$Name.' ('.$BuyerUserID.'),</b></p>';
		}
		
		if (sizeof($item)==1 && $item[0]!="")
		{
			$text.='<p>Vielen Dank für Ihren Kauf des Artikels <b>'.$item[0].'</b>.</p>';
		}
		else
		{
			$text.='<p>Vielen Dank für Ihre Bestellung.</p>';
		}
		
		$text.='Bitte übermitteln Sie uns noch die Schlüsselnummern zu 2.1 und 2.2 und DAS DATUM DER ERSTZULASSUNG sowie die komplette Fahrgestellnummer (17-stellig) Ihres Fahrzeugs, sodass wir Ihnen die passenden Teile zusenden können.<br />';
		$text.='Sollten Sie zu Ihrem Fahrzeug keine Schlüsselnummern haben, senden Sie uns stattdessen die allgemeinen Fahrzeugdaten (Hersteller, Modell, Motorisierung, Sonderausstattungen, etc.)</p>';
		$text.='<p>Sofern Sie uns bereits Ihre Fahrzeugdaten mitgeteilt haben, brauchen Sie auf diese Nachricht nicht zu antworten.</p>';
		$text.='<br /><p>Vielen Dank!<br />';
		$text.='<br />'.$sender.'</p>';
		$text.='<br /><br />';
		
		$text.='</td></tr><tr><td>';
		$text.='<img style="margin:0px 0px 0px 0px; border:0; padding:0; float:right;" src="'.PATH.'images/schrauber.png" alt="Der Schrauber" title="Der Schrauber">';
		$text.='</td></tr></table>';		
		
		SendMail($mail, $sendermail, $subject, $text);
		//SendMail("nputzing@mapco.de", $sendermail, $subject, $text);
	
	//UPDATE FINMAIL IN SHOP ORDERS
	$mailcount=$row_order["fz_fin_mail_count"];
	$mailcount++;
	q("UPDATE shop_orders SET fz_fin_mail_count = ".$mailcount.", fz_fin_mail_lastsent = ".time()." WHERE id_order = ".$_POST["OrderID"].";", $dbshop, __FILE__, __LINE__);
	

	echo '<send_fin_mailResponse>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	echo '</send_fin_mailResponse>'."\n";

<?php

	//************************ 
	//*     SOA2-SERVICE     *
	//************************

/*
	mode:	first	->	erste Fahrzeugdatenanfrage-Email
			second	->	zweite Fahrzeugdatenanfrage-Email
			third	->	dritte Fahrzeugdatenanfrage-Email	
*/
	
	$required=array("mode"		=> "text",
				    "OrderID"	=> "numeric");
					
	check_man_params($required);				

	//GET ORDER-DATA
	$res_order=q("SELECT * FROM shop_orders WHERE id_order = ".$_POST["OrderID"].";", $dbshop, __FILE__, __LINE__);
	if (mysqli_num_rows($res_order)==0)
	{
		show_error(9811, 7, __FILE__, __LINE__, "Keine Bestellung gefunden. OrderID: ".$_POST["OrderID"]);
		exit;
	}
	$row_order=mysqli_fetch_array($res_order);
	
	//GET ORDER DETAIL
	$post_data=array();
	$post_data["API"]="shop";
	$post_data["APIRequest"]="OrderDetailGet";
	$post_data["OrderID"]=$_POST["OrderID"];		
	$postdata=http_build_query($post_data);
	$xml=soa2($postdata);
	if($xml->Ack[0]!="Success")
	{
		show_error(9812, 7, __FILE__, __LINE__, "Keine Bestelldaten gefunden. OrderID: ".$_POST["OrderID"]);
		exit;
	}
	
		
	//CREATE MAIL DATA
	//get Items
	$item=array();
	for ($i=0; $i<sizeof($xml->Order[0]->OrderItems[0]->Item); $i++)
	{
		$item[]=$xml->Order[0]->OrderItems[0]->Item[$i]->OrderItemAmount[0]."x ".$xml->Order[0]->OrderItems[0]->Item[$i]->OrderItemDesc[0];
	}

	//get Mail-Address
	$mail=(string)$xml->Order[0]->usermail[0];
	if ($mail=="" || $mail=="Invalid Request")
	{
		show_error(9814, 7, __FILE__, __LINE__, "Keine email-Adresse gefunden. OrderID: ".$_POST["OrderID"]);
		exit;
	}
	
	//Get BuyerUserID
	$BuyerUserID=$xml->Order[0]->buyerUserID[0];
	
	//Get Name
	$Name="";
	$Name=$xml->Order[0]->bill_company[0];
	if ($Name!="") $Name.=", ";
	$Name.=$xml->Order[0]->bill_firstname[0]." ".$xml->Order[0]->bill_lastname[0];
	
	//Get shop_id
	$shop_id=$xml->Order[0]->shop_id[0];
	
	//GET CMS_USER_ID
	$customer_id=(int)$xml->Order[0]->customer_id[0];
	
	//GET USER_TOKEN AND cms_users DATA
	$user_token="";
	$user_origin="";
	$res_token=q("SELECT * FROM cms_users WHERE id_user = ".$customer_id.";", $dbweb, __FILE__, __LINE__);
	if (mysqli_num_rows($res_token)==1)
	{
		$row_token=mysqli_fetch_array($res_token);
		$user_token=$row_token["user_token"];
		$user_origin=$row_token["origin"];
	}
	
	//GET ARTICLES
	$article_id=35905;
	$msgtext="";
	$subject="";
	
	if($user_origin!="DE" and $user_origin!="CH" and $user_origin!="AT")
	{
		$post_data=array();
		$post_data["API"]="cms";
		$post_data["APIRequest"]="ArticleTranslationGet";
		$post_data["article_id"]=$article_id;
		$post_data["id_language"]=2;		
		$postdata=http_build_query($post_data);
		$xml=soa2($postdata);
		if($xml->Ack[0]!="Success")
		{
			show_error(9815, 1, __FILE__, __LINE__, "Keine Übersetzung des Beitrags gefunden. article_id: ".$article_id);
			//exit;
		}
		else
			$article_id=$xml->article_id_trans[0];
	}

	$res_cms_articles=q("SELECT introduction, article FROM cms_articles WHERE id_article=".$article_id.";", $dbweb, __FILE__, __LINE__);
	$cms_articles=mysqli_fetch_assoc($res_cms_articles);
	$subject=$cms_articles["introduction"];
	$msgtext=$cms_articles["article"];	

	//SHOPSPEZIFISCHE DATEN
	$sendermail="";
	$shop="";
	$mail_header="";
	$phone="";
	if($shop_id==3)
	{
		$sendermail="ebay@mapco.de";
		$shop="MAPCO (ebay)";
		$mail_header='<img style="margin:0px 0px 0px 0px; border:0; padding:0; float:right;" src="'.PATH.'images/newsletter_header.jpg" alt="MAPCO" title="MAPCO">';
		$phone="033844/758236";
	}
	else if($shop_id==4)
	{
		$sendermail="ebay@ihr-autopartner.com";
		$shop="Ihr Autopartner (ebay)";
		$mail_header='<img style="margin:0px 0px 0px 0px; border:0; padding:0; float:right;" src="http://www.ihr-autopartner.de/images/mail_header.jpg" alt="Ihr Autopartner" title="Ihr Autopartner">';
		$phone="033844/758280";
	}
	
	//CREATE MAIL
	if($shop_id==3)
	{
		if($_POST["mode"]=="first" or $_POST["mode"]=="second" or $_POST["mode"]=="third")
		{
			$subject=str_replace("<!-- SHOP -->", $shop, $subject);
			$subject=str_replace("<!-- ORDER-ID -->", $_POST["OrderID"], $subject);
			
			$msgtext=str_replace("<!-- MAIL-HEADER -->", $mail_header, $msgtext);
			if (trim($Name)=="")
			{
				if($BuyerUserID!="")
				{
					$msgtext=str_replace("<!-- NAME -->", $BuyerUserID, $msgtext);
					$msgtext=cutout($msgtext, "<!-- BUYER-START -->", "<!-- BUYER-END -->");
				}
			}
			else
			{
				if($BuyerUserID!="")
					$Name_buf=$Name." (".$BuyerUserID.")";
				$msgtext=str_replace("<!-- NAME -->", $Name_buf, $msgtext);	
				$msgtext=cutout($msgtext, "<!-- BUYER-START -->", "<!-- BUYER-END -->");
			}
			$msgtext=str_replace("<!-- SHOP -->", $shop, $msgtext);
			$msgtext=str_replace("<!-- PHONE -->", $phone, $msgtext);
			$msgtext=str_replace("<!-- PATH -->", PATH, $msgtext);
		}
		if($_POST["mode"]=="first")
		{
			$subject=str_replace("<!-- SUBJECT-FIRST-START -->", "", $subject);
			$subject=str_replace("<!-- SUBJECT-FIRST-END -->", "", $subject);
			$subject=cutout($subject, "<!-- SUBJECT-SECOND-START -->", "<!-- SUBJECT-SECOND-END -->");
			$subject=cutout($subject, "<!-- SUBJECT-THIRD-START -->", "<!-- SUBJECT-THIRD-END -->");
			
			if (sizeof($item)==1 && $item[0]!="")
			{
				$msgtext=str_replace("<!-- ITEMS -->", $item[0], $msgtext);
				$msgtext=cutout($msgtext, "<!-- THANKS-2-START -->", "<!-- THANKS-3-END -->");
			}
			elseif (sizeof($item)>1 && !in_array("", $item))
			{
				$buf="";
				for ($i=0; $i<sizeof($item); $i++)
				{
					$buf.= '<b>'.$item[$i].'</b><br />';
				}
				$msgtext=str_replace("<!-- ITEMS -->", $buf, $msgtext);
				$msgtext=cutout($msgtext, "<!-- THANKS-1-START -->", "<!-- THANKS-1-END -->");
				$msgtext=cutout($msgtext, "<!-- THANKS-3-START -->", "<!-- THANKS-3-END -->");
			}
			else
			{
				$msgtext=cutout($msgtext, "<!-- THANKS-1-START -->", "<!-- THANKS-2-END -->");
			}
			$msgtext=cutout($msgtext, "<!-- TOP-START -->", "<!-- TOP-END -->");
			$msgtext=str_replace("<!-- TOKEN -->", $user_token, $msgtext);
			$msgtext=str_replace("<!-- ORDER-ID -->", $_POST["OrderID"], $msgtext);
			$msgtext=cutout($msgtext, "<!-- KBA-TWO-START -->", "<!-- KBA-TWO-END -->");
			$msgtext=cutout($msgtext, "<!-- MAIN-TWO-START -->", "<!-- MAIN-TWO-END -->");
		}
		else if($_POST["mode"]=="second")
		{
			$subject=cutout($subject, "<!-- SUBJECT-FIRST-START -->", "<!-- SUBJECT-FIRST-END -->");
			$subject=str_replace("<!-- SUBJECT-SECOND-START -->", "", $subject);
			$subject=str_replace("<!-- SUBJECT-SECOND-END -->", "", $subject);
			$subject=cutout($subject, "<!-- SUBJECT-THIRD-START -->", "<!-- SUBJECT-THIRD-END -->");
			
			$msgtext=cutout($msgtext, "<!-- THANKS-1-START -->", "<!-- THANKS-3-END -->");
			$msgtext=str_replace("<!-- TOKEN -->", $user_token, $msgtext);
			$msgtext=str_replace("<!-- ORDER-ID -->", $_POST["OrderID"], $msgtext);
			$msgtext=cutout($msgtext, "<!-- KBA-TWO-START -->", "<!-- KBA-TWO-END -->");
			$msgtext=cutout($msgtext, "<!-- MAIN-TWO-START -->", "<!-- MAIN-TWO-END -->");
		}
		else if($_POST["mode"]=="third")
		{
			$subject=cutout($subject, "<!-- SUBJECT-FIRST-START -->", "<!-- SUBJECT-FIRST-END -->");
			$subject=cutout($subject, "<!-- SUBJECT-SECOND-START -->", "<!-- SUBJECT-SECOND-END -->");
			$subject=str_replace("<!-- SUBJECT-THIRD-START -->", "", $subject);
			$subject=str_replace("<!-- SUBJECT-THIRD-END -->", "", $subject);
			
			$msgtext=cutout($msgtext, "<!-- THANKS-1-START -->", "<!-- THANKS-3-END -->");
			$msgtext=cutout($msgtext, "<!-- TOP-START -->", "<!-- TOP-END -->");
			$msgtext=cutout($msgtext, "<!-- MAIN-ONE-START -->", "<!-- MAIN-ONE-END -->");
		}
	}
	else if($shop_id==4)
	{
		if($_POST["mode"]=="first" or $_POST["mode"]=="second" or $_POST["mode"]=="third")
		{
			$subject=str_replace("<!-- SHOP -->", $shop, $subject);
			$subject=str_replace("<!-- ORDER-ID -->", $_POST["OrderID"], $subject);
			
			$msgtext=str_replace("<!-- MAIL-HEADER -->", $mail_header, $msgtext);
			if (trim($Name)=="")
			{
				if($BuyerUserID!="")
				{
					$msgtext=str_replace("<!-- NAME -->", $BuyerUserID, $msgtext);
					$msgtext=cutout($msgtext, "<!-- BUYER-START -->", "<!-- BUYER-END -->");
				}
			}
			else
			{
				if($BuyerUserID!="")
					$Name_buf=$Name." (".$BuyerUserID.")";
				$msgtext=str_replace("<!-- NAME -->", $Name_buf, $msgtext);	
				$msgtext=cutout($msgtext, "<!-- BUYER-START -->", "<!-- BUYER-END -->");
			}
			$msgtext=str_replace("<!-- SHOP -->", $shop, $msgtext);
			$msgtext=str_replace("<!-- PHONE -->", $phone, $msgtext);
			$msgtext=str_replace("<!-- PATH -->", PATH, $msgtext);
		}
		if($_POST["mode"]=="first")
		{
			$subject=str_replace("<!-- SUBJECT-FIRST-START -->", "", $subject);
			$subject=str_replace("<!-- SUBJECT-FIRST-END -->", "", $subject);
			$subject=cutout($subject, "<!-- SUBJECT-SECOND-START -->", "<!-- SUBJECT-SECOND-END -->");
			$subject=cutout($subject, "<!-- SUBJECT-THIRD-START -->", "<!-- SUBJECT-THIRD-END -->");
			
			if (sizeof($item)==1 && $item[0]!="")
			{
				$msgtext=str_replace("<!-- ITEMS -->", $item[0], $msgtext);
				$msgtext=cutout($msgtext, "<!-- THANKS-2-START -->", "<!-- THANKS-3-END -->");
			}
			elseif (sizeof($item)>1 && !in_array("", $item))
			{
				$buf="";
				for ($i=0; $i<sizeof($item); $i++)
				{
					$buf.= '<b>'.$item[$i].'</b><br />';
				}
				$msgtext=str_replace("<!-- ITEMS -->", $buf, $msgtext);
				$msgtext=cutout($msgtext, "<!-- THANKS-1-START -->", "<!-- THANKS-1-END -->");
				$msgtext=cutout($msgtext, "<!-- THANKS-3-START -->", "<!-- THANKS-3-END -->");
			}
			else
			{
				$msgtext=cutout($msgtext, "<!-- THANKS-1-START -->", "<!-- THANKS-2-END -->");
			}
			$msgtext=cutout($msgtext, "<!-- TOP-START -->", "<!-- TOP-END -->");
			//$msgtext=str_replace("<!-- TOKEN -->", $user_token, $msgtext);
			//$msgtext=str_replace("<!-- ORDER-ID -->", $_POST["OrderID"], $msgtext);
			$msgtext=cutout($msgtext, "<!-- TOKEN-START -->", "<!-- TOKEN-END -->");
			$msgtext=cutout($msgtext, "<!-- KBA-ONE-START -->", "<!-- KBA-ONE-END -->");
			$msgtext=cutout($msgtext, "<!-- MAIN-TWO-START -->", "<!-- MAIN-TWO-END -->");
		}
		else if($_POST["mode"]=="second")
		{
			$subject=cutout($subject, "<!-- SUBJECT-FIRST-START -->", "<!-- SUBJECT-FIRST-END -->");
			$subject=str_replace("<!-- SUBJECT-SECOND-START -->", "", $subject);
			$subject=str_replace("<!-- SUBJECT-SECOND-END -->", "", $subject);
			$subject=cutout($subject, "<!-- SUBJECT-THIRD-START -->", "<!-- SUBJECT-THIRD-END -->");
			
			$msgtext=cutout($msgtext, "<!-- THANKS-1-START -->", "<!-- THANKS-3-END -->");
			//$msgtext=str_replace("<!-- TOKEN -->", $user_token, $msgtext);
			//$msgtext=str_replace("<!-- ORDER-ID -->", $_POST["OrderID"], $msgtext);
			$msgtext=cutout($msgtext, "<!-- TOKEN-START -->", "<!-- TOKEN-END -->");
			$msgtext=cutout($msgtext, "<!-- KBA-ONE-START -->", "<!-- KBA-ONE-END -->");
			$msgtext=cutout($msgtext, "<!-- MAIN-TWO-START -->", "<!-- MAIN-TWO-END -->");
		}
		else if($_POST["mode"]=="third")
		{
			$subject=cutout($subject, "<!-- SUBJECT-FIRST-START -->", "<!-- SUBJECT-FIRST-END -->");
			$subject=cutout($subject, "<!-- SUBJECT-SECOND-START -->", "<!-- SUBJECT-SECOND-END -->");
			$subject=str_replace("<!-- SUBJECT-THIRD-START -->", "", $subject);
			$subject=str_replace("<!-- SUBJECT-THIRD-END -->", "", $subject);
			
			$msgtext=cutout($msgtext, "<!-- THANKS-1-START -->", "<!-- THANKS-3-END -->");
			$msgtext=cutout($msgtext, "<!-- TOP-START -->", "<!-- TOP-END -->");
			$msgtext=cutout($msgtext, "<!-- MAIN-ONE-START -->", "<!-- MAIN-ONE-END -->");
		}
	}

/*	
//********************************ALT-START******************************************************	

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

	
	
	$sendermaila="";
	
	//ACCOUNT spezifische Daten
	
	if ($shop_id==1)
	{
		$sendermaila="info@mapco.de";
		
		//BETREFF
		if ($_POST["mode"] == "first")
		{
			$subjecta="Fahrzeugdaten für Ihre Bestellung im MAPCO Onlineshop";
		}
		elseif ($_POST["mode"] == "second")
		{
			$subjecta="Erinnerung: Fahrzeugdaten für Ihre Bestellung im MAPCO Onlineshop";
		}
		elseif ($_POST["mode"] == "third")
		{
			$subjecta="Ihre Bestellung bei MAPCO wird in Kürze versendet";
		}

		
		$mail_headera='<img style="margin:0px 0px 0px 0px; border:0; padding:0; float:right;" src="'.PATH.'images/newsletter_header.jpg" alt="MAPCO" title="MAPCO">';
	}
	

	
	
	//ACCOUNT spezifische Daten
	if ($shop_id==3)
	{
		
		$sendermaila="ebay@mapco.de";
		
		//BETREFF
		if ($_POST["mode"] == "first")
		{
			$subjecta="Fahrzeugdaten für Ihre Bestellung bei MAPCO (eBay)";
		}
		elseif ($_POST["mode"] == "second")
		{
			$subjecta="Erinnerung: Fahrzeugdaten für Ihre Bestellung bei MAPCO (eBay)";
		}
		elseif ($_POST["mode"] == "third")
		{
			$subjecta="Ihre Bestellung bei MAPCO (eBay) wird in Kürze versendet";
		}
		
		$mail_headera='<img style="margin:0px 0px 0px 0px; border:0; padding:0; float:right;" src="'.PATH.'images/newsletter_header.jpg" alt="MAPCO" title="MAPCO">';
		
	}
	
	//ACCOUNT spezifische Daten
	if ($shop_id==4)
	{

		$sendermaila="ebay@ihr-autopartner.com";

		//BETREFF
		if ($_POST["mode"] == "first")
		{
			$subjecta="Fahrzeugdaten für Ihre Bestellung bei Ihr-Autopartner (eBay)";
		}
		elseif ($_POST["mode"] == "second")
		{
			$subjecta="Erinnerung: Fahrzeugdaten für Ihre Bestellung bei Ihr-Autopartner (eBay)";
		}
		elseif ($_POST["mode"] == "third")
		{
			$subjecta="Ihre Bestellung bei Ihr-Autopartner (eBay) wird in Kürze versendet";
		}
		
		$mail_headera='<img style="margin:0px 0px 0px 0px; border:0; padding:0; float:right;" src="http://www.ihr-autopartner.de/images/mail_header.jpg" alt="Ihr Autopartner" title="Ihr Autopartner">';
	}
	
	
	//MAIL TEXT
	if ($shop_id==1 || $shop_id==4)
	{
		if ($_POST["mode"] == "first")
		{

			$text='<table style="width:600px; border:none; font-family:Arial; line-height:1.5em; "><tr><td>';
			$text.=$mail_headera;
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
			$text.=$mail_headera;
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
			$text.=$mail_headera;
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
			$text.=$mail_headera;
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
			$text.=$mail_headera;
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
			$text.=$mail_headera;
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
	
	//SendMail($mail, $sendermaila, $subjecta, $text);
	//SendMail("nputzing@mapco.de", $sendermaila, $subjecta, $text);
	//SendMail("mwosgien@mapco.de", $sendermaila, $subjecta, $text);
//*************************************ALT-ENDE*******************************************
*/
/*	
	//kopie an mwosgien
	$post_data=array();
	$post_data["API"]="cms";
	$post_data["APIRequest"]="MailSend";
	$post_data["ToReceiver"]="mwosgien@mapco.de";	
	$post_data["FromSender"]=$sendermaila;
	$post_data["Subject"]=$subjecta." *****ALT*****";
	$post_data["MsgText"]=$text;
	
	$postdata=http_build_query($post_data);
	
	$responseXml = post(PATH."soa2/", $postdata);
	$use_errors = libxml_use_internal_errors(true);
	try
	{
		$response = new SimpleXMLElement($responseXml);
	}
	catch(Exception $e)
	{
		//echo $e;
	}
	libxml_clear_errors();
	libxml_use_internal_errors($use_errors);
*/

//*****************mail versand und beitrag speichern**********************************************************

	//MailSend
	$post_data=array();
	$post_data["API"]="cms";
	$post_data["APIRequest"]="MailSend";
	$post_data["ToReceiver"]=$mail;	
	$post_data["FromSender"]=$sendermail;
	$post_data["Subject"]=$subject;
	$post_data["MsgText"]=$msgtext;
	
	$postdata=http_build_query($post_data);
	
	$responseXml = post(PATH."soa2/", $postdata);
	$use_errors = libxml_use_internal_errors(true);
	try
	{
		$response = new SimpleXMLElement($responseXml);
	}
	catch(Exception $e)
	{
		//echo $e;
	}
	libxml_clear_errors();
	libxml_use_internal_errors($use_errors);
	
	//save email in cms_articles
	$post_data["API"]="cms";
	$post_data["APIRequest"]="ArticleAdd";
	$post_data["title"]=$subject;
	$post_data["article"]=$msgtext;	
	$post_data["format"]=1;
	
	$postdata=http_build_query($post_data);
	
	$responseXml = post(PATH."soa2/", $postdata);
	$use_errors = libxml_use_internal_errors(true);
	try
	{
		$response = new SimpleXMLElement($responseXml);
	}
	catch(Exception $e)
	{
		//echo $e;
	}
	libxml_clear_errors();
	libxml_use_internal_errors($use_errors);
	$article_id=(int)$response->article_id[0];
	
	//save conversation in crm_conversations
	$post_data=array();
	$post_data["API"]="crm";
	$post_data["APIRequest"]="ConversationAdd";
	$post_data["user_id"]=$customer_id;	
	$post_data["order_id"]=$_POST["OrderID"];
	$post_data["article_id"]=$article_id;
	$post_data["type_id"]=1;
	$post_data["con_from"]=$sendermail;
	$post_data["con_to"]=$mail;
	
	$postdata=http_build_query($post_data);
	
	$responseXml = post(PATH."soa2/", $postdata);
	$use_errors = libxml_use_internal_errors(true);
	try
	{
		$response = new SimpleXMLElement($responseXml);
	}
	catch(Exception $e)
	{
		//echo $e;
	}
	libxml_clear_errors();
	libxml_use_internal_errors($use_errors);
	
	//save article label in cms_articles_labels
	$post_data=array();
	$post_data["API"]="cms";
	$post_data["APIRequest"]="ArticleLabelAdd";
	$post_data["article_id"]=$article_id;
	$post_data["label_id"]=21;
	
	$postdata=http_build_query($post_data);
	
	$responseXml=post(PATH."soa2/", $postdata);
	$use_errors=libxml_use_internal_errors(true);
	try
	{
		$response=new SimpleXMLElement($responseXml);
	}
	catch(Exception $e)
	{
	}
	libxml_clear_errors();
	libxml_use_internal_errors($use_errors);
	
//****************************mail versand und beitrag speichern ende********************************************

/*
	//Kopie an mwosgien	
	$post_data=array();
	$post_data["API"]="cms";
	$post_data["APIRequest"]="MailSend";
	$post_data["ToReceiver"]="mwosgien@mapco.de";	
	$post_data["FromSender"]=$sendermail;
	$post_data["Subject"]=$subject." *****KOPIE*****";
	$post_data["MsgText"]=$msgtext.'<br />'.$customer_id.'<br />'.$mail;
	
	$postdata=http_build_query($post_data);
	
	$responseXml = post(PATH."soa2/", $postdata);
	$use_errors = libxml_use_internal_errors(true);
	try
	{
		$response = new SimpleXMLElement($responseXml);
	}
	catch(Exception $e)
	{
		//echo $e;
	}
	libxml_clear_errors();
	libxml_use_internal_errors($use_errors);
*/
	
	//UPDATE FINMAIL IN SHOP ORDERS
//	$mailcount=$row_order["fz_fin_mail_count"];
//	$mailcount++;
/*
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
*/
?>
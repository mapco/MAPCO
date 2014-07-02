<?php

	include("../functions/shop_get_prices.php");
	include("../functions/shop_itemstatus.php");	
	include("../functions/mapco_gewerblich.php");
	include("../functions/cms_t.php");


	check_man_params(array("order_id"	=> "numeric"));
	
	//order data
	$res_order=q("SELECT * FROM shop_orders WHERE id_order=".$_POST["order_id"].";", $dbshop, __FILE__, __LINE__);
	$row_order=mysqli_fetch_array($res_order);
	
	$shop_id=$row_order["shop_id"];
	$status_id=$row_order["status_id"];
	$payments_type_id=$row_order["payments_type_id"];
	$bill_adr_id=$row_order["bill_adr_id"];
	$ship_adr_id=$row_order["ship_adr_id"];
	$customer_id=$row_order["customer_id"];
	$partner_id=$row_order["partner_id"];
	
	$gewerblich=gewerblich($customer_id);
	
	//shop data
	$res_shop=q("SELECT * FROM shop_shops WHERE id_shop=".$shop_id.";", $dbshop, __FILE__, __LINE__);
	$row_shop=mysqli_fetch_array($res_shop);
	$shop_mail=$row_shop["mail"];
	$customer_mail_article_id=$row_shop["customer_mail_article_id"];
	$withdrawal_article_id=$row_shop["withdrawal_article_id"];
	
	//user_data
	$usermail='';
	$res_user=q("SELECT * FROM cms_users WHERE id_user=".$customer_id.";", $dbweb, __FILE__, __LINE__);
	$row_user=mysqli_fetch_array($res_user);
	$usermail=$row_user["usermail"];
	if($usermail=='') $usermail=$row_order["usermail"];
	if($usermail=='')
	{
		show_error(9816, 7, __FILE__, __LINE__, "Keine Emailadresse gefunden. OrderID: ".$_POST["OrderID"]);
		exit;
	}

	
	//Daten für den Kunden
	
	//Bewertungslink für TrustedShops
	$text_trusted="";
	$text_trusted.='<p>';
	$text_trusted.=t("Waren Sie mit Ihrem Einkauf und unserem Service zufrieden").'?<br />';
	$text_trusted.='<a href="https://www.trustedshops.de/bewertung/bewerten_'.$row_shop["TrustedShops_id"].'.html&buyerEmail='.$usermail.'&shopOrderID='.$_POST["order_id"].'" target="_blank" title="'.t("Online-Shop bewerten").'!">';
	$text_trusted.=t("Hier können Sie").' www.'.$row_shop["domain"].' '.t("bewerten").'.<br />';
	$text_trusted.='</a>';
	$text_trusted.=t("Wir freuen uns auf Ihr Feedback").'.';
	$text_trusted.='</p>';
	
	//CHECK AUF PAYPAL-Zahlung && ZAHLSTATUS
	//$onlinePayment=false;
	$paymentState="";
	$text01='';
	if ($row_order["Payments_TransactionID"]!="")
	{
		//$onlinePayment=true;
		if($row["Payments_TransactionState"]=="Pending")
		{
			//$paypalState="Pending";
			$text01 .= ' '.t("Der Versand erfolgt nachdem PayPal uns den Erhalt Ihrer Zahlung bestätigt hat.").'';
		}
	}
	
	//addresses
	$res_bill_adr=q("SELECT * FROM shop_bill_adr WHERE adr_id=".$bill_adr_id.";", $dbshop, __FILE__, __LINE__);
	$row_bill_adr=mysqli_fetch_array($res_bill_adr);
	$bill_gender=t("Herr");
	if($row_bill_adr["gender"]==1) $bill_gender=t("Frau");
	if($ship_adr_id>0 and $ship_adr_id!=$bill_adr_id)
	{
		$res_ship_adr=q("SELECT *FROM shop_bill_adr WHERE adr_id=".$ship_adr_id.";", $dbshop, __FILE__, __LINE__);
		$row_ship_adr=mysqli_fetch_array($res_ship_adr);
		$ship_gender=t("Herr");
		if($row_ship_adr["gender"]==1) $ship_gender=t("Frau");
	}
	
	//country data
	$eu=1;
	$res=q("SELECT * FROM shop_countries WHERE id_country=".$row_bill_adr["country_id"], $dbshop, __FILE__, __LINE__);
	if(mysqli_num_rows>0)
	{
		$shop_countries=mysqli_fetch_assoc($res);
		$eu=$shop_countries["EU"];
	}
	
	$adr_title=t("Rechnungsanschrift");
	if($bill_adr_id==$ship_adr_id) $adr_title=t("Rechnungs- und Lieferanschrift");
	
	if( $shop_id == 17 )
	{
		$text02='<p><b>'.$adr_title.':</b> ';
		if($row_bill_adr["company"]!="") $text02.=$row_bill_adr["company"].', ';
		$text02.= $bill_gender.' '.$row_bill_adr["title"];
		$text02.=$row_bill_adr["firstname"].' '.$row_bill_adr["lastname"];
		$text02.=', '.$row_bill_adr["street"].' '.$row_bill_adr["number"];
		$text02.=', '.$row_bill_adr["zip"].' '.$row_bill_adr["city"];
		if($row_bill_adr["additional"]!="") $text02.=', '.$row_bill_adr["additional"];
		$text02.=', '.$row_bill_adr["country"].'<br>';
		$text02.='</p>';
		if($ship_adr_id>0 and $ship_adr_id!=$bill_adr_id)
		{
			$text02.='<p><b>'.t("Lieferanschrift").':</b> ';
			if($row_ship_adr["company"]!="") $text02.=$row_ship_adr["company"].', ';
			$text02.= $ship_gender.' '.$row_ship_adr["title"];
			$text02.=$row_ship_adr["firstname"].' '.$row_ship_adr["lastname"];
			$text02.=', '.$row_ship_adr["street"].' '.$row_ship_adr["number"];
			$text02.=', '.$row_ship_adr["zip"].' '.$row_ship_adr["city"];
			if($row_ship_adr["additional"]!="") $text02.=', '.$row_ship_adr["additional"];
			$text02.=', '.$row_ship_adr["country"].'<br>';
			$text02.='</p>';
		}
	}
	else
	{	
		$text02='<p><b>'.$adr_title.':</b><br>';
		if($row_bill_adr["company"]!="") $text02.=$row_bill_adr["company"].'<br>';
		$text02.=$bill_gender.' '.$row_bill_adr["title"].'<br>';
		$text02.=$row_bill_adr["firstname"].' '.$row_bill_adr["lastname"].'<br>';
		$text02.=$row_bill_adr["street"].' '.$row_bill_adr["number"].'<br>';
		$text02.=$row_bill_adr["zip"].' '.$row_bill_adr["city"].'<br>';
		if ($row_bill_adr["additional"]!="") $text02 .= $row_bill_adr["additional"].'<br>';
		$text02.=$row_bill_adr["country"].'<br>';
		$text02.='</p>';
		if($ship_adr_id>0 and $ship_adr_id!=$bill_adr_id)
		{
			$text02.='<p><b>'.t("Lieferanschrift").':</b><br>';
			if($row_ship_adr["company"]!="") $text02.=$row_ship_adr["company"].'<br>';
			$text02.=$ship_gender.' '.$row_ship_adr["title"].'<br>';
			$text02.=$row_ship_adr["firstname"].' '.$row_ship_adr["lastname"].'<br>';
			$text02.=$row_ship_adr["street"].' '.$row_ship_adr["number"].'<br>';
			$text02.=$row_ship_adr["zip"].' '.$row_ship_adr["city"].'<br>';
			if ($row_ship_adr["additional"]!="") $text02 .= $row_ship_adr["additional"].'<br>';
			$text02.=$row_ship_adr["country"].'<br>';
			$text02.='</p>';
		}
	}
	
	//bill Tabelle
	$text03='<table border="1" cellpadding="4">';
	$text03.='  <tr><th colspan="6">'.t("Bestellung").'</th></tr>';
	$text03.='  <tr>';
	$text03.='    <td>'.t("Artikel-Nr.").'</td>';
	$text03.='    <td>'.t("Menge").'</td>';
	$text03.='    <td>'.t("Bezeichnung").'</td>';
	$text03.='    <td>'.t("EK").'</td>';
	$text03.='    <td>'.t("Gesamt").'</td>';
	$text03.='    <td></td>';
	$text03.='  </tr>';
	
	$results2=q("SELECT a.price, a.netto, a.amount, a.collateral, c.title, b.MPN, b.id_item FROM shop_orders_items AS a, shop_items AS b, shop_items_de AS c WHERE a.order_id=".$row_order["id_order"]." AND a.item_id=b.id_item AND b.id_item=c.id_item;", $dbshop, __FILE__, __LINE__);
	$total=0;
	$totalpos=0;
	$collateral_count=0;
	$collateral_sum=0;
	while($row2=mysqli_fetch_array($results2))
	{
		$text03.='  <tr>';
		$text03.='  <td>'.$row2["MPN"].'</td>';
		$text03.='  <td>'.number_format($row2["amount"], 0).'</td>';
		$text03.='  <td>'.$row2["title"];
		if($row2["collateral"]>0) 
		{
			$text03.='<br />zzgl. '.$row2["collateral"].' € '.t("Altteilpfand").'';
			$collateral_count=$collateral_count+$row2["amount"];
			$collateral_sum=$collateral_sum+(number_format($row2["collateral"]*$row2["amount"], 2));
		}
		$text03.='  </td>';
		$text03.='  <td>'.number_format($row2["price"], 2).' €</td>';
		$price=$row2["amount"]*$row2["price"];
		$total+=$price;
		$totalpos+=($row2["amount"]*$row2["netto"]);
		$text03.='  <td>'.number_format($price, 2).' €</td>';
		if ($partner_id>0) 
		{
			$text03.='  <td><span style="float:right;">'.itemstatus_rc($row2["id_item"], 1, $row2["amount"]).'</span></td>';					
		}
		else
		{
			$text03.='  <td><span style="float:right;">'.itemstatus($row2["id_item"], 1, $row2["amount"]).'</span></td>';
		}
		$text03.='  </tr>';
	}
	
	//Altteilpfand
	if($collateral_count>0 and $collateral_sum>0)
	{
		$text03.='  <tr>';
		$text03.='    <td colspan="4">';
		$text03.=' '.t("Altteilpfand für").' '.$collateral_count.' '.t("Artikel").'';
		if ( $collateral_count==1 ) $text03.='<br />'.t("Dieser wird Ihnen nach Rücksendung des Alteils zurück erstattet.").'';
		else $text03.='<br />'.t("Dieser wird Ihnen nach Rücksendung der Alteile zurück erstattet.").'';
		$text03.='<br />'.t("Bitte achten Sie bei Altteilen darauf dass diese vollständig und nicht beschädigt sind.").'';
		$text03.='   <td colspan="2">'.number_format($collateral_sum, 2).' €</td>';
		$text03.='  </tr>';
		$total+=$collateral_sum;
	}

	//Versandkosten
	$text03.='  <tr>';
	$text03.='    <td colspan="4">'.$row_order["shipping_details"].'<br />'.$row_order["shipping_details_memo"].'</td>';
	$text03.='    <td colspan="2">'.number_format($row_order["shipping_costs"], 2).' €</td>';
	$text03.='  </tr>';
	$total+=$row_order["shipping_costs"];
			
	//Gesamt Netto
	if ($gewerblich or (!$gewerblich and $eu==0))
	{		
		//$discount = 0;
		$text03.='  <tr>';
		if(isset($row_bill_adr["country_id"]) and $row_bill_adr["country_id"]>1)
			$text03.='    <td colspan="4"><b>'.t("Gesamtpreis Netto").'</b></td><td colspan="2"><b>'.number_format($total, 2).' €</b></td>';
		else
			$text03.='    <td colspan="4">'.t("Gesamtpreis Netto").'</td><td colspan="2">'.number_format($total, 2).' €</td>';
		$text03.='  </tr>';
		$ust=(UST/100)*$total;
		$total=((100+UST)/100)*$total;
	}
	else 
	{
		//$discount = 0;
		$ust=$total/(100+UST)*UST;
	}

	if($shop_id!=17 and ((!$gewerblich and $eu==1) or !isset($row_bill_adr["country_id"]) or (isset($row_bill_adr["country_id"]) and $row_bill_adr["country_id"]==1)))
	{
		$text03.='  <tr>';
		$text03.='    <td colspan="4">';
		if ($gewerblich)
		{
			$text03.=''.t("zzgl.").' '.UST.'% '.t("gesetzliche").'';
		}
		else
		{
			$text03.=''.t("darin enthalten").', '.UST.'%';  
		}
		$text03.=' '.t("Umsatzsteuer").'</td><td colspan="2">'.number_format($ust, 2).' €</td>';
		$text03.='  </tr>';
	
		//Gesamt Brutto
		$text03.='  <tr>';
		$text03.='    <td colspan="4"><b>'.t("Gesamtpreis Brutto").'</b></td><td colspan="2"><b>'.number_format($total, 2).' €</b></td>';
		$text03.='  </tr>';
	}
	$text03.='</table>';
	
	//cancellation policy
	$res_articles=q("SELECT * FROM cms_articles WHERE id_article=".$withdrawal_article_id.";", $dbweb, __FILE__, __LINE__);
	$row_articles=mysqli_fetch_array($res_articles);
	$text04=$row_articles["article"];
	
	//get article-ids
//	if($customer_mail_article_id==0)		
//		$customer_mail_article_id=33418; // Bestellbestätigungs-Template
	$results=q("SELECT article FROM cms_articles WHERE id_article=".$customer_mail_article_id.";", $dbweb, __FILE__, __LINE__);
	$row=mysqli_fetch_array($results);
	$msgtext = $row["article"];
	
	$msgtext=str_replace("<!-- TRUSTEDSHOPS -->", $text_trusted, $msgtext);
	$msgtext=str_replace("<!-- PAYCHECK -->", $text01, $msgtext);
	$msgtext=str_replace("<!-- ORDER-ID -->", $_POST["order_id"], $msgtext);
	$msgtext=str_replace("<!-- ADDRESS -->", $text02, $msgtext);
	$msgtext=str_replace("<!-- TABLE -->", $text03, $msgtext);
	$msgtext=str_replace("<!-- CANCEL -->", $text04, $msgtext);
	
	//cutouts
	if($status_id==7 or ($payments_type_id>2 and $payments_type_id<7))
		$msgtext=cutout($msgtext, "<!-- PAYDATA-START -->", "<!-- PAYDATA-END -->");
		
	//subject
	$subject=t("Eingangsbestätigung zu Ihrer Bestellung Nr.").": ".$_POST["order_id"];
	
	//MailSend
	$post_data=array();
	$post_data["API"]="cms";
	$post_data["APIRequest"]="MailSend";
	$post_data["ToReceiver"]=$usermail;	
	$post_data["FromSender"]=$shop_mail;
	$post_data["Subject"]=$subject;
	$post_data["MsgText"]=$msgtext;
	
	$postdata=http_build_query($post_data);
	
	$response=soa2($postdata, __FILE__, __LINE__);
	
	
	//save email in cms_articles
	$post_data["API"]="cms";
	$post_data["APIRequest"]="ArticleAdd";
	$post_data["title"]=$subject;
	$post_data["article"]=$msgtext;	
	$post_data["format"]=1;
	
	$postdata=http_build_query($post_data);
	
	$response=soa2($postdata, __FILE__, __LINE__);	
	$article_id=(int)$response->article_id[0];
	
	
	//save conversation in crm_conversations
	$post_data=array();
	$post_data["API"]="crm";
	$post_data["APIRequest"]="ConversationAdd";
	$post_data["user_id"]=$customer_id;	
	$post_data["order_id"]=$_POST["order_id"];
	$post_data["article_id"]=$article_id;
	$post_data["type_id"]=1;
	$post_data["con_from"]=$shop_mail;
	$post_data["con_to"]=$usermail;
	
	$postdata=http_build_query($post_data);
	
	$response=soa2($postdata, __FILE__, __LINE__);

	
	//save article label in cms_articles_labels
	$post_data=array();
	$post_data["API"]="cms";
	$post_data["APIRequest"]="ArticleLabelAdd";
	$post_data["article_id"]=$article_id;
	$post_data["label_id"]=21;
	
	$postdata=http_build_query($post_data);
	
	$response=soa2($postdata, __FILE__, __LINE__);
		
	if($gewerblich and isset($row_bill_adr["country_id"]) and $row_bill_adr["country_id"]>1)
	{
		//kopie an mwosgien
		$post_data=array();
		$post_data["API"]="cms";
		$post_data["APIRequest"]="MailSend";
		$post_data["ToReceiver"]="mwosgien@mapco.de";	
		$post_data["FromSender"]=$shop_mail;
		$post_data["Subject"]=$subject.'*********Auslands-Gewerbekunde********************';
		$post_data["MsgText"]=$msgtext.$usermail.$article_id;
		
		$postdata=http_build_query($post_data);
		
		$response=soa2($postdata, __FILE__, __LINE__);
	}

?>
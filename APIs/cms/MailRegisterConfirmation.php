<?php

	//************************ 
	//*     SOA2-SERVICE     *
	//************************
	
	$required=array("username"	=>	"textNN",
					"usermail"	=>	"textNN");
	
	check_man_params($required);
	
	//GET ARTICLES
	//$article_id=36211;
	$res_shop_shops=q("SELECT * FROM shop_shops WHERE site_id=".$_SESSION["id_site"].";", $dbshop, __FILE__, __LINE__);
	$shop_shops=mysqli_fetch_assoc($res_shop_shops);
	
	$article_id=$shop_shops["customer_register_mail_article_id"];
	$sendermail=$shop_shops["mail"];
	
	$msgtext="";
	$subject="";
	
	if($_SESSION["origin"]!="DE" and $_SESSION["origin"]!="CH" and $_SESSION["origin"]!="AT")
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
	
	$msgtext=str_replace("<!-- USERNAME -->", $_POST["username"], $msgtext);
	$msgtext=str_replace("<!-- USERMAIL -->", $_POST["usermail"], $msgtext);
	
	
	//MailSend
	
	$post_data=array();
	$post_data["API"]="cms";
	$post_data["APIRequest"]="MailSend";
	$post_data["ToReceiver"]=$_POST["usermail"];	
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

/*	
	//Kopie an mwosgien	
	$post_data=array();
	$post_data["API"]="cms";
	$post_data["APIRequest"]="MailSend";
	$post_data["ToReceiver"]="mwosgien@mapco.de";	
	$post_data["FromSender"]=$sendermail;
	$post_data["Subject"]=$subject." *****KOPIE*****";
	$post_data["MsgText"]=$msgtext.'<br />'.$_POST["usermail"];
	
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
?>
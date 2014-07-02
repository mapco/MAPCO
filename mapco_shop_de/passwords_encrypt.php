<?php
	
	include("config.php");
	include("templates/".TEMPLATE."/header.php");
	//include("functions/cms_createPassword.php");
	include("modules/php-barcode/php-barcode.php");
	//phpinfo();
	//exit;
	//echo __FILE__;
	//print_r($_SERVER);
	//exit;



/*	
	// SERVICE-TESTER
	$post_data = 					array();
	
	$post_data['API'] = 			'crm';
//	$post_data['API'] = 			'shop';
	
//	$post_data['APIRequest'] = 		'MailOrderConfirmation';
	$post_data['APIRequest'] = 		'MailOrderSent';
//	$post_data['APIRequest'] = 		'MailOrderSeller';
	
//	$post_data['OrderID'] = 		1803886;
//	$post_data['order_id'] = 		1806388;
	$post_data['mode'] = 			'other';
	$post_data['shipping_number'] = '00340433836626225586';
	$post_data['ToReceiver'] =		'mwosgien@mapco.de';
	$post_data['company'] = 		'Firma 1';
	$post_data['company2'] = 		'-';
	$post_data['firstname'] = 		'Hans';
	$post_data['lastname'] = 		'-';
	$post_data['origin'] = 			'DE';
	
	$postdata = http_build_query( $post_data );
	$response = soa2( $postdata, __FILE__, __LINE__ );
*/

	
	
	//echo system('mysqldump.exe --defaults-extra-file="/usr/www/users/admapco/mapco_shop_de/temp/000000tmp111111.cnf"  --set-gtid-purged=OFF --user=mapcoshop --max_allowed_packet=1G --host=dedi473.your-server.de --port=3306 --default-character-set=utf8 "admapco_mapcoshop" "shop_orders"');
	//echo 'fertig';
/*	
	//Barcode-Creation***************************************************************************
	$im     = imagecreatetruecolor(300, 50) or die('Cannot Initialize new GD image stream');  
	$black  = ImageColorAllocate($im,0x00,0x00,0x00);  
	$white  = ImageColorAllocate($im,0xff,0xff,0xff);  
	imagefilledrectangle($im, 0, 0, 300, 50, $white);  
	$data = Barcode::gd($im, $black, 150, 25, 0, "ean13", "4043605841919", 3, 50);
	imagepng($im, "images/barcodes/test.png");
	imagedestroy($im);
	echo '<img src="'.PATH.'images/barcodes/test.png">';
	echo print_r($data);
	
1797232*/
/*
	//Testemail Italien
	$post_data=array();
	$post_data["API"]="shop";
	$post_data["APIRequest"]="MailOrderSeller2";
	$post_data["order_id"]=1797232;	
	
	$postdata=http_build_query($post_data);
	
	$response=soa2($postdata, __FILE__, __LINE__);
*/
/*
	$item_id=10003;

	$post_data=array();
	$post_data["API"]="shop";
	$post_data["APIRequest"]="BarcodeCreate";
	$post_data["item_id"]=$item_id;
	$response=soa2($post_data, __FILE__, __LINE__);
	//echo $response;
	echo '<img src="'.PATH.'images/barcodes/'.$item_id.'.png">';
	echo '<br />'.(string)$response->ean[0];
	echo '<br /><input type="text">';
	//******************************************************************************************
*/
	
/*
	echo 'erzeuge salts.....<br />';
	
	$results=q("SELECT * FROM cms_users WHERE id_user>69750 AND id_user<69771;", $dbweb, __FILE__, __LINE__);
	while($row=mysqli_fetch_array($results))
	{
		$salt=createPassword(32);
		$results2=q("UPDATE cms_users SET user_salt='".$salt."' WHERE id_user=".$row["id_user"].";", $dbweb, __FILE__, __LINE__);
	}
	
	echo 'Fertig!<br />';
	
	echo 'verschluessele Passworte.....<br />';
	
	$results=q("SELECT * FROM cms_users WHERE id_user>69750 AND id_user<69771;", $dbweb, __FILE__, __LINE__);
	while($row=mysqli_fetch_array($results))
	{
		$pw=md5($row["password_old"]);
		$pw=md5($pw.$row["user_salt"]);
		$pw=md5($pw.PEPPER);
		$results2=q("UPDATE cms_users SET password='".$pw."' WHERE id_user=".$row["id_user"].";", $dbweb, __FILE__, __LINE__);
	}
*/	
	//echo 'Fertig!';
	//echo createPassword(49);
	//include("templates/".TEMPLATE."/footer.php");
/*
	//User-Passwort verschlüsseln
	$results=q("SELECT * FROM cms_users WHERE id_user=83234;", $dbweb, __FILE__, __LINE__);
	while($row=mysqli_fetch_array($results))
	{
		$pw=md5("pmcr96");
		$pw=md5($pw.$row["user_salt"]);
		$pw=md5($pw.PEPPER);
		echo $pw;
	}
*/	
	//$results=q("UPDATE cms_users SET user_token WHERE", $dbweb, __FILE__, __LINE__);

/*	
	//MailSend - Test
	echo 'Test-Start'."\n";
	$subject="Mail-Send Test mit mehreren Anhängen";
	
	$msgtext='<html>';
	$msgtext.='<head><title>MailSend.php Test</title></head>';
	$msgtext.='<body>';
	$msgtext.='<p>Dies ist eine E-mail mit mehreren Anhängen</p>';
	$msgtext.='</body>';
	$msgtext.='</html>';
	
	$ifile[]='../files/1000/1000123.jpg';
	//$ifile='backend_shop_statistics.php';
	$ifilename[]='testbild.jpg';
	$ifile[]='../files/1000/1000124.jpg';
	$ifilename[]='testbild2.jpg';
	$ifile[]='../files/1142/1142218.pdf';
	$ifilename[]='test.pdf';
	
	echo $subject."\n";
	echo $msgtext."\n";
	echo print_r($ifile)."\n";
	echo print_r($ifilename)."\n";
	
	$post_data["API"]="cms";
	$post_data["APIRequest"]="MailSend";
	$post_data["ToReceiver"]="mwosgien@mapco.de";
	$post_data["FromSender"]="ww@ww.ww";
	$post_data["Subject"]=$subject;
	$post_data["MsgText"]=$msgtext;
	$post_data["IFile"]=$ifile;
	$post_data["IFileName"]=$ifilename;
	
	$postdata=http_build_query($post_data);
	
	$responseXml = post(PATH."soa2/",$postdata);
	$use_errors = libxml_use_internal_errors(true);
	try
	{
		$response = new SimpleXMLElement($responseXml);
	}
	catch(Exception $e)
	{
		echo $e;
	}
	libxml_clear_errors();
	libxml_use_internal_errors($use_errors);
	//$stock=$response->Stock[0];
	$mail_status=$response->mail_status[0];
	echo $mail_status;
	//echo $response->mail_msg[0];
*/

	//MailOrderConfirmation Test
/*	
	$post_data["API"]="shop";
	$post_data["APIRequest"]="MailOrderConfirmation";
	$post_data["order_id"]=1776216;
	
	$postdata=http_build_query($post_data);
	
	$responseXml = post(PATH."soa2/",$postdata);
	$use_errors = libxml_use_internal_errors(true);
	try
	{
		$response = new SimpleXMLElement($responseXml);
	}
	catch(Exception $e)
	{
		echo $e;
	}
	libxml_clear_errors();
	libxml_use_internal_errors($use_errors);
	//$stock=$response->Stock[0];
*/
/*
	//ConversationAdd Test
	$post_data=array();
	$post_data["API"]="crm";
	$post_data["APIRequest"]="ConversationAdd";
	$post_data["user_id"]=12345;	
	$post_data["order_id"]=23456;
	$post_data["article_id"]=33333;
	$post_data["type_id"]=1;
	$post_data["con_from"]="martin@wosgien.de";
//	$post_data["To"]=$usermail;
	
	$postdata=http_build_query($post_data);
	
	echo $responseXml = post(PATH."soa2/", $postdata);
	$use_errors = libxml_use_internal_errors(true);
	try
	{
		$response = new SimpleXMLElement($responseXml);
	}
	catch(Exception $e)
	{
		echo $e;
	}
	libxml_clear_errors();
	libxml_use_internal_errors($use_errors);
*/
/*	
	//MailRegisterConfirmation Test
	$post_data=array();
	$post_data["API"]="cms";
	$post_data["APIRequest"]="MailRegisterConfirmation";	
	$post_data["username"]="mwosgien";
	$post_data["usermail"]="mwosgien@mapco.de";
	
	$postdata=http_build_query($post_data);
	
	$response=soa2($postdata);
*/	
/*	
	echo $responseXml = post(PATH."soa2/", $postdata);
	$use_errors = libxml_use_internal_errors(true);
	try
	{
		$response = new SimpleXMLElement($responseXml);
	}
	catch(Exception $e)
	{
		echo $e;
	}
	libxml_clear_errors();
	libxml_use_internal_errors($use_errors);
*/		
/*
	//OrderNetPriceCorrection Test
	$post_data=array();
	$post_data["API"]="shop";
	$post_data["APIRequest"]="OrderNetPriceCorrection";	
	$post_data["orderid"]=1743137;
	
	$postdata=http_build_query($post_data);
	
	$res=soa2($postdata, __FILE__, __LINE__);
*/
/*
	//auf-id_date aus shop-orders_events nachtragen
	$results=q("SELECT * FROM shop_orders_events WHERE eventtype_id=11 ORDER BY id_event;", $dbshop, __FILE__, __LINE__);
	while($shop_orders_events=mysqli_fetch_array($results))
	{
		$results2=q("UPDATE shop_orders SET auf_id_date=".$shop_orders_events["firstmod"]." WHERE id_order=".$shop_orders_events["order_id"].";", $dbshop, __FILE__, __LINE__);
		$results3=q("UPDATE shop_orders SET auf_id_date=".$shop_orders_events["firstmod"]." WHERE combined_with=".$shop_orders_events["order_id"].";", $dbshop, __FILE__, __LINE__);
		
	}
	echo 'fertig';
*/
/*	
	// crm_customer import
	$res = q( "SELECT * FROM kunde LIMIT 100", $dbshop, __FILE__, __LINE__ );
	while ( $kunde = mysqli_fetch_assoc( $res ) )
	{

		$res2 = q( "SELECT * FROM crm_customers WHERE company='" . $kunde['ANSCHR_1'] . ' ' . $kunde['ANSCHR_2'] . "'" , $dbweb, __FILE__, __LINE__ );
		if ( mysqli_num_rows( $res2 ) == 0 )
		{
			echo $kunde['ANSCHR_1'] . ' ' . $kunde['ANSCHR_2'] . '<br />';
		}
		
	}
*/	
?>
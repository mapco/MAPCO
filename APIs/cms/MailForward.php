<?php 
	mb_internal_encoding("UTF-8");
		
	check_man_params(array("account" => "numericNN", "msg_num" => "numericNN", "target" => "text"));

	require_once("../../mapco_shop_de/functions/mail_connect.php");						

	if ( $_POST['target'] != '' )
	{
		$mbox = mail_connect($_POST['account']);
	/*	getmsg($mbox,$_POST['msg_num'],'full');

		if ( $plainmsg != '' )
		{
			$body = $plainmsg;
		}
		else
		{
			$body = $htmlmsg;
		}
		*/
	//	$mail_struct = imap_fetchstructure($mbox, $_POST['msg_num'], FT_UID);
	//	$hText = imap_fetchbody($mbox, $_POST['msg_num'], '0', FT_UID); 
	//	$header1 = imap_rfc822_parse_headers($hText); 
		
		$header2 = imap_fetchheader($mbox, $_POST['msg_num'], FT_UID);
		$body = imap_body($mbox,$_POST['msg_num'], FT_UID | FT_PEEK);  // simple
		//$body= iconv_mime_decode($body, 0, "utf-8");
		//$body = $body;
	//	var_dump($header['subject']);die();
		imap_close($mbox);
		
	/*	if ($p->encoding==4)
			$data = quoted_printable_decode($data);
		elseif ($p->encoding==3)
			$data = base64_decode($data);
			
	*/		
		
		if ( strpos($_POST['target'],",") != FALSE )
		{
			$where = 'id_account IN ('.$_POST['target'].')';
		}
		else
		{
			$where = 'id_account ='.$_POST['target'];
		}
		
		$res = q("SELECT id_account, user FROM cms_mail_accounts WHERE ".$where.";", $dbweb, __FILE__, __LINE__);
		while ( $row = mysqli_fetch_assoc($res) )
		{
			$targets[] = $row['user'];
			//mail($row['user'], "FWD: ", $body, $header);
		}

		$to = implode(",",$targets);
		$subject = "FWD: ";
		$cc = null;
		$bcc = null;
		$return_path = "segerland@mapco.de";
		
		imap_mail($to, $subject, $body, $header2, $cc, $bcc, $return_path);

	//var_dump($body);die();
//		imap_mail( $empfaenger, "FWD: ".iconv_mime_decode($header->subject, 0, "utf-8"), $body, $header);
		//imap_mail( "segerland@mapco.de", "FWD: ".iconv_mime_decode($header->subject, 0, "utf-8"), $body, $header);
		
		//archiviere originalmail
		$mail_moved = move_mail_to_archiv($mbox, $_POST['msg_num']);
	}
?>
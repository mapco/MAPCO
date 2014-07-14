<?php 
	mb_internal_encoding("UTF-8");
		
	check_man_params(array("account" => "numericNN", "folder" => "numericNN", "msg_num" => "numericNN", "target" => "numericNN"));

	require_once("../../mapco_shop_de/functions/mail_connect.php");						

	if ( $_POST['target'] != '' )
	{
		$mbox = mail_connect($_POST['account'], $_POST['folder']);
		
		$header2 = imap_fetchheader($mbox, $_POST['msg_num'], FT_UID);
		$head = imap_rfc822_parse_headers($header2);
		$from = $head->from[0]->mailbox.'@'.$head->from[0]->host;
		
		if ( strpos($_POST['target'],",") != FALSE )
		{
			$where = 'id_account IN ('.$_POST['target'].')';
		}
		else
		{
			$where = 'id_account ='.$_POST['target'];
		}
		
		$targets = array();
		$res = q("SELECT id_account, user FROM cms_mail_accounts WHERE ".$where.";", $dbweb, __FILE__, __LINE__);
		while ( $row = mysqli_fetch_assoc($res) )
		{
			$targets[] = $row['user'];
			//mail($row['user'], "FWD: ", $body, $header);
		}

		$to = implode(",",$targets); $to = 'segerland@mapco.de';
//		$subject = "FWD: ";
		$cc = null;
		$bcc = null;
		
		$res = q("SELECT user FROM cms_mail_accounts WHERE id_account=".$_POST['account']." LIMIT 1;", $dbweb, __FILE__, __LINE__);
		$row = mysqli_fetch_assoc($res);
		$return_path = $row['user'];
		
		$boundary = "173361623-1804289383-1404199736=:";
		for ($i=0;$i<5;$i++) {
		  $n = rand(0,9);
		  $boundary .= $n;
		}
		
		$header2 = 'From: '.$row['user'].'
To: '.$to.'
cc: '.$cc.'
MIME-Version: 1.0
Content-Type: MULTIPART/mixed; BOUNDARY="'.$boundary.'"';
		
		$org_msg = 'Gesendet: '.$head->date.'
Von: '.$head->fromaddress.'
An: '.$head->toaddress.'
Betreff: '.$head->subject;

		getmsg($mbox,$_POST['msg_num'],1);

		if ( $htmlmsg != '' )
		{
			$body = $htmlmsg;
		}
		else
		{
			$body = $plainmsg;
		}
		$body = nl2br($org_msg.$boundary.$body);
		
		imap_mail($to, 'Fw '.$head->subject, $body, $header2, $cc, $bcc, $return_path);

	//var_dump($body);die();
//		imap_mail( $empfaenger, "FWD: ".iconv_mime_decode($header->subject, 0, "utf-8"), $body, $header);
		//imap_mail( "segerland@mapco.de", "FWD: ".iconv_mime_decode($header->subject, 0, "utf-8"), $body, $header);
		
		//archiviere originalmail
		//$mail_moved = move_mail_to_archiv($mbox, $_POST['msg_num'], $_POST['account_id'], $_POST['folder']);
		imap_close($mbox);
	}
?>
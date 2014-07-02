<?php

	//************************ 
	//*     SOA2-SERVICE     *
	//************************
	mb_internal_encoding('UTF-8');
	require_once("../../mapco_shop_de/functions/mail_connect.php");
	
	$required=array("page_act"		=> "numericNN",
					"mails_p_page"	=> "numericNN",
					"account"	=> "numeric"
					);
	
	check_man_params($required);

	$xml='';

	//$mbox = mail_connect($_POST['account']);
	
	$mbox = mail_connect($_POST['account']);

	$arr_folders = imap_listmailbox($mbox, $row['server'], "*");
	foreach ( $arr_folders as $folder)
	{
		$foldername = str_replace($row['server'],"",$folder);
		$folders[$foldername] = $folder;
	}
//		unset($folders['Trash']);
	var_dump($folders);  die();

	$mc = imap_check($mbox);
	$msg_numbers = $mc->Nmsgs;

	$xml.='<num_of_msgs><![CDATA['.$msg_numbers.']]></num_of_msgs>'."\n";
	
	$msg_p_page=$_POST["mails_p_page"];
	$page_act=$_POST["page_act"];

	$mbox_sort=imap_sort($mbox, SORTARRIVAL, 0);
	
	$start = ($page_act-1)*$msg_p_page;
	if ( $start < 1 )
	{
		$start = 1;
	}
	$end = $start + $msg_p_page;
	if ( $end > $msg_numbers )
	{
		$end = $msg_numbers;
	}

	$result = imap_fetch_overview($mbox,$start.':'.$end,0);
//var_dump($result);die();	
/*	foreach ($result as $key => $row) 
	{
		$row=(array)$row;
		$msg_nr[$key]    = $row['msgno'];
	//	$msg_nr[$key]    = $row['subject'];
	}
	array_multisort($msg_nr, SORT_DESC, $result);*/
	foreach ($result as $overview) 
	{
		//$msg_uid = imap_uid($mbox, $overview->msgno);
		$msg_uid = $overview->uid;
		$from=iconv_mime_decode($overview->from, 0, "utf-8");
		$subject=iconv_mime_decode($overview->subject, 0, "utf-8");

		if ( $msg_uid != '' );
		{
			//schreibe mail-uid in history-table falls noch nicht vorhanden
			$res_history = q("SELECT firstmod FROM cms_mail_history WHERE account_id=".$_POST['account']." AND msg_uid=".$msg_uid.";", $dbweb, __FILE__, __LINE__);
			if ( mysqli_num_rows($res_history) == 0 )
			{
				$insert_data = array();
				// erstelle artikel mit dem Text der Notiz
				$insert_data['account_id'] = $_POST['account'];
				$insert_data['msg_uid'] = $msg_uid;
				$insert_data["subject"] = $subject;
				$insert_data["firstmod"] = time();
				$insert_data["firstmod_user"] = $_SESSION['id_user'];
				$insert_data["lastmod"] = $insert_data["firstmod"];
				$insert_data["lastmod_user"] = $_SESSION['id_user'];
				q_insert('cms_mail_history', $insert_data, $dbweb, __FILE__, __LINE__);
			}
		}
		
		$show=true;
		$move=false;

		//Regel für Mail Filter
		if( strpos($subject, "Herzlichen Glückwunsch, Ihr Artikel") !== false ) $move=true;
		if( strpos($subject, "Benachrichtigung über Zahlungseingang") !== false ) $move=true;
		if( strpos($subject, "PayPal-Zahlung erhalten von") !== false ) $move=true;
		if( strpos($subject, "Ich werde die Bezahlung in Höhe von") !== false ) $move=true;
		if( strpos($subject, "MC018") !== false ) $move=true;
		if( strpos($subject, "Bitte teilen Sie mir den Gesamtbetrag für den eBay-Artikel mit") !== false ) $move=true;
		if( strpos($subject, "PayPal-Zahlung storniert") !== false ) $move=true;
		if( strpos($subject, "PayPal-Zahlung noch nicht abgeschlossen") !== false ) $move=true;
		if( strpos($from, "<ebay@ebay.de>") !== false and strpos($subject, "Preisvorschlag:") !== false ) $move=true;
		if( strpos($from, "<member@paypal.de>") !== false and strpos($subject, "PayPal-Zahlung für mehrere Artikel erhalten") !== false ) $move=true;
		if( strpos($from, "<member@paypal.de>") !== false and strpos($subject, "Benachrichtigung über erhaltene Zahlung") !== false ) $move=true;
		
		if($move)
		{
			$folder_exist = 0;
			$list = imap_list($mbox, "{mail.your-server.de:993/imap/ssl}", "*");
		
			foreach ( $list as $mailbox )
			{
				if ( substr($mailbox, strrpos($mailbox, '.')+1) == "Archiv" )
				{
					$folder_exist = 1;
					break;
				}
			}
				
			//mail ins archiv verschieben
			$mailbox = "INBOX.Archiv";
			if ( $folder_exist == 0 )
			{
				$create_folder = imap_createmailbox($mbox, imap_utf7_encode("{mail.your-server.de:993/imap/ssl}".$mailbox));
			}
			$mail_moved = imap_mail_move($mbox,$overview->uid,$mailbox, CP_UID);
			imap_expunge($mbox);
			$show=false;
		}
		
		if( $show )
		{
			$xml.='<message>'."\n";
			$xml.='   <msgno><![CDATA['.$msg_uid.']]></msgno>'."\n";
			$xml.='   <msg_date><![CDATA['.strtotime($overview->date).']]></msg_date>'."\n";
			$xml.='   <msg_from><![CDATA['.mb_decode_mimeheader($overview->from).']]></msg_from>'."\n";
			$xml.='   <msg_subject><![CDATA['.$subject.']]></msg_subject>'."\n";
			$xml.='   <msg_deleted><![CDATA['.$overview->deleted.']]></msg_deleted>'."\n";
			$xml.='   <msg_seen><![CDATA['.$overview->seen.']]></msg_seen>'."\n";
			$xml.='</message>'."\n";
			$i++;
		}

	}
	
	imap_close($mbox);
	
	echo $xml;
?> 
<?php

	//************************ 
	//*     SOA2-SERVICE     *
	//************************
	
	$required=array("page_act"		=> "numericNN",
					"mails_p_page"	=> "numericNN");
	
	check_man_params($required);

	$xml='';
		
	//$mbox = imap_open("{dedi473.your-server.de:110/pop3}", "nputzing@mapco.de", "np75w6t2")
	//$mbox = imap_open("{mail.your-server.de:995/pop3/ssl}INBOX", "ebay@mapco.de", "kljo55")
	$mbox = imap_open("{mail.your-server.de:993/imap/ssl}INBOX", "ebay@mapco.de", "kljo55")
	//$mbox = imap_open("{dedi473.your-server.de:110/pop3}INBOX", "mwosgien@mapco.de", "mw544uzh5")
	//$mbox = imap_open("{dedi473.your-server.de:143/imap}INBOX", "mwosgien@mapco.de", "mw544uzh5")
     or die("can't connect: " . imap_last_error());
	
	$mc = imap_check($mbox);
	$xml.='<num_of_msgs><![CDATA['.$mc->Nmsgs.']]></num_of_msgs>'."\n";
	
	//$result = imap_fetch_overview($mbox,"1:{$mc->Nmsgs}",0);
	$msg_numbers="";
	$msg_p_page=$_POST["mails_p_page"];
	$page_act=$_POST["page_act"];
	
	$mbox_sort=imap_sort($mbox, SORTARRIVAL, 1);
	
	for($i=(($page_act-1)*$msg_p_page);$i<(($page_act*$msg_p_page));$i++)
	{
		if($i<$mc->Nmsgs)
			$msg_numbers.=",".$mbox_sort[$i];
	}
	$msg_numbers=substr($msg_numbers,1);
	$result = imap_fetch_overview($mbox,$msg_numbers,0);
	//$result = imap_fetch_overview($mbox,"1:200",0);
	//echo print_r($mbox)."\n";
	
	mb_internal_encoding('UTF-8');
	//echo print_r($result);
	
	foreach ($result as $key => $row) 
	{
		$row=(array)$row;
		$msg_nr[$key]    = $row['msgno'];
	//	$msg_nr[$key]    = $row['subject'];
	}
	array_multisort($msg_nr, SORT_DESC, $result);
	
	foreach ($result as $overview) 
	{
		//$subject=$overview->subject;
		//$subject=imap_utf8($subject);
		//echo mb_detect_encoding($subject);
		//if(mb_detect_encoding($subject, 'ASCII', true))
			//$subject=mb_decode_mimeheader($subject);
		$xml.='<message>'."\n";
		$xml.='   <msgno><![CDATA['.$overview->msgno.']]></msgno>'."\n";
		$xml.='   <msg_date><![CDATA['.strtotime($overview->date).']]></msg_date>'."\n";
		//$xml.='   <msg_from><![CDATA['.$overview->from.']]></msg_from>'."\n";
		$xml.='   <msg_from><![CDATA['.mb_decode_mimeheader($overview->from).']]></msg_from>'."\n";
		//$xml.='   <msg_subject><![CDATA['.utf8_decode(mb_decode_mimeheader($overview->subject)).']]></msg_subject>'."\n";
		$xml.='   <msg_subject><![CDATA['.imap_utf8($overview->subject).']]></msg_subject>'."\n";
		$xml.='   <msg_deleted><![CDATA['.$overview->deleted.']]></msg_deleted>'."\n";
		$xml.='</message>'."\n";
//		echo "#{$overview->msgno} ({$overview->date}) - From: {$overview->from}
//		{$overview->subject}\n";
	}
	
	imap_close($mbox);
	
	echo $xml;
	
/*	
	//echo print_r($MC);
	$mbox = imap_open("{dedi473.your-server.de:110/pop3}INBOX", "mwosgien@mapco.de", "mw544uzh5")
      or die("can't connect: " . imap_last_error());

	$list = imap_getmailboxes($mbox, "{dedi473.your-server.de:110/pop3}", "*");
	if (is_array($list)) {
		foreach ($list as $key => $val) {
			echo "($key) ";
			echo imap_utf7_decode($val->name) . ",";
			echo "'" . $val->delimiter . "',";
			echo $val->attributes . "<br />\n";
		}
	} else {
		echo "imap_getmailboxes failed: " . imap_last_error() . "\n";
	}

	imap_close($mbox);
*/	
/*	
	$mbox = imap_open("{dedi473.your-server.de:110/pop3}", "mwosgien@mapco.de", "mw544uzh5")
      or die("can't connect: " . imap_last_error());
	  
	//imap_createmailbox($mbox, imap_utf7_encode("{dedi473.your-server.de:110/pop3}INBOX.ablage"));
	
	$list = imap_list($mbox, "{dedi473.your-server.de:110/pop3}", "*");
	if (is_array($list)) {
		foreach ($list as $val) {
			echo imap_utf7_decode($val) . "\n";
		}
	} else {
		echo "imap_list failed: " . imap_last_error() . "\n";
	}
	
	imap_close($mbox);
*/	
?> 
<?php

	require_once('des_encryption.php');

	class imap_mail_system
	{
		public $mbox;
		public $mc;
		
		public $charset;
		public $htmlmsg;
		public $plainmsg;
		public $bytes;
		public $attachments;
		public $header;
		public $options;
		
		function __construct($account_id)
		{			
			$res = q("SELECT server, user, password FROM cms_mail_accounts WHERE id_account=".$account_id.";", $GLOBALS['dbweb'], __FILE__, __LINE__);
			$row = mysqli_fetch_assoc($res);
	
			$data = hexToString($row['password']);
			$pass = des($key, $data, 0, 0, null, null);
			$this->mbox = imap_open($row['server'], $row['user'], $pass) or die("can't connect: " . imap_last_error());	
		}
		
		public function check_mbox()
		{
			$this->mc = imap_check($this->mbox);
		}	

		public function get_overview($sort_mode, $sort_direct, $page_act, $msg_p_page)	
		{	
			$mbox_sort=imap_sort($this->mbox, $sort_mode, $sort_direct);
			for($i=(($page_act-1)*$msg_p_page);$i<(($page_act*$msg_p_page));$i++)
			{
				if($i<$this->mc->Nmsgs)
					$msg_numbers.=",".$mbox_sort[$i];
			}
			$msg_numbers=substr($msg_numbers,1);
			return $result = imap_fetch_overview($this->mbox,$msg_numbers,0);
		}
		
		public function move_mail($msg_uid)
		{
			//mail ins archiv verschieben
			$mailbox = "INBOX.Archiv";
			if ( $folder_exist == 0 )
			{
				$create_folder = imap_createmailbox($this->mbox, imap_utf7_encode("{mail.your-server.de:993/imap/ssl}".$mailbox));
			}
			$mail_moved = imap_mail_move($mbox->mbox,$msg_uid,$mailbox, CP_UID);
			imap_expunge($this->mbox);	
		}
		
		public function get_msg($msg_num, $mode)
		{
			if ( $mode == 1 )
			{
				$options = FT_UID | FT_PEEK;
			}
			else
			{
				$options = FT_UID;
			}
			
			$mail_struct = imap_fetchstructure($this->mbox,$msg_num, $options);
			//var_dump($mail_struct); die();
			if ($mail_struct->parts)
			{
				$body = '';
				$plain = '';
				$html = '';
								
				foreach ($mail_struct->parts as $partno0 => $p)
				{ 
					if ($p->parts)
					{
						foreach ($p->parts as $subpartno0 => $subp)
						{
							if ( $subp->type == 0 )
							{
								$partNbr = $partno0+1 .".".$subpartno0;
								$data = imap_fetchbody($this->mbox,$msg_num,$partNbr, $options);
								var_dump($partNbr);
								//var_dump($subp);
								//var_dump(base64_decode($data));
								
								if ($subp->encoding==4)
								{
									$body .= quoted_printable_decode($data);
								}
								elseif ($subp->encoding==3)
								{
									$body .= base64_decode($data);
								}
							}
						}
					}
					else
					{
						if ( $subp->type == 0 )
						{
							$data = imap_fetchbody($this->mbox,$msg_num,$partno+2, $options);
							
							if ($p->encoding==4)
							{
								$data = quoted_printable_decode($data);
							}
							elseif ($p->encoding==3)
							{
								$data = base64_decode($data);
							}
							
							if ( $p->subtype == 'PLAIN' )
							{
								$plain .= $data;	
							}
							elseif ( $p->subtype == 'HTML' )
							{
								$html .= $data;
							}
	
							if ( $html != '' )
							{
								/*$body = preg_replace('<html\b[^>]*>', ' ', $html);
								$body = preg_replace('</html>', ' ', $html);
								$body = preg_replace('<head\b[^>]*>(.*?)</head>', ' ', $html);
								$body = preg_replace('<body\b[^>]*>', ' ', $html);
								$body = preg_replace('</body>', ' ', $html);*/
								$body = $html;
							}
							else
							{
								//$body = preg_replace(' > ', '\n', $plain);
								$body = $plain;
							}
						}
					}
					var_dump($body);
				}die();
			}
			else
			{
				$data = imap_body($this->mbox,$msg_num, $options);
				if ($mail_struct->encoding==4)
				{
					$data = quoted_printable_decode($data);
				}
				elseif ($mail_struct->encoding==3)
				{
					$data = base64_decode($data);
				}
				$body .= $data;	
			}
			return $body;
		}
	}
	/*
	function mail_connect($account_id)
	{ 
		global $dbweb;
		
		$res = q("SELECT server, user, password FROM cms_mail_accounts WHERE id_account=".$account_id.";", $dbweb, __FILE__, __LINE__);
		$row = mysqli_fetch_assoc($res);

		$data = hexToString($row['password']);
		$pass = des($key, $data, 0, 0, null, null);
		$mbox = imap_open($row['server'], $row['user'], $pass) or die("can't connect: " . imap_last_error());

		return $mbox;
	}
	
	function getmsg($mbox,$mid, $mode) 
	{		
		// input $mbox = IMAP stream, $mid = message id
		// output all the following:
		global $charset,$htmlmsg,$plainmsg,$attachments, $header, $options;
		$htmlmsg = $plainmsg = $charset = '';
		$attachments = array();
	
		if ( $mode == 'light' )
		{
			$options = FT_UID | FT_PEEK;
		}
		else
		{
			$options = FT_UID;
		}
	
		// HEADER
		//$h = imap_header($mbox,$mid);
		$h = imap_fetchheader($mbox, $_POST['msg_num'], FT_UID);
		$header = $h;
		// add code here to get date, from, to, cc, subject...
	
		// BODY
		$s = imap_fetchstructure($mbox,$mid, FT_UID); var_dump($s);
		if (!$s->parts)  // simple
			getpart($mbox,$mid,$s,0);  // pass 0 as part-number
		else {  // multipart: cycle through each part
			foreach ($s->parts as $partno0=>$p)
				getpart($mbox,$mid,$p,$partno0+1);
		}
	}

	function getpart($mbox,$mid,$p,$partno) 
	{
		// $partno = '1', '2', '2.1', '2.1.3', etc for multipart, 0 if simple
		global $htmlmsg,$plainmsg,$charset,$attachments, $options;
	
		// DECODE DATA
		$data = ($partno)?
			imap_fetchbody($mbox,$mid,$partno, $options):  // multipart
			imap_body($mbox,$mid, $options);  // simple
		// Any part may be encoded, even plain text messages, so check everything.
		if ($p->encoding==4)
			$data = quoted_printable_decode($data);
		elseif ($p->encoding==3)
			$data = base64_decode($data);

		// PARAMETERS
		// get all parameters, like charset, filenames of attachments, etc.
		$params = array();
		if ($p->parameters)
			foreach ($p->parameters as $x)
				$params[strtolower($x->attribute)] = $x->value;
		if ($p->dparameters)
			foreach ($p->dparameters as $x)
				$params[strtolower($x->attribute)] = $x->value;
	
		// ATTACHMENT
		// Any part with a filename is an attachment,
		// so an attached text file (type 0) is not mistaken as the message.
		if ($params['filename'] || $params['name']) 
		{
			// filename may be given as 'Filename' or 'Name' or both
			$filename = ($params['filename'])? $params['filename'] : $params['name'];
			// filename may be encoded, so see imap_mime_header_decode()
			$attachments[$filename] = $data;  // this is a problem if two files have same name
		}
	
		// TEXT
		if ($p->type==0 && $data) 
		{
			// Messages may be split in different parts because of inline attachments,
			// so append parts together with blank row.
			if (strtolower($p->subtype)=='plain')
			{
				$plainmsg .= trim($data) ."\n\n";
			}
			else
			{
				$htmlmsg .= $data ."<br><br>";
			}
			$charset = $params['charset'];  // assume all parts are same charset
		}
	
		// EMBEDDED MESSAGE
		// Many bounce notifications embed the original message as type 2,
		// but AOL uses type 1 (multipart), which is not handled here.
		// There are no PHP functions to parse embedded messages,
		// so this just appends the raw source to the main message.
		elseif ($p->type==2 && $data) 
		{
			$plainmsg .= $data."\n\n";
		}

		// SUBPART RECURSION
		if ($p->parts)
		 {
			foreach ($p->parts as $partno0=>$p2)
				getpart($mbox,$mid,$p2,$partno.'.'.($partno0+1));  // 1.2, 1.2.1, etc.
		}
	}
	
	$charset;
	$htmlmsg;
	$plainmsg;
	$bytes;
	$attachments;
	$header;
	$options;*/
?>
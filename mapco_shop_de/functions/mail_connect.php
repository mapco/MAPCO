<?php
	mb_internal_encoding('UTF-8');
	require_once('des_encryption.php');
	
	function mail_connect($account_id, $folder_id)
	{ 
		global $dbweb;
		
		$sql = "SELECT cms_mail_servers.server, user, password, mailbox FROM cms_mail_accounts, cms_mail_servers, cms_mail_accounts_folders WHERE id_account=".$account_id." AND cms_mail_servers.id=cms_mail_accounts.server AND cms_mail_accounts_folders.id_folder=".$folder_id.";";
		$res = q($sql, $dbweb, __FILE__, __LINE__);
		$row = mysqli_fetch_assoc($res);
		
		$server = $row['server'].$row['mailbox'];		

		$data = hexToString($row['password']);
		$pass = des('f798f38d1ffa27a6c790c0f6bb842f6c', $data, 0, 0, null, null);
		$mbox = imap_open($server, $row['user'], $pass) or die("can't connect: " . imap_last_error());
		return $mbox;
	}
	
	function getmsg($mbox,$mid,$mode) 
	{		
		// input $mbox = IMAP stream, $mid = message id
		// output all the following:
		global $charset,$htmlmsg,$plainmsg,$attachments, $header, $head, $options, $struct;
		$htmlmsg = $plainmsg = $charset = '';
		$attachments = array();
		
		if ( $mode === 1 )
		{
			$options = FT_UID;
		}
		else
		{
			$options = FT_UID | FT_PEEK;
		}
		
		// HEADER
		//$h = imap_header($mbox,$mid);
		$h = imap_fetchheader($mbox, $mid, FT_UID);
		$b = imap_body($mbox,$mid, FT_UID | FT_PEEK);
		//var_dump($h); var_dump($b); die();
		$head = $h;
		$header = imap_rfc822_parse_headers( $h);
		// add code here to get date, from, to, cc, subject...
	
		// BODY
		$s = imap_fetchstructure($mbox,$mid, FT_UID); //var_dump($s); die();
		$struct = $s;
/*		if ( $_SESSION['id_user'] == 87921 )
		{
			var_dump($struct); die();
		}
*/
		if (!$s->parts)  // simple
			getpart($mbox,$mid,$s,0);  // pass 0 as part-number
		else {  // multipart: cycle through each part
			foreach ($s->parts as $partno0=>$p)
				getpart($mbox,$mid,$p,$partno0+1);
		}
	}
	
	function getpart($mbox,$mid,$p,$partno) 
	{	if ( $partno == '' )
		{	
			$part_number++;
		}
		// $partno = '1', '2', '2.1', '2.1.3', etc for multipart, 0 if simple
		global $htmlmsg,$plainmsg,$charset,$attachments, $options, $inline_pics;
	
		// DECODE DATA
		$data = ($partno)?
			imap_fetchbody($mbox,$mid,$partno, $options):  // multipart
			imap_body($mbox,$mid, $options);  // simple

		// Any part may be encoded, even plain text messages, so check everything.
		//var_dump($p); die();
		if ($p->encoding==4)
		{
			$data = quoted_printable_decode($data);
		/*	if ( $p->parameters[0]->attribute == "charset" and $p->parameters[0]->value == "iso-8859-1" )
			{
				$data = utf8_encode($data);
			}*/
		}
		elseif ($p->encoding==3)
		{
			$data = base64_decode($data);
		}// var_dump($data);
		//elseif ($p->encoding==1)
			//$data = imap_8bit($data);	
			
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
			if ( $p->ifid == 1 )
			{
				$inline_pics[$p->id]["type"]= $p->subtype; //image type ist gif
				$inline_pics[$p->id]["encoding"]= $p->encoding; //codierung
				$inline_pics[$p->id]["content"] = $data;
			}
			else
			{
				// filename may be given as 'Filename' or 'Name' or both
				$filename = ($params['filename'])? $params['filename'] : $params['name'];
				// filename may be encoded, so see imap_mime_header_decode()
				$attachments[$filename]['data'] = $data;  // this is a problem if two files have same name
				if ( $p->type == 5 )
				{
					$attachments[$filename]['type'] = 'images';
				}
				elseif ( $p->type == 6 )
				{
					$attachments[$filename]['type'] = 'videos';
				}
				else
				{
					$attachments[$filename]['type'] = 'files';
				}
				$attachments[$filename]['bytes'] = $p->bytes;
			}
		}
	
		// TEXT
		if ($p->type==0 && $data) 
		{
			if ( is_array($p->parameters) )
			{
				if ( $p->parameters[0]->attribute == 'charset' && ($p->parameters[0]->value != 'UTF-8' || $p->parameters[0]->value != 'utf-8') )
				{
					$data = nl2br(iconv($p->parameters[0]->value, "UTF-8", $data) );
				}
				else
				{
					$data = nl2br($data);
				}
			}
			
			// Messages may be split in different parts because of inline attachments,
			// so append parts together with blank row.
			//$data = imap_utf8($data);
			
			if (strtolower($p->subtype)=='plain' || strtolower($p->subtype)=='x-vcard' )
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
	
	function move_mail_to_archiv($mbox, $msg_num, $account_id, $folder_id)
	{
		global $dbweb;
		$folder_exist = 0;
		
		$res = q("SELECT cms_mail_servers.server FROM cms_mail_servers, cms_mail_accounts WHERE cms_mail_accounts.id_account=".$account_id." AND cms_mail_servers.id=cms_mail_accounts.server;", $dbweb, __FILE__, __LINE__);
	$row = mysqli_fetch_assoc($res);
		
		$list = imap_list($mbox, $row['server'], "*");
	
		foreach ( $list as $mailbox )
		{
			if ( substr($mailbox, strrpos($mailbox, '.')+1) == "Archiv" )
			{
				$folder_exist = 1;
				break;
			}
		}		
		$mailbox = "INBOX.Archiv";
		if ( $folder_exist == 0 )
		{
			$create_folder = imap_createmailbox($mbox, imap_utf7_encode($row['server'].$mailbox));
			if ( $create_folder == 1 )
			{
				$mail_moved = imap_mail_move($mbox,$msg_num,$mailbox, CP_UID);
			}
			else
			{
				var_dump($create_folder);
			}
		}
		else
		{
			$mail_moved = imap_mail_move($mbox,$msg_num,$mailbox, CP_UID);
		}
		
		imap_expunge($mbox);
		
		// ermittle history-eintrag
		$res_history = q("SELECT id_mail_history FROM cms_mail_history WHERE account_id='".$account_id."' AND folder_id=".$folder_id." AND msg_uid='".$msg_num."' LIMIT 1;", $dbweb, __FILE__, __LINE__);
		if ( mysqli_num_rows($res_history) == 1 )
		{
			$row_history = mysqli_fetch_assoc($res_history);
			
			// ermittle ziel folder_id
			$res_folder = q("SELECT id_folder FROM cms_mail_accounts_folders WHERE account_id='".$account_id."' AND mailbox='INBOX.Archiv';", $dbweb, __FILE__, __LINE__);
			$row_folder = mysqli_fetch_assoc($res_folder);
			
			$update_data = array();
			// erstelle artikel mit dem Text der Notiz
			$where = 'WHERE id_mail_history='.$row_history['id_mail_history'];
			$update_data['folder_id'] = $row_folder['id_folder'];
			q_update('cms_mail_history', $update_data, $where, $dbweb, __FILE__, __LINE__);
		}
		return $mail_moved;
		
	}
	
	$charset;
	$htmlmsg;
	$plainmsg;
	$attachments;
	$header;
	$struct;
	$options;
	$inline_pics;
	$head;
	
	function decodeISO88591($string)
	{ 
	  // Arrays for obtaining hexadecimal values 
	  // for each ISO-8859-1 charset
	  $mAlfa=array("A","B","C","D","E","F");
	  $mNum=array();
	  for($n=0;$n<10;$n++)
	  {
		$mNum[]=$n;
	  }
	  
	  // ISO-8859-1 charset
	  $iso88591=array(" ","�","�","�","�",
							 "�","�","�","�","�","�",
							 "�","�","�","�","�","�",
							 "�","�","�","�","�","�",
							 "�","�","�","�","�","�",
							 "�","�","�","�","�","�",
							 "�","�","�","�","�","�",
							 "�","�","�","�","�","�","�",
							 "�","�","�","�","�","�",
							 "�","�","�","�","�","�",
							 "�","�","�","�","�","�","�",
							 "�","�","�","�","�","�","�",
							 "�","�","�","�","�","�","�","�",
							 "�","�","�","�","�","�","�",
							 "�","�","�","�","�","�","�");
	  // Hexadecimal values array
	  for($a=0;$a<sizeof($mAlfa);$a++)
	  {
		for($n=0;$n<sizeof($mNum);$n++)
		{
		  $mHex[]=$mAlfa[$a].$mNum[$n];
		}
		for($a2=0;$a2<sizeof($mAlfa);$a2++)
		{
		  $mHex[]=$mAlfa[$a].$mAlfa[$a2];
		}
	  }
	  
	  // ISO-8859-1 string header and footer are deleted
	  $string=str_replace("=?iso-8859-1?q?","",$string);
	  $string=str_replace("?= ","",$string);
	  
	  // Encoded values are decoded
	  for($h=0;$h<sizeof($mHex);$h++)
	  {
		$string=str_replace(("=".$mHex[$h]),$iso88591[$h],$string);
	  }
	  
	  return($string);
	}
	
	function lock_mail ( $msg_num, $account )
	{
		global $dbweb;
		$where = 'WHERE `msg_uid`= '.$msg_num.' AND `account_id`='.$account;
		$update_data = array();
		$update_data['locked_by'] = $_SESSION['id_user'];
		$update_data["locked"] = time();
		q_update('cms_mail_history', $update_data, $where, $dbweb, __FILE__, __LINE__);
	}
	
	function unlock_mail ( $msg_num, $account )
	{
		global $dbweb;
		$where = 'WHERE `msg_uid`= '.$msg_num.' AND `account_id`='.$account;
		$update_data = array();
		$update_data['locked'] = 0;
		$update_data['locked_by'] = 0;
		q_update('cms_mail_history', $update_data, $where, $dbweb, __FILE__, __LINE__);
	}
	
	function replace_inline_images($message)
	{
		global $inline_pics;

		foreach ( $inline_pics as $img_id => $img_data )
		{ 
			$img_id = substr($img_id, 1, strlen($img_id)-2);
			$search_string = 'cid:'.$img_id;
			$replace_data = 'data:image/gif;base64,'.base64_encode($img_data['content']);
			$message = str_ireplace($search_string, $replace_data, $message);
		} 
		return $message;
	}
?>

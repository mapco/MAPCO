<?php 
	function createPassword($length)
	{
		$chars = "1234567890abcdefghijkmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
		$i = 0;
		$password = "";
		while ($i <= $length) {
			$password .= $chars{mt_rand(0,strlen($chars)-1)};
			$i++;
		}
		return $password;
	}

	mb_internal_encoding('UTF-8');
	require_once("../../mapco_shop_de/functions/mail_connect.php");
	
/*	function replaceByContentID($matches)
	{var_dump($matches);
		global $inline_pics;
		$image = $inline_pics[ $matches['cid'] ]; //Hole image aus array mit der jeweiligen cid
		var_dump('src="data:' . $image['type'] .';'. $image['encoding'] .','. $image['content'] .'"');
		return '$matches';//'src="data:' . $image['type'] .';'. $image['encoding'] .','. $image['content'] .'"'; //gibt das image zurück...			
	}*/

	check_man_params(array(
						"msg_num"	=> "numeric",
						"account"	=> "numericNN",
						"mode"		=> "numericNN"
						)
					);

	//$mbox = mail_connect($_POST['account']);
	$mbox = mail_connect($_POST['account']);
	
	if ( $_POST['mode'] == 2 && $_POST['msg_num'] == 0 )
	{
		$xml = '';
		$tmp_mails = array();
		//get sender mails
		$result_mails=q("SELECT DISTINCT user, id_account FROM cms_mail_accounts, cms_mail_accounts_users WHERE user_id=".$_SESSION['id_user']." AND id_account=account_id;", $dbweb, __FILE__, __LINE__);
		while ($row_mails=mysqli_fetch_array($result_mails) )
		{				
			if ( !in_array($row_mails['user'], $tmp_mails) )
			{
				$tmp_mails[] = $row_mails['user'];
				$xml .= '<sendermail><![CDATA[<option';
				if ( $row_mails['id_account'] == $_POST['account'] )
				{
					$xml .= ' selected="selected"';
				}
				$xml .= '>'.$row_mails['user'].'</option>]]></sendermail>'."\n";
			}
		}
		print $xml;
	}
	elseif ( $_POST['mode'] == 3 )
	{
		unlock_mail ( $_POST['msg_num'], $_POST['account'] );
		imap_close($mbox);
	}
	else
	{
		$result_lock=q("SELECT locked, locked_by FROM cms_mail_history WHERE account_id=".$_POST['account']." AND msg_uid=".$_POST['msg_num']." LIMIT 1;", $dbweb, __FILE__, __LINE__);
		$row_lock=mysqli_fetch_array($result_lock);

		$lock_time = time()-900;
		if ( ($row_lock['locked'] == 0 and $row_lock['locked_by'] == 0) or ($row_lock['locked']<$lock_time and $row_lock['locked_by'] > 0) or $row_lock['locked_by'] == $_SESSION['id_user'] )
		{
			$xml = '';
			$msg = array();
			
			getmsg($mbox,$_POST['msg_num']);

			imap_close($mbox);
			
			$i = 0;
			foreach ( $attachments as $filename => $filedata )
			{	
				$fname = explode(".",$filename);
				$filename = createPassword(20).'.'.$fname[1];
				
				//get filename
				do
				{
					$filename2="../temp/".$filename;
					$filename=substr($filename2,3);
				}
				while( file_exists($filename2) );
				
				//create file
				$handle=fopen($filename2, "w");
				fwrite($handle,$filedata);
				
			//	$path = PATH.$filename;
			//	$path = str_replace("temp/","",$path, 1);
				
				$msg['attachments'][$i]['File'] = str_replace("../", "", $filename2);
				$msg['attachments'][$i]['Filename'] = $fname[0].'.'.$fname[1];
				$msg['attachments'][$i]['Path'] = PATH.$filename2;
				$i++;
			}
			
		//	$attachments = array_keys($attachments);			
		//$msg['attachments'] = implode(',',$attachments);
	
	/*		//CID image names will be dynamic so i must use REGEX
        $fileto = "/src="cid:(.*).png@(.*)/"";
        #$fileto = "/src="cid:(.*).bmp(.*)/"";
        #$fileto = "/src="cid:(.*).png(.*)/"";
        #$fileto = "/src="cid:(.*).gif(.*)/"";

        $remote_f = 'data:image/gif;base64,DATEN';
		
        $new_img = str_replace($fileto, $remote_f, $body);*/

			if ( $_POST['mode'] == 2)
			{
				if ( $plainmsg != '' )
				{
					$body = nl2br($plainmsg);
				}
				else
				{
					$body = $htmlmsg;
				}
				$msg['text'] = $body;
				
				//get sender mails
				
				$result_mails=q("SELECT DISTINCT user, id_account FROM cms_mail_accounts, cms_mail_accounts_users WHERE user_id=".$_SESSION['id_user']." AND id_account=account_id;", $dbweb, __FILE__, __LINE__);
				while ($row_mails=mysqli_fetch_array($result_mails) )
				{
					//$xml .= '<sendermail>'.$row_mails['mail'].'</sendermail>'."\n";
					$xml .= '<sendermail><![CDATA[<option';
					if ( $row_mails['id_account'] == $_POST['account'] )
					{
						$xml .= ' selected="selected"';
					}
					$xml .= '>'.$row_mails['user'].'</option>]]></sendermail>'."\n";
				}
				$head = imap_rfc822_parse_headers($header);
				$msg['From'] = $head->from[0]->mailbox.'@'.$head->from[0]->host;
				$msg['Subject'] = iconv_mime_decode($head->subject, 0, "utf-8");
			}
			elseif ( $_POST['mode'] == 1 )
			{ 
				lock_mail ( $_POST['msg_num'], $_POST['account'] );
	
				if ( $htmlmsg != '' )
				{
					$body = $htmlmsg;
				}
				else
				{
					$body = nl2br($plainmsg);
				}
				$msg['text'] = $body;
			}

	/*		preg_match_all('/src="cid:(.*)"/Uims', $msg['text'], $out, PREG_SET_ORDER);
var_dump($inline_pics['<image001.png@01CF8A10.649C04D0">']); die();
			if ( sizeof($out) > 0 )
			{ 
			
				foreach ( $out as $inline_element )
				{
					$imageid = "<". $inline_element[0] .">";
					$imageid = str_ireplace('src="cid:','',$imageid);
					var_dump($imageid);
								var_dump($inline_pics[$imageid]);
					
					$msg['text'] = str_ireplace($inline_element[0],$replace_data,$msg['text']);
					
				}
			} 
			
			var_dump($inline_pics);die();
			*/
			if ( sizeof($inline_pics) > 0 )
			{
				foreach ( $inline_pics as $img_id => $img_data )
				{ 
					$img_id = substr($img_id, 1, strlen($img_id)-2);
					$search_string = 'cid:'.$img_id;
					$img_id = "<".$img_id.">";
					$replace_data = 'data:image/gif;base64,'.base64_encode($inline_pics[$img_id]['content']);
					$msg['text'] = str_ireplace($search_string, $replace_data, $msg['text']);
				}
			}


		//	$msg['text'] = $mbox->get_msg($_POST['msg_num'], $_POST['mode']);
			//var_dump($msg); die();
			$xml .= '<msg>'."\n";
			foreach ( $msg as $key => $value )
			{
				if ( $key == 'attachments' )
				{
					foreach ( $value as $attachment )
					{
						$xml .= '<attachment>'."\n";
						$xml .= '	<File><![CDATA['.$attachment['File'].']]></File>'."\n";
						$xml .= '	<Filename><![CDATA['.$attachment['Filename'].']]></Filename>'."\n";
						$xml .= '	<Path><![CDATA['.$attachment['Path'].']]></Path>'."\n";
						$xml .= '</attachment>'."\n";	
					}
				}
				else
				{
					if ( !is_numeric($value) )
					{
						$value = "<![CDATA[".$value."]]>";	
					}
					$xml .= '	<'.$key.'>'.$value.'</'.$key.'>'."\n";
				}
			}
			$xml .= '</msg>'."\n";
			
			print $xml;
		}
		else
		{
			$result_user=q("SELECT firstname, lastname FROM cms_contacts WHERE idCmsUser=".$row_locked['locked_by']." LIMIT 1;", $dbweb, __FILE__, __LINE__);
			$row_user=mysqli_fetch_array($result_user);
			$xml = '<Error>Diese Mail wird gerade von '.$row_user['firstname'].' '.$row_user['lastname'].' bearbeitet!</Error>'."\n";
			print $xml;
		}
	}
		
?>
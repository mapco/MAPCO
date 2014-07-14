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
						"folder"	=> "numericNN",
						"mode"		=> "numericNN"
						)
					);

	//$mbox = mail_connect($_POST['account']);
	$mbox = mail_connect($_POST['account'], $_POST['folder']);
	
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
		$result_lock=q("SELECT conversation_id, locked, locked_by FROM cms_mail_history WHERE account_id=".$_POST['account']." AND folder_id=".$_POST['folder']." AND msg_uid=".$_POST['msg_num']." LIMIT 1;", $dbweb, __FILE__, __LINE__);
		$row_lock=mysqli_fetch_assoc($result_lock);
		
		$lock_time = time()-900;
		if ( ($row_lock['locked'] == 0 and $row_lock['locked_by'] == 0) or ($row_lock['locked']<$lock_time and $row_lock['locked_by'] > 0) or $row_lock['locked_by'] == $_SESSION['id_user'] )
		{
			$xml = '';
			$msg = array();
			
			$result_order=q("SELECT order_id, user_id FROM crm_conversations WHERE id=".$row_lock['conversation_id']." LIMIT 1;", $dbweb, __FILE__, __LINE__);
			$row_order=mysqli_fetch_assoc($result_order);
			
			$msg['order_id'] = $row_order['order_id'];
			$msg['user_id'] = $row_order['user_id'];

			getmsg($mbox,$_POST['msg_num'],$mode,1);

			imap_close($mbox);

			if ( sizeof($attachments) > 0 )
			{
				$res_his = q('SELECT id_mail_history FROM cms_mail_history WHERE `msg_uid`= '.$_POST["msg_num"].' AND `account_id`='.$_POST["account"].' AND folder_id='.$_POST['folder'].";", $dbweb, __FILE__, __LINE__);
				$row_his = mysqli_fetch_assoc($res_his);
				$id_mail_history = $row_his['id_mail_history'];

				$res_att = q('SELECT att_name, att_path FROM cms_mail_history_files WHERE `msg_id`='.$id_mail_history.';', $dbweb, __FILE__, __LINE__);
				while ( $row_att = mysqli_fetch_assoc($res_att) )
				{
					$registered_attachments[$row_att['att_name']] = $row_att['att_path'];
				}
				
				$i = 0;
				$update_history = false;
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
					
					if ( sizeof($registered_attachments) > 0 )
					{
						foreach ( $registered_attachments as $att_name => $att_path )
						{
							if ( $att_name == $fname[0].".".$fname[1] && file_exists($att_path) === true )
							{
								$filename2 = $att_path;
								$msg['attachments'][$i]['writed'] = "0";	
							}
							else
							{
								//create file
								$handle=fopen($filename2, "w");
								fwrite($handle,$filedata['data']);
								
								$msg['attachments'][$i]['writed'] = "1";	
							
								$res_his_files = q("SELECT id FROM cms_mail_history_files WHERE att_name='".$att_name."' AND msg_id=".$id_mail_history.";", $dbweb, __FILE__, __LINE__);
								$row_his_files = mysqli_fetch_assoc($res_his_files);
								$where = 'WHERE `id`= '.$row_his_files['id'];
								$update_data = array();	
								$update_data['att_path'] = $filename2;
								q_update('cms_mail_history_files', $update_data, $where, $dbweb, __FILE__, __LINE__);	
							}
						}
					}
					else
					{
						$update_data = array();
						$update_data['msg_id'] = $id_mail_history;
						$update_data['att_name'] = $fname[0].".".$fname[1];
						$update_data['att_path'] = $filename2;
						q_insert('cms_mail_history_files', $update_data, $dbweb, __FILE__, __LINE__);	
					}
					//	$path = PATH.$filename;
					//	$path = str_replace("temp/","",$path, 1);
					
					$msg['attachments'][$i]['File'] = str_replace("../", "", $filename2);
					$msg['attachments'][$i]['Filename'] = $fname[0].'.'.$fname[1];
					$msg['attachments'][$i]['Path'] = PATH.$filename2;
					
					$i++;
				}
			}
			
			if ( $_POST['mode'] == 2 or $_POST['mode'] == 4 )
			{
				if ( $_POST['msg_num'] > 0 )
				{					
					if ( $plainmsg != '' )
					{
						$body = $plainmsg;
					}
					else
					{
						$body = $htmlmsg;
					}
					$msg['text'] = nl2br($body);
				}
				else
				{
					$msg['text'] = '';
				}
				
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

				if ( $_POST['submode'] == 1 )
				{
					$msg['From'] = $header->from[0]->mailbox.'@'.$header->from[0]->host;
				}
				$msg['Subject'] = iconv_mime_decode($header->subject, 0, "utf-8");

				$result_mails=q("SELECT DISTINCT user, id_account FROM cms_mail_accounts, cms_mail_accounts_users WHERE user_id!=".$_SESSION['id_user']." AND id_account=account_id;", $dbweb, __FILE__, __LINE__);
				while ($row_mails=mysqli_fetch_array($result_mails) )
				{				
					if ( !in_array($row_mails['user'], $tmp_mails) )
					{
						$tmp_mails[] = $row_mails['user'];
						$xml .= '<ToAddress><![CDATA['.$row_mails['user'].']]></ToAddress>'."\n";
					}
				}
				print $xml;

				switch($_SESSION['lang'])
				{
					case 'de': setlocale(LC_TIME,"de_DE");
								break;
					case 'fr': setlocale (LC_TIME,"fr_FR");
								break;			
					case 'es': setlocale (LC_TIME,"es_ES");
								break;
					case 'it': setlocale (LC_TIME,"it_IT");
								break;
					case 'pl': setlocale (LC_TIME,"pl_PL");
								break;			
					default: setlocale(LC_TIME,"en_GB");
							break;
				}

				$msg['text'] = '
==========boundary=old_message==========
____________________________________________
Gesendet: '.strftime("%A, %d. %B %G, %R",strtotime($header->date)).'
Von: '.$header->fromaddress.'
An: '.$header->toaddress.'
Betreff: '.$msg['Subject'].' 

'.$msg['text'];					

				//archiviere originalmail
				//$mail_moved = move_mail_to_archiv($mbox, $_POST['msg_num'], $_POST['account'], $_POST['folder']);		
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
					$body = $plainmsg;
				}
				$msg['text'] = nl2br($body);
			}
			
			if ( sizeof($inline_pics) > 0 )
			{
				$msg['text'] = replace_inline_images($msg['text']);
			}

			$xml .= '<msg>'."\n";
			foreach ( $msg as $key => $value )
			{
				if ( $key == 'attachments' )
				{
					foreach ( $value as $attachment )
					{
						$xml .= '<attachment>'."\n";
						$xml .= '	<writed><![CDATA['.$attachment['writed'].']]></writed>'."\n";
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
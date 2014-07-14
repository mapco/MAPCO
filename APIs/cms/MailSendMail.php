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

	require '../../mapco_shop_de/modules/PHPMailer/PHPMailerAutoload.php';
	//Import('PHPMailer.PHPMailerAutoload');
		
	require_once("../../mapco_shop_de/functions/mail_connect.php");
	$mbox = mail_connect($_POST['account_id'], $_POST['folder_id']);
	
	$mail = new PHPMailer();
	//Set who the message is to be sent from
	$mail->setFrom($_POST['sender']);
	//Set an alternative reply-to address
	$mail->addReplyTo($_POST['sender']);
	//Set who the message is to be sent to
	$mail->addAddress($_POST['receiver']);
	
	
	$mail->addCC($_POST['CC']);
	
	$mail->addBCC($_POST['BCC']);
	
	//Set the subject line
	$mail->CharSet = 'utf-8';
	$mail->ContentType = 'multipart/mixed';
	$mail->Subject = $_POST['subject'];
	
	$mail->AltBody = $mail->html2text($_POST['message'],true);
	$mail->Body = nl2br($_POST['message']);
	
	if ( sizeof($_POST['attachments']) > 0 )
	{
		foreach ( $_POST['attachments'] as $filename => $filepath )
		{			
			$mail->addAttachment($filepath,$filename);
		}
	}	

	$res_signature = q("SELECT id_signature, signature_text, signature_cid FROM cms_mail_signatures, cms_mail_accounts WHERE id_account=".$_POST['account_id']." AND id_signature=signature_id LIMIT 1;", $dbweb, __FILE__, __LINE__);
	$row_signature = mysqli_fetch_assoc($res_signature);
	$signature = $row_signature['signature_text'];
	if ( $row_signature > 0 )
	{
		$res_sign_imgs = q("SELECT replace_tag, file_id, filename, extension FROM cms_mail_signatures_images, cms_files WHERE signature_id=".$row_signature['id_signature']." AND id_file=file_id;", $dbweb, __FILE__, __LINE__);
		while ( $row_sign_imgs = mysqli_fetch_assoc($res_sign_imgs) )
		{
			$filename = $row_sign_imgs['filename'].".".$row_sign_imgs['extension'];
			$cid = $row_sign_imgs['filename'].'@'.$row_signature['signature_cid']; //md5(uniqid (rand(8), 1)).'.'.md5(uniqid (rand(8), 1));
			
			$search_string = "<!--".$row_sign_imgs['replace_tag']."-->";
			$replace_string = "<img src=\"cid:".$cid."\" alt=\"\">";

			if ( stripos($row_signature['signature_text'], $cid) === false )
			{
				$img_folder = $row_sign_imgs['file_id']/1000;
				$image_path = "../../mapco_shop_de/files/".$img_folder;
				$mail->addEmbeddedImage($image_path, $cid, $filename);
			}
			
			$signature = str_ireplace($search_string, $replace_string, $signature);
		}
	}		

	$mail->ALtBody .= $row_signature['signature_text'];
	$mail->Body .= '<br /><br />'.nl2br($signature);
	
	if ( isset($_POST['msg_num']) && $_POST['msg_num'] > 0 )
	{	
		getmsg($mbox,$_POST['msg_num'],$mode,1);

		$oldMsg .= '
		____________________________________________
Gesendet: '.strftime("%A, %d. %B %G, %R",strtotime($header->date)).'
Von: '.$header->fromaddress.'
An: '.$header->toaddress.'
Betreff: '.iconv_mime_decode($header->subject, 0, "utf-8").'

';

		$mail->AltBody .= $oldMsg.'
	
		'.$plainmsg;
	
		if ( $htmlmsg != '' )
		{
			$mail->Body .= nl2br($oldMsg).'<br /><br />'.$htmlmsg;
		}
		else
		{
			$mail->Body .= nl2br($oldMsg).'<br /><br />'.nl2br($plainmsg);
		}
		
		if ( $_POST['mode'] == 2 )
		{
			if ( sizeof($attachments) > 0 && sizeof($inline_pics) >0 )
			{
				$attachments = array_merge($attachments, $inline_pics);
			}
			elseif ( sizeof($inline_pics) > 0 )
			{
				$attachments = $inline_pics;
			}
		}
		else
		{
			$attachments = $inline_pics;
		}
		
		if ( sizeof($attachments) > 0 )
		{	
			$isInline = false;
			foreach ( $attachments as $filename => $filedata )
			{	
				$isInline = strpos($filename, "@");		
				$data = $filedata['data'];
				if ( $isInline !== false )
				{
					$filename = substr($filename, 1, strlen($filename)-2);
					$cid = $filename;
					$filename = explode("@",$filename);	
					$filename = $filename[0];
					$data = $filedata['content'];
				}

				$fname = explode(".",$filename);
				$filename = createPassword(20).'.'.$fname[1];
				
				//get filename
				do
				{
					$filename2="../../mapco_shop_de/temp/".$filename;
					$filename=substr($filename2,3);
				}
				while( file_exists($filename2) );
				
				//create file																					
				$handle=fopen($filename2, "w");
				fwrite($handle,$data);
				
				if ( $isInline !== false )
				{	
					$mail->addEmbeddedImage($filename2, $cid, $fname[0]);
				}
				else
				{
					$mail->addAttachment($filename2,$fname[0].'.'.$fname[1]);
				}
			}
		}
	}
	
	//send the message, check for errors
	if (!$mail->send()) 
	{
		echo "Mailer Error: " . $mail->ErrorInfo;
	} 
	else 
	{
		// schicke kopie an den postausgang
		$res_server = q("SELECT cms.server FROM cms_mail_servers AS cms, cms_mail_accounts AS cma WHERE cma.id_account=".$_POST['account_id']." AND cms.id=cma.server LIMIT 1;", $dbweb, __FILE__, __LINE__);
		$row_server = mysqli_fetch_assoc($res_server);
		$server = $row_server['server']."INBOX.Sent";
		$move_to_sent = imap_append($mbox, $server
			   , "Subject: ".$_POST["subject"]."\r\n"
               . $mail->MIMEHeader."\r\n"
			   . $mail->MIMEBody."\r\n"
			   );
		imap_close($mbox);
		
		// setze replied und replied by in history
		if ( isset($_POST['account_id']) && isset($_POST['folder_id']) )
		{	
			if ( isset($_POST['msg_num']) && $_POST['msg_num'] > 0 )
			{		
				$where = 'WHERE `msg_uid`= '.$_POST["msg_num"].' AND `account_id`='.$_POST["account_id"].' AND folder_id='.$_POST['folder_id'];
				$update_data = array();	
				$update_data['replied'] = $msg_date_ts;
				$update_data['replied_by'] = $_SESSION['id_user'];
				$update_data["lastmod"] = $msg_date_ts;
				$update_data["lastmod_user"] = $_SESSION['id_user'];
				q_update('cms_mail_history', $update_data, $where, $dbweb, __FILE__, __LINE__);
			}
			
			$result_sent=q("SELECT id_folder FROM cms_mail_accounts_folders WHERE account_id=".$_POST["account_id"]." AND mailbox='INBOX.Sent';", $dbweb, __FILE__, __LINE__);
			$row_sent=mysqli_fetch_array($result_sent);
			
			$mbox = mail_connect($_POST['account_id'], $row_sent['id_folder']);
			
			$moved_mail_search = imap_search($mbox, 'ON "'.$msg_date.'"', SE_UID);
			$search_results = sizeof($moved_mail_search);
			if ( $search_results > 1 )
			{
				for($i=0; $i<$search_results; $i++)
				{
					$newheader = imap_fetchheader($mbox, $moved_mail_search[$i], FT_UID);
					$newhead = imap_rfc822_parse_headers($newheader);
					if ( $newhead->from[0]->mailbox.'@'.$newhead->from[0]->host === $_POST["sender"] && iconv_mime_decode($newhead->subject, 0, "utf-8") === $_POST["subject"] )
					{
						$new_msg_num = $moved_mail_search[$i];	
					}
					else
					{
						unset($moved_mail_search[$i]);	
					}
				}
			}
			elseif( $search_results === 1 )
			{
				$new_msg_num = $moved_mail_search[0];
			}
			
			$header = imap_fetchheader($mbox, $new_msg_num, FT_UID);
			$header = imap_rfc822_parse_headers($header);
			imap_close($mbox);
			
			$update_data = array();
			$update_data["account_id"] = $_POST['account_id'];
			$update_data["folder_id"] = $_POST['folder_id'];
			$update_data["msg_uid"] = $_POST['msg_uid'];
			$update_data["mail_from"] = $_POST['sender'];
			$update_data["subject"] = $_POST["subject"];
			$update_data["locked"] = 0;
			$update_data["locked_by"] = 0;
			$update_data['replied'] = 0;
			$update_data['replied_by'] = 0;
			$update_data['conversation_id'] = (int)$response->conversation_id[0];
			$update_data["firstmod"] = $msg_date_ts;
			$update_data["firstmod_user"] = $_SESSION['id_user'];
			$update_data["lastmod"] = $msg_date_ts;
			$update_data["lastmod_user"] = $_SESSION['id_user'];
			q_insert('cms_mail_history', $update_data, $dbweb, __FILE__, __LINE__);
			
			if ( $_POST['order_id'] > 0 || $_POST['user_id'] > 0 )
			{
				//GET SITE-ID
				$results=q("SELECT * FROM cms_users_sites WHERE user_id=".$_POST["user_id"].";", $dbweb, __FILE__, __LINE__);
				$cms_users_sites=mysqli_fetch_array($results);
				
				//save email in cms_articles
				$post_data["API"]="cms";
				$post_data["APIRequest"]="ArticleAdd";
				$post_data["site_id"]=$cms_users_sites["site_id"];
				$post_data["title"]=$_POST["subject"];
				$post_data["article"]=$mail->Body;	
				$post_data["format"]=1;
				$post_data["firstmod"] = $msg_date_ts;
				$post_data["lastmod"] = $msg_date_ts;
				
				$postdata=http_build_query($post_data);
				
				$response=soa2($postdata, __FILE__, __LINE__);
				$article_id=(int)$response->article_id[0];
				
				
				//save conversation in crm_conversations
				$post_data=array();
				$post_data["API"]="crm";
				$post_data["APIRequest"]="ConversationAdd";
				$post_data["user_id"]=$_POST["user_id"];	
				$post_data["order_id"]=$_POST["order_id"];
				$post_data["article_id"]=$article_id;
				$post_data["type_id"]=1;
				$post_data["con_from"]=$_POST["sender"];
				$post_data["con_to"]=$_POST["receiver"];
				
				$postdata=http_build_query($post_data);
				
				$response=soa2($postdata, __FILE__, __LINE__);
			
				//save article label in cms_articles_labels
				$post_data=array();
				$post_data["API"]="cms";
				$post_data["APIRequest"]="ArticleLabelAdd";
				$post_data["article_id"]=$article_id;
				$post_data["label_id"]=21;
				
				$postdata=http_build_query($post_data);
				
				$response=soa2($postdata, __FILE__, __LINE__);	
			}
		}
	}
?>
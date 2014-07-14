<?php 
	mb_internal_encoding("UTF-8");
	require_once("../../mapco_shop_de/functions/mail_connect.php");

	function decode_mail_part($encoding, $data)
	{   global $mbox;
		switch($encoding)
		{
			case 3: $data = base64_decode($data);
				break;
			case 4:	$data = imap_qprint($data);//quoted_printable_decode($data);
				break;
			case 5: $data = base64_decode($data);
				break;
		}
		return $data;
	}

	//id_type: 1 -> user_id, 2 -> order_id
	check_man_params(array("id_type" => "numeric", "id"	=> "numeric", "msg_num" => "numericNN"));
	
	$user_id = 0;
	$order_id = 0;
	
	if ( $_POST['id_type'] == 1 )
	{
		$user_id = $_POST['id'];
	}
	elseif ( $_POST['id_type'] == 2 )
	{
		// Daten auslesen	
		// lese Notizen aus Shop Orders
		$res_order = q("SELECT customer_id, shop_id FROM shop_orders WHERE id_order=".$_POST['id'].";", $dbshop, __FILE__, __LINE__);
		if ( mysqli_num_rows($res_order) != 1)
		{
			print "<Error>Keine Bestellung mit der ID ".$_POST['id']." gefunden!</Error>\n";
			die();
		}
		$row_order = mysqli_fetch_assoc($res_order);
		$user_id = $row_order['customer_id'];
		$order_id = $_POST['id'];
	}
	
	$mbox = mail_connect($_POST['account'], $_POST['folder']);

	getmsg($mbox,$_POST['msg_num'],$mode);

	$msg['to'] = $header->to[0]->mailbox.'@'.$header->to[0]->host;
	//$msg['from'] = mb_decode_mimeheader($header->from[0]->mailbox).'@'.mb_decode_mimeheader($header->from[0]->host);
	$msg['from'] = $header->from[0]->mailbox.'@'.$header->from[0]->host;
	$msg['cc'] = '';
	$msg['bcc'] = '';
	$msg['subject'] = iconv_mime_decode($header->subject, 0, "utf-8");
	$msg['date'] = strtotime($header->date);
	$att_types = array();
	$attachment_types = array(
							1 => "files",
							2 => "images",
							3 => "videos"
						);
	
	if ( $htmlmsg != '' )
	{
		$msg["message"] = $htmlmsg;
		$msg["format"] = 1;
	}
	else
	{
		$msg["message"] = $plainmsg;
		$msg["format"] = 0;
	}

	//get article ordering
	$results=q("SELECT COUNT(id_article) AS articles FROM cms_articles;", $dbweb, __FILE__, __LINE__);
	$row=mysqli_fetch_array($results);
	$ordering_articles=$row['articles']+1;
	
	//get articles_lables ordering
	$results=q("SELECT COUNT(article_id) AS articles_labels FROM cms_articles_labels WHERE label_id=21;", $dbweb, __FILE__, __LINE__);
	$row=mysqli_fetch_array($results);
	$ordering_labels=$row['articles_lables']+1;

	if ( $order_id > 0 )
	{
		$res_shop = q("SELECT site_id FROM shop_shops WHERE id_shop=".$row_order['shop_id']." LIMIT 1;", $dbshop, __FILE__, __LINE__);
		$shop = mysqli_fetch_assoc($res_shop);
	}
	else
	{
		$res_site = q("SELECT site_id FROM cms_users_sites WHERE user_id=".$user_id." LIMIT 1;", $dbweb, __FILE__, __LINE__);
		$shop = mysqli_fetch_assoc($res_site);
	}
	
	$data = array();
	// erstelle artikel mit dem Text der Notiz
	$data['title'] = $msg['subject'];
	$data['article'] = $msg["message"];
	$data["site_id"] = $shop["site_id"];
	$data['firstmod'] = $msg['date'];
	$data['firstmod_user'] = $_SESSION['id_user'];
	$data['lastmod'] = time();
	$data['lastmod_user'] = $_SESSION['id_user'];
	$data['published'] = 0;
	$data['format'] = $msg['format'];
	$data['ordering'] = $ordering_articles;
	q_insert('cms_articles', $data, $dbweb,  __FILE__, __LINE__);
	$res_check_article = q("SELECT id_article FROM cms_articles WHERE title='".mysqli_real_escape_string($dbshop, $data['title'])."' AND site_id=".$data['site_id']." AND firstmod=".$data['firstmod']." AND lastmod=".$data['lastmod']." AND lastmod_user=".$data['lastmod_user'].";", $dbweb, __FILE__, __LINE__);
	$row_check_article = mysqli_fetch_assoc($res_check_article);
	$article_id = $row_check_article['id_article'];

		
	if ( $article_id > 0 )
	{
		// setze Label für diesen Artikel
		$data = array();
		$data['article_id'] = $article_id;
		$data['label_id'] = 21;
		$data['ordering'] = $ordering_labels;
		q_insert('cms_articles_labels', $data, $dbweb,  __FILE__, __LINE__);
	 
	 	// ermittle CustomerID
		$res_customer = q("SELECT id_crm_customer FROM crm_customers WHERE user_id=".$user_id.";", $dbweb, __FILE__, __LINE__);
		$row_customer = mysqli_fetch_assoc($res_customer);
		
		// schreibe Eintrag in die crm_conversations
		$data = array();
		$data["con_from"]=$msg["from"];
		$data["con_to"]=$msg["to"];
		$data["con_cc"]=$msg["cc"];
		$data["con_bcc"]=$msg["bcc"];
		$data['article_id'] = $article_id;
		$data['user_id'] = $user_id;
		$data['customer_id'] = $row_customer['id_crm_customer'];
		$data['order_id'] = $order_id;
		$data['type_id'] = 1;
		$data['firstmod'] = $msg['date'];
		$data['firstmod_user'] = $_SESSION['id_user'];
		$data['lastmod'] = time();
		$data['lastmod_user'] = $_SESSION['id_user'];
		q_insert('crm_conversations', $data, $dbweb,  __FILE__, __LINE__);
		$sql = "SELECT id FROM crm_conversations WHERE article_id=".$article_id." AND con_from='".$data['con_from']."' AND con_to='".$data['con_to']."' AND firstmod=".$data['firstmod']." AND lastmod=".$data['lastmod']." AND lastmod_user=".$data['lastmod_user'].";";
		$res_check_convers = q($sql, $dbweb, __FILE__, __LINE__);
		$row_check_convers = mysqli_fetch_assoc($res_check_convers);
		$conversation_id = $row_check_convers['id'];
		
		$f = 0;
		$i = 0;
		$v = 0;
		
		foreach( $attachments as $filename => $file_data )
		{		
			$fn_arr = explode('.', $filename);
			$data = array();		
			$data['filename'] = $fn_arr[0];
			$data['extension'] = $fn_arr[1];
			$data['filesize'] = $file_data['bytes'];
			$data['description'] = '';
			$data['original_id'] = 0;
			
			$data['firstmod'] = $msg['date'];
			$data['firstmod_user'] = $user_id;
			$data['lastmod'] = time();
			$data['lastmod_user'] = $user_id;
			
			q_insert('cms_files', $data, $dbweb, __FILE__, __LINE__);
			$res_file = q("SELECT id_file FROM cms_files WHERE filename='".$data['filename']."' AND extension='".$data['extension']."' AND firstmod=".$data['firstmod']." AND firstmod_user=".$data['firstmod_user']." AND lastmod=".$data['lastmod']." AND lastmod_user=".$data['lastmod_user'].";",$dbweb, __FILE__, __LINE__);
			$row_file = mysqli_fetch_assoc($res_file);
			$file_id = $row_file['id_file'];
			
			$extension = $fn_arr[1];
			$dir=floor(bcdiv($file_id, 1000)); 
	
			if (!file_exists("../files/".$dir)) mkdir("../files/".$dir);
			$destination='../files/'.$dir.'/'.$file_id.'.'.$fn_arr[1];
			$filehandle = fopen($destination, "w");
			fputs($filehandle, $file_data['data']);
			fclose($filehandle);
			
			$data = array();
			$data['article_id'] = $article_id;
			$data['file_id'] = $file_id;
			
			if ( $file_data['type'] == 'images' )
			{
				$data['firstmod'] = $file_date;
				$data['firstmod_user'] = $user_id;
				$data['lastmod'] = time();
				$data['lastmod_user'] = $user_id;
				$data['ordering'] = $i;
				$i++;
			}
			elseif ( $file_data['type'] == 'videos' )
			{
				$data['ordering'] = $v;
				$v++;
			}
			else
			{
				$data['ordering'] = $f;
				$f++;
			}
			q_insert('cms_articles_'.$file_data['type'], $data, $dbweb, __FILE__, __LINE__);
		}
	}


	// wenn conversation geschrieben, archiviere Mail und aktualisiere history eintrag
	if ( isset($conversation_id) )
	{
		$where = 'WHERE `msg_uid`= '.$_POST["msg_num"].' AND `account_id`='.$_POST["account"];
		$update_data = array();
		$update_data['locked'] = 0;
		$update_data['locked_by'] = 0;
		$update_data['conversation_id'] = $conversation_id;
		$update_data["lastmod"] = time();
		$update_data["lastmod_user"] = $_SESSION['id_user'];
		q_update('cms_mail_history', $update_data, $where, $dbweb, __FILE__, __LINE__);
		
		print '<conversation_id>'.$conversation_id.'</conversation_id>'."\n";
		//$mail_moved = move_mail_to_archiv($mbox, $_POST['msg_num'], $_POST['account'], $_POST['folder']);
	}
	
	imap_close($mbox);
?>
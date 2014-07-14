<?php 
	mb_internal_encoding("UTF-8");
	
	check_man_params(array("account" => "numericNN", "folder" => "numericNN", "msg_num" => "numericNN", "target" => "numericNN"));
	
	require_once("../../mapco_shop_de/functions/mail_connect.php");						

	$mbox = mail_connect($_POST['account'], $_POST['folder']);

	$res = q("SELECT mailbox FROM cms_mail_accounts_folders WHERE id_folder=".$_POST['target'].";", $dbweb, __FILE__, __LINE__);
	$row = mysqli_fetch_assoc($res);

	$msg = array();
	$header = imap_fetchheader($mbox, $_POST['msg_num'], FT_UID);
	$head = imap_rfc822_parse_headers($header);
	$msg['From'] = $head->from[0]->mailbox.'@'.$head->from[0]->host;
	$msg['Subject'] = iconv_mime_decode($head->subject, 0, "utf-8");
	$msg['Date'] = $head->date;
	
	$mail_moved = imap_mail_move($mbox,$_POST['msg_num'],$row['mailbox'], CP_UID);
	imap_expunge($mbox);	
	imap_close($mbox);
	
	$mbox = mail_connect($_POST['account'], $_POST['target']);
	$moved_mail_search = imap_search($mbox, 'ON "'.$msg['Date'].'"', SE_UID);
	$search_results = sizeof($moved_mail_search);
	if ( $search_results > 1 )
	{
		for($i=0; $i<$search_results; $i++)
		{
			$newheader = imap_fetchheader($mbox, $moved_mail_search[$i], FT_UID);
			$newhead = imap_rfc822_parse_headers($newheader);
			if ( $newhead->from[0]->mailbox.'@'.$newhead->from[0]->host === $msg['From'] && iconv_mime_decode($newhead->subject, 0, "utf-8") === $msg['Subject'] )
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

	// ermittle history-eintrag
	$res_history = q("SELECT id_mail_history FROM cms_mail_history WHERE account_id='".$_POST['account']."' AND folder_id=".$_POST['folder']." AND msg_uid='".$_POST['msg_num']."' LIMIT 1;", $dbweb, __FILE__, __LINE__);
	if ( mysqli_num_rows($res_history) == 1 )
	{
		$row_history = mysqli_fetch_assoc($res_history);
		$update_data = array();
		// erstelle artikel mit dem Text der Notiz
		$where = 'WHERE id_mail_history='.$row_history['id_mail_history'];
		$update_data['folder_id'] = $_POST['target'];
		$update_data['msg_uid'] = $new_msg_num;
		$update_data['locked'] = 0;
		$update_data['locked_by'] = 0;
		q_update('cms_mail_history', $update_data, $where, $dbweb, __FILE__, __LINE__);
	}
?>
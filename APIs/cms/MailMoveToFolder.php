<?php 
	mb_internal_encoding("UTF-8");
	
	check_man_params(array("account" => "numericNN", "msg_num" => "numericNN", "target" => "numericNN"));
	
	require_once("../../mapco_shop_de/functions/mail_connect.php");						

	$mbox = mail_connect($_POST['account']);

	$res = q("SELECT cms_mail_servers.server FROM cms_mail_accounts, cms_mail_servers WHERE id_account=".$_POST['target']." AND cms_mail_servers.id=cms_mail_accounts.server;", $dbweb, __FILE__, __LINE__);
	$row = mysqli_fetch_assoc($res);
	
	$mailbox = explode(".",$row['server']);
	$mailbox = 'INBOX.'.$mailbox[3];
	
	$mail_moved = imap_mail_move($mbox,$_POST['msg_num'],$mailbox, CP_UID);

	imap_expunge($mbox);
	
	imap_close($mbox);
?>
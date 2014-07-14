<?php 
	mb_internal_encoding("UTF-8");
	require_once("../../mapco_shop_de/functions/mail_connect.php");

	$res = q("SELECT cms_mail_servers.server FROM cms_mail_servers, cms_mail_accounts WHERE cms_mail_accounts.id_account=".$_POST['account']." AND cms_mail_servers.id=cms_mail_accounts.server;", $dbweb, __FILE__, __LINE__);
	$row = mysqli_fetch_assoc($res);

	$mbox = mail_connect($_POST['account'],$_POST['folder']);
	$mail_folders = imap_list($mbox, $row['server'], "*");
	imap_close($mbox);

	$res_folder = q("SELECT mailbox FROM cms_mail_accounts_folders WHERE account_id=".$_POST['account'].";",$dbweb, __FILE__, __LINE__);
	while ( $row_folder = mysqli_fetch_assoc($res_folder) )
	{
		$mailbox = $row['server'].$row_folder['mailbox'];
		$key = array_search($mailbox, $mail_folders);
		var_dump($key);
		if ( $folder_pos !== FALSE )
		{
			unset($mail_folders[$key]);
		}
	}

	if ( sizeof($mail_folders) > 0 )
	{
		foreach( $mail_folders as $folder )
		{
			$folder = str_replace($row['server'],"",$folder);
			$data = array();
			$data["account_id"] = $_POST['account'];
			$data["name"] = $folder;
			$data["mailbox"] = $folder;
			q_insert('cms_mail_accounts_folders', $data, $dbweb,  __FILE__, __LINE__);
		}
	}
?>
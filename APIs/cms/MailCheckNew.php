<?php 
	
	/*********************/
	/********SOA2*********/
	/*********************/
	
	mb_internal_encoding("UTF-8");
	
	require_once("../../mapco_shop_de/functions/mail_connect.php");						
	$new_msgs = 0;

	$res_accounts = q("SELECT account_id FROM cms_mail_accounts_users WHERE user_id=".$_SESSION['id_user'].";", $dbweb, __FILE__, __LINE__);
	while ( $row_accounts = mysqli_fetch_assoc($res_accounts) )
	{
		$res_folders = q("SELECT id_folder FROM cms_mail_accounts_folders WHERE account_id=".$row_accounts['account_id']." AND mailbox='INBOX';", $dbweb, __FILE__, __LINE__);
		$row_folders = mysqli_fetch_assoc($res_folders);
		
		$mbox = mail_connect($row_accounts['account_id'], $row_folders['id_folder']);
	
		$new_msgs = imap_search($mbox, 'NEW', SE_UID);
		if ( $new_msgs[0] != '' )
		{
			print "<folder_new_msgs>".$row_accounts['account_id']."</folder_new_msgs>\n";
		}
	}
	

?>
<?php 
	
	/*********************/
	/********SOA2*********/
	/*********************/
	
	mb_internal_encoding("UTF-8");
	
	check_man_params(array("account" => "numericNN", "folder" => "numericNN", "name" => "text", "mailbox" => "text"));
	
	require_once("../../mapco_shop_de/functions/mail_connect.php");						

	$res = q("SELECT cms_mail_servers.server FROM cms_mail_servers, cms_mail_accounts WHERE cms_mail_accounts.id_account=".$_POST['account']." AND cms_mail_servers.id=cms_mail_accounts.server;", $dbweb, __FILE__, __LINE__);
	$row = mysqli_fetch_assoc($res);
	
	$folder = 'INBOX.'.$_POST['mailbox'];
	$server = $row['server'].$folder;

	$mbox = mail_connect($_POST['account'], $_POST['folder']);
	
	$list = imap_list($mbox, $row['server'], "*");
	$folder_exist = 0;
	
	foreach ( $list as $mailbox )
	{
		if ( $mailbox == $server )
		{	
			$folder_exist = 1;
			break;
		}
	}		

	if ( $folder_exist == 0 )
	{
		$create_folder = imap_createmailbox($mbox, imap_utf7_encode( $server ));
	}
	else
	{
		print 'Ordner existiert bereits'; die();
	}

	$data = array();
	$data['account_id'] = $_POST['account'];
	$data['name'] = $_POST['name'];
	$data['mailbox'] = $folder;

	$res = q("SELECT COUNT(id_folder) as ordering FROM cms_mail_accounts_folders WHERE account_id=".$_POST['account'].";", $dbweb, __FILE__, __LINE__);
	$row = mysqli_fetch_assoc($res);
	$data['user_ordering'] = $row['ordering']+1;
	
	q_insert('cms_mail_accounts_folders',$data, $dbweb, __FILE__, __LINE__);
	
?>
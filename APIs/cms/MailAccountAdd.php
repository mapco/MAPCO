<?php
	mb_internal_encoding('utf-8');
	require_once("../../mapco_shop_de/functions/des_encryption.php");
	
	$required = array(
				"server" => "numericNN",
				"mailbox" => "textNN",
				"title" => "textNN"
				);
	check_man_params($required);

	$pass = trim(hexToString($_POST['password']));

	$pass = des('f798f38d1ffa27a6c790c0f6bb842f6c', $pass, 0, 0, NULL, NULL);

	// get server address
	$result = q("SELECT COUNT(ordering) AS ordering FROM cms_mail_accounts;", $dbweb, __FILE__, __LINE__);
	$row = mysqli_fetch_assoc($result);
	$ordering = $row['ordering'];

	// get server address
	$result = q("SELECT server FROM cms_mail_servers WHERE id=".$_POST['server'].";", $dbweb, __FILE__, __LINE__);
	$row = mysqli_fetch_assoc($result);
	
	$mbox = imap_open($row['server'].'INBOX', $_POST['mailbox'], $pass, NULL, 1) or die("can't connect: " . imap_last_error());	
	if ( $mbox != FALSE )
	{
		$data = array();
		$data['server'] = $_POST['server'];
		$data['title'] = $_POST['title'];
		if ( $_POST['postausgang'] == 1 )
		{
			$data['title'] .= ' Posteingang';
		}
		$data['user'] = $_POST['mailbox'];
		$data['password'] = $_POST['password'];
		$data['ordering'] = $ordering+1;
		$data['firstmod'] = time();
		$data['firstmod_user'] = $_SESSION['id_user'];
		$data['lastmod'] = $data['firstmod'];
		$data['lastmod_user'] = $_SESSION['id_user'];
		
		q_insert("cms_mail_accounts", $data, $dbweb, __FILE__, __LINE__);

		$result_account = q("SELECT id_account FROM cms_mail_accounts WHERE user='".$data['user']."' AND server='".$data['server']."' AND firstmod=".$data['firstmod']." AND firstmod_user=".$data['firstmod_user'].";", $dbweb, __FILE__, __LINE__);
		$row_account = mysqli_fetch_assoc($result_account);
		
		$data = array();
		$data['account_id'] = $row_account['id_account'];
		$data['name'] = "Posteingang";
		$data['mailbox'] = "INBOX";
		
		$res = q("SELECT COUNT(id_folder) as ordering FROM cms_mail_accounts_folders WHERE account_id=".$row_account['id_account'].";", $dbweb, __FILE__, __LINE__);
		$row = mysqli_fetch_assoc($res);
		$data['user_ordering'] = $row['ordering']+1;
		
		q_insert('cms_mail_accounts_folders',$data, $dbweb, __FILE__, __LINE__);
	}
	imap_close($mbox);
?>
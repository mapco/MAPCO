<?php
	mb_internal_encoding('utf-8');
	require_once("../../mapco_shop_de/functions/des_encryption.php");
	
	$required = array(
				"server" => "numericNN",
				"mailbox" => "textNN",
				"title" => "textNN",
				"postausgang" => "numericNN",
				"archiv" => "numericNN"
				);
	check_man_params($required);

	$pass = trim(hexToString($_POST['password']));

	$pass = des('f798f38d1ffa27a6c790c0f6bb842f6c', $pass, 0, 0, NULL, NULL);

	// get server address
	$result = q("SELECT server FROM cms_mail_servers WHERE id=".$_POST['server'].";", $dbweb, __FILE__, __LINE__);
	$row = mysqli_fetch_assoc($result);
	
	$mbox = imap_open($row['server'], $_POST['mailbox'], $pass, NULL, 1) or die("can't connect: " . imap_last_error());	
	imap_close($mbox);
	if ( $mbox != FALSE )
	{
		imap_close($mbox);
		
		$data = array();
		$data['server'] = $_POST['server'];
		$data['title'] = $_POST['title'];
		if ( $_POST['postausgang'] == 1 )
		{
			$data['title'] .= ' Posteingang';
		}
		$data['user'] = $_POST['mailbox'];
		$data['password'] = $_POST['password'];
		$data['firstmod'] = time();
		$data['firstmod_user'] = $_SESSION['id_user'];
		$data['lastmod'] = $data['firstmod'];
		$data['lastmod_user'] = $_SESSION['id_user'];
		
		q_insert("cms_mail_accounts", $data, $dbweb, __FILE__, __LINE__);
		
		if ( $_POST['archiv'] == 1 )
		{
			$data = array();
			$data['server'] = 2;
			$data['title'] = $_POST['title'].' Archiv';
			$data['user'] = $_POST['mailbox'];
			$data['password'] = $_POST['password'];
			$data['firstmod'] = time();
			$data['firstmod_user'] = $_SESSION['id_user'];
			$data['lastmod'] = $data['firstmod'];
			$data['lastmod_user'] = $_SESSION['id_user'];
			
			q_insert("cms_mail_accounts", $data, $dbweb, __FILE__, __LINE__);
		}
		
		if ( $_POST['postausgang'] == 1 )
		{
			$data = array();
			$data['server'] = 3;
			$data['title'] = $_POST['title'].' Postausgang';
			$data['user'] = $_POST['mailbox'];
			$data['password'] = $_POST['password'];
			$data['firstmod'] = time();
			$data['firstmod_user'] = $_SESSION['id_user'];
			$data['lastmod'] = $data['firstmod'];
			$data['lastmod_user'] = $_SESSION['id_user'];
			
			q_insert("cms_mail_accounts", $data, $dbweb, __FILE__, __LINE__);
		}
	}
?>
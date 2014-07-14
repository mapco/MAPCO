<?php 
	mb_internal_encoding("UTF-8");
	
	check_man_params(array("account" => "numericNN", "folder" => "numericNN"));
	
	require_once("../../mapco_shop_de/functions/mail_connect.php");			

	$mbox = mail_connect($_POST['account'], $_POST['folder']);

	$mail_moved = move_mail_to_archiv($mbox, $_POST['msg_num'], $_POST['account'], $_POST['folder']);
	
	imap_close($mbox);
?>
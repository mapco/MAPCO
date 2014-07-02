<?php 
	mb_internal_encoding("UTF-8");
	
	check_man_params(array("account" => "numericNN", "msg_num" => "numericNN"));
	
	require_once("../../mapco_shop_de/functions/mail_connect.php");						

	$mbox = mail_connect($_POST['account']);

	$mail_moved = move_mail_to_archiv($mbox, $_POST['msg_num']);
	
	imap_close($mbox);
?>
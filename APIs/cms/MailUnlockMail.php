<?php 
	mb_internal_encoding("UTF-8");
	require_once("../../mapco_shop_de/functions/mail_connect.php");

	check_man_params(array(
						"msg_num"	=> "numericNN",
						"account"	=> "numericNN"
						)
					);
	unlock_mail ( $_POST['msg_num'], $_POST['account'] );
?>
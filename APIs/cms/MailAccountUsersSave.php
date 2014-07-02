<?php

	$required = array(
				"account_id" => "numericNN",
				"user_ids" => "textNN"
				);
	check_man_params($required);

	$checked_users = explode(', ', $_POST['user_ids']);

	$cms_mail_accounts_users = array();

	$result = q("SELECT user_id FROM cms_mail_accounts_users WHERE account_id=".$_POST['account_id'].";", $dbweb, __FILE__, __LINE__);
	while ( $row = mysqli_fetch_assoc($result) )
	{
		$cms_mail_accounts_users[$row['user_id']] = $row['user_id'];
	}
	
	$data = array();
	$data['account_id'] = $_POST['account_id'];
	$data['firstmod'] = time();
	$data['firstmod_user'] = $_SESSION['id_user'];
	$data['lastmod'] = $data['firstmod'];
	$data['lastmod_user'] = $_SESSION['id_user'];
		
	$count_checked = sizeof($checked_users);
	
	for($x=0; $x<$count_checked; $x++)
	{
		if ( isset($cms_mail_accounts_users[$checked_users[$x]]) )
		{
			unset($cms_mail_accounts_users[$checked_users[$x]]);
			unset($checked_users[$x]);
		}
		else
		{
			$data['user_id'] = $checked_users[$x];
			q_insert("cms_mail_accounts_users", $data, $dbweb, __FILE__, __LINE__);
		}
	}
	
	if ( sizeof($cms_mail_accounts_users) != 0 )
	{
		$cms_mail_accounts_users = implode(',',$cms_mail_accounts_users);
		q("DELETE FROM cms_mail_accounts_users WHERE account_id=".$_POST['account_id']." AND user_id IN (".$cms_mail_accounts_users.");", $dbweb, __FILE__, __LINE__);
	}
?>
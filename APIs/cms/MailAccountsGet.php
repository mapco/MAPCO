<?php

	$required = array(
				"account_id" => "numericNN",
				);
	check_man_params($required);

	$users = array();

	$result = q("SELECT idCmsUser, firstname, lastname FROM cms_contacts WHERE active=1;", $dbweb, __FILE__, __LINE__);
	while ( $row = mysqli_fetch_assoc($result) )
	{
		$users[$row['idCmsUser']]['checked'] = 0;
		$users[$row['idCmsUser']]['firstname'] = $row['firstname'];
		$users[$row['idCmsUser']]['lastname'] = $row['lastname'];
	}
		
	$result = q("SELECT account_id, title, user FROM cms_mail_accounts, cms_mail_accounts_users WHERE account_id=".$_POST['account_id'].";", $dbweb, __FILE__, __LINE__);
	while ( $row = mysqli_fetch_assoc($result) )
	{
		$users[$row['user_id']]['checked'] = 1;
	}
	
	foreach ( $users as $id => $user )
	{
		print "<mail_account_user>\n";
		print "	<user_id>".$id."</user_id>\n";
		foreach ( $user as $key => $value )
		{
			print "	<".$key.">".$value."</".$key.">\n";
		}
		print "</mail_account_user>\n";
	}
	
?>
<?php

	$required = array(
				"account_id" => "numericNN",
				);
	check_man_params($required);

	$users = array();

/* Get users from cms_contacts
	$result = q("SELECT idCmsUser, firstname, lastname FROM cms_contacts WHERE active=1;", $dbweb, __FILE__, __LINE__);
	while ( $row = mysqli_fetch_assoc($result) )
	{
		$users[$row['idCmsUser']]['checked'] = 0;
		$users[$row['idCmsUser']]['firstname'] = $row['firstname'];
		$users[$row['idCmsUser']]['lastname'] = $row['lastname'];
	}
*/

	$result = q("SELECT idCmsUser, firstname, lastname, department_id, department FROM cms_contacts, cms_contacts_departments WHERE department_id NOT IN (14,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,31,32,33,34,35,36,39,40) AND id_department=department_id AND active=1;", $dbweb, __FILE__, __LINE__);
	while ( $row = mysqli_fetch_assoc($result) )
	{
		$users[$row['department_id']][$row['idCmsUser']]['checked'] = 0;
		$users[$row['department_id']][$row['idCmsUser']]['firstname'] = $row['firstname'];
		$users[$row['department_id']][$row['idCmsUser']]['lastname'] = $row['lastname'];
		
		$departments[$row['department_id']] = $row['department'];
	}
		
	$result = q("SELECT user_id FROM cms_mail_accounts_users WHERE account_id=".$_POST['account_id'].";", $dbweb, __FILE__, __LINE__);
	while ( $row = mysqli_fetch_assoc($result) )
	{
		$checked_users[] = $row['user_id'];
	}

	foreach ( $departments as $id => $title )
	{
		print "<departments>\n";
		print "	<department_id>".$id."</department_id>\n";
		print "	<department_title>".$title."</department_title>\n";
		print "</departments>\n";
	}
	
	foreach ( $users as $department_id => $department )	
	{
		print "<department>\n";
		print "	<department_id>".$department_id."</department_id>\n";
		foreach ( $department as $user_id => $user )
		{
			$checked = 0;
			if ( in_array($user_id,$checked_users) )
			{
				$checked = 1;
			}
			print "<mail_account_user>\n";
			print "	<user_id>".$user_id."</user_id>\n";
			print "	<checked>".$checked."</checked>\n";
			print "	<firstname>".$user['firstname']."</firstname>\n";
			print "	<lastname>".$user['lastname']."</lastname>\n";
			print "</mail_account_user>\n";
		}
		print "</department>\n";
	}
?>
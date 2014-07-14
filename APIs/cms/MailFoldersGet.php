<?php 
	
	/*********************/
	/********SOA2*********/
	/*********************/
	
	mb_internal_encoding("UTF-8");
	
	check_man_params(array("account" => "numericNN"));
	
	require_once("../../mapco_shop_de/functions/mail_connect.php");						
	$xml = '';

	$res = q("SELECT id_folder, name FROM cms_mail_accounts_folders WHERE account_id=".$_POST['account']." ORDER BY user_ordering ASC;", $dbweb, __FILE__, __LINE__);
	while ( $row = mysqli_fetch_assoc($res) )
	{
		$mbox = mail_connect($_POST['account'], $row['id_folder']);
		$mc = imap_check($mbox);
	
		$new_msgs = imap_search($mbox, 'UNSEEN', SE_UID);
		if ( $new_msgs[0] != '' )
		{
			$new_msgs = sizeof($new_msgs);
		}
		else
		{
			$new_msgs = 0;
		}	
		
		$xml .= "<account_folder>\n";
		$xml .= "	<folder_id>".$row['id_folder']."</folder_id>\n";
		$xml .= "	<folder_name><![CDATA[".$row['name']."]]></folder_name>\n";
		$xml .= "	<folder_new_msgs>".$new_msgs."</folder_new_msgs>\n";
		$xml .= "	<folder_Nmsgs>".$mc->Nmsgs."</folder_Nmsgs>\n";
		$xml .= "</account_folder>\n";
	}
	
	print $xml;
?>
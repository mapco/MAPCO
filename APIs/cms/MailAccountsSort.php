<?php
	/*************************
	********** SOA 2 *********
	*************************/	

	if($_POST['list'][0] != '')
	{
		for($i=0; $i<sizeof($_POST["list"]); $i++)
		{
			if ( $_POST['type'] === 'accounts' )
			{
				$id=str_replace("account_", "", $_POST["list"][$i]);
	
				q("UPDATE cms_mail_accounts_users SET user_ordering=".($i+1)." WHERE user_id=".$_SESSION['id_user']." AND account_id=".$id.";", $dbweb, __FILE__, __LINE__);
			}
			elseif ( $_POST['type'] === 'folders' )
			{
				$id=str_replace("folder_", "", $_POST["list"][$i]);
	
				q("UPDATE cms_mail_accounts_folders SET user_ordering=".($i+1)." WHERE id_folder=".$id.";", $dbweb, __FILE__, __LINE__);				
			}
		}
	}
	
?>
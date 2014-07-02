<?php

	//ACCOUNT REMOVE
	if ( $_POST["action"]=="account_remove" )
	{
		if ( !($_POST["id_account"]>0) ) echo 'Es konnte keine ID für den Account gefunden werden.';
		else
		{
			q("DELETE FROM ebay_accounts WHERE id_account=".$_POST["id_account"].";", $dbshop, __FILE__, __LINE__);
		}
	}

?>
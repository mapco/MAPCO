<?php

	//ACCOUNT SORT
	if ( $_POST["action"]=="account_sort" )
	{
		for($i=1; $i<sizeof($_POST["list"]); $i++)
		{
			q("UPDATE ebay_accounts SET ordering=".$i." WHERE id_account=".$_POST["list"][$i].";", $dbshop, __FILE__, __LINE__);
		}
	}

?>
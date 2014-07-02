<?php

	//ACCOUNT ADD
	if ( $_POST["action"]=="account_add" )
	{
		if ( $_POST["title"]=="" ) echo 'Der Titel darf nicht leer sein.';
		else
		{
			//get ordering
			$results=q("SELECT * FROM ebay_accounts;", $dbshop, __FILE__, __LINE__);
			$ordering=mysqli_num_rows($results)+1;
			
			q("INSERT INTO ebay_accounts (title, description, production, devID, devID_sandbox, appID, appID_sandbox, certID, certID_sandbox, token, token_sandbox, DispatchTimeMax, PaymentMethods, PayPalEmailAddress, PostalCode, pricelist, id_imageformat, ordering, firstmod, firstmod_user, lastmod, lastmod_user) VALUES('".addslashes(stripslashes($_POST["title"]))."', '".addslashes(stripslashes($_POST["description"]))."', '".addslashes(stripslashes($_POST["production"]))."', '".addslashes(stripslashes($_POST["devID"]))."', '".addslashes(stripslashes($_POST["devID_sandbox"]))."', '".addslashes(stripslashes($_POST["appID"]))."', '".addslashes(stripslashes($_POST["appID_sandbox"]))."', '".addslashes(stripslashes($_POST["certID"]))."', '".addslashes(stripslashes($_POST["certID_sandbox"]))."', '".addslashes(stripslashes($_POST["token"]))."', '".addslashes(stripslashes($_POST["token_sandbox"]))."', '".addslashes(stripslashes($_POST["DispatchTimeMax"]))."', '".addslashes(stripslashes($_POST["PaymentMethods"]))."', '".addslashes(stripslashes($_POST["PayPalEmailAddress"]))."', '".addslashes(stripslashes($_POST["PostalCode"]))."', '".addslashes(stripslashes($_POST["pricelist"]))."', '".addslashes(stripslashes($_POST["id_imageformat"]))."', ".$ordering.", ".time().", ".$_SESSION["id_user"].", ".time().", ".$_SESSION["id_user"].");", $dbshop, __FILE__, __LINE__);
		}
	}

?>
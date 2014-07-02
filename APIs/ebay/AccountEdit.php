<?php

	if( !isset($_POST["id_account"]) )
	{
		echo '<AccountEditResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Account nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss ein Ebay-Account (id_account) angegeben werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</AccountEditResponse>'."\n";
		exit;
	}

	$data=array();
	if( isset($_POST["title"]) ) $data["title"]=$_POST["title"];
	if( isset($_POST["description"]) ) $data["description"]=$_POST["description"];
	if( isset($_POST["production"]) ) $data["production"]=$_POST["production"];
	if( isset($_POST["devID"]) ) $data["devID"]=$_POST["devID"];
	if( isset($_POST["devID_sandbox"]) ) $data["devID_sandbox"]=$_POST["devID_sandbox"];
	if( isset($_POST["appID"]) ) $data["appID"]=$_POST["appID"];
	if( isset($_POST["appID_sandbox"]) ) $data["appID_sandbox"]=$_POST["appID_sandbox"];
	if( isset($_POST["certID"]) ) $data["certID"]=$_POST["certID"];
	if( isset($_POST["certID_sandbox"]) ) $data["certID_sandbox"]=$_POST["certID_sandbox"];
	if( isset($_POST["token"]) ) $data["token"]=$_POST["token"];
	if( isset($_POST["token_sandbox"]) ) $data["token_sandbox"]=$_POST["token_sandbox"];
	if( isset($_POST["DispatchTimeMax"]) ) $data["DispatchTimeMax"]=$_POST["DispatchTimeMax"];
	if( isset($_POST["PaymentMethods"]) ) $data["PaymentMethods"]=$_POST["PaymentMethods"];
	if( isset($_POST["PayPalEmailAddress"]) ) $data["PayPalEmailAddress"]=$_POST["PayPalEmailAddress"];
	if( isset($_POST["PostalCode"]) ) $data["PostalCode"]=$_POST["PostalCode"];
	if( isset($_POST["ReturnsAcceptedOption"]) ) $data["ReturnsAcceptedOption"]=$_POST["ReturnsAcceptedOption"];
	if( isset($_POST["ReturnsWithinOption"]) ) $data["ReturnsWithinOption"]=$_POST["ReturnsWithinOption"];
	if( isset($_POST["ShippingCostPaidByOption"]) ) $data["ShippingCostPaidByOption"]=$_POST["ShippingCostPaidByOption"];
	if( isset($_POST["pricelist"]) ) $data["pricelist"]=$_POST["pricelist"];
	if( isset($_POST["id_imageformat"]) ) $data["id_imageformat"]=$_POST["id_imageformat"];
	$data["lastmod"]=time();
	$data["lastmod_user"]=$_SESSION["id_user"];
	
	$where="WHERE id_account=".$_POST["id_account"].";";
	q_update("ebay_accounts", $data, $where, $dbshop, __FILE__, __LINE__);

	echo '<AccountEditResponse>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	echo '</AccountEditResponse>'."\n";
/*
	//ACCOUNT EDIT
	if ( $_POST["action"]=="account_edit" )
	{
		if ( !($_POST["id_account"]>0) ) echo 'Es konnte keine ID für den Account gefunden werden.';
		elseif ( $_POST["title"]=="" ) echo 'Der Titel darf nicht leer sein.';
		else
		{
			q("UPDATE ebay_accounts
			   SET title='".addslashes(stripslashes($_POST["title"]))."',
			       description='".addslashes(stripslashes($_POST["description"]))."',
			       production='".addslashes(stripslashes($_POST["production"]))."',
			       devID='".addslashes(stripslashes($_POST["devID"]))."',
			       devID_sandbox='".addslashes(stripslashes($_POST["devID_sandbox"]))."',
			       appID='".addslashes(stripslashes($_POST["appID"]))."',
			       appID_sandbox='".addslashes(stripslashes($_POST["appID_sandbox"]))."',
			       certID='".addslashes(stripslashes($_POST["certID"]))."',
			       certID_sandbox='".addslashes(stripslashes($_POST["certID_sandbox"]))."',
			       token='".addslashes(stripslashes($_POST["token"]))."',
			       token_sandbox='".addslashes(stripslashes($_POST["token_sandbox"]))."',
			       DispatchTimeMax='".addslashes(stripslashes($_POST["DispatchTimeMax"]))."',
			       PaymentMethods='".addslashes(stripslashes($_POST["PaymentMethods"]))."',
			       PayPalEmailAddress='".addslashes(stripslashes($_POST["PayPalEmailAddress"]))."',
			       PostalCode='".addslashes(stripslashes($_POST["PostalCode"]))."',
			       pricelist='".addslashes(stripslashes($_POST["pricelist"]))."',
			       id_imageformat='".addslashes(stripslashes($_POST["id_imageformat"]))."',
			       lastmod=".time().",
			       lastmod_user=".$_SESSION["id_user"]."
			   WHERE id_account=".$_POST["id_account"].";", $dbshop, __FILE__, __LINE__);
		}
	}
*/
?>
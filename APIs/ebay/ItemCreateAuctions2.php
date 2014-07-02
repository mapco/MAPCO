<?php

	$response="";
	$results=q("SELECT * FROM ebay_accounts WHERE active>0;", $dbshop, __FILE__, __LINE__);
	while( $account=mysqli_fetch_array($results) )
	{
		$results2=q("SELECT * FROM ebay_accounts_sites WHERE active>0;", $dbshop, __FILE__, __LINE__);
		while( $accountsite=mysqli_fetch_array($results2) )
		{
			$fieldset=array();
			$fieldset["API"]="ebay";
			$fieldset["Action"]="ItemCreateAuctions";
			$fieldset["id_item"]=$_POST["id_item"];
			$fieldset["id_account"]=$account["id_account"];
			$fieldset["id_accountsite"]=$accountsite["id_accountsite"];
			$response .= post(PATH."soa/", $fieldset);
		}
	}
	
	echo '<ItemsCreateAuctionsResponse>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	echo '	<Response><![CDATA['.$response.']]></Response>'."\n";
	echo '</ItemsCreateAuctionsResponse>'."\n";
	
?>
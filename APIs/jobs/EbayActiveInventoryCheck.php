<?php

	$zipfile="test.zip";
	$zip = new ZipArchive;
	$res = $zip->open($zipfile);
	$filename = $zip->getNameIndex(0);
	$zip->extractTo('.');
	$zip->close();
	
	$use_errors = libxml_use_internal_errors(true);
	try
	{
		$response = simplexml_load_file($filename);
	}
	catch(Exception $e)
	{
		echo '<AddItemResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Bild hochladen fehlgeschlagen.</shortMsg>'."\n";
		echo '		<longMsg>Beim Hochladen eines Bildes ist ein Fehler aufgetreten.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</AddItemResponse>'."\n";
		exit;
	}
	libxml_clear_errors();
	libxml_use_internal_errors($use_errors);

/*
	$auction=array();
	$results=q("SELECT ItemID FROM ebay_auctions WHERE account_id=1;", $dbshop, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		$auction[$row["ItemID"]]=0;
	}
*/
	
	count($response->ActiveInventoryReport[0]->SKUDetails);
	$query = "INSERT INTO ebay_jobs_ActiveInventory (account_id, SKU, Price, Quantity, ItemID) VALUES";
	$j=0;
	for($i=0; $i<count($response->ActiveInventoryReport[0]->SKUDetails); $i++)
//	for($i=0; $i<5000; $i++)
	{
		$SKU=$response->ActiveInventoryReport[0]->SKUDetails[$i]->SKU;
		$Price=$response->ActiveInventoryReport[0]->SKUDetails[$i]->Price;
		$Quantity=$response->ActiveInventoryReport[0]->SKUDetails[$i]->Quantity;
		$ItemID=$response->ActiveInventoryReport[0]->SKUDetails[$i]->ItemID;
		
		if( $j>0 ) $query .= ", ";
		$query .= "(".$_POST["id_account"].", '".$SKU."', '".$Price."', ".$Quantity.", ".$ItemID.")";
		$j++;
		
		if($j==99)
		{
			$query .= ";";
			q($query, $dbshop, __FILE__, __LINE__);
			$query = "INSERT INTO ebay_jobs_ActiveInventory (account_id, SKU, Price, Quantity, ItemID) VALUES";
			$j=0;
		}
	}
		

?>
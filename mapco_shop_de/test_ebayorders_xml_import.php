<?php
	include("config.php");

	if (file_exists("soa/ebay_export_test.xml")) 
	{

		$use_errors = libxml_use_internal_errors(true);
		try
		{

	    	$xml = simplexml_load_file("soa/ebay_export_test.xml");
			
		}
		catch(Exception $e)
		{
			error__log(4,1,__FILE__, __LINE__, "Beim Einlesen der EbayOrderExport Datai ist ein XML-Fehler aufgetreten.");	
			exit;
		}
		if( !isset($xml->SoldReport[0]->OrderDetails) || sizeof($xml->SoldReport[0]->OrderDetails)==0 )
		{
			error__log(4,2,__FILE__, __LINE__, "Die EbayOrderExport Datai enthält keine Datensätze.");	
			exit;
		}

		
		//ANZAHL ORDERS
		echo $size = sizeof($xml->SoldReport[0]->OrderDetails)."<br />";
		
		$res=q("TRUNCATE TABLE ebay_orders_import;", $dbshop, __FILE__, __LINE__);
		echo "Gelöschte Datensätze: ".mysqli_affected_rows($dbshop);
	
	//	for($i=0; $i<$size; $i++)
		for($i=0; $i<$size; $i++)
		{

		
		//	print_r($xml->SoldReport[0]->OrderDetails[$i])."< br/>";
			$doc = new DOMDocument('1.0');
			$doc->formatOutput = true;
			$domnode = dom_import_simplexml($xml->SoldReport[0]->OrderDetails[$i]);
			// Import node into current document
			$domnode = $doc->importNode($domnode, true);
			// Add new child at the end of the children
			$domnode = $doc->appendChild($domnode);
			// Dump the internal XML tree back into a string
			$saveXml = $doc->saveXML();
			
			q("INSERT INTO ebay_orders_import (order_xml, ebay_account, lastmod, updated) VALUES ('".mysqli_real_escape_string($dbshop, $saveXml)."', 1, ".time().", 0);", $dbshop, __FILE__, __LINE__);
		}
		
		
	}


?>
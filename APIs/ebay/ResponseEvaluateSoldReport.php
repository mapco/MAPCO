<?php
	$starttime=time()+microtime();

	if( isset($_POST["id_job"]) and $_POST["id_job"]>0 )
	{
		$results=q("SELECT * FROM ebay_jobs WHERE id_job=".$_POST["id_job"].";", $dbshop, __FILE__, __LINE__);
		if( mysqli_num_rows($results)==0 )
		{
			echo '<ResponseEvaluateActiveInventoryReportResponse>'."\n";
			echo '	<Ack>Failure</Ack>'."\n";
			echo '	<Error>'."\n";
			echo '		<Code>'.__LINE__.'</Code>'."\n";
			echo '		<shortMsg>Job nicht gefunden.</shortMsg>'."\n";
			echo '		<longMsg>Der angegebene Job konnte nicht gefunden werden.</longMsg>'."\n";
			echo '	</Error>'."\n";
			echo '	<Response>'.$responseXml.'</Response>'."\n";
			echo '</ResponseEvaluateActiveInventoryReportResponse>'."\n";
			exit;
		}
		$job=mysqli_fetch_array($results);

		//download response file if not temporary available
		if( $job["fileReferenceId_tempfile"]=="" or ($job["fileReferenceId_tempfiletimestamp"]+24*3600)<time() )
		{
			$fieldset=array();
			$fieldset["API"]="ebay_lms";
			$fieldset["Action"]="downloadFile";
			$fieldset["id_account"]=$job["account_id"];
			$fieldset["fileReferenceId"]=$job["fileReferenceId"];
			$fieldset["taskReferenceId"]=$job["jobId"];
		
			$responseXml = post(PATH."soa/", $fieldset);
			$use_errors = libxml_use_internal_errors(true);
			try
			{
				$response = new SimpleXMLElement($responseXml);
			}
			catch(Exception $e)
			{
				echo '<ResponseEvaluateActiveInventoryReportResponse>'."\n";
				echo '	<Ack>Failure</Ack>'."\n";
				echo '	<Error>'."\n";
				echo '		<Code>'.__LINE__.'</Code>'."\n";
				echo '		<shortMsg>XML nicht valide.</shortMsg>'."\n";
				echo '		<longMsg>Beim Auswerten der XML-Daten trat ein Fehler auf.</longMsg>'."\n";
				echo '	</Error>'."\n";
				echo '	<PostFields>'.print_r($_POST, true).'</PostFields>'."\n";
				echo '	<Response>'.$responseXml.'</Response>'."\n";
				echo '</ResponseEvaluateActiveInventoryReportResponse>'."\n";
				exit;
			}
			libxml_clear_errors();
			libxml_use_internal_errors($use_errors);
			$filename=(string)$response->File[0];
			
			$data=array();
			$data["fileReferenceId_tempfile"]=$filename;
			$data["fileReferenceId_tempfiletimestamp"]=time()-5;
			q_update("ebay_jobs", $data, "WHERE id_job=".$_POST["id_job"], $dbshop, __FILE__, __LINE__);
		}
		else
		{
			$filename="../../mapco_shop_de/temp/".$job["fileReferenceId_tempfile"];
		}
	}

	//cache ebay_sites
	$site=array();
	$results=q("SELECT * FROM ebay_accounts_sites WHERE account_id=".$job["account_id"].";", $dbshop, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		$site[$row["SiteID"]]=$row["id_accountsite"];
	}

	//remember new items for return values
	$new=array();

	//compare XML with known auctions
	$z = new XMLReader;
	$z->open($filename);
	$i=0;
	
	// move to the first <SKUDetails /> node
	while ($z->read() && $z->name !== 'OrderDetails');
	
	// now that we're at the right depth, hop to the next <SKUDetails/> until the end of the tree
	while ($z->name === 'OrderDetails')
	{
		$node = new SimpleXMLElement($z->readOuterXML());
		$id=(int)$node->ItemID[0];

		//get data
		$OrderID=(string)$node->OrderID[0];
		$CheckoutSiteID=(int)$node->CheckoutSiteID[0];
		foreach( $node->OrderItemDetails[0]->OrderLineItem as $item )
		{
			$TransactionID=(string)$item->OrderLineItemID[0];
			$TransactionID=substr($TransactionID, strpos($TransactionID, "-")+1);
			
			$results=q("SELECT * FROM ebay_orders_items WHERE TransactionID='".$TransactionID."' LIMIT 1;", $dbshop, __FILE__, __LINE__);
			if( mysqli_num_rows($results)>0 )
			{
//				echo 'update';
	//			exit;
			}
			else
			{
				echo $TransactionID;
				echo 'insert';
			}
		}
/*
		$data=array();
		$data["accountsite_id"]=$site[$CheckoutSiteID];
		q_update("ebay_orders", $data, "WHERE OrderID='".$OrderID."';", $dbshop, __FILE__, __LINE__);
*/
		// go to next <SKUDetails />
		$z->next('OrderDetails');
		$i++;
	}

	echo 'fertig';
	exit;

	//später dann mal
/*
UPDATE table SET Col1 = CASE id 
                          WHEN 1 THEN 1 
                          WHEN 2 THEN 2 
                          WHEN 4 THEN 10 
                          ELSE Col1 
                        END, 
                 Col2 = CASE id 
                          WHEN 3 THEN 3 
                          WHEN 4 THEN 12 
                          ELSE Col2 
                        END
             WHERE id IN (1, 2, 3, 4);
*/

	//set evaluated when end of file
	if ( isset($job) )
	{
		$data=array();
		$data["processed_lines"]=$i;
		$data["evaluated"]=1;
		q_update("ebay_jobs", $data, "WHERE id_job=".$_POST["id_job"].";", $dbshop, __FILE__, __LINE__);
	}

	//return success
	$stoptime=time()+microtime();
	$duration=$stoptime-$starttime;
	echo '<ResponseEvaluateActiveInventoryReportResponse>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	echo '	<JobID>'.$job["id_job"].'</JobID>'."\n";
	echo '	<Duration>'.$duration.'</Duration>'."\n";
	for($i=0; $i<sizeof($new); $i++)
	{
		echo '	<ItemID>'.$new[$i].'</ItemID>'."\n";
	}
	echo '</ResponseEvaluateActiveInventoryReportResponse>'."\n";

?>
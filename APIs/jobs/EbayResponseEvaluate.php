<?php

	$starttime = time()+microtime();

	//determine necessary account updates
	$accounts=array();
	$results=q("SELECT account_id FROM ebay_jobs WHERE jobStatus='Send' OR jobType='Scheduled' GROUP BY account_id;", $dbshop, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		$accounts[]=$row["account_id"];
	}

	//refresh ebay_jobs table for necessary accounts
	if( sizeof($accounts)>0 )
	{
		$results=q("SELECT * FROM ebay_accounts WHERE id_account IN (".implode(", ", $accounts).");", $dbshop, __FILE__, __LINE__);
		while( $account=mysqli_fetch_array($results) )
		{
			$fieldset=array();
			$fieldset["API"]="ebay_lms";
			$fieldset["Action"]="getJobs";
			$fieldset["id_account"]=$account["id_account"];
			echo $responseXml=post(PATH."soa/", $fieldset);
			$stoptime=time()+microtime();
			if( $stoptime-$starttime > 60 ) exit;
		}
	}

	//evaluate jobs
	$results=q("SELECT * FROM ebay_jobs WHERE (jobType='AddItem' OR jobType='ReviseItem' OR jobType='EndItem' OR jobType='ActiveInventoryReport') AND jobStatus='Completed' AND evaluated=0 ORDER BY firstmod;", $dbshop, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		$fieldset=array();
		$fieldset["API"]="ebay";
		$fieldset["Action"]="ResponseEvaluate".$row["jobType"];
		$fieldset["id_job"]=$row["id_job"];
		echo $responseXml=post(PATH."soa/", $fieldset);
		
		$stoptime=time()+microtime();
		if( $stoptime-$starttime > 60 ) exit;
	}
	
?>
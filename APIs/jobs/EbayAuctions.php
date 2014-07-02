<?php
	$starttime=time()+microtime();

	//get active accountsites
	$accountsite=array();
	$results=q("SELECT * FROM ebay_accounts_sites WHERE active>0;", $dbshop, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		$accountsite[]=$row;
	}

	//0. GET JOBS IF 24H AGO
	for($i=0; $i<sizeof($accountsite); $i++)
	{
		$results=q("SELECT * FROM ebay_jobs WHERE jobType='ActiveInventoryReport' AND account_id=".$accountsite[$i]["account_id"]." AND firstmod>".(time()-24*3600).";", $dbshop, __FILE__, __LINE__);
		if( mysqli_num_rows($results)==0 )
		{
			post(PATH."soa/", array("API" => "ebay_lms", "Action" => "getJobs", "id_account" => $accountsite[$i]["account_id"]));
			echo '<JobsGet id_account="'.$accountsite[$i]["account_id"].'"></JobsGet>'."\n";
			//exit if times over
			$stoptime=time()+microtime();
			if( $stoptime-$starttime > 60 ) exit;
		}
	}


	//1. UPDATE JOB STATUSES
	$results=q("SELECT * FROM ebay_jobs WHERE (jobType='AddItem' OR jobType='ReviseItem' OR jobType='EndItem' OR jobType='ActiveInventoryReport') AND NOT jobStatus='Completed' AND NOT jobStatus='Aborted' AND NOT jobStatus='Failed' ORDER BY firstmod;", $dbshop, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		$postdata=array();
		$postdata["API"]="ebay_lms";
		$postdata["Action"]="getJobStatus";
		$postdata["id_job"]=$row["id_job"];
		$responseXml=post(PATH."soa/", $postdata);
		if( strpos($responseXml, "<Ack>Success</Ack>") !== false )
		{
			echo '<JobStatusUpdated id_job="'.$row["id_job"].'" jobType="'.$row["jobType"].'">Success</JobStatusUpdated>'."\n";
		}
		else
		{
			echo '<JobStatusUpdated id_job="'.$row["id_job"].'" jobType="'.$row["jobType"].'">'."\n";
			echo '	<![CDATA['.$responseXml.']]>'."\n";
			echo '</JobStatusUpdated>'."\n";
		}

		//exit if times over
		$stoptime=time()+microtime();
		if( $stoptime-$starttime > 60 ) exit;
	}


	//2. EVALUATE JOBS
	$results=q("SELECT * FROM ebay_jobs WHERE (jobType='AddItem' OR jobType='ReviseItem' OR jobType='EndItem' OR jobType='ActiveInventoryReport') AND jobStatus='Completed' AND evaluated=0 ORDER BY firstmod;", $dbshop, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		$fieldset=array();
		$fieldset["API"]="ebay";
		$fieldset["Action"]="ResponseEvaluate".$row["jobType"];
		$fieldset["id_job"]=$row["id_job"];
		$responseXml=post(PATH."soa/", $fieldset);
		if( strpos($responseXml, "<Ack>Success</Ack>") !== false )
		{
			echo '<Evaluated id_job="'.$row["id_job"].'" jobType="'.$row["jobType"].'">Success</Evaluated>'."\n";
		}
		else
		{
			echo '<Evaluated id_job="'.$row["id_job"].'" jobType="'.$row["jobType"].'">'."\n";
			echo '	<![CDATA['.$responseXml.']]>'."\n";
			echo '</Evaluated>'."\n";
		}
		//exit if times over
		$stoptime=time()+microtime();
		if( $stoptime-$starttime > 60 ) exit;
	}


	//3. UPLOAD JOBS
	$fieldset=array();
	$fieldset["API"]="jobs";
	$fieldset["Action"]="EbayAuctionsUpload";
	$responseXml=post(PATH."soa/", $fieldset);
	echo '<EbayAuctionsResponse>';
	echo '	<Ack>Success</Ack>';
	echo '	<Job>Upload</Job>';
	echo '	<Response><![CDATA['.$responseXml.']]></Response>';
	echo '</EbayAuctionsResponse>';
	//exit if times over
	$stoptime=time()+microtime();
//	if( $stoptime-$starttime > 45 ) exit;

	
	//4. CREATE AND UPDATE AUCTIONS
	$fieldset=array();
	$fieldset["API"]="jobs";
	$fieldset["Action"]="EbayAuctionsUpdate";
	$fieldset["timelimit"]=60-($stoptime-$starttime);
	$responseXml=post(PATH."soa/", $fieldset);
	echo '<EbayAuctionsResponse>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	echo '	<Job>Update</Job>'."\n";
	echo '	<Response><![CDATA['.$responseXml.']]></Response>'."\n";
	echo '</EbayAuctionsResponse>'."\n";
	
?>
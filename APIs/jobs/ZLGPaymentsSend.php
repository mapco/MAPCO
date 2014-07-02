<?php

	$start = time();
	//CHECK IF THERE ARE ANY SENDABLE ENTRIES
//	$res_zlg_msg = q("SELECT * FROM idims_zlg_log WHERE response_time = 0 AND shop_id IN (1,2,3,4) AND difference>-0.02 AND difference<0.02 ORDER BY creation_time", $dbshop, __FILE__, __LINE__);
	$res_zlg_msg = q("SELECT * FROM idims_zlg_log WHERE response_time = 0 AND shop_id IN (1,2,3,4) AND difference=0 ORDER BY creation_time", $dbshop, __FILE__, __LINE__);

	$rowcount = mysqli_num_rows($res_zlg_msg);
	
	$postfield = array();
	$postfield["API"] = "idims";
	$postfield["APIRequest"] = "ZLGPaymentSend";
	$postfield["quantity"] = 10;

	$jobresponse = '';

	while ($rowcount >0 && (time()-$start)<60)
//	while ($rowcount >0 && (time()-$start) < 10)
	{
		$jobresponse.=post(PATH."soa2/", $postfield);
		$res_zlg_msg = q("SELECT * FROM idims_zlg_log WHERE response_time = 0 AND shop_id IN (1,2,3,4) AND difference=0 ORDER BY creation_time", $dbshop, __FILE__, __LINE__);		$rowcount = mysqli_num_rows($res_zlg_msg);
		$rowcount = mysqli_num_rows($res_zlg_msg);

	}
	
	echo $jobresponse;
	
?>
	
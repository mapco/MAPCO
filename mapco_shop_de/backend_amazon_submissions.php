<?php
	include("config.php");
	include("templates/".TEMPLATE_BACKEND."/header.php");
?>
	<style type="text/css">
	.ui-dialog .ui-dialog-buttonpane .ui-dialog-buttonset {
	   float: none;
	}
	
	.ui-dialog .ui-dialog-buttonpane {
		 text-align: center; /* left/center/right */
	}
    </style>

	<script type="text/javascript">
		function GetFeedSubmissionListResult(id)
		{
			var $post_data = new Object();
			$post_data['API'] = "amazon";
			$post_data['APIRequest'] = "AmazonSubmit";
			$post_data['id_account'] = 1;
			$post_data['action'] = "Action=GetFeedSubmissionResult&FeedSubmissionId=" + id;
			$.post('<?php echo PATH;?>soa2/', $post_data, function($data) {
					show_status2($data);
				});
		}
	
		function feed_cancel(id)
		{
			$.post("modules/backend_amazon_submissions_actions.php", { action:"feed_cancel", id:id },
			   function(data)
			   {
				   if (data!="") {
					   alert(data);
				   } else {
					   show_status("Übertragung erfolgreich abgebrochen.");
				   }
			   }
			);
		}
		
		function feed_status(id)
		{
			$.post("modules/backend_amazon_submissions_actions.php", { action:"feed_status", id:id },
				   function(data)
				   {
					   show_status2(data);
					   return;
						$("#feed_status_dialog").html(data);
						$("#feed_status_dialog").dialog
						({	buttons:
							[
								{ text: "OK", click: function() { $(this).dialog("close"); } }
							],
							closeText:"Fenster schließen",
							hide: { effect: 'drop', direction: "up" },
							modal:true,
							resizable:false,
							show: { effect: 'drop', direction: "up" },
							title:"Übertragungsstatus",
							width:200
						});
				   }
			);
		}
		
	</script>
<?php
	echo '<div id="breadcrumbs" class="breadcrumbs">';
	echo '<a href="backend_index.php">Backend</a>';
	echo ' &#187; <a href="backend_amazon_index.php">Amazon</a>';
	echo ' &#187; Übertragungen';
	echo '</div>';
	echo '<h1>Übertragungen</h1>';	

	function amazon_sendrequest($request, $datapost=array())
	{
		$method = "GET";
		$uri = "/";

		$AWSAccessKeyId = "AKIAIVV6BQ6NVVWUWEUA";
		$Marketplace = "A1PA6795UKMFR9";
		$Merchant = "A3UOJO2H7UZY88";
		$SecretKey = "B8k51dOOQFeWoaAmdcvTrOVEb7AyFvQJ0XlzEpMe";
		$host = "mws.amazonservices.de";

		$request .= "&AWSAccessKeyId=$AWSAccessKeyId&Marketplace=$Marketplace&Merchant=$Merchant&Timestamp=".gmdate("Y-m-d\TH:i:s\Z")."&Version=2009-01-01&SignatureVersion=2&SignatureMethod=HmacSHA256";

		// Clean up and sort
		$request = explode('&',$request);
		
		foreach ($request as $key => $value)
		{
			$t = explode("=",$value);
			$params[$t[0]] = $t[1];
		}
		unset($request);

		ksort($params);
		foreach ($params as $param=>$value)
		{
			$param = str_replace("%7E", "~", rawurlencode($param));
			if ($param != "NextToken")
				$value = str_replace("%7E", "~", rawurlencode($value));
			else
				$value = str_replace("%7E", "~", $value);
			$canonicalized_query[] = $param."=".$value;
		}

		$canonicalized_query = implode("&", $canonicalized_query);

		// create the string to sign
		$string_to_sign = $method."\n".$host."\n".$uri."\n".$canonicalized_query;
		
		// calculate HMAC with SHA256 and base64-encoding
		$signature = base64_encode(hash_hmac("sha256", $string_to_sign, $SecretKey, true));
		
		// encode the signature for the request
		$signature = str_replace("%7E", "~", rawurlencode($signature));
		
		// create request
		$request = "https://".$host.$uri."?".$canonicalized_query."&Signature=".$signature;
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_USERAGENT, "AmazonQuery/1.0 (Language=Amazon)");
		if ( !empty($datapost) )
		{
			echo '<br /><br />'.htmlentities($datapost["FeedContent"]).'<br /><br />';
			$feedHandle = fopen('php://temp', 'w');
			fwrite($feedHandle, $datapost["FeedContent"]);
			rewind($feedHandle);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: text/xml; charset=iso-8859-1", "Content-MD5:".base64_encode(md5(stream_get_contents($feedHandle), true)) ));
			rewind($feedHandle);
			curl_setopt($ch, CURLOPT_POSTFIELDS, stream_get_contents($feedHandle));
		} else {
			curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: text/xml; charset=utf-8"));
		}

		curl_setopt($ch, CURLOPT_URL, $request);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$response = curl_exec($ch);
		curl_close($ch);
		return($response);
	}

	function amazon_post_request($request, $datapost=array())
	{
		$method = "GET";
		$uri = "/";

		$AWSAccessKeyId = "AKIAIVV6BQ6NVVWUWEUA";
		$Marketplace = "A1PA6795UKMFR9";
		$Merchant = "A3UOJO2H7UZY88";
		$SecretKey = "B8k51dOOQFeWoaAmdcvTrOVEb7AyFvQJ0XlzEpMe";
		$host = "mws.amazonservices.de";

		$request .= "&AWSAccessKeyId=$AWSAccessKeyId&Marketplace=$Marketplace&Merchant=$Merchant&Timestamp=".gmdate("Y-m-d\TH:i:s\Z")."&Version=2009-01-01&SignatureVersion=2&SignatureMethod=HmacSHA256";

		// Clean up and sort
		$request = explode('&',$request);
		
		foreach ($request as $key => $value)
		{
			$t = explode("=",$value);
			$params[$t[0]] = $t[1];
		}
		unset($request);

		ksort($params);
		foreach ($params as $param=>$value)
		{
			$param = str_replace("%7E", "~", rawurlencode($param));
			$value = str_replace("%7E", "~", rawurlencode($value));
			$canonicalized_query[] = $param."=".$value;
		}

		$canonicalized_query = implode("&", $canonicalized_query);

		// create the string to sign
		$string_to_sign = $method."\n".$host."\n".$uri."\n".$canonicalized_query;
		
		// calculate HMAC with SHA256 and base64-encoding
		$signature = base64_encode(hash_hmac("sha256", $string_to_sign, $SecretKey, true));
		
		// encode the signature for the request
		$signature = str_replace("%7E", "~", rawurlencode($signature));
		
		// create request
		$request = "https://".$host.$uri."?".$canonicalized_query."&Signature=".$signature;
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_USERAGENT, "AmazonQuery/1.0 (Language=Amazon)");
		if ( !empty($datapost) )
		{
			echo '<br /><br />'.htmlentities($datapost["FeedContent"]).'<br /><br />';
			$feedHandle = fopen('php://temp', 'w');
			fwrite($feedHandle, $datapost["FeedContent"]);
			rewind($feedHandle);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: text/xml; charset=iso-8859-1", "Content-MD5:".base64_encode(md5(stream_get_contents($feedHandle), true)) ));
			rewind($feedHandle);
			curl_setopt($ch, CURLOPT_POSTFIELDS, stream_get_contents($feedHandle));
		} else {
			curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: text/xml; charset=iso-8859-1"));
			curl_setopt($ch, CURLOPT_POSTFIELDS, "");
		}
		curl_setopt($ch, CURLOPT_URL, $request);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$response = curl_exec($ch);
		curl_close($ch);
		return($response);
	}

	//CHECK FOR ANSWER
	$request = "Action=GetFeedSubmissionList";
	$results = amazon_sendrequest($request);
	$xml = new SimpleXMLElement($results);
	$array = json_decode(json_encode($xml), TRUE);

	//submission list
	$FeedType = array(
		"_POST_PRODUCT_DATA_" => "Product Feed",
		"_POST_PRODUCT_RELATIONSHIP_DATA_" => "Relationships Feed",
		"_POST_ITEM_DATA_" => "Single Format Item Feed",
		"_POST_PRODUCT_OVERRIDES_DATA_" => "Shipping Override Feed",
		"_POST_PRODUCT_IMAGE_DATA_" => "Product Images Feed",
		"_POST_PRODUCT_PRICING_DATA_" => "Pricing Feed",
		"_POST_INVENTORY_AVAILABILITY_DATA_" => "Inventory Feed",
		"_POST_ORDER_ACKNOWLEDGEMENT_DATA_" => "Order Acknowledgement Feed",
		"_POST_ORDER_FULFILLMENT_DATA_" => "Order Fulfillment Feed",
		"_POST_FULFILLMENT_ORDER_REQUEST_DATA_" => "FBA Shipment Injection Fulfillment Feed",
		"_POST_FULFILLMENT_ORDER_CANCELLATION_REQUEST_DATA" => "FBA Shipment Injection Cancellation Feed",
		"_POST_PAYMENT_ADJUSTMENT_DATA_" => "Order Adjustment Feed",
		"_POST_INVOICE_CONFIRMATION_DATA_" => "Invoice Confirmation Feed",
		"_POST_ITEM_DATA_" => ""
	);
	$FeedProcessingStatus = array(
			"_SUBMITTED_" => '<span style="color:#9c6500;">Empfangen</span>', 
			"_IN_PROGRESS_" => '<span style="color:#9c6500;">In Bearbeitung</span>', 
			"_CANCELLED_" => '<span style="color:#9c0006;">Abgebrochen</span>', 
			"_DONE_" => '<span style="color:#006100;">Erledigt</span>'
		);
	
	echo '<ul class="orderlist" style="width:721px;">';
	echo '	<li id="ebay_accounts_header" style="width:700px; background:#ccc;">';
	echo '		<div style="width:100px;">ID</div>';
	echo '		<div style="width:150px;">Typ</div>';
	echo '		<div style="width:150px;">Datum</div>';
	echo '		<div style="width:100px;">Status</div>';
	echo '		<div style="width:100px;">Optionen</div>';
	echo '	</li>';

	for($i = 0; $i < sizeof($array["GetFeedSubmissionListResult"]["FeedSubmissionInfo"]); $i++)
	{
		$feedSubmissionId = $array["GetFeedSubmissionListResult"]["FeedSubmissionInfo"][$i]["FeedSubmissionId"];
		$feedType = $array["GetFeedSubmissionListResult"]["FeedSubmissionInfo"][$i]["FeedType"];
		$submittedDate = $array["GetFeedSubmissionListResult"]["FeedSubmissionInfo"][$i]["SubmittedDate"];
		$feedProcessingStatus = $array["GetFeedSubmissionListResult"]["FeedSubmissionInfo"][$i]["FeedProcessingStatus"];
		
		if (isset($_GET["id_report"]) and $_GET["id_report"] == $feedSubmissionId) 
		{
			 $styleLink = ' style="font-weight: bold;"'; 
			 $styleLi = ' style="width:700px;background: #F2F2F2;"';
		} else {
			$styleLink = "";
			$styleLi = "";
		}
		
		echo '	<li' . $styleLi. '>';
			echo '		<div style="width:100px;">';
			echo '	<a' . $styleLink . ' href="backend_amazon_submissions.php?id_report=' . $feedSubmissionId . '">' . $feedSubmissionId . '</a>';
			echo '		</div>';
			echo '		<div style="width:150px;">';
			echo $FeedType[$feedType];
			echo '		</div>';		
			echo '		<div style="width:150px;">';
			echo date("d-m-Y H:i", strtotime($submittedDate));
			echo '		</div>';		
			echo '		<div style="width:100px;">';
			echo $FeedProcessingStatus[$feedProcessingStatus];
			echo '		</div>';	
			echo '		<div style="width:100px;">';
			if ($status == "_SUBMITTED_") {
				echo '<img src="images/icons/24x24/remove.png" onclick="feed_cancel('.$feedSubmissionId.');" />';
			} else {
				echo '<img src="images/icons/24x24/info.png" onclick="feed_status('.$feedSubmissionId.');" />';
			}
			echo '		</div>';
		echo '	</li>';
	}
	
	//next report list
	$array["GetFeedSubmissionListByNextTokenResult"]["NextToken"] = $array["GetFeedSubmissionListResult"]["NextToken"];
	$j = 0;
	while ($array["GetFeedSubmissionListByNextTokenResult"]["NextToken"] != "" )
	{
		//NextToken Fix	
		$NextToken=$array["GetFeedSubmissionListByNextTokenResult"]["NextToken"];
		$NextToken=str_replace("#", ".", $NextToken);
		$NextToken=str_replace("*", "/", $NextToken);
		$NextToken=rawurlencode($NextToken);
		$request = "Action=GetFeedSubmissionListByNextToken&NextToken=".$NextToken;
		$results = amazon_sendrequest($request);
		$xml = new SimpleXMLElement($results);
		$array = json_decode(json_encode($xml), TRUE);
		for($i=0; $i<sizeof($array["GetFeedSubmissionListByNextTokenResult"]["FeedSubmissionInfo"]); $i++)
		{
			$feedSubmissionId =  $array["GetFeedSubmissionListByNextTokenResult"]["FeedSubmissionInfo"][$i]["FeedSubmissionId"];
			$feedType = $array["GetFeedSubmissionListByNextTokenResult"]["FeedSubmissionInfo"][$i]["FeedType"];
			$submittedDate = $array["GetFeedSubmissionListByNextTokenResult"]["FeedSubmissionInfo"][$i]["SubmittedDate"];
			$feedProcessingStatus = $array["GetFeedSubmissionListByNextTokenResult"]["FeedSubmissionInfo"][$i]["FeedProcessingStatus"];
			
			if (isset($_GET["id_report"]) and $_GET["id_report"] == $feedSubmissionId) 
			{
				 $styleLink = ' style="font-weight:bold;"'; 
				 $styleLi = ' style="width:700px;background: #F2F2F2;"';
			} else {
				$styleLink = "";
				$styleLi = "";
			}			
			
			echo '<li' . $styleLi . '>';
			echo '	<div style="width:100px;">';
			echo '		<a' . $styleLink . ' href="backend_amazon_submissions.php?id_report=' . $feedSubmissionId . '">' . $feedSubmissionId . '</a>';
			echo '	</div>';
			echo '	<div style="width:150px;">';
			echo $FeedType[$feedType];
			echo '	</div>';		
			echo '	<div style="width:150px;">';
			echo date("d-m-Y H:i", strtotime($submittedDate));
			echo '	</div>';		
			echo '	<div style="width:100px;">';
			echo $FeedProcessingStatus[$feedProcessingStatus];
			echo '	</div>';	
			echo '	<div style="width:100px;">';
			if ($status == "_SUBMITTED_") 
			{
				echo '<img src="images/icons/24x24/remove.png" onclick="feed_cancel(' . $feedSubmissionId . ');" />';
			} else {
				echo '<img src="images/icons/24x24/info.png" onclick="feed_status(' . $feedSubmissionId . ');" />';
			}
			echo '	</div>';
			echo '</li>';
		}
		$j++;
		if ($j > 2) break;
	}
	echo '</ul>';

	//	report details
	if (isset($_GET["id_report"]) and $_GET["id_report"] > 0) 
	{
		$request = "Action=GetFeedSubmissionResult&FeedSubmissionId=".$_GET["id_report"];
		$results = amazon_sendrequest($request,$country);
		$xml = new SimpleXMLElement($results);
		$resultToArray = json_decode(json_encode($xml), TRUE);
	
		echo '<div id="logfile" class="widget-logfile">';
		echo '	<h2>Bericht für Report ID #' . $_GET["id_report"] . '</h2>';	
		$html = "";
		if (count($resultToArray['Message']) > 0) 
		{	
			$documentTransactionID = $resultToArray['Message']['ProcessingReport']['DocumentTransactionID'];
			$statusCode = $resultToArray['Message']['ProcessingReport']['StatusCode'];
			$processingSummary = $resultToArray['Message']['ProcessingReport']['ProcessingSummary'];
			
			$html.= '<div style="margin-bottom: 5px;">';
			$html.= ' <div style="padding: 5px;color: #333333;">DocumentTransactionID: ' . $documentTransactionID. ' | StatusCode: ' . $statusCode . '</div>';
			$html.= ' <div style="padding: 5px;">';
			$html.= '	<div style="padding: 5px;background: #D6F1B5;">';
				$html.= 'MessagesProcessed: ' . $processingSummary['MessagesProcessed'] . ' | ' . 'MessagesSuccessful: ' . $processingSummary['MessagesSuccessful'] . ' | ';
				$html.= 'MessagesWithError: ' . $processingSummary['MessagesWithError'] . ' | ' . 'MessagesWithWarning: ' . $processingSummary['MessagesWithWarning'];
			$html.=  '	</div>';		
			if (count($resultToArray['Message']['ProcessingReport']['Result']) > 0) 
			{
				foreach($resultToArray['Message']['ProcessingReport']['Result'] as $result)
				{
					$html.= '<div style="padding: 5px;background: #dddddd;margin-bottom: 1px;">';
					$html.= 'MessageID: ' . $result['MessageID'] . ' | ' . $result['ResultCode'] . ' | ' . $result['ResultMessageCode'] . '<br />';
					if (isset($result['AdditionalInfo']) && $result['AdditionalInfo'] != null) 
					{
						$html.= ' <strong>SKU: ' . $result['AdditionalInfo']['SKU'] . '</strong><br />';
					}
					$html.= $result['ResultDescription'];
					$html.= '</div>';
				}
			}
			$html.= '	</div>';
			$html.= '<div>';
		}
		echo $html;
		echo '</div>';
	}

	//report details
	if ( isset($_GET["cancel_submission"]) and $_GET["cancel_submission"]>0 )
	{
		echo '<div id="logfile" class="widget-logfile">';
		echo '	<h2>Bericht #' . $_GET["cancel_submission"] . '</h2>';
		$request = "Action=CancelFeedSubmissions&FeedSubmissionIdList.Id.1=".$_GET["cancel_submission"];
		echo $results = amazon_sendrequest($request,$country);
		$xml = new SimpleXMLElement($results);
		$array = json_decode(json_encode($xml), TRUE);
		$status = $array["CancelFeedSubmissionsResult"]["FeedSubmissionInfo"]["FeedProcessingStatus"];
		if ($status == "_CANCELLED_") 
		{
			echo 'Auftrag erfolgreich abgebrochen.';
		} else {
			if ($status == "_IN_PROGRESS_") 
			{
				echo 'Der Auftrag konnte nicht abgebrochen werden, da er bereits bearbeitet wird.';
			} else {
				echo 'Der Auftrag konnte nicht abgebrochen werden.';
			}
		}
	}
	
	//FEED STATUS DIALOG
	echo '	<div style="display:none;" id="feed_status_dialog">';
	echo '</div>';
	include("templates/".TEMPLATE_BACKEND."/footer.php");
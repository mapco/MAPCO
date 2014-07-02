<?php

/***
 *	@author: rlange@mapco.de
 *	Amazon Service for submission result
 *	-
 *
*******************************************************************************/

$PATH = dirname(__FILE__);
require_once($PATH . '/Model/AmazonModel.php');
//include("../soa2/index.php");


//show_error(9866,7,__FILE__, __LINE__, "AmazonOrderId = test");
//exit;

//	keep post submit
$post = $_POST;

	//	get amazon accountsites for amazon marketplaces by accountssites id
	$amazonAccountsSites = getAmazonAccountsites($post);

    //  get the amazon account data
    $amazonAccount = getAmazonAccountById($amazonAccountsSites['account_id']);

   /************************************************************************
    * REQUIRED
    *
    * Access Key ID and Secret Acess Key ID, obtained from:
    * http://mws.amazon.com
    ***********************************************************************/
    $AWS_ACCESS_KEY_ID = $amazonAccount["AWSAccessKeyId"];
    $AWS_SECRET_ACCESS_KEY = $amazonAccount["SecretKey"];

   /************************************************************************
    * REQUIRED
    *
    * All MWS requests must contain a User-Agent header. The application
    * name and version defined below are used in creating this value.
    ***********************************************************************/
    $APPLICATION_NAME = '<Your Application Name>';
    $APPLICATION_VERSION = '2009-01-01';

   /************************************************************************
    * REQUIRED
    *
    * All MWS requests must contain the seller's merchant ID and
    * marketplace ID.
    ***********************************************************************/
    $MERCHANT_ID = $amazonAccount["MerchantId"];
    $MARKETPLACE_ID = $amazonAccountsSites["MarketplaceID"];
	$MARKETPLACE_HOST = $amazonAccountsSites["host"];

   /************************************************************************
    * REQUIRED
    *
    * All MWS requests must contain the type and the method
    ***********************************************************************/
    $MWS_TYPE = '<Use the MWS Type>';
    $MWS_METHOD = 'GET';
	$MWS_OPERATION_TYPE = 'Update';
	$MWS_MESSAGE_TYPE = $post['MessageType'];

	$urlTerm = "&AWSAccessKeyId=" . $AWS_ACCESS_KEY_ID . "&Marketplace=" . $MARKETPLACE_ID;
	$urlTerm.= "&Merchant=" . $MERCHANT_ID . "&Timestamp=" . gmdate("Y-m-d\TH:i:s\Z");
	$urlTerm.= "&Version=" . $APPLICATION_VERSION . "&SignatureVersion=2&SignatureMethod=HmacSHA256";

	$countFeedSubmissionInserts = 0;
	$countFeedSubmissionUpdates = 0;
	$countFeedSubmissionMessageInserts = 0;
	$countFeedSubmissionResultInserts  = 0;
	$countFeedSubmissionByNextTokenInserts = 0;
	$countFeedSubmissionByNextTokenUpdates = 0;

	//	get the latest 10 results
	$post_data = array();
	$post_data['url'] = "Action=GetFeedSubmissionList" . $urlTerm;
	$post_data['SecretKey'] = $AWS_SECRET_ACCESS_KEY;
	$post_data['method'] = $MWS_METHOD;
	$responseFeedSubmissionList = MarketplaceWebServiceSubmit($post_data, $MARKETPLACE_HOST);
	$xmlResponseFeedSubmissionList = new SimpleXMLElement($responseFeedSubmissionList);
	$reportsFeedSubmissionList = json_decode(json_encode($xmlResponseFeedSubmissionList), TRUE);
	if (isset($reportsFeedSubmissionList['GetFeedSubmissionListResult']['FeedSubmissionInfo']) && $reportsFeedSubmissionList['GetFeedSubmissionListResult']['FeedSubmissionInfo'] != null)
	{
		foreach($reportsFeedSubmissionList['GetFeedSubmissionListResult']['FeedSubmissionInfo'] as $reportFeedSubmissionList)
		{
			$data = array();
			$data['from'] = 'amazon_feed_submissions';
			$data['select'] = '*';
			$data['where'] = "
				FeedSubmissionId = " . $reportFeedSubmissionList['FeedSubmissionId'];
			$amazonFeedSubmissions = SQLSelect($data['from'], $data['select'], $data['where'], 0, 0, 1, 'shop',  __FILE__, __LINE__);
			if (count($amazonFeedSubmissions) > 0)
			{
				//	update amazon feed submission
				$data = array();
				$data['SubmittedDate'] = $reportFeedSubmissionList["SubmittedDate"];
				$data['StartedProcessingDate'] = $reportFeedSubmissionList["StartedProcessingDate"];
				$data['CompletedProcessingDate'] = $reportFeedSubmissionList["CompletedProcessingDate"];
				$data['FeedProcessingStatus'] = $reportFeedSubmissionList["FeedProcessingStatus"];
				$data['NextToken'] = $reportFeedSubmissionList['GetFeedSubmissionListResult']["NextToken"];
				$data['lastmod'] = time();
				$addWhere = "
					id_feed_submission = " .  $amazonFeedSubmissions['id_feed_submission'];
				SQLUpdate('amazon_feed_submissions', $data, $addWhere, 'shop', __FILE__, __LINE__);
				$countFeedSubmissionUpdates++;
			} else {
				//	insert a new amazon feed submission
				$field = array(
					'table' => 'amazon_feed_submissions'
				);
				$data = array();
				$data['FeedSubmissionId'] = $reportFeedSubmissionList["FeedSubmissionId"];
				$data['FeedType'] = $reportFeedSubmissionList["FeedType"];
				$data['SubmittedDate'] = $reportFeedSubmissionList["SubmittedDate"];
				$data['StartedProcessingDate'] = $reportFeedSubmissionList["StartedProcessingDate"];
				$data['CompletedProcessingDate'] = $reportFeedSubmissionList["CompletedProcessingDate"];
				$data['FeedProcessingStatus'] = $reportFeedSubmissionList["FeedProcessingStatus"];
				$data['NextToken'] = $reportFeedSubmissionList['GetFeedSubmissionListResult']["NextToken"];
				$data['firstmod'] = time();
				$data['lastmod'] = time();
				SQLInsert($field, $data, 'shop', __FILE__, __LINE__);
				$countFeedSubmissionInserts++;
			}

			//	get amazon submission message by feed submission id
			$post_data = array();
			$post_data['url'] = "Action=GetFeedSubmissionResult&FeedSubmissionId=" . $reportFeedSubmissionList["FeedSubmissionId"] . $urlTerm;
			$post_data['SecretKey'] = $AWS_SECRET_ACCESS_KEY;
			$post_data['method'] = $MWS_METHOD;
			$responseFeedSubmissionId = MarketplaceWebServiceSubmit($post_data, $MARKETPLACE_HOST);
			$xmlResponseFeedSubmissionId = new SimpleXMLElement($responseFeedSubmissionId);
			$reportResponseFeedSubmissionId = json_decode(json_encode($xmlResponseFeedSubmissionId), TRUE);
			if (count($reportResponseFeedSubmissionId['Message']) > 0)
			{
				$documentTransactionID = $reportResponseFeedSubmissionId['Message']['ProcessingReport']['DocumentTransactionID'];
				$processingSummary = $reportResponseFeedSubmissionId['Message']['ProcessingReport']['ProcessingSummary'];

				$setUpdate = 0;
				$data = array();
				$data['from'] = 'amazon_feed_submission_message';
				$data['select'] = '*';
				$data['where'] = "
					FSMFeedSubmissionId = " . $reportFeedSubmissionList["FeedSubmissionId"];
				$amazonFeedSubmissionMessage = SQLSelect($data['from'], $data['select'], $data['where'], 0, 0, 1, 'shop',  __FILE__, __LINE__);
				if (count($amazonFeedSubmissionMessage) > 0)
				{
					//	update an amazon feed submission message, when it useful
					$setUpdate = 1;
					$data = array();
					$data['StatusCode'] = $reportResponseFeedSubmissionId['Message']['ProcessingReport']['StatusCode'];
					$data['MessagesProcessed'] = $processingSummary['MessagesProcessed'];
					$data['MessagesSuccessful'] = $processingSummary['MessagesSuccessful'];
					$data['MessagesWithError'] = $processingSummary['MessagesWithError'];
					$data['MessagesWithWarning'] = $processingSummary['MessagesWithWarning'];
					$addWhere = "
						FSMFeedSubmissionId = " . $reportFeedSubmissionList["FeedSubmissionId"];
					SQLUpdate('amazon_feed_submission_message', $data, $addWhere, 'shop', __FILE__, __LINE__);
				} else {
					//	insert a new amazon feed submission message
					$field = array(
						'table' => 'amazon_feed_submission_message',
						'lastInsertId' => 1
					);
					$data = array();
					$data['FSMFeedSubmissionId'] = $reportFeedSubmissionList["FeedSubmissionId"];
					$data['MessageID'] = $reportResponseFeedSubmissionId['Message']['MessageID'];
					$data['DocumentTransactionID'] = $documentTransactionID;
					$data['StatusCode'] = $reportResponseFeedSubmissionId['Message']['ProcessingReport']['StatusCode'];
					$data['MessagesProcessed'] = $processingSummary['MessagesProcessed'];
					$data['MessagesSuccessful'] = $processingSummary['MessagesSuccessful'];
					$data['MessagesWithError'] = $processingSummary['MessagesWithError'];
					$data['MessagesWithWarning'] = $processingSummary['MessagesWithWarning'];
					$lastInsertId = SQLInsert($field, $data, 'shop', __FILE__, __LINE__);
					$countFeedSubmissionMessageInserts++;
				}

				if (count($reportResponseFeedSubmissionId['Message']['ProcessingReport']['Result']) > 0 && $setUpdate == 0)
				{
					$processingReportResult = null;
					if (!isset($reportResponseFeedSubmissionId['Message']['ProcessingReport']['Result'][0]))
					{
						$reportResponseFeedSubmissionId['Message']['ProcessingReport']['Result'] = array($reportResponseFeedSubmissionId['Message']['ProcessingReport']['Result']);
					}

					foreach($reportResponseFeedSubmissionId['Message']['ProcessingReport']['Result'] as $processingReportResult)
					{
						//	insert an amazon feed submission message result
						$field = array(
							'table' => 'amazon_feed_submission_result'
						);
						$data = array();
						$data['amazon_feed_submission_message_id'] = $lastInsertId;
						$data['FSRFeedSubmissionId'] = $reportFeedSubmissionList["FeedSubmissionId"];
						$data['DocumentTransactionID'] = $documentTransactionID;
						$data['MessageID'] = (string)$processingReportResult['MessageID'];
						$data['ResultCode'] = (string)$processingReportResult['ResultCode'];
						$data['ResultMessageCode'] = (string)$processingReportResult['ResultMessageCode'];
						$data['ResultDescription'] = (string)$processingReportResult['ResultDescription'];
						$data['AdditionalInfoSKU'] = (string)$processingReportResult['AdditionalInfo']['SKU'];
						SQLInsert($field, $data, 'shop', __FILE__, __LINE__);
						$countFeedSubmissionResultInserts++;
					}
				}

				//	error reporting
				if ($setUpdate == 0 && $processingSummary['MessagesWithError'] > 0 || $setUpdate == 0 && $processingSummary['MessagesWithWarning'] > 0)
				{
					$txtheaders = "From: Amazon Error Report \n" ;
					$txtheaders.= "Reply-To:  \n" ;
					$txtheaders.= "X-Mailer: PHP\n" ;
					$txtheaders.= "X-Sender: rlange@mapco.de \n" ;
					$txtheaders.= 'MIME-Version: 1.0' ."\n";
					$txtheaders.= 'Content-Transfer-Encoding: 8bit' . "\n";
					$txtheaders.= 'Content-Type: text/html; charset=utf-8' . "\n\n";
					$ToReceiver = 'rlange@mapco.de';
					$Subject = 'Report ID: ' .  $reportFeedSubmissionList["FeedSubmissionId"];

					$message = 'Report ID: <a href="' . PATH . 'backend_amazon_feed_submission.php?FeedSubmissionId=' .  $reportFeedSubmissionList["FeedSubmissionId"] . '">' .  $reportFeedSubmissionList["FeedSubmissionId"] . '</a><br />';
					$message.= 'Beim Datenabgleich mit Amazon sind Fehler aufgetreten. Bitte kontrolliere diese!' . '<br />';
					$message.= '<strong>Auswertung:</strong><br />';
					$message.= 'Fehler: ' . $processingSummary['MessagesWithError'] . '<br />';
					$message.= 'Warnungen: ' . $processingSummary['MessagesWithWarning'] . '<br />';
					mail($ToReceiver, $Subject, $message, $txtheaders);
				}
            }
		}
	}

	//	next report list
	if ($reportsFeedSubmissionList['GetFeedSubmissionListResult']["NextToken"] != null)
	{
		//	cache the amazon feed submission in $amazonFeedSubmissionsList
		$data = array();
		$data['from'] = 'amazon_feed_submissions';
		$data['select'] = '*';
		$amazonFeedSubmissions = SQLSelect($data['from'], $data['select'], 0, 0, 0, 0, 'shop',  __FILE__, __LINE__);
		$amazonFeedSubmissionsList = array();
		if (count($amazonFeedSubmissions) > 0)
		{
			foreach($amazonFeedSubmissions as $amazonFeedSubmission)
			{
				$amazonFeedSubmissionsList[$amazonFeedSubmission['FeedSubmissionId']] =  $amazonFeedSubmission;
			}
		}

		$post_data = array();
		$post_data['url'] = "Action=GetFeedSubmissionListByNextToken&NextToken=" . $reportsFeedSubmissionList['GetFeedSubmissionListResult']["NextToken"] . $urlTerm;
		$post_data['SecretKey'] = $AWS_SECRET_ACCESS_KEY;
		$post_data['method'] = $MWS_METHOD;
		$responseFeedSubmissionIdByNextToken = MarketplaceWebServiceSubmit($post_data, $MARKETPLACE_HOST);
		$xmlResponseFeedSubmissionIdByNextToken = new SimpleXMLElement($responseFeedSubmissionIdByNextToken);
		$reportResultByNextToken = json_decode(json_encode($xmlResponseFeedSubmissionIdByNextToken), TRUE);
		if (count($reportResultByNextToken["GetFeedSubmissionListByNextTokenResult"]["FeedSubmissionInfo"]) > 0)
		{
			foreach($reportResultByNextToken["GetFeedSubmissionListByNextTokenResult"]["FeedSubmissionInfo"] as $feedSubmissionInfoByNextToken)
			{
				if (isset($amazonFeedSubmissionsList[$feedSubmissionInfoByNextToken["FeedSubmissionId"]]))
				{
					//	update amazon feed submission
					$data = array();
					$data['SubmittedDate'] = $feedSubmissionInfoByNextToken["SubmittedDate"];
					$data['StartedProcessingDate'] = $feedSubmissionInfoByNextToken["StartedProcessingDate"];
					$data['CompletedProcessingDate'] = $feedSubmissionInfoByNextToken["CompletedProcessingDate"];
					$data['FeedProcessingStatus'] = $feedSubmissionInfoByNextToken["FeedProcessingStatus"];
					$data['NextToken'] = $reportResultByNextToken["GetFeedSubmissionListByNextTokenResult"]["NextToken"];
					$data['lastmod'] = time();
					$addWhere = "
						id_feed_submission = " .  $amazonFeedSubmissionsList[$feedSubmissionInfoByNextToken["FeedSubmissionId"]]['id_feed_submission'];
					SQLUpdate('amazon_feed_submissions', $data, $addWhere, 'shop', __FILE__, __LINE__);
					$countFeedSubmissionByNextTokenUpdates++;
				} else {

					//	insert a new amazon feed submission
					$field = array(
						'table' => 'amazon_feed_submissions'
					);
					$data = array();
					$data['FeedSubmissionId'] = $feedSubmissionInfoByNextToken["FeedSubmissionId"];
					$data['FeedType'] = $feedSubmissionInfoByNextToken["FeedType"];
					$data['SubmittedDate'] = $feedSubmissionInfoByNextToken["SubmittedDate"];
					$data['StartedProcessingDate'] = $feedSubmissionInfoByNextToken["StartedProcessingDate"];
					$data['CompletedProcessingDate'] = $feedSubmissionInfoByNextToken["CompletedProcessingDate"];
					$data['FeedProcessingStatus'] = $feedSubmissionInfoByNextToken["FeedProcessingStatus"];
					$data['NextToken'] = $reportResultByNextToken["GetFeedSubmissionListByNextTokenResult"]["NextToken"];
					$data['firstmod'] = time();
					$data['lastmod'] = time();
					SQLInsert($field, $data, 'shop', __FILE__, __LINE__);
					$countFeedSubmissionByNextTokenInserts++;
				}

				//	get amazon submission message by feed submission id
				$post_data = array();
				$post_data['url'] = "Action=GetFeedSubmissionResult&FeedSubmissionId=" . $feedSubmissionInfoByNextToken["FeedSubmissionId"] . $urlTerm;
				$post_data['SecretKey'] = $AWS_SECRET_ACCESS_KEY;
				$post_data['method'] = $MWS_METHOD;
				$responseFeedSubmissionIdByNextToken = MarketplaceWebServiceSubmit($post_data, $MARKETPLACE_HOST);
				$xmlResponseFeedSubmissionIdByNextToken = new SimpleXMLElement($responseFeedSubmissionIdByNextToken);
				$reportResultByNextToken = json_decode(json_encode($xmlResponseFeedSubmissionIdByNextToken), TRUE);
				if (count($reportResultByNextToken['Message']) > 0)
				{
					$documentTransactionID = $reportResultByNextToken['Message']['ProcessingReport']['DocumentTransactionID'];
					$processingSummary = $reportResultByNextToken['Message']['ProcessingReport']['ProcessingSummary'];

					$setUpdate = 0;
					$data = array();
					$data['from'] = 'amazon_feed_submission_message';
					$data['select'] = '*';
					$data['where'] = "
						FSMFeedSubmissionId = " . $feedSubmissionInfoByNextToken["FeedSubmissionId"];
					$amazonFeedSubmissionMessage = SQLSelect($data['from'], $data['select'], $data['where'], 0, 0, 1, 'shop',  __FILE__, __LINE__);
					if (count($amazonFeedSubmissionMessage) > 0)
					{
						//	update an amazon feed submission message, when it useful
						$setUpdate = 1;
					} else {
						//	insert a new amazon feed submission message
						$field = array(
							'table' => 'amazon_feed_submission_message',
							'lastInsertId' => 1
						);
						$data = array();
						$data['FSMFeedSubmissionId'] = $feedSubmissionInfoByNextToken["FeedSubmissionId"];
						$data['MessageID'] = $reportResultByNextToken['Message']['MessageID'];
						$data['DocumentTransactionID'] = $documentTransactionID;
						$data['StatusCode'] = $reportResultByNextToken['Message']['ProcessingReport']['StatusCode'];
						$data['MessagesProcessed'] = $processingSummary['MessagesProcessed'];
						$data['MessagesSuccessful'] = $processingSummary['MessagesSuccessful'];
						$data['MessagesWithError'] = $processingSummary['MessagesWithError'];
						$data['MessagesWithWarning'] = $processingSummary['MessagesWithWarning'];
						$lastInsertId = SQLInsert($field, $data, 'shop', __FILE__, __LINE__);
						$countFeedSubmissionMessageInserts++;
					}
					if (count($reportResultByNextToken['Message']['ProcessingReport']['Result']) > 0 && $setUpdate == 0)
					{
						$processingReportResult = null;
						if (!isset($reportResultByNextToken['Message']['ProcessingReport']['Result'][0]))
						{
							$reportResultByNextToken['Message']['ProcessingReport']['Result'] = array($reportResultByNextToken['Message']['ProcessingReport']['Result']);
						}
						foreach($reportResultByNextToken['Message']['ProcessingReport']['Result'] as $processingReportResult)
						{
							//	insert an amazon feed submission message result
							$field = array(
								'table' => 'amazon_feed_submission_result'
							);
							$data = array();
							$data['amazon_feed_submission_message_id'] = $lastInsertId;
							$data['FSRFeedSubmissionId'] = $feedSubmissionInfoByNextToken["FeedSubmissionId"];
							$data['DocumentTransactionID'] = $documentTransactionID;
							$data['MessageID'] = (string)$processingReportResult['MessageID'];
							$data['ResultCode'] = (string)$processingReportResult['ResultCode'];
							$data['ResultMessageCode'] = (string)$processingReportResult['ResultMessageCode'];
							$data['ResultDescription'] = (string)$processingReportResult['ResultDescription'];
							$data['AdditionalInfoSKU'] = (string)$processingReportResult['AdditionalInfo']['SKU'];
							SQLInsert($field, $data, 'shop', __FILE__, __LINE__);
							$countFeedSubmissionResultInserts++;
						}
					}

					//	error reporting
					if ($setUpdate == 0 && $processingSummary['MessagesWithError'] > 0 || $setUpdate == 0 && $processingSummary['MessagesWithWarning'] > 0)
					{
						$txtheaders = "From: Amazon Error Report \n" ;
						$txtheaders.= "Reply-To:  \n" ;
						$txtheaders.= "X-Mailer: PHP\n" ;
						$txtheaders.= "X-Sender: rlange@mapco.de \n" ;
						$txtheaders.= 'MIME-Version: 1.0' ."\n";
						$txtheaders.= 'Content-Transfer-Encoding: 8bit' . "\n";
						$txtheaders.= 'Content-Type: text/html; charset=utf-8' . "\n\n";
						$ToReceiver = 'rlange@mapco.de';
						$Subject = 'Report ID: ' . $feedSubmissionInfoByNextToken["FeedSubmissionId"];

						$message = 'Report ID: <a href="' . PATH . 'backend_amazon_feed_submission.php?FeedSubmissionId=' . $feedSubmissionInfoByNextToken["FeedSubmissionId"] . '">' . $feedSubmissionInfoByNextToken["FeedSubmissionId"] . '</a><br />';
						$message.= 'Beim Datenabgleich mit Amazon sind Fehler aufgetreten. Bitte kontrolliere diese!' . '<br />';
						$message.= '<strong>Auswertung:</strong><br />';
						$message.= 'Fehler: ' . $processingSummary['MessagesWithError'] . '<br />';
						$message.= 'Warnungen: ' . $processingSummary['MessagesWithWarning'] . '<br />';
						mail($ToReceiver, $Subject, $message, $txtheaders);
					}
				}
			}
		}
	}

	$xml = "\n" . "<AmazonSubmissionResult>" . "\n";
	$xml.= '	<marketplace>' . $amazonAccountsSites["name"] . '</marketplace>' . "\n";
	$xml.= '	<feedSubmissionInserts>' . $countFeedSubmissionInserts . '</feedSubmissionInserts>' . "\n";
	$xml.= '	<feedSubmissionUpdates>' . $countFeedSubmissionUpdates . '</feedSubmissionUpdates>' . "\n";
	$xml.= '	<feedSubmissionByNextTokenInserts>' . $countFeedSubmissionByNextTokenInserts . '</feedSubmissionByNextTokenInserts>' . "\n";
	$xml.= '	<feedSubmissionByNextTokenUpdates>' . $countFeedSubmissionByNextTokenUpdates . '</feedSubmissionByNextTokenUpdates>' . "\n";
	$xml.= '	<feedSubmissionMessageInserts>' . $countFeedSubmissionMessageInserts . '</feedSubmissionMessageInserts>' . "\n";
	$xml.= '	<feedSubmissionResultInserts>' . $countFeedSubmissionResultInserts . '</feedSubmissionResultInserts>' . "\n";
	$xml.= '</AmazonSubmissionResult>'. "\n";
	echo $xml;

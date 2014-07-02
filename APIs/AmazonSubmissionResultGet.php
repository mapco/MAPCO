<?php

/***
 *	@author: rlange@mapco.de
 *	Amazon Service for submission result
 *	- 
 *
*******************************************************************************/

$PATH = dirname(__FILE__);
require_once($PATH . '/Model/AmazonModel.php');

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
	if (isset($post['ReportId']) && !empty($post['ReportId'])) 
	{
		$urlTerm.= "&ReportId=" . $post['ReportId'];	
	}
	
	$post_data = array();
	$post_data['url'] = "Action=" . $post['action'] . $urlTerm;
	$post_data['SecretKey'] = $AWS_SECRET_ACCESS_KEY;
	$post_data['method'] = $MWS_METHOD;
	$response = MarketplaceWebServiceSubmit($post_data, $MARKETPLACE_HOST);
	$xml = new SimpleXMLElement($response);
	$reports = json_decode(json_encode($xml), TRUE);

	$countReportInfo = 0;
	if (isset($reports["GetReportListResult"]["NextToken"]) && $reports["GetReportListResult"]["NextToken"] != null)  
	{
		//	save next token for report list
		$field = array(
			'table' => 'amazon_reports',
			'lastInsertId' => 1
		);
		$data = array();
		$data['NextToken'] = $reports["GetReportListResult"]["NextToken"];
		$data['HasNext'] = $reports["GetReportListResult"]["HasNext"];
		$lastInsertID = SQLInsert($field, $data, 'shop', __FILE__, __LINE__);
		
		//	save report ids for the report list
		foreach($reports["GetReportListResult"]["ReportInfo"] as $reportInfo)
		{
			$field = array(
				'table' => 'amazon_reports_report'
			);							
			$data = array();
			$data['report_id'] = $lastInsertID;
			$data['ReportId'] = $reportInfo["ReportId"];
			$data['ReportType'] = $reportInfo["ReportType"];
			$data['ReportRequestId'] = $reportInfo["ReportRequestId"];
			$data['AvailableDate'] = $reportInfo["AvailableDate"];
			$data['Acknowledged'] = $reportInfo["Acknowledged"];
			SQLInsert($field, $data, 'shop', __FILE__, __LINE__);			
		}

		//	next report list
		if ($reports["GetReportListResult"]["NextToken"] != "") 
		{	
			$post_data = array();
			$post_data['url'] = "Action=GetReportListByNextToken&NextToken=" . $reports["GetReportListResult"]["NextToken"] . $urlTerm;
			$post_data['SecretKey'] = $AWS_SECRET_ACCESS_KEY;
			$post_data['method'] = $MWS_METHOD;
			$resultsByNextTocken = MarketplaceWebServiceSubmit($post_data, $MARKETPLACE_HOST);
			$xmlByNextTocken = new SimpleXMLElement($resultsByNextTocken);
			$arrayByNextTocken = json_decode(json_encode($xmlByNextTocken), TRUE);
	
			foreach($arrayByNextTocken["GetReportListByNextTokenResult"]["ReportInfo"] as $info)
			{	
				//report details
				$post_data = array();
				$post_data['url'] = "Action=GetReport&ReportId=" . $info["ReportId"] . $urlTerm;
				$post_data['SecretKey'] = $AWS_SECRET_ACCESS_KEY;
				$post_data['method'] = $MWS_METHOD;
				$resultsById = MarketplaceWebServiceSubmit($post_data, $MARKETPLACE_HOST);
				$xmlResultsById = new SimpleXMLElement($resultsById);
				$resultToArray = json_decode(json_encode($xmlResultsById), TRUE);
				if (isset($resultToArray["Message"]["ProcessingReport"]["Result"])) 
				{	
					$documentTransactionID = $resultToArray["Message"]["ProcessingReport"]["DocumentTransactionID"];
					$statusCode = $resultToArray['Message']['ProcessingReport']['StatusCode'];
					$processingSummary = $resultToArray['Message']['ProcessingReport']['ProcessingSummary'];	
					
					if (count($resultToArray['Message']['ProcessingReport']['Result']) > 0) 
					{
						foreach($resultToArray['Message']['ProcessingReport']['Result'] as $result)
						{
							//	save report message processing report result for report id
							$resultSKU = null;
							if (isset($result['AdditionalInfo']) && $result['AdditionalInfo'] != null) 
							{
								$resultSKU = $result['AdditionalInfo']['SKU'];
							}
							$field = array(
								'table' => 'amazon_report_infos'
							);							
							$data = array();
							$data['DocumentTransactionID'] = $documentTransactionID;
							$data['MessageID'] = $result['MessageID'];
							$data['ResultCode'] = $result['ResultCode'];
							$data['ResultMessageCode'] = $result['ResultMessageCode'];
							$data['ResultDescription'] = $result['ResultDescription'];
							$data['AdditionalInfoSKU'] = $resultSKU;
							SQLInsert($field, $data, 'shop', __FILE__, __LINE__);
							$countReportInfo++;
						}
					}
				}
			}
		}
	}
	$xml = "\n" . "<AmazonSubmissionResult>" . "\n";
	$xml.= '<marketplace>' . $amazonAccountsSites["name"] . '</marketplace>' . "\n";
	$xml.= '<insertReportInfo>' . $countReportInfo . '</insertReportInfo>' . "\n";
	$xml.= '</AmazonSubmissionResult>'. "\n";
	echo $xml;
<?php

/***
 *	@author: rlange@mapco.de
 *	Amazon Service for Feed Submissions get
 *	- get the latest feed submissions
 *
 *	@params
 *	- set
 *
*******************************************************************************/
$PATH = dirname(__FILE__);
require_once($PATH . '/Model/AmazonModel.php');
include("../functions/cms_core.php");

// keep post submit
$post = $_POST;

	/**
	 *	list amazon feed submissions
	 *
	 */	
	if ($post['action'] == 'listAmazonSubmissions') 
	{			
		$data = array();
		$data['from'] = 'amazon_feed_submissions
			LEFT JOIN amazon_feed_submission_message ON FSMFeedSubmissionId = FeedSubmissionId';
		$data['select'] = '*';
		$data['orderBy'] = 'SubmittedDate DESC';
		$amazonFeedSubmissions = SQLSelect($data['from'], $data['select'], 0, $data['orderBy'], 0, $post['limit'], 'shop',  __FILE__, __LINE__);
		if (count($amazonFeedSubmissions) > 0 ) 
		{	
			$xml = '';
			foreach($amazonFeedSubmissions as $amazonFeedSubmission)
			{
				$xml.= getXmlAmazonFeedSubmission($amazonFeedSubmission);
			}
			echo $xml;
		}
	}
	
	if ($post['action'] == 'showAmazonSubmissionResult')
	{
		$data = array();
		$data['from'] = 'amazon_feed_submission_result';
		$data['select'] = '*';
		$data['where'] = "
			FSRFeedSubmissionId = " . $post['FeedSubmissionId'];
		$amazonFeedSubmissionsResults = SQLSelect($data['from'], $data['select'], $data['where'], 0, 0, 0, 'shop',  __FILE__, __LINE__);
		if (count($amazonFeedSubmissionsResults) > 0 ) 
		{	
			$xml = '';
			foreach($amazonFeedSubmissionsResults as $amazonFeedSubmissionsResult)
			{
				$xml.= getXmlAmazonFeedSubmissionResult($amazonFeedSubmissionsResult);
			}
			$xml.= '<FeedSubmissionId>' . $post['FeedSubmissionId'] . '</FeedSubmissionId>' . "\n";
			echo $xml;
		}		
	}
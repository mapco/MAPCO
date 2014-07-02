<?php
	include("config.php");
	include("templates/".TEMPLATE_BACKEND."/header.php");
	
// keep get request and post submit
$post = $_POST;
$get = $_GET;	
?>

<script type="text/javascript">
	
	/* 
	 * amazon feed submissions list
	 * list the latest feed submissions fromm amazon report import
	 */	
	function listAmazonFeedSubmissions(FeedSubmissionId)
	{
		wait_dialog_show();
		var $post_data = new Object();
		$post_data['API'] = "amazon";
		$post_data['APIRequest'] = "AmazonSubmission";
		$post_data['action'] = 'listAmazonSubmissions',		
		$post_data['limit'] = '150';
		$.post('<?php echo PATH;?>soa2/', $post_data, function($data) {	
			try {$xml = $($.parseXML($data));} catch($err) {show_status2($err.message); return;}
			if ($xml.find('Ack').text() != 'Success') {show_status2($data); return;}
			
			$('#amazonFeedSubmissions').empty();
			amazonFeedSubmissionTable($xml);
			wait_dialog_hide();
		});			
	}
	
	/* 
	 * amazon feed submissions result show
	 * 
	 */		
	function getFeedSubmissionResult(FeedSubmissionId)
	{
		wait_dialog_show();
		var $post_data = new Object();
		$post_data['API'] = "amazon";
		$post_data['APIRequest'] = "AmazonSubmission";
		$post_data['action'] = "showAmazonSubmissionResult";
		$post_data['FeedSubmissionId'] = FeedSubmissionId;
		$.post('<?php echo PATH;?>soa2/', $post_data, function($data) {	
			try {$xml = $($.parseXML($data));} catch($err) {show_status2($err.message); return;}
			if ($xml.find('Ack').text() != 'Success') {show_status2($data); return;}
			
			$('#amazonFeedSubmissionsResult').empty();
			amazonFeedSubmissionResultTable($xml, FeedSubmissionId);
			wait_dialog_hide();
		});
	}
</script>	
	
<?php

	echo '<div id="breadcrumbs" class="breadcrumbs">';
	echo '<a href="backend_index.php">Backend</a>';
	echo ' &#187; <a href="backend_amazon_index.php">Amazon</a>';
	echo ' &#187; <a href="backend_amazon_feed_submission.php">Übertragungen</a>';
	echo '</div>';
	echo '<h1>Übertragungen</h1>';

	echo '
		<div id="content-wrapper">
			<div class="widget-logfile">
				<div id="amazonFeedSubmissions"></div>
			</div>
			<div class="widget-details">
				<div id="amazonFeedSubmissionsResult"></div>
			<div>
			<div class="clear"></div>
		</div>';

	//loading....
	echo '<div style="display:none;" id="feed_status_dialog"></div>';
	echo '<script src="//datatables.net/download/build/nightly/jquery.dataTables.js"></script>';
	echo '<script src="' . PATH . 'javascript/cms/DataTablesConfig.php" type="text/javascript"></script>';
	echo '<script src="' . PATH . 'javascript/amazon/TableViewSubmissions.php" type="text/javascript"></script>';	
	echo '<script type="text/javascript">listAmazonFeedSubmissions(' . $get['FeedSubmissionId'] . ');</script>';
	if (isset($get['FeedSubmissionId']) && $get['FeedSubmissionId'] != null)
	{
		echo '<script type="text/javascript">getFeedSubmissionResult(' . $get['FeedSubmissionId'] . ');</script>';	
	}
	include("templates/".TEMPLATE_BACKEND."/footer.php");
<?php

/**
 *	@author: rlange@mapco.de
 *	- Table Collection for Amazon Feed Submission View
 */
 
header('Content-type: text/javascript');

	//make dreamweaver highlight javascript
	if (true == false) { ?> <script type="text/javascript"> <?php }
?>
	/*
	 *--------------------------------------- Feed Submissions Tables ------------------------------------------------
	 */
	 
	/**
	 * show amazon feed submission table
	 */
	function amazonFeedSubmissionTable($xml)
	{
		var headline = $('<h3>Feed Listing</h3>');
		var table = $('<table id="datatableAmazonFeedSubmission" class="listing display"></table>');
		var thead = $('<thead></thead>');
		var tfoot = $('<tfoot></tfoot>');
		var tbody = $('<tbody></tbody>');
		var tr = $('<tr></tr>');
		var td = $('<td></td>');
		var th = $('<tr><th>ID</th><th>Type</th><th>Datum</th><th>Processed</th><th>Successful</th><th>Error</th><th>Warning</th><th>Status</th></tr>');

		thead.append(th);
		table.append(thead);
		var row;
		$xml.find('AmazonFeedSubmission').each(function()
		{	
			var FeedSubmissionId = $(this).find('FeedSubmissionId').text();
			row = '<tr id="' + FeedSubmissionId + '">'
					+ '<td class="center"><a href="javascript:getFeedSubmissionResult(' + FeedSubmissionId + ')">' + FeedSubmissionId + '</a></td>'
					+ '<td class="center">' + $(this).find('FeedType').text() + '</td>'
					+ '<td class="center">' + $(this).find('SubmittedDate').text() + '</td>'
					+ '<td class="center">' + $(this).find('MessagesProcessed').text() + '</td>'
					+ '<td class="center">' + $(this).find('MessagesSuccessful').text() + '</td>'
					+ '<td class="center">' + $(this).find('MessagesWithError').text() + '</td>'
					+ '<td class="center">' + $(this).find('MessagesWithWarning').text() + '</td>'
					+ '<td class="center">' + $(this).find('FeedProcessingStatus').text() + '</td>'
				+ '</tr>';
				tbody.append(row);
		});
		table.append(tbody);

		$('#amazonFeedSubmissions').empty();
		$('#amazonFeedSubmissions').append(headline);
		$('#amazonFeedSubmissions').append(table);

		$tableOptions = {
			//"fnSort": [[1,'desc']],
			"bJQueryUI": true,
			"sPaginationType": "full_numbers",
			"iDisplayLength": 25,
			"bSort": false,
    		"aLengthMenu": [[25, 50, 100], [25, 50, 100]]
		};
		$('#datatableAmazonFeedSubmission').dataTable($tableOptions);
	}
	
	/**
	 * show amazon feed submission result table
	 */
	function amazonFeedSubmissionResultTable($xml, FeedSubmissionId)
	{
		var headline = $('<h3>Bericht Details</h3>');
		var table = $('<table class="listing display"></table>');
		var thead = $('<thead></thead>');
		var tfoot = $('<tfoot></tfoot>');
		var tbody = $('<tbody></tbody>');
		var tr = $('<tr></tr>');
		var td = $('<td></td>');
		var th = $('<tr><th>Bericht für Report ID #:' + FeedSubmissionId + '</th></tr>');

		thead.append(th);
		table.append(thead);
		var row;
		$xml.find('AmazonFeedSubmissionResult').each(function()
		{	
			row = '<tr>'
					+ '<td>'
					+ '	<div style="width: 94%;" class="info">MessageID: ' + $(this).find('MessageID').text() + ' | ' + $(this).find('ResultCode').text()
					+ ' | MessageCode: ' + $(this).find('ResultMessageCode').text() + ' | SKU: ' + $(this).find('AdditionalInfoSKU').text()
					+ '	</div>'
					+ '	<div class="description">' + $(this).find('ResultDescription').text() + '</div>'
					+ '</td>'
				+ '</tr>';
				tbody.append(row);
		});
		table.append(tbody);

		$('#amazonFeedSubmissionsResult').empty();
		$('#amazonFeedSubmissionsResult').append(headline);
		$('#amazonFeedSubmissionsResult').append(table);
	}
	
	
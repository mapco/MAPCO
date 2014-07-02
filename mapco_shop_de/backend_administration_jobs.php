<?php
	include("config.php");
	include("templates/".TEMPLATE_BACKEND."/header.php");
?>
<style>
	.rulecol:hover .rulecolJobs {
		display:inline;
	}
	.rulecol .rulecolJobs {
		display:none;
	}
</style>

<script type="text/javascript">
	$.datepicker.regional['de'] = {
		clearText: 			'löschen',
	   clearStatus: 		'aktuelles Datum löschen',
	   closeText: 			'schließen', 
	   closeStatus: 		'ohne Änderungen schließen',
	   prevText:			'zurück', 
	   prevStatus: 			'letzten Monat zeigen',
	   nextText: 			'vor', 
	   nextStatus: 			'nächsten Monat zeigen',
	   currentText: 		'heute', 
	   currentStatus: 		'',
	   monthNames: 			['Januar','Februar','März','April','Mai','Juni','Juli','August','September','Oktober','November','Dezember'],
	   monthNamesShort: 	['Jan','Feb','Mär','Apr','Mai','Jun','Jul','Aug','Sep','Okt','Nov','Dez'],
	   monthStatus: 		'anderen Monat anzeigen',
	   yearStatus: 			'anderes Jahr anzeigen',
	   weekHeader: 			'Wo', 
	   weekStatus: 			'Woche des Monats',
	   dayNames: 			['Sonntag','Montag','Dienstag','Mittwoch','Donnerstag','Freitag','Samstag'],
	   dayNamesShort: 		['So','Mo','Di','Mi','Do','Fr','Sa'],
	   dayNamesMin: 		['So','Mo','Di','Mi','Do','Fr','Sa'],
	   dayStatus: 			'Setze DD als ersten Wochentag', 
	   dateStatus: 			'Wähle D, M d',
	   dateFormat: 			'dd.mm.yy', 
	   firstDay: 			1, 
	   initStatus:			'Wähle ein Datum', 
	   isRTL: 				false,
	   changeMonth: 		true,
	   changeYear: 			true,
	   showOtherMonths:		true,
	   selectOtherMonths:	true
	};

	function view(view)
	{
		var group = 0;	
		wait_dialog_show();	
		$.post("<?php echo PATH; ?>soa/", {API: "jobs", Action: "JobsView", view:view },
			function (data)
			{
				if (view == "menu") {
					$("#menu").html(data);
				}
				if (view == "jobs" || view == "rules" || view == "logfile") {
					$("#detail").html(data);
					wait_dialog_hide();
					var i = 0;
					var key = new Array();
					$("h3").find('.btn').each(function() { 
						key[i] = parseInt(i + 1);
						i++;
					});
					
					for(n in key) 
					{
						(function(k) {
							$('#jobsgroup-' + k).click(function() 
							{	
								$('.set').hide();
								$('.groupID' + k).show();							
							});
						 })(key[n]);
					}				
				}
			}
		);
	}
	
	/**
	 * view joblogfile by job id
	 */
	function viewLogfileByJobId(id_job, mintime, maxtime)
	{	
		var $post_data = new Object();
		$post_data['API'] = "cms";
		$post_data['APIRequest'] = "JobLogfileGet";
		$post_data['jobID'] = id_job;
		$post_data['mintime'] = mintime;
		$post_data['maxtime'] = maxtime;
		$.post('<?php echo PATH;?>soa2/', $post_data,
		function($data)
		{
			try {$xml = $($.parseXML($data));} catch($err) {show_status2($err.message); return;}
			if ($xml.find('Ack').text() != 'Success') {show_status2($data); return;}
			
			$("#logfile_show_button").click(function() 
			{
				var date_from = $("#date_from").datepicker("getDate");
				var date_to = $("#date_to").datepicker("getDate");
				var mintime = (date_from.getTime() / 1000).toFixed(0);
				var maxtime = (date_to.getTime() / 1000).toFixed(0);				
				viewLogfileByJobId(id_job, mintime, maxtime);
			});	

			$("#logfile_remove_button").click(function() 
			{
				$('#message_dialog').empty();
				$('#message_dialog').html('<?php echo t("Wollen Sie diesen Zeitraum wirklich löschen? Sie können dies nicht rückgängig machen!"); ?>');
				$('#message_dialog').dialog({	
						buttons:[{ 
								text: "<?php echo t("Ok"); ?>", click: function() {
									$(this).dialog("close");
									removeLogfileByJobId(id_job, mintime, maxtime);	
								} 	
							}],
						closeText: "<?php echo t("Fenster schließen"); ?>",
						hide: { effect: 'drop', direction: "up" },
						modal: true,
						resizable: false,
						show: { effect: 'drop', direction: "up" },
						title: "<?php echo t("Achtung!"); ?>",
						width: 300
					});
			});
			
			viewLogfileTable($xml);
		});
	}
	
	/**
	 * view logfile by define a peek value
	 */	
	function viewLogfileByJobIdAndPeek(id_job)
	{
		var $post_data = new Object();
		$post_data['API'] = "cms";
		$post_data['APIRequest'] = "JobLogfileGet";
		$post_data['jobID'] = id_job;
		$post_data['peek'] = true;
		$.post('<?php echo PATH;?>soa2/', $post_data,
		function($data)
		{
			try {$xml = $($.parseXML($data));} catch($err) {show_status2($err.message); return;}
			if ($xml.find('Ack').text() != 'Success') {show_status2($data); return;}
			
			viewLogfileTable($xml);
		});
	}
	
	/**
	 * view logfile table
	 */	
	function viewLogfileTable($xml)
	{
		if ($xml.find('jobfilePeekDuration').text() > 0) { addPeekClass = 'widget-light peek'; } else { addPeekClass = 'widget-light';}		
		var jobfileTotalDuration = '<label>Laufzeit gesamt</label>' + $xml.find('jobfileTotalDuration').text();
		var jobfileAverageDuration = '<label>Durchschnitt</label>' + $xml.find('jobfileAverageDuration').text();
		var jobfilePeekDuration = '<label>Peek</label>' + $xml.find('jobfilePeekDuration').text();
		statBlock = '<div class="widget-light">' + jobfileTotalDuration + '</div>';
		statBlock += '<div class="widget-light">' + jobfileAverageDuration + '</div>';
		statBlock += '<div class="' + addPeekClass + '">' + jobfilePeekDuration + '</div>';
			
		var headline = $('<h3>Logfile</h3>');
		var table = $('<table class="logfile"></table>');
				var thead = $('<thead></thead>');
				var tbody = $('<tbody></tbody>');
				var tr = $('<tr></tr>');
				var td = $('<td></td>');
					var th = $('<th>ID</th><th>Startzeit</th><th>Dauer</th><th>Manuell</th><th>Antwort</th>');
					thead.append(th)
					table.append(thead);
					var row;
					$xml.find('log').each(function()
					{
						row = '<tr><td class="center">' + $(this).find('id').text() + '</td>'
							+ '<td class="center">' + $(this).find('startTime').text() + '</td>'
							+ $(this).find('endTime').text()
							+ '<td class="center">' + $(this).find('manual').text() + '</td>'
							+ '<td><a href="javascript: $(\'#response' + $(this).find('id').text() + '\').toggle()' + ';">Anzeigen</a>'
							+ '<div style="display:none;" id="response' + $(this).find('id').text() + '">' + $(this).find('response').text() + '</div>'
							+ '</td></tr>'; 
							tbody.append(row);
					});
		$('#logfile').empty();		
		$('#logfile').append(headline);
		$('#logfile').append(statBlock);
		table.append(tbody);
		$('#logfile').append(table);		
	}
	
	/**
	 * remove jobfiles by job id and range time
	 */
	function removeLogfileByJobId(id_job, mintime, maxtime)
	{
		var $post_data = new Object();
		$post_data['API'] = "cms";
		$post_data['APIRequest'] = "JobLogfileRemove";
		$post_data['jobID'] = id_job;
		$post_data['mintime'] = mintime;
		$post_data['maxtime'] = maxtime;
		
		$.post('<?php echo PATH;?>soa2/', $post_data, 
		function($data)
		{
			try {$xml = $($.parseXML($data));} catch($err) {show_status2($err.message); return;}
			if ($xml.find('Ack').text() != 'Success') {show_status2($data); return;}
			viewJobById($xml.find('job').text());
		});
	}

	/**
	 * view job by job id
	 */
	function viewJobById(id_job, mintime, maxtime)
	{
		wait_dialog_show();		
		
		var $post_data = new Object();
		$post_data['API'] = "cms";
		$post_data['APIRequest'] = "JobGet";
		$post_data['jobID'] = id_job;
		
		$.post('<?php echo PATH;?>soa2/', $post_data, 
			function($data)
			{
				try {$xml = $($.parseXML($data));} catch($err) {show_status2($err.message); return;}
				if ($xml.find('Ack').text() != 'Success') {show_status2($data); return;}
				$("#detail").empty();
				var main = $('#detail');
				main.empty();
				// details widget
				var divDetails = $('<div class="widget-details"></div>');
				var textDetails = $('<h3>Details - ' + $xml.find('name').text() + '</h3><p>' + $xml.find('description').text() + '</p>');
				var ulDetails = $('<ul></ul>');
				
				var liDetails = '<li><span>API</span>' + $xml.find('api').text() + '</li>'
					+ '<li><span>Service</span>' + $xml.find('service').text() + '</li>'
					+ '<li><span>Post Var</span>' + $xml.find('postVar').text() + '</li>'
					+ '<li><span>Aktiv</span>' + $xml.find('active').text() + '</li>'
					+ '<li><span>Regel</span>' + $xml.find('jobRule').text() + '</li>'
					+ '<li><span>Erste Bearbeitung</span>' + $xml.find('firstmod').text() + $xml.find('firstmodUser').text() + '</li>'
					+ '<li><span>zuletzt bearbeitet</span>' + $xml.find('lastmod').text() + $xml.find('lastmodUser').text() + '</li>';
				
				ulDetails.append(liDetails);
				divDetails.append(textDetails);
				divDetails.append(ulDetails);
				main.append(divDetails);
				
				//stats widget
				var firstCall = '<label>First Call</label>' + $xml.find('firstCall').text();
				var lastCall = '<label>Last Call</label>' + $xml.find('lastCall').text();
				var nextCall = '<label>Next Call</label>' + $xml.find('nextCall').text();
				var totalCall = '<label>Total Calls</label>' + $xml.find('totalCalls').text();
				
				var totalDuration = '<label>Laufzeit gesamt</label>' + $xml.find('totalDuration').text();
				var averageDuration = '<label>Durchschnitt</label>' + $xml.find('averageDuration').text();
				var peekDuration = '<label>Peek</label>' + $xml.find('peekDuration').text();
				
				statBlock = '';
				statBlock += '<div class="widget-important">' + firstCall + '</div>';
				statBlock += '<div class="widget-success">' + lastCall + '</div>';
				statBlock += '<div class="widget-warning">' + nextCall + '</div>';
				statBlock += '<div class="widget-info">' + totalCall + '</div>';
				statBlock += '<div class="hr hr10"></div>';
				statBlock += '<div class="widget-light">' + totalDuration + '</div>';
				statBlock += '<div class="widget-light">' + averageDuration + '</div>';
				statBlock += '<div class="widget-light"><input type="button" class="info-corner-button" id="peek_duration_button" value="!">' + peekDuration + '</div>';
				
				var divStats = $('<div class="widget-stats"></div>');
				var textStats = $('<h3>Statistik</h3>' + statBlock);
				divStats.append(textStats);
				main.append(divStats);
				main.append('<div class="clear"></div>');
				
				// widget information
				var divInformation = $('<div style="min-height: 500px;" class="widget-details"></div>');
				var textInformation = $('<h3>Information</h3>');
				divInformation.append(textInformation);
				main.append(divInformation);
								
				// logfile range select field
				var rangeSelect = $('<div id="range-select" class="widget-range-select"></div>');
				main.append(rangeSelect);
				viewRangeSelectField();
				
				//logfile widget
				var divLogfile = $('<div id="logfile" class="widget-logfile"></div>');
				main.append(divLogfile);
				viewLogfileByJobId(id_job);
				
				$("#peek_duration_button").click(function() {
					$('#logfile').empty();
					viewLogfileByJobIdAndPeek(id_job);
				});
								
				wait_dialog_hide();
			});
	}
	
	/**
	 * view logfile range select field
	 */
	function viewRangeSelectField()
	{	
		html = '';
		html += '<h3>Zeitraum</h3>'
		html += '<div id="date_range" data="' + $xml.find('job').text() + '" class="datepicker">';
		html += '	<p class="information">Range festlegen</p>';		
		html += '	<p class="field">vom:</p><input type="text" id="date_from">';
		html += '	<p class="field">bis:</p><input type="text" id="date_to">';
		html += '	<input type="button" id="logfile_show_button" value="Start">';
		html += '	<input type="button" id="logfile_remove_button" value="Zeitraum löschen">';
		html += '</div>';
		$('#range-select').append(html);
		
		$("#date_from").datepicker($.datepicker.regional['de']);
		$("#date_to").datepicker($.datepicker.regional['de']);
		$("#date_from" ).datepicker( "setDate", new Date());
		$("#date_to" ).datepicker( "setDate", new Date());

		$( "#date_from" ).datepicker({
			defaultDate: "+1w",
			changeMonth: true,
			numberOfMonths: 1,
			onClose: function( selectedDate ) {
				$( "#date_to" ).datepicker( "option", "minDate", selectedDate );
			}	
		});	
		$( "#date_to" ).datepicker({
			defaultDate: "+1w",
			changeMonth: true,
			numberOfMonths: 1,
			onClose: function( selectedDate ) {
				$( "#date_from" ).datepicker( "option", "maxDate", selectedDate );
			}
		});
	}
	
	/**
	 * 
	 */
	function setAddRule_desc_addRulesDays()
	{
		if ($("#addRulesDaysWeek").attr("checked"))
		{
			$("#AddRule_desc_addRulesDays").html("<small>Tage der Woche <i>1-7</i>, Bsp.: 1,3,5,6</small>");
		}
		if ($("#addRulesDaysMonth").attr("checked"))
		{
			$("#AddRule_desc_addRulesDays").html("<small>Tage des Monats <i>1-31</i>, Bsp.: 1,8,15,22,29</small>");
		}
	}

	/**
	 * 
	 */
	function AddRule_desc_addRulesTimes()
	{
		if ($("#addRulesTime").attr("checked"))
		{
			$("#AddRule_desc_addRulesTimes").html("<small>Uhrzeit, Bsp.: 0700 (für 7:00 UHR)</small>");
			$("#AddRule_desc_addRulesPreriods").html("<small>&nbsp;</small>");
			$("#addRulesPreriods").hide();
		}
		if ($("#addRulesPeriod").attr("checked"))
		{
			$("#AddRule_desc_addRulesTimes").html("<small>Zeitspanne, Bsp.: 0700-1630 (für 7:00-16:30 UHR</small>");
			$("#AddRule_desc_addRulesPreriods").html("<small>Abstand zwischen den Ausführungen in Minuten, Bsp: 30</small>");
			$("#addRulesPreriods").show();
		}
	}

	/**
	 * 
	 */
	function AddRule()
	{
		$("#addRulesName").val("");
		$("#addRulesRule").val("");
		setAddRule_desc_addRulesDays();
		AddRule_desc_addRulesTimes();
		$("#addRulesDialog").dialog
		({	buttons:
			[
				{ text: "Speichern", click: function() {  AddRuleSave(); } },
				{ text: "Abbrechen", click: function() { $(this).dialog("close"); } }
			],
			closeText:"Fenster schließen",
			hide: { effect: 'drop', direction: "up" },
			modal: true,
			resizable: false,
			show: { effect: 'drop', direction: "up" },
			title: "Neue Ausführungsregel einfügen",
			width: 500
		});	
	}

	/**
	 * 
	 */
	function AddRuleSave()
	{
		var ruleName=$("#addRulesName").val();
		if (ruleName=="") {alert("Bitte einen Namen für die Ausführungsregel eingeben"); $("#addRulesName").focus(); return;}
		var addRulesDays=$("#addRulesDays").val();
		if (addRulesDays=="") {alert("Bitte die Tage eingeben, an denen ein Job ausgeführt werden soll"); $("#addRulesDays").focus(); return;}
		var addRulesTimes=$("#addRulesTimes").val();
		if (addRulesTimes=="") {
			if ($("#addRulesTime").attr("checked"))	{alert("Bitte die Uhrzeit eingeben, an denen ein Job ausgeführt werden soll"); $("#addRulesTimes").focus(); return;}
			if ($("#addRulesPeriod").attr("checked")) {alert("Bitte den Zeitraum (Uhrzeit von-bis) eingeben, an denen ein Job wiederkehrend ausgeführt werden soll"); $("#addRulesTimes").focus(); return;}
		}
		var addRulesPreriods=$("#addRulesPreriods").val();
		if (addRulesPreriods=="" && $("#addRulesPeriod").attr("checked")) {alert("Bitte den Zeitabstand eingeben, der zwischen den wiederkehrenden Ausfürungen eines Jobs liegen soll"); $("#addRulesPreriods").focus(); return;}

		var rule="<rule>";
		
			if ($("#addRulesDaysWeek").attr("checked")) {
				rule+="<week>"+addRulesDays+"</week>";
			}	
			if ($("#addRulesDaysMonth").attr("checked")) {
				rule+="<month>"+addRulesDays+"</month>";
			}
	
			if ($("#addRulesTime").attr("checked")) {
				rule+="<daytime>"+addRulesTimes+"</daytime>";
			}
			if ($("#addRulesPeriod").attr("checked")) {
				rule+="<dayperiod>"+addRulesTimes+","+$("#addRulesPreriods").val()+"</dayperiod>";
			}
		rule+="</rule>";
		
		wait_dialog_show();
		$.post("<?php echo PATH; ?>soa/", {API: "jobs", Action: "JobsAddRule", ruleName:ruleName, rule:rule },
			function (data)
			{
				var $xml=$($.parseXML(data));
				var Ack = $xml.find("Ack").text();
				if (Ack=="Success")  {
					wait_dialog_hide();
					$("#addRulesDialog").dialog("close");
					view("rules");
				} else {
					show_status2(data);
					return;
				}
			}
		);
	}
		
	/**
	 * 
	 */
	function UpdateRule(id_JobRule)
	{
		wait_dialog_show();
		$.post("<?php echo PATH; ?>soa/", {API: "jobs", Action: "JobsGetRule", id_JobRule:id_JobRule },
			function (data)
			{
				var $xml=$($.parseXML(data));
				var Ack = $xml.find("Ack").text();
				if (Ack=="Success") 
				{
					$("#addRulesName").val($xml.find("RulesName").text());
					var tmp=$xml.find("rule").text();
					var rule=$($.parseXML(tmp));
					var week=rule.find("week").text();
					var month=rule.find("month").text();
					
					if (week.length>0) {
						$("#addRulesDaysWeek").attr("checked", "check");
						$("#addRulesDays").val(week);
					}
					
					if (month!="") {
						$("#addRulesDaysMonth").attr("checked", "check");
						$("#addRulesDays").val(month);
					}

					var daytime=rule.find("daytime").text();
					var dayperiod=rule.find("dayperiod").text();
					if (daytime!="") {
						$("#addRulesTime").attr("checked", "check");
						$("#addRulesTimes").val(daytime);
						$("#addRulesPreriods").hide();
					}
					if (dayperiod!="") {
						$("#addRulesPeriod").attr("checked", "check");
						var parts=dayperiod.split(",");
						$("#addRulesTimes").val(parts[0]);
						$("#addRulesPreriods").val(parts[1]);
					}
				
					wait_dialog_hide();
					$("#addRulesDialog").dialog
						({	buttons:
							[
								{ text: "JobRule speichern", click: function() {  UpdateRuleSave(id_JobRule); } },
								{ text: "Abbrechen", click: function() { $(this).dialog("close"); } }
							],
							closeText:"Fenster schließen",
							hide: { effect: 'drop', direction: "up" },
							modal:true,
							resizable:false,
							show: { effect: 'drop', direction: "up" },
							title:"Ausführungsregel bearbeiten",
							width:500
					});	
				}
				else 
				{
					wait_dialog_hide();
					show_status2(data);
					return;
				}
			}
		);

	}

	/**
	 * 
	 */
	function UpdateRuleSave(id_JobRule)
	{
		var ruleName=$("#addRulesName").val();
		if (ruleName=="") {alert("Bitte einen Namen für die Ausführungsregel eingeben"); $("#addRulesName").focus(); return;}
		var addRulesDays=$("#addRulesDays").val();
		if (addRulesDays=="") {alert("Bitte die Tage eingeben, an denen ein Job ausgeführt werden soll"); $("#addRulesDays").focus(); return;}
		var addRulesTimes=$("#addRulesTimes").val();
		if (addRulesTimes=="") {
			if ($("#addRulesTime").attr("checked"))	{alert("Bitte die Uhrzeit eingeben, an denen ein Job ausgeführt werden soll"); $("#addRulesTimes").focus(); return;}
			if ($("#addRulesPeriod").attr("checked")) {alert("Bitte den Zeitraum (Uhrzeit von-bis) eingeben, an denen ein Job wiederkehrend ausgeführt werden soll"); $("#addRulesTimes").focus(); return;}
		}
		var addRulesPreriods=$("#addRulesPreriods").val();
		if (addRulesPreriods=="" && $("#addRulesPeriod").attr("checked")) {alert("Bitte den Zeitabstand eingeben, der zwischen den wiederkehrenden Ausfürungen eines Jobs liegen soll"); $("#addRulesPreriods").focus(); return;}

		var rule="<rule>";
		
			if ($("#addRulesDaysWeek").attr("checked")) {
				rule+="<week>"+addRulesDays+"</week>";
			}	
			if ($("#addRulesDaysMonth").attr("checked")) {
				rule+="<month>"+addRulesDays+"</month>";
			}
	
			if ($("#addRulesTime").attr("checked")) {
				rule+="<daytime>"+addRulesTimes+"</daytime>";
			}
			if ($("#addRulesPeriod").attr("checked")) {
				rule+="<dayperiod>"+addRulesTimes+","+$("#addRulesPreriods").val()+"</dayperiod>";
			}
		rule+="</rule>";
		
		wait_dialog_show();
		$.post("<?php echo PATH; ?>soa/", {API: "jobs", Action: "JobsUpdateRule", id_JobRule:id_JobRule, ruleName:ruleName, rule:rule },
			function (data)
			{
				var $xml=$($.parseXML(data));
				var Ack = $xml.find("Ack").text();
				if (Ack=="Success") {
					wait_dialog_hide();
					$("#addRulesDialog").dialog("close");
					view("rules");
				} else {
					show_status2(data);
					return;
				}
			}
		);
	}
	
	/**
	 * 
	 */	
	function deleteRule(id_JobRule)
	{
		wait_dialog_show();
		$.post("<?php echo PATH; ?>soa/", {API: "jobs", Action: "JobsGetRule", id_JobRule:id_JobRule },
			function (data)
			{
				var $xml=$($.parseXML(data));
				var Ack = $xml.find("Ack").text();
				if (Ack=="Success") 
				{
					wait_dialog_hide();
					$("#deleteRulesDialog").html("Soll die Ausführungsregel: <b>"+$xml.find("RulesName").text()+"</b> wirklich gelöscht werden?");
					$("#deleteRulesDialog").dialog
					({	buttons:
						[
							{ text: "Löschen", click: function() {  DodeleteRule(id_JobRule); } },
							{ text: "Abbrechen", click: function() { $(this).dialog("close"); } }
						],
						closeText:"Fenster schließen",
						hide: { effect: 'drop', direction: "up" },
						modal:true,
						resizable:false,
						show: { effect: 'drop', direction: "up" },
						title:"Ausführungsregel löschen",
						width:500
					});	
				} else {
					wait_dialog_hide();
					show_status2(data);
					return;
				}
			}
		);

	}
	
	/**
	 * 
	 */	
	function DodeleteRule(id_JobRule)
	{
		wait_dialog_show();
		$.post("<?php echo PATH; ?>soa/", {API: "jobs", Action: "JobsDeleteRule", id_JobRule:id_JobRule },
			function (data)
			{
				var $xml=$($.parseXML(data));
				var Ack = $xml.find("Ack").text();
				if (Ack=="Success") {
					wait_dialog_hide();
					$("#deleteRulesDialog").dialog("close");
					show_status("Ausführungsregel wurde gelöscht");
					view("rules");
				} else {
					wait_dialog_hide();
					show_status2(data);
					return;
				}
			}
		);
	}
	
	function AddJob()
	{
		$("#addJobName").val("");
		$("#addJobDesc").val("");
		$("#addJobAPI").val("");
		$("#addJobService").val("");
		$("#addJobPostVars").val("");
		$("#addJobRule").val(0);

		$("#addJobDialog").dialog
			({	buttons:
				[
					{ text: "Speichern", click: function() {  AddJobSave(); } },
					{ text: "Abbrechen", click: function() { $(this).dialog("close"); } }
				],
				closeText:"Fenster schließen",
				hide: { effect: 'drop', direction: "up" },
				modal:true,
				resizable:false,
				show: { effect: 'drop', direction: "up" },
				title:"Neuen Job anlegen",
				width:700
			}
		);	
	}

	/**
	 * 
	 */
	function AddJobSave()
	{
		var JobName = $("#addJobName").val();
		if (JobName=="") {alert("Bitte einen Namen für den Job eingeben"); $("#addJobName").focus(); return;}
		var JobAPI = $("#addJobAPI").val();
		if (JobAPI=="") {alert("Bitte die API für den Job eingeben"); $("#addJobAPI").focus(); return;}
		var JobService = $("#addJobService").val();
		if (JobService=="") {alert("Bitte den Servic für den Job eingeben"); $("#addJobService").focus(); return;}
		var JobRule = $("#addJobRule").val();
		if (JobRule==0) {alert("Bitte eine Ausführungsregel für den Job eingeben"); $("#addJobRule").focus(); return;}
		var JobDesc=$("#addJobDesc").val();
		var JobPostVars=$("#addJobPostVars").val();
		var JobActive=$("#addJobActive").val();
		
		wait_dialog_show();
		
		$.post("<?php echo PATH; ?>soa/", {API: "jobs", Action: "JobsAddJob", JobName:JobName, JobAPI:JobAPI, JobService:JobService, JobRule:JobRule, JobDesc:JobDesc, JobPostVars:JobPostVars, JobActive:JobActive},
			function (data)
			{
				var $xml=$($.parseXML(data));
				var Ack = $xml.find("Ack").text();
				if (Ack=="Success") {
					wait_dialog_hide();
					$("#addJobDialog").dialog("close");
					show_status("Job wurde erfolgreich angelegt");
					view("jobs");
				} else {
					wait_dialog_hide();
					show_status2(data);
					return;
				}
			}
		);
	
	}
	
	/**
	 *	Update Job by Job Id
	 */	
	function UpdateJob(id_job)
	{
		wait_dialog_show();
		$.post("<?php echo PATH; ?>soa/", {API: "jobs", Action: "JobsGetJob", id_job:id_job },
			function (data)
			{
				var $xml=$($.parseXML(data));
				var Ack = $xml.find("Ack").text();
				if (Ack == "Success") 
				{
					$("#addJobName").val($xml.find("JobName").text());
					$("#addJobDesc").val($xml.find("JobDescription").text());
					$("#addJobAPI").val($xml.find("JobAPI").text());
					$("#addJobService").val($xml.find("JobService").text());
					$("#addJobPostVars").val($xml.find("JobPost_Vars").text());
					$("#addJobPostVarsList").val($xml.find("JobPost_VarsList").text());
					$("#addJobLastCall").val($xml.find("JobLastCall").text());
					$("#addJobLastCall").show();
					$("#addJobNextCall").val($xml.find("JobNextCall").text());
					$("#addJobNextCall").show();
					$("#addJobActive").val($xml.find("JobActive").text());
					$("#addJobRule").val($xml.find("JobRule").text());
					if ($xml.find("JobGroups").text() == "") {
						$("#addJobGroups").val('0');
					} else {
						$("#addJobGroups").val($xml.find("JobGroups").text());
					}

					wait_dialog_hide();
					$("#addJobDialog").dialog
						({	buttons:
							[
								{ text: "Speichern", click: function() {  UpdateJobSave(id_job); } },
								{ text: "Abbrechen", click: function() { $(this).dialog("close"); } }
							],
							closeText: "Fenster schließen",
							hide: { effect: 'drop', direction: "up" },
							modal: true,
							resizable: false,
							show: { effect: 'drop', direction: "up" },
							title: "Job bearbeiten",
							width: 700
					});	
				}
				else 
				{
					wait_dialog_hide();
					show_status2(data);
					return;
				}
			}
		);

	}

	/**
	 *	Save Update Job by Job Id
	 */
	function UpdateJobSave(id_job)
	{
		var JobName = $("#addJobName").val();
		if (JobName == "") {alert("Bitte einen Namen für den Job eingeben"); $("#addJobName").focus(); return;}
		var JobAPI = $("#addJobAPI").val();
		if (JobAPI == "") {alert("Bitte die API für den Job eingeben"); $("#addJobAPI").focus(); return;}
		var JobService = $("#addJobService").val();
		if (JobService == "") {alert("Bitte den Service für den Job eingeben"); $("#addJobService").focus(); return;}
		var JobRule = $("#addJobRule").val();
		if (JobRule == 0) {alert("Bitte eine Ausführungsregel für den Job eingeben"); $("#addJobRule").focus(); return;}
		var JobDesc = $("#addJobDesc").val();
		var JobPostVars = $("#addJobPostVars").val();
		var JobPostVarsList = $("#addJobPostVarsList").val();
		var JobActive = $("#addJobActive").val();
		var JobGroups = $("#addJobGroups").val();
		
		var JobLastCall=$("#addJobLastCall").val();	
		var JobNextCall=$("#addJobNextCall").val();	
		
		wait_dialog_show();
		$.post("<?php echo PATH; ?>soa/", {API: "jobs", Action: "JobsUpdateJob", id_job:id_job, JobName:JobName, JobAPI:JobAPI, JobService:JobService, JobRule:JobRule, JobDesc:JobDesc, JobPostVars:JobPostVars, JobPostVarsList:JobPostVarsList, JobActive:JobActive, JobGroups:JobGroups, JobLastCall:JobLastCall, JobNextCall:JobNextCall},
			function (data)
			{
				var $xml=$($.parseXML(data));
				var Ack = $xml.find("Ack").text();
				if (Ack=="Success") {
					wait_dialog_hide();
					$("#addJobDialog").dialog("close");
					$("#addJobLastCall").hide();
					$("#addJobNextCall").hide();
					view("jobs");
				} else {
					show_status2(data);
					return;
				}
			}
		);
	}

	/**
	 *	Delete Job by Job Id
	 */
	function deleteJob(id_job)
	{
		wait_dialog_show();
		$.post("<?php echo PATH; ?>soa/", {API: "jobs", Action: "JobsGetJob", id_job:id_job },
			function (data)
			{
				var $xml=$($.parseXML(data));
				var Ack = $xml.find("Ack").text();
				if (Ack=="Success") 
				{
					wait_dialog_hide();
					$("#deleteJobDialog").html("Soll der Job: <b>"+$xml.find("JobName").text()+"</b> wirklich gelöscht werden?");
					$("#deleteJobDialog").dialog
					({	buttons:
						[
							{ text: "Löschen", click: function() {  DodeleteJob(id_job); } },
							{ text: "Abbrechen", click: function() { $(this).dialog("close"); } }
						],
						closeText:"Fenster schließen",
						hide: { effect: 'drop', direction: "up" },
						modal:true,
						resizable:false,
						show: { effect: 'drop', direction: "up" },
						title:"Job löschen",
						width:500
					});	
				}
				else 
				{
					wait_dialog_hide();
					show_status2(data);
					return;
				}
			}
		);

	}
	
	/**
	 *	Execute delete Job by Job Id
	 */	
	function DodeleteJob(id_job)
	{
		wait_dialog_show();
		$.post( "<?php echo PATH; ?>soa/", {API: "jobs", Action: "JobsDeleteJob", id_job:id_job },
			function (data)
			{
				var $xml=$($.parseXML(data));
				var Ack = $xml.find("Ack").text();
				if (Ack=="Success") 
				{
					wait_dialog_hide();
					$("#deleteJobDialog").dialog("close");
					show_status("Job wurde gelöscht");
					view("jobs");
				}
				else 
				{
					    wait_dialog_hide();
					show_status2(data);
					return;
				}
			}
		);
	}
	
	/**
	 *	Show Job by Id
	 */	
	function JobAusgabe(id_job)
	{
		$("#JobAusgabeText").val("");
		$("#JobAusgabe").dialog
		({	buttons:
			[
				{ text: "Job erneut aufrufen", click: function() { JobAusgabe(id_job); } },
				{ text: "OK", click: function() { $(this).dialog("close"); } }
			],
			closeText: "Fenster schließen",
			hide: { effect: 'drop', direction: "up" },
			modal: true,
			resizable: false,
			show: { effect: 'drop', direction: "up" },
			title: "Ausgabe von Job",
			width: 700
		});	
		wait_dialog_show();

		$.post("<?php echo PATH; ?>soa/", {API: "jobs", Action: "JOBHANDLER", id_job:id_job },
			function ($data)
			{
				wait_dialog_hide();
				try
				{
					$xml = $($.parseXML($data));
				}
				catch (err)
				{
					$("#JobAusgabeText").val(err.message);
					return;
				}
				$ack = $xml.find("Ack");
				if ( $ack.text()!="Success" )
				{
					$("#JobAusgabeText").val($data);
					return;
				}

				$("#JobAusgabeText").val($xml.find("Response").text());
			}
		);
	}
</script>

<?php	

	//BREADCRUMBS
	$breadcrumbs = '
	<div id="breadcrumbs" class="breadcrumbs">
		<a href="backend_index.php">Backend</a>
		&#187; <a href="backend_administration_index.php">Administration</a>
		&#187; Jobs
	</div>
	<h1>Jobs</h1>';
	echo $breadcrumbs;

	//CONTENT WRAPPER
	echo '<div id="content-wrapper">';
		echo '<div id="menu"></div>';	
		echo '<div id="detail"></div>';
	echo '</div>';
	
	//ADD RULE DIALOG
	echo '<div id="addRulesDialog" style="display:none">';
	echo '<table>';
	echo '<tr>';
		echo '<td><b>Name</b></td>';
		echo '<td><input type="text" name="name" id="addRulesName" size="40" />';
	echo '</tr><tr>';
		echo '<td><b>Ausführungstage</td>';
		echo '<td>';
			echo '<input type="radio" id="addRulesDaysWeek" name="days[]" value="week" checked="checked" onclick="setAddRule_desc_addRulesDays();"\>Woche';
			echo '<input type="radio" name="days[]" id="addRulesDaysMonth" value="month" disabled="disabled" onclick="setAddRule_desc_addRulesDays();"\>Monat<br />';
			echo '<span id="AddRule_desc_addRulesDays"></span><br />';
			echo '<input type="text" id="addRulesDays" size="20" \>';
		echo '</td>';
	echo '</tr><tr>';
		echo '<td><b>Ausführungszeitpunkt(e)</td>';
		echo '<td>';
			echo '<input type="radio" name="times[]" id="addRulesTime" value="time" onclick="AddRule_desc_addRulesTimes();" checked="checked"\>Zeitpunkt';
			echo '<input type="radio" name="times[]" id="addRulesPeriod" value="period" onclick="AddRule_desc_addRulesTimes();"\>Periode<br />';
			echo '<div><span id="AddRule_desc_addRulesTimes"></span><br /><input type="text" id="addRulesTimes" size="20" \></div>';
			echo '<div><span id="AddRule_desc_addRulesPreriods"></span><br /><input type="text" id="addRulesPreriods" size="20" \></div>';
		echo '</td>';
	echo '</tr>';
	echo '</table>';
	echo '</div>';
	
	//DELETE RULE DIALOG
	echo '<div id="deleteRulesDialog" style="display:none"></div>';
	
	//ADD/EDIT JOB DIALOG
	echo '<div id="addJobDialog" style="display:none">';
	echo '<table>';
	echo '<tr>';
		echo '<td><b>Name</b></td>';
		echo '<td><input type="text" name="name" id="addJobName" size="40" />';
	echo '</tr><tr>';
		echo '<td><b>Beschreibung</td>';
		echo '<td>';
			echo '<textarea name="desc" id="addJobDesc" cols="40" rows="4"></textarea>';
		echo '</td>';
	echo '</tr><tr>';
		echo '<td><b>API</td>';
		echo '<td>';
			echo '<input type="text" name="api" id="addJobAPI" size="40" \>';
		echo '</td>';
	echo '</tr><tr>';
		echo '<td><b>Servicename</td>';
		echo '<td>';
			echo '<input type="text" name="service" id="addJobService" size="40" \>';
		echo '</td>';
	echo '</tr><tr>';
		echo '<td><b>POST-Variablen</td>';
		echo '<td>';
			echo '<textarea name="postvars" id="addJobPostVars" cols="40" rows="4"></textarea>';
		echo '</td>';
	echo '</tr><tr>';
		echo '<td><b>POST-Params</td>';
		echo '<td>';
			echo '<textarea name="postvarslist" id="addJobPostVarsList" cols="40" rows="4"></textarea>';
		echo '</td>';
	echo '</tr><tr>';
		echo '<td><b>Gruppe zuweisen</td>';
		echo '<td>';
			echo '
				<select name="groups" id="addJobGroups" size="1">
					<option value=0>Bitte wählen...</option>';
				//$groups = SQLSelect('jobs_groups', '*', 0, 'jobgroup_name ASC', 0, 0, 'shop',  __FILE__, __LINE__);
				$groups = q("SELECT * FROM jobs_groups;", $dbweb, __LINE__, __FILE__);
				while ($group = mysqli_fetch_array($groups)) {
					echo '<option value=' . $group['id_jobgroup'] . '>' . $group['jobgroup_name'] . '</option>';
				}
				echo '
				</select>';
		echo '</td>';
	echo '</tr><tr>';
		echo '<td><b>Job-Rule</td>';
		echo '<td>
				<select name="rule" id="addJobRule" size="1">
					<option value=0>Bitte eine Job Regel auswählen</option>';
			$jobRulesResult = q("SELECT * FROM job_rules;", $dbweb, __LINE__, __FILE__);
			while ($jobRule = mysqli_fetch_array($jobRulesResult))
			{
				echo '<option value=' . $jobRule["id_jobRule"] . '>' . $jobRule["Name"] . '</option>';
			}
		echo '</select>
		</td>';
	echo '</tr><tr>';
		echo '<td><b>Job Aktiv</td>';
		echo '<td><select name="aktiv" id="addJobActive" size="1">';
			echo '<option value=1>Ja</option>';
			echo '<option value=0>Nein</option>';	
		echo '</select></td>';
	echo '</tr><tr style="display:none">';
		echo '<td><b>Letzte Jobausführung</td>';
		echo '<td>';
			echo '<input type="text" name="lastcall" id="addJobLastCall" size="40" \>';
		echo '</td>';
	echo '</tr><tr style="display:none">';
		echo '<td><b>Nächste Jobausführung</td>';
		echo '<td>';
			echo '<input type="text" name="nextcall" id="addJobNextCall" size="40" \>';
		echo '</td>';
	echo '</tr>';
	echo '</table>';
	echo '</div>';
	
	//DELETE JOB DIALOG
	echo '<div id="deleteJobDialog" style="display:none"></div>';
	
	// MESSAGE DIALOG
	echo '<div id="message_dialog" style="display: none"></div>';	

	//JOB OUTPUT
	echo '<div id="JobAusgabe" style="display:none">';
	echo '	<textarea name="ausgabe" id="JobAusgabeText" cols="60" rows="20"></textarea>';
	echo '</div>';
	
	echo '<script type="text/javascript">view(\'menu\');</script>';
	
	include("templates/".TEMPLATE_BACKEND."/footer.php");
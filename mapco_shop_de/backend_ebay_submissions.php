<?php
	include("config.php");
	include("templates/".TEMPLATE_BACKEND."/header.php");
?>
	
    <script type="text/javascript">
		function job_add_dialog()
		{
				$("#job_add_dialog").dialog
				({	buttons:
					[
						{ text: "Job starten", click: function() { job_add(); } },
						{ text: "Abbrechen", click: function() { $(this).dialog("close"); } }
					],
					closeText:"Fenster schließen",
					hide: { effect: 'drop', direction: "up" },
					modal:true,
					resizable:false,
					show: { effect: 'drop', direction: "up" },
					title:"Neuen Job anlegen",
					width:600
				});
		}
		
		function job_abort($id_job)
		{
			$.post("<?php echo PATH; ?>soa/", { API:"ebay_lms", Action:"abortJob", id_job:$id_job }, function($data)
			{
				try
				{
					$xml = $($.parseXML($data));
					$ack = $xml.find("Ack");
					if ( $ack.text()!="Success" )
					{
						show_status2($data);
						return;
					}
				}
				catch (err)
				{
					show_status2(err.message);
					return;
				}

				show_status("Job erfolgreich abgebrochen.");
				view();
			});
		}

		function delete_recurring_job($id_job, $id_account)
		{
			$.post("<?php echo PATH; ?>soa/", { API:"ebay_lms", Action:"deleteRecurringJob", id_job:$id_job, id_account:$id_account }, function($data)
			{
				try
				{
					$xml = $($.parseXML($data));
					$ack = $xml.find("Ack");
					if ( $ack.text()!="Success" )
					{
						show_status2($data);
						return;
					}
				}
				catch (err)
				{
					show_status2(err.message);
					return;
				}

				show_status("Job erfolgreich abgebrochen.");
				view();
			});
		}

		function get_recurring_jobs($id_account)
		{
			var $id_account=$("#jobs_get_id_account").val();
			wait_dialog_show();
			$.post("<?php echo PATH; ?>soa/", { API:"ebay_lms", Action:"getRecurringJobs", id_account:$id_account }, function($data)
			{
				wait_dialog_hide();
				try
				{
					$xml = $($.parseXML($data));
					$ack = $xml.find("ack");
					if ( $ack.text()!="Success" )
					{
						show_status2($data);
						return;
					}
				}
				catch (err)
				{
					show_status2(err.message);
					return;
				}

				var $html='<table>';
					$html += '<tr>';
					$html += '	<th>ID</th>';
					$html += '	<th>Typ</th>';
					$html += '	<th>Frequenz in min</th>';
					$html += '	<th>Status</th>';
					$html += '	<th>Optionen</th>';
					$html += '</tr>';
				$xml.find("recurringJobDetail").each(function()
				{
					$html += '<tr>';
					$html += '	<td>'+$(this).find("recurringJobId").text()+'</td>';
					$html += '	<td>'+$(this).find("downloadJobType").text()+'</td>';
					$html += '	<td>'+$(this).find("frequencyInMinutes").text()+'</td>';
					$html += '	<td>'+$(this).find("jobStatus").text()+'</td>';
					$html += '	<td><img src="<?php echo PATH; ?>images/icons/24x24/remove.png" title="Job abbrechen" onclick="delete_recurring_job('+$(this).find("recurringJobId").text()+', '+$id_account+');" style="cursor:pointer;" /></td>';
					$html += '</tr>';
				});
				$html += '<table>';
				show_status($html);
			});
		}

		function job_add()
		{
			var $id_account=$("#job_add_id_account").val();
			var $Action=$("#job_add_Action").val();
			var $JobType=$("#job_add_JobType").val();
			var $frequencyInMinutes=$("#job_add_frequencyInMinutes").val();
			$.post("<?php echo PATH; ?>soa/", { API:"ebay_lms", Action:$Action, JobType:$JobType, id_account:$id_account, frequencyInMinutes:$frequencyInMinutes }, function($data)
			{
				show_status2($data);
			});
		}

		function job_status($id_job)
		{
			wait_dialog_show();
			$.post("<?php echo PATH; ?>soa/", { API:"ebay_lms", Action:"getJobStatus", id_job:$id_job }, function($data)
			{
				wait_dialog_hide();
				try { $xml = $($.parseXML($data)); } catch ($err) { show_status2($err.message); return; }
				$ack = $xml.find("Ack");
				if ( $ack.text()!="Success" ) { show_status2($data); return; }
				
				show_status("Jobstatus erfolgreich abgerufen.");
				view();
			});
		}

		function jobs_get($id_account)
		{
			var $id_account=$("#jobs_get_id_account").val();
			wait_dialog_show();
			$.post("<?php echo PATH; ?>soa/", { API:"ebay_lms", Action:"getJobs", id_account:$id_account }, function($data)
			{
				wait_dialog_hide();
				try
				{
					$xml = $($.parseXML($data));
					$ack = $xml.find("Ack");
					if ( $ack.text()!="Success" )
					{
						show_status2($data);
						return;
					}
				}
				catch (err)
				{
					show_status2(err.message);
					return;
				}

				show_status("Jobs erfolgreich abgerufen.");
				view();
			});
		}


		function view()
		{
			var $id_account=$("#jobs_get_id_account").val();
			var $page=$("#jobs_get_page").val();
			var $jobType=$("#jobs_get_jobType").val();
			var $jobStatus=$("#jobs_get_jobStatus").val();
			if( typeof $id_account == "undefined" ) $id_account=0;
			if( typeof $page == "undefined" ) $page=1;
			if( typeof $jobType == "undefined" ) $jobType="Alle";
			wait_dialog_show();
			$.post("<?php echo PATH; ?>soa/", { API:"ebay_lms", Action:"JobsGet", id_account:$id_account, page:$page, jobType:$jobType, jobStatus:$jobStatus }, function($data)
			{
				wait_dialog_hide();
				try
				{
					$xml = $($.parseXML($data));
					$ack = $xml.find("Ack");
					if ( $ack.text()!="Success" )
					{
						show_status2($data);
						return;
					}
				}
				catch (err)
				{
					show_status2(err.message);
					return;
				}

				$.post("<?php echo PATH; ?>soa/", { API:"ebay", Action:"AccountsGet" }, function($data)
				{
					try
					{
						$accounts = $($.parseXML($data));
						$ack = $accounts.find("Ack");
						if ( $ack.text()!="Success" )
						{
							show_status2($data);
							return;
						}
					}
					catch (err)
					{
						show_status2(err.message);
						return;
					}
					//select account
					var $joblist='';
					$joblist += '<select id="jobs_get_id_account" onchange="view();">';
					if( $id_account==0 ) $selected=' selected="selected"'; else $selected='';
					$joblist += '<option'+$selected+' value="0">Alle Accounts</option></a>';
					$accounts.find("Account").each(function()
					{
						if( $(this).find("active").text() > 0 )
						{
							if( $id_account==$(this).find("id_account").text() ) $selected=' selected="selected"'; else $selected='';
							$joblist += '<option'+$selected+' value="'+$(this).find("id_account").text()+'">'+$(this).find("title").text()+'</option></a>';
						}
					});
					$joblist += '</select>';
					//select page
					$joblist += '<select id="jobs_get_page" onchange="view();">';
					var $maxpages=$xml.find("Pages").text();
					if( $maxpages<1 ) $maxpages=1;
					for($i=0; $i<$maxpages; $i++)
					{
						if( ($i+1)==$page ) $selected=' selected="selected"'; else $selected='';
						$joblist += '<option'+$selected+' value="'+($i+1)+'">Seite '+($i+1)+'</option></a>';
					}
					$joblist += '</select>';
					//select jobType
					$jobTypes=new Array("Alle", "AddItem", "ReviseItem", "EndItem", "ActiveInventoryReport", "SoldReport");
					$joblist += '<select id="jobs_get_jobType" onchange="view();">';
					for($i=0; $i<$jobTypes.length; $i++)
					{
						if( $jobTypes[$i]==$jobType ) $selected=' selected="selected"'; else $selected='';
						$joblist += '<option'+$selected+' value="'+$jobTypes[$i]+'">'+$jobTypes[$i]+'</option></a>';
					}
					$joblist += '</select>';
					//select jobStatus
					$jobStatuses=new Array("Alle", "Created", "Scheduled", "Completed", "Failed");
					$joblist += '<select id="jobs_get_jobStatus" onchange="view();">';
					for($i=0; $i<$jobStatuses.length; $i++)
					{
						if( $jobStatuses[$i]==$jobStatus ) $selected=' selected="selected"'; else $selected='';
						$joblist += '<option'+$selected+' value="'+$jobStatuses[$i]+'">'+$jobStatuses[$i]+'</option></a>';
					}
					$joblist += '</select>';
					//get jobs button
					$joblist += '<input type="button" value="Anzeigen" onclick="view();" />';
					$joblist += '<input type="button" value="Jobs aktualisieren" onclick="jobs_get();" />';
					//show jobs table
					$joblist += '<table class="hover">';
					$joblist += '<tr>';
					$joblist += '	<th>Nr.</th>';
					$joblist += '	<th>Account ID</th>';
					$joblist += '	<th>Evaluiert</th>';
					$joblist += '	<th>jobId</th>';
					$joblist += '	<th>jobType</th>';
					$joblist += '	<th>jobStatus</th>';
					$joblist += '	<th>creationTime</th>';
					$joblist += '	<th>completionTime</th>';
					$joblist += '	<th>errorCount</th>';
					$joblist += '	<th>percentComplete</th>';
					$joblist += '	<th>fileReferenceId</th>';
					$joblist += '	<th>inputFileReferenceId</th>';
					$joblist += '	<th>EvalCounter</th>';
					$joblist += '	<th>startTime</th>';
					$joblist += '	<th>Optionen</th>';
					$joblist += '</tr>';
					$joblist += '<tr>';
					$joblist += '	<th colspan="15">';
					$joblist += '		<img src="<?php echo PATH; ?>images/icons/24x24/info.png" title="Wiederkehrende Jobs abrufen" onclick="get_recurring_jobs();" style="cursor:pointer; float:right;" />';
					$joblist += '		<img src="<?php echo PATH; ?>images/icons/24x24/add.png" title="Wiederkehrenden Job hinzufügen" onclick="job_add_dialog();" style="cursor:pointer; float:right;" />';
					$joblist += '	</th>';
					$joblist += '</tr>';
					var $nr=0;
					$xml.find("Job").each(
						function()
						{
							var $jobType=$(this).find("jobType").text();
							var $jobStatus=$(this).find("jobStatus").text();
							$joblist += '<tr>';
							$nr++;
							$joblist += '	<td>'+$nr+'</td>';
							$joblist += '	<td>'+$(this).find("account_id").text()+'</td>';
							var $evaluated=$(this).find("evaluated").text();
							if( $evaluated==1 )
							{
								$class=' class="good"';
								$evaluated="ja";
							}
							else
							{
								$class=' class="bad"';
								$evaluated="nein";
							}
							$joblist += '	<td'+$class+'>'+$evaluated+'</td>';
							$joblist += '	<td>'+$(this).find("jobId").text()+'</td>';
							$joblist += '	<td>'+$jobType+'</td>';
							if( $jobStatus=="Completed" ) $class=' class="good"';
							else if( $jobStatus=="InProcess" ) $class=' class="neutral"';
							else $class=' class="bad"';
							$joblist += '	<td'+$class+'>'+$jobStatus+'</td>';
							$joblist += '	<td>'+$(this).find("creationTime").text()+'</td>';
							$joblist += '	<td>'+$(this).find("completionTime").text()+'</td>';
							$joblist += '	<td>'+$(this).find("errorCount").text()+'</td>';
							$joblist += '	<td>'+$(this).find("percentComplete").text()+'</td>';
							$joblist += '	<td><a href="javascript:download_file('+$(this).find("account_id").text()+', '+$(this).find("jobId").text()+', '+$(this).find("fileReferenceId").text()+');">'+$(this).find("fileReferenceId").text()+'</a></td>';
							$joblist += '	<td><a href="javascript:download_file('+$(this).find("account_id").text()+', '+$(this).find("jobId").text()+', '+$(this).find("inputFileReferenceId").text()+');">'+$(this).find("inputFileReferenceId").text()+'</a></td>';
							$joblist += '	<td>'+$(this).find("processed_lines").text()+'</td>';
							$joblist += '	<td>'+$(this).find("startTime").text()+'</td>';
							$joblist += '	<td>';
							if( $jobStatus!="Completed" && $jobStatus!="Aborted" && $jobStatus!="Failed" )
							{
								$joblist += '		<img src="<?php echo PATH; ?>images/icons/24x24/info.png" title="Jobstatus abrufen" onclick="job_status('+$(this).find("id_job").text()+');" style="cursor:pointer;" />';
							}
							if( $(this).find("evaluated").text()!=1 )
							{
								$joblist += '		<img src="<?php echo PATH; ?>images/icons/24x24/repeat.png" title="Job evaluieren" onclick="response_evaluate(\''+$(this).find("jobType").text()+'\', '+$(this).find("id_job").text()+');" style="cursor:pointer;" />';
							}
							if( $jobType=="SoldReport" || $jobType=="ActiveInventoryReport" )
							{
							}
							else
							{
								if( $jobStatus!="Completed" && $jobStatus!="Aborted" && $jobStatus!="Failed" )
								{
									$joblist += '		<img src="<?php echo PATH; ?>images/icons/24x24/remove.png" title="Job abbrechen" onclick="job_abort('+$(this).find("id_job").text()+');" style="cursor:pointer;" />';
								}
							}
							$joblist += '	</td>';
							$joblist += '</tr>';
						}
					);
					$joblist += '</table>';
					$("#view").html($joblist);
				}); //post AccountsGet
			}); //post JobsGet
		}


		function active_inventory()
		{
			wait_dialog_show();
			$.post("<?php echo PATH; ?>soa/", { API:"jobs", Action:"EbayActiveInventoryCheck", id_account:1 }, function($data)
			{
				show_status2($data);
				wait_dialog_hide();
			});
		}


		function download_file($id_account, $taskReferenceId, $fileReferenceId)
		{
			wait_dialog_show();
			$.post("<?php echo PATH; ?>soa/", { API:"ebay_lms", Action:"downloadFile", id_account:$id_account, fileReferenceId:$fileReferenceId, taskReferenceId:$taskReferenceId }, function($data)
			{
				wait_dialog_hide();
				try
				{
					$xml = $($.parseXML($data));
					$ack = $xml.find("Ack");
					if ( $ack.text()!="Success" )
					{
						show_status2($data);
						return;
					}
				}
				catch (err)
				{
					show_status2(err.message);
					return;
				}
				
				var $link=$xml.find("Path").text();
				show_status('<a href="'+$link+'" target="_blank">Download</a>');
				
			});
		}

		function response_evaluate($jobType, $id_job)
		{
			wait_dialog_show();
			$.post("<?php echo PATH; ?>soa/", { API:"ebay", Action:"ResponseEvaluate"+$jobType, id_job:$id_job }, function($data)
			{
				wait_dialog_hide();
				show_status2($data);
				view();
			});
		}


	</script>

<?php
	//PATH
	echo '<p>';
	echo '<a href="backend_index.php">Backend</a>';
	echo ' > <a href="backend_ebay_index.php">eBay</a>';
	echo ' > Übertragungen';
	echo '</p>';

	echo '<h1>Übertragungen</h1>';

	echo '<div id="view"></div>';
	echo '<script type="text/javascript"> view(); </script>';
	
	echo '<div id="job_add_dialog" style="display:none;">';
	echo '	<table>';
	echo '		<tr>';
	echo '			<td>Account</td>';
	echo '			<td>';
	echo '				<select id="job_add_id_account">';
	$results=q("SELECT * FROM ebay_accounts WHERE active>0 LIMIT 10;", $dbshop, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		echo '					<option value="'.$row["id_account"].'">'.$row["title"].'</option>';
	}
	echo '				</select>';
	echo '			</td>';
	echo '		</tr>';
	echo '		<tr>';
	echo '			<td>Aufruf</td>';
	echo '			<td>';
	echo '				<select id="job_add_Action">';
	echo '					<option value="createRecurringJob">wiederholender Job</option>';
	echo '					<option value="createUploadJob">Upload-Job</option>';
	echo '					<option value="startDownloadJob">Download-Job</option>';
	echo '				</select>';
	echo '			</td>';
	echo '		</tr>';
	echo '		<tr>';
	echo '			<td>Art</td>';
	echo '			<td>';
	echo '				<select id="job_add_JobType">';
	echo '					<option value="ActiveInventoryReport">ActiveInventoryReport</option>';
	echo '					<option value="FeeSettlementReport">FeeSettlementReport</option>';
	echo '					<option value="OrderAck">OrderAck</option>';
	echo '					<option value="SetShipmentTrackingInfo">SetShipmentTrackingInfo</option>';
	echo '					<option value="SoldReport">SoldReport</option>';
	echo '				</select>';
	echo '			</td>';
	echo '		</tr>';
	echo '		<tr>';
	echo '			<td>Frequenz (in min)</td>';
	echo '			<td>';
	echo '				<input id="job_add_frequencyInMinutes" value="" />';
	echo '			</td>';
	echo '		</tr>';
	echo '	</table>';
	echo '</div>';
	
	include("templates/".TEMPLATE_BACKEND."/footer.php");
?>
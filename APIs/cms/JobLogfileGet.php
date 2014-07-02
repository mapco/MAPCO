<?php
	//************************
	//*     SOA2-SERVICE     *
	//************************
	
	$required = array('jobID'	=> 'numericNN');
	check_man_params($required);

	//include cms core functions
	include("../functions/cms_core.php");
	
	$jobID = $_POST["jobID"];
	
	$xml = '';
	if (!isset($_POST["peek"])) {
		if (isset($_POST["mintime"]) && $_POST["mintime"] != null) {
			$minTime = $_POST["mintime"];
		} else {
			$minTime = getTime() - 86400;	
		}
		if (isset($_POST["mintime"]) && $_POST["mintime"] != null) {
			$maxTime = $_POST["maxtime"];
		} else {
			$maxTime = getTime();	
		}	
		$resultsById = q("SELECT * FROM jobs_logfile WHERE job_id = " . $jobID . " AND StartTime >= " . $minTime . " AND EndTime <= " . $maxTime . " ORDER BY id_logfile DESC;", $dbweb, __FILE__, __LINE__);#
		if( mysqli_num_rows($resultsById) > 0 )
		{
			$i = 0;
			$peek = 0;
			while($jobsLogfile = mysqli_fetch_array($resultsById))
			{
				$xml.= xmlOutput($jobsLogfile);
				$durationTime = getDuration($jobsLogfile);
				if ($durationTime >= '120') {
					$peek++;
				}
				$var += $durationTime;
				$i++;
			}
			($i != 0) ? $averageDuration = covertSecondTo(($var/$i)) : $averageDuration = 'keinen';
			$xml.= '<jobfileTotalDuration><![CDATA[' . covertSecondTo($var) . ']]></jobfileTotalDuration>' . "\n";
			$xml.= '<jobfileAverageDuration><![CDATA[' . $averageDuration . ']]></jobfileAverageDuration>' . "\n";
			$xml.= '<jobfilePeekDuration><![CDATA[' . $peek . ']]></jobfilePeekDuration>' . "\n";
		}
	}
	
	if (isset($_POST["peek"]) && $_POST["peek"] == true) {
		$resultsById = q("SELECT * FROM jobs_logfile WHERE job_id = " . $jobID, $dbweb, __FILE__, __LINE__);
		$peek = 0;
		if( mysqli_num_rows($resultsById) > 0 )
		{
			$i = 0;
			while($peek = mysqli_fetch_array($resultsById))
			{
				$durationTime = getDuration($peek);
				if ($durationTime >= '120') {
					$xml.= xmlOutput($peek);
					$var += $durationTime;
					$i++;	
				}
			}
			($i != 0) ? $averageDuration = covertSecondTo(($var/$i)) : $averageDuration = 'keinen';
			$xml.= '<jobfileTotalDuration><![CDATA[' . covertSecondTo($var) . ']]></jobfileTotalDuration>' . "\n";
			$xml.= '<jobfileAverageDuration><![CDATA[' . $averageDuration . ']]></jobfileAverageDuration>' . "\n";
		}
	}
	
	echo $xml;
	
	function xmlOutput($data)
	{
		$xml = '<log>';
			$xml.= '<id>' . $data['id_logfile'] . '</id>' . "\n";
			$xml.= '<jobId>' . $data['job_id'] . '</jobId>' . "\n";
			$xml.= '<startTime><![CDATA[' . getDateTime($data['StartTime']) . ']]></startTime>' . "\n";
			$xml.= '<endTime><![CDATA[' . getWorktimeResult($data) . ']]></endTime>' . "\n";
			$xml.= '<manual><![CDATA[' . getManuelStatus($data['manual']) . ']]></manual>' . "\n";
			$xml.= '<response><![CDATA[' . getStrIreplace($data['Response']) . ']]></response>' . "\n";
		$xml.= '</log>' . "\n";
		return $xml;
	}
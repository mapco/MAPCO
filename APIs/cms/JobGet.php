<?php
	//************************
	//*     SOA2-SERVICE     *
	//************************
	
	$required = array('jobID'	=> 'numericNN');
	check_man_params($required);
	
	//include cms core functions
	include("../functions/cms_core.php");
	
	$jobID = $_POST["jobID"];
	$results = q("SELECT * FROM jobs WHERE id_job = " . $jobID, $dbweb, __FILE__, __LINE__);
	
	if( mysqli_num_rows($results) > 0 )
	{
		$job = mysqli_fetch_array($results);
		$userFirst = q("SELECT * FROM cms_users WHERE id_user = " . $job['firstmod_user'], $dbweb, __FILE__, __LINE__);
		$firstmodUser = mysqli_fetch_array($userFirst);
		$userLast = q("SELECT * FROM cms_users WHERE id_user = " . $job['lastmod_user'], $dbweb, __FILE__, __LINE__);
		$lastmodUser = mysqli_fetch_array($userLast);
		$jobRule = q("SELECT * FROM job_rules WHERE id_jobRule = " . $job['JobRule'], $dbweb, __FILE__, __LINE__);
		$rule = mysqli_fetch_array($jobRule);			
		
		$xml = '';
		$xml.= '<job>' . $job['id_job'] . '</job>' . "\n";
		$xml.= '<name>' . $job['Name'] . '</name>' . "\n";
		$xml.= '<description><![CDATA[' . $job['Description'] . ']]></description>' . "\n";
		$xml.= '<api>' . $job['API'] . '</api>' . "\n";
		$xml.= '<service>' . $job['Service'] . '</service>' . "\n";
		$xml.= '<postVar>' . $job['Post_Vars'] . '</postVar>' . "\n";
		$xml.= '<lastCall><![CDATA[' . getDateToday($job['LastCall']) . ']]></lastCall>' . "\n";
		$xml.= '<nextCall><![CDATA[' . getDateToday($job['NextCall']) . ']]></nextCall>' . "\n";
		$xml.= '<active><![CDATA[' . getActiveStatus($job['Active']) . ']]></active>' . "\n";
		$xml.= '<jobRule><![CDATA[' . $rule['Name'] . ']]></jobRule>' . "\n";
		$xml.= '<firstmod><![CDATA[' . getDateTime($job['firstmod']) . ']]></firstmod>' . "\n";
		$xml.= '<firstmodUser>' . getUserName($firstmodUser) . '</firstmodUser>' . "\n";
		$xml.= '<lastmod><![CDATA[' . getDateTime($job['lastmod']) . ']]></lastmod>' . "\n";
		$xml.= '<lastmodUser><![CDATA[' . getUserName($lastmodUser) . ']]></lastmodUser>' . "\n";
		
		// finds all jobs logfiles by job id
		$countAllById = q("SELECT COUNT(*) FROM jobs_logfile WHERE job_id = " . $jobID . "", $dbweb, __FILE__, __LINE__);
		$countResult = mysqli_fetch_array($countAllById);
		$xml.= '<totalCalls><![CDATA[' . $countResult[0] . ']]></totalCalls>' . "\n";
		
		
		$firstCall = q("SELECT * FROM jobs_logfile WHERE job_id = " . $jobID . " ORDER BY StartTime ASC", $dbweb, __FILE__, __LINE__);
		$firstCallResult = mysqli_fetch_array($firstCall);
		$xml.= '<firstCall><![CDATA[' . getDateTime($firstCallResult['StartTime']) . ']]></firstCall>' . "\n";
		
		//stats
		$calls = q("SELECT * FROM jobs_logfile WHERE job_id = " . $jobID, $dbweb, __FILE__, __LINE__);
		$i = 0;
		$peek = 0;
		while($totalDuration = mysqli_fetch_array($calls))
		{
			$durationTime = getDuration($totalDuration);
			if ($durationTime >= '200') {
				$peek++;	
			}
			$var += $durationTime;
			$i++;
		}
		($i != 0) ? $averageDuration = covertSecondTo(($var/$i)) : $averageDuration = 'keinen';		
		
		$xml.= '<totalDuration><![CDATA[' . covertSecondTo($var) . ']]></totalDuration>' . "\n";
		$xml.= '<averageDuration><![CDATA[' . $averageDuration . ']]></averageDuration>' . "\n";
		$xml.= '<peekDuration><![CDATA[' . $peek . ']]></peekDuration>' . "\n";
		
		echo $xml;		
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
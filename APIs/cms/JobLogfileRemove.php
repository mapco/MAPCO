<?php
    //************************
	//*     SOA2-SERVICE     *
	//************************
	
	$required = array('jobID'	=> 'numericNN');
	check_man_params($required);

	//include cms core functions
	include("../functions/cms_core.php");
	
	$jobID = $_POST["jobID"];
	$minTime = $_POST["mintime"];
	$maxTime = $_POST["maxtime"];	
	
	if (isset($_POST["mintime"]) AND $_POST["mintime"] != null AND isset($_POST["maxtime"]) && $_POST["maxtime"] != null) {
	$resultsById = q("SELECT * FROM jobs_logfile WHERE job_id = " . $jobID . " AND StartTime >= " . $minTime . " AND EndTime <= " . $maxTime . " ORDER BY id_logfile DESC;", $dbweb, __FILE__, __LINE__);
		if( mysqli_num_rows($resultsById) > 0 )
		{
			while($removeLogfile = mysqli_fetch_array($resultsById))
			{
				q("DELETE FROM jobs_logfile WHERE id_logfile = " . $removeLogfile['id_logfile'], $dbweb, __FILE__, __LINE__);
			}
		}
		$xml.= '<job>' . $jobID . '</job>' . "\n";
		echo $xml;
	}
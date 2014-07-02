<?php
	include("../../mapco_shop_de/config.php");
	
	echo '<JOBHANDLERResponse>'."\n";

	$starttime=time()+microtime();
//	set_time_limit(1790);
//	$endtime=time()+1790;
//	$endtime=time()+60;
//	$starttime=time();

	//session required
	if( !isset($_SESSION["id_user"]) )
	{
		session_start();
		$_SESSION["id_user"]=1;
		$_SESSION["userrole_id"]=1;
	}

	//JOBS AUSFÜHREEN
	$jobOK=true;
	if ($jobOK)
	{
		$jobsstart=time();

		$jobs = array();
		$i=0;

	//get jobs
	if( isset($_POST["id_job"]) )
	{
		$res=q("SELECT * FROM jobs where id_job=".$_POST["id_job"].";", $dbweb, __FILE__, __LINE__);
	}
	else
	{
		echo "SELECT * FROM jobs where Active = 1 AND NextCall<= ".time()." order by NextCall;";
		$res=q("SELECT * FROM jobs where Active = 1 AND NextCall<= ".time()." order by NextCall;", $dbweb, __FILE__, __LINE__);
	}
	//stop if nothing to do
	if (mysqli_num_rows($res) == 0)
		exit;
	}
	while ($row=mysqli_fetch_array($res)) 
	{
		$jobs[$i]["id_job"]=$row["id_job"];
		$jobs[$i]["name"]=$row["Name"];
		$jobs[$i]["API"]=$row["API"];
		$jobs[$i]["Action"]=$row["Service"];
		$jobs[$i]["Post_Vars"]=$row["Post_Vars"];
		$jobs[$i]["JobRule"]=$row["JobRule"];
		echo '<JobsToExecute>'.$jobs[$i]["name"].'</JobsToExecute>'."\n";
		$i++;
	}
	
	$executedJobs = array();
	$response='';
	for ($i=0; $i<sizeof($jobs); $i++)
	{
		//GET RULE
		$res_rule=q("SELECT * FROM job_rules where id_jobRule = ".$jobs[$i]["JobRule"].";", $dbweb, __FILE__, __LINE__	);
		if( mysqli_num_rows($res_rule)==0 )
		{
			show_error(9893, 6, __FILE__, __LINE__);
			exit;
		}
	
		//SETZEN DES NÄCHSTEN AUSFÜHRUNGSZEITPUNKTES
		$row_rule=mysqli_fetch_array($res_rule);
		$rule = new SimpleXMLElement($row_rule["Rules"]);
	
		//AUSLESEN DER WOCHNETAGE DES JOBS
		$week = array();
		if (isset($rule->week[0])) 
		{
			$week=$rule->week[0];
			$week=explode(',',$week);
		}
		
		//AUSLESEN DER START&ENDZEIT DES JOBS UND ABSTAND DER WIEDERHOLUNG
		$day_period = array();
		if (isset($rule->dayperiod[0]))
		{
			$day_period=$rule->dayperiod[0];
			$day_period_from_hour=substr($day_period,0, 2);
			$day_period_from_mins=substr($day_period,2, 2);
			$day_period_to_hour=substr($day_period,5,2);
			$day_period_to_mins=substr($day_period,7,2);
			$day_period_repeat=substr($day_period,10);
	
			$last_repeat=mktime($day_period_to_hour,$day_period_to_mins,0, date("n"), date("j"), date("Y"));
			$next_repeat=$starttime+($day_period_repeat-2)*60;
			if ($next_repeat>$last_repeat)
			{
				$k=1;
				$wochentag=date("N");
				while (!in_array( date("N", time()+86400*$k), $week )) $k++;
				$next_repeat=mktime($day_period_from_hour,$day_period_from_mins,0, date("n"), date("j")+$k, date("Y"));
			}
		}
	
		//AUSLESEN DER TAGESZEITEN EINES JOBS
		if (isset($rule->daytime[0]))
		{
			$day_time=$rule->daytime[0];
			$day_time_hour=substr($day_time,0,2);
			$day_time_mins=substr($day_time,2,2);
			
			$k=1;
			$wochentag=date("N");
			while (!in_array( date("N", time()+86400*$k), $week )) $k++;
			$next_repeat=mktime($day_time_hour,$day_time_mins,0, date("n"), date("j")+$k, date("Y"));
		}
	
		//jobrule 3min fix
		if ($jobs[$i]["JobRule"]==27) $next_repeat=time()+180;
	
		//update job next call
		$data=array();
		$data["NextCall"]=$next_repeat;
		$data["LastCall"]=time();
		q_update("jobs", $data, "WHERE id_job=".$jobs[$i]["id_job"].";", $dbweb, __FILE__, __LINE__);
	
		//what is this???
		$vars=explode(';', trim($jobs[$i]["Post_Vars"]));
		$varField = array();
		for ($j=0; $j<sizeof($vars); $j++) {
			$tmp=explode('=',trim($vars[$j]));
			//if (isset($tmp[0]) && isset($tmp[1])) $_POST[$tmp[0]]=$tmp[1];
			if (isset($tmp[0]) && isset($tmp[1])) $varField[$tmp[0]]=$tmp[1];
		}
		$varField["API"]=$jobs[$i]["API"];
		$varField["Action"]=$jobs[$i]["Action"];
		if( !isset($_SESSION["id_user"]) ) $varField["usertoken"]="YoeBdHw035a9Ai0KkKDtHBaPorF5rGTquEGXPZ4GU7gqAaCcsM";
		$varField["JobEndTime"]=$endtime;
			
		//start a logfile entry
		$data=array();
		$data["job_id"]=$jobs[$i]["id_job"];
		$data["StartTime"]=time();
		$data["EndTime"]=0;
		$data["NextCall"]=0;
		if( isset($_POST["id_job"]) ) $data["manual"]=1;
		else $data["manual"]=0;
		$data["Response"]='';
		q_insert("jobs_logfile", $data, $dbweb, __FILE__, __LINE__);
		$id_logfile=mysqli_insert_id($dbweb);
	
		//JOB AUSFÜHREN
		$executedJobs[$i]=$jobs[$i]["name"];
		try
		{
			$responseXml =  post(PATH."soa/", $varField)."\n";
		}
		catch (Exception $e)
		{
			$response.= $e->getMessage()."==>>";
			$response.= 'in '.$e->getFile().', line: '.$e->getLine().'.';
		}
		echo '<Response><![CDATA['.$responseXml.']]></Response>'."\n";
	
		//try XML validation
		$use_errors = libxml_use_internal_errors(true);
		try
		{
			$response = new SimpleXMLElement($responseXml);
		}
		catch(Exception $e)
		{
			//save endtime and response to logfile
			$data=array();
			$data["EndTime"]=time();
			$data["NextCall"]=$next_repeat;
			q_update("jobs_logfile", $data, "WHERE id_logfile=".$id_logfile.";", $dbweb, __FILE__, __LINE__);
			$data=array();
			$data["Response"]=$responseXml;
			q_update("jobs_logfile", $data, "WHERE id_logfile=".$id_logfile.";", $dbweb, __FILE__, __LINE__);
			$endtime=time()+microtime();
			if( ($endtime-$starttime)>60 ) break;
		}
		libxml_clear_errors();
		libxml_use_internal_errors($use_errors);
	
		//get next call from job if returned
		if( isset($response->NextCall[0]) )	$NextCall=(integer)$response->NextCall[0];
		else $NextCall=$next_repeat;
			
		//save endtime, nextcall and response to logfile
		q("	UPDATE jobs_logfile
			SET EndTime=".time().",
				NextCall=".$NextCall.",						
				Response='".mysqli_real_escape_string($dbweb, $responseXml)."'
			WHERE id_logfile=".$id_logfile.";", $dbweb, __FILE__, __LINE__);
		
		q("	UPDATE jobs
			SET NextCall = '".$NextCall."'
			WHERE id_job = '".$jobs[$i]["id_job"]."';", $dbweb, __FILE__, __LINE__);
	
		$endtime=time()+microtime();
		if( ($endtime-$starttime)>60 ) break;
	}
	
	if (sizeof($executedJobs)>0) 
	{ 
		for ($j=0; $j<sizeof($executedJobs); $j++) echo '<JobsExecuted>'.$executedJobs[$j].'</JobsExecuted>'."\n";
		echo '<Runtime>'.(time()-$jobsstart).'</Runtime>'."\n";
	}
	echo '</JOBHANDLERResponse>'."\n";

?>
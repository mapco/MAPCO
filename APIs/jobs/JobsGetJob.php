<?php

//	keep post submit
$post = $_POST;

	if ( !isset($post["id_job"]) || $post["id_job"] == "" ) {
		echo '<JobsGetJobResponse>' . "\n";
		echo '	<Ack>Failure</Ack>' . "\n";
		echo '	<Error>' . "\n";
		echo '		<Code>' . __LINE__ . '</Code>' . "\n";
		echo '		<shortMsg>ID für Job nicht gefunden</shortMsg>' . "\n";
		echo '		<longMsg>Es muss eine ID für den zu bearbeitenden Job übermittelt werden.</longMsg>' . "\n";
		echo '	</Error>' . "\n";
		echo '</JobsGetJobResponse>' . "\n";
		exit;
	}

	$jobsQuery = "
		SELECT * 
		FROM jobs 
		WHERE id_job = '" . $post["id_job"] . "'";
	$jobsResult = q($jobsQuery, $dbweb, __LINE__, __FILE__);
	if (mysqli_num_rows($jobsResult) == 0) {
		echo '<JobsGetJobResponse>' . "\n";
		echo '	<Ack>Failure</Ack>' . "\n";
		echo '	<Error>' . "\n";
		echo '		<Code>'.__LINE__.'</Code>' . "\n";
		echo '		<shortMsg>Job nicht gefunden</shortMsg>' . "\n";
		echo '		<longMsg>Zur ID konnte kein Job gefunden werden.</longMsg>' . "\n";
		echo '	</Error>' . "\n";
		echo '</JobsGetJobResponse>' . "\n";
		exit;
	}

	$job = mysqli_fetch_array($jobsResult);
	
	echo '<JobsGetJobResponse>' . "\n";
	echo '	<Ack>Success</Ack>' . "\n";
	echo '	<JobName><![CDATA[' . $job["Name"].']]></JobName>'."\n";
	echo '	<JobDescription><![CDATA[' . $job["Description"] . ']]></JobDescription>' . "\n";
	echo '	<JobAPI><![CDATA[' . $job["API"].']]></JobAPI>' . "\n";
	echo '	<JobService><![CDATA[' . $job["Service"].']]></JobService>' . "\n";
	echo '	<JobPost_Vars><![CDATA[' . $job["Post_Vars"].']]></JobPost_Vars>' . "\n";
	echo '	<JobPost_VarsList><![CDATA[' . $job["PostVarsList"].']]></JobPost_VarsList>' . "\n";
	echo '	<JobLastCall><![CDATA[' . $job["LastCall"].']]></JobLastCall>' . "\n";
	echo '	<JobNextCall><![CDATA[' . $job["NextCall"].']]></JobNextCall>' . "\n";
	echo '	<JobActive><![CDATA[' . $job["Active"].']]></JobActive>' . "\n";
	echo '	<JobGroups><![CDATA[' . $job["jobsGroupID"].']]></JobGroups>' . "\n";
	echo '	<JobRule><![CDATA[' . $job["JobRule"].']]></JobRule>' . "\n";
	echo '</JobsGetJobResponse>' . "\n";
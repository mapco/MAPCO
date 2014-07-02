<?php

	if ( !isset($_POST["id_job"]) || $_POST["id_job"]=="" )
	{
		echo '<JobsManualExecuteResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>ID für Job nicht gefunden</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine ID für den zu bearbeitenden Job übermittelt werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</JobsManualExecuteResponse>'."\n";
		exit;
	}

	$res=q("SELECT * FROM jobs WHERE id_job = '".$_POST["id_job"]."';", $dbweb, __LINE__, __FILE__);
	if (mysqli_num_rows($res)==0)
	{
		echo '<JobsManualExecuteResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Job nicht gefunden</shortMsg>'."\n";
		echo '		<longMsg>Zur ID konnte kein Job gefunden werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</JobsManualExecuteResponse>'."\n";
		exit;
	}
	//session required	
	session_start();

	$row=mysqli_fetch_array($res);
	$vars=explode(';', trim($row["Post_Vars"]));
	$varField = array();
	for ($j=0; $j<sizeof($vars); $j++) {
		$tmp=explode('=',trim($vars[$j]));
		//if (isset($tmp[0]) && isset($tmp[1])) $_POST[$tmp[0]]=$tmp[1];
		if (isset($tmp[0]) && isset($tmp[1])) $varField[$tmp[0]]=$tmp[1];
	}
	
	$varField["API"]=$row["API"];
	$varField["Action"]=$row["Service"];
	//$varField["usertoken"]="merci2664";

	$response= post(PATH."soa/index.php", $varField)."\n";
	if( $response=="" ) echo 'response empty'; else echo $response;
	exit;
	
	echo '<JobsManualExecuteResponse>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	echo '	<Response><![CDATA['.$response.']]></Response>'."\n";
	echo '	<JobName><![CDATA['.$row["Name"].']]></JobName>'."\n";
	echo '</JobsManualExecuteResponse>'."\n";

?>
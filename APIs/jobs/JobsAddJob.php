<?php

	if (!isset($_POST["JobName"]) || $_POST["JobName"] == "" ) {
		echo '<JobsAddJobResponse>' . "\n";
		echo '	<Ack>Failure</Ack>' . "\n";
		echo '	<Error>' . "\n";
		echo '		<Code>' . __LINE__ . '</Code>' . "\n";
		echo '		<shortMsg>Name für Job nicht gefunden</shortMsg>' . "\n";
		echo '		<longMsg>Es muss ein Name für den zu speichernden Job übermittelt werden.</longMsg>' . "\n";
		echo '	</Error>' . "\n";
		echo '</JobsAddJobResponse>' . "\n";
		exit;
	}

	if (!isset($_POST["JobAPI"]) || $_POST["JobAPI"] == "") {
		echo '<JobsAddJobResponse>' . "\n";
		echo '	<Ack>Failure</Ack>' . "\n";
		echo '	<Error>' . "\n";
		echo '		<Code>' . __LINE__ . '</Code>' . "\n";
		echo '		<shortMsg>API für Job nicht gefunden</shortMsg>' . "\n";
		echo '		<longMsg>Es muss eine API für den zu speichernden Job übermittelt werden.</longMsg>' . "\n";
		echo '	</Error>' . "\n";
		echo '</JobsAddJobResponse>' . "\n";
		exit;
	}
	
	if (!isset($_POST["JobService"]) || $_POST["JobService"] == "" ) {
		echo '<JobsAddJobResponse>' . "\n";
		echo '	<Ack>Failure</Ack>' . "\n";
		echo '	<Error>' . "\n";
		echo '		<Code>' . __LINE__ . '</Code>' . "\n";
		echo '		<shortMsg>Service für Job nicht gefunden</shortMsg>' . "\n";
		echo '		<longMsg>Es muss ein Service für den zu speichernden Job übermittelt werden.</longMsg>' . "\n";
		echo '	</Error>' . "\n";
		echo '</JobsAddJobResponse>' . "\n";
		exit;
	}

	if (!isset($_POST["JobRule"]) || $_POST["JobRule"] == 0) {
		echo '<JobsAddJobResponse>' . "\n";
		echo '	<Ack>Failure</Ack>' . "\n";
		echo '	<Error>' . "\n";
		echo '		<Code>'.__LINE__.'</Code>' . "\n";
		echo '		<shortMsg>JobRule für Job nicht gefunden</shortMsg>' . "\n";
		echo '		<longMsg>Es muss eine Ausführungsregel für den zu speichernden Job übermittelt werden.</longMsg>' . "\n";
		echo '	</Error>' . "\n";
		echo '</JobsAddJobResponse>' . "\n";
		exit;
	}
	
	if (!isset($_POST["JobDesc"]) || $_POST["JobDesc"] == "" ) {
		$_POST["JobDesc"] = "";
	}
	if (!isset($_POST["JobPostVars"]) || $_POST["JobPostVars"] == "") {
		$_POST["JobPostVars"] = "";
	}
	
	if (!isset($_POST["JobActive"])) {
		echo '<JobsAddJobResponse>' . "\n";
		echo '	<Ack>Failure</Ack>' . "\n";
		echo '	<Error>' . "\n";
		echo '		<Code>' . __LINE__.'</Code>' . "\n";
		echo '		<shortMsg>Status für Job nicht gefunden</shortMsg>' . "\n";
		echo '		<longMsg>Es muss angegeben werden, ob der Job als aktiviert oder deaktiviert gekennzeichnet ist.</longMsg>' . "\n";
		echo '	</Error>' . "\n";
		echo '</JobsAddJobResponse>' . "\n";
		exit;
	}

	$data=array();
	$data["Name"]=$_POST["JobName"];
	$data["Description"]=$_POST["JobDesc"];
	$data["API"]=$_POST["JobAPI"];
	$data["Service"]=$_POST["JobService"];
	$data["Post_Vars"]=$_POST["JobPostVars"];
	$data["LastCall"]=0;
	$data["NextCall"]=0;
	$data["Active"]=$_POST["JobActive"];
	$data["jobsGroupID"]=$_POST["JobGroups"];
	$data["JobRule"]=$_POST["JobRule"];
	$data["firstmod"]=time();
	$data["firstmod_user"]=$_SESSION["id_user"];
	$data["lastmod"]=time();
	$data["lastmod_user"]=$_SESSION["id_user"];
	q_insert("jobs", $data, $dbweb, __FILE__, __LINE__);
	$id_Job = mysqli_insert_id($dbweb);
	
	echo '<JobsAddJobResponse>' . "\n";
	echo '	<Ack>Success</Ack>' . "\n";
	echo '	<id_job><![CDATA[' . $id_Job . ']]></id_job>' . "\n";
	echo '</JobsAddJobResponse>' . "\n";
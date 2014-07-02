<?php

	if (!isset($_POST["id_job"]) || $_POST["id_job"] == "" ) {
		echo '<JobsUpdateJobResponse>' . "\n";
		echo '	<Ack>Failure</Ack>' . "\n";
		echo '	<Error>' . "\n";
		echo '		<Code>' . __LINE__ . '</Code>' . "\n";
		echo '		<shortMsg>ID für Job nicht gefunden</shortMsg>' . "\n";
		echo '		<longMsg>Es muss eine ID für den zu bearbeitenden Job übermittelt werden.</longMsg>' . "\n";
		echo '	</Error>' . "\n";
		echo '</JobsUpdateJobResponse>' . "\n";
		exit;
	}
	
	$res = q("SELECT * FROM jobs WHERE id_job = '" . $_POST["id_job"] . "';", $dbweb, __LINE__, __FILE__);
	if (mysqli_num_rows($res) == 0) {
		echo '<JobsUpdateJobResponse>' . "\n";
		echo '	<Ack>Failure</Ack>' . "\n";
		echo '	<Error>' . "\n";
		echo '		<Code>' . __LINE__ . '</Code>' . "\n";
		echo '		<shortMsg>Job nicht gefunden</shortMsg>' . "\n";
		echo '		<longMsg>Zur ID konnte kein Job gefunden werden.</longMsg>' . "\n";
		echo '	</Error>' . "\n";
		echo '</JobsUpdateJobResponse>' . "\n";
		exit;
	}
	
	if (!isset($_POST["JobName"]) || $_POST["JobName"] == "" ) {
		echo '<JobsUpdateJobResponse>' . "\n";
		echo '	<Ack>Failure</Ack>' . "\n";
		echo '	<Error>' . "\n";
		echo '		<Code>' . __LINE__ . '</Code>' . "\n";
		echo '		<shortMsg>Name für Job nicht gefunden</shortMsg>' . "\n";
		echo '		<longMsg>Es muss ein Name für den zu speichernden Job übermittelt werden.</longMsg>' . "\n";
		echo '	</Error>' . "\n";
		echo '</JobsUpdateJobResponse>' . "\n";
		exit;
	}

	if (!isset($_POST["JobAPI"]) || $_POST["JobAPI"] == "") {
		echo '<JobsUpdateJobResponse>' . "\n";
		echo '	<Ack>Failure</Ack>' . "\n";
		echo '	<Error>' . "\n";
		echo '		<Code>' . __LINE__ . '</Code>' . "\n";
		echo '		<shortMsg>API für Job nicht gefunden</shortMsg>' . "\n";
		echo '		<longMsg>Es muss eine API für den zu speichernden Job übermittelt werden.</longMsg>' . "\n";
		echo '	</Error>' . "\n";
		echo '</JobsUpdateJobResponse>' . "\n";
		exit;
	}
	
	if (!isset($_POST["JobService"]) || $_POST["JobService"] == "") {
		echo '<JobsUpdateJobResponse>' . "\n";
		echo '	<Ack>Failure</Ack>' . "\n";
		echo '	<Error>' . "\n";
		echo '		<Code>' . __LINE__ . '</Code>' . "\n";
		echo '		<shortMsg>Service für Job nicht gefunden</shortMsg>' . "\n";
		echo '		<longMsg>Es muss ein Service für den zu speichernden Job übermittelt werden.</longMsg>' . "\n";
		echo '	</Error>' . "\n";
		echo '</JobsUpdateJobResponse>' . "\n";
		exit;
	}

	if (!isset($_POST["JobRule"]) || $_POST["JobRule"] == 0) {
		echo '<JobsUpdateJobResponse>' . "\n";
		echo '	<Ack>Failure</Ack>' . "\n";
		echo '	<Error>' . "\n";
		echo '		<Code>' . __LINE__ . '</Code>' . "\n";
		echo '		<shortMsg>JobRule für Job nicht gefunden</shortMsg>' . "\n";
		echo '		<longMsg>Es muss eine Ausführungsregel für den zu speichernden Job übermittelt werden.</longMsg>' . "\n";
		echo '	</Error>' . "\n";
		echo '</JobsUpdateJobResponse>' . "\n";
		exit;
	}
	
	if (!isset($_POST["JobActive"])) {
		echo '<JobsUpdateJobResponse>' . "\n";
		echo '	<Ack>Failure</Ack>' . "\n";
		echo '	<Error>' . "\n";
		echo '		<Code>'.__LINE__.'</Code>' . "\n";
		echo '		<shortMsg>Status für Job nicht gefunden</shortMsg>' . "\n";
		echo '		<longMsg>Es muss angegeben werden, ob der Job als aktiviert oder deaktiviert gekennzeichnet ist.</longMsg>' . "\n";
		echo '	</Error>' . "\n";
		echo '</JobsUpdateJobResponse>' . "\n";
		exit;
	}
	
	if (!isset($_POST["JobDesc"]) || $_POST["JobDesc"] == "") {
		$_POST["JobDesc"] = "";
	}
	if (!isset($_POST["JobPostVars"]) || $_POST["JobPostVars"] == "") {
		$_POST["JobPostVars"] = "";
	}
	if (!isset($_POST["JobPostVarsList"]) || $_POST["JobPostVarsList"] == "") {
		$_POST["JobPostVarsList"] = "";
	}	
	if (!isset($_POST["JobLastCall"]) || $_POST["JobLastCall"] == "") {
		$_POST["JobLastCall"] = 0;
	}
	if (!isset($_POST["JobNextCall"]) || $_POST["JobNextCall"] == "") {
		$_POST["JobNextCall"] = 0;
	}

	$res = q("UPDATE jobs 
		SET Name = '" . mysqli_real_escape_string($dbweb, $_POST["JobName"]) . "', 
		Description = '" . mysqli_real_escape_string($dbweb, $_POST["JobDesc"]) . "', 
		API = '" . mysqli_real_escape_string($dbweb, $_POST["JobAPI"]) . "', 
		Service = '" . mysqli_real_escape_string($dbweb, $_POST["JobService"]) . "', 
		Post_Vars = '" . mysqli_real_escape_string($dbweb, $_POST["JobPostVars"]) . "',
		PostVarsList = '" . mysqli_real_escape_string($dbweb, $_POST["JobPostVarsList"]) . "', 
		LastCall = " . $_POST["JobLastCall"] . ", 
		NextCall = " . $_POST["JobNextCall"] . ", 
		Active = " . $_POST["JobActive"] . ",
		jobsGroupID = " . $_POST["JobGroups"] . ", 
		JobRule = " . $_POST["JobRule"] . ", 
		lastmod = " . time() . ", 
		lastmod_user = " . $_SESSION["id_user"] . " WHERE id_job = " . $_POST["id_job"] . ";", $dbweb, __FILE__, __LINE__);

	echo '<JobsUpdateJobResponse>' . "\n";
	echo '	<Ack>Success</Ack>' . "\n";
	echo '	<Response><![CDATA[' . $_POST["id_job"] . ']]></Response>' . "\n";
	echo '</JobsUpdateJobResponse>' . "\n";
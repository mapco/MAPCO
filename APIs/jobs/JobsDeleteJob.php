<?php

	if ( !isset($_POST["id_job"]) || $_POST["id_job"]=="" )
	{
		echo '<JobsDeleteJobResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>ID für Job nicht gefunden</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine ID für den zu löschenden Job übermittelt werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</JobsDeleteJobResponse>'."\n";
		exit;
	}

	$res=q("SELECT * FROM jobs WHERE id_job = '".$_POST["id_job"]."';", $dbweb, __LINE__, __FILE__);
	if (mysqli_num_rows($res)==0)
	{
		echo '<JobsDeleteJobResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Job nicht gefunden</shortMsg>'."\n";
		echo '		<longMsg>Zur ID konnte kein Job gefunden werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</JobsDeleteJobResponse>'."\n";
		exit;
	}

	$res=q("DELETE FROM jobs WHERE id_job = '".$_POST["id_job"]."';", $dbweb, __LINE__, __FILE__);
	
	echo '<JobsDeleteJobResponse>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	echo '	<id_job><![CDATA['.$_POST["id_job"].']]></id_job>'."\n";
	echo '</JobsDeleteJobResponse>'."\n";

?>
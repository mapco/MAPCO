<?php

	if ( !isset($_POST["ruleName"]) || $_POST["ruleName"]=="" )
	{
		echo '<JobsUpdateRuleResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Name für JobRule nicht gefunden</shortMsg>'."\n";
		echo '		<longMsg>Es muss ein Name für die zu speichernde JobRule übermittelt werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</JobsUpdateRuleResponse>'."\n";
		exit;
	}

	if ( !isset($_POST["rule"]) || $_POST["rule"]=="")
	{
		echo '<JobsUpdateRuleResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Zeitangeben/Ausführungsregeln für JobRule nicht gefunden</shortMsg>'."\n";
		echo '		<longMsg>Es müssen Zeitangeben/Ausführungsregeln für die zu speichernde JobRule übermittelt werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</JobsUpdateRuleResponse>'."\n";
		exit;
	}

	if ( !isset($_POST["id_JobRule"]) || $_POST["id_JobRule"]=="" )
	{
		echo '<JobsUpdateRuleResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>ID für JobRule nicht gefunden</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine ID für die zu bearbeitende JobRule übermittelt werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</JobsUpdateRuleResponse>'."\n";
		exit;
	}

	
	$res=q("SELECT * FROM job_rules WHERE id_JobRule = '".$_POST["id_JobRule"]."';", $dbweb, __LINE__, __FILE__);
	if (mysqli_num_rows($res)==0)
	{
		echo '<JobsUpdateRuleResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>JobRule nicht gefunden</shortMsg>'."\n";
		echo '		<longMsg>Zur ID konnte keine JobRule gefunden werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</JobsUpdateRuleResponse>'."\n";
		exit;
	}

	$res=q("UPDATE job_rules SET Name = '".mysqli_real_escape_string($dbweb, $_POST["ruleName"])."', Rules = '".mysqli_real_escape_string($dbweb, $_POST["rule"])."', lastmod = ".time()." , lastmod_user = ".$_SESSION["id_user"]." WHERE id_jobRule= ".$_POST["id_JobRule"].";", $dbweb, __FILE__, __LINE__);

	echo '<JobsUpdateRuleResponse>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	echo '	<Response><![CDATA['.$_POST["id_JobRule"].']]></Response>'."\n";
	echo '</JobsUpdateRuleResponse>'."\n";

?>
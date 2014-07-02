<?php

	if ( !isset($_POST["ruleName"]) || $_POST["ruleName"]=="" )
	{
		echo '<JobsAddRuleResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Name für JobRule nicht gefunden</shortMsg>'."\n";
		echo '		<longMsg>Es muss ein Name für die zu speichernde JobRule übermittelt werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</JobsAddRuleResponse>'."\n";
		exit;
	}

	if ( !isset($_POST["rule"]) || $_POST["rule"]=="")
	{
		echo '<JobsAddRuleResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Zeitangeben/Ausführungsregeln für JobRule nicht gefunden</shortMsg>'."\n";
		echo '		<longMsg>Es müssen Zeitangeben/Ausführungsregeln für die zu speichernde JobRule übermittelt werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</JobsAddRuleResponse>'."\n";
		exit;
	}
	
	$res=q("INSERT INTO job_rules (Name, Rules, firstmod, firstmod_user, lastmod, lastmod_user) VALUES ('".mysqli_real_escape_string($dbweb, $_POST["ruleName"])."', '".mysqli_real_escape_string($dbweb, $_POST["rule"])."', ".$_SESSION["id_user"].", ".time().", ".$_SESSION["id_user"].", ".time().");", $dbweb, __FILE__, __LINE__);
	$id_JobRule=mysqli_insert_id($dbweb);
	
	echo '<JobsAddRuleResponse>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	echo '	<Response><![CDATA['.$id_JobRule.']]></Response>'."\n";
	echo '</JobsAddRuleResponse>'."\n";

?>
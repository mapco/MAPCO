<?php

	if ( !isset($_POST["id_JobRule"]) || $_POST["id_JobRule"]=="" )
	{
		echo '<JobsGetRuleResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>ID für JobRule nicht gefunden</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine ID für die zu bearbeitende JobRule übermittelt werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</JobsGetRuleResponse>'."\n";
		exit;
	}

	$res=q("SELECT * FROM job_rules WHERE id_JobRule = '".$_POST["id_JobRule"]."';", $dbweb, __LINE__, __FILE__);
	if (mysqli_num_rows($res)==0)
	{
		echo '<JobsGetRuleResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>JobRule nicht gefunden</shortMsg>'."\n";
		echo '		<longMsg>Zur ID konnte keine JobRule gefunden werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</JobsGetRuleResponse>'."\n";
		exit;
	}

	$row=mysqli_fetch_array($res);
	
	echo '<JobsGetRuleResponse>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	echo '	<RulesName><![CDATA['.$row["Name"].']]></RulesName>'."\n";
	echo '	<rule><![CDATA['.$row["Rules"].']]></rule>'."\n";
	echo '</JobsGetRuleResponse>'."\n";

?>
<?php

	if ( !isset($_POST["listID"]) )
	{
		echo '<crm_update_customer_list>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Listen ID nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine Listen ID angegeben werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</crm_update_customer_list>'."\n";
		exit;
	}

	if ( !isset($_POST["title"]) )
	{
		echo '<crm_update_customer_list>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Titel nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Der Titel der zu bearbeitenden Liste konnte nicht gefunden werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</crm_update_customer_list>'."\n";
		exit;
	}

	if ( !isset($_POST["private"]) )
	{
		echo '<crm_update_customer_list>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Private/Public Einstellung nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss angegeben werden, ob die Liste privat oder öffentlich ist.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</crm_update_customer_list>'."\n";
		exit;
	}

	$res=q("SELECT * FROM crm_costumer_lists WHERE id_list = ".$_POST["listID"].";", $dbweb, __FILE__, __LINE__);
	if (mysql_num_rows($res)==0)
	{
		echo '<crm_update_customer_list>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Liste nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Zur ID konnte keine Liste gefunden werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</crm_update_customer_list>'."\n";
		exit;
	}

	q("UPDATE crm_costumer_lists SET title = '".mysql_real_escape_string($_POST["title"], $dbweb)."', private = ".$_POST["private"]." WHERE id_list = ".$_POST["listID"].";", $dbweb, __FILE__, __LINE__);
	
	echo "<crm_update_customer_list>\n";
	echo "<Ack>Success</Ack>\n";
	echo "</crm_update_customer_list>";

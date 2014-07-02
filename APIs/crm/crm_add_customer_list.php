<?php

	if ( !isset($_POST["customerListTitle"]) )
	{
		echo '<crm_add_customer_listResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Listentitel nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss ein Titel für anzulegende Liste angegeben werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</crm_add_customer_listResponse>'."\n";
		exit;
	}

	if ( !isset($_POST["ListPrivate"]) )
	{
		echo '<crm_add_customer_listResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Privat/Öffentlich Auswahl nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss angegeben werden, ob die Liste privat oder öffentlich ist.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</crm_add_customer_listResponse>'."\n";
		exit;
	}

	q("INSERT INTO crm_costumer_lists (title, private, firstmod, firstmod_user, lastmod, lastmod_user) VALUES ('".$_POST["customerListTitle"]."', ".$_POST["ListPrivate"].", ".time().", ".$_SESSION["id_user"].", ".time().", ".$_SESSION["id_user"].");", $dbweb, __FILE__, __LINE__);
	
	echo "<crm_add_customer_listResponse>\n";
	echo "<Ack>Success</Ack>\n";
	echo "</crm_add_customer_listResponse>";

?>
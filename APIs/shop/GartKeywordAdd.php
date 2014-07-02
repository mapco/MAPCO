<?php
	$starttime = time()+microtime();

	if ( !isset($_POST["GART"]) )
	{
		echo '<GartKeywordAdd>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>generische Artikelgruppe nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine generische Artikelgruppe ausgewählt werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</GartKeywordAdd>'."\n";
		exit;
	}

	if ( !isset($_POST["id_language"]) )
	{
		echo '<GartKeywordAdd>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Sprache nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine Sprache (id_language) übergeben werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</GartKeywordAdd>'."\n";
		exit;
	}

	if ( !($_POST["id_language"]>0) )
	{
		echo '<GartKeywordAdd>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Sprache ungültig.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine gültige Sprache (id_language) übergeben werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</GartKeywordAdd>'."\n";
		exit;
	}

	if ( !isset($_POST["keyword"]) )
	{
		echo '<GartKeywordAdd>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Synonym nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss ein Synonym (keyword) übergeben werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</GartKeywordAdd>'."\n";
		exit;
	}

	if ( $_POST["keyword"]=="" )
	{
		echo '<GartKeywordAdd>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Synonym leer.</shortMsg>'."\n";
		echo '		<longMsg>Das übergebene Synonym (keyword) darf nicht leer sein.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</GartKeywordAdd>'."\n";
		exit;
	}

	$query="SELECT GART FROM shop_items WHERE GART=".$_POST["GART"].";";
	$results=q($query, $dbshop, __FILE__, __LINE__);
	if ( mysqli_num_rows($results)==0 )
	{
		echo '<GartKeywordAdd>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>generische Artikelgruppe nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es konnte keine passende generische Artikelgruppe zur Auswahl gefunden werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</GartKeywordAdd>'."\n";
		exit;
	}

	$results=q("SELECT id FROM shop_items_keywords WHERE GART=".$_POST["GART"].";", $dbshop, __FILE__, __LINE__);
	$ordering=mysqli_num_rows($results)+1;
	q("INSERT INTO shop_items_keywords (GART, language_id, ordering, keyword, firstmod, firstmod_user, lastmod, lastmod_user) VALUES(".$_POST["GART"].", ".$_POST["id_language"].", ".$ordering.", '".mysqli_real_escape_string($dbshop, $_POST["keyword"])."', ".time().", ".$_SESSION["id_user"].", ".time().", ".$_SESSION["id_user"].");", $dbshop, __FILE__, __LINE__);
	
	echo '<GartKeywordAdd>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	echo '	<Runtime>'.number_format($time, 2).'</Runtime>'."\n";
	echo '</GartKeywordAdd>'."\n";

?>
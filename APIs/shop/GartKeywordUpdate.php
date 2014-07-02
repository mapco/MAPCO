<?php

	if ( !isset($_POST["id"]) )
	{
		echo '<GartKeywordUpdate>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Synonym-ID fehlt.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine Synonym-ID (id) übergeben werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</GartKeywordUpdate>'."\n";
		exit;
	}

	if ( !isset($_POST["keyword"]) )
	{
		echo '<GartKeywordUpdate>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Synonym nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss ein Synonym (keyword) übergeben werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</GartKeywordUpdate>'."\n";
		exit;
	}

	if ( $_POST["keyword"]=="" )
	{
		echo '<GartKeywordUpdate>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Synonym leer.</shortMsg>'."\n";
		echo '		<longMsg>Das übergebene Synonym (keyword) darf nicht leer sein.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</GartKeywordUpdate>'."\n";
		exit;
	}

	q("	UPDATE shop_items_keywords
		SET keyword='".mysqli_real_escape_string($dbshop, $_POST["keyword"])."',
			lastmod=".time().",
			lastmod_user=".$_SESSION["id_user"]."
		WHERE id=".$_POST["id"].";", $dbshop, __FILE__, __LINE__);
	
	echo '<GartKeywordUpdate>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	echo '</GartKeywordUpdate>'."\n";

?>
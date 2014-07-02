<?php

	if ( !isset($_POST["id"]) )
	{
		echo '<GartKeywordRemove>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Synonym-ID (id) nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine Synonym-ID (id) übergeben werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</GartKeywordRemove>'."\n";
		exit;
	}

	q("DELETE FROM shop_items_keywords WHERE id=".$_POST["id"].";", $dbshop, __FILE__, __LINE__);
	
	echo '<GartKeywordRemove>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	echo '	<Runtime>'.number_format($time, 2).'</Runtime>'."\n";
	echo '</GartKeywordRemove>'."\n";

?>
<?php
	if ( !isset($_POST["list_id"]) )
	{
		echo '<ListItemsRemove>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Listen-ID nicht gesetzt.</shortMsg>'."\n";
		echo '		<longMsg>Es muss einen Listen-ID übergeben werden, damit der Artikel zugeordnet werden kann.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ListItemsRemove>'."\n";
		exit;
	}

	q("DELETE FROM shop_lists_items WHERE list_id=".$_POST["list_id"].";", $dbshop, __FILE__, __LINE__);

	//return success
	echo '<ListItemsRemove>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	echo '</ListItemsRemove>'."\n";

?>
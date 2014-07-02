<?php
	if ( !isset($_POST["id_item"]) )
	{
		echo '<ItemCreateAuctionsResponse>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Shopartikel-ID nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss einen Shopartikel-ID übergeben werden, damit der Service weiß, zu welchem Shopartikel die Auktionen bearbeitet werden sollen.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ItemCreateAuctionsResponse>'."\n";
		exit;
	}
	
	q("UPDATE shop_items SET lastupdate=".time()." WHERE id_item=".$_POST["id_item"].";", $dbshop, __FILE__, __LINE__);
?>
<?php
	if ( !isset($_POST["ArtNr"]) )
	{
		echo '<ItemUpdateCOSResponse>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>ArtNr nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es konnte keine gültige ArtNr ermittelt werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ItemUpdateCOSResponse>'."\n";
		exit;
	}
	
	if ( !isset($_POST["COS"]) )
	{
		echo '<ItemUpdateCOSResponse>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>COS nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es konnte kein gültiger COS-Wert ermittelt werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ItemUpdateCOSResponse>'."\n";
		exit;
	}
	
	if ( !isset($_POST["VORGABE"]) )
	{
		echo '<ItemUpdateCOSResponse>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>VORGABE nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es konnte kein gültiger VORGABE-Wert ermittelt werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ItemUpdateCOSResponse>'."\n";
		exit;
	}
	
	q("UPDATE shop_items SET VORGABE='".str_replace(",", ".", $_POST["VORGABE"])."', COS='".str_replace(",", ".", $_POST["COS"])."' WHERE MPN='".$_POST["ArtNr"]."';", $dbshop, __FILE__, __LINE__);
		echo '<ItemUpdateCOSResponse>'."\n";
		echo '	<Ack>Success</Ack>'."\n";
		echo '</ItemUpdateCOSResponse>'."\n";
	

?>
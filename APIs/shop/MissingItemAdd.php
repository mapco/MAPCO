<?php

	if ( !isset($_POST["missingItem"]) &&  $_POST["missingItem"]!="")
	{
		echo '<MissingItemAddResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<ErrorCode>1</ErrorCode>'."\n";
		echo '		<shortMsg>MAPCO-Artikel nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine MAPCO-Artikelnummer eigegeben werden werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</MissingItemAddResponse>'."\n";
		exit;
	}

	$results=q("SELECT * FROM shop_items WHERE MPN='".$_POST["missingItem"]."';", $dbshop, __FILE__, __LINE__);
	if ( mysqli_num_rows($results)==0 )
	{
		echo '<MissingItemAddResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<ErrorCode>1</ErrorCode>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>gShop-Artikel nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Zur MAPCO-Artikelnummer konnte kein Shopartikel gefunden werden</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</MissingItemAddResponse>'."\n";
		exit;
	}
	
	if ( !isset($_POST["missingQty"]) &&  $_POST["missingQty"]!="")
	{
		echo '<MissingItemAddResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<ErrorCode>2</ErrorCode>'."\n";
		echo '		<shortMsg>Anzahl der fehlenden Artikel nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine Anzahl der fehlenden MAPCO-Artikel eigegeben werden werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</MissingItemAddResponse>'."\n";
		exit;
	}
	if (is_numeric($_POST["missingQty"]))
	{
		if (strpos($_POST["missingQty"], ",") || strpos($_POST["missingQty"], "."))
		{
			echo '<MissingItemAddResponse>'."\n";
			echo '	<Ack>Failure</Ack>'."\n";
			echo '	<Error>'."\n";
			echo '		<Code>'.__LINE__.'</Code>'."\n";
			echo '		<ErrorCode>2</ErrorCode>'."\n";
			echo '		<shortMsg>Ungüliger Wert für die Anzahl der fehlenden Artikel</shortMsg>'."\n";
			echo '		<longMsg>Es können nur ganzzahlige Werte eingegeben werden.</longMsg>'."\n";
			echo '	</Error>'."\n";
			echo '</MissingItemAddResponse>'."\n";
			exit;
		}
	}
	else 
	{
		echo '<MissingItemAddResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<ErrorCode>2</ErrorCode>'."\n";
		echo '		<shortMsg>Ungüliger Wert für die Anzahl der fehlenden Artikel</shortMsg>'."\n";
		echo '		<longMsg>Das Feld `Anzahl` enthält nichtnummerische Zeichen.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</MissingItemAddResponse>'."\n";
		exit;
	}
	
	$res=q("INSERT INTO shop_missing_items (MPN, Qty, comment, firstmod, firstmod_user) VALUES ('".mysqli_real_escape_string($dbshop, $_POST["missingItem"])."', ".$_POST["missingQty"].", '".mysqli_real_escape_string($dbshop, $_POST["missingComment"])."', ".time().", ".$_SESSION["id_user"]." );", $dbshop, __FILE__, __LINE__);
	
	echo '<MissingItemAddResponse>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	echo '</MissingItemAddResponse>'."\n";



?>
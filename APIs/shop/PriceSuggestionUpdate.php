<?php
	if ( !isset($_POST["id_pricesuggestion"]) )
	{
		echo '<PriceSuggestionUpdateResponse>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Es konnte keine Preisvorschlag-ID gefunden werden.</shortMsg>'."\n";
		echo '		<longMsg>Es konnte keine ID für den Preisvorschlag gefunden werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</PriceSuggestionUpdateResponse>'."\n";
		exit;
	}

	if ( !isset($_POST["price"]) )
	{
		echo '<PriceSuggestionUpdateResponse>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Es konnte kein Preisvorschlag gefunden werden.</shortMsg>'."\n";
		echo '		<longMsg>Es konnte kein Preisvorschlag für den Shopartikel gefunden werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</PriceSuggestionUpdateResponse>'."\n";
		exit;
	}
	$_POST["price"]=str_replace(",", ".", $_POST["price"]);

	if ( $_POST["price"]<=0 )
	{
		echo '<PriceSuggestionUpdateResponse>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Es konnte kein gültiger Preisvorschlag gefunden werden.</shortMsg>'."\n";
		echo '		<longMsg>Es konnte kein gültiger Preisvorschlag für den Shopartikel gefunden werden. Der Preis muss größer 0 sein.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</PriceSuggestionUpdateResponse>'."\n";
		exit;
	}

	if ( !isset($_POST["status"]) )
	{
		echo '<PriceSuggestionUpdateResponse>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Es konnte kein Status gefunden werden.</shortMsg>'."\n";
		echo '		<longMsg>Es konnte kein Status für den Preisvorschlag gefunden werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</PriceSuggestionUpdateResponse>'."\n";
		exit;
	}
	$_POST["price"]=str_replace(",", ".", $_POST["price"]);
	$_POST["price"]=round(round($_POST["price"]/1.19, 2)*1.19, 2);
	
	//prüfen ob der Preis vom vorgeschlagenen abweicht
	$suggestion=0;
	$res=q("SELECT * FROM shop_price_suggestions WHERE id_pricesuggestion=".$_POST["id_pricesuggestion"]." LIMIT 1;", $dbshop, __FILE__, __LINE__);
	if (mysqli_num_rows($res)>0) 
	{
		$row=mysqli_fetch_array($res);
		if ($_POST["status"]==2 && $_POST["price"]!=$row["price"])
		{
			$_POST["status"]=4;
			$suggestion=$row["price"];
		}
	}

	//decide status 0=pending, 1=accepted but not live, 2=chief accepted, 3=rejected, 4=accepted (chief price changed)

	//update table
	if ( $_POST["status"]==3 )
	{
	q("	UPDATE shop_price_suggestions
		SET price=".$_POST["price"].",
			status=".$_POST["status"].",
			imported=1,
			lastmod=".time().",
			lastmod_user=".$_SESSION["id_user"]."
		WHERE id_pricesuggestion=".$_POST["id_pricesuggestion"].";", $dbshop, __FILE__, __LINE__);
	}
	else
	{
		q("	UPDATE shop_price_suggestions
			SET price=".$_POST["price"].",
				suggestion=".$suggestion.",
				status=".$_POST["status"].",
				lastmod=".time().",
				lastmod_user=".$_SESSION["id_user"]."
			WHERE id_pricesuggestion=".$_POST["id_pricesuggestion"].";", $dbshop, __FILE__, __LINE__);
	}

	//response
	echo '<PriceSuggestionUpdateResponse>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	echo '</PriceSuggestionUpdateResponse>'."\n";
?>
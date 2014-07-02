<?php
	if ( !isset($_POST["id_account"]) )
	{
		echo '<StoreCategoriesEditResponse>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Account-ID nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss einen Account-ID übergeben werden, damit der Service weiß, mit welchem Account die Verbindung aufgebaut werden soll.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</StoreCategoriesEditResponse>'."\n";
		exit;
	}

	if ( !isset($_POST["GART"]) )
	{
		echo '<StoreCategoriesEditResponse>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Account-ID nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss einen Account-ID übergeben werden, damit der Service weiß, mit welchem Account die Verbindung aufgebaut werden soll.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</StoreCategoriesEditResponse>'."\n";
		exit;
	}

	if ( !isset($_POST["StoreCategory"]) )
	{
		echo '<StoreCategoriesEditResponse>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Shop-Kategorie 1 nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine Shop-Kategorie 1 (StoreCategory) übergeben werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</StoreCategoriesEditResponse>'."\n";
		exit;
	}

	if ( !isset($_POST["StoreCategory2"]) )
	{
		echo '<StoreCategoriesEditResponse>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Shop-Kategorie 2 nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine Shop-Kategorie 2 (StoreCategory2) übergeben werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</StoreCategoriesEditResponse>'."\n";
		exit;
	}

	$results=q("SELECT * FROM ebay_store_categories WHERE GART=".$_POST["GART"]." AND account_id=".$_POST["id_account"].";", $dbshop, __FILE__, __LINE__);
	if( mysqli_num_rows($results)>0 )
	{
		$row=mysqli_fetch_array($results);
		q("	UPDATE ebay_store_categories
			SET StoreCategory='".$_POST["StoreCategory"]."',
				StoreCategory2='".$_POST["StoreCategory2"]."'
			WHERE id=".$row["id"].";", $dbshop, __FILE__, __LINE__);
	}
	else
	{
		q("	INSERT INTO ebay_store_categories (GART, account_id, StoreCategory, StoreCategory2) VALUES(".$_POST["GART"].", ".$_POST["id_account"].", '".$_POST["StoreCategory"]."', '".$_POST["StoreCategory2"]."');", $dbshop, __FILE__, __LINE__);
	}

	echo '<StoreCategoriesEditResponse>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	echo '</StoreCategoriesEditResponse>'."\n";

?>
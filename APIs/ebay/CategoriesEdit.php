<?php
	if ( !isset($_POST["id_account"]) )
	{
		echo '<CategoriesEditResponse>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Account-ID nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss einen Account-ID übergeben werden, damit der Service weiß, mit welchem Account die Verbindung aufgebaut werden soll.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</CategoriesEditResponse>'."\n";
		exit;
	}

	if ( !isset($_POST["GART"]) )
	{
		echo '<CategoriesEditResponse>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Account-ID nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss einen Account-ID übergeben werden, damit der Service weiß, mit welchem Account die Verbindung aufgebaut werden soll.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</CategoriesEditResponse>'."\n";
		exit;
	}

	if ( !isset($_POST["CategoryID"]) )
	{
		echo '<CategoriesEditResponse>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Account-ID nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss einen Account-ID übergeben werden, damit der Service weiß, mit welchem Account die Verbindung aufgebaut werden soll.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</CategoriesEditResponse>'."\n";
		exit;
	}

	if ( !isset($_POST["CategoryID"]) )
	{
		echo '<CategoriesEditResponse>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Account-ID nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss einen Account-ID übergeben werden, damit der Service weiß, mit welchem Account die Verbindung aufgebaut werden soll.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</CategoriesEditResponse>'."\n";
		exit;
	}

	$results=q("SELECT * FROM ebay_categories WHERE GART=".$_POST["GART"]." AND account_id=".$_POST["id_account"].";", $dbshop, __FILE__, __LINE__);
	if( mysqli_num_rows($results)>0 )
	{
		$row=mysqli_fetch_array($results);
		q("	UPDATE ebay_categories
			SET CategoryID='".$_POST["CategoryID"]."',
				CategoryID2='".$_POST["CategoryID2"]."'
			WHERE id=".$row["id"].";", $dbshop, __FILE__, __LINE__);
	}
	else
	{
		q("	INSERT INTO ebay_categories (GART, account_id, CategoryID, CategoryID2) VALUES(".$_POST["GART"].", ".$_POST["id_account"].", '".$_POST["CategoryID"]."', '".$_POST["CategoryID2"]."');", $dbshop, __FILE__, __LINE__);
	}

	echo '<CategoriesEditResponse>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	echo '</CategoriesEditResponse>'."\n";

?>
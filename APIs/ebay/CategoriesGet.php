<?php
	if ( !isset($_POST["id_account"]) )
	{
		echo '<CategoriesGetResponse>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Account-ID nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss einen Account-ID übergeben werden, damit der Service weiß, mit welchem Account die Verbindung aufgebaut werden soll.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</CategoriesGetResponse>'."\n";
		exit;
	}

	$results=q("SELECT * FROM ebay_accounts WHERE id_account=".$_POST["id_account"].";", $dbshop, __FILE__, __LINE__);
	if ( mysqli_num_rows($results)==0 )
	{
		echo '<CategoriesGetResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Ebay-Account nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Der angegebene Ebay-Account konnte nicht gefunden werden. Die Account-ID scheint es nicht zu geben.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</CategoriesGetResponse>'."\n";
		exit;
	}
	$account=mysqli_fetch_array($results);

	echo '<CategoriesGet>';
	$results=q("SELECT * FROM ebay_categories WHERE account_id=".$_POST["id_account"].";", $dbshop, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		echo '<Category>';
		echo '	<GART>'.$row["GART"].'</GART>';
		echo '	<CategoryID>'.$row["CategoryID"].'</CategoryID>';
		echo '	<CategoryID2>'.$row["CategoryID2"].'</CategoryID2>';
		echo '</Category>';
	}
	echo '</CategoriesGet>';

?>
<?php
	if ( !isset($_POST["id_account"]) )
	{
		echo '<StoreCategoryResponse>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Account-ID nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss einen Account-ID übergeben werden, damit der Service weiß, mit welchem Account die Verbindung aufgebaut werden soll.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</StoreCategoryResponse>'."\n";
		exit;
	}

	$results=q("SELECT * FROM ebay_accounts WHERE id_account=".$_POST["id_account"].";", $dbshop, __FILE__, __LINE__);
	if ( mysqli_num_rows($results)==0 )
	{
		echo '<StoreCategoryResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Ebay-Account nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Der angegebene Ebay-Account konnte nicht gefunden werden. Die Account-ID scheint es nicht zu geben.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</StoreCategoryResponse>'."\n";
		exit;
	}
	$account=mysqli_fetch_array($results);

	$results=q("SELECT * FROM ebay_store_categories WHERE account_id=".$_POST["id_account"].";", $dbshop, __FILE__, __LINE__);
	echo '<StoreCategoryResponse>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	while( $row=mysqli_fetch_array($results) )
	{
		echo '<StoreCategory>';
		$keys=array_keys($row);
		for($i=0; $i<sizeof($keys); $i++)
		{
			if( !is_numeric($keys[$i]) )
				echo '	<'.$keys[$i].'>'.str_replace("&", "&amp;", ($row[$keys[$i]])).'</'.$keys[$i].'>'."\n";
		}
		echo '</StoreCategory>';
	}
	echo '</StoreCategoryResponse>'."\n";

?>
<?php
	if( !isset($_POST["id"]) )
	{
		echo '<DPDImportSuccessResponse>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>DPD-Importnummer nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine DPD-Importnummer (id) übergeben werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</DPDImportSuccessResponse>'."\n";
		exit;
	}
	$results=q("SELECT * FROM dpd_import WHERE id=".$_POST["id"].";", $dbshop, __FILE__, __LINE__);
	if( mysqli_num_rows($results)==0 )
	{
		echo '<DPDImportSuccessResponse>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>DPD-Importdaten nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Unter dieser DPD-Importnummer ('.$_POST["id"].') konnte kein Eintrag gefunden werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</DPDImportSuccessResponse>'."\n";
		exit;
	}
	$row=mysqli_fetch_array($results);

	if( !isset($_POST["id_order"]) )
	{
		echo '<DPDImportSuccessResponse>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Bestellnummer nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine Bestellnummer (id_order) übergeben werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</DPDImportSuccessResponse>'."\n";
		exit;
	}

	if( $row["CountryCode"]=="DE" ) $shipping_type_id=3; else $shipping_type_id=6;
	q("UPDATE shop_orders
	   SET	status_id=3,
	  		shipping_number='".$row["TrackingCode"]."',
			shipping_WeightInKG='".$row["Weight"]."',
			shipping_type_id=".$shipping_type_id.",
			lastmod=".time().",
			lastmod_user=".$_SESSION["id_user"]."
	   WHERE id_order=".$_POST["id_order"].";", $dbshop, __FILE__, __LINE__);

	q("UPDATE dpd_import SET order_id=".$_POST["id_order"].", imported=1 WHERE id=".$_POST["id"].";", $dbshop, __FILE__, __LINE__);

	echo '<DPDImportSuccessResponse>';
	echo '	<Ack>Success</Ack>';
	echo '</DPDImportSuccessResponse>';

?>
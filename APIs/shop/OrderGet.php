<?php
	include("../functions/cms_t.php");

	if( isset($_POST["auf_id"]) )
	{
		$results=q("SELECT * FROM shop_orders_auf_id WHERE AUF_ID=".$_POST["auf_id"].";", $dbshop, __FILE__, __LINE__);
		if ( mysqli_num_rows($results)==0 )
		{
			echo '<OrderGetResponse>'."\n";
			echo '	<Ack>Error</Ack>'."\n";
			echo '	<Error>'."\n";
			echo '		<Code>'.__LINE__.'</Code>'."\n";
			echo '		<shortMsg>Auftrags-ID (auf_id) ungültig.</shortMsg>'."\n";
			echo '		<longMsg>Die übergebene Auftrags-ID (auf_id) konnte in der Datenbank nicht gefunden werden.</longMsg>'."\n";
			echo '	</Error>'."\n";
			echo '</OrderGetResponse>'."\n";
			exit;
		}
		$row=mysqli_fetch_array($results);
		$_POST["id_order"]=$row["order_id"];
	}

	if ( !isset($_POST["id_order"]) or !($_POST["id_order"]>0) )
	{
		echo '<OrderGetResponse>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Bestellnummer nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es wird eine gültige Bestellnummer benötigt, damit der Service weiß, aus welche Bestellung die Daten zurück gegeben werden sollen.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</OrderGetResponse>'."\n";
		exit;
	}
	$results=q("SELECT * FROM shop_orders WHERE id_order=".$_POST["id_order"].";", $dbshop, __FILE__, __LINE__);
	if ( mysqli_num_rows($results)==0 )
	{
		echo '<OrderGetResponse>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Bestellung nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es scheint keine Bestellung mit der angegebenen Nummer zu existieren.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</OrderGetResponse>'."\n";
		exit;
	}
	$row=mysqli_fetch_array($results);
	$keys=array_keys($row);
	
	echo '<OrderGetResponse>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	for($i=0; $i<sizeof($keys); $i++)
	{
		if( !is_numeric($keys[$i]) )
			echo '	<'.$keys[$i].'><![CDATA['.$row[$keys[$i]].']]></'.$keys[$i].'>'."\n";
	}
	$id_order=$row["id_order"];
	if( $row["combined_with"]!=0 ) $id_order=$row["combined_with"];
	$results=q("SELECT * FROM shop_orders WHERE id_order=".$id_order." OR combined_with=".$id_order.";", $dbshop, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		$results2=q("SELECT * FROM shop_orders_items WHERE order_id=".$row["id_order"].";", $dbshop, __FILE__, __LINE__);
		while( $row2=mysqli_fetch_array($results2) )
		{
			$keys=array_keys($row2);
			echo '<OrderItem>'."\n";
			for($i=0; $i<sizeof($keys); $i++)
			{
				if( !is_numeric($keys[$i]) )
					echo '	<'.$keys[$i].'><![CDATA['.$row2[$keys[$i]].']]></'.$keys[$i].'>'."\n";
			}
			echo '</OrderItem>'."\n";
		}
	}
	echo '</OrderGetResponse>'."\n";

?>
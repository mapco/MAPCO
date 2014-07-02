<?php
	include("../../mapco_shop_de/functions/cms_url_encode.php");
	
	$required=array(); 
		
	check_man_params($required);
	
	$xml='';
	
	$results=q("SELECT * FROM shop_lists WHERE firstmod_user=".$_SESSION["id_user"]." AND listtype_id=5;", $dbshop, __FILE__, __LINE__);
	if(mysqli_num_rows($results)>0)
	{
		$row=mysqli_fetch_array($results);
		$results2=q("SELECT * FROM shop_lists_items WHERE list_id=".$row["id_list"].";", $dbshop, __FILE__, __LINE__);
		if(mysqli_num_rows($results2)>0)
		{
			while($row2=mysqli_fetch_array($results2))
			{
				$results3=q("SELECT * FROM shop_items_de WHERE id_item=".$row2["item_id"].";", $dbshop, __FILE__, __LINE__);
				$row3=mysqli_fetch_array($results3);
				
				$responseXml = post(PATH."soa/", array("API" => "shop", "Action" => "StockGet", "id_item" => $row2["item_id"]));
				$use_errors = libxml_use_internal_errors(true);
				try
				{
					$response = new SimpleXMLElement($responseXml);
				}
				catch(Exception $e)
				{
					echo '<ReorderListGetResponse>'."\n";
					echo '	<Ack>Failure</Ack>'."\n";
					echo '	<Error>'."\n";
					echo '		<Code>'.__LINE__.'</Code>'."\n";
					echo '		<shortMsg>Istbestand-Abfrage fehlgeschlagen.</shortMsg>'."\n";
					echo '		<longMsg>Bei der Abfrage des Istbestands ist ein Fehler aufgetreten.</longMsg>'."\n";
					echo '	</Error>'."\n";
					echo '</ReorderListGetResponse>'."\n";
					exit;
				}
				libxml_clear_errors();
				libxml_use_internal_errors($use_errors);
				$stock=$response->Stock[0];
				
				$xml.='<item>'."\n";
				$xml.='  <id>'.$row2["id"].'</id>'."\n";
				$xml.='  <item_id>'.$row2["item_id"].'</item_id>'."\n";
				$xml.='  <title>'.$row3["title"].'</title>'."\n";
				$xml.='  <url_title>'.url_encode($row3["title"]).'</url_title>'."\n";
				$xml.='  <amount>'.$row2["amount"].'</amount>'."\n";
				$xml.='  <stock>'.$stock.'</stock>'."\n";
				$xml.='</item>'."\n";
			}
		}
	}
		
	//return success
	//echo '<ReorderListGetResponse>'."\n";
	//echo '	<Ack>Success</Ack>'."\n";
	echo $xml;
	//echo '</ReorderListGetResponse>'."\n";

?>
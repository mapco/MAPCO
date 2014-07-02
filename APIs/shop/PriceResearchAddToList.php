<?php

	if ( !isset($_POST["id_list"]) )
	{
		echo '<PriceResearchAddToList>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Listen-ID nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es konnte keine Listen-ID (id_list) gefunden werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</PriceResearchAddToList>'."\n";
		exit;
	}
	$results=q("SELECT * FROM shop_lists WHERE id_list=".$_POST["id_list"].";", $dbshop, __FILE__, __LINE__);
	if( mysqli_num_rows($results)==0 )
	{
		echo '<PriceResearchAddToList>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Liste nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es konnte keine Listen unter der angegebenen Listen-ID (id_list) gefunden werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</PriceResearchAddToList>'."\n";
		exit;
	}

	/*$i=0;
	$items=array();
	$results=q("SELECT * FROM shop_items WHERE KFZ_BESTAND_TECDOC>100000 AND InPriceResearch = 0;", $dbshop, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		//FILTER EINZELBREMSSCHEIBEN
		if ($row["GART"]!=82 ||($row["GART"]==82 && strpos($row["MPN"], "/2")===true))
		{		
			$results2=q("SELECT * FROM lager WHERE ArtNr='".$row["MPN"]."';", $dbshop, __FILE__, __LINE__);
			if( mysqli_num_rows($results2)>0 )
			{
				$row2=mysqli_fetch_array($results2);
				if( ($row2["ISTBESTAND"]+$row2["MOCOMBESTAND"])>10 )
				{
					$results3=q("SELECT * FROM shop_price_suggestions WHERE item_id=".$row["id_item"].";", $dbshop, __FILE__, __LINE__);
					if( mysqli_num_rows($results3)==0 )
					{
						$responseXml=post(PATH."soa/", array("API" => "shop", "Action" => "ListAddItem", "list_id" => $_POST["id_list"], "item_id" => $row["id_item"]) );
	//					q("INSERT INTO shop_lists_items (list_id, item_id) VALUES(".$_POST["id_list"].", ".$row["id_item"].");", $dbshop, __FILE__, __LINE__);
						$i++;
	
						libxml_clear_errors();
						libxml_use_internal_errors($use_errors);
	
						try
						{
							$response = new SimpleXMLElement($responseXml);
						}
						catch(Exception $e)
						{
							echo '<PriceReasearchAddToListResponse>'."\n";
							echo '	<Ack>Failure</Ack>'."\n";
							echo '	<Error>'."\n";
							echo '		<Code>'.__LINE__.'</Code>'."\n";
							echo '		<shortMsg>XML nicht valide.</shortMsg>'."\n";
							echo '		<longMsg>Die zurückgelieferten XML-Daten (ListAddItem) sind nicht valide und können deshalb nicht ausgewertet werden. Service gestoppt.</longMsg>'."\n";
							echo '	</Error>'."\n";
							echo '</PriceReasearchAddToListResponse>'."\n";
							exit;
						}
						libxml_clear_errors();
						libxml_use_internal_errors($use_errors);
						if ($response->Ack[0]=="Success")
						{
							//SET FLAG FOR 'InPriceResearch'
							q("UPDATE shop_items SET InPriceResearch = 1 WHERE id_item = ".$row["id_item"].";", $dbshop, __FILE__, __LINE__);
						}
					}
				}
			}
			if( $i>9 ) break;
		}
	}*/
	
	//*************************************************************************************************************************************
	$pricesuggestions=array();
	$items=array();
	$price=array();
	$results=q("SELECT * FROM shop_price_suggestions WHERE firstmod>".(time()-3*30*24*3600).";", $dbshop, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		$pricesuggestions[$row["item_id"]]=0;
	}
	
	$results=q("SELECT * FROM prpos WHERE LST_NR=4 AND POS_0_WERT>=50;", $dbshop, __FILE__, __LINE__);
	while($row=mysqli_fetch_array($results))
	{
		$price[$row["ARTNR"]]=$row["POS_0_WERT"];
	}
	
	//remove priceresearched items from list
	$results=q("SELECT * FROM shop_lists_items WHERE list_id=".$_POST["id_list"].";", $dbshop, __FILE__, __LINE__);
	while($row=mysqli_fetch_array($results))
	{
		if(isset($pricesuggestions[$row["item_id"]]))
		{
			$results2=q("DELETE FROM shop_lists_items WHERE id=".$row["id"].";", $dbshop, __FILE__, __LINE__);
		}
	}


	//wrong or missing prices
	$items=array();
	$results=q("SELECT * FROM prpos WHERE LST_NR=16815;", $dbshop, __FILE__, __LINE__);
	while($row=mysqli_fetch_array($results))
	{
		$ebay_price[$row["ARTNR"]]=$row["POS_0_WERT"];
	}
	$results=q("SELECT * FROM prpos WHERE LST_NR=5;", $dbshop, __FILE__, __LINE__);
	while($row=mysqli_fetch_array($results))
	{
		if(!isset($ebay_price[$row["ARTNR"]]) or $row["POS_0_WERT"]<$ebay_price[$row["ARTNR"]])
		{
			$results2=q("SELECT id_item FROM shop_items WHERE active>0 AND MPN='".$row["ARTNR"]."' LIMIT 1;", $dbshop, __FILE__, __LINE__);
			if(mysqli_num_rows($results2)>0)
			{
				$row2=mysqli_fetch_array($results2);
				$items[]=$row2["id_item"];
				$responseXml=post(PATH."soa/", array("API" => "shop", "Action" => "ListAddItem", "list_id" => $_POST["id_list"], "item_id" => $row2["id_item"]) );
				if(sizeof($items)==10) break;
			}
		}
	}

	//important parts	
	if( sizeof($items)==0 )
	{
		$items=array();
		$results=q("SELECT a.id_item, (b.ISTBESTAND+b.MOCOMBESTAND) AS Istbestand, a.MPN, a.GART FROM shop_items AS a, lager AS b WHERE b.ArtNr=a.MPN AND a.InPriceResearch=0
		 AND a.KFZ_BESTAND_TECDOC>=100000 AND a.GART>0 ORDER BY Istbestand DESC;", $dbshop, __FILE__, __LINE__);
		while( $row=mysqli_fetch_array($results) )
		{
			if( !isset($pricesuggestions[$row["id_item"]]) and ($row["GART"]!=82 ||($row["GART"]==82 && strpos($row["MPN"], "/2")===true)) and isset($price[$row["MPN"]]) and $row["GART"]!=4 and $row["GART"]!=2 and $row["Istbestand"]>3)
			{
				$items[]=$row["id_item"];
				$responseXml=post(PATH."soa/", array("API" => "shop", "Action" => "ListAddItem", "list_id" => $_POST["id_list"], "item_id" => $row["id_item"]) );
			}
			if(sizeof($items)==10) break;
		}
	}
	
	//less important parts	
	if( sizeof($items)==0 )
	{
		$items=array();
		$results=q("SELECT a.id_item, (b.ISTBESTAND+b.MOCOMBESTAND) AS Istbestand, a.MPN, a.GART FROM shop_items AS a, lager AS b WHERE b.ArtNr=a.MPN AND a.InPriceResearch=0
		 AND a.GART>0 ORDER BY Istbestand DESC;", $dbshop, __FILE__, __LINE__);
		while( $row=mysqli_fetch_array($results) )
		{
			if( !isset($pricesuggestions[$row["id_item"]]) and ($row["GART"]!=82 ||($row["GART"]==82 && strpos($row["MPN"], "/2")===true)) and isset($price[$row["MPN"]]) and $row["GART"]!=4 and $row["GART"]!=2 and $row["Istbestand"]>3)
			{
				$items[]=$row["id_item"];
				$responseXml=post(PATH."soa/", array("API" => "shop", "Action" => "ListAddItem", "list_id" => $_POST["id_list"], "item_id" => $row["id_item"]) );
			}
			if(sizeof($items)==10) break;
		}
	}
	

	//rest
	if( sizeof($items)==0 )
	{
		$i=0;
		$items=array();
		$results=q("SELECT a.id_item, (b.ISTBESTAND+b.MOCOMBESTAND) AS Istbestand, a.MPN, a.GART FROM shop_items AS a, lager AS b WHERE b.ArtNr=a.MPN AND a.InPriceResearch=0 AND a.GART>0 ORDER BY Istbestand DESC;", $dbshop, __FILE__, __LINE__);
		while( $row=mysqli_fetch_array($results) )
		{
			if( !isset($pricesuggestions[$row["id_item"]]) and ($row["GART"]!=82 ||($row["GART"]==82 && strpos($row["MPN"], "/2")===true)) and $row["GART"]!=4 and $row["GART"]!=2)
			{
				$items[]=$row["id_item"];
				$responseXml=post(PATH."soa/", array("API" => "shop", "Action" => "ListAddItem", "list_id" => $_POST["id_list"], "item_id" => $row["id_item"]) );
			}
			if(sizeof($items)==10) break;
		}
	}
	
	$i=sizeof($items);
	
	//*************************************************************************************************************************************
	echo '<PriceResearchAddToList>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	echo '	<Items>'.$i.'</Items>A'."\n";
	echo '</PriceResearchAddToList>'."\n";

?>
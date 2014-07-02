<?php
	if ( !isset($_POST["list_id"]) )
	{
		echo '<ItemExportResponse>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Listen-ID nicht gesetzt.</shortMsg>'."\n";
		echo '		<longMsg>Es muss einen Listen-ID übergeben werden, damit der Artikel zugeordnet werden kann.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ItemExportResponse>'."\n";
		exit;
	}

	if ( !isset($_POST["text"]) )
	{
		echo '<ItemExportResponse>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Artikel-ID nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine Artikel-ID übergeben werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ItemExportResponse>'."\n";
		exit;
	}

	$found=0;
	$skipped=array();
	$numbers=$_POST["text"];
	$numbers=str_replace(";", "\n", $numbers);
	$numbers=str_replace(",", "\n", $numbers);
	$numbers=explode("\n", $numbers);
	for($i=0; $i<sizeof($numbers); $i++)
	{
		$number=trim($numbers[$i]);
		if( $number!="" )
		{
			$skip=true;
			
			//search for MAPCO ArtNr
			$results=q("SELECT * FROM shop_items WHERE MPN='".$number."';", $dbshop, __FILE__, __LINE__);
			if (mysqli_num_rows($results)>0)
			{
				$row=mysqli_fetch_array($results);
				$results=q("SELECT * FROM shop_lists_items WHERE list_id=".$_POST["list_id"]." AND item_id=".$row["id_item"].";", $dbshop, __FILE__, __LINE__);
				if ( mysqli_num_rows($results)==0 )
				{
					q("INSERT INTO shop_lists_items (list_id, item_id, firstmod, firstmod_user, lastmod, lastmod_user) VALUES(".$_POST["list_id"].", ".$row["id_item"].", ".time().", ".$_SESSION["id_user"].", ".time().", ".$_SESSION["id_user"].");", $dbshop, __FILE__, __LINE__);
				}
				$found++;
				$skip=false;
			}
	
			//search for MAPCO ArtNr
			if( is_numeric($number) )
			{
				$results=q("SELECT * FROM ebay_auctions WHERE ItemID='".$number."';", $dbshop, __FILE__, __LINE__);
				if (mysqli_num_rows($results)>0)
				{
					$row=mysqli_fetch_array($results);
					$results=q("SELECT * FROM shop_lists_items WHERE list_id=".$_POST["list_id"]." AND item_id=".$row["shopitem_id"].";", $dbshop, __FILE__, __LINE__);
					if ( mysqli_num_rows($results)==0 )
					{
						q("INSERT INTO shop_lists_items (list_id, item_id, firstmod, firstmod_user, lastmod, lastmod_user) VALUES(".$_POST["list_id"].", ".$row["shopitem_id"].", ".time().", ".$_SESSION["id_user"].", ".time().", ".$_SESSION["id_user"].");", $dbshop, __FILE__, __LINE__);
					}
					$found++;
					$skip=false;
				}
			}
			
			if ($skip) $skipped[]=$number;
		}
	}

	//return success
	echo '<ItemExportResponse>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	echo '	<Found>'.$found.'</Found>'."\n";
	for($i=0; $i<sizeof($skipped); $i++)
	{
		echo '	<Skipped>'.$skipped[$i].'</Skipped>'."\n";
	}
	echo '</ItemExportResponse>'."\n";

?>
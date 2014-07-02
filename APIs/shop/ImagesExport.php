<?php
	
	if ( !isset($_POST["id_item"]) )
	{
		echo '<ItemExportResponse>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Es konnte keine Shopartikel-ID gefunden werden.</shortMsg>'."\n";
		echo '		<longMsg>Es konnte keine Shopartikel-ID gefunden werden. Die ID ist notwendig, da der Service sonst nicht weiß, welchen Shopartikel er exportieren soll.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ItemExportResponse>'."\n";
		exit;
	}

	if ( !isset($_POST["zipname"]) )
	{
		echo '<ItemExportResponse>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Es konnte keine Shopartikel-ID gefunden werden.</shortMsg>'."\n";
		echo '		<longMsg>Es konnte keine Shopartikel-ID gefunden werden. Die ID ist notwendig, da der Service sonst nicht weiß, welchen Shopartikel er exportieren soll.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ItemExportResponse>'."\n";
		exit;
	}

	$results=q("SELECT * FROM amazon_products WHERE item_id=".$_POST["id_item"]." AND account_id=5;", $dbshop, __FILE__, __LINE__);
	if ( mysqli_num_rows($results)==0 )
	{
		echo '<ItemExportResponse>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Es konnte kein gültiger Shopartikel gefunden werden.</shortMsg>'."\n";
		echo '		<longMsg>Es konnte kein gültiger Shopartikel gefunden werden. Die ID ist ungültig.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ItemExportResponse>'."\n";
		exit;
	}
	$row=mysqli_fetch_array($results);
	$ASIN=$row["ASIN"];
	
	//create file
	if ( !file_exists($_POST["zipname"]) )
	{
		$handle=fopen($_POST["zipname"], "w");
		fclose($handle);
	}
	
	$zip = new ZipArchive;
	if ($zip->open($_POST["zipname"]) === false)
	{
		echo '<ItemExportResponse>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>ZIP-Datei kann nicht geöffnet werden.</shortMsg>'."\n";
		echo '		<longMsg>ZIP-Datei kann nicht geöffnet werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ItemExportResponse>'."\n";
		exit;
	}

	$results=q("SELECT * FROM shop_items WHERE id_item=".$_POST["id_item"].";", $dbshop, __FILE__, __LINE__);
	$row=mysqli_fetch_array($results);
	$results=q("SELECT * FROM cms_articles_images WHERE article_id=".$row["article_id"]." ORDER BY ordering;", $dbweb, __FILE__, __LINE__);
	$i=0;
	if ( mysqli_num_rows($results)==0 )
	{
		$source='../files/0en.jpg';
		$destination=$ASIN.".MAIN.jpg";
		if ( $zip->addFile($source, $destination) === false)
		{
			echo 'FEHLER';
			exit;
		}
	}
	else
	{
		while( $row=mysqli_fetch_array($results) )
		{
			$results2=q("SELECT * FROM cms_files WHERE original_id=".$row["file_id"]." AND imageformat_id=19 LIMIT 1;", $dbweb, __FILE__, __LINE__);
			$row2=mysqli_fetch_array($results2);
			$source='../files/'.floor(bcdiv($row2["id_file"], 1000)).'/'.$row2["id_file"].'.'.$row2["extension"];
			if ( $i==0 )
			{
				$destination=$ASIN.".MAIN.jpg";
			}
			else
			{
				$destination=$ASIN.".PT0".$i.".jpg";
			}
			$i++;
			//add file to zip-archive
			if ( $zip->addFile($source, $destination) === false)
			{
				echo 'FEHLER';
				exit;
			}
		}
	}
	
	//return URL
	echo '<a href="'.PATH."soa/".$_POST["zipname"].'">Download</a>';

?>
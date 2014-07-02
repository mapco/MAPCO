<?php
	$starttime = time()+microtime();

	if ( !isset($_POST["date"]) )
	{
		echo '<ImageMassDownloadResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Datum nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss ein Datum übermittelt werden, damit der Service weiß, ab wann Fotos exportiert werden sollen.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ImageMassDownloadResponse>'."\n";
		exit;
	}

	if ( !isset($_POST["hour"]) )
	{
		echo '<ImageMassDownloadResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Stunde nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine Stundenangabe übermittelt werden, damit der Service weiß, ab wann Fotos exportiert werden sollen.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ImageMassDownloadResponse>'."\n";
		exit;
	}

	if ( !isset($_POST["minute"]) )
	{
		echo '<ImageMassDownloadResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Minute nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine Minutenangabe übermittelt werden, damit der Service weiß, ab wann Fotos exportiert werden sollen.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ImageMassDownloadResponse>'."\n";
		exit;
	}

	if ( !isset($_POST["format"]) )
	{
		echo '<ImageMassDownloadResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Format nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss ein Format übermittelt werden, damit der Service weiß, in welchem Format die Fotos exportiert werden sollen.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ImageMassDownloadResponse>'."\n";
		exit;
	}

	//create timestamp
	$timestamp=strtotime($_POST["date"])+($_POST["hour"]*3600)+($_POST["minute"]*60);

	//create zipfile
	if ($_POST["format"]==1) $zipfile="export_tecdoc.zip";
	elseif ($_POST["format"]==2) $zipfile="export_idims.zip";
	else $zipfile="export.zip";
	$handle=fopen($zipfile, "w");
	fclose($handle);

	//create filelist
	$SKU=array();
	$results=q("SELECT * FROM shop_items;", $dbshop, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		$SKU[$row["article_id"]]=str_replace("/", "_", $row["MPN"]);
	}
	
	$article=array();
	$results=q("SELECT * FROM cms_articles_labels WHERE label_id=11;", $dbweb, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		$article[$row["article_id"]]=$row["article_id"];
	}

	$files=array();
	$artnr=array();
	$results=q("SELECT * FROM cms_articles_images WHERE firstmod>=".$timestamp.";", $dbweb, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		if ( isset($article[$row["article_id"]]) )
		{
			if ( isset($SKU[$row["article_id"]]) )
			{
				$files[]=$row["file_id"];
				$artnr[]=$SKU[$row["article_id"]];
			}
		}
	}
	
	array_multisort($artnr, $files);

	//output	
	echo '<ImageMassUploadResponse>'."\n";
	echo '	<ExportFile>'.$zipfile.'</ExportFile>'."\n";
	$last="";
	$j=0;
	$alphabet=array("a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", "n", "o", "p", "q", "r");
	for($i=0; $i<sizeof($files); $i++)
	{
		if ( $last!=$artnr[$i] )
		{
			$last=$artnr[$i];
			$j=0;
		}
		echo '	<Image filename="'.$artnr[$i].$alphabet[$j].'.jpg">'.$files[$i].'</Image>'."\n";
		$j++;
	}
	echo '	<Ack>Success</Ack>'."\n";
	$stoptime = time()+microtime();
	$time = $stoptime-$starttime;
	echo '	<Runtime>'.number_format($time, 2).'</Runtime>'."\n";
	echo '</ImageMassUploadResponse>'."\n";

?>
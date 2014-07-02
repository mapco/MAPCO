<?php
	include("config.php");
	include("functions/cms_t2.php");
	include("templates/".TEMPLATE_BACKEND."/header.php");

	//functions
	function fwriteln($handle, $text)
	{
		fwrite($handle, $text."\n");
	}
	function readdirs($start)
	{
		$dirs=array();
		$dirs[sizeof($dirs)]=$start;
		if (@$handle = opendir($start))
		{
			while (false !== ($file = readdir($handle)))
			{
				if ($file != "." && $file != "..")
				{
					if (is_dir($start.'/'.$file))
					{
						$dirs[sizeof($dirs)]=$start.'/'.$file;
						readdirs($start.'/'.$file);
					}
				}
			}
			closedir($handle);
		}
		return($dirs);
	}

	//Katalog herunterladen
	if($_POST["form_button"]=="Katalog herunterladen")
	{
		//create empty zip archive
		$archivname="mapco_catalogs.zip";
		$start_dir="CD";
		$handle=fopen($archivname, "w");
		fclose($handle);

		//read dirs
		$dirs=readdirs($start_dir);
	
		//zip files
		echo 'Generiere ZIP-Archiv ... ';
		$zip = new ZipArchive;
		if ($zip->open($archivname) === TRUE)
		{
			foreach($dirs as $dir)
			{
				$handle=opendir($dir);
				while (false !== ($file = readdir($handle)))
				{
					if ($file!="." and $file!=".." and !is_dir($file))
					{
						if ($zip->addFile($dir.'/'.$file, $dir.'/'.$file))
						{
//							echo 'Datei "'.$file.'" in Archiv hinzugefügt.<br />';
						}
						else die('<p>Fehler beim Hinzufügen der Datei "'.$file.'".</p>');
					}
				}
			}
		}
		else echo '<p>Fehler beim Öffnen des ZIP-Archivs.</p>';
		$zip->close();
		echo 'OK.<br />';
		echo '<a href="mapco_catalogs.zip">Jetzt herunterladen</a><hr />';
	}
	
	//Sprachvariablen und -konstanten
	$sprachen=array("de", "en", "fr", "ru", "it", "zh");
	$sprache=array("Deutsch", "English", "Français", "Русский", "Italiano", "中文");


	//TecDoc catalog
	echo 'Generiere CD/index.html ... ';
	$handle=fopen("CD/index.html", "w");
	fwriteln($handle, '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">');
	fwriteln($handle, '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="de" lang="de">');
	fwriteln($handle, '	<head>');
	fwriteln($handle, '		<link rel="stylesheet" type="text/css" href="css/styles.css" />');
	fwriteln($handle, '		<meta http-equiv="content-type" content="text/html; charset=utf-8" />');
	fwriteln($handle, '		<title>MAPCO Autotechnik GmbH</title>');
	fwriteln($handle, '	</head>');
	fwriteln($handle, '	<body>');
	fwriteln($handle, '		<p><div id="banner"></div></p>');
	fwriteln($handle, '		<div id="hauptmenue"> ');
	fwriteln($handle, '		<ul>');
	fwriteln($handle, '			<li style="float: left;width: 172px;text-align: center;height: 82px;background-image: url(images/hauptnavi_kopf.gif);background-repeat: no-repeat;"></li>');
	for ($i=0; $i<sizeof($sprachen); $i++)
	{
		fwriteln($handle, '			<li><a href="'.$sprachen[$i].'/index.html">'.$sprache[$i].'</a></li>');
	}
	fwriteln($handle, '			<li style="float: left;width: 172px;text-align: center;height: 134px;background-image: url(images/hauptnavi_mitte.gif);background-repeat: no-repeat;"></li>');
	fwriteln($handle, '			<li style="float: left;width: 172px;text-align: center;height: 75px;background-image: url(images/hauptnavi_unten.gif);background-repeat: no-repeat;"></li>');
	fwriteln($handle, '</ul>');
	fwriteln($handle, '</div>');
	fwriteln($handle, '		<div id="content">');
	fwriteln($handle, '			<div id="content_header">');
	fwriteln($handle, '			</div>');
	fwriteln($handle, '		<div id="content_container">');
	fwriteln($handle, '			<img src="images/logo.jpg" />');
	fwriteln($handle, '				<table>');
	fwriteln($handle, '					<tr>');
	for ($i=0; $i<sizeof($sprachen); $i++)
	{
		fwriteln($handle, '<td align="center">');
		fwriteln($handle, '<a href="'.$sprachen[$i].'/index.html">');
		fwriteln($handle, '<img style="margin:0;" src="images/sprachen_'.$sprachen[$i].'.jpg" border="0" alt="'.$sprache[$i].'" title="'.$sprache[$i].'" /><br />');
		fwriteln($handle, $sprache[$i]);
		fwriteln($handle, '</a>');
		fwriteln($handle, '</td>');
	}
	fwriteln($handle, '					</tr>');
	fwriteln($handle, '				</table>');
	fwriteln($handle, '				</div>');
	fwriteln($handle, '		</div>');
	fwriteln($handle, '	</body>');
	fwriteln($handle, '</html>');
	fclose($handle);
	echo 'OK.<br />';


	//catalogues-Ordner
	if (!file_exists('CD/catalogues'))
	{
		echo 'Erstelle Verzeichnis CD/catalogues ... ';
		mkdir('CD/catalogues');
		echo 'OK.<br />';
	}
	echo 'Kopiere Kataloge und Katalogbilder ... ';
	$results=q("SELECT * FROM web_catalogs WHERE active ORDER BY title;", $dbweb, __FILE__, __LINE__);
	while($row=mysqli_fetch_array($results))
	{
		copy('images/kataloge/'.strtolower($row["number"]).'.jpg', 'CD/catalogues/'.strtolower($row["number"]).'.jpg');	
		copy('images/kataloge/'.strtolower($row["number"]).'.pdf', 'CD/catalogues/'.strtolower($row["number"]).'.pdf');	
		if( $row["number_fr"] )
		{
			copy('images/kataloge/'.strtolower($row["number"]).'.pdf', 'CD/catalogues/'.strtolower($row["number_fr"]).'.pdf');	
		}
	}
	echo 'OK.<br />';


	//Sprachordner generieren
	for ($i=0; $i<sizeof($sprachen); $i++)
	{
		$results=q("SELECT * FROM web_translations;", $dbweb, __FILE__, __LINE__);
		while($row=mysqli_fetch_array($results))
		{
			if ($row[$sprachen[$i]]!="") $t[$row["constant"]]=$row[$sprachen[$i]];
			elseif ($row["en"]!="") $t[$row["constant"]]=$row["en"];
			else $t[$row["constant"]]=$row["de"];
		}
		
		//Verzeichnis erstellen
		if (!file_exists('CD/'.$sprachen[$i]))
		{
			echo 'Erstelle Verzeichnis CD/'.$sprachen[$i].' ... ';
			mkdir('CD/'.$sprachen[$i]);
			echo 'OK.<br />';
		}
		
		//index.html erstellen
		echo 'Generiere CD/'.$sprachen[$i].'/index.html ... ';
		$handle=fopen('CD/'.$sprachen[$i].'/index.html', "w");
		fwriteln($handle, '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">');
		fwriteln($handle, '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="de" lang="de">');
		fwriteln($handle, '<head>');
		fwriteln($handle, '		<link rel="stylesheet" type="text/css" href="../css/styles.css" />');
		fwriteln($handle, '<meta http-equiv="content-type" content="text/html; charset=utf-8" />');
		fwriteln($handle, '<title>MAPCO: Autoersatzteile GmbH</title>	');
		fwriteln($handle, '</head>');
		fwriteln($handle, '<body>');
		fwriteln($handle, '		<p><div id="banner"></div>');
		fwriteln($handle, '		<div id="hauptmenue">');
		fwriteln($handle, '			<ul>');
		fwriteln($handle, '				<li style="float: left;width: 172px;text-align: center;height: 82px;background-image: url(../images/hauptnavi_kopf.gif);background-repeat: no-repeat;"></li>');
		fwriteln($handle, '				<li><a href="../index.html">Home</a></li>');
		fwriteln($handle, '				<li><a href="index.html">Video</a></li>');
		fwriteln($handle, '				<li><a href="catalogue.html">PDF-'.$t["KATALOGE"].'</a></li>');
		fwriteln($handle, '				<li><a href="adobe.html">Adobe</a></li>');
		fwriteln($handle, '				<li style="float: left;width: 172px;text-align: center;height: 134px;background-image: url(../images/hauptnavi_mitte.gif);background-repeat: no-repeat;"></li>');
		fwriteln($handle, '				<li style="float: left;width: 172px;text-align: center;height: 75px;background-image: url(../images/hauptnavi_unten.gif);background-repeat: no-repeat;"></li>');
		fwriteln($handle, '			</ul>');
		fwriteln($handle, '		</div>');
		fwriteln($handle, '		<div id="content">');
		fwriteln($handle, '			<div id="content_header">');
		fwriteln($handle, '				<p>Video</p>');
		fwriteln($handle, '			</div>');
		fwriteln($handle, '		<div id="content_container">');
		fwriteln($handle, '			<embed src="../video/OSplayer.swf?movie=MesseParis2013.mov&btncolor=0x333333&accentcolor=0x31b8e9&txtcolor=0xdddddd&volume=30&autoload=on&autoplay=on&vTitle=MAPCO Autotechnik&showTitle=yes" width="690" height="577" allowFullScreen="true" type="application/x-shockwave-flash">');
		fwriteln($handle, '		</div>');
		fwriteln($handle, '	</div>');
		fwriteln($handle, '</body>');
		fwriteln($handle, '</html>	');
		fclose($handle);
		echo 'OK.<br />';
		
		//TECDOC index.html erstellen
/*
		echo 'Generiere CD/'.$sprachen[$i].'/index.html ... ';
		$handle=fopen('CD/'.$sprachen[$i].'/index.html', "w");
		fwriteln($handle, '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">');
		fwriteln($handle, '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="de" lang="de">');
		fwriteln($handle, '<head>');
		fwriteln($handle, '		<link rel="stylesheet" type="text/css" href="../css/styles.css" />');
		fwriteln($handle, '<meta http-equiv="content-type" content="text/html; charset=utf-8" />');
		fwriteln($handle, '<title>MAPCO: Autoersatzteile GmbH</title>	');
		fwriteln($handle, '</head>');
		fwriteln($handle, '<body>');
		fwriteln($handle, '<p><div id="banner"></div></p>');
		fwriteln($handle, '<div id="hauptmenue"> ');
		fwriteln($handle, '<ul>');
		fwriteln($handle, '<li style="float: left;width: 172px;text-align: center;height: 82px;background-image: url(../images/hauptnavi_kopf.gif);background-repeat: no-repeat;"></li>');
		fwriteln($handle, '<li><a href="../index.html">Home</a></li>');
		fwriteln($handle, '<li><a href="index.html">Video</a></li>');
		fwriteln($handle, '<li><a href="catalogue.html">PDF-'.$t["KATALOGE"].'</a></li>');
		fwriteln($handle, '<li><a href="adobe.html">Adobe</a></li>');
		fwriteln($handle, '<li style="float: left;width: 172px;text-align: center;height: 134px;background-image: url(../images/hauptnavi_mitte.gif);background-repeat: no-repeat;"></li>');
		fwriteln($handle, '<li style="float: left;width: 172px;text-align: center;height: 75px;background-image: url(../images/hauptnavi_unten.gif);background-repeat: no-repeat;"></li>');
		fwriteln($handle, '</ul>');
		fwriteln($handle, '</div>');
		fwriteln($handle, '<div id="content">');
		fwriteln($handle, '<div id="content_header">');
		fwriteln($handle, '<p>Video</p>');
		fwriteln($handle, '</div>');
		fwriteln($handle, '<div id="content_container" style="min-height:215px"> ');
		fwriteln($handle, '<p>'.$t["INSTALLATION_BESCHREIBUNG"].'</p>');
		fwriteln($handle, '<p><a href="../tecdoc/setup.exe" id="install">'.$t["INSTALLATION"].'</a></p>');
		fwriteln($handle, '</div>');
		fwriteln($handle, '</div>');
		fwriteln($handle, '</body>');
		fwriteln($handle, '</html>	');
		fclose($handle);
		echo 'OK.<br />';
*/
		
		//catalogue.html erstellen		
		echo 'Generiere CD/'.$sprachen[$i].'/catalogue.html ... ';
		$handle=fopen('CD/'.$sprachen[$i].'/catalogue.html', "w");
		fwriteln($handle, '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">');
		fwriteln($handle, '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="de" lang="de">');
		fwriteln($handle, '	<head>');
		fwriteln($handle, '		<link rel="stylesheet" type="text/css" href="../css/styles.css" />');
		fwriteln($handle, '		<meta http-equiv="content-type" content="text/html; charset=utf-8" />');
		fwriteln($handle, '		<title>MAPCO: Autoersatzteile GmbH</title>');
		fwriteln($handle, '	</head>');
		fwriteln($handle, '	<body>');
		fwriteln($handle, '		<p><div id="banner"></div></p>');
		fwriteln($handle, '		<div id="hauptmenue">');
		fwriteln($handle, '			<ul>');
		fwriteln($handle, '				<li style="float: left;width: 172px;text-align: center;height: 82px;background-image: url(../images/hauptnavi_kopf.gif);background-repeat: no-repeat;"></li>');
		fwriteln($handle, '				<li><a href="../index.html">Home</a></li>');
		fwriteln($handle, '				<li><a href="index.html">Video</a></li>');
		fwriteln($handle, '				<li><a href="catalogue.html">PDF-'.$t["KATALOGE"].'</a></li>');
		fwriteln($handle, '				<li><a href="adobe.html">Adobe</a></li>');
		fwriteln($handle, '				<li style="float: left;width: 172px;text-align: center;height: 134px;background-image: url(../images/hauptnavi_mitte.gif);background-repeat: no-repeat;"></li>');
		fwriteln($handle, '				<li style="float: left;width: 172px;text-align: center;height: 75px;background-image: url(../images/hauptnavi_unten.gif);background-repeat: no-repeat;"></li>');
		fwriteln($handle, '			</ul>');
		fwriteln($handle, '		</div>');
		fwriteln($handle, '		<div id="content">');
		fwriteln($handle, '			<div id="content_header">');
		fwriteln($handle, '				<p style="height:auto;">'.$t["KATALOGE"].'</p>');
		fwriteln($handle, '			</div>');
		fwriteln($handle, '			<div id="content_container">');
		fwriteln($handle, '				<h3>PDF-'.$t["KATALOGE"].'</h3>');
		fwriteln($handle, '				<p>'.$t["KATALOG_BESCHREIBUNG"].'</p>');
		$results=q("SELECT * FROM web_catalogs WHERE active ORDER BY title;", $dbweb, __FILE__, __LINE__);
		while($row=mysqli_fetch_array($results))
		{
			if( $sprachen[$i]=="fr" and $row["number_fr"]!="" ) $number=$row["number_fr"];
			else $number=$row["number"];
			if( $row["id_catalog"]!=32 or ($row["id_catalog"]==32 and $sprachen[$i]=="de") )
			{
				$title=t($row["title"], __FILE__, __LINE__, $sprachen[$i]);
				fwriteln($handle, '<div class="catalog">');
				fwriteln($handle, '	<a href="../catalogues/'.strtolower($number).'.pdf" title="'.$title.'">');
				fwriteln($handle, '		<img src="../catalogues/'.strtolower($row["number"]).'.jpg" alt="'.$title.'" title="'.$title.'" /><br />');
				fwriteln($handle, $title);
				fwriteln($handle, '	</a>');
				fwriteln($handle, '</div>');
			}
		}
		fwriteln($handle, '</div>');
		fwriteln($handle, '</body>');
		fwriteln($handle, '</html>');
		fclose($handle);
		echo 'OK.<br />';

		//adobe.html erstellen
		echo 'Generiere CD/'.$sprachen[$i].'/adobe.html ... ';
		$handle=fopen('CD/'.$sprachen[$i].'/adobe.html', "w");
		fwriteln($handle, '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">');
		fwriteln($handle, '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="de" lang="de">');
		fwriteln($handle, '<head>');
		fwriteln($handle, '<link rel="stylesheet" type="text/css" href="../css/styles.css" />');
		fwriteln($handle, '<meta http-equiv="content-type" content="text/html; charset=utf-8" />');
		fwriteln($handle, '<title>MAPCO: Autoersatzteile GmbH</title>	');
		fwriteln($handle, '</head>');
		fwriteln($handle, '<body>');
		fwriteln($handle, '<p><div id="banner"></div></p>');
		fwriteln($handle, '<div id="hauptmenue"> ');
		fwriteln($handle, '<ul>');
		fwriteln($handle, '<li style="float: left;width: 172px;text-align: center;height: 82px;background-image: url(../images/hauptnavi_kopf.gif);background-repeat: no-repeat;"></li>');
		fwriteln($handle, '<li><a href="../index.html">Home</a></li>');
		fwriteln($handle, '				<li><a href="index.html">Video</a></li>');
		fwriteln($handle, '				<li><a href="catalogue.html">PDF-'.$t["KATALOGE"].'</a></li>');
		fwriteln($handle, '<li><a href="adobe.html">Adobe</a></li>');
		fwriteln($handle, '<li style="float: left;width: 172px;text-align: center;height: 134px;background-image: url(../images/hauptnavi_mitte.gif);background-repeat: no-repeat;"></li>');
		fwriteln($handle, '<li style="float: left;width: 172px;text-align: center;height: 75px;background-image: url(../images/hauptnavi_unten.gif);background-repeat: no-repeat;"></li>');
		fwriteln($handle, '</ul>');
		fwriteln($handle, '</div>');
		fwriteln($handle, '<div id="content"> ');
		fwriteln($handle, '<div id="content_header">');
		fwriteln($handle, '<p>Adobe</p>');
		fwriteln($handle, '</div>');
		fwriteln($handle, '<div id="content_container" style="min-height:215px"> ');
		fwriteln($handle, '<p>'.$t["ADOBE_BESCHREIBUNG"].'</p>');
		fwriteln($handle, '<p><a target="_blank" href="http://get.adobe.com/reader/"><img src="../images/get_adobe_reader.png" title="Get Adobe Reader" alt="Get Adobe Reader" /></a></p>');
		fwriteln($handle, '</div>');
		fwriteln($handle, '</div>');
		fwriteln($handle, '</body>');
		fwriteln($handle, '</html>	');
		fclose($handle);
		echo 'OK.<br />';	
	}


	//Abschluss
	echo '<p>Katalogdateien erfolgreich generiert.</p>';
	echo '<p><a target="_blank" href="CD/index.html">Katalog-CD ansehen</a></p>';
	echo '<form method="post"><input type="submit" name="form_button" value="Katalog herunterladen" /></form>';

	include("templates/".TEMPLATE_BACKEND."/footer.php");
?>
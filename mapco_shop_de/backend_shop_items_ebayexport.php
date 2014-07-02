<?php
	include("config.php");
	include("templates/".TEMPLATE_BACKEND."/header.php");

	function cutout($text, $from, $to)
	{
		while($start=strpos($text, $from))
		{
			$end=strpos($text, $to, $start)+strlen($to);
			$text2=substr($text, 0, $start);
			$text2.=substr($text, $end, strlen($text));
			$text=$text2;
		}
		return($text);
	}

	//PATH
	echo '<p>';
	echo '<a href="backend_index.php">Backend</a>';
	echo ' > <a href="backend_shop_index.php">Online-Shop</a>';
	echo ' > <a href="backend_shop_items.php">Artikel</a>';
	echo ' > eBay-Exportdaten';
	echo '</p>';


	echo '<h1>eBay-Exportdaten</h1>';
	
	//Bild URLs
	$i=0;
	$results=q("SELECT * FROM shop_items_files WHERE item_id=".$_GET["id_item"].";", $dbshop, __FILE__, __LINE__);
	while($row=mysqli_fetch_array($results))
	{
		$i++;
		echo '<b>Bild #'.$i.'</b><br />';
		$link='http://www.mapco.de/files/'.floor(bcdiv($row["file_id"], 1000)).'/'.$row["file_id"].'.jpg';
		echo '<input style="width:600px;" type="text" value="'.$link.'" />';
		echo ' <a target="_blank" href="'.$link.'">Vorschau</a>';
		echo '<br /><br />';
	}

	//Artikelbeschreibung
	if($_GET["id_item"]>0)
	{
		$description='
<style type="text/css">	
.hover
{
	width:600px;
	margin:5px 0px 5px 45px;
	border:0;
	padding:0;
	background:#ffffff;
	font-family:Arial, Helvetica, sans-serif;
	font-family:Arial, Helvetica, sans-serif;
}
.hover tr:hover
{
	background:#eeeeee;
	font-family:Arial, Helvetica, sans-serif;
}
.hover tr td
{
	border:1px solid #cccccc;
	color:#393939;
	font-family:Arial, Helvetica, sans-serif;
}
.hover tr th
{
	border:1px solid #cccccc;
/*	color:#222222;;
	background:#eeeeee; */
	color:#ffffff;
	background:#e25400 url(http://www.mapco.de/tempßlates/shop/images/table_bg.jpg);
	font-family:Arial, Helvetica, sans-serif;
}

.box
{
	width:100%;
	margin:10px 0px 10px 0px;
	border:1px solid #cccccc;
	padding:20px 0px 20px 0px;
	font-family:Arial, Helvetica, sans-serif;
}
.box h1
{
	width:600px;
	margin:5px 0px 5px 45px;
	border:0;
	padding:0;
	color:#333333;
	font-size:15px;
	font-weight:bold;
	font-family:Arial, Helvetica, sans-serif;
}
.box p, ul
{
	width:600px;
	margin:5px 0px 5px 45px;
	font-family:Arial, Helvetica, sans-serif;
}

.box ul
{
	list-style:circle;
}
		</style>
		';
		$results=q("SELECT * FROM shop_items_de WHERE id_item=".$_GET["id_item"].";", $dbshop, __FILE__, __LINE__);
		$row=mysqli_fetch_array($results);
		echo '<b>Artikelbeschreibung</b><br />';
		echo '<textarea style="width:600px; height:300px;">';
		
		
		if (strpos($row["title"], "HPS")>0) echo '<img src="http://www.mapco.de/images/ebay_hps.jpg" />';
		else echo '<img src="http://mapco.de/images/newsletter_header.jpg" />';

		echo '<div class="box">';
		echo '	<h1 style="font-size:20px;">'.$row["title"].'</h1>';
		echo '	<p>Bitte prüfen Sie anhand untenstehender Tabelle, ob der Artikel für Ihr Fahrzeug geeignet ist. Wenn Sie sich nicht sicher sind, können Sie gerne per E-Mail unter <a href="mailto:ebay@mapco.de">ebay@mapco.de</a> nachfragen!</p>';
		echo '	<p style="color:#090; font-weight:bold;">Unser eBay-Shop befindet sich gerade im Aufbau. Wir haben über 13.000 verschiedene Wartungs- und Ersatzteile im Lager. Was Sie auch benötigen: fragen Sie uns! ebay@mapco.de</p>';
		echo '</div>';
		
		
		
		
		$description.=$row["description"];
		$description=str_replace('<h1>', '<div class="box"><h1>', $description);
		$description=str_replace('</table>', '</table></div>', $description);
		$description=cutout($description, '<a href="', '">');
		$description=str_replace('</a>', '', $description);
		
		echo $description;
		echo '<div style="font-family:Arial, Helvetica, sans-serif;">';
		echo '<i>OEM-Nummern dienen ausschließlich Vergleichszwecken.</i>';
		echo '<br /><i>Alle Artikelfotos sind Originalbilder des angebotenen Artikels und Eigentum der MAPCO Autotechnik GmbH.</i>';
		
		echo '<br /><br /><span style="font-size:16px; font-weight:bold; color:#ff0022;">ACHTUNG - bitte teilen Sie uns zu jedem Kauf die Schlüsselnummern zu 2. und 3. sowie das Datum der Erstzulassung mit. Nur so können wir gewährleisten, dass Sie die passenden Teile erhalten.</span>';
		echo '</div>';
		
	
		echo '<div class="box">';
		echo '<h1>Selbstabholung und Versand</h1>';
		echo '<p>Nach vorheriger Absprache ist eine Abholung in einem unserer RegionalCENTER an folgenden Standorten möglich:</p>';
		echo '<ul>';
		echo '<li>Berlin</li>';
		echo '<li>Brück</li>';
		echo '<li>Dresden</li>';
		echo '<li>Frankfurt / Main</li>';
		echo '<li>Leipzig</li>';
		echo '<li>Magdeburg</li>';
		echo '<li>Neubrandenburg</li>';
		echo '<li>Potsdam</li>';
		echo '<li>Sömmerda</li>';
		echo '</ul>';
		echo '<p>Inselzuschlag von 10,15€  wird erhoben für PLZ: 18565, 25845-25849, 25859, 25863, 25869, 25929-25955, 25961-25999, 26465-26486, 26548, 26571-26579, 26757,  27498-27499, 83209, 83256</li>';
		echo '</div>';

		echo '<div class="box">';
		echo '<h1>Über MAPCO</h1>';
		echo '<p>MAPCO-Produkte werden in Deutschland seit 1977 angeboten und mit großem Erfolg verkauft. Millionenfach werden MAPCO-Produkte in unendlich viele Fahrzeugtypen eingebaut. Kunden-zufriedenheit besitzt stets höchste Priorität. Ursprünglich in Frankreich als Aktiengesellschaft gegründet, werden heute sämtliche MAPCO-Aktivitäten von Borkheide bei Berlin gesteuert.</p>';
		echo '<p>MAPCO hat sich seit mehr als 3 Jahrzehnten europaweit einen Namen als Bremsenspezialist gemacht. Obwohl das Lieferprogramm inzwischen gewaltig erweitert wurde, wird das Programm Bremsenteile innerhalb des Sortiments weiter gepflegt und entwickelt.</p>';
		echo '<p>MAPCO-Lenkungs- und Chassisteile werden seit 1985 auf dem deutschen Markt angeboten. Das Programm entwickelte sich allerdings erst ab etwa 1995 in die Dimension, die heute erreicht wurde. Die neuen Technologien bei der Vorder- und Hinterachskonstruktion, welche die Automobilhersteller in den 90er Jahren einführten, hat zu einem explosionsartig ansteigenden Marktpotential für diese Ersatzteile geführt. Weit mehr als 3500 Einzelpositionen werden in dieser Produktfamilie geführt. Der Produktkatalog mit Originalabbildungen ist übersichtlich und praxisnah gehalten. Qualität, Preis und Verfügbarkeit dieser Teile sind vorbildlich.</p>';
		echo '<p>MAPCO-Lenkgetriebe für hydraulische und mechanische Lenkungen runden das Programm ab. Auch hier hat die von der Automobilindustrie in den 90er Jahren verfolgte Politik der Erhöhung von Komfort und Sicherheit im Fahrzeug einen völlig neuen Ersatzteilmarkt entstehen lassen. Das MAPCO-Programm beinhaltet des Weiteren eine Vielzahl umsatzstarker Verschleißteile.</p>';
		echo '</div>';
		echo '<img src="http://www.mapco.de/templates/shop/images/sitemap_bg.jpg" />';
		
		echo '</textarea>';
	}

	include("templates/".TEMPLATE_BACKEND."/footer.php");
?>
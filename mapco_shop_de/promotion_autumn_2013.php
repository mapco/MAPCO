<?php
	include("config.php");
	include("templates/".TEMPLATE."/header.php");
	include("templates/".TEMPLATE."/cms_leftcolumn.php");
	include("functions/cms_t.php");

	echo '<div id="mid_right_column">';
	
	echo '  <img src="'.PATH.'images/herbstaktion2013/header_promotion.jpg" style="padding:20px 64px 0px;" alt="'.t("Sicher durch den Herbst mit der MAPCO Herbstaktion").'!" />';

	echo '	<div style="padding:15px 75px; width:606px;">';

	//Landing Page
	if ($_GET["content"]=="landing")
	{
		echo '	<h2 style="font-size:18px; text-align:center;">Sicher und entspannt durch den Herbst mit der MAPCO-Herbstaktion</h2>';
		echo '	<p style="font-size:16px; text-align:justify;">';
		echo '	Mit den Qualitätsprodukten von MAPCO kommen Sie sicher durch den Herbst. Setzen Sie auf geprüfte Sicherheit.<br />';
		echo '	Egal ob Bremse oder Scheibenwischer, mit unseren Produkten sind Sie auf der sicheren Seite. Bestellen Sie jetzt und profitieren Sie von der MAPCOHerbstaktion.';
		echo '	</p><p style="font-size:16px; text-align:justify;">';
		echo '	Hier kommen Sie zur Herbstaktion 2013 für';
		echo '	</p>';
		echo '<div style="padding:20px 0px; float:left;">';
		echo '	<a href="'.PATHLANG.'herbstaktion2013/endkunden/" title="MAPCO Herbstaktion für Endkunden!">';
		echo '  <img src="'.PATH.'images/herbstaktion2013/ButtonB2C.png" alt="Herbstaktion für Endkunden!" />';
		echo '	</a>';
		echo '</div>';
		echo '<div style="padding:20px 0px; float:right;">';
		echo '	<a href="'.PATHLANG.'herbstaktion2013/geschaeftskunden/" title="MAPCO Herbstaktion für Geschäftskunden!">';
		echo '  	<img src="'.PATH.'images/herbstaktion2013/ButtonB2B.png" alt="Herbstaktion für Geschäftskunden!" />';
		echo '	</a>';
		echo '</div>';
	}

	//Händler & Werkstätten
	if ($_GET["content"]=="b2b")
	{
		echo '	<h2 style="font-size:18px; text-align:center;">GRATIS Lampen-Sets: Ab einem Auftragswert von 150 Euro (netto)</h2>';
		echo '	<p style="font-size:16px; text-align:justify;">';
		echo '	Im Zeitraum vom 21.10.13 - 24.11.2013 können Sie ab einem Einkaufswert von 150 Euro zwischen zwei attraktiven GRATIS Lampen-Sets mit einem Wiederverkaufswert von 39 Euro* wählen. ';
		echo '	Dies gilt für alle Einkäufe ab 150 Euro im Aktionszeitraum und nur solange der Vorrat reicht. ';
		echo '	</p>';
		echo '	<p style="font-size:16px; text-align:justify;">';
		echo '	<b>Vorgehensweise:</b><br />';
		echo '	Sie kaufen Ihre Produkte in unserem Onlineshop. ';
		echo '	Wenn Sie Waren für 150 Euro (netto) im Warenkorb haben, können Sie gratis das Lampenset 1 oder das Lampenset 2 dazu legen. ';
		echo '	</p>';
		echo '<div style="padding:10px 0px 0px; float:left;">';
		echo '  <img src="'.PATH.'images/herbstaktion2013/Lampenset-1.png" style="float:left;" alt="MAPCO Herbstaktion Lampenset 1" />';
		echo '	<p style="font-size:14px; float:left;">';
		echo '	<b>Lampen-Set 1 [MAPCO Art.Nr. 103374]</b>';
		echo '	</p>';
		echo '	<p style="font-size:14px; float:left;">';
		echo '	bestehend aus jeweils zwei Lampen der Serien: <br />';
		echo '	12V H1 [MAPCO Art.Nr. 103202] x 2 <br />';
		echo '	12V H4 [MAPCO Art.Nr. 103200] x 2 <br />';
		echo '	12V H7 [MAPCO Art.Nr. 103230] x 2 <br />';
		echo '	12V H11 [MAPCO Art.Nr. 103211] x 2 <br />';
		echo '	</p>';
		echo '</div>';
		echo '<div style="padding:0px 10px; float:left;">';
		echo '  <img src="'.PATH.'images/herbstaktion2013/Lampenset-2.png" style="float:left;" alt="MAPCO Herbstaktion Lampenset 2" />';
		echo '	<p style="font-size:14px; float:left;">';
		echo '	<b>Lampen-Set 2 [MAPCO Art.Nr. 103375]</b>';
		echo '	</p>';
		echo '	<p style="font-size:14px; float:left;">';
		echo '	bestehend aus jeweils einem 10er Set Glühlampen: <br />';
		echo '	Schlussleuchte [MAPCO Art.Nr. 103234] x 10 <br />';
		echo '	Kennzeichenleuchte [MAPCO Art.Nr. 103235] x 10 <br />';
		echo '	Blinkleuchte [MAPCO Art.Nr. 103239] x 10 <br />';
		echo '	</p>';
		echo '</div>';
		echo '<div style="padding:0px 0px; float:left;">';
		echo '  <img src="'.PATH.'images/herbstaktion2013/Lampenset-3.png" style="float:left;" alt="MAPCO Herbstaktion Lampenset 3" />';
		echo '	<p style="font-size:14px; float:left;">';
		echo '	<b>Lampen-Set 3 [MAPCO Art.Nr. 103376]</b>';
		echo '	</p>';
		echo '	<p style="font-size:14px; float:left;">';
		echo '	bestehend aus jeweils einem 10er Set Glühlampen: <br />';
		echo '	Schlussleuchte [MAPCO Art.Nr. 103234] x 10 <br />';
		echo '	Brems-/Schlussleuchte [MAPCO Art.Nr. 103280] x 10 <br />';
		echo '	Kennzeichenleuchte [MAPCO Art.Nr. 103290] x 10 <br />';
		echo '	</p>';
		echo '</div>';
		echo '<div style="padding:20px 0px; float:left;">';
		echo '	<p style="font-size:10px;">';
		echo '	* unverbindliche Preisempfehlung';
		echo '	</p>';
		echo '</div>';
		echo '<div style="padding:10px 0px; float:right;">';
		echo '	<a href="'.PATHLANG.'online-shop/" title="zum Shop">';
		echo '  	<img src="'.PATH.'images/herbstaktion2013/ButtonShop.png" alt="zum Onlineshop" />';
		echo '	</a>';
		echo '</div>';
	}

	//Endkunden
	if ($_GET["content"]=="b2c")
	{
		echo '	<h2 style="font-size:18px; text-align:center;">10 % GRATIS GUTSCHEIN: Bei einem Einkauf in Höhe von 79 Euro</h2>';
		echo '	<p style="font-size:16px; text-align:justify;">';
		echo '	Im Zeitraum vom 21.10.13 - 24.11.2013 erhalten Sie ab einem Einkaufswert von 79 Euro (inkl. MwSt.) einen Gutschein in Höhe von 10% Ihres Einkaufswertes. ';
		echo '	Dies gilt für alle Einkäufe von Endkunden im Aktionszeitraum. ';
		echo '	Den Gutscheinbetrag wird Ihnen beim nächsten Einkauf in unserem Onlineshop gutgeschrieben.';
		echo '	</p>';
		echo '	<p style="font-size:16px; text-align:justify;">';
		echo '	<b>Vorgehensweise:</b><br />';
		echo '	Sie kaufen Ihre Produkte in unserem Onlineshop. ';
		echo '	Wenn Sie Waren für 79 Euro im Warenkorb haben, wird automatisch der entsprechende Wert als Gutscheinbetrag in Ihrem Onlinekonto für den nächsten Einkauf hinterlegt. ';
		echo '	Wird der Gutschein beim nächsten Einkauf nicht aufgebraucht, kann der verbleibende Betrag beim übernächsten Einkauf eingelöst werden, u.s.w.';
		echo '	</p>';
		echo '	<p style="font-size:16px; text-align:justify;">';
		echo '	<a href="'.PATH.'images/herbstaktion2013/AGB Gutschein.pdf" target="_blank" title="Gutschein AGBs">Hier geht es zu den Gutschein AGBs</a>';
		echo '	</p>';
		echo '<div style="padding:10px 0px; float:right;">';
		echo '	<a href="'.PATHLANG.'online-shop/" title="zum Shop">';
		echo '  	<img src="'.PATH.'images/herbstaktion2013/ButtonShop.png" alt="zum Onlineshop" />';
		echo '	</a>';
		echo '</div>';
	}
	
	echo '	</div>';	
	echo '</div>';
	
	include("templates/".TEMPLATE."/footer.php");
?>

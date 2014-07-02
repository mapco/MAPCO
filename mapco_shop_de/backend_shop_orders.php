<?php
	include("config.php");
	$leftmenu=7;
	$columns="MR";
	include("templates/".TEMPLATE_BACKEND."/header.php");
	include("functions/mapco_gewerblich.php");
?>


<script>
	$(document).ready(function()
	{
		$("#orders_from").datepicker( { "dateFormat":"D dd.mm.yy", firstDay:1, showOtherMonths: true, selectOtherMonths: true });
		$("#orders_to").datepicker( { "dateFormat":"D dd.mm.yy", firstDay:1, showOtherMonths: true, selectOtherMonths: true });
	});
</script>


<?php
	//REMOVE
	if ( isset($_POST["form_button"]) and $_POST["form_button"]=="Bestellung löschen")
    {
		if ($_POST["id_article"]<=0) echo '<div class="failure">Es konnte keine ID für den Bestellung gefunden werden!</div>';
		else
		{
			q("DELETE FROM cms_articles WHERE id_article=".$_POST["id_article"]." LIMIT 1;", $dbweb, __FILE__, __LINE__);
			echo '<div class="success">Bestellung erfolgreich gelöscht!</div>';
		}
	}

	//PATH
	echo '<p>';
	echo '<a href="backend_index.php">Backend</a>';
	echo ' > <a href="backend_shop_index.php">Online-Shop</a>';
	echo ' > Bestellungen';
	echo '</p>';


	//SEARCH
	echo '<form method="post">';
	if (!isset($_POST["partner"])) $_POST["partner"]=-1;
	if ( !isset($_POST["tffrom"]) or $_POST["tffrom"]=="") $_POST["tffrom"]=date("D d.m.Y", time());
	if ( !isset($_POST["tfto"]) or $_POST["tfto"]=="") $_POST["tfto"]=date("D d.m.Y", time());
	echo '<table>';
	echo '	<tr>';
	echo '		<th colspan="4">Suchfunktion</th>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>';
	echo '			<b>Zeitraum von</b><br /><input type="text" id="datepicker_from" name="tffrom" value="'.$_POST["tffrom"].'" />';
	echo '		</td>';
	echo '		<td>';
	echo '			<b>Zeitraum bis</b><br /><input type="text" id="datepicker_to" name="tfto" value="'.$_POST["tfto"].'" />';
	echo '		</td>';
	echo '		<td>';
	echo '		<b>Gefunden über</b><br />';
	echo '			<select name="partner">';
						if($_POST["partner"]==-1) echo '<option selected="selected" value="-1">Alle</option>';		
						else echo '<option value="-1">Alle</option>';
						if($_POST["partner"]==0) echo '<option selected="selected" value="0">MAPCO direkt</option>';
						else echo '<option value="0">MAPCO direkt</option>';
	$results2=q("SELECT distinct a.id_partner, a.title FROM shop_partners AS a, shop_orders AS b WHERE a.id_partner=b.partner_id ORDER BY a.title;", $dbshop, __FILE__, __LINE__);
	while($row2=mysqli_fetch_array($results2))
	{
						if($_POST["partner"]==$row2["id_partner"]) echo '<option selected="selected" value="'.$row2["id_partner"].'">'.$row2["title"].'</option>';
						else  echo '<option value="'.$row2["id_partner"].'">'.$row2["title"].'</option>';
	}
	echo '			</select>';
	echo '		</td>';
	echo '		<td>';
	if( !isset($_POST["search"]) ) $_POST["search"]="";
	echo '			<b>Suchbegriffe</b><br /><input type="text" name="search" value="'.$_POST["search"].'" />';
	echo '			<input type="submit" value="Suchen" />';
	echo '		</td>';
	echo '	</tr>';
	echo '</table>';
	echo '</form>';
	
	//LIST
	$nowtime=getdate(time()-24*3600);
	if ($_POST["tffrom"]!="") $starttime=mktime(0, 0, 0, substr($_POST["tffrom"], 7, 2), substr($_POST["tffrom"], 4, 2), substr($_POST["tffrom"], 10, 4));
	if ($_POST["tfto"]!="") $endtime=mktime(23, 59, 59, substr($_POST["tfto"], 7, 2), substr($_POST["tfto"], 4, 2), substr($_POST["tfto"], 10, 4));
//	echo '<p>Zeitraum: '.date("d.m.Y H:i", $starttime).' - '.date("d.m.Y H:i", $endtime).'</p>';


	//Download
	if (file_exists("shop_orders.csv")) unlink("shop_orders.csv");
	$handle=fopen("shop_orders.csv", "w");

	//Zeitraumanzeige
	$i=0;
	if ($_POST["partner"]==-1)
	{
		echo '<h1>'.t("Bestellungen");
	}
	else
	{
		$results3=q("SELECT title FROM shop_partners WHERE id_partner=".$_POST["partner"]." LIMIT 1;", $dbshop, __FILE__, __LINE__);
		$row3=mysqli_fetch_array($results3);
		echo '<h1>'.t("Bestellungen").' '.$row3["title"];
	}
	echo '<a href="'.PATH.'shop_orders.csv"><img alt="als CSV exportieren" src="images/icons/24x24/down.png" style="cursor:pointer;" title="als CSV exportieren" /></a>';
	echo '</h1>';
	
	echo '<table class="hover">';
	echo '<tr>';
	echo '	<th>Nr.</th>';
	echo '	<th>Firma</th>';
	echo '	<th>Ansprechpartner</th>';
	echo '	<th>Bestellnummer</th>';
	echo '	<th>Datum</th>';
	echo '	<th>Gesamtpreis</th>';
	echo '</tr>';
	fwrite($handle, "Firma;Ansprechpartner;Bestellnummer;Datum;Gesamtpreis;"."\n");

	if ( isset($_POST["search"]) and $_POST["search"]!="")
	{
		if ($_POST["partner"]==-1)
		{
			$results=q("SELECT * FROM shop_orders WHERE (bill_company LIKE '%".$_POST["search"]."%' OR bill_lastname LIKE '%".$_POST["search"]."%' OR usermail LIKE '%".$_POST["search"]."%' AND firstmod>=".$starttime.") AND firstmod<=".$endtime." AND firstmod>=".$starttime." AND firstmod<=".$endtime." ORDER BY firstmod;", $dbshop, __FILE__, __LINE__);
		}
		else
		{
			$results=q("SELECT * FROM shop_orders WHERE partner_id=".$_POST["partner"]." AND (bill_company LIKE '%".$_POST["search"]."%' OR bill_lastname LIKE '%".$_POST["search"]."%' OR usermail LIKE '%".$_POST["search"]."%' AND firstmod>=".$starttime.") AND firstmod<=".$endtime." AND firstmod>=".$starttime." AND firstmod<=".$endtime." ORDER BY firstmod;", $dbshop, __FILE__, __LINE__);
		}
	}
	else
	{
		if ($_POST["partner"]==-1)
		{
			$results=q("SELECT * FROM shop_orders WHERE firstmod>=".$starttime." AND firstmod<=".$endtime." ORDER BY firstmod;", $dbshop, __FILE__, __LINE__);
		}
		else
		{
			$results=q("SELECT * FROM shop_orders WHERE partner_id=".$_POST["partner"]." AND firstmod>=".$starttime." AND firstmod<=".$endtime." ORDER BY firstmod;", $dbshop, __FILE__, __LINE__);
			
		}
	}
	$total=0;
	$total_private=0;
	$total_business=0;
	while($row=mysqli_fetch_array($results))
	{
		$private='';
		$ordertotal=0;
		$results2=q("SELECT * FROM shop_orders_items WHERE order_id='".$row["id_order"]."';", $dbshop, __FILE__, __LINE__);
		while($row2=mysqli_fetch_array($results2))
		{
			$ordertotal+=$row2["netto"]*$row2["amount"];
		}
		$total+=$ordertotal;
		if (!gewerblich($row["customer_id"]))
		{
			$total_private+=$ordertotal;
			$private='*';
		}
		else $total_business+=$ordertotal;
		echo '<tr>';
		$i++;
		echo '	<td>'.$i.'</td>';
		echo '	<td>'.$row["bill_company"].'</td>';
		echo '	<td>'.$row["bill_gender"].' '.$row["bill_lastname"].' '.$private.'</td>';
		echo '	<td><a href="backend_shop_order.php?id_order='.$row["id_order"].'" target="_blank">'.$row["id_order"].'</a></td>';
		echo '	<td>'.date("d.m.Y H:i", $row["firstmod"]).'</td>';
		echo '	<td>€ '.number_format($ordertotal, 2).'</td>';
		echo '</tr>';
		fwrite($handle, '"'.utf8_decode($row["bill_company"]).'";"'.utf8_decode($row["bill_gender"]).' '.utf8_decode($row["bill_lastname"]).' '.$private.'";"'.$row["id_order"].'";"'.date("d.m.Y H:i", $row["firstmod"]).'";"'.number_format($ordertotal, 2, ',', '').'"'."\n");
	}
	echo '<tr><td colspan="5"><b>Gesamt Geschäftskunden</b></td><td><b>€ '.number_format($total_business, 2).'</b></td></tr>';
	fwrite($handle, '"'.utf8_decode("Gesamt Geschäftskunden").'";"";"";"";"'.number_format($total_business, 2, ',', '').'"'."\n");
	echo '<tr><td colspan="5"><b>Gesamt Privatkunden</b></td><td><b>€ '.number_format($total_private, 2).'</b></td></tr>';
	fwrite($handle, '"'.utf8_decode("Gesamt Privatkunden").'";"";"";"";"'.number_format($total_private, 2, ',', '').'"'."\n");
	echo '<tr><td colspan="5"><b>Gesamt Netto</b></td><td><b>€ '.number_format($total, 2).'</b></td></tr>';
	fwrite($handle, '"'.utf8_decode("Gesamt Netto").'";"";"";"";"'.number_format($total, 2, ',', '').'"'."\n");
//	$total*=((100+UST)/100);
//	echo '<tr><td colspan="5"><b>Gesamt Brutto</b></td><td><b>€ '.number_format($total, 2).'</b></td></tr>';
/*	
	if (date("m", time()) == date("m", $starttime))
	{
		echo '<tr>';
		echo '	<td colspan="5">Voraussichtlicher Monatsumsatz</td>';
		echo '	<td>€ '.number_format(($total/date("d", time())*30), 2).'</td>';
		echo '</tr>';
	}
*/	
	echo '</table>';
	fclose($handle);
	
	
	/*
	echo '<h1>';
	echo '&nbsp;<span style="display:inline; float:left;">Bestellungen</span>';
	echo '<a href="backend_article_editor.php" title="Neuen Artikel anlegen"><img src="images/icons/24x24/page_add.png" alt="Neuen Artikel anlegen" title="Neuen Artikel anlegen" /></a>';
	echo '</h1>';
	echo '<p>In der nachfolgenden Liste, finden Sie alle derzeit im System registrierten Bestellungen.</p>';
	$results=q("SELECT * FROM shop_orders ORDER BY time DESC;", $dbshop, __FILE__, __LINE__);
	echo '<table class="hover">';
	echo '	<tr>';
	echo '		<th>Bestellnummer</th>';
	echo '		<th>Kunde</th>';
	echo '		<th>Bestelldatum</th>';
	echo '		<th>Optionen</th>';
	echo '	</tr>';
	while ($row=mysqli_fetch_array($results))
	{
		echo '<tr>';
		echo '	<td>'.$row["id_order"].'</td>';
		echo '	<td>'.$row["bill_firstname"].' '.$row["bill_lastname"].'</a></td>';
		echo '	<td>'.date("d-m-Y H:i", $row["firstmod"]).'</td>';
		echo '	<td>';
		echo '		<form action="backend_articles.php" style="margin:0; border:0; padding:0; float:right;" method="post">';
		echo '			<input type="hidden" name="id_article" value="'.$row["id_article"].'" />';
		echo '			<input type="hidden" name="form_button" value="Artikel löschen" />';
		echo '			<a href="backend_article_editor.php?id_article='.$row["id_article"].'" title="Artikel bearbeiten"><img src="images/icons/24x24/page.png" alt="Bestellung bearbeiten" title="Artikel bearbeiten" /></a>';
		echo '		</form>';
		echo '	</td>';
		echo '</tr>';
	}
	echo '</table>';*/
	
	include("templates/".TEMPLATE_BACKEND."/footer.php");
?>
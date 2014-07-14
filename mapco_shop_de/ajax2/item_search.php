<?php
	session_start();
	include("../config.php");
	include("../functions/shop_get_prices.php");
	include("../functions/cms_t.php");
	include("../functions/mapco_hide_price.php");	
	include("../functions/cms_url_encode.php");
	
	if ($_GET["wert"]=="") exit;
	$results=q("SELECT * FROM shop_items_".$_GET["lang"]." WHERE title LIKE '%".mysqli_real_escape_string($dbshop, $_GET["wert"])."%' LIMIT 20;", $dbshop, __FILE__, __LINE__);
	echo '<table class="hover" style="margin:0;">';
	echo '<tr><th colspan="4">';
//	echo mysqli_num_rows($results).' '.t("Suchergebnisse gefunden").'.';
	echo '	<img src="'.PATH.'images/icons/16x16/remove.png" style="cursor:pointer; float:right;" onclick="suche3();" alt="Schließen" title="Schließen" />';
	echo '</th></tr>';
	
	//Kopfzeile
	echo '<tr>';
	echo '	<th>'.t("Artikelbezeichnung").'</th>';
	echo '	<th>'.t("Verfügbarkeit").'</th>';
	echo '	<th>'.t("Preis").'</th>';
	echo '	<th style="width:150px;">'.t("Bestellen").'</th>';
	echo '</tr>';

	$i=0;
	while($row=mysqli_fetch_array($results))
	{
		$results3=q("SELECT * FROM shop_items WHERE id_item='".$row["id_item"]."' AND active=1 LIMIT 1;", $dbshop, __FILE__, __LINE__);
		if (mysqli_num_rows($results3)>0)
		{
			echo '<tr>';
			echo '	<td style="width:100px;"><a href="'.PATHLANG.'online-shop/autoteile/'.$row["id_item"].'/'.url_encode($row["title"]).'">'.$row["title"].'</a></td>';
			if($_SESSION["rcid"]!=9999 and $_SESSION["rcid"]>0)
			{
				$results2=q("SELECT * FROM lagerrc AS a, shop_items AS b WHERE b.id_item='".$row["id_item"]."' AND b.MPN=a.ARTNR AND a.RCNR='".$_SESSION["rcid"]."' LIMIT 1;", $dbshop, __FILE__, __LINE__);
				$row2=mysqli_fetch_array($results2);
				echo '<td>';
				if ($row2["ISTBESTAND"]>0) echo '<a href="'.PATHLANG.'online-shop/status/'.$row["id_item"].'/'.url_encode($row["title"]).'" style="color:#008000;">'.t("in").' '.$_SESSION["rcbez"].' '.t("vorrätig").'</a>';
				else 
				{
					$results2=q("SELECT * FROM lager AS a, shop_items AS b WHERE b.id_item='".$row["id_item"]."' AND b.MPN=a.ArtNr LIMIT 1;", $dbshop, __FILE__, __LINE__);
					$row2=mysqli_fetch_array($results2);
					if ($row2["ISTBESTAND"]>0) echo '<a href="'.PATHLANG.'online-shop/status/'.$row["id_item"].'/'.url_encode($row["title"]).'" style="color:#DE9800;">'.t("in der Zentrale vorrätig").'</a>';
					else echo '<a style="color:#800000;" href="'.PATHLANG.'online-shop/status/'.$row["id_item"].'/'.url_encode($row["title"]).'" >'.t("z.Z nicht lieferbar").'</a>';
				}
			}
			else
			{
				$results2=q("SELECT * FROM lager AS a, shop_items AS b WHERE b.id_item='".$row["id_item"]."' AND b.MPN=a.ArtNr LIMIT 1;", $dbshop, __FILE__, __LINE__);
				$row2=mysqli_fetch_array($results2);
				echo '<td>';
				if ($row2["ISTBESTAND"]>10) echo '<a href="'.PATHLANG.'online-shop/status/'.$row["id_item"].'/'.url_encode($row["title"]).'" style="color:#008000;">'.t("sofort lieferbar").'</a>';
				elseif ($row2["ISTBESTAND"]>0) echo '<a style="color:#000080;" href="'.PATHLANG.'online-shop/status/'.$row["id_item"].'/'.url_encode($row["title"]).'" >'.t("Nur noch wenige lieferbar").'</a>';
				else echo '<a style="color:#800000;" href="'.PATHLANG.'online-shop/status/'.$row["id_item"].'/'.url_encode($row["title"]).'" >'.t("z.Z nicht lieferbar").'</a>';
			}
	
			echo '	</td>';
			echo '	<td>';
	
			$hide_price=hide_price($_SESSION["id_user"]);
			$price = get_prices($row["id_item"]);
			if ($price["total"]<9000)
			{
				if ($hide_price)
				{
					echo '<span id="hide_price"';
					echo 'onmouseover="this.innerHTML = \'€ '.number_format($price["total"], 2).'\'"';
					if ($price["brutto"]>0)
					{
						echo 'onmouseout="this.innerHTML = \'€ '.number_format($price["brutto"], 2).'\'">';
						echo '€ '.number_format($price["brutto"], 2);
					}
					else 
					{
						echo 'onmouseout="this.innerHTML = \''.t("Preis auf Anfrage").'\'">';
						echo t("Preis auf Anfrage");
					}
//					echo 'onmouseout="this.innerHTML = \'€ '.number_format($price["brutto"], 2).'\'">';
//					echo '€ '.number_format($price["brutto"], 2);
					echo '</span>';
				}
				else echo '€ '.number_format($price["total"], 2);
				echo '<span style="font-size:10px;">';
				if ( isset($price["offline_net"]) ) echo '<br /><span style="color:#ff0000;">ONLINE-PREIS</span>';
				if ($price["collateral_total"]>0) echo '<br />zzgl. € '.number_format($price["collateral_total"], 2).' '.t("Altteilpfand");
				if ($price["total"]==$price["gross"]) echo '	<br />'.t("inkl. Mehrwertsteuer").' ('.$price["VAT"].'%)';
				echo '</span>';
				echo '  </td>';
				echo '	<td style="width:100px;">';
				echo '		<input id="article'.$row["id_item"].'" type="text" style="width:30px;" value="1" onkeyup="cart_add_enter('.$row["id_item"].')" />';
				echo '		<a href="javascript:cart_add('.$row["id_item"].');">'.t("In den Warenkorb").'</a>';
				echo '	</td>';
			}
			else
			{
				echo 'Preis auf Anfrage';
				echo '  </td>';
				echo '	<td style="width:100px;">';
//				echo '		<form onsubmit="return cart_add('.$row["id_item"].');"><input id="article'.$row["id_item"].'" type="text" style="width:30px;" value="1" /></form>';
//				echo '		<a href="" onclick="return cart_add('.$row["id_item"].');">'.t("In den Warenkorb").'</a>';
				echo '	</td>';
			}
			echo '</tr>';
			$i++;
			if ($i==15) break;
		}
	}
	echo '</table>';
?>
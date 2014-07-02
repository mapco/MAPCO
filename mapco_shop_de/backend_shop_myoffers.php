<?php
	include("config.php");
	include("templates/".TEMPLATE_BACKEND."/header.php");

	//PATH
	echo '<p>';
	echo '<a href="backend_index.php">Backend</a>';
	echo ' > <a href="backend_shop_index.php">Online-Shop</a>';
	echo ' > Meine Angebote';
	echo '</p>';

	//Hinzufügen
	if (isset($_POST["offer_add"]))
    {
		$_POST["percent"]=str_replace(",", ".", $_POST["percent"]);
		if ( !($_POST["from_day"]>=1 and $_POST["from_day"]<=31) ) $_POST["until_day"]=0;
		if ( !($_POST["from_month"]>=1 and $_POST["from_month"]<=12) ) $_POST["until_month"]=0;
		if ( !($_POST["from_year"]>0) ) $_POST["from_year"]=0;
		$from=mktime(23, 59, 59, $_POST["from_month"], $_POST["from_day"], $_POST["from_year"]);
		if ( !($_POST["until_day"]>=1 and $_POST["until_day"]<=31) ) $_POST["until_day"]=0;
		if ( !($_POST["until_month"]>=1 and $_POST["until_month"]<=12) ) $_POST["until_month"]=0;
		if ( !($_POST["until_year"]>0) ) $_POST["until_year"]=0;
		$until=mktime(23, 59, 59, $_POST["until_month"], $_POST["until_day"], $_POST["until_year"]);
		if ($_POST["artnr"]<=0) echo '<div class="failure">Das Feld Artikelnummer darf nicht leer sein!</div>';
		elseif ($_POST["percent"]<=0) echo '<div class="failure">Es muss ein gültiger Prozentsatz angegeben werden!</div>';
		elseif ($_POST["percent"]>50) echo '<div class="failure">Der Rabatt darf 50% nicht übersteigen!</div>';
		elseif ($_POST["from_day"]<=0 or $_POST["from_month"]<=0 or $_POST["from_year"]<=0) echo '<div class="failure">Es muss ein gültiges Datum für den Angebotsbeginn eingegeben werden!</div>';
		elseif ($_POST["until_day"]<=0 or $_POST["until_month"]<=0 or $_POST["until_year"]<=0) echo '<div class="failure">Es muss ein gültiges Datum für das Angebotsende eingegeben werden!</div>';
		elseif ($until<time()) echo '<div class="failure">Das Angebotsende muss in der Zukunft liegen!</div>';
		else
		{
			$results=q("SELECT * FROM shop_items WHERE MPN='".$_POST["artnr"]."';", $dbshop, __FILE__, __LINE__);
			if (mysqli_num_rows($results)==0) echo '<div class="failure">Artikel '.$_POST["artnr"].' konnte nicht gefunden werden!</div>';
			else
			{
				$row=mysqli_fetch_array($results);
				$results=q("SELECT * FROM shop_offers WHERE item_id='".$row["id_item"]."' LIMIT 1;", $dbshop, __FILE__, __LINE__);
				if (mysqli_num_rows($results)>0) echo '<div class="failure">Artikel ist bereits im Angebot!</div>';
				else
				{
					q("INSERT INTO shop_offers (item_id, percent, `from`, until, firstmod, firstmod_user, lastmod, lastmod_user) VALUES(".$row["id_item"].", '".$_POST["percent"]."', ".$from.", ".$until.", ".time().", ".$_SESSION["id_user"].", ".time().", ".$_SESSION["id_user"].");", $dbshop, __FILE__, __LINE__);
					echo '<div class="success">Artikel '.$_POST["artnr"].' ist nun im Angebot!</div>';
					$_POST["artnr"]="";
				}
			}
		}
	}

	//Entfernen
	if (isset($_POST["removeoffer"]))
    {
		if ($_POST["id_item"]<=0) echo '<div class="failure">Es konnte keine ID für den Artikel gefunden werden!</div>';
		else
		{
			q("DELETE FROM shop_offers WHERE item_id='".$_POST["id_item"]."' LIMIT 1;", $dbshop, __FILE__, __LINE__);
			echo '<div class="success">Artikel ist nun nicht mehr im Angebot!</div>';
		}
	}

	echo '<h1>';
	echo '&nbsp;<span style="display:inline; float:left;">Angebote</span>';
	echo '</h1>';
	echo '<p>In der nachfolgenden Liste, finden Sie alle derzeit im System als Angebote markierte Artikel.</p>';
	
	echo '<form method="post">';
	echo '<table>';
	
	echo '	<tr>';
	//Artikelnummer
	echo '		<td>Artikelnummer</td>';
	echo '		<td><input style="width:70px;"type="text" name="artnr" value="'.$_POST["artnr"].'" /></td>';
	//Rabatt
	echo '		<td>Rabatt</td>';
	echo '		<td><input style="width:40px;" type="text" name="percent" value="'.$_POST["percent"].'" />%</td>';
	echo '	</tr>';
	
	echo '	<tr>';
	//gültig von
	if ($_POST["from_day"]=="")
	{
		$_POST["from_day"]=date("d", time());
		$_POST["from_month"]=date("m", time());
		$_POST["from_year"]=date("Y", time());
	}
	echo '		<td>gültig von</td>';
	echo '		<td>';
	echo '			<input style="width:20px;" type="text" name="from_day" value="'.$_POST["from_day"].'" />';
	echo '			<input style="width:20px;" type="text" name="from_month" value="'.$_POST["from_month"].'" />';
	echo '			<input style="width:40px;" type="text" name="from_year" value="'.$_POST["from_year"].'" />';
	echo '		</td>';
	//gültig bis
	if ($_POST["until_day"]=="")
	{
		$time=time()+14*24*3600;
		$_POST["until_day"]=date("d", $time);
		$_POST["until_month"]=date("m", $time);
		$_POST["until_year"]=date("Y", $time);
	}
	echo '		<td>gültig bis</td><td>';
	echo '			<input style="width:20px;" type="text" name="until_day" value="'.$_POST["until_day"].'" />';
	echo '			<input style="width:20px;" type="text" name="until_month" value="'.$_POST["until_month"].'" />';
	echo '			<input style="width:40px;" type="text" name="until_year" value="'.$_POST["until_year"].'" />';
	echo '		</td>';
	echo '	</tr>';
	
	echo '	<tr><td colspan="2"><input class="formbutton" type="submit" name="offer_add" value="Angebot einstellen" /></td></tr>';
	echo '</table>';
	echo '</form>';
	
	$results=q("SELECT * FROM shop_offers WHERE firstmod_user=".$_SESSION["id_user"].";", $dbshop, __FILE__, __LINE__);
	echo '<table class="hover">';
	echo '	<tr>';
	echo '		<th>Bezeichnung</th>';
	echo '		<th>Rabatt</th>';
	echo '		<th>gültig von</th>';
	echo '		<th>gültig bis</th>';
	echo '		<th>Optionen</th>';
	echo '	</tr>';
	while ($row=mysqli_fetch_array($results))
	{
		$results2=q("SELECT * FROM shop_items_".$_GET["lang"]." WHERE id_item=".$row["item_id"].";", $dbshop, __FILE__, __LINE__);
		$row2=mysqli_fetch_array($results2);
		echo '<tr>';
		echo '	<td><a href="shop_item.php?id_item='.$row["item_id"].'">'.$row2["title"].'</a></td>';
		echo '	<td>'.$row["percent"].'%</td>';
		echo '	<td>'.date("d.m.Y", $row["from"]).'</td>';
		echo '	<td>'.date("d.m.Y", $row["until"]).'</td>';
		echo '	<td>';
		echo '		<form action="backend_shop_offers.php" style="margin:0; border:0; padding:0; float:right;" method="post">';
		echo '			<input type="hidden" name="id_item" value="'.$row["item_id"].'" />';
		echo '			<input type="hidden" name="offer_remove" value="Aus dem Angebot nehmen" />';
		echo '			<input style="margin:2px 8px 2px 0px; border:0; padding:0; float:right;" type="image" src="images/icons/24x24/remove.png" alt="Aus dem Angebot nehmen" title="Aus dem Angebot nehmen" onclick="return confirm(\'Wirklich aus dem Angebot nehmen?\');" />';
		echo '		</form>';
		echo '	</td>';
		echo '</tr>';
	}
	echo '</table>';
	
	include("templates/".TEMPLATE_BACKEND."/footer.php");
?>
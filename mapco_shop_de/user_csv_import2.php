<?php
	include("config.php");
	$login_required=true;
	include("templates/".TEMPLATE."/header.php");
	include("modules/cms_leftcolumn.php");
	include("functions/mapco_gewerblich.php");
	include("functions/shop_itemstatus.php");
	include("functions/shop_get_prices.php");
?>


<script type="text/javascript">

function isInteger (id) {
	var element = document.getElementById(id);
	if (!element.value.match (/^[+-]?[0-9]+$/)) 
	{
	  alert ('Die eingegebene Menge ist ungültig!');
	  element.focus ();
	  element.style.color = "white";
	  element.style.background = "red";
	  document.getElementById('checkbox'+id).disabled=true;
	  document.getElementById('checkbox'+id).checked=false;
	  return false;
	}
	element.style.color = "";
	element.style.background = "";
	document.getElementById('checkbox'+id).disabled=false;
	if (document.getElementById('cb_val'+id).value==1) 
	{
		document.getElementById('checkbox'+id).checked=true;		
	}
	return true;
}

</script>


<?php
	echo '<div id="mid_column">';
	
	//PATH
	echo '<p>';
	echo '<a href="'.PATHLANG.'online-shop/mein-konto/">Mein Konto</a>';
	echo ' > '.t("Auftragsimport");
	echo '</p>';

	echo '<h1>'.t("Auftragsimport").'</h1>';

//	IMPORT CANCEL
	if (isset($_POST["import_cancel"]))
    {
		unset($_POST);
	}

//	IMPORT TO CART
	if (isset($_POST["import_to_cart"]))
    {
//		print_r($_POST);

		$count=0;
		$count_ok=0;
		$count_unknown=0;
		for($i=0; $i<sizeof($_POST["id_item"]); $i++)
		{
			$id_item=$_POST["id_item"][$i];
			$amount=$_POST["amount"][$id_item];
			if ( $amount>0 and is_int(str_replace(",",".",$amount)*1))
			{
				$results2=q("SELECT * FROM shop_carts WHERE item_id=".$id_item." AND user_id='".$_SESSION["id_user"]."';", $dbshop, __FILE__, __LINE__);
	
				if (mysql_num_rows($results2)>0)
				{
					$row2=mysql_fetch_array($results2);
					$results=q("UPDATE shop_carts SET amount='".($row2["amount"]+$amount)."' WHERE id_carts='".$row2["id_carts"]."';", $dbshop, __FILE__, __LINE__);
				}
				else
				{
					$results=q("INSERT INTO shop_carts (item_id, amount, session_id, user_id) VALUES('".$id_item."', '".$amount."', '".session_id()."', '".$_SESSION["id_user"]."');", $dbshop, __FILE__, __LINE__);
				}
				$count_ok++;
			}
        }
		echo '<script language="javascript"> cart_update(); </script>';
		echo '<div class="success">'.$count_ok.' '.t("Artikel wurden zum Warenkorb hinzugefügt").'</div>';
	}

//	ORDER IMPORT
	if (isset($_POST["user_csv_import"]))
    {
		$failure=array();
		if ($_FILES["user_import_file"]["tmp_name"]=="") echo '<div class="failure">'.t("Es wurde keine Datei ausgewählt").'!</div>';
		else
		{
			$check_id=1;
			$import_txt='';

			echo '<form method="post" enctype="multipart/form-data">';
			echo '	<table class="hover">';
			echo '		<tr>';
			echo '			<th></th>';
			echo '			<th>Artikelnummer</th>';
			echo '			<th>Menge</th>';
			echo '			<th>Einzelpreis</th>';
			echo '			<th>Verfügbarkeit</th>';
			echo '		</tr>';

			$handle = fopen($_FILES['user_import_file']['tmp_name'], "r"); 
//			$data = fgetcsv($handle, 100, ";");
			while (($data = fgetcsv($handle, 100, ";")) !== FALSE) 
			{

				$results=q("SELECT * FROM shop_items WHERE MPN='".mysql_real_escape_string($data[0], $dbshop)."' LIMIT 1;", $dbshop, __FILE__, __LINE__);
				if (mysql_num_rows($results)>0)
				{
					$row=mysql_fetch_array($results);
					$price = get_prices($row["id_item"]);
					$checked = '';
					$style='';
					$results2=q("SELECT * FROM lager WHERE ArtNr='".$data[0]."' LIMIT 1;", $dbshop, __FILE__, __LINE__);
					$row2=mysql_fetch_array($results2);

					echo '<tr>';
					echo '	<td>';

					if ($row2["Bestand"]==1)
					{
						$checked='checked="checked"';
						echo '<input type="hidden" id="cb_val'.$check_id.'" value="1" />';
					}
					else echo '<input type="hidden" id="cb_val'.$check_id.'" value="0" />';

					if ($data[1]<=0 or !is_int(str_replace(",",".",$data[1])*1))
					{
						$style='color:white; background:red;';
						$checked='disabled="disabled"';
					}

					echo '		<input type="checkbox" '.$checked.' id="checkbox'.$check_id.'" name="id_item[]" value="'.$row["id_item"].'" />';
					echo '	</td>';
					echo '	<td style="width:160px;">';
					echo '		<a target="_blank" href="'.PATH.'online-shop/autoteile/'.$row["id_item"].'/">'.$data[0].'</a>';;
					echo '	</td>';
					echo '	<td>';
					echo '		<input style="width:40px; '.$style.'" id="'.$check_id.'" name="amount['.$row["id_item"].']" type="text" value="'.$data[1].'" onblur="isInteger('.$check_id.');" onclick="this.select()" onfocus="this.select()" />';
					echo '	</td>';
					echo '	<td>';
					echo '		€ '.number_format($price["total"], 2);
					echo '		<span style="font-size:10px;">';
					if ($price["collateral_total"]>0) echo '<br />zzgl. € '.number_format($price["collateral_total"], 2).' '.t("Altteilpfand");
					if ($price["total"]==$price["gross"]) echo '	<br />'.t("inkl. Mehrwertsteuer").' ('.$price["VAT"].'%)';
					echo '		</span>';
					echo '	</td>';	
					echo '	<td>';
					echo 		itemstatus($row["id_item"]);
					echo '	</td>';
					$check_id++;
				}
				echo '</tr>';

			}
			fclose($handle);

			echo '	<tr>';
			echo '		<td colspan="5">';
			echo '			<input class="formbutton" type="submit" name="import_to_cart" value="'.t("Artikel in den Warenkorb übernehmen").'!">';
			echo '			<input class="formbutton" type="submit" name="import_cancel" value="'.t("Abbrechen").'!">';
			echo '		</td>';
			echo '	</tr>';
			echo '	</table>';
			echo '</form>';
        }
	}
	else
	{
		// 	ORDER IMPORT WINDOW
		echo '<form method="post" enctype="multipart/form-data">';
		echo '<table class="hover">';
		echo '	<tr>';
		echo '	<th>'.t("Bitte CSV Datei auswählen").'!</th>';
		echo '	</tr>';
		echo '	<tr>';
		echo '		<td>';
		echo '			<br />';
		echo '			<input type="file" style="width:500px" name="user_import_file" />';
		echo '			<br />';
		echo '			<br />( '.t("Spalte").' 1 = '.t("Artikelnummer").' / '.t("Spalte").' 2 = '.t("Menge").' )';
		echo '			<br />';
		echo '		</td>';
		echo '	</tr>';
		echo '	<tr>';
		echo '		<td>';
		echo '			<input class="formbutton" type="submit" name="user_csv_import" value="'.t("Datei einlesen").'">';
		echo '		</td>';
		echo '	</tr>';
		echo '</table>';
		echo '</form>';
	}

	

	echo '</div>';

	include("modules/cms_rightcolumn.php");
	include("templates/".TEMPLATE."/footer.php");
	
	
?>
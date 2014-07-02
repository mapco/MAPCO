<?php
	include("config.php");
	include("templates/".TEMPLATE_BACKEND."/header.php");
?>

<script language="javascript">



</script>

<?php
//	UPDATE ITEM PRICE
	if (isset($_POST["ebay_price_update"]))
    {
		$failure=array();
		if($_POST["accounts"]=="0") echo '<div class="failure" style="float:none;">Es wurde kein Account ausgewählt!</div>';
		elseif ($_FILES["price_update_file"]["tmp_name"]=="") echo '<div class="failure">Es wurde keine Datei ausgewählt!</div>';
		else
		{
			$count_ok=0;
			$count_ebay=0;
			$count_auctions=0;
			$results=q("SELECT * FROM ebay_accounts WHERE id_account=".$_POST["accounts"]." LIMIT 1 ;", $dbshop, __FILE__, __LINE__);
			$row=mysqli_fetch_array($results);
			move_uploaded_file($_FILES['price_update_file']['tmp_name'], "ebay_price_tmp.csv"); 
			$handle = fopen("ebay_price_tmp.csv", "r"); 
			while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) 
			{
				$count++;
				if($count>1)
				{
					$results3=q("SELECT * FROM shop_items WHERE MPN='".$data[0]."' LIMIT 1;", $dbshop, __FILE__, __LINE__);
					if (mysqli_num_rows($results3)>0 and number_format(str_replace(",", ".", $data[1]), 2)>0)
					{
						$row3=mysqli_fetch_array($results3);

//						echo $data[0].' / '.$row3["item_id"].' / '.$_POST["accounts"].'<br />';
	
						//PREISE EINPFLEGEN
						$timestamp = time();
						$GUELTIG_AB = date("Y-m-j", $timestamp);
						$timestamp = $timestamp+315532800;
						$GUELTIG_BIS = date("Y-m-j", $timestamp);
	
						$results2=q("SELECT * FROM prpos WHERE ARTNR = '".$data[0]."' AND LST_NR = '".$row["pricelist"]."' ;", $dbshop, __FILE__, __LINE__);
						if (mysqli_num_rows($results2)>0)
						{
//							echo "UPDATE prpos SET POS_0_WERT=".number_format(str_replace(",", ".", $data[1]), 2).", GEAND='".date("Y-m-j H:i:s")."' WHERE ARTNR = '".$data[0]."' AND LST_NR = '".$row["pricelist"]."';<br />";
							q("UPDATE prpos SET POS_0_WERT=".number_format(str_replace(",", ".", $data[1]), 2).", GEAND='".date("Y-m-j H:i:s")."' WHERE ARTNR = '".$data[0]."' AND LST_NR = '".$row["pricelist"]."';", $dbshop, __FILE__, __LINE__);
						}
						else
						{
//							echo "INSERT INTO prpos(ARTNR,LSt_NR,POS_0_WERT,POS_0_PE,LSt_AKTIV_CHK,POS_AKTIV_CHK,AKTION_CHK,NETTO_CHK,GUELTIG_AB,GUELTIG_BIS,NEU,GEAND,MAN_ID) VALUES('".$data[0]."', ".$row["pricelist"].", ".number_format(str_replace(",", ".", $data[1]), 2).", 		0, 		1, 		1, 		0, 		1,		'".$GUELTIG_AB."',	'".$GUELTIG_BIS."',	'".date("Y-m-j H:i:s")."',	'".date("Y-m-j H:i:s")."', 	1) ;<br />";
							q("INSERT INTO prpos(ARTNR,LSt_NR,POS_0_WERT,POS_0_PE,LSt_AKTIV_CHK,POS_AKTIV_CHK,AKTION_CHK,NETTO_CHK,GUELTIG_AB,GUELTIG_BIS,NEU,GEAND,MAN_ID) VALUES('".$data[0]."', ".$row["pricelist"].", ".number_format(str_replace(",", ".", $data[1]), 2).", 		0, 		1, 		1, 		0, 		1,		'".$GUELTIG_AB."',	'".$GUELTIG_BIS."',	'".date("Y-m-j H:i:s")."',	'".date("Y-m-j H:i:s")."', 	1) ;", $dbshop, __FILE__, __LINE__);
						}
						$count_ok++;
						//
						$results4=q("SELECT * FROM ebay_auctions WHERE shopitem_id=".$row3["id_item"]." and account_id=".$_POST["accounts"].";", $dbshop, __FILE__, __LINE__);
						if (mysqli_num_rows($results4)>0)
						{
//							echo $data[0].' / '.$row3["item_id"].' / '.mysqli_num_rows($results4).'<br />';
							$count_auctions=$count_auctions+mysqli_num_rows($results4);
							$count_ebay++;
							$StartPrice=number_format(str_replace(",", ".", $data[1])*((100+UST)/100), 2); //mandatory
//							echo "UPDATE ebay_auctions SET StartPrice='".$StartPrice."', lastmod=".time().", lastupdate=0 WHERE shopitem_id=".$row3["item_id"]." and account_id=".$_POST["accounts"].";<br />";
							q("UPDATE ebay_auctions SET StartPrice='".$StartPrice."', lastmod=".time().", lastupdate=-1 WHERE shopitem_id=".$row3["id_item"]." and account_id=".$_POST["accounts"].";", $dbshop, __FILE__, __LINE__);
						}
					}
				}
			}
			fclose($handle);
			unlink("ebay_price_tmp.csv");
			echo '<div class="success">'.$count_ok.' Preise wurden für '.$row["title"].' aktualisiert. ('.$count_ebay.' Artikel, mit '.$count_auctions.' Auktionen)</div>';

        }
	}

	//PATH
	echo '<p>';
	echo '<a href="backend_index.php">Backend</a>';
	echo ' > <a href="backend_ebay_prices.php">eBay</a>';
	echo ' > Auktionen';
	echo '</p>';

	//HEADLINE
	echo '<h1>Preispflege';
	echo '</h1>';




// 	PRICE UPDATE WINDOW
	echo '<form method="post" enctype="multipart/form-data">';
	echo '<table style="width:600px;">';
	echo '	<tr>';
	echo '		<th colspan="2">';
	echo '			<span style="display:inline; float:left;">eBay-Preise hochladen</span>';
	echo '		</th>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Accounts</td>';
	echo '		<td>';
	echo '			<select name="accounts">';
	echo '				<option value="0">bitte wählen</option>';
	$results=q("SELECT * FROM ebay_accounts WHERE id_account>0 AND pricelist>0;", $dbshop, __FILE__, __LINE__);
	while($row=mysqli_fetch_array($results))
	{
		echo '			<option value="'.$row["id_account"].'">'.$row["title"].'</option>';
	}
	echo '			</select>';
	echo '		</td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Datei</td>';
	echo '		<td>';
	echo '			<input type="file" name="price_update_file" />';
	echo '			<br />Bitte CSV Datei auswählen!';
	echo '			<br />(Spalte1=Artnr. / Spalte2=Nettopreis)';
	echo '		</td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td colspan="2">';
	echo '			<input class="formbutton" type="submit" name="ebay_price_update" value="Preise hochladen">';
	echo '	</td>';
	echo '	</tr>';
	echo '</table>';
	echo '</form>';






?>
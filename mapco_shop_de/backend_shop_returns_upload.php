<?php
	include("config.php");
	include("templates/".TEMPLATE_BACKEND."/header.php");
	
//	session_start();
	if ( !isset($_SESSION["id_user"]) or !($_SESSION["id_user"]>0) ) exit;

	
	//PATH
	echo '<p>';
	echo '<a href="backend_index.php">Backend</a>';
	echo ' > <a href="backend_shop_index.php">Online-Shop</a>';
	echo ' > ';
	echo '</p>';
	
	echo '<h1>Rückgabedaten importieren</h1>';
	echo '<form method="post" enctype="multipart/form-data">';
	echo '<table>';
	echo '	<tr>';
	echo '		<td>Datei</td>';
	echo '		<td><input type="file" name="file" /></td>';
	echo '	</tr>';
	echo '	<tr><td colspan="2"><input style="float:right;" type="submit" name="import" value="Importieren" /></td></tr>';
	echo '</table>';
	echo '</form>';
	
	
	$eingelesen=false;
	$counter=0;
	$fertig=false;

	if ( isset($_SESSION["eingelesen"])) {$eingelesen=$_SESSION["eingelesen"];}
//	if ( isset($_SESSION["fertig"])) {$eingelesen=$_SESSION["fertig"];}
	if ( isset($_SESSION["counter"])) {$counter=$_SESSION["counter"];}
	
// DATENIMPORT
	if (isset($_POST["import"]))
	{
			$i=0;
			$handle = fopen ($_FILES["file"]["tmp_name"], "r"); 
			while($zeile=fgetcsv($handle, 1024,";"))
			{
				if ($zeile!="")
				{
				//	echo $zeile[2].'<br/>';
				$j=0;
				foreach ($zeile as $spalte) {
					if ($j<15) {
					$line[$i][$j]=htmlspecialchars($spalte);
					$j++;
					}
				}
					$i++;
				}
			}
			$_SESSION["line"]=$line;
			$eingelesen=true;
			$_SESSION["eingelesen"]=$eingelesen;
			$_SESSION["counter"]=1;
			fclose($handle);

	}
	
		// KONVERTIERUNG DATUM IN TIMESTAMP
	function dateConverter($date) {
			if ($date!="") 
			{
				return mktime(0,0,0,number_format(substr($date,3,2)), number_format(substr($date,0,2)), number_format(substr($date,8)));
			}
			else 
			{
				return 0;
			}
	}



//DATEN IN DB

	if ($_POST["speichern"]=="Speichern") {

	$sql ="INSERT INTO shop_returns (";
	$sql.="state, platform, userid, buyername, MPU, articlegroup, quantity, transactionID, invoiceID, rAction, rReason, rReason_detail, exchange_MPU, exchange_quantity, date_exchange_sent, date_order, date_announced, date_return, date_refund, date_refund_reshipment, date_r_IDIMS, date_case_closed, refund, refund_reshipment, firstmod, firstmod_user, lastmod, lastmod_user";
	$sql.=") VALUES(";
	$sql.="'".$_POST["state"]."', '".$_POST["platform"]."', '".$_POST["userid"]."', '".$_POST["buyername"]."', '".$_POST["MPU"]."', '".$_POST["articlegroup"]."','".$_POST["quantity"]."', '', '', '".$_POST["rAction"]."', '".$_POST["rReason"]."', '".$_POST["rReason_detail"]."' , '".$_POST["exchangeMPU"]."', '".$_POST["exchange_quantity"]."', '".dateConverter($_POST["exchange_date"])."', '', '".dateConverter($_POST["date"])."', '', '', '', '', '', '', '' ,'".dateConverter($_POST["date"])."', '23916', '".time()."', '23916'";
	$sql.=");";
//echo $sql;

q($sql, $dbshop, __FILE__, __LINE__);

	}

	
	if ($eingelesen && !$fertig) {
		
		$line=$_SESSION["line"];
		if ($counter=="") { $counter=1;}
		
	if (sizeof($line)>$counter) {	
		echo '<form method="post" enctype="multipart/form-data">';
		echo '<table>';
// KOPFZEILE
		echo '	<tr>';
			foreach ($line[0] as $col) {
				echo '<td>'.htmlspecialchars($col).'</td>';
			}
		echo '</tr>';
//DATENZEILE
		echo '	<tr>';
			foreach ($line[$counter] as $col) {
				echo '<td>'.htmlspecialchars($col).'</td>';
			}
		echo '</tr>';
		
//EIMGABENZEILE

		$platform=trim($line[$counter][1]);
		switch (trim($line[$counter][1])) {
			case "AP": $platform="ebay_AP"; break;
			case "MAPCO": $platform="ebay_MAPCO"; break;
			case "OOE": $platform="ooe"; break;
			case "AMA": $platform="amazon"; break;
		}
		
		if ($line[13]=="")
		{
			$rAction="return";
		}
		else { $rAction="exchange";}
		
		

		echo '<tr>';
		echo '<td><input type="text" name="date" value="'.$line[$counter][0].'" size="10" /></td>';
		echo '<td><input type="text" name="platform" value="'.$platform.'" size="8" /></td>';
		echo '<td><input type="text" name="quantity" value="'.$line[$counter][2].'" size="1" /></td>';
		echo '<td><input type="text" name="MPU" value="'.$line[$counter][3].'" size="10" /></td>';
		echo '<td><input type="text" name="articlegroup" value="'.$line[$counter][4].'" size="10" /></td>';
		echo '<td><input type="text" name="buyername" value="'.$line[$counter][5].'" size="20" /></td>';
	echo '		<td><select name="rReason" size="1" />';
	echo '			<option value="">Bitte Rückgabe-/Umtauschgrund wählen</option>';
		$results=q("SELECT * FROM shop_returns_rReason;", $dbshop, __FILE__, __LINE__);
		while( $row=mysql_fetch_array($results) ) {
			echo '			<option value='.$row["ID"].' title='.$row["rDescription"].'>'.$row["rReason"].'</option>';
		}
	echo '		</select></td>';
		echo '<td><input type="text" name="userid" value="'.$line[$counter][7].'" size="10" /></td>';
		echo '<td><input type="text" name="rReason_detail" value="'.$line[$counter][8].'" size="20" /></td>';
		echo '		<td><select name="rAction" size="1" />';
			echo '<option value="return">Rückgabe</option>';
			echo '<option value="exchange">Umtausch</option>';
		echo '		</select></td>';
		echo '<td>exMPU<input type="text" name="exchangeMPU" size="10" /><br>';
		echo 'exQuantity<input type="text" name="exchange_quantity" size="1" /><br>';
		echo '<input type="text" name="exchange_date" size="10" /></td>';
		echo '		<td><select name="state" size="1" />';
			echo '<option value="closed">geschlossen</option>';
			if ($line[$counter][11]=="offen") { echo '<option value="open" selected>offen</option>'; }
			else {echo '<option value="open">offen</option>';}
		echo '		</select></td>';

		echo '</table>';
		
		$counter++;
		$_SESSION["counter"]=$counter;
		
		echo '<input type="submit" name="speichern" value="Speichern" />';
		echo '<input type="submit" name="cancel" value="Überspringen" />';
		
		
		echo '</form>';

	}

		
		//echo sizeof($line);
		
		
	}
	
	


	include("templates/".TEMPLATE_BACKEND."/footer.php");
?>
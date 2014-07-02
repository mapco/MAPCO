<?php
	include("config.php");
	include("templates/".TEMPLATE_BACKEND."/header.php");

	if ( isset($_FILES["file"]) )
	{
		$filename=$_FILES["file"]["name"];
		$results=q("SELECT * FROM dpd_import WHERE filename='".$filename."';", $dbshop, __FILE__, __LINE__);
		if( mysqli_num_rows($results)>0 )
		{
			echo '<div class="failure">Datei bereits bekannt.</div>';
			exit;
		}
		$handle=fopen($_FILES["file"]["tmp_name"], "r");
		while($line=fgetcsv($handle, 4096, ";"))
		{
//			echo utf8_encode($line[2]).'<br />';
			$pos=-1;
			for($i=0; $i<7; $i++)
			{
				$pos=strpos($line[2], ",", $pos+1);
				if( substr($line[2], $pos+4, 1) == "-" ) break;
			}
			$CountryCode=substr($line[2], $pos+2, 2);
			$ZipCode=substr($line[2], $pos+5, 5);
			
			q("INSERT INTO dpd_import (TrackingCode, Address, CountryCode, ZipCode, Weight, filename, imported) VALUES('".$line[0]."', '".mysqli_real_escape_string($dbshop, utf8_encode($line[2]))."', '".$CountryCode."', '".$ZipCode."', '".str_replace(",", ".", $line[3])."', '".mysqli_real_escape_string($dbshop, $filename)."', 0);", $dbshop, __FILE__, __LINE__);
		}
	}
?>

	<script type="text/javascript">
		function import_failure($id)
		{
			$.post("<?php echo PATH; ?>soa/", { API:"dhl", Action:"DPDImportFailure", id:$id }, function($data)
			{
				try
				{
					$xml = $($.parseXML($data));
					$ack = $xml.find("Ack");
					if ( $ack.text()!="Success" )
					{
						show_status2($data);
						return;
					}
				}
				catch (ex)
				{
					show_status2($data);
					return;
					this.debug(ex);
				}

				show_status("Datensatz erfolgreich markiert.");
				window.location.href=window.location.href;
			});
		}
	

		function import_success($id, $id_order)
		{
			$.post("<?php echo PATH; ?>soa/", { API:"dhl", Action:"DPDImportSuccess", id:$id, id_order:$id_order }, function($data)
			{
				try
				{
					$xml = $($.parseXML($data));
					$ack = $xml.find("Ack");
					if ( $ack.text()!="Success" )
					{
						show_status2($data);
						return;
					}
				}
				catch (ex)
				{
					show_status2($data);
					return;
					this.debug(ex);
				}

				show_status("Datensatz erfolgreich zugeordnet.");
				window.location.href=window.location.href;
			});
		}
	</script>

<?php
	echo '<div style="position:relative;" id="progressbarWrapper" style="width:300px; height: 30px;" class="ui-widget-default">';
	echo '	<div id="progressbar"></div>';
	echo '	<div style="position:absolute; left:0px; top:5px; width:100%; height:25px; text-align:center;" id="progressText"></div>';
	echo '</div>';

	//PATH
	echo '<p>';
	echo '<a href="backend_index.php">Backend</a>';
	echo ' > <a href="backend_admin_index.php">Administration</a>';
	echo ' > Zolltarifnummern';
	echo '</p>';

	echo '<h1>DPD-Sendungsnummern importieren</h1>';
	echo '<form method="post" enctype="multipart/form-data">';
	echo '	<input type="file" name="file" />';
	echo '	<input type="submit" value="Hochladen" />';
	echo '</form>';


	$results=q("SELECT * FROM dpd_import WHERE imported=0;", $dbshop, __FILE__, __LINE__);
	echo '<br /><br />TODO: '.mysqli_num_rows($results);
	while( $row=mysqli_fetch_array($results) )
	{
		$results2=q("SELECT * FROM shop_orders WHERE bill_zip='".$row["ZipCode"]."' AND bill_country_code='".$row["CountryCode"]."' ORDER BY firstmod DESC;", $dbshop, __FILE__, __LINE__);
		$results3=q("SELECT * FROM shop_orders WHERE ship_zip='".$row["ZipCode"]."' AND ship_country_code='".$row["CountryCode"]."' ORDER BY firstmod DESC;", $dbshop, __FILE__, __LINE__);
		if( mysqli_num_rows($results2)>0 or mysqli_num_rows($results3)>0)
		{
			echo '<table>';
			
			echo '<tr style="background:#00ff00;">';
			echo '	<td colspan="9">'.$row["Address"].'</td>';
			echo '	<td>'.$row["Weight"].'</td>';
			echo '	<td><a href="https://tracking.dpd.de/cgi-bin/delistrack?pknr='.$row["TrackingCode"].'" target="_blank">'.$row["TrackingCode"].'</a></td>';
			echo '	<td><input type="button" value="Keiner Bestellung zuordnen" onclick="import_failure('.$row["id"].');" /></td>';
			echo '</tr>';
			
			while( $row2=mysqli_fetch_array($results2) )
			{
				echo '<tr>';
//				echo '	<td>'.$row2["id_order"].'</td>';
				echo '	<td>'.date("d-m-Y H:i", $row2["firstmod"]).'</td>';
				echo '	<td>'.$row2["bill_company"].'</td>';
				echo '	<td>'.$row2["bill_firstname"].'</td>';
				echo '	<td>'.$row2["bill_lastname"].'</td>';
				echo '	<td>'.$row2["bill_street"].'</td>';
				echo '	<td>'.$row2["bill_number"].'</td>';
				echo '	<td>'.$row2["bill_zip"].'</td>';
				echo '	<td>'.$row2["bill_country"].'</td>';
				echo '	<td>'.$row2["shipping_details"].'</td>';
				echo '	<td>'.$row2["shipping_WeightInKG"].'</td>';
				echo '	<td><a href="http://nolp.dhl.de/nextt-online-public/set_identcodes.do?lang=de&idc='.$row2["shipping_number"].'&rfn=&extendedSearch=true" target="_blank">'.$row2["shipping_number"].'</a></td>';
				echo '	<td><input type="button" value="Dieser Bestellung zuordnen" onclick="import_success('.$row["id"].', '.$row2["id_order"].');" /></td>';
				echo '</tr>';
			}
			while( $row3=mysqli_fetch_array($results3) )
			{
				echo '<tr>';
				echo '	<td>'.date("d-m-Y H:i", $row3["firstmod"]).'</td>';
				echo '	<td>'.$row3["ship_company"].'</td>';
				echo '	<td>'.$row3["ship_firstname"].'</td>';
				echo '	<td>'.$row3["ship_lastname"].'</td>';
				echo '	<td>'.$row3["ship_street"].'</td>';
				echo '	<td>'.$row3["ship_number"].'</td>';
				echo '	<td>'.$row3["ship_zip"].'</td>';
				echo '	<td>'.$row3["ship_country"].'</td>';
				echo '	<td>'.$row3["shipping_details"].'</td>';
				echo '	<td>'.$row3["shipping_WeightInKG"].'</td>';
				echo '	<td><a href="http://nolp.dhl.de/nextt-online-public/set_identcodes.do?lang=de&idc='.$row3["shipping_number"].'&rfn=&extendedSearch=true" target="_blank">'.$row3["shipping_number"].'</a></td>';
				echo '	<td><input type="button" value="Dieser Bestellung zuordnen" onclick="import_success('.$row["id"].', '.$row3["id_order"].');" /></td>';
				echo '</tr>';
			}
			echo '</table>';
		}
		else
		{
			echo '<table>';
			echo '<tr style="background:#ff0000;">';
			echo '	<td colspan="8">'.$row["Address"].'</td>';
			echo '	<td><input type="button" value="Keiner Bestellung zuordnen" onclick="import_failure('.$row["id"].');" /></td>';
			echo '</tr>';
			echo '</table>';
		}
		break;
	}


	include("templates/".TEMPLATE_BACKEND."/footer.php");
?>
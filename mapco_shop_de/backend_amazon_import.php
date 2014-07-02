<?php
	include("config.php");
	include("templates/".TEMPLATE_BACKEND."/header.php");

	if ( isset($_FILES["file"]) )
	{
		//cache amazon products
		$products=array();
		$results=q("SELECT * FROM amazon_products WHERE account_id=".$_POST["id_account"].";", $dbshop, __FILE__, __LINE__);
		while( $row=mysqli_fetch_array($results) )
		{
			$products[$row["ASIN"]]=$row["id_product"];
		}
		
		$skipped=0;
		$new=0;
		$handle=fopen($_FILES["file"]["tmp_name"], "r");
		$line=fgetcsv($handle, 4096, ";");
		$line=fgetcsv($handle, 4096, ";");
		while($line=fgetcsv($handle, 4096, ";"))
		{
			if( !isset($line[18]) )
			{
				print_r($line);
				exit;
			}
			if( !isset($products[$line[0]]) )
			{
				$query="SELECT * FROM shop_items WHERE MPN='".mysqli_real_escape_string($dbshop, utf8_decode($line[18]))."';";
				if( mysqli_num_rows($results2)>0 )
				{
					$row2=mysqli_fetch_array($results2);
					q("INSERT INTO amazon_products(item_id, ASIN, account_id) VALUES(".$row2["id_item"].", '".$line[0]."', ".$_POST["id_account"].");", $dbshop, __FILE__, __LINE__);
					$new++;
				}
				else
				{
					echo '<div class="failure">'.$line[18].' nicht gefunden. Produkt übersprungen.</div>';
				}
			}
			else $skipped++;
		}
		echo '<div class="success">'.$new.' Produkte neu eingetragen. '.$skipped.' Produkte übersprungen, da bekannt.</div>';
	}

	//PATH
	echo '<p>';
	echo '<a href="backend_index.php">Backend</a>';
	echo ' > <a href="backend_amazon_index.php">Amazon</a>';
	echo ' > Vendor Central Import';
	echo '</p>';

	echo '<h1>Vendor Central Import</h1>';
	echo '<p>Diese Funktion kan eine ASIN-Liste von Amazon mit der Shop-Datenbank abgleichen.</p>';
	echo '<form method="post" enctype="multipart/form-data">';
	echo '	Account: <select name="id_account">';
	$results=q("SELECT * FROM amazon_accounts;", $dbshop, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		echo '<option value="'.$row["id_account"].'">'.$row["title"].'</option>';
	}
	echo '	</select><br />';
	echo '	<input type="file" name="file" />';
	echo '	<input type="submit" value="Hochladen" />';
	echo '</form>';


	include("templates/".TEMPLATE_BACKEND."/footer.php");
?>
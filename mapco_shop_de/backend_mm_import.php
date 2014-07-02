<?php
	include("config.php");
	include("templates/".TEMPLATE_BACKEND."/header.php");

	//Import
	if ($_POST["form_button"]=="Importieren")
	{
		if ($_POST["format"]=="Scheibel GmbH")
		{
			q("TRUNCATE shop_items;", $dbshop, __FILE__, __LINE__);
			q("TRUNCATE shop_items_de;", $dbshop, __FILE__, __LINE__);
			q("TRUNCATE shop_items_files;", $dbshop, __FILE__, __LINE__);
			echo '<table>';
			echo '	<tr>';
			echo '		<th>Bild</th>';
			echo '		<th>Titel</th>';
			echo '		<th>Beschreibung</th>';
			echo '		<th>Preis</th>';
			echo '	</tr>';
			$handle = fopen ($_FILES["file"]["tmp_name"],"r"); 
			$artikel=fgetcsv($handle, 1024, ";");
			while($artikel=fgetcsv($handle, 1024, ";"))
			{
				//title and description
				if (strpos($artikel[1], ",")==0)
				{
					$title=utf8_encode($artikel[1]);
					$description="";
				}
				else
				{
					$title=utf8_encode(substr($artikel[1], 0, strpos($artikel[1], ",")));
					$description=utf8_encode(substr($artikel[1], strpos($artikel[1], ",")+2, strlen($artikel[1])));
				}
				
				//imagefile
				if ($artikel[10]!='http://www.scheibel-gmbh.de/eclass_gif/')
				{
					$image=file_get_contents($artikel[10]);
					$filename=substr($artikel[10], strrpos($artikel[10], "/")+1, (strlen($artikel[10])-strrpos($artikel[10], ".")+2));
					$extension=substr($artikel[10], strrpos($artikel[10], ".")+1, strlen($artikel[10]));
					q("INSERT INTO cms_files (filename, extension, firstmod, firstmod_user, lastmod, lastmod_user) VALUES('".$filename."', '".$extension."', '".time()."', '".$_SESSION["id_user"]."', '".time()."', '".$_SESSION["id_user"]."');", $dbweb, __FILE__, __LINE__);
					$id_file=mysql_insert_id($dbweb);
					$dir=bcdiv($id_file, 1000, 0); 
					if (!file_exists('files/'.$dir)) mkdir('files/'.$dir);
					file_put_contents('files/'.$dir.'/'.$id_file.'.'.$extension, $image);
				}
				
				echo '<tr>';
				echo '	<td>'.utf8_encode($artikel[10]).'</td>';
				echo '	<td>'.$title.'</td>';
				echo '	<td>'.$description.'</td>';
				echo '	<td>'.utf8_encode($artikel[2]).'</td>';
				echo '</tr>';
				q("INSERT INTO shop_items (price, category_id, firstmod, lastmod) VALUES('".($artikel[2] * 1.2)."', '2', '".time()."', '".time()."');", $dbshop, __FILE__, __LINE__);
				$id_item=mysql_insert_id($dbshop);
				q("INSERT INTO shop_items_files (item_id, file_id) VALUES('".$id_item."', '".$id_file."');", $dbshop, __FILE__, __LINE__);
				q("INSERT INTO shop_items_de (title, short_description, description) VALUES('".$title."', '".$description."', '".$description."'); ", $dbshop, __FILE__, __LINE__);
			}
			echo '</table>';
		}
		else echo '<div class="failure">Für dieses Datenformat exisitiert noch kein Importfilter.</div>';
	}
	
	//Editor
	echo '<h1>Artikeldaten importieren</h1>';
	echo '<form method="post" enctype="multipart/form-data">';
	echo '<table>';
	echo '	<tr>';
	echo '		<td>Datenformat</td>';
	echo '		<td>';
	echo '			<select name="format">';
	echo '				<option>Scheibel GmbH</option>';
	echo '				<option>Andere</option>';
	echo '			</select>';
	echo '		</td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Datei</td>';
	echo '		<td><input type="file" name="file" /></td>';
	echo '	</tr>';
	echo '	<tr><td colspan="2"><input style="float:right;" class="formbutton" type="submit" name="form_button" value="Importieren" /></td></tr>';
	echo '</table>';
	echo '</form>';

	include("templates/".TEMPLATE_BACKEND."/footer.php");
?>
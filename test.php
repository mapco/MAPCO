<?php
	function copy_folder($source, $destination)
	{
		if (is_dir($source))
		{
			if ($dh = opendir($source))
			{
				echo '<table border="1">';
				echo '<tr>';
				echo '	<th>Nr.</th>';
				echo '	<th>Datei</th>';
				echo '	<th>Status</th>';
				echo '</tr>';
				$i=0;
				while (($file = readdir($dh)) !== false)
				{
					if ($file!="." and $file!=".." and !file_exists($destination.'/'.$file))
					{
						echo '<tr>';
						$i++;
						echo '<td>'.$i.'</td>';
						echo '<td>'.$file.'</td>';
						if ( copy($source.'/'.$file, $destination.'/'.$file) ) echo '<td>OK</td>';
						else echo '<td><span style="color:#ff0000;">FEHLER</span></td>';
					}
				}
				echo '<tr><td colspan="3">'.$i.' Dateien erfolgreich kopiert.</td></tr>';
				echo '</table>';
				closedir($dh);
			}
		} else echo '<p>'.$source.' ist kein Verzeichnis!</p>';
	}
	
	copy_folder("mapco_de/fotos/abbildungen/tecdoc", "mapco_shop_de/fotos/abbildungen/tecdoc");
?>
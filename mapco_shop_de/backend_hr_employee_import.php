<?php
	include("config.php");
	$leftmenu=true;
	include("templates/".TEMPLATE_BACKEND."/header.php");

	//PATH
	echo '<p>';
	echo '<a href="backend_index.php">Backend</a>';
	echo ' > <a href="backend_crm_index.php">Kundenpflege</a>';
	echo ' > <a href="backend_crm_customers.php">Alle Kunden</a>';
	echo ' > Import';
	echo '</p>';

	if (isset($_POST["import"]))
	{
		//CSV-Import
		if ($_POST["format"]==0)
		{
			echo '<div class="failure">CSV-Import derzeit noch nicht integriert.</div>';
		}
		
		//Import aus MAPCO-Kontaktliste
		elseif ($_POST["format"]==1)
		{
			echo '<table>';
			$handle = fopen ($_FILES["file"]["tmp_name"],"r"); 
			
			//Kopfzeile
			echo '	<tr>';
			$mitarbeiter=fgetcsv($handle, 16384, ";");
			for ($i=0; $i<sizeof($mitarbeiter); $i++)
			{
				echo '<th>('.$i.') '.utf8_encode($kunde[$i]).'</th>';
			}
			echo '	</tr>';
			
			while($mitarbeiter=fgetcsv($handle, 16384, ";"))
			{
				if ($mitarbeiter[0]!="" and $mitarbeiter[2]!="")
				{
					$name=explode(", ", $mitarbeiter[0]);
					echo '<tr>';
					for ($i=0; $i<sizeof($mitarbeiter); $i++)
					{
						$mitarbeiter[$i]=utf8_encode($mitarbeiter[$i]);
						echo '<td>'.$mitarbeiter[$i].'</td>';
						$mitarbeiter[$i]=addslashes($mitarbeiter[$i]);
					}
					echo '	</tr>';
					q("INSERT INTO hr_employees (firstname, middlename, lastname, position, department, street, street_nr, city, country, phone, fax, mobile, mail, user_id, firstmod, firstmod_user, lastmod, lastmod_user) VALUES('".utf8_encode($name[1])."', '', '".utf8_encode($name[0])."', '', '".$mitarbeiter[5]."', '', '', '', '', '".$mitarbeiter[2]."', '".$mitarbeiter[4]."', '".$mitarbeiter[3]."', '".$mitarbeiter[1]."', 0, ".$_SESSION["id_user"].", ".time().", ".$_SESSION["id_user"].", ".time().");", $dbweb, __FILE__, __LINE__);
				}
			}
			echo '</table>';
		}
		
		//Import Error
		else
		{
			echo '<div class="failure">Beim Importieren trat ein Fehler auf.</div>';
		}
	}


	//EDITOR
	echo '<h1>Mitarbeiterprofile importieren</h1>';
	echo '<form method="post" enctype="multipart/form-data">';
	echo '<table>';
	echo '	<tr>';
	echo '		<td>Datenformat</td>';
	echo '		<td>';
	echo '			<select name="format">';
	echo '				<option value="0">CSV-Format</option>';
	echo '				<option value="1">MAPCO-Kontaktliste</option>';
	echo '			</select>';
	echo '		</td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Datei</td>';
	echo '		<td><input type="file" name="file" /></td>';
	echo '	</tr>';
	echo '	<tr><td colspan="2"><input style="float:right;" class="formbutton" type="submit" name="import" value="Importieren" /></td></tr>';
	echo '</table>';
	echo '</form>';

	include("templates/".TEMPLATE_BACKEND."/footer.php");
?>
<?php
	//locations
	echo '<div style="margin:5px; float:left;">';
	echo '	<a href="javascript:$(\'#locations\').toggle();" style="padding:10px; text-align:center; background:#cccccc; float:left;">';
	if ( isset($_POST["id_location"]) and $_POST["id_location"]>0 )
	{
		$display='none'; 
		$results=q("SELECT * FROM cms_contacts_locations WHERE id_location=".$_POST["id_location"].";", $dbweb, __FILE__, __LINE__);
		if( mysqli_num_rows($results)>0 )
		{
			$row=mysqli_fetch_array($results);
			$title="Standort ".$row["location"];
		}
	}
	else
	{
		$display='block';
		$title="Standorte";
	}
	$title=str_split(utf8_decode($title), 1);
	echo utf8_encode(implode("<br />", $title));
	echo '	</a>';
	echo '<ul id="locations" style="width:250px; margin:0; display:'.$display.'; float:left;" class="orderlist">';
	echo '	<li id="locations_header" style="width:238px; background:#ccc;">';
	echo '		Standorte';
	echo '		<img src="images/icons/24x24/add.png" alt="Standort hinzufügen" title="Standort hinzufügen" onclick="location_add();" />';
	echo '	</li>';

	$location=array();
	$results=q("SELECT * FROM cms_contacts_locations WHERE site_id=".$_SESSION["id_site"]." ORDER BY ordering;", $dbweb, __FILE__, __LINE__);
	while($row=mysqli_fetch_array($results))
	{
		$location[$row["id_location"]]=$row["location"];
		echo '<li style="width:238px;" id="locations_'.$row["id_location"].'">';
		echo '	<img src="images/icons/24x24/remove.png" onclick="location_remove('.$row["id_location"].');" alt="Standort löschen" title="Standort löschen" />';
		echo '	<img src="images/icons/24x24/edit.png" onclick="location_edit('.$row["id_location"].', \''.addslashes($row["location"]).'\', \''.addslashes($row["company"]).'\', \''.addslashes($row["title"]).'\', \''.addslashes($row["firstname"]).'\', \''.addslashes($row["lastname"]).'\', \''.addslashes($row["street"]).'\', \''.addslashes($row["streetnr"]).'\', \''.addslashes($row["zipcode"]).'\', \''.addslashes($row["city"]).'\', \''.addslashes($row["country"]).'\', \''.addslashes($row["country_code"]).'\', \''.addslashes($row["phone"]).'\', \''.addslashes($row["fax"]).'\', \''.addslashes($row["website"]).'\', \''.addslashes($row["mail"]).'\', \''.addslashes($row["monday"]).'\', \''.addslashes($row["tuesday"]).'\', \''.addslashes($row["wednesday"]).'\', \''.addslashes($row["thursday"]).'\', \''.addslashes($row["friday"]).'\', \''.addslashes($row["saturday"]).'\', \''.addslashes($row["sunday"]).'\');" alt="Standort bearbeiten" title="Standort bearbeiten" />';
		if ($_POST["id_location"]==$row["id_location"]) $style=' style="font-weight:bold;"'; else $style='';
		echo '	<a'.$style.' href="javascript:id_location='.$row["id_location"].'; id_department=0; id_contact=0; contacts_view();">'.$row["ordering"].'. '.$row["location"].'</a>';
		echo '</li>';
	}
	echo '</ul>';
	echo '</div>';
	echo '</div>';

	//departments
	if ( isset($_POST["id_location"]) and $_POST["id_location"]>0 )
	{
		echo '<div style="margin:5px; float:left;">';
		echo '	<a href="javascript:$(\'#departments\').toggle();" style="padding:10px; text-align:center; background:#cccccc; float:left;">';
		if ( isset($_POST["id_department"]) and $_POST["id_department"]>0 )
		{
			$display='none'; 
			$results=q("SELECT * FROM cms_contacts_departments WHERE id_department=".$_POST["id_department"].";", $dbweb, __FILE__, __LINE__);
			if( mysqli_num_rows($results)>0 )
			{
				$row=mysqli_fetch_array($results);
				$title="Abteilung ".$row["department"];
			}
		}
		else
		{
			$display='block';
			$title="Abteilungen";
		}
		$title=str_split(utf8_decode($title), 1);
		echo utf8_encode(implode("<br />", $title));
		echo '	</a>';
		
		echo '<ul id="departments" style="width:300px; margin:0; display:'.$display.'; float:left;" class="orderlist">';
		$results=q("SELECT * FROM cms_contacts_departments WHERE location_id=".$_POST["id_location"]." ORDER BY ordering;", $dbweb, __FILE__, __LINE__);
		echo '	<li style="width:288px; background:#ccc;" id="departments_header">';
		echo '		Abteilungen';
		echo '		<img src="images/icons/24x24/add.png" alt="Abteilung hinzufügen" title="Abteilung hinzufügen" onclick="department_add('.$_POST["id_location"].');" />';
		echo '	</li>';
		while($row=mysqli_fetch_array($results))
		{
			echo '<li style="width:288px;" id="departments_'.$row["id_department"].'">';
			echo '	<img src="images/icons/24x24/remove.png" onclick="department_remove('.$row["id_department"].');" alt="Abteilung löschen" title="Abteilung löschen" />';
			echo '	<img src="images/icons/24x24/edit.png" onclick="department_edit('.$row["id_department"].', \''.addslashes($row["department"]).'\');" alt="Abteilung bearbeiten" title="Abteilung bearbeiten" />';
			if ($_POST["id_department"]==$row["id_department"]) $style=' style="font-weight:bold;"'; else $style='';
			echo '	<a'.$style.' href="javascript:id_location='.$_POST["id_location"].'; id_department='.$row["id_department"].'; id_contact=0; contacts_view();">'.$row["ordering"].'. '.$row["department"].'</a>';
			echo '</li>';
		}
		echo '</ul>';
		echo '</div>';
	}

	//contacts
	if ( isset($_POST["id_department"]) and $_POST["id_department"]>0 )
	{
		echo '<div style="margin:5px; float:left;">';
		echo '	<a href="javascript:$(\'#contacts\').toggle();" style="padding:10px; text-align:center; background:#cccccc; float:left;">';
		echo '		K<br />';
		echo '		o<br />';
		echo '		n<br />';
		echo '		t<br />';
		echo '		a<br />';
		echo '		k<br />';
		echo '		t<br />';
		echo '		e<br />';
		echo '	</a>';
//		if ( isset($_POST["id_contact"]) and $_POST["id_contact"]>0 ) $display='none'; else $display='block';
		$display='block';
		echo '<ul id="contacts" style="width:400px; margin:0; display:'.$display.'; float:left;" class="orderlist">';
		$results=q("SELECT * FROM cms_contacts WHERE department_id=".$_POST["id_department"]." ORDER BY ordering;", $dbweb, __FILE__, __LINE__);
		echo '	<li style="width:388px; background:#ccc;" id="contacts_header">';
		echo '		Kontakte';
		echo '		<img src="images/icons/24x24/add.png" alt="Kontakt hinzufügen" title="Kontakt hinzufügen" onclick="contact_add('.$_POST["id_department"].');" />';
		echo '	</li>';
		while($row=mysqli_fetch_array($results))
		{
			echo '<li style="width:388px;" id="contacts_'.$row["id_contact"].'">';
			echo '	<img src="images/icons/24x24/remove.png" onclick="contact_remove('.$row["id_contact"].');" alt="Kontakt löschen" title="Kontakt löschen" />';
			echo '	<img src="images/icons/24x24/edit.png" onclick="contact_edit('.$row["id_contact"].', \''.addslashes($row["firstname"]).'\', \''.addslashes($row["lastname"]).'\', \''.addslashes($row["position"]).'\', \''.addslashes($row["languages"]).'\', \''.addslashes($row["phone"]).'\', \''.addslashes($row["fax"]).'\', \''.addslashes($row["mobile"]).'\', \''.addslashes($row["mail"]).'\', \''.addslashes($row["gender"]).'\', \''.addslashes($row["active"]).'\');" alt="Kontakt bearbeiten" title="Kontakt bearbeiten" />';
			if ( $row["active"]==0 ) $style=' style="text-decoration:line-through;"'; else $style='';
			if ($_POST["id_contact"]==$row["id_contact"])
			{
				if( $style!="" ) $style=' style="font-weight:bold; text-decoration:line-through;"';
				else $style=' style="font-weight:bold;"';
			}
			echo '	<a'.$style.' href="javascript:id_location='.$_POST["id_location"].'; id_department='.$_POST["id_department"].'; id_contact='.$row["id_contact"].'; contacts_view();">'.$row["ordering"].'. '.$row["firstname"].' '.$row["lastname"].'</a>';
			echo '	<br /><i style="color:#bbbbbb;">'.$row["position"].'</i>';
			echo '</li>';
		}
		echo '</ul>';
		echo '</div>';
	}

	//contact
	if ( isset($_POST["id_contact"]) and $_POST["id_contact"]>0 )
	{
		$results=q("SELECT * FROM cms_contacts WHERE id_contact=".$_POST["id_contact"].";", $dbweb, __FILE__, __LINE__);
		if( mysqli_num_rows($results)>0 )
		{
			$row=mysqli_fetch_array($results);
			echo '<table class="hover" style="margin:5px; float:left;">';
			echo '	<tr>';
			echo '		<th colspan="3">Kontaktinformationen</th>';
			echo '	</tr>';
			echo '	<tr>';
			echo '		<td rowspan="11" style="vertical-align:top;">';
			$src=PATH.'images/icons/128x128/user.png';
			if( $row["article_id"]>0 )
			{
				$results2=q("SELECT * FROM cms_articles_images WHERE article_id=".$row["article_id"]." ORDER BY ordering DESC;", $dbweb, __FILE__, __LINE__);
				if( mysqli_num_rows($results2)>0 )
				{
					$row2=mysqli_fetch_array($results2);
					$results3=q("SELECT * FROM cms_files WHERE original_id=".$row2["file_id"]." AND imageformat_id=23;", $dbweb, __FILE__, __LINE__);
					$row3=mysqli_fetch_array($results3);
					$folder=floor($row3["id_file"]/1000);
					$src=PATH.'files/'.$folder.'/'.$row3["id_file"].'.'.$row3["extension"];
				}
			}
			echo '			<img alt="Neue Foto hochladen" onclick="images_upload();" src="'.$src.'" style="cursor:pointer;" title="Neues Foto hochladen" />';
			echo '		</td>';
			echo '		<td>Vorname</td>';
			echo '		<td>'.$row["firstname"].'</td>';
			echo '	</tr>';
			echo '	<tr>';
			echo '		<td>Nachname</td>';
			echo '		<td>'.$row["lastname"].'</td>';
			echo '	</tr>';
			echo '	<tr>';
			echo '		<td>Position</td>';
			echo '		<td>'.$row["position"].'</td>';
			echo '	</tr>';
			echo '	<tr>';
			echo '		<td>Sprachen</td>';
			echo '		<td>'.$row["languages"].'</td>';
			echo '	</tr>';
			echo '	<tr>';
			echo '		<td>Telefon</td>';
			echo '		<td>'.$row["phone"].'</td>';
			echo '	</tr>';
			echo '	<tr>';
			echo '		<td>Telefax</td>';
			echo '		<td>'.$row["fax"].'</td>';
			echo '	</tr>';
			echo '	<tr>';
			echo '		<td>Mobiltelefon</td>';
			echo '		<td>'.$row["mobile"].'</td>';
			echo '	</tr>';
			echo '	<tr>';
			echo '		<td>E-Mail</td>';
			echo '		<td>'.$row["mail"].'</td>';
			echo '	</tr>';
			echo '	<tr>';
			echo '		<td>Geschlecht</td>';
			if( $row["gender"]=="f" ) $gender="weiblich"; else $gender='männlich';
			echo '		<td>'.$gender.'</td>';
			echo '	</tr>';
			echo '	<tr>';
			echo '		<td>Aktiv?</td>';
			if( $row["active"]==0 ) $active="nein"; else $active='ja';
			echo '		<td>'.$active.'</td>';
			echo '	</tr>';
			echo '	<tr>';
			echo '		<td>Benutzeraccount</td>';
			if( $row["idCmsUser"]>0 )
			{
				$results2=q("SELECT * FROM cms_users WHERE id_user=".$row["idCmsUser"].";", $dbweb, __FILE__, __LINE__);
				$row2=mysqli_fetch_array($results2);
				$account=$row2["username"];
			}
			else $account='---';
			echo '		<td>'.$account.'</td>';
			echo '	</tr>';
			echo '</table>';
		}
	}

?>
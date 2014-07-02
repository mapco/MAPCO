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
	echo '	</li>';

	$location=array();
	$results=q("SELECT * FROM cms_contacts_locations WHERE site_id=".$_SESSION["id_site"]." ORDER BY ordering;", $dbweb, __FILE__, __LINE__);
	while($row=mysqli_fetch_array($results))
	{
		$location[$row["id_location"]]=$row["location"];
		echo '<li style="width:238px;" id="locations_'.$row["id_location"].'">';
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
		echo '	</li>';
		while($row=mysqli_fetch_array($results))
		{
			echo '<li style="width:288px;" id="departments_'.$row["id_department"].'">';
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
		echo '	</li>';
		while($row=mysqli_fetch_array($results))
		{
			echo '<li style="width:388px;" id="contacts_'.$row["id_contact"].'">';
			if ( $row["active"]==0 ) $style=' style="text-decoration:line-through;"'; else $style='';
			if ($_POST["id_contact"]==$row["id_contact"])
			{
				if( $style!="" ) $style=' style="font-weight:bold; text-decoration:line-through;"';
				else $style=' style="font-weight:bold;"';
			}
			//echo '	<a'.$style.' href="javascript:id_location='.$_POST["id_location"].'; id_department='.$_POST["id_department"].'; id_contact='.$row["id_contact"].'; contacts_view();">'.$row["ordering"].'. '.$row["firstname"].' '.$row["lastname"].'</a>';
			echo '<input id="" class="mail_account_user_check" value="'.$row["idCmsUser"].'" type="checkbox" />'.$row["firstname"].' '.$row["lastname"];
			echo '	<br /><i style="color:#bbbbbb;">'.$row["position"].'</i>';
			echo '</li>';
		}
		echo '<li><button onClick="mail_account_users_save()">Ausgewählte Benutzer zuweisen</button></li>';
		echo '</ul>';
		echo '</div>';
	}

?>
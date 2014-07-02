<?php
	include("config.php");
	include("templates/".TEMPLATE."/header.php");
	include("templates/".TEMPLATE."/cms_leftcolumn.php");

	function show_department($department_id)
	{
		global $dbweb;
		
		$results=q("SELECT * FROM cms_contacts_locations AS a, cms_contacts_departments AS b WHERE b.id_department='".$department_id."' AND a.id_location=b.location_id ORDER BY b.ordering ;", $dbweb, __FILE__, __LINE__);
		$row=mysqli_fetch_array($results);
		$results2=q("SELECT * FROM cms_contacts WHERE department_id='".$department_id."' AND active=1 AND published=1 ORDER BY ordering;", $dbweb, __FILE__, __LINE__);
		if(mysqli_num_rows($results2)>0)
		{
			echo '<table class="hover">';
			echo '<tr><th colspan="3">'.$row["title"].' - '.$row["department"].'</th></tr>';
			while($row2=mysqli_fetch_array($results2))
			{
				echo '<tr>';
				if( $row2["article_id"]>0 )
				{
					$results3=q("SELECT * FROM cms_articles_images WHERE article_id=".$row2["article_id"]." ORDER BY ordering DESC;", $dbweb, __FILE__, __LINE__);
					if( mysqli_num_rows($results3)>0 )
					{
						$row3=mysqli_fetch_array($results3);
						$results4=q("SELECT * FROM cms_files WHERE original_id=".$row3["file_id"]." AND imageformat_id=23;", $dbweb, __FILE__, __LINE__);
						$row4=mysqli_fetch_array($results4);
						$folder=floor($row4["id_file"]/1000);
						$src=PATH.'files/'.$folder.'/'.$row4["id_file"].'.'.$row4["extension"];
						echo '	<td style="width:100px; vertical-align:top;"><img style="width:100px;" src="'.$src.'" /></td>';
					}
				}
				else echo '	<td style="width:100px; vertical-align:top;"><img style="width:100px;" src="'.PATH.'images/employees/0employee.jpg" /></td>';
				echo '	<td style="vertical-align:top;">';
				echo '		<b>'.$row2["firstname"].' '.$row2["lastname"].'</b>';
				echo '		<br /><i>'.$row2["position"].'</i>';
				echo '		<br />';
				if ($row2["phone"]!="") echo '		<br />Tel.: '.$row2["phone"];
				if ($row2["fax"]!="") echo '		<br />Fax: '.$row2["fax"];
				if ($row2["mobile"]!="") echo '		<br />Mobil: '.$row2["mobile"];
				if ($row2["mail"]!="") echo '		<br /><br />Mail: <a href="mailto:'.$row2["mail"].'">'.$row2["mail"].'</a>';
				echo '	</td>';
				echo '</tr>';
			}
			echo '</table>';
		}
	}


	function show_person($id_employee)
	{
		global $dbweb;
		
		$results=q("SELECT * FROM cms_contacts WHERE id_contact='".$id_employee."' AND active=1 AND published=1;", $dbweb, __FILE__, __LINE__);
		if(mysqli_num_rows($results)>0)
		{
			echo '<table class="hover">';
			$row=mysqli_fetch_array($results);
			$results2=q("SELECT * FROM cms_contacts_locations AS a, cms_contacts_departments AS b WHERE b.id_department='".$row["department_id"]."' AND a.id_location=b.location_id ORDER BY b.ordering ;", $dbweb, __FILE__, __LINE__);
			$row2=mysqli_fetch_array($results2);
			echo '<tr><th colspan="3">'.$row2["department"].'</th></tr>';
			echo '<tr>';
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
					echo '	<td style="width:100px; vertical-align:top;"><img style="width:100px;" src="'.$src.'" /></td>';
				}
			}
			else echo '	<td style="width:100px; vertical-align:top;"><img style="width:100px;" src="'.PATH.'images/employees/0employee.jpg" /></td>';
			echo '	<td style="vertical-align:top;">';
			echo '		<b>'.$row["firstname"].' '.$row["middlename"].' '.$row["lastname"].'</b>';
			echo '		<br /><i>'.$row["position"].'</i>';
			echo '		<br />';
			if ($row["phone"]!="") echo '		<br />Tel.: '.$row["phone"];
			if ($row["fax"]!="") echo '		<br />Tel.: '.$row["phone"];
			if ($row["mobile"]!="") echo '		<br />Tel.: '.$row["phone"];
			if ($row["mail"]!="") echo '		<br /><br />Mail: <a href="mailto:'.$row["mail"].'">'.$row["mail"].'</a>';
			echo '	</td>';
			echo '</tr>';
			echo '</table>';
		}
	}


	echo '<div id="mid_column">';
	
	if($_SESSION["id_site"]==1)
	{
		//link menu	
		if ( strpos($_GET["url"], "inland") !== false ) $style=' style="font-weight:bold;"'; else $style='';
		echo '<a'.$style.' class="formbutton" href="'.PATHLANG.'kontakt-inland/">Inland</a>';
		echo '&nbsp; &nbsp;';
	
		if ( strpos($_GET["url"], "export") !== false ) $style=' style="font-weight:bold;"'; else $style='';
		echo '<a'.$style.' class="formbutton" href="'.PATHLANG.'kontakt-export/">Export</a>';
		echo '&nbsp; &nbsp;';
	
		if ( strpos($_GET["url"], "online") !== false ) $style=' style="font-weight:bold;"'; else $style='';
		echo '<a'.$style.' class="formbutton" href="'.PATHLANG.'kontakt-online/">Online-Handel</a>';
		echo '<br style="clear:both;" />';
		echo '<br style="clear:both;" />';
	}

	//domestic market contacts
	if ( strpos($_GET["url"], "inland") !== false and $_SESSION["id_site"]==1)
	{
		show_department(4); //Vertrieb Deutschland - Verkaufsleitung
		show_department(33); //Vertrieb Deutschland - Außendienst
		show_department(14); //Vertrieb Deutschland - Innendienst
		show_department(8); //Vertrieb Export - Außendienst
		show_department(7); //Vertrieb Export - Innendienst
		show_department(29); //RegionalCENTER Berlin Außendienst
		show_department(30); //RegionalCENTER Berlin Innendienst
 		show_department(23); //RegionalCENTER Dresden Außendienst
 		show_department(24); //RegionalCENTER Dresden Innendienst
		show_department(31); //RegionalCENTER Essen Außendienst
		show_department(32); //RegionalCENTER Essen Innendienst
		show_department(27); //RegionalCENTER Frankfurt/Main Außendienst
		show_department(28); //RegionalCENTER Frankfurt/Main Innendienst
		show_department(19); //RegionalCENTER Leipzig Außendienst
		show_department(20); //RegionalCENTER Leipzig Innendienst
		show_department(25); //RegionalCENTER Magdeburg Außendienst
		show_department(26); //RegionalCENTER Magdeburg
		show_department(17); //RegionalCENTER Neubrandenburg Außendienst
		show_department(18); //RegionalCENTER Neubrandenburg Innendienst
		show_department(21); //RegionalCENTER Sömmerda Außendienst
		show_department(22); //RegionalCENTER Sömmerda
		show_department(36); //MAPCO Shop Brück
		show_department(2); //Product Management
	}
	
	//export contacts
	if ( strpos($_GET["url"], "export") !== false and $_SESSION["id_site"]==1 )
	{
		if ($_GET["lang"]=="pl")
		{
			show_person(89);
		}
		elseif ($_GET["lang"]=="fr")
		{
			show_department(34); //RegionalCENTER Lyon
		}
		show_department(8); //Vertrieb Export - Außendienst
		show_department(7); //Vertrieb Export - Innendienst
	}
	
	//online contacts
	if ( strpos($_GET["url"], "online") !== false and $_SESSION["id_site"]==1 )
	{
		show_department(1); //Onlinehandel
	}

	//site contacts
	if ( strpos($_GET["url"], "kontakt/") !== false )
	{
		$results=q("SELECT * FROM cms_contacts_locations WHERE site_id=".$_SESSION["id_site"]." ORDER BY ordering ;", $dbweb, __FILE__, __LINE__);
		while($row=mysqli_fetch_array($results))
		{		
			$results2=q("SELECT * FROM cms_contacts_departments WHERE location_id=".$row["id_location"]." ORDER BY ordering ;", $dbweb, __FILE__, __LINE__);
			while($row2=mysqli_fetch_array($results2))
			{		
				show_department($row2["id_department"]);
			}
		}
	}


	echo '</div>';

	include("templates/".TEMPLATE."/cms_rightcolumn.php");
	include("templates/".TEMPLATE."/footer.php");
?>
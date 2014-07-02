<?php
	include("config.php");
	include("templates/".TEMPLATE."/header.php");
	include("templates/".TEMPLATE."/cms_leftcolumn.php");

	function show_department($department)
	{
		global $dbweb;
		
		echo '<table class="hover">';
		echo '<tr><th colspan="3">'.$department.'</th></tr>';
		$results=q("SELECT * FROM hr_employees WHERE department='".$department."' ORDER BY ordering;", $dbweb, __FILE__, __LINE__);
		while($row=mysqli_fetch_array($results))
		{
			echo '<tr>';
			if (file_exists('images/employees/'.substr($row["mail"], 0, strpos($row["mail"], "@")).'.jpg'))
				echo '	<td style="width:100px; vertical-align:top;"><img style="width:100px;" src="'.PATH.'images/employees/'.substr($row["mail"], 0, strpos($row["mail"], "@")).'.jpg" /></td>';
			else
				echo '	<td style="width:100px; vertical-align:top;"><img style="width:100px;" src="'.PATH.'images/employees/0employee.jpg" /></td>';
echo '	<td style="vertical-align:top;">';
			echo '		<b>'.$row["firstname"].' '.$row["middlename"].' '.$row["lastname"].'</b>';
			echo '		<br /><i>'.$row["position"].'</i>';
			echo '		<br />';
			if ($row["phone"]!="") echo '		<br />Tel.: '.$row["phone"];
			if ($row["fax"]!="") echo '		<br />Fax: '.$row["fax"];
			if ($row["mobile"]!="") echo '		<br />Mobil: '.$row["mobile"];
			if ($row["mail"]!="") echo '		<br /><br />Mail: <a href="mailto:'.$row["mail"].'">'.$row["mail"].'</a>';
			echo '	</td>';
			echo '</tr>';
		}
		echo '</table>';
	}


	function show_person($id_employee)
	{
		global $dbweb;
		
		echo '<table class="hover">';
		$results=q("SELECT * FROM hr_employees WHERE id_employee=".$id_employee.";", $dbweb, __FILE__, __LINE__);
		$row=mysqli_fetch_array($results);
		echo '<tr><th colspan="3">'.$row["department"].'</th></tr>';
		echo '<tr>';
		if (file_exists('images/employees/'.substr($row["mail"], 0, strpos($row["mail"], "@")).'.jpg'))
			echo '	<td style="width:100px; vertical-align:top;"><img style="width:100px;" src="'.PATH.'images/employees/'.substr($row["mail"], 0, strpos($row["mail"], "@")).'.jpg" /></td>';
		else
			echo '	<td style="width:100px; vertical-align:top;"><img style="width:100px;" src="'.PATH.'images/employees/_keinfoto.jpg" /></td>';
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


	echo '<div id="mid_column">';
	
	if ($_GET["lang"]=="de")
	{
		show_department("Vertrieb Deutschland - Verkaufsleitung");
		show_department("Vertrieb Deutschland - Außendienst");
		show_department("Vertrieb Deutschland - Innendienst");
		show_department("Onlinehandel");
		show_department("Vertrieb Export - Außendienst");
		show_department("Vertrieb Export - Innendienst");
		show_department("RegionalCENTER Berlin");
		show_department("RegionalCENTER Dresden");
		show_department("RegionalCENTER Essen");
		show_department("RegionalCENTER Frankfurt/Main");
		show_department("RegionalCENTER Leipzig");
		show_department("RegionalCENTER Magdeburg");
		show_department("RegionalCENTER Neubrandenburg");
		show_department("RegionalCENTER Sömmerda");
		show_department("MAPCO Shop Brück");
		show_department("Product Management");
	}
	elseif ($_GET["lang"]=="pl")
	{
		show_person(1234672);
		show_department("Vertrieb Export - Außendienst");
		show_department("Vertrieb Export - Innendienst");
	}
	elseif ($_GET["lang"]=="fr")
	{
		show_department("RegionalCENTER Lyon");
		show_department("Vertrieb Export - Außendienst");
		show_department("Vertrieb Export - Innendienst");
	}
	else
	{
		show_department("Vertrieb Export - Außendienst");
		show_department("Vertrieb Export - Innendienst");
	}
	
	echo '</div>';

	include("templates/".TEMPLATE."/cms_rightcolumn.php");
	include("templates/".TEMPLATE."/footer.php");
?>
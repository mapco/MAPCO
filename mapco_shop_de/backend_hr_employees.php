<?php
	include("config.php");
	include("templates/".TEMPLATE_BACKEND."/header.php");

	//PATH
	echo '<p>';
	echo '<a href="backend_index.php">Backend</a>';
	echo ' > <a href="backend_hr_index.php">Personalverwaltung</a>';
	echo ' > Mitarbeiter';
	echo '</p>';


	//REMOVE
	if (isset($_POST["remove"]))
    {
		if ($_POST["id_employee"]<=0) echo '<div class="failure">Es konnte keine ID für den Mitarbeiter gefunden werden!</div>';
		else
		{
			q("DELETE FROM hr_employees WHERE id_employee=".$_POST["id_employee"]." LIMIT 1;", $dbweb, __FILE__, __LINE__);
			echo '<div class="success">Mitarbeiter '.$_POST["id_employee"].' erfolgreich gelöscht!</div>';
		}
	}

	//LIST
	echo '<h1>';
	echo '&nbsp;<span style="display:inline; float:left;">Alle Mitarbeiter</span>';
	echo '<a href="backend_hr_employee_editor.php" title="Neuen Mitarbeiter anlegen"><img src="images/icons/24x24/user_add.png" alt="Neuen Mitarbeiter anlegen" title="Neuen Mitarbeiter anlegen" /></a>';
	echo '<a style="float:right;" href="backend_hr_employees_export.php" title="'.t("Mitarbeiter exportieren").'"><img src="images/icons/24x24/archive.png" alt="'.t("Mitarbeiter exportieren").'" title="'.t("Mitarbeiter exportieren").'" /></a>';
	echo '</h1>';
	echo '<p>In der nachfolgenden Liste, finden Sie alle derzeit im System abgelegten Mitarbeiter.</p>';
	$department='';
	$results=q("SELECT * FROM hr_employees ORDER BY department, lastname;", $dbweb, __FILE__, __LINE__);
	while ($row=mysqli_fetch_array($results))
	{
		if ($department!=$row["department"])
		{
			if ($department!="") echo '</table>';
			$department=$row["department"];
			echo '<h3>'.$department.'</h3>';
			echo '<table style="width:600px;" class="hover">';
			echo '	<tr>';
			echo '		<th style="width:50px;">Foto</th>';
			echo '		<th style="width:200px;">Name</th>';
			echo '		<th style="width:300px;">Position</th>';
			echo '		<th style="width:100px;">Optionen</th>';
			echo '	</tr>';
		}
		echo '<tr>';
		$file="images/employees/".substr($row["mail"], 0, strpos($row["mail"], "@")).".jpg";
		if (file_exists($file))	echo '<td><img style="width:50px;" src="'.$file.'?'.rand(0, 999999).'" /></td>';
		else echo '<td><img style="width:50px;" src="images/employees/0employee.jpg" /></td>';
		echo '	<td>'.$row["lastname"].', '.$row["firstname"].' '.$row["middlename"].'</td>';
		echo '	<td>'.$row["position"].'</td>';
		echo '	<td>';
		echo '<form style="margin:0; border:0; padding:0; float:right;" method="post">';
		echo '	<input type="hidden" name="id_employee" value="'.$row["id_employee"].'" />';
		echo '	<input type="hidden" name="remove" value="Mitarbeiter löschen" />';
		echo '	<input style="margin:2px 8px 2px 0px; border:0; padding:0; float:right;" type="image" src="images/icons/24x24/user_remove.png" alt="Mitarbeiter löschen" title="Mitarbeiter löschen" onclick="return confirm(\'Mitarbeiter wirklich löschen?\');" />';
		echo '</form>';
		echo '		<a href="backend_hr_employee_editor.php?id_employee='.$row["id_employee"].'" title="Mitarbeiter bearbeiten"><img src="images/icons/24x24/user.png" alt="Mitarbeiter bearbeiten" title="Mitarbeiter bearbeiten" /></a>';
		echo '	</td>';
		echo '</tr>';
	}
	echo '</table>';
	
	include("templates/".TEMPLATE_BACKEND."/footer.php");
?>
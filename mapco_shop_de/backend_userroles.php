<?php
	include("config.php");
	$leftmenu=7;
	$columns="MR";
	include("templates/".TEMPLATE_BACKEND."/header.php");

	//Benutzerprofil löschen
	if ($_POST["form_button"]=="Benutzerprofil löschen")
    {
		if ($_POST["form_id_user"]<=0) echo '<div class="failure">Es konnte keine ID für das Benutzerprofil gefunden werden!</div>';
		else
		{
			q("DELETE FROM users WHERE id_user=".$_POST["form_id_user"]." LIMIT 1;", __FILE__, __LINE__);
			echo '<div class="success">Benutzerprofil erfolgreich gelöscht!</div>';
		}
	}

	echo '<h1>';
	echo '&nbsp;<span style="display:inline; float:left;">Benutzerrollen</span>';
	echo '<a href="backend_userrole_editor.php" title="Neues Benutzerprofil anlegen"><img src="images/icons/24x24/user_add.png" alt="Neues Benutzerprofil anlegen" title="Neues Benutzerprofil anlegen" /></a>';
	echo '</h1>';
	$results=q("SELECT * FROM cms_userroles ORDER BY userrole;", $dbweb, __FILE__, __LINE__);
	echo '<table class="hover">';
	echo '	<tr>';
	echo '		<th>ID</th>';
	echo '		<th>Benutzerrolle</th>';
	echo '		<th>Optionen</th>';
	echo '	</tr>';
	while ($row=mysqli_fetch_array($results))
	{
		echo '<tr>';
		echo '	<td>'.$row["id_userrole"].'</td>';
		echo '	<td>'.$row["userrole"].'</td>';
		echo '	<td>';
		echo '<form action="backend_users.php" style="margin:0; border:0; padding:0; float:right;" method="post">';
		echo '	<input type="hidden" name="form_id_userrole" value="'.$row["id_userrole"].'" />';
		echo '	<input type="hidden" name="form_button" value="Benutzerprofil löschen" />';
		echo '	<input style="margin:2px 8px 2px 0px; border:0; padding:0; float:right;" type="image" src="images/icons/24x24/user_remove.png" alt="Benutzerprofil löschen" title="Benutzerprofil löschen" onclick="return confirm(\'Benutzerprofil wirklich löschen?\');" />';
		echo '		<a href="backend_userrole_editor.php?id_userrole='.$row["id_userrole"].'" title="Benutzerprofil bearbeiten"><img src="images/icons/24x24/user.png" alt="Benutzerprofil bearbeiten" title="Benutzerprofil bearbeiten" /></a>';
		echo '	</td>';
		echo '</tr>';
	}
	echo '</table>';
	
	include("templates/".TEMPLATE_BACKEND."/footer.php");
?>
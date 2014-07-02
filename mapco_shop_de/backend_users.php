<?php
	include("config.php");
	include("templates/".TEMPLATE_BACKEND."/header.php");

	//Doppelte Benutzer finden
	$results=q("SELECT * FROM fa_user_login;", $dbshop, __FILE__, __LINE__);
	while($row=mysqli_fetch_array($results))
	{
		$kundnrs[$row["nic"]]=$row["nic"];
		$ids[$row["nic"]][sizeof($ids[$row["nic"]])]=$row["ID"];
	}
	
	echo '<table>';
	echo '<tr>';
	echo '<th>Nr.</th>';
	echo '<th>ID</th>';
	echo '<th>Kundennummer</th>';
	echo '</tr>';
	$j=0;
	foreach($kundnrs as $kundnr)
	{
		if (sizeof($ids[$kundnr])>1)
		{
			$j++;
			for($i=0; $i<sizeof($ids[$kundnr]); $i++)
			{
				echo '<tr><td>'.$j.'</td><td>'.$ids[$kundnr][$i].'</td><td>'.$kundnr.'</td></tr>';
			}
			echo '<tr><td colspan="3"><hr /></td></tr>';
		}
	}
	echo '</table>';
	exit();


	//Benutzerprofil löschen
	if ($_POST["form_button"]=="Benutzerprofil löschen")
    {
		if ($_POST["form_id_user"]<=0) echo '<div class="failure">Es konnte keine ID für das Benutzerprofil gefunden werden!</div>';
		else
		{
			q("DELETE FROM cms_users WHERE id_user=".$_POST["form_id_user"]." LIMIT 1;", $dbweb, __FILE__, __LINE__);
			echo '<div class="success">Benutzerprofil erfolgreich gelöscht!</div>';
		}
	}

	echo '<h1>';
	echo '&nbsp;<span style="display:inline; float:left;">Benutzerprofile</span>';
	echo '<a href="backend_user_editor.php" title="Neues Benutzerprofil anlegen"><img src="images/icons/24x24/user_add.png" alt="Neues Benutzerprofil anlegen" title="Neues Benutzerprofil anlegen" /></a>';
	echo '</h1>';
	echo '<p>In der nachfolgenden Liste, finden Sie alle derzeit im System registrierten Benutzer.</p>';
	$results=q("SELECT * FROM cms_users ORDER BY username;", $dbweb, __FILE__, __LINE__);
//	$foldername="";
	echo '<table class="hover">';
	echo '	<tr>';
	echo '		<th>Nr.</th>';
	echo '		<th>Benutzername</th>';
	echo '		<th>E-Mail</th>';
	echo '		<th>Optionen</th>';
	echo '	</tr>';
	$i=0;
	while ($row=mysqli_fetch_array($results))
	{
		$i++;
		echo '<tr>';
		echo '	<td>'.$i.'</td>';
		echo '	<td>'.$row["username"].'</td>';
		echo '	<td>'.$row["usermail"].'</td>';
		echo '	<td>';
		echo '<form action="backend_users.php" style="margin:0; border:0; padding:0; float:right;" method="post">';
		echo '	<input type="hidden" name="form_id_user" value="'.$row["id_user"].'" />';
		echo '	<input type="hidden" name="form_button" value="Benutzerprofil löschen" />';
		echo '	<input style="margin:2px 8px 2px 0px; border:0; padding:0; float:right;" type="image" src="images/icons/24x24/user_remove.png" alt="Benutzerprofil löschen" title="Benutzerprofil löschen" onclick="return confirm(\'Benutzerprofil wirklich löschen?\');" />';
		echo '		<a href="backend_user_editor.php?id_user='.$row["id_user"].'" title="Benutzerprofil bearbeiten"><img src="images/icons/24x24/user.png" alt="Benutzerprofil bearbeiten" title="Benutzerprofil bearbeiten" /></a>';
		echo '	</td>';
		echo '</tr>';
	}
	echo '</table>';
	
	include("templates/".TEMPLATE_BACKEND."/footer.php");
?>
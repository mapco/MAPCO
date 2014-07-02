<?php
	include("config.php");
	$leftmenu=true;
	$columns="MR";
	include("templates/".TEMPLATE_BACKEND."/header.php");
?>

<script language="javascript">
	function checkAll(name)
	{
		var state = document.getElementById("selectall_"+name).checked;
		var theForm = document.userroleform;
		for (i=0; i<theForm.elements.length; i++)
		{
			if (theForm.elements[i].name==name+'[]')
				theForm.elements[i].checked = state;
		}
	}
</script>

<?php
	//CREATE
	if (isset($_POST["create"]))
    {
		if ($_POST["form_userrole"]=="") echo '<div class="failure">Der Name darf nicht leer sein!</div>';
		else
        {
			q("INSERT INTO cms_userroles (userrole) VALUES('".addslashes(stripslashes($_POST["form_userrole"]))."');", $dbweb, __FILE__, __LINE__);
			$_GET["id_userrole"]=mysqli_insert_id($dbweb);
			echo '<div class="success">Benutzerrolle erfolgreich angelegt!</div>';
        }
	}
	//UPDATE
	if (isset($_POST["update"]))
    {
		if ($_GET["id_userrole"]<=0) echo '<div class="failure">Es konnte keine ID für den Benutzer gefunden werden!</div>';
		elseif ($_POST["form_userrole"]=="") echo '<div class="failure">Der Titel darf nicht leer sein!</div>';
		else
        {
			//Namen ändern
			q("UPDATE cms_userroles SET userrole='".$_POST["form_userrole"]."' WHERE id_userrole=".$_GET["id_userrole"].";", $dbweb, __FILE__, __LINE__);
			//Berechtigungen auslesen
			$oldrights=array();
			$results=q("SELECT * FROM cms_userroles_scripts WHERE userrole_id=".$_GET["id_userrole"].";", $dbweb, __FILE__, __LINE__);
			while($row=mysqli_fetch_array($results))
			{
				$oldrights[$row["script"]]=$row["script"];
			}
			$newrights=array();
			for ($i=0; $i<sizeof($_POST["backend_files"]); $i++) $newrights[$_POST["backend_files"][$i]]=$_POST["backend_files"][$i];
			for ($i=0; $i<sizeof($_POST["frontend_files"]); $i++) $newrights[$_POST["frontend_files"][$i]]=$_POST["frontend_files"][$i];
			
			//Berechtigungen anpassen
			foreach($newrights as $newright)
			{
				if (!isset($oldrights[$newright]))
				{
					q("INSERT INTO cms_userroles_scripts (userrole_id, script) VALUES(".$_GET["id_userrole"].", '".$newright."');", $dbweb, __FILE, __LINE__);
				}
			}
			foreach($oldrights as $oldright)
			{
				if (!isset($newrights[$oldright]))
				{
					q("DELETE FROM cms_userroles_scripts WHERE userrole_id=".$_GET["id_userrole"]." AND script='".$oldright."';", $dbweb, __FILE__, __LINE__);
				}
			}
			echo '<div class="success">Benutzerrolle erfolgreich aktualisiert!</div>';
        }
    }

	//READ
	if (isset($_GET["id_userrole"]))
	{
		$results=q("SELECT * FROM cms_userroles WHERE id_userrole=".$_GET["id_userrole"].";", $dbweb, __FILE__, __LINE__);
		$row=mysqli_fetch_array($results);
		$_POST["form_userrole"]=$row["userrole"];
		
		$oldrights=array();
		$results=q("SELECT * FROM cms_userroles_scripts WHERE userrole_id=".$_GET["id_userrole"].";", $dbweb, __FILE__, __LINE__);
		while($row=mysqli_fetch_array($results))
		{
			$oldrights[$row["script"]]=$row["script"];
		}
	}

	//PATH
	echo '<p>';
	echo '<a href="backend_index.php">Backend</a>';
	echo ' > <a href="backend_cms_index.php">Content Management</a>';
	echo ' > <a href="backend_cms_userroles.php">Benutzerrollen</a>';
	echo ' > Editor';
	echo '</p>';


    //EDITOR
	if (isset($_GET["id_userrole"]))
	{
		echo '<form name="userroleform" action="backend_cms_userrole_editor.php?id_userrole='.$_GET["id_userrole"].'" method="post">';
	}
	else
	{
		echo '<form name="userroleform" action="backend_cms_userrole_editor.php" method="post">';
	}
	//scan files
	$backend_files=array();
	$frontend_files=array();
	if ($handle = opendir('.'))
	{
		while (false !== ($file = readdir($handle)))
		{
			if (strpos($file, ".php")>0)
			{
				if (strpos($file, "backend_") === false) $frontend_files[]=$file;
				else $backend_files[]=$file;
			}
		}
		closedir($handle);
	}
	sort($backend_files);
	sort($frontend_files);
	
	//show backend files
	echo '<table style="margin:5px; float:left;">';
	echo '<tr>';
	echo '	<th><input id="selectall_backend_files" type="checkbox" title="Alle auswählen" onclick="checkAll(\'backend_files\')" /></th>';
	echo '	<th>Backend-Datei</th>';
	echo '</tr>';
	for($i=0; $i<sizeof($backend_files); $i++)
	{
		if (isset($oldrights[$backend_files[$i]])) $checked=' checked="checked"'; else $checked='';
		echo '<tr>';
		echo '	<td><input'.$checked.' type="checkbox" name="backend_files[]" value="'.$backend_files[$i].'" /></td>';
		echo '	<td>'.$backend_files[$i].'</td>';
		echo '</tr>';
	}
	echo '</table>';
	
	//show frontend files
	echo '<table style="margin:5px; float:left;">';
	echo '<tr>';
	echo '	<th><input id="selectall_frontend_files" type="checkbox" title="Alle auswählen" onclick="checkAll(\'frontend_files\')" /></th>';
	echo '	<th>Frontend-Datei</th>';
	echo '	<th>SEO-Link</th>';
	echo '</tr>';
	for($i=0; $i<sizeof($frontend_files); $i++)
	{
		if (isset($oldrights[$frontend_files[$i]])) $checked=' checked="checked"'; else $checked='';
		echo '<tr>';
		echo '	<td><input'.$checked.' type="checkbox" name="frontend_files[]" value="'.$frontend_files[$i].'" /></td>';
		echo '	<td>'.$frontend_files[$i].'</td>';
		$results=q("SELECT * FROM cms_menuitems WHERE link='".$frontend_files[$i]."';", $dbweb, __FILE__, __LINE__);
		if ( mysqli_num_rows($results)>0 )
		{
			$row=mysqli_fetch_array($results);
		}
		else $row["alias"]='KEIN ALIAS VORHANDEN!';
		echo '	<td>'.$row["alias"].'</td>';
		echo '</tr>';
	}
	echo '</table>';

	//show other options
	echo '<table style="margin:5px; float:left;">';
	echo '	<tr><th colspan="2">Benutzerrollen-Editor</th></tr>';
	echo '	<tr>';
	echo '		<td>Benutzerrolle</td>';
	echo '		<td>';
	echo '			<input size="40" type="text" name="form_userrole" value="'.$_POST["form_userrole"].'" />';
	if (isset($_GET["id_userrole"]))
	{
		echo '<input class="formbutton" type="submit" name="update" value="Benutzerrolle aktualisieren" />';
	}
	else
	{
		echo '<input class="formbutton" type="submit" name="create" value="Benutzerrolle anlegen" />';
	}
	echo '		</td>';
	echo '	</tr>';
	echo '</table>';
	
	echo '</form>';
	
	include("templates/".TEMPLATE_BACKEND."/footer.php");
?>
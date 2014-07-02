<?php
	include("config.php");
	include("templates/".TEMPLATE_BACKEND."/header.php");
?>

<script type="text/javascript">
	function view_on_enter(evt)
	{
		var e = evt || window.event;
		var code = e.keyCode || e.which;
		if( code == 13 ) view();
	}

	function user_switch($id_user)
	{
		wait_dialog_show();
		$.post("<?php echo PATH; ?>soa/", { API:"cms", Action:"UserSwitch", id_user:$id_user }, function($data)
		{
			wait_dialog_hide();
			try { $xml = $($.parseXML($data)); } catch ($err) { show_status($err.message); return; }
			$ack = $xml.find("Ack");
			if ( $ack.text()!="Success" ) { show_status2($data); return; }

			show_status("Benutzer erfolgreich gewechselt.");
		});
	}


	function user_edit($id_user)
	{
		wait_dialog_show();
		$.post("<?php echo PATH; ?>soa/", { API:"cms", Action:"UserGet", id_user:$id_user }, function($data)
		{
			wait_dialog_hide();
			try { $xml = $($.parseXML($data)); } catch ($err) { show_status($err.message); return; }
			$ack = $xml.find("Ack");
			if ( $ack.text()!="Success" ) { show_status2($data); return; }

			$html  = '';
			$html += '	<table>';
			var $username=$xml.find("username").text();
			$html += '		<tr>';
			$html += '			<td>Benutzername</td>';
			$html += '			<td><input id="user_edit_username" type="text" value="'+$username+'" /></td>';
			$html += '		</tr>';
			var $usermail=$xml.find("usermail").text();
			$html += '		<tr>';
			$html += '			<td>E-Mail-Adresse</td>';
			$html += '			<td><input id="user_edit_usermail" type="text" value="'+$usermail+'" /></td>';
			$html += '		</tr>';
			var $name=$xml.find("name").text();
			$html += '		<tr>';
			$html += '			<td>Name</td>';
			$html += '			<td><input id="user_edit_name" type="text" value="'+$name+'" /></td>';
			$html += '		</tr>';
			var $language_id=$xml.find("language_id").text();
			$html += '		<tr>';
			$html += '			<td>Sprache</td>';
			$html += '			<td><input id="user_edit_language_id" type="text" value="'+$language_id+'" /></td>';
			$html += '		</tr>';
			var $origin=$xml.find("origin").text();
			$html += '		<tr>';
			$html += '			<td>Herkunft</td>';
			$html += '			<td><input id="user_edit_origin" type="text" value="'+$origin+'" /></td>';
			$html += '		</tr>';
			$html += '	</table>';
			if( $("#user_edit_dialog").length==0 ) $("body").append('<div id="user_edit_dialog"></div>');
			$("#user_edit_dialog").html($html);

			$("#user_edit_dialog").dialog
			({	buttons:
				[
					{ text: "Speichern", click: function() { user_edit_save(); } },
					{ text: "Abbrechen", click: function() { $(this).dialog("close"); } }
				],
				closeText:"Fenster schließen",
				modal:true,
				resizable:false,
				title:"Benutzerdetails bearbeiten",
				width:400
			});
		});
	}


	function user_edit_save()
	{
		alert("Nocht nicht verfügbar!");
	}


	function user_sites_edit($id_user)
	{
		wait_dialog_show();
		$.post("<?php echo PATH; ?>soa/", { API:"cms", Action:"UserGet", id_user:$id_user }, function($data)
		{
			try { $xml = $($.parseXML($data)); } catch ($err) { show_status($err.message); return; }
			$ack = $xml.find("Ack");
			if ( $ack.text()!="Success" ) { show_status2($data); return; }

			var $sites=new Array();
			$xml.find("Site").each(function($data)
			{
				$sites[$(this).text()]=$(this).text();
			});

			$.post("<?php echo PATH; ?>soa/", { API:"cms", Action:"SitesGet" }, function($data)
			{
				wait_dialog_hide();
				try { $xml = $($.parseXML($data)); } catch ($err) { show_status($err.message); return; }
				$ack = $xml.find("Ack");
				if ( $ack.text()!="Success" ) { show_status2($data); return; }
	
				$html  = '';
				$html += '	<table>';
				$html += '		<tr>';
				$html += '			<th><input id="user_sites_edit_checkall" type="checkbox" onclick="user_sites_edit_checkall();" /></th>';
				$html += '			<th>ID</th>';
				$html += '			<th>Titel</th>';
				$html += '			<th>domain</th>';
				$html += '		</tr>';
				$xml.find("Site").each(function()
				{			
					var $id_site=$(this).find("id_site").text();
					var $title=$(this).find("title").text();
					var $description=$(this).find("description").text();
					var $domain=$(this).find("domain").text();
					var $checked='';
					if( typeof $sites[$id_site] != "undefined" ) $checked=' checked="checked"';
					$html += '		<tr>';
					$html += '			<td><input'+$checked+' name="user_sites_edit_site_id" type="checkbox" value="'+$id_site+'" /></td>';
					$html += '			<td>'+$id_site+'</td>';
					$html += '			<td>'+$title+'<br /><i>'+$description+'</i></td>';
					$html += '			<td>'+$domain+'</td>';
					$html += '		</tr>';
				});
				$html += '	</table>';
				$html += '	<input id="user_sites_edit_user_id" type="hidden" value="'+$id_user+'" />';
				if( $("#user_sites_edit_dialog").length==0 ) $("body").append('<div id="user_sites_edit_dialog"></div>');
				$("#user_sites_edit_dialog").html($html);

				$("#user_sites_edit_dialog").dialog
				({	buttons:
					[
						{ text: "Speichern", click: function() { user_sites_edit_save(); } },
						{ text: "Abbrechen", click: function() { $(this).dialog("close"); } }
					],
					closeText:"Fenster schließen",
					modal:true,
					resizable:false,
					title:"Benutzerseiten bearbeiten",
					width:600
				});
			});
		});
	}


	function user_sites_edit_checkall()
	{
		var $checkall=$('#user_sites_edit_checkall').prop('checked');
		$('[name=user_sites_edit_site_id]').prop('checked', $checkall);
	}


	function user_sites_edit_save()
	{
		var $id_user=$("#user_sites_edit_user_id").val();
		var $sites="";
		$('[name=user_sites_edit_site_id]:checked').each(function()
		{
			if($sites!="") $sites+=', ';
			$sites+=$(this).val();
		});
		wait_dialog_show();
		$.post("<?php echo PATH; ?>soa/", { API:"cms", Action:"UserSitesUpdate", id_user:$id_user, sites:$sites }, function($data)
		{
			wait_dialog_hide();
			try { $xml = $($.parseXML($data)); } catch ($err) { show_status2($err.message); return; }
			$ack = $xml.find("Ack");
			if ( $ack.text()!="Success" ) { show_status2($data); return; }
			
			show_status("Seiten für Benutzer erfolgreich aktualisiert.");
			$("#user_sites_edit_dialog").dialog("close");
		});
	}


	function view()
	{
		var $search=$("#view_search").val();
		if( typeof $search == "undefined" ) $search='';
		wait_dialog_show();
		$.post("<?php echo PATH; ?>soa/", { API:"cms", Action:"UsersGet", search:$search }, function($data)
		{
			wait_dialog_hide();
			try { $xml = $($.parseXML($data)); } catch ($err) { show_status2($err.message); return; }
			$ack = $xml.find("Ack");
			if ( $ack.text()!="Success" ) { show_status2($data); return; }

			$html  = '';
			$html += '<input id="view_search" onkeyup="view_on_enter();" type="text" value="'+$search+'" />';
			$html += '<input type="button" onclick="view();" value="Suchen" />';
			$html += '<table>';
			$html += '	<tr>';
			$html += '		<th>Nr.</th>';
			$html += '		<th>ID</th>';
			$html += '		<th>Benutzername</th>';
			$html += '		<th>E-Mail</th>';
			$html += '		<th>Name</th>';
			$html += '		<th>Letzter Login</th>';
			$html += '		<th>Optionen</th>';
			$html += '	</tr>';
			$nr=0;
			$xml.find("User").each(function()
			{
				$nr++;
				$html += '	<tr>';
				$html += '		<td>'+$nr+'</td>';
				var $id_user=$(this).find("id_user").text();
				$html += '		<td>'+$id_user+'</td>';
				$html += '		<td>'+$(this).find("username").text()+'</td>';
				$html += '		<td>'+$(this).find("usermail").text()+'</td>';
				$html += '		<td>'+$(this).find("name").text()+'</td>';
				var $timestamp=Number($(this).find("lastlogin").text())*1000;
				var $lastlogin=new Date($timestamp);
				$html += '		<td>'+$lastlogin.toLocaleString()+'</td>';
				$html += '		<td>';
				$html += '			<img alt="Als Benutzer einloggen" src="<?php echo PATH; ?>images/icons/24x24/user_accept.png" style="cursor:pointer;" title="Als Benutzer einloggen" onclick="user_switch('+$id_user+');" />';
				$html += '			<img alt="Benutzerdetails bearbeiten" src="<?php echo PATH; ?>images/icons/24x24/user.png" style="cursor:pointer;" title="Benutzerdetails bearbeiten" onclick="user_edit('+$id_user+');" />';
				$html += '			<img alt="Benutzerseiten bearbeiten" src="<?php echo PATH; ?>images/icons/24x24/users.png" style="cursor:pointer;" title="Benutzerseiten bearbeiten" onclick="user_sites_edit('+$id_user+');" />';
				$html += '		</td>';
				$html += '	</tr>';
			});
			$html += '</table>';
			$("#view").html($html);
		});
	}
	$( document ).ready(function() { view(); });
</script>

<?php
/*
	//REMOVE
	if ($_POST["remove"])
    {
		if ($_POST["id_user"]<=0) echo '<div class="failure">Es konnte keine ID für das Benutzerprofil gefunden werden!</div>';
		else
		{
			q("DELETE FROM cms_users WHERE id_user=".$_POST["id_user"]." LIMIT 1;", $dbweb, __FILE__, __LINE__);
			echo '<div class="success">Benutzerprofil erfolgreich gelöscht!</div>';
		}
	}
*/

	//PATH
	echo '<p>';
	echo '<a href="backend_index.php">Backend</a>';
	echo ' > <a href="backend_cms_index.php">Content Management</a>';
	echo ' > Benutzer';
	echo '</p>';
	echo '<h1>Benutzer</h1>';

	echo '<div id="view"></div>';

/*
	//LIST
	echo '<h1>';
	echo '&nbsp;<span style="display:inline; float:left;">Benutzerprofile</span>';
	echo '<a href="backend_cms_user_editor.php" title="Neues Benutzerprofil anlegen"><img src="images/icons/24x24/user_add.png" alt="Neues Benutzerprofil anlegen" title="Neues Benutzerprofil anlegen" /></a>';
	echo '</h1>';
	
	echo '<form>';
	echo '	<input type="text" name="search" value="'.$_GET["search"].'" />';
	echo '	<input class="formbutton" type="submit" value="Suchen" />';
	echo '</form>';
	
	if ($_GET["search"]!="")
	{
		$results=q("SELECT * FROM cms_users WHERE username LIKE '%".$_GET["search"]."%' OR usermail LIKE '%".$_GET["search"]."%' ORDER BY username;", $dbweb, __FILE__, __LINE__);
		if (mysqli_num_rows($results)==0) echo '<p>Die Suche ergab keine Treffer.</p>';
		else
		{
			echo '<p>Die Suche ergab '.mysqli_num_rows($results).' Treffer.</p>';
			$userroles=array();
			$results2=q("SELECT * FROM cms_userroles;", $dbweb, __FILE__, __LINE__);
			while ($row2=mysqli_fetch_array($results2))
			{
				$userroles[$row2["id_userrole"]]=$row2["userrole"];
			}
			echo '<table class="hover">';
			echo '	<tr>';
			echo '		<th>Benutzername</th>';
			echo '		<th>E-Mail</th>';
			echo '		<th>Benutzerrechte</th>';
			echo '		<th>Optionen</th>';
			echo '	</tr>';
			while ($row=mysqli_fetch_array($results))
			{
				echo '<tr>';
				echo '	<td>'.$row["username"].'</td>';
				echo '	<td>'.$row["usermail"].'</td>';
				echo '	<td>'.$userroles[$row["userrole_id"]].'</td>';
				echo '	<td>';
				echo '<form action="backend_cms_users.php" style="margin:0; border:0; padding:0; float:right;" method="post">';
				echo '	<input type="hidden" name="id_user" value="'.$row["id_user"].'" />';
				echo '	<input type="hidden" name="remove" value="Benutzerprofil löschen" />';
				echo '	<input style="margin:2px 8px 2px 0px; border:0; padding:0; float:right;" type="image" src="images/icons/24x24/user_remove.png" alt="Benutzerprofil löschen" title="Benutzerprofil löschen" onclick="return confirm(\'Benutzerprofil wirklich löschen?\');" />';
				echo '		<a href="backend_cms_user_editor.php?id_user='.$row["id_user"].'" title="Benutzerprofil bearbeiten"><img src="images/icons/24x24/user.png" alt="Benutzerprofil bearbeiten" title="Benutzerprofil bearbeiten" /></a>';
				echo '	</td>';
				echo '</tr>';
			}
			echo '</table>';
		}
	}
*/
	
	include("templates/".TEMPLATE_BACKEND."/footer.php");
?>
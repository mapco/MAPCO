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
		$.post("<?php echo PATH; ?>soa2/", { API:"cms", APIRequest:"UserGet_neu", id_user:$id_user }, function($data)
		{
			//show_status2($data); return;
			try { $xml = $($.parseXML($data)); } catch ($err) { show_status($err.message); return; }
			$ack = $xml.find("Ack");
			if ( $ack.text()!="Success" ) { show_status2($data); return; }

			$html  = '<div id="user_edit_error" style="color:red;"></div>';
			$html += '	<table>';
			var $username=$xml.find("username").text();
			$html += '		<tr>';
			$html += '			<td>Benutzername</td>';
			$html += '			<td><input id="user_edit_username" type="text" value="'+$username+'" /></td>';
			$html += '		</tr>';
		//	$html += '		<tr>';
			//$html += '			<td>IDIMS Benutzer ID</td>';
			//$html += '			<td><input id="user_edit_idims_user_id" type="text" value="'+$(this).find("idims_user_id").text()+'" /></td>';
		//	$html += '		</tr>';
			$html += '		<tr>';

			var $userrole = $xml.find('userrole_id').text();
			var $userrole_id = 0;
			$html += '			<td>Benutzerrolle</td>';
			$html += '			<td><select id="user_edit_userrole">';
			$xml.find("userrole").each(function(){
				$userrole_id =$(this).find("user_role_id").text();
				$html +=			'<option value="'+$(this).find("user_role_id").text()+'"';
				if( $userrole == $userrole_id ){ $html += ' selected="selected"'; }
				$html += '>'+$(this).find("user_role_title").text()+'</option>';	
			});			
			$html += '			</select></td>';
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
			$html += '		<tr>';
			$html += '			<td>Sprache</td>';
			$html += '			<td><select id="user_edit_language">';
			
			var $language = $xml.find('user_language').text();
			var $language_id = 0;
			$xml.find('language').each(function(){
				$language_id = $(this).find("language_id").text();
				$html +=			'<option value="'+$language_id+'"';
				if( $language == $language_id ){ $html += ' selected="selected"'; }
				$html += '>'+$(this).find("language_title").text()+'</option>';	
			});
			$html += '			</select></td>';
			$html += '		</tr>';
			$html += '		<tr>';
			$html += '			<td>Land</td>';
			$html += '			<td><select id="user_edit_country">';
			var $origin = $xml.find('origin').text();
			var $country = 0;
			$xml.find("country").each(function(){
				$html +=			'<option value="'+$(this).find("country_code").text()+'"';
				if( $(this).find("country_code").text() == $origin ){ $html += ' selected="selected"'; }
				$html += '>'+$(this).find("country_title").text()+'</option>';	
			});			
			$html += '			</select></td>';
			$html += '		</tr>';
			$html += '	</table>';
			if( $("#user_edit_dialog").length==0 ) $("body").append('<div id="user_edit_dialog"></div>');
			$("#user_edit_dialog").html($html);

			$("#user_edit_dialog").dialog
			({	buttons:
				[
					{ text: "Speichern", click: function() { user_edit_save($id_user); } },
					{ text: "Schließen", click: function() { $(this).dialog("close"); } }
				],
				closeText:"Fenster schließen",
				modal:true,
				resizable:false,
				title:"Benutzerdetails bearbeiten",
				width:400
			});
			wait_dialog_hide();
		});
	}


	function user_edit_save($id_user)
	{
		var $postdata = new Object();
		$postdata['API'] = 'cms';
		$postdata['APIRequest'] = 'UserEdit';
		$postdata['id_user'] = $id_user;
		$postdata['username'] = $("#user_edit_username").val();
		$postdata['usermail'] = $("#user_edit_usermail").val();
		$postdata['name'] = $("#user_edit_name").val();
		$postdata['userrole'] = $("#user_edit_userrole").val();
		$postdata['language'] = $("#user_edit_language").val();
		$postdata['country'] = $("#user_edit_country").val();
		//show_status2(print_r($postdata)); return;
		wait_dialog_show();
		$.post("<?php echo PATH; ?>soa2/",$postdata, function($data){
			//show_status2($data); return;
			try { $xml = $($.parseXML($data)); } catch ($err) { show_status($err.message); return; }
			$ack = $xml.find("Ack");
			if ( $ack.text()!="Success" ) { show_status2($data); return; }
			
			var status_name = $xml.find('status_name').text();
			var status_mail = $xml.find('status_mail').text();
					
			var error = '';
			if ( status_name == 2 )
			{
				error = '<div>Dieser Name ist bereits vergeben!</div>';
			}
			
			if ( status_mail == 2 )
			{
				error = '<div>Diese Mail ist bereits vergeben!</div>';
			}
			
			$("#user_edit_error").append(error);
			
			$("#user_edit_dialog").dialog("close");
			view();
		})
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
		$postdata=new Object();
		$postdata["API"]="cms";
		$postdata["APIRequest"]="UsersGet_neu";
		$postdata["search"]=$search;
		$postdata["limit"]=100;
		$.post("<?php echo PATH; ?>soa2/", $postdata, function($data)
		{
			wait_dialog_hide();
			//show_status2($data); return;
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
			$html += '		<th>Sperrung</th>';
			$html += '		<th>Benutzerrolle</th>';
			$html += '		<th>Benutzername</th>';
			$html += '		<th>E-Mail</th>';
			$html += '		<th>Name</th>';
			$html += '		<th>Sprache</th>';
			$html += '		<th>Land</th>';
			$html += '		<th>Usertoken</th>';
			$html += '		<th>Letzter Login</th>';
			$html += '		<th>Optionen</th>';
			$html += '	</tr>';
			$nr=0;
			$xml.find("User").each(function()
			{
				$is_active = $(this).find("active").text();
				if ( $is_active == 1 )
				{
					$active_image = 'lock_off.png';
				}
				else
				{
					$active_image = 'lock_disabled.png';
				}
				
				$nr++;
				$html += '	<tr>';
				$html += '		<td>'+$nr+'</td>';
				var $id_user=$(this).find("id_user").text();
				$html += '		<td>'+$id_user+'</td>';
				$html += '		<td><img src="<?php echo PATH; ?>images/icons/24x24/'+$active_image+'" onclick="Javascript:user_set_active('+$id_user+','+$is_active+')" style="cursor:pointer;"></td>';
				$html += '		<td>'+$(this).find("userrole").text()+'</td>';
				$html += '		<td>'+$(this).find("username").text()+'</td>';
				$html += '		<td>'+$(this).find("usermail").text()+'</td>';
				$html += '		<td>'+$(this).find("name").text()+'</td>';
				$html += '		<td>'+$(this).find("language").text()+'</td>';
				$html += '		<td>'+$(this).find("country").text()+'</td>';
				$html += '		<td>'+$(this).find("user_token").text()+'</td>';
				
				$lastlogin = $(this).find("lastlogin").text();
				if ( $lastlogin == 0 )
				{
					$lastlogin = 'noch nie';	
				}
				else
				{
					var $timestamp=Number($lastlogin)*1000;
					$lastlogin=new Date($timestamp);
				}
				
				$html += '		<td>'+$lastlogin.toLocaleString()+'</td>';
				$html += '		<td>';
				$html += '			<img alt="Als Benutzer einloggen" src="<?php echo PATH; ?>images/icons/24x24/user_accept.png" style="cursor:pointer;" title="Als Benutzer einloggen" onclick="user_switch('+$id_user+');" />';
				$html += '			<img alt="Benutzerdetails bearbeiten" src="<?php echo PATH; ?>images/icons/24x24/user.png" style="cursor:pointer;" title="Benutzerdetails bearbeiten" onclick="user_edit('+$id_user+');" />';
				if ( $(this).find("is_gewerbe").text() == 0 )
				{
					$html += '			<img alt="Benutzerpasswort ändern" src="<?php echo PATH; ?>images/icons/24x24/process.png" style="cursor:pointer;" title="Passwort ändern" onclick="dialog_user_password_edit('+$id_user+');" />';
				}
				$html += '			<img alt="Benutzerseiten bearbeiten" src="<?php echo PATH; ?>images/icons/24x24/users.png" style="cursor:pointer;" title="Benutzerseiten bearbeiten" onclick="user_sites_edit('+$id_user+');" />';
				$html += '		</td>';
				$html += '	</tr>';
			});
			$html += '</table>';
			$("#view").html($html);
		});
	}
	
	function user_set_active(id_user, is_active)
	{
		wait_dialog_show();
		$.post("<?php echo PATH; ?>soa2/", { API:'cms', APIRequest:'UserActiveSet', id_user:id_user, is_active:is_active }, function($data)
		{
			//show_status2($data); return;
			try { $xml = $($.parseXML($data)); } catch ($err) { show_status2($err.message); return; }
			$ack = $xml.find("Ack");
			if ( $ack.text()!="Success" ) { show_status2($data); return; }
			
			wait_dialog_hide();
			view();
		});
	}
	
	function dialog_user_password_edit(id_user)
	{
		if ($("#dialog_user_password_edit").length == 0)
		{
			var dialog_div = $('<div id="dialog_user_password_edit"></div>');
			$("#content").append(dialog_div);
		}
		$("#dialog_user_password_edit").empty();
		
		var dialog_content = '<input type="password" id="input_user_password_edit" />';
		
		$("#dialog_user_password_edit").append(dialog_content);
		
		$("#dialog_user_password_edit").dialog({	
			buttons:
			[
				{ text: "<?php echo t("OK"); ?>", click: function() { user_password_edit(id_user); } },
				{ text: "<?php echo t("Abbrechen"); ?>", click: function() {$(this).dialog("close");} }
			],
			closeText:"<?php echo t("Fenster schließen"); ?>",
			hide: { effect: 'drop', direction: "up" },
			modal:true,
			resizable:false,
			show: { effect: 'drop', direction: "up" },
			title:"<?php echo t("Neues Passwort festlegen"); ?>",
			width:300
		});	
	}
	
	function user_password_edit(id_user)
	{
		$new_password = $("#input_user_password_edit").val(); //prompt("Neues Passwort festlegen", "");

		if ( $new_password !== null )
		{
			$where = 'WHERE id_user='+id_user;
			wait_dialog_show();
			$.post("<?php echo PATH; ?>soa2/", { API:'cms', APIRequest:'TableDataSelect', table:'cms_users', db:'dbweb', select:'user_salt, password', where:$where }, function($data)
			{
				//show_status2($data); return;
				try { $xml = $($.parseXML($data)); } catch ($err) { show_status2($err.message); return; }
				$ack = $xml.find("Ack");
				if ( $ack.text()!="Success" ) { show_status2($data); return; }
				
				$new_password = md5($new_password);
				$new_password += $xml.find('user_salt').text();
				$new_password = md5($new_password);
				$new_password += "<?php print PEPPER; ?>";
				$new_password = md5($new_password);
				
				
				$.post("<?php echo PATH; ?>soa2/", { API:'cms', APIRequest:'UserChangePassword', id_user:id_user, password:$new_password }, function($data)
				{
					//show_status2($data); return;
					try { $xml = $($.parseXML($data)); } catch ($err) { show_status2($err.message); return; }
					$ack = $xml.find("Ack");
					if ( $ack.text()!="Success" ) { show_status2($data); return; }
					alert($xml.find('message').text());
				});
				
				wait_dialog_hide();
			});
		}
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
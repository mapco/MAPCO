<?php
	if(isset($_GET["getvars1"])) $_GET["id_menu"]=$_GET["getvars1"];

	include("config.php");
	include("templates/".TEMPLATE_BACKEND."/header.php");
	
?>
<script type="text/javascript">
	function menu_add()
	{
		wait_dialog_show("Zeichne Menü-Editor", 100);
		var $html = '';
		$html += '<table class="hover" style="float:left;">';
		$html += '	<tr>';
		$html += '		<td>Seite</td>';
		$html += '		<td>';
		$html += '			<select id="menu_add_site_id">';
		$html += '				<option value="<?php echo $_SESSION["id_site"]; ?>">Aktuelle Seite</option>';
		$html += '				<option value="0">Global</option>';
		$html += '			</select>';
		$html += '		</td>';
		$html += '	</tr>';
		$html += '	<tr>';
		$html += '		<td>Titel</td>';
		$html += '		<td><input id="menu_add_title" style="width:300px;" type="text" /></td>';
		$html += '	</tr>';
		$html += '	<tr>';
		$html += '		<td>Beschreibung</td>';
		$html += '		<td><textarea id="menu_add_description" style="width:300px; height:100px;"></textarea></td>';
		$html += '	</tr>';
		$html += '	<tr>';
		$html += '		<td>Bezeichner</td>';
		$html += '		<td><input id="menu_add_idtag" style="width:300px;" type="text" /></td>';
		$html += '	</tr>';
		$html += '</table>';
		if( $("#menu_add_dialog").length == 0 ) $("body").append('<div id="menu_add_dialog" style="display:none;"></div>');
		$("#menu_add_dialog").html($html);
		$("#menu_add_dialog").dialog
		({	buttons:
			[
				{ text: "Hinzufügen", click: function() { menu_add_save(); } },
				{ text: "Abbrechen", click: function() { $(this).dialog("close"); } }
			],
			closeText:"Fenster schließen",
			modal:true,
			resizable:false,
			title:"Menü hinzufügen",
			width:450
		});
		wait_dialog_hide();		
		
	}


	function menu_add_save($xml)
	{
		if( typeof $xml === "undefined" )
		{
			wait_dialog_show("Speichere Menü", 100);
			var $postdata=new Object();
			$postdata["API"]="cms";
			$postdata["APIRequest"]="MenuAdd";
			$postdata["site_id"]=$("#menu_add_site_id").val();
			$postdata["title"]=$("#menu_add_title").val();
			$postdata["description"]=$("#menu_add_description").val();
			$postdata["idtag"]=$("#menu_add_idtag").val();
			soa2($postdata, "menu_add_save");
			return;
		}

		$("#menu_add_dialog").dialog("close");
		show_status("Menü erfolgreich hinzugefügt.");
		wait_dialog_hide();
		delete $cms_menus;
		view();
	}


	function menu_edit($i)
	{
		wait_dialog_show("Lese Menüs aus", 0);
		if(table_data_select("cms_menus", "*", "WHERE site_id IN(0, <?php echo $_SESSION["id_site"]; ?>) ORDER BY title;", "dbweb", "$cms_menus", "view")) return;
		wait_dialog_show("Zeichne Menü-Editor", 100);
		var $html = '';
		$html += '<table class="hover" style="float:left;">';
		$html += '	<tr>';
		$html += '		<td>Seite</td>';
		$html += '		<td>';
		$html += '			<select id="menu_edit_site_id">';
		$html += '				<option value="<?php echo $_SESSION["id_site"]; ?>">Aktuelle Seite</option>';
		$html += '				<option value="0">Global</option>';
		$html += '			</select>';
		$html += '		</td>';
		$html += '	</tr>';
		$html += '	<tr>';
		$html += '		<td>Titel</td>';
		$html += '		<td><input id="menu_edit_title" style="width:300px;" type="text" value="'+$cms_menus[$i]["title"]+'" /></td>';
		$html += '	</tr>';
		$html += '	<tr>';
		$html += '		<td>Beschreibung</td>';
		$html += '		<td><textarea id="menu_edit_description" style="width:300px; height:100px;">'+$cms_menus[$i]["description"]+'</textarea></td>';
		$html += '	</tr>';
		$html += '	<tr>';
		$html += '		<td>Bezeichner</td>';
		$html += '		<td><input id="menu_edit_idtag" style="width:300px;" type="text"  value="'+$cms_menus[$i]["idtag"]+'"/></td>';
		$html += '	</tr>';
		$html += '</table>';
		$html += '<input id="menu_edit_id_menu" type="hidden" value="'+$cms_menus[$i]["id_menu"]+'" />';
		if( $("#menu_edit_dialog").length == 0 ) $("body").append('<div id="menu_edit_dialog" style="display:none;"></div>');
		$("#menu_edit_dialog").html($html);
		$("#menu_edit_site_id").val($cms_menus[$i]["site_id"]);
		$("#menu_edit_dialog").dialog
		({	buttons:
			[
				{ text: "Speichern", click: function() { menu_edit_save(); } },
				{ text: "Abbrechen", click: function() { $(this).dialog("close"); } }
			],
			closeText:"Fenster schließen",
			modal:true,
			resizable:false,
			title:"Menü hinzufügen",
			width:450
		});
		wait_dialog_hide();		
	}


	function menu_edit_save($xml)
	{
		if( typeof $xml === "undefined" )
		{
			wait_dialog_show("Speichere Menü", 100);
			var $postdata=new Object();
			$postdata["API"]="cms";
			$postdata["APIRequest"]="MenuEdit";
			$postdata["site_id"]=$("#menu_edit_site_id").val();
			$postdata["title"]=$("#menu_edit_title").val();
			$postdata["description"]=$("#menu_edit_description").val();
			$postdata["idtag"]=$("#menu_edit_idtag").val();
			$postdata["id_menu"]=$("#menu_edit_id_menu").val();
			soa2($postdata, "menu_edit_save");
			return;
		}

		$("#menu_edit_dialog").dialog("close");
		show_status("Menü erfolgreich aktualisiert.");
		wait_dialog_hide();
		delete $cms_menus;
		view();
	}


	function menu_remove($id_menu)
	{
		if( !confirm("Sind Sie sicher, dass Sie das Menü löschen wollen?") ) return;
		
		wait_dialog_show("Lösche Menü", 100);
		var $postdata=new Object();
		$postdata["API"]="cms";
		$postdata["APIRequest"]="MenuRemove";
		$postdata["id_menu"]=$id_menu;
		soa2($postdata, "menu_remove_save");
	}


	function menu_remove_save($xml)
	{
		show_status("Menü erfolgreich gelöscht.");
		wait_dialog_hide();
		delete $cms_menus;
		view();
	}


	function menuitem_add()
	{
		wait_dialog_show("Lese Sprachen aus", 0);
		if(table_data_select("cms_languages", "*", "ORDER BY ordering;", "dbweb", "$cms_languages", "menuitem_add")) return;
		wait_dialog_show("Zeichne Menüpunkt-Editor", 100);
		var $html = '';
		$html += '<div id="menuitem_add_tabs">';
		$html += '<ul>';
		for($i=0; $i<$cms_languages.length; $i++)
		{
			$html += '	<li><a href="#menuitem_add_tab'+$i+'">'+$cms_languages[$i]["language"]+'</a></li>';
		}
		$html += '</ul>';
		for($i=0; $i<$cms_languages.length; $i++)
		{
			$html += '<div id="menuitem_add_tab'+$i+'">';
			$html += '<table>';
			$html += '	<tr>';
			$html += '		<td>';
			$html += '			<label>Titel</label><br />';
			$html += '			<input id="menuitem_add_title'+$cms_languages[$i]["id_language"]+'" style="width:300px;" type="text" value="" />';
			$html += '			<span id="menuitem_add_title'+$cms_languages[$i]["id_language"]+'_length"></span>';
			$html += '		</td>';
			$html += '		<td>';
			$html += '			<label>Meta-Titel</label><br />';
			$html += '			<input id="menuitem_add_meta_title'+$cms_languages[$i]["id_language"]+'" style="width:300px;" type="text" value="" onkeyup="textlength(\'menuitem_add_meta_title'+$cms_languages[$i]["id_language"]+'\', \'menuitem_add_meta_title'+$cms_languages[$i]["id_language"]+'_length\', 80);" />';
			$html += '			<span id="menuitem_add_meta_title'+$cms_languages[$i]["id_language"]+'_length"></span>';
			$html += '		</td>';
			$html += '	</tr>';
			$html += '	<tr>';
			$html += '		<td>Beschreibung</td>';
			$html += '		<td><textarea id="menuitem_add_description'+$cms_languages[$i]["id_language"]+'" style="width:300px; height:100px;"></textarea></td>';
			$html += '		<td>Meta-Description</td>';
			$html += '		<td><textarea id="menuitem_add_meta_description'+$cms_languages[$i]["id_language"]+'" style="width:300px; height:100px;"></textarea></td>';
			$html += '	</tr>';
			$html += '	<tr>';
			$help='Der Alias wird als SEO-Link verwendet. Beispiel: Alias=test/ => Link=http://www.domain.de/test/';
			$html += '		<td>Alias <img alt="Hilfe" onclick="alert($help);" src="<?php echo PATH; ?>images/icons/16x16/help.png" style="cursor:pointer; float:right;" title="Hilfe" /></td>';
			$html += '		<td><input id="menuitem_add_alias'+$cms_languages[$i]["id_language"]+'" style="width:300px;" type="text"  value=""/></td>';
			$html += '		<td>Meta-Keywords</td>';
			$html += '		<td><input id="menuitem_add_meta_keywords'+$cms_languages[$i]["id_language"]+'" style="width:300px;" type="text" value="" /></td>';
			$html += '	</tr>';
			$html += '</table>';
			$html += '</div>';
		}
		$html += '</div>';
		$html += '<table class="hover" style="float:left;">';
		$html += '	<tr>';
		$html += '		<td>Lokal?</td>';
		$html += '		<td>';
		$html += '			<select id="menuitem_add_local">';
		$html += '				<option value="0">Nein</option>';
		$html += '				<option value="1">Ja</option>';
		$html += '			</select>';
		$html += '		</td>';
		$html += '	</tr>';
		$html += '	<tr>';
		$html += '		<td>Link</td>';
		$html += '		<td><input id="menuitem_add_link" style="width:300px;" type="text" value="" /></td>';
		$html += '	</tr>';
		$html += '	<tr>';
		$html += '		<td>Icon</td>';
		$html += '		<td><input id="menuitem_add_icon" style="width:300px;" type="text" value="" /></td>';
		$html += '	</tr>';
		$html += '</table>';
		$html += '<input id="menuitem_add_id_menu" type="hidden" value="'+$cms_menus[$i]["id_menu"]+'" />';
		if( $("#menuitem_add_dialog").length == 0 ) $("body").append('<div id="menuitem_add_dialog" style="display:none;"></div>');
		$("#menuitem_add_dialog").html($html);
		
		$(function() { $("#menuitem_add_tabs").tabs(); });
		$("#menuitem_add_dialog").dialog
		({	buttons:
			[
				{ text: "Speichern", click: function() { menuitem_add_save(); } },
				{ text: "Abbrechen", click: function() { $(this).dialog("close"); } }
			],
			closeText:"Fenster schließen",
			modal:true,
			resizable:false,
			title:"Menüpunkt hinzufügen",
			width:800
		});
		wait_dialog_hide();	
	}


	function menuitem_add_save($xml)
	{
		if( typeof $xml === "undefined" )
		{
			wait_dialog_show("Lese Sprachen aus", 0);
			if(table_data_select("cms_languages", "*", "ORDER BY ordering;", "dbweb", "$cms_languages", "menuitem_add_save")) return;
			wait_dialog_show("Lese Menüpunktfelder aus", 20);
			var $postdata=new Object();
			$postdata["API"]="cms";
			$postdata["APIRequest"]="MenuitemAdd";
			$postdata["id_menu"]=$("#menuitem_add_id_menu").val();
			$postdata["id_menuitem"]=$("#menuitem_add_id_menuitem").val();
			for($i=0; $i<$cms_languages.length; $i++)
			{
				$postdata["title"+$cms_languages[$i]["id_language"]]=$("#menuitem_add_title"+$cms_languages[$i]["id_language"]).val();
				$postdata["description"+$cms_languages[$i]["id_language"]]=$("#menuitem_add_description"+$cms_languages[$i]["id_language"]).val();
				$postdata["alias"+$cms_languages[$i]["id_language"]]=$("#menuitem_add_alias"+$cms_languages[$i]["id_language"]).val();
			}
			$postdata["local"]=$("#menuitem_add_local").val();
			$postdata["link"]=$("#menuitem_add_link").val();
			$postdata["icon"]=$("#menuitem_add_icon").val();
			wait_dialog_show("Speichere Menüpunkt", 100);
			soa2($postdata, "menuitem_add_save");
			return;
		}

		$("#menuitem_add_dialog").dialog("close");
		show_status("Menüpunkt erfolgreich hinzugefügt.");
		wait_dialog_hide();
		delete $cms_menuitems;
		view_menuitems();
	}


	function menuitem_edit($id_menuitem)
	{
		if( typeof $id_menuitem === "undefined" ) $id_menuitem=$("#menuitem_edit_id_menuitem").val();
		else $("#menuitem_edit_id_menuitem").val($id_menuitem);
		wait_dialog_show("Lese Menüpunkte aus", 0);
		if(table_data_select("cms_menuitems", "*", "", "dbweb", "$cms_menuitems", "menuitem_edit")) return;
		wait_dialog_show("Lese Menüpunkttitel aus", 0);
		if(table_data_select("cms_menuitems_languages", "*", "", "dbweb", "$cms_menuitems_languages", "menuitem_edit")) return;
		wait_dialog_show("Lese Sprachen aus", 50);
		if(table_data_select("cms_languages", "*", "ORDER BY ordering;", "dbweb", "$cms_languages", "menuitem_edit")) return;
		wait_dialog_show("Zeichne Menüpunkt-Editor", 100);
		var $html = '';
		$html += '<div id="menuitem_edit_tabs">';
		$html += '<ul>';
		for($i=0; $i<$cms_languages.length; $i++)
		{
			$html += '	<li><a href="#menuitem_edit_tab'+$i+'">'+$cms_languages[$i]["language"]+'</a></li>';
		}
		$html += '</ul>';
		for($i=0; $i<$cms_languages.length; $i++)
		{
			var $title='';
			var $description='';
			var $alias='';
			for($j=0; $j<$cms_menuitems_languages.length; $j++)
			{
				if( $cms_menuitems_languages[$j]["menuitem_id"]==$id_menuitem )
				{
					if( $cms_menuitems_languages[$j]["language_id"]==$cms_languages[$i]["id_language"] )
					{
						$title=$cms_menuitems_languages[$j]["title"];
						$description=$cms_menuitems_languages[$j]["description"];
						$alias=$cms_menuitems_languages[$j]["alias"];
					}
				}
			}
			$html += '<div id="menuitem_edit_tab'+$i+'">';
			$html += '<table>';
			$html += '	<tr>';
			$html += '		<td>Titel</td>';
			$html += '		<td><input id="menuitem_edit_title'+$cms_languages[$i]["id_language"]+'" style="width:300px;" type="text" value="'+$title+'" /></td>';
			$html += '	</tr>';
			$html += '	<tr>';
			$html += '		<td>Beschreibung</td>';
			$html += '		<td><textarea id="menuitem_edit_description'+$cms_languages[$i]["id_language"]+'" style="width:300px; height:100px;">'+$description+'</textarea></td>';
			$html += '	</tr>';
			$html += '	<tr>';
			$help='Der Alias wird als SEO-Link verwendet. Beispiel: Alias=test/ => Link=http://www.domain.de/test/';
			$html += '		<td>Alias <img alt="Hilfe" onclick="alert($help);" src="<?php echo PATH; ?>images/icons/16x16/help.png" style="cursor:pointer; float:right;" title="Hilfe" /></td>';
			$html += '		<td><input id="menuitem_edit_alias'+$cms_languages[$i]["id_language"]+'" style="width:300px;" type="text" value="'+$alias+'"/></td>';
			$html += '	</tr>';
			$html += '</table>';
			$html += '</div>';
		}
		var $link='';
		var $icon='';
		for($j=0; $j<$cms_menuitems.length; $j++)
		{
			if( $cms_menuitems[$j]["id_menuitem"]==$id_menuitem )
			{
				$local=$cms_menuitems[$j]["local"];
				$link=$cms_menuitems[$j]["link"];
				$icon=$cms_menuitems[$j]["icon"];
			}
		}
		$html += '</div>';
		$html += '<table class="hover" style="float:left;">';
		$html += '	<tr>';
		$html += '		<td>Lokal?</td>';
		$html += '		<td>';
		$html += '			<select id="menuitem_edit_local">';
		$html += '				<option value="0">Nein</option>';
		$html += '				<option value="1">Ja</option>';
		$html += '			</select>';
		$html += '		</td>';
		$html += '	</tr>';
		$html += '	<tr>';
		$html += '		<td>Link</td>';
		$html += '		<td><input id="menuitem_edit_link" style="width:300px;" type="text" value="'+$link+'" /></td>';
		$html += '	</tr>';
		$html += '	<tr>';
		$html += '		<td>Icon</td>';
		$html += '		<td><input id="menuitem_edit_icon" style="width:300px;" type="text" value="'+$icon+'" /></td>';
		$html += '		<td>';
		if( $icon!="" ) $html += '<img src="<?php echo PATH; ?>'+$icon+'" />';
		$html += '		</td>';
		$html += '	</tr>';
		$html += '</table>';
		if( $("#menuitem_edit_dialog").length == 0 ) $("body").append('<div id="menuitem_edit_dialog" style="display:none;"></div>');
		$("#menuitem_edit_dialog").html($html);
		$("#menuitem_edit_local").val($local);
		$(function() { $("#menuitem_edit_tabs").tabs(); });
		$("#menuitem_edit_dialog").dialog
		({	buttons:
			[
				{ text: "Speichern", click: function() { menuitem_edit_save(); } },
				{ text: "Abbrechen", click: function() { $(this).dialog("close"); } }
			],
			closeText:"Fenster schließen",
			modal:true,
			resizable:false,
			title:"Menüpunkt bearbeiten",
			width:800
		});
		wait_dialog_hide();	
	}


	function menuitem_edit_save()
	{
		var $input=$('#menuitem_edit_dialog').find("input[id^='menuitem_edit_']");
		$input.each(function()
		{
			alert($(this).id);
		})
		return;
		$postdata=new Object();
		$postdata["API"]="cms";
		$postdata["APIRequest"]="MenuitemEdit";
		soa2($postdata, "menuitem_edit_save2");
	}


	function menuitem_edit_save2($xml)
	{
		alert($xml);
	}


	function menuitem_edit_ordering()
	{
		delete $cms_menuitems;
		wait_dialog_show("Zeichne Menüpunkte", 100);
		view_menuitems();
	}


	function menuitem_remove($id_menuitem)
	{
		if( !confirm("Sind Sie sicher, dass Sie diesen Menüpunkt und alle seine Unterpunkte löschen wollen?") ) return;
		var $postdata=new Object();
		$postdata["API"]="cms";
		$postdata["APIRequest"]="MenuitemRemove";
		$postdata["id_menuitem"]=$id_menuitem;
		wait_dialog_show("Lösche Menüpunkt", 100);
		soa2($postdata, "menuitem_remove_save");
	}


	function menuitem_remove_save($xml)
	{
		show_status("Menüpunkt erfolgreich gelöscht.");
		wait_dialog_hide();
		delete $cms_menuitems;
		view_menuitems();
	}


	function update_categorypictures()
	{
		wait_dialog_show();
		$.post("<?php echo PATH; ?>soa/", {API: "cms", Action: "Create_cmsArticles_for_shopcategories"},
			function (data)
			{
				var $xml=$($.parseXML(data));
				var Ack = $xml.find("Ack").text();
				if (Ack=="Success") 
				{
					wait_dialog_hide();
					var counter = $xml.find("counter").text();
					show_status("Beiträge erfolgreich aktualisiert. Es wurden "+counter+" neue Beiträge angelegt");
				}
				else 
				{
					show_status2(data);
					return;
				}
			}
		);
	}
	
	function view()
	{
		wait_dialog_show("Lese Menüs aus", 0);
		if(table_data_select("cms_menus", "*", "WHERE site_id IN(0, <?php echo $_SESSION["id_site"]; ?>) ORDER BY title;", "dbweb", "$cms_menus", "view")) return;
		wait_dialog_show("Zeichne Menüs", 100);
		if( $("#view").length == 0 ) $("body").append('<div id="view"></div>');
		
		//view menus
		var $html = '';
		$html += '<table class="hover" style="float:left;">';
		$html += '	<tr>';
		$html += '		<th>Nr.</th>';
		$html += '		<th>Titel</th>';
		$html += '		<th>Bezeichner</th>';
		$html += '		<th>Global</th>';
		$html += '		<th>';
		$html += '			<img alt="Menü hinzufügen" onclick="menu_add();" src="<?php echo PATH; ?>images/icons/24x24/add.png" style="cursor:pointer;" title="Menü hinzufügen" />';
		$html += '		</th>';
		$html += '	</tr>';
		var $id_menu=0;
		for($i=0; $i<$cms_menus.length; $i++)
		{
			if( '<?php echo $_GET["getvars1"]; ?>'==$cms_menus[$i]["id_menu"] ) $id_menu=$cms_menus[$i]["id_menu"];
			$html += '	<tr>';
			$html += '		<td>'+($i+1)+'</td>';
			$html += '		<td>';
			if( '<?php echo $_GET["getvars1"]; ?>'==$cms_menus[$i]["id_menu"] ) $style=' style="font-weight:bold;"'; else $style='';
			$html += '		<a'+$style+' href="<?php echo PATH; ?>backend/inhalte/menue-editor/'+$cms_menus[$i]["id_menu"]+'/">'+$cms_menus[$i]["title"]+'</a>';
			$html += '		<br /><i>'+$cms_menus[$i]["description"]+'</i>';
			$html += '		</td>';
			$html += '		<td>'+$cms_menus[$i]["idtag"]+'</td>';
			if($cms_menus[$i]["site_id"]==0) $global='<img src="<?php echo PATH; ?>images/icons/24x24/accept.png" />'; else $global='';
			$html += '		<td>'+$global+'</td>';
			$html += '		<td>';
			$html += '			<img alt="Menü bearbeiten" onclick="menu_edit('+$i+');" src="<?php echo PATH; ?>images/icons/24x24/edit.png" style="cursor:pointer;" title="Menü bearbeiten" />';
			$html += '			<img alt="Menü löschen" onclick="menu_remove('+$cms_menus[$i]["id_menu"]+');" src="<?php echo PATH; ?>images/icons/24x24/remove.png" style="cursor:pointer;" title="Menü löschen" />';
			$html += '		</td>';
			$html += '	</tr>';
		}
		$html += '</table>';
		$html += '<input id="menu_id_menu" type="hidden" value="'+$id_menu+'">';
		$html += '<div id="view_menuitem" style="float:left;"></div>';
		$("#view").html($html);
		wait_dialog_hide();
		view_menuitems();
	}
	
	
	function view_menuitems($id_menu, $id_menuitem)
	{
		if( typeof $id_menu === "undefined" )
		{
			if( $("#menu_id_menu").val() == 0 ) return;
			else var $id_menu=$("#menu_id_menu").val();
		}
		if( typeof $id_menuitem === "undefined" )
		{
			if( $("#view_menuitems_id_menuitem").length == 0 ) var $id_menuitem=0;
			else var $id_menuitem=$("#view_menuitems_id_menuitem").val();
		}
		wait_dialog_show("Lese Sprachen aus", 0);
		if(table_data_select("cms_languages", "*", "ORDER BY ordering;", "dbweb", "$cms_languages", "view_menuitems")) return;
		wait_dialog_show("Lese Menüpunkte aus", 20);
		if(table_data_select("cms_menuitems", "*", "WHERE menu_id="+$id_menu+" ORDER BY menuitem_id, ordering;", "dbweb", "$cms_menuitems", "view_menuitems")) return;
		wait_dialog_show("Lese Menüpunkttitel aus", 50);
		if(table_data_select("cms_menuitems_languages", "*", "", "dbweb", "$cms_menuitems_languages", "view_menuitems")) return;
		wait_dialog_show("Zeichne Menüpunkte", 100);
		var $html = '';
		$html += '<div style="margin:5px; float:left;">'+view_menuitem_path($id_menuitem, "")+'</div>';
		$html += '<br style="clear:both;" />';
		$html += '<table class="hover" id="menuitem_edit" style="float:left;">';
		$html += '	<tr class="unsortable">';
		$html += '		<th>Nr.</th>';
		$html += '		<th>Icon</th>';
		$html += '		<th>Titel</th>';
		$html += '		<th>Unterpunkte</th>';
		$html += '		<th>Alias</th>';
		$html += '		<th>';
		$html += '			<img alt="Menüpunkt hinzufügen" onclick="menuitem_add();" src="<?php echo PATH; ?>images/icons/24x24/add.png" style="cursor:pointer;" title="Menüpunkt hinzufügen" />';
		$html += '		</th>';
		$html += '	</tr>';
		for($i=0; $i<$cms_menuitems.length; $i++)
		{
			//find translations
			var $title="";
			var $description="";
			var $alias="";
			for($j=0; $j<$cms_menuitems_languages.length; $j++)
			{
				if( $cms_menuitems_languages[$j]["menuitem_id"]==$cms_menuitems[$i]["id_menuitem"] && $cms_menuitems_languages[$j]["language_id"]==<?php echo $_SESSION["id_language"]; ?> )
				{
					$title=$cms_menuitems_languages[$j]["title"];
					$description=$cms_menuitems_languages[$j]["description"];
					$alias=$cms_menuitems_languages[$j]["alias"];
					break;
				}
			}
			//find alternative translations if needed
			if( $title=="" )
			{
				for($k=0; $k<$cms_languages.length; $k++)
				{
					for($j=0; $j<$cms_menuitems_languages.length; $j++)
					{
						if( $cms_menuitems_languages[$j]["menuitem_id"]==$cms_menuitems[$i]["id_menuitem"] && $cms_menuitems_languages[$j]["language_id"]==$cms_languages[$k]["id_language"] )
						{
							$title=$cms_menuitems_languages[$j]["title"];
							$description=$cms_menuitems_languages[$j]["description"];
							$alias=$cms_menuitems_languages[$j]["alias"];
							break;
						}
					}
				}
			}
			if( $cms_menuitems[$i]["menuitem_id"]==$id_menuitem )
			{
				//count sub menuitems
				var $count=0;
				for($j=0; $j<$cms_menuitems.length; $j++)
				{
					if( $cms_menuitems[$j]["menuitem_id"]==$cms_menuitems[$i]["id_menuitem"] ) $count++;
				}
				$html += '	<tr id="'+$cms_menuitems[$i]["id_menuitem"]+'">';
				$html += '		<td style="cursor:move;">'+$cms_menuitems[$i]["ordering"]+'</td>';
				$html += '		<td>';
				if( $cms_menuitems[$i]["icon"]!="" ) $html += '<img src="<?php echo PATH; ?>'+$cms_menuitems[$i]["icon"]+'" style="width:24px;" />';
				$html += '		</td>';
				$html += '		<td>';
				$html += '			<a href="javascript:view_menuitems('+$id_menu+', '+$cms_menuitems[$i]["id_menuitem"]+');">'+$title+'</a>';
				$html += '			<br /><i>'+$description+'</i>';
				$html += '		</td>';
				$html += '		<td>'+$count+'</td>';
				$html += '		<td>'+$alias+'</td>';
				$html += '		<td>';
				$html += '			<img alt="Menüpunkt bearbeiten" onclick="menuitem_edit('+$cms_menuitems[$i]["id_menuitem"]+');" src="<?php echo PATH; ?>images/icons/24x24/edit.png" style="cursor:pointer;" title="Menüpunkt bearbeiten" />';
				$html += '			<img alt="Menüpunkt löschen" onclick="menuitem_remove('+$cms_menuitems[$i]["id_menuitem"]+');" src="<?php echo PATH; ?>images/icons/24x24/remove.png" style="cursor:pointer;" title="Menüpunkt löschen" />';
				$html += '		</td>';
				$html += '	</tr>';
			}
		}
		$html += '</table>';
		$html += '<input id="view_menuitems_id_menuitem" type="hidden" value="'+$id_menuitem+'">';
		$html += '<input id="menuitem_edit_id_menuitem" type="hidden" value="">';
		$("#view_menuitem").html($html);
		$(function() {
			$("#menuitem_edit").sortable( { items:"tr:not(.unsortable)" } );
			$("#menuitem_edit").disableSelection();
		});
		$("#menuitem_edit").bind("sortupdate", function(event, ui)
		{
			wait_dialog_show("Aktualisiere Menüpunkte-Sortierung", 0);
			var $postdata=new Object();
			$postdata["API"]="cms";
			$postdata["APIRequest"]="MenuitemEditOrdering";
			var $ids = $('#menuitem_edit').sortable('toArray');
			$postdata["ids"]=$ids.join(", ");
			soa2($postdata, "menuitem_edit_ordering");
		});
		wait_dialog_hide();
	}
	
	
	function view_menuitem_path($id_menuitem, $path)
	{
		if($id_menuitem==0)
		{
			for($i=0; $i<$cms_menus.length; $i++)
			{
				if( $cms_menus[$i]["id_menu"]==$cms_menuitems[0]["menu_id"] )
				{
					var $id_menu=$cms_menus[$i]["id_menu"];
					var $title=$cms_menus[$i]["title"];
					break;
				}
			}
			return('<a href="javascript:view_menuitems('+$id_menu+', 0);">'+$title+'</a>'+$path);
		}
		for($i=0; $i<$cms_menuitems.length; $i++)
		{
			//find current element
			if( $cms_menuitems[$i]["id_menuitem"]==$id_menuitem )
			{
				$path=' &gt; <a href="javascript:view_menuitems('+$cms_menuitems[$i]["menu_id"]+', '+$id_menuitem+');">'+$cms_menuitems[$i]["title"]+'</a>'+$path;
				$path=view_menuitem_path($cms_menuitems[$i]["menuitem_id"], $path);
				break;
			}
		}
		return($path);
	}
	
	$(function() { view(); });

</script>
<?php

	//PATH
	echo '<p>';
	echo '<a href="backend_index.php">Backend</a>';
	echo ' > <a href="backend_cms_index.php">Content Management</a>';
	echo ' > Menüs';
	echo '</p>';

	
	//HEADLINE
	echo '<h1>Menüs</h1>';
	echo '<div id="view"></div>';


	//CREATE MENUITEM
	if (isset($_POST["menuitem_add"]))
    {
		if ($_POST["link"]=="") echo '<div class="failure">Der Link darf nicht leer sein!</div>';
		elseif ($_POST["title"]=="") echo '<div class="failure">Der Titel darf nicht leer sein!</div>';
		elseif ($_GET["id_menu"]=="") echo '<div class="failure">Es konnte keine ID für das Menü gefunden werden!</div>';
		elseif (!($_POST["menuitem_id"]>=0)) echo '<div class="failure">Es konnte keine ID für einen übergeordneten Menüpunkt gefunden werden!</div>';
		else
        {
			q("INSERT INTO cms_menuitems (link, alias, local, title, description, ordering, icon, menu_id, menuitem_id, firstmod, firstmod_user, lastmod, lastmod_user) VALUES('".addslashes(stripslashes($_POST["link"]))."', '".addslashes(stripslashes($_POST["alias"]))."', ".$_POST["local"].", '".addslashes(stripslashes($_POST["title"]))."', '".addslashes(stripslashes($_POST["description"]))."', ".$_POST["ordering"].", '".addslashes(stripslashes($_POST["icon"]))."', ".$_GET["id_menu"].", ".$_POST["menuitem_id"].", '".time()."', '".$_SESSION["id_user"]."', '".time()."', '".$_SESSION["id_user"]."');", $dbweb, __FILE__, __LINE__);
			echo '<div class="success">Menüpunkt erfolgreich angelegt!</div>';
			if (!file_exists($_POST["link"])) copy("index.php", $_POST["link"]);
        }
	}


	//UPDATE MENUITEM
	if (isset($_POST["menuitem_update"]))
    {
		if ($_POST["link"]=="") echo '<div class="failure">Der Link darf nicht leer sein!</div>';
		elseif ($_POST["title"]=="") echo '<div class="failure">Der Titel darf nicht leer sein!</div>';
		elseif (!($_POST["menuitem_id"]>=0)) echo '<div class="failure">Es konnte keine ID für einen übergeordneten Menüpunkt gefunden werden!</div>';
		else
        {
//			$alias=str_replace(" ", "%20", $_POST["alias"]);
			$alias=$_POST["alias"];
			q("UPDATE cms_menuitems
						 SET link='".addslashes(stripslashes($_POST["link"]))."',
						 	 alias='".mysqli_real_escape_string($dbweb, $alias)."',
							 local=".$_POST["local"].",
						 	 title='".addslashes(stripslashes($_POST["title"]))."',
						 	 description='".addslashes(stripslashes($_POST["description"]))."',
						 	 icon='".addslashes(stripslashes($_POST["icon"]))."',
						 	 ordering='".addslashes(stripslashes($_POST["ordering"]))."',
						 	 menuitem_id='".addslashes(stripslashes($_POST["menuitem_id"]))."',
						 	 lastmod='".time()."',
						 	 lastmod_user='".$_SESSION["id_user"]."'
						 WHERE id_menuitem=".$_POST["id_menuitem"].";", $dbweb, __FILE__, __LINE__);
			if ($_POST["link"]!="")
			{
				if (!file_exists($_POST["link"])) copy("index.php", $_POST["link"]);
			}
//			echo '<div class="success">Menüpunkt erfolgreich aktualisiert!</div>';
        }
    }


	//REMOVE MENUITEM
	if (isset($_POST["menuitem_remove"]))
    {
		if (!($_POST["id_menuitem"]>0)) echo '<div class="failure">Es konnte keine ID für den Menüpunkt gefunden werden!</div>';
		else
		{
			q("DELETE FROM cms_menuitems WHERE id_menuitem=".$_POST["id_menuitem"]." LIMIT 1;", $dbweb, __FILE__, __LINE__);
			echo '<div class="success">Menüpunkt erfolgreich gelöscht!</div>';
		}
	}


	function menu2table($menu, $id, $level)
	{
		$spacer='';
		for ($j=0; $j<$level; $j++) $spacer.='&nbsp; &nbsp; &nbsp; &nbsp;';
		for($i=0; $i<sizeof($menu["title"]); $i++)
		{
			if ($menu["menuitem_id"][$i]==$id)
			{
				echo '<tr>';
//				echo '	<td>'.$menu["ordering"][$i].'</td>';
				if ($menu["icon"][$i]=="") echo '<td></td>';
				else echo '<td><img style="width:24px; height:24px;" src="'.PATH.$menu["icon"][$i].'" alt="'.$menu["icon"][$i].'" title="'.$menu["icon"][$i].'" /></td>';
				echo '	<td>'.$spacer.$menu["ordering"][$i].'. '.$menu["title"][$i].'</td>';
				echo '	<td>';
				echo '		<form action="backend_cms_menus.php?id_menu='.$_GET["id_menu"].'" style="margin:0; border:0; padding:0; float:right;" method="post">';
				echo '			<input type="hidden" name="id_menuitem" value="'.$menu["id_menuitem"][$i].'" />';
				echo '			<input type="hidden" name="menuitem_remove" value="Menüpunkt löschen" />';
				echo '			<input style="margin:2px 8px 2px 0px; border:0; padding:0; float:right;" type="image" src="'.PATH.'images/icons/24x24/remove.png" alt="Menüpunkt löschen" title="Menüpunkt löschen" onclick="return confirm(\'Menüpunkt wirklich löschen?\');" />';
				echo '		</form>';
				echo '		<img style="margin:0px 5px 0px 0px; border:0; padding:0; cursor:pointer; float:right;" src="'.PATH.'images/icons/24x24/edit.png" alt="Menüpunkt bearbeiten" title="Menüpunkt bearbeiten" onclick="popup(\'modules/backend_cms_menuitem_editor.php?id_menuitem='.$menu["id_menuitem"][$i].'\', 520, 400);" />';
				echo '	</td>';
				echo '</tr>';
				$children=0;
				for($j=0; $j<sizeof($menu["title"]); $j++)
				{
					if ($menu["id_menuitem"][$i]==$menu["menuitem_id"][$j])
					{
						$children++;
					}
				}
				if ($children>0)
				{
					$level++;
					echo '	<tr>';
					menu2table($menu, $menu["id_menuitem"][$i], $level);
					echo '	</tr>';
					$level--;
				}
			}
		}
	}

	//copy links to cms_menuitems_languages
/*
	$results=q("SELECT * FROM cms_menuitems;", $dbweb, __FILE__, __LINE__);
	while( $row=mysqli_fetch_assoc($results) )
	{
		$results2=q("SELECT * FROM cms_menuitems_languages WHERE language_id=1 AND menuitem_id=".$row["id_menuitem"].";", $dbweb, __FILE__, __LINE__);
		if( mysqli_num_rows($results2)==0 )
		{
			$data=$row;
			unset($data["id_menuitem"]);
			unset($data["link"]);
			unset($data["local"]);
			unset($data["ordering"]);
			unset($data["icon"]);
			unset($data["menu_id"]);
			unset($data["hide"]);
			$data["language_id"]=1;
			$data["menuitem_id"]=$row["id_menuitem"];
			q_insert("cms_menuitems_languages", $data, $dbweb, _FILE__, __LINE__);
		}
	}
*/

	include("templates/".TEMPLATE_BACKEND."/footer.php");
?>
<?php
	if(isset($_GET["getvars1"])) $_GET["id_menu"]=$_GET["getvars1"];

	include("config.php");
	include("functions/cms_t2.php");
	include("templates/".TEMPLATE_BACKEND."/header.php");
	
?>
<style>
ul.ui-autocomplete {
    z-index: 1100;
}
</style>
<script type="text/javascript">
	var	$data=new Array();
<?php
	//array of local files for autocomplete
	echo '	var $global_files=new Array();'."\n";
	$dir = ".";
	$i=0;
	if (is_dir($dir))
	{
		if ($dh = opendir($dir))
		{
			while ( ($file = readdir($dh)) !== false )
			{
				if( !is_dir($file) and strpos($file, ".LCK") === false )
				{
					echo "	$"."global_files[".$i."]='".$file."';\n";
					$i++;
				}
			}
			closedir($dh);
		}
	}
	//array of local files for autocomplete
	echo '	var $local_files=new Array();'."\n";
	$dir = "templates/".TEMPLATE;
	$i=0;
	if (is_dir($dir))
	{
		if ($dh = opendir($dir))
		{
			while ( ($file = readdir($dh)) !== false )
			{
				if( !is_dir($file) and strpos($file, ".LCK") === false )
				{
					echo "	$"."local_files[".$i."]='".$file."';\n";
					$i++;
				}
			}
			closedir($dh);
		}
	}
	//array of image files
	echo '	var $icon_files=new Array();'."\n";
	$dir = "images/icons/64x64/";
	$i=0;
	if (is_dir($dir))
	{
		if ($dh = opendir($dir))
		{
			while ( ($file = readdir($dh)) !== false )
			{
				if( !is_dir($file) and strpos($file, ".LCK") === false )
				{
					echo "	$"."icon_files[".$i."]='".$dir.$file."';\n";
					$i++;
				}
			}
			closedir($dh);
		}
	}
	echo "\n";
?>
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
	
	function alias_preview($input, $output)
	{
		$("#"+$output).html("<?php echo PATH; ?>"+$("#"+$input).val());
	}
	
	
	function menuitem_add_local_change()
	{
		if($("#menuitem_add_local").val()==0)
		{
			$( "#menuitem_add_link" ).autocomplete('option', 'source', $global_files)
		}
		else
		{
			$( "#menuitem_add_link" ).autocomplete('option', 'source', $local_files)
		}
	}
	
	
	function menuitem_add_image_change()
	{
		var $src=$("#menuitem_add_icon").val();
		if( $src.indexOf(".") >-1 )
		{
			$("#menuitem_add_image").html('<img src="<?php echo PATH; ?>'+$src+'" style="float:right;" />');
		}
	}


	function menuitem_add()
	{
		wait_dialog_show("Lese Sprachen aus", 0);
		if(table_data_select("cms_languages", "*", "ORDER BY ordering;", "dbweb", "$cms_languages", "menuitem_add")) return;
		wait_dialog_show("Zeichne Menüpunkt-Editor", 100);
		var $html = '';
		$html += '<div id="menuitem_add_tabs">';
		$html += '<ul>';
		$html += '	<li><a href="#menuitem_add_tab">Allgemein</a></li>';
		for($i=0; $i<$cms_languages.length; $i++)
		{
			$html += '	<li><a href="#menuitem_add_tab'+$i+'">'+$cms_languages[$i]["language"]+'</a></li>';
		}
		$html += '</ul>';
		$html += '<div id="menuitem_add_tab">';
		$html += '<table class="hover">';
		$html += '	<tr>';
		$html += '		<td>Dynamischer Link</td>';
		$html += '		<td>';
		$html += '			<select id="menuitem_add_dynamic">';
		$html += '				<option value="0">Nein</option>';
		$html += '				<option value="1">Ja</option>';
		$html += '			</select>';
		$html += '		</td>';
		$html += '	</tr>';
		$html += '	<tr>';
		$html += '		<td>Lokal</td>';
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
		$html += '		<td>';
		$html += '			<input id="menuitem_add_icon" style="width:200px;" type="text" value="" />';
		$html += '			<span id="menuitem_add_image"></span>';
		$html += '		</td>';
		$html += '	</tr>';
		$html += '</table>';
		$html += '</div>';
		for($i=0; $i<$cms_languages.length; $i++)
		{
			$html += '<div id="menuitem_add_tab'+$i+'">';
			$html += '<table>';
			$html += '	<tr>';
			$html += '		<td style="vertical-align:top;">';
			$html += '			<label>Titel</label><br />';
			$html += '			<input id="menuitem_add_title'+$cms_languages[$i]["id_language"]+'" style="width:300px;" type="text" value="" />';
			$html += '		</td>';
			$html += '		<td style="vertical-align:top;">';
			$html += '			<label>Meta-Titel</label><br />';
			$html += '			<input id="menuitem_add_meta_title'+$cms_languages[$i]["id_language"]+'" style="width:300px;" type="text" value="" onkeyup="textwidth(\'menuitem_add_meta_title'+$cms_languages[$i]["id_language"]+'\', \'menuitem_add_meta_title'+$cms_languages[$i]["id_language"]+'_length\', 512, 18);" />';
			$html += '			<br /><span id="menuitem_add_meta_title'+$cms_languages[$i]["id_language"]+'_length"></span>';
			$html += '		</td>';
			$html += '	</tr>';
			$html += '	<tr>';
			$html += '		<td style="vertical-align:top;">';
			$html += '			<label>Beschreibung</label><br />';
			$html += '			<textarea id="menuitem_add_description'+$cms_languages[$i]["id_language"]+'" style="width:300px; height:100px;"></textarea>';
			$html += '		</td>';
			$html += '		<td style="vertical-align:top;">';
			$html += '			<label>Meta-Description</label><br />';
			$html += '			<textarea id="menuitem_add_meta_description'+$cms_languages[$i]["id_language"]+'" style="width:300px; height:100px;" onkeyup="textwidth(\'menuitem_add_meta_description'+$cms_languages[$i]["id_language"]+'\', \'menuitem_add_meta_description'+$cms_languages[$i]["id_language"]+'_length\', 923, 13);"></textarea>';
			$html += '			<br /><span id="menuitem_add_meta_description'+$cms_languages[$i]["id_language"]+'_length"></span>';
			$html += '		</td>';
			$html += '	</tr>';
			$html += '	<tr>';
			$html += '		<td style="vertical-align:top;">';
			$html += '			<label>Alias</label><br />';
			$html += '			<input id="menuitem_add_alias'+$cms_languages[$i]["id_language"]+'" style="width:300px;" type="text" value="" onkeyup="alias_preview(\'menuitem_add_alias'+$cms_languages[$i]["id_language"]+'\', \'menuitem_add_alias'+$cms_languages[$i]["id_language"]+'_preview\');" />';
			$html += '			<br /><span id="menuitem_add_alias'+$cms_languages[$i]["id_language"]+'_preview"></span>';
			$html += '		</td>';
			$html += '		<td style="vertical-align:top;">';
			$html += '			<label>Meta-Keywords</label><br />';
			$html += '			<input id="menuitem_add_meta_keywords'+$cms_languages[$i]["id_language"]+'" style="width:300px;" type="text" value="" onkeyup="textlength(\'menuitem_add_meta_keywords'+$cms_languages[$i]["id_language"]+'\', \'menuitem_add_meta_keywords'+$cms_languages[$i]["id_language"]+'_length\', 300);" />';
			$html += '			<br /><span id="menuitem_add_meta_keywords'+$cms_languages[$i]["id_language"]+'_length"></span>';
			$html += '		</td>';
			$html += '	</tr>';
			$html += '</table>';
			$html += '</div>';
		}
		$html += '</div>';
		$html += '<input id="menuitem_add_menu_id" type="hidden" value="'+$("#menu_id_menu").val()+'" />';
		$html += '<input id="menuitem_add_menuitem_id" type="hidden" value="'+$("#view_menuitems_id_menuitem").val()+'" />';
		if( $("#menuitem_add_dialog").length == 0 ) $("body").append('<div id="menuitem_add_dialog" style="display:none;"></div>');
		$("#menuitem_add_dialog").html($html);
		$("#menuitem_add_local").bind("change", function() { menuitem_add_local_change(); } );
		$("#menuitem_add_icon").bind("change", function() { menuitem_add_image_change(); } );
		$(function() { $("#menuitem_add_link").autocomplete({ source: $global_files }) });
		$(function() { $("#menuitem_add_icon").autocomplete({ source: $icon_files }) });
		$('.ui-autocomplete').on('click', '.ui-menu-item', function()
		{
		    $('.college').trigger('click');
		});
		
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
			width:850
		});
		wait_dialog_hide();	
	}

	function menuitem_add_save($xml)
	{
		if( typeof $xml === "undefined" )
		{
			var $postdata=new Object();
			$postdata=get_values("input, textarea, select", "menuitem_add_");
			$postdata["API"]="cms";
			$postdata["APIRequest"]="MenuitemAdd";
			wait_dialog_show("Speichere Menüpunkt", 100);
			soa2($postdata, "menuitem_add_save");
			return;
		}

		$("#menuitem_add_dialog").dialog("close");
		show_status("Menüpunkt erfolgreich hinzugefügt.");
		wait_dialog_hide();
		delete $cms_menuitems;
		delete $cms_menuitems_languages;
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
		$html += '	<li><a href="#menuitem_edit_tab">Allgemein</a></li>';
		for($i=0; $i<$cms_languages.length; $i++)
		{
			$html += '	<li><a href="#menuitem_edit_tab'+$i+'">'+$cms_languages[$i]["language"]+'</a></li>';
		}
		$html += '</ul>';
		$html += '<div id="menuitem_edit_tab">';
		$html += '<table>';
		$html += '	<tr>';
		$html += '		<td>Oberkategorie</td>';
		$html += '		<td>';
		$html += '			<select id="menuitem_edit_menuitem_id">';
		$html += '				<option value="0">kein Unterpunkt</option>';
		for($i=0; $i<$cms_menuitems.length; $i++)
		{
			var $title="";
			for($j=0; $j<$cms_menuitems_languages.length; $j++)
			{
				if( $cms_menuitems_languages[$j]["language_id"]==<?php echo $_SESSION["id_language"]; ?> && $cms_menuitems_languages[$j]["menuitem_id"]==$cms_menuitems[$i]["id_menuitem"] )
				{
					$title=$cms_menuitems_languages[$j]["title"];
				}
				if( $title=="" && $cms_menuitems_languages[$j]["menuitem_id"]==$cms_menuitems[$i]["id_menuitem"] )
				{
					$title=$cms_menuitems_languages[$j]["title"];
				}
				if( $title!="" ) break;
			}
			$html += '				<option value="'+$cms_menuitems[$i]["id_menuitem"]+'">'+$title+'</option>';
		}
		$html += '			</select>';
		$html += '		</td>';
		$html += '	</tr>';
		$html += '	<tr>';
		$html += '		<td>Startseite</td>';
		$html += '		<td>';
		$html += '			<select id="menuitem_edit_home">';
		$html += '				<option value="0">Nein</option>';
		$html += '				<option value="1">Ja</option>';
		$html += '			</select>';
		$html += '		</td>';
		$html += '	</tr>';
		$html += '	<tr>';
		$html += '		<td>Dynamischer Link</td>';
		$html += '		<td>';
		$html += '			<select id="menuitem_edit_dynamic">';
		$html += '				<option value="0">Nein</option>';
		$html += '				<option value="1">Ja</option>';
		$html += '			</select>';
		$html += '		</td>';
		$html += '	</tr>';
		$html += '	<tr>';
		$html += '		<td>Lokal</td>';
		$html += '		<td>';
		$html += '			<select id="menuitem_edit_local">';
		$html += '				<option value="0">Nein</option>';
		$html += '				<option value="1">Ja</option>';
		$html += '			</select>';
		$html += '		</td>';
		$html += '	</tr>';
		$html += '	<tr>';
		$html += '		<td>Link</td>';
		$html += '		<td><input id="menuitem_edit_link" style="width:300px;" type="text" value="" /></td>';
		$html += '	</tr>';
		$html += '	<tr>';
		$html += '		<td>Icon</td>';
		$html += '		<td>';
		$html += '			<input id="menuitem_edit_icon" style="width:250px;" type="text" value="" />';
		$html += '			<span id="menuitem_add_image"></span>';
		$html += '		</td>';
		$html += '	</tr>';
		$html += '</table>';
		$html += '</div>';
		for($i=0; $i<$cms_languages.length; $i++)
		{
			$html += '<div id="menuitem_edit_tab'+$i+'">';
			$html += '<table>';
			$html += '	<tr>';
			$html += '		<td style="vertical-align:top;">';
			$html += '			<label>Titel</label><br />';
			$html += '			<input id="menuitem_edit_title'+$cms_languages[$i]["id_language"]+'" style="width:300px;" type="text" value="" />';
			$html += '		</td>';
			$html += '		<td style="vertical-align:top;">';
			$html += '			<label>Meta-Titel</label><br />';
			$html += '			<input id="menuitem_edit_meta_title'+$cms_languages[$i]["id_language"]+'" style="width:300px;" type="text" value="" onkeyup="textwidth(\'menuitem_edit_meta_title'+$cms_languages[$i]["id_language"]+'\', \'menuitem_edit_meta_title'+$cms_languages[$i]["id_language"]+'_length\', 512, 18);" />';
			$html += '			<br /><span id="menuitem_edit_meta_title'+$cms_languages[$i]["id_language"]+'_length"></span>';
			$html += '		</td>';
			$html += '	</tr>';
			$html += '	<tr>';
			$html += '		<td style="vertical-align:top;">';
			$html += '			<label>Beschreibung</label><br />';
			$html += '			<textarea id="menuitem_edit_description'+$cms_languages[$i]["id_language"]+'" style="width:300px; height:100px;"></textarea>';
			$html += '		</td>';
			$html += '		<td style="vertical-align:top;">';
			$html += '			<label>Meta-Description</label><br />';
			$html += '			<textarea id="menuitem_edit_meta_description'+$cms_languages[$i]["id_language"]+'" style="width:300px; height:100px;" onkeyup="textwidth(\'menuitem_edit_meta_description'+$cms_languages[$i]["id_language"]+'\', \'menuitem_edit_meta_description'+$cms_languages[$i]["id_language"]+'_length\', 923, 13);"></textarea>';
			$html += '			<br /><span id="menuitem_edit_meta_description'+$cms_languages[$i]["id_language"]+'_length"></span>';
			$html += '		</td>';
			$html += '	</tr>';
			$html += '	<tr>';
			$html += '		<td style="vertical-align:top;">';
			$html += '			<label>Alias</label><br />';
			$html += '			<input id="menuitem_edit_alias'+$cms_languages[$i]["id_language"]+'" style="width:300px;" type="text" value="" onkeyup="alias_preview(\'menuitem_edit_alias'+$cms_languages[$i]["id_language"]+'\', \'menuitem_edit_alias'+$cms_languages[$i]["id_language"]+'_preview\');" />';
			$html += '			<br /><span id="menuitem_edit_alias'+$cms_languages[$i]["id_language"]+'_preview"></span>';
			$html += '		</td>';
			$html += '		<td style="vertical-align:top;">';
			$html += '			<label>Meta-Keywords</label><br />';
			$html += '			<input id="menuitem_edit_meta_keywords'+$cms_languages[$i]["id_language"]+'" style="width:300px;" type="text" value="" onkeyup="textlength(\'menuitem_edit_meta_keywords'+$cms_languages[$i]["id_language"]+'\', \'menuitem_edit_meta_keywords'+$cms_languages[$i]["id_language"]+'_length\', 300);" />';
			$html += '			<br /><span id="menuitem_edit_meta_keywords'+$cms_languages[$i]["id_language"]+'_length"></span>';
			$html += '		</td>';
			$html += '	</tr>';
			$html += '</table>';
			$html += '</div>';
		}
		$html += '</div>';
//		$html += '<input id="menuitem_edit_menu_id" type="hidden" value="'+$("#menu_id_menu").val()+'" />';
		$html += '<input id="menuitem_edit_menuitem_id" type="hidden" value="'+$("#view_menuitems_id_menuitem").val()+'" />';
		if( $("#menuitem_edit_dialog").length == 0 ) $("body").append('<div id="menuitem_edit_dialog" style="display:none;"></div>');
		$("#menuitem_edit_dialog").html($html);

		//update general field values
		for($j=0; $j<$cms_menuitems.length; $j++)
		{
			if( $cms_menuitems[$j]["id_menuitem"]==$id_menuitem ) break;
		}
		$("#menuitem_edit_menuitem_id").val($cms_menuitems[$j]["menuitem_id"]);
		$("#menuitem_edit_home").val($cms_menuitems[$j]["home"]);
		$("#menuitem_edit_dynamic").val($cms_menuitems[$j]["dynamic"]);
		$("#menuitem_edit_local").val($cms_menuitems[$j]["local"]);
		$("#menuitem_edit_link").val($cms_menuitems[$j]["link"]);
		$("#menuitem_edit_icon").val($cms_menuitems[$j]["icon"]);
		
		//update language field values
		for($i=0; $i<$cms_languages.length; $i++)
		{
			for($j=0; $j<$cms_menuitems_languages.length; $j++)
			{
				if( $cms_menuitems_languages[$j]["menuitem_id"]==$id_menuitem )
				{
					if( $cms_menuitems_languages[$j]["language_id"]==$cms_languages[$i]["id_language"] )
					{
						$("#menuitem_edit_title"+$cms_languages[$i]["id_language"]).val($cms_menuitems_languages[$j]["title"]);
						$("#menuitem_edit_description"+$cms_languages[$i]["id_language"]).val($cms_menuitems_languages[$j]["description"]);
						$("#menuitem_edit_alias"+$cms_languages[$i]["id_language"]).val($cms_menuitems_languages[$j]["alias"]);
						$("#menuitem_edit_meta_title"+$cms_languages[$i]["id_language"]).val($cms_menuitems_languages[$j]["meta_title"]);
						$("#menuitem_edit_meta_description"+$cms_languages[$i]["id_language"]).val($cms_menuitems_languages[$j]["meta_description"]);
						$("#menuitem_edit_meta_keywords"+$cms_languages[$i]["id_language"]).val($cms_menuitems_languages[$j]["meta_keywords"]);
					}
				}
			}
		}

		//add events handlers
		$("#menuitem_edit_local").bind("change", function() { menuitem_edit_local_change(); } );
		$("#menuitem_edit_icon").bind("change", function() { menuitem_edit_image_change(); } );
		$(function() { $("#menuitem_edit_link").autocomplete({ source: $global_files }) });
		$(function() { $("#menuitem_edit_icon").autocomplete({ source: $icon_files }) });
		menuitem_edit_local_change();
		
		//show dialog
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
			width:850
		});
		wait_dialog_hide();	
	}


	function menuitem_edit_local_change()
	{
		if($("#menuitem_edit_local").val()==0)
		{
			$( "#menuitem_edit_link" ).autocomplete('option', 'source', $global_files)
		}
		else
		{
			$( "#menuitem_edit_link" ).autocomplete('option', 'source', $local_files)
		}
	}
	
	
	function menuitem_edit_image_change()
	{
		var $src=$("#menuitem_edit_icon").val();
		if( $src.indexOf(".") >-1 )
		{
			$("#menuitem_edit_image").html('<img src="<?php echo PATH; ?>'+$src+'" style="float:right;" />');
		}
	}


	function menuitem_edit_save()
	{
		var $postdata=new Object();
		$postdata=get_values("input, textarea, select", "menuitem_edit_");
		$postdata["API"]="cms";
		$postdata["APIRequest"]="MenuitemEdit";
		soa2($postdata, "menuitem_edit_save2");
	}


	function menuitem_edit_save2($xml)
	{
		delete $cms_menuitems;
		delete $cms_menuitems_languages;
		$("#menuitem_edit_dialog").dialog("close");
		view_menuitems();
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
		$html += '<div id="view_tabs">';
		$html += '	<ul>';
		$html += '		<li><a href="#view_menus_tab">Menüs</a></li>';
		$html += '		<li><a id="view_optimization_tab_link" href="#view_optimization_tab">SEO-Optimierung</a></li>';
		$html += '	</ul>';
		$html += '	<div id="view_menus_tab">';
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
		$html += '		</table>';
		$html += '		<input id="menu_id_menu" type="hidden" value="'+$id_menu+'">';
		$html += '		<input id="menuitem_edit_id_menuitem" type="hidden" value="">';
		$html += '		<div id="view_menuitem" style="float:left;"></div>';
		$html += '	</div>';
		$html += '	<div id="view_optimization_tab"></div>';
		$html += '</div>';
		$("#view").html($html);
		$("#view_tabs").tabs();
		$("#view_optimization_tab_link").bind("click", function(event, ui) { view_optimization_tab(); } );
		wait_dialog_hide();
		view_menuitems();
	}
	
	
	function view_optimization_tab($xml)
	{
		if( typeof $xml === "undefined" )
		{
			$postdata=new Object();
			$postdata["API"]="cms";
			$postdata["APIRequest"]="MenuitemOptimization";
			$postdata["id_language"]=<?php echo $_SESSION["id_language"]; ?>;
			soa2($postdata, "view_optimization_tab");
			return;
		}
		
		var $html = '';
		//progress bar
		var $total=$xml.find("ItemsTotal").text();
		var $optimized=$xml.find("ItemsOptimized").text();
		var $progress=Math.round($optimized/$total*10000)/100;
		$html += '<div id="optimization_progress_wrapper" style="position:relative;">';
		$html += '	<div id="optimization_progress" style="width:100%;"></div>';
		$html += '	<div id="optimization_progress_status" style="width:100%; position:absolute; left:0; top:5px; text-align:center;">'+$optimized+' / '+$total+'</div>';
		$html += '</div>';
		
		//table
		$html += '<table>';
		$html += '	<tr>';
		$html += '		<th>Nr.</th>';
		$html += '		<th>Titel</th>';
		$html += '		<th>Grund</th>';
		$html += '		<th>Optionen</th>';
		$html += '	</tr>';
		$i=0;
		$xml.find("Menuitem").each(function()
		{
			$i++;
			if($i>=21) return;
			$html += '	<tr>';
			$html += '		<td>'+$i+'</td>';
			$html += '		<td>'+$(this).find("Title").text()+'</td>';
			$html += '		<td>'+$(this).find("Reason").text()+'</td>';
			$html += '		<td>';
			$html += '			<img alt="Menüpunkt bearbeiten" onclick="menuitem_edit('+$(this).find("menuitem_id").text()+');" src="<?php echo PATH; ?>images/icons/24x24/edit.png" style="cursor:pointer;" title="Menüpunkt bearbeiten" />';
			$html += '		</td>';
			$html += '	</tr>';
		});
		$html += '</table>';
		$("#view_optimization_tab").html($html);
		$(function() {
			$("#optimization_progress").progressbar({
				value: $progress
			});
		});
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
		var $in="";
		for($i=0; $i<$cms_menuitems.length; $i++)
		{
			if( $in != "" ) $in+=', ';
			$in += $cms_menuitems[$i]["id_menuitem"];
		}
		if ( $in != "" )
		{
			if(table_data_select("cms_menuitems_languages", "*", "WHERE menuitem_id IN("+$in+");", "dbweb", "$cms_menuitems_languages", "view_menuitems")) return;
		}
		wait_dialog_show("Zeichne Menüpunkte", 100);
		var $html = '';
		$html += '<div style="margin:5px; float:left;">'+view_menuitem_path($id_menuitem, "")+'</div>';
		$html += '<br style="clear:both;" />';
		$html += '<table class="hover" id="menuitem_edit" style="margin:5px; float:left;">';
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
				if( $cms_menus[$i]["id_menu"]==$('#menu_id_menu').val() )
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
				var $title="";
				for($j=0; $j<$cms_menuitems_languages.length; $j++)
				{
					if( $cms_menuitems_languages[$j]["language_id"]==<?php echo $_SESSION["id_language"]; ?> && $cms_menuitems_languages[$j]["menuitem_id"]==$cms_menuitems[$i]["id_menuitem"] )
					{
						$title=$cms_menuitems_languages[$j]["title"];
					}
					if( $title=="" && $cms_menuitems_languages[$j]["menuitem_id"]==$cms_menuitems[$i]["id_menuitem"] )
					{
						$title=$cms_menuitems_languages[$j]["title"];
					}
					if( $title!="" ) break;
				}
				$path=' &gt; <a href="javascript:view_menuitems('+$cms_menuitems[$i]["menu_id"]+', '+$id_menuitem+');">'+$title+'</a>'+$path;
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

/*
	//copy links to cms_menuitems_languages
	$results=q("SELECT * FROM cms_menuitems;", $dbweb, __FILE__, __LINE__);
	while( $row=mysqli_fetch_assoc($results) )
	{
		$results2=q("SELECT * FROM cms_menuitems_languages WHERE language_id=1 AND menuitem_id=".$row["id_menuitem"].";", $dbweb, __FILE__, __LINE__);
		if( mysqli_num_rows($results2)==0 )
		{
			$data=$row;
			unset($data["id_menuitem"]);
			unset($data["link"]);
			unset($data["dynamic"]);
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


	//copy translated links to cms_menuitems_languages
/*
	$results=q("SELECT * FROM cms_menuitems;", $dbweb, __FILE__, __LINE__);
	while( $row=mysqli_fetch_assoc($results) )
	{
		$results3=q("SELECT * FROM cms_languages;", $dbweb, __FILE__, __LINE__);
		while( $cms_languages=mysqli_fetch_array($results3) )
		{
			$results2=q("SELECT * FROM cms_menuitems_languages WHERE language_id=".$cms_languages["id_language"]." AND menuitem_id=".$row["id_menuitem"].";", $dbweb, __FILE__, __LINE__);
			if( mysqli_num_rows($results2)==0 )
			{
				$data=$row;
				unset($data["id_menuitem"]);
				unset($data["link"]);
				unset($data["dynamic"]);
				unset($data["local"]);
				unset($data["ordering"]);
				unset($data["icon"]);
				unset($data["menu_id"]);
				unset($data["hide"]);
				$data["language_id"]=$cms_languages["id_language"];
				$data["menuitem_id"]=$row["id_menuitem"];
//				print_r($data);
//				echo '<br><br>';
				$data["title"]=t($data["title"], __FILE__, __LINE__, $cms_languages["code"]);
				$data["description"]=t($data["description"], __FILE__, __LINE__, $cms_languages["code"]);
//				print_r($data);
				q_insert("cms_menuitems_languages", $data, $dbweb, _FILE__, __LINE__);
//				exit;
			}
		}
	}
*/


	include("templates/".TEMPLATE_BACKEND."/footer.php");
?>
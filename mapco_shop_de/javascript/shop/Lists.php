<?php
	include("../../config.php");
	header('Content-type: text/javascript');
	
	//make dreamweaver highlight javascript
	if(true==false) { ?> 	<script type="text/javascript"> <?php }
?>

	function column_add($id_list)
	{
		//initialize
		delete $shop_fields;
		delete $shop_fields_values;

    	if( $("#column_add_dialog").length==0 )
        {
			$html  = '<div id="column_add_dialog"></div>';
            $("body").append($html);
        }

		var $html = '';
		$html += '		<table style="width:100%;">';
		$html += '			<tr>';
		$html += '				<td>Feld</td>';
		$html += '				<td><select id="column_add_field_id" onchange="column_add_view();"></select></td>';
		$html += '			</tr>';
		$html += '			<tr>';
		$html += '				<td>Wert</td>';
		$html += '				<td><select id="column_add_value_id"></select></td>';
		$html += '			</tr>';
		$html += '			<tr>';
		$html += '				<td>Spaltentitel</td>';
		$html += '				<td><input id="column_add_title" type="text" value=""></select></td>';
		$html += '			</tr>';
		$html += '		</table>';
		$html += '	<input type="hidden" id="column_add_list_id" value="'+$id_list+'" />';
		$("#column_add_dialog").html($html);

		column_add1($id_list);
	}

	function column_add1()
	{
		wait_dialog_show("Lese Listenfelder aus", 0);
		if(table_data_select("shop_fields", "*", "ORDER BY title", "dbshop", "$shop_fields", "column_add1")) return;
		wait_dialog_show("Lese Listenwerte aus", 50);
		if(table_data_select("shop_fields_values", "*", "", "dbshop", "$shop_fields_values", "column_add1")) return;
		wait_dialog_hide();

		$("#column_add_dialog").dialog
		({	buttons:
			[
				{ text: "Hinzufügen", click: function() { column_add_save(); } },
				{ text: "Abbrechen", click: function() { $(this).dialog("close"); } }
			],
			closeText:"Fenster schließen",
			modal:true,
			resizable:false,
			title:"Spalte hinzufügen",
			width:550
		});
		
		column_add_view();
	}
	
	function column_add_view()
	{
		var $id_field=$("#column_add_field_id").val();
		if( $id_field == null ) $id_field=0;
		var $id_value=$("#column_add_value_id").val();
		if( $id_value == null ) $id_value=0;

		if( $id_field==0 )
		{
			$("#column_add_field_id").empty();
			$("#column_add_field_id").append('<option value="0">Bitte auswählen...</option>');
			for($i=0; $i<$shop_fields.length; $i++)
			{
				$("#column_add_field_id").append('<option value="'+$shop_fields[$i]["id_field"]+'">'+$shop_fields[$i]["title"]+'</option>');
			}
		}

		$("#column_add_value_id").empty();
		$("#column_add_value_id").append('<option value="0">Bitte auswählen...</option>');
		for($i=0; $i<$shop_fields_values.length; $i++)
		{
			if( $id_field==$shop_fields_values[$i]["field_id"] )
			{
				$("#column_add_value_id").append('<option value="'+$shop_fields_values[$i]["id_value"]+'">'+$shop_fields_values[$i]["title"]+'</option>');
			}
		}
		if( $("#column_add_value_id option").size()>1) $("#column_add_value_id").prop("disabled", false);
		else $("#column_add_value_id").prop("disabled", true);
	}
	
	
	function column_add_save()
	{
		/*
		var $test=$('#column_add_dialog').find("input[name^='propertyName']")
		alert($test["column_add_list_id"]);
		return;
		*/
		var $postdata=new Object();
		$postdata["API"]="shop";
		$postdata["APIRequest"]="ListFieldAdd";
		$postdata["list_id"]=$("#column_add_list_id").val();
		$postdata["field_id"]=$("#column_add_field_id").val();
		if( $postdata["field_id"] == null ) { alert("Bitte wählen Sie zuerst einen Wert aus."); return; }
		$postdata["value_id"]=$("#column_add_value_id").val();
		if( $postdata["value_id"] == null ) $postdata["value_id"]=0;
		$postdata["title"]=$("#column_add_title").val();
		wait_dialog_show("Spalte wird hinzugefügt...");
		$.post("<?php echo PATH; ?>soa2/", $postdata, function($data)
		{
			wait_dialog_hide();
			try { $xml = $($.parseXML($data)); } catch ($err) { show_status2($err.message); return; }
			$ack = $xml.find("Ack");
			if ( $ack.text()!="Success" ) { show_status2($data); return; }

			$("#column_add_dialog").dialog("close");			
			list_edit5($("#column_add_list_id").val());
		})
	}


	function column_edit($id, $id_list)
	{
		//initialize
		delete $shop_fields;
		delete $shop_fields_values;
		delete $shop_lists_fields;
		delete $cms_languages;

    	if( $("#column_edit_dialog").length==0 )
        {
			$html  = '<div id="column_edit_dialog"></div>';
            $("body").append($html);
        }

		var $html = '';
		$html += '		<table style="width:100%;">';
		$html += '			<tr>';
		$html += '				<td>Feld</td>';
		$html += '				<td><select id="column_edit_field_id" onchange="column_edit_view();"></select></td>';
		$html += '			</tr>';
		$html += '			<tr>';
		$html += '				<td>Wert</td>';
		$html += '				<td><select id="column_edit_value_id" onchange="column_edit_view();"></select></td>';
		$html += '			</tr>';
		$html += '			<tr id="column_edit_language">';
		$html += '				<td>Sprache</td>';
		$html += '				<td><select id="column_edit_language_id"></select></td>';
		$html += '			</tr>';
		$html += '			<tr>';
		$html += '				<td>Spaltentitel</td>';
		$html += '				<td><input id="column_edit_title" type="text" value=""></select></td>';
		$html += '			</tr>';
		$html += '		</table>';
		$html += '	<input type="hidden" id="column_edit_list_id" value="'+$id_list+'" />';
		$html += '	<input type="hidden" id="column_edit_id" value="'+$id+'" />';
		$("#column_edit_dialog").html($html);

		column_edit1();
	}


	function column_edit1()
	{
		if(table_data_select("shop_fields", "*", "ORDER BY title", "dbshop", "$shop_fields", "column_edit1")) return;
		if(table_data_select("shop_fields_values", "*", "ORDER BY title", "dbshop", "$shop_fields_values", "column_edit1")) return;
		if(table_data_select("cms_languages", "*", "", "dbweb", "$cms_languages", "column_edit1")) return;
		var $id=$("#column_edit_id").val();
		if(table_data_select("shop_lists_fields", "*", "WHERE id="+$id, "dbshop", "$shop_lists_fields", "column_edit1")) return;
		$("#column_edit_title").val($shop_lists_fields[0]["title"]);
		
		$("#column_edit_dialog").dialog
		({	buttons:
			[
				{ text: "Speichern", click: function() { column_edit_save(); } },
				{ text: "Abbrechen", click: function() { $(this).dialog("close"); } }
			],
			closeText:"Fenster schließen",
			modal:true,
			resizable:false,
			title:"Spalte bearbeiten",
			width:550
		});
		
		column_edit_view();
	}


	function column_edit_view()
	{
		var $id_field=$("#column_edit_field_id").val();
		if( $id_field == null ) $id_field=$shop_lists_fields[0]["field_id"];
		var $id_value=$("#column_edit_value_id").val();
		if( $id_value == null ) $id_value=$shop_lists_fields[0]["value_id"];
		var $id_language=$("#column_edit_language_id").val();
		if( $id_language == null ) $id_language=$shop_lists_fields[0]["language_id"];

		$("#column_edit_field_id").empty();
		$("#column_edit_field_id").append('<option value="0">Bitte auswählen...</option>');
		for($i=0; $i<$shop_fields.length; $i++)
		{
			if( $id_field==$shop_fields[$i]["id_field"] ) $selected=' selected="selected"'; else $selected='';
			$("#column_edit_field_id").append('<option'+$selected+' value="'+$shop_fields[$i]["id_field"]+'">'+$shop_fields[$i]["title"]+'</option>');
		}

		$("#column_edit_value_id").empty();
		$("#column_edit_value_id").append('<option value="0">Bitte auswählen...</option>');
		delete $id_value_nr;
		for($i=0; $i<$shop_fields_values.length; $i++)
		{
			if( $id_field==$shop_fields_values[$i]["field_id"] )
			{
				if( $id_value==$shop_fields_values[$i]["id_value"] )
				{
					var $id_value_nr=$i;
					$selected=' selected="selected"';
				}
				else $selected='';
				$("#column_edit_value_id").append('<option'+$selected+' value="'+$shop_fields_values[$i]["id_value"]+'">'+$shop_fields_values[$i]["title"]+'</option>');
			}
		}

		$("#column_edit_language_id").empty();
		$("#column_edit_language_id").append('<option value="0">Bitte auswählen...</option>');
		if( typeof $id_value_nr !== "undefined" && $shop_fields_values[$id_value_nr]["multilingual"]>0 )
		{
			$("#column_edit_language").show();
			for($j=0; $j<$cms_languages.length; $j++)
			{
				if( $id_language==$cms_languages[$j]["id_language"] ) $selected=' selected="selected"'; else $selected='';
				$("#column_edit_language_id").append('<option'+$selected+' value="'+$cms_languages[$j]["id_language"]+'">'+$cms_languages[$j]["language"]+'</option>');
			}
		}
		else $("#column_edit_language").hide();
		if( $("#column_edit_value_id option").size()>1) $("#column_edit_value_id").prop("disabled", false);
		else $("#column_edit_value_id").prop("disabled", true);
	}


	function column_edit_save()
	{
		var $postdata=new Object();
		$postdata["API"]="shop";
		$postdata["APIRequest"]="ListFieldEdit";
		$postdata["id"]=$("#column_edit_id").val();
		$postdata["field_id"]=$("#column_edit_field_id").val();
		if( $postdata["field_id"] == null ) { alert("Bitte wählen Sie zuerst einen Wert aus."); return; }
		$postdata["value_id"]=$("#column_edit_value_id").val();
		$postdata["language_id"]=$("#column_edit_language_id").val();
		if( $postdata["value_id"] == null ) $postdata["value_id"]=0;
		$postdata["title"]=$("#column_edit_title").val();
		wait_dialog_show("Spalte wird aktualisiert...");
		$.post("<?php echo PATH; ?>soa2/", $postdata, function($data)
		{
			wait_dialog_hide();
			try { $xml = $($.parseXML($data)); } catch ($err) { show_status2($err.message); return; }
			$ack = $xml.find("Ack");
			if ( $ack.text()!="Success" ) { show_status2($data); return; }

			delete $shop_fields;
			delete $shop_fields_values;
			delete $shop_lists_fields;

			$("#column_edit_dialog").dialog("close");			
			list_edit5($("#column_edit_list_id").val());
		})
	}


	function column_remove($id, $id_list)
	{
		if (!confirm("Wollen Sie die Spalte wirklich löschen?")) return;
		var $postdata=new Object();
		$postdata["API"]="shop";
		$postdata["APIRequest"]="ListFieldRemove";
		$postdata["id"]=$id;
		wait_dialog_show("Spalte wird gelöscht...");
		$.post("<?php echo PATH; ?>soa2/", $postdata, function($data)
		{
			wait_dialog_hide();
			try { $xml = $($.parseXML($data)); } catch ($err) { show_status2($err.message); return; }
			$ack = $xml.find("Ack");
			if ( $ack.text()!="Success" ) { show_status2($data); return; }

			list_edit5($id_list);
		})
	}


	function list_edit($id_list)
	{
		var $html = '';
		$html += '<div id="list_edit_tabs">';
		$html += '	<ul>';
		$html += '		<li><a href="#list_edit_tab1">Allgemein</a></li>';
		$html += '		<li><a href="#list_edit_tab2">Spalten</a></li>';
		$html += '	</ul>';
		$html += '	<div id="list_edit_tab1">';
		$html += '		<table style="width:100%;">';
		$html += '			<tr>';
		$html += '				<td>Titel</td>';
		$html += '				<td><input id="list_edit_title" style="width:300px;" type="text" value="" /></td>';
		$html += '			</tr>';
		$html += '			<tr>';
		$html += '				<td>Listentyp</td>';
		$html += '				<td>';
		$html += '					<select id="list_edit_listtype_id">';
		$html += '					</select>';
		$html += '				</td>';
		$html += '			</tr>';
		$html += '		</table>';
		$html += '		<input type="hidden" id="list_edit_id_list" value="'+$id_list+'" />';
		$html += '	</div>';
		$html += '	<div id="list_edit_tab2">';
		$html += '	</div>';
		$html += '</div>';
		$("#list_edit_dialog").html($html);
		$(function() { $("#list_edit_tabs").tabs(); });

		list_edit1($id_list); //get shop_listtypes
	}
	
	function list_edit1($id_list)
	{
		//get shop_listtypes
		var $postdata=new Object();
		$postdata["API"]="cms";
		$postdata["APIRequest"]="TableDataSelect";
		$postdata["table"]="shop_listtypes";
		$postdata["db"]="dbshop";
		$postdata["where"]="ORDER BY title;";
		wait_dialog_show("Lese Listentypen aus", 20);
		$.post("<?php echo PATH; ?>soa2/", $postdata, function($data)
		{
			try { $xml = $($.parseXML($data)); } catch ($err) { show_status2($err.message); return; }
			$ack = $xml.find("Ack");
			if ( $ack.text()!="Success" ) { show_status2($data); return; }
			
			$xml.find("shop_listtypes").each(function()
			{
				var $id_list=$(this).find("id_listtype").text();
				var $title=$(this).find("title").text();
				$("#list_edit_listtype_id").append('<option value="'+$id_list+'">'+$title+'</option>');
			});
			list_edit2($id_list); //get shop_lists
		});
	}


	function list_edit2($id_list)
	{
		//get shop_lists
		var $postdata=new Object();
		$postdata["API"]="cms";
		$postdata["APIRequest"]="TableDataSelect";
		$postdata["table"]="shop_lists";
		$postdata["db"]="dbshop";
		$postdata["where"]="WHERE id_list="+$id_list;
		wait_dialog_show("Lese Listendaten aus", 40);
		$.post("<?php echo PATH; ?>soa2/", $postdata, function($data)
		{
			try { $xml = $($.parseXML($data)); } catch ($err) { show_status2($err.message); return; }
			$ack = $xml.find("Ack");
			if ( $ack.text()!="Success" ) { show_status2($data); return; }
			
			var $title=$xml.find("title").text();
			$("#list_edit_title").val($title);
			var $listtype_id=$xml.find("listtype_id").text();
			$("#list_edit_listtype_id").val($listtype_id);
			list_edit3($id_list); //get shop_lists_fields
		});
	}
	
	
	function list_edit3($id_list)
	{
		//get shop_fields
		var $postdata=new Object();
		$postdata["API"]="cms";
		$postdata["APIRequest"]="TableDataSelect";
		$postdata["table"]="shop_fields";
		$postdata["db"]="dbshop";
		wait_dialog_show("Lese Listenfelder aus", 60);
		$.post("<?php echo PATH; ?>soa2/", $postdata, function($data)
		{
			try { $xml = $($.parseXML($data)); } catch ($err) { show_status2($err.message); return; }
			$ack = $xml.find("Ack");
			if ( $ack.text()!="Success" ) { show_status2($data); return; }
			
			$xml.find("shop_fields").each(function()
			{
				$id_field=$(this).find("id_field").text();
				$fields[$id_field]=new Array();
				$(this).children().each(function()
				{
					$fields[$id_field][this.tagName]=$(this).text();
				});
			});
			list_edit4($id_list); //get shop_fields_values
		});
	}
	
	
	function list_edit4($id_list)
	{
		//get shop_fields_values
		var $postdata=new Object();
		$postdata["API"]="cms";
		$postdata["APIRequest"]="TableDataSelect";
		$postdata["table"]="shop_fields_values";
		$postdata["db"]="dbshop";
		wait_dialog_show("Lese Listenwerte aus", 70);
		$.post("<?php echo PATH; ?>soa2/", $postdata, function($data)
		{
			try { $xml = $($.parseXML($data)); } catch ($err) { show_status2($err.message); return; }
			$ack = $xml.find("Ack");
			if ( $ack.text()!="Success" ) { show_status2($data); return; }
			
			$xml.find("shop_fields_values").each(function()
			{
				$id_value=$(this).find("id_value").text();
				$values[$id_value]=new Array();
				$(this).children().each(function()
				{
					$values[$id_value][this.tagName]=$(this).text();
				});
			});
			list_edit5(); //get shop_lists_fields
		});
	}
	
	
	function list_edit5()
	{
		wait_dialog_show("Lese Listenprofile aus", 80);
		if(table_data_select("shop_lists_profiles", "*", "", "dbshop", "$shop_lists_profiles", "list_edit5")) return;
		if(table_data_select("cms_languages", "*", "", "dbweb", "$cms_languages", "list_edit5")) return;
		var $id_list=$("#list_edit_id_list").val();

		//get shop_lists_fields
		var $postdata=new Object();
		$postdata["API"]="cms";
		$postdata["APIRequest"]="TableDataSelect";
		$postdata["table"]="shop_lists_fields";
		$postdata["db"]="dbshop";
		$postdata["where"]="WHERE list_id="+$id_list+" ORDER BY ordering;";
		wait_dialog_show("Lese Listenformat aus", 90);
		$.post("<?php echo PATH; ?>soa2/", $postdata, function($data)
		{
			wait_dialog_hide();
			try { $xml = $($.parseXML($data)); } catch ($err) { show_status2($err.message); return; }
			$ack = $xml.find("Ack");
			if ( $ack.text()!="Success" ) { show_status2($data); return; }

			wait_dialog_show("Zeichne Dialog", 100);
			var $html = '';
			$html += '<table style="width:100%;">';
			$html += '	<tr>';
			$html += '		<td>';
			$html += '			<select id="list_profile_add_id_listprofile">';
			$html += '				<option value="0">Listenprofil auswählen...</option>';
			for($i=0; $i<$shop_lists_profiles.length; $i++)
			{
				$html += '	<option value="'+$shop_lists_profiles[$i]["id_listprofile"]+'">'+$shop_lists_profiles[$i]["title"]+'</option>';
			}
			$html += '</select>';
			$html += '		</td>';
			$html += '		<td>';
			$html += '			<img alt="Listenprofil hinzufügen" onclick="list_profile_add();" src="<?php echo PATH; ?>images/icons/24x24/add.png" style="cursor:pointer;" title="Listenprofil hinzufügen">';
			$html += '			<img alt="Listenprofil auf Liste anwenden" onclick="list_profile_change();" src="<?php echo PATH; ?>images/icons/24x24/down.png" style="cursor:pointer;" title="Listenprofil auf Liste anwenden">';
			$html += '		</td>';
			$html += '	<tr>';
			$html += '</table>';
			$html += '<table id="list_edit_fields" style="width:100%;">';
			$html += '	<tr class="unsortable">';
			$html += '		<th>Nr.</th>';
			$html += '		<th>Feld</th>';
			$html += '		<th>Wert</th>';
			$html += '		<th>Sprache</th>';
			$html += '		<th>Bezeichnung</th>';
			$html += '		<th>';
			$html += '			<img alt="Spalte hinzufügen" onclick="column_add('+$id_list+');" src="<?php echo PATH; ?>images/icons/24x24/add.png" style="cursor:pointer;" title="Spalte hinzufügen" />';
			$html += '		</th>';
			$html += '	</tr>';
			$html += '</table>';
			$("#list_edit_tab2").html($html);
			$xml.find("shop_lists_fields").each(function()
			{
				var $id=$(this).find("id").text();
				var $html = '<tr id="'+$id+'">';
				//ordering
				var $ordering=$(this).find("ordering").text();
				$html += '	<td style="cursor:move;">'+$ordering+'</td>';
				//field
				var $field_id=$(this).find("field_id").text();
				$html += '	<td>'+$fields[$field_id]["title"]+'</td>';
				//value
				var $value_id=$(this).find("value_id").text();
				if( $value_id>0 )
				{
					$html += '	<td>'+$values[$value_id]["title"]+'</td>';
				}
				else $html += '	<td></td>';
				var $language_id=$(this).find("language_id").text();
				if( $language_id>0 )
				{
					for($i=0; $i<$cms_languages.length; $i++)
					{
						if( $language_id==$cms_languages[$i]["id_language"] )
						{
							$html += '	<td>'+$cms_languages[$i]["language"]+'</td>';
						}
					}
				}
				else $html += '	<td></td>';
				//title
				var $title=$(this).find("title").text();
				$html += '	<td>'+$title+'</td>';
				//options
				$html += '	<td>';
				$html += '		<img alt="Spalte bearbeiten" onclick="column_edit('+$id+', '+$id_list+');" src="<?php echo PATH; ?>images/icons/24x24/edit.png" style="cursor:pointer;" title="Spalte bearbeiten" />';
				$html += '		<img alt="Spalte löschen" onclick="column_remove('+$id+', '+$id_list+');" src="<?php echo PATH; ?>images/icons/24x24/remove.png" style="cursor:pointer;" title="Spalte löschen" />';
				$html += '	</td>';
				$html += '</tr>';
				$("#list_edit_fields").append($html);
			});
			$(function() {
				$("#list_edit_fields").sortable( { items:"tr:not(.unsortable)" } );
				$("#list_edit_fields").disableSelection();
			});
			$("#list_edit_fields").bind( "sortupdate", function(event, ui)
			{
				var $postdata=new Object();
				$postdata["API"]="shop";
				$postdata["APIRequest"]="ListEditOrdering";
				var $ids = $('#list_edit_fields').sortable('toArray');
				$postdata["ids"]=$ids.join(", ");
				$.post("<?php echo PATH; ?>soa2/", $postdata, function($data)
				{
					list_edit3($id_list);
				});
			});

			list_edit_view();
		});
	}
	

	function list_edit_view()
	{
		wait_dialog_hide();
		$("#list_edit_dialog").dialog
		({	buttons:
			[
				{ text: "Speichern", click: function() { list_edit_save(); } },
				{ text: "Abbrechen", click: function() { $(this).dialog("close"); } }
			],
			closeText:"Fenster schließen",
			modal:true,
			resizable:false,
			title:"Liste bearbeiten",
			width:550
		});
	}


	function list_profile_add()
	{
		var $id_list=$("#list_edit_id_list").val();
		
    	if( $("#list_profile_add_dialog").length==0 )
        {
			$html  = '<div id="list_profile_add_dialog"></div>';
            $("body").append($html);
        }

		var $html = '';
		$html += '		<table style="width:100%;">';
		$html += '			<tr>';
		$html += '				<td>Title</td>';
		$html += '				<td><input id="list_profile_add_title" type="text" value=""></select></td>';
		$html += '			</tr>';
		$html += '		</table>';
		$html += '	<input type="hidden" id="list_profile_add_list_id" value="'+$id_list+'" />';
		$("#list_profile_add_dialog").html($html);

		$("#list_profile_add_dialog").dialog
		({	buttons:
			[
				{ text: "Hinzufügen", click: function() { list_profile_add_save(); } },
				{ text: "Abbrechen", click: function() { $(this).dialog("close"); } }
			],
			closeText:"Fenster schließen",
			modal:true,
			resizable:false,
			title:"Listenprofil hinzufügen",
			width:550
		});
	}


	function list_profile_add_save()
	{
		var $postdata=new Object();
		$postdata["API"]="shop";
		$postdata["APIRequest"]="ListProfileAdd";
		$postdata["title"]=$("#list_profile_add_title").val();
		if( $postdata["title"]=="" )
		{
			alert("Der Titel darf nicht leer sein.");
			return;
		}
		$postdata["id_list"]=$("#list_profile_add_list_id").val();
		wait_dialog_show("Listenprofil wird gespeichert");
		$.post("<?php echo PATH; ?>soa2/", $postdata, function($data)
		{
			wait_dialog_hide();
			try { $xml = $($.parseXML($data)); } catch ($err) { show_status2($err.message); return; }
			$ack = $xml.find("Ack");
			if ( $xml.find("Error").length>0 )
			{
				var $Code=$xml.find("Error Code").text();
				var $shortMsg=$xml.find("Error shortMsg").text();
				var $longMsg=$xml.find("Error longMsg").text();
				alert("Fehler "+$Code+"\n\n"+$longMsg);
				return;
			}
			if ( $ack.text()!="Success" ) { show_status2($data); return; }

			delete $shop_lists_profiles;
			$("#list_profile_add_dialog").dialog("close");			
			list_edit5();
		});
	}


	function list_profile_change()
	{
		if( $("#list_profile_add_id_listprofile").val()==0 ) { alert("Bitte wählen Sie zuerst ein Listenprofil aus."); return; }
		if( !confirm("ACHTUNG: Das bestehende Listenprofil wird durch das ausgewählte Listenprofil ersetzt. Sind Sie sicher?") ) return;
		var $postdata=new Object();
		$postdata["API"]="shop";
		$postdata["APIRequest"]="ListProfileChange";
		$postdata["id_listprofile"]=$("#list_profile_add_id_listprofile").val();
		$postdata["id_list"]=$("#list_edit_id_list").val();
		wait_dialog_show("Listenprofil wird geändert");
		$.post("<?php echo PATH; ?>soa2/", $postdata, function($data)
		{
			wait_dialog_hide();
			try { $xml = $($.parseXML($data)); } catch ($err) { show_status2($err.message); return; }
			$ack = $xml.find("Ack");
			if ( $xml.find("Error").length>0 )
			{
				var $Code=$xml.find("Error Code").text();
				var $shortMsg=$xml.find("Error shortMsg").text();
				var $longMsg=$xml.find("Error longMsg").text();
				alert("Fehler "+$Code+"\n\n"+$longMsg);
				return;
			}
			if ( $ack.text()!="Success" ) { show_status2($data); return; }

			list_edit5();
		});
	}
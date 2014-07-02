<?php
	include("config.php");
	include("templates/".TEMPLATE_BACKEND."/header.php");
?>
<script type="text/javascript" src="<?php echo PATH; ?>javascript/cms/ContactImageUpload.php"></script>
<script>
	var id_location=0;
	var id_department=0;
	var id_contact=0;
	
	function contact_add(id_department)
	{
		$("#contact_add_firstname").val("");
		$("#contact_add_lastname").val("");
		$("#contact_add_position").val("");
		$("#contact_add_languages").val("");
		$("#contact_add_phone").val("");
		$("#contact_add_fax").val("");
		$("#contact_add_mobile").val("");
		$("#contact_add_mail").val("");
		$("#contact_add_gender").val("");
		$("#contact_add_active").val("");
		$("#contact_add_id_department").val(id_department);
		$("#contact_add_dialog").dialog
		({	buttons:
			[
				{ text: "Speichern", click: function() { contact_add_save(); } },
				{ text: "Abbrechen", click: function() { $(this).dialog("close"); } }
			],
			closeText:"Fenster schließen",
			hide: { effect: 'drop', direction: "up" },
			modal:true,
			resizable:false,
			show: { effect: 'drop', direction: "up" },
			title:"Kontakt hinzufügen",
		});
	}

	function contact_add_save()
	{
		var firstname=$("#contact_add_firstname").val();
		var lastname=$("#contact_add_lastname").val();
		var position=$("#contact_add_position").val();
		var languages=$("#contact_add_languages").val();
		var phone=$("#contact_add_phone").val();
		var fax=$("#contact_add_fax").val();
		var mobile=$("#contact_add_mobile").val();
		var mail=$("#contact_add_mail").val();
		var gender=$("#contact_add_gender").val();
		var active=$("#contact_add_active").val();
		var id_department=$("#contact_add_id_department").val();
		wait_dialog_show();
		$.post("<?php echo PATH; ?>soa/", { API:"cms", Action:"ContactsContactAdd", firstname:firstname, lastname:lastname, position:position, languages:languages, phone:phone, fax:fax, mobile:mobile, mail:mail, department_id:id_department, gender:gender, active:active },
			function(data)
			{
				wait_dialog_hide();
				$xml = $($.parseXML(data));
				$ack = $xml.find("Ack");
				if ( $ack.text()!="Success" )
				{
					show_status2(data);
					return;
				}

				show_status("Der Kontakt wurde erfolgreich angelegt.");
				contacts_view();
				$("#contact_add_dialog").dialog("close");
			}
		);
	}

	function contact_edit(id_contact, firstname, lastname, position, languages, phone, fax, mobile, mail, gender, active)
	{
		$("#contact_edit_id_contact").val(id_contact);
		$("#contact_edit_firstname").val(firstname);
		$("#contact_edit_lastname").val(lastname);
		$("#contact_edit_position").val(position);
		$("#contact_edit_languages").val(languages);
		$("#contact_edit_phone").val(phone);
		$("#contact_edit_fax").val(fax);
		$("#contact_edit_mobile").val(mobile);
		$("#contact_edit_mail").val(mail);
		$("#contact_edit_gender option[value='"+gender+"']").attr('selected',true);
		$("#contact_edit_active option[value='"+active+"']").attr('selected',true);
		$("#contact_edit_dialog").dialog
		({	buttons:
			[
				{ text: "Speichern", click: function() { contact_edit_save(); } },
				{ text: "Abbrechen", click: function() { $(this).dialog("close"); } }
			],
			closeText:"Fenster schließen",
			hide: { effect: 'drop', direction: "up" },
			modal:true,
			resizable:false,
			show: { effect: 'drop', direction: "up" },
			title:"Kontakt bearbeiten",
		});
	}

	function contact_edit_save()
	{
		var firstname=$("#contact_edit_firstname").val();
		var lastname=$("#contact_edit_lastname").val();
		var position=$("#contact_edit_position").val();
		var languages=$("#contact_edit_languages").val();
		var phone=$("#contact_edit_phone").val();
		var fax=$("#contact_edit_fax").val();
		var mobile=$("#contact_edit_mobile").val();
		var mail=$("#contact_edit_mail").val();
		var gender=$("#contact_edit_gender").val();
		var active=$("#contact_edit_active").val();
		var id_contact=$("#contact_edit_id_contact").val();
		wait_dialog_show();
		$.post("<?php echo PATH; ?>soa/", { API:"cms", Action:"ContactsContactEdit", id_contact:id_contact, firstname:firstname, lastname:lastname, position:position, languages:languages, phone:phone, fax:fax, mobile:mobile, mail:mail, gender:gender, active:active },
			function(data)
			{
				wait_dialog_hide();
				$xml = $($.parseXML(data));
				$ack = $xml.find("Ack");
				if ( $ack.text()!="Success" )
				{
					show_status2(data);
					return;
				}

				show_status("Der Kontakt wurde erfolgreich geändert.");
				contacts_view();
				$("#contact_edit_dialog").dialog("close");
			}
		);
	}

	function contact_remove(id_contact)
	{
		$("#contact_remove_id_contact").val(id_contact);
		$("#contact_remove_dialog").dialog
		({	buttons:
			[
				{ text: "Löschen", click: function() { contact_remove_accept(); } },
				{ text: "Abbrechen", click: function() { $(this).dialog("close"); } }
			],
			closeText:"Fenster schließen",
			hide: { effect: 'drop', direction: "up" },
			modal:true,
			resizable:false,
			show: { effect: 'drop', direction: "up" },
			title:"Kontakt löschen",
		});
	}
	
	function contact_search()
	{
		var $search=$("#contact_search").val();
		if( $search=="" ) return;
		
		$.post("<?php echo PATH; ?>soa/", { API:"cms", Action:"ContactsContactSearch", search:$search }, function($data)
		{
			//stop if search string is obsolete
			$search2=$("#contact_search").val();
			if( $search!=$search2 ) return;

			$xml = $($.parseXML($data));
			if ( $xml.find("Ack").text()!="Success" ) show_status2($data);
			
			var $html='';
			$xml.find("Contact").each(function()
			{
				var $id_contact=$(this).find("id_contact").text();
				var $id_department=$(this).find("department_id").text();
				var $id_location=$(this).find("location_id").text();
				var $firstname=$(this).find("firstname").text();
				var $lastname=$(this).find("lastname").text();
				var $position=$(this).find("position").text();
				$html += '<a href="javascript:contact_view('+$id_contact+', '+$id_department+', '+$id_location+');" style="margin:5px; float:left;">'+$firstname+' '+$lastname+', '+$position+'</a><br />';
			});
			$("#contact_search_results").html($html);
			$("#contact_search_results").show();
		});
	}
	
	function contact_view($id_contact, $id_department, $id_location)
	{
		$("#contact_search_results").html("");
		$("#contact_search_results").hide();
		id_location=$id_location;
		id_department=$id_department;
		id_contact=$id_contact;
		contacts_view();
	}

	function contact_remove_accept()
	{
		var id_contact=$("#contact_remove_id_contact").val();
		wait_dialog_show();
		$.post("<?php echo PATH; ?>soa/", { API:"cms", Action:"ContactsContactRemove", id_contact:id_contact },
			function(data)
			{
				wait_dialog_hide();
				$xml = $($.parseXML(data));
				$ack = $xml.find("Ack");
				if ( $ack.text()!="Success" )
				{
					show_status2(data);
					return;
				}

				show_status("Der Kontakt wurde erfolgreich gelöscht.");
				contacts_view();
				$("#contact_remove_dialog").dialog("close");
			}
		);
	}

	function department_add(id_location)
	{
		$("#department_add_department").val("");
		$("#department_add_id_location").val(id_location);
		$("#department_add_dialog").dialog
		({	buttons:
			[
				{ text: "Speichern", click: function() { department_add_save(); } },
				{ text: "Abbrechen", click: function() { $(this).dialog("close"); } }
			],
			closeText:"Fenster schließen",
			hide: { effect: 'drop', direction: "up" },
			modal:true,
			resizable:false,
			show: { effect: 'drop', direction: "up" },
			title:"Abteilung hinzufügen",
		});
	}

	function department_add_save()
	{
		var id_location=$("#department_add_id_location").val();
		var department=$("#department_add_department").val();
		wait_dialog_show();
		$.post("<?php echo PATH; ?>soa/", { API:"cms", Action:"ContactsDepartmentAdd", location_id:id_location, department:department },
			function(data)
			{
				wait_dialog_hide();
				$xml = $($.parseXML(data));
				$ack = $xml.find("Ack");
				if ( $ack.text()!="Success" )
				{
					show_status2(data);
					return;
				}

				show_status("Abteilung erfolgreich hinzugefügt.");
				contacts_view();
				$("#department_add_dialog").dialog("close");
			}
		);
	}

	function department_edit(id_department, department)
	{
		$("#department_edit_id_department").val(id_department);
		$("#department_edit_department").val(department);
		$("#department_edit_dialog").dialog
		({	buttons:
			[
				{ text: "Speichern", click: function() { department_edit_save(); } },
				{ text: "Abbrechen", click: function() { $(this).dialog("close"); } }
			],
			closeText:"Fenster schließen",
			hide: { effect: 'drop', direction: "up" },
			modal:true,
			resizable:false,
			show: { effect: 'drop', direction: "up" },
			title:"Abteilung bearbeiten",
		});
	}

	function department_edit_save()
	{
		var id_department=$("#department_edit_id_department").val();
		var department=$("#department_edit_department").val();
		wait_dialog_show();
		$.post("<?php echo PATH; ?>soa/", { API:"cms", Action:"ContactsDepartmentEdit", id_department:id_department, department:department },
			function(data)
			{
				wait_dialog_hide();
				$xml = $($.parseXML(data));
				$ack = $xml.find("Ack");
				if ( $ack.text()!="Success" )
				{
					show_status2(data);
					return;
				}

				show_status("Abteilung erfolgreich geändert.");
				contacts_view();
				$("#department_edit_dialog").dialog("close");
			}
		);
	}

	function department_remove(id_department)
	{
		$("#department_remove_id_department").val(id_department);
		$("#department_remove_dialog").dialog
		({	buttons:
			[
				{ text: "Löschen", click: function() { department_remove_accept(); } },
				{ text: "Abbrechen", click: function() { $(this).dialog("close"); } }
			],
			closeText:"Fenster schließen",
			hide: { effect: 'drop', direction: "up" },
			modal:true,
			resizable:false,
			show: { effect: 'drop', direction: "up" },
			title:"Abteilung löschen",
		});
	}

	function department_remove_accept()
	{
		var id_department=$("#department_remove_id_department").val();
		wait_dialog_show();
		$.post("<?php echo PATH; ?>soa/", { API:"cms", Action:"ContactsDepartmentRemove", id_department:id_department },
			function(data)
			{
				wait_dialog_hide();
				$xml = $($.parseXML(data));
				$ack = $xml.find("Ack");
				if ( $ack.text()!="Success" )
				{
					show_status2(data);
					return;
				}

				show_status("Abteilung erfolgreich gelöscht.");
				contacts_view();
				$("#department_remove_dialog").dialog("close");
			}
		);
	}

	function location_add()
	{
		$("#location_add_location").val("");
		$("#location_add_company").val("");
		$("#location_add_title").val("");
		$("#location_add_firstname").val("");
		$("#location_add_lastname").val("");
		$("#location_add_street").val("");
		$("#location_add_streetnr").val("");
		$("#location_add_zipcode").val("");
		$("#location_add_city").val("");
		$("#location_add_country_code").val("DE");
		$("#location_add_phone").val("");
		$("#location_add_fax").val("");
		$("#location_add_website").val("");
		$("#location_add_mail").val("");
		$("#location_add_monday").val("");
		$("#location_add_tuesday").val("");
		$("#location_add_wednesday").val("");
		$("#location_add_thursday").val("");
		$("#location_add_friday").val("");
		$("#location_add_saturday").val("");
		$("#location_add_sunday").val("");
		$("#location_add_dialog").dialog
		({	buttons:
			[
				{ text: "Speichern", click: function() { location_add_save(); } },
				{ text: "Abbrechen", click: function() { $(this).dialog("close"); } }
			],
			closeText:"Fenster schließen",
			hide: { effect: 'drop', direction: "up" },
			modal:true,
			resizable:false,
			show: { effect: 'drop', direction: "up" },
			title:"Standort hinzufügen",
			width:700
		});
	}

	function location_add_save()
	{
		var $postdata=new Object();
		$postdata["API"]="cms";
		$postdata["Action"]="ContactsLocationAdd";
		$postdata["location"]=$("#location_add_location").val();
		$postdata["company"]=$("#location_add_company").val();
		$postdata["title"]=$("#location_add_title").val();
		$postdata["firstname"]=$("#location_add_firstname").val();
		$postdata["lastname"]=$("#location_add_lastname").val();
		$postdata["street"]=$("#location_add_street").val();
		$postdata["streetnr"]=$("#location_add_streetnr").val();
		$postdata["zipcode"]=$("#location_add_zipcode").val();
		$postdata["city"]=$("#location_add_city").val();
		$postdata["country"]=$("#location_add_country_code option:selected").text();
		$postdata["country_code"]=$("#location_add_country_code").val();
		$postdata["phone"]=$("#location_add_phone").val();
		$postdata["fax"]=$("#location_add_phone").val();
		$postdata["website"]=$("#location_add_website").val();
		$postdata["mail"]=$("#location_add_mail").val();
		$postdata["monday"]=$("#location_add_monday").val();
		$postdata["tuesday"]=$("#location_add_tuesday").val();
		$postdata["wednesday"]=$("#location_add_wednesday").val();
		$postdata["thursday"]=$("#location_add_thursday").val();
		$postdata["friday"]=$("#location_add_friday").val();
		$postdata["saturday"]=$("#location_add_saturday").val();
		$postdata["sunday"]=$("#location_add_sunday").val();
		wait_dialog_show();
		$.post("<?php echo PATH ?>soa/", $postdata, function(data)
		{
			wait_dialog_hide();
			$xml = $($.parseXML(data));
			$ack = $xml.find("Ack");
			if ( $ack.text()!="Success" )
			{
				show_status2(data);
				return;
			}

			show_status("Standort erfolgreich hinzugefügt.");
			contacts_view();
			$("#location_add_dialog").dialog("close");
		});
	}

	function location_edit(id_location, location, company, title, firstname, lastname, street, streetnr, zipcode, city, country, country_code, phone, fax, website, mail, monday, tuesday, wednesday, thursday, friday, saturday, sunday)
	{
		$("#location_edit_id_location").val(id_location);
		$("#location_edit_location").val(location);
		$("#location_edit_company").val(company);
		$("#location_edit_title").val(title);
		$("#location_edit_firstname").val(firstname);
		$("#location_edit_lastname").val(lastname);
		$("#location_edit_street").val(street);
		$("#location_edit_streetnr").val(streetnr);
		$("#location_edit_zipcode").val(zipcode);
		$("#location_edit_city").val(city);
		$("#location_edit_country_code").val(country_code);
		$("#location_edit_phone").val(phone);
		$("#location_edit_fax").val(fax);
		$("#location_edit_website").val(website);
		$("#location_edit_mail").val(mail);
		$("#location_edit_monday").val(monday);
		$("#location_edit_tuesday").val(tuesday);
		$("#location_edit_wednesday").val(wednesday);
		$("#location_edit_thursday").val(thursday);
		$("#location_edit_friday").val(friday);
		$("#location_edit_saturday").val(saturday);
		$("#location_edit_sunday").val(sunday);
		$("#location_edit_dialog").dialog
		({	buttons:
			[
				{ text: "Speichern", click: function() { location_edit_save(); } },
				{ text: "Abbrechen", click: function() { $(this).dialog("close"); } }
			],
			closeText:"Fenster schließen",
			hide: { effect: 'drop', direction: "up" },
			modal:true,
			resizable:false,
			show: { effect: 'drop', direction: "up" },
			title:"Standort bearbeiten",
			width:700
		});
	}

	function location_edit_save()
	{
		var $postdata=new Object();
		$postdata["API"]="cms";
		$postdata["Action"]="ContactsLocationEdit";
		$postdata["id_location"]=$("#location_edit_id_location").val();
		$postdata["location"]=$("#location_edit_location").val();
		$postdata["company"]=$("#location_edit_company").val();
		$postdata["title"]=$("#location_edit_title").val();
		$postdata["firstname"]=$("#location_edit_firstname").val();
		$postdata["lastname"]=$("#location_edit_lastname").val();
		$postdata["street"]=$("#location_edit_street").val();
		$postdata["streetnr"]=$("#location_edit_streetnr").val();
		$postdata["zipcode"]=$("#location_edit_zipcode").val();
		$postdata["city"]=$("#location_edit_city").val();
		$postdata["country"]=$("#location_edit_country_code option:selected").text();
		$postdata["country_code"]=$("#location_edit_country_code").val();
		$postdata["phone"]=$("#location_edit_phone").val();
		$postdata["fax"]=$("#location_edit_fax").val();
		$postdata["website"]=$("#location_edit_website").val();
		$postdata["mail"]=$("#location_edit_mail").val();
		$postdata["monday"]=$("#location_edit_monday").val();
		$postdata["tuesday"]=$("#location_edit_tuesday").val();
		$postdata["wednesday"]=$("#location_edit_wednesday").val();
		$postdata["thursday"]=$("#location_edit_thursday").val();
		$postdata["friday"]=$("#location_edit_friday").val();
		$postdata["saturday"]=$("#location_edit_saturday").val();
		$postdata["sunday"]=$("#location_edit_sunday").val();
		wait_dialog_show();
		$.post("<?php echo PATH; ?>soa/", $postdata, function(data)
		{
			wait_dialog_hide();
			$xml = $($.parseXML(data));
			$ack = $xml.find("Ack");
			if ( $ack.text()!="Success" )
			{
				show_status2(data);
				return;
			}

			show_status("Standort erfolgreich bearbeitet.");
			contacts_view();
			$("#location_edit_dialog").dialog("close");
		});
	}

	function location_remove(id_location)
	{
		$("#location_remove_id_location").val(id_location);
		$("#location_remove_dialog").dialog
		({	buttons:
			[
				{ text: "Löschen", click: function() { location_remove_accept(); } },
				{ text: "Abbrechen", click: function() { $(this).dialog("close"); } }
			],
			closeText:"Fenster schließen",
			hide: { effect: 'drop', direction: "up" },
			modal:true,
			resizable:false,
			show: { effect: 'drop', direction: "up" },
			title:"Standort löschen",
		});
	}

	function location_remove_accept()
	{
		var id_location=$("#location_remove_id_location").val();
		wait_dialog_show();
		$.post("<?php echo PATH; ?>soa/", { API:"cms", Action:"ContactsLocationRemove", id_location:id_location },
			function(data)
			{
				wait_dialog_hide();
				$xml = $($.parseXML(data));
				$ack = $xml.find("Ack");
				if ( $ack.text()!="Success" )
				{
					show_status2(data);
					return;
				}

				show_status("Standort erfolgreich gelöscht.");
				contacts_view();
				$("#location_remove_dialog").dialog("close");
			}
		);
	}

	function contacts_view()
	{
		wait_dialog_show();
		$.post("<?php echo PATH; ?>soa/", { API:"cms", Action:"ContactsView", id_location:id_location, id_department:id_department, id_contact:id_contact },
			function(data)
			{
				wait_dialog_hide();
				$("#contacts_view").html(data);
				$(function() {
					$( "#locations" ).sortable({cancel: "#locations_header"});
					$( "#locations" ).disableSelection();
					$( "#locations" ).bind( "sortupdate", function(event, ui)
					{
						var list = $('#locations').sortable('toArray');
						wait_dialog_show();
						$.post("<?php echo PATH; ?>soa/", { API:"cms", Action:"ContactsLocationSort", list:list},
							function(data)
							{
								wait_dialog_hide();
								$xml = $($.parseXML(data));
								$ack = $xml.find("Ack");
								if ( $ack.text()!="Success" )
								{
									show_status2(data);
									return;
								}

								show_status("Standorte erfolgreich sortiert.");
								contacts_view();
							}
						);
					});
				});
				if ( typeof($("#departments").val()) != "undefined" )
				{
					$(function() {
						$( "#departments" ).sortable({cancel: "#departments_header"});
						$( "#departments" ).disableSelection();
						$( "#departments" ).bind( "sortupdate", function(event, ui)
						{
							var list = $('#departments').sortable('toArray');
							wait_dialog_show();
							$.post("<?php echo PATH; ?>soa/", { API:"cms", Action:"ContactsDepartmentSort", list:list },
								function(data)
								{
									wait_dialog_hide();
									$xml = $($.parseXML(data));
									$ack = $xml.find("Ack");
									if ( $ack.text()!="Success" )
									{
										show_status2(data);
										return;
									}

									show_status("Abteilungen erfolgreich sortiert.");
									contacts_view();
								}
							);
						});
					});
				}
				if ( typeof($("#contacts").val()) != "undefined" )
				{
					$(function() {
						$( "#contacts" ).sortable({cancel: "#contacts_header"});
						$( "#contacts" ).disableSelection();
						$( "#contacts" ).bind( "sortupdate", function(event, ui)
						{
							var list = $('#contacts').sortable('toArray');
							wait_dialog_show();
							$.post("<?php echo PATH; ?>soa/", { API:"cms", Action:"ContactsContactSort", list:list},
								function(data)
								{
									wait_dialog_hide();
									$xml = $($.parseXML(data));
									$ack = $xml.find("Ack");
									if ( $ack.text()!="Success" )
									{
										show_status2(data);
										return;
									}

									show_status("Kontakte erfolgreich sortiert.");
									contacts_view();
								}
							);
						});
					});
				}
			}
		);
	}
</script>

<?php	
	echo '<h1>Kontakte</h1>';
	echo '<div style="position:relative;">';
	echo '	Kontakt suchen: <input id="contact_search" type="text" value="" onkeyup="contact_search();" />';
	echo '	<div id="contact_search_results" style="position:absolute; margin:0; border:1px solid black; padding:10px; background-color:#ffffff; display:none;"></div>';
	echo '</div>';
	echo '<br style="clear:both;" />';
	
	echo '<div id="contacts_view"></div>';
	echo '<script> contacts_view(); </script>';
	
	
	//CONTACT ADD DIALOG
	echo '<div id="contact_add_dialog" style="display:none;">';
	echo '<table>';
	echo '	<tr>';
	echo '		<td>Vorname</td>';
	echo '		<td><input type="text" id="contact_add_firstname" /></td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Nachname</td>';
	echo '		<td><input type="text" id="contact_add_lastname" /></td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Position</td>';
	echo '		<td><input type="text" id="contact_add_position" /></td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Sprachen</td>';
	echo '		<td><input type="text" id="contact_add_languages" /></td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Telefon</td>';
	echo '		<td><input type="text" id="contact_add_phone" /></td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Telefax</td>';
	echo '		<td><input type="text" id="contact_add_fax" /></td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Mobiltelefon</td>';
	echo '		<td><input type="text" id="contact_add_mobile" /></td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>E-Mail</td>';
	echo '		<td><input type="text" id="contact_add_mail" /></td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Geschlecht</td>';
	echo '		<td>';
	echo '			<select id="contact_add_gender">';
	echo '				<option value="m">männlich</option>';
	echo '				<option value="f">weiblich</option>';
	echo '			</select>';
	echo '		</td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Aktiv</td>';
	echo '		<td>';
	echo '			<select id="contact_add_active">';
	echo '				<option value="1">Ja</option>';
	echo '				<option value="0">Nein</option>';
	echo '			</select>';
	echo '		</td>';
	echo '	</tr>';
	echo '</table>';
	echo '	<input type="hidden" id="contact_add_id_department" value="" />';
	echo '</div>';
	
	//CONTACT EDIT DIALOG
	echo '<div id="contact_edit_dialog" style="display:none;">';
	echo '<table>';
	echo '	<tr>';
	echo '		<td>Vorname</td>';
	echo '		<td><input type="text" id="contact_edit_firstname" /></td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Nachname</td>';
	echo '		<td><input type="text" id="contact_edit_lastname" /></td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Position</td>';
	echo '		<td><input type="text" id="contact_edit_position" /></td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Sprachen</td>';
	echo '		<td><input type="text" id="contact_edit_languages" /></td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Telefon</td>';
	echo '		<td><input type="text" id="contact_edit_phone" /></td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Telefax</td>';
	echo '		<td><input type="text" id="contact_edit_fax" /></td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Mobiltelefon</td>';
	echo '		<td><input type="text" id="contact_edit_mobile" /></td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>E-Mail</td>';
	echo '		<td><input type="text" id="contact_edit_mail" /></td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Geschlecht</td>';
	echo '		<td>';
	echo '			<select id="contact_edit_gender">';
	echo '				<option value="m">männlich</option>';
	echo '				<option value="f">weiblich</option>';
	echo '			</select>';
	echo '		</td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Aktiv</td>';
	echo '		<td>';
	echo '			<select id="contact_edit_active">';
	echo '				<option value="1">Ja</option>';
	echo '				<option value="0">Nein</option>';
	echo '			</select>';
	echo '		</td>';
	echo '	</tr>';
	echo '</table>';
	echo '	<input type="hidden" id="contact_edit_id_contact" value="" />';
	echo '</div>';
	
	//CONTACT REMOVE DIALOG
	echo '<div id="contact_remove_dialog" style="display:none;">';
	echo '<table>';
	echo '	<tr>';
	echo '		<td colspan="">Sind Sie sicher, dass Sie den Kontakt löschen möchten?</td>';
	echo '	</tr>';
	echo '</table>';
	echo '	<input type="hidden" id="contact_remove_id_contact" value="" />';
	echo '</div>';
	
	//DEPARTMENT ADD DIALOG
	echo '<div id="department_add_dialog" style="display:none;">';
	echo '<table>';
	echo '	<tr>';
	echo '		<td>Standortname</td>';
	echo '		<td><input type="text" id="department_add_department" /></td>';
	echo '	</tr>';
	echo '</table>';
	echo '<input type="hidden" id="department_add_id_location" />';
	echo '</div>';
	
	//DEPARTMENT EDIT DIALOG
	echo '<div id="department_edit_dialog" style="display:none;">';
	echo '<table>';
	echo '	<tr>';
	echo '	<tr>';
	echo '		<td>Abteilung</td>';
	echo '		<td><input type="text" id="department_edit_department" /></td>';
	echo '	</tr>';
	echo '</table>';
	echo '<input type="hidden" id="department_edit_id_department" value="" />';
	echo '</div>';

	//DEPARTMENT REMOVE DIALOG
	echo '<div id="department_remove_dialog" style="display:none;">';
	echo '<table>';
	echo '	<tr>';
	echo '		<td colspan="">Sind Sie sicher, dass Sie die Abteilung löschen möchten?</td>';
	echo '	</tr>';
	echo '</table>';
	echo '<input type="hidden" id="department_remove_id_department" value="" />';
	echo '</div>';
	
	//LOCATION ADD DIALOG
	echo '<div id="location_add_dialog" style="display:none;">';
	echo '<table>';
	echo '	<tr>';
	echo '		<th colspan="2">Adresse</th>';
	echo '		<th colspan="2">Kontaktdaten</th>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Standort</td>';
	echo '		<td><input style="width:200px;" type="text" id="location_add_location" /></td>';
	echo '		<td>Telefon</td>';
	echo '		<td><input style="width:200px;" type="text" id="location_add_phone" /></td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Firma</td>';
	echo '		<td><input style="width:200px;" type="text" id="location_add_company" /></td>';
	echo '		<td>Telefax</td>';
	echo '		<td><input style="width:200px;" type="text" id="location_add_fax" /></td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Titel</td>';
	echo '		<td><input style="width:200px;" type="text" id="location_add_title" /></td>';
	echo '		<td>Webseite</td>';
	echo '		<td><input style="width:200px;" type="text" id="location_add_website" /></td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Ansprechpartner<br />Vorname<br />Nachname</td>';
	echo '		<td>';
	echo '			<br /><input style="width:200px;" type="text" id="location_add_firstname" />';
	echo '			<br /><input style="width:200px;" type="text" id="location_add_lastname" />';
	echo '		</td>';
	echo '		<td>E-Mail</td>';
	echo '		<td><input style="width:200px;" type="text" id="location_add_mail" /></td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Straße<br />Hausnummer</td>';
	echo '		<td>';
	echo '			<input style="width:200px;" type="text" id="location_add_street" />';
	echo '			<br /><input style="width:50px;" type="text" id="location_add_streetnr" />';
	echo '		</td>';
	echo '		<th colspan="2">Öffnungszeiten</th>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Postleitzahl</td>';
	echo '		<td><input style="width:50px;" type="text" id="location_add_zipcode" /></td>';
	echo '		<td>Montag</td>';
	echo '		<td><input style="width:200px;" type="text" id="location_add_monday" /></td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Stadt</td>';
	echo '		<td><input style="width:200px;" type="text" id="location_add_city" /></td>';
	echo '		<td>Dienstag</td>';
	echo '		<td><input style="width:200px;" type="text" id="location_add_tuesday" /></td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Land</td>';
	echo '		<td>';
	echo '			<select id="location_add_country_code">';
	$results=q("SELECT * FROM shop_countries ORDER BY country;", $dbshop, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		echo '<option value="'.$row["country_code"].'">'.$row["country"].'</option>';
	}
	echo '			</select>';
	echo '		</td>';
	echo '		<td>Mittwoch</td>';
	echo '		<td><input style="width:200px;" type="text" id="location_add_wednesday" /></td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td colspan="2"></td>';
	echo '		<td>Donnerstag</td>';
	echo '		<td><input style="width:200px;" type="text" id="location_add_thursday" /></td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td colspan="2"></td>';
	echo '		<td>Freitag</td>';
	echo '		<td><input style="width:200px;" type="text" id="location_add_friday" /></td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td colspan="2"></td>';
	echo '		<td>Sonnabend</td>';
	echo '		<td><input style="width:200px;" type="text" id="location_add_saturday" /></td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td colspan="2"></td>';
	echo '		<td>Sonntag</td>';
	echo '		<td><input style="width:200px;" type="text" id="location_add_sunday" /></td>';
	echo '	</tr>';
	echo '</table>';
	echo '</div>';
	
	//LOCATION EDIT DIALOG
	echo '<div id="location_edit_dialog" style="display:none;">';
	echo '<table>';
	echo '	<tr>';
	echo '		<th colspan="2">Adresse</th>';
	echo '		<th colspan="2">Kontaktdaten</th>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Standort</td>';
	echo '		<td><input style="width:200px;" type="text" id="location_edit_location" /></td>';
	echo '		<td>Telefon</td>';
	echo '		<td><input style="width:200px;" type="text" id="location_edit_phone" /></td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Firma</td>';
	echo '		<td><input style="width:200px;" type="text" id="location_edit_company" /></td>';
	echo '		<td>Telefax</td>';
	echo '		<td><input style="width:200px;" type="text" id="location_edit_fax" /></td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Titel</td>';
	echo '		<td><input style="width:200px;" type="text" id="location_edit_title" /></td>';
	echo '		<td>Webseite</td>';
	echo '		<td><input style="width:200px;" type="text" id="location_edit_website" /></td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Ansprechpartner<br />Vorname<br />Nachname</td>';
	echo '		<td>';
	echo '			<br /><input style="width:200px;" type="text" id="location_edit_firstname" />';
	echo '			<br /><input style="width:200px;" type="text" id="location_edit_lastname" />';
	echo '		</td>';
	echo '		<td>E-Mail</td>';
	echo '		<td><input style="width:200px;" type="text" id="location_edit_mail" /></td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Straße<br />Hausnummer</td>';
	echo '		<td>';
	echo '			<input style="width:200px;" type="text" id="location_edit_street" />';
	echo '			<input style="width:50px;" type="text" id="location_edit_streetnr" />';
	echo '		</td>';
	echo '		<th colspan="2">Öffnungszeiten</th>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Postleitzahl</td>';
	echo '		<td><input style="width:200px;" type="text" id="location_edit_zipcode" /></td>';
	echo '		<td>Montag</td>';
	echo '		<td><input style="width:200px;" type="text" id="location_edit_monday" /></td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Stadt</td>';
	echo '		<td><input style="width:200px;" type="text" id="location_edit_city" /></td>';
	echo '		<td>Dienstag</td>';
	echo '		<td><input style="width:200px;" type="text" id="location_edit_tuesday" /></td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Land</td>';
	echo '		<td>';
	echo '			<select id="location_edit_country_code">';
	$results=q("SELECT * FROM shop_countries ORDER BY country;", $dbshop, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		echo '<option value="'.$row["country_code"].'">'.$row["country"].'</option>';
	}
	echo '			</select>';
	echo '		</td>';
	echo '		<td>Mittwoch</td>';
	echo '		<td><input style="width:200px;" type="text" id="location_edit_wednesday" /></td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td colspan="2"></td>';
	echo '		<td>Donnerstag</td>';
	echo '		<td><input style="width:200px;" type="text" id="location_edit_thursday" /></td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td colspan="2"></td>';
	echo '		<td>Freitag</td>';
	echo '		<td><input style="width:200px;" type="text" id="location_edit_friday" /></td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td colspan="2"></td>';
	echo '		<td>Sonnabend</td>';
	echo '		<td><input style="width:200px;" type="text" id="location_edit_saturday" /></td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td colspan="2"></td>';
	echo '		<td>Sonntag</td>';
	echo '		<td><input style="width:200px;" type="text" id="location_edit_sunday" /></td>';
	echo '	</tr>';
	echo '</table>';
	echo '<input type="hidden" id="location_edit_id_location" value="" />';
	echo '</div>';
	
	//LOCATION REMOVE DIALOG
	echo '<div id="location_remove_dialog" style="display:none;">';
	echo '<table>';
	echo '	<tr>';
	echo '		<td colspan="">Sind Sie sicher, dass Sie den Standort löschen möchten?</td>';
	echo '	</tr>';
	echo '</table>';
	echo '<input type="hidden" id="location_remove_id_location" value="" />';
	echo '</div>';
	
	include("templates/".TEMPLATE_BACKEND."/footer.php");
?>
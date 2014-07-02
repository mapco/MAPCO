<?php
	include("config.php");
	include("templates/".TEMPLATE_BACKEND."/header.php");
?>

	<script>
		var id_label=0;
		
		function label(id)
		{
			id_label=id;
			view();
		}
		
		function label_add()
		{
			$("#label_add_label").val("");
			$("#label_add_description").val("");
			$("#label_add_dialog").dialog
			({	buttons:
				[
					{ text: "Speichern", click: function() { label_add_save(); } },
					{ text: "Abbrechen", click: function() { $(this).dialog("close"); } }
				],
				closeText:"Fenster schließen",
				hide: { effect: 'drop', direction: "up" },
				modal:true,
				resizable:false,
				show: { effect: 'drop', direction: "up" },
				title:"Kontakt hinzufügen",
				width:450
			});
		}
		
		function label_add_save()
		{
			var label=$("#label_add_label").val();
			var description=$("#label_add_description").val();
			wait_dialog_show();
			$.post("<?php echo PATH; ?>soa/", { API:"cms", Action:"LabelAdd", label:label, description:description },
				function(data)
				{
					$xml = $($.parseXML(data));
					$ack = $xml.find("Ack");
					if ( $ack.text()!="Success" )
					{
						show_status2(data);
						return;
					}

					show_status("Das Stichwort wurde erfolgreich angelegt.");
					view();
					$("#label_add_dialog").dialog("close");
					wait_dialog_hide();
				}
			);
		}
		
		function label_edit($id_label)
		{
			wait_dialog_show();
			$.post("<?php echo PATH; ?>soa/", { API:"cms", Action:"LabelGet", id_label:$id_label }, function($data)
			{
				wait_dialog_hide();
				try { $xml = $($.parseXML($data)); } catch ($err) { show_status($err.message); return; }
				if ( $xml.find("Ack").text()!="Success" ) { show_status2($data); return; }

				$("#label_edit_label").val($xml.find("label").text());
				$("#label_edit_id_label").val($xml.find("id_label").text());
				$("#label_edit_description").val($xml.find("description").text());
				$("#label_edit_site_id").val(Number($xml.find("site_id").text()));
				$("#label_edit_dialog").dialog
				({	buttons:
					[
						{ text: "Speichern", click: function() { label_edit_save(); } },
						{ text: "Abbrechen", click: function() { $(this).dialog("close"); } }
					],
					closeText:"Fenster schließen",
					hide: { effect: 'drop', direction: "up" },
					modal:true,
					resizable:false,
					show: { effect: 'drop', direction: "up" },
					title:"Stichwort bearbeiten",
					width:450
				});
			});
		}
		
		function label_edit_save()
		{
			var $postdata=new Object();
			$postdata["API"]="cms";
			$postdata["Action"]="LabelEdit";
			$postdata["id_label"]=$("#label_edit_id_label").val();
			$postdata["label"]=$("#label_edit_label").val();
			$postdata["description"]=$("#label_edit_description").val();
			$postdata["site_id"]=$("#label_edit_site_id").val();
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

				show_status("Das Stichwort wurde erfolgreich geändert.");
				view();
				$("#label_edit_dialog").dialog("close");
			});
		}
		
		function label_remove(id_label)
		{
			$("#label_remove_dialog").dialog
			({	buttons:
				[
					{ text: "Löschen", click: function() { label_remove_accept(id_label); } },
					{ text: "Abbrechen", click: function() { $(this).dialog("close"); } }
				],
				closeText:"Fenster schließen",
				hide: { effect: 'drop', direction: "up" },
				modal:true,
				resizable:false,
				show: { effect: 'drop', direction: "up" },
				title:"Stichwort wirklich löschen?",
			});
		}

		function label_remove_accept(id_label)
		{
			$("#label_remove_dialog").dialog("close");
			wait_dialog_show();
			$.post("<?php echo PATH; ?>soa/", { API:"cms", Action:"LabelRemove", id_label:id_label },
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

					show_status("Stichwort erfolgreich entfernt.");
					view();
				}
			);
		}

		function article_remove(id_article)
		{
			$("#article_remove_window").dialog
				({	buttons:
					[
						{ text: "Löschen", click: function() { article_remove_accept(id_article); } },
						{ text: "Abbrechen", click: function() { $(this).dialog("close"); } }
					],
				 	closeText:"Löschen abbrechen",
					hide: { effect: 'explode', pieces:32, duration:500 },
				 	modal:true,
					resizable:false,
					show: { effect: 'drop', direction: "up" },
				 	title:"Artikel wirklich löschen?"
				});
		}
		
		function article_remove_accept(id_article)
		{
			$("#article_remove_window").dialog("close");
			$.post("<?php echo PATH; ?>soa2/", { API:"cms", APIRequest:"ArticleRemove", id_article:id_article }, function(data) { show_status(data); view(); } );
		}

		function view()
		{
			wait_dialog_show();
			$.post("<?php echo PATH; ?>soa/", { API:"cms", Action:"ArticlesView", id_label:id_label },
				function(data)
				{
					$("#view").html(data);
					$(function() {
						$( "#labels" ).sortable( { items:"li:not(.header)" } );
						$( "#labels" ).sortable( { cancel:".header"} );
						$( "#labels" ).disableSelection();
						$( "#labels" ).bind( "sortupdate", function(event, ui)
						{
							var list = $('#labels').sortable('toArray');
							$.post("<?php echo PATH; ?>soa/", { API:"cms", Action:"LabelSort", list:list},
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

									view();
								}
							);
						});
					});
					$(function() {
						$( "#articles" ).sortable( { items:"li:not(.header)" } );
						$( "#articles" ).sortable( { cancel:".header"} );
						$( "#articles" ).sortable({cancel: ".header"});
						$( "#articles" ).disableSelection();
						$( "#articles" ).bind( "sortupdate", function(event, ui)
						{
							var list = $('#articles').sortable('toArray');
							$.post("<?php echo PATH; ?>soa/", { API:"cms", Action:"ArticleSort", list:list, id_label:id_label }, function(data) { show_status(data); view(); });
						});
					});
					wait_dialog_hide();
				}
			);
		}
	</script>

<?php
	//REMOVE
	if (isset($_POST["form_button"]) and $_POST["form_button"]=="Artikel löschen")
    {
		if ($_POST["id_article"]<=0) echo '<div class="failure">Es konnte keine ID für den Artikel gefunden werden!</div>';
		else
		{
			q("DELETE FROM cms_articles WHERE id_article=".$_POST["id_article"]." LIMIT 1;", $dbweb, __FILE__, __LINE__);
			echo '<div class="success">Artikel erfolgreich gelöscht!</div>';
		}
	}

	//PATH
	echo '<p>';
	echo '<a href="backend_index.php?lang='.$_GET["lang"].'">'.t("Backend").'</a>';
	echo ' > <a href="backend_cms_index.php?lang='.$_GET["lang"].'">'.t("Content Management").'</a>';
	echo ' > Beiträge';
	echo '</p>';
	
	echo '<h1>Beiträge</h1>';

	//LIST
	echo '<div id="view"></div>';
	if ( !isset($_GET["id_label"]) ) echo '<script> view(); </script>';
	else echo '<script> label('.$_GET["id_label"].'); </script>';
	
	//DIALOGS
	echo '<div style="display:none;" id="article_remove_window">';
	echo '	Dieser Vorgang löscht nur den Artikel und dessen Stichworte, nicht jedoch verknüpfte Dateien wie Abbildungen oder Videos.';
	echo '</div>';
	
	//LABEL ADD DIALOG
	echo '<div style="display:none;" id="label_add_dialog">';
	echo '	<table>';
	echo '		<tr>';
	echo '			<td>Stichwort</td>';
	echo '			<td><input type="text" id="label_add_label" value="" /></td>';
	echo '		</tr>';
	echo '		<tr>';
	echo '			<td>Beschreibung</td>';
	echo '			<td><textarea id="label_add_description" style="width:300px; height:200px;"></textarea></td>';
	echo '		</tr>';
	echo '	</table>';
	echo '</div>';
	
	//LABEL EDIT DIALOG
	echo '<div style="display:none;" id="label_edit_dialog">';
	echo '	<table>';
	echo '		<tr>';
	echo '			<td>Stichwort</td>';
	echo '			<td><input type="text" id="label_edit_label" value="" /></td>';
	echo '		</tr>';
	echo '		<tr>';
	echo '			<td>Beschreibung</td>';
	echo '			<td><textarea id="label_edit_description" style="width:300px; height:200px;"></textarea></td>';
	echo '		</tr>';
	echo '	<tr>';
	echo '		<td>Seite</td>';
	echo '		<td>';
	echo '			<select id="label_edit_site_id">';
	if( $_SESSION["userrole_id"]==1 )
	{
		echo '				<option value="0">Global</option>';
		$results=q("SELECT * FROM cms_sites ORDER BY title;", $dbweb, __FILE__, __LINE__);
	}
	else
	{
		$results=q("SELECT * FROM cms_sites WHERE id_site=".$_SESSION["id_site"]." ORDER BY title;", $dbweb, __FILE__, __LINE__);
	}
	while( $row=mysqli_fetch_array($results) )
	{
		echo '				<option value="'.$row["id_site"].'">'.$row["title"].'</option>';
	}
	echo '			</select>';
	echo '		</td>';
	echo '	</tr>';
	echo '	</table>';
	echo '	<input type="hidden" id="label_edit_id_label" value="" />';
	echo '</div>';
	
	//LABEL REMOVE DIALOG
	echo '<div style="display:none;" id="label_remove_dialog">';
	echo '	<p>Wollen Sie das Stichwort wirklich löschen?</p>';
	echo '</div>';
	
	include("templates/".TEMPLATE_BACKEND."/footer.php");
?>
<?php
	include("config.php");
	include("templates/".TEMPLATE_BACKEND."/header.php");
?>

<script language="javascript">
	var auction=new Array();
	
	function site_reload() 
	{
		window.location.href="http://www.mapco.de/backend_ebay_auctions_berlin.php?lang=de&id_menuitem=173&account=5";
	}

	function ebay_cancel()
	{
		$("#ebay_window").hide("puff", {}, 2000);
	}

	function ebay_deactivate_cancel()
	{
		$("#ebay_deactivate").hide("puff", {}, 2000);
	}

	function ebay_failure_cancel()
	{
		$("#ebay_failure").hide();
	}
	
	function ebay_get_auctions(account)
	{
		if (account == null)
		{
			account = 5;
		}

		$.post("modules/backend_ebay_auctions_actions.php", { action:"get_auctions", account:account },
			   function(data)
			   {
				   var data = JSON.parse(data);
				   ebay_send_auctions(data);
			   }
		);
	}
	
	function ebay_send_auctions(data)
	{
		auction=data;
		$("#ebay_send_view").html('Sende Auktion 1 von '+auction.length+'...');
		$("#ebay_send_window").show();
		ebay_send_auction(0);
	}
	
	function ebay_send_cancel(i)
	{
		$("#ebay_send_window").hide("explode");
	}

	function ebay_send_auction(i)
	{
		if (i<auction.length)
		{
			var wait = window.setTimeout("site_reload()", 180000);
			$.post("jobs/ebay_auctions_send.php", { id_auction:auction[i] }, function(data) { $("#ebay_send_view").html((i+1)+" von "+auction.length+" gesendet.<br /><br />"+data); clearTimeout(wait); ebay_send_auction(i+1); } );
		}
		else
		{
			var html=$("#ebay_send_view").html();
			$("#ebay_send_view").html(html+'<br /><br /><input type="button" value="Fertig" onclick="ebay_send_cancel();" />');
		}
	}
	
	function ebay_view_auctions(item_id, account_id)
	{
		$.post("modules/backend_ebay_auctions_actions.php", { action:"ebay_view_auctions", item_id:item_id, account_id:account_id },
			   function success(data)
			   {
					$("#ebay_view_auctions_dialog").html(data);
					$("#ebay_view_auctions_dialog").dialog
					({	closeText:"Fenster schließen",
						hide: { effect: 'drop', direction: "up" },
						modal:true,
						resizable:false,
						show: { effect: 'drop', direction: "up" },
						title:"Auktionsübersicht",
						width:1000
					});
			   }
		);
	}

	function ebay_window()
	{
		var theForm = document.itemform;
		var selected=false;
		for (i=0; i<theForm.elements.length; i++)
		{
			if (theForm.elements[i].name=='item_id[]')
				if (theForm.elements[i].checked) selected=true;
		}
		if (selected) $("#ebay_window").show();
		else alert("Bitte wählen Sie zuerst Artikel aus.");
	}
	
	function ebay_deactivate()
	{
		$("#ebay_deactivate").show();
	}

	function ebay_activate_save()
	{
		var items=Array();
		var theForm = document.itemform;
		var j=0;
		for (i=0; i<theForm.elements.length; i++)
		{
			if (theForm.elements[i].checked)
			{
				if (theForm.elements[i].name=='item_id[]')
				{
					items[j]=theForm.elements[i].value;
					j++;
				}
			}
		}
		
		var accounts=Array();
		theForm = document.accountform;
		var selected=false;
		j=0;
		for (i=0; i<theForm.elements.length; i++)
		{
			if (theForm.elements[i].checked)
			{
				accounts[j]=theForm.elements[i].value;
				selected=true;
				j++;
			}
		}
		
		var active = $("#ebay_active").val()
		var article_id = $("#ebay_article_id").val()
		var pricelist_id = $("#ebay_pricelist_id").val()
		var bestoffer = $("#ebay_bestoffer:checked").val()
		if ( bestoffer=="on" ) bestoffer=1; else bestoffer=0;
		var free_shipping = $("#ebay_free_shipping:checked").val()
		if ( free_shipping=="on" ) free_shipping=1; else free_shipping=0;
		var comment = $("#ebay_comment").val()
		
		if (!selected) alert("Bitten wählen Sie mindestens einen Account aus.")
		else
		{
			$.post("modules/backend_ebay_auctions_actions.php", { action:"ebay_activate", items:items, accounts:accounts, article_id:article_id, pricelist_id:pricelist_id, bestoffer:bestoffer, free_shipping:free_shipping, comment:comment, active:active }, function(data) { show_status(data); $("#ebay_window").hide(); view(); } );
		}
		
	}

	function text_search(text)
	{
		setTimeout("view('"+text+"')", 250);
	}
	function view(text)
	{
		if (text==undefined) text="";
		var text2=document.getElementById("needle").value;
		if (text2==text)
		{
			var id_menuitem=document.getElementById("id_menuitem").value;
			var filter=document.getElementById("filter").value;
			var fotostatus=document.getElementById("fotostatus").value;
			var needle=document.getElementById("needle").value;
			
			var response=ajax('modules/backend_ebay_auctions_actions.php?action=view&lang='+encodeURIComponent('<?php echo $_GET["lang"]; ?>')+'&id_menuitem='+id_menuitem+'&filter='+filter+'&needle='+encodeURIComponent(needle)+'&fotostatus='+fotostatus+'&account=5&rcnr=21', false);
			document.getElementById("results").innerHTML=response;
		}
	}
	
	function checkAll()
	{
		var state = document.getElementById("selectall").checked;
		var theForm = document.itemform;
		for (i=0; i<theForm.elements.length; i++)
		{
			if (theForm.elements[i].name=='item_id[]')
				theForm.elements[i].checked = state;
		}
	}
</script>

<?php
	if (isset($_GET["account"]))
	{
		echo '<script language="javascript"> ebay_get_auctions('.$_GET["account"].'); </script>';	
	}

	//PATH
	echo '<p>';
	echo '<a href="backend_index.php">Backend</a>';
	echo ' > <a href="backend_ebay_index.php">eBay</a>';
	echo ' > Auktionen Berlin';
	echo '</p>';

	//HEADLINE
	echo '<h1>Shopartikel';
	echo '<img style="margin:0px 5px 0px 0px; border:0; padding:0; cursor:pointer; float:right;" src="images/icons/24x24/page_add.png" alt="Neuen Shopartikel anlegen" title="Neuen Shopartikel anlegen" onclick="popup(\'modules/backend_shop_item_editor.php\', 850, 600);" />';
	echo '</h1>';


	//DEACTIVATE ITEM
	if (isset($_POST["ebay_deactivate"]))
    {
		$failure=array();
		if($_POST["accounts"]=="0") echo '<div class="failure" style="float:none;">Es wurde kein Account ausgewählt!</div>';
		elseif ($_POST["deactivate_text"]=="" or $_POST["deactivate_text"]<=0) echo '<div class="failure">Die Menge darf nicht unter 1 sein!</div>';
		elseif ($_FILES["deactivate_file"]["tmp_name"]=="") echo '<div class="failure">Es wurde keine Datei ausgewählt!</div>';
		else
		{
			$count_ok=0;
			move_uploaded_file($_FILES['deactivate_file']['tmp_name'], "ebay_tmp.csv"); 
			$handle = fopen("ebay_tmp.csv", "r"); 
			while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) 
			{
				$count++;
				if($count>1 and $data[5]<$_POST["deactivate_text"])
				{
					$results=q("SELECT * FROM shop_items WHERE MPN LIKE '".$data[2]."' ;", $dbshop, __FILE__, __LINE__);
					if (mysqli_num_rows($results)>0)
					{
						$row=mysqli_fetch_array($results);
						$results2=q("SELECT * FROM ebay_accounts_items WHERE account_id=".$_POST["accounts"]." AND item_id=".$row["id_item"]." ;", $dbshop, __FILE__, __LINE__);
						if (mysqli_num_rows($results2)>0)
						{
							$max++;
							q("UPDATE ebay_accounts_items SET active=0 WHERE account_id=".$_POST["accounts"]." AND active=1 AND item_id=".$row["id_item"].";", $dbshop, __FILE__, __LINE__);
							$count_ok++;
						}
						else $failure[]=$data[2];
					}
				}
			}
			fclose($handle);
			unlink("ebay_tmp.csv");
			if($count_ok!=0) 
			{
				echo '<div class="success">'.$count_ok.' Artikel wurden deaktiviert.</div>';
				if (isset($failure)) $failure_view=1;
			}
			else echo '<div class="failure">Es konnten keine Artikel deaktiviert werden.</div>';
        }
	}

	//SELECTION
	$artgr=array();
	$artgr_title=array();
	$artgr_rc=array(82 => "", 84 => "", 93 => "", 99 => "", 107 => "");
	$results=q("SELECT * FROM `cms_menuitems` WHERE menu_id=5 AND menuitem_id>0 ORDER BY title;", $dbweb, __FILE__, __LINE__);
	echo '<table style="width:600px;">';
	echo '	<tr><th colspan="2">'.t("Suchfunktion").'</th></tr>';
	echo '	<tr>';
	echo '		<td>'.t("Artikelgruppe").'</td>';
	echo '		<td>';
	echo '			<select id="id_menuitem" name="id_menuitem" onchange="view();">';
	echo '				<option value="0">bitte wählen</option>';
	while($row=mysqli_fetch_array($results))
	{
		if ( isset($artgr_rc[$row["id_menuitem"]]) )
		{
			if ($_GET["id_menuitem"]==$row["id_menuitem"]) $selected=' selected="selected"'; else $selected='';
			echo '<option'.$selected.' value="'.$row["id_menuitem"].'">'.$row["id_menuitem"].' '.$row["title"].'</option>';
		}
	}
	echo '			</select>';
	echo '		</td>';
	echo '	</tr>';
	
	echo '	<tr>';
	echo '		<td>'.t("Lieferstatus").'</td>';
	echo '		<td>';
	echo '			<select id="filter" onchange="view()">';
	echo '				<option value="4">alle anzeigen</option>';
	echo '				<option selected="selected" value="1">nur sofort lieferbare anzeigen</option>';
	echo '				<option value="2">nur z.Z. nicht lieferbare anzeigen</option>';
	echo '				<option value="0">nur nicht lieferbare anzeigen</option>';
	echo '			</select>';
	echo '		</td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>'.t("Abbildungen").'</td>';
	echo '		<td>';
	echo '			<select id="fotostatus" onchange="view()">';
	echo '				<option value="0">Alle anzeigen</option>';
	echo '				<option selected="selected" value="1">nur mit Fotos anzeigen</option>';
	echo '				<option value="2">nur ohne Fotos anzeigen</option>';
	echo '			</select>';
	echo '		</td>';
	echo '	</tr>';
	echo '	<tr><td>Suchtext</td><td><input id="needle" type="text" onkeyup="text_search(this.value);" /></td></tr>';
	echo '</table>';
	
	//EBAY BUTTONS
	echo '<input type="button" class="formbutton" value="Ebay-Aktivierung" onclick="ebay_window();" />';
	echo '<input type="button" class="formbutton" value="Ebay-Deaktivierung" onclick="ebay_deactivate();" />';
	echo '<input type="button" class="formbutton" value="Ebay-Aktualisierung" onclick="ebay_get_auctions();" />';

	//VIEW
	echo '<div id="results">';
	echo '</div>';
	echo '<script language="javascript"> view(); </script>';
	
	//EBAY SEND WINDOW
	echo '<div id="ebay_send_window" class="popup" style="display:none;">';
	echo '<table style="position:absolute; left:50%; top:50%; width:600px; height:400px; margin-left:-300px; margin-top:-200px; background:#ffffff;">';
	echo '	<tr>';
	echo '		<th colspan="2">';
	echo '			<span style="display:inline; float:left;">eBay-Auktionen senden</span>';
	echo '		</th>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>';
	echo '			<div id="ebay_send_view"></div>';
	echo '		</td>';
	echo '	</tr>';
	echo '</table>';
	echo '</div>';

	//EBAY ACTIVATION WINDOW
	echo '<div id="ebay_view_auctions_dialog" style="display:none;">';
	echo '	<p>Keine Auktionen vorhanden.</p>';
	echo '</div>';

	//EBAY ACTIVATION WINDOW
	echo '<div id="ebay_window" class="popup" style="display:none;">';
	echo '<table style="position:absolute; left:50%; top:50%; width:600px; height:400px; margin-left:-300px; margin-top:-200px; background:#ffffff;">';
	echo '	<tr>';
	echo '		<th colspan="2">';
	echo '			<span style="display:inline; float:left;">eBay-Auktionen anlegen</span>';
	echo '			<img style="margin:0; border:0; padding:0; cursor:pointer; display:inline; float:right;" src="images/icons/16x16/remove.png" onclick="ebay_cancel();" alt="Schließen" title="Schließen" />';
	echo '		</th>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Accounts</td>';
	echo '		<td>';
	echo '			<form name="accountform">';
	$results=q("SELECT * FROM ebay_accounts WHERE id_account=5;", $dbshop, __FILE__, __LINE__);
	while($row=mysqli_fetch_array($results))
	{
		echo '<input name="accounts" type="checkbox"  checked="checked" value="'.$row["id_account"].'" /> '.$row["title"].'<br />';
	}
	echo '			</form>';
	echo '		</td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Design</td>';
	echo '		<td>';
	echo '			<select id="ebay_article_id">';
	$results=q("SELECT * FROM cms_articles WHERE id_article=246;", $dbweb, __FILE__, __LINE__);
	while ( $row=mysqli_fetch_array($results) )
	{
		echo '<option value="'.$row["id_article"].'">'.$row["title"].'</option>';
	}
	echo '			</select>';
	echo '		</td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Aktiv</td>';
	echo '		<td>';
	echo '			<select id="ebay_active">';
	echo '				<option value="1">Aktivieren</option>';
	echo '				<option value="0">Deaktivieren</option>';
	echo '			</select>';
	echo '		</td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Preisliste</td>';
	echo '		<td>';
	echo '	<select id="ebay_pricelist_id">';
	echo '		<option value="0">0 - Bruttopreisliste</option>';
//	echo '		<option selected="selected" value="1">1 - yellow Preisliste</option>';
	echo '		<option value="2">2 - Werksverkaufsliste</option>';
	echo '		<option value="3" selected="selected">3 - blaue Preisliste</option>';
	echo '		<option value="4">4 - grüne Preisliste</option>';
	echo '		<option value="5">5 - gelbe Preisliste</option>';
	echo '		<option value="6">6 - orange Preisliste</option>';
	echo '		<option value="7">7 - rote Preisliste</option>';
//	echo '		<option value="8">8 - red Preisliste</option>';
//	echo '		<option value="9">9 - GH-HR Preisliste</option>';
//	echo '		<option selected="selected" value="16815">16815 - eBay-VP-Preisliste</option>';
	echo '	</select>';
	echo '		</td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Preisvorschlag</td>';
	echo '		<td>';
	echo '				<input type="checkbox" id="ebay_bestoffer" /> Preisvorschlag aktivieren';
	echo '		</td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>kostenloser Versand</td>';
	echo '		<td>';
	echo '				<input type="checkbox" id="ebay_free_shipping" /> kostenloser Versand aktivieren';
	echo '		</td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Kommentar</td>';
	echo '		<td>';
	echo '			<textarea id="ebay_comment" style="width:300px; height:50px;" id="shipping_edit_shipping_memo"></textarea>';
	echo '		</td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td colspan="2">';
	echo '			<input class="formbutton" type="button" value="Speichern" onclick="ebay_activate_save();">';
	echo '			<input class="formbutton" type="button" value="Abbrechen" onclick="ebay_cancel();" />';
	echo '	</td>';
	echo '	</tr>';
	echo '</table>';
	echo '</div>';

	//EBAY DEACTIVATION WINDOW
	echo '<div id="ebay_deactivate" class="popup" style="display:none;">';
	echo '<form method="post" enctype="multipart/form-data">';
	echo '<table style="position:absolute; left:50%; top:50%; width:600px; height:400px; margin-left:-300px; margin-top:-200px; background:#ffffff;">';
	echo '	<tr>';
	echo '		<th colspan="2">';
	echo '			<span style="display:inline; float:left;">eBay-Auktionen deaktivieren</span>';
	echo '			<img style="margin:0; border:0; padding:0; cursor:pointer; display:inline; float:right;" src="images/icons/16x16/remove.png" onclick="ebay_deactivate_cancel();" alt="Schließen" title="Schließen" />';
	echo '		</th>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Accounts</td>';
	echo '		<td>';
	echo '			<select name="accounts">';
	$results=q("SELECT * FROM ebay_accounts where id_account=5;", $dbshop, __FILE__, __LINE__);
	while($row=mysqli_fetch_array($results))
	{
		echo '			<option value="'.$row["id_account"].'">'.$row["title"].'</option>';
	}
	echo '			</select>';
	echo '		</td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Menge</td>';
	echo '		<td>';
	echo '			<input type="text" name="deactivate_text" value="1" />';
	echo '			<br />Bitte niedrigste gültige Menge eingeben!';
	echo '		</td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Datei</td>';
	echo '		<td>';
	echo '			<input type="file" name="deactivate_file" />';
	echo '			<br />Bitte CSV Datei auswählen!';
	echo '			<br />(Spalte3=Artnr. / Spalte6=Bestand)';
	echo '		</td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td colspan="2">';
	echo '			<input class="formbutton" type="submit" name="ebay_deactivate" value="Deaktivieren">';
	echo '			<input class="formbutton" type="button" value="Abbrechen" onclick="ebay_deactivate_cancel();" />';
	echo '	</td>';
	echo '	</tr>';
	echo '</table>';
	echo '</form>';
	echo '</div>';
	
	//EBAY FAILURE WINDOW
	if( sizeof($failure)>0 )
	{
		echo '<div id="ebay_failure" class="popup" style="display:block;">';
		echo '<form method="post" enctype="multipart/form-data">';
		echo '<table style="position:absolute; left:50%; top:50%; width:500px; height:500px; margin-left:-250px; margin-top:-250px; background:#ffffff;">';
		echo '	<tr>';
		echo '		<th>';
		echo '			<span style="display:inline; float:left;">folgende Artikel konnten<br />nicht deaktiviert werden:</span>';
		echo '			<img style="margin:0; border:0; padding:0; cursor:pointer; display:inline; float:right;" src="images/icons/16x16/remove.png" onclick="ebay_failure_cancel();" alt="Schließen" title="Schließen" />';
		echo '		</th>';
		echo '	</tr>';
		echo '	<tr>';
		echo '		<td>';
		echo '			<div style="margin:0px 10px; overflow:scroll; max-height:300px;">';
	
		foreach($failure as $art)
		{
			echo $art.'<br />';	
		}
	
		echo '			</div>';
		echo '		</td>';
		echo '	</tr>';
		echo '	<tr>';
		echo '		<td>';
		echo '			<input class="formbutton" type="button" value="Schließen" onclick="ebay_failure_cancel();" />';
		echo '	</td>';
		echo '	</tr>';
		echo '</table>';
		echo '</form>';
		echo '</div>';
	}
	
	include("templates/".TEMPLATE_BACKEND."/footer.php");
?>
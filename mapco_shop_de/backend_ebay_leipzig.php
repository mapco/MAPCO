<?php
	include("config.php");
	include("templates/".TEMPLATE_BACKEND."/header.php");
?>

<script language="javascript">
	function ebay_cancel()
	{
		$("#ebay_window").hide();
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
	
	function ebay_save()
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
		
		var bestoffer = $("#ebay_bestoffer:checked").val()
		var comment = $("#ebay_comment").val()
		
		if (!selected) alert("Bitten wählen Sie mindestens einen Account aus.")
		else
		{
			$.post("modules/backend_shop_items_actions.php", { action:"ebay_activate", items:items, accounts:accounts, bestoffer:bestoffer, comment:comment }, function(data) { alert(data); } );
		}
		
	}

	function jump(artnr, e)
	{
		if (!e) var e=window.event;
		if(e.keyCode == 13)
		{
			var id_item=ajax('modules/backend_shop_artnr2id.php?artnr='+artnr, false);
			window.location="backend_shop_item_editor.php?id_item="+id_item;
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
			
			var response=ajax('modules/backend_shop_items_view.php?lang='+encodeURIComponent('<?php echo $_GET["lang"]; ?>')+'&id_menuitem='+id_menuitem+'&filter='+filter+'&needle='+encodeURIComponent(needle)+'&fotostatus='+fotostatus, false);
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
	//PATH
	echo '<p>';
	echo '<a href="backend_index.php">Backend</a>';
	echo ' > <a href="backend_shop_index.php">Online-Shop</a>';
	echo ' > Artikel';
	echo '</p>';
	
	//HEADLINE
	echo '<h1>Shopartikel';
	echo '<img style="margin:0px 5px 0px 0px; border:0; padding:0; cursor:pointer; float:right;" src="images/icons/24x24/page_add.png" alt="Neuen Shopartikel anlegen" title="Neuen Shopartikel anlegen" onclick="popup(\'modules/backend_shop_item_editor.php\', 850, 600);" />';
	echo '</h1>';


	//JUMP
	echo '<table style="width:600px;">';
	echo '	<tr><th colspan="2">'.t("Suchfunktion").'</th></tr>';
	echo '	<tr><td>Springe zu Artikelnummer</td><td><input id="artnr" type="text" onkeyup="jump(this.value, event)" /></td></tr>';
	echo '</table>';
	
	//SELECTION
	$artgr=array();
	$artgr_title=array();
	$results=q("SELECT * FROM `cms_menuitems` WHERE menu_id=5 AND menuitem_id>0 ORDER BY title;", $dbweb, __FILE__, __LINE__);
	echo '<table style="width:600px;">';
	echo '	<tr><th colspan="2">'.t("Suchfunktion").'</th></tr>';
	echo '	<tr>';
	echo '		<td>'.t("Artikelgruppe").'</td>';
	echo '		<td>';
	echo '			<select id="id_menuitem" name="id_menuitem" onchange="view();">';
	while($row=mysqli_fetch_array($results))
	{
		if ($_GET["id_menuitem"]==$row["id_menuitem"]) $selected=' selected="selected"'; else $selected='';
		echo '<option'.$selected.' value="'.$row["id_menuitem"].'">'.$row["title"].'</option>';
	}
	echo '			</select>';
	echo '		</td>';
	echo '	</tr>';
	
	echo '	<tr>';
	echo '		<td>'.t("Lieferstatus").'</td>';
	echo '		<td>';
	echo '			<select id="filter" onchange="view()">';
	echo '				<option value="4">alle anzeigen</option>';
	echo '				<option value="1">nur sofort lieferbare anzeigen</option>';
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
	echo '				<option value="1">nur mit Fotos anzeigen</option>';
	echo '				<option value="2">nur ohne Fotos anzeigen</option>';
	echo '			</select>';
	echo '		</td>';
	echo '	</tr>';
	echo '	<tr><td>Suchtext</td><td><input id="needle" type="text" onkeyup="text_search(this.value);" /></td></tr>';
	echo '</table>';
	
	//EBAY ACTIVATION BUTTON
	echo '<input type="button" class="formbutton" value="Ebay-Aktivierung" onclick="ebay_window();" />';


	//VIEW
	echo '<div id="results">';
	echo '</div>';
	echo '<script language="javascript"> view(); </script>';
	
	//EBAY ACTIVATION
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
	$results=q("SELECT * FROM ebay_accounts;", $dbshop, __FILE__, __LINE__);
	while($row=mysqli_fetch_array($results))
	{
		echo '<input name="accounts" type="checkbox" value="'.$row["id_account"].'" /> '.$row["title"].'<br />';
	}
	echo '			</form>';
	echo '		</td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Preisvorschlag</td>';
	echo '		<td>';
	echo '				<input type="checkbox" id="ebay_bestoffer" /> Preisvorschlag aktivieren';
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
	echo '			<input class="formbutton" type="button" value="Speichern" onclick="ebay_save();">';
	echo '			<input class="formbutton" type="button" value="Abbrechen" onclick="ebay_cancel();" />';
	echo '	</td>';
	echo '	</tr>';
	echo '</table>';
	echo '</div>';
	
	include("templates/".TEMPLATE_BACKEND."/footer.php");
?>
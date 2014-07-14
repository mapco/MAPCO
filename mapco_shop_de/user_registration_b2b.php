<?php
	include("config.php");
	$title='Registrierung Gewerbekunde';
	$login_required=true;
	
	include("templates/".TEMPLATE."/header.php");
	
	include("functions/cms_t.php");
	include("functions/mapco_gewerblich.php");
//	include("javascript/cms/FileUpload.php");
	
	//check, if already registered as b2b-customer
	$firstmod=0;
	$gewerblich=0;
	$reg=0;
	$datum='';
	$results=q("SELECT * FROM mapco_b2b_registration WHERE user_id=".$_SESSION["id_user"]." AND site_id=".$_SESSION["id_site"].";", $dbshop, __FILE__, __LINE__);
	if(mysqli_num_rows($results)>0)
	{
		$row=mysqli_fetch_array($results);
		$reg=1;
		$firstmod=$row["firstmod"];
		$datum = date("d.m.Y",$firstmod);
	}
	if(gewerblich($_SESSION["id_user"])) $gewerblich=1;
	
	//echo $gewerblich;
	//echo $reg;
?>
<script type="text/javascript" src="<?php echo PATH; ?>javascript/cms/FileUpload.php"></script>
<script type="text/javascript">

	var aktiv = '';
	var cnt = 0;
	
	function registerb2b()
	{
		if($('#company').val().length == 0) {show_message_dialog("<?php echo t("Sie müssen einen Firmennamen angeben")?>."); return;}
		if($('#company_voice').val().length == 0) {show_message_dialog("<?php echo t("Sie müssen einen Ansprechpartner angeben")?>."); return;}
		if($('#street').val().length == 0) {show_message_dialog("<?php echo t("Sie müssen Straße und Hausnummer angeben")?>."); return;}
		if($('#zip').val().length == 0) {show_message_dialog("<?php echo t("Sie müssen eine Postleitzahl angeben")?>."); return;}
		if($('#city').val().length == 0) {show_message_dialog("<?php echo t("Sie müssen einen Ort angeben")?>."); return;}
		if($('#tel').val().length == 0) {show_message_dialog("<?php echo t("Sie müssen eine Telefonnummer angeben")?>."); return;}
		if($('#tax_number').val().length == 0) {show_message_dialog("<?php echo t("Sie müssen eine Steuernummer angeben")?>."); return;}
		if($filename_temp.length == 0) {show_message_dialog("<?php echo t("Sie müssen eine Gewerbeanmeldung hochladen angeben")?>."); return;}
		
		var post_object = 						new Object();
		post_object["API"] = 					"cms";
		post_object["APIRequest"] =				"UserRegister";
		post_object["company"] = 				$('#company').val();
		post_object["company_voice"] = 			$('#company_voice').val();
		post_object["street"] = 				$('#street').val();
		post_object["zip"] = 					$('#zip').val();
		post_object["city"] = 					$('#city').val();
		post_object["tel"] = 					$('#tel').val();
		post_object["fax"] =					$('#fax').val();
		post_object["tax_number"] = 			$('#tax_number').val();
		post_object["trade_registration"] =		'';
		post_object["trade_filename"] =			$filename;
		post_object["trade_filename_temp"] =	$filename_temp;
		post_object["trade_fileext"] =			$fileext;
		post_object["trade_filesize"] =			$filesize;
		if($('#ship_adr_checkbox:checked').prop('checked') == true)
		{	
			post_object["ship_adr"] = 			1;
			post_object["ship_company"] =		$('#ship_company').val();
			post_object["ship_company_voice"] =	$('#ship_company_voice').val();
			post_object["ship_street"] =		$('#ship_street').val();
			post_object["ship_zip"] =			$('#ship_zip').val();
			post_object['ship_city'] =			$('#ship_city').val();
			post_object['ship_tel'] =			$('#ship_tel').val();
			post_object['ship_fax'] =			$('#ship_fax').val();
		}
		else
		{
			post_object["ship_adr"] = 			0;
			post_object["ship_company"] =		'';
			post_object["ship_company_voice"] =	'';
			post_object["ship_street"] =		'';
			post_object["ship_zip"] =			'';
			post_object['ship_city'] =			'';
			post_object['ship_tel'] =			'';
			post_object['ship_fax'] =			'';
		}
		post_object["mode"] = 			"b2b";
		wait_dialog_show();
		$.post("<?php echo PATH; ?>soa2/", post_object, function($data) 
		{ 
			//alert($data);
			try { $xml = $($.parseXML($data)); } catch ($err) { show_status2($err.message); wait_dialog_hide(); return; }
			if ( $xml.find("Ack").text()!="Success" ) { show_message_dialog('<?php echo t("Die Registrierung ist fehlgeschlagen");?>'); wait_dialog_hide(); return; }
			
			if($xml.find("b2b_already").text()=="1") {show_message_dialog('<?php echo t("Sie sind bereits als Gewerbekunde registriert");?>.'); wait_dialog_hide(); registerb2b_end(); return;}
			if($xml.find("reg_success").text()=="0") {show_message_dialog('<?php echo t("Die Registrierung ist fehlgeschlagen");?>.'); wait_dialog_hide(); return;}
			//b2b_register();
			registerb2b_end();			
			wait_dialog_hide();
		});
	}

	function registerb2b_end()
	{
		var main = $('#left_mid_right_column');
		main.empty();
		var text = $('<br /><br /><br /><p style="font-weight: bold; font-size: 22px"><?php echo t("Sie haben sich erfolgreich als Gewerbekunde registriert");?>!</p>');
		main.append(text);
		text = $('<br /><br /><br /><a href="<?php echo PATHLANG.$_SESSION["for_url"];?>" style="font-weight: bold; font-size: 15px"><?php echo t("Wenn Sie nicht in 5 Sekunden automatsch weitergeleitet werden, klicken Sie hier");?>.</a>');
		main.append(text);
		
		setTimeout(function(){location.href = '<?php echo PATHLANG.$_SESSION["for_url"];?>';},5000);
	}
	
	function shop_user_registerb2b_main()
	{
		//$filename = 'Keine Datei ausgewählt';
		
		if(<?php echo $gewerblich;?> == 1)
		{
			var main = $('#left_mid_right_column');
			var text = $('<br /><br /><br /><p style="font-weight: bold; font-size: 22px"><?php echo t("Sie sind bereits Gewerbekunde");?>!</p>');
			main.append(text);
			text = $('<br /><br /><br /><a href="<?php echo PATHLANG.$_SESSION["for_url"];?>" style="font-weight: bold; font-size: 15px"><?php echo t("Wenn Sie nicht in 5 Sekunden automatsch weitergeleitet werden, klicken Sie hier");?>.</a>');
			main.append(text);
			
			setTimeout(function(){location.href = '<?php echo PATHLANG;?>';},5000);
			return;
		}
		if(<?php echo $reg;?> == 1)
		{
			var main = $('#left_mid_right_column');
			var text = $('<br /><br /><br /><p style="font-weight: bold; font-size: 22px"><?php echo t("Sie haben sich bereits als Gewerbekunde registriert");?>!</p>');
			main.append(text);
			text = $('<br /><br /><br /><p style="font-weight: bold; font-size: 15px"><?php echo t("Datum der Registrierung");?>: <?php echo $datum;?></p>');
			main.append(text);
			text = $('<br /><br /><br /><a href="<?php echo PATHLANG;?>" style="font-weight: bold; font-size: 15px"><?php echo t("Weiter");?></a>');
			main.append(text);
			return;
		}
		
		var main = $('#left_mid_right_column');
		var text = $('<p style="font-weight: bold; font-size: 18px"><?php echo t("Bitte registrieren Sie sich als Gewerbekunde");?>:</p>');
		main.append(text);
		var table = $('<table></table>');
			var tr = $('<tr></tr>');
				var td = $('<td style="font-weight: bold; text-align: right; width: 50%"><?php echo t("Firmenname");?>:</td>');
				tr.append(td);
				td = $('<td style="text-align: left;"><input type="text" id="company" style="width: 150px" /><span style="color: #FF0000;">*</span></td>');
				tr.append(td);
			table.append(tr);
			tr = $('<tr></tr>');
				td = $('<td style="font-weight: bold; text-align: right; width: 50%"><?php echo t("Ansprechpartner");?>:</td>');
				tr.append(td);
				td = $('<td style="text-align: left;"><input type="text" id="company_voice" style="width: 150px" /><span style="color: #FF0000;">*</span></td>');
				tr.append(td);
			table.append(tr);
			tr = $('<tr></tr>');
				td = $('<td style="font-weight: bold; text-align: right; width: 50%"><?php echo t("Straße/Hausnummer");?>:</td>');
				tr.append(td);
				td = $('<td style="text-align: left;"><input type="text" id="street" style="width: 150px" /><span style="color: #FF0000;">*</span></td>');
				tr.append(td);
			table.append(tr);
			tr = $('<tr></tr>');
				td = $('<td style="font-weight: bold; text-align: right; width: 50%"><?php echo t("PLZ");?>:</td>');
				tr.append(td);
				td = $('<td style="text-align: left;"><input type="text" id="zip" style="width: 150px" /><span style="color: #FF0000;">*</span></td>');
				tr.append(td);
			table.append(tr);
			tr = $('<tr></tr>');
				td = $('<td style="font-weight: bold; text-align: right; width: 50%"><?php echo t("Ort");?>:</td>');
				tr.append(td);
				td = $('<td style="text-align: left;"><input type="text" id="city" style="width: 150px" /><span style="color: #FF0000;">*</span></td>');
				tr.append(td);
			table.append(tr);
			tr = $('<tr></tr>');
				td = $('<td style="font-weight: bold; text-align: right; width: 50%"><?php echo t("Telefonnummer");?>:</td>');
				tr.append(td);
				td = $('<td style="text-align: left;"><input type="text" id="tel" style="width: 150px" /><span style="color: #FF0000;">*</span></td>');
				tr.append(td);
			table.append(tr);
			tr = $('<tr></tr>');
				td = $('<td style="font-weight: bold; text-align: right; width: 50%"><?php echo t("Faxnummer");?>:</td>');
				tr.append(td);
				td = $('<td style="text-align: left;"><input type="text" id="fax" style="width: 150px" /></td>');
				tr.append(td);
			table.append(tr);
			tr = $('<tr></tr>');
				td = $('<td style="font-weight: bold; text-align: right; width: 50%; vertical-align:top;"><?php echo t("Steuernummer");?>:</td>');
				tr.append(td);
				td = $('<td style="text-align: left;"><input type="text" id="tax_number" style="width: 150px" /><span style="color: #FF0000;">*</span><br /><p><?php  echo t('Hier können Sie Ihre Steuernummer auf EU-Gültigkeit prüfen'); ?>: <a target="_blank" href="http://ec.europa.eu/taxation_customs/vies/?locale=<?php echo $_SESSION["lang"]; ?>">http://ec.europa.eu/taxation_customs/vies/</a></p></td>');
				tr.append(td);
			table.append(tr);
			tr = $('<tr></tr>');
				td = $('<td style="font-weight: bold; text-align: right; width: 50%;"><?php echo t("Gewerbeanmeldung (als Datei)");?>:</td>');
				tr.append(td);
				td = $('<td style="text-align: left;"><input type="button" id="trade_registration_button" value="<?php echo t("Datei hochladen");?>" /><span id="trade_registration_text"><?php echo t("Keine Datei ausgewählt");?></span><span style="color: #FF0000;">*</span></td>');
				tr.append(td);
			table.append(tr);
			tr = $('<tr></tr>');
				td = $('<td></td>');
				tr.append(td);
				td = $('<td style="text-align: left"><span style="color: #FF0000">*</span> = <span style="font-size: 12px"><?php echo t("Pflichtfelder");?></span></td>');
				tr.append(td);
			table.append(tr);
			tr = $('<tr></tr>');
				td = $('<td></td>');
				tr.append(td);
				//td = $('<td style="text-align: left"><input type="button" class="ship_adr" id="ship_adr_button" value="<?php echo t("Lieferanschrift (falls abweichend)");?>" /><input type="button" class="ship_adr" id="ship_adr_button_2" value="<?php echo t("Lieferanschrift ausblenden");?>" style="display: none;" /></td>');
				td = $('<td style="text-align: left"><input type="checkbox" id="ship_adr_checkbox" value="1"><span style="font-size: 12px; font-weight: bold; vertical-align: 2px;"><?php echo t("abweichende Lieferadresse");?></span></td>');
				tr.append(td);
			table.append(tr);
		main.append(table);
		
		var text = $('<p class="ship_adr" style="display: none; font-weight: bold; font-size: 18px"><?php echo t("Lieferadresse");?>:</p>');
		main.append(text);
		var table = $('<table class="ship_adr" style="display: none"></table>');
			var tr = $('<tr></tr>');
				var td = $('<td style="font-weight: bold; text-align: right; width: 50%"><?php echo t("Firmenname");?>:</td>');
				tr.append(td);
				td = $('<td style="text-align: left;"><input type="text" id="ship_company" style="width: 150px" /></td>');
				tr.append(td);
			table.append(tr);
			tr = $('<tr></tr>');
				td = $('<td style="font-weight: bold; text-align: right; width: 50%"><?php echo t("Ansprechpartner");?>:</td>');
				tr.append(td);
				td = $('<td style="text-align: left;"><input type="text" id="ship_company_voice" style="width: 150px" /></td>');
				tr.append(td);
			table.append(tr);
			tr = $('<tr></tr>');
				td = $('<td style="font-weight: bold; text-align: right; width: 50%"><?php echo t("Straße/Hausnummer");?>:</td>');
				tr.append(td);
				td = $('<td style="text-align: left;"><input type="text" id="ship_street" style="width: 150px" /></td>');
				tr.append(td);
			table.append(tr);
			tr = $('<tr></tr>');
				td = $('<td style="font-weight: bold; text-align: right; width: 50%"><?php echo t("PLZ");?>:</td>');
				tr.append(td);
				td = $('<td style="text-align: left;"><input type="text" id="ship_zip" style="width: 150px" /></td>');
				tr.append(td);
			table.append(tr);
			tr = $('<tr></tr>');
				td = $('<td style="font-weight: bold; text-align: right; width: 50%"><?php echo t("Ort");?>:</td>');
				tr.append(td);
				td = $('<td style="text-align: left;"><input type="text" id="ship_city" style="width: 150px" /></td>');
				tr.append(td);
			table.append(tr);
			tr = $('<tr></tr>');
				td = $('<td style="font-weight: bold; text-align: right; width: 50%"><?php echo t("Telefonnummer");?>:</td>');
				tr.append(td);
				td = $('<td style="text-align: left;"><input type="text" id="ship_tel" style="width: 150px" /></td>');
				tr.append(td);
			table.append(tr);
			tr = $('<tr></tr>');
				td = $('<td style="font-weight: bold; text-align: right; width: 50%"><?php echo t("Faxnummer");?>:</td>');
				tr.append(td);
				td = $('<td style="text-align: left;"><input type="text" id="ship_fax" style="width: 150px" /></td>');
				tr.append(td);
			table.append(tr);
		main.append(table);
		
		text = $('<br /><input type="button" id="registerb2b_button" value="<?php echo t("Registrieren");?>" style="font-weight: bold; height: 30px; width: 150px"><br /><br />');
		main.append(text);
		
		text = $('<p style="font-size: 12px;"><?php echo t("Oder senden Sie uns Ihre Firmendaten mit Gewerbeanmeldung und/oder HRB-Eintrag, Umsatzsteuerdaten, Rechnungs- und Lieferadresse");?> <b><?php echo t("als Fax");?></b>: +49 (0) 33845 / 4 10 32 <br /><?php echo t("oder");?> <b><?php echo t("per Mail");?></b> <?php echo t("an");?>: <a href="mailto:info@mapco.de">info@mapco.de</a></p>');
		main.append(text);
		text = $('<p style="font-size: 12px;"><?php echo t("Sie erhalten dann von uns eine Bestätigung und können rund um die Uhr Bestellungen aus unserem qualitativ hochwertigen Angebot vornehmen");?>. <?php echo t("Denn bei uns erhalten Sie");?> <b><?php echo t("Autoteile vom Hersteller");?>!</b></p>');
		main.append(text);
		
		$('#registerb2b_button').click(function(){
			//alert($filename);
			//alert($filename_temp);
			registerb2b();
		});
/*		
		$('#ship_adr_button').click(function(){
			$('.ship_adr').toggle(500);
		});
		
		$('#ship_adr_button_2').click(function(){
			$('.ship_adr').toggle(500);
		});
*/
		$('#ship_adr_checkbox').change(function(){
			$('.ship_adr').toggle(500);
		});
		
		$('#trade_registration_button').click(function(){
			file_upload();
			cnt = 0;
			aktiv = window.setInterval("show_file_name()", 500);
			//$('#trade_registration_text').text('Datei hochgeladen');
		});
		
	}
	
	function show_file_name()
	{
		cnt++;
		//alert($('#trade_registration_text').text());
		if($filename!=$('#trade_registration_text').text() && $filename!='')
		{
			$('#trade_registration_text').text($filename);
			window.clearInterval(aktiv);
		}
		if(cnt>120)
			window.clearInterval(aktiv);
	}
	
	function show_message_dialog(message)
	{
		$("#message").html(message);
		$("#message").dialog
		({	buttons:
			[
				{ text: "Ok", click: function() {$(this).dialog("close");} }
			],
			closeText:"<?php echo t("Fenster schließen");?>",
			hide: { effect: 'drop', direction: "up" },
			modal:true,
			resizable:false,
			show: { effect: 'drop', direction: "up" },
			title:"<?php echo t("Achtung!");?>",
			width:300
		});
	}
	
</script>

<?php
	echo '<div id="left_mid_right_column" style="min-height: 450px; text-align: center"></div>';
	echo '<div id="message"></div>';
	include("templates/".TEMPLATE."/footer.php");
?>
<script type="text/javascript">shop_user_registerb2b_main();</script>
<?php
	include("config.php");
	$title='Registrierung';
	include("templates/".TEMPLATE."/header.php");
	
	include("functions/cms_t.php");
	//print_r($_SESSION);
	
	//prüfen, ob eine Gewerbeanmeldung möglich sein soll
	$b2b_reg=0;
	$results=q("SELECT * FROM shop_shops WHERE site_id=".$_SESSION["id_site"].";", $dbshop, __FILE__, __LINE__);
	if(mysqli_num_rows($results)>0)
	{
		$row=mysqli_fetch_array($results);
		$b2b_reg=$row["b2b"];
	}
	
	//cart merge?
	$in_cart=0;
	$results=q("SELECT * FROM shop_carts WHERE session_id='".session_id()."' AND shop_id=".$_SESSION["id_shop"].";", $dbshop, __FILE__, __LINE__);
	if(mysqli_num_rows($results)>0) $in_cart=1;
?>

<script type="text/javascript">
	
	function b2b_register()
	{
		var main = $('#left_mid_right_column');
		main.empty();
		var text = $('<br /><br /><br /><p style="font-weight: bold; font-size: 22px"><?php echo t("Sie haben sich erfolgreich registriert");?>!</p>');
		main.append(text);
		if(<?php echo $b2b_reg;?>==1)
		{
			text = $('<br /><br /><br /><p style="font-weight: bold; font-size: 15px"><?php echo t("Möchten Sie sich als Gewerbekunde registrieren");?>?</p>');
			main.append(text);
			text = $('<br /><input type="button" id="ok_button" value="<?php echo t("Ja, registrieren");?>"><input type="button" id="cancel_button" value="<?php echo t("Nein");?>">');
			main.append(text);
			
			$('#ok_button').click(function(){
				location.href = '<?php echo PATHLANG;?>gewerberegistrierung/';
			});
			
			$('#cancel_button').click(function(){
				location.href =  '<?php echo PATHLANG.$_SESSION["for_url"];?>';
			});
		}
		else
		{
			text = $('<br /><br /><br /><a href="<?php echo PATHLANG.$_SESSION["for_url"];?>" style="font-weight: bold; font-size: 15px"><?php echo t("Wenn Sie nicht in 5 Sekunden automatsch weitergeleitet werden, klicken Sie hier");?>.</a>');
			main.append(text);
			
			setTimeout(function(){location.href = '<?php echo PATHLANG.$_SESSION["for_url"];?>';},5000);
		}
	}
	
	function register()
	{
		//input check
		if($('#lastname').val().length == 0) {show_message_dialog("<?php echo t("Das Nachname-Feld darf nicht leer sein")?>."); return;}
		if($('#username').val().length == 0) {show_message_dialog("<?php echo t("Das Benutzername-Feld darf nicht leer sein")?>."); return;}
		if($('#username').val().length < 5) {show_message_dialog("<?php echo t("Der Benutzername muß mindestens 5 Zeichen enthalten")?>."); return;}
		if($('#username').val().match(/[^a-zA-Z0-9_]/)!=null) 
		{
			show_message_dialog("<?php echo t("Der Benutzername darf nur die folgenden Zeichen enthalten: Buchstaben: (a-z)(A-Z), Zahlen (0-9) und das Sonderzeichen (_)")?>.");
			return;
		}
		if($('#password').val().length == 0) {show_message_dialog("<?php echo t("Das Passwort-Feld darf nicht leer sein")?>."); return;}
		if($('#password').val().length < 5) {show_message_dialog("<?php echo t("Das Passwort muß mindestens 5 Zeichen enthalten")?>."); return;}
		if($('#password').val().match(/[^a-zA-Z0-9_]/)!=null) 
		{
			show_message_dialog("<?php echo t("Das Passwort darf nur die folgenden Zeichen enthalten: Buchstaben: (a-z)(A-Z), Zahlen (0-9) und das Sonderzeichen (_)")?>.");
			return;
		}
		if($('#password').val()!=$('#password_repeat').val()) {show_message_dialog("<?php echo t("Die Passworte stimmen nicht überein")?>."); return;}	
		if($('#usermail').val().length == 0) {show_message_dialog("<?php echo t("Sie müssen eine Email-Adresse angeben")?>."); return;}	
		if($('#usermail').val().indexOf('@')==-1 || $('#usermail').val().indexOf('.')==-1) {show_message_dialog("<?php echo t("Sie müssen eine gültige Email-Adresse angeben")?>."); return;}
		
		
		var post_object = 			new Object();
		post_object["API"] = 		"cms";
		post_object["APIRequest"] = "UserRegister";
		post_object["gender"] = 	$('#gender').val();
		post_object["firstname"] = 	$('#firstname').val();
		post_object["lastname"] = 	$('#lastname').val();
		post_object["username"] = 	$('#username').val();
		post_object["password"] = 	$('#password').val();
		post_object["usermail"] = 	$('#usermail').val();
		
		if($('#newsletter:checked').prop('checked') == true)
			post_object["newsletter"] = 1;
		else
			post_object["newsletter"] = 0;
		
		post_object["mode"] = "b2c";
		
		if($('#cart_merge_cb').length > 0 && $('#cart_merge_cb:checked').prop('checked') == true)
			post_object['cart'] = 'merge';
		else if($('#cart_merge_cb').length > 0)
			post_object['cart'] = 'delete';
		//alert(post_object['cart']);
		wait_dialog_show();
		$.post("<?php echo PATH; ?>soa2/", post_object, function($data) 
		{ 
			//alert($data);
			try { $xml = $($.parseXML($data)); } catch ($err) { show_status2($err.message); wait_dialog_hide(); return; }
			if ( $xml.find("Ack").text()!="Success" ) { show_message_dialog('<?php echo t("Die Registrierung ist fehlgeschlagen");?>'); wait_dialog_hide(); return; }
			
			if($xml.find("username_exist").text()=="1") {show_message_dialog('<?php echo t("Der Benutzername ist bereits vergeben");?>.'); wait_dialog_hide(); return;}
			if($xml.find("usermail_exist").text()=="1") {show_message_dialog('<?php echo t("Die Email-Adresse ist bereits vergeben, haben Sie vielleicht schon ein Benutzerkonto bei uns?");?>'); wait_dialog_hide(); return;}
			if($xml.find("reg_success").text()=="0") {show_message_dialog('<?php echo t("Die Registrierung ist fehlgeschlagen");?>.'); wait_dialog_hide(); return;}
			
			cart_update();
			b2b_register();
			
			wait_dialog_hide();
		});
	}
	
	function shop_user_register_main()
	{
		var main = $('#left_mid_right_column');
		var text = $('<p style="font-weight: bold; font-size: 18px"><?php echo t("Hier können Sie sich registrieren");?>:</p>');
		main.append(text);
		var table = $('<table></table>');
			var tr = $('<tr></tr>');
				var td = $('<td style="font-weight: bold; text-align: right; width: 50%"><?php echo t("Anrede");?>:</td>');
				tr.append(td);
				td = $('<td style="text-align: left"></td>');
				var select_box = $('<select id="gender"></select>');
				var option = $('<option value="0" selected><?php echo t("Herr");?></option>');
				select_box.append(option);
				option = $('<option value="1"><?php echo t("Frau");?></option>');
				select_box.append(option);
				td.append(select_box);
				tr.append(td);
			table.append(tr);
			tr = $('<tr></tr>');
				var td = $('<td style="font-weight: bold; text-align: right"><?php echo t("Vorname");?>:</td>');
				tr.append(td);
				td = $('<td style="text-align: left"><input type="text" id="firstname" style="width: 150px"></td>');
				tr.append(td);
			table.append(tr);
			tr = $('<tr></tr>');
				var td = $('<td style="font-weight: bold; text-align: right"><?php echo t("Nachname");?>:</td>');
				tr.append(td);
				td = $('<td style="text-align: left"><input type="text" id="lastname" style="width: 150px"><span style="color: #FF0000">*</span></td>');
				tr.append(td);
			table.append(tr);
			tr = $('<tr></tr>');
				var td = $('<td style="font-weight: bold; text-align: right"><?php echo t("Benutzername");?>:</td>');
				tr.append(td);
				td = $('<td style="text-align: left"><input type="text" id="username" style="width: 150px"><span style="color: #FF0000">*</span></td>');
				tr.append(td);
			table.append(tr);
			tr = $('<tr></tr>');
				var td = $('<td style="font-weight: bold; text-align: right"><?php echo t("Passwort");?>:</td>');
				tr.append(td);
				td = $('<td style="text-align: left"><input type="password" id="password" style="width: 150px"><span style="color: #FF0000">*</span></td>');
				tr.append(td);
			table.append(tr);
			tr = $('<tr></tr>');
				var td = $('<td style="font-weight: bold; text-align: right"><?php echo t("Passwort wiederholen");?>:</td>');
				tr.append(td);
				td = $('<td style="text-align: left"><input type="password" id="password_repeat" style="width: 150px"><span style="color: #FF0000">*</span></td>');
				tr.append(td);
			table.append(tr);
			tr = $('<tr></tr>');
				var td = $('<td style="font-weight: bold; text-align: right"><?php echo t("Email-Adresse");?>:</td>');
				tr.append(td);
				td = $('<td style="text-align: left"><input type="text" id="usermail" style="width: 150px"><span style="color: #FF0000">*</span></td>');
				tr.append(td);
			table.append(tr);
			tr = $('<tr></tr>');
				var td = $('<td style="font-weight: bold; text-align: right"><?php echo t("Newsletter");?>:</td>');
				tr.append(td);
				td = $('<td style="text-align: left"><input type="checkbox" id="newsletter" value="1"><span style="font-size: 12px"><?php echo t("Ja ich möchte den MAPCO-Newsletter abonnieren");?>! <br /> &nbsp &nbsp (<?php echo t("Abmeldung jederzeit möglich");?>)</span></td>');
				tr.append(td);
			table.append(tr);
			tr = $('<tr></tr>');
				var td = $('<td style="font-weight: bold; text-align: right"></td>');
				tr.append(td);
				td = $('<td style="text-align: left"><span style="color: #FF0000">*</span> = <span style="font-size: 12px"><?php echo t("Pflichtfelder");?></span></td>');
				tr.append(td);
			table.append(tr);
		if(<?php echo $in_cart;?>==1)
		{
			tr = $('<tr></tr>');
			var td = $('<td style="font-weight: bold; text-align: right"></td>');
			tr.append(td);
			td = $('<td style="text-align: left"><input type="checkbox" id="cart_merge_cb" checked /><span style="font-size: 12px"><?php echo t("Warenkorb übernehmen");?></span></td>');
			tr.append(td);
			table.append(tr);
		}
		main.append(table);
		text = $('<br /><input type="button" id="register_button" value="<?php echo t("Registrieren");?>" style="font-weight: bold; height: 30px; width: 150px">');
		main.append(text);
	
		$('#register_button').click(function()
		{
			register();
		});
	}
	
	function show_message_dialog(message)
	{
		$("#message").html(message);
		$("#message").dialog
		({	buttons:
			[
				{ text: "Ok", click: function() {$(this).dialog("close");} }
			],
			closeText:"Fenster schließen",
			hide: { effect: 'drop', direction: "up" },
			modal:true,
			resizable:false,
			show: { effect: 'drop', direction: "up" },
			title:"Achtung!",
			width:300
		});
	}
	
</script>

<?php
	echo '<div id="left_mid_right_column" style="min-height: 450px; text-align: center"></div>';
	echo '<div id="message"></div>';
	include("templates/".TEMPLATE."/footer.php");
?>
<script type="text/javascript">shop_user_register_main();</script>
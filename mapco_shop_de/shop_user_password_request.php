<?php
	//$login_required=true;
	$title="Passwort anfordern";
	
	include("config.php");
	include("templates/".TEMPLATE."/header.php");
	include("functions/cms_t.php");
	//include("modules/cms_leftcolumn_shop.php");
?>

<script type="text/javascript">

function password_request()
{
	wait_dialog_show();
	$.post("<?php echo PATH;?>soa2/", { API: "cms", APIRequest: "UserPasswordRequestMailSend", user_name_mail: $('#user_name_mail').val()}, function($data)
	{
		wait_dialog_hide();
		try { $xml = $($.parseXML($data)); } catch ($err) { show_status($err.message); return; }
		if ( $xml.find("Code").text()=="9785" ) { show_message_dialog("Keine E-Mail-Adresse hinterlegt."); return; }
		if ( $xml.find("Ack").text()!="Success" ) { show_status2($data); return; }

		if($xml.find("mail_send").text()=='0')
		{
			show_message_dialog('<?php echo t("Dieser Benutzername/Diese Emailadresse ist auf dieser Seite nicht registriert. Befinden Sie sich vielleicht im falschen MAPCO-Shop?");?>');
			return;
		}

		$("#message").html("<?php echo t("Sie erhalten in Kürze eine email mit weiteren Informationen zu Ihrem Passwort.");?>");
		$("#message").dialog
		({	buttons:
			[
				{ text: "<?php echo t("Ok"); ?>", click: function() {location.href = "<?php echo PATH;?>aktuelles/"; $(this).dialog("close");} }
			],
			closeText:"<?php echo t("Fenster schließen"); ?>",
			hide: { effect: 'drop', direction: "up" },
			modal:true,
			resizable:false,
			show: { effect: 'drop', direction: "up" },
			title:"<?php echo t("Achtung"); ?>!",
			width:300
		});
	});
}

function shop_user_password_request_main()
{
	var main = $('#left_mid_right_column');
	var text = $('<br /><br /><br /><br /><p style="font-size: 18px; font-weight: bold"><?php echo t("Bitte geben Sie Ihren Benutzernamen oder Ihre Emailadresse ein");?>:</p>');
	main.append(text);
	var input = $('<input type="text" id="user_name_mail" style="width: 200px">');
	main.append(input);
	input = $('<br /><br /><input type="button" id="request_button" value="<?php echo t("Passwort anfordern");?>" style="cursor: pointer">');
	main.append(input);
	
	$('#user_name_mail').focus();
	
	$('#request_button').click(
		function()
		{
			password_request();
		}
	);
	
	$('#user_name_mail').keydown(
		function(event)
		{
			if(event.keyCode==13)
				$('#request_button').focus();
		}
	);
}

function show_message_dialog(message)
	{
		$("#message").html(message);
		$("#message").dialog
		({	buttons:
			[
				{ text: "<?php echo t("Ok"); ?>", click: function() {$(this).dialog("close");} }
			],
			closeText:"<?php echo t("Fenster schließen"); ?>",
			hide: { effect: 'drop', direction: "up" },
			modal:true,
			resizable:false,
			show: { effect: 'drop', direction: "up" },
			title:"<?php echo t("Achtung"); ?>!",
			width:300
		});
	}

</script>

<?php
	echo '<div id="left_mid_right_column" style="min-height: 450px; text-align: center"></div>';
	echo '<div id="message"></div>';
	include("templates/".TEMPLATE."/footer.php");
	
?>
<script type="text/javascript">shop_user_password_request_main();</script>
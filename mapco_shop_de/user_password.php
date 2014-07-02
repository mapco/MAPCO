<?php
	$login_required=true;
	$title="Neues Passwort";
	
	include("config.php");
	include("templates/".TEMPLATE."/header.php");
	include("functions/cms_t.php");
	//include("modules/cms_leftcolumn_shop.php");
?>

<script type="text/javascript">

function password_save()
{
	if($('#password_one').val().length < 5) {show_message_dialog("<?php echo t("Das Passwort muß mindestens 5 Zeichen enthalten")?>."); return;}
	if($('#password_one').val().match(/[^a-zA-Z0-9_]/)!=null) 
	{
		show_message_dialog("<?php echo t("Das Passwort darf nur folgende Zeichen enthalten: Buchstaben: (a-z)(A-Z), Zahlen (0-9) und das Sonderzeichen (_)")?>.");
		return;
	}
	if($('#password_one').val()!=$('#password_two').val())
	{
		$('#password_one').focus();
		show_message_dialog("<?php echo t("Die eingegebenen Passworte sind nicht gleich!");?>");
		$('#password_one').val('');
		$('#password_two').val('');
		return;
	}
	else if($('#password_one').val()==$('#password_two').val())
	{
		$.post("<?php echo PATH;?>soa2/", { API: "cms", APIRequest: "UserPasswordChange", pw: $('#password_one').val()}, 
			function($data)
			{
				$xml = $($.parseXML($data));
				$ack = $xml.find("Ack").text();
				if($ack == "Success")
				{
					$("#message").html("<?php echo t("Ihr neues Passwort wurde erfolgreich gespeichert");?>");
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
					//show_message_dialog("<?php echo t("Ihr neues Passwort wurde erfolgreich gespeichert");?>");
					//location.href = "<?php echo PATH;?>aktuelles/";
				}
			}
		);
	}
}

function shop_user_password_main()
{
	var main = $('#left_mid_right_column');
	//var input = $('<input id="username">');
	//main.append(input);
	var text = $('<br /><br /><p style="font-size: 18px; font-weight: bold"><?php echo t("Bitte geben Sie ein neues Passwort ein");?>:</p>');
	main.append(text);
	var input = $('<input type="password" id="password_one">');
	main.append(input);
	text = $('<p style="font-size: 18px; font-weight: bold"><?php echo t("Bitte wiederholen Sie das neue Passwort")?>:</p>');
	main.append(text);
	input = $('<input type="password" id="password_two"><br /><br /><br />');
	main.append(input);
	input = $('<input type="button" id="save_button" value="<?php echo t("Passwort speichern");?>" style="cursor: pointer">');
	main.append(input);
	
	$('#password_one').focus();
	
	$('#save_button').click(
		function()
		{
			password_save();
		}
	);
	
	$('#password_one').keydown(
		function(event)
		{
			if(event.keyCode==13)
				$('#password_two').focus();
		}
	);
	
	$('#password_two').keydown(
		function(event)
		{
			if(event.keyCode==13)
				$('#save_button').focus();
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
	echo '<div id="left_mid_right_column" style="height: 450px; text-align: center"></div>';
	echo '<div id="message"></div>';
	include("templates/".TEMPLATE."/footer.php");
	
?>
<script type="text/javascript">shop_user_password_main();</script>
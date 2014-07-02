<?php
	include("config.php");
	$title='Anmeldung';
	include("templates/".TEMPLATE."/header.php");
	include("functions/cms_t.php");
	include("functions/cms_createPassword.php");
	//include("modules/cms_leftcolumn_shop.php");
	
	//random salt
	//$salt=md5(mt_rand(0,65337).time());
	//$_SESSION["salt"]=$salt;
	//echo'************************'.$_SESSION["get_url"];
	if(!isset($_SESSION["get_url"])) $_SESSION["get_url"]='';
	if(isset($_SESSION["id_user"]))
	{
		if(isset($_SESSION["get_url"])) unset($_SESSION["get_url"]);
		echo '<script type="text/javascript">location.href = "'.PATHLANG.'";</script>';
	}
	
	//$salt=createPassword(32);
	//echo $salt;
	/*$pw=md5("wwwww");
	$pw=md5($pw."EwSwmwd3FbaDUtDSeVvU1qHLbrpw1Pv9L");
	$pw=md5($pw.PEPPER);
	echo $pw;*/
?>

<script type="text/javascript">
	
	var for_link = '';
	var for_shop_id = '0';
	var for_user_id = '0';
	
	function cart_merge(mode)
	{
		$.post("<?php echo PATH;?>soa2/", {API: "shop", APIRequest: "CartMerge", mode: mode, for_link: for_link, for_shop_id: for_shop_id, for_user_id: for_user_id}, 
			function($data)
			{
				//alert($data);
				$xml = $($.parseXML($data));
				$ack = $xml.find("Ack").text()
				if($ack == "Success")
				{
					if(for_link != '')
						location.href = for_link;
					else
						location.href = "<?php echo $_SESSION["get_url"];?>";
						//location.href = "<?php echo PATHLANG.$_SESSION["get_url"];?>";
				}
			}
		);
	}
	
	function cart_merge_dialog(cart_items)
	{
		$('#message').empty();
		var message = $('#message');
		var p = $('<p><?php echo t("In Ihrem Warenkorb liegen folgende Artikel, sollen diese in Ihr Benutzerkonto übernommen werden?");?></p>');
		message.append(p);
		var table = $('<table></table>');
		var tr = $('<tr></tr>');
		var th = $('<th style="background-color: #DDDDDD; border-color: #DDDDDD; border-style: solid; border-width: 1px; text-align: left"><?php echo t("Menge");?></th>');
		tr.append(th);
		th = $('<th style="background-color: #DDDDDD; border-color: #DDDDDD; border-style: solid; border-width: 1px; text-align: left"><?php echo t("Artikel");?></th>');
		tr.append(th);
		table.append(tr);
		var td;
		for(a in cart_items)
		{
			tr = $('<tr></tr>');
			td = $('<td style="background-color: #FFFFFF; border-color: #DDDDDD; border-style: solid; border-width: 1px; text-align: left">' + cart_items[a]["amount"] + '</td>');
			tr.append(td);
			td = $('<td style="background-color: #FFFFFF; border-color: #DDDDDD; border-style: solid; border-width: 1px; text-align: left">' + cart_items[a]["title"] + '</td>');
			tr.append(td);
			table.append(tr);
		}
		message.append(table);
		$("#message").dialog
		({	buttons:
			[
				{ text: "<?php echo t("Ja"); ?>", click: function() {cart_merge("merge"); $(this).dialog("close");}},
				{ text: "<?php echo t("Nein"); ?>", click: function() {cart_merge("delete"); $(this).dialog("close");}} 
			],
			closeText:"<?php echo t("Fenster schließen"); ?>",
			hide: { effect: 'drop', direction: "up" },
			modal:true,
			resizable:false,
			show: { effect: 'drop', direction: "up" },
			title:"<?php echo t("Warenkorb übernehmen"); ?>!",
			width:500
		});
	}
	
	function shop_user_login_main()
	{
		
		var main = $('#left_mid_right_column');
		var text = $('<br /><br /><p style="font-weight: bold; font-size: 18px"><?php echo t("Bitte melden Sie sich an");?>:</p>');
		main.append(text);
		text = $('<p style="display: inline; font-weight: bold;"><?php echo t("Benutzername");?>:</p><p style="display: inline"><?php echo t(" (oder E-mail-Adresse)");?>:</p><br />');
		main.append(text);
		var input = $('<input type="text" id="username" name="username"><br /><br />');
		main.append(input);
		text = $('<p style="display: inline; font-weight: bold;"><?php echo t("Passwort");?>:</p><br />');
		main.append(text);
		input = $('<input type="password" id="password" name="password"><br />');
		main.append(input);
		input = $('<input type="button" id="login_button" value="<?php echo t("Anmelden");?>" style="cursor: pointer; margin-left: 457px">');
		main.append(input);
		var link_text = $('<br /><br /><br /><br /><a href="<?php echo PATHLANG;?>passwort-anfrage/" style="cursor: pointer; font-size: 12px"><?php echo t("Haben Sie Ihr Passwort vergessen? Wir schicken Ihnen gerne ein neues.");?></a>');
		main.append(link_text);
		if(<?php echo $_SESSION["id_site"]?>==2)
			link_text = $('<br /><br /><a href="<?php echo PATHLANG;?>registrieren/" style="cursor: pointer; font-size: 12px"><?php echo t("Hier können Sie sich registrieren.");?></a>');
		else
			link_text = $('<br /><br /><a href="<?php echo PATHLANG;?>online-shop/registrieren/" style="cursor: pointer; font-size: 12px"><?php echo t("Hier können Sie sich registrieren.");?></a>');
		//link_text = $('<br /><br /><a href="<?php echo PATHLANG;?>register/" style="cursor: pointer; font-size: 12px"><?php echo t("Hier können Sie sich registrieren.");?></a>');
		main.append(link_text);
		
		$('#username').focus();
		
		$('#login_button').click(
			function()
			{
				user_login();
			}
		);
		
		$('#username').keydown(function(event){    
			if(event.keyCode==13){
				$('#password').focus();
			   //$('#login_button').trigger('click');
			}
		});
		
		$('#password').keydown(function(event){    
			if(event.keyCode==13){
			   $('#login_button').trigger('click');
			}
		});
		

/*
		var main = $('#left_mid_right_column');
		var text = $('<br /><br /><p style="font-weight: bold; font-size: 18px"><?php echo t("Bitte melden Sie sich an");?>:</p>');
		main.append(text);
		text = $('<iframe id="remember" name="remember" src="/content/blank" style="display: none"></iframe>');
		main.append(text);
		var form = $('<form target="remember" method="post" action="/content/blank"></form>');
		//var fieldset = $('<fieldset></fieldset>');
		var label = $('<label for="username">Benutzername (oder E-mail-Adresse):</label>');
		form.append(label);
		var input = $('<br /><input type="text" name="username" id="username" value="">');
		form.append(input);
		label = $('<br /><label for="password">Passwort:</label>');
		form.append(label);
		input = $('<br /><input type="password" name="password" id="password" value="">');
		form.append(input);
		//form.append(fieldset);
		var button = $('<button type="submit" style="display: none"></button>');
		form.append(button);
		main.append(form);
		input = $('<br /><input type="button" id="login_button" value="<?php echo t("Anmelden");?>" style="cursor: pointer; margin-left: 457px">');
		main.append(input);
		var link_text = $('<br /><br /><br /><br /><a href="<?php echo PATHLANG;?>passwort-anfrage/" style="cursor: pointer; font-size: 12px"><?php echo t("Haben Sie Ihr Passwort vergessen? Wir schicken Ihnen gerne ein neues.");?></a>');
		main.append(link_text);
		link_text = $('<br /><br /><a href="<?php echo PATHLANG;?>online-shop/registrieren/" style="cursor: pointer; font-size: 12px"><?php echo t("Hier können Sie sich registrieren.");?></a>');
		//link_text = $('<br /><br /><a href="<?php echo PATHLANG;?>register/" style="cursor: pointer; font-size: 12px"><?php echo t("Hier können Sie sich registrieren.");?></a>');
		main.append(link_text);
		
		$('#login_button').click(function(){
			$('form').submit();
			//user_login();
		});
		
		$('#username').keydown(function(event){    
			if(event.keyCode==13){
				$('#password').focus();
			   //$('#login_button').trigger('click');
			}
		});
		
		$('#password').keydown(function(event){    
			if(event.keyCode==13){
			   $('#login_button').trigger('click');
			}
		});
*/
		/*
		text = $('<p style="display: inline; font-weight: bold;"><?php echo t("Benutzername");?>:</p><p style="display: inline"><?php echo t(" (oder E-mail-Adresse)");?>:</p><br />');
		main.append(text);
		var input = $('<input type="text" id="username" name="username"><br /><br />');
		main.append(input);
		text = $('<p style="display: inline; font-weight: bold;"><?php echo t("Passwort");?>:</p><br />');
		main.append(text);
		input = $('<input type="password" id="password" name="password"><br />');
		main.append(input);
		input = $('<input type="button" id="login_button" value="<?php echo t("Anmelden");?>" style="cursor: pointer; margin-left: 457px">');
		main.append(input);
		var link_text = $('<br /><br /><br /><br /><a href="<?php echo PATHLANG;?>passwort-anfrage/" style="cursor: pointer; font-size: 12px"><?php echo t("Haben Sie Ihr Passwort vergessen? Wir schicken Ihnen gerne ein neues.");?></a>');
		main.append(link_text);
		link_text = $('<br /><br /><a href="<?php echo PATHLANG;?>online-shop/registrieren/" style="cursor: pointer; font-size: 12px"><?php echo t("Hier können Sie sich registrieren.");?></a>');
		//link_text = $('<br /><br /><a href="<?php echo PATHLANG;?>register/" style="cursor: pointer; font-size: 12px"><?php echo t("Hier können Sie sich registrieren.");?></a>');
		main.append(link_text);
		
		$('#username').focus();
		
		$('#login_button').click(
			function()
			{
				user_login();
			}
		);
		
		$('#username').keydown(function(event){    
			if(event.keyCode==13){
				$('#password').focus();
			   //$('#login_button').trigger('click');
			}
		});
		
		$('#password').keydown(function(event){    
			if(event.keyCode==13){
			   $('#login_button').trigger('click');
			}
		});*/
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
	
	function user_login()
	{
		if($('#username').val()=="")
		{
			show_message_dialog("<?php echo t("Sie müssen einen Benutzernamen oder Ihre email-Adresse eingeben!");?>");
			return;
		}
		if($('#password').val().length<5)
		{
			show_message_dialog("<?php echo t("Das Passwort muss mindestens 5 Zeichen lang sein!");?>");
			return;
		}
		var post_object = 			new Object();
		post_object["API"] = 		"cms";
		post_object["APIRequest"] = "UserLogin";
		post_object["username"] = 	$('#username').val();
		post_object["password"] = 	md5($('#password').val());
		wait_dialog_show();
		$.post("<?php echo PATH; ?>soa2/", post_object, function($data) 
		{ 
			try { $xml = $($.parseXML($data)); } catch ($err) { show_status2($err.message); wait_dialog_hide(); return; }
			if ( $xml.find("Ack").text()!="Success" ) { show_message_dialog('<?php echo t("Die Kombination aus Benutzername und Passwort ist nicht bekannt");?>.<br /><br /><span style="font-weight:bold; color:#ff0000;"> <?php echo t("Bitte beachten Sie die Groß- und Kleinschreibung");?>!</span>'); wait_dialog_hide(); return; }
			if ( $xml.find("autologin").text() == '0' ) alert('<?php echo t("Sie werden auf Ihre passende Shop-Seite weitergeleitet. Dort müssen Sie sich noch einmal anmelden");?>');
			
			if($xml.find("login").text() == "0")
			{
				$('#password').val('');
				$('#password').focus();
				show_message_dialog($xml.find("message").text());
				wait_dialog_hide();
				return;
			}
			
			if($xml.find("site_link").text() != '')
			{
				for_link = $xml.find("site_link").text() + '<?php echo $_SESSION["get_url"];?>';
				for_shop_id = $xml.find("for_shop_id").text();
				for_user_id = $xml.find("for_user_id").text();
			}
			$.post("<?php echo PATH;?>soa2/", {API: "shop", APIRequest: "CartItemsGet"}, 
				function($data)
				{ 
					$xml = $($.parseXML($data));
					$ack = $xml.find("Ack").text();
					if($ack == "Success")
					{
						if($xml.find("shop_cart_items").text()!=0)
						{
							var item_cnt = 0;
							var cart_items = new Array();
							$xml.find("item").each(
								function()
								{
									cart_items[item_cnt] = 				new Array();
									cart_items[item_cnt]["amount"] = 	$(this).find("amount").text();
									cart_items[item_cnt]["title"] = 	$(this).find("title").text();
									item_cnt++;
								}
							);
							cart_merge_dialog(cart_items);
						}
						else
						{
							//alert(for_link);
							if(for_link != '')
								location.href = for_link;
							else
								//location.href = "<?php echo PATHLANG.$_SESSION["get_url"];?>";
								location.href = "<?php echo $_SESSION["get_url"];?>";
						}
					}
					wait_dialog_hide();
				}
			);
		});
	}

</script>

<?php
	//if(isset($_SESSION["for_url"])) unset($_SESSION["for_url"]);
	if(isset($_SESSION["get_url"])) unset($_SESSION["get_url"]);
	
	echo '<div id="left_mid_right_column" style="height: 450px; text-align: center"></div>';
	echo '<div id="message"></div>';
	include("templates/".TEMPLATE."/footer.php");
?>
<script type="text/javascript">shop_user_login_main();</script>
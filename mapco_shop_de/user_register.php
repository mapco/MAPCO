<?php
	include("config.php");
	$title='Registrieren';
	include("templates/".TEMPLATE."/header.php");
	//include("functions/shop_show_item.php");
	include("functions/cms_t.php");
	//include("modules/cms_leftcolumn_shop.php");
?>

<script type="text/javascript">

	function shop_user_register_main()
	{
		var main = $('#left_mid_right_column');
		var text = $('<p style="font-weight: bold; font-size: 18px"><?php echo t("Bitte registrieren Sie sich");?>:</p>');
		main.append(text);
/*
		text = $('<p style="display: inline"><?php echo t("E-Mail/Benutzername");?>:</p><br />');
		main.append(text);
		var input = $('<input type="text" id="username"><br /><br />');
		main.append(input);
		text = $('<p style="display: inline"><?php echo t("Passwort");?>:</p><br />');
		main.append(text);
		input = $('<input type="password" id="password"><br />');
		main.append(input);
		input = $('<input type="button" id="login_button" value="<?php echo t("Anmelden");?>" style="cursor: pointer; margin-left: 342px">');
		main.append(input);
		//var link_text = $('<br /><br /><br /><br /><a href="" style="cursor: pointer; font-size: 12px"><?php echo t("Haben Sie Ihr Passwort vergessen? Wir schicken Ihnen gerne ein neues.");?></a>');
		//main.append(link_text);
		var link_text = $('<br /><br /><br /><br /><a href="<?php echo PATH;?>login/" style="cursor: pointer; font-size: 12px"><?php echo t("Zurück zur Anmeldung.");?></a>');
		main.append(link_text);
*/
	}
</script>

<?php
	//echo md5(md5("martin").$salt);
	//unset($_SESSION["salt"]);
	echo '<div id="left_mid_right_column" style="min-height: 450px; text-align: center"></div>';
	include("templates/".TEMPLATE."/footer.php");
?>
<script type="text/javascript">shop_user_register_main();</script>
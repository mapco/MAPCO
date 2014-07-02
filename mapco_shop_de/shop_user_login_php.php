<?php

	include("config.php");
	$title='Anmeldung';
	
	if(!isset($_SESSION["get_url"])) $_SESSION["get_url"]='';
	if(isset($_SESSION["id_user"]))
	{
		if(isset($_SESSION["get_url"])) unset($_SESSION["get_url"]);
		header("Location: ".PATHLANG);
		exit;
	}
	
	//echo $_SESSION["get_url"];
	$fail_mess=0;
	$unknown_mess=0;
	if(isset($_POST["username"]) and $_POST["username"]!='' and isset($_POST["password"]) and strlen($_POST["password"])>4)
	{
		$responseXml = post(PATH."soa2/", array("API" => "cms", "APIRequest" => "UserLogin", "username" => $_POST["username"], "password" => md5($_POST["password"])));	
		$use_errors = libxml_use_internal_errors(true);
		try
		{
			$response = new SimpleXMLElement($responseXml);
		}
		catch(Exception $e)
		{
			//echo $e;
			//exit;
		}
		libxml_clear_errors();
		libxml_use_internal_errors($use_errors);
		//print_r($response);
		if($response->login[0]==1 and $response->site_link[0]=='')
		{
			header("Location: ".PATHLANG.$_SESSION["get_url"]);
			if(isset($_SESSION["get_url"])) unset($_SESSION["get_url"]);
//			echo '<script>location.href = "'.$_SESSION["get_url"].'";';
			exit;
		}
		else if($response->login[0]==1 and $response->site_link[0]!='')
		{
//			$message=$response->site_link[0];
//			echo '<script>location.href = "'.$response->site_link[0].$_SESSION["get_url"].'";</script';
			header("Location: ".$response->site_link[0].$_SESSION["get_url"]);
			if(isset($_SESSION["get_url"])) unset($_SESSION["get_url"]);
			exit;
		}
		else if(isset($response->login[0]) and $response->login[0]==0)
			$unknown_mess=1;
		else if($response->Ack[0]!='Success')
			$fail_mess=1;
	}
	
	include("templates/".TEMPLATE."/header.php");
	include("functions/cms_t.php");
	include("functions/cms_createPassword.php");
/*
	if(!isset($_SESSION["get_url"])) $_SESSION["get_url"]='';
	if(isset($_SESSION["id_user"]))
	{
		if(isset($_SESSION["get_url"])) unset($_SESSION["get_url"]);
		echo '<script type="text/javascript">location.href = "'.PATHLANG.'";</script>';
	}
*/
?>
	
	<script type="text/javascript">
	
	function show_message_dialog(message)
	{
		//alert(message);
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
	
//	if(isset($_SESSION["for_url"])) unset($_SESSION["for_url"]);
//	if(isset($_SESSION["get_url"])) unset($_SESSION["get_url"]);

	if( isset($_POST["login"]) )
	{
		//print_r($_POST);
	}
	
	
	//check input
	$message='';
	if(isset($_POST["username"]) and $_POST["username"]=='')
		$message=t("Sie müssen einen Benutzernamen eingeben.");
	else if(isset($_POST["password"]) and strlen($_POST["password"])<5)
		$message=t("Das Passwort muss mindestens fünf Zeichen lang sein.");
	
	if($fail_mess==1)
		$message=t("Die Kombination aus Benutzername und Passwort ist nicht bekannt").'.<br /><br /><span style=\"font-weight:bold; color:#ff0000;\">'.t("Bitte beachten Sie die Groß- und Kleinschreibung").'!</span>';
	if($unknown_mess==1)
		$message=t("Ungültige Benutzername/Passwort-Kombination.");
	
	echo '<div id="message"></div>';
	//echo $message;
	if(strlen($message)>0)	
		echo '<script type="text/javascript">show_message_dialog("'.$message.'");</script>';	
	
	echo '<div id="left_mid_right_column" style="height: 450px; text-align: center">';
	
	echo '<form method="post" action="'.PATHLANG.$_GET["url"].'">';
	echo '<br /><br /><p style="font-weight: bold; font-size: 18px">'.t("Bitte melden Sie sich an").':</p>';
	if(isset($_POST["username"])) $username=$_POST["username"];
	else $username='';
	echo '<label for="username"><p style="display: inline; font-weight: bold;">'.t("Benutzername").':</p><p style="display: inline">'.t(" (oder E-mail-Adresse)").':</p></label><br />';
	echo '<input type="text" name="username" id="username" autocomplete="on" value="'.$username.'"><br /><br />';
	echo '<label for="password"><p style="display: inline; font-weight: bold;">'.t("Passwort").':</p></label><br />';
	echo '<input type="password" name="password" id="password" autocomplete="on"><br /><br />';
	
	if( isset($dbshop) )
	{
		$results=q("SELECT * FROM shop_carts WHERE session_id='".session_id()."' AND shop_id=".$_SESSION["id_shop"].";", $dbshop, __FILE__, __LINE__);
		if( mysql_num_rows($results)>0 )
		{
			echo '<input checked="checked" type="checkbox" name="cart_merge" />'.t("Warenkorb übernehmen").'<br /><br />';
		}
	}
	
	echo '<input type="submit" name="login" value="Anmelden" />';
//	echo '<input type="submit" name="login" value="'.t("Anmelden").'" />';
	echo '</form>';
	
	
	echo '<br /><br /><br /><br /><a href="'.PATHLANG.'passwort-anfrage/" style="cursor: pointer; font-size: 12px">'.t("Haben Sie Ihr Passwort vergessen? Wir schicken Ihnen gerne ein neues.").'</a>';
	echo '<br /><br /><a href="'.PATHLANG.'online-shop/registrieren/" style="cursor: pointer; font-size: 12px">'.t("Hier können Sie sich registrieren.").'</a>';

	echo '</div>';

	include("templates/".TEMPLATE."/footer.php");

/*	
	echo '<div id="left_mid_right_column" style="height: 450px; text-align: center"></div>';
	echo '<div id="message"></div>';
	include("templates/".TEMPLATE."/footer.php");
*/	
?>


<?php

	include("config.php");
	include("functions/cms_tl.php");
	$title='Anmeldung';
	
	if (isset($_SESSION["id_user"])) {
		if (isset($_SESSION["get_url"])) unset($_SESSION["get_url"]);
		header("Location: ".PATHLANG);
		exit;
	}
	
	$fail_mess = 0;
	$merge = 0;
	$unknown_mess = 0;
	
	//Warenkorb übernehmen?
	if (isset($_POST["cart_merge"]) and $_POST["cart_merge"] == 'on')
		$merge = 1;
	
	//if(isset($_POST["username"]) and $_POST["username"]!='' and isset($_POST["password"]) and strlen($_POST["password"])>4 and !isset($_POST["id_user"]))
	if (isset($_POST["username"]) and $_POST["username"] != '' and isset($_POST["password"]) and !isset($_POST["id_user"])) {
/*		
		echo $responseXml = post(PATH."soa2/", array("API" => "cms", "APIRequest" => "UserLogin", "username" => $_POST["username"], "password" => md5($_POST["password"]), "merge" => $merge));	
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
*/

		//LOGIN
		$post_data = array();
		$post_data["API"] = "cms";
		$post_data["APIRequest"] = "UserLogin";
		$post_data["username"] = $_POST["username"];
		$post_data["password"] = md5($_POST["password"]);
		$post_data["merge"] = $merge;
		$post_data["id_site"]= $_SESSION['id_site'];
		
		$response = soa2($post_data, __FILE__, __LINE__);
		
		
		//SIND UNGELESENE INTERNE NEWS VORHANDEN	
		$art_show=0;
		
		if ($response->login[0] == 1) {
			$post_data = array();
			$post_data["API"] = "cms";
			$post_data["APIRequest"] = "ArticlesUnreadGet";
			$post_data["id_user"] = (int)$response->user_id[0];
			$response2=soa2($post_data, __FILE__, __LINE__);
			if ((int)$response2->num_art_unread[0] > 0) $art_show = 1;
		}
		
		if ($response->login[0] == 1 and $response->site_link[0] == '') {
			if ($art_show==1) {
				session_start();
				$_SESSION["in_path"] = PATHLANG.$_POST["url"];
				header("Location: " . PATHLANG . tl(736, "alias"));
				exit;
			}
			$temp_path = $_POST["url"];
			header("Location: " . PATHLANG . $temp_path);
			exit;
		} else if($response->login[0] == 1 and $response->site_link[0]!='') {
			if ($art_show == 1) {
				session_start();
				$_SESSION["in_path"] = $response->site_link[0] . $_POST["url"];
				header("Location: " . PATHLANG . tl(736, "alias")); //interne news
				exit;
			}
			$temp_path=$_POST["url"];
			header("Location: " . $response->site_link[0] . $temp_path);
			exit;
		} else if(isset($response->login[0]) and $response->login[0] == 0)
			$unknown_mess=1;
			else if($response->Ack[0] != 'Success')
			$fail_mess = 1;
	}

	if (!isset($_SESSION["get_url"])) $_SESSION["get_url"] = '';
	if (strlen($_SESSION["get_url"])>0) $_SESSION["get_url"] = substr($_SESSION["get_url"], 1);
	if (isset($_POST["url"])) $get_url = $_POST["url"];
	else $get_url = $_SESSION["get_url"];
	$_SESSION["for_url"] = $_SESSION["get_url"]; //Weiterleitung für Registrierungsseiten	
	
	include("templates/" . TEMPLATE . "/header.php");
	include("functions/cms_t.php");
	include("functions/cms_createPassword.php");
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
	
	function show_message_dialog2(message)
	{
		//alert(message);
		$("#message").html(message);
		$("#message").dialog
		({	buttons:
			[
				{ text: "<?php echo t("Passwort anfordern"); ?>", click: function() {password_get(); $(this).dialog("close");} },
				{ text: "<?php echo t("Abbrechen"); ?>", click: function() {$(this).dialog("close");} }
			],
			closeText:"<?php echo t("Fenster schließen"); ?>",
			hide: { effect: 'drop', direction: "up" },
			modal:true,
			resizable:false,
			show: { effect: 'drop', direction: "up" },
			title:"<?php echo t("Achtung"); ?>!",
			width:350
		});
	}
	
	function password_get()
	{
		location.href="<?php echo PATHLANG;?>passwort-anfrage/";
	}
	
</script>
	
<?php
	
	if( isset($_POST["login"]) )
	{
		//print_r($_POST);
	}
	
	
	//check input
	$message='';
	$message2='';
	if (isset($_POST["username"]) and $_POST["username"] == '')
		$message = t("Sie müssen einen Benutzernamen eingeben.");
	else if(isset($_POST["password"]) and strlen($_POST["password"]) < 1)
		$message = t("Sie müssen ein Passwort angeben.");
	
	if ($fail_mess == 1)
		$message = t("Die Kombination aus Benutzername und Passwort ist nicht bekannt").'.<br /><br /><span style=\"font-weight:bold; color:#ff0000;\">'.t("Bitte beachten Sie die Groß- und Kleinschreibung").'!</span>';
	if ($unknown_mess == 1)
		$message2 = t("Ungültige Benutzername/Passwort-Kombination.").'<br />'.t("Haben Sie vielleicht Ihr Passwort vergessen?").'<br />'.t("Wir schicken Ihnen gerne ein neues.");
		//$message=t("Ungültige Benutzername/Passwort-Kombination.").'<br />'.t("Haben Sie vielleicht Ihr Passwort vergessen?").'<br />'.t("Wir schicken Ihnen gerne ein neues. Benutzen Sie bitte den Link auf der Login-Seite.");
	
	echo '<div id="message"></div>';
	if (strlen($message) > 0)	
		echo '<script type="text/javascript">show_message_dialog("'.$message.'");</script>';
	if (strlen($message2) > 0)	
		echo '<script type="text/javascript">show_message_dialog2("'.$message2.'");</script>';	
	
	echo '<div id="left_mid_right_column" style="height: 450px; text-align: center">';
	
	echo '	<form method="post" action="' . PATHLANG . $_GET["url"] . '">';
	/*
	if(($_SESSION["id_shop"]>8 and $_SESSION["id_shop"]<17) or $_SESSION["id_shop"]==18)
	{
		echo '		<br /><br /><p style="font-weight: bold; font-size: 18px">' . t("Als Gewerbekunde melden Sie sich bitte hier an") . ':</p>';
	}
	else
	*/
	{
		echo '		<br /><br /><p style="font-weight: bold; font-size: 18px">' . t("Bitte melden Sie sich an") . ':</p>';		
	}
	
	if (isset($_POST["username"])) $username = $_POST["username"];
	else $username = '';
	echo '		<label for="username"><p style="display: inline; font-weight: bold;">'.t("Benutzername").':</p><p style="display: inline">'.t(" (oder E-mail-Adresse)").':</p></label><br />';
	echo '		<input type="text" name="username" id="username" autocomplete="on" value="'.$username.'"><br /><br />';
	echo '		<label for="password"><p style="display: inline; font-weight: bold;">'.t("Passwort").':</p></label><br />';
	echo '		<input type="password" name="password" id="password" autocomplete="on"><br /><br />';
	
	if (isset($dbshop))
	{
		$results = q("
			SELECT * 
			FROM shop_carts 
			WHERE session_id = '" . session_id() . "' AND shop_id = " . $_SESSION["id_shop"] . " AND user_id=0;", $dbshop, __FILE__, __LINE__);
		if ( mysqli_num_rows( $results ) > 0 ) 
		{
			echo '<input checked="checked" type="checkbox" name="cart_merge" />'.t("Warenkorb übernehmen").'<br /><br />';
		}
	}
	
	echo '		<input type="submit" name="login" value="'.t("Anmelden").'" />';
	echo '		<input type="hidden" name="url" value="' . $get_url . '" />';
	echo '	</form>';
	/*
	if(($_SESSION["id_shop"]>8 and $_SESSION["id_shop"]<17) or $_SESSION["id_shop"]==18)
	{
		echo '	<br /><br /><br /><br /><a href="http://www.mapco.de/online-shop/" style="border:3px solid red; padding:10px; cursor: pointer; font-size: 14px">'.t("Als Privatkunde können Sie unter www.mapco.de bestellen!").'</a>';
	}
	*/
	//echo '	<br /><br /><br /><br /><a href="'.PATHLANG.'passwort-anfrage/" style="cursor: pointer; font-size: 12px">'.t("Haben Sie Ihr Passwort vergessen? Wir schicken Ihnen gerne ein neues.").'</a>';
	echo '	<br /><br /><br /><br /><a href="' . PATHLANG . tl(658, "alias") . '" style="cursor: pointer; font-size: 12px">'.t("Haben Sie Ihr Passwort vergessen? Wir schicken Ihnen gerne ein neues.").'</a>';
//	echo '	<br /><br /><a href="'.PATHLANG.'registrierung/" style="color: red;cursor: pointer; font-size: 15px; font-weight: bold;">'.t("Hier können Sie sich registrieren.").'</a>';
	
	if( $_SESSION['id_site'] != 17 )
	{	
		echo '	<br /><br /><a href="' . PATHLANG . tl(662, "alias") .'" style="color: red;cursor: pointer; font-size: 15px; font-weight: bold;">'.t("Hier können Sie sich registrieren.").'</a>';
	}
	
	echo '</div>';

	include("templates/".TEMPLATE."/footer.php");
?>


<?php 
	include("../functions/cms_t.php");
	check_man_params(array( "username"	=>	"text",
							"password"	=>	"text",
							"merge"		=>	"numeric"));

	//keep post submit
	$post = $_POST;
	
	//eingegebenes Passwort verschlüsseln
	$pw = "";
	$user_id = 0;
	$user_token = '';
	$autologin = 0;
	$forward = 0;
	$login = 0;
	$results = q("
		SELECT * 
		FROM cms_users 
		WHERE username = '" . mysqli_real_escape_string($dbweb, trim($_POST["username"])) . "' AND active=1;", $dbweb, __FILE__, __LINE__);
	if (mysqli_num_rows($results) == 0) {
		$results = q("
			SELECT * 
			FROM cms_users 
			WHERE usermail='".mysqli_real_escape_string($dbweb, trim($_POST["username"]))."' AND active=1;", $dbweb, __FILE__, __LINE__);
	}
	
	if (mysqli_num_rows($results) > 0) {
		while($row = mysqli_fetch_array($results))
		{
			$results2 = q("
				SELECT * 
				FROM cms_users_sites 
				WHERE user_id = " . mysqli_real_escape_string($dbweb, $row["id_user"]) . ";", $dbweb, __FILE__, __LINE__);
			if (mysqli_num_rows($results2) > 0) {
				while($row2=mysqli_fetch_array($results2))
				{
					if($row2["site_id"] == $_SESSION["id_site"]) {
						$user_id = $row2["user_id"];	
					}
				}
			}
		}
	}

	//Weiterleitung
	if ($user_id == 0) {
		if ($_SESSION["id_site"] == 1) {
			$results=q("
				SELECT * 
				FROM cms_users 
				WHERE username = '" . mysqli_real_escape_string($dbweb, $_POST["username"]) . "' AND active = 1;", $dbweb, __FILE__, __LINE__);
			if (mysqli_num_rows($results)==0) {
				$results=q("
					SELECT * 
					FROM cms_users 
					WHERE usermail = '" . mysqli_real_escape_string($dbweb, $_POST["username"])."' AND active=1;", $dbweb, __FILE__, __LINE__);
			}
			if (mysqli_num_rows($results) > 0) {
				while ($row = mysqli_fetch_array($results))
				{
					$results2=q("
						SELECT * 
						FROM cms_users_sites 
						WHERE user_id = " . mysqli_real_escape_string($dbweb, $row["id_user"])." AND site_id!= 1 AND site_id != 2 and site_id != 7;", $dbweb, __FILE__, __LINE__);
					if (mysqli_num_rows($results2) == 1) {
						$row2 = mysqli_fetch_array($results2);
						$user_id = $row2["user_id"];
						$user_token = $row["user_token"];	
						$site_id = $row2["site_id"];
						$forward = 1;
						if ($user_token != '' and strlen($user_token) == 50) $autologin = 1;		
					}
				}
			}
		}
	}
	
	$results3 = q("
		SELECT * 
		FROM cms_users 
		WHERE id_user = " . mysqli_real_escape_string($dbweb, $user_id) . ";", $dbweb, __FILE__, __LINE__);	
	if (mysqli_num_rows($results3) == 0) {
		show_error(9784, 1, __FILE__, __LINE__, print_r($_SESSION, true) . "\n" . print_r($_POST, true));
		exit;
	}
	
	$row3 = mysqli_fetch_array($results3);
	$pw = md5($_POST["password"].$row3["user_salt"]);
	$pw = md5($pw.PEPPER);

	//Weiterleitung
	$xml='';
	if ($row3["password"] == $pw and $forward == 1) {
		
		$results4=q("
			SELECT * 
			FROM shop_shops 
			WHERE site_id = " . $site_id . ";", $dbshop, __FILE__, __LINE__);
		$row4 = mysqli_fetch_array($results4);
		$domain = $row4["domain"];
		$xml.= '<for_user_id>' . $user_id . '</for_user_id>' . "\n";
		$xml.= '<for_shop_id>' . $row4["id_shop"] . '</for_shop_id>' . "\n";
		if ($autologin == 1) {
			$xml.= '<site_link><![CDATA[http://' . $domain . '/autologin/' . $user_token . '/]]></site_link>' . "\n";
		} else {
			$xml.= '<site_link><![CDATA[http://' . $domain . ']]></site_link>' . "\n";
			$xml.= '<autologin>0</autologin>';
		}
		$xml.= '<login>1</login>';
		$xml.= '<user_id>'.$user_id.'</user_id>';
		
		$login = 1;
		if ($_POST["merge"] == 1) {
			$results = q("
				UPDATE shop_carts 
				SET user_id = " . $user_id . ", 
				shop_id = " . $row4["id_shop"] . " WHERE session_id = '" . session_id() . "' AND user_id = 0;", $dbshop, __FILE__, __LINE__);
		}
	} else if ($row3["password"] == $pw and $forward == 0) {
		$_SESSION["id_user"] = $row3["id_user"];
		$_SESSION["userrole_id"] = $row3["userrole_id"];
		if ($row3["origin"]!="") $_SESSION["origin"] = $row3["origin"];
		else {
			$responseXml = post(PATH."soa2/", array("API" => "cms", "APIRequest" => "UserOriginSet", "user_id" => $row3["id_user"]));
			$use_errors = libxml_use_internal_errors(true);
			try
			{
				$response = new SimpleXMLElement($responseXml);
			}
			catch(Exception $e)
			{
				show_error(9788, 1, __FILE__, __LINE__, $e."\n".print_r($_SESSION, true)."\n".print_r($_POST, true));
				exit;
			}
			libxml_clear_errors();
			libxml_use_internal_errors($use_errors);
			$origin2=$response->origin[0];
			
			$_SESSION["origin"]=$origin2;
			//$_SESSION["origin"]=set_origin($row3["id_user"]);
		}

		setcookie("id_user", $row3["id_user"], time()+24*3600, "/", ".".str_replace("www.", "", $_SERVER['HTTP_HOST']));
		
		//update lastvisit
		q("
			UPDATE cms_users 
			SET lastlogin = " . time() . " WHERE id_user = " . $row3["id_user"] . ";", $dbweb, __FILE__, __LINE__);
		
		//update session_id
		q("
			UPDATE cms_users 
			SET session_id = '" . session_id() . "' WHERE id_user = " . $row3["id_user"] . ";", $dbweb, __FILE__, __LINE__);
		
		//set language
		$results2 = q("
			SELECT * 
			FROM cms_languages 
			WHERE id_language = " . $row3["language_id"] . " LIMIT 1;", $dbweb, __FILE__, __LINE__);
		$row2 = mysqli_fetch_array($results2);
		$_GET["lang"] = $row2["code"];
		
		$xml.= '<login>1</login>';
		$xml.= '<user_id>' . $row3["id_user"] . '</user_id>';
		$login = 1;
		if ($_POST["merge"] == 1) {
			$results=q("
				UPDATE shop_carts 
				SET user_id = " . $_SESSION["id_user"] . " WHERE session_id = '" .session_id() . "' AND user_id = 0;", $dbshop, __FILE__, __LINE__);
		}
	} else {
		$xml.= '<login>0</login>';
		$xml.= '<message>' . t("Ungültige Benutzername/Passwort-Kombination.") . '</message>';
	}
	echo $xml;
	
	if ($_POST["merge"] == 0 and $login == 1) {
		$results = q("
			DELETE FROM shop_carts
			WHERE session_id = '" . session_id() . "' AND user_id = 0;", $dbshop, __FILE__, __LINE__);
	}
?>
<?php
	include("config.php");
	$title="Autologin";	
	
	//EXTRACT USERTOKEN
	$uriparts=explode("/",$_SERVER['REQUEST_URI']);
	//FIND TOKEN
	$index=array_search("autologin", $uriparts);
	if ($index)	$usertoken=$uriparts[$index+1];
	//CHECK TOKEN
	if (ctype_alnum($usertoken) && strlen($usertoken)==50)
	{
		
		//$usertoken=substr($_SERVER['REQUEST_URI'], strrpos($_SERVER['REQUEST_URI'],"/")+1);
		//GET USER
		$res_user=q("SELECT * FROM cms_users WHERE user_token = '".$usertoken."' LIMIT 1;", $dbweb, __FILE__, __LINE__);
		//$res_user=q("SELECT * FROM cms_users WHERE user_token = '".$usertoken."' AND site_id=".$_SESSION["id_site"]." LIMIT 1;", $dbweb, __FILE__, __LINE__);
		if (mysqli_num_rows($res_user)==1)
		{
			$row_user=mysqli_fetch_array($res_user);
			$query="SELECT * FROM cms_users AS a, cms_users_sites AS b WHERE a.id_user=".$row_user["id_user"]." AND a.id_user=b.user_id AND b.site_id=".$_SESSION["id_site"]." AND active=1 LIMIT 1;";
			$results=q($query, $dbweb, __FILE__, __LINE__);
			if(mysqli_num_rows($results)==1)
			{
				$_SESSION["id_user"]=$row_user["id_user"];
				$_SESSION["userrole_id"]=$row_user["userrole_id"];
				if ($row_user["origin"]!="") $_SESSION["origin"]=$row_user["origin"];
				else
				{
					$responseXml = post(PATH."soa2/", array("API" => "cms", "APIRequest" => "UserOriginSet", "user_id" => $row_user["id_user"]));
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
					
					//$_SESSION["origin"]=set_origin($row_user["id_user"]);
				}
				
				//update lastlogin
				q("UPDATE cms_users SET lastlogin=".time()." WHERE id_user=".$row_user["id_user"].";", $dbweb, __FILE__, __LINE__);
				
				//set language
				$results2=q("SELECT * FROM cms_languages WHERE id_language=".$row_user["language_id"]." LIMIT 1;", $dbweb, __FILE__, __LINE__);
				if(mysqli_num_rows($results2)!=0)
				{
					$row2=mysqli_fetch_array($results2);
					$_GET["lang"]=$row2["code"];
				}
				else $_GET["lang"]="de";
	
				if ($_SESSION["lang"]!=$_GET["lang"]) $_SESSION["lang"]=$_GET["lang"];

				if($_SESSION["lang"]=="de")
				{
					header("HTTP/1.1 303 See Other");
					header("location: ".PATH.$_GET["url"]);
					exit;
				}
				else
				{
					header("HTTP/1.1 303 See Other");
					header("location: ".PATH.$_SESSION["lang"].'/'.$_GET["url"]);
					exit;
				}
				//set cookie
			//	setcookie("id_user", $row_user["id_user"], time()+24*3600, "/", ".".str_replace("www.", "", $_SERVER['HTTP_HOST']));
			
			}
		}
	}
	if($_SESSION["lang"]=="de")
	{
		header("HTTP/1.1 303 See Other");
		header("location: ".PATH.$_GET["url"]);
		exit;
	}
	else
	{
		header("HTTP/1.1 303 See Other");
		header("location: ".PATH.$_SESSION["lang"].'/'.$_GET["url"]);
		exit;
	}
	
	include("templates/".TEMPLATE."/header.php");
?>

<script type="text/javascript">

	function user_autologin_token_main()
	{
		var text = $('<br /><br /><br /><p style="font-size: 20px; font-weight: bold"><?php echo t("Sie werden angemeldet"); ?>...</p>');
		$('#left_mid_right_column').append(text);
	}

</script>

<?php
	echo '<br /><div id="left_mid_right_column" style="height: 450px; text-align: center; width: 950px"></div>';

	include("templates/".TEMPLATE."/footer.php");
?>	
<script type="text/javascript">user_autologin_token_main();</script>
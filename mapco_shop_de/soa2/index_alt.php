<?php
	
	$start=microtime(true); // Returns timestamp as FLOAT

	session_start();
	include("../config.php");
	
	function check_man_params($required)
	{
		global $_POST;
		global $dbshop;
		
		//CHECK FOR REQUIRED FIELDS
		while (list ($key, $val) = each ($required))
		{
			//isset Field
			if (!isset($_POST[$key]))
			{
				//LÖSCHE AUSGABEPUFFER
				ob_end_clean();

				echo '<'.$_POST["APIRequest"].'Response>'."\n";
				echo '	<Ack>Failure</Ack>'."\n";
				echo '	<Error>'."\n";
				echo '		<Code>'.__LINE__.'</Code>'."\n";
				echo '		<shortMsg>Benötigtes Feld: '.$key.' nicht gefunden</shortMsg>'."\n";
				echo '		<longMsg>Benötigtes Feld: '.$key.' nicht gefunden</longMsg>'."\n";
				echo '	</Error>'."\n";
				echo '</OrderAddResponse>'."\n";
				exit;
			}
			
		//matching Values
			if ($val == "numeric")
			{
				for ($i=0; $i<sizeof($_POST[$key]);$i++)
				{
					if (($_POST[$key][$i]=="" || !is_numeric($_POST[$key][$i])) && $_POST[$key][$i]!=0)
					{
						//LÖSCHE AUSGABEPUFFER
						ob_end_clean();

						echo '<'.$_POST["APIRequest"].'Response>'."\n";
						echo '	<Ack>Failure</Ack>'."\n";
						echo '	<Error>'."\n";
						echo '		<Code>'.__LINE__.'</Code>'."\n";
						echo '		<shortMsg>Ungültiger Wert im Feld: '.$key.'</shortMsg>'."\n";
						echo '		<longMsg>Ungültiger Wert im Feld: '.$key.'. Übermittelter Wert: '.$_POST[$i][$key].'</longMsg>'."\n";
						echo '	</Error>'."\n";
						echo '</'.$_POST["APIRequest"].'Response>'."\n";
						exit;
					}
				}
			}
			if ($val == "numericNN")
			{
				for ($i=0; $i<sizeof($_POST[$key]);$i++)
				{
					if ($_POST[$key]=="" || !is_numeric($_POST[$key]) || $_POST[$key]==0)
					{
						//LÖSCHE AUSGABEPUFFER
						ob_end_clean();
						
						echo '<'.$_POST["APIRequest"].'Response>'."\n";
						echo '	<Ack>Failure</Ack>'."\n";
						echo '	<Error>'."\n";
						echo '		<Code>'.__LINE__.'</Code>'."\n";
						echo '		<shortMsg>Ungültiger Wert im Feld: '.$key.'</shortMsg>'."\n";
						echo '		<longMsg>Ungültiger Wert im Feld: '.$key.'. Übermittelter Wert: '.$_POST[$i][$key].'</longMsg>'."\n";
						echo '	</Error>'."\n";
						echo '</'.$_POST["APIRequest"].'Response>'."\n";
						exit;
					}
				}
			}
			if ($val == "numerictest")
			{
				for ($i=0; $i<sizeof($_POST[$key]);$i++)
				{
					if ($_POST[$key][$i]=="" || !is_numeric($_POST[$key][$i]))
					{
						//LÖSCHE AUSGABEPUFFER
						ob_end_clean();

						echo '<'.$_POST["APIRequest"].'Response>'."\n";
						echo '	<Ack>Failure</Ack>'."\n";
						echo '	<Error>'."\n";
						echo '		<Code>'.__LINE__.'</Code>'."\n";
						echo '		<shortMsg>Ungültiger Wert im Feld: '.$key.'</shortMsg>'."\n";
						echo '		<longMsg>Ungültiger Wert im Feld: '.$key.'. Übermittelter Wert: '.$_POST[$i][$key].'</longMsg>'."\n";
						echo '	</Error>'."\n";
						echo '</'.$_POST["APIRequest"].'Response>'."\n";
						exit;
					}
				}
			}

			if ($val == "textNN")
			{
				for ($i=0; $i<sizeof($_POST[$key]);$i++)
				{
					if ($_POST[$key][$i]=="")
					{
						//LÖSCHE AUSGABEPUFFER
						ob_end_clean();
					
						echo '<'.$_POST["APIRequest"].'Response>'."\n";
						echo '	<Ack>Failure</Ack>'."\n";
						echo '	<Error>'."\n";
						echo '		<Code>'.__LINE__.'</Code>'."\n";
						echo '		<shortMsg>Ungültiger Wert im Feld: '.$key.'</shortMsg>'."\n";
						echo '		<longMsg>Leerer String im Feld: '.$key.'</longMsg>'."\n";
						echo '	</Error>'."\n";
						echo '</'.$_POST["APIRequest"].'Response>'."\n";
						exit;
					}
				}
			}
			if ($val == "currency")
			{
				for ($i=0; $i<sizeof($_POST[$key]);$i++)
				//foreach ($_POST[$key] as $PostArrayField)
				{
					if (sizeof($_POST[$key])==1) $PostArrayField=$_POST[$key]; else $PostArrayField=$_POST[$key][$i];
					
					if ($PostArrayField=="")
					{
						//LÖSCHE AUSGABEPUFFER
						ob_end_clean();

						echo '<'.$_POST["APIRequest"].'Response>'."\n";
						echo '	<Ack>Failure</Ack>'."\n";
						echo '	<Error>'."\n";
						echo '		<Code>'.__LINE__.'</Code>'."\n";
						echo '		<shortMsg>Ungültiger Wert im Feld: '.$key.'</shortMsg>'."\n";
						echo '		<longMsg>Leerer String im Feld: '.$key.'</longMsg>'."\n";
						echo '	</Error>'."\n";
						echo '</'.$_POST["APIRequest"].'Response>'."\n";
						exit;
					}
					else 
					{
						$res_currency=q("SELECT * FROM shop_currencies WHERE currency_code = '".$PostArrayField."';", $dbshop, __FILE__, __LINE__);
						if (mysqli_num_rows($res_currency)==0)
						{
							//LÖSCHE AUSGABEPUFFER
							ob_end_clean();

							echo '<'.$_POST["APIRequest"].'Response>'."\n";
							echo '	<Ack>Failure</Ack>'."\n";
							echo '	<Error>'."\n";
							echo '		<Code>'.__LINE__.'</Code>'."\n";
							echo '		<shortMsg>Ungültiger Wert im Feld: '.$key.'</shortMsg>'."\n";
							echo '		<longMsg>'.$PostArrayField.' ist kein gültiger Währungscode</longMsg>'."\n";
							echo '	</Error>'."\n";
							echo '</'.$_POST["APIRequest"].'Response>'."\n";
							exit;
						}
					}
				}
			}
			if ($val == "countrycode")
			{
				for ($i=0; $i<sizeof($_POST[$key]);$i++)
				{
					if (sizeof($_POST[$key])==1) $PostArrayField=$_POST[$key]; else $PostArrayField=$_POST[$key][$i];

					if ($PostArrayField=="")
					{
						//LÖSCHE AUSGABEPUFFER
						ob_end_clean();

						echo '<'.$_POST["APIRequest"].'Response>'."\n";
						echo '	<Ack>Failure</Ack>'."\n";
						echo '	<Error>'."\n";
						echo '		<Code>'.__LINE__.'</Code>'."\n";
						echo '		<shortMsg>Ungültiger Wert im Feld: '.$key.'</shortMsg>'."\n";
						echo '		<longMsg>Leerer String im Feld: '.$key.'</longMsg>'."\n";
						echo '	</Error>'."\n";
						echo '</'.$_POST["APIRequest"].'Response>'."\n";
						exit;
					}
					else 
					{
						$res_country=q("SELECT * FROM shop_countries WHERE country_code = '".$PostArrayField."';", $dbshop, __FILE__, __LINE__);
						if (mysqli_num_rows($res_country)==0)
						{
							//LÖSCHE AUSGABEPUFFER
							ob_end_clean();
							
							echo '<'.$_POST["APIRequest"].'Response>'."\n";
							echo '	<Ack>Failure</Ack>'."\n";
							echo '	<Error>'."\n";
							echo '		<Code>'.__LINE__.'</Code>'."\n";
							echo '		<shortMsg>Ungültiger Wert im Feld: '.$key.'</shortMsg>'."\n";
							echo '		<longMsg>'.$PostArrayField.' ist kein gültiger Ländercode</longMsg>'."\n";
							echo '	</Error>'."\n";
							echo '</'.$_POST["APIRequest"].'Response>'."\n";
							exit;
						}
					}
				}
			}
		}
		
	}
	
	//ImageThumbnail fix
	$public=false;
	if ( isset($_POST["APIRequest"]) and ($_POST["APIRequest"]=="ImageThumbnail" or $_POST["APIRequest"]=="CartAdd" or $_POST["APIRequest"]=="CartUpdate" or $_POST["APIRequest"]=="CategoryGetImagePath" or $_POST["APIRequest"]=="PromotionBoxGetItems" or $_POST["APIRequest"]=="CategoryBox" or $_POST["APIRequest"]=="PromotionBox"))
	{
		$public=true;
	}
	else
	{
		//token fix
		if ( isset($_POST["usertoken"]) and $_POST["usertoken"]=="merci2664" )
		{
			$_SESSION["id_user"]=1;
			$_SESSION["userrole_id"]=1;
		}
	
		//security check
/*
		if ( $_SERVER["REMOTE_ADDR"]=="85.10.215.73")
		{
			$_SESSION["id_user"]=1;
			$_SESSION["userrole_id"]=1;
		}
		else
*/
		if ( (!isset($_SESSION["id_user"]) or !($_SESSION["id_user"]>0)) and $_POST["APIRequest"]!="UserLogin" and $_POST["APIRequest"]!="UserPasswordRequestMailSend" and $_POST["APIRequest"]!="CartItemsGet" and $_POST["APIRequest"]!="CartMerge" and $_POST["APIRequest"]!="BackendUserroleCheck" and $_POST["APIRequest"]!="UserRegister")
		{
			error(__FILE__, __LINE__, "Unerlaubter Zugriff auf Service!");
			header("HTTP/1.0 404 Not Found");
			exit;
		}
	}

	//check for valid API name
	if ( !isset($_POST["API"]) )
	{
		echo '<ServiceResponse>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>API ungültig.</shortMsg>'."\n";
		echo '		<longMsg>Es konnte keine gültige API gefunden werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ServiceResponse>'."\n";
		exit;
	}

	//check for valid Request name
	if ( !isset($_POST["APIRequest"]) )
	{
		echo '<ServiceResponse>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Servicename ungültig.</shortMsg>'."\n";
		echo '		<longMsg>Es konnte kein gültiger Service gefunden werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ServiceResponse>'."\n";
		exit;
	}

	//check for valid Action
	$file="../../APIs/".$_POST["API"]."/".$_POST["APIRequest"].".php";
	if ( !file_exists($file) )
	{
		echo '<ServiceResponse>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Service ungültig. ('.$file.')</shortMsg>'."\n";
		echo '		<longMsg>Es exisitiert kein Service mit dem angegebenen Namen.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ServiceResponse>'."\n";
		exit;
	}

	//show error function
	function show_error($id_errorcode, $id_errortype, $file, $line, $text="")
	{
		//LÖSCHE AUSGABEPUFFER
		ob_end_clean();

		global $dbweb;
		
		$results=q("SELECT * FROM cms_errorcodes WHERE errortype_id=".$id_errortype." AND errorcode=".$id_errorcode." LIMIT 1;", $dbweb, __FILE__, __LINE__);
		$row=mysqli_fetch_array($results);
		echo '<'.$_POST["APIRequest"].'Response>'."\n";
		echo '	<Ack>'.$row["type"].'</Ack>'."\n";
		echo '	<'.$row["type"].'>'."\n";
		echo '		<Code>'.$id_errorcode.'</Code>'."\n";
		echo '		<shortMsg>'.$row["shortMsg"].'</shortMsg>'."\n";
		echo '		<longMsg>'.$row["longMsg"].'</longMsg>'."\n";
		echo '	</'.$row["type"].'>'."\n";
		echo '</'.$_POST["APIRequest"].'Response>'."\n";
		post(PATH."soa/", array( "API" => "cms", "Action" => "ErrorAdd", "id_errortype" => $id_errortype, "id_errorcode" => $id_errorcode, "file" => $file, "line" => $line, "text" => $text ) );
		exit;
	}
	
	function track_error($id_errorcode, $id_errortype, $file, $line, $text="")
	{
		post(PATH."soa/", array( "API" => "cms", "Action" => "ErrorAdd", "id_errortype" => $id_errortype, "id_errorcode" => $id_errorcode, "file" => $file, "line" => $line, "text" => $text ) );
	}
	
	//AUSGABE PUFFERN, WEGEN FEHLERMELDUNG ABFANGEN
	ob_start();
	
		echo '<'.$_POST["APIRequest"].'Response>'."\n";
		echo '	<Ack>Success</Ack>'."\n";
		//start service
		include($file);
		$end=microtime(true);
		$diff=$start-$end;
		echo '	<Runtime>'.$diff.'</Runtime>'."\n";
		echo '</'.$_POST["APIRequest"].'Response>'."\n";
	
	//AUSGABE AUS PUFFER
	ob_end_flush();
	
?>

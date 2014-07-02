<?php
	//include("../config.php");
	include("../functions/cms_createPassword.php");//verschlüsselung

	echo "\n";
	$anz=0;
	q("DELETE FROM kunde WHERE IDIMS_ID in (16815, 17285, 18465, 18466, 18211, 17292, 9753, 18467, 18469, 18209, 18471, 14110, 18463, 18472, 18213, 18473);", $dbshop, __FILE__, __LINE__); 
	q("DELETE FROM fa_user_login WHERE kunid in (16815, 17285, 18465, 18466, 18211, 17292, 9753, 18467, 18469, 18209, 18471, 14110, 18463, 18472, 18213, 18473);", $dbshop, __FILE__, __LINE__); 
	$results=q("SELECT * FROM fa_user_login WHERE kunid in (16815, 17285, 18465, 18466, 18211, 17292, 9753, 18467, 18469, 18209, 18471, 14110, 18463, 18472, 18213, 18473);", $dbshop, __FILE__, __LINE__);
	$anz=$anz+mysqli_num_rows($results);
	$results=q("SELECT * FROM fa_user_login WHERE kunid in (16815, 17285, 18465, 18466, 18211, 17292, 9753, 18467, 18469, 18209, 18471, 14110, 18463, 18472, 18213, 18473);", $dbshop, __FILE__, __LINE__);
	$anz=$anz+mysqli_num_rows($results);
	
	if($anz>0) echo $anz.' Ebay Zugaenge vorhanden'."\n";

	//IMPORT
	$kundnr=array();
	$password=array();
	$salt=array();
	$usermail=array();
	$adrid=array();
	$username=array();
	$name=array();
	
	$results=q("SELECT username, usermail, name, password, user_salt, idims_adr_id FROM cms_users WHERE idims_adr_id>0;", $dbweb, __FILE__, __LINE__);
	while($row=mysqli_fetch_array($results))
	{
		$kundnr[$row["idims_adr_id"]]=$row["username"];
		$password[$row["idims_adr_id"]]=$row["password"];
		$salt[$row["idims_adr_id"]]=$row["user_salt"];
		$usermail[$row["idims_adr_id"]]=$row["usermail"];
		$adrid[$row["idims_adr_id"]]=$row["idims_adr_id"];
		$username[$row["username"]]=$row["idims_adr_id"];
		$name[$row["idims_adr_id"]]=$row["name"];
	}

	$h=0;
	$i=0;
	$j=0;
	$k=0;
	$l=0;
	$m=0;
	$n=0;

	$results=q("SELECT * FROM fa_user_login;", $dbshop, __FILE__, __LINE__);
	while($row=mysqli_fetch_array($results))
	{
		if (!isset($adrid[$row["adrid"]]) and $row["adrid"]>0)
		{
			//IMPORT NEW USER
			if (!isset($username[$row["nic"]]))
			{
				//****************verschlüsselung und user-token********************************
				$user_token=createPassword(50);
				$salt_i=createPassword(32);
				$pw=$row["pass"];
				$pw=md5($pw);
				$pw=md5($pw.$salt_i);
				$pw=md5($pw.PEPPER);
				q("INSERT INTO cms_users (username, usermail, password, user_token, user_salt, userrole_id, idims_adr_id, lastlogin, lastvisit, session_id, firstmod, firstmod_user, lastmod, lastmod_user) VALUES('".$row["nic"]."', '".$row["reg_mail"]."', '".$pw."', '".$user_token."', '".$salt_i."', 10, ".$row["adrid"].", 0, 0, '', ".time().", 10, ".time().", 10);", $dbweb, __FILE__, __LINE__);
				//******************************************************************************
				$user_id=mysqli_insert_id($dbweb);
				q("INSERT INTO cms_users_sites (user_id, site_id, firstmod, firstmod_user, lastmod, lastmod_user) VALUES(".$user_id.", 17, ".time().", 10, ".time().", 10);", $dbweb, __FILE__, __LINE__);
		
				if($row["reg_mail"]!="")
				{
					$results2=q("SELECT * FROM cms_newsletter WHERE email='".$row["reg_mail"]."' LIMIT 1;", $dbweb, __FILE__, __LINE__);
					if(mysqli_num_rows($results2)>0)
					{
						q("UPDATE cms_users SET newsletter=1 WHERE idims_adr_id='".$row["adrid"]."';", $dbweb, __FILE__, __LINE__);
					}	
				}
	
				$i++;
				echo $i.': '.$row["nic"].'(ADR_ID '.$row["adrid"].') wurde als neuer Benutzer angelegt.'."\n";
			}
			else $h++; //DUPLICATE USER IN IDIMS
		}
		else
		{
			//UPDATE USERNAME
			if(isset($kundnr[$row["adrid"]]) and $row["nic"]!="" and $kundnr[$row["adrid"]]!=$row["nic"])
			{
				q("UPDATE cms_users SET username='".mysqli_real_escape_string($dbweb, $row["nic"])."' WHERE idims_adr_id='".$row["adrid"]."';", $dbweb, __FILE__, __LINE__);
				$n++;
				echo $n.'. '.$row["nic"].'(ADR_ID '.$row["adrid"].'): Benutzername aktualisiert.'."\n";
			}

			//UPDATE PASSWORD
			if(isset($salt[$row["adrid"]]) and strlen($salt[$row["adrid"]])==33 and isset($password[$row["adrid"]]) and (strlen($password[$row["adrid"]])==32 or $password[$row["adrid"]]==$row["pass"]))
			{
				$pw=$row["pass"];
				$pw=md5($pw);
				$pw=md5($pw.$salt[$row["adrid"]]);
				$pw=md5($pw.PEPPER);
				
				if ($password[$row["adrid"]]!=$pw)
				{
					if ( strlen( $pw ) == 32 ) {
						//echo $password[$row["adrid"]].' <--> '.$pw.'<br />';
						q("UPDATE cms_users SET password='".$pw."' WHERE idims_adr_id='".$row["adrid"]."';", $dbweb, __FILE__, __LINE__);
						$j++;
						echo $j.'. '.$row["nic"].'(ADR_ID '.$row["adrid"].'): Passwort aktualisiert.'."\n";
					}
					else {
						show_error( 9841, 6, __FILE__, __LINE__, "nic: " . $row['nic'] . "\n" . "password: " . $pw );
					}
				}
			}
			else
			{
				show_error(9786, 1, __FILE__, __LINE__, print_r($_SESSION, true)."\n".print_r($_POST, true)."\n"."nic: ".$row["nic"]."\n"."salt: ".$salt[$row["adrid"]]."\n"."password: ".$password[$row["adrid"]]);
			}
	
			//UPDATE EMAIL
			if ( isset($usermail[$row["adrid"]]) and $row["reg_mail"]!="" and $usermail[$row["adrid"]]!=$row["reg_mail"] )
			{
				if( $usermail[$row["adrid"]]!="" and $usermail[$row["adrid"]]!=$row["reg_mail"] )
				{
					q("UPDATE cms_users SET usermail='".mysqli_real_escape_string($dbweb, $row["reg_mail"])."' WHERE idims_adr_id='".$row["adrid"]."';", $dbweb, __FILE__, __LINE__);
					$k++;
					echo $k.'. '.$row["nic"].': E-Mail-Adresse wurde von '.$usermail[$row["adrid"]].' auf '.$row["reg_mail"].' aktualisiert.'."\n";
				}
				else
				{
					q("UPDATE cms_users SET usermail='".mysqli_real_escape_string($dbweb, $row["reg_mail"])."' WHERE idims_adr_id='".$row["adrid"]."';", $dbweb, __FILE__, __LINE__);
					$l++;
					echo $l.'. '.$row["nic"].'(ADR_ID '.$row["adrid"].'): E-Mail-Adresse eingetragen.'."\n";
				}
			}
		}
	}

	echo $h.' Benutzer sind in IDIMS mehrfach vorhanden.'."\n";
	echo $i.' neue Benutzer gefunden und importiert.'."\n";
	echo $n.' Benutzernamen wurden aktualisiert.'."\n";
	echo $j.' Passwörter wurden aktualisiert.'."\n";
	echo $k.' E-Mail Adressen wurden aktualisiert.'."\n";
	echo $l.' E-Mail Adressen wurden nachgetragen.'."\n";
	echo $m.' Adress-IDs wurden nachgetragen.'."\n";
	
	//cms_users name import
	$i=0;
	$results=q("SELECT ADR_ID, ANSCHR_1, ANSCHR_2, ANSCHR_3 FROM kunde;", $dbshop, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		if(isset($name[$row["ADR_ID"]]))
		{
			$cms_users_name=$row["ANSCHR_1"].' '.$row["ANSCHR_2"].' '.$row["ANSCHR_3"];
			if( $name[$row["ADR_ID"]]!=$cms_users_name )
			{
				$i++;
				q("UPDATE cms_users SET name='".mysqli_real_escape_string($dbweb, $cms_users_name)."' WHERE idims_adr_id=".$row["ADR_ID"].";", $dbweb, __FILE__, __LINE__);
			}
		}
	}
	echo $i.' Namen (name) wurden in der cms_users aktualisiert.'."\n";
	
?>
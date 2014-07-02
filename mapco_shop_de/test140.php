<?php

	/*********************************************************
	 * IMPORTS USERS FROM OLD SHOPS TO THE CLONE SHOP SYSTEM *
	 *********************************************************/

	include("config.php");
//	if( $_SESSION["id_user"]!=21371 ) exit;
	$db=$dblenkung24;
	$site_id=7;

	$results=q("SELECT * FROM cms_users;", $db, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		$conflict=false;
		$results2=q("SELECT id_user FROM cms_users WHERE username='".$row["username"]."';", $dbweb, __FILE__, __LINE__);
		while( $row2=mysqli_fetch_array($results2) )
		{
			$results3=q("SELECT * FROM cms_users_sites WHERE user_id=".$row2["id_user"]." AND site_id=".$site_id.";", $dbweb, __FILE__, __LINE__);
			if( mysqli_num_rows($results3)>0 )
			{
				$conflict=true;
				if( $row["new_user_id"]==0 )
				{
					q("UPDATE cms_users SET new_user_id=".$row2["id_user"]." WHERE id_user=".$row["id_user"].";", $db, __FILE__, __LINE__);
					echo $row["username"].' ist bereits bekannt und wurde als news_user_id eingetragen.<br />';
				}
				else
				{
					if( $row["new_user_id"]!=$row2["id_user"] )
					{
						echo $row["username"].' steht im Konflikt.<br />';
					}
					else
					{
						echo $row["username"].' wurde bereits eingetragen und wird deshalb übersprungen.<br />';
					}
				}
			}
		}

		if( !$conflict )
		{
			//remove sequential keys
			for($i=0; $i<sizeof($row); $i++)
			{
				if( isset($row[$i]) ) unset($row[$i]);
			}
			//remove unknown key
			unset($row["new_user_id"]);
			$old_id_user=$row["id_user"];
			unset($row["id_user"]);
			//update lastmod data
			$row["lastmod"]=time();
			$row["lastmod_user"]=21371;
			//insert into new cms_users
			q_insert("cms_users", $row, $dbweb, __FILE__, __LINE__);
			$new_user_id=mysqli_insert_id($dbweb);
			//update old cms_users
			q("UPDATE cms_users SET new_user_id=".$new_user_id." WHERE id_user=".$old_id_user.";", $db, __FILE__, __LINE__);
			//update firstmod_user in new cms_users
			q("UPDATE cms_users SET firstmod_user=".$new_user_id." WHERE id_user=".$new_user_id.";", $dbweb, __FILE__, __LINE__);
			//set user site rights
			q("INSERT INTO cms_users_sites (user_id, site_id, firstmod, firstmod_user, lastmod, lastmod_user) VALUES(".$new_user_id.", ".$site_id.", ".time().", 21371, ".time().", 21371);", $dbweb, __FILE__, __LINE__);
			echo $row["username"].' erfolgreich neu eingetragen.<br />';
		}
	}
?>
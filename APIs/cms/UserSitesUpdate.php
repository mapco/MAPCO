<?php
	if ( !isset($_POST["id_user"]) )
	{
		echo '<UserSitesUpdateResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Benutzer-ID nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine Benutzer-ID (id_user) übergeben werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</UserSitesUpdateResponse>'."\n";
		exit;
	}

	if ( !isset($_POST["sites"]) )
	{
		echo '<UserSitesUpdateResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Seiten nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es müssen Seiten(sites)übergeben werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</UserSitesUpdateResponse>'."\n";
		exit;
	}

	//get all user sites
	$site=array();
	$site2=array();
	$site3=array();
	$i=0;
	$results=q("SELECT * FROM cms_users_sites WHERE user_id=".$_POST["id_user"].";", $dbweb, __FILE__, __LINE__);	
	while( $row=mysqli_fetch_array($results) )
	{
		$site[$row["site_id"]]=$row["site_id"];
		$site2[$i]["id"]=$row["id"];
		$site2[$i]["site_id"]=$row["site_id"];
		$i++;
	}
	
	//add new sites
	$sites=explode(", ", $_POST["sites"]);
	for($i=0; $i<sizeof($sites); $i++)
	{
		if( $sites[$i]>0 and !isset($site[$sites[$i]]) )
		{
			q("INSERT INTO cms_users_sites (user_id, site_id, firstmod, firstmod_user, lastmod, lastmod_user) VALUES(".$_POST["id_user"].", ".$sites[$i].", ".time().", ".$_SESSION["id_user"].", ".time().", ".$_SESSION["id_user"].");", $dbweb, __FILE__, __LINE__);
		}
	}
	
	//remove old sites
	$sites=array_flip($sites);
	for($i=0; $i<sizeof($site2); $i++)
	{
		if( !isset($sites[$site2[$i]["site_id"]]) )
		{
			q("DELETE FROM cms_users_sites WHERE id=".$site2[$i]["id"].";", $dbweb, __FILE__, __LINE__);
		}
	}
	
	echo '<UserSitesUpdateResponse>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	echo '</UserSitesUpdateResponse>'."\n";

?>
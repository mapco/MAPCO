<?php

	if ( !isset($_POST["id_site"]) )
	{
		echo '<SiteSelectResponse>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Seite nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine Seite (id_site) übergeben werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</SiteSelectResponse>'."\n";
		exit;
	}
	$results=q("SELECT * FROM cms_sites WHERE id_site=".$_POST["id_site"].";", $dbweb, __FILE__, __LINE__);
	if( mysqli_num_rows($results)==0 )
	{
		echo '<SiteSelectResponse>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Seite ungültig.</shortMsg>'."\n";
		echo '		<longMsg>Die übergebene Seite (id_site) existiert nicht.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</SiteSelectResponse>'."\n";
		exit;
	}
	$site=mysqli_fetch_array($results);
	
	$results=q("SELECT * FROM cms_users_sites WHERE site_id=".$_POST["id_site"]." AND user_id=".$_SESSION["id_user"].";", $dbweb, __FILE__, __LINE__);
	if( mysqli_num_rows($results)==0 )
	{
		echo '<SiteSelectResponse>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Seitenberechtigung fehlt.</shortMsg>'."\n";
		echo '		<longMsg>Sie haben keine Berechtigung, um diese Seite aufzurufen.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</SiteSelectResponse>'."\n";
		exit;
	}

	$results=q("SELECT * FROM cms_sites WHERE id_site=".$_SESSION["id_site"].";", $dbweb, __FILE__, __LINE__);
	if( mysqli_num_rows($results)==0 )
	{
		echo '<SiteSelectResponse>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Seite ungültig.</shortMsg>'."\n";
		echo '		<longMsg>Die aktuelle Seite (id_site) existiert nicht.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</SiteSelectResponse>'."\n";
		exit;
	}
	$oldsite=mysqli_fetch_array($results);
	
	$_SESSION["id_site"]=$_POST["id_site"];

	//return success
	echo '<SiteSelectResponse>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	echo '	<OldDomain>'.$oldsite["domain"].'</OldDomain>'."\n";
	echo '	<NewDomain>'.$site["domain"].'</NewDomain>'."\n";
	echo '</SiteSelectResponse>'."\n";

?>
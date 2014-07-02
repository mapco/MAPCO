<?php

	if ( !isset($_POST["title"]) )
	{
		echo '<PartnerAddResponse>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Titel nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss ein Titel (title) übergeben werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</PartnerAddResponse>'."\n";
		exit;
	}
	
	if ( $_POST["title"]=="" )
	{
		echo '<PartnerAddResponse>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Titel leer.</shortMsg>'."\n";
		echo '		<longMsg>Der übergebene Titel (title) darf nicht leer sein.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</PartnerAddResponse>'."\n";
		exit;
	}
	
	if ( !isset($_POST["id_partnerprogram"]) )
	{
		echo '<PartnerAddResponse>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Partnerprogramm nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss ein Partnerprogramm (id_partnerprogram) übergeben werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</PartnerAddResponse>'."\n";
		exit;
	}
	$results=q("SELECT * FROM shop_partnerprograms WHERE id_partnerprogram='".$_POST["id_partnerprogram"]."';", $dbshop, __FILE__, __LINE__);
	if( mysqli_num_rows($results)==0 )
	{
		echo '<PartnerAddResponse>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Partnerprogramm ungültig.</shortMsg>'."\n";
		echo '		<longMsg>Das angegebene Partnerprogramm (id_partnerprogram) konnte nicht gefunden werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</PartnerAddResponse>'."\n";
		exit;
	}
	
	if ( !isset($_POST["description"]) ) $_POST["description"]="";
	
	//add partner program
	q("INSERT INTO shop_partners (partnerprogram_id, title, description, visit_counter, firstmod, firstmod_user, lastmod, lastmod_user) VALUES(".$_POST["id_partnerprogram"].", '".mysqli_real_escape_string($dbshop, $_POST["title"])."', '".mysqli_real_escape_string($dbshop, $_POST["description"])."', 0, ".time().", ".$_SESSION["id_user"].", ".time().", ".$_SESSION["id_user"].");", $dbshop, __FILE__, __LINE__);
	$id_partner=mysqli_insert_id($dbshop);

	//return success
	echo '<PartnerAddResponse>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	echo '	<id_partner>'.$id_partner.'</id_partner>'."\n";
	echo '</PartnerAddResponse>'."\n";

?>
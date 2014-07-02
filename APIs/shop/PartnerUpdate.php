<?php

	if ( !isset($_POST["id_partner"]) )
	{
		echo '<PartnerUpdateResponse>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Partner-ID nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine Partner-ID (id_partner) übergeben werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</PartnerUpdateResponse>'."\n";
		exit;
	}
	$results=q("SELECT * FROM shop_partners WHERE id_partner=".$_POST["id_partner"].";", $dbshop, __FILE__, __LINE__);
	if( mysqli_num_rows($results)==0 )
	{
		echo '<PartnerUpdateResponse>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Partnerprogramm nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Zu der angegebenen Partner-ID (id_partner) konnte keine Partnerprogramm gefunden werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</PartnerUpdateResponse>'."\n";
		exit;
	}

	if ( !isset($_POST["title"]) )
	{
		echo '<PartnerUpdateResponse>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Titel nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss ein Titel (title) übergeben werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</PartnerUpdateResponse>'."\n";
		exit;
	}
	
	if ( $_POST["title"]=="" )
	{
		echo '<PartnerUpdateResponse>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Titel leer.</shortMsg>'."\n";
		echo '		<longMsg>Der übergebene Titel (title) darf nicht leer sein.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</PartnerUpdateResponse>'."\n";
		exit;
	}
	
	if ( !isset($_POST["description"]) ) $_POST["description"]="";

	q("UPDATE shop_partners
		SET	title='".mysqli_real_escape_string($dbshop, $_POST["title"])."',
			description='".mysqli_real_escape_string($dbshop, $_POST["description"])."'
		WHERE id_partner=".$_POST["id_partner"].";", $dbshop, __FILE__, __LINE__);

	echo '<PartnerUpdateResponse>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	echo '</PartnerUpdateResponse>'."\n";

?>
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
	
	if ( !isset($_POST["description"]) ) $_POST["description"]="";
	
	//add partner program
	q("INSERT INTO shop_partnerprograms (title, description, firstmod, firstmod_user, lastmod, lastmod_user) VALUES('".mysqli_real_escape_string($dbshop, $_POST["title"])."', '".mysqli_real_escape_string($dbshop, $_POST["description"])."', ".time().", ".$_SESSION["id_user"].", ".time().", ".$_SESSION["id_user"].");", $dbshop, __FILE__, __LINE__);
	$id_partnerprogram=mysqli_insert_id($dbshop);

	//return success
	echo '<PartnerAddResponse>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	echo '	<id_partnerprogram>'.$id_partnerprogram.'</id_partnerprogram>'."\n";
	echo '</PartnerAddResponse>'."\n";

?>
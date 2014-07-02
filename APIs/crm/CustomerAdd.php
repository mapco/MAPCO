<?php
	if ( !isset($_POST["name"]) )
	{
		echo '<CustomerAddResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Name nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss Name (name) übergeben werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</CustomerAddResponse>'."\n";
		exit;
	}

	if ( $_POST["name"]=="" )
	{
		echo '<CustomerAddResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Name leer.</shortMsg>'."\n";
		echo '		<longMsg>Der übergebene Name (name) darf nicht leer sein.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</CustomerAddResponse>'."\n";
		exit;
	}

	q("INSERT INTO crm_customers (
		company,
		name,
		street1, 
		street2, 
		zip, 
		city, 
		country, 
		phone, 
		mobile, 
		fax, 
		mail, 
		firstmod, 
		firstmod_user, 
		lastmod, 
		lastmod_user
	) VALUES(
		'".mysqli_real_escape_string($dbweb, $_POST["company"])."', 
		'".mysqli_real_escape_string($dbweb, $_POST["name"])."', 
		'".mysqli_real_escape_string($dbweb, $_POST["street1"])."', 
		'".mysqli_real_escape_string($dbweb, $_POST["street2"])."', 
		'".mysqli_real_escape_string($dbweb, $_POST["zip"])."', 
		'".mysqli_real_escape_string($dbweb, $_POST["city"])."', 
		'".mysqli_real_escape_string($dbweb, $_POST["country"])."', 
		'".mysqli_real_escape_string($dbweb, $_POST["phone"])."', 
		'".mysqli_real_escape_string($dbweb, $_POST["mobile"])."', 
		'".mysqli_real_escape_string($dbweb, $_POST["fax"])."', 
		'".mysqli_real_escape_string($dbweb, $_POST["mail"])."', 
		".time().", 
		".$_SESSION["id_user"].", 
		".time().", 
		".$_SESSION["id_user"]."
	);", $dbweb, __FILE__, __LINE__);
	
	echo '<CustomerAddResponse>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	echo '</CustomerAddResponse>'."\n";
?>
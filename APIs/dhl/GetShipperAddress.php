<?php
	if( !isset($_SESSION["id_site"]) )
	{
		echo '<GetShipperAddressResponse>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Site ID nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es konnte keine Site ID (id_site) ermittelt werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</GetShipperAddressResponse>'."\n";
		exit;
	}

	$results=q("SELECT location_id FROM cms_sites WHERE id_site=".$_SESSION["id_site"].";", $dbweb, __FILE__, __LINE__);
	if( mysqli_num_rows($results)==0 )
	{
		echo '<GetShipperAddressResponse>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Seite nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Zu der angegebenen Site ID (id_site) konnte keine Webseite in der Datenbank gefunden werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</GetShipperAddressResponse>'."\n";
		exit;
	}
	$site=mysqli_fetch_array($results);

	$results=q("SELECT * FROM cms_contacts_locations WHERE id_location=".$site["location_id"].";", $dbweb, __FILE__, __LINE__);
	if( mysqli_num_rows($results)==0 )
	{
		echo '<GetShipperAddressResponse>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Standort nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Zu der angegebenen Seite (domain) konnte kein Standort in der Datenbank gefunden werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</GetShipperAddressResponse>'."\n";
		exit;
	}
	$location=mysqli_fetch_array($results);

	echo '<GetShipperAddressResponse>';
	echo '	<Ack>Success</Ack>';
	echo '	<ShipperCompany>'.$location["company"].'</ShipperCompany>';
	echo '	<ShipperCompany2>'.$location["title"].'</ShipperCompany2>';
	echo '	<ShipperCompanyFirstname>'.$location["firstname"].'</ShipperCompanyFirstname>';
	echo '	<ShipperCompanyLastname>'.$location["lastname"].'</ShipperCompanyLastname>';
	echo '	<ShipperStreetName>'.$location["street"].'</ShipperStreetName>';
	echo '	<ShipperStreetNumber>'.$location["streetnr"].'</ShipperStreetNumber>';
	echo '	<ShipperZip>'.$location["zipcode"].'</ShipperZip>';
	echo '	<ShipperCity>'.$location["city"].'</ShipperCity>';
	echo '	<ShipperContactPerson>'.$location["firstname"].' '.$location["lastname"].'</ShipperContactPerson>';
	echo '	<ShipperOrigin>'.$location["country_code"].'</ShipperOrigin>';
	echo '	<ShipperPhone>'.$location["phone"].'</ShipperPhone>';
	echo '	<ShipperEmail>'.$location["mail"].'</ShipperEmail>';
	echo '</GetShipperAddressResponse>';

?>
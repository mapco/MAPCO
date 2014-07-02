<?php
	$starttime = time()+microtime();

	if ( !isset($_POST["search"]) )
	{
		echo '<ContactsContactSearchResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Suchezeichenkette fehlt.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine Suchzeichenkette (search) übergeben werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ContactsContactSearchResponse>'."\n";
		exit;
	}

	$search=explode(" ", $_POST["search"]);
	$results=q("SELECT * FROM cms_contacts WHERE active>0;", $dbweb, __FILE_, __LINE__);
	$contacts=array();
	while( $row=mysqli_fetch_array($results) )
	{
		$found=false;
		for($i=0; $i<sizeof($search); $i++)
		{
			if( stripos($row["firstname"], $search[$i]) !== false ) { $found=true; break; }
			if( stripos($row["lastname"], $search[$i]) !== false ) { $found=true; break; }
			if( stripos($row["position"], $search[$i]) !== false ) { $found=true; break; }
		}
		if($found) $contacts[]=$row;
	}
	
	//performance
	$stoptime = time()+microtime();
	$time = $stoptime-$starttime;
	
	echo '<ContactsContactSearchResponse>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	echo '	<Runtime>'.number_format($time, 2).'</Runtime>'."\n";
	foreach($contacts as $contact)
	{
		$keys=array_keys($contact);
		echo '<Contact>'."\n";
		for($i=0; $i<sizeof($keys); $i++)
		{
			if( !is_numeric($keys[$i]) )
				echo '	<'.$keys[$i].'><![CDATA['.$contact[$keys[$i]].']]></'.$keys[$i].'>'."\n";
		}
		$results=q("SELECT location_id FROM cms_contacts_departments WHERE id_department=".$contact["department_id"].";", $dbweb, __FILE__, __LINE__);
		$row=mysqli_fetch_array($results);
		echo '	<location_id><![CDATA['.$row["location_id"].']]></location_id>'."\n";
		echo '</Contact>'."\n";
	}
	echo '</ContactsContactSearchResponse>'."\n";

?>
<?php
	$starttime = time()+microtime();

	if ( !isset($_POST["Zipcode1"]) )
	{
		echo '<ZipcodeDistanceResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Postleitzahl 1 nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine Postleitzahl 1 übermittelt werden, damit der Service weiß, zwischen welchen Orten die Distanz berechnet werden soll.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ImageMassDownloadResponse>'."\n";
		exit;
	}

	if ( !isset($_POST["Zipcode2"]) )
	{
		echo '<ZipcodeDistanceResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Postleitzahl 2 nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine Postleitzahl 2 übermittelt werden, damit der Service weiß, zwischen welchen Orten die Distanz berechnet werden soll.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ImageMassDownloadResponse>'."\n";
		exit;
	}

	//get latitude and longitude for each zipcode
	$results=q("SELECT * FROM cms_zipcodes WHERE zipcode=".$_POST["Zipcode1"].";", $dbweb, __FILE__, __LINE__);
	$row=mysqli_fetch_array($results);
	$name1=$row["name"];
	$lat1=$row["latitude"];
	$long1=$row["longitude"];
	$results=q("SELECT * FROM cms_zipcodes WHERE zipcode=".$_POST["Zipcode2"].";", $dbweb, __FILE__, __LINE__);
	$row=mysqli_fetch_array($results);
	$name2=$row["name"];
	$lat2=$row["latitude"];
	$long2=$row["longitude"];
	
	//GRAD to RAD
	$lat1=$lat1/180*pi();
	$long1=$long1/180*pi();
	$lat2=$lat2/180*pi();
	$long2=$long2/180*pi();
	
	//lat and long in GRAD
	$e = acos( sin($lat1)*sin($lat2) + cos($lat1)*cos($lat2)*cos($long2-$long1) );
	
	//get distance
	$distance = $e * 6378.137;

	//return distance
	echo '<ZipcodeDistanceResponse>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	echo '	<Name1>'.$name1.'</Name1>'."\n";
	echo '	<Name2>'.$name2.'</Name2>'."\n";
	echo '	<Distance>'.$distance.'</Distance>'."\n";
	echo '</ZipcodeDistanceResponse>'."\n";
	exit;

?>
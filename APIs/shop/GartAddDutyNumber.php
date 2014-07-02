<?php
	$starttime = time()+microtime();

	if ( !isset($_POST["mode"]) || (isset($_POST["mode"]) && $_POST["mode"]=="") )
	{
		echo '<GartAddDutyNumberResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Bearbeitungsmodus nicht angegeben</shortMsg>'."\n";
		echo '		<longMsg>Es muss ein Bearbeitungsmodus angegeben werden. Verfügbare Modi: add || update </longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</GartAddDutyNumberResponse>'."\n";
		exit;
	}
	

	if ($_POST["mode"]=="add") {
		
		if ( !isset($_POST["GART"]) )
		{
			echo '<GartAddDutyNumberResponse>'."\n";
			echo '	<Ack>Failure</Ack>'."\n";
			echo '	<Error>'."\n";
			echo '		<Code>'.__LINE__.'</Code>'."\n";
			echo '		<shortMsg>generische Artikelgruppe nicht gefunden.</shortMsg>'."\n";
			echo '		<longMsg>Es muss eine generische Artikelgruppe ausgewählt werden.</longMsg>'."\n";
			echo '	</Error>'."\n";
			echo '</GartAddDutyNumberResponse>'."\n";
			exit;
		}

		$results=q("SELECT GART FROM t_200 WHERE GART='".$_POST["GART"]."';", $dbshop, __FILE__, __LINE__);
		if ( mysqli_num_rows($results)==0 )
		{
			echo '<GartAddDutyNumberResponse>'."\n";
			echo '	<Ack>Failure</Ack>'."\n";
			echo '	<Error>'."\n";
			echo '		<Code>'.__LINE__.'</Code>'."\n";
			echo '		<shortMsg>generische Artikelgruppe nicht gefunden.</shortMsg>'."\n";
			echo '		<longMsg>Es konnte keine passende generische Artikelgruppe zur Auswahl gefunden werden.</longMsg>'."\n";
			echo '	</Error>'."\n";
			echo '</GartAddDutyNumberResponse>'."\n";
			exit;
		}
		
		q("INSERT INTO shop_items_duty_numbers (GART, duty_number) VALUES (".$_POST["GART"].", '".$_POST["DutyNumber"]."');", $dbshop, __FILE__, __LINE__);
		

	} // IF MODE ADD
	
	if ($_POST["mode"]=="update") {
		
		if ( !isset($_POST["id_DutyNumber"]) || (isset($_POST["id_DutyNumber"]) && $_POST["id_DutyNumber"]=="") )
		{
			echo '<GartAddDutyNumberResponse>'."\n";
			echo '	<Ack>Failure</Ack>'."\n";
			echo '	<Error>'."\n";
			echo '		<Code>'.__LINE__.'</Code>'."\n";
			echo '		<shortMsg>DutyNumber-ID nicht gefunden.</shortMsg>'."\n";
			echo '		<longMsg>Es muss eine DutyNumber-ID angegeben werden um ein Update zur Zolltarifnummer durchzuführen.</longMsg>'."\n";
			echo '	</Error>'."\n";
			echo '</GartAddDutyNumberResponse>'."\n";
			exit;
		}

		q("UPDATE shop_items_duty_numbers SET duty_number = '".$_POST["DutyNumber"]."' WHERE id = ".$_POST["id_DutyNumber"].";", $dbshop, __FILE__, __LINE__);

	} // IF MODE UPDATE
	
	$error=mysqli_error($dbshop);
	if ($error!="")
	{
		echo '<GartAddDutyNumberResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Fehler beim Schreiben der Daten</shortMsg>'."\n";
		echo '		<longMsg>MySQL-Fehlermeldung: '.$error.'</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</GartAddDutyNumberResponse>'."\n";
		exit;
	}

	//performance
	$stoptime = time()+microtime();
	$time = $stoptime-$starttime;
	
	echo '<GartAddDutyNumberResponse>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	echo '	<Runtime>'.number_format($time, 2).'</Runtime>'."\n";
	echo '</GartAddDutyNumberResponse>'."\n";

?>
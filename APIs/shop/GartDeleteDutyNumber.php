<?php
	$starttime = time()+microtime();

	if ( !isset($_POST["id_DutyNumber"]) )
	{
		echo '<GartDeleteDutyNumber>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Zolltarifnummer ID nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine Zolltarifnummer ID ausgewählt werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</GartDeleteDutyNumber>'."\n";
		exit;
	}

	if ( !isset($_POST["GART"]) )
	{
		echo '<GartDeleteDutyNumber>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>generische Artikelgruppe nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine generische Artikelgruppe ausgewählt werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</GartDeleteDutyNumber>'."\n";
		exit;
	}

	$res=q("DELETE FROM shop_items_duty_numbers WHERE GART = ".$_POST["GART"]." AND id = ".$_POST["id_DutyNumber"].";", $dbshop, __FILE__, __LINE__);
	
	$error=mysqli_error($dbshop);
	if ($error!="")
	{
		echo '<GartDeleteDutyNumber>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Fehler beim Löschen der Zolltarifnummer</shortMsg>'."\n";
		echo '		<longMsg>MySQL-Fehlermeldung: '.$error.'</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</GartDeleteDutyNumber>'."\n";
		exit;
	}

	//performance
	$stoptime = time()+microtime();
	$time = $stoptime-$starttime;

	if (mysqli_affected_rows()==0)
	{
		echo '<GartDeleteDutyNumber>'."\n";
		echo '	<Ack>Warning</Ack>'."\n";
		echo '		<Warning>'."\n";
		echo '		<shortMsg>Zolltarifnummer wurde nicht gelöscht</shortMsg>'."\n";
		echo '		<longMsg>Zur Gart '.$_POST["GART"].' wurde keine Zolltarifnummer gefunden</longMsg>'."\n";
		echo '		</Warning>'."\n";
		echo '	<Runtime>'.number_format($time, 2).'</Runtime>'."\n";
		echo '</GartDeleteDutyNumber>'."\n";

	}

	else 
	{
		echo '<GartDeleteDutyNumber>'."\n";
		echo '	<Ack>Success</Ack>'."\n";
		echo '	<Succes>'."\n";
		echo '		<shortMsg>Zolltarifnummer wurde gelöscht</shortMsg>'."\n";
		echo '		<longMsg>Zolltarifnummer zur Gart '.$_POST["GART"].' wurde gelöscht</longMsg>'."\n";
		echo '	</Succes>'."\n";
		echo '	<Runtime>'.number_format($time, 2).'</Runtime>'."\n";
		echo '</GartDeleteDutyNumber>'."\n";
	}

?>
<?php
	$starttime = time()+microtime();

	if ( !isset($_POST["id_DutyNumber"]) )
	{
		echo '<GartGetDutyNumberResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>DutyNumber ID nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine DutyNumber ID ausgewählt werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</GartGetDutyNumberResponse>'."\n";
		exit;
	}

	
	$res=q("SELECT * FROM shop_items_duty_numbers WHERE id= ".$_POST["id_DutyNumber"].";", $dbshop, __FILE__, __LINE__);
	//performance
	$stoptime = time()+microtime();
	$time = $stoptime-$starttime;

	if (mysqli_num_rows($res)==0)
	{
		echo '<GartGetDutyNumberResponse>'."\n";
		echo '	<Ack>Warning</Ack>'."\n";
		echo '		<Warning>'."\n";
		echo '		<shortMsg>Anfrage liefert kein Ergebnis</shortMsg>'."\n";
		echo '		<longMsg>Zur Gart DutyNumber ID wurde keine Zolltarifnummer gefunden</longMsg>'."\n";
		echo '		</Warning>'."\n";
		echo '	<Runtime>'.number_format($time, 2).'</Runtime>'."\n";
		echo '</GartGetDutyNumberResponse>'."\n";

	}

	else 
	{
		$row=mysqli_fetch_array($res);
		$DutyNumber=$row["duty_number"];
		echo '<GartGetDutyNumberResponse>'."\n";
		echo '	<Ack>Success</Ack>'."\n";
		echo '	<Succes>'."\n";
		echo '		<DutyNumber>'.$row["duty_number"].'</DutyNumber>';
		echo '	</Succes>'."\n";
		echo '	<Runtime>'.number_format($time, 2).'</Runtime>'."\n";
		echo '</GartGetDutyNumberResponse>'."\n";
	}

?>
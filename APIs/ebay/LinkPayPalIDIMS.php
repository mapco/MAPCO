<?php

	if ( !isset($_POST["IDIMS_ID"]) || $_POST["IDIMS_ID"]=="" )
	{
		echo '<LinkPayPalIDIMSResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>IDIMS Auftragsnummer nicht gefunden</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine IDIMS Auftragsnummer für die Verknüpfung übermittelt werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</LinkPayPalIDIMSResponse>'."\n";
		exit;
	}

	if ( !isset($_POST["ID_PN"]) || $_POST["ID_PN"]=="" )
	{
		echo '<LinkPayPalIDIMSResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>PaymentNotification ID nicht gefunden</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine PaymentNotification ID für die Verknüpfung übermittelt werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</LinkPayPalIDIMSResponse>'."\n";
		exit;
	}

	$res=q("SELECT * FROM payment_notifications WHERE id_PN = '".$_POST["ID_PN"]."';", $dbshop, __FILE__, __LINE__);
	
	if (mysqli_num_rows($res)==0)
	{
		echo '<LinkPayPalIDIMSResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>PaymentNotification nicht gefunden</shortMsg>'."\n";
		echo '		<longMsg>Zur ID konnte keine PaymentNotification gefunden werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</LinkPayPalIDIMSResponse>'."\n";
		exit;
	}

	$res=q("UPDATE payment_notifications SET IDIMS_AuftragsNR ='".$_POST["IDIMS_ID"]."' WHERE id_PN = '".$_POST["ID_PN"]."';", $dbshop, __FILE__, __LINE__);
	
	echo '<LinkPayPalIDIMSResponse>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	echo '</LinkPayPalIDIMSResponse>'."\n";

?>
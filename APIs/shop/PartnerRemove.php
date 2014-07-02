<?php

	if ( !isset($_POST["id_partner"]) )
	{
		echo '<PartnerRemoveResponse>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Partner-ID nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine Partner-ID (id_partner) übergeben werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</PartnerRemoveResponse>'."\n";
		exit;
	}
	$results=q("SELECT * FROM shop_partners WHERE id_partner=".$_POST["id_partner"].";", $dbshop, __FILE__, __LINE__);
	if( mysqli_num_rows($results)==0 )
	{
		echo '<PartnerRemoveResponse>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Partnerprogramm nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Zu der angegebenen Partner-ID (id_partner) konnte keine Partnerprogramm gefunden werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</PartnerRemoveResponse>'."\n";
		exit;
	}
	$row=mysqli_fetch_array($results);
	if( $row["visit_counter"]>0 )
	{
		echo '<PartnerRemoveResponse>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Partnerprogramm nicht löschbar.</shortMsg>'."\n";
		echo '		<longMsg>Das Partnerprogramm kann nicht gelöscht werden, weil es bereits verwendet wurde.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</PartnerRemoveResponse>'."\n";
		exit;
	}

	q("DELETE FROM shop_partners WHERE id_partner=".$_POST["id_partner"].";", $dbshop, __FILE__, __LINE__);

	echo '<PartnerRemoveResponse>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	echo '</PartnerRemoveResponse>'."\n";

?>
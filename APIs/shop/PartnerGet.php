<?php

	if ( !isset($_POST["id_partner"]) )
	{
		echo '<PartnerGetResponse>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Partner-ID nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine Partner-ID (id_partner) übergeben werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</PartnerGetResponse>'."\n";
		exit;
	}
	$results=q("SELECT * FROM shop_partners WHERE id_partner=".$_POST["id_partner"].";", $dbshop, __FILE__, __LINE__);
	if( mysqli_num_rows($results)==0 )
	{
		echo '<PartnerGetResponse>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Partnerprogramm nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Zu der angegebenen Partner-ID (id_partner) konnte keine Partnerprogramm gefunden werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</PartnerGetResponse>'."\n";
		exit;
	}

	echo '<PartnerGetResponse>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	$row=mysqli_fetch_array($results);
	if( !isset($key) ) $keys=array_keys($row);
	for($i=0; $i<sizeof($keys); $i++)
	{
		if( !is_numeric($keys[$i]) )
			echo '		<'.$keys[$i].'><![CDATA['.$row[$keys[$i]].']]></'.$keys[$i].'>'."\n";
	}
	echo '</PartnerGetResponse>'."\n";

?>
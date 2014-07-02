<?php

	if( !isset($_POST["id_account"]) )
	{
		echo '<AccountGetResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Account nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss ein Ebay-Account (id_account) angegeben werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</AccountGetResponse>'."\n";
		exit;
	}
	$results=q("SELECT * FROM ebay_accounts WHERE id_account=".$_POST["id_account"].";", $dbshop, __FILE__, __LINE__);
	if ( mysqli_num_rows($results)==0 )
	{
		echo '<AccountGetResponse>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Bestellung nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es scheint keine Bestellung mit der angegebenen Nummer zu existieren.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</AccountGetResponse>'."\n";
		exit;
	}
	$row=mysqli_fetch_array($results);
	$keys=array_keys($row);
	
	echo '<AccountGetResponse>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	for($i=0; $i<sizeof($keys); $i++)
	{
		if( !is_numeric($keys[$i]) )
			echo '	<'.$keys[$i].'><![CDATA['.$row[$keys[$i]].']]></'.$keys[$i].'>'."\n";
	}
	echo '</AccountGetResponse>'."\n";


?>
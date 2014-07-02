<?php
	if ( !isset($_POST["ids"]) )
	{
		echo '<ListEditResponse>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Sortierung fehlt.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine Sotierung (ordering) übergeben werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ListEditResponse>'."\n";
		exit;
	}

	$id=explode(", ", $_POST["ids"]);
	
	//update shop_lists
	for($i=0; $i<sizeof($id); $i++)
	{
		$data=array();
		$data["ordering"]=$i+1;
		q_update("shop_lists_fields", $data, "WHERE id=".$id[$i], $dbshop, __FILE__, __LINE__);
	}

?>
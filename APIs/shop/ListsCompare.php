<?php
	if ( !isset($_POST["id_list1"]) )
	{
		echo '<ListsCompare>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Liste 1 nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine erste Liste (id_list1) übergeben werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ListsCompare>'."\n";
		exit;
	}

	if ( !isset($_POST["id_list2"]) )
	{
		echo '<ListsCompare>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Liste 2 nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss ein zweite Liste (id_list2) übergeben werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ListsCompare>'."\n";
		exit;
	}

	$list1=array();
	$results=q("SELECT * FROM shop_lists_items WHERE list_id=".$_POST["id_list1"].";", $dbshop, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		$list1[$row["item_id"]]=0;
	}

	$list=array();
	$results=q("SELECT * FROM shop_lists_items WHERE list_id=".$_POST["id_list2"].";", $dbshop, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		if( isset($list1[$row["item_id"]]) )
		{
			$list[]=$row["item_id"];
		}
	}

	//return success
	echo '<ListsCompare>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	for($i=0; $i<sizeof($list); $i++)
	{
		echo '	<ItemID>'.$list[$i].'</ItemID>'."\n";
	}
	echo '</ListsCompare>'."\n";

?>
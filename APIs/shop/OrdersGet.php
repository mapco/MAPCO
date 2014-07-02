<?php
	include("../functions/cms_t.php");


	if( !isset($_POST["from"]) ) $_POST["from"]=time()-15*24*3600;
	if( !isset($_POST["to"]) ) $_POST["to"]=time();
	$query  = "SELECT * FROM shop_orders WHERE firstmod>=".$_POST["from"]." AND firstmod<=".$_POST["to"];
	if( isset($_POST["filter"]) and $_POST["filter"]=="open" ) $query .= " AND AUF_ID>0 AND shipping_number=''";
	elseif( isset($_POST["filter"]) and $_POST["filter"]=="return" ) $query .= " AND RetourLabelID!=''";
	if( isset($_POST["shippingtypes"]) ) $query.=" AND shipping_type_id IN (".$_POST["shippingtypes"].")";
	$query .= " ORDER BY firstmod;";

	$results=q($query, $dbshop, __FILE__. __LINE__);
	echo '<OrdersGetResponse>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	while( $row=mysqli_fetch_array($results) )
	{
		echo '<Order>';
		$keys=array_keys($row);
		
//		echo '<OrdersGetResponse>'."\n";
		for($i=0; $i<sizeof($keys); $i++)
		{
			if( !is_numeric($keys[$i]) )
				echo '	<'.$keys[$i].'><![CDATA['.str_replace("&", "&amp;", ($row[$keys[$i]])).']]></'.$keys[$i].'>'."\n";
		}
		echo '</Order>';
	}
	echo '</OrdersGetResponse>'."\n";

?>
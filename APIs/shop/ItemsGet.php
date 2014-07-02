<?php

	echo '<ItemsGetResponse>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	if( isset($_POST["GART"]) )
	{
		$results=q("SELECT * FROM shop_items WHERE GART=".$_POST["GART"].";", $dbshop, __FILE__, __LINE__);
	}
	else
	{
		$results=q("SELECT * FROM shop_items WHERE active>0;", $dbshop, __FILE__, __LINE__);
	}
	while( $row=mysqli_fetch_array($results) )
	{
		echo '	<Item>'.$row["MPN"].'</Item>'."\n";
	}
	echo '</ItemsGetResponse>'."\n";

?>
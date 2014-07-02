<?php

	echo '<PartnersGetResponse>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	$results=q("SELECT * FROM shop_partners WHERE partnerprogram_id=".$_POST["id_partnerprogram"]." ORDER BY lastmod DESC;", $dbshop, __FILE__, __LINE__);
	while($row=mysqli_fetch_array($results))
	{
		if( !isset($key) ) $keys=array_keys($row);
		echo '	<Partner>'."\n";
		
		//general data
		for($i=0; $i<sizeof($keys); $i++)
		{
			if( !is_numeric($keys[$i]) )
				echo '		<'.$keys[$i].'><![CDATA['.$row[$keys[$i]].']]></'.$keys[$i].'>'."\n";
		}
		
		//registrations
		$results2=q("SELECT id_user FROM cms_users WHERE partner_id_registration=".$row["id_partner"].";", $dbweb, __FILE__, __LINE__);
		$Registrations=mysqli_num_rows($results2);
		echo '<Registrations>'.$Registrations.'</Registrations>';
		
		//participants
		$results2=q("SELECT id_user FROM cms_users WHERE partner_id_registration=".$row["id_partner"]." OR partner_id=".$row["id_partner"].";", $dbweb, __FILE__, __LINE__);
		$Registrations=mysqli_num_rows($results2);
		echo '<Participants>'.$Registrations.'</Participants>';
		
		//orders
		$results2=q("SELECT id_order FROM shop_orders WHERE partner_id=".$row["id_partner"].";", $dbshop, __FILE__, __LINE__);
		$Orders=mysqli_num_rows($results2);
		echo '<Orders>'.$Orders.'</Orders>';
		
		//revenue
		$in=array();
		while( $row2=mysqli_fetch_array($results2) ) $in[]=$row2["id_order"];
		$Revenue=0;
		if( sizeof($in)>0 )
		{
			$results2=q("SELECT netto FROM shop_orders_items WHERE order_id IN (".implode(", ", $in).");", $dbshop, __FILE__, __LINE__);
			while( $row2=mysqli_fetch_array($results2) ) $Revenue += $row2["netto"];
		}
		echo '<Revenue>'.$Revenue.'</Revenue>';

		echo '	</Partner>'."\n";
	}
	echo '</PartnersGetResponse>'."\n";

?>
<?php

	if ( !isset($_POST["itemID"]) && !isset($_POST["MPN"]) )
	{
		echo '<ShopItemGetResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>ItemID odr MPN nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine ItemID oder MPN angegeben werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ShopItemGetResponse>'."\n";
		exit;
	}
	

	if (isset($_POST["itemID"]))
	{
		$sql="SELECT a.MPN, b.* FROM shop_items as a, shop_items_".$_SESSION["lang"]." as b WHERE a.id_item = ".$_POST["itemID"]." AND a.id_item = b.id_item;";
	}
	if (isset($_POST["MPN"]))
	{
		$sql="SELECT a.MPN, b.* FROM shop_items as a, shop_items_".$_SESSION["lang"]." as b WHERE a.MPN = '".$_POST["MPN"]."' AND a.id_item = b.id_item;";
	}
	$results=q($sql, $dbshop, __FILE__, __LINE__);
	if (mysqli_num_rows($results)==0)
	{
		echo '<ShopItemGetResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Kein Ergebnis gefunden</shortMsg>'."\n";
		echo '		<longMsg>Kein Ergebnis gefunden zur ItemID oder MPN gefunden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ShopItemGetResponse>'."\n";
		exit;
	}
	else
	{
		
		$row=mysqli_fetch_array($results);
		$keys=array_keys($row);
		
		echo '<ShopItemGetResponse>'."\n";
		$results=q($sql, $dbshop, __FILE__, __LINE__);
		if (mysqli_num_rows($results)==1) 
		{
			echo '	<Ack>Success</Ack>'."\n";

			while($row=mysqli_fetch_array($results))
			{
				for($i=0; $i<sizeof($keys); $i++)
				{
					if( !is_numeric($keys[$i]) )
						echo '	<'.$keys[$i].'><![CDATA['.$row[$keys[$i]].']]></'.$keys[$i].'>'."\n";
				}
			}
		}
		if (mysqli_num_rows($results)==0) 
		{
			echo '	<Ack>Warning</Ack>'."\n";
			echo '	<Error>'."\n";
			echo '		<Code>'.__LINE__.'</Code>'."\n";
			echo '		<shortMsg>Kein Artikel gefunden</shortMsg>'."\n";
			echo '		<longMsg>Kein Artikel gefunden</longMsg>'."\n";
			echo '	</Error>'."\n";

		}
		echo '</ShopItemGetResponse>'."\n";
	}
?>
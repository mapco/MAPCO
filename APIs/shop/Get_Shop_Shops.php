<?php

	if ( !isset($_POST["mode"]) )
	{
		echo '<Get_Shop_ShopsResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Ausgabemodus nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss ein Modus für die Ausgabe der Zahlungstypen angegeben werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</Get_Shop_ShopsResponse>'."\n";
		exit;
	}

	if ($_POST["mode"]=="all")
	{
		$results=q("SELECT * FROM shop_shops;", $dbshop, __FILE__, __LINE__);
		$row=mysqli_fetch_array($results);
		$keys=array_keys($row);
		
		$xmldata='';
		
		$res=q("SELECT * FROM shop_shops;", $dbshop, __FILE__, __LINE__);
		while ($row=mysqli_fetch_array($res))
		{
			$xmldata.="	<Shop_Shop>\n";
			for($i=0; $i<sizeof($keys); $i++)
			{
				if( !is_numeric($keys[$i]) )
					$xmldata.='	 <'.$keys[$i].'><![CDATA['.$row[$keys[$i]].']]></'.$keys[$i].'>'."\n";
			}
			$xmldata.="	</Shop_Shop>\n";
		}
	}
	
	if ($_POST["mode"]=="user")
	{
		$user_shops=array();
		$user_sites='';
		
		$results2=q("SELECT * FROM cms_users_sites WHERE user_id=".$_SESSION["id_user"].";", $dbweb, __FILE__, __LINE__);
		while($row2=mysqli_fetch_array($results2))
		{
			$user_sites.=','.$row2["site_id"];
		}
		$user_sites=substr($user_sites, 1);
		
		$results3=q("SELECT * FROM shop_shops WHERE site_id IN (".$user_sites.");", $dbshop, __FILE__, __LINE__);
		while($row3=mysqli_fetch_array($results3))
		{
			$user_shops[]=$row3["id_shop"];
		}
		
		$results=q("SELECT * FROM shop_shops;", $dbshop, __FILE__, __LINE__);
		$row=mysqli_fetch_array($results);
		$keys=array_keys($row);
		
		$xmldata='';
		
		$res=q("SELECT * FROM shop_shops;", $dbshop, __FILE__, __LINE__);
		while ($row=mysqli_fetch_array($res))
		{
			if(in_array($row["id_shop"],$user_shops) or in_array($row["parent_shop_id"],$user_shops))
			{
				$xmldata.="	<Shop_Shop>\n";
				for($i=0; $i<sizeof($keys); $i++)
				{
					if( !is_numeric($keys[$i]) )
						$xmldata.='	 <'.$keys[$i].'><![CDATA['.$row[$keys[$i]].']]></'.$keys[$i].'>'."\n";
				}
				$xmldata.="	</Shop_Shop>\n";
			}
		}
	}

	echo "<Get_Shop_ShopsResponse>\n";
	echo "<Ack>Success</Ack>\n";
	echo $xmldata;
	echo "</Get_Shop_ShopsResponse>";

?>

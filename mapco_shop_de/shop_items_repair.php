<?php
	include("config.php");

	function get_menuitem_id($artnr)
	{
		global $dbshop;
		$results=q("SELECT menuitem_id FROM t_200 AS a, shop_menuitems_artgr AS b WHERE a.ArtNr='".$artnr."' AND a.ARTGR=b.artgr;", $dbshop, __FILE__, __LINE__);
		$row=mysqli_fetch_array($results);
		if ($row["menuitem_id"]=="") $row["menuitem_id"]=0;
		return($row["menuitem_id"]);
	}


//Alle Artikel auslesen
$results=q("SELECT * FROM shop_items;", $dbshop, __FILE__, __LINE__);
while($row=mysqli_fetch_array($results))
{	
	$ziel[$row["MPN"]]=$row["id_item"];
	$shop[$row["MPN"]]=$row["MPN"];
	$shop_id[$row["MPN"]]=$row["id_item"];
	$shop_active[$row["MPN"]]=$row["active"];
}

//Neue Artikel bestimmen
$k=0;
$count=0;
foreach($shop as $item)
{
	$exists=false;
	if ($ziel[$item]==$shop_id[$item]) $exists=true;
	if (!$exists)
	{
		$results=q("SELECT * FROM t_200 WHERE ArtNr='".$item."' LIMIT 1;", $dbshop, __FILE__, __LINE__);
		if (mysqli_num_rows($results)>0)
		{	
			$row=mysqli_fetch_array($results);
			$collateral=$row["ATWERT"];
		}
		else
		{
			$collateral=0;
		}
		$k++;

		$results2=q("SELECT * FROM shop_items WHERE id_item=".$shop_id[$item].";", $dbshop, __FILE__, __LINE__);
		if (mysqli_num_rows($results2)>0)
		{
			$id='vorhanden';
		}
		else 
		{
			$id='gueltig';
		}

		
		
		echo $k.' / '.$shop_id[$item].' / '.$id.' / '.$item.' / '.$collateral.' / '.get_menuitem_id($item).'<br />';
//		q("INSERT INTO shop_items (id_item, Brand, MPN, collateral, price, menuitem_id, firstmod, lastmod) VALUES(".$shop_id[$item].", 'MAPCO Autotechnik GmbH', '".$item."', ".$collateral.", '0', '".get_menuitem_id($item)."', '".time()."', '".time()."');", $dbshop, __FILE__, __LINE__);	
	}
	else $count++;
}
echo $count.' Artikel stimmen!';

?>
<?php

	include("../modules/cms_translations.php");
	include("../functions/mapco_gewerblich.php");
	include("../functions/shop_get_prices.php");
	include("../functions/cms_t.php");
	include("../functions/cms_url_encode.php");

	//update cart
	$total=0;
	$amount=0;
	$price=array();
	if ( isset($_SESSION["id_user"]) and $_SESSION["id_user"]>0)
		$query="SELECT * FROM shop_carts WHERE shop_id=".$_SESSION["id_shop"]." AND user_id='".$_SESSION["id_user"]."';";
	else
		$query="SELECT * FROM shop_carts WHERE shop_id=".$_SESSION["id_shop"]." AND session_id='".session_id()."';";
	$results=q($query, $dbshop, __FILE__, __LINE__);
	while($row=mysqli_fetch_array($results))
	{
		$price=get_prices($row["item_id"], $row["amount"]);
		$amount+=$row["amount"];
		if ($price["total"]>9900) $price["total"]=0;
		$total+=$price["total"]*$row["amount"];
		$total+=$price["collateral_total"]*$row["amount"];
	}
	//***********Herbstaktion 2013**************
	if( isset($_SESSION["id_user"]) and $_SESSION["id_site"]==1 )
	{
		$results2=q("SELECT * FROM shop_user_deposit WHERE user_id=".$_SESSION["id_user"].";", $dbshop, __FILE__, __LINE__);
		if(mysqli_num_rows($results2)>0)
		{
			$row2=mysqli_fetch_array($results2);
			if($total>=$row2["deposit"])
			{
				$total=$total-$row2["deposit"];
				$deposit=$row2["deposit"];
			}
			else
			{
				$deposit=$total;
				$total=0;
			}
		}
	}
	//***********Ende Herbstakton***************

	$results=q("SELECT * FROM cms_sites WHERE id_site=".$_SESSION["id_site"].";", $dbweb, __FILE__, __LINE__);
	$row_sites=mysqli_fetch_array($results);
	if($row["ssl"]==1) $path=str_replace("http:", "https:", PATHLANG);
	else $path=PATHLANG;

	echo '<a class="cart_items" href="'.$path.'online-shop/kasse/" title="'.t("Weiter zur Kasse").'">'.$amount.' '.t("Artikel").'</a>';
	echo '<a class="cart_price" href="'.$path.'online-shop/kasse/" title="'.t("Weiter zur Kasse").'">'.number_format($total, 2).' €</a>';
//	echo '<a class="button" style="margin:5px 0px 0px 5px; float:left;" href="'.str_replace("http:", "https:", PATHLANG).'online-shop/kasse/">'.t("Weiter zur Kasse").'</a>';
	if (mysqli_num_rows($results)>0)
	{
		echo '<ul><li>';
		echo '<table class="hover">';
		echo '<tr>';
		echo '	<th>'.t("Menge").'</th>';
		echo '	<th>'.t("Artikel").'</th>';
		echo '	<th>'.t("Preis").'</th>';
		echo '</tr>';
		if ($_SESSION["id_user"]>0)
			$query="SELECT * FROM shop_carts AS a, shop_items AS b, shop_items_".$_SESSION["lang"]." AS c WHERE a.shop_id=".$_SESSION["id_shop"]." AND  a.user_id='".$_SESSION["id_user"]."' AND item_id=b.id_item AND b.id_item=c.id_item;";
		else
			$query="SELECT * FROM shop_carts AS a, shop_items AS b, shop_items_".$_SESSION["lang"]." AS c WHERE a.shop_id=".$_SESSION["id_shop"]." AND a.session_id='".session_id()."' AND item_id=b.id_item AND b.id_item=c.id_item;";
		$total=0;
		$collateral_count=0;
		$collateral_sum=0;
		$price2=array();
		$results=q($query, $dbshop, __FILE__, __LINE__);
		while($row=mysqli_fetch_array($results))
		{			
			$price2=get_prices($row["id_item"], $row["amount"]);
			echo '<tr>';
			echo '	<td>'.$row["amount"].'</td>';
			echo '	<td><a href="'.PATHLANG.'online-shop/autoteile/'.$row["id_item"].'/'.url_encode($row["title"]).'">'.$row["title"].'</a>';
			if($price2["collateral_total"]>0) 
			{
				echo '<br clear="both" />zzgl. '.number_format($price2["collateral_total"], 2).'€ Altteilpfand';
				$collateral_count=$collateral_count+$row["amount"];
				$collateral_sum=$collateral_sum+(number_format($price2["collateral_total"]*$row["amount"], 2));
			}
			echo '  </td>';
			if ($price2["total"]>9900) $price2["total"]=0;
			echo '	<td style="text-align:right;">€ '.number_format($price2["total"]*$row["amount"], 2).'</td>';
			echo '</tr>';
			$total+=$price2["total"]*$row["amount"];
		}
		//***************Herbstaktion 2013*********************
		if(isset($_SESSION["id_user"]))
		{
			if($deposit>0)
			{
				echo '  <tr>';
				echo '    <td colspan="2">';
				echo ' Gutschrift aus Rabattaktion';
				echo '   <td style="text-align:right;">€ -'.number_format($deposit, 2).'</td>';
				echo '  </tr>';
				$total-=$deposit;
			}
		}
		//***************Ende Herbstaktion*********************
		if($collateral_count>0 and $collateral_sum>0)
		{
			echo '  <tr>';
			echo '    <td colspan="2">';
			echo ' Altteilpfand für '.$collateral_count.' Artikel';
			echo '   <td style="text-align:right;">€ '.number_format($collateral_sum, 2).'</td>';
			echo '  </tr>';
			$total+=$collateral_sum;
		}
		
		//Umsatzsteuer
		if (!gewerblich($_SESSION["id_user"]))
		{
			echo '<tr>';
			echo '	<td colspan="2">gesetzliche Umsatzsteuer '.UST.'%</td>';
			echo '<td style="text-align:right;">€ '.number_format($total/(100+UST)*UST, 2).'</td>';
			echo '</tr>';
		}
		
		echo '<tr>';
		echo '	<td colspan="2" style="font-weight:bold;">'.t("Gesamt").'</td>';
		echo '	<td style="font-weight:bold; text-align:right;">€ '.number_format($total, 2).'</td>';
		echo '</tr>';
		echo '</table>';

		echo '</li></ul>';
	}

?>
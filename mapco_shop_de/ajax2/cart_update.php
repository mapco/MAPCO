<?php
	exit;
	if ( !isset($_SESSION) ) session_start();
	if (!isset($_GET["lang"]) or $_GET["lang"]=="undefined") $_GET["lang"]="de";
	//äöü
	if (!isset($skip ) or !$skip)
	{
		include_once("../config.php");
		include_once("../modules/cms_translations.php");
		include("../functions/mapco_gewerblich.php");
		include("../functions/shop_get_prices.php");
		include("../functions/cms_t.php");
		include("../functions/cms_url_encode.php");
	}
	else
	{
		include("functions/mapco_gewerblich.php");
		include("functions/shop_get_prices.php");
		include("functions/cms_t.php");
		include("functions/cms_url_encode.php");
	}
	
	//Gewerbskunde?
	/*
	$gewerblich=false;
	if ($_SESSION["id_user"]>0)
	{
		$query="SELECT * FROM cms_users WHERE id_user=".$_SESSION["id_user"].";";
		$results=q($query, $dbweb, __FILE__, __LINE__);
		$row=mysqli_fetch_array($results);
		$query="SELECT * FROM kunde WHERE ADR_ID='".$row["idims_adr_id"]."';";
		$results=q($query, $dbshop, __FILE__, __LINE__);
		if (mysqli_num_rows($results)>0) $gewerblich=true;
	}
	*/
	
	//update cart
	$total=0;
	$amount=0;
	$price=array();
	if ( isset($_SESSION["id_user"]) and $_SESSION["id_user"]>0)
		$query="SELECT * FROM shop_carts WHERE user_id='".$_SESSION["id_user"]."';";
	else
		$query="SELECT * FROM shop_carts WHERE session_id='".session_id()."';";
	$results=q($query, $dbshop, __FILE__, __LINE__);
	while($row=mysqli_fetch_array($results))
	{
		$price=get_prices($row["item_id"], $row["amount"]);
		$amount+=$row["amount"];
		$total+=$price["total"]*$row["amount"];
	}
	echo '<a href="'.str_replace("http:", "https:", PATHLANG).'online-shop/kasse/" style="margin:25px 0px 0px 57px; display:inline; float:left;">'.$amount.' '.t("Artikel").' <br /> '.number_format($total, 2).' €</a>';
	echo '<a class="button" style="margin:5px 0px 0px 5px; float:left;" href="'.str_replace("http:", "https:", PATHLANG).'online-shop/kasse/">'.t("Weiter zur Kasse").'</a>';
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
			$query="SELECT * FROM shop_carts AS a, shop_items AS b, shop_items_".$_GET["lang"]." AS c WHERE a.user_id='".$_SESSION["id_user"]."' AND item_id=b.id_item AND b.id_item=c.id_item;";
		else
			$query="SELECT * FROM shop_carts AS a, shop_items AS b, shop_items_".$_GET["lang"]." AS c WHERE a.session_id='".session_id()."' AND item_id=b.id_item AND b.id_item=c.id_item;";
		$total=0;
		$price2=array();
		$results=q($query, $dbshop, __FILE__, __LINE__);
		while($row=mysqli_fetch_array($results))
		{
			$price2=get_prices($row["id_item"], $row["amount"]);
			echo '<tr>';
//			echo '	<td><input type="text" size="2" value="'.$row["amount"].'"></td>';
			echo '	<td>'.$row["amount"].'</td>';
			echo '	<td><a href="'.PATHLANG.'online-shop/autoteile/'.$row["id_item"].'/'.url_encode($row["title"]).'">'.$row["title"].'</a></td>';
//			echo '	<td><a href="'.PATH.'shop_item.php?id_item='.$row["id_item"].'"&lang='.$_GET["lang"].'>'.$row["title"].'</a></td>';
			echo '	<td style="text-align:right;">€ '.number_format($price2["total"]*$row["amount"], 2).'</td>';
			echo '</tr>';
			$total+=$price2["total"]*$row["amount"];
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
/*
		echo '<input style="display:inline; float:left;" type="button" value="'.WARENKORB_LEEREN.'" onclick="cart_clear();" />';
		echo '<form action="shop_cart.php" method="get">';
		echo '<input style="display:inline; float:right;" type="submit" value="'.ZUR_KASSE.'" />';
		echo '<input type="hidden" name="lang" value="'.$_GET["lang"].'" />';
		echo '</form>';
*/

		echo '</li></ul>';
	}
?>
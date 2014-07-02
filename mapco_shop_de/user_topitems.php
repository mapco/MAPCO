<?php
	include("config.php");
	$login_required=true;
	include("templates/".TEMPLATE."/header.php");
	include("templates/".TEMPLATE."/cms_leftcolumn.php");
	include("functions/cms_url_encode.php");

	echo '<div id="mid_column">';
	
	//PATH
	echo '<p>';
	echo '<a href="'.PATHLANG.'online-shop/mein-konto/">'.t("Mein Konto").'</a>';
	echo ' > '.t("Meine Top-Artikel");
	echo '</p>';


	//ANALYZE
	$results=q("SELECT * FROM shop_orders WHERE customer_id=".$_SESSION["id_user"].";", $dbshop, __FILE__, __LINE__);
	while($row=mysqli_fetch_array($results))
	{
		$results2=q("SELECT * FROM shop_orders_items WHERE order_id=".$row["id_order"].";", $dbshop, __FILE__, __LINE__);
		while($row2=mysqli_fetch_array($results2))
		{
			$topitems_id[$row2["item_id"]]=$row2["item_id"];
			$topitems_amount[$row2["item_id"]]+=$row2["amount"];
			$results3=q("SELECT * FROM shop_items_".$_GET["lang"]." WHERE id_item=".$row2["item_id"].";", $dbshop, __FILE__, __LINE__);
			$row3=mysqli_fetch_array($results3);
			$topitems_title[$row2["item_id"]]=$row3["title"];
		}
	}
	if (sizeof($topitems_amount)>0)	array_multisort($topitems_amount, SORT_DESC, $topitems_id, $topitems_title);
	
	//VIEW
	echo '<h1>Meine Top-Artikel</h1>';
	echo '<table class="hover">';
	echo '	<tr>';
	echo '		<th>Nr.</th>';
	echo '		<th>Artikel</th>';
	echo '		<th>Menge</th>';
	echo '	</tr>';
	for($i=0; $i<sizeof($topitems_amount); $i++)
	{
		echo '<tr>';
		echo '	<td>'.($i+1).'</td>';
		echo '	<td><a href="'.PATHLANG.'online-shop/autoteile/'.$topitems_id[$i].'/'.url_encode($topitems_title[$i]).'">'.$topitems_title[$i].'</a></td>';
		echo '	<td><input style="width:30px;" type="text" name="" value="'.$topitems_amount[$i].'" /></td>';
		echo '<tr>';
	}
	echo '</table>';
	
	echo '</div>';

	include("templates/".TEMPLATE."/cms_rightcolumn.php");
	include("templates/".TEMPLATE."/footer.php");
?>
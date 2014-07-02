<?php
	$from=strtotime($_POST["from"]);
	$to=strtotime($_POST["to"])+(3600*24)-1;
	$comp_from=strtotime($_POST["comp_from"]);
	$comp_to=strtotime($_POST["comp_to"])+(3600*24)-1;
	//$to=strtotime($_POST["to"])+(3600*24)-1;

	if(isset($_POST["Notnull"]) && $_POST["Notnull"]>0) {$notnull=true;} else {$notnull=false;}
	if(isset($_POST["comp_from"]) && $_POST["comp_from"]>0 && isset($_POST["comp_to"]) && $_POST["comp_to"]>0) {$comparison=true;} else {$comparison=false;}
	
//ARTIKELGRUPPEN AUSLESEN
	$res=q("SELECT * FROM cms_menuitems where menu_id ='5' order by title", $dbweb, __FILE__, __LINE__);
	$i=0;
	$j=0;
	while ($row=mysqli_fetch_array($res)) {
		//HAUPTGRUPPEN
		if ($row["menuitem_id"]=="0")
		{
			$main_group[$row["id_menuitem"]]=str_replace("_", " ", $row["title"]); 
		}
		//UNTERGRUPPEN
		else
		{
			
			$sub_group[$row["id_menuitem"]]=$row["menuitem_id"];
			$group_desc[$row["id_menuitem"]]=$row["title"];
		}
	}
	
	//ARTIKELBEZEICHNUNGEN AUSLESEN
	$item_data=array();
	$item_category=array();
	$res_data=q("select a.id_item, a. title, b.MPN from shop_items_de a, shop_items b where a.id_item=b.id_item;", $dbshop, __FILE__, __LINE__);
	while ($row_data=mysqli_fetch_array($res_data)) {
		$item_data[$row_data["MPN"]]=$row_data["title"];
	//	$item_category[$row_data["MPN"]]=$row_data["category_id"];
	}


//VERKAUFSADTEN EINLESEN
	$res=q("select a.MPN, a.menuitem_id, c.TransactionID, c.account_id, c.TransactionPrice, c.QuantityPurchased 
	from shop_items a, ebay_orders_items c
where a.MPN = c.ItemSKU AND CreatedDateTimestamp>='".$from."' AND CreatedDateTimestamp<='".$to."';", $dbshop, __FILE__, __LINE__);
							
$SUM_group_qnt=array();
$SUM_sub_qnt=array();
$SUM_item_qnt=array();
$item_group=array();
$SUM_group_total=array();
$SUM_sub_total=array();
$SUM_item_total=array();

while ($row=mysqli_fetch_array($res)){
	if (!isset($SUM_group_qnt[$sub_group[$row["menuitem_id"]]])) {$SUM_group_qnt[$sub_group[$row["menuitem_id"]]]=0; $SUM_group_total[$sub_group[$row["menuitem_id"]]]=0;}
	$qnt=number_format($row["QuantityPurchased"]);
	$SUM_group_qnt[$sub_group[$row["menuitem_id"]]]+=$qnt;
	//$SUM_group_total[$sub_group[$row["menuitem_id"]]]+=number_format($row["TransactionPrice"],2)*$qnt;
	$SUM_group_total[$sub_group[$row["menuitem_id"]]]+=$row["TransactionPrice"]*$qnt;

	if (!isset($SUM_sub_qnt[$row["menuitem_id"]])) {$SUM_sub_qnt[$row["menuitem_id"]]=0; $SUM_sub_total[$row["menuitem_id"]]=0;}
	$qnt=number_format($row["QuantityPurchased"]);
	$SUM_sub_qnt[$row["menuitem_id"]]+=$qnt;
	//$SUM_sub_total[$row["menuitem_id"]]+=number_format($row["TransactionPrice"],2)*$qnt;
	$SUM_sub_total[$row["menuitem_id"]]+=$row["TransactionPrice"]*$qnt;
	
	if (!isset($SUM_item_qnt[$row["MPN"]])) {$SUM_item_qnt[$row["MPN"]]=0; $SUM_item_total[$row["MPN"]]=0;}
	$qnt=number_format($row["QuantityPurchased"]);
	$SUM_item_qnt[$row["MPN"]]+=$qnt;
	//$SUM_item_total[$row["MPN"]]+=number_format($row["TransactionPrice"],2)*$qnt;
	$SUM_item_total[$row["MPN"]]+=$row["TransactionPrice"]*$qnt;

	$item_group[$row["MPN"]]=$row["menuitem_id"];

	}
//VERGLEICGSZEITRAUM
//VERKAUFSADTEN EINLESEN
	$res=q("select a.MPN, a.menuitem_id, c.TransactionID, c.account_id, c.TransactionPrice, c.QuantityPurchased 
	from shop_items a, ebay_orders_items c
where a.MPN = c.ItemSKU AND CreatedDateTimestamp>='".$comp_from."' AND CreatedDateTimestamp<='".$comp_to."';", $dbshop, __FILE__, __LINE__);

$comp_SUM_group_qnt=array();
$comp_SUM_sub_qnt=array();
$comp_SUM_item_qnt=array();
$comp_SUM_group_total=array();
$comp_SUM_sub_total=array();
$comp_SUM_item_total=array();

while ($row=mysqli_fetch_array($res)){
	if (!isset($comp_SUM_group_qnt[$sub_group[$row["menuitem_id"]]])) {$comp_SUM_group_qnt[$sub_group[$row["menuitem_id"]]]=0; $comp_SUM_group_total[$sub_group[$row["menuitem_id"]]]=0;}
	$qnt=number_format($row["QuantityPurchased"]);
	$comp_SUM_group_qnt[$sub_group[$row["menuitem_id"]]]+=$qnt;
	//$comp_SUM_group_total[$sub_group[$row["menuitem_id"]]]+=number_format($row["TransactionPrice"],2)*$qnt;
	$comp_SUM_group_total[$sub_group[$row["menuitem_id"]]]+=$row["TransactionPrice"]*$qnt;

	if (!isset($comp_SUM_sub_qnt[$row["menuitem_id"]])) {$comp_SUM_sub_qnt[$row["menuitem_id"]]=0; $comp_SUM_sub_total[$row["menuitem_id"]]=0;}
	$qnt=number_format($row["QuantityPurchased"]);
	$comp_SUM_sub_qnt[$row["menuitem_id"]]+=$qnt;
	//$comp_SUM_sub_total[$row["menuitem_id"]]+=number_format($row["TransactionPrice"],2)*$qnt;
	$comp_SUM_sub_total[$row["menuitem_id"]]+=$row["TransactionPrice"]*$qnt;
	
	if (!isset($comp_SUM_item_qnt[$row["MPN"]])) {$comp_SUM_item_qnt[$row["MPN"]]=0; $comp_SUM_item_total[$row["MPN"]]=0;}
	$qnt=number_format($row["QuantityPurchased"]);
	$comp_SUM_item_qnt[$row["MPN"]]+=$qnt;
	//$comp_SUM_item_total[$row["MPN"]]+=number_format($row["TransactionPrice"],2)*$qnt;
	$comp_SUM_item_total[$row["MPN"]]+=$row["TransactionPrice"]*$qnt;

	$item_group[$row["MPN"]]=$row["menuitem_id"];

	}


echo '<table>';
echo '<colgroup><col width="300px"><col width="100px"><col width="150px">';
if ($comparison) {echo '<col width="100px"><col width="150px">';}
echo '</colgroup>';
echo '<th></th><th colspan="2" style="text-align:center;">'.date("d.m.Y",$from).' - '.date("d.m.Y",$to-1).'</th>';
if($comparison)	{ echo '<th colspan="2" style="text-align:center;">'.date("d.m.Y",$comp_from).' - '.date("d.m.Y",$comp_to-1).'</th>';}

while (list($key, $val) = each ($main_group))
//while (list($key, $val) = each ($group_desc))
{
	echo '<tr onclick="show_sub_group('.$key.');" style="background-color:#ddd;">';
	echo '	<td>'.$val.'</td>';
	if (isset($SUM_group_qnt[$key])) {
		echo '	<td style="text-align:right;"><b>'.number_format($SUM_group_qnt[$key]).'</b></td>';}
	else {echo '<td style="text-align:right;"><b>0</b></td>';}
	if (isset($SUM_group_total[$key])) {
		echo '	<td style="text-align:right;"><b>'.number_format($SUM_group_total[$key],2,",",".").' €</b></td>';}
	else {echo '<td style="text-align:right;"><b>0 €</b></td>';}
	if ($comparison) 
	{
		if (isset($comp_SUM_group_qnt[$key])) { echo '	<td style="text-align:right;"><b>'.number_format($comp_SUM_group_qnt[$key]).'</b></td>';}
		else { echo '	<td style="text-align:right;"><b>0</b></td>';}
	}
	if ($comparison)
	{
		if (isset($comp_SUM_group_total[$key])) { echo '	<td style="text-align:right;"><b>'.number_format($comp_SUM_group_total[$key],2,",",".").' €</b></td>';}
		else { echo '	<td style="text-align:right;"><b>0 €</b></td>';}
	}
	echo '</tr>';
	
	$dump=reset($sub_group);
	while (list($key_group, $group_id) = each ($sub_group))
	{
		if ($key==$group_id) {
			echo '<tr class="sub_group sub_group'.$group_id.'" style="display:none; background-color:#eee" onclick="show_sub_items('.$key_group.');">';
			echo '	<td>&nbsp;&nbsp;&nbsp;&nbsp;'.$group_desc[$key_group].'</td>';
			if (isset($SUM_sub_qnt[$key_group])) {
				echo '	<td style="text-align:right;"><b>'.number_format($SUM_sub_qnt[$key_group]).'</b></td>';
				echo '	<td style="text-align:right;"><b>'.number_format($SUM_sub_total[$key_group],2,",",".").' €</b></td>';
			}
			else 
			{	
				echo '<td style="text-align:right;"><b>0</b></td>';
				echo '<td style="text-align:right;"><b>0 €</b></td>';
			}
			if ($comparison) {
				if (isset($comp_SUM_sub_qnt[$key_group])) {echo '	<td style="text-align:right;"><b>'.$comp_SUM_sub_qnt[$key_group].'</b></td>';}
				else { echo '	<td  style="text-align:right;"><b>0</b></td>';}
			}
			if ($comparison) {
				if (isset($comp_SUM_sub_total[$key_group])) {echo '	<td style="text-align:right;"><b>'.number_format($comp_SUM_sub_total[$key_group],2,",",".").' €</b></td>';}
				else { echo '	<td  style="text-align:right;"><b>0 €</b></td>';}
			}
			echo '</tr>';
			
			$dump=reset($item_group);
		
			while (list($MPN, $key_i_group) = each ($item_group))
			{
				if ($key_i_group==$key_group) 
				{
					echo '<tr class="sub_items sub_items'.$key_i_group.'" style="display:none">';
					echo '<td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$item_data[$MPN].'</td>';
					if (isset($SUM_item_qnt[$MPN])) {
						echo '<td style="text-align:right;">'.number_format($SUM_item_qnt[$MPN]).'</td>';
						echo '<td style="text-align:right;">'.number_format($SUM_item_total[$MPN],2,",",".").' €</td>';
					}
					else {
						echo '<td style="text-align:right;"><b>0</b></td>';
						echo '<td style="text-align:right;"><b>0 €</b></td>';
					}
					if ($comparison) {
						if (isset($comp_SUM_item_qnt[$MPN])) {echo '<td style="text-align:right;">'.$comp_SUM_item_qnt[$MPN].'</td>';}
						else {echo '<td style="text-align:right;"><b>0</b></td>';}
					}
					if ($comparison) {
						if (isset($comp_SUM_item_total[$MPN])) {echo '<td style="text-align:right;">'.number_format($comp_SUM_item_total[$MPN],2,",",".").' €</td>';}
						else {	echo '<td style="text-align:right;"><b>0 €</b></td>';}
					}

					echo '</tr>';
				}
			}
		}
	
	}
	
}
echo '</table>';
?>

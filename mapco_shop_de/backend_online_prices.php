<?php
	include("config.php");
	include("templates/".TEMPLATE_BACKEND."/header.php");
?>

<?php

	//PATH
	echo '<p>';
	echo '<a href="backend_index.php?lang='.$_GET["lang"].'">'.t("Backend").'</a>';
	echo ' > <a href="backend_administration_index.php?lang='.$_GET["lang"].'">'.t("Administration").'</a>';
	echo ' > Online Preise';
	echo '</p>';
	
	echo '<h1>Aktuelle Online Sonderpreise</h1>';

	//get all yellow Prices
	$yellow_price=array();
	$results=q("SELECT ARTNR, POS_0_WERT FROM prpos WHERE LST_NR=5;", $dbshop, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		$yellow_price[$row["ARTNR"]]=$row["POS_0_WERT"];
	}
	
	//get all ebay special Prices
	$ebay_price=array();
	$results=q("SELECT ARTNR, POS_0_WERT FROM prpos WHERE LST_NR=16815 AND ARTNR NOT LIKE '%FRACHT%';", $dbshop, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		if (!isset($yellow_price[$row["ARTNR"]]))
		{
			$ebay_price[$row["ARTNR"]]=$row["POS_0_WERT"];
			$ebay_artnr[]=$row["ARTNR"];
		}
//		elseif ($row["POS_0_WERT"]<($yellow_price[$row["ARTNR"]]-0.30)) 	//Yellow - 30 Cent
		elseif ($row["POS_0_WERT"]<$yellow_price[$row["ARTNR"]]) 
		{
			$ebay_price[$row["ARTNR"]]=$row["POS_0_WERT"];
			$ebay_artnr[]=$row["ARTNR"];
		}
	}


	sort($ebay_artnr, SORT_NUMERIC);
	echo '<table class="hover" style="float:left;">'."\n";
	echo '	<tr>'."\n";
	echo '		<th>Nr.</th>'."\n";
	echo '		<th>Artikel</th>'."\n";
	echo '		<th>Nettopreis</th>'."\n";
	echo '		<th>Gelber Preis</th>'."\n";
	echo '	</tr>'."\n";
	for($i=0; $i<sizeof($ebay_artnr); $i++)
	{
		echo '<tr>'."\n";
		echo '	<td>'.($i+1).'</td>'."\n";
		echo '	<td>'.$ebay_artnr[$i].'</td>'."\n";
		echo '	<td>'.$ebay_price[$ebay_artnr[$i]].'</td>'."\n";
		echo '	<td>'.$yellow_price[$ebay_artnr[$i]].'</td>'."\n";
		echo '</tr>'."\n";
	}
	echo '</table>'."\n";

	include("templates/".TEMPLATE_BACKEND."/footer.php");
?>
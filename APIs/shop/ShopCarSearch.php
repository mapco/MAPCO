<?php
	include("../functions/shop_get_prices.php");
	include("../functions/cms_t.php");
	include("../functions/mapco_hide_price.php");	
	include("../functions/cms_url_encode.php");


	echo '<table id="car_search_table" style="margin:0;">';
//	echo '<tr><th colspan="5">';
//	echo t("").'.';
//	echo '</th></tr>';
	
	//Kopfzeile
	echo '<tr>';
	echo '	<th>Fahrzeugsuche</th>';
	echo '	<th>KBA-Suche';
	echo '<img src="'.PATH.'images/icons/16x16/remove.png" style="cursor:pointer; float:right;" onclick="car_search_close();" alt="Schließen" title="Schließen" />';
	echo '</th>';
	echo '</tr>';
	echo '<tr>';
	echo '	<td>';
		//search by car
		include("../modules/shop_searchbycar.php");
	echo '  </td>';
	echo '	<td>';
		//search by kba
		include("../modules/shop_searchbykba.php");
	echo '  </td>';
	echo '</tr>';

	echo '</table>';
?>
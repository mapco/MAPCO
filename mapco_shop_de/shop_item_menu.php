<?php
	//TABS
	//http://www.mapco-leipzig.de/online-shop/autoteile/22413/hydraulikpumpe%2c+lenkung+%2827642%29
	if( $_SESSION["id_user"]==21371 )
	{
		echo '<div style="float:left;">';
		echo '	<a href="'.PATH.'ersatzteil/'.$_GET["id_item"].'/">Allgemein</a>';
		echo '	<a href="'.PATH.'ersatzteil-details/'.$_GET["id_item"].'/">Details</a>';
		echo '	<a href="'.PATH.'ersatzteil-fahrzeuge/'.$_GET["id_item"].'/">Fahrzeuge</a>';
		echo '	<a href="'.PATH.'ersatzteil-hilfe/'.$_GET["id_item"].'/">Hilfe & Support</a>';
		echo '</div>';
		echo '<br style="clear:both;" />';
	}
?>
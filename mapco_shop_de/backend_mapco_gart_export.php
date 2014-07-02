<?php
	include("config.php");
	include("templates/".TEMPLATE_BACKEND."/header.php");

	//PATH
	echo '<p>';
	echo '<a href="backend_index.php">Backend</a>';
	echo ' > <a href="backend_shop_index.php">Online-Shop</a>';
	echo ' > Artikelkategorien für eBay, YouTube und Idealo';
	echo '</p>';
?>
<script>
	function view()
	{
		var response=ajax("modules/backend_mapco_gart_export_view.php", false);
		document.getElementById("view").innerHTML=response;
	}
	function remove(id)
	{
		var response=ajax("modules/backend_mapco_gart_export_remove.php?id="+id, false);
		if (response!="") show_status(response);
		else
		{
			if (value=="") show_status("Feld erfolgreich geleert."); else show_status(value+" erfolgreich gespeichert.");
		}
		view();
	}
	function update_field(id, field, value)
	{
		var response=ajax("modules/backend_mapco_gart_export_update.php?id="+id+"&field="+field+"&value="+encodeURIComponent(value), false);
		if (response!="") show_status(response);
		else
		{
			if (value=="") show_status("Feld erfolgreich geleert."); else show_status(value+" erfolgreich gespeichert.");
		}
		view();
	}
</script>
<?php

	//VIEW
	echo '<div id="view"></div>';
	echo '<script>view();</script>';
	
	include("templates/".TEMPLATE_BACKEND."/footer.php");
?>
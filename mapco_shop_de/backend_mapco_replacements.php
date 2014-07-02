<?php
	include("config.php");
	include("templates/".TEMPLATE_BACKEND."/header.php");

	//PATH
	echo '<p>';
	echo '<a href="backend_index.php">Backend</a>';
	echo ' > <a href="backend_shop_index.php">Online-Shop</a>';
	echo ' > Ersetzungs-Editor';
	echo '</p>';
?>
<script>
	function view()
	{
		var response=ajax("modules/backend_mapco_replacements_view.php", false);
		document.getElementById("view").innerHTML=response;
	}
	function insert()
	{
		var search=document.getElementById("form_search").value;
		var replace=document.getElementById("form_replace").value;
		var response=ajax("modules/backend_mapco_replacements_insert.php?search="+encodeURIComponent(search)+"&replace="+encodeURIComponent(replace), false);
		if (response!="") show_status(response);
		else
		{
			if (value=="") show_status("Feld erfolgreich geleert."); else show_status(value+" erfolgreich gespeichert.");
		}
		view();
	}
	function remove(id)
	{
		var response=ajax("modules/backend_mapco_replacements_remove.php?id_replacement="+id, false);
		if (response!="") show_status(response);
		else
		{
			if (value=="") show_status("Feld erfolgreich geleert."); else show_status(value+" erfolgreich gespeichert.");
		}
		view();
	}
	function update_field(id, field, value)
	{
		var response=ajax("modules/backend_mapco_replacements_update.php?id_replacement="+id+"&field="+field+"&value="+encodeURIComponent(value), false);
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
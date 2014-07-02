<?php

	include("config.php");
	include("templates/".TEMPLATE_BACKEND."/header.php");
	
	include("functions/cms_t.php");

?>	

<script type="text/javascript">
	
	$(window).load(function(){
		backend_table_edit_main();
	});
	
	function backend_table_edit_main()
	{
		var main_div = $('#main_div');
		var input = $('<input id="file_input" type="file" style="width: 400px">');
		main_div.append(input);
		
		$('#file_input').change(function(){
			alert($('#file_input').val());
		});
	}

</script>

<?php	
    
	//PATH
	echo '<p>';
	echo '<a href="backend_index.php">Backend</a>';
	//echo ' > <a href="backend_interna_index.php">Interna</a>';
	echo ' > '.t("Lagerorte-Liste");
	echo '</p>';
	
	echo '<h1>'.t("Lagerorte-Liste").'</h1>';
	echo '<div id="main_div">';
	//echo '	<div id="tree_div" style="float: left;"></div>';
	//echo '	<div id="list_div" style="float: left"></div>';
	echo '</div>';
	
	include("templates/".TEMPLATE_BACKEND."/footer.php");

?>
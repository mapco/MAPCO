<?php
	include("config.php");
	include("templates/".TEMPLATE_BACKEND."/header.php");
	

?>

<script type="text/javascript">
</script>


<?

	//PATH
	echo '<p>';
	echo '<a href="backend_index.php">Backend</a>';
	echo ' > <a href="backend_shop_index.php">Online-Shop</a>';
	echo ' > Fehlende Artikel';
	echo '</p>';
	echo '<h1>Fehlende Artikel</h1>';
	
	
	include("templates/".TEMPLATE_BACKEND."/footer.php");
?>
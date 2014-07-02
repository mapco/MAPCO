<?php
	include("config.php");
	include("templates/".TEMPLATE."/header.php");
	include("templates/".TEMPLATE."/cms_leftcolumn.php");

	echo '<div id="mid_right_column">';
	echo '<form method="post">';
	echo '<table>';
	echo '	<tr>';
	echo '		<td><input type="text" name="form_search" value="" /></td>';
	echo '	</tr>';
	echo '</table>';
	echo '</form>';
	echo '</div>';
	
	include("templates/".TEMPLATE."/footer.php");
?>
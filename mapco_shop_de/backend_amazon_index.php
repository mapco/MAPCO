<?php
include("config.php");
$login_required = true;
include("templates/".TEMPLATE_BACKEND."/header.php");

echo '<div id="breadcrumbs" class="breadcrumbs">';
echo '	<a href="backend_index.php">Backend</a>';
echo ' 	&#187 <a href="">Amazon</a>';
echo '</div>';
echo '<h1></h1>';

echo '<h1>Amazon</h1>';
echo '<ul class="quickaccess">';
show_tree(178, true);
echo '</ul>';

include("templates/".TEMPLATE_BACKEND."/footer.php");

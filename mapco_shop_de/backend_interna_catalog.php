<?php

//	Header
include("config.php");
include("templates/" . TEMPLATE_BACKEND . "/header.php");

//	Content

//	Breadcrumbs
echo '
	<div id="breadcrumbs" class="breadcrumbs">
		<a href="backend_index.php">Backend</a>
		&#187; <a href="">Catalog</a>
		&#187; Dashboard
	</div>
	<h1>Catalog - Dashboard</h1>';

//	Footer
include("templates/" . TEMPLATE_BACKEND . "/footer.php");
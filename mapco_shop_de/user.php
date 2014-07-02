<?php
	include("config.php");
	$login_required=true;
	include("templates/".TEMPLATE."/header.php");

	$results=q("SELECT * FROM fa_user_login WHERE id='".$_SESSION["user_id"]."';", $dbshop, __FILE__, __LINE__);
	$row=mysqli_fetch_array($results);

	include("templates/".TEMPLATE."/footer.php");
?>
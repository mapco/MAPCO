<?php
	include("templates/".TEMPLATE."/header.php");

	//NEWSLETTER UNSUBSCRIBE
	if ( isset($_GET["getvars1"]) and $_GET["getvars1"]!="" )
	{
		q("UPDATE cms_users SET newsletter=0 WHERE usermail='".$_GET["getvars1"]."' ;", $dbweb, __FILE__, __LINE__);
		echo '<div class="success">'.t("Ihre E-Mail Adresse wurde aus unserem Verteiler gelöscht.").'</div>';
	}

	include("templates/".TEMPLATE."/footer.php");

?>
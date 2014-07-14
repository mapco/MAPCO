<?php
	include("config.php");
	$login_required=true;
	include("templates/".TEMPLATE."/header.php");
	include("templates/".TEMPLATE."/cms_leftcolumn.php");

	echo '<div id="mid_column">';
	
	//PATH
	echo '<p>';
	echo '<a href="'.PATHLANG.tl(301, "alias").'" title="'.tl(301, "description").'">'.tl(301, "title").'</a>';
	echo ' >';
	echo '</p>';

	echo '<h1>'.t("Mein Konto").'</h1>';
	show_tree(301);
/*	
	echo '	<a href="'.PATHLANG.'online-shop/mein-konto/benutzerkonto/" class="cp_icon">';
	echo '		<img src="'.PATH.'images/icons/128x128/user.png" alt="'.t("Benutzerkonto").'" title="'.t("Benutzerkonto").'" />';
	echo '		<br />'.t("Benutzerkonto");
	echo '	</a>';
	
	echo '	<a href="'.PATHLANG.'online-shop/mein-konto/bestellungen/" class="cp_icon">';
	echo '		<img src="'.PATH.'images/icons/128x128/shopping_cart_accept.png" alt="'.t("Bestellungen").'" title="'.t("Bestellungen").'" />';
	echo '		<br />'.t("Bestellungen");
	echo '	</a>';

	echo '	<a href="'.PATHLANG.'online-shop/mein-konto/fuhrpark/" class="cp_icon">';
	echo '		<img src="'.PATH.'images/icons/128x128/home.png" alt="'.t("Fuhrpark").'" title="'.t("Fuhrpark").'" />';
	echo '		<br />'.t("Fuhrpark");
	echo '	</a>';

	echo '	<a href="'.PATHLANG.'online-shop/mein-konto/meine-listen/" class="cp_icon">';
	echo '		<img src="'.PATH.'images/icons/128x128/notes_edit.png" alt="'.t("Top-Artikel").'" title="'.t("Top-Artikel").'" />';
	echo '		<br />'.t("Listen");
	echo '	</a>';

	echo '	<a href="'.PATHLANG.'online-shop/mein-konto/auftragsimport/" class="cp_icon">';
	echo '		<img src="'.PATH.'images/icons/128x128/application_add.png" alt="'.t("Auftragsimport").'" title="'.t("Auftragsimport").'" />';
	echo '		<br />'.t("Auftragsimport");
	echo '	</a>';

	//print_r($_SESSION["id_user"]);
	if ( function_exists("gewerblich") and gewerblich($_SESSION["id_user"]) or $_SESSION["id_user"]==21371 )
	{
		echo '	<a href="'.PATH.'mapco_images.zip" class="cp_icon">';
		echo '		<img src="'.PATH.'images/icons/128x128/image.png" alt="'.t("Bilderdownload").'" title="'.t("Bilderdownload").'" />';
		echo '		<br />'.t("Bilderdownload");
		echo '	</a>';
	}

/*
	echo '	<a href="shop_user_leaflet.php" class="cp_icon">';
	echo '		<img src="images/icons/128x128/notes_edit.png" alt="Merkzettel" title="Merktzettel" />';
	echo '		<br />Merkzettel';
	echo '	</a>';
*/
	echo '</div>';

	include("templates/".TEMPLATE."/cms_rightcolumn.php");
	include("templates/".TEMPLATE."/footer.php");
?>

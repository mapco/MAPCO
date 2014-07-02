<?php
include("../../mapco_shop_de/functions/cms_t.php");
?>

<script type="text/javascript">

	function show_CatSub(id_menuitem) {

		$(".allsub").hide();
		$(".aCatSub"+id_menuitem).show();
		
		$(".aCatMain").css("background-color", "#d0d0d0");
		$("#aCatMain"+id_menuitem).css("background-color", "#e3e3e3");
		
		
	}
	function show_random_pic(menuitem_id, initial) {
		
		if ($("#submenu"+menuitem_id).is(":hover") || initial==true) {
		//$.post("<?php echo PATH; ?>soa/", {API: "shop", Action: "CategoryGetImagePath", usertoken: "merci2664", menuitem_id:menuitem_id},
		$.post("<?php echo PATH; ?>soa/", {API: "shop", Action: "CategoryGetImagePath", menuitem_id:menuitem_id},
	
			function (data) {
				if (data!="") {
					$("#category_pic").html(data);
					};
				}
			);
		}

	}
	
	function pic_fadeout(menuitem_id, initial)
	{
		setTimeout(function() {
			if ($("#submenu"+menuitem_id).is(":hover") || initial) {
				$("#category_pic").fadeOut(250, "linear", 
					function() {
						//$("#category_pic").fadeOut(100);
						show_random_pic(menuitem_id, initial);
					}
				);
			}
		}, 400);

	}
	
	function pic_fadein()
	{
		$("#category_pic").fadeIn(250, "linear");
	}

</script>


<?php

	if (isset($_POST["idtag"]) && $_POST["idtag"]!="") 
	{

		$idtag='shopmenu';	
		//Menudaten einlesen
		$results=q("SELECT * FROM cms_menus WHERE idtag='".$idtag."';", $dbweb, __FILE__, __LINE__);
		$row=mysqli_fetch_array($results);
		$results=q("SELECT * FROM cms_menuitems WHERE menu_id=".$row["id_menu"]." ORDER BY ordering;", $dbweb, __FILE__, __LINE__);
		$i=0;
		$j=0;
		while($row=mysqli_fetch_array($results))
		{
		if ($row["menuitem_id"]=="0") {
			$menu["id_menuitem"][$i]=$row["id_menuitem"];
			$menu["icon"][$i]=$row["icon"];
			$menu["description"][$i]=$row["description"];
			$menu["title"][$i]=$row["title"];
			$menu["menuitem_id"][$i]=$row["menuitem_id"];
			$menu["link"][$i]=$row["link"];
			$i++;
		}
		else {
			$submenu["id_menuitem"][$j]=$row["id_menuitem"];
			$submenu["icon"][$j]=$row["icon"];
			$submenu["description"][$j]=$row["description"];
			$submenu["title"][$j]=$row["title"];
			$submenu["menuitem_id"][$j]=$row["menuitem_id"];
			$submenu["link"][$j]=$row["link"];
			$submenu["alias"][$j]=$row["alias"];
			$j++;
		}

	
	}
	
	//HEADER
		echo '<div class="promotionHead">';
		echo t('Shop-Kategorien');
		echo '</div>';

	//CATEGORY MAIN
		echo '<div id="category_main" style="float:left; vertical-text-align:center;">';
		for($i=0; $i<sizeof($menu["title"]); $i++)
		{
			echo '<div class="aCatMain" id="aCatMain'.$menu["id_menuitem"][$i].'" style="width:100%; height:20%; vertical-text-align:middle; padding-left:5px; cursor:pointer;" onclick="show_CatSub(\''.$menu["id_menuitem"][$i].'\');">';
			echo t($menu["title"][$i]).'</div>';
		}
		echo '</div>';
		
		//SUBMENU
		echo '<div id="category_sub" style="float:left; display:inline;">';
		echo '<ul id="cat_sub" style="list-style:disc; width:160px; height:100%; margin:0px 0px 0px 0px; float:right;">';
		for ($i=0; $i<sizeof($submenu["title"]); $i++) {
			$link=PATHLANG.$submenu["alias"][$i];
			/*if ( $submenu["alias"][$i]!="" ) {
				$link=PATHLANG.$submenu["alias"][$i];}
			else {
				$link=PATH.$submenu["link"][$i].'?lang='.$_SESSION["lang"].'&id_menuitem='.$submenu["id_menuitem"][$i]; }
			*/
			echo '<li class="aCatSub'.$submenu["menuitem_id"][$i].' allsub" style="text-align:left;"><a id="submenu'.$submenu["id_menuitem"][$i].'" style="text-align:left;" href="'.str_replace(" ", "%20", $link).'" title="'.t("Zeige alle Artikel der Kategorie").' '.t($submenu["description"][$i], __FILE__, __LINE__).'" onmouseover="pic_fadeout('.$submenu["id_menuitem"][$i].', false)";>';
			echo '<span style="text-align:left">'.t($submenu["title"][$i], __FILE__, __LINE__).'</span></a></li>';
		}
		echo '</ul>';
		echo '</div>';
		
		//KATEGORIE IMAGE
		echo '<div id="category_pic">';
		echo '</div>';

		echo '<script>show_CatSub('.$menu["id_menuitem"][0].')</script>';
		$count=0;
		while ($menu["id_menuitem"][0]!=$submenu["menuitem_id"][$count]) $count++;
		echo '<script>pic_fadeout('.$submenu["id_menuitem"][$count].', true)</script>';
	}


?>
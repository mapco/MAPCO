<?php
	$starttime=time()+microtime();
	include("config.php");
	include("functions/cms_remove_element.php");
	include("functions/mapco_get_titles.php");
	include("functions/shop_itemstatus.php");
	include("functions/shop_get_prices.php");
	include("functions/mapco_cutout.php");	
	include("functions/mapco_baujahr.php");	
	include("functions/mapco_hide_price.php");	
	include("functions/cms_url_encode.php");
	include("functions/cms_t.php");

	if ( !is_numeric($_GET["id_item"]) )
	{
		header("HTTP/1.0 404 Not Found");
		exit;
	}

	$results=q("SELECT * FROM shop_items WHERE id_item='".mysqli_real_escape_string($dbshop, $_GET["id_item"])."' AND active=1 LIMIT 1;", $dbshop, __FILE__, __LINE__);
	if ( mysqli_num_rows($results)==0 )
	{		
		include("templates/".TEMPLATE."/header.php");
		include("templates/".TEMPLATE."/cms_leftcolumn.php");
		echo '<div id="mid_right_column">';
		echo '<p>Artikel nicht gefunden.</p>';
		echo '</div>';
		include("templates/".TEMPLATE."/footer.php");
		exit;
	}
	$shop_items=mysqli_fetch_array($results);
	
	//get variable für kategorienliste
	if(!isset($_GET["id_menuitem"]))
		$_GET["id_menuitem"]=$shop_items["menuitem_id"];

	//description
	$results3=q("SELECT * FROM cms_languages WHERE code='".$_SESSION["lang"]."';", $dbweb, __FILE__, __LINE__);
	$row3=mysqli_fetch_array($results3);
	$id_language=$row3["id_language"];
	$results3=q("SELECT * FROM shop_items_descriptions WHERE GART=".$shop_items["GART"]." AND language_id=".$id_language.";", $dbshop, __FILE__, __LINE__);
	if ( mysqli_num_rows($results3)>0 ) 
	{
		$row3=mysqli_fetch_array($results3);
		$description=$row3["description"];
		$keywords=$row3["keywords"];
	}

	//keywords
	$results3=q("SELECT * FROM shop_items_keywords WHERE GART=".$shop_items["GART"]." AND language_id=".$id_language." order by ordering;", $dbshop, __FILE__, __LINE__);
	if (mysqli_num_rows($results3)>0) 
	{
		$count = mysqli_num_rows($results3)-1;
		$i=0;
		while ($row3=mysqli_fetch_array($results3))
		{
			if($i==0 and $keywords!="") $keywords .= ", ";
			$i++;
			$keywords .= $row3["keyword"];
			if($i<=$count) $keywords .= ", ";
		}
	}


	//header
	$right_column=true;
	$results=q("SELECT * FROM shop_items_".mysqli_real_escape_string($dbshop, $_GET["lang"])." WHERE id_item='".mysqli_real_escape_string($dbshop, $_GET["id_item"])."';", $dbshop, __FILE__, __LINE__);
	$shop_items_lang=mysqli_fetch_array($results);
	$title='MAPCO '.$shop_items_lang["title"].' günstig online kaufen';
	include("templates/".TEMPLATE."/header.php");

	//part compatibility
	if( isset($_SESSION["ktypnr"]) )
	{
		$fits=false;
		$parts=array();
		$results=q("SELECT * FROM vehicles_de WHERE KTypNr=".$_SESSION["ktypnr"].";", $dbshop, __FILE__, __LINE__);
		$row=mysqli_fetch_array($results);
		$id_vehicle=$row["id_vehicle"];
		$results=q("SELECT * FROM shop_items_vehicles WHERE language_id=1 AND vehicle_id=".$row["id_vehicle"].";", $dbshop, __FILE__, __LINE__);
		while( $row=mysqli_fetch_array($results) )
		{
			$parts[$row["item_id"]]=$row;
			if( $row["item_id"]==$_GET["id_item"] )
			{
				$fits=true;
			}
		}
	}
	
	$vehicle_save=0;
	
	if ( isset($fits) )
	{
		if ( $fits )
		{
			echo '<div class="success">';
			echo '	Dieses Ersatzteil passt zu Ihrem ausgewählten Fahrzeug!';
			echo '	(<a href="'.PATHLANG.'fahrzeugsuche/'.$id_vehicle.'/">'.sizeof($parts).' Ersatzteile für Ihr Fahrzeug verfügbar</a>).';
			echo '</div>';
			if($parts[$_GET["id_item"]]["criteria"]!="") echo '<div class="warning">Beachten Sie folgende Einschränkungen: '.$parts[$_GET["id_item"]]["criteria"].'</div>';
			$vehicle_save=1;
		}
		else
		{
			echo '<div class="failure">';
			echo '	Vorsicht: Dieses Ersatzteil passt NICHT zu Ihrem ausgewählten Fahrzeug!';
			echo '	(<a href="'.PATHLANG.'fahrzeugsuche/'.$id_vehicle.'/">'.sizeof($parts).' Ersatzteile für Ihr Fahrzeug verfügbar</a>).';
			echo '</div>';
		}
	}
	else
	{
		echo '<div class="warning">Bitte wählen Sie in der Fahrzeugsuche ein Fahrzeug aus, um zu erfahren, ob dieses Ersatzteil passt.</div>';
	}
	
//	include("templates/".TEMPLATE."/cms_leftcolumn.php");
	echo '<div id="left_mid_right_column">';

?>

	<style>
		#shopitem
		{
			width:988px;
			
			margin:0;
			border:0;
			border-top:1px solid #a89e95;
			border-bottom:1px solid #a89e95;
			padding:0;
			
			color:#000000;
			background:#ffffff url(<?php echo PATH; ?>images/shopitem_bg.gif) 770px repeat-y;
			
			display:inline;
			float:left;
		}
		#shopitem_content
		{
			width:770px;
			
			margin:0;
			border:0;
			padding:0;
			
			background.#ffffff;
			
			display:inline;
			float:left;
		}
	</style>
    
    <script type="text/javascript">
		function shopitem_show($tab)
		{
			$("#shopitem_description_general").hide();
			$("#shopitem_description_detailed").hide();
			$("#shopitem_description_special").hide();
			$("#shopitem_description_help").hide();
			$("#shopitem_description_"+$tab).show();
			if( $tab=="general" ) $("#shopitem_img").css("width", 400);
			else $("#shopitem_img").css("width", 200);
		}
	</script>

<?php

	//PATH
	$results2=q("SELECT alias, title FROM cms_menuitems WHERE id_menuitem='".$shop_items["menuitem_id"]."';", $dbweb, __FILE__, __LINE__);
	$row2=mysqli_fetch_array($results2);
	echo '<p>';
	echo '<a href="'.PATHLANG.'online-shop/">Online-Shop</a>';
	echo ' > <a href="'.PATHLANG.str_replace(" ", "%20", $row2["alias"]).'">'.$row2["title"].'</a>';
	echo ' > '.$shop_items_lang["title"];
	echo '</p>';
	echo '<br style="clear:both;" />';


	//TABS
	echo '<div style="width:988px; margin:0; border:0; padding:0; text-align:left; float:left;">';
	echo '	<a href="javascript:shopitem_show(\'general\');">Allgemein</a>';
	echo '	<a href="javascript:shopitem_show(\'detailed\');">Details</a>';
	echo '	<a href="javascript:shopitem_show(\'special\');">Fahrzeuge</a>';
	echo '	<a href="javascript:shopitem_show(\'help\');">Hilfe & Support</a>';
	echo '</div>';
	echo '<br style="clear:both;" />';


	//CONTENT
	echo '<div id="shopitem">';
	echo '	<div id="shopitem_content">';
	//CONTENT IMAGE
	$results_pics=q("SELECT * FROM cms_articles_images WHERE article_id=".$shop_items["article_id"]." ORDER BY ordering;", $dbweb, __FILE__, __LINE__);
	$max=mysqli_num_rows($results_pics);
	if ( $max==0 )
	{
		if( $_SESSION["id_shop"]==2 ) $src='images/library/ap_frame_noimage.jpg';
		else $src='images/library/rahmen-bild-folgt.jpg';
		echo '	<img style="width:500px; margin:5px; border:0; padding:0; float:left;" src="'.PATH.$src.'" alt="Bild folgt" title="Bild folgt" />';
	}
	else
	{
		$i=0;
		echo '<div class="highslide-gallery" style="margin:0; border:0; padding:0; float:left;">';
//		$results_pics2=q("SELECT * FROM cms_articles_images WHERE article_id='".$shop_items["article_id"]."' ORDER BY ordering;", $dbweb, __FILE__, __LINE__);
		
		while($row_pics2=mysqli_fetch_array($results_pics))
		{
			$i++;
				
			//BILDERGALERIE
			$results_pic3=q("SELECT * FROM cms_files WHERE original_id=".$row_pics2["file_id"]." AND imageformat_id=19 LIMIT 1;", $dbweb, __FILE__, __LINE__);
			$row_pic3=mysqli_fetch_array($results_pic3);
			$src='files/'.floor($row_pic3["id_file"]/1000).'/'.$row_pic3["id_file"].'.'.$row_pic3["extension"];
			if ( !file_exists($src) ) $src='images/library/rahmen-bild-folgt.jpg';
			echo '<a style="display:none;" class="highslide" href="'.PATH.$src.'" onclick="return hs.expand(this)">';
			echo '	<img style="margin:5px; border:0; padding:0; " src="'.PATH.$src.'" alt="'.$row_pic3["description"].'" title="'.$row_pic3["description"].'" />';
			echo '</a>';
			//ARTIKELBILD
			if ($i==1) 
			{
				$results55=q("SELECT * FROm shop_shops WHERE id_shop=".$_SESSION["id_shop"].";", $dbshop, __FILE__, __LINE__);
				$shop=mysqli_fetch_array($results55);
				$results_pic4=q("SELECT * FROM cms_files WHERE original_id=".$row_pics2["file_id"]." AND imageformat_id=".$shop["imageformat_id"]." LIMIT 1;", $dbweb, __FILE__, __LINE__);
				$row_pic4=mysqli_fetch_array($results_pic4);
				$src2='files/'.floor($row_pic4["id_file"]/1000).'/'.$row_pic4["id_file"].'.'.$row_pic4["extension"];
				if ( !file_exists($src2) ) $src2='images/library/rahmen-bild-folgt.jpg';
	
				$cursorpath1=PATH.'images/cursor/search.png';
				$cursorpath2=PATH.'images/cursor/search.ico';
				echo '<img id="shopitem_img" style="margin:0; border:0; padding:0; width:400px; cursor:url('.$cursorpath1.'),url('.$cursorpath2.'), pointer;" src="'.PATH.$src2.'" alt="'.$row_pic4["description"].'" title="'.$row_pic4["description"].'" onclick="return hs.expand(null, {src: \''.PATH.$src.'\'});"/>';
			}
	
		} // WHILE
		echo '</div>';
	}
	//END CONTENT IMAGE

	//CONTENT DESCRIPTION GENERAL
	echo '<div id="shopitem_description_general">';
	echo '<h1 style="width:350px; border:0; display:inline; float:left; clear:none;">'.$shop_items_lang["title"].'</h1>';
	echo '</div>';
	//CONTENT DESCRIPTION GENERAL END

	//CONTENT DESCRIPTION DETAILED
	echo '<div id="shopitem_description_detailed">';
	echo '	DETAILLIERT';
	echo '</div>';
	//CONTENT DESCRIPTION DETAILED END

	//CONTENT DESCRIPTION SPECIAL
	echo '<div id="shopitem_description_special">';
	echo '	FAHRZEUGE';
	echo '</div>';
	//CONTENT DESCRIPTION SPECIAL END

	//CONTENT DESCRIPTION HELP
	echo '<div id="shopitem_description_help">';
	echo '	HILFE';
	echo '</div>';
	//CONTENT DESCRIPTION HELP END
	echo '	</div>';
	
	//CONTENT PRICE COLUMN
	echo '<div style="width:214px; border:0; float:right;">';
	$hide_price=hide_price($_SESSION["id_user"]);
	
	echo '<div style="width:200px; display:inline; float:right;">';
	$price = get_prices($_GET["id_item"]);
	if ($price["discount"]>0)
	{
		echo '	<span style="width:100px; font-size:10px; font-weight:bold; font-style:italic; color:#ff0000;">';
		echo '	AKTIONSPREIS!';
		echo '	</span><br />';
		echo '	<span style="width:100px; font-size:30px; font-weight:bold; font-style:italic; color:#ff0000;">';	
	}
	elseif( isset($price["offline_net"]) )
	{
		echo '	<span style="width:100px; font-size:10px; font-weight:bold; font-style:italic; color:#ff0000;">';
		echo '	ONLINE-PREIS';
		echo '	</span><br />';
		echo '	<span style="width:100px; font-size:10px; font-weight:bold; font-style:italic; color:#ff0000;">';
		echo '	Ihr Offline-Preis wäre € '.number_format($price["offline_net"], 2);
		echo '	</span><br />';
		echo '	<span style="width:100px; font-size:24px; font-weight:bold; font-style:italic; color:#000000;">';	
	}
	else
	{
		echo '	<span style="width:100px; font-size:10px; font-weight:bold; color:#000000;">';
		echo t("Ihr Preis").':';
		echo '	</span><br />';
		echo '	<span style="width:100px; font-size:24px; font-weight:bold; font-style:italic; color:#000000;">';	
	}
	if ($price["total"]<9000)
	{
		if ($hide_price)
		{
			echo '<span id="hide_price"';
			echo 'onmouseover="this.innerHTML = \'		€ '.number_format($price["total"], 2).'\'"';

			if ($price["brutto"]>0)
			{
				echo 'onmouseout="this.innerHTML = \'€ '.number_format($price["brutto"], 2).'\'">';
				echo '€ '.number_format($price["brutto"], 2);
			}
			else 
			{
				echo 'onmouseout="this.innerHTML = \''.t("Preis auf Anfrage").'\'">';
				echo t("Preis auf Anfrage");
			}

//			echo 'onmouseout="this.innerHTML = \'		€ '.number_format($price["brutto"], 2).'\'">';
//			echo '		€ '.number_format($price["brutto"], 2);
			echo '</span>';
		}
		else echo '		€ '.number_format($price["total"], 2);
	}
	else 
	{
		echo '<span style="width:100px; font-size:18px; font-weight:bold; color:#000000;">';	
		echo 'Preis auf Anfrage';
		echo '</span>';	
	}
	echo '	</span>';
//	if ($price["collateral_total"]>0)
	if( $shop_items["collateral"]>0 )
	{
		echo '<span style="font-size:10px; font-weight:bold; color:#ff0000;">';
//		echo '<br />zzgl. € '.number_format($price["collateral_total"], 2).' '.t("Altteilpfand");
		echo '<br />zzgl. € '.number_format($shop_items["collateral"]*1.19, 2).' '.t("Altteilpfand");
		echo '</span>';
	}
	echo '<span style="font-size:12px; color:#ff0000;">';
	if (isset($price["season_price"]))
	{
		 echo '	<br />€ '.number_format($price["season_price"][0], 2).' '.t("ab").' '.$price["season_amount"][0].' Stück<br />';
	}
	echo '</span>';	
	echo '<span style="font-size:10px;">';
	if ($price["total"]==$price["gross"]) echo '	<br />'.t("inkl. Mehrwertsteuer").' ('.$price["VAT"].'%)';
	echo '	<br /><a href="'.PATHLANG.'online-shop/versandkosten/" target="_blank">'.t("zzgl. Versandkosten").'</a></span>';
	if ($price["brutto"]>0 and $price["brutto"]>$price["total"])
	{
		echo '<br />';
		echo '<span style="font-size:10px;">';
		echo '<br />'.t("unverbindl. Preisempfehlung").' € '.number_format($price["brutto"], 2);
		echo '</span>';
		if ($price["total"]==$price["net"])
		{
			echo '<span style="font-size:16px; font-weight:bold;">';
			echo '<br />'.t("Ihr Rabatt").':&nbsp&nbsp&nbsp'.number_format($price["percent"], 1).' %<br />';
			echo '</span>';
		}
		else echo '<br />'; 

	}
	if ($price["total"]<9000)
	{
		if ($shop_items["GART"]==82 and strpos($shop_items["MPN"] ,'/2')=== false) // keine einzelnen Bremsscheiben verkaufen
		{
			echo '	<br /><input class="cart_add_amount" id="article'.$_GET["id_item"].'" type="text" size="1" value="2" onkeyup="check_onEnter('.$_GET["id_item"].', '.$vehicle_save.')" />';
			echo '	<input class="cart_add_button" type="button" onclick="javascript:check_amount('.$_GET["id_item"].', '.$vehicle_save.');" value="'.t("In den Warenkorb").'" name="form_button" />';
		}
		else
		{
			echo '	<br /><input class="cart_add_amount" id="article'.$_GET["id_item"].'" type="text" size="1" value="1" onkeyup="cart_add_enter('.$_GET["id_item"].')" />';
			echo '	<input class="cart_add_button" type="button" onclick="javascript:cart_add('.$_GET["id_item"].', '.$vehicle_save.');" value="'.t("In den Warenkorb").'" name="form_button" />';
		}
			
	}
	if($_SESSION["rcid"]!=9999 and $_SESSION["rcid"]>0)
	{
		echo '<br />'.itemstatus_rc($_GET["id_item"]);
	}
	else
	{
		echo '<br />'.itemstatus($_GET["id_item"]);
	}
	
	//GOOGLE +1 BUTTON	
	echo '<br /><br /><br />';
	echo '<div class="g-plusone" data-size="medium"></div>';
	//FACEBOOK LIKE BUTTON	
	echo '<br /><br />';
	echo '<div class="fb-like" data-send="false" data-layout="button_count" data-width="100" data-show-faces="false"></div>';
	echo '<br /><br />';
	echo '</div>';
	echo '</div>';
	//CONTENT PRICE COLUMN END
	
	echo '</div>';
	

//********************************Kontaktfenster********************************************
	if($_SESSION["origin"]!="RU" and ($_SESSION["id_site"]==1 or $_SESSION["id_site"]==7 or $_SESSION["id_site"]==17))
	{
		echo '<div style="position: fixed; right: 0px; top: 30%; z-index: 100;">';
		/*echo '<div id="contact_button" style="background-color: #E6E6E6; border-color: #CCCCCC; border-style: solid; border-width: 4px 0px 4px 4px; cursor: pointer; float: left; height: 232px"><img id="info" src="'.PATH.'images/icons/24x24/info.png" style="cursor: pointer"><img id="close" src="'.PATH.'images/icons/24x24/remove.png" style="cursor: pointer; display: none"></div>';*/
		echo '<div id="contact_button" style="cursor: pointer; float: left; height: 232px"><img id="info" src="'.PATH.'images/Fragen Sie uns 2.png" style="cursor: pointer"></div>';
		echo '<div id="contact_div" style="cursor: pointer; display: none; float: left; background-color: #E6E6E6; text-align: center;"></div>';
		echo '</div>';
	}
//*********************************************************************************************

?>
<script type="text/javascript" src="<?php echo PATH; ?>modules/highslide/highslide/highslide-with-gallery.js"></script>
<link rel="stylesheet" type="text/css" href="<?php echo PATH; ?>modules/highslide/highslide/highslide.css" />
<!--[if lt IE 7]>
<link rel="stylesheet" type="text/css" href="<?php echo PATH; ?>modules/highslide/highslide-ie6.css" />
<![endif]-->

<script type="text/javascript">
// Language strings
hs.lang = {
   cssDirection:     'ltr',
   loadingText :     'Lade...',
   loadingTitle :    'Klick zum Abbrechen',
   focusTitle :      'Klick um nach vorn zu bringen',
   fullExpandTitle : 'Zur Originalgröße erweitern',
   fullExpandText :  'Vollbild',
   creditsText :     '',
   creditsTitle :    'Gehe zur Highslide JS Homepage',
   previousText :    'Voriges',
   previousTitle :   'Voriges (Pfeiltaste links)',
   nextText :        'Nächstes',
   nextTitle :       'Nächstes (Pfeiltaste rechts)',
   moveTitle :       'Verschieben',
   moveText :        'Verschieben',
   closeText :       'Schließen',
   closeTitle :      'Schließen (Esc)',
   resizeTitle :     'Größe wiederherstellen',
   playText :        'Abspielen',
   playTitle :       'Slideshow abspielen (Leertaste)',
   pauseText :       'Pause',
   pauseTitle :      'Pausiere Slideshow (Leertaste)',
   number :          'Bild %1/%2',
   restoreTitle :    'Klick um das Bild zu schließen, klick und ziehe um zu verschieben. Benutze Pfeiltasten für vor und zurück.'
};
</script>

<!--
    2) Optionally override the settings defined at the top
    of the highslide.js file. The parameter hs.graphicsDir is important!
-->

<script type="text/javascript">
//******************************Kontaktfenster*******************************************
	function contact_div_build()
	{
		var gew = "<?php if($_SESSION["id_site"]==17) echo 'true'; else echo 'false';?>";
		//alert(gew);
		var image = '';
		var name = '';
		if(gew=='true')
		{
			var seller = Math.round((Math.random()*4)+0.5);
			if(seller==1)
			{
			  	image = '<img src="<?php echo PATH;?>/images/employees/ddrohla.jpg">';
			  	name = '<p style="display: inline; font-weight: bold"><nobr>Detlef Drohla</p><p style="display: inline">  Tel.: 033845/600 33</nobr></p>';
			}
			else if(seller==2)
			{
			  	image = '<img src="<?php echo PATH;?>/images/employees/dwolter.jpg">';
			  	name = '<p style="display: inline; font-weight: bold"><nobr>Doreen Wolter</p><p style="display: inline">  Tel.: 033845/600 49</nobr></p>';
			}
			else if(seller==3)
			{
				image = '<img src="<?php echo PATH;?>/images/employees/mmenzel.jpg">';
			  	name = '<p style="display: inline; font-weight: bold"><nobr>Marlies Menzel</p><p style="display: inline">  Tel.: 033845/600 31</nobr></p>';
			}
			else if(seller==4)
			{
				image = '<img src="<?php echo PATH;?>/images/employees/sbolle.jpg">';
			  	name = '<p style="display: inline; font-weight: bold"><nobr>Sybille Bolle</p><p style="display: inline">  Tel.: 033845/600 32</nobr></p>';
			}
		}
		else
		{
			var seller = Math.round((Math.random()*3)+0.5);
			if(seller==1)
			{
			  	image = '<img src="<?php echo PATH;?>/images/employees/tbuls.jpg">';
			  	name = '<p style="display: inline; font-weight: bold"><nobr>Tobias Buls</p><p style="display: inline">  Tel.: 033844/75 82 36</nobr></p>';
			}
			else if(seller==2)
			{
			  	image = '<img src="<?php echo PATH;?>/images/employees/abraun.jpg">';
			  	name = '<p style="display: inline; font-weight: bold"><nobr>Andy Braun</p><p style="display: inline">  Tel.: 033844/75 82 37</nobr></p>';
			}
			else if(seller==3)
			{
				image = '<img src="<?php echo PATH;?>/images/employees/areinke.jpg">';
			  	name = '<p style="display: inline; font-weight: bold"><nobr>Andreas Reinke</p><p style="display: inline">  Tel.: 033844/75 82 29</nobr></p>';
			}
		}
		
		var table = $('<table style="border: solid; border-width: 4px; border-color: #CCCCCC; height: 240px;"></table>');
		
		var tr = $('<tr></tr>');
		var td = $('<td style="background-color: #DDDDDD"><p style="font-weight: bold"><nobr>Technik-Hotline</nobr></p></td>');
		tr.append(td);
		td = $('<td style="background-color: #DDDDDD"><p style="font-weight: bold"><nobr>Bestell-Hotline</nobr></p></td>');
		tr.append(td);
		table.append(tr);
		
		tr = $('<tr></tr>');
		td = $('<td><img src="<?php echo PATH;?>/images/employees/nmueller.jpg"></td>');
		tr.append(td);
		td = $('<td>' + image + '</td>');
		tr.append(td);
		table.append(tr);
		
		tr = $('<tr></tr>');
		td = $('<td style="background-color: #DDDDDD"><p style="display: inline; font-weight: bold"><nobr>Der Schrauber</p><p style="display: inline">  Tel.: 0800/20 60 666</nobr></p></td>');
		tr.append(td);
		td = $('<td style="background-color: #DDDDDD">' + name + '</td>');
		tr.append(td);
		table.append(tr);
		
		$('#contact_div').append(table);	
	}

	$(window).load(function () {
		contact_div_build();
  	});
		
	$('#contact_button').mouseenter(function(){
		$("#contact_div").toggle(500);
		$('#contact_button').unbind("mouseenter");
		});	
		
	$("#contact_button").click(function(){
		//alert($("#contact_div").css('display'));
		if($("#contact_div").css('display')=='block')
		{
			$('#contact_button').mouseenter(function(){
				$("#contact_div").toggle(500);
				$('#contact_button').unbind("mouseenter");
				});
		}
		$("#contact_div").toggle(500);
		//$('#close').toggle(1);
		//$('#info').toggle(1);
		});
	
	$("#contact_div").click(function(){
		if($("#contact_div").css('display')=='block')
		{
			$("#contact_div").toggle(500, function(){
				$('#contact_button').mouseenter(function(){
					$("#contact_div").toggle(500);
					$('#contact_button').unbind("mouseenter");
					});
				});
		}
		else
			$("#contact_div").toggle(500);
		//$('#close').hide();
		//$('#info').show();
		});

//*************************************************************************************	
</script>

<script type="text/javascript">
	hs.graphicsDir = '<?php echo PATH; ?>modules/highslide/highslide/graphics/';
	hs.align = 'center';
	hs.transitions = ['expand', 'crossfade'];
	hs.fadeInOut = true;
	hs.dimmingOpacity = 0.8;
	hs.wrapperClassName = 'borderless floating-caption';
	hs.captionEval = 'this.thumb.alt';
	hs.marginLeft = 100; // make room for the thumbstrip
	hs.marginBottom = 80 // make room for the controls and the floating caption
	hs.numberPosition = 'caption';
	hs.lang.number = '%1/%2';

	// Add the slideshow providing the controlbar and the thumbstrip
	hs.addSlideshow({
		//slideshowGroup: 'group1',
		interval: 5000,
		repeat: false,
		useControls: true,
		overlayOptions: {
			className: 'text-controls',
			position: 'bottom center',
			relativeTo: 'viewport',
			offsetX: 50,
			offsetY: -5

		},
		thumbstrip: {
			position: 'middle left',
			mode: 'vertical',
			relativeTo: 'viewport'
		}
	});

	// Add the simple close button
	hs.registerOverlay({
		html: '<div class="closebutton" onclick="return hs.close(this)" title="Close"></div>',
		position: 'top right',
		fade: 2 // fading the semi-transparent overlay looks bad in IE
	});

		function ebay_update_ma(id_item)
		{
			$.post("<?php echo PATH; ?>modules/backend_ebay_auction_actions.php", { action:"item_submit", id_item:id_item, pricelist_id:16815, id_account:1 }, function(data) { show_status(data); } );
		}

		function ebay_update_ap(id_item)
		{
			$.post("<?php echo PATH; ?>modules/backend_ebay_auction_actions.php", { action:"item_submit", id_item:id_item, pricelist_id:18209, id_account:2 }, function(data) { show_status(data); } );
		}

		function tecdoc_update(id_item)
		{
			wait_dialog_show();
			$.post("<?php echo PATH; ?>soa/", { API:"shop", Action:"ItemUpdate", id_item:id_item },
			function(data)
			{
				wait_dialog_hide();
				$xml = $($.parseXML(data));
				$ack = $xml.find("Ack");
				if ( $ack.text()=="Success" )
				{
					show_status("Artikel erfolgreich aktualisiert.");
					return;
				}
				else if ( $ack.text()=="Unfinished" )
				{
					show_status("Artikel wurde teilweise überarbeitet. Update wurde erneut gestartet um den Rest zu aktualisieren. Bitte warten.");
					tecdoc_update(id_item);
					return;
				}
				else
				{
					show_status("Artikel erfolgreich aktualisiert.");
					location.href=location.href;
					return;
				}
			});
		}
		
		function check_onEnter(id_item, vehicle_save)
		{
			if(!e) var e = event || window.event;
			if ((e.keyCode) == 13)  check_amount(id_item, vehicle_save);
		}

		function check_amount(id_item, vehicle_save)
		{
			var $amount=$("#article" + id_item).val();
			if (($amount%2) != 0) alert("<?php echo t("Bremsscheiben werden nur als Satz verkauft!"); ?>");
			else cart_add(id_item, vehicle_save);
		}
	
	</script>
	
<?php



	echo '<form method="post">';
	if ( $_SESSION["userrole_id"]==1 or $_SESSION["userrole_id"]==3 )
	{
		echo '<input class="formbutton" type="button" value="TecDoc-Update" onclick="tecdoc_update('.$_GET["id_item"].');" />';
	}
	echo '</form>';

	//title
//	$results=q("SELECT * FROM shop_items AS a, shop_items_".$_GET["lang"]." AS b WHERE a.id_item='".$_GET["id_item"]."' AND a.id_item=b.id_item;", $dbshop, __FILE__, __LINE__);
//	$row=mysqli_fetch_array($results);
//	$artnr=$row["MPN"];
	$desc = $shop_items_lang["description"];
	
	//price


	echo '<br style="clear:both;" />';
	
	//Keywords (Zuordnung über GART)
	$results3=q("SELECT * FROM shop_items_keywords WHERE GART=".$shop_items["GART"]." AND language_id='".$_GET["lang"]."' order by ordering;", $dbshop, __FILE__, __LINE__);
	if (mysqli_num_rows($results3)>1) 
	{
		echo '<div style="padding:5px;">';
		echo '<h3>Artikelbeschreibung</h3>';
		echo t("Dieser Artikel ist auch bekannt als").": ";
		$count = mysqli_num_rows($results3)-1;
		$i=0;
		$msg="";
		while ($row3=mysqli_fetch_array($results3))
		{
			$i++;
			$msg .= $row3["keyword"];
			if($i<$count) $msg .= ", ";
			elseif($i==$count) $msg .= " ".t("und")." ";
			else $msg .= "!";
		}
		echo $msg;
	}
	
	//Infotexte (ZUORDNUNG über GART)
	$results3=q("SELECT * FROM shop_items_descriptions WHERE GART=".$shop_items["GART"]." AND language_id=".$id_language.";", $dbshop, __FILE__, __LINE__);
	if ( mysqli_num_rows($results3)>0 ) 
	{
		$row3=mysqli_fetch_array($results3);
		echo '<div style="padding:5px;">';
		echo '	<h3>Artikelbeschreibung</h3>';
		echo nl2br($row3["description"]);
		echo '</div>';
		echo '<br style="clear:both;" />';		
	}
	else
	{
			echo '<br style="clear:both;" />';
			echo '<br style="clear:both;" />';			
	}


	//Der-Schrauber-Hilfetexte (ZUORDNUNG über SHOPITEM)
	$results3=q("SELECT * FROM cms_articles_shopitems WHERE item_id=".$_GET["id_item"].";", $dbweb, __FILE__, __LINE__);
	while( $row3=mysqli_fetch_array($results3) )
	{
		$results2=q("SELECT * FROM cms_articles WHERE id_article=".$row3["article_id"].";", $dbweb, __FILE__, __LINE__);
		if ( mysqli_num_rows($results2)>0 )
		{
			$row2=mysqli_fetch_array($results2);
			if ( $row2["published"]>0 )
			{
				echo '<br style="clear:both;" />';
				echo '<div style="border:2px solid #bb3712; padding:5px;">';
				echo '<h2>Der Schrauber hilft!</h2>';
				echo '<img src="http://www.mapco.de/images/schrauber.png" alt="Der Schrauber" style="width:300px; margin:0px 5px 3px 0px; float:left;" title="Der Schrauber">';
				echo nl2br($row2["article"]);
				echo '</div>';
				echo '<br style="clear:both;" />';
			}
		}
	}
	
	//Der-Schrauber-Hilfetexte (ZUORDNUNG über GART)
	$results3=q("SELECT * FROM cms_articles_gart WHERE GART_id=".$shop_items["GART"].";", $dbweb, __FILE__, __LINE__);
	while( $row3=mysqli_fetch_array($results3) )
	{
		$results2=q("SELECT * FROM cms_articles WHERE id_article=".$row3["article_id"].";", $dbweb, __FILE__, __LINE__);
		if ( mysqli_num_rows($results2)>0 )
		{
			$row2=mysqli_fetch_array($results2);
			if ( $row2["published"]>0 )
			{
				echo '<br style="clear:both;" />';
				echo '<div style="border:2px solid #bb3712; padding:5px;">';
				echo '<h2>Der Schrauber hilft!</h2>';
				echo '<img src="http://www.mapco.de/images/schrauber.png" alt="Der Schrauber" style="width:300px; margin:0px 5px 3px 0px; float:left;" title="Der Schrauber">';
				echo nl2br($row2["article"]);
				echo '</div>';
				echo '<br style="clear:both;" />';
			}
		}
	}

	//description
//	$desc = cutout($desc, 'OE START -->', '<!-- OE STOP');
	$desc = cutout($desc, '<!-- OEM START -->', '<!-- OEM STOP -->');
//	$desc = cutout($desc, '<!-- VEHICLE APPLICATION START -->', '<!-- VEHICLE APPLICATION STOP -->');

	$desc=str_replace("http://www.mapco.de/", PATH, $desc);
	echo $desc;


	$stoptime=time()+microtime();
//	echo '!!!'.round($stoptime-$starttime, 2).'s<br />';
/*
		//vehicle applications
		$vapps=array();
		$vapps_criteria=array();
		$results=q("SELECT vehicle_id, criteria FROM shop_items_vehicles WHERE item_id=".$_GET["id_item"]." AND language_id=1;", $dbshop, __FILE__, __LINE__);
		while( $row=mysqli_fetch_array($results) )
		{
			$vapps[$row["vehicle_id"]]=$row["vehicle_id"];
			$vapps_criteria[$row["vehicle_id"]]=$row["criteria"];
		}
		echo '<h2>'.t("Fahrzeugzuordnungen", $_GET["lang"]).'</h2>'."\n";
		echo '<table class="hover">'."\n";
		echo '<tr>'."\n";
		echo '	<th>'.t("Fahrzeug", $_GET["lang"]).'</th>'."\n";
		echo '	<th width="120">'.t("Baujahr", $_GET["lang"]).'</th>'."\n";
		echo '	<th width="100">'.t("Leistung", $_GET["lang"]).'</th>'."\n";
		echo '	<th width="80">'.t("Hubraum", $_GET["lang"]).'</th>'."\n";
		echo '	<th width="80">'.t("KBA-Nr.", $_GET["lang"]).'</th>'."\n";
		echo '</tr>'."\n";
		$results=q("SELECT * FROM vehicles_".$_GET["lang"]." WHERE id_vehicle IN (".implode(", ", $vapps).") ORDER BY BEZ1, BEZ2, BEZ3 LIMIT 20;", $dbshop, __FILE__, __LINE__);
		while( $row=mysqli_fetch_array($results) )
		{
			if ( isset($vapps[$row["id_vehicle"]]) )
			{
				echo '<tr>';
				echo '	<td>';
				echo '    <a href="'.PATHLANG.'fahrzeugsuche/'.$row["id_vehicle"].'/'.url_encode($row["BEZ1"].' '.$row["BEZ2"].' '.$row["BEZ3"]).'" title="'.$row["BEZ1"].' '.$row["BEZ2"].' '.$row["BEZ3"].'">';
				echo        $row["BEZ1"].' '.$row["BEZ2"].' '.$row["BEZ3"];
				echo '    </a>';
				echo '    <br /><span style="color:#FF0000"><i>'.$vapps_criteria[$row["id_vehicle"]].'</i></span>'."\n";
				echo '  </td>';
				echo '  <td>'.baujahr($row["BJvon"]).' - '.baujahr($row["BJbis"]).'</td>';
				echo '  <td>'.($row["kW"]*1).'KW ('.($row["PS"]*1).'PS)</td>';
				echo '  <td>'.number_format($row["ccmTech"]).'ccm</td>';
				$kba_txt='';
				if(strpos($row["KBA"], ",") === FALSE) $kba_txt=substr($row["KBA"], 0, 4).'-'.substr($row["KBA"], 4, 3);
				else
				{
					$kbas=explode(", ", $row["KBA"]);
					foreach($kbas as $kba)
					{
						if($kba_txt=='') $kba_txt=substr($kba, 0, 4).'-'.substr($kba, 4, 3);
						else $kba_txt.= ', '.substr($kba, 0, 4).'-'.substr($kba, 4, 3);
					}
				}
				echo '  <td>'.$kba_txt.'</td>';
				echo '</tr>';
			}
		}
		echo '</table>'."\n";
*/

	//lastmod
	$results=q("SELECT lastmod FROM shop_items WHERE id_item='".$_GET["id_item"]."';", $dbshop, __FILE__, __LINE__);
	$row=mysqli_fetch_array($results);
	echo '<br style="clear:both;" />'.t("Letzte Aktualisierung").': '.date("d.m.Y H:i", $row["lastmod"]);
	echo '</div>';

	include("templates/".TEMPLATE."/footer.php");
?>
<?php
	///äöüÄÖÜ UTF-8
	if (!function_exists("show_item"))
	{
		function show_item($id_item, $artnr, $title, $description, $oenr="", $vehicle_save=0)
		{
			global $dbweb;
			global $dbshop;
			
			include("shop_get_prices.php");
			include("mapco_hide_price.php");
			include("cms_url_encode.php");
			
			//Gewerbskunde?
			$gewerblich=gewerblich($_SESSION["id_user"]);
			
			echo '<div class="shopitem">';
			
			//Image
			$results=q("SELECT * FROM shop_items WHERE id_item=".$id_item.";", $dbshop, __FILE__, __LINE__);
			$shop_items=mysqli_fetch_array($results);
			$results3=q("SELECT * FROM cms_articles_images WHERE article_id=".$shop_items["article_id"]." ORDER BY ordering LIMIT 1;", $dbweb, __FILE__, __LINE__);
			if ( mysqli_num_rows($results3)==0 )
			{
				if( $_SESSION["id_shop"]==2 ) $src=PATH.'images/library/ap_frame_noimage.jpg';
				else $src=PATH.'files_thumbnail/0.jpg';
			}
			else
			{
				$results55=q("SELECT * FROm shop_shops WHERE id_shop=".$_SESSION["id_shop"].";", $dbshop, __FILE__, __LINE__);
				$shop=mysqli_fetch_array($results55);
				$row3=mysqli_fetch_array($results3);
				$results=q("SELECT * FROM cms_files WHERE original_id='".$row3["file_id"]."' AND imageformat_id=".$shop["imageformat_id"]." LIMIT 1;", $dbweb, __FILE__, __LINE__);
				$row=mysqli_fetch_array($results);
				$src='files/'.floor(bcdiv($row["id_file"], 1000)).'/'.$row["id_file"].'.'.$row["extension"];
				if ( file_exists($src) ) $src=PATH.$src;
				else
				{
					if( $_SESSION["id_shop"]==2 ) $src=PATH.'images/library/ap_frame_noimage.jpg';
					else $src=PATH.'files_thumbnail/0.jpg';
				}
			}
			if ($oenr!="") echo '<b>'.t("Gefunden über OE-Nr.").' '.$oenr.'</b><br style="clear:both;" /><br />';
			echo '<a href="'.PATHLANG.'online-shop/autoteile/'.$id_item.'/'.url_encode($title).'">';
//			echo '<img alt="'.$row["id_file"].'" class="lazyimage" src="'.PATH.'images/icons/loaderb64.gif" title="'.$title.'" />';
			echo '<img src="'.$src.'" alt="'.$title.'" style="width:120px;" title="'.$title.'" onmouseover="document.getElementById(\'bigimage\').src=this.src" />';
			echo '</a>';
			
			$price = get_prices($id_item);
			$hide_price=hide_price($_SESSION["id_user"]);

			//title and description
			echo '<div class="shopitem_description">';
			
			echo '<a class="shopitem_title" href="'.PATHLANG.'online-shop/autoteile/'.$id_item.'/'.url_encode($title).'" alt="'.$title.'" title="'.$title.'">';
			echo strtoupper($title);
			echo '</a>';
			echo '<div class="shopitem_description">'.$description;
			echo '</div>';
			echo '</div>';

/*
			//get discount
			$discount=0;
			$query="SELECT * FROM shop_offers WHERE item_id='".$id_item."' AND `from`<".time()." AND `until`>".time().";";
			$results=q($query, $dbshop, __FILE__, __LINE__);
			if (mysqli_num_rows($results)>0)
			{
				$row=mysqli_fetch_array($results);
				$discount=$row["discount"];
			}
*/
	
			//price
			if ($discount>0) $style='border:1px solid red; ';
			else $style='';
			echo '<div style="width:200px; float:right;">';
			echo '	<span class="shopitem_price">';
			if ($price["total"]<9000)
			{
				if ($hide_price)
				{
					echo '<span id="hide_price"';
					echo 'onmouseover="this.innerHTML = \'€ '.number_format($price["total"], 2).'\'"';
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
					echo '</span>';
				}
				else echo '€ '.number_format($price["total"], 2);
			}
			else 
			{
				echo '<span style="width:100px; font-size:18px; font-weight:bold; color:#000000;">';	
				echo t("Preis auf Anfrage");
				echo '</span>';	
			}
			echo '</span>';	
			echo '<span style="font-size:10px;">';
			if ($price["collateral_total"]>0) echo '<br />zzgl. € '.number_format($price["collateral_total"], 2).' '.t("Altteilpfand");
			if (!$gewerblich) echo '<br />'.t("inkl. Mehrwertsteuer").' ('.UST.'%)';
			echo '<br /><a href="'.PATHLANG.'online-shop/versandkosten/" target="_blank">'.t("zzgl. Versandkosten").'</a></span>';
			if ($price["total"]<9000)
			{
				if ($shop_items["GART"]==82 and strpos($shop_items["MPN"] ,'/2')=== false) // keine einzelnen Bremsscheiben verkaufen
				{
					echo '	<br /><input class="cart_add_amount" id="article'.$id_item.'" type="text" value="2" onkeyup="check_onEnter('.$id_item.', '.$vehicle_save.')" />';
					echo '	<input class="cart_add_button" type="button" onclick="javascript:check_amount('.$id_item.', '.$vehicle_save.');" value="'.t("In den Warenkorb").'" name="form_button" />';
				}
				else
				{
					echo '	<br /><input class="cart_add_amount" id="article'.$id_item.'" type="text" value="1" onkeyup="cart_add_enter('.$id_item.')" />';
					echo '	<input class="cart_add_button" type="button" onclick="javascript:cart_add('.$id_item.', '.$vehicle_save.');" value="'.t("In den Warenkorb").'" name="form_button" />';
				}
			}

			if($_SESSION["rcid"]!=9999 and $_SESSION["rcid"]>0)
			{
				echo '<br /><span style="float:right;">'.itemstatus_rc($id_item).'</span>';
			}
			else
			{
				echo '<br /><span style="float:right;">'.itemstatus($id_item).'</span>';
			}

			echo '</div>';
			echo '</div>';
		}
	}
	

	if (!function_exists("show_item2"))
	{
		function show_item2($row)
		{
			global $dbweb;
			global $dbshop;

			include("shop_get_prices.php");
			include("mapco_hide_price.php");
			
			//Gewerbskunde?
			$gewerblich=gewerblich($_SESSION["id_user"]);
/*			$titles=get_titles($artnr, 8192);
			if ($titles == "MAPCO - Autoteile vom Hersteller")
			{
				$alt_title=$title;
			}
			else
			{
				$alt_title=$titles[0];
			}
*/			
			echo '<tr>';
			
			//Compare
	//		echo '	<td style="background:#e8e8e6; vertical-align:middle;"><input type="checkbox" /></td>';
			
			//Image
			echo '<td style="width:100px;">';
			$results3=q("SELECT * FROM shop_items_files WHERE item_id='".$row["id_item"]."' LIMIT 1;", $dbshop, __FILE__, __LINE__);
			if (mysqli_num_rows($results3)>0)
			{
				$results55=q("SELECT * FROm shop_shops WHERE id_shop=".$_SESSION["id_shop"].";", $dbshop, __FILE__, __LINE__);
				$shop=mysqli_fetch_array($results55);
				$row3=mysqli_fetch_array($results3);
				$results2=q("SELECT * FROM cms_files WHERE original_id=".$row3["file_id"]." AND imageformat_id=".$shop["imageformat_id"]." LIMIT 1;", $dbweb, __FILE__, __LINE__);
				$row2=mysqli_fetch_array($results2);
				$filename=PATH.'files/'.floor(bcdiv($row2["id_file"], 1000)).'/'.$row2["id_file"].'.'.$row2["extension"];
//				echo '<a href="shop_item.php?lang='.$_GET["lang"].'&id_item='.$row["id_item"].'">';
				echo '<a href="'.PATHLANG.'online-shop/autoteile/'.$row["id_item"].'/'.url_encode($row["title"]).'">';
				echo '<img style="width:100px; margin:2px; border:0; padding:0;" src="'.$filename.'" alt="'.$row["title"].'" title="'.$row["title"].'" onmouseover="document.getElementById(\'bigimage\').src=this.src" />';
				echo '</a>';
			}
			else
			{
				echo '<a href="'.PATHLANG.'online-shop/autoteile/'.$row["id_item"].'/'.url_encode($row["title"]).'" alt="'.$row["title"].'" title="'.$row["title"].'">';
				if($_SESSION["id_user"]==21371) print_r($_SESSION);
				if( $_SESSION["id_shop"]==2 ) echo '<img src="'.PATH.'images/library/ap_frame_noimage.jpg" />';
				else echo '<img src="'.PATH.'images/library/rahmen-bild-folgt.jpg" />';
				echo '</a>';
			}
			echo '</td>';		
			
			$price = get_prices($row["id_item"]);
			$hide_price=hide_price($_SESSION["id_user"]);

			//title and description
			echo '<td>';
			if (isset($oenr) && $oenr!="") echo '<b>'.t("Gefunden über OE-Nr.").' '.$oenr.'</b>';
			
			echo '<a href="'.PATHLANG.'online-shop/autoteile/'.$row["id_item"].'/'.url_encode($row["title"]).'" alt="'.$row["title"].'" title="'.$row["title"].'">';
			echo '	<h1>'.strtoupper($row["title"]).'</h1>';
			echo '</a>';
			echo '<br style="clear:both;" /><br />'.$row["short_description"];
			echo '</td>';
/*			
			//Altteilpfand
			$atwert=false;
			$atwert=$row["collateral"];
			if (!$gewerblich) $atwert*=((100+UST)/100);
	
			//get discount
			$discount=0;
			$query="SELECT * FROM shop_offers WHERE item_id='".$row["id_item"]."' AND `from`<".time()." AND `until`>".time().";";
			$results2=q($query, $dbshop, __FILE__, __LINE__);
			if (mysqli_num_rows($results2))
			{
				$row2=mysqli_fetch_array($results2);
				$discount=$row2["percent"];
			}
*/	
			//price
			if (isset($discount) && $discount>0) $style='border:1px solid red; '; else $style='';
			echo '<td style="'.$style.'text-align:center; width:200px; text-align:right;">';
			echo '	<span style="width:100px; font-size:16px; font-weight:bold; font-style:italic; color:#fc7204;">';
			if ($price["total"]<9000)
			{
				if ($hide_price)
				{
					echo '<span id="hide_price"';
					echo 'onmouseover="this.innerHTML = \'€ '.number_format($price["total"], 2).'\'"';
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
					echo '</span>';
				}
				else echo '€ '.number_format($price["total"], 2);
			}
			else 
			{
				echo '<span style="width:100px; font-size:18px; font-weight:bold; color:#000000;">';	
				echo 'Preis auf Anfrage';
				echo '</span>';	
			}
			echo '</span>';	
			echo '<span style="font-size:10px;">';
			if ($price["collateral_total"]>0) echo '<br />zzgl. € '.number_format($price["collateral_total"], 2).' '.t("Altteilpfand");
			if (!$gewerblich) echo '<br />'.t("inkl. Mehrwertsteuer").' ('.UST.'%)';
			echo '<br /><a href="'.PATHLANG.'online-shop/versandkosten/" target="_blank">'.t("zzgl. Versandkosten").'</a></span>';
			echo '	<br /><input style="width:30px;" id="article'.$row["id_item"].'" type="text" value="1" onkeyup="cart_add_enter('.$row["id_item"].')" />';
			echo '	<input style="width:160px;" type="button" onclick="javascript:cart_add('.$row["id_item"].');" value="'.t("In den Warenkorb").'" name="form_button" />';

			if($_SESSION["rcid"]!=9999 and $_SESSION["rcid"]>0)
			{
				echo '<br /><span style="float:right;">'.itemstatus_rc($row["id_item"]).'</span>';
			}
			else
			{
				echo '<br /><span style="float:right;">'.itemstatus($row["id_item"]).'</span>';
			}


//			echo '<br /><span style="float:right;">'.itemstatus2($row["MPN"]).'</span>';
		/*
			echo '	<ul style="margin-top:0;" >';
			echo '		<li><a href="" onclick="return note_add('.$row["id_item"].')">'.AUF_DEN_MERKZETTEL.'</a></li>';
			echo '		<li><a href="">'.BEITRAEGE_ANSEHEN.'</a></li>';
			echo '	</ul>';
		*/
			echo '</td>';
			echo '</tr>';
		}
	}
?>
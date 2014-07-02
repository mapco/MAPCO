<?php

	include("../functions/cms_url_encode.php");
	include("../functions/shop_get_prices.php");
	include("../functions/cms_t.php");

	//$index=$_POST["Index"];
	if (isset($_POST["promotionBoxPicStart"]))
	{
		$promotionBoxPicStart=$_POST["promotionBoxPicStart"];
	}
	else 
	{
		$promotionBoxPicStart=0;
	}
	
	echo '<div style="width:18px; margin-top:6px; margin-left:3px; height:135px; float:left; z-index:101">';
	echo '<img style="height:135px; border:0; padding:0; float:left; cursor:pointer;" src="'.PATH.'images/testordner/Up.png" alt="vorherige Artikel" title="vorherige Artikel" onclick="prev_Pics3('.$_POST["OfferType"].');" />';
	echo '</div>';
		
//	$res=q("select * from shop_offers where offertype_id = '".$_POST["OfferType"]."';", $dbshop, __FILE__, __LINE__);
//	$res=q("SELECT ArtNr FROM t_200 WHERE GART IN (00051, 00191, 00284, 00914, 00915, 02640, 02641, 03236) ORDER BY RAND() LIMIT 30;", $dbshop, __FILE__, __LINE__);
/*
	while ($row=mysqli_fetch_array($res) and $pic_count<$promotionBoxPicStart+3) 
	{
	if ($promotionBoxPicStart<=$pic_count) {
*/		
		//$pic_count=0;
	
	//LISTENAUSWAHL
	$pl_type=3; //1=Händler 2=Alle anderen 3=Aktionsliste(n)
/*	
	//if($_SESSION["id_user"]==49352)
	{
		//RABATT ÜBER IDIMS-PREISLISTEN (20214-HÄNDLER 20215-ALLE ANDEREN)
		$res=q("SELECT * FROM cms_users WHERE id_user=".$_SESSION["id_user"].";", $dbweb, __FILE__, __LINE__);
		$cms_users=mysqli_fetch_assoc($res);
		$res2=q("SELECT * FROM kunde WHERE ADR_ID=".$cms_users["idims_adr_id"].";", $dbshop, __FILE__, __LINE__);
		$kunde=mysqli_fetch_assoc($res2);
		
		if(isset($kunde["PREISGR"]) and ($kunde["PREISGR"]==6 or $kunde["PREISGR"]==7))
		{
			$res3=q("SELECT * FROM prpos WHERE LST_NR=20214 AND GUELTIG_AB<='".date("Y-m-d", time())."' AND GUELTIG_BIS>='".date("Y-m-d", time())."';", $dbshop, __FILE__, __LINE__);
			if(mysqli_num_rows($res3)>0)
			{ 
				$pl_type=1;
				$artnr=array();
				$order_e_art=array();
				while($prpos=mysqli_fetch_assoc($res3))
				{
					$artnr[$prpos["ARTNR"]]=$prpos["ARTNR"];
					$order_e_art[$prpos["ARTNR"]]=strtotime($prpos["GUELTIG_BIS"]);
				}
				$results99=q("SELECT * FROM shop_items WHERE MPN IN ('".implode("' ,'", $artnr)."') ORDER BY RAND() LIMIT 3;", $dbshop, __FILE__, __LINE__);
			}
		}
		else
		{
			$res3=q("SELECT * FROM prpos WHERE LST_NR=20215 AND GUELTIG_AB<='".date("Y-m-d", time())."' AND GUELTIG_BIS>='".date("Y-m-d", time())."';", $dbshop, __FILE__, __LINE__);
			if(mysqli_num_rows($res3)>0) 
			{
				$pl_type=2;
				$artnr=array();
				$order_e_art=array();
				while($prpos=mysqli_fetch_assoc($res3))
				{
					$artnr[$prpos["ARTNR"]]=$prpos["ARTNR"];
					$order_e_art[$prpos["ARTNR"]]=strtotime($prpos["GUELTIG_BIS"]);
				}
				$results99=q("SELECT * FROM shop_items WHERE MPN IN ('".implode("', '", $artnr)."') ORDER BY RAND() LIMIT 3;", $dbshop, __FILE__, __LINE__);
			}
		}
	}
*/	
	if($pl_type==3)
	{	
		//AKTIVE LISTEN SUCHEN
		$lists=array();
		$res=q("SELECT * FROM shop_offers WHERE offer_start<=".time()." AND offer_end>=".time().";", $dbshop, __FILE__, __LINE__);
		if(mysqli_num_rows($res)>0)
		{
			while($shop_offers=mysqli_fetch_assoc($res))
			{
				$res2=q("SELECT * FROM shop_lists WHERE id_list=".$shop_offers["list_id"].";", $dbshop, __FILE__, __LINE__);
				if(mysqli_num_rows($res2)>0) $lists[]=$shop_offers["list_id"];
			}
		}
		$results99=q("SELECT * FROM shop_lists_items WHERE list_id IN (".implode(",", $lists).") ORDER BY RAND() LIMIT 3;", $dbshop, __FILE__, __LINE__);
	}
	while( $row=mysqli_fetch_array($results99) )
	{
		//LAUFZEIT
		if($pl_type==3)
		{
			$res=q("SELECT * FROM shop_offers WHERE list_id=".$row["list_id"].";", $dbshop, __FILE__, __LINE__);
			$shop_offers=mysqli_fetch_assoc($res);
			$offer_end=$shop_offers["offer_end"];
			$res2=q("SELECT * FROM shop_items WHERE id_item='".$row["item_id"]."';", $dbshop, __FILE__, __LINE__);
		}
/*		
		else
		{
			$offer_end=$order_e_art[$row["MPN"]];
			$res2=q("SELECT * FROM shop_items WHERE id_item='".$row["id_item"]."';", $dbshop, __FILE__, __LINE__);
		}
*/		
		//echo print_r($row);
		//$res2=q("SELECT * FROM shop_items WHERE id_item='".$row["item_id"]."';", $dbshop, __FILE__, __LINE__);
		$row2=mysqli_fetch_array($res2);
//		$res4=q("select a.*, b.* from cms_files a, cms_articles_images b where b.article_id='".$row2["article_id"]."' and a.id_file=b.file_id and a.imageformat_id = '9' LIMIT 1;", $dbweb, __FILE__, __LINE__);
		$res4=q("select a.*, b.* from cms_files a, cms_articles_images b where b.article_id='".$row2["article_id"]."' and a.original_id=b.file_id and a.imageformat_id = '9' LIMIT 1;", $dbweb, __FILE__, __LINE__);
		if (mysqli_num_rows($res4)>0) {
		//	if ($pic_count>=$index) {
			$row4=mysqli_fetch_array($res4);
			// PATH FOR PIC
			$folder=substr($row4["id_file"], 0, 4);
			$path=PATH."files/".$folder."/".$row4["id_file"].".".$row4["extension"];
				
			// GET INFO FOR DETAIL TEXT
			$sql="select * from shop_items_de where id_item = '".$row2["id_item"]."' limit 1";
			$results=q($sql, $dbshop, __FILE__, __LINE__);
			$row_=mysqli_fetch_array($results);
		
			$title=$row_["title"];
			$description=$row_["short_description"];
			// DETAIL TEXT
			$txt='<a class="shopitem_title" style="margin-left:0px; width:200px;" href="'.PATH.'online-shop/autoteile/'.$row2["id_item"].'/'.url_encode($title).'" alt="'.$title.'" title="'.$title.'">'.strtoupper($title).'</a>';
			$txt.= '<div class="shopitem_description" style="margin-left:0px; width:197px;">'.$description.'</div>';
		
			$vk=get_prices($row2["id_item"]);
			$vk_preis=number_format($vk["total"],2);
			if (isset($_SESSION["id_user"]) && gewerblich($_SESSION["id_user"]) )
			{
				$txt.='<div style="width:200px;"><span class="shopitem_price" style="margin-left:0px; padding-top:15px;width:200px; text-align:left;">'.t("Verkaufspreis").': € '.$vk_preis.'</span><br/><span><small><a href="'.PATHLANG.'online-shop/versandkosten/" target="_blank">'.t("zzgl. Versandkosten").'</a></small></span></div>';
			} 
			else 
			{
				$txt.='<div style="width:200px;"><span class="shopitem_price" style="margin-left:0px; padding-top:15px;width:200px; text-align:left;">'.t("Verkaufspreis").': € '.$vk_preis.'</span><br/><span><small>incl. MwSt. / <a href="'.PATHLANG.'online-shop/versandkosten/" target="_blank">'.t("zzgl. Versandkosten").'</a></small></span></div>';
			}
			//if($_SESSION["id_user"]==49352)
			{
				$txt.='<div style="width: 200px"><input class="cart_add_amount" id="article'.$row2["id_item"].'" type="text" size="1" value="1" onkeyup="cart_add_enter('.$row2["id_item"].')"> <input class="cart_add_button" type="button" onclick="javascript: cart_add('.$row2["id_item"].', 0)" value="'.t("In den Warenkorb").'"></div>';			
			}
			if($pl_type==3)
				$txt.='<div style="width: 200px"><br /><span style="color: red"><b>'.t("Angebot gültig bis: ").date("j.n.Y",(int)$offer_end).' '.date("G:i", (int)$offer_end).'</b></span></div>';
			else
				$txt.='<div style="width: 200px"><br /><span style="color: red"><b>'.t("Angebot gültig bis: ").date("j.n.Y",(int)$offer_end).'</b></span></div>';	
	
			//ANZEIGE PICTURE
				
			echo '<div class="picbox2" id="picbox2'.$row2["id_item"].'"style="margin:5px 1px 5px 15px; width:218px; height:135px; float:left;position:relative;">';
			echo '<a href="'.PATH.'online-shop/autoteile/'.$row2["id_item"].'/'.url_encode($title).'" alt="'.$title.'" title="'.$title.'"><img id="picture'.$row2["id_item"].'" src="'.$path.'" style="width:217px; height:135px; z-index:0; border:1px solid #999; position: relative;" /></a>';
			//echo '<img id="picture'.$row2["id_item"].'" src="'.$path.'" style="width:50px; height:50px; z-index:1; border:1px solid #999; position: absolute; top: 30px; left: 70px" />';
			//echo '<span style="color: red; font-weight: bold; font-size: 25px; width:50px; height:50px; z-index:1; position: absolute; top: 10px; left: 170px">€$%</span>';
			if(number_format($vk["percentage"],0)!='0')
			{
				echo '<img id="picture'.$row2["id_item"].'" src="'.PATH.'images/angebote.png" style="width:70px; height:70px; z-index:1; position: absolute; top: 5px; left: 145px" />';
				echo '<span style="font-weight: bold; font-size: 12px; width:50px; height:50px; z-index:2; position: absolute; top: 24px; left: 165px">-'.number_format($vk["percentage"],0).'%</span>';
			}
			echo '<div class="DetailBox" style="display:none; margin:6px; margin-top:-3px; width: 207px;">'.$txt.'</div>';
			echo '</div>';
			//	echo '</div>';
			//$pic_count++; // ABBRUCHBEDINGUNG
		}
/*
		else {
			$path=PATH."images/library/mapco_frame_noimage.jpg";
		}
*/			
	}
	echo '<div style="margin-top:6px; margin-right:3px; width:18px; height:135px; float:right;">';
	echo '<img style="height:135px; border:0; padding:0; float:left; cursor:pointer;" src="'.PATH.'images/testordner/Down.png" alt="weitere Artikel" title="weitere Artikel" onclick="next_Pics3('.$_POST["OfferType"].');" />';
	echo '</div>';

?>
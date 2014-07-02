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
	echo '<img style="height:135px; border:0; padding:0; float:left; cursor:pointer;" src="'.PATH.'images/testordner/Up.png" alt="vorherige Artikel" title="vorherige Artikel" onclick="prev_Pics('.$_POST["OfferType"].');" />';
	echo '</div>';
		
//	$res=q("select * from shop_offers where offertype_id = '".$_POST["OfferType"]."';", $dbshop, __FILE__, __LINE__);
//	$res=q("SELECT ArtNr FROM t_200 WHERE GART IN (00051, 00191, 00284, 00914, 00915, 02640, 02641, 03236) ORDER BY RAND() LIMIT 30;", $dbshop, __FILE__, __LINE__);
/*
	while ($row=mysqli_fetch_array($res) and $pic_count<$promotionBoxPicStart+3) 
	{
	if ($promotionBoxPicStart<=$pic_count) {
*/		
		$pic_count=0;
	$results99=q("SELECT * FROM shop_lists_items WHERE list_id=328 ORDER BY RAND() LIMIT 3;", $dbshop, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results99) )
	{
		$res2=q("SELECT * FROM shop_items WHERE id_item='".$row["item_id"]."';", $dbshop, __FILE__, __LINE__);
		$row2=mysqli_fetch_array($res2);
//		$res4=q("select a.*, b.* from cms_files a, cms_articles_images b where b.article_id='".$row2["article_id"]."' and a.id_file=b.file_id and a.imageformat_id = '9' LIMIT 1;", $dbweb, __FILE__, __LINE__);
		$res4=q("select a.*, b.* from cms_files a, cms_articles_images b where b.article_id='".$row2["article_id"]."' and a.original_id=b.file_id and a.imageformat_id = '9' LIMIT 1;", $dbweb, __FILE__, __LINE__);
		if (mysqli_num_rows($res4)>0) {
		//	if ($pic_count>=$index) {
			$row4=mysqli_fetch_array($res4);
			// PATH FOR PIC
			$folder=substr($row4["id_file"], 0, 4);
			$path="https://www.mapco.de/files/".$folder."/".$row4["id_file"].".".$row4["extension"];
				
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
				$txt.='<div style="width:200px;"><span class="shopitem_price" style="margin-left:0px; padding-top:15px;width:200px; text-align:left;">'.("Verkaufspreis").': € '.$vk_preis.'</span><br/><span><small>incl. MwSt. / <a href="'.PATHLANG.'online-shop/versandkosten/" target="_blank">'.t("zzgl. Versandkosten").'</a></small></span></div>';
			}
//			$txt.='<div style="width:200px;"><span class="shopitem_price" style="margin-left:0px; padding-top:15px; width:200px; text-align:left;">'.t("Verkaufspreis").': € '.number_format($vk["brutto"],2).'</span><br/><span><small><a href="'.PATHLANG.'online-shop/versandkosten/" target="_blank">'.t("zzgl. Versandkosten").'</a></small></span></div>';
	
			//ANZEIGE PICTURE
				
			//	echo '<div class="picbox" id="picbox'.$row["item_id"].'" style="width:230px; height:153px; border:1px solid #999; float:left; display:inline; z-index:0; margin:5px; padding:5px; border-radius:10px;"  >';
			echo '<div class="picbox2" id="picbox2'.$row2["id_item"].'"style="margin:5px 1px 5px 15px; width:208px; height:135px; z-index:100; float:left;"  ;>';
			echo '<img id="picture'.$row2["id_item"].'" src="'.$path.'" style="width:207px; height:135px; z-index:0; border:1px solid #999;" />';
			echo '<div class="DetailBox" style="display:none; margin:6px; margin-top:-3px;">'.$txt.'</div>';
			echo '</div>';
			//	echo '</div>';
			$pic_count++; // ABBRUCHBEDINGUNG
		}
/*
		else {
			$path="https://www.mapco.de/images/library/mapco_frame_noimage.jpg";
		}
*/			
	}
	echo '<div style="margin-top:6px; margin-right:3px; width:18px; height:135px; float:right;">';
	echo '<img style="height:135px; border:0; padding:0; float:left; cursor:pointer;" src="'.PATH.'images/testordner/Down.png" alt="weitere Artikel" title="weitere Artikel" onclick="next_Pics('.$_POST["OfferType"].');" />';
	echo '</div>';

?>
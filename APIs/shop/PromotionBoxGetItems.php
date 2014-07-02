<?php

	include("../functions/cms_url_encode.php");
	include("../functions/shop_get_prices.php");
	include("../functions/mapco_gewerblich.php");
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
	echo '<div style="width:20px; margin-top:6px; margin-left:3px; height:150px; float:left; z-index:101">';
	echo '<img style="border:0; padding:0; float:left; cursor:pointer;" src="'.PATH.'images/testordner/Up.png" alt="vorherige Artikel" title="vorherige Artikel" onclick="prev_Pics('.$_POST["OfferType"].');" />';
	echo '</div>';
		
	//$res=q("select * from shop_offers where offertype_id = '".$_POST["OfferType"]."';", $dbshop, __FILE__, __LINE__);
$now=time();
$res=q("SELECT * FROM shop_offers WHERE offertype_id='".$_POST["OfferType"]."' and '".$now."' >= shop_offers.from and '".$now."' <= shop_offers.until ORDER BY RAND();", $dbshop, __FILE__, __LINE__);
	$pic_count=0;
	while ($row=mysqli_fetch_array($res) and $pic_count<$promotionBoxPicStart+3) 
	{
	if ($promotionBoxPicStart<=$pic_count) {
		$res2=q("SELECT * FROM shop_items WHERE id_item='".$row["item_id"]."';", $dbshop, __FILE__, __LINE__);
		$row2=mysqli_fetch_array($res2);
		$res4=q("select a.*, b.* from cms_files a, cms_articles_images b where b.article_id='".$row2["article_id"]."' and a.original_id=b.file_id and a.imageformat_id = '9' LIMIT 1;", $dbweb, __FILE__, __LINE__);
		if (mysqli_num_rows($res4)>0) {
		//	if ($pic_count>=$index) {
			$row4=mysqli_fetch_array($res4);
			// PATH FOR PIC
			$folder=substr($row4["id_file"], 0, 4);
			$path=PATH."files/".$folder."/".$row4["id_file"].".".$row4["extension"];
		}
		else {
			$path=PATH."images/library/mapco_frame_noimage.jpg";
		}
				
		// GET INFO FOR DETAIL TEXT
		$sql="select * from shop_items_de where id_item = '".$row["item_id"]."' limit 1";
		$results=q($sql, $dbshop, __FILE__, __LINE__);
		$row_=mysqli_fetch_array($results);
	
		$title=$row_["title"];
		$description=$row_["short_description"];
		// DETAIL TEXT
		$txt='<a class="shopitem_title" style="margin-left:0px; width:200px;" href="'.PATH.'online-shop/autoteile/'.$row["item_id"].'/'.url_encode($title).'" alt="'.$title.'" title="'.$title.'">'.strtoupper($title).'</a>';
		$txt.= '<div class="shopitem_description" style="margin-left:0px; width:200px;">'.$description.'</div>';
	
		$vk=get_prices($row["item_id"]);
		$vk_preis=number_format($vk["total"],2);
		if (isset($_SESSION["id_user"]) && gewerblich($_SESSION["id_user"]) )
		{
			$txt.='<div style="width:200px;"><span class="shopitem_price" style="margin-left:0px; padding-top:15px;width:200px; text-align:left;">'.t("Verkaufspreis").': € '.$vk_preis.'</span><br/><span><small><a href="'.PATHLANG.'online-shop/versandkosten/" target="_blank">'.t("zzgl. Versandkosten").'</a></small></span></div>';
		} 
		else 
		{
			$txt.='<div style="width:200px;"><span class="shopitem_price" style="margin-left:0px; padding-top:15px;width:200px; text-align:left;">'.t("Verkaufspreis").': € '.$vk_preis.'</span><br/><span><small>incl. MwSt. / <a href="'.PATHLANG.'online-shop/versandkosten/" target="_blank">'.t("zzgl. Versandkosten").'</a></small></span></div>';
		}
		

		//ANZEIGE PICTURE
			
		echo '<div class="picbox2" id="picbox2'.$row["item_id"].'"style="margin:5px; margin-right:0px; width:230px; height:150px; z-index:100; float:left;"  ;>';
		echo '<img id="picture'.$row["item_id"].'" src="'.$path.'" style="width:230px; height:150px; z-index:0; border:1px solid #999;" />';
		echo '<div class="DetailBox" style="display:none; margin:6px; margin-top:-3px;">'.$txt.'</div>';
		echo '</div>';
	}
	$pic_count++; // ABBRUCHBEDINGUNG
	}
	echo '<div style="margin-top:6px; margin-right:3px; width:20px; height:150px; float:right;">';
	echo '<img style="border:0; padding:0; float:left; cursor:pointer;" src="'.PATH.'images/testordner/Down.png" alt="weitere Artikel" title="weitere Artikel" onclick="next_Pics('.$_POST["OfferType"].');" />';
	echo '</div>';

?>
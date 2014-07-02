<?php

	/*************************
	*		  SOA2			 *
	*************************/
	
	include("../modules/php-barcode/php-barcode.php");
	
	$required=array("item_id"	=> "numericNN");
	
	check_man_params($required);

	$xml='';
	
	$ean=='';
	
	//if(!file_exists(PATH."images/barcodes/".$_POST["item_id"].".png"))
	//{
		$res=q("SELECT * FROM shop_items WHERE id_item=".$_POST["item_id"].";", $dbshop, __FILE__, __LINE__);
		if(mysqli_num_rows($res)==1)
		{
			$shop_items=mysqli_fetch_assoc($res);
			if($shop_items["EAN"]!="" and !file_exists(PATH."images/barcodes/".$shop_items["EAN"].".png"))
			{
				$ean=$shop_items["EAN"];
				$im     = imagecreatetruecolor(300, 50) or die('Cannot Initialize new GD image stream');  
				$black  = ImageColorAllocate($im,0x00,0x00,0x00);  
				$white  = ImageColorAllocate($im,0xff,0xff,0xff);  
				imagefilledrectangle($im, 0, 0, 300, 50, $white);  
				$data = Barcode::gd($im, $black, 150, 25, 0, "ean13", $shop_items["EAN"], 3, 50);
				//imagepng($im, "../images/barcodes/".$_POST["item_id"].".png");
				imagepng($im, "../images/barcodes/".$shop_items["EAN"].".png");
				imagedestroy($im);
			}
		}
	//}
	
	$xml='<ean><![CDATA['.$ean.']]></ean>';
	
	echo $xml;
	
/*	
	$im     = imagecreatetruecolor(300, 50) or die('Cannot Initialize new GD image stream');  
	$black  = ImageColorAllocate($im,0x00,0x00,0x00);  
	$white  = ImageColorAllocate($im,0xff,0xff,0xff);  
	imagefilledrectangle($im, 0, 0, 300, 50, $white);  
	$data = Barcode::gd($im, $black, 150, 25, 0, "ean13", "4043605841919", 3, 50);
	imagepng($im, "images/barcodes/test.png");
	imagedestroy($im);
	echo '<img src="'.PATH.'images/barcodes/test.png">';
	echo print_r($data);
*/
?>
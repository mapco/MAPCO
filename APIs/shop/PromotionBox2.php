<script type="text/javascript">

	function show_items(OfferType)
	{
		var promotionBoxPicStart=$("#promotionBoxPicStart"+OfferType).val();
		//$.post("<?php echo PATH; ?>soa/", {API: "shop", Action: "PromotionBoxGetItems2", usertoken: "merci2664", OfferType:OfferType},
		//$.post("<?php echo PATH; ?>soa/", {API: "shop", Action: "PromotionBoxGetItems2", OfferType:OfferType, promotionBoxPicStart:promotionBoxPicStart},
		$.post("<?php echo PATH; ?>soa/", {API: "shop", Action: "PromotionBoxGetItems2", OfferType:OfferType},
			function (data) {
				$("#PicBox"+OfferType).html(data);
				$(".picbox2").hover(
					function(e) {
						$(this).children(".DetailBox").slideDown(300);
					//	$(this).parent(".picbox").css("border-bottom-left-radius", 0);
					//	$(this).parent(".picbox").css("border-bottom-right-radius", 0);	
					},
					function(e) {
						$(this).children(".DetailBox").slideUp(300);
					//	$(this).parent(".picbox").css("border-bottom-left-radius", 10)
					//	$(this).parent(".picbox").css("border-bottom-right-radius", 10);
					}
				); // HOVER
					
			} // FUNCTION DATA
		); // $.post
		
	}
	
	function next_Pics(OfferType)
	{
		var index=$("#promotionBoxPicStart"+OfferType).val();
		if (index<$("#offers"+OfferType).val()) 
		{ 
			$("#promotionBoxPicStart"+OfferType).val(index*1+3);
		}
		show_items(OfferType);
	}
	function prev_Pics(OfferType)
	{
		var index=$("#promotionBoxPicStart"+OfferType).val();
		if (index>0) 
		{ 
			$("#promotionBoxPicStart"+OfferType).val(index*1-3);
		}
		show_items(OfferType);

	}


</script>

<?php

$now=time();
//$res=q("SELECT item_id FROM shop_offers WHERE offertype_id='".$_POST["OfferType"]."' LIMIT 10;", $dbshop, __FILE__, __LINE__);
$res=q("SELECT * FROM shop_lists_items WHERE list_id=328;", $dbshop, __FILE__, __LINE__);
$OK=false;
$count=0;
$offers=mysqli_num_rows($res);
if ($offers>0) $OK=true;
//echo mysqli_num_rows($res);
if ($OK) 
{	
echo '<input type="hidden" id="promotionBoxPicStart'.$_POST["OfferType"].'" value=0>';
echo '<input type="hidden" id="offers'.$_POST["OfferType"].'" value='.$offers.'>';

	//OFFERTYPE - HEADLINE
$res=q("select * from shop_offertypes where id_offertype = '".$_POST["OfferType"]."';", $dbshop, __FILE__, __LINE__);
$row=mysqli_fetch_array($res);
//	echo '<div class="promotionHead">';
//echo '<h2>'.$row["title"].'</h2>';
//	echo '</div>';

echo '<div class="promotionBox">';


	echo '<div id="PicBox'.$_POST["OfferType"].'" ></div>';
	echo '<script>show_items('.$_POST["OfferType"].');</script>';

echo '</div>';
}
?>
<script type="text/javascript">

	function show_items3(OfferType)
	{
		var promotionBoxPicStart=$("#promotionBoxPicStart"+OfferType).val();
		$.post("<?php echo PATH; ?>soa/", {API: "shop", Action: "PromotionBoxGetItems3", OfferType:OfferType},
			function (data) {
				$("#PicBox"+OfferType).html(data);
				$(".picbox2").hover(
					function(e) {
						$(this).children(".DetailBox").slideDown(300);
					},
					function(e) {
						$(this).children(".DetailBox").slideUp(300);
					}
				); // HOVER
					
			} // FUNCTION DATA
		); // $.post
		
	}
	
	function next_Pics3(OfferType)
	{
/*		
		var index=$("#promotionBoxPicStart"+OfferType).val();
		//alert(index);
		if (index<$("#offers"+OfferType).val()) 
		{ 
			$("#promotionBoxPicStart"+OfferType).val(index*1+3);
		}
*/
		show_items3(OfferType);
	}
	function prev_Pics3(OfferType)
	{
/*		
		var index=$("#promotionBoxPicStart"+OfferType).val();
		if (index>0) 
		{ 
			$("#promotionBoxPicStart"+OfferType).val(index*1-3);
		}
*/		
		show_items3(OfferType);

	}


</script>

<?php
/*
	$now=time();
	$res=q("SELECT item_id FROM shop_offers WHERE offertype_id='".$_POST["OfferType"]."' LIMIT 10;", $dbshop, __FILE__, __LINE__);
	$OK=false;
	$count=0;
	$offers=mysqli_num_rows($res);
	if ($offers>0) $OK=true;
*/
/*	
	$OK=false;
	$res=q("SELECT * FROM shop_offers WHERE offer_start<=".time()." AND offer_end>=".time().";", $dbshop, __FILE__, __LINE__);
	if(mysqli_num_rows($res)>0)
	{
		while($shop_offers=mysqli_fetch_assoc($res))
		{
			$res2=q("SELECT * FROM shop_lists WHERE id_list=".$shop_offers["list_id"].";", $dbshop, __FILE__, __LINE__);
			if(mysqli_num_rows($res2)>0) $OK=true;
		}
	}
*/
	$OK=true;
	if ($OK) 
	{	
		echo '<input type="hidden" id="promotionBoxPicStart'.$_POST["OfferType"].'" value=0>';
		echo '<input type="hidden" id="offers'.$_POST["OfferType"].'" value='.$offers.'>';
		
			//OFFERTYPE - HEADLINE
		//$res=q("select * from shop_offertypes where id_offertype = '".$_POST["OfferType"]."';", $dbshop, __FILE__, __LINE__);
		//$row=mysqli_fetch_array($res);
		
		//	echo '<div class="promotionHead">';
		//echo '<h2>'.$row["title"].'</h2>';
		//	echo '</div>';
		
		echo '<div class="promotionBox" style="border-top: none; height: 155px; margin-bottom: 10px; margin-top: 0px">';
			echo '<div id="PicBox'.$_POST["OfferType"].'" ></div>';
			echo '<script>show_items3('.$_POST["OfferType"].');</script>';
		echo '</div>';
	}
?>
<?php
	include("config.php");
//	$title='MAPCO Autoteile Shop / KFZ Teile 24 Stunden am Tag günstig kaufen!';
	include("templates/".TEMPLATE."/header.php");
	include("functions/shop_show_item.php");
	include("functions/shop_itemstatus.php");
	include("functions/mapco_gewerblich.php");
	include("functions/mapco_get_titles.php");	
	include("functions/mapco_motorart.php");
	include("functions/mapco_baujahr.php");
	include("functions/cms_t.php");
	include("functions/cms_tl.php");
	include("functions/cms_url_encode.php");

?>
<script type="text/javascript">

	function view_categorybox()
	{
		//$.post("<?php echo PATH; ?>soa/", {API: "shop", Action: "CategoryBox", usertoken: "merci2664", idtag: "shopmenu"},
		$.post( "<?php echo PATH; ?>soa/", {API: "shop", Action: "CategoryBox", idtag: "shopmenu"}, function ( data ) {
			$( "#categorybox" ).html( data );
		} );
	}
	
	function view_promotionsbox( OfferType )
	{
		//$.post("<?php echo PATH; ?>soa/", {API: "shop", Action: "PromotionBox", usertoken: "merci2664", OfferType: OfferType},
		$.post( "<?php echo PATH; ?>soa/", {API: "shop", Action: "PromotionBox", OfferType: OfferType}, function ( data ) {
				$( "#promotionsbox" + OfferType ).html( data );
		} );
	}
	
	function view_promotionsbox3( OfferType )
	{
		$.post( "<?php echo PATH; ?>soa/", {API: "shop", Action: "PromotionBox3", OfferType: OfferType}, function ( data ) {
			$( "#promotionsbox" + OfferType ).html( data );
		} );
	}
</script>
<?php
//	include("templates/".TEMPLATE."/cms_leftcolumn.php");
	include("templates/".TEMPLATE."/cms_leftcolumn_shop.php");
	echo '<div id="mid_right_column">';


	//Regional-Center News
	if( ( $_SESSION["id_site"] >= 8 and $_SESSION["id_site"] <= 15 ) or $_SESSION['id_site'] == 17 )
	{
		$published = FALSE;
		
		// get label_id
		$label_id = 0;
		$res = q( "SELECT * FROM cms_labels WHERE site_id=" . $_SESSION['id_site'] . " AND label='RC News'", $dbweb, __FILE__, __LINE__ );
		if ( mysqli_num_rows( $res ) > 0 )
		{
			$cms_labels = 	mysqli_fetch_assoc( $res );
			$label_id = 	$cms_labels['id_label'];
		}
		
		$results = q( "SELECT * FROM cms_articles_labels WHERE label_id=" . $label_id . " ORDER BY ordering;", $dbweb, __FILE__, __LINE__ );
		while( $row = mysqli_fetch_array( $results ) )
		{
			$results2=q("SELECT * FROM cms_articles AS a, cms_languages AS b WHERE a.id_article=".$row["article_id"]." AND a.language_id=b.id_language;", $dbweb, __FILE__, __LINE__);
			$row2=mysqli_fetch_array($results2);
			if($row2["published"]>0)
			{
				echo '<div id="shop_news">';
				echo '<h1>'.t("Aktuelles").'!</h1>';
				echo '<h2>'.$row2["title"].'</h2>';
				
				$results3=q("SELECT * FROM cms_articles_images WHERE article_id=".$row["article_id"]." ORDER BY ordering LIMIT 1;", $dbweb, __FILE__, __LINE__);
				if (mysqli_num_rows($results3)>0)
				{
					$row3=mysqli_fetch_array($results3);
					$results4=q("SELECT * FROM cms_files WHERE original_id=".$row3["file_id"]." AND imageformat_id=2;", $dbweb, __FILE__, __LINE__);
					if ( mysqli_num_rows($results4)>0 )
					{
						$row4=mysqli_fetch_array($results4);
						$filename='files/'.floor(bcdiv($row4["id_file"], 1000)).'/'.$row4["id_file"].'.'.$row4["extension"];
						echo '<a href="'.PATHLANG.'news/'.$row["article_id"].'/'.url_encode($row2["title"]).'" title="'.$row2["title"].'">';
						echo '	<img src="'.PATH.$filename.'" alt="'.$row2["title"].'" title="'.$row2["title"].'" />';
						echo '</a>';
					}
				}
				if ($row2["introduction"]!="") 
				{
					if ($row2["format"]==0) echo nl2br($row2["introduction"]);
					else echo $row2["introduction"];
				}
				else echo substr($row2["article"], 0, strpos($row2["article"], "</p>"));
				echo '<p><a style="font-size:14px; float:right;" href="'.PATHLANG.'news/'.$row["article_id"].'/'.url_encode($row2["title"]).'" title="'.$row2["title"].'">'.t("weiterlesen").'</a></p>';
				echo '</div>';		
				$published=TRUE;
			}
			if($published==TRUE) break;
		}//end while
	}//end if( $_SESSION["id_site"]>=8 and $_SESSION["id_site"]<=15 )


	//AKTION DER WOCHE BOX
	if ( ( ( $_SESSION["id_site"] >= 8 and $_SESSION["id_site"] <= 15 ) or $_SESSION["id_site"] == 17 ) and isset( $_SESSION["id_user"] ) and $_SESSION['id_user'] != 65391 and $_SESSION['id_user'] != 101111 and $_SESSION['id_user'] != 3715 and $_SESSION['id_user'] != 101112 and $_SESSION['id_user'] != 114664 ) {
		
		$pb_show = 0;
		
		//PRÜFEN, OB KUNDE HANDEL ODER WERKSTATT 
		$pb_show_2 = 0;
		$res3 = q( "SELECT * FROM cms_users WHERE id_user=" . $_SESSION["id_user"], $dbweb, __FILE__, __LINE__ );
		$cms_users = mysqli_fetch_array( $res3 );
		$res4 = q( "SELECT * FROM kunde WHERE ADR_ID=" . $cms_users["idims_adr_id"] . " AND GEWERBE=1 AND PREISGR IN (3,4,5,6,7)", $dbshop, __FILE__, __LINE__ );
		if ( mysqli_num_rows( $res4 ) > 0 ) $pb_show_2 = 1;
		
		//RABATT ÜBER AKTIONSLISTEN
		$res = q( "SELECT * FROM shop_offers WHERE offer_start<=" . time() . " AND offer_end>=" . time() . ";", $dbshop, __FILE__, __LINE__ );
		if ( mysqli_num_rows( $res ) > 0 ) {
			while ( $shop_offers = mysqli_fetch_assoc( $res ) ) {
				$res2 = q( "SELECT * FROM shop_lists WHERE id_list=" . $shop_offers["list_id"] . ";", $dbshop, __FILE__, __LINE__ );
				if ( mysqli_num_rows( $res2 ) > 0 ) $pb_show = 1;
			}
		}
		if ( $pb_show == 1 and $pb_show_2 == 1 ) {	
			//echo '<p>';
			echo '<div>';
			echo '<div id="suche_head" style="border-color: #999; border-bottom: none;border-style: solid; border-width: 1px;">' . t( "Artikel der Woche" ) . ' / ' . t( 'Aktion der Woche' ) .  '</div>';
			echo '<div id="promotionsbox3"></div>';
			echo '</div>';
			echo '<script>view_promotionsbox3(3);</script>';
			//echo '</p>';
		}
	}

	//search functions
	echo '<div id="suche" style="display:inline">';
	echo '<div id="suche_head">'.t("FAHRZEUG-TEILESUCHE").'</div>';
	echo '<div id="pkw_suche">';
		include("modules/shop_start_searchbycar.php");
	echo '</div>';
	echo '<div id="kba_suche" style="display:inline">';
		include("modules/shop_start_searchbykba.php");
	echo '</div>';
	echo '<div id="teile_suche" style="display:inline">';
		include("modules/shop_start_searchbynumber.php");
	echo '</div>';
	echo '</div>'; // DIV ID="SUCHE"

	//promotions box
	echo '<div id="promotionsbox1">'; // 1=> OfferType 1 == NEUVORSTELLUNG
	echo '</div>';
	echo '<script>view_promotionsbox(1);</script>';
	echo '<div id="promotionsbox2">'; // 1=> OfferType 2 == Angebot
	echo '</div>';
	echo '<script>view_promotionsbox(2);</script>';
	echo '<div id="categorybox">';
	echo '</div>';
	echo '<script>view_categorybox();</script>';
	echo '</div>';


	include("templates/".TEMPLATE."/footer.php");
?>
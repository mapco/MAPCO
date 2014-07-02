<?php
	include("functions/cms_tl.php");
	if( isset($_GET["getvars1"]) ) $_GET["id_article"]=$_GET["getvars1"];
	if( isset($_GET["getvars2"]) ) $_GET["title"]=$_GET["getvars2"];

	$domain = str_replace("www.", "", $_SERVER['HTTP_HOST']);

	include("functions/cms_url_encode.php");
	if ( isset($_GET["id_article"]) and $_GET["id_article"]>0 )
	{
		$results=q("SELECT * FROM cms_articles WHERE id_article=".$_GET["id_article"].";", $dbweb, __FILE__, __LINE__);
		$row=mysqli_fetch_array($results);
		//translated version available?
		$results=q("SELECT * FROM cms_articles WHERE article_id=".$_GET["id_article"]." AND language_id=".$_SESSION["id_language"].";", $dbweb, __FILE__, __LINE__);
		if( mysqli_num_rows($results)>0 )
		{
			$row=mysqli_fetch_array($results);
		}
		
		//SEO link check
		$slug=tl(819, "alias").$_GET["id_article"]."/".url_encode($row["title"]);
		if( $slug!=$_GET["url"] )
		{
			/*
			echo $slug;
			echo "<br>\n";
			echo $_GET["url"];
			exit;
			*/
			header("HTTP/1.1 301 Moved Permanently");
			header("location: ".PATHLANG.$slug );
			exit;
		}
		
		//SEO
		if($row["meta_title"]!="") $meta_title=$row["meta_title"]; else $meta_title=$row["title"];
		if($row["meta_description"]!="") $meta_description=$row["meta_description"]; else $meta_description=htmlentities($row["introduction"]);
		$meta_keywords=$row["meta_keywords"];
	}
	include("templates/".TEMPLATE."/header.php");
	include("functions/cms_show_article.php");
	include("functions/mapco_gewerblich.php");
	include("functions/cms_url_encode.php");

	//left column
	include("templates/".TEMPLATE."/cms_leftcolumn.php");
?>

<script type="text/javascript">
	
	function view_promotionsbox3(OfferType)
		{
			$.post("<?php echo PATH; ?>soa/", {API: "shop", Action: "PromotionBox3", OfferType: OfferType},
				function (data)
				{
					$("#promotionsbox"+OfferType).html(data);
				}
			);
	
		}



</script>

<?php	
	//AKTION DER WOCHE BOX
//			if( $_SESSION["id_site"]>=8 and $_SESSION["id_site"]<=15 and ($_SESSION["id_user"]==49352 or $_SESSION["id_user"]==21371))
	if( (($_SESSION["id_site"]>=8 and $_SESSION["id_site"]<=15) or $_SESSION["id_site"]==17) and isset($_SESSION["id_user"]))
	{
		echo '<div id="mid_right_column">';
		$pb_show=0;
		
		//PRÜFEN, OB KUNDE HANDEL ODER WERKSTATT 
		$pb_show_2=0;
		$res3=q("SELECT * FROM cms_users WHERE id_user=".$_SESSION["id_user"], $dbweb, __FILE__, __LINE__);
		$cms_users=mysqli_fetch_array($res3);
		$res4=q("SELECT * FROM kunde WHERE ADR_ID=".$cms_users["idims_adr_id"]." AND GEWERBE=1 AND PREISGR IN (3,4,5,6,7)", $dbshop, __FILE__, __LINE__);
		if(mysqli_num_rows($res4)>0) $pb_show_2=1;
		
		//RABATT ÜBER AKTIONSLISTEN
		$res=q("SELECT * FROM shop_offers WHERE offer_start<=".time()." AND offer_end>=".time().";", $dbshop, __FILE__, __LINE__);
		if(mysqli_num_rows($res)>0)
		{
			while($shop_offers=mysqli_fetch_assoc($res))
			{
				$res2=q("SELECT * FROM shop_lists WHERE id_list=".$shop_offers["list_id"].";", $dbshop, __FILE__, __LINE__);
				if(mysqli_num_rows($res2)>0) $pb_show=1;
			}
		}
/*		
		//if($_SESSION["id_user"]==49352)
		{
			//RABATT ÜBER IDIMS-PREISLISTEN (20214-HÄNDLER 20215-ALLE ANDEREN)
			$res=q("SELECT * FROM cms_users WHERE id_user=".$_SESSION["id_user"].";", $dbweb, __FILE__, __LINE__);
			$cms_users=mysqli_fetch_assoc($res);
			$res2=q("SELECT * FROM kunde WHERE ADR_ID=".$cms_users["idims_adr_id"].";", $dbshop, __FILE__, __LINE__);
			$kunde=mysqli_fetch_assoc($res2);
			
			if($kunde["PREISGR"]==6 or $kunde["PREISGR"]==7)
			{
				$res3=q("SELECT * FROM prpos WHERE LST_NR=20214 AND GUELTIG_AB<='".date("Y-m-d", time())."' AND GUELTIG_BIS>='".date("Y-m-d", time())."';", $dbshop, __FILE__, __LINE__);
				if(mysqli_num_rows($res3)>0) $pb_show=1;
			}
			else
			{
				$res3=q("SELECT * FROM prpos WHERE LST_NR=20215 AND GUELTIG_AB<='".date("Y-m-d", time())."' AND GUELTIG_BIS>='".date("Y-m-d", time())."';", $dbshop, __FILE__, __LINE__);
				if(mysqli_num_rows($res3)>0) $pb_show=1;
			}
			//echo date("Y-m-d", time());
			//echo strtotime("2013-12-10")."<br />";
			//echo date("Y-m-d", 1391000857)."<br />";
			//echo strtotime(date("Y-m-d", 1391000857));
		}
*/		
		if($pb_show==1 and $pb_show_2==1)
		{	
			//echo '<p>';
			echo '<div>';
			echo '<div id="suche_head" style="border-color: #999; border-bottom: none;border-style: solid; border-width: 1px;height:22px;font-size:16px">'.t("Aktion der Woche").'</div>';
			echo '<div id="promotionsbox3"></div>';
			echo '</div>';
			echo '<script>view_promotionsbox3(3);</script>';
			//echo '</p>';
		}
		echo '</div>';
	}
	
	// Suche Box
	if( !isset($_GET["id_article"]) )
	{
		//if($_SESSION["id_user"]==49352) echo print_r($_SESSION);
		echo '<div id="suche_box" style="display:inline">';
		echo '<h3>'.t("Fahrzeug-Teilesuche").'</h3>';
		echo '<div id="suche_box_content" style="display:inline">';
		
			echo '<div id="pkw_suche" style="display:inline">';
				include("modules/shop_start_searchbycar.php");
			echo '</div>';
		
			echo '<div id="kba_suche" style="display:inline">';
				include("modules/shop_start_searchbykba.php");
			echo '</div>';
			
		echo '</div>'; // DIV ID="suche_box_content"
		echo '</div>'; // DIV ID="suche_box"
	}

	//ImageSlider
/*
	if (!isset($_GET["id_article"]))
	{
		$slider="";
		$slider_thumb="";
		$results=q("SELECT id_language FROM cms_languages WHERE code='".$_SESSION["lang"]."' LIMIT 1;", $dbweb, __FILE__, __LINE__);
		$row=mysqli_fetch_array($results);
		$results=q("SELECT a.* FROM cms_articles AS a, cms_articles_labels AS b WHERE b.label_id=38 AND a.id_article=b.article_id AND a.language_id=".$row["id_language"]." AND a.published>0 ORDER BY b.ordering;", $dbweb, __FILE__, __LINE__);
		if(mysqli_num_rows($results)>0)
		{
			echo '<div id="mid_right_column">';
			echo'<div id="coin-slider">';
			while($row=mysqli_fetch_array($results))
			{
				$results2=q("SELECT * FROM cms_articles_images WHERE article_id=".$row["id_article"]." ORDER BY ordering LIMIT 1;", $dbweb, __FILE__, __LINE__);
				if (mysqli_num_rows($results2)>0)
				{
					$row2=mysqli_fetch_array($results2);
					$results3=q("SELECT * FROM cms_files WHERE original_id=".$row2["file_id"]." AND imageformat_id IN (21, 22);", $dbweb, __FILE__, __LINE__);
					while($row3=mysqli_fetch_array($results3))
					{
						if ( $row3["imageformat_id"]==21 )
						{
							$filename='files/'.floor(bcdiv($row3["id_file"], 1000)).'/'.$row3["id_file"].'.'.$row3["extension"];
							echo '<a href="'.$row["introduction"].'" title="'.$row["title"].'">';
							echo '	<img src="'.PATH.$filename.'" alt="'.$row["title"].'" title="'.$row["title"].'" />';
							echo '<span>'.$row["article"].'</span></a>';
						}
					}
				}
			}
			echo'</div>';
			echo'<script type="text/javascript">';
			echo'$(document).ready(function() {';
			echo'$("#coin-slider").coinslider({ width:730, height:400, links:true, delay:5000 });';
			echo'});';
			echo'</script>';
			echo'</div>';
		}
	}
*/

	//Videobox
	if (!isset($_GET["id_article"]))
	{
		echo '<div id="videobox" style="width:548px; margin:0px 10px 10px 10px; border:0; padding:10px; background:url('.PATH.'images/videobox_bg.jpg); display:inline; float:left;">';
		$i=0;
		$results=q("SELECT * FROM cms_videos WHERE active=1 ORDER BY ordering LIMIT 4;", $dbweb, __FILE__, __LINE__);
		//automechanika live fix
//		if ( date("H", time())>18 or date("H", time())<9 )
//		{
//			$row=mysqli_fetch_array($results);
//			$autoplay="autoplay";
//		}
		while($row=mysqli_fetch_array($results))
		{
			$i++;
			if ($i==1)
			{
				echo ' <video id="player" style="width:547px; height:308px; background-color:black;" src="'.PATH.'player/videos/'.$row["file"].'" type="video/mp4" controls '.$autoplay.'>';
				echo '<div style="color:white;">Schade – hier käme ein Video, wenn Ihr Browser HTML5 Unterstützung hätte!</div>';
				echo '</video>';
			}
			if ($i!=4) $style=' style="margin:10px 22px 0px 0px; display:inline; float:left;"'; else $style=' style="margin:10px 0px 0px 0px; display:inline; float:left;"';
			echo '<a'.$style.' href="javascript:play(\''.$row["file"].'\');">';
			echo '	<img style="display:inline; float:left;" src="'.PATH.'player/videos/'.$row["preview"].'" alt="'.$row["title"].'" title="'.$row["title"].'" />';
			echo '</a>';
		}
		echo '</div>';
		include("modules/cms_rightcolumn.php");
	}

	//Gewerbskunde?
	$gewerblich=gewerblich($_SESSION["id_user"]);
	
	//news
	echo '<div id="mid_column">';
	if (isset($_GET["id_article"]) and $_GET["id_article"]>0)
	{
		show_article($_GET["id_article"]);
	}
	else
	{
//		echo '<h1>'.t("Aktuelles").'!</h1><hr />';
		$results=q("SELECT * FROM cms_articles_labels WHERE label_id=3 ORDER BY ordering;", $dbweb, __FILE__, __LINE__);
		$i=0;
		while($row=mysqli_fetch_array($results))
		{
			$results2=q("SELECT * FROM cms_articles_labels WHERE article_id=".$row["article_id"]." AND label_id=9;", $dbweb, __FILE__, __LINE__);
			if( mysqli_num_rows($results2)==0 or (mysqli_num_rows($results2)>0 and $gewerblich)  )
			{
				$results2=q("SELECT * FROM cms_articles AS a, cms_languages AS b WHERE a.id_article=".$row["article_id"]." AND a.language_id=b.id_language;", $dbweb, __FILE__, __LINE__);
				$row2=mysqli_fetch_array($results2);
				if($row2["published"]>0)
				{
					$i++;
					if ($i==1) echo '<h1>'.$row2["title"].'</h1>';
					else echo '<h2>'.$row2["title"].'</h2>';
					
					$results3=q("SELECT * FROM cms_articles_images WHERE article_id=".$row["article_id"]." ORDER BY ordering LIMIT 1;", $dbweb, __FILE__, __LINE__);
					if (mysqli_num_rows($results3)>0)
					{
						$row3=mysqli_fetch_array($results3);
						$results4=q("SELECT * FROM cms_files WHERE original_id=".$row3["file_id"]." AND imageformat_id=20;", $dbweb, __FILE__, __LINE__);
						if ( mysqli_num_rows($results4)>0 )
						{
							$row4=mysqli_fetch_array($results4);
							$filename='files/'.floor(bcdiv($row4["id_file"], 1000)).'/'.$row4["id_file"].'.'.$row4["extension"];
							echo '<a href="'.PATHLANG.'news/'.$row["article_id"].'/'.url_encode($row2["title"]).'" title="'.$row2["title"].'">';
							echo '	<img src="'.PATH.$filename.'" alt="'.$row2["title"].'" title="'.$row2["title"].'" />';
							echo '</a>';
							echo '<br /><br />';
						}
					}
					if ($row2["introduction"]!="") 
					{
						if ($row2["format"]==0) echo nl2br($row2["introduction"]);
						else echo $row2["introduction"];
					}
					else echo substr($row2["article"], 0, strpos($row2["article"], "</p>"));
					echo '<p><a style="font-size:14px; float:right;" href="'.PATHLANG.'news/'.$row["article_id"].'/'.url_encode($row2["title"]).'" title="'.$row2["title"].'">'.t("weiterlesen").'</a></p>';
					if ($i<2) echo '<br style="clear:both;" /><hr />';
				}
				if($i==3) break;
			}
		}
		echo '<br style="clear:both;" /><hr />';
		echo '<a style="font-size:14px; float:left;" href="'.PATHLANG.'presse/newsletter-archiv/">'.t("Newsletter-Archiv").'</a>';
		echo '<a style="font-size:14px; float:right;" href="'.PATHLANG.'presse/nachrichten-archiv/">'.t("Nachrichten-Archiv").'</a>';
	}
	echo '</div>';
	
	if (isset($_GET["id_article"]) and $_GET["id_article"]>0) include("templates/".TEMPLATE."/cms_rightcolumn.php");


	include("templates/".TEMPLATE."/footer.php");

?>
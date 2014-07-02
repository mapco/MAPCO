<?php
include("../../mapco_shop_de/functions/cms_t.php");


			$path="";
			
			//Check, ob zur Shop Kategorie ein Bild hinterlegt ist
			$res=q("Select a.file_id, c.extension from cms_articles_images as a, cms_articles as b, cms_files as c where b.introduction = ".$_POST["menuitem_id"]." and b.id_article=a.article_id and c.id_file=a.file_id;", $dbweb, __FILE__, __LINE__);
			if (mysqli_num_rows($res)!=0) {
				$row=mysqli_fetch_array($res);
				$folder=substr($row["file_id"], 0, 4);
				$path=PATH.'files/'.$folder.'/'.$row["file_id"].'.'.$row["extension"];
								
			}
			else
			
			//ZUFALLSBILD AUS ARTIKELKATEGORIE
			{
				

				// Artikel aus Artikelgruppe
				$results2=q("SELECT article_id FROM shop_items WHERE menuitem_id=".$_POST["menuitem_id"].";", $dbshop, __FILE__, __LINE__);

				while ($path=="" && $row2=mysqli_fetch_array($results2)) {
				
				
					$results3=q("SELECT * FROM cms_articles_images WHERE article_id=".$row2["article_id"]." ORDER BY ordering LIMIT 1;", $dbweb, __FILE__, __LINE__);
					$row3=mysqli_fetch_array($results3);
		//			$results4=q("SELECT * FROM cms_files WHERE id_file='".$row3["file_id"]."' AND imageformat_id='9' LIMIT 1;", $dbweb, __FILE__, __LINE__);
					$results4=q("SELECT * FROM cms_files WHERE original_id='".$row3["file_id"]."' AND imageformat_id='9' LIMIT 1;", $dbweb, __FILE__, __LINE__);
					$row4=mysqli_fetch_array($results4);
						
					if ($row4["filename"]<>"") {
						$folder=substr($row4["id_file"], 0, 4);
						$path=PATH.'files/'.$folder.'/'.$row4["id_file"].'.'.$row4["extension"];
				
						
					}
				}
			}
			//PFAD für Link erstellen
			$result=q("SELECT * FROM cms_menuitems WHERE id_menuitem = ".$_POST["menuitem_id"].";", $dbweb, __FILE__, __LINE__);
			$row=mysqli_fetch_array($result);
			//$link=PATHLANG.$row["alias"];
			if ( $row["alias"]!="" ) $link=PATHLANG.$row["alias"];
			else $link=PATH.$row["link"].'?lang='.$_SESSION["lang"].'&id_menuitem='.$row["id_menuitem"];

			if ($path=="") {
				$path="../../images/library/mapco_frame_noimage.jpg";
				echo '<a href="'.str_replace(" ", "%20", $link).'" title="Zeige alle Artikel der Kategorie '.t($row["description"], __FILE__, __LINE__).'" ><img alt="Aktuell ist keine Abbildung vorhanden"  style="float:left; width:420px;" src="'.$path.'" \></a>';
			}
			else {
				echo '<a href="'.str_replace(" ", "%20", $link).'" title="Zeige alle Artikel der Kategorie '.t($row["description"], __FILE__, __LINE__).'" ><img alt="'.$path.'"  style="float:left; width:420px;" src="'.$path.'" \></a>';
			}
				echo '<script>pic_fadein()</script>';

?>
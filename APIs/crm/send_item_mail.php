<?php
	include("../functions/shop_get_prices.php");
	include("../functions/mapco_gewerblich.php");
	include("../functions/cms_send_html_mail.php");

	if ( !isset($_POST["itemList"]) || $_POST["itemList"]=="" )
	{
		echo '<send_item_mailResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Artikelliste nicht gefunden</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine Artikelliste zur Erstellung der Nachricht angegeben werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</send_item_mailResponse>'."\n";
		exit;
	}

	$res=q("SELECT * FROM shop_lists WHERE id_list = ".$_POST["itemList"].";", $dbshop, __LINE__, __FILE__);
	if (mysqli_num_rows($res)==0)
	{
		echo '<send_item_mailResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Artikelliste nicht gefunden</shortMsg>'."\n";
		echo '		<longMsg>Die angegebene Artikelliste konnte nicht gefunden werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</send_item_mailResponse>'."\n";
		exit;
	}
	
	$res=q("SELECT * FROM shop_lists_items WHERE list_id = ".$_POST["itemList"].";", $dbshop, __LINE__, __FILE__);
	if (mysqli_num_rows($res)==0)
	{
		echo '<send_item_mailResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Artikelliste hat keine Einträge</shortMsg>'."\n";
		echo '		<longMsg>Zur angegebenen Artikelliste konnten keine Einträge gefunden werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</send_item_mailResponse>'."\n";
		exit;
	}
	
	else {
		
		$warning=array();
		
		$text = '<table style="border-color:#000000; border-style:solid; border-width:1px; table-layout:fixed; ">';
		
		while ($row=mysqli_fetch_array($res))
		{
			//SHOPITEM-DATEN beziehen
			$res_shop_item=q("SELECT * FROM shop_items WHERE id_item = ".$row["item_id"].";", $dbshop, __LINE__, __FILE__);
			if (mysqli_num_rows($res_shop_item)==0) $warning[$row["item_id"]];
			else
			{
				$row_shop_item=mysqli_fetch_array($res_shop_item);
			}
			//Artikelbild beziehen 
			$res_pic_info=q("select a.*, b.* from cms_files a, cms_articles_images b where b.article_id='".$row_shop_item["article_id"]."' and a.original_id=b.file_id and a.imageformat_id = '9' LIMIT 1;", $dbweb, __FILE__, __LINE__);
			if (mysqli_num_rows($res_pic_info)>0) {
				$row_pic_info=mysqli_fetch_array($res_pic_info);
				// PATH FOR PIC
				$folder=substr($row_pic_info["id_file"], 0, 4);
				$path=PATH."files_thumbnail/".$folder."/".$row_pic_info["id_file"].".".$row_pic_info["extension"];
			}
			else 
			{
				$path=PATH."files_thumbnail/0.jpg";
			}
		
			//Artikelinfos beziehen
			$res_item_info=q("SELECT * FROM shop_items_de WHERE id_item = ".$row["item_id"].";", $dbshop, __FILE__, __LINE__);
			if (mysqli_num_rows($res_item_info)==0) $warning[$row["item_id"]];
			else
			{
				$row_item_info=mysqli_fetch_array($res_item_info);
			}

			//ARTNR beziehen
			$res_item_info2=q("SELECT * FROM shop_items WHERE id_item = ".$row["item_id"].";", $dbshop, __FILE__, __LINE__);
			if (mysqli_num_rows($res_item_info2)==0) $warning[$row["item_id"]];
			else
			{
				$row_item_info2=mysqli_fetch_array($res_item_info2);
			}


				//get vehicles
				$j=0;
				$results=q("SELECT * FROM t_400 WHERE ArtNr='".$row_item_info2["MPN"]."' AND (KritNr=2 OR KritNr=16);", $dbshop, __FILE__, __LINE__);
				while( $row3=mysqli_fetch_array($results) )
				{
					$results2=q("SELECT * FROM vehicles_de WHERE KTypNr='".$row3["KritWert"]."';", $dbshop, __FILE__, __LINE__);
					if ( mysqli_num_rows($results2)>0 )
					{
						$row2=mysqli_fetch_array($results2);
						$bez1[$j]=utf8_decode($row2["BEZ1"]);
						$bez2[$j]=utf8_decode($row2["BEZ2"]);
		//				$bez3[$j]=utf8_decode($row2["BEZ3"]);
						$j++;
					}
				}
				$vehicles="";
				$testbez1="";
				$testbez2="";
		//		$testbez3="";
		//		array_multisort($bez1, $bez2, $bez3);
				array_multisort($bez1, $bez2);
				for($j=0; $j<sizeof($bez1); $j++)
				{
					if ( $testbez1!=$bez1[$j] )
					{
						$vehicles.=$bez1[$j];
						$testbez1=$bez1[$j];
					}
					if ( $testbez2!=$bez2[$j] )
					{
						$vehicles.=" ".$bez2[$j];
						$testbez2=$bez2[$j];
						if ( ($j+1)<sizeof($bez1) ) $vehicles.=", ";
					}
				}

			//VK-Preis ermitteln
			$price=get_prices($row["item_id"], 1, $_POST["account_user_id"]);
			
			//$text.= '<div style="display:inline; border-color:#999; border-style:solid; border-width:1px; padding:5px;">';
			//$text.= '	<div style="float:left">';
			$text.= '<tr>';
			$text.= '	<td rowspan="2" style="border-color:#000000; border-style:solid; border-width:1px; border-collapse:collapse; padding:3px;">';
			$text.= '		<img style="margin:0px; border:0; padding:0; float:left; width:300px;" src="'.$path.'" alt="'.$row_item_info["title"].'" title="'.$row_item_info["title"].'" />';
			$text.= '	</td>';
			$text.= '	<td style="border-color:#000000; border-style:solid; border-width:1px; border-collapse:collapse;">';
			$text.= '		<span style="font-size:16px; font-weight:bold; font-family:Arial; color:#000000;">';
			$text.= 		$row_item_info["title"];
			$text.= '		</span>';
			$text.= '	</td>';
			$text.= '	<td rowspan="2" style="width:70px; border-color:#000000; border-style:solid; border-width:1px; border-collapse:collapse;">';
			$text.= '		<span style="font-size:14px; font-weight:bold; font-family:Arial; color:#000000;">';
			$text.= 'Ihr Preis';
			$text.= '		</span><br />';			
			$text.= '		<span style="font-size:16px; font-weight:bold; font-family:Arial; font-style:italic; color:#000000;">';
			$text.= '€ '.number_format($price["total"], 2);
			$text.= '		</span><br />';			
			$text.= '	</td>';
			$text.=	'</tr>';
			$text.= '<tr>';
			$text.= '	<td style="border-color:#000000; border-style:solid; border-width:1px; border-collapse:collapse;">';
			$text.= '		<span style="font-size:10px; font-family:Arial; color:#000000;">';
			$text.= $vehicles;
			$text.= '	</span>';
			$text.= '	</td>';
			$text.= '</tr>';
	/*		$text.=  '	</div>';
			$text.= '	<div style="float:left; padding-left:10px; width=450px;">';
			$text.= '	<span style="font-size:24px; font-weight:bold; font-family:Arial; color:#000000;">';
			$text.= '		<p>'.$row_item_info["title"].'</p>';
			$text.= '	</span><br />';
			$text.= '	<span style="width:100px; font-size:16px; font-weight:bold; font-family:Arial; color:#000000;">';
			$text.= 'Ihr Preis:';
			$text.= '	</span>';
			$text.= '	<span style="width:100px; font-size:24px; font-weight:bold; font-family:Arial; font-style:italic; color:#000000;">';	
			$text.= '€ '.number_format($price["total"], 2);
			$text.= '	</span><br /><br />';
			$text.= '	<span style="font-size:14px; font-weight:bold; font-family:Arial; color:#000000;">';
			$text.= 'Artikelinformationen:';
			$text.= '	</span><br />';
			$text.= '	<span style="font-size:12px; font-weight:bold; font-family:Arial; color:#999;">';
			$krits=array();
			$krits=explode(';', $row_item_info["short_description"]);
			for ($j=0; $j<sizeof($krits); $j++) {
				$text.= $krits[$j].'<br />';
			}
			$text.= '	</span>';
			$text.= '	</div>';
			$text.= '</div>';
	*/

		}
echo '</table>';
	}

//echo $text;
send_html_mail2($_POST["mail"], "MAPCO-Shop <newsletter@mapco-shop.de>", $_POST["subject"], $text);
//send_html_mail2("nputzing@mapco.de", $_POST["mail"]." MAPCO-Shop <newsletter@mapco-shop.de>", $_POST["subject"], $text);

?>
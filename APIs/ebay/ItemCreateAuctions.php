<?php
	require_once("../functions/mapco_cutout.php");
	require_once("../functions/shop_get_prices.php");
	require_once("../functions/cms_t2.php");

	if ( !isset($_POST["id_accountsite"]) )
	{
		echo '<ItemCreateAuctionsResponse>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Accountsite-ID nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss einen Accountsite-ID übergeben werden, damit der Service weiß, zu welchem Account die Auktionen bearbeitet werden sollen.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ItemCreateAuctionsResponse>'."\n";
		exit;
	}
	$results=q("SELECT * FROM ebay_accounts_sites WHERE id_accountsite=".$_POST["id_accountsite"].";", $dbshop, __FILE__, __LINE__);
	if ( mysqli_num_rows($results)==0 )
	{
		echo '<ItemCreateAuctionsResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Ebay-Accountseite nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Der angegebene Ebay-Accountseite konnte nicht gefunden werden. Die Accountsite-ID scheint es nicht zu geben.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ItemCreateAuctionsResponse>'."\n";
		exit;
	}
	$accountsite=mysqli_fetch_array($results);
	
	$results=q("SELECT * FROM ebay_accounts WHERE id_account=".$accountsite["account_id"].";", $dbshop, __FILE__, __LINE__);
	if ( mysqli_num_rows($results)==0 )
	{
		echo '<ItemCreateAuctionsResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Ebay-Account nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Der angegebene Ebay-Account konnte nicht gefunden werden. Die Account-ID scheint es nicht zu geben.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ItemCreateAuctionsResponse>'."\n";
		exit;
	}
	$account=mysqli_fetch_array($results);

	$results=q("SELECT * FROM cms_languages WHERE id_language=".$accountsite["language_id"].";", $dbweb, __FILE__, __LINE__);
	$row=mysqli_fetch_array($results);
	$lang=$row["code"];

	if ( !isset($_POST["id_item"]) )
	{
		echo '<ItemCreateAuctionsResponse>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Shopartikel-ID nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss einen Shopartikel-ID übergeben werden, damit der Service weiß, zu welchem Shopartikel die Auktionen bearbeitet werden sollen.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ItemCreateAuctionsResponse>'."\n";
		exit;
	}

	if ( !isset($_POST["bestoffer"]) ) $_POST["bestoffer"]=1;
	if ( !isset($_POST["ShippingServiceCost"]) ) $_POST["ShippingServiceCost"]=4.9;

	//EPS Check
	function eps_check($url)
	{
		global $account;
		
		$ch = curl_init();
		curl_setopt	($ch, CURLOPT_FORBID_REUSE, true); 
		curl_setopt	($ch, CURLOPT_FRESH_CONNECT, true);
		curl_setopt ($ch, CURLOPT_POST, false);
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt ($ch, CURLOPT_URL, $url);
		$response = curl_exec ($ch);
		if( $response===false )	$response=curl_error($ch);
		curl_close($ch);
		
		if( crc32($response)==1579385611 ) return(false);
		return(true);
	}

	//EPS Upload
	function eps_upload($PictureURL)
	{
		global $account;
		global $auction;
		global $dbshop;

//		$results=q("SELECT * FROM ebay_accounts WHERE id_account=1;", $dbshop, __FILE__, __LINE__);
//		$account=mysqli_fetch_array($results);
		$requestXmlBody='
		<?xml version="1.0" encoding="utf-8"?>
		<UploadSiteHostedPicturesRequest xmlns="urn:ebay:apis:eBLBaseComponents">';
		if ( $account["production"]==0 )
		{
			$requestXmlBody .= '<RequesterCredentials><eBayAuthToken>'.$account["token_sandbox"].'</eBayAuthToken></RequesterCredentials>';
		}
		else
		{
			$requestXmlBody .= '<RequesterCredentials><eBayAuthToken>'.$account["token"].'</eBayAuthToken></RequesterCredentials>';
		}
		$requestXmlBody .= '
		  <WarningLevel>High</WarningLevel>
		  <ExternalPictureURL><![CDATA['.$PictureURL.']]></ExternalPictureURL>
		  <PictureName><![CDATA['.$auction["Title"].']]></PictureName>
		  <PictureSet>Supersize</PictureSet>
		</UploadSiteHostedPicturesRequest>';
		$responseXml = post(PATH."soa/", array("API" => "ebay", "Action" => "EbaySubmit", "Call" => "UploadSiteHostedPictures", "id_account" => $account["id_account"], "request" => $requestXmlBody));

		$use_errors = libxml_use_internal_errors(true);
		try
		{
			$response = new SimpleXMLElement($responseXml);
		}
		catch(Exception $e)
		{
			echo '<ItemCreateAuctionsResponse>'."\n";
			echo '	<Ack>Failure</Ack>'."\n";
			echo '	<Error>'."\n";
			echo '		<Code>'.__LINE__.'</Code>'."\n";
			echo '		<shortMsg>Bild hochladen fehlgeschlagen.</shortMsg>'."\n";
			echo '		<longMsg>Beim Hochladen eines Bildes ist ein Fehler aufgetreten.</longMsg>'."\n";
			echo '	</Error>'."\n";
			echo '</ItemCreateAuctionsResponse>'."\n";
			exit;
		}
		libxml_clear_errors();
		libxml_use_internal_errors($use_errors);
		$FullURL=(string)$response->SiteHostedPictureDetails[0]->FullURL[0];
		if( $FullURL=="" )
		{
			echo '<ItemCreateAuctionsResponse>'."\n";
			echo '	<Ack>Failure</Ack>'."\n";
			echo '	<Error>'."\n";
			echo '		<Code>'.__LINE__.'</Code>'."\n";
			echo '		<shortMsg>Bild-URL nicht gefunden.</shortMsg>'."\n";
			echo '		<longMsg>Nach dem Hochladen des Bildes wurde keine URL zurück gegeben.</longMsg>'."\n";
			echo '	</Error>'."\n";
			echo '	<Response><![CDATA['.$PictureURL.$responseXml.']]></Response>';
			echo '</ItemCreateAuctionsResponse>'."\n";
			exit;
		}
		return($FullURL);
	}

	//submit Pictures to EPS if needed
	$PictureURL=array();
	$results=q("SELECT article_id FROM shop_items WHERE id_item=".$_POST["id_item"].";", $dbshop, __FILE__, __LINE__);
	$row=mysqli_fetch_array($results);
	$results=q("SELECT * FROM cms_articles_images WHERE article_id=".$row["article_id"]." ORDER BY ordering;", $dbweb, __FILE__, __LINE__);
	if ( mysqli_num_rows($results)==0 )
	{
		$noimage=eps_upload(PATH.$accountsite["PictureURL"]);
		$PictureURL[]=$noimage;
		$PictureURL[]=$noimage;
	}
	$i=0;
	while($row=mysqli_fetch_array($results))
	{
		if ( $i==0 )
		{
			$results2=q("SELECT * FROM cms_files WHERE original_id=".$row["file_id"]." AND imageformat_id=".$accountsite["id_imageformat"].";", $dbweb, __FILE__, __LINE__);
			if ( mysqli_num_rows($results2)>0 )
			{
				//check if eps link is still working
				$row2=mysqli_fetch_array($results2);
				if( eps_check($row2["EPS_link"]) )
				{
					$PictureURL[]=$row2["EPS_link"];
				}
				else
				{
					$EPS_link=eps_upload(PATH.'files/'.floor(bcdiv($row2["id_file"], 1000)).'/'.$row2["id_file"].'.'.$row2["extension"]);
					$PictureURL[]=$EPS_link;
					q("UPDATE cms_files SET EPS_link='".mysqli_real_escape_string($dbweb, $EPS_link)."' WHERE id_file=".$row2["id_file"].";", $dbweb, __FILE__, __LINE__);
				}
			}
		}
		$results2=q("SELECT * FROM cms_files WHERE original_id=".$row["file_id"]." AND imageformat_id=19;", $dbweb, __FILE__, __LINE__);
		if ( mysqli_num_rows($results2)>0 )
		{
			//check if eps link is still working
			$row2=mysqli_fetch_array($results2);
			if( eps_check($row2["EPS_link"]) )
			{
				$PictureURL[]=$row2["EPS_link"];
			}
			else
			{
				$EPS_link=eps_upload(PATH.'files/'.floor(bcdiv($row2["id_file"], 1000)).'/'.$row2["id_file"].'.'.$row2["extension"]);
				$PictureURL[]=$EPS_link;
				q("UPDATE cms_files SET EPS_link='".mysqli_real_escape_string($dbweb, $EPS_link)."' WHERE id_file=".$row2["id_file"].";", $dbweb, __FILE__, __LINE__);
			}
		}
		$i++;
		if( $accountsite["PicturePack"]==0 ) break;
	}


	//end unsuccessful auctions
	$results=q("SELECT * FROM ebay_auctions WHERE shopitem_id='".$_POST["id_item"]."' AND accountsite_id=".$accountsite["id_accountsite"].";", $dbshop, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		$results2=q("SELECT * FROM ebay_orders_items WHERE ItemItemID=".$row["ItemID"]." ORDER BY CreatedDateTimestamp DESC;", $dbshop, __FILE__, __LINE__);
		$QuantitySold=mysqli_num_rows($results2);
		if( $QuantitySold==0 )
		{
			if( $row["firstmod"]<(time()-31*24*3600)  )
			{
				q("UPDATE ebay_auctions SET `Call`='EndItem', upload=1 WHERE id_auction=".$row["id_auction"].";", $dbshop, __FILE__, __LINE__);
			}
		}
		else
		{
			$row2=mysqli_fetch_array($results2);
			if( $row2["CreatedDateTimestamp"]<(time()-31*24*3600) )
			{
				q("UPDATE ebay_auctions SET `Call`='EndItem', upload=1 WHERE id_auction=".$row["id_auction"].";", $dbshop, __FILE__, __LINE__);
			}
			else
			{
				q("UPDATE ebay_auctions SET `Call`='ReviseItem', QuantitySold=".$QuantitySold." WHERE id_auction=".$row["id_auction"].";", $dbshop, __FILE__, __LINE__);
			}
		}
	}

	//categories
	$results=q("SELECT * FROM  ebay_store_categories;", $dbshop, __FILE__, __LINE__);
	while($row=mysqli_fetch_array($results))
	{
		$StoreCategoryID[$row["account_id"]][$row["GART"]]=$row["StoreCategory"];
		$StoreCategory2ID[$row["account_id"]][$row["GART"]]=$row["StoreCategory2"];
	}
	$results=q("SELECT * FROM  ebay_categories WHERE accountsite_id=".$accountsite["id_accountsite"].";", $dbshop, __FILE__, __LINE__);
	while($row=mysqli_fetch_array($results))
	{
		$CatID[$row["GART"]]=$row["CategoryID"];
		$CatID2[$row["GART"]]=$row["CategoryID2"];
		$youtube[$row["GART"]]=$row["youtube"];
	}
	$results=q("SELECT * FROM  mapco_gart_export;", $dbshop, __FILE__, __LINE__);
	while($row=mysqli_fetch_array($results))
	{
		$youtube[$row["GART"]]=$row["youtube"];
	}


	//end auctions if item is not active
	$results=q("SELECT * FROM shop_items WHERE id_item=".$_POST["id_item"].";", $dbshop, __FILE__, __LINE__);
	$item=mysqli_fetch_array($results);
	if( $item["active"]==0 )
	{
		q("	UPDATE ebay_auctions
			SET `Call`='EndItem',
				upload=1
			WHERE accountsite_id=".$accountsite["id_accountsite"]." AND shopitem_id=".$_POST["id_item"].";", $dbshop, __FILE__, __LINE__);
		echo '<ItemCreateAuctionsResponse>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Artikel inaktiv.</shortMsg>'."\n";
		echo '		<longMsg>Der Artikel ist inaktiv. Bestehende Auktionen werden gegebenenfalls beendet.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ItemCreateAuctionsResponse>'."\n";
		exit;
	}


	/***********************************
	 * GATHER INFORMATION FOR AUCTIONS *
	 ***********************************/
	//SKU
	$id_article=$item["article_id"];
	$SKU=$item["MPN"];
	$GART=$item["GART"];
	$results=q("SELECT * FROM t_200 WHERE ArtNr='".$SKU."';", $dbshop, __FILE__, __LINE__);
	$row=mysqli_fetch_array($results);
	$collateral=$item["collateral"]*1.19;

	//BestOffer
	//is set after StartPrice


	//CategoryID, CategoryID2
	$item["GART"]*=1;
	if ($CatID[$item["GART"]]>0) $CategoryID=$CatID[$item["GART"]];
	else $CategoryID=$accountsite["CategoryOther"];
	if ($CatID2[$item["GART"]]>0 and $CatID2[$item["GART"]]!=$CategoryID)
	{
		$CategoryID2=$CatID2[$item["GART"]];
	}
	else
	{
		if( $CategoryID != $accountsite["CategoryOther"] ) $CategoryID2=$accountsite["CategoryOther"];
		else $CategoryID2=0;
	}

	//CategoryMappingAllowed
	$CategoryMappingAllowed=1;


	//ConditionID
	$ConditionID=1000;
	//ConditionID track control
	if ( $GART==286 )
	{
		$results=q("SELECT * FROM shop_items_".$lang." WHERE id_item=".$_POST["id_item"].";", $dbshop, __FILE__, __LINE__);
		$row=mysqli_fetch_array($results);
		$crits=explode("; ", $row["short_description"]);
		$new=false;
		for($i=0; $i<sizeof($crits); $i++)
		{
			$crit=explode(":", $crits[$i]);
			if ( $crit[0]=="Neuteil" ) $new=true;
		}
		if ( !$new ) $ConditionID=2500;
	}


	//Country
	$Country="DE";


	//Currency
	$Currency=$accountsite["currencyID"];
	//currency fix
	$results=q("SELECT * FROM shop_currencies WHERE currency_code='".$accountsite["currencyID"]."';", $dbshop, __FILE__, __LINE__);
	if( mysqli_num_rows($results)!=1 )
	{
		echo 'currency error';
		exit;
	}
	$currency=mysqli_fetch_array($results);


	//Description
	$Description="";
	//Description - Hybrid-Scheibenwischer
	if( $GART==298 and strpos($SKU, "HPS") !==false )
	{
		$Description .= '<div class="box">';
		$Description .= '	<h2>Die Vorteile der MAPCO-HPS-Hybrid-Flachbalkenwischer</h2>';
		$Description .= '	<img alt="Die Vorteile der MAPCO-Hybridscheibenwischer" src="http://www.mapco.de/images/ebay_hybridwischervorteile.png" style="width:800px;margin:0px 0px 0px 26px;" title="Die Vorteile der MAPCO-Hybridscheibenwischer" />';
		$Description .= '</div>';
	}
	//Description - Altteilpfand
	if ( $collateral>0 )
	{
		$Description .= '<div class="box" style="border:5px solid red;">';
		$Description .= '	<h2>Holen Sie sich den Altteilwert!</h2>';
		$Description .= '	<p>Für diese Auktion müssen Sie zusätzlich einen Altteilpfand i.H.v. '.number_format($collateral, 2, ",", ".").' Euro überweisen. Wenn Sie nach Erhalt dieses Artikels Ihr Altteil an uns zurücksenden, bekommen Sie von uns '.number_format($collateral, 2, ",", ".").' Euro (inkl. Mehrwertsteuer) zurück! Das zurückgesandte Altteil darf keine groben mechanischen Beschädigungen durch Unfall oder gebrochene Teile aufweisen. Bitte fordern Sie einen Rücksendeschein bei uns an!</p>';
		$Description .= '</div>';
	}
	//Description - YouTube
	if ( $youtube[$GART]!="" )
	{
		$Description .= '<div class="box">';
		$Description .= '	<h2>MAPCO TV</h2>';
		$Description .= '<object width="846" height="476" style="margin:0px 0px 0px 26px;"><param name="movie" value="http://www.youtube.com/v/EsPZ-KdVIU4?version=3&amp;hl=de_DE&amp;rel=0"></param><param name="allowFullScreen" value="true"></param><param name="allowscriptaccess" value="always"></param><embed src="http://www.youtube.com/v/'.$youtube[$GART].'?version=3&amp;hl=de_DE&amp;rel=0" type="application/x-shockwave-flash" width="846" height="476" allowscriptaccess="always" allowfullscreen="true"></embed></object>';
		$Description .= '</div>';
	}
	//Description - article
	$results3=q("SELECT * FROM cms_articles WHERE id_article=".$accountsite["article_id"].";", $dbweb, __FILE__, __LINE__);
	$row3=mysqli_fetch_array($results3);
	$Description.=$row3["article"];
	if ( strpos($SKU, "HPS")>0 )
	{
		$Description=str_replace("<!-- HPS -->", '<img src="http://www.mapco.de/images/ebay_hps.jpg" />', $Description);
	}
	else
	{
		$Description=str_replace("<!-- HPS -->", '<img src="http://mapco.de/images/ebay_header_mapco.png" />', $Description);
	}

	$results5=q("SELECT * FROM shop_items_".$lang." WHERE id_item=".$_POST["id_item"].";", $dbshop, __FILE__, __LINE__);
	$row5=mysqli_fetch_array($results5);
	$Desc=$row5["description"];

	$Desc=cutout($Desc, '<!-- Reverse Start -->', '<!-- Reverse Stop -->');
	$Desc=str_replace('<div style="width:370px; margin:0px 16px 0px 0px; float:left;">', '<div>', $Desc);
	$Desc=str_replace('<h2>', '<div class="box"><h2>', $Desc);
	$Desc=str_replace('</h1>', '</h2>', $Desc);
	$Desc=str_replace('</table>', '</table></div>', $Desc);
	$Desc=cutout($Desc, '<a href="', '">');
	$Desc=str_replace('</a>', '', $Desc);
	$Description=str_replace("<!-- DESCRIPTION -->", $Desc, $Description);
	if ($_POST["comment"]!="")
	{
		$Description=str_replace("<!-- COMMENT -->", '<div class="box"><p>'.$_POST["comment"].'</p></div>', $Description);
	}

	$Description=str_replace("<div class=\"box\"><h2>Fahrzeugzuordnungen</h2>", "<div style=\"overflow:scroll; max-height:600px;\" class=\"box\"><h2>Fahrzeugzuordnungen</h2>", $Description);	
//	$Description=str_replace("&", "&amp;", $Description);
//	$Description=str_replace("<", "&lt;", $Description);
//	$Description=str_replace(">", "&gt;", $Description);
	if ($accountsite["SiteID"]==3 or $accountsite["SiteID"]==101) $Description=str_replace('* Die angezeigten OE- und OEM-Nummern dienen nur zur Zuordnung technischer Daten und der Verwendungszwecke.', '', $Description);
	$Description='<![CDATA['.substr($Description, 0, 499987).']]>';


	//DiscountPercent
	$DiscountPercent=round($_POST["DiscountPercent"], 2);


	//ItemCompatibilityList			
	//ItemCompatibilityList - get ebay vehicles (ebay knows less vehicles than TecDoc)
	$ebay_vehicles=array();
	$ItemCompatibilityList=array();
	$results=q("SELECT * FROM ebay_vehicles WHERE marketplace_id=".$accountsite["marketplace_id"].";", $dbshop, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		$ebay_vehicles[$row["KType"]]=$row["KType"];
	}
	//ItemCompatibilityList - get data
	$auctions=array();
	
	//get all vehicle applications
	$vapps=array();
	$results=q("SELECT * FROM shop_items_vehicles WHERE language_id=".$accountsite["language_id"]." AND item_id=".$_POST["id_item"].";", $dbshop, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		$vapps[$row["vehicle_id"]]=$row["vehicle_id"];
		$vapps_restrictions[$row["vehicle_id"]]=substr($row["criteria"], 0, 50);
	}

	//generate standard auctions
	//count vehicles and brands
	$i=0;
	$noa=0;
	$count=0;
	$KType=array();
	$title=array();
	$restrictions=array();
	$registrations=array();
	$brands=array();
	if( sizeof($vapps)>0 )
	{
		$results2=q("SELECT * FROM vehicles_".$lang." WHERE id_vehicle IN(".implode(", ", $vapps).")  ORDER BY BEZ1, BEZ2, BEZ3;", $dbshop, __FILE__, __LINE__);
		while($row2=mysqli_fetch_array($results2))
		{
	//		if ( isset($vapps[$row2["id_vehicle"]]) ) //vehicle applications only
			{
				if ( ($accountsite["SiteID"]!=77 and $accountsite["SiteID"]!=3) or isset($ebay_vehicles[$row2["KTypNr"]*1]) ) //ebay known vehicles only
				{
					$KType[]=$row2["KTypNr"]*1;
					$title[]=$row2["BEZ1"].' '.$row2["BEZ2"].' '.$row2["BEZ3"];
					$registrations[]=$row2["registrations"];
					$restrictions[]=substr($vapps_restrictions[$row2["id_vehicle"]], 0, 500);
	
					//determine vehicle application slots
					$bjvon=substr($row2["BJvon"], 0, 4);
					$bjbis=substr($row2["BJbis"], 0, 4);
					if ( $bjbis==0 ) $bjbis=date("Y", time()); //zero year fix
					$number=$bjbis-$bjvon+3;
					if ( $noa==1 )
					{
	//					echo $bjbis.' - '.$bjvon.' = '.$number.'<br />\n';
					}
	
					if ( ($count+$number)>1000 )
					{
						if ($noa==1)
						{
	//						print_r($auctions[$noa]["ItemCompatibilityKtype"]);
	//						exit;
						}
						$noa++;
	//					echo $count.'<br /><br />';
						$count=$number;
						$i=0;
						if ($noa>14) break;
					}
					$auctions[$noa]["Title"]="";
					$count+=$number;
					$auctions[$noa]["ItemCompatibilityKtype"][$i]=($row2["KTypNr"]*1);
					$auctions[$noa]["ItemCompatibilityNotes"][$i]=utf8_encode(substr(utf8_decode($vapps_restrictions[$row2["id_vehicle"]]), 0, 480));
					$i++;
				}
	//			else echo $row2["KTypNr"]."<br />";
			}
		}
	}
	else
	{
		$auctions[$noa]["Title"]="";
		$auctions[$noa]["ItemCompatibilityKtype"][0]="";
		$auctions[$noa]["ItemCompatibilityNotes"][0]="";
	}
	$noa++;

	//generate top vehicle auctions
	if( $noa<15 )
	{
		$i=0;
		array_multisort($registrations, SORT_DESC, $KType, $title, $restrictions);
		for($i; $i<sizeof($registrations); $i++)
		{
			$double=false;
			for($j=0; $j<sizeof($auctions); $j++)
			{
				if( strtolower($auctions[$j]["Title"])==strtolower($title[$i]) )
				{
					$double=true;
					break;
				}
			}
			if ( !$double )
			{
				$prefix="";
				if (strpos(strtolower($title[$i]),"honda") !== false) 
				{
					$prefix=t("passend für", $lang);
				}
				$auctions[$noa]["Title"]=$prefix." ".$title[$i];
				//search for right KTypes
				for($j=0; $j<sizeof($auctions); $j++)
				{
					for($k=0; $k<sizeof($auctions[$j]["ItemCompatibilityKtype"]); $k++)
					{
						if ( $auctions[$j]["ItemCompatibilityKtype"][$k] == $KType[$i] )
						{
							$auctions[$noa]["ItemCompatibilityKtype"]=$auctions[$j]["ItemCompatibilityKtype"];
							$auctions[$noa]["ItemCompatibilityNotes"]=$auctions[$j]["ItemCompatibilityNotes"];
						}
					}
				}
				$noa++;
			}
			if ( $noa==15 ) break;
		}
	}


	//ItemCompatibilityList - create
	for($i=0; $i<sizeof($auctions); $i++)
	{
		$ItemCompatibilityList[$i] = '	<ItemCompatibilityList>'."\n";
		$ItemCompatibilityList[$i] .= '		<ReplaceAll>true</ReplaceAll>'."\n";
		for($j=0; $j<sizeof($auctions[$i]["ItemCompatibilityKtype"]); $j++)
		{
			$ItemCompatibilityList[$i] .= '		<Compatibility>'."\n";
			$ItemCompatibilityList[$i] .= '			<NameValueList>'."\n";
			$ItemCompatibilityList[$i] .= '				<Name>KType</Name>'."\n";
			$ItemCompatibilityList[$i] .= '				<Value>'.$auctions[$i]["ItemCompatibilityKtype"][$j].'</Value>'."\n";
			$ItemCompatibilityList[$i] .= '			</NameValueList>'."\n";
			if ( $auctions[$i]["ItemCompatibilityNotes"][$j]!="" )
			{
				$CompatibilityNotes=$auctions[$i]["ItemCompatibilityNotes"][$j];
				$CompatibilityNotes=str_replace("&", "&amp;", $CompatibilityNotes);
				$CompatibilityNotes=str_replace("<", "&lt;", $CompatibilityNotes);
				$CompatibilityNotes=str_replace(">", "&gt;", $CompatibilityNotes);
				$ItemCompatibilityList[$i] .= '			<CompatibilityNotes>'.$CompatibilityNotes.'</CompatibilityNotes>'."\n";
//				$ItemCompatibilityList[$i] .= '			<CompatibilityNotes><![CDATA['.$CompatibilityNotes.']]></CompatibilityNotes>'."\n";
			}
			$ItemCompatibilityList[$i] .= '		</Compatibility>'."\n";
		}
		$ItemCompatibilityList[$i] .= '	</ItemCompatibilityList>'."\n";
	}
//	echo strlen($ItemCompatibilityList[2]);
//	echo '<br />'.$ItemCompatibilityList[2];
//	exit;

	//ItemSpecifics
	$ItemSpec=array();
	$results=q("SELECT * FROM shop_items_".$lang." WHERE id_item=".$_POST["id_item"].";", $dbshop, __FILE__, __LINE__);
	$row=mysqli_fetch_array($results);
	$crits=explode("; ", $row["short_description"]);
	for($i=0; $i<sizeof($crits); $i++)
	{
		$crit=explode(": ", $crits[$i]);
		$ItemSpec[$i]["Name"]=substr($crit[0], 0, 40);
		$ItemSpec[$i]["Value"]=substr($crit[1], 0, 50);
		if ( $crit[0]=="Ergänzungsartikel / Ergänzende Info 2" ) $Title.=" ".$crit[1];
	}
	$ItemSpecifics 	= '';
	$ItemSpecifics .= '	<ItemSpecifics>'."\n";
	//Manufacturer
	$ItemSpecifics .= '		<NameValueList>'."\n";
	if ($accountsite["SiteID"]==101) $name='Manufacturer';
	elseif ($accountsite["SiteID"]==9) $name='Marca Prodotto';
	else $name='Hersteller';
	$ItemSpecifics .= '			<Name>'.$name.'</Name>'."\n";
	$ItemSpecifics .= '			<Value>MAPCO Autotechnik GmbH</Value>'."\n";
	$ItemSpecifics .= '			<Source>ItemSpecific</Source>'."\n";
	$ItemSpecifics .= '		</NameValueList>'."\n";
	//Part Manufacturer Number
	$ItemSpecifics .= '		<NameValueList>'."\n";
	if ($accountsite["SiteID"]==3) $name='Part Manufacturer Number';
	elseif ($accountsite["SiteID"]==101) $name='Codice Prodotto';
	else $name='Artikelnummer';
	$ItemSpecifics .= '			<Name>'.$name.'</Name>'."\n";
	$ItemSpecifics .= '			<Value>'.$SKU.'</Value>'."\n";
	$ItemSpecifics .= '			<Source>ItemSpecific</Source>'."\n";
	$ItemSpecifics .= '		</NameValueList>'."\n";
	//sum up criteria
	$crits=array();
	for($i=0; $i<sizeof($ItemSpec); $i++)
	{
		$double=false;
		for($j=0; $j<sizeof($crits); $j++)
		{
			if( $ItemSpec[$i]["Name"]==$crits[$j]["Name"] )
			{
				$crits[$j]["Value"].=' '.$ItemSpec[$i]["Value"];
				$double=true;
			}
		}
		if( !$double )
		{
			$crits[]=$ItemSpec[$i];
		}
	}
	//get criteria
	for($j=0; $j<sizeof($ItemSpec); $j++)
	{
		$ItemSpecifics .= '		<NameValueList>'."\n";
		$ItemSpecifics .= '			<Name>'.substr($crits[$j]["Name"], 0, 40).'</Name>'."\n";
		$ItemSpecifics .= '			<Value>'.substr($crits[$j]["Value"], 0, 50).'</Value>'."\n";
		$ItemSpecifics .= '			<Source>ItemSpecific</Source>'."\n";
		$ItemSpecifics .= '		</NameValueList>'."\n";
	}
	if( $accountsite["SiteID"]!=101 )
	{
		//get OE numbers
		$oenr=array();
		$oebez=array();
		$results2=q("SELECT LBezNr, OENr, SOE FROM t_203 AS a, t_100 AS b WHERE ArtNr='".$SKU."' AND a.KHerNr=b.KherNr AND VGL=0;", $dbshop, __FILE__, __LINE__);
		$oem_numbers=mysqli_num_rows($results2);
		if ($oem_numbers>0)
		{
			while ($row2=mysqli_fetch_array($results2))
			{
				$results3=q("SELECT Bez FROM t_012 WHERE LBezNr=".$row2["LBezNr"]." AND SprachNr=1;", $dbshop, __FILE__, __LINE__);
				$row3=mysqli_fetch_array($results3);
				$oenr[]=$row2["OENr"];
				$oebez[]=$row3["Bez"];
				$oenr[]=$row2["SOE"];
				$oebez[]=$row3["Bez"];
			}
			//sort by name
			array_multisort($oebez, $oenr);
		}
		$ItemSpecifics .= '		<NameValueList>'."\n";
		if ($accountsite["SiteID"]==3)
		{
			$ItemSpecifics .= '			<Name>Reference OE/OEM Number</Name>'."\n";
		}
		elseif ($accountsite["SiteID"]==101)
		{
			$ItemSpecifics .= '			<Name>Referenznummer(n) OE</Name>'."\n";
		}
		else
		{
			$ItemSpecifics .= '			<Name>Referenznummer(n) OE</Name>'."\n";
		}
		for($j=0; $j<sizeof($oenr); $j++)
		{
			$value=$oebez[$j].' '.$oenr[$j];
			$value=str_replace("&", "&amp;", $value);
			$ItemSpecifics .= '<Value>'.$value.'</Value>'."\n";
		}
		//add synonyms
		$results=q("SELECT * FROM shop_items_keywords WHERE GART=".$item["GART"]." AND language_id=".$accountsite["language_id"].";", $dbshop, __FILE__, __LINE__);
		while($row=mysqli_fetch_array($results))
		{
			$ItemSpecifics .= '<Value>'.$row["keyword"].'</Value>'."\n";
		}
		if ($accountsite["SiteID"]!=3 and $accountsite["SiteID"]!=101)
		{
			$ItemSpecifics .= '			<Source>ItemSpecific</Source>'."\n";
			$ItemSpecifics .= '		</NameValueList>'."\n";
		}
		//get OEM numbers
		$oemnr=array();
		$oembez=array();
		$results2=q("SELECT LBezNr, OENr, SOE FROM t_203 AS a, t_100 AS b WHERE ArtNr='".$SKU."' AND a.KHerNr=b.KherNr AND VGL=1;", $dbshop, __FILE__, __LINE__);
		$oem_numbers=mysqli_num_rows($results2);
		if ($oem_numbers>0)
		{
			while ($row2=mysqli_fetch_array($results2))
			{
				$results3=q("SELECT Bez FROM t_012 WHERE LBezNr=".$row2["LBezNr"]." AND SprachNr=1;", $dbshop, __FILE__, __LINE__);
				$row3=mysqli_fetch_array($results3);
				$oemnr[]=$row2["OENr"];
				$oembez[]=$row3["Bez"];
				$oemnr[]=$row2["SOE"];
				$oembez[]=$row3["Bez"];
			}
			//sort by name
			array_multisort($oembez, $oemnr);
		}
		if ($accountsite["SiteID"]!=3 and $accountsite["SiteID"]!=101)
		{
			$ItemSpecifics .= '		<NameValueList>'."\n";
			$ItemSpecifics .= '			<Name>Referenznummer(n) OEM</Name>'."\n";
		}
		for($j=0; $j<sizeof($oemnr); $j++)
		{
			$value=$oembez[$j].' '.$oemnr[$j];
			$value=str_replace("&", "&amp;", $value);
			$ItemSpecifics .= '<Value>'.$value.'</Value>'."\n";
		}
		$ItemSpecifics .= '			<Source>ItemSpecific</Source>'."\n";
		$ItemSpecifics .= '		</NameValueList>'."\n";
	}
	$ItemSpecifics .= '	</ItemSpecifics>'."\n";


	//ListingDuration
	if ($account["production"]==1) $ListingDuration="GTC"; else $ListingDuration="Days_7";


	//ListingType
	$ListingType="FixedPriceItem";


	//PictureDetails
	//submit Pictures to EPS if needed
	$PictureURL=array();
	$results=q("SELECT article_id FROM shop_items WHERE id_item=".$_POST["id_item"].";", $dbshop, __FILE__, __LINE__);
	$row=mysqli_fetch_array($results);
	$results=q("SELECT * FROM cms_articles_images WHERE article_id=".$row["article_id"]." ORDER BY ordering;", $dbweb, __FILE__, __LINE__);
	if ( mysqli_num_rows($results)==0 )
	{
		$results=q("SELECT * FROM cms_articles_images WHERE article_id=".$accountsite["article_id"]." ORDER BY ordering;", $dbweb, __FILE__, __LINE__);
		$row=mysqli_fetch_array($results);
		$results=q("SELECT * FROM cms_files WHERE id_file=".$row["file_id"].";", $dbweb, __FILE__, __LINE__);
		$row=mysqli_fetch_array($results);
		if( $row["EPS_link"]=="" )
		{
			$EPS_link=eps_upload(PATH.'files/'.floor(bcdiv($row["id_file"], 1000)).'/'.$row["id_file"].'.'.$row["extension"]);
			$PictureURL[]=$EPS_link;
			$PictureURL[]=$EPS_link;
			q("UPDATE cms_files SET EPS_link='".mysqli_real_escape_string($dbweb, $EPS_link)."' WHERE id_file=".$row["id_file"].";", $dbweb, __FILE__, __LINE__);
		}
		else
		{
			$PictureURL[]=$row["EPS_link"];
			$PictureURL[]=$row["EPS_link"];
		}
	}
	$i=0;
	while($row=mysqli_fetch_array($results))
	{
		if ( $i==0 )
		{
			$results2=q("SELECT * FROM cms_files WHERE original_id=".$row["file_id"]." AND imageformat_id=".$accountsite["id_imageformat"].";", $dbweb, __FILE__, __LINE__);
			if ( mysqli_num_rows($results2)>0 )
			{
				$row2=mysqli_fetch_array($results2);
				if( $row2["EPS_link"]!="" ) $PictureURL[]=$row2["EPS_link"];
				else
				{
					$EPS_link=eps_upload(PATH.'files/'.floor(bcdiv($row2["id_file"], 1000)).'/'.$row2["id_file"].'.'.$row2["extension"]);
					$PictureURL[]=$EPS_link;
					q("UPDATE cms_files SET EPS_link='".mysqli_real_escape_string($dbweb, $EPS_link)."' WHERE id_file=".$row2["id_file"].";", $dbweb, __FILE__, __LINE__);
				}
			}
		}
		$results2=q("SELECT * FROM cms_files WHERE original_id=".$row["file_id"]." AND imageformat_id=19;", $dbweb, __FILE__, __LINE__);
		if ( mysqli_num_rows($results2)>0 )
		{
			$row2=mysqli_fetch_array($results2);
			if( $row2["EPS_link"]!="" ) $PictureURL[]=$row2["EPS_link"];
			else
			{
				$EPS_link=eps_upload(PATH.'files/'.floor(bcdiv($row2["id_file"], 1000)).'/'.$row2["id_file"].'.'.$row2["extension"]);
				$PictureURL[]=$EPS_link;
				q("UPDATE cms_files SET EPS_link='".mysqli_real_escape_string($dbweb, $EPS_link)."' WHERE id_file=".$row2["id_file"].";", $dbweb, __FILE__, __LINE__);
			}
		}
		$i++;
		if( $accountsite["PicturePack"]==0 ) break;
	}
	//build PictureDetails
	$PictureDetails="";
	$PictureDetails .= '	<GalleryType>Gallery</GalleryType>'."\n";
	if( $accountsite["PicturePack"]==1 )
	{
		$max=sizeof($PictureURL);
		$PictureDetails .= '	<PhotoDisplay>PicturePack</PhotoDisplay>'."\n";
	}
	else $max=2;
	for($j=0; $j<$max; $j++)
	{
		if ( $j==0 ) $PictureDetails .= '	<GalleryURL>'.$PictureURL[$j].'</GalleryURL>'."\n";
		else $PictureDetails .= '	<PictureURL>'.$PictureURL[$j].'</PictureURL>'."\n";
	}


	//Quantity
	$Quantity=0;
	$results=q("SELECT * FROM lager WHERE ArtNr='".$SKU."';", $dbshop, __FILE__, __LINE__);
	if ( mysqli_num_rows($results)==0 )
	{
/*
		echo '<ItemCreateAuctionsResponse>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Lagerbestand nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss ein Lagerbestand vorhanden sein, damit der Service weiß, ob der Artikel einzustellen ist oder die Auktionen beendet werden müssen.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ItemCreateAuctionsResponse>'."\n";
		exit;
*/
	}
	$row=mysqli_fetch_array($results);
	$Quantity=$row["ISTBESTAND"]+$row["MOCOMBESTAND"]+$row["ONLINEBESTAND"];
	$Quantity_real=$Quantity;
	if ($Quantity>15) $Quantity=15;
	//Mittelschalldämpfer & Endschalldämpfer
	if( $GART==3436 or $GART==3437 )
	{
		$Quantity=$Quantity_real;
	}
	//skip when steering gear
	if( $GART!=286 and $Quantity>0 )
	{
		//cancel low quantities
		if ($Quantity<2 and $row["ONLINEBESTAND"]==0)
		{
			$Quantity=0;
			q("	UPDATE ebay_auctions
				SET `Call`='EndItem',
					upload=1
				WHERE accountsite_id=".$accountsite["id_accountsite"]." AND shopitem_id=".$_POST["id_item"].";", $dbshop, __FILE__, __LINE__);
			echo '<ItemCreateAuctionsResponse>'."\n";
			echo '	<Ack>Error</Ack>'."\n";
			echo '	<Error>'."\n";
			echo '		<Code>'.__LINE__.'</Code>'."\n";
			echo '		<shortMsg>Artikel ohne Bestand.</shortMsg>'."\n";
			echo '		<longMsg>Der Artikel hat keinen oder einen zu geringen Bestand (<2). Bestehende Auktionen werden gegebenenfalls beendet.</longMsg>'."\n";
			echo '	</Error>'."\n";
			echo '</ItemCreateAuctionsResponse>'."\n";
			exit;
		}
	}
	//Quantity Single Brake Disc Fix
	if ( $GART==82 )
	{
		if ( strpos($SKU, "/2") === false )
		{
			$Quantity=0;
			echo '<ItemCreateAuctionsResponse>'."\n";
			echo '	<Ack>Error</Ack>'."\n";
			echo '	<Error>'."\n";
			echo '		<Code>'.__LINE__.'</Code>'."\n";
			echo '		<shortMsg>Einzelbremsscheibe erkannt.</shortMsg>'."\n";
			echo '		<longMsg>Einzelbremsscheiben können nicht als Auktion erstellt werden.</longMsg>'."\n";
			echo '	</Error>'."\n";
			echo '</ItemCreateAuctionsResponse>'."\n";
			exit;
		}
	}
/*
	//deactivated as talked about with Tobias Buls
	//Collateral Fix Brake Calipers
	if ( $item["menuitem_id"]==70  and $collateral>0 )
	{
		$Quantity=0;
		echo '<ItemCreateAuctionsResponse>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Bremssattel mit Altteilpfand.</shortMsg>'."\n";
		echo '		<longMsg>Bremssättel mit Altteilpfand können nicht als Auktion erstellt werden:</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ItemCreateAuctionsResponse>'."\n";
		exit;
	}
*/
	//BLACKLIST
	$results=q("SELECT * FROM shop_lists_items WHERE list_id=198 AND item_id=".$_POST["id_item"].";", $dbshop, __FILE__, __LINE__);
	if ( mysqli_num_rows($results)>0 )
	{
		 $Quantity=0;
		q("	UPDATE ebay_auctions
			SET `Call`='EndItem',
				upload=1
			WHERE accountsite_id=".$accountsite["id_accountsite"]." AND shopitem_id=".$_POST["id_item"].";", $dbshop, __FILE__, __LINE__);
		echo '<ItemCreateAuctionsResponse>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Artikel auf Blacklist.</shortMsg>'."\n";
		echo '		<longMsg>Der Artikel befindet sich auf der Blacklist. Auktionen können nicht erstellt werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ItemCreateAuctionsResponse>'."\n";
		exit;
	}

	//ReturnPolicy
	$ReturnPolicy  = '';
	$ReturnPolicy .= '	<Description>'.$accountsite["ReturnPolicyDescription"].'</Description>';
	$ReturnPolicy .= '	<ReturnsAcceptedOption>'.$accountsite["ReturnsAcceptedOption"].'</ReturnsAcceptedOption>';
	$ReturnPolicy .= '	<ReturnsWithinOption>'.$accountsite["ReturnsWithinOption"].'</ReturnsWithinOption>';
	$ReturnPolicy .= '	<ShippingCostPaidByOption>'.$accountsite["ShippingCostPaidByOption"].'</ShippingCostPaidByOption>';

	//SellerContactDetails
	$SellerContactDetails  = '';
	$SellerContactDetails .= '	<CompanyName>MAPCO Autotechnik GmbH</CompanyName>'."\n";
	$SellerContactDetails .= '	<County>DE</County>'."\n";
	$SellerContactDetails .= '	<PhoneAreaOrCityCode>033844</PhoneAreaOrCityCode>'."\n";
	$SellerContactDetails .= '	<PhoneCountryCode>DE</PhoneCountryCode>'."\n";
	$SellerContactDetails .= '	<PhoneLocalNumber>758227</PhoneLocalNumber>'."\n";
	$SellerContactDetails .= '	<Street>Moosweg 1</Street>'."\n";
	$SellerContactDetails .= '	<Street2>Gewerbegebiet</Street2>'."\n";


	//ShippingPackageDetailsWeight
	$ShippingPackageDetailsWeightMajor=0;
	$ShippingPackageDetailsWeightMinor=0;
	if( $item["GrossWeight"]>0 )
	{
		$ShippingPackageDetailsWeightMajor=bcdiv($item["GrossWeight"], 1000);
		$ShippingPackageDetailsWeightMinor=bcmod($item["GrossWeight"], 1000);
	}
	elseif( $item["ItemWeight"]>0 )
	{
		$weight=$item["ItemWeight"]*1.3;
		$ShippingPackageDetailsWeightMajor=bcdiv($weight, 1000);
		$ShippingPackageDetailsWeightMinor=bcmod($weight, 1000);
	}
	else
	{
		$weight=20;
		$ShippingPackageDetailsWeightMajor=bcdiv($weight, 1000);
		$ShippingPackageDetailsWeightMinor=bcmod($weight, 1000);
	}


	//ShippingServiceCost
	//is determined after Title


	//SKU
	//SKU is defined at the beginning and equals ArtNr


	//StartPrice
	$StartPrice=array();
	if ($accountsite["pricelist"]==16815)
	{
		$price=get_prices($_POST["id_item"], 1, 27991);
		$StartPrice2=round($price["gross"], 2); //mandatory
	}
	elseif ($accountsite["pricelist"]==18209)
	{
		$price=get_prices($_POST["id_item"], 1, 27992);
		$StartPrice2=round($price["gross"], 2); //mandatory
	}
	else
	{
		$results=q("SELECT * FROM prpos WHERE ARTNR='".$SKU."' AND LST_NR=".$accountsite["pricelist"].";", $dbshop, __FILE__, __LINE__);
		$row=mysqli_fetch_array($results);
		$StartPrice2=round($row6["POS_0_WERT"]*1.19, 2); //mandatory
	}
	//cheap item quantity adjustment
	if ( $StartPrice2 < 4 )
	{
		if( $Quantity_real>50 ) $Quantity=50;
	}
	//currency
	$StartPrice2=round($StartPrice2*$currency["exchange_rate_to_EUR"], 2);
	//low price fix
	if ($StartPrice2<1) $StartPrice2=1;
	//set start price for all auctions
	for($i=0; $i<$noa; $i++) $StartPrice[$i]=$StartPrice2;


	//BestOfferEnabled
	if ($StartPrice[$i]>10 and $_POST["bestoffer"]==1) $BestOfferEnabled=1; else $BestOfferEnabled=0;
	//BestOfferEnabled exceptions 26810, 26812, 26639
	if ( $SKU=="26810" or $SKU=="26812" or $SKU=="26639" )
	{
		$BestOfferEnabled=0;
	}
	if( $SKU=='61096/10' or $SKU=='61097/10' or $SKU=='61459/10' or $SKU=='61201/10' or $SKU=='61218/10' or $SKU=='61317/10' or $SKU=='61340/10' or $SKU=='64401/10' )
	{
		$BestOfferEnabled=0;
	}


	//StoreCategoryID, StoreCategory2ID
	$StoreCategoryID=$StoreCategoryID[$account["id_account"]][$GART];
	$StoreCategory2ID=$StoreCategory2ID[$account["id_account"]][$GART];
	if ( $StoreCategoryID == "") $StoreCategoryID=0;
	if ( $StoreCategory2ID == "") $StoreCategory2ID=0;


	//SubTitle
//	if ( $item["menuitem_id"]==87  and $collateral>0 )
	if ( $collateral>0 )
	{
		$SubTitle='Preis zzgl. Altteilpfand von '.number_format($collateral, 2).' Euro';
	}
	if ( $SubTitle=="" )
	{
		$results3=q("SELECT * FROM shop_items_".$lang." WHERE id_item=".$_POST["id_item"].";", $dbshop, __FILE__, __LINE__);
		$row3=mysqli_fetch_array($results3);
		$SubTitle=$row3["short_description"];
		$results7=q("SELECT * FROM mapco_replacements;", $dbweb, __FILE__, __LINE__);
		while ($row7=mysqli_fetch_array($results7))
		{
			$SubTitle=str_replace($row7["search"], $row7["replace"], $SubTitle);
		}
		$SubTitles=explode("; ", $SubTitle);
		$SubTitle2="";
		for($i=0; $i<sizeof($SubTitles); $i++)
		{
			if ( $i>0 ) $SubTitle2.='; ';
			$SubTitle2.=$SubTitles[$i];
			if ( strlen($SubTitle2)>55 ) break;
			$SubTitle=$SubTitle2;
		}
	}
	if( $SKU=='61096/10' or $SKU=='61097/10' or $SKU=='61459/10' or $SKU=='61201/10' or $SKU=='61218/10' or $SKU=='61317/10' or $SKU=='61340/10' or $SKU=='64401/10' )
	{
		$SubTitle='Ölfilterset 10 Stück';
	}


	//Title
	$results3=q("SELECT * FROM shop_items_".$lang." WHERE id_item=".$_POST["id_item"].";", $dbshop, __FILE__, __LINE__);
	$row3=mysqli_fetch_array($results3);
	$results4=q("SELECT * FROM shop_items_keywords WHERE GART=".$GART." AND language_id=".$accountsite["language_id"]." ORDER BY ordering;", $dbshop, __FILE__, __LINE__);
	if( mysqli_num_rows($results4)>0 )
	{
		$row4=mysqli_fetch_array($results4);
		$Title='MAPCO '.$row4["keyword"];
	}
	else
	{
		$Title='MAPCO '.substr($itemtitle, 0, strpos($row3["title"], "(")-1);
	}
	$short_description=$row3["short_description"];
	if ( strpos($row3["short_description"], "Aktivkohlefilter") !== false )
	{
		$Title="MAPCO Aktivkohlefilter";
	}
	if( $SKU=='61096/10' or $SKU=='61097/10' or $SKU=='61459/10' or $SKU=='61201/10' or $SKU=='61218/10' or $SKU=='61317/10' or $SKU=='61340/10' or $SKU=='64401/10' or $SKU=='64806/10')
	{
		$Title='10x '.$Title;
	}
	if( $SKU=='95944/5' )
	{
		$Title='5x '.$Title;
	}
	//Title - get titlelength
	$titlelength=strlen($Title);
	//Title - lumame + luftfilter
	if ( substr($SKU, 0, 2) == "42" )
	{
		if ( strpos($SKU, "/6") !== false )
		{
			$Title.=" + Luftfilter";
		}
	}
	//Title - ABS-Sensor + ABS-Ring
	if ( substr($SKU, 0, 2) == "86" )
	{
		if ( strpos($SKU, "/7") !== false )
		{
			$Title.=" + ABS-Ring";
		}
	}
	//Title - fitting position
	$crits=explode(";", $short_description);
	for($i=0; $i<sizeof($crits); $i++)
	{
		$crit=explode(":", $crits[$i]);
		if (trim($crit[0])=="Einbauseite")
		{
			$Title.=" ".trim($crit[1]);
		}
	}
	//Title - additional information
	for($i=0; $i<sizeof($ItemSpec); $i++)
	{
		if ( $ItemSpec[$i]["Name"]=="Ergänzungsartikel / Ergänzende Info 2" ) $Title.=" ".$ItemSpec[$i]["Value"];
	}
	//Title - mapco replacements
	$results7=q("SELECT * FROM mapco_replacements;", $dbweb, __FILE__, __LINE__);
	while ($row7=mysqli_fetch_array($results7))
	{
		$Title=str_replace($row7["search"], $row7["replace"], $Title);
	}
	$Description=str_replace("<!-- TITLE -->", $Title, $Description);
	$Titles2=array();
	for($i=0; $i<$noa; $i++)
	{
		$title=utf8_decode($Title);
		$part2=strlen(utf8_decode($auctions[$i]["Title"]));
		$cutlength=80-($part2+1);
		if($cutlength<0) $cutlength=0;
/*		
		if(strlen($title)>8)
		{
			$titlelength=strlen($Title);
			if( $titlelength === false ) $titlelength=strlen($title);
		}
		else $titlelength=strlen($title);
*/
		if( $cutlength<$titlelength ) $cutlength=$titlelength;
		$title=utf8_encode(substr($title, 0, $cutlength));
		$Titles2[$i]=$title." ".$auctions[$i]["Title"];
	}
	$Titles=$Titles2;


	//ShippingDetails
	$shipping=array();
	if ($accountsite["id_accountsite"]==1)
	{
		for($i=0; $i<sizeof($Titles); $i++)
		{
			$ShippingDetails[$i] .= '';
			$ShippingDetails[$i] .= '	<ShippingType>Flat</ShippingType>'."\n";
			if( $accountsite["ShippingDiscountProfileID"]!="" )
			{
				$ShippingDetails[$i] .= '	<ShippingDiscountProfileID>'.$accountsite["ShippingDiscountProfileID"].'</ShippingDiscountProfileID>'."\n";
			}
			if( $accountsite["InternationalShippingDiscountProfileID"]!="" )
			{
				$ShippingDetails[$i] .= '	<InternationalShippingDiscountProfileID>'.$accountsite["InternationalShippingDiscountProfileID"].'</InternationalShippingDiscountProfileID>'."\n";
			}

			//DHL National
			$j=0;
			$NationalShippingFee=$accountsite["NationalShippingFee"];
			if($auctions[$i]["Title"]=="")
			{
				$NationalShippingServiceCost[$i]=0;
				$StartPrice[$i]+=$NationalShippingFee;
			}
			else $NationalShippingServiceCost[$i]=$NationalShippingFee;
			$NationalShippingServiceCost[$i]=round($NationalShippingServiceCost[$i] * $currency["exchange_rate_to_EUR"], 2);

			$ShippingDetails[$i] .= '	<ShippingServiceOptions>'."\n";
			$ShippingDetails[$i] .= '		<ShippingService>DE_DHLPaket</ShippingService>'."\n";
			$ShippingDetails[$i] .= '		<ShippingServiceCost>'.str_replace(",", ".", $NationalShippingServiceCost[$i]).'</ShippingServiceCost>'."\n";
			$ShippingDetails[$i] .= '		<ShippingServiceAdditionalCost>0.00</ShippingServiceAdditionalCost>'."\n";
			$ShippingDetails[$i] .= '		<ShippingServicePriority>'.($j+1).'</ShippingServicePriority>'."\n";
			$ShippingDetails[$i] .= '	</ShippingServiceOptions>'."\n";
			/*
			$shipping[$i][$j]["ShippingService"]="DE_DHLPaket";
			$shipping[$i][$j]["ShippingServiceCost"]=str_replace(",", ".", $NationalShippingServiceCost[$i]);
			$shipping[$i][$j]["ShippingServiceAdditionalCost"]=0;
			$shipping[$i][$j]["ShippingServicePriority"]=($j+1);
			$shipping[$i][$j]["ShipToLocation"]="";
			*/

			//DHL National Nachnahme
			$j++;
			$ShippingServiceCost=8.90;
			if($auctions[$i]["Title"]=="") $ShippingServiceCost-=$NationalShippingFee;
			if($ShippingServiceCost<0) $ShippingServiceCost=0;
			$ShippingServiceCost=round($ShippingServiceCost * $currency["exchange_rate_to_EUR"], 2);

			$ShippingDetails[$i] .= '	<ShippingServiceOptions>'."\n";
			$ShippingDetails[$i] .= '		<ShippingService>DE_Nachname</ShippingService>'."\n";
			$ShippingDetails[$i] .= '		<ShippingServiceCost>'.$ShippingServiceCost.'</ShippingServiceCost>'."\n";
			$ShippingDetails[$i] .= '		<ShippingServiceAdditionalCost>0.00</ShippingServiceAdditionalCost>'."\n";
			$ShippingDetails[$i] .= '		<ShippingServicePriority>'.($j+1).'</ShippingServicePriority>'."\n";
			$ShippingDetails[$i] .= '	</ShippingServiceOptions>'."\n";

			//DHL Express National
			$j++;
			$weight=$auction["ShippingPackageDetailsWeightMajor"]*1000+$auction["ShippingPackageDetailsWeightMinor"];
			if( $weight<=0 ) $ShippingServiceCost=23.90;
			elseif( $weight<5000 ) $ShippingServiceCost=13.90;
			elseif( $weight<10000 ) $ShippingServiceCost=17.90;
			elseif( $weight<20000 ) $ShippingServiceCost=22.90;
			elseif( $weight<30000 ) $ShippingServiceCost=26.90;
			else
			{
				$ShippingServiceCost=ceil( (19.90+ceil(($weight-30000)/1000)*1.6)*1.1*1.19 )-0.1;
			}
			if($auctions[$i]["Title"]=="") $ShippingServiceCost-=$NationalShippingFee;
			if($ShippingServiceCost<0) $ShippingServiceCost=0;
			$ShippingServiceCost=round($ShippingServiceCost * $currency["exchange_rate_to_EUR"], 2);

			$ShippingDetails[$i] .= '	<ShippingServiceOptions>'."\n";
			$ShippingDetails[$i] .= '		<ShippingService>DE_Express</ShippingService>'."\n";
			$ShippingDetails[$i] .= '		<ShippingServiceCost>'.str_replace(",", ".", $ShippingServiceCost).'</ShippingServiceCost>'."\n";
			$ShippingDetails[$i] .= '		<ShippingServiceAdditionalCost>0.00</ShippingServiceAdditionalCost>'."\n";
			$ShippingDetails[$i] .= '		<ShippingServicePriority>'.($j+1).'</ShippingServicePriority>'."\n";
			$ShippingDetails[$i] .= '	</ShippingServiceOptions>'."\n";
			/*
			$shipping[$i][$j]["ShippingService"]="DE_Express";
			if($ShippingServiceCost<0) $ShippingServiceCost=0;
			$shipping[$i][$j]["ShippingServiceCost"]=str_replace(",", ".", $ShippingServiceCost);;
			$shipping[$i][$j]["ShippingServiceAdditionalCost"]=0.00;
			$shipping[$i][$j]["ShippingServicePriority"]=($j+1);
			$shipping[$i][$j]["ShipToLocation"]="";
			*/

			//Pickup
			$j++;

			$ShippingDetails[$i] .= '	<ShippingServiceOptions>'."\n";
			$ShippingDetails[$i] .= '		<ShippingService>DE_Pickup</ShippingService>'."\n";
			$ShippingDetails[$i] .= '		<ShippingServiceCost>4.50</ShippingServiceCost>'."\n";
			$ShippingDetails[$i] .= '		<ShippingServiceAdditionalCost>0.00</ShippingServiceAdditionalCost>'."\n";
			$ShippingDetails[$i] .= '		<ShippingServicePriority>'.($j+1).'</ShippingServicePriority>'."\n";
			$ShippingDetails[$i] .= '	</ShippingServiceOptions>'."\n";
			/*
			$shipping[$i][$j]["ShippingService"]="DE_Pickup";
			$shipping[$i][$j]["ShippingServiceCost"]=0.00;
			$shipping[$i][$j]["ShippingServiceAdditionalCost"]=0.00;
			$shipping[$i][$j]["ShippingServicePriority"]=($j+1);
			$shipping[$i][$j]["ShipToLocation"]="";
			*/

			//DHL International
			$ShippingServicePriority=0;
			$results=q("SELECT * FROM shop_countries WHERE country_code='AT' OR country_code='BG';", $dbshop, __FILE__, __LINE__);
			while( $row=mysqli_fetch_array($results) )
			{
				if( $row["country_code"]=="BG" ) $ShipToLocation="EuropeanUnion";
				else $ShipToLocation=$row["country_code"];
				$WeightInKG=$auction["ShippingPackageDetailsWeightMajor"]+$auction["ShippingPackageDetailsWeightMinor"]/1000;
				if($WeightInKG==0) $WeightInKG=20;
				$responseXml = post(PATH."soa/", array("API" => "dhl", "Action" => "ShipmentCostsGet", "shipping_type" => 1, "id_country" => $row["id_country"], "WeightInKG" => $WeightInKG));
				$use_errors = libxml_use_internal_errors(true);
				try
				{
					$response = new SimpleXMLElement($responseXml);
				}
				catch(Exception $e)
				{
					echo '<ReviseItemResponse>'."\n";
					echo '	<Ack>Failure</Ack>'."\n";
					echo '	<Error>'."\n";
					echo '		<Code>'.__LINE__.'</Code>'."\n";
					echo '		<shortMsg>Versandkostenpreis abrufen fehlgeschlagen.</shortMsg>'."\n";
					echo '		<longMsg>Beim Versuch die Versandkosten abzurufen, trat ein Fehler auf.</longMsg>'."\n";
					echo '	</Error>'."\n";
					echo '</ReviseItemResponse>'."\n";
					exit;
				}
				libxml_clear_errors();
				libxml_use_internal_errors($use_errors);
				if( $response->Ack != "Success")
				{
					echo '<ReviseItemResponse>'."\n";
					echo '	<Ack>Failure</Ack>'."\n";
					echo '	<Error>'."\n";
					echo '		<Code>'.__LINE__.'</Code>'."\n";
					echo '		<shortMsg>Versandkostenpreis abrufen fehlgeschlagen.</shortMsg>'."\n";
					echo '		<longMsg>Beim Versuch die Versandkosten abzurufen, trat ein Fehler auf.</longMsg>'."\n";
					echo '	</Error>'."\n";
					echo '</ReviseItemResponse>'."\n";
					exit;
				}
				$ShippingServiceCost=(float)$response->CustomerGrossPrice;
				$j++;
				$ShippingServicePriority++;
				if($auctions[$i]["Title"]=="") $ShippingServiceCost-=$NationalShippingFee;
				if($ShippingServiceCost<0) $ShippingServiceCost=0;
				$ShippingServiceCost=round($ShippingServiceCost * $currency["exchange_rate_to_EUR"], 2);

				$ShippingDetails[$i] .= '	<InternationalShippingServiceOption>'."\n";
				$ShippingDetails[$i] .= '		<ShippingService>DE_DHLPaketInternational</ShippingService>'."\n";
				$ShippingDetails[$i] .= '		<ShippingServiceCost>'.$ShippingServiceCost.'</ShippingServiceCost>'."\n";
				$ShippingDetails[$i] .= '		<ShippingServiceAdditionalCost>0.00</ShippingServiceAdditionalCost>'."\n";
				$ShippingDetails[$i] .= '		<ShippingServicePriority>'.$ShippingServicePriority.'</ShippingServicePriority>'."\n";
				$ShippingDetails[$i] .= '		<ShipToLocation>'.$ShipToLocation.'</ShipToLocation>'."\n";
				$ShippingDetails[$i] .= '	</InternationalShippingServiceOption>'."\n";
				/*
				$shipping[$i][$j]["ShippingService"]="DE_DHLPaketInternational";
				$shipping[$i][$j]["ShippingServiceCost"]=str_replace(",", ".", $ShippingServiceCost);
				$shipping[$i][$j]["ShippingServiceAdditionalCost"]=0;
				$shipping[$i][$j]["ShippingServicePriority"]=$ShippingServicePriority;
				$shipping[$i][$j]["ShipToLocation"]=$ShipToLocation;
				*/
			}
		}
	}
	if ($accountsite["id_accountsite"]==2)
	{
		for($i=0; $i<sizeof($Titles); $i++)
		{
			$ShippingDetails[$i] .= '';
			$ShippingDetails[$i] .= '	<ShippingType>Flat</ShippingType>'."\n";
			if( $accountsite["ShippingDiscountProfileID"]!="" )
			{
				$ShippingDetails[$i] .= '	<ShippingDiscountProfileID>'.$accountsite["ShippingDiscountProfileID"].'</ShippingDiscountProfileID>'."\n";
			}
			if( $accountsite["InternationalShippingDiscountProfileID"]!="" )
			{
				$ShippingDetails[$i] .= '	<InternationalShippingDiscountProfileID>'.$accountsite["InternationalShippingDiscountProfileID"].'</InternationalShippingDiscountProfileID>'."\n";
			}

			//DHL National
			$j=0;
			$NationalShippingFee=$accountsite["NationalShippingFee"];
			if($auctions[$i]["Title"]=="")
			{
				$NationalShippingServiceCost[$i]=0;
				$StartPrice[$i]+=$NationalShippingFee;
			}
			else $NationalShippingServiceCost[$i]=$NationalShippingFee;
			$NationalShippingServiceCost[$i]=round($NationalShippingServiceCost[$i] * $currency["exchange_rate_to_EUR"], 2);

			$ShippingDetails[$i] .= '	<ShippingServiceOptions>'."\n";
			$ShippingDetails[$i] .= '		<ShippingService>DE_DHLPaket</ShippingService>'."\n";
			$ShippingDetails[$i] .= '		<ShippingServiceCost>'.str_replace(",", ".", $NationalShippingServiceCost[$i]).'</ShippingServiceCost>'."\n";
			$ShippingDetails[$i] .= '		<ShippingServiceAdditionalCost>0.00</ShippingServiceAdditionalCost>'."\n";
			$ShippingDetails[$i] .= '		<ShippingServicePriority>'.($j+1).'</ShippingServicePriority>'."\n";
			$ShippingDetails[$i] .= '	</ShippingServiceOptions>'."\n";
			/*
			$shipping[$i][$j]["ShippingService"]="DE_DHLPaket";
			$shipping[$i][$j]["ShippingServiceCost"]=str_replace(",", ".", $NationalShippingServiceCost[$i]);
			$shipping[$i][$j]["ShippingServiceAdditionalCost"]=0;
			$shipping[$i][$j]["ShippingServicePriority"]=($j+1);
			$shipping[$i][$j]["ShipToLocation"]="";
			*/


			//DHL International
			$ShippingServicePriority=0;
			$results=q("SELECT * FROM shop_countries WHERE country_code='AT' OR country_code='BG';", $dbshop, __FILE__, __LINE__);
			while( $row=mysqli_fetch_array($results) )
			{
				if( $row["country_code"]=="BG" ) $ShipToLocation="EuropeanUnion";
				else $ShipToLocation=$row["country_code"];
				$WeightInKG=$auction["ShippingPackageDetailsWeightMajor"]+$auction["ShippingPackageDetailsWeightMinor"]/1000;
				if($WeightInKG==0) $WeightInKG=20;
				$responseXml = post(PATH."soa/", array("API" => "dhl", "Action" => "ShipmentCostsGet", "shipping_type" => 1, "id_country" => $row["id_country"], "WeightInKG" => $WeightInKG));
				$use_errors = libxml_use_internal_errors(true);
				try
				{
					$response = new SimpleXMLElement($responseXml);
				}
				catch(Exception $e)
				{
					echo '<ReviseItemResponse>'."\n";
					echo '	<Ack>Failure</Ack>'."\n";
					echo '	<Error>'."\n";
					echo '		<Code>'.__LINE__.'</Code>'."\n";
					echo '		<shortMsg>Versandkostenpreis abrufen fehlgeschlagen.</shortMsg>'."\n";
					echo '		<longMsg>Beim Versuch die Versandkosten abzurufen, trat ein Fehler auf.</longMsg>'."\n";
					echo '	</Error>'."\n";
					echo '</ReviseItemResponse>'."\n";
					exit;
				}
				libxml_clear_errors();
				libxml_use_internal_errors($use_errors);
				$ShippingServiceCost=(float)$response->CustomerGrossPrice;
				$j++;
				$ShippingServicePriority++;
				if($auctions[$i]["Title"]=="") $ShippingServiceCost-=$NationalShippingFee;
				if($ShippingServiceCost<0) $ShippingServiceCost=0;
				$ShippingServiceCost=round($ShippingServiceCost * $currency["exchange_rate_to_EUR"], 2);

				$ShippingDetails[$i] .= '	<InternationalShippingServiceOption>'."\n";
				$ShippingDetails[$i] .= '		<ShippingService>DE_DHLPaketInternational</ShippingService>'."\n";
				$ShippingDetails[$i] .= '		<ShippingServiceCost>'.$ShippingServiceCost.'</ShippingServiceCost>'."\n";
				$ShippingDetails[$i] .= '		<ShippingServiceAdditionalCost>0.00</ShippingServiceAdditionalCost>'."\n";
				$ShippingDetails[$i] .= '		<ShippingServicePriority>'.$ShippingServicePriority.'</ShippingServicePriority>'."\n";
				$ShippingDetails[$i] .= '		<ShipToLocation>'.$ShipToLocation.'</ShipToLocation>'."\n";
				$ShippingDetails[$i] .= '	</InternationalShippingServiceOption>'."\n";
				/*
				$shipping[$i][$j]["ShippingService"]="DE_DHLPaketInternational";
				$shipping[$i][$j]["ShippingServiceCost"]=str_replace(",", ".", $ShippingServiceCost);
				$shipping[$i][$j]["ShippingServiceAdditionalCost"]=0;
				$shipping[$i][$j]["ShippingServicePriority"]=$ShippingServicePriority;
				$shipping[$i][$j]["ShipToLocation"]=$ShipToLocation;
				*/
			}
		}
	}
	if ($accountsite["id_accountsite"]==8)
	{
		for($i=0; $i<sizeof($Titles); $i++)
		{
			$ShippingDetails[$i] .= '';
			$ShippingDetails[$i] .= '	<ShippingType>Flat</ShippingType>'."\n";
			if( $accountsite["ShippingDiscountProfileID"]!="" )
			{
				$ShippingDetails[$i] .= '	<ShippingDiscountProfileID>'.$accountsite["ShippingDiscountProfileID"].'</ShippingDiscountProfileID>'."\n";
			}
			if( $accountsite["InternationalShippingDiscountProfileID"]!="" )
			{
				$ShippingDetails[$i] .= '	<InternationalShippingDiscountProfileID>'.$accountsite["InternationalShippingDiscountProfileID"].'</InternationalShippingDiscountProfileID>'."\n";
			}

			//DHL "National"
			$j=0;
			$NationalShippingFee=$accountsite["NationalShippingFee"];
			if($auctions[$i]["Title"]=="")
			{
				$NationalShippingServiceCost[$i]=0;
				$StartPrice[$i]+=$NationalShippingFee;
			}
			else $NationalShippingServiceCost[$i]=$NationalShippingFee;
			$NationalShippingServiceCost[$i]=round($NationalShippingServiceCost[$i] * $currency["exchange_rate_to_EUR"], 2);

			$ShippingDetails[$i] .= '	<ShippingServiceOptions>'."\n";
			$ShippingDetails[$i] .= '		<ShippingService>UK_SellersStandardRate</ShippingService>'."\n";
			$ShippingDetails[$i] .= '		<ShippingServiceCost>'.str_replace(",", ".", $NationalShippingServiceCost[$i]).'</ShippingServiceCost>'."\n";
			$ShippingDetails[$i] .= '		<ShippingServiceAdditionalCost>0.00</ShippingServiceAdditionalCost>'."\n";
			$ShippingDetails[$i] .= '		<ShippingServicePriority>'.($j+1).'</ShippingServicePriority>'."\n";
			$ShippingDetails[$i] .= '	</ShippingServiceOptions>'."\n";
			/*
			$shipping[$i][$j]["ShippingService"]="DE_DHLPaket";
			$shipping[$i][$j]["ShippingServiceCost"]=str_replace(",", ".", $NationalShippingServiceCost[$i]);
			$shipping[$i][$j]["ShippingServiceAdditionalCost"]=0;
			$shipping[$i][$j]["ShippingServicePriority"]=($j+1);
			$shipping[$i][$j]["ShipToLocation"]="";
			*/

			//DHL International
			$ShippingServicePriority=0;
			$results=q("SELECT * FROM shop_countries WHERE country_code='BG';", $dbshop, __FILE__, __LINE__);
			while( $row=mysqli_fetch_array($results) )
			{
				if( $row["country_code"]=="BG" ) $ShipToLocation="EuropeanUnion";
				else $ShipToLocation=$row["country_code"];
				$WeightInKG=$auction["ShippingPackageDetailsWeightMajor"]+$auction["ShippingPackageDetailsWeightMinor"]/1000;
				if($WeightInKG==0) $WeightInKG=20;
				$responseXml = post(PATH."soa/", array("API" => "dhl", "Action" => "ShipmentCostsGet", "shipping_type" => 1, "id_country" => $row["id_country"], "WeightInKG" => $WeightInKG));
				$use_errors = libxml_use_internal_errors(true);
				try
				{
					$response = new SimpleXMLElement($responseXml);
				}
				catch(Exception $e)
				{
					echo '<ReviseItemResponse>'."\n";
					echo '	<Ack>Failure</Ack>'."\n";
					echo '	<Error>'."\n";
					echo '		<Code>'.__LINE__.'</Code>'."\n";
					echo '		<shortMsg>Versandkostenpreis abrufen fehlgeschlagen.</shortMsg>'."\n";
					echo '		<longMsg>Beim Versuch die Versandkosten abzurufen, trat ein Fehler auf.</longMsg>'."\n";
					echo '	</Error>'."\n";
					echo '</ReviseItemResponse>'."\n";
					exit;
				}
				libxml_clear_errors();
				libxml_use_internal_errors($use_errors);
				$ShippingServiceCost=(float)$response->CustomerGrossPrice;
				$j++;
				$ShippingServicePriority++;
				if($auctions[$i]["Title"]=="") $ShippingServiceCost-=$NationalShippingFee;
				if($ShippingServiceCost<0) $ShippingServiceCost=0;
				$ShippingServiceCost=round($ShippingServiceCost * $currency["exchange_rate_to_EUR"], 2);

				$ShippingDetails[$i] .= '	<InternationalShippingServiceOption>'."\n";
				$ShippingDetails[$i] .= '		<ShippingService>DE_DHLPaketInternational</ShippingService>'."\n";
				$ShippingDetails[$i] .= '		<ShippingServiceCost>'.$ShippingServiceCost.'</ShippingServiceCost>'."\n";
				$ShippingDetails[$i] .= '		<ShippingServiceAdditionalCost>0.00</ShippingServiceAdditionalCost>'."\n";
				$ShippingDetails[$i] .= '		<ShippingServicePriority>'.$ShippingServicePriority.'</ShippingServicePriority>'."\n";
				$ShippingDetails[$i] .= '		<ShipToLocation>'.$ShipToLocation.'</ShipToLocation>'."\n";
				$ShippingDetails[$i] .= '	</InternationalShippingServiceOption>'."\n";
				/*
				$shipping[$i][$j]["ShippingService"]="DE_DHLPaketInternational";
				$shipping[$i][$j]["ShippingServiceCost"]=str_replace(",", ".", $ShippingServiceCost);
				$shipping[$i][$j]["ShippingServiceAdditionalCost"]=0;
				$shipping[$i][$j]["ShippingServicePriority"]=$ShippingServicePriority;
				$shipping[$i][$j]["ShipToLocation"]=$ShipToLocation;
				*/
			}
		}
	}
	if ($accountsite["id_accountsite"]==9)
	{
		for($i=0; $i<sizeof($Titles); $i++)
		{
			$ShippingDetails[$i] .= '';
			$ShippingDetails[$i] .= '	<ShippingType>Flat</ShippingType>'."\n";
			if( $accountsite["ShippingDiscountProfileID"]!="" )
			{
				$ShippingDetails[$i] .= '	<ShippingDiscountProfileID>'.$accountsite["ShippingDiscountProfileID"].'</ShippingDiscountProfileID>'."\n";
			}
			if( $accountsite["InternationalShippingDiscountProfileID"]!="" )
			{
				$ShippingDetails[$i] .= '	<InternationalShippingDiscountProfileID>'.$accountsite["InternationalShippingDiscountProfileID"].'</InternationalShippingDiscountProfileID>'."\n";
			}

			//DHL "National"
			$j=0;
			$NationalShippingFee=$accountsite["NationalShippingFee"];
			if($auctions[$i]["Title"]=="")
			{
				$NationalShippingServiceCost[$i]=0;
				$StartPrice[$i]+=$NationalShippingFee;
			}
			else $NationalShippingServiceCost[$i]=$NationalShippingFee;
			$NationalShippingServiceCost[$i]=round($NationalShippingServiceCost[$i] * $currency["exchange_rate_to_EUR"], 2);

			$ShippingDetails[$i] .= '	<ShippingServiceOptions>'."\n";
			$ShippingDetails[$i] .= '		<ShippingService>IT_TrackedDeliveryFromAbroad</ShippingService>'."\n";
			$ShippingDetails[$i] .= '		<ShippingServiceCost>'.str_replace(",", ".", $NationalShippingServiceCost[$i]).'</ShippingServiceCost>'."\n";
			$ShippingDetails[$i] .= '		<ShippingServiceAdditionalCost>0.00</ShippingServiceAdditionalCost>'."\n";
			$ShippingDetails[$i] .= '		<ShippingServicePriority>'.($j+1).'</ShippingServicePriority>'."\n";
			$ShippingDetails[$i] .= '	</ShippingServiceOptions>'."\n";
			/*
			$shipping[$i][$j]["ShippingService"]="DE_DHLPaket";
			$shipping[$i][$j]["ShippingServiceCost"]=str_replace(",", ".", $NationalShippingServiceCost[$i]);
			$shipping[$i][$j]["ShippingServiceAdditionalCost"]=0;
			$shipping[$i][$j]["ShippingServicePriority"]=($j+1);
			$shipping[$i][$j]["ShipToLocation"]="";
			*/

			//DHL International
			$ShippingServicePriority=0;
			$results=q("SELECT * FROM shop_countries WHERE country_code='BG';", $dbshop, __FILE__, __LINE__);
			while( $row=mysqli_fetch_array($results) )
			{
				if( $row["country_code"]=="BG" ) $ShipToLocation="EuropeanUnion";
				else $ShipToLocation=$row["country_code"];
				$WeightInKG=$auction["ShippingPackageDetailsWeightMajor"]+$auction["ShippingPackageDetailsWeightMinor"]/1000;
				if($WeightInKG==0) $WeightInKG=20;
				$responseXml = post(PATH."soa/", array("API" => "dhl", "Action" => "ShipmentCostsGet", "shipping_type" => 1, "id_country" => $row["id_country"], "WeightInKG" => $WeightInKG));
				$use_errors = libxml_use_internal_errors(true);
				try
				{
					$response = new SimpleXMLElement($responseXml);
				}
				catch(Exception $e)
				{
					echo '<ReviseItemResponse>'."\n";
					echo '	<Ack>Failure</Ack>'."\n";
					echo '	<Error>'."\n";
					echo '		<Code>'.__LINE__.'</Code>'."\n";
					echo '		<shortMsg>Versandkostenpreis abrufen fehlgeschlagen.</shortMsg>'."\n";
					echo '		<longMsg>Beim Versuch die Versandkosten abzurufen, trat ein Fehler auf.</longMsg>'."\n";
					echo '	</Error>'."\n";
					echo '</ReviseItemResponse>'."\n";
					exit;
				}
				libxml_clear_errors();
				libxml_use_internal_errors($use_errors);
				$ShippingServiceCost=(float)$response->CustomerGrossPrice;
				$j++;
				$ShippingServicePriority++;
				if($auctions[$i]["Title"]=="") $ShippingServiceCost-=$NationalShippingFee;
				if($ShippingServiceCost<0) $ShippingServiceCost=0;
				$ShippingServiceCost=round($ShippingServiceCost * $currency["exchange_rate_to_EUR"], 2);

				$ShippingDetails[$i] .= '	<InternationalShippingServiceOption>'."\n";
				$ShippingDetails[$i] .= '		<ShippingService>IT_OtherInternational</ShippingService>'."\n";
				$ShippingDetails[$i] .= '		<ShippingServiceCost>'.$ShippingServiceCost.'</ShippingServiceCost>'."\n";
				$ShippingDetails[$i] .= '		<ShippingServiceAdditionalCost>0.00</ShippingServiceAdditionalCost>'."\n";
				$ShippingDetails[$i] .= '		<ShippingServicePriority>'.$ShippingServicePriority.'</ShippingServicePriority>'."\n";
				$ShippingDetails[$i] .= '		<ShipToLocation>'.$ShipToLocation.'</ShipToLocation>'."\n";
				$ShippingDetails[$i] .= '	</InternationalShippingServiceOption>'."\n";
				/*
				$shipping[$i][$j]["ShippingService"]="DE_DHLPaketInternational";
				$shipping[$i][$j]["ShippingServiceCost"]=str_replace(",", ".", $ShippingServiceCost);
				$shipping[$i][$j]["ShippingServiceAdditionalCost"]=0;
				$shipping[$i][$j]["ShippingServicePriority"]=$ShippingServicePriority;
				$shipping[$i][$j]["ShipToLocation"]=$ShipToLocation;
				*/
			}
		}
	}


	//Version
	$Version=$accountsite["Version"];

//	print_r($auctions);
//	exit;

	/*******************
	 * CREATE AUCTIONS *
	 *******************/
	//get auctions for item_id
	$auctions_id=array();
	$auctions_ItemID=array();
	$todo=array();
	$todo_action=array();
	$results=q("SELECT id_auction FROM ebay_auctions WHERE shopitem_id='".$_POST["id_item"]."' AND accountsite_id=".$accountsite["id_accountsite"]." AND `Call`='EndItem' AND upload=0 ORDER BY firstmod;", $dbshop, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		q("UPDATE ebay_auctions SET upload=1 WHERE id_auction=".$row["id_auction"].";", $dbshop, __FILE__, __LINE__);
	}
	$results=q("SELECT id_auction, ItemID FROM ebay_auctions WHERE shopitem_id='".$_POST["id_item"]."' AND accountsite_id=".$accountsite["id_accountsite"]." AND NOT `Call`='EndItem' ORDER BY firstmod;", $dbshop, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		$auctions_id[]=$row["id_auction"];
		$auctions_ItemID[]=$row["ItemID"];
	}

	//update existing auctions
	for($i=0; $i<sizeof($auctions_id); $i++)
	{
		if ( $i<$noa )
		{
			if ($auctions_ItemID[$i]==0) $Call='AddItem'; else $Call='ReviseItem';
			if ($Quantity==0) $Call='EndItem';
			if ( $item["active"]==0 ) $Call='EndItem';

			q("UPDATE ebay_auctions
			   SET	`Call`='".$Call."',
			   		BestOfferEnabled=".$BestOfferEnabled.",
			   		CategoryID=".$CategoryID.",
					CategoryID2=".$CategoryID2.",
					CategoryMappingAllowed=".$CategoryMappingAllowed.",
					ConditionID=".$ConditionID.",
					Country='".$Country."',
					Currency='".$Currency."',
					Description='".mysqli_real_escape_string($dbshop, $Description)."',
					DiscountPercent='".$DiscountPercent."',
					ItemCompatibilityList='".mysqli_real_escape_string($dbshop, $ItemCompatibilityList[$i])."',
					ItemID=".$auctions_ItemID[$i].",
					ItemSpecifics='".mysqli_real_escape_string($dbshop, $ItemSpecifics)."',
					ListingDuration='".$ListingDuration."',
					ListingType='".$ListingType."',
					PictureDetails='".mysqli_real_escape_string($dbshop, $PictureDetails)."',
					Quantity=".$Quantity.",
					ReturnPolicy='".mysqli_real_escape_string($dbshop, $ReturnPolicy)."',
					SellerContactDetails='".mysqli_real_escape_string($dbshop, $SellerContactDetails)."',
					ShippingDetails='".mysqli_real_escape_string($dbshop, $ShippingDetails[$i])."',
					ShippingPackageDetailsWeightMajor=".$ShippingPackageDetailsWeightMajor.",
					ShippingPackageDetailsWeightMinor=".$ShippingPackageDetailsWeightMinor.",
					ShippingServiceCost=".$NationalShippingServiceCost[$i].",
					SKU='".mysqli_real_escape_string($dbshop, $SKU)."',
					StartPrice=".$StartPrice[$i].",
					StoreCategoryID=".$StoreCategoryID.",
					StoreCategory2ID=".$StoreCategory2ID.",
					SubTitle='".mysqli_real_escape_string($dbshop, $SubTitle)."',
					Title='".mysqli_real_escape_string($dbshop, $Titles[$i])."',
					Version='".$Version."',
					upload=1,
					lastmod=".time().",
					lastmod_user=".$_SESSION["id_user"]."
			   WHERE id_auction=".$auctions_id[$i].";", $dbshop, __FILE__, __LINE__);
			$todo[]=$auctions_id[$i];
			$todo_action[]=$Call;
			
			//add auctions shipping services
			/*
			q("DELETE FROM ebay_auctions_shipping WHERE auction_id=".$auctions_id[$i].";", $dbshop, __FILE__, __LINE__);
			for($j=0; $j<sizeof($shipping[$i]); $j++)
			{
				q("INSERT INTO ebay_auctions_shipping (auction_id, ShippingService, ShippingServiceCost, ShippingServiceAdditionalCost, ShippingServicePriority, ShipToLocation) VALUES(".$auctions_id[$i].", '".$shipping[$i][$j]["ShippingService"]."', ".$shipping[$i][$j]["ShippingServiceCost"].", ".$shipping[$i][$j]["ShippingServiceAdditionalCost"].", ".$shipping[$i][$j]["ShippingServicePriority"].", '".$shipping[$i][$j]["ShipToLocation"]."');", $dbshop, __FILE__, __LINE__);
			}
			*/
		}
		//end further auctions
		else
		{
			if ( $auctions_ItemID[$i]>0 )
			{
				q("UPDATE ebay_auctions
				   SET `Call`='EndItem',
				   		upload=1,
						lastmod=".time().",
						lastmod_user=".$_SESSION["id_user"]."
				   WHERE id_auction=".$auctions_id[$i].";", $dbshop, __FILE__, __LINE__);
			   $todo[]=$auctions_id[$i];
			   $todo_action[]="EndItem";
			}
			else
			{
				q("DELETE FROM ebay_auctions WHERE id_auction=".$auctions_id[$i].";", $dbshop, __FILE__, __LINE__);
			}
		}
	} //end for($i=0; $i<sizeof($auctions_id); $i++)


	//add further auctions
	if ( $Quantity>0 and $item["active"]>0 )
	{
		for($i; $i<$noa; $i++)
		{
			q("INSERT INTO ebay_auctions (`Call`, shopitem_id, account_id, accountsite_id, BestOfferEnabled, CategoryID, CategoryID2, CategoryMappingAllowed, ConditionID, Country, Currency, Description, DiscountPercent, ItemCompatibilityList, ItemID, ItemSpecifics, ListingDuration, ListingType, PictureDetails, Quantity, ReturnPolicy, SellerContactDetails, ShippingDetails, ShippingPackageDetailsWeightMajor, ShippingPackageDetailsWeightMinor, ShippingServiceCost, SKU, StartPrice, StoreCategoryID, StoreCategory2ID, SubTitle, Title, Version, upload, firstmod, firstmod_user, lastmod, lastmod_user, lastupdate) VALUES('AddItem', ".$_POST["id_item"].", ".$account["id_account"].", ".$accountsite["id_accountsite"].", ".$BestOfferEnabled.", ".$CategoryID.", ".$CategoryID2.", ".$CategoryMappingAllowed.", ".$ConditionID.", '".$Country."', '".$Currency."', '".mysqli_real_escape_string($dbshop, $Description)."', ".$DiscountPercent.", '".mysqli_real_escape_string($dbshop, $ItemCompatibilityList[$i])."', 0, '".mysqli_real_escape_string($dbshop, $ItemSpecifics)."', '".$ListingDuration."', '".$ListingType."', '".mysqli_real_escape_string($dbshop, $PictureDetails)."', ".$Quantity.", '".mysqli_real_escape_string($dbshop, $ReturnPolicy)."', '".mysqli_real_escape_string($dbshop, $SellerContactDetails)."', '".mysqli_real_escape_string($dbshop, $ShippingDetails[$i])."', ".$ShippingPackageDetailsWeightMajor.", ".$ShippingPackageDetailsWeightMinor.", ".$NationalShippingServiceCost[$i].", '".mysqli_real_escape_string($dbshop, $SKU)."', ".$StartPrice[$i].", ".$StoreCategoryID.", ".$StoreCategory2ID.", '".mysqli_real_escape_string($dbshop, $SubTitle)."', '".mysqli_real_escape_string($dbshop, $Titles[$i])."', '".$Version."', 1, ".time().", ".$_SESSION["id_user"].", ".time().", ".$_SESSION["id_user"].", 0);", $dbshop, __FILE__, __LINE__);
			$id_auction=mysqli_insert_id($dbshop);
			$todo[]=$id_auction;
		   $todo_action[]="AddItem";

			//add auctions shipping services
			/*
			for($j=0; $j<sizeof($shipping[$i]); $j++)
			{
				q("INSERT INTO ebay_auctions_shipping (auction_id, ShippingService, ShippingServiceCost, ShippingServiceAdditionalCost, ShippingServicePriority, ShipToLocation) VALUES(".$auctions_id[$i].", '".$shipping[$i][$j]["ShippingService"]."', ".$shipping[$i][$j]["ShippingServiceCost"].", ".$shipping[$i][$j]["ShippingServiceAdditionalCost"].", ".$shipping[$i][$j]["ShippingServicePriority"].", '".$shipping[$i][$j]["ShipToLocation"]."');", $dbshop, __FILE__, __LINE__);
			}
			*/
		}
	}

	//return success
	echo '<ItemsCreateAuctionsResponse>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	for($i=0; $i<sizeof($todo); $i++)
	{
		echo '	<AuctionID action="'.$todo_action[$i].'">'.$todo[$i].'</AuctionID>'."\n";
	}
	echo '</ItemsCreateAuctionsResponse>'."\n";
?>
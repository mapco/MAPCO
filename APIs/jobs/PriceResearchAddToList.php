<?php

//	session_start();
//	include("../config.php");

	$JobResponse="";
	$listcounter=0;

	//GET SELLER
	$res_seller = q("SELECT * FROM cms_users WHERE userrole_id = 4;", $dbweb, __FILE__, __LINE__);
	while ($row_seller = mysqli_fetch_array($res_seller))
	{
		if ($row_seller["username"]!="mpeter@mapco.de" && $row_seller["username"]!="sjendry" && $row_seller["username"]!="abraun" )
		{
			$seller[$row_seller["id_user"]]=$row_seller["name"];
		}
	}

	//GET PRICE_RESEARCH_LISTS
	$res_lists = q("SELECT * FROM shop_lists WHERE listtype_id = 4;", $dbshop, __FILE__, __LINE__);
	while ($row_lists = mysqli_fetch_array($res_lists))
	{
		$lists[$row_lists["firstmod_user"]]=$row_lists["id_list"];
		$lists_exist[$row_lists["id_list"]]=$row_lists["id_list"];
	}

	//CHECK ITEMS IN PRICE RESEARCH 
	$res_list_items=q("SELECT * FROM shop_lists_items WHERE list_id IN (".implode(", ", $lists_exist).") ;", $dbshop, __FILE__, __LINE__);
	while ($row_list_items = mysqli_fetch_array($res_list_items))
	{
		$list_items[$row_list_items["item_id"]]=$row_list_items["item_id"];
	}
	$i=0;
	$res_research=q("SELECT id_item, MPN FROM shop_items WHERE InPriceResearch=1 ;", $dbshop, __FILE__, __LINE__);
	while ($row_research = mysqli_fetch_array($res_research))
	{
		if(!isset($list_items[$row_research["id_item"]]))
		{
			q("UPDATE shop_items SET InPriceResearch=0 WHERE id_item=".$row_research["id_item"].";", $dbshop, __FILE__, __LINE__);
			$i++;
		}
	}
	if ($i>0) echo $i.' Artikel wurden in keiner Rechercheliste gefunden und zurück gesetzt.'."\n";
	
	//CHECK IF LISTS EXISTS FOR EACH SELLER
	while (list ($user, $name) = each ($seller))
	{
		//IF LISTS DOESN'T EXISTS -> CREATE LIST
		if (!isset($lists[$user]))
		{
			q("INSERT INTO shop_lists (
				title,
				listtype_id,
				private,
				firstmod,
				firstmod_user,
				lastmod,
				lastmod_user
			) VALUES (
				'".mysqli_real_escape_string($dbshop, "Preisrechercheliste ".$name)."',
				4,
				0,
				".time().",
				".$user.",
				".time().",
				".$user."
			);", $dbshop, __FILE__, __LINE__);
			
			$lists[$user]=mysqli_insert_id($dbshop);
			
			$JobResponse.='Es wurde eine Rechercheliste für '.$name.' angelegt.<br />';
			
		}
	}
	
	//ADD ITEMS TO LISTS
	
	while (list ($user, $list_id) = each ($lists))
	{
echo		$responseXML2 = post(PATH."soa/", array("API" => "shop", "Action" => "PriceResearchAddToList", "id_list" => $list_id));

		$use_errors = libxml_use_internal_errors(true);
		try
		{
			$response2 = new SimpleXMLElement($responseXML2);
		}
		catch(Exception $e)
		{
			echo '<PriceReasearchAddToListResponse>'."\n";
			echo '	<Ack>Failure</Ack>'."\n";
			echo '	<Error>'."\n";
			echo '		<Code>'.__LINE__.'</Code>'."\n";
			echo '		<shortMsg>XML nicht valide.</shortMsg>'."\n";
			echo '		<longMsg>Die zurückgelieferten XML-Daten sind nicht valide und können deshalb nicht ausgewertet werden. Service gestoppt.</longMsg>'."\n";
			echo '	</Error>'."\n";
			echo '</PriceReasearchAddToListResponse>'."\n";
			exit;
		}
		libxml_clear_errors();
		libxml_use_internal_errors($use_errors);
		if ($response2->Ack[0]=="Success")
		{
			$JobResponse.='Es wurden '.$response2->Items[0].' Artikel zur Rechercheliste von '.$seller[$user].' hinzugefügt.<br />';
			$listcounter++;
		}
		else
		{
			$JobResponse.=$response2->Error[0]->Code[0]." - ".$response2->Error[0]->shortMsg[0]." - ".$response2->Error[0]->longMsg[0]."<br />";
		}
	}

echo '<PriceReasearchAddToListResponse>'."\n";
echo '	<Ack>Success</Ack>'."\n";
echo '	<shortMsg><!CDATA[Es wurden Artikel zu '.$listcounter.' Recherchelisten hinzugefügt.]]></shortMsg>'."\n";
echo '	<longMsg><!CDATA['.$JobResponse.']]></longMsg>'."\n";
echo '</PriceReasearchAddToListResponse>'."\n";

		
?>
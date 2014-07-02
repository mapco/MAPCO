<?php
	/*************************
	********** SOA 2 *********
	*************************/
	
	$required=array("lang"	=> "text");
	check_man_params($required);
	
	$lang = $_POST['lang'];
	
	//language
	$result=q("SELECT code FROM cms_languages WHERE id_language=".$lang.";", $dbweb, __FILE__, __LINE__);
	$row=mysqli_fetch_array($result);
	$language = $row["code"];

	$xml = '';
	
	//LABEL
	$results = q('SELECT id_label, label FROM cms_labels WHERE site_id IN (0, '.$_SESSION["id_site"].') ORDER BY label ASC', $dbweb , __FILE__, __LINE__);
	while($row = mysqli_fetch_assoc($results))
	{
		$labels[$row['id_label']] = $row["label"];
	}
	
	//GART
	$results=q("SELECT distinct GART FROM t_200 order by GART;", $dbshop, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{ 
		$GART[$row["GART"]*1]="";
	}
	$results=q("SELECT BezNr, GenArtNr FROM t_320;", $dbshop, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{ 
		if (isset($GART[$row["GenArtNr"]*1])) $GART[$row["GenArtNr"]*1]=$row["BezNr"];
	}
	$results=q("SELECT Bez, BezNr FROM t_030 where SprachNr = '1';", $dbshop, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{ 
		$GART_Bez[$row["BezNr"]]=$row["Bez"];	
	}
	
	//GART-ID mit Bezeichnung direkt verknüpfen
	while (list($key, $val) = each ($GART))
	{
		$Gart_Names[$key]=$GART_Bez[$val];
	}
	$OK=asort($Gart_Names);	
	
	while (list($key, $val) = each ($Gart_Names))
	{	
			$xml .= '	<gart_option>'."\n";
			$xml .= '		<option_value><![CDATA['.$key.']]></option_value>'."\n";
			$xml .= '		<option_text><![CDATA['.$val.']]></option_text>'."\n";
			$xml .= '	</gart_option>'."\n";
	}
	
	//imageprofile
	$results=q("SELECT * FROM cms_imageprofiles ORDER BY title;", $dbweb, __FILE__, __LINE__);
	while($row=mysqli_fetch_array($results))
	{
		$xml .= '<imageprofile_option>'."\n";
		$xml .= '	<option_value><![CDATA['.$row["id_imageprofile"].']]></option_value>'."\n";
		$xml .= '	<option_text><![CDATA['.$row["title"].']]></option_text>'."\n";
		$xml .= '</imageprofile_option>'."\n";
	}

	if( isset($_POST['article_id']) && $_POST['article_id'] != 0 && $_POST['article_id'] != '' )
	{
		$required=array("article_id"	=> "numeric", "lang"	=> "text");
		check_man_params($required);
		
		$article_id = $_POST['article_id'];
			
		$results=q("SELECT * FROM cms_articles_gart WHERE article_id=".$article_id.";", $dbweb, __FILE__, __LINE__);
		
		while( $row=mysqli_fetch_array($results) )
		{
			$xml .= '<gart_articles>'."\n";
			$xml .= '	<gart_bez><![CDATA['.$GART_Bez[$GART[$row["GART_id"]*1]].']]></gart_bez>'."\n";
			$xml .= '	<gart_id>'.$row["id"].'</gart_id>'."\n";
			$xml .= '</gart_articles>'."\n";
		}
		
		$query="SELECT label_id, id FROM cms_articles_labels WHERE article_id=".$article_id." ORDER BY ordering";
		$results2=q($query, $dbweb, __FILE__, __LINE__);
		$label_count2=mysqli_num_rows($results2);
		$tmp_labels = $labels;
		while($row2=mysqli_fetch_array($results2))
		{
			$xml .= '<article_labels>'."\n";
			$xml .= '	<id_label><![CDATA['.$row2["id"].']]></id_label>'."\n";
			$xml .= '	<label><![CDATA['.$labels[$row2['label_id']].']]></label>'."\n";
			$xml .= '	<label_id><![CDATA['.$row2["label_id"].']]></label_id>'."\n";
			$xml .= '</article_labels>'."\n";
			unset($labels[$row2['label_id']]);
		}
			
		//GET ARTIKELDATA DATA
		$result=q("SELECT id_article, language_id, title AS article_title, introduction AS article_introduction, article AS article_text, published AS article_published, format AS article_format, imageprofile_id, meta_title AS article_meta_title, meta_keywords AS article_meta_keywords, meta_description AS article_meta_description FROM cms_articles WHERE id_article=".$article_id.";", $dbweb, __FILE__, __LINE__);
		$row = mysqli_fetch_assoc($result);
		 
		foreach($row as $key => $value)
		{
			$xml .= '	<'.$key.'><![CDATA['.$value.']]></'.$key.'>'."\n";	
		}
		
		$result=q("SELECT id, item_id FROM cms_articles_shopitems WHERE article_id=".$article_id.";", $dbweb, __FILE__, __LINE__);
		while( $row=mysqli_fetch_assoc($result) )
		{
			$result2=q("SELECT title FROM shop_items_".$language." WHERE id_item=".$row["item_id"].";", $dbshop, __FILE__, __LINE__);
			$row2=mysqli_fetch_assoc($result2);
			$xml .= '<article_shopitems>'."\n";
			$xml .= '	<shoparticle_id><![CDATA['.$row["id"].']]></shoparticle_id>'."\n";
			$xml .= '	<shoparticle_title><![CDATA['.$row2["title"].']]></shoparticle_title>'."\n";
			$xml .= '</article_shopitems>'."\n";
		}
		unset($result2);
		unset($row2);
				
		$result=q("SELECT id_article, language_id, published FROM cms_articles WHERE article_id=".$article_id.";", $dbweb, __FILE__, __LINE__);
		while( $row=mysqli_fetch_assoc($result) )
		{
			$translations[$row['language_id']]['id'] = $row['id_article'];
			$translations[$row['language_id']]['published'] = $row['published'];
		}
		$translations[$lang]['id'] = $article_id;
	}

	//language
	$results=q("SELECT id_language, language, code FROM cms_languages ORDER BY ordering;", $dbweb, __FILE__, __LINE__);
	while($row=mysqli_fetch_array($results))
	{
		$xml .= '<languages>'."\n";
		$xml .= '	<language_text><![CDATA['.$row["language"].']]></language_text>'."\n";
		$xml .= '	<language_id><![CDATA['.$row["id_language"].']]></language_id>'."\n";
		if( isset($translations[$row['id_language']]['id']))
		{
			$xml .= '	<translation_id>'.$translations[$row['id_language']]['id'].'</translation_id>'."\n";
			$xml .= '	<translation_published>'.$translations[$row['id_language']]['published'].'</translation_published>'."\n";
		}
		else
		{
			$xml .= '	<translation_id>0</translation_id>'."\n";
		}
		$xml .= '</languages>'."\n";
		//print_r($translations); exit; 
		$languages[$row["id_language"]] = $row["code"];
	}
	
	foreach($labels as $key => $value)
	{
		$xml .= '	<label_list_label>'."\n";
		$xml .= '		<id_label>'.$key.'</id_label>'."\n";
		$xml .= '		<title>'.$value.'</title>'."\n";
		$xml .= '	</label_list_label>'."\n";
	}

	print $xml;
?>
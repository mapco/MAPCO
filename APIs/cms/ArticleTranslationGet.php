<?php
	
	//************************
	//*     SOA2-SERVICE     *
	//************************
	
	$required=array("article_id"	=> "numericNN",
					"id_language"	=> "numericNN");
	
	check_man_params($required);
	
	$article_id_trans=$_POST["article_id"];
	
	$res=q("SELECT id_article, language_id, article_id FROM cms_articles WHERE article_id=".$_POST["article_id"]." AND language_id=".$_POST["id_language"].";", $dbweb, __FILE__, __LINE__);
	if(mysqli_num_rows($res)>0)
	{
		$cms_articles=mysqli_fetch_assoc($res);
		$article_id_trans=$cms_articles["id_article"];//1.gesuchte Sprache
	}
	else
	{
		$res2=q("SELECT * FROM cms_languages WHERE id_language=".$_POST["id_language"].";", $dbweb, __FILE__, __LINE__);
		if(mysqli_num_rows($res2)>0)
		{
			$cms_languages=mysqli_fetch_assoc($res2);
			$language_id_default=$cms_languages["language_id"];
			$res3=q("SELECT id_article, language_id, article_id FROM cms_articles WHERE article_id=".$_POST["article_id"]." AND language_id=".$language_id_default.";", $dbweb, __FILE__, __LINE__);
			if(mysqli_num_rows($res3)>0)
			{
				$cms_articles_2=mysqli_fetch_assoc($res3);
				$article_id_trans=$cms_articles_2["id_article"];//2.Standardsprache
			}
			else
			{
				$res4=q("SELECT id_article, language_id, article_id FROM cms_articles WHERE article_id=".$_POST["article_id"]." AND language_id=2;", $dbweb, __FILE__, __LINE__);
				if(mysqli_num_rows($res4)>0)
				{
					$cms_articles_3=mysqli_fetch_assoc($res4);
					$article_id_trans=$cms_articles_3["id_article"];//3.Englisch
				}
			}
		}
		else
		{
			$res5=q("SELECT id_article, language_id, article_id FROM cms_articles WHERE article_id=".$_POST["article_id"]." AND language_id=2;", $dbweb, __FILE__, __LINE__);
			if(mysqli_num_rows($res5)>0)
			{
				$cms_articles_4=mysqli_fetch_assoc($res4);
				$article_id_trans=$cms_articles_4["id_article"];//3.Englisch
			}
		}
	}
	
	$xml='<article_id_trans><![CDATA['.$article_id_trans.']]></article_id_trans>';
	
	echo $xml;
	
?>
<?php

	//************************ 
	//*     SOA2-SERVICE     *
	//************************
	
	$xml='';
	$num_art=0;
	
	$res=q("SELECT * FROM cms_contacts WHERE idCmsUser=".$_SESSION["id_user"].";", $dbweb, __FILE__, __LINE__);
	if(mysqli_num_rows($res)>0)
	{
		$res=q("SELECT * FROM cms_articles_labels WHERE label_id=39 ORDER BY ordering;", $dbweb, __FILE__, __LINE__);
		while($cms_articles_labels=mysqli_fetch_assoc($res))
		{
			$article_read=0;
			$num_att=0;
			$res2=q("SELECT * FROM cms_articles WHERE id_article=".$cms_articles_labels["article_id"].";", $dbweb, __FILE__, __LINE__);
			$cms_articles=mysqli_fetch_assoc($res2);
			
			$xml.='<articles>'."\n";
			$xml.='	<article_id><![CDATA['.$cms_articles_labels["article_id"].']]></article_id>'."\n";
			$xml.='	<ordering><![CDATA['.$cms_articles_labels["ordering"].']]></ordering>'."\n";
			$xml.='	<firstmod><![CDATA['.$cms_articles["firstmod"].']]></firstmod>'."\n";
			$xml.='	<title><![CDATA['.$cms_articles["title"].']]></title>'."\n";
			$xml.='	<introduction><![CDATA['.$cms_articles["introduction"].']]></introduction>'."\n";
			if($cms_articles["format"]==0)
				$xml.='	<article><![CDATA['.nl2br($cms_articles["article"]).']]></article>'."\n";
			else
				$xml.='	<article><![CDATA['.$cms_articles["article"].']]></article>'."\n";
			$xml.='	<format><![CDATA['.$cms_articles["format"].']]></format>'."\n";
			//ATTACHMENTS
			$res3=q("SELECT * FROM cms_articles_files WHERE article_id=".$cms_articles_labels["article_id"].";", $dbweb, __FILE__, __LINE__);
			while($cms_articles_files=mysqli_fetch_array($res3))
			{
				$res4=q("SELECT * FROM cms_files WHERE id_file=".$cms_articles_files["file_id"].";", $dbweb, __FILE__, __LINE__);
				$cms_files=mysqli_fetch_assoc($res4);
				$xml.='	<file>'."\n";
				$xml.='		<id_file><![CDATA['.$cms_files["id_file"].']]></id_file>'."\n";
				$xml.='		<filename><![CDATA['.$cms_files["filename"].']]></filename>'."\n";
				$xml.='		<extension><![CDATA['.$cms_files["extension"].']]></extension>'."\n";
				$xml.='	</file>'."\n";
				$num_att++;
			}
			//READ BY
			$res5=q("SELECT * FROM cms_articles_read WHERE article_id=".$cms_articles_labels["article_id"]." ORDER BY firstmod;", $dbweb, __FILE__, __LINE__);
			while($cms_articles_read=mysqli_fetch_assoc($res5))
			{
				if($cms_articles_read["user_id"]==$_SESSION["id_user"]) $article_read=1;
				$res6=q("SELECT * FROM cms_contacts WHERE idCmsUser=".$cms_articles_read["user_id"].";", $dbweb, __FILE__, __LINE__);
				$cms_contacts=mysqli_fetch_assoc($res6);
				$xml.='	<user>'."\n";
				$xml.='		<firstname><![CDATA['.$cms_contacts["firstname"].']]></firstname>'."\n";
				$xml.='		<lastname><![CDATA['.$cms_contacts["lastname"].']]></lastname>'."\n";
				$xml.='		<phone><![CDATA['.$cms_contacts["phone"].']]></phone>'."\n";
				$xml.='		<mail><![CDATA['.$cms_contacts["mail"].']]></mail>'."\n";
				$xml.='	</user>'."\n";
			}
			$xml.='	<num_att><![CDATA['.$num_att.']]></num_att>'."\n";
			$xml.='	<article_read><![CDATA['.$article_read.']]></article_read>."\n"';
			$xml.='</articles>'."\n";
			$num_art++;
		}
	}
	$xml.='<num_art><![CDATA['.$num_art.']]></num_art>'."\n";
	
	echo $xml;

?>
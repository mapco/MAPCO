<?php

	//************************ 
	//*     SOA2-SERVICE     *
	//************************
	
	$required=array("id_user"	=> "numeric");
	
	check_man_params($required);
		
	$xml='';
	$num_art_unread=0;
	
	$res=q("SELECT * FROM cms_contacts WHERE idCmsUser=".$_POST["id_user"].";", $dbweb, __FILE__, __LINE__);
	if(mysqli_num_rows($res)>0)
	{
		$res=q("SELECT * FROM cms_articles_labels WHERE label_id=39 ORDER BY ordering;", $dbweb, __FILE__, __LINE__);
		while($cms_articles_labels=mysqli_fetch_assoc($res))
		{
			//READ BY
			$res5=q("SELECT * FROM cms_articles_read WHERE article_id=".$cms_articles_labels["article_id"]." AND user_id=".$_POST["id_user"].";", $dbweb, __FILE__, __LINE__);
			if(mysqli_num_rows($res5)==0)
			{
				$xml.='<article_id><![CDATA['.$cms_articles_labels["article_id"].']]></article_id>'."\n";
				$num_art_unread++;
			}
		}
	}
	$xml.='<num_art_unread><![CDATA['.$num_art_unread.']]></num_art_unread>'."\n";
	
	echo $xml;

?>
<?php

	//************************ 
	//*     SOA2-SERVICE     *
	//************************
	
	$required=array("article_id"	=> "numeric");
	
	check_man_params($required);
	
	$res=q("SELECT * FROM cms_articles_read WHERE article_id=".$_POST["article_id"]." and user_id=".$_SESSION["id_user"].";", $dbweb, __FILE__, __LINE__);
	if(mysqli_num_rows($res)==0)
	{
		$data=array();
		$data["article_id"]=$_POST["article_id"];
		$data["user_id"]=$_SESSION["id_user"];
		$data["firstmod"]=time();
		$data["firstmod_user"]=$_SESSION["id_user"];
		$data["lastmod"]=time();
		$data["lastmod_user"]=$_SESSION["id_user"];
		
		$res=q_insert("cms_articles_read", $data, $dbweb, __FILE__, __LINE__);
	}
	
	//SEND BACK ARTICLE READERS
	$xml='';
	$res2=q("SELECT * FROM cms_articles_read WHERE article_id=".$_POST["article_id"]." ORDER BY firstmod;", $dbweb, __FILE__, __LINE__);
	while($cms_articles_read=mysqli_fetch_assoc($res2))
	{
		$res3=q("SELECT * FROM cms_contacts WHERE idCmsUser=".$cms_articles_read["user_id"].";", $dbweb, __FILE__, __LINE__);
		$cms_contacts=mysqli_fetch_assoc($res3);
		$xml.='<user>'."\n";
		$xml.='	<firstname><![CDATA['.$cms_contacts["firstname"].']]></firstname>'."\n";
		$xml.='	<lastname><![CDATA['.$cms_contacts["lastname"].']]></lastname>'."\n";
		$xml.='	<phone><![CDATA['.$cms_contacts["phone"].']]></phone>'."\n";
		$xml.='	<mail><![CDATA['.$cms_contacts["mail"].']]></mail>'."\n";
		$xml.='</user>'."\n";
	}
	
	echo $xml;
	
?>
<?php
	
	check_man_params(array("user_id"	=> "numeric",
						   "receiver"	=> "text",
						   "sender"		=> "text",
						   "subject"	=> "text",
						   "message"	=> "text"));
						   
	if(isset($_POST["order_id"]) and $_POST["order_id"]!="")
		check_man_params(array("order_id"	=> "numeric"));
	
	//GET SITE-ID
	$results=q("SELECT * FROM cms_users_sites WHERE user_id=".$_POST["user_id"].";", $dbweb, __FILE__, __LINE__);
	$cms_users_sites=mysqli_fetch_array($results);
	
	//save note in cms_articles
	$post_data["API"]="cms";
	$post_data["APIRequest"]="ArticleAdd";
	$post_data["site_id"]=$cms_users_sites["site_id"];
	$post_data["title"]=$_POST["subject"];
	$post_data["article"]=nl2br($_POST["message"]);	
	$post_data["format"]=1;
	
	$postdata=http_build_query($post_data);
	
	$response=soa2($postdata, __FILE__, __LINE__);
	$article_id=(int)$response->article_id[0];
	
	//save conversation in crm_conversations
	$post_data=array();
	$post_data["API"]="crm";
	$post_data["APIRequest"]="ConversationAdd";
	$post_data["user_id"]=$_POST["user_id"];	
	$post_data["order_id"]=$_POST["order_id"];
	$post_data["article_id"]=$article_id;
	$post_data["type_id"]=4;
	$post_data["con_from"]=$_POST["sender"];
	$post_data["con_to"]=$_POST["receiver"];
	
	$postdata=http_build_query($post_data);
	
	$response=soa2($postdata, __FILE__, __LINE__);
	
	
	//save article label in cms_articles_labels
	$post_data=array();
	$post_data["API"]="cms";
	$post_data["APIRequest"]="ArticleLabelAdd";
	$post_data["article_id"]=$article_id;
	$post_data["label_id"]=21;
	
	$postdata=http_build_query($post_data);
	
	$response=soa2($postdata, __FILE__, __LINE__);

?>
<?php
	
	$cnt=1;
	echo 'Emails an folgende Adressen verschickt:'."\n\n";
	
	$res=q("SELECT * FROM cms_contacts WHERE mail!='' AND active=1 AND idCmsUser>1", $dbweb, __FILE__, __LINE__);
	while($cms_contacts=mysqli_fetch_assoc($res))
	{
		$post_data=array();
		$post_data["API"]="cms";
		$post_data["APIRequest"]="ArticlesUnreadGet";
		$post_data["id_user"]=$cms_contacts["idCmsUser"];
		
		$postdata=http_build_query($post_data);
		
		$response=soa2($postdata, __FILE__, __LINE__);
			
		if((int)$response->num_art_unread[0]>0)
		{
			$res2=q("SELECT * FROM cms_articles WHERE id_article=44092", $dbweb, __FILE__, __LINE__);
			if(mysqli_num_rows($res2)>0)
			{
				$cms_articles=mysqli_fetch_assoc($res2);
				
				$cms_articles["article"]=str_replace("<!-- NAME -->", $cms_contacts["firstname"].' '.$cms_contacts["lastname"], $cms_articles["article"]);
				
				$res3=q("SELECT * FROM cms_users WHERE id_user=".$cms_contacts["idCmsUser"], $dbweb, __FILE__, __LINE__);
				$cms_users=mysqli_fetch_assoc($res3);
				
				$post_data=array();
				$post_data["API"]="cms";
				$post_data["APIRequest"]="MailSend";
				$post_data["ToReceiver"]=$cms_contacts["mail"];
				$post_data["FromSender"]="noreply@mapco.de";
				$post_data["Subject"]=$cms_articles["introduction"];
				$post_data["MsgText"]=str_replace("<!-- INT_NEWS_PATH -->", PATH."autologin/".$cms_users["user_token"]."/interne-news/", $cms_articles["article"]);
				
				$postdata=http_build_query($post_data);
				
				$response=soa2($postdata, __FILE__, __LINE__);

/*				
				//KOPIE AN MWOSGIEN
				$post_data=array();
				$post_data["API"]="cms";
				$post_data["APIRequest"]="MailSend";
				$post_data["ToReceiver"]="mwosgien@mapco.de";
				$post_data["FromSender"]="noreply@mapco.de";
				$post_data["Subject"]=$cms_contacts["firstname"]." ".$cms_contacts["lastname"];
				$post_data["MsgText"]=str_replace("<!-- INT_NEWS_PATH -->", PATH."autologin/".$cms_users["user_token"]."/backend/", $cms_articles["article"]);
				
				$postdata=http_build_query($post_data);
				
				$response=soa2($postdata, __FILE__, __LINE__);
*/			
				echo $cnt.'. '.$cms_contacts["mail"]."\n";
				$cnt++;
			}
		}		

	}

?>
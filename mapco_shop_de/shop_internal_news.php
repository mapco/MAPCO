<?php

	include("config.php");
	include("functions/cms_show_article.php");
	
	
	echo '<META HTTP-EQUIV="content-type" CONTENT="text/html; charset=utf-8">';
	echo '	<script src="'.PATH.'modules/jQuery/jquery-1.10.0.min.js" type="text/javascript"></script>'."\n";
	//echo print_r($_SESSION);
	$in_path = 'http://www.mapco.de';
	if ( isset( $_SESSION["in_path"] ) )
	{
		$in_path=$_SESSION["in_path"];
		unset($_SESSION["in_path"]);
	}
	
?>

<script type="text/javascript">

	function article_read(article_id)
	{
		//alert(article_id);
		var post_data = 			new Object();
		post_data['API'] = 			'cms';
		post_data['APIRequest'] = 	'ArticleReadSet';
		post_data['article_id'] = 	article_id;
		
		$.post('<?php echo PATH;?>soa2/', post_data, function($data){
			try{$xml = $($.parseXML($data));} catch($err){show_status2($err.message); return;}
			if($xml.find('Ack').text() != 'Success') {show_status2($data); return;}
			
			var post_data = new Object();
			post_data['API'] = 'cms';
			post_data['APIRequest'] = 'VariableSet';
			post_data['key'] = 'in_path';
			post_data['value'] = '<?php echo $in_path;?>';
			
			$.post('<?php echo PATH;?>soa2/', post_data, function($data){
				try{$xml = $($.parseXML($data));} catch($err){show_status2($err.message); return;}
				if($xml.find('Ack').text() != 'Success') {show_status2($data); return;}
				//location.reload();
				location.href='<?php echo PATH;?>interne-news/';
			});
		});
	}
	
	function article_read_later(in_path)
	{
		location.href=in_path;
	}
	
</script>
	
<?php
	
	//GET UNREAD ARTICLE
	$post_data=array();
	$post_data["API"]="cms";
	$post_data["APIRequest"]="ArticlesUnreadGet";
	$post_data["id_user"]=$_SESSION["id_user"];
	$response=soa2($post_data, __FILE__, __LINE__);
	
	if((int)$response->num_art_unread[0]==0)
	{
		//header("Location: ".$_SESSION["in_path"]);
		echo '<script type="text/javascript">location.href = "'.$in_path.'";</script>';
		exit;
	}
	
	echo '<div style="margin-left: auto; margin-right: auto; width: 800px;">';
	if(isset($_SESSION["id_user"]) and (int)$response->num_art_unread[0]>0)
	{
		utf8_encode(show_article((int)$response->article_id[0], false));
	}
	echo '<br /><br /><input type="button" onclick="article_read('.(int)$response->article_id[0].')" value="Gelesen!"> <input type="button" onclick="article_read_later(\''.$in_path.'\')" value="Später lesen">';
	echo '</div>';
	echo '<script type="text/javascript">self.scrollTo(0,0);</script>';

?>
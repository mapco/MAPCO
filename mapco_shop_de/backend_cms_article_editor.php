<?php
	/******Author Sven E.******/
	/****Lastmod 25.03.2014****/

	include("config.php");
	include("templates/".TEMPLATE_BACKEND."/header.php");
	
	if (($_GET["id_article"]==0))
	{
		$query="INSERT INTO cms_articles (site_id, language_id, article_id, title, article, published, ordering, firstmod, firstmod_user, lastmod, lastmod_user) VALUES(".$_SESSION["id_site"].", 1, 0, '', '', 0, 1, ".time().", ".$_SESSION["id_user"].", ".time().", ".$_SESSION["id_user"].");";
		q($query, $dbweb, __FILE__, __LINE__);
		$_GET["id_article"]=mysqli_insert_id($dbweb);
		
		q("UPDATE cms_articles SET ordering=ordering+1 WHERE site_id=".$_SESSION["id_site"]." AND NOT id_article=".$_GET["id_article"].";", $dbweb, __FILE__, __LINE__); 
	}	
?>
<a href="#tab-general" class="ui-tabs-anchor" role="presentation" tabindex="-1" id="ui-id-1">Allgemein</a>
<?php
	//PATH
	if (isset($_GET["id_article"]))
	{
		$results=q("SELECT * FROM cms_articles_labels WHERE article_id=".$_GET["id_article"]." LIMIT 1;", $dbweb, __FILE__, __LINE__);
		if ( mysqli_num_rows($results)>0 )
		{
			$row=mysqli_fetch_array($results);
			$id_label=$row["label_id"];
			$results=q("SELECT * FROM cms_labels WHERE site_id IN(0, ".$_SESSION["id_site"].") AND id_label=".$id_label.";", $dbweb, __FILE__, __LINE__);
			$row=mysqli_fetch_array($results);
			$label=$row["label"];
		}
	}
	echo '<div>';
	echo '<a href="backend_index.php?lang='.$_GET["lang"].'">Backend</a>';
	echo ' > <a href="backend_cms_index.php?lang='.$_GET["lang"].'">Content Management</a>';
	echo ' > <a href="backend_cms_articles.php?lang='.$_GET["lang"].'">Beiträge</a>';
	echo '<span id="path_link_label">';
	if (isset($id_label))
	{
		echo ' > <a href="backend_cms_articles.php?lang='.$_GET["lang"].'&id_label='.$id_label.'">'.$label.'</a>';
	}
	echo '</span>';
	echo ' > Editor';
	echo '</div>';

	print '<div id="article_editor"></div>';
?> 
 	<script src="javascript/cms/ArticleEditor/Root.php" type="text/javascript" /></script>
	<script src="javascript/cms/DialogConfirm.php" type="text/javascript" /></script>
    <script src="javascript/cms/DialogNotify.php" type="text/javascript" /></script>
  
    
    <?php
	if ( $_SESSION['id_user'] == '87921' )
	{ ?>
    <?php }
?>
	<script src="javascript/cms/ArticleEditor/GenericView.php" type="text/javascript" /></script>    
    <script src="javascript/cms/ArticleEditor/NvpView.php" type="text/javascript" /></script>
    <script src="javascript/cms/ArticleEditor/FileView.php" type="text/javascript" /></script>
    <script src="javascript/cms/FileUpload.php" type="text/javascript" /></script>
    <script src="javascript/cms/ArticleEditor/ImageView.php" type="text/javascript" /></script>
    <script src="javascript/cms/ArticleEditor/VideoView.php" type="text/javascript" /></script>
    <script src="javascript/cms/ArticleEditor/AuctionsView.php" type="text/javascript" /></script>

		


<script type="text/javascript">
	start_editor(<?php print $_GET['id_article']; ?>, 1, 'article_editor');
</script>
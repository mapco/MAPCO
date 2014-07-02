<?php
	check_man_params( array("id_language" => "numericNN") );

//	if(!isset($_POST["id_language"])) $_POST["id_language"]=1;

	//get menus
	$menus=array();
	$results=q("SELECT * FROM cms_menus WHERE site_id IN (0, ".$_SESSION["id_site"].");", $dbweb, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		$menus[]=$row["id_menu"];
	}
	//get menuitems
	$menuitems=array();
	$results=q("SELECT * FROM cms_menuitems WHERE menu_id IN (".implode(", ", $menus).");", $dbweb, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		$menuitems[]=$row["id_menuitem"];
	}
	//analyze translations
	//coming soon

	//analyze SEO
	$found=0;
	$missing_seo=array();
	$results=q("SELECT * FROM cms_menuitems_languages WHERE menuitem_id IN (".implode(", ", $menuitems).") AND language_id=".$_POST["id_language"].";", $dbweb, __FILE__, __LINE__);
	$total=mysqli_num_rows($results);
	while( $row=mysqli_fetch_array($results) )
	{
		if( $row["meta_title"]=="" or $row["meta_description"]=="" )
		{
			$found++;
			$Reason="";
			if( $row["alias"]=="" )
			{
				if( $Reason!="" ) $Reason .= ', ';
				$Reason .= 'Alias fehlt';
			}
			if( $row["meta_title"]=="" )
			{
				if( $Reason!="" ) $Reason .= ', ';
				$Reason .= 'META-Titel fehlt';
			}
			if( $row["meta_description"]=="" )
			{
				if( $Reason!="" ) $Reason .= ', ';
				$Reason .= 'META-Beschreibung fehlt';
			}
			echo '<Menuitem>';
			echo '	<Reason>'.$Reason.'</Reason>';
			echo '	<Title><![CDATA['.$row["title"].']]></Title>';
			echo '	<menuitem_id>'.$row["menuitem_id"].'</menuitem_id>';
			echo '</Menuitem>';
		}
	}
	
	//progress
	echo '<ItemsTotal>'.$total.'</ItemsTotal>'."\n";
	echo '<ItemsOptimized>'.($total-$found).'</ItemsOptimized>'."\n";
	echo '<Progress>'.round(($total-$found)/$total*100, 2).'</Progress>'."\n";

?>
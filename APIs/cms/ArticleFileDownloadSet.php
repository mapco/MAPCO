<?php

	//************************ 
	//*     SOA2-SERVICE     *
	//************************
	
	$required = array( "article_id"	=> 	"numeric",
					   "file_id" => 	"numeric" );
					   
	check_man_params( $required );
	
	$res = q( "SELECT * FROM cms_articles_files_downloads WHERE article_id=" . $_POST[ "article_id" ] . " AND user_id=" . $_SESSION[ "id_user" ] . " AND file_id=" . $_POST[ 'file_id' ], $dbweb, __FILE__, __LINE__);
	if ( mysqli_num_rows( $res ) == 0 ) {
		
		$data = 				array();
		$data[ "article_id" ] = $_POST[ "article_id" ];
		$data[ "user_id" ] = 	$_SESSION[ "id_user" ];
		$data[ 'file_id' ] = 	$_POST[ 'file_id' ];
		$data[ "firstmod" ] = 	time();
		
		$res=q_insert("cms_articles_files_downloads", $data, $dbweb, __FILE__, __LINE__);
	}
	
	//ATTACHMENTS
	$num_att = 0;
	$att = array();
	$res3=q("SELECT * FROM cms_articles_files WHERE article_id=".$_POST["article_id"]." ORDER BY ordering", $dbweb, __FILE__, __LINE__);
	while($cms_articles_files=mysqli_fetch_array($res3))
	{
		$att[] = $cms_articles_files[ 'file_id' ];
		$res4=q("SELECT * FROM cms_files WHERE id_file=".$cms_articles_files["file_id"].";", $dbweb, __FILE__, __LINE__);
		$cms_files=mysqli_fetch_assoc($res4);
		$xml.='	<file>'."\n";
		$xml.='		<id_file><![CDATA['.$cms_files["id_file"].']]></id_file>'."\n";
		$xml.='		<filename><![CDATA['.$cms_files["filename"].']]></filename>'."\n";
		$xml.='		<extension><![CDATA['.$cms_files["extension"].']]></extension>'."\n";
		$xml.='	</file>'."\n";
		$num_att++;
	}
	
	//SEND BACK ARTICLE READERS
	$xml = '';
	$num_article_read = 0;
	$user_cnt = 0;
//	$res7 = q( "SELECT * FROM cms_contacts WHERE active=1 AND idCmsUser>1 ORDER BY lastname, firstname", $dbweb, __FILE__, __LINE__ );
	$res7 = q( "SELECT a.idCmsUser,a.firstname,a.lastname,c.location FROM cms_contacts AS a,cms_contacts_departments AS b,cms_contacts_locations AS c WHERE a.active=1 AND a.idCmsUser>1 AND a.department_id=b.id_department AND b.location_id=c.id_location ORDER BY c.ordering,a.lastname,a.firstname", $dbweb, __FILE__, __LINE__ );
	while ( $cms_contacts = mysqli_fetch_assoc( $res7 ) ) {
		
		$files_downloaded = array();
		foreach ( $att as $file_id ) {
			$res5 = q( "SELECT * FROM cms_articles_files_downloads WHERE user_id=" . $cms_contacts[ 'idCmsUser' ] . " AND file_id=" . $file_id . " AND article_id=" . $_POST[ 'article_id' ], $dbweb, __FILE__, __LINE__ );
			if ( mysqli_num_rows( $res5 ) > 0 ) {
				$files_downloaded[] = 1;
			} else {
				$files_downloaded[] = 0;
			}
		}
		
		$read = 0;
		$xml .= '<user>' . "\n";
		$xml .= '	<firstname><![CDATA[' . $cms_contacts[ 'firstname' ] . ']]></firstname>' . "\n";
		$xml .= '	<lastname><![CDATA[' . $cms_contacts[ 'lastname' ] . ']]></lastname>' . "\n";
		$xml .= '	<location><![CDATA[' . $cms_contacts['location'] . ']]></location>' . "\n";
		$res9 = q( "SELECT * FROM cms_articles_read WHERE article_id=" . $_POST[ "article_id" ] . " AND user_id=" . $cms_contacts[ 'idCmsUser' ], $dbweb, __FILE__, __LINE__ );
		if ( mysqli_num_rows( $res9 ) > 0 ) {
			$read = 1;
			$num_article_read++;
		}
		$xml .= '	<read><![CDATA[' . $read . ']]></read>' . "\n";
		$xml .= '	<downloads><![CDATA[' . implode( '_', $files_downloaded ) . ']]></downloads>' . "\n";
		$xml .= '</user>' . "\n";
		$user_cnt++;
	}
	$xml.='	<num_att><![CDATA['.$num_att.']]></num_att>'."\n";
	$xml .= '<num_article_read><![CDATA[' . $num_article_read . ']]></num_article_read>' . "\n";
	$xml .= '<num_user><![CDATA[' . $user_cnt . ']]></num_user>' . "\n";
	
	echo $xml;
	
?>
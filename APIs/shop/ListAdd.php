<?php
	if ( !isset($_POST["id_listtype"]) )
	{
		echo '<ListAddResponse>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Listentyp nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss ein Listentype (id_listtype) übergeben werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ListAddResponse>'."\n";
		exit;
	}

	if ( !isset($_POST["title"]) )
	{
		echo '<ListAddResponse>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Titel nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Die Liste muss einen Titel haben.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ListAddResponse>'."\n";
		exit;
	}

	if ( $_POST["title"]=="" )
	{
		echo '<ListAddResponse>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Titel leer.</shortMsg>'."\n";
		echo '		<longMsg>Der Titel der Liste darf nicht leer sein.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ListAddResponse>'."\n";
		exit;
	}

	q("INSERT INTO shop_lists (title, listtype_id, firstmod, firstmod_user, lastmod, lastmod_user) VALUES('".$_POST["title"]."', '".$_POST["id_listtype"]."', ".time().", ".$_SESSION["id_user"].", ".time().", ".$_SESSION["id_user"].");", $dbshop, __FILE__, __LINE__);
	$id_list=mysqli_insert_id($dbshop);
	//add column ArtNr
	q("INSERT INTO shop_lists_fields (list_id, field_id, value_id, title, ordering, firstmod, firstmod_user, lastmod, lastmod_user) VALUES(".$id_list.", 1, '', 'ArtNr', 1, ".time().", ".$_SESSION["id_user"].", ".time().", ".$_SESSION["id_user"].");", $dbshop, __FILE__, __LINE__);
	//add column Title
	q("INSERT INTO shop_lists_fields (list_id, field_id, value_id, title, ordering, firstmod, firstmod_user, lastmod, lastmod_user) VALUES(".$id_list.", 2, '', 'Titel', 2, ".time().", ".$_SESSION["id_user"].", ".time().", ".$_SESSION["id_user"].");", $dbshop, __FILE__, __LINE__);
	if($_POST["id_listtype"]==5)
	{
		//add column  Menge
		q("INSERT INTO shop_lists_fields (list_id, field_id, value_id, title, ordering, firstmod, firstmod_user, lastmod, lastmod_user) VALUES(".$id_list.", 28, '', 'Menge', 24, ".time().", ".$_SESSION["id_user"].", ".time().", ".$_SESSION["id_user"].");", $dbshop, __FILE__, __LINE__);
	}
	//return success
	echo '<ListAddResponse>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	echo '	<ListID>'.$id_list.'</ListID>'."\n";
	echo '</ListAddResponse>'."\n";

?>
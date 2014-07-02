<?php
	if ( !isset($_POST["id_list"]) )
	{
		echo '<ListEditResponse>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Listen-ID nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Die Listen-ID muss angegeben werden, damit der Service weiß, welche Liste bearbeitet werden soll.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ListEditResponse>'."\n";
		exit;
	}

	if ( !isset($_POST["title"]) )
	{
		echo '<ListEditResponse>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Titel nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Die Liste muss einen Titel haben.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ListEditResponse>'."\n";
		exit;
	}

	if ( $_POST["title"]=="" )
	{
		echo '<ListEditResponse>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Titel leer.</shortMsg>'."\n";
		echo '		<longMsg>Der Titel der Liste darf nicht leer sein.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ListEditResponse>'."\n";
		exit;
	}

	q("UPDATE shop_lists SET title='".mysqli_real_escape_string($dbshop, $_POST["title"])."', private='".$_POST["private"]."', lastmod=".time().", lastmod_user=".$_SESSION["id_user"]." WHERE id_list=".$_POST["id_list"].";", $dbshop, __FILE__, __LINE__);

	//return success
	echo '<ListEditResponse>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	echo '</ListEditResponse>'."\n";

?>
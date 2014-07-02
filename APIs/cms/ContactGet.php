<?php

	if ( !isset($_POST["id_contact"]) )
	{
		echo '<ContactGetResponse>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Kontakt fehlt.</shortMsg>'."\n";
		echo '		<longMsg>Es muss ein Kontakt (id_contact) übergeben werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ContactGetResponse>'."\n";
		exit;
	}
	$results=q("SELECT * FROm cms_contacts WHERE id_contact=".$_POST["id_contact"].";", $dbweb, __FILE__, __LINE__);
	if ( mysqli_num_rows($results)==0 )
	{
		echo '<ContactGetResponse>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Kontakt nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Der angegebenen Kontakt (id_contact) existiert nicht.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ContactGetResponse>'."\n";
		exit;
	}
	$row=mysqli_fetch_array($results);
	if( $row["article_id"]==0 )
	{
		//add article
		$data=array();
		$data["API"]="cms";
		$data["APIRequest"]="ArticleAdd";
		$data["title"]=$row["firstname"].' '.$row["lastname"];
		$data["introduction"]="";
		$data["article"]="";
		$data["published"]=1;
		$data["format"]=1;
		$data["imageprofile_id"]=7;
		$responseXml = post(PATH."soa2/", $data);		
		$use_errors = libxml_use_internal_errors(true);
		try
		{
			$response = new SimpleXMLElement($responseXml);
		}
		catch(Exception $e)
		{
			echo '<ContactGetResponse>'."\n";
			echo '	<Ack>Failure</Ack>'."\n";
			echo '	<Error>'."\n";
			echo '		<Code>'.__LINE__.'</Code>'."\n";
			echo '		<shortMsg>XML nicht valide.</shortMsg>'."\n";
			echo '		<longMsg>Beim Auswerten der XML-Daten trat ein Fehler auf.</longMsg>'."\n";
			echo '	</Error>'."\n";
			echo '	<Response><![CDATA['.$responseXml.']]></Response>'."\n";
			echo '</ContactGetResponse>'."\n";
			exit;
		}
		libxml_clear_errors();
		libxml_use_internal_errors($use_errors);
		$ack=(string)$response->Ack[0];
		if( $ack!="Success" )
		{
			echo '<ContactGetResponse>'."\n";
			echo '	<Ack>Failure</Ack>'."\n";
			echo '	<Error>'."\n";
			echo '		<Code>'.__LINE__.'</Code>'."\n";
			echo '		<shortMsg>Beitrag hinzufügen fehlgeschlagen.</shortMsg>'."\n";
			echo '		<longMsg>Beim Hinzufügen eines Beitrages trat ein Fehler auf.</longMsg>'."\n";
			echo '	</Error>'."\n";
			echo '	<Response><![CDATA['.$responseXml.']]></Response>'."\n";
			echo '</ContactGetResponse>'."\n";
			exit;
		}
		$article_id=(integer)$response->article_id[0];
		q("UPDATE cms_contacts SET article_id=".$article_id." WHERE id_contact=".$_POST["id_contact"].";", $dbweb, __FILE__, __LINE__);
		$row["article_id"]=$article_id;

		//add label to article
		$data=array();
		$data["API"]="cms";
		$data["APIRequest"]="ArticleLabelAdd";
		$data["article_id"]=$article_id;
		$data["label_id"]=36;
		$responseXml = post(PATH."soa2/", $data);		
		$use_errors = libxml_use_internal_errors(true);
		try
		{
			$response = new SimpleXMLElement($responseXml);
		}
		catch(Exception $e)
		{
			echo '<ContactGetResponse>'."\n";
			echo '	<Ack>Failure</Ack>'."\n";
			echo '	<Error>'."\n";
			echo '		<Code>'.__LINE__.'</Code>'."\n";
			echo '		<shortMsg>XML nicht valide.</shortMsg>'."\n";
			echo '		<longMsg>Beim Auswerten der XML-Daten trat ein Fehler auf.</longMsg>'."\n";
			echo '	</Error>'."\n";
			echo '	<Response><![CDATA['.$responseXml.']]></Response>'."\n";
			echo '</ContactGetResponse>'."\n";
			exit;
		}
		libxml_clear_errors();
		libxml_use_internal_errors($use_errors);
		$ack=(string)$response->Ack[0];
		if( $ack!="Success" )
		{
			echo '<ContactGetResponse>'."\n";
			echo '	<Ack>Failure</Ack>'."\n";
			echo '	<Error>'."\n";
			echo '		<Code>'.__LINE__.'</Code>'."\n";
			echo '		<shortMsg>Stichwort hinzufügen fehlgeschlagen.</shortMsg>'."\n";
			echo '		<longMsg>Beim Hinzufügen eines Stichwortes zu einem Beitrag trat ein Fehler auf.</longMsg>'."\n";
			echo '	</Error>'."\n";
			echo '	<Response><![CDATA['.$responseXml.']]></Response>'."\n";
			echo '</ContactGetResponse>'."\n";
			exit;
		}
	}
	$keys=array_keys($row);

	echo '<ContactGetResponse>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	for($i=0; $i<sizeof($keys); $i++)
	{
		if( !is_numeric($keys[$i]) )
			echo '	<'.$keys[$i].'>'.$row[$keys[$i]].'</'.$keys[$i].'>'."\n";
	}
	echo '</ContactGetResponse>'."\n";

?>
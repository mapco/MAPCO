<?php

	if ( !isset($_POST["customer_id"]) )
	{
		echo '<crm_get_customer_notesResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Customer ID nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine Kunden ID angegeben werden, die zur Liste hinzugefügt werden soll.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</crm_get_customer_notesResponse>'."\n";
		exit;
	}

	$xmldata="";
	$counter=0;
	$res_notes=q("SELECT * FROM crm_customer_notes WHERE customer_id = ".$_POST["customer_id"].";", $dbweb, __FILE__, __LINE__);
	$notes_count=mysqli_num_rows($res_notes);
	if ($notes_count>0)
	{

		//get_cms_contacts
		$cms_contacts=array();
		$res_contacts=q("SELECT * FROM cms_contacts;",  $dbweb, __FILE__, __LINE__);
		while ($row_contacts=mysqli_fetch_array($res_contacts))
		{
			$cms_contacts[$row_contacts["idCmsUser"]]=$row_contacts["firstname"]." ".$row_contacts["lastname"];
		}

		while ($row_notes=mysqli_fetch_array($res_notes))
		{
			$xmldata.= "<note>\n";
			$xmldata.= "<noteID>".$row_notes["id_note"]."</noteID>\n";
			$xmldata.= "<note_text><![CDATA[".$row_notes["note"]."]]></note_text>\n";
			$xmldata.= "<note_lastmod>".$row_notes["lastmod"]."</note_lastmod>\n";
			$xmldata.= "<note_lastmod_user>".$row_notes["lastmod_user"]."</note_lastmod_user>\n";
			$xmldata.= "<note_lastmod_user_name>".$cms_contacts[$row_notes["lastmod_user"]]."</note_lastmod_user_name>\n";
			$xmldata.= "<note_firstmod>".$row_notes["firstmod"]."</note_firstmod>\n";
			$xmldata.= "<note_firstmod_user>".$row_notes["firstmod_user"]."</note_firstmod_user>\n";
			$xmldata.= "<note_firstmod_user_name>".$cms_contacts[$row_notes["firstmod_user"]]."</note_firstmod_user_name>\n";
			$xmldata.= "</note>\n";
			$counter++;
		}
	}
	
	else 
	{
		echo '<crm_get_customer_notesResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Keine Notizen zum Customer gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es konnten keine Notizen zum Customer gefunden werden</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</crm_get_customer_notesResponse>'."\n";
		exit;
	}

	echo "<crm_add_customer_noteResponse>\n";
	echo "<Ack>Success</Ack>\n";
	echo "<count>".$counter."</count>\n";
	echo "<Notes>".$xmldata."</Notes>\n";
	echo "</crm_add_customer_noteResponse>";

?>


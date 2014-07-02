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

	echo '<table>';
	echo '	<tr><th colspan="5">Ansicht</th></tr>';
	echo '	<tr>';
	echo '		<th>Nr.</th>';
	echo '		<th>Feld</th>';
	echo '		<th>Wert</th>';
	echo '		<th>Titel</th>';
	echo '		<th>Optionen</th>';
	echo '	</tr>';
	$results=q("SELECT * FROM shop_lists_fields WHERE list_id=".$_POST["id_list"]." ORDER BY ordering;", $dbshop, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		echo '<tr>';
		//ordering
		echo '	<td>'.$row["ordering"].'</td>';
		//title
		$results2=q("SELECT * FROM shop_fields WHERE id_field=".$row["field_id"].";", $dbshop, __FILE__, __LINE__);
		$row2=mysqli_fetch_array($results2);
		echo '	<td>'.$row2["title"].'</td>';
		//value
		$results2=q("SELECT * FROM shop_fields_values WHERE id_value=".$row["value_id"].";", $dbshop, __FILE__, __LINE__);
		$row2=mysqli_fetch_array($results2);
		echo '	<td>'.$row2["title"].'</td>';
		//title
		echo '	<td>'.$row["title"].'</td>';
		//options
		echo '	<td><img src="images/icons/24x24/remove.png" style="cursor:pointer;" alt="Spalte löschen" title="Spalte löschen" onclick="list_field_remove('.$row["id"].');" /></td>';
		echo '</tr>';
	}
	echo '<tr>';
	echo '	<td></td>';
	echo '	<td>';
	echo '		<select id="list_edit_new_id_field" onchange="list_edit_view();">';
	echo '			<option value="0">Bitte wählen...</option>';
	$results=q("SELECT * FROM shop_fields;", $dbshop, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		if ( $row["id_field"]==$_POST["id_field"] ) $selected=' selected="selected"'; else $selected='';
		echo '<option'.$selected.' value="'.$row["id_field"].'">'.$row["title"].'</option>';
	}
	echo '		</select>';
	echo '	</td>';
	echo '	<td>';
	if ( isset($_POST["id_field"]) and is_numeric($_POST["id_field"]) )
	{
		$results=q("SELECT * FROM shop_fields_values WHERE field_id=".$_POST["id_field"].";", $dbshop, __FILE__, __LINE__);
		if ( mysqli_num_rows($results)>0 )
		{
			echo '		<select id="list_edit_new_id_value" onchange="list_edit_view();">';
			while( $row=mysqli_fetch_array($results) )
			{
			if ( $row["id_value"]==$_POST["id_value"] ) $selected=' selected="selected"'; else $selected='';
				echo '<option'.$selected.' value="'.$row["id_value"].'">'.$row["title"].'</option>';
			}
			echo '		</select>';
		}
	}
	echo '	</td>';
	echo '	<td><input id="list_edit_new_title" type="text" value="" /></td>';
	echo '	<td><img src="images/icons/24x24/add.png" style="cursor:pointer;" alt="Spalte hinzufügen" title="Spalte hinzufügen" onclick="list_field_add();" /></td>';
	echo '</tr>';
	echo '</table>';

?>
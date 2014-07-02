<?php

	echo '<h1>Felder und Werte</h1>';

	echo '<ul class="orderlist" style="width:522px;">';
	echo '	<li class="header">';
	echo '		<div style="width:50px;">Nr.</div>';
	echo '		<div style="width:50px;">ID</div>';
	echo '		<div style="width:150px;">Feld</div>';
	echo '		<div style="width:100px;">Bezeichner</div>';
	echo '		<div style="width:60px;">';
	echo '			<img src="'.PATH.'images/icons/24x24/add.png" alt="Neues Feld hinzufügen" title="Neues Feld hinzufügen" onclick="field_add();" />';
	echo '		</div>';
	echo '	</li>';
	$results=q("SELECT * FROM shop_fields ORDER BY ordering;", $dbshop, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		echo '<li>';
		echo '	<div style="width:50px;">'.$row["ordering"].'</div>';
		echo '	<div style="width:50px;">'.$row["id_field"].'</div>';
		if ( $row["id_field"]==$_POST["id_field"] ) $style=' style="font-weight:bold;"'; else $style="";
		echo '	<div style="width:150px;">';
		if ($row["title"]=="") $title="-"; else $title=$row["title"];
		echo '		<a'.$style.' href="javascript:field_select('.$row["id_field"].');">'.$title.'</a>';
		echo '</div>';
		if ($row["name"]=="") $name="-"; else $name=$row["name"];
		echo '	<div style="width:100px;">'.$name.'</div>';
		echo '	<div style="width:60px;">';
		echo '		<img src="images/icons/24x24/remove.png" alt="Feld löschen" title="Feld löschen" onclick="field_remove('.$row["id_field"].', \''.addslashes(stripslashes($row["title"])).'\');" />';
		echo '		<img src="images/icons/24x24/edit.png" alt="Feld bearbeiten" title="Feld bearbeiten" onclick="field_edit('.$row["id_field"].', \''.addslashes(stripslashes($row["title"])).'\', \''.addslashes(stripslashes($row["name"])).'\');" />';
		echo '	</div>';
		echo '</li>';
	}
	echo '</ul>';
	
	if ($_POST["id_field"]>0)
	{
		$results=q("SELECT * FROM shop_fields WHERE id_field=".$_POST["id_field"].";", $dbshop, __FILE__, __LINE__);
		$row=mysqli_fetch_array($results);
		echo '<ul class="orderlist" style="width:522px;">';
		echo '	<li class="header">';
		echo '		<div style="width:50px;">Nr.</div>';
		echo '		<div style="width:50px;">ID</div>';
		echo '		<div style="width:150px;">Wert</div>';
		echo '		<div style="width:100px;">Bezeichner</div>';
		echo '		<div style="width:60px;">';
		echo '			<img src="'.PATH.'images/icons/24x24/add.png" alt="Neuen Wert hinzufügen" title="Neuen Wert hinzufügen" onclick="value_add();" />';
		echo '		</div>';
		echo '	</li>';
		$results=q("SELECT * FROM shop_fields_values WHERE field_id=".$_POST["id_field"]." ORDER BY ordering;", $dbshop, __FILE__, __LINE__);
		while( $row=mysqli_fetch_array($results) )
		{
			echo '	<li>';
			echo '		<div style="width:50px;">'.$row["ordering"].'</div>';
			echo '		<div style="width:50px;">'.$row["id_value"].'</div>';
			if ($row["title"]=="") $title="-"; else $title=$row["title"];
			echo '		<div style="width:150px;">'.$title.'</div>';
			if ($row["value"]=="") $value="-"; else $value=$row["value"];
			echo '		<div style="width:100px;">'.$value.'</div>';
			echo '		<div style="width:60px;">';
		echo '		<img src="images/icons/24x24/remove.png" alt="Feld löschen" title="Wert löschen" onclick="value_remove('.$row["id_value"].', \''.addslashes(stripslashes($row["title"])).'\');" />';
			echo '			<img src="images/icons/24x24/edit.png" alt="Wert bearbeiten" title="Wert bearbeiten" onclick="value_edit('.$row["id_value"].', \''.addslashes(stripslashes($row["title"])).'\', \''.addslashes(stripslashes($row["value"])).'\');" />';
			echo '		</div>';
			echo '	</li>';
		}
	}

?>
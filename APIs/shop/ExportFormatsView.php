<?php

	echo '<h1>Exportformate</h1>';

	echo '<ul class="orderlist" style="width:322px;">';
	echo '	<li class="header">';
	echo '		<div style="width:200px;">Exportformate</div>';
	echo '		<div style="width:70px;">';
	echo '			<img src="'.PATH.'images/icons/24x24/add.png" alt="Exportformat importieren" title="Exportformat importieren" onclick="exportformat_add();" />';
	echo '		</div>';
	echo '	</li>';
	$results=q("SELECT * FROM shop_export_formats ORDER BY ordering;", $dbshop, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		echo '<li>';
		if ( $row["id_exportformat"]==$_POST["id_exportformat"] ) $style=' style="font-weight:bold;"'; else $style="";
		echo '	<div style="width:200px;"><a'.$style.' href="javascript:exportformat_select('.$row["id_exportformat"].');">'.$row["title"].'</a></div>';
		echo '	<div style="width:70px;">';
		echo '		<img src="images/icons/24x24/remove.png" alt="Exportformat löschen" title="Exportformat löschen" onclick="exportformat_remove('.$row["id_exportformat"].', \''.addslashes(stripslashes($row["title"])).'\');" />';
		echo '		<img src="images/icons/24x24/edit.png" alt="Exportformat bearbeiten" title="Exportformat bearbeiten" onclick="exportformat_edit('.$row["id_exportformat"].', \''.addslashes(stripslashes($row["title"])).'\');" />';
		echo '	</div>';
		echo '</li>';
	}
	echo '</ul>';
	
	if ($_POST["id_exportformat"]>0)
	{
		$results=q("SELECT * FROM shop_export_formats WHERE id_exportformat=".$_POST["id_exportformat"].";", $dbshop, __FILE__, __LINE__);
		$row=mysqli_fetch_array($results);
		echo '<ul class="orderlist" style="width:440px;">';
		echo '	<li class="header">';
		echo '		<div style="width:320px;">'.$row["title"].'</div>';
		echo '		<div style="width:60px;">';
		echo '			<img src="images/icons/24x24/add.png" alt="" title="" />';
		echo '		</div>';
		echo '	</li>';
		$results=q("SELECT * FROM shop_export_fields WHERE exportformat_id=".$_POST["id_exportformat"]." ORDER BY ordering;", $dbshop, __FILE__, __LINE__);
		while( $row=mysqli_fetch_array($results) )
		{
			echo '	<li>';
			echo '		<div style="width:150px;">'.$row["name"].'</div>';
			if ($row["value"]=="") $value="-"; else $value=$row["value"];
			echo '		<div style="width:150px;">'.$value.'</div>';
			echo '		<div style="width:60px;">';
			echo '			<img src="images/icons/24x24/remove.png" alt="Exportfeld löschen" title="Exportfeld löschen" />';
			echo '			<img src="images/icons/24x24/edit.png" alt="Exportfeld bearbeiten" title="Exportfeld bearbeiten" onclick="field_edit('.$row["id_field"].', \''.addslashes(stripslashes($row["name"])).'\', \''.addslashes(stripslashes($row["value"])).'\');" />';
			echo '		</div>';
			echo '	</li>';
		}
	}

?>
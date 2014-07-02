<?php 	include("config.php"); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Unbenanntes Dokument</title>

<script type="text/javascript">

	function show_herstellerlist()
	{
		//$("#herstellerlist").show();
		document.getElementById("herstellerlist").style.display="block";
	}
	
	function hide_herstellerlist()
	{
		//$("#herstellerlist").show();
		document.getElementById("herstellerlist").style.display="none";
	}

</script>

<style>
#herstellerlist
	{
		width:345px; 
		height:250px; 
		margin-left:5px; 
		overflow:auto; 

		border-right-color:#999;
		border-right-width:2px;
		border-right-style:solid;
		border-left-color:#999;
		border-left-width:2px; 
		border-left-style:solid;
		border-bottom-color:#999;
		border-bottom-style:solid;
		border-bottom-width:2px; 
/*
		border-color:#999;
		border-width:2px;
		
		border-bottom-style:solid;
		border-left-style:solid;
		border-right-style:solid;
*/		
		display:none
	}

#herstellerliste
	{
		/*
		border-right-color:#999;
		border-right-width:2px; 
		border-left-color:#999;
		border-left-width:2px; 
		border-bottom-color:#999;
		border-bottom-width:2px; 
		*/
	}
</style>


</head>

<body>
<div style="width:360px; height:300px; border:solid; float:left">
	<div style="background-image:url(images/testordner/select.png); width:350px; height:35px; margin-left:5px; margin-top:5px;">

		<span id="herstellerlist_ausw" style="margin: 3px 3px 80px 3px; width:280px; height:22px;" onclick="show_herstellerlist();">Bitte Modell auswählen</span>
	</div>
    <div id="herstellerlist" style="display:none">
	<ul id="herstelleliste" style="list-style-type:none;">
    		<li onclick="hide_herstellerlist();">liste</li>
	<?php
	
		$results=q("select distinct Bez1, KTypNr from vehicles_de", $dbweb, __FILE__, __LINE__);
	
		while ($row=mysql_fetch_array($result)) 
		{
			echo '<li>';
				echo '<span id="'.$row["KTypNr"].'">'.$row["Bez1"].'</span>';
			echo '</li>';
		}
	

	?>
    </ul>
	</div>	

</div>

</body>
</html>
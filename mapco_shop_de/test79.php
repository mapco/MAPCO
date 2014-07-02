<?php
	include("config.php");
	include("templates/".TEMPLATE_BACKEND."/header.php");

	if ( isset($_FILES["file"]) )
	{
?>
	<script type="text/javascript">
	var artnr=new Array();
	var vorgabe=new Array();
	var coss=new Array();

<?php
		//import cos and other data to shop_items
		$handle=fopen($_FILES["file"]["tmp_name"], "r");
		$line=fgetcsv($handle, 4096, ";");
		$i=0;
		while($line=fgetcsv($handle, 4096, ";"))
		{
			echo 'artnr['.$i.']=\''.$line[0]."';\n";
			echo 'vorgabe['.$i.']='.str_replace(",", ".", $line[2]).";\n";
			echo 'coss['.$i.']='.str_replace(",", ".", $line[3]).";\n";
			$i++;
		}
?>
	function submitCOS(i)
    {
		if ( i==artnr.length )
		{
			alert("Alle Werte erfolgreich importiert.");
			return;
		}
		$.post("soa/", { API:"shop", Action:"ItemUpdateCOS", ArtNr:artnr[i], VORGABE:vorgabe[i], COS:coss[i] },
			function(data)
			{
				if ( data.indexOf("Success")<0 ) show_status(data);
				else
				{
					$( "#progressbar" ).progressbar("option", "value", Math.round((i+1)/artnr.length*100));
					$("#progressText").html((i+1)+" von "+artnr.length);
					submitCOS(i+1);
				}
			}
		);
    }
	$(function() {
        $( "#progressbar" ).progressbar({
            value: 0
        });
    });
	submitCOS(0);
	</script>
<?php		
	}
	echo '<div style="position:relative;" id="progressbarWrapper" style="width:300px; height: 30px;" class="ui-widget-default">';
	echo '	<div id="progressbar"></div>';
	echo '	<div style="position:absolute; left:0px; top:5px; width:100%; height:25px; text-align:center;" id="progressText"></div>';
	echo '</div>';

	echo '<p>Aktualisiert die Werte für COS, etc. in der Tabelle shop_items.</p>';	
	echo '<form method="post" enctype="multipart/form-data">';
	echo '	<input type="file" name="file" />';
	echo '	<input type="submit" value="Hochladen" />';
	echo '</form>';


	include("templates/".TEMPLATE_BACKEND."/footer.php");
?>
<?php
	include("../../config.php");
	include("../../functions/cms_t.php");
	header('Content-type: text/javascript');
	
	//make dreamweaver highlight javascript
	if(true==false) { ?> 	<script type="text/javascript"> <?php }
?>
	var $file_upload_progressbar;
	var $file_upload_progressbar2;
	var $filename='';
	var $filename_temp='';
	var $filesize='';
	var $fileext='';
	var $tempfile="";
	var $file_upload_cancel=false;
	var $files="";
	var $filecontent="";
	
	$upload_end = function(){};

	function file_upload()
	{
    	if( $("#file_upload_dialog").length==0 )
        {
			$html  = '<div id="file_upload_dialog"></div>';
            $("body").append($html);
        }
	
		$html  = '';
		$html += '	<input type="file" id="file_upload_file" name="file" />';
		$html += '	<div id="file_upload_dialog_status" style="width:100%; position:relative;">';
		$html += '		<div id="file_upload_dialog_status2" style="width:100%; position:absolute; top:4px; text-align:center;"></div>';
		$html += '	</div>';
		$html += '	<div id="file_upload_dialog_status3" style="width:100%; position:relative;">';
		$html += '		<div id="file_upload_dialog_status4" style="width:100%; position:absolute; top:4px; text-align:center;"></div>';
		$html += '	</div>';
		$("#file_upload_dialog").html($html);
		
		document.getElementById('file_upload_file').addEventListener('click', file_upload_filehandler_open, false);
		document.getElementById('file_upload_file').addEventListener('change', file_upload_filehandler_close, false);
		if (window.File && window.FileReader && window.FileList && window.Blob)
		{
			$("#file_upload_dialog_status2").html("<?php echo t("Bitte wählen die Datei aus, die Sie hochladen möchten.")?>");
		}
		else
		{
			$("#file_upload_dialog_status2").html("<?php echo t("Die Datei-APIs werden in diesem Browser nicht unterstützt. Bitte benutzen Sie Chrome oder Firefox.")?>");
		}
		$("#file_upload_dialog").dialog
		({	buttons:
			[
				{ text: "<?php echo t("Schließen")?>", click: function() { $(this).dialog("close"); } }
			],
			closeText:"<?php echo t("Fenster schließen")?>",
			modal:true,
			resizable:false,
			title:"<?php echo t("Datei hochladen")?>",
			height:225,
			width:400
		});
	}
	
	function file_upload_cancel()
	{
		$file_upload_cancel=true;
		$('#file_upload_dialog').dialog('option', 'buttons', {});
	}
	
	function file_upload_cancel_completed()
	{
		$("#file_upload_dialog").html("<?php echo t("Hochladen der Datei erfolgreich abgebrochen.")?>");
		$('#file_upload_dialog').dialog('option', 'buttons', [ { text: "<?php echo t("Schließen")?>", click: function() { $(this).dialog("close");} } ] );
	}
	
	function file_upload_file($filenr, $pos)
	{
		if( $file_upload_cancel == true )
		{
			$filename='';
			$filename_temp='';
			$filesize='';
			$fileext='';
			$tempfile="";
			file_upload_cancel_completed();
			return;
		}
	
		if( $pos>=$files[$filenr].size )
		{
			$('#file_upload_dialog').dialog('option', 'buttons', 
			[
				{ text: "Schließen", click: function() { $(this).dialog("close"); } }
			] );
			$file_upload_progressbar2.progressbar("value", 100);
			$("#file_upload_dialog_status").hide();
			$("#file_upload_dialog_status4").html("<?php echo t("Datei erfolgreich hochgeladen.")?>");
			$tempfile='';
			
			window.$upload_end();
			
			return;
		}

		//create tempfile if necessary
		if( $tempfile=="" )
		{
			$fileext=$files[$filenr].name.substr($files[$filenr].name.lastIndexOf(".")*1+1);
			$.post("<?php echo PATH; ?>soa/", { API:"cms", Action:"TempFileAdd", extension: $files[$filenr].name.substr($files[$filenr].name.lastIndexOf(".")*1+1) }, function($data)
			{
				try
				{
					$xml = $($.parseXML($data));
				}
				catch (err)
				{
					show_status(err.message);
					return;
				}
				$ack = $xml.find("Ack");
				if ( $ack.text()!="Success" )
				{
					show_status($data);
					return;
				}
				
				$tempfile=$xml.find("Filename").text();
				$filename_temp=$tempfile;
				$filename=$files[$filenr].name;
				$filesize=$files[$filenr].size;
	
				$("#file_upload_dialog_status2").html("0% <?php echo t("hochgeladen")?>.");
				file_upload_file($filenr, 0);
			});
			return;
		}
		

		var $chunksize=32768;
		var $start=$pos;
		var $stop=$pos+$chunksize;
		if ($stop > $files[$filenr].size) $stop=$files[$filenr].size;
		var reader = new FileReader();
		reader.onloadend = function(evt)
		{
			if (evt.target.readyState == FileReader.DONE)
			{ // DONE == 2
				$Data=evt.target.result;
				$.post("<?php echo PATH; ?>soa/", { API:"cms", Action:"TempFileUpdate", Filename:$tempfile, Data:$Data }, function($data)
				{
					var $percent=Math.floor($stop / $files[$filenr].size * 100);
					$("#file_upload_dialog_status2").html('<?php echo t("Datei")?> '+($filenr+1)+' <?php echo t("von")?> '+$files.length+': '+ $percent+'% <?php echo t("hochgeladen")?>. ('+$stop+' / '+$files[$filenr].size+') <br /><br />'+$data);
					$file_upload_progressbar.progressbar("value", $percent);
					file_upload_file($filenr, $pos+$chunksize);
				});
			}
		};

		//start reading file
		var blob = $files[$filenr].slice($start, $stop) || file.mozSlice($start, $stop) || file.webkitSlice($start, $stop);
		reader.readAsDataURL(blob);

	}
	
	function file_upload_filehandler_close(evt)
	{
		wait_dialog_show();
		$files = evt.target.files; // FileList object
		$("#file_upload_dialog_status2").html($files.length+" <?php echo t("Datei(en) ausgewählt.")?>");
		$('#file_upload_dialog').dialog('option', 'buttons', 
		[
			{ text: "<?php echo t("Hochladen")?>", click: function() { file_upload_start(); } },
			{ text: "<?php echo t("Schließen")?>", click: function() { $(this).dialog("close"); } }
		] );
		wait_dialog_hide();
	}


  	function file_upload_filehandler_open()
	{
		$("#file_upload_dialog_status2").html("<?php echo t("Datei wird eingelesen.")?>");
	}
	
	function file_upload_start()
	{
		if( typeof $files == "undefined" || $files.length==0 )
		{
			alert("<?php echo t("Datei nicht gefunden.")?>");
			return;
		}

		$('#file_upload_dialog').dialog('option', 'buttons', 
		[
			{ text: "<?php echo t("Abbrechen")?>", click: function() { file_upload_cancel(); } }
		] );

		//reset dialog
		$("#file_upload_files").hide();
		$file_upload_progressbar=$("#file_upload_dialog_status").progressbar({ value: false });
		$file_upload_progressbar2=$("#file_upload_dialog_status3").progressbar({ value: 0 });
		$("#file_upload_dialog_status4").html("0 von "+$files.length+" <?php echo t("Dateien fertig.")?>");

		//start upload of first file
		$file_upload_cancel=false;
		file_upload_file(0, 0);
	}
	
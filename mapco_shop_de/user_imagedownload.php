<?php
	include("config.php");
	include("templates/".TEMPLATE."/header.php");
?>

	<script>
		var	$images=new Array();

		function download_images_with_frames()
		{
			if( $("#download_images_with_frames_dialog").length==0 ) $("body").append('<div id="download_images_with_frames_dialog" style="display:none;"></div>');
			$("#download_images_with_frames_dialog").dialog
			({	buttons:
				[
					{ text: "Schließen", click: function() { $(this).dialog("close"); } }
				],
				closeText:"Fenster schließen",
				height: 220,
				hide: { effect: 'drop', direction: "up" },
				modal:true,
				resizable:false,
				show: { effect: 'drop', direction: "up" },
				title:"Bilder mit Rahmen herunterladen",
				width:600
			});

			//create progressbar1
			var id="#download_images_with_frames_dialog";
			var id_progressbar1="download_images_with_frames_progressbar1";
			$(id).html('<div id="'+id_progressbar1+'_wrapper" style="position:relative;" style="100%"></div>');
			$("#"+id_progressbar1+"_wrapper").append('<div id="'+id_progressbar1+'" style="width:100%;"></div>');
			$("#"+id_progressbar1+"_wrapper").append('<div id="'+id_progressbar1+'_text" style="width:100%; position:absolute; left:0; top:5px; text-align:center; color:#000000; text-shadow: 1px 1px 0 #fff;"></div>');
			$(function() {
				$("#"+id_progressbar1).progressbar({
					value: false
				});
			});
			$( "#download_images_with_frames_progressbar1_text" ).text("Erstelle Zip-Datei...");

			$.post("<?php echo PATH; ?>soa/", { API:"cms", Action:"TempFileAdd", extension:"zip" }, function($data)
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
				var $zipfile=$xml.find("Filename").text();
	
				$( "#download_images_with_frames_progressbar1_text" ).text("Erstelle Bilderliste...");
				$.post("<?php echo PATH; ?>soa/", { API:"cms", Action:"ImagelistGet" }, function($data)
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
					var $i=0;
					var $total=$xml.find("Image").length;
					$xml.find("Image").each(function()
					{
						$images[$i]=new Array();
						$images[$i]["file"]=$(this).text();
						$images[$i]["filename"]=$(this).attr("filename");
						$i++;
					});
					

					download_images_with_frames2(0, $total, $zipfile);
				});
			});
		}

		function download_images_with_frames2($i, $total, $zipfile)
		{
			$( "#download_images_with_frames_progressbar1" ).progressbar({ value: Math.round($i/$total*100) });
			$( "#download_images_with_frames_progressbar1_text" ).text( Math.round($i/$total*100)+"%" );
			if( $i==$total )
			{
				$("#download_images_with_frames_dialog").append('<br style="clear:both;" /><a href="<?php echo PATH; ?>soa/'+$zipfile+'">Download Images</a>');
				return;
			}
			
			var $files=$images[$i]["file"];
			var $filenames=$images[$i]["filename"];
			$i++;
			var $j=0;
			var $max=Math.ceil($total/10);
			while( $j<$max && $i<$total )
			{
				$files+=", "+$images[$i]["file"];
				$filenames+=", "+$images[$i]["filename"];
				$i++;
				$j++;
			}
			$.post("<?php echo PATH; ?>soa/", { API:"cms", Action:"ZipFileAdd", zipfile:$zipfile, file:$files, filename:$filenames }, function($data)
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
				
				download_images_with_frames2($i, $total, $zipfile);
			});
		}
	</script>

<?php
	include("templates/".TEMPLATE."/cms_leftcolumn_shop.php");

	echo '<div id="mid_column">';

	//PATH
	echo '<p>';
	echo '<a href="'.PATHLANG.tl(301, "alias").'" title="'.tl(301, "description").'">'.tl(301, "title").'</a>';
	echo ' > '.tl(836, "title");
	echo '</p>';

	echo '	<p>Hier können Sie Produktbilder zu MAPCO-Produkten selbstständig herunterladen.</p>';
	echo '	<a href="javascript:download_images_with_frames();">Bilderdownload</a>';
	echo '</div>';
	
	include("templates/".TEMPLATE."/cms_rightcolumn.php");
	include("templates/".TEMPLATE."/footer.php");
?>
<?php
	/***** Author Sven E. *****/
	/*** Lastmod 26.03.2014 ***/
	
	include("../../../config.php");
	header('Content-type: text/javascript');
	
	//make dreamweaver highlight javascript
	if(true==false) { ?> 	<script type="text/javascript"> <?php }
?>

function load_view_files()
{	
	wait_dialog_show('Ermittle zugehörige Dateianhänge', 0);
	var article_id = $("#editor_tabs").attr('article');
	var language_id = $("#editor_tabs").attr('language_id');
	
	$.post("<?php print PATH; ?>soa2/", { API:"cms", APIRequest:"ArticleFilesGet", article_id:article_id, language_id:language_id }, function($data)
	{ 
		//show_status2($data); return;
		try{$xml = $($.parseXML($data))} catch($err){show_status2($err.message);return;}
		if($xml.find('Ack').text()!='Success'){show_status2($data);return;}

		wait_dialog_show('Erstelle Liste der Dateianhänge', 50);
		var article_files_content = '<table id="filelist" class="orderlist ui-sortable" margin-bottom:0;">';
		article_files_content += '	<tr class="header">';
		article_files_content += '		<th colspan="3">Anhänge<img id="button_upload_file" style="cursor:pointer; float:right;" src="images/icons/24x24/add.png" alt="Neuen Anhang hinzufügen" title="Neuen Anhang hinzufügen" /></th>';
		article_files_content += '	</tr>';
		article_files_content += '	<tr class="header">';
		article_files_content += '		<th style="width:50px;">Nr.</th>';
		article_files_content += '		<th style="width:500px;">Anhang</th>';
		article_files_content += '		<th style="width:100px;">Optionen</th>';
		article_files_content += '	</tr>';
		if($xml.find('Error').length==0)
		{
			var file_id = 0;
			$xml.find('file').each(function(){
				file_id =$(this).find("file_id").text();
				article_files_content += '	<tr id="art_file_'+$(this).find('article_file_id').text()+'">';
				article_files_content += '		<td style="width:50px;">'+$(this).find("ordering").text()+'</td>';
				article_files_content += '		<td style="width:500px;">'+$(this).find("filename").text()+'.'+$(this).find("extension").text()+'<br /><i>'+$(this).find("description").text()+'</i></td>';
				article_files_content += '		<td style="width:100px;">';
				article_files_content += '			<img class="button_remove_file" style="cursor:pointer;" src="images/icons/24x24/remove.png" alt="Anhang löschen" title="Anhang löschen" file_id="'+$(this).find("file_id").text()+'" />';
				article_files_content += '			<img class="button_edit_file" style="cursor:pointer;" src="images/icons/24x24/edit.png" alt="Anhang bearbeiten" title="Anhang bearbeiten" file_id="'+file_id+'" filename="'+$(this).find("filename").text()+'" extension="'+$(this).find("extension").text()+'" description="'+$(this).find("description").text()+'" />';
				article_files_content += '		</td>';
				article_files_content += '	</tr>';
			});
		}
		else
		{
			article_files_content += '	<tr>';
			article_files_content += '		<td colspan=3>'+$xml.find('Error').text()+'</td>';
			article_files_content += '	</tr>';
		}
		article_files_content += '</table>';
		
		wait_dialog_show('Erzeuge Oberfläche', 100);
		$("#article_editor_content").empty().append(article_files_content);
		
		$("#button_upload_file").click(function(){		
			window.$upload_end = function()
			{
				$.post("<?php print PATH; ?>soa2/", { API:"cms", APIRequest:"ArticleFileAdd", article_id:article_id, filename:$filename, filesize:$filesize, filename_temp:$filename_temp  }, function($data){ });
				load_subview('file');
			}
				
			file_upload();
		});
		
		$(".button_edit_file").click(function(){ 
			dialog_edit_file($(this).attr("file_id"), $(this).attr("filename"), $(this).attr("extension"), $(this).attr("description"), 'file');
		});
		
		$(".button_remove_file").click(function(){
			remove_file($(this).attr("file_id"),'file');
		});
		
		$(function() {
			$( "#filelist" ).sortable( { items:"tr:not(.header)" } );
			$( "#filelist" ).sortable( { cancel:".header"} );
			$( "#filelist" ).disableSelection();
			$( "#filelist" ).bind( "sortupdate", function(event, ui)
			{
				wait_dialog_show('Sortiere Einträge', 0);
				var list = $('#filelist').sortable('toArray');
				$.post("<?php echo PATH; ?>soa2/", { API:"cms", APIRequest:"ArticleFileSort", list:list, type:'file' }, function($data){ 
					//show_status2($data); return;
					try{$xml = $($.parseXML($data))} catch($err){show_status2($err.message);return;}
					if($xml.find('Ack').text()!='Success'){show_status2($data);return;}
					
					wait_dialog_show('Aktualisiere Ansicht', 100);
					load_subview('file');
				});
				wait_dialog_hide();
			});
		});
		
		wait_dialog_hide();
	});
}
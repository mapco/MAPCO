<?php
	/***** Author Sven E. *****/
	/*** Lastmod 26.03.2014 ***/
	
	include("../../../config.php");
	header('Content-type: text/javascript');
	
	//make dreamweaver highlight javascript
	if(true==false) { ?> 	<script type="text/javascript"> <?php }
?>
function load_view_videos()
{	
	wait_dialog_show('Ermittle zugehörige Videos', 0);
	var article_id = $("#editor_tabs").attr('article');
	var language_id = $("#editor_tabs").attr('language_id');
	
	$.post("<?php echo PATH; ?>soa2/", { API:"cms", APIRequest:"ArticleVideosGet", article_id:article_id, lang:language_id }, function($data){ 
		//show_status2($data); return;
		try{$xml = $($.parseXML($data))} catch($err){show_status2($err.message);return;}
		if($xml.find('Ack').text()!='Success'){show_status2($data);return;}
		
		wait_dialog_show('Erstelle Liste der Videos', 50);
		var article_videos_content = '<ul id="videolist" class="orderlist ui-sortable" style="width:542px;">';
		article_videos_content += '	<li style="width:540px;" class="header">';
		article_videos_content += '		<div style="width:75px;">Nr</div>';
		article_videos_content += '		<div style="width:300px;">Video</div>';
		article_videos_content += '		<div style="width:100px;"><img id="button_upload_video" style="cursor:pointer; float:right;" src="images/icons/24x24/add.png" alt="Neues Video hinzufügen" title="Neues Video hinzufügen" /></div>';

		article_videos_content += '	</li>';

		if ( $xml.find('article_video').length>0 )
		{
			$xml.find('article_video').each(function(){
				article_videos_content += '	<li style="clear:both; width:540px;" id="art_vid_'+$(this).find('id').text()+'">';
				article_videos_content += '		<div style="width:75px;">'+$(this).find("ordering").text()+'</div>';
				article_videos_content += '		<div style="width:300px;">'+$(this).find("filename").text()+'.'+$(this).find("extension").text()+'<br /><i>'+$(this).find("description").text()+'</i></div>';
				article_videos_content += '		<div style="width:100px;">';
				article_videos_content += '			<img class="button_remove_video" style="cursor:pointer;" src="images/icons/24x24/remove.png" alt="Abbildung löschen" title="Abbildung löschen" file_id="'+$(this).find('file_id').text()+'" />';
				article_videos_content += '			<img class="button_edit_video" style="cursor:pointer;" src="images/icons/24x24/edit.png" alt="Abbildung bearbeiten" title="Abbildung bearbeiten" video_id="'+$(this).find('file_id').text()+'" video_ext="'+$(this).find('extension').text()+'" video_name="'+$(this).find('filename').text()+'" video_desc="'+$(this).find('description').text()+'" />';
				article_videos_content += '		</div>';
				article_videos_content += '	</li>';
			});
		}
		else
		{
			article_videos_content += '	<li style=width:540px;">';
			article_videos_content += '		<div>Keine Videos mit diesem Artikel verbunden!</div>';
			article_videos_content += '	</li>';
		}
		article_videos_content += '</ul>';
		
		wait_dialog_show('Erzeuge Oberfläche', 100);
		$("#article_editor_content").empty().append(article_videos_content);
		
		$("#button_upload_video").click(function(){		
			window.$upload_end = function()
			{
				$.post("<?php print PATH; ?>soa2/", { API:"cms", APIRequest:"ArticleVideoAdd", article_id:article_id, filename:$filename, filesize:$filesize, filename_temp:$filename_temp  }, function($data){ });
				load_subview('video');
			}
				
			file_upload();
		});
		
		$(".button_edit_video").click(function(){ 
			dialog_edit_file($(this).attr("file_id"), $(this).attr("filename"), $(this).attr("extension"), $(this).attr("description"), 'video');
		});
		
		$(".button_remove_video").click(function(){
			remove_file($(this).attr("file_id"),'video');
		});
		
		$(function() {
			$( "#videolist" ).sortable( { items:"li:not(.header)" } );
			$( "#videolist" ).sortable( { cancel:".header"} );
			$( "#videolist" ).disableSelection();
			$( "#videolist" ).bind( "sortupdate", function(event, ui)
			{
				var list = $('#videolist').sortable('toArray');
				$.post("<?php echo PATH; ?>soa2/", { API:"cms", APIRequest:"TableDataSort", list:list, table:'cms_articles_videos', label:'art_vid_', column:'id', db:'dbweb' }, function($data){ 
					//show_status2($data); return;
					try{$xml = $($.parseXML($data))} catch($err){show_status2($err.message);return;}
					if($xml.find('Ack').text()!='Success'){show_status2($data);return;}
					
					load_subview('video');
				});
			});
		});
		
		wait_dialog_hide();
		});
}
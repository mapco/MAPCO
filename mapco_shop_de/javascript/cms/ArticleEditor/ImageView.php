<?php
	/***** Author Sven E. *****/
	/*** Lastmod 26.03.2014 ***/
	
	include("../../../config.php");
	header('Content-type: text/javascript');
	
	//make dreamweaver highlight javascript
	if(true==false) { ?> 	<script type="text/javascript"> <?php }
?>
	function imageprofile_check($id_imageprofile)
	{
		$postdata=new Object();
		$postdata["API"]="cms";
		$postdata["APIRequest"]="ArticlesImageformatCheck";
		$postdata["id_imageprofile"]=$id_imageprofile;
		wait_dialog_show("Lese Artikel mit ausgewähltem Profil aus");
		$.post("<?php echo PATH; ?>soa2/", $postdata, function($data)
		{
			wait_dialog_hide();
			try{$xml = $($.parseXML($data))} catch($err){show_status2($err.message);return;}
			if($xml.find('Ack').text()!='Success'){show_status2($data);return;}
			
			var $length=$xml.find("result").length;
			var $i=0;
			var $data=new Array();
			$xml.find("ImageformatMissing").each(function()
			{
				$data[$i]=new Array();
				$data[$i]["id_article"]=$(this).find("id_article").text();
				$data[$i]["id_file"]=$(this).find("id_file").text();
				$data[$i]["id_imageformat"]=$(this).find("id_imageformat").text();
				$i++;
			});
			article_imageformat_check2($data, 0);
		});
	}

	function article_imageformat_check()
	{
		$postdata=new Object();
		$postdata["API"]="cms";
		$postdata["APIRequest"]="ArticleImageformatCheck";
		$postdata["id_article"]=$("#editor_tabs").attr('article');
		wait_dialog_show("Prüfe auf fehlende Bildformate");
		$.post("<?php echo PATH; ?>soa2/", $postdata, function($data)
		{
			try { $xml = $($.parseXML($data)); } catch ($err) { wait_dialog_hide(); show_status2("XML-Error: "+$err.message); return; }
			if ( $xml.find("Ack").text()!="Success" ) { wait_dialog_hide(); show_status2("Failure: "+$data); return; }
			
			var $length=$xml.find("result").length;
			var $i=0;
			var $data=new Array();
			$xml.find("ImageformatMissing").each(function()
			{
				$data[$i]=new Array();
				$data[$i]["id_article"]=$(this).find("id_article").text();
				$data[$i]["id_file"]=$(this).find("id_file").text();
				$data[$i]["id_imageformat"]=$(this).find("id_imageformat").text();
				$i++;
			});
			article_imageformat_check2($data, 0);
		});
	}

	function article_imageformat_check2($data, $i)
	{
		if( $i==$data.length )
		{
			if(typeof load_subview == 'function') load_subview('image');
			wait_dialog_hide();
			return;
		}
		
		var $postdata=new Object();
		$postdata["API"]="cms";
		$postdata["Action"]="ArticleImageImageformatAdd";
		$postdata["id_article"]=$data[$i]["id_article"];
		$postdata["id_file"]=$data[$i]["id_file"];
		$postdata["id_imageformat"]=$data[$i]["id_imageformat"];
		var $percent=Math.round($i/$data.length*100);
		wait_dialog_show("Artikel "+$data[$i]["id_article"]+", Bild "+$postdata["id_file"]+", Bildformat "+$postdata["id_imageformat"]+" ("+($i+1)+" / "+$data.length+")", $percent);
		$.post("<?php echo PATH; ?>soa/", $postdata, function($data2)
		{
			try { $xml = $($.parseXML($data2)); } catch ($err) { wait_dialog_hide(); show_status2("XML-Error: "+$err.message); return; }
			if ( $xml.find("Ack").text()!="Success" ) { wait_dialog_hide(); show_status2("Failure: "+$data2); return; }

			article_imageformat_check2($data, $i+1);
		});
	}


function load_view_images()
{	
	wait_dialog_show('Ermittle zugehörige Bilder', 0);
	var article_id = $("#editor_tabs").attr('article');
	var language_id = $("#editor_tabs").attr('language_id');
		
	$.post("<?php echo PATH; ?>soa2/", { API:"cms", APIRequest:"ArticleImagesGet", article_id:article_id, lang:language_id }, function($data){ 
		//show_status2($data); return;
		try{$xml = $($.parseXML($data))} catch($err){show_status2($err.message);return;}
		if($xml.find('Ack').text()!='Success'){show_status2($data);return;}
		
		var article_images = '<ul id="imagelist" class="orderlist ui-sortable" style="width:100%;">';
			
		article_images += '	<li class="header">';
		article_images += '		<div style="width:50px;">Nr.</div>';
		article_images += '		<div style="width:130px; text-align:left;">Original</div>';
		$xml.find('image_format').each(function(){
			article_images += '<div style="width:130px; text-align:left;">'+$(this).text()+'</div>';
		});
		article_images += '		<div style="width:120px; text-align:right;">';
		article_images += '			<img alt="Neues Bild hochladen" id="button_upload_image" src="<?php echo PATH; ?>images/icons/24x24/add.png" title="Neues Bild hochladen">';
		article_images += '			<img alt="Bildformate korrigieren" id="button_upload_image" onclick="article_imageformat_check();" src="<?php echo PATH; ?>images/icons/24x24/search.png" title="Bildformate korrigieren">';
		article_images += '		</div>';
		article_images += '	</li>';
		
		wait_dialog_show('Liste Bilder auf', 50);
		
		if($xml.find('image').length > 0)
		{
			$xml.find('image').each(function(){
				article_images += '	<li style="clear:both;" id="art_img_'+$(this).find('id').text()+'">';
				//ordering
				article_images += '		<div style="width:50px;">'+$(this).find('ordering').text()+'</div>';
				//original
				article_images += '		<div style="width:130px;">';
				article_images += '	<a target="_blank" href="'+$(this).find('original_name').text()+'" title="Original anzeigen">';
				article_images += '		<img src="'+$(this).find('original_path').text()+'" alt="Original anzeigen" title="Original anzeigen" />';
				article_images += '</a>';
				article_images += '		</div>';		
			
				var image_path = '';
				var image_name = '';
				var image_link = '';
			
				$(this).find('subimage').each(function(){
					image_path = $(this).find('image_path').text();
					image_link = image_path.replace('_thumbnail','');
					image_name = $(this).find('image_name').text();
					
					article_images += '		<div style="width:130px;">';
					article_images += '			<a target="_blank" href="'+image_link+'" title="'+image_name+' anzeigen">';
					article_images += '				<img src="'+image_path+'" title="'+image_name+' anzeigen" alt="'+image_name+' anzeigen" />';
					article_images += '			</a>';
					article_images += '		</div>';
				});
					article_images += '		<div style="width:120px;">';
					article_images += '			<img class="button_remove_image" style="cursor:pointer;" src="images/icons/24x24/remove.png" alt="Abbildung löschen" title="Abbildung löschen" file_id="'+$(this).find('file_id').text()+'" />';
					article_images += '			<img class="button_edit_image" style="cursor:pointer;" src="images/icons/24x24/edit.png" alt="Abbildung bearbeiten" title="Abbildung bearbeiten" image_id="'+$(this).find('file_id').text()+'" image_ext="'+$(this).find('extension').text()+'" image_name="'+$(this).find('file_name').text()+'" image_desc="'+$(this).find('image_description_text').text()+'" />';
					article_images += '		</div>';
					
					//Bildunterschrift
					var description = $(this).find('image_description').text();
					var style=' style="margin:0px 0px 0px 75px; font-style:italic;clear:both;"';
					if (description =="")
					{
						description = 'ACHTUNG: Keine Bildunterschrift vorhanden. Schlecht für Suchmaschinenoptimierung!';
						style=' style="margin:0px 0px 0px 75px; color:#ff0000; font-style:italic;clear:both;"';
					}
					article_images += '		<div'+style+'>'+description+'</div>';
					article_images += '	</li>';
			});
		}
		else
		{
			article_images += '	<li style="width:960px;">';
			article_images += '		<div>Keine Bilder mit diesem Artikel verbunden!</div>';
			article_images += '	</li>';
		}
		article_images += '	</ul>';
		
		wait_dialog_show('Liste Bilder auf', 100);
		$("#article_editor_content").empty().append(article_images);
		
		$("#button_upload_image").click(function(){		
			window.$upload_end = function()
			{
				$.post("<?php echo PATH; ?>soa2/", { API:"cms", APIRequest:"ArticleImageAddSoa2", article_id:article_id, filename:$filename, source:$filename_temp, filesize:$filesize }, function($data){ 
					//show_status2($data); return;
					try{$xml = $($.parseXML($data))} catch($err){show_status2($err.message);return;}
					if($xml.find('Ack').text()!='Success'){show_status2($data);return;}
					load_subview('image');
				});				
			}
				
			file_upload();
		});
		
		$(".button_remove_image").click(function(){
			remove_file($(this).attr("file_id"), 'image');
		});
		
		$(".button_edit_image").click(function(){
			var image_id = $(this).attr('image_id');
			var imagename = $(this).attr('image_name');
			var extension = $(this).attr('image_ext');
			var description = $(this).attr('image_desc');
			dialog_edit_file(image_id, imagename, extension, description, 'image');
		});
		
		$(function() {
			$( "#imagelist" ).sortable( { items:"li:not(.header)" } );
			$( "#imagelist" ).sortable( { cancel:".header"} );
			$( "#imagelist" ).disableSelection();
			$( "#imagelist" ).bind( "sortupdate", function(event, ui)
			{
				wait_dialog_show('Sortiere Einträge', 0);
				var list = $('#imagelist').sortable('toArray');
				$.post("<?php echo PATH; ?>soa2/", { API:"cms", APIRequest:"TableDataSort", list:list, table:'cms_articles_images', label:'art_img_', column:'id', db:'dbweb' }, function($data){ 
					//show_status2($data); return;
					try{$xml = $($.parseXML($data))} catch($err){show_status2($err.message);return;}
					if($xml.find('Ack').text()!='Success'){show_status2($data);return;}
					wait_dialog_show('Aktualisiere Ansicht', 100);
					load_subview('image');
					
					wait_dialog_hide();
				});
			});
		});
		
		wait_dialog_hide();
	});
}
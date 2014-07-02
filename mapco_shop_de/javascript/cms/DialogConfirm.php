<?php
	include("../../config.php");
	include("../../functions/cms_t.php");
	header('Content-type: text/javascript');
	
	//make dreamweaver highlight javascript
	if(true==false) { ?> 	<script type="text/javascript"> <?php }
?>

	// Sicherheitsnachfragedialog
	function dialog_confirm(message, $callback)
	{
		if ($("#dialog_confirm").length == 0)
		{
			var dialog_div = $('<div id="dialog_confirm"></div>');
			$("#content").append(dialog_div);
		}
		$("#dialog_confirm").empty();
		
		var dialog_content = message;
		
		$("#dialog_confirm").append(dialog_content);
		
		$("#dialog_confirm").dialog({	
			buttons:
			[
				{ text: "<?php echo t("OK"); ?>", click: function() { $callback(); $(this).dialog("close");	} },
				{ text: "<?php echo t("Abbrechen"); ?>", click: function() { $(this).dialog("close"); } }
			],
			closeText:"<?php echo t("Fenster schließen"); ?>",
			hide: { effect: 'drop', direction: "up" },
			modal:true,
			resizable:false,
			show: { effect: 'drop', direction: "up" },
			title:"<?php echo t("Bestätigung"); ?>",
			width:300
		});
	}
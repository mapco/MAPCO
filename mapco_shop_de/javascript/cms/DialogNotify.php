<?php
	include("../../config.php");
	include("../../functions/cms_t.php");
	header('Content-type: text/javascript');
	
	//make dreamweaver highlight javascript
	if(true==false) { ?> 	<script type="text/javascript"> <?php }
?>

	// Einfacher Hinweisdialog
	function dialog_notify(message)
	{
		if ($("#dialog_notify").length == 0)
		{
			var dialog_div = $('<div id="dialog_notify"></div>');
			$("#content").append(dialog_div);
		}
		$("#dialog_notify").empty();
		
		var dialog_content = message;
		
		$("#dialog_notify").append(dialog_content);
		
		$("#dialog_notify").dialog({	
			buttons:
			[
				{ text: "<?php echo t("OK"); ?>", click: function() {$(this).dialog("close");} }
			],
			closeText:"<?php echo t("Fenster schließen"); ?>",
			hide: { effect: 'drop', direction: "up" },
			modal:true,
			resizable:false,
			show: { effect: 'drop', direction: "up" },
			title:"<?php echo t("Meldung"); ?>",
			width:300
		});
	}
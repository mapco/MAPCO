<?php

	include("config.php");
	include("templates/".TEMPLATE_BACKEND."/header.php");

?>

<script type="text/javascript">

	function backend_mailaccounts_main()
	{
		var post_object = new Object();
		post_object['API'] = 'cms';
		post_object['APIRequest'] = 'TableDataSelect';
		post_object['table'] = 'cms_mail_accounts';
		post_object['db'] = 'dbweb';
		
		wait_dialog_show();
		$.post('<?php echo PATH;?>soa2/', post_object, function($data){  
			show_status2($data); //return;
			try{$xml = $($.parseXML($data));} catch($err){show_status2($err.message); wait_dialog_hide(); return;}
			if($xml.find("Ack").text()!='Success'){show_status2('Die Mailbox konnte nicht gelesen werden.'); wait_dialog_hide(); return;};
			
		});
	}
	
	$(function(){
		backend_mailaccounts_main();
	});

</script>

<?php

	include("templates/".TEMPLATE_BACKEND."/footer.php");

?>
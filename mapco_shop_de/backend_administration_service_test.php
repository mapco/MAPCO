<?php
	include("config.php");
	include("templates/".TEMPLATE_BACKEND."/header.php");
?>

	<script type="text/javascript">
	
	var posts = new Array();
	var step = 0;
	var file = '';
	var api = '';
	
		function call_service()
		{				//show_status2(print_r(posts));return;
			var id = '';
			var step = 2;
			var post_data = new Array();

			for(x=0;x<posts.length;x++)
			{
				id = '#'+posts[x];
				post_data[x]= $(id).val();
			}
			
			var post_keys = posts.toString();
			var post_data_string = post_data.toString();
			
			wait_dialog_show('Ruft den Service auf');
			$.post("<?php echo PATH; ?>soa2/", { API:"cms", APIRequest:"ServiceTester", post_keys:post_keys, post_data_string:post_data_string, step:step, api:api, file:file }, function($data)
			{				
				//show_status2($data);
				var service_output = '<textarea style="width:100%; height:450px;">';
				service_output += $data;
				service_output += '</textarea>';
				$("#service_output").empty().append(service_output);	
				wait_dialog_hide();
			});
		}
	
		function prepare_service_call()
		{
			var step = 1;
			var count_posts = 0;
			wait_dialog_show('Prüft auf notwendige Eingabewerte');
			$.post("<?php echo PATH; ?>soa2/", { API:"cms", APIRequest:"ServiceTester", step:step, api:api, file:file }, function($data)
			{	
				//show_status2($data);return;			
				try{$xml = $($.parseXML($data))} catch($err){show_status2($err.message);return;}
				if($xml.find('Ack').text()!='Success'){show_status2($data);return;}
				
				var service_input = '<div id="service_input">';
				service_input += '<p>Erforderliche Eingaben:</p>';
				service_input += '	<table style="border:solid">';
				var $posts=new Array();
				$xml.find('post').each(function()
				{
					$posts[$posts.length]=$(this).text();
				});
				$posts.sort( function(a, b) { return a.localeCompare(b); } );
				for($i=0; $i<$posts.length; $i++)
				{
					service_input += '		<tr>';
					service_input += '			<td style="border:none;">' + $posts[$i] + '</td>';
					service_input += '			<td style="border:none;"><input id="'+$posts[$i]+'" /></td>';	
					service_input += '		</tr>';
					posts[$i] = $posts[$i];
				}
	
				service_input += '		<tr>';
				service_input += '			<td style="border:none;"><button id="button_call_service">Service ausführen</button></td><td style="border:none;"></td>';
				service_input += '		</tr>';
				service_input += '	</table>';
				service_input += '</div>';
				service_input += '<div id="service_output"></div>';
				
				$("#post_input").empty().append(service_input);

				$("#button_call_service").click(function(){
					call_service();
				});
				wait_dialog_hide();
			});
		}
	
	
		function build_test_menu()
		{
			var menu = '<table>';
			menu += '	<tr>';
			menu += '		<td style="border:0;">Ordner</td>';
			menu += '		<td style="border:0;">Service</td>';
			menu += '	</tr>';
			menu += '	<tr>';
			menu += '		<td style="border:0;"><input id="field_api" /></td>';
			menu += '		<td style="border:0;"><input id="field_file" /></td>';
			menu += '		<td style="border:0;"><button id="button_search_service">Service aufrufen</button></td>';
			menu += '	</tr>';
			menu += '</table>';
			menu += '<div id="post_input"></div>';
			
			file = '';
			api = '';
			
			$("#service_tester").append(menu);
			
			$("#button_search_service").click(function(){
				if ( $("#field_api").val() != '' && $("#field_file").val() != '' )
				{
					api = $("#field_api").val();
					file = $("#field_file").val(); 
					prepare_service_call();
				}
				else
				{
					alert('Zum Ausführen eines Services sind sowohl der Ordner als auch der Dateinamen erforderlich!');
				}
			});
		}
	
	$(function(){build_test_menu()});
	
	</script>
    
<?php
	echo '<div id="service_tester"></div>';
?>
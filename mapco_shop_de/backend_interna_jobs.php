<?php
	include("config.php");
	include("templates/".TEMPLATE_BACKEND."/header.php");
?>

<script type="text/javascript">

	function view(view)
	{
	
		wait_dialog_show();
		$.post("<?php echo PATH; ?>soa/", {API: "jobs", Action: "JobsView", view:view },
			function (data)
			{
				if (view=="menu") $("#menu").html(data);
				if (view=="jobs" || view=="rules") {$("#detail").html(data);}
				
				wait_dialog_hide();
			}
		);
	}

	function AddRule()
	{
		var name = $("#addRulesName").val();
		var rule = $("#rule").val();
		wait_dialog_show();
		$.post("<?php echo PATH; ?>soa/", {API: "jobs", Action: "JobsAddRule", name:name, rule:rule },
			function (data)
			{
				wait_dialog_hide();
				view("rules");
			}
		);
	}
		
		
		

</script>

<?php	
	echo '<div style="display:inline">';
	
		echo '<div id="menu" style="display:inline"></div>';	
		echo '<div id="detail" style="display:inline"></div>';
	
	echo '</div>';
	
	//ADD RULE
	echo '<div id="addRules" style="display:none">';
	echo '<table>';
	echo '<tr>';
		echo '<td><b>Name</b></td>';
		echo '<td><input type="text" name="name" id="addRulesName" size="40" />';
	echo '</tr><tr>';
		echo '<td><b>Rules</td>';
		echo '<td><textarea name="rule" id="addRulesRule" cols="40" rows="10"></textarea></td>';
	echo '</tr>';
	echo '</table>';
	echo '</div>';
	
	echo '<script>view(\'menu\');</script>';
	
	include("templates/".TEMPLATE_BACKEND."/footer.php");
?>
<?php
	include("config.php");
	$title="Abmeldung";	
	
	session_unset();
	session_destroy();
	session_start();
	session_regenerate_id();
	
	include("templates/".TEMPLATE."/header.php");
	include("functions/cms_t.php");
	
	//left column
//	include("templates/".TEMPLATE."/cms_leftcolumn.php");

?>
				
<script type="text/javascript">
	
	function user_logout_main()
	{
		var text = $('<br /><br /><br /><p style="font-size: 20px; font-weight: bold"><?php echo t("Sie haben sich erfolgreich abgemeldet"); ?>.</p>');
		$('#left_mid_right_column').append(text);
		text =$('<br /><br /><br /><br /><a href="<?php echo PATHLANG?>login/" style="cursor: pointer; font-size: 12px"><?php echo t("Möchten Sie sich erneut anmelden? Hier geht es zur Login-Seite");?></a>');
		$('#left_mid_right_column').append(text);
	}
	
</script>
		
<?php
	echo '<br /><div id="left_mid_right_column" style="height: 450px; text-align: center; width: 950px"></div>';
	//echo print_r($_SESSION);
	include("templates/".TEMPLATE."/footer.php");
?>	
<script type="text/javascript">user_logout_main();</script>
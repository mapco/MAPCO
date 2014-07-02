<?php
	include("config.php");
	include("templates/".TEMPLATE."/header.php");

	//left column
	include("templates/".TEMPLATE."/cms_leftcolumn.php");
	
?>

	<script>
		var date=new Date();
		var year=date.getFullYear();
		var month=date.getMonth()+1;
		
		function view()
		{
			var response=ajax("<?php echo PATH; ?>modules/news_archive_view.php?lang=<?php echo $_GET["lang"]; ?>&year="+year+"&month="+month, false);
			document.getElementById("view").innerHTML=response;
		}
		
		function setSelectedMonth(month2)
		{
			month=month2;
			view();
		}
		function setSelectedYear(year2)
		{
			year=year2;
			view();
		}
    </script>


<?php

	echo '<div id="view"></div>';
	echo '<script> view(); </script>';

	include("templates/".TEMPLATE."/cms_rightcolumn.php");
	include("templates/".TEMPLATE."/footer.php");
?>
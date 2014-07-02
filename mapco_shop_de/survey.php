
<script language="javascript">

function survey_intro()
{
	$("#survey_intro").show();
}

function survey_intro_hide()
{
	$("#survey_intro").hide();
}

function survey_page1()
{
	$("#survey_intro").hide();
	$("#survey_page2").hide();
	$("#survey_page3").hide();
	$("#survey_page4").hide();
	$("#survey_page1").show();
}

function survey_page1_hide()
{
	$("#survey_page1").hide();
}

function survey_page2()
{
	$("#survey_page1").hide();
	$("#survey_page2").show();
}

function survey_page2_hide()
{
	$("#survey_page2").hide();
}

function survey_page3()
{
	$("#survey_page2").hide();
	$("#survey_page3").show();
}

function survey_page3_hide()
{
	$("#survey_page3").hide();
}

function survey_page4()
{
	$("#survey_page3").hide();
	$("#survey_page4").show();
}

function survey_page4_hide()
{
	$("#survey_page4").hide();
}

survey_intro();

</script>

<?php

	//Survey Window Intro
	echo '<div id="survey_intro" class="popup" style="display:block;">';
	echo '<form method="post" enctype="multipart/form-data">';
	echo '<table style="position:absolute; left:50%; top:50%; width:600px; height:400px; margin-left:-300px; margin-top:-200px; background:#ffffff;">';
	echo '	<tr>';
	echo '		<th colspan="2">';
	echo '			<span style="display:inline; float:left;">Umfrage</span>';
	echo '			<img style="margin:0; border:0; padding:0; cursor:pointer; display:inline; float:right;" src="images/icons/16x16/remove.png" onclick="ebay_deactivate_cancel();" alt="Schließen" title="Schließen" />';
	echo '		</th>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td colspan="2">';
	echo '			<input class="formbutton" type="button" value="Abbrechen" onclick="survey_intro_hide();" />';
	echo '			<input class="formbutton" type="button" value="Weiter" onclick="survey_page1();" />';
	echo '	</td>';
	echo '	</tr>';
	echo '</table>';
	echo '</form>';
	echo '</div>';

	//Survey Window Page1
	echo '<div id="survey_page1" class="popup" style="display:none;">';
	echo '<form method="post" enctype="multipart/form-data">';
	echo '<table style="position:absolute; left:50%; top:50%; width:600px; height:400px; margin-left:-300px; margin-top:-200px; background:#ffffff;">';
	echo '	<tr>';
	echo '		<th colspan="2">';
	echo '			<span style="display:inline; float:left;">Seite 1</span>';
	echo '			<img style="margin:0; border:0; padding:0; cursor:pointer; display:inline; float:right;" src="images/icons/16x16/remove.png" onclick="ebay_deactivate_cancel();" alt="Schließen" title="Schließen" />';
	echo '		</th>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td colspan="2">';
	echo '			<input class="formbutton" type="button" value="Abbrechen" onclick="survey_page1_hide();" />';
	echo '			<input class="formbutton" type="button" value="Zurück" onclick="survey_page1();" />';
	echo '			<input class="formbutton" type="button" value="Weiter" onclick="survey_page2();" />';
	echo '	</td>';
	echo '	</tr>';
	echo '</table>';
	echo '</form>';
	echo '</div>';

	//Survey Window Page 2
	echo '<div id="survey_page2" class="popup" style="display:none;">';
	echo '<form method="post" enctype="multipart/form-data">';
	echo '<table style="position:absolute; left:50%; top:50%; width:600px; height:400px; margin-left:-300px; margin-top:-200px; background:#ffffff;">';
	echo '	<tr>';
	echo '		<th colspan="2">';
	echo '			<span style="display:inline; float:left;">Seite 2</span>';
	echo '			<img style="margin:0; border:0; padding:0; cursor:pointer; display:inline; float:right;" src="images/icons/16x16/remove.png" onclick="ebay_deactivate_cancel();" alt="Schließen" title="Schließen" />';
	echo '		</th>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td colspan="2">';
	echo '			<input class="formbutton" type="button" value="Abbrechen" onclick="survey_page2_hide();" />';
	echo '			<input class="formbutton" type="button" value="Weiter" onclick="survey_page3();" />';
	echo '	</td>';
	echo '	</tr>';
	echo '</table>';
	echo '</form>';
	echo '</div>';

	//Survey Window Page 3
	echo '<div id="survey_page3" class="popup" style="display:none;">';
	echo '<form method="post" enctype="multipart/form-data">';
	echo '<table style="position:absolute; left:50%; top:50%; width:600px; height:400px; margin-left:-300px; margin-top:-200px; background:#ffffff;">';
	echo '	<tr>';
	echo '		<th colspan="2">';
	echo '			<span style="display:inline; float:left;">Seite 3</span>';
	echo '			<img style="margin:0; border:0; padding:0; cursor:pointer; display:inline; float:right;" src="images/icons/16x16/remove.png" onclick="ebay_deactivate_cancel();" alt="Schließen" title="Schließen" />';
	echo '		</th>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td colspan="2">';
	echo '			<input class="formbutton" type="button" value="Abbrechen" onclick="survey_page3_hide();" />';
	echo '			<input class="formbutton" type="button" value="Weiter" onclick="survey_page4();" />';
	echo '	</td>';
	echo '	</tr>';
	echo '</table>';
	echo '</form>';
	echo '</div>';

	//Survey Window Page 4
	echo '<div id="survey_page4" class="popup" style="display:none;">';
	echo '<form method="post" enctype="multipart/form-data">';
	echo '<table style="position:absolute; left:50%; top:50%; width:600px; height:400px; margin-left:-300px; margin-top:-200px; background:#ffffff;">';
	echo '	<tr>';
	echo '		<th colspan="2">';
	echo '			<span style="display:inline; float:left;">Seite 4</span>';
	echo '			<img style="margin:0; border:0; padding:0; cursor:pointer; display:inline; float:right;" src="images/icons/16x16/remove.png" onclick="ebay_deactivate_cancel();" alt="Schließen" title="Schließen" />';
	echo '		</th>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td colspan="2">';
	echo '			<input class="formbutton" type="button" value="Abbrechen" onclick="survey_page4_hide();" />';
	echo '			<input class="formbutton" type="button" value="Absenden" onclick="survey_page4_hide();" />';
	echo '	</td>';
	echo '	</tr>';
	echo '</table>';
	echo '</form>';
	echo '</div>';






?>
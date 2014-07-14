<?php
	include("config.php");
	$login_required=true;
	include("templates/".TEMPLATE_BACKEND."/header.php");
?>

<script language="javascript">
	function view(editlang, lang)
	{
		var response=ajax('<?php echo PATH; ?>modules/backend_cms_translations_view.php?editlang='+editlang+'&lang='+lang, false);
		document.getElementById("results").innerHTML=response;
	}
	function update(id_translation, translation, editlang, lang, e)
	{
		if (!e) var e=window.event;
		if (e.keyCode == 13)
		{
			var response=ajax('<?php echo PATH; ?>modules/backend_cms_translations_update.php?id_translation='+id_translation+'&translation='+encodeURIComponent(translation)+'&editlang='+encodeURIComponent(editlang), false);
			view(editlang, lang);
		}
	}
	function remove(id_translation, editlang, lang)
	{
		if (confirm("Übersetzung wirklich löschen"))
		{
			var response=ajax('<?php echo PATH; ?>modules/backend_cms_translations_remove.php?id_translation='+id_translation, false);
			view(editlang, lang);
		}
	}
	
	function check_translations()
	{
		$.post("<?php echo PATH; ?>soa/", { API:"cms", Action:"TranslationsCheck" }, function($data)
		{
			show_status2($data);
		});
	}
</script>

<?php
	//PATH
	echo '<p>';
	echo '<a href="backend_index.php?lang='.$_GET["lang"].'">'.t("Backend", __FILE__, __LINE__).'</a>';
	echo ' > <a href="backend_cms_index.php?lang='.$_GET["lang"].'">'.t("Inhalte", __FILE__, __LINE__).'</a>';
	echo ' > '.t("Übersetzungen", __FILE__, __LINE__);
	echo '</p>';


	//select language
	echo '<h1>'.t("Übersetzungsverwaltung", __FILE__, __LINE__).'</h1>';
	echo '<form method="post">';
	echo '	<table class="hover">';
	echo '		<tr><th colspan="2">'.t("Sprache auswählen", __FILE__, __LINE__).'</th></tr>';
	echo '		<tr>';
	echo '			<td>'.t("Sprache", __FILE__, __LINE__).'</td>';
	echo '			<td>';
	echo '				<select name="editlang" onchange="view(this.value, \''.$_GET["lang"].'\')">';
	$results=q("SELECT * FROM cms_languages ORDER BY ordering;", $dbweb, __FILE__, __LINE__);
	while($row=mysqli_fetch_array($results))
	{
		if ($_GET["lang"]==$row["code"]) $selected=' selected="selected"'; else $selected='';
		echo '<option'.$selected.' value="'.$row["code"].'">'.t($row["language"], __FILE__, __LINE__).'</option>';
	}
	echo '				</select>';
	echo '			</td>';
	echo '		</tr>';
	echo '	</table>';
	echo '</form>';

		
	//UPDATE
	if (isset($_POST["translation_update"]))
	{
		$results=q("SELECT * FROM cms_translations;", $dbweb, __FILE__, __LINE__);
		while($row=mysqli_fetch_array($results))
		{
			if (isset($_POST[$row["id_translation"]]) and $_POST[$row["id_translation"]]!=$row[$_POST["editlang"]])
			{
				q("UPDATE cms_translations SET ".$_POST["editlang"]."='".addslashes(stripslashes($_POST[$row["id_translation"]]))."', lastmod=".time().", lastmod_user=".$_SESSION["id_user"]." WHERE id_translation=".$row["id_translation"].";", $dbweb, __FILE__, __LINE__);
			}
		}
		echo '<div class="success">'.t("Übersetzungen erfolgreich gespeichert", __FILE__, __LINE__).'.</div>';
	}


	//VIEW
	echo '<div id="results"></div>';
	echo '<script language="javascript"> view(\''.$_GET["lang"].'\', \''.$_GET["lang"].'\'); </script>';
		
	include("templates/".TEMPLATE_BACKEND."/footer.php");
?>
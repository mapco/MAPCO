<?php
	include("config.php");
	include("templates/".TEMPLATE_BACKEND."/header.php");
?>

	<script type="text/javascript">
		function error_remove($id_errortype, $id_error)
		{
			$confirm=confirm("Sind Sie sicher, dass der Fehler gelöscht werden kann?");
			if( $confirm )
			{
				$.post("<?php echo PATH; ?>soa/", { API:"cms", Action:"ErrorRemove", id_errortype:$id_errortype, id_error:$id_error }, function($data)
				{
					show_status2($data);
				});
			}
		}
	</script>

<?php
	//PATH
	echo '<p>';
	echo '<a href="backend_index.php?lang='.$_GET["lang"].'">'.t("Backend").'</a>';
	echo ' > <a href="backend_administration_index.php?lang='.$_GET["lang"].'">'.t("Administration").'</a>';
	echo ' > Fehler';
	echo '</p>';
	
	echo '<h1>Fehler</h1>';

	echo '<table>';
	echo '	<tr>';
	echo '		<th>Nr.</th>';
	echo '		<th>Prozess</th>';
	echo '	</tr>';
	$i=0;
	$results=q("SHOW PROCESSLIST;", $dbshop, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		$i++;
		echo '<tr>';
		echo '	<td>'.$i.'</td>';
		echo '	<td>'.print_r($row, true).'</td>';
		echo '</tr>';
	}
	echo '</table>';

?>
<form name="mysqlform" action="https://konsoleh.your-server.de/mysql.php" method="GET" target="PMA">
  <table cellpadding="0" cellspacing="0" border="0" width="590" align="center" class="textbox_text_light">
    <tbody><tr>
      <td width="3"><img src="/templates/ui-default/de/images/header_left_bottomsquare.gif" width="3" height="18"></td>
      <td></td>
      <td>Quota:&nbsp;<acronym title="MySQL:10&nbsp;/&nbsp;PgSQL:0">10</acronym> / ∞</td>
      <td align="right">[&nbsp;<a href="mysql.php?add=display&amp;tdbs=10">Hinzufügen</a>&nbsp;|&nbsp;<a href="#" onclick="MM_changeProp('mysqlform','','action','mysql.php','FORM');MM_changeProp('mysqlform','','target','_self','FORM');MM_changeProp('mysqlaction','','value','edit','INPUT');document.forms[0].submit();return false;">Ändern</a>&nbsp;|&nbsp;<a href="#" onclick="MM_changeProp('mysqlform','','action','mysql.php','FORM');MM_changeProp('mysqlform','','target','_self','FORM');MM_changeProp('mysqlaction','','value','delete','INPUT');document.forms[0].submit();return false;">Löschen</a>&nbsp;|&nbsp;<a href="#" onclick="MM_changeProp('mysqlform','','action','backup.php','FORM');MM_changeProp('mysqlform','','target','_self','FORM');MM_changeProp('mysqlaction','','value','backup.php','INPUT');document.forms[0].submit();return false;">Backup</a>&nbsp;|&nbsp;<a href="#" onclick="MM_changeProp('mysqlform','','action','mysql.php','FORM');MM_changeProp('mysqlform','','target','PMA','FORM');MM_changeProp('mysqlaction','','value','phpmyadmin','INPUT');document.forms[0].submit();return false;">phpMyAdmin</a><img src="/templates/ui-default/de/images/blockblock_trans.gif">&nbsp;]								
      </td>
  		<td width="3"><img src="/templates/ui-default/de/images/header_right_bottomsquare.gif" width="3" height="18"></td>
  	</tr>
  </tbody></table>
  
  <table width="590" align="center" cellpadding="0" cellspacing="0" border="0">
  	<tbody><tr><td colspan="4"><img src="/templates/ui-default/de/images/trans.gif" width="1" height="1"></td></tr>
  	<tr>
  		<td width="1" class="loopOn"><img src="/templates/ui-default/de/images/trans.gif" width="1" height="20"></td>
  		<td width="100%">
  			<table width="100%" cellpadding="0" cellspacing="0" border="0">
  				<tbody><tr class="loopon">
            <td width="21" align="center"><input id="Features_used_Number" name="Features_used_Number" type="radio" class="radio" value="FU0993237809:_mapcoshop:dedi473.your-server.de" checked=""></td>
            <td>_mapcoshop <small>(dedi473.your-server.de)</small></td>
  					<td>MySQL 5.0</td>	
  					<td align="right"></td>
  					<td width="21"><img src="/templates/ui-default/de/images/trans.gif" width="21" height="16" border="0"></td>
          </tr>  				<tr>
            <td width="21" align="center"><input id="Features_used_Number" name="Features_used_Number" type="radio" class="radio" value="FU0993239909:admapco_autoparts:dedi473.your-server.de"></td>
            <td>admapco_autoparts <small>(dedi473.your-server.de)</small></td>
  					<td>MySQL 5.0</td>	
  					<td align="right"></td>
  					<td width="21"><img src="/templates/ui-default/de/images/trans.gif" width="21" height="16" border="0"></td>
          </tr>  				<tr class="loopon">
            <td width="21" align="center"><input id="Features_used_Number" name="Features_used_Number" type="radio" class="radio" value="FU02278046213:admapco_franchise:dedi473.your-server.de"></td>
            <td>admapco_franchise <small>(dedi473.your-server.de)</small></td>
  					<td>MySQL 5.0</td>	
  					<td align="right"></td>
  					<td width="21"><img src="/templates/ui-default/de/images/trans.gif" width="21" height="16" border="0"></td>
          </tr>  				<tr>
            <td width="21" align="center"><input id="Features_used_Number" name="Features_used_Number" type="radio" class="radio" value="FU0993238509:admapco_mapco-shop:dedi473.your-server.de"></td>
            <td>admapco_mapco-shop <small>(dedi473.your-server.de)</small></td>
  					<td>MySQL 5.0</td>	
  					<td align="right"></td>
  					<td width="21"><img src="/templates/ui-default/de/images/trans.gif" width="21" height="16" border="0"></td>
          </tr>  				<tr class="loopon">
            <td width="21" align="center"><input id="Features_used_Number" name="Features_used_Number" type="radio" class="radio" value="FU0993239209:admapco_mapcoshop:dedi473.your-server.de"></td>
            <td>admapco_mapcoshop <small>(dedi473.your-server.de)</small></td>
  					<td>MySQL 5.0</td>	
  					<td align="right"></td>
  					<td width="21"><img src="/templates/ui-default/de/images/trans.gif" width="21" height="16" border="0"></td>
          </tr>  				<tr>
            <td width="21" align="center"><input id="Features_used_Number" name="Features_used_Number" type="radio" class="radio" value="FU02175224511:mapco_shop_fr:dedi473.your-server.de"></td>
            <td>mapco_shop_fr <small>(dedi473.your-server.de)</small></td>
  					<td>MySQL 5.0</td>	
  					<td align="right"></td>
  					<td width="21"><img src="/templates/ui-default/de/images/trans.gif" width="21" height="16" border="0"></td>
          </tr>  				<tr class="loopon">
            <td width="21" align="center"><input id="Features_used_Number" name="Features_used_Number" type="radio" class="radio" value="FU03179694911:mapco_shop_gomersall:dedi473.your-server.de"></td>
            <td>mapco_shop_gomersall <small>(dedi473.your-server.de)</small></td>
  					<td>MySQL 5.0</td>	
  					<td align="right"></td>
  					<td width="21"><img src="/templates/ui-default/de/images/trans.gif" width="21" height="16" border="0"></td>
          </tr>  				<tr>
            <td width="21" align="center"><input id="Features_used_Number" name="Features_used_Number" type="radio" class="radio" value="FU02177133811:mapco_shop_it:dedi473.your-server.de"></td>
            <td>mapco_shop_it <small>(dedi473.your-server.de)</small></td>
  					<td>MySQL 5.0</td>	
  					<td align="right"></td>
  					<td width="21"><img src="/templates/ui-default/de/images/trans.gif" width="21" height="16" border="0"></td>
          </tr>  				<tr class="loopon">
            <td width="21" align="center"><input id="Features_used_Number" name="Features_used_Number" type="radio" class="radio" value="FU0993240609:test_autoparts:dedi473.your-server.de"></td>
            <td>test_autoparts <small>(dedi473.your-server.de)</small></td>
  					<td>MySQL 5.0</td>	
  					<td align="right"></td>
  					<td width="21"><img src="/templates/ui-default/de/images/trans.gif" width="21" height="16" border="0"></td>
          </tr>  				<tr>
            <td width="21" align="center"><input id="Features_used_Number" name="Features_used_Number" type="radio" class="radio" value="FU0993241309:test_mapcoshop:dedi473.your-server.de"></td>
            <td>test_mapcoshop <small>(dedi473.your-server.de)</small></td>
  					<td>MySQL 5.0</td>	
  					<td align="right"></td>
  					<td width="21"><img src="/templates/ui-default/de/images/trans.gif" width="21" height="16" border="0"></td>
          </tr>  			</tbody></table>
  		</td>
  		<td width="1" class="loopOn"><img src="/templates/ui-default/de/images/trans.gif" width="1" height="20"></td>
  	</tr>
  	<tr class="loopOn" height="1"><td colspan="4"><img src="/templates/ui-default/de/images/trans.gif" width="1" height="1"></td></tr>
  </tbody></table>
  
      <table>
    <tbody><tr><td align="center">[ <a href="mysql.php?sizes=true">Datenbankgröße anzeigen</a> ]</td></tr>
    <tr><td>Bitte beachten Sie, dass diese Funktion sehr lange benötigt falls Sie viele Datenbanken haben.</td></tr>       
  </tbody></table>  
  <table width="590" align="center" border="0" cellpadding="0" cellspacing="0">
  	<tbody><tr><td colspan="3"><img src="/templates/ui-default/de/images/trans.gif" height="1"></td></tr>
  	<tr>
  		<td width="3"><img src="/templates/ui-default/de/images/footer_left.gif" width="3" height="10"></td>
  		<td width="100%" class="footer_bg"></td>
  		<td width="3"><img src="/templates/ui-default/de/images/footer_right.gif" width="3" height="10"></td>
  	</tr>
  </tbody></table>
  <input type="hidden" name="mysqlaction" id="mysqlaction" value="phpmyadmin">
  <input type="submit" value="Zur Datenbank" />
</form>

<?php	
	
	include("templates/".TEMPLATE_BACKEND."/footer.php");
?>
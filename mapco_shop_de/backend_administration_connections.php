<?php
	include("config.php");
	include("templates/".TEMPLATE_BACKEND."/header.php");
?>

	<script type="text/javascript">
		function whitelist_add($ip)
		{
			$("#whitelist_add_name").val("");
			$("#whitelist_add_ip").val($ip);
			$("#whitelist_add_dialog").dialog
			({	buttons:
				[
					{ text: "Hinzufügen", click: function() { whitelist_add_save(); } },
					{ text: "Abbrechen", click: function() { $(this).dialog("close"); } }
				],
				closeText:"Fenster schließen",
				hide: { effect: 'drop', direction: "up" },
				modal:true,
				resizable:false,
				show: { effect: 'drop', direction: "up" },
				title:"Zur Whitelist hinzufügen",
				width:400
			});
		}
		
		function whitelist_add_save()
		{
			wait_dialog_show();
			$("#whitelist_add_dialog").dialog("close");
			var $ip=$("#whitelist_add_ip").val();
			var $name=$("#whitelist_add_name").val();
			$.post("<?php echo PATH; ?>soa/", { API:"cms", Action:"ConnectionsWhitelistAdd", ip:$ip, name:$name }, function($data)
			{
				show_status2($data);
				wait_dialog_hide();
			});
		}

		function blacklist_remove($ip)
		{
			if( confirm("Möchten Sie die IP-Adresse wirklich von der Blacklist löschen?") )
			$.post("<?php echo PATH; ?>soa/", { API:"cms", Action:"ConnectionsBlacklistRemove", ip:$ip }, function($data)
			{
				show_status2($data);
			});
		}
	</script>

<?php

	//PATH
	echo '<p>';
	echo '<a href="backend_index.php?lang='.$_GET["lang"].'">'.t("Backend").'</a>';
	echo ' > <a href="backend_administration_index.php?lang='.$_GET["lang"].'">'.t("Administration").'</a>';
	echo ' > Verbindungen';
	echo '</p>';
	
	echo '<h1>Verbindungen</h1>';

	//get all whitelist connections
	$whitelist=array();
	$results=q("SELECT * FROM cms_connections_whitelist;", $dbweb, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		$whitelist[$row["ip"]]=$row["name"];
	}

	//all connections in the last 60 seconds
	$connection=array();
	$results=q("SELECT * FROM cms_connections;", $dbweb, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		if( isset($connection[$row["ip"]]) )
		{
			$connection[$row["ip"]]["count"]++;
		}
		else
		{
			$connection[$row["ip"]]["count"]=1;
			$connection[$row["ip"]]["ip"]=$row["ip"];
		}
	}
	rsort($connection);
	echo '<table class="hover" style="float:left;">'."\n";
	echo '	<tr>'."\n";
	echo '		<th colspan="6">Aktuelle Verbindungen (<60s)</th>'."\n";
	echo '	</tr>'."\n";
	echo '	<tr>'."\n";
	echo '		<th>Nr.</th>'."\n";
	echo '		<th>ID</th>'."\n";
	echo '		<th>IP-Adresse</th>'."\n";
	echo '		<th>Verbindungen</th>'."\n";
	echo '		<th>Name</th>'."\n";
	echo '		<th>Optionen</th>'."\n";
	echo '	</tr>'."\n";
	for($i=0; $i<sizeof($connection); $i++)
	{
		echo '<tr>'."\n";
		echo '	<td>'.($i+1).'</td>'."\n";
		echo '	<td>'.$connection[$i]["ip"].'</td>'."\n";
		echo '	<td><a href="http://www.ip-adress.com/ip_lokalisieren/'.long2ip($connection[$i]["ip"]).'" target="_blank">'.long2ip($connection[$i]["ip"]).'</a></td>'."\n";
		echo '	<td>'.$connection[$i]["count"].'</td>'."\n";
		if( isset($whitelist[$connection[$i]["ip"]]) ) $name=$whitelist[$connection[$i]["ip"]]; else $name="";
		echo '	<td>'.$name.'</td>'."\n";
		echo '	<td>'."\n";
		if( $name=="" )
		{
			echo '	<img alt="Zur Whitelist hinzufügen" onclick="whitelist_add('.$connection[$i]["ip"].');" src="'.PATH.'images/icons/24x24/add.png" style="cursor:pointer;" title="Zur Whitelist hinzufügen" />'."\n";
		}
		echo '	</td>'."\n";
		echo '</tr>'."\n";
	}
	echo '</table>'."\n";

	//all blocked users
	echo '<table class="hover" style="float:left">'."\n";
	echo '	<tr>'."\n";
	echo '		<th colspan="6">Gesperrte IP-Adressen (Blacklist)</th>'."\n";
	echo '	</tr>'."\n";
	echo '	<tr>'."\n";
	echo '		<th>Nr.</th>'."\n";
	echo '		<th>IP-Adresse</th>'."\n";
	echo '		<th>Provider</th>'."\n";
	echo '		<th>Gesperrt bis</th>'."\n";
	echo '		<th>Optionen</th>'."\n";
	echo '	</tr>'."\n";
	$i=0;
	$results=q("SELECT * FROM cms_connections_blacklist;", $dbweb, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		$i++;
		echo '<tr>'."\n";
		echo '	<td>'.$i.'</td>'."\n";
		echo '	<td><a href="http://www.ip-adress.com/ip_lokalisieren/'.long2ip($row["ip"]).'" target="_blank">'.long2ip($row["ip"]).'</a></td>'."\n";
		echo '	<td>'.gethostbyaddr(long2ip($row["ip"])).' <span style="float:right;">(<a href="http://whois.domaintools.com/'.long2ip($row["ip"]).'" target="_blank">Whois</a>)</span></td>'."\n";
		echo '	<td>'.date("d-m-Y H:i", $row["time"]).'</td>'."\n";
		echo '	<td>'."\n";
		echo '		<img alt="Zur Whitelist hinzufügen" onclick="whitelist_add('.$row["ip"].');" src="'.PATH.'images/icons/24x24/add.png"  style="cursor:pointer;" title="Zur Whitelist hinzufügen" />'."\n";
		echo '		<img alt="Von der Blacklist löschen" onclick="blacklist_remove('.$row["ip"].');" src="'.PATH.'images/icons/24x24/remove.png"  style="cursor:pointer;" title="Von der Blacklist löschen" />'."\n";
		echo '	</td>'."\n";
		echo '</tr>'."\n";
	}
	echo '</table>'."\n";

	//whitelist add dialog
	echo '<div id="whitelist_add_dialog" style="display:none;">';
	echo '	<input id="whitelist_add_name" type="text" value="" />';
	echo '	<input id="whitelist_add_ip" type="hidden" value="" />';
	echo '</div>';
	
	include("templates/".TEMPLATE_BACKEND."/footer.php");
?>
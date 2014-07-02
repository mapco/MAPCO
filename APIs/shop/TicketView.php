<script type="text/javascript">

	function show_msg_recieved() 
	{
		$("#msg_recieved").show();
		$("#msg_sent").hide();
		$("#msg_all").hide();
		}
		
	function show_msg_sent() 
	{
		$("#msg_recieved").hide();
		$("#msg_sent").show();
		$("#msg_all").hide();
		}

	function show_msg_all() 
	{
		$("#msg_recieved").hide();
		$("#msg_sent").hide();
		$("#msg_all").show();
		}

</script>
<?

//ermittle Usermail
	// Usermail ermitteln
	$sql = "select usermail from cms_users where id_user = '".$_SESSION["id_user"]."'";
	$results=q($sql, $dbweb, __FILE__, __LINE__);
	while( $row=mysql_fetch_array($results) ) { $usermail=$row["usermail"]; }


if ($_POST["mode"]=="view_tabs") 
	{
	// ANZEIGE TABS
		echo '<table>';
		echo '<tr>';
		echo '	<td style="width:150px" onclick="show_msg_recieved();"> erhaltene Nachrichten</a></td>';
		echo '	<td style="width:150px" onclick="show_msg_sent();">gesendete Nachrichten</td>';
		echo '	<td style="width:150px" onclick="show_msg_all();">alle Nachrichten</td>';
		echo '</tr>';
		echo '</table>';
	}
	
if ($_POST["mode"]=="view_msg") 
	{

	// ANZEIGE MSG
		echo '<div id="msg_recieved" style="display:inline; float:left;">';
		echo '<table style="width:1200px; height="700px">';
		echo '<tr>';

		echo '	<td style="width:150px">hier stehen die erhaltenen nachrichten</td>';
		echo '</tr>';
		echo '</table>';
		echo '</div>';

		echo '<div id="msg_sent" style="display:inline; float:left;">';
		echo '<table style="width:1200px; height="700px">';
		
				$sql = "select * from cms_conversations where conv_start_usermail = '".$usermail."'";
		$results=q($sql, $dbweb, __FILE__, __LINE__);
		
			while( $row=mysql_fetch_array($results) ) 
			{
				echo '<tr><td id='".$row["id_conv"]."' onclick="conversation('\.$row["id_conv"].'\);">';
				$sql2="select * from cms_conversations_posts where id_conv = '".$row["id_conv"]."' order by post_date";
				$results2=q($sql2, $dbweb, __FILE__, __LINE__);
				while( $row2=mysql_fetch_array($results2) ) 
				{
					
					$sql3="select title from cms_articles where id_article = '".$row2["id_cms_article"]."'";
					$results3=q($sql3, $dbweb, __FILE__, __LINE__);
					while( $row3=mysql_fetch_array($results3) ) { echo $row3["title"].'<br>'; }
				 }
				 echo '</td></tr>';
			}

//		echo '	<td style="width:150px">hier stehen die gesendeten nachrichten</td>';
		
		echo '</table>';
		echo '</div>';

		echo '<div id="msg_all" style="display:inline; float:left;">';
		echo '<table style="width:1200px; height="700px">';
		echo '<tr>';
		echo '	<td style="width:150px">hier stehen alle nachrichten</td>';
		echo '</tr>';
		echo '</table>';
		echo '</div>';
		
		echo '<script>show_msg_recieved();</script>';

	}
?>

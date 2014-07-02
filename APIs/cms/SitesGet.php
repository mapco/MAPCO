<?php
	$results=q("SELECT * FROM cms_sites LIMIT 1;", $dbweb, __FILE__, __LINE__);
	$row=mysqli_fetch_array($results);
	$keys=array_keys($row);

	$results=q("SELECT * FROM cms_sites;", $dbweb, __FILE__, __LINE__);
	
	echo '<SitesGetResponse>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	while($row=mysqli_fetch_array($results))
	{
		if( $_SESSION["id_site"]==$row["id_site"] ) $selected=' selected="selected"'; else $selected='';
		echo '<Site'.$selected.'>';
		for($i=0; $i<sizeof($keys); $i++)
		{
			if( !is_numeric($keys[$i]) )
				echo '	<'.$keys[$i].'><![CDATA['.$row[$keys[$i]].']]></'.$keys[$i].'>'."\n";
		}
		echo '</Site>';
	}
	echo '</SitesGetResponse>'."\n";

?>
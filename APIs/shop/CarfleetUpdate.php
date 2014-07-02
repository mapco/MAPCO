<?php
//	echo 'Service läuft';
//	echo $_SESSION["id_user"];
	if ( !isset($_POST["mode"]) )
	{
		echo '<CarfleetUpdateResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Modus nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss ein Modus für das Update angegeben werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</CarfleetUpdateResponse>'."\n";
		exit;
	}
	
	if ( !isset($_POST["customer_vehicle_id"]) )
	{
		echo '<CarfleetUpdateResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Customer_Vehicle ID nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine Customer_Vehicle ID angegeben werden, die zur Liste hinzugefügt werden soll.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</CarfleetUpdateResponse>'."\n";
		exit;
	}
	
	if($_POST["mode"]=="car_deactivate")
	{
		$res_check=q("SELECT * FROM shop_carfleet WHERE id = ".$_POST["customer_vehicle_id"].";", $dbshop, __FILE__, __LINE__);
		if (mysqli_num_rows($res_check)==1) 
		{		
			q("UPDATE shop_carfleet SET active=0, lastmod=".time().", lastmod_user=".$_SESSION["id_user"]." WHERE id = ".$_POST["customer_vehicle_id"].";", $dbshop, __FILE__, __LINE__);
		
			echo "<CarfleetUpdateResponse>\n";
			echo "<Ack>Success</Ack>\n";
			echo "</CarfleetUpdateResponse>";
			exit;
		}
		echo "<CarfleetUpdateResponse>\n";
		echo "<Ack>Failure</Ack>\n";
		echo "</CarfleetUpdateResponse>";
		exit;
	}
	
	if($_POST["mode"]=="car_update_date_built")
	{
		if ( !isset($_POST["date_built"]) )
		{
			echo '<CarfleetUpdateResponse>'."\n";
			echo '	<Ack>Failure</Ack>'."\n";
			echo '	<Error>'."\n";
			echo '		<Code>'.__LINE__.'</Code>'."\n";
			echo '		<shortMsg>Erstzulassungsdatum nicht gefunden.</shortMsg>'."\n";
			echo '		<longMsg>Es muss ein Erstzulassungsdatum angegeben werden, das zur Liste hinzugefügt werden soll.</longMsg>'."\n";
			echo '	</Error>'."\n";
			echo '</CarfleetUpdateResponse>'."\n";
			exit;
		}
		$res_check=q("SELECT * FROM shop_carfleet WHERE id = ".$_POST["customer_vehicle_id"].";", $dbshop, __FILE__, __LINE__);
		if (mysqli_num_rows($res_check)==1) 
		{		
			q("UPDATE shop_carfleet SET date_built= ".$_POST["date_built"].", lastmod=".time().", lastmod_user=".$_SESSION["id_user"]." WHERE id = ".$_POST["customer_vehicle_id"].";", $dbshop, __FILE__, __LINE__);
		
			echo "<CarfleetUpdateResponse>\n";
			echo "<Ack>Success</Ack>\n";
			echo "</CarfleetUpdateResponse>";
			exit;
		}
		echo "<CarfleetUpdateResponse>\n";
		echo "<Ack>Failure</Ack>\n";
		echo "</CarfleetUpdateResponse>";
		exit;
	}
	
	if($_POST["mode"]=="car_update_flex")
	{
		if ( !isset($_POST["sql"]) )
		{
			echo '<CarfleetUpdateResponse>'."\n";
			echo '	<Ack>Failure</Ack>'."\n";
			echo '	<Error>'."\n";
			echo '		<Code>'.__LINE__.'</Code>'."\n";
			echo '		<shortMsg>sql-string nicht gefunden.</shortMsg>'."\n";
			echo '		<longMsg>Es muss ein sql-string angegeben werden.</longMsg>'."\n";
			echo '	</Error>'."\n";
			echo '</CarfleetUpdateResponse>'."\n";
			exit;
		}
		$res_check=q("SELECT * FROM shop_carfleet WHERE id = ".$_POST["customer_vehicle_id"].";", $dbshop, __FILE__, __LINE__);
		if (mysqli_num_rows($res_check)==1) 
		{		
			q("UPDATE shop_carfleet SET ".$_POST["sql"].", lastmod=".time().", lastmod_user=".$_SESSION["id_user"]." WHERE id = ".$_POST["customer_vehicle_id"].";", $dbshop, __FILE__, __LINE__);
		
			echo "<CarfleetUpdateResponse>\n";
			echo "<Ack>Success</Ack>\n";
			echo "</CarfleetUpdateResponse>";
			exit;
		}
		echo "<CarfleetUpdateResponse>\n";
		echo "<Ack>Failure</Ack>\n";
		echo "</CarfleetUpdateResponse>";
		exit;
	}
	
	if ( !isset($_POST["fin"]) )
	{
		echo '<CarfleetUpdateResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Fahrgestellnummer nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine Fahrgestellnummer angegeben werden, die zur Liste hinzugefügt werden soll.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</CarfleetUpdateResponse>'."\n";
		exit;
	}
	
	if($_POST["mode"]=="car_update_fin")
	{
		$res_check=q("SELECT * FROM shop_carfleet WHERE id = ".$_POST["customer_vehicle_id"].";", $dbshop, __FILE__, __LINE__);
		if (mysqli_num_rows($res_check)==1) 
		{		
			q("UPDATE shop_carfleet SET FIN='".$_POST["fin"]."', lastmod=".time().", lastmod_user=".$_SESSION["id_user"]." WHERE id = ".$_POST["customer_vehicle_id"].";", $dbshop, __FILE__, __LINE__);
		
			echo "<CarfleetUpdateResponse>\n";
			echo "<Ack>Success</Ack>\n";
			echo "</CarfleetUpdateResponse>";
			exit;
		}
		echo "<CarfleetUpdateResponse>\n";
		echo "<Ack>Failure</Ack>\n";
		echo "</CarfleetUpdateResponse>";
		exit;
	}	
	
	if ( !isset($_POST["date_built"]) )
	{
		echo '<CarfleetUpdateResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Erstzulassungsdatum nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss ein Erstzulassungsdatum angegeben werden, das zur Liste hinzugefügt werden soll.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</CarfleetUpdateResponse>'."\n";
		exit;
	}
	
	if($_POST["mode"]=="car_update")
	{
		$res_check=q("SELECT * FROM shop_carfleet WHERE id = ".$_POST["customer_vehicle_id"].";", $dbshop, __FILE__, __LINE__);
		if (mysqli_num_rows($res_check)==1) 
		{		
			q("UPDATE shop_carfleet SET date_built= ".$_POST["date_built"].", FIN='".$_POST["fin"]."', lastmod=".time().", lastmod_user=".$_SESSION["id_user"]." WHERE id = ".$_POST["customer_vehicle_id"].";", $dbshop, __FILE__, __LINE__);
		
			echo "<CarfleetUpdateResponse>\n";
			echo "<Ack>Success</Ack>\n";
			echo "</CarfleetUpdateResponse>";
			exit;
		}
		echo "<CarfleetUpdateResponse>\n";
		echo "<Ack>Failure</Ack>\n";
		echo "</CarfleetUpdateResponse>";
		exit;
	}
	
	if ( !isset($_POST["vehicle_id"]) )
	{
		echo '<CarfleetUpdateResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Vehicle ID nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine Vehicle ID angegeben werden, die zur Liste hinzugefügt werden soll.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</CarfleetUpdateResponse>'."\n";
		exit;
	}
	
	if ( !isset($_POST["kbanr"]) )
	{
		echo '<CarfleetUpdateResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>kba nr nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine KBA Nummer angegeben werden, die zur Liste hinzugefügt werden soll.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</CarfleetUpdateResponse>'."\n";
		exit;
	}
	
	if ( !isset($_POST["additional"]) )
	{
		echo '<CarfleetUpdateResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Keine Bemerkungen gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Keine Bemerkungen gefunden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</CarfleetUpdateResponse>'."\n";
		exit;
	}
	
	if ( !isset($_POST["c0003"]) )
	{
		echo '<CarfleetUpdateResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Keine c0003-Variable gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Keine c0003-Variable gefunden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</CarfleetUpdateResponse>'."\n";
		exit;
	}
	
	if ( !isset($_POST["c0004"]) )
	{
		echo '<CarfleetUpdateResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Keine c0004-Variable gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Keine c0004-Variable gefunden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</CarfleetUpdateResponse>'."\n";
		exit;
	}
	
	if ( !isset($_POST["c0005"]) )
	{
		echo '<CarfleetUpdateResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Keine c0005-Variable gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Keine c0005-Variable gefunden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</CarfleetUpdateResponse>'."\n";
		exit;
	}
	
	if ( !isset($_POST["c0006"]) )
	{
		echo '<CarfleetUpdateResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Keine c0006-Variable gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Keine c0006-Variable gefunden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</CarfleetUpdateResponse>'."\n";
		exit;
	}
	
	if ( !isset($_POST["s0033"]) )
	{
		echo '<CarfleetUpdateResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Keine s0033-Variable gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Keine s0033-Variable gefunden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</CarfleetUpdateResponse>'."\n";
		exit;
	}
	
	if ( !isset($_POST["s0038"]) )
	{
		echo '<CarfleetUpdateResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Keine s0038-Variable gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Keine s0038-Variable gefunden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</CarfleetUpdateResponse>'."\n";
		exit;
	}
	
	if ( !isset($_POST["s0040"]) )
	{
		echo '<CarfleetUpdateResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Keine s0040-Variable gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Keine s0040-Variable gefunden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</CarfleetUpdateResponse>'."\n";
		exit;
	}
	
	if ( !isset($_POST["s0067"]) )
	{
		echo '<CarfleetUpdateResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Keine s0067-Variable gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Keine s0067-Variable gefunden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</CarfleetUpdateResponse>'."\n";
		exit;
	}
	
	if ( !isset($_POST["s0072"]) )
	{
		echo '<CarfleetUpdateResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Keine s0072-Variable gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Keine s0072-Variable gefunden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</CarfleetUpdateResponse>'."\n";
		exit;
	}
	
	if ( !isset($_POST["s0112"]) )
	{
		echo '<CarfleetUpdateResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Keine s0112-Variable gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Keine s0112-Variable gefunden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</CarfleetUpdateResponse>'."\n";
		exit;
	}
	
	if ( !isset($_POST["s0139"]) )
	{
		echo '<CarfleetUpdateResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Keine s0139-Variable gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Keine s0139-Variable gefunden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</CarfleetUpdateResponse>'."\n";
		exit;
	}
	
	if ( !isset($_POST["s0233"]) )
	{
		echo '<CarfleetUpdateResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Keine s0233-Variable gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Keine s0233-Variable gefunden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</CarfleetUpdateResponse>'."\n";
		exit;
	}
	
	if ( !isset($_POST["s0514"]) )
	{
		echo '<CarfleetUpdateResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Keine s0514-Variable gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Keine s0514-Variable gefunden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</CarfleetUpdateResponse>'."\n";
		exit;
	}
	
	if ( !isset($_POST["s0564"]) )
	{
		echo '<CarfleetUpdateResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Keine s0564-Variable gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Keine s0564-Variable gefunden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</CarfleetUpdateResponse>'."\n";
		exit;
	}
	
	if ( !isset($_POST["s0567"]) )
	{
		echo '<CarfleetUpdateResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Keine s0567-Variable gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Keine s0567-Variable gefunden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</CarfleetUpdateResponse>'."\n";
		exit;
	}
	
	if ( !isset($_POST["s0608"]) )
	{
		echo '<CarfleetUpdateResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Keine s0608-Variable gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Keine s0608-Variable gefunden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</CarfleetUpdateResponse>'."\n";
		exit;
	}
	
	if ( !isset($_POST["s0649"]) )
	{
		echo '<CarfleetUpdateResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Keine s0649-Variable gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Keine s0649-Variable gefunden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</CarfleetUpdateResponse>'."\n";
		exit;
	}
	
	if ( !isset($_POST["s1197"]) )
	{
		echo '<CarfleetUpdateResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Keine s1197-Variable gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Keine s1197-Variable gefunden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</CarfleetUpdateResponse>'."\n";
		exit;
	}
	
	if($_POST["mode"]=="car_new")
	{
		/*$res_check=q("SELECT * FROM shop_carfleet WHERE user_id = ".$_SESSION["id_user"]." AND vehicle_id = ".$_POST["vehicle_id"].";", $dbshop, __FILE__, __LINE__);
		if (mysqli_num_rows($res_check)==0) 
		{*/		
		$res2=q("INSERT INTO shop_carfleet (user_id,
											shop_id,
										    vehicle_id,
										    kbanr,
										    FIN,
										    date_built,
										    c0003,
											c0004,
											c0005,
											c0006,
											s0033,
											s0038,
											s0040,
											s0067,
											s0072,
											s0112,
											s0139,
											s0233,
											s0514,
											s0564,
											s0567,
											s0608,
											s0649,
											s1197,
										    additional,
										    active,
										    firstmod,
										    firstmod_user,
										    lastmod,
										    lastmod_user) VALUES (".$_SESSION["id_user"].",
																  ".$_SESSION["id_shop"].",
																  ".$_POST["vehicle_id"].",
																  '".$_POST["kbanr"]."',
																  '".$_POST["fin"]."',
																  ".$_POST["date_built"].",
																  '".$_POST["c0003"]."',
																  '".$_POST["c0004"]."',
																  '".$_POST["c0005"]."',
																  '".$_POST["c0006"]."',
																  '".$_POST["s0033"]."',
																  '".$_POST["s0038"]."',
																  '".$_POST["s0040"]."',
																  '".$_POST["s0067"]."',
																  '".$_POST["s0072"]."',
																  '".$_POST["s0112"]."',
																  '".$_POST["s0139"]."',
																  '".$_POST["s0233"]."',
																  '".$_POST["s0514"]."',
																  '".$_POST["s0564"]."',
																  '".$_POST["s0567"]."',
																  '".$_POST["s0608"]."',
																  '".$_POST["s0649"]."',
																  '".$_POST["s1197"]."',
																  '".$_POST["additional"]."',
																  1,
																  ".time().",
																  ".$_SESSION["id_user"].",
																  ".time().",
																  ".$_SESSION["id_user"].");", $dbshop, __FILE__, __LINE__);
		$id=mysqli_insert_id($dbshop);
		if($res2==1)
		{
			echo "<CarfleetUpdateResponse>\n";
			echo "<Ack>Success</Ack>\n";
			echo "<id>".$id."</id>\n";
			echo "</CarfleetUpdateResponse>";
			exit;
		}
		else
		{
			echo "<CarfleetUpdateResponse>\n";
			echo "<Ack>Failure</Ack>\n";
			echo "</CarfleetUpdateResponse>";
			exit;
		}
	}
	
	if($_POST["mode"]=="car_change")
	{
		$res_check=q("SELECT * FROM shop_carfleet WHERE id = ".$_POST["customer_vehicle_id"].";", $dbshop, __FILE__, __LINE__);
		if (mysqli_num_rows($res_check)==1) 
		{		
			q("UPDATE shop_carfleet SET user_id=".$_SESSION["id_user"].",
									    vehicle_id=".$_POST["vehicle_id"].",
									    kbanr='".$_POST["kbanr"]."',
									    FIN='".$_POST["fin"]."',
									    date_built=".$_POST["date_built"].",
										c0003='".$_POST["c0003"]."',
										c0004='".$_POST["c0004"]."',
										c0005='".$_POST["c0005"]."',
										c0006='".$_POST["c0006"]."',
										s0033='".$_POST["s0033"]."',
										s0038='".$_POST["s0038"]."',
										s0040='".$_POST["s0040"]."',
										s0067='".$_POST["s0067"]."',
										s0072='".$_POST["s0072"]."',
										s0112='".$_POST["s0112"]."',
										s0139='".$_POST["s0139"]."',
										s0233='".$_POST["s0233"]."',
										s0514='".$_POST["s0514"]."',
										s0564='".$_POST["s0564"]."',
										s0567='".$_POST["s0567"]."',
										s0608='".$_POST["s0608"]."',
										s0649='".$_POST["s0649"]."',
										s1197='".$_POST["s1197"]."',
									    additional='".$_POST["additional"]."',
									    lastmod=".time().",
									    lastmod_user=".$_SESSION["id_user"]." WHERE id = ".$_POST["customer_vehicle_id"].";", $dbshop, __FILE__, __LINE__);
		
			echo "<CarfleetUpdateResponse>\n";
			echo "<Ack>Success</Ack>\n";
			echo "</CarfleetUpdateResponse>";
			exit;
		}
		echo "<CarfleetUpdateResponse>\n";
		echo "<Ack>Failure</Ack>\n";
		echo "</CarfleetUpdateResponse>";
		exit;
	}
	
?>
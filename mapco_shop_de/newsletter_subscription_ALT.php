<?php
	include("config.php");
	include("templates/".TEMPLATE."/header.php");
	include("templates/".TEMPLATE."/cms_leftcolumn.php");
	include("functions/cms_createPassword.php");
	include("functions/cms_send_html_mail.php");
	
	echo '<div id="mid_column">';


	//CONFIRMED
	if (isset($_GET["id"]))
	{
		$results=q("SELECT * FROM cms_newsletter WHERE id=".$_GET["id"]." and confirmed=0;", $dbweb, __FILE__, __LINE__);
		if(mysqli_num_rows($results)>0)
		{
			q("UPDATE cms_newsletter SET confirmed=1, confirmed_stamp=".time()." WHERE id=".$_GET["id"].";", $dbweb, __FILE__, __LINE__);
			echo '<div class="success">';
			echo t("Vielen Dank für Ihre Anmeldung zu unserem Newsletter!");
			echo '<br /><br />';
			echo t("Ab sofort werden Sie regelmäßig über das aktuelle Geschehen bei der MAPCO Autotechnik GmbH informiert.");
			echo '</div>';
		}
		else
		{
			q("UPDATE cms_newsletter SET confirmed=1, confirmed_stamp=".time()." WHERE id=".$_GET["id"].";", $dbweb, __FILE__, __LINE__);
			echo '<div class="warning">';
			echo t("Ihre Anmeldung zu unserem Newsletter wurde bereits bestätigt!");
			echo '</div>';
		}

	}

	//SUBSCRIBE
	if (isset($_POST["subscribe"]))
	{
		if (strpos($_POST["form_mail"], "@")==0 or strpos($_POST["form_mail"], ".")==0) echo '<div class="failure">Sie haben keine gültige E-Mail-Adresse angeben!</div>';
		else
		{
			$results=q("SELECT * FROM cms_newsletter WHERE email='".$_POST["form_mail"]."' LIMIT 1;", $dbweb, __FILE__, __LINE__);
			if(mysqli_num_rows($results)>0)
			{
				echo '<div class="warning">';
				echo 	t("Achtung, wollen Sie sich wirklich vom Newsletter abmelden?");
				echo '	<br /><br />';
				echo '	<form method="post">';
				echo '	<input type="hidden" name="form_mail" value="'.$_POST["form_mail"].'" />';
				echo '	<input type="submit" name="unsubscribe" value="'.t("Abmelden").'" />';
				echo '	</form>';
				echo '</div>';
			}
			else
			{
				q("INSERT INTO cms_newsletter (email, insert_stamp) VALUES ('".$_POST["form_mail"]."', ".time().");", $dbweb, __FILE__, __LINE__);
				$results=q("SELECT * FROM cms_newsletter WHERE email='".$_POST["form_mail"]."' LIMIT 1;", $dbweb, __FILE__, __LINE__);
				$row=mysqli_fetch_array($results);

				$text  = '<p><b>Newsletter Anmeldung</b></p>';
				$text .= '<p>Sie erhalten diese E-Mail weil Ihre Email Adrese auf unserer Webseite '.$_SERVER['HTTP_HOST'].' für den Newsletter eingetragen wurde.</p>';
				$text .= '<a href="http://www.mapco.de/newsletter_subscription.php?id='.$row["id"].'" target="_blank">Newsletter Anmeldung bestätigen</a>';



				send_news_mail($_POST["form_mail"], "Bestätigungsmail zum MAPCO Newsletter", $text);


				echo '<div class="success">';
				echo 'Sie erhalten in Kürze eine E-Mail in der Sie Ihre Anmeldung zu unserem Newsletter bestätigen müssen!';
//				echo '<br /><br />';
//				echo t("Ab sofort werden Sie regelmäßig über das aktuelle Geschehen bei der MAPCO Autotechnik GmbH informiert.");
				echo '</div>';
			}
			
			
		}
		
	}


	//UNSUBSCRIBE
	if (isset($_POST["unsubscribe"]))
	{
		q("DELETE FROM cms_newsletter WHERE email='".$_POST["form_mail"]."' ;", $dbweb, __FILE__, __LINE__);
		echo '<div class="success">'.t("Ihre E-Mail Adresse wurde aus unserem Verteiler gelöscht.").'</div>';
	}

	if (isset($_GET["unsubscribe"]))
	{
		q("DELETE FROM cms_newsletter WHERE email='".$_GET["email"]."' ;", $dbweb, __FILE__, __LINE__);
		echo '<div class="success">'.t("Ihre E-Mail Adresse wurde aus unserem Verteiler gelöscht.").'</div>';
	}
	
	
	//FORM
	if ($_GET["id_user"]>0)
	{
		$results=q("SELECT * FROM cms_users WHERE id_user=".$_GET["id_user"]." LIMIT 1;", $dbweb, __FILE__, __LINE__);
		if (mysqli_num_rows($results)>0)
		{
			echo '<div class="warning">';
			echo 	t("Achtung, wollen Sie sich wirklich vom Newsletter abmelden?");
			echo '	<br /><br />';
			echo '	<form method="post">';
			echo '	<input type="hidden" name="form_user_id" value="'.$_GET["id_user"].'" />';
			echo '	<input type="submit" name="unsubscribe" value="'.t("Abmelden").'" />';
			echo '	</form>';
			echo '</div>';
		}
		else
		{
			echo '<div class="failure">'.t("Benutzer nicht gefunden.").'</div>';
		}
		{
		}
	}
	
	echo '</div>';
	include("templates/".TEMPLATE."/cms_rightcolumn.php");
	include("templates/".TEMPLATE."/footer.php");
?>


<?php
	include("config.php");
	$login_required=true;
	include("templates/".TEMPLATE_BACKEND."/header.php");


	//PATH
	echo '<p>';
	echo '<a href="backend_index.php">Backend</a>';
	echo ' > Personalverwaltung';
	echo '</p>';

	echo '<h1>Personalverwaltung</h1>';
	echo '<ul class="quickaccess">';
	show_tree(4, true);
	echo '</ul>';
	
	
	//CREATE TABLE hr_employees
	q("
		CREATE TABLE IF NOT EXISTS `hr_employees` (
		  `id_employee` int(11) NOT NULL AUTO_INCREMENT,
		  `firstname` tinytext CHARACTER SET utf8 NOT NULL,
		  `middlename` tinytext CHARACTER SET utf8 NOT NULL,
		  `lastname` tinytext CHARACTER SET utf8 NOT NULL,
		  `position` tinytext CHARACTER SET utf8 NOT NULL,
		  `department` tinytext CHARACTER SET utf8 NOT NULL,
		  `street` tinytext CHARACTER SET utf8 NOT NULL,
		  `street_nr` tinytext CHARACTER SET utf8 NOT NULL,
		  `zip` tinytext CHARACTER SET utf8 NOT NULL,
		  `city` tinytext CHARACTER SET utf8 NOT NULL,
		  `country` tinytext CHARACTER SET utf8 NOT NULL,
		  `phone` tinytext CHARACTER SET utf8 NOT NULL,
		  `fax` tinytext CHARACTER SET utf8 NOT NULL,
		  `mobile` tinytext CHARACTER SET utf8 NOT NULL,
		  `mail` tinytext CHARACTER SET utf8 NOT NULL,
		  `user_id` int(11) NOT NULL DEFAULT '0',
		  `firstmod` int(11) NOT NULL,
		  `firstmod_user` int(11) NOT NULL,
		  `lastmod` int(11) NOT NULL,
		  `lastmod_user` int(11) NOT NULL,
		  PRIMARY KEY (`id_employee`)
		) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1234591 ;
	", $dbweb, __FILE__, __LINE__);
	
	
	
	
	
	
	
	
	
	include("templates/".TEMPLATE_BACKEND."/footer.php");
?>

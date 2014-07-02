<?php
	///äöüÄÖÜ UTF-8
	if (!function_exists("motorart"))
	{
		function motorart($motart)
		{
			if ($motart==1) return("Benziner");
			elseif ($motart==2) return("Diesel");
		}
	}
?>
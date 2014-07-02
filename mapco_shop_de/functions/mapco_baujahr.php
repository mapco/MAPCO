<?php
	///äöüÄÖÜ UTF-8
	if (!function_exists("baujahr"))
	{
		function baujahr($baujahr)
		{
			if ($baujahr=='000000') return("");
			else
			{
				return (substr($baujahr, 4, 2).'/'.substr($baujahr, 0, 4));
			}
		}
	}
?>
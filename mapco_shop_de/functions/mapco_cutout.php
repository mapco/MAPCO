<?php
	///äöüÄÖÜ UTF-8
	if (!function_exists("cutout"))
	{
		function cutout($text, $from, $to)
		{
			while( ($start = strpos($text, $from)) !== false )
			{
				$end=strpos($text, $to, $start)+strlen($to);
				$text2=substr($text, 0, $start);
				$text2.=substr($text, $end, strlen($text));
				$text=$text2;
			}
			return($text);
		}
	}
?>
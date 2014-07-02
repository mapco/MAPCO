<?php
	///äöüÄÖÜ UTF-8
	if (!function_exists("remove_element"))
	{
		function remove_element($elements, $number)
		{
			$i=0;
			$output=array();
			foreach($elements as $element)
			{
				if ($i!=$number) $output[]=$element;
				$i++;
			}
			return($output);
		}
	}
?>
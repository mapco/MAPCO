<?php
	///äöüÄÖÜ UTF-8
	if (!function_exists("createPassword"))
	{
		function createPassword($length)
		{
			$chars = "1234567890abcdefghijkmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
			$i = 0;
			$password = "";
			while ($i <= $length) {
				$password .= $chars{mt_rand(0,strlen($chars)-1)};
				$i++;
			}
			return $password;
		}
	}
?>
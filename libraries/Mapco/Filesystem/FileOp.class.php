<?php
	/**************************************************
	***	Class: MFileOp								***
	***	Namespace: Mapco.Filesystem.FileOp			***
	*** Author: C.Haendler <chaendler(at)mapco.de> 	*** 
	***	Version: 1.0  		30/06/14/ 				***
	***	Last mod: 30/06/14							***
	***************************************************/
		
	/*
	*
	*
	*	abstract class for fileoperations
	*/
	
	abstract class MFileOp {
		
			// removes files and non-empty directories
			/*
			*
			*	MLoader::import('Mapco.Filesystem.FileOp');
			*	MFileOp::rdelete('PATHTOFILE');
			*/
			
			public static function rdelete($directory) 
			{
				if (isset($directory)) 
				{
					  if (is_dir($directory)) 
					  {
						$files = scandir($directory);
						foreach ($files as $file)
						if ($file != "." && $file != "..") MFileOp::rdelete("$directory/$file");
						rmdir($directory);
					  } else if (file_exists($directory))
					  {
						   unlink($directory);
					  }
				}
			}
			
			// copies files and non-empty directories
			/*
			*
			*	MLoader::import('Mapco.Filesystem.FileOp');
			*	MFileOp::rcopy('SOURCEPATH', 'DESTINATION');
			*/
			public static function rcopy($src, $dst) 
			{
				if (file_exists($dst)) 
				{ 
					MFileOp::rdelete($dst);
				}
				
				if (is_dir($src)) 
				{
					mkdir($dst);
					$files = scandir($src);
					foreach ($files as $file) 
					{
						if ($file != "." && $file != "..") 
						{
							MFileOp::rcopy("$src/$file", "$dst/$file"); 
						}
					}
				} else if (file_exists($src)) 
				{
					copy($src, $dst);	
				}
			}
				
	}
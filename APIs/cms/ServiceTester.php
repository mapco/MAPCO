<?php

	$xml ='';

	/*************************
	********** SOA 2 *********
	*************************/
	
	$required=array("step" => "numeric", "api" => "textNN", "file" => "textNN");
	
	check_man_params($required);
	
	$filename = "../../APIs/".$_POST['api'] . "/" . $_POST['file'].".php";
	
	$step = $_POST['step'];
	
	if ( $step == 1 )
	{	
		$xml='';
		$content=file_get_contents($filename);
		
		$arrcontent = explode('$_POST[',$content);

		for($x=1;$x<sizeof($arrcontent);$x++)
		{	
			$arrposts = explode(']',$arrcontent[$x],2);
			
			if(strpos($arrposts[0],' ') == FALSE )
			{
				$post = substr($arrposts[0], 1, strlen($arrposts[0])-2);
				$post_key_value[$post] = $post;
			}
		}
		
		foreach($post_key_value as $value)
		{
			$xml .= '<post>' . $value . '</post>';
		}
		
		echo $xml;
	}
	elseif( $step == 2 )
	{
		if(isset($_POST['post_data_string']))
		{	
			$post_data_arr = explode(',',$_POST['post_data_string']);
			$post_keys_arr = explode(',',$_POST['post_keys']);
			
			for($x=0; $x<sizeof($post_data_arr);$x++)
			{
				$_POST[$post_keys_arr[$x]] = $post_data_arr[$x];
			}
		}
		require_once($filename);	
	}
?>
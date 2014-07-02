<?php
	include("config.php");
	
function downloadFile( $fullPath ){ 

  // Must be fresh start 
  if( headers_sent() ) 
    die('Headers Sent'); 

  // Required for some browsers 
  if(ini_get('zlib.output_compression')) 
    ini_set('zlib.output_compression', 'Off'); 

  // File Exists? 
  if( file_exists($fullPath) ){ 
    
    // Parse Info / Get Extension 
    $fsize = filesize($fullPath); 
    $path_parts = pathinfo($fullPath); 
    $ext = strtolower($path_parts["extension"]); 
    
    // Determine Content Type 
    switch ($ext) { 
      case "pdf": $ctype="application/pdf"; break; 
      case "exe": $ctype="application/octet-stream"; break; 
      case "zip": $ctype="application/zip"; break; 
      case "doc": $ctype="application/msword"; break; 
      case "xls": $ctype="application/vnd.ms-excel"; break; 
      case "ppt": $ctype="application/vnd.ms-powerpoint"; break; 
      case "gif": $ctype="image/gif"; break; 
      case "png": $ctype="image/png"; break; 
      case "jpeg": 
      case "jpg": $ctype="image/jpg"; break; 
      default: $ctype="application/force-download"; 
    } 

    header("Pragma: public"); // required 
    header("Expires: 0"); 
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
    header("Cache-Control: private",false); // required for certain browsers 
    header("Content-Type: $ctype"); 
    header("Content-Disposition: attachment; filename=\"".basename($fullPath)."\";" ); 
    header("Content-Transfer-Encoding: binary"); 
    header("Content-Length: ".$fsize); 
    ob_clean(); 
    flush(); 
    readfile( $fullPath ); 

  } else 
    die('File Not Found'); 
} 


if ($_GET["id_file"]>0)
	{
		$query="SELECT * FROM cms_files WHERE id_file=".$_GET["id_file"].";";
		$results2=q($query, $dbweb, __FILE__, __LINE__);
		if ( mysqli_num_rows($results2)==0 )
		{
				header("HTTP/1.0 404 Not Found");
				exit;
		}
		$row2=mysqli_fetch_array($results2);
		
		$dir=floor(bcdiv($row2["id_file"], 1000));
		$file='files/'.$dir.'/'.$row2["id_file"].'.'.$row2["extension"];
		$download_name = basename($row2["filename"].'.'.$row2["extension"]);
		if (file_exists($file))
		{
		    if ( strpos($download_name, ".jpg") !== false ) header('Content-Type: image/jpeg');
			elseif (strpos($download_name, ".pdf") !== false ) header('Content-Type: application/pdf');
			else header('Content-Type: application/octet-stream');
			header("Content-Disposition: attachment; filename=\"".$download_name."\";" ); 
			readfile($file);
		}
	}

?>
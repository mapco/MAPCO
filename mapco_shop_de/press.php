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


if ($_GET["id_press"]>0)
	{
		$query="SELECT * FROM cms_press WHERE id_press=".$_GET["id_press"].";";
		$results=q($query, $dbweb, __FILE__, __LINE__);
		$row=mysqli_fetch_array($results);
		$dir=floor(bcdiv($row["file_id"], 1000));
		
		$query="SELECT * FROM cms_files WHERE id_file=".$row["file_id"].";";
		$results2=q($query, $dbweb, __FILE__, __LINE__);
		$row2=mysqli_fetch_array($results2);
		
		$file='files/'.$dir.'/'.$row["file_id"].'.'.$row2["extension"];
		$download_name = basename($file);
		if (file_exists($file))
		{
		    if (eregi(".jpg", $download_name)) header('Content-Type: image/jpeg');
			elseif (eregi(".pdf", $download_name)) header('Content-Type: application/pdf');
			else header('Content-Type: application/octet-stream');
			readfile($file);
		}
	}

?>
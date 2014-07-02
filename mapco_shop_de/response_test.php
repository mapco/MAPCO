<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Server-Geschwidnigkeitstest</title>





</head>

<body onload="start();">

        <script type="text/javascript">
         <!--
            function ajax(url, synchronous)
			{
				var req = null;

                try
				{
                    req = new XMLHttpRequest();
                }
                catch (ms)
				{
                    try
					{
                        req = new ActiveXObject("Msxml2.XMLHTTP");
                    } 
                    catch (nonms)
					{
                        try
						{
                            req = new ActiveXObject("Microsoft.XMLHTTP");
                        } 
                        catch (failed)
						{
                            req = null;
                        }
                    }  
                }

                if (req == null) alert("Error creating request object!");
                  
				req.open("GET", url, synchronous);
				
                //Beim abschliessen des request wird diese Funktion ausgeführt
                req.onreadystatechange = function()
				{            
                    switch(req.readyState) {
                            case 4:
                            if(req.status!=200)
							{
                                alert("ERROR #"+req.status); 
                            }
							else
							{
//								return req.responseText;
                            }
                            break;
                    
                            default:
                                return false;
                            break;     
                        }
                    };
  
                  req.setRequestHeader("Content-Type",
                                      "application/x-www-form-urlencoded");
                req.send(null);
				return(req.responseText);
            }


			//read GET parameters
			function html_entity_decode(str)
			{
				var ta=document.createElement("textarea");
				ta.innerHTML=str.replace(/</g,"&lt;").replace(/>/g,"&gt;");
				return ta.value;
			}
			
			function get_GET_params()
			{
				var GET = new Array();
				if(location.search.length > 0)
				{
					var get_param_str = location.search.substring(1, location.search.length);
					var get_params = get_param_str.split("&");
					for(j = 0; j < get_params.length; j++)
					{
						var key_value = get_params[j].split("=");
						if(key_value.length == 2)
						{
							var key = unescape(key_value[0]).split("+").join(" ");
							var value = unescape(key_value[1]).split("+").join(" ");
							GET[key] = value;
						}
					}
				}
				return(GET);
			}
 
			function start()
			{
				var starttime = new Date().getTime();
				document.getElementById("status").innerHTML="Starte Serveranfrage...";
				var response=ajax('response_test2.php', false);
				document.getElementById("status").innerHTML="Serveranfrage abgeschlossen.";
				var stoptime = new Date().getTime();
				document.getElementById("details").innerHTML="Server-Antwortzeit: "+(stoptime-starttime)/1000+"s";
				document.getElementById("details").innerHTML+=response;
            }
			
			function scan_dir(dir)
			{
				document.getElementById("status").innerHTML="Scanne Verzeichnis "+dir;
				var response=ajax('malware2.php?dir='+encodeURIComponent(dir), false);
				document.getElementById("details").innerHTML=document.getElementById("details").innerHTML+response;
            }
				
         //-->
        </script>





<?php

	
	
	echo '<div id="status" style="width:600px; margin:5px; border:2px solid grey; padding:5px;"></div>';
	echo '<div id="details" style="width:600px; margin:5px; border:2px solid grey; padding:5px;"></div>';
	
?>
</body>
</html>

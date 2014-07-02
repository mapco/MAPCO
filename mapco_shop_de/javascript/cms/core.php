<?php
	include("../../config.php");
	header('Content-type: text/javascript');
	
	//make dreamweaver highlight javascript
	if(true == false) { ?> <script type="text/javascript"> <?php }

/*
	//DEACTIVATED FOR SECURITY REASONS
	//forward constants to javascript
	$constants=get_defined_constants(true);
	$keys=array_keys($constants["user"]);
	for($i=0; $i<sizeof($keys); $i++)
	{
		echo 'var '.$keys[$i].'=\''.$constants["user"][$keys[$i]].'\';'."\n";
	}
*/

/*
	//DEACTIVATED FOR SECURITY REASONS
	//forward session values to javascript
	if( isset($_SESSION) )
	{
		echo 'var $_SESSION=new Array();'."\n";
		$keys=array_keys($_SESSION);
		for($i=0; $i<sizeof($keys); $i++)
		{
			echo '$_SESSION["'.$keys[$i].'"]=\''.$_SESSION[$keys[$i]].'\';'."\n";
		}
	}
*/

?>

	function isset($var)
	{
		return( typeof $var !== "undefined" )
	}

	function textlength($input, $output, $maxlength)
	{
		if( $maxlength==0 ) return;
		var $text=$("#"+$input).val();
		if( $text.length<=$maxlength )
		{
			$("#"+$output).css("color", "#00dd00");
			$("#"+$output).html("Noch "+($maxlength-$text.length)+" Zeichen");
		}
		else
		{
			$("#"+$output).css("color", "#ff0000");
			$("#"+$output).html(($text.length-$maxlength)+" Zeichen zu lang");
		}
	}


	function textwidth($input, $output, $maxwidth, $fontsize)
	{
		if( $maxwidth==0 ) return;
		if( $("#textwidth_test").length == 0 ) $("body").append('<div id="textwidth_test" style="position:absolute; visibility:hidden; height:auto; width:auto; font-family:Arial; font-size:'+$fontsize+'px"></div>');
		var $text=$("#"+$input).val();
		$("#textwidth_test").html($text);
		var $width = ($("#textwidth_test").width() + 1);
		if( $width<=$maxwidth )
		{
			$("#"+$output).css("color", "#00dd00");
			$("#"+$output).html("Noch "+($maxwidth-$width)+" Pixel");
		}
		else
		{
			$("#"+$output).css("color", "#ff0000");
			$("#"+$output).html(($width-$maxwidth)+" Pixel zu lang");
		}
	}


	function get_values($element, $prefix)
	{
		var $data=new Object();
		var $elements=$element.split(", ");
		for($i=0; $i<$elements.length; $i++)
		{
			var $get=$($elements[$i]+"[id^='"+$prefix+"']");
			$get.each(function()
			{
				//substring is better than replace!!
				var $name=$(this).attr('id').replace($prefix, "");
				var $value=$(this).val();
				$data[$name]=$value;
				return($data);
			});
		}
		return($data);
	}

	function isValidEmailAddress(emailAddress)
	{
		var pattern = new RegExp(/^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?$/i);
		return pattern.test(emailAddress);
	};

	function is_numeric(mixed_var) {
	  var whitespace =
		" \n\r\t\f\x0b\xa0\u2000\u2001\u2002\u2003\u2004\u2005\u2006\u2007\u2008\u2009\u200a\u200b\u2028\u2029\u3000";
	  return (typeof mixed_var === 'number' || (typeof mixed_var === 'string' && whitespace.indexOf(mixed_var.slice(-1)) === -
		1)) && mixed_var !== '' && !isNaN(mixed_var);
	}


	var $wait=false;

	function ajax(url, synchronous)
	{
		var req = null;
		var wait_dialog_timer;
	
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
	//					return req.responseText;
					}
					break;
			
					default:
						return false;
					break;     
				}
			};
	
		  req.setRequestHeader("If-Modified-Since", "Sat, 1 Jan 2000 00:00:00 GMT");
		  req.setRequestHeader("Content-Type",
							  "application/x-www-form-urlencoded");
		req.send(null);
		return req.responseText;
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

	function clean()
	{
		document.getElementById("tooltip").innerHTML='';
		document.getElementById("tooltip").style.display='none';
	}
	
	//Item Search
	/*
	function suche(wert)
	{
		if (wert.length>2)
		{
			suche2(wert);
		}
		else
		{
			document.getElementById("results").innerHTML='';
			document.getElementById("results").style.visibility='hidden';
		}
	}
		
	function suche2(wert)
	{
		var get=get_GET_params();
		var response=ajax('<?php echo PATH; ?>ajax/item_search.php?wert='+encodeURIComponent(wert)+'&lang='+get["lang"], false);
		$("#results").html(response);
		$("#results").show();
	}
	*/

	function suche3()
	{
		$("#results").html("");
		document.getElementById("results").style.visibility="hidden";
	}
	
	function header_search(evt)
	{
		var text=$("#search").val();
		var e = evt || window.event;
		var code = e.keyCode || e.which;
		if( code === 13 )
		{
			window.location.href="<?php echo PATHLANG; ?>oe-nummern-suche/"+text+"/";
		}
		else
		{
			$("#results").html("");
			document.getElementById("results").style.visibility="hidden";
			setTimeout("header_search2('"+text+"')", 300);
		}
	}
	
	function header_search2(text)
	{
		if (text==undefined) text="";
		var text2=document.getElementById("search").value;
		if (text!="" && text2==text)
		{
			$.post("<?php echo PATH; ?>soa/", { API:"shop", Action:"ShopSearch", wert:text }, function($data)
			{
				if($("#search").val()==text)
				{
					$("#results").html($data);		
					document.getElementById("results").style.visibility="visible";
				}
			});
		}
	}

	function car_search_close()
	{
		$("#car_search").html("");
		document.getElementById("car_search").style.visibility="hidden";
		document.getElementById("results").style.top="54px";
	}

	function car_search()
	{
		$.post("<?php echo PATH; ?>soa/", { API:"shop", Action:"ShopCarSearch" }, function($data)
		{
			$("#car_search").html($data);		
			document.getElementById("car_search").style.visibility="visible";
			document.getElementById("results").style.top="192px";
		});
	}


	function cart_add(item_id, vehicle_save)
	{
		if(typeof vehicle_save == 'undefined') vehicle_save = 0;
		var amount=$("#article"+item_id).val();
		
		wait_dialog_show();
		$.post("<?php echo PATH; ?>soa/", { API:"shop", Action:"CartAdd", id_item:item_id, amount:amount, vehicle_save:vehicle_save }, function($data)
		{
			wait_dialog_hide();
	//		try { $xml = $($.parseXML($data)); } catch ($err) { show_status($err.message); return; }
	//		if ( $xml.find("Ack").text()!="Success" ) { show_status2($data); return; }
			
			cart_update();
			alert($data);
		});		
	}

	function cart_add_enter(id_item, e)
	{
		if(!e) e = window.event;
		if(e.keyCode == 13) cart_add(id_item);
	}


	function note_add(item_id)
	{
		alert("noch nicht verfügbar");
	//				var response=ajax('<?php echo PATH; ?>ajax/cart_add.php?id_item='+encodeURIComponent(item_id), false);
	//				cart_update();
		return(false);
	}

	function cart_update()
	{
		var get=get_GET_params();
		$.post("<?php echo PATH; ?>soa/", { API:"shop", Action:"CartUpdate",lang:get["lang"] }, 
		function(data) { 
		document.getElementById("cart").innerHTML=data;
		document.getElementById("cart").style.visibility='visible';
		} );		
	}
	
	function cart_clear()
	{
		if (confirm('Warenkorb wirklich leeren?'))
		{
			$.post("<?php echo PATH; ?>soa/", { API:"shop", Action:"CartClear" }, function(data) { cart_update(); } );		
		}
	}
	
	function cart_clear2()
	{
		$.post("<?php echo PATH; ?>soa/", { API:"shop", Action:"CartClear" }, function(data) { cart_update(); } );		
	}
	
	function mouse_pos(e) 
	{
		if(!e) e = window.event;
		var body = (window.document.compatMode && window.document.compatMode == "CSS1Compat") ? 
		window.document.documentElement : window.document.body;
		return {
		// Position im Dokument
		top: e.pageY ? e.pageY : e.clientY + body.scrollTop - body.clientTop,
		left: e.pageX ? e.pageX : e.clientX + body.scrollLeft  - body.clientLeft
	 
		};
	}

	function getMouseXY(e) 
	{
		if(!e) e = window.event;
		var body = (window.document.compatMode && window.document.compatMode == "CSS1Compat") ? 
		window.document.documentElement : window.document.body;
		return {
		// Position im Dokument
		top: e.pageY ? e.pageY : e.clientY + body.scrollTop - body.clientTop,
		left: e.pageX ? e.pageX : e.clientX + body.scrollLeft  - body.clientLeft
	 
		};
	}


	function showhide(id)
	{
		var display=document.getElementById(id).style.display;
		if (display=="block")
		{
			document.getElementById(id).style.display="none";
			document.getElementById("cat"+id).style.backgroundImage='url(<?php echo PATH; ?>images/icons/16x16/right.png)';
		}
		else
		{
			document.getElementById(id).style.display="block";
			document.getElementById("cat"+id).style.backgroundImage='url(<?php echo PATH; ?>images/icons/16x16/down.png)';
		}
	}
	
	function show(id)
	{
		document.getElementById(id).style.display="block";
	}
	
	function hide(id)
	{
		document.getElementById(id).style.display="none";
	}

	function play(file)
	{
		document.getElementById("player").setAttribute("src", "<?php echo PATH; ?>player/videos/"+file);
		document.getElementById("player").setAttribute("autoplay", "");
	}

	function popup_window(width, height, title, text)
	{
		var popup=document.createElement("div");
		var id="popup"+Math.round(Math.random()*1000);
		popup.setAttribute("id", id);
		popup.setAttribute("class", "popup");
		popup.style.border="10px solid black";
		
		var div=document.createElement("div");
		div.style.width=width+"px";
		div.style.height=height+"px";
		div.style.marginLeft='-'+Math.round(width/2)+'px';
		div.style.marginTop='-'+Math.round(height/2)+'px';
		
		var html='<table style="position:absolute; left:50%; top:50%; width:'+width+'px; height:'+height+'px; margin-left:-'+Math.round(width/2)+'px; margin-top:-'+Math.round(height/2)+'px; background:#ffffff;">';
		html+='<tr><th style="height:20px;">'+title+'<img style="margin:0; border:0; padding:0; cursor:pointer; display:inline; float:right;" src="<?php echo PATH; ?>images/icons/16x16/remove.png" onclick="document.getElementById(\''+id+'\').style.display=\'none\';" alt="Schließen" title="Schließen" /></th></tr>';
		html+='<tr><td style="text-align:center;">'+text+'</td></tr></table>';
		popup.innerHTML=html;
		
	//	popup.appendChild(div);
		document.body.appendChild(popup);
		popup.focus();
		return(id);
	}
	
	function popup(url, width, height)
	{
		var response=ajax(url, false);
		var popup=document.createElement("div");
		popup.setAttribute("class", "popup");
		
		var div=document.createElement("div");
		div.innerHTML=response;
		div.style.width=width+"px";
		div.style.height=height+"px";
		div.style.marginLeft='-'+Math.round(width/2)+'px';
		div.style.marginTop='-'+Math.round(height/2)+'px';
		popup.appendChild(div);
		
		document.body.appendChild(popup);
	}
	
	function popup_text(text, width, height)
	{
		var response=text;
		var popup=document.createElement("div");
		var id=Math.round(Math.random()*1000);
		popup.setAttribute("id", "popup"+id);
		popup.setAttribute("class", "popup");
		popup.style.border="10px solid black";
		
		var div=document.createElement("div");
		div.innerHTML=response;
		div.style.width=width+"px";
		div.style.height=height+"px";
		div.style.marginLeft='-'+Math.round(width/2)+'px';
		div.style.marginTop='-'+Math.round(height/2)+'px';
		popup.appendChild(div);
		
		document.body.appendChild(popup);
		popup.focus();
		return(id);
	}
	
	function popup_id(id, width, height)
	{
		var response=document.getElementById(id).innerHTML;
		var popup=document.createElement("div");
		var id=Math.round(Math.random()*1000);
		popup.setAttribute("id", "popup"+id);
		popup.setAttribute("class", "popup");
		popup.style.border="10px solid black";
		
		var div=document.createElement("div");
		div.innerHTML=response;
		div.style.width=width+"px";
		div.style.height=height+"px";
		div.style.marginLeft='-'+Math.round(width/2)+'px';
		div.style.marginTop='-'+Math.round(height/2)+'px';
		popup.appendChild(div);
		
		document.body.appendChild(popup);
		popup.focus();
		return(id);
	}

	function delay(prmSec)
	{
		prmSec *= 1000;
		
		var eDate = null;
		var eMsec = 0;
		
		var sDate = new Date();
		var sMsec = sDate.getTime();
		
		do {
			eDate = new Date();
			eMsec = eDate.getTime();
			
		} while ((eMsec-sMsec)<prmSec);
	}

	function show_status(text)
	{
		$("#status").show("puff", {}, 250);
		$("#status").html(text+' <a href="#" onclick="hide_status();">Schließen</a>');
	}

	function show_status2(text)
	{
		$("#status").show("puff", {}, 250);
		$("#status").html('<textarea style="width:500px; height:400px;">'+text+'</textarea> <a href="#" onclick="hide_status();">Schließen</a>');
	}

	function hide_status()
	{
		$("#status").hide("puff", {}, 250);
	}

	function wait_dialog_show($status, $percent)
	{
		$wait=true;
	    if( typeof $status == "undefined" ) $status="Bitte warten...";
		wait_dialog_timer=setTimeout("wait_dialog_show2('"+$status+"', "+$percent+");", 500);
	}

	function wait_dialog_show2($status, $percent)
	{
		if( $wait )
		{
			if( $("#wait_dialog").length == 0 )
			{
				//create wait dialog
				var $html='';
				$html += '<div id="wait_dialog" style="display:none;">';
				$html += '</div>';
				$("body").append($html);

				//create wait dialog progressbar1
				var id_progressbar="wait_dialog_progressbar1";
				$("#wait_dialog").append('<div id="'+id_progressbar+'_wrapper" style="position:relative;"></div>');
				$("#"+id_progressbar+"_wrapper").append('<div id="'+id_progressbar+'" style="width:100%;"></div>');
				$("#"+id_progressbar+"_wrapper").append('<div id="'+id_progressbar+'_status" style="width:100%; position:absolute; left:0; top:5px; text-align:center;"></div>');
				$(function() {
					$("#"+id_progressbar).progressbar({
						value: false
					});
				});

				//create wait dialog progressbar2 and hide
				$("#wait_dialog").append('<br style="clear:both;" />');
				var id_progressbar="wait_dialog_progressbar2";
				$("#wait_dialog").append('<div id="'+id_progressbar+'_wrapper" style="position:relative; display:none;"></div>');
				$("#"+id_progressbar+"_wrapper").append('<div id="'+id_progressbar+'" style="width:100%;"></div>');
				$("#"+id_progressbar+"_wrapper").append('<div id="'+id_progressbar+'_status" style="width:100%; position:absolute; left:0; top:5px; text-align:center;"></div>');
				$(function() {
					$("#"+id_progressbar).progressbar({
						value: false
					});
				});
//				$(id).append('<br style="clear:both;" />');
/*
				$html += '	<p id="wait_dialog_status"></p><br style="clear:both;"/>';
				$html += '	<img src="<?php echo PATH ?>images/icons/loaderb64.gif" style="margin:0px 0px 0px 30px" alt="Bitte warten!" />';
*/
			}
			
			//show status1
			$("#wait_dialog_progressbar1_status").html($status);
			//show progress1
			if( $percent == null ) $percent=false;
			$(function() {
				$("#wait_dialog_progressbar1").progressbar({
					value: $percent
				});
			});

			$("#wait_dialog").dialog
			({	closeText:"Fenster schließen",
				hide: { effect: 'drop', direction: "up" },
				modal:true,
				resizable:false,
				show: { effect: 'drop', direction: "up" },
				title:$status,
				width:400
			});
		}
	}

	function wait_dialog_hide()
	{
		$wait=false;
		clearTimeout(wait_dialog_timer);
		if( $("#wait_dialog").length > 0) $("#wait_dialog").dialog("close");
	}

	function print_r(arr, level)
	{
		var dumped_text = "";
		if (!level) level = 0;
	 
		//The padding given at the beginning of the line.
		var level_padding = "";
		var bracket_level_padding = "";
	 
		for (var j = 0; j < level + 1; j++) level_padding += "    ";
		for (var b = 0; b < level; b++) bracket_level_padding += "    ";
	 
		if (typeof(arr) == 'object') { //Array/Hashes/Objects 
			dumped_text += "Array\n";
			dumped_text += bracket_level_padding + "(\n";
			for (var item in arr) {
	 
				var value = arr[item];
	 
				if (typeof(value) == 'object') { //If it is an array,
					dumped_text += level_padding + "[" + item + "] => ";
					dumped_text += print_r(value, level + 2);
				} else {
					dumped_text += level_padding + "[" + item + "] => " + value + "\n";
				}
	 
			}
			dumped_text += bracket_level_padding + ")\n\n";
		} else { //Stings/Chars/Numbers etc.
			dumped_text = "===>" + arr + "<===(" + typeof(arr) + ")";
		}
	 
		return dumped_text;
	 
	}
    
    function list_sort(id, offset)  //Sortiert Listboxen-Einträge alphabetisch(offset optional) unter Beibehaltung der values
	{ 
		var lb = document.getElementById(id); 
		arrTexts = 	  new Array(); 
		arrValues =   new Array(); 
		arrOldTexts = new Array(); 
		
		if (!offset) offset = 0;
		
		for(i=offset; i<lb.length; i++) 
		{ 
			arrTexts[i] = 	 lb.options[i].text; 
			arrValues[i] = 	 lb.options[i].value; 
			arrOldTexts[i] = lb.options[i].text; 
		} 
		
		arrTexts.sort(function (a, b) {
			return a.toLowerCase().localeCompare(b.toLowerCase());
		}); 
		
		for(i=offset; i<lb.length; i++) 
		{ 
			lb.options[i].text = arrTexts[i-offset]; 
			for(j=offset; j<lb.length; j++) 
			{ 
				if (arrTexts[i-offset] == arrOldTexts[j]) 
				{ 
					lb.options[i].value = arrValues[j]; 
					j = lb.length; 
				} 
			} 
		} 
	}

	function md5(string) 
	{
		function RotateLeft(lValue, iShiftBits) 
		{
			return (lValue<<iShiftBits) | (lValue>>>(32-iShiftBits));
		}
	 
		function AddUnsigned(lX,lY) 
		{
			var lX4,lY4,lX8,lY8,lResult;
			lX8 = (lX & 0x80000000);
			lY8 = (lY & 0x80000000);
			lX4 = (lX & 0x40000000);
			lY4 = (lY & 0x40000000);
			lResult = (lX & 0x3FFFFFFF)+(lY & 0x3FFFFFFF);
			if (lX4 & lY4) {
				return (lResult ^ 0x80000000 ^ lX8 ^ lY8);
			}
			if (lX4 | lY4) {
				if (lResult & 0x40000000) {
					return (lResult ^ 0xC0000000 ^ lX8 ^ lY8);
				} else {
					return (lResult ^ 0x40000000 ^ lX8 ^ lY8);
				}
			} else {
				return (lResult ^ lX8 ^ lY8);
			}
		}
	 
		function F(x,y,z) { return (x & y) | ((~x) & z); }
		function G(x,y,z) { return (x & z) | (y & (~z)); }
		function H(x,y,z) { return (x ^ y ^ z); }
		function I(x,y,z) { return (y ^ (x | (~z))); }
	 
		function FF(a,b,c,d,x,s,ac) 
		{
			a = AddUnsigned(a, AddUnsigned(AddUnsigned(F(b, c, d), x), ac));
			return AddUnsigned(RotateLeft(a, s), b);
		};
	 
		function GG(a,b,c,d,x,s,ac) 
		{
			a = AddUnsigned(a, AddUnsigned(AddUnsigned(G(b, c, d), x), ac));
			return AddUnsigned(RotateLeft(a, s), b);
		};
	 
		function HH(a,b,c,d,x,s,ac) 
		{
			a = AddUnsigned(a, AddUnsigned(AddUnsigned(H(b, c, d), x), ac));
			return AddUnsigned(RotateLeft(a, s), b);
		};
	 
		function II(a,b,c,d,x,s,ac) 
		{
			a = AddUnsigned(a, AddUnsigned(AddUnsigned(I(b, c, d), x), ac));
			return AddUnsigned(RotateLeft(a, s), b);
		};
	 
		function ConvertToWordArray(string) 
		{
			var lWordCount;
			var lMessageLength = string.length;
			var lNumberOfWords_temp1=lMessageLength + 8;
			var lNumberOfWords_temp2=(lNumberOfWords_temp1-(lNumberOfWords_temp1 % 64))/64;
			var lNumberOfWords = (lNumberOfWords_temp2+1)*16;
			var lWordArray=Array(lNumberOfWords-1);
			var lBytePosition = 0;
			var lByteCount = 0;
			while ( lByteCount < lMessageLength ) {
				lWordCount = (lByteCount-(lByteCount % 4))/4;
				lBytePosition = (lByteCount % 4)*8;
				lWordArray[lWordCount] = (lWordArray[lWordCount] | (string.charCodeAt(lByteCount)<<lBytePosition));
				lByteCount++;
			}
			lWordCount = (lByteCount-(lByteCount % 4))/4;
			lBytePosition = (lByteCount % 4)*8;
			lWordArray[lWordCount] = lWordArray[lWordCount] | (0x80<<lBytePosition);
			lWordArray[lNumberOfWords-2] = lMessageLength<<3;
			lWordArray[lNumberOfWords-1] = lMessageLength>>>29;
			return lWordArray;
		};
	 
		function WordToHex(lValue) 
		{
			var WordToHexValue="",WordToHexValue_temp="",lByte,lCount;
			for (lCount = 0;lCount<=3;lCount++) {
				lByte = (lValue>>>(lCount*8)) & 255;
				WordToHexValue_temp = "0" + lByte.toString(16);
				WordToHexValue = WordToHexValue + WordToHexValue_temp.substr(WordToHexValue_temp.length-2,2);
			}
			return WordToHexValue;
		};
	 
		function Utf8Encode(string) 
		{
			string = string.replace(/\r\n/g,"\n");
			var utftext = "";
	 
			for (var n = 0; n < string.length; n++) {
	 
				var c = string.charCodeAt(n);
	 
				if (c < 128) {
					utftext += String.fromCharCode(c);
				}
				else if((c > 127) && (c < 2048)) {
					utftext += String.fromCharCode((c >> 6) | 192);
					utftext += String.fromCharCode((c & 63) | 128);
				}
				else {
					utftext += String.fromCharCode((c >> 12) | 224);
					utftext += String.fromCharCode(((c >> 6) & 63) | 128);
					utftext += String.fromCharCode((c & 63) | 128);
				}
	 
			}
	 
			return utftext;
		};
	 
		var x=Array();
		var k,AA,BB,CC,DD,a,b,c,d;
		var S11=7, S12=12, S13=17, S14=22;
		var S21=5, S22=9 , S23=14, S24=20;
		var S31=4, S32=11, S33=16, S34=23;
		var S41=6, S42=10, S43=15, S44=21;
	 
		string = Utf8Encode(string);
	 
		x = ConvertToWordArray(string);
	 
		a = 0x67452301; b = 0xEFCDAB89; c = 0x98BADCFE; d = 0x10325476;
	 
		for (k=0;k<x.length;k+=16) 
		{
			AA=a; BB=b; CC=c; DD=d;
			a=FF(a,b,c,d,x[k+0], S11,0xD76AA478);
			d=FF(d,a,b,c,x[k+1], S12,0xE8C7B756);
			c=FF(c,d,a,b,x[k+2], S13,0x242070DB);
			b=FF(b,c,d,a,x[k+3], S14,0xC1BDCEEE);
			a=FF(a,b,c,d,x[k+4], S11,0xF57C0FAF);
			d=FF(d,a,b,c,x[k+5], S12,0x4787C62A);
			c=FF(c,d,a,b,x[k+6], S13,0xA8304613);
			b=FF(b,c,d,a,x[k+7], S14,0xFD469501);
			a=FF(a,b,c,d,x[k+8], S11,0x698098D8);
			d=FF(d,a,b,c,x[k+9], S12,0x8B44F7AF);
			c=FF(c,d,a,b,x[k+10],S13,0xFFFF5BB1);
			b=FF(b,c,d,a,x[k+11],S14,0x895CD7BE);
			a=FF(a,b,c,d,x[k+12],S11,0x6B901122);
			d=FF(d,a,b,c,x[k+13],S12,0xFD987193);
			c=FF(c,d,a,b,x[k+14],S13,0xA679438E);
			b=FF(b,c,d,a,x[k+15],S14,0x49B40821);
			a=GG(a,b,c,d,x[k+1], S21,0xF61E2562);
			d=GG(d,a,b,c,x[k+6], S22,0xC040B340);
			c=GG(c,d,a,b,x[k+11],S23,0x265E5A51);
			b=GG(b,c,d,a,x[k+0], S24,0xE9B6C7AA);
			a=GG(a,b,c,d,x[k+5], S21,0xD62F105D);
			d=GG(d,a,b,c,x[k+10],S22,0x2441453);
			c=GG(c,d,a,b,x[k+15],S23,0xD8A1E681);
			b=GG(b,c,d,a,x[k+4], S24,0xE7D3FBC8);
			a=GG(a,b,c,d,x[k+9], S21,0x21E1CDE6);
			d=GG(d,a,b,c,x[k+14],S22,0xC33707D6);
			c=GG(c,d,a,b,x[k+3], S23,0xF4D50D87);
			b=GG(b,c,d,a,x[k+8], S24,0x455A14ED);
			a=GG(a,b,c,d,x[k+13],S21,0xA9E3E905);
			d=GG(d,a,b,c,x[k+2], S22,0xFCEFA3F8);
			c=GG(c,d,a,b,x[k+7], S23,0x676F02D9);
			b=GG(b,c,d,a,x[k+12],S24,0x8D2A4C8A);
			a=HH(a,b,c,d,x[k+5], S31,0xFFFA3942);
			d=HH(d,a,b,c,x[k+8], S32,0x8771F681);
			c=HH(c,d,a,b,x[k+11],S33,0x6D9D6122);
			b=HH(b,c,d,a,x[k+14],S34,0xFDE5380C);
			a=HH(a,b,c,d,x[k+1], S31,0xA4BEEA44);
			d=HH(d,a,b,c,x[k+4], S32,0x4BDECFA9);
			c=HH(c,d,a,b,x[k+7], S33,0xF6BB4B60);
			b=HH(b,c,d,a,x[k+10],S34,0xBEBFBC70);
			a=HH(a,b,c,d,x[k+13],S31,0x289B7EC6);
			d=HH(d,a,b,c,x[k+0], S32,0xEAA127FA);
			c=HH(c,d,a,b,x[k+3], S33,0xD4EF3085);
			b=HH(b,c,d,a,x[k+6], S34,0x4881D05);
			a=HH(a,b,c,d,x[k+9], S31,0xD9D4D039);
			d=HH(d,a,b,c,x[k+12],S32,0xE6DB99E5);
			c=HH(c,d,a,b,x[k+15],S33,0x1FA27CF8);
			b=HH(b,c,d,a,x[k+2], S34,0xC4AC5665);
			a=II(a,b,c,d,x[k+0], S41,0xF4292244);
			d=II(d,a,b,c,x[k+7], S42,0x432AFF97);
			c=II(c,d,a,b,x[k+14],S43,0xAB9423A7);
			b=II(b,c,d,a,x[k+5], S44,0xFC93A039);
			a=II(a,b,c,d,x[k+12],S41,0x655B59C3);
			d=II(d,a,b,c,x[k+3], S42,0x8F0CCC92);
			c=II(c,d,a,b,x[k+10],S43,0xFFEFF47D);
			b=II(b,c,d,a,x[k+1], S44,0x85845DD1);
			a=II(a,b,c,d,x[k+8], S41,0x6FA87E4F);
			d=II(d,a,b,c,x[k+15],S42,0xFE2CE6E0);
			c=II(c,d,a,b,x[k+6], S43,0xA3014314);
			b=II(b,c,d,a,x[k+13],S44,0x4E0811A1);
			a=II(a,b,c,d,x[k+4], S41,0xF7537E82);
			d=II(d,a,b,c,x[k+11],S42,0xBD3AF235);
			c=II(c,d,a,b,x[k+2], S43,0x2AD7D2BB);
			b=II(b,c,d,a,x[k+9], S44,0xEB86D391);
			a=AddUnsigned(a,AA);
			b=AddUnsigned(b,BB);
			c=AddUnsigned(c,CC);
			d=AddUnsigned(d,DD);
		}
	 
		var temp = WordToHex(a)+WordToHex(b)+WordToHex(c)+WordToHex(d);
	 
		return temp.toLowerCase();
    }

	function table_data_select($table, $select, $where, $db, $var, $function)
	{
		if( typeof window[$var] !== "undefined" ) return(false); 
	
		var $postdata=new Object();
		$postdata["API"]="cms";
		$postdata["APIRequest"]="TableDataSelect";
		$postdata["table"]=$table;
		if( $select=="" ) $select="*";
		$postdata["select"]=$select;
		$postdata["db"]=$db;
		if( $where != "" ) $postdata["where"]=$where;
		$.post("<?php echo PATH; ?>soa2/", $postdata, function($data)
		{
			try { $xml = $($.parseXML($data)); } catch ($err) { show_status2($data); return(false); }
			$ack = $xml.find("Ack");
			if ( $ack.text()!="Success" ) { show_status2($data); return(false); }
			
			$data=new Array();	
			var $i=0;
			$xml.find($table).each(function()
			{
				$data[$i]=new Array();
				$(this).children().each(function()
				{
					$data[$i][this.tagName]=$(this).text();
				});
				$i++;
			});
			window[$var]=$data; //put contents to global variable
			window[$function](); //call the return function
		});
		return(true);
	}

	function soa2($postdata, $function, $mode)
	{
		$.post("<?php echo PATH; ?>soa2/", $postdata, function($data)
		{
			if( typeof $mode === "undefined" ) $mode="obj";
			wait_dialog_hide();
			if ($mode=="xml") $xml=$data;
			else
			{
				try { $xml = $($.parseXML($data)); } catch ($err) { show_status2($err.message); return; }
				$ack = $xml.find("Ack");
				if ( $xml.find("Error").length>0 )
				{
					var $Code=$xml.find("Error Code").text();
					var $shortMsg=$xml.find("Error shortMsg").text();
					var $longMsg=$xml.find("Error longMsg").text();
					alert("Fehler "+$Code+"\n\n"+$longMsg);
					return;
				}
				if ( $ack.text()!="Success" ) { show_status2($data); return; }
			
				if ($mode=="array")
				{
					//convert XML to Array
				}
			}
			window[$function]($xml); //call the return function
		});
	}
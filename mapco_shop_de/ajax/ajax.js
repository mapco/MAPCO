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
	var response=ajax('http://www.mapco.de/ajax/item_search.php?wert='+encodeURIComponent(wert)+'&lang='+get["lang"], false);
	document.getElementById("results").style.visibility='visible';
	document.getElementById("results").innerHTML=response;
}

function suche3()
{
	document.getElementById("results").innerHTML='';
	document.getElementById("results").style.visibility='hidden';
}

function cart_add(item_id)
{
	var amount=document.getElementById("article"+item_id).value;
	var response=ajax('http://www.mapco.de/ajax/cart_add.php?id_item='+encodeURIComponent(item_id)+'&amount='+amount, false);
	cart_update();
	alert(response);
	return(false);
}

function note_add(item_id)
{
	alert("noch nicht verfügbar");
//				var response=ajax('ajax/cart_add.php?id_item='+encodeURIComponent(item_id), false);
//				cart_update();
	return(false);
}

function cart_update()
{
	var get=get_GET_params();
	var response=ajax('http://www.mapco.de/ajax/cart_update.php'+'?lang='+get["lang"], false);
	document.getElementById("cart").innerHTML=response;
	document.getElementById("cart").style.visibility='visible';
}

function cart_clear()
{
	if (confirm('Warenkorb wirklich leeren?'))
	{
		var get=get_GET_params();
		var response=ajax('http://www.mapco.de/ajax/cart_clear.php?lang='+get["lang"], false);
		if (response!="") alert("ERROR: "+response);
		cart_update();
	}
}

function cart_clear2()
{
	var get=get_GET_params();
	var response=ajax('http://www.mapco.de/ajax/cart_clear.php?lang='+get["lang"], false);
	if (response!="") alert("ERROR: "+response);
	cart_update();
}

function mouse_pos(e) {
	if(!e) e = window.event;
	var body = (window.document.compatMode && window.document.compatMode == "CSS1Compat") ? 
	window.document.documentElement : window.document.body;
	return {
	// Position im Dokument
	top: e.pageY ? e.pageY : e.clientY + body.scrollTop - body.clientTop,
	left: e.pageX ? e.pageX : e.clientX + body.scrollLeft  - body.clientLeft
 
	};
}


function getMouseXY(e) {
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
		document.getElementById("cat"+id).style.backgroundImage='url(http://www.mapco.de/images/icons/16x16/right.png)';
	}
	else
	{
		document.getElementById(id).style.display="block";
		document.getElementById("cat"+id).style.backgroundImage='url(http://www.mapco.de/images/icons/16x16/down.png)';
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
	html+='<tr><th style="height:20px;">'+title+'<img style="margin:0; border:0; padding:0; cursor:pointer; display:inline; float:right;" src="http://www.mapco.de/images/icons/16x16/remove.png" onclick="document.getElementById(\''+id+'\').style.display=\'none\';" alt="Schließen" title="Schließen" /></th></tr>';
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
		$("#status").html('<textarea style="width:600px; height:100px;">'+text+'</textarea> <a href="#" onclick="hide_status();">Schließen</a>');
	}

	function hide_status()
	{
		$("#status").hide("puff", {}, 250);
	}

	function wait_dialog_show($var)
	{
		wait_dialog_timer = setTimeout("wait_dialog_show2(" + $var + ");", 500);
	}

	function wait_dialog_show2($var)
	{
		$var = typeof $var === 'undefined' ? "Bitte warten... " : $var;
		$("#wait_dialog").dialog
		({	closeText: "Fenster schließen",
			height: 118,
			hide: { effect: 'drop', direction: "up" },
			modal: true,
			resizable: false,
			show: { effect: 'drop', direction: "up" },
			title: $var,
			width: 118
		});
	}

	function wait_dialog_hide()
	{
		clearTimeout(wait_dialog_timer);
		$("#wait_dialog").dialog("close");
	}
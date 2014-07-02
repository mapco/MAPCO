/**
 * JSAction 01_13_18
 * www.i-coding.de
 */
function HpConstants(){}
HpConstants.JSF=':';
HpConstants.MESSAGES='messages';
HpConstants.SERVICE_SUCCESS='enterprise.SUCCESS';
HpConstants.control='control';
HpConstants.config='config';
HpConstants.init='init';
HpConstants.localize='localize';
HpConstants.localizer='localizer';
HpConstants.text='text';
HpConstants.integer='integer';
HpConstants.decimal='decimal';
HpConstants.currency='currency';
HpConstants.rate='rate';
HpConstants.date='date';
HpConstants.time='time';
HpConstants.minlength= 'minlength';
HpConstants.maxlength= 'maxlength';
HpConstants.minimum='minimum';
HpConstants.maximum='maximum';
HpConstants.step='step';
HpConstants.format='format';
HpConstants.notrim='notrim';
HpConstants.plain='plain';
HpConstants.required='required';
HpConstants.readonly='readonly';
HpConstants.pagebreakbefore='pagebreakbefore';
HpConstants.pagebreakafter='pagebreakafter';
HpConstants.info='info';
HpConstants.warn='warn';
HpConstants.error='error';
HpConstants.success='success';
HpConstants.active='active';
HpConstants.passive='passive';
HpConstants.positive='positive';
HpConstants.negative='negative';
HpConstants.hide='hide';
HpConstants.show='show';
HpConstants.visible='visible';
HpConstants.unvisible='unvisible';
HpConstants.light='light';
HpConstants.select='select';
HpConstants.highlight='highlight';
HpConstants.print='print';
function HpResource(){}
HpResource.datePattern='dd-MM-yyyy';
HpResource.dateSeparator='-';
HpResource.timePattern='HH:mm';
HpResource.timeSeparator=':';
HpResource.decimalSeparator='.';
HpResource.groupingSeparator=',';
HpResource.minlength='Minimum charaters';
HpResource.maxlength='Maximum characters';
HpResource.minimum='Minimum';
HpResource.maximum='Maximum';
HpResource.step='Allowed step size:';
HpResource.errorAJAX='Action failed!';
HpResource.errorCurrency='Not a valid number or more than two decimal places!';
HpResource.errorDecimal='Not a valid number!';
HpResource.errorInteger='Only integers allowed!';
HpResource.errorRate='Not a valid number or more than four decimal places!';
HpResource.errorRequired='Input data are missing!';
HpResource.errorTrim='No leading or trailing spaces!';
HpResource.errorDate='Not a valid date!';
HpResource.errorTime='Not a valid time!';
function HpEvent(event)
{
this.type=null;
this.target=null;
this.relatedTarget=null;
this.keyCode=null;
this.charCode=null;
this.ctrlKey=null;
this.clientX=null;
this.clientY=null;
this.offsetX=null;
this.offsetY=null;
if(event==null)
event=window.event;
this.type=event.type;
if(event.srcElement==null)
this.target=event.target;
else
this.target=event.srcElement;
if(event.type=='mouseover')
{
if((event.relatedTarget==null) && (event.fromElement!=null))
this.relatedTarget=event.fromElement;
else
this.relatedTarget=event.relatedTarget;
}
else if(event.type=='mouseout')
{
if((event.relatedTarget==null) && (event.toElement!=null))
this.relatedTarget=event.toElement;
else
this.relatedTarget=event.relatedTarget;
}
this.keyCode=event.keyCode;
if(event.charCode)
this.charCode=event.charCode;
else
this.charCode=event.keyCode;
this.ctrlKey=event.ctrlKey;
this.clientX=event.clientX;
this.clientY=event.clientY;
if(event.offsetX)
{
this.offsetX=event.offsetX;
this.offsetY=event.offsetY;
}
else
{
this.offsetX=event.layerX;
this.offsetY=event.layerY;
}
}
HpEvent.addEvent=function(a,b,c,d){if(HpCore.runtime==true){HpEvent.setRuntimeEvent(a,b,c);return true;}if(a.addEventListener){a.addEventListener(b,c,d);return true;}else if(a.attachEvent){var e=a.attachEvent('on'+b,c);return e;}else {a['on'+b]=c;}}
HpEvent.setRuntimeEvent=function(a,b,c){if(b=='abort') a.onabort=c;else if(b=='blur') a.onblur=c;else if(b=='change') a.onchange=c;else if(b=='click') a.onclick=c;else if(b=='dblclick') a.ondblclick=c;else if(b=='error') a.onerror=c;else if(b=='focus') a.onfocus=c;else if(b=='keydown') a.onkeydown=c;else if(b=='keypress') a.onkeypress=c;else if(b=='keyup') a.onkeyup=c;else if(b=='load') a.onload=c;else if(b=='mousedown') a.onmousedown=c;else if(b=='mousemove') a.onmousemove=c;else if(b=='mouseout') a.onmouseout=c;else if(b=='mouseover') a.onmouseover=c;else if(b=='mouseup') a.onmouseup=c;else if(b=='reset') a.onreset=c;else if(b=='resize') a.onresize=c;else if(b=='select') a.onselect=c;else if(b=='submit') a.onsubmit=c;else if(b=='unload') a.onunload=c;else throw 'Unhandeled event b in HpEvent.setRuntimeEvent! '+b;}
function HpCore(){}
HpCore.browser=null;
HpCore.messages=null;
HpCore.runtime=false;
HpCore.actions=new Array();
HpCore.locale='en';
HpCore.locales=new Array('en','de');
HpCore.initAutomatic=true;
HpCore.initBefore=new Array();
HpCore.initStartElement=new Array();
HpCore.initEndElement=new Array();
HpCore.initAfter=new Array();
HpEvent.addEvent(window,'load',initHpCore,false);
function initHpCore()
{
var i=0;
var temp=null;
HpCore.determineBrowser();
temp=document.getElementById(HpConstants.MESSAGES);
if(temp!=null)
HpCore.messages=new HpScrollBox(null,temp,true);
for(i=0;i<HpCore.initBefore.length;i++)
HpCore.initBefore[i]();
if(HpCore.initAutomatic==true)
HpAction.registerAutomatic(document);
for(i=0;i<HpCore.initAfter.length;i++)
HpCore.initAfter[i]();
HpCore.initBefore=null;
HpCore.initStartElement=null;
HpCore.initEndElement=null;
HpCore.initAfter=null;
HpCore.runtime=true;
}
HpCore.addInitBefore=function(a){HpCore.initBefore[HpCore.initBefore.length]=a;}
HpCore.addInitStartElement=function(a){HpCore.initStartElement[HpCore.initStartElement.length]=a;}
HpCore.addInitEndElement=function(a){HpCore.initEndElement[HpCore.initEndElement.length]=a;}
HpCore.addInitAfter=function(a){HpCore.initAfter[HpCore.initAfter.length]=a;}
HpCore.determineBrowser=function(){var a=null;if(navigator.userAgent==null) return;a=navigator.userAgent.toLowerCase();if(a.indexOf("msie")!=-1){HpCore.browser='EXPLORER';return;}if(a.indexOf("firefox")!=-1){HpCore.browser='FIREFOX';return;}if(a.indexOf("opera")!=-1){HpCore.browser='OPERA';return;}}
function HpDom(){}
HpDom.searchClass=function(a,b){var c=0;var d='';if((a.className==null)||(a.className.length<=0)) return null;c=a.className.indexOf(b);if((c<0)||((c>0)&&(a.className.charAt(c-1)!=' '))) return null;for(;(c<a.className.length)&&(a.className.charAt(c)!=' ');c++) d=d+a.className.charAt(c);return d;}
HpDom.containsClass=function(a,b){if(HpDom.getClassIndex(a,b)>=0) return true;else return false;}
HpDom.getClassIndex=function(a,b){var c=0;if(a.className==null) return -1;if(a.className==b) return 0;c=a.className.indexOf(b+' ');if(c==0) return 0;c=a.className.indexOf(' '+b+' ');if(c>=0) return c+1;c=a.className.indexOf(' '+b);if((c>=0)&&(c==a.className.length-1-b.length)) return c+1;return -1;}
HpDom.getClasses=function(a){return a.className;}
HpDom.setClasses=function(a,b){a.className=b;}
HpDom.listClass=function(a){var b=HpDom.getClasses(a);if((b==null)||(b.length==0)) return new Array();return b.split(' ');}
HpDom.addClass=function(a,b){if(b==null) return;if(HpDom.containsClass(a,b)==false){if(a.className.length==0) a.className=b;else a.className=a.className+' '+b;}}
HpDom.addClasses=function(a,b){var c=0;var d=null;if(b==null) return;d=b.split(' ');for(c=0;c<d.length;c++) HpDom.addClass(a,d[c]);}
HpDom.removeClass=function(a,b){var c=0;if(b==null) return;c=HpDom.getClassIndex(a,b);if(c>=0) a.className=a.className.substring(0,c)+a.className.substring(c+b.length,a.className.length);}
HpDom.removeClasses=function(a,b){var c=0;var d=null;if(b==null) return;d=b.split(' ');for(c=0;c<d.length;c++) HpDom.removeClass(a,d[c]);}
HpDom.getElementsByClass=function(a,b){var c=0;var d=null;var e=new Array();if(HpDom.containsClass(a,b)==true) e[e.length]=a;for(c=0;c<a.childNodes.length;c++){d=HpDom.getElementsByClass(a.childNodes[c],b);HpUtil.append(e,d);}return e;}
HpDom.removeChilds=function(a){var b=a.childNodes.length;while(a.hasChildNodes()) a.removeChild(a.firstChild);return b;}
HpDom.getChild=function(a,b){var c=0;for(c=0;c<a.childNodes.length;c++){if(a.childNodes[c].nodeName.toLowerCase()==b.toLowerCase()) return a.childNodes[c];}return null;}
HpDom.listChild=function(a,b,c){var d=0;var e=0;var f=null;var g=new Array();var h=null;if(b!=null) h=b.split(',');for(d=0;d<a.childNodes.length;d++){f=a.childNodes[d];if((f==null)||(f.nodeType!=1)) continue;if(b==null){g[g.length]=f;}else {if(f.nodeName!=null){for(e=0;e<h.length;e++){if(f.nodeName.toLowerCase()==h[e].toLowerCase()) g[g.length]=f;}}}if((c==true)&&(f.hasChildNodes()==true)) HpUtil.appendArray(g,HpDom.listChild(f,b,c));}return g;}
HpDom.listData=function(a){var b=0;var c=0;var d=null;var e=null;var f=null;var g=new HpPairList();d=HpDom.listChild(a,'input,select,textarea',true);for(b=0;b<d.length;b++){f=null;e=d[b];if(HpUtil.isEmpty(e.name)) continue;if(e.disabled) continue;if(HpDom.isCheckBox(e)){if(e.checked==false) continue;}else if(HpDom.isRadio(e)){if(e.checked==false) continue;}else if(HpDom.isSelect(e)){if(e.selectedIndex==-1) continue;f=e.options;}else if(HpDom.isInput(e)){if(e.type=='reset') continue;}if(f==null) g.addNew(e.name,e.value);else for(c=0;c<f.length;c++){if(f[c].selected) g.addNew(e.name,f[c].value);}}return g;}
HpDom.isParent=function(a,b){var c=0;while(b.parentNode!=null){if(b.parentNode==a) return true;b=b.parentNode;}return false;}
HpDom.findParent=function(a,b){if(a.parentNode==null) return null;if(a.parentNode.nodeName.toLowerCase()==b.toLowerCase()) return a.parentNode;return HpDom.findParent(a.parentNode,b);}
HpDom.findPrevious=function(a,b){while(a!=null){a=a.previousSibling;if(a==null) return null;if(a.nodeName.toLowerCase()==b) return a;}return null;}
HpDom.findNext=function(a,b){while(a!=null){a=a.nextSibling;if(a==null) return null;if(a.nodeName.toLowerCase()==b) return a;}return null;}
HpDom.getText=function(a){if(HpDom.isField(a)==true) return a.value;else if(a.nodeType==1){if(a.hasChildNodes()) return a.firstChild.nodeValue;else return null;}else if(a.nodeType==3) return a.nodeValue;}
HpDom.setText=function(a,b){if(HpDom.isField(a)==true) a.value=b;else if(a.nodeType==1){if(a.hasChildNodes()) a.firstChild.nodeValue=b;else a.appendChild(document.createTextNode(b));}else if(a.nodeType==3) a.nodeValue=b;}
HpDom.unselect=function(a){var b=HpDom.getText(a);HpDom.setText(a,'');HpDom.setText(a,b);}
HpDom.isField=function(a){var b=null;if(a.nodeName==null) return false;b=a.nodeName.toLowerCase();if((b=='input')||(b=='select')||(b=='textarea')) return true;else return false;}
HpDom.isInput=function(a){return HpDom.checkNodeName(a,'input');}
HpDom.isCheckBox=function(a){if((HpDom.isInput(a)==true)&&(a.type=='checkbox')) return true;else return false;}
HpDom.isRadio=function(a){if((HpDom.isInput(a)==true)&&(a.type=='radio')) return true;else return false;}
HpDom.isSelect=function(a){return HpDom.checkNodeName(a,'select');}
HpDom.isTextArea=function(a){return HpDom.checkNodeName(a,'textarea');}
HpDom.checkNodeName=function(a,b){if((a.nodeName!=null)&&(a.nodeName.toLowerCase()==b)) return true;else return false;}
HpDom.clearElement=function(a){if(HpDom.isSelect(a)==true) HpDom.removeChilds(a);else if(HpDom.isField(a)==true) HpDom.setText(a,'');else HpDom.removeChilds(a);}
HpDom.getOption=function(a,b){return a.options[b] }
HpDom.getOptionSelected=function(a){var b=0;var c=new Array();for(b=0;b<a.options.length;b++) if(a.options[b].selected) c.push(a.options[b]);return c;}
HpDom.setOptionSelected=function(a,b){var c=0;var d=0;var e=new Array();for(c=0;c<a.options.length;c++){a.options[c].selected=false;if(b==null) continue;for(d=0;d<b.length;d++){if(a.options[c].value==b[d]){a.options[c].selected=true;e.push(a.options[c]);}}}return e;}
HpDom.getOptionSelectedValuesAsString=function(a){var b=0;var c=null;var d='';c=HpDom.getOptionSelected(a);if(c.length==0) return null;for(b=0;b<c.length;b++){d=d+c[b].value;if(b<c.length-1) d=d+',';}return d;}
HpDom.setOptionSelectedValuesAsString=function(a,b){if(b==null) return HpDom.setOptionSelected(a,null);else return HpDom.setOptionSelected(a,b.split(','));}
HpDom.getOptionSelectedLabelsAsString=function(a){var b=0;var c=null;var d='';c=HpDom.getOptionSelected(a);if(c.length==0) return null;for(b=0;b<c.length;b++){d=d+c[b].text;if(b<c.length-1) d=d+',';}return d;}
HpDom.addOption=function(a,b,c,d,e,f){var g=new Option(c,d,e,f);a.add(g,b);return g;}
HpDom.changeOption=function(a,b,c,d,e,f){var g=a.options[b];g.text=c;g.value=d;g.defaultSelected=e;g.selected=f;return g;}
HpDom.removeOption=function(a,b){a.remove(b);}
HpDom.getAttribute=function(a,b){return a.getAttributeNode(b);}
HpDom.setAttribute=function(a,b,c){var d=null;try{d=document.createAttribute(b);d.nodeValue=c;a.setAttributeNode(d);}catch(e){a.setAttribute(b,c);}return a.getAttributeNode(b);}
HpDom.removeAttribute=function(a,b){a.removeAttribute(b,0);}
HpDom.includeCSS=function(a){var b=document.getElementsByTagName('head')[0];var c=document.createElement('link');c.setAttribute('rel','stylesheet');c.setAttribute('type','text/css');c.setAttribute('href',a);b.appendChild(c);}
HpDom.includeJavaScript=function(a){var b=document.getElementsByTagName('head')[0];var c=document.createElement('script');c.setAttribute('type','text/javascript');c.setAttribute('src',a);b.appendChild(c);}
HpDom.cloneXML=function(a){var b=0;var c=null;var d=null;var e=null;if(a.nodeType==1){c=document.createElement(a.nodeName);for(b=0;b<a.attributes.length;b++){d=a.attributes[b].nodeName;e=a.attributes[b].nodeValue;if(d.substring(0,2)=='on') HpAction.registerAll(c,d.substring(2),null,new Function('event',e),null,null);else HpDom.setAttribute(c,d,e);}for(b=0;b<a.childNodes.length;b++) c.appendChild(HpDom.cloneXML(a.childNodes[b]));}else if(a.nodeType==3){c=document.createTextNode(HpUtil.filter(a.nodeValue));}else {throw 'Unhandeled a type in HpDom.cloneXML!';}return c;}
function HpNumber(){}
HpNumber.randomInteger=function(a,b){var c=0;if(a>b){c=a;a=b;b=c;}c=Math.floor(Math.random()*(b-a+1));c=c+a;return c;}
HpNumber.round=function(a,b){var c=Math.pow(10,b);var d=Math.round(Math.abs(a)*c);if(a>=0.0) return d/c;else return d/c*-1;}
HpNumber.checkInteger=function(a){return HpNumber.checkNumber(a,0);}
HpNumber.checkDecimal=function(a){return HpNumber.checkNumber(a,-1);}
HpNumber.checkCurrency=function(a){return HpNumber.checkNumber(a,2);}
HpNumber.checkRate=function(a){return HpNumber.checkNumber(a,4);}
HpNumber.checkNumber=function(a,b){return HpNumber.check(a,b,HpResource.decimalSeparator,HpResource.groupingSeparator);}
HpNumber.parseInteger=function(a){return HpNumber.parseNumber(a,0);}
HpNumber.parseIntegerOrNullWhenEmpty=function(a){if(HpUtil.isEmpty(a)) return null;return HpNumber.parseInteger(a);}
HpNumber.parseDecimal=function(a){return HpNumber.parseNumber(a,-1);}
HpNumber.parseDecimalOrNullWhenEmpty=function(a){if(HpUtil.isEmpty(a)) return null;return HpNumber.parseDecimal(a);}
HpNumber.parseCurrency=function(a){return HpNumber.parseNumber(a,2);}
HpNumber.parseCurrencyOrNullWhenEmpty=function(a){if(HpUtil.isEmpty(a)) return null;return HpNumber.parseCurrency(a);}
HpNumber.parseRate=function(a){return HpNumber.parseNumber(a,4);}
HpNumber.parseRateOrNullWhenEmpty=function(a){if(HpUtil.isEmpty(a)) return null;return HpNumber.parseRate(a);}
HpNumber.parseNumber=function(a,b){return HpNumber.parse(a,b,HpResource.decimalSeparator,HpResource.groupingSeparator);}
HpNumber.formatInteger=function(a){return HpNumber.formatNumber(a,0) }
HpNumber.formatDecimal=function(a){return HpNumber.formatNumber(a,-1) }
HpNumber.formatCurrency=function(a){return HpNumber.formatNumber(a,2) }
HpNumber.formatRate=function(a){return HpNumber.formatNumber(a,4) }
HpNumber.formatNumber=function(a,b){return HpNumber.format(a,b,HpResource.decimalSeparator,HpResource.groupingSeparator) }
HpNumber.check=function(a,b,c,d){var e=null;var f=-1;if(a.length<=0) return false;for(i=0;i<a.length;i++){e=a.charAt(i);if((e>='0')&&(e<='9')) continue;if(e==d){if((f!=-1)&&(i>f)) return false;continue;}if(e==c){if(b==0) return false;if((b!=-1)&&(i<a.length-b-1)) return false;if(f!=-1) return false;f=i;continue;}if(((e=='+')||(e=='-'))&&(i==0)) continue;return false;}return true;}
HpNumber.parse=function(a,b,c,d){var e=0;var f=null;var g='';var h='';if(e<a.length){f=a.charAt(e);if((f=='+')||(f=='-')){e++;h=f;}}if(e<a.length){f=a.charAt(e);if(f==c) g=h+'0';else g=h;}for(;e<a.length;e++){f=a.charAt(e);if(f==d) continue;else if(f==c) g=g+'.';else if((f>='0')&&(f<='9')) g=g+f;}if(g.length==0) return null;if(b>=0) return HpNumber.round(parseFloat(g),b);return parseFloat(g);}
HpNumber.extract=function(a){var b=0;var c=null;var d='';var e=false;var f=false;if(a==null) return null;for(b=0;b<a.length;b++){c=a.charAt(b);if((d.length==0)&&((c=='-')||(c=='+'))){d=d+c;}else if((c>='0')&&(c<='9')){f=true;d=d+c;}else if((f==true)&&(e==false)&&(c=='.')){e=true;d=d+c;}}if(f==false) return null;else return parseFloat(d);}
HpNumber.format=function(a,b,c,d){var e=0;var f=null;var g=0;var h='';var i='';var j=-1;if(b>=0) i=HpNumber.round(a,b).toFixed(b);else i=a.toString();j=i.indexOf('.');for(e=i.length-1;e>=0;e--){f=i.charAt(e);if((f=='-')||(f=='+')){h=f+h;break;}if(f=='.'){h=c+h;continue;}if((j==-1)||(j>e)) g++;if(g==4){g=1;h=d+h;}h=f+h;}return h;}
function HpUtil(){}
HpUtil.formatList=function(a,b){var c=0;var d='';for(c=0;c<a.length;c++){d=d+a[c];if(c<a.length-1) d=d+b;}return d;}
HpUtil.appendArray=function(a,b){var c=0;for(c=0;c<b.length;c++) a[a.length]=b[c];}
HpUtil.getInnerWidth=function(){if(self.innerWidth) return self.innerWidth;if(document.documentElement && document.documentElement.clientWidth) return document.documentElement.clientWidth;if(document.body) return document.body.clientWidth;return 0;}
HpUtil.getInnerHeight=function(){if(self.innerHeight) return self.innerHeight;if(document.documentElement && document.documentElement.clientHeight) return document.documentElement.clientHeight;if(document.body) return document.body.clientHeight;return 0;}
HpUtil.filter=function(a){var b=null;var c='';var d=false;var e=null;for(b=0;b<a.length;b++){e=a.charAt(b);if((e==' ')||(e=='\n')||(e=='\t')){if(d==true) c=c+' ';d=false;}else {c=c+e;d=true;}}return c;}
HpUtil.compressSpace=function(a){var b=0;var c=0;var d='';var e=null;a=HpUtil.trim(a);for(b=0;b<a.length;b++){e=a.charAt(b);if(e==' ') c++;else c=0;if(c<=1) d=d+e;}return d;}
HpUtil.trim=function(a){return HpUtil.trimRight(HpUtil.trimLeft(a));}
HpUtil.trimLeft=function(a){var b=0;for(b=0;b<a.length;b++){if(a.charAt(b)!=' ') break;}return a.substring(b);}
HpUtil.trimRight=function(a){var b=0;for(b=a.length-1;b>=0;b--){if(a.charAt(b)!=' ') break;}return a.substring(0,b+1);}
HpUtil.append=function(a,b){for(var c=0;c<b.length;c++) a[a.length]=b[c];}
HpUtil.getIndex=function(a,b){for(var c=0;c<a.length;c++){if(a[c]==b) return c;}return -1;}
HpUtil.isNaviKey=function(a){if(a.keyCode==null) return false;if(a.keyCode==9) return true;if((a.keyCode>=37)&&(a.keyCode<=40)) return true;return false;}
HpUtil.isArrowKey=function(a){if(a.keyCode==null) return false;if((a.keyCode>=37)&&(a.keyCode<=40)) return true;return false;}
HpUtil.isEmpty=function(a){if(a==null) return true;if(a.length<=0) return true;return false;}
HpUtil.isNotEmpty=function(a){if(a==null) return false;if(a.length<=0) return false;return true;}
HpUtil.insertTag=function(a,b){HpUtil.insertText(a,new Array('['+b+']','[/'+b+']'));}
HpUtil.insertText=function(a,b){var c=null;var d=null;var e=null;var f=null;var g=null;if(document.selection){a.focus();f=document.selection.createRange();if(b instanceof Array){g=f.text;f.text=b[0]+g+b[1];if(g.length==0){f.moveStart('character',-b[1].length);f.moveEnd ('character',-b[1].length);}}else f.text=b;f.select();}else if((a.selectionStart)||(a.selectionStart=='0')){d=a.scrollTop;e=a.selectionStart;c=a.selectionEnd;if(b instanceof Array){g=a.value.substring(e,c);a.value=a.value.substring(0,e)+b[0]+g+b[1]+a.value.substring(c,a.value.length);if(g.length==0) a.selectionStart=e+b[0].length;else a.selectionStart=e+b[0].length+g.length+b[1].length;}else {a.value=a.value.substring(0,e)+b+a.value.substring(c,a.value.length);a.selectionStart=e+b.length;}a.focus();a.selectionEnd=a.selectionStart;a.scrollTop=d;}else {if(b instanceof Array) a.value=a.value+b[0]+b[1];else a.value=a.value+b;a.focus();}}
HpUtil.setCursorPosition=function(a,b){if(a!=null){if(a.createTextRange!=null){var c=a.createTextRange();c.move('character',b);c.select();}else {if(a.selectionStart!=null){a.focus();a.setSelectionRange(b,b);}else a.focus();}}}
HpUtil.getSelectedText=function(){if(window.getSelection) return window.getSelection();else if(document.getSelection) return document.getSelection();else if(document.selection) return document.selection.createRange().text;else return null;}
HpUtil.stringToXml=function(a){var b=0;var c=null;var d='';for(b=0;b<a.length;b++){c=a.charAt(b);switch(c){case '<': d=d+'&lt;';break;case '>': d=d+'&gt;';break;case '&': d=d+'&amp;';break;case '\'': d=d+'&apos;';break;case '"': d=d+'&quot;';break;default: d=d+c;break;}}return d;}
function HpElement(){}
HpElement.separator=',';
HpElement.renderText=function(a,b,c,d){var e=null;if(b==null) return null;if((b.length<=0)&&(c==true)) return null;e=HpDom.getText(a);if((e==null)||(e.length<=0)){HpDom.setText(a,b);return a;}if(c==true){if(d==null) HpDom.setText(a,e+b);else HpDom.setText(a,e+d+b);return a;}if(b!=e) HpDom.setText(a,b);return a;}
function HpInput(){}
HpInput.separator=',';
HpInput.renderText=function(a,b,c,d){return HpElement.renderText(a,b,c,d);}
function HpSelect(){}
HpSelect.renderText=function(a,b,c,d){if(b==null) return null;if(d<0){HpDom.addOption(a,null,b,c,false,true);return a;}HpDom.changeOption(a,d,b,c,false,true);return a;}
function HpTextArea(){}
HpTextArea.separator='\n';
HpTextArea.renderText=function(a,b,c,d){return HpElement.renderText(a,b,c,d);}
function HpDiv(parent,element)
{
this.parent=parent;
this.element=element;
this.top=null;
this.left=null;
this.width=null;
this.height=null;
this.visible=true;
if(element==null)
return;
this.visible=(HpDom.containsClass(element,HpConstants.hide)==false);
if(this.visible==true)
{
this.top=element.offsetTop;
this.left=element.offsetLeft;
this.width=element.offsetWidth;
this.height=element.offsetHeight;
}
else
{
this.setVisible(true);
this.top=element.offsetTop;
this.left=element.offsetLeft;
this.width=element.offsetWidth;
this.height=element.offsetHeight;
this.setVisible(false);
}
}
HpDiv.prototype.removeChilds=function(){var a=true;if(this.visible==false){a=false;this.setVisible(true);}this.element.style.width='';this.element.style.height='';HpDom.removeChilds(this.element);this.setWidth (this.element.offsetWidth);this.setHeight(this.element.offsetHeight);if(a==false) this.setVisible(false);}
HpDiv.prototype.appendChild=function(a){var b=true;if(this.visible==false){b=false;this.setVisible(true);}this.element.style.width='';this.element.style.height='';this.element.appendChild(a);this.setWidth (this.element.offsetWidth);this.setHeight(this.element.offsetHeight);if(b==false) this.setVisible(false);}
HpDiv.prototype.setVisible=function(a){this.visible=a;if(a==true) HpDom.removeClass(this.element,HpConstants.hide);else HpDom.addClass(this.element,HpConstants.hide);}
HpDiv.prototype.setTop=function(a){this.top=a;this.element.style.top=a+'px';}
HpDiv.prototype.setLeft=function(a){this.left=a;this.element.style.left=a+'px';}
HpDiv.prototype.setWidth=function(a){this.width=a;this.element.style.width=a+'px';}
HpDiv.prototype.setHeight=function(a){this.height=a;this.element.style.height=a+'px';}
HpBox.prototype=new HpDiv();
HpBox.prototype.constructor=HpBox;
function HpBox(parent,element,auto)
{
var temp=null;
var visibleState=true;
this.content=null;
this.close=null;
this.indent=null;
this.full=null;
this.auto=auto;
this.minimumHeight=26;
this.maximumHeight=124;
if(element==null)
return;
if(HpDom.containsClass(element,HpConstants.hide)==true)
{
HpDom.removeClass(element,HpConstants.hide);
visibleState=false;
}
HpDom.addClass(element,'box');
HpDiv.call(this,parent,element);
temp=HpDom.getChild(element,'ul');
if(temp==null)
{
temp=document.createElement('ul');
this.element.appendChild(temp);
}
this.content=new HpDiv(this,temp);
temp=document.createElement('div');
this.element.appendChild(temp);
HpDom.addClass(temp,'close');
this.close=new HpDiv(this,temp);
HpDom.setText(temp,'X');
temp=document.createElement('div');
this.element.appendChild(temp);
HpDom.addClass(temp,'indent');
this.indent=new HpDiv(this,temp);
HpDom.setText(temp,'▲');
temp=document.createElement('div');
this.element.appendChild(temp);
HpDom.addClass(temp,'full');
this.full=new HpDiv(this,temp);
HpDom.setText(temp,'▼');
HpBox.prototype.resize.call(this);
HpAction.registerRender(this.close.element,'click',HpBox.renderClose,this);
HpAction.registerRender(this.indent.element,'click',HpBox.renderIndent,this);
HpAction.registerRender(this.full.element,'click',HpBox.renderFull,this);
if(visibleState==false)
this.setVisible(false);
}
HpBox.prototype.resize=function(){if(this.auto==true) this.setHeight(this.minimumHeight);this.close.setLeft (this.width-this.close.width-5);this.indent.setLeft(this.close.left-this.indent.width-1);this.full.setLeft (this.indent.left);if(this.height>=this.content.height){this.indent.setVisible(false);this.full.setVisible(false);}else if(this.height>=this.maximumHeight){this.indent.setVisible(true);this.full.setVisible(false);}else {this.indent.setVisible(false);this.full.setVisible(true);}}
HpBox.prototype.setWidth=function(a){HpDiv.prototype.setWidth.call(this,a);this.resize();}
HpBox.prototype.removeItems=function(){var a=true;if(this.visible==false){a=false;this.setVisible(true);}HpDiv.prototype.removeChilds.call(this.content);this.resize();if(a==false) this.setVisible(false);}
HpBox.prototype.addItem=function(a){var b=null;var c=true;if(this.visible==false){c=false;this.setVisible(true);}b=document.createElement('li');b.appendChild(document.createTextNode(a));HpDiv.prototype.appendChild.call(this.content,b);this.resize();if(c==false) this.setVisible(false);return b;}
HpBox.renderClose=function(a){a.targets[0].setVisible(false);}
HpBox.renderIndent=function(a){var b=a.targets[0];b.indent.setVisible(false);b.full.setVisible(true);b.setHeight(b.minimumHeight);}
HpBox.renderFull=function(a){var b=a.targets[0];b.indent.setVisible(true);b.full.setVisible(false);if(b.content.height>=b.maximumHeight) b.setHeight(b.maximumHeight);else b.setHeight(b.content.height);}
HpScrollBox.prototype=new HpBox();
HpScrollBox.prototype.constructor=HpScrollBox;
function HpScrollBox(parent,element,auto)
{
var temp=null;
this.scroller=null;
var visibleState=true;
if(element==null)
return;
if(HpDom.containsClass(element,HpConstants.hide)==true)
{
HpDom.removeClass(element,HpConstants.hide);
visibleState=false;
}
HpBox.call(this,parent,element,auto);
temp=document.createElement('div');
this.element.appendChild(temp);
this.scroller=new HpScroller(this,temp,true);
this.scroller.onMouseUp=HpScrollBox.onScrollerMouseUp;
this.scroller.onButtonMouseMove=HpScrollBox.onScrollerButtonMouseMove;
HpScrollBox.prototype.resize.call(this);
if(visibleState==false)
this.setVisible(false);
}
HpScrollBox.prototype.setWidth=function(a){HpBox.prototype.setWidth.call(this,a);}
HpScrollBox.prototype.resize=function(){HpScrollBox.scroll(this,this.scroller.position);HpBox.prototype.resize.call(this);if(this.content.height>this.maximumHeight){this.scroller.setVisible(true);this.scroller.setLeft (this.width-this.scroller.width-4);this.scroller.setHeight (this.maximumHeight-this.scroller.top-5);this.scroller.setPositions(0,this.content.height-this.maximumHeight);}else {this.scroller.setVisible(false);}}
HpScrollBox.onScrollerMouseUp=function(a){HpScrollBox.scroll(a.targets[0],a.targets[2]);}
HpScrollBox.onScrollerButtonMouseMove=function(a){HpScrollBox.scroll(a.targets[0],a.targets[2]*-1);}
HpScrollBox.scroll=function(a,b){a.content.setTop(a.scroller.scroll(b));}
function HpCursor(parent)
{
var temp=null;
this.parent=parent;
this.topLine=null;
this.leftLine=null;
this.rightLine=null;
this.bottomLine=null;
this.top=0;
this.left=0;
this.width=0;
this.height=0;
this.visible=true;
this.setVisible=function(a){this.visible=a;if(a==true){this.topLine.setVisible(true);this.leftLine.setVisible(true);this.rightLine.setVisible(true);this.bottomLine.setVisible(true);}else {this.topLine.setVisible(false);this.leftLine.setVisible(false);this.rightLine.setVisible(false);this.bottomLine.setVisible(false);}}
this.setTop=function(a){this.top=a;this.topLine.setTop (a);this.leftLine.setTop (a);this.rightLine.setTop (a);this.bottomLine.setTop(a+this.leftLine.height-1);}
this.setLeft=function(a){this.left=a;this.topLine.setLeft (a);this.leftLine.setLeft (a);this.rightLine.setLeft (a+this.topLine.width-1);this.bottomLine.setLeft(a);}
this.setWidth=function(a){this.width=a;this.topLine.setWidth (a);this.rightLine.setLeft (this.topLine.left+a-1);this.bottomLine.setWidth(a);}
this.setHeight=function(a){this.height=a;this.leftLine.setHeight (a);this.rightLine.setHeight(a);this.bottomLine.setTop (this.topLine.top+a-1);}
temp=document.createElement('div');
this.parent.element.appendChild(temp);
HpDom.addClass(temp,'cursor');
this.topLine=new HpDiv(this,temp);
this.topLine.setHeight(1);
temp=document.createElement('div');
this.parent.element.appendChild(temp);
HpDom.addClass(temp,'cursor');
this.leftLine=new HpDiv(this,temp);
this.leftLine.setWidth(1);
temp=document.createElement('div');
this.parent.element.appendChild(temp);
HpDom.addClass(temp,'cursor');
this.rightLine=new HpDiv(this,temp);
this.rightLine.setWidth(1);
temp=document.createElement('div');
this.parent.element.appendChild(temp);
HpDom.addClass(temp,'cursor');
this.bottomLine=new HpDiv(this,temp);
this.bottomLine.setHeight(1);
}
HpScroller.prototype=new HpDiv();
HpScroller.prototype.constructor=HpScroller;
function HpScroller(parent,element,vertical)
{
var visibleState=true;
this.vertical=vertical;
this.onMouseUp=null;
this.onButtonMouseStart=null;
this.onButtonMouseMove=null;
this.onButtonMouseStop=null;
this.button=null;
this.process=false;
this.range=0;
this.factor=1;
this.position=0;
this.positionStart=0;
this.positionEnd=0;
this.lastPosition=0;
this.lastClientPosition=0;
this.setPositions=function(a,b){this.position=a;this.positionStart=a;this.range=b;this.positionEnd=a-b;this.calculateFactor();this.scroll(0);}
this.setButtonWidth=function(a){this.button.setWidth(a);this.calculateFactor();}
this.setButtonHeight=function(a){this.button.setHeight(a);this.calculateFactor();}
this.calculateFactor=function(){if(this.vertical==true) this.factor=this.range/(this.height-this.button.height-2);else this.factor=this.range/(this.width-this.button.width-2);}
this.scroll=function(a){this.position=this.position-a;if(this.position>this.positionStart) this.position=this.positionStart;else if(this.position<this.positionEnd) this.position=this.positionEnd;if(this.vertical==true) this.button.setTop (HpNumber.round(((this.positionStart-this.position)/this.factor),0));else this.button.setLeft(HpNumber.round(((this.positionStart-this.position)/this.factor),0));return this.position;}
if(element==null)
return;
if(HpDom.containsClass(element,HpConstants.hide)==true)
{
HpDom.removeClass(element,HpConstants.hide);
visibleState=false;
}
if(vertical==true)
HpDom.addClass(element,'verticalScroller');
else
HpDom.addClass(element,'horizontalScroller');
HpDiv.call(this,parent,element);
this.element.appendChild(document.createElement('div'));
this.button=new HpDiv(this,this.element.firstChild);
this.button.setTop (0);
this.button.setLeft(0);
if(vertical==true)
this.button.setHeight(39);
else
this.button.setWidth (50);
HpAction.registerRender(this.element,'mouseup',HpScroller.mouseUp,[this.parent,this]);
HpAction.registerRender(this.button.element,'mousedown',HpScroller.buttonMouseStart,[this.parent,this]);
HpAction.registerRender(this.button.element,'mousemove',HpScroller.buttonMouseMove,[this.parent,this]);
HpAction.registerRender(this.button.element,'mouseup',HpScroller.buttonMouseStop,[this.parent,this]);
HpAction.registerRender(this.button.element,'mouseout',HpScroller.buttonMouseStop,[this.parent,this]);
if(visibleState==false)
this.setVisible(false);
}
HpScroller.prototype.setWidth=function(a){HpDiv.prototype.setWidth.call(this,a);if(this.vertical==true) this.button.setWidth(a-2);this.calculateFactor();}
HpScroller.prototype.setHeight=function(a){HpDiv.prototype.setHeight.call(this,a);if(this.vertical==false) this.button.setHeight(a-2);this.calculateFactor();}
HpScroller.mouseUp=function(a){var b=null;var c=null;c=a.targets[1];if(c.process==true) return;c.process=true;if(c.vertical==true) b=a.event.offsetY-c.button.top-HpNumber.round((c.button.height/2),0);else b=a.event.offsetX-c.button.left-HpNumber.round((c.button.width/2),0);b=HpNumber.round((b*c.factor),0);a.targets[2]=b;if(c.onMouseUp!=null) c.onMouseUp(a);c.process=false;}
HpScroller.buttonMouseStart=function(a){var b=null;b=a.targets[1];b.process=true;if(b.vertical==true){b.lastPostion=b.button.top;b.lastClientPosition=a.event.clientY;}else {b.lastPostion=b.button.left;b.lastClientPosition=a.event.clientX;}if(b.onButtonMouseStart!=null) b.onButtonMouseStart(a);}
HpScroller.buttonMouseMove=function(a){var b=0;var c=null;c=a.targets[1];if(c.process==false) return;if(c.vertical==true) b=c.button.top-(c.lastPostion+(a.event.clientY-c.lastClientPosition));else b=c.button.left-(c.lastPostion+(a.event.clientX-c.lastClientPosition));b=HpNumber.round((b*c.factor),0);a.targets[2]=b;if(c.onButtonMouseMove!=null) c.onButtonMouseMove(a);}
HpScroller.buttonMouseStop=function(a){var b=null;b=a.targets[1];if(b.onButtonMouseStop!=null) b.onButtonMouseStop(a);b.process=false;}
HpCell.prototype=new HpDiv();
HpCell.prototype.constructor=HpCell;
function HpCell(parent,index,element)
{
var visibleState=true;
this.index=index;
this.name=HpDom.listClass(element)[0];
this.highlight=false;
this.active=false;
this.select=false;
this.action=HpDom.containsClass(element,'action');
this.getTotalIndex=function(){var a=0;var b=0;var c=this.parent.parent;for(a=0;a<c.zones.length;a++){if(c.zones[a]==this.parent) break;b=b+c.zones[a].cells.length;}return this.index+b;}
this.setHighlight=function(a){this.highlight=a;if(a==true) HpDom.addClass(this.element,HpConstants.highlight);else HpDom.removeClass(this.element,HpConstants.highlight);}
this.setActive=function(a){this.active=a;if(a==true) HpDom.addClass(this.element,HpConstants.active);else HpDom.removeClass(this.element,HpConstants.active);}
this.setSelect=function(a){this.select=a;if(a==true) HpDom.addClass(this.element,HpConstants.select);else HpDom.removeClass(this.element,HpConstants.select);}
if(element==null)
return;
if(HpDom.containsClass(element,HpConstants.hide)==true)
{
HpDom.removeClass(element,HpConstants.hide);
visibleState=false;
}
HpDiv.call(this,parent,element);
if(visibleState==false)
this.setVisible(false);
}
function HpCellState(cell)
{
this.left=cell.left;
this.width=cell.width;
this.visible=cell.visible;
}
HpZone.prototype=new HpDiv();
HpZone.prototype.constructor=HpZone;
function HpZone(parent,index,element)
{
var i=0;
var list=null;
var visibleState=true;
this.index=index;
this.cells=new Array();
this.scroll=HpDom.containsClass(element,'scroll');
if(element==null)
return;
if(HpDom.containsClass(element,HpConstants.hide)==true)
{
HpDom.removeClass(element,HpConstants.hide);
visibleState=false;
}
HpDiv.call(this,parent,element);
list=HpDom.listChild(element,'div',false);
for(i=0;i<list.length;i++)
this.cells[this.cells.length]=new HpCell(this,i,list[i]);
if(visibleState==false)
this.setVisible(false);
}
function HpZoneState(zone)
{
this.left=zone.left;
this.width=zone.width;
}
HpRow.prototype=new HpDiv();
HpRow.prototype.constructor=HpRow;
function HpRow(parent,index,element)
{
var i=0;
var zone=null;
var list=null;
var visibleState=true;
this.index=index;
this.zones=new Array();
this.cells=new Array();
if(element==null)
return;
if(HpDom.containsClass(element,HpConstants.hide)==true)
{
HpDom.removeClass(element,HpConstants.hide);
visibleState=false;
}
HpDiv.call(this,parent,element);
list=HpDom.listChild(element,'div',false);
for(i=0;i<list.length;i++)
{
zone=new HpZone(this,i,list[i])
this.zones[this.zones.length]=zone;
HpUtil.appendArray(this.cells,zone.cells);
}
if(visibleState==false)
this.setVisible(false);
}
function HpRowState(row)
{
this.width=row.width;
this.height=row.height;
}
HpSection.prototype=new HpDiv();
HpSection.prototype.constructor=HpSection;
function HpSection(parent,index,element)
{
var i=0;
var list=null;
var visibleState=true;
this.index=index;
this.rows=new Array();
if(element==null)
return;
if(HpDom.containsClass(element,HpConstants.hide)==true)
{
HpDom.removeClass(element,HpConstants.hide);
visibleState=false;
}
HpDiv.call(this,parent,element);
list=HpDom.listChild(element,'div',false);
for(i=0;i<list.length;i++)
this.rows[this.rows.length]=new HpRow(this,i,list[i]);
if(visibleState==false)
this.setVisible(false);
}
HpGrid.prototype=new HpDiv();
HpGrid.prototype.constructor=HpGrid;
function HpGrid(element,form)
{
var i=0;
var temp=null;
var list=null;
var visibleState=true;
this.element=element;
this.form=form;
temp=element.id.split(HpConstants.JSF);
this.id=temp[temp.length-1];
this.edit=false;
this.editElement=null;
this.horizontalScrollIndex=0;
this.sectionWidth=0;
this.rowStateList=null;
this.rowStatePrint=null;
this.rowStateCurrent=null;
this.cellStatesList=new Array();
this.cellStatesPrint=new Array();
this.cellStatesCurrent=null;
this.zoneStatesList=new Array();
this.zoneStatesPrint=new Array();
this.zoneStatesCurrent=null;
this.prints=new Array();
this.printHeads=null;
this.printBodys=null;
this.printFoots=null;
this.printPixelShort=657;
this.printPixelLong=987;
this.cell=null;
this.head=null;
this.body=null;
this.foot=null;
this.control=null;
this.focuser=null;
this.cursor=new HpCursor(this);
this.target=null;
this.matrix=null;
this.verticalScroller=null;
this.horizontalScroller=null;
this.components=new HpPairList();
this.filler=new Array();
this.skin=null;
this.printRows=47;
this.cellCursor=false;
this.columnCursor=false;
this.headHighlight=true;
this.onRenderCursor=null;
this.onResetCursor=null;
this.onStartEdit=null;
this.onStopEdit=null;
this.onMouseDown=null;
this.onMouseUp=null;
this.onRenderCell=null;
this.onChangeCell=null;
this.onChangeRow=null;
this.maxWidth=0;
this.maxHeight=0;
this.changeSkin=function(a){var b=0;var c=0;var d=null;var e=new Array();d=this.body.rows[0];for(b=0;b<d.cells.length;b++){if(d.cells[b].action==true) e[e.length]=b;}if(this.skin!=null){HpDom.removeClass(this.head.element,this.skin);HpDom.removeClass(this.body.element,this.skin);if(this.foot!=null) HpDom.removeClass(this.foot.element,this.skin);HpDom.removeClass(this.control.element,this.skin);HpDom.removeClass(this.verticalScroller.element,this.skin);for(b=0;b<this.filler.length;b++) HpDom.removeClass(this.filler[b].element,this.skin);for(b=0;b<this.body.rows.length;b++){d=this.body.rows[b];for(c=0;c<e.length;c++) HpDom.removeClass(d.cells[e[c]].element,this.skin);}}this.skin=a;if(this.skin!=null){HpDom.addClass(this.head.element,this.skin);HpDom.addClass(this.body.element,this.skin);if(this.foot!=null) HpDom.addClass(this.foot.element,this.skin);HpDom.addClass(this.control.element,this.skin);HpDom.addClass(this.verticalScroller.element,this.skin);for(b=0;b<this.filler.length;b++) HpDom.addClass(this.filler[b].element,this.skin);for(b=0;b<this.body.rows.length;b++){d=this.body.rows[b];for(c=0;c<e.length;c++) HpDom.addClass(d.cells[e[c]].element,this.skin);}}}
this.addComponent=function(a,b){HpDom.addClass(b,HpConstants.hide);b.style.zIndex=800;this.element.appendChild(b);this.components.addNew(a,b);}
this.focus=function(){try{this.focuser.firstChild.focus();}catch(e){}}
this.setCell=function(a){var b=null;var c=null;c=this.cell;b=a.parent.parent.parent;if(b==this.body) this.cell=a;else if(b==this.head) this.cell=this.body.rows[0].cells[a.getTotalIndex()];else if((this.foot!=null)&&(b==this.foot)) this.cell=this.body.rows[0].cells[a.getTotalIndex()];if(c!=null){if((this.onChangeCell!=null)&&(this.cell!=c)) this.onChangeCell(this,this.cell.parent.parent,c.parent.parent,this.cell,c);if((this.onChangeRow!=null)&&(this.cell.parent.parent!=c.parent.parent)) this.onChangeRow(this,this.cell.parent.parent,c.parent.parent,this.cell,c);}HpDom.setText(this.horizontalScroller.button.element,(this.cell.parent.parent.index+1) +':'+ (this.cell.getTotalIndex()+1));}
this.setNextVisibleCell=function(){var a=0;var b=null;var c=0;var d=0;if(this.cell.visible==true) return;c=this.cell.parent.parent.index;d=this.cell.getTotalIndex();for(a=d+1;(b==null)&&(a<this.body.rows[c].cells.length);a++){if(this.body.rows[c].cells[a].visible==true) b=this.body.rows[c].cells[a];}for(a=d-1;(b==null)&&(a>=0);a--){if(this.body.rows[c].cells[a].visible==true) b=this.body.rows[c].cells[a];}if(b!=null) this.setCell(b);}
this.getTopHeight=function(){var a=0;if(this.head!=null) a=a+this.head.height;return a;}
this.getBottomHeight=function(){var a=0;if(this.foot!=null) a=a+this.foot.height;if(this.control!=null) a=a+this.control.height;return a;}
this.getBodyVisibleHeight=function(){return this.height-this.getTopHeight()-this.getBottomHeight();}
this.getBodyOverflowHeight=function(){return this.body.height-this.getBodyVisibleHeight();}
this.initSubmit=function(){var a=0;var b=null;var c=null;b=this.listInput();for(a=0;a<b.length;a++){c=document.createElement('input');c.type='hidden';c.id=b[a].id;c.name=b[a].id;c.value=HpDom.getText(b[a]);this.form.appendChild(c);}}
this.listInput=function(){var a=0;var b=null;var c=new Array();b=this.element.getElementsByTagName('span');for(a=0;a<b.length;a++){if((b[a].id!=null)&&(b[a].id.length!=0)) c[c.length]=b[a];}return c;}
this.determineSection=function(a){if(a==null) return null;if(a==this.body.element) return this.body;if(a==this.head.element) return this.head;if((this.foot!=null)&&(a==this.foot.element)) return this.foot;if(a==this.control.element) return this.control;return null;}
this.determineRow=function(a){var b=0;var c=null;c=this.determineSection(a.parentNode);for(b=0;b<c.rows.length;b++){if(c.rows[b].element==a) return c.rows[b];}return null;}
this.determineZone=function(a){var b=0;var c=null;c=this.determineRow(a.parentNode);for(b=0;b<c.zones.length;b++){if(c.zones[b].element==a) return c.zones[b];}return null;}
this.determineCell=function(a){var b=0;var c=null;c=this.determineZone(a.parentNode);for(b=0;b<c.cells.length;b++){if(c.cells[b].element==a) return c.cells[b];}return null;}
this.setCursor=function(a,b){this.cellCursor=a;this.columnCursor=b;if((a==true)||(b==true)) this.cursor.setVisible(true);else this.cursor.setVisible(false);this.renderCursor();}
this.renderCursor=function(){var a=null;if((this.columnCursor==true)&&(((this.target!=null)&&(this.target.parent.parent.parent==this.head))||(this.cellCursor==false))){this.cell.setSelect(true);this.cursor.setTop (0);this.cursor.setLeft (this.cell.parent.left+this.cell.left);this.cursor.setWidth (this.cell.width);this.cursor.setHeight(this.control.top);if(this.onRenderCursor!=null){a=this.onRenderCursor(this);if(a!=null) HpDom.addClass(a,HpConstants.select);}if((this.horizontalScroller.visible==true)&&(this.cell.parent.scroll==true)&&((this.cursor.left<this.horizontalScroller.positionStart)||((this.cursor.left+this.cursor.width)>this.horizontalScroller.positionStart+(this.head.rows[0].zones[this.horizontalScrollIndex].width-this.horizontalScroller.range)))) this.cursor.setVisible(false);else this.cursor.setVisible(true);}else if(this.cellCursor==true){this.cell.setSelect(true);this.cursor.setTop (this.body.top+this.cell.parent.parent.top-1);this.cursor.setLeft (this.cell.parent.left+this.cell.left);this.cursor.setWidth (this.cell.width);this.cursor.setHeight(this.cell.parent.parent.height+1);if(this.onRenderCursor!=null){a=this.onRenderCursor(this);if(a!=null) HpDom.addClass(a,HpConstants.select);}if((this.cursor.top<(this.body.top-1))||(this.cursor.top>this.getBodyVisibleHeight())) this.cursor.setVisible(false);else if((this.horizontalScroller.visible==true)&&(this.cell.parent.scroll==true)&&((this.cursor.left<this.horizontalScroller.positionStart)||((this.cursor.left+this.cursor.width)>this.horizontalScroller.positionStart+(this.head.rows[0].zones[this.horizontalScrollIndex].width-this.horizontalScroller.range)))) this.cursor.setVisible(false);else this.cursor.setVisible(true);}this.focus();}
this.resetCursor=function(){var a=null;this.cell.setSelect(false);if(this.onResetCursor!=null){a=this.onResetCursor(this);if(a!=null) HpDom.removeClass(a,HpConstants.select);}}
this.naviLeft=function(){var a=null;var b=this.cell.getTotalIndex()-1;var c=this.cell.parent.parent.index;for(;b>=0;b--){a=this.body.rows[c].cells[b];if(a.action==true) break;if(a.visible==false) continue;this.resetCursor();this.setCell(a);if((this.horizontalScroller.visible==true)&&(this.cell.parent.scroll==true)&&((this.cell.parent.left+this.cell.left)<this.horizontalScroller.positionStart)) HpGrid.scrollHorizontal(this,(this.cell.width*-1));else this.renderCursor();break;}}
this.naviUp=function(){var a=this.cell.getTotalIndex();var b=this.cell.parent.parent.index;if(b>0){this.resetCursor();this.setCell(this.body.rows[b-1].cells[a]);if(this.cell.parent.parent.top<0) HpGrid.scrollVertical(this,(this.cell.parent.parent.height*-1));else this.renderCursor();}}
this.naviRight=function(){var a=null;var b=this.cell.getTotalIndex()+1;var c=this.cell.parent.parent.index;for(;b<this.body.rows[c].cells.length;b++){a=this.body.rows[c].cells[b];if(a.action==true) break;if(a.visible==false) continue;this.resetCursor();this.setCell(a);if((this.horizontalScroller.visible==true)&&(this.cell.parent.scroll==true)&&((this.horizontalScroller.position+this.cell.left+this.cell.width)>(this.horizontalScroller.positionStart+(this.head.rows[0].zones[this.horizontalScrollIndex].width-this.horizontalScroller.range)))) HpGrid.scrollHorizontal(this,this.cell.width);else this.renderCursor();break;}}
this.naviDown=function(){var a=this.cell.getTotalIndex();var b=this.cell.parent.parent.index;if(b<this.body.rows.length-1){this.resetCursor();this.setCell(this.body.rows[b+1].cells[a]);if((this.cell.parent.parent.top+this.cell.parent.parent.height)>=(this.height-this.getBottomHeight())) HpGrid.scrollVertical(this,this.cell.parent.parent.height);else this.renderCursor();}}
this.createList=function(){var a=0;for(a=0;a<this.prints.length;a++) this.element.parentNode.removeChild(this.prints[a].element);this.prints.length=0;}
this.createPrint=function(){var a=0;var b=0;var c=0;var d=0;var e=null;var f=null;var g=null;var h=null;var i=0;this.printHeads=new Array();this.printBodys=new Array();this.printFoots=new Array();for(a=0;a<this.prints.length;a++) this.element.parentNode.removeChild(this.prints[a].element);this.prints.length=0;i=this.printRows-this.head.rows.length;if(this.foot!=null) i=i-this.foot.rows.length;for(a=0;a<this.body.rows.length;a=a+i){g=this.element.cloneNode(false);g.style.height='0px';this.element.parentNode.insertBefore(g,this.element);if(this.skin!=null) HpDom.removeClass(g,this.skin);h=new HpDiv(null,g);this.prints[this.prints.length]=h;h.element.appendChild(document.createElement('br'));if(a!=0) HpDom.addClass(h.element,HpConstants.pagebreakbefore);g=this.head.element.cloneNode(true);h.element.appendChild(g);if(this.skin!=null) HpDom.removeClass(g,this.skin);this.printHeads[d]=new HpSection(h,0,g);c=0;g=this.body.element.cloneNode(false);h.element.appendChild(g);HpDom.removeClass(g,HpConstants.hide);if(this.skin!=null) HpDom.removeClass(g,this.skin);this.printBodys[d]=new HpSection(h,1,g);for(b=a;(b<this.body.rows.length)&&(b<(a+i));b++){g=this.body.rows[b].element.cloneNode(true);this.printBodys[d].element.appendChild(g);if(this.skin!=null) HpDom.removeClass(g,this.skin);e=new HpRow(this.printBodys[d],this.printBodys[d].rows.length,g);this.printBodys[d].rows[this.printBodys[d].rows.length]=e;e.setTop(c);c=c+e.height;}this.printBodys[d].setTop (this.printHeads[d].height);this.printBodys[d].setHeight(c);if(this.foot!=null){g=this.foot.element.cloneNode(true);h.element.appendChild(g);HpDom.removeClass(g,HpConstants.hide);if(this.skin!=null) HpDom.removeClass(g,this.skin);this.printFoots[d]=new HpSection(h,2,g);this.printFoots[d].setTop(this.printBodys[d].top+this.printBodys[d].height);}h.setWidth(this.printBodys[d].width);if(this.printFoots[d]==null) h.setHeight(this.printBodys[d].top+this.printBodys[d].height);else h.setHeight(this.printFoots[d].top+this.printFoots[d].height);HpDom.addClass(h.element,HpConstants.print);d++;}}
this.resize=function(a,b){var c=0;var d=0;var e=null;this.maxWidth=a;this.maxHeight=b;e=this.getTopHeight()+this.body.height+this.getBottomHeight();if(e<b) this.setHeight(e);else this.setHeight(b);if(this.getBodyOverflowHeight()>0) this.verticalScroller.setVisible(true);else this.verticalScroller.setVisible(false);for(c=0;c<this.cellStatesList.length;c++){if(this.cellStatesList[c].visible==true) d=d+this.cellStatesList[c].width;}if(this.verticalScroller.visible==true) d=d+this.verticalScroller.width+2;if(d<this.maxWidth) this.setWidth(d);else this.setWidth(this.maxWidth);if(this.verticalScroller.visible==true) this.sectionWidth=this.width-this.verticalScroller.width-2;else this.sectionWidth=this.width;this.control.setTop (this.height-this.control.height);this.control.setWidth(this.width);this.control.rows[0].setWidth(this.width);this.control.rows[0].zones[0].setWidth(this.width);this.control.rows[0].zones[0].cells[1].setLeft(this.width-this.control.rows[0].zones[0].cells[1].width);this.resizeStates();this.resizeScroller();this.renderList();if(this.prints.length>0) this.renderPrint();this.focuser.style.top=(this.height-1)+'px';}
this.resizeStates=function(){var a=0;var b=0;var c=0;var d=null;var e=null;var f=null;var g=null;var h=0;var i=0;var j=null;var k=null;d=this.head.rows[0];for(a=0;a<d.zones.length;a++){h=0;i=0;e=d.zones[a];for(b=0;b<e.cells.length;b++){f=e.cells[b];c=f.getTotalIndex();j=this.cellStatesList[c];j.left=h;if(j.visible==true) h=h+j.width;k=this.cellStatesPrint[c];k.left=i;if(k.visible==true) i=i+k.width;}this.zoneStatesList[a].width=h;this.zoneStatesPrint[a].width=i;if(a>0){this.zoneStatesList[a].left=this.zoneStatesList[a-1].left+this.zoneStatesList[a-1].width;this.zoneStatesPrint[a].left=this.zoneStatesPrint[a-1].left+this.zoneStatesPrint[a-1].width;}}this.rowStateList.width=this.sectionWidth;this.rowStatePrint.width=this.zoneStatesPrint[this.zoneStatesPrint.length-1].left+this.zoneStatesPrint[this.zoneStatesPrint.length-1].width;if(d.zones.length==2){if(d.zones[0].scroll==true) this.zoneStatesList[1].left=this.sectionWidth-this.zoneStatesList[1].width;else if(d.zones[1].scroll==true) this.zoneStatesList[1].left=this.zoneStatesList[0].width;}else if(d.zones.length==3){if(d.zones[0].scroll==true){this.zoneStatesList[1].left=this.sectionWidth-this.zoneStatesList[1].width-this.zoneStatesList[2].width;this.zoneStatesList[2].left=this.sectionWidth-this.zoneStatesList[2].width;}else if(d.zones[1].scroll==true){this.zoneStatesList[1].left=this.zoneStatesList[0].width;this.zoneStatesList[2].left=this.sectionWidth-this.zoneStatesList[2].width;}else if(d.zones[2].scroll==true){this.zoneStatesList[1].left=this.zoneStatesList[0].width;this.zoneStatesList[2].left=this.zoneStatesList[0].width+this.zoneStatesList[1].width;}}}
this.resizeScroller=function(){var a=0;var b=null;var c=0;var d=0;var e=0;for(a=0;a<this.filler.length;a++) this.element.removeChild(this.filler[a].element);this.filler.length=0;if(this.verticalScroller.visible==true){this.verticalScroller.setTop (this.getTopHeight());this.verticalScroller.setLeft (this.sectionWidth);this.verticalScroller.setHeight (this.getBodyVisibleHeight()-2);this.verticalScroller.setPositions(0,this.getBodyOverflowHeight());for(a=0;a<this.head.rows.length;a++){b=this.head.rows[a].element.cloneNode(false);this.element.appendChild(b);b=new HpDiv(this,b);this.filler[this.filler.length]=b;if(this.skin==null) HpDom.addClass(b.element,'fillerHead');else HpDom.addClass(b.element,'fillerHead '+this.skin);b.setTop (this.head.top+b.top);b.setLeft(this.verticalScroller.left);}if(this.foot!=null){for(a=0;a<this.foot.rows.length;a++){b=this.foot.rows[a].element.cloneNode(false);this.element.appendChild(b);b=new HpDiv(this,b);this.filler[this.filler.length]=b;if(this.skin==null) HpDom.addClass(b.element,'fillerFoot');else HpDom.addClass(b.element,'fillerFoot '+this.skin);b.setTop (this.verticalScroller.top+this.verticalScroller.height+2+(a*b.height));b.setLeft(this.verticalScroller.left);}}}for(a=0;a<this.head.rows[0].zones.length;a++){zone=this.head.rows[0].zones[a];if(zone.scroll==true) e=this.zoneStatesList[a].width;else c=c+this.zoneStatesList[a].width;}d=e-(this.width-c);if(this.verticalScroller.visible==true) d=d+this.verticalScroller.width+2;if(d<=0){this.horizontalScroller.setVisible(false);}else {this.horizontalScroller.setVisible(true);this.horizontalScroller.setTop (this.control.top+1);this.horizontalScroller.setLeft (HpNumber.round(this.width/2,0)-HpNumber.round(this.horizontalScroller.width/2,0));this.horizontalScroller.setPositions(this.zoneStatesList[this.horizontalScrollIndex].left,d);}}
this.renderList=function(){this.rowStateCurrent=this.rowStateList;this.zoneStatesCurrent=this.zoneStatesList;this.cellStatesCurrent=this.cellStatesList;this.renderSection(this.head);this.renderSection(this.body);this.body.setTop (this.head.height);if(this.foot!=null){this.renderSection(this.foot);this.foot.setTop (this.control.top-this.foot.height);}}
this.renderPrint=function(){var a=0;this.rowStateCurrent=this.rowStatePrint;this.zoneStatesCurrent=this.zoneStatesPrint;this.cellStatesCurrent=this.cellStatesPrint;for(a=0;a<this.prints.length;a++){this.renderSection(this.printHeads[a]);this.renderSection(this.printBodys[a]);if(this.foot!=null) this.renderSection(this.printFoots[a]);this.prints[a].setWidth(this.printHeads[a].width);}}
this.renderSection=function(a){var b=0;var c=0;var d=null;for(b=0;b<a.rows.length;b++){d=a.rows[b];d.setTop(c);this.renderRow(d);c=c+d.height;}a.setWidth (d.width);a.setHeight(c);}
this.renderRow=function(a){var b=0;if(a.visible==false) a.setVisible(true);a.setWidth (this.rowStateCurrent.width);a.setHeight(this.rowStateCurrent.height);for(b=0;b<a.zones.length;b++) this.renderZone(a.zones[b]);}
this.renderZone=function(a){var b=0;var c=null;var d=null;for(b=0;b<a.cells.length;b++){c=a.cells[b];d=this.cellStatesCurrent[c.getTotalIndex()];c.setLeft (d.left);c.setWidth (d.width);c.setHeight (a.parent.height-1);c.setVisible(d.visible);if(this.onRenderCell!=null) this.onRenderCell(c);}a.setLeft (this.zoneStatesCurrent[a.index].left);a.setWidth(this.zoneStatesCurrent[a.index].width);}
this.calculateColumnSum=function(a,b,c,d,e){var f=0;var g=null;var h=null;var i=null;if(d==null) d=0;if(e==null) e=this.body.rows.length-1;while(d<=e){g=this.body.rows[d].cells[a];i=g.element.firstChild.childNodes[b];h=HpDom.getText(i);if((h!=null)&&(h.length>0)) f=f+HpNumber.parseNumber(h,c);d++;}return f;}
this.calculateRowSum=function(a,b,c,d,e){var f=0;var g=null;var h=null;var i=null;var j=null;g=this.body.rows[a];if(d==null) d=0;if(e==null) e=g.cells.length-1;while(d<=e){h=g.cells[d];j=h.element.firstChild.childNodes[b];i=HpDom.getText(j);if((i!=null)&&(i.length>0)) f=f+HpNumber.parseNumber(i,c);d++;}return f;}
this.showCols=function(a){var b=0;var c=0;var d=null;var e=new HpAction();var f=a.split(',');for(b=0;b<this.head.rows[0].cells.length;b++){d=this.head.rows[0].cells[b];d.setHighlight(true);for(c=0;c<f.length;c++){if(b==parseInt(f[c])){d.setHighlight(false);break;}}}e.addTarget(this);HpGrid.renderHideCols(e);}
if(element==null)
return;
if(HpDom.containsClass(element,HpConstants.hide)==true)
{
HpDom.removeClass(element,HpConstants.hide);
visibleState=false;
}
HpDiv.call(this,null,element);
list=HpDom.listChild(this.element,'div',false);
for(i=0;i<list.length;i++)
{
if(HpDom.containsClass(list[i],'head'))
this.head=new HpSection(this,i,list[i]);
else if(HpDom.containsClass(list[i],'body'))
this.body=new HpSection(this,i,list[i]);
else if(HpDom.containsClass(list[i],'foot'))
this.foot=new HpSection(this,i,list[i]);
else if(HpDom.containsClass(list[i],'control'))
this.control=new HpSection(this,i,list[i]);
}
this.maxWidth=this.width;
this.maxHeight=this.height;
this.rowStateList=new HpRowState(this.head.rows[0]);
this.rowStatePrint=new HpRowState(this.head.rows[0]);
for(i=0;i<this.head.rows[0].zones.length;i++)
{
temp=this.head.rows[0].zones[i];
this.zoneStatesList[i]=new HpZoneState(temp);
this.zoneStatesPrint[i]=new HpZoneState(temp);
if(temp.scroll==true)
this.horizontalScrollIndex=i;
}
for(i=0;i<this.head.rows[0].cells.length;i++)
{
temp=this.head.rows[0].cells[i];
this.cellStatesList[i]=new HpCellState(temp);
this.cellStatesPrint[i]=new HpCellState(temp);
}
this.rowStateCurrent=this.rowStateList;
this.zoneStatesCurrent=this.zoneStatesList;
this.cellStatesCurrent=this.cellStatesList;
temp=document.createElement('div');
this.element.appendChild(temp);
this.verticalScroller=new HpScroller(this,temp,true);
this.verticalScroller.setWidth(16);
this.verticalScroller.onMouseUp=HpGrid.onVerticalScrollerMouseUp;
this.verticalScroller.onButtonMouseStart=HpGrid.stopEdit;
this.verticalScroller.onButtonMouseMove=HpGrid.onVerticalScrollerButtonMouseMove;
this.verticalScroller.onButtonMouseStop=HpGrid.onVerticalScrollerButtonMouseStop;
temp=document.createElement('div');
this.element.appendChild(temp);
this.horizontalScroller=new HpScroller(this,temp,false);
this.horizontalScroller.setWidth (200);
this.horizontalScroller.setHeight(16);
this.horizontalScroller.onMouseUp=HpGrid.onHorizontalScrollerMouseUp;
this.horizontalScroller.onButtonMouseStart=HpGrid.stopEdit;
this.horizontalScroller.onButtonMouseMove=HpGrid.onHorizontalScrollerButtonMouseMove;
this.focuser=document.createElement('div');
this.focuser.id=this.element.id+'-focuser';
HpDom.addClass(this.focuser,'focuser');
this.focuser.style.left='0px';
this.focuser.style.width='1px';
this.focuser.style.height='1px';
this.element.appendChild(this.focuser);
temp=document.createElement('textarea');
temp.tabIndex=0;
this.focuser.appendChild(temp);
this.cursor.setVisible(false);
for(i=0;i<this.body.rows[0].cells.length;i++)
{
temp=this.body.rows[0].cells[i];
if(temp.action==false)
{
this.setCell(temp);
break;
}
}
temp=HpAction.registerRender(this.element,'keypress',HpGrid.renderKeyPress,this);
if(HpCore.browser=='EXPLORER')
HpAction.registerRender(this.element,'keydown',HpGrid.renderKeyArrow,this);
else
temp.addRender(HpGrid.renderKeyArrow);
HpAction.registerRender(this.body.element,'mousedown',HpGrid.renderMouseDown,this);
HpAction.registerRender(this.head.element,'mousedown',HpGrid.renderMouseDown,this);
HpAction.registerRender(this.body.element,'mouseup',HpGrid.renderMouseUp,this);
HpAction.registerRender(this.head.element,'mouseup',HpGrid.renderMouseUp,this);
HpAction.registerRender(HpDom.getElementsByClass(this.control.element,'listImage')[0],'click',HpGrid.renderListView,this);
HpAction.registerRender(HpDom.getElementsByClass(this.control.element,'printImage')[0],'click',HpGrid.renderPrintView,this);
HpAction.registerRender(HpDom.getElementsByClass(this.control.element,'printInput')[0],'keyup',HpGrid.renderPrintInput,this);
HpAction.registerRender(HpDom.getElementsByClass(this.control.element,'showCols')[0],'click',HpGrid.renderShowCols,this);
HpAction.registerRender(HpDom.getElementsByClass(this.control.element,'hideCols')[0],'click',HpGrid.renderHideCols,this);
this.resize(this.maxWidth,this.maxHeight);
this.renderCursor();
this.focus();
if(visibleState==false)
this.setVisible(false);
}
HpGrid.startEdit=function(a){var b=null;var c=null;var d=null;b=a.targets[0];if(b.edit==true) return null;if(b.prints.length>0) HpGrid.renderListView(a);if(b.onStartEdit==null) return null;d=b.onStartEdit(a);if(d==null) return null;b.edit=true;b.editElement=d.key;b.cell.element.appendChild(d.key);HpDom.removeClass(d.key,HpConstants.hide);if(HpDom.isCheckBox(d.key)){c=HpDom.getText(d.value);if(c=='X') d.key.checked=true;else d.key.checked=false;}else if(HpDom.isSelect(d.key)){if(a.event.keyCode==46) HpDom.setOptionSelectedValuesAsString(d.key,null);else HpDom.setOptionSelectedValuesAsString(d.key,HpDom.getText(d.value));}else {if((a.type=='mousedown')||(a.event.keyCode==8)||(a.event.keyCode==13)){c=HpDom.getText(d.value);if(c==null) HpDom.setText(d.key,'');else {HpDom.setText(d.key,c);HpUtil.setCursorPosition(d.key,c.length);}}else if((a.event.keyCode==46)||(a.event.keyCode==32)||(a.event.charCode==32)) HpDom.setText(d.key,'');else if(HpCore.browser=='FIREFOX'){c=String.fromCharCode(a.event.charCode);HpDom.setText(d.key,c);if(c!=null) HpUtil.setCursorPosition(d.key,c.length);}else d.key.select();}HpDom.setText(d.value,'');d.key.focus();d.key.focus();return d.key;}
HpGrid.stopEdit=function(a){var b=null;var c=null;b=a.targets[0];b.focus();if(b.edit==false) return;b.edit=false;b.editElement=null;if(b.onStopEdit==null) return;c=b.onStopEdit(a);if(c==null) return;if(HpDom.isCheckBox(c.key)){if(c.key.checked==true) HpDom.setText(c.value,'X');else HpDom.setText(c.value,'');}else if(HpDom.isSelect(c.key)){HpDom.setText(c.value,HpDom.getOptionSelectedValuesAsString(c.key));if(c.value.nextSibling==null) c.value.parentNode.appendChild(document.createTextNode(HpDom.getOptionSelectedLabelsAsString(c.key)));else HpDom.setText(c.value.nextSibling,HpDom.getOptionSelectedLabelsAsString(c.key));HpDom.setOptionSelected(c.key,null);}else HpDom.setText(c.value,HpDom.getText(c.key));b.element.appendChild(c.key);HpDom.addClass(c.key,HpConstants.hide);}
HpGrid.determineCellNode=function(a){var b=a;while(b.parentNode!=null){if(HpDom.containsClass(b.parentNode,'zone')==true) return b;b=b.parentNode;}return null;}
HpGrid.renderMouseDown=function(a){var b=null;var c=null;var d=null;d=a.targets[0];b=HpGrid.determineCellNode(a.event.target);if(b==null) return;c=d.determineCell(b);if(c.action==true) return;if(c==d.cell){if(d.edit==false) HpGrid.startEdit(a);}else {HpGrid.stopEdit(a);d.resetCursor();d.target=c;d.setCell(c);d.renderCursor();if(c.parent.parent.parent==d.body) HpGrid.startEdit(a);}if(d.onMouseDown!=null) d.onMouseDown(a);}
HpGrid.renderMouseUp=function(a){var b=0;var c=0;var d=null;var e=null;var f=null;var g=null;var h=0;var i=0;var j=0;var k=0;e=a.targets[0];e.matrix=null;f=e.target;d=HpGrid.determineCellNode(a.event.target);e.target=e.determineCell(d);if((e.headHighlight==true)&&(f!=null)&&(f.parent.parent.parent==e.head)){e.matrix=new Array();g=(f.highlight==false);i=f.getTotalIndex();h=e.target.getTotalIndex();if(i>h){b=i;i=h;h=b;}k=f.parent.parent.index;j=e.target.parent.parent.index;if(k>j){b=k;k=j;j=b;}for(b=k;b<=j;b++){e.matrix[b-k]=new Array();for(c=i;c<=h;c++){e.matrix[b-k][c-i]=e.head.rows[b].cells[c];e.head.rows[b].cells[c].setHighlight(g);}}}if(e.onMouseUp!=null) e.onMouseUp(a);if(e.edit==false) e.focus();else if(e.editElement!=null) e.editElement.focus();}
HpGrid.renderKeyPress=function(a){var b=null;var c=null;var d=null;var e=null;d=a.targets[0];e=a.event.keyCode;if(a.event.target!=d.focuser.firstChild){b=HpGrid.determineCellNode(a.event.target);if(b!=null) c=d.determineCell(b);if((c!=null)&&(c.parent.parent.parent==d.control)) return;}if((e>=37)&&(e<=40)) return;if(e==9) return;if(e==13){if(d.edit==false) HpGrid.startEdit(a);else HpGrid.stopEdit(a);return;}if(d.edit==false) HpGrid.startEdit(a);}
HpGrid.renderKeyArrow=function(a){var b=null;var c=null;var d=null;var e=null;d=a.targets[0];e=a.event.keyCode;if((d.edit==true)&&(d.editElement!=null)&&(HpDom.isSelect(d.editElement))) return;if(a.event.target!=d.focuser.firstChild){b=HpGrid.determineCellNode(a.event.target);if(b!=null) c=d.determineCell(b);if((c!=null)&&(c.parent.parent.parent==d.control)) return;}if(e==37){HpGrid.stopEdit(a);d.naviLeft();return;}if(e==38){HpGrid.stopEdit(a);d.naviUp();return;}if(e==39){HpGrid.stopEdit(a);d.naviRight();return;}if(e==40){HpGrid.stopEdit(a);d.naviDown();return;}}
HpGrid.renderListView=function(a){var b=null;var c=null;var d=null;var e=null;b=a.targets[0];c=HpDom.getElementsByClass(b.control.element,'listImage')[0];d=HpDom.getElementsByClass(b.control.element,'printImage')[0];e=HpDom.getElementsByClass(b.control.element,'printInput')[0];b.cursor.setVisible(true);HpDom.addClass(c,HpConstants.hide);HpDom.addClass(e,HpConstants.hide);HpDom.removeClass(d,HpConstants.hide);b.createList();b.changeSkin(null);b.focus();}
HpGrid.renderPrintView=function(a){var b=null;var c=null;var d=null;var e=null;b=a.targets[0];e=HpDom.getElementsByClass(b.control.element,'printInput')[0];d=HpDom.getElementsByClass(b.control.element,'printImage')[0];c=HpDom.getElementsByClass(b.control.element,'listImage')[0];HpGrid.stopEdit(a);HpDom.addClass(d,HpConstants.hide);HpDom.removeClass(c,HpConstants.hide);HpDom.removeClass(e,HpConstants.hide);b.createPrint();b.renderPrint();b.changeSkin(HpConstants.print);HpDom.setText(e,b.printRows);e.focus();}
HpGrid.renderPrintInput=function(a){var b=null;var c=null;var d=0;var e=null;if(HpUtil.isArrowKey(a.event)==true) return;b=a.targets[0];d=b.head.rows.length+1;if(b.foot!=null) d=d+b.foot.rows.length;c=new HpInteger(true,false,null,null,d,null,null).execute(a.source,a.event,null);if(c.state==false) return;e=HpNumber.parseInteger(HpDom.getText(a.source));if(e!=b.printRows){b.printRows=e;b.createPrint();b.renderPrint();}}
HpGrid.renderHideCols=function(a){var b=null;b=a.targets[0];HpGrid.setCellVisiblity(b,false);b.resize(b.maxWidth,b.maxHeight);b.resetCursor();b.setNextVisibleCell();b.renderCursor();b.focus();}
HpGrid.renderShowCols=function(a){var b=null;b=a.targets[0];HpGrid.setCellVisiblity(b,true);b.resize(b.maxWidth,b.maxHeight);b.resetCursor();b.setNextVisibleCell();b.renderCursor();b.focus();}
HpGrid.setCellVisiblity=function(a,b){var c=0;for(c=0;c<a.head.rows[0].cells.length;c++){if((b==true)||(a.head.rows[0].cells[c].highlight==false)){a.cellStatesList[c].visible=true;a.cellStatesPrint[c].visible=true;a.cellStatesCurrent[c].visible=true;}else {a.cellStatesList[c].visible=false;a.cellStatesPrint[c].visible=false;a.cellStatesCurrent[c].visible=false;}}}
HpGrid.onHorizontalScrollerMouseUp=function(a){HpGrid.stopEdit(a);HpGrid.scrollHorizontal(a.targets[0],a.targets[2]);}
HpGrid.onHorizontalScrollerButtonMouseMove=function(a){HpGrid.scrollHorizontal(a.targets[0],a.targets[2]*-1);}
HpGrid.scrollHorizontal=function(a,b){var c=0;var d=0;if(a.horizontalScroller.visible==false) return;d=a.horizontalScroller.scroll(b);for(c=0;c<a.head.rows.length;c++) a.head.rows[c].zones[a.horizontalScrollIndex].setLeft(d);for(c=0;c<a.body.rows.length;c++) a.body.rows[c].zones[a.horizontalScrollIndex].setLeft(d);if(a.foot!=null){for(c=0;c<a.foot.rows.length;c++) a.foot.rows[c].zones[a.horizontalScrollIndex].setLeft(d);}a.renderCursor();}
HpGrid.onVerticalScrollerMouseUp=function(a){var b=a.targets[2];b=b+HpGrid.calculateScrollDifference(b,a.targets[0].body.rows[0].height);HpGrid.stopEdit(a);HpGrid.scrollVertical(a.targets[0],b);}
HpGrid.onVerticalScrollerButtonMouseMove=function(a){HpGrid.scrollVertical(a.targets[0],a.targets[2]*-1);}
HpGrid.onVerticalScrollerButtonMouseStop=function(a){var b=0;var c=null;c=a.targets[0];b=c.body.rows[0].top%c.body.rows[0].height;b=HpGrid.calculateScrollDifference(b,c.body.rows[0].height);HpGrid.scrollVertical(c,b*-1);}
HpGrid.scrollVertical=function(a,b){var c=0;var d=0;var e=0;if(a.verticalScroller.visible==false) return;e=a.verticalScroller.scroll(b);d=a.body.rows[0].height;for(c=0;c<a.body.rows.length;c++) a.body.rows[c].setTop((c*d)+e);a.renderCursor();}
HpGrid.calculateScrollDifference=function(a,b){var c=0;c=Math.abs(a)%b;if(c>=HpNumber.round((b/2),0)){if(a>=0) return Math.abs(b-c);else return Math.abs(b-c)*-1;}else {if(a>=0) return c*-1;else return c;}}
HpAttribute.TAG_ATTRIBUTE='Attribute';
HpAttribute.TAG_ID='Id';
HpAttribute.TAG_NAME='Name';
HpAttribute.TAG_VALUE='Value';
function HpAttribute(element,name,value)
{
this.element=element;
this.name=name;
this.value=value;
}
HpAttribute.create=function(a){var b=new HpAttribute(null,null,null);b.element=document.getElementById(HpDom.getText(HpDom.getChild(a,HpAttribute.TAG_ID)));b.name=HpDom.getText(HpDom.getChild(a,HpAttribute.TAG_NAME));b.value=HpDom.getText(HpDom.getChild(a,HpAttribute.TAG_VALUE));return b;}
function HpAttributeList()
{
var list=new Array();
this.size=function(){return list.length;}
this.item=function(a){return list[a];}
this.get=function(a,b){var c=0;for(c=0;c<list.length;c++){if((list[c].element==a)&&(list[c].name==b)) return list[c].value;}return null;}
this.getItem=function(a,b){var c=0;for(c=0;c<list.length;c++){if((list[c].element==a)&&(list[c].name==b)) return list[c];}return null;}
this.indexOf=function(a,b){var c=0;for(c=0;c<list.length;c++){if((list[c].element==a)&&(list[c].name==b)) return c;}return -1;}
this.contains=function(a,b){if(this.indexOf(a,b)>=0) return true;else return false;}
this.add=function(a){if(a instanceof HpAttribute) list[list.length]=a;else throw 'No instance of HpAttribute!';}
this.addNew=function(a,b,c){var d=new HpAttribute(a,b,c);list[list.length]=d;return d;}
this.addList=function(a){for(i=0;i<a.size();i++) this.add(a.item(i));}
this.put=function(a,b,c){var d=this.getItem(a,b);if(d==null) return this.addNew(a,b,c);d.element=a;d.name=b;d.value=c;return d;}
this.remove=function(a){var b=0;var c=null;for(b=0;b<list.length;b++){if(c!=null) list[b-1]=list[b];if((c==null)&&(b==a)) c=list[b];}if(c!=null) list.length=list.length-1;return c;}
this.clear=function(){list.length=0;}
this.filter=function(a,b){var c=0;var d=new HpAttributeList();for(c=0;c<list.length;c++){if((a!=null)&&(a!=list[c].element)) continue;if((b!=null)&&(b!=list[c].name)) continue;d.add(list[c]);}return d;}
this.formatFallBack=function(a,b,c){var d=0;var e='';var f='';for(d=0;d<list.length;d++){if((a==null)&&(list[d].element!=null)) continue;if((a!=null)&&(list[d].element!=null)&&(a!=list[d].element)) continue;if((b!=null)&&(list[d].name!=null)&&(b!=list[d].name)) continue;if(list[d].element==null){if(e.length<=0) e=list[d].value;else e=e +c+ list[d].value;}else {if(f.length<=0) f=list[d].value;else f=f +c+ list[d].value;}}if(f.length>0) return f;else return e;}
}
HpAttributeList.create=function(a){var b=0;var c=null;var d=new HpAttributeList();c=HpDom.listChild(a,HpAttribute.TAG_ATTRIBUTE,false);for(b=0;b<c.length;b++) d.add(HpAttribute.create(c[b]));return d;}
function HpPair(key,value)
{
this.key=key;
this.value=value;
this.format=function(a){if((this.key==null)&&(this.value==null)) return '';if(this.key==null) return a+this.value;if(this.value==null) return this.key+a;return this.key+a+this.value;}
}
HpPair.parse=function(a,b,c,d){var e=0;var f=null;if((a==null)||(a.length<=0)) return null;e=a.indexOf(b);if(e==0) return null;else if(e<0) f=new HpPair(a,null);else if(e==a.length-1) f=new HpPair(a.substring(0,e),null);else f=new HpPair(a.substring(0,e),a.substring(e+1,a.length));if(c==true){f.key=HpUtil.trim(f.key);if(f.value!=null) f.value=HpUtil.trim(f.value);if((f.key==null)||(f.key.length<=0)) return null;if((f.value!=null)&&(f.value.length<=0)) f.value=null;}if(d==true){f.key=f.key.toLowerCase();if(f.value!=null) f.value=f.value.toLowerCase();}return f;}
function HpPairList()
{
var list=new Array();
this.size=function(){return list.length;}
this.item=function(a){return list[a];}
this.get=function(a){var b=0;for(b=0;b<list.length;b++){if(list[b].key==a) return list[b].value;}return null;}
this.getItem=function(a){var b=0;for(b=0;b<list.length;b++){if(list[b].key==a) return list[b];}return null;}
this.indexOf=function(a){var b=0;for(b=0;b<list.length;b++){if(list[b].key==a) return b;}return -1;}
this.containsKey=function(a){if(this.indexOf(a)>=0) return true;else return false;}
this.add=function(a){if(a instanceof HpPair) list[list.length]=a;else throw 'No instance of HpPair!';}
this.addNew=function(a,b){var c=new HpPair(a,b);list[list.length]=c;return c;}
this.put=function(a,b){var c=this.getItem(a);if(c==null) return this.addNew(a,b);c.key=a;c.value=b;return c;}
this.remove=function(a){var b=0;var c=null;for(b=0;b<list.length;b++){if(c!=null) list[b-1]=list[b];if((c==null)&&(list[b].key==a)) c=list[b];}if(c!=null) list.length=list.length-1;return c;}
this.clear=function(){list.length=0;}
this.format=function(a,b){var c='';for(i=0;i<list.length;i++){c=c+list[i].format(b);if(i<list.length-1) c=c+a;}return c;}
}
function HpUrl()
{
this.address=null;
this.params=new HpPairList();
this.format=function(){if((this.address==null)&&(this.params.size()<=0)) return '';if(this.address==null) return HpUrl.formatParams(this.params);if(this.params.size()<=0) return HpUrl.formatAddress(this.address);return HpUrl.formatAddress(this.address)+'?'+HpUrl.formatParams(this.params);}
}
HpUrl.formatAddress=function(a){if((a==null)||(a.length<=0)) return '';else return encodeURI(a);}
HpUrl.formatParams=function(a){var b=0;var c='';var d=null;for(b=0;b<a.size();b++){d=a.item(b);c=c+encodeURIComponent(d.key)+'=';if(d.value!=null) c=c+encodeURIComponent(d.value);if(b<a.size()-1) c=c+'&';}return c;}
HpUrl.parse=function(a){var b=0;var c=new HpUrl();if((a==null)||(a.length<=0)||(a=='?')) return c;b=a.indexOf('?');if(b<0) c.address=decodeURI(a);else if(b==a.length-1) c.address=decodeURI(a.substring(0,b));else if(b>0){c.address=decodeURI(a.substring(0,b));c.params=HpUrl.parseParams(a.substring(b+1,a.length));}else c.params=HpUrl.parseParams(a.substring(1,a.length));return c;}
HpUrl.parseParams=function(a){var b=0;var c=null;var d=new HpPairList();if((a==null)||(a.length<=0)) return d;c=a.split('&');for(b=0;b<c.length;b++){pair=HpPair.parse(decodeURIComponent(c[b]),'=',false,false);if(pair!=null) d.add(pair);}return d;}
function HpCurrency(required,format,minLength,maxLength,minimum,maximum,step)
{
this.required=required;
this.format=format;
this.minLength=minLength;
this.maxLength=maxLength;
this.minimum=minimum;
this.maximum=maximum;
this.step=step;
this.execute=function(a,b,c){var d=new HpAction(a,b);d.addInit (HpCurrency.init);d.addRender(HpCurrency.render);d.controls.put(HpConstants.config,this);if(c==null) d.addTarget(a);else d.addTarget(c);HpCurrency.config(d);d.execute();return d;}
}
HpCurrency.executeKeyUp=function(a,b,c){var d=null;var e=new HpAction(a,b);e.addInit (HpCurrency.init);e.addRender(HpCurrency.render);if(c==null) e.addTarget(a);else e.addTarget(c);d=HpCurrency.config(e);d.format=false;d.minLength=null;d.minimum=null;d.step=null;e.execute();return e;}
HpCurrency.executeBlur=function(a,b,c){var d=null;var e=new HpAction(a,b);e.addInit (HpCurrency.init);e.addRender(HpCurrency.render);if(c==null) e.addTarget(a);else e.addTarget(c);d=HpCurrency.config(e);e.execute();return e;}
HpCurrency.register=function(a,b,c,d,e){var f=null;var g=null;var h=new HpPairList();if(b==null) b=HpCurrency.init;if(d==null) d=HpCurrency.render;if(e==null) e=a;g=HpAction.registerAll(a,'keyup',b,c,d,e);f=HpCurrency.config(g);f.format=false;f.minLength=null;f.minimum=null;f.step=null;h.addNew(g.type,g);g=HpAction.registerAll(a,'blur',b,c,d,e);f=HpCurrency.config(g);h.addNew(g.type,g);return h;}
HpCurrency.config=function(a){var b=null;var c=null;if(a.controls.containsKey(HpConstants.config)==false){c=new HpCurrency();c.required=HpDom.containsClass(a.source,HpConstants.required);c.format=(HpDom.containsClass(a.source,HpConstants.plain)==false);b=HpDom.searchClass(a.source,HpConstants.minlength);if(b!=null) c.minLength=HpNumber.extract(b);b=HpDom.searchClass(a.source,HpConstants.maxlength);if(b!=null) c.maxLength=HpNumber.extract(b);b=HpDom.searchClass(a.source,HpConstants.minimum);if(b!=null) c.minimum=HpNumber.extract(b);b=HpDom.searchClass(a.source,HpConstants.maximum);if(b!=null) c.maximum=HpNumber.extract(b);b=HpDom.searchClass(a.source,HpConstants.step);if(b!=null) c.step=HpNumber.extract(b);a.controls.put(HpConstants.config,c);}a.styles.addNew(null,HpConstants.error,HpConstants.error);a.styles.addNew(null,HpConstants.success,HpConstants.success);return c;}
HpCurrency.init=function(a){var b=null;var c=null;if(a.state==false) return;b=HpDom.getText(a.source);c=a.controls.get(HpConstants.config);if(b.length<=0){if(c.required==true){a.state=false;a.messages.addNew(null,HpConstants.error,HpResource.errorRequired);}return;}a.state=HpNumber.checkCurrency(b);if(a.state==false){a.messages.addNew(null,HpConstants.error,HpResource.errorCurrency);return;}HpInit.initLength(a,b,c.minLength,c.maxLength);if(a.state==false) return;HpInit.initRange(a,HpNumber.parseCurrency(b),c.minimum,c.maximum,c.step);if(a.state==false) return;}
HpCurrency.render=function(a){var b=0;var c=null;var d=null;var e=null;if(HpUtil.isNaviKey(a.event)==true) return true;c=HpDom.getText(a.source);d=a.controls.get(HpConstants.config);if((a.state==true)&&(d.format==true)&&(c.length>0)) e=HpNumber.formatCurrency(HpNumber.parseCurrency(c));else e=c;for(b=0;b<a.targets.length;b++){HpRender.renderInit(a.targets[b],a.styles);HpRender.renderText(a.targets[b],e,false);HpRender.renderState(a.targets[b],a.state,a.styles);HpRender.messageRender(a);}return true;}
function HpDecimal(required,format,minLength,maxLength,minimum,maximum,step)
{
this.required=required;
this.format=format;
this.minLength=minLength;
this.maxLength=maxLength;
this.minimum=minimum;
this.maximum=maximum;
this.step=step;
this.execute=function(a,b,c){var d=new HpAction(a,b);d.addInit (HpDecimal.init);d.addRender(HpDecimal.render);d.controls.put(HpConstants.config,this);if(c==null) d.addTarget(a);else d.addTarget(c);HpDecimal.config(d);d.execute();return d;}
}
HpDecimal.executeKeyUp=function(a,b,c){var d=null;var e=new HpAction(a,b);e.addInit (HpDecimal.init);e.addRender(HpDecimal.render);if(c==null) e.addTarget(a);else e.addTarget(c);d=HpDecimal.config(e);d.format=false;d.minLength=null;d.minimum=null;d.step=null;e.execute();return e;}
HpDecimal.executeBlur=function(a,b,c){var d=null;var e=new HpAction(a,b);e.addInit (HpDecimal.init);e.addRender(HpDecimal.render);if(c==null) e.addTarget(a);else e.addTarget(c);d=HpDecimal.config(e);e.execute();return e;}
HpDecimal.register=function(a,b,c,d,e){var f=null;var g=null;var h=new HpPairList();if(b==null) b=HpDecimal.init;if(d==null) d=HpDecimal.render;if(e==null) e=a;g=HpAction.registerAll(a,'keyup',b,c,d,e);f=HpDecimal.config(g);f.format=false;f.minLength=null;f.minimum=null;f.step=null;h.addNew(g.type,g);g=HpAction.registerAll(a,'blur',b,c,d,e);f=HpDecimal.config(g);h.addNew(g.type,g);return h;}
HpDecimal.config=function(a){var b=null;var c=null;if(a.controls.containsKey(HpConstants.config)==false){c=new HpDecimal();c.required=HpDom.containsClass(a.source,HpConstants.required);c.format=(HpDom.containsClass(a.source,HpConstants.plain)==false);b=HpDom.searchClass(a.source,HpConstants.minlength);if(b!=null) c.minLength=HpNumber.extract(b);b=HpDom.searchClass(a.source,HpConstants.maxlength);if(b!=null) c.maxLength=HpNumber.extract(b);b=HpDom.searchClass(a.source,HpConstants.minimum);if(b!=null) c.minimum=HpNumber.extract(b);b=HpDom.searchClass(a.source,HpConstants.maximum);if(b!=null) c.maximum=HpNumber.extract(b);b=HpDom.searchClass(a.source,HpConstants.step);if(b!=null) c.step=HpNumber.extract(b);a.controls.put(HpConstants.config,c);}a.styles.addNew(null,HpConstants.error,HpConstants.error);a.styles.addNew(null,HpConstants.success,HpConstants.success);return c;}
HpDecimal.init=function(a){var b=null;var c=null;if(a.state==false) return;b=HpDom.getText(a.source);c=a.controls.get(HpConstants.config);if(b.length<=0){if(c.required==true){a.state=false;a.messages.addNew(null,HpConstants.error,HpResource.errorRequired);}return;}a.state=HpNumber.checkDecimal(b);if(a.state==false){a.messages.addNew(null,HpConstants.error,HpResource.errorDecimal);return;}HpInit.initLength(a,b,c.minLength,c.maxLength);if(a.state==false) return;HpInit.initRange(a,HpNumber.parseDecimal(b),c.minimum,c.maximum,c.step);if(a.state==false) return;}
HpDecimal.render=function(a){var b=0;var c=null;var d=null;var e=null;if(HpUtil.isNaviKey(a.event)==true) return true;c=HpDom.getText(a.source);d=a.controls.get(HpConstants.config);if((a.state==true)&&(d.format==true)&&(c.length>0)) e=HpNumber.formatDecimal(HpNumber.parseDecimal(c));else e=c;for(b=0;b<a.targets.length;b++){HpRender.renderInit(a.targets[b],a.styles);HpRender.renderText(a.targets[b],e,false);HpRender.renderState(a.targets[b],a.state,a.styles);HpRender.messageRender(a);}return true;}
function HpEntry(){}
HpEntry.wrapper='div';
HpEntry.renderText=function(a,b,c,d,e){var f=null;var g=null;if(b==null) return null;if(c<0){if(d==null) g=document.createElement(HpEntry.wrapper);else g=document.createElement(d);g.appendChild(document.createTextNode(b));HpDom.setAttribute(g,'class','');if((e!=null)&&(e!=true)&&(e!=false)) HpDom.setAttribute(g,'id',e);a.appendChild(g);return g;}f=HpDom.listChild(a,null,false);if(d==null) d=f[c].nodeName;g=document.createElement(d);g.appendChild(document.createTextNode(b));HpDom.setAttribute(g,'class','');if((e!=null)&&(e!=false)){if(e==true) HpDom.setAttribute(g,'id',f[c].id);else HpDom.setAttribute(g,'id',e);}a.replaceChild(g,f[c]);return g;}
function HpInteger(required,format,minLength,maxLength,minimum,maximum,step)
{
this.required=required;
this.format=format;
this.minLength=minLength;
this.maxLength=maxLength;
this.minimum=minimum;
this.maximum=maximum;
this.step=step;
this.execute=function(a,b,c){var d=new HpAction(a,b);d.addInit (HpInteger.init);d.addRender(HpInteger.render);d.controls.put(HpConstants.config,this);if(c==null) d.addTarget(a);else d.addTarget(c);HpInteger.config(d);d.execute();return d;}
}
HpInteger.executeKeyUp=function(a,b,c){var d=null;var e=new HpAction(a,b);e.addInit (HpInteger.init);e.addRender(HpInteger.render);if(c==null) e.addTarget(a);else e.addTarget(c);d=HpInteger.config(e);d.format=false;d.minLength=null;d.minimum=null;d.step=null;e.execute();return e;}
HpInteger.executeBlur=function(a,b,c){var d=null;var e=new HpAction(a,b);e.addInit (HpInteger.init);e.addRender(HpInteger.render);if(c==null) e.addTarget(a);else e.addTarget(c);d=HpInteger.config(e);e.execute();return e;}
HpInteger.register=function(a,b,c,d,e){var f=null;var g=null;var h=new HpPairList();if(b==null) b=HpInteger.init;if(d==null) d=HpInteger.render;if(e==null) e=a;g=HpAction.registerAll(a,'keyup',b,c,d,e);f=HpInteger.config(g);f.format=false;f.minLength=null;f.minimum=null;f.step=null;h.addNew(g.type,g);g=HpAction.registerAll(a,'blur',b,c,d,e);f=HpInteger.config(g);h.addNew(g.type,g);return h;}
HpInteger.config=function(a){var b=null;var c=null;if(a.controls.containsKey(HpConstants.config)==false){c=new HpInteger();c.required=HpDom.containsClass(a.source,HpConstants.required);c.format=(HpDom.containsClass(a.source,HpConstants.plain)==false);b=HpDom.searchClass(a.source,HpConstants.minlength);if(b!=null) c.minLength=HpNumber.extract(b);b=HpDom.searchClass(a.source,HpConstants.maxlength);if(b!=null) c.maxLength=HpNumber.extract(b);b=HpDom.searchClass(a.source,HpConstants.minimum);if(b!=null) c.minimum=HpNumber.extract(b);b=HpDom.searchClass(a.source,HpConstants.maximum);if(b!=null) c.maximum=HpNumber.extract(b);b=HpDom.searchClass(a.source,HpConstants.step);if(b!=null) c.step=HpNumber.extract(b);a.controls.put(HpConstants.config,c);}a.styles.addNew(null,HpConstants.error,HpConstants.error);a.styles.addNew(null,HpConstants.success,HpConstants.success);return c;}
HpInteger.init=function(a){var b=null;var c=null;if(a.state==false) return;b=HpDom.getText(a.source);c=a.controls.get(HpConstants.config);if(b.length<=0){if(c.required==true){a.state=false;a.messages.addNew(null,HpConstants.error,HpResource.errorRequired);}return;}a.state=HpNumber.checkInteger(b);if(a.state==false){a.messages.addNew(null,HpConstants.error,HpResource.errorInteger);return;}HpInit.initLength(a,b,c.minLength,c.maxLength);if(a.state==false) return;HpInit.initRange(a,HpNumber.parseInteger(b),c.minimum,c.maximum,c.step);if(a.state==false) return;}
HpInteger.render=function(a){var b=0;var c=null;var d=null;var e=null;if(HpUtil.isNaviKey(a.event)==true) return true;c=HpDom.getText(a.source);d=a.controls.get(HpConstants.config);if((a.state==true)&&(d.format==true)&&(c.length>0)) e=HpNumber.formatInteger(HpNumber.parseInteger(c));else e=c;for(b=0;b<a.targets.length;b++){HpRender.renderInit(a.targets[b],a.styles);HpRender.renderText(a.targets[b],e,false);HpRender.renderState(a.targets[b],a.state,a.styles);HpRender.messageRender(a);}return true;}
function HpLocale(){}
HpLocale.register=function(a,b,c,d,e){var f=null;var g=new HpPairList();if(d==null) d=HpLocale.render;f=HpAction.registerAll(a,'click',b,c,d,e);g.addNew('click',f);HpLocale.config(f);return g;}
HpLocale.config=function(a){a.targets=null;}
HpLocale.render=function(a){var b=0;var c=0;var d=null;var e=null;for(b=0;b<HpCore.locales.length;b++){if(HpDom.containsClass(a.source,HpCore.locales[b])==true) e=HpCore.locales[b];}for(b=0;b<HpCore.locales.length;b++){if(a.targets==null) d=HpDom.getElementsByClass(document,HpCore.locales[b]);else d=a.targets;for(c=0;c<d.length;c++){if(HpDom.containsClass(d[c],HpConstants.localizer)==true) continue;if(HpCore.locales[b]==e) HpDom.removeClasses(d[c],HpConstants.localize);else HpDom.addClasses(d[c],HpConstants.localize);}}return true;}
function HpRate(required,format,minLength,maxLength,minimum,maximum,step)
{
this.required=required;
this.format=format;
this.minLength=minLength;
this.maxLength=maxLength;
this.minimum=minimum;
this.maximum=maximum;
this.step=step;
this.execute=function(a,b,c){var d=new HpAction(a,b);d.addInit (HpRate.init);d.addRender(HpRate.render);d.controls.put(HpConstants.config,this);if(c==null) d.addTarget(a);else d.addTarget(c);HpRate.config(d);d.execute();return d;}
}
HpRate.executeKeyUp=function(a,b,c){var d=null;var e=new HpAction(a,b);e.addInit (HpRate.init);e.addRender(HpRate.render);if(c==null) e.addTarget(a);else e.addTarget(c);d=HpRate.config(e);d.format=false;d.minLength=null;d.minimum=null;d.step=null;e.execute();return e;}
HpRate.executeBlur=function(a,b,c){var d=null;var e=new HpAction(a,b);e.addInit (HpRate.init);e.addRender(HpRate.render);if(c==null) e.addTarget(a);else e.addTarget(c);d=HpRate.config(e);e.execute();return e;}
HpRate.register=function(a,b,c,d,e){var f=null;var g=null;var h=new HpPairList();if(b==null) b=HpRate.init;if(d==null) d=HpRate.render;if(e==null) e=a;g=HpAction.registerAll(a,'keyup',b,c,d,e);f=HpRate.config(g);f.format=false;f.minLength=null;f.minimum=null;f.step=null;h.addNew(g.type,g);g=HpAction.registerAll(a,'blur',b,c,d,e);f=HpRate.config(g);h.addNew(g.type,g);return h;}
HpRate.config=function(a){var b=null;var c=null;if(a.controls.containsKey(HpConstants.config)==false){c=new HpRate();c.required=HpDom.containsClass(a.source,HpConstants.required);c.format=(HpDom.containsClass(a.source,HpConstants.plain)==false);b=HpDom.searchClass(a.source,HpConstants.minlength);if(b!=null) c.minLength=HpNumber.extract(b);b=HpDom.searchClass(a.source,HpConstants.maxlength);if(b!=null) c.maxLength=HpNumber.extract(b);b=HpDom.searchClass(a.source,HpConstants.minimum);if(b!=null) c.minimum=HpNumber.extract(b);b=HpDom.searchClass(a.source,HpConstants.maximum);if(b!=null) c.maximum=HpNumber.extract(b);b=HpDom.searchClass(a.source,HpConstants.step);if(b!=null) c.step=HpNumber.extract(b);a.controls.put(HpConstants.config,c);}a.styles.addNew(null,HpConstants.error,HpConstants.error);a.styles.addNew(null,HpConstants.success,HpConstants.success);return c;}
HpRate.init=function(a){var b=null;var c=null;if(a.state==false) return;b=HpDom.getText(a.source);c=a.controls.get(HpConstants.config);if(b.length<=0){if(c.required==true){a.state=false;a.messages.addNew(null,HpConstants.error,HpResource.errorRequired);}return;}a.state=HpNumber.checkRate(b);if(a.state==false){a.messages.addNew(null,HpConstants.error,HpResource.errorRate);return;}HpInit.initLength(a,b,c.minLength,c.maxLength);if(a.state==false) return;HpInit.initRange(a,HpNumber.parseRate(b),c.minimum,c.maximum,c.step);if(a.state==false) return;}
HpRate.render=function(a){var b=0;var c=null;var d=null;var e=null;if(HpUtil.isNaviKey(a.event)==true) return true;c=HpDom.getText(a.source);d=a.controls.get(HpConstants.config);if((a.state==true)&&(d.format==true)&&(c.length>0)) e=HpNumber.formatRate(HpNumber.parseRate(c));else e=c;for(b=0;b<a.targets.length;b++){HpRender.renderInit(a.targets[b],a.styles);HpRender.renderText(a.targets[b],e,false);HpRender.renderState(a.targets[b],a.state,a.styles);HpRender.messageRender(a);}return true;}
function HpDate(pattern,required)
{
this.required=required;
this.pattern=null;
this.patternFormated=null;
this.format=true;
this.checkLength=true;
if(pattern==null)
{
this.pattern=HpResource.datePattern.replace(new RegExp(HpResource.dateSeparator.replace('.','\\.'),'g'),'');
this.patternFormated=HpResource.datePattern;
}
else
{
this.pattern=pattern;
this.patternFormated=HpDate.formatPattern(pattern);
}
this.execute=function(a,b,c){var d=new HpAction(a,b);d.addInit (HpDate.init);d.addRender(HpDate.render);d.controls.put(HpConstants.config,this);if(c==null) d.addTarget(a);else d.addTarget(c);HpDate.config(d);d.execute();return d;}
}
HpDate.executeKeyUp=function(a,b,c){var d=null;var e=new HpAction(a,b);e.addInit (HpDate.init);e.addRender(HpDate.render);if(c==null) e.addTarget(a);else e.addTarget(c);d=HpDate.config(e);d.format=false;d.checkLength=false;e.execute();return e;}
HpDate.executeBlur=function(a,b,c){var d=null;var e=new HpAction(a,b);e.addInit (HpDate.init);e.addRender(HpDate.render);if(c==null) e.addTarget(a);else e.addTarget(c);d=HpDate.config(e);e.execute();return e;}
HpDate.register=function(a,b,c,d,e){var f=null;var g=null;var h=new HpPairList();if(b==null) b=HpDate.init;if(d==null) d=HpDate.render;if(e==null) e=a;g=HpAction.registerAll(a,'keyup',b,c,d,e);f=HpDate.config(g);f.format=false;f.checkLength=false;h.addNew(g.type,g);g=HpAction.registerAll(a,'blur',b,c,d,e);f=HpDate.config(g);h.addNew(g.type,g);return h;}
HpDate.config=function(a){var b=0;var c=null;var d=null;var e=null;var f=false;if(a.controls.containsKey(HpConstants.config)==false){d=HpDom.listClass(a.source);for(b=0;b<d.length;b++){if(d[b]==HpConstants.required) f=true;else if(((d[b].length>=2)&&((d[b].substr(0,2)=='dd')||(d[b].substr(0,2)=='MM')))||((d[b].length>=4)&&(d[b].substr(0,4)=='yyyy'))) e=d[b];}c=new HpDate(e,f);a.controls.put(HpConstants.config,c);}a.styles.addNew(null,HpConstants.error,HpConstants.error);a.styles.addNew(null,HpConstants.success,HpConstants.success);return c;}
HpDate.init=function(a){var b=null;var c=null;var d=null;if(a.state==false) return;b=HpDom.getText(a.source);c=a.controls.get(HpConstants.config);if(b.length<=0){if(c.required==true){a.state=false;a.messages.addNew(null,HpConstants.error,HpResource.errorRequired);}return;}if(b.indexOf(HpResource.dateSeparator)>=0) d=c.patternFormated;else d=c.pattern;if((c.checkLength==true)&&(b.length!=d.length)){a.state=false;a.messages.addNew(null,HpConstants.error,HpResource.errorDate);return;}a.state=HpDate.check(d,b);if(a.state==false){a.messages.addNew(null,HpConstants.error,HpResource.errorDate);return;}}
HpDate.render=function(a){var b=0;var c=null;var d=null;var e=null;var f=null;if(HpUtil.isNaviKey(a.event)==true) return true;c=HpDom.getText(a.source);d=a.controls.get(HpConstants.config);if((a.state==true)&&(d.format==true)&&(c.length>0)&&(c.indexOf(HpResource.dateSeparator)<0)){e='';for(b=0;b<d.pattern.length;b++){if((f!=null)&&(f!=d.pattern.charAt(b))) e=e+HpResource.dateSeparator;f=d.pattern.charAt(b);e=e+c.charAt(b);}}else e=c;for(b=0;b<a.targets.length;b++){HpRender.renderInit(a.targets[b],a.styles);HpRender.renderText(a.targets[b],e,false);HpRender.renderState(a.targets[b],a.state,a.styles);HpRender.messageRender(a);}return true;}
HpDate.check=function(a,b){var c=1;var d=0;var e=0;var f=0;var g=null;var h=null;if(b.length>a.length) return false;h=a.substr(0,b.length);h=h.replace(new RegExp('\\.','g'),'\\.');h=h.replace('dd','(0[1-9]|[12][0-9]|3[01])');h=h.replace('d','[0-3]');h=h.replace('MM','(0[1-9]|1[012])');h=h.replace('M','[01]');h=h.replace(new RegExp('y','g'),'[0-9]');if(b.search(h)<0) return false;f=a.indexOf('dd');if((f>=0)&&(f+1<b.length)) c=parseInt(b.substr(f,2),10);f=a.indexOf('MM');if((f>=0)&&(f+1<b.length)) e=parseInt(b.substr(f,2),10)-1;f=a.indexOf('yyyy');if((f>=0)&&(f+3<b.length)) d=parseInt(b.substr(f,4),10);g=new Date();g.setFullYear(d,e,c);return (g.getDate()==c)&&(g.getMonth()==e)&&(g.getFullYear()==d);}
HpDate.formatPattern=function(a){var b=0;var c='';var d=null;for(b=0;b<a.length;b++){if((d!=null)&&(d!=a.charAt(b))) c=c+HpResource.dateSeparator;d=a.charAt(b);c=c+d;}return c;}
function HpTime(pattern,required)
{
this.required=required;
this.pattern=null;
this.patternFormated=null;
this.format=true;
this.checkLength=true;
if(pattern==null)
{
this.pattern=HpResource.timePattern.replace(new RegExp(HpResource.timeSeparator.replace('.','\\.'),'g'),'');
this.patternFormated=HpResource.timePattern;
}
else
{
this.pattern=pattern;
this.patternFormated=HpTime.formatPattern(pattern);
}
this.execute=function(a,b,c){var d=new HpAction(a,b);d.addInit (HpTime.init);d.addRender(HpTime.render);d.controls.put(HpConstants.config,this);if(c==null) d.addTarget(a);else d.addTarget(c);HpTime.config(d);d.execute();return d;}
}
HpTime.executeKeyUp=function(a,b,c){var d=null;var e=new HpAction(a,b);e.addInit (HpTime.init);e.addRender(HpTime.render);if(c==null) e.addTarget(a);else e.addTarget(c);d=HpTime.config(e);d.format=false;d.checkLength=false;e.execute();return e;}
HpTime.executeBlur=function(a,b,c){var d=null;var e=new HpAction(a,b);e.addInit (HpTime.init);e.addRender(HpTime.render);if(c==null) e.addTarget(a);else e.addTarget(c);d=HpTime.config(e);e.execute();return e;}
HpTime.register=function(a,b,c,d,e){var f=null;var g=null;var h=new HpPairList();if(b==null) b=HpTime.init;if(d==null) d=HpTime.render;if(e==null) e=a;g=HpAction.registerAll(a,'keyup',b,c,d,e);f=HpTime.config(g);f.format=false;f.checkLength=false;h.addNew(g.type,g);g=HpAction.registerAll(a,'blur',b,c,d,e);f=HpTime.config(g);h.addNew(g.type,g);return h;}
HpTime.config=function(a){var b=0;var c=null;var d=null;var e=null;var f=false;if(a.controls.containsKey(HpConstants.config)==false){d=HpDom.listClass(a.source);for(b=0;b<d.length;b++){if(d[b]==HpConstants.required) f=true;else if((d[b].length>=2)&&((d[b].substr(0,2)=='HH')||(d[b].substr(0,2)=='mm')||(d[b].substr(0,2)=='ss'))) e=d[b];}c=new HpTime(e,f);a.controls.put(HpConstants.config,c);}a.styles.addNew(null,HpConstants.error,HpConstants.error);a.styles.addNew(null,HpConstants.success,HpConstants.success);return c;}
HpTime.init=function(a){var b=null;var c=null;var d=null;if(a.state==false) return;b=HpDom.getText(a.source);c=a.controls.get(HpConstants.config);if(b.length<=0){if(c.required==true){a.state=false;a.messages.addNew(null,HpConstants.error,HpResource.errorRequired);}return;}if(b.indexOf(HpResource.timeSeparator)>=0) d=c.patternFormated;else d=c.pattern;if((c.checkLength==true)&&(b.length!=d.length)){a.state=false;a.messages.addNew(null,HpConstants.error,HpResource.errorTime);return;}a.state=HpTime.check(d,b);if(a.state==false){a.messages.addNew(null,HpConstants.error,HpResource.errorTime);return;}}
HpTime.render=function(a){var b=0;var c=null;var d=null;var e=null;var f=null;if(HpUtil.isNaviKey(a.event)==true) return true;c=HpDom.getText(a.source);d=a.controls.get(HpConstants.config);if((a.state==true)&&(d.format==true)&&(c.length>0)&&(c.indexOf(HpResource.timeSeparator)<0)){e='';for(b=0;b<d.pattern.length;b++){if((f!=null)&&(f!=d.pattern.charAt(b))) e=e+HpResource.timeSeparator;f=d.pattern.charAt(b);e=e+c.charAt(b);}}else e=c;for(b=0;b<a.targets.length;b++){HpRender.renderInit(a.targets[b],a.styles);HpRender.renderText(a.targets[b],e,false);HpRender.renderState(a.targets[b],a.state,a.styles);HpRender.messageRender(a);}return true;}
HpTime.check=function(a,b){var c=null;if(b.length>a.length) return false;c=a.substr(0,b.length);c=c.replace(new RegExp('\\.','g'),'\\.');c=c.replace('HH','([01][0-9]|2[0-3])');c=c.replace('H','[012]');c=c.replace('mm','([0-5][0-9])');c=c.replace('m','[0-5]');c=c.replace('ss','([0-5][0-9])');c=c.replace('s','[0-5]');return (b.search(c)>=0);}
HpTime.formatPattern=function(a){var b=0;var c='';var d=null;for(b=0;b<a.length;b++){if((d!=null)&&(d!=a.charAt(b))) c=c+HpResource.timeSeparator;d=a.charAt(b);c=c+d;}return c;}
function HpText(required,trim,minLength,maxLength)
{
this.required=required;
this.trim=trim;
this.minLength=minLength;
this.maxLength=maxLength;
this.execute=function(a,b,c){var d=new HpAction(a,b);d.addInit (HpText.init);d.addRender(HpText.render);d.controls.put(HpConstants.config,this);if(c==null) d.addTarget(a);else d.addTarget(c);HpText.config(d);d.execute();return d;}
}
HpText.executeKeyUp=function(a,b,c){var d=null;var e=new HpAction(a,b);e.addInit (HpText.init);e.addRender(HpText.render);if(c==null) e.addTarget(a);else e.addTarget(c);d=HpText.config(e);d.trim=false;d.minLength=null;e.execute();return e;}
HpText.executeBlur=function(a,b,c){var d=null;var e=new HpAction(a,b);e.addInit (HpText.init);e.addRender(HpText.render);if(c==null) e.addTarget(a);else e.addTarget(c);d=HpText.config(e);e.execute();return e;}
HpText.register=function(a,b,c,d,e){var f=null;var g=null;var h=new HpPairList();if(b==null) b=HpText.init;if(d==null) d=HpText.render;if(e==null) e=a;g=HpAction.registerAll(a,'keyup',b,c,d,e);f=HpText.config(g);f.trim=false;f.minLength=null;h.addNew(g.type,g);g=HpAction.registerAll(a,'blur',b,c,d,e);f=HpText.config(g);h.addNew(g.type,g);return h;}
HpText.config=function(a){var b=null;var c=null;if(a.controls.containsKey(HpConstants.config)==false){c=new HpText();c.required=HpDom.containsClass(a.source,HpConstants.required);c.trim=(HpDom.containsClass(a.source,HpConstants.notrim)==false);b=HpDom.searchClass(a.source,HpConstants.minlength);if(b!=null) c.minLength=HpNumber.extract(b);b=HpDom.searchClass(a.source,HpConstants.maxlength);if(b!=null) c.maxLength=HpNumber.extract(b);a.controls.put(HpConstants.config,c);}a.styles.addNew(null,HpConstants.error,HpConstants.error);a.styles.addNew(null,HpConstants.success,HpConstants.success);return c;}
HpText.init=function(a){var b=null;var c=null;if(a.state==false) return;b=HpDom.getText(a.source);c=a.controls.get(HpConstants.config);if(b.length<=0){if(c.required==true){a.state=false;a.messages.addNew(null,HpConstants.error,HpResource.errorRequired);}return;}if((c.trim==true)&&((b.charAt(0)==' ')||(b.charAt(b.length-1)==' '))){a.state=false;a.messages.addNew(null,HpConstants.error,HpResource.errorTrim);return;}HpInit.initLength(a,b,c.minLength,c.maxLength);if(a.state==false) return;}
HpText.render=function(a){var b=0;if(HpUtil.isNaviKey(a.event)==true) return true;for(b=0;b<a.targets.length;b++){HpRender.renderInit(a.targets[b],a.styles);HpRender.renderState(a.targets[b],a.state,a.styles);HpRender.messageRender(a);}return true;}
function HpAction(source,event)
{
this.id=null;
this.name=null;
this.source=null;
this.type=null;
this.init=new Array();
this.action=new Array();
this.render=new Array();
this.targets=new Array();
this.controls=new HpPairList();
this.styles=new HpAttributeList();
this.messages=new HpAttributeList();
this.state=true;
this.event=null;
this.ajax=null;
this.id=++HpAction.count;
this.source=source;
if(event!=null)
{
this.type=event.type;
this.event=new HpEvent(event);
}
this.addInit=function(a){this.init[this.init.length]=a;}
this.addAction=function(a){this.action[this.action.length]=a;}
this.addRender=function(a){this.render[this.render.length]=a;}
this.addTarget=function(a){this.targets[this.targets.length]=a;}
this.setAJAX=function(a){this.ajax=new HpAJAX(this,a);}
this.execute=function(){var a=0;if(HpAction.debug==true) HpTest.log(this,'action','execute: '+this.source.nodeName +' '+ this.source.id);this.messages.clear();this.state=true;for(a=0;a<this.init.length;a++) this.init[a](this);for(a=0;a<this.action.length;a++) this.action[a](this);if(this.ajax==null){for(a=0;a<this.render.length;a++) this.render[a](this);}return this.state;}
}
HpAction.count=0;
HpAction.debug=false;
HpAction.get=function(a,b){var c=HpAction.indexOf(a,b);if(c>=0) return HpCore.actions[c];else return null;}
HpAction.indexOf=function(a,b){var c=0;for(c=0;c<HpCore.actions.length;c++){if((HpCore.actions[c].source==a)&&(HpCore.actions[c].type==b)) return c;}return -1;}
HpAction.execute=function(a){var b=-1;var c=new HpEvent(a);var d=c.target;while((d!=null)&&(b<0)){b=HpAction.indexOf(d,c.type);d=d.parentNode;}if(b<0) return false;HpCore.actions[b].event=c;return HpCore.actions[b].execute();}
HpAction.register=function(a){HpCore.actions[HpCore.actions.length]=a;HpEvent.addEvent(a.source,a.type,HpAction.execute,false);}
HpAction.registerAll=function(a,b,c,d,e,f){var g=new HpAction();g.source=a;g.type=b;if(c!=null){if(c instanceof Array) g.init=c;else g.init[0]=c;}if(d!=null){if(d instanceof Array) g.action=d;else g.action[0]=d;}if(e!=null){if(e instanceof Array) g.render=e;else g.render[0]=e;}if(f!=null){if(f instanceof Array) g.targets=f;else g.targets[0]=f;}HpAction.register(g);return g;}
HpAction.registerRender=function(a,b,c,d){return HpAction.registerAll(a,b,null,null,c,d) }
HpAction.registerAutomatic=function(a){var b=0;var c=null;if(a.nodeType==1){if(HpAction.debug==true){HpAction.registerRender(a,'focus',null,null);HpAction.registerRender(a,'blur',null,null);}if(HpDom.isInput(a)==true){if(HpDom.containsClass(a,HpConstants.integer)==true) c=HpInteger.register(a);else if(HpDom.containsClass(a,HpConstants.decimal)==true) c=HpDecimal.register(a);else if(HpDom.containsClass(a,HpConstants.currency)==true) c=HpCurrency.register(a);else if(HpDom.containsClass(a,HpConstants.rate)==true) c=HpRate.register(a);else if(HpDom.containsClass(a,HpConstants.date)==true) c=HpDate.register(a);else if(HpDom.containsClass(a,HpConstants.time)==true) c=HpTime.register(a);else c=HpText.register(a);}else if(HpDom.isTextArea(a)==true) c=HpText.register(a);else if(HpDom.containsClass(a,HpConstants.localizer)==true) c=HpLocale.register(a);if(c==null) c=new HpPairList();for(b=0;b<HpCore.initStartElement.length;b++) HpCore.initStartElement[b](a,c);}for(b=0;b<a.childNodes.length;b++) HpAction.registerAutomatic(a.childNodes[b]);if(a.nodeType==1){for(b=0;b<HpCore.initEndElement.length;b++) HpCore.initEndElement[b](a,c);}}
HpAJAX.TAG_MESSAGES='Messages';
HpAJAX.TAG_SERVICE_STATE='ServiceState';
HpAJAX.ACTIVEX=['MSXML2.XMLHTTP.5.0','MSXML2.XMLHTTP.4.0','MSXML2.XMLHTTP.3.0','MSXML2.XMLHTTP','Microsoft.XMLHTTP'];
function HpAJAX(action,url)
{
this.action=action;
this.url=HpUrl.parse(url);
this.data=new HpPairList();
this.request=null;
this.response=null;
this.sendGet=function(){HpAJAX.send(this.action,this.url,null);}
this.sendPost=function(){HpAJAX.send(this.action,this.url,this.data);}
}
HpAJAX.send=function(a,b,c){var d=0;try{a.state=false;a.ajax.response=null;a.ajax.request=HpAJAX.createXMLHttpRequest();b.params.put('de.hp.ajax',(new Date()).getTime());if(c==null){a.ajax.request.open('GET',b.format(),true);a.ajax.request.onreadystatechange=process;a.ajax.request.send(null);}else {a.ajax.request.open('POST',b.format(),true);a.ajax.request.onreadystatechange=process;a.ajax.request.setRequestHeader('Content-Type','application/x-www-form-urlencoded;charset=utf-8');a.ajax.request.send(HpUrl.formatParams(c));}}catch(e){a.messages.addNew(null,HpConstants.error,'[Send] ' +HpResource.errorAJAX);for(d=0;d<a.render.length;d++){a.render[d](a);}}function process(){if(a.ajax.request.readyState==4){try{if(a.ajax.request.status==200) HpAJAX.response(a);else throw '[Status] '+HpResource.errorAJAX;}catch(e){a.messages.addNew(null,HpConstants.error,'[Process] '+HpResource.errorAJAX);}finally {for(d=0;d<a.render.length;d++){a.render[d](a);}}}}}
HpAJAX.createXMLHttpRequest=function(){var a=0;var b=null;try{b=new XMLHttpRequest();}catch(e){}if(b!=null) return b;for(a=0;a<HpAJAX.ACTIVEX.length;a++){try{b=new ActiveXObject(HpAJAX.ACTIVEX[a]);}catch(e){}if(b!=null){HpAJAX.ACTIVEX=[HpAJAX.ACTIVEX[a]];return b;}}throw HpResource.errorAJAX+' [Request]';}
HpAJAX.response=function(a){var b=null;var c=null;if((a.ajax.request==null)||(a.ajax.request.responseXML==null)){a.messages.addNew(null,HpConstants.error,'[Response] '+HpResource.errorAJAX);throw HpResource.errorAJAX;}a.ajax.response=HpDom.getChild(a.ajax.request.responseXML,'JSAction');if(a.ajax.response==null){a.messages.addNew(null,HpConstants.error,'[Root] '+HpResource.errorAJAX);throw HpResource.errorAJAX;}b=HpDom.getChild(a.ajax.response,HpAJAX.TAG_SERVICE_STATE);if(b==null){a.messages.addNew(null,HpConstants.error,'[State] '+HpResource.errorAJAX);throw HpResource.errorAJAX;}c=HpAJAX.listMessage(a.ajax.response);if(c!=null) a.messages.addList(c);if(HpAJAX.getServiceState(a.ajax.response)==HpConstants.SERVICE_SUCCESS){a.state=true;}else {a.state=false;a.messages.addNew(null,HpConstants.error,'[Service] '+HpResource.errorAJAX);}}
HpAJAX.getServiceState=function(a){return HpDom.getText(HpDom.getChild(a,HpAJAX.TAG_SERVICE_STATE));}
HpAJAX.listMessage=function(a){var b=HpDom.getChild(a,HpAJAX.TAG_MESSAGES);if(b==null) return null;else return HpAttributeList.create(b);}
function HpInit(){}
HpInit.initLength=function(a,b,c,d){if((c!=null)&&(b.length<c)){a.state=false;a.messages.addNew(null,HpConstants.error,HpResource.minlength +' '+ c);return;}if((d!=null)&&(b.length>d)){a.state=false;a.messages.addNew(null,HpConstants.error,HpResource.maxlength +' '+ d);return;}}
HpInit.initRange=function(a,b,c,d,e){var f=null;if((c!=null)&&(b<c)){a.state=false;a.messages.addNew(null,HpConstants.error,HpResource.minimum +' '+ HpNumber.formatDecimal(c));return;}if((d!=null)&&(b>d)){a.state=false;a.messages.addNew(null,HpConstants.error,HpResource.maximum +' '+ HpNumber.formatDecimal(d));return;}if(e!=null){if(c==null) f=b%e;else f=(b-c)%e;if(f!=0){a.state=false;a.messages.addNew(null,HpConstants.error,HpResource.step +' '+ HpNumber.formatDecimal(e));}return;}}
function HpRender(){}
HpRender.messageRender=function(a){var b=null;var c=null;var d=null;var e=null;var f=new HpPairList();if(HpCore.messages==null) return true;HpCore.messages.removeItems();HpDom.removeClass(HpCore.messages.element,HpConstants.error);HpDom.removeClass(HpCore.messages.element,HpConstants.warn);HpDom.removeClass(HpCore.messages.element,HpConstants.info);HpCore.messages.setVisible(false);for(i=0;i<a.messages.size();i++){if(a.messages.item(i).element==null){if(HpCore.messages.visible==false) HpCore.messages.setVisible(true);c=HpCore.messages.content.element;b=HpCore.messages.addItem(a.messages.item(i).value);}else {c=a.messages.item(i).element;if(f.containsKey(c)==false){HpDom.clearElement(c);f.addNew(c,null);}b=HpRender.renderEntry(c,a.messages.item(i).value);}d=a.messages.item(i).name;HpRender.renderStyle(b,d,null);if(c==HpCore.messages.content.element){if(e==null) e=d;else if(e==HpConstants.info) e=d;else if((e==HpConstants.warn)&&(d==HpConstants.error)) e=d;}}if(e!=null) HpDom.setClasses(HpCore.messages.element,e);return true;}
HpRender.stateRender=function(a){var b=0;var c=null;c=a.controls.get(HpConstants.text);for(b=0;b<a.targets.length;b++){HpRender.renderInit (a.targets[b],a.styles);HpRender.renderText (a.targets[b],c,false);HpRender.renderState(a.targets[b],a.state,a.styles);}return true;}
HpRender.renderEntry=function(a,b){if(HpDom.isInput(a)==true) return HpInput.renderText(a,b,true,HpInput.separator);if(HpDom.isTextArea(a)==true) return HpTextArea.renderText(a,b,true,HpTextArea.separator);if(HpDom.isSelect(a)==true) return HpSelect.renderText(a,b,b,-1);if(HpDom.checkNodeName(a,'ul')==true) return HpEntry.renderText(a,b,-1,'li',null);return HpEntry.renderText(a,b,-1,HpEntry.wrapper,null);}
HpRender.renderText=function(a,b,c){if(HpDom.isInput(a)==true) return HpInput.renderText(a,b,c,HpInput.separator);if(HpDom.isTextArea(a)==true) return HpTextArea.renderText(a,b,c,HpTextArea.separator);if(HpDom.isSelect(a)==true){if(c==true) return HpSelect.renderText(a,b,b,-1);else return HpSelect.renderText(a,b,b,0);}return HpElement.renderText(a,b,c,HpElement.separator);}
HpRender.renderInit=function(a,b){if((b.contains(a,HpConstants.init)==true)||(b.contains(null,HpConstants.init)==true)) HpDom.setClasses(a,b.formatFallBack(a,HpConstants.init,' '));}
HpRender.renderState=function(a,b,c){var d=c.formatFallBack(a,HpConstants.error,' ');var e=c.formatFallBack(a,HpConstants.success,' ');if(b==true){HpDom.removeClasses(a,d);HpDom.addClasses (a,e);}else {HpDom.removeClasses(a,e);HpDom.addClasses (a,d);}return a;}
HpRender.renderStyle=function(a,b,c){if(b instanceof HpAttributeList) HpDom.addClasses(a,b.formatFallBack(a,c,' '));else HpDom.addClasses(a,b);return a;}
HpRender.display=function(a){var b=document.getElementById(a);if(HpDom.containsClass(b,HpConstants.hide)==false) HpDom.addClass(b,HpConstants.hide);else HpDom.removeClass(b,HpConstants.hide);}



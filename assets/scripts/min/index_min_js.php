<?php
if( extension_loaded( "zlib" ) )
{
    ob_start( "ob_gzhandler" );
}
else
{
    ob_start();
}
header( 'Content-Encoding: gzip' );
header( 'Cache-Control: max-age=2678400' );
header( 'Expires: Sun, 08 Jun 2014 14:26:04 GMT' );
header( 'Last-Modified: Wed, 07 May 2014 16:59:56 GMT' );
header( 'Content-type: application/javascript; charset: UTF-8' );
?>
/**
 * Table of contents: 
 * site.js
 * Generated: 2014-05-08 02:26:04
 */


/* Filename: site.js */
function getObjId(a){return obj=document.getElementById(a)}
function trim(a){if(a){for(var b=0;b<a.length;b++)if(-1===" \n\r\t\f\x0B\u00a0\u2000\u2001\u2002\u2003\u2004\u2005\u2006\u2007\u2008\u2009\u200a\u200b\u2028\u2029\u3000".indexOf(a.charAt(b))){a=a.substring(b);break}for(b=a.length-1;0<=b;b--)if(-1===" \n\r\t\f\x0B\u00a0\u2000\u2001\u2002\u2003\u2004\u2005\u2006\u2007\u2008\u2009\u200a\u200b\u2028\u2029\u3000".indexOf(a.charAt(b))){a=a.substring(0,b+1);break}return-1===" \n\r\t\f\x0B\u00a0\u2000\u2001\u2002\u2003\u2004\u2005\u2006\u2007\u2008\u2009\u200a\u200b\u2028\u2029\u3000".indexOf(a.charAt(0))?a:
""}}function isArray(a){return"undefined"==typeof a.length?!1:!0}var w3cookies={date:new Date,create:function(a,b,d){d?(this.date.setTime(this.date.getTime()+864E5*d),d="; expires="+this.date.toGMTString()):d="";document.cookie=a+"="+b+d+"; path=/"},read:function(a){a+="=";for(var b=document.cookie.split(";"),d=0,e;e=b[d];d++){for(;" "==e.charAt(0);)e=e.substring(1,e.length);if(0==e.indexOf(a))return e.substring(a.length,e.length)}return null},erase:function(a){this.create(a,"",-1)}},dhxWins,win;
function openWinModal(a,b,d,e,h,k,g,l,m,n){var f=new dhtmlXWindows;f.enableAutoViewport(!0);f.setImagePath("../lib/dhtmlx_free/dhtmlxWindows/codebase/imgs/");f.attachEvent("onContentLoaded",doOnLoadWin);win=f.createWindow(a,e,h,b,d);win.progressOn();win.denyPark();!0===k&&win.centerOnScreen();win.setModal(!0);win.setText(m);0<g.length&&win.attachStatusBar().setText(g);win.attachURL(l);!1===n&&win.button("close").disable();return f}function doOnLoadWin(){}
function getSelRadio(a){itemSelecionado="";objField=document.getElementsByName(a);if(null!=objField)for(tam=objField.length,x=0;x<tam;x++)radio=objField[x],check=radio.checked,rdValue=radio.value,check&&(itemSelecionado=rdValue);else alert(a+": OBJ N\u00c3O ENCONTRADO");return itemSelecionado}function setRadio(a,b){objField=document.getElementsByName(a);if(null!=objField)for(c=objField.length,x=0;x<c;x++)rdValue=objField[x].value,objField[x].checked=!1,rdValue==b&&(objField[x].checked=!0)}
function getSelCombo(a){var b=-1;a=document.getElementById(a);if(null!=a)if(tipo=a.type,"select-multiple"==tipo){x=0;b=[];for(i=0;i<a.length;i++)if(opt=a.options[i],sel=opt.selected)b[x++]=opt.value;b=b.join(",")}else{var d=a.selectedIndex;0<=d&&(b=a.options[d].value)}return b}function getTextSelCombo(a){var b="";a=document.getElementById(a);null!=a&&(b=a.options[a.selectedIndex].text);return b}
function setCombo(a,b){objField=document.getElementById(a);if(null!=objField){cb=objField.length;for(x=0;x<cb;x++)objField.options[x].selected=!1;tipo=objField.type;if("select-one"==tipo)for(cb=objField.length,x=0;x<cb;x++){if(cbValue=objField.options[x].value,cbValue==b){objField.options[x].selected=!0;break}}else if("select-multiple"==tipo)for(arr=b.split(","),i=0;i<arr.length;i++)for(vl=arr[i],cb=objField.length,x=0;x<cb;x++)cbValue=objField.options[x].value,0<vl.length&&cbValue==vl&&(objField.options[x].selected=
!0)}}function formataOption(a,b,d){objField=document.getElementById(a);arrValores=b.split(",");if(null!=objField)for(cb=objField.length,x=0;x<cb;x++)for(cbValue=objField.options[x].value,w=0;w<arrValores.length;w++)listValue=arrValores[w],listValue==cbValue&&(objField.options[x].className=d,objField.options[x].value=0,objField.options[x].selected=!1)}
function getChecked(a){grupoCheck=document.getElementsByName(a);tam=grupoCheck.length;$c=0;a=[];for(i=0;i<tam;i++)obj=grupoCheck[i],check=obj.checked,vl=obj.value,check&&(a[$c++]=vl);return strCheck=a.join(",")}
function validaSelecionados(a){arr=[];grupoAtual="CHECK_ITEM_"+a;grupoCheck=document.getElementsByName(grupoAtual);checkTodos=document.getElementById("CHECK_TODOS_"+a);checkSelecionados=document.getElementById("CHECK_SELECIONADOS");tam=grupoCheck.length;for(i=itensDoGrupoAtual=0;i<tam;i++)obj=grupoCheck[i],name=obj.name,check=obj.checked,vl=obj.value,check&&(arr.push(vl),grupoAtual==name&&itensDoGrupoAtual++);str=arr.join(",");null!=checkSelecionados&&(checkSelecionados.value=str);itensDoGrupoAtual!=
tam&&!0==checkTodos.checked?checkTodos.checked=!1:0<itensDoGrupoAtual&&itensDoGrupoAtual==tam&&(checkTodos.checked=!0)}
function selectAll(a){grupoCheck=document.getElementsByName("CHECK_ITEM_"+a);checkTodos=document.getElementById("CHECK_TODOS_"+a);checkSelecionados=document.getElementById("CHECK_SELECIONADOS");if(null!=grupoCheck&&null!=checkSelecionados){for(i=0;i<grupoCheck.length;i++)grupoCheck[i].checked=checkTodos.checked;validaSelecionados(a)}else alert("N\u00e3o h\u00e1 itens dispon\u00edveis para sele\u00e7\u00e3o na p\u00e1gina atual!"),null!=checkTodos&&(checkTodos.checked=!1)}
function selectItem(a,b){checkTodos=document.getElementById("CHECK_TODOS_"+b);obj=document.getElementById(a);null!=obj&&(check=obj.checked,check||(checkTodos.checked=!1),validaSelecionados(b))}function OnEnter(a){a=a.keyCode?a.keyCode:a.charCode?a.charCode:a.which?a.which:void 0;if(13==a||9==a)return!0}function focoEnter(a){if(OnEnter(a))try{obj=window.event.srcElement}catch(b){try{obj=event.target}catch(d){}}id=null;null!=obj&&(id=obj.id);return id}
function execEnterTextField(a,b){return OnEnter(a)?(vldTextfield(b),!1):!0}function TeclaEnter(a){null==a&&(a=event);null!=a&&(a=a.which?a.which:a.keyCode,13==a&&Enter())}document.onkeypress=TeclaEnter;function Enter(){};
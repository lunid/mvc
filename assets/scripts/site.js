	//UTIL
	function getObjId(nomeId){
		obj = document.getElementById(nomeId);
		return obj;
	}
	
	function trim(str) {
            
            
		var whitespace = ' \n\r\t\f\x0b\xa0\u2000\u2001\u2002\u2003\u2004\u2005\u2006\u2007\u2008\u2009\u200a\u200b\u2028\u2029\u3000';
		if (str){
			for (var i = 0; i < str.length; i++) {
				if (whitespace.indexOf(str.charAt(i)) === -1) {
					str = str.substring(i);
					break;
				}
			}
			for (i = str.length - 1; i >= 0; i--) {
				if (whitespace.indexOf(str.charAt(i)) === -1) {
					str = str.substring(0, i + 1);
					break;
				}
			}
			return whitespace.indexOf(str.charAt(0)) === -1 ? str : '';
		} else {
			str = '';	
		}
	}
	
	function isArray(o){
	return(typeof(o.length)=="undefined")?false:true;
	}


	//COOKIE
	var w3cookies = {
		date: new Date(),
		// Cria o(s) cookie(s)
		// Forma de uso: w3cookies.create('nome_do_cookie','valor',dias_para_expirar);
		create: function(strName, strValue, intDays) {
			if ( intDays ) {
				this.date.setTime(this.date.getTime()+(intDays*24*60*60*1000));
				var expires = "; expires=" + this.date.toGMTString();
			} else {
				var expires = "";
			}
			document.cookie = strName + "=" + strValue + expires + "; path=/";
		},
		// Ler as informações de um cookie em específico
		// Forma de uso: w3cookies.read('showHideFiltro');
		read: function(strName) {
			var strNameIgual = strName + "=";
			var arrCookies = document.cookie.split(";");
			for ( var i = 0, strCookie; strCookie = arrCookies[i]; i++ ) {
				while ( strCookie.charAt(0) == " ") {
					strCookie = strCookie.substring(1,strCookie.length);
				}
				if ( strCookie.indexOf(strNameIgual) == 0 ) {
					return strCookie.substring(strNameIgual.length,strCookie.length);
				}
			}
			return null;
		},
		// Delete um cookie desejado
		// Forma de uso: w3cookies.erase('nome_do_cookie');
		erase: function(strName) {
			this.create(strName,"",-1);
		}
	}
	
	//WINDHTML
	//PERMITE ABRIR UMA JANELA MODAL
	//É NECESSÁRIO INSERIR O INCLUDE PHP '../lib/includes/headXwindows.php' NA PÁGINA
	
	var dhxWins;
	var win;
	function openWinModal(idWin,w,h,posX,posY,center,msgStatus,url,titulo,btClose){
		//var w=500;
		//var h=410;//USA A ALTURA TOTAL DO FRAME PRINCIPAL
		var dhxWins= new dhtmlXWindows();
		dhxWins.enableAutoViewport(true);
		dhxWins.setImagePath("../lib/dhtmlx_free/dhtmlxWindows/codebase/imgs/");
		dhxWins.attachEvent("onContentLoaded",doOnLoadWin);		
		//dhxWins.attachEvent("onClose",doOnCloseWin);
		/*dhxWins.attachEvent("onClose", function(win){
			dhxWins.window('w1').close();
		});*/		
		
		//DEFINE UMA JANELA MODAL
		win = dhxWins.createWindow(idWin,posX,posY,w,h);	
		win.progressOn();
		win.denyPark();//NÃO PERMITE MINIMIZAR
		//win.center();
		if (center === true) win.centerOnScreen();//CENTRALIZA NA TELA
		win.setModal(true);//DEFINE COMO JANELA MODAL
		win.setText(titulo);
		if (msgStatus.length > 0){
			var sb = win.attachStatusBar();
			sb.setText(msgStatus);
		}		
		//win.allowResize();//PERMITIR REDIMENSIONAR
		//win.allowMove();//PERMITIR MOVER
		//url +="?etc="+new Date().getTime();
		win.attachURL(url);
		//sb = win.attachStatusBar();
		//sb.setText("Responder a mensagem atual");
		//w1.hideHeader();
		if (btClose === false) win.button("close").disable();//DESABILITA BOTÃO FECHAR	
		//dhxWins.attachEvent("onClose", doOnCloseWin);			
		return dhxWins;
	}
	
	function doOnLoadWin(){
		
	}
	
	//RDBUTTON
	function getSelRadio(nome){
		//Retorna a opção selecionada
		itemSelecionado='';
		objField = document.getElementsByName(nome);
		if (objField != null) {
			tam = objField.length;//Número de itens no grupo de rádio
			for(x=0;x<tam;x++){
				radio = objField[x];
				check = radio.checked;
				rdValue = radio.value;
				if (check) {
					itemSelecionado = rdValue;
				}
			}//Fim do loop for			
		} else {
			alert(nome + ': OBJ NÃO ENCONTRADO');	
		}
		return itemSelecionado;
	}
	
	function setRadio(nome,value){
		//Seleciona o valor value no radio atual
		objField = document.getElementsByName(nome);
		if (objField != null) {
			c = objField.length;//Número de itens no grupo de rádio
			//alert(c);
			for(x=0;x<c;x++){
				rdValue = objField[x].value;
				objField[x].checked = false;//Desfaz a seleção do item atual
				//alert(rdValue +" == "+ value);
				if (rdValue == value) {
					//Seleciona a opção atual
					objField[x].checked = true;
					//break;
				}
			}//Fim do loop for
		}//Fim do if objField
	}

	//SELCOMBOBOX
	function getSelCombo(nomeCombo){
		//RETORNA O VALOR DO ID REF À OPÇÃO SELECIONADA
		var id = -1;
		var comboOrig = document.getElementById(nomeCombo);		
		if (comboOrig != null){
			tipo = comboOrig.type;
			if (tipo == 'select-multiple'){
				x=0;
				var arrResp = new Array();
				for(i=0;i<comboOrig.length;i++){
					opt = comboOrig.options[i];
					sel = opt.selected;
					if (sel){
						//A opção atual está selecionada
						//alert(opt.value);
						arrResp[x++]=opt.value;
					}
				}
				id = arrResp.join(',');
				//alert("ID: " + id);
			} else if('select-one') {
				var ind = comboOrig.selectedIndex;			
				if (ind >= 0) id = comboOrig.options[ind].value;
			}
			//alert(id);
		}
		return id;	
	}
	
	function getTextSelCombo(nomeCombo){
		//RETORNA O TEXTO ENTRE <OPTION></OPTION> REF À OPÇÃO SELECIONADA
		var text = '';
		var comboOrig = document.getElementById(nomeCombo);
		if (comboOrig != null){
			var ind = comboOrig.selectedIndex;			
			text = comboOrig.options[ind].text;
			//alert(id);
		}
		return text;	
	}
	
	function setCombo(nome,value){
		//Seleciona o valor value no combobox atual
		objField = document.getElementById(nome);
		if (objField != null) {
			//LIMPA AS OPÇÕES ATUAIS
			cb = objField.length;
			for(x=0;x<cb;x++){
				objField.options[x].selected = false;
			}//Fim do loop fo			
			tipo = objField.type;									
			if (tipo == 'select-one'){
				cb = objField.length;
				for(x=0;x<cb;x++){
					cbValue = objField.options[x].value;
					//alert(cbValue +" == "+ value);
					if (cbValue == value) {
						//alert('foi');
						objField.options[x].selected = true;
						break;
					}
				}//Fim do loop for
			} else if (tipo =='select-multiple'){
				arr = value.split(',');
				for(i=0;i<arr.length;i++){
					vl = arr[i];
					cb = objField.length;
					for(x=0;x<cb;x++){
						cbValue = objField.options[x].value;
						//alert(cbValue +" == "+ vl);
						if (vl.length > 0 && cbValue == vl) {
							//alert('foi');
							objField.options[x].selected = true;
						}
					}//Fim do loop for					
				}
			}//Fim do if tipo		
		}//Fim do if objField
	}
	
	
	function formataOption(nome,listaValor,cssFormat){
		//Formata os itens em listaValor com o css especificado
		objField = document.getElementById(nome);
		arrValores = listaValor.split(',');
		if (objField != null) {
			cb = objField.length;
			for(x=0;x<cb;x++){
				cbValue = objField.options[x].value;
				for(w=0;w<arrValores.length;w++){
					listValue = arrValores[w];
					if (listValue == cbValue) {
						objField.options[x].className=cssFormat;
						objField.options[x].value=0;
						objField.options[x].selected=false;
						//alert(cssFormat);
					}
				}
			}//Fim do loop for
		}//Fim do if objField
	}	
	
	//SELECTCOMBOBOX
	function getChecked(id){
		//RETORNA UMA STRING COM OS VALORES SELECIONADOS NO GRUPO	
		grupoCheck = document.getElementsByName(id);
		tam = grupoCheck.length;
		$c=0;
		var arrCheck = new Array();
		for(i=0;i<tam;i++){
			obj = grupoCheck[i];
			check = obj.checked;
			vl = obj.value;
			if (check) {
				//O CHECKBOX ESTÁ CHECADO
				arrCheck[$c++]=vl;
			}			
		}
		strCheck = arrCheck.join(',');
		return strCheck;
	}
	
	function validaSelecionados(idGrupoCheck){
		//GUARDA OS VALORES SELECIONADOS
		arr = new Array();
		grupoAtual = 'CHECK_ITEM_'+idGrupoCheck;
		grupoCheck = document.getElementsByName(grupoAtual);
		checkTodos = document.getElementById('CHECK_TODOS_'+idGrupoCheck);
		checkSelecionados = document.getElementById('CHECK_SELECIONADOS');
		tam = grupoCheck.length;
		//form = eval('document.form1');
		//itens_form = form.length;
		itensDoGrupoAtual=0;
		for(i=0;i<tam;i++){
			obj = grupoCheck[i];
			//tipo = form.elements[i].type;
			//nome = form.elements[i].name;
			//if (tipo == "checkbox"){
				//obj = grupoCheck[i];
				name = obj.name;
				check = obj.checked;
				vl = obj.value;
				//arrVl = vl.split(';');				
				if (check) {
					//O CHECKBOX ESTÁ CHECADO
					arr.push(vl);
					if (grupoAtual == name) itensDoGrupoAtual++;
				}
				
			//}
		}
		str = arr.join(',');
		if (checkSelecionados != null) checkSelecionados.value=str;
		if (itensDoGrupoAtual != tam && checkTodos.checked == true){
			checkTodos.checked=false;
		} else if (itensDoGrupoAtual > 0 && itensDoGrupoAtual == tam){
			checkTodos.checked=true;
		}
	}
	
	
	function selectAll(idGrupoCheck){
		//Seleciona/desseleciona todos os checkboxes da página
		grupoCheck = document.getElementsByName('CHECK_ITEM_'+idGrupoCheck);
		checkTodos = document.getElementById('CHECK_TODOS_'+idGrupoCheck);
		checkSelecionados = document.getElementById('CHECK_SELECIONADOS');
		if (grupoCheck != null && checkSelecionados != null){
			//alert(CHECK_ALL.checked);
			for(i=0;i < grupoCheck.length;i++){
				grupoCheck[i].checked = checkTodos.checked;
			}		
			validaSelecionados(idGrupoCheck);
		} else {
			alert("Não há itens disponíveis para seleção na página atual!");
			if (checkTodos != null) checkTodos.checked = false;
		}
	}	
	
	function selectItem(id,idGrupoCheck){			
		checkTodos = document.getElementById('CHECK_TODOS_'+idGrupoCheck);
		obj = document.getElementById(id);
		if (obj != null) {
			check = obj.checked;
			if (!check) checkTodos.checked = false;
			validaSelecionados(idGrupoCheck);
		}
	}


	//SUBMIT
	function OnEnter(evt){
		//Retorna true se o usuário presssionou ENTER
		//Diferente da função TeclaEnter, que dispara uma função ao identificar o ENTER
		var key_code = evt.keyCode  ? evt.keyCode  :
						   evt.charCode ? evt.charCode :
						   evt.which    ? evt.which    : void 0;
	
		if (key_code == 13 || key_code == 9){
			//ENTER OU TAB
			return true;
		}
	}
	
	function focoEnter(e){
		//Retorna o id do campo que possui o foco ao ser pressionado ENTER	
		if(OnEnter(e)){
			try {
				//IE
				obj = window.event.srcElement;   
			} catch(e){
				//FF
				try {	
					obj = event.target; 
				} catch(e){
					//alert('Não funcion no FF '+e);		
				}				
			}	
		}
		id=null;
		if (obj != null) id = obj.id;
		return id;
	}
	
	function execEnterTextField(e,id){
		//Executa uma ação ao pressionar enter. 
		//Esse método é usado com campo textfield
		if(OnEnter(e)){
			vldTextfield(id);//Este método deve ser sobrescrito na página onde encontra-se o campo textfield
			return false;
		} else {
			return true;
		}
	}	



	function TeclaEnter(evt) {
		//CAPTURA A TECLA ENTER E EXECUTA A FUNÇÃO Enter()
		//o método Enter deve ser sobrescrito na página
		var keyCode;
		if (evt == null) evt = event;
		if (evt != null){
			keyCode = (evt.which) ? evt.which : evt.keyCode;
			if (keyCode==13) {
				Enter();
			} else {
				//alert('Não foi '+ keyCode);	
			}
		}
	}
	document.onkeypress = TeclaEnter;

	function Enter(){
		//Este método deve ser sobrescrito na página que executa alguma coisa ao pressionar ENTER	
	}	
	
	
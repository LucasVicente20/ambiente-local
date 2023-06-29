
data = new Date();

MASCARA_BLOQUEIO = "9999.99.99.9.9999";
MASCARA_BLOQUEIO_ANO_CORRENTE = data.getFullYear()+".99.99.9.9999";
MASCARA_DOTACAO = "9999.9999.99.9999.9999.9.999.9.9.99.99.9999";
MASCARA_CPF = "999.999.999-99";
MASCARA_CNPJ = "99.999.999/9999-99";
MASCARA_DATA = "99/99/9999";
MASCARA_SOLICITACAO = "9999.9999.9999";

CHARS_STRING_UPCASE =  "ABCDEFGHIJKLMNOPQRSTUVWXYZÇÁÉÍÓÚÀÈÌÒÙÃÕÂÔÛ ";
CHARS_STRING_LOWCASE = "abcdefghijklmnopqrstuvwxyzçáéíóúàèìòùãõâôû ";
CHARS_STRING = CHARS_STRING_UPCASE + CHARS_STRING_LOWCASE;
CHARS_INTEIRO_POSITIVO = "0123456789";
CHARS_INTEIRO = CHARS_INTEIRO_POSITIVO+"-";
CHARS_NUMERO = CHARS_INTEIRO+".,";
CHARS_ALFANUMERICO = CHARS_STRING + CHARS_INTEIRO_POSITIVO;
CHARS_SIMBOLO = "'!@#$%¨&*(),:}{^~/\\\"<>;[]{}§´`'º°ª";

POSICAO_CENTRO = 0;
POSICAO_ESQUERDA = 1;
POSICAO_DIREITA = 2;
POSICAO_ACIMA = 3;
POSICAO_ABAIXO = 4;

function janela( pageToLoad, winName, width, height, center, barra) {
	xposition=0;
	yposition=0;
	if ((parseInt(navigator.appVersion) >= 4 ) && (center)){
		xposition = (screen.width - width) / 2;
		yposition = (screen.height - height) / 2;
	}
	args = "width=" + width + ","
	+ "height=" + height + ","
	+ "location=0,"
	+ "menubar=0,"
	+ "resizable=0,"
	+ "scrollbars=" + barra + ","
	+ "status=0,"
	+ "titlebar=no,"
	+ "toolbar=0,"
	+ "hotkeys=0,"
	+ "z-lock=1," //Netscape Only
	+ "screenx=" + xposition + "," //Netscape Only
	+ "screeny=" + yposition + "," //Netscape Only
	+ "left=" + xposition + "," //Internet Explore Only
	+ "top=" + yposition; //Internet Explore Only
	window.open( pageToLoad,winName,args );
}

/** Função que recarrega a página e informa o botão pressionado
*   (requer o input tipo 'hidden' nome 'Botao' para guardar o nome do botão pressionado) */
function enviarBotao(formulario, botaoPressionado){
	formulario.Botao.value = botaoPressionado;
	formulario.submit();
}
/** Função que atualiza a página e foca em um campo
*   (requer o input tipo 'hidden' nome 'Foco' para guardar o nome do campo a receber foco) */
function atualizarPagina(formulario, valor){
	formulario.Foco.value = valor;
	formulario.submit();
}
/** Preenchenche uma string com n caracteres. exemplo (string, 4, 'X', POSICAO_DIREITA) */
function str_pad (n, len, padding, posicao){
  var sign = '', s = n;

  if (typeof n === 'number'){
     sign = n < 0 ? '-' : '';
     s = Math.abs (n).toString ();
  }

  if ((len -= s.length) > 0){
  if(posicao == POSICAO_DIREITA){
  s = s + Array (len + 1).join (padding || '0');
  }else{
  s = Array (len + 1).join (padding || '0') + s;
  }
  }
  return sign + s;
}
/** formata um numero em float para o formato especificado */
function number_format (number, decimals, dec_point, thousands_sep) {
    number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
    var n = !isFinite(+number) ? 0 : +number,
        prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),        sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
        dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
        s = '',
        toFixedFix = function (n, prec) {
            var k = Math.pow(10, prec);            return '' + Math.round(n * k) / k;
        };
    // Fix for IE parseFloat(0.55).toFixed(0) = 0;
    s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
    if (s[0].length > 3) {        s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
    }
    if ((s[1] || '').length < prec) {
        s[1] = s[1] || '';
        s[1] += new Array(prec - s[1].length + 1).join('0');    }
    return s.join(dec);
}

function float2moeda(num) {
	num = parseFloat(num); // garantindo que o número vindo é float
	// tratando fração para 4 dígitos
	noDigitos =4;
	numeroMoeda = number_format (num, noDigitos, ',','.');
	return numeroMoeda;
}

function moeda2float(moeda){
  moeda = moeda.replace(/\./g,"");
  moeda = moeda.replace(/\,/g,".");
  return parseFloat(moeda);
}

function replaceAll(str, de, para){
    var pos = str.indexOf(de);
    while (pos > -1){
		str = str.replace(de, para);
		pos = str.indexOf(de);
	}
    return (str);
}

function carregamentoDinamico(arquivo, parametros, divRecipiente){
	if (window.XMLHttpRequest){// code for IE7+, Firefox, Chrome, Opera, Safari
	 xmlhttp=new XMLHttpRequest();
	}
	else{// code for IE6, IE5
	 xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
	}
	xmlhttp.onreadystatechange= function(){
	 if (xmlhttp.readyState==4 && xmlhttp.status==200){
	   document.getElementById(divRecipiente).innerHTML = xmlhttp.responseText;
	 }
	};
	link = arquivo+"?"+parametros;
	xmlhttp.open("GET", link ,true);
	xmlhttp.send();
}
/** Funções de formatação */
function formataCpfCnpj(cnpj){
	var resultado = cnpj;
	cnpj = limpaCPFCNPJ(cnpj);
	if(cnpj.length==14){
		resultado = cnpj.substr(0,2)+"."+cnpj.substr(2,3)+"."+cnpj.substr(5,3)+"/"+cnpj.substr(8,4)+"-"+cnpj.substr(12,2);
	}else if(cnpj.length==11){
		resultado = cnpj.substr(0,3)+"."+cnpj.substr(3,3)+"."+cnpj.substr(6,3)+"-"+cnpj.substr(9,2);
	}
	return resultado;
}
function formataBloqueio(num){
	var resultado = num;
	num = removerCaracteresEspeciais(num);
	resultado = num.substr(0,2)+"."+num.substr(2,2)+"."+num.substr(4,1)+"."+num.substr(5,4);
	return resultado;
}

function removerCaracteresEspeciais(string){
	var resultado = string;
	var er = /\^|~|\?|,|\*|\.|\/|\_|\-/g;
	resultado = resultado.replace(er,'');
	return resultado;
}
function isInteiroPositivo(valor){
	var str = valor;
	var resultado = true;
	for(itr=0; itr<str.length; itr++){
		if (CHARS_INTEIRO_POSITIVO.indexOf(str.charAt(itr))==-1){
			resultado = false;
			itr=9999999;
		}
	}
	return resultado;
}
function apenasNumeros(string){
	var resultado = string;
	for(itr=0; itr<resultado.length; itr++){
		if (CHARS_INTEIRO_POSITIVO.indexOf(resultado.charAt(itr))==-1){
			resultado = removerCharEmString(resultado,itr);
			itr--;
		}
	}
	return resultado;
}

function preencherValor(valor,caractere, quantidade, inicioOuFim){
	var resultado = valor;
	for(itr=0;itr<quantidade;itr++){
		resultado = resultado;
	}
return resultado;
}

function limpaCPFCNPJ(cnpj){
	var resultado = cnpj;
	resultado = removerCaracteresEspeciais(resultado);
	return resultado;
}

/** funcão que retorna true se o keyCode recebido pelo evento é permitido para edição de um campo */
function _isEdicaoDeCampoPressed(event){
keyCode= event.keyCode;
  if(
  (keyCode==8 || keyCode==9 || keyCode==13|| keyCode==46) || //backspace, tab, enter, delete
  (keyCode>=37 && keyCode<=40) || //cursor
  ( (event.ctrlKey) && (keyCode==86 || keyCode==67) ) // ctrl C ctrl V
  ){
  resultado = true;
  }else{
  resultado = false;
  }
  return resultado;
}

function _isNumeroPressed(event){
keyCode= event.keyCode;
isShiftPressed= event.shiftKey;
  if(
  (!isShiftPressed && /*keyCode!=16 &&*/ ( (keyCode>47 && keyCode<58) || (keyCode>95 && keyCode<106) ) ) //números
  ){
  resultado = true;
  }else{
  resultado = false;
  }
  return resultado;
}

function campoTipoInteiroPositivo(e) {
	resultado = _keyCodeIsEdicaoDeCampo(e.keyCode);
	if(!resultado){
	resultado = _keyCodeIsNumero(e.keyCode);
	}
	return resultado;
}

/** Métodos extras para classe String */
function alterarCharEmString(string,index,chr) {
	if(index > string.length-1) return string + chr;
	return string.substr(0,index) + chr + string.substr(index+1);
};
function adicionarCharEmString(string,index,chr) {
	if(index > string.length) return string + chr;
	return string.substr(0,index) + chr + string.substr(index);
};
function removerCharEmString(string, index) {
	if(index >= string.length) return string.substr(0,string.length-1);
	return string.substr(0,index) + string.substr(index+1);
}

function contador(classe) {
    var colCount = 0;            
    $('tr.' + classe + ' td').each(function () {
        if ($(this).attr('colspan')) {
            colCount += +$(this).attr('colspan');
        } else {
            colCount++;
        }
    });
    return colCount;
}


/*
**  Returns the caret (cursor) position of the specified text field.
**  Return value range is 0-oField.length.
*/

/*
HTMLInputElement.prototype.getCaretPosition = function (oField) {
oField = this;

  // Initialize
  var iCaretPos = 0;

  // IE Support
  if (document.selection) {

    // Set focus on the element
    oField.focus ();

    // To get cursor position, get empty selection range
    var oSel = document.selection.createRange ();

    // Move selection start to 0 position
    oSel.moveStart ('character', -oField.value.length);

    // The caret position is selection length
    iCaretPos = oSel.text.length;
  }

  // Firefox support
  else if (oField.selectionStart || oField.selectionStart == '0')
    iCaretPos = oField.selectionStart;

  // Return results
  return (iCaretPos);
};


/*
**  Sets the caret (cursor) position of the specified text field.
**  Valid positions are 0-oField.length.
*/

/*


HTMLInputElement.prototype.setCaretPosition = function (iCaretPos) {
oField = this;

  // IE Support
  if (document.selection) {

    // Set focus on the element
    oField.focus ();

    // Create empty selection range
    var oSel = document.selection.createRange ();

    // Move selection start and end to 0 position
    oSel.moveStart ('character', -oField.value.length);

    // Move selection start and end to desired position
    oSel.moveStart ('character', iCaretPos);
    oSel.moveEnd ('character', 0);
    oSel.select ();
  }

  // Firefox support
  else if (oField.selectionStart || oField.selectionStart == '0') {
    oField.selectionStart = iCaretPos;
    oField.selectionEnd = iCaretPos;
    oField.focus ();
  }
};

*/

//Máscaras padrões pelo jQuery
$(document).ready(function() {
  $(".bloqueio").mask(MASCARA_BLOQUEIO, {placeholder:"_"});
  $(".bloqueioAnoCorrente").mask(MASCARA_BLOQUEIO_ANO_CORRENTE, {placeholder:"_"});
  $(".dotacao").mask(MASCARA_DOTACAO, {placeholder:"_"});
  $(".dinheiro4casas").maskMoney( { thousands:'.', decimal:',', precision:4 } );//  $(".dinheiro4casas").maskMoney( { thousands:'.', decimal:',', precision:4 } );
  $(".dinheiroNegativo").maskMoney( { allowNegative: true, thousands:'.', decimal:',', precision:4 } );
  $(".inteiroPositivo").maskMoney( { thousands:'', decimal:'', precision:0 } );
  $(".dinheiro").maskMoney( { thousands:'.', decimal:',', precision:2 } );
  $(".data").mask(MASCARA_DATA,{placeholder:"_"});
  $(".cnpj").mask(MASCARA_CNPJ, {placeholder:"_"});
  $(".solicitacao").mask(MASCARA_SOLICITACAO, {placeholder:"_"});

  // --inteiroPositivo
  // onkeydown- impedir que não-números sejam digitados

  /*
  $(".inteiroPositivo").keydown(
function(event) {
resultado = _isEdicaoDeCampoPressed(event);
if(!resultado){
resultado = _isNumeroPressed(event);
}
return resultado;
}
  );
  // onkeyup- remover caracteres não-números
  $(".inteiroPositivo").keyup(
  function(event) {
if(!isInteiroPositivo(event.target.value)){
var pos = event.target.getCaretPosition();
event.target.value = apenasNumeros(event.target.value);
event.target.setCaretPosition (pos-1);
}
}
  );
  */

});

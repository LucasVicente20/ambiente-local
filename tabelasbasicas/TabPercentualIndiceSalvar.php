<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: TabPercentualIndiceSalvar.php
# Autor:    João Batista Brito
# Data:     23/11/11
# Objetivo: Programa Inclusão de Valores Percentuais por Índice
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/tabelasbasicas/TabPercentualIndiceSalvar.php' );
AddMenuAcesso( '/tabelasbasicas/TabPercentualIndiceSelecionar.php' );

# Variaveis que limitam os anos (ultimos 7 anos)
$anoAtual = date('Y');
$anoIni = $anoAtual - 6;

# Loop para criar array de indices zerados
$arrIndices = array();    
for ($ano=$anoIni; $ano<=$anoAtual; $ano++) {
	for ($mes=1; $mes<=12; $mes++) $arrIndices[$ano][$mes] = '';
}

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST"){
	$IndCorrCodigo 	= $_POST['IndCorrCodigo'];
	$Critica      	= $_POST['Critica'];
	$Botao          = $_POST['Botao'];
	
# Loop em $_POST para carregar valores informados	
	foreach ($_POST as $mesAno => $valor) {
		if (strpos($mesAno,'/')>0 && strpos($mesAno,'chk')===false) {
			//if ($_POST['chk'.$mesAno]!='on') {
				$mesAno = explode('/', $mesAno);
				$arrIndices[$mesAno[1]][$mesAno[0]] = $valor;
			//}
		}
	}
}else{
	$IndCorrCodigo 	= $_GET['IndCorrCodigo'];
	$Critica      	= $_GET['Critica'];
	$Mensagem     	= urldecode($_GET['Mensagem']);
	$Mens         	= $_GET['Mens'];
	$Tipo         	= $_GET['Tipo'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "TabPercentualIndiceSalvar.php";

# Redireciona para a página Salvar #
if( $Botao == "Salvar" ){	
	$Url = "TabPercentualIndiceSalvar.php?IndCorrCodigo=$IndCorrCodigo";
	if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }

	# Critica dos Campos #
	/**
	 * TODO Fazer criticas de dados
	 */
	
	//Inicializa conexao e transacao
	$db = Conexao();
	$db->query("BEGIN TRANSACTION");
	if( PEAR::isError($result) ){
	  ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
	}
	$erro = false;
	
	$sql = "DELETE FROM SFPC.TBVALORINDICE
					 WHERE CINCORSEQU = $IndCorrCodigo
						 AND AVAINDANO >= $anoIni ";
	$result = $db->query($sql);
	if( PEAR::isError($result) ){
		$db->query("ROLLBACK");
		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		$erro = true;
	}
	
	foreach ($arrIndices as $ano => $meses) {
		foreach ($meses as $mes => $indice) {
			if (trim($indice)!='') {
				$indice = str_replace(',', '.', $indice);
				$sql = "INSERT INTO SFPC.TBVALORINDICE (CINCORSEQU, AVAINDANO, AVAINDMES, CUSUPOCODI, VVAINDPERC, TVAINDULAT) 
								VALUES ($IndCorrCodigo, $ano, $mes, ".$_SESSION['_cusupocodi_'].", $indice, now()) ";
				$result = $db->query($sql);
				if( PEAR::isError($result) ){
					$db->query("ROLLBACK");
					ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
					$erro = true;
				}
			}						
		}
	}
	
	if (!$erro) {
		$db->query("COMMIT");
		if (PEAR::isError($result)) ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		
		# Envia mensagem para página selecionar #
		$Mensagem = urlencode("Percentual por Índice Alterado com Sucesso");
		$Url = "TabPercentualIndiceSelecionar.php?Mensagem=$Mensagem&Mens=1&Tipo=1&Critica=0";
		if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
		header("location: ".$Url);
		exit();
	}
	        
	$db->query("END TRANSACTION");
	$db->disconnect();
	
	
}else if( $Botao == "Voltar" ){
	header("location: TabPercentualIndiceSelecionar.php".$Url);
	exit();
} else {
	$db = Conexao();

	
//Seleciona dados de tbvalorindice
	$sql = "SELECT CINCORSEQU, AVAINDANO, AVAINDMES, CUSUPOCODI, VVAINDPERC, TVAINDULAT
						FROM SFPC.TBVALORINDICE
					 WHERE CINCORSEQU = $IndCorrCodigo
						 AND AVAINDANO >= $anoIni ";		
	$result = $db->query($sql);
	if (PEAR::isError($result)) {
		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
	}else{
		while( $linha = $result->fetchRow() ){
			$ano = $linha[1];
			$mes = $linha[2];
			$valor = str_replace('.', ',', $linha[4]);
			$arrIndices[$ano][$mes] = $valor;
		}
	}
	$db->disconnect();
}	

//Seleciona dados de TBINDICECORRECAO
$db     = Conexao();
$sql    = "SELECT EINCORNOME, CINCORSEQU, cincorsiti FROM SFPC.TBINDICECORRECAO WHERE CINCORSEQU = $IndCorrCodigo";
$result = $db->query($sql);
if (PEAR::isError($result)) {
	ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
}else{
	while( $Linha = $result->fetchRow() ){
		$IndCorrDesc   = $Linha[0];
		$IndCorrCodigo = $Linha[1];
		$Situacao      = $Linha[2];
	}
}
$db->disconnect();
	
function mesExtenso($mes) {
	switch ($mes){
		case 1:  $mes = "Janeiro";   break;
		case 2:  $mes = "Fevereiro"; break;
		case 3:  $mes = "Março";     break;
		case 4:  $mes = "Abril";     break;
		case 5:  $mes = "Maio";      break;
		case 6:  $mes = "Junho";     break;
		case 7:  $mes = "Julho";     break;
		case 8:  $mes = "Agosto";    break;
		case 9:  $mes = "Setembro";  break;
		case 10: $mes = "Outubro";   break;
		case 11: $mes = "Novembro";  break;
		case 12: $mes = "Dezembro";  break;
	}
	return $mes;	
}


?>
<html>
<?php 
# Carrega o layout padrão #
layout();
?>
<script type="text/javascript">
<!--
<?php  MenuAcesso(); ?>

function enviar(valor){
	if (valor == 'Voltar' || confirm('Os meses em branco são considerados nulos, Confirma?')) {
		document.Indice.Botao.value = valor;
		document.Indice.submit();
	} 
}

function exibicaoMeses(link, ano) {
	if (link.className == 'anoFechado') {
		link.className = 'anoAberto';
	} else {
		link.className = 'anoFechado';
	}
	var div = document.getElementById('div'+ano);
	if (div.style.display == 'none') {
		div.style.display = 'block';
	} else {
		div.style.display = 'none';
	}
	document.getElementById('1/'+ano).focus();
}

function indiceNulo(indice) {
	var inp = document.getElementById(indice);
	inp.value = '';
	if (document.getElementById('chk'+indice).checked == true) { 
		inp.readOnly = true; 
	}	else {
		inp.readOnly = false;
	}
}

function formatar_moeda(campo, tecla) {
	var sep = 0;
	var key = '';
	var i = j = 0;
	var len = len2 = 0;
	var strCheck = '-0123456789';
	var aux = aux2 = '';
	var whichCode = (window.Event) ? tecla.which : tecla.keyCode;
	var separador_milhar = '.';
	var separador_decimal = ',';
	var ehNegativo = false;
	var valor = campo.value.replace('-','');
	 
	if (whichCode == 13) return true; // Tecla Enter
	if (whichCode == 8) return true; // Tecla Delete
	if (whichCode == 0) return true; // Tecla seta + tab
	key = String.fromCharCode(whichCode); // Pegando o valor digitado
	if (strCheck.indexOf(key) == -1) return false; // Valor inválido (não inteiro)
	if (key == '-') { //Tecla negativo (-)
		key = '';
		ehNegativo = true; 
	}

	if (campo.value.indexOf('-') == 0) ehNegativo = true;  
	
	len = valor.length;
	if ( (len >= campo.maxLength)) return false;
	for(i = 0; i < len; i++)
		if ((valor.charAt(i) != '0') && (valor.charAt(i) != separador_decimal)) break;
	aux = '';
	for(; i < len; i++)
		if (strCheck.indexOf(valor.charAt(i))!=-1) aux += valor.charAt(i);
	aux += key;
	len = aux.length;
	if (len == 0) valor = '';
	if (len == 1) valor = '0'+ separador_decimal + '0' + aux;
	if (len == 2) valor = '0'+ separador_decimal + aux;

	if (len > 2) {
		aux2 = '';

		for (j = 0, i = len - 3; i >= 0; i--) {
			if (j == 3) {
				aux2 += separador_milhar;
				j = 0;
			}
			aux2 += aux.charAt(i);
			j++;
		}

		valor = '';
		len2 = aux2.length;
		for (i = len2 - 1; i >= 0; i--)
			valor += aux2.charAt(i);
		valor += separador_decimal + aux.substr(len - 2, len);
	}

	campo.value = valor; //Preenche campo
	
	if ( (ehNegativo) && (campo.value.indexOf('-') == -1) ) //Tecla negativo (-) 
		campo.value = '-' + valor;			
	
	return false;
}

function CurrencyFormatted(campo) {
	var amount = campo.value; 
	if (amount != '') {
		var i = parseFloat(amount);
		if(isNaN(i)) { i = 0,00; }
		var minus = '';
		if(i < 0) { minus = '-'; }
		i = Math.abs(i);
		i = parseInt((i + .005) * 100);
		i = i / 100;
		s = new String(i);
		if(s.indexOf(',') < 0) { s += ',00'; }
		if(s.indexOf(',') == (s.length - 2)) { s += '0'; }
		s = minus + s;
		campo.value = s;
	}
}
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<style>
.anoFechado {
	font-family: Verdana,sans-serif,Arial; font-size: 8pt; font-weight: normal; font-variant: normal; color: #000000; font-style: normal; line-height: normal; text-decoration: none;
	background: url(../midia/mais.gif) no-repeat center left;
	padding-left: 8px;	
}
.anoAberto {
	font-family: Verdana,sans-serif,Arial; font-size: 8pt; font-weight: normal; font-variant: normal; color: #000000; font-style: normal; line-height: normal; text-decoration: none;
	background: url(../midia/menos.gif) no-repeat center left;
	padding-left: 8px;
}
</style>

<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form name="Indice" id="Indice" method="post" action="TabPercentualIndiceSalvar.php" >
<br><br><br><br><br>
<table cellpadding="3" border="0">
  <!-- Caminho -->
  <tr>
    <td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
    <td align="left" class="textonormal" colspan="2">
      <font class="titulo2">|</font>
      <a href="../index.php"><font color="#000000">Página Principal</font></a> > Tabelas > Percentual Índice > Manter
    </td>
  </tr>
  <!-- Fim do Caminho-->

	<!-- Erro -->
	<?php  if ( $Mens == 1 ) {?>
	<tr>
	  <td width="150"></td>
	  <td align="left" colspan="2"><?php  ExibeMens($Mensagem,$Tipo,1); ?></td>
	</tr>
	<?php  } ?>
	<!-- Fim do Erro -->

	<!-- Corpo -->
	<tr>
		<td width="150"></td>
		<td class="textonormal">
			<table width="100%" border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal"  bgcolor="#FFFFFF">
        <tr>
          <td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
						MANTER - PERCENTUAL ÍNDICE
          </td>
        </tr>
        <tr>
          <td class="textonormal">
             <p align="justify">
             Para atualizar o Índice, preencha os dados abaixo e clique no botão "Salvar". Para retornar ao campo Índice clique no botão "Voltar". 
             </p>
          </td>
        </tr>
        <tr>
          <td>
            <table>
              <tr>
                <td class="textonormal" bgcolor="#DCEDF7">Indice* </td>
               	<td class="textonormal">
               		<?php echo $IndCorrDesc?>
                	<input type="hidden" name="Critica" value="1" />
                	<input type="hidden" name="IndCorrCodigo" value="<?php echo $IndCorrCodigo?>" />
                </td>
              </tr>
            </table>
          </td>
        </tr>
        <tr>
          <td style="padding-left: 7px;">
          	<?php 
          	foreach ($arrIndices as $ano => $meses) {
          		echo "\t<a href='#' onclick=\"javascript: exibicaoMeses(this, '$ano');\" class='anoFechado'>$ano</a>\n";
          		echo "\t<div id='div$ano' style='display: none;'> \n";
          		echo "\t\t<table class='textonormal' style='padding-left:21px;'>\n";
          		foreach ($meses as $mes => $indice) {
          			$mesExtenso = mesExtenso($mes);
          			$maReadOnly = '';
          			$maChecked = '';
          			/*if (trim($indice) == '') {
									$maReadOnly = "readonly='readonly'";
          				$maChecked = "checked='checked'";
          			}*/
          			echo "\t\t\t<tr>\n";
          			
          			echo "\t\t\t\t<td>$mesExtenso</td>\n";
          			echo "\t\t\t\t<td>\n";
          			echo "\t\t\t\t\t<input type='text' id='$mes/$ano' name='$mes/$ano' value='$indice' maxlength='6' size='6' $maReadOnly onkeypress='return formatar_moeda(this,event);' />\n";
          			//echo "\t\t\t\t\t<input type='checkbox' id='chk$mes/$ano' name='chk$mes/$ano' onclick=\"indiceNulo('$mes/$ano')\" $maChecked /><label for='chk$mes/$ano'>Nulo</label><br/>\n";
          			echo "\t\t\t\t\t</td>\n";
								echo "\t\t\t</tr>\n";
          		}
          		echo "\t\t</table>\n";
          		echo "\t</div>\n\t<br/>\n";
          	}
          	?>
          </td>
        </tr>
        <tr>
 	        <td class="textonormal" align="right">
						<input type="button" value="Salvar" class="botao" onclick="javascript:enviar('Salvar');">
						<input name="voltar" type="button" value="Voltar" class="botao" onclick="javascript:enviar('Voltar');">
						<input type="hidden" name="Botao" value="">
					</td>
        </tr>
      </table>
		</td>
	</tr>
	<!-- Fim do Corpo -->
</table>
</form>
</body>
</html>

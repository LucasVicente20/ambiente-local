<?php
# -------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadFaseLicitacaoCofirmar.php
# Autor:    Roberta Costa
# Data:     30/12/04
# Objetivo: Programa que Confirma o Valor Homologado
# OBS.:     Tabulação 2 espaços
# -------------------------------------------------------------------------
# Alterado: Lucas Baracho
# Data:		24/10/2018
# Objetivo: Tarefa Redmine 73662
# -------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST"){
		$ValorHomologado      = $_POST['ValorHomologado'];
}else{
		$ProgramaOrigem	      = urldecode($_GET['ProgramaOrigem']);
//		$ValorHomologado      = converte_valor(sprintf("%01.2f",str_replace(",",".",$_GET['ValorHomologado'])));
		$ValorHomologado      = $_GET['ValorHomologado'];
		$Processo             = $_GET['Processo'];
		$ProcessoAno          = $_GET['ProcessoAno'];
		$ComissaoCodigo       = $_GET['ComissaoCodigo'];
		$OrgaoLicitanteCodigo = $_GET['OrgaoLicitanteCodigo'];
		$ModalidadeCodigo     = $_GET['ModalidadeCodigo'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

# Pega o Valor estimado desse Processo Licitatório #
$db     = Conexao();
$sql    = "SELECT VLICPOVALE FROM SFPC.TBLICITACAOPORTAL ";
$sql   .= " WHERE CLICPOPROC = $Processo AND ALICPOANOP = $ProcessoAno ";
$sql   .= "   AND CGREMPCODI = ".$_SESSION['_cgrempcodi_']." ";
$sql   .= "   AND CCOMLICODI = $ComissaoCodigo AND CORGLICODI = $OrgaoLicitanteCodigo ";


$result = $db->query($sql);
if( PEAR::isError($result) ){
    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
}else{
		$Linha = $result->fetchRow();
		if( $Linha[0] == "" ){
				$ValorEstimado = "0,00";
		}else{
				$ValorEstimado = converte_valor($Linha[0]);
		}
}
$db->disconnect();
?>
<html>
<head>
<title>Portal de Compras - Confirmação Fase</title>
<script language="javascript" type="">
function voltar(){
	self.close();
}
function enviar(){
	if( document.Fase.ProgramaOrigem.value == 'FaseLicitacaoAlterar' ){
		opener.document.<?php echo $ProgramaOrigem; ?>.Botao.value = 'Alterar';
	}else{
		opener.document.<?php echo $ProgramaOrigem; ?>.Botao.value = 'Incluir';
	}
	opener.document.<?php echo $ProgramaOrigem; ?>.Homologacao.value = 'C';
	opener.document.<?php echo $ProgramaOrigem; ?>.submit();
	self.close();
}
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
</head>
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<form action="CadFaseLicitacaoSelecionar.php" method="post" name="Fase">
<table cellpadding="3" border="0" summary="">
	<!-- Corpo -->
	<tr>
		<td class="textonormal"><br>
			<table width="100%" border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" bgcolor="#ffffff" class="textonormal" summary="">
        <tr>
          <td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
	           FASE DE LICITAÇÃO - CONFIRMAR
          </td>
        </tr>
        <tr>
          <td class="textonormal">
             <p align="justify">
             Para confirmar o valor homologado clique no botão "Sim". Para voltar a tela anterior clique no botão "Não".
             </p>
          </td>
        </tr>
        <tr>
          <td>
            <table border="0" summary="">
              <tr>
                <td class="textonormal" bgcolor="#DCEDF7" height="20">Valor Estimado</td>
                <td class="textonormal"><?php echo $ValorEstimado;?></td>
              </tr>
              <tr>
                <td class="textonormal" bgcolor="#DCEDF7" height="20">Valor Homologado</td>
                <td class="textonormal"><?php echo $ValorHomologado;?></td>
              </tr>
            </table>
          </td>
        </tr>
        <tr>
          <td class="textonormal"align="right">
						<input type="hidden" name="ProgramaOrigem" value="<?php echo $ProgramaOrigem; ?>">
         		<!--<input type="hidden" name="ValorHomologado" value="<?php echo $ValorHomologado; ?>">-->
            <input type="button" value="Sim" class="botao" onClick="javascript:enviar();">
            <input type="button" value="Não" class="botao" onClick="javascript:voltar();">
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
<script language="JavaScript">
<!--
window.focus();
//-->
</script>

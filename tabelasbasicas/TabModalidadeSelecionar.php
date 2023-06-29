<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: TabModalidadeSelecionar.php
# Autor:    Rossana Lira
# Data:     03/04/03
# Objetivo: Programa de Manutenção de Modalidade
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/tabelasbasicas/TabModalidadeAlterar.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST" ){
		$Botao      = $_POST['Botao'];
		$Modalidade = $_POST['Modalidade'];
}else{
		$Mens     = $_GET['Mens'];
		$Tipo     = $_GET['Tipo'];
		$Mensagem = urldecode($_GET['Mensagem']);
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

if( $Botao == "Selecionar" ){
		# Critica dos Campos #
		$Mens     = 0;
		$Mensagem = "Informe: ";
    if( $Modalidade == "" ) {
	      $Mens      = 1;
	      $Tipo      = 2;
        $Mensagem .= "<a href=\"javascript: document.Modalidade.Modalidade.focus();\" class=\"titulo2\">Modalidade</a>";
    }else{
    		$Url = "TabModalidadeAlterar.php?Modalidade=$Modalidade";
				if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
	      header("location: ".$Url);
	      exit();
    }
}
?>
<html>
<?
# Carrega o layout padrão #
layout();
?>
<script language="javascript" type="">
<!--
function enviar(valor){
	document.Modalidade.Botao.value = valor;
	document.Modalidade.submit();
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="TabModalidadeSelecionar.php" method="post" name="Modalidade">
<br><br><br><br><br>
<table cellpadding="3" border="0">
  <!-- Caminho -->
  <tr>
    <td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
    <td align="left" class="textonormal" colspan="2">
      <font class="titulo2">|</font>
      <a href="../index.php"><font color="#000000">Página Principal</font></a> > Tabelas > Modalidade > Manter
    </td>
  </tr>
  <!-- Fim do Caminho-->

	<!-- Erro -->
	<?php if ( $Mens == 1 ) {?>
	<tr>
	  <td width="150"></td>
	  <td align="left" colspan="2"><?php ExibeMens($Mensagem,$Tipo,1); ?></td>
	</tr>
	<?php } ?>
	<!-- Fim do Erro -->

	<!-- Corpo -->
	<tr>
		<td width="150"></td>
		<td class="textonormal">
			<table width="100%" border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" bgcolor="#FFFFFF" summary="">
        <tr>
          <td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
	        	MANTER - MODALIDADE
          </td>
        </tr>
        <tr>
          <td class="textonormal" bgcolor="#FFFFFF">
             <p align="justify">
             Para atualizar/excluir uma Modalidade já cadastrada, selecione a Modalidade e clique no botão "Selecionar".
             </p>
          </td>
        </tr>
        <tr>
          <td>
            <table border="0" width="100%" summary="">
              <tr>
                <td class="textonormal" bgcolor="#DCEDF7" width="30%">Modalidade </td>
                <td class="textonormal">
                  <select name="Modalidade" class="textonormal">
                  	<option value="">Selecione uma Modalidade...
                  	<?
                  	# Mostra as modalidades cadastradas #
                		$db  = Conexao();
                		$sql = "SELECT CMODLICODI, AMODLIORDE, EMODLIDESC FROM SFPC.TBMODALIDADELICITACAO ORDER BY EMODLIDESC";
                		$res = $db->query($sql);
                		if( PEAR::isError($res) ){
										    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
										}else{
												while( $Linha = $res->fetchRow() ){
		          	      			echo"<option value=\"$Linha[0]\">$Linha[2]</option>\n";
			                	}
			              }
  	              	$db->disconnect();
      	            ?>
                  </select>
                </td>
              </tr>
            </table>
          </td>
        </tr>
        <tr>
 	        <td class="textonormal" align="right">
          	<input type="button" value="Selecionar" name="Selecionar" class="botao" onClick="javascript:enviar('Selecionar')">
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
<script language="javascript" type="">
<!--
document.Modalidade.Modalidade.focus();
//-->
</script>

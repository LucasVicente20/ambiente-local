<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: TabSituacaoSolicitacaoSelecionar.php
# Autor:    Luiz Alves
# Data:     10/06/11
# Objetivo: Programa de Manutenção de Situação Solicitação - Demanda Redmine: #3281
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/tabelasbasicas/TabSituacaoSolicitacaoAlterar.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST"){
		$SituacaoCodigo = $_POST['SituacaoCodigo'];
		$Botao            = $_POST['Botao'];
}else{
		$Mensagem     = urldecode($_GET['Mensagem']);
		$Mens         = $_GET['Mens'];
		$Tipo         = $_GET['Tipo'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "TabSituacaoSolicitacaoSelecionar.php";

if( $Botao == "Selecionar" ){
		# Critica dos Campos #
		$Mens     = 0;
		$Mensagem = "Informe: ";
    if( $SituacaoCodigo == "" ) {
	      $Mens      = 1;
	      $Tipo      = 2;
        $Mensagem .= "<a href=\"javascript: document.TabSituacaoSolicitacaoSelecionar.SituacaoCodigo.focus();\" class=\"titulo2\">Situação Solicitação </a>";
    }else{
    		$Url = "TabSituacaoSolicitacaoAlterar.php?SituacaoCodigo=$SituacaoCodigo";
				if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
	      header("location: ".$Url);
	      exit();
    }
}
?>
<html>
<?php 
# Carrega o layout padrão #
layout();
?>
<script language="javascript">
<!--
function enviar(valor){
	document.TabSituacaoSolicitacaoSelecionar.Botao.value=valor;
	document.TabSituacaoSolicitacaoSelecionar.submit();
}
<?php  MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="TabSituacaoSolicitacaoSelecionar.php" method="post" name="TabSituacaoSolicitacaoSelecionar">
<br><br><br><br><br>
<table cellpadding="3" border="0">
  <!-- Caminho -->
  <tr>
    <td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
    <td align="left" class="textonormal" colspan="2">
      <font class="titulo2">|</font>
      <a href="../index.php"><font color="#000000">Página Principal</font></a> > Tabelas > Situação Solicitação > Manter
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
	           MANTER - SITUAÇÃO SOLICITAÇÃO
          </td>
        </tr>
        <tr>
          <td class="textonormal" bgcolor="#FFFFFF">
             <p align="justify">
             Para atualizar/excluir uma Situação Solicitação já cadastrada, selecione a Situação Solicitação e clique no botão "Selecionar".
             </p>
          </td>
        </tr>
        <tr>
          <td>
            <table>
              <tr>
                <td class="textonormal" bgcolor="#DCEDF7">Situação Solicitação</td>
                <td class="textonormal">
                  <select name="SituacaoCodigo" class="textonormal">
                  	<option value="">Selecione uma Situação Solicitação...</option>
                  	<!-- Mostra os perfis cadastrados -->
                  	<?php 
                		$db     = Conexao();
                		$sql    = "SELECT CSITSOCODI, ESITSONOME FROM SFPC.TBSITUACAOSOLICITACAO ORDER BY ESITSONOME";
                		$result = $db->query($sql);
                		if( PEAR::isError($result) ){
										    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
										}else{
												while( $Linha = $result->fetchRow() ){
		          	      			echo"<option value=\"$Linha[0]\">$Linha[1]</option>\n";
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
          <td>
   	        <table class="textonormal" border="0" align="right">
              <tr>
      	      	<td>
      	      		<input type="button" value="Selecionar" class="botao" onClick="javascript:enviar('Selecionar');">
 									<input type="hidden" name="Botao" value="">
      	      	</td>
					     </tr>
            </table>
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
<script language="javascript">
<!--
document.TabSituacaoSolicitacaoSelecionar.SituacaoCodigo.focus();
//-->
</script>

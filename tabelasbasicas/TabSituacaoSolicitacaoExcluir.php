<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: TabSituacaoSolicitacaoExcluir.php
# Autor:    Luiz Alves
# Data:     10/06/11
# Objetivo: Programa de Exclusão da Situação Solicitação - Demanda Redmine: #3281
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include '../funcoes.php';

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/tabelasbasicas/TabSituacaoSolicitacaoAlterar.php' );
AddMenuAcesso( '/tabelasbasicas/TabSituacaoSolicitacaoSelecionar.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD']  == "POST"){
		$Botao        = $_POST['Botao'];
		$SituacaoCodigo = $_POST['SituacaoCodigo'];
}else{
		$SituacaoCodigo = $_GET['SituacaoCodigo'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "TabSituacaoSolicitacaoExcluir.php";

# Critica dos Campos #
$db = Conexao();
if( $Botao == "Voltar" ){
		$Url = "TabSituacaoSolicitacaoAlterar.php?SituacaoCodigo=$SituacaoCodigo";
		if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
		header("location: ".$Url);
		exit();
}elseif( $Botao == "Excluir" ){
		$Mens     = 0;
    $Mensagem = "Informe: ";
		if( $SituacaoCodigo <= 10 ){

	   	    $Mensagem = urlencode("Exclusão cancelada, esta situação é padrão do portal e não pode ser excluida.");
			$Url = "TabSituacaoSolicitacaoSelecionar.php?Mensagem=$Mensagem&Mens=1&Tipo=1";
		    if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
	        header("location: ".$Url);
	        exit();
	}
						if( $Mens == 0 ){
								# Exclui Situação #
								$db->query("BEGIN TRANSACTION");
								$sql    = "DELETE FROM SFPC.TBSITUACAOSOLICITACAO WHERE CSITSOCODI = $SituacaoCodigo";
								$result = $db->query($sql);
								if( PEAR::isError($result) ){
										$db->query("ROLLBACK");
										ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
								}else{
										$db->query("COMMIT");
										$db->query("END TRANSACTION");
										$db->disconnect();

										# Envia mensagem para página selecionar #
										$Mensagem = urlencode("Solicitação Excluída com Sucesso");
										$Url = "TabSituacaoSolicitacaoSelecionar.php?Mensagem=$Mensagem&Mens=1&Tipo=1";
										if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
										header("location: ".$Url);
										exit();
								}
				   	}

}

if( $Botao == "" ){
		$sql    = "SELECT ESITSONOME FROM SFPC.TBSITUACAOSOLICITACAO WHERE CSITSOCODI = $SituacaoCodigo";
		$result = $db->query($sql);
		if (PEAR::isError($result)) {
		    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
				while( $Linha = $result->fetchRow() ){
						$SituacaoDescricao = $Linha[0];
				}
		}
}
$db->disconnect();
?>
<html>
<?php 
# Carrega o layout padrão #
layout();
?>
<script language="javascript" type="">
<!--
function enviar(valor){
	document.TabSituacaoSolicitacaoExcluir.Botao.value=valor;
	document.TabSituacaoSolicitacaoExcluir.submit();
}
<?php  MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="TabSituacaoSolicitacaoExcluir.php" method="post" name="TabSituacaoSolicitacaoExcluir">
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
			<table width="100%" border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" bgcolor="#FFFFFF">
        <tr>
          <td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
	           EXCLUIR - SITUAÇÃO SOLICITAÇÃO
          </td>
        </tr>
        <tr>
          <td class="textonormal" >
             <p align="justify">
               Para confirmar a exclusão da Situação Solicitação clique no botão "Excluir", caso contrário clique no botão "Voltar".
             </p>
          </td>
        </tr>
        <tr>
          <td>
            <table>
              <tr>
                <td class="textonormal" bgcolor="#DCEDF7" height="20">Situação Solicitação</td>
               	<td class="textonormal">
               		<?php  echo $SituacaoDescricao; ?>
                	<input type="hidden" name="SituacaoCodigo" value="<?php  echo $SituacaoCodigo ?>">
                </td>
              </tr>
            </table>
          </td>
        </tr>
        <tr>
          <td align="right">
          	<input type="button" value="Excluir" class="botao" onclick="javascript:enviar('Excluir')">
          	<input type="button" value="Voltar"  class="botao" onclick="javascript:enviar('Voltar')">
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

<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: TabOcorrenciaExcluir.php
# Autor:    Roberta Costa
# Data:     22/10/04
# Objetivo: Programa de Exclusão da Ocorrência
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include '../funcoes.php';

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/tabelasbasicas/TabOcorrenciaAlterar.php' );
AddMenuAcesso( '/tabelasbasicas/TabOcorrenciaSelecionar.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD']  == "POST"){
		$Botao        = $_POST['Botao'];
		$OcorrenciaCodigo = $_POST['OcorrenciaCodigo'];
}else{
		$OcorrenciaCodigo = $_GET['OcorrenciaCodigo'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "TabOcorrenciaExcluir.php";

# Critica dos Campos #
$db = Conexao();
if( $Botao == "Voltar" ){
		$Url = "TabOcorrenciaAlterar.php?OcorrenciaCodigo=$OcorrenciaCodigo";
		if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
		header("location: ".$Url);
		exit();
}elseif( $Botao == "Excluir" ){
		$Mens     = 0;
    $Mensagem = "Informe: ";

		# Verifica se o Ocorrência tem algum Fornecedor relacionado #
		$sql    = "SELECT COUNT(*) FROM SFPC.TBFORNECEDOROCORRENCIA WHERE CFORTOCODI = $OcorrenciaCodigo";
		$result = $db->query($sql);
		if (PEAR::isError($result)) {
				$db->query("ROLLBACK");
		    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
				$Linha = $result->fetchRow();
				$Qtd = $Linha[0];
				if( $Qtd > 0 ) {
				    $Mensagem = "Exclusão Cancelada!<br>Ocorrência Relacionado com ($Qtd) Forneceodor(es)";
				    $Url = "TabOcorrenciaSelecionar.php?Mensagem=".urlencode($Mensagem)."&Mens=1&Tipo=2";
						if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
				    header("location: ".$Url);
				    exit();
				}else{
						if( $Mens == 0 ){
								# Exclui Ocorrência #
								$db->query("BEGIN TRANSACTION");
								$sql    = "DELETE FROM SFPC.TBFORNTIPOOCORRENCIA WHERE CFORTOCODI = $OcorrenciaCodigo";
								$result = $db->query($sql);
								if( PEAR::isError($result) ){
										$db->query("ROLLBACK");
										ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
								}else{
										$db->query("COMMIT");
										$db->query("END TRANSACTION");
										$db->disconnect();

										# Envia mensagem para página selecionar #
										$Mensagem = urlencode("Ocorrência Excluída com Sucesso");
										$Url = "TabOcorrenciaSelecionar.php?Mensagem=$Mensagem&Mens=1&Tipo=1";
										if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
										header("location: ".$Url);
										exit();
								}
				   	}
				}
		}
}

if( $Botao == "" ){
		$sql    = "SELECT EFORTODESC FROM SFPC.TBFORNTIPOOCORRENCIA WHERE CFORTOCODI = $OcorrenciaCodigo";
		$result = $db->query($sql);
		if (PEAR::isError($result)) {
		    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
				while( $Linha = $result->fetchRow() ){
						$OcorrenciaDescricao = $Linha[0];
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
	document.Ocorrencia.Botao.value=valor;
	document.Ocorrencia.submit();
}
<?php  MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="TabOcorrenciaExcluir.php" method="post" name="Ocorrencia">
<br><br><br><br><br>
<table cellpadding="3" border="0">
	<!-- Caminho -->
	<tr>
		<td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
		<td align="left" class="textonormal" colspan="2">
			<font class="titulo2">|</font>
			<a href="../index.php"><font color="#000000">Página Principal</font></a> > Tabelas > Ocorrência > Manter
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
	           EXCLUIR - OCORRÊNCIA
          </td>
        </tr>
        <tr>
          <td class="textonormal" >
             <p align="justify">
               Para confirmar a exclusão da Ocorrência clique no botão "Excluir", caso contrário clique no botão "Voltar".
             </p>
          </td>
        </tr>
        <tr>
          <td>
            <table>
              <tr>
                <td class="textonormal" bgcolor="#DCEDF7" height="20">Ocorrência </td>
               	<td class="textonormal">
               		<?php  echo $OcorrenciaDescricao; ?>
                	<input type="hidden" name="OcorrenciaCodigo" value="<?php  echo $OcorrenciaCodigo ?>">
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

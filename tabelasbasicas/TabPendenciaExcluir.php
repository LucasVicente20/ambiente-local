<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: TabPendenciaExcluir.php
# Autor:    Roberta Costa
# Data:     27/12/04
# Objetivo: Programa de Exclusão da Pendência
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include '../funcoes.php';

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/tabelasbasicas/TabPendenciaAlterar.php' );
AddMenuAcesso( '/tabelasbasicas/TabPendenciaSelecionar.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD']  == "POST"){
		$Botao        = $_POST['Botao'];
		$PendenciaCodigo = $_POST['PendenciaCodigo'];
}else{
		$PendenciaCodigo = $_GET['PendenciaCodigo'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "TabPendenciaExcluir.php";

# Critica dos Campos #
$db = Conexao();
if( $Botao == "Voltar" ){
		$Url = "TabPendenciaAlterar.php?PendenciaCodigo=$PendenciaCodigo";
		if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
		header("location: ".$Url);
		exit();
}elseif( $Botao == "Excluir" ){
		$Mens     = 0;
    $Mensagem = "Informe: ";

		# Verifica se o Pendência tem algum Fornecedor relacionado #
		$sql    = "SELECT COUNT(*) FROM SFPC.TBLICITACAOPENDENCIAS WHERE CTIPPECODI = $PendenciaCodigo";
		$result = $db->query($sql);
		if (PEAR::isError($result)) {
				$db->query("ROLLBACK");
		    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
				$Linha = $result->fetchRow();
				$Qtd = $Linha[0];
				if( $Qtd > 0 ) {
				    $Mensagem = "Exclusão Cancelada!<br>Pendência Relacionado com ($Qtd) Forneceodor(es)";
				    $Url = "TabPendenciaSelecionar.php?Mensagem=".urlencode($Mensagem)."&Mens=1&Tipo=2";
						if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
				    header("location: ".$Url);
				    exit();
				}else{
						if( $Mens == 0 ){
								# Exclui Pendência #
								$db->query("BEGIN TRANSACTION");
								$sql    = "DELETE FROM SFPC.TBTIPOPENDENCIA WHERE CTIPPECODI = $PendenciaCodigo";
								$result = $db->query($sql);
								if( PEAR::isError($result) ){
										$db->query("ROLLBACK");
										ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
								}else{
										$db->query("COMMIT");
										$db->query("END TRANSACTION");
										$db->disconnect();

										# Envia mensagem para página selecionar #
										$Mensagem = urlencode("Pendência Excluída com Sucesso");
										$Url = "TabPendenciaSelecionar.php?Mensagem=$Mensagem&Mens=1&Tipo=1";
										if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
										header("location: ".$Url);
										exit();
								}
				   	}
				}
		}
}

if( $Botao == "" ){
		$sql    = "SELECT ETIPPEDESC FROM SFPC.TBTIPOPENDENCIA WHERE CTIPPECODI = $PendenciaCodigo";
		$result = $db->query($sql);
		if (PEAR::isError($result)) {
		    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
				while( $Linha = $result->fetchRow() ){
						$PendenciaDescricao = $Linha[0];
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
	document.Pendencia.Botao.value=valor;
	document.Pendencia.submit();
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="TabPendenciaExcluir.php" method="post" name="Pendencia">
<br><br><br><br><br>
<table cellpadding="3" border="0">
	<!-- Caminho -->
	<tr>
		<td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
		<td align="left" class="textonormal" colspan="2">
			<font class="titulo2">|</font>
			<a href="../index.php"><font color="#000000">Página Principal</font></a> > Tabelas > Pendência > Manter
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
			<table width="100%" border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" bgcolor="#FFFFFF">
        <tr>
          <td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
	           EXCLUIR - PENDÊNCIA
          </td>
        </tr>
        <tr>
          <td class="textonormal" >
             <p align="justify">
               Para confirmar a exclusão da Pendência clique no botão "Excluir", caso contrário clique no botão "Voltar".
             </p>
          </td>
        </tr>
        <tr>
          <td>
            <table>
              <tr>
                <td class="textonormal" bgcolor="#DCEDF7" height="20">Pendência </td>
               	<td class="textonormal">
               		<?php echo $PendenciaDescricao; ?>
                	<input type="hidden" name="PendenciaCodigo" value="<?php echo $PendenciaCodigo ?>">
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

<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: TabTipoCompraExcluir.php
# Autor:    Luiz Alves
# Data:     16/06/11
# Objetivo: Programa de Exclusão do Tipo de Compra - Demanda Redmine: #3281
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------
# Alterado: Luiz Alves
# Data:     20/09/2011
# Objetivo: Correção dos erros - Demanda Redmine: #3651
# Acesso ao arquivo de funções #
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include '../funcoes.php';

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/tabelasbasicas/TabTipoCompraAlterar.php' );
AddMenuAcesso( '/tabelasbasicas/TabTipoCompraSelecionar.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD']  == "POST"){
		$Botao        = $_POST['Botao'];
		$CompraCodigo = $_POST['CompraCodigo'];
		$CompraDescricao = $_POST['CompraDescricao'];
}else{
		 $CompraCodigo = $_GET['CompraCodigo'];

}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "TabTipoCompraExcluir.php";

# Critica dos Campos #
$db = Conexao();
if( $Botao == "Voltar" ){
		header("location: TabTipoCompraSelecionar.php");
		exit();

}elseif( $Botao == "Excluir" ){
		$Mens     = 0;
    $Mensagem = "Informe: ";

	if( $CompraCodigo <= 5 ){

	        /*$Mens      = 1;
			$Tipo      = 2;
			*/
		    $Mensagem = urlencode("Exclusão cancelada, este Tipo de Compra é padrão do portal e não pode ser excluido.");
			$Url = "TabTipoCompraSelecionar.php?Mensagem=$Mensagem&Mens=1&Tipo=1";
		    if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
	        header("location: ".$Url);
	        exit();
	}


				if( $Mens == 0 ){
								# Exclui o Tipo de Compra#
								$db->query("BEGIN TRANSACTION");
								$sql    = "DELETE FROM SFPC.TBTIPOCOMPRA WHERE CTPCOMCODI = $CompraCodigo";
								$result = $db->query($sql);
								if( PEAR::isError($result) ){
										$db->query("ROLLBACK");
										ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
								}else{
										$db->query("COMMIT");
										$db->query("END TRANSACTION");


										# Envia mensagem para página selecionar #
										$Mensagem = urlencode("Tipo de Compra Excluída com Sucesso");
										$Url = "TabTipoCompraSelecionar.php?Mensagem=$Mensagem&Mens=1&Tipo=1";
										if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
										header("location: ".$Url);
										exit();
								}
				   	}


		$db->disconnect();
}

if( $Botao == "" ){
	    $db     = Conexao();
		$sql    = "SELECT ETPCOMNOME FROM SFPC.TBTIPOCOMPRA WHERE CTPCOMCODI = $CompraCodigo";
		$result = $db->query($sql);
		if (PEAR::isError($result)) {
		    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
				while( $Linha = $result->fetchRow() ){
						$CompraDescricao = $Linha[0];

				}
		}
		$db->disconnect();
}

?>
<html>
<?php 
# Carrega o layout padrão #
layout();
?>
<script language="javascript" type="">
<!--
function enviar(valor){
	document.TabTipoCompraExcluir.Botao.value=valor;
	document.TabTipoCompraExcluir.submit();
}
<?php  MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="TabTipoCompraExcluir.php" method="post" name="TabTipoCompraExcluir">
<br><br><br><br><br>
<table cellpadding="3" border="0">
	<!-- Caminho -->
	<tr>
		<td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
		<td align="left" class="textonormal" colspan="2">
			<font class="titulo2">|</font>
			<a href="../index.php"><font color="#000000">Página Principal</font></a> > Tabelas > Tipo de Compra > Manter
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
	           EXCLUIR - TIPO DE COMPRA
          </td>
        </tr>
        <tr>
          <td class="textonormal" >
             <p align="justify">
               Para confirmar a exclusão do Tipo de Compra clique no botão "Excluir", caso contrário clique no botão "Voltar".
             </p>
          </td>
        </tr>
        <tr>
          <td>
            <table>
              <tr>
                <td class="textonormal" bgcolor="#DCEDF7" height="20">Tipo de Compra </td>
               	<td class="textonormal">
               		<?php  echo $CompraDescricao; ?>
                	<input type="hidden" name="CompraCodigo" value="<?php  echo $CompraCodigo ?>">
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

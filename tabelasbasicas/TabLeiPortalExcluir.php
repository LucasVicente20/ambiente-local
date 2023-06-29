<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: TabLeiPortalExcluir.php
# Autor:    Luiz Alves
# Data:     27/06/11
# Objetivo: Programa de Criação de leis do Portal - Demanda Redmine: #3281
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------

# Acesso ao arquivo de Funções #
include '../funcoes.php';

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso('/tabelasbasicas/TabLeiPortalAlterar.php');
AddMenuAcesso('/tabelasbasicas/TabLeiPortalSelecionar.php');

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST"){
	  $Botao         = $_POST['Botao'];
	  $LeiCodigo     = $_POST['LeiCodigo'];
}else{
	  $LeiCodigo     = $_GET['LeiCodigo'];
}


# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "TabLeiPortalExcluir.php";

# Critica dos Campos #
$db = Conexao();
if( $Botao == "Voltar" ){
		$Url = "TabLeiPortalAlterar.php?LeiCodigo=$LeiCodigo";
		if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
		header("location: ".$Url);
		exit();
}elseif( $Botao == "Excluir" ){
	   $Mens     = 0;
    $Mensagem = "Informe: ";
                                 # Exclui Lei do Portal#
								$db->query("BEGIN TRANSACTION");
								$sql    = "DELETE FROM SFPC.TBLEIPORTAL WHERE CTPLEITIPO = $LeiCodigo";
								$result = $db->query($sql);
								if( PEAR::isError($result) ){
										$db->query("ROLLBACK");
										ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
								}else{
										$db->query("COMMIT");
										$db->query("END TRANSACTION");
										$db->disconnect();

										# Envia mensagem para página selecionar #
										$Mensagem = urlencode("Lei Excluída com Sucesso");
										$Url = "TabLeiPortalSelecionar.php?Mensagem=$Mensagem&Mens=1&Tipo=1";
										if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
										header("location: ".$Url);
										exit();
								}
				   	}




if( $Botao == "" ){
		$sql    = "SELECT CLEIPONUME FROM SFPC.TBLEIPORTAL WHERE CTPLEITIPO = $LeiCodigo";
		$result = $db->query($sql);
		if (PEAR::isError($result)) {
		    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
				while( $Linha = $result->fetchRow() ){
						$NdaLei = $Linha[0];
				}
		}
}
$db->disconnect();
?>
<html>
<?
# Carrega o layout padrão #
layout();
?>
<script language="javascript" type="">
<!--
function enviar(valor){
	document.Lei.Botao.value=valor;
	document.Lei.submit();
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="TabLeiPortalExcluir.php" method="post" name="Lei">
<br><br><br><br><br>
<table cellpadding="3" border="0">
	<!-- Caminho -->
	<tr>
		<td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
		<td align="left" class="textonormal" colspan="2">
			<font class="titulo2">|</font>
			<a href="../index.php"><font color="#000000">Página Principal</font></a> > Tabelas > Lei do Portal > Manter
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
	           EXCLUIR - LEI DO PORTAL
	                    </td>
        </tr>
        <tr>
          <td class="textonormal" >
             <p align="justify">
               Para confirmar a exclusão da Lei do Portal clique no botão "Excluir", caso contrário clique no botão "Voltar".
             </p>
          </td>
        </tr>
        <tr>
          <td>
            <table>
              <tr>
                <td class="textonormal" bgcolor="#DCEDF7" height="20">Lei do Portal </td>
               	<td class="textonormal">
               		<?php echo $LeiDescricao; ?>
                	<input type="hidden" name="LeiCodigo" value="<?php echo $LeiCodigo ?>">
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

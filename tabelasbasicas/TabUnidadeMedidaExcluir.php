<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: TabUnidadeMedidaExcluir.php
# Autor:    Roberta Costa
# Data:     31/05/05
# Alterado: Rodrigo Melo
# Data:     22/09/2009 - Alterando o nome das tabelas SFPC.TBPREMATERIAL e SFPC.TBPREMATERIALTIPOSITUACAO para SFPC.TBPREMATERIALSERVICO e BPREMATERIALSERVICOTIPOSITUACAO, respectivamente (CR 2749).
# Objetivo: Programa de Exclusão da Unidade de Medida
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include '../funcoes.php';

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/tabelasbasicas/TabUnidadeMedidaAlterar.php' );
AddMenuAcesso( '/tabelasbasicas/TabUnidadeMedidaSelecionar.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD']  == "POST"){
		$Botao        = $_POST['Botao'];
		$UnidadeMedida = $_POST['UnidadeMedida'];
}else{
		$UnidadeMedida = $_GET['UnidadeMedida'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

# Critica dos Campos #
$db = Conexao();
if( $Botao == "Voltar" ){
		$Url = "TabUnidadeMedidaAlterar.php?UnidadeMedida=$UnidadeMedida";
		if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
		header("location: ".$Url);
		exit();
}elseif( $Botao == "Excluir" ){
		$Mens     = 0;
    $Mensagem = "Informe: ";

		# Verifica se a Unidade de Medida tem algum Pré-Material relacionado #
		$sql    = "SELECT COUNT(CPREMACODI) FROM SFPC.TBPREMATERIALSERVICO WHERE CUNIDMCODI = $UnidadeMedida";
		$result = $db->query($sql);
		if (PEAR::isError($result)) {
				$db->query("ROLLBACK");
		    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
				$Linha = $result->fetchRow();
				$Qtd   = $Linha[0];
				if( $Qtd > 0 ) {
				    $Mensagem = "Exclusão Cancelada!<br>Unidade de Medida Relacionada com ($Qtd) Pré-Material(ais)";
				    $Url = "TabUnidadeMedidaSelecionar.php?Mensagem=".urlencode($Mensagem)."&Mens=1&Tipo=2";
						if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
				    header("location: ".$Url);
				    exit();
				    exit;
				}else{
						# Verifica se a Unidade de Medida tem algum Material relacionado #
						$sql    = "SELECT COUNT(CMATEPSEQU) FROM SFPC.TBMATERIALPORTAL WHERE CUNIDMCODI = $UnidadeMedida";
						$result = $db->query($sql);
						if (PEAR::isError($result)) {
								$db->query("ROLLBACK");
						    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
						}else{
								$Linha = $result->fetchRow();
								$Qtd   = $Linha[0];
								if( $Qtd > 0 ) {
										$Mens     = 0;
								    $Mensagem = "Exclusão Cancelada!<br>Unidade de Medida Relacionada com ($Qtd) Material(ais)";
								    $Url = "TabUnidadeMedidaSelecionar.php?Mensagem=".urlencode($Mensagem)."&Mens=1&Tipo=2";
										if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
								    header("location: ".$Url);
								    exit();
								}else{
										if( $Mens == 0 ){
												# Exclui Unidade de Medida #
												$db->query("BEGIN TRANSACTION");
												$sql    = "DELETE FROM SFPC.TBUNIDADEDEMEDIDA WHERE CUNIDMCODI = $UnidadeMedida";
												$result = $db->query($sql);
												if( PEAR::isError($result) ){
														$db->query("ROLLBACK");
														ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
												}else{
														$db->query("COMMIT");
														$db->query("END TRANSACTION");
														$db->disconnect();

														# Envia mensagem para página selecionar #
														$Mensagem = urlencode("Unidade de Medida Excluída com Sucesso");
														$Url = "TabUnidadeMedidaSelecionar.php?Mensagem=$Mensagem&Mens=1&Tipo=1";
														if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
														header("location: ".$Url);
														exit();
												}
								   	}
								}
						}
				}
		}
}

if( $Botao == "" ){
		$sql    = "SELECT EUNIDMSIGL, EUNIDMDESC FROM SFPC.TBUNIDADEDEMEDIDA WHERE CUNIDMCODI = $UnidadeMedida";
		$result = $db->query($sql);
		if (PEAR::isError($result)) {
		    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
				while( $Linha = $result->fetchRow() ){
						$Sigla		 = $Linha[0];
						$Descricao = $Linha[1];
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
	document.UnidadeMedida.Botao.value=valor;
	document.UnidadeMedida.submit();
}
<?php  MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="TabUnidadeMedidaExcluir.php" method="post" name="UnidadeMedida">
<br><br><br><br><br>
<table cellpadding="3" border="0">
	<!-- Caminho -->
	<tr>
		<td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
		<td align="left" class="textonormal" colspan="2">
			<font class="titulo2">|</font>
			<a href="../index.php"><font color="#000000">Página Principal</font></a> > Tabelas > Estoques > Unidade de Medida > Manter
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
	           EXCLUIR - UNIDADE DE MEDIDA
          </td>
        </tr>
        <tr>
          <td class="textonormal" >
             <p align="justify">
               Para confirmar a exclusão da Unidade de Medida clique no botão "Excluir", caso contrário clique no botão "Voltar".
             </p>
          </td>
        </tr>
        <tr>
          <td>
            <table>
              <tr>
                <td class="textonormal" bgcolor="#DCEDF7" height="20">Sigla</td>
               	<td class="textonormal">
               		<?php  echo $Sigla; ?>
                	<input type="hidden" name="Sigla" value="<?php  echo $Sigla ?>">
                </td>
              </tr>
                <td class="textonormal" bgcolor="#DCEDF7" height="20">Descrição</td>
               	<td class="textonormal">
               		<?php  echo $Descricao; ?>
                	<input type="hidden" name="Descricao" value="<?php  echo $Descricao ?>">
                	<input type="hidden" name="UnidadeMedida" value="<?php  echo $UnidadeMedida ?>">
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

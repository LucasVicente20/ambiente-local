<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: TabCertidaoFiscalAlterar.php
# Autor:    Rossana Lira
# Data:     10/02/05
# Objetivo: Programa de Alteração da Certidão Fiscal
# Alterado: Rossana Lira
# Data:     30/05/2007 - Aumento do tamanho do campo para recebimento (70)
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include '../funcoes.php';

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/tabelasbasicas/TabCertidaoFiscalExcluir.php' );
AddMenuAcesso( '/tabelasbasicas/TabCertidaoFiscalSelecionar.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST"){
		$Botao               = $_POST['Botao'];
		$Critica             = $_POST['Critica'];
		$CertidaoCodigo   	 = $_POST['CertidaoCodigo'];
		$CertidaoDescricao	 = strtoupper2(trim($_POST['CertidaoDescricao']));
}else{
		$CertidaoCodigo    	 = $_GET['CertidaoCodigo'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "TabCertidaoFiscalAlterar.php";

# Redireciona para a página de excluir #
if( $Botao == "Excluir" ){
		$Url = "TabCertidaoFiscalExcluir.php?CertidaoCodigo=$CertidaoCodigo";
		if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
	  header("location: ".$Url);
	  exit();
	  exit();
}else if( $Botao == "Voltar" ){
		header("location: TabCertidaoFiscalSelecionar.php");
		exit();
		exit();
}else{
		# Critica dos Campos #
		if( $Critica == 1 ) {
			  $Mens     = 0;
		    $Mensagem = "Informe: ";
		    if( $CertidaoDescricao == "" ) {
			      $Critica   = 1;
		        $LerTabela = 0;
				 	  $Mens      = 1;
				 	  $Tipo      = 2;
				    $Mensagem .= "<a href=\"javascript:document.Certidao.CertidaoDescricao.focus();\" class=\"titulo2\">Certidão</a>";
		    }
		    if( $Mens == 0 ){
						# Verifica a Duplicidade de Certidao #
						$db     = Conexao();
				   	$sql    = "SELECT COUNT(CTIPCECODI) FROM SFPC.TBTIPOCERTIDAO WHERE RTRIM(LTRIM(ETIPCEDESC)) = '$CertidaoDescricao' AND CTIPCECODI <> $CertidaoCodigo";
				 		$result = $db->query($sql);
						if( PEAR::isError($result) ){
						    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
						}else{
								$Linha = $result->fetchRow();
						    $Qtd   = $Linha[0];
				    		if( $Qtd > 0 ) {
						    	$Mens     = 1;
						    	$Tipo     = 2;
									$Mensagem = "<a href=\"javascript:document.Certidao.CertidaoDescricao.focus();\" class=\"titulo2\"> Certidão Já Cadastrada</a>";
								}else{
								    # Verifica a Duplicidade da Ordem #
										$sql    = "SELECT COUNT(CTIPCECODI) FROM SFPC.TBTIPOCERTIDAO";
										$sql   .= " WHERE CTIPCECODI <> $CertidaoCodigo";
								 		$result = $db->query($sql);
										if( PEAR::isError($result) ){
										    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
										}else{
								        # Atualiza Certidao #
								        $Data   = date("Y-m-d H:i:s");
								        $db->query("BEGIN TRANSACTION");
												$sql    = "UPDATE SFPC.TBTIPOCERTIDAO ";
												$sql   .= "   SET ETIPCEDESC = '$CertidaoDescricao', ";
								        $sql   .= "       TTIPCEULAT = '$Data' ";
								        $sql   .= " WHERE CTIPCECODI = $CertidaoCodigo";
								        $result = $db->query($sql);
												if( PEAR::isError($result) ){
														$db->query("ROLLBACK");
												    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
												}else{
														$db->query("COMMIT");
														$db->query("END TRANSACTION");
										        $db->disconnect();

										        # Envia mensagem para página selecionar #
										        $Mensagem = urlencode("Certidão Alterada com Sucesso");
										        $Url = "TabCertidaoFiscalSelecionar.php?Mensagem=$Mensagem&Mens=1&Tipo=1&Critica=0";
														if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
										        header("location: ".$Url);
										        exit();
										    }
								    }
								}
						}
						$db->disconnect();
		    }
		}
}
if( $Critica == 0 ){
		$db     = Conexao();
		$sql    = "SELECT ETIPCEDESC, CTIPCECODI ";
		$sql   .= "  FROM SFPC.TBTIPOCERTIDAO ";
		$sql   .= " WHERE CTIPCECODI = $CertidaoCodigo";
		$result = $db->query($sql);
		if (PEAR::isError($result)) {
		    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
				while( $Linha = $result->fetchRow() ){
						$CertidaoDescricao = $Linha[0];
						$CertidaoCodigo    = $Linha[1];
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
	document.Certidao.Botao.value=valor;
	document.Certidao.submit();
}
<?php  MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="TabCertidaoFiscalAlterar.php" method="post" name="Certidao">
<br><br><br><br><br>
<table cellpadding="3" border="0">
	<!-- Caminho -->
	<tr>
		<td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
		<td align="left" class="textonormal" colspan="2">
			<font class="titulo2">|</font>
			<a href="../index.php"><font color="#000000">Página Principal</font></a> > Tabelas > Certidão > Manter
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
	           MANTER - CERTIDÃO
          </td>
        </tr>
        <tr>
					<td class="textonormal">
						<p align="justify">
						Para atualizar a Certidão, preencha os dados abaixo e clique no botão "Alterar". Para apagar a Certidão clique no botão "Excluir".
						</p>
					</td>
        </tr>
        <tr>
          <td>
            <table>
              <tr>
                <td class="textonormal" bgcolor="#DCEDF7">Certidão*</td>
               	<td class="textonormal">
               		<input type="text" name="CertidaoDescricao" size="80" maxlength="70" value="<?php  echo $CertidaoDescricao; ?>" class="textonormal">
                	<input type="hidden" name="Critica" value="1">
                	<input type="hidden" name="CertidaoCodigo" value="<?php  echo $CertidaoCodigo; ?>">
                </td>
              </tr>
            </table>
          </td>
        </tr>
        <tr>
 	        <td class="textonormal" align="right">
          	<input type="button" value="Alterar" class="botao" onclick="javascript:enviar('Alterar');">
						<input type="button" value="Excluir" class="botao" onclick="javascript:enviar('Excluir');">
            <input type="button" value="Voltar" class="botao" onclick="javascript:enviar('Voltar')">
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
document.Certidao.CertidaoDescricao.focus();
//-->
</script>

<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: TabCertidaoFiscalExcluir.php
# Autor:    Rossana Lira
# Data:     11/02/05
# Objetivo: Programa de Exclusão da Certidão Fiscal
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include '../funcoes.php';

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/tabelasbasicas/TabCertidaoFiscalAlterar.php' );
AddMenuAcesso( '/tabelasbasicas/TabCertidaoFiscalSelecionar.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD']  == "POST"){
		$Botao            = $_POST['Botao'];
		$Critica          = $_POST['Critica'];
		$CertidaoCodigo = $_POST['CertidaoCodigo'];
}else{
		$CertidaoCodigo = $_GET['CertidaoCodigo'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "TabCertidaoFiscalExcluir.php";

if( $Botao == "Voltar" ){
		$Url = "TabCertidaoFiscalAlterar.php?CertidaoCodigo=$CertidaoCodigo";
		if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
		header("location: ".$Url);
		exit();
}else{
		# Critica dos Campos #
		if( $Critica == 1 ) {
				$Mens     = 0;
		    $Mensagem = "Informe: ";

		    # Verifica se a Certidão está relacionada com alguma licitação #
		    $db     = Conexao();
		    $sql    = "SELECT COUNT(*) FROM SFPC.TBPREFORNCERTIDAO WHERE CTIPCECODI = $CertidaoCodigo";
				$result = $db->query($sql);
				if (PEAR::isError($result)) {
				    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
				}else{
						$Linha        = $result->fetchRow();
					 	$QtdInscrito 	= $Linha[0];
				    if( $QtdInscrito > 0 ){
				        $Mensagem = urlencode("Exclusão Cancelada!<br>Certidão Relacionada com ($QtdInscrito) Inscrito(s)");
				        $Url = "TabCertidaoFiscalSelecionar.php?Mensagem=$Mensagem&Mens=1&Tipo=2";
								if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
				        header("location: ".$Url);
				        exit();
				    } else {
							$Linha        = $result->fetchRow();
						 	$QtdFornecedor= $Linha[0];
					    if( $QtdFornecedor > 0 ){
					        $Mensagem = urlencode("Exclusão Cancelada!<br>Certidão Relacionada com ($QtdFornecedor) Fornecedor(es)");
					        $Url = "TabCertidaoFiscalSelecionar.php?Mensagem=$Mensagem&Mens=1&Tipo=2";
									if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
					        header("location: ".$Url);
					        exit();
					    }
						}
				    if( $Mens == 0 ){
								# Exclui Certidão #
								$db->query("BEGIN TRANSACTION");
								$sql = "DELETE FROM SFPC.TBTIPOCERTIDAO WHERE CTIPCECODI = $CertidaoCodigo";
								$result = $db->query($sql);
								if( PEAR::isError($result) ){
										$db->query("ROLLBACK");
								    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
								}else{
										$db->query("COMMIT");
										$db->query("END TRANSACTION");
						        $db->disconnect();

										# Envia mensagem para página selecionar #
							      $Mens      = 1;
							      $Tipo      = 2;
										$Mensagem = urlencode("Certidão Excluída com Sucesso");
										$Url = "TabCertidaoFiscalSelecionar.php?Mensagem=$Mensagem&Mens=1&Tipo=1";
										if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
										header("location: ".$Url);
										exit();
								}
				    }
				}
				$db->disconnect();
		}
}

if( $Critica == 0 ){
		$db     = Conexao();
		$sql    = "SELECT ETIPCEDESC,FTIPCEOBRI FROM SFPC.TBTIPOCERTIDAO WHERE CTIPCECODI = $CertidaoCodigo";
		$result = $db->query($sql);
		if( PEAR::isError($result) ){
		    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
				while( $Linha = $result->fetchRow() ){
						$CertidaoDescricao = $Linha[0];
						$Obrigatoriedade	 = $Linha[1];
				}
		}
		if ($Obrigatoriedade	== "S") {
			$Obrigatoriedade = "SIM";
		} else {
			$Obrigatoriedade = "NÃO";
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
<form action="TabCertidaoFiscalExcluir.php" method="post" name="Certidao">
<br><br><br><br><br>
<table cellpadding="3" border="0">
	<!-- Caminho -->
	<tr>
		<td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
		<td align="left" class="textonormal" colspan="2">
			<font class="titulo2">|</font>
			<a href="../index.php"><font color="#000000">Página Principal</font></a> > Tabelas > Certidão >  Manter
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
	        	EXCLUIR - CERTIDÃO
          </td>
        </tr>
        <tr>
          <td class="textonormal" >
             <p align="justify">
               Para confirmar a exclusão da Certidão clique no botão "Excluir", caso contrário clique no botão "Voltar".
             </p>
          </td>
        </tr>
        <tr>
          <td>
            <table>
              <tr>
                <td class="textonormal" bgcolor="#DCEDF7" height="20">Certidão</td>
               	<td class="textonormal">
               		<?php  echo $CertidaoDescricao; ?>
                	<input type="hidden" name="Critica" value="1">
                	<input type="hidden" name="CertidaoCodigo" value="<?php  echo $CertidaoCodigo; ?>">
                </td>
              </tr>
              <tr>
								<td class="textonormal"  bgcolor="#DCEDF7" height="20">Obrigatoriedade</td>
								<td class="textonormal"><?php  echo $Obrigatoriedade; ?></td>
              </tr>
            </table>
          </td>
        </tr>
        <tr>
        	<td class="textonormal" align="right">
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

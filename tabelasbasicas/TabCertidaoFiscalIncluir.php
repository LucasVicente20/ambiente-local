<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: TabCertidaoFiscalIncluir.php
# Autor:    Rossana Lira
# Data:     11/02/05
# Objetivo: Programa de Inclusão de Certidão Fiscal
# Alterado: Rossana Lira
# Data:     30/05/2007 - Aumento do tamanho do campo para recebimento (70)
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST"){
		$Critica              = $_POST['Critica'];
		$CertidaoDescricao 		= strtoupper2(trim($_POST['CertidaoDescricao']));
		$Obrigatoriedade      = $_POST['Obrigatoriedade'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "TabCertidaoFiscalIncluir.php";

# Critica dos Campos #
if( $Critica == 1 ) {
		$Mens = 0;
		$Mensagem = "Informe: ";
	  if( $CertidaoDescricao == "" ) {
		    $Mens      = 1;
		    $Tipo      = 2;
  			$Mensagem .= "<a href=\"javascript:document.Certidao.CertidaoDescricao.focus();\" class=\"titulo2\">Certidão</a>";
    }
	  if( $Mens == 0 ) {
	  	  # Verifica a Duplicidade de Certidão #
				$db     = Conexao();
		   	$sql    = "SELECT COUNT(CTIPCECODI) FROM SFPC.TBTIPOCERTIDAO WHERE RTRIM(LTRIM(ETIPCEDESC))  = '$CertidaoDescricao'";
		 		$result = $db->query($sql);
				if( PEAR::isError($result) ){
				    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
				}else{
		    		$Linha = $result->fetchRow();
				    $Qtd = $Linha[0];
		    		if( $Qtd > 0 ) {
					    	$Mens     = 1;
					    	$Tipo     = 2;
								$Mensagem = "<a href=\"javascript:document.Certidao.CertidaoDescricao.focus();\" class=\"titulo2\"> Certidão Já Cadastrada</a>";
						}else{
								# Recupera a última Certidão e incrementa mais um #
								$sql    = "SELECT MAX(CTIPCECODI) FROM SFPC.TBTIPOCERTIDAO";
								$result = $db->query($sql);
								if (PEAR::isError($result)) {
								    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
								}else{
										$Linha  = $result->fetchRow();
										$Codigo = $Linha[0] + 1;

										# Insere Certidão #
										$Data   = date("Y-m-d H:i:s");
										$sql    = "INSERT INTO SFPC.TBTIPOCERTIDAO (CTIPCECODI, ETIPCEDESC, FTIPCEOBRI, TTIPCEULAT) VALUES ($Codigo, '$CertidaoDescricao', '$Obrigatoriedade', '$Data')";
										$result = $db->query($sql);
										if( PEAR::isError($result) ){
										    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
										}else{
												# Limpa Variáveis #
												$Mens                = 1;
												$Tipo                = 1;
												$Mensagem            = "Certidão Incluída com Sucesso";
												$CertidaoDescricao 	 = "";
												$Obrigatoriedade     = "";
										}
								}
						}
				}
		    $db->disconnect();
		}
}
?>
<html>
<?php 
# Carrega o layout padrão
layout();
?>
<script language="javascript" type="">
<!--
<?php  MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="TabCertidaoFiscalIncluir.php" method="post" name="Certidao">
<br><br><br><br><br>
<table cellpadding="3" border="0">
  <!-- Caminho -->
  <tr>
    <td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
    <td align="left" class="textonormal" colspan="2">
      <font class="titulo2">|</font>
      <a href="../index.php"><font color="#000000">Página Principal</font></a> > Tabelas > Certidão > Incluir
    </td>
  </tr>
  <!-- Fim do Caminho-->

	<!-- Erro -->
	<?php  if ( $Mens == 1 ) {?>
	<tr>
	  <td width="100"></td>
	  <td align="left" colspan="2"><?php  ExibeMens($Mensagem,$Tipo,1); ?></td>
	</tr>
	<?php  } ?>
	<!-- Fim do Erro -->

	<!-- Corpo -->
	<tr>
		<td width="100"></td>
		<td class="textonormal">
      <table  border="0" cellspacing="0" cellpadding="3">
        <tr>
	      	<td class="textonormal">
	        	<table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" bgcolor="#FFFFFF">
	          	<tr>
	            	<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
		    					INCLUIR - CERTIDÃO
		          	</td>
		        	</tr>
	  	      	<tr>
	    	      	<td class="textonormal">
	      	    		<p align="justify">
	        	    		Para incluir uma nova Certidão, informe os dados abaixo e clique no botão "Incluir". Os itens obrigatórios estão com *.
	        	    	</p>
	          		</td>
		        	</tr>
		        	<tr>
	  	        	<td>
	    	      		<table class="textonormal" border="0" align="left" class="caixa">
	      	      		<tr>
	        	      		<td class="textonormal" bgcolor="#DCEDF7">Certidão*</td>
	          	    		<td class="textonormal">
	          	    			<input type="text" name="CertidaoDescricao" value="<?php  echo $CertidaoDescricao; ?>" size="80" maxlength="70" class="textonormal">
	          	    			<input type="hidden" name="Critica" value="1">
	          	    		</td>
	            			</tr>
										<tr>
											<td class="textonormal"  bgcolor="#DCEDF7">Obrigatoriedade*</td>
				              <td class="textonormal">
				              	<input type="radio" name="Obrigatoriedade" value="S" <?php if( $Obrigatoriedade == "" or $Obrigatoriedade == "S" ){ echo "checked"; } ?> > Sim
				              	<input type="radio" name="Obrigatoriedade" value="N" <?php if( $Obrigatoriedade == "N" ){ echo "checked"; }?> > Não
				              </td>
				            </tr>
	            		</table>
		          	</td>
		        	</tr>
	  	      	<tr>
  	  	  			<td class="textonormal" align="right">
	          	  	<input type="submit" name="Incluir" value="Incluir" class="botao">
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
<script language="javascript" type="">
<!--
document.Certidao.CertidaoDescricao.focus();
//-->
</script>

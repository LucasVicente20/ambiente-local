<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: TabCertidaoFiscalSelecionar.php
# Autor:    Rossana Lira
# Data:     11/02/05
# Objetivo: Programa de Manutenção de Certidão Fiscal
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/tabelasbasicas/TabCertidaoFiscalAlterar.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST"){
		$CertidaoCodigo 	= $_POST['CertidaoCodigo'];
		$Critica          = $_POST['Critica'];
		$Obrigatoriedade  = $_POST['Obrigatoriedade'];
}else{
		$Critica          = $_GET['Critica'];
		$Mensagem         = urldecode($_GET['Mensagem']);
		$Mens             = $_GET['Mens'];
		$Tipo             = $_GET['Tipo'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "TabCertidaoFiscalSelecionar.php";

# Critica dos Campos #
if( $Critica == 1 ){
		$Mens     = 0;
		$Mensagem = "Informe: ";
    if( $CertidaoCodigo == "" ) {
	      $Mens      = 1;
	      $Tipo      = 2;
        $Mensagem .= "<a href=\"javascript: document.Certidao.CertidaoCodigo.focus();\" class=\"titulo2\">Certidão</a>";
    }else{
    		$Url = "TabCertidaoFiscalAlterar.php?CertidaoCodigo=$CertidaoCodigo";
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
<script language="javascript" type="">
<!--
<?php  MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="TabCertidaoFiscalSelecionar.php" method="post" name="Certidao">
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
			<table width="100%" border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal"  bgcolor="#FFFFFF">
        <tr>
          <td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
	        	MANTER - CERTIDÃO
          </td>
        </tr>
        <tr>
          <td class="textonormal" bgcolor="#FFFFFF">
             <p align="justify">
             Para atualizar/excluir uma Certidão já cadastrada, selecione a Certidão e clique no botão "Selecionar".
             </p>
          </td>
        </tr>
        <tr>
          <td>
            <table>
							<tr>
								<td class="textonormal"  bgcolor="#DCEDF7">Obrigatoriedade*</td>
	              <td class="textonormal">
	              	<input type="radio" name="Obrigatoriedade" value="S" onClick="javascript:document.Certidao.Critica.value=0;document.Certidao.submit();" <?php if( $Obrigatoriedade == "" or $Obrigatoriedade == "S" ){ echo "checked"; } ?> > Sim
	              	<input type="radio" name="Obrigatoriedade" value="N" onClick="javascript:document.Certidao.Critica.value=0;document.Certidao.submit();" <?php if( $Obrigatoriedade == "N" ){ echo "checked"; }?> > Não
	              </td>
	            </tr>
              <tr>
                <td class="textonormal" bgcolor="#DCEDF7">Certidão*</td>
                <td class="textonormal" bgcolor="#FFFFFF">
                  <select name="CertidaoCodigo" class="textonormal">
                  	<option value="">Selecione uma Certidão...
                  	<?php 
                  	# Mostra as Certidaos cadastradas #
                		$db     = Conexao();
                		if ($Obrigatoriedade == "") {
												$Obrigatoriedade = "S";
										}
                		$sql    = "SELECT CTIPCECODI, ETIPCEDESC FROM SFPC.TBTIPOCERTIDAO ";
                		$sql   .= " WHERE FTIPCEOBRI = '$Obrigatoriedade' ORDER BY ETIPCEDESC";
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
                  <input type="hidden" name="Critica" value="1">
                </td>
              </tr>
            </table>
          </td>
        </tr>
        <tr>
 	        <td class="textonormal" align="right">
          	<input type="submit" value="Selecionar" class="botao">
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
document.Certidao.CertidaoCodigo.focus();
//-->
</script>

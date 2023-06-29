<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: TabPercentualIndiceSelecionar.php
# Autor:    João Batista Brito
# Data:     23/11/11
# Objetivo: Programa de Valores Percentuais por Índice
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/tabelasbasicas/TabPercentualIndiceSalvar.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST"){
		$IndCorrCodigo = $_POST['IndCorrCodigo'];
		$Critica      = $_POST['Critica'];
}else{
		$Critica      = $_GET['Critica'];
		$Mensagem     = urldecode($_GET['Mensagem']);
		$Mens         = $_GET['Mens'];
		$Tipo         = $_GET['Tipo'];
	
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "TabPercentualIndiceSelecionar.php";

# Critica dos Campos #
if( $Critica == 1 ){
		$Mens     = 0;
		$Mensagem = "Informe: ";
    if( $IndCorrCodigo == "" ) {
	      $Mens      = 1;
	      $Tipo      = 2;
        $Mensagem .= "<a href=\"javascript: document.Indice.IndCorrCodigo.focus();\" class=\"titulo2\">Índice</a>";
    }else{
    	$Url = "TabPercentualIndiceSalvar.php?IndCorrCodigo=$IndCorrCodigo";
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
</script><link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="TabPercentualIndiceSelecionar.php" method="post" name="Indice">
<br><br><br><br><br>
<table cellpadding="3" border="0">
  <!-- Caminho -->
  <tr>
    <td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
    <td align="left" class="textonormal" colspan="2">
      <font class="titulo2">|</font>
      <a href="../index.php"><font color="#000000">Página Principal</font></a> > Tabelas > Percentual Índice > Manter
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
	        	MANTER - PERCENTUAL ÍNDICE
          </td>
        </tr>
        <tr>
          <td class="textonormal">
             <p align="justify">
             Para selecionar um Índice Ativo já cadastrado, escolha o Índice e clique no botão "Selecionar".
             </p>
          </td>
        </tr>
        <tr>
          <td>
            <table>
              <tr>
                <td class="textonormal" bgcolor="#DCEDF7">Índice </td>
                <td class="textonormal">
                  <select name="IndCorrCodigo" class="textonormal">
                  	<option value="">Selecione um Índice...</option>
                  	<?php 
                  	# Mostra os perfis cadastrados #
                		$db     = Conexao();
                		$sql    = "SELECT CINCORSEQU, EINCORNOME FROM SFPC.TBINDICECORRECAO WHERE cincorsiti = 1 ORDER BY EINCORNOME";
                		$result = $db->query($sql);
                		if( PEAR::isError($result) ){
										    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
										}else{
												while( $Linha = $result->fetchRow() ){
		          	      			echo"<option value=\"$Linha[0]\">$Linha[1]\n";
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
document.Indice.IndCorrCodigo.focus();
//-->
</script>

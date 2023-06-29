<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: TabFasesSelecionar.php
# Autor:    Rossana Lira
# Data:     23/04/03
# Objetivo: Programa de Manutenção de Fases
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/tabelasbasicas/TabFasesAlterar.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST"){
		$FasesCodigo = $_POST['FasesCodigo'];
		$Critica     = $_POST['Critica'];
}else{
		$Critica     = $_GET['Critica'];
		$Mensagem    = $_GET['Mensagem'];
		$Mens        = $_GET['Mens'];
		$Tipo        = $_GET['Tipo'];
}

# Critica dos Campos #
$Mensagem = urldecode($Mensagem);
if( $Critica == 1 ){
		$Mens     = 0;
		$Mensagem = "Informe: ";
    if( $FasesCodigo == "" ) {
	      $Mens      = 1;
	      $Tipo      = 2;
        $Mensagem .= "<a href=\"javascript: document.Fases.FasesCodigo.focus();\" class=\"titulo2\">Fase </a>";
    }else{
    		$Url = "TabFasesAlterar.php?FasesCodigo=$FasesCodigo";
				if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
	      header("location: ".$Url);
	      exit();
    }
}
?>
<html>
<?php
#Carrega o layout padrão #
layout();
?>
<script language="javascript" type="">
<!--
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="TabFasesSelecionar.php" method="post" name="Fases">
<br><br><br><br><br>
<table cellpadding="3" border="0">
  <!-- Caminho -->
  <tr>
    <td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
		<td align="left" class="textonormal">
			<font class="titulo2">|</font>
			<a href="../index.php"><font color="#000000">Página Principal</font></a> > Tabelas > Fases > Manter
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
			<table width="100%" border="1" cellpadding="3" cellspacing="0"  bgcolor="#ffffff" bordercolor="#75ADE6"  class="textonormal" summary="">
        <tr>
          <td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
	        	MANTER - FASES
          </td>
        </tr>
        <tr>
          <td class="textonormal">
             <p align="justify">
             Para atualizar/excluir uma Fase já cadastrada, selecione a Fase e clique no botão "Selecionar".
             </p>
          </td>
        </tr>
        <tr>
          <td>
            <table border="0">
              <tr>
                <td class="textonormal" bgcolor="#DCEDF7">Fase </td>
                <td class="textonormal">
                  <select name="FasesCodigo" class="textonormal">
                  	<option value="">Selecione uma Fase...</option>
                  	<?php
                	  # Mostra as fases cadastradas #
                		$db     = Conexao();
                		$sql    = "SELECT CFASESCODI, EFASESDESC, AFASESORDE FROM SFPC.TBFASES ORDER BY AFASESORDE";
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
<?php MenuAcesso(); ?>
//-->
</script>

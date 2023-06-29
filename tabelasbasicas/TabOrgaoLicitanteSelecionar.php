<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: TabOrgaoLicitanteSelecionar.php
# Autor:    Roberta Costa
# Data:     01/04/03
# Objetivo: Programa de Manutenção de Modalidade
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/tabelasbasicas/TabOrgaoLicitanteAlterar.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST"){
		$OrgaoLicitanteCodigo = $_POST['OrgaoLicitanteCodigo'];
		$Critica              = $_POST['Critica'];
}else{
		$Critica              = $_GET['Critica'];
		$Mensagem             = urldecode($_GET['Mensagem']);
		$Mens                 = $_GET['Mens'];
		$Tipo                 = $_GET['Tipo'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "TabOrgaoSolicitanteSelecionar.php";

# Critica dos Campos #
if( $Critica == 1 ){
		$Mens     = 0;
		$Mensagem = "Informe: ";
    if( $OrgaoLicitanteCodigo == "" ) {
	      $Mens      = 1;
	      $Tipo      = 2;
        $Mensagem .= "<a href=\"javascript: document.Orgao.OrgaoLicitanteCodigo.focus();\" class=\"titulo2\">Órgão Licitante</a>";
    }else{
    		$Url = "TabOrgaoLicitanteAlterar.php?OrgaoLicitanteCodigo=$OrgaoLicitanteCodigo";
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
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="TabOrgaoLicitanteSelecionar.php" method="post" name="Orgao">
<br><br><br><br><br>
<table cellpadding="3" border="0">
  <!-- Caminho -->
  <tr>
    <td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
    <td align="left" class="textonormal" colspan="2">
      <font class="titulo2">|</font>
      <a href="../index.php"><font color="#000000">Página Principal</font></a> > Tabelas > Órgao Licitante > Manter
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
			<table width="100%" border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal"  bgcolor="#FFFFFF">
        <tr>
          <td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
	           MANTER - ÓRGÃO LICITANTE
          </td>
        </tr>
        <tr>
          <td class="textonormal">
						<p align="justify">
						Para atualizar/excluir um órgão já cadastrado, selecione o órgão licitante e clique no botão "Selecionar".
						</p>
          </td>
        </tr>
        <tr>
          <td>
            <table>
              <tr>
                <td class="textonormal" bgcolor="#DCEDF7">Órgão Licitante </td>
                <td class="textonormal">
                  <select name="OrgaoLicitanteCodigo" class="textonormal">
                  	<option value="">Selecione um Órgão Licitante...</option>
                  	<?php
                  	# Mostra os órgãos cadastrados #
                		$db     = Conexao();
                		$sql    = "SELECT CORGLICODI, EORGLIDESC, FORGLISITU FROM SFPC.TBORGAOLICITANTE ORDER BY EORGLIDESC";
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
document.Orgao.OrgaoLicitanteCodigo.focus();
//-->
</script>

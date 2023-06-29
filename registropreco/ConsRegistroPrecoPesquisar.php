<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: ConsRegistroPrecoPesquisar.php
# Autor:    Rossana Lira
# Data:     15/03/07
# Objetivo: Programa de Pesquisa de Registro de Preços
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------

// 220038--

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso('/registropreco/ConsRegistroPrecoResultado.php');

# Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $Botao                = $_POST['Botao'];
    $Objeto               = $_POST['Objeto'];
    $OrgaoLicitanteCodigo = $_POST['OrgaoLicitanteCodigo'];
} else {
    $Mens                 = $_GET['Mens'];
    $Tipo                 = $_GET['Tipo'];
    $Mensagem             = urldecode($_GET['Mensagem']);
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "ConsRegistroPrecoPesquisar.php";
?>
<html>
<?php
# Carrega o layout padrão #
layout();
?>
<script language="javascript" type="">
<!--
function enviar(valor){
	document.Pesquisa.Botao.value=valor;
	document.Pesquisa.submit();
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" bgcolor="#FFFFFF" text="#000000" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="ConsRegistroPrecoResultado.php" method="post" name="Pesquisa">
<br><br><br><br>
<table cellpadding="3" border="0" summary="">
  <!-- Caminho -->
  <tr>
    <td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
    <td align="left" class="textonormal" colspan="2"><br>
      <font class="titulo2">|</font>
      <a href="../index.php"><font color="#000000">Página Principal</font></a> > Registro Preço > Consulta
    </td>
  </tr>
  <!-- Fim do Caminho-->

	<!-- Erro -->
	<?php if ($Mens == 1) {
    ?>
	<tr>
	  <td width="100"></td>
	  <td align="left" colspan="2"><?php if ($Mens == 1) {
    ExibeMens($Mensagem, $Tipo, 1);
} ?></td>
	</tr>
	<?php 
} ?>
	<!-- Fim do Erro -->

	<!-- Corpo -->
	<tr>
		<td width="100"></td>
		<td class="textonormal">
      <table  border="0" cellspacing="0" cellpadding="3" bgcolor="#ffffff" summary="">
        <tr>
	      	<td class="textonormal">
	        	<table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal">
	          	<tr>
	            	<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
		    					CONSULTA DE REGISTRO DE PREÇO - ATAS INTERNAS
		          	</td>
		        	</tr>
	  	      	<tr>
	    	      	<td class="textonormal">
	      	    		<p align="justify">
	        	    		Para consultar as Atas de Registro de Preços dos Processos Licitatórios, selecione o item de pesquisa e  clique no botão "Pesquisar". Para limpar a pesquisa, clique no botão "Limpar".
	          	   	</p>
	          		</td>
		        	<tr>
	  	        	<td>
	    	      		<table class="textonormal" border="0" summary="">
	      	      		<tr>
	        	      		<td class="textonormal" bgcolor="#DCEDF7">Objeto</td>
	          	    		<td class="textonormal">
	          	    			<input type="text" name="Objeto" size="45" maxlength="60" value="<?php echo $Objeto;?>" class="textonormal">
	          	    		</td>
	            			</tr>
										<tr>
	        	      		<td class="textonormal" bgcolor="#DCEDF7">Órgão Licitante</td>
	          	    		<td class="textonormal">
		  				  	      <select name="OrgaoLicitanteCodigo" class="textonormal">
													<option value="">Todos os Órgãos Licitantes...</option>
													<?php
                                                    $db     = Conexao();
                                                    $sql    = "SELECT CORGLICODI, EORGLIDESC FROM SFPC.TBORGAOLICITANTE ORDER BY EORGLIDESC";
                                  $result = $db->query($sql);
                                                    if (PEAR::isError($result)) {
                                                        ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
                                                    } else {
                                                        while ($Linha = $result->fetchRow()) {
                                                            if ($Linha[0] == $OrgaoLicitanteCodigo) {
                                                                echo "<option value=\"$Linha[0]\" selected>$Linha[1]</option>\n";
                                                            } else {
                                                                echo "<option value=\"$Linha[0]\">$Linha[1]</option>\n";
                                                            }
                                                        }
                                                    }
                                  $db->disconnect();
                                                    ?>
											  </select>
										  </td>
	            			</tr>
	            			<tr>
		              		<td class="textonormal"  bgcolor="#DCEDF7">Comissão </td>
		              		<td class="textonormal">
		  				  	      <select name="ComissaoCodigo" class="textonormal">
													<option value="">Todas as Comissões...</option>
													<?php
                                                    $db     = Conexao();
                                                    $sql    = "SELECT CCOMLICODI, ECOMLIDESC, CGREMPCODI ";
                                                    $sql   .= "FROM SFPC.TBCOMISSAOLICITACAO ORDER BY CGREMPCODI,ECOMLIDESC";
                                  $result = $db->query($sql);
                                                    if (PEAR::isError($result)) {
                                                        ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
                                                    } else {
                                                        while ($Linha = $result->fetchRow()) {
                                                            if ($Linha[0] == $ComissaoCodigo) {
                                                                echo "<option value=\"$Linha[0]\" selected>$Linha[1]</option>\n";
                                                            } else {
                                                                echo "<option value=\"$Linha[0]\">$Linha[1]</option>\n";
                                                            }
                                                        }
                                                    }
                                  $db->disconnect();
                                                    ?>
											  </select>
										  </td>
	            			</tr>
	          			</table>
		          	</td>
		        	</tr>
	  	      	<tr>
   	  	  			<td class="textonormal" align="right">
        	      	<input type="button" name="Pesquisar" value="Pesquisar" class="botao" onclick="document.Pesquisa.submit();">
        	      	<input type="button" name="Limpar" value="Limpar" class="botao" onclick="document.location.reload();">
	                <input type="hidden" name="Botao" value="">
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
document.Pesquisa.Objeto.focus();
//-->
</script>

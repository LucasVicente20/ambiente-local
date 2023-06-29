<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: ConsInformacaoPortal.php
# Autor:    Rodrigo Melo
# Data:     11/03/2008
# Objetivo: Programa de Consulta de Informações do Portal, tais como: Versão, analistas responsáveis e suporte ao sistema.
# Alterado:	Rossana Lira
# Data:			10/06/2008
# Alterado:	Rodrigo Melo
# Data:		25/07/2008 - Alteração para a versão 2.2
#
# Alterado: Everton Lino
# Data:     07/07/2010 	- ALTERAÇÃO DE TEXTOS.
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();
?>
<html>
<?php
# Carrega o layout padrão #
layout();
?>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<script language="javascript" type="">
<!--
<?php MenuAcesso(); ?>
//-->
</script>
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="ConsInformacaoPortal.php" method="post" name="InfPortal">
<br><br><br><br><br>
<table cellpadding="3" border="0" summary="" width="100%">
  <!-- Caminho -->
  <tr align="right">
    <td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
    <td align="left" class="textonormal" colspan="2">
      <font class="titulo2">|</font>
      <a href="../index.php"><font color="#000000">Página Principal</font></a> > Controles > Informação
    </td>
  </tr>
  <!-- Fim do Caminho-->

	<!-- Corpo -->
	<tr width="100%">
		<td></td>
		<td class="textonormal">
      <table  border="0" cellspacing="0" cellpadding="3" bgcolor="#ffffff" summary="">
        <tr>
	      	<td class="textonormal" align="center">
	        	<table border="1" width="100%" cellpadding="3" align="center" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal">
	          	<tr>
	            	<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3" colspan=2>
		    					INFORMAÇÃO SOBRE O SISTEMA PORTAL DE COMPRAS
		          	</td>
		        	</tr>
	  	      	<tr>
	    	      	<td class="textonormal">
	          	   	Versão:
		         		</td>
                <td class="textonormal">
	          	   	<?php echo VERSAO; ?>
		         		</td>
		        	</tr>
              <tr>
	    	      	<td class="textonormal" colspan="1">
	          	   	Suporte ao Sistema - GGLIC – Gerência Geral de Licitações e Compras:
		         		</td>
                <td class="textonormal">
	          	   	 <a href="mailto:gglic@recife.pe.gov.br">gglic@recife.pe.gov.br</a>
		         		</td>
		        	</tr>
              <tr>
	    	      	<td class="textonormal">
                  Telefone para suporte:
		         		</td>
                <td class="textonormal">
	          	   3355-8790
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
<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: ConsLegislacaoDecretos.php
# Autor:    Rossana Lira
# Data:     04/09/03
# Objetivo: Programa de Consulta do Decretos (Legislação) da DGCO
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
<script language="javascript" type="">
<!--
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="ConsLegislacaoDecretos.php" method="post" name="Decretos">
<br><br><br><br><br>
<table cellpadding="3" border="0" summary="">
  <!-- Caminho -->
  <tr>
    <td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
    <td align="left" class="textonormal" colspan="2">
      <font class="titulo2">|</font>
      <a href="../index.php"><font color="#000000">Página Principal</font></a> > Institucional > Legislação > Decretos
    </td>
  </tr>
  <!-- Fim do Caminho-->

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
		    					DECRETOS DA GERÊNCIA GERAL DE LICITAÇÕES E COMPRAS - GGLIC
		          	</td>
		        	</tr>
	  	      	<tr>
	    	      	<td class="textonormal">
	      	    		<p align="justify">
	        	    		Para Acessar mais informações sobre os Decretos, clique no link desejado.
	          	   	</p>
	          		</td>
		        	</tr>
		        	<tr>
	  	        	<td>
	    	      		<table class="textonormal" border="0" align="left" summary="">
	      	      		<tr>
	        	      		<td class="textonormal" bgcolor="#DCEDF7"> <a href="ConsDecreto19205.php">  Decreto 19.205  </a></td>
	        	      		<td class="textonormal"> Regulamenta o Sistema Registro de Preços </td>
	        	      	</tr>
										<tr>
	        	      		<td class="textonormal" bgcolor="#DCEDF7"> <a href="ConsDecreto19300.php">  Decreto 19.300  </a></td>
	        	      		<td class="textonormal">
	        	      			Estabelece medidas desburocratizadoras para a celebração de contratos  <br>
	        	      			no âmbito da Administração Municipal e delega competência
	        	      		</td>
	        	      	</tr>
										<tr>
	        	      		<td class="textonormal" bgcolor="#DCEDF7"> <a href="ConsDecreto19789.php">  Decreto 19.789  </a></td>
	        	      		<td class="textonormal">
	        	      			Regulamenta licitações na modalidade pregão no âmbito da Administração <br>
	        	      			Pública Municipal
	        	      		</td>
	        	      	</tr>
	        	      	<tr>
	        	      		<td class="textonormal" bgcolor="#DCEDF7"> <a href="ConsDecreto19805.php">  Decreto 19.805  </a></td>
	        	      		<td class="textonormal">
	        	      			Regulamenta a estrutura organizacional da Diretoria Geral de Compras  <br>
	        	      			de Bens e Serviços - DGCO da Secretaria de Finanças.
	        	      		</td>
										</tr>
	            		</table>
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

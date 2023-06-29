<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: ConsFluxoRegistroPreco.php
# Autor:    Rossana Lira
# Data:     15/03/06
# Objetivo: Programa de Consulta de Fluxos dos Registro de Preços
#-------------------------------------------------------------------------
# Alterado: Pitang Agile TI
# Data:     18/05/2015
# Objetivo: Redimne 73650 - Incluir link "Fluxo do Participante" e atualizar os documentos exibidos ao acionar cada link

// 220038--

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
<form action="ConsFluxoRegistroPreco.php" method="post" name="InfRegistroPreco">
<br><br><br><br><br>
<table cellpadding="3" border="0" summary="">
  <!-- Caminho -->
  <tr>
    <td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
    <td align="left" class="textonormal" colspan="2">
      <font class="titulo2">|</font>
      <a href="../index.php"><font color="#000000">Página Principal</font></a> > Registro Preço > Fluxos
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
		    					FLUXOS DE REGISTRO DE PREÇO
		          	</td>
		        	</tr>
	  	      	<tr>
	    	      	<td class="textonormal">
	          	   	<p align="justify">
	          	   	<br>
										Seguem abaixo alguns fluxos de documentos que deverão do processo de Registro de Preços:
										<ul>
											<li><a href="FluxoGestor.pdf"> Fluxo do Gestor</a></li>
											<li><a href="FluxoParticipante.pdf"> Fluxo do Participante</a></li>
											<li><a href="FluxoCarona.pdf"> Fluxo do Carona</a></li>
										</ul>
									</p>
									<p align="center">
										Informações na Diretoria de Licitações e Compras<br>
										Endereço: Rua Cais do Apolo, 925 - 14º andar <br>
										CEP:500030-903 Bairro do Recife - Recife-PE <br>
										Telefones: 3232-8374/ 8216
									</p>
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

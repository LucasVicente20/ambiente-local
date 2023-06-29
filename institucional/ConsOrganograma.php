<?php
# -------------------------------------------------------------------------
# Portal da dlc
# Programa: ConsOrganograma.php
# Autor:    Rossana Lira
# Data:     28/05/2003
# Objetivo: Programa de Consulta do Organograma da dlc
# OBS.:     Tabulação 2 espaços
#           /pastaszona.js contém dados do organograma
# -------------------------------------------------------------------------
# Alterado: Wagner Barros
# Data:     14/07/2006
# -------------------------------------------------------------------------
# Alterado: Carlos Abreu
# Data:     14/07/2006 - Retirada dos hrefs para RotEmailPadrao.php
# -------------------------------------------------------------------------
# Alterado: Lucas Baracho
# Data:		24/10/2018
# Objetivo: Tarefa Redmine 73662
# -------------------------------------------------------------------------

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
<script language="JavaScript" src="../pastaszona.js"></script>
<script language="JavaScript">Init();</script>
<form action="ConsOrganograma.php" method="post" name="Organograma">
<br><br><br><br><br>
<table cellpadding="3" border="0" summary="">
  <!-- Caminho -->
  <tr>
    <td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
    <td align="left" class="textonormal" colspan="2">
      <font class="titulo2">|</font>
      <a href="../index.php"><font color="#000000">Página Principal</font></a> > Institucional > Organograma
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
		    					ORGANOGRAMA DA GERÊNCIA GERAL DE LICITAÇÕES E COMPRAS - GGLIC
		          	</td>
		        	</tr>
		        	<tr>
	  	        	<td>
	    	      		<table class="textonormal" border="0" align="center" summary="">
	      	      		<tr>
		      	      		<td>
												<img src="../midia/organograma.gif" alt="" usemap="#organograma" style="border-style:none" />			      	      		
												<map id="organograma" name="organograma">

													<area shape="rect" coords="194,0,369,33" onMouseover="showmenu(event,linkset[0])" onMouseout="delayhidemenu()" title=""/>
													<area shape="rect" coords="161,67,244,95"  onMouseover="showmenu(event,linkset[1])" onMouseout="delayhidemenu()" title=""/>
													<area shape="rect" coords="161,108,244,136"  onMouseover="showmenu(event,linkset[2])" onMouseout="delayhidemenu()" title=""/>
													<area shape="rect" coords="161,149,245,177"  onMouseover="showmenu(event,linkset[3])" onMouseout="delayhidemenu()" title=""/>
													<area shape="rect" coords="159,190,244,216"  onMouseover="showmenu(event,linkset[4])" onMouseout="delayhidemenu()" title=""/>
													<area shape="rect" coords="161,232,244,258"  onMouseover="showmenu(event,linkset[5])" onMouseout="delayhidemenu()" title=""/>
													<area shape="rect" coords="320,46,381,73"  onMouseover="showmenu(event,linkset[6])" onMouseout="delayhidemenu()" title=""/>
													<area shape="rect" coords="322,83,383,113"  onMouseover="showmenu(event,linkset[7])" onMouseout="delayhidemenu()" title=""/>
													<area shape="rect" coords="320,129,383,159" onMouseover="showmenu(event,linkset[8])" onMouseout="delayhidemenu()" title=""/>
													<area shape="rect" coords="320,169,382,200" onMouseover="showmenu(event,linkset[9])" onMouseout="delayhidemenu()" title=""/>
													<area shape="rect" coords="321,211,382,241"  onMouseover="showmenu(event,linkset[10])" onMouseout="delayhidemenu()" title=""/>
													<area shape="rect" coords="321,270,382,241"  onMouseover="showmenu(event,linkset[11])" onMouseout="delayhidemenu()" title=""/>
													<area shape="rect" coords="64,341,100,362"  onMouseover="showmenu(event,linkset[12])" onMouseout="delayhidemenu()" title=""/>																										
													<area shape="rect" coords="207,341,244,363" onMouseover="showmenu(event,linkset[13])" onMouseout="delayhidemenu()" title=""/>
													<area shape="rect" coords="307,343,343,361" onMouseover="showmenu(event,linkset[14])" onMouseout="delayhidemenu()" title=""/>
													<area shape="rect" coords="458,342,497,362" onMouseover="showmenu(event,linkset[15])" onMouseout="delayhidemenu()" title=""/>
													<area shape="rect" coords="1,390,69,405"  onMouseover="showmenu(event,linkset[16])" onMouseout="delayhidemenu()" title=""/>
													<area shape="rect" coords="77,390,123,406"  onMouseover="showmenu(event,linkset[17])" onMouseout="delayhidemenu()" title=""/>
													<area shape="rect" coords="138,375,206,394" onMouseover="showmenu(event,linkset[18])" onMouseout="delayhidemenu()" title=""/>
													<area shape="rect" coords="244,402,289,423" onMouseover="showmenu(event,linkset[19])" onMouseout="delayhidemenu()" title=""/>
													<area shape="rect" coords="300,404,346,421" onMouseover="showmenu(event,linkset[20])" onMouseout="delayhidemenu()" title=""/>
													<area shape="rect" coords="361,404,406,425" onMouseover="showmenu(event,linkset[21])" onMouseout="delayhidemenu()" title=""/>
													<area shape="rect" coords="423,393,465,415" onMouseover="showmenu(event,linkset[22])" onMouseout="delayhidemenu()" title=""/>
													<area shape="rect" coords="479,393,528,410" onMouseover="showmenu(event,linkset[23])" onMouseout="delayhidemenu()" title=""/>
													<area shape="rect" coords="72,448,114,467"  onMouseover="showmenu(event,linkset[24])" onMouseout="delayhidemenu()" title=""/>
													<area shape="rect" coords="128,448,168,469" onMouseover="showmenu(event,linkset[25])" onMouseout="delayhidemenu()" title=""/>
													<area shape="rect" coords="187,449,237,467" onMouseover="showmenu(event,linkset[25])" onMouseout="delayhidemenu()" title=""/>
													<area shape="rect" coords="251,448,290,468" onMouseover="showmenu(event,linkset[26])" onMouseout="delayhidemenu()" title=""/>
													<area shape="rect" coords="55,479,124,494"  onMouseover="showmenu(event,linkset[28])" onMouseout="delayhidemenu()" title=""/>
													<area shape="default" nohref="nohref" alt="" />
													
													<area shape="poly" coords="11,68,17,57,73,46,75,65,92,63,77,99,15,118" href="" onMouseover="showmenu(event,linkset[0])" onMouseout="delayhidemenu()" title="Portal de Compras"/>
													</map>
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

<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: ConsSetorDPS.php
# Autor:    Rossana Lira
# Data:     28/05/03
# Objetivo: Programa de Consulta do Organograma da DGCO - SETOR DPS
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/institucional/ConsOrganograma.php' );
AddMenuAcesso( '/institucional/RotEmailPadrao.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST"){
		$Botao   = $_POST['Botao'];
}

# Redireciona para página do Organograma #
if( $Botao == "Voltar" ){
	  header("location: ConsOrganograma.php");
	  exit;
}
?>
<html>
<?php
# Carrega o layout padrão #
layout();
?>
<script language="javascript" type="">
<!--
function enviar(valor){
	document.SetorDPS.Botao.value=valor;
	document.SetorDPS.submit();
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="ConsSetorDPS.php" method="post" name="SetorDPS">
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
	            	<td align="center" bgcolor="#75ADE6" valign="middle" colspan="2" class="titulo3">
		    					INFORMAÇÕES ORGANIZACIONAIS - DIVISÃO DE PLANEJAMENTO DE SERVIÇOS
		          	</td>
		        	</tr>
							<tr>
							  <td>
							  	<table border="0" summary="">
							  		<tr>
											<td class="textonegrito" align="left" valign="middle" colspan="2">	RESPONSÁVEL </td>
				          	</tr>
										<tr>
											<td class="textonormal" colspan="2">
				      	    		<p align="justify">
										     	Nome: Viviane Patrícia Lemos da Silva
			     							</p>
												<p align="justify">
													Fone: 3232-8374
												</p>
												<p align="justify">
												  <?php
											    $Pessoa = urlencode("Viviane Patrícia Lemos da Silva");
											    $Para   = urlencode("portalcompras@recife.pe.gov.br");
												  ?>
												  E-mail: <a href="../institucional/RotEmailPadrao.php?Pessoa=<?php echo $Pessoa;?>&Para=<?php echo $Para;?>">portalcompras@recife.pe.gov.br</a><br>
												</p>
											</td>
										</tr>
										<tr>
											<td class="textonegrito" align="left" valign="middle" colspan="2">	ATRIBUIÇÕES </td>
				          	</tr>
				  	      	<tr>
				    	      	<td class="textonormal" colspan="2">
				      	    		<p align="justify">
								     			- Regulamentação da área:
												</p>
												<p align="justify">
								    			- Competência:
												</p>
											</td>
										</tr>
										<tr>
											<td class="textonormal">&nbsp;</td>
											<td class="textonormal">
												<p align="justify">
													I - elaborar e consolidar, com auxílio dos órgãos da Administração direta, a Programação Periódica de Provimento
													de Serviços;
												</p>
												<p align="justify">
													II - realizar estudos estatísticos quanto aos preços praticados pela Administração para a prestação de serviços por
													terceiros, estimando, inclusive, a necessidade de licitações;
												</p>
												<p align="justify">
													III - realizar, nos órgãos da Administração direta, levantamento dos fluxogramas de prestação de serviços por
													terceiros, com e sem licitação, visando à padronização de que trata o inciso IV do art. 5º deste Decreto;

												</p>
												<p align="justify">
													IV - propor mecanismos de controle, a serem previstos nos contratos de serviços, que garantam mais eficiência na
													execução;
												</p>
												<p align="justify">
													V - subsidiar o processo de tomada de decisões, no âmbito do DEPLAN.
												</p>
												</td>
					        	</tr>
					        </table>
					      </td>
					    </tr>
					    <tr>
   	  	  			<td class="textonormal" align="right">
        	      	<input type="button" value="Voltar" class="botao" onclick="javascript:enviar('Voltar');">
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

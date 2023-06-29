<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: ConsInformacaoRegistroPreco.php
# Autor:    Rossana Lira
# Data:     15/03/06
# Objetivo: Programa de Consulta de Informações dos Registro de Preço
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------

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
<form action="ConsInformacaoRegistroPreco.php" method="post" name="InfRegistroPreco">
<br><br><br><br><br>
<table cellpadding="3" border="0" summary="">
  <!-- Caminho -->
  <tr>
    <td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
    <td align="left" class="textonormal" colspan="2">
      <font class="titulo2">|</font>
      <a href="../index.php"><font color="#000000">Página Principal</font></a> > Registro Preço > Informação
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
		    					INFORMAÇÃO SOBRE O CADASTRO DE REGISTRO DE PREÇO
		          	</td>
		        	</tr>
	  	      	<tr>
	    	      	<td class="textonormal">
	          	   	<p align="justify">
	          	   	<br>
										Sistema de Registro de Preço(SRP) é um procedimento especial de licitação
										que efetiva por meio de uma concorrência ou pregão sui generis, 
										selecionando a proposta mais vantajosa, com observância  do principio
										da isonomia para eventual e futura contratação pela Administração.
	          	   	</p>
	          	   	<p align="justify">
										O administrador não deixará de fazer licitação, apenas fará uma 
										licitação com procedimento especial, onde a aquisição do produto 
										ou serviço não é obrigatória 	  
									</p>
	          	   	<p align="justify">									
										Figuras compõem o SRP									
										<ul>
											<li>Órgão Controlador</li>
											<li>Órgão Gestor</li>
											<li>Órgão Participante</li>
											<li>Órgão Não participante (Carona)</li>											
										</ul>																											
									</p> 							
 									<p align="justify" class="textonegrito">
										Órgão Controlador<br> 										
									</p> 							
									<p> 										
										O Órgão Controlador se encarrega de manter acompanhamento geral 
										das licitações por registro de preço e fica  responsável por todos 
										os processos  de controle e informação dos sistemas de registro de 
										preços.<br> 										
										O Órgão Controlador é a GGLIC para todos os itens de registros de preços,
										exceto os referentes a medicamentos, materiais e equipamentos médico-hospitalares,
										materiais de laboratório e materiais odontológicos, que estarão sobre o
										controle da Secretaria de Saúde. 
	          	   	</p>
 									<p align="justify" class="textonegrito">
										Órgão Gestor<br> 										
									</p> 							
	          	   	<p align="justify">
										É o órgão ou entidade da Administração Pública responsável pela utilização da Ata de Registro de Preços, desempenha atividades como:
										<ul>
											<li>O DAS elabora a ata para assinatura dos vencedores da licitação, conforme modelo constante do edital;</li>
											<li>Contatar os fornecedores para a aquisição via contrato ou empenho;</li>
											<li>Controlar a aquisição os seus itens e os dos participantes;</li>																						
										</ul>
									</p> 																	
 									<p align="justify" class="textonegrito">
										Órgão Participante<br> 										
									</p> 							
 									<p align="justify">
										É órgão ou entidade da Administração Pública que participa do conjunto de 
										procedimentos do certame para registro de preços e integra a Ata de Registro 
										de Preços.<br>
										O órgão participante pode pertencer a outra esfera da Administração. 
										Ex. CHESF, Hospital das Clinicas
	          	   	</p>	
 									<p align="justify" class="textonegrito">
										Órgão Não Participante (Carona)<br> 										
									</p> 							
 									<p align="justify">
										É o órgão ou entidade Administração Pública que, não tendo participado 
										na época oportuna, informando, posteriormente, suas estimativas de 
										consumo, requer ao órgão controlador da ata, com anuência do fornecedor 
										o uso da Ata de Registro de Preços.
									</p>	
 									<p align="justify" class="textonegrito">
										SÓ SERÁ POSSÍVEL EMPENHAR AS DEMANDAS DE REGISTRO DE PREÇOS APÓS O DESPACHO DA GGLIC
									</p> 							
									<p align="center">
										Informações na Diretoria de Licitações e Compras<br>
										Endereço: Rua Cais do Apolo, 925 - 14º andar <br>
										CEP:50030-903 Bairro do Recife - Recife-PE <br>
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

<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: ConsInformacaoFornecedores.php
# Autor:    Rossana Lira
# Data:     29/05/03
# Alterado: Everton Lino
# Data:     07/07/2010 	- ALTERAÇÃO DE TEXTOS.
# Alterado: Rodrigo Melo
# Data:     05/05/2011 	- Atualização do texto. Tarefa do Redmine: 2208
# Objetivo: Programa de Consulta de Informações dos Fornecedores
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/fornecedores/ConsDocumentacaoFornecedores.php' );
AddMenuAcesso( '/fornecedores/CadInscritoIncluir.php' );
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
<form action="ConsInformacaoFornecedores.php" method="post" name="InfFornecedores">
<br><br><br><br><br>
<table cellpadding="3" border="0" summary="">
  <!-- Caminho -->
  <tr>
    <td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
    <td align="left" class="textonormal" colspan="2">
      <font class="titulo2">|</font>
      <a href="../index.php"><font color="#000000">Página Principal</font></a> > Fornecedores > Informação
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
	        	<table width="60%" border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal">
	          	<tr>
	            	<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
		    					INFORMAÇÃO SOBRE O CADASTRO DE FORNECEDORES
		          	</td>
		        	</tr>
	  	      	<tr>
	    	      	<td class="textonormal">
	          	   	<p align="justify">
	          	   	<br>
										O Sistema de Credenciamento Unificado de Fornecedores da
										Prefeitura do Recife (SICREF) foi criado para inscrever pessoas físicas
										e jurídicas que pretendam contratar com a Administração Pública
										Municipal, destinando-se a desburocratizar, agilizar e aumentar
										a competitividade das compras governamentais.
	          	   	</p>
	          	   	<p align="justify">
										O Certificado correspondente à inscrição no SICREF poderá substituir os 
										documentos necessários à habilitação em licitações promovidas pelos 
										órgãos e entidades municipais, bem como em contratações diretas, 
										além de ser exigido para a participação na modalidade Tomada de Preços.
	          	   	</p>
	          	   	<p align="justify">
										Para facilitar o processo de inscrição no SICREF, a Prefeitura
										está disponibilizando a <a href="CadInscritoIncluir.php"> Inscrição On-line dos Fornecedores </a>ou através do
										preenchimento do <a href="formulariofornecedoreswebatual.pdf"> formulário</a> e entrega posterior.

										A documentação relativa à habilitação jurídica, qualificação técnica, qualificação
										econômico-financeira e regularidade fiscal, deverá ser enviada ao Protocolo da Gerência Geral de Licitações e Compras - GGLIC (Cais do Apolo, 925,
										2.º andar, CEP:50030-903, Bairro do Recife, Recife-PE - telefone: 3355-8235).
	          	   	</p>
	          	   	<p align="justify">
										O requerimento de inscrição no SICREF, instruído por toda a <a href="ConsDocumentacaoFornecedores.php">documentação</a>
										exigida, será submetido à análise da Gerência de Serviços de
										Credenciamento de Fornecedores, a quem caberá autorizar ou não, no
										prazo máximo de 3 (três) dias úteis da data de recebimento da
										solicitação, a efetuação do cadastro, salvo pendências dos documentos.
	          	   	</p>
	          	   	<p align="justify">
	          	   		O recebimento do CHF será feito através da internet, mediante a utilização de senha concedida ao
	          	   		solicitante, quando da solicitação do Cadastramento ou atualização.
	          	   	</p>
					<p align="justify">
					<b>Cadastro simplificado para Microempresas e Empresas de Pequeno Porte</b> 
					
  	         	   	</p>
					<p align="justify">
					As Microempresas e Empresas de Pequeno Porte poderão ter acesso a um Certificado de Habilitação de Firmas - CHF simplificado em que não será exigido o Balanço Patrimonial e a Demonstração do Resultado do Exercício. Esta simplificação permitirá a participação e habilitação em licitações exclusivas para esses tipos de pessoas jurídicas - de acordo com o disposto no Decreto Municipal nº 27.300/2013 - e também para aquelas licitações que não exijam qualificação econômica.
  	         	   	</p>
  	         	   	
 					<p align="justify">
 	         	   	Caso as empresas portadoras desse CHF almejem participar de licitações não exclusivas, de caráter geral, em que o Balanço Patrimonial seja exigido para fins de qualificação econômico- financeira, deverão obrigatoriamente apresentar os referidos demonstrativos contábeis juntamente com os termos de abertura e encerramento do livro diário, autenticados na Junta Comercial, de acordo com a Instrução Normativa do Departamento de Registro Empresarial e Integração - DREI nº 11 de 05.12.2013, gerando, assim, um CHF completo.
  	         	   	</p>
					<p align="justify">
						Para que haja a caracterização da pessoa jurídica como Microempresa ou Empresa de Pequeno Porte, é necessária a entrega da declaração de enquadramento como tal, de acordo com os requisitos indicados no art.3º da Lei Complementar nº 123/2006. 				
  	         	   	</p>

					<p align="justify">
						O Microempreendedor Individual – MEI está dispensado legalmente da elaboração do Balanço Patrimonial e da Demonstração do Resultado do Exercício. No entanto, no tocante ao Sistema de Credenciamento de Fornecedores – SICREF , é necessária a prova dessa situação por meio de certificado de condição de Microempreendedor individual.	   	</p>
  	         	   	
  	         	   	
  	         	   	
  	         	   	
  	         	   	
  	         	   	
  	         	   	
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

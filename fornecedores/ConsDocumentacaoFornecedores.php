<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: ConsDocumentacaoFornecedores.php
# Autor:    Rossana Lira
# Data:     29/05/03
# Objetivo: Programa de Consulta de Documentação dos Fornecedores
# Alterado: Rossana Lira
# Data:     03/06/2008 - Alteração do texto do item 12, "consideram-se..."
# Alterado: Everton Lino
# Data:     07/07/2010 	- ALTERAÇÃO DE TEXTOS.
# Alterado: Rodrigo Melo 
# Data:     05/05/2011 	- Atualização do texto. Tarefa do Redmine: 2208
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------
/**
* -----------------------------------------------------------------------------
* Alterado: João Madson
* Data:     13/09/2019
* Objetivo: Tarefa Redmine 223695
* -----------------------------------------------------------------------------
*/

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
<body background="../midia/bg.gif" bgcolor="#FFFFFF" text="#000000" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="ConsDocumentacaoFornecedores.php" method="post" name="DocumentacaoForn">
<br><br><br><br><br>
<table cellpadding="3" border="0" summary="">
  <!-- Caminho -->
  <tr>
    <td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
    <td align="left" class="textonormal" colspan="2">
      <font class="titulo2">|</font>
      <a href="../index.php"><font color="#000000">Página Principal</font></a> > Fornecedores > Documentação
    </td>
  </tr>
  <!-- Fim do Caminho-->

	<!-- Corpo -->
	<tr>
		<td width="100"></td>
		<td class="textonormal">
      <table  width="60%" border="0" cellspacing="0" cellpadding="3" bgcolor="#ffffff" summary="">
        <tr>
	      	<td class="textonormal">
	        	<table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal">
	          	<tr>
	            	<td align="left" bgcolor="#75ADE6" valign="middle" class="titulo3">
		    					DOCUMENTAÇÃO EXIGIDA PARA REGISTRO CADASTRAL NA PREFEITURA DO RECIFE, DE ACORDO COM A LEI 8.666 DE 21 DE JUNHO DE 1993
		          	</td>
		        	</tr>
	  	      	<tr>
	    	      	<td class="textonormal" >
	          	   	<p align="justify">
	          	   		<br><p align="justify">
                                <br />
                                <b>01)</b> Registro comercial, acompanhado de Cédula de Identidade do representante legal no caso de empresa individual; <br /><br />
                                <b>02)</b> Ato constitutivo, estatuto ou contrato social em vigor e demais alterações ou a última alteração consolidada, devidamente registrado na junta comercial, em se tratando de sociedades comerciais, e, no caso de sociedade por ações acompanhado de documentos de eleição de seus administradores;<br /><br />
                                <b>03)</b> Inscrição do ato constitutivo, no caso de sociedades civis, acompanhada de prova de diretoria em exercício;<br /><br />
                                <b>04)</b> Prova de inscrição no Cadastro de Pessoas Físicas (CPF), ou Cadastro Nacional da Pessoa Jurídica (CNPJ);<br /><br />
                                <b>05)</b> Certidões (Negativa ou Regularidade):<br /><br />
                                <ul>
                                    <li> Prova de regularidade para com a Fazenda Federal e Dívida Ativa da União; </li>
                                    <li> Regularidade no FGTS;</li>
                                    <li> Regularidade (Negativa) Estadual; </li>
                                    <li> Regularidade (Negativa) Municipal (relativo a todos os tributos mobiliários e imobiliários); </li>
                                    <li> Certidão Negativa de Débitos Trabalhistas (CNDT); </li>
                                    <li> Certidão Negativa de Falência e Concordata; </li>
                                    <li> Declaração que não emprega menor (Conforme Disposto no Inciso V de Art.27 da Lei nº 8.666 de 21/06/1993, acrescido pela Lei no 9.854 de 27/10/1999.)<br /><a href="../fornecedores/DeclaracaoMenor.rtf"> modelo de declaração que não emprega menor; </a></li>

                                </ul><br />
                                <b>06)</b> Empresas com domicílio ou sede localizada em outro município, que tenham filial no Município do RECIFE, além da prova exigida nesta relação referente ao item 05, devem também apresentar Certidão Negativa de Débitos da Filial exarada pelo Departamento de Tributos Mercantis da Secretaria de Finanças da Prefeitura do Recife;<br /><br />
                                <b>07)</b> Registro ou inscrição na entidade profissional competente (nos casos específicos em que seja necessário). Ex.: CREA, CRF, CRA, ETC...<br /><br />
                                <b>08)</b> Demonstrações Contábeis do último exercício social, já exigíveis, que comprovem a boa situação financeira da empresa.<br /><br />
                          
                                <u>Para as Sociedades Anônimas:</u><br /><br />
                           
                                    <ul> <b>a)</b> Serão exigidas as demonstrações publicadas no sítio eletrônico da Comissão de Valores Mobiliários (CVM) ou em jornal na forma da lei 6.404/76 que foi alterada pela Medida Provisória 892/2019 e alterações posteriores;</ul><br />
                                    <ul> <b>b)</b> É preciso apresentar, além do Estatuto Social, resumo da Ata de Assembleia Geral Ordinária ou Extraordinária que aprovou o último exercício financeiro e Ata de Reunião do Conselho de Administração que elegeu a diretoria atual.</ul><br />
                            
                                <u>Em relação as empresas submetidas ao Sistema Público de Escrituração Digital (SPED):</u><br /><br />
                            
                           
                                    Conforme previsto no Decreto 6.022, de 22 de janeiro de 2007, e que pela legislação pertinente à Receita Federal do Brasil sejam obrigadas à Escrituração Contábil Digital (ECD) deverão apresentar os seguintes documentos emitidos pelo próprio sistema do SPED:<br /><br />
                                
                                    <ul>
                                        <li> Termo de Abertura e Encerramento;</li>
                                        <li> Balanço Patrimonial;</li>
                                        <li> Demonstração do Resultado do Exercício;</li>
                                        <li> Recibo de Entrega da Escrituração Contábil Digital.</li>
                                    </ul>
                                
                                A Escrituração Contábil Digital será considerada autenticada no momento da transmissão via SPED, conforme previsão da Instrução Normativa 1486, da Receita Federal do Brasil e do Decreto 8.683, de 25 de fevereiro de 2016. <br /><br />

                            
                                <u>Das demais sociedades serão exigidas as seguintes demonstrações:</u><br /><br />
                                    
                                    <ul><b>a)</b> Balanço patrimonial do último exercício social, já exigível, devidamente copiados do Livro Diário;</ul><br />
                                    <ul><b>b)</b> Demonstração do resultado do último exercício social, já exigível, devidamente copiados do Livro Diário;</ul><br />
                                    <ul><b>c)</b> Os balanços patrimoniais e a demonstração de resultado do último exercício social já exigíveis, <u>devem ser acompanhados dos termos de abertura e encerramento do livro diário,</u> sendo todos copiados dos livros registrados na junta comercial, devidamente autenticados por esta, conforme o Código Civil e Instrução Normativa Nº 11 do Departamento de Registro Empresarial e Integração;</ul><br />

                            
                                <u>Observações:</u><br /><br />

                                <ul>
                                    <li>Para fins de validação do último exercício social, poderá ser exigido, em diligência, o balanço do exercício anterior ao exigível;</li>
                                    <li>Se houver no exercício exigível movimentação na conta lucros/prejuízos acumulados, que cause impacto no Patrimônio Líquido, poderá ser solicitado em diligência a Demonstração dos Lucros ou Prejuízos Acumulados (DLPA), para comprovação dos valores apresentados na referida conta, sendo essa de elaboração obrigatória como prevê a Lei 6.404/76 em seu artigo 176 e o CPC 26. Vale ressaltar, que no caso de a empresa elaborar a Demonstração das Mutações do Patrimônio Líquido (DMPL), esta substitui a DLPA como preceitua o disposto no §2° da respectiva Lei.</li>
                                    <li>No caso das micro e pequenas empresas que optarem pelo cadastro completo, ao invés do cadastro simplificado, que é exclusivo para esse porte de empresa, será necessário o envio das demonstrações contábeis de acordo com a ITG 1000 - Modelo Contábil simplificado para Microempresas e Empresas de Pequeno Porte, definido pelo Conselho Federal de Contabilidade.</li>
                                    <li>Fica vedada a apresentação de balancetes ou balanços provisórios em substituição as demonstrações do último exercício social, já exigíveis;</li>
                                    <li>Nas demonstrações contábeis apresentadas deverá constar o Certificado de Regularidade Profissional do Contador instituída através da Resolução Nº 1.402/2012 do Conselho Federal de Contabilidade;</li>
                                    <li>Consideram-se já exigíveis para as Sociedades Anônimas, no prazo determinado no Art. 132 da Lei 6.404/76, e para as demais Sociedades a data determinada pelo Art. 1.078 do Código Civil.</li>

                                </ul><br />


                                <b>09)</b> Prova de atendimento de requisitos previsto em Lei Especial.<br /><br />

                                <u>Empresas de Medicamentos e Materiais Hospitalares:</u>

                                    <ul> 
                                        <li>Autorização de funcionamento expedida pela Agencia Nacional de Vigilância Sanitária - ANVISA, em original ou cópia reprográfica do Diário Oficial da União, devidamente autenticada;</li><br />
                                        <li> Licença de funcionamento expedida pelo órgão sanitário estadual ou municipal competente para o fabricante, distribuidor, representante comercial ou comerciante, da sede do licitante, válida para o ano em exercício;</li><br />
                                        <li> Quando o interessado for distribuidor, representante comercial ou comerciante deverá apresentar a autorização de funcionamento pela Agencia Nacional de Vigilância Sanitária, emitida para o fabricante, em original ou cópia reprográfica do Diário Oficial da União, devidamente autenticada;
                                    </ul>        
                                    
                                <u>Para as Empresas de Segurança Armada:</u>
                                    
                                    <ul>    
                                        <li> Alvará de funcionamento do Ministério da Justiça;</li>
                                        <li> Certificado de Segurança da Policia Federal;</li>
                                        <li> Revisão ou autorização para Funcionamento emitida pela Policia Federal.</li>
                                    </ul>
                            </p><br>
                            <p align="center">
										Informações no Setor de Cadastramento de Fornecedores<br>
										Endereço: Rua Cais do Apolo, 925 - 11º andar sala 23 <br>
										CEP:50030-903 Bairro do Recife - Recife-PE <br>
										Telefones: 3355-8368/ 8275 <br>
                    Email: sicref@recife.pe.gov.br
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
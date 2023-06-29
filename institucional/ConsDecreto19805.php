<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: ConsDecreto19805.php
# Autor:    Rossana Lira
# Data:     05/09/03
# Objetivo: Programa de Consulta do Decreto 19805
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/institucional/ConsLegislacaoDecretos.php' );

if( $Botao == "Voltar" ){
	  header("location: ConsLegislacaoDecretos.php");
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
	document.Decreto19805.Botao.value=valor;
	document.Decreto19805.submit();
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="ConsDecreto19300.php" method="post" name="Decreto19805">
<br><br><br><br><br>
<table cellpadding="3" border="0" summary="">
  <!-- Caminho -->
  <tr>
    <td width="100"><img border="0" src="../midia/linha.gif" alt></td>
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
		    					DECRETO N° 19.805 DE 31 DE MARÇO DE 2003.
		          	</td>
		        	</tr>
		        	<tr>
							  <td class="textonormal">
							  	<table border="0" summary="">
										<tr>
											<td class="textonegrito">
												<p align="right">
													EMENTA: Regulamenta a estrutura organizacional da Diretoria Geral de Compras de Bens e Serviços - DGCO da Secretaria de Finanças.
													<br><br>
												</p>
			     						</td>
			     					</tr>
			     					<tr>
			     						<td class="textonormal">
												<p align="justify" class="textonormal">
													O PREFEITO DO RECIFE, no uso das atribuições contidas no artigo 54, IV, da Lei Orgânica do Município,
													CONSIDERANDO o disposto nos artigos 43 e 50 da Lei Municipal n° 16.662, de 19 de junho de 2001;
													CONSIDERANDO as disposições contidas na Lei Municipal nº 16.842, de 31 de janeiro de 2003,
													D E C R E T A: <br><br>
												</p>
											</td>
										</tr>
										<tr>
			     						<td>
												<p align="justify" class="textonormal">
													Art. 1º - Os cargos em comissão de que trata o Anexo XIII do Decreto n.º 18.861/2001, de Diretor do Departamento Técnico e Diretor do Departamento Comercial, ambos símbolo DDP, passam a ter a seguinte denominação, respectivamente:
													<br>
													I - Diretor do Departamento de Planejamento;
													<br>
													II - Diretor do Departamento de Relações Comerciais.
													<br><br>
													Art. 2º - O cargo em comissão de que trata o Anexo XIII do Decreto n.º 18.861/2001, de Diretor da Divisão de Cotação, Manutenção de Bens e Serviços, símbolo DDI, passa a se denominar Diretor da Divisão de Cadastro de Insumos e Cotações.
													<br><br>
													Art. 3º - A Diretoria Geral de Compras de Bens e Serviços - (DGCO), símbolo DS - 2, subordinada ao Gabinete da Secretaria de Finanças é composta da seguinte estrutura organizacional:
													<br>
													I - Departamento de Planejamento - DEPLAN, composto das seguintes divisões:
													<br>
													a) Divisão de Planejamento de Materiais - DPM;
													<br>
													b) Divisão de Planejamento de Serviços - DPS;
													<br>
													II - Departamento de Relações Comerciais - DECOM, composto das seguintes divisões:
													<br>
													a) Divisão de Cadastro de Insumos e Cotações - DIC;
													<br>
													b) Divisão de Execução de Compras - DEC;
													<br>
													III - Departamento de Licitações e Contratos - DELIC, composto das seguintes divisões:
													<br>
													a) Divisão de Suporte às Licitações - DSL;
													<br>
													b) Divisão de Monitoramento de Contratos - DMC;
													<br>
													c) Divisão de Credenciamento de Fornecedores - DCF;
													<br>
													IV - Departamento de Logística de Materiais - DELOG, composto das seguintes divisões:
													<br>
													a) Divisão de Monitoramento de Estoques - DME;
													<br>
													b) Divisão de Controle e Expedição de Materiais - DCE;
													<br>
													V - Assessoria Executiva - AE;
													<br>
													VI - Assessoria de Tecnologia da Informação - ATI;
													<br>
													VII - Assessoria Jurídica - AJ.
													<br><br>
													§ 1º - As chefias dos Departamentos de que tratam os incisos I a IV deste artigo correspondem a cargos em comissão de símbolo DDP.
													<br>
													§ 2º - As chefias das Divisões de que tratam as alíneas a e b do inciso I, a e b do inciso II, a, b e c do inciso III, a e b do inciso IV, bem como as assessorias de que tratam os inciso V, VI e VII deste artigo, correspondem a cargos em comissão de símbolo DDI.
													<br><br>
													Art. 4º - Compete à Diretoria Geral de Compras de Bens e Serviços - DGCO:
													<br>
													I - propor e desenvolver diretrizes e ações destinadas à desburocratização, modernização e maior transparência dos processos de aquisição de bens, contratação de serviços e gestão de estoques no âmbito da Administração pública municipal;
													<br>
													II - supervisionar as relações comerciais entre os órgãos e entidades da Administração pública municipal e seus fornecedores, efetivos e potenciais, nos processos de aquisição de bens e contratação de serviços;
													<br>
													III - supervisionar as atividades relativas à gestão da logística, compreendendo a armazenagem e distribuição dos materiais de uso ou consumo da Administração direta;
													<br>
													IV - subsidiar as decisões do Secretário de Finanças relativamente aos processos licitatórios, bem como às atividades das Comissões de Licitação da Administração direta;
													<br>
													V - promover medidas destinadas à uniformização procedimental para os processos de compras, licitações, contratos e gestão de estoques, no âmbito da Administração pública municipal;
													<br>
													VI - subsidiar as atividades do Secretário de Finanças na coordenação dos processos licitatórios, no âmbito da Administração direta, assim como os de dispensa e inexigibilidade de licitação;
													<br><br>
													Art. 5º - Compete ao Departamento de Planejamento - DEPLAN:
													<br>
													I - coordenar a criação e execução, junto aos órgãos da Administração direta, da Programação Periódica de Provimento de Materiais e Serviços;
													<br>
													II - produzir informações gerenciais, a serem periodicamente apresentadas ao Prefeito e ao Secretário de Finanças, sobre o desempenho da DGCO;
													<br>
													III - coordenar estudos estatísticos quanto aos preços praticados pela Administração direta, assim como às necessidades de compras e contratações de seus órgãos;
													<br>
													IV - subsidiar, com informações gerenciais, a padronização dos processos de compras de bens e serviços.
													<br><br>
													Art. 6º - Compete à Divisão de Planejamento de Materiais - DPM:
													<br>
													I - elaborar e consolidar a Programação Periódica de Provimento de Materiais, com auxílio dos órgãos da Administração direta;
													<br>
													II - realizar estudos estatísticos quanto aos preços praticados pela Administração para a aquisição de bens, estimando a necessidade de compras diretas e licitações;
													<br>
													III - realizar, nos órgãos da Administração direta, levantamento dos fluxogramas de aquisição de bens, com e sem licitação, visando à padronização de que trata o inciso IV do artigo anterior;
													<br>
													IV - subsidiar o processo de tomada de decisões, no âmbito do DEPLAN.
													<br><br>
													Art. 7º - Compete à Divisão de Planejamento de Serviços - DPS:
													<br>
													I - elaborar e consolidar, com auxílio dos órgãos da Administração direta, a Programação Periódica de Provimento de Serviços;
													<br>
													II - realizar estudos estatísticos quanto aos preços praticados pela Administração para a prestação de serviços por terceiros, estimando, inclusive, a necessidade de licitações;
													<br>
													III - realizar, nos órgãos da Administração direta, levantamento dos fluxogramas de prestação de serviços por terceiros, com e sem licitação, visando à padronização de que trata o inciso IV do art. 5º deste Decreto;
													<br>
													IV - propor mecanismos de controle, a serem previstos nos contratos de serviços, que garantam mais eficiência na execução;
													<br>
													V - subsidiar o processo de tomada de decisões, no âmbito do DEPLAN.
													<br><br>
													Art. 8º - Compete ao Departamento de Relações Comerciais - DECOM:
													<br>
													I - coordenar as atividades de relacionamento comercial entre a Administração direta e seus fornecedores, efetivos ou potenciais, nos processos de compras de materiais e serviços;
													<br>
													II - coordenar a gestão dos cadastros de insumos e preços;
													<br>
													III - subsidiar as decisões do Diretor da DGCO e do Secretário de Finanças relativamente aos processos de administração de execução de compras e dos cadastros de insumos e preços.
													<br><br>
													Art. 9º - Compete à Divisão de Cadastro de Insumos e Cotações - DIC:
													<br>
													I - executar as atividades de relacionamento comercial entre a Administração direta e seus fornecedores, efetivos e potenciais, nos processos de cadastro de insumos e preços;
													<br>
													II - executar as cotações para instruir os processos de compras de bens e serviços, com e sem licitação;
													<br>
													III - executar a gestão do cadastro de insumos e preços, compreendendo atividades de registro, exclusão, alteração e atualização cadastral;
													<br>
													IV - gerir os cadastros de insumos e preços, disponibilizando os dados correspondentes para consulta dos órgãos da Administração direta;
													<br>
													V - subsidiar o processo de tomada de decisões, no âmbito do Departamento de Relações Comerciais, que envolvam informações oriundas da administração dos cadastros de insumos e preços e de cotações de materiais e serviços.
													<br><br>
													Art. 10 - Compete à Divisão de Execução de Compras - DEC:
													<br>
													I - executar as atividades relativas ao relacionamento comercial entre a Administração direta e seus fornecedores, efetivos e potenciais, nos processos de compras;
													<br>
													II - gerir os processos de compras dispensadas de licitação pelo valor, a partir do preenchimento da Requisição de Materiais e Serviços pelos órgãos da Administração direta;
													<br>
													III - gerenciar as atas do Sistema de Registro de Preços, bem como dar início aos processos de compras correspondentes;
													<br>
													IV - executar as atividades de operação e atualização dos dados contidos no Portal Eletrônico de Compras da Prefeitura do Recife;
													<br>
													V - comunicar à Assessoria Jurídica da SEFIN a inadimplência de fornecedores na entrega de materiais, conforme informações prestadas pelo Almoxarifado Central, após insucesso em prévia negociação;
													<br>
													VI - subsidiar o processo de tomada de decisões, no âmbito do Departamento de Relações Comerciais, que envolvam informações oriundas dos processos de execução de compras.
													<br><br>
													Art. 11 - Compete ao Departamento de Licitações e Contratos - DELIC:
													<br>
													I - coordenar, no âmbito da Administração direta, a realização dos procedimentos licitatórios, desde a fase preparatória;
													<br>
													II - coordenar as atividades de gestão dos contratos vigentes na Administração direta;
													<br>
													III - gerenciar o sistema de gestão de contratos DECON;
													<br>
													IV - gerenciar o Sistema de Credenciamento Unificado de Fornecedores da Prefeitura do Recife - SICREF;
													<br>
													V - gerenciar o cadastro de fornecedores do sistema SOFIN;
													<br>
													VI - subsidiar as decisões do Secretário de Finanças e do Diretor da DGCO relativamente aos procedimentos licitatórios, bem como aos contratos vigentes na Administração direta.
													<br><br>
													Art. 12 - Compete à Divisão de Suporte às Licitações - DSL:
													<br>
													I - executar atividades relativas à fase preparatória das licitações promovidas pelos órgãos da Administração direta;
													<br>
													II - prestar suporte administrativo às Comissões de Licitação antes, durante e após as sessões;
													<br>
													III - viabilizar o trâmite legal dos processos licitatórios, desde o pedido de abertura até a homologação;
													<br>
													IV - subsidiar as decisões, no âmbito do Departamento de Licitações e Contratos, envolvendo informações relativas aos procedimentos licitatórios;
													<br>
													V - executar atividades de suporte administrativo tais como reprografia, impressão e encadernação, visando atender necessidade de toda a estrutura da DGCO.
													<br><br>
													Art. 13 - Compete à Divisão de Monitoramento de Contratos - DMC:
													<br>
													I - executar atividades de gestão, acompanhamento e controle dos contratos vigentes na Administração direta.
													<br>
													II - executar as atividades relativas ao acompanhamento e controle de todos os contratos vigentes na Administração direta;
													<br>
													III - prestar informações aos órgãos da Administração direta quanto a prazos de vigência dos contratos, bem como a tempestividade para aditamentos;
													<br>
													IV - executar atividades relativas ao sistema de gestão de contratos DECON;
													<br>
													V - subsidiar as decisões, no âmbito do Departamento de Licitações e Contratos, quanto aos contratos vigentes na Administração direta.
													<br><br>
													Art. 14 - Compete à Divisão de Credenciamento de Fornecedores - DCF:
													<br>
													I - executar atividades relacionadas ao Sistema de Credenciamento Unificado de Fornecedores da Prefeitura do Recife - SICREF e ao SOFIN, compreendendo registro, exclusão e alteração cadastral dos fornecedores da Administração pública municipal;
													<br>
													II - promover o compartilhamento de informações, entre a DCF e as áreas tributárias e de arrecadação da Prefeitura, quanto à situação fiscal dos fornecedores cadastrados no SICREF e no SOFIN;
													<br>
													III - divulgar, na página oficial da Prefeitura do Recife na Internet, todos os editais de licitação da Administração direta, bem como expedir as cartas-convites aos fornecedores;
													<br>
													IV - disponibilizar, aos interessados, no espaço físico da Divisão, os editais referentes às licitações processadas pelas Comissões de Licitação da Administração direta;
													<br>
													V - subsidiar as decisões, no âmbito do Departamento de Licitações e Contratos, envolvendo os registros cadastrais dos fornecedores da Administração pública municipal, bem como a divulgação e disponibilização dos editais aos fornecedores, nos termos dos incisos III e IV deste artigo.
													<br><br>
													Art. 15 - Compete ao Departamento de Logística de Materiais - DELOG:
													<br>
													I - coordenar as atividades relativas à gestão da logística, compreendendo a armazenagem e distribuição dos materiais de uso comum;
													<br>
													II - coordenar a gestão do Almoxarifado Central da Prefeitura do Recife, fixando procedimentos de operação e controle dos materiais estocados;
													<br>
													III - fixar procedimentos de operação e controle dos estoques descentralizados e específicos destinados ao atendimento da Administração direta;
													<br>
													IV - fixar procedimentos de recebimento e guarda, este último mediante acordo com a Guarda Municipal, dos materiais a serem estocados no Almoxarifado Central da Prefeitura do Recife;
													<br>
													V - coordenar, de forma articulada com os demais órgãos da Administração direta, atividades para desenvolver a gestão do Almoxarifado Central da Prefeitura do Recife;
													<br>
													VI - gerenciar o monitoramento e controle dos estoques descentralizados e de bens específicos para atender necessidade da Administração direta;
													<br>
													VII - coordenar o sistema informatizado de controle de estoques;
													<br>
													VIII - coordenar a produção de informações sobre o abastecimento de bens dos órgãos da Administração direta;
													<br>
													IX - subsidiar as decisões do Secretário de Finanças e do Diretor da DGCO quanto aos processos de gestão de estoques, armazenagem e distribuição de materiais.
													<br><br>
													Art. 16 - Compete à Divisão de Monitoramento de Estoques - DME:
													<br>
													I - executar atividades de monitoramento e controle dos estoques do Almoxarifado Central da Prefeitura do Recife;
													<br>
													II - executar a atividades de monitoramento e controle dos estoques descentralizados e de bens específicos para atender necessidade da Administração direta;
													<br>
													III - executar atividades destinadas à reposição de estoques, no Âmbito da Administração direta, com base nas técnicas de gestão de materiais;
													<br>
													IV - subsidiar decisões, no âmbito do Departamento de Logística de Materiais, envolvendo os processos de gestão de estoques de materiais.
													<br><br>
													Art. 17 - Compete à Divisão de Controle e Expedição de Materiais - DCE:
													<br>
													I - executar as atividades de armazenagem e distribuição dos materiais de uso comum para atender os órgãos da Administração direta;
													<br>
													II - executar os procedimentos, fixados pelo DELOG, de operação e controle dos estoques do Almoxarifado Central;
													<br>
													III - executar atividades de recebimento dos materiais a serem estocados no Almoxarifado Central da Prefeitura do Recife, avaliando, inclusive, a técnica e qualidade dos materiais entregues pelos fornecedores, com base nos requisitos dos processos de aquisição correspondentes;
													<br>
													IV - gerenciar a realização dos procedimentos fixados pelo DELOG, mediante acordo com a Guarda Municipal, para a guarda dos materiais estocados no Almoxarifado Central da Prefeitura do Recife;
													<br>
													V - subsidiar as decisões, no âmbito do Departamento de Logística de Materiais, envolvendo os processos de armazenagem e distribuição de materiais de uso comum.
													<br><br>
													Art. 18 - Compete à Assessoria Executiva:
													<br>
													I - executar atividades de secretariado em suporte a toda a estrutura da DGCO;
													<br>
													II - executar a gestão de protocolo da DGCO, compreendendo o recebimento, registro, tramitação, distribuição e expedição de todos os documentos, processos e correspondências que nela tramitem;
													<br>
													III - executar a gestão do arquivo local da DGCO, compreendendo controle, guarda e pesquisa;
													<br>
													IV - gerenciar as correspondências da DGCO;
													<br>
													V - manter o controle das publicações no Diário Oficial do Município;
													<br>
													VI - executar atividades relativas ao atendimento ao público em geral da DGCO, inclusive, de recepção, telefonia e outros meios de comunicação;
													<br>
													VII - viabilizar o suprimento administrativo de materiais de serviços de suporte às atividades de competência da DGCO, seus departamentos, divisões e assessorias;
													<br>
													VIII - redigir a documentação oficial solicitada pelo Diretor da DGCO;
													<br>
													IX - administrar a agenda do Diretor DGCO;
													<br>
													X - subsidiar as decisões, no âmbito da estrutura da DGCO, envolvendo atividades de suporte administrativo.
													<br><br>
													Art. 19 - Compete à Assessoria de Tecnologia da Informação - ATI:
													<br>
													I - executar atividades de suporte em tecnologia da informação, compreendidas as demandas relativas a Hardware, Software, rede lógica e de transmissão de dados, para toda a estrutura da DGCO, incluindo suporte aos usuários;
													<br>
													II - realizar manutenção das funcionalidades técnico-eletrônicas do Portal de Compras da Prefeitura do Recife;
													<br>
													III - subsidiar as decisões, no âmbito da DGCO, que envolvam tecnologia da informação quanto às atividades da DGCO.
													<br><br>
													Art. 20 - Compete à Assessoria Jurídica:
													<br>
													I - elaborar minutas de projetos de leis, decretos, portarias e demais atos normativos referentes a licitações e contratos administrativos, no âmbito da Administração municipal, mediante solicitação do Secretário de Finanças ou do Diretor da DGCO.
													<br>
													II - elaborar minutas de edital e contratos, mediante solicitação do Secretário de Finanças ou do Diretor da DGCO;
													<br>
													III - subsidiar análise jurídica dos processos licitatórios, quando solicitado pelas comissões;
													<br>
													IV - subsidiar a análise de recursos interpostos por licitantes em processos licitatórios;
													<br><br>
													Parágrafo Único - As atribuições da Assessoria Jurídicas não implicam a dispensa da análise e aprovação final dos seus respectivos atos pela Procuradoria Geral do Município, nos termos do art. 9º da Lei Municipal nº 16.662/2001.
													<br><br>
													Art. 21 - Este Decreto entra em vigor na data de sua publicação.
													<br><br>
													Recife, 31 de Março de 2003.
												</p>
											</td>
										</tr>
										<tr>
											<td class="textonegrito">
												<p align="center">
													João Paulo
													<br>
													Prefeito
													<br><br>
													José Eduardo Santos Vital
													<br>
													Secretário de Finanças
													<br><br>
													Bruno Ariosto Luna de Holanda
													<br>
													Secretário de Assuntos Jurídicos
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

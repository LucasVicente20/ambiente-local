<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: ConsDecretoRegistroPreco.php
# Autor:    Rossana Lira
# Data:     15/03/06
# Objetivo: Programa de Consulta do Decreto Municipal de Registro de Preço
#
# Autor:    Pitang Agile TI
# Data:     20/03/2015
# Objetivo: [CR redmine 280] Alterar conteúdo do decreto
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
<form action="ConsDecretoRegistroPreco.php" method="post" name="InfRegistroPreco">
<br><br><br><br><br>
<table cellpadding="3" border="0" summary="">
  <!-- Caminho -->
  <tr>
    <td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
    <td align="left" class="textonormal" colspan="2">
      <font class="titulo2">|</font>
      <a href="../index.php"><font color="#000000">Página Principal</font></a> > Registro Preço > Decreto
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
		    					DECRETO DE REGISTRO DE PREÇO
		          	</td>
		        	</tr>
	  	      	<tr>
	    	      	<td class="textonormal">
	          	   	<p align="justify" class="textonegrito">
						DECRETO Nº 27.070 DE 10 DE MAIO DE 2013<br>
					</p>
	          	   	<p align="justify">
										Ementa: Regulamenta o Sistema de Registro de Preços, previsto no art. 15 da Lei nº 8.666, de 21 de junho de 1993.<br>
										<br>
										O PREFEITO DO RECIFE, no uso das atribuições que lhe confere o inciso IV do art. 54 da Lei Orgânica do Recife, e nos termos do
disposto nos arts. 15 e 118 da Lei nº 8.666, de 21 de junho de 1993, e no art. 11 da Lei nº 10.520, de 17 de julho de 2002,<br/>
										DECRETA:
										<br/><br/>
										CAPÍTULO I
                                        <br/><br/>
                                        DISPOSIÇÕES GERAIS
                                        <br/><br/>
                                        Art. 1º As contratações de serviços e a aquisição de bens, quando efetuadas pelo Sistema de Registro de Preços - SRP, no
âmbito da administração pública municipal direta e Indireta, obedecerão ao disposto neste Decreto.
<br/><br/>
Art. 2º Para os efeitos deste Decreto, são adotadas as seguintes definições:<br/>
I - sistema de Registro de Preços - conjunto de procedimentos para registro formal de preços relativos à prestação de serviços e
aquisição de bens, para contratações futuras;<br/>
II - ata de registro de preços - documento vinculativo, obrigacional, com característica de compromisso para futura contratação,
em que se registram os preços, fornecedores, órgãos participantes e condições a serem praticadas, conforme as disposições
contidas no instrumento convocatório e propostas apresentadas;<br/>
III - órgão gerenciador - órgão ou entidade da administração pública municipal responsável pela condução do conjunto de
procedimentos para registro de preços e gerenciamento da ata de registro de preços dele decorrente;<br/>
IV - órgão participante - órgão ou entidade da administração pública que participa dos procedimentos iniciais do Sistema de
Registro de Preços e integra a ata de registro de preços;<br/>
V - órgão não participante - órgão ou entidade da administração pública que, não tendo participado dos procedimentos iniciais
da licitação, atendidos os requisitos desta norma, faz adesão à ata de registro de preços;<br/>
VI - fornecedores - empresas vencedoras de item ou itens em licitação pública, através do sistema de registro de preços e que
tenham seus preços registrados e/ou classificados;<br/>
VII - compras corporativas - as aquisições ou contratações de serviços globais de determinados serviços e bens de uso comum,
visando o suprimento de vários órgãos ou entidades.
<br/><br/>
§ 1º - A Secretaria de Administração e Gestão de Pessoas, através de seu órgão competente, é o órgão gerenciador dos
registros de preços realizados para atender aos órgãos da Administração Direta.
<br/><br/>
§2º- Em se tratando de compras corporativas, a Secretaria de Administração e Gestão de Pessoas, através de seu órgão
competente, será o gerenciador dos registros de preços, inclusive, nos casos de serem realizados pelas entidades da
Administração Indireta.
<br/><br/>
§3º - Os registros de preços da Administração Indireta poderão ser realizados pelas respectivas entidades, competindo à
Secretaria de Administração e Gestão de Pessoas supervisionar os parâmetros econômicos da contratação, dependendo de
autorização prévia desta Secretaria quando se tratar de registro de preços para atender às compras corporativas, nos termos de
regulamentação específica.
<br/><br/>
Art. 3º O Sistema de Registro de Preços poderá ser adotado nas seguintes hipóteses:<br/>
I - quando, pelas características do bem ou serviço, houver necessidade de contratações frequentes;<br/>
II - quando for conveniente a aquisição de bens com previsão de entregas parceladas ou contratação de serviços remunerados
por unidade de medida ou em regime de tarefa;<br/>
III - quando for conveniente a aquisição de bens ou a contratação de serviços para atendimento a mais de um órgão ou
entidade, ou a programas de governo; ou<br/>
IV - quando, pela natureza do objeto, não for possível definir previamente o quantitativo a ser demandado pela Administração.
<br/><br/>
CAPÍTULO II
<br/><br/>
DA INTENÇÃO PARA REGISTRO DE PREÇOS
<br/><br/>
Art. 4º A intenção para registro de preço será formalizada através da Solicitação de Compras ou Contratação de Serviços (SCC)
presente no Portal de Compras da Prefeitura do Recife.
<br/><br/>
CAPÍTULO III
<br/><br/>
DAS COMPETÊNCIAS DO ÓRGÃO GERENCIADOR
<br/><br/>
Art. 5º Caberá ao órgão gerenciador a prática de todos os atos de controle e administração do Sistema de Registro de Preços, e
ainda o seguinte:<br/>
I - registrar sua intenção de registro de preços no Portal de Compras da Prefeitura do Recife;<br/>
II - consolidar informações relativas à estimativa individual e total de consumo, promovendo a adequação dos respectivos
termos de referência ou projetos básicos encaminhados para atender aos requisitos de padronização e racionalização;<br/>
III - promover os atos necessários à instrução processual para a realização do procedimento licitatório;<br/>
IV - realizar pesquisa de mercado para identificação do valor estimado da licitação e consolidar os dados das pesquisas de
mercado realizadas pelos órgãos e entidades participantes;<br/>
V - confirmar junto aos órgãos participantes a sua concordância com o objeto a ser licitado, inclusive quanto aos quantitativos e
termo de referência ou projeto básico;<br/>
VI - realizar o procedimento licitatório;<br/>
VII - gerenciar a ata de registro de preços;<br/>
VIII - conduzir eventuais renegociações dos preços registrados;<br/>
IX - aplicar, garantida a ampla defesa e o contraditório, as penalidades decorrentes de infrações no procedimento licitatório; e<br/>
X - aplicar, garantida a ampla defesa e o contraditório, as penalidades decorrentes do descumprimento do pactuado na ata de
registro de preços ou do descumprimento das obrigações contratuais, em relação às suas próprias contratações.
<br/><br/>
§ 1º A ata de registro de preços, disponibilizada no Portal de Compras da Prefeitura do Recife, poderá ser assinada por
certificação digital.
<br/><br/>
§ 2º O órgão gerenciador poderá solicitar auxílio técnico aos órgãos participantes para execução das atividades previstas nos
incisos III, IV, VI e VII deste artigo.
<br/><br/>
CAPÍTULO IV
<br/><br/>
DAS COMPETÊNCIAS DO ÓRGÃO PARTICIPANTE
<br/><br/>
Art. 6º O órgão participante será responsável pela manifestação de interesse em participar do registro de preços, providenciando
o encaminhamento ao órgão gerenciador de sua estimativa de consumo, local de entrega e, quando couber, cronograma de
contratação e respectivas especificações ou termo de referência ou projeto básico, nos termos da Lei nº 8.666, de 21 de junho
de 1993, da Lei nº 10.520, de 17 de julho de 2002, e da legislação municipal atinente à matéria, adequado ao registro de preços
do qual pretende fazer parte, devendo ainda:<br/>
I - manifestar, junto ao órgão Gerenciador, mediante a utilização da Solicitação de Compras ou Contratação, sua concordância
com o objeto a ser licitado, antes da realização do procedimento licitatório; e<br/>
II - tomar conhecimento da ata de registros de preços, inclusive de eventuais alterações, para o correto cumprimento de suas
disposições;<br/>
Parágrafo único - Cabe ao órgão participante aplicar, garantida a ampla defesa e o contraditório, as penalidades decorrentes do
descumprimento do pactuado na ata de registro de preços ou do descumprimento das obrigações contratuais, em relação às
suas próprias contratações, informando as ocorrências ao órgão gerenciador.
<br/><br/>
CAPÍTULO V
<br/><br/>
DA LICITAÇÃO PARA REGISTRO DE PREÇOS
<br/><br/>
Art. 7º A licitação para registro de preços será realizada na modalidade de concorrência, do tipo menor preço, nos termos da Lei
nº 8.666, de 21 de junho de 1993, ou na modalidade de pregão, nos termos da Lei nº 10.520, de 17 de julho 2002, e será
precedida de ampla pesquisa de mercado.
Parágrafo Único - O julgamento por técnica e preço poderá ser excepcionalmente adotado a critério do órgão gerenciador e
mediante despacho devidamente fundamentado do Secretário de Administração e Gestão de Pessoas.
<br/><br/>
Art. 8º O órgão gerenciador poderá distribuir os itens do objeto em lotes, quando técnica e economicamente viável, para
possibilitar maior competitividade, observados o prazo e o local de entrega ou de prestação dos serviços.
<br/><br/>
§ 1º No caso de serviços, a divisão se dará em função da unidade de medida adotada para aferição dos produtos e resultados, e
será observada a demanda específica de cada órgão ou entidade participante do certame.
<br/><br/>
Art. 9º O edital de licitação para registro de preços observará o disposto na Lei nº 8.666, de 21 de junho de 1993, e na Lei nº
10.520, de 17 de julho de 2002, e contemplará, no mínimo:<br/>
I - a especificação ou descrição do objeto, que explicitará o conjunto de elementos necessários e suficientes, com nível de
precisão adequado para a caracterização do bem ou serviço, inclusive definindo as respectivas unidades de medida usualmente
adotadas;<br/>
II - estimativa de quantidades a serem adquiridas pelo órgão gerenciador e órgãos participantes;<br/>
III - a previsão de contratação por órgãos não participantes, observado o limite do quíntuplo de adesões previsto no § 4º do art.
22, no caso de o órgão gerenciador admitir adesões;<br/>
IV - condições quanto ao local, prazo de entrega, forma de pagamento, e nos casos de serviços, quando cabível, frequência,
periodicidade, características do pessoal, materiais e equipamentos a serem utilizados, procedimentos, cuidados, deveres,
disciplina e controles a serem adotados;<br/>
V - prazo de validade do registro de preço, observado o disposto no caput do art. 12;<br/>
VI - órgãos e entidades participantes do registro de preço;<br/>
VII - modelos de planilhas de custo e minutas de contratos, quando cabível;<br/>
VIII - penalidades por descumprimento das condições;<br/>
IX - minuta da ata de registro de preços como anexo; e<br/>
X - realização periódica de pesquisa de mercado para comprovação da vantajosidade.
<br/><br/>
Parágrafo Único - O edital poderá admitir, como critério de julgamento, o menor preço aferido pela oferta de desconto sobre
tabela de preços praticados no mercado, desde que tecnicamente justificado.
<br/><br/>
Art. 10. Após o encerramento da etapa competitiva, os licitantes poderão reduzir seus preços ao valor da proposta do licitante
mais bem classificado.Parágrafo único. A apresentação de novas propostas para atender ao disposto neste artigo não prejudicará o resultado do
certame em relação ao licitante mais bem classificado.
<br/><br/>
CAPÍTULO VI
<br/><br/>
DO REGISTRO DE PREÇOS E DA VALIDADE DA ATA
<br/><br/>
Art. 11. Após a homologação da licitação e desde que previsto no edital de licitação, o registro de preços observará, entre
outras, as seguintes condições:<br/>
I - será incluído, na respectiva ata da licitação, o registro dos licitantes que aceitarem cotar os bens ou serviços com preços
iguais ao do licitante vencedor na sequência da classificação do certame;<br/>
II - o preço registrado com indicação dos fornecedores será divulgado no Portal de Compras da Prefeitura do Recife e ficará
disponibilizado durante a vigência da ata de registro de preços; e<br/>
III - a ordem de classificação dos licitantes registrados na ata deverá ser respeitada nas contratações.
<br/>§ 1º O registro a que se refere o inciso I tem por objetivo a formação de cadastro de reserva, no caso de exclusão do primeiro
colocado da ata, nas hipóteses previstas nos arts. 20 e 21.
<br/>§ 2º Serão registrados na ata de registro de preços, nesta ordem:<br/>
I - os preços e quantitativos do licitante mais bem classificado durante a etapa competitiva; e<br/>
II - os preços e quantitativos dos licitantes que tiverem aceitado cotar seus bens ou serviços em valor igual ao do licitante mais
bem classificado.
<br/><br/>
Art. 12. O prazo de validade da ata de registro de preços não será superior a doze meses, incluídas eventuais prorrogações,
conforme o inciso III do § 3º do art. 15 da Lei nº 8.666, de 21 de junho 1993.
<br/><br/>
§ 1º É vedado efetuar acréscimos nos quantitativos fixados pela ata de registro de preços, inclusive o acréscimo de que trata o §
1º do art. 65 da Lei nº 8.666, de 1993.
<br/><br/>
§ 2º A vigência dos contratos decorrentes do Sistema de Registro de Preços será definida nos instrumentos convocatórios,
observado o disposto no art. 57 da Lei nº 8.666, de 1993.
<br/><br/>
§ 3º Os contratos decorrentes do Sistema de Registro de Preços poderão ser alterados, observado o disposto no art. 65 da Lei nº
8.666, de 1993.
<br/><br/>
§ 4º O contrato decorrente do Sistema de Registro de Preços deverá ser assinado no prazo de validade da ata de registro de
preços.
<br/><br/>
CAPÍTULO VII
<br/><br/>
DA ASSINATURA DA ATA E DA CONTRATAÇÃO COM FORNECEDORES REGISTRADOS
<br/><br/>
Art. 13. Homologado o resultado da licitação, os fornecedores classificados, observado o disposto no art. 11, serão convocados
para assinar a ata de registro de preços, dentro do prazo e condições estabelecidos no instrumento convocatório, podendo o
prazo ser prorrogado uma vez, por igual período, quando solicitado pelo fornecedor e desde que ocorra motivo justificado aceito
pela administração.
<br/><br/>
Parágrafo único. É facultado à administração, quando o convocado não assinar a ata de registro de preços no prazo e condições
estabelecidos, convocar os licitantes remanescentes, na ordem de classificação, para fazê-lo em igual prazo e nas mesmas
condições propostas pelo primeiro classificado.
<br/><br/>
Art. 14. A ata de registro de preços implicará compromisso de fornecimento nas condições estabelecidas, após cumpridos os
requisitos de publicidade.
Parágrafo único. A recusa injustificada de fornecedor classificado em assinar a ata, dentro do prazo estabelecido neste artigo,
ensejará a aplicação das penalidades legalmente estabelecidas.
<br/><br/>
Art. 15. A contratação com os fornecedores registrados será formalizada pelo órgão interessado por intermédio de instrumento
contratual, emissão de nota de empenho de despesa, autorização de compra ou outro instrumento hábil, conforme o art. 62 da
Lei nº 8.666, de 21 de junho de 1993.
<br/><br/>
Art. 16. A existência de preços registrados não obriga a administração a contratar, facultando-se a realização de licitação
específica para a aquisição pretendida, assegurada preferência ao fornecedor registrado em igualdade de condições.
<br/><br/>
CAPÍTULO VIII
<br/><br/>
DA REVISÃO E DO CANCELAMENTO DOS PREÇOS REGISTRADOS
<br/><br/>
Art. 17. Os preços registrados poderão ser revistos em decorrência de eventual redução dos preços praticados no mercado ou de
fato que eleve o custo dos serviços ou bens registrados, cabendo ao órgão gerenciador promover as negociações junto aos
fornecedores, com apoio dos órgãos participantes, observadas as disposições contidas na alínea "d" do inciso II do caput do art.
65 da Lei nº 8.666, de 1993.
<br/><br/>
Art. 18. Quando o preço registrado tornar-se superior ao preço praticado no mercado por motivo superveniente, o órgão
gerenciador convocará os fornecedores para negociarem a redução dos preços aos valores praticados pelo mercado.
<br/><br/>
§ 1º Os fornecedores que não aceitarem reduzir seus preços aos valores praticados pelo mercado serão liberados do
compromisso assumido, sem aplicação de penalidade.
<br/><br/>
§ 2º A ordem de classificação dos fornecedores que aceitarem reduzir seus preços aos valores de mercado observará a
classificação original.
<br/><br/>
Art. 19. Quando o preço de mercado tornar-se superior aos preços registrados e o fornecedor não puder cumprir o compromisso,
o órgão gerenciador poderá:<br/>
I - liberar o fornecedor do compromisso assumido, caso a comunicação ocorra antes do pedido de fornecimento, e sem aplicação
da penalidade se confirmada a veracidade dos motivos e comprovantes apresentados; e<br/>
II - convocar os demais fornecedores para assegurar igual oportunidade de negociação.
<br/>Parágrafo único. Não havendo êxito nas negociações, o órgão gerenciador deverá proceder à revogação da ata de registro de
preços, adotando as medidas cabíveis para obtenção da contratação mais vantajosa.
<br/><br/>
Art. 20. O registro do fornecedor será cancelado quando:<br/>
I - descumprir as condições da ata de registro de preços ou exigências do instrumento convocatório que deu origem ao Registro
de Preços;<br/>
II - não retirar a nota de empenho ou instrumento equivalente no prazo estabelecido pela Administração, sem justificativa
aceitável;<br/>
III - não aceitar reduzir o seu preço registrado, na hipótese deste se tornar superior àqueles praticados no mercado; ou<br/>
IV - sofrer sanção prevista nos incisos III ou IV do caput do art. 87 da Lei nº 8.666, de 1993, ou no art. 7º da Lei nº 10.520, de
2002.
<br/><br/>
Parágrafo único. O cancelamento de registros nas hipóteses previstas nos incisos I, II e IV deste artigo, será formalizado por
despacho do órgão gerenciador, assegurado o contraditório e a ampla defesa.
<br/><br/>
Art. 21. O cancelamento do registro de preços poderá ocorrer por fato superveniente, decorrente de caso fortuito ou força maior,
que prejudique o cumprimento da ata, devidamente comprovados e justificados:<br/>
I - por razão de interesse público; ou<br/>
II - a pedido do fornecedor.
<br/><br/>
CAPÍTULO IX
<br/><br/>
DA UTILIZAÇÃO DA ATA DE REGISTRO DE PREÇOS POR ÓRGÃO OU ENTIDADES NÃO PARTICIPANTES<br/>
Art. 22. Desde que devidamente justificada a vantagem, a ata de registro de preços, durante sua vigência, poderá ser utilizada
por qualquer órgão ou entidade da administração pública que não tenha participado do certame licitatório, mediante anuência do
órgão gerenciador.
<br/><br/>
§ 1º Os órgãos e entidades que não participaram do registro de preços, quando desejarem fazer uso da ata de registro de
preços, deverão consultar o órgão gerenciador da ata para manifestação de anuência quanto à adesão.
<br/><br/>
§ 2º Caberá ao fornecedor beneficiário da ata de registro de preços, observadas as condições nela estabelecidas, optar pela
aceitação ou não do fornecimento decorrente de adesão, desde que não prejudique as obrigações presentes e futuras decorrentes da ata, assumidas com o órgão gerenciador e órgãos participantes.
<br/><br/>
§ 3º As aquisições ou contratações adicionais a que se refere este artigo não poderão exceder, por órgão ou entidade, a cem por
cento dos quantitativos dos itens do instrumento convocatório e registrados na ata de registro de preços para o órgão
gerenciador e órgãos participantes.
<br/><br/>
§ 4º O instrumento convocatório deverá prever que o quantitativo decorrente das adesões à ata de registro de preços não
poderá exceder, na totalidade, ao quíntuplo do quantitativo de cada item registrado na ata de registro de preços para o órgão
gerenciador e órgãos participantes, independente do número de órgãos não participantes que aderirem.
<br/><br/>
§ 5o O órgão gerenciador somente poderá autorizar adesão à ata após a primeira aquisição ou contratação por órgão integrante
da ata.
<br/><br/>
§ 6º Compete ao órgão não participante os atos relativos à cobrança do cumprimento pelo fornecedor das obrigações
contratualmente assumidas e a aplicação, observada a ampla defesa e o contraditório, de eventuais penalidades decorrentes do
descumprimento de cláusulas contratuais, em relação às suas próprias contratações, informando as ocorrências ao órgão
gerenciador.
<br/><br/>
§ 7º Os órgãos e entidades da Administração Pública Municipal poderão contratar mediante o uso de Ata de Registro de Preços
de órgão ou entidade de qualquer esfera da Administração Pública que possua orçamento igual ou superior ao do Município do
Recife, cumpridos os seguintes requisitos:<br/>
I - comprovação da vantajosidade dos preços registrados, apurada pelo órgão ou entidade interessada;<br/>
II - prévia consulta e anuência do órgão gerenciador da Ata;<br/>
III - aceitação, pelo fornecedor, da contratação pretendida, condicionada ao cumprimento do compromisso assumido na Ata de
Registro de Preços;<br/>
IV - manutenção das mesmas condições do Registro, inclusive as negociações promovidas pelo órgão gerenciador;<br/>
V - limitação da quantidade a 100% (cem por cento) dos quantitativos registrados na Ata;<br/>
VI - autorização prévia da Secretaria de Administração e Gestão de Pessoas, por seu órgão competente;<br/>
VIII - formalização do compromisso entre o órgão aderente e o fornecedor, mediante Termo de Adesão à Ata de Registro de
Preços ou Contrato.
<br/><br/>
CAPÍTULO X
<br/><br/>
DISPOSIÇÕES FINAIS E TRANSITÓRIAS
<br/><br/>
Art. 23. A Administração utilizará recursos de tecnologia da informação na operacionalização do disposto neste Decreto e
automatizará procedimentos de controle e atribuições do órgão gerenciador e participantes.
<br/><br/>
Art. 24. As atas de registro de preços vigentes, decorrentes de certames realizados sob a vigência do Decreto nº 19.205 de 08
de março de 2002, poderão ser utilizadas pelos órgãos gerenciadores e participantes, até o término de sua vigência.
<br/><br/>
Art. 25. A Secretaria de Administração e Gestão de Pessoas editará normas complementares a este Decreto.
<br/><br/>
Art. 26. Este Decreto entra na data de sua publicação.
<br/><br/>
Art. 27. Fica revogado o Decreto nº 19.205 de 08 de março de 2002.
<br/><br/>
Recife, 10 de maio de 2013.
<br/><br/>
GERALDO JULIO DE MELLO FILHO<br/>
PREFEITO
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

<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: ConsDecreto19205.php
# Autor:    Rossana Lira
# Data:     04/09/03
# Objetivo: Programa de Consulta do Decreto 19205
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
	  exit();
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
	document.Decreto19205.Botao.value=valor;
	document.Decreto19205.submit();
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="ConsDecreto19205.php" method="post" name="Decreto19205">
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
		    					DECRETO Nº 19.205 DE 08 DE MARÇO DE 2002
		          	</td>
		        	</tr>
							<tr>
							  <td>
							  	<table border="0" summary="">
										<tr>
											<td class="textonegrito">
												<p align="right">
													Ementa: Regulamenta, no Município de Recife, o Sistema de Registro de Preços previsto
													no art. 15 da Lei nº 8.666, de 21 de junho de 1993.
													<br><br>
												</p>
			     						</td>
			     					</tr>
			     					<tr>
			     						<td>
												<p align="justify" class="textonormal">
													O PREFEITO DO RECIFE, no uso das atribuições que lhe confere o Inciso IV do art. 54 da
													Lei Orgânica do Recife, e nos termos do disposto nos arts. 15 e 118 da Lei nº 8.666, de
													21 de junho de 1993, D E C R E T A:
													<br><br>
												</p>
											</td>
										</tr>
										<tr>
			     						<td>
												<p align="justify" class="textonormal">
													Art. 1º - O Sistema de Registro de Preços para compras e serviços dos órgãos da Administração
													Direta e Indireta do Município de Recife, obedecerá ao disposto neste Decreto.
													<br>
													Parágrafo Único - Para os efeitos deste Decreto, são adotadas as seguintes definições:
													<br>
													I - Sistema de Registro de Preços - SRP - conjunto de procedimentos para registro formal
													de preços relativos à prestação de serviços, aquisição e locação de bens, para contratações futuras;
													<br>
													II - Ata de Registro de Preços - documento vinculativo, obrigacional, com característica
													de compromisso para futura contratação, onde se registram os preços, fornecedores, Órgãos
													Participantes e condições a serem praticadas, conforme as disposições contidas no instrumento
													convocatório e propostas apresentadas, respeitado o disposto no artigo 11 deste Decreto;
													<br>
													III - Diretoria Geral de Compras de Bens e Serviços - órgão da Secretaria de Finanças responsável
													pela condução do conjunto de procedimentos do certame para registro de preços e gerenciamento da
													Ata de Registro de Preços dele decorrente;
													<br>
													IV - Órgão Participante - órgão ou entidade da Administração municipal direta ou indireta que participa
													dos procedimentos iniciais do SRP e integra a Ata de Registro de Preços; e,
													<br>
													V - Fornecedores: empresas vencedoras de item ou itens em concorrência pública, através do sistema de
													registro de preços e que tenham seus preços registrados e/ou classificados.
													<br><br>
													Art. 2º - As licitações por registro de preços serão realizadas pela Secretaria de Finanças na modalidade
													Concorrência do tipo menor preço, conforme estabelece o inciso I, parágrafo 3º do artigo 15 da Lei nº 8.666/93.
													<br><br>
													§ 1º - A Secretaria de Finanças poderá delegar a outros órgãos e secretarias a realização de registros de preços,
													devendo nestes casos supervisionar os parâmetros econômicos da contratação.
													<br><br>
													§ 2º - Excepcionalmente, a critério do órgão gerenciador e mediante despacho devidamente fundamentado da autoridade
													máxima do órgão ou entidade, poderá ser adotado o tipo de licitação técnica e preço.
													<br><br>
													Art. 3º - O Sistema de Registro de Preços será utilizado pela Administração Municipal para a aquisição de materiais
													médico-hospitalares, odontológicos, de laboratório, medicamentos e soluções, gêneros alimentícios, materiais e gêneros
													de consumo, materiais permanentes; para a aquisição de serviços e ainda quando:
													<br>
													I - pelas características do bem ou serviço, houver necessidade de contratações freqüentes;
													<br>
													II - for mais conveniente a aquisição de bens com previsão de entregas parceladas ou contratação de serviços necessários
													à Administração para o desempenho de suas atribuições;
													<br>
													III - for conveniente a aquisição de bens ou a contratação de serviços para atendimento a mais de um órgão ou entidade,
													ou a programas de governo;
													<br>
													IV - pela natureza do objeto não for possível definir previamente o quantitativo a ser demandado pela Administração.
													<br><br>
													Parágrafo Único - Poderá ser realizado registro de preços para contratação de bens e serviços de informática, obedecida
													à legislação vigente, desde que devidamente justificada e caracterizada a vantagem econômica.
													<br><br>
													Art. 4º - Caberá à Diretoria Geral de Compras de Bens e Serviços da Secretaria de Finanças a prática de todos os atos de
													controle e administração do SRP, e ainda o seguinte:
													<br>
													I - convidar, mediante correspondência eletrônica ou outro meio eficaz, os órgãos e entidades para participarem do registro
													de preços;
													<br>
													II - consolidar todas as informações relativas à estimativa individual e total de consumo, promovendo a adequação dos respectivos
													projetos básicos encaminhados para atender aos requisitos de padronização e racionalização;
													<br>
													III - promover todos os atos necessários à instrução processual para a realização do procedimento licitatório pertinente,
													inclusive a documentação das justificativas nos casos em que a restrição à competição for admissível pela lei;
													<br>
													IV - realizar a necessária pesquisa de mercado com vistas à identificação dos valores a serem licitados;
													<br>
													V - confirmar junto aos Órgãos Participantes a sua concordância com o objeto a ser licitado, inclusive quanto aos quantitativos
													e projeto-básico;
													<br>
													VI - coordenar a realização do procedimento licitatório, bem como os atos dele decorrentes, tais como a assinatura da Ata e o encaminhamento
													de sua cópia aos demais Órgãos Participantes;
													<br>
													VII - gerenciar a Ata de Registro de Preços, providenciando a indicação, sempre que solicitado, dos fornecedores, para atendimento às
													necessidades da Administração, obedecendo à ordem de classificação e os quantitativos de contratação definidos pelos participantes da Ata;
													<br>
													VIII - conduzir os procedimentos relativos a eventuais renegociações dos preços registrados e a aplicação de penalidades por descumprimento
													do pactuado na Ata de Registro de Preços; e,
													<br>
													IX - realizar, quando necessário, prévia reunião com licitantes, visando informá-los das peculiaridades do SRP e coordenar, com os Órgãos Participantes,
													a qualificação mínima dos respectivos gestores indicados.
													<br><br>
													Parágrafo Único - Caberá à Secretaria de Saúde a gestão do sistema de registro de preços de medicamentos, materiais e equipamentos médico-hospitalares,
													materiais de laboratório e materiais odontológicos.
													<br><br>
													Art. 5º - O Registro de Preços será sempre precedido de ampla pesquisa de mercado, a ser realizada pelo órgão responsável pela licitação, e acompanhada
													pelos órgãos interessados, quando necessário.
													<br><br>
													Art. 6º - Todos os órgãos da Administração Municipal interessados em participar dos processos de registro de preços, devem efetuar o levantamento do
													quantitativo anual estimado, indicar as dotações orçamentárias que darão cobertura às despesas, elaborar um cronograma de contrAtação e cuidar das especificações
													dos materiais, nos termos da Lei nº 8.666, de 1993, adequados ao registro de preço do qual pretende fazer parte.
													<br><br>
													Art. 7º - Os órgãos da Administração de que trata o artigo anterior devem ainda:
													<br>
													I - garantir que todos os atos inerentes ao procedimento para sua inclusão no registro de preços a ser realizado estejam devidamente formalizados e aprovados pela
													autoridade competente;
													<br>
													II - manifestar, junto à Diretoria Geral de Compras de Bens e Serviços, sua concordância com o objeto a ser licitado, antes da realização do procedimento licitatório;
													<br>
													III - tomar conhecimento da Ata de Registros de Preços, inclusive as alterações porventura ocorridas, com o objetivo de assegurar, quando de seu uso, o correto cumprimento de suas disposições, logo depois de concluído o procedimento licitatório.
													<br>
													IV - Indicar o gestor do contrato.
													<br><br>
													Art. 8º - Ao gestor do contrato, além das atribuições previstas no art. 67 da Lei n° 8.666/93, compete:
													<br>
													I - promover consulta prévia junto à Diretoria Geral de Compras de Bens e Serviços, quando da necessidade de contratação, a fim de obter a indicação do fornecedor, os respectivos quantitativos e os valores a serem praticados, encaminhando, posteriormente, as informações sobre a contratação efetivamente realizada;
													<br>
													II - assegurar-se, quando do uso da Ata de Registro de Preços, de que a contratação a ser procedida atenda aos seus interesses, sobretudo quanto aos valores praticados, informando à Diretoria Geral de Compras de Bens e Serviços eventual desvantagem, quanto à sua utilização;
													III - zelar, após receber a indicação do fornecedor, pelos demais atos relativos ao cumprimento, pelo fornecedor, das obrigações contratualmente assumidas, e também, em coordenação com a Diretoria Geral de Compras de Bens e Serviços, pela aplicação de eventuais penalidades decorrentes do descumprimento de cláusulas contratuais; e,
													<br>
													IV - informar à Diretoria Geral de Compras de Bens e Serviços, quando de sua ocorrência, a recusa do fornecedor em atender às condições estabelecidas em edital, firmadas na Ata de Registro de Preços, as divergências relativas à entrega, as características e origem dos bens licitados e a recusa do fornecedor em assinar contrato para fornecimento ou prestação de serviços.
													<br><br>
													Art. 9º - A Ata de Registro de Preços, durante sua vigência, poderá ser utilizada por qualquer órgão ou entidade da Administração que não tenha participado do certame licitatório, mediante prévia consulta ao órgão gerenciador, desde que devidamente comprovada a vantagem.
													<br><br>
													Art. 10 - Os órgãos da Administração Pública Municipal que não efetuarem o levantamento do quantitativo anual estimado, nem indicarem a(s) dotação(ões) orçamentária(s) para o Processo de Registro de Preços, poderão ser incluídos, desde que o acréscimo não ultrapasse o percentual de vinte e cinco por cento (25%) previstos no artigo 65 § 1º da Lei Federal nº 8.666/93 e alterações posteriores.
													<br><br>
													Parágrafo Único - A Solicitação para a inclusão deve ser feita através de Ofício, que será submetido à apreciação do Secretário de Finanças.
													<br><br>
													Art. 11 - O prazo de validade para o Registro de Preços será de até 12 (doze) meses.
													<br><br>
													§ 1º - No caso de serviços contínuos, aplica-se a regra do art. 57, inciso II da Lei 8.666/93, admitida a prorrogação da vigência da Ata, nos termos do art. 57, § 4º, da Lei nº 8.666, de 1993, quando a proposta continuar se mostrando mais vantajosa, satisfeitos os demais requisitos desta norma.
													<br><br>
													§ 2º - No caso de fornecimento de bens, havendo concordância do fornecedor quanto à manutenção dos preços da Ata e havendo pesquisa de mercado que ateste a adequação do preço, poderá haver prorrogação da vigência da Ata do registro de preços até a conclusão de processo licitatório, respeitados os limites de aditamento estabelecidos no artigo 65, §1º, da Lei 8.666/93.
													<br><br>
													Art. 12 - Observados os critérios e condições estabelecidos na Licitação, a Administração poderá registrar os preços dos fornecedores remanescentes da Licitação, atendida a ordem de classificação.
													§ 1º - Excepcionalmente, a critério da Diretoria Geral de Compras de Bens e Serviços, quando a quantidade do primeiro colocado não for suficiente para as demandas estimadas, desde que se trate de objetos de qualidade ou desempenho superior, devidamente justificada e comprovada a vantagem, e as ofertas sejam em valor inferior ao máximo admitido, poderão ser registrados outros preços.
													<br><br>
													§ 2º - No caso deste artigo, sendo os preços dos licitantes remanescentes registrados em Ata, ficam os mesmos obrigados a fornecer os materiais quando solicitados pela Administração, através da ordem de fornecimento e empenho ou contrato, obedecendo às condições estabelecidas na Licitação.
													<br><br>
													Art. 13 - A existência de preço registrado não obriga a Administração Municipal a firmar as contratações ou aquisições que dele poderão advir, ficando-lhe facultada a utilização de outros meios, respeitada a legislação relativa às licitações, sendo assegurado ao beneficiário do Registro de Preços preferência em igualdade de condições.
													<br><br>
													Art. 14 - O edital de Concorrência para Registro de Preços contemplará, pelo menos:

													I - a especificação ou descrição do objeto, explicitando o conjunto de elementos necessários e suficientes, com nível de precisão adequado, para a caracterização do bem ou serviço, inclusive definindo as respectivas unidades de medida usualmente adotadas;
													<br>
													II - a estimativa de quantidades a serem adquiridas no prazo de validade do registro;
													<br>
													III - o preço unitário máximo que a Administração se dispõe a pagar, por contratação, considerando o local de entrega ou de execução dos serviços e as estimativas de quantidades a serem adquiridas;
													<br>
													IV - a quantidade mínima de unidades a ser cotada, por item, no caso de bens, considerando o disposto no artigo 20 deste decreto;
													<br>
													V - as condições quanto aos locais, prazos de entrega, forma de pagamento e, complementarmente, nos casos de serviços, quando cabíveis, a freqüência, periodicidade, características do pessoal, materiais e equipamentos a serem fornecidos e utilizados, procedimentos a serem seguidos, cuidados, deveres, disciplina e controles a serem adotados;
													<br>
													VI - o prazo de validade do registro de preço;
													<br>
													VII - os órgãos e entidades participantes do respectivo registro de preço;
													<br>
													VIII - os modelos de planilhas de custo, quando cabíveis, e as respectivas minutas de contratos, no caso de prestação de serviços; e,
													<br>
													IX - as penalidades a serem aplicadas por descumprimento das condições estabelecidas.
													<br><br>
													Parágrafo Único - O edital poderá admitir, como critério de adjudicação, a oferta de desconto sobre tabela de preços praticados no mercado, nos casos de peças de veículos, medicamentos, passagens aéreas, manutenções e outros similares.
													<br><br>
													Art. 15 - Os preços registrados e atualizados não poderão ser superiores aos preços praticados no mercado.
													<br><br>
													§ - 1º Os preços registrados, quando sujeitos ao controle oficial do Governo Federal de preços mínimos, poderão ser reajustados nos termos e prazos fixados pelo Órgão Federal Controlador, conforme o caso.
													<br><br>
													§ 2º - O disposto no parágrafo anterior aplica-se igualmente aos casos de incidência de novos impostos ou taxas, alterações das alíquotas já existentes e outros motivos imprevisíveis ditados pelo mercado.
													<br><br>
													Art. 16 - O preço registrado poderá ser cancelado nos seguintes casos:
													<br>
													I - Pela Administração quando:
													<br>
													a) o fornecedor não cumprir as exigências do instrumento convocatório que deu origem ao Registro de Preços;
													<br>
													b) em qualquer das hipóteses de inexecução total ou parcial do fornecimento decorrente do Registro de Preços;
													<br>
													c) os preços registrados se apresentarem superiores aos praticados pelo mercado e o fornecedor não aceitar reduzir o seu preço registrado;
													<br>
													d) por razões de interesse público devidamente fundamentadas;
													<br>
													e) o fornecedor não retirar a respectiva nota de empenho dentro dos prazos estabelecidos pela Administração, sem justificativa aceitável.
													<br>
													II - a pedido do fornecedor quando, mediante solicitação formal, comprovar estar impossibilitado definitivamente de cumprir as exigências do instrumento convocatório que deu origem ao Registro de Preços por causa de fato superveniente que venha comprometer a perfeita execução contratual, decorrentes de caso fortuito ou de força maior devidamente comprovado.
													<br><br>
													Art. 17 - A comunicação do cancelamento do preço registrado, nos casos previstos no inciso I deste artigo, será feita mediante correspondência ao fornecedor, que fará parte integrante dos autos que deram origem ao Registro de Preços, com publicação no Diário Oficial do Município.
													<br><br>
													Parágrafo único. No caso de não localização do fornecedor, a comunicação será feita mediante publicação no Diário Oficial do Município por 01 (uma) vez, considerando-se cancelado o preço registrado a partir do prazo estipulado na publicação, facultada à Administração a aplicação das penalidades previstas na Lei de Licitações e Contratos.
													<br><br>
													Art. 18 - A solicitação do fornecedor para cancelamento dos preços registrados deverá ser formulada com antecedência mínima de 30 (trinta) dias do término do prazo de validade do Registro de Preços, facultada à Administração a aplicação das penalidades previstas na Lei de Licitações e Contratos, caso não aceitas as razões do pedido.
													<br><br>
													Art. 19 - Para as ordens de fornecimento (empenhos) já emitidas fica o fornecedor obrigado a efetuar a entrega dos materiais ou executar os serviços, pelo valor empenhado.
													<br><br>
													Parágrafo Único - No caso do não cumprimento da obrigação pelo fornecedor, serão aplicadas as penalidades previstas na Lei de Licitações e Contratos.
													<br><br>
													Art. 20 - Nas hipóteses previstas no inciso I, letras a, b e e do Art. 17, fica o fornecedor sujeito às penalidades previstas na Lei de Licitações e Contratos.
													<br><br>
													Art. 21 - Homologado o resultado da licitação, a Diretoria Geral de Compras de Bens e Serviços, respeitada a ordem de classificação e a quantidade de fornecedores a serem registrados, convocará os interessados para assinatura da Ata de Registro de Preços que, após cumprimento dos requisitos de publicidade, terá efeito de compromisso de fornecimento nas condições estabelecidas.
													<br><br>
													Art. 22 - A qualquer tempo, o preço registrado poderá ser revisto em decorrência de eventual redução ou acréscimo em relação aos praticados no mercado, cabendo à Diretoria Geral de Compras de Bens e Serviços convocar os fornecedores registrados para negociar o novo valor no caso de redução e ao fornecedor solicitar e comprovar o desequilíbrio econômico financeiro no preço registrado.
													<br><br>
													Art. 23 - Quando o preço inicialmente registrado, por motivo superveniente, tornar-se superior ao preço praticado no mercado a Diretoria Geral de Compras de Bens e Serviços deverá convocar o fornecedor visando à negociação para redução de preços e sua adequação ao praticado pelo mercado.
													<br><br>
													Parágrafo Único - Frustrada a negociação, o fornecedor será liberado do compromisso assumido e a Diretoria Geral de Compras e Serviços poderá convocar os demais fornecedores visando igual oportunidade de negociação.
													<br><br>
													Art. 24 - Quando o preço de mercado tornar-se superior aos preços registrados e o fornecedor, mediante requerimento devidamente comprovado, não puder cumprir o compromisso, A Diretoria Geral de Compras de Bens e Serviços poderá convocar o fornecedor visando à negociação de preços e revisão do equilíbrio econômico-financeiro.
													<br><br>
													Parágrafo Único - Frustrada a negociação, o fornecedor será liberado do compromisso assumido sem aplicação da penalidade, se confirmada a veracidade dos motivos e comprovantes apresentados, e desde que a comunicação ocorra antes do pedido de fornecimento e a Diretoria Geral de Compras de Bens e Serviços poderá convocar os demais fornecedores visando igual oportunidade de negociação.
													<br><br>
													Art. 25 - Não havendo êxito nas negociações, a Diretoria Geral de Compras de Bens e Serviços deverá proceder à revogação da Ata de Registro de Preços, adotando as medidas cabíveis para obtenção da contratação mais vantajosa.
													<br><br>
													Art. 26 - As modificações dos preços decorrentes de revisão de equilíbrio econômico-financeiro considerados procedentes ou de redução nos preços registrados, serão registradas na Ata de Registro de Preços e publicadas no Diário Oficial do Município.
													<br><br>
													Art. 27 - A contratação com os fornecedores registrados, após a indicação pelo órgão gerenciador do registro de preços, será formalizada pelo órgão interessado, por intermédio de instrumento contratual, emissão de nota de empenho de despesa, autorização de compra ou outro instrumento similar, conforme o disposto no art. 62 da Lei nº 8.666, de 1993.
													<br><br>
													Art. 28 - A Diretoria Geral de Compras de Bens e Serviços, quando realizar a concorrência de Registro de Preços, fará publicar os preços registrados, trimestralmente, no Diário Oficial do Município, para orientação dos órgãos da Administração Municipal e fornecedores.
													<br><br>
													Art. 29 - A aquisição com os fornecedores que possuem os menores preços registrados será formalizada pela Administração Pública Municipal através da emissão da nota de empenho ou formalização de contrato, conforme o caso e o estabelecido no edital da Licitação.
													<br><br>
													Parágrafo Único - Os órgãos da Administração indireta participantes do registro de preços deverão, quando da necessidade de contratação, recorrerem à Diretoria Geral de Compras de Bens e Serviços, para que esta proceda a indicação do fornecedor e respectivos preços a serem praticados.
													<br><br>
													Art. 30 - A Administração poderá subdividir a quantidade total do item em lotes, sempre que comprovada técnica e economicamente viável, de forma a possibilitar maior competitividade, observado, neste caso, dentre outros, a quantidade mínima, o prazo e o local de entrega.
													<br><br>
													Parágrafo Único - No caso deste artigo, que deverá ser previsto em Edital, poderão ser registrados tantos preços quantos necessários para que, em função da proposta de fornecimento de cada fornecedor, seja atingida a quantidade total estimada para o item ou lote.
													<br><br>
													Art. 31 - A aplicação das penalidades aos fornecedores que não cumprirem as condições estabelecidas neste decreto e na Licitação caberá ao Secretário de Finanças, após a tramitação do regular processo administrativo.
													<br><br>
													Art. 32 - Qualquer cidadão ou entidade legalmente constituída é parte legitima para, a qualquer momento, impugnar preço registrado quando este vier a apresentar incompatibilidade com o preço vigente no mercado.
													<br><br>
													Parágrafo Único - A impugnação do preço registrado deverá ser acompanhada da sua respectiva fundamentação, e instruída com os elementos probatórios existentes para a demonstração da veracidade do alegado.
													<br><br>
													Art. 33 - A impugnação apresentada na forma do artigo antecedente será prontamente encaminhada ao Secretário de Finanças que, em prazo não superior a 02 (dois) dias úteis, contados da data em que receber a petição, determinará:
													I - a autuação da impugnação e a instauração do procedimento de apuração de preços;
													II - a realização de nova pesquisa de mercado, se necessário;
													<br><br>
													§ 1° - Cumpridas as providências previstas no caput deste artigo, os autos serão encaminhados à Diretoria Geral de Compras de Bens e Serviços da Secretaria de Finanças para a formulação de manifestação opinativa, que deverá ser firmada em prazo não superior a 05 (cinco) dias úteis contados da data do recebimento dos autos.
													<br><br>
													§ 2° - Encaminhados os autos ao Secretário de Finanças este proferirá despacho declarando, conforme o caso, a adequação ou a inadequação do preço registrado.
													<br><br>
													Art. 34 - Se o despacho a que se refere o Art. 22, § 2º, decidir pela inadequação do preço registrado, o Diretor Geral de Compras de Bens e Serviços intimará o fornecedor para que este, no prazo de 72 (setenta e duas) horas, manifeste por escrito sua concordância ou não com a redução do preço registrado, nos termos propostos pela Administração.
													<br><br>
													§ 1° - Manifestando o fornecedor sua concordância com a redução, a Administração providenciará o aditamento da Ata de registro de preços e do contrato de compromisso de fornecimento, que serão publicados na imprensa oficial.
													<br><br>
													§ 2° - Manifestando o fornecedor sua discordância com a redução, o Secretário de Finanças instaurará procedimento com o objetivo de rescindir o compromisso de fornecimento e cancelar o preço registrado.
													<br><br>
													§ 3° - A ausência de resposta escrita do fornecedor no prazo previsto, será considerada como aceitação incondicional da redução do preço registrado, nos termos estabelecidos pelo despacho referido no caput do presente artigo.
													<br><br>
													Art. 35 - No prazo referido no caput do artigo antecedente poderá o fornecedor, sem efeito suspensivo, apresentar recurso do despacho que declara a inadequação do preço registrado ao Secretário de Finanças.
													<br><br>
													§ 1° - O recurso de que trata o presente artigo apenas será admitido se interposto no prazo referido, e for acompanhado da prova da manifestação escrita encaminhada pelo fornecedor ao Secretário de Finanças, acerca da sua não concordância com a redução do preço registrado.
													<br><br>
													§ 2° - O Secretário de Finanças poderá negar em caráter definitivo, provimento ao recurso interposto, comunicando por escrito a sua decisão ao fornecedor.
													<br><br>
													Art. 36 - A Secretaria de Finanças da Prefeitura poderá editar portaria regulamentando o disposto neste decreto.
													<br><br>
													Art. 37 - Este Decreto entra em vigor na data de sua publicação.
													<br><br>
													Recife, 08 de março de 2002.
												</p>
											</td>
										</tr>
										<tr>
											<td class="textonegrito">
												<p align="center">
													João Paulo Lima e Silva
													<br>
													Prefeito
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

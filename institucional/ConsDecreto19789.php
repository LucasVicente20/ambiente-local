<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: ConsDecreto19789.php
# Autor:    Rossana Lira
# Data:     05/09/03
# Objetivo: Programa de Consulta do Decreto 19789
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
	document.Decreto19789.Botao.value=valor;
	document.Decreto19789.submit();
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="ConsDecreto19300.php" method="post" name="Decreto19789">
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
      <table border="0" cellspacing="0" cellpadding="3" bgcolor="#ffffff" summary="">
        <tr>
	      	<td class="textonormal">
	        	<table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal">
	          	<tr>
	            	<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
		    					DECRETO N.º 19.789/2003
		          	</td>
		        	</tr>
		        	<tr>
							  <td>
							  	<table border="0" summary="">
										<tr>
											<td class="textonegrito">
												<p align="right">
													E M E N T A: Regulamenta licitações na modalidade pregão no âmbito da Administração Pública Municipal.
													<br><br>
												</p>
			     						</td>
			     					</tr>
			     					<tr>
			     						<td>
												<p align="justify" class="textonormal">
													O PREFEITO DO RECIFE, no uso das atribuições que lhe confere o inciso IV do artigo 54 da Lei Orgânica Municipal, de 04 de abril de 1990, <br><br>
													CONSIDERANDO a necessidade de dotar de maior racionalização e agilidade os processos licitatórios no âmbito da Administração Pública Municipal; <br><br>
													CONSIDERANDO a necessidade de adotar medidas que contribuam para a redução de despesas na Administração Pública Municipal; e  <br><br>
													CONSIDERANDO o que dispõe a Lei Federal n.º 10.520, de 17 de julho de 2002, <br><br>
													D E C R E T A: <br><br>
												</p>
											</td>
										</tr>
										<tr>
			     						<td>
												<p align="justify" class="textonormal">
													Art. 1º - As licitações na modalidade pregão serão processadas, no âmbito da Administração Pública Municipal Direta e Indireta, na forma prevista pela Lei Federal n.º 10.520/2002, observados os procedimentos previstos neste Decreto.
													<br><br>
													Art. 2º - Os órgãos e entidades da Administração Pública Municipal poderão realizar licitações na modalidade pregão para aquisição dos bens e contratação dos serviços comuns relacionados em portaria do Secretário de Finanças.
													<br><br>
													§1° - Consideram-se bens e serviços comuns, para fins e efeitos deste Decreto, aqueles cujos padrões de desempenho e qualidade possam ser objetivamente definidos pelo edital, por meio de especificações usuais no mercado.
													<br><br>
													§2° - É facultada a realização do pregão por meio eletrônico, nos termos de regulamentação específica.
													<br><br>
													Art. 3º - Os pregões serão processados por comissões permanentes ou especiais de licitação, cabendo a função de pregoeiro a servidor ou empregado público municipal membro da comissão de licitação.
													<br><br>
													§1° - O pregoeiro, responsável por todo o procedimento licitatório, será auxiliado, durante o certame, por uma equipe de apoio integrada, em sua maioria, por servidores ocupantes de cargo efetivo ou emprego público municipal, preferencialmente pertencentes ao quadro permanente do órgão ou entidade promotora do certame, devidamente designados pela autoridade competente para o cumprimento de tal função.
													<br><br>
													§2° - Somente poderá atuar como pregoeiro o servidor que tenha realizado capacitação específica para a atribuição, com emissão de certificado comprobatório.
													<br><br>
													§3° - Os pregões para a aquisição de bens que exigem conhecimentos técnicos específicos só poderão ser realizados com a participação de, no mínimo, um servidor com capacitação profissional para emitir parecer técnico, a fim de subsidiar o julgamento dos documentos de habilitação e de propostas.
													<br><br>
													Art. 4º - O pregoeiro terá as seguintes atribuições:
													<br>
													I - o credenciamento dos interessados, na sessão pública;
													<br>
													II - o recebimento dos envelopes das propostas de preços e da documentação de habilitação;
													<br>
													III - a abertura dos envelopes das propostas de preços, o seu exame, a classificação das propostas que atenderem às exigências do edital, e a desclassificação das propostas que estiverem em desacordo com o edital;
													<br>
													IV - a condução dos procedimentos relativos aos lances e à escolha da proposta ou do lance de menor preço;
													<br>
													V - a abertura do envelope e a análise da documentação do licitante que apresentar a proposta de menor preço;
													<br>
													VI - a adjudicação da proposta vencedora;
													<br>
													VII - a elaboração de ata contendo o registro de todas as ocorrências da sessão do pregão;
													<br>
													VIII - a condução dos trabalhos da equipe de apoio;
													<br>
													IX - o recebimento, o exame e a decisão sobre os recursos;
													<br>
													X - a instrução do processo com a juntada dos documentos essenciais; e
													<br>
													XI - o encaminhamento do processo devidamente instruído, após a adjudicação, à autoridade superior, para fins de homologação e contratação.
													<br><br>
													Art. 5º - A fase interna do pregão terá início com a abertura do processo contendo a requisição do bem ou do serviço, o Termo de Referência ou Projeto Básico, a estimativa do valor baseada em pesquisa de preço, a previsão do recurso orçamentário, a autorização da autoridade competente e demais atos e procedimentos necessários para a formalização do processo licitatório, conforme exige a legislação municipal em vigor.
													<br><br>
													Art. 6º - A fase externa terá início com a convocação dos interessados, mediante a publicação do aviso de edital no Diário Oficial do Município e na página da Prefeitura do Recife na Internet, qualquer que seja o valor estimado da despesa.
													<br><br>
													§1º - No caso de valores estimados superiores ao limite estabelecido na alínea a do inciso I do art. 23 da Lei nº 8.666/93, a convocação de que trata o caput deste artigo será efetuada, também, mediante publicação do aviso de edital em jornal de grande circulação.
													<br><br>
													§2º - O prazo fixado para apresentação das propostas, contado a partir da publicação do aviso, não será inferior a 08 (oito) dias úteis.
													<br><br>
													Art. 7º - Na sessão pública de pregão, serão observados os seguintes procedimentos:
													<br>
													I - identificação dos licitantes, ou de seus representantes legais dotados de poderes específicos para formulação de lances verbais e para a prática de todos os demais atos inerentes ao certame;
													<br>
													II - recebimento dos envelopes, contendo as propostas e a documentação de habilitação;
													<br>
													III - abertura dos envelopes contendo as propostas e análise desta documentação, promovendo-se a desclassificação daquelas que não atenderem às exigências do edital e a classificação provisória das demais, em ordem crescente de preços;
													<br>
													IV - abertura de oportunidade para lances verbais e sucessivos dos licitantes, ou de seus representantes legais, cuja proposta tenha sido classificada em primeiro lugar, e daqueles cujas propostas apresentem valor até 10% do menor valor ofertado;
													<br>
													V - não havendo pelo menos 03 (três) ofertas nas condições definidas no inciso anterior, poderão os autores das melhores propostas, até o máximo de 03 (três), oferecer novos lances verbais e sucessivos, quaisquer que sejam os preços oferecidos.
													<br>
													VI - abertura do envelope contendo os documentos de habilitação, apresentado pelo licitante cuja proposta tenha sido classificada em primeiro lugar;
													<br>
													VII - deliberação sobre a habilitação do licitante classificado em primeiro lugar ou sobre sua inabilitação, prosseguindo-se, no segundo caso, com a abertura do envelope contendo os documentos de habilitação dos licitantes sucessivos na ordem de classificação, até a apuração de um que atenda às exigências do edital;
													<br>
													VIII - comunicação do resultado do julgamento, declarando o licitante vencedor, após o que os demais licitantes poderão manifestar imediata e motivadamente a intenção de recorrer, quando lhes será concedido o prazo de três dias para apresentação do recurso, ficando aqueles que não recorreram desde logo intimados para apresentar contra-razões em igual número de dias, que começarão a correr do término do prazo do recorrente, sendo-lhes assegurada vista imediata dos autos;
													<br>
													IX - o acolhimento de recurso importará a invalidação apenas dos atos insuscetíveis de aproveitamento;
													<br>
													X - a falta de manifestação imediata e motivada do licitante importará a decadência do direito de recurso e a adjudicação do objeto da licitação pelo pregoeiro ao vencedor;
													<br>
													XI - decididos os recursos, a autoridade competente fará a adjudicação do objeto da licitação ao licitante vencedor;
													<br>
													XII - homologada a licitação pela autoridade competente, o adjudicatário será convocado para assinar o contrato no prazo definido em edital;
													<br>
													XIII - se o licitante vencedor, convocado dentro do prazo de validade da sua proposta, não celebrar o contrato, aplicar-se-á o disposto no inciso XVI do artigo 4° da Lei 10.520/2002.
													<br><br>
													Art. 8º - Até dois dias úteis antes da data fixada para recebimento das propostas, qualquer pessoa poderá solicitar esclarecimentos, providências ou impugnar o ato convocatório do pregão.
													<br><br>
													Art. 9º - Este Decreto entra em vigor na data de sua publicação.
													<br><br>
													Recife, 21 de março de 2003.
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

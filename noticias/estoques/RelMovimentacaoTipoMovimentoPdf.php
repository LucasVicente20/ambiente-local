<?php
# -------------------------------------------------------------------------------------------------------------------
# Portal da DGCO
# Programa: RelMovimentacaoTipoMovimentoPdf.php
# Objetivo: Programa para impressão do relatório de movimentações de material
#           agrupadas por tipo de movimentação
# Autor:    Álvaro Faria
# Data:     03/11/2005
# -------------------------------------------------------------------------------------------------------------------
# Alterado: Álvaro Faria
# Data:     08/03/2006
# Objetivo: Programa de Impressão da Movimentação de Material
# -------------------------------------------------------------------------------------------------------------------
# Alterado: Álvaro Faria
# Data:     17/07/2006 - Adição de valor ao relatório
# -------------------------------------------------------------------------------------------------------------------
# Alterado: Álvaro Faria
# Data:     04/08/2006 - Totalizador por tipo de movimentação
# -------------------------------------------------------------------------------------------------------------------
# Alterado: Álvaro Faria
# Data:     23/08/2006
# -------------------------------------------------------------------------------------------------------------------
# Alterado: Carlos Abreu
# Data:     07/05/2007 - Ajuste para que as movimentações (0,5) não possam ser exibidas
# -------------------------------------------------------------------------------------------------------------------
# Alterado: Carlos Abreu
# Data:     11/05/2007 - Acrescentado número de empenho
# -------------------------------------------------------------------------------------------------------------------
# Alterado: Carlos Abreu
# Data:     29/05/2007 - Exibir sequencial da requisicao
# -------------------------------------------------------------------------------------------------------------------
# Alterado: Fausto Feitosa
# Data:     12/11/2007 - Exibir asterico nas movimentações pendentes
# -------------------------------------------------------------------------------------------------------------------
# Alterado: Álvaro Faria
# Data:     14/12/2007 - Correção para exibição de asterico nas movimentações pendentes
#                      - Exibição de SubTotais de movimentações pendentes e confirmadas
# -------------------------------------------------------------------------------------------------------------------
# Alterado: Álvaro Faria / Rossana Lira
# Data:     17/12/2007 - Correção para exibir requisições canceladas como confirmadas
# -------------------------------------------------------------------------------------------------------------------
# Alterado: Rodrigo Melo
# Data:     20/12/2007 - Correção para obter as requisições conforme a última data da situação.
# -------------------------------------------------------------------------------------------------------------------
# Alterado: Rodrigo Melo
# Data:     27/06/2008 - Alteração do relatório pois estava verificando se a requisição estava pedente de forma errada.
# -------------------------------------------------------------------------------------------------------------------
# Alterado: Rodrigo Melo
# Data:     01/08/2008 - Alteração do relatório pois estava verificando se a requisição estava pedente de forma errada,
#                        porque quando a requisição for cancelada, esta é considerada confirmada.
# -------------------------------------------------------------------------------------------------------------------
# Alterado: Ariston Cordeiro
# Data:     10/08/2009 - Limitando tamanho máximo do documento para 1000 folhas.
# -------------------------------------------------------------------------------------------------------------------
# Alterado: Lucas Baracho
# Data:     27/12/2018
# Objetivo: Tarefa Redmine 208586
# -------------------------------------------------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_cache_limiter('private_no_expire');
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso ('/estoques/RelMovimentacaoTipoMovimento.php');

# Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD'] == "GET") {
	$Almoxarifado     = $_GET['Almoxarifado'];
	$Movimentacao     = $_GET['Movimentacao'];
	$TipoMovimentacao = $_GET['TipoMovimentacao'];
	$Ordem            = $_GET['Ordem'];
	$DataFim          = $_GET['DataFim'];
	$DataIni          = $_GET['DataIni'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

function imprimeempenho($Almoxarifado,$AnoNota,$NotaFiscal,$db,$pdf){
		# Recupera dados dos empenhos #
		$limite    = 8;
		$limitemax = 2*$limite;
		$sqlemp  = "SELECT ANFEMPANEM, CNFEMPOREM, CNFEMPUNEM, ";
		$sqlemp .= "       CNFEMPSEEM, CNFEMPPAEM ";
		$sqlemp .= "  FROM SFPC.TBNOTAFISCALEMPENHO ";
		$sqlemp .= " WHERE CALMPOCODI = $Almoxarifado ";
		$sqlemp .= "   AND AENTNFANOE = $AnoNota ";
		$sqlemp .= "   AND CENTNFCODI = $NotaFiscal ";
		$resemp  = $db->query($sqlemp);
		if(db::isError($resemp)){
				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlemp");
		}else{
				$c = 0;
				while($LinhaEmp = $resemp->fetchRow()){
						$AnoEmp        = $LinhaEmp[0];
						$OrgaoEmp      = $LinhaEmp[1];
						$UnidadeEmp    = $LinhaEmp[2];
						$SequencialEmp = $LinhaEmp[3];
						$ParcelaEmp    = $LinhaEmp[4];
						$c++;
						if($c <= $limitemax){
								if($c <= $limite){
										if($ParcelaEmp){
												if(!$DescEmpenho1){
														$DescEmpenho1 = "$AnoEmp.$OrgaoEmp.$UnidadeEmp.$SequencialEmp-$ParcelaEmp";
												}else{
														$DescEmpenho1 .= " / $AnoEmp.$OrgaoEmp.$UnidadeEmp.$SequencialEmp-$ParcelaEmp";
												}
										}else{
												if(!$DescEmpenho1){
														$DescEmpenho1 = "$AnoEmp.$OrgaoEmp.$UnidadeEmp.$SequencialEmp";
												}else{
														$DescEmpenho1 .= " / $AnoEmp.$OrgaoEmp.$UnidadeEmp.$SequencialEmp";
												}
										}
								}else{
										if($ParcelaEmp){
												if(!$DescEmpenho2){
														$DescEmpenho2 = "$AnoEmp.$OrgaoEmp.$UnidadeEmp.$SequencialEmp-$ParcelaEmp";
												}else{
														$DescEmpenho2 .= " / $AnoEmp.$OrgaoEmp.$UnidadeEmp.$SequencialEmp-$ParcelaEmp";
												}
										}else{
												if(!$DescEmpenho2){
														$DescEmpenho2 = "$AnoEmp.$OrgaoEmp.$UnidadeEmp.$SequencialEmp";
												}else{
														$DescEmpenho2 .= " / $AnoEmp.$OrgaoEmp.$UnidadeEmp.$SequencialEmp";
												}
										}
								}
						}
				}
		}
		# Escreve no relatório os empenhos encontrados
		if(!$DescEmpenho1){
				$DescEmpenho1 = "NENHUM Nº INFORMADO";
		}
		if($c > $limite){
				if($c > $limitemax){
						$pdf->Cell(28,5,"Nº(S) EMPENHO(S)","LRT",0,"L",0);
						$pdf->Cell(252,5,$DescEmpenho1,1,1,"L",0);
						$pdf->Cell(28,5," ","LRB",0,"L",0);
						$pdf->Cell(252,5,$DescEmpenho2."...",1,1,"L",0);
				}else{
						$pdf->Cell(28,5,"Nº(S) EMPENHO(S)","LRT",0,"L",0);
						$pdf->Cell(252,5,$DescEmpenho1,1,1,"L",0);
						$pdf->Cell(28,5," ","LRB",0,"L",0);
						$pdf->Cell(252,5,$DescEmpenho2,1,1,"L",0);
				}
		}else{
				$pdf->Cell(28,5,"Nº(S) EMPENHO(S)",1,0,"L",0);
				$pdf->Cell(252,5,$DescEmpenho1,1,1,"L",0);
		}
}

# Função exibe o Cabeçalho e o Rodapé #
CabecalhoRodapePaisagem();

# Informa o Título do Relatório #
if($Ordem == "C"){
		$TituloRelatorio = "Relatório de Movimentação por Tipo (Ordem: Centro de Custo)";
}else{
		$TituloRelatorio = "Relatório de Movimentação por Tipo (Ordem: Data da Movimentação)";
}

# Cria o objeto PDF, o Default é formato Retrato, A4  e a medida em milímetros #
$pdf = new PDF("L","mm","A4");

# Define um apelido para o número total de páginas #
$pdf->AliasNbPages();

# Define as cores do preenchimentos que serão usados #
$pdf->SetFillColor(220,220,220);

# Adiciona uma página no documento #
$pdf->AddPage();

# Seta as fontes que serão usadas na impressão de strings #
if($Ordem == "D"){
		$pdf->SetFont("Arial","",8);
}else{
		$pdf->SetFont("Arial","",9);
}

# Datas para consulta no banco de dados #
$DataInibd = substr($DataIni,6,4)."-".substr($DataIni,3,2)."-".substr($DataIni,0,2);
$DataFimbd = substr($DataFim,6,4)."-".substr($DataFim,3,2)."-".substr($DataFim,0,2);

# Pega os dados do almoxarifado #
$db   = Conexao();
$sql = "SELECT EALMPODESC FROM SFPC.TBALMOXARIFADOPORTAL WHERE CALMPOCODI = $Almoxarifado ";
$res = $db->query($sql);
if( db::isError($res) ){
		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
}else{
		$Campo            = $res->fetchRow();
		$DescAlmoxarifado = $Campo[0];
}
$pdf->Cell(30,5,"ALMOXARIFADO",1,0,"L",1);
$pdf->Cell(250,5,$DescAlmoxarifado,1,1,"L",0);

$pdf->Cell(30,5,"PERÍODO",1,0,"L",1);
$pdf->Cell(250,5,$DataIni." À ".$DataFim,1,1,"L",0);
$pdf->Cell(190,5,"OBS: O asterisco (*) indica que a movimentação está pendente, isto é, precisa ser finalizada para gerar lançamento custo/contábil.",0,1,"L",0);
$pdf->Cell(190,5,"A movimentação confirmada não indica necessariamente que houve um lançamento custo/contábil, podendo ser uma movimentação interna de estoque finalizada.",0,1,"L",0);
$pdf->ln(5);

# Inicializa variáveis #
$TotalQtdMov     = 0;
$TotalValMov     = 0;
$TotalEntrada    = 0;
$TotalEntradaVal = 0;
$TotalSaida      = 0;
$TotalSaidaVal   = 0;

$sqlitens .= " SELECT MOV.CALMPOCODI, MOV.AMOVMAANOM, MOV.CMOVMACODI, ";
$sqlitens .= "        CASE WHEN MOV.CREQMASEQU IS NOT NULL AND MOV.CTIPMVCODI NOT IN (2,21,22) THEN TO_DATE(TO_CHAR(SIT.TSITRESITU,'YYYY-MM-DD'),'YYYY-MM-DD') ELSE MOV.DMOVMAMOVI END AS DATAMOV, ";
$sqlitens .= "        MOV.AMOVMAQTDM,  ";
$sqlitens .= "        MOV.AMOVMAQTDM * (CASE WHEN MOV.CTIPMVCODI IN (3,7,8) THEN (MOV.VMOVMAVALO) ELSE (MOV.VMOVMAUMED) END) AS VALOR,  ";
$sqlitens .= "        MOV.CMOVMACODT, MOV.CTIPMVCODI, ";
$sqlitens .= "        MAT.CMATEPSEQU, MAT.EMATEPDESC, ";
$sqlitens .= "        UND.EUNIDMSIGL, ";
$sqlitens .= "        TIP.FTIPMVTIPO, TIP.ETIPMVDESC, ";
$sqlitens .= "        NFI.AENTNFNOTA, NFI.AENTNFSERI, NFI.FENTNFCANC, ";
$sqlitens .= "        REQ.AREQMAANOR, REQ.CREQMACODI, ";
$sqlitens .= "        CEN.CCENPOSEQU, CEN.ECENPODESC, CEN.ECENPODETA, ";
$sqlitens .= "        ALM.EALMPODESC, NFI.AENTNFANOE, NFI.CENTNFCODI, REQ.CREQMASEQU, ";


#$sqlitens .= "        MOV.FMOVMACORR, ";

//TESTE

//TERMINAR - Caso, seja a movimentação 12,13,15 ou 30 e não ter havido a movimentação correspondente no período, essa movimentação é pedente para o período.
$sqlitens .= " CASE WHEN MOV.CTIPMVCODI IN (12,13,15,30) AND ";
$sqlitens .= "  ( ";
$sqlitens .= "                      ( NOT (SELECT DATATRANSACAO.DMOVMAMOVI ";
$sqlitens .= "                             FROM SFPC.TBMOVIMENTACAOMATERIAL DATATRANSACAO ";
$sqlitens .= "                             WHERE DATATRANSACAO.CALMPOCOD1 = MOV.CALMPOCODI ";
$sqlitens .= "                             AND DATATRANSACAO.AMOVMAANO1 = MOV.AMOVMAANOM ";
$sqlitens .= "                             AND DATATRANSACAO.CMOVMACOD1 = MOV.CMOVMACODI ";
$sqlitens .= "                             AND DATATRANSACAO.CTIPMVCODI IN (6,9,11,29) ";
$sqlitens .= "                             AND CASE WHEN MOV.CTIPMVCODI = 11 THEN ";
$sqlitens .= "                                        DATATRANSACAO.CMATEPSEQ1 = MOV.CMATEPSEQU ";
$sqlitens .= "                                      ELSE ";
$sqlitens .= "                                        DATATRANSACAO.CMATEPSEQU = MOV.CMATEPSEQU ";
$sqlitens .= "                              END) ";
$sqlitens .= "                              BETWEEN '".DataInvertida($DataIni)." 00:00:00' AND '".DataInvertida($DataFim)." 23:59:59' ) ";
$sqlitens .= " OR ";
$sqlitens .= "   (MOV.FMOVMACORR IS NULL OR MOV.FMOVMACORR = 'N') ";
$sqlitens .= "  ) ";
$sqlitens .= " THEN 'N' ";
$sqlitens .= " ELSE 'S' END AS CORRESPONDENTE, ";

//FIM TESTE



$sqlitens .= "        MOV.CALMPOCOD1, ";
#$sqlitens .= "        (CASE WHEN MOV.CTIPMVCODI = 4 THEN  ";
$sqlitens .= "        (CASE WHEN MOV.CTIPMVCODI IN (4,19,20) THEN  ";
#$sqlitens .= "              CASE WHEN se_requisicao_baixada(MOV.CREQMASEQU) = 0 THEN 'S' ";
$sqlitens .= "              CASE WHEN SIT.CTIPSRCODI IN (3,4) THEN 'S' ";
$sqlitens .= "              ELSE 'N' END";

// $sqlitens .= "             THEN 'S' ";
// $sqlitens .= "             ELSE 'N' END ";
$sqlitens .= "         END) AS PENDENTE, ";
$sqlitens .= "			MOV.CALMPOCOD1 ";
//FIM TERMINAR
#$sqlitens .= "          ELSE 'N' END) AS PENDENTE";




$sqlitens .= "   FROM SFPC.TBALMOXARIFADOPORTAL ALM, ";
$sqlitens .= "        SFPC.TBMATERIALPORTAL MAT, ";
$sqlitens .= "        SFPC.TBUNIDADEDEMEDIDA UND, ";
$sqlitens .= "        SFPC.TBTIPOMOVIMENTACAO TIP, ";
$sqlitens .= "        SFPC.TBMOVIMENTACAOMATERIAL MOV ";
$sqlitens .= "   LEFT OUTER JOIN SFPC.TBENTRADANOTAFISCAL NFI ";
$sqlitens .= "     ON MOV.CALMPOCODI = NFI.CALMPOCODI ";
$sqlitens .= "    AND MOV.AENTNFANOE = NFI.AENTNFANOE ";
$sqlitens .= "    AND MOV.CENTNFCODI = NFI.CENTNFCODI ";
$sqlitens .= "   LEFT OUTER JOIN SFPC.TBREQUISICAOMATERIAL REQ ";
$sqlitens .= "     ON MOV.CREQMASEQU = REQ.CREQMASEQU ";
$sqlitens .= "   LEFT OUTER JOIN SFPC.TBCENTROCUSTOPORTAL CEN ";
$sqlitens .= "     ON REQ.CCENPOSEQU = CEN.CCENPOSEQU ";
# Busca pela situação da requisição, se esta movimentação tiver haver com requisição #
$sqlitens .= "   LEFT OUTER JOIN SFPC.TBSITUACAOREQUISICAO SIT ";
$sqlitens .= "     ON MOV.CREQMASEQU = SIT.CREQMASEQU ";

$sqlitens .= "    AND SIT.TSITREULAT IN ";
$sqlitens .= "                   (SELECT MAX(TSITREULAT) FROM SFPC.TBSITUACAOREQUISICAO ";
$sqlitens .= "                     WHERE CREQMASEQU = SIT.CREQMASEQU ";
$sqlitens .= "                     AND TSITREULAT ";
$sqlitens .= "                     BETWEEN '".DataInvertida($DataIni)." 00:00:00' AND '".DataInvertida($DataFim)." 23:59:59' ";
$sqlitens .= "                   ) ";



$sqlitens .= "  WHERE MOV.CALMPOCODI =  $Almoxarifado  ";
if($TipoMovimentacao) $sqlitens .= " AND   TIP.FTIPMVTIPO = '$TipoMovimentacao' ";
if($Movimentacao)     $sqlitens .= " AND   TIP.CTIPMVCODI = '$Movimentacao' ";
$sqlitens .= "  AND   TIP.CTIPMVCODI NOT IN (0,5) ";
$sqlitens .= "  AND ";

//teste
$sqlitens .= "  CASE WHEN MOV.CREQMASEQU IS NOT NULL AND MOV.CTIPMVCODI IN (4,19,20) THEN ";
#$sqlitens .= "        TO_DATE(TO_CHAR(SIT.TSITRESITU,'YYYY-MM-DD'),'YYYY-MM-DD') BETWEEN '".DataInvertida($DataIni)." 00:00:00' AND '".DataInvertida($DataFim)." 23:59:59' ";
#$sqlitens .= "        AND REQ.CCENPOSEQU = CEN.CCENPOSEQU ";

$sqlitens .= "     CASE ";
$sqlitens .= "        WHEN SIT.CTIPSRCODI IN (3,4) ";
$sqlitens .= "          THEN (DMOVMAMOVI BETWEEN '".DataInvertida($DataIni)." 00:00:00' AND '".DataInvertida($DataFim)." 23:59:59')  ";
$sqlitens .= "                AND MOV.CREQMASEQU NOT IN ( ";
$sqlitens .= "                SELECT CREQMASEQU FROM SFPC.TBSITUACAOREQUISICAO WHERE CREQMASEQU = MOV.CREQMASEQU AND CTIPSRCODI = 5 AND TSITREULAT BETWEEN '".DataInvertida($DataIni)." 00:00:00' AND '".DataInvertida($DataFim)." 23:59:59') ";
$sqlitens .= "        ELSE MOV.CREQMASEQU IN ( ";
$sqlitens .= "           SELECT CREQMASEQU FROM SFPC.TBSITUACAOREQUISICAO WHERE CREQMASEQU = MOV.CREQMASEQU AND CTIPSRCODI = 5 AND TSITREULAT BETWEEN '".DataInvertida($DataIni)." 00:00:00' AND '".DataInvertida($DataFim)." 23:59:59') ";
$sqlitens .= "      END  ";
$sqlitens .= "  ELSE ";
//teste


// $sqlitens .= "         CASE WHEN MOV.CTIPMVCODI IN (12,13,15,30) THEN ";
// $sqlitens .= "              (SELECT DATATRANSACAO.DMOVMAMOVI ";
// $sqlitens .= "                 FROM SFPC.TBMOVIMENTACAOMATERIAL DATATRANSACAO ";
// $sqlitens .= "                WHERE DATATRANSACAO.CALMPOCOD1 = MOV.CALMPOCODI ";
// $sqlitens .= "                  AND DATATRANSACAO.AMOVMAANO1 = MOV.AMOVMAANOM ";
// $sqlitens .= "                  AND DATATRANSACAO.CMOVMACOD1 = MOV.CMOVMACODI ";
// $sqlitens .= "                  AND DATATRANSACAO.CTIPMVCODI IN (6,9,11,29) ";
// $sqlitens .= "                  AND CASE WHEN MOV.CTIPMVCODI = 11 THEN ";
// $sqlitens .= "                           DATATRANSACAO.CMATEPSEQ1 = MOV.CMATEPSEQU ";
// $sqlitens .= "                      ELSE ";
// $sqlitens .= "                           DATATRANSACAO.CMATEPSEQU = MOV.CMATEPSEQU ";
// $sqlitens .= "                      END";
// $sqlitens .= "              ) BETWEEN '".DataInvertida($DataIni)."' AND '".DataInvertida($DataFim)."' ";
// $sqlitens .= "         ELSE  ";
$sqlitens .= "	           MOV.DMOVMAMOVI BETWEEN '".DataInvertida($DataIni)." 00:00:00' AND '".DataInvertida($DataFim)." 23:59:59' ";
$sqlitens .= "         END ";

// $sqlitens .= "  END ";//TESTE

$sqlitens .= "  AND   MOV.CTIPMVCODI = TIP.CTIPMVCODI ";
$sqlitens .= "  AND   MAT.CMATEPSEQU = MOV.CMATEPSEQU ";
$sqlitens .= "  AND   MAT.CUNIDMCODI = UND.CUNIDMCODI ";
$sqlitens .= "  AND   MOV.CALMPOCODI = ALM.CALMPOCODI ";
$sqlitens .= "  AND   (MOV.FMOVMASITU IS NULL OR MOV.FMOVMASITU = 'A') "; // Apresentar só as movimentações ativas
if($Ordem == "C"){
		$sqlitens .= " ORDER BY TIP.ETIPMVDESC, CEN.ECENPODESC, CEN.ECENPODETA, MOV.DMOVMAMOVI, MOV.CMOVMACODT ";
}else{
		$sqlitens .= " ORDER BY TIP.ETIPMVDESC, MOV.DMOVMAMOVI, MOV.CMOVMACODT";
}

// echo $sqlitens;
// exit;

$resitens = $db->query($sqlitens);

if( db::isError($resitens) ){
		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlitens");
}else{
		$rowsitens = $resitens->numRows();
		if($rowsitens > 0){
				while($rowitens = $resitens->fetchRow()){
						$Almoxarifado          = $rowitens[0];
						$AnoMovimentacao       = $rowitens[1];
						$Sequencial            = $rowitens[2];
						$Data                  = databarra($rowitens[3]);
						$Quantidade            = converte_quant(sprintf("%01.2f",str_replace(",",".",$rowitens[4])));
						$QuantSoma             = $rowitens[4];
						$QuantValor            = converte_valor_estoques(sprintf("%01.4f",str_replace(",",".",$rowitens[5])));
						$QuantXValor           = $rowitens[5];
						$MovNumero             = $rowitens[6];
						$Movimentacao          = $rowitens[7];
						$CodigoReduzido        = $rowitens[8];
						$MaterialDesc          = $rowitens[9];
						$Unidade               = $rowitens[10];
						$TipoMovimentacao      = $rowitens[11];
						$DescricaoMovimentacao = $rowitens[12];
						$NotaNum               = $rowitens[13];
						$NotaSeri              = $rowitens[14];
						$NotaCancelada         = $rowitens[15];
						$ReqAno                = $rowitens[16];
						$ReqNum                = substr("00000".$rowitens[17],-5);
						$Centro                = $rowitens[18];
						$CentroDesc            = substr($rowitens[19],0,39);
						$CentroDeta            = substr($rowitens[20],0,43);
						$AlmoxarifadoDesc      = substr($rowitens[21],0,39);
						$NotaAno               = $rowitens[22];
						$NotaCodigo            = $rowitens[23];
						$ReqSeq                = $rowitens[24];
						$MovCorrespondente     = $rowitens[25];
						$AlmoxarifadoDestino   = $rowitens[26];
						$MovPendente           = $rowitens[27];
						$MovCod1			   = $rowitens[28];

						$Pendencia             = 0;

						# Se a MovPendente for igual a S quer dizer que a movimentação
						# está pendente, para isso ela tem que ter o atributo CTIPMVCODI = 4 na tabela SFPC.TBMOVIMENTACAOMATERIAL
						# e na tabela SFPC.TBSITUACAOREQUISICAO não conter o atributo CTIPSRCODI = 5
						# em nenhuma de suas movimentações, se for S concatena um * ao número da movimentação.
						if($MovPendente == 'S') {
								$MovNumero .= "*";
								$Pendencia  = 1;
						}

						# Se o código da movimentação for as saídas 12, 13, 15, 30 e não houver correspondência, significa que há pendência #
						# As entradas (6, 9, 11, 29) destas movimentações de saída citadas acima não precisam ser checadas, pois sempre geram lançamento, nunca ficando pendentes #
						# Se houver pendência, concatena-se um * ao número da movimentação e seta a flag 1 na variável $Pendencia para ser usada na totalização #
						if( ($Movimentacao == 12 or $Movimentacao == 13 or $Movimentacao == 15 or $Movimentacao == 30) and ($MovCorrespondente != "S" or $MovCorrespondente == null) ){
								$MovNumero .= "*";
								$Pendencia  = 1;
						}

						# Verificar o orgão destino
						if($_GET['Movimentacao'] == 12 && !empty($MovCod1)) {
                            $sqlmov = "SELECT EALMPODESC FROM SFPC.TBALMOXARIFADOPORTAL WHERE CALMPOCODI = $MovCod1 ";
                            $res = $db->query($sqlmov);
                            if( db::isError($res) ){
                                ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
                            }else{
                                $Campo            = $res->fetchRow();
                                $AlmoxarifadoDesc .= " PARA " . $Campo[0];
                            }
						}

						if($NotaCancelada != 'S'){
								if($Movimentacao != $MovimentacaoEscrito){
										if($UmaOuMais){
												# Imprime totais da movimentação anterior #
												if($Ordem == "C"){
														if(!is_null($MovAnterior)){
																if($MovAnteriorTipo=="R"){
																		$pdf->Cell(232,5,"TOTAL DA REQUISIÇÃO", 1, 0, "R", 1);
																}elseif($MovAnteriorTipo=="N"){
																		imprimeempenho($Almoxarifado,$NotaAnoAnterior,$NotaCodigoAnterior,$db,$pdf);
																		$pdf->Cell(232,5,"TOTAL DA NOTA FISCAL", 1, 0, "R", 1);
																		$NotaAnoAnterior = null;
																		$NotaCodigoAnterior = null;
																}
																$pdf->Cell(24,5,converte_quant($MovQtdAnterior), 1, 0, "R", 0);
																$pdf->Cell(24,5,converte_valor_estoques($MovValorAnterior), 1, 1, "R", 0);
																$MovQtdAnterior = 0;
																$MovValorAnterior = 0;
														}
														if($TotalQtdMovPend){
																$TotalQtdMovPend = converte_quant(sprintf("%01.2f",str_replace(",",".",$TotalQtdMovPend)));
																$TotalValMovPend = converte_valor_estoques(sprintf("%01.4f",str_replace(",",".",$TotalValMovPend)));
																$pdf->Cell(232,5,"TOTAL $DescricaoMovAnterior (Pendente)",1,0,"R",1);
																$pdf->Cell(24,5,$TotalQtdMovPend,1,0,"R",0);
																$pdf->Cell(24,5,$TotalValMovPend,1,1,"R",0);
														}
														if($TotalQtdMovConf){
																$TotalQtdMovConf = converte_quant(sprintf("%01.2f",str_replace(",",".",$TotalQtdMovConf)));
																$TotalValMovConf = converte_valor_estoques(sprintf("%01.4f",str_replace(",",".",$TotalValMovConf)));
																$pdf->Cell(232,5,"TOTAL $DescricaoMovAnterior (Confirmada)",1,0,"R",1);
																$pdf->Cell(24,5,$TotalQtdMovConf,1,0,"R",0);
																$pdf->Cell(24,5,$TotalValMovConf,1,1,"R",0);
														}
														$TotalQtdMov     = converte_quant(sprintf("%01.2f",str_replace(",",".",$TotalQtdMov)));
														$TotalValMov     = converte_valor_estoques(sprintf("%01.4f",str_replace(",",".",$TotalValMov)));
														$pdf->Cell(232,5,"TOTAL $DescricaoMovAnterior",1,0,"R",1);
														$pdf->Cell(24,5,$TotalQtdMov,1,0,"R",0);
														$pdf->Cell(24,5,$TotalValMov,1,1,"R",0);
												}else{
														if(!is_null($MovAnterior)){
																if($MovAnteriorTipo=="R"){
																	$pdf->Cell(237,5,"TOTAL DA REQUISIÇÃO", 1, 0, "R", 1);
																}elseif($MovAnteriorTipo=="N"){
																		imprimeempenho($Almoxarifado,$NotaAnoAnterior,$NotaCodigoAnterior,$db,$pdf);
																		$pdf->Cell(237,5,"TOTAL DA NOTA FISCAL", 1, 0, "R", 1);
																		$NotaAnoAnterior = null;
																		$NotaCodigoAnterior = null;
																}
																$pdf->Cell(20,5,converte_quant($MovQtdAnterior), 1, 0, "R", 0);
																$pdf->Cell(23,5,converte_valor_estoques($MovValorAnterior), 1, 1, "R", 0);
																$MovQtdAnterior = 0;
																$MovValorAnterior = 0;
														}
														if($TotalQtdMovPend){
																$TotalQtdMovPend = converte_quant(sprintf("%01.2f",str_replace(",",".",$TotalQtdMovPend)));
																$TotalValMovPend = converte_valor_estoques(sprintf("%01.4f",str_replace(",",".",$TotalValMovPend)));
																$pdf->Cell(237,5,"TOTAL $DescricaoMovAnterior (Pendente)",1,0,"R",1);
																$pdf->Cell(20,5,$TotalQtdMovPend,1,0,"R",0);
																$pdf->Cell(23,5,$TotalValMovPend,1,1,"R",0);
														}
														if($TotalQtdMovConf){
																$TotalQtdMovConf = converte_quant(sprintf("%01.2f",str_replace(",",".",$TotalQtdMovConf)));
																$TotalValMovConf = converte_valor_estoques(sprintf("%01.4f",str_replace(",",".",$TotalValMovConf)));
																$pdf->Cell(237,5,"TOTAL $DescricaoMovAnterior (Confirmada)",1,0,"R",1);
																$pdf->Cell(20,5,$TotalQtdMovConf,1,0,"R",0);
																$pdf->Cell(23,5,$TotalValMovConf,1,1,"R",0);
														}
														$TotalQtdMov     = converte_quant(sprintf("%01.2f",str_replace(",",".",$TotalQtdMov)));
														$TotalValMov     = converte_valor_estoques(sprintf("%01.4f",str_replace(",",".",$TotalValMov)));
														$pdf->Cell(237,5,"TOTAL $DescricaoMovAnterior",1,0,"R",1);
														$pdf->Cell(20,5,$TotalQtdMov,1,0,"R",0);
														$pdf->Cell(23,5,$TotalValMov,1,1,"R",0);
												}
										}
										$UmaOuMais        = 1;
										$TotalQtdMov      = 0;
										$TotalValMov      = 0;
										$TotalQtdMovConf  = 0;
										$TotalQtdMovPend  = 0;
										$TotalValMovConf  = 0;
										$TotalValMovPend  = 0;
										$DescricaoMovAnterior = $DescricaoMovimentacao;
										$pdf->Cell(280,5,"TIPO DE MOVIMENTO: $DescricaoMovimentacao",1,1,"C",1);
										$MovimentacaoEscrito = $Movimentacao;
										$CentroEscrito       = null;
										$AlmoxarifadoEscrito = null;
										$PorDataCabecalho    = 1;
										$MovAnterior         = null;
								}
								$TotalQtdMov = $TotalQtdMov + $QuantSoma;
								$TotalValMov = $TotalValMov + $QuantXValor;

								if($Pendencia == 1){
										$TotalQtdMovPend = $TotalQtdMovPend + $QuantSoma;
										$TotalValMovPend = $TotalValMovPend + $QuantXValor;
										if($TipoMovimentacao == "S") $TotalSaidaPend      = $TotalSaidaPend      + str_replace(",",".",$QuantSoma);
										if($TipoMovimentacao == "E") $TotalEntradaPend    = $TotalEntradaPend    + str_replace(",",".",$QuantSoma);
										if($TipoMovimentacao == "S") $TotalSaidaValPend   = $TotalSaidaValPend   + str_replace(",",".",$QuantXValor);
										if($TipoMovimentacao == "E") $TotalEntradaValPend = $TotalEntradaValPend + str_replace(",",".",$QuantXValor);
								}else{
										$TotalQtdMovConf  = $TotalQtdMovConf + $QuantSoma;
										$TotalValMovConf  = $TotalValMovConf + $QuantXValor;
										if($TipoMovimentacao == "S") $TotalSaidaConf      = $TotalSaidaConf      + str_replace(",",".",$QuantSoma);
										if($TipoMovimentacao == "E") $TotalEntradaConf    = $TotalEntradaConf    + str_replace(",",".",$QuantSoma);
										if($TipoMovimentacao == "S") $TotalSaidaValConf   = $TotalSaidaValConf   + str_replace(",",".",$QuantXValor);
										if($TipoMovimentacao == "E") $TotalEntradaValConf = $TotalEntradaValConf + str_replace(",",".",$QuantXValor);
								}

								if($TipoMovimentacao == "S") $TotalSaida      = $TotalSaida      + str_replace(",",".",$QuantSoma);
								if($TipoMovimentacao == "E") $TotalEntrada    = $TotalEntrada    + str_replace(",",".",$QuantSoma);
								if($TipoMovimentacao == "S") $TotalSaidaVal   = $TotalSaidaVal   + str_replace(",",".",$QuantXValor);
								if($TipoMovimentacao == "E") $TotalEntradaVal = $TotalEntradaVal + str_replace(",",".",$QuantXValor);

								if($Ordem == "C"){
										if( ($Centro != $CentroEscrito) or ( (!$Centro) and ($Almoxarifado != $AlmoxarifadoEscrito) )  ){
												if($CentroDesc){
														$pdf->Cell(280,5,"CENTRO DE CUSTO: $CentroDesc - $CentroDeta",1,1,"C",0);
														$CentroDesc = "";
												}
												$CentroEscrito = $Centro;
												$AlmoxarifadoEscrito = $Almoxarifado;

												$pdf->Cell(18,5,"DATA", 1, 0, "C", 1);
												$pdf->Cell(13,5,"Nº MOV", 1, 0, "C", 1);
												if(($ReqNum) and ($ReqNum != "00000")){
														$pdf->Cell(35,5,"REQ / ANO - SEQ", 1, 0, "C", 1);
												}elseif($NotaNum){
														$pdf->Cell(35,5,"NOTA - SÉRIE", 1, 0, "C", 1);
												}else{
														$pdf->Cell(35,5,"REQ / NOTA-SÉRIE", 1, 0, "C", 1);
												}
												$pdf->Cell(18,5,"COD RED", 1, 0, "C", 1);
												$pdf->Cell(137,5,"DESCRIÇÃO DO MATERIAL", 1, 0, "L", 1);
												$pdf->Cell(11,5,"UNID", 1, 0, "C", 1);
												$pdf->Cell(24,5,"QUANTIDADE", 1, 0, "C", 1);
												$pdf->Cell(24,5,"VALOR TOTAL", 1, 1, "C", 1);
										}
								}else{
										if($PorDataCabecalho == 1){
												$PorDataCabecalho = 0;
												$CentroEscrito = $Centro;
												$AlmoxarifadoEscrito = $Almoxarifado;
												$pdf->Cell(16,5,"DATA", 1, 0, "C", 1);
												$pdf->Cell(12,5,"Nº MOV", 1, 0, "C", 1);
												if(($ReqNum) and ($ReqNum != "00000")) {
														$pdf->Cell(31,5,"REQ / ANO - SEQ", 1, 0, "C", 1);
												}elseif($NotaNum) {
														$pdf->Cell(31,5,"NOTA - SÉRIE", 1, 0, "C", 1);
												}else{
														$pdf->Cell(31,5,"REQ / NOTA-SÉRIE", 1, 0, "C", 1);
												}
												$pdf->Cell(70,5,"CENTRO DE CUSTO", 1, 0, "L", 1);
												$pdf->Cell(15,5,"COD RED", 1, 0, "C", 1);
												$pdf->Cell(84,5,"DESCRIÇÃO DO MATERIAL", 1, 0, "L", 1);
												$pdf->Cell(9,5,"UNID", 1, 0, "C", 1);
												$pdf->Cell(20,5,"QUANTIDADE", 1, 0, "C", 1);
												$pdf->Cell(23,5,"VALOR TOTAL", 1, 1, "C", 1);
										}
								}

								# Ordenado por Centro de Custo #
								if($Ordem == "C"){
										# Quebra de Linha para Descrição do Material - Início #
										$Fim    = 70;  // 81
										$Coluna = 130; //167 - Grande demais, corta palavras, pequeno, provoca espaços desnecessários
										$setX = 94;
										$MaterialDescSepara = SeparaFrase($MaterialDesc,$Fim);
										$TamDescMaterial    = $pdf->GetStringWidth($MaterialDescSepara);
										if($TamDescMaterial <= $Coluna){
												$LinhasMat = 1;
												$AlturaMat = 5;
										}elseif($TamDescMaterial > $Coluna and $TamDescMaterial <= 2*$Coluna ){
												$LinhasMat = 2;
												$AlturaMat = 10;
										}elseif($TamDescMaterial > 2*$Coluna and $TamDescMaterial <= 3*$Coluna ){
												$LinhasMat = 3;
												$AlturaMat = 15;
										}else{
												$LinhasMat = 4;
												$AlturaMat = 20;
										}
										if($TamDescMaterial > $Coluna){
												$Inicio = 0;
												for($Quebra = 0; $Quebra < $LinhasMat; $Quebra++){
														if($pdf->PageNo()>1000){
															//$pdf->Close();
															//header("location: RelMovimentacaoTipoMovimento.php?Mens=1&Tipo=2&Mensagem=Documento é grande demais para ser gerado (maior que 1000 folhas). Tente selecionar um período menor");
															//exit();
														}

														if($Quebra == 0){
																if(!is_null($MovAnterior)){
																		if(($ReqNum and $ReqNum != "00000" and $ReqNum != $MovAnterior) or ($NotaNum and $NotaNum != $MovAnterior)){
																				if($MovAnteriorTipo=="R"){
																						$pdf->Cell(232,5,"TOTAL DA REQUISIÇÃO", 1, 0, "R", 1);
																				}elseif($MovAnteriorTipo=="N"){
																						imprimeempenho($Almoxarifado,$NotaAnoAnterior,$NotaCodigoAnterior,$db,$pdf);
																						$pdf->Cell(232,5,"TOTAL DA NOTA FISCAL", 1, 0, "R", 1);
																						$NotaAnoAnterior = null;
																						$NotaCodigoAnterior = null;
																				}
																				$pdf->Cell(24,5,converte_quant($MovQtdAnterior), 1, 0, "R", 0);
																				$pdf->Cell(24,5,converte_valor_estoques($MovValorAnterior), 1, 1, "R", 0);
																				$MovQtdAnterior = 0;
																				$MovValorAnterior = 0;
																		}
																}
																$pdf->Cell(18,$AlturaMat,$Data,1,0,"C",0);
																$pdf->Cell(13,$AlturaMat,$MovNumero,1, 0,"R",0);
																if(($ReqNum) and ($ReqNum != "00000")){
																		$pdf->Cell(35,$AlturaMat,"$ReqNum/$ReqAno - ".sprintf("%06d",$ReqSeq), 1, 0, "R", 0);
																		$MovAnterior = $ReqNum;
																		$MovQtdAnterior += str_replace(",",".",str_replace(".","",$Quantidade));
																		$MovValorAnterior += str_replace(",",".",str_replace(".","",$QuantValor));
																		$MovAnteriorTipo = "R";
																}elseif($NotaNum){
																		$pdf->Cell(35,$AlturaMat,$NotaNum."-".$NotaSeri, 1, 0, "R", 0);
																		$MovAnterior = $NotaNum;
																		$MovQtdAnterior += str_replace(",",".",str_replace(".","",$Quantidade));
																		$MovValorAnterior += str_replace(",",".",str_replace(".","",$QuantValor));
																		$NotaAnoAnterior = $NotaAno;
																		$NotaCodigoAnterior = $NotaCodigo;
																		$MovAnteriorTipo = "N";
																}else{
																		$pdf->Cell(35,$AlturaMat,"-", 1, 0, "R", 0);
																}
																$pdf->Cell(18,$AlturaMat,"$CodigoReduzido", 1, 0, "R", 0);
																$pdf->Cell(137,5,trim(substr($MaterialDescSepara,$Inicio,$Fim)),0,0,"L",0);
																$pdf->Cell(11,$AlturaMat,"$Unidade", 1, 0, "C", 0);
																$pdf->Cell(24,$AlturaMat,"$Quantidade", 1, 0, "R", 0);
																$pdf->Cell(24,$AlturaMat,"$QuantValor", 1, 0, "R", 0);
																$pdf->Ln(5);
														}elseif($Quebra == 1){
																$pdf->SetX($setX);
																$pdf->Cell(137,5,trim(substr($MaterialDescSepara,$Inicio,$Fim)),0,0,"L",0);
																$pdf->Ln(5);
														}elseif($Quebra == 2){
																$pdf->SetX($setX);
																$pdf->Cell(137,5,trim(substr($MaterialDescSepara,$Inicio,$Fim)),0,0,"L",0);
																$pdf->Ln(5);
														}elseif($Quebra == 3){
																$pdf->SetX($setX);
																$pdf->Cell(137,5,trim(substr($MaterialDescSepara,$Inicio,$Fim)),0,0,"L",0);
																$pdf->Ln(5);
														}else{
																$pdf->SetX($setX);
																$pdf->Cell(137,5,trim(substr($MaterialDescSepara,$Inicio,$Fim)),0,0,"L",0);
																$pdf->Ln(5);
														}
														$Inicio = $Inicio + $Fim;
												}
												$pdf->SetX($setX);
												$pdf->Cell(137,0,"",1,1,"",0);
												# Quebra de Linha para Descrição do Material - Fim #
												}else{
														if(!is_null($MovAnterior)){
																if(($ReqNum and $ReqNum != "00000" and $ReqNum != $MovAnterior) or ($NotaNum and $NotaNum != $MovAnterior)){
																		if($MovAnteriorTipo=="R"){
																				$pdf->Cell(232,5,"TOTAL DA REQUISIÇÃO", 1, 0, "R", 1);
																		}elseif($MovAnteriorTipo=="N"){
																				imprimeempenho($Almoxarifado,$NotaAnoAnterior,$NotaCodigoAnterior,$db,$pdf);
																				$pdf->Cell(232,5,"TOTAL DA NOTA FISCAL", 1, 0, "R", 1);
																				$NotaAnoAnterior = null;
																				$NotaCodigoAnterior = null;
																		}
																		$pdf->Cell(24,5,converte_quant($MovQtdAnterior), 1, 0, "R", 0);
																		$pdf->Cell(24,5,converte_valor_estoques($MovValorAnterior), 1, 1, "R", 0);
																		$MovQtdAnterior = 0;
																		$MovValorAnterior = 0;
																}
														}
														$CentroEscrito = $Centro;
														$AlmoxarifadoEscrito = $Almoxarifado;
														$pdf->Cell(18,5,"$Data", 1, 0, "L", 0);
														$pdf->Cell(13,5,"$MovNumero", 1, 0, "R", 0);
														if(($ReqNum) and ($ReqNum != "00000")){
																$pdf->Cell(35,5,"$ReqNum/$ReqAno - ".sprintf("%06d",$ReqSeq), 1, 0, "R", 0);
																$MovAnterior = $ReqNum;
																$MovQtdAnterior += str_replace(",",".",str_replace(".","",$Quantidade));
																$MovValorAnterior += str_replace(",",".",str_replace(".","",$QuantValor));
																$MovAnteriorTipo = "R";
														}elseif($NotaNum){
																$pdf->Cell(35,5,$NotaNum."-".$NotaSeri, 1, 0, "R", 0);
																$MovAnterior = $NotaNum;
																$MovQtdAnterior += str_replace(",",".",str_replace(".","",$Quantidade));
																$MovValorAnterior += str_replace(",",".",str_replace(".","",$QuantValor));
																$NotaAnoAnterior = $NotaAno;
																$NotaCodigoAnterior = $NotaCodigo;
																$MovAnteriorTipo = "N";
														}else{
																$pdf->Cell(35,5,"-", 1, 0, "R", 0);
														}
														$pdf->Cell(18,5,"$CodigoReduzido", 1, 0, "R", 0);
														$pdf->Cell(137,5,"$MaterialDesc", 1, 0, "L", 0);
														$pdf->Cell(11,5,"$Unidade", 1, 0, "C", 0);
														$pdf->Cell(24,5,"$Quantidade", 1, 0, "R", 0);
														$pdf->Cell(24,$AlturaMat,"$QuantValor", 1, 1, "R", 0);
												}
										}else{
												# Ordenado por Data #
												# Quebra de Linha para Descrição do Material - Início #
												$Fim    = 45; //52
												$Coluna = 79; //92 - Grande demais, corta palavras, pequeno, provoca espaços desnecessários
												$setX   = 154;
												$MaterialDescSepara = SeparaFrase($MaterialDesc,$Fim);
												$TamDescMaterial    = $pdf->GetStringWidth($MaterialDescSepara);
												if($TamDescMaterial <= $Coluna){
														$LinhasMat = 1;
														$AlturaMat = 5;
												}elseif($TamDescMaterial > $Coluna and $TamDescMaterial <= 2*($Coluna-2) ){
														$LinhasMat = 2;
														$AlturaMat = 10;
												}elseif($TamDescMaterial > 2*($Coluna-2) and $TamDescMaterial <= 3*($Coluna-4) ){
														$LinhasMat = 3;
														$AlturaMat = 15;
												}elseif($TamDescMaterial > 3*($Coluna-4) and $TamDescMaterial <= 4*($Coluna-6) ){
														$LinhasMat = 4;
														$AlturaMat = 20;
												}elseif($TamDescMaterial > 4*($Coluna-6) and $TamDescMaterial <= 5*($Coluna-8) ) {
														$LinhasMat = 5;
														$AlturaMat = 25;
												}elseif($TamDescMaterial > 5*($Coluna-8) and $TamDescMaterial <= 6*($Coluna-10) ){
														$LinhasMat = 6;
														$AlturaMat = 30;
												}elseif($TamDescMaterial > 6*($Coluna-10) and $TamDescMaterial <= 7*($Coluna-10) ){
														$LinhasMat = 7;
														$AlturaMat = 35;
												}else{
														$LinhasMat = 8;
														$AlturaMat = 40;
												}

												if($TamDescMaterial > $Coluna){
														$Inicio = 0;
														for($Quebra = 0; $Quebra < $LinhasMat; $Quebra++){
																if($pdf->PageNo()>1000){
																	//$pdf->Close();
																	//header("location: RelMovimentacaoTipoMovimento.php?Mens=1&Tipo=2&Mensagem=Documento é grande demais para ser gerado (maior que 1000 folhas). Tente selecionar um período menor");
																	//exit();
																}
																if($Quebra == 0){
																		if(!is_null($MovAnterior)){
																				if(($ReqNum and $ReqNum != "00000" and $ReqNum != $MovAnterior) or ($NotaNum and $NotaNum != $MovAnterior)){
																						if($MovAnteriorTipo=="R"){
																								$pdf->Cell(237,5,"TOTAL DA REQUISIÇÃO", 1, 0, "R", 1);
																						}elseif($MovAnteriorTipo=="N"){
																								imprimeempenho($Almoxarifado,$NotaAnoAnterior,$NotaCodigoAnterior,$db,$pdf);
																								$pdf->Cell(237,5,"TOTAL DA NOTA FISCAL", 1, 0, "R", 1);
																								$NotaAnoAnterior = null;
																								$NotaCodigoAnterior = null;
																						}
																						$pdf->Cell(20,5,converte_quant($MovQtdAnterior), 1, 0, "R", 0);
																						$pdf->Cell(23,5,converte_valor_estoques($MovValorAnterior), 1, 1, "R", 0);
																						$MovQtdAnterior = 0;
																						$MovValorAnterior = 0;
																				}
																		}
																		$pdf->Cell(16,$AlturaMat,$Data,1,0,"C",0);
																		$pdf->Cell(12,$AlturaMat,$MovNumero,1, 0,"R",0);
																		if(($ReqNum) and ($ReqNum != "00000")){
																				$pdf->Cell(31,$AlturaMat,"$ReqNum/$ReqAno - ".sprintf("%06d",$ReqSeq), 1, 0, "R", 0);
																				$MovAnterior = $ReqNum;
																				$MovQtdAnterior += str_replace(",",".",str_replace(".","",$Quantidade));
																				$MovValorAnterior += str_replace(",",".",str_replace(".","",$QuantValor));
																				$MovAnteriorTipo = "R";
																		}elseif($NotaNum){
																				$pdf->Cell(31,$AlturaMat,$NotaNum."-".$NotaSeri, 1, 0, "R", 0);
																				$MovAnterior = $NotaNum;
																				$MovQtdAnterior += str_replace(",",".",str_replace(".","",$Quantidade));
																				$MovValorAnterior += str_replace(",",".",str_replace(".","",$QuantValor));
																				$NotaAnoAnterior = $NotaAno;
																				$NotaCodigoAnterior = $NotaCodigo;
																				$MovAnteriorTipo = "N";
																		}else{
																				$pdf->Cell(31,$AlturaMat,"-", 1, 0, "R", 0);
																		}
																		if($CentroDesc){
                                                                            $pdf->Cell(70,$AlturaMat,"$CentroDesc", 1, 0, "L", 0);
                                                                        }else{
                                                                            if($_GET['Movimentacao'] == 12 && !empty($MovCod1)) {
                                                                                $x = $pdf->GetX() + 70;
                                                                                $y = $pdf->GetY();
                                                                                $pdf->MultiCell(70, 6.7, $AlmoxarifadoDesc, 1, "L", 0);
                                                                                $pdf->SetXY($x, $y);
                                                                            } else{
                                                                                $pdf->Cell(70,$AlturaMat,"$AlmoxarifadoDesc", 1, 0, "L", 0);
                                                                            }
																		}
																		$CentroDesc = null;
																		$Centro = null;
																		$pdf->Cell(15,$AlturaMat,"$CodigoReduzido", 1, 0, "R", 0);
																		$pdf->Cell(84,5,trim(substr($MaterialDescSepara,$Inicio,$Fim)),0,0,"L",0);
																		$pdf->Cell(9,$AlturaMat,"$Unidade", 1, 0, "C", 0);
																		$pdf->Cell(20,$AlturaMat,"$Quantidade", 1, 0, "R", 0);
																		$pdf->Cell(23,$AlturaMat,"$QuantValor", 1, 0, "R", 0);
																		$pdf->Ln(5);
																}elseif($Quebra == 1){
																		$pdf->SetX($setX);
																		$pdf->Cell(84,5,trim(substr($MaterialDescSepara,$Inicio,$Fim)),0,0,"L",0);
																		$pdf->Ln(5);
																}elseif($Quebra == 2){
																		$pdf->SetX($setX);
																		$pdf->Cell(84,5,trim(substr($MaterialDescSepara,$Inicio,$Fim)),0,0,"L",0);
																		$pdf->Ln(5);
																}elseif($Quebra == 3){
																		$pdf->SetX($setX);
																		$pdf->Cell(84,5,trim(substr($MaterialDescSepara,$Inicio,$Fim)),0,0,"L",0);
																		$pdf->Ln(5);
																}else{
																		$pdf->SetX($setX);
																		$pdf->Cell(84,5,trim(substr($MaterialDescSepara,$Inicio,$Fim)),0,0,"L",0);
																		$pdf->Ln(5);
																}
																$Inicio = $Inicio + $Fim;
														}
														$pdf->SetX($setX);
														$pdf->Cell(84,0,"",1,1,"",0);
												# Quebra de Linha para Descrição do Material - Fim #
												}else{
														if(!is_null($MovAnterior)){
																if(($ReqNum and $ReqNum != "00000" and $ReqNum != $MovAnterior) or ($NotaNum and $NotaNum != $MovAnterior)){
																		if($MovAnteriorTipo=="R"){
																				$pdf->Cell(237,5,"TOTAL DA REQUISIÇÃO", 1, 0, "R", 1);
																		}elseif($MovAnteriorTipo=="N"){
																				imprimeempenho($Almoxarifado,$NotaAnoAnterior,$NotaCodigoAnterior,$db,$pdf);
																				$pdf->Cell(237,5,"TOTAL DA NOTA FISCAL", 1, 0, "R", 1);
																				$NotaAnoAnterior = null;
																				$NotaCodigoAnterior = null;
																		}
																		$pdf->Cell(20,5,converte_quant($MovQtdAnterior), 1, 0, "R", 0);
																		$pdf->Cell(23,5,converte_valor_estoques($MovValorAnterior), 1, 1, "R", 0);
																		$MovQtdAnterior = 0;
																		$MovValorAnterior = 0;
																}
														}
														$pdf->Cell(16,5,"$Data", 1, 0, "L", 0);
														$pdf->Cell(12,5,"$MovNumero", 1, 0, "R", 0);
														if(($ReqNum) and ($ReqNum != "00000")){
																$pdf->Cell(31,5,"$ReqNum/$ReqAno - ".sprintf("%06d",$ReqSeq), 1, 0, "R", 0);
																$MovAnterior = $ReqNum;
																$MovQtdAnterior += str_replace(",",".",str_replace(".","",$Quantidade));
																$MovValorAnterior += str_replace(",",".",str_replace(".","",$QuantValor));
																$MovAnteriorTipo = "R";
														}elseif($NotaNum){
																$pdf->Cell(31,5,$NotaNum."-".$NotaSeri , 1, 0, "R", 0);
																$MovAnterior = $NotaNum;
																$MovQtdAnterior += str_replace(",",".",str_replace(".","",$Quantidade));
																$MovValorAnterior += str_replace(",",".",str_replace(".","",$QuantValor));
																$NotaAnoAnterior = $NotaAno;
																$NotaCodigoAnterior = $NotaCodigo;
																$MovAnteriorTipo = "N";
														}else{
																$pdf->Cell(31,5,"-", 1, 0, "R", 0);
														}
														if($CentroDesc){
																$pdf->Cell(70,5,"$CentroDesc", 1, 0, "L", 0);
														}else{
																$pdf->Cell(70,5,"$AlmoxarifadoDesc", 1, 0, "L", 0);
														}
														$CentroDesc = null;
														$Centro = null;
														$pdf->Cell(15,5,"$CodigoReduzido", 1, 0, "R", 0);
														$pdf->Cell(84,5,"$MaterialDesc", 1, 0, "L", 0);
														$pdf->Cell(9,5,"$Unidade", 1, 0, "C", 0);
														$pdf->Cell(20,5,"$Quantidade", 1, 0, "R", 0);
														$pdf->Cell(23,$AlturaMat,"$QuantValor", 1, 1, "R", 0);
												}
										}
								}
						}
						if($UmaOuMais){
								# Imprime último/único total de movimentação #
								if($Ordem == "C"){
										if(!is_null($MovAnterior)){
												if(!is_null($MovAnterior)){
														if($MovAnteriorTipo=="R"){
																$pdf->Cell(232,5,"TOTAL DA REQUISIÇÃO", 1, 0, "R", 1);
														}elseif($MovAnteriorTipo=="N"){
																imprimeempenho($Almoxarifado,$NotaAnoAnterior,$NotaCodigoAnterior,$db,$pdf);
																$pdf->Cell(232,5,"TOTAL DA NOTA FISCAL", 1, 0, "R", 1);
																$NotaAnoAnterior = null;
																$NotaCodigoAnterior = null;
														}
														$pdf->Cell(24,5,converte_quant($MovQtdAnterior), 1, 0, "R", 0);
														$pdf->Cell(24,5,converte_valor_estoques($MovValorAnterior), 1, 1, "R", 0);
														$MovQtdAnterior = 0;
														$MovValorAnterior = 0;
												}
										}
										if($TotalQtdMovPend){
												$TotalQtdMovPend = converte_quant(sprintf("%01.2f",str_replace(",",".",$TotalQtdMovPend)));
												$TotalValMovPend = converte_valor_estoques(sprintf("%01.4f",str_replace(",",".",$TotalValMovPend)));
												$pdf->Cell(237,5,"TOTAL $DescricaoMovAnterior (Pendente)",1,0,"R",1);
												$pdf->Cell(20,5,$TotalQtdMovPend,1,0,"R",0);
												$pdf->Cell(23,5,$TotalValMovPend,1,1,"R",0);
										}
										if($TotalQtdMovConf){
												$TotalQtdMovConf = converte_quant(sprintf("%01.2f",str_replace(",",".",$TotalQtdMovConf)));
												$TotalValMovConf = converte_valor_estoques(sprintf("%01.4f",str_replace(",",".",$TotalValMovConf)));
												$pdf->Cell(237,5,"TOTAL $DescricaoMovAnterior (Confirmada)",1,0,"R",1);
												$pdf->Cell(20,5,$TotalQtdMovConf,1,0,"R",0);
												$pdf->Cell(23,5,$TotalValMovConf,1,1,"R",0);
										}
										$TotalQtdMov     = converte_quant(sprintf("%01.2f",str_replace(",",".",$TotalQtdMov)));
										$TotalValMov     = converte_valor_estoques(sprintf("%01.4f",str_replace(",",".",$TotalValMov)));
										$pdf->Cell(232,5,"TOTAL $DescricaoMovAnterior",1,0,"R",1);
										$pdf->Cell(24,5,$TotalQtdMov,1,0,"R",0);
										$pdf->Cell(24,5,$TotalValMov,1,1,"R",0);
								}else{
										if(!is_null($MovAnterior)){
												if(!is_null($MovAnterior)){
														if($MovAnteriorTipo=="R"){
																$pdf->Cell(237,5,"TOTAL DA REQUISIÇÃO", 1, 0, "R", 1);
														}elseif($MovAnteriorTipo=="N"){
																imprimeempenho($Almoxarifado,$NotaAnoAnterior,$NotaCodigoAnterior,$db,$pdf);
																$pdf->Cell(237,5,"TOTAL DA NOTA FISCAL", 1, 0, "R", 1);
																$NotaAnoAnterior = null;
																$NotaCodigoAnterior = null;
														}
														$pdf->Cell(20,5,converte_quant($MovQtdAnterior), 1, 0, "R", 0);
														$pdf->Cell(23,5,converte_valor_estoques($MovValorAnterior), 1, 1, "R", 0);
														$MovQtdAnterior = 0;
														$MovValorAnterior = 0;
												}
										}
										if($TotalQtdMovPend){
												$TotalQtdMovPend = converte_quant(sprintf("%01.2f",str_replace(",",".",$TotalQtdMovPend)));
												$TotalValMovPend = converte_valor_estoques(sprintf("%01.4f",str_replace(",",".",$TotalValMovPend)));
												$pdf->Cell(237,5,"TOTAL $DescricaoMovAnterior (Pendente)",1,0,"R",1);
												$pdf->Cell(20,5,$TotalQtdMovPend,1,0,"R",0);
												$pdf->Cell(23,5,$TotalValMovPend,1,1,"R",0);
										}
										if($TotalQtdMovConf){
												$TotalQtdMovConf = converte_quant(sprintf("%01.2f",str_replace(",",".",$TotalQtdMovConf)));
												$TotalValMovConf = converte_valor_estoques(sprintf("%01.4f",str_replace(",",".",$TotalValMovConf)));
												$pdf->Cell(237,5,"TOTAL $DescricaoMovAnterior (Confirmada)",1,0,"R",1);
												$pdf->Cell(20,5,$TotalQtdMovConf,1,0,"R",0);
												$pdf->Cell(23,5,$TotalValMovConf,1,1,"R",0);
										}
										$TotalQtdMov     = converte_quant(sprintf("%01.2f",str_replace(",",".",$TotalQtdMov)));
										$TotalValMov     = converte_valor_estoques(sprintf("%01.4f",str_replace(",",".",$TotalValMov)));
										$pdf->Cell(237,5,"TOTAL $DescricaoMovAnterior",1,0,"R",1);
										$pdf->Cell(20,5,$TotalQtdMov,1,0,"R",0);
										$pdf->Cell(23,5,$TotalValMov,1,1,"R",0);
								}
						}
				if($Ordem == "C"){
						if($TotalEntrada){
								if($TotalEntradaPend){
										$TotalEntradaPend    = converte_quant(sprintf("%01.2f",str_replace(",",".",$TotalEntradaPend)));
										$TotalEntradaValPend = converte_valor_estoques(sprintf("%01.4f",str_replace(",",".",$TotalEntradaValPend)));
										$pdf->Cell(232,5,"TOTAL GERAL ENTRADA (Pendente)",1,0,"R",1);
										$pdf->Cell(24,5,$TotalEntradaPend,1,0,"R",0);
										$pdf->Cell(24,5,$TotalEntradaValPend,1,1,"R",0);
								}
								if($TotalEntradaConf){
										$TotalEntradaConf    = converte_quant(sprintf("%01.2f",str_replace(",",".",$TotalEntradaConf)));
										$TotalEntradaValConf = converte_valor_estoques(sprintf("%01.4f",str_replace(",",".",$TotalEntradaValConf)));
										$pdf->Cell(232,5,"TOTAL GERAL ENTRADA (Confirmada)",1,0,"R",1);
										$pdf->Cell(24,5,$TotalEntradaConf,1,0,"R",0);
										$pdf->Cell(24,5,$TotalEntradaValConf,1,1,"R",0);
								}
								$TotalEntrada    = converte_quant(sprintf("%01.2f",str_replace(",",".",$TotalEntrada)));
								$TotalEntradaVal = converte_valor_estoques(sprintf("%01.4f",str_replace(",",".",$TotalEntradaVal)));
								$pdf->Cell(232,5,"TOTAL GERAL ENTRADA",1,0,"R",1);
								$pdf->Cell(24,5,$TotalEntrada,1,0,"R",0);
								$pdf->Cell(24,5,$TotalEntradaVal,1,1,"R",0);
						}
						if($TotalSaida){
								if($TotalSaidaPend){
										$TotalSaidaPend    = converte_quant(sprintf("%01.2f",str_replace(",",".",$TotalSaidaPend)));
										$TotalSaidaValPend = converte_valor_estoques(sprintf("%01.4f",str_replace(",",".",$TotalSaidaValPend)));
										$pdf->Cell(232,5,"TOTAL GERAL SAÍDA (Pendente)",1,0,"R",1);
										$pdf->Cell(24,5,$TotalSaidaPend,1,0,"R",0);
										$pdf->Cell(24,5,$TotalSaidaValPend,1,1,"R",0);
								}
								if($TotalSaidaConf){
										$TotalSaidaConf    = converte_quant(sprintf("%01.2f",str_replace(",",".",$TotalSaidaConf)));
										$TotalSaidaValConf = converte_valor_estoques(sprintf("%01.4f",str_replace(",",".",$TotalSaidaValConf)));
										$pdf->Cell(232,5,"TOTAL GERAL SAÍDA (Confirmada)",1,0,"R",1);
										$pdf->Cell(24,5,$TotalSaidaConf,1,0,"R",0);
										$pdf->Cell(24,5,$TotalSaidaValConf,1,1,"R",0);
								}
								$TotalSaida    = converte_quant(sprintf("%01.2f",str_replace(",",".",$TotalSaida)));
								$TotalSaidaVal = converte_valor_estoques(sprintf("%01.4f",str_replace(",",".",$TotalSaidaVal)));
								$pdf->Cell(232,5,"TOTAL GERAL SAÍDA",1,0,"R",1);
								$pdf->Cell(24,5,$TotalSaida,1,0,"R",0);
								$pdf->Cell(24,5,$TotalSaidaVal,1,1,"R",0);
						}
				}else{
						if($TotalEntrada){
								if($TotalEntradaPend){
										$TotalEntradaPend    = converte_quant(sprintf("%01.2f",str_replace(",",".",$TotalEntradaPend)));
										$TotalEntradaValPend = converte_valor_estoques(sprintf("%01.4f",str_replace(",",".",$TotalEntradaValPend)));
										$pdf->Cell(237,5,"TOTAL GERAL ENTRADA (Pendente)",1,0,"R",1);
										$pdf->Cell(20,5,$TotalEntradaPend,1,0,"R",0);
										$pdf->Cell(23,5,$TotalEntradaValPend,1,1,"R",0);
								}
								if($TotalEntradaConf){
										$TotalEntradaConf    = converte_quant(sprintf("%01.2f",str_replace(",",".",$TotalEntradaConf)));
										$TotalEntradaValConf = converte_valor_estoques(sprintf("%01.4f",str_replace(",",".",$TotalEntradaValConf)));
										$pdf->Cell(237,5,"TOTAL GERAL ENTRADA (Confirmada)",1,0,"R",1);
										$pdf->Cell(20,5,$TotalEntradaConf,1,0,"R",0);
										$pdf->Cell(23,5,$TotalEntradaValConf,1,1,"R",0);
								}
								$TotalEntrada    = converte_quant(sprintf("%01.2f",str_replace(",",".",$TotalEntrada)));
								$TotalEntradaVal = converte_valor_estoques(sprintf("%01.4f",str_replace(",",".",$TotalEntradaVal)));
								$pdf->Cell(237,5,"TOTAL GERAL ENTRADA",1,0,"R",1);
								$pdf->Cell(20,5,$TotalEntrada,1,0,"R",0);
								$pdf->Cell(23,5,$TotalEntradaVal,1,1,"R",0);
						}
						if($TotalSaida){
								if($TotalSaidaPend){
										$TotalSaidaPend    = converte_quant(sprintf("%01.2f",str_replace(",",".",$TotalSaidaPend)));
										$TotalSaidaValPend = converte_valor_estoques(sprintf("%01.4f",str_replace(",",".",$TotalSaidaValPend)));
										$pdf->Cell(237,5,"TOTAL GERAL SAÍDA (Pendente)",1,0,"R",1);
										$pdf->Cell(20,5,$TotalSaidaPend,1,0,"R",0);
										$pdf->Cell(23,5,$TotalSaidaValPend,1,1,"R",0);
								}
								if($TotalSaidaConf){
										$TotalSaidaConf    = converte_quant(sprintf("%01.2f",str_replace(",",".",$TotalSaidaConf)));
										$TotalSaidaValConf = converte_valor_estoques(sprintf("%01.4f",str_replace(",",".",$TotalSaidaValConf)));
										$pdf->Cell(237,5,"TOTAL GERAL SAÍDA (Confirmada)",1,0,"R",1);
										$pdf->Cell(20,5,$TotalSaidaConf,1,0,"R",0);
										$pdf->Cell(23,5,$TotalSaidaValConf,1,1,"R",0);
								}
								$TotalSaida    = converte_quant(sprintf("%01.2f",str_replace(",",".",$TotalSaida)));
								$TotalSaidaVal = converte_valor_estoques(sprintf("%01.4f",str_replace(",",".",$TotalSaidaVal)));
								$pdf->Cell(237,5,"TOTAL GERAL SAÍDA",1,0,"R",1);
								$pdf->Cell(20,5,$TotalSaida,1,0,"R",0);
								$pdf->Cell(23,5,$TotalSaidaVal,1,1,"R",0);
						}
				}
		}else{
				$Mensagem = "Nenhuma Ocorrência Encontrada";
				$Url = "RelMovimentacaoTipoMovimento.php?Mensagem=".urlencode($Mensagem)."&Mens=1&Tipo=1";
				if(!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
				header("location: ".$Url);
				exit;
		}
}
$db->disconnect();
$pdf->Output();
?>

<?php
#------------------------------------------------------------------------------------
# Portal da DGCO
# Programa: RelMovimentacaoTipoMovimentoCustoPdf.php
# Objetivo: Programa para impressão do relatório de movimentações de material
#           agrupadas por tipo de movimentação, para movimentações que geram custo e 
#           contabilidade. No caso de saída por requisição a data de movimentação é 
#           baseada na data da situação (tipo=5 - baixada) da requisição em questão.
# Autor:    Álvaro Faria
# Data:     20/09/2006
# Alterado: Álvaro Faria
# Data:     01/12/2006 - Mudança da posição do totalizador por requisição que estava
#                        imprimindo após a exibição do tipo das movimentações seguintes
# Alterado: Álvaro Faria
# Data:     11/12/2006 - Correção no select para trazer as movimentações diferentes de requisição
# Alterado: Álvaro Faria
# Data:     12/12/2006 - Correção dos totalizadores, principalmente o de requisição
# Alterado: Carlos Abreu
# Data:     28/12/2006 - Correção exibição código centro de custo
# Alterado: Carlos Abreu
# Data:     19/04/2007 - Correção para ajsutar data das movimentacoes quando for emprestimo ou mov parecida
# Alterado: Rossana Lira
# Data:     25/04/2007 - Correção para não entrar os código de movimentações 19 e 20, pois estes não geram movimentos 
# Alterado: Carlos Abreu
# Data:     27/04/2007 - Correção para atribuir valor mais recente da requisicao quando possui movimentacoes 19 e 20 
#           para custos e contabilidade 
# Alterado: Carlos Abreu
# Data:     07/05/2007 - sql retornar apenas as requisicoes baixadas
# Alterado: Carlos Abreu
# Data:     29/05/2007 - exibir sequencial da requisicao
# Alterado: Rossana Lira/ Rodrigo Melo
# Data:     22/10/2007 - Correção para não exibir atendimento com quantidade zerada, pois não gera lançamentos e
#                        correção para buscar a quantidade atendida em caso de requisição com acerto de requisição
#           23/10/2007 - Alteração da query p/correção da exibição de outras movimentações, além de saída p/requisição
#           24/10/2007 - Inserção do Total por requisição
#           25/10/2007 - Correção para exibir quantidade zerada nos casos de requisição atendida total e com devolução interna
#                        após o período de geração do relatório, deixando a qtde zerada, mas o movimento da época tem que ser exibido.
#                        Por exemplo uma requisição com atendimento total e depois devolução interna de um item zerando a qtde atendida
#                        deve exibir neste relatório a movimentação do item e depois no período em que houver o lançamento de devolução
#                        exibir o estorno e não não mostrar o lançamento se a qtde estiver atendida. Pois o sistema não guarda histórico
#                        de quantidades atendidas, grava sempre a última alteração.
#           29/10/2007 - Correção para exibir quantidade movimentada de um item que teve a quantidade atendida igual a zero no atendimento
#                        e que depois foi feita uma movimentação de saída por acerto de requisição (movimento nº 20 e que não teve nº 4 
#                        na tabela de movimentação).
# Alterado: Rodrigo Melo
# Data:     11/06/2008 - Correção para obter o Valor correto para as movimentações 3,7 e 8 (valor unitário * quantidade) e para as outras movimentações (valor médio * quantidade)
# OBS.:     Tabulação 2 espaços
#------------------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_cache_limiter('private_no_expire');
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/estoques/RelMovimentacaoTipoMovimentoCusto.php' );

# Variáveis com o global off #
if($_SERVER['REQUEST_METHOD'] == "GET"){
		$Almoxarifado     = $_GET['Almoxarifado'];
		$TipoMaterial     = $_GET['TipoMaterial'];
		$Movimentacao     = $_GET['Movimentacao'];
		$TipoMovimentacao = $_GET['TipoMovimentacao'];
		$Ordem            = $_GET['Ordem'];
		$DataFim          = $_GET['DataFim'];
		$DataIni          = $_GET['DataIni'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;
//# Função exibe o Cabeçalho e o Rodapé #
CabecalhoRodapePaisagem();

# Informa o Título do Relatório #
if($Ordem == "C"){
		$TituloRelatorio = "Relatório de Movimentação por Tipo para Custo (Ordem: Descrição Centro de Custo)";
}else{
		$TituloRelatorio = "Relatório de Movimentação por Tipo para Custo (Ordem: Data da Movimentação)";
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
if( PEAR::isError($res) ){
		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
}else{
		$Campo            = $res->fetchRow();
		$DescAlmoxarifado = $Campo[0];
}
$pdf->Cell(30,5,"ALMOXARIFADO",1,0,"L",1);
$pdf->Cell(250,5,$DescAlmoxarifado,1,1,"L",0);

$pdf->Cell(30,5,"PERÍODO",1,0,"L",1);
$pdf->Cell(250,5,$DataIni." À ".$DataFim,1,1,"L",0);
$pdf->ln(5);

# Inicializa variáveis #
$TotalQtdMov     = 0;
$TotalValMov     = 0;
$TotalEntrada    = 0;
$TotalEntradaVal = 0;
$TotalSaida      = 0;
$TotalSaidaVal   = 0;

$sqlitens .= " SELECT DISTINCT MOV.CALMPOCODI, MOV.AMOVMAANOM, MOV.CMOVMACODI, "; // SFPC.TBMOVIMENTACAOMATERIAL
$sqlitens .= "        CASE WHEN MOV.CREQMASEQU IS NOT NULL AND MOV.CTIPMVCODI NOT IN (2,21,22) THEN TO_DATE(TO_CHAR(SIT.TSITRESITU,'YYYY-MM-DD'),'YYYY-MM-DD') ELSE MOV.DMOVMAMOVI END AS DATAMOV, ";
$sqlitens .= "        CASE WHEN NOT (MOV.CTIPMVCODI = 4 AND EXISTS (SELECT CTIPMVCODI FROM SFPC.TBMOVIMENTACAOMATERIAL WHERE CREQMASEQU = MOV.CREQMASEQU AND CMATEPSEQU = MOV.CMATEPSEQU 	AND CTIPMVCODI IN(19,20)))";
$sqlitens .= "        THEN MOV.AMOVMAQTDM	";	
$sqlitens .= "        ELSE (SELECT ITEM.AITEMRQTAT FROM SFPC.TBITEMREQUISICAO ITEM WHERE MOV.CMATEPSEQU = ITEM.CMATEPSEQU AND MOV.CREQMASEQU = ITEM.CREQMASEQU)";
$sqlitens .= "        END AS QTDEMOV,";
$sqlitens .= "        CASE WHEN  NOT (MOV.CTIPMVCODI = 4 AND EXISTS (SELECT CTIPMVCODI FROM SFPC.TBMOVIMENTACAOMATERIAL WHERE CREQMASEQU = MOV.CREQMASEQU AND CMATEPSEQU = MOV.CMATEPSEQU 	AND CTIPMVCODI IN(19,20)))";
$sqlitens .= "        THEN MOV.AMOVMAQTDM * (CASE WHEN MOV.CTIPMVCODI IN (3,7,8) THEN (MOV.VMOVMAVALO) ELSE (MOV.VMOVMAUMED) END) ";		
$sqlitens .= "        ELSE (SELECT ITEM.AITEMRQTAT FROM SFPC.TBITEMREQUISICAO ITEM WHERE MOV.CMATEPSEQU = ITEM.CMATEPSEQU AND MOV.CREQMASEQU = ITEM.CREQMASEQU) *";
$sqlitens .= "             (SELECT VMOVMAVALO FROM SFPC.TBMOVIMENTACAOMATERIAL WHERE CMATEPSEQU = MOV.CMATEPSEQU AND CREQMASEQU = MOV.CREQMASEQU AND (FMOVMASITU IS NULL OR FMOVMASITU = 'A' )"; 
$sqlitens .= "          		AND TMOVMAULAT = ( SELECT MAX(TMOVMAULAT) FROM SFPC.TBMOVIMENTACAOMATERIAL WHERE CTIPMVCODI IN (4,19,20) 	AND CMATEPSEQU = MOV.CMATEPSEQU AND CREQMASEQU = MOV.CREQMASEQU AND (FMOVMASITU IS NULL OR FMOVMASITU = 'A' ))) ";
$sqlitens .= "        END AS VALOR,";
$sqlitens .= "        MOV.CMOVMACODT, MOV.CTIPMVCODI, ";
$sqlitens .= "        MAT.CMATEPSEQU, MAT.EMATEPDESC, ";                 // SFPC.TBMATERIALPORTAL
$sqlitens .= "        UND.EUNIDMSIGL, ";                                 // SFPC.TBUNIDADEDEMEDIDA
$sqlitens .= "        TIP.FTIPMVTIPO, TIP.ETIPMVDESC, ";                 // SFPC.TBTIPOMOVIMENTACAO
$sqlitens .= "        NFI.AENTNFNOTA, NFI.AENTNFSERI, NFI.FENTNFCANC, "; // SFPC.TBENTRADANOTAFISCAL
$sqlitens .= "        REQ.AREQMAANOR, REQ.CREQMACODI, ";                 // SFPC.TBREQUISICAOMATERIAL
$sqlitens .= "        CEN.CCENPOCENT, CEN.CCENPODETA, CEN.ECENPODESC, CEN.ECENPODETA, "; // SFPC.TBCENTROCUSTOPORTAL
$sqlitens .= "        ALM.EALMPODESC, SIT.CTIPSRCODI, GRU.FGRUMSTIPM, "; // SFPC.TBALMOXARIFADOPORTAL
$sqlitens .= "        CEN.CCENPOCORG, CEN.CCENPOUNID, CEN.CCENPONRPA, REQ.CREQMASEQU "; // Órgão / Unidade / RPA
$sqlitens .= "   FROM SFPC.TBALMOXARIFADOPORTAL ALM, ";
$sqlitens .= "        SFPC.TBMATERIALPORTAL MAT, ";
$sqlitens .= "        SFPC.TBUNIDADEDEMEDIDA UND, ";
$sqlitens .= "        SFPC.TBSUBCLASSEMATERIAL SUB, SFPC.TBGRUPOMATERIALSERVICO GRU, ";
$sqlitens .= "        SFPC.TBTIPOMOVIMENTACAO TIP, ";
$sqlitens .= "        SFPC.TBMOVIMENTACAOMATERIAL MOV ";
$sqlitens .= "   LEFT OUTER JOIN SFPC.TBENTRADANOTAFISCAL NFI ";
$sqlitens .= "     ON MOV.CALMPOCODI = NFI.CALMPOCODI ";
$sqlitens .= "    AND MOV.AENTNFANOE = NFI.AENTNFANOE ";
$sqlitens .= "    AND MOV.CENTNFCODI = NFI.CENTNFCODI ";
$sqlitens .= "   LEFT OUTER JOIN SFPC.TBREQUISICAOMATERIAL REQ ";
$sqlitens .= "     ON MOV.CREQMASEQU = REQ.CREQMASEQU ";
$sqlitens .= "   LEFT OUTER JOIN SFPC.TBITEMREQUISICAO ITE ";
$sqlitens .= "     ON MOV.CREQMASEQU = ITE.CREQMASEQU ";
# Busca pela situação da requisição, se esta movimentação tiver haver com requisição #
$sqlitens .= "   LEFT OUTER JOIN SFPC.TBSITUACAOREQUISICAO SIT ";
$sqlitens .= "     ON MOV.CREQMASEQU = SIT.CREQMASEQU ";
$sqlitens .= "    AND TSITREULAT IN ";
$sqlitens .= "                   (SELECT MAX(TSITREULAT) FROM SFPC.TBSITUACAOREQUISICAO ";
$sqlitens .= "                     WHERE CREQMASEQU = SIT.CREQMASEQU) ";
$sqlitens .= "   LEFT OUTER JOIN SFPC.TBCENTROCUSTOPORTAL CEN ";
$sqlitens .= "     ON REQ.CCENPOSEQU = CEN.CCENPOSEQU ";
$sqlitens .= "  WHERE MOV.CALMPOCODI = $Almoxarifado ";
if ($TipoMovimentacao) $sqlitens .= " AND   TIP.FTIPMVTIPO = '$TipoMovimentacao' ";
if ($Movimentacao)     $sqlitens .= " AND   TIP.CTIPMVCODI = '$Movimentacao' ";
# Não traz movimentações que não geram custo #
$sqlitens .= "  AND   (TIP.CTIPMVCODI NOT IN(0,1,3,5,7,8,18,19,20,31) ";
# Traz movimentações de saída por acerto de requisição(20) que não tenha tido a quantidade 
# atendida do item no momento do atendimento(tem 20-saída por acerto de requisição mas não tem 4-saída por requisição
# na tabela de movimentação)
$sqlitens .= "  OR    MOV.CTIPMVCODI = 20 AND NOT EXISTS (SELECT CTIPMVCODI FROM SFPC.TBMOVIMENTACAOMATERIAL WHERE CREQMASEQU = MOV.CREQMASEQU AND CMATEPSEQU = MOV.CMATEPSEQU 	AND CTIPMVCODI IN(4)))";
# Dependendo do tipo da movimentação, sendo uma movimentação entre almoxarifados, #
# deve requerer que esta já esteja concluída (MOV.FMOVMACORR = 'S'), pois só elas geram custo #
$sqlitens .= "  AND ( (TIP.CTIPMVCODI IN(12,13,15,30) AND MOV.FMOVMACORR = 'S') OR ";
$sqlitens .= "        (TIP.CTIPMVCODI NOT IN(12,13,15,30) ) ) ";
# Só busca quantidades atendidas maior que zero, para o caso de atendimento total e depois parcial de um item da requisição
$sqlitens .= "  AND ((MOV.CTIPMVCODI = 4 AND EXISTS (SELECT CTIPMVCODI FROM SFPC.TBMOVIMENTACAOMATERIAL WHERE CREQMASEQU = MOV.CREQMASEQU AND CMATEPSEQU = MOV.CMATEPSEQU	AND CTIPMVCODI IN(19,20))";
$sqlitens .= "  AND ITE.AITEMRQTAT > 0 AND MOV.CREQMASEQU IS NOT NULL AND MOV.CMATEPSEQU = ITE.CMATEPSEQU ) ";
$sqlitens .= "  OR (MOV.CTIPMVCODI = 4 AND NOT EXISTS (SELECT CTIPMVCODI FROM SFPC.TBMOVIMENTACAOMATERIAL WHERE CREQMASEQU = MOV.CREQMASEQU AND CMATEPSEQU = MOV.CMATEPSEQU	AND CTIPMVCODI IN(19,20))";
$sqlitens .= "  AND MOV.CREQMASEQU IS NOT NULL AND MOV.CMATEPSEQU = ITE.CMATEPSEQU) OR MOV.CTIPMVCODI <> 4)"; 
#
$sqlitens .= "  AND ";
$sqlitens .= "  CASE WHEN MOV.CREQMASEQU IS NOT NULL AND MOV.CTIPMVCODI NOT IN (2,21,22) THEN ";
$sqlitens .= "        TO_DATE(TO_CHAR(SIT.TSITRESITU,'YYYY-MM-DD'),'YYYY-MM-DD') BETWEEN '".DataInvertida($DataIni)."' AND '".DataInvertida($DataFim)."' ";
$sqlitens .= "        AND REQ.CCENPOSEQU = CEN.CCENPOSEQU ";
$sqlitens .= "  ELSE ";
$sqlitens .= "         CASE WHEN MOV.CTIPMVCODI IN (12,13,15,30) THEN ";
$sqlitens .= "              (SELECT DATATRANSACAO.DMOVMAMOVI ";
$sqlitens .= "                 FROM SFPC.TBMOVIMENTACAOMATERIAL DATATRANSACAO ";
$sqlitens .= "                WHERE DATATRANSACAO.CALMPOCOD1 = MOV.CALMPOCODI ";
$sqlitens .= "                  AND DATATRANSACAO.AMOVMAANO1 = MOV.AMOVMAANOM ";
$sqlitens .= "                  AND DATATRANSACAO.CMOVMACOD1 = MOV.CMOVMACODI ";
$sqlitens .= "                  AND DATATRANSACAO.CTIPMVCODI IN (6,9,11,29) ";
$sqlitens .= "                  AND CASE WHEN MOV.CTIPMVCODI = 11 THEN ";
$sqlitens .= "                           DATATRANSACAO.CMATEPSEQ1 = MOV.CMATEPSEQU ";
$sqlitens .= "                      ELSE ";
$sqlitens .= "                           DATATRANSACAO.CMATEPSEQU = MOV.CMATEPSEQU ";                     
$sqlitens .= "                      END";
$sqlitens .= "              ) BETWEEN '".DataInvertida($DataIni)."' AND '".DataInvertida($DataFim)."' ";
$sqlitens .= "         ELSE  ";
$sqlitens .= "	           MOV.DMOVMAMOVI BETWEEN '".DataInvertida($DataIni)."' AND '".DataInvertida($DataFim)."' ";
$sqlitens .= "         END ";
$sqlitens .= "  END ";
$sqlitens .= "  AND MOV.CTIPMVCODI = TIP.CTIPMVCODI ";
$sqlitens .= "  AND MAT.CMATEPSEQU = MOV.CMATEPSEQU ";
$sqlitens .= "  AND MAT.CUNIDMCODI = UND.CUNIDMCODI ";
$sqlitens .= "  AND MOV.CALMPOCODI = ALM.CALMPOCODI ";
$sqlitens .= "  AND MAT.CSUBCLSEQU = SUB.CSUBCLSEQU ";
$sqlitens .= "  AND SUB.CGRUMSCODI = GRU.CGRUMSCODI ";
$sqlitens .= "  AND CASE WHEN SIT.CTIPSRCODI IS NOT NULL THEN SIT.CTIPSRCODI = 5 ELSE SIT.CTIPSRCODI IS NULL END ";
if($TipoMaterial != 'T'){
		$sqlitens .= "  AND GRU.FGRUMSTIPM = '$TipoMaterial' ";
}
$sqlitens .= "  AND   (MOV.FMOVMASITU IS NULL OR MOV.FMOVMASITU = 'A') "; // Apresentar só as movimentações ativas
if($Ordem == "C"){
		$sqlitens .= " ORDER BY TIP.ETIPMVDESC, CEN.ECENPODESC, CEN.ECENPODETA, ";
		$sqlitens .= " CASE WHEN MOV.CREQMASEQU IS NOT NULL AND MOV.CTIPMVCODI NOT IN (2,21,22) THEN TO_DATE(TO_CHAR(SIT.TSITRESITU,'YYYY-MM-DD'),'YYYY-MM-DD') ELSE MOV.DMOVMAMOVI END, ";
		$sqlitens .= " MOV.CMOVMACODT ";
}else{
		$sqlitens .= " ORDER BY TIP.ETIPMVDESC, ";
		$sqlitens .= " CASE WHEN MOV.CREQMASEQU IS NOT NULL AND MOV.CTIPMVCODI NOT IN (2,21,22) THEN TO_DATE(TO_CHAR(SIT.TSITRESITU,'YYYY-MM-DD'),'YYYY-MM-DD') ELSE MOV.DMOVMAMOVI END, ";
		$sqlitens .= " MOV.CMOVMACODT";
}

$resitens = $db->query($sqlitens);
if( PEAR::isError($resitens) ){
		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlitens");
}else{
		$rowsitens = $resitens->numRows();
		if($rowsitens > 0){
				while($rowitens = $resitens->fetchRow()){
						$Almoxarifado          = $rowitens[0];
						$AnoMovimentacao       = $rowitens[1];
						$Sequencial            = $rowitens[2];
						$Data                  = databarra($rowitens[3]);
						$Quantidade            = converte_quant($rowitens[4]);
						$QuantSoma             = $rowitens[4];
						$QuantValor            = converte_valor_estoques($rowitens[5]);
						$QuantXValor           = $rowitens[5];
						$MovNumero             = $rowitens[6];
						$Movimentacao          = $rowitens[7];
						$CodigoReduzido        = $rowitens[8];
						$MaterialDesc          = $rowitens[9]." (".$rowitens[24].")";
						$Unidade               = $rowitens[10];
						$TipoMovimentacao      = $rowitens[11];
						$DescricaoMovimentacao = $rowitens[12];
						$NotaNum               = $rowitens[13];
						$NotaSeri              = $rowitens[14];
						$NotaCancelada         = $rowitens[15];
						$ReqAno                = $rowitens[16];
						$ReqNum                = substr("00000".$rowitens[17],-5); 
						$Centro                = $rowitens[18];
						$Deta                  = $rowitens[19];
						$CentroDesc            = substr($rowitens[20],0,39);
						$CentroDeta            = substr($rowitens[21],0,43);
						$AlmoxarifadoDesc      = substr($rowitens[22],0,43);
						$SituacaoRequisicao    = $rowitens[23];
						$CCOrgao               = $rowitens[25];
						$CCUnidade             = $rowitens[26];
						$CCRpa                 = $rowitens[27];
						$ReqSeq                = $rowitens[28];
						# Só exibe a movimentação de requisição se esta for uma requisição baixada (5), pois só esta gera custo #
						if(!$SituacaoRequisicao or $SituacaoRequisicao == 5) {
								if($Ordem == "C"){							
										if( ($ReqNum."/".$ReqAno != $ReqAnterior) and $ReqAnterior){
												$TotalQtdReq = converte_quant($TotalQtdReq);
												$TotalValReq = converte_valor_estoques($TotalValReq);
												$pdf->Cell(232,5,"TOTAL DA REQUISIÇÃO $ReqAnterior-$SeqAnterior",1,0,"R",1);
												$pdf->Cell(24,5,$TotalQtdReq,1,0,"R",0);
												$pdf->Cell(24,5,$TotalValReq,1,1,"R",0);
												$TotalQtdReq = 0;
												$TotalValReq = 0;
										}
								}else{
										if( ($ReqNum."/".$ReqAno != $ReqAnterior) and $ReqAnterior){
												$TotalQtdReq = converte_quant($TotalQtdReq);
												$TotalValReq = converte_valor_estoques($TotalValReq);
												$pdf->Cell(237,5,"TOTAL DA REQUISIÇÃO $ReqAnterior-$SeqAnterior",1,0,"R",1);
												$pdf->Cell(20,5,$TotalQtdReq,1,0,"R",0);
												$pdf->Cell(23,5,$TotalValReq,1,1,"R",0);
												$TotalQtdReq = 0;
												$TotalValReq = 0;
										}
								}		
								if($Movimentacao != $MovimentacaoEscrito){
										if($UmaOuMais){
												# Imprime totais da movimentação anterior #
												$TotalQtdMov = converte_quant($TotalQtdMov);
												$TotalValMov = converte_valor_estoques($TotalValMov);
												if($Ordem == "C"){
														$pdf->Cell(232,5,"TOTAL $DescricaoMovAnterior",1,0,"R",1);
														$pdf->Cell(24,5,$TotalQtdMov,1,0,"R",0);
														$pdf->Cell(24,5,$TotalValMov,1,1,"R",0);
												}else{
														$pdf->Cell(237,5,"TOTAL $DescricaoMovAnterior",1,0,"R",1);
														$pdf->Cell(20,5,$TotalQtdMov,1,0,"R",0);
														$pdf->Cell(23,5,$TotalValMov,1,1,"R",0);
												}
										}
										$UmaOuMais   = 1;
										$TotalQtdMov = 0;
										$TotalValMov = 0;
										$TotalQtdReq = 0;
										$TotalValReq = 0;
										$DescricaoMovAnterior = $DescricaoMovimentacao;
										$pdf->Cell(280,5,"TIPO DE MOVIMENTO: $DescricaoMovimentacao",1,1,"C",1);
										$MovimentacaoEscrito = $Movimentacao;
										$CentroEscrito       = null;
										$AlmoxarifadoEscrito = null;
										$PorDataCabecalho    = 1;
								}

								$TotalQtdMov = $TotalQtdMov + $QuantSoma;
								$TotalValMov = $TotalValMov + $QuantXValor;

								if($TipoMovimentacao == "S") $TotalSaida   = $TotalSaida   + str_replace(",",".",$QuantSoma);
								if($TipoMovimentacao == "E") $TotalEntrada = $TotalEntrada + str_replace(",",".",$QuantSoma);

								if($TipoMovimentacao == "S") $TotalSaidaVal   = $TotalSaidaVal   + str_replace(",",".",$QuantXValor);
								if($TipoMovimentacao == "E") $TotalEntradaVal = $TotalEntradaVal + str_replace(",",".",$QuantXValor);

								if($Ordem == "C"){
										$TotalQtdReq = $TotalQtdReq + $QuantSoma;
										$TotalValReq = $TotalValReq + $QuantXValor;
										if($ReqNum and $ReqAno) {
												$ReqAnterior = $ReqNum."/".$ReqAno;
												$SeqAnterior = $ReqSeq; // sequencial da requisição (chave primária)
										} else {
											 	$ReqAnterior = null;
											 	$SeqAnterior = null;
										}	 
										if( ($Centro != $CentroEscrito) or ( (!$Centro) and ($Almoxarifado != $AlmoxarifadoEscrito) )  ){
												if($CentroDesc) {
														$pdf->Cell(280,5,"Org. $CCOrgao / Unid. $CCUnidade / RPA $CCRpa / Centro Custo $Centro / Func. de Gov. $Deta","TLR",1,"C",0);
														$pdf->Cell(280,5,"CENTRO DE CUSTO: $CentroDesc - $CentroDeta","LRB",1,"C",0);
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
												$pdf->Cell(137,5,"DESCRIÇÃO DO MATERIAL (CONSUMO/PERMANENTE)", 1, 0, "L", 1);
												$pdf->Cell(11,5,"UNID", 1, 0, "C", 1);
												$pdf->Cell(24,5,"QUANTIDADE", 1, 0, "C", 1);
												$pdf->Cell(24,5,"VALOR TOTAL", 1, 1, "C", 1);
										}
								}else{
	                  $TotalQtdReq = $TotalQtdReq + $QuantSoma; 
									  $TotalValReq = $TotalValReq + $QuantXValor; 
	                  if($ReqNum and $ReqAno){
	                  		$ReqAnterior = $ReqNum."/".$ReqAno; 
	                  		$SeqAnterior = $ReqSeq; // sequencial da requisição (chave primária)
	                  }	else {
	                  		$ReqAnterior = null;
												$SeqAnterior = null;             		
	                  }		
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
												$pdf->Cell(84,5,"DESCRIÇÃO DO MATERIAL (CONSUMO/PERMANENTE)", 1, 0, "L", 1);
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
										$setX   = 94;
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
														if($Quebra == 0){
																$pdf->Cell(18,$AlturaMat,$Data,1,0,"C",0);
																$pdf->Cell(13,$AlturaMat,$MovNumero,1, 0,"R",0);
																if(($ReqNum) and ($ReqNum != "00000")){
																		$pdf->Cell(35,$AlturaMat,"$ReqNum/$ReqAno - ".sprintf("%06d",$ReqSeq), 1, 0, "R", 0);
																}elseif($NotaNum){
																		$pdf->Cell(35,$AlturaMat,$NotaNum."-".$NotaSeri, 1, 0, "R", 0);
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
														$CentroEscrito = $Centro;
														$AlmoxarifadoEscrito = $Almoxarifado;
														$pdf->Cell(18,5,"$Data", 1, 0, "L", 0);
														$pdf->Cell(13,5,"$MovNumero", 1, 0, "R", 0);
														if(($ReqNum) and ($ReqNum != "00000")){
																$pdf->Cell(35,5,"$ReqNum/$ReqAno - ".sprintf("%06d",$ReqSeq), 1, 0, "R", 0);
														}elseif($NotaNum){
																$pdf->Cell(35,5,$NotaNum."-".$NotaSeri, 1, 0, "R", 0);
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
												$Fim    = 40; //52
												$Coluna = 82; //92 - Grande demais, corta palavras, pequeno, provoca espaços desnecessários
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
																if($Quebra == 0){
																		$pdf->Cell(16,$AlturaMat,$Data,1,0,"C",0);
																		$pdf->Cell(12,$AlturaMat,$MovNumero,1, 0,"R",0);
																		if(($ReqNum) and ($ReqNum != "00000")){
																				$pdf->Cell(31,$AlturaMat,"$ReqNum/$ReqAno - ".sprintf("%06d",$ReqSeq), 1, 0, "R", 0);
																		}elseif($NotaNum){
																				$pdf->Cell(31,$AlturaMat,$NotaNum."-".$NotaSeri, 1, 0, "R", 0);
																		}else{
																				$pdf->Cell(31,$AlturaMat,"-", 1, 0, "R", 0);
																		}
																		if($CentroDesc){
																				$pdf->Cell(70,$AlturaMat,"$CentroDesc", 1, 0, "L", 0);
																		}else{
																				$pdf->Cell(70,$AlturaMat,"$AlmoxarifadoDesc", 1, 0, "L", 0);
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
														$pdf->Cell(16,5,"$Data", 1, 0, "L", 0);
														$pdf->Cell(12,5,"$MovNumero", 1, 0, "R", 0);
														if(($ReqNum) and ($ReqNum != "00000")){
																$pdf->Cell(31,5,"$ReqNum/$ReqAno - ".sprintf("%06d",$ReqSeq), 1, 0, "R", 0);
														}elseif($NotaNum){
																$pdf->Cell(31,5,$NotaNum."-".$NotaSeri, 1, 0, "R", 0);
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
								$TotalQtdMov = converte_quant($TotalQtdMov);
								$TotalValMov = converte_valor_estoques($TotalValMov);
								if($Ordem == "C"){
										if($ReqNum and $ReqAno){
												$TotalQtdReq = converte_quant($TotalQtdReq);
												$TotalValReq = converte_valor_estoques($TotalValReq);
												$pdf->Cell(232,5,"TOTAL DA REQUISIÇÃO ".$ReqNum."/".$ReqAno."-".$ReqSeq,1,0,"R",1);
												$pdf->Cell(24,5,$TotalQtdReq,1,0,"R",0);
												$pdf->Cell(24,5,$TotalValReq,1,1,"R",0);
										}
										$pdf->Cell(232,5,"TOTAL $DescricaoMovAnterior",1,0,"R",1);
										$pdf->Cell(24,5,$TotalQtdMov,1,0,"R",0);
										$pdf->Cell(24,5,$TotalValMov,1,1,"R",0);
								}else{
										if($ReqNum and $ReqAno){
												$TotalQtdReq = converte_quant($TotalQtdReq);
												$TotalValReq = converte_valor_estoques($TotalValReq);
												$pdf->Cell(237,5,"TOTAL DA REQUISIÇÃO ".$ReqNum."/".$ReqAno."-".$ReqSeq,1,0,"R",1);
												$pdf->Cell(20,5,$TotalQtdReq,1,0,"R",0);
												$pdf->Cell(23,5,$TotalValReq,1,1,"R",0);
										}
										$pdf->Cell(237,5,"TOTAL $DescricaoMovAnterior",1,0,"R",1);
										$pdf->Cell(20,5,$TotalQtdMov,1,0,"R",0);
										$pdf->Cell(23,5,$TotalValMov,1,1,"R",0);
								}
						}
				if($Ordem == "C"){
						if($TotalEntrada){
								$pdf->Cell(232,5,"TOTAL GERAL ENTRADA",1,0,"R",1);
								$TotalEntrada    = converte_quant($TotalEntrada);
								$TotalEntradaVal = converte_valor_estoques($TotalEntradaVal);
								$pdf->Cell(24,5,$TotalEntrada,1,0,"R",0);
								$pdf->Cell(24,5,$TotalEntradaVal,1,1,"R",0);
						}
						if($TotalSaida){
								$pdf->Cell(232,5,"TOTAL GERAL SAÍDA",1,0,"R",1);
								$TotalSaida    = converte_quant($TotalSaida);
								$TotalSaidaVal = converte_valor_estoques($TotalSaidaVal);
								$pdf->Cell(24,5,$TotalSaida,1,0,"R",0);
								$pdf->Cell(24,5,$TotalSaidaVal,1,1,"R",0);
						}
				}else{
						if($TotalEntrada){
								$pdf->Cell(237,5,"TOTAL GERAL ENTRADA",1,0,"R",1);
								$TotalEntrada    = converte_quant($TotalEntrada);
								$TotalEntradaVal = converte_valor_estoques($TotalEntradaVal);
								$pdf->Cell(20,5,$TotalEntrada,1,0,"R",0);
								$pdf->Cell(23,5,$TotalEntradaVal,1,1,"R",0); 
						}
						if($TotalSaida){
								$pdf->Cell(237,5,"TOTAL GERAL SAÍDA",1,0,"R",1);
								$TotalSaida    = converte_quant($TotalSaida);
								$TotalSaidaVal = converte_valor_estoques($TotalSaidaVal);
								$pdf->Cell(20,5,$TotalSaida,1,0,"R",0);
								$pdf->Cell(23,5,$TotalSaidaVal,1,1,"R",0);
						}
				}
		}else{
				$Mensagem = "Nenhuma Ocorrência Encontrada";
				$Url = "RelMovimentacaoTipoMovimentoCusto.php?Mensagem=".urlencode($Mensagem)."&Mens=1&Tipo=1";
				if(!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
				header("location: ".$Url);
				exit;
		}
}
$db->disconnect();
$pdf->Output();
?>

<?php
#---------------------------------------------------------------------------------------
# Portal da DGCO
# Programa: RelConsumoMaterialPdf.php
# Autor:    Álvaro Faria
# Data:     06/07/2006
# Objetivo: Programa de Impressão do Relatório de Consumo de Material Anual (PDF).
# Alterado: Carlos Abreu
# Data:     09/05/2007 - Ajuste no sql para padronização de relatórios 
# Alterado: Carlos Abreu
# Data:     21/08/2007 - Ajuste no sql para corrigir geracao de relatório
# OBS.:     Tabulação 2 espaços
#---------------------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_cache_limiter('private_no_expire');
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/estoques/RelConsumoMaterial.php' );

# Variáveis com o global off #
if($_SERVER['REQUEST_METHOD'] == "GET"){
		$Almoxarifado     = $_GET['Almoxarifado'];
		$Material 		    = $_GET['Material'];
		$Ano    					= $_GET['Ano'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

# Fução exibe o Cabeçalho e o Rodapé #
CabecalhoRodapePaisagem();

# Informa o Título do Relatório #
$TituloRelatorio = "Relatório de Consumo Anual de Material";

# Cria o objeto PDF, o Default é formato Retrato, A4  e a medida em milímetros #
$pdf = new PDF("L","mm","A4");

# Define um apelido para o número total de páginas #
$pdf->AliasNbPages();

# Define as cores do preenchimentos que serão usados #
$pdf->SetFillColor(220,220,220);

# Adiciona uma página no documento #
$pdf->AddPage();

# Seta as fontes que serão usadas na impressão de strings #
$pdf->SetFont("Arial","",9);

# Fazer os sqls dos primeiros dados da página #
$db = Conexao();

# Pega os dados do Almoxarifado #
$sql = "SELECT EALMPODESC FROM SFPC.TBALMOXARIFADOPORTAL WHERE CALMPOCODI = $Almoxarifado ";
$res = $db->query($sql);
if( PEAR::isError($res) ){
		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
}else{
		$Campo            = $res->fetchRow();
		$DescAlmoxarifado = $Campo[0];
}

# Query para escrever o material e a unidade do material #
$sqlmat  = " SELECT A.EMATEPDESC, B.EUNIDMSIGL FROM SFPC.TBMATERIALPORTAL A, SFPC.TBUNIDADEDEMEDIDA B ";
$sqlmat .= "  WHERE A.CUNIDMCODI = B.CUNIDMCODI AND CMATEPSEQU = $Material ";
$resmat  = $db->query($sqlmat);
if( PEAR::isError($resmat) ){
		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlmat");
}else{
		$Linha        = $resmat->fetchRow();
		$DescMaterial = $Linha[0];
		$DescUnidade  = $Linha[1];
}

$pdf->Cell(30,5,"ALMOXARIFADO",1,0,"L",1);
$pdf->Cell(250,5,$DescAlmoxarifado,1,1,"L",0);
$pdf->Cell(30,5,"ANO",1,0,"L",1);
$pdf->Cell(250,5,$Ano,1,1,"L",0);
$pdf->Cell(280,5,"CÓDIGO REDUZIDO / MATERIAL / UNIDADE",1,1,"C",1);
$pdf->MultiCell(280,5,$Material." / ".$DescMaterial." / ".$DescUnidade,1,"J",0);
$pdf->ln(5);

$pdf->Cell(94,5,"MÊS DE CONSUMO",1, 0,"C",1);
$pdf->Cell(93,5,"QUANTIDADE NO MÊS",1, 0,"C",1);
$pdf->Cell(93,5,"VALOR NO MÊS",1,1,"C",1);

# Sql principal #
$sql  = "SELECT A.DMOVMAMOVI, A.AMOVMAQTDM, A.AMOVMAQTDM * ";
$sql .= "       CASE WHEN A.CTIPMVCODI IN (4,19,20) THEN ";
$sql .= "       ( ";
$sql .= "       SELECT VMOVMAVALO FROM SFPC.TBMOVIMENTACAOMATERIAL ";
$sql .= "        WHERE CTIPMVCODI IN (4,19,20) ";
$sql .= "          AND CMATEPSEQU = A.CMATEPSEQU ";
$sql .= "          AND CREQMASEQU = A.CREQMASEQU ";
$sql .= "          AND (FMOVMASITU IS NULL OR FMOVMASITU = 'A' ) ";
$sql .= "          AND TMOVMAULAT = ( ";
$sql .= "                SELECT MAX(TMOVMAULAT) FROM SFPC.TBMOVIMENTACAOMATERIAL ";
$sql .= "                 WHERE CTIPMVCODI IN (4,19,20) ";
$sql .= "                   AND CMATEPSEQU = A.CMATEPSEQU ";
$sql .= "                   AND CREQMASEQU = A.CREQMASEQU ";
$sql .= "                   AND (FMOVMASITU IS NULL OR FMOVMASITU = 'A' ) ";
$sql .= "              ) ";
$sql .= "       ) ";
$sql .= "       ELSE ";
$sql .= "           A.VMOVMAVALO ";
$sql .= "       END ";
$sql .= "       AS SOMA, C.FTIPMVTIPO ";
$sql .= "  FROM SFPC.TBMOVIMENTACAOMATERIAL A, SFPC.TBTIPOMOVIMENTACAO C ";
$sql .= " WHERE A.CALMPOCODI = $Almoxarifado ";
$sql .= "   AND (A.FMOVMASITU IS NULL OR A.FMOVMASITU = 'A') ";
$sql .= "   AND A.DMOVMAMOVI >= '$Ano-01-01' "; // Ano-Mês-Dia
$sql .= "   AND A.DMOVMAMOVI <= '$Ano-12-31' "; // Ano-Mês-Dia
$sql .= "   AND A.CTIPMVCODI IN (2,4,18,19,20,21,22) ";
$sql .= "   AND A.CTIPMVCODI = C.CTIPMVCODI ";
$sql .= "   AND A.CMATEPSEQU = $Material ";
$sql .= " ORDER BY A.DMOVMAMOVI ";
$res  = $db->query($sql);
if( PEAR::isError($res) ){
		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
}else{
		$rows = $res->numRows();
		if( $rows == 0 ){
				$Mensagem = "Nenhuma Ocorrência Encontrada";
				$Url = "RelConsumoMaterial.php?Mensagem=".urlencode($Mensagem)."&Mens=1&Tipo=1";
				if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
				header("location: ".$Url);
				exit;
		}else{
				for( $i=0; $i < $rows; $i++ ){
						$Linha             = $res->fetchRow();
						# Através da data completa, extrai os meses durante o loop
						$Data              = explode("-",$Linha[0]);
						$Mes               = $Data[1];
						# Quantidade consumida
						$Quant             = $Linha[1];
						# Valor consumido do item (quantidade x valor)
						$QuantXValor       = $Linha[2];
						# Descobre o tipo da movimentação (S ou E) para efetuar cálculo de consumo (+ ou -)
						$TipoMovimentacao  = $Linha[3];

						# Se for a primeira execução ou o mês for o mesmo no loop, efetua cálculo de consumo
						if( (!$MesTrabalhado) or ($Mes == $MesTrabalhado) ){
								# Se for a primeira execução do loop, o MesTrabalhado passa as ser o mês corrente do loop
								if(!$MesTrabalhado) $MesTrabalhado = $Mes;
								if ($TipoMovimentacao == 'S') {
										$QtdMes   = $QtdMes + $Quant;
										$ValorMes = $ValorMes + ($QuantXValor);
								}else{
										$QtdMes   = $QtdMes - $Quant;
										$ValorMes = $ValorMes - ($QuantXValor);
								}
								# Atribui valores nas variáveis que serão impressas #
								$MesPDF   = $Mes;
								$QtdPDF   = $QtdMes;
								$ValorPDF = $ValorMes;
						# Se for outro mês no loop, começa novo cálculo do mês, e imprime no PDF o mês que terminou o cálculo
						}else{
								# Imprime no PDF o mês que terminou os cálculos, se a quantidade for diferente de 0
								if($QtdPDF != 0){
										$TotalQtd = $TotalQtd + $QtdPDF;
										$TotalVal = $TotalVal + $ValorPDF;
										if($QtdPDF > 0){
												$QtdPDF = converte_quant(sprintf("%01.2f",str_replace(",",".",$QtdPDF)));
										}else{
												$QtdPDF = "-".converte_quant(sprintf("%01.2f",str_replace(",",".",abs($QtdPDF))));
										}
										if($ValorPDF > 0){
												$ValorPDF = converte_valor_estoques(sprintf("%01.4f",str_replace(",",".",$ValorPDF)));
										}else{
												$ValorPDF = "-".converte_valor_estoques(sprintf("%01.4f",str_replace(",",".",abs($ValorPDF))));
										}
										$MesExt = MesExt($MesPDF);
										$pdf->Cell(94,5,$MesExt,1,0,"L",0);
										$pdf->Cell(93,5,$QtdPDF,1,0,"R",0);
										$pdf->Cell(93,5,$ValorPDF,1,1,"R",0);
										# Incrementa a quantidade de meses processados #
										$QtdMeses++;
								}
								# Zera contadores para o próximo mês
								$QtdMes   = 0;
								$ValorMes = 0;
								$MesPDF   = 0;
								$QtdPDF   = 0;
								$ValorPDF = 0;
								# Inicia cálculo do mês seguinte
								if ($TipoMovimentacao == 'S') {
										$QtdMes   = $QtdMes + $Quant;
										$ValorMes = $ValorMes + ($QuantXValor);
								}else{
										$QtdMes   = $QtdMes - $Quant;
										$ValorMes = $ValorMes - ($QuantXValor);
								}
								# Atribui valores nas variáveis que serão impressas #
								$MesPDF   = $Mes;
								$QtdPDF   = $QtdMes;
								$ValorPDF = $ValorMes;
								# O mes trabalhado passa a ser o mês corrente do loop #
								$MesTrabalhado = $Mes;
						}
				}

		# Imprime o ultimo/único mês #
		if($QtdPDF != 0){
				$TotalQtd = $TotalQtd + $QtdPDF;
				$TotalVal = $TotalVal + $ValorPDF;
				if($QtdPDF > 0){
						$QtdPDF = converte_quant(sprintf("%01.2f",str_replace(",",".",$QtdPDF)));
				}else{
						$QtdPDF = "-".converte_quant(sprintf("%01.2f",str_replace(",",".",abs($QtdPDF))));
				}
				if($ValorPDF > 0){
						$ValorPDF = converte_valor_estoques(sprintf("%01.4f",str_replace(",",".",$ValorPDF)));
				}else{
						$ValorPDF = "-".converte_valor_estoques(sprintf("%01.4f",str_replace(",",".",abs($ValorPDF))));
				}
				$MesExt = MesExt($MesPDF);
				$pdf->Cell(94,5,$MesExt,1,0,"L",0);
				$pdf->Cell(93,5,$QtdPDF,1,0,"R",0);
				$pdf->Cell(93,5,$ValorPDF,1,1,"R",0);
				# Incrementa a quantidade de meses processados #
				$QtdMeses++;
		}

		# Imprime resumo #		
		$pdf->Cell(47,5,"QUANTIDADE DE MESES",1,0,"R",1);
		$pdf->Cell(47,5,$QtdMeses,1,0,"R",0);
		$pdf->Cell(46,5,"QUANTIDADE TOTAL",1,0,"R",1);
		$pdf->Cell(47,5,converte_quant(sprintf("%01.2f",str_replace(",",".",$TotalQtd))),1,0,"R",0);
		$pdf->Cell(46,5,"VALOR TOTAL",1,0,"R",1);
		$pdf->Cell(47,5,converte_valor_estoques(sprintf("%01.4f",str_replace(",",".",$TotalVal))),1,1,"R",0);
		}
}
$db->disconnect();
$pdf->Output();
?>

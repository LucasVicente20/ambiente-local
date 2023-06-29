<?php
#---------------------------------------------------------------------------------------
# Portal da DGCO
# Programa: RelAtendimentoMaterialPdf.php
# Objetivo: Programa de Impressão do Relatório de Atendimento por Material em um Período.
# Autor:    Filipe Cavalcanti
# Data:     16/08/2005
# Alterado: Álvaro Faria
# Data:     10/03/2006
# Alterado: Álvaro Faria
# Data:     22/08/2006
# Alterado: Álvaro Faria
# Data:     18/12/2006 - Correção do select para mostrar a data do atendimento,
#                        e não a data da última atualização da requisição
# Alterado: Carlos Abreu
# Data:     19/04/2007 - Correção no cálculo da quantidade (conversão de ',')
# OBS.:     Tabulação 2 espaços
#---------------------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_cache_limiter('private_no_expire');
session_start();
Seguranca();

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "GET"){
		$Almoxarifado     = $_GET['Almoxarifado'];
		$Material         = $_GET['Material'];
		$DataFim          = $_GET['DataFim'];
		$DataIni          = $_GET['DataIni'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

# Fução exibe o Cabeçalho e o Rodapé #
CabecalhoRodapePaisagem();

# Informa o Título do Relatório #
$TituloRelatorio = "Relatório de Atendimento por Material em um Período";

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


$DataInibd = substr($DataIni,6,4)."-".substr($DataIni,3,2)."-".substr($DataIni,0,2);
$DataFimbd = substr($DataFim,6,4)."-".substr($DataFim,3,2)."-".substr($DataFim,0,2);

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
$sqlmat .= " WHERE A.CUNIDMCODI = B.CUNIDMCODI AND CMATEPSEQU = $Material ";
$resmat  = $db->query($sqlmat);
if( PEAR::isError($resmat) ){
		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $SqlMaterial");
}else{
		$Linha       	= $resmat->fetchRow();
		$DescMaterial = $Linha[0];
		$DescUnidade  = $Linha[1];
}

$pdf->Cell(30,5,"ALMOXARIFADO",1,0,"L",1);
$pdf->Cell(250,5,$DescAlmoxarifado,1,1,"L",0);
$pdf->Cell(30,5,"PERÍODO",1,0,"L",1);
$pdf->Cell(250,5,$DataIni." À ".$DataFim,1,1,"L",0);
$pdf->Cell(280,5,"CÓDIGO REDUZIDO / MATERIAL / UNIDADE",1,1,"C",1);
$pdf->MultiCell(280,5,$Material." / ".$DescMaterial." / ".$DescUnidade,1,"J",0);
$pdf->ln(5);

$pdf->Cell(34,5,"DATA ATENDIMENTO",1, 0,"C",1);
$pdf->Cell(146,5,"CENTRO DE CUSTO",1, 0,"C",1);
$pdf->Cell(45,5,"NÚMERO/ANO REQUISIÇÃO",1,0,"C",1);
$pdf->Cell(29,5,"QTD. SOLICITADA",1,0,"C",1);
$pdf->Cell(26,5,"QTD. ATENDIDA",1,1,"C",1);

# Sql principal #
$sql  = "SELECT DISTINCT C.TSITRESITU, A.AREQMAANOR, A.CREQMACODI, ";
$sql .= "       B.AITEMRQTSO, B.AITEMRQTAT, A.DREQMADATA, ";
$sql .= "       D.ECENPODESC, D.ECENPODETA, D.CCENPONRPA ";
$sql .= "  FROM SFPC.TBREQUISICAOMATERIAL A, ";
$sql .= "       SFPC.TBITEMREQUISICAO B, ";
$sql .= "       SFPC.TBSITUACAOREQUISICAO C, ";
$sql .= "       SFPC.TBCENTROCUSTOPORTAL D ";
$sql .= " WHERE A.CALMPOCODI = $Almoxarifado ";
$sql .= "   AND A.DREQMADATA >= '$DataInibd' ";
$sql .= "   AND A.DREQMADATA <= '$DataFimbd' ";
$sql .= "   AND A.CREQMASEQU = B.CREQMASEQU ";
$sql .= "   AND B.CMATEPSEQU = $Material ";
$sql .= "   AND B.AITEMRQTAT > 0 ";
$sql .= "   AND A.CREQMASEQU = C.CREQMASEQU ";
$sql .= "   AND C.TSITRESITU = ";
$sql .= "      (SELECT MAX(SIT.TSITRESITU) FROM SFPC.TBSITUACAOREQUISICAO SIT ";
$sql .= "        WHERE SIT.CREQMASEQU = A.CREQMASEQU AND (SIT.CTIPSRCODI IN (3,4) OR SIT.CTIPSRCODI = 6) ) ";
$sql .= "   AND C.CTIPSRCODI <> 6 ";
$sql .= "   AND A.CCENPOSEQU = D.CCENPOSEQU ";
$sql .= " ORDER BY C.TSITRESITU ";
$res  = $db->query($sql);
if( PEAR::isError($res) ){
		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
}else{
		$rows = $res->numRows();
		if($rows == 0){
				$Mensagem = "Nenhuma Ocorrência Encontrada";
				$Url = "RelAtendimentoMaterial.php?Mensagem=".urlencode($Mensagem)."&Mens=1&Tipo=1";
				if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
				header("location: ".$Url);
				exit;
		}else{
				for($i=0; $i < $rows; $i++){
						$Linha            = $res->fetchRow();
						$DataAtendimento  = $Linha[0];
						$AnoRequisicao    = $Linha[1];
						$NumRequisicao    = $Linha[2];
						$QtdSolicitada    = $Linha[3];
						$QtdAtendida      = $Linha[4];
						$DataRequisicao   = $Linha[5];
						$DescCC           = $Linha[6];
						$DetaCC           = $Linha[7];
						$RpaCC            = $Linha[8];
						$DescCentro       = "RPA ".$RpaCC." - ".$DescCC." - ".$DetaCC;
						$DataRequisicao   = substr($DataRequisicao,8,2)."/".substr($DataRequisicao,5,2)."/".substr($DataRequisicao,0,4);
						$DataAtendimento  = substr($DataAtendimento,8,2)."/".substr($DataAtendimento,5,2)."/".substr($DataAtendimento,0,4);
						$DescCentroSepara = SeparaFrase($DescCentro,93);
						$TamDescCentro    = strlen($DescCentro);
						$TamDesc    = $pdf->GetStringWidth($DescCentroSepara);
						$TamLinha   = 141;
				if( $TamDesc <= $TamLinha ){
					$LinhasDesc = 1;
					$AlturaDesc = 5;
				}elseif( $TamDesc > $TamLinha and $TamDesc <= ( $TamLinha * 2 ) ){
					$LinhasDesc = 2;
					$AlturaDesc = 10;
				}elseif( $TamDesc > ( $TamLinha * 2 ) and $TamDesc <= ( $TamLinha * 3 ) ){
					$LinhasDesc = 3;
					$AlturaDesc = 15;
				}elseif( $TamDesc > ( $TamLinha * 3 ) and $TamDesc <= ( $TamLinha * 4 ) ){
					$LinhasDesc = 4;
					$AlturaDesc = 20;
				}elseif( $TamDesc > ( $TamLinha * 4 ) and $TamDesc <= ( $TamLinha * 5 ) ){
					$LinhasDesc = 5;
					$AlturaDesc = 25;
				}elseif( $TamDesc > ( $TamLinha * 6 ) and $TamDesc <= ( $TamLinha * 6 ) ){
					$LinhasDesc = 6;
					$AlturaDesc = 30;
				}else{
					$LinhasDesc = 7;
					$AlturaDesc = 35;
				}
				if( $LinhasDesc > 1 ){
						$Inicio    = 0;
						$InicioRaz = 0;
						$pdf->Cell(34,$AlturaDesc,$DataAtendimento,1,0,"C",0);
						$pdf->SetX(44);
						$pdf->Cell(146,$AlturaDesc,"",1,0,"L",0);
						for( $Quebra = 0; $Quebra < $LinhasDesc; $Quebra ++ ){
							if( $Quebra == 0 ){
									$pdf->SetX(190);
									$pdf->Cell(45,$AlturaDesc,substr($NumRequisicao+100000,1)."/".$AnoRequisicao,1,0,"C",0);
									$pdf->Cell(29,$AlturaDesc,converte_quant($QtdSolicitada),1,0,"R",0);
									$pdf->Cell(26,$AlturaDesc,converte_quant($QtdAtendida),1,0,"R",0);
									$pdf->SetX(44);
									$pdf->Cell(220,5,trim(substr($DescCentroSepara,$Inicio,76)),0,0,"L",0);
									$pdf->Ln(5);
							}elseif( $Quebra == 1 ){
									$pdf->SetX(44);
									$pdf->Cell(220,5,trim(substr($DescCentroSepara,$Inicio,76)),0,0,"L",0);
									$pdf->Ln(5);
							}elseif( $Quebra == 2 ){
									$pdf->SetX(44);
									$pdf->Cell(220,5,trim(substr($DescCentroSepara,$Inicio,76)),0,0,"L",0);
									$pdf->Ln(5);
							}elseif( $Quebra == 3 ){
									$pdf->SetX(44);
									$pdf->Cell(220,5,trim(substr($DescCentroSepara,$Inicio,76)),0,0,"L",0);
										$pdf->Ln(5);
							}elseif( $Quebra == 4 ){
									$pdf->SetX(44);
									$pdf->Cell(220,5,trim(substr($DescCentroSepara,$Inicio,76)),0,0,"L",0);
										$pdf->Ln(5);
							}elseif( $Quebra == 5 ){
									$pdf->SetX(44);
									$pdf->Cell(220,5,trim(substr($DescCentroSepara,$Inicio,76)),0,0,"L",0);
										$pdf->Ln(5);
							}else{
									$pdf->SetX(44);
									$pdf->Cell(220,5,trim(substr($DescCentroSepara,$Inicio,76)),0,0,"L",0);
									$pdf->Ln(5);
							}
							$Inicio    = $Inicio + 76;
					}
			}else{
				$pdf->Cell(34,5,$DataAtendimento,1,0,"C",0);
				$pdf->Cell(146,5,$DescCentro,1,0,"L",0);
				$pdf->Cell(45,5,substr($NumRequisicao+100000,1)."/".$AnoRequisicao,1,0,"C",0);
				$pdf->Cell(29,5,converte_quant($QtdSolicitada),1,0,"R",0);
				$pdf->Cell(26,5,converte_quant($QtdAtendida),1,1,"R",0);
			}
						$TotalAtendida = $TotalAtendida + $QtdAtendida;
		}
		$pdf->Cell(254,5,"TOTAL ATENDIDO",1,0,"R",1);
		$pdf->Cell(26,5,converte_quant($TotalAtendida),1,1,"R",0);
	}
}
$db->disconnect();
$pdf->Output();
?>

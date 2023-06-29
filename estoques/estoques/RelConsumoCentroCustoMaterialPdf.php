<?php
#---------------------------------------------------------------------------------------
# Portal da DGCO
# Programa: RelConsumoCentroCustoPdf.php
# Autor:    Filipe Cavalcanti
# Data:     23/02/2006
# OBS.:     Tabulação 2 espaços
# ---------------------------------------------------------------------------------------
# Alterado: Álvaro Faria
# Data:     07/03/2006
# ---------------------------------------------------------------------------------------
# Alterado: Álvaro Faria
# Data:     23/08/2006
# ---------------------------------------------------------------------------------------
# Alterado: Álvaro Faria
# Data:     11/09/2006 - Mudança para 4 digitos no valor após a vírgula
# Objetivo: Programa de Impressão do Relatório de Consumo por Centro de Custo
# ---------------------------------------------------------------------------------------
# Alterado: Lucas Baracho
# Data:		24/10/2018
# Objetivo: Tarefa Redmine 205790
# ---------------------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_cache_limiter('private_no_expire');
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/estoques/RelConsumoCentroCusto.php' );

# Variáveis com o global off #
if($_SERVER['REQUEST_METHOD'] == "GET"){
		$Almoxarifado = $_GET['Almoxarifado'];
		$DataFim      = $_GET['DataFim'];
		$DataIni      = $_GET['DataIni'];
		$Material     = $_GET['Material'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

# Fução exibe o Cabeçalho e o Rodapé #
CabecalhoRodapePaisagem();

# Informa o Título do Relatório #
$TituloRelatorio = "Relatório de Consumo de Material por Centro de Custo";

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

# Período para consulta no banco de dados #
$DataInibd = substr($DataIni,6,4)."-".substr($DataIni,3,2)."-".substr($DataIni,0,2);
$DataFimbd = substr($DataFim,6,4)."-".substr($DataFim,3,2)."-".substr($DataFim,0,2);

# Conecta no banco de dados #
$db = Conexao();

# Query para escrever o almoxarifado #
$SqlAlmoxarifado  = "SELECT EALMPODESC FROM SFPC.TBALMOXARIFADOPORTAL WHERE CALMPOCODI = $Almoxarifado ";
$cms              = $db->query($SqlAlmoxarifado);
if( db::isError($cms) ){
		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $SqlAlmoxarifado");
}else{
		$campo    = $cms->fetchRow();
		$DescAlmo = $campo[0];
}

$pdf->Cell(33,5,"ALMOXARIFADO",1,0,"L",1);
$pdf->Cell(247,5,$DescAlmo,1,1,"L",0);
$pdf->Cell(33,5,"PERÍODO",1,0,"L",1);
$pdf->Cell(247,5,$DataIni." a ".$DataFim,1,1,"L",0);

# Query para escrever o material e a unidade do material #
$Sqlmaterial   = " SELECT A.EMATEPDESC, B.EUNIDMSIGL FROM SFPC.TBMATERIALPORTAL A, SFPC.TBUNIDADEDEMEDIDA B ";
$Sqlmaterial  .= " WHERE A.CUNIDMCODI = B.CUNIDMCODI AND CMATEPSEQU = $Material ";
$result        = $db->query($Sqlmaterial);
if( db::isError($result) ){
		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $SqlMaterial");
}else{
		$campo2    	= $result->fetchRow();
		$descMat    = $campo2[0];
		$descMatUni = $campo2[1];
}
$pdf->Cell(280,5,"CÓDIGO REDUZIDO / MATERIAL / UNIDADE",1,1,"C",1);
$pdf->MultiCell(280,5,$Material." / ".$descMat." / ".$descMatUni,1,"J",0);
$pdf->ln(5);

$pdf->Cell(220,5,"CENTRO DE CUSTO",1,0,"C",1);
$pdf->Cell(30,5,"QUANTIDADE",1,0,"C",1);
$pdf->Cell(30,5,"VALOR TOTAL",1,1,"C",1);

#SQL PRINCIPAL #
$Sql  = "SELECT C.CCENPONRPA, C.ECENPODESC, C.ECENPODETA, D.FTIPMVTIPO, A.AMOVMAQTDM, ";
$Sql .= "       A.AMOVMAQTDM*A.VMOVMAVALO AS SOMA, B.CCENPOSEQU ";
$Sql .= "  FROM SFPC.TBMOVIMENTACAOMATERIAL A, SFPC.TBREQUISICAOMATERIAL B, ";
$Sql .= "       SFPC.TBCENTROCUSTOPORTAL C, SFPC.TBTIPOMOVIMENTACAO D ";
$Sql .= " WHERE A.CALMPOCODI = $Almoxarifado ";
$Sql .= "   AND (A.FMOVMASITU IS NULL OR A.FMOVMASITU = 'A') ";
$Sql .= "   AND A.DMOVMAMOVI >= '$DataInibd' ";
$Sql .= "   AND A.DMOVMAMOVI <= '$DataFimbd' ";
$Sql .= "   AND A.CTIPMVCODI IN (2,4,18,19,20,21,22) ";
$Sql .= "   AND A.CTIPMVCODI = D.CTIPMVCODI ";
$Sql .= "   AND A.CREQMASEQU = B.CREQMASEQU ";
$Sql .= "   AND B.CCENPOSEQU = C.CCENPOSEQU ";
$Sql .= "   AND A.CMATEPSEQU = $Material ";
$Sql .= " ORDER BY C.CCENPONRPA, C.ECENPODESC, C.ECENPODETA ";
$res  = $db->query($Sql);
if( db::isError($res) ){
		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $Sql");
}else{
		$rows = $res->numRows();
		if($rows == 0){
				$Mensagem = "Nenhuma Ocorrência Encontrada";
				$Url = "RelConsumoCentroCusto.php?Mensagem=".urlencode($Mensagem)."&Mens=1&Tipo=1&DataIni=".urlencode($DataIni)."&DataFim=".urlencode($DataFim)."&Almoxarifado=$Almoxarifado";
				if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
				header("location: ".$Url);
				exit;
		}else{
				for($i=0; $i< $rows; $i++){
						$Linha = $res->fetchRow();
						if($i == 0){
								$DescCentroAntes  = "RPA ".$Linha[0]." - ".$Linha[1]." - ".$Linha[2];
								$DescCentroAntes  = substr($DescCentroAntes,0,115);
								$CentroCustoAntes = $Linha[5];
						}
						$RpaCC            = $Linha[0];
						$DescCC           = $Linha[1];
						$DescDeta         = $Linha[2];
						$TipoMovimentacao = $Linha[3];
						$Quantidade       = $Linha[4];						
						$QuantXValor      = $Linha[5];
						$CentroCusto      = $Linha[6];
						$DescCentro       = "RPA ".$RpaCC." - ".$DescCC." - ".$DescDeta;
						$DescCentro       = substr($DescCentro,0,115);
						if($CentroCustoAntes <> $CentroCusto){
								if($TotalCC != 0){
										$pdf->Cell(220,5,substr($DescCentroAntes,0,115),1,0,"L",0);
										$pdf->Cell(30,5,converte_quant(sprintf("%01.2f",str_replace(",",".",$QtdeCC))),1,0,"R",0);	
										if($TotalCC > 0){
												$pdf->Cell(30,5,converte_valor_estoques(sprintf("%01.4f",str_replace(",",".",$TotalCC))),1,1,"R",0);
										}else{
												$pdf->Cell(30,5,"-".converte_valor_estoques(sprintf("%01.4f",str_replace(",",".",abs($TotalCC)))),1,1,"R",0);
										}
								}
								$DescCentroAntes = $DescCentro;
								$CentroCustoAntes = $CentroCusto;
								$TotalCC    = 0;
								$QtdeCC     = 0;
						}
						if($TipoMovimentacao == 'S'){
								$QtdeCC          = $QtdeCC  +  $Quantidade;
								$TotalQtde       = $TotalQtde  +  $Quantidade;
								$TotalCC         = $TotalCC    + ($QuantXValor);
								$TotalValor      = $TotalValor + ($QuantXValor);
								
						}else{
								$QtdeCC          = $QtdeCC  -  $Quantidade;
								$TotalQtde       = $TotalQtde  -  $Quantidade;
								$TotalCC         = $TotalCC    - ($QuantXValor);
								$TotalValor      = $TotalValor - ($QuantXValor);
						}
				}
				if($TotalCC != 0){
						$pdf->Cell(220,5,substr($DescCentro,0,115),1,0,"L",0);
						$pdf->Cell(30,5,converte_quant(sprintf("%01.2f",str_replace(",",".",$QtdeCC))),1,0,"R",0);	
						if($TotalCC > 0){
								$pdf->Cell(30,5,converte_valor_estoques(sprintf("%01.4f",str_replace(",",".",$TotalCC))),1,1,"R",0);
						}else{
								$pdf->Cell(30,5,"-".converte_valor_estoques(sprintf("%01.4f",str_replace(",",".",abs($TotalCC)))),1,1,"R",0);
						}
						
				}
				$pdf->Cell(220,5,"TOTAL GERAL DE CONSUMO NO PERÍODO",1,0,"R",1);
				if($TotalValor >= 0){
						$pdf->Cell(30,5,converte_quant(sprintf("%01.2f",str_replace(",",".",$TotalQtde))),1,0,"R",0);  				
						$pdf->Cell(30,5,converte_valor_estoques(sprintf("%01.4f",str_replace(",",".",$TotalValor))),1,1,"R",0);
				}else{
						$pdf->Cell(30,5,"-".converte_quant(sprintf("%01.2f",str_replace(",",".",abs($TotalQtde)))),1,0,"R",0);
						$pdf->Cell(30,5,"-".converte_valor_estoques(sprintf("%01.4f",str_replace(",",".",abs($TotalValor)))),1,1,"R",0); 		  			
				}
		}
}
$db->disconnect();
$pdf->Output();
?>

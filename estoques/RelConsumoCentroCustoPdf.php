<?php
#---------------------------------------------------------------------------------------
# Portal da DGCO
# Programa: RelConsumoCentroCustoPdf.php
# Objetivo: Programa de Impressão do Relatório de Consumo por Centro de Custo
#---------------------------------
# Autor:    Filipe Cavalcanti
# Data:     23/02/2006
# Alterado: Álvaro Faria
# Data:     07/03/2006
# Alterado: Álvaro Faria
# Data:     23/08/2006
# Alterado: Álvaro Faria
# Data:     11/09/2006 - Mudança para 4 digitos no valor após a vírgula
# Alterado: Carlos Abreu
# Data:     09/05/2007 - Ajuste no sql para padronização de relatórios
# Alterado: Ariston Cordeiro
# Data:     13/01/2008 - Correção no relatório que mostra um mesmo almoxarifado várias vezes
# Alterado: Ariston Cordeiro
# Data:     20/01/2008 - 	Devido a alterações em tmovmaulat em 2007 e 2006, o campo não reflete a data da movimentação, o que gera erro de banco.
# 							Devido a isso, diversas correções são necessárias para tratar este caso.
#----------------------------
# OBS.:     Tabulação 2 espaços
#---------------------------------------------------------------------------------------

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
		$Almoxarifado     = $_GET['Almoxarifado'];
		$DataFim          = $_GET['DataFim'];
		$DataIni          = $_GET['DataIni'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

# Fução exibe o Cabeçalho e o Rodapé #
CabecalhoRodapePaisagem();

# Informa o Título do Relatório #
$TituloRelatorio = "Relatório de Consumo por Centro de Custo";

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
if( PEAR::isError($cms) ){
		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $SqlAlmoxarifado");
}else{
		$campo    = $cms->fetchRow();
		$DescAlmo = $campo[0];
}

$pdf->Cell(33,5,"ALMOXARIFADO",1,0,"L",1);
$pdf->Cell(247,5,$DescAlmo,1,1,"L",0);
$pdf->Cell(33,5,"PERÍODO",1,0,"L",1);
$pdf->Cell(247,5,$DataIni." À ".$DataFim,1,1,"L",0);
$pdf->ln(5);

$pdf->Cell(250,5,"CENTRO DE CUSTO",1,0,"C",1);
$pdf->Cell(30,5,"VALOR TOTAL",1,1,"C",1);

#SQL PRINCIPAL #

$Sql  = "
		SELECT C.CCENPONRPA, C.ECENPODESC, C.ECENPODETA, D.FTIPMVTIPO,
       		A.AMOVMAQTDM *
				CASE WHEN A.CTIPMVCODI IN (4,19,20) THEN
				(
					SELECT VMOVMAVALO FROM SFPC.TBMOVIMENTACAOMATERIAL MM1
						WHERE
							MM1.CMATEPSEQU = A.CMATEPSEQU
							AND MM1.CREQMASEQU = A.CREQMASEQU
							AND MM1.CTIPMVCODI IN (4,19,20)
							AND (MM1.FMOVMASITU IS NULL OR MM1.FMOVMASITU = 'A' )
							AND (MM1.DMOVMAMOVI, MM1.CMOVMACODI) = (
								SELECT MAX(MM2.DMOVMAMOVI), MAX(MM2.CMOVMACODI) FROM SFPC.TBMOVIMENTACAOMATERIAL MM2
									WHERE
										MM2.CTIPMVCODI IN (4,19,20)
										AND MM2.CMATEPSEQU = MM1.CMATEPSEQU
										AND MM2.CREQMASEQU = MM1.CREQMASEQU
										AND (MM2.FMOVMASITU IS NULL OR MM2.FMOVMASITU = 'A' )
							)
				)
				ELSE
					A.VMOVMAVALO
				END
			AS SOMA, B.CCENPOSEQU, C.CORGLICODI, C.CCENPOCORG, C.CCENPOUNID,
			C.CCENPONRPA, C.CCENPOCENT, C.CCENPODETA
		FROM
			SFPC.TBMOVIMENTACAOMATERIAL A, SFPC.TBREQUISICAOMATERIAL B,
			SFPC.TBCENTROCUSTOPORTAL C, SFPC.TBTIPOMOVIMENTACAO D
		WHERE A.CALMPOCODI = $Almoxarifado
			AND (A.FMOVMASITU IS NULL OR A.FMOVMASITU = 'A')
			AND A.DMOVMAMOVI >= '$DataInibd'
			AND A.DMOVMAMOVI <= '$DataFimbd'
			AND A.CTIPMVCODI IN (2,4,18,19,20,21,22)
			AND A.CTIPMVCODI = D.CTIPMVCODI
			AND A.CREQMASEQU = B.CREQMASEQU
			AND B.CCENPOSEQU = C.CCENPOSEQU
		ORDER BY C.CCENPONRPA, C.ECENPODESC, C.ECENPODETA, B.CCENPOSEQU
";
//echo "[".$Sql."]";

$res  = $db->query($Sql);
if( PEAR::isError($res) ){
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

				$SfpcOrgaoAntes		  = 0;
				$SfpcUnidadeAntes  	  = 0;
				$SfpcRPAAntes	      = 0;
				$SfpcCentroCustoAntes  = 0;
				$SfpcDetalhamentoAntes = 0;


				for($i=0; $i< $rows; $i++){
						$Linha = $res->fetchRow();
						if($i == 0) {
								$DescCentroAntes = "RPA ".$Linha[0]." - ".$Linha[1]." - ".$Linha[2];
								$CentroCustoAntes = $Linha[5];
						}
						$RpaCC            = $Linha[0];
						$DescCC           = $Linha[1];
						$DescDeta         = $Linha[2];
						$TipoMovimentacao = $Linha[3];
						$QuantXValor      = $Linha[4];
						$CentroCusto      = $Linha[5];

						$SfpcOrgaoPortal  = $Linha[6];
						$SfpcOrgao		  = $Linha[7];
						$SfpcUnidade  	  = $Linha[8];
						$SfpcRPA	      = $Linha[9];
						$SfpcCentroCusto  = $Linha[10];
						$SfpcDetalhamento = $Linha[11];

						$DescCentro       = "RPA ".$RpaCC." - ".$DescCC." - ".$DescDeta;
						//if($CentroCustoAntes <> $CentroCusto){
						if(
							$SfpcOrgaoAntes	<> $SfpcOrgao or
							$SfpcUnidadeAntes <> $SfpcUnidade or
							$SfpcRPAAntes <> $SfpcRPA or
							$SfpcCentroCustoAntes <> $SfpcCentroCusto or
							$SfpcDetalhamentoAntes <> $SfpcDetalhamento
						){
								if($TotalCC != 0){
										$pdf->Cell(250,5,substr($DescCentroAntes,0,127),1,0,"L",0);
										//$pdf->Cell(250,5,$Txt,1,0,"L",0);
										if($TotalCC > 0){
												$pdf->Cell(30,5,converte_valor_estoques(sprintf("%01.4f",str_replace(",",".",$TotalCC))),1,1,"R",0);
										}else{
												$pdf->Cell(30,5,"-".converte_valor_estoques(sprintf("%01.4f",str_replace(",",".",abs($TotalCC)))),1,1,"R",0);
										}
								}
								$DescCentroAntes = $DescCentro;
								$CentroCustoAntes = $CentroCusto;

								$SfpcOrgaoAntes		  = $SfpcOrgao;
								$SfpcUnidadeAntes  	  = $SfpcUnidade;
								$SfpcRPAAntes	      = $SfpcRPA;
								$SfpcCentroCustoAntes  = $SfpcCentroCusto;
								$SfpcDetalhamentoAntes = $SfpcDetalhamento;
								$TotalCC    = 0;
						}
						if($TipoMovimentacao == 'S'){
								$TotalCC         = $TotalCC    + ($QuantXValor);
								$TotalValor      = $TotalValor + ($QuantXValor);
						}else{
								$TotalCC         = $TotalCC    - ($QuantXValor);
								$TotalValor      = $TotalValor - ($QuantXValor);
						}
				}
				if($TotalCC != 0){
						$pdf->Cell(250,5,substr($DescCentro,0,127),1,0,"L",0);
						if($TotalCC > 0){
								$pdf->Cell(30,5,converte_valor_estoques(sprintf("%01.4f",str_replace(",",".",$TotalCC))),1,1,"R",0);
						}else{
								$pdf->Cell(30,5,"-".converte_valor_estoques(sprintf("%01.4f",str_replace(",",".",abs($TotalCC)))),1,1,"R",0);
						}
				}
				$pdf->Cell(250,5,"TOTAL GERAL DE CONSUMO NO PERÍODO",1,0,"R",1);
				if($TotalValor >= 0){
						$pdf->Cell(30,5,converte_valor_estoques(sprintf("%01.4f",str_replace(",",".",$TotalValor))),1,1,"R",0);
				}else{
						$pdf->Cell(30,5,"-".converte_valor_estoques(sprintf("%01.4f",str_replace(",",".",abs($TotalValor)))),1,1,"R",0);
				}
		}
}
$db->disconnect();
$pdf->Output();
?>

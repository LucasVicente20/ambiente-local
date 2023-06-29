<?php
# ---------------------------------------------------------------------------------------
# Portal da DGCO
# Programa: RelEntradaNotaFiscalPdf.php
# Objetivo: Imprimir o Relatório de Entrada por Nota Fiscal
# Autor:    Álvaro Faria
# Data:     11/07/2006
# OBS.:     Tabulação 2 espaços
# ---------------------------------------------------------------------------------------
# Alterado: Álvaro Faria
# Data:     24/08/2006 - Máximo de 16 empenhos
# ---------------------------------------------------------------------------------------
# Alterado: Carlos Abreu
# Data:     23/04/2007 - Correcao da apresentacao do numero do empenho que estava passando o almoxarifado errado
# ---------------------------------------------------------------------------------------
# Alterado: Rodrigo Melo
# Data:     18/03/2008 - Alteração para exibir o valor de consumo e permanente de forma separada.
# ---------------------------------------------------------------------------------------
# Alterado: Ariston Cordeiro
# Data:      11/09/2008 - Removido todos acessos a SFPC.TBFORNECEDORESTOQUE
# ---------------------------------------------------------------------------------------
# Alterado: Lucas Baracho
# Data:		24/10/2018
# Objetivo: Tarefa Redmine 205790
# ---------------------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/estoques/RelEntradaNotaFiscal.php' );

# Variáveis com o global off #
if($_SERVER['REQUEST_METHOD'] == "GET"){
		$Almoxarifado = $_GET['Almoxarifado'];
		$DataIni      = $_GET['DataIni'];
		$DataFim      = $_GET['DataFim'];
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
		if(PEAR::isError($resemp)){
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
						$pdf->Cell(39,5,"Nº(S) EMPENHO(S)","LRT",0,"L",0);
						$pdf->Cell(241,5,$DescEmpenho1,1,1,"L",0);
						$pdf->Cell(39,5," ","LRB",0,"L",0);
						$pdf->Cell(241,5,$DescEmpenho2."...",1,1,"L",0);
				}else{
						$pdf->Cell(39,5,"Nº(S) EMPENHO(S)","LRT",0,"L",0);
						$pdf->Cell(241,5,$DescEmpenho1,1,1,"L",0);
						$pdf->Cell(39,5," ","LRB",0,"L",0);
						$pdf->Cell(241,5,$DescEmpenho2,1,1,"L",0);
				}
		}else{
				$pdf->Cell(39,5,"Nº(S) EMPENHO(S)",1,0,"L",0);
				$pdf->Cell(241,5,$DescEmpenho1,1,1,"L",0);
		}
}

# Fução exibe o Cabeçalho e o Rodapé #
CabecalhoRodapePaisagem();

# Informa o Título do Relatório #
$TituloRelatorio = "Relatório de Entrada por Nota Fiscal";

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

# Conecta no banco de dados #
$db = Conexao();

# Query para escrever o almoxarifado #
$SqlAlmoxarifado  = "SELECT EALMPODESC FROM SFPC.TBALMOXARIFADOPORTAL WHERE CALMPOCODI = $Almoxarifado ";
$cms              = $db->query($SqlAlmoxarifado);
if(PEAR::isError($cms)){
		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $SqlAlmoxarifado");
}else{
		$campo    = $cms->fetchRow();
		$DescAlmo = $campo[0];
}

$pdf->Cell(40,5,"ALMOXARIFADO",1,0,"L",1);
$pdf->Cell(240,5,$DescAlmo,1,1,"L",0);
$pdf->Cell(40,5,"PERÍODO DE ENTRADA",1,0,"L",1);
$pdf->Cell(240,5,$DataIni." a ".$DataFim,1,1,"L",0);

# Busca os Dados da Tabela de Entrada NF de Acordo com o Argumento da Pesquisa #
$sql  = "SELECT A.CENTNFCODI, A.AENTNFANOE, A.AENTNFNOTA, A.AENTNFSERI, ";
$sql .= "       A.DENTNFENTR, B.CMATEPSEQU, C.EMATEPDESC, D.EUNIDMSIGL, ";
$sql .= "       B.AITENFQTDE, B.VITENFUNIT, ";
$sql .= "       E.NFORCRRAZS, E.AFORCRCCGC, E.AFORCRCCPF ";

##
$sql .= "       , G.FGRUMSTIPO, G. FGRUMSTIPM  ";
#$sql .= "  FROM SFPC.TBUNIDADEDEMEDIDA D, SFPC.TBMATERIALPORTAL C, ";
#$sql .= "       SFPC.TBITEMNOTAFISCAL B, SFPC.TBENTRADANOTAFISCAL A ";
$sql .= " FROM SFPC.TBSUBCLASSEMATERIAL F, SFPC.TBGRUPOMATERIALSERVICO G, ";
$sql .= " SFPC.TBUNIDADEDEMEDIDA D, SFPC.TBMATERIALPORTAL C, SFPC.TBITEMNOTAFISCAL B, SFPC.TBENTRADANOTAFISCAL A ";
##

$sql .= " INNER JOIN SFPC.TBFORNECEDORCREDENCIADO E ON E.AFORCRSEQU = A.AFORCRSEQU ";
$sql .= " WHERE A.CALMPOCODI = $Almoxarifado AND A.CENTNFCODI = B.CENTNFCODI ";
$sql .= "   AND A.AENTNFANOE = B.AENTNFANOE AND A.CALMPOCODI = B.CALMPOCODI ";
$sql .= "   AND B.CMATEPSEQU = C.CMATEPSEQU ";
$sql .= "   AND A.DENTNFENTR >= '".DataInvertida($DataIni)."' ";
$sql .= "   AND A.DENTNFENTR <= '".DataInvertida($DataFim)."' ";
$sql .= "   AND C.CUNIDMCODI = D.CUNIDMCODI ";
$sql .= "   AND (A.FENTNFCANC IS NULL OR A.FENTNFCANC = 'N' ) ";

##
$sql .= "   AND C.CSUBCLSEQU = F.CSUBCLSEQU AND F.CGRUMSCODI = G.CGRUMSCODI ";
##
/*
$sql .= " UNION ";
$sql .= "SELECT A.CENTNFCODI, A.AENTNFANOE, A.AENTNFNOTA, A.AENTNFSERI, ";
$sql .= "       A.DENTNFENTR, B.CMATEPSEQU, C.EMATEPDESC, D.EUNIDMSIGL, ";
$sql .= "       B.AITENFQTDE, B.VITENFUNIT, ";
$sql .= "       F.EFORESRAZS, F.AFORESCCGC, F.AFORESCCPF ";

##
$sql .= "       , H.FGRUMSTIPO, H. FGRUMSTIPM ";
#$sql .= "  FROM SFPC.TBUNIDADEDEMEDIDA D, SFPC.TBMATERIALPORTAL C, ";
#$sql .= "       SFPC.TBITEMNOTAFISCAL B, SFPC.TBENTRADANOTAFISCAL A ";
$sql .= "  FROM SFPC.TBSUBCLASSEMATERIAL G, SFPC.TBGRUPOMATERIALSERVICO H, ";
$sql .= "  SFPC.TBUNIDADEDEMEDIDA D, SFPC.TBMATERIALPORTAL C, SFPC.TBITEMNOTAFISCAL B, SFPC.TBENTRADANOTAFISCAL A  ";
##

$sql .= " INNER JOIN SFPC.TBFORNECEDORESTOQUE F ON F.CFORESCODI = A.CFORESCODI ";
$sql .= " WHERE A.CALMPOCODI = $Almoxarifado AND A.CENTNFCODI = B.CENTNFCODI ";
$sql .= "   AND A.AENTNFANOE = B.AENTNFANOE AND A.CALMPOCODI = B.CALMPOCODI ";
$sql .= "   AND B.CMATEPSEQU = C.CMATEPSEQU ";
$sql .= "   AND A.DENTNFENTR >= '".DataInvertida($DataIni)."' ";
$sql .= "   AND A.DENTNFENTR <= '".DataInvertida($DataFim)."' ";
$sql .= "   AND C.CUNIDMCODI = D.CUNIDMCODI ";
$sql .= "   AND (A.FENTNFCANC IS NULL OR A.FENTNFCANC = 'N' ) ";

##
$sql .= "   AND C.CSUBCLSEQU = G.CSUBCLSEQU AND G.CGRUMSCODI = H.CGRUMSCODI ";
##
*/
$sql .= "ORDER BY 5,11,3,4,7";

$res  = $db->query($sql);
if(PEAR::isError($res)){
		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
}else{
		$Qtd = $res->numRows();
		if($Qtd > 0){
				while($Linha = $res->fetchRow()){
						$NotaCodi     = $Linha[0];
						$NotaAno      = $Linha[1];
						$NotaNumero   = $Linha[2];
						$NotaSerie    = $Linha[3];
						$NotaData     = $Linha[4];
						$MatCodi      = $Linha[5];
						$MatDesc      = $Linha[6];
						$MatUnid      = $Linha[7];
						$MatQtdm      = $Linha[8];
						$MatValUnit   = $Linha[9];
						$MatVal       = $Linha[8]*$Linha[9];
						$Razao        = $Linha[10];
						$CNPJ         = $Linha[11];
						$CPF          = $Linha[12];
            $TipoGrupo    = $Linha[13]; #Tipo do Grupo (M - Material ou S - Serviço)
            $TipoMaterial = $Linha[14]; #Tipo de Material (C - Consumo ou P - Permanente)
						$IdentNota  = "$Almoxarifado.$Linha[1].$Linha[0]";

            if($DataAnterior != $NotaData){
                if($UmaOuMaisDatas){
										# Imprime empenhos relativos a nota anterior #
										$dadosnfant = explode(".",$IdentAnterior);
										imprimeempenho($dadosnfant[0],$dadosnfant[1],$dadosnfant[2],$db,$pdf);

                    ## Separando o valores para os grupo do tipo material e serviço de uma nota fiscal
                    if($ValorTotalServicoNotaFiscal > 0){ //Tipo Material Serviço - 'S'
                      $pdf->Cell(220,5,"VALOR TOTAL SERVIÇO",1,0,"R",0);
                      $pdf->Cell(60,5,converte_valor_estoques(sprintf("%01.4f",str_replace(",",".",$ValorTotalServicoNotaFiscal))),1,1,"R",0);
                    }
                    if($ValorTotalMatConsumoNotaFiscal > 0){ //Materiais do tipo Consumo - 'C'
                      $pdf->Cell(220,5,"VALOR TOTAL MATERIAL CONSUMO",1,0,"R",0);
                      $pdf->Cell(60,5,converte_valor_estoques(sprintf("%01.4f",str_replace(",",".",$ValorTotalMatConsumoNotaFiscal))),1,1,"R",0);
                    }
                    if($ValorTotalMatPermanenteNotaFiscal > 0) {  //Materiais do tipo Permanente - 'P'
                      $pdf->Cell(220,5,"VALOR TOTAL MATERIAL PERMANENTE",1,0,"R",0);
                      $pdf->Cell(60,5,converte_valor_estoques(sprintf("%01.4f",str_replace(",",".",$ValorTotalMatPermanenteNotaFiscal))),1,1,"R",0);
                    }
                    ##

                    $pdf->Cell(220,5,"VALOR TOTAL NOTA",1,0,"R",0);
										$pdf->Cell(60,5,converte_valor_estoques(sprintf("%01.4f",str_replace(",",".",$ValorTotalNota))),1,1,"R",0);
                    $pdf->Cell(220,5,"VALOR TOTAL DIA",1,0,"R",1);
										$pdf->Cell(60,5,converte_valor_estoques(sprintf("%01.4f",str_replace(",",".",$ValorTotalDia))),1,1,"R",0);
								}

                $ValorTotalServicoNotaFiscal = 0; #Referente ao valor de serviço de uma nota fiscal - TESTE
                $ValorTotalMatConsumoNotaFiscal = 0; #Referente ao valor do material do tipo consumo de uma nota fiscal - TESTE
                $ValorTotalMatPermanenteNotaFiscal = 0; #Referente ao valor do material do tipo permanente de uma nota fiscal - TESTE

								$ValorTotalNota    = 0;
								$ValorTotalDia     = 0;
								$UmaOuMaisNotasDia = 0;
								$UmaOuMaisDatas    = 1;
								# Zerando a variável $Forn neste ponto, o fornecedor será reescrito quando a data mudar #
								//$Forn              = 0;

								$pdf->ln(5);
								$pdf->Cell(29,5,"DATA:",1,0,"L",1);
								$pdf->MultiCell(251,5,DataBarra($NotaData),1,1,"L",1);
								$pdf->SetFont("Arial","",8);
								$pdf->Cell(16,5,"Nota",1,0,"C",1);
								$pdf->Cell(13,5,"Série",1,0,"C",1);
								$pdf->Cell(10,5,"Cod.",1,0,"C",1);
								$pdf->Cell(81,5,"Material",1,0,"C",1);
								$pdf->Cell(8,5,"Unid",1,0,"C",1);
								$pdf->Cell(17,5,"Qtd",1,0,"C",1);
								$pdf->Cell(17,5,"Preço Unit",1,0,"C",1);
								$pdf->Cell(30,5,"Valor",1,0,"C",1);
								$pdf->Cell(28,5,"CNPJ/CPF",1,0,"C",1);
								$pdf->Cell(60,5,"Razão / Nome",1,1,"L",1);
						}
						# Imprime o total da nota, sempre que a nota mudar #
						if($IdentNota != $IdentAnterior){
								if($UmaOuMaisNotasDia){
										# Imprime empenhos relativos a nota anterior #
										$dadosnfant = explode(".",$IdentAnterior);
										imprimeempenho($dadosnfant[0],$dadosnfant[1],$dadosnfant[2],$db,$pdf);

                    ## Separando o valores para os grupo do tipo material e serviço de uma nota fiscal
                    if($ValorTotalServicoNotaFiscal > 0){ //Tipo Material Serviço - 'S'
                      $pdf->Cell(220,5,"VALOR TOTAL SERVIÇO",1,0,"R",0);
                      $pdf->Cell(60,5,converte_valor_estoques(sprintf("%01.4f",str_replace(",",".",$ValorTotalServicoNotaFiscal))),1,1,"R",0);
                    }
                    if($ValorTotalMatConsumoNotaFiscal > 0){ //Materiais do tipo Consumo - 'C'
                      $pdf->Cell(220,5,"VALOR TOTAL MATERIAL CONSUMO",1,0,"R",0);
                      $pdf->Cell(60,5,converte_valor_estoques(sprintf("%01.4f",str_replace(",",".",$ValorTotalMatConsumoNotaFiscal))),1,1,"R",0);
                    }
                    if($ValorTotalMatPermanenteNotaFiscal > 0) {  //Materiais do tipo Permanente - 'P'
                      $pdf->Cell(220,5,"VALOR TOTAL MATERIAL PERMANENTE",1,0,"R",0);
                      $pdf->Cell(60,5,converte_valor_estoques(sprintf("%01.4f",str_replace(",",".",$ValorTotalMatPermanenteNotaFiscal))),1,1,"R",0);
                    }
                    ##

										$pdf->Cell(220,5,"VALOR TOTAL NOTA",1,0,"R",0);
										$pdf->Cell(60,5,converte_valor_estoques(sprintf("%01.4f",str_replace(",",".",$ValorTotalNota))),1,1,"R",0);
								}

                $ValorTotalServicoNotaFiscal = 0; #Referente ao valor de serviço de uma nota fiscal - TESTE
                $ValorTotalMatConsumoNotaFiscal = 0; #Referente ao valor do material do tipo consumo de uma nota fiscal - TESTE
                $ValorTotalMatPermanenteNotaFiscal = 0; #Referente ao valor do material do tipo permanente de uma nota fiscal - TESTE

								$ValorTotalNota = 0;
								$UmaOuMaisNotasDia = 1;
								# Zerando a variável $Forn neste ponto, o fornecedor será reescrito quando a número da nota mudar #
								$Forn              = 0;
						}

            if($TipoGrupo == 'S'){ //Tipo Material Serviço - 'S' -> Valor Total dos serviços de uma nota fiscal - TESTE
              $ValorTotalServicoNotaFiscal = $ValorTotalServicoNotaFiscal + $MatVal;
              $ValorTotalServicoPeriodo = $ValorTotalServicoPeriodo + $MatVal;
            } else { // Tipo Material Material - 'M'
              if($TipoMaterial == 'C'){ //Materiais do tipo Consumo - 'C'
                $ValorTotalMatConsumoNotaFiscal = $ValorTotalMatConsumoNotaFiscal + $MatVal;
                $ValorTotalMatConsumoPeriodo = $ValorTotalMatConsumoPeriodo + $MatVal;
              } else {  //Materiais do tipo Permanente - 'P'
                $ValorTotalMatPermanenteNotaFiscal = $ValorTotalMatPermanenteNotaFiscal + $MatVal;
                $ValorTotalMatPermanentePeriodo = $ValorTotalMatPermanentePeriodo + $MatVal;
              }
            }

						# Valor total de uma nota #
						$ValorTotalNota	= $ValorTotalNota + $MatVal;
						# Valor total no dia #
						$ValorTotalDia  = $ValorTotalDia  + $MatVal;
						# Valor total relatório #
						$ValorTotal     = $ValorTotal     + $MatVal;

						# Formata CNPJ/CPF #
						if($CNPJ != ""){
								$DescCnpjCpf = FormataCNPJ($CNPJ);
						}else{
								$DescCnpjCpf = FormataCPF($CPF);
						}

						# Quebra de Linha para Descrição do Material #
						if( ( $RazaoAnterior == $Razao and $Forn > 0 ) || (strlen($MatDesc) > strlen($Razao)) ){
								$DescSepara = SeparaFrase($MatDesc,45);
								$TamDesc    = $pdf->GetStringWidth($DescSepara);
								$TamLinha   = 71;
								if( $TamDesc <= $TamLinha ){
										$LinhasMat = 1;
										$AlturaMat = 5;
								}elseif( $TamDesc > $TamLinha and $TamDesc <= ( $TamLinha * 2 ) ){
										$LinhasMat = 2;
										$AlturaMat = 10;
								}elseif( $TamDesc > ( $TamLinha * 2 ) and $TamDesc <= ( $TamLinha * 3 ) ){
										$LinhasMat = 3;
										$AlturaMat = 15;
								}elseif( $TamDesc > ( $TamLinha * 3 ) and $TamDesc <= ( $TamLinha * 4 ) ){
										$LinhasMat = 4;
										$AlturaMat = 20;
								}elseif( $TamDesc > ( $TamLinha * 4 ) and $TamDesc <= ( $TamLinha * 5 ) ){
										$LinhasMat = 5;
										$AlturaMat = 25;
								}elseif( $TamDesc > ( $TamLinha * 6 ) and $TamDesc <= ( $TamLinha * 6 ) ){
										$LinhasMat = 6;
										$AlturaMat = 30;
								}else{
										$LinhasMat = 7;
										$AlturaMat = 35;
								}
						}else{
								$DescSepara = SeparaFrase($Razao,33);
								$TamDesc  = $pdf->GetStringWidth($DescSepara);
								if( $TamDesc <= 45 ){
										$LinhasMat = 1;
										$AlturaMat = 5;
								}elseif( $TamDesc > 45 and $TamDesc <= 90 ){
										$LinhasMat = 2;
										$AlturaMat = 10;
								}elseif( $TamDesc > 90 and $TamDesc <= 135 ){
										$LinhasMat = 3;
										$AlturaMat = 15;
								}else{
										$LinhasMat = 4;
										$AlturaMat = 20;
								}
						}
						$DescMaterialSepara = substr($MatDesc,0,45);
						$DescMaterialSepara = SeparaFrase($MatDesc,45);
						$DescRazaoSepara    = SeparaFrase($Razao,33);
						$DescRazao          = $Razao;
						if( ($RazaoAnterior != $Razao) and ($Forn > 0) ){
								$pdf->Ln(5);
						}
						# Se o forncecedor não mudar, não imprime a razão social, nem o CNPJ #
						if($RazaoAnterior == $Razao and $Forn > 0 ){
								$DescCnpjCpf     = "";
								$DescRazaoSepara = "";
								$DescRazao       = "";
						}
						if($LinhasMat > 1){
								$Inicio    = 0;
								$InicioRaz = 0;
								$pdf->SetX(49);
								$pdf->Cell(81,$AlturaMat,"",1,0,"L",0);
								$pdf->SetX(230);
								$pdf->Cell(60,$AlturaMat,"",1,0,"L",0);
								for( $Quebra = 0; $Quebra < $LinhasMat; $Quebra ++ ){
										if($Quebra == 0){
												$pdf->SetX(10);
												if($IdentNota != $IdentAnterior){
														$pdf->Cell(16,$AlturaMat,$NotaNumero,1,0,"C",0);
														$pdf->Cell(13,$AlturaMat,$NotaSerie,1,0,"C",0);
												}else{
														$pdf->Cell(16,$AlturaMat,"",1,0,"C",0);
														$pdf->Cell(13,$AlturaMat,"",1,0,"C",0);
												}
												$pdf->Cell(10,$AlturaMat,$MatCodi,1,0,"C",0);
												$pdf->SetX(130);
												$pdf->Cell(8,$AlturaMat,$MatUnid,1,0,"C",0);
												$pdf->Cell(17,$AlturaMat,converte_quant(sprintf("%01.2f",str_replace(",",".",$MatQtdm))),1,0,"C",0);
												$pdf->Cell(17,$AlturaMat,converte_valor_estoques(sprintf("%01.4f",str_replace(",",".",$MatValUnit))),1,0,"C",0);
												$pdf->Cell(30,$AlturaMat,converte_valor_estoques(sprintf("%01.4f",str_replace(",",".",$MatVal))),1,0,"C",0);
												$pdf->Cell(28,$AlturaMat,$DescCnpjCpf,1,0,"C",0);
												$pdf->SetX(49);
												$pdf->Cell(81,5,trim(substr($DescMaterialSepara,$Inicio,45)),0,0,"L",0);
												$pdf->SetX(230);
												$pdf->Cell(60,5,trim(substr($DescRazaoSepara,$InicioRaz,33)),0,0,"L",0);
												$pdf->Ln(5);
										}elseif($Quebra == 1){
												$pdf->SetX(49);
												$pdf->Cell(81,5,trim(substr($DescMaterialSepara,$Inicio,45)),0,0,"L",0);
												$pdf->SetX(230);
												$pdf->Cell(60,5,trim(substr($DescRazaoSepara,$InicioRaz,33)),0,0,"L",0);
												$pdf->Ln(5);
										}elseif($Quebra == 2){
												$pdf->SetX(49);
												$pdf->Cell(81,5,trim(substr($DescMaterialSepara,$Inicio,45)),0,0,"L",0);
												$pdf->SetX(230);
												$pdf->Cell(60,5,trim(substr($DescRazaoSepara,$InicioRaz,33)),0,0,"L",0);
												$pdf->Ln(5);
										}elseif($Quebra == 3){
												$pdf->SetX(49);
												$pdf->Cell(81,5,trim(substr($DescMaterialSepara,$Inicio,45)),0,0,"L",0);
												$pdf->SetX(230);
												$pdf->Cell(60,5,trim(substr($DescRazaoSepara,$InicioRaz,33)),0,0,"L",0);
												$pdf->Ln(5);
										}elseif($Quebra == 4){
												$pdf->SetX(49);
												$pdf->Cell(81,5,trim(substr($DescMaterialSepara,$Inicio,45)),0,0,"L",0);
												$pdf->SetX(230);
												$pdf->Cell(60,5,trim(substr($DescRazaoSepara,$InicioRaz,33)),0,0,"L",0);
												$pdf->Ln(5);
										}elseif($Quebra == 5){
												$pdf->SetX(49);
												$pdf->Cell(81,5,trim(substr($DescMaterialSepara,$Inicio,45)),0,0,"L",0);
												$pdf->SetX(230);
												$pdf->Cell(60,5,trim(substr($DescRazaoSepara,$InicioRaz,33)),0,0,"L",0);
												$pdf->Ln(5);
										}else{
												$pdf->SetX(49);
												$pdf->Cell(81,5,trim(substr($DescMaterialSepara,$Inicio,45)),0,0,"L",0);
												$pdf->SetX(230);
												$pdf->Cell(60,5,trim(substr($DescRazaoSepara,$InicioRaz,33)),0,0,"L",0);
												$pdf->Ln(5);
										}
										$Inicio    = $Inicio + 45;
										$InicioRaz = $InicioRaz + 33;
								}
						}else{
								if($IdentNota != $IdentAnterior){
										$pdf->Cell(16,$AlturaMat,$NotaNumero,1,0,"C",0);
										$pdf->Cell(13,$AlturaMat,$NotaSerie,1,0,"C",0);
								}else{
										$pdf->Cell(16,$AlturaMat,"",1,0,"C",0);
										$pdf->Cell(13,$AlturaMat,"",1,0,"C",0);
								}
								$pdf->Cell(10,5,$MatCodi,1,0,"C",0);
								$pdf->Cell(81,5,$MatDesc,1,0,"L",0);
								$pdf->Cell(8,5,$MatUnid,1,0,"C",0);
								$pdf->Cell(17,5,converte_quant(sprintf("%01.2f",str_replace(",",".",$MatQtdm))),1,0,"C",0);
								$pdf->Cell(17,5,converte_valor_estoques(sprintf("%01.4f",str_replace(",",".",$MatValUnit))),1,0,"C",0);
								$pdf->Cell(30,5,converte_valor_estoques(sprintf("%01.4f",str_replace(",",".",$MatVal))),1,0,"C",0);
								$pdf->Cell(28,5,$DescCnpjCpf,1,0,"C",0);
								$pdf->Cell(60,5,$DescRazao,1,1,"L",0);
						}
						$RazaoAnterior = $Razao;
						$IdentAnterior = $IdentNota;
						$DataAnterior  = $NotaData;
						# Setando 1 na variável $Forn, evita-se escrever os dados do fornecedor #
						$Forn          = 1;
				}

				if($UmaOuMaisDatas){
						# Imprime empenhos relativos a última nota do loop #
						imprimeempenho($Almoxarifado,$NotaAno,$NotaCodi,$db,$pdf);

            ## Separando o valores para os grupo do tipo material e serviço de uma nota fiscal
            if($ValorTotalServicoNotaFiscal > 0){ //Tipo Material Serviço - 'S'
              $pdf->Cell(220,5,"VALOR TOTAL SERVIÇO",1,0,"R",0);
              $pdf->Cell(60,5,converte_valor_estoques(sprintf("%01.4f",str_replace(",",".",$ValorTotalServicoNotaFiscal))),1,1,"R",0);
            }
            if($ValorTotalMatConsumoNotaFiscal > 0){ //Materiais do tipo Consumo - 'C'
              $pdf->Cell(220,5,"VALOR TOTAL MATERIAL CONSUMO",1,0,"R",0);
              $pdf->Cell(60,5,converte_valor_estoques(sprintf("%01.4f",str_replace(",",".",$ValorTotalMatConsumoNotaFiscal))),1,1,"R",0);
            }
            if($ValorTotalMatPermanenteNotaFiscal > 0) {  //Materiais do tipo Permanente - 'P'
              $pdf->Cell(220,5,"VALOR TOTAL MATERIAL PERMANENTE",1,0,"R",0);
              $pdf->Cell(60,5,converte_valor_estoques(sprintf("%01.4f",str_replace(",",".",$ValorTotalMatPermanenteNotaFiscal))),1,1,"R",0);
            }
            ##

            # Imprime o último total de uma nota do loop #
						$pdf->Cell(220,5,"VALOR TOTAL NOTA",1,0,"R",0);
						$pdf->Cell(60,5,converte_valor_estoques(sprintf("%01.4f",str_replace(",",".",$ValorTotalNota))),1,1,"R",0);
						# Imprime a último total do dia do loop #
						$pdf->Cell(220,5,"VALOR TOTAL DIA",1,0,"R",1);
						$pdf->Cell(60,5,converte_valor_estoques(sprintf("%01.4f",str_replace(",",".",$ValorTotalDia))),1,1,"R",0);
				}
				# Imprime o total do relatório #
				$pdf->Ln(5);

        ## Separando o valor total para os grupo do tipo material e serviço no periodo
        if($ValorTotalServicoPeriodo > 0){ //Tipo Material Serviço - 'S'
          $pdf->Cell(220,5,"VALOR TOTAL SERVIÇO NO PERÍODO",1,0,"R",1);
          $pdf->Cell(60,5,converte_valor_estoques(sprintf("%01.4f",str_replace(",",".",$ValorTotalServicoPeriodo))),1,1,"R",0);
        }
        if($ValorTotalMatConsumoPeriodo > 0){ //Materiais do tipo Consumo - 'C'
          $pdf->Cell(220,5,"VALOR TOTAL MATERIAL CONSUMO NO PERÍODO",1,0,"R",1);
          $pdf->Cell(60,5,converte_valor_estoques(sprintf("%01.4f",str_replace(",",".",$ValorTotalMatConsumoPeriodo))),1,1,"R",0);
        }
        if($ValorTotalMatPermanentePeriodo > 0) {  //Materiais do tipo Permanente - 'P'
          $pdf->Cell(220,5,"VALOR TOTAL MATERIAL PERMANENTE NO PERÍODO",1,0,"R",1);
          $pdf->Cell(60,5,converte_valor_estoques(sprintf("%01.4f",str_replace(",",".",$ValorTotalMatPermanentePeriodo))),1,1,"R",0);
        }
        ##

        $pdf->Cell(220,5,"VALOR TOTAL PERÍODO",1,0,"R",1);
				$pdf->Cell(60,5,converte_valor_estoques(sprintf("%01.4f",str_replace(",",".",$ValorTotal))),1,1,"R",0);
		}else{
				$Mensagem = "Nenhuma Ocorrência Encontrada";
				$Url = "RelEntradaNotaFiscal.php?Mensagem=".urlencode($Mensagem)."&Mens=1&Tipo=1";
				if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
				$db->disconnect();
				header("location: ".$Url);
				exit();
		}
}
$db->disconnect();
$pdf->Output();
?>

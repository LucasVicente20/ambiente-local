<?php
#------------------------------------------------------------------------------------
# Portal da DGCO
# Programa:   RelSaldoInicialPdf.php
# Autor:      Álvaro Faria
# Data:       14/07/2006
# Alterado:   Álvaro Faria
# Data:       03/08/2006
# Objetivo:   Relatório que mostra o saldo inicial dos almoxarifados
# OBS.:       Tabulação 2 espaços
#------------------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_cache_limiter('private_no_expire');
session_start();
Seguranca();

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "GET"){
		$Almoxarifado = $_GET['Almoxarifado'];
		$Ano          = $_GET['Ano'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

# Função exibe o Cabeçalho e o Rodapé #
CabecalhoRodape();

# Informa o Título do Relatório #
$TituloRelatorio = "Relatório de Saldo Inicial Almoxarifado";

# Cria o objeto PDF, o Default é formato Retrato, A4  e a medida em milímetros #
$pdf = new PDF("P","mm","A4");

# Define um apelido para o número total de páginas #
$pdf->AliasNbPages();

# Define as cores do preenchimentos que serão usados #
$pdf->SetFillColor(220,220,220);

# Adiciona uma página no documento #
$pdf->AddPage();

# Seta as fontes que serão usadas na impressão de strings #
$pdf->SetFont("Arial","",9);

$pdf->Cell(30,5,"ANO",1,0,"L",1);
$pdf->Cell(160,5,$Ano,1,1,"L",0);
$pdf->ln(5);

$pdf->Cell(150,5,"ALMOXARIFADO",1, 0,"C",1);
$pdf->Cell(40,5,"VALOR ESTOCADO (R$)",1, 1,"C",1);

# Inicia variáveis #
$QuantAlmox  = 0;
$ValorGeralC = 0;
$ValorGeralP = 0;

# Conecta ao banco de dados #
$db = Conexao();

# Busca pelo Almoxarifado especificado, ou todos, trazendo a flag de fechamento, se este almoxarifado fez inventário #
$sql  = " SELECT DISTINCT A.CALMPOCODI, A.EALMPODESC, C.FINVCOFECH ";
$sql .= "   FROM SFPC.TBALMOXARIFADOPORTAL A, SFPC.TBLOCALIZACAOMATERIAL B ";
$sql .= "        LEFT OUTER JOIN SFPC.TBINVENTARIOCONTAGEM C ON (B.CLOCMACODI = C.CLOCMACODI AND C.AINVCOANOB = 2006) ";
$sql .= "  WHERE A.CALMPOCODI = B.CALMPOCODI ";
 if($Almoxarifado != "T") {
		$sql .= " AND B.CALMPOCODI = $Almoxarifado ";
}
$sql .= " ORDER BY A.EALMPODESC ";
$res  = $db->query($sql);
if( PEAR::isError($res) ){
		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nsql: $sql");
}else{
		while($Linha = $res->fetchRow()){
				$Almoxarifado = $Linha[0];
				$AlmoxDesc    = $Linha[1];
				$Fechamento   = $Linha[2];
				# Caso seja um Almoxarifado que não fez fechamento, faz quatro selects simples das entradas e das saídas (consumo / permanente) e subtrai as entradas e saídas #
				if($Fechamento != 'S'){
						# Calcula as entradas Consumo #
						$sqlEC  = " SELECT SUM(A.AMOVMAQTDM*A.VMOVMAVALO) ";
						$sqlEC .= "   FROM SFPC.TBMOVIMENTACAOMATERIAL A,  ";
						$sqlEC .= "        SFPC.TBMATERIALPORTAL B, SFPC.TBSUBCLASSEMATERIAL C, ";
						$sqlEC .= "        SFPC.TBGRUPOMATERIALSERVICO D ";
						$sqlEC .= "  WHERE A.CMATEPSEQU = B.CMATEPSEQU ";
						$sqlEC .= "    AND B.CSUBCLSEQU = C.CSUBCLSEQU ";
						$sqlEC .= "    AND C.CGRUMSCODI = D.CGRUMSCODI ";
						$sqlEC .= "    AND D.FGRUMSTIPM = 'C' ";
						$sqlEC .= "    AND A.CTIPMVCODI IN (1,5,28) ";
						$sqlEC .= "    AND A.CALMPOCODI = $Almoxarifado ";
						$sqlEC .= "    AND (A.FMOVMASITU IS NULL OR A.FMOVMASITU = 'A') "; // Traz só as movimentações ativas
						$resEC  = $db->query($sqlEC);
						if( PEAR::isError($resEC) ){
								ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nsql: $sqlEC");
						}else{
								$LinhaEC = $resEC->fetchRow();
								$ValorEntradasC = $LinhaEC[0];
								# Calcula as entradas Permanente #
								$sqlEP  = " SELECT SUM(A.AMOVMAQTDM*A.VMOVMAVALO) ";
								$sqlEP .= "   FROM SFPC.TBMOVIMENTACAOMATERIAL A,  ";
								$sqlEP .= "        SFPC.TBMATERIALPORTAL B, SFPC.TBSUBCLASSEMATERIAL C, ";
								$sqlEP .= "        SFPC.TBGRUPOMATERIALSERVICO D ";
								$sqlEP .= "  WHERE A.CMATEPSEQU = B.CMATEPSEQU ";
								$sqlEP .= "    AND B.CSUBCLSEQU = C.CSUBCLSEQU ";
								$sqlEP .= "    AND C.CGRUMSCODI = D.CGRUMSCODI ";
								$sqlEP .= "    AND D.FGRUMSTIPM = 'P' ";
								$sqlEP .= "    AND A.CTIPMVCODI IN (1,5,28) ";
								$sqlEP .= "    AND A.CALMPOCODI = $Almoxarifado ";
								$sqlEP .= "    AND (A.FMOVMASITU IS NULL OR A.FMOVMASITU = 'A') "; // Traz só as movimentações ativas
								$resEP  = $db->query($sqlEP);
								if( PEAR::isError($resEP) ){
										ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nsql: $sqlEP");
								}else{
										$LinhaEP = $resEP->fetchRow();
										$ValorEntradasP = $LinhaEP[0];
										# Calcula as saídas Consumo #
										$sqlSC  = " SELECT SUM(A.AMOVMAQTDM*A.VMOVMAVALO) ";
										$sqlSC .= "   FROM SFPC.TBMOVIMENTACAOMATERIAL A,  ";
										$sqlSC .= "        SFPC.TBMATERIALPORTAL B, SFPC.TBSUBCLASSEMATERIAL C, ";
										$sqlSC .= "        SFPC.TBGRUPOMATERIALSERVICO D ";
										$sqlSC .= "  WHERE A.CMATEPSEQU = B.CMATEPSEQU ";
										$sqlSC .= "    AND B.CSUBCLSEQU = C.CSUBCLSEQU ";
										$sqlSC .= "    AND C.CGRUMSCODI = D.CGRUMSCODI ";
										$sqlSC .= "    AND D.FGRUMSTIPM = 'C' ";
										$sqlSC .= "    AND A.CTIPMVCODI = 25 ";
										$sqlSC .= "    AND A.CALMPOCODI = $Almoxarifado ";
										$sqlSC .= "    AND (A.FMOVMASITU IS NULL OR A.FMOVMASITU = 'A') "; // Traz só as movimentações ativas
										$resSC  = $db->query($sqlSC);
										if( PEAR::isError($resSC) ){
												ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nsql: $sqlSC");
										}else{
												$LinhaSC = $resSC->fetchRow();
												$ValorSaidasC = $LinhaSC[0]; 
												# Calcula as saídas Permanente #
												$sqlSP  = " SELECT SUM(A.AMOVMAQTDM*A.VMOVMAVALO) ";
												$sqlSP .= "   FROM SFPC.TBMOVIMENTACAOMATERIAL A,  ";
												$sqlSP .= "        SFPC.TBMATERIALPORTAL B, SFPC.TBSUBCLASSEMATERIAL C, ";
												$sqlSP .= "        SFPC.TBGRUPOMATERIALSERVICO D ";
												$sqlSP .= "  WHERE A.CMATEPSEQU = B.CMATEPSEQU ";
												$sqlSP .= "    AND B.CSUBCLSEQU = C.CSUBCLSEQU ";
												$sqlSP .= "    AND C.CGRUMSCODI = D.CGRUMSCODI ";
												$sqlSP .= "    AND D.FGRUMSTIPM = 'P' ";
												$sqlSP .= "    AND A.CTIPMVCODI = 25 ";
												$sqlSP .= "    AND A.CALMPOCODI = $Almoxarifado ";
												$sqlSP .= "    AND (A.FMOVMASITU IS NULL OR A.FMOVMASITU = 'A') "; // Traz só as movimentações ativas
												$resSP  = $db->query($sqlSP);
												if( PEAR::isError($resSP) ){
														ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nsql: $sqlSP");
												}else{
														$LinhaSP = $resSP->fetchRow();
														$ValorSaidasP = $LinhaSP[0];
														# Calcula totais #
														$TotalAlmoxC = $ValorEntradasC - $ValorSaidasC;
														$TotalAlmoxP = $ValorEntradasP - $ValorSaidasP;
														# Imprime o Total (Cálculo simples) para este Almoxarifado #
														$AlmoxDescPDF = strtoupper2(trim($AlmoxDesc));
														$TotalAlmox   = $TotalAlmoxC + $TotalAlmoxP;
														$TotalPDF     = converte_valor_estoques(sprintf("%01.4f",str_replace(",",".",$TotalAlmox)));
														$TotalPDFC    = converte_valor_estoques(sprintf("%01.4f",str_replace(",",".",$TotalAlmoxC)));
														$TotalPDFP    = converte_valor_estoques(sprintf("%01.4f",str_replace(",",".",$TotalAlmoxP)));
														$QuantAlmox = $QuantAlmox + 1;
														$ValorGeralC = $ValorGeralC + $TotalAlmoxC;
														$ValorGeralP = $ValorGeralP + $TotalAlmoxP;
														$pdf->Cell(150,5,$AlmoxDescPDF,1, 0,"L",0);
														$pdf->Cell(40,5,$TotalPDF,1, 1,"R",0);
														$pdf->Cell(150,5,"CONSUMO",1, 0,"R",0);
														$pdf->Cell(40,5,$TotalPDFC,1, 1,"R",0);
														$pdf->Cell(150,5,"PERMANENTE",1, 0,"R",0);
														$pdf->Cell(40,5,$TotalPDFP,1, 1,"R",0);
												}
										}
								}
						}
				# Se não, o almoxarifado fez fechamento de inventário, passa para o cálculo complexo #
				}else{
						# Inicializa/Zera variável #
						$TotalAlmox  = 0;
						$TotalAlmoxC = 0;
						$TotalAlmoxP = 0;
						$sqlC  = " SELECT A.CMATEPSEQU, A.CTIPMVCODI, A.VMOVMAVALO, A.AMOVMAQTDM, D.FGRUMSTIPM ";
						$sqlC .= "   FROM SFPC.TBMOVIMENTACAOMATERIAL A, ";
						$sqlC .= "        SFPC.TBMATERIALPORTAL B, SFPC.TBSUBCLASSEMATERIAL C, ";
						$sqlC .= "        SFPC.TBGRUPOMATERIALSERVICO D ";
						$sqlC .= "  WHERE A.CMATEPSEQU = B.CMATEPSEQU ";
						$sqlC .= "    AND B.CSUBCLSEQU = C.CSUBCLSEQU ";
						$sqlC .= "    AND C.CGRUMSCODI = D.CGRUMSCODI ";
						$sqlC .= "    AND A.CTIPMVCODI IN (1,5,28, 25) ";
						$sqlC .= "    AND A.CALMPOCODI = $Almoxarifado ";
						$sqlC .= "    AND (A.FMOVMASITU IS NULL OR A.FMOVMASITU = 'A') ";
						$sqlC .= " ORDER BY A.CMATEPSEQU, A.CTIPMVCODI, A.TMOVMAULAT ";
						$resC  = $db->query($sqlC);
						if( PEAR::isError($resC) ){
								ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nsql: $sqlC");
						}else{
								while($LinhaC = $resC->fetchRow()){
										$Material = $LinhaC[0];
										$TipoMov  = $LinhaC[1];
										$ValorMov = $LinhaC[2];
										$QtdmMov  = $LinhaC[3];
										$TipoMat  = $LinhaC[4];
										if($MaterialChecado != $Material){
												# Soma total do material checado ao total do Almoxarifado dependendo do tipo Consumo/Permanente #
												if($TipoMatChecado == 'P'){
														$TotalAlmoxP = $TotalAlmoxP + $TotalMaterial;
												}elseif($TipoMatChecado == 'C'){
														$TotalAlmoxC = $TotalAlmoxC + $TotalMaterial;
												}
												$MaterialChecado = $Material;
												$TipoMatChecado  = $TipoMat;
												# Zera os dados do Material anterior #
												$TotalMaterial = 0;
												$Zerado        = null;
												# Calcula a primeira entrada do próximo Material #
												# Contabiliza positivamente ou negativamente, dependendo do tipo da movimentação #
												if($TipoMov == 5){
														# Descobre o valor do material através da carga inicial #
														$sqlV  = " SELECT VMOVMAVALO ";
														$sqlV .= "   FROM SFPC.TBMOVIMENTACAOMATERIAL ";
														$sqlV .= "  WHERE CTIPMVCODI = 1 ";
														$sqlV .= "    AND CMATEPSEQU = $Material ";
														$sqlV .= "    AND CALMPOCODI = $Almoxarifado ";
														$sqlV .= "    AND AMOVMAANOM = 2006 ";
														$sqlV .= "    AND (FMOVMASITU IS NULL OR FMOVMASITU = 'A') ";
														$sqlV .= " ORDER BY TMOVMAULAT DESC ";
														$resV  = $db->query($sqlV);
														if( PEAR::isError($resV) ){
																ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nsql: $sqlV");
														}else{
																$LinhaV = $resV->fetchRow();
																$ValorMov = $LinhaV[0];
														}
														$TotalMaterial = $TotalMaterial + ($ValorMov*$QtdmMov);
												}elseif($TipoMov == 1 or $TipoMov == 28){
														$TotalMaterial = $TotalMaterial + ($ValorMov*$QtdmMov);
												}elseif($TipoMov == 25){
														$TotalMaterial = $TotalMaterial - ($ValorMov*$QtdmMov);
												}
										}else{
												# Contabiliza positivamente ou negativamente, dependendo do tipo da movimentação #
												if($TipoMov == 5){
														if(!$Zerado){
																# Caso seja a primeira execução neste material do tipo inventário, zera o total #
																$TotalMaterial = 0;
																$Zerado = 1;
														}
														# Descobre o valor do material através da carga inicial #
														$sqlV  = " SELECT VMOVMAVALO ";
														$sqlV .= "   FROM SFPC.TBMOVIMENTACAOMATERIAL ";
														$sqlV .= "  WHERE CTIPMVCODI = 1 ";
														$sqlV .= "    AND CMATEPSEQU = $Material ";
														$sqlV .= "    AND CALMPOCODI = $Almoxarifado ";
														$sqlV .= "    AND (FMOVMASITU IS NULL OR FMOVMASITU = 'A') ";
														$sqlV .= " ORDER BY TMOVMAULAT DESC ";
														$resV  = $db->query($sqlV);
														if( PEAR::isError($resV) ){
																ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nsql: $sqlV");
														}else{
																$LinhaV = $resV->fetchRow();
																$ValorMov = $LinhaV[0];
														}
														$TotalMaterial = $TotalMaterial + ($ValorMov*$QtdmMov);
												}elseif($TipoMov == 1 or $TipoMov == 28){
														$TotalMaterial = $TotalMaterial + ($ValorMov*$QtdmMov);
												}elseif($TipoMov == 25){
														$TotalMaterial = $TotalMaterial - ($ValorMov*$QtdmMov);
												}
										}
								}
								# Soma total do último material checado ao total do Almoxarifado #
								if($TipoMat == 'P'){
										$TotalAlmoxP = $TotalAlmoxP + $TotalMaterial;
								}elseif($TipoMatChecado == 'C'){
										$TotalAlmoxC = $TotalAlmoxC + $TotalMaterial;
								}
								
								# Imprime o Total (Cálculo complexo) para este almoxarifado #
								$AlmoxDescPDF = strtoupper2(trim($AlmoxDesc));
								$TotalAlmox   = $TotalAlmoxC + $TotalAlmoxP;
								$TotalPDF     = converte_valor_estoques(sprintf("%01.4f",str_replace(",",".",$TotalAlmox)));
								$TotalPDFC    = converte_valor_estoques(sprintf("%01.4f",str_replace(",",".",$TotalAlmoxC)));
								$TotalPDFP    = converte_valor_estoques(sprintf("%01.4f",str_replace(",",".",$TotalAlmoxP)));
								$QuantAlmox = $QuantAlmox + 1;
								$ValorGeralC = $ValorGeralC + $TotalAlmoxC;
								$ValorGeralP = $ValorGeralP + $TotalAlmoxP;
								$pdf->Cell(150,5,$AlmoxDescPDF,1, 0,"L",0);
								$pdf->Cell(40,5,$TotalPDF,1, 1,"R",0);
								$pdf->Cell(150,5,"CONSUMO",1, 0,"R",0);
								$pdf->Cell(40,5,$TotalPDFC,1, 1,"R",0);
								$pdf->Cell(150,5,"PERMANENTE",1, 0,"R",0);
								$pdf->Cell(40,5,$TotalPDFP,1, 1,"R",0);
								
								$TotalMaterial = 0;
								$ValorEntradas = 0;
								$ValorSaidas   = 0;
						}
				}
		}
}
# Imprime resumo #
$pdf->Cell(150,5,"TOTAL DE ALMOXARIFADOS LISTADOS",1,0,"R",1);
$pdf->Cell(40,5,$QuantAlmox,1,1,"R",0);

$pdf->Cell(150,5,"TOTAL CONSUMO",1,0,"R",1);
//$pdf->Cell(14,5,"TOTAL CONSUMO",1,0,"R",1);
$ValorGeralPDFC = converte_valor_estoques(sprintf("%01.4f",str_replace(",",".",$ValorGeralC)));
$pdf->Cell(40,5,$ValorGeralPDFC,1,1,"R",0);

$pdf->Cell(150,5,"TOTAL PERMANENTE",1,0,"R",1);
//$pdf->Cell(14,5,"TOTAL PERMANENTE",1,0,"R",1);
$ValorGeralPDFP = converte_valor_estoques(sprintf("%01.4f",str_replace(",",".",$ValorGeralP)));
$pdf->Cell(40,5,$ValorGeralPDFP,1,1,"R",0);

$pdf->Cell(150,5,"TOTAL GERAL",1,0,"R",1);
$ValorGeral = $ValorGeralC + $ValorGeralP;
$ValorGeralPDF = converte_valor_estoques(sprintf("%01.4f",str_replace(",",".",$ValorGeral)));
$pdf->Cell(40,5,$ValorGeralPDF,1,1,"R",0);

$pdf->ln(5);

$db->disconnect();
$pdf->Output();
?>

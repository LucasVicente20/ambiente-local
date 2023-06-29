<?php
#------------------------------------------------------------------------------------
# Portal da DGCO
# Programa: RelNotaFiscalPdf.php
# Autor:    Filipe Cavalcanti
# Data:     02/09/2005
# Alterado: Álvaro Faria
# Data:     01/08/2006 - Exibição de múltiplos empenhos
# Alterado: Álvaro Faria
# Data:     24/08/2006 - Máximo de 16 empenhos
# Objetivo: Programa de Impressão do Espelho da Fota Fiscal
# OBS.:     Tabulação 2 espaços
#------------------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_cache_limiter('private_no_expire');
session_start();
Seguranca();

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "GET"){
		$NotaFiscal     = $_GET['NotaFiscal'];
		$AnoNota        = $_GET['AnoNota'];
		$Almoxarifado   = $_GET['Almoxarifado'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

# Função exibe o Cabeçalho e o Rodapé #
CabecalhoRodapePaisagem();

# Informa o Título do Relatório #
$TituloRelatorio = "Espelho da Nota Fiscal";

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

# Pega os dados para exibição #

if($Botao == ""){
		# Pega os dados da Entrada por NF de acordo com o Sequencial #
		$db   = Conexao();
		$sql  = "SELECT A.AENTNFNOTA, A.AENTNFSERI, A.DENTNFENTR, ";
		$sql .= "       A.DENTNFEMIS, A.VENTNFTOTA, ";
		$sql .= "       B.AITENFQTDE, B.VITENFUNIT, ";
		$sql .= "       C.CMATEPSEQU, C.EMATEPDESC, D.EUNIDMSIGL,  ";
		$sql .= "       A.AFORCRSEQU, A.CFORESCODI, A.FENTNFCANC ";
		$sql .= "  FROM SFPC.TBENTRADANOTAFISCAL A, SFPC.TBITEMNOTAFISCAL B, SFPC.TBMATERIALPORTAL C, ";
		$sql .= "       SFPC.TBUNIDADEDEMEDIDA D ";
		$sql .= " WHERE A.CENTNFCODI = B.CENTNFCODI AND B.CMATEPSEQU = C.CMATEPSEQU ";
		$sql .= "   AND A.CALMPOCODI = B.CALMPOCODI AND A.CENTNFCODI = B.CENTNFCODI ";
		$sql .= "   AND A.AENTNFANOE = B.AENTNFANOE AND A.CALMPOCODI = $Almoxarifado ";
		$sql .= "   AND A.CENTNFCODI = $NotaFiscal AND A.AENTNFANOE = $AnoNota ";
		$sql .= "   AND C.CUNIDMCODI = D.CUNIDMCODI ";
		$sql .= " ORDER BY A.AENTNFNOTA, C.EMATEPDESC ";
		$res  = $db->query($sql);
		if( db::isError($res) ){
				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
				$Rows = $res->numRows();
				for( $i=0;$i<$Rows;$i++ ){
						$Linha            = $res->fetchRow();
						$NumeroNota       = $Linha[0];
						$SerieNota        = $Linha[1];
						$DataEntrada      = DataBarra($Linha[2]);
						$DataEmissao      = DataBarra($Linha[3]);
						$ValorNota        = str_replace(",",".",$Linha[4]);
						$QtdItem[$i]      = str_replace(",",".",$Linha[5]);
						$ValorItem[$i]    = str_replace(",",".",$Linha[6]);
						$Material[$i]     = $Linha[7];
						$DescMaterial[$i] = $Linha[8];
						$DescUnidade[$i]  = $Linha[9];
						$FornecedorSequ 	= $Linha[10];
						$FornecedorCodi 	= $Linha[11];
						$Situacao       	= $Linha[12];
				}
		}
}
if( $Situacao == "S" ){
		$pdf->Cell(280,5,"NOTA FISCAL CANCELADA",1,0,"C",1);
}

if( $FornecedorSequ != "" ){
		# Verifica se o Fornecedor de Estoque é Credenciado #
		$sqlforn  = "SELECT NFORCRRAZS,AFORCRCCGC,AFORCRCCPF FROM SFPC.TBFORNECEDORCREDENCIADO ";
		$sqlforn .= " WHERE AFORCRSEQU = '$FornecedorSequ' ";
		$resforn  = $db->query($sqlforn);
		if( db::isError($resforn) ){
				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlforn");
		}else{
				$Linhaforn  = $resforn->fetchRow();
				$Razao = $Linhaforn[0];
				$CNPJ  = $Linhaforn[1];
				$CPF   = $Linhaforn[2];
		}
}else{
		# Verifica se o Fornecedor de Estoque já está cadastrado #
		$sqlforn  = "SELECT EFORESRAZS,AFORESCCGC,AFORESCCPF FROM SFPC.TBFORNECEDORESTOQUE ";
		$sqlforn .= "	WHERE CFORESCODI = '$FornecedorCodi' ";
		$resforn  = $db->query($sqlforn);
		if( db::isError($resforn) ){
				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlforn");
		}else{
				$Linhaforn  = $resforn->fetchRow();
				$Razao = $Linhaforn[0];
				$CNPJ  = $Linhaforn[1];
				$CPF   = $Linhaforn[2];
		}
}
$pdf->ln(5);

# Pega as informações do Almoxarifado #
$sqlalmo = "SELECT EALMPODESC FROM SFPC.TBALMOXARIFADOPORTAL WHERE CALMPOCODI = $Almoxarifado";
$resalmo = $db->query($sqlalmo);
if( db::isError($resalmo) ){
		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlalmo");
}else{
	$Almox     = $resalmo->fetchRow();
	$DescAlmox = $Almox[0];
	$pdf->Cell(28,5,"ALMOXARIFADO",1,0,"L",1);
	$pdf->Cell(252,5,$DescAlmox,1,1,"L",0);
}

# Escreve o Número #
$pdf->Cell(28,5,"NÚMERO",1,0,"L",1);
$pdf->Cell(45,5,$NumeroNota."/".$AnoNota,1,0,"L",0);
$pdf->Cell(26,5,"SÉRIE",1,0,"L",1);
$pdf->Cell(33,5,$SerieNota,1,0,"L",0);
$pdf->Cell(37,5,"DATA DE EMISSÃO",1,0,"L",1);
$pdf->Cell(34,5,$DataEmissao,1,0,"L",0);
$pdf->Cell(37,5,"DATA DE ENTRADA",1,0,"L",1);
$pdf->Cell(40,5,$DataEntrada,1,1,"L",0);
if($CPF){
		$CPF = FormataCPF($CPF);
		$pdf->Cell(40,5,"CPF DO FORNECEDOR",1,0,"L",1);
		$pdf->Cell(33,5,$CPF,1,0,"L",0);
}else{
		$CNPJ = FormataCNPJ($CNPJ);
		$pdf->Cell(40,5,"CNPJ DO FORNECEDOR",1,0,"L",1);
		$pdf->Cell(33,5,$CNPJ,1,0,"L",0);
}
if($CPF){
		$pdf->Cell(26,5,"NOME",1,0,"L",1);
}else{
		$pdf->Cell(26,5,"RAZÃO SOCIAL",1,0,"L",1);
}
$pdf->Cell(181,5,substr($Razao,0,92),1,1,"L",0);

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
				$pdf->Cell(40,5,"Nº(S) EMPENHO(S)","LRT",0,"L",1);
				$pdf->Cell(240,5,$DescEmpenho1,1,1,"L",0);
				$pdf->Cell(40,5," ","LRB",0,"L",1);
				$pdf->Cell(240,5,$DescEmpenho2."...",1,1,"L",0);
		}else{
				$pdf->Cell(40,5,"Nº(S) EMPENHO(S)","LRT",0,"L",1);
				$pdf->Cell(240,5,$DescEmpenho1,1,1,"L",0);
				$pdf->Cell(40,5," ","LRB",0,"L",1);
				$pdf->Cell(240,5,$DescEmpenho2,1,1,"L",0);
		}
}else{
		$pdf->Cell(40,5,"Nº(S) EMPENHO(S)",1,0,"L",1);
		$pdf->Cell(240,5,$DescEmpenho1,1,1,"L",0);
}
$pdf->ln(8);

# Linhas de Itens de Material #
$pdf->Cell(187,5,"DESCRIÇÃO DO ITEM",1,0,"L",1);
$pdf->Cell(20,5,"UNIDADE",1, 0,"C",1);
$pdf->Cell(25,5,"QUANTIDADE",1,0,"C",1);
$pdf->Cell(24,5,"VALOR UNIT",1,0,"C",1);
$pdf->Cell(24,5,"VALOR TOTAL",1,1,"C",1);

$rows = $res->numRows();
for( $i=0; $i< $rows; $i++ ){
		$Linha = $res->fetchRow();
		# Quebra de Linha para Descrição do Material #
		$DescMaterialSepara = SeparaFrase($DescMaterial[$i],79);
		$TamDescMaterial    = $pdf->GetStringWidth($DescMaterialSepara);
		if( $TamDescMaterial <= 148 ){
				$LinhasMat = 1;
				$AlturaMat = 5;
		}elseif( $TamDescMaterial > 148 and $TamDescMaterial <= 296 ){
				$LinhasMat = 2;
				$AlturaMat = 10;
		}elseif( $TamDescMaterial > 296 and $TamDescMaterial <= 444 ){
				$LinhasMat = 3;
				$AlturaMat = 15;
		}else{
				$LinhasMat = 4;
				$AlturaMat = 20;
		}
		if( $TamDescMaterial > 147 ){
				$Inicio = 0;
				$pdf->Cell(187,$AlturaMat,"",1,0,"L",0);
				for( $Quebra = 0; $Quebra < $LinhasMat; $Quebra++ ){
						if( $Quebra == 0 ){
								$pdf->SetX(10);
								$pdf->Cell(187,5,trim(substr($DescMaterialSepara,$Inicio,79)),0,0,"L",0);
								$pdf->Cell(20,$AlturaMat,$DescUnidade[$i],1,0,"C",0);
								$pdf->Cell(25,$AlturaMat,converte_quant(sprintf("%01.2f",str_replace(",",".",$QtdItem[$i]))),1,0,"R",0);
								$pdf->Cell(24,$AlturaMat,converte_valor_estoques(sprintf("%01.4f",str_replace(",",".",$ValorItem[$i]))),1,0,"R",0);
								$ValorTotal = $QtdItem[$i] * $ValorItem[$i];
								$ValorTotal = converte_valor_estoques(sprintf("%01.4f",str_replace(",",".",$ValorTotal)));
								$pdf->Cell(24,$AlturaMat,$ValorTotal,1,0,"R",0);
								$pdf->Ln(5);
						}elseif( $Quebra == 1 ){
								$pdf->Cell(187,5,trim(substr($DescMaterialSepara,$Inicio,79)),0,0,"L",0);
								$pdf->Ln(5);
						}elseif( $Quebra == 2 ){
								$pdf->Cell(187,5,trim(substr($DescMaterialSepara,$Inicio,79)),0,0,"L",0);
								$pdf->Ln(5);
						}elseif( $Quebra == 3 ){
								$pdf->Cell(187,5,trim(substr($DescMaterialSepara,$Inicio,79)),0,0,"L",0);
								$pdf->Ln(5);
						}else{
								$pdf->Cell(181,5,trim(substr($DescMaterialSepara,$Inicio,79)),0,0,"L",0);
								$pdf->Ln(5);
						}
						$Inicio = $Inicio + 79;
				}
		}else{
				$pdf->Cell(187,5,$DescMaterial[$i], 1,0, "L",0);
				$pdf->Cell(20,5,$DescUnidade[$i],1,0,"C",0);
				$pdf->Cell(25,5,converte_quant(sprintf("%01.2f",str_replace(",",".",$QtdItem[$i]))),1,0,"R",0);
				$pdf->Cell(24,5,converte_valor_estoques(sprintf("%01.4f",str_replace(",",".",$ValorItem[$i]))),1,0,"R",0);
				$ValorTotal = $QtdItem[$i] * $ValorItem[$i];
				$ValorTotal = converte_valor_estoques(sprintf("%01.4f",str_replace(",",".",$ValorTotal)));
				$pdf->Cell(24,5,$ValorTotal,1,1,"R",0);
		}
}

# Total da Nota #
$pdf->Cell(256,5,"TOTAL",1,0,"R",1);
$pdf->Cell(24,5,converte_valor_estoques(sprintf("%01.4f",str_replace(",",".",$ValorNota))),1,0,"R",0);

$db->disconnect();
$pdf->Output();
?>

<?php
#------------------------------------------------------------------------------------
# Portal da DGCO
# Programa: RelTeste.php
# Autor:    Ávaro Faria
# Data:     17/03/2006
# Alterado: Ávaro Faria
# Data:     07/06/2006
# Alterado: Ávaro Faria
# Data:     12/09/2006 - Indentaç / Padronizaç do cabeçho
# Alterado: Ávaro Faria
# Data:     06/11/2006 - Desativaç do envio do e-mail quando da utilizaç deste relató
# Alterado: Carlos Abreu
# Data:     01/10/2007 - Ajustes na query e programacao para evitar estouro de memoria na execuç
# Alterado: Rossana Lira/ Rodrigo Melo
# Data:     10/12/2007 - Ajustes no programa para exibir o ultimo valor unitáo méo do perío informado
# Objetivo: Programa de Impressãdos itens em estoque em uma determinada data
# OBS.:     Tabulaç 2 espaç
#------------------------------------------------------------------------------------

# Acesso ao arquivo de funçs #
include "../funcoes.php";

ini_set(max_execution_time, '600');
session_cache_limiter('private_no_expire');

# Executa o controle de seguranç#
session_start();
//Seguranca();

# Adiciona pánas no MenuAcesso #
AddMenuAcesso( '/estoques/RelContagemInventario.php' );

# Envia e-mail indicando uso do programa para resoluç de problema de desempenho do banco de dados $
// $Assunto = "O Relató de Item de Estoque por Data foi utilizado";
// $Texto   = "Grupo: ".$_SESSION['_cgrempcodi_'].", Usuáo: ".$_SESSION['_eusupologi_'];
// EnviaEmail($Mail, $Assunto, $Texto, $From);

# Variáis com o global off #
if($_SERVER['REQUEST_METHOD'] == "GET"){
		$Almoxarifado = $_GET['Almoxarifado'];
		$Ordem        = $_GET['Ordem'];
		$ExibirLoc    = $_GET['ExibirLoc'];
		$ExibirZer    = $_GET['ExibirZer'];
		$Data         = $_GET['Data'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

# Funç exibe o Cabeçho e o Rodapé
CabecalhoRodapePaisagem();

# Informa o Tílo do Relató #
if($Ordem == "1"){
		$TituloRelatorio = "Relató de Itens de Material em Estoque em ".$Data." (Ordem: Famía";
}else{
		$TituloRelatorio = "Relató de Itens de Material em Estoque em ".$Data." (Ordem: Material";
}
if($ExibirZer == 'N') $TituloRelatorio .= ", Opç: Nãexibir itens zerados";
elseif($ExibirZer == 'S') $TituloRelatorio .= ", Opç: Exibir itens zerados";
$TituloRelatorio .= ")";

# Cria o objeto PDF, o Default éormato Retrato, A4  e a medida em milítros #
$pdf = new PDF("L","mm","A4");

# Define um apelido para o nú total de pánas #
$pdf->AliasNbPages();

# Define as cores do preenchimentos que serãusados #
$pdf->SetFillColor(220,220,220);

# Adiciona uma pána no documento #
$pdf->AddPage();

# Seta as fontes que serãusadas na impressãde strings #
$pdf->SetFont("Arial","",9);

# Pega os dados para exibiç #
$db = Conexao();

# Converte a data para o formato de pesquisa no banco de dados
$DataPesquisa = DataInvertida($Data);
# Resgata os itens do estoque #

$sql  = "SELECT * FROM ( "; // PREPARA RESULTADO 

$sql .= " SELECT MAT.CMATEPSEQU, MAT.EMATEPDESC, UNI.EUNIDMSIGL, ARM.AARMATQTDE, ";
$sql .= "        LOC.FLOCMAEQUI, LOC.ALOCMANEQU, LOC.ALOCMAPRAT, ";
$sql .= "        LOC.ALOCMACOLU, LOC.CARLOCCODI, ";
$sql .= "        AARMATQTDE + SUM(CASE WHEN FTIPMVTIPO = 'S' THEN AMOVMAQTDM ELSE CASE WHEN FTIPMVTIPO = 'E' THEN -AMOVMAQTDM ELSE 0 END END ) AS QTD ";
if($Ordem == 1){
		$sql .= "       , GRU.EGRUMSDESC, CLA.ECLAMSDESC ";
}
$sql .= "   FROM SFPC.TBMATERIALPORTAL MAT, SFPC.TBUNIDADEDEMEDIDA UNI, ";
$sql .= "        SFPC.TBLOCALIZACAOMATERIAL LOC, SFPC.TBARMAZENAMENTOMATERIAL ARM ";
$sql .= "   LEFT OUTER JOIN SFPC.TBMOVIMENTACAOMATERIAL MOV ";
$sql .= "     ON ARM.CMATEPSEQU = MOV.CMATEPSEQU ";
$sql .= "    AND MOV.CALMPOCODI = $Almoxarifado ";
$sql .= "    AND MOV.TMOVMAULAT > '$DataPesquisa 23:59:59' ";
$sql .= "    AND (MOV.FMOVMASITU IS NULL OR MOV.FMOVMASITU = 'A') ";
$sql .= "   LEFT OUTER JOIN SFPC.TBTIPOMOVIMENTACAO TIP ";
$sql .= "     ON MOV.CTIPMVCODI = TIP.CTIPMVCODI ";
if($Ordem == 1){
		$sql .= "       , SFPC.TBGRUPOMATERIALSERVICO GRU, SFPC.TBCLASSEMATERIALSERVICO CLA, SFPC.TBSUBCLASSEMATERIAL SUB ";
}
$sql .= "  WHERE MAT.CUNIDMCODI = UNI.CUNIDMCODI AND MAT.CMATEPSEQU = ARM.CMATEPSEQU ";
$sql .= "    AND LOC.CLOCMACODI = ARM.CLOCMACODI AND LOC.CALMPOCODI = $Almoxarifado ";
if($Ordem == 1){
		$sql .= "   AND GRU.CGRUMSCODI = CLA.CGRUMSCODI AND CLA.CCLAMSCODI = SUB.CCLAMSCODI ";
		$sql .= "   AND CLA.CGRUMSCODI = SUB.CGRUMSCODI AND SUB.CSUBCLSEQU = SUB.CSUBCLSEQU ";
		$sql .= "   AND SUB.CSUBCLSEQU = MAT.CSUBCLSEQU ";
}

$sql .= " GROUP BY MAT.CMATEPSEQU, MAT.EMATEPDESC, UNI.EUNIDMSIGL, ARM.VARMATUMED, LOC.FLOCMAEQUI, ";
$sql .= "          LOC.ALOCMANEQU, LOC.ALOCMAPRAT, LOC.ALOCMACOLU, LOC.CARLOCCODI, AARMATQTDE ";
if($Ordem == 1){
		$sql .= "       , GRU.EGRUMSDESC, CLA.ECLAMSDESC ";
}

$sql .= ") AS RESULTADO ";

if ($ExibirZer == 'N'){
	$sql .= " WHERE QTD<>0 ";
}
if($Ordem == 1){
	$sql .= " ORDER BY EGRUMSDESC, ECLAMSDESC, EMATEPDESC ";
} else {
	$sql .= " ORDER BY EMATEPDESC ";
}

$res  = $db->query($sql);
if( db::isError($res) ){
		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nsql: $sql");
}else{
		# Pega as informaçs do Almoxarifado #
		$sqlalmo = "SELECT EALMPODESC FROM SFPC.TBALMOXARIFADOPORTAL WHERE CALMPOCODI = $Almoxarifado";
		$resalmo = $db->query($sqlalmo);
		if( db::isError($resalmo) ){
				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nsql: $sqlalmo");
		}else{
				$Almox     = $resalmo->fetchRow();
				$DescAlmox = $Almox[0];
				$pdf->Cell(30,5,"ALMOXARIFADO",1,0,"L",1);
				$pdf->Cell(250,5,$DescAlmox,1,0,"L",0);
				$pdf->ln(8);
		}

		# Linhas de Itens de Material #
		$rows = $res->numRows();
		if($rows == 0){
				$Mensagem = "Nenhuma Ocorrêia Encontrada";
				$Url = "RelContagemInventario.php?Mensagem=".urlencode($Mensagem)."&Mens=1&Tipo=1";
				if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
				header("location: ".$Url);
				exit;
		}else{
				$DescGrupoAntes  = "";
				$DescCalsseAntes = "";
				
				for($i=0; $i < $rows; $i++){
						$Linha = $res->fetchRow();
						$CodigoReduzido = $Linha[0];
						$DescMaterial   = $Linha[1];
						$Unidade        = $Linha[2];
						$QtdEstoque     = $Linha[9];
						$Equipamento    = $Linha[4];
						$NumEquipamento = $Linha[5];
						$Prateleira     = $Linha[6];
						$Coluna         = $Linha[7];
						$Area           = $Linha[8];
						if($Ordem == 1){
								$DescGrupo      = $Linha[10];
								$DescClasse     = $Linha[11];
						}
						$Localizacao    = "A:". $Area." E:".$Equipamento.$NumEquipamento."/ESC:".$Prateleira.$Coluna;

						# Descobre o valor unitáo para o material na data especificada #
						$sqlvalor  = " SELECT VMOVMAUMED FROM SFPC.TBMOVIMENTACAOMATERIAL ";
						$sqlvalor .= "  WHERE CALMPOCODI = $Almoxarifado ";
						$sqlvalor .= "    AND CMATEPSEQU = ".$CodigoReduzido." ";
						$sqlvalor .= "    AND (FMOVMASITU IS NULL OR FMOVMASITU = 'A') ";
						$sqlvalor .= "    AND TMOVMAULAT = ";
						$sqlvalor .= "        (SELECT MAX(TMOVMAULAT) FROM SFPC.TBMOVIMENTACAOMATERIAL ";
						$sqlvalor .= "          WHERE CALMPOCODI = $Almoxarifado ";
						$sqlvalor .= "            AND CMATEPSEQU = ".$CodigoReduzido." ";
						$sqlvalor .= "            AND TMOVMAULAT <= '$DataPesquisa 23:59:59' ";
						$sqlvalor .= "            AND (FMOVMASITU IS NULL OR FMOVMASITU = 'A') )";
						$sqlvalor .= " ORDER BY AMOVMAANOM DESC, CMOVMACODI DESC";
						$resvalor    = $db->query($sqlvalor);
						if( db::isError($resvalor) ){
								ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlvalor");
						}else{
								$LinhaValor              = $resvalor->fetchRow();
								$ValorUnitarioPesquisado = $LinhaValor[0];
						}

						$QtdTotal        = $QtdTotal + $QtdEstoque;
						$Totalizador     = $Totalizador + ($QtdEstoque * $ValorUnitarioPesquisado);

						if( $Ordem == 1 ){
								if( $DescGrupoAntes != $DescGrupo or ( $DescGrupoAntes == $DescGrupo and $DescClasseAntes != $DescClasse ) ){
										$pdf->Cell(30,5,"GRUPO / CLASSE",1,0,"L",1);
										$pdf->Cell(250,5,$DescGrupo." / ".$DescClasse,1,1,"L",0);
										if($ExibirLoc == "S"){
												$pdf->Cell(152,5,"DESCRIÇO DO ITEM",1,0,"L",1);
												$pdf->Cell(9,5,"UND",1, 0,"C",1);
												$pdf->Cell(17,5,"CÓ RED",1,0,"C",1);
												$pdf->Cell(25,5,"QTD ESTOQUE",1,0,"C",1);
												$pdf->Cell(22,5,"VALOR UNIT",1,0,"C",1);
												$pdf->Cell(26,5,"VALOR TOTAL",1,0,"C",1);
												$pdf->Cell(29,5,"LOCALIZAÇO",1,1,"C",1);
										}else{
												$pdf->Cell(181,5,"DESCRIÇO DO ITEM",1,0,"L",1);
												$pdf->Cell(9,5,"UND",1, 0,"C",1);
												$pdf->Cell(17,5,"CÓ RED",1,0,"C",1);
												$pdf->Cell(25,5,"QTD ESTOQUE",1,0,"C",1);
												$pdf->Cell(22,5,"VALOR UNIT",1,0,"C",1);
												$pdf->Cell(26,5,"VALOR TOTAL",1,1,"C",1);
										}
								}
								$DescGrupoAntes  = $DescGrupo;
								$DescClasseAntes = $DescClasse;
						}else{
								if($i == 0){
										if($ExibirLoc == "S"){
												$pdf->Cell(152,5,"DESCRIÇO DO ITEM",1,0,"L",1);
												$pdf->Cell(9,5,"UND",1, 0,"C",1);
												$pdf->Cell(17,5,"CÓ RED",1,0,"C",1);
												$pdf->Cell(25,5,"QTD ESTOQUE",1,0,"C",1);
												$pdf->Cell(22,5,"VALOR UNIT",1,0,"C",1);
												$pdf->Cell(26,5,"VALOR TOTAL",1,0,"C",1);
												$pdf->Cell(29,5,"LOCALIZAÇO",1,1,"C",1);
										}else{
												$pdf->Cell(181,5,"DESCRIÇO DO ITEM",1,0,"L",1);
												$pdf->Cell(9,5,"UND",1, 0,"C",1);
												$pdf->Cell(17,5,"CÓ RED",1,0,"C",1);
												$pdf->Cell(25,5,"QTD ESTOQUE",1,0,"C",1);
												$pdf->Cell(22,5,"VALOR UNIT",1,0,"C",1);
												$pdf->Cell(26,5,"VALOR TOTAL",1,1,"C",1);
										}
								}
						}

						# Quebra de Linha para Descriç do Material #
						$DescMaterialSepara = SeparaFrase($DescMaterial,79);
						$TamDescMaterial    = $pdf->GetStringWidth($DescMaterialSepara);
						if($TamDescMaterial <= 150){
								$LinhasMat = 1;
								$AlturaMat = 5;
						}elseif($TamDescMaterial > 150 and $TamDescMaterial <= 296){
								$LinhasMat = 2;
								$AlturaMat = 10;
						}elseif($TamDescMaterial > 296 and $TamDescMaterial <= 444){
								$LinhasMat = 3;
								$AlturaMat = 15;
						}else{
								$LinhasMat = 4;
								$AlturaMat = 20;
						}
						if($TamDescMaterial > 150){
								$Inicio = 0;
								if($ExibirLoc == "S"){
										$pdf->Cell(152,$AlturaMat,"",1,0,"L",0);
								}else{
										$pdf->Cell(181,$AlturaMat,"",1,0,"L",0);
								}
								for($Quebra = 0; $Quebra < $LinhasMat; $Quebra++){
										if($Quebra == 0){
												$pdf->SetX(10);
												if($ExibirLoc == "S"){
														$pdf->Cell(152,5,trim(substr($DescMaterialSepara,$Inicio,79)),0,0,"L",0);
														$pdf->Cell(9,$AlturaMat,$Unidade,1,0,"C",0);
														$pdf->Cell(17,$AlturaMat,$CodigoReduzido,1, 0,"C",0);
														$pdf->Cell(25,$AlturaMat,converte_quant(sprintf("%01.2f",str_replace(",",".",$QtdEstoque))),1,0,"R",0);
														$pdf->Cell(22,$AlturaMat,converte_valor_estoques(sprintf("%01.4f",str_replace(",",".",$ValorUnitarioPesquisado))),1,0,"R",0);
														$ValorTotal = $QtdEstoque * $ValorUnitarioPesquisado;
														$ValorTotal = converte_valor_estoques(sprintf("%01.4f",str_replace(",",".",$ValorTotal)));
														$pdf->Cell(26,$AlturaMat,$ValorTotal,1,0,"R",0);
														$pdf->Cell(29,$AlturaMat,$Localizacao,1,0,"L",0);
												}else{
														$pdf->Cell(181,5,trim(substr($DescMaterialSepara,$Inicio,79)),0,0,"L",0);
														$pdf->Cell(9,$AlturaMat,$Unidade,1,0,"C",0);
														$pdf->Cell(17,$AlturaMat,$CodigoReduzido,1, 0,"C",0);
														$pdf->Cell(25,$AlturaMat,converte_quant(sprintf("%01.2f",str_replace(",",".",$QtdEstoque))),1,0,"R",0);
														$pdf->Cell(22,$AlturaMat,converte_valor_estoques(sprintf("%01.4f",str_replace(",",".",$ValorUnitarioPesquisado))),1,0,"R",0);
														$ValorTotal = $QtdEstoque * $ValorUnitarioPesquisado;
														$ValorTotal = converte_valor_estoques(sprintf("%01.4f",str_replace(",",".",$ValorTotal)));
														$pdf->Cell(26,$AlturaMat,$ValorTotal,1,0,"R",0);
												}
												$pdf->Ln(5);
										}elseif($Quebra == 1){
												if($ExibirLoc == "S"){
														$pdf->Cell(152,5,trim(substr($DescMaterialSepara,$Inicio,79)),0,0,"L",0);
												}else{
														$pdf->Cell(181,5,trim(substr($DescMaterialSepara,$Inicio,79)),0,0,"L",0);
												}
												$pdf->Ln(5);
										}elseif($Quebra == 2){
												if($ExibirLoc == "S"){
														$pdf->Cell(152,5,trim(substr($DescMaterialSepara,$Inicio,79)),0,0,"L",0);
												}else{
														$pdf->Cell(181,5,trim(substr($DescMaterialSepara,$Inicio,79)),0,0,"L",0);
												}
												$pdf->Ln(5);
										}elseif($Quebra == 3){
												if($ExibirLoc == "S"){
														$pdf->Cell(152,5,trim(substr($DescMaterialSepara,$Inicio,79)),0,0,"L",0);
												}else{
														$pdf->Cell(181,5,trim(substr($DescMaterialSepara,$Inicio,79)),0,0,"L",0);
												}
												$pdf->Ln(5);
										}else{
												if($ExibirLoc == "S"){
														$pdf->Cell(152,5,trim(substr($DescMaterialSepara,$Inicio,79)),0,0,"L",0);
												}else{
														$pdf->Cell(181,5,trim(substr($DescMaterialSepara,$Inicio,79)),0,0,"L",0);
												}
												$pdf->Ln(5);
										}
										$Inicio = $Inicio + 79;
								}
						}else{
								if($ExibirLoc == "S"){
										$pdf->Cell(152,5,$DescMaterial,1,0,"L",0);
										$pdf->Cell(9,5,$Unidade,1,0,"C",0);
										$pdf->Cell(17,5,$CodigoReduzido,1,0,"C",0);
										$pdf->Cell(25,5,converte_quant(sprintf("%01.2f",str_replace(",",".",$QtdEstoque))),1,0,"R",0);
										$pdf->Cell(22,5,converte_valor_estoques(sprintf("%01.4f",str_replace(",",".",$ValorUnitarioPesquisado))),1,0,"R",0);
										$ValorTotal = $QtdEstoque * $ValorUnitarioPesquisado;
										$ValorTotal = converte_valor_estoques(sprintf("%01.4f",str_replace(",",".",$ValorTotal)));
										$pdf->Cell(26,5,$ValorTotal,1,0,"R",0);
										$pdf->Cell(29,5,$Localizacao,1,1,"L",0);
								}else{
										$pdf->Cell(181,5,$DescMaterial,1,0,"L",0);
										$pdf->Cell(9,5,$Unidade,1,0,"C",0);
										$pdf->Cell(17,5,$CodigoReduzido,1,0,"C",0);
										$pdf->Cell(25,5,converte_quant(sprintf("%01.2f",str_replace(",",".",$QtdEstoque))),1,0,"R",0);
										$pdf->Cell(22,5,converte_valor_estoques(sprintf("%01.4f",str_replace(",",".",$ValorUnitarioPesquisado))),1,0,"R",0);
										$ValorTotal = $QtdEstoque * $ValorUnitarioPesquisado;
										$ValorTotal = converte_valor_estoques(sprintf("%01.4f",str_replace(",",".",$ValorTotal)));
										$pdf->Cell(26,5,$ValorTotal,1,1,"R",0);
								}
						}
						
				}
		}
}
$db->disconnect();

# Mostra o totalizador de Materiais #
if($ExibirLoc == "S"){
		$pdf->Cell(280,5,"",1,1,"R",0);
		$pdf->Cell(132,5,"TOTAL DE ITENS",1,0,"R",1);
		$pdf->Cell(20,5,$rows,1,0,"R",0);
		$pdf->Cell(26,5,"QTD TOTAL",1,0,"R",1);
		$pdf->Cell(25,5,converte_quant(sprintf("%01.2f",str_replace(",",".",$QtdTotal))),1,0,"R",0);
		$pdf->Cell(22,5,"VLR TOTAL",1,0,"R",1);
		$pdf->Cell(26,5,converte_valor_estoques(sprintf("%01.4f",str_replace(",",".",$Totalizador))),1,0,"R",0);
		$pdf->Cell(29,5,"",1,1,"R",1);
}else{
		$pdf->Cell(280,5,"",1,1,"R",0);
		$pdf->Cell(161,5,"TOTAL DE ITENS",1,0,"R",1);
		$pdf->Cell(20,5,$rows,1,0,"R",0);
		$pdf->Cell(26,5,"QTD TOTAL",1,0,"R",1);
		$pdf->Cell(25,5,converte_quant(sprintf("%01.2f",str_replace(",",".",$QtdTotal))),1,0,"R",0);
		$pdf->Cell(22,5,"VLR TOTAL",1,0,"R",1);
		$pdf->Cell(26,5,converte_valor_estoques(sprintf("%01.4f",str_replace(",",".",$Totalizador))),1,1,"R",0);
}
$pdf->Output();
?>

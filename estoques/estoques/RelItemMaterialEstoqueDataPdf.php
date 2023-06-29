<?php
#------------------------------------------------------------------------------------
# Portal da DGCO
# Programa: RelItemMaterialEstoqueDataPdf.php
# Autor:    Álvaro Faria
# Data:     17/03/2006
# Alterado: Álvaro Faria
# Data:     07/06/2006
# Alterado: Álvaro Faria
# Data:     12/09/2006 - Indentação / Padronização do cabeçalho
# Alterado: Álvaro Faria
# Data:     06/11/2006 - Desativação do envio do e-mail quando da utilização deste relatório
# Alterado: Carlos Abreu
# Data:     01/10/2007 - Ajustes na query e programacao para evitar estouro de memoria na execução
# Alterado: Rossana Lira/ Rodrigo Melo
# Data:     10/12/2007 - Ajustes no programa para exibir o ultimo valor unitário médio do período informado
#                      - Retirada de campo desnecessário (VMOVMAVALO) do primeiro Select
# Alterado: Álvaro Faria / Rossana Lira
# Data:     11/12/2007 - Adição de order by descendente no select da busca do valor do material para correção
#                        de TMOVMAULAT iguais causados por inventário periódico
#                      - Aumento da célula do valor total e diminuição da célula de valor unitário
# Alterado: Álvaro Faria / Rossana Lira
# Data:     13/12/2007 - Retirada de campo desnecessário (AARMATQTDE) do primeiro Select
# Alterado: Rodrigo Melo
# Data:     27/12/2007 - Inclusão de total dos itens em estoque conforme a classificação "Permanente" e "Consumo" e correção para obter as requisições conforme a última data da situação.
# Alterado: Rodrigo Melo
# Data:     28/01/2008 - Alteração para exibir os dados dos itens em estoque anterior à data atual a partir do histórico do material, para não alterar o valor dos itens de consumo e permanente.
# Alterado: Rodrigo Melo
# Data:     03/03/2008 - Remoção da integração com a tabela de histórico, para que os dados sejam obtidos a partir dos materiais e não do histórico do material.
# Objetivo: Programa de Impressão dos itens em estoque em uma determinada data
# OBS.:     Tabulação 2 espaços
#------------------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

ini_set(max_execution_time, '600');
session_cache_limiter('private_no_expire');

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/estoques/RelContagemInventario.php' );

# Envia e-mail indicando uso do programa para resolução de problema de desempenho do banco de dados $
// $Assunto = "O Relatório de Item de Estoque por Data foi utilizado";
// $Texto   = "Grupo: ".$_SESSION['_cgrempcodi_'].", Usuário: ".$_SESSION['_eusupologi_'];
// EnviaEmail($Mail, $Assunto, $Texto, $From);

# Variáveis com o global off #
if($_SERVER['REQUEST_METHOD'] == "GET"){
		$Almoxarifado = $_GET['Almoxarifado'];
		$Ordem        = $_GET['Ordem'];
		$ExibirLoc    = $_GET['ExibirLoc'];
		$ExibirZer    = $_GET['ExibirZer'];
		$Data         = $_GET['Data'];
		$GrupoClasses = $_GET['GrupoClasses'];
}

# convertendo $GrupoClasses de string para um array de arrays
$Grupos = array();
$Classes = array();
$GruposClassesItens = array();
$itr=0;
if(!is_null($GrupoClasses) and $GrupoClasses != ""){
	$GrupoClasses = explode("!",$GrupoClasses);
	foreach($GrupoClasses as $GrupoClasse){
		$GruposClassesItens[$itr] = explode("_",$GrupoClasse);
		$Grupos[$itr] = $GruposClassesItens[$itr][0];
		$Classes[$itr] = $GruposClassesItens[$itr][1];
		$itr++;
	}
}else{
	$GrupoClasses = array();
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

# Função exibe o Cabeçalho e o Rodapé #
CabecalhoRodapePaisagem();

# Informa o Título do Relatório #
if($Ordem == "1"){
		$TituloRelatorio = "Relatório de Itens de Material em Estoque em ".$Data." (Ordem: Família";
}else{
		$TituloRelatorio = "Relatório de Itens de Material em Estoque em ".$Data." (Ordem: Material";
}
if($ExibirZer == 'N') $TituloRelatorio .= ", Opção: Não exibir itens zerados";
elseif($ExibirZer == 'S') $TituloRelatorio .= ", Opção: Exibir itens zerados";
$TituloRelatorio .= ")";

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
$db = Conexao();

# Converte a data para o formato de pesquisa no banco de dados
$DataPesquisa = DataInvertida($Data);
# Resgata os itens do estoque #

$sql  = "SELECT * FROM ( "; // PREPARA RESULTADO 

$sql .= " SELECT MAT.CMATEPSEQU, MAT.EMATEPDESC, UNI.EUNIDMSIGL, ";
$sql .= "        LOC.FLOCMAEQUI, LOC.ALOCMANEQU, LOC.ALOCMAPRAT, ";
$sql .= "        LOC.ALOCMACOLU, LOC.CARLOCCODI, ";
$sql .= "        AARMATQTDE + SUM(CASE WHEN FTIPMVTIPO = 'S' THEN AMOVMAQTDM ELSE CASE WHEN FTIPMVTIPO = 'E' THEN -AMOVMAQTDM ELSE 0 END END ) AS QTD, ";
$sql .= "        GRU.FGRUMSTIPM ";
if($Ordem == 1){
		$sql .= "       , GRU.EGRUMSDESC, CLA.ECLAMSDESC, GRU.CGRUMSCODI, CLA.CCLAMSCODI ";
}
$sql .= "   FROM SFPC.TBMATERIALPORTAL MAT, SFPC.TBUNIDADEDEMEDIDA UNI, ";
$sql .= "        SFPC.TBLOCALIZACAOMATERIAL LOC, SFPC.TBGRUPOMATERIALSERVICO GRU, SFPC.TBSUBCLASSEMATERIAL SUB, SFPC.TBARMAZENAMENTOMATERIAL ARM ";
$sql .= "   LEFT OUTER JOIN SFPC.TBMOVIMENTACAOMATERIAL MOV ";
$sql .= "     ON ARM.CMATEPSEQU = MOV.CMATEPSEQU ";
$sql .= "    AND MOV.CALMPOCODI = $Almoxarifado ";
$sql .= "    AND MOV.TMOVMAULAT > '$DataPesquisa 23:59:59' ";
$sql .= "    AND (MOV.FMOVMASITU IS NULL OR MOV.FMOVMASITU = 'A') ";
$sql .= "   LEFT OUTER JOIN SFPC.TBTIPOMOVIMENTACAO TIP ";
$sql .= "     ON MOV.CTIPMVCODI = TIP.CTIPMVCODI ";
if($Ordem == 1){
		//$sql .= "       , SFPC.TBGRUPOMATERIALSERVICO GRU, SFPC.TBCLASSEMATERIALSERVICO CLA, SFPC.TBSUBCLASSEMATERIAL SUB ";		
		$sql .= "       , SFPC.TBCLASSEMATERIALSERVICO CLA ";
}
$sql .= "  WHERE MAT.CUNIDMCODI = UNI.CUNIDMCODI AND MAT.CMATEPSEQU = ARM.CMATEPSEQU ";
$sql .= "    AND LOC.CLOCMACODI = ARM.CLOCMACODI AND LOC.CALMPOCODI = $Almoxarifado ";
$sql .= "    AND MAT.CSUBCLSEQU = SUB.CSUBCLSEQU AND SUB.CGRUMSCODI = GRU.CGRUMSCODI ";
if($Ordem == 1){
		$sql .= "   AND GRU.CGRUMSCODI = CLA.CGRUMSCODI AND CLA.CCLAMSCODI = SUB.CCLAMSCODI ";
		$sql .= "   AND CLA.CGRUMSCODI = SUB.CGRUMSCODI AND SUB.CSUBCLSEQU = SUB.CSUBCLSEQU ";
		#$sql .= "   AND SUB.CSUBCLSEQU = MAT.CSUBCLSEQU ";
}

$sql .= " GROUP BY MAT.CMATEPSEQU, MAT.EMATEPDESC, UNI.EUNIDMSIGL, ARM.VARMATUMED, LOC.FLOCMAEQUI, ";
$sql .= "          LOC.ALOCMANEQU, LOC.ALOCMAPRAT, LOC.ALOCMACOLU, LOC.CARLOCCODI, AARMATQTDE, GRU.FGRUMSTIPM ";
if($Ordem == 1){
		$sql .= "       , GRU.EGRUMSDESC, GRU.CGRUMSCODI, CLA.ECLAMSDESC, CLA.CCLAMSCODI ";
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
		# Pega as informações do Almoxarifado #
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
				$Mensagem = "Nenhuma Ocorrência Encontrada";
				$Url = "RelContagemInventario.php?Mensagem=".urlencode($Mensagem)."&Mens=1&Tipo=1";
				if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
				header("location: ".$Url);
				exit;
		}else{
				$DescGrupoAntes  = "";
				$DescCalsseAntes = "";
				$TipoClasseAntes = "";
				$ValorTotalGrupo = 0;
				$ValorTotalQtde = 0;
				$ImprimirClasse = true;
				
				for($i=0; $i < $rows; $i++){
						$Linha = $res->fetchRow();
						$CodigoReduzido = $Linha[0];
						$DescMaterial   = $Linha[1];
						$Unidade        = $Linha[2];
						$QtdEstoque     = $Linha[8];
						$Equipamento    = $Linha[3];
						$NumEquipamento = $Linha[4];
						$Prateleira     = $Linha[5];
						$Coluna         = $Linha[6];
						$Area           = $Linha[7];
            $TipoMaterial   = $Linha[9];
						if($Ordem == 1){
								$DescGrupo     = $Linha[10];
								$DescClasse    = $Linha[11];
								$CodGrupo 	   = $Linha[12];
								$CodClasse     = $Linha[13];
						}
						$Localizacao    = "A:". $Area." E:".$Equipamento.$NumEquipamento."/ESC:".$Prateleira.$Coluna;

						# Descobre o valor unitário para o material na data especificada #
						$sqlvalor  = " SELECT VMOVMAUMED FROM SFPC.TBMOVIMENTACAOMATERIAL MOV";
						$sqlvalor .= "  WHERE MOV.CALMPOCODI = $Almoxarifado ";
						$sqlvalor .= "    AND MOV.CMATEPSEQU = ".$CodigoReduzido." ";
						$sqlvalor .= "    AND (MOV.FMOVMASITU IS NULL OR MOV.FMOVMASITU = 'A') ";
						$sqlvalor .= "    AND MOV.TMOVMAULAT = ";
						$sqlvalor .= "        (SELECT MAX(TMOVMAULAT) FROM SFPC.TBMOVIMENTACAOMATERIAL ";
						$sqlvalor .= "          WHERE CALMPOCODI = $Almoxarifado ";
						$sqlvalor .= "            AND CMATEPSEQU = ".$CodigoReduzido." ";						
            $sqlvalor .= "            AND TMOVMAULAT <= '$DataPesquisa 23:59:59' ";
						$sqlvalor .= "            AND (FMOVMASITU IS NULL OR FMOVMASITU = 'A') )";
						$sqlvalor .= " ORDER BY MOV.AMOVMAANOM DESC, MOV.CMOVMACODI DESC";
						$resvalor    = $db->query($sqlvalor);
						if( db::isError($resvalor) ){
								ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlvalor");
						}else{
								$LinhaValor              = $resvalor->fetchRow();
								$ValorUnitarioPesquisado = $LinhaValor[0];
						}

						$QtdTotal        = $QtdTotal + $QtdEstoque;
            $Totalizador     = $Totalizador + ($QtdEstoque * $ValorUnitarioPesquisado);
            
            if($TipoMaterial == 'C'){ //Tipo Material Consumo
              $QtdTotalConsumo = $QtdTotalConsumo + $QtdEstoque;
              $TotalizadorConsumo = $TotalizadorConsumo + ($QtdEstoque * $ValorUnitarioPesquisado);
            } else { //Tipo Material Permanente -> 'P'
              $QtdTotalPermanente = $QtdTotalPermanente + $QtdEstoque;
              $TotalizadorPermanente = $TotalizadorPermanente + ($QtdEstoque * $ValorUnitarioPesquisado);
            }
						
						if($ImprimirClasse){
							if( ( ($DescGrupoAntes != $DescGrupo) or ( $DescGrupoAntes == $DescGrupo and $DescClasseAntes != $DescClasse ) ) and ($i!=0) ){
								if($TipoClasseAntes=="P"){
									$TipoClasseDescr = "PERMANENTE";
								}else{
									$TipoClasseDescr = "CONSUMO";
								}
								$pdf->Cell(133,5,"TOTAL DO GRUPO/CLASSE",1,0,"L",1);
								$pdf->Cell(24,5,"TIPO",1,0,"R",1);
								$pdf->Cell(24,5,$TipoClasseDescr,1,0,"R",0);
								$pdf->Cell(26,5,"QTD TOTAL",1,0,"R",1);
								$pdf->Cell(25,5,converte_quant(sprintf("%01.2f",str_replace(",",".",$ValorTotalQtde))),1,0,"R",0);
								$pdf->Cell(22,5,"VLR TOTAL",1,0,"R",1);
								$pdf->Cell(26,5,converte_valor_estoques(sprintf("%01.4f",str_replace(",",".",$ValorTotalGrupo))),1,1,"R",0);
								$pdf->Cell(280,5,"",1,1,"R",0);
							}
						}
            
						if( count($GrupoClasses)==0 or in_array($CodGrupo."_".$CodClasse,$GrupoClasses) ){
							$ImprimirClasse = true;
						}else{
							$ImprimirClasse = false;
						}
						if($ImprimirClasse){
							if( $Ordem == 1 ){
									if( $DescGrupoAntes != $DescGrupo or ( $DescGrupoAntes == $DescGrupo and $DescClasseAntes != $DescClasse ) ){
											$TipoClasseAntes = $TipoMaterial;
											$ValorTotalGrupo = 0;
											$ValorTotalQtde = 0;
											$pdf->Cell(30,5,"GRUPO / CLASSE",1,0,"L",1);
											$pdf->Cell(250,5,$DescGrupo." / ".$DescClasse,1,1,"L",0);
											if($ExibirLoc == "S"){
													$pdf->Cell(152,5,"DESCRIÇÃO DO ITEM",1,0,"L",1);
													$pdf->Cell(9,5,"UND",1, 0,"C",1);
													$pdf->Cell(17,5,"CÓD RED",1,0,"C",1);
													$pdf->Cell(25,5,"QTD ESTOQUE",1,0,"C",1);
													$pdf->Cell(22,5,"VALOR UNIT",1,0,"C",1);
													$pdf->Cell(26,5,"VALOR TOTAL",1,0,"C",1);
													$pdf->Cell(29,5,"LOCALIZAÇÃO",1,1,"C",1);
											}else{
													$pdf->Cell(181,5,"DESCRIÇÃO DO ITEM",1,0,"L",1);
													$pdf->Cell(9,5,"UND",1, 0,"C",1);
													$pdf->Cell(17,5,"CÓD RED",1,0,"C",1);
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
													$pdf->Cell(152,5,"DESCRIÇÃO DO ITEM",1,0,"L",1);
													$pdf->Cell(9,5,"UND",1, 0,"C",1);
													$pdf->Cell(17,5,"CÓD RED",1,0,"C",1);
													$pdf->Cell(25,5,"QTD ESTOQUE",1,0,"C",1);
													$pdf->Cell(22,5,"VALOR UNIT",1,0,"C",1);
													$pdf->Cell(26,5,"VALOR TOTAL",1,0,"C",1);
													$pdf->Cell(29,5,"LOCALIZAÇÃO",1,1,"C",1);
											}else{
													$pdf->Cell(181,5,"DESCRIÇÃO DO ITEM",1,0,"L",1);
													$pdf->Cell(9,5,"UND",1, 0,"C",1);
													$pdf->Cell(17,5,"CÓD RED",1,0,"C",1);
													$pdf->Cell(25,5,"QTD ESTOQUE",1,0,"C",1);
													$pdf->Cell(22,5,"VALOR UNIT",1,0,"C",1);
													$pdf->Cell(26,5,"VALOR TOTAL",1,1,"C",1);
											}
									}
							}

						# Quebra de Linha para Descrição do Material #
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
														$ValorTotalQtde += $QtdEstoque;
														$ValorTotalGrupo += $ValorTotal;
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
														$ValorTotalQtde += $QtdEstoque;
														$ValorTotalGrupo += $ValorTotal;
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
										$ValorTotalQtde += $QtdEstoque;
										$ValorTotalGrupo += $ValorTotal;
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
										$ValorTotalQtde += $QtdEstoque;
										$ValorTotalGrupo += $ValorTotal;
										$ValorTotal = converte_valor_estoques(sprintf("%01.4f",str_replace(",",".",$ValorTotal)));
										$pdf->Cell(26,5,$ValorTotal,1,1,"R",0);
								}
						}
						
					}
            
            if($TipoMaterial == 'C'){ //Tipo Material Consumo
              $TotalItensConsumo = $TotalItensConsumo + 1;
            } else { //Tipo Material Permanente -> 'P'
              $TotalItensPermanente = $TotalItensPermanente + 1;
            }		
				}
		}
}
$db->disconnect();

if($ImprimirClasse){
	if( ( ($DescGrupoAntes != $DescGrupo) or ( $DescGrupoAntes == $DescGrupo and $DescClasseAntes != $DescClasse ) ) and ($i!=0) ){
		if($TipoClasseAntes=="P"){
			$TipoClasseDescr = "PERMANENTE";
		}else{
			$TipoClasseDescr = "CONSUMO";
		}
		$pdf->Cell(133,5,"TOTAL DO GRUPO/CLASSE",1,0,"L",1);
		$pdf->Cell(24,5,"TIPO",1,0,"R",1);
		$pdf->Cell(24,5,$TipoClasseDescr,1,0,"R",0);
		$pdf->Cell(26,5,"QTD TOTAL",1,0,"R",1);
		$pdf->Cell(25,5,converte_quant(sprintf("%01.2f",str_replace(",",".",$ValorTotalQtde))),1,0,"R",0);
		$pdf->Cell(22,5,"VLR TOTAL",1,0,"R",1);
		$pdf->Cell(26,5,converte_valor_estoques(sprintf("%01.4f",str_replace(",",".",$ValorTotalGrupo))),1,1,"R",0);
		$pdf->Cell(280,5,"",1,1,"R",0);
	}
}

# Mostra o totalizador de Materiais #
if($ExibirLoc == "S"){

    if($TotalItensConsumo != null && $TotalItensConsumo > 0){
      $pdf->Cell(280,5,"",1,1,"R",0);
  		$pdf->Cell(132,5,"TOTAL DE ITENS DE CONSUMO",1,0,"R",1);
  		$pdf->Cell(20,5,$TotalItensConsumo,1,0,"R",0);
  		$pdf->Cell(26,5,"QTD TOTAL",1,0,"R",1);
  		$pdf->Cell(25,5,converte_quant(sprintf("%01.2f",str_replace(",",".",$QtdTotalConsumo))),1,0,"R",0);
  		$pdf->Cell(22,5,"VLR TOTAL",1,0,"R",1);
  		$pdf->Cell(26,5,converte_valor_estoques(sprintf("%01.4f",str_replace(",",".",$TotalizadorConsumo))),1,0,"R",0);
  		$pdf->Cell(29,5,"",1,1,"R",1);
    }
    
    if($TotalItensPermanente != null && $TotalItensPermanente > 0){
      $pdf->Cell(280,5,"",1,1,"R",0);
  		$pdf->Cell(132,5,"TOTAL DE ITENS DE PERMANENTES",1,0,"R",1);
  		$pdf->Cell(20,5,$TotalItensPermanente,1,0,"R",0);
  		$pdf->Cell(26,5,"QTD TOTAL",1,0,"R",1);
  		$pdf->Cell(25,5,converte_quant(sprintf("%01.2f",str_replace(",",".",$QtdTotalPermanente))),1,0,"R",0);
  		$pdf->Cell(22,5,"VLR TOTAL",1,0,"R",1);
  		$pdf->Cell(26,5,converte_valor_estoques(sprintf("%01.4f",str_replace(",",".",$TotalizadorPermanente))),1,0,"R",0);
  		$pdf->Cell(29,5,"",1,1,"R",1);
    }

		$pdf->Cell(280,5,"",1,1,"R",0);
		$pdf->Cell(132,5,"TOTAL DE ITENS",1,0,"R",1);
		$pdf->Cell(20,5,$rows,1,0,"R",0);
		$pdf->Cell(26,5,"QTD TOTAL",1,0,"R",1);
		$pdf->Cell(25,5,converte_quant(sprintf("%01.2f",str_replace(",",".",$QtdTotal))),1,0,"R",0);
		$pdf->Cell(22,5,"VLR TOTAL",1,0,"R",1);
		$pdf->Cell(26,5,converte_valor_estoques(sprintf("%01.4f",str_replace(",",".",$Totalizador))),1,0,"R",0);
		$pdf->Cell(29,5,"",1,1,"R",1);
}else{

    if($TotalItensConsumo != null && $TotalItensConsumo > 0){
      $pdf->Cell(280,5,"",1,1,"R",0);
  		$pdf->Cell(161,5,"TOTAL DE ITENS DE CONSUMO",1,0,"R",1);
  		$pdf->Cell(20,5,$TotalItensConsumo,1,0,"R",0);
  		$pdf->Cell(26,5,"QTD TOTAL",1,0,"R",1);
  		$pdf->Cell(25,5,converte_quant(sprintf("%01.2f",str_replace(",",".",$QtdTotalConsumo))),1,0,"R",0);
  		$pdf->Cell(22,5,"VLR TOTAL",1,0,"R",1);
  		$pdf->Cell(26,5,converte_valor_estoques(sprintf("%01.4f",str_replace(",",".",$TotalizadorConsumo))),1,1,"R",0);
    }
    
    if($TotalItensPermanente != null && $TotalItensPermanente > 0){
      $pdf->Cell(280,5,"",1,1,"R",0);
  		$pdf->Cell(161,5,"TOTAL DE ITENS PERMANENTES",1,0,"R",1);
  		$pdf->Cell(20,5,$TotalItensPermanente,1,0,"R",0);
  		$pdf->Cell(26,5,"QTD TOTAL",1,0,"R",1);
  		$pdf->Cell(25,5,converte_quant(sprintf("%01.2f",str_replace(",",".",$QtdTotalPermanente))),1,0,"R",0);
  		$pdf->Cell(22,5,"VLR TOTAL",1,0,"R",1);
  		$pdf->Cell(26,5,converte_valor_estoques(sprintf("%01.4f",str_replace(",",".",$TotalizadorPermanente))),1,1,"R",0);
    }

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

<?php
#------------------------------------------------------------------------------------------------------------------
# Portal da DGCO
# Programa: RelInventarioFechamentoAuditoriaPdf.php
# Autor:    Carlos Abreu
# Data:     13/12/2006
# Objetivo: Programa de Impressão da contagem de inventário de acordo com o Almoxarifado
#--------------------------------
#--------------------------------
# OBS.:     Tabulação 2 espaços
#           Ao passar para produção trocar o caminho da imagem do cabeçalho na função
#           CabecalhoRodapeInventario() neste arquivo
#------------------------------------------------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_cache_limiter('private_no_expire');
session_start();
Seguranca();

# Variáveis com o global off #
if($_SERVER['REQUEST_METHOD'] == "GET"){
		$Almoxarifado = $_GET['Almoxarifado'];
		$Localizacao  = $_GET['Localizacao'];
		$Ordem        = $_GET['Ordem'];
		$Ano          = $_GET['Ano'];
		$Sequencial   = $_GET['Sequencial'];
		$DataBase     = $_GET['DataBase'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

# Função exibe o Cabeçalho e o Rodapé #
CabecalhoRodapePaisagem();

# Informa o Título do Relatório #
if ($Ordem == "1"){
		$TituloRelatorio = "Relatório de Acertos/Justificativas (Ordem: Família)";
} else {
		$TituloRelatorio = "Relatório de Acertos/Justificativas (Ordem: Material)";
}

# Cria o objeto PDF, o Default é formato Retrato, A4  e a medida em milímetros #
$pdf = new PDF("L","mm","A4");

# Define um apelido para o número total de páginas #
$pdf->AliasNbPages();

# Define as cores do preenchimentos que serão usados #
$pdf->SetFillColor(220,220,220);

# Adiciona uma página no documento #
$pdf->AddPage();

# Muda o tamanho do Rodapé #
$pdf->SetAutoPageBreak(true,32);

# Seta as fontes que serão usadas na impressão de strings #
$pdf->SetFont("Arial","",9);

$db = Conexao();
if($Ordem == 1){
		# Pega os dados dos Materiais Cadastrados - Ordem Família #
		$sql  = "SELECT A.CMATEPSEQU, B.EMATEPDESC, C.EUNIDMSIGL, A.AINVMAQTEA, A.AINVMAESTO, A.VINVMAUNIT, A.EINVMAJUST, GRUPO.EGRUMSDESC, CLASSE.ECLAMSDESC, ";
		$sql .= "       CASE WHEN (A.AINVMAQTEA IS NOT NULL) THEN 'S' ELSE 'N' END ";
		$sql .= "  FROM SFPC.TBINVENTARIOMATERIAL A ";
		$sql .= " INNER JOIN SFPC.TBMATERIALPORTAL B ";
		$sql .= "    ON (A.CMATEPSEQU = B.CMATEPSEQU) ";
		$sql .= " INNER JOIN SFPC.TBUNIDADEDEMEDIDA C ";
		$sql .= "    ON (B.CUNIDMCODI = C.CUNIDMCODI), ";
		$sql .= "       SFPC.TBSUBCLASSEMATERIAL AS SUBCLASSE, ";
		$sql .= "       SFPC.TBCLASSEMATERIALSERVICO AS CLASSE, ";
		$sql .= "       SFPC.TBGRUPOMATERIALSERVICO AS GRUPO ";
		$sql .= " WHERE A.CLOCMACODI = $Localizacao AND A.AINVCOSEQU = $Sequencial AND A.AINVCOANOB = $Ano ";
		$sql .= "   AND B.CSUBCLSEQU = SUBCLASSE.CSUBCLSEQU ";
		$sql .= "   AND SUBCLASSE.CGRUMSCODI = CLASSE.CGRUMSCODI ";
		$sql .= "   AND SUBCLASSE.CCLAMSCODI = CLASSE.CCLAMSCODI ";
		$sql .= "   AND CLASSE.CGRUMSCODI = GRUPO.CGRUMSCODI ";
		$sql .= "   AND A.AINVMAESTO <> A.AINVMAQTEA";
		$sql .= " ORDER BY GRUPO.EGRUMSDESC, CLASSE.ECLAMSDESC, B.EMATEPDESC ";
}elseif( $Ordem == 2 ){
		# Pega os dados dos Materiais Cadastrados - Ordem Material #
		$sql  = "SELECT A.CMATEPSEQU, B.EMATEPDESC, C.EUNIDMSIGL, A.AINVMAQTEA, A.AINVMAESTO, A.VINVMAUNIT, A.EINVMAJUST, ";
		$sql .= "       CASE WHEN (A.AINVMAQTEA IS NOT NULL) THEN 'S' ELSE 'N' END ";
		$sql .= "  FROM SFPC.TBINVENTARIOMATERIAL A ";
		$sql .= " INNER JOIN SFPC.TBMATERIALPORTAL B ";
		$sql .= "    ON (A.CMATEPSEQU = B.CMATEPSEQU) ";
		$sql .= " INNER JOIN SFPC.TBUNIDADEDEMEDIDA C ";
		$sql .= "    ON (B.CUNIDMCODI = C.CUNIDMCODI) ";
		$sql .= " WHERE A.CLOCMACODI = $Localizacao ";
		$sql .= "   AND A.AINVCOSEQU = $Sequencial ";
		$sql .= "   AND A.AINVCOANOB = $Ano ";
		$sql .= "   AND ((A.AINVMAESTO <> A.AINVMAQTEA) OR (A.AINVMAQTEA IS NULL AND A.AINVMAESTO IS NOT NULL))";
		$sql .= " ORDER BY B.EMATEPDESC ";
}
$res = $db->query($sql);
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
				$pdf->Cell(183,5,"$DescAlmox",1,0,"L",0);
				$pdf->Cell(31,5,"DATA INVENTÁRIO", 1, 0, "L", 1);
				$pdf->Cell(36,5,$DataBase, 1, 1, "C", 0);
				$pdf->ln(5);
		}

		# Linhas de Itens de Material #
		$rows = $res->numRows();
		if($rows == 0){
				$Mensagem = "Nenhuma Ocorrência Encontrada";
				$Url = "RelInventarioFechamentoAuditoria.php?Mensagem=".urlencode($Mensagem)."&Mens=1&Tipo=1";
				if(!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
				header("location: ".$Url);
				exit;
		}else{
				for($i=0; $i< $rows; $i++){
						$Linha                      = $res->fetchRow();
						$CodigoReduzido[$i]         = $Linha[0];
						$DescMaterial[$i]           = RetiraAcentos($Linha[1]).$SimboloConcatenacaoDesc.str_replace("\"","”",$Linha[1]);
						$Unidade[$i]                = $Linha[2];
						$AlmoxarifadoQuantidade[$i] = $Linha[3];
						$InventarioQuantidade[$i]   = $Linha[4];
						$InventarioValor[$i]        = $Linha[5];
						$Justificativa[$i]          = $Linha[6];
						$Armazenamento[$i]          = $Linha[7];

						# Montando o array de Itens do Inventário #
						if($Ordem == 1){
								$DescGrupo[$i]  = RetiraAcentos($Linha[7]).$SimboloConcatenacaoDesc.str_replace("\"","”",$Linha[7]);
								$DescClasse[$i] = RetiraAcentos($Linha[8]).$SimboloConcatenacaoDesc.str_replace("\"","”",$Linha[8]);
								$Itens[$i]      = $DescGrupo[$i].$SimboloConcatenacaoArray.$DescClasse[$i].$SimboloConcatenacaoArray.$DescMaterial[$i].$SimboloConcatenacaoArray.$CodigoReduzido[$i].$SimboloConcatenacaoArray.$Unidade[$i].$SimboloConcatenacaoArray.$AlmoxarifadoQuantidade[$i].$SimboloConcatenacaoArray.$InventarioQuantidade[$i].$SimboloConcatenacaoArray.$InventarioValor[$i].$SimboloConcatenacaoArray.$Justificativa[$i].$SimboloConcatenacaoArray.$Armazenamento[$i];
						}else{
								$Itens[$i] = $DescMaterial[$i].$SimboloConcatenacaoArray.$CodigoReduzido[$i].$SimboloConcatenacaoArray.$Unidade[$i].$SimboloConcatenacaoArray.$AlmoxarifadoQuantidade[$i].$SimboloConcatenacaoArray.$InventarioQuantidade[$i].$SimboloConcatenacaoArray.$InventarioValor[$i].$SimboloConcatenacaoArray.$Justificativa[$i].$SimboloConcatenacaoArray.$Armazenamento[$i];
						}
				}
		}
}
$db->disconnect();

# Escrevendo o Relatório #
$DescGrupoAntes     = "";
$DescCalsseAntes    = "";

sort($Itens);
$Diferenca = '';
for($i=0; $i< count($Itens); $i++){
		# Extrai os dados do Array de Itens #
		$Dados = split($SimboloConcatenacaoArray,$Itens[$i]);
		if($Ordem == 1){
				$DescGrupo              = $Dados[0];
				$DescClasse             = $Dados[1];
				$DescMaterial           = $Dados[2];
				$CodigoReduzido         = $Dados[3];
				$Unidade                = $Dados[4];
				$AlmoxarifadoQuantidade = $Dados[5];
				$InventarioQuantidade   = $Dados[7];
				$InventarioValor        = $Dados[8];
				$Justificativa          = strtoupper2($Dados[9]);
				$Armazenamento          = $Dados[10];
				$Diferenca += (($InventarioQuantidade-$AlmoxarifadoQuantidade)*$InventarioValor);

				$InvQtdTotal += $InventarioQuantidade;
				$AlmQtdTotal += $AlmoxarifadoQuantidade;

				# Pega a descrição do Grupo com acento #
				$DescricaoG = split($SimboloConcatenacaoDesc,$DescGrupo);
				$DescGrupo  = $DescricaoG[1];

				# Pega a descrição do Grupo com acento #
				$DescricaoC = split($SimboloConcatenacaoDesc,$DescClasse);
				$DescClasse = $DescricaoC[1];

				if( $DescGrupoAntes != $DescGrupo or ( $DescGrupoAntes == $DescGrupo and $DescClasseAntes != $DescClasse ) ){
						$pdf->Cell(30,5,"GRUPO / CLASSE",1,0,"L",1);
						$pdf->Cell(250,5,$DescGrupo." / ".$DescClasse,1,1,"L",0);
						$pdf->Cell(83,5,"DESCRIÇÃO DO ITEM",1,0,"L",1);
						$pdf->Cell(10,5,"UNID.",1, 0,"C",1);
						$pdf->Cell(16,5,"CÓD.RED.",1,0,"C",1);
						$pdf->Cell(27,5,"QTD.ESTOQUE",1,0,"C",1);
						$pdf->Cell(27,5,"QTD.INVENTÁRIO.",1,0,"C",1);
						$pdf->Cell(27,5,"VALOR UNITÁRIO",1,0,"C",1);
						$pdf->Cell(90,5,"JUSTIFICATIVA",1,1,"C",1);
				}
				$DescGrupoAntes     = $DescGrupo;
				$DescClasseAntes    = $DescClasse;
		}else{
				$DescMaterial           = $Dados[0];
				$CodigoReduzido         = $Dados[1];
				$Unidade                = $Dados[2];
				$AlmoxarifadoQuantidade = $Dados[3];
				$InventarioQuantidade   = $Dados[4];
				$InventarioValor        = $Dados[5];
				$Justificativa          = strtoupper2($Dados[6]);
				$Armazenamento          = $Dados[7];

				$Diferenca += (($InventarioQuantidade-$AlmoxarifadoQuantidade)*$InventarioValor);

				$InvQtdTotal += $InventarioQuantidade;
				$AlmQtdTotal += $AlmoxarifadoQuantidade;

				if($i == 0){
						$pdf->Cell(83,5,"DESCRIÇÃO DO ITEM",1,0,"L",1);
						$pdf->Cell(10,5,"UNID.",1, 0,"C",1);
						$pdf->Cell(16,5,"CÓD.RED.",1,0,"C",1);
						$pdf->Cell(27,5,"QTD.ESTOQUE",1,0,"C",1);
						$pdf->Cell(27,5,"QTD.INVENTÁRIO.",1,0,"C",1);
						$pdf->Cell(27,5,"VALOR UNITÁRIO",1,0,"C",1);
						$pdf->Cell(90,5,"JUSTIFICATIVA",1,1,"C",1);
				}
		}

		# Pega a descrição do Material com acento #
		$Descricao    = split($SimboloConcatenacaoDesc,$DescMaterial);
		$DescMaterial = $Descricao[1];

		# Quebra de Linha para Descrição do Material #
		$Fim    = 38; //52
		$Coluna = 70; //92 - Grande demais, corta palavras, pequeno, provoca espaços desnecessários
		$setX   = 10;
		$FimJust    = 46; //52
		$ColunaJust = 80; //92 - Grande demais, corta palavras, pequeno, provoca espaços desnecessários

		$DescMaterialSepara = SeparaFrase($DescMaterial,$Fim);
		$TamDescMaterial    = $pdf->GetStringWidth($DescMaterialSepara);

		$JustMaterialSepara = SeparaFrase($Justificativa,$FimJust);
		$TamJustMaterial    = $pdf->GetStringWidth($JustMaterialSepara);


		if($TamDescMaterial <= $Coluna and $TamJustMaterial <= $ColunaJust){
				$LinhasMat = 1;
				$AlturaMat = 5;
		}elseif($TamDescMaterial <= 2*($Coluna-2) and $TamJustMaterial <= 2*($ColunaJust-2)){
				$LinhasMat = 2;
				$AlturaMat = 10;
		}elseif($TamDescMaterial <= 3*($Coluna-4) and $TamJustMaterial <= 3*($ColunaJust-4)){
				$LinhasMat = 3;
				$AlturaMat = 15;
		}elseif($TamDescMaterial <= 4*($Coluna-6) and $TamJustMaterial <= 4*($ColunaJust-6)){
				$LinhasMat = 4;
				$AlturaMat = 20;
		}elseif($TamDescMaterial <= 5*($Coluna-8) and $TamJustMaterial <= 5*($ColunaJust-8)) {
				$LinhasMat = 5;
				$AlturaMat = 25;
		}elseif($TamDescMaterial <= 6*($Coluna-10) and $TamJustMaterial <= 6*($ColunaJust-10)){
				$LinhasMat = 6;
				$AlturaMat = 30;
		}elseif($TamDescMaterial <= 7*($Coluna-10) and $TamJustMaterial <= 7*($ColunaJust-10)){
				$LinhasMat = 7;
				$AlturaMat = 35;
		}else{
				$LinhasMat = 8;
				$AlturaMat = 40;
		}
		if(
			(($AlmoxarifadoQuantidade <> $InventarioQuantidade) and ($Armazenamento!='N') ) or
			($Armazenamento=='N' and $InventarioQuantidade!='0.00')
		){
			if($TamDescMaterial > $Coluna or $TamJustMaterial > $ColunaJust){
					$Inicio = 0;
					$InicioJust = 0;
					$pdf->Cell(83,$AlturaMat,"",1,0,"L",0);
					$pdf->Cell(107,5,"",0,0,"",0);
					$pdf->Cell(90,$AlturaMat,"",1,0,"L",0);
					for($Quebra = 0; $Quebra < $LinhasMat; $Quebra++){
							if($Quebra == 0){
									$pdf->SetX(10);
									$pdf->Cell(83,5,trim(substr($DescMaterialSepara,$Inicio,$Fim)),0,0,"L",0);
									$pdf->Cell(10,$AlturaMat,$Unidade,1,0,"C",0);
									$pdf->Cell(16,$AlturaMat,$CodigoReduzido,1, 0,"C",0);
									if ($Armazenamento=='N'){
											$pdf->Cell(27,$AlturaMat,"Inexistente",1, 0,"R",0);
									} else {
											$pdf->Cell(27,$AlturaMat,converte_quant($AlmoxarifadoQuantidade),1, 0,"R",0);
									}
									$pdf->Cell(27,$AlturaMat,converte_quant($InventarioQuantidade),1, 0,"R",0);
									$pdf->Cell(27,$AlturaMat,converte_valor_estoques($InventarioValor),1, 0,"R",0);
									$pdf->Cell(90,5,trim(substr($JustMaterialSepara,$Inicio,$FimJust)),0,0,"L",0);
									$pdf->Ln(5);
							}else{
									$pdf->Cell(83,5,trim(substr($DescMaterialSepara,$Inicio,$Fim)),0,0,"L",0);
									$pdf->Cell(107,5,"",0,0,"",0);
									$pdf->Cell(90,5,trim(substr($JustMaterialSepara,$InicioJust,$FimJust)),0,0,"L",0);
									$pdf->Ln(5);
							}
							$Inicio = $Inicio + $Fim;
							$InicioJust = $InicioJust + $FimJust;
					}
			}else{
					$pdf->Cell(83,5,$DescMaterial, 1,0, "L",0);
					$pdf->Cell(10,5,$Unidade,1,0,"C",0);
					$pdf->Cell(16,5,$CodigoReduzido,1, 0,"C",0);
					if ($Armazenamento=='N'){
							$pdf->Cell(27,$AlturaMat,"Inexistente",1, 0,"R",0);
					} else {
							$pdf->Cell(27,$AlturaMat,converte_quant($AlmoxarifadoQuantidade),1, 0,"R",0);
					}
					$pdf->Cell(27,$AlturaMat,converte_quant($InventarioQuantidade),1, 0,"R",0);
					$pdf->Cell(27,$AlturaMat,converte_valor_estoques($InventarioValor),1, 0,"R",0);
					$pdf->Cell(90,5,$Justificativa,1,1,"L",0);
			}
		}
}
# Mostra o totalizador de Itens do Inventário #
$pdf->Cell(83,5,"VALOR DO ACERTO", 1,0, "R",1);
$pdf->Cell(26,5, converte_valor_estoques($Diferenca), 1,0, "R",0);
$pdf->Cell(81,5,"TOTAL DE ITENS", 1,0, "R",1);
$pdf->Cell(90,5, $rows, 1,0, "R",0);
$pdf->Output();
?>

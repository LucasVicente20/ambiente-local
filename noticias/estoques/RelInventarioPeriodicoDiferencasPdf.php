<?php
#------------------------------------------------------------------------------------------------------------------
# Portal da DGCO
# Programa: RelInventarioPeriodicoDiferencasPdf.php
# Autor:    Carlos Abreu
# Data:     13/12/2006
# Objetivo: Programa de Impressão da contagem de inventário de acordo com o Almoxarifado
#-----------------------
# Alterado: Álvaro Faria
# Data:     22/12/2006 - Alteração do tamanho das colunas e correção das quebras de linhas
#                        para a descrição dos materiais
# Alterado: Álvaro Faria
# Data:     22/12/2006 - Alteração do tamanho das colunas e correção das quebras de linhas
# Alterado: Ariston Cordeiro
# Data:     10/12/2008 - Comentários no SQL que traz diferenças por material
# Alterado: Ariston Cordeiro
# Data:     11/12/2008 - Pegando valores de estoque real, ao invés de estoque total (incluindo virtual)
#------------------------
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
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

# Função exibe o Cabeçalho e o Rodapé #
CabecalhoRodapePaisagem();

# Informa o Título do Relatório #
if($Ordem == "1"){
		$TituloRelatorio = "Relatório de Diferenças de Inventário (Ordem: Família)";
}else{
		$TituloRelatorio = "Relatório de Diferenças de Inventário (Ordem: Material)";
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
$pdf->SetFont("Arial","",8);

$db = Conexao();
if($Ordem == 1){
		# Pega os dados dos Materiais Cadastrados - Ordem Família #
		$sql  = "SELECT DADOS.CMATEPSEQU, MATERIAL.EMATEPDESC, UNIDADE.EUNIDMSIGL, DADOS.ALMQTD, DADOS.ALMVAL, DADOS.INVQTD, DADOS.INVVAL, GRUPO.EGRUMSDESC, CLASSE.ECLAMSDESC, CASE WHEN (ARMAZENAMENTO.CLOCMACODI IS NULL ) THEN 'N' ELSE 'S' END  ";
		$sql .= "  FROM (  ";
		$sql .= "       SELECT TABELA.CLOCMACODI, TABELA.CMATEPSEQU, SUM(TABELA.QTD1) AS ALMQTD, SUM(TABELA.VAL1) AS ALMVAL, SUM(TABELA.QTD2) AS INVQTD,  ";
		$sql .= "              SUM(TABELA.VAL2) AS INVVAL ";
		$sql .= "         FROM (  ";
		$sql .= "              SELECT CLOCMACODI, CMATEPSEQU, AARMATESTR AS QTD1, VARMATUMED AS VAL1, 0.00 AS QTD2, 0.0000 AS VAL2  ";
		$sql .= "                FROM SFPC.TBARMAZENAMENTOMATERIAL  ";
		$sql .= "               WHERE CLOCMACODI = $Localizacao  ";
		$sql .= "               UNION ALL  ";
		$sql .= "              SELECT CLOCMACODI, CMATEPSEQU, 0.00, 0.0000, AINVMAESTO, VINVMAUNIT  ";
		$sql .= "                FROM SFPC.TBINVENTARIOMATERIAL  ";
		$sql .= "               WHERE (CLOCMACODI, AINVCOANOB, AINVCOSEQU) = (  ";
		$sql .= "                     SELECT A.CLOCMACODI, A.AINVCOANOB, MAX(A.AINVCOSEQU) AS AINVCOSEQU  ";
		$sql .= "                       FROM SFPC.TBINVENTARIOCONTAGEM A  ";
		$sql .= "                      WHERE A.CLOCMACODI=$Localizacao  ";
		$sql .= "                        AND A.FINVCOFECH IS NULL  ";
		$sql .= "                        AND A.AINVCOANOB=( ";
		$sql .= "                            SELECT MAX(AINVCOANOB)  ";
		$sql .= "                              FROM SFPC.TBINVENTARIOCONTAGEM  ";
		$sql .= "                             WHERE CLOCMACODI=$Localizacao)  ";
		$sql .= "                             GROUP BY A.CLOCMACODI,A.AINVCOANOB) ";
		$sql .= "                     ) AS TABELA  ";
		$sql .= "               GROUP BY TABELA.CLOCMACODI, TABELA.CMATEPSEQU ";
		$sql .= "              ) AS DADOS  ";
		$sql .= "  LEFT OUTER JOIN SFPC.TBARMAZENAMENTOMATERIAL AS ARMAZENAMENTO  ";
		$sql .= "    ON DADOS.CLOCMACODI = ARMAZENAMENTO.CLOCMACODI  ";
		$sql .= "   AND DADOS.CMATEPSEQU = ARMAZENAMENTO.CMATEPSEQU, ";
		$sql .= "       SFPC.TBMATERIALPORTAL AS MATERIAL, ";
		$sql .= "       SFPC.TBUNIDADEDEMEDIDA AS UNIDADE,  ";
		$sql .= "       SFPC.TBSUBCLASSEMATERIAL AS SUBCLASSE, ";
		$sql .= "       SFPC.TBCLASSEMATERIALSERVICO AS CLASSE, ";
		$sql .= "       SFPC.TBGRUPOMATERIALSERVICO AS GRUPO ";
		$sql .= " WHERE (DADOS.INVQTD <> DADOS.ALMQTD OR DADOS.ALMVAL = 0.0000)  ";
		$sql .= "   AND DADOS.CMATEPSEQU = MATERIAL.CMATEPSEQU  ";
		$sql .= "   AND MATERIAL.CUNIDMCODI = UNIDADE.CUNIDMCODI  ";
		$sql .= "   AND MATERIAL.CSUBCLSEQU = SUBCLASSE.CSUBCLSEQU ";
		$sql .= "   AND SUBCLASSE.CGRUMSCODI = CLASSE.CGRUMSCODI ";
		$sql .= "   AND SUBCLASSE.CCLAMSCODI = CLASSE.CCLAMSCODI ";
		$sql .= "   AND CLASSE.CGRUMSCODI = GRUPO.CGRUMSCODI ";
		$sql .= " ORDER BY GRUPO.EGRUMSDESC, CLASSE.ECLAMSDESC, MATERIAL.EMATEPDESC ";
}elseif($Ordem == 2){
		# Pega os dados dos Materiais Cadastrados - Ordem Material #
		$sql  = "
			SELECT DADOS.CMATEPSEQU, MATERIAL.EMATEPDESC, UNIDADE.EUNIDMSIGL, DADOS.ALMQTD, DADOS.ALMVAL, DADOS.INVQTD, DADOS.INVVAL, CASE WHEN (ARMAZENAMENTO.CLOCMACODI IS NULL ) THEN 'N' ELSE 'S' END 
			FROM ( 
				SELECT TABELA.CLOCMACODI, TABELA.CMATEPSEQU, SUM(TABELA.QTD1) AS ALMQTD, SUM(TABELA.VAL1) AS ALMVAL, SUM(TABELA.QTD2) AS INVQTD, SUM(TABELA.VAL2) AS INVVAL 
				FROM ( 
					-- Retorna dados dos materiais que estão sendo usados pelo inventário em aberto
					SELECT CLOCMACODI, CMATEPSEQU, AARMATESTR AS QTD1, VARMATUMED AS VAL1, 0.00 AS QTD2, 0.0000 AS VAL2 
					FROM SFPC.TBARMAZENAMENTOMATERIAL 
					WHERE CLOCMACODI = $Localizacao 
					UNION ALL 
						-- Retorna dados dos materiais no inventário em aberto
						SELECT CLOCMACODI, CMATEPSEQU, 0.00, 0.0000, AINVMAESTO, VINVMAUNIT 
						FROM SFPC.TBINVENTARIOMATERIAL 
						WHERE (CLOCMACODI, AINVCOANOB, AINVCOSEQU) = (
							-- Retorna dados da situação do inventário em aberto, da localização
							SELECT A.CLOCMACODI, A.AINVCOANOB, MAX(A.AINVCOSEQU) AS AINVCOSEQU 
							FROM SFPC.TBINVENTARIOCONTAGEM A 
							WHERE A.CLOCMACODI=$Localizacao 
								AND A.FINVCOFECH IS NULL -- Inventário está aberto
								AND A.AINVCOANOB=( 
									-- Pega ano do último inventário da localização 
									-- Obs.: Ver se é necessário este select, pois apenas o último inventário pode ficar aberto
									SELECT MAX(AINVCOANOB) 
									FROM SFPC.TBINVENTARIOCONTAGEM 
									WHERE CLOCMACODI=$Localizacao
								) 
							GROUP BY A.CLOCMACODI,A.AINVCOANOB
						) 
				) AS TABELA 
				GROUP BY TABELA.CLOCMACODI, TABELA.CMATEPSEQU 
			) AS DADOS 
				LEFT OUTER JOIN SFPC.TBARMAZENAMENTOMATERIAL AS ARMAZENAMENTO 
					ON DADOS.CLOCMACODI = ARMAZENAMENTO.CLOCMACODI 
					AND DADOS.CMATEPSEQU = ARMAZENAMENTO.CMATEPSEQU, 
				SFPC.TBMATERIALPORTAL AS MATERIAL, 
				SFPC.TBUNIDADEDEMEDIDA AS UNIDADE 
			WHERE (DADOS.INVQTD <> DADOS.ALMQTD OR DADOS.ALMVAL = 0.0000) 
				AND DADOS.CMATEPSEQU = MATERIAL.CMATEPSEQU 
				AND MATERIAL.CUNIDMCODI = UNIDADE.CUNIDMCODI 
			ORDER BY MATERIAL.EMATEPDESC
		";
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
				$pdf->Cell(36,5,"          /          /", 1, 1, "L", 0);
				$pdf->ln(5);
		}

		# Linhas de Itens de Material #
		$rows = $res->numRows();
		if($rows == 0){
				$Mensagem = "Nenhuma Ocorrência Encontrada";
				$Url = "RelInventarioPeriodicoDiferencas.php?Mensagem=".urlencode($Mensagem)."&Mens=1&Tipo=1";
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
						$AlmoxarifadoValor[$i]      = $Linha[4];
						$InventarioQuantidade[$i]   = $Linha[5];
						$InventarioValor[$i]        = $Linha[6];
						$Armazenamento[$i]          = $Linha[7];

						# Montando o array de Itens do Inventário #
						if($Ordem == 1){
								$DescGrupo[$i]  = RetiraAcentos($Linha[8]).$SimboloConcatenacaoDesc.str_replace("\"","”",$Linha[8]);
								$DescClasse[$i] = RetiraAcentos($Linha[9]).$SimboloConcatenacaoDesc.str_replace("\"","”",$Linha[9]);
								$Itens[$i]      = $DescGrupo[$i].$SimboloConcatenacaoArray.$DescClasse[$i].$SimboloConcatenacaoArray.$DescMaterial[$i].$SimboloConcatenacaoArray.$CodigoReduzido[$i].$SimboloConcatenacaoArray.$Unidade[$i].$SimboloConcatenacaoArray.$AlmoxarifadoQuantidade[$i].$SimboloConcatenacaoArray.$AlmoxarifadoValor[$i].$SimboloConcatenacaoArray.$InventarioQuantidade[$i].$SimboloConcatenacaoArray.$InventarioValor[$i].$SimboloConcatenacaoArray.$Armazenamento[$i];
						}else{
								$Itens[$i]      = $DescMaterial[$i].$SimboloConcatenacaoArray.$CodigoReduzido[$i].$SimboloConcatenacaoArray.$Unidade[$i].$SimboloConcatenacaoArray.$AlmoxarifadoQuantidade[$i].$SimboloConcatenacaoArray.$AlmoxarifadoValor[$i].$SimboloConcatenacaoArray.$InventarioQuantidade[$i].$SimboloConcatenacaoArray.$InventarioValor[$i].$SimboloConcatenacaoArray.$Armazenamento[$i];
						}
				}
		}
}
$db->disconnect();

# Escrevendo o Relatório #
$DescGrupoAntes     = "";
$DescCalsseAntes    = "";

sort($Itens);
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
				$AlmoxarifadoValor      = $Dados[6];
				$InventarioQuantidade   = $Dados[7];
				$InventarioValor        = $Dados[8];
				$Armazenamento          = $Dados[9];

				# Pega a descrição do Grupo com acento #
				$DescricaoG = split($SimboloConcatenacaoDesc,$DescGrupo);
				$DescGrupo = $DescricaoG[1];

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
				$DescGrupoAntes         = $DescGrupo;
				$DescClasseAntes        = $DescClasse;
		}else{
				$DescMaterial           = $Dados[0];
				$CodigoReduzido         = $Dados[1];
				$Unidade                = $Dados[2];
				$AlmoxarifadoQuantidade = $Dados[3];
				$AlmoxarifadoValor      = $Dados[4];
				$InventarioQuantidade   = $Dados[5];
				$InventarioValor        = $Dados[6];
				$Armazenamento          = $Dados[7];
				if($i == 0){
						$pdf->Cell(83,5,"DESCRIÇÃO DO ITEM",1,0,"L",1);
						$pdf->Cell(10,5,"UNID.",1, 0,"C",1);
						$pdf->Cell(16,5,"CÓD.RED.",1,0,"C",1);
						$pdf->Cell(27,5,"QTD.ESTOQUE",1,0,"C",1);
						$pdf->Cell(27,5,"QTD.INVENTÁRIO",1,0,"C",1);
						$pdf->Cell(27,5,"VALOR UNITÁRIO",1,0,"C",1);
						$pdf->Cell(90,5,"JUSTIFICATIVA",1,1,"C",1);
				}
		}

		# Pega a descrição do Material com acento #
		$Descricao    = split($SimboloConcatenacaoDesc,$DescMaterial);
		$DescMaterial = $Descricao[1];

		# Quebra de Linha para Descrição do Material #
		$Fim    = 46; //52
		$Coluna = 83; //92 - Grande demais, corta palavras, pequeno, provoca espaços desnecessários
		$setX   = 10;
		$DescMaterialSepara = SeparaFrase($DescMaterial,$Fim);
		$TamDescMaterial    = $pdf->GetStringWidth($DescMaterialSepara);
		if($TamDescMaterial <= $Coluna){
				$LinhasMat = 1;
				$AlturaMat = 5;
		}elseif($TamDescMaterial > $Coluna and $TamDescMaterial <= 2*($Coluna-2) ){
				$LinhasMat = 2;
				$AlturaMat = 10;
		}elseif($TamDescMaterial > 2*($Coluna-2) and $TamDescMaterial <= 3*($Coluna-4) ){
				$LinhasMat = 3;
				$AlturaMat = 15;
		}elseif($TamDescMaterial > 3*($Coluna-4) and $TamDescMaterial <= 4*($Coluna-6) ){
				$LinhasMat = 4;
				$AlturaMat = 20;
		}elseif($TamDescMaterial > 4*($Coluna-6) and $TamDescMaterial <= 5*($Coluna-8) ) {
				$LinhasMat = 5;
				$AlturaMat = 25;
		}elseif($TamDescMaterial > 5*($Coluna-8) and $TamDescMaterial <= 6*($Coluna-10) ){
				$LinhasMat = 6;
				$AlturaMat = 30;
		}elseif($TamDescMaterial > 6*($Coluna-10) and $TamDescMaterial <= 7*($Coluna-10) ){
				$LinhasMat = 7;
				$AlturaMat = 35;
		}else{
				$LinhasMat = 8;
				$AlturaMat = 40;
		}
		if($TamDescMaterial > $Coluna){
				$Inicio = 0;
				$pdf->Cell(83,$AlturaMat,"",1,0,"L",0);
				for($Quebra = 0; $Quebra < $LinhasMat; $Quebra++){
						if($Quebra == 0){
								$pdf->SetX($setX);
								$pdf->Cell(83,5,trim(substr($DescMaterialSepara,$Inicio,$Fim)),0,0,"L",0);
								$pdf->Cell(10,$AlturaMat,$Unidade,1,0,"C",0);
								$pdf->Cell(16,$AlturaMat,$CodigoReduzido,1, 0,"C",0);
								if($Armazenamento=="S"){
										$pdf->Cell(27,$AlturaMat,str_replace(".",",",sprintf("%1.2f",$AlmoxarifadoQuantidade)),1, 0,"R",0);
								}else{
										$pdf->Cell(27,$AlturaMat,"Inexistente",1, 0,"R",0);
								}
								$pdf->Cell(27,$AlturaMat,str_replace(".",",",sprintf("%1.2f",$InventarioQuantidade)),1, 0,"R",0);
								if($AlmoxarifadoValor!=0){
										$pdf->Cell(27,$AlturaMat,str_replace(".",",",sprintf("%1.4f",$AlmoxarifadoValor)),1, 0,"R",0);
								}else{
										$pdf->Cell(27,$AlturaMat,"",1, 0,"R",0);
								}
								$pdf->Cell(90,$AlturaMat,"",1,0,"L",0);
								$pdf->Ln(5);
						}else{
								$pdf->Cell(83,5,trim(substr($DescMaterialSepara,$Inicio,$Fim)),0,0,"L",0);
								$pdf->Ln(5);
						}
						$Inicio = $Inicio + $Fim;
				}
				$pdf->Cell(83,0,"",1,1,"",0);
		}else{
				$pdf->Cell(83,5,$DescMaterial, 1,0, "L",0);
				$pdf->Cell(10,5,$Unidade,1,0,"C",0);
				$pdf->Cell(16,5,$CodigoReduzido,1, 0,"C",0);
				if($Armazenamento=="S"){
						$pdf->Cell(27,$AlturaMat,str_replace(".",",",sprintf("%1.2f",$AlmoxarifadoQuantidade)),1, 0,"R",0);
				}else{
						$pdf->Cell(27,$AlturaMat,"Inexistente",1, 0,"R",0);
				}
				$pdf->Cell(27,$AlturaMat,str_replace(".",",",sprintf("%1.2f",$InventarioQuantidade)),1, 0,"R",0);
				if($AlmoxarifadoValor!=0){
						$pdf->Cell(27,$AlturaMat,str_replace(".",",",sprintf("%1.4f",$AlmoxarifadoValor)),1, 0,"R",0);
				}else{
						$pdf->Cell(27,$AlturaMat,"",1, 0,"R",0);
				}
				$pdf->Cell(90,5,"",1,1,"L",0);
		}
}
# Mostra o totalizador de Itens do Inventário #
$pdf->Cell(190,5,"TOTAL DE ITENS", 1,0, "R",1);
$pdf->Cell(90,5,$rows, 1,1, "R",0);
$pdf->Output();
?>

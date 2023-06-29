<?php
#------------------------------------------------------------------------------------------------------------------
# Portal da DGCO
# Programa: RelInventarioFechamentoPdf.php
# Autor:    Carlos Abreu
# Data:     21/12/2006
# Objetivo: Programa de Impressão do Relatório de Conclusão do Inventário
#----------------------------------------
# Alterado: Rossana Lira
# Data:     24/05/2007 - Exibição da data de fechamento do inventário
# Alterado: Rodrigo Melo
# Data:     28/01/2008 - Alteração para exibir os dados de fechamento de inventário a partir do histórico do material, para não alterar o valor dos itens de consumo e permanente.
# Alterado: Rodrigo Melo
# Data:     03/03/2008 - Remoção da integração com a tabela de histórico, para que os dados sejam obtidos a partir dos materiais e não do histórico do material.
# Alterado: Ariston Cordeiro
# Data:     30/12/2010 - Adicionei um filtro para apenas mostrar materiais contados (zerados ou não) e os itens novos zerados
#----------------------------------------
# OBS.:     Tabulação 2 espaços
#------------------------------------------------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_cache_limiter('private_no_expire');
session_start();
Seguranca();

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "GET"){
		$Almoxarifado = $_GET['Almoxarifado'];
		$Localizacao  = $_GET['Localizacao'];
		$Ordem        = $_GET['Ordem'];
		$Ano          = $_GET['Ano'];
		$Sequencial   = $_GET['Sequencial'];
		$DataBase     = $_GET['DataBase'];
		$DataFecha    = $_GET['DataFecha'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

# Função exibe o Cabeçalho e o Rodapé #
CabecalhoRodapePaisagem();

# Informa o Título do Relatório #
if ($Ordem == "1"){
		$TituloRelatorio = "Relatório de Fechamento de Inventário (Ordem: Família)";
} else {
		$TituloRelatorio = "Relatório de Fechamento de Inventário (Ordem: Material)";
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
if( $Ordem == 1 ){
		# Pega os dados dos Materiais Cadastrados - Ordem Família #
		$sql  = "
			SELECT
				A.CMATEPSEQU, B.EMATEPDESC, C.EUNIDMSIGL, A.AINVMAESTO, A.VINVMAUNIT,
				GRUPO.EGRUMSDESC, CLASSE.ECLAMSDESC, GRUPO.FGRUMSTIPM
			FROM
				SFPC.TBINVENTARIOMATERIAL A
					INNER JOIN SFPC.TBMATERIALPORTAL B
						ON (A.CMATEPSEQU = B.CMATEPSEQU)
					INNER JOIN SFPC.TBUNIDADEDEMEDIDA C
						ON (B.CUNIDMCODI = C.CUNIDMCODI),
				SFPC.TBSUBCLASSEMATERIAL AS SUBCLASSE,
				SFPC.TBCLASSEMATERIALSERVICO AS CLASSE,
				SFPC.TBGRUPOMATERIALSERVICO AS GRUPO
			WHERE
				A.CLOCMACODI = $Localizacao
				AND A.AINVCOSEQU = $Sequencial
				AND A.AINVCOANOB = $Ano
				AND B.CSUBCLSEQU = SUBCLASSE.CSUBCLSEQU
				AND SUBCLASSE.CGRUMSCODI = CLASSE.CGRUMSCODI
				AND SUBCLASSE.CCLAMSCODI = CLASSE.CCLAMSCODI
				AND CLASSE.CGRUMSCODI = GRUPO.CGRUMSCODI
				-- pegar apenas materiais contados ou itens novos diferente de zero
				AND (
					(
						A.CMATEPSEQU IN (
							SELECT DISTINCT IR.CMATEPSEQU
							FROM SFPC.TBINVENTARIOREGISTRO IR
							WHERE
								IR.CLOCMACODI  = $Localizacao
								AND IR.AINVCOSEQU = $Sequencial
								AND IR.AINVCOANOB = $Ano
						)
					)
					or (A.AINVMAESTO > 0 )
				)
			ORDER BY GRUPO.EGRUMSDESC, CLASSE.ECLAMSDESC, B.EMATEPDESC
		";
}elseif( $Ordem == 2 ){
		# Pega os dados dos Materiais Cadastrados - Ordem Material #
		$sql  = "
			SELECT A.CMATEPSEQU, B.EMATEPDESC, C.EUNIDMSIGL, A.AINVMAESTO, A.VINVMAUNIT, E.FGRUMSTIPM
			FROM
				SFPC.TBINVENTARIOMATERIAL A
					INNER JOIN SFPC.TBMATERIALPORTAL B
						ON (A.CMATEPSEQU = B.CMATEPSEQU)
					INNER JOIN SFPC.TBUNIDADEDEMEDIDA C
				    ON (B.CUNIDMCODI = C.CUNIDMCODI)
					INNER JOIN SFPC.TBSUBCLASSEMATERIAL D
						ON B.CSUBCLSEQU = D.CSUBCLSEQU
					INNER JOIN SFPC.TBGRUPOMATERIALSERVICO E
						ON D.CGRUMSCODI = E.CGRUMSCODI
			WHERE A.CLOCMACODI = $Localizacao
				AND A.AINVCOSEQU = $Sequencial
				AND A.AINVCOANOB = $Ano
				-- pegar apenas materiais contados ou itens novos diferente de zero
				AND (
					(
						A.CMATEPSEQU IN (
							SELECT DISTINCT IR.CMATEPSEQU
							FROM SFPC.TBINVENTARIOREGISTRO IR
							WHERE
								IR.CLOCMACODI  = $Localizacao
								AND IR.AINVCOSEQU = $Sequencial
								AND IR.AINVCOANOB = $Ano
						)
					)
					or (A.AINVMAESTO > 0 )
				)
			ORDER BY B.EMATEPDESC
		";
}

$res = $db->query($sql);
if( PEAR::isError($res) ){
    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nsql: $sql");
}else{
		# Pega as informações do Almoxarifado #
		$sqlalmo = "SELECT EALMPODESC FROM SFPC.TBALMOXARIFADOPORTAL WHERE CALMPOCODI = $Almoxarifado";
		$resalmo = $db->query($sqlalmo);
		if( PEAR::isError($resalmo) ){
		    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nsql: $sqlalmo");
		}else{
				$Almox     = $resalmo->fetchRow();
				$DescAlmox = $Almox[0];

				$pdf->Cell(30,5,"ALMOXARIFADO",1,0,"L",1);
				$pdf->Cell(156,5,"$DescAlmox",1,0,"L",0);
				$pdf->Cell(20,5,"DATA BASE", 1, 0, "L", 1);
				$pdf->Cell(20,5,$DataBase, 1, 0, "C", 0);
				$pdf->Cell(34,5,"DATA FECHAMENTO", 1, 0, "L", 1);
				$pdf->Cell(20,5,$DataFecha, 1, 1, "C", 0);
				$pdf->ln(5);
		}

		# Linhas de Itens de Material #
		$rows = $res->numRows();
		if( $rows == 0 ){
				$Mensagem = "Nenhuma Ocorrência Encontrada";
				$Url = "RelInventarioFechamento.php?Mensagem=".urlencode($Mensagem)."&Mens=1&Tipo=1";
				if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
				header("location: ".$Url);
				exit;
		}else{
				for( $i=0; $i< $rows; $i++ ){
						$Linha              = $res->fetchRow();
						$CodigoReduzido[$i] = $Linha[0];
						$DescMaterial[$i]   = RetiraAcentos($Linha[1]).$SimboloConcatenacaoDesc.str_replace("\"","”",$Linha[1]);
						$Unidade[$i]   		  = $Linha[2];
						$InventarioQuantidade[$i]   = $Linha[3];
						$InventarioValor[$i]        = $Linha[4];

						# Montando o array de Itens do Inventário #
						if( $Ordem == 1 ){
								$DescGrupo[$i]  = RetiraAcentos($Linha[5]).$SimboloConcatenacaoDesc.str_replace("\"","”",$Linha[5]);
								$DescClasse[$i] = RetiraAcentos($Linha[6]).$SimboloConcatenacaoDesc.str_replace("\"","”",$Linha[6]);
								$InventarioTipoMat[$i] = $Linha[7];
								$Itens[$i] = $DescGrupo[$i].$SimboloConcatenacaoArray.$DescClasse[$i].$SimboloConcatenacaoArray.$DescMaterial[$i].$SimboloConcatenacaoArray.$CodigoReduzido[$i].$SimboloConcatenacaoArray.$Unidade[$i].$SimboloConcatenacaoArray.$InventarioQuantidade[$i].$SimboloConcatenacaoArray.$InventarioValor[$i].$SimboloConcatenacaoArray.$InventarioTipoMat[$i];
						}else{
								$InventarioTipoMat[$i] = $Linha[5];
								$Itens[$i] = $DescMaterial[$i].$SimboloConcatenacaoArray.$CodigoReduzido[$i].$SimboloConcatenacaoArray.$Unidade[$i].$SimboloConcatenacaoArray.$InventarioQuantidade[$i].$SimboloConcatenacaoArray.$InventarioValor[$i].$SimboloConcatenacaoArray.$InventarioTipoMat[$i];
						}
				}
		}
}
$db->disconnect();

# Escrevendo o Relatório #
$DescGrupoAntes     = "";
$DescCalsseAntes    = "";

sort($Itens);
for( $i=0; $i< count($Itens); $i++ ){
		# Extrai os dados do Array de Itens #
		$Dados = explode($SimboloConcatenacaoArray,$Itens[$i]);

		if( $Ordem == 1 ){
				$DescGrupo              = $Dados[0];
				$DescClasse             = $Dados[1];
				$DescMaterial           = $Dados[2];
				$CodigoReduzido         = $Dados[3];
				$Unidade   		        = $Dados[4];
				$InventarioQuantidade   = $Dados[5];
				$InventarioValor        = $Dados[6];
				$InventarioTipoMat      = $Dados[7];

				$QtdTotal += $InventarioQuantidade;
				$ValorTotal += $InventarioQuantidade*$InventarioValor;

				# Pega a descrição do Grupo com acento #
				$DescricaoG = explode($SimboloConcatenacaoDesc,$DescGrupo);
				$DescGrupo = $DescricaoG[1];

				# Pega a descrição do Grupo com acento #
				$DescricaoC = explode($SimboloConcatenacaoDesc,$DescClasse);
				$DescClasse = $DescricaoC[1];

				if( $DescGrupoAntes != $DescGrupo or ( $DescGrupoAntes == $DescGrupo and $DescClasseAntes != $DescClasse ) ){
						$pdf->Cell(30,5,"GRUPO / CLASSE",1,0,"L",1);
						$pdf->Cell(250,5,$DescGrupo." / ".$DescClasse,1,1,"L",0);
						$pdf->Cell(169,5,"DESCRIÇÃO DO ITEM",1,0,"L",1);
						$pdf->Cell(13,5,"UNID",1, 0,"C",1);
						$pdf->Cell(17,5,"CÓD. RED.",1,0,"C",1);
						$pdf->Cell(27,5,"QTD. INVENT.",1,0,"C",1);
						$pdf->Cell(27,5,"VALOR UNITÁRIO",1,0,"C",1);
						$pdf->Cell(27,5,"VALOR TOTAL",1,1,"C",1);
				}
				$DescGrupoAntes     = $DescGrupo;
				$DescClasseAntes    = $DescClasse;
		}else{
				$DescMaterial           = $Dados[0];
				$CodigoReduzido         = $Dados[1];
				$Unidade   		        = $Dados[2];
				$InventarioQuantidade   = $Dados[3];
				$InventarioValor        = $Dados[4];
				$InventarioTipoMat      = $Dados[5];

				$QtdTotal += $InventarioQuantidade;
				$ValorTotal += $InventarioQuantidade*$InventarioValor;

				if( $i == 0 ){
						$pdf->Cell(169,5,"DESCRIÇÃO DO ITEM",1,0,"L",1);
						$pdf->Cell(13,5,"UNID",1, 0,"C",1);
						$pdf->Cell(17,5,"CÓD. RED.",1,0,"C",1);
						$pdf->Cell(27,5,"QTD. INVENT.",1,0,"C",1);
						$pdf->Cell(27,5,"VALOR UNITÁRIO",1,0,"C",1);
						$pdf->Cell(27,5,"VALOR TOTAL",1,1,"C",1);
				}
		}
		switch ($InventarioTipoMat){
			case 'C':
				$ValorTotalConsumo += $InventarioQuantidade*$InventarioValor;
				break;
			case 'P':
				$ValorTotalPermanente += $InventarioQuantidade*$InventarioValor;
				break;
		}

		# Pega a descrição do Material com acento #
		$Descricao    = explode($SimboloConcatenacaoDesc,$DescMaterial);
		$DescMaterial = $Descricao[1];

		# Quebra de Linha para Descrição do Material #
		$DescMaterialSepara = SeparaFrase($DescMaterial,75);
		$TamDescMaterial    = $pdf->GetStringWidth($DescMaterialSepara);
		if( $TamDescMaterial <= 138 ){
				$LinhasMat = 1;
				$AlturaMat = 5;
		}elseif( $TamDescMaterial > 138 and $TamDescMaterial <= 276 ){
				$LinhasMat = 2;
				$AlturaMat = 10;
		}elseif( $TamDescMaterial > 276 and $TamDescMaterial <= 414 ){
				$LinhasMat = 3;
				$AlturaMat = 15;
		}else{
				$LinhasMat = 4;
				$AlturaMat = 20;
		}
		if( $TamDescMaterial > 137 ){
				$Inicio = 0;
				$pdf->Cell(169,$AlturaMat,"",1,0,"L",0);
				for( $Quebra = 0; $Quebra < $LinhasMat; $Quebra++ ){
						if( $Quebra == 0 ){
					  		$pdf->SetX(10);
					  		$pdf->Cell(169,5,trim(substr($DescMaterialSepara,$Inicio,75)),0,0,"L",0);
								$pdf->Cell(13,$AlturaMat,$Unidade,1,0,"C",0);
								$pdf->Cell(17,$AlturaMat,$CodigoReduzido,1, 0,"C",0);
								$pdf->Cell(27,$AlturaMat,converte_quant($InventarioQuantidade),1,0,"R",0);
								$pdf->Cell(27,$AlturaMat,converte_valor_estoques($InventarioValor),1,0,"R",0);
								$pdf->Cell(27,$AlturaMat,converte_valor_estoques($InventarioQuantidade*$InventarioValor),1,0,"R",0);
								$pdf->Ln(5);
						}elseif( $Quebra == 1 ){
								$pdf->Cell(169,5,trim(substr($DescMaterialSepara,$Inicio,79)),0,0,"L",0);
					  		$pdf->Ln(5);
					  }elseif( $Quebra == 2 ){
								$pdf->Cell(169,5,trim(substr($DescMaterialSepara,$Inicio,79)),0,0,"L",0);
								$pdf->Ln(5);
					 }elseif( $Quebra == 3 ){
								$pdf->Cell(169,5,trim(substr($DescMaterialSepara,$Inicio,79)),0,0,"L",0);
								$pdf->Ln(5);
					 }else{
						  	$pdf->Cell(169,5,trim(substr($DescMaterialSepara,$Inicio,79)),0,0,"L",0);
								$pdf->Ln(5);
					  }
						$Inicio = $Inicio + 75;
			  }
	  		$pdf->Cell(169,0,"",1,1,"",0);
		}else{
				$pdf->Cell(169,5,$DescMaterial, 1,0, "L",0);
				$pdf->Cell(13,5,$Unidade,1,0,"C",0);
				$pdf->Cell(17,5,$CodigoReduzido,1, 0,"C",0);
				$pdf->Cell(27,$AlturaMat,converte_quant($InventarioQuantidade),1, 0,"R",0);
				$pdf->Cell(27,$AlturaMat,converte_valor_estoques($InventarioValor),1, 0,"R",0);
				$pdf->Cell(27,$AlturaMat,converte_valor_estoques($InventarioQuantidade*$InventarioValor),1,1,"R",0);
		}
}
# Mostra o totalizador de Itens do Inventário #
$pdf->Cell(142,5,"TOTAL DE ITENS", 1,0, "R",1);
$pdf->Cell(27,5,$rows, 1,0, "R",0);
$pdf->Cell(30,5,"QUANT. TOTAL", 1,0, "R",1);
$pdf->Cell(27,5,converte_quant($QtdTotal), 1,0, "R",0);
$pdf->Cell(27,5,"VALOR TOTAL", 1,0, "R",1);
$pdf->Cell(27,5,converte_valor_estoques($ValorTotal), 1,1, "R",0);
$pdf->Ln(3);
$pdf->Cell(253,5,"VALOR TOTAL CONSUMO", 1,0, "L",1);
$pdf->Cell(27,5,converte_valor_estoques($ValorTotalConsumo), 1,1, "R",0);
$pdf->Cell(253,5,"VALOR TOTAL PERMANENTE", 1,0, "L",1);
$pdf->Cell(27,5,converte_valor_estoques($ValorTotalPermanente), 1,1, "R",0);
$pdf->Output();
?>

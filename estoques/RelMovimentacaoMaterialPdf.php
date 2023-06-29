<?php
# ------------------------------------------------------------------------------------
# Portal da DGCO
# Programa: RelMovimentacaoMaterialPdf.php
# Objetivo: Programa de Impressão da Movimentação de Material
# Autor:    Rossana Lira
# Data:     24/08/2005
# OBS.:     Tabulação 2 espaços
# ------------------------------------------------------------------------------------
# Alterado: Carlos Abreu
# Data:     07/05/2007 - Ajuste para que as movimentações (0,5) não possam ser exibidas
# ------------------------------------------------------------------------------------
# Alterado: Ariston Cordeiro
# Data:     29/12/2008 - Ajuste para que as movimentação 5 possa ser exibida, para corrigir bug em que movimentações tipo 5 não são contadas nos cálculos da quantidade em estoque.
# ------------------------------------------------------------------------------------
# Alterado: Rodrigo Melo
# Data:     23/04/2009 - Resolvendo CR770 - Colocando filtro para o tipo de movimentação (entrada, saída ou todos)
# ------------------------------------------------------------------------------------
# Alterado: Ariston Cordeiro
# Data:     22/10/2009 - CR770- Novo campo mostrando o tipo de movimentação escolhido: entrada, saída ou entrada e saída.
# ------------------------------------------------------------------------------------
# Alterado: Lucas Baracho
# Data:		24/10/2018
# Objetivo: Tarefa Redmine 205790
# ------------------------------------------------------------------------------------


# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_cache_limiter('private_no_expire');
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/estoques/RelMovimentacaoMaterial.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "GET"){
	$Almoxarifado = $_GET['Almoxarifado'];
	$Localizacao  = $_GET['Localizacao'];
	$TipoMovimentacao = $_GET['TipoMovimentacao'];
	$Material     = $_GET['Material'];
	$DataFim      = $_GET['DataFim'];
	$DataIni      = $_GET['DataIni'];
}

$MAX_NO_CARACTERES_DESCRICAO = 45; //máximo número de caracteres por linha em uma descrição
$Y_ABAIXO_CABECALHO = 39; //y inicial para as células, depois do cabeçalho, a partir da 2a folha
$H_LINHA = 5; //altura padrão de uma linha
$W_CELL1 = 17; //largura da 1a célula
$W_CELL2 = 20; //largura da 2a célula
$W_CELL3 = 93;
$W_CELL4 = 20;
$W_CELL5 = 20;
$W_CELL6 = 20;
$X1_CELL1 = 10; //x do início da 1a célula
$X1_CELL2 = $X1_CELL1+$W_CELL1; //x do início da 2a célula
$X1_CELL3 = $X1_CELL2+$W_CELL2;
$X1_CELL4 = $X1_CELL3+$W_CELL3;
$X1_CELL5 = $X1_CELL4+$W_CELL4;
$X1_CELL6 = $X1_CELL5+$W_CELL5;

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

$ErroAssunto = "Relatório de Movimentação de Material";

# Função exibe o Cabeçalho e o Rodapé #
CabecalhoRodape();

# Informa o Título do Relatório #
$TituloRelatorio = "Relatório de Movimentação de Material";

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

# Datas para consulta no banco de dados #
$DataInibd = substr($DataIni,6,4)."-".substr($DataIni,3,2)."-".substr($DataIni,0,2);
$DataFimbd = substr($DataFim,6,4)."-".substr($DataFim,3,2)."-".substr($DataFim,0,2);

# Pega os dados do almoxarifado #
$db   = Conexao();
$sql = "SELECT EALMPODESC FROM SFPC.TBALMOXARIFADOPORTAL WHERE CALMPOCODI = $Almoxarifado ";
$res = $db->query($sql);
if( PEAR::isError($res) ){
	//ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
	EmailErroSQL($ErroAssunto,$ErroPrograma,__LINE__,"Falha no select", $sql, $res);
}else{
	$Campo            = $res->fetchRow();
	$DescAlmoxarifado = $Campo[0];
}
$pdf->Cell(30,$H_LINHA,"Almoxarifado",1,0,"L",1);
$pdf->Cell(160,$H_LINHA,$DescAlmoxarifado,1,1,"L",0);

# Pega os dados do Material #
$db   = Conexao();
$sql  = "SELECT A.EMATEPDESC, B.EUNIDMSIGL ";
$sql .= "  FROM SFPC.TBMATERIALPORTAL A, SFPC.TBUNIDADEDEMEDIDA B ";
$sql .= " WHERE A.CMATEPSEQU = $Material AND A.CUNIDMCODI = B.CUNIDMCODI";
$res  = $db->query($sql);
if( PEAR::isError($res) ){
	//ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
	EmailErroSQL($ErroAssunto,$ErroPrograma,__LINE__,"Falha no select", $sql, $res);
}else{
	$Linha        = $res->fetchRow();
	$DescMaterial = $Linha[0];
	$Unidade      = $Linha[1];
	# Pega os dados da Movimentação #
	$Sql  = "
		SELECT
			A.DMOVMAMOVI, A.CMOVMACODI, A.AMOVMAQTDM, A.VMOVMAVALO, B.FTIPMVTIPO,
			B.ETIPMVDESC, D.CREQMACODI, D.AREQMAANOR, E.AENTNFNOTA, E.AENTNFSERI,
			E.FENTNFCANC, A.CMOVMACODT
		FROM
			SFPC.TBARMAZENAMENTOMATERIAL C,
			SFPC.TBTIPOMOVIMENTACAO B,
			SFPC.TBMOVIMENTACAOMATERIAL A
				LEFT OUTER JOIN SFPC.TBENTRADANOTAFISCAL  E
					ON (
						A.CALMPOCODI = E.CALMPOCODI AND
						A.AENTNFANOE = E.AENTNFANOE AND
						A.CENTNFCODI = E.CENTNFCODI
					)
				LEFT OUTER JOIN SFPC.TBREQUISICAOMATERIAL D
					ON (A.CREQMASEQU = D.CREQMASEQU)
		WHERE
			A.CALMPOCODI = $Almoxarifado AND
			C.CLOCMACODI = $Localizacao AND
			A.CMATEPSEQU = $Material AND
			A.CTIPMVCODI NOT IN (0) AND
			A.DMOVMAMOVI >= '$DataInibd' AND
			A.DMOVMAMOVI <= '$DataFimbd' ";
			$Sql .= " AND A.CMATEPSEQU = C.CMATEPSEQU AND
			A.CTIPMVCODI = B.CTIPMVCODI AND
			(
				A.FMOVMASITU IS NULL OR
				A.FMOVMASITU = 'A'
			)"; // Apresentar só as movimentações ativas
	$Sql .= " ORDER BY A.DMOVMAMOVI, A.CMOVMACODI ";
	$res  = $db->query($Sql);
	if( PEAR::isError($res) ){
		//ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $Sql");
		EmailErroSQL($ErroAssunto,$ErroPrograma,__LINE__,"Falha no select", $Sql, $res);
	}else{
		$rows = $res->numRows();
		if( $rows == 0 ){
			$Mensagem = "Nenhuma Ocorrência Encontrada";
			$Url = "RelMovimentacaoMaterial.php?Mensagem=".urlencode($Mensagem)."&Mens=1&Tipo=1";
			if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
			header("location: ".$Url);
			exit;
		}else{
			if( $TipoMovimentacao == "E" ){
				$TipoMovimentoStr1 = "ENTRADAS";
			}elseif( $TipoMovimentacao == "S" ){
				$TipoMovimentoStr1 = "SAÍDAS";
			}else{
				$TipoMovimentoStr1 = "ENTRADAS E SAÍDAS";
			}

			$pdf->Cell(30,$H_LINHA,"Período",1,0,"L",1);
			$pdf->Cell(160,$H_LINHA,$DataIni." a ".$DataFim,1,1,"L",0);
			$pdf->Cell(30,$H_LINHA,"Tipo Movimentação",1,0,"L",1);
			$pdf->Cell(160,$H_LINHA,$TipoMovimentoStr1,1,1,"L",0);
			$pdf->Cell(190,$H_LINHA,"Código Reduzido / Material / Unidade",1,1,"C",1);
			$pdf->MultiCell(190,$H_LINHA,$Material." / ".$DescMaterial." / ".$Unidade,1,"J",0);
			$pdf->ln(5);
			$QtdeEstoque	= 0;

			for( $i=0; $i< $rows; $i++ ){
				$Linha = $res->fetchRow();
				$DataMovimentoBanco = $Linha[0]; // data de movimento não tratado, no formato do banco
				$DataMovimento	= DataBarra($DataMovimentoBanco);
				$CodMovimento  	= $Linha[1];
				$QtdeMovimentoBanco= $Linha[2];// quantidade de movimento não tratado, no formato do banco
				$QtdeMovimento	= converte_quant(sprintf("%01.2f",str_replace(",",".",$Linha[2])));
				$ValorMovimento = converte_valor_estoques(sprintf("%01.4f",str_replace(",",".",$Linha[3])));
				$TipoMovimento	= $Linha[4];
				if( $TipoMovimento == "E" ){
						$TipoMovimentoStr = "ENTRADA";
				}elseif( $TipoMovimento == "S" ){
						$TipoMovimentoStr = "SAÍDA";
				}
				$DescMovimento = $Linha[5];
				$Requisicao	   = $Linha[6];
				$AnoRequisicao = $Linha[7];
				$NotaNumero    = $Linha[8];
				$NotaSerie     = $Linha[9];
				$NotaCancelada = $Linha[10];
				$CodMovimentoT = $Linha[11];

				if( $i == 0 ){
					$pdf->Cell($W_CELL1,$H_LINHA,"Data", 1, 0, "C", 1);
					$pdf->Cell($W_CELL2,$H_LINHA,"Movimento", 1, 0, "C", 1);
					$pdf->Cell($W_CELL3,$H_LINHA,"Descrição do movimento", 1, 0, "L", 1);
					$pdf->Cell($W_CELL4,$H_LINHA,"Quantidade", 1, 0, "C", 1);
					$pdf->Cell($W_CELL5,$H_LINHA,"Valor Unit.", 1, 0, "C", 1);
					$pdf->Cell($W_CELL6,$H_LINHA,"Qtde Estoque", 1, 1, "C", 1);

					# Pega a quantidade do material no almoxarifado antes da 1a movimentação

					## Soma das quantidades das movimentações de entrada e saída
					$SqlSoma  = "
						SELECT
							SUM(
								CASE
									WHEN TM.FTIPMVTIPO = 'S'
									THEN -MM.AMOVMAQTDM
									ELSE
										CASE
											WHEN TM.FTIPMVTIPO = 'E'
											THEN MM.AMOVMAQTDM
											ELSE 0
											END
									END
							) AS SUBTESTOQUE
						FROM
							SFPC.TBMOVIMENTACAOMATERIAL MM,
							SFPC.TBTIPOMOVIMENTACAO TM
						WHERE
							MM.CALMPOCODI = $Almoxarifado
							AND MM.CMATEPSEQU = $Material
							AND MM.DMOVMAMOVI >= '$DataMovimentoBanco'
							AND TM.CTIPMVCODI = MM.CTIPMVCODI
							AND (MM.FMOVMASITU IS NULL OR MM.FMOVMASITU = 'A')
					";
					$resSoma  = $db->query($SqlSoma);
					if( PEAR::isError($resSoma) ){
						EmailErroSQL($ErroAssunto,$ErroPrograma,__LINE__,"Falha no select", $SqlSoma, $resSoma);
					}
					$LinhaSoma = $resSoma->fetchRow();
					$SomaQtdeMovimentacoes = $LinhaSoma[0];

					## Quantidade atual no estoque
					$SqlEstoque  = "
						SELECT AARMATQTDE
						FROM SFPC.TBARMAZENAMENTOMATERIAL
						WHERE CMATEPSEQU = $Material AND CLOCMACODI = $Localizacao
					";
					$resSEstoque  = $db->query($SqlEstoque);
					if( PEAR::isError($resSEstoque) ){
						EmailErroSQL($ErroAssunto,$ErroPrograma,__LINE__,"Falha no select", $SqlEstoque, $resSEstoque);
					}
					$LinhaEstoque = $resSEstoque->fetchRow();
					$QtdeEstoqueAtual = $LinhaEstoque[0];

					## Cálculo da quantidade do estoque antes da 1a movimentação
					$QtdeEstoqueInicial = $QtdeEstoqueAtual - $SomaQtdeMovimentacoes;
					/*echo "[".$QtdeEstoqueInicial."]";
					echo "[".$QtdeEstoqueAtual."]";
					echo "[".$SomaQtdeMovimentacoes."]";*/
					$QtdeEstoque = $QtdeEstoqueInicial;
				}

				if     ( $Requisicao != "" ){
					$Add = " - ".substr($Requisicao+100000,1)."/$AnoRequisicao";
				}elseif( $NotaNumero != "" ){
					$Add = " - ".$NotaNumero."/".$NotaSerie;
				}else{
					$Add = " - ".$CodMovimentoT;
				}
				if($TipoMovimento == "S"){
					$QtdeEstoque -= $QtdeMovimentoBanco;
				} else if($TipoMovimento == "E"){
					$QtdeEstoque += $QtdeMovimentoBanco;
				}


                if( ($TipoMovimentacao == null) || ($TipoMovimentacao == $TipoMovimento)) { //Caso seja definido um tipo de movimentacao - E: Entrada e S: Saída, mostrar apenas as movimentações de entrada ou de saída. Caso não seja definido, exibir todas as movimentações (entradas e saídas).

					$Descricao = $DescMovimento.$Add;
					$AlturaNormal = $H_LINHA;

					$pdf->SetX($X1_CELL3);
					$y1=$pdf->GetY();
					$x1=$pdf->GetX();
					$pdf->MultiCell($W_CELL3,$AlturaNormal,$Descricao,1,"L",0);
					$y2=$pdf->GetY();
					if($y2<$y1){ //célula foi escrita na proxima página (pois alcancou final da página atual)
						$y1=$Y_ABAIXO_CABECALHO; // escrever células no início da próxima folha
					}
					$AlturaMultiCell= $y2-$y1;
					$AlturaLinhaNoFloat = $AlturaMultiCell /5;
					$AlturaLinhaNoFloat -= 0.01; //corrigir erro de arredondamento de ceil para variáveis com valores redondos
					$AlturaLinhaNo = ceil ($AlturaLinhaNoFloat);
					$AlturaLinha = $AlturaNormal*$AlturaLinhaNo;
					$pdf->SetY($y1);
					$pdf->SetX($X1_CELL1);

					if( $DataMovimento <> $DataMovAnt ) {
						$pdf->Cell($W_CELL1,$AlturaLinha,"$DataMovimento",1,0,"C",0);
						$DataMovAnt	= $DataMovimento;
					}else{
						$pdf->Cell($W_CELL1,$AlturaLinha,"",1,0,"C",0);
					}

					$pdf->Cell($W_CELL2,$AlturaLinha,$TipoMovimentoStr,1,0,"L",0);
					$pdf->SetY($y1);
					$pdf->SetX($X1_CELL4);
					$pdf->Cell($W_CELL4,$AlturaLinha,$QtdeMovimento,1,0,"R",0);
					$pdf->Cell($W_CELL5,$AlturaLinha,$ValorMovimento,1,0,"R",0);
					$pdf->Cell($W_CELL6,$AlturaLinha,converte_quant(sprintf("%01.2f",str_replace(",",".",$QtdeEstoque))),1,1,"R",0);
					$Add = "";
			    }
			}
		}
	}
}
$db->disconnect();
$pdf->Output();
?>

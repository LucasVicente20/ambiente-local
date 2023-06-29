<?php
    #-------------------------------------------------------------------------
    # Portal da DGCO
    # Programa: ConsAgrupamento.php
    # Autor:    José Almir <jose.almir@pitang.com>
    # Data:     02/07/2014 - [CR125346]: REDMINE 73
    # Objetivo: Consultar agrupamentos
    
    $programa = "ConsAgrupamento.php";
    
    # Acesso ao arquivo de funções #
    require_once 'funcoesCompras.php';
    
    # Executa o controle de segurança #
    session_start();
    
    Seguranca();
    
    AddMenuAcesso('/compras/ConsAcompSolicitacaoCompra.php');
    
    # Abrindo Conexao #
    $db = Conexao();
    
    # Identifica o Programa para Erro de Banco de Dados #
    $ErroPrograma = __FILE__;
    
    $Orgao = '';
    
    # Variáveis com o global off #
    if (isset($_SESSION['Botao'])) {
        $Botao = $_SESSION['Botao'];
        $idSolicitacao = $_SESSION['idSolicitacao'];
    }
    
    if ($Botao == "Imprimir" && $idSolicitacao != "") {
//         echo '<pre>';
//         var_dump(array($Botao, $idSolicitacao));
//         die;
        
        # Função exibe o Cabeçalho e o Rodapé #
        CabecalhoRodapePaisagem();
        
        # Informa o Título do Relatório #
        $TituloRelatorio = "DETALHE AGRUPAMENTO - SOLICITAÇÃO DE COMPRA E CONTRATAÇÃO (SCC)";
        
        # Cria o objeto PDF, o Default é formato Retrato, A4  e a medida em milímetros #
        $pdf = new PDF("L", "mm", "A4");
        
        # Define um apelido para o número total de páginas #
        $pdf->AliasNbPages();
        
        # Define as cores do preenchimentos que serão usados #
        $pdf->SetFillColor(220, 220, 220);
        
        # Adiciona uma página no documento #
        $pdf->AddPage();
        
        # Seta as fontes que serão usadas na impressão de strings #
        $pdf->SetFont("Arial", "", 7);
        
        $db = Conexao();
        
        $sql = "SELECT
                    SOL.CSOLCOSEQU ,
                    SOL.TSOLCODATA ,
                    SOL.CORGLICODI ,
                    ORG.EORGLIDESC ,
                    SOL.CSITSOCODI ,
                    SSO.ESITSONOME ,
                    GRU.CAGSOLSEQU ,
                    GRU.FAGSOLFLAG ,
                    GRU.TAGSOLULAT ,
                    CEN.ECENPODESC ,
                    CEN.ECENPODETA
                FROM
                    SFPC.TBSOLICITACAOCOMPRA AS SOL JOIN SFPC.TBORGAOLICITANTE AS ORG
                        ON SOL.CORGLICODI = ORG.CORGLICODI JOIN SFPC.TBSITUACAOSOLICITACAO AS SSO
                        ON SOL.CSITSOCODI = SSO.CSITSOCODI JOIN SFPC.TBAGRUPASOLICITACAO AS GRU
                        ON SOL.CSOLCOSEQU = GRU.CSOLCOSEQU JOIN SFPC.TBCENTROCUSTOPORTAL AS CEN
                        ON SOL.CCENPOSEQU = CEN.CCENPOSEQU
                WHERE
                    SOL.CTPCOMCODI = 2
                    AND SOL.FSOLCORGPR = 'S'
                    AND GRU.CAGSOLSEQU = $idSolicitacao";
        
        $res = $db->query($sql);
        if (PEAR::isError($res)) {
            $CodErroEmail  = $res->getCode();
            $DescErroEmail = $res->getMessage();
            ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
        } else {
            $CodAgrupamentoAntes = "";
            $ContagemGrupo = 0;
            $DescOrgaoAntes = "";
            $DescCentroCustoAntes = "";
            
            $pdf->SetFillColor(150, 150, 150);
            $pdf->Cell(280, 5, "SOLICITAÇÕES AGRUPADAS", 1, 1, "C", 1);
            $pdf->SetFillColor(220, 220, 220);
            
            $arrayItens = new ArrayObject();
            $nomeOrgaoGestor = "";
            
            while ($Linha = $res->fetchRow()) {
                $CodSolicitacao  = $Linha[0]; 			 // SOL.CSOLCOSEQU, /* CÓDIGO SEQUENCIAL DA SOLICITAÇÃO DE COMPRA */
                $DataSolicitacao = DataBarra($Linha[1]); // SOL.TSOLCODATA, /* DATA E HORA DA SOLICITAÇÃO DE COMPRA */
                $CodOrgao        = $Linha[2]; 			 // SOL.CORGLICODI, /* CÓDIGO DO ÓRGÃO */
                $DescOrgao  	 = $Linha[3]; 			 // ORG.EORGLIDESC, /* DESCRIÇÃO DO ÓRGÃO LICITANTE */
                $CodSituacao	 = $Linha[4];			 // SOL.CSITSOCODI, /* SITUAÇÃO ATUAL DA SOLICITAÇÃO */
                $DescSolicitacao = $Linha[5];			 // SSO.ESITSONOME, /* DESCRIÇÃO DA SOLICITAÇÃO DA LICITAÇÃO */
                $CodAgrupamento  = $Linha[6];			 // GRU.CAGSOLSEQU, /* CÓDIGO SEQUENCIAL DO AGRUPAMENTO DAS LICITAÇÕES */
                $FlagGrupo		 = $Linha[7]; 			 // GRU.FAGSOLFLAG, /* FLAG QUE INDICA A SCC COM O ÓRGÃO GESTOR RESPONSÁVEL PELO AGRUPAMENTO - S/N */
                $DataAgrupamento = DataBarra($Linha[8]); // GRU.TAGSOLULAT  /* DATA E HORA DA ÚLTIMA ATUALIZAÇÃO */
                $DescCentroCusto = $Linha[9];			 // CEN.ECENPODESC, /* DESCRIÇÃO DO CENTRO DE CUSTO SFPC */
                $DetaCentroCusto = $Linha[10];			 // CEN.ECENPODETA, /* DESCRIÇÃO DO DETALHAMENTO DO CENTRO DE CUSTO SFPC */
                
                if ($DescOrgaoAntes != $DescOrgao) {
                    $pdf->SetFillColor(190, 190, 190);
                    $pdf->Cell(280, 5, $DescOrgao, 1, 1, "C", 1);
                }
                
                $DescOrgaoAntes = $DescOrgao;
                
                if ($FlagGrupo == "S") {
                	$nomeOrgaoGestor = $DescOrgao;
                }
                
                if ($DescCentroCustoAntes != $DescCentroCusto) {
                    $pdf->SetFillColor(220, 220, 220);
                    $pdf->Cell(280, 5, $DescCentroCusto, 1, 1, "C", 1);
                    
                    $pdf->Cell(65, 8, " SOLICITAÇÃO ", 1, 0, "C", 0);
                    $pdf->Cell(110, 8, " DETALHAMENTO ", 1, 0, "C", 0);
                    $pdf->Cell(30, 8, " DATA ", 1, 0, "C", 0);
                    $pdf->Cell(75, 8, " SITUAÇÃO ", 1, 0, "C", 0);
                    $pdf->Ln(8);
                }
                 
                $DescCentroCustoAntes = $DescCentroCusto;
                
                $strSolicitacaoCodigo = getNumeroSolicitacaoCompra($db, $CodSolicitacao);
                $pdf->Cell(65, 6, $strSolicitacaoCodigo, 1, 0, "C", 0);
                $pdf->Cell(110, 6, $DetaCentroCusto, 1, 0, "C", 0);
                $pdf->Cell(30, 6, $DataSolicitacao, 1, 0, "C", 0);
                $pdf->Cell(75, 6, $DescSolicitacao, 1, 0, "C", 0);
                $pdf->Ln(6);
                
                // Itens
                $sqlItens = "   SELECT
                                    I.CITESCSEQU ,
                                    I.CMATEPSEQU ,
                                    I.CSERVPSEQU ,
                                    I.AITESCORDE ,
                                    I.AITESCQTSO ,
                                    I.VITESCUNIT ,
                                    I.VITESCVEXE ,
                                    M.EMATEPDESC ,
                                    S.ESERVPDESC ,
                                    I.EITESCDESCMAT ,
                                    I.EITESCDESCSE
                                FROM
                                    SFPC.TBITEMSOLICITACAOCOMPRA I LEFT JOIN SFPC.TBMATERIALPORTAL M
                                        ON(
                                        M.CMATEPSEQU = I.CMATEPSEQU
                                    ) LEFT JOIN SFPC.TBSERVICOPORTAL S
                                        ON(
                                        S.CSERVPSEQU = I.CSERVPSEQU
                                    )
                                WHERE
                                    I.CSOLCOSEQU = $CodSolicitacao
                                ORDER BY
                                    I.CITESCSEQU ASC";
                
                $resItens = $db->query($sqlItens);
                if (PEAR::isError($resItens)) {
                    $CodErroEmail  = $resIntens->getCode();
                    $DescErroEmail = $resIntens->getMessage();
                    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlItens\n\n$DescErroEmail ($CodErroEmail)");
                } else {
                    while ($LinhaItens = $resItens->fetchRow()) {
                        $intSeqIntem 		= $LinhaItens[0];
                        $srtSeqMaterial 	= $LinhaItens[1];
                        $srtSeqServico 		= $LinhaItens[2];
                        $ordemItem	 		= $LinhaItens[3];
                        $strQuantidade		= $LinhaItens[4];
                        $strValorEstimado	= $LinhaItens[5];
                        
                        $item = new ArrayObject();
                        $item->offsetSet('total', $strQuantidade);
                        $item->offsetSet('licitantes', new ArrayObject());
                        
                        $strTipo = '';
                        $strDescricao = '';
                        $strDescricaoDetalhada = '';
                        $codRed = '';
                        
                        if ($srtSeqMaterial != "") {
                            $codRed 		= $srtSeqMaterial;
                            $strDescricao 	= $LinhaItens[7];
                            $strDescricaoDetalhada = $LinhaItens[9];
                            $strTipo 		= "CADUM";
                            $item->offsetSet('id', $srtSeqMaterial);
                        } else {
                            $codRed 		= $srtSeqServico;
                            $strDescricao 	= $LinhaItens[8];
                            $strDescricaoDetalhada = $LinhaItens[10];
                            $strTipo 		= "CADUS";
                            $item->offsetSet('id', $srtSeqServico);
                        }
                        
                        $item->offsetSet('ordem', $ordemItem);
                        $item->offsetSet('quantidade', $strQuantidade);
                        $item->offsetSet('valorEstimado', $strValorEstimado);
                        $item->offsetSet('descricao', $strDescricao);
                        $item->offsetSet('descricaoDetalhada', $strDescricaoDetalhada);
                        $item->offsetSet('tipo', $strTipo);
                        $item->offsetSet('codigoReduzido', $codRed);
                        $itemExiste = false;
                        
                        // Varre o arrayItens para verifica se o item existe.
                        // Caso exista incrementa a quantidade.
                        foreach ($arrayItens as $itemSolicitacao) {
                            if ($itemSolicitacao->offsetGet('id') == $item->offsetGet('id') &&
                            $itemSolicitacao->offsetGet('tipo') == $item->offsetGet('tipo')) {
                        
                                // Incrementa a quantidade
                                $somaQtdItem = $itemSolicitacao->offsetGet('total') + $item->offsetGet('quantidade');
                                $itemSolicitacao->offsetSet('total', $somaQtdItem);
                                $itemExiste = true;
                        
                                $licitantes = $itemSolicitacao->offsetGet('licitantes');
                        
                                foreach ($licitantes as $licitante) {
                                    $itensLicitante = $licitante->offsetGet('itemOrgao');
                        
                                    // Item do licitante
                                    $itemOrgao = new ArrayObject();
                                    $itemOrgao->offsetSet('idItemOrgao', $item->offsetGet('id'));
                                    $itemOrgao->offsetSet('descricaoItemOrgao', $item->offsetGet('descricao'));
                                    $itemOrgao->offsetSet('descricaoDetalhadaItemOrgao', $item->offsetGet('descricaoDetalhada'));
                                    $itemOrgao->offsetSet('ordemItemOrgao', $item->offsetGet('ordem'));
                                    $itemOrgao->offsetSet('quantidadeItemOrgao', $item->offsetGet('quantidade'));
                                    $itemOrgao->offsetSet('valorEstimadoItemOrgao', $item->offsetGet('valorEstimado'));
                        
                                    $itensLicitante->append($itemOrgao);
                                }
                        
                                break;
                            }
                        }
                        
                        // Caso não exista, adiciona o item no array
                        if ($itemExiste === false) {
                            // Licitante
                            $orgao = new ArrayObject();
                            $orgao->offsetSet('id', $CodOrgao);
                            $orgao->offsetSet('descricao', $DescOrgao);
                            $orgao->offsetSet('itemOrgao', new ArrayObject());
                        
                            // Item do licitante
                            $itemOrgao = new ArrayObject();
                            $itemOrgao->offsetSet('idItemOrgao', $item->offsetGet('id'));
                            $itemOrgao->offsetSet('descricaoItemOrgao', $item->offsetGet('descricao'));
                            $itemOrgao->offsetSet('descricaoDetalhadaItemOrgao', $item->offsetGet('descricaoDetalhada'));
                            $itemOrgao->offsetSet('ordemItemOrgao', $item->offsetGet('ordem'));
                            $itemOrgao->offsetSet('quantidadeItemOrgao', $item->offsetGet('quantidade'));
                            $itemOrgao->offsetSet('valorEstimadoItemOrgao', $item->offsetGet('valorEstimado'));
                        
                            // Adiciona ao array o item do licitante
                            $itensOrgao = $orgao->offsetGet('itemOrgao');
                            $itensOrgao->append($itemOrgao);
                        
                            // Adiciona o licitante ao array de licitantes
                            $licitantes = $item->offsetGet('licitantes');
                            $licitantes->append($orgao);
                        
                            // Redefine o array de licitantes
                            $item->offsetSet('licitantes', $licitantes);
                             
                            // Adiciona o item ao array
                            $arrayItens->append($item);
                        }
                    }
                }
            }
            
            $pdf->SetFillColor(150, 150, 150);
            $pdf->Cell(65, 6, "ÓRGÃO GESTOR", 1, 0, "C", 1);
            $pdf->Cell(215, 6, $nomeOrgaoGestor, 1, 0, "L", 1);
            $pdf->Ln(6);
            
            $pdf->MultiCell(280, 10, "", 1, "L", 0);
            $pdf->SetFillColor(150, 150, 150);
            $pdf->Cell(280, 5, "ITENS SELECIONADOS", 1, 1, "C", 1);
            $pdf->SetFillColor(220, 220, 220);
            
            $pdf->Cell(16, 8, " ORDEM ", 1, 0, "C", 0);
            $pdf->Cell(100, 8, " DESCRIÇÃO ", 1, 0, "C", 0);
            $pdf->Cell(96, 8, " DESCRIÇÃO DETALHADA ", 1, 0, "C", 0);
            $pdf->Cell(16, 8, " TIPO ", 1, 0, "C", 0);
            $pdf->Cell(26, 8, " CÓD. RED ", 1, 0, "C", 0);
            $pdf->Cell(26, 8, " QUANTIDADE ", 1, 0, "C", 0);
            $pdf->Ln(8);
            
            $posicao = 1;
            foreach ($arrayItens as $item) {
                $descricaoDetalhadaItem = $item->offsetGet('descricaoDetalhada');
                if ($item->offsetGet('descricaoDetalhada') == "") {
                    $descricaoDetalhadaItem = ' - ';
                }
                
                $h  = 6;
                $hm = 0;
                $h1 = $pdf->GetStringHeight(100, $h, $item->offsetGet('descricao'), "L");
                $h2 = $pdf->GetStringHeight(96, $h, $descricaoDetalhadaItem, "L");
                $hm = $h1;
                
                if ($hm < $h2) {
                    $hm = $h2;
                }
                
                $h1 = $hm / ($h1 / $h);
                $h2 = $hm / ($h2 / $h);
                
                $pdf->Cell(16, $hm, $item->offsetGet('ordem'), 1, 0, "C", 0);
                
                $x = $pdf->GetX() + 100;
                $y = $pdf->GetY();
                $pdf->MultiCell(100, $h1, $item->offsetGet('descricao'), 1, "C", 0);
                $pdf->SetXY($x, $y);
                
                $x = $pdf->GetX() + 96;
                $y = $pdf->GetY();
                $pdf->MultiCell(96, $h2, $descricaoDetalhadaItem, 1, "C", 0);
                $pdf->SetXY($x, $y);
                
                $pdf->Cell(16, $hm, $item->offsetGet('tipo'), 1, 0, "C", 0);
                $pdf->Cell(26, $hm, $item->offsetGet('codigoReduzido'), 1, 0, "C", 0);
                $pdf->Cell(26, $hm, converte_valor_estoques($item->offsetGet('total')), 1, 0, "C", 0);
                $pdf->Ln($hm);
                
                $posicao++;
            }
        }
        
        $db->disconnect();
        $pdf->Output();
    }
?>
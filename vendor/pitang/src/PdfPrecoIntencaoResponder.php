<?php
// 220038--
/**
 * Portal da DGCO
 *
 * PHP version 5.2.5
 *
 * LICENSE: This source file is subject to version 3.01 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_01.txt. If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @category  Pitang Registro Preço
 * @package   registropreco
 * @author    Pitang Agile TI <contato@pitang.com>
 * @copyright 2014 EMPRESA MUNICIPAL DE INFORMÁTICA - EMPREL
 * @license   http://www.php.net/license/3_01.txt PHP License 3.01
 * @version   Git: $Id:$
 */

 /**
 * -----------------------------------------------------------------------------
 * Alterado: Caio Coutinho <caio.cezar@pitang.com>
 * Data:     07/06/2018
 * Objetivo: [CR 196500]: (Registro de Preço - Intenção)
 * -----------------------------------------------------------------------------
 * Alterado: Lucas Baracho
 * Data:     24/09/2018
 * Objetivo: Tarefa Redmine 203923
 * -----------------------------------------------------------------------------
 * Alterado: Pitang Agile TI
 * Data:     15/02/2019
 * Objetivo: Tarefa Redmine 211087
 * -----------------------------------------------------------------------------
 * Alterado: Pitang Agile TI - Caio Coutinho
 * Data:     01/04/2019
 * Objetivo: Tarefa Redmine 214041
 * -----------------------------------------------------------------------------
 * Alterado: Lucas Baracho
 * Data:     04/06/2019
 * Objetivo: Tarefa Redmine 217955
 * -----------------------------------------------------------------------------
 */

class PdfPrecoIntencaoResponder extends AbstractPdfRegistroPreco {
    const ALTURA_PADRAO = 4;

    private $chaveIntencao;
    private $orgaoLicitante;
    private $totalEstimado;
    private $exibirUltimaResposta = false;

    public function __construct($orientacao = "L", $unidadeMedida = "mm", $formato = "A4") {
        $this->setOrgaoLicitante(null);
        $this->setExibirUltimaResposta(false);
        
        parent::__construct($orientacao, $unidadeMedida, $formato);
    }

    public function setOrgaoLicitante($orgaoLicitante) {
        $this->orgaoLicitante = $orgaoLicitante;
        
        return $this;
    }

    public function getOrgaoLicitante() {
        return $this->orgaoLicitante;
    }

    public function setExibirUltimaResposta($exibirUltimaResposta) {
        $this->exibirUltimaResposta = $exibirUltimaResposta;
        
        return $this;
    }

    public function getExibirUltimaResposta() {
        return $this->exibirUltimaResposta;
    }

    public function getTitulo() {
        return "RESPOSTA DA INTENÇÃO DE REGISTRO DE PREÇOS";
    }   

    public function gerarRelatorio($name = 'IntençãoResponder_') {
        $intencao       = $this->dadosIntencao();
        $itensIntencoes = $this->consultaItensItencao();

        foreach ($itensIntencoes as $itemIntencao) {
            $this->montarDadosItensIntencao($intencao, $itemIntencao);
        }
        $this->preencheTotal();

        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        
        $this->Output($name. date('d/m/Y_h_i_s') .'.pdf', 'I');
    }

    private function loadIntencao() {
        $chaveIntencao = $this->chaveIntencao;

        if (!empty($chaveIntencao['sequencialIntencao']) && !empty($chaveIntencao['anoIntencao'])) {
            $database  = ClaDatabasePostgresql::getConexao();
            $sql       = Helper_Acompanhar_Visualizar::sqlSelectIntencao($chaveIntencao['sequencialIntencao'], $chaveIntencao['anoIntencao'], $this->getOrgaoLicitante());
            $resultSet = executarSQL($database, $sql);

            return $resultSet->fetchRow(DB_FETCHMODE_OBJECT);
        }
    }

    private function consultaItensItencao() {
        $sequencialIntencao = $this->chaveIntencao['sequencialIntencao'];
        $anoIntencao        = $this->chaveIntencao['anoIntencao'];
        
        $orgao = isset($_POST['orgaoLicitante']) ? $_POST['orgaoLicitante'] : $_GET['orgao'];

        $sql = "SELECT DISTINCT I.CITIRPSEQU, I.CINTRPSEQU, I.CINTRPSANO, SUM(R.AIRIRPQTPR) AS QTDCONSOLIDADA,
		    	                I.CMATEPSEQU, M.EMATEPDESC, I.EITIRPDESCMAT, S.ESERVPDESC, I.CSERVPSEQU, I.EITIRPDESCSE, I.AITIRPORDE,
		    	                I.VITIRPVUES, M.FMATEPGENE, RI.TRINRPULAT, RI.XRINRPJUST
	    	    FROM    SFPC.TBITEMRESPOSTAINTENCAORP R
		    	        INNER JOIN SFPC.TBITEMINTENCAOREGISTROPRECO I ON R.CITIRPSEQU = I.CITIRPSEQU AND R.CINTRPSANO = I.CINTRPSANO AND R.CINTRPSEQU = I.CINTRPSEQU
		    	        INNER JOIN SFPC.TBRESPOSTAINTENCAORP RI ON RI.CORGLICODI = R.CORGLICODI AND RI.CINTRPSEQU = I.CINTRPSEQU AND RI.CINTRPSANO = I.CINTRPSANO
		    	        LEFT JOIN SFPC.TBMATERIALPORTAL M ON I.CMATEPSEQU = M.CMATEPSEQU
		    	        LEFT JOIN SFPC.TBSERVICOPORTAL S ON I.CSERVPSEQU = S.CSERVPSEQU
	    	    WHERE   R.CINTRPSEQU = $sequencialIntencao
		    	        AND R.CINTRPSANO = $anoIntencao
		    	        AND R.corglicodi = $orgao
	    	    GROUP BY    I.CITIRPSEQU, I.CINTRPSEQU, I.CINTRPSANO, I.CMATEPSEQU, M.EMATEPDESC,  I.EITIRPDESCMAT,
                            S.ESERVPDESC, I.CSERVPSEQU, I.EITIRPDESCSE, I.AITIRPORDE, I.VITIRPVUES, M.FMATEPGENE,
                            RI.TRINRPULAT, RI.XRINRPJUST
	    	    ORDER BY    I.AITIRPORDE ";

        $res = ClaDatabasePostgresql::executarSQL($sql);
        
        ClaDatabasePostgresql::hasError($res);
        return $res;
    }

    private function dadosIntencao() {
        $intencao = $this->loadIntencao();

        $numeroIntencaoFormatado = substr($intencao->cintrpsequ + 10000, 1) . '/' . $intencao->cintrpsano;

        $this->SetFillColor(220, 220, 220);
        $this->Cell(50, 6, " NÚMERO DA INTENÇÃO ", 1, 0, "L", 1);
        $this->Cell(230, 6, $numeroIntencaoFormatado, 1, 0, "L", 0);
        $this->Ln();
        $this->Cell(50, 6, " DATA DE CADASTRAMENTO ", 1, 0, "L", 1);
        $this->Cell(230, 6, ClaHelper::converterDataBancoParaBr($intencao->tintrpdcad), 1, 0, "L", 0);
        $this->Ln();
        $this->Cell(50, 6, " DATA LIMITE ", 1, 0, "L", 1);
        $this->Cell(230, 6, ClaHelper::converterDataBancoParaBr($intencao->tintrpdlim), 1, 0, "L", 0);
        $this->Ln();

        if ($this->getExibirUltimaResposta()) {
            $this->Cell(50, 6, " DATA DA ÚLTIMA ALTERAÇÃO ", 1, 0, "L", 1);
            $this->Cell(230, 6, ClaHelper::converterDataBancoParaBr($intencao->trinrpulat), 1, 0, "L", 0);
            $this->Ln();
        }
        $this->Cell(50, 6, " USUÁRIO RESPONSÁVEL ", 1, 0, "L", 1);
        $this->Cell(230, 6, $intencao->eusuporesp, 1, 0, "L", 0);
        $this->Ln();

        $l = $this->getInstance()->GetStringHeight(120, 6, trim($intencao->xintrpobje), "L");
        $altura = ceil($l / 2) + 3;
        if ($altura <= 6) {
            $altura = $l;
        }
        $this->Cell(50, $altura, " OBJETO ", 1, 0, "L", 1);
        $this->MultiCell(230, 5, $intencao->xintrpobje, 1, "L", 0);

        $l = $this->getInstance()->GetStringHeight(113, 6, trim($intencao->xintrpobse), "L");
        $altura = ceil($l / 2) + 1;
        if ($altura <= 6) {
            $altura = $l;
        }
        $this->Cell(50, $altura, " OBSERVAÇÃO ", 1, 0, "L", 1);
        $this->MultiCell(230, 5, $intencao->xintrpobse, 1, 0, "L", 0);
        
        $this->Cell(50, 6, " ÓRGÃO DA INTENÇÃO ", 1, 0, "L", 1);
        $this->Cell(230, 6, $intencao->eorglidesc, 1, 0, "L", 0);
        $this->Ln();

        $grupoUsuario          = (integer) $_SESSION['_cgrempcodi_'];
        $registroPrecoIntencao = new ClaRegistroPrecoIntencao();
        $situacaoIntencao      = $registroPrecoIntencao->getSituacaoIntencao($intencao->cintrpsequ, $intencao->cintrpsano, $grupoUsuario);
        if ($situacaoIntencao != 'RESPONDIDA') {
            $this->Cell(50, 6, " SITUAÇÃO DA INTENÇÃO ", 1, 0, "L", 1);
            $this->Cell(230, 6, 'RESPOSTA DA IRP EM RASCUNHO', 1, 0, "L", 0);
            $this->Ln();
        }

        $h1 = 5;
        $h1 = $this->GetStringHeight(230, 5, trim(str_ireplace($breaks, '\r\n', $intencao->xrinrpjust)), 'L');
        
        if ($h1 < 5) {
            $h1 = 5;
        }

        $this->Cell(50, $h1, "JUSTIFICATIVA ", 1, 0, "L", 1);
        $this->MultiCell(230, 5, $intencao->xrinrpjust, 1, 1, "L", 0);
        $this->Ln();

        $this->SetFillColor(220, 220, 220);
        $this->Cell(280, 6, " ITEM(NS) DA INTENÇÃO ", 1, 0, "C", 1);
        $this->Ln();
        // Plota as colunas necessárias para o item da intenção
        $this->SetFillColor(220, 220, 220);
        $this->Cell(30, 6, " ORDEM ", 1, 0, "C", 1);
        $this->Cell(40, 6, " DESCRIÇÃO MATERIAL/SERVIÇO ", 1, 0, "C", 1);
        $this->Cell(40, 6, " DESCRIÇÃO DETALHADA ", 1, 0, "C", 1);
        $this->Cell(30, 6, " TIPO", 1, 0, "C", 1);
        $this->Cell(30, 6, " COD. RED. ", 1, 0, "C", 1);
        $this->Cell(40, 6, " VALOR TRP UNITÁRIO ", 1, 0, "C", 1);
        $this->Cell(40, 6, " VALOR EST. UNITÁRIO ", 1, 0, "C", 1);
        $this->Cell(30, 6, " QTDE PREVISTA ", 1, 0, "C", 1);
        $this->Ln();

        return $intencao;
    }

    /**
     * Seleciona quantidade informada no item de registro de preço
     *
     * @param Negocio_ValorObjeto_IntencaoRegistroPreco $voIRP
     * @param integer $corglicodi
     * @param integer $citirpsequ
     *
     * @return DB_common::getOne() array, string or numeric data to be added to the prepared statement.
     *         Quantity of items passed must match quantity of placeholders in the prepared statement:
     *         meaning 1 placeholder for non-array parameters or 1 placeholder per array element.
     */
    public function selecionaQuantidadeItemIRP($cintrpsequ, $cintrpsano, $corglicodi, $citirpsequ) {
        $sql = "SELECT  AIRIRPQTPR
                FROM    SFPC.TBITEMRESPOSTAINTENCAORP
                WHERE   CINTRPSEQU = ?
                        AND CINTRPSANO = ?
                        AND CORGLICODI = ?
                        AND CITIRPSEQU = ? ";

        $database = ClaDatabasePostgresql::getConexao();

        $res = &$database->getOne($sql, array($cintrpsequ, $cintrpsano, $corglicodi, $citirpsequ));

        ClaDatabasePostgresql::hasError($res);
        return $res;
    }

    private function montarDadosItensIntencao($intencao, $itemIntencao) {
        $material    = $itemIntencao->cmatepsequ == null ? false : true;
        $maiorAltura = 0;
        $orgao       = isset($_POST['orgaoLicitante']) ? $_POST['orgaoLicitante'] : $_GET['orgao'];

        $this->SetFillColor(255, 255, 255);

        $item                   = $this->unificarChavesItem($itemIntencao);
        $quantidadePrevistaItem = $this->selecionaQuantidadeItemIRP($intencao->cintrpsequ, $intencao->cintrpsano, $orgao, $item->sequencialItemIntencao);
        $alturaDesc             = $this->getInstance()->GetStringHeight(40, self::ALTURA_PADRAO, trim($item->descricao), "L"); // Pega a altura dos textos que podem ter mais de uma linha
        $alturaDescDetalhada    = $this->getInstance()->GetStringHeight(40, self::ALTURA_PADRAO, trim($item->descricaoDetalhada), "L");

        // Verifica a maior altura
        $maiorAltura = $alturaDesc;
        if ($maiorAltura < $alturaDescDetalhada) {
            $maiorAltura = $alturaDescDetalhada;
        }

        // Verifica a altura correta para a célula que pode ter mais de uma linha
        $alturaDesc          = $maiorAltura / ($alturaDesc / self::ALTURA_PADRAO);
        $alturaDescDetalhada = $maiorAltura / ($alturaDescDetalhada / self::ALTURA_PADRAO);

        // Informa o número de ordem
        $this->Cell(30, $maiorAltura, $item->ordem, 1, 0, "C", 1);

        $x = $this->GetX() + 40;
        $y = $this->GetY();
        
        $this->MultiCell(40, $alturaDesc, removeSimbolos($item->descricao) . ' ', 1, "L", 0);
        $this->SetXY($x, $y);

        $x = $this->GetX() + 40;
        $y = $this->GetY();
        
        $this->MultiCell(40, $alturaDescDetalhada, removeSimbolos($item->descricaoDetalhada), 1, "L", 0);
        $this->SetXY($x, $y);

        $this->Cell(30, $maiorAltura, $item->tipo, 1, 0, "C", 1);
        $this->Cell(30, $maiorAltura, $item->sequencialItem, 1, 0, "C", 1);

        if ($material) {
            $this->Cell(40, $maiorAltura, $item->valorTrpUnitario, 1, 0, "C", 1);
        } else {
            $this->Cell(40, $maiorAltura, " ---", 1, 0, "C", 1);
        }
        $totalPorItem = $item->valorUnitario * $quantidadePrevistaItem;
        $this->totalEstimado = $this->totalEstimado + $totalPorItem;
        
        $this->Cell(40, $maiorAltura, converte_valor_estoques($item->valorUnitario), 1, 0, "C", 1);
        $this->Cell(30, $maiorAltura, converte_valor_estoques($quantidadePrevistaItem), 1, 0, "C", 1);
        $this->Ln();
    }

    /* Preenche no relatório o total */
    private function preencheTotal() {
        $this->SetFillColor(220, 220, 220);
        $this->Cell(210, 6, "TOTAL ESTIMADO", 1, 0, "L", 1);
        $this->Cell(70, 6, "R$ " . converte_valor_estoques($this->totalEstimado), 1, 0, "C", 1);
    }

    /* Unifica os valores do item */
    private function unificarChavesItem($item) {
        $novoItem = array();

        // Atributos em comum
        $novoItem['sequencialItemIntencao'] = $item->citirpsequ;
        $novoItem['sequencialIntencao']     = $item->cintrpsequ;
        $novoItem['anoIntencao']            = $item->cintrpsano;
        $novoItem['quantidadeConsolidada']  = $item->qtdconsolidada;
        $novoItem['ordem']                  = $item->aitirporde;
        $novoItem['valorUnitario']          = $item->vitirpvues;

        // Serviço
        $novoItem['tipo']               = 'CADUS';
        $novoItem['generico']           = false;
        $novoItem['descricao']          = $item->eservpdesc;
        $novoItem['descricaoDetalhada'] = $item->eitirpdescse;
        $novoItem['sequencialItem']     = $item->cservpsequ;
        $novoItem['valorTrpUnitario']   = '---';

        // Material
        if (isset($item->cmatepsequ)) {
            $novoItem['tipo']               = 'CADUM';
            $novoItem['generico']           = ($item->fmatepgene == "S") ? true : false;
            $novoItem['descricao']          = $item->ematepdesc;
            $novoItem['descricaoDetalhada'] = $item->eitirpdescmat;
            $novoItem['sequencialItem']     = $item->cmatepsequ;
            $novoItem['valorTrpUnitario']   = $this->getValorEstimadoTRP($item->cmatepsequ, 'CADUM');
        }
        return (object) $novoItem;
    }

    /* Gets */
    private function getValorEstimadoTRP($codigo, $tipo) {
        $database = ClaDatabasePostgresql::getConexao();
        if ('CADUM' == $tipo) {
            return converte_valor_estoques(calcularValorTrp($database, 2, $codigo));
        }
        return '---';
    }

    public function setChaveIntencao($chave) {
        $this->chaveIntencao = $chave;
    }
}
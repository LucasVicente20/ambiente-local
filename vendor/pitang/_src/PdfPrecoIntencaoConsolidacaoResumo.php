<?php

/**
 * Portal da DGCO.
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
 *
 * @author    Pitang Agile TI <contato@pitang.com>
 * @copyright 2014 EMPRESA MUNICIPAL DE INFORMÁTICA - EMPREL
 * @license   http://www.php.net/license/3_01.txt PHP License 3.01
 */
class PdfPrecoIntencaoConsolidacaoResumo extends AbstractPdfRegistroPreco
{

    const ALTURA_PADRAO = 4;

    private $chaveIntencao;

    private $totalEstimado;

    public function __construct($orientacao = 'L', $unidadeMedida = 'mm', $formato = 'A4')
    {
        parent::__construct($orientacao, $unidadeMedida, $formato);
    }

    public function getTitulo()
    {
        return 'Resumo Consolidação de Intenção de Registro de Preço - IRP';
    }

    /**
     */
    public function gerarRelatorio($name = 'ConsolidaçãoResumo_')
    {
        $this->dadosIntencao();
        $itensIntencoes = $this->consultaItensItencao();

        // echo '<pre>';
        // print_r($itensIntencoes);
        // die;

        foreach ($itensIntencoes as $itemIntencao) {
            $this->montarDadosItensIntencao($itemIntencao);
        }
        $this->preencheTotal();

        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        $this->Output($name. date('d/m/Y_h_i_s') .'.pdf', 'I');
    }

    private function loadIntencao()
    {
        $chaveIntencao = $this->chaveIntencao;

        if (! empty($chaveIntencao['sequencialIntencao']) && ! empty($chaveIntencao['anoIntencao'])) {
            $database = ClaDatabasePostgresql::getConexao();
            $sql = Helper_Acompanhar_Visualizar::sqlSelectIntencao($chaveIntencao['sequencialIntencao'], $chaveIntencao['anoIntencao']);
            $resultSet = executarSQL($database, $sql);

            return $resultSet->fetchRow(DB_FETCHMODE_OBJECT);
        }
    }

    private function consultaItensItencao()
    {
        $sql = Helper_Acompanhar_Visualizar::sqlSelectItensIntencao($this->chaveIntencao['sequencialIntencao'], $this->chaveIntencao['anoIntencao']);
        $resultado = ClaDatabasePostgresql::executarSQL($sql);
        ClaDatabasePostgresql::hasError($resultado);
        return $resultado;
    }

    private function dadosIntencao()
    {
        $intencao = $this->loadIntencao();
        $orgaosIntencao = (string) $this->listarOrgaosIntencao();
        $numeroIntencaoFormatado = substr($intencao->cintrpsequ + 10000, 1) . '/' . $intencao->cintrpsano;

        $this->SetFillColor(220, 220, 220);

        $this->Cell(50, 6, ' NÚMERO DA INTENÇÃO ', 1, 0, 'L', 1);
        $this->Cell(230, 6, $numeroIntencaoFormatado, 1, 0, 'L', 0);
        $this->Ln();

        $this->Cell(50, 6, ' DATA DE CADASTRAMENTO ', 1, 0, 'L', 1);
        $this->Cell(230, 6, ClaHelper::converterDataBancoParaBr($intencao->tintrpdcad), 1, 0, 'L', 0);
        $this->Ln();

        $this->Cell(50, 6, ' DATA LIMITE ', 1, 0, 'L', 1);
        $this->Cell(230, 6, ClaHelper::converterDataBancoParaBr($intencao->tintrpdlim), 1, 0, 'L', 0);
        $this->Ln();

        $l = $this->getInstance()->GetStringHeight(120, 6, trim($intencao->xintrpobje), 'L');
        $altura = ceil($l / 2);
        if ($l <= 6) {
            $altura = $l;
        }
        $this->Cell(50, $altura, ' OBJETO ', 1, 0, 'L', 1);
        $this->MultiCell(230, 6, $intencao->xintrpobje, 1, 'L', 0);         

        $orgaosIntencao = str_replace("\n", '', $orgaosIntencao);

        
        $l = $this->getInstance()->GetStringHeight(230, 6, trim($orgaosIntencao), 'L');
        $this->Cell(50, $l, ' ÓRGÃOS ', 1, 0, 'L', 1);
        $this->MultiCell(230, 6, $orgaosIntencao, 1, 'L', 0);

        $l = $this->getInstance()->GetStringHeight(113, 6, trim($intencao->xintrpobse), 'L');
        $altura = ceil($l / 2) + 1;
        if ($l <= 6) {
            $altura = $l;
        }
        $this->Cell(50, $altura, ' OBSERVAÇÃO ', 1, 0, 'L', 1);
        $this->MultiCell(230, 6, $intencao->xintrpobse, 1, 0, 'L', 0);

        $this->SetFillColor(220, 220, 220);
        $this->Cell(280, 6, ' ITEM(NS) DA INTENÇÃO ', 1, 0, 'C', 1);
        $this->Ln();
        // Plota as colunas necessárias para o item da intenção
        $this->SetFillColor(220, 220, 220);

        $this->Cell(30, 6, ' ORDEM ', 1, 0, 'C', 1);
        $this->Cell(40, 6, ' DESCRIÇÃO MATERIAL/SERVIÇO ', 1, 0, 'C', 1);
        $this->Cell(40, 6, ' DESCRIÇÃO DETALHADA ', 1, 0, 'C', 1);
        $this->Cell(30, 6, ' TIPO', 1, 0, 'C', 1);
        $this->Cell(30, 6, ' COD. RED. ', 1, 0, 'C', 1);
        $this->Cell(40, 6, ' VALOR TRP UNITÁRIO ', 1, 0, 'C', 1);
        $this->Cell(40, 6, ' VALOR EST. UNITÁRIO ', 1, 0, 'C', 1);
        $this->Cell(30, 6, ' QTDE CONSOLIDADA ', 1, 0, 'C', 1);
        $this->Ln();
    }

    private function montarDadosItensIntencao($itemIntencao)
    {
        $material = $itemIntencao->cmatepsequ == null ? false : true;

        $maiorAltura = 0;

        $this->SetFillColor(255, 255, 255);

        $item = $this->unificarChavesItem($itemIntencao);

        // echo '<pre>';
        // print_r($item);
        // die;

        // Pega a altura dos textos que podem ter mais de uma linha
        $alturaDesc = $this->getInstance()->GetStringHeight(40, self::ALTURA_PADRAO, trim($item->descricao), 'L');
        $alturaDescDetalhada = $this->getInstance()->GetStringHeight(40, self::ALTURA_PADRAO, trim($item->descricaoDetalhada), 'L');

        // Verifica a maior altura
        $maiorAltura = $alturaDesc;
        if ($maiorAltura < $alturaDescDetalhada) {
            $maiorAltura = $alturaDescDetalhada;
        }

        // Verifica a altura correta para a célula que pode ter mais de uma linha
        $alturaDesc = $maiorAltura / ($alturaDesc / self::ALTURA_PADRAO);
        $alturaDescDetalhada = $maiorAltura / ($alturaDescDetalhada / self::ALTURA_PADRAO);

        // Informa o número de ordem
        $this->Cell(30, $maiorAltura, $item->ordem, 1, 0, 'C', 1);

        $x = $this->GetX() + 40;
        $y = $this->GetY();
        $this->MultiCell(40, $alturaDesc, removeSimbolos($item->descricao), 1, 'L', 0);
        $this->SetXY($x, $y);

        $x = $this->GetX() + 40;
        $y = $this->GetY();
        $this->MultiCell(40, $alturaDescDetalhada, removeSimbolos($item->descricaoDetalhada), 1, 'L', 0);
        $this->SetXY($x, $y);

        $this->Cell(30, $maiorAltura, $item->tipo, 1, 0, 'C', 1);
        $this->Cell(30, $maiorAltura, $item->sequencialItem, 1, 0, 'C', 1);

        if ($material) {
            $this->Cell(40, $maiorAltura, $item->valorTrpUnitario, 1, 0, 'C', 1);
        } else {
            $this->Cell(40, $maiorAltura, ' ---', 1, 0, 'C', 1);
        }

        $totalPorItem = $item->valorUnitario * $item->quantidadeConsolidada;
        $this->totalEstimado = $this->totalEstimado + $totalPorItem;

        $this->Cell(40, $maiorAltura, converte_valor_estoques($item->valorUnitario), 1, 0, 'C', 1);
        $this->Cell(30, $maiorAltura, converte_valor_estoques($item->quantidadeConsolidada), 1, 0, 'C', 1);
        $this->Ln();
    }

    /* Preenche no relatório o total */
    private function preencheTotal()
    {
        $this->SetFillColor(220, 220, 220);
        $this->Cell(210, 6, 'TOTAL ESTIMADO', 1, 0, 'L', 1);
        $this->Cell(70, 6, 'R$ ' . converte_valor_estoques($this->totalEstimado), 1, 0, 'C', 1);
    }

    /* Unifica os valores do item */
    private function unificarChavesItem($item)
    {
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
            $novoItem['generico']           = ($item->fmatepgene == 'S') ? true : false;
            $novoItem['descricao']          = $item->ematepdesc;
            $novoItem['descricaoDetalhada'] = $item->eitirpdescmat;
            $novoItem['sequencialItem']     = $item->cmatepsequ;
            $novoItem['valorTrpUnitario']   = $this->getValorEstimadoTRP($item->cmatepsequ, 'CADUM');
        }

        return (object) $novoItem;
    }

    /* Consultas */
    private function listarOrgaosIntencao()
    {
        $orgaosIntencoes = array();

        $database = ClaDatabasePostgresql::getConexao();
        $sql = Helper_Acompanhar_Visualizar::sqlSelectOrgaosIntencao($this->chaveIntencao['sequencialIntencao'], $this->chaveIntencao['anoIntencao'], null);
        $resultado = executarSQL($database, $sql);
        $row = null;
        while ($resultado->fetchInto($row, DB_FETCHMODE_OBJECT)) {
            array_push($orgaosIntencoes, $row->eorglidesc);
        }

        return implode(", ", $orgaosIntencoes);
    }

    /* Gets */
    private function getValorEstimadoTRP($codigo, $tipo)
    {
        $database = ClaDatabasePostgresql::getConexao();

        if ('CADUM' == $tipo) {
            return converte_valor_estoques(calcularValorTrp($database, 2, $codigo));
        }

        return '---';
    }

    public function setChaveIntencao($chave)
    {
        $this->chaveIntencao = $chave;
    }
}

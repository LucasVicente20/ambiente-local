<?php
// 220038--
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
 * -----------------------------------------------------------------------------
 * Alterado: Eliakim Ramos
 * data: 27/11/2019
 * Objetivo: #225675
 * -----------------------------------------------------------------------------
 */
class PdfVisualizarConsolidacaoCompleta extends AbstractPdfRegistroPreco
{

    private $chaveIntencao;

    private $maiorAltura = 0;

    const ALTURA_PADRAO = 4;

    public function __construct($orientacao = 'L', $unidadeMedida = 'mm', $formato = 'A4')
    {
        parent::__construct($orientacao, $unidadeMedida, $formato);
    }

    public function getTitulo()
    {
        return 'Consolidação Completa de Intenção de Registro de Preço - IRP';
    }

    public function gerarRelatorio($name = 'ConsolidaçãoCompleta_')
    {
        $this->dadosIntencao();
        $this->listarItensIntencao();

        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        $this->Output($name. date('d/m/Y_h_i_s') .'.pdf', 'I');
    }

    public function setChaveIntencao($chaveIntencao)
    {
        $this->chaveIntencao = $chaveIntencao;
    }

    private function dadosIntencao()
    {
        $intencao = $this->loadIntencao();

        if (! is_null($intencao)) {
            $this->SetFillColor(220, 220, 220);

            $numeroIntencao = substr($intencao->cintrpsequ + 10000, 1) . '/' . $intencao->cintrpsano;
            $this->Cell(50, 6, ' NÚMERO DA INTENÇÃO ', 1, 0, 'L', 1);
            $this->Cell(230, 6, $numeroIntencao, 1, 0, 'L', 0);
            $this->Ln();

            $this->Cell(50, 6, ' DATA DE CADASTRAMENTO ', 1, 0, 'L', 1);
            $this->Cell(230, 6, ClaHelper::converterDataBancoParaBr($intencao->tintrpdcad), 1, 0, 'L', 0);
            $this->Ln();

            $this->Cell(50, 6, ' DATA LIMITE ', 1, 0, 'L', 1);
            $this->Cell(230, 6, ClaHelper::converterDataBancoParaBr($intencao->tintrpdlim), 1, 0, 'L', 0);
            $this->Ln();

            $l = $this->getInstance()->GetStringHeight(120, 6, trim($intencao->xintrpobje), 'L');
            $altura = ceil($l / 2) + 3;
            if ($altura <= 6) {
                $altura = $l;
            }
            $this->Cell(50, $altura, ' OBJETO ', 1, 0, 'L', 1);
            $this->MultiCell(230, 5, $intencao->xintrpobje, 1, 'L', 0);        

            $l = $this->getInstance()->GetStringHeight(230, 6, trim($this->getOrgaosIntencao()), 'L');
            $this->Cell(50, $l, ' ÓRGÃOS ', 1, 0, 'L', 1);
            $this->MultiCell(230, 5, $this->getOrgaosIntencao(), 1, 'L', 0);

            $l = $this->getInstance()->GetStringHeight(113, 6, trim($intencao->xintrpobse), 'L');
            $altura = ceil($l / 2) + 1;
            if ($altura <= 6) {
                $altura = $l;
            }
            $this->Cell(50, $altura, ' OBSERVAÇÃO ', 1, 0, 'L', 1);
            $this->MultiCell(230, 5, $intencao->xintrpobse, 1, 0, 'L', 0);
        }
    }

    private function listarItensIntencao()
    {
        $totalEstimado = 0;
        $sql = Helper_Acompanhar_Visualizar::sqlSelectItensIntencao($this->chaveIntencao['sequencialIntencao'], $this->chaveIntencao['anoIntencao']);
        $resultado = ClaDatabasePostgresql::executarSQL($sql);

        $this->Cell(280, 6, 'ITEM(NS) DA INTENÇÃO', 1, 1, 'C', 1);

        $this->SetFillColor(255, 255, 255);
        $this->Cell(15, 10, 'ORDEM', 1, 0, 'C', 1);
        $this->Cell(67, 10, 'DESCRIÇÃO MATERIAL/SERVIÇO', 1, 0, 'C', 1);
        $this->Cell(68, 10, 'DESCRIÇÃO DETALHADA', 1, 0, 'C', 1);
        $this->Cell(20, 10, 'TIPO', 1, 0, 'C', 1);
        $this->Cell(20, 10, 'COD. RED.', 1, 0, 'C', 1);
        $this->Cell(30, 10, 'VALOR TRP UNITÁRIO', 1, 0, 'C', 1);
        $this->Cell(30, 10, 'VALOR EST. UNITÁRIO', 1, 0, 'C', 1);
        $this->Cell(30, 10, 'QTDE CONSOLIDADA', 1, 0, 'C', 1);
        $this->Ln();

        foreach ($resultado as $item) {
            $itemIntencao = $this->unificarChavesItem($item);

            // Pega a altura dos textos que podem ter mais de uma linha
            $alturaDesc = $this->getInstance()->GetStringHeight(67, self::ALTURA_PADRAO, trim($itemIntencao->descricao), 'L');
            $alturaDescDetalhada = $this->getInstance()->GetStringHeight(68, self::ALTURA_PADRAO, trim($itemIntencao->descricaoDetalhada), 'L');

            // Verifica a maior altura
            $this->maiorAltura = $alturaDesc;
            if ($this->maiorAltura < $alturaDescDetalhada) {
                $this->maiorAltura = $alturaDescDetalhada;
            }

            // Verifica a altura correta para a célula que pode ter mais de uma linha
            $alturaDesc = $this->maiorAltura / ($alturaDesc / self::ALTURA_PADRAO);
            $alturaDescDetalhada = $this->maiorAltura / ($alturaDescDetalhada / self::ALTURA_PADRAO);

            // Preenche as celulas com os valores
            $this->Cell(15, $this->maiorAltura, $itemIntencao->ordem, 1, 0, 'C', 1);

            $x = $this->GetX() + 67;
            $y = $this->GetY();
            $this->MultiCell(67, $alturaDesc, removeSimbolos($itemIntencao->descricao), 1, 'L', 0);
            $this->SetXY($x, $y);

            $x = $this->GetX() + 68;
            $y = $this->GetY();
            $this->MultiCell(68, $alturaDescDetalhada, removeSimbolos($itemIntencao->descricaoDetalhada), 1, 'L', 0);
            $this->SetXY($x, $y);

            $this->Cell(20, $this->maiorAltura, $itemIntencao->tipo, 1, 0, 'C', 1);
            $this->Cell(20, $this->maiorAltura, $itemIntencao->sequencialItem, 1, 0, 'C', 1);
            $this->Cell(30, $this->maiorAltura, $itemIntencao->valorTrpUnitario, 1, 0, 'C', 1);
            $this->Cell(30, $this->maiorAltura, converte_valor_estoques($itemIntencao->valorUnitario), 1, 0, 'C', 1);
            $this->Cell(30, $this->maiorAltura, converte_valor_estoques($itemIntencao->quantidadeConsolidada), 1, 0, 'C', 1);
            $this->Ln();

            // Órgãos que informaram o item na resposta
            $this->listarOrgaosPorItem($this->chaveIntencao['sequencialIntencao'], $this->chaveIntencao['anoIntencao'], $itemIntencao->sequencialItemIntencao);

            $totalPorItem = $itemIntencao->valorUnitario * $itemIntencao->quantidadeConsolidada;
            $totalEstimado = $totalEstimado + $totalPorItem;
        }

        if (count($resultado) > 0) {
            $this->SetFillColor(220, 220, 220);
            $this->Cell(220, 6, 'TOTAL ESTIMADO', 1, 0, 'L', 1);
            $this->Cell(60, 6, 'R$ ' . converte_valor_estoques($totalEstimado), 1, 0, 'C', 1);
        }
    }

    private function listarOrgaosPorItem($sequencialIntencao, $anoIntencao, $sequencialItemIntencao)
    {
        $sql = Helper_Acompanhar_Visualizar::sqlSelectOrgaosPorItem($sequencialIntencao, $anoIntencao, $sequencialItemIntencao);
        $resultado = ClaDatabasePostgresql::executarSQL($sql);
        $this->SetFillColor(220, 220, 220);
        $this->Cell(250, 6, 'ÓRGÃOS', 1, 0, 'C', 1);
        $this->Cell(30, 6, 'QTDE INFORMADA', 1, 0, 'C', 1);
        $this->Ln();

        $this->SetFillColor(255, 255, 255);
        foreach ($resultado as $orgao) {
           if($orgao->airirpqtpr != 0){
            $this->Cell(250, self::ALTURA_PADRAO, $orgao->eorglidesc, 1, 0, 'C', 1);
            $this->Cell(30, self::ALTURA_PADRAO, converte_valor_estoques($orgao->airirpqtpr), 1, 0, 'C', 1);
            $this->Ln();
           }
        }
    }

    private function unificarChavesItem($item)
    {
        $novoItem = array();

        // Atributos em comum
        $novoItem['sequencialItemIntencao'] = $item->citirpsequ;
        $novoItem['sequencialIntencao'] = $item->cintrpsequ;
        $novoItem['anoIntencao'] = $item->cintrpsano;
        $novoItem['quantidadeConsolidada'] = $item->qtdconsolidada;
        $novoItem['ordem'] = $item->aitirporde;
        $novoItem['valorUnitario'] = $item->vitirpvues;

        // Serviço
        $novoItem['tipo'] = 'CADUS';
        $novoItem['generico'] = false;
        $novoItem['descricao'] = $item->eservpdesc;
        $novoItem['descricaoDetalhada'] = $item->eitirpdescse;
        $novoItem['sequencialItem'] = $item->cservpsequ;
        $novoItem['valorTrpUnitario'] = '---';

        // Material
        if (isset($item->cmatepsequ)) {
            $novoItem['tipo'] = 'CADUM';
            $novoItem['generico'] = ($item->fmatepgene == 'S') ? true : false;
            $novoItem['descricao'] = $item->ematepdesc;
            $novoItem['descricaoDetalhada'] = $item->eitirpdescmat;
            $novoItem['sequencialItem'] = $item->cmatepsequ;
            $novoItem['valorTrpUnitario'] = $this->getValorEstimadoTRP($item->cmatepsequ, 'CADUM');
        }

        return (object) $novoItem;
    }

    private function getValorEstimadoTRP($codigo, $tipo)
    {
        $database = &Conexao();

        if ('CADUM' == $tipo) {
            return converte_valor_estoques(calcularValorTrp($database, 2, $codigo));
        }

        return '---';
    }

    private function getOrgaosIntencao()
    {
        $arrayOrgaos = array();

        $database = &Conexao();
        $sql = Helper_Acompanhar_Visualizar::sqlSelectOrgaosIntencaoInformada($this->chaveIntencao['sequencialIntencao'], $this->chaveIntencao['anoIntencao']);
        $resultado = executarSQL($database, $sql);

        while ($resultado->fetchInto($orgao, DB_FETCHMODE_OBJECT)) {
            $arrayOrgaos[] = $orgao->eorglidesc;
        }

        return implode(', ', $arrayOrgaos);
    }

    private function loadIntencao()
    {
        if (! empty($this->chaveIntencao['sequencialIntencao']) && ! empty($this->chaveIntencao['anoIntencao'])) {
            $database = &Conexao();
            $sql = Helper_Acompanhar_Visualizar::sqlSelectIntencao($this->chaveIntencao['sequencialIntencao'], $this->chaveIntencao['anoIntencao']);
            $resultSet = executarSQL($database, $sql);

            return $resultSet->fetchRow(DB_FETCHMODE_OBJECT);
        }
    }
}

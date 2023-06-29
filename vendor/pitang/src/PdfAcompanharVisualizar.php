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
 *
 * @version   Git: $Id:$
 */
class PdfAcompanharVisualizar extends AbstractPdfRegistroPreco
{
    private $chaveIntencao;
    private $imprimirJustificativa;

    public function __construct($orientacao = 'L', $unidadeMedida = 'mm', $formato = 'A4')
    {
        parent::__construct($orientacao, $unidadeMedida, $formato);
    }

    public function getTitulo()
    {
        return 'Acompanhamento de Intenção de Registro de Preço - IRP';
    }

    public function gerarRelatorio($name = 'intencao_')
    {
        $this->dadosIntencao();
        $this->listarOrgaosPorSituacaoResposta();

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
            
            $l = $this->getInstance()->GetStringHeight(120, 6, trim($intencao->xintrpobje), "L");
            $altura = ceil($l / 2);
            if ($l <= 6) {
                $altura = $l;
            } 
            
            $this->Cell(50, $altura, " OBJETO ", 1, 0, "L", 1);
            $this->MultiCell(230, 6, $intencao->xintrpobje, 1, "L", 0);        

            $l = $this->getInstance()->GetStringHeight(250, 6, trim($this->getOrgaosIntencao()), "L");
            if ($l <= 6) {
                $l = 6;
            } 
            $this->Cell(50, $l, " ÓRGÃOS ", 1, 0, "L", 1);
            $this->MultiCell(230, 5, $this->getOrgaosIntencao(), 1, "L", 0);
            
            $l = $this->getInstance()->GetStringHeight(113, 6, trim($intencao->xintrpobse), "L");
            $altura = ceil($l / 2) + 1;
            if ($l <= 6) {
                $altura = $l;
            }
            $this->Cell(50, $altura, " OBSERVAÇÃO ", 1, 0, "L", 1);
            $this->MultiCell(230, 5, $intencao->xintrpobse, 1, 0, "L", 0);
        }
    }

    private function listarOrgaosPorSituacaoResposta()
    {
        $situacaoRespostas = array();
        $orgaosPorResposta = array(
            'A' => null,
            'I' => null,
            '' => null
        );
        
        $database = ClaDatabasePostgresql::getConexao();
        $ordem = 'situacao';
        $sql = Helper_Acompanhar_Visualizar::sqlSelectOrgaosIntencao($this->chaveIntencao['sequencialIntencao'], $this->chaveIntencao['anoIntencao'], $ordem);
        
        $resultado = executarSQL($database, $sql);
        $orgao = null;
        while ($resultado->fetchInto($orgao, DB_FETCHMODE_OBJECT)) {
            if ($orgao->frinrpsitu == 'A') {
                $orgaosPorResposta['A'][] = $orgao;
            } elseif ($orgao->frinrpsitu == 'I') {
                $orgaosPorResposta['I'][] = $orgao;
            } else {
                $orgaosPorResposta[''][] = $orgao;
            }
            
            $situacaoRespostas[] = $orgao->frinrpsitu;
        }
        
        $this->exibirTabelaBloco(array_unique($situacaoRespostas), $orgaosPorResposta);
    }
    function ImprimirJustificativa($imprimir){
        $this->imprimirJustificativa = $imprimir; 
    }
    function ConsultaJustificativa($codigoOrgao, $anoIntencao){
        $database = ClaDatabasePostgresql::getConexao();
        $chaveIntencao = $this->chaveIntencao;
        $sql = "SELECT xrinrpjust FROM sfpc.tbrespostaintencaorp ";
        $sql .= " WHERE cintrpsequ = ".$chaveIntencao['sequencialIntencao'];
        $sql .= " AND corglicodi = ".$codigoOrgao;
        $sql .= " AND frinrpsitu = 'A'";
        $sql .= " AND cintrpsano = ".$anoIntencao;
        $resultado = executarSQL($database, $sql);
        $row = null;
        while ($resultado->fetchInto($row, DB_FETCHMODE_OBJECT)) {
            return $row;
        }
             
    }

    private function exibirTabelaBloco($situacoesFiltradas, $orgaosPorResposta)
    {
        if (in_array('A', $situacoesFiltradas)) {
            $this->SetFillColor(220, 220, 220);
            $this->Cell(280, 5, ' ÓRGÃO(S) COM INTENÇÃO INFORMADA ', 1, 0, 'C', 1);
            $this->Ln();
            
            $this->SetFillColor(255, 255, 255);
            $this->Cell(200, 5, ' ÓRGÃO ', 1, 0, 'C', 1);
            $this->Cell(80, 5, ' DATA DE ÚLTIMA ALTERAÇÃO ', 1, 0, 'C', 1);
            $this->Ln();
            $imprimirJustificativa = $this->imprimirJustificativa;
            if($imprimirJustificativa == true){
                foreach ($orgaosPorResposta['A'] as $orgaoIntencaoInformada) {
                    $justificativa = $this->ConsultaJustificativa($orgaoIntencaoInformada->corglicodi, $this->chaveIntencao['anoIntencao']);
                    $this->Cell(200, 5, $orgaoIntencaoInformada->eorglidesc, 1, 0, 'L', 1);
                    $this->Cell(80, 5, ClaHelper::converterDataBancoParaBr($orgaoIntencaoInformada->trinrpulat), 1, 0, 'C', 1);
                    $this->Ln();
                    $this->SetFillColor(220, 220, 220);
                    $this->Cell(280, 5, ' JUSTIFICATIVA ', 1, 0, 'C', 1);
                    $this->Ln();
                    $this->Cell(280, 10, $justificativa->xrinrpjust, 1, 0, 'L', 1);
                    $this->Ln();
                }
            }else{
                foreach ($orgaosPorResposta['A'] as $orgaoIntencaoInformada) {
                    $this->Cell(200, 5, $orgaoIntencaoInformada->eorglidesc, 1, 0, 'L', 1);
                    $this->Cell(80, 5, ClaHelper::converterDataBancoParaBr($orgaoIntencaoInformada->trinrpulat), 1, 0, 'C', 1);
                    $this->Ln();
                }
            }
        }
        
        if (in_array('I', $situacoesFiltradas)) {
            $this->SetFillColor(220, 220, 220);
            $this->Cell(280, 5, ' ÓRGÃO(S) COM INTENÇÃO EM RASCUNHO ', 1, 0, 'C', 1);
            $this->Ln();
            
            $this->SetFillColor(255, 255, 255);
            $this->Cell(200, 5, ' ÓRGÃO ', 1, 0, 'C', 1);
            $this->Cell(80, 5, ' DATA DE ÚLTIMA ALTERAÇÃO ', 1, 0, 'C', 1);
            $this->Ln();
            
            foreach ($orgaosPorResposta['I'] as $orgaoIntencaoInformada) {
                $this->Cell(200, 5, $orgaoIntencaoInformada->eorglidesc, 1, 0, 'L', 1);
                $this->Cell(80, 5, ClaHelper::converterDataBancoParaBr($orgaoIntencaoInformada->trinrpulat), 1, 0, 'C', 1);
                $this->Ln();
            }
        }
        
        if (in_array(null, $situacoesFiltradas)) {
            $this->SetFillColor(220, 220, 220);
            $this->Cell(280, 5, ' ÓRGÃO(S) SEM PREENCHER A INTENÇÃO ', 1, 0, 'C', 1);
            $this->Ln();
            
            $this->SetFillColor(255, 255, 255);
            $this->Cell(280, 5, ' ÓRGÃO ', 1, 0, 'C', 1);
            $this->Ln();
            
            foreach ($orgaosPorResposta[''] as $orgaoIntencaoInformada) {
                $this->Cell(280, 4, $orgaoIntencaoInformada->eorglidesc, 1, 0, 'L', 1);
                $this->Ln();
            }
        }
    }

    private function getOrgaosIntencao()
    {
        $arrayOrgaos = array();
        
        $database = ClaDatabasePostgresql::getConexao();
        $sql = Helper_Acompanhar_Visualizar::sqlSelectOrgaosIntencao($this->chaveIntencao['sequencialIntencao'], $this->chaveIntencao['anoIntencao']);
        $resultado = executarSQL($database, $sql);
        $orgao = null;
        while ($resultado->fetchInto($orgao, DB_FETCHMODE_OBJECT)) {
            $arrayOrgaos[] = $orgao->eorglidesc;
        }
        
        return implode(", ", $arrayOrgaos);
    }

    private function loadIntencao()
    {
        if (! empty($this->chaveIntencao['sequencialIntencao']) && ! empty($this->chaveIntencao['anoIntencao'])) {
            $database = ClaDatabasePostgresql::getConexao();
            $sql = Helper_Acompanhar_Visualizar::sqlSelectIntencao($this->chaveIntencao['sequencialIntencao'], $this->chaveIntencao['anoIntencao']);
            $resultSet = executarSQL($database, $sql);
            
            return $resultSet->fetchRow(DB_FETCHMODE_OBJECT);
        }
    }
}




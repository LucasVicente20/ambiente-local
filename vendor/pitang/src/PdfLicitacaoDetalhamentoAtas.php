<?php
// 220038--
/**
 * Portal da DGCO
 *
 * PHP version 5
 *
 * LICENSE: This source file is subject to version 3.01 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_01.txt. If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @category RegistroPreco/Intencao
 * @package RegistroPreco/Intencao/AcompanharVisualizar
 * @author José Almir <jose.almir@pitang.com>
 * @copyright 2014 EMPRESA MUNICIPAL DE INFORMÁTICA - EMPREL
 * @license http://www.php.net/license/3_01.txt PHP License 3.01
 * @version CVS: $Id: PdfLicitacaoDetalhamentoAtas.php,v 1.1.2.1 2014/10/08 17:39:16 rlfo Exp $
 */

/**
 * -----------------------------------------------------------------------------
 * HISTORICO
 * -----------------------------------------------------------------------------
 * Alterado: José Almir <jose.almir@pitang.com>
 * Data: 23/09/2014
 * Objetivo: [CR125258]: REDMINE 67 (Registro de Preço)
 */
class PdfLicitacaoDetalhamentoAtas extends AbstractPdfRegistroPreco
{
    const ALTURA_PADRAO = 4;
    private $ano;
    private $processo;

    /**
     *
     * @param string $orientacao            
     * @param string $unidadeMedida            
     * @param string $formato            
     */
    public function __construct($orientacao = "L", $unidadeMedida = "mm", $formato = "A4")
    {
        parent::__construct($orientacao, $unidadeMedida, $formato);
    }

    /**
     *
     * {@inheritDoc}
     *
     * @see AbstractPdfRegistroPreco::getTitulo()
     */
    public function getTitulo()
    {
        return "Detalhamento de ata Licitação";
    }

    /* Método responsável por gerar o relatório */
    public function gerarRelatorio($name = 'DetalhamentoAta_')
    {

        $dadoLicitacao = $this->dadosAtaLicitacao();
        
        if (! empty($dadoLicitacao['carpnosequ'])) {
            $itensIntencoes = $this->consultarItemRegistroPreco($dadoLicitacao['carpnosequ']);
        }
        

        if ($itensIntencoes != null) {
            $this->montarDadosItensIntencao($itensIntencoes);
        }
        
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        $this->Output($name. date('d/m/Y_h_i_s') .'.pdf', 'I');
    }

    /**
     */
    private function loadLicitacaoAta()
    {
        $orgaoUsuario = FuncoesUsuarioLogado::obterOrgaoUsuarioLogado();

        $valoresProcesso = explode("-", $this->processo);

        $licitacaoAtas = $this->consultarLicitacaoAtasInterna($valoresProcesso[1], $this->processo, $orgaoUsuario, $_SESSION["numeroAta"]);
                
        return $licitacaoAtas;
    }

    /**
     *
     * @param unknown $seguencialAtaInterna            
     */
    private function consultarItemRegistroPreco($seguencialAtaInterna)
    {
        $resultados = array();
        
        $db = Conexao();
        $sql = $this->sqlItemAtaRegistroPreco($seguencialAtaInterna);
        $resultado = executarSQL($db, $sql);
        $itemsRegistroDePreco = null;
        while ($resultado->fetchInto($itemsRegistroDePreco, DB_FETCHMODE_OBJECT)) {
            array_push($resultados, $itemsRegistroDePreco);
        }
        
        return $resultados;
    }

    /**
     */
    private function dadosAtaLicitacao()
    {
        $licitacaoAtas = $this->loadLicitacaoAta();

        $ccenpocorg = $_SESSION['valor_ccenpocorg'];
        $ccenpounid = $_SESSION['valor_ccenpounid'];

        $numeroAta = $ccenpocorg . str_pad($ccenpounid, 2, '0', STR_PAD_LEFT);
        $numeroAta .= "." . str_pad($licitacaoAtas["carpincodn"], 4, "0", STR_PAD_LEFT) . "/" . $licitacaoAtas["aarpinanon"];

        $this->SetFillColor(220, 220, 220);
        
        $this->Cell(50, 6, " Comissão ", 1, 0, "L", 1);
        $this->Cell(230, 6, $licitacaoAtas["ecomlidesc"], 1, 0, "L", 0);
        $this->Ln();
        
        $this->Cell(50, 6, "Processo", 1, 0, "L", 1);
        $this->Cell(230, 6, substr($licitacaoAtas["clicpoproc"] + 10000, 1), 1, 0, "L", 0);
        $this->Ln();
        
        $this->Cell(50, 6, " Ano ", 1, 0, "L", 1);
        $this->Cell(230, 6, $licitacaoAtas["alicpoanop"], 1, 0, "L", 0);
        $this->Ln();
        
        $this->Cell(50, 6, " Modalidade ", 1, 0, "L", 1);
        $this->Cell(230, 6, $licitacaoAtas["emodlidesc"], 1, 0, "L", 0);
        $this->Ln();
        
        /* Verificar esse valor */
        $this->Cell(50, 6, " Licitação ", 1, 0, "L", 1);
        $this->Cell(230, 6, substr($licitacaoAtas["clicpocodl"] + 10000, 1), 1, 0, "L", 0);
        $this->Ln();
        
        $this->Cell(50, 6, " Ano Licitação ", 1, 0, "L", 1);
        $this->Cell(230, 6, $licitacaoAtas["alicpoanol"], 1, 0, "L", 0);
        $this->Ln();
        
        $this->Cell(50, 6, " Orgão Licitação ", 1, 0, "L", 1);
        $this->Cell(230, 6, $licitacaoAtas["eorglidesc"], 1, 0, "L", 0);
        $this->Ln();
        
        $this->Cell(50, 6, " Nº Ata ", 1, 0, "L", 1);
        $this->Cell(230, 6, $numeroAta, 1, 0, "L", 0);
        $this->Ln();
        
        $this->Cell(50, 6, " Data Inicial ", 1, 0, "L", 1);
        $this->Cell(230, 6, DataBarra($licitacaoAtas["tarpindini"]), 1, 0, "L", 0);
        $this->Ln();
        
        $this->Cell(50, 6, " Vigência ", 1, 0, "L", 1);
        $this->Cell(230, 6, $licitacaoAtas["aarpinpzvg"] . " Meses", 1, 0, "L", 0);
        $this->Ln();
        
        $this->Cell(50, 6, " Fornecedor Original ", 1, 0, "L", 1);
        $this->Cell(230, 6, $licitacaoAtas["nforcrrazs"], 1, 0, "L", 0);
        $this->Ln();

        $this->Cell(50, 6, " Tipo de Controle da Ata ", 1, 0, "L", 1);
        $this->Cell(230, 6, tipoControle($licitacaoAtas["farpnotsal"]), 1, 0, "L", 0);
        $this->Ln();
        
        $this->SetFillColor(220, 220, 220);
        
        $this->Cell(15, 6, " ORD.", 1, 0, "C", 1);
        $this->Cell(77, 6, " DESCRIÇÃO MATERIAL/SERVIÇO ", 1, 0, "C", 1);
        $this->Cell(15, 6, " TIPO ", 1, 0, "C", 1);
        $this->Cell(15, 6, " COD. RED.", 1, 0, "C", 1);
        $this->Cell(10, 6, " LOTE ", 1, 0, "C", 1);
        $this->Cell(25, 6, " UND. ", 1, 0, "C", 1);
        $this->Cell(25, 6, " QUANTIDADE ", 1, 0, "C", 1);
        $this->Cell(16, 6, " MARCA ", 1, 0, "C", 1);
        $this->Cell(17, 6, " MODELO ", 1, 0, "C", 1);
        $this->Cell(35, 6, " VALOR HOMOLOGADO. ", 1, 0, "C", 1);
        $this->Cell(30, 6, " VALOR TOTAL ", 1, 0, "C", 1);
        $this->Ln();
        return $licitacaoAtas;
    }

    /**
     *
     * @param unknown $itemRegistroPreco            
     */
    private function montarDadosItensIntencao($itemRegistroPreco)
    {
        $this->SetFillColor(255, 255, 255);
        $h = 6;

        foreach ($itemRegistroPreco as $item) {
            // Informa o número de ordem
            $descricao = ($item->ematepdesc != null) ? $item->ematepdesc : $item->eservpdesc;
            $codigoReduzido = (null != $item->cmatepsequ) ? $item->cmatepsequ : $item->cservpsequ;
            
            $altura = $this->getInstance()->GetStringHeight(77, 6, trim($descricao), "L");
            $alturaMarca = $this->getInstance()->GetStringHeight(16, 6, trim($item->eitarpmarc), "L");
            $alturaModelo = $this->getInstance()->GetStringHeight(17, 6, trim($item->eitarpmode), "L");
            $hm = $altura;
            $hm = ($hm > $alturaMarca) ? $hm : $alturaMarca;
            $hm = ($hm > $alturaModelo) ? $hm : $alturaModelo;
            $altura             = $hm / ($altura / $h);
            $alturaMarca        = $hm / ($alturaMarca / $h);   
            $alturaModelo       = $hm / ($alturaModelo / $h);          
            $tipo = $item->cmatepsequ != null ? "CADUM" : "CADUS";
            $total = $item->vitarpvori * $item->aitarpqtor;
            
            
            $this->Cell(15, $hm, $item->aitarporde, 1, 0, "C", 1);
            
            // Descrição
            $x = $this->GetX();
            $y = $this->GetY();
            
            $this->MultiCell(77, $altura, removeSimbolos($descricao), 1, 0, "C", 1);
            
            $this->SetXY($x + 77, $y);

            $this->Cell(15, $hm, $tipo, 1, 0, "C", 1);
            $this->Cell(15, $hm, $codigoReduzido, 1, 0, "C", 1);
            $this->Cell(10, $hm, $item->citarpnuml, 1, 0, "C", 1);
            $this->Cell(25, $hm, $item->eunidmdesc, 1, 0, "C", 1);
            $this->Cell(25, $hm, converte_valor_estoques($item->aitarpqtor), 1, 0, "C", 1);
            
            // Marca
            $x = $this->GetX();
            $y = $this->GetY();            
            $this->MultiCell(16, $alturaMarca, removeSimbolos($item->eitarpmarc), 1, 0, "C", 1);            
            $this->SetXY($x + 16, $y);

            // Modelo
            $x = $this->GetX();
            $y = $this->GetY();            
            $this->MultiCell(17, $alturaModelo, removeSimbolos($item->eitarpmode), 1, 0, "C", 1);            
            $this->SetXY($x + 17, $y);

            $this->Cell(35, $hm, converte_valor_estoques($item->vitarpvori), 1, 0, "C", 1);
            $this->Cell(30, $hm, converte_valor_licitacao($total), 1, 0, "C", 1);

            $this->Ln();
        }
        
        $this->Ln();
    }

    private function consultarLicitacaoAtasInterna($ano, $processo, $orgaoUsuario, $ata)
    {

        $db = Conexao();
        $sql = $this->sqlLicitacaoAtaInterna($ano, $processo, $orgaoUsuario, $ata);
        $resultado = executarSQL($db, $sql);
        $resultado->fetchInto($licitacaoAtas, DB_FETCHMODE_OBJECT);

        $sql = $this->sqlLicitacaoAta($ata);
        $resultado = executarSQL($db, $sql);
        $resultado->fetchInto($atas, DB_FETCHMODE_OBJECT);

        $licitacaoInternaAtas = array();

        $licitacaoInternaAtas['ecomlidesc'] = $licitacaoAtas->ecomlidesc;
        $licitacaoInternaAtas['clicpoproc'] = $licitacaoAtas->clicpoproc;
        $licitacaoInternaAtas['alicpoanop'] = $licitacaoAtas->alicpoanop;
        $licitacaoInternaAtas['emodlidesc'] = $licitacaoAtas->emodlidesc;
        $licitacaoInternaAtas['carpnosequ'] = $atas->carpnosequ;
        $licitacaoInternaAtas['aarpinanon'] = $atas->aarpinanon;
        $licitacaoInternaAtas['eorglidesc'] = $licitacaoAtas->eorglidesc;
        $licitacaoInternaAtas['tarpindini'] = $atas->tarpindini != null ? $atas->tarpindini : "";
        $licitacaoInternaAtas['aarpinpzvg'] = $atas->aarpinpzvg != null ? $atas->aarpinpzvg : "";
        $licitacaoInternaAtas['nforcrrazs'] = $atas->nforcrrazs;
        $licitacaoInternaAtas['clicpocodl'] = $licitacaoAtas->clicpocodl;
        $licitacaoInternaAtas['alicpoanol'] = $licitacaoAtas->alicpoanol;
        $licitacaoInternaAtas['csolcosequ'] = $licitacaoAtas->csolcosequ;
        $licitacaoInternaAtas['corglicodi'] = $licitacaoAtas->corglicodi;
        $licitacaoInternaAtas['carpincodn'] = $atas->carpincodn;
        $licitacaoInternaAtas['farpnotsal'] = $licitacaoAtas->farpnotsal;

        return $licitacaoInternaAtas;
    }

    private function sqlLicitacaoAtaInterna($ano, $processo, $orgaoUsuario, $ata)
    {   
        $codigoGrupo = $this->variables['session']['_cgrempcodi_'];  
        $processoCompleto = $_SESSION['processo_selecionado'];
        $valores = explode('-', $processoCompleto);

        $sql = " SELECT ";
        $sql .= "   DISTINCT l.clicpoproc, ";
        $sql .= "   l.alicpoanop, l.xlicpoobje, l.ccomlicodi, c.ecomlidesc, o.corglicodi, ";
        $sql .= "   o.eorglidesc, m.emodlidesc, l.clicpocodl, l.alicpoanol, l.cgrempcodi, l.clicpocodl, l.alicpoanol, slp.csolcosequ, arpn.farpnotsal ";
        $sql .= " FROM ";
        $sql .= "   sfpc.tblicitacaoportal l  ";
        $sql .= "       LEFT JOIN sfpc.tbataregistropreconova arpn on arpn.carpnosequ = " . $ata;
        $sql .= "       INNER JOIN sfpc.tborgaolicitante o ON o.corglicodi = $valores[4] AND l.corglicodi = o.corglicodi ";
        $sql .= "       INNER JOIN sfpc.tbcomissaolicitacao c ON l.ccomlicodi = c.ccomlicodi ";
        $sql .= "       INNER JOIN sfpc.tbmodalidadelicitacao m ON l.cmodlicodi = m.cmodlicodi ";
        $sql .= "       INNER JOIN sfpc.tbcomissaolicitacao cs ON l.ccomlicodi = cs.ccomlicodi AND cs.cgrempcodi =" . $valores[2];
        $sql .= "       LEFT OUTER JOIN sfpc.tbsolicitacaolicitacaoportal slp ON slp.alicpoanop = $ano ";
        $sql .= "           AND slp.clicpoproc = $valores[0] AND slp.cgrempcodi = $valores[2] AND slp.corglicodi = $valores[4]";
        $sql .= " WHERE ";
        //$sql .= "     l.alicpoanop = $ano AND l.clicpoproc = $processo AND l.cgrempcodi =" . $codigoGrupo;

        $sql .= " l.clicpoproc = %d AND l.alicpoanop = %d AND l.cgrempcodi = %d AND l.ccomlicodi = %d AND l.corglicodi = %d";
        $sql = sprintf($sql, $valores[0], $valores[1], $valores[2], $valores[3], $valores[4]);

        return $sql;
    }

    private function sqlLicitacaoAta($ata)
    {
        $sql = " select a.carpnosequ, a.tarpindini, a.aarpinpzvg, f.nforcrrazs, a.aforcrsequ, a.carpincodn, a.aarpinanon from sfpc.tbataregistroprecointerna a";
        $sql .= " inner join sfpc.tbfornecedorcredenciado f";
        $sql .= " on a.aforcrsequ = f.aforcrsequ";
        $sql .= " where a.carpnosequ =" . $ata;
        $sql .= " group by f.nforcrrazs, a.carpnosequ,a.tarpindini,a.aarpinpzvg,a.aforcrsequ,a.carpincodn, a.aarpinanon";
        $sql .= " order by a.carpnosequ asc";

        return $sql;
    }

    /**
     *
     * @param unknown $sequencialAta            
     */
    private function sqlItemAtaRegistroPreco($sequencialAta)
    {
        $sql = "
        SELECT
            i.aitarporde,
            mpo.ematepdesc,
            i.eitarpdescmat,
            srvp.eservpdesc,
            i.eitarpdescse,
            i.cmatepsequ,
            i.citarpnuml,
            i.aitarpqtor,
            i.vitarpvatu,
            i.cservpsequ,
            i.eitarpdescse,
            i.vitarpvori,
            i.eitarpmarc,
            i.eitarpmode,
            (
                CASE
                    WHEN i.cmatepsequ IS NOT NULL
                    THEN(
                        SELECT
                            um.eunidmsigl
                        FROM
                            sfpc.tbunidadedemedida um
                        RIGHT JOIN sfpc.tbmaterialportal mp
                            ON mp.cmatepsequ = i.cmatepsequ
                        WHERE
                            um.cunidmcodi = mp.cunidmcodi
                    )
                END
            ) AS eunidmdesc
        FROM
            sfpc.tbitemataregistropreconova i
        LEFT JOIN sfpc.tbmaterialportal mpo ON mpo.cmatepsequ = i.cmatepsequ
        LEFT JOIN sfpc.tbservicoportal srvp ON srvp.cservpsequ = i.cservpsequ
        WHERE
            i.carpnosequ = $sequencialAta
        ";
        
        return $sql;
    }

    /**
     *
     * @param unknown $processo            
     */
    public function setProcesso($processo)
    {
        $this->processo = $processo;
    }

    /**
     *
     * @param unknown $ano            
     */
    public function setAno($ano)
    {
        $this->ano = $ano;
    }
}

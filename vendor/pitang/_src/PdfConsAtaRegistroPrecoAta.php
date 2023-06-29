<?php

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
 * @copyright 2014 EMPRESA MUNICIPAL DE INFORMÁTICA - EMPREL
 * @license http://www.php.net/license/3_01.txt PHP License 3.01
 */
class PdfConsAtaRegistroPrecoAta extends AbstractPdfRegistroPreco
{

    /**
     *
     * @var unknown
     */
    const ALTURA_PADRAO = 4;

    /**
     * [$entidadeAta description]
     *
     * @var [type]
     */
    private $entidadeAta;

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
    public function gerarRelatorio()
    {
        $dadoLicitacao = $this->dadosAtaLicitacao();
        
        if (! empty($dadoLicitacao['carpnosequ'])) {
            $itensIntencoes = $this->consultarItemRegistroPreco($dadoLicitacao['carpnosequ']);
        }
        
        if ($itensIntencoes != null) {
            $this->montarDadosItensIntencao($itensIntencoes);
        }
        
        $this->Output();
    }

    /**
     */
    private function loadLicitacaoAta()
    {
        $orgaoUsuario = FuncoesUsuarioLogado::obterOrgaoUsuarioLogado();
        $objeto = $this->getEntidadeAta();
        if ($objeto->carpnotiat == 'I') {
            $licitacaoAtas = $this->consultarLicitacaoAtasInterna($objeto->alicpoanop, $objeto->clicpoproc, $orgaoUsuario);
        } else {
            $licitacaoAtas = $this->consultarLicitacaoAtasExterna(new Negocio_ValorObjeto_Carpnosequ($objeto->carpnosequ));
        }
        
        return $licitacaoAtas;
    }

    /**
     * [consultarItemRegistroPreco description]
     *
     * @param [type] $seguencialAtaInterna
     *            [description]
     * @return [type] [description]
     */
    private function consultarItemRegistroPreco($seguencialAtaInterna)
    {
        $sql = $this->sqlItemAtaRegistroPreco($seguencialAtaInterna);
        $resultado = ClaDatabasePostgresql::executarSQL($sql);
        
        ClaDatabasePostgresql::hasError($resultado);
        
        return $resultado;
    }

    /**
     * [dadosAtaLicitacao description]
     *
     * @return [type] [description]
     */
    private function dadosAtaLicitacao()
    {
        $licitacaoAtas = $this->loadLicitacaoAta();
        $entidadeAta = $this->getEntidadeAta();
        $this->SetFillColor(220, 220, 220);
        if ($entidadeAta->carpnotiat == "I") {
            $this->Cell(50, 6, " Comissão ", 1, 0, "L", 1);
            $this->Cell(230, 6, $licitacaoAtas["ecomlidesc"], 1, 0, "L", 0);
            $this->Ln();
        }
        
        $this->Cell(50, 6, "Processo", 1, 0, "L", 1);
        $this->Cell(230, 6, $licitacaoAtas["clicpoproc"], 1, 0, "L", 0);
        $this->Ln();
        
        if ($entidadeAta->carpnotiat == "I") {
            $this->Cell(50, 6, " Ano ", 1, 0, "L", 1);
            $this->Cell(230, 6, $licitacaoAtas["alicpoanop"], 1, 0, "L", 0);
            $this->Ln();
        }
        
        $this->Cell(50, 6, " Modalidade ", 1, 0, "L", 1);
        $this->Cell(230, 6, $licitacaoAtas["emodlidesc"], 1, 0, "L", 0);
        $this->Ln();
        
        if ($entidadeAta->carpnotiat == "I") {
            /* Verificar esse valor */
            $this->Cell(50, 6, " Licitação ", 1, 0, "L", 1);
            $this->Cell(230, 6, "", 1, 0, "L", 0);
            $this->Ln();
            
            $this->Cell(50, 6, " Ano Licitação ", 1, 0, "L", 1);
            $this->Cell(230, 6, $licitacaoAtas["alicpoanol"], 1, 0, "L", 0);
            $this->Ln();
        }
        
        $this->Cell(50, 6, " Orgão Licitação ", 1, 0, "L", 1);
        $this->Cell(230, 6, $licitacaoAtas["eorglidesc"], 1, 0, "L", 0);
        $this->Ln();
        
        $this->Cell(50, 6, " Nº Licitação ", 1, 0, "L", 1);
        $this->Cell(230, 6, $licitacaoAtas["carpnosequ"], 1, 0, "L", 0);
        $this->Ln();
        
        $this->Cell(50, 6, " Data Inicial ", 1, 0, "L", 1);
        $this->Cell(230, 6, date('d/m/Y H:i:s', strtotime($licitacaoAtas["tarpindini"])), 1, 0, "L", 0);
        $this->Ln();
        
        $this->Cell(50, 6, " Vigência ", 1, 0, "L", 1);
        $this->Cell(230, 6, $licitacaoAtas["aarpinpzvg"], 1, 0, "L", 0);
        $this->Ln();
        
        $this->Cell(50, 6, " Fornecedor Original ", 1, 0, "L", 1);
        $this->Cell(230, 6, $licitacaoAtas["nforcrrazs"], 1, 0, "L", 0);
        $this->Ln();
        
        $this->SetFillColor(220, 220, 220);
        
        $this->Cell(15, 6, " ORD.", 1, 0, "C", 1);
        $this->Cell(100, 6, " DESCRIÇÃO MATERIAL/SERVIÇO ", 1, 0, "C", 1);
        $this->Cell(15, 6, " TIPO ", 1, 0, "C", 1);
        $this->Cell(15, 6, " COD. RED.", 1, 0, "C", 1);
        $this->Cell(15, 6, " LOTE ", 1, 0, "C", 1);
        $this->Cell(30, 6, " UND. ", 1, 0, "C", 1);
        $this->Cell(25, 6, " QUANTIDADE ", 1, 0, "C", 1);
        $this->Cell(35, 6, " VALOR HOMOLOGADO. ", 1, 0, "C", 1);
        $this->Cell(30, 6, " VALOR TOTAL ", 1, 0, "C", 1);
        $this->Ln();
        return $licitacaoAtas;
    }

    /**
     * [montarDadosItensIntencao description]
     *
     * @param [type] $itemRegistroPreco
     *            [description]
     * @return [type] [description]
     */
    private function montarDadosItensIntencao($itemRegistroPreco)
    {
        $this->SetFillColor(255, 255, 255);
        foreach ($itemRegistroPreco as $item) {
            // Informa o número de ordem
            $descricao = ($item->ematepdesc != null) ? $item->ematepdesc : $item->eservpdesc;
            $codigoReduzido = (null != $item->cmatepsequ) ? $item->cmatepsequ : $item->cservpsequ;
            $altura = $this->getInstance()->GetStringHeight(100, 6, trim($descricao), "L");
            $tipo = $item->cmatepsequ != null ? "CADUM" : "CADUS";
            $total = $item->vitarpvatu * $item->aitarpqtor;
            
            $this->Cell(15, $altura, $item->aitarporde, 1, 0, "C", 1);
            // storing the X and Y co-ordinates and then setting them after the write
            $x = $this->GetX();
            $y = $this->GetY();
            $this->MultiCell(100, 6, $descricao, 1, 0, "C", 1);
            $this->SetXY($x + 100, $y);
            $this->Cell(15, $altura, $tipo, 1, 0, "C", 1);
            $this->Cell(15, $altura, $codigoReduzido, 1, 0, "C", 1);
            $this->Cell(15, $altura, $item->citarpnuml, 1, 0, "C", 1);
            $this->Cell(30, $altura, $item->eunidmdesc, 1, 0, "C", 1);
            $this->Cell(25, $altura, converte_valor_estoques($item->aitarpqtor), 1, 0, "C", 1);
            $this->Cell(35, $altura, converte_valor_estoques($item->vitarpvatu), 1, 0, "C", 1);
            $this->Cell(30, $altura, converte_valor_licitacao($total), 1, 0, "C", 1);
            $this->Ln();
        }
        
        $this->Ln();
    }

    /**
     * [consultarLicitacaoAtasInterna description]
     *
     * @param [type] $ano
     *            [description]
     * @param [type] $processo
     *            [description]
     * @param [type] $orgaoUsuario
     *            [description]
     * @return [type] [description]
     */
    private function consultarLicitacaoAtasInterna($ano, $processo, $orgaoUsuario)
    {
        $db = Conexao();
        $sql = $this->sqlLicitacaoAtaInterna($ano, $processo, $orgaoUsuario);
        $resultado = executarSQL($db, $sql);
        $licitacaoAtas = null;
        $resultado->fetchInto($licitacaoAtas, DB_FETCHMODE_OBJECT);
        
        $sql = $this->sqlLicitacaoAta($ano, $processo, $orgaoUsuario);
        $resultado = executarSQL($db, $sql);
        $atas = null;
        $resultado->fetchInto($atas, DB_FETCHMODE_OBJECT);
        
        $licitacaoInternaAtas = array();
        
        $licitacaoInternaAtas['ecomlidesc'] = $licitacaoAtas->ecomlidesc;
        $licitacaoInternaAtas['clicpoproc'] = $licitacaoAtas->clicpoproc;
        $licitacaoInternaAtas['alicpoanop'] = $licitacaoAtas->alicpoanop;
        $licitacaoInternaAtas['emodlidesc'] = $licitacaoAtas->emodlidesc;
        $licitacaoInternaAtas['carpnosequ'] = $atas->carpnosequ;
        $licitacaoInternaAtas['aarpinanon'] = $licitacaoAtas->aarpinanon;
        $licitacaoInternaAtas['eorglidesc'] = $licitacaoAtas->eorglidesc;
        $licitacaoInternaAtas['tarpindini'] = $atas->tarpindini != null ? $atas->tarpindini : "";
        $licitacaoInternaAtas['aarpinpzvg'] = $atas->aarpinpzvg != null ? $atas->aarpinpzvg : "";
        $licitacaoInternaAtas['nforcrrazs'] = $atas->nforcrrazs;
        $licitacaoInternaAtas['alicpoanol'] = $licitacaoAtas->alicpoanol;
        
        return $licitacaoInternaAtas;
    }

    /**
     * [consultarLicitacaoAtasExterna description]
     *
     * @param Negocio_ValorObjeto_Carpnosequ $carpnosequ
     *            [description]
     * @return [type] [description]
     */
    private function consultarLicitacaoAtasExterna(Negocio_ValorObjeto_Carpnosequ $carpnosequ)
    {
        $sql = $this->sqlLicitacaoAtaExterna($carpnosequ->getValor());
        
        $resultado = ClaDatabasePostgresql::executarSQL($sql);
        
        ClaDatabasePostgresql::hasError($resultado);
        
        $licitacaoAtas = current($resultado);
        
        $licitacaoInternaAtas = array();
        
        $licitacaoInternaAtas['ecomlidesc'] = '';
        $licitacaoInternaAtas['clicpoproc'] = $licitacaoAtas->earpexproc;
        $licitacaoInternaAtas['alicpoanop'] = '';
        $licitacaoInternaAtas['emodlidesc'] = $licitacaoAtas->emodlidesc;
        $licitacaoInternaAtas['carpnosequ'] = $licitacaoAtas->carpnosequ;
        $licitacaoInternaAtas['aarpinanon'] = $licitacaoAtas->aarpexanon;
        $licitacaoInternaAtas['eorglidesc'] = $licitacaoAtas->earpexorgg;
        $licitacaoInternaAtas['tarpindini'] = $licitacaoAtas->tarpexdini != null ? $licitacaoAtas->tarpexdini : "";
        $licitacaoInternaAtas['aarpinpzvg'] = $licitacaoAtas->aarpexpzvg != null ? $licitacaoAtas->aarpexpzvg : "";
        $fornecedorOriginal = RegistroPreco_UI_Helper_Fornecedor::mapear(Helper_RegistroPreco::getFornecedorDaAtaExterna($licitacaoAtas->carpnosequ));
        $licitacaoInternaAtas['nforcrrazs'] = RegistroPreco_UI_Helper_Fornecedor::trataDadosDoFornecedorDaAta($fornecedorOriginal);
        $licitacaoInternaAtas['alicpoanol'] = $licitacaoAtas->alicpoanol;
        
        return $licitacaoInternaAtas;
    }

    /**
     * [sqlLicitacaoAtaInterna description]
     *
     * @param [type] $ano
     *            [description]
     * @param [type] $processo
     *            [description]
     * @param [type] $orgaoUsuario
     *            [description]
     * @return [type] [description]
     */
    private function sqlLicitacaoAtaInterna($ano, $processo, $orgaoUsuario)
    {
        $sql = "select distinct l.clicpoproc,";
        $sql .= " l.alicpoanop,";
        $sql .= " l.xlicpoobje,";
        $sql .= " l.ccomlicodi,";
        $sql .= " c.ecomlidesc,";
        $sql .= " o.corglicodi,";
        $sql .= " o.eorglidesc,";
        $sql .= " m.emodlidesc,";
        $sql .= " l.clicpocodl,";
        $sql .= " l.alicpoanol";
        $sql .= " from sfpc.tblicitacaoportal l";
        $sql .= " inner join sfpc.tborgaolicitante o";
        $sql .= " on o.corglicodi=" . $orgaoUsuario;
        $sql .= " and l.corglicodi = o.corglicodi";
        $sql .= " inner join sfpc.tbcomissaolicitacao c";
        $sql .= " on l.ccomlicodi = c.ccomlicodi";
        $sql .= " inner join sfpc.tbmodalidadelicitacao m";
        $sql .= " on l.cmodlicodi = m.cmodlicodi";
        $sql .= " where l.alicpoanop =" . $ano;
        $sql .= " and l.clicpoproc =" . $processo;
        return $sql;
    }

    /**
     *
     * @param integer $carpnosequ            
     */
    private function sqlLicitacaoAtaExterna($carpnosequ)
    {
        return sprintf("
                SELECT
                       arpe.*,
                       m.emodlidesc
                  FROM
                       sfpc.tbataregistroprecoexterna arpe
                       INNER JOIN sfpc.tbmodalidadelicitacao m
                               ON m.cmodlicodi = arpe.cmodlicodi
                 WHERE arpe.carpnosequ = %d
            ", $carpnosequ);
    }

    /**
     *
     * @param unknown $ano            
     * @param unknown $processo            
     * @param unknown $orgaoUsuario            
     */
    private function sqlLicitacaoAta($ano, $processo, $orgaoUsuario)
    {
        $sql = " select a.carpnosequ, a.tarpindini,a.aarpinpzvg, f.nforcrrazs from sfpc.tbataregistroprecointerna a";
        $sql .= " inner join sfpc.tbfornecedorcredenciado f";
        $sql .= " on a.aforcrsequ = f.aforcrsequ";
        $sql .= " where a.clicpoproc =" . $processo;
        $sql .= " and a.alicpoanop =" . $ano;
        $sql .= " and a.corglicodi =" . $orgaoUsuario;
        $sql .= " and a.cgrempcodi =" . $_SESSION['_cgrempcodi_'];
        $sql .= " group by f.nforcrrazs, a.carpnosequ,a.tarpindini,a.aarpinpzvg,f.nforcrrazs";
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
     * Gets the [$entidadeAta description].
     *
     * @return [type]
     */
    public function getEntidadeAta()
    {
        return $this->entidadeAta;
    }

    /**
     * Sets the [$entidadeAta description].
     *
     * @param stdClass $entidadeAta
     *            the entidade ata
     *            
     * @return self
     */
    public function setEntidadeAta($entidadeAta)
    {
        $this->entidadeAta = $entidadeAta;
        
        return $this;
    }
}

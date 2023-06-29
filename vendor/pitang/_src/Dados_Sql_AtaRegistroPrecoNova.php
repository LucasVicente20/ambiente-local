<?php

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
 * @category  Pitang_Registro_Preco
 * @package   registropreco
 * @author    Pitang Agile TI <contato@pitang.com>
 * @copyright 2014 EMPRESA MUNICIPAL DE INFORMÁTICA - EMPREL
 * @license   http://www.php.net/license/3_01.txt PHP License 3.01
 * @version   GIT: v1.18.0-17-g9920068
 */
#------------------------------------------
# Alterado: Pitang Agile TI - Caio Coutinho
# Data: 28/06/2018
# Objtivo: Tarefa Redine #194536
#------------------------------------------
class Dados_Sql_AtaRegistroPrecoNova
{

    /**
     * Seleciona a descricao da comissão da licitação utilizando o código CCOMLICODI
     *
     * @param integer $ccomlicodi
     *
     * @return string
     */
    public static function selecionaComissaoLicitacaoPorComissao($ccomlicodi)
    {
        $sql = "SELECT cl.ecomlidesc FROM sfpc.tbcomissaolicitacao cl";
        $sql .= " WHERE cl.ccomlicodi = %d";

        return sprintf($sql, $ccomlicodi);
    }

    public static function sqlLicitacaoAtaInterna($ano, $processo, $orgaoUsuario)
    {
        $ano = filter_var($ano, FILTER_SANITIZE_NUMBER_INT);
        $processo = filter_var($processo, FILTER_SANITIZE_NUMBER_INT);
        $orgaoUsuario = filter_var($orgaoUsuario, FILTER_SANITIZE_NUMBER_INT);

        $sql = "
        select
        distinct l.clicpoproc,
        l.alicpoanop,
        l.xlicpoobje,
        l.ccomlicodi,
        c.ecomlidesc,
        o.corglicodi,
        o.eorglidesc,
        m.emodlidesc,
        l.clicpocodl,
        l.alicpoanol,
        l.cgrempcodi
    from
        sfpc.tblicitacaoportal l inner join sfpc.tborgaolicitante o on
        l.corglicodi = o.corglicodi inner join sfpc.tbcomissaolicitacao c on
        l.ccomlicodi = c.ccomlicodi inner join sfpc.tbmodalidadelicitacao m on
        l.cmodlicodi = m.cmodlicodi
    where
        l.clicpoproc = %d
        and l.alicpoanop = %d
        and l.corglicodi = %d
        ";

        return sprintf($sql, $processo, $ano, $orgaoUsuario);
    }


    public static function sqlLicitacaoAtaInternaNova($ano, $processo, $orgaoUsuario, $comissao, $grupo)
    {
        $ano            = filter_var($ano, FILTER_SANITIZE_NUMBER_INT);
        $processo       = filter_var($processo, FILTER_SANITIZE_NUMBER_INT);
        $orgaoUsuario   = filter_var($orgaoUsuario, FILTER_SANITIZE_NUMBER_INT);
        $comissao       = filter_var($comissao, FILTER_SANITIZE_NUMBER_INT);
        $grupo          = filter_var($grupo, FILTER_SANITIZE_NUMBER_INT);

        $sql = "
        select
        distinct l.clicpoproc,
        l.alicpoanop,
        l.xlicpoobje,
        l.ccomlicodi,
        c.ecomlidesc,
        o.corglicodi,
        o.eorglidesc,
        m.emodlidesc,
        l.clicpocodl,
        l.alicpoanol,
        l.cgrempcodi
    from
        sfpc.tblicitacaoportal l inner join sfpc.tborgaolicitante o on
        l.corglicodi = o.corglicodi inner join sfpc.tbcomissaolicitacao c on
        l.ccomlicodi = c.ccomlicodi inner join sfpc.tbmodalidadelicitacao m on
        l.cmodlicodi = m.cmodlicodi
    where
        l.clicpoproc = %d
        and l.alicpoanop = %d
        and l.corglicodi = %d
        and l.ccomlicodi = %d
        and l.cgrempcodi = %d
        ";

        return sprintf($sql, $processo, $ano, $orgaoUsuario, $comissao, $grupo);
    }

    /**
     * Seleciona os órgãos participantes de uma ata.
     *
     * @param integer $processo da ata
     * @param integer $ano da ata
     * @param integer $orgaoGestor da ata
     *
     * @return string
     */
    public static function sqlOrgaosParticipantesAta($processo, $ano, $orgaoGestor, $seqAta = null)
    {
        $sql = "
            SELECT distinct o.eorglidesc
            FROM sfpc.tbparticipanteatarp p 
            INNER JOIN sfpc.tborgaolicitante o ON o.corglicodi = p.corglicodi
            WHERE p.carpnosequ IN 
                (SELECT a.carpnosequ
                 FROM sfpc.tbataregistroprecointerna a
                 LEFT OUTER JOIN sfpc.tbsolicitacaolicitacaoportal s ON
                    (s.clicpoproc = a.clicpoproc
                     AND s.alicpoanop = a.alicpoanop
                     AND s.ccomlicodi = a.ccomlicodi
                     AND s.corglicodi = a.corglicodi)
                 WHERE a.clicpoproc   = %d
                     AND a.alicpoanop = %d
                     AND a.corglicodi = %d ";

        if (!is_null($seqAta)){
            $sql .= " AND a.carpnosequ = %d ";
        }

        $sql .=  "ORDER BY a.carpnosequ)";

        if (!is_null($seqAta)){
            return sprintf($sql, $processo, $ano, $orgaoGestor, $seqAta);
        } else {
            return sprintf($sql, $processo, $ano, $orgaoGestor);
        }

    }

    public static function sqlParticipantesLicitacaoAtaInterna($ano, $processo)
    {
        $ano = filter_var($ano, FILTER_SANITIZE_NUMBER_INT);
        $processo = filter_var($processo, FILTER_SANITIZE_NUMBER_INT);

        $sql = "
            SELECT
                o.eorglidesc
            FROM
                sfpc.tblicitacaoportal l
            INNER JOIN sfpc.tborgaolicitante o ON l.corglicodi = o.corglicodi
            WHERE
                l.alicpoanop = %d
                AND l.clicpoproc = %d
        ";

        return sprintf($sql, $ano, $processo);
    }

    /**
     *
     * @param unknown $processo
     * @param unknown $orgao
     * @param unknown $ano
     * @return string
     */
    public static function sqlAtaLicitacao($processo, $orgao, $ano)
    {
        $ano = filter_var($ano, FILTER_SANITIZE_NUMBER_INT);
        $processo = filter_var($processo, FILTER_SANITIZE_NUMBER_INT);
        $orgao = filter_var($orgao, FILTER_SANITIZE_NUMBER_INT);

        $sql = "select a.farpinsitu,a.aarpinpzvg,";
        $sql .= "a.tarpinulat,a.carpnosequ,a.alicpoanop, a.aarpinanon, a.cgrempcodi, a.cusupocodi, ";
        $sql .= "a.corglicodi,s.csolcosequ from sfpc.tbataregistroprecointerna a";
        $sql .= " left outer join sfpc.tbsolicitacaolicitacaoportal s";
        $sql .= " on (s.clicpoproc = a.clicpoproc";
        $sql .= " and s.alicpoanop = a.alicpoanop";
        $sql .= " and s.ccomlicodi = a.ccomlicodi";
        $sql .= " and s.corglicodi = a.corglicodi)";
        $sql .= " where a.clicpoproc =" . $processo;
        $sql .= " and a.alicpoanop =" . $ano;
        $sql .= " and a.corglicodi =" . $orgao;
        $sql .= " order by a.carpnosequ";

        return $sql;
    }


    /**
     *
     * @param unknown $processo (2-2017-1-4-7)
     * @param unknown $orgao
     * @param unknown $ano
     * @return string
     */
    public static function sqlAtaLicitacaoList($processo, $orgao, $ano)
    {
        $ano = filter_var($ano, FILTER_SANITIZE_NUMBER_INT);
        $valores = explode('-', $processo);
        $orgao = filter_var($orgao, FILTER_SANITIZE_NUMBER_INT);

        $sql = "
            SELECT
                arpi.carpnosequ,
                arpi.clicpoproc,
                arpi.alicpoanop,
                arpi.aforcrsequ,
                fc.nforcrrazs,
                arpi.aarpinanon,
                arpi.aarpinpzvg,
                arpi.farpinsitu,               
            
                arpi.cgrempcodi,
                arpi.ccomlicodi,
                arpi.corglicodi,
                arpi.cusupocodi,
                arpi.tarpinulat
                
            FROM
                sfpc.tbataregistroprecointerna arpi
                INNER JOIN sfpc.tbfornecedorcredenciado fc
                        ON arpi.aforcrsequ = fc.aforcrsequ
                

            WHERE 1 = 1
                AND arpi.clicpoproc = %d
                AND arpi.alicpoanop = %d
                AND arpi.cgrempcodi = %d
                AND arpi.ccomlicodi = %d
                AND arpi.corglicodi = %d
           ORDER BY
                arpi.carpnosequ
        ";

        return sprintf($sql, $valores[0], $valores[1], $valores[2], $valores[3], $valores[4]);
    }




    /**
     *
     * @param unknown $processo
     * @param unknown $orgao
     * @param unknown $ano
     * @param unknown $chaveAta
     */
    public static function sqlAtaPorchave($processo, $orgao, $ano, $chaveAta)
    {
        $ano = filter_var($ano, FILTER_SANITIZE_NUMBER_INT);
        $processo = filter_var($processo, FILTER_SANITIZE_NUMBER_INT);
        $orgao = filter_var($orgao, FILTER_SANITIZE_NUMBER_INT);
        $chaveAta = filter_var($chaveAta, FILTER_SANITIZE_NUMBER_INT);
        
        $sql = "
            SELECT a.carpnosequ, a.aarpinpzvg, to_char(a.tarpindini, 'DD/MM/YYYY') AS tarpindini, a.cgrempcodi, a.cusupocodi, f.nforcrrazs,
                   f.aforcrccgc, f.aforcrccpf, f.nforcrfant, d.edoclinome, a.alicpoanop, a.carpnoseq1, a.corglicodi
              FROM sfpc.tbataregistroprecointerna a ";
        $sql .= " LEFT OUTER JOIN sfpc.tbfornecedorcredenciado f";
        $sql .= " ON f.aforcrsequ = a.aforcrsequ";
        $sql .= " LEFT OUTER JOIN sfpc.tbdocumentolicitacao d";
        $sql .= " ON d.clicpoproc =a.clicpoproc";
        $sql .= " AND d.clicpoproc = %d";
        $sql .= " AND d.corglicodi = %d";
        $sql .= " AND d.alicpoanop = %d";
        $sql .= " WHERE a.carpnosequ = %d";

        return sprintf($sql, $processo, $orgao, $ano, $chaveAta);
    }

    public static function sqlMaximoId()
    {
        $sql = "
        SELECT
            MAX(a.carpnosequ) AS carpnosequ
        FROM sfpc.tbataregistropreconova a
        ";

        return $sql;
    }

    public static function sqlParticipantesAta($processo, $ano, $orgaoGestor, $carpnosequ){
        $sql = "
        SELECT distinct p.*
        FROM sfpc.tbparticipanteatarp p 
        INNER JOIN sfpc.tborgaolicitante o ON o.corglicodi = p.corglicodi
        WHERE p.carpnosequ IN 
            (SELECT a.carpnosequ
             FROM sfpc.tbataregistroprecointerna a
             LEFT OUTER JOIN sfpc.tbsolicitacaolicitacaoportal s ON
                (s.clicpoproc = a.clicpoproc
                 AND s.alicpoanop = a.alicpoanop
                 AND s.ccomlicodi = a.ccomlicodi
                 AND s.corglicodi = a.corglicodi)
             WHERE a.clicpoproc   = %d
                 AND a.alicpoanop = %d
                 AND a.corglicodi = %d 
                 AND a.carpnosequ = %d
             ORDER BY a.carpnosequ)";

        return sprintf($sql, $processo, $ano, $orgaoGestor, $carpnosequ);
    }

    public static function sqlParticipantesItensAta($seqAta) {
        $sql = 
            "SELECT * FROM sfpc.tbparticipanteitematarp PIARP
             LEFT JOIN sfpc.tbitemataregistropreconova IARPN ON
                IARPN.carpnosequ = PIARP.carpnosequ
                AND IARPN.citarpsequ = PIARP.citarpsequ
             WHERE PIARP.carpnosequ = %d";
        return sprintf($sql, $seqAta);
    }    

    public function sqlConsultarSCCDoProcesso($ata, $orgao, $codigoItem, $tipoItem, $seqItem) {

        $sql = "    SELECT DISTINCT ATAI.carpnosequ, SOL.csolcosequ, ITEMS.aitescqtso, SOL.tsolcodata, ol.eorglidesc, SOL.clicpoproc, SOL.alicpoanop, SOL.cgrempcodi, SOL.ccomlicodi, SOL.corglicodi, SOL.corglicodi as orgao_agrupamento, SOL.corglicod1 as orgao_gestor
                FROM sfpc.tbataregistroprecointerna ATAI, sfpc.tbitemataregistropreconova ITEMA,
                    sfpc.tbsolicitacaocompra SOL, sfpc.tbitemsolicitacaocompra ITEMS, sfpc.tborgaolicitante ol ";


        $sql .= "  WHERE  1=1
                AND ATAI.carpnosequ  = $ata
                AND SOL.corglicodi  = $orgao
                AND ATAI.carpnosequ  = SOL.carpnosequ
                AND ITEMS.citarpsequ  = $seqItem
                AND SOL.csolcosequ   = ITEMS.csolcosequ
                AND SOL.ctpcomcodi   = 5
                AND SOL.fsolcorpcp   = 'P'
                AND ol.corglicodi = SOL.corglicodi ";

        if ($tipoItem == 'M') {
            $sql .= " and ITEMS.cmatepsequ = " . $codigoItem;
        } else {
            $sql .= " and ITEMS.cservpsequ = " . $codigoItem;
        }

        $sql .= "  AND ITEMS.carpnosequ = ATAI.carpnosequ ";

        return $sql;
    }    

}

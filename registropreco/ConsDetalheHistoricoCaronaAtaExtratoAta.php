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
 * @category  Pitang Registro Preço
 * @package   registropreco
 * @author    Pitang Agile TI <contato@pitang.com>
 * @copyright 2014 EMPRESA MUNICIPAL DE INFORMÁTICA - EMPREL
 * @license   http://www.php.net/license/3_01.txt PHP License 3.01
 * @version   Git: $Id:$
 * -----------------------------------------------------------------------------------------------
 * Alterado: Pitang Agile TI - Caio Coutinho
 * Data:     18/06/2018
 * Objetivo: Ajustar botão voltar em compras para o histórico em extrato (Registro de preço)
 * -----------------------------------------------------------------------------------------------
 * Alterado: Pitang Agile TI - Caio Coutinho
 * Data:     26/06/2018
 * Objetivo: Tarefa Redmine 194536
 * -----------------------------------------------------------------------------------------------
 * Alterado: Pitang Agile TI - Caio Coutinho
 * Data:     28/06/2018
 * Objetivo: Tarefa Redmine 198000
 * -----------------------------------------------------------------------------------------------
 * Alterado: Pitang Agile TI - Caio Coutinho
 * Data:     04/07/2018
 * Objetivo: Tarefa Redmine 198149
 * -----------------------------------------------------------------------------------------------
 * Alterado: Pitang Agile TI - Caio Coutinho
 * Data:     05/07/2018
 * Objetivo: Tarefa Redmine 194536
 * -----------------------------------------------------------------------------------------------
 * Alterado: Pitang Agile TI - Caio Coutinho
 * Data:     11/07/2018
 * Objetivo: Tarefa Redmine 198981
 * -----------------------------------------------------------------------------------------------
 * Alterado: Lucas Baracho
 * Data:     15/08/2018
 * Objetivo: Tarefa Redmine 200288
 * -----------------------------------------------------------------------------------------------
 * Alterado: Pitang Agile TI - Caio Coutinho
 * Data:     24/08/2018
 * Objetivo: Tarefa Redmine 201047
 * -----------------------------------------------------------------------------------------------
 * Alterado: Pitang Agile TI - Caio Coutinho
 * Data:     09/11/2018
 * Objetivo: Tarefa Redmine 205803
 * -----------------------------------------------------------------------------------------------
 * Alterado: Pitang Agile TI - Caio Coutinho
 * Data:     15/11/2018
 * Objetivo: Tarefa Redmine 214323
 * -----------------------------------------------------------------------------------------------
 * Alterado: Lucas Baracho
 * Data:     29/05/2019
 * Objetivo: Tarefa Redmine 216712
 * -----------------------------------------------------------------------------------------------
 */

 // 220038--

# Acesso ao arquivo de funções #
include "../funcoes.php";

if (! @require_once dirname(__FILE__) . "/../bootstrap.php") {
    throw new Exception("Error Processing Request - Bootstrap", 1);
}

/**
 * A camada de Dados conterá o código que irá realizar todo o acesso aos dados.
 *
 * @author jfsi
 *
 */
class RegistroPreco_Dados_ConsDetalheHistoricoParticipanteExtratoAta extends Dados_Abstrata
{

    /**
     *
     * @param unknown $tipoAta
     * @param unknown $ata
     * @param unknown $material
     * @param unknown $servico
     */
    public function sqlConsultarAta($tipoAta, $ata, $material, $servico, $seqItem)
    {
        $sql = "SELECT ata.*, iarpn.aitarporde, iarpn.cmatepsequ, iarpn.cservpsequ, iarpn.eitarpdescmat, iarpn.eitarpdescse, iarpn.aitarpqtor, iarpn.aitarpqtat, iarpn.vitarpvatu, iarpn.vitarpvori, arpn.carpnotiat, iarpn.eitarpdescmat, iarpn.eitarpdescse, iarpn.citarpnuml, mp2.fmatepgene,
                    (CASE
                        when iarpn.cmatepsequ IS NOT NULL
                            then (select mp.ematepdesc from sfpc.tbmaterialportal mp
                            where mp.cmatepsequ = iarpn.cmatepsequ)
                        when iarpn.cservpsequ IS NOT NULL
                            then (select sp.eservpdesc from sfpc.tbservicoportal sp
                            where sp.cservpsequ = iarpn.cservpsequ)
                        END) AS descricaoItem
                    FROM  ";

        if ($tipoAta == "I") {
            $sql .= " sfpc.tbataregistroprecointerna ata ";
        } else {
            $sql .= " sfpc.tbataregistroprecoexterna ata ";
        }

        $sql .= "   INNER JOIN sfpc.tbataregistropreconova arpn
                        ON arpn.carpnosequ = ata.carpnosequ 
                        AND arpn.carpnotiat = '" . $tipoAta . "'";

        $sql .= "  JOIN sfpc.tbitemataregistropreconova iarpn
                        ON iarpn.carpnosequ = ata.carpnosequ 
                    LEFT JOIN sfpc.tbmaterialportal mp2 ON mp2.cmatepsequ = iarpn.cmatepsequ
                    LEFT JOIN sfpc.tbservicoportal sp2 ON sp2.cservpsequ = iarpn.cservpsequ                
            WHERE";
        $sql .= " ata.carpnosequ = " . $ata;
        $sql .= " AND iarpn.citarpsequ = " . $seqItem;

        if ($material != null) {
            $sql .= " and iarpn.cmatepsequ=" . $material;
        } else {
            $sql .= " and iarpn.cservpsequ=" . $servico;
        }

        return $sql;
    }

    public function sqlConsultarUtilizadoAtaAnterior($ata) {
        $sql = " SELECT isc.carpnosequ,
                sum(isc.aitescqtso) as soma
                FROM sfpc.tbitemsolicitacaocompra isc
                INNER JOIN sfpc.tbsolicitacaocompra sc ON
                sc.csolcosequ = isc.csolcosequ
                AND sc.carpnosequ = isc.carpnosequ
                AND sc.fsolcorpcp = 'P' OR (sc.fsolcorpcp = 'C' AND sc.fsolcoautc = 'S')
                INNER JOIN sfpc.tbataregistroprecointerna arpi ON
                sc.carpnosequ = arpi.carpnosequ
                WHERE arpi.carpnoseq1 = " . $ata['ata'] . "
                AND sc.corglicodi = " . $ata['orgao'] . "
                GROUP BY isc.carpnosequ;";
        return $sql;
    }

    public function sqlConsultarUtilizadoSccAtaAnterior($ata) {
        $sql = "  SELECT sc.carpnosequ,
                sum(isc.aitescqtso) AS soma
                FROM sfpc.tbitemsolicitacaocompra isc
                INNER JOIN sfpc.tbsolicitacaocompra sc ON
                sc.csolcosequ = isc.csolcosequ
                AND sc.carpnosequ = isc.carpnosequ
                INNER JOIN sfpc.tbataregistroprecointerna arpi ON
                sc.carpnosequ = arpi.carpnosequ
                WHERE arpi.carpnoseq1 = " . $ata['ata'] . "
                AND sc.fsolcorpcp = 'C' AND sc.fsolcoautc = 'S' --CARONA               
                GROUP BY sc.carpnosequ;";

        return $sql;
    }

    private function sqlConsultarCentroDeCustoUsuario($cgrempcodi, $cusupocodi, $corglicodi)
    {
        $sql = "
        SELECT
               ccp.ccenpocorg, ccp.ccenpounid, ccp.corglicodi
          FROM sfpc.tbcentrocustoportal ccp
         WHERE 1=1
        ";

        if ($corglicodi != null || $corglicodi != "") {
            $sql .= " AND ccp.corglicodi = %d";
        }

        return sprintf($sql, $corglicodi);
    }

    public function sqlItemDaAtaInterna($nAta, $nItem)
    {
        $sql = "SELECT * FROM ";
        $sql .= "   SFPC.TBITEMATAREGISTROPRECONOVA ITEMA  ";
        $sql .= "   LEFT JOIN SFPC.TBITEMCARONAINTERNAATARP ITEMCI ON ITEMA.CARPNOSEQU = ITEMCI.CARPNOSEQU and ITEMA.CITARPSEQU = ITEMCI.CITARPSEQU "; // TBITEMCARONAINTERNAATARP            

        $sql .= "   LEFT JOIN SFPC.TBSOLICITACAOCOMPRA ITEMSC ON ITEMA.CARPNOSEQU = ITEMSC.CARPNOSEQU  ";    // TBSOLICITACAOCOMPRA
        $sql .= "   LEFT JOIN SFPC.TBITEMSOLICITACAOCOMPRA ITEMS ON ITEMSC.CSOLCOSEQU = ITEMS.CSOLCOSEQU AND ITEMA.CITARPSEQU = ITEMS.CITARPSEQU "; // TBITEMSOLICITACAOCOMPRA
        $sql .= "   WHERE 1=1 AND ITEMA.CARPNOSEQU = " . $nAta;
        $sql .= "   AND ITEMA.CITARPSEQU = " . $nItem;
        $sql .= "   AND ITEMSC.csitsocodi IN (3,4) ";
        $sql .= "   AND ITEMCI.fitcrpsitu = 'A' ";

        return $sql;
    }

    public function sqlItemDaAtaExterna($nAta, $nItem) {

        $sql = "  SELECT SUM(COALESCE(coei.acoeitqtat,0)) as qtdtotal ";
        $sql .= "  FROM sfpc.tbcaronaorgaoexternoitem COEI ";
        $sql .= "  WHERE    coei.carpnosequ = " . $nAta . " AND ";
        $sql .= "  coei.citarpsequ = " . $nItem;

        return $sql;

    }


    public function consultarDCentroDeCustoUsuario($cgrempcodi, $cusupocodi, $corglicodi)
    {
        $db = Conexao();
        $sql = $this->sqlConsultarCentroDeCustoUsuario($cgrempcodi, $cusupocodi, $corglicodi);

        $res = executarSQL($db, $sql);

        $itens = array();
        $item = null;
        while ($res->fetchInto($item, DB_FETCHMODE_OBJECT)) {
            $itens[] = $item;
        }
        $this->hasError($res);
        $db->disconnect();
        return $itens;
    }

    function consultarItemDaAtaInterna($nAta, $nItem)
    {
        $db = Conexao();
        $sql = $this->sqlItemDaAtaInterna($nAta, $nItem);
        $res = executarSQL($db, $sql);
        $itens = null;
        $item = null;

        while ($res->fetchInto($item, DB_FETCHMODE_OBJECT)) {
            $itens = $item;
        }

        $this->hasError($res);
        return $itens;
    }

    function consultarItemDaAtaExterna($nAta, $nItem)
    {
        $db = Conexao();
        $sql = $this->sqlItemDaAtaExterna($nAta, $nItem);
        $res = executarSQL($db, $sql);
        $itens = null;
        $item = null;

        while ($res->fetchInto($item, DB_FETCHMODE_OBJECT)) {
            $itens = $item;
        }

        $this->hasError($res);
        return $itens;
    }

    /**
     *
     * @param unknown $ata
     * @param unknown $codigoItem
     * @param unknown $tipoItem
     * @param unknown $comparacao
     * @param unknown $orgao
     */
    public function sqlConsultarSCCDoProcesso($tipoAta, $ata, $codigoItem, $tipoItem, $comparacao, $seqItem)
    {
        $sql =  " SELECT DISTINCT ATAI.carpnosequ, SOL.csolcosequ, ITEMS.aitescqtso, SOL.tsolcodata, ol.eorglidesc, ";
        $sql .= "    SOL.clicpoproc, SOL.alicpoanop, SOL.corglicodi as orgao_agrupamento, ITEMS.vitescunit, ";
        $sql .= "    SOL.corglicod1 AS orgao_gestor, SOL.corglicodi, ";

        if ($tipoAta == "I") {
            $sql .= " SOL.cgrempcodi, SOL.ccomlicodi FROM SFPC.tbataregistroprecointerna ATAI ";
        } else {
            $sql .= " ATAI.carpnosequ, ATAI.aarpexanon, ATAI.carpexcodn, ATAI.earpexproc FROM SFPC.tbataregistroprecoexterna ATAI ";
        }

        $sql .= " INNER JOIN sfpc.tbitemataregistropreconova ITEMA ON ITEMA.carpnosequ = ATAI.carpnosequ ";
        $sql .= " INNER JOIN sfpc.tbitemsolicitacaocompra ITEMS ON ITEMS.carpnosequ = ATAI.carpnosequ ";
        $sql .= " INNER JOIN sfpc.tbsolicitacaocompra SOL ON SOL.csolcosequ = ITEMS.csolcosequ AND SOL.carpnosequ = ATAI.carpnosequ ";
        $sql .= " INNER JOIN sfpc.tborgaolicitante ol ON ol.corglicodi = SOL.corglicodi ";

        $sql .= "  WHERE  1=1 ";
        $sql .= "   AND ATAI.carpnosequ  = $ata ";
        $sql .= "   AND ITEMA.citarpsequ  = $seqItem ";
        $sql .= "   AND SOL.ctpcomcodi   = 5 ";
        $sql .= "   AND SOL.fsolcorpcp   = 'C'";
        $sql .= "   AND SOL.fsolcoautc   = 'S'";

        if ($tipoItem == 'M') {
            $sql .= " AND ITEMS.cmatepsequ = " . $codigoItem;
        } else {
            $sql .= " AND ITEMS.cservpsequ = " . $codigoItem;
        }

        return $sql;
    }


    public function sqlConsultarItensAtaCarona($carpnosequ, $itemCodigo, $tipoItem, $seqItem)
    {
        $sql  = "SELECT * ";
        $sql .= "         FROM ";
        $sql .= "             sfpc.tbcaronainternaatarp arpi ";
        $sql .= "             inner join sfpc.tbitemcaronainternaatarp ipa on  ";
        $sql .= "               ipa.carpnosequ = arpi.carpnosequ  ";
        $sql .= "               and ipa.corglicodi = arpi.corglicodi ";
        $sql .= "             inner join sfpc.tbitemataregistropreconova i on ";
        $sql .= "               i.carpnosequ = arpi.carpnosequ ";
        $sql .= "               and i.citarpsequ = ipa.citarpsequ ";
        $sql .= "             left outer join sfpc.tbmaterialportal m on ";
        $sql .= "               i.cmatepsequ = m.cmatepsequ  ";
        $sql .= "             left outer join sfpc.tbunidadedemedida ump on ";
        $sql .= "               ump.cunidmcodi = m.cunidmcodi  ";
        $sql .= "             left outer join sfpc.tbservicoportal s on ";
        $sql .= "               i.cservpsequ = s.cservpsequ ";
        $sql .= "             inner join sfpc.tborgaolicitante o on ";
        $sql .= "               o.corglicodi = arpi.corglicodi ";

        $sql .= "         WHERE ";
        $sql .= "             i.carpnosequ = %d ";

        if ($tipoItem == 'M') {
            $sql .= " and i.cmatepsequ = " . $itemCodigo;
        } else {
            $sql .= " and i.cservpsequ = " . $itemCodigo;
        }

        $sql .= " and i.citarpsequ = " . $seqItem;

        $sql .= "         order by ipa.corglicodi asc  ";

        return sprintf($sql, $carpnosequ);

    }//end sqlConsultarItensAta()


    public function sqlConsultarCaronaOrgaoExterno($tipoAta, $ata, $codigoItem, $tipoItem, $comparacao, $orgao = null, $seqItem)
    {

        $sql = " SELECT 
                    COEIT.ccaroesequ,
                    COEIT.citarpsequ,
                    COEIT.carpnosequ,
                    COE.ecaroeorgg,
                    COEIT.acoeitqtat,
                    COEIT.vcoeitvuti,
                    COE.tcaroeincl,
                    COE.tcaroedaut                   
                FROM
                    sfpc.tbitemataregistropreconova ITEMA,
                    sfpc.tbcaronaorgaoexternoitem COEIT,
                    sfpc.tbcaronaorgaoexterno COE 
                WHERE   COE.carpnosequ  = $ata
                        AND COEIT.citarpsequ = ITEMA.citarpsequ
                        AND COEIT.carpnosequ = ITEMA.carpnosequ
                        AND COE.ccaroesequ = COEIT.ccaroesequ
                        AND COE.carpnosequ = COEIT.carpnosequ  ";

        $sql .= " AND ITEMA.citarpsequ = " . $seqItem;

        if ($tipoItem == 'M') {
            $sql .= " AND ITEMA.cmatepsequ = " . $codigoItem;
        } else {
            $sql .= " AND ITEMA.cservpsequ = " . $codigoItem;
        }

        return $sql;
    }


    public function sqlConsultarQtantidadeSolicitadaDoProcesso($clicpoproc, $alicpoanop, $cgrempcodi, $ccomlicodi, $orgaoGestor, $orgaoAgrupamento, $tipoItem, $codigoItem)
    {

        $sql = "SELECT sol.csolcosequ, sol.asolcoanos, citelpsequ, itel.cmatepsequ, itel.cservpsequ, itel.aitelpqtso 
                FROM 
                    sfpc.tbsolicitacaolicitacaoportal solic, 
                    sfpc.tbsolicitacaocompra sol, 
                    sfpc.tbitemlicitacaoportal itel, 
                    sfpc.tbitemsolicitacaocompra ites
                WHERE 1=1
                AND solic.clicpoproc = $clicpoproc 
                AND solic.alicpoanop = $alicpoanop 
                AND solic.cgrempcodi = $cgrempcodi 
                AND solic.ccomlicodi = $ccomlicodi
                AND solic.corglicodi = $orgaoGestor                    
                AND solic.clicpoproc = itel.clicpoproc
                AND solic.alicpoanop = itel.alicpoanop
                AND solic.cgrempcodi = itel.cgrempcodi
                AND solic.ccomlicodi = itel.ccomlicodi
                AND solic.corglicodi = itel.corglicodi
                AND sol.corglicodi   = $orgaoAgrupamento
                AND sol.csolcosequ   = solic.csolcosequ
                AND sol.csolcosequ   = ites.csolcosequ
                AND itel.citelpsequ  = ites.citescsequ ";



        if ($tipoItem == 'M') {
            $sql .= " and itel.cmatepsequ = " . $codigoItem;
        } else {
            $sql .= " and itel.cservpsequ = " . $codigoItem;
        }

        return $sql;
    }

    public function sqlConsultarQtantidadeSolicitadaDoProcessoExterno($carpnosequ, $citarpsequ)
    {

        $sql = "SELECT sol.csolcosequ, sol.asolcoanos, itel.cmatepsequ, itel.cservpsequ, itel.aitescqtso 
                FROM 
                    sfpc.tbsolicitacaocompra sol
                LEFT JOIN sfpc.tbitemsolicitacaocompra itel ON sol.csolcosequ = itel.csolcosequ
                WHERE 1=1
                AND sol.carpnosequ   = $carpnosequ
                AND sol.csitsocodi in (3,4)
                AND sol.fsolcorpcp = 'C'
                AND itel.carpnosequ = $carpnosequ
                AND itel.citarpsequ = $citarpsequ";

        /*AND solic.clicpoproc = $clicpoproc
        AND solic.alicpoanop = $alicpoanop
        AND solic.cgrempcodi = $cgrempcodi
        AND solic.ccomlicodi = $ccomlicodi
            sfpc.tbsolicitacaolicitacaoportal solic,
        AND solic.corglicodi = $orgaoGestor
        AND solic.clicpoproc = itel.clicpoproc
        AND solic.alicpoanop = itel.alicpoanop
        AND solic.cgrempcodi = itel.cgrempcodi
        AND solic.ccomlicodi = itel.ccomlicodi
        AND solic.corglicodi = itel.corglicodi*/


        /*if ($tipoItem == 'M') {
            $sql .= " and itel.cmatepsequ = " . $codigoItem;
        } else {
            $sql .= " and itel.cservpsequ = " . $codigoItem;
        }*/
        //print_r($sql); exit;
        return $sql;
    }

    /**
     *
     * @param unknown $numeroAta
     * @param unknown $item
     * @param unknown $tipoItem
     */
    public function sqlConsultarQuantidadeParticipanteItem($numeroAta, $item, $tipoItem)
    {
        $sql = "    SELECT DISTINCT ATAI.carpnosequ, SOL.csolcosequ, ITEMS.aitescqtso, SOL.corglicodi as orgao_agrupamento, SOL.corglicod1 as orgao_gestor
                FROM sfpc.tbataregistroprecointerna ATAI, sfpc.tbitemataregistropreconova ITEMA,
                    sfpc.tbsolicitacaocompra SOL, sfpc.tbitemsolicitacaocompra ITEMS
                WHERE  1=1
                AND ATAI.carpnosequ  = $numeroAta
                AND ATAI.carpnosequ  = SOL.carpnosequ
                AND SOL.csolcosequ   = ITEMS.csolcosequ
                AND SOL.ctpcomcodi   = 5
                AND SOL.fsolcorpcp   = 'C' ";

        if ($tipoItem == 'M') {
            $sql .= " and ITEMS.cmatepsequ = " . $item;
        } else {
            $sql .= " and ITEMS.cservpsequ = " . $item;
        }


        $sql .= "  AND ITEMS.carpnosequ = ATAI.carpnosequ ";


        return $sql;
    }

    public function sqlConsultarTipoControle($ata)
    {
        $sql = " SELECT arpn.farpnotsal 
                 FROM sfpc.tbataregistropreconova arpn
                 WHERE arpn.carpnosequ = %d";

        return sprintf($sql, $ata);
    }
}

/**
 * A camada de Negócio conterá o código que irá implementar todas as regras de negócio do sistema.
 *
 * Utiliza serviços da camada de Dados.
 *
 * @author jfsi
 *
 */
class RegistroPreco_Negocio_ConsDetalheHistoricoParticipanteExtratoAta extends Negocio_Abstrata
{

    /**
     */
    public function __construct()
    {
        $this->setDados(new RegistroPreco_Dados_ConsDetalheHistoricoParticipanteExtratoAta());
    }

    /**
     */
    public function consultarOrgaosParticipantesAtas()
    {
        $sql = $this->getDados()->sqlOrgaoParticipante();
        $resultado = ClaDatabasePostgresql::executarSQL($sql);

        ClaDatabasePostgresql::hasError($resultado);

        return $resultado;
    }

    /**
     *
     * @param unknown $ata
     * @param unknown $itemAta
     * @param unknown $tipoAta
     */
    public function consultarQuantidadeParticipanteItem($ata, $itemAta, $tipoAta)
    {
        $sql = $this->getDados()->sqlConsultarQuantidadeParticipanteItem($ata, $itemAta, $tipoAta);
        $resultado = ClaDatabasePostgresql::executarSQL($sql);

        ClaDatabasePostgresql::hasError($resultado);

        return $resultado;
    }

    /**
     *
     * @param unknown $tipoAta
     * @param unknown $ata
     * @param unknown $material
     * @param unknown $servico
     */
    public function consultarExtratoAta($tipoAta, $ata, $material, $servico, $seqItem)
    {
        $sql = $this->getDados()->sqlConsultarAta($tipoAta, $ata, $material, $servico, $seqItem);

        $resultado = ClaDatabasePostgresql::executarSQL($sql);

        ClaDatabasePostgresql::hasError($resultado);

        return $resultado;
    }

    public function consultarUltilizadoAtaAnterior($ata)
    {
        $sql = $this->getDados()->sqlConsultarUtilizadoAtaAnterior($ata);
        $resultado = ClaDatabasePostgresql::executarSQL($sql);        
        ClaDatabasePostgresql::hasError($resultado);

        return $resultado;
    }

    public function consultarUtilizadoSccAtaAnterior($ata)
    {
        $sql = $this->getDados()->sqlConsultarUtilizadoSccAtaAnterior($ata);
        $resultado = ClaDatabasePostgresql::executarSQL($sql);
        ClaDatabasePostgresql::hasError($resultado);

        return $resultado;
    }

    /**
     *
     * @param unknown $ata
     * @param unknown $item
     * @param unknown $tipoItem
     * @param unknown $comparacao
     * @param unknown $orgao
     */
    public function consultarSccDoProcesso($tipoAta, $ata, $item, $tipoItem, $comparacao, $seqItem)
    {
        $sql = $this->getDados()->sqlConsultarSCCDoProcesso($tipoAta, $ata, $item, $tipoItem, $comparacao, $seqItem);
        $resultado = ClaDatabasePostgresql::executarSQL($sql);

        ClaDatabasePostgresql::hasError($resultado);

        return $resultado;
    }

    public function consultarItensAtaCarona($carpnosequ, $itemCodigo, $tipoItem, $seqItem)
    {
        $db = Conexao();
        $sql = $this->getDados()->sqlConsultarItensAtaCarona($carpnosequ, $itemCodigo, $tipoItem, $seqItem);
        $res = executarSQL($db, $sql);
        $itens = array();
        $item = null;
        $itemTipo = new stdClass();
        while ($res->fetchInto($item, DB_FETCHMODE_OBJECT)) {
            $item->tipoItem = $itemTipo->tipoItem;
            $itens[] = $item;
        }
        return $itens;
    }//end consultarItensAta()


    public function consultarCaronaOrgaoExterno($tipoAta, $ata, $item, $tipoItem, $comparacao, $orgao, $seqItem)
    {
        $sql = $this->getDados()->sqlConsultarCaronaOrgaoExterno($tipoAta, $ata, $item, $tipoItem, $comparacao, $orgao, $seqItem);
        $resultado = ClaDatabasePostgresql::executarSQL($sql);

        ClaDatabasePostgresql::hasError($resultado);

        return $resultado;
    }



    public function consultarQuantidadeSolicitadoDoProcesso($clicpoproc, $alicpoanop, $cgrempcodi, $ccomlicodi, $orgaoGestor, $orgaoAgrupamento, $tipoItem, $codigoItem)
    {
        $sql = $this->getDados()->sqlConsultarQtantidadeSolicitadaDoProcesso($clicpoproc, $alicpoanop, $cgrempcodi, $ccomlicodi, $orgaoGestor, $orgaoAgrupamento, $tipoItem, $codigoItem);
        $resultado = ClaDatabasePostgresql::executarSQL($sql);

        ClaDatabasePostgresql::hasError($resultado);

        return $resultado;
    }

    public function consultarQuantidadeSolicitadoDoProcessoExterno($carpnosequ, $citarpsequ)
    {
        $sql = $this->getDados()->sqlConsultarQtantidadeSolicitadaDoProcessoExterno($carpnosequ, $citarpsequ);
        $resultado = ClaDatabasePostgresql::executarSQL($sql);

        ClaDatabasePostgresql::hasError($resultado);

        return $resultado;
    }

    public function consultarTipoControle($ata) {
        $sql = $this->getDados()->sqlConsultarTipoControle($ata);
        $resultado = ClaDatabasePostgresql::executarSQL($sql);

        ClaDatabasePostgresql::hasError($resultado);

        return $resultado;
    }
}

/**
 * A camada de Adaptação e Transformação conterá o código que tratará a lógica de apresentação dos resultados
 * das requisições dos usuários e a troca de dados com sistemas externos.
 *
 * Utiliza serviços da camada de Negócio.
 *
 * @author jfsi
 *
 */
class RegistroPreco_Adaptacao_ConsDetalheHistoricoParticipanteExtratoAta extends Adaptacao_Abstrata
{
    public $quantidadeUtilizadaTotalParticipante = 0;
    public $quantidadeUtilizadaTotalGestor       = 0;
    public $quantidadeAta                        = 0;
    public $valorAta                             = 0;

    /**
     */
    public function __construct()
    {
        $this->setNegocio(new RegistroPreco_Negocio_ConsDetalheHistoricoParticipanteExtratoAta());
    }

    /**
     *
     * @param ArrayObject $dto
     */
    public function configurarValoresAta(ArrayObject $dto)
    {
        if ($dto['tipoItem'] == "M") {
            $material = $dto['item'];
        } else {
            $servico = $dto['item'];
        }
        return $this->getNegocio()->consultarExtratoAta($dto['tipoAta'], $dto['ata'], $material, $servico, $dto['seqItem']);
    }

    public function consultarUltilizadoAtaAnterior($ata)
    {
        return $this->getNegocio()->consultarUltilizadoAtaAnterior($ata);
    }

    public function consultarUtilizadoSccAtaAnterior($ata)
    {
        return $this->getNegocio()->consultarUtilizadoSccAtaAnterior($ata);
    }

    /**
     *
     * @param ArrayObject $dto
     */
    public function configurarValoresScc(ArrayObject $dto)
    {
        return $this->getNegocio()->consultarQuantidadeParticipanteItem($dto['ata'], $dto['item'], $dto['tipoItem']);
    }
}

/**
 * A camada de Interface Gráfica com o Usuário é a camada que conterá o código que irá implementar a interação
 * do sistema com os usuários (telas, relatórios e troca de dados).
 *
 * Utiliza serviços da camada de Adaptação e Transformação.
 *
 * @author jfsi
 *
 */
class RegistroPreco_UI_ConsDetalheHistoricoParticipanteExtratoAta extends UI_Abstrata
{

    /**
     *
     * @param unknown $ata
     */
    private function plotarBlocoResultadoAta($ata)
    {
        $ata = $ata[0];
        $consultarfor = new RegistroPreco_Dados_ConsDetalheHistoricoParticipanteExtratoAta();
        $tipoControle = $this->getAdaptacao()->getNegocio()->consultarTipoControle($ata->carpnosequ);
        $fatorMaxCarona = getFatorQtdMaxCarona(ClaDatabasePostgresql::getConexao());
        
        if($ata->carpnotiat == "I"){
            $dtoConsulta = $consultarfor->consultarDCentroDeCustoUsuario($ata->cgrempcodi, $ata->cusupocodi, $ata->corglicodi);
            $objetoDado = current($dtoConsulta);
            $numeroAtaFormatado = $objetoDado->ccenpocorg . str_pad($objetoDado->ccenpounid, 2, '0', STR_PAD_LEFT);
            $numeroAtaFormatado .= "." . str_pad($ata->carpincodn, 4, "0", STR_PAD_LEFT) . "/" . $ata->aarpinanon;

        }else{
            $numeroAtaFormatado = str_pad($ata->carpexcodn, 4, "0", STR_PAD_LEFT) . "/" . $ata->aarpexanon;
        }

        $quantidade         = (int) $ata->aitarpqtat != 0 ? (int) $ata->aitarpqtat : (int) $ata->aitarpqtor;
        $item               = $ata->cmatepsequ != null ? $ata->cmatepsequ : $ata->cservpsequ;
        $descricaoDetalhada = '';
        $hiddenDescricaoDet = 'display: none';
        $tipoServico        = $ata->cmatepsequ != null ? 'CADUM' : 'CADUS';

        if($tipoServico == 'CADUS' || ($tipoServico == 'CADUM' && $ata->fmatepgene == 'S')) {
            $hiddenDescricaoDet = '';
            $descricaoDetalhada = ($tipoServico == 'CADUS') ? $ata->eitarpdescse : $ata->eitarpdescmat;
        }

        if(!empty($ataAnterior) || $tipoControle[0]->farpnotsal != 1) {
            $itemAtaInterna = getQtdTotalOrgaoCaronaInterna(
                ClaDatabasePostgresql::getConexao(), null, $_GET['ata'], $_GET['seqItem']
            );
            
            $itemAtaExterna = getQtdTotalOrgaoCaronaExterna(
                ClaDatabasePostgresql::getConexao(), $_GET['ata'], $_GET['seqItem']
            );
            $itemAtaInternaIncDir = getQtdTotalOrgaoCaronaInternaInclusaoDireta(
                ClaDatabasePostgresql::getConexao(), $_GET['ata'], $_GET['seqItem']
            );
        } else {
            $itemAtaInterna = getQtdTotalOrgaoCaronaInterna(
                ClaDatabasePostgresql::getConexao(), null, $_GET['ata'], $_GET['seqItem'], 'vitescunit'
            );
            $itemAtaExterna = getQtdTotalOrgaoCaronaExterna(
                ClaDatabasePostgresql::getConexao(), $_GET['ata'], $_GET['seqItem'], 'vcoeitvuti'
            );
            $itemAtaInternaIncDir = getQtdTotalOrgaoCaronaInternaInclusaoDireta(
                ClaDatabasePostgresql::getConexao(), $_GET['ata'], $_GET['seqItem'], null, 'vitcrpvuti'
            );
        }
        
        $saldoGeralCaronaAta = ($itemAtaInterna + $itemAtaExterna + $itemAtaInternaIncDir);

        // Verificar ata anterior
        $exibirAtaAnterior = 'display:none;';
        $qtdUtilizadaAnterior = 0;
        unset($_SESSION['VALOR_ATA_ANTERIOR_C']); // remover valor da sessão
        if(!empty($ataAnterior) && $tipoControle[0]->farpnotsal != 1) {
            $exibirAtaAnterior = '';
            $itemAtaInterna_ = getQtdTotalOrgaoCaronaInterna(
                ClaDatabasePostgresql::getConexao(), null, $ataAnterior[0]->carpnosequ, $_GET['seqItem']
            );
            $itemAtaExterna_ = getQtdTotalOrgaoCaronaExterna(
                ClaDatabasePostgresql::getConexao(), $ataAnterior[0]->carpnosequ, $_GET['seqItem']
            );
            $itemAtaInternaIncDir_ = getQtdTotalOrgaoCaronaInternaInclusaoDireta(
                ClaDatabasePostgresql::getConexao(), $ataAnterior[0]->carpnosequ, $_GET['seqItem']
            );

            $qtdUtilizadaAnterior = ($itemAtaInterna_ + $itemAtaExterna_ + $itemAtaInternaIncDir_);
            $_SESSION['VALOR_ATA_ANTERIOR_C'] = $qtdUtilizadaAnterior;
        }

        if(!empty($sccAtaAnterior) || $tipoControle[0]->farpnotsal != 1) {
            //$saldoGeralCaronaAta += $sccAtaAnterior[0]->soma;
        }

        $valor    = $ata->vitarpvatu != 0 ? $ata->vitarpvatu : $ata->vitarpvori;
        $valorMax = $valor * $fatorMaxCarona;

        $saldoGeralCaronaView = ($tipoControle[0]->farpnotsal != 1) ? converte_valor_licitacao(($quantidade * $fatorMaxCarona) - $saldoGeralCaronaAta) : converte_valor_licitacao($valorMax - $saldoGeralCaronaAta);

        $this->getTemplate()->EXIBIR_VALOR_ATA_ANTERIOR = $exibirAtaAnterior;
        $this->getTemplate()->DISPLAY_QUANTIDADE        = ($tipoControle[0]->farpnotsal == 1) ? 'display:none;' : '';
        $this->getTemplate()->DISPLAY_VALOR             = ($tipoControle[0]->farpnotsal != 1) ? 'display:none;' : '';
        $this->getTemplate()->VALOR_ATA_ANTERIOR        = converte_valor_licitacao($qtdUtilizadaAnterior);
        $this->getTemplate()->NUMEROATA                 = $numeroAtaFormatado;
        $this->getTemplate()->ORDEM                     = $ata->aitarporde;
        $this->getTemplate()->LOTE                      = $ata->citarpnuml;
        $this->getTemplate()->TIPOMATERIAL              = $tipoServico;
        $this->getTemplate()->TIPOMATERIALVALUE         = $item;
        $this->getTemplate()->DESCRICAO                 = $ata->descricaoitem;
        $this->getTemplate()->HIDDEDESCRICAODETALHADA   = $hiddenDescricaoDet;
        $this->getTemplate()->DESCRICAODETALHADA        = $descricaoDetalhada;
        $this->getTemplate()->QUANTIDADE                = converte_valor_licitacao($quantidade);
        $this->getTemplate()->LABEL_QTD_UTLIZADA        = 'Quantidade  Máxima Carona';
        $this->getTemplate()->QTDGESTORPARTICIPANTE     = converte_valor_licitacao($quantidade * $fatorMaxCarona);
        $this->getTemplate()->LABEL_SALDO               = 'Quantidade Utilizada Carona';
        $this->getTemplate()->SALDO_GERAL_CARONAS       = $saldoGeralCaronaView;
        $this->getTemplate()->SALDOGESTORPARTICIPANTE   = converte_valor_licitacao($saldoGeralCaronaAta);
        $this->getTemplate()->VALOR                     = converte_valor_licitacao($valor);
        $this->getTemplate()->VALOR_MAXIMO              = converte_valor_licitacao($valorMax);
        $this->getTemplate()->VALOR_TOTAL               = converte_valor_licitacao($saldoGeralCaronaAta);
        //$this->getTemplate()->BOTAO_VOLTAR              = (isset($_GET['window']) && $_GET['window'] == 0) ? "javascript:enviar('Voltar')" : 'javascript:window.close();';
        $this->getAdaptacao()->quantidadeAta            = $quantidade;
        $this->getAdaptacao()->valorAta                 = $valor;
    }

    /**
     *
     * @param unknown $sccGestor
     * @param unknown $qtdsSolicitada
     */
    private function plotarBlocoSCCGestor($sccGestor, $dto)
    {

        $plotouOrgao = false;
        $qtd_utilizada = 0;
        $qtdSolicitadaAta = 0;
        foreach ($sccGestor as $scc) {
            if($scc->orgao_agrupamento == $scc->orgao_gestor){
                $plotouOrgao = true;

                $sccQuantidade = $this->getAdaptacao()
                    ->getNegocio()
                    ->consultarQuantidadeSolicitadoDoProcesso($scc->clicpoproc, $scc->alicpoanop, $scc->cgrempcodi, $scc->ccomlicodi, $scc->orgao_gestor, $scc->orgao_agrupamento, $dto['tipoItem'], $dto['item']);

                foreach ($sccQuantidade as $quantidade) {
                    $qtdSolicitadaAta += $quantidade->aitelpqtso;
                }

                $this->getTemplate()->ORGAOGESTOR = $scc->eorglidesc;
                //$this->getTemplate()->TIPO_ORGAO = 'GESTOR';
                $this->getTemplate()->IDSOLIC = $scc->csolcosequ;
                $this->getTemplate()->NUMEROSCC = getNumeroSolicitacaoCompra(ClaDatabasePostgresql::getConexao(), $scc->csolcosequ);
                $this->getTemplate()->DATASCC = date("d/m/Y", strtotime($scc->tsolcodata));
                $this->getTemplate()->QTDUTILIZADA = $scc->aitescqtso;
                $qtd_utilizada = (float) ($scc->aitescqtso + $qtd_utilizada);
                $this->getTemplate()->block('bloco_resultado_scc');
            }
        }
        if($plotouOrgao){
            $this->getTemplate()->QTD_UTILIZADA = $qtd_utilizada;
            //$this->getTemplate()->QTD_SOLICITADA = $qtdSolicitadaAta;            
            $this->getAdaptacao()->quantidadeUtilizadaTotalGestor += $qtd_utilizada;

            $this->getTemplate()->block('bloco_orgao_scc');
        }
    }

    /**
     *
     * @param unknown $sccParticipante
     * @param unknown $quantidades
     */
    private function plotarBlocoSCCParticipante($sccParticipante, $dto)
    {
        $plotouOrgao = false;
        $ultimoOrgaoPlotado = null;

        $qtd_utilizada = 0;
        $qtdSolicitadaAta = 0;

        foreach ($sccParticipante as $scc) {

            if($scc->orgao_agrupamento != $scc->orgao_gestor){
                $plotouOrgao = true;

                if($dto['tipoAta'] == 'I') {
                    $sccQuantidade = $this->getAdaptacao()
                        ->getNegocio()
                        ->consultarQuantidadeSolicitadoDoProcesso($scc->clicpoproc, $scc->alicpoanop, $scc->cgrempcodi, $scc->ccomlicodi, $scc->orgao_gestor, $scc->orgao_agrupamento, $dto['tipoItem'], $dto['item']);
                } else {
                    $sccQuantidade = $this->getAdaptacao()
                        ->getNegocio()
                        ->consultarQuantidadeSolicitadoDoProcessoExterno($scc->carpnosequ, $dto['item']);
                }

                foreach ($sccQuantidade as $quantidade) {
                    $qtdSolicitadaAta += $quantidade->aitelpqtso;
                }

                $this->getTemplate()->ORGAOGESTOR = $scc->eorglidesc;
                //$this->getTemplate()->TIPO_ORGAO = 'PARTICIPANTE';
                $this->getTemplate()->IDSOLIC = $scc->csolcosequ;
                $this->getTemplate()->NUMEROSCC = getNumeroSolicitacaoCompra(ClaDatabasePostgresql::getConexao(), $scc->csolcosequ);
                $this->getTemplate()->DATASCC = date("d/m/Y", strtotime($scc->tsolcodata));
                $this->getTemplate()->QTDUTILIZADA = $scc->aitescqtso;
                $qtd_utilizada += $scc->aitescqtso;
                $this->getTemplate()->block('bloco_resultado_scc');
            }
        }

        if($plotouOrgao){
            $this->getTemplate()->QTD_UTILIZADA = $qtd_utilizada;
            //$this->getTemplate()->QTD_SOLICITADA = $qtdSolicitadaAta);

            $this->getAdaptacao()->quantidadeUtilizadaTotalParticipante += $qtd_utilizada;

            $this->getTemplate()->block('bloco_orgao_scc');

        }
    }

    /**
     * @param $sccParticipante
     * @param unknown $quantidades
     *
     * @return array
     */
    private function plotarBlocoInclusaoDiretaCarona($itensAtaCarona, $dto)
    {
        $plotouOrgao = false;
        $ultimoOrgaoPlotado = null;

        $qtd_utilizada = 0;
        $qtdSolicitadaAta = 0;

        foreach ($itensAtaCarona as $item) {
            $plotouOrgao = true;
            $qtd_utilizada += $item->aitcrpqtut;
            $this->getTemplate()->NOMEORGAO = $item->eorglidesc;
            $this->getTemplate()->DATA = date("d/m/Y", strtotime($item->titcrpulat));
            $this->getTemplate()->QTDUTILIZADA = converte_valor_licitacao($item->aitcrpqtut);
            $this->getTemplate()->block('bloco_resultado_inclusao_direta');
        }

        if($plotouOrgao){
            $this->getTemplate()->QTD_UTILIZADA = converte_valor_licitacao($qtd_utilizada);

            //$this->getAdaptacao()->quantidadeUtilizadaTotalParticipante += $qtd_utilizada;
            $this->getTemplate()->block('bloco_inclusao_direta');
        }

        $exibirInclusao = '';
        if($_GET['tipo'] == 'E') {
            $exibirInclusao = 'style="display:none"';
        }

        $this->getTemplate()->EXIBIR_INCLUSAO_DIRETA = $exibirInclusao;
        $this->getTemplate()->block('bloco_tr_inclusao_direta');
    }

    private function plotarBlocoCaronaExterna($sccParticipante, $dto, $orgaos_externo)
    {
        $plotouOrgao        = false;
        $ultimoOrgaoPlotado = null;
        $qtdSolicitadaAta   = 0;

        // Tipo de controle
        $tipoControle = $this->getAdaptacao()->getNegocio()->consultarTipoControle($dto['ata']);
        $textoScc     = ($tipoControle[0]->farpnotsal != 1) ? 'QUANTIDADE UTILIZADA' : 'TOTAL VALOR UTILIZADO';
        $textoTotal   = ($tipoControle[0]->farpnotsal != 1) ? 'TOTAL QUANTIDADE UTILIZADA: ' : 'TOTAL VALOR UTILIZADO: ';
        
        // Organizar por orgao
        foreach ($sccParticipante as $key => $value) {
            $orgaos_externo[$value->ecaroeorgg][] = $value;
        }
        
        foreach ($orgaos_externo as $key => $value) {
            $qtd_utilizada  = 0;
            $plotouOrgao    = true;
            $this->getTemplate()->ORGAOGESTOR = $key;
            foreach ($value as $scc) {
                $dataInclusao = (!empty($scc->tcaroedaut)) ? $scc->tcaroedaut : $scc->tcaroeincl;
                $this->getTemplate()->TEXTO_SCC_UTILIZADO = $textoScc;
                $this->getTemplate()->IDSOLIC = $scc->ccaroesequ;
                $this->getTemplate()->NUMEROSCC = $scc->ccaroesequ;
                $this->getTemplate()->DATASCC = date("d/m/Y", strtotime($dataInclusao));
                $qtdTemp = ($tipoControle[0]->farpnotsal != 1) ? $scc->acoeitqtat : $scc->vcoeitvuti;
                $this->getTemplate()->QTDUTILIZADA = converte_valor_licitacao($qtdTemp);
                $qtd_utilizada += $qtdTemp;
                $this->getTemplate()->block('bloco_resultado_tr_carona_externa');
            }
            
            $this->getTemplate()->QTD_UTILIZADA = $textoTotal . converte_valor_licitacao($qtd_utilizada);
            $this->getTemplate()->block('bloco_resultado_carona_externa');
        }

        if($plotouOrgao){
            $this->getAdaptacao()->quantidadeUtilizadaTotalParticipante += $qtd_utilizada;
            $this->getTemplate()->block('bloco_orgao_carona_externa');
        }
    }

    /**
     *
     * @param $sccParticipante
     * @param $quantidades
     *
     * @return array
     */
    private function itensInclusaoDiretaCarona($itensAtaCarona, $dto, $orgaos)
    {
        if(!empty($itensAtaCarona)) {
            foreach ($itensAtaCarona as $key => $item) {
                $orgaos[$item->corglicodi]['inclusao_direta'][] = array(
                    'aitcrpqtut' => $item->aitcrpqtut,
                    'vitcrpvatu' => $item->vitcrpvatu,
                    'vitcrpvuti' => $item->vitcrpvuti,
                );
            }
        }

        return $orgaos;
    }

    private function itensScc($sccParticipante, $dto, $orgaos)
    {
        if(!empty($sccParticipante)){
            foreach ($sccParticipante as $key => $item) {
                $qtdSolicitadaAta = 0;

                if($dto['tipoAta'] == 'I') {
                    $sccQuantidade = $this->getAdaptacao()
                        ->getNegocio()
                        ->consultarQuantidadeSolicitadoDoProcesso($item->clicpoproc, $item->alicpoanop, $item->cgrempcodi, $item->ccomlicodi, $item->orgao_gestor, $item->orgao_agrupamento, $dto['tipoItem'], $dto['item']);
                } else {
                    $sccQuantidade = $this->getAdaptacao()
                        ->getNegocio()
                        ->consultarQuantidadeSolicitadoDoProcessoExterno($item->carpnosequ, $dto['item']);
                }

                foreach ($sccQuantidade as $quantidade) {
                    $qtdSolicitadaAta += $quantidade->aitelpqtso;
                }

                $orgaos[$item->corglicodi]['item'][$key]['data']            = date("d/m/Y", strtotime($item->tsolcodata));
                $orgaos[$item->corglicodi]['item'][$key]['qtd_utilizada']   = $item->aitescqtso;
                $orgaos[$item->corglicodi]['item'][$key]['qtd_solicitada']  = $qtdSolicitadaAta;
                $orgaos[$item->corglicodi]['item'][$key]['numero']          = getNumeroSolicitacaoCompra(ClaDatabasePostgresql::getConexao(), $item->csolcosequ);
                $orgaos[$item->corglicodi]['item'][$key]['id']              = $item->csolcosequ;
                $orgaos[$item->corglicodi]['item'][$key]['vitescunit']      = $item->vitescunit;
            }
        }

        return $orgaos;
    }

    /**
     * Plotar tabela com os dados dos orgaõs
     *
     * @param $data
     *
     * @return void
     */
    private function plotarBlocoOrgaos($data, $carpnosequ) {
        if(!empty($data)) {
            // Tipo de controle
            $tipoControle = $this->getAdaptacao()->getNegocio()->consultarTipoControle($carpnosequ);
            $textoScc     = ($tipoControle[0]->farpnotsal != 1) ? 'QUANTIDADE UTILIZADA' : 'TOTAL VALOR UTILIZADO';
            $textoInc     = ($tipoControle[0]->farpnotsal != 1) ? 'QUANTIDADE UTILIZADA - INCLUSÃO DIRETA:' : 'VALOR UTILIZADO INCLUSÃO DIRETA: ';
            $textoTotal   = ($tipoControle[0]->farpnotsal != 1) ? 'TOTAL QUANTIDADE UTILIZADA: ' : 'TOTAL VALOR UTILIZADO: ';

            foreach($data as $key => $value) {
                $qtdTotal           = 0;
                $qtd_utilizada      = 0;
                $saldo_disponivel   = 0;
                $exibir_tr = 'display:none';
                
                $this->getTemplate()->ORGAO = $value['orgao'];
                $this->getTemplate()->ORGAOURL = $_GET['orgao'];
                $this->getTemplate()->SEQITEM = $_GET['seqItem'];
                $this->getTemplate()->ATATIPO = $_GET['tipo'];

                if(!empty($value['item'])) {
                    $exibir_tr = '';
                    foreach($value['item'] as $key_ => $value_) {                        
                        $tmpSumQtdUtilizada = ($tipoControle[0]->farpnotsal != 1) ? $value_['qtd_utilizada'] : $value_['vitescunit'];
                        $qtd_utilizada += $tmpSumQtdUtilizada;
                        $this->getTemplate()->DATASCC = $value_['data'];
                        $this->getTemplate()->NUMEROSCC = $value_['numero'];
                        $this->getTemplate()->IDSOLIC = $value_['id'];
                        $this->getTemplate()->QTDUTILIZADA = converte_valor_licitacao($tmpSumQtdUtilizada);
                        $this->getTemplate()->TEXTO_SCC = $textoScc;
                        $this->getTemplate()->block('bloco_resultado_orgao');
                    }
                }

                $this->getTemplate()->EXIBIR_SCC = $exibir_tr;
                $this->getTemplate()->block('bloco_tr_resultado_orgao');
                
                // Inclusão direta
                if(!empty($value['inclusao_direta'])) {
                    foreach($value['inclusao_direta'] as $key_ => $value_) {
                        $valorInclusaoDireta = ($tipoControle[0]->farpnotsal != 1) ? $value_['aitcrpqtut'] : $value_['vitcrpvuti'];
                        $this->getTemplate()->RESULTADO_QTD_UTILIZADA = $textoInc . converte_valor_licitacao($valorInclusaoDireta);
                        $qtd_utilizada += $valorInclusaoDireta;
                        $saldo_disponivel += $value_['apiarpqtut'];
                        $this->getTemplate()->block('bloco_resultado_qtd_utilizado');
                    }
                }
                $this->getTemplate()->VALOR_QUANTIDADE_UTILIZADA = $textoTotal . converte_valor_licitacao($qtd_utilizada);

                $this->getTemplate()->block('bloco_orgao_interna');
            }
        }
    }


    /**
     * [__construct description]
     */
    public function __construct()
    {
        $this->setAdaptacao(new RegistroPreco_Adaptacao_ConsDetalheHistoricoParticipanteExtratoAta());
        
        if($_GET['window']==1 || $_POST['window']==1){
            $this->setTemplate(new TemplateNovaJanela("templates/ConsDetalheHistoricoCaronaAtaExtratoAta.html", "Registro de Preço > Extrato Atas"));
            $this->getTemplate()->BOTAO_VOLTAR  = "javascript:window.close()";
        }else{
            $this->setTemplate(new TemplatePaginaPadrao("templates/ConsDetalheHistoricoCaronaAtaExtratoAta.html", "Registro de Preço > Extrato Atas"));
            $this->getTemplate()->BOTAO_VOLTAR =  "javascript:enviar('Voltar')";
        }

        $this->getTemplate()->NOME_PROGRAMA = "ConsDetalheHistoricoCaronaAtaExtratoAta";
        $this->getTemplate()->TITULO_SUPERIOR = 'EXTRATO ATAS - HISTÓRICO CARONA';
    }

    /**
     */
    public function proccessPrincipal()
    {
        $arrayDto = array(
            'ata' => isset($_REQUEST['ata']) ? filter_var($_REQUEST['ata'], FILTER_SANITIZE_NUMBER_INT) : null,
            'orgao' => isset($_REQUEST['orgao']) ? filter_var($_REQUEST['orgao'], FILTER_SANITIZE_NUMBER_INT) : null,
            'item' => isset($_REQUEST['item']) ? filter_var($_REQUEST['item'], FILTER_SANITIZE_NUMBER_INT) : null,
            'tipoItem' => isset($_REQUEST['tipoItem']) ? filter_var($_REQUEST['tipoItem'], FILTER_SANITIZE_STRING) : null,
            'tipoAta' => isset($_REQUEST['tipo']) ? filter_var($_REQUEST['tipo'], FILTER_SANITIZE_STRING) : null,
            'seqItem' => isset($_REQUEST['seqItem']) ? filter_var($_REQUEST['seqItem'], FILTER_SANITIZE_NUMBER_INT) : null
        );
        $dto                 = new ArrayObject($arrayDto);
        $orgaos              = array();
        $_SESSION['tp_ata']  = $_REQUEST['tipo'];
        $_SESSION['seqItem'] = $_REQUEST['seqItem'];

        $this->getTemplate()->TPATA = $_REQUEST['tipo'];

        $extratosAta = $this->getAdaptacao()->configurarValoresAta($dto);
        //$valorAtaAnterior = $this->getAdaptacao()->consultarUltilizadoAtaAnterior($dto);
        //$valorSccAtaAnterior = $this->getAdaptacao()->consultarUtilizadoSccAtaAnterior($dto);

        if (! empty($extratosAta)) {
            $this->plotarBlocoResultadoAta($extratosAta);
        }
        //$quantidadeOrgao = $this->getAdaptacao()->configurarValoresScc($dto);


        $sccGestor = $this->getAdaptacao()
            ->getNegocio()
            ->consultarSccDoProcesso($dto['tipoAta'], $dto['ata'], $dto['item'], $dto['tipoItem'], '=', $dto['seqItem']);

        $sccParticipante = $this->getAdaptacao()->getNegocio()->consultarSccDoProcesso(
            $dto['tipoAta'],
            $dto['ata'],
            $dto['item'],
            $dto['tipoItem'],
            '!=',
            $dto['seqItem']
        );

        $itensAtaCarona = $this->getAdaptacao()->getNegocio()->consultarItensAtaCarona(
            $dto['ata'],
            $dto['item'],
            $dto['tipoItem'],
            $dto['seqItem']
        );

        // Verificar os orgãos de carona interna
        if(!empty($sccParticipante)) {
            foreach($sccParticipante as $key => $value) {
                $orgaos[$value->orgao_agrupamento]['orgao'] = $value->eorglidesc;
            }
        }

        if(!empty($itensAtaCarona)) {
            foreach($itensAtaCarona as $key => $value) {
                $orgaos[$value->corglicodi]['orgao'] = $value->eorglidesc;
            }
        }

        $orgaos = $this->itensInclusaoDiretaCarona($itensAtaCarona, $dto, $orgaos);
        $orgaos = $this->itensScc($sccParticipante, $dto, $orgaos);

        $this->plotarBlocoOrgaos($orgaos, $dto['ata']);

        //$this->plotarBlocoSCCParticipante($sccParticipante, $dto);
        //$this->plotarBlocoInclusaoDiretaCarona($itensAtaCarona, $dto);


        $qtdUtilizada = $this->getAdaptacao()->quantidadeUtilizadaTotalParticipante + $this->getAdaptacao()->quantidadeUtilizadaTotalGestor;

        //$this->getTemplate()->SALDOGESTORPARTICIPANTE = converte_valor_licitacao($saldoGeralCaronaAta);
        $this->getTemplate()->FLAG_EXIBIR = '';
        if($_REQUEST['tipo'] == "I"){
            $orgaos_externo = array();
            $caronaOrgaoGestor = $this->getAdaptacao()->getNegocio()->consultarCaronaOrgaoExterno(
                $dto['tipoAta'],
                $dto['ata'],
                $dto['item'],
                $dto['tipoItem'],
                '!=',
                $dto['orgao'],
                $dto['seqItem']
            );

            if(!empty($caronaOrgaoGestor)) {
                foreach($caronaOrgaoGestor as $key => $value) {
                    $orgaos_externo[$value->ecaroeorgg] = array();
                }
            }
        
            $this->plotarBlocoCaronaExterna($caronaOrgaoGestor, $dto, $orgaos_externo);
        }else{
            $this->getTemplate()->FLAG_EXIBIR = 'style="display: none !important;"';
        }

        $this->getTemplate()->ATA = $dto['ata'];
        $this->getTemplate()->TIPO = $dto['tipoItem'];
        $this->getTemplate()->ORGAO = $dto['orgao'];
        $this->getTemplate()->ITEM = $dto['item'];
        $this->getTemplate()->TIPOITEM = $dto['tipoItem'];
    }

    /**
     */
    public function processVoltar()
    {
        $uri = "ConsAtaRegistroPrecoExtratoAtaDetalhe.php?carpnosequ=".$_REQUEST['ata'];
        header('Location: ' . $uri);
        exit();
    }

    /**
     */
    public function imprimir()
    {
        $ata        = $_REQUEST['ata'];
        $tipo       = $_SESSION['tp_ata'];
        $orgao      = $_REQUEST['orgao'];
        $item       = $_REQUEST['item'];
        $seqItem    = $_SESSION['seqItem'];
        $tipoItem   = $_REQUEST['tipoItem'];

        $uri = "PdfVisualizarExtratoCaronaAtaParticipante.php?ata=$ata&tipo=$tipo&orgao=$orgao&item=$item&tipoItem=$tipoItem&seqItem=$seqItem";
        header('Location: ' . $uri);
        exit();
    }
}
/**
 * [$app description]
 *
 * @var Negocio
 */
$app = new RegistroPreco_UI_ConsDetalheHistoricoParticipanteExtratoAta();

$acao = filter_input(INPUT_POST, 'Botao', FILTER_SANITIZE_STRING);

switch ($acao) {
    case 'Voltar':
        $app->processVoltar();
        break;
    case 'Pesquisar':
        $app->consultarExtratoAta();
        $app->proccessPrincipal();
        break;
    case 'Imprimir':
        $app->imprimir();
    default:
        $app->proccessPrincipal();
        break;
}

echo $app->getTemplate()->show();

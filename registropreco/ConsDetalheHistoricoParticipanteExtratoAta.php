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
 * 
 * -------------------------------------------------------------------------
 * Alterado: Caio Coutinho
 * Data:     18/06/2018
 * Objetivo: Ajustar btn voltar em compras para o histórico em extrato (Registro de preço)
 * -------------------------------------------------------------------------
 * Alterado: Caio Coutinho
 * Data:     26/06/2018
 * Objetivo: Tarefa Redmine #194536
 * -------------------------------------------------------------------------
 * Alterado: Caio Coutinho
 * Data:     28/06/2018
 * Objetivo: Tarefa Redmine #198000
 * -------------------------------------------------------------------------
 * Alterado: Caio Coutinho
 * Data:     04/07/2018
 * Objetivo: Tarefa Redmine #198149
 * -------------------------------------------------------------------------
 * Alterado: Pitang Agile TI - Caio Coutinho
 * Data:     05/07/2018
 * Objtivo:  Tarefa Redine #194536
 * -------------------------------------------------------------------------
 * Alterado: Pitang Agile TI - Caio Coutinho
 * Data:     11/07/2018
 * Objtivo:  Tarefa Redine #198981
 * -------------------------------------------------------------------------
 * Alterado: Caio Coutinho - Pitang Agile TI
 * Data:     22/08/2018
 * Objetivo: Tarefa Redmine 201674
 * -------------------------------------------------------------------------
 * Alterado: Caio Coutinho - Pitang Agile TI
 * Data:     24/08/2018
 * Objetivo: Tarefa Redmine 201047
 * -------------------------------------------------------------------------
 * Alterado: Lucas Baracho
 * Data:     08/05/2019
 * Objetivo: Tarefa Redmine 216344
 * -------------------------------------------------------------------------
 * Alterado: Lucas Baracho
 * Data:     13/05/2019
 * Objetivo: Tarefa Redmine 216440
 * -------------------------------------------------------------------------
 */

 // 220038--

if (! @require_once dirname(__FILE__) . "/../bootstrap.php") {
    throw new Exception("Error Processing Request - Bootstrap", 1);
}

/**
 * A camada de Dados conterá o código que irá realizar todo o acesso aos dados.
 *
 * @author jfsi
 *
 */
class RegistroPreco_Dados_ConsDetalheHistoricoParticipanteExtratoAta extends Dados_Abstrata {
    /**
     *
     * @param unknown $tipoAta
     * @param unknown $ata
     * @param unknown $material
     * @param unknown $servico
     */
    public function sqlConsultarAta($tipoAta, $ata, $material, $servico, $seqItem) {
        $sql  = "SELECT  ATA.*, IARPN.AITARPORDE, IARPN.CMATEPSEQU, IARPN.CSERVPSEQU, IARPN.EITARPDESCMAT, ";
        $sql .= "       IARPN.EITARPDESCSE, IARPN.AITARPQTOR, IARPN.AITARPQTAT, IARPN.VITARPVATU, IARPN.VITARPVORI, ";
        $sql .= "       IARPN.CITARPNUML, PIARP.VPIARPVATU, PIARP.VPIARPVUTI, ";
        $sql .= "       (CASE   WHEN IARPN.CMATEPSEQU IS NOT NULL ";
        $sql .= "                   THEN (SELECT MP.EMATEPDESC FROM SFPC.TBMATERIALPORTAL MP ";
        $sql .= "                         WHERE MP.CMATEPSEQU = IARPN.CMATEPSEQU) ";
        $sql .= "               WHEN IARPN.CSERVPSEQU IS NOT NULL ";
        $sql .= "                   THEN (SELECT SP.ESERVPDESC FROM SFPC.TBSERVICOPORTAL SP ";
        $sql .= "                         WHERE SP.CSERVPSEQU = IARPN.CSERVPSEQU) ";
        $sql .= "       END) AS DESCRICAOITEM ";
        $sql .= "FROM    SFPC.TBATAREGISTROPRECOINTERNA ATA ";
        $sql .= "       INNER JOIN SFPC.TBITEMATAREGISTROPRECONOVA IARPN ON IARPN.CARPNOSEQU = ATA.CARPNOSEQU ";
        $sql .= "       LEFT JOIN SFPC.TBPARTICIPANTEITEMATARP PIARP ON PIARP.CARPNOSEQU = IARPN.CARPNOSEQU ";
        $sql .= "WHERE   ATA.CARPNOSEQU = " . $ata;

            if ($material != null) {
                $sql .= " AND IARPN.CMATEPSEQU = " . $material;
            } else {
                $sql .= " AND IARPN.CSERVPSEQU = " . $servico;
            }

        $sql .= "       AND IARPN.CITARPSEQU = " . $seqItem;

        return $sql;
    }

    public function sqlConsultarUtilizadoAtaAnterior($ata) {
        $sql  = "SELECT ISC.CARPNOSEQU, SUM(ISC.AITESCQTSO) AS SOMA ";
        $sql .= "FROM   SFPC.TBITEMSOLICITACAOCOMPRA ISC ";
        $sql .= "       INNER JOIN SFPC.TBSOLICITACAOCOMPRA SC ON SC.CSOLCOSEQU = ISC.CSOLCOSEQU ";
        $sql .= "                                              AND SC.CARPNOSEQU = ISC.CARPNOSEQU ";
        $sql .= "                                              AND SC.FSOLCORPCP = 'P' ";   
        $sql .= "       INNER JOIN SFPC.TBATAREGISTROPRECOINTERNA ARPI ON SC.CARPNOSEQU = ARPI.CARPNOSEQU ";
        $sql .= "                                                      AND ISC.CARPNOSEQU = ARPI.CARPNOSEQU ";
        $sql .= "WHERE  ARPI.CARPNOSEQ1 = " . $ata['ata'] . " ";
        $sql .= "       AND SC.CORGLICODI IN (" . implode(",", $ata['orgao']) . ") ";
        $sql .= "GROUP BY ISC.CARPNOSEQU ";

        return $sql;
    }

    public function sqlConsultarUtilizadoSccAtaAnterior($ata) {
        $sql  = "SELECT SC.CARPNOSEQU, SUM(ISC.AITESCQTSO) AS SOMA ";
        $sql .= "FROM   SFPC.TBITEMSOLICITACAOCOMPRA ISC ";
        $sql .= "       INNER JOIN SFPC.TBSOLICITACAOCOMPRA SC ON SC.CSOLCOSEQU = ISC.CSOLCOSEQU ";
        $sql .= "                                              AND SC.CARPNOSEQU = ISC.CARPNOSEQU ";
        $sql .= "       INNER JOIN SFPC.TBATAREGISTROPRECOINTERNA ARPI ON SC.CARPNOSEQU = ARPI.CARPNOSEQU ";
        $sql .= "WHERE  ARPI.CARPNOSEQ1 = " . $ata['ata'] . " ";
        $sql .= "       AND ISC.CITARPSEQU = " . $ata['seqItem'] . " ";
        $sql .= "       AND SC.FSOLCORPCP = 'P' ";
        $sql .= "GROUP BY SC.CARPNOSEQU ";

        return $sql;
    }

    private function sqlConsultarCentroDeCustoUsuario($cgrempcodi, $cusupocodi, $corglicodi) {
        $sql  = "SELECT CCP.CCENPOCORG, CCP.CCENPOUNID, CCP.CORGLICODI ";
        $sql .= "FROM   SFPC.TBCENTROCUSTOPORTAL CCP ";
        $sql .= "WHERE  1=1 ";

            if ($corglicodi != null || $corglicodi != "") {
                $sql .= " AND CCP.CORGLICODI = %d";
            }

        return sprintf($sql, $corglicodi);
    }


    public function consultarDCentroDeCustoUsuario($cgrempcodi, $cusupocodi, $corglicodi) {
        $db = Conexao();

        $sql = $this->sqlConsultarCentroDeCustoUsuario($cgrempcodi, $cusupocodi, $corglicodi);

        $res = executarSQL($db, $sql);

        $itens = array();
        $item  = null;

        while ($res->fetchInto($item, DB_FETCHMODE_OBJECT)) {
            $itens[] = $item;
        }

        $this->hasError($res);

        $db->disconnect();

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
    public function sqlConsultarSCCDoProcesso($ata, $codigoItem, $tipoItem, $comparacao, $seqItem) {
        $sql  = "SELECT DISTINCT ATAI.CARPNOSEQU, SOL.CSOLCOSEQU, ITEMS.AITESCQTSO, SOL.TSOLCODATA, OL.EORGLIDESC, ";
        $sql .= "       SOL.CLICPOPROC, SOL.ALICPOANOP, SOL.CGREMPCODI, SOL.CCOMLICODI, ";
        $sql .= "       SOL.CORGLICODI, SOL.CORGLICODI AS ORGAO_AGRUPAMENTO, SOL.CORGLICOD1 AS ORGAO_GESTOR, ";
        $sql .= "       ITEMS.VITESCUNIT ";
        $sql .= "FROM   SFPC.TBATAREGISTROPRECOINTERNA ATAI, ";
        $sql .= "       SFPC.TBITEMATAREGISTROPRECONOVA ITEMA, ";
        $sql .= "       SFPC.TBSOLICITACAOCOMPRA SOL, ";
        $sql .= "       SFPC.TBITEMSOLICITACAOCOMPRA ITEMS, ";
        $sql .= "       SFPC.TBORGAOLICITANTE OL ";
        $sql .= "WHERE  ATAI.CARPNOSEQU      = $ata ";
        $sql .= "       AND ATAI.CARPNOSEQU  = SOL.CARPNOSEQU ";
        $sql .= "       AND ITEMS.CITARPSEQU = $seqItem ";
        $sql .= "       AND SOL.CSOLCOSEQU   = ITEMS.CSOLCOSEQU ";
        $sql .= "       AND SOL.CTPCOMCODI   = 5 ";
        $sql .= "       AND SOL.FSOLCORPCP   = 'P' ";
        $sql .= "       AND OL.CORGLICODI    = SOL.CORGLICODI ";
        $sql .= "       AND ITEMS.CARPNOSEQU = ATAI.CARPNOSEQU ";
        $sql .= "       AND SOL.CSITSOCODI IN (3, 4, 5) ";

            if ($tipoItem == 'M') {
                $sql .= " AND ITEMS.CMATEPSEQU = " . $codigoItem;
            } else {
                $sql .= " AND ITEMS.CSERVPSEQU = " . $codigoItem;
            }

        return $sql;
    }

    public function sqlConsultarQtantidadeSolicitadaDoProcesso($clicpoproc, $alicpoanop, $cgrempcodi, $ccomlicodi, $orgaoGestor, $orgaoAgrupamento, $tipoItem, $codigoItem) {
        $sql  = "SELECT SOL.CSOLCOSEQU, SOL.ASOLCOANOS, ITEL.CITELPSEQU, ITEL.CMATEPSEQU, ITEL.CSERVPSEQU, ";
        $sql .= "       ITEL.AITELPQTSO ";
        $sql .= "FROM   SFPC.TBSOLICITACAOLICITACAOPORTAL SOLIC, ";
        $sql .= "       SFPC.TBSOLICITACAOCOMPRA SOL, ";
        $sql .= "       SFPC.TBITEMLICITACAOPORTAL ITEL, "; 
        $sql .= "       SFPC.TBITEMSOLICITACAOCOMPRA ITES ";
        $sql .= "WHERE  SOLIC.CLICPOPROC     = $clicpoproc ";
        $sql .= "       AND SOLIC.ALICPOANOP = $alicpoanop ";
        $sql .= "       AND SOLIC.CGREMPCODI = $cgrempcodi ";
        $sql .= "       AND SOLIC.CCOMLICODI = $ccomlicodi ";
        $sql .= "       AND SOLIC.CORGLICODI = $orgaoGestor ";
        $sql .= "       AND SOLIC.CLICPOPROC = ITEL.CLICPOPROC ";
        $sql .= "       AND SOLIC.ALICPOANOP = ITEL.ALICPOANOP ";
        $sql .= "       AND SOLIC.CGREMPCODI = ITEL.CGREMPCODI "; 
        $sql .= "       AND SOLIC.CCOMLICODI = ITEL.CCOMLICODI ";
        $sql .= "       AND SOLIC.CORGLICODI = ITEL.CORGLICODI ";
        $sql .= "       AND SOL.CORGLICODI   = $orgaoAgrupamento ";
        $sql .= "       AND SOL.CSOLCOSEQU   = SOLIC.CSOLCOSEQU ";
        $sql .= "       AND SOL.CSOLCOSEQU   = ITES.CSOLCOSEQU ";
        $sql .= "       AND ITEL.CITELPSEQU  = ITES.CITESCSEQU ";

            if ($tipoItem == 'M') {
                $sql .= " AND ITEL.CMATEPSEQU = " . $codigoItem;
            } else {
                $sql .= " AND ITEL.CSERVPSEQU = " . $codigoItem;
            }

        return $sql;
    }

    public function sqlConsultarResultadoDosItens($carpnosequ, $itemCodigo, $tipoItem, $seqItem) {
        $sql  = "SELECT DISTINCT ATAI.CARPNOSEQU, SOL.CSOLCOSEQU, ITEMS.AITESCQTSO, SOL.TSOLCODATA, OL.EORGLIDESC, ";
        $sql .= "       SOL.CLICPOPROC, SOL.ALICPOANOP, SOL.CGREMPCODI, SOL.CCOMLICODI, ";
        $sql .= "       SOL.CORGLICODI AS ORGAO_AGRUPAMENTO, SOL.CORGLICOD1 AS ORGAO_GESTOR, ITEMS.VITESCUNIT ";
        $sql .= "FROM   SFPC.TBATAREGISTROPRECOINTERNA ATAI, ";
        $sql .= "       SFPC.TBITEMATAREGISTROPRECONOVA ITEMA, ";
        $sql .= "       SFPC.TBSOLICITACAOCOMPRA SOL, ";
        $sql .= "       SFPC.TBITEMSOLICITACAOCOMPRA ITEMS, ";
        $sql .= "       SFPC.TBORGAOLICITANTE OL ";
        $sql .= "WHERE  ATAI.CARPNOSEQU      = SOL.CARPNOSEQU ";
        $sql .= "       AND SOL.CSOLCOSEQU   = ITEMS.CSOLCOSEQU ";
        $sql .= "       AND SOL.CTPCOMCODI   = 5 ";
        $sql .= "       AND SOL.FSOLCORPCP   = 'P' ";
        $sql .= "       AND SOL.CSITSOCODI IN (3, 4, 5) ";
        $sql .= "       AND OL.CORGLICODI    = SOL.CORGLICODI ";
        $sql .= "       AND ITEMS.CARPNOSEQU = ATAI.CARPNOSEQU ";
        $sql .= "       AND ATAI.CARPNOSEQU  = ". $carpnosequ;

            if ($tipoItem == 'M') {
                $sql .= " AND ITEMS.CMATEPSEQU = " . $itemCodigo;
            } else {
                $sql .= " AND ITEMS.CSERVPSEQU = " . $itemCodigo;
            }

        $sql .= "ORDER BY SOL.CSOLCOSEQU DESC ";


        return $sql;
    }

    function sqlConsultarItensInclusaoDireta($carpnosequ, $itemCodigo, $tipoItem, $seqItem) {
        $sql  = "SELECT * ";
        $sql .= "FROM   SFPC.TBPARTICIPANTEATARP ARPI ";
        $sql .= "       INNER JOIN SFPC.TBPARTICIPANTEITEMATARP IPA ON IPA.CARPNOSEQU = ARPI.CARPNOSEQU AND IPA.CORGLICODI = ARPI.CORGLICODI ";
        $sql .= "       INNER JOIN SFPC.TBITEMATAREGISTROPRECONOVA I ON I.CARPNOSEQU = ARPI.CARPNOSEQU AND I.CITARPSEQU = IPA.CITARPSEQU ";
        $sql .= "       LEFT OUTER JOIN SFPC.TBMATERIALPORTAL M ON I.CMATEPSEQU = M.CMATEPSEQU ";
        $sql .= "       LEFT OUTER JOIN SFPC.TBUNIDADEDEMEDIDA UMP ON UMP.CUNIDMCODI = M.CUNIDMCODI ";
        $sql .= "       LEFT OUTER JOIN SFPC.TBSERVICOPORTAL S ON I.CSERVPSEQU = S.CSERVPSEQU ";
        $sql .= "       INNER JOIN SFPC.TBORGAOLICITANTE O ON O.CORGLICODI = ARPI.CORGLICODI ";
        $sql .= "WHERE  I.CARPNOSEQU = %d ";
        $sql .= "       AND I.CITARPSEQU = " . $seqItem;

            if ($tipoItem == 'M') {
                $sql .= " AND I.CMATEPSEQU = " . $itemCodigo;
            } else {
                $sql .= " AND I.CSERVPSEQU = " . $itemCodigo;
            }

        $sql .= " ORDER BY IPA.CORGLICODI ASC ";

        return sprintf($sql, $carpnosequ);
    }

    public function sqlConsultarItensAtaParticipante($carpnosequ, $itemCodigo, $tipoItem, $seqItem) {
        $sql  = "SELECT *, IPA.*, I.*, M.*, UMP.*, ";
        $sql .= "       S.*, SC.CORGLICODI AS orgao_agrupamento, SC.CORGLICODI AS orgao_gestor ";
        $sql .= "FROM   SFPC.TBPARTICIPANTEATARP ARPI ";
        $sql .= "       INNER JOIN SFPC.TBPARTICIPANTEITEMATARP IPA ON IPA.CARPNOSEQU = ARPI.CARPNOSEQU AND IPA.CORGLICODI = ARPI.CORGLICODI ";
        $sql .= "       INNER JOIN SFPC.TBITEMATAREGISTROPRECONOVA I ON I.CARPNOSEQU = ARPI.CARPNOSEQU AND I.CITARPSEQU = IPA.CITARPSEQU ";
        $sql .= "       LEFT OUTER JOIN SFPC.TBMATERIALPORTAL M ON I.CMATEPSEQU = M.CMATEPSEQU  ";
        $sql .= "       LEFT OUTER JOIN SFPC.TBUNIDADEDEMEDIDA UMP ON UMP.CUNIDMCODI = M.CUNIDMCODI  ";
        $sql .= "       LEFT OUTER JOIN SFPC.TBSERVICOPORTAL S ON I.CSERVPSEQU = S.CSERVPSEQU ";
        $sql .= "       INNER JOIN SFPC.TBORGAOLICITANTE O ON O.CORGLICODI = ARPI.CORGLICODI ";
        $sql .= "       LEFT JOIN SFPC.TBATAREGISTROPRECOINTERNA SC ON SC.CARPNOSEQU = ARPI.CARPNOSEQU AND SC.CORGLICODI = ARPI.CORGLICODI ";
        $sql .= "WHERE  I.CARPNOSEQU = %d ";
        $sql .= "       AND I.CITARPSEQU = " . $seqItem;

            if ($tipoItem == 'M') {
                $sql .= " AND I.CMATEPSEQU = " . $itemCodigo;
            } else {
                $sql .= " AND I.CSERVPSEQU = " . $itemCodigo;
            }

        $sql .= " ORDER BY IPA.CORGLICODI ASC ";

        return sprintf($sql, $carpnosequ);

    }

    /**
     *
     * @param unknown $numeroAta
     * @param unknown $item
     * @param unknown $tipoItem
     */
    public function sqlConsultarQuantidadeParticipanteItem($numeroAta, $item, $tipoItem) {
        $sql  = "SELECT DISTINCT ATAI.CARPNOSEQU, SOL.CSOLCOSEQU, ITEMS.AITESCQTSO, ";
        $sql .= "       SOL.CORGLICODI as orgao_agrupamento, SOL.CORGLICOD1 as orgao_gestor ";
        $sql .= "FROM   SFPC.TBATAREGISTROPRECOINTERNA ATAI, ";
        $sql .= "       SFPC.TBITEMATAREGISTROPRECONOVA ITEMA, ";
        $sql .= "       SFPC.TBSOLICITACAOCOMPRA SOL, ";
        $sql .= "       SFPC.TBITEMSOLICITACAOCOMPRA ITEMS ";
        $sql .= "WHERE  1 = 1 ";
        $sql .= "       AND ATAI.CARPNOSEQU = $numeroAta ";
        $sql .= "       AND ATAI.CARPNOSEQU = SOL.CARPNOSEQU ";
        $sql .= "       AND SOL.CSOLCOSEQU  = ITEMS.CSOLCOSEQU ";
        $sql .= "       AND SOL.CTPCOMCODI  = 5 ";
        $sql .= "       AND SOL.FSOLCORPCP  = 'P' ";

            if ($tipoItem == 'M') {
                $sql .= " and ITEMS.cmatepsequ = " . $item;
            } else {
                $sql .= " and ITEMS.cservpsequ = " . $item;
            }

        $sql .= "       AND ITEMS.CARPNOSEQU = ATAI.CARPNOSEQU ";

        return $sql;
    }

    public function sqlConsultarTipoControle($ata) {
        $sql  = "SELECT ARPN.FARPNOTSAL ";
        $sql .= "FROM   SFPC.TBATAREGISTROPRECONOVA ARPN ";
        $sql .= "WHERE  ARPN.CARPNOSEQU = %d ";

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
class RegistroPreco_Negocio_ConsDetalheHistoricoParticipanteExtratoAta extends Negocio_Abstrata {
    public function __construct() {
        $this->setDados(new RegistroPreco_Dados_ConsDetalheHistoricoParticipanteExtratoAta());
    }

    public function consultarOrgaosParticipantesAtas() {
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
    public function consultarQuantidadeParticipanteItem($ata, $itemAta, $tipoAta) {
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
    public function consultarExtratoAta($tipoAta, $ata, $material, $servico, $seqItem) {
        $sql = $this->getDados()->sqlConsultarAta($tipoAta, $ata, $material, $servico, $seqItem);

        $resultado = ClaDatabasePostgresql::executarSQL($sql);

        ClaDatabasePostgresql::hasError($resultado);

        return $resultado;
    }

    public function consultarUltilizadoAtaAnterior($ata) {
        $sql = $this->getDados()->sqlConsultarUtilizadoAtaAnterior($ata);
        
        $resultado = ClaDatabasePostgresql::executarSQL($sql);        
        
        ClaDatabasePostgresql::hasError($resultado);

        return $resultado;
    }

    public function consultarUtilizadoSccAtaAnterior($ata) {
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
    public function consultarSccDoProcesso($ata, $item, $tipoItem, $comparacao, $seqItem)
    {
        $sql = $this->getDados()->sqlConsultarSCCDoProcesso($ata, $item, $tipoItem, $comparacao, $seqItem);
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


    public function consultarQuantidadeSolicitadoDoProcesso($clicpoproc, $alicpoanop, $cgrempcodi, $ccomlicodi, $orgaoGestor, $orgaoAgrupamento, $tipoItem, $codigoItem)
    {
        $sql = $this->getDados()->sqlConsultarQtantidadeSolicitadaDoProcesso($clicpoproc, $alicpoanop, $cgrempcodi, $ccomlicodi, $orgaoGestor, $orgaoAgrupamento, $tipoItem, $codigoItem);
        $resultado = ClaDatabasePostgresql::executarSQL($sql);

        ClaDatabasePostgresql::hasError($resultado);

        return $resultado;
    }


    public function consultarItensAtaParticipante($carpnosequ, $itemCodigo, $tipoItem, $seqItem)
    {
        $db = Conexao();
        $sql = $this->getDados()->sqlConsultarItensAtaParticipante($carpnosequ, $itemCodigo, $tipoItem, $seqItem);
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

    public function consultarResultadoDosItens($carpnosequ, $itemCodigo, $tipoItem, $seqItem)
    {
        $sql = $this->getDados()->sqlConsultarResultadoDosItens($carpnosequ, $itemCodigo, $tipoItem, $seqItem);
        $resultado = ClaDatabasePostgresql::executarSQL($sql);

        ClaDatabasePostgresql::hasError($resultado);

        return $resultado;
    }

    // ($carpnosequ, $itemCodigo, $tipoItem, $seqItem)
    public function consultarItensInclusaoDireta($carpnosequ, $itemCodigo, $tipoItem, $seqItem)
    {
        $sql = $this->getDados()->sqlConsultarItensInclusaoDireta($carpnosequ, $itemCodigo, $tipoItem, $seqItem);
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
    public $quantidadeUtilizadaTotalGestor = 0;
    public $quantidadeAta = 0;
    public $valorAta = 0;
    public $quantidadeUtilizada = 0;

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
        return $this->getNegocio()->consultarExtratoAta('I', $dto['ata'], $material, $servico, $dto['seqItem']);
    }
     // consultarUltilizadoAtaAnterior
    
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
    private function plotarBlocoResultadoAta($ata, $ataAnterior, $sccAtaAnterior)
    {
        $ata = $ata[0];
        $consultarfor       = new RegistroPreco_Dados_ConsDetalheHistoricoParticipanteExtratoAta();
        $dtoConsulta        = $consultarfor->consultarDCentroDeCustoUsuario($ata->cgrempcodi, $ata->cusupocodi, $ata->corglicodi);
        $objetoDado         = current($dtoConsulta);
        $numeroAtaFormatado = $objetoDado->ccenpocorg . str_pad($objetoDado->ccenpounid, 2, '0', STR_PAD_LEFT);
        $numeroAtaFormatado .= "." . str_pad($ata->carpincodn, 4, "0", STR_PAD_LEFT) . "/" . $ata->aarpinanon;
        $quantidade         = $ata->aitarpqtat != 0 ? $ata->aitarpqtat : $ata->aitarpqtor;
        $item               = $ata->cmatepsequ != null ? $ata->cmatepsequ : $ata->cservpsequ;
        $descricaoCompleta  = $ata->cmatepsequ != null ? $ata->eitarpdescmat : $ata->eitarpdescse;
        $tipoServico        = $ata->cservpsequ != null ? 'CADUS' : 'CADUM';
        $tipoServicoSigla   = $ata->cservpsequ != null ? 'S' : 'M';

         // Verificar tipo de controle 
         $tipoControle = $this->getAdaptacao()->getNegocio()->consultarTipoControle($ata->carpnosequ); 

        // Consultar itens por inclusão direta
        $qtdUtilizada = 0;
        $qtdUtilizadaAnterior = 0;
        $valorUtilizadoGP = 0;
        $cid = $this->getAdaptacao()->getNegocio()->consultarItensInclusaoDireta($ata->carpnosequ, $item, $tipoServicoSigla, $_GET['seqItem']);

        /*if(!empty($sccAtaAnterior)) {
            $qtdUtilizada += $sccAtaAnterior[0]->soma; 
        }*/

        // Consultar resultados dos itens
        $cri = $this->getAdaptacao()->getNegocio()->consultarResultadoDosItens($ata->carpnosequ, $item, $tipoServicoSigla, $_GET['seqItem']);

        if(!empty($cid)) {
            foreach($cid as $key => $value) {                
                $qtdUtilizada += $value->apiarpqtut;
                $valorUtilizadoGP += $value->vpiarpvuti;
            }
        }

        if(!empty($cri)) {
            foreach($cri as $key => $value) {
                $qtdUtilizada += $value->aitescqtso;
                $valorUtilizadoGP += $value->vitescunit;
            }
        }

        // Verificar ata anterior
        $exibirAtaAnterior = 'display:none';
        unset($_SESSION['VALOR_ATA_ANTERIOR_P']); // remover valor da sessão
        if(!empty($ataAnterior)) {
            $exibirAtaAnterior = '';
            $cid_ = $this->getAdaptacao()->getNegocio()->consultarItensInclusaoDireta($ataAnterior[0]->carpnosequ, $item, $tipoServicoSigla, $_GET['seqItem']);

            // Consultar resultados dos itens
            $cri_ = $this->getAdaptacao()->getNegocio()->consultarResultadoDosItens($ataAnterior[0]->carpnosequ, $item, $tipoServicoSigla, $_GET['seqItem']);

            if(!empty($cid_)) {
                foreach($cid_ as $key => $value) {
                    $qtdUtilizadaAnterior += $value->apiarpqtut;
                }
            }

            if(!empty($cri_)) {
                foreach($cri_ as $key => $value) {
                    $qtdUtilizadaAnterior += $value->aitescqtso;
                }
            }

            $_SESSION['VALOR_ATA_ANTERIOR_P'] = $qtdUtilizadaAnterior;
        }

        $exibir_descricao_completa = '';
        if(empty($descricaoCompleta)) {
            $exibir_descricao_completa = ' display: none;"';
        }
        
        $this->getTemplate()->DISPLAY_QUANTIDADE = '';
        $this->getTemplate()->DISPLAY_VALOR = 'display: none;';
        if($tipoControle[0]->farpnotsal == 1) {
            $this->getTemplate()->DISPLAY_QUANTIDADE = 'display: none;';
            $this->getTemplate()->DISPLAY_VALOR = '';
            $exibirAtaAnterior = 'display: none;';
        }

        $valorTotalAta = (!empty($ata->vitarpvatu) && $ata->vitarpvatu != 0) ? $ata->vitarpvatu : $ata->vitarpvori;

        $this->getTemplate()->EXIBIR_VALOR_ATA_ANTERIOR = $exibirAtaAnterior;
        $this->getTemplate()->NUMEROATA                 = $numeroAtaFormatado;
        $this->getTemplate()->ORDEM                     = $ata->aitarporde;
        $this->getTemplate()->LOTE                      = $ata->citarpnuml;
        $this->getTemplate()->TIPOMATERIAL              = $tipoServico;
        $this->getTemplate()->TIPOMATERIALVALUE         = $item;
        $this->getTemplate()->DESCRICAO                 = $ata->descricaoitem;
        $this->getTemplate()->SHOWDESCRICAOCOMPLETA     = $exibir_descricao_completa;
        $this->getTemplate()->DESCRICAOCOMPLETA         = $descricaoCompleta;    
        $this->getTemplate()->QUANTIDADE                = converte_valor_licitacao($quantidade);   
        $this->getTemplate()->QTDGESTORPARTICIPANTE     = converte_valor_licitacao($qtdUtilizada);
        $this->getTemplate()->QUANTIDADE_ATA_ANTERIOR   = converte_valor_licitacao($qtdUtilizadaAnterior);        
        $this->getTemplate()->VALOR_TOTAL_ATA           = converte_valor_licitacao($valorTotalAta);
        $this->getTemplate()->VALOR_UTILIZADO_GP        = converte_valor_licitacao($valorUtilizadoGP);                
        $this->getAdaptacao()->quantidadeUtilizada      = $qtdUtilizada;
        $this->getAdaptacao()->valorUtilizado           = $valorUtilizadoGP;
        $this->getAdaptacao()->quantidadeAta            = $quantidade;
        $this->getAdaptacao()->valorAta                 = $valorTotalAta;
    }

    /**
     *
     * @param unknown $sccGestor
     * @param unknown $qtdsSolicitada
     */
    private function plotarBlocoSCCGestora($sccGestor, $dto)
    {

        $qtd_utilizada = 0;
        $qtdSolicitadaAta = 0;

        $plotouOrgao = false;
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
                $this->getTemplate()->TIPO_ORGAO = 'GESTOR';
                $this->getTemplate()->IDSOLIC = $scc->csolcosequ;
                $this->getTemplate()->NUMEROSCC = getNumeroSolicitacaoCompra(ClaDatabasePostgresql::getConexao(), $scc->csolcosequ);
                $this->getTemplate()->DATASCC = date("d/m/Y", strtotime($scc->tsolcodata));
                $this->getTemplate()->QTDUTILIZADA = converte_valor_licitacao($scc->aitescqtso);
                $qtd_utilizada = $scc->aitescqtso + $qtd_utilizada;
                $this->getTemplate()->block('bloco_resultado_scc');
            }
        }
        if($plotouOrgao){
            $this->getTemplate()->QTD_UTILIZADA = converte_valor_licitacao($qtd_utilizada);
            $this->getTemplate()->QTD_SOLICITADA = converte_valor_licitacao($qtdSolicitadaAta);
            $this->getAdaptacao()->quantidadeUtilizadaTotalGestor += converte_valor_licitacao($qtd_utilizada);

            $this->getTemplate()->block('bloco_orgao_scc');
        }
    }



    /**
     *
     * @param unknown $sccParticipante
     * @param unknown $quantidades
     */
    private function itensInclusaoDiretaParticipante($itensAtaParticipante, $dto, $orgaos, $tipoConstrole)
    {
        if(!empty($itensAtaParticipante)) {
            foreach ($itensAtaParticipante as $key => $item) {
                $orgaos[$item->corglicodi]['inclusao_direta'][] = array(
                    'apiarpqtut' => ($tipoConstrole == 1) ? $item->vpiarpvuti : $item->apiarpqtut,
                    'apiarpqtat' => ($tipoConstrole == 1) ? $item->vpiarpvatu : $item->apiarpqtat,
                    'vitarpvori' => $item->vitarpvori,
                    'vitarpvatu' => $item->vitarpvatu,
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

                $sccQuantidade = $this->getAdaptacao()
                ->getNegocio()
                ->consultarQuantidadeSolicitadoDoProcesso($item->clicpoproc, $item->alicpoanop, $item->cgrempcodi, $item->ccomlicodi, $item->orgao_gestor, $item->orgao_agrupamento, $dto['tipoItem'], $dto['item']);

                foreach ($sccQuantidade as $quantidade) {
                    $qtdSolicitadaAta += $quantidade->aitelpqtso;
                }

                $orgaos[$item->corglicodi]['item'][$key]['data']           = date("d/m/Y", strtotime($item->tsolcodata));
                $orgaos[$item->corglicodi]['item'][$key]['qtd_utilizada']  = $item->aitescqtso;
                $orgaos[$item->corglicodi]['item'][$key]['qtd_solicitada'] = $qtdSolicitadaAta;
                $orgaos[$item->corglicodi]['item'][$key]['numero']         = getNumeroSolicitacaoCompra(ClaDatabasePostgresql::getConexao(), $item->csolcosequ);
                $orgaos[$item->corglicodi]['item'][$key]['id']             = $item->csolcosequ;
                $orgaos[$item->corglicodi]['item'][$key]['vitescunit']     = $item->vitescunit;
            }
        }

        return $orgaos;
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
                $sccQuantidade = $this->getAdaptacao()
                ->getNegocio()
                ->consultarQuantidadeSolicitadoDoProcesso($scc->clicpoproc, $scc->alicpoanop, $scc->cgrempcodi, $scc->ccomlicodi, $scc->orgao_gestor, $scc->orgao_agrupamento, $dto['tipoItem'], $dto['item']);

                foreach ($sccQuantidade as $quantidade) {
                    $qtdSolicitadaAta += $quantidade->aitelpqtso;
                }

                $this->getTemplate()->ORGAOGESTOR = $scc->eorglidesc;
                $this->getTemplate()->TIPO_ORGAO = 'PARTICIPANTE';
                $this->getTemplate()->IDSOLIC = $scc->csolcosequ;
                $this->getTemplate()->NUMEROSCC = getNumeroSolicitacaoCompra(ClaDatabasePostgresql::getConexao(), $scc->csolcosequ);
                $this->getTemplate()->DATASCC = date("d/m/Y", strtotime($scc->tsolcodata));
                $this->getTemplate()->QTDUTILIZADA = converte_valor_licitacao($scc->aitescqtso);
                $qtd_utilizada += $scc->aitescqtso;
                $this->getTemplate()->block('bloco_resultado_scc');
            }
        }
    }

    /**
     * [__construct description]
     */
    public function __construct()
    {
        if($_GET['window']==1 || $_POST['window']==1){
            $this->setTemplate(new TemplateNovaJanela("templates/ConsDetalheHistoricoParticipanteExtratoAta.html", "Registro de Preço > Extrato Atas"));
            $this->getTemplate()->WINDOW  = "1";
            $this->getTemplate()->BOTAO_VOLTAR =  "javascript:enviar('Voltar')";
        }else{
            $this->setTemplate(new TemplatePaginaPadrao("templates/ConsDetalheHistoricoParticipanteExtratoAta.html", "Registro de Preço > Extrato Atas"));
            $this->getTemplate()->WINDOW  = "0";
            $this->getTemplate()->BOTAO_VOLTAR =  "javascript:enviar('Voltar')";
        }
        $this->setAdaptacao(new RegistroPreco_Adaptacao_ConsDetalheHistoricoParticipanteExtratoAta());

        $this->getTemplate()->NOME_PROGRAMA = "ConsDetalheHistoricoParticipanteExtratoAta";
        $this->getTemplate()->TITULO_SUPERIOR = 'EXTRATO ATAS - HISTÓRICO PARTICIPANTE';
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
            'seqItem' => isset($_REQUEST['seqItem']) ? filter_var($_REQUEST['seqItem'], FILTER_SANITIZE_NUMBER_INT) : null
        );
        $dto = new ArrayObject($arrayDto);
        $params_ = $dto;
        $_SESSION['seqItem'] = $dto['seqItem'];
        $extratosAta = $this->getAdaptacao()->configurarValoresAta($dto);        
        $orgaos = array();                               

        $sccGestor = $this->getAdaptacao()
            ->getNegocio()
            ->consultarSccDoProcesso($dto['ata'], $dto['item'], $dto['tipoItem'], '=', $dto['seqItem']);

        $sccParticipante = $this->getAdaptacao()->getNegocio()->consultarSccDoProcesso(
            $dto['ata'], $dto['item'], $dto['tipoItem'], '!=', $dto['seqItem']
        );

        $itensAtaParticipante = $this->getAdaptacao()->getNegocio()->consultarItensAtaParticipante(
            $dto['ata'],
            $dto['item'],
            $dto['tipoItem'],
            $dto['seqItem']
        );        

        $params_['orgao'] = array($dto['orgao']);
        if(!empty($itensAtaParticipante)) {
            foreach($itensAtaParticipante as $key => $value) {
                $params_['orgao'][] = $value->corglicodi;
                $orgao = $value->eorglidesc;
                if($value->corglicodi == $value->orgao_gestor) {
                    $orgao .= ' - GESTOR';
                }
                $orgaos[$value->corglicodi]['orgao'] = $orgao;
            }
        }

        if(!empty($sccParticipante)) {
            foreach($sccParticipante as $key => $value) {
                $params_['orgao'][] = $value->corglicodi;
                if(isset($orgaos[$value->corglicodi]['orgao']) &&  strpos($orgaos[$value->corglicodi]['orgao'], 'GESTOR') !== false) {
                    continue;
                }

                $orgao = $value->eorglidesc;
                if($value->orgao_agrupamento == $value->orgao_gestor) {
                    $orgao .= ' - GESTOR';
                }
                $orgaos[$value->corglicodi]['orgao'] = $orgao;
            }
        }

        $valorAtaAnterior = $this->getAdaptacao()->consultarUltilizadoAtaAnterior($params_);
        $valorSccAtaAnterior = $this->getAdaptacao()->consultarUtilizadoSccAtaAnterior($params_);

        if (! empty($extratosAta)) {
            $this->plotarBlocoResultadoAta($extratosAta, $valorAtaAnterior, $valorSccAtaAnterior);
        }

        $tipoControle = $this->getAdaptacao()->getNegocio()->consultarTipoControle($dto['ata']); 
        $orgaos = $this->itensInclusaoDiretaParticipante($itensAtaParticipante, $dto, $orgaos, $tipoControle[0]->farpnotsal);
        $orgaos = $this->itensScc($sccParticipante, $dto, $orgaos);

        $this->plotarBlocoOrgaos($orgaos, $dto['ata']);

        $qtdUtilizada   = $this->getAdaptacao()->quantidadeUtilizada;
        $valorUtilizado = $this->getAdaptacao()->valorUtilizado;

        $this->getTemplate()->QTDGESTORPARTICIPANTE   = converte_valor_licitacao($qtdUtilizada);
        $this->getTemplate()->SALDOGESTORPARTICIPANTE = converte_valor_licitacao($this->getAdaptacao()->quantidadeAta - $qtdUtilizada);
        $this->getTemplate()->SALDO_VALOR_GP          = converte_valor_licitacao($this->getAdaptacao()->valorAta - $valorUtilizado);

        $this->getTemplate()->ATA      = $dto['ata'];
        $this->getTemplate()->TIPO     = $dto['tipoItem'];
        $this->getTemplate()->ORGAO    = $dto['orgao'];
        $this->getTemplate()->ITEM     = $dto['item'];
        $this->getTemplate()->TIPOITEM = $dto['tipoItem'];
    }

    /**
     * Plotar tabela com os dados dos orgaõs
     * 
     * @param $data
     * @param $ata
     * @return void
     */
    private function plotarBlocoOrgaos($data, $ata) {

        // Verificar tipo de controle 
        $tipoControle = $this->getAdaptacao()->getNegocio()->consultarTipoControle($ata); 

        if(!empty($data)) {
            usort($data, array($this, 'compareOrgaos'));

            $gestores = array();
            foreach( $data as $key => $value) {
                if (strpos($value['orgao'], 'GESTOR') !== false) {
                    $gestores[$key] = $data[$key];
                    unset($data[$key]);
                }
            }

            $data = array_merge($gestores, $data);        

            foreach($data as $key => $value) {
                $qtdTotal           = 0;
                $qtd_utilizada      = 0;
                $qtd_solicitada     = 0;
                $saldo_disponivel   = 0;                    
                $exibir_tr = 'display:none';

                $this->getTemplate()->ORGAO = $value['orgao'];
                $this->getTemplate()->ORGAOURL = $_GET['orgao'];
                $this->getTemplate()->SEQITEM = $_GET['seqItem'];
                $this->getTemplate()->ATATIPO = $_GET['tipo'];
                
                if(!empty($value['item'])){ 
                    $exibir_tr = '';                   
                    foreach($value['item'] as $key_ => $value_) {
                        $tmpSumQtdUtilizada = ($tipoControle[0]->farpnotsal != 1) ? $value_['qtd_utilizada'] : $value_['vitescunit'];
                        $qtd_utilizada += $tmpSumQtdUtilizada;
                        //$qtd_solicitada += $value_['qtd_solicitada'];
                        $this->getTemplate()->DATASCC = $value_['data'];
                        $this->getTemplate()->NUMEROSCC = $value_['numero'];
                        $this->getTemplate()->IDSOLIC = $value_['id'];
                        $tmpQtdUtilizada = ($tipoControle[0]->farpnotsal != 1) ? $value_['qtd_utilizada'] : $value_['vitescunit'];
                        $this->getTemplate()->QTDUTILIZADA = converte_valor_licitacao($tmpQtdUtilizada);
                        
                        $this->getTemplate()->block('bloco_resultado_orgao');
                    }                                                     
                }

                $this->getTemplate()->EXIBIR_SCC = $exibir_tr;
                $this->getTemplate()->block('bloco_tr_resultado_orgao');
                if($tipoControle[0]->farpnotsal != 1) {                    
                    // Inclusão direta
                    if(!empty($value['inclusao_direta'])) {
                        foreach($value['inclusao_direta'] as $key_ => $value_) {
                            $this->getTemplate()->RESULTADO_QTD_UTILIZADA = 'QUANTIDADE UTILIZADA - INCLUSÃO DIRETA: ' . converte_valor_licitacao($value_['apiarpqtut']);  
                            $qtd_utilizada += $value_['apiarpqtut'];
                            $qtd_solicitada += $value_['apiarpqtat'];
                            $saldo_disponivel += $value_['apiarpqtut'];
                            $this->getTemplate()->block('bloco_resultado_qtd_utilizado');
                        }
                    }
                    
                    $this->getTemplate()->VALOR_QUANTIDADE_UTILIZADA = converte_valor_licitacao($qtd_utilizada); 
                    $this->getTemplate()->VALOR_QUANTIDADE_SOLICITADA = converte_valor_licitacao($qtd_solicitada);
                    $this->getTemplate()->block('bloco_total_utilizado_solicitado_processo');
                    $this->getTemplate()->VALOR_SALDO_DISPONIVEL = converte_valor_licitacao($qtd_solicitada - $qtd_utilizada);
                } else {
                    // Inclusão direta
                    $valorUtilizado = 0;
                    if(!empty($value['inclusao_direta'])) {
                        foreach($value['inclusao_direta'] as $key_ => $value_) {
                            $qtd_utilizada  += $value_['apiarpqtut'];
                            $qtd_solicitada += $value_['apiarpqtat'];
                            $valorUtilizado += $value_['apiarpqtut'];

                            $this->getTemplate()->block('bloco_resultado_qtd_utilizado');
                        }
                    }
                    
                    $this->getTemplate()->VALOR_UTILIZADO = converte_valor_licitacao($valorUtilizado);;
                    $this->getTemplate()->VALOR_TOTAL_UTILIZADO = converte_valor_licitacao($qtd_utilizada);
                    $this->getTemplate()->VALOR_SOLICITADO_PROCESSO = converte_valor_licitacao($qtd_solicitada);
                    $this->getTemplate()->VALOR_SALDO_DISPONIVEL = converte_valor_licitacao($qtd_solicitada - $qtd_utilizada);
                    $this->getTemplate()->block('bloco_valores');
                }


                $this->getTemplate()->block('bloco_orgao');
            }
        }
    }   

    private function compareOrgaos($a, $b)
    {
        return strcmp($a["orgao"], $b["orgao"]);
    }

    /**
     */
    public function processVoltar()
    {
        $uri = "ConsAtaRegistroPrecoExtratoAtaDetalhe.php?window=".$_REQUEST['window']."&ata=".$_REQUEST['ata'];
        header('Location: ' . $uri);
        exit();
    }

    /**
     */
    public function imprimir()
    {
        $ata        = $_REQUEST['ata'];
        $tipo       = $_REQUEST['tipo'];
        $orgao      = $_REQUEST['orgao'];
        $item       = $_REQUEST['item'];
        $seqItem    = $_SESSION['seqItem'];
        $tipoItem   = $_REQUEST['tipoItem'];

        $uri = "PdfVisualizarExtratoAtaParticipante.php?ata=$ata&tipo=$tipo&orgao=$orgao&item=$item&tipoItem=$tipoItem&seqItem=$seqItem";
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

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
 *
 * @version   GIT: EMPREL-SAD-PORTAL-COMPRAS-REGISTRO-PRECO-BL-FUNC-20160519-1035
 * ----------------------------------------------------------------------------------
 * Alterado: Lucas Baracho
 * Data:     31/10/2018
 * Objetivo: Tarefa Redmine 206181
 * ----------------------------------------------------------------------------------
 * Alterado: Caio Coutinho - Pitang Agile TI
 * Data:     08/11/2018
 * Objetivo: Tarefa Redmine 205436
 * ----------------------------------------------------------------------------------
 * Alterado: Pitang Agile TI - Caio Coutinho
 * Data:     04/12/2018
 * Objetivo: Tarefa Redmine 207316
 * ----------------------------------------------------------------------------------
 * Alterado: Caio Coutinho - Pitang Agile TI
 * Data:     06/12/2018
 * Objetivo: Tarefa Redmine 207946
 * ----------------------------------------------------------------------------------
 * Alterado: Caio Coutinho - Pitang Agile TI
 * Data:     12/12/2018
 * Objetivo: Tarefa Redmine 207316
 * ----------------------------------------------------------------------------------
 * Alterado: Caio Coutinho - Pitang Agile TI
 * Data:     18/02/2019
 * Objetivo: Tarefa Redmine 211230
 * ----------------------------------------------------------------------------------
 * Alterado: Lucas Baracho
 * Data:     09/05/2019
 * Objetivo: Tarefa Redmine 216344
 * ----------------------------------------------------------------------------------
 * Alterado: João Madson
 * Data:     16/09/2019
 * Objetivo: Tarefa Redmine 222533
 * ----------------------------------------------------------------------------------
 * Alterado: João Madson
 * Data:     17/09/2019
 * Objetivo: Tarefa Redmine 223217
 * ----------------------------------------------------------------------------------
 */

 // 220038--

if (! @require_once dirname(__FILE__) . "/../bootstrap.php") {
    throw new Exception("Error Processing Request - Bootstrap", 1);
}

Seguranca();

global $SimboloConcatenacaoArray, $SimboloConcatenacaoDesc;

if(!empty($_SESSION['mensagemFeedback'])){    //Condição para chegagem de mensagem de erro indevidamente vindo de outra pag. e limpar o campo de mensagem. |Madson|
    if($_SESSION['conferePagina'] != 'participante'){
    unset($_SESSION['mensagemFeedback']);
    unset($_SESSION['conferePagina']);
    }
}
// Classe RegistroPreco_Dados_CadMigracaoAddParticipante
class RegistroPreco_Dados_CadMigracaoAddParticipante extends Dados_Abstrata {
    // sqlAtaPorchave
    private function sqlAtaPorchave($processo, $orgao, $ano, $chaveAta) {
        $sql  = "SELECT A.CARPINCODN, A.EARPINOBJE, A.AARPINANON, A.AARPINPZVG, A.TARPINDINI, ";
        $sql .= "       A.CGREMPCODI, A.CUSUPOCODI, F.NFORCRRAZS, D.EDOCLINOME, A.CORGLICODI, ";
        $sql .= "       A.CARPNOSEQU, A.ALICPOANOP, S.CSOLCOSEQU, A.AARPINANON, CARPNOSEQ1, ";
        $sql .= "       F.NFORCRRAZS, F.AFORCRCCGC, F.AFORCRCCPF, F.EFORCRLOGR, F.AFORCRNUME, ";
        $sql .= "       F.EFORCRBAIR, F.NFORCRCIDA, F.CFORCRESTA, FA.NFORCRRAZS AS RAZAOFORNECEDORATUAL, ";
        $sql .= "       FA.AFORCRCCGC AS CGCFORNECEDORATUAL, FA.AFORCRCCPF AS CPFFORNECEDORATUAL, ";
        $sql .= "       FA.EFORCRLOGR AS LOGRADOUROFORNECEDORATUAL, FA.AFORCRNUME AS NUMEROENDERECOFORNECEDORATUAL, ";
        $sql .= "       FA.EFORCRBAIR AS BAIRROFORNECEDORATUAL, FA.NFORCRCIDA AS CIDADEFORNECEDORATUAL, FA.CFORCRESTA AS ESTADOFORNECEDORATUAL ";
        $sql .= "FROM   SFPC.TBATAREGISTROPRECOINTERNA A ";
        $sql .= "       LEFT OUTER JOIN SFPC.TBSOLICITACAOLICITACAOPORTAL S ON (S.CLICPOPROC = A.CLICPOPROC AND S.ALICPOANOP = A.ALICPOANOP AND S.CCOMLICODI = A.CCOMLICODI AND S.CORGLICODI = A.CORGLICODI) ";
        $sql .= "       LEFT OUTER JOIN SFPC.TBFORNECEDORCREDENCIADO F ON F.AFORCRSEQU = A.AFORCRSEQU ";
        $sql .= "       LEFT OUTER JOIN SFPC.TBFORNECEDORCREDENCIADO FA ON FA.AFORCRSEQU = (SELECT AFA.AFORCRSEQU FROM SFPC.TBATAREGISTROPRECOINTERNA AFA WHERE AFA.CARPNOSEQU = A.CARPNOSEQ1) ";
        $sql .= "       LEFT OUTER JOIN SFPC.TBDOCUMENTOLICITACAO D ON D.CLICPOPROC = A.CLICPOPROC AND D.CLICPOPROC = $processo AND D.CORGLICODI = $orgao AND D.ALICPOANOP = $ano ";
        $sql .= "WHERE  A.CARPNOSEQU = $chaveAta ";

        return $sql;
    }

    private function sqlAtaParticipanteAta($chaveAta) {
        $sql  = "SELECT * ";
        $sql .= "FROM   SFPC.TBPARTICIPANTEATARP PA ";
        $sql .= "       INNER JOIN SFPC.TBORGAOLICITANTE O ON O.CORGLICODI = PA.CORGLICODI  ";
        $sql .= "WHERE  PA.CARPNOSEQU = " . $chaveAta->carpnosequ;

        return $sql;
    }

    private function sqlAtaParticipanteAtaOrgao($chaveAta, $numeroOrgao) {
        $sql  = "SELECT * ";
        $sql .= "FROM   SFPC.TBPARTICIPANTEATARP PA ";
        $sql .= "       INNER JOIN SFPC.TBORGAOLICITANTE O ON O.CORGLICODI = PA.CORGLICODI ";
        $sql .= "WHERE  PA.CARPNOSEQU = " . $chaveAta;
        $sql .= "       AND PA.CORGLICODI = " . $numeroOrgao;

        return $sql;
    }

    public function consultarAtaPorChave($processo, $ano, $orgao, $numeroAta) {
        $db = Conexao();

        $sql = $this->sqlAtaPorchave($processo, $orgao, $ano, $numeroAta);

        $res = executarSQL($db, $sql);
        $res->fetchInto($res, DB_FETCHMODE_OBJECT);

        $this->hasError($res);

        return $res;
    }

    public function consultarAtaParticipanteChave($numeroAta) {
        $db = Conexao();

        $sql = $this->sqlAtaParticipanteAta($numeroAta);

        $res      = executarSQL($db, $sql);
        $itens    = array();
        $item     = null;
        $itemTipo = new stdClass();
        
        while ($res->fetchInto($item, DB_FETCHMODE_OBJECT)) {
            $item->tipoItem = $itemTipo->tipoItem;
            $itens[] = $item;            
        }

        return $itens;
    }

    public function consultarAtaParticipanteAtaOrgao($numeroAta, $numeroOrgao) {
        $db = Conexao();

        $sql = $this->sqlAtaParticipanteAtaOrgao($numeroAta, $numeroOrgao);

        $res      = executarSQL($db, $sql);
        $itens    = array();
        $item     = null;
        $itemTipo = new stdClass();

        while ($res->fetchInto($item, DB_FETCHMODE_OBJECT)) {
            $item->tipoItem = $itemTipo->tipoItem;
            $itens[] = $item;
        }

        return $itens;
    }
   
    /**
     * Dados. Consultar itens da ata.
     *
     * @param $clicpoproc Código do Processo Licitatório
     * @param $alicpoanop Ano do Processo Licitatório
     * @param $cgrempcodi Código do Grupo
     * @param $ccomlicodi Código da Comissão
     * @param $corglicodi Código do Órgão Licitante
     */
    public function consultarItensAta($alicpoanop, $carpnosequ) {
        $db = Conexao();
        
        $sql = $this->sqlConsultarItensAta($alicpoanop, $carpnosequ);

        $res      = executarSQL($db, $sql);
        $itens    = array();
        $item     = null;
        $itemTipo = new stdClass();
        
        while ($res->fetchInto($item, DB_FETCHMODE_OBJECT)) {
            $item->tipoItem = $itemTipo->tipoItem;
            $itens[] = $item;
        }

        return $itens;
    }

    /**
     * Dados. Consultar itens da ata.
     *
     * @param $clicpoproc Código do Processo Licitatório
     * @param $alicpoanop Ano do Processo Licitatório
     * @param $cgrempcodi Código do Grupo
     * @param $ccomlicodi Código da Comissão
     * @param $corglicodi Código do Órgão Licitante
     */
    public function consultarItensAtaNotIn($alicpoanop, $carpnosequ) {
        $db = Conexao();

        $sql = $this->sqlConsultarItensAtaNotIn($alicpoanop, $carpnosequ);

        $res      = executarSQL($db, $sql);
        $itens    = array();
        $item     = null;
        $itemTipo = new stdClass();
        
        while ($res->fetchInto($item, DB_FETCHMODE_OBJECT)) {
            $item->tipoItem = $itemTipo->tipoItem;
            $itens[] = $item;
        }

        return $itens;
    }

    /**
     * Dados. Consultar itens da ata.
     *
     * @param $carpnosequ Código do Processo Licitatório    
     */
    public function consultarItensAtaParticipante($carpnosequ) {
        $db = Conexao();

        $sql = $this->sqlConsultarItensAtaParticipante($carpnosequ);

        $res      = executarSQL($db, $sql);
        $itens    = array();
        $item     = null;
        $itemTipo = new stdClass();
        $itemTipo->tipoItemValores = array();
        $itemTipo->tipoItem = "ITEMPARTICIPANTE";
        $carpnosequAnterior = '';
        $corglicodiAnterior = '';
        $citarpsequAnterior = '';

        while ($res->fetchInto($item, DB_FETCHMODE_OBJECT)) {
            $varArray = array($item->corglicodi => array('apiarpqtat' => $item->apiarpqtat, 'apiarpqtut' => $item->apiarpqtut, 'vpiarpvatu' => $item->vpiarpvatu, 'vpiarpvuti' => $item->vpiarpvuti));
            
            if ($citarpsequAnterior != $item->citarpsequ) {
                $citarpsequAnterior = $item->citarpsequ;
                $item->tipoItem = $itemTipo->tipoItem;
                $item->tipoItemValores = array();

                if ($itemTipo->tipoItemValores != null) {
                    $itemTipo->tipoItemValores = array();       
                }
                
                array_push($item->tipoItemValores, $varArray);
                $itens[] = $item;
            } else {
                $endItem = end($itens);
                array_push($endItem->tipoItemValores, $varArray);                                 
            }
        }

        return $itens;
    }

    /**
     * Dados. Consultar itens da ata.
     *
     * @param $carpnosequ Código do Processo Licitatório    
     */
    public function consultarItensProcessoParticipante($clicpoproc, $alicpoanop, $ccomlicodi, $cgrempcodi, $corglicodi, $aforcrsequ) {
        $db = Conexao();

        $sql = $this->sqlConsultarItensProcessoParticipante($clicpoproc, $alicpoanop, $ccomlicodi, $cgrempcodi, $corglicodi, $aforcrsequ);

        $res      = executarSQL($db, $sql);
        $itens    = array();
        $item     = null;
        $itemTipo = new stdClass();
        $itemTipo->tipoItemValores = array();
        $itemTipo->tipoItem = "ITEMPARTICIPANTE";
        $citelpsequsequAnterior = '';

        while ($res->fetchInto($item, DB_FETCHMODE_OBJECT)) {
            $varArray = array($item->corglicodi => array('aitelpqtso' => $item->aitelpqtso, 'apiarpqtut'));

            if ($citelpsequsequAnterior != $item->citelpsequ) {
                $citelpsequsequAnterior = $item->citelpsequ;
                $item->tipoItem         = $itemTipo->tipoItem;
                $item->tipoItemValores  = array();

                if ($itemTipo->tipoItemValores != null) {
                    $itemTipo->tipoItemValores = array();       
                }

                array_push($item->tipoItemValores, $varArray);
                $itens[] = $item;
            } else {
                $endItem = end($itens);
                array_push($endItem->tipoItemValores, $varArray);
            }
        }

        return $itens;
    }

    /**
     * SQL consultar itens da ata.
     *
     * @param $clicpoproc Código do Processo Licitatório
     * @param $alicpoanop Ano do Processo Licitatório
     * @param $cgrempcodi Código do Grupo
     * @param $ccomlicodi Código da Comissão
     * @param $corglicodi Código do Órgão Licitante
     */
    private function sqlConsultarItensAta($alicpoanop, $carpnosequ) {
        $sql  = "SELECT * ";
        $sql .= "FROM   SFPC.TBITEMATAREGISTROPRECONOVA I ";
        $sql .= "       INNER JOIN SFPC.TBATAREGISTROPRECOINTERNA ARPI ON ARPI.CARPNOSEQU = I.CARPNOSEQU AND ARPI.ALICPOANOP = %d ";
        $sql .= "       LEFT OUTER JOIN SFPC.TBMATERIALPORTAL M ON I.CMATEPSEQU = M.CMATEPSEQU ";
        $sql .= "       LEFT OUTER JOIN SFPC.TBUNIDADEDEMEDIDA UMP ON UMP.CUNIDMCODI = M.CUNIDMCODI ";
        $sql .= "       LEFT OUTER JOIN SFPC.TBSERVICOPORTAL S ON I.CSERVPSEQU = S.CSERVPSEQU ";
        $sql .= "WHERE  I.CARPNOSEQU = %d ";

        return sprintf($sql, $alicpoanop, $carpnosequ);
    }

    /**
     * SQL consultar itens da ata.
     *
     * @param $clicpoproc Código do Processo Licitatório
     * @param $alicpoanop Ano do Processo Licitatório
     * @param $cgrempcodi Código do Grupo
     * @param $ccomlicodi Código da Comissão
     * @param $corglicodi Código do Órgão Licitante
     */
    private function sqlConsultarItensAtaNotIn($alicpoanop, $carpnosequ) {
        $sql  = "SELECT * ";
        $sql .= "FROM   SFPC.TBITEMATAREGISTROPRECONOVA I ";
        $sql .= "       INNER JOIN SFPC.TBATAREGISTROPRECOINTERNA ARPI ON ARPI.CARPNOSEQU = I.CARPNOSEQU AND ARPI.AARPINANON = $alicpoanop ";
        $sql .= "       LEFT OUTER JOIN SFPC.TBMATERIALPORTAL M ON I.CMATEPSEQU = M.CMATEPSEQU ";
        $sql .= "       LEFT OUTER JOIN SFPC.TBUNIDADEDEMEDIDA UMP ON UMP.CUNIDMCODI = M.CUNIDMCODI ";
        $sql .= "       LEFT OUTER JOIN SFPC.TBSERVICOPORTAL S ON I.CSERVPSEQU = S.CSERVPSEQU ";
        $sql .= "WHERE  I.CARPNOSEQU = $carpnosequ ";
        $sql .= "       AND I.CITARPSEQU NOT IN (SELECT IPA.CITARPSEQU  ";
        $sql .= "                                FROM   SFPC.TBPARTICIPANTEATARP ARPI ";
        $sql .= "                                       INNER JOIN SFPC.TBPARTICIPANTEITEMATARP IPA ON IPA.CARPNOSEQU = ARPI.CARPNOSEQU AND IPA.CORGLICODI = ARPI.CORGLICODI ";
		$sql .= "                                       INNER JOIN SFPC.TBITEMATAREGISTROPRECONOVA I ON I.CARPNOSEQU = ARPI.CARPNOSEQU AND I.CITARPSEQU = IPA.CITARPSEQU ";
		$sql .= "                                       LEFT OUTER JOIN SFPC.TBMATERIALPORTAL M ON I.CMATEPSEQU = M.CMATEPSEQU ";
		$sql .= "                                       LEFT OUTER JOIN SFPC.TBUNIDADEDEMEDIDA UMP ON UMP.CUNIDMCODI = M.CUNIDMCODI ";
		$sql .= "                                       LEFT OUTER JOIN SFPC.TBSERVICOPORTAL S ON I.CSERVPSEQU = S.CSERVPSEQU INNER ";
		$sql .= "                                       JOIN SFPC.TBORGAOLICITANTE O ON O.CORGLICODI = ARPI.CORGLICODI ";
	    $sql .= "                               WHERE   I.CARPNOSEQU = $carpnosequ ";
	    $sql .= "                               ORDER BY IPA.CITARPSEQU, IPA.CORGLICODI ASC) ";

        return $sql;
    }

    /**
     * SQL consultar itens da ata Participante.
     *
     * @param $carpnosequ Código do Processo Licitatório     
     */
    private function sqlConsultarItensAtaParticipante($carpnosequ) {
        $sql  = "SELECT ARPI.*, I.*, M.*, UMP.*, S.*, O.*, ISC.VITESCUNIT, ";
        $sql .= "       ISC.AITESCQTSO, IPA.APIARPQTAT, IPA.APIARPQTUT, IPA.VPIARPVUTI, IPA.VPIARPVATU ";
        $sql .= "FROM   SFPC.TBPARTICIPANTEATARP ARPI ";
        $sql .= "       INNER JOIN SFPC.TBPARTICIPANTEITEMATARP IPA ON IPA.CARPNOSEQU = ARPI.CARPNOSEQU AND IPA.CORGLICODI = ARPI.CORGLICODI ";
        $sql .= "       INNER JOIN SFPC.TBITEMATAREGISTROPRECONOVA I ON I.CARPNOSEQU = ARPI.CARPNOSEQU AND I.CITARPSEQU = IPA.CITARPSEQU ";
        $sql .= "       LEFT OUTER JOIN SFPC.TBMATERIALPORTAL M ON I.CMATEPSEQU = M.CMATEPSEQU ";
        $sql .= "       LEFT OUTER JOIN SFPC.TBUNIDADEDEMEDIDA UMP ON UMP.CUNIDMCODI = M.CUNIDMCODI ";
        $sql .= "       LEFT OUTER JOIN SFPC.TBSERVICOPORTAL S ON I.CSERVPSEQU = S.CSERVPSEQU ";
        $sql .= "       INNER JOIN SFPC.TBORGAOLICITANTE O ON O.CORGLICODI = ARPI.CORGLICODI ";
        $sql .= "       LEFT JOIN SFPC.TBSOLICITACAOCOMPRA SC ON SC.CARPNOSEQU = I.CARPNOSEQU ";
        $sql .= "       LEFT JOIN SFPC.TBITEMSOLICITACAOCOMPRA ISC ON ISC.CARPNOSEQU = I.CARPNOSEQU AND ISC.CSOLCOSEQU = SC.CSOLCOSEQU AND ISC.CITARPSEQU = I.CITARPSEQU ";
        $sql .= "WHERE  I.CARPNOSEQU = %d ";
        $sql .= "ORDER BY IPA.CITARPSEQU, IPA.CORGLICODI ASC ";

        return sprintf($sql, $carpnosequ);
    }

    // SQL consultar itens do processo
    private function sqlConsultarItensProcessoParticipante($clicpoproc, $alicpoanop, $ccomlicodi, $cgrempcodi, $corglicodi, $aforcrsequ) {
        $sql  = "SELECT * ";
        $sql .= "FROM 	SFPC.TBITEMLICITACAOPORTAL I  ";
        $sql .= "       LEFT OUTER JOIN SFPC.TBMATERIALPORTAL M ON I.CMATEPSEQU = M.CMATEPSEQU ";
        $sql .= "       LEFT OUTER JOIN SFPC.TBUNIDADEDEMEDIDA UMP ON UMP.CUNIDMCODI = M.CUNIDMCODI ";
        $sql .= "       LEFT OUTER JOIN SFPC.TBSERVICOPORTAL S ON I.CSERVPSEQU = S.CSERVPSEQU ";
        $sql .= "WHERE 	I.CLICPOPROC = $clicpoproc ";
        $sql .= "       AND I.CCOMLICODI = $ccomlicodi ";
        $sql .= "       AND I.ALICPOANOP = $alicpoanop ";
        $sql .= "       AND I.CGREMPCODI = $cgrempcodi ";
        $sql .= "       AND I.CORGLICODI = $corglicodi ";
        $sql .= "       AND I.AFORCRSEQU = $aforcrsequ ";

        return $sql;
    }

    /**
     *
     * @param unknown $valores
     */
    public function sqlAddParticipanteOrgaoAta($valores) {
        $sql  = "INSERT INTO SFPC.TBPARTICIPANTEATARP ";
        $sql .= "(CARPNOSEQU, CORGLICODI, CUSUPOCODI, TPATRPULAT,FPATRPSITU) ";
        $sql .= "VALUES($valores->carpnosequ, $valores->corglicodi, $valores->cusupocodi, '$valores->tpatrpulat', '$valores->fpatrpsitu') ";

        return $sql;
    }

    /**
     *
     * @param unknown $codigoOrgao $cogidoAta
     */
    public function sqlAtivarItemOrgaoParticipante($codigoOrgao, $cogidoAta) {
        $sql  = "UPDATE SFPC.TBPARTICIPANTEITEMATARP ";
        $sql .= "SET    FPIARPSITU = 'A' ";
        $sql .= "WHERE  CARPNOSEQU = $cogidoAta ";
        $sql .= "       AND CORGLICODI = $codigoOrgao ";

        return $sql;
    }

    /**
     *
     * @param unknown $codigoOrgao $cogidoAta
     */
    public function sqlAtivarOrgaoParticipante($codigoOrgao, $cogidoAta) {
        $sql  = "UPDATE SFPC.TBPARTICIPANTEATARP ";
        $sql .= "SET    FPATRPSITU = 'A' ";
        $sql .= "WHERE  CARPNOSEQU = $cogidoAta ";
        $sql .= "       AND CORGLICODI = $codigoOrgao ";

        return $sql;
    }

    /**
     *
     * @param unknown $codigoOrgao $cogidoAta
     */
    public function sqlInativarItemOrgaoParticipante($codigoOrgao, $cogidoAta) {
        $sql  = "UPDATE SFPC.TBPARTICIPANTEITEMATARP ";
        $sql .= "SET    FPIARPSITU = 'I' ";
        $sql .= "WHERE  CARPNOSEQU = $cogidoAta ";
        $sql .= "       AND CORGLICODI = $codigoOrgao ";

        return $sql;
    }

    /**
     *
     * @param unknown $codigoOrgao $cogidoAta
     */
    public function sqlInativarOrgaoParticipante($codigoOrgao, $cogidoAta) {
        $sql  = "UPDATE SFPC.TBPARTICIPANTEATARP ";
        $sql .= "SET    FPATRPSITU = 'I' ";
        $sql .= "WHERE CARPNOSEQU =  $cogidoAta ";
        $sql .= "       AND CORGLICODI = $codigoOrgao ";

        return $sql;
    }

    /**
     *
     * @param unknown $codigoOrgao $cogidoAta
     */
    public function sqlRemoverOrgaoParticipante($codigoOrgao, $cogidoAta) {
        $sql  = "DELETE FROM SFPC.TBPARTICIPANTEATARP ";
        $sql .= "WHERE  CARPNOSEQU =  $cogidoAta ";
        $sql .= "       AND CORGLICODI = $codigoOrgao ";

        return $sql;
    }

    /**
     *
     * @param unknown $codigoOrgao $cogidoAta
     */
    public function sqlRemoverItemOrgaoParticipante($codigoOrgao, $cogidoAta) {
        $sql  = "DELETE FROM SFPC.TBPARTICIPANTEITEMATARP ";
        $sql .= "WHERE  CARPNOSEQU =  $cogidoAta ";
        $sql .= "       AND CORGLICODI = $codigoOrgao ";

        return $sql;
    }

    /**
     *
     * @param unknown $valores
     */
    public function sqlUpdateParticipanteOrgaoAta($valores) {
        $sql  = "UPDATE SFPC.TBPARTICIPANTEATARP ";
        $sql .= "SET    CUSUPOCODI = " . $valores->cusupocodi;
        $sql .= ",      TPATRPULAT = '" . $valores->tpatrpulat . "' ";
        $sql .= ",      FPATRPSITU = '" . $valores->fpatrpsitu . "' ";
        $sql .= "WHERE  CARPNOSEQU = ". $valores->carpnosequ;
        $sql .= "       AND CORGLICODI = ". $valores->corglicodi;

        return $sql;
    }

    /**
     *
     * @return string
     */
    public function sqlConsultarMaiorItem() {
        $sql  = "SELECT  MAX(I.CITARPSEQU) ";
        $sql .="FROM    SFPC.TBPARTICIPANTEITEMATARP I ";

        return $sql;
    }

    private function sqlConsultarCentroDeCustoUsuario($cgrempcodi, $cusupocodi, $corglicodi) {
        $sql  = "SELECT CCP.CCENPOCORG, CCP.CCENPOUNID, CCP.CORGLICODI ";
        $sql .= "FROM   SFPC.TBCENTROCUSTOPORTAL CCP ";
        $sql .= "WHERE  1 = 1 ";

            if ($corglicodi != null || $corglicodi != "") {
                $sql .= "AND CCP.CORGLICODI = %d";
            }

        return sprintf($sql, $corglicodi);
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

        return $itens;
    }

    public function consultarTipoControle($ata) {
        $sql = " SELECT arpn.farpnotsal 
                 FROM sfpc.tbataregistropreconova arpn
                 WHERE arpn. carpnosequ = %d";

        $resultado = ClaDatabasePostgresql::executarSQL(sprintf($sql, $ata));

        return $resultado;
    }
}

/**
 * Classe RegistroPreco_Negocio_CadMigracaoAddParticipante
 */
class RegistroPreco_Negocio_CadMigracaoAddParticipante extends Negocio_Abstrata {
    /**
     *
     * {@inheritdoc}
     *
     * @see Negocio_Abstrata::getDados()
     */
    public function getDados() {
        $this->setDados(new RegistroPreco_Dados_CadMigracaoAddParticipante());
        
        return parent::getDados();
    }

    // Negócio - consultar itens da ata
    public function consultarItensAta($alicpoanop, $carpnosequ) {
        return $this->getDados()->consultarItensAta($alicpoanop, $carpnosequ);
    }

    // Negócio - consultar itens da ata
    public function consultarItensAtaNotIn($alicpoanop, $carpnosequ) {
        return $this->getDados()->consultarItensAtaNotIn($alicpoanop, $carpnosequ);
    }

    // Negócio - Consultar itens da ata
    public function consultarItensAtaParticipante($carpnosequ) {
        return $this->getDados()->consultarItensAtaParticipante($carpnosequ);
    }

    public function consultarItensProcessoParticipante($processo, $ano, $comissao, $grupo, $orgao, $fornecedor) {
        return $this->getDados()->consultarItensProcessoParticipante($processo, $ano, $comissao, $grupo, $orgao, $fornecedor);
    }

    public function consultarAtaPorChave($processo, $ano, $orgao, $numeroAta) {
        return $this->getDados()->consultarAtaPorChave($processo, $ano, $orgao, $numeroAta);
    }

    public function consultarAtaParticipanteChave($numeroAta) {
        return $this->getDados()->consultarAtaParticipanteChave($numeroAta);
    }

    public function consultarAtaParticipanteAtaOrgao($numeroAta, $numeroOrgao) {
        return $this->getDados()->consultarAtaParticipanteAtaOrgao($numeroAta, $numeroOrgao);
    }

    public function consultarDCentroDeCustoUsuario($cgrempcodi, $cusupocodi, $corglicodi) {
        return $this->getDados()->consultarDCentroDeCustoUsuario($cgrempcodi, $cusupocodi, $corglicodi);
    }

    public function consultarTipoControle($carpnosequ) {
        return $this->getDados()->consultarTipoControle($carpnosequ);
    }

    /**
     * Negócio. Salvar
     *
     * @return void
     */
    public function salvar($entidade, $itensOrgao) {
        $arrayEntidadeFinal = array();
        $semerror = true;

        if (!$this->validarItemAta($itensOrgao)){
            return false;
        }

        if (!empty($_SESSION['orgaos'])) {
            foreach ($_SESSION['orgaos'] as $key => $value) {
                $entidade->corglicodi = $key;

                // Depois verificar a situação
                $entidade->fpatrpsitu = 'A';
                $entidade->tpatrpulat = date('Y-m-d H:i:s');

                $db = Conexao();

                $db->autoCommit(false);
                $db->query("BEGIN TRANSACTION");

                $consultarParticipanteAtaOrgao = $this->consultarParticipanteAtaOrgao($db, $entidade->carpnosequ, $entidade->corglicodi);

                if ($consultarParticipanteAtaOrgao == null) {
                    $sqlParticipanteNovo = $this->getDados()->sqlAddParticipanteOrgaoAta($entidade);
                    $resultadoAtaNova    = executarTransacao($db, $sqlParticipanteNovo);
                    $commited            = $db->commit();
                } else {
                    $sqlParticipanteNovo = $this->getDados()->sqlUpdateParticipanteOrgaoAta($entidade);
                    $resultadoAtaNova    = executarTransacao($db, $sqlParticipanteNovo);
                    $commited            = $db->commit();
                }

                if ($commited instanceof DB_error) {
                    $db->rollback();
                    
                    $_SESSION['mensagemFeedback'] = 'Erro ao salvar dados';
                    $_SESSION['conferePagina'] = 'participante';
                    ExibeErroBD(self::$erroPrograma . "\nLinha: " . __LINE__ . "\nSql: " . $e->getMessage());
                    $semerror = false;
                }
            }

            $database = Conexao();
            
            $database->autoCommit(false);
            $database->query("BEGIN TRANSACTION");

            try {
                foreach ($itensOrgao as $codigoItem => $item) {
                    foreach ($item as $codigoOrgao => $itemOrgao) {
                        if (is_array($itemOrgao)) {
                            $this->salvarItemAtaParticipante($database, $_SESSION['ata'], $codigoItem, $codigoOrgao, $itemOrgao, $item['tipoControle']);
                        }
                    }
                }

                $database->query("COMMIT");
                $database->query("END TRANSACTION");

                $_SESSION['mensagemFeedback'] = 'Dados salvos com sucesso';
                $_SESSION['conferePagina'] = 'participante';
            } catch (Exception $e) {
                $semerror = false;
                $database->query("ROLLBACK");
                $_SESSION['mensagemFeedback'] = 'Erro ao salvar dados';
                $_SESSION['conferePagina'] = 'participante';
                ExibeErroBD("\nLinha: ".__LINE__."\nSql: " . $e->getMessage());
            }
        } else {
            $_SESSION['mensagemFeedback'] = 'Dados salvos com sucesso';
            $_SESSION['conferePagina'] = 'participante';
        }
        
        return $semerror;
    }

    /**
     * Negócio. Retirar Participante
     *
     * @return void
     */
    public function retirarParticipante($orgaosParaRemover) {
        $semerror = true;

        foreach ($orgaosParaRemover as $keyCodigo => $orgao) {
            if (!$this->removerItemOrgao($keyCodigo, $_SESSION['ata'])) {
                $semerror = false;
                break;
            }

            if (!$this->removerParticipanteOrgao($keyCodigo, $_SESSION['ata'])) {
                $semerror = false;
                break;
            }

            unset($_SESSION['orgaos'][$keyCodigo]);
        }
        
        return $semerror;
    }

    public function verificarExcluirParticipante($carpnosequ, $orgaos) {
        $corglocodi = implode(',', $orgaos);
        
        $db = Conexao();
        
        $sql  = "SELECT  COUNT(*) ";
        $sql .= "FROM   SFPC.TBSOLICITACAOCOMPRA ";
        $sql .= "WHERE  FSOLCORPCP = 'P' ";
        $sql .= "       AND CARPNOSEQU = %d ";
        $sql .= "       AND CORGLICODI IN (".$corglocodi.") ";
        $sql .= "       AND CSITSOCODI IN (3,4,5) ";

        $resultado = executarTransacao($db, sprintf($sql, $carpnosequ));
        $resultado->fetchInto($resultado, DB_FETCHMODE_OBJECT);

        return $resultado;
    }

    /**
     * Negócio. Ativar Participante
     *
     * @return void
     */
    public function ativarParticipante($orgaosParaAtivar) {
        $semerror = true;

        foreach ($orgaosParaAtivar as $keyCodigo => $orgao) {
            if (!$this->ativarItemOrgao($keyCodigo, $_SESSION['ata'])) {
                $semerror = false;
                break;
            }

            if (!$this->ativarParticipanteOrgao($keyCodigo, $_SESSION['ata'])) {
                $semerror = false;
                break;
            }
        }             
        return $semerror;
    }

    /**
     * Negócio. Ativar Participante
     *
     * @return void
     */
    public function inativarParticipante($orgaosParaAtivar) {        
        $semerror = true;

        foreach ($orgaosParaAtivar as $keyCodigo => $orgao) {
            if (!$this->inativarItemOrgao($keyCodigo, $_SESSION['ata'])) {
                $semerror = false;
                break;
            }

            if (!$this->inativarParticipanteOrgao($keyCodigo, $_SESSION['ata'])) {
                $semerror = false;
                break;
            }
        }

        return $semerror;
    }

    public function validarItemAta($itensOrgao) {
        $_SESSION['post_itens_armazenar_tela'] = $itensOrgao;

        $field_1 = 'apiarpqtat';
        $field_2 = 'apiarpqtut';

        foreach ($itensOrgao as $codigoItem => $item) {
            $somatorioDaVez = 0;

            if ($item['tipoControle'] == 1) {
                $field_1 = 'vpiarpvatu';
                $field_2 = 'vpiarpvuti';
            }

            foreach ($item as $codigoOrgao => $itemOrgao) {
                if (is_array($itemOrgao)) {
                    if ((!empty($itemOrgao[$field_1]) && !empty($itemOrgao[$field_2])) && moeda2float($itemOrgao[$field_1]) < (moeda2float($itemOrgao[$field_2]) + moeda2float($itemOrgao['scc']))) {
                        $_SESSION['mensagemFeedbackTipo'] = 1;
                        $_SESSION['mensagemFeedback'] = 'O Utilizado do Item de Lote ' . $item['lote'] . ' e Ordº ' . $item['ord'] . ' não pode ser maior que o Solicitado';
                        $_SESSION['conferePagina'] = 'participante';
                        return false;
                    }

                    if ($itemOrgao[$field_2] != 0 && $itemOrgao[$field_1] == 0) {
                        $_SESSION['mensagemFeedbackTipo'] = 1;
                        $_SESSION['mensagemFeedback'] = 'O Solicitado do Item de Lote ' . $item['lote'] . ' e Ordº ' . $item['ord'] . ' não pode ser zero';
                        $_SESSION['conferePagina'] = 'participante';
                        return false;
                    }

                    $somatorioDaVez += moeda2float($itemOrgao[$field_1]);
                    $somatorioDaVez = number_format((float)$somatorioDaVez, 2, '.', '');
                }
            }

            // Verificando se o saldo do item é menor que a soma de todas as quantidades para o item
            if (moeda2float($item['qtd_total_item']) < $somatorioDaVez) {
                $_SESSION['mensagemFeedbackTipo'] = 1;
                $_SESSION['mensagemFeedback'] = 'A soma das Quantidades Totais do Item de Lote ' . $item['lote'] . ' e Ordº ' . $item['ord'] . ' de todos os Participantes não pode ser superior que a Quantidade Total da Ata';
                $_SESSION['conferePagina'] = 'participante';
                return false;
            }
        }

        return true;
    }

    private function removerItemOrgao($keyCodigo, $ata) {
        $db = Conexao();
        $db->autoCommit(false);
        $db->query("BEGIN TRANSACTION");
        
        $sqlRemoverOrgaoItemParticipante = $this->getDados()->sqlRemoverItemOrgaoParticipante($keyCodigo, $ata);
        $resultadoAtaNova = executarTransacao($db, $sqlRemoverOrgaoItemParticipante);
        $commited = $db->commit();                

        if ($commited instanceof DB_error) {
            $db->rollback();
            $_SESSION['mensagemFeedback'] = 'Erro ao salvar dados';
            $_SESSION['conferePagina'] = 'participante';
            ExibeErroBD(self::$erroPrograma . "\nLinha: " . __LINE__ . "\nSql: " . $e->getMessage());
            return false;
        }

        return true;
    }

    private function ativarItemOrgao($keyCodigo, $ata) {
        $db = Conexao();
        $db->autoCommit(false);
        $db->query("BEGIN TRANSACTION");

        $sqlRemoverOrgaoItemParticipante = $this->getDados()->sqlAtivarItemOrgaoParticipante($keyCodigo, $ata);
        $resultadoAtaNova = executarTransacao($db, $sqlRemoverOrgaoItemParticipante);
        $commited = $db->commit();

        if ($commited instanceof DB_error) {
            $db->rollback();
            $_SESSION['mensagemFeedback'] = 'Erro ao salvar dados';
            $_SESSION['conferePagina'] = 'participante';
            ExibeErroBD(self::$erroPrograma . "\nLinha: " . __LINE__ . "\nSql: " . $e->getMessage());
            return false;
        }

        return true;
    }

    private function ativarParticipanteOrgao($keyCodigo, $ata) {
        $db = Conexao();
        $db->autoCommit(false);
        $db->query("BEGIN TRANSACTION");

        $sqlRemoverOrgaoItemParticipante = $this->getDados()->sqlAtivarOrgaoParticipante($keyCodigo, $ata);
        $resultadoAtaNova = executarTransacao($db, $sqlRemoverOrgaoItemParticipante);
        $commited = $db->commit();

        if ($commited instanceof DB_error) {
            $db->rollback();
            $_SESSION['mensagemFeedback'] = 'Erro ao salvar dados';
            $_SESSION['conferePagina'] = 'participante';
            ExibeErroBD(self::$erroPrograma . "\nLinha: " . __LINE__ . "\nSql: " . $e->getMessage());
            return false;
        }

        return true;
    }

    private function inativarItemOrgao($keyCodigo, $ata) {
        $db = Conexao();
        $db->autoCommit(false);
        $db->query("BEGIN TRANSACTION");

        $sqlRemoverOrgaoItemParticipante = $this->getDados()->sqlInativarItemOrgaoParticipante($keyCodigo, $ata);
        $resultadoAtaNova = executarTransacao($db, $sqlRemoverOrgaoItemParticipante);
        $commited = $db->commit();

        if ($commited instanceof DB_error) {
            $db->rollback();
            $_SESSION['mensagemFeedback'] = 'Erro ao salvar dados';
            $_SESSION['conferePagina'] = 'participante';
            ExibeErroBD(self::$erroPrograma . "\nLinha: " . __LINE__ . "\nSql: " . $e->getMessage());
            return false;
        }

        return true;
    }

    private function inativarParticipanteOrgao($keyCodigo, $ata) {
        $db = Conexao();
        $db->autoCommit(false);
        $db->query("BEGIN TRANSACTION");

        $sqlRemoverOrgaoItemParticipante = $this->getDados()->sqlInativarOrgaoParticipante($keyCodigo, $ata);
        $resultadoAtaNova = executarTransacao($db, $sqlRemoverOrgaoItemParticipante);
        $commited = $db->commit();

        if ($commited instanceof DB_error) {
            $db->rollback();
            $_SESSION['mensagemFeedback'] = 'Erro ao salvar dados';
            $_SESSION['conferePagina'] = 'participante';
            ExibeErroBD(self::$erroPrograma . "\nLinha: " . __LINE__ . "\nSql: " . $e->getMessage());
            return false;
        }

        return true;
    }

    private function removerParticipanteOrgao($keyCodigo, $ata) {
        $db = Conexao();
        $db->autoCommit(false);
        $db->query("BEGIN TRANSACTION");

        $sqlRemoverOrgaoParticipante = $this->getDados()->sqlRemoverOrgaoParticipante($keyCodigo, $ata);
        $resultadoAtaNova = executarTransacao($db, $sqlRemoverOrgaoParticipante);
        $commited = $db->commit();

        if ($commited instanceof DB_error) {
            $db->rollback();
            $_SESSION['mensagemFeedback'] = 'Erro ao salvar dados';
            $_SESSION['conferePagina'] = 'participante';
            ExibeErroBD(self::$erroPrograma . "\nLinha: " . __LINE__ . "\nSql: " . $e->getMessage());
            return false;
        }

        return true;
    }

    private function salvarItemAtaParticipante($db, $ata, $codigoItem, $codigoOrgao, $itemOrgao, $tipoControle) {
        $itemNoBanco = $this->consultarItemAta($db, $ata, $codigoItem, $codigoOrgao);

        $resultado = null;

        if ($itemNoBanco == null) {
            $resultado = $this->inserirItem($db, $ata, $codigoItem, $codigoOrgao, $itemOrgao, $tipoControle);
        } else {
            $resultado = $this->atualizarItem($db, $ata, $codigoItem, $codigoOrgao, $itemOrgao, $tipoControle);
        }

        if (PEAR::isError($resultado)) {
            throw new RuntimeException($resultado->getMessage());
        }
    }

    private function inserirItem($db, $ata, $codigoItem, $codigoOrgao, $itemOrgao, $tipoControle) {
        $sequencial    = $codigoItem;
        $situacao      = 'A';
        $codigoUsuario = $_SESSION['_cusupocodi_'];
        $tpiarpulat    = date('Y-m-d H:i:s');

        if ($tipoControle != 1) {
            $quatidadeDoParticipante = $itemOrgao['apiarpqtat'] == null ? moeda2float(0, 4) : moeda2float($itemOrgao['apiarpqtat'], 4);
            $quatidadeUtilizadaDoParticipante = $itemOrgao['apiarpqtut'] == null ? moeda2float(0, 4) : moeda2float($itemOrgao['apiarpqtut'], 4);
        } else {
            $quatidadeDoParticipante = $itemOrgao['vpiarpvatu'] == null ? moeda2float(0, 4) : moeda2float($itemOrgao['vpiarpvatu'], 4);
            $quatidadeUtilizadaDoParticipante = $itemOrgao['vpiarpvuti'] == null ? moeda2float(0, 4) : moeda2float($itemOrgao['vpiarpvuti'], 4);
        }

        $sql  = "INSERT INTO SFPC.TBPARTICIPANTEITEMATARP ( ";
        $sql .= "CARPNOSEQU, CORGLICODI, CITARPSEQU, ";
        
            if ($tipoControle != 1) {
                $sql .= "APIARPQTAT, ";
            } else {
                $sql .= "VPIARPVATU, ";
            }

        $sql .= "FPIARPSITU, CUSUPOCODI, TPIARPULAT, ";

            if ($tipoControle != 1) {
                $sql .= "APIARPQTUT ";
            } else {
                $sql .= "VPIARPVUTI ";
            }

        $sql .= ") VALUES (";
        $sql .= "$ata, $codigoOrgao, $sequencial, ";
        $sql .= "$quatidadeDoParticipante, ";
        $sql .= "'" . $situacao . "', $codigoUsuario, '" . $tpiarpulat . "', ";
        $sql .= "$quatidadeUtilizadaDoParticipante) ";

        $resultado = $db->query($sql);

        return $resultado;
    }

    private function atualizarItem($db, $ata, $codigoItem, $codigoOrgao, $itemOrgao, $tipoControle) {
        $sequencial    = $codigoItem;
        $situacao      = 'A';
        $codigoUsuario = $_SESSION['_cusupocodi_'];
        $tpiarpulat = date('Y-m-d H:i:s');

        if ($tipoControle != 1) {
            $quatidadeDoParticipante = $itemOrgao['apiarpqtat'] == null ? moeda2float(0, 4)  : moeda2float($itemOrgao['apiarpqtat'], 4);
            $quatidadeUtilizadaDoParticipante = $itemOrgao['apiarpqtut'] == null ?  moeda2float(0, 4): moeda2float($itemOrgao['apiarpqtut'], 4);
        } else {
            $quatidadeDoParticipante = $itemOrgao['vpiarpvatu'] == null ? moeda2float(0, 4)  : moeda2float($itemOrgao['vpiarpvatu'], 4);
            $quatidadeUtilizadaDoParticipante = $itemOrgao['vpiarpvuti'] == null ?  moeda2float(0, 4): moeda2float($itemOrgao['vpiarpvuti'], 4);
        }

        $sql  = "UPDATE SFPC.TBPARTICIPANTEITEMATARP SET ";

            if ($tipoControle != 1) {
                $sql .= "   APIARPQTAT = " . $quatidadeDoParticipante;
                $sql .= ",  APIARPQTUT = " . $quatidadeUtilizadaDoParticipante;
            } else {
                $sql .= "   VPIARPVATU = " . $quatidadeDoParticipante;
                $sql .= ",  VPIARPVUTI = " . $quatidadeUtilizadaDoParticipante;
            }

            $sql .= ", FPIARPSITU = " . "'" . $situacao . "' ";
            $sql .= ", CUSUPOCODI = " . $codigoUsuario;
            $sql .= ", TPIARPULAT = " . "'" . $tpiarpulat . "' ";
            $sql .= "WHERE  CARPNOSEQU = " . $ata;
            $sql .= "       AND CORGLICODI = " . $codigoOrgao;
            $sql .= "       AND CITARPSEQU = " . $sequencial;

            $resultado = $db->query($sql);

            return $resultado;
    }

    /**
     *
     * @param integer $ata
     * @param integer $codigoItem
     * @param integer $codigoOrgao
     */
    public function consultarItemAta($db, $ata, $codigoItem, $codigoOrgao) {
        $sql = "SELECT  IPIA.CARPNOSEQU, IPIA.CORGLICODI, IPIA.CITARPSEQU, IPIA.APIARPQTAT, IPIA.APIARPQTUT,
                        IPIA.FPIARPSITU, IPIA.CUSUPOCODI, IPIA.TPIARPULAT
                FROM    SFPC.TBPARTICIPANTEITEMATARP IPIA
                WHERE   IPIA.CARPNOSEQU = $ata
                        AND IPIA.CITARPSEQU = $codigoItem
                        AND IPIA.CORGLICODI = " . $codigoOrgao;
        
        $resultado = executarSQL($db, $sql);
        $resultado->fetchInto($item, DB_FETCHMODE_OBJECT);

        return $item;
    }

    /**
     *
     * @param integer $ata
     * @param integer $codigoItem
     * @param integer $codigoOrgao
     */
    public function consultarParticipanteAtaOrgao($db, $ata, $codigoOrgao) {
        $sql  = "SELECT * ";
        $sql .= "FROM   SFPC.TBPARTICIPANTEATARP PA ";
        $sql .= "       INNER JOIN SFPC.TBORGAOLICITANTE O ON O.CORGLICODI = PA.CORGLICODI  ";
        $sql .= "WHERE  PA.CARPNOSEQU = " . $ata;
        $sql .= "       AND PA.CORGLICODI = " . $codigoOrgao;

        $resultado = executarSQL($db, $sql);
        $resultado->fetchInto($item, DB_FETCHMODE_OBJECT);

        return $item;
    }

    public function obterProximoNumeroItem() {
        $sql = $this->getDados()->sqlConsultarMaiorItem();
        
        $resultado = executarSQL(ClaDatabasePostgresql::getConexao(), $sql);
        $resultado->fetchInto($valorMaximo, DB_FETCHMODE_OBJECT);

        $valorAtual = intval($valorMaximo->max) + 1;

        return $valorAtual;
    }
}

// Classe RegistroPreco_Adaptacao_CadMigracaoAddParticipante
class RegistroPreco_Adaptacao_CadMigracaoAddParticipante extends Adaptacao_Abstrata {
    /**
     *
     * {@inheritdoc}
     *
     * @see Adaptacao_Abstrata::getNegocio()
     */
    public function getNegocio() {
        $this->setNegocio(new RegistroPreco_Negocio_CadMigracaoAddParticipante());
        return parent::getNegocio();
    }

    // Adaptação - Consultar itens de uma ata
    public function consultarItensAta($alicpoanop, $carpnosequ) {
        return $this->getNegocio()->consultarItensAta($alicpoanop, $carpnosequ);
    }

    // Adaptação - Consultar itens de uma ata
    public function consultarItensAtaNotIn($alicpoanop, $carpnosequ) {
        return $this->getNegocio()->consultarItensAtaNotIn($alicpoanop, $carpnosequ);
    }

    // Adaptação - Consultar itens do participante
    public function consultarItensAtaParticipante($carpnosequ) {
        return $this->getNegocio()->consultarItensAtaParticipante($carpnosequ);
    }

    // Adaptação - consultar itens do processo
    public function consultarItensProcessoParticipante($processo, $ano, $comissao, $grupo, $orgao, $fornecedor) {
        return $this->getNegocio()->consultarItensProcessoParticipante($processo, $ano, $comissao, $grupo, $orgao, $fornecedor);
    }
    
    // consultarAtaPorChave
    public function consultarAtaPorChave($processo, $ano, $orgao, $numeroAta) {
        return $this->getNegocio()->consultarAtaPorChave($processo, $ano, $orgao, $numeroAta);
    }

    // consultarAtaParticipanteChave
    public function consultarAtaParticipanteChave($numeroAta) {
        return $this->getNegocio()->consultarAtaParticipanteChave($numeroAta);
    }

    // consultarAtaParticipanteAtaOrgao
    public function consultarAtaParticipanteAtaOrgao($numeroAta, $numeroOrgao) {
        return $this->getNegocio()->consultarAtaParticipanteAtaOrgao($numeroAta, $numeroOrgao);
    }

    /**
     * Ataptação. Salvar
     *
     * @return boolean
     */
    public function salvar() {
        $entidade = $this->getNegocio()
            ->getDados()
            ->getEntidade('sfpc.tbparticipanteatarp');

        // Tabela: sfpc.tbparticipanteatarp
        // Campos:
        // - carpnosequ = Código sequencial da ata de registro de preço
        // - corglicodi = Código do órgão licitante - Participante
        // - cusupocodi = Código do Usuário Responsável pela Última Alteração
        // - tpatrpulat = Data/Hora da Última Alteração
        // - fpatrpsitu = Situação do participante (A ou null-ativo ou I-Inativo)
               
        // exemplo validacao
        if (isset($_SESSION['ata'])) {
            $entidade->carpnosequ = (int) filter_var($_SESSION['ata'], FILTER_SANITIZE_NUMBER_INT);
            if (empty($entidade->carpnosequ)) {
                $_SESSION['mensagemFeedback'] = 'Código da ata não foi informado';
                $_SESSION['conferePagina'] = 'participante';
                return;
            }
        }

        if (isset($_SESSION['_cusupocodi_'])) {
            $entidade->cusupocodi = (int) filter_var($_SESSION['_cusupocodi_'], FILTER_SANITIZE_NUMBER_INT);

            if (empty($entidade->cusupocodi)) {
                $_SESSION['mensagemFeedback'] = 'Código da ata não foi informado';
                $_SESSION['conferePagina'] = 'participante';
                return;
            }
        }
        
        return $this->getNegocio()->salvar($entidade, $_POST['itemOrgao']);
    }

    /**
     * Adaptação. Retirar Participante
     *
     * @return boolean
     */
    public function retirarParticipante() {
       return $this->getNegocio()->retirarParticipante($_POST['columnOrgao']);
    }

    public function verificarExcluirParticipante($carpnosequ, $corglicodi) {
        $orgaos = array();
        
        if(is_array($corglicodi)) {
            foreach ($corglicodi as $key => $value) {
                $orgaos[] = $key;
            }
        } else {
            $orgaos[] = $corglicodi;
        }

        $removerParticipante = $this->getNegocio()->verificarExcluirParticipante($carpnosequ, $orgaos);
        
        return $removerParticipante;
    }

    /**
     * Adaptação. Ativar Participante
     *
     * @return boolean
     */
    public function ativarParticipante() {
       if (!isset($_POST['columnOrgao'])) {
            $_SESSION['mensagemFeedback'] = 'Selecione um Órgão Participante';
            $_SESSION['conferePagina'] = 'participante';

            return false;
       }

       return $this->getNegocio()->ativarParticipante($_POST['columnOrgao']);
    }

    /**
     * Adaptação. Inativar Participante
     *
     * @return boolean
     */
    public function inativarParticipante() {
       if (!isset($_POST['columnOrgao'])) {
            $_SESSION['mensagemFeedback'] = 'Selecione um Órgão Participante';
            $_SESSION['conferePagina'] = 'participante';
            
            return false;
       }
       
       return $this->getNegocio()->inativarParticipante($_POST['columnOrgao']);
    }

    public function consultarTipoControle($carpnosequ) {
        return $this->getNegocio()->consultarTipoControle($carpnosequ);
    }
}

// Classe RegistroPreco_UI_CadMigracaoAddParticipante
class RegistroPreco_UI_CadMigracaoAddParticipante extends UI_Abstrata {
    // Tipo da ata
    private $tipo;

    // Processo licitatório da ata
    private $processo;

    // Órgão da ata
    private $orgao;

    // Ano da ata
    private $ano;

    // Fornecedor da ata
    private $fornecedor;

    // Código da ata
    private $ata;

    private $codigoComissao;

    private $codigoGrupo;

    /**
     * plotarBlocoBotao
     *
     * Define os valores dos botões
     *
     * @param Integer $ano      Ano do Processo Licitatório
     * @param Integer $orgao    Código do Órgão Licitante
     * @param Integer $processo Código do Processo Licitatório
     * @param Integer $ata      Código sequencial da ata de registro de preço
     *
     * @return void
     */
    private function plotarBlocoBotao($ano, $orgao, $processo, $ata) {
        $this->getTemplate()->VALOR_ANO_SESSAO      = $ano;
        $this->getTemplate()->VALOR_ORGAO_SESSAO    = $orgao;
        $this->getTemplate()->VALOR_PROCESSO_SESSAO = $processo;
        $this->getTemplate()->VALOR_ATA_SESSAO      = $ata;
        $this->getTemplate()->block("BLOCO_BOTAO");
    }

    private function plotarBlocoItemAta($itens, $ata, $hasAta = true) {
        global $SimboloConcatenacaoArray, $SimboloConcatenacaoDesc;
        $tipoControle = $this->getAdaptacao()->consultarTipoControle($ata->carpnosequ);
        $db = Conexao();

        if ($itens == null) {
            return;
        }
        
        $_itens[] = $itens;        

        $this->getTemplate()->TR_LAYOUT = '';

        //Colunas órgãos
        if (!empty($_SESSION['orgaos'])) {
            foreach ($_SESSION['orgaos'] as $key => $orgao) {
                if ($key != '') {
                    $this->getTemplate()->ID_ORGAO_COLUMN = $key;
                    $this->getTemplate()->NOME_ORGAO = $orgao;

                    $statusOrgao = $this->getAdaptacao()->consultarAtaParticipanteAtaOrgao($ata->carpnosequ, $key);
                    $valor       = 'ATIVO';
                    
                    if ($statusOrgao != null) {
                        if ($statusOrgao[0]->fpatrpsitu != 'A') {
                            $valor = 'INATIVO';
                        }
                    }

                    $this->getTemplate()->STATUS  = $valor;
                    
                    if ($tipoControle[0]->farpnotsal == 1) {
                        $this->getTemplate()->VALOR_COLSPAN = 4;
                        $this->getTemplate()->block("BLOCO_ORGAO_ITEM_COLUNA_TR_1");
                    } else {
                        $this->getTemplate()->VALOR_COLSPAN = 4;
                        $this->getTemplate()->block("BLOCO_ORGAO_ITEM_COLUNA_TR");
                    }

                    $this->getTemplate()->block("BLOCO_ORGAO_ITEM_COLUNA");
                }
            }
        } else {
            $this->getTemplate()->TR_LAYOUT  = '<tr></tr>';
        }

        foreach ($_itens[0] as $item) {
            // CADUM = material e CADUS = serviço
            $tipo = 'material';
            
            if (is_null($item->cmatepsequ) == true) {
                $tipo = 'servico';
            }

            // Código do item
            $valorCodigo = $item->cmatepsequ;
            
            if ($tipo == 'servico') {
                $valorCodigo = $item->cservpsequ;
            }

            // Descrição do item
            $valorDescricao = $item->ematepdesc;
            
            if ($tipo === 'servico') {
                $valorDescricao = $item->eservpdesc;
            }

            $valorDescricaoDetalhada = $item->eitarpdescmat;
            
            if ($tipo === 'servico') {
                $valorDescricaoDetalhada = $item->eitarpdescse;
            }

            // Situação do item
            $situacao = $item->cmatepsitu;
            
            if ($tipo === 'servico') {
                $situacao = $item->cservpsitu;
            }

            // Valor total
            $valorTotal = ($item->aitelpqtso * $item->vitelpvlog);
            $tipoFinal  = ($tipo == 'material') ? 'CADUM' : 'CADUS';
            $this->getTemplate()->VALOR_SEQITEM = $item->citarpsequ;
            $ordenacao = $item->aitarporde;
            $this->getTemplate()->VALOR_ORD = $ordenacao;
            $this->getTemplate()->VALOR_TIPO = $tipoFinal; // Código Sequencial do Material OU
            $this->getTemplate()->VALOR_CADUS = $valorCodigo; // Código Sequencial do Material OU Código sequencial do serviço
            $this->getTemplate()->VALOR_DESCRICAO = $valorDescricao; ; // Descrição do material ou serviço
            $this->getTemplate()->VALOR_DESCRICAO_DETALHADA = $valorDescricaoDetalhada;
            $this->getTemplate()->VALOR_UND = $item->eunidmsigl;
            $this->getTemplate()->VALOR_LOTE = $item->citarpnuml;
            $this->getTemplate()->TIPO_CONTROLE = !empty($tipoControle[0]->farpnotsal) ? $tipoControle[0]->farpnotsal : 0;

            $situacao = $item->fitarpsitu;

            if ($situacao === 'A') {
                $this->getTemplate()->VALOR_SITUACAO = 'ATIVO';
            } else {
                $this->getTemplate()->VALOR_SITUACAO = 'INATIVO';
            }

            $this->getTemplate()->QTD_PARTICIPANTE_ITEM = '';
            $this->getTemplate()->QTD_PARTICIPANTE_ITEM_UTILIZADA = '';
            $this->getTemplate()->QTD_SALDO_BLOCO = '';

            $saldoQuantidadeTotal = valorItemAta($db, $item->carpnosequ, $item->citarpsequ);
            $fiels_inc_direta = 'apiarpqtut'; // Campo para somar valor utilizado
            $fiels_solicitado = 'apiarpqtat';
            $field_scc = 'aitescqtso';
            
            if ($tipoControle[0]->farpnotsal == 1) {
                $field_scc = 'vitescunit'; // aitescqtso
                $fiels_inc_direta = 'vpiarpvuti'; // Campo para somar valor utilizado
                $fiels_solicitado = 'vpiarpvatu';
                $saldoQuantidadeTotal = valorItemAta($db, $item->carpnosequ, $item->citarpsequ, 'vitarpvatu', 'vitarpvori');
            }

            //$calculoInclusaoDireta = getQtdTotalOrgaoParticipanteInterna(Conexao(), null, $item->carpnosequ, $item->citarpsequ, $fiels_inc_direta, $field_valor_utilizado);
            //$calculoSaldoAta = 0;

            if (!empty($_SESSION['orgaos'])) {
                foreach ($_SESSION['orgaos'] as $key => $orgao) {
                    if ($key != '') {
                        if ($item->tipoItem == "ITEMPARTICIPANTE") {
                            if (isset($_SESSION['post_itens_armazenar_tela'])) {
                                $this->getTemplate()->QTD_PARTICIPANTE_ITEM = ($_SESSION['post_itens_armazenar_tela'][$item->citarpsequ][$key][$fiels_solicitado] != null) ? $_SESSION['post_itens_armazenar_tela'][$item->citarpsequ][$key][$fiels_solicitado] : '';
                                $this->getTemplate()->QTD_PARTICIPANTE_ITEM_UTILIZADA = ($_SESSION['post_itens_armazenar_tela'][$item->citarpsequ][$key][$fiels_inc_direta] != null) ? $_SESSION['post_itens_armazenar_tela'][$item->citarpsequ][$key][$fiels_inc_direta] : '';
                                $this->getTemplate()->QTD_SALDO_BLOCO = ($_SESSION['post_itens_armazenar_tela'][$item->citarpsequ][$key][$fiels_solicitado] != null && $_SESSION['post_itens_armazenar_tela'][$item->citarpsequ][$key][$fiels_inc_direta] != null) ? converte_valor_estoques($_SESSION['post_itens_armazenar_tela'][$item->citarpsequ][$key][$fiels_solicitado] - $_SESSION['post_itens_armazenar_tela'][$item->citarpsequ][$key][$fiels_inc_direta]) : '';
                            } else {
                                $this->getTemplate()->QTD_PARTICIPANTE_ITEM = '';
                                $this->getTemplate()->QTD_PARTICIPANTE_ITEM_UTILIZADA = '';
                                $this->getTemplate()->QTD_SALDO_BLOCO = '';
                            }

                            foreach ($item->tipoItemValores as $keyConstrucao => $value) {
                                foreach ($value as $keyOrgaoInteno => $valueOrgao) {
                                    $chaveVerificacao = null;

                                    if (is_int($keyOrgaoInteno)) {
                                        $chaveVerificacao = $keyOrgaoInteno;
                                    } else {
                                        $chaveVerificacao = $keyConstrucao;
                                    }

                                    if ($chaveVerificacao == $key) {
                                        if (is_int($keyOrgaoInteno)) {
                                            if (isset($_SESSION['post_itens_armazenar_tela'])) {
                                                $valorParticipanteItem = ($_SESSION['post_itens_armazenar_tela'][$item->citarpsequ][$key][$fiels_solicitado] != null) ? $_SESSION['post_itens_armazenar_tela'][$item->citarpsequ][$key][$fiels_solicitado] : '';
                                                $valorParticipanteItemUt = ($_SESSION['post_itens_armazenar_tela'][$item->citarpsequ][$key][$fiels_inc_direta] != null) ? $_SESSION['post_itens_armazenar_tela'][$item->citarpsequ][$key][$fiels_inc_direta] : '';
                                                $this->getTemplate()->QTD_PARTICIPANTE_ITEM = $valorParticipanteItem;
                                                $this->getTemplate()->QTD_PARTICIPANTE_ITEM_UTILIZADA = $valorParticipanteItemUt;
                                                $this->getTemplate()->QTD_SALDO_BLOCO = ($_SESSION['post_itens_armazenar_tela'][$item->citarpsequ][$key][$fiels_solicitado] != null && $_SESSION['post_itens_armazenar_tela'][$item->citarpsequ][$key][$fiels_inc_direta] != null) ? converte_valor_estoques(moeda2float($_SESSION['post_itens_armazenar_tela'][$item->citarpsequ][$key][$fiels_solicitado]) - moeda2float($_SESSION['post_itens_armazenar_tela'][$item->citarpsequ][$key][$fiels_inc_direta])) : '';
                                                $valorParticipanteItem = str_replace(',', '.', str_replace('.', '', $valorParticipanteItem));
                                                $valorParticipanteItemUt = str_replace(',', '.', str_replace('.', '', $valorParticipanteItemUt));
                                            } else {
                                                $valorParticipanteItem = $valueOrgao[$fiels_solicitado];
                                                $valorParticipanteItemUt = $valueOrgao[$fiels_inc_direta];
                                                $this->getTemplate()->QTD_PARTICIPANTE_ITEM = converte_valor_estoques($valorParticipanteItem);
                                                $this->getTemplate()->QTD_PARTICIPANTE_ITEM_UTILIZADA = converte_valor_estoques($valorParticipanteItemUt);
                                                $this->getTemplate()->QTD_SALDO_BLOCO = converte_valor_estoques($valueOrgao[$fiels_solicitado] - $valueOrgao[$fiels_inc_direta]);
                                            }
                                        } else {
                                            if (isset($_SESSION['post_itens_armazenar_tela'])) {
                                                $valorParticipanteItem = ($_SESSION['post_itens_armazenar_tela'][$item->citarpsequ][$key][$fiels_solicitado] != null) ? $_SESSION['post_itens_armazenar_tela'][$item->citarpsequ][$key][$fiels_solicitado] : '';
                                                $valorParticipanteItemUt = ($_SESSION['post_itens_armazenar_tela'][$item->citarpsequ][$key][$fiels_inc_direta] != null) ? $_SESSION['post_itens_armazenar_tela'][$item->citarpsequ][$key][$fiels_inc_direta] : '';
                                                $this->getTemplate()->QTD_PARTICIPANTE_ITEM = $valorParticipanteItem;
                                                $this->getTemplate()->QTD_PARTICIPANTE_ITEM_UTILIZADA = $valorParticipanteItemUt;
                                                $this->getTemplate()->QTD_SALDO_BLOCO = ($_SESSION['post_itens_armazenar_tela'][$item->citarpsequ][$key][$fiels_solicitado] != null && $_SESSION['post_itens_armazenar_tela'][$item->citarpsequ][$key][$fiels_inc_direta] != null) ? converte_valor_estoques(moeda2float($_SESSION['post_itens_armazenar_tela'][$item->citarpsequ][$key][$fiels_solicitado]) - moeda2float($_SESSION['post_itens_armazenar_tela'][$item->citarpsequ][$key][$fiels_inc_direta])) : '';
                                                $valorParticipanteItem = str_replace(',', '.', str_replace('.', '', $valorParticipanteItem));
                                                $valorParticipanteItemUt = str_replace(',', '.', str_replace('.', '', $valorParticipanteItemUt));
                                            } else {
                                                $valorParticipanteItem = $value[$fiels_solicitado];
                                                $valorParticipanteItemUt = $value[$fiels_inc_direta];
                                                $this->getTemplate()->QTD_PARTICIPANTE_ITEM = converte_valor_estoques($valorParticipanteItem);
                                                $this->getTemplate()->QTD_PARTICIPANTE_ITEM_UTILIZADA = converte_valor_estoques($valorParticipanteItemUt);
                                                $this->getTemplate()->QTD_SALDO_BLOCO = converte_valor_estoques($value[$fiels_solicitado] - $value[$fiels_inc_direta]);
                                            }
                                        }

                                        $scc = utilizadoParticipanteAtaAtualScc(Conexao(), $item->carpnosequ, $item->citarpsequ, $field_scc, $keyOrgaoInteno);
                                        
                                        $this->getTemplate()->VALOR_SCC = converte_valor_estoques($scc);
                                        $this->getTemplate()->QTD_SALDO_BLOCO = converte_valor_estoques($valorParticipanteItem - ($valorParticipanteItemUt + $scc));
                                    } else {
                                        continue;
                                    }
                                }
                            }
                        } else {
                            if (isset($_SESSION['post_itens_armazenar_tela'])) {
                                $this->getTemplate()->QTD_PARTICIPANTE_ITEM = ($_SESSION['post_itens_armazenar_tela'][$item->citarpsequ][$key][$fiels_solicitado] != null) ? $_SESSION['post_itens_armazenar_tela'][$item->citarpsequ][$key][$fiels_solicitado] : '';
                                $this->getTemplate()->QTD_PARTICIPANTE_ITEM_UTILIZADA = ($_SESSION['post_itens_armazenar_tela'][$item->citarpsequ][$key][$fiels_inc_direta] != null) ? $_SESSION['post_itens_armazenar_tela'][$item->citarpsequ][$key][$fiels_inc_direta] : '';
                                $this->getTemplate()->QTD_SALDO_BLOCO = ($_SESSION['post_itens_armazenar_tela'][$item->citarpsequ][$key][$fiels_solicitado] != null && $_SESSION['post_itens_armazenar_tela'][$item->citarpsequ][$key][$fiels_inc_direta] != null) ? converte_valor_estoques($_SESSION['post_itens_armazenar_tela'][$item->citarpsequ][$key][$fiels_solicitado] - $_SESSION['post_itens_armazenar_tela'][$item->citarpsequ][$key][$fiels_inc_direta]) : '';
                            }
                        }

                        $descricaoLinhaTd = "Lote: " . $item->citarpnuml;
                        $descricaoLinhaTd .= "; Ordem: " . $ordenacao;
                        $descricaoLinhaTd .= "; Tipo: " . $tipo;
                        $descricaoLinhaTd .= "; Cod reduzido: " . $valorCodigo;
                        $descricaoLinhaTd .= "; Descrição: " . $valorDescricao;
                        $descricaoLinhaTd .= "; Descrição detalhada: " . $valorDescricaoDetalhada;

                        $this->getTemplate()->DESCRICAO_LINHA_TD = $descricaoLinhaTd;
                        $this->getTemplate()->ID_ORGAO = $key;
                        
                        if ($tipoControle[0]->farpnotsal == 1) {
                            $this->getTemplate()->block("BLOCO_ORGAO_ITEM_TD_1");
                        } else {
                            $this->getTemplate()->block("BLOCO_ORGAO_ITEM_TD");
                        }
                    }
                }
            }

            $this->getTemplate()->VALOR_QTD_TOTAL = converte_valor_estoques($saldoQuantidadeTotal);
            $totalAtaAtualIncDireta = utilizadoParticipanteAtaAtual($db, $item->carpnosequ, $item->citarpsequ, $fiels_inc_direta);
            $totalAtaAtualScc = utilizadoParticipanteAtaAtualScc($db, $item->carpnosequ, $item->citarpsequ, $field_scc);
            $quantidadeUtilizadoTotal = $totalAtaAtualIncDireta + $totalAtaAtualScc;
            
            $this->getTemplate()->VALOR_INCLUSAO_DIRETA = converte_valor_estoques($totalAtaAtualIncDireta);
            
            $descricaoLinha  = "Lote: " . $item->citarpnuml;
            $descricaoLinha .= "; Ordem: " . $ordenacao;
            $descricaoLinha .= "; Tipo: " . $tipo;
            $descricaoLinha .= "; Cod reduzido: " . $valorCodigo;
            $descricaoLinha .= "; Descrição: " . $valorDescricao;
            $descricaoLinha .= "; Descrição detalhada: " . $valorDescricaoDetalhada;
            
            $this->getTemplate()->DESCRICAO_LINHA = $descricaoLinha;
            $this->getTemplate()->VALOR_TOTAL_SCC = converte_valor_estoques($totalAtaAtualScc);
            $this->getTemplate()->VALOR_TOTAL = converte_valor_estoques($quantidadeUtilizadoTotal);
            $this->getTemplate()->SALDO = converte_valor_estoques($saldoQuantidadeTotal - $quantidadeUtilizadoTotal);
            $this->getTemplate()->block("BLOCO_ITEM");
            $this->getTemplate()->block("BLOCO_RESULTADO_ATAS");
            $this->getTemplate()->block("BLOCO_ITEM_TOTAL");
        }
    }

    public function valorAtaMontado($orgao, $ata) {
        $consultarfor = new RegistroPreco_Dados_CadMigracaoAddParticipante();
        $dto = $consultarfor->consultarDCentroDeCustoUsuario($ata->cgrempcodi, $ata->cusupocodi, $orgao);
        $objeto = current($dto);
        $numeroAtaFormatado = $objeto->ccenpocorg . str_pad($objeto->ccenpounid, 2, '0', STR_PAD_LEFT);
      // madson o ano buscado era o de processo da ata ->  $numeroAtaFormatado .= "." . str_pad($ata->carpincodn, 4, "0", STR_PAD_LEFT) . "/" . $ata->alicpoanop;
         $numeroAtaFormatado .= "." . str_pad($ata->carpincodn, 4, "0", STR_PAD_LEFT) . "/" . $ata->aarpinanon;
        return $numeroAtaFormatado;
    }

    /**
     *
     * @return boolean
     */
    private function validarOrgao() {
        $this->orgao = isset($_GET['orgao']) ? filter_var($_GET['orgao'], FILTER_SANITIZE_NUMBER_INT) : null;
        
        $_SESSION['orgao'] = ($this->orgao != null) ? $this->orgao : $_SESSION['orgao'];        
        
        $this->orgao = $_SESSION['orgao'];
        
        if (! filter_var($this->orgao, FILTER_VALIDATE_INT)) {
            $_SESSION['mensagemFeedback'] = 'Órgão não foi informado';
            $_SESSION['conferePagina'] = 'participante';
            return false;
        }
        return true;
    }

    /**
     *
     * @return boolean
     */
    private function validarAno() {
        $this->ano = isset($_GET['ano']) ? filter_var($_GET['ano'], FILTER_SANITIZE_NUMBER_INT) : null;
        
        $_SESSION['ano'] = ($this->ano != null) ? $this->ano : $_SESSION['ano'];        
        
        $this->ano = $_SESSION['ano'];
        
        if (! filter_var($this->ano, FILTER_VALIDATE_INT)) {
            $_SESSION['mensagemFeedback'] = 'Ano não foi informado';
            $_SESSION['conferePagina'] = 'participante';
            return false;
        }

        return true;
    }

    /**
     *
     * @return boolean
     */
    private function validarProcesso() {
        $this->processo = isset($_GET['processo']) ? filter_var($_GET['processo'], FILTER_SANITIZE_NUMBER_INT) : null;
        
        $_SESSION['processo'] = ($this->processo != null) ? $this->processo : $_SESSION['processo'];
        
        $this->processo = $_SESSION['processo'];        
        
        if (! filter_var((int)$this->processo, FILTER_VALIDATE_INT)) {
            $_SESSION['mensagemFeedback'] = 'Processo não foi informado';
            $_SESSION['conferePagina'] = 'participante';
            return false;
        }
                
        $this->codigoGrupo = isset($_SESSION['grupocodigo']) ? filter_var($_SESSION['grupocodigo'], FILTER_SANITIZE_NUMBER_INT) : null;
        
        $_SESSION['grupocodigo'] = ($this->codigoGrupo != null) ? $this->codigoGrupo : $_SESSION['grupocodigo'];
        
        $this->codigoGrupo = $_SESSION['grupocodigo'];        
        
        if (! filter_var((int) $this->codigoGrupo, FILTER_VALIDATE_INT)) {
            $_SESSION['mensagemFeedback'] = 'Grupo não foi informado';
            $_SESSION['conferePagina'] = 'participante';
            return false;
        }

        return true;
    }

    /**
     *
     * @return boolean
     */
    private function validarTipo() {          
        $this->tipo = isset($_GET['tipo']) ? filter_var($_GET['tipo'], FILTER_SANITIZE_STRING) : null;
        
        $_SESSION['tipo'] = ($this->tipo != null) ? $this->tipo : $_SESSION['tipo'];
        
        $this->tipo = $_SESSION['tipo'];        

        if (! $this->tipo) {
            $_SESSION['mensagemFeedback'] = 'Tipo não foi informado';
            $_SESSION['conferePagina'] = 'participante';
            return false;
        }

        return true;
    }

    /**
     *
     * @return boolean
     */
    private function validarFornecedor() {
        $this->fornecedor = isset($_GET['fornecedor']) ? filter_var($_GET['fornecedor'], FILTER_SANITIZE_NUMBER_INT) : null;
        
        $_SESSION['fornecedor'] = ($this->fornecedor != null) ? $this->fornecedor : $_SESSION['fornecedor'];
        
        $this->fornecedor = $_SESSION['fornecedor'];              
        
        if (! $this->fornecedor) {
            $_SESSION['mensagemFeedback'] = 'Fornecedor não foi informado';
            $_SESSION['conferePagina'] = 'participante';
            return false;
        }
        
        return true;
    }

    /**
     *
     * @return boolean
     */
    private function validarAta() {
        $this->ata = isset($_GET['ata']) ? filter_var($_GET['ata'], FILTER_SANITIZE_NUMBER_INT) : null;
        
        $_SESSION['ata'] = ($this->ata != null) ? $this->ata : $_SESSION['ata'];
        
        $this->ata = $_SESSION['ata'];       
        
        if (! $this->ata) {
            $_SESSION['mensagemFeedback'] = 'Ata não foi informado';
            $_SESSION['conferePagina'] = 'participante';
            return false;
        }
        
        return true;
    }

    /**
     *
     * @return boolean
     */
    private function validarComissao() {
        $this->comissao = isset($_GET['comissaocodigo']) ? filter_var($_GET['comissaocodigo'], FILTER_SANITIZE_NUMBER_INT) : null;
        
        $_SESSION['comissaocodigo'] = ($this->comissao != null) ? $this->comissao : $_SESSION['comissaocodigo'];
        
        $this->comissao = $_SESSION['comissaocodigo'];
        
        if (! $this->comissao) {
            return false;
        }
        
        return true;
    }

    /**
     *
     * @return boolean
     */
    private function validarGrupo() {
        $this->grupo = isset($_GET['grupo']) ? filter_var($_GET['grupo'], FILTER_SANITIZE_NUMBER_INT) : null; 
        
        $_SESSION['grupo'] = ($this->grupo != null) ? $this->grupo : $_SESSION['grupo'];
        
        $this->grupo = $_SESSION['grupo'];       
        
        if (! $this->grupo) {
            $_SESSION['mensagemFeedback'] = 'Grupo não foi informado';
            $_SESSION['conferePagina'] = 'participante';
            return false;
        }

        return true;
    }

    /**
     * getParametros
     *
     * Define os parêmtros do programa
     */
    private function getParametros() {
        $this->validarAta();
        
        if (!$this->validarOrgao() || !$this->validarAno() || !$this->validarProcesso() || !$this->validarTipo() || !$this->validarFornecedor() || !$this->validarComissao() || !$this->validarGrupo()) {
            return false;
        }
    }  

    public function __construct() {
        $template = new TemplateNovaJanela("templates/CadManterEspecialParticipante.html", "Registro de Preço > Migração > Adicionar Participantes", true);
        $template->NOMEPROGRAMA = 'CadManterEspecialParticipante';
        $template->TITULO_PAGINA = 'MANTER ESPECIAL - ATA INTERNA - PARTICIPANTES';
        
        $this->setTemplate($template);
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see UI_Abstrata::getAdaptacao()
     */
    public function getAdaptacao() {
        $this->setAdaptacao(new RegistroPreco_Adaptacao_CadMigracaoAddParticipante());
        
        return parent::getAdaptacao();
    }

    public function processVoltar() {        
        $uri = "CadManterEspecial.php?tipo=".$_SESSION['tipo']."&ano=".$_SESSION['ano']."&processo=".$_SESSION['processo']."&orgao=".$_SESSION['orgao']."&fornecedor=".$_SESSION['fornecedor']."&comissaocodigo=".$_SESSION['processo']."&grupocodigo=".$_SESSION['grupocodigo'];        
        
        header('location: ' . $uri);
    }


    /**
     * Processo principal
     *
     * Processo inicial para montar os dados do programa
     *
     * @return void
     */
    public function proccessPrincipal() {
        if ($_SESSION['mensagemFeedback'] != null) {
            $tipoMsg = 0;

            if (isset($_SESSION['mensagemFeedbackTipo'])) {
                if ($_SESSION['mensagemFeedbackTipo'] == 1) {
                    $tipoMsg = 1;
                }
            }

            $this->mensagemSistema($_SESSION['mensagemFeedback'], $tipoMsg, $tipoMsg);
        }

        unset($_SESSION['mensagemFeedback']);

        // Define os parâmetros do sistema
        $this->getParametros();

        $itensAta = null;

        // Consulta a ata
        if (isset($this->ata)) {
            $ata = $this->getAdaptacao()->consultarAtaPorChave($this->processo, $this->ano, $this->orgao, $this->ata);

            $this->plotarBlocoLicitacao($licitacao, $ata, null, null);

            $itensAta = $this->getAdaptacao()->consultarItensAtaParticipante($ata->carpnosequ);

            if ($itensAta == null || $itensAta == '') {
                $itensAta = $this->getAdaptacao()->consultarItensAta($this->ano,$ata->carpnosequ);
            } else {
                $itensAdicionadosAposCriarParticipantes = $this->getAdaptacao()->consultarItensAtaNotIn($this->ano,$ata->carpnosequ);

                foreach ($itensAdicionadosAposCriarParticipantes as $key => $itemParaAdicionar) {
                    array_push($itensAta, $itemParaAdicionar);
                }

                $ataParticipante = $this->getAdaptacao()->consultarAtaParticipanteChave($ata);

                foreach ($ataParticipante as $orgao) {
                    $_SESSION['orgaos'][$orgao->corglicodi] = $orgao->eorglidesc;
                }
            }
        }

        $this->plotarBlocoItemAta($itensAta, $ata, isset($this->ata));
    }

    /**
     *
     * @param stdClass $licitacao
     * @param stdClass $ata
     * @param unknown $dataInformada
     * @param unknown $vigenciaInformada
     */
    private function plotarBlocoLicitacao($licitacao, $ata, $dataInformada, $vigenciaInformada) {
        $numeroAtaMontado = $this->valorAtaMontado($this->orgao, $ata);
        $tipoControle = $statusOrgao = $this->getAdaptacao()->consultarTipoControle($ata->carpnosequ);

        if ($tipoControle[0]->farpnotsal == 1) {
            $this->getTemplate()->block("BLOCO_ITEM_COLUNA_TR_1");
        } else {
            $this->getTemplate()->block("BLOCO_ITEM_COLUNA_TR");
        }

        $this->getTemplate()->NUM_ATA = $numeroAtaMontado;
        $this->getTemplate()->block("BLOCO_LICITACAO");
    }

    /**
     * UI. Salvar.
     *
     * @return void
     */
    public function salvar() {
        if (!$this->getAdaptacao()->salvar()) {
            $this->proccessPrincipal();
            
            return;
        }

        $_SESSION['mensagemFeedbackTipo'] = 1;
        $_SESSION['mensagemFeedback']     = 'Dados salvos com sucesso';
        $_SESSION['conferePagina'] = 'participante';

        $uri = "CadManterEspecialParticipante.php?tipo=".$_SESSION['tipo']."&ano=".$_SESSION['ano']."&processo=".$_SESSION['processo']."&orgao=".$_SESSION['orgao']."&fornecedor=".$_SESSION['fornecedor']."&comissaocodigo=".$_SESSION['processo']."&grupocodigo=".$_SESSION['grupocodigo'];
        
        header('location: ' . $uri);
        exit();
    }

    /**
     * UI. Salvar.
     *
     * @return void
     */
    public function retirarParticipante() {
        // Verificar se selecionou o órgão
        
        if (!isset($_POST['columnOrgao'])) {
            $_SESSION['mensagemFeedbackTipo'] = 0;
            $_SESSION['mensagemFeedback']     = 'Selecione um Órgão Participante';
            $_SESSION['conferePagina'] = 'participante';

            $uri = "CadManterEspecialParticipante.php?tipo=".$_SESSION['tipo']."&ano=".$_SESSION['ano']."&processo=".$_SESSION['processo']."&orgao=".$_SESSION['orgao']."&fornecedor=".$_SESSION['fornecedor']."&comissaocodigo=".$_SESSION['processo']."&grupocodigo=".$_SESSION['grupocodigo'];        
            
            header('location: ' . $uri);
            exit();
        }

        // Verificar SCC antes de remover o participante
        $verificarRemover = $this->getAdaptacao()->verificarExcluirParticipante($_SESSION['ata'], $_POST['columnOrgao']);
        
        if ($verificarRemover->count > 0) {
            $_SESSION['mensagemFeedbackTipo'] = 0;
            $_SESSION['mensagemFeedback']     = 'Este órgão possui SCC cadastrada e não pode ser retirado'; 
            $_SESSION['conferePagina'] = 'participante';

            $uri = "CadManterEspecialParticipante.php?tipo=".$_SESSION['tipo']."&ano=".$_SESSION['ano']."&processo=".$_SESSION['processo']."&orgao=".$_SESSION['orgao']."&fornecedor=".$_SESSION['fornecedor']."&comissaocodigo=".$_SESSION['processo']."&grupocodigo=".$_SESSION['grupocodigo'];

            header('location: ' . $uri);
            exit();
        }

        // Remover participante
        if (!$this->getAdaptacao()->retirarParticipante()) {
            $_SESSION['mensagemFeedbackTipo'] = 0;
            $_SESSION['mensagemFeedback']     = 'Erro ao remover participante';
            $_SESSION['conferePagina'] = 'participante';
        }

        $_SESSION['mensagemFeedbackTipo'] = 1;
        $_SESSION['mensagemFeedback']     = 'Participante removido com sucesso';
        $_SESSION['conferePagina'] = 'participante';

        //$this->proccessPrincipal();
        $uri = "CadManterEspecialParticipante.php?tipo=".$_SESSION['tipo']."&ano=".$_SESSION['ano']."&processo=".$_SESSION['processo']."&orgao=".$_SESSION['orgao']."&fornecedor=".$_SESSION['fornecedor']."&comissaocodigo=".$_SESSION['processo']."&grupocodigo=".$_SESSION['grupocodigo'];
        
        header('location: ' . $uri);
        exit();
    }

    /**
     * UI. Salvar.
     *
     * @return void
     */
    public function ativarParticipante() {
        if (!$this->getAdaptacao()->ativarParticipante()) {
            $_SESSION['mensagemFeedbackTipo'] = 0;
            $_SESSION['mensagemFeedback']     = 'Selecione um Órgão Participante';
            $_SESSION['conferePagina'] = 'participante';

            $uri = "CadManterEspecialParticipante.php?tipo=".$_SESSION['tipo']."&ano=".$_SESSION['ano']."&processo=".$_SESSION['processo']."&orgao=".$_SESSION['orgao']."&fornecedor=".$_SESSION['fornecedor']."&comissaocodigo=".$_SESSION['processo']."&grupocodigo=".$_SESSION['grupocodigo'];
            
            header('location: ' . $uri);
            exit();
        }

        $_SESSION['mensagemFeedbackTipo'] = 1;
        $_SESSION['mensagemFeedback']     = 'Alteração da Situação do Órgão Participante executada com sucesso';
        $_SESSION['conferePagina'] = 'participante';
        
        $uri = "CadManterEspecialParticipante.php?tipo=".$_SESSION['tipo']."&ano=".$_SESSION['ano']."&processo=".$_SESSION['processo']."&orgao=".$_SESSION['orgao']."&fornecedor=".$_SESSION['fornecedor']."&comissaocodigo=".$_SESSION['processo']."&grupocodigo=".$_SESSION['grupocodigo'];        
        
        header('location: ' . $uri);
        exit();
    }

    /**
     * UI. Salvar.
     *
     * @return void
     */
    public function inativarParticipante() {
        if (!$this->getAdaptacao()->inativarParticipante()) {
            $_SESSION['mensagemFeedbackTipo'] = 0;
            $_SESSION['mensagemFeedback']     = 'Selecione um Órgão Participante';
            $_SESSION['conferePagina'] = 'participante';

            $uri = "CadManterEspecialParticipante.php?tipo=".$_SESSION['tipo']."&ano=".$_SESSION['ano']."&processo=".$_SESSION['processo']."&orgao=".$_SESSION['orgao']."&fornecedor=".$_SESSION['fornecedor']."&comissaocodigo=".$_SESSION['processo']."&grupocodigo=".$_SESSION['grupocodigo'];        
            
            header('location: ' . $uri);
            exit();
        }

        $_SESSION['mensagemFeedbackTipo'] = 1;
        $_SESSION['mensagemFeedback']     = 'Alteração da Situação do Órgão Participante executada com sucesso';
        $_SESSION['conferePagina'] = 'participante';

        $uri = "CadManterEspecialParticipante.php?tipo=".$_SESSION['tipo']."&ano=".$_SESSION['ano']."&processo=".$_SESSION['processo']."&orgao=".$_SESSION['orgao']."&fornecedor=".$_SESSION['fornecedor']."&comissaocodigo=".$_SESSION['processo']."&grupocodigo=".$_SESSION['grupocodigo'];        
        
        header('location: ' . $uri);
        exit();
    }
}

$programa = new RegistroPreco_UI_CadMigracaoAddParticipante();
$botao    = isset($_POST['Botao']) ? $_POST['Botao'] : 'Principal';

switch ($botao) {
    case 'Voltar':
        $programa->processVoltar();
        break;
    case 'RetirarParticipante':
        $_SESSION['post_itens_armazenar_tela'] = $_POST['itemOrgao'];
        $programa->retirarParticipante();
        $programa->proccessPrincipal();
        break;
    case 'AtivarParticipante':
        $_SESSION['post_itens_armazenar_tela'] = $_POST['itemOrgao'];
        $programa->ativarParticipante();
        $programa->proccessPrincipal();
        break;
    case 'InativarParticipante':
        $_SESSION['post_itens_armazenar_tela'] = $_POST['itemOrgao'];
        $programa->inativarParticipante();
        $programa->proccessPrincipal();
        break;
    case 'Salvar':
        $programa->salvar();
        //$programa->proccessPrincipal();
        break;
    case 'RetirarItem':
        $_SESSION['post_itens_armazenar_tela'] = $_POST['itemOrgao'];
        $programa->retirarItem();
        $programa->proccessPrincipal();
        break;
    case 'RetirarDocumento':
    $_SESSION['post_itens_armazenar_tela'] = $_POST['itemOrgao'];
        $programa->RetirarDocumento();
        $programa->proccessPrincipal();
        break;
    case 'Inserir':
        $_SESSION['post_itens_armazenar_tela'] = $_POST['itemOrgao'];
        $programa->adicionarDocumento();
    case 'Principal':
    default:
        $programa->proccessPrincipal();
        break;
}

echo $programa->getTemplate()->show();

if (isset($_SESSION['post_itens_armazenar_tela'])) {
    unset($_SESSION['post_itens_armazenar_tela']);
}
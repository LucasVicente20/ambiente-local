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
 * -----------------------------------------------------------------------------------------------------------
 * Tarefas Redmine relacionadas: 191609, 187493, 187036, 186982, 186347, 186075, 184450, 73674
 * -----------------------------------------------------------------------------------------------------------
 * Alterado: Pitang Agile TI - Caio Coutinho - e Lucas Baracho
 * Data:     01/06/2018
 * Objetivo: Tarefa Redmine 194539
 * -----------------------------------------------------------------------------------------------------------
 * Alterado: Pitang Agile TI - Caio Coutinho
 * Data:     01/09/2018
 * Objetivo: Tarefa Redmine 202692
 * -----------------------------------------------------------------------------------------------------------
 * Alterado: Pitang Agile TI - Caio Coutinho
 * Data:     17/09/2018
 * Objetivo: Tarefa Redmine 201676
 * -----------------------------------------------------------------------------------------------------------
 * Alterado: Pitang Agile TI - Caio Coutinho
 * Data:     30/10/2018
 * Objetivo: Tarefa Redmine 206033
 * -----------------------------------------------------------------------------------------------------------
 * Alterado: Pitang Agile TI - Caio Coutinho
 * Data:     08/11/2018
 * Objetivo: Tarefa Redmine 205436
 * -----------------------------------------------------------------------------------------------------------
 * Alterado: Pitang Agile TI - Caio Coutinho
 * Data:     20/11/2018
 * Objetivo: Tarefa Redmine 206842
 * -----------------------------------------------------------------------------------------------------------
 * Alterado: Pitang Agile TI - Caio Coutinho
 * Data:     03/12/2018
 * Objetivo: Tarefa Redmine 207361
 * -----------------------------------------------------------------------------------------------------------
 * Alterado: Pitang Agile TI - Caio Coutinho
 * Data:     18/03/2019
 * Objetivo: Tarefa Redmine 212703
 * -----------------------------------------------------------------------------------------------------------
 * Alterado: Lucas Baracho
 * Data:     28/06/2019
 * Objetivo: Tarefa Redmine 219631
 * -----------------------------------------------------------------------------------------------------------
 * Alterado: Lucas Baracho
 * Data:     08/07/2019
 * Objetivo: Tarefa Redmine 220038
 * -----------------------------------------------------------------------------------------------------------
 * Alterado: João Madson
 * Data:     12/09/2019
 * Objetivo: Tarefa Redmine 220038
 * -----------------------------------------------------------------------------------------------------------
 * Alterado: João Madson
 * Data:     06/12/2019
 * Objetivo: Tarefa Redmine 227471
 * -----------------------------------------------------------------------------------------------------------
 * Alterado: Osmar Celestino
 * Data:     08/09/2021
 * Objetivo: Tarefa Redmine #252088
 * -----------------------------------------------------------------------------------------------------------
 * Alterado: Osmar Celestino
 * Data:     01/02/2023
 * Objetivo: Tarefa Redmine #278435
 * -----------------------------------------------------------------------------------------------------------
 * Alterado: Lucas Vicente e Lucas André
 * Data:     02/05/2023
 * Objetivo: Tarefa Redmine #282321
 * -----------------------------------------------------------------------------------------------------------
 * Alterado: Osmar Celestino
 * Data:     08/05/2023
 * Objetivo: Tarefa Redmine #282321
 * -----------------------------------------------------------------------------------------------------------
 * Alterado: Osmar Celestino
 * Data:     23/05/2023
 * Objetivo: Tarefa Redmine #283603
 * -----------------------------------------------------------------------------------------------------------
 */


 // Colocar validação para só retirar item da ata se não tiver ligado a uma SCC

if (! @require_once dirname(__FILE__) . "/../bootstrap.php") {
    throw new Exception("Error Processing Request - Bootstrap", 1);
}
header('X-XSS-Protection: 0');
Seguranca();

global $SimboloConcatenacaoArray, $SimboloConcatenacaoDesc;

/**
 * Classe RegistroPreco_Dados_CadMigracaoAtaInternaAlterar
 */
class RegistroPreco_Dados_CadMigracaoAtaInternaAlterar extends Dados_Abstrata
{

    private function sqlSelectOrgaosPorItem($sequencialAta, $sequencialItemAta)
    {
        $sql = "SELECT
				    ol.eorglidesc as descricao,
				    piarp.apiarpqtat as quantidade
				FROM
				    sfpc.tbparticipanteitematarp piarp
				    INNER JOIN sfpc.tborgaolicitante ol
				    	ON piarp.corglicodi = ol.corglicodi
				    INNER JOIN sfpc.tbataregistroprecointerna arpi
				    	ON piarp.carpnosequ = arpi.carpnosequ
				WHERE
				    piarp.carpnosequ = %d
				    AND piarp.citarpsequ = %d";

        return sprintf($sql, $sequencialAta, $sequencialItemAta);
    }

    private function sqlLicitacaoAtaInterna($ano, $processo, $orgaoUsuario, $codigoComissao, $codigoGrupo = null)
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
        $sql .= " and l.ccomlicodi =". $codigoComissao;
        
        if(!is_null($codigoGrupo)) {
            $sql .= " and l.cgrempcodi =". $codigoGrupo;
        }
        return $sql;
    }


    /**
     * sqlAtaPorchave
     *
     *
     *
     */
    private function sqlAtaPorchave($processo, $orgao, $ano, $chaveAta)
    {
        

        $sql  = "SELECT a.carpincodn, a.earpinobje, a.aarpinanon, a.aarpinpzvg, a.tarpindini, a.cgrempcodi, a.cusupocodi, f.nforcrrazs, d.edoclinome,";
        $sql .= " a.corglicodi, a.carpnosequ, a.alicpoanop, s.csolcosequ, a.aarpinanon, carpnoseq1, n.farpnotsal, a.farpinsitu, a.farpincorp, ";
        $sql .= " f.nforcrrazs, f.aforcrccgc, f.aforcrccpf, f.eforcrlogr, f.aforcrnume, f.eforcrbair, f.nforcrcida, f.cforcresta, ";
        $sql .= " fa.nforcrrazs as razaoFornecedorAtual, fa.aforcrccgc as cgcFornecedorAtual, fa.aforcrccpf as cpfFornecedorAtual, fa.eforcrlogr as logradouroFornecedorAtual, ";
        $sql .= " fa.aforcrnume as numeroEnderecoFornecedorAtual, fa.eforcrbair as bairroFornecedorAtual, fa.nforcrcida as cidadeFornecedorAtual, fa.cforcresta as estadoFornecedorAtual ";

        $sql .= " FROM sfpc.tbataregistroprecointerna a";

        $sql .= " LEFT JOIN sfpc.tbataregistropreconova n";
        $sql .= " ON n.carpnosequ = a.carpnosequ ";

        $sql .= " LEFT OUTER JOIN sfpc.tbsolicitacaolicitacaoportal s";
        $sql .= " ON (s.clicpoproc = a.clicpoproc";
        $sql .= " AND s.alicpoanop = a.alicpoanop";
        $sql .= " AND s.ccomlicodi = a.ccomlicodi";
        $sql .= " AND s.corglicodi = a.corglicodi)";

        $sql .= " LEFT OUTER JOIN sfpc.tbfornecedorcredenciado f";
        $sql .= " ON f.aforcrsequ = a.aforcrsequ";

        $sql .= " LEFT OUTER JOIN sfpc.tbfornecedorcredenciado fa";
        $sql .= " ON fa.aforcrsequ = (SELECT afa.aforcrsequ from sfpc.tbataregistroprecointerna afa WHERE afa.carpnosequ = a.carpnoseq1)";

        $sql .= " LEFT OUTER JOIN sfpc.tbdocumentolicitacao d";
        $sql .= " ON d.clicpoproc = a.clicpoproc";
        $sql .= " AND d.clicpoproc = " . $processo;
        $sql .= " AND d.corglicodi = " . $orgao;
        $sql .= " AND d.alicpoanop = " . $ano;

        $sql .= " WHERE a.carpnosequ = " . $chaveAta;

        return $sql;
    }


    private function sqlConsultarDadosFornecedorProcesso($fornecedor)
    {
    
        $sql  = "select  f.nforcrrazs, f.aforcrccgc, f.aforcrccpf, f.eforcrlogr, ";
        $sql .= " f.aforcrnume, f.eforcrbair, f.nforcrcida, f.cforcresta ";

        $sql .= " from sfpc.tbfornecedorcredenciado f";

        $sql .= " where f.aforcrsequ = " . $fornecedor;

        return $sql;
    }


    private function sqlConsultarCentroDeCustoUsuario($cgrempcodi, $cusupocodi, $corglicodi)
    {
        /*
        $sql = "
            SELECT
                   ccp.ccenpocorg, ccp.ccenpounid, ccp.corglicodi
              FROM sfpc.tbcentrocustoportal ccp
                   INNER JOIN sfpc.tbusuariocentrocusto ucc
                           ON ucc.ccenposequ = ccp.ccenposequ
             WHERE ucc.cgrempcodi = %d
                   AND ucc.cusupocodi = %d
                   AND ucc.fusucctipo LIKE 'C'
        ";

        if ($corglicodi != null || $corglicodi != "") {
          $sql .= " AND ccp.corglicodi = %d";
        }

        return sprintf($sql, $cgrempcodi, $cusupocodi, $corglicodi);
        */

        $sql = "
            SELECT
                   ccp.ccenpocorg, ccp.ccenpounid, ccp.corglicodi
              FROM sfpc.tbcentrocustoportal ccp                   
             WHERE true
        ";

        if ($corglicodi != null || $corglicodi != "") {
          $sql .= "  and ccp.corglicodi = %d";
        }

        return sprintf($sql, $corglicodi);
    }

    private function sqlItemAtaNova($numeroAta, $anoAta)
    {
       
        $sql = "SELECT
        
				    i.citarpsequ,
				    i.aitarporde,
				    i.aitarpqtor,
				    i.vitarpvori,
				    i.aitarpqtat,
				    i.vitarpvatu,
				    i.citarpnuml,
				    i.fitarpsitu,
				    i.eitarpdescmat,
				    i.cservpsequ,
				    i.cmatepsequ,
				    i.eitarpdescse,
				    m.ematepdesc,
				    s.eservpdesc,
    				ump.eunidmsigl
				FROM
				    sfpc.tbitemataregistropreconova i
				    INNER JOIN sfpc.tbataregistroprecointerna arpi
				    	ON arpi.carpnosequ = i.carpnosequ
				    		AND arpi.aarpinanon = %d
				    LEFT OUTER JOIN sfpc.tbmaterialportal m ON i.cmatepsequ = m.cmatepsequ
    				LEFT OUTER JOIN sfpc.tbunidadedemedida ump ON ump.cunidmcodi = m.cunidmcodi
				    LEFT OUTER JOIN sfpc.tbservicoportal s ON i.cservpsequ = s.cservpsequ
				WHERE
				    i.carpnosequ = %d";
           
        return sprintf($sql, $anoAta, $numeroAta);
        
    }

    private function sqlCodigoMaximoDocumento($processo, $orgao, $ano, $grupo)
    {
        
        $sql = "select max(d.cdoclicodi) from sfpc.tbdocumentolicitacao d";
        $sql .= "where d.clicpoproc =" . $processo;
        $sql .= "and d.cgrempcodi =" . $grupo;
        $sql .= "and d.corglicodi =" . $orgao;
        $sql .= "and d.alicpoanop =" . $ano;

        return $sql;
    }

    private function sqlInsereDocumento($valores)
    { 
      
        $sql = "INSERT INTO sfpc.tbdocumentolicitacao (clicpoproc,alicpoanop,cgrempcodi,ccomlicodi,corglicodi,";
        $sql .= "cdoclicodi,edoclinome,tdoclidata,cusupocodi,tdocliulat)";
        $sql .= " VALUES (" . $valores . ")";
        return $sql;
    }

    private function atualizarItemDoParticipante($db, $ata, $participante, $item)
    {
        //aqui 1
        
        $codigoUsuario = $this->getCodigoUsuarioLogado();
        $quantidadeItem = moeda2float($participante->quantidadeItem);

        $sql = "UPDATE
        sfpc.tbparticipanteitematarp
        SET
        apiarpqtat=$quantidadeItem,
        fpiarpsitu='$participante->situacaoParaItem',
        cusupocodi=$codigoUsuario,
        tpiarpulat=now()
        WHERE
        carpnosequ=$ata
        AND corglicodi=$participante->sequencial
        AND citarpsequ=$item->sequencial";

        $resultado = $db->query($sql);
        return $resultado;
    }

    private function consultarParticipanteAta($db, $ata, $participante)
    {
        
        $sql = "SELECT
        carpnosequ, corglicodi, fpatrpexcl, cusupocodi, tpatrpulat
        FROM
        sfpc.tbparticipanteatarp
        WHERE
        carpnosequ = $ata
        AND corglicodi = $participante->sequencial";

        $resultado = executarSQL($db, $sql);
        $resultado->fetchInto($participanteDaAta, DB_FETCHMODE_OBJECT);

        return $participanteDaAta;
    }


    /**
     * Dados. ConsultarAtaInterna
     *
     * Consulta uma ata interna através do "Código sequencial da ata de registro de preço"
     *
     * @param Negocio_ValorObjeto_Clicpoproc $clicpoproc Código sequencial da ata de registro de preço
     * @param Negocio_ValorObjeto_Alicpoanop $alicpoanop Ano do Processo Licitatório
     * @param Negocio_ValorObjeto_Corglicodi $corglicodi Código do Órgão Licitante
     * @param Negocio_ValorObjeto_Aforcrsequ $aforcrsequ Código do Fornecedor Credenciado
     *
     * @return Array
     */
    public function consultarAtaInterna(
        
            Negocio_ValorObjeto_Clicpoproc $clicpoproc,
            Negocio_ValorObjeto_Alicpoanop $alicpoanop,
            Negocio_ValorObjeto_Corglicodi $corglicodi,
            Negocio_ValorObjeto_Aforcrsequ $aforcrsequ,
            $ccomlicodi,
            $cgrempcodi
        )
    { 
		$resultadoAta = array();
        $db = Conexao();

        $sql  = "SELECT arpi.*, arpn.farpnotsal";
        $sql .= " FROM sfpc.tbataregistroprecointerna arpi";
        $sql .= " INNER JOIN sfpc.tbataregistropreconova arpn ON ";
        $sql .= " arpi.carpnosequ = arpn.carpnosequ ";
        $sql .= " WHERE";
        $sql .= " clicpoproc = %d";
        $sql .= " and arpi.alicpoanop = %d ";
        $sql .= " and arpi.corglicodi = %d ";
        $sql .= " and arpi.aforcrsequ = %d ";
        $sql .= " and arpi.ccomlicodi = %d ";
        $sql .= " and arpi.cgrempcodi = %d ";
        /*$ata = executarSQL($db,
            sprintf(
                $sql,
                $clicpoproc->getValor(),
                2016,
                7,
                4712
            )
        );*/
        $resultadoAta = null;        
        $ata = executarSQL($db,
            sprintf(
                $sql,
                $clicpoproc->getValor(),
                $alicpoanop->getValor(),
                $corglicodi->getValor(),
                $aforcrsequ->getValor(),
                $ccomlicodi,
                $cgrempcodi
            )
        );

		$ata->fetchInto($resultadoAta, DB_FETCHMODE_OBJECT);

        $this->hasError($resultado);
        
        if (empty($resultadoAta) == true) {
            $_SESSION['mensagemFeedback'] = 'A ata não existe';
            return false;
        }

        return $resultadoAta;

    }//end consultarAtaInterna()

    /**
     * Dados. ConsultarAtaInterna
     *
     * Consulta uma ata interna e verificar se o número é válido    
     *
     * @return Array
     */
    public function consultarNumeroAtaInterna($processo, $alicpoanop, $aarpinanon, $corglicodi, $carpincodn, $cgrempcodi, $ccomlicodi) {
     
        $resultadoAta = array();
        $db = Conexao();

        $sql  = "SELECT *";
        $sql .= " FROM sfpc.tbataregistroprecointerna";
        $sql .= " WHERE ";
        $sql .= " aarpinanon = %d and corglicodi = %d and carpincodn = %d ";       

        $resultadoAta = null;
        $ata = executarSQL($db, sprintf($sql,$aarpinanon, $corglicodi, $carpincodn));

        $ata->fetchInto($resultadoAta, DB_FETCHMODE_OBJECT);

        $this->hasError($resultado);
        
        if (empty($resultadoAta) == true) {
            return false;
        }

        return $resultadoAta;
    }



    /**
     * Deletar Item Ata
     *     
     */
    public function retirarItemAta($citarpsequ) {
        
        $database = Conexao();
        $database->autoCommit(false);
        $database->query("BEGIN TRANSACTION");
        

        try {
            $deleteFromParticipante     = sprintf("DELETE FROM sfpc.tbparticipanteitematarp WHERE carpnosequ = %d  AND citarpsequ = %d", $_REQUEST['ata'], $citarpsequ);
            $deleteFromItemCarona       = sprintf("DELETE FROM sfpc.tbitemcaronainternaatarp WHERE carpnosequ = %d  AND citarpsequ = %d", $_REQUEST['ata'], $citarpsequ);
            $deleteFromCaronaOrgExt     = sprintf("DELETE FROM sfpc.tbcaronaorgaoexternoitem WHERE carpnosequ = %d  AND citarpsequ = %d", $_REQUEST['ata'], $citarpsequ);
            $deleteFromItemPrecoNova    = sprintf("DELETE FROM sfpc.tbitemataregistropreconova WHERE carpnosequ = %d  AND citarpsequ = %d", $_REQUEST['ata'], $citarpsequ);

            $database->query($deleteFromParticipante);
            $database->query($deleteFromItemCarona);
            $database->query($deleteFromCaronaOrgExt);
            $database->query($deleteFromItemPrecoNova);

            $database->query("COMMIT");
            $database->query("END TRANSACTION");

            $_SESSION['mensagemFeedback'] = 'Item retirado com sucesso';
        } catch (Exception $e) {
            $semerror = false;
            $database->query("ROLLBACK");
            $_SESSION['mensagemFeedback'] = 'Erro ao retirar o item';
            ExibeErroBD("\nLinha: ".__LINE__."\nSql: " . $e->getMessage());
        }
    }//end consultarAtaInterna()

    public function consultarAtaPorChave($processo, $ano, $orgao, $numeroAta)
    {	
        $db = Conexao();
        $sql = $this->sqlAtaPorchave($processo, $orgao, $ano, $numeroAta);
		
        $res = executarSQL($db, $sql);
        $res->fetchInto($res, DB_FETCHMODE_OBJECT);
        $this->hasError($res);
        $db->disconnect();

        return $res;
    }

    public function updateNumeroFormadatoSemAno($numero, $carpnosequ)
    {
        $sql = "UPDATE sfpc.tbataregistroprecointerna ";
        $sql .= " SET earpinumf = '".$numero."'";
        $sql .= " WHERE carpnosequ = " . $carpnosequ;

        return $sql;
    }

    public function consultarDadosFornecedorProcesso($fornecedor)
    {   
        
        $db = Conexao();
        $sql = $this->sqlConsultarDadosFornecedorProcesso($fornecedor);
        
        $res = executarSQL($db, $sql);
        $res->fetchInto($res, DB_FETCHMODE_OBJECT);
        $this->hasError($res);
        $db->disconnect();

        return $res;
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

    public function consultarLicitacaoAtaInterna($ano, $processo, $orgaoUsuario, $codigoComissao, $codigoGrupo)
    {
       
        $db = Conexao();
        $sql = $this->sqlLicitacaoAtaInterna($ano, $processo, $orgaoUsuario, $codigoComissao, $codigoGrupo);
        $res = executarSQL($db, $sql);
        $res->fetchInto($res, DB_FETCHMODE_OBJECT);
        $this->hasError($res);
        $db->disconnect();

        return $res;
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
    private function sqlConsultarItensAta($alicpoanop, $carpnosequ)
    {
        
        $sql  = "SELECT * ";                    
        $sql .= "         FROM ";
        $sql .= "             sfpc.tbitemataregistropreconova i ";
        $sql .= "             INNER JOIN sfpc.tbataregistroprecointerna arpi ";
        $sql .= "                 ON arpi.carpnosequ = i.carpnosequ ";
        $sql .= "                     AND arpi.alicpoanop = %d ";
        $sql .= "             LEFT OUTER JOIN sfpc.tbmaterialportal m ON i.cmatepsequ = m.cmatepsequ ";
        $sql .= "             LEFT OUTER JOIN sfpc.tbunidadedemedida ump ON ump.cunidmcodi = m.cunidmcodi ";
        $sql .= "             LEFT OUTER JOIN sfpc.tbservicoportal s ON i.cservpsequ = s.cservpsequ ";
        $sql .= "         WHERE ";
        $sql .= "             i.carpnosequ = %d ";
        $sql .= "             order by i.citarpnuml ASC, i.aitarporde ASC ";
        
        //osmar
        return sprintf($sql, $alicpoanop, $carpnosequ);
      
       
    }//end sqlConsultarItensAta()


    /**
     * SQL consultar itens da ata.
     *
     * @param $clicpoproc Código do Processo Licitatório
     * @param $alicpoanop Ano do Processo Licitatório     
     * @param $ccomlicodi Código da Comissão
     * @param $corglicodi Código do Órgão Licitante
     */
    private function sqlConsultarItensProcessoSql($clicpoproc, $alicpoanop, $ccomlicodi, $corglicodi, $fornecedor, $lote)
    {
        

        $sql = " SELECT A.xlicpoobje, B.aitelporde, B.cmatepsequ, C.ematepdesc, D.eservpdesc, B.eitelpdescmat, B.eitelpdescse, "; 
        $sql .= " B.cservpsequ, B.aitelpqtso, B.vitelpvlog, B.vitelpunit, B.citelpnuml, C.cmatepsitu, U.eunidmsigl, ";
        $sql .= " B.citelpsequ, B.aitelporde, B.aitelpqtso, B.vitelpvlog, B.citelpnuml, B.eitelpmarc, B.eitelpmode, B.eitelpdescmat, B.eitelpdescse";
        $sql .= " FROM SFPC.TBLICITACAOPORTAL A, SFPC.TBITEMLICITACAOPORTAL B ";
        $sql .= " LEFT JOIN SFPC.TBMATERIALPORTAL C ON C.CMATEPSEQU = B.CMATEPSEQU ";
        $sql .= " LEFT JOIN SFPC.TBUNIDADEDEMEDIDA U ON C.CUNIDMCODI = U.CUNIDMCODI ";
        $sql .= " LEFT OUTER JOIN SFPC.TBSERVICOPORTAL D ON D.CSERVPSEQU = B.CSERVPSEQU ";
        $sql .= "WHERE ";
        $sql .= "A.CLICPOPROC = " . $clicpoproc;
        $sql .= " AND A.ALICPOANOP = " . $alicpoanop;        
        $sql .= " AND A.CCOMLICODI = " . $ccomlicodi;
        $sql .= " AND A.CORGLICODI = " . $corglicodi;
        $sql .= " AND B.AFORCRSEQU = " . $fornecedor;
        $sql .= " AND A.CLICPOPROC = B.CLICPOPROC ";
        $sql .= " AND A.ALICPOANOP = B.ALICPOANOP ";
        $sql .= " AND A.CGREMPCODI = B.CGREMPCODI ";
        $sql .= " AND A.CCOMLICODI = B.CCOMLICODI ";
        $sql .= " AND A.CORGLICODI = B.CORGLICODI ";
        $sql .= " AND A.CGREMPCODI = B.CGREMPCODI ";
        $sql .= " AND ((B.cmatepsequ = C.cmatepsequ AND B.cmatepsequ IS NOT NULL AND B.cmatepsequ <> 0) ";
        $sql .= " AND B.CITELPNUML IN (".$lote.")";
        $sql .= " OR (B.cservpsequ = D.cservpsequ AND B.cservpsequ IS NOT NULL AND B.cservpsequ <> 0 )) ";
        $sql .= "ORDER BY B.citelpnuml ASC";
        return $sql;

    }//end sqlConsultarItensAta()



    /**
     * Dados. Consultar itens da ata.
     *
     * @param $clicpoproc Código do Processo Licitatório
     * @param $alicpoanop Ano do Processo Licitatório
     * @param $cgrempcodi Código do Grupo
     * @param $ccomlicodi Código da Comissão
     * @param $corglicodi Código do Órgão Licitante
     */
    public function consultarItensAta($alicpoanop, $carpnosequ)
    {
     
        $db = Conexao();
        $sql = $this->sqlConsultarItensAta($alicpoanop, $carpnosequ);
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

    /**
     * Dados. Consultar itens da ata.
     *
     * @param $clicpoproc Código do Processo Licitatório
     * @param $alicpoanop Ano do Processo Licitatório     
     * @param $ccomlicodi Código da Comissão
     * @param $corglicodi Código do Órgão Licitante
     */
    public function sqlConsultarItensProcesso($clicpoproc, $alicpoanop, $ccomlicodi, $corglicodi, $fornecedor, $lote)
    {
        
      
        $database = Conexao();
        $sql = $this->sqlConsultarItensProcessoSql($clicpoproc, $alicpoanop, $ccomlicodi, $corglicodi, $fornecedor, $lote);
        $resultado = executarSQL($database, $sql);
        $itens = array();
        $item = null;
        $itemTipo = new stdClass();
        $itemTipo->tipoItem = "ITEMPROCESSO";
        while ($resultado->fetchInto($item, DB_FETCHMODE_OBJECT)) {
            $item->tipoItem = $itemTipo->tipoItem;
            $itens[] = $item;        
        }
        return $itens;
    }

    public function sqlConsultarSccExcluir($carpnosequ) {
       
        $database = Conexao();
        $sql = " SELECT COUNT(*) FROM sfpc.tbsolicitacaocompra 
                 WHERE carpnosequ = ".$carpnosequ."
                       AND csitsocodi IN (3,4,5)";
        $resultado = executarSQL($database, $sql);
        $resultado->fetchInto($resultado, DB_FETCHMODE_OBJECT);
        $this->hasError($resultado);
        return $resultado;
    }

    public function sqlConsultarDocumento(Negocio_ValorObjeto_Carpnosequ $carpnosequ)
    {
      
        $sql = " select carpnosequ,
                cdocatsequ,
                edocatnome,
                encode(idocatarqu, 'base64') as idocatarqu,                
                tdocatcada,
                cusupocodi,
                tdocatulat from sfpc.tbdocumentoatarp darp where darp.carpnosequ = %d";
        return sprintf($sql, $carpnosequ->getValor());
    }


    public function sqlConsultarAtaNumeracaoAno($numeracao, $ano, $orgao)
    {
       
        $sql = "  SELECT x.* FROM sfpc.tbataregistroprecointerna x
                    WHERE carpincodn = %d and aarpinanon = %d and corglicodi = %d ";

        return sprintf($sql, $numeracao, $ano, $orgao);
    }

    /**
     *
     * @param array $processo
     * @param unknown $fornecedorCredenciado
     * @param unknown $valorChave
     * @return string
     */
    public function configurarGerarAtas($processo, $fornecedorCredenciado, $valorChave, $situacaoAta = 'A', $ataCorporativa = 'N')
    {
        
        date_default_timezone_set('America/Recife');
        $valorAnoNumeracao = date('Y');
      
        $valor = explode('-', $processo);
        $valorOrgao = $valor[4];
        $repoAtaInterna = new Negocio_Repositorio_AtaRegistroPrecoInterna();
        //$ataInterna = $repoAtaInterna->procurarPorOrgaoAno($valorAnoNumeracao, $valorOrgao);

        $valorFornecedor    = is_null($fornecedorCredenciado) ? "'NULL'" : (int) $fornecedorCredenciado;
        $valorPrazoVigencia = !empty($valor[5]) ? $valor[5] : 12;
        $valorSituacao      = "'".$situacaoAta."'";
        $ataCorporativa     = "'".$ataCorporativa."'";
        $seguencialOutro    = 'NULL';
        $valorUsuario       = $_SESSION['_cusupocodi_'];

       

        $dataInicial = ClaHelper::dateTimeFormat($_REQUEST['DataInicial']);
        $castDataString = $dataInicial->format('Y-m-d H:i:s');        
        $date = date('Y-m-d H:i:s', strtotime($castDataString));
        //$dataInicial = $dataInicial->setTimezone(new DateTimeZone('UTC'));
        $valorObjeto = "'". $_REQUEST['VALOR_OBJETO'] ."'";

        //$timestamp = strtotime($dataInicialFinal);


        $valores = $valorChave . ',' . $valor[0] . ',';
        $valores .= $valor[1] . ',' . $valor[2] . ',' . $valor[3] . ',';
        $valores .= $valor[4] . ',';//6
        $valores .= $valorObjeto != null ? $valorObjeto . ',' : " 0" . ',';
        $valores .= $_REQUEST['ANO_ATA'] . ',';
        //$count = count($ataInterna) + 1; //receberParametrotela
        $valores .= $_REQUEST['VALOR_ATA'] . ',' . $valorFornecedor . ",'" . $date . "',";
        $valores .= $valorPrazoVigencia . ',' . $valorSituacao . ','. $ataCorporativa . ',' . $seguencialOutro . ',';
        $valores .= $valorUsuario . ',' . "now() ,";
        $valores .= $_SESSION['numeroAtaFormatado'];

        return $valores;
    }


    /**
     *
     * @param unknown $valores
     */
    public function sqlGerarAtasLicitacao($valores)
    {

        $sql = "INSERT INTO sfpc.tbataregistroprecointerna";
        $sql .= "(carpnosequ, clicpoproc, alicpoanop, cgrempcodi,";
        $sql .= "ccomlicodi, corglicodi, earpinobje, aarpinanon,";
        $sql .= "carpincodn, aforcrsequ, tarpindini, aarpinpzvg,";
        $sql .= "farpinsitu, farpincorp, carpnoseq1, cusupocodi, tarpinulat, earpinumf)";
        $sql .= " VALUES(" . $valores . ")";

        return $sql;
    }

    public function sqlAtualizarControle($carpnosequ, $controle)
    {
        $sql = "UPDATE sfpc.tbataregistropreconova ";
        $sql .= " SET farpnotsal = ".$controle;
        $sql .= " WHERE carpnosequ = " . $carpnosequ;

        return $sql;
    }

    /**
     *
     * @param unknown $ata, $processoCompleto, $fornecedor
     */
    public function sqlUpdateAtaLicitacao($ata, $processoCompleto, $fornecedor, $situacao, $ataCorporativa)
    {
       
        
        $dataInicial = ClaHelper::dateTimeFormat($_REQUEST['DataInicial']);
        $castDataString = $dataInicial->format('Y-m-d H:i:s');

        $carpincodn = $_REQUEST['VALOR_ATA'];
        $aarpinanon = $_REQUEST['ANO_ATA'];

            $str_objeto = str_replace("'","''",$_REQUEST['VALOR_OBJETO']);

        $earpinobje = "'". $str_objeto ."'";
        $tarpindini = "'". date('Y-m-d H:i:s', strtotime($castDataString))."'";

        $aarpinpzvg = $_REQUEST['VALOR_VIGENCIA'];
        $valoresProcesso = explode("-", $processoCompleto);
        $carpnosequ = $ata->carpnosequ;
        $clicpoproc = $valoresProcesso[0];
        $alicpoanop = $valoresProcesso[1];
        $cgrempcodi = $valoresProcesso[2];
        $ccomlicodi = $valoresProcesso[3];
        $corglicodi = $valoresProcesso[4];


        $sql = " UPDATE sfpc.tbataregistroprecointerna ";
        //Nº da Ata Interna*
        $sql .= " SET carpincodn = %d, ";
        //Ano da Ata Interna*
        $sql .= " aarpinanon = %d, ";
        //Objeto*
        $sql .= " earpinobje = ".$earpinobje.",";
        //Data Inicial*
        $sql .= " tarpindini = ".$tarpindini.",";
        //Vigência*
        $sql .= " aarpinpzvg = %d, ";
        // Situação*
        $sql .= " farpinsitu = '".$situacao."', ";
        // Ata corporativa
        $sql .= " farpincorp = '".$ataCorporativa."', ";
        //lat
        $sql .= " tarpinulat = now()";
        // where pela chave (carpnosequ, clicpoproc, alicpoanop, cgrempcodi, ccomlicodi, corglicodi)
        $sql .= " where carpnosequ = %d ";
        $sql .= " and clicpoproc = %d ";
        $sql .= " and alicpoanop = %d ";
        $sql .= " and cgrempcodi = %d ";
        $sql .= " and ccomlicodi = %d ";
        $sql .= " and corglicodi = %d ";
            

        return sprintf($sql, $carpincodn, $aarpinanon, $aarpinpzvg, 
                        $carpnosequ, $clicpoproc, $alicpoanop, $cgrempcodi, $ccomlicodi, $corglicodi);
    }

    /**
     *
     * @return string
     */
    public function sqlConsultarMaiorAta()
    {
        
        $sql = "select max(a.carpnosequ) from sfpc.tbataregistropreconova a";
        return $sql;
    }

    /**
     *
     * @return string
     */
    public function sqlConsultarMaiorItem($carpnosequ)
    {
      
        $sql = "select max(i.citarpsequ) from sfpc.tbitemataregistropreconova i where carpnosequ =".$carpnosequ;
        return $sql;
    }

    /**
     *
     * @param unknown $valorChave
     */
    public function configurarGerarAtaRegistroPrecoNova($valorChave, $tipoControle)
    {
        $valores = $valorChave . "," . "'I'" . "," . "now()" . "," . $_SESSION['_cusupocodi_'] . "," . "now(), " . $tipoControle;

        return $valores;
    }

    /**
     *
     * @param unknown $valores
     */
    public function sqlGerarAtasRegistroPrecoNova($valores)
    {
        $sql = "INSERT INTO sfpc.tbataregistropreconova";
        $sql .= "(carpnosequ, carpnotiat, tarpnoincl, cusupocodi,";
        $sql .= "tarpnoulat, farpnotsal)";
        $sql .= " VALUES(" . $valores . ")";

        return $sql;
    }    

}

/**
 * Classe RegistroPreco_Negocio_CadMigracaoAtaInternaAlterar
 */
class RegistroPreco_Negocio_CadMigracaoAtaInternaAlterar extends Negocio_Abstrata
{

    public $ErroPrograma = __FILE__;

    /**
     *
     * {@inheritdoc}
     *
     * @see Negocio_Abstrata::getDados()
     */
    public function getDados()
    {
        $this->setDados(new RegistroPreco_Dados_CadMigracaoAtaInternaAlterar());
        return parent::getDados();
    }

    /**
     *
     * @param unknown $carpnosequ
     */
    public function consultarAtaIterna($clicpoproc, $alicpoanop, $corglicodi, $aforcrsequ, $ccomlicodi, $cgrempcodi)
    {
        $voClicpoproc = new Negocio_ValorObjeto_Clicpoproc($clicpoproc);
        $voAlicpoanop = new Negocio_ValorObjeto_Alicpoanop($alicpoanop);
        $voCorglicodi = new Negocio_ValorObjeto_Corglicodi($corglicodi);
        $voAforcrsequ = new Negocio_ValorObjeto_Aforcrsequ($aforcrsequ);

        return $this->getDados()->consultarAtaInterna($voClicpoproc, $voAlicpoanop, $voCorglicodi, $voAforcrsequ, $ccomlicodi, $cgrempcodi);
    }

    public function consultarNumeroAtaInterna($processo, $alicpoanop, $aarpinanon, $corglicodi, $carpincodn, $cgrempcodi, $ccomlicodi) 
    {
        return $this->getDados()->consultarNumeroAtaInterna($processo, $alicpoanop, $aarpinanon, $corglicodi, $carpincodn, $cgrempcodi, $ccomlicodi);
    }

    /**
     *
     * @param unknown $carpnosequ
     */
    public function retirarItemAta($citarpsequ)
    {        
        return $this->getDados()->retirarItemAta($citarpsequ);
    }

    public function consultarAtaPorChave($processo, $ano, $orgao, $numeroAta)
    {
        return $this->getDados()->consultarAtaPorChave($processo, $ano, $orgao, $numeroAta);
    }

    public function updateNumeroFormadatoSemAno($numero, $ata)
    {
        return $this->getDados()->updateNumeroFormadatoSemAno($numero, $ata);
    }

    public function consultarDadosFornecedorProcesso($fornecedor)
    {
        return $this->getDados()->consultarDadosFornecedorProcesso($fornecedor);
    }

    public function consultarDCentroDeCustoUsuario($cgrempcodi, $cusupocodi, $corglicodi)
    {
        return $this->getDados()->consultarDCentroDeCustoUsuario($cgrempcodi, $cusupocodi, $corglicodi);
    }

    public function consultarLicitacaoAtaInterna($ano, $processo, $orgaoUsuario, $codigoComissao, $codigoGrupo)
    {
        return $this->getDados()->consultarLicitacaoAtaInterna($ano, $processo, $orgaoUsuario, $codigoComissao, $codigoGrupo);
    }

    /**
     * Negócio. Consultar itens da ata.
     */
    public function consultarItensAta($alicpoanop, $carpnosequ)
    {
        return $this->getDados()->consultarItensAta($alicpoanop, $carpnosequ);

    }//end consultarItensAta()

    /**
     * Negócio. Consultar itens da ata.
     */
    public function sqlConsultarItensProcesso($clicpoproc, $alicpoanop, $ccomlicodi, $corglicodi, $fornecedor, $lote)
    {
        return $this->getDados()->sqlConsultarItensProcesso($clicpoproc, $alicpoanop, $ccomlicodi, $corglicodi, $fornecedor, $lote);

    }

    public function sqlConsultarSccExcluir($carpnosequ) {
        return $this->getDados()->sqlConsultarSccExcluir($carpnosequ);
    }

    private function getCodigoUsuarioLogado()
    {
        return (integer) $this->variables['session']['_cusupocodi_'];
    }

    /**
     * Negócio. Salvar
     *
     * @return void
     */
    public function salvar()
    {        
        $db                 = Conexao();
        $ano                = $_REQUEST['ano'];
        $processo           = $_REQUEST['processo'];
        $orgao              = $_REQUEST['orgao'];
        $ata                = $_REQUEST['ata'];            
        $codigoGrupo        = ($_POST['VALOR_CODIGO_GRUPO'] != '') ? $_POST['VALOR_CODIGO_GRUPO'] : $_SESSION['codigoGrupo'];
        $situacaoAta        = $_POST['situacaoAta'];
        $ataCorporativa     = $_POST['ataCorporativa']; 
        if($ata){
            $atas = $this->consultarAtaPorChave($processo, $ano, $orgao, $ata); 
        }
       

        $valorAta = str_pad($atas->carpincodn, 4, "0", STR_PAD_LEFT);
        
        $consultarfor       = new RegistroPreco_Dados_CadMigracaoAtaInternaAlterar();
        $dto                = $consultarfor->consultarDCentroDeCustoUsuario($atas->cgrempcodi, $atas->cusupocodi, $orgao);
        $objeto             = current($dto);              
        $numeroAtaFormatado = "";
        $numeroAtaFormatado .= $objeto->ccenpocorg . str_pad($objeto->ccenpounid, 2, '0', STR_PAD_LEFT);

        $ataConcatenada = $numeroAtaFormatado.'.'.$valorAta;
        
        $updateNumeroFormadatoSemAno = $this->updateNumeroFormadatoSemAno($ataConcatenada, $ata);

        $processoCompleto   = $_REQUEST['processo'] . '-' . $_REQUEST['ano'] . '-' . $codigoGrupo . '-' . $_REQUEST['codigocomissao'] . '-' . $_REQUEST['orgao'];

        if($_REQUEST['ata'] != ''){
            $ataConsultada    = $this->consultarAtaPorChave($_REQUEST['processo'], $_REQUEST['ano'], $_REQUEST['orgao'], $_REQUEST['ata']);
            
            if($ataConsultada != null){
                return $this->updateAta($ataConsultada, $processoCompleto, $_REQUEST['fornecedor'], $situacaoAta, $ataCorporativa);
            }
        }
        
        $semerror = true;
        
        $db = Conexao();
        $db->autoCommit(false);
        $db->query("BEGIN TRANSACTION");
        $carpnosequ             = $this->obterProximoNumeroAta();
        
        $valoresNova            = $this->getDados()->configurarGerarAtaRegistroPrecoNova($carpnosequ, $_REQUEST['TipoControle']);
        
        $processo               = $_REQUEST['processo'] . '-' . $_REQUEST['ano'] . '-' . $codigoGrupo . '-' . $_REQUEST['codigocomissao'] . '-' . $_REQUEST['orgao'] . '-' . $_REQUEST['VALOR_VIGENCIA'];
        
        $valores                = $this->getDados()->configurarGerarAtas($processo, $_REQUEST['fornecedor'], $carpnosequ, $situacaoAta, $ataCorporativa);
        
        $sqlAtaNova             = $this->getDados()->sqlGerarAtasRegistroPrecoNova($valoresNova);
       
        $sql                    = $this->getDados()->sqlGerarAtasLicitacao($valores);
        
        $sqlAtualizarControle   = $this->getDados()->sqlAtualizarControle($carpnosequ, $_REQUEST['TipoControle']);

        $resultadoAtualizarCont = executarTransacao($db, $sqlAtualizarControle);
        $resultadoAtaNova       = executarTransacao($db, $sqlAtaNova);
        $resultadoAtaInterna    = executarTransacao($db, $sql);
        
        $commited = $db->commit();
        unset($_SESSION['numeroAtaFormatado']);

        if ($commited instanceof DB_error) {
            $db->rollback();

            $_SESSION['mensagemFeedback'] = 'Erro ao salvar dados';
            ExibeErroBD("\nLinha: " . __LINE__ . "\nSql: " . $e->getMessage());

            $semerror = false;
        }
        
        if($carpnosequ){
            $ata = $this->consultarAtaPorChave($_REQUEST['processo'], $_REQUEST['ano'], $_REQUEST['orgao'], $carpnosequ);
            $conexao = ClaDatabasePostgresql::getConexao();
            $conexao->autoCommit(false);
            $this->inserirDocumentoAta($conexao, $ata->carpnosequ);

            $commited = $conexao->commit();
        }
       

     

        if ($commited instanceof DB_error) {
            $conexao->rollback();

            return false;
        }

        unset($_SESSION['Arquivos_Upload']);

        $database = Conexao();
        $database->autoCommit(false);
        $database->query("BEGIN TRANSACTION");

        try {
           
            foreach ($_REQUEST['itemAta'] as $key=> $item) {   
                if(empty($item['valor_descricao_detalhada'])){
                    $item['valor_descricao_detalhada'] = $_SESSION['descricao_detalhada_m'][$key];
                }
                $this->salvarItemAta($database, $ata, $item);                                
            }
            $database->query("COMMIT");
            $database->query("END TRANSACTION");

            $_SESSION['mensagemFeedback'] = 'Dados salvos com sucesso';
        } catch (Exception $e) {
            $semerror = false;
            $database->query("ROLLBACK");
            $_SESSION['mensagemFeedback'] = 'Erro ao salvar dados';
            ExibeErroBD($this->ErroPrograma . "\nLinha: ".__LINE__."\nSql: " . $_SESSION['sqlErro']);
        }

        $database->disconnect();

        UNSET($_SESSION['descricao_detalhada_m']);
        return $semerror;
    }
       

    private function updateAta($ata, $processoCompleto, $fornecedor, $situacao, $ataCorporativa){
        $db = Conexao();
        $db->autoCommit(false);

        $sqlControle = $this->getDados()->sqlAtualizarControle($ata->carpnosequ, $_REQUEST['TipoControle']);
        $sql         = $this->getDados()->sqlUpdateAtaLicitacao($ata, $processoCompleto, $fornecedor, $situacao, $ataCorporativa);
        $semerror = true;
        
        $resultadoControle   = executarTransacao($db, $sqlControle);
        $resultadoAtaInterna = executarTransacao($db, $sql);

        $commited = $db->commit();

        if ($commited instanceof DB_error) {
            $db->rollback();

            $_SESSION['mensagemFeedback'] = 'Erro ao salvar dados';
            ExibeErroBD(self::$erroPrograma . "\nLinha: " . __LINE__ . "\nSql: " . $e->getMessage());

            $semerror = false;
        }

        $conexao = ClaDatabasePostgresql::getConexao();
        $conexao->autoCommit(false);
        $this->inserirDocumentoAta($conexao, $ata->carpnosequ);

        $commited = $conexao->commit();

        if ($commited instanceof DB_error) {
            $conexao->rollback();

            return false;
        }

        unset($_SESSION['Arquivos_Upload']);

        //Atualizar/salvar os Itens
        
        if(!empty($_REQUEST['itemAta'])) {
            $database = Conexao();
            $database->autoCommit(false);
            $database->query("BEGIN TRANSACTION");

            try {
                
                foreach ($_REQUEST['itemAta'] as $item) {
                    $this->salvarItemAta($database, $ata, $item);                                
                }

                $database->query("COMMIT");
                $database->query("END TRANSACTION");

                $_SESSION['mensagemFeedback'] = 'Dados salvos com sucesso';
            } catch (Exception $e) {
                $semerror = false;
                $database->query("ROLLBACK");
                $_SESSION['mensagemFeedback'] = 'Erro ao salvar dados';
                ExibeErroBD("\nLinha: ".__LINE__."\nSql: " . $e->getMessage());
            }

            $database->disconnect();
        }        

        return $semerror;        
    }


    /**
     * [inserirDocumentoAta description]
     *
     * @param [type] $conexao
     *            [description]
     * @param [type] $carpnosequ
     *            [description]
     * @return [type] [description]
     */
    function deletaDocumento($carpnosequ){
        $db = Conexao();
        $sql = "DELETE FROM sfpc.tbdocumentoatarp WHERE carpnosequ = $carpnosequ";
        $resultado = executarSQL($db, $sql);
    }
    function contaDocumento($carpnosequ){
        $db = Conexao();
        $sql = "SELECT MAX(cdocatsequ) FROM sfpc.tbdocumentoatarp WHERE carpnosequ = $carpnosequ";
        $resultado = executarSQL($db, $sql);
        $result = 0;
        $resultado->fetchInto($result, DB_FETCHMODE_OBJECT);
        $retorno = $result->max + 1;
        return $retorno;
    }
    private function inserirDocumentoAta($conexao, $carpnosequ)
    {
        // $conexao->query(sprintf("DELETE FROM sfpc.tbdocumentoatarp WHERE carpnosequ = %d", $carpnosequ));

        // $documento = $conexao->getRow('SELECT MAX(cdocatsequ) FROM sfpc.tbdocumentoatarp WHERE carpnosequ = ?', array(
        //     (int)$carpnosequ
        // ), DB_FETCHMODE_OBJECT);
        $this->deletaDocumento($carpnosequ);
        $valorMax = $this->contaDocumento($carpnosequ);
        $tamanho = count($_SESSION['Arquivos_Upload']['nome']);
        
        // $nomeTabela = 'sfpc.tbdocumentoatarp';
        // $entidade = ClaDatabasePostgresql::getEntidade($nomeTabela);
       $db = Conexao();
        for ($i = 0; $i < $tamanho; $i++) {
        //     $entidade->carpnosequ = (int)$carpnosequ;
        //     $entidade->cdocatsequ = (int)$valorMax;
        //     $entidade->edocatnome = $_SESSION['Arquivos_Upload']['nome'][$i];  
     
        //     $entidade->idocatarqu = bin2hex($_SESSION['Arquivos_Upload']['conteudo'][$i]);
            
        //     $entidade->tdocatcada = 'NOW()';
        //     $entidade->cusupocodi = (int)$_SESSION['_cusupocodi_'];
        //     $entidade->tdocatulat = 'NOW()';

        //    $resultado =  $conexao->autoExecute($nomeTabela, (array)$entidade, DB_AUTOQUERY_INSERT);
        //    var_dump('oi');die;
        //     if (ClaDatabasePostgresql::hasError($resultado)) {
        //         $conexao->rollback();
        //         return false;
        //     }
                $sql ="
                insert into sfpc.tbdocumentoatarp 
                (   carpnosequ, 
                    cdocatsequ,	
                    edocatnome,	
                    idocatarqu,	
                    tdocatcada,	
                    cusupocodi,	
                    tdocatulat
                ) values ( 
                    ".$carpnosequ.",
                    ".$valorMax.",
                    '".$_SESSION['Arquivos_Upload']['nome'][$i]."',
                    '".bin2hex($_SESSION['Arquivos_Upload']['conteudo'][$i])."',
                    now(),
                    ".$_SESSION['_cusupocodi_'].",
                    now()
                )
            ";
            $resultado = executarSQL($db, $sql);
            $valorMax++;
            }

    }

    private function salvarParticipante($db, $ata, $item)
    {
        if (isset($item['participantes']) === true) {
            foreach ($item['participantes'] as $participante) {
                $participanteNoBanco = $this->consultarParticipanteAta($db, $ata, $participante);
                $resultado = null;
                $codigoUsuario = $this->getCodigoUsuarioLogado();

                if ($participanteNoBanco == null) {
                    $resultado = $this->inserirParticipante($db, $ata, $participante, $codigoUsuario);
                } else {
                    $resultado = $this->atualizarParticipante($db, $ata, $participante, $codigoUsuario);
                }

                if (PEAR::isError($resultado)) {
                    throw new RuntimeException($resultado->getMessage());
                }

                $this->salvarItemParticipante($db, $ata, $item, $participante);
            }
        }
    }
//???????????????????????????????????????????????????????????????????????????????????????????????????????????????????
    private function salvarItemAta($db, $ata, $item)
    {
        
        $codigoReduzido = $item['cservpsequ'];
        if(isset($item['cmatepsequ']) === true) {
            $codigoReduzido = $item['cmatepsequ'];
        }
        $itemNoBanco = $this->consultarItemAta($db, $ata->carpnosequ, $codigoReduzido, $item['fgrumstipo']);
        

        $resultado = null;
        if ($itemNoBanco == null || $item['citelpsequ'] == '' || $_REQUEST['ata'] == '') {            
            $resultado = $this->inserirItem($db, $ata, $item);
        } else {
            $resultado = $this->atualizarItem($db, $ata, $item);            
        }

        if (PEAR::isError($resultado)) {
            throw new RuntimeException($resultado->getMessage());
        }
    }
    
    private function inserirItem($db, $ata, $item)
    {
     
;
        $sequencial         = $item['citelpsequ'] == null ? $this->obterProximoNumeroItem($ata->carpnosequ) : $item['citelpsequ'];
        $ordem              = $item['aitelporde'] == null ? 'null' : $item['aitelporde'];
        $sequencialMaterial = 'null';
        $sequencialServico  = 'null';
        
        if($item['valor_qtd_original'] != ''){
            if(is_numeric(moeda2float($item['valor_qtd_original'], 4))){
                $quantidadeOriginal = moeda2float($item['valor_qtd_original'], 4);
            }else{
                $quantidadeOriginal = 0;
            }
        }

        if($item['valor_original_unit'] != ''){
            if(is_numeric(moeda2float($item['valor_original_unit'], 4))){
                $valorUnitarioOriginal = moeda2float($item['valor_original_unit'], 4);
            }else{
                $valorUnitarioOriginal = 0;
            }
        }

        //$quantidadeOriginal = $item['aitelpqtso'] == null ? 0  :moeda2float($item['aitelpqtso'], 4);
        //$valorUnitarioOriginal = $item['vitelpvlog'] == null ? 0  :moeda2float($item['vitelpvlog'], 4);
       
        if(isset($item['valor_qtd_original'])){
            if($item['valor_qtd_original'] != ''){
                $quantidadeOriginal = moeda2float($item['valor_qtd_original'], 4);
            }
        }

        if(isset($item['valor_original_unit'])){
            if($item['valor_original_unit'] != ''){
                $valorUnitarioOriginal = moeda2float($item['valor_original_unit'], 4);
            }
        }               

        $quantidadeAtual    = (($item['quantidade_total'] == null) || ($item['quantidade_total'] == '')) ? 0  : moeda2float($item['quantidade_total'], 4);        
        $valorUnitarioAtual = (($item['valor_unitario_atual'] == null) || ($item['valor_unitario_atual'] == '') ) ? 0  :moeda2float($item['valor_unitario_atual'], 4); 
        $lote               = $item['citelpnuml'] == null ? 0 : $item['citelpnuml'];        
        $situacaoItem       = $item['situacao'];
        $incluidoDiretamente = $item['fitarpincl'];
        $excluidoDiretamente = $item['fitarpexcl'];             
        $marca              = $item['eitelpmarc'] == null ? 'null' : $item['eitelpmarc'];
        $modelo             = $item['eitelpmode'] == null ? 'null' : $item['eitelpmode'];        
        $codigoUsuario      = $_SESSION['_cusupocodi_'];
        
        if ($item['fgrumstipo'] == 'CADUM') {
            $sequencialMaterial = $item['cmatepsequ'] == null ? 'null' : $item['cmatepsequ'];
        } else {
            $sequencialServico = $item['cmatepsequ'] == null ? 'null' : $item['cmatepsequ'];
        }

        if(strlen($item['fgrumstipo']) > 1){
            $colunaSequencialItem = ($item['fgrumstipo'] == 'CADUM') ? 'eitarpdescmat' : 'eitarpdescse';
        }else{
            $colunaSequencialItem = ($item['fgrumstipo'] == 'M') ? 'eitarpdescmat' : 'eitarpdescse';    
        }
      
    
       if($loteExistente == true){
        $_SESSION['colecaoMensagemErro'][] = "
        <a href='javascript:document.getElementById(\"VALOR_VIGENCIA\").focus();' class='titulo2'>
            Nenhum documento da ata foi informado
        </a>";
        $retorno = false;
       }
       $marca = str_replace('®','',$marca);
       $modelo = str_replace('®','',$modelo);

        $sql  = "INSERT INTO ";
        $sql .= "sfpc.tbitemataregistropreconova ";
        $sql .= "(";
        $sql .= "carpnosequ, ";
        $sql .= "citarpsequ, ";
        $sql .= "aitarporde, ";
        $sql .= "cmatepsequ, ";
        $sql .= "cservpsequ, ";
        $sql .= "aitarpqtor, ";
        $sql .= "aitarpqtat, ";
        $sql .= "vitarpvori, ";
        $sql .= "vitarpvatu, ";
        $sql .= "citarpnuml, ";
        $sql .= "fitarpsitu, ";
        $sql .= "fitarpincl, ";
        $sql .= "fitarpexcl, ";
        $sql .= "titarpincl, ";
        $sql .= "cusupocodi, ";
        $sql .= "titarpulat, ";
        $sql .= "eitarpmarc, ";
        
        //FIXME: logica para modificar quando for processo licitatorio
        if(isset($item['valor_descricao_detalhada'])){
            $sql .= $colunaSequencialItem . ", ";
        }
        $sql .= "eitarpmode, ";
        $sql .= "citarpitel ";
        $sql .= ")";
        $sql .= "VALUES ";
        $sql .= "(";
        $sql .= "$ata->carpnosequ, ";
        $sql .= "$sequencial, ";
        $sql .= "$ordem, ";
        $sql .= "$sequencialMaterial, ";
        $sql .= "$sequencialServico, ";
        $sql .= "$quantidadeOriginal, ";
        $sql .= "$quantidadeAtual, ";
        $sql .= "$valorUnitarioOriginal, ";
        $sql .= "$valorUnitarioAtual, ";
        $sql .= "$lote, ";
        $sql .= "'$situacaoItem', ";
        $sql .= "'$incluidoDiretamente', ";
        $sql .= "'$excluidoDiretamente', ";
        $sql .= "now(), ";
        $sql .= "$codigoUsuario, ";
        $sql .= "now(), ";
        $sql .= "'$marca', ";

        if(isset($item['valor_descricao_detalhada'])){

            $item['valor_descricao_detalhada'] = str_replace("'","''",$item['valor_descricao_detalhada']);
            $sql .= " '" . strtoupper($item['valor_descricao_detalhada']) ."', "; 
        }

        $sql .= "'$modelo', ";
        $sql .= !empty($item['citelpsequ']) ? $item['citelpsequ'] : 'null';
        $sql .= ")";

        $_SESSION['sqlErro'] = $sql;

        $resultado = $db->query($sql);
        return $resultado;
    }

    private function atualizarItem($db, $ata, $item)
    {
    
        $valorUnitarioOriginal = null;
        $quantidadeOriginal = null;

        if(isset($item['valor_qtd_original'])){
            if($item['valor_original_unit'] != null && $item['valor_original_unit'] != ''){
                $valorUnitarioOriginal  = (float) moeda2float($item['valor_original_unit'],4);
            } else {
                $valorUnitarioOriginal  = (float) moeda2float('0,0000',4);
            }            
        }

        if(isset($item['valor_original_unit'])){
            if($item['valor_qtd_original'] != null && $item['valor_qtd_original'] != ''){
                $quantidadeOriginal     = (float) moeda2float($item['valor_qtd_original'],4);
            }else{
                $quantidadeOriginal  = (float) moeda2float('0,0000',4);
            }
        }
        
        $aitarpqtat         = ($item['quantidade_total'] == null || $item['quantidade_total'] == '') ? (float) moeda2float('0,0000',4) : (float) moeda2float($item['quantidade_total'], 4);
        $vitarpvatu         = ($item['valor_unitario_atual'] == null || $item['valor_unitario_atual'] == '')  ? (float) moeda2float('0,0000',4) : (float) moeda2float($item['valor_unitario_atual'], 4);
        $situacao           = $item['situacao'];        
        $codigoUsuario      = $_SESSION['_cusupocodi_'];        
        $citelpsequ         = $item['citelpsequ'] == null ? $item['citarpsequ'] : $item['citelpsequ'];
        $citarpnuml         = $item['citelpnuml'];
        $aitarporde         = $item['aitelporde'];
        
        if(strlen($item['fgrumstipo']) > 1){
            $colunaSequencialItem = ($item['fgrumstipo'] == 'CADUM') ? 'eitarpdescmat' : 'eitarpdescse';
        } else {
            $colunaSequencialItem = ($item['fgrumstipo'] == 'M') ? 'eitarpdescmat' : 'eitarpdescse';    
        }

        $marca              = $item['eitelpmarc'] == null ? 'null' : $item['eitelpmarc'];
        $modelo             = $item['eitelpmode'] == null ? 'null' : $item['eitelpmode'];  
        $marca = str_replace('®','',$marca);
        $modelo = str_replace('®','',$modelo);
        $sql = "UPDATE sfpc.tbitemataregistropreconova
                SET aitarpqtat=$aitarpqtat, 
                    fitarpsitu='$situacao',
                    vitarpvatu=$vitarpvatu, 
                    cusupocodi=$codigoUsuario, 
                    eitarpmarc='$marca', 
                    citarpnuml=$citarpnuml, 
                    aitarporde=$aitarporde, 
                    eitarpmode='$modelo', 
                    citarpitel=" . $item['citelpsequ'] . ","; 

        if(is_float($valorUnitarioOriginal)){
            $sql .= " vitarpvori =" . $valorUnitarioOriginal . ", ";            
        }

        if(is_float($quantidadeOriginal)){
            $sql .= " aitarpqtor =" . $quantidadeOriginal . ", ";
        }
        
        //FIXME: logica para modificar quando for processo licitatorio
        if(isset($item['valor_descricao_detalhada'])){            
            $item['valor_descricao_detalhada'] = str_replace("'","''",$item['valor_descricao_detalhada']);
            $sql .= $colunaSequencialItem . " = '" . strtoupper($item['valor_descricao_detalhada']) ."', "; 
        }

        $sql .= "   titarpulat=now()
                WHERE
                    carpnosequ = $ata->carpnosequ
                    
                    AND citarpsequ=$citelpsequ";

        $resultado = $db->query($sql);

        return $resultado;
    }

    /**
     *
     * @param integer $ata
     * @param integer $codigoReduzido
     * @param integer $tipo
     */
    public function consultarItemAta($db, $ata, $codigoReduzido, $tipo, $codSeqItem = null) {
        $colunaSequencialItem = "";

        if (strlen($tipo) > 1) {
            $colunaSequencialItem = ($tipo == 'CADUM') ? 'CMATEPSEQU' : 'CSERVPSEQU';
        } else {
            $colunaSequencialItem = ($tipo == 'M') ? 'CMATEPSEQU' : 'CSERVPSEQU';    
        }

        $sql  = "SELECT IARPN.CARPNOSEQU, IARPN.$colunaSequencialItem, IARPN.AITARPQTOR, IARPN.AITARPQTAT, IARPN.FITARPSITU, ";
        $sql .= "       IARPN.VITARPVATU ";
        $sql .= "FROM   SFPC.TBITEMATAREGISTROPRECONOVA IARPN ";
        $sql .= "WHERE  IARPN.CARPNOSEQU = " . $ata;
        $sql .= "       AND IARPN." . $colunaSequencialItem . " = " . $codigoReduzido;
        
        if (!empty($codSeqItem)) {
            $sql .= "       AND IARPN.CITARPSEQU = " . $codSeqItem;
        }

        $resultado = executarSQL($db, $sql);
        $resultado->fetchInto($item, DB_FETCHMODE_OBJECT);

        return $item;
    }

    /**
     *
     * @param integer $ata
     * @param integer $codigoReduzido
     * @param integer $tipo
     */
    public function consultarItemCompleto($db, $codigoReduzido, $tipo)
    {
    
        $colunaSequencialItem = "";
        if(strlen($tipo) > 1){
            $colunaSequencialItem = ($tipo == 'CADUM') ? 'cmatepsequ' : 'cservpsequ';
        }else{
            $colunaSequencialItem = ($tipo == 'M') ? 'cmatepsequ' : 'cservpsequ';    
        }

        $sql = " SELECT
        *
        FROM ";

        if ($tipo == 'M') {
            $sql .= " sfpc.tbmaterialportal i left join SFPC.TBunidadedemedida um ON
            um.cunidmcodi = i.cunidmcodi   ";
        }else{
            $sql .= " sfpc.tbservicoportal i ";
        }
        
        $sql .= " WHERE
        i.$colunaSequencialItem = ".$codigoReduzido;

        $resultado = executarSQL($db, $sql);
        $resultado->fetchInto($item, DB_FETCHMODE_OBJECT);

        return $item;
    }


    public function obterProximoNumeroAta()
    {   
        $sql = $this->getDados()->sqlConsultarMaiorAta();
        $resultado = executarPGSQL($sql);
        $resultado->fetchInto($valorMaximo, DB_FETCHMODE_OBJECT);

        $valorAtual = intval($valorMaximo->max) + 1;

        return $valorAtual;
    }

    public function obterProximoNumeroItem($carpnosequ)
    {
        $sql = $this->getDados()->sqlConsultarMaiorItem($carpnosequ);
        $resultado = executarSQL(ClaDatabasePostgresql::getConexao(), $sql);
        $resultado->fetchInto($valorMaximo, DB_FETCHMODE_OBJECT);

        $valorAtual = intval($valorMaximo->max) + 1;

        return $valorAtual;
    }

}// end class

/**
 * Classe RegistroPreco_Adaptacao_CadMigracaoAtaInternaAlterar
 */
class RegistroPreco_Adaptacao_CadMigracaoAtaInternaAlterar extends Adaptacao_Abstrata
{

    /**
     *
     * {@inheritdoc}
     *
     * @see Adaptacao_Abstrata::getNegocio()
     */
    public function getNegocio()
    {
        $this->setNegocio(new RegistroPreco_Negocio_CadMigracaoAtaInternaAlterar());
        return parent::getNegocio();
    }

    /**
     *
     * @param unknown $carpnosequ
     */
    public function consultarAtaInterna($clicpoproc, $alicpoanop, $corglicodi, $aforcrsequ, $ccomlicodi, $cgrempcodi)
    {
        return $this->getNegocio()->consultarAtaIterna($clicpoproc, $alicpoanop, $corglicodi, $aforcrsequ, $ccomlicodi, $cgrempcodi);
    }

    public function consultarNumeroAtaIterna($processo, $alicpoanop, $aarpinanon, $corglicodi, $carpincodn, $cgrempcodi, $ccomlicodi) 
    {
        return $this->getNegocio()->consultarNumeroAtaIterna($processo, $alicpoanop, $aarpinanon, $corglicodi, $carpincodn, $cgrempcodi, $ccomlicodi);   
    }


    /**
     *
     * @param unknown $carpnosequ
     */
    public function retirarItemAta($citarpsequ)
    {
        return $this->getNegocio()->retirarItemAta($citarpsequ);
    }

    /**
     *
     * @param integer $ata
     * @param integer $codigoReduzido
     * @param integer $tipo
     */
    public function consultarItemAta($ata, $codigoReduzido, $tipo)
    {
        $colunaSequencialItem = ($tipo == 'CADUM') ? 'cmatepsequ' : 'cservpsequ';

        $sql = "SELECT
        iarpn.carpnosequ, iarpn.$colunaSequencialItem, aitarpqtor, aitarpqtat
        FROM
        sfpc.tbitemataregistropreconova iarpn
        WHERE
        iarpn.carpnosequ = $ata
        AND iarpn.$colunaSequencialItem = $codigoReduzido";

        $resultado = executarSQL($db, $sql);
        $resultado->fetchInto($item, DB_FETCHMODE_OBJECT);

        return $item;
    }

    /**
     * Adaptação. Consultar itens de uma ata.
     */
    public function consultarItensAta($alicpoanop, $carpnosequ)
    {
        return $this->getNegocio()->consultarItensAta($alicpoanop, $carpnosequ);

    }//end consultarItensAta()

    /**
     * Adaptação. Consultar itens de uma ata.
     */
    public function sqlConsultarItensProcesso($clicpoproc, $alicpoanop, $ccomlicodi, $corglicodi, $fornecedor, $lote)
    {
        return $this->getNegocio()->sqlConsultarItensProcesso($clicpoproc, $alicpoanop, $ccomlicodi, $corglicodi, $fornecedor,$lote);

    }

    public function consultarSccExcluir($carpnosequ)
    {
        return $this->getNegocio()->sqlConsultarSccExcluir($carpnosequ);

    }

    public function consultarItemDoParticipante($db, $ata, $sequencialParticipante, $sequencialItem)
    {
        $sql = "SELECT
        carpnosequ, corglicodi, citarpsequ, apiarpqtat, fpiarpsitu, cusupocodi, tpiarpulat
        FROM
        sfpc.tbparticipanteitematarp
        WHERE
        carpnosequ = $ata
        AND corglicodi = $sequencialParticipante
        AND citarpsequ = $sequencialItem";

        $resultado = executarSQL($db, $sql);
        $resultado->fetchInto($itemDoParticipante, DB_FETCHMODE_OBJECT);

        return $itemDoParticipante;
    }

    public function consultarLicitacaoAtaInterna($ano, $processo, $orgaoUsuario, $codigoComissao, $codigoGrupo)
    {
        return $this->getNegocio()->consultarLicitacaoAtaInterna($ano, $processo, $orgaoUsuario, $codigoComissao, $codigoGrupo);
    }


    /**
     * consultarAtaPorChave
     */
    public function consultarAtaPorChave($processo, $ano, $orgao, $numeroAta)
    {
        return $this->getNegocio()->consultarAtaPorChave($processo, $ano, $orgao, $numeroAta);

    }//end consultarAtaPorChave()

    /**
     * consultarAtaPorChave
     */
    public function consultarDadosFornecedorProcesso($fornecedor)
    {
        return $this->getNegocio()->consultarDadosFornecedorProcesso($fornecedor);

    }//end consultarDadosFornecedorProcesso()

    /**
     * consultarAtaPorChave
     */
    public function consultarDCentroDeCustoUsuario($cgrempcodi, $cusupocodi, $corglicodi)
    {
        return $this->getNegocio()->consultarDCentroDeCustoUsuario($cgrempcodi, $cusupocodi, $corglicodi);

    }//end consultarDadosFornecedorProcesso()

    public function consultarValorMaximoDocumento($processo, $orgao, $ano, $grupo)
    {
        $db = Conexao();
        $sql = $this->sqlCodigoMaximoDocumento($processo, $orgao, $ano, $grupo);
        $resultado = executarSQL($db, $sql);
        $resultado->fetchInto($documento, DB_FETCHMODE_OBJECT);
        return $documento;
    }


    /**
     * Ataptação. Salvar
     *
     * @return boolean
     */ 
    public function salvar()
    {        
        $entidade = $this->getNegocio()
            ->getDados()
            ->getEntidade('sfpc.tbataregistroprecointerna');

        // Tabela: sfpc.tbitemataregistropreconova
        // Campos:
        // - carpnosequ = $ata                  // Código sequencial da ata de registro de preço
        // - citarpsequ = $item->sequencial     // Código Sequencial dos Itens da Ata de Registro de Preço
        // - aitarporde = $item->ordem          // Ordem do item
        // - cmatepsequ = $sequencialMaterial   // Código Sequencial do Material
        // - cservpsequ = $sequencialServico    // Código sequencial do serviço
        // - aitarpqtor = $quantidadeTotal      // Quantidade original
        // - aitarpqtat = $quantidadeTotal      // Quantidade atual
        // - vitarpvori = $valorUnitario        // Valor unitário original
        // - vitarpvatu = $valorUnitario        // Valor unitário atual
        // - citarpnuml = $item->lote           // Número do Lote
        // - fitarpsitu = 'A'                   // Situação do Item da Ata (A- Ativa / I - Inativa)
        // - fitarpincl = 'S'                   // Indica se o item foi incluído diretamente na ata de registro de preço (S- Sim / N - Não)
        // - fitarpexcl = 'N'                   // Indica se o o item foi excluído da ata de registro de preço (S- Sim / N - Não)
        // - titarpincl = now()                 // Data/Hora da Inclusão
        // - cusupocodi = $codigoUsuario        // Código do Usuário Responsável pela Última Alteração
        // - titarpulat = now()                 // Data/Hora da Última Alteração
        // - eitarpmarc = $marca                // Marca do Item
        // - eitarpmode = $modelo               // Modelo do Item

        $retorno = true;
       
        $_SESSION['requestDaVez'] = $_REQUEST;

        // Validar itens
        if(!empty($_REQUEST['itemAta'])) {
            $ordem = array();
            foreach($_REQUEST['itemAta'] as $key => $value) {
                $ItemNovo = $_REQUEST['itemAta'][$key]['citelpsequ'];
                if(empty($ItemNovo)){
                    $emptyOrdemLote = (empty($_REQUEST['itemAta'][$key]['citelpnuml']) || empty($_REQUEST['itemAta'][$key]['aitelporde'])) ? true : false; 
                    if(empty($ordem[$_REQUEST['itemAta'][$key]['citelpnuml']][$_REQUEST['itemAta'][$key]['aitelporde']])){
                        $ordem[$_REQUEST['itemAta'][$key]['citelpnuml']][$_REQUEST['itemAta'][$key]['aitelporde']] = $_REQUEST['itemAta'][$key]['aitelporde'];
                    } else {
                        $_SESSION['colecaoMensagemErro'][] = "
                        <a href='javascript:;' class='titulo2'>
                            Verifique os itens de lote " . $_REQUEST['itemAta'][$key]['citelpnuml'] ." e ordem " .$_REQUEST['itemAta'][$key]['aitelporde']." (ordem repetida)
                        </a>";
                        $retorno = false;
                     }

                    if(!empty($_REQUEST['itemAta'])) {
                        $ordem = array();
                        foreach($_REQUEST['itemAta'] as $key => $value) {
                            $processoCodi = $_POST['processo'];
                            $grupoCodigo = $_POST['VALOR_CODIGO_GRUPO'];
                            $orgaoCodigo = $_POST['orgao'];
                            $comissao =  $_GET['codigocomissao'];
                            $anoCodigo = $_POST['ano'];
                            $loteAtualSql = $_REQUEST['itemAta'][$key]['citelpnuml'];
                            $ordemAtualSql = $_REQUEST['itemAta'][$key]['aitelporde'];
                                $db=Conexao();
                                $sqlLote = "SELECT * from sfpc.tbataregistroprecointerna a left join  sfpc.tbitemataregistropreconova b on  a.carpnosequ = b.carpnosequ
                                WHERE a.clicpoproc =  $processoCodi 
                                AND a.alicpoanop =  $anoCodigo 
                                AND a.cgrempcodi =  $grupoCodigo
                                AND a.corglicodi = $orgaoCodigo
                                AND a.ccomlicodi = $comissao 
                                AND a.carpnosequ = b.carpnosequ
                                AND b.citarpnuml = $loteAtualSql
                                AND b.aitarporde = $ordemAtualSql";               
                                $result = executarTransacao($db, $sqlLote);
                                $loteExiste = $result->fetchRow();
                            
                        }
                        if(!empty($loteExiste)){
                                $_SESSION['colecaoMensagemErro'][] = "
                                <a href='javascript:;' class='titulo2'>
                                    Verifique os itens de lote " . $loteAtualSql ." e ordem " .$ordemAtualSql." (ja existe na Tabela)
                                </a>";
                                $retorno = false;
                                break;
                        }elseif(empty($loteExiste)){
                            $sqlLote = "SELECT  * from sfpc.tbitemlicitacaoportal as a
                            WHERE a.clicpoproc = $processoCodi 
                             AND a.alicpoanop =  $anoCodigo 
                             AND a.cgrempcodi =  $grupoCodigo
                             AND a.corglicodi = $orgaoCodigo
                             AND a.ccomlicodi = $comissao 
                             AND a.citelpnuml = $loteAtualSql";               
                             $result = executarTransacao($db, $sqlLote);
                             $loteExisteb = $result->fetchRow();
                             if(!empty($loteExisteb)){
                                $_SESSION['colecaoMensagemErro'][] = "
                                <a href='javascript:;' class='titulo2'>
                                    Verifique os itens de lote " .$loteAtualSql." (ja existe na Tabela)
                                </a>";
                                $retorno = false;
                                break;
                         }
                    }
                    
                }
            }

                if($emptyOrdemLote) {
                    $_SESSION['colecaoMensagemErro'][] = "<a href='javascript:;' class='titulo2'> Informe os lotes e ordem corretamente</a>";
                    $retorno = false;
                }

                // Marca
                if(empty($_REQUEST['itemAta'][$key]['eitelpmarc'])) {
                    $_SESSION['colecaoMensagemErro'][] = "
                    <a href='javascript:document.getElementById(\"itemAta[$key][eitelpmarc]\").focus();' class='titulo2'>
                        Marca do item de ordem ".$_REQUEST['itemAta'][$key]['aitelporde']." não informado
                    </a>";
                    $retorno = false;
                }
                // Modelo
                if(empty($_REQUEST['itemAta'][$key]['eitelpmode'])) {
                    $_SESSION['colecaoMensagemErro'][] = "
                    <a href='javascript:document.getElementById(\"itemAta[$key][eitelpmode]\").focus();' class='titulo2'>
                        Modelo do item de ordem ".$_REQUEST['itemAta'][$key]['aitelporde']." não informado
                    </a>";
                    $retorno = false;
                }
                // Qtd Original
                if(empty($_REQUEST['itemAta'][$key]['valor_qtd_original']) ||$_REQUEST['itemAta'][$key]['valor_qtd_original'] == '0,0000') {
                    $_SESSION['colecaoMensagemErro'][] = "
                    <a href='javascript:document.getElementById(\"itemAta[$key][valor_qtd_original]\").focus();' class='titulo2'>
                        Quantidade original do item de ordem ".$_REQUEST['itemAta'][$key]['aitelporde']." não informado
                    </a>";
                    $retorno = false;
                }
                // Valor Original Unitário
                if(empty($_REQUEST['itemAta'][$key]['valor_original_unit']) ||$_REQUEST['itemAta'][$key]['valor_original_unit'] == '0,0000') {
                    $_SESSION['colecaoMensagemErro'][] = "
                    <a href='javascript:document.getElementById(\"itemAta[$key][valor_original_unit]\").focus();' class='titulo2'>
                        Valor Original Unitário do item de ordem ".$_REQUEST['itemAta'][$key]['aitelporde']." não informado
                    </a>";
                    $retorno = false;
                }
                // Qtd Atual
                /*if(empty($_REQUEST['itemAta'][$key]['quantidade_total']) ||$_REQUEST['itemAta'][$key]['quantidade_total'] == '0,0000') {
                    $_SESSION['colecaoMensagemErro'][] = "
                    <a href='javascript:document.getElementById(\"itemAta[$key][quantidade_total]\").focus();' class='titulo2'>
                        Quantidade Atual da Ata do item de ordem ".$_REQUEST['itemAta'][$key]['aitelporde']." não informado
                    </a>";
                    $retorno = false;
                }*/
                // Valor Unitário
                /*if(empty($_REQUEST['itemAta'][$key]['valor_unitario_atual']) ||$_REQUEST['itemAta'][$key]['valor_unitario_atual'] == '0,0000') {
                    $_SESSION['colecaoMensagemErro'][] = "
                    <a href='javascript:document.getElementById(\"itemAta[$key][valor_unitario_atual]\").focus();' class='titulo2'>
                        Valor Unitário Atual do item de ordem ".$_REQUEST['itemAta'][$key]['aitelporde']." não informado
                    </a>";
                    $retorno = false;
                }*/
            }
        }

        // Tipo da ata: I = Interna e E = Externa
        // A ata externa não está sendo utilizada a pedido do cliente
        if (isset($_REQUEST['tipo'])) {
            $tipo = (int) filter_var($_REQUEST['processo'], FILTER_SANITIZE_STRING);
            if (empty($tipo) === true) {
                $_SESSION['mensagemFeedback'] = 'Tipo da ata não foi informado';
                $retorno = false;
            }
        }

        // Ano do processo licitatório
        if (isset($_REQUEST['ano'])) {
            $entidade->alicpoanop = (int) filter_var($_REQUEST['ano'], FILTER_SANITIZE_NUMBER_INT);
            if (empty($entidade->alicpoanop)) {
                $_SESSION['mensagemFeedback'] = 'Ano do processo não foi informado';
                $retorno = false;
            }
        }

        // Ano do processo licitatório
        if (isset($_REQUEST['VALOR_ATA'])) {
            $valor = (int) filter_var($_REQUEST['VALOR_ATA'], FILTER_SANITIZE_NUMBER_INT);
            if (empty($valor)) {                
                $_SESSION['colecaoMensagemErro'][] = "
                <a href='javascript:document.getElementById(\"VALOR_ATA\").focus();' class='titulo2'>
                    Número da ata não foi informado
                </a>";
                $retorno = false;
            }
        }

        // Ano do processo licitatório
        if (isset($_REQUEST['ANO_ATA'])) {
            $valor = (int) filter_var($_REQUEST['ANO_ATA'], FILTER_SANITIZE_NUMBER_INT);
            if (empty($valor)) {                
                $_SESSION['colecaoMensagemErro'][] = "
                <a href='javascript:document.getElementById(\"ANO_ATA\").focus();' class='titulo2'>
                    Ano da ata não foi informado
                </a>";
                $retorno = false;
            }
        }       

        // Vigência
        if (isset($_REQUEST['VALOR_VIGENCIA'])) {
            $valor = (int) filter_var($_REQUEST['VALOR_VIGENCIA'], FILTER_SANITIZE_NUMBER_INT);
            if (empty($valor)) {                
                $_SESSION['colecaoMensagemErro'][] = "
                <a href='javascript:document.getElementById(\"VALOR_VIGENCIA\").focus();' class='titulo2'>
                    Vigência da ata não foi informado
                </a>";
                $retorno = false;
            }
        }        

        // Objeto
        if (isset($_REQUEST['VALOR_OBJETO'])) {
            $valor = $_REQUEST['VALOR_OBJETO'];
            if (empty($valor)) {                
                $_SESSION['colecaoMensagemErro'][] = "
                <a href='javascript:document.getElementById(\"VALOR_OBJETO\").focus();' class='titulo2'>
                    Objeto da ata não foi informado
                </a>";
                $retorno = false;
            }
        }

        // Data inicial
        if (isset($_REQUEST['DataInicial'])) {
            $valor = (int) filter_var($_REQUEST['DataInicial'], FILTER_SANITIZE_NUMBER_INT);
            if (empty($valor)) {                
                $_SESSION['colecaoMensagemErro'][] = "
                <a href='javascript:document.getElementById(\"DataInicial\").focus();' class='titulo2'>
                    Data inicial da ata não foi informado
                </a>";
                $retorno = false;
            }
        }        

        // Ano do processo licitatório
        if (isset($_FILES['fileArquivo'])) {
            if($_FILES['fileArquivo']['size'] == 0 && $_SESSION['Arquivos_Upload'] == null){
                $_SESSION['colecaoMensagemErro'][] = "
                <a href='javascript:document.getElementById(\"VALOR_VIGENCIA\").focus();' class='titulo2'>
                    Nenhum documento da ata foi informado
                </a>";
                $retorno = false;
            }else{
                if(count($_SESSION['Arquivos_Upload']['nome']) <= 0){
                    $_SESSION['colecaoMensagemErro'][] = "
                    <a href='javascript:document.getElementById(\"VALOR_VIGENCIA\").focus();' class='titulo2'>
                        Nenhum documento da ata foi informado
                    </a>";
                    $retorno = false;
                }
            }   
        }

       
        // Código do Órgão Licitante
        if (isset($_REQUEST['orgao'])) {
            $entidade->corglicodi = (int) filter_var($_REQUEST['orgao'], FILTER_SANITIZE_NUMBER_INT);
            if (empty($entidade->corglicodi)) {
                $_SESSION['mensagemFeedback'] = 'Órgão do licitante não foi informado';
                $retorno = false;
            }
        }

        // Código sequencial da ata de registro de preço
        if (isset($_REQUEST['ata'])) {
            if($_REQUEST['ata'] != '' && !is_null($_REQUEST['ata'])){
                $entidade->carpnosequ = (int) filter_var($_REQUEST['ata'], FILTER_SANITIZE_NUMBER_INT);
                if (empty($entidade->carpnosequ)) {
                    $_SESSION['mensagemFeedback'] = 'Código sequencial da ata de registro de preço não foi informado';
                    $retorno = false;
                }
            }
        }

        // Código do Fornecedor Credenciado
        if (isset($_REQUEST['fornecedor'])) {
            $entidade->aforcrsequ = (int) filter_var($_REQUEST['fornecedor'], FILTER_SANITIZE_NUMBER_INT);
            if (empty($entidade->aforcrsequ)) {
                $_SESSION['mensagemFeedback'] = 'Código do fornecedor não foi informado';
                $retorno = false;
            }
        }
        if(!$retorno){
            $_SESSION['itemAta'] = $_POST['itemAta'];
            return $retorno;
        }              

		$mesmoNumero = null;
		
		
        /*if($this->contemAtaComMesmaNumeracaoEAno($_REQUEST['VALOR_ATA'] ,$_REQUEST['ANO_ATA']) ){
				$_SESSION['colecaoMensagemErro'][] = "
                    <a href='javascript:document.getElementById(\"VALOR_ATA\").focus();' class='titulo2'>
                        Já existe uma ata cadastrada com o mesmo número e ano informado para o mesmo órgão ou com a mesma comissão/processo/fornecedor/lote
                    </a>"; 
			
            return false;
        }*/      

        return $this->getNegocio()->salvar($entidade, $_POST['itemAta']);

    }//end salvar()



    public function contemAtaComMesmaNumeracaoEAno($numeracao, $ano )
    {

        $database = Conexao();
        $sql = $this->getNegocio()->getDados()->sqlConsultarAtaNumeracaoAno($numeracao, $ano, $_REQUEST['orgao']);
        $resultado = executarSQL($database, $sql);
        $documentos = array();
        $documento = null;
        
        while ($resultado->fetchInto($documento, DB_FETCHMODE_OBJECT)) {
            $documentos[] = $documento;
        }    
              
		$resultadoMesmoNumeroAta = array();
		$sql  = "SELECT COUNT(*)";
		$sql .= " FROM sfpc.tbataregistroprecointerna WHERE ";
        if(empty($_REQUEST['ata'])){   
            $sql .= "aarpinanon = ".$_REQUEST['ANO_ATA']." and corglicodi = ".$_REQUEST['orgao']." and carpincodn = ".$_REQUEST['VALOR_ATA'];   
        }else{
            $sql .= "aarpinanon = ".$_REQUEST['ANO_ATA']." and corglicodi = ".$_REQUEST['orgao']." and carpincodn = ".$_REQUEST['VALOR_ATA']." and carpnosequ <> ".$_REQUEST['ata'] ; 
        }
    
		$resultadoMesmoNumeroAta = null;
	    $ata = executarSQL($database, $sql);
		$ata->fetchInto($resultadoMesmoNumeroAta, DB_FETCHMODE_OBJECT);

		if($resultadoMesmoNumeroAta->count != '0'){
                return true;
        }
        
        $resultadoAta = array();
        $database = Conexao();
        if(empty($documentos)){
            $documentos[0]->clicpoproc = $_REQUEST['processo'];
            $documentos[0]->alicpoanop = $_REQUEST['ANO_ATA'];
            $documentos[0]->corglicodi = $_REQUEST['orgao'];
            $documentos[0]->aforcrsequ = $_REQUEST['fornecedor'];
            $documentos[0]->ccomlicodi = $_REQUEST['codigocomissao'];
            $documentos[0]->cgrempcodi = $_REQUEST['codigoGrupo'];

            $resultadoAta = $_REQUEST['itemAta'];
        }else{
            $sql = "SELECT DISTINCT itema.citarpnuml FROM sfpc.tbitemataregistropreconova itema WHERE itema.carpnosequ = ".$documentos[0]->carpnosequ;
            $resultado = executarSQL($database, $sql);
            $loteAta = null;
            while ($resultado->fetchInto($loteAta, DB_FETCHMODE_OBJECT)) {
                $resultadoAta[] = $loteAta;
            }
            
        }   
        if( empty($resultadoAta[0])){
                $i=1;
        }else{ 
                $i=0;
        }
        while ($i < count($resultadoAta)){       
            $sqlCont = null;
            $sqlCont = "SELECT count(*) FROM sfpc.tbataregistroprecointerna arpi, sfpc.tbitemataregistropreconova itema";
            $sqlCont .= " WHERE arpi.clicpoproc = ".$documentos[0]->clicpoproc;
            $sqlCont .= " and arpi.alicpoanop = ".$documentos[0]->alicpoanop;
            $sqlCont .= " and arpi.corglicodi = ".$documentos[0]->corglicodi;
            $sqlCont .= " and arpi.aforcrsequ = ".$documentos[0]->aforcrsequ;
            $sqlCont .= " and arpi.ccomlicodi = ".$documentos[0]->ccomlicodi;
            $sqlCont .= " and arpi.cgrempcodi = ".$documentos[0]->cgrempcodi;
            // Verifica se é vem de uma manutenção ou de uma inclusão de ata
            if(!empty($documentos[0]->carpnosequ)){  
                $sqlCont .= " and arpi.carpnosequ <> ".$documentos[0]->carpnosequ;
                $sqlCont .= " and itema.citarpnuml = ".$resultadoAta[$i]->citarpnuml;
            }else{
                $sqlCont .= " and itema.citarpnuml = ".$resultadoAta[$i]['citelpnuml'];
            }
            $sqlCont .= " and arpi.carpnosequ = itema.carpnosequ";

            $i++;
            $comparaLote = null;
            $resultado = executarSQL($database, $sqlCont);
            $resultado->fetchInto($comparaLote, DB_FETCHMODE_OBJECT);
          
			if($comparaLote->count != '0'){
                return true;
            }
        }
        return false;	
		
	}

		//Retirado o código abaixo da função contemAtaComMesmaNumeracaoEAno com a execução da CR 220038

		/*$ataMesmoNumero = $this->getNegocio()->consultarNumeroAtaInterna(
                        intval($_REQUEST['processo']),
                        intval($_REQUEST['ano']), 
                        intval($_REQUEST['ANO_ATA']),                         
                        intval($_REQUEST['orgao']),                         
                        intval($_REQUEST['VALOR_ATA']),
                        intval($_REQUEST['codigoGrupo']),
                        intval($_REQUEST['codigocomissao'])
						); 
		if (!empty($ataMesmoNumero)){
				$mesmoNumero = true;
				return true;
        }





        $ataC = $this->getNegocio()->consultarAtaIterna(intval($_REQUEST['processo']), intval($_REQUEST['ANO_ATA']), intval($_REQUEST['orgao']), intval($_REQUEST['fornecedor']), $_REQUEST['codigocomissao'], $_REQUEST['codigoGrupo']);
        $retorno = false;

        if(count($documentos->carpnosequ) > 1){
            $retorno = true;
        }else{
            if(!empty($documentos) && !empty($ataC)){
                if(!is_null($ataC->carpnosequ) && $documentos[0]->carpnosequ != $ataC->carpnosequ){
                    $retorno = true;
                }
            }else{
                if(empty($ataC)) {                    
                    $_ataC = $this->getNegocio()->consultarNumeroAtaInterna(
                        intval($_REQUEST['processo']),
                        intval($_REQUEST['ano']), 
                        intval($_REQUEST['ANO_ATA']),                         
                        intval($_REQUEST['orgao']),                         
                        intval($_REQUEST['VALOR_ATA']),
                        intval($_REQUEST['codigoGrupo']),
                        intval($_REQUEST['codigocomissao'])
                    ); 
                    
                    if(!empty($_ataC)) {
                        if(
                            $_REQUEST['processo']       != $_ataC->clicpoproc ||
                            $_REQUEST['fornecedor']     != $_ataC->aforcrsequ ||
                            $_REQUEST['ano']            != $_ataC->alicpoanop ||
                            $_REQUEST['ANO_ATA']        != $_ataC->aarpinanon ||
                            $_REQUEST['orgao']          != $_ataC->corglicodi ||
                            $_REQUEST['codigocomissao'] != $_ataC->ccomlicodi ||
                            $_REQUEST['codigoGrupo']    != $_ataC->cgrempcodi
                        ) {
                            return true;
                        }
                    }                

                }
                //if(count($_SESSION['Arquivos_Upload']['nome']) <= 0) {
                  //  $retorno = true;
                //} 
            }
        }
        return ($retorno);
    }
 */





    public function consultarDocumento($carpnosequ)
    {

        $database = Conexao();
        $sql = $this->getNegocio()->getDados()->sqlConsultarDocumento(new Negocio_ValorObjeto_Carpnosequ($carpnosequ));
        $resultado = executarSQL($database, $sql);
        $documentos = array();
        $documento = null;

        while ($resultado->fetchInto($documento, DB_FETCHMODE_OBJECT)) {
            $documentos[] = $documento;
        }
        return $documentos;
    }

}

/**
 * Classe RegistroPreco_UI_CadMigracaoAtaInternaAlterar
 */
class RegistroPreco_UI_CadMigracaoAtaInternaAlterar extends UI_Abstrata
{

    /**
     * Tipo da ata
     */
    private $tipo;

    /**
     * Processo licitatório da ata
     */
    private $processo;

    /**
     * Órgão da ata
     */
    private $orgao;

    /**
     * Ano da ata
     */
    private $ano;

    /**
     * Fornecedor da ata
     */
    private $fornecedor;

    /**
     * Código da ata
     */
    private $ata; 

    private $codigoComissao;

    private $codigoGrupo;    


    public function adicionarDocumento()
    {
        $_SESSION['itemAtaSubmitDocument'] = $_POST['itemAta'];
        $arquivo = new Arquivo();
        $arquivo->setExtensoes('doc,odt,pdf');
        $arquivo->setTamanhoMaximo(20000000000000000);
        $arquivo->configurarArquivo();
        unset($_FILES['fileArquivo']);

        $_SESSION['requestDaVez'] = $_REQUEST;
        
        $uri = "CadManterEspecial.php?tipo=I&ano=".$_REQUEST['ano']."&fullprocesso=".$_REQUEST['fullprocesso']."&processo=".$_REQUEST['processo']."&orgao=".$_REQUEST['orgao']."&fornecedor=".$_REQUEST['fornecedor']."&comissaocodigo=".$_REQUEST['codigocomissao']."&grupocodigo=".$_REQUEST['codigoGrupo']."&ata=".$_REQUEST['ata']."&lote=".$_REQUEST['VALOR_LOTE'];
        header('Location: ' . $uri);
        exit();
    }


    private function plotarBlocoDocumentos($ata)
    {   
        if (isset($ata->carpnosequ)) {
           $documentos = $this->getAdaptacao()->consultarDocumento($ata->carpnosequ);
            if (!empty($documentos) && !isset($_SESSION['Arquivos_Upload'])) {                
                foreach ($documentos as $documento) {
                    $documentoHexDecodificado = base64_decode($documento->idocatarqu);
                    $documentoToBin = $this->hextobin($documentoHexDecodificado);
                    $_SESSION['Arquivos_Upload']['nome'][] = $documento->edocatnome;
                    $_SESSION['Arquivos_Upload']['conteudo'][] = $documentoToBin;
                }                
            }
        }

        $this->coletarDocumentosAdicionados();
        $this->getTemplate()->block('BLOCO_FILE');
    }

    function hextobin($hexstr) 
    { 
        $n = strlen($hexstr); 
        $sbin="";   
        $i=0; 
        while($i<$n) 
        {       
            $a =substr($hexstr,$i,2);           
            $c = pack("H*",$a); 
            if ($i==0){$sbin=$c;} 
            else {$sbin.=$c;} 
            $i+=2; 
        } 
        return $sbin; 
    } 


    public function retirarDocumento()
    {
        $_SESSION['itemAtaSubmitDocument'] = $_POST['itemAta'];
        $idDocumento = filter_var($_POST['documentoExcluir'], FILTER_VALIDATE_INT);

        if (! is_int($idDocumento)) {
            throw new Exception("Error Processing Request", 1);
        }

        unset($_SESSION['Arquivos_Upload']['conteudo'][$idDocumento]);
        unset($_SESSION['Arquivos_Upload']['nome'][$idDocumento]);
        $_SESSION['Arquivos_Upload']['nome'] = array_values($_SESSION['Arquivos_Upload']['nome']);
        $_SESSION['Arquivos_Upload']['conteudo'] = array_values($_SESSION['Arquivos_Upload']['conteudo']);

        $_SESSION['requestDaVez'] = $_REQUEST;

        $uri = "CadManterEspecial.php?tipo=I&ano=".$_REQUEST['ano']."&processo=".$_REQUEST['processo']."&orgao=".$_REQUEST['orgao']."&fornecedor=".$_REQUEST['fornecedor']."&comissaocodigo=".$_REQUEST['codigocomissao']."&grupocodigo=".$_REQUEST['codigoGrupo']."&ata=".$_REQUEST['ata']."&lote=".$_REQUEST['VALOR_LOTE'];
        header('Location: ' . $uri);
        exit();        
    }


    private function coletarDocumentosAdicionados()
    {
        if (isset($_SESSION['Arquivos_Upload']['nome'])) {
            $lista = '';
            $qtdeDocumentos = sizeof($_SESSION['Arquivos_Upload']['nome']);
            for ($i = 0; $i < $qtdeDocumentos; $i ++) {
                $nomeDocumento = $_SESSION['Arquivos_Upload']['nome'][$i];
                $lista .= '<li>' . $nomeDocumento . '<input type="button" name="remover[]" value="Remover" class="botao removerDocumento" doc="' . $i . '" /></li>';
            }
            $this->getTemplate()->VALOR_DOCUMENTOS_ATA = $lista;
            //unset($_SESSION['Arquivos_Upload']);
        }
    }


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
    private function plotarBlocoBotao($ano, $orgao, $processo, $ata)
    {
        $this->getTemplate()->VALOR_ANO_SESSAO      = $ano;
        $this->getTemplate()->VALOR_ORGAO_SESSAO    = $orgao;
        $this->getTemplate()->VALOR_PROCESSO_SESSAO = $processo;
        $this->getTemplate()->VALOR_ATA_SESSAO      = $ata;
        $this->getTemplate()->DISPLAY_EXCLUIR       = (empty($ata)) ? 'display:none' : '';
        $this->getTemplate()->ESCONDER_BOTOES       = (empty($ata)) ? 'display:none' : '';
        $this->getTemplate()->block("BLOCO_BOTAO");

    }//end plotarBlocoBotao()

    /**
     *
     * @param stdClass $licitacao
     * @param stdClass $ata
     * @param unknown $dataInformada
     * @param unknown $vigenciaInformada
     */
    private function plotarBlocoLicitacao($licitacao, $ata, $dataInformada, $vigenciaInformada)
    {

        $dataHota           = new DataHora($ata->tarpindini);
        $consultarfor       = new RegistroPreco_Dados_CadMigracaoAtaInternaAlterar();
        $fornecedor         = $consultarfor->consultarDadosFornecedorProcesso($this->fornecedor);       
        $dto                = $consultarfor->consultarDCentroDeCustoUsuario($ata->cgrempcodi, $ata->cusupocodi, $this->orgao);
        $objeto             = current($dto);              
        $numeroAtaFormatado = "";
        $numeroAtaFormatado .= $objeto->ccenpocorg . str_pad($objeto->ccenpounid, 2, '0', STR_PAD_LEFT);        
        $dadosObjeto        = ($ata->earpinobje != '0') ? $ata->earpinobje : $licitacao->xlicpoobje;
        $_SESSION['numeroAtaFormatado'] = $numeroAtaFormatado.'.'.isset($_SESSION['requestDaVez']['VALOR_ATA']) ? $_SESSION['requestDaVez']['VALOR_ATA'] : str_pad($ata->carpincodn, 4, "0", STR_PAD_LEFT);
        $this->getTemplate()->VALOR_ORGAO_UNIDADE   = $numeroAtaFormatado;
        $this->getTemplate()->VALOR_ATA             = (isset($_SESSION['requestDaVez']['VALOR_ATA'])) ? $_SESSION['requestDaVez']['VALOR_ATA'] : str_pad($ata->carpincodn, 4, "0", STR_PAD_LEFT);
        $this->getTemplate()->ANO_ATA               = (isset($_SESSION['requestDaVez']['ANO_ATA'])) ? $_SESSION['requestDaVez']['ANO_ATA'] : $ata->aarpinanon;
        $this->getTemplate()->PROCESSO_LICITATORIO  =  substr($licitacao->clicpoproc + 10000, 1);
        $this->getTemplate()->COMISSAO              = $licitacao->ecomlidesc;        
        $this->getTemplate()->VALOR_ANO             = $licitacao->alicpoanop;        
        $this->getTemplate()->FORNECEDOR_ORIGINAL   = $this->getDadosFornecedorOriginal($ata); 
        $this->getTemplate()->ORGAO_ATA             = $licitacao->eorglidesc;    
        $this->getTemplate()->FORNECEDOR_ATUAL      = ''; //FIXME:// verificar com rossana como pegar esse valor
        $this->getTemplate()->VALOR_OBJETO          = (isset($_SESSION['requestDaVez']['VALOR_OBJETO'])) ? $_SESSION['requestDaVez']['VALOR_OBJETO'] : $dadosObjeto;
        $this->getTemplate()->VALOR_DATA            = (isset($_SESSION['requestDaVez']['DataInicial'])) ? $_SESSION['requestDaVez']['DataInicial'] : $dataHota->formata('d/m/Y');
        $this->getTemplate()->VALOR_VIGENCIA        = (isset($_SESSION['requestDaVez']['VALOR_VIGENCIA'])) ? $_SESSION['requestDaVez']['VALOR_VIGENCIA'] : $ata->aarpinpzvg;        
        $this->plotarBlocoDocumentos($ata);
        $this->tipoControle($ata);     
        $this->situacaoAta($ata);
        $this->ataCorporativa($ata);
        $this->getTemplate()->block("BLOCO_LICITACAO");
    }

    /**
     *
     * @param stdClass $licitacao
     * @param stdClass $ata
     * @param unknown $dataInformada
     * @param unknown $vigenciaInformada
     */
    private function plotarBlocoLicitacaoProcessoLicitatorio($licitacao, $ata, $dataInformada, $vigenciaInformada)
    {        
        $consultarfor = new RegistroPreco_Dados_CadMigracaoAtaInternaAlterar();
        $fornecedor = $consultarfor->consultarDadosFornecedorProcesso($this->fornecedor);        
        $repoCentroCusto = new Negocio_Repositorio_CentroCustoPortal();
        $dto = $consultarfor->consultarDCentroDeCustoUsuario($ata->cgrempcodi, $ata->cusupocodi, $this->orgao);        
        $objeto = current($dto);
        $numeroAtaFormatado = "";
       
        $numeroAtaFormatado .= $objeto->ccenpocorg . str_pad($objeto->ccenpounid, 2, '0', STR_PAD_LEFT);
        $_SESSION['numeroAtaFormatado'] = $numeroAtaFormatado.'.'.isset($_SESSION['requestDaVez']['VALOR_ATA']) ? $_SESSION['requestDaVez']['VALOR_ATA'] : '';   
        $this->getTemplate()->VALOR_ORGAO_UNIDADE   = $numeroAtaFormatado;
        $this->getTemplate()->VALOR_ATA             = (isset($_SESSION['requestDaVez']['VALOR_ATA'])) ? $_SESSION['requestDaVez']['VALOR_ATA'] : '';
        $this->getTemplate()->ANO_ATA               = (isset($_SESSION['requestDaVez']['ANO_ATA'])) ? $_SESSION['requestDaVez']['ANO_ATA'] : '';
        $this->getTemplate()->PROCESSO_LICITATORIO  = substr($licitacao->clicpoproc + 10000, 1);
        $this->getTemplate()->COMISSAO              = $licitacao->ecomlidesc; 
        $this->getTemplate()->VALOR_ANO             = $licitacao->alicpoanop;
        $this->getTemplate()->ORGAO_ATA             = $licitacao->eorglidesc;    
        $this->getTemplate()->FORNECEDOR_ORIGINAL   = $this->getDadosFornecedorOriginal($fornecedor);
        $this->getTemplate()->FORNECEDOR_ATUAL      = ''; //FIXME:// verificar com rossana como pegar esse valor
        $this->getTemplate()->VALOR_OBJETO          = $licitacao->xlicpoobje;
        $this->getTemplate()->VALOR_DATA            = (isset($_SESSION['requestDaVez']['DataInicial'])) ? $_SESSION['requestDaVez']['DataInicial'] : '';
        $this->getTemplate()->VALOR_VIGENCIA        = (isset($_SESSION['requestDaVez']['VALOR_VIGENCIA'])) ? $_SESSION['requestDaVez']['VALOR_VIGENCIA'] : '';
        $this->tipoControle($ata);       
        

        //como é a primeira vez nao ira ter documentos
        $this->plotarBlocoDocumentos($ata);
        $this->getTemplate()->block("BLOCO_LICITACAO");
    }

    private function tipoControle($ata) {
        $controle = selectTipoControle();
        $tipo = (isset($_SESSION['requestDaVez']['TipoControle'])) ? $_SESSION['requestDaVez']['TipoControle'] : $ata->farpnotsal;   
        
        // Tipo controle
        foreach ($controle as $key => $value) {
            $this->getTemplate()->VALOR_CONTROLE = $key;
            $this->getTemplate()->DESCRICAO_CONTROLE = $value;
            
            $this->getTemplate()->clear("VALOR_CONTROLE_SELECIONADO");
            if ($tipo == $key) {
                $this->getTemplate()->VALOR_CONTROLE_SELECIONADO = "selected";
            }
            $this->getTemplate()->block("BLOCO_TIPOCONTROLE");
        }
    }

    public function situacaoAta($ata) {
        $situacao = (isset($_SESSION['requestDaVez']['situacaoAta'])) ? $_SESSION['requestDaVez']['situacaoAta'] : $ata->farpinsitu;   
        $a = ($situacao == 'A') ? 'selected' : ''; 
        $i = ($situacao == 'I') ? 'selected' : '';

        $this->getTemplate()->VALOR_SITUACAO_A = $a;
        $this->getTemplate()->VALOR_SITUACAO_I = $i;
    }

    public function ataCorporativa($ata) {
        $situacao = (isset($_SESSION['requestDaVez']['ataCorporativa'])) ? $_SESSION['requestDaVez']['ataCorporativa'] : $ata->farpincorp;
        $n = ($situacao == 'N') ? 'selected' : '';
        $s = ($situacao == 'S') ? 'selected' : '';

        $this->getTemplate()->VALOR_CORPORATIVA_N = $n;
        $this->getTemplate()->VALOR_CORPORATIVA_S = $s;
    }
                        
    private function plotarBlocoItemAta($itens, $ata)
    {        


        global $SimboloConcatenacaoArray, $SimboloConcatenacaoDesc;
        $quantidadeItem = $_REQUEST['quantidadeItem'];

        if ($itens == null && empty($_SESSION['item'])) {
            return false;
        }
        
        $itensDaSessao = $_SESSION['item'];
        $_itens[] = $itens;
      
        $ordenacaoMaior = 0;
        foreach ($_itens[0] as $item) {
            if($ordenacaoMaior <= $item->aitarporde){
                $ordenacaoMaior = $item->aitarporde;
            }
        }
     
        // verificar o numero do lote
        $maior_ord = 0; 
        if(!empty($_itens[0])) {
            foreach ($_itens[0] as $key => $value) {
                if($value->citarpnuml == 0) {
                    $maior_ord = $value->aitarporde;
                }
            }
        } 

        // Pegar os itens da sessão para exibir na tela
        if (isset($itensDaSessao) === true) {
            foreach ($itensDaSessao as $key => $value) {
                $dadosSessaoItemQuebra = explode($SimboloConcatenacaoArray, $value);
                $idItemParaConsultar    = $dadosSessaoItemQuebra[1];
                $tipoDaVez              = $dadosSessaoItemQuebra[3];
                $dataBase = Conexao();
                $itemConsultadoCompleto = $this->getAdaptacao()->getNegocio()->consultarItemCompleto($dataBase, $idItemParaConsultar, $tipoDaVez);
                
                $novoArray = new StdClass;

                //$novoArray->aitarporde      = ($ordenacaoMaior != 0) ? $ordenacaoMaior + 1 : count($_itens[0]) + 1;
                $novoArray->aitarporde      = $maior_ord + (1 + $key);
                $novoArray->citelpnuml      = 0;
                $novoArray->citarpnuml      = 0;
                $novoArray->cmatepsequ      = $itemConsultadoCompleto->cmatepsequ;
                $novoArray->cservpsequ      = $itemConsultadoCompleto->cservpsequ;

                $novoArray->ematepdesc      = $itemConsultadoCompleto->ematepdesc;
                $novoArray->eservpdesc      = $itemConsultadoCompleto->eservpdesc;
                $novoArray->eunidmsigl      = $itemConsultadoCompleto->eunidmsigl;

                $novoArray->eitelpdescmat   = '';
                $novoArray->eitelpdescse    = '';
                
                
                $novoArray->aitelpqtso      = '';
                $novoArray->vitelpvlog      = '';
                $novoArray->vitelpunit      = '';
                
                $novoArray->citelpnuml      = 0;
                $novoArray->cmatepsitu      = '';
                $novoArray->cservpsitu      = '';
                array_push($_itens[0], $novoArray);                 
            }
        }
        
        // Remover da sessão e do itemAtaSubmitDocument para atualizar na tela
        if (!empty($_SESSION['itens_deletados']) && !empty($_SESSION['item'])) { // abaco
            $database = Conexao();
            $carpnosequ = $_REQUEST['ata'];

            foreach ($_SESSION['itens_deletados'] as $key => $itensParaDeletar) {
                $citarpsequ = $_POST['itemAta'][$itensParaDeletar]['citelpsequ'];

                if(!empty($citarpsequ)){
                    $sql  = "SELECT COUNT(*) ";
                    $sql .= "FROM   SFPC.TBITEMSOLICITACAOCOMPRA ";
                    $sql .= "WHERE  CARPNOSEQU = " . $carpnosequ;
                    $sql .= "       AND CITARPSEQU = " . $citarpsequ;
                    
    
                    $res = executarSQL($database, $sql);
                    
                    $res = $res->fetchRow();
                    $res = $res[0];
                    }
               
                if ($res == 0) {
                    foreach ($_SESSION['itens_deletados'] as $key => $value) {
                        $pos = $value - 1;
                
                        if (empty($_itens[0][$pos]->citarpsequ)) {
                            // remover da sessão de itens adicionados
                            foreach ($_SESSION['item'] as $key_ => $value_) {
                                $dadosSessaoItemQuebra = explode($SimboloConcatenacaoArray, $value_);
                                if ($dadosSessaoItemQuebra == 'M' && $dadosSessaoItemQuebra[1] == $_itens[0][$pos]->cmatepsequ) {
                                    unset($_SESSION['item'][$key_]);
                                    unset($_SESSION['itens_deletados'][$key]);
                                    $atualizarSessaoItem =- true;
                                }
                        
                                if ($dadosSessaoItemQuebra[3] == 'S' && $dadosSessaoItemQuebra[1] == $_itens[0][$pos]->cservpsequ) {
                                    unset($_SESSION['item'][$key_]);
                                    unset($_SESSION['itemAtaSubmitDocument'][$value]);
                                    $atualizarSessaoItem = true;
                                }
                            }

                            unset($_itens[0][$pos]);
                            unset($_SESSION['itens_deletados'][$key]);
                        }
                    }
                } else {
                    $_SESSION['itens_deletados'] = null;
                    $Mens = 1;
                    $Tipo = 2;
                    $_SESSION['colecaoMensagemErro'][] = "Item associado a uma SARP, em qualquer situação, não pode ser removido";
                }
            }
        }
        // todos deveriam está no mesmo lote
        $ultimoLote = null;
        $itensDeletados = $_SESSION['itens_deletados'];
        $contador = 1;  
     
        foreach ($_itens[0] as $key => $item) {
          

            if (empty($itensDeletados) === true || in_array($contador, $itensDeletados) === false) {
                $ultimoLote = empty($ultimoLote) ? $item->lote : $ultimoLote;
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

                $valorDescricaoDetalhadaDb = $item->eitelpdescmat; 
                if ($tipo === 'servico') {
                    $valorDescricaoDetalhadaDb = $item->eitelpdescse;
                }        

                // Situação do item
                $situacao = $item->cmatepsitu;
                if ($tipo === 'servico') {
                    $situacao = $item->cservpsitu;
                }

                // Verificação para compo descição detalhada
                $exibeCampoDescDet = false;
                if ($tipo === 'servico' || ($tipo == 'material' && $itemConsultadoCompleto->fmatepgene == 'S')) {
                    $exibeCampoDescDet = true;
                }

                // Valor total
                $valorTotal = ($item->aitelpqtso * $item->vitelpvlog);

                $tipoFinal = ($tipo == 'material') ? 'CADUM' : 'CADUS';

                $ordenacao_ata = !empty($item->aitelporde) ? $item->aitelporde : $item->aitarporde;
                $ordenacao_inputs = $key + 1;
               
                $this->getTemplate()->VALOR_SEQITEM             = ($item->citelpsequ == null) ? $item->citarpsequ : $item->citelpsequ;

                // Adicionar itens da sessão quando incluir documento                
                if(!empty($_SESSION['itemAtaSubmitDocument'])) {
                    $this->getTemplate()->VALOR_MARCA   = empty($_SESSION['itemAtaSubmitDocument'][$ordenacao_inputs]['eitelpmarc']) ? $_SESSION['itemAtaSubmitDocument'][$ordenacao_inputs]['eitarpmarc'] : $_SESSION['itemAtaSubmitDocument'][$ordenacao_inputs]['eitelpmarc'];                    
                    $this->getTemplate()->VALOR_MODELO  = empty($_SESSION['itemAtaSubmitDocument'][$ordenacao_inputs]['eitelpmode']) ? $_SESSION['itemAtaSubmitDocument'][$ordenacao_inputs]['eitarpmode'] : $_SESSION['itemAtaSubmitDocument'][$ordenacao_inputs]['eitelpmode']; 

                    $this->getTemplate()->VALOR_MARCA2  = empty($_SESSION['itemAtaSubmitDocument'][$ordenacao_inputs]['eitelpmarc']) ? $_SESSION['itemAtaSubmitDocument'][$ordenacao_inputs]['eitarpmarc'] : $_SESSION['itemAtaSubmitDocument'][$ordenacao_inputs]['eitelpmarc'];                    
                    $this->getTemplate()->VALOR_MODELO2 = empty($_SESSION['itemAtaSubmitDocument'][$ordenacao_inputs]['eitelpmode']) ? $_SESSION['itemAtaSubmitDocument'][$ordenacao_inputs]['eitarpmode'] : $_SESSION['itemAtaSubmitDocument'][$ordenacao_inputs]['eitelpmode']; 
                }             
               

                $this->getTemplate()->VALOR_TIPO            = $tipoFinal;         // Código Sequencial do Material OU 
                
                $flagExibirCampoProcesso = false;
                if(isset($item->tipoItem)){
                    if($item->tipoItem == "ITEMPROCESSO"){
                        $flagExibirCampoProcesso = true;
                    }
                }

                // TODO: Validar com Rossana se o nome da coluna será CADUS mesmo, pois CADUS é para serviço e CADUM para material.cservpsequ
                $this->getTemplate()->VALOR_CADUS       = $valorCodigo;         // Código Sequencial do Material OU Código sequencial do serviço
                $this->getTemplate()->VALOR_ID_ITEM     = $valorCodigo;
                $this->getTemplate()->VALOR_DESCRICAO   = $valorDescricao;      // Descrição do material ou serviço

                if($flagExibirCampoProcesso && false){                    
                    //$ordenacao_inputs = $key + 1;
                    $qdtOriginal = (isset($item->aitarpqtor)) ? $item->aitarpqtor : $item->aitelpqtso;
                    //madson #227471
                    $_SESSION['descricao_detalhada_m'][$contador] = $valorDescricaoDetalhadaDb;
                    $this->getTemplate()->VALOR_DESCRICAO_DETALHADA2 = $valorDescricaoDetalhadaDb;
                    $this->getTemplate()->VALOR_DESCRICAO_DETALHADA = !empty($valorDescricaoDetalhadaDb) ? $valorDescricaoDetalhadaDb : ' - ';
                    $this->getTemplate()->VALOR_QTD_ORIGINAL    = converte_valor_estoques($qdtOriginal);
                    $this->getTemplate()->VALOR_ORIGINAL_UNIT   = converte_valor_estoques($item->vitelpvlog);
                   
                }else{
                    $ordenacao = $item->aitarporde;                                     
                    $ordenacao_inputs = $key + 1;
                        if($valorDescricaoDetalhada === null)
                        $valorDescricaoDetalhada = $item->eitarpdescmat; 
                        if ($tipo === 'servico') {                        
                            $valorDescricaoDetalhada = $item->eitarpdescse;
                        }
                

                    // Verificar dados da sessão
                    if(isset($_SESSION['itemAta'])){
                        $valorDescricaoDetalhada = $_SESSION['itemAta'][$ordenacao_inputs]['valor_descricao_detalhada']; 
                        $this->getTemplate()->VALOR_DESCRICAO_DETALHADA2 = $valorDescricaoDetalhada;  
                        $item->aitarpqtor = str_replace(',','.',str_replace('.','',$_SESSION['itemAta'][$ordenacao_inputs]['valor_qtd_original']));
                        $item->vitarpvori = str_replace(',','.',str_replace('.','',$_SESSION['itemAta'][$ordenacao_inputs]['valor_original_unit']));
                    } else if(isset($_SESSION['itemAtaSubmitDocument'])) {
                        $valorDescricaoDetalhada = $_SESSION['itemAtaSubmitDocument'][$ordenacao_inputs]['valor_descricao_detalhada'];
                        $this->getTemplate()->VALOR_DESCRICAO_DETALHADA2 = $valorDescricaoDetalhada;
                        $item->aitarpqtor = str_replace(',','.',str_replace('.','',$_SESSION['itemAtaSubmitDocument'][$ordenacao_inputs]['valor_qtd_original']));
                        $item->vitarpvori = str_replace(',','.',str_replace('.','',$_SESSION['itemAtaSubmitDocument'][$ordenacao_inputs]['valor_original_unit']));
                    } else if(empty($item->carpnosequ)){   
                        $aitarpqtor = (isset($item->aitarpqtor)) ? $item->aitarpqtor : $item->aitelpqtso;
                        $item->aitarpqtor = str_replace(',','.',$aitarpqtor);
                        $item->vitarpvori = str_replace(',','.',$item->vitelpvlog);
                        $valorDescricaoDetalhada = !empty($valorDescricaoDetalhadaDb) ? $valorDescricaoDetalhadaDb : ' - ';   
                         //madson #227471
                        //madson Ele implementa a SESSION
                        $_SESSION['descricao_detalhada_m'][$contador] = $valorDescricaoDetalhadaDb;
                        $this->getTemplate()->VALOR_DESCRICAO_DETALHADA2 = $valorDescricaoDetalhadaDb;
                    }  

                    // Adicionar itens da sessão quando incluir documento
                             
                    if($exibeCampoDescDet) {  
                        $this->getTemplate()->VALOR_DESCRICAO_DETALHADA = '<textarea style="text-transform: uppercase;" name="itemAta['.$ordenacao_inputs.'][valor_descricao_detalhada]" cols="30"
                        rows="4" class="textonormal">'.$valorDescricaoDetalhada.'</textarea>';
                        $valorDescricaoDetalhada = '';
                        
                    } else {
                        $this->getTemplate()->VALOR_DESCRICAO_DETALHADA = !empty($valorDescricaoDetalhada) ? $valorDescricaoDetalhada : ' - ';
                         //madson #227471
                        //madson Ele implementa a SESSION
                        $_SESSION['descricao_detalhada_m'][$contador] = $valorDescricaoDetalhada;
                        $this->getTemplate()->VALOR_DESCRICAO_DETALHADA2 = $valorDescricaoDetalhada;
                        unset($valorDescricaoDetalhada);
                    }            
                    
                    //Pegar campo Novo contendo a descricao
                    $this->getTemplate()->VALOR_QTD_ORIGINAL    = "<input value='".converte_valor_estoques($item->aitarpqtor)."' id='itemAta[".$ordenacao_inputs."][valor_qtd_original]' name='itemAta[".$ordenacao_inputs."][valor_qtd_original]' class='dinheiro4casas' size='5' onblur="."javascript:AtualizarValorTotal('itemAta[".$ordenacao_inputs."][valor_qtd_original]','itemAta[".$ordenacao_inputs."][valor_original_unit]','totalValorItem[".$ordenacao_inputs."]');"."  >";

                    $this->getTemplate()->VALOR_ORIGINAL_UNIT    = "<input value='".converte_valor_estoques($item->vitarpvori)."' id='itemAta[".$ordenacao_inputs."][valor_original_unit]' name='itemAta[".$ordenacao_inputs."][valor_original_unit]' class='dinheiro4casas' size='5' onblur="."javascript:AtualizarValorTotal('itemAta[".$ordenacao_inputs."][valor_qtd_original]','itemAta[".$ordenacao_inputs."][valor_original_unit]','totalValorItem[".$ordenacao_inputs."]');"."  >";
                    
                    $valorTotal = ($item->aitarpqtor * $item->vitarpvori);

                    // Adicionar itens da sessão quando incluir documento
                    if(!empty($_SESSION['itemAtaSubmitDocument'])) {                       
                        $_marca = empty($_SESSION['itemAtaSubmitDocument'][$ordenacao_inputs]['eitelpmarc']) ? $_SESSION['itemAtaSubmitDocument'][$ordenacao_inputs]['eitarpmarc'] : $_SESSION['itemAtaSubmitDocument'][$ordenacao_inputs]['eitelpmarc'];
                        $_modelo = empty($_SESSION['itemAtaSubmitDocument'][$ordenacao_inputs]['eitelpmode']) ? $_SESSION['itemAtaSubmitDocument'][$ordenacao_inputs]['eitarpmode'] : $_SESSION['itemAtaSubmitDocument'][$ordenacao_inputs]['eitelpmode'];
                        $this->getTemplate()->VALOR_MARCA    = '<textarea style="text-transform: uppercase;" id="itemAta['.$ordenacao_inputs.'][eitelpmarc]" name="itemAta['.$ordenacao_inputs.'][eitelpmarc]" cols="10" rows="4" class="textonormal">'.$_marca.'</textarea>';
                        $this->getTemplate()->VALOR_MODELO   = '<textarea style="text-transform: uppercase;" id="itemAta['.$ordenacao_inputs.'][eitelpmode]" name="itemAta['.$ordenacao_inputs.'][eitelpmode]" cols="10" rows="4" class="textonormal">'.$_modelo.'</textarea>';
                        $this->getTemplate()->VALOR_ORD_ITEM = $_SESSION['itemAtaSubmitDocument'][$ordenacao_inputs]['aitelporde'];
                        $this->getTemplate()->VALOR_LOTE     = $_SESSION['itemAtaSubmitDocument'][$ordenacao_inputs]['citelpnuml'];
                    } else if(isset($_SESSION['itemAta'])){ 
                        $this->getTemplate()->VALOR_MARCA    = '<textarea style="text-transform: uppercase;" id="itemAta['.$ordenacao_inputs.'][eitelpmarc]" name="itemAta['.$ordenacao_inputs.'][eitelpmarc]" cols="10" rows="4" class="textonormal">'.$item->eitarpmarc.'</textarea>';
                        $this->getTemplate()->VALOR_MODELO   = '<textarea style="text-transform: uppercase;" id="itemAta['.$ordenacao_inputs.'][eitelpmode]" name="itemAta['.$ordenacao_inputs.'][eitelpmode]" cols="10" rows="4" class="textonormal">'.$item->eitarpmode.'</textarea>';
                        $this->getTemplate()->VALOR_ORD_ITEM = $ordenacao_ata;
                        $this->getTemplate()->VALOR_LOTE     = ($item->citelpnuml == null) ? $item->citarpnuml : $item->citelpnuml;                
                    } else {
                        $valorMarca = ($item->eitelpmarc == null) ? $item->eitarpmarc : $item->eitelpmarc;
                        $valorModelo = ($item->eitelpmode == null) ? $item->eitarpmode : $item->eitelpmode;                    
                        $this->getTemplate()->VALOR_MARCA    = '<textarea style="text-transform: uppercase;" id="itemAta['.$ordenacao_inputs.'][eitelpmarc]" name="itemAta['.$ordenacao_inputs.'][eitelpmarc]" cols="10" rows="4" class="textonormal">'.$valorMarca.'</textarea>';;
                        $this->getTemplate()->VALOR_MODELO   = '<textarea style="text-transform: uppercase;" id="itemAta['.$ordenacao_inputs.'][eitelpmode]" name="itemAta['.$ordenacao_inputs.'][eitelpmode]" cols="10" rows="4" class="textonormal">'.$valorModelo.'</textarea>';
                        $this->getTemplate()->VALOR_MARCA2   = ($item->eitelpmarc == null) ? $item->eitarpmarc : $item->eitelpmarc;
                        $this->getTemplate()->VALOR_MODELO2  = ($item->eitelpmode == null) ? $item->eitarpmode : $item->eitelpmode;
                        $this->getTemplate()->VALOR_ORD_ITEM = $ordenacao_ata;
                        $this->getTemplate()->VALOR_LOTE     = ($item->citelpnuml == null) ? $item->citarpnuml : $item->citelpnuml;                
                    }

                }  

                $this->getTemplate()->VALOR_ORD         = $contador;          // Contador do número de itens

                // Verificar dados da sessão
                $_vitelpunit = (isset($_SESSION['itemAta'][$ordenacao_inputs]['valor_unitario_atual'])) ? str_replace(',','.',$_SESSION['itemAta'][$ordenacao_inputs]['valor_unitario_atual']) : $item->vitelpunit;

                // TODO: Validar com Rossana de onde vai pegar esse campo
                $this->getTemplate()->VALOR_UND             = $item->eunidmsigl;                                
                $this->getTemplate()->VALOR_TOTAL           = converte_valor_estoques($valorTotal);
                $this->getTemplate()->VALOR_QTD_TOTAL       = "<input value='".converte_valor_estoques(0)."' id='itemAta[".$ordenacao_inputs."][quantidade_total]' name='itemAta[".$ordenacao_inputs."][quantidade_total]' class='dinheiro4casas' size='5' onblur="."javascript:AtualizarValorTotal('itemAta[".$ordenacao_inputs."][quantidade_total]','itemAta[".$ordenacao_inputs."][valor_unitario_atual]','totalUnitarioItem[".$ordenacao_inputs."]');"." >";
                //$this->getTemplate()->VALOR_UNITARIO_ATUAL = converte_valor_estoques($_vitelpunit);
                $this->getTemplate()->VALOR_UNITARIO_ATUAL  = "<input value='".converte_valor_estoques(0)."' id='itemAta[".$ordenacao_inputs."][valor_unitario_atual]' name='itemAta[".$ordenacao_inputs."][valor_unitario_atual]' class='dinheiro4casas' size='5' onblur="."javascript:AtualizarValorTotal('itemAta[".$ordenacao_inputs."][quantidade_total]','itemAta[".$ordenacao_inputs."][valor_unitario_atual]','totalUnitarioItem[".$ordenacao_inputs."]');"."  >";
                $VALOR_UNITARIO_ATUAL = '';
                $this->getTemplate()->QTDATUAL_X_VLUNITARIOATUAL = converte_valor_estoques(0);

                $codigoReduzido = $item->cservpsequ;
                $codSeqItem = $item->citarpsequ;
                
                if(isset($item->cmatepsequ) === true) {
                    $codigoReduzido = $item->cmatepsequ;
                }
              
                $db = Conexao();
                $itemSessao = false;
                if($ata->carpnosequ != null){
                    $itemConsultado = $this->getAdaptacao()->getNegocio()->consultarItemAta($db, $ata->carpnosequ, $codigoReduzido, $tipoFinal, $codSeqItem);

                    if(isset($itemConsultado)){
                        // Verificar dados da sessão
                        if(isset($_SESSION['itemAtaSubmitDocument'])) {
                            $_aitarpqtat = (isset($_SESSION['itemAtaSubmitDocument'][$ordenacao_inputs]['quantidade_total'])) ? str_replace(',','.',str_replace('.','',$_SESSION['itemAtaSubmitDocument'][$ordenacao_inputs]['quantidade_total'])) : $itemConsultado->aitarpqtat;
                            $_vitarpvatu = (isset($_SESSION['itemAtaSubmitDocument'][$ordenacao_inputs]['valor_unitario_atual'])) ? str_replace(',','.',str_replace('.','',$_SESSION['itemAtaSubmitDocument'][$ordenacao_inputs]['valor_unitario_atual'])) : $itemConsultado->vitarpvatu;
                            $_fitarpsitu = (isset($_SESSION['itemAtaSubmitDocument'][$ordenacao_inputs]['situacao'])) ? $_SESSION['itemAtaSubmitDocument'][$ordenacao_inputs]['situacao'] : $itemConsultado->fitarpsitu;
                        } else {
                            $_aitarpqtat = (isset($_SESSION['itemAta'][$ordenacao_inputs]['quantidade_total'])) ? str_replace(',','.',str_replace('.','',$_SESSION['itemAta'][$ordenacao_inputs]['quantidade_total'])) : $itemConsultado->aitarpqtat;
                            $_vitarpvatu = (isset($_SESSION['itemAta'][$ordenacao_inputs]['valor_unitario_atual'])) ? str_replace(',','.',str_replace('.','',$_SESSION['itemAta'][$ordenacao_inputs]['valor_unitario_atual'])) : $itemConsultado->vitarpvatu;
                            $_fitarpsitu = (isset($_SESSION['itemAta'][$ordenacao_inputs]['situacao'])) ? $_SESSION['itemAta'][$ordenacao_inputs]['situacao'] : $itemConsultado->fitarpsitu;
                        }

                        $this->getTemplate()->VALOR_QTD_TOTAL = "<input value='".converte_valor_estoques($_aitarpqtat)."' id='itemAta[".$ordenacao_inputs."][quantidade_total]' name='itemAta[".$ordenacao_inputs."][quantidade_total]' class='dinheiro4casas' size='5' onblur="."javascript:AtualizarValorTotal('itemAta[".$ordenacao_inputs."][quantidade_total]','itemAta[".$ordenacao_inputs."][quantidade_total]','totalUnitarioItem[".$ordenacao_inputs."]');"."  >";
                        $this->getTemplate()->VALOR_UNITARIO_ATUAL = "<input value='".converte_valor_estoques($_vitarpvatu)."' id='itemAta[".$ordenacao_inputs."][valor_unitario_atual]' name='itemAta[".$ordenacao_inputs."][valor_unitario_atual]' class='dinheiro4casas' size='5' onblur="."javascript:AtualizarValorTotal('itemAta[".$ordenacao_inputs."][quantidade_total]','itemAta[".$ordenacao_inputs."][valor_unitario_atual]','totalUnitarioItem[".$ordenacao_inputs."]');"."  >";
                        $VALOR_UNITARIO_ATUAL = '';
                        $this->getTemplate()->QTDATUAL_X_VLUNITARIOATUAL = converte_valor_estoques($_aitarpqtat * $_vitarpvatu);

                        $situacao = $_fitarpsitu;
                    } else {
                        $itemSessao = true;
                    }
                   
                } else {
                    $itemSessao = true;
                }

                // Verificar os valores enviados por post dos itens da sessão
                if($itemSessao) {
                    if(isset($_SESSION['itemAtaSubmitDocument'][$ordenacao_inputs]['quantidade_total'])) {
                        $_aitarpqtat = (isset($_SESSION['itemAtaSubmitDocument'][$ordenacao_inputs]['quantidade_total'])) ? str_replace(',','.',str_replace('.','',$_SESSION['itemAtaSubmitDocument'][$ordenacao_inputs]['quantidade_total'])) : $itemConsultado->aitarpqtat;
                        $_vitarpvatu = (isset($_SESSION['itemAtaSubmitDocument'][$ordenacao_inputs]['valor_unitario_atual'])) ? str_replace(',','.',str_replace('.','',$_SESSION['itemAtaSubmitDocument'][$ordenacao_inputs]['valor_unitario_atual'])) : $itemConsultado->vitarpvatu;
                        $_fitarpsitu = (isset($_SESSION['itemAtaSubmitDocument'][$ordenacao_inputs]['situacao'])) ? $_SESSION['itemAtaSubmitDocument'][$ordenacao_inputs]['situacao'] : $itemConsultado->fitarpsitu;
                        $this->getTemplate()->VALOR_QTD_TOTAL = "<input value='".converte_valor_estoques($_aitarpqtat)."' id='itemAta[".$ordenacao_inputs."][quantidade_total]' name='itemAta[".$ordenacao_inputs."][quantidade_total]' class='dinheiro4casas' size='5' onblur="."javascript:AtualizarValorTotal('itemAta[".$ordenacao_inputs."][quantidade_total]','itemAta[".$ordenacao_inputs."][valor_unitario_atual]','totalUnitarioItem[".$ordenacao_inputs."]');"."  >";
                        $this->getTemplate()->VALOR_UNITARIO_ATUAL = "<input value='".converte_valor_estoques($_vitarpvatu)."' id='itemAta[".$ordenacao_inputs."][valor_unitario_atual]' name='itemAta[".$ordenacao_inputs."][valor_unitario_atual]' class='dinheiro4casas' size='5' onblur="."javascript:AtualizarValorTotal('itemAta[".$ordenacao_inputs."][quantidade_total]','itemAta[".$ordenacao_inputs."][valor_unitario_atual]','totalUnitarioItem[".$ordenacao_inputs."]');"."  >";
                        $situacao = $_fitarpsitu;
                        $VALOR_UNITARIO_ATUAL='';
                        $valorTotal = $_aitarpqtat * $_vitarpvatu;
                        $this->getTemplate()->QTDATUAL_X_VLUNITARIOATUAL           = converte_valor_estoques($valorTotal);
                    }
                }
               
                if ($situacao === 'I' || (isset($_SESSION['itemAta'][$ordenacao_inputs]['situacao']) && $_SESSION['itemAta'][$ordenacao_inputs]['situacao'] == 'I')) {
                    $situacao_ativo      = '';
                    $situacao_inativo    = 'selected';
                } else {
                    $situacao_ativo  = 'selected';
                    $situacao_inativo    = '';
                }

                $this->getTemplate()->SELECT_SITUACAO = "<select name='itemAta[".$ordenacao_inputs."][situacao]'>
                    <option ".$situacao_ativo." value='A'>ATIVO</option>
                    <option ".$situacao_inativo." value='I'>INATIVO</option>
                </select>";

                $this->getTemplate()->block("BLOCO_ITEM");

                if (! empty($item->participantes)) {
                    foreach ($item->participantes as $participante) {
                        $quantidadeInformada = $_REQUEST['itemOrgao[' . $item->ordem . '][' . $orgao->sequencial . ']'];

                        $this->getTemplate()->VALOR_SEQ_ORGAO               = $participante->sequencial;
                        $this->getTemplate()->VALOR_ORGAO_ITEM              = $participante->descricao;
                        $this->getTemplate()->VALOR_QUANTIDADE_ITEM_ORGAO   = $quantidadeInformada == null ? $participante->quantidadeItem : $quantidadeInformada;
                        $this->getTemplate()->block("BLOCO_ORGAO_ITEM");
                    }
                }

                $this->getTemplate()->block("BLOCO_RESULTADO_ATAS");
                $this->getTemplate()->block("BLOCO_ITEM_TOTAL");
            }
            $contador ++;

       } 
    }


    /**
     * Retira um ou mais intens da ata
     */
    public function retirarItem()
    {
        $database = Conexao();
        $_SESSION['itemAtaSubmitDocument'] = $_POST['itemAta'];
        $itensDeletados = array();
        $itens_esconder = array();
        

        if (empty($_SESSION['itens_deletados']) === false) {
            $itensDeletados = $_SESSION['itens_deletados'];
        }

        if (empty($_SESSION['itens_esconder']) === false) {
            $itens_esconder = $_SESSION['itens_esconder'];
        }

        if (!empty($_POST['idItem'])) {
            $itens = $_POST['idItem'];
            foreach($itens as $value) {
                array_push($itensDeletados, $value);
                array_push($itens_esconder, $_POST['itemAta'][$value]['citelpsequ']);
            }
            $_SESSION['itens_deletados'] = $itensDeletados;
            $_SESSION['itens_esconder'] = $itens_esconder;
           



            $carpnosequ = $_REQUEST['ata'];
        $processoCodi = $_POST['processo'];
        $grupoCodigo = $_POST['VALOR_CODIGO_GRUPO'];
        $orgaoCodigo = $_POST['orgao'];
        $comissao =  $_GET['codigocomissao'];
        $anoCodigo = $_POST['ano'];
        
            $sql = " SELECT ilp.citelpsequ
            FROM sfpc.tbitemlicitacaoportal ilp INNER JOIN 
                    (SELECT arpi.clicpoproc, iarpn.cmatepsequ, arpi.alicpoanop, arpi.ccomlicodi, arpi.corglicodi, 
                            arpi.cgrempcodi, iarpn.citarpitel, arpi.carpnosequ, arpi.aarpinanon, iarpn.citarpnuml,
                            arpi.carpincodn, fc1.nforcrrazs, fc1.aforcrsequ
            FROM sfpc.tbitemataregistropreconova iarpn
            LEFT JOIN sfpc.tbataregistroprecointerna arpi ON 
                        iarpn.carpnosequ = arpi.carpnosequ
            LEFT JOIN sfpc.tbfornecedorcredenciado fc1 ON 
                    arpi.aforcrsequ = fc1.aforcrsequ
                WHERE arpi.clicpoproc = ".$processoCodi."
                        AND arpi.alicpoanop = ".$anoCodigo ."
                        AND arpi.ccomlicodi = ".$comissao ."
                        AND arpi.corglicodi = ".$orgaoCodigo."
                        AND arpi.carpnosequ = ".$carpnosequ."
                        AND arpi.cgrempcodi = ".$grupoCodigo .") AS ata ON 
                            ata.citarpitel = ilp.citelpsequ
                            AND ata.clicpoproc = ilp.clicpoproc 
                            AND ata.alicpoanop = ilp.alicpoanop 
                            AND ata.cgrempcodi = ilp.cgrempcodi 
                            AND ata.ccomlicodi = ilp.ccomlicodi 
                            AND ata.corglicodi = ilp.corglicodi
            INNER JOIN sfpc.tbfornecedorcredenciado fc ON 
                    ilp.aforcrsequ = fc.aforcrsequ
            WHERE ilp.clicpoproc = ".$processoCodi."
                AND ilp.alicpoanop = ".$anoCodigo ."
                AND ilp.cgrempcodi = ".$grupoCodigo ."
                AND ilp.ccomlicodi = ".$comissao ."
                AND ilp.corglicodi = ".$orgaoCodigo."
            ORDER BY ata.aarpinanon DESC, ata.carpincodn ASC";
            $res = executarSQL($database, $sql);
            $numeroItem = array();
        
            while ($res->fetchInto($numeroItem, DB_FETCHMODE_OBJECT)) {
                $itensLicitacao[] = $numeroItem;
                $contador = count($itensLicitacao);
            }
            foreach($_SESSION['itens_deletados'] as $key => $itensParaDeletar){
                $numeroItemDel = $_POST['itemAta'][$itensParaDeletar]['citelpsequ'];
                    $qntdItensDeletados = count($_SESSION['itens_deletados']);
                    
                foreach($itensLicitacao as $key=>$itens){
                    $verificarParaExcluir = $itens;
                    if(!empty($numeroItemDel)){
                    if($contador == 1 || $numeroItemDel == $verificarParaExcluir){
                        $_SESSION['colecaoMensagemErro'][] = "
                        <a href='javascript:;' class='titulo2'>
                           Não é possivel excluir o ultimo item que veio junto a licitção!
                        </a>";
                        unset($_SESSION['itens_esconder']);
                        unset( $_SESSION['itens_deletados']);
                    }
                }
                    if($numeroItemDel == $verificarParaExcluir || $qntdItensDeletados > 1){
                        $_SESSION['colecaoMensagemErro'][] = "
                        <a href='javascript:;' class='titulo2'>
                           Não é possivel excluir o mais de um item que veio junto a licitção ao mesmo tempo, itens originarios da licitação excluir  separadamente dos demais lote: ".$_POST['itemAta'][$itensParaDeletar]['citelpnuml']." ordem: ".$_POST['itemAta'][$itensParaDeletar]['aitelporde']." !
                        </a>";
                        unset($_SESSION['itens_esconder']);
                        unset( $_SESSION['itens_deletados']);
                        break;
                    }
                  
                }
            }
           


            
        }
    }


    /**
     *
     * @return boolean
     */
    private function validarOrgao()
    {
        $this->orgao = isset($_GET['orgao']) ? filter_var($_GET['orgao'], FILTER_SANITIZE_NUMBER_INT) : null;
        if (! filter_var($this->orgao, FILTER_VALIDATE_INT)) {
            $_SESSION['mensagemFeedback'] = 'Órgão não foi informado';
            return false;        
        }
        return true;
    }

    /**
     *
     * @return boolean
     */
    private function validarAno()
    {
        $this->ano = isset($_GET['ano']) ? filter_var($_GET['ano'], FILTER_SANITIZE_NUMBER_INT) : null;
        if (! filter_var($this->ano, FILTER_VALIDATE_INT)) {
            $_SESSION['mensagemFeedback'] = 'Ano não foi informado';
            return false;
        }

        return true;
    }

    /**
     *
     * @return boolean
     */
    private function validarProcesso()
    {
        $this->processo = isset($_GET['processo']) ? filter_var($_GET['processo'], FILTER_SANITIZE_NUMBER_INT) : null;
        if (! filter_var($this->processo, FILTER_VALIDATE_INT)) {
            $_SESSION['mensagemFeedback'] = 'Processo não foi informado';
            return false;
        }

        $this->codigoComissao = isset($_GET['comissaocodigo']) ? filter_var($_GET['comissaocodigo'], FILTER_SANITIZE_NUMBER_INT) : null;
        if (! filter_var($this->codigoComissao, FILTER_VALIDATE_INT)) {
            $_SESSION['mensagemFeedback'] = 'Comissão  não foi informada';
            return false;
        }

        
        $this->codigoGrupo = isset($_SESSION['grupocodigo']) ? filter_var($_SESSION['grupocodigo'], FILTER_SANITIZE_NUMBER_INT) : null;
        
        /*if(is_null($this->codigoGrupo) && !empty($_GET['codigoGrupo'])) {
            $this->codigoGrupo = isset($_GET['codigoGrupo']) ? filter_var($_GET['codigoGrupo'], FILTER_SANITIZE_NUMBER_INT) : null;
        }*/

        if (! filter_var($this->codigoGrupo, FILTER_VALIDATE_INT)) {
            $_SESSION['mensagemFeedback'] = 'Grupo não foi informado';
            return false;
        }

        return true;
    }

    /**
     *
     * @return boolean
     */
    private function validarTipo()
    {
        $this->tipo = isset($_GET['tipo']) ? filter_var($_GET['tipo'], FILTER_SANITIZE_STRING) : null;
        if (! $this->tipo) {
            $_SESSION['mensagemFeedback'] = 'Tipo não foi informado';
            return false;
        }
        return true;
    }

    /**
     *
     * @return boolean
     */
    private function validarFornecedor()
    {
                
        $this->fornecedor = isset($_GET['fornecedor']) ? filter_var($_GET['fornecedor'], FILTER_SANITIZE_NUMBER_INT) : null;
                
        if (! filter_var($this->fornecedor, FILTER_VALIDATE_INT)) {
            $_SESSION['mensagemFeedback'] = 'Fornecedor não foi informado';
            return false;
        }
        
        return true;
    }

    /**
     * getParametros
     *
     * Define os parêmtros do programa
     */
    private function getParametros()
    {
        if (
                !$this->validarOrgao() ||
                !$this->validarAno() ||
                !$this->validarProcesso() ||
                !$this->validarTipo() ||
                !$this->validarFornecedor()
            ) {
                return false;
        }
    }

    private function getDadosFornecedorOriginal($ata)
    {
        $numeroInscricaoFornecedorOriginal = (! empty($ata->aforcrccgc)) ? $ata->aforcrccgc : $ata->aforcrccpf;

        $dadosFornecedorOriginal = Helper_RegistroPreco::montarDadosDoFornecedorDaAta($numeroInscricaoFornecedorOriginal, $ata->nforcrrazs, $ata->eforcrlogr, $ata->aforcrnume, $ata->eforcrbair, $ata->nforcrcida, $ata->cforcresta);

       return $dadosFornecedorOriginal;
    }

    private function getDadosFornecedorAtual($ata)
    {
        $dadosFornecedorAtual = null;

        if (isset($ata->razaofornecedoratual) && ! empty($ata->razaofornecedoratual)) {
            $numeroInscricaoFornecedorAtual = (! empty($ata->cgcfornecedoratual)) ? $ata->cgcfornecedoratual : $ata->cpffornecedoratual;

            $dadosFornecedorAtual = Helper_RegistroPreco::montarDadosDoFornecedorDaAta($numeroInscricaoFornecedorAtual, $ata->razaofornecedoratual, $ata->logradourofornecedoratual, $ata->numeroenderecofornecedoratual, $ata->bairrofornecedoratual, $ata->cidadefornecedoratual, $ata->estadofornecedoratual);
        } else {
            $dadosFornecedorAtual = $this->getDadosFornecedorOriginal($ata);
        }

        return $dadosFornecedorAtual;
    }

    /**
     *
     * @param stdClass $ata
     */
    private function getNumeroAtaInterna($ata)
    {
        
        $db = Conexao();
        $numeroAta          = getNumeroSolicitacaoCompra($db, $ata->csolcosequ);
        $valoresExploded    = explode(".", $numeroAta);

        $numeroAtaFormatado = $valoresExploded[0];

        $numeroAtaFormatado .= "." . str_pad($ata->carpincodn, 4, "0", STR_PAD_LEFT) . "/" . $ata->aarpinanon;

        return $numeroAtaFormatado;
    }

    /**
     */
    public function __construct()
    {
        $template = new TemplatePaginaPadrao("templates/CadManterEspecial.html", "Registro de Preço > Manter Especial  > Manter");
        $template->NOMEPROGRAMA = 'CadManterEspecial';
        $this->setTemplate($template);
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see UI_Abstrata::getAdaptacao()
     */
    public function getAdaptacao()
    {
        $this->setAdaptacao(new RegistroPreco_Adaptacao_CadMigracaoAtaInternaAlterar());
        return parent::getAdaptacao();
    }

    public function processVoltar()
    {
        $uri = 'CadManterEspecialSelecionar.php';
        unset($_SESSION['mensagemFeedback']);
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
        unset($_FILES['fileArquivo']);
        unset($_SESSION['orgaos']);
        
        $fullprocesso = (isset($_GET['fullprocesso'])) ? $_GET['fullprocesso'] : $_POST['fullprocesso'];

        if(isset($_POST['itemAta'])) {
            $_SESSION['itemAtaSubmitDocument'] = $_POST['itemAta'];
        }

        if ($_SESSION['colecaoMensagemErro'] != null) {
            $this->mensagemSistema(implode(', ', $_SESSION['colecaoMensagemErro']), 1, 0);
        }
    
        
        // Remover itens do banco // abaco
        if (isset($_SESSION['itens_deletados'])) {
            $database = Conexao();
            $carpnosequ = $_REQUEST['ata'];

            foreach ($_SESSION['itens_deletados'] as $key => $itensParaDeletar) {
                $citarpsequ = $_POST['itemAta'][$itensParaDeletar]['citelpsequ'];

                if(!empty($citarpsequ)){
                    $sql  = "SELECT COUNT(*) ";
                    $sql .= "FROM   SFPC.TBITEMSOLICITACAOCOMPRA ";
                    $sql .= "WHERE  CARPNOSEQU = " . $carpnosequ;
                    $sql .= "       AND CITARPSEQU = " . $citarpsequ;
    
                    $res = executarSQL($database, $sql);
                    
                    $res = $res->fetchRow();
                    $res = $res[0];
                    }

                if ($res == 0) {
                    $ordernar = false;

                    if (!empty($_REQUEST['ata'])) {
                        foreach ($_SESSION['itens_deletados'] as $key => $itensParaDeletar) {
                            if (!empty($_POST['itemAta'][$itensParaDeletar]['citelpsequ'])) {
                                $this->getAdaptacao()->retirarItemAta($_POST['itemAta'][$itensParaDeletar]['citelpsequ']);

                                unset($_SESSION['itemAtaSubmitDocument'][$itensParaDeletar]);
                                unset($_SESSION['itens_deletados'][$key]);

                                $ordernar = true;
                            }
                        }
                    } else {
                        if (empty($_SESSION['item'])) {
                            foreach ($_SESSION['itens_deletados'] as $key => $itensParaDeletar) {
                                if (!empty($_POST['itemAta'][$itensParaDeletar])) {
                                    unset($_SESSION['itemAtaSubmitDocument'][$itensParaDeletar]);
                                    unset($_SESSION['itens_deletados'][$key]);

                                    $ordernar = true;
                                }
                            }
                        }
                    }

                    if ($ordernar) {
                        $ini = 1;
                        $tmp = $_SESSION['itemAtaSubmitDocument'];
                        $_SESSION['itemAtaSubmitDocument'] = array();

                        foreach ($tmp as $key => $value) {
                            $_SESSION['itemAtaSubmitDocument'][$ini] = $value;
                            $ini++;
                        }
                    }
                } else {
                    $_SESSION['itens_deletados'] = null;
                    $Mens = 1;
                    $Tipo = 2;
                    $_SESSION['colecaoMensagemErro'][] = "Item associado a uma SARP, em qualquer situação, não pode ser removido";
                }
            }
        }

        if (isset($_GET['grupocodigo'])) {
            if ($_GET['grupocodigo'] != '') {
                $_SESSION['grupocodigo'] = $_GET['grupocodigo'];
            }
        } elseif (isset($_SESSION['grupocodigo'])) {
            $this->codigoGrupo = $_SESSION['grupocodigo'];
        }
        
        // Lote
        if(isset($_GET['lote'])){
            $this->lote = $_GET['lote'];
            $_SESSION['lote'] = $_GET['lote'];
        } elseif (isset($_SESSION['lote'])) {
            $this->lote = $_SESSION['lote'];
        }

        // Define os parâmetros do sistema
        $this->getParametros();

        $this->fornecedor = ($this->fornecedor != null) ? $this->fornecedor : $_REQUEST['fornecedor'];
        $carpnosequ = isset($_REQUEST['ata']) ? $_REQUEST['ata'] : null;
        $this->codigoComissao = ($this->codigoComissao != null) ? $this->codigoComissao : $_REQUEST['codigocomissao'];

        $itensAta = null;

        // Consulta a ata
        if (!empty($carpnosequ)) {
            $ata = new stdClass();
            $ata->carpnosequ = $carpnosequ; // Transformar em objeto para não ter que alterar outras funções
        } else {
            // Para Plotar na tela ele puxa dessa sql  Eliakim Ramos
            $ata = $this->getAdaptacao()->sqlConsultarItensProcesso($this->processo, $this->ano, $this->codigoComissao, $this->orgao, $this->fornecedor, $this->lote);
        }

        $licitacao = $this->getAdaptacao()->consultarLicitacaoAtaInterna($this->ano, $this->processo, $this->orgao, $this->codigoComissao, $this->codigoGrupo);

        $this->plotarBlocoBotao($this->ano, $this->orgao, $this->processo, $ata->carpnosequ);
      

        if(!empty($carpnosequ)){ //madson Se a ata tive sido salva
            $atas = $this->getAdaptacao()->consultarAtaPorChave($this->processo, $this->ano, $this->orgao, $ata->carpnosequ);         
            $this->plotarBlocoLicitacao($licitacao, $atas, null, null);
            $itensAta = $this->getAdaptacao()->consultarItensAta($this->ano, $carpnosequ);
        
        } else { //madson Se a ata não tive sido salva
            $this->plotarBlocoLicitacaoProcessoLicitatorio($licitacao, $ata, null, null);
            $itensAta = $this->getAdaptacao()->sqlConsultarItensProcesso($this->processo, $this->ano, $this->codigoComissao, $this->orgao, $this->fornecedor, $this->lote);            
    
        }

        
      
        $this->plotarBlocoItemAta($itensAta, $ata);
        $this->getTemplate()->TIPO              = $this->tipo;
        $this->getTemplate()->ANO               = $this->ano;
        $this->getTemplate()->PROCESSO          = $this->processo;
        $this->getTemplate()->FULLPROCESSO      = $fullprocesso;
        $this->getTemplate()->ORGAO             = $this->orgao;
        $this->getTemplate()->ATA               = $ata->carpnosequ;
        $this->getTemplate()->FORNECEDOR        = $this->fornecedor;
        $this->getTemplate()->CODIGOCOMISSAO    = $this->codigoComissao;
        $this->getTemplate()->CODIGOGRUPO       = $this->codigoGrupo;
        $this->getTemplate()->LOTE              = $this->lote;

        if (!empty($carpnosequ)) {
            $uriAdicionarParticipante = "../registropreco/CadManterEspecialParticipante.php?orgao=$this->orgao&ano=$this->ano&processo=$this->processo&ata=$carpnosequ&fornecedor=$this->fornecedor&tipo=I&comissaocodigo=$this->codigoComissao&grupo=$this->codigoGrupo";
            $uriAdicionarCarona = "../registropreco/CadManterEspecialCarona.php?orgao=$this->orgao&ano=$this->ano&processo=$this->processo&ata=$carpnosequ&fornecedor=$this->fornecedor&tipo=$this->tipo";
        } else {
            $uriAdicionarParticipante = $uriAdicionarCarona = '';
        }
        $this->getTemplate()->JANELA_ADICIONAR_PARTICIPANTE = $uriAdicionarParticipante;        
        $this->getTemplate()->JANELA_ADICIONAR_CARONA = $uriAdicionarCarona;
    }//end proccessPrincipal()


    /**
     * UI. Salvar.
     *
     * @return void
     */
    public function salvar()
    {
        $_SESSION['itemAtaSubmitDocument'] = $_POST['itemAta'];
        if (!$this->getAdaptacao()->salvar()) {
            //$this->mensagemSistema(implode(', ', $_SESSION['colecaoMensagemErro']), 1, 0);
            
            $uri = basename($_SERVER['REQUEST_URI']);
            
            header('Location: ' . $uri);
            exit();
            
            //$this->proccessPrincipal();
            //return;
        }

        $_SESSION['mensagemFeedbackTipo']   = 1;
        $_SESSION['mensagemFeedback']       = 'Dados salvos com sucesso';

        header('Location: CadManterEspecialSelecionar.php');
        exit();

    }//end salvar()
    
    public function excluir()
    {
        $fullprocesso   = $_GET['fullprocesso'];
        $ata            = $_GET['ata'];
        $quebraProcesso = explode("-", $fullprocesso);
        $codProcesso    = $quebraProcesso[0];
        $codOrgao       = $quebraProcesso[4];
    
        if($this->verificarExcluir($ata)){        
            $database = Conexao();
            $database->autoCommit(false);
            $database->query("BEGIN TRANSACTION");

            try {                
                $database->query(sprintf("UPDATE sfpc.tbataregistroprecointerna SET carpnoseq1 = NULL WHERE carpnoseq1 = %d  ", $ata));    
                $database->query(sprintf("DELETE FROM sfpc.tbparticipanteitematarp WHERE carpnosequ = %d  ", $ata));    
                $database->query(sprintf("DELETE FROM sfpc.tbparticipanteatarp WHERE carpnosequ = %d", $ata));        
                $database->query(sprintf("DELETE FROM sfpc.tbitemcaronainternaatarp WHERE carpnosequ = %d", $ata));        
                $database->query(sprintf("DELETE FROM sfpc.tbcaronainternaatarp WHERE carpnosequ = %d", $ata));        
                $database->query(sprintf("DELETE FROM sfpc.tbcaronaorgaoexternoitem WHERE carpnosequ = %d", $ata));
                $database->query(sprintf("DELETE FROM sfpc.tbcaronaorgaoexterno WHERE carpnosequ = %d", $ata));
                $database->query(sprintf("DELETE FROM sfpc.tbitemataregistropreconova WHERE carpnosequ = %d", $ata));
                $database->query(sprintf("DELETE FROM sfpc.tbdocumentoatarp WHERE carpnosequ = %d  ", $ata));
                $database->query(sprintf("DELETE FROM sfpc.tbataregistroprecointerna WHERE carpnosequ = %d", $ata));
                $database->query(sprintf("DELETE FROM sfpc.tbataregistropreconova WHERE carpnosequ = %d", $ata));
                $database->query("COMMIT");
                $database->query("END TRANSACTION");
                $_SESSION['mensagemFeedbackTipo']   = 1;
                $_SESSION['mensagemFeedback'] = 'Ata excluída com sucesso';
            } catch (Exception $e) {
                $semerror = false;
                $database->query("ROLLBACK");
                $_SESSION['mensagemFeedback'] = 'Erro ao excluir ata';
                ExibeErroBD("\nLinha: ".__LINE__."\nSql: " . $e->getMessage());
            }
            
            $database->disconnect();
            //$_SESSION['mensagemFeedbackTipo']   = 1;
            //$_SESSION['mensagemFeedback'] = 'Ata excluída com sucesso';
            $uri = "CadManterEspecialSelecionar.php";
        
        } else {
            $_SESSION['colecaoMensagemErro'][] = "Esta ata possui SCC cadastrada e não pode ser excluída";
            $uri = basename($_SERVER['REQUEST_URI']);
        }
        
        header('Location: ' . $uri);
        exit();

    }

    /**
     * Verificação da ata para excluir
     * 
     * @var $ata
     * @return void
     */ 
    public function verificarExcluir($ata) {
        $verificarScc = $itensAta = $this->getAdaptacao()->consultarSccExcluir($ata);
        if($verificarScc->count == 0) {
            return true;
        }

        return false;
    }

}

/**
 * Classe CadMigracaoAtaInternaAlterar
 */
class CadMigracaoAtaInternaAlterar
{

    private function transformarItem($item, $ata)
    {
        $itemSessao = new stdClass();
        $itemSessao->ordem = $item->aitarporde;
        $itemSessao->descricao = ($item->cmatepsequ == null) ? $item->eservpdesc : $item->ematepdesc;
        $itemSessao->tipo = ($item->cmatepsequ == null) ? 'CADUS' : 'CADUM';
        $itemSessao->codigoReduzido = ($item->cmatepsequ == null) ? $item->cservpsequ : $item->cmatepsequ;
        $itemSessao->lote = $item->citarpnuml;
        $itemSessao->siglaUnidade = ($item->cmatepsequ == null) ? 'UN' : $item->eunidmsigl;
        $itemSessao->quantidadeTotal = converte_valor_estoques($item->aitarpqtor);
        $itemSessao->participantes = $this->listarOrgaosPorItem($ata, $item->citarpsequ);

        return $itemSessao;
    }

    private function montarTela()
    {
        $ano = $this->variables['post']['ano'];
        $orgao = $this->variables['post']['orgao'];
        $processo = $this->variables['post']['processo'];
        $ata = $this->variables['post']['ata'];

        $this->plotarBlocoBotao($ano, $orgao, $processo, $ata);
        if($ata){
            $atas = $this->consultarAtaPorChave($processo, $ano, $orgao, $ata);
            $licitacao = $this->consultarLicitaçãoAtaInterna($ano, $processo, $orgao);

            $dada = $_REQUEST["data"];
            $vigencia = $_REQUEST["vigencia"];

            $this->plotarBlocoLicitacao($licitacao, $atas, $dada, $vigencia);
        }
       
        
    }

    private function removeDocumento()
    {
        $this->files = $_SESSION['files'];
        array_pop($this->files);

        $_SESSION['files'] = $this->files;
    }

    private function getCodigoUsuarioLogado()
    {
        return (integer) $this->variables['session']['_cusupocodi_'];
    }

    private function validarQuantidades($db, $ata, $item, $participante)
    {
        $colunaTipoItem = null;

        if ($item->tipo == 'CADUM') {
            $colunaTipoItem = 'cmatepsequ';
        } else {
            $colunaTipoItem = 'cservpsequ';
        }

        $sql = "SELECT
				    isc.$colunaTipoItem, isc.aitescqtso
				FROM
				    sfpc.tbsolicitacaocompra sc
				    INNER JOIN sfpc.tbitemsolicitacaocompra isc
				    	ON isc.csolcosequ = sc.csolcosequ
				    	AND isc.$colunaTipoItem = $item->sequencial
				WHERE
				    sc.carpnosequ = $ata
				    AND sc.fsolcorpcp IS NOT NULL
					AND sc.csitsocodi != 10
					AND sc.corglicodi = $participante->sequencial";

        $resultado = executarSQL($db, $sql);
        $resultado->fetchInto($itemConsumido, DB_FETCHMODE_OBJECT);

        if ($itemConsumido != null) {
            if (moeda2float($participante->quantidadeItem) < $itemConsumido->quantidade) {
                $msg = 'Quantidade a ser diminuída para o item "%s" é menor que o saldo da quantidade do órgão "%s"';
                $msgFormatada = sprintf($msg, $item->descricao, $participante->descricao);

                throw new RuntimeException($msgFormatada);
            }
        }
    }

    private function inserirParticipante($db, $ata, $participante, $codigoUsuario)
    {
        $sequencialOrgao = $participante->sequencial;
        $excluido = $participante->inativo;

        $sql = "INSERT INTO
    				sfpc.tbparticipanteatarp
					(carpnosequ, corglicodi, fpatrpexcl, cusupocodi, tpatrpulat)
				VALUES
    				($ata, $sequencialOrgao, $excluido, $codigoUsuario, now())";

        $resultado = $db->query($sql);
    }

    private function atualizarParticipante($db, $ata, $participante, $codigoUsuario)
    {
        $sequencialOrgao = $participante->sequencial;
        $excluido = $participante->inativo;

        $sql = "UPDATE
    				sfpc.tbparticipanteatarp
				SET
					fpatrpexcl='$excluido', cusupocodi=$codigoUsuario, tpatrpulat=now()
				WHERE
    				carpnosequ=$ata AND corglicodi=$sequencialOrgao";

        $resultado = $db->query($sql);
    }

    private function salvarItemAta($db, $ata, $item)
    {               
        $itemNoBanco = $this->consultarItemAta($db, $ata, $item->codigoReduzido, $item->tipo);

        $resultado = null;

        if ($itemNoBanco == null || $_REQUEST['ata'] == '') {
            $resultado = $this->inserirItem($db, $ata, $item);
        } else {
            $resultado = $this->atualizarItem($db, $ata, $item);
        }

        if (PEAR::isError($resultado)) {
            throw new RuntimeException($resultado->getMessage());
        }
       
    }

    private function inserirItem($db, $ata, $item)
    {

        // Tabela: sfpc.tbitemataregistropreconova
        // Campos:
        // - carpnosequ = $ata                  // Código sequencial da ata de registro de preço
        // - citarpsequ = $item->sequencial     // Código Sequencial dos Itens da Ata de Registro de Preço
        // - aitarporde = $item->ordem          // Ordem do item
        // - cmatepsequ = $sequencialMaterial   // Código Sequencial do Material
        // - cservpsequ = $sequencialServico    // Código sequencial do serviço
        // - aitarpqtor = $quantidadeTotal      // Quantidade original
        // - aitarpqtat = $quantidadeTotal      // Quantidade atual
        // - vitarpvori = $valorUnitario        // Valor unitário original
        // - vitarpvatu = $valorUnitario        // Valor unitário atual
        // - citarpnuml = $item->lote           // Número do Lote
        // - fitarpsitu = 'A'                   // Situação do Item da Ata (A- Ativa / I - Inativa)
        // - fitarpincl = 'S'                   // Indica se o item foi incluído diretamente na ata de registro de preço (S- Sim / N - Não)
        // - fitarpexcl = 'N'                   // Indica se o o item foi excluído da ata de registro de preço (S- Sim / N - Não)
        // - titarpincl = now()                 // Data/Hora da Inclusão
        // - cusupocodi = $codigoUsuario        // Código do Usuário Responsável pela Última Alteração
        // - titarpulat = now()                 // Data/Hora da Última Alteração
        // - eitarpmarc = $marca                // Marca do Item
        // - eitarpmode = $modelo               // Modelo do Item

        //die('inserirItem');

        $sequencialMaterial = 'null';
        $sequencialServico  = 'null';
        $quantidadeTotal    = (($item->quantidadeTotal == null) || ($item->quantidadeTotal == '')) ? 0 : moeda2float($item->quantidadeTotal);
        $valorUnitario      = (($item->valorUnitario == null) || ($item->valorUnitario == '')) ? 0 : moeda2float($item->valorUnitario);
        $marca              = $item->marca == null ? 'null' : $item->marca;
        $modelo             = $item->modelo == null ? 'null' : $item->modelo;
        $codigoUsuario      = $this->getCodigoUsuarioLogado();

        if ($item->tipo == 'CADUM') {
            $sequencialMaterial = $item->codigoReduzido;
        } else {
            $sequencialServico = $item->codigoReduzido;
        }
        $marca = str_replace('®','',$marca);
        $modelo = str_replace('®','',$modelo);
        $sql  = "INSERT INTO ";
        $sql .= "sfpc.tbitemataregistropreconova ";
        $sql .= "(";
        $sql .= "carpnosequ, ";
        $sql .= "citarpsequ, ";
        $sql .= "aitarporde, ";
        $sql .= "cmatepsequ, ";
        $sql .= "cservpsequ, ";
        $sql .= "aitarpqtor, ";
        $sql .= "aitarpqtat, ";
        $sql .= "vitarpvori, ";
        $sql .= "vitarpvatu, ";
        $sql .= "citarpnuml, ";
        $sql .= "fitarpsitu, ";
        $sql .= "fitarpincl, ";
        $sql .= "fitarpexcl, ";
        $sql .= "titarpincl, ";
        $sql .= "cusupocodi, ";
        $sql .= "titarpulat, ";
        $sql .= "eitarpmarc, ";
        $sql .= "eitarpmode, ";
        $sql .= "citarpitel ";
        $sql .= ")";
        $sql .= "VALUES ";
        $sql .= "(";
        $sql .= "$ata, ";
        $sql .= "$item->sequencial, ";
        $sql .= "$item->ordem, ";
        $sql .= "$sequencialMaterial, ";
        $sql .= "$sequencialServico, ";
        $sql .= "$quantidadeTotal, ";
        $sql .= "$quantidadeTotal, ";
        $sql .= "$valorUnitario, ";
        $sql .= "$valorUnitario, ";
        $sql .= "$item->lote, ";
        $sql .= "'A', ";
        $sql .= "'S', ";
        $sql .= "'N', ";
        $sql .= "now(), ";
        $sql .= "$codigoUsuario, ";
        $sql .= "now(), ";
        $sql .= "$marca, ";
        $sql .= "$modelo, ";
        $sql .= !empty($item['citelpsequ']) ? $item['citelpsequ'] : 'null';
        $sql .= ")";
   
        $resultado = $db->query($sql);
        return $resultado;
    }

    private function atualizarItem($db, $ata, $item)
    {
        $quantidadeTotal = (($item->quantidadeTotal == null) || ($item->quantidadeTotal == '')) ? 0 : moeda2float($item->quantidadeTotal);
        
        $codigoUsuario = $this->getCodigoUsuarioLogado();
        $valorUnitario = (($item->valorUnitario == null) || ($item->valorUnitario == '')) ? 0 : moeda2float($item->valorUnitario);

        $sql = "UPDATE
    				sfpc.tbitemataregistropreconova
				SET
    				aitarpqtor=$quantidadeTotal, fitarpsitu='$item->situacao',
	    			vitarpvori=$valorUnitario, cusupocodi=$codigoUsuario,
	    			titarpulat=now()
				WHERE
    				carpnosequ=$ata
    				AND citarpsequ=$item->sequencial";
                   ;

        $resultado = $db->query($sql);
        return $resultado;
    }

    private function antigo_salvar_()
    {
        //verificaraqui
        $db = Conexao();
        $ano = $_REQUEST['ano'];
        $processo = $_REQUEST['processo'];
        $orgao = $_REQUEST['orgao'];
        $ata = $_REQUEST['ata'];

        foreach ($_SESSION['itens'] as $item) {
            foreach ($item->participantes as $participante) {
                // $this->validarQuantidades($db,$ata,$item,$participante);
            }
        }

        $db = Conexao();
        $db->autoCommit(false);
        $db->query("BEGIN TRANSACTION");

        try {
            
            foreach ($_SESSION['itens'] as $item) {
                $this->salvarItemAta($db, $ata, $item);
                $this->salvarParticipante($db, $ata, $item);
            }

            $db->query("COMMIT");
            $db->query("END TRANSACTION");

            $_SESSION['mensagemFeedback'] = 'Dados salvos com sucesso';
        } catch (Exception $e) {
            $db->query("ROLLBACK");
            $_SESSION['mensagemFeedback'] = 'Erro ao salvar dados';
            ExibeErroBD("\nLinha: " . __LINE__ . "\nSql: " . $e->getMessage());
        }

        $db->disconnect();
    }

    private function salvarItemParticipante($db, $ata, $item, $participante)
    {
        $this->validarQuantidades($db, $ata, $item, $participante);

        $itemDoParticipanteNoBanco = $this->consultarItemDoParticipante($db, $ata, $participante->sequencial, $item->sequencial);
        $resultado = null;

        if ($itemDoParticipanteNoBanco == null) {
            $resultado = $this->inserirItemDoParticipante($db, $ata, $participante, $item);
        } else {
            $resultado = $this->atualizarItemDoParticipante($db, $ata, $participante, $item);
        }

        if (PEAR::isError($resultado)) {
            throw new RuntimeException($resultado->getMessage());
        }
    }

    private function salvarParticipante($db, $ata, $item)
    {
        foreach ($item->participantes as $participante) {
            $participanteNoBanco = $this->consultarParticipanteAta($db, $ata, $participante);
            $resultado = null;
            $codigoUsuario = $this->getCodigoUsuarioLogado();

            if ($participanteNoBanco == null) {
                $resultado = $this->inserirParticipante($db, $ata, $participante, $codigoUsuario);
            } else {
                $resultado = $this->atualizarParticipante($db, $ata, $participante, $codigoUsuario);
            }

            if (PEAR::isError($resultado)) {
                throw new RuntimeException($resultado->getMessage());
            }
          
            $this->salvarItemParticipante($db, $ata, $item, $participante);
        }
    }

    private function listarOrgaosPorItem($sequencialAta, $sequencialItemAta)
    {
        $database = Conexao();
        $sql = $this->sqlSelectOrgaosPorItem($sequencialAta, $sequencialItemAta);
        $resultado = executarSQL($database, $sql);
        $participantes = array();
        $orgao = null;

        while ($resultado->fetchInto($orgao, DB_FETCHMODE_OBJECT)) {
            $participantes[] = $orgao;
        }
        return $participantes;
    }

    private function montaValoresInsercaoDocumento($processo, $orgao, $ano, $grupo, $comissao)
    {
        $timestamp = date('U');
        $swatch = date('B');

        $now = $timestamp . $swatch;

        $docCodMAx = 1;
        $docNome = "Documento.txt";
        $valores = $processo . "," . $ano . "," . $grupo . "," . $comissao . "," . $orgao . "," . $docCodMAx . "," . $docNome . "," . $timestamp . "," . $_SESSION['_cusupocodi_'] . "," . $now;
    }

    private function processVoltar()
    {
        $uri = 'CadAtaRegistroPrecoInternaManterEspecial.php';
        header('location: ' . $uri);
    }

    private function oldfuncao()
    {   
        if (! empty($_SERVER['item'])) {
        sort($_SESSION['item']);
        for ($i = 0; $i < count($_SESSION['item']); $i ++) {
        $DadosSessao = explode($this->variables['separatorArray'], $_SESSION['item'][$i]);

        $ItemCodigo = $DadosSessao[1];
        $ItemTipo = $DadosSessao[3];

        if (! empty($DadosSessao[2])) {
        // verificando se item já existe
        /* */
        $itemJaExiste = false;

        $sql = " select m.ematepdesc, u.eunidmsigl
        from SFPC.TBmaterialportal m, SFPC.TBunidadedemedida u
        where m.cmatepsequ = " . $ItemCodigo . "
        and u.cunidmcodi = m.cunidmcodi
        ";

        $database = Conexao();
        $res = $database->query($sql);
        if (PEAR::isError($res)) {
            EmailErroSQL("Erro em SQL", __FILE__, __LINE__, "Erro em SQL", $sql, $res);
        }

        $Linha = $res->fetchRow();
        $MaterialDescricao = $Linha[0];
        $MaterialUnidade = $Linha[1];

        $pos = count($materiais);

        $materiais = new stdClass();

        $materiais->ordem = $pos + 1;
        $materiais->descricao = $MaterialDescricao;
        $materiais->tipo = TIPO_ITEM_MATERIAL;
        $materiais->codigoReduzido = $ItemCodigo;
        $materiais->lote = 1;
        $materiais->siglaUnidade = $MaterialUnidade;
        $materiais->quantidadeTotal = converte_valor_estoques(0);
        $materiais->valorUnitario = $DadosSessao[4];
        $materiais->marca = $DadosSessao[5];
        $materiais->modelo = $DadosSessao[6];
        $_SESSION['itens'][] = $materiais;

        // [/CUSTOMIZAÇÃO]
        } else {

        // verificando se item já existe

        $itemJaExiste = false;
        $qtdeServicos = count($_SESSION['itens']);

        for ($i2 = 0; $i2 < $qtdeServicos; $i2 ++) {
            if ($ItemCodigo == $_SESSION['itens']->codigoReduzidoi) {
                $itemJaExiste = true;
            }
        }

        // incluindo item
        if (! $itemJaExiste) {
        $sql = " select m.eservpdesc from SFPC.TBservicoportal m where m.cservpsequ = " . $ItemCodigo . " ";

        $database = Conexao();
        $res = $database->query($sql);
        if (PEAR::isError($res)) {
            EmailErroSQL("Erro em SQL", __FILE__, __LINE__, "Erro em SQL", $sql, $res);
        }

        $Linha = $res->fetchRow();
        $Descricao = $Linha[0];

        $servicos = new stdClass();
        $servicos->ordem = $pos + 1;
        $servicos->descricao = $Descricao;
        $servicos->tipo = 'CADUS';
        $servicos->codigoReduzido = $ItemCodigo;
        $servicos->lote = 1;
        $servicos->siglaUnidade = 'UN';
        $servicos->quantidadeTotal = converte_valor_estoques(0);

        $_SESSION['itens'][] = $servicos;
        }
        }
        }

        unset($_SESSION['item']);
        }

        if (empty($_SESSION['itens'])) {
        $itens = $this->getAdaptacao()->consultarItemAta($atas->carpnosequ, $atas->aarpinanon);
        foreach ($itens as $item) {
        $itemNovo = $this->transformarItem($item, $ata);
        $_SESSION['itens'][] = $itemNovo;
        }
        }

        $this->plotarBlocoItemAta($_SESSION['itens'], $ata);
    }
}

$programa   = new RegistroPreco_UI_CadMigracaoAtaInternaAlterar();
$botao      = isset($_POST['Botao']) ? $_POST['Botao'] : 'Principal';

switch ($botao) {
    case 'Voltar':
        $programa->processVoltar();
        break;
    case 'Excluir':
        $programa->excluir();
        break;
    case 'Salvar':
        $programa->salvar();
        //$programa->proccessPrincipal();
        break;
    case 'RetirarItem':
        $programa->retirarItem();
        $programa->proccessPrincipal();
        break;
    case 'RetirarDocumento':
        $programa->RetirarDocumento();
        //$programa->proccessPrincipal();
        break;
    case 'Inserir':
        $programa->adicionarDocumento();        
    case 'Principal':
    default:
        $programa->proccessPrincipal();
        break;
}
echo $programa->getTemplate()->show();

if(!empty($_SESSION['itemAta'])) {
    unset($_SESSION['itemAta']);
}

if(!empty($_SESSION['itemAtaSubmitDocument'])) {
    unset($_SESSION['itemAtaSubmitDocument']);
}

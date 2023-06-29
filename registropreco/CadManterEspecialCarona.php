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
 */

#----------------------------------------------------------------------------------
# Alterado: Caio Coutinho - Pitang Agile TI
# Data:     08/11/2018
# Objetivo: Tarefa Redmine 205436
#----------------------------------------------------------------------------------
# Alterado: Caio Coutinho - Pitang Agile TI
# Data:     06/12/2018
# Objetivo: Tarefa Redmine 207946
#----------------------------------------------------------------------------------
# Alterado: Caio Coutinho - Pitang Agile TI
# Data:     12/12/2018
# Objetivo: Tarefa Redmine 207316
#----------------------------------------------------------------------------------
# Alterado: Caio Coutinho - Pitang Agile TI
# Data:     19/12/2018
# Objetivo: Tarefa Redmine 206574
#----------------------------------------------------------------------------------
# Alterado: Caio Coutinho - Pitang Agile TI
# Data:     18/02/2019
# Objetivo: Tarefa Redmine 211230
#----------------------------------------------------------------------------------
# Alterado: João Madson
# Data:     16/09/2019
# Objetivo: Tarefa Redmine 222533
#----------------------------------------------------------------------------------
# Alterado: João Madson
# Data:     17/09/2019
# Objetivo: Tarefa Redmine 223217
# ----------------------------------------------------------------------------------
# Alterado: João Madson
# Data:     19/11/2019
# Objetivo: Tarefa Redmine 226147
# ----------------------------------------------------------------------------------
# Alterado: Osmar Celestino
# Data:     01/09/2021
# Objetivo: Tarefa Redmine 252240
# ----------------------------------------------------------------------------------
# Alterado : Osmar Celestino
# Data: 25/03/2022
# Objetivo: CR #261161
#---------------------------------------------------------------------------


// 220038--

if (!@require_once dirname(__FILE__) . "/../bootstrap.php") {
    throw new Exception("Error Processing Request - Bootstrap", 1);
}

Seguranca();

global $SimboloConcatenacaoArray, $SimboloConcatenacaoDesc;

if (!empty($_SESSION['mensagemFeedback'])) {    //Condição para chegagem de mensagem de erro indevidamente vindo de outra pag. e limpar o campo de mensagem. |Madson|
    if ($_SESSION['conferePagina'] != 'carona') {
        unset($_SESSION['mensagemFeedback']);
        unset($_SESSION['conferePagina']);
    }
}
/**
 * Classe RegistroPreco_Dados_CadMigracaoAddCarona
 */
class RegistroPreco_Dados_CadMigracaoAddCarona extends Dados_Abstrata
{

    /**
     * sqlAtaPorchave
     *
     *
     *
     */
    private function sqlAtaPorchave($processo, $orgao, $ano, $chaveAta)
    {
        $sql  = "select a.carpincodn, a.earpinobje, a.aarpinanon, a.aarpinpzvg, a.tarpindini, a.cgrempcodi, a.cusupocodi, f.nforcrrazs, d.edoclinome,";
        $sql .= " a.corglicodi, a.carpnosequ, a.alicpoanop, s.csolcosequ, a.aarpinanon, carpnoseq1, ";

        $sql .= " f.nforcrrazs, f.aforcrccgc, f.aforcrccpf, f.eforcrlogr, ";
        $sql .= " f.aforcrnume, f.eforcrbair, f.nforcrcida, f.cforcresta, ";

        $sql .= " fa.nforcrrazs as razaoFornecedorAtual, fa.aforcrccgc as cgcFornecedorAtual, fa.aforcrccpf as cpfFornecedorAtual, fa.eforcrlogr as logradouroFornecedorAtual, ";
        $sql .= " fa.aforcrnume as numeroEnderecoFornecedorAtual, fa.eforcrbair as bairroFornecedorAtual, fa.nforcrcida as cidadeFornecedorAtual, fa.cforcresta as estadoFornecedorAtual ";

        $sql .= " from sfpc.tbataregistroprecointerna a";

        $sql .= " left outer join sfpc.tbsolicitacaolicitacaoportal s";
        $sql .= " on (s.clicpoproc = a.clicpoproc";
        $sql .= " and s.alicpoanop = a.alicpoanop";
        $sql .= " and s.ccomlicodi = a.ccomlicodi";
        $sql .= " and s.corglicodi = a.corglicodi)";

        $sql .= " left outer join sfpc.tbfornecedorcredenciado f";
        $sql .= " on f.aforcrsequ = a.aforcrsequ";

        $sql .= " left outer join sfpc.tbfornecedorcredenciado fa";
        $sql .= " on fa.aforcrsequ = (select afa.aforcrsequ from sfpc.tbataregistroprecointerna afa where afa.carpnosequ = a.carpnoseq1)";

        $sql .= " left outer join sfpc.tbdocumentolicitacao d";
        $sql .= " on d.clicpoproc = a.clicpoproc";
        $sql .= " and d.clicpoproc = " . $processo;
        $sql .= " and d.corglicodi = " . $orgao;
        $sql .= " and d.alicpoanop = " . $ano;

        $sql .= " where a.carpnosequ = " . $chaveAta;

        return $sql;
    }


    private function sqlAtaParticipanteAta($chaveAta)
    {
        $sql  = "select * ";
        $sql .= " from sfpc.tbcaronainternaatarp pa ";
        $sql .= " inner join sfpc.tborgaolicitante o on ";
        $sql .= " o.corglicodi = pa.corglicodi  ";
        $sql .= "  where pa.carpnosequ = " . $chaveAta->carpnosequ;



        return $sql;
    }

    private function sqlAtaConsultarLimiteCarona()
    {
        $sql  = "select qpargecaro ";
        $sql .= " from sfpc.tbparametrosgerais pa limit 1";

        return $sql;
    }


    private function sqlAtaParticipanteAtaOrgao($chaveAta, $numeroOrgao)
    {
        $sql  = "select * ";
        $sql .= " from sfpc.tbcaronainternaatarp pa ";
        $sql .= " inner join sfpc.tborgaolicitante o on ";
        $sql .= " o.corglicodi = pa.corglicodi  ";
        $sql .= "  where pa.carpnosequ = " . $chaveAta;

        $sql .= "  and pa.corglicodi = " . $numeroOrgao;

        return $sql;
    }


    public function consultarAtaPorChave($processo, $ano, $orgao, $numeroAta)
    {
        $db = Conexao();
        $sql = $this->sqlAtaPorchave($processo, $orgao, $ano, $numeroAta);

        $res = executarSQL($db, $sql);
        $res->fetchInto($res, DB_FETCHMODE_OBJECT);
        $this->hasError($res);

        return $res;
    }


    public function consultarAtaParticipanteChave($numeroAta)
    {
        $db = Conexao();
        $sql = $this->sqlAtaParticipanteAta($numeroAta);

        $res = executarSQL($db, $sql);
        $itens = array();
        $item = null;
        $itemTipo = new stdClass();
        while ($res->fetchInto($item, DB_FETCHMODE_OBJECT)) {
            $item->tipoItem = $itemTipo->tipoItem;
            $itens[] = $item;
        }
        return $itens;
    }


    public function consultarLimiteCarona()
    {
        $db = Conexao();
        $sql = $this->sqlAtaConsultarLimiteCarona();

        $res = executarSQL($db, $sql);
        $res->fetchInto($res, DB_FETCHMODE_OBJECT);
        $this->hasError($res);

        return $res;
    }


    public function consultarAtaParticipanteAtaOrgao($numeroAta, $numeroOrgao)
    {
        $db = Conexao();
        $sql = $this->sqlAtaParticipanteAtaOrgao($numeroAta, $numeroOrgao);

        $res = executarSQL($db, $sql);
        $itens = array();
        $item = null;
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
    } //end consultarItensAta()



    /**
     * Dados. Consultar itens da ata.
     *
     * @param $clicpoproc Código do Processo Licitatório
     * @param $alicpoanop Ano do Processo Licitatório
     * @param $cgrempcodi Código do Grupo
     * @param $ccomlicodi Código da Comissão
     * @param $corglicodi Código do Órgão Licitante
     */
    public function consultarItensAtaNotIn($alicpoanop, $carpnosequ)
    {
        $db = Conexao();
        $sql = $this->sqlConsultarItensAtaNotIn($alicpoanop, $carpnosequ);
        $res = executarSQL($db, $sql);
        $itens = array();
        $item = null;
        $itemTipo = new stdClass();
        while ($res->fetchInto($item, DB_FETCHMODE_OBJECT)) {
            $item->tipoItem = $itemTipo->tipoItem;
            $itens[] = $item;
        }
        return $itens;
    } //end consultarItensAtaNotIn()



    /**
     * Dados. Consultar itens da ata.
     *
     * @param $carpnosequ Código do Processo Licitatório    
     */
    public function consultarItensAtaParticipante($carpnosequ)
    {
        $db = Conexao();
        $sql = $this->sqlConsultarItensAtaParticipante($carpnosequ);
        $res = executarSQL($db, $sql);
        $itens = array();
        $item = null;
        $itemTipo = new stdClass();
        $itemTipo->tipoItemValores = array();
        $itemTipo->tipoItem = "ITEMPARTICIPANTE";
        $carpnosequAnterior = '';
        $corglicodiAnterior = '';
        $citarpsequAnterior = '';

        while ($res->fetchInto($item, DB_FETCHMODE_OBJECT)) {

            $varArray = array($item->corglicodi => array(
                'apiarpqtat' => $item->aitcrpqtat,
                'apiarpqtut' => $item->aitcrpqtut,
                'vitcrpvuti' => $item->vitcrpvuti
            ));

            if ($citarpsequAnterior != $item->citarpsequ) {
                $citarpsequAnterior = $item->citarpsequ;
                $item->tipoItem = $itemTipo->tipoItem;

                $item->tipoItemValores = array();

                if ($itemTipo->tipoItemValores != null) {
                    $itemTipo->tipoItemValores = array();
                }


                //if(sizeof($itens) > 0){                    
                array_push($item->tipoItemValores, $varArray);
                //}else{
                //array_push($itemTipo->tipoItemValores, $varArray);                
                //}


                $itens[] = $item;
            } else {
                $endItem = end($itens);

                //array_push($itemTipo->tipoItemValores, $varArray); 

                array_push($endItem->tipoItemValores, $varArray);
            }
        }

        return $itens;
    } //end consultarItensAta()



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

        return sprintf($sql, $alicpoanop, $carpnosequ);
    } //end sqlConsultarItensAta()


    /**
     * SQL consultar itens da ata.
     *
     * @param $clicpoproc Código do Processo Licitatório
     * @param $alicpoanop Ano do Processo Licitatório
     * @param $cgrempcodi Código do Grupo
     * @param $ccomlicodi Código da Comissão
     * @param $corglicodi Código do Órgão Licitante
     */
    private function sqlConsultarItensAtaNotIn($alicpoanop, $carpnosequ)
    {
        $sql  = "SELECT * ";
        $sql .= "         FROM ";
        $sql .= "             sfpc.tbitemataregistropreconova i ";
        $sql .= "             INNER JOIN sfpc.tbataregistroprecointerna arpi ";
        $sql .= "                 ON arpi.carpnosequ = i.carpnosequ ";
        $sql .= "                     AND arpi.aarpinanon =$alicpoanop ";
        $sql .= "             LEFT OUTER JOIN sfpc.tbmaterialportal m ON i.cmatepsequ = m.cmatepsequ ";
        $sql .= "             LEFT OUTER JOIN sfpc.tbunidadedemedida ump ON ump.cunidmcodi = m.cunidmcodi ";
        $sql .= "             LEFT OUTER JOIN sfpc.tbservicoportal s ON i.cservpsequ = s.cservpsequ ";
        $sql .= "         WHERE ";
        $sql .= "             i.carpnosequ = $carpnosequ ";
        $sql .= "           and i.citarpsequ not in(  ";
        $sql .= "           select  ";
        $sql .= "               ipa.citarpsequ  ";
        $sql .= "           from  ";
        $sql .= "               sfpc.tbcaronainternaatarp arpi inner join sfpc.tbitemcaronainternaatarp ipa on  ";
        $sql .= "               ipa.carpnosequ = arpi.carpnosequ  ";
        $sql .= "               and ipa.corglicodi = arpi.corglicodi inner join sfpc.tbitemataregistropreconova i on  ";
        $sql .= "               i.carpnosequ = arpi.carpnosequ  ";
        $sql .= "               and i.citarpsequ = ipa.citarpsequ left outer join sfpc.tbmaterialportal m on  ";
        $sql .= "               i.cmatepsequ = m.cmatepsequ left outer join sfpc.tbunidadedemedida ump on  ";
        $sql .= "               ump.cunidmcodi = m.cunidmcodi left outer join sfpc.tbservicoportal s on  ";
        $sql .= "               i.cservpsequ = s.cservpsequ inner join sfpc.tborgaolicitante o on   ";
        $sql .= "               o.corglicodi = arpi.corglicodi   ";
        $sql .= "           where   ";
        $sql .= "               i.carpnosequ = $carpnosequ  ";
        $sql .= "           order by   ";
        $sql .= "               ipa.citarpsequ,  ";
        $sql .= "               ipa.corglicodi asc  )";

        return $sql;
    } //end sqlConsultarItensAtaNotIn()


    /**
     * SQL consultar itens da ata Participante.
     *
     * @param $carpnosequ Código do Processo Licitatório     
     */
    private function sqlConsultarItensAtaParticipante($carpnosequ)
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
        $sql .= "         order by ipa.citarpsequ, ipa.corglicodi asc  ";

        return sprintf($sql, $carpnosequ);
    } //end sqlConsultarItensAta()


    /**
     *
     * @param unknown $valores
     */
    public function sqlAddParticipanteOrgaoAta($valores)
    {
        $sql = "INSERT INTO sfpc.tbcaronainternaatarp";
        $sql .= "(carpnosequ, corglicodi, cusupocodi, tcarrpulat,fcarrpsitu)";
        $sql .= " VALUES($valores->carpnosequ, $valores->corglicodi, $valores->cusupocodi, '$valores->tpatrpulat', '$valores->fpatrpsitu')";



        return $sql;
    }

    /**
     *
     * @param unknown $codigoOrgao $cogidoAta
     */
    public function sqlAtivarItemOrgaoParticipante($codigoOrgao, $cogidoAta)
    {
        $sql = "    UPDATE sfpc.tbitemcaronainternaatarp SET fitcrpsitu = 'A'  ";
        $sql .= "   WHERE  carpnosequ =  $cogidoAta  ";
        $sql .= "   AND    corglicodi = $codigoOrgao  ";

        return $sql;
    }

    /**
     *
     * @param unknown $codigoOrgao $cogidoAta
     */
    public function sqlAtivarOrgaoParticipante($codigoOrgao, $cogidoAta)
    {
        $sql = "    UPDATE sfpc.tbcaronainternaatarp SET fcarrpsitu = 'A'  ";
        $sql .= "   WHERE  carpnosequ =  $cogidoAta  ";
        $sql .= "   AND    corglicodi = $codigoOrgao  ";

        return $sql;
    }

    /**
     *
     * @param unknown $codigoOrgao $cogidoAta
     */
    public function sqlInativarItemOrgaoParticipante($codigoOrgao, $cogidoAta)
    {
        $sql = "    UPDATE sfpc.tbitemcaronainternaatarp SET fitcrpsitu = 'I'  ";
        $sql .= "   WHERE  carpnosequ =  $cogidoAta  ";
        $sql .= "   AND    corglicodi = $codigoOrgao  ";

        return $sql;
    }

    /**
     *
     * @param unknown $codigoOrgao $cogidoAta
     */
    public function sqlInativarOrgaoParticipante($codigoOrgao, $cogidoAta)
    {
        $sql = "    UPDATE sfpc.tbcaronainternaatarp SET fcarrpsitu = 'I'  ";
        $sql .= "   WHERE  carpnosequ =  $cogidoAta  ";
        $sql .= "   AND    corglicodi = $codigoOrgao  ";

        return $sql;
    }


    /**
     *
     * @param unknown $codigoOrgao $cogidoAta
     */
    public function sqlRemoverOrgaoParticipante($codigoOrgao, $cogidoAta)
    {
        $sql = "    DELETE FROM sfpc.tbcaronainternaatarp  ";
        $sql .= "   WHERE  carpnosequ =  $cogidoAta  ";
        $sql .= "   AND    corglicodi = $codigoOrgao  ";

        return $sql;
    }

    /**
     *
     * @param unknown $codigoOrgao $cogidoAta
     */
    public function sqlRemoverItemOrgaoParticipante($codigoOrgao, $cogidoAta)
    {
        $sql = "    DELETE FROM sfpc.tbitemcaronainternaatarp  ";
        $sql .= "   WHERE  carpnosequ =  $cogidoAta  ";
        $sql .= "   AND    corglicodi = $codigoOrgao  ";

        return $sql;
    }

    /**
     *
     * @param unknown $valores
     */
    public function sqlUpdateParticipanteOrgaoAta($valores)
    {
        

        $sql = "UPDATE sfpc.tbcaronainternaatarp SET";
        $sql .= " cusupocodi = " . $valores->cusupocodi;
        $sql .= " , tcarrpulat = '" . $valores->tpatrpulat . "'";
        // $sql .= " , fcarrpsitu = '" . $valores->fpatrpsitu . "'";
        $sql .= " where ";
        $sql .= " carpnosequ = " . $valores->carpnosequ;
        $sql .= " and corglicodi = " . $valores->corglicodi;

        return $sql;
    }

    /**
     *
     * @return string
     */
    public function sqlConsultarMaiorItem()
    {
        $sql = "select max(i.citarpsequ) from sfpc.tbitemcaronainternaatarp i";
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
        //$db->disconnect();
        return $itens;
    }

    public function consultarTipoControle($ata)
    {
        $sql = " SELECT arpn.farpnotsal 
                 FROM sfpc.tbataregistropreconova arpn
                 WHERE arpn. carpnosequ = %d";
        $resultado = ClaDatabasePostgresql::executarSQL(sprintf($sql, $ata));

        return $resultado;
    }
} //end class

/**
 * Classe RegistroPreco_Negocio_CadMigracaoAddCarona
 */
class RegistroPreco_Negocio_CadMigracaoAddCarona extends Negocio_Abstrata
{

    /**
     *
     * {@inheritdoc}
     *
     * @see Negocio_Abstrata::getDados()
     */
    public function getDados()
    {
        $this->setDados(new RegistroPreco_Dados_CadMigracaoAddCarona());
        return parent::getDados();
    }


    /**
     * Negócio. Consultar itens da ata.
     */
    public function consultarItensAta($alicpoanop, $carpnosequ)
    {
        return $this->getDados()->consultarItensAta($alicpoanop, $carpnosequ);
    } //end consultarItensAta()


    /**
     * Negócio. Consultar itens da ata.
     */
    public function consultarItensAtaNotIn($alicpoanop, $carpnosequ)
    {
        return $this->getDados()->consultarItensAtaNotIn($alicpoanop, $carpnosequ);
    } //end consultarItensAtaNotIn()


    /**
     * Negócio. Consultar itens da ata.
     */
    public function consultarItensAtaParticipante($carpnosequ)
    {
        return $this->getDados()->consultarItensAtaParticipante($carpnosequ);
    } //end consultarItensAta()


    public function consultarAtaPorChave($processo, $ano, $orgao, $numeroAta)
    {
        return $this->getDados()->consultarAtaPorChave($processo, $ano, $orgao, $numeroAta);
    }

    public function consultarAtaParticipanteChave($numeroAta)
    {
        return $this->getDados()->consultarAtaParticipanteChave($numeroAta);
    }

    public function consultarLimiteCarona()
    {
        return $this->getDados()->consultarLimiteCarona();
    }

    public function consultarAtaParticipanteAtaOrgao($numeroAta, $numeroOrgao)
    {
        return $this->getDados()->consultarAtaParticipanteAtaOrgao($numeroAta, $numeroOrgao);
    }

    public function consultarDCentroDeCustoUsuario($cgrempcodi, $cusupocodi, $corglicodi)
    {
        return $this->getDados()->consultarDCentroDeCustoUsuario($cgrempcodi, $cusupocodi, $corglicodi);
    }

    public function consultarTipoControle($carpnosequ)
    {
        return $this->getDados()->consultarTipoControle($carpnosequ);
    }


    /**
     * Negócio. Salvar
     *
     * @return void
     */
    public function salvar($entidade, $itensOrgao)
    {

        $arrayEntidadeFinal = array();

        $semerror = true;

        if (!$this->validarItemAta($itensOrgao)) {
            return false;
        }

        if (!empty($_SESSION['orgaos_c'])) {
            foreach ($_SESSION['orgaos_c'] as $key => $value) {
                $entidade->corglicodi = $key;
                //depois Verificar a situacao
                $entidade->fpatrpsitu = 'A';
                $entidade->tpatrpulat = date('Y-m-d H:i:s');

                $db = Conexao();
                $db->autoCommit(false);
                $db->query("BEGIN TRANSACTION");

                $consultarParticipanteAtaOrgao = $this->consultarParticipanteAtaOrgao($db, $entidade->carpnosequ, $entidade->corglicodi);
                
                if ($consultarParticipanteAtaOrgao == null) {
                    $sqlParticipanteNovo = $this->getDados()->sqlAddParticipanteOrgaoAta($entidade);
                    $resultadoAtaNova    = executarTransacao($db, $sqlParticipanteNovo);
                    $commited = $db->commit();
                } else {
                    $sqlParticipanteNovo = $this->getDados()->sqlUpdateParticipanteOrgaoAta($entidade);
                    $resultadoAtaNova       = executarTransacao($db, $sqlParticipanteNovo);
                    $commited = $db->commit();
                }

                if ($commited instanceof DB_error) {
                    $db->rollback();
                    $_SESSION['mensagemFeedback'] = 'Erro ao salvar dados';
                    $_SESSION['conferePagina'] = 'carona';
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
                $_SESSION['conferePagina'] = 'carona';
                unset($_SESSION['orgaos_c']);
                unset($_SESSION['post_itens_armazenar_tela_c']);
            } catch (Exception $e) {
                $semerror = false;
                $database->query("ROLLBACK");
                $_SESSION['mensagemFeedback'] = 'Erro ao salvar dados';
                $_SESSION['conferePagina'] = 'carona';
                ExibeErroBD("\nLinha: " . __LINE__ . "\nSql: " . $e->getMessage());
                die;
            }
        } else {
            $_SESSION['mensagemFeedback'] = 'Dados salvos com sucesso';
            $_SESSION['conferePagina'] = 'carona';
        }
        return $semerror;
    }


    /**
     * Negócio. Retirar Participante
     *
     * @return void
     */
    public function retirarCarona($orgaosParaRemover)
    {

        $semerror = true;
        foreach ($orgaosParaRemover as $keyCodigo => $orgao) {
            if (isset($orgaosParaRemover[$keyCodigo]['coluna_orgao']) && $orgaosParaRemover[$keyCodigo]['coluna_orgao'] == 'on') {
                if (!$this->removerItemOrgao($keyCodigo, $_SESSION['ata'])) {
                    $semerror = false;
                    break;
                }

                if (!$this->removerParticipanteOrgao($keyCodigo, $_SESSION['ata'])) {
                    $semerror = false;
                    break;
                }

                unset($_SESSION['orgaos_c'][$keyCodigo]);
            }
        }

        return $semerror;
    }

    public function verificarExcluirCarona($carpnosequ, $orgaos)
    {
        $corglocodi = implode(',', $orgaos);
        $db = Conexao();
        $sql = " SELECT COUNT(*) FROM sfpc.tbsolicitacaocompra 
                 WHERE fsolcorpcp = 'C'
                       AND carpnosequ = %d
                       AND corglicodi IN (" . $corglocodi . ")
                       AND fsolcoautc = 'S'
                       AND csitsocodi IN (3,4,5)";
        $resultado = executarTransacao($db, sprintf($sql, $carpnosequ));
        $resultado->fetchInto($resultado, DB_FETCHMODE_OBJECT);

        return $resultado;
    }


    /**
     * Negócio. Ativar Participante
     *
     * @return void
     */
    public function ativarCarona($orgaosParaAtivar)
    {
        $semerror = (bool) true;

        foreach ($orgaosParaAtivar as $keyCodigo => $orgao) {

            if (isset($orgaosParaAtivar[$keyCodigo]['coluna_orgao']) && $orgaosParaAtivar[$keyCodigo]['coluna_orgao'] == 'on') {
                if (isset($orgaosParaAtivar[$keyCodigo]['novo_orgao']) && $orgaosParaAtivar[$keyCodigo]['novo_orgao'] == 'S') {
                    $semerror = (string) 'Salvar Orgão';
                    break;
                }

                if (!$this->ativarItemOrgao($keyCodigo, $_SESSION['ata'])) {
                    $semerror = (bool) false;
                    break;
                }
                if (!$this->ativarCaronaOrgao($keyCodigo, $_SESSION['ata'])) {
                    $semerror = (bool) false;
                    break;
                }
            }
        }

        return $semerror;
    }


    /**
     * Negócio. Ativar Participante
     *
     * @return void
     */
    public function inativarCarona($orgaosParaAtivar)
    {

        $semerror = (bool) true;

        foreach ($orgaosParaAtivar as $keyCodigo => $orgao) {

            if (isset($orgaosParaAtivar[$keyCodigo]['coluna_orgao']) && $orgaosParaAtivar[$keyCodigo]['coluna_orgao'] == 'on') {
                if (isset($orgaosParaAtivar[$keyCodigo]['novo_orgao']) && $orgaosParaAtivar[$keyCodigo]['novo_orgao'] == 'S') {
                    $semerror = (string) 'Salvar Orgão';
                    break;
                }

                if (!$this->inativarItemOrgao($keyCodigo, $_SESSION['ata'])) {
                    $semerror = (bool) false;
                    break;
                }

                if (!$this->inativarCaronaOrgao($keyCodigo, $_SESSION['ata'])) {
                    $semerror = (bool) false;
                    break;
                }
            }
        }

        return $semerror;
    }

    public function validarItemAta($itensOrgao)
    {

        $_SESSION['post_itens_armazenar_tela_c'] = $itensOrgao;
        $field = 'apiarpqtut';
        $field_2 = 'apiarpqtat';

        foreach ($itensOrgao as $codigoItem => $item) {
            $somatorioDaVez = 0;

            if ($item['tipoControle'] == 1) {
                $field = 'vitcrpvuti';
            }

            foreach ($item as $codigoOrgao => $itemOrgao) {
                if (is_array($itemOrgao)) {

                    if ($item['tipoControle'] == 1) {
                        $field_2 = (moeda2float($item['totalAta']) < moeda2float($item['saldoGeral'])) ? 'totalAta' : 'saldoGeral';
                    }

                    if (moeda2float($item['apiarpqtat']) == 0) {
                        $item['apiarpqtat'] = $item['aitarpqtor'];
                    }

                    $valorOrdemDaVez = $itemOrgao['ordemTela'];
                    $somaValores = moeda2float($itemOrgao[$field]) + moeda2float($itemOrgao['scc']) + moeda2float($itemOrgao['ata_anterior']);
                    if ((!empty($itemOrgao[$field])) && ((moeda2float($item[$field_2]) * 1) < $somaValores)) {
                        $_SESSION['mensagemFeedbackTipo']   = 1;
                        $_SESSION['mensagemFeedback'] = 'O Utilizado do Item de Lote ' . $itemOrgao['lote'] . ' e Ordº ' . $itemOrgao['ordemTela'] . ' não pode ser maior que o Solicitado';
                        $_SESSION['conferePagina'] = 'carona';
                        return false;
                    }

                    if (moeda2float($itemOrgao[$field]) > moeda2float($item[$field_2])) {
                        $_SESSION['mensagemFeedbackTipo']   = 1;
                        $_SESSION['mensagemFeedback'] = 'A Quantidade Utilizada do Item de Ordº ' . $valorOrdemDaVez . ' não pode ser superior a Quantidade Total do Item';
                        $_SESSION['conferePagina'] = 'carona';
                        return false;
                    }
                    $somatorioDaVez += moeda2float($itemOrgao[$field]);
                    $somatorioDaVez = number_format((float)$somatorioDaVez, 2, '.', '');
                }
            }

            //verificando se o saldo do item é menor que a soma de todas as quantidades para o item
            if (moeda2float($item['qtd_total_max_carona']) < $somatorioDaVez) {
                $_SESSION['mensagemFeedbackTipo']   = 1;
                $_SESSION['mensagemFeedback'] = 'A soma das Quantidades Totais do Item de Ordº ' . $valorOrdemDaVez . ' de todos os Caronas não pode ser superior que a Quantidade Total da Ata';
                $_SESSION['conferePagina'] = 'carona';
                return false;
            }
        }

        return true;
    }


    private function removerItemOrgao($keyCodigo, $ata)
    {

        $db = Conexao();
        $db->autoCommit(false);
        $db->query("BEGIN TRANSACTION");

        $sqlRemoverOrgaoItemParticipante = $this->getDados()->sqlRemoverItemOrgaoParticipante($keyCodigo, $ata);
        $resultadoAtaNova       = executarTransacao($db, $sqlRemoverOrgaoItemParticipante);
        $commited = $db->commit();

        if ($commited instanceof DB_error) {
            $db->rollback();
            $_SESSION['mensagemFeedback'] = 'Erro ao salvar dados';
            $_SESSION['conferePagina'] = 'carona';
            ExibeErroBD(self::$erroPrograma . "\nLinha: " . __LINE__ . "\nSql: " . $e->getMessage());
            return false;
        }

        return true;
    }

    private function ativarItemOrgao($keyCodigo, $ata)
    {

        $db = Conexao();
        $db->autoCommit(false);
        $db->query("BEGIN TRANSACTION");

        $sqlRemoverOrgaoItemParticipante = $this->getDados()->sqlAtivarItemOrgaoParticipante($keyCodigo, $ata);
        $resultadoAtaNova       = executarTransacao($db, $sqlRemoverOrgaoItemParticipante);
        $commited = $db->commit();

        if ($commited instanceof DB_error) {
            $db->rollback();
            $_SESSION['mensagemFeedback'] = 'Erro ao salvar dados';
            $_SESSION['conferePagina'] = 'carona';
            ExibeErroBD(self::$erroPrograma . "\nLinha: " . __LINE__ . "\nSql: " . $e->getMessage());
            return false;
        }

        return true;
    }

    private function ativarCaronaOrgao($keyCodigo, $ata)
    {

        $db = Conexao();
        $db->autoCommit(false);
        $db->query("BEGIN TRANSACTION");

        $sqlRemoverOrgaoItemParticipante = $this->getDados()->sqlAtivarOrgaoParticipante($keyCodigo, $ata);
        $resultadoAtaNova       = executarTransacao($db, $sqlRemoverOrgaoItemParticipante);
        $commited = $db->commit();

        if ($commited instanceof DB_error) {
            $db->rollback();
            $_SESSION['mensagemFeedback'] = 'Erro ao salvar dados';
            $_SESSION['conferePagina'] = 'carona';
            ExibeErroBD(self::$erroPrograma . "\nLinha: " . __LINE__ . "\nSql: " . $e->getMessage());
            return false;
        }

        return true;
    }


    private function inativarItemOrgao($keyCodigo, $ata)
    {

        $db = Conexao();
        $db->autoCommit(false);
        $db->query("BEGIN TRANSACTION");

        $sqlRemoverOrgaoItemParticipante = $this->getDados()->sqlInativarItemOrgaoParticipante($keyCodigo, $ata);
        $resultadoAtaNova       = executarTransacao($db, $sqlRemoverOrgaoItemParticipante);
        $commited = $db->commit();

        if ($commited instanceof DB_error) {
            $db->rollback();
            $_SESSION['mensagemFeedback'] = 'Erro ao salvar dados';
            $_SESSION['conferePagina'] = 'carona';
            ExibeErroBD(self::$erroPrograma . "\nLinha: " . __LINE__ . "\nSql: " . $e->getMessage());
            return false;
        }

        return true;
    }

    private function inativarCaronaOrgao($keyCodigo, $ata)
    {

        $db = Conexao();
        $db->autoCommit(false);
        $db->query("BEGIN TRANSACTION");

        $sqlRemoverOrgaoItemParticipante = $this->getDados()->sqlInativarOrgaoParticipante($keyCodigo, $ata);
        $resultadoAtaNova       = executarTransacao($db, $sqlRemoverOrgaoItemParticipante);
        $commited = $db->commit();

        if ($commited instanceof DB_error) {
            $db->rollback();
            $_SESSION['mensagemFeedback'] = 'Erro ao salvar dados';
            $_SESSION['conferePagina'] = 'carona';
            ExibeErroBD(self::$erroPrograma . "\nLinha: " . __LINE__ . "\nSql: " . $e->getMessage());
            return false;
        }

        return true;
    }

    private function removerParticipanteOrgao($keyCodigo, $ata)
    {

        $db = Conexao();
        $db->autoCommit(false);
        $db->query("BEGIN TRANSACTION");

        $sqlRemoverOrgaoParticipante = $this->getDados()->sqlRemoverOrgaoParticipante($keyCodigo, $ata);
        $resultadoAtaNova       = executarTransacao($db, $sqlRemoverOrgaoParticipante);
        $commited = $db->commit();

        if ($commited instanceof DB_error) {
            $db->rollback();
            $_SESSION['mensagemFeedback'] = 'Erro ao salvar dados';
            $_SESSION['conferePagina'] = 'carona';
            ExibeErroBD(self::$erroPrograma . "\nLinha: " . __LINE__ . "\nSql: " . $e->getMessage());
            return false;
        }

        return true;
    }

    // Salvar item
    private function salvarItemAtaParticipante($db, $ata, $codigoItem, $codigoOrgao, $itemOrgao, $tipoControle)
    {
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

    private function inserirItem($db, $ata, $codigoItem, $codigoOrgao, $itemOrgao, $tipoControle)
    {
        $field = ($tipoControle != 1) ? 'apiarpqtut' : 'vitcrpvuti';
        $filtro = ($tipoControle != 1) ? 'aitcrpqtat' : 'vitcrpvatu';
        $sequencial  = $codigoItem;
        $quatidadeDoParticipante = $itemOrgao[$filtro] == null ? 0  : moeda2float($itemOrgao[$filtro], 4);
        $quatidadeUtilizadaDoParticipante = $itemOrgao[$field] == null ? 0  : moeda2float($itemOrgao[$field], 4);
        $situacao = 'A';
        $codigoUsuario      = $_SESSION['_cusupocodi_'];
        $tpiarpulat = date('Y-m-d H:i:s');

        $sql  = "INSERT INTO ";
        $sql .= "sfpc.tbitemcaronainternaatarp ";
        $sql .= "(";
        $sql .= "carpnosequ, ";
        $sql .= "corglicodi, ";
        $sql .= "citarpsequ, ";
        if ($tipoControle != 1) {
            $sql .= "aitcrpqtat, ";
        } else {
            $sql .= "vitcrpvatu, ";
        }

        $sql .= "fitcrpsitu, ";
        $sql .= "cusupocodi, ";
        $sql .= "titcrpulat, ";
        if ($tipoControle != 1) {
            $sql .= "aitcrpqtut) ";
        } else {
            $sql .= "vitcrpvuti) ";
        }

        $sql .= "VALUES ";
        $sql .= "(";
        $sql .= "$ata, ";
        $sql .= "$codigoOrgao, ";
        $sql .= "$sequencial, ";
        $sql .= "$quatidadeDoParticipante, ";
        $sql .= "'" . $situacao . "',";
        $sql .= "$codigoUsuario, ";
        $sql .= "'" . $tpiarpulat . "',";
        $sql .= "$quatidadeUtilizadaDoParticipante ";

        $sql .= ")";
        $resultado = $db->query($sql);
        return $resultado;
    }

    private function atualizarItem($db, $ata, $codigoItem, $codigoOrgao, $itemOrgao, $tipoControle)
    {
        $field = ($tipoControle != 1) ? 'apiarpqtut' : 'vitcrpvuti';
        $sequencial  = $codigoItem;
        $quatidadeDoParticipante = $itemOrgao['apiarpqtat'] == null ? 0  : moeda2float($itemOrgao['apiarpqtat'], 4);
        $quatidadeUtilizadaDoParticipante = $itemOrgao[$field] == null ? 0  : moeda2float($itemOrgao[$field], 4);
        $situacao = 'A';
        $codigoUsuario      = $_SESSION['_cusupocodi_'];
        $tpiarpulat = date('Y-m-d H:i:s');

        $sql  = "UPDATE ";
        $sql .= "sfpc.tbitemcaronainternaatarp SET ";

        $sql .= " aitcrpqtat  = " . $quatidadeDoParticipante;

        if ($tipoControle != 1) {
            $sql .= " , aitcrpqtut  = " . $quatidadeUtilizadaDoParticipante;
        } else {
            $sql .= " , vitcrpvuti  = " . $quatidadeUtilizadaDoParticipante;
        }

        $sql .= " , fitcrpsitu  = " . "'" . $situacao . "'";

        $sql .= " , cusupocodi  = " . $codigoUsuario;

        $sql .= " , titcrpulat  = " . "'" . $tpiarpulat . "'";

        $sql .= " where ";
        $sql .= " carpnosequ = " . $ata;
        $sql .= " and corglicodi = " . $codigoOrgao;
        $sql .= " and citarpsequ = " . $sequencial;


        $resultado = $db->query($sql);
        return $resultado;
    }

    /**
     *
     * @param integer $ata
     * @param integer $codigoItem
     * @param integer $codigoOrgao
     */
    public function consultarItemAta($db, $ata, $codigoItem, $codigoOrgao)
    {

        $sql = "SELECT
        ipia.carpnosequ,
        ipia.corglicodi,
        ipia.citarpsequ,
        ipia.aitcrpqtat,
        ipia.aitcrpqtut,
        ipia.fitcrpsitu,
        ipia.cusupocodi,
        ipia.titcrpulat
        FROM
        sfpc.tbitemcaronainternaatarp ipia
        WHERE
        ipia.carpnosequ = $ata
        AND ipia.citarpsequ = $codigoItem
        AND ipia.corglicodi = " . $codigoOrgao;

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
    public function consultarParticipanteAtaOrgao($db, $ata, $codigoOrgao)
    {

        $sql  = "select * ";
        $sql .= " from sfpc.tbcaronainternaatarp pa ";
        $sql .= " inner join sfpc.tborgaolicitante o on ";
        $sql .= " o.corglicodi = pa.corglicodi  ";
        $sql .= "  where pa.carpnosequ = " . $ata;

        $sql .= "  and pa.corglicodi = " . $codigoOrgao;

        $resultado = executarSQL($db, $sql);
        $resultado->fetchInto($item, DB_FETCHMODE_OBJECT);

        return $item;
    }

    public function obterProximoNumeroItem()
    {
        $sql = $this->getDados()->sqlConsultarMaiorItem();
        $resultado = executarSQL(ClaDatabasePostgresql::getConexao(), $sql);
        $resultado->fetchInto($valorMaximo, DB_FETCHMODE_OBJECT);

        $valorAtual = intval($valorMaximo->max) + 1;

        return $valorAtual;
    }
} // end class

/**
 * Classe RegistroPreco_Adaptacao_CadMigracaoAddCarona
 */
class RegistroPreco_Adaptacao_CadMigracaoAddCarona extends Adaptacao_Abstrata
{

    /**
     *
     * {@inheritdoc}
     *
     * @see Adaptacao_Abstrata::getNegocio()
     */
    public function getNegocio()
    {
        $this->setNegocio(new RegistroPreco_Negocio_CadMigracaoAddCarona());
        return parent::getNegocio();
    }

    /**
     * Adaptação. Consultar itens de uma ata.
     */
    public function consultarItensAta($alicpoanop, $carpnosequ)
    {
        return $this->getNegocio()->consultarItensAta($alicpoanop, $carpnosequ);
    } //end consultarItensAta()

    /**
     * Adaptação. Consultar itens de uma ata.
     */
    public function consultarItensAtaNotIn($alicpoanop, $carpnosequ)
    {
        return $this->getNegocio()->consultarItensAtaNotIn($alicpoanop, $carpnosequ);
    } //end consultarItensAtaNotIn()

    /**
     * Adaptação. Consultar itens do participante.
     */
    public function consultarItensAtaParticipante($carpnosequ)
    {
        return $this->getNegocio()->consultarItensAtaParticipante($carpnosequ);
    } //end consultarItensAtaParticipante()


    /**
     * consultarAtaPorChave
     */
    public function consultarAtaPorChave($processo, $ano, $orgao, $numeroAta)
    {
        return $this->getNegocio()->consultarAtaPorChave($processo, $ano, $orgao, $numeroAta);
    } //end consultarAtaPorChave()


    /**
     * consultarAtaParticipanteChave
     */
    public function consultarAtaParticipanteChave($numeroAta)
    {
        return $this->getNegocio()->consultarAtaParticipanteChave($numeroAta);
    } //end consultarAtaParticipanteChave()

    /**
     * consultarLimiteCarona
     */
    public function consultarLimiteCarona()
    {
        return $this->getNegocio()->consultarLimiteCarona();
    } //end consultarLimiteCarona()


    /**
     * consultarAtaParticipanteAtaOrgao
     */
    public function consultarAtaParticipanteAtaOrgao($numeroAta, $numeroOrgao)
    {
        return $this->getNegocio()->consultarAtaParticipanteAtaOrgao($numeroAta, $numeroOrgao);
    } //end consultarAtaParticipanteAtaOrgao()


    /**
     * Ataptação. Salvar
     *
     * @return boolean
     */
    public function salvar()
    {
        $entidade = $this->getNegocio()->getDados()->getEntidade('sfpc.tbcaronainternaatarp');

        // exemplo validacao
        if (isset($_SESSION['ata'])) {
            $entidade->carpnosequ = (int) filter_var($_SESSION['ata'], FILTER_SANITIZE_NUMBER_INT);
            if (empty($entidade->carpnosequ)) {
                $_SESSION['mensagemFeedback'] = 'Código da ata não foi informado';
                $_SESSION['conferePagina'] = 'carona';
                return;
            }
        }

        if (isset($_SESSION['_cusupocodi_'])) {
            $entidade->cusupocodi = (int) filter_var($_SESSION['_cusupocodi_'], FILTER_SANITIZE_NUMBER_INT);
            if (empty($entidade->cusupocodi)) {
                $_SESSION['mensagemFeedback'] = 'Código da ata não foi informado';
                $_SESSION['conferePagina'] = 'carona';
                return;
            }
        }

        return $this->getNegocio()->salvar($entidade, $_POST['itemOrgao']);
    } //end salvar()



    /**
     * Remover caronas
     *
     * @return boolean
     */
    public function retirarCarona()
    {
        return $this->getNegocio()->retirarCarona($_POST['columnOrgao']);
    }

    public function verificarExcluirCarona($carpnosequ, $corglicodi)
    {
        $orgaos = array();
        if (is_array($corglicodi)) {
            foreach ($corglicodi as $key => $value) {
                $orgaos[] = $key;
            }
        } else {
            $orgaos[] = $corglicodi;
        }

        $removerParticipante = $this->getNegocio()->verificarExcluirCarona($carpnosequ, $orgaos);
        return $removerParticipante;
    }


    /**
     * Ataptação. Salvar
     *
     * @return boolean
     */
    public function ativarCarona()
    {

        if (!isset($_POST['columnOrgao'])) {
            $_SESSION['mensagemFeedback'] = 'Selecione uma Carona';
            $_SESSION['conferePagina'] = 'carona';
            return false;
        }

        return $this->getNegocio()->ativarCarona($_POST['columnOrgao']);
    } //end salvar()


    /**
     * Ataptação. Salvar
     *
     * @return boolean
     */
    public function inativarCarona()
    {

        if (!isset($_POST['columnOrgao'])) {
            $_SESSION['mensagemFeedback'] = 'Selecione uma Carona';
            $_SESSION['conferePagina'] = 'carona';
            return false;
        }

        return $this->getNegocio()->inativarCarona($_POST['columnOrgao']);
    } //end salvar()

    public function consultarTipoControle($carpnosequ)
    {
        return $this->getNegocio()->consultarTipoControle($carpnosequ);
    }
}

/**
 * Classe RegistroPreco_UI_CadMigracaoAddCarona
 */
class RegistroPreco_UI_CadMigracaoAddCarona extends UI_Abstrata
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
        $this->getTemplate()->block("BLOCO_BOTAO");
    } //end plotarBlocoBotao()

    private function plotarBlocoItemAta($itens, $ata)
    {
        global $SimboloConcatenacaoArray, $SimboloConcatenacaoDesc;
        $tipoControle = $this->getAdaptacao()->consultarTipoControle($ata->carpnosequ);
        $ataCorporativa = verificarAtaCorporativa(ClaDatabasePostgresql::getConexao(), $ata->carpnosequ);

        if ($itens == null) {
            return;
        }

        $_itens[] = $itens;

        $this->getTemplate()->TR_LAYOUT = '';
        //Colunas Orgaos
        if (!empty($_SESSION['orgaos_c'])) {
            foreach ($_SESSION['orgaos_c'] as $key => $orgao) {
                $novoOrgao = "N";
                if ($key != '') {
                    $this->getTemplate()->ID_ORGAO_COLUMN  = $key;
                    $this->getTemplate()->NOME_ORGAO  = $orgao;


                    $statusOrgao = $this->getAdaptacao()->consultarAtaParticipanteAtaOrgao($ata->carpnosequ, $key);

                    $valor = '';
                    if ($statusOrgao != null) {
                        if ($statusOrgao[0]->fcarrpsitu != 'A') {
                            $valor = 'INATIVO';
                        } else {
                            $valor = 'ATIVO';
                        }
                    } else {
                        $novoOrgao = "S";
                    }
                    $this->getTemplate()->NOVO_ORGAO  = $novoOrgao;
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

        $limiteMaximoCarona = $this->getAdaptacao()->consultarLimiteCarona();
        if ($limiteMaximoCarona != null) {
            $limiteMaximoCarona = $limiteMaximoCarona->qpargecaro;
        }
        $count = 0;
        $KeyOrgaAnt = array();
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


            $tipoFinal = ($tipo == 'material') ? 'CADUM' : 'CADUS';

            $this->getTemplate()->VALOR_SEQITEM             = $item->citarpsequ;

            $ordenacao = $item->aitarporde;

            $this->getTemplate()->VALOR_ORD             = $ordenacao;
            $this->getTemplate()->VALOR_TIPO            = $tipoFinal;         // Código Sequencial do Material OU
            $this->getTemplate()->VALOR_CADUS           = $valorCodigo;         // Código Sequencial do Material OU Código sequencial do serviço
            $this->getTemplate()->VALOR_DESCRICAO       = $valorDescricao;      // Descrição do material ou serviço
            $this->getTemplate()->VALOR_DESCRICAO_DETALHADA = $valorDescricaoDetalhada;
            $this->getTemplate()->VALOR_UND             = $item->eunidmsigl;
            $this->getTemplate()->VALOR_LOTE            = $item->citarpnuml;
            $this->getTemplate()->TIPO_CONTROLE         = !empty($tipoControle[0]->farpnotsal) ? $tipoControle[0]->farpnotsal : 0;

            $situacao = $item->fitarpsitu;

            if ($situacao === 'A') {
                $this->getTemplate()->VALOR_SITUACAO      = 'ATIVO';
            } else {
                $this->getTemplate()->VALOR_SITUACAO    = 'INATIVO';
            }

            $this->getTemplate()->QTD_PARTICIPANTE_ITEM_UTILIZADA = '';
            $this->getTemplate()->QTD_SALDO_BLOCO = '';

            $saldoQuantidadeTotal = valorItemAta(ClaDatabasePostgresql::getConexao(), $item->carpnosequ, $item->citarpsequ);
            $valorMaximoCarona = valorMaximoCarona(ClaDatabasePostgresql::getConexao(), $item->carpnosequ, $item->citarpsequ, 'AITARPQTAT', 'AITARPQTOR', $ataCorporativa);
            $fiels_utilizado = 'apiarpqtut'; // Campo para somar valor utilizado
            $field_scc = 'acoeitqtat';
            $fiels_carona_scc      = 'aitescqtso';
            $field_inclusao_direta = 'AITCRPQTUT';
            $field_carona_externa = 'acoeitqtat';
            if ($tipoControle[0]->farpnotsal == 1) {
                $fiels_carona_scc = 'vitescunit';
                $field_scc = 'vcoeitvuti';
                $fiels_utilizado = 'vitcrpvuti'; // Campo para somar valor utilizado
                $field_inclusao_direta = 'VITCRPVUTI';
                $field_carona_externa = 'vcoeitvuti';
                //$fiels_solicitado = 'vpiarpvatu';
                $saldoQuantidadeTotal = valorItemAta(ClaDatabasePostgresql::getConexao(), $item->carpnosequ, $item->citarpsequ, 'vitarpvatu', 'vitarpvori');
                $valorMaximoCarona = valorMaximoCarona(ClaDatabasePostgresql::getConexao(), $item->carpnosequ, $item->citarpsequ, 'vitarpvatu', 'vitarpvori', $ataCorporativa);
            }

            $qtdUsadaOrgaos                = getQtdTotalOrgaoCaronaInternaInclusaoDireta(ClaDatabasePostgresql::getConexao(), $item->carpnosequ, $item->citarpsequ);
            $calculoQuantidateUtilizada    = utilizadoCaronaInclusaoDiretaGeralAtaAtual(ClaDatabasePostgresql::getConexao(), $item->carpnosequ, $item->citarpsequ, $field_inclusao_direta);
            $calculoQuantidateUtilizadaScc = utilizadoCaronaSccGeralAtaAtual(ClaDatabasePostgresql::getConexao(), $item->carpnosequ, $item->citarpsequ, $fiels_carona_scc);
            $calculoCaronaExterna          = utilizadoCaronaOrgaoExternoGeralAtaAtual(ClaDatabasePostgresql::getConexao(), $item->carpnosequ, $item->citarpsequ, $field_carona_externa);

            // Anterior
            $calculoQuantidateUtilizadaA    = UtilizadoCaronaInclusaoDiretaGeralAtaAnterior(ClaDatabasePostgresql::getConexao(), $item->carpnosequ, $item->citarpsequ, $field_inclusao_direta);
            $calculoQuantidateUtilizadaSccA = utilizadoCaronaSccGeralAtaAnterior(ClaDatabasePostgresql::getConexao(), $item->carpnosequ, $item->citarpsequ, $fiels_carona_scc);
            $calculoCaronaExternaA          = utilizadoCaronaOrgaoExternoGeralAtaAnterior(ClaDatabasePostgresql::getConexao(), $item->carpnosequ, $item->citarpsequ, $field_carona_externa);

            $total = ($calculoQuantidateUtilizada + $calculoQuantidateUtilizadaScc + $calculoCaronaExterna +
                $calculoQuantidateUtilizadaA + $calculoQuantidateUtilizadaSccA + $calculoCaronaExternaA);

            $saldoGeral = $valorMaximoCarona - $total;

            if (!empty($_SESSION['orgaos_c'])) {
                foreach ($_SESSION['orgaos_c'] as $key => $orgao) {
                    $calculoQuantidateUtilizada2 = 0;
                    $sccTotal = 0;
                    if ($key != '') {
                        if ($item->tipoItem == "ITEMPARTICIPANTE") {
                            if (isset($_SESSION['post_itens_armazenar_tela_c'])) {
                                $orgaoUtilizado = ($_SESSION['post_itens_armazenar_tela_c'][$item->citarpsequ][$key][$fiels_utilizado] != null) ? $_SESSION['post_itens_armazenar_tela_c'][$item->citarpsequ][$key][$fiels_utilizado] : 0;
                                $this->getTemplate()->QTD_PARTICIPANTE_ITEM = ($_SESSION['post_itens_armazenar_tela_c'][$item->citarpsequ][$key]['apiarpqtat'] != null) ? $_SESSION['post_itens_armazenar_tela_c'][$item->citarpsequ][$key]['apiarpqtat'] : '';
                                $this->getTemplate()->QTD_PARTICIPANTE_ITEM_UTILIZADA = $orgaoUtilizado;
                                $this->getTemplate()->QTD_SALDO_BLOCO = ($_SESSION['post_itens_armazenar_tela_c'][$item->citarpsequ][$key]['apiarpqtat'] != null && $_SESSION['post_itens_armazenar_tela_c'][$item->citarpsequ][$key][$fiels_utilizado] != null) ? converte_valor_estoques($_SESSION['post_itens_armazenar_tela_c'][$item->citarpsequ][$key]['apiarpqtat'] - $_SESSION['post_itens_armazenar_tela_c'][$item->citarpsequ][$key][$fiels_utilizado]) : '';
                                $calculoQuantidateUtilizada2 += $orgaoUtilizado;
                            } else {
                                $this->getTemplate()->QTD_PARTICIPANTE_ITEM = '';
                                $this->getTemplate()->QTD_PARTICIPANTE_ITEM_UTILIZADA = '';
                                $this->getTemplate()->QTD_SALDO_BLOCO = '';
                            }
                            foreach ($item->tipoItemValores as $keyConstrucao => $value) {
                                foreach ($value as $keyOrgaoInteno => $valueOrgao) {
                                    $KeyOrgaAnt[$count] = ($keyOrgaoInteno) ? $keyOrgaoInteno : $key;
                                    $count++;
                                    $scc = 0;
                                    $chaveVerificacao = null;
                                    if (is_int($keyOrgaoInteno)) {
                                        $chaveVerificacao = $keyOrgaoInteno;
                                    } else {
                                        $chaveVerificacao = $keyConstrucao;
                                    }

                                    if ($chaveVerificacao == $key) {
                                        if (is_int($keyOrgaoInteno)) {
                                            //$calculoQuantidateUtilizada += $valueOrgao[$fiels_utilizado]; // comentado pq estava somand 2x
                                            if (isset($_SESSION['post_itens_armazenar_tela_c'])) {
                                                $orgaoUtilizado = ($_SESSION['post_itens_armazenar_tela_c'][$item->citarpsequ][$key][$fiels_utilizado] != null) ? $_SESSION['post_itens_armazenar_tela_c'][$item->citarpsequ][$key][$fiels_utilizado] : 0;
                                                $this->getTemplate()->QTD_PARTICIPANTE_ITEM = ($_SESSION['post_itens_armazenar_tela_c'][$item->citarpsequ][$key]['apiarpqtat'] != null) ? $_SESSION['post_itens_armazenar_tela_c'][$item->citarpsequ][$key]['apiarpqtat'] : '';
                                                $this->getTemplate()->QTD_PARTICIPANTE_ITEM_ORG = ($_SESSION['post_itens_armazenar_tela_c'][$item->citarpsequ][$key]['aitarpqtor'] != null) ? $_SESSION['post_itens_armazenar_tela_c'][$item->citarpsequ][$key]['aitarpqtor'] : '';
                                                $this->getTemplate()->QTD_PARTICIPANTE_ITEM_UTILIZADA = $orgaoUtilizado;
                                                $saldoBloco = ($_SESSION['post_itens_armazenar_tela_c'][$item->citarpsequ][$key]['apiarpqtat'] != null && $_SESSION['post_itens_armazenar_tela_c'][$item->citarpsequ][$key][$fiels_utilizado] != null) ? $saldoQuantidadeTotal - $_SESSION['post_itens_armazenar_tela_c'][$item->citarpsequ][$key][$fiels_utilizado] : '';
                                                $calculoQuantidateUtilizada2 += $orgaoUtilizado;
                                            } else {
                                                $orgaoUtilizado = $valueOrgao[$fiels_utilizado];
                                                $this->getTemplate()->QTD_PARTICIPANTE_ITEM = converte_valor_estoques($valueOrgao['apiarpqtat']);
                                                $this->getTemplate()->QTD_PARTICIPANTE_ITEM_ORG = converte_valor_estoques($valueOrgao['aitarpqtor']);
                                                $this->getTemplate()->QTD_PARTICIPANTE_ITEM_UTILIZADA = converte_valor_estoques($orgaoUtilizado);
                                                $saldoBloco = $saldoQuantidadeTotal - $valueOrgao[$fiels_utilizado];
                                                $calculoQuantidateUtilizada2 += $orgaoUtilizado;
                                            }
                                        } else {
                                            //$calculoQuantidateUtilizada += $value[$fiels_utilizado]; // comentado pq estava somand 2x
                                            if (isset($_SESSION['post_itens_armazenar_tela_c'])) {
                                                $orgaoUtilizado = ($_SESSION['post_itens_armazenar_tela_c'][$item->citarpsequ][$key][$fiels_utilizado] != null) ? $_SESSION['post_itens_armazenar_tela_c'][$item->citarpsequ][$key][$fiels_utilizado] : 0;
                                                $this->getTemplate()->QTD_PARTICIPANTE_ITEM = ($_SESSION['post_itens_armazenar_tela_c'][$item->citarpsequ][$key]['apiarpqtat'] != null) ? $_SESSION['post_itens_armazenar_tela_c'][$item->citarpsequ][$key]['apiarpqtat'] : '';
                                                $this->getTemplate()->QTD_PARTICIPANTE_ITEM_ORG = ($_SESSION['post_itens_armazenar_tela_c'][$item->citarpsequ][$key]['aitarpqtor'] != null) ? $_SESSION['post_itens_armazenar_tela_c'][$item->citarpsequ][$key]['aitarpqtor'] : '';
                                                $this->getTemplate()->QTD_PARTICIPANTE_ITEM_UTILIZADA = $orgaoUtilizado;
                                                $saldoBloco = ($_SESSION['post_itens_armazenar_tela_c'][$item->citarpsequ][$key]['apiarpqtat'] != null && $_SESSION['post_itens_armazenar_tela_c'][$item->citarpsequ][$key][$fiels_utilizado] != null) ? $saldoQuantidadeTotal - $_SESSION['post_itens_armazenar_tela_c'][$item->citarpsequ][$key][$fiels_utilizado] : '';
                                                $calculoQuantidateUtilizada2 += $orgaoUtilizado;
                                            } else {
                                                $orgaoUtilizado = $value[$fiels_utilizado];
                                                $this->getTemplate()->QTD_PARTICIPANTE_ITEM = converte_valor_estoques($value['apiarpqtat']);
                                                $this->getTemplate()->QTD_PARTICIPANTE_ITEM_UTILIZADA = converte_valor_estoques($orgaoUtilizado);
                                                $saldoBloco = $value['apiarpqtat'] - $value[$fiels_utilizado];
                                                $calculoQuantidateUtilizada2 += $orgaoUtilizado;
                                            }
                                        }

                                        $scc = getQtdTotalOrgaoCaronaExterna(Conexao(), $item->carpnosequ, $item->citarpsequ, $field_scc);
                                        $sccOrgao = utilizadoCaronaSccOrgaoAtaAtual(Conexao(), $item->carpnosequ, $item->citarpsequ, $fiels_carona_scc, $keyOrgaoInteno);
                                        $utilizadoCaronaSccOrgaoAtaAnterior = utilizadoCaronaSccOrgaoAtaAnterior(Conexao(), $item->carpnosequ, $item->citarpsequ, $fiels_carona_scc, $keyOrgaoInteno);
                                        $utilizadoCaronaInclusaoDiretaOrgaoAtaAnterior = utilizadoCaronaInclusaoDiretaOrgaoAtaAnterior(Conexao(), $item->carpnosequ, $item->citarpsequ, $field_inclusao_direta, $keyOrgaoInteno);
                                        $orgaoAtaAnterior = $utilizadoCaronaSccOrgaoAtaAnterior + $utilizadoCaronaInclusaoDiretaOrgaoAtaAnterior;
                                        $scc = !empty($scc) ? $scc : 0;
                                        $sccTotal += $scc;
                                        $saldo = ($saldoQuantidadeTotal) - ($orgaoUtilizado + $sccOrgao + $orgaoAtaAnterior);
                                        $saldoBloco = ($saldoGeral < $saldo) ? $saldoGeral : $saldo;
                                        $this->getTemplate()->QTD_SALDO_BLOCO = converte_valor_estoques($saldoBloco);
                                        $this->getTemplate()->QTD_PARTICIPANTE_ITEM_UTILIZADA_2 = converte_valor_estoques($qtdUsadaOrgaos);

                                        $this->getTemplate()->VALOR_SCC = converte_valor_estoques($sccOrgao);
                                        $this->getTemplate()->VALOR_ORGAO_AA = converte_valor_estoques($orgaoAtaAnterior);
                                    } else {

                                        if (in_array($key, $KeyOrgaAnt)) {
                                            $sccOrgao = utilizadoCaronaSccOrgaoAtaAtual(Conexao(), $item->carpnosequ, $item->citarpsequ, $fiels_carona_scc, $key);
                                        } else {
                                            $sccOrgao = null;
                                        }
                                        $this->getTemplate()->VALOR_SCC = converte_valor_estoques($sccOrgao);
                                        continue;
                                        //$calculoQuantidateUtilizada = 0;
                                        $sccOrgao = utilizadoCaronaSccOrgaoAtaAtual(Conexao(), $item->carpnosequ, $item->citarpsequ, $fiels_carona_scc, $key);
                                        $utilizadoCaronaSccOrgaoAtaAnterior = utilizadoCaronaSccOrgaoAtaAnterior(Conexao(), $item->carpnosequ, $item->citarpsequ, $fiels_carona_scc, $key);
                                        $utilizadoCaronaInclusaoDiretaOrgaoAtaAnterior = utilizadoCaronaInclusaoDiretaOrgaoAtaAnterior(Conexao(), $item->carpnosequ, $item->citarpsequ, $field_inclusao_direta, $key);
                                        $orgaoAtaAnterior = $utilizadoCaronaSccOrgaoAtaAnterior + $utilizadoCaronaInclusaoDiretaOrgaoAtaAnterior;
                                        $saldo = ($saldoQuantidadeTotal) - ($calculoQuantidateUtilizada + $sccOrgao + $orgaoAtaAnterior);

                                        $this->getTemplate()->QTD_SALDO_BLOCO = converte_valor_estoques($saldo);
                                        $this->getTemplate()->QTD_PARTICIPANTE_ITEM_UTILIZADA_2 = converte_valor_estoques($qtdUsadaOrgaos);

                                        $this->getTemplate()->VALOR_SCC = converte_valor_estoques($sccOrgao);
                                        $this->getTemplate()->VALOR_ORGAO_AA = converte_valor_estoques($orgaoAtaAnterior);
                                    }
                                }
                            }
                        } else {
                            if (isset($_SESSION['post_itens_armazenar_tela_c'])) {
                                //exit;
                                $this->getTemplate()->QTD_PARTICIPANTE_ITEM = ($_SESSION['post_itens_armazenar_tela_c'][$item->citarpsequ][$key]['apiarpqtat'] != null) ? $_SESSION['post_itens_armazenar_tela_c'][$item->citarpsequ][$key]['apiarpqtat'] : '';
                                $this->getTemplate()->QTD_PARTICIPANTE_ITEM_ORG = ($_SESSION['post_itens_armazenar_tela_c'][$item->citarpsequ][$key]['aitarpqtor'] != null) ? $_SESSION['post_itens_armazenar_tela_c'][$item->citarpsequ][$key]['aitarpqtor'] : '';
                                $this->getTemplate()->QTD_PARTICIPANTE_ITEM_UTILIZADA = ($_SESSION['post_itens_armazenar_tela_c'][$item->citarpsequ][$key][$fiels_utilizado] != null) ? $_SESSION['post_itens_armazenar_tela_c'][$item->citarpsequ][$key][$fiels_utilizado] : '';
                                $this->getTemplate()->QTD_SALDO_BLOCO = ($_SESSION['post_itens_armazenar_tela_c'][$item->citarpsequ][$key]['apiarpqtat'] != null && $_SESSION['post_itens_armazenar_tela_c'][$item->citarpsequ][$key][$fiels_utilizado] != null) ? converte_valor_estoques($saldoQuantidadeTotal - $_SESSION['post_itens_armazenar_tela_c'][$item->citarpsequ][$key][$fiels_utilizado]) : '';
                            }
                        }

                        $this->getTemplate()->QTD_PARTICIPANTE_ITEM = converte_valor_estoques($item->aitarpqtat);
                        $this->getTemplate()->QTD_PARTICIPANTE_ITEM_ORG = converte_valor_estoques($item->aitarpqtor);
                        $this->getTemplate()->ID_ORGAO  = $key;

                        $descricaoLinhaTd = "Lote: " . $item->citarpnuml;
                        $descricaoLinhaTd .= "; Ordem: " . $ordenacao;
                        $descricaoLinhaTd .= "; Tipo: " . $tipo;
                        $descricaoLinhaTd .= "; Cod reduzido: " . $valorCodigo;
                        $descricaoLinhaTd .= "; Descrição: " . $valorDescricao;
                        $descricaoLinhaTd .= "; Descrição detalhada: " . $valorDescricaoDetalhada;
                        $this->getTemplate()->DESCRICAO_LINHA_TD = $descricaoLinhaTd;

                        if ($tipoControle[0]->farpnotsal == 1) {
                            $this->getTemplate()->block("BLOCO_ORGAO_ITEM_TD_1");
                        } else {
                            $this->getTemplate()->block("BLOCO_ORGAO_ITEM_TD");
                        }
                    }
                }
            }

            // Verificar percentual de adesão
            $percetualAdesao = getPercentualAdesao(ClaDatabasePostgresql::getConexao(), $ataCorporativa);

            $descricaoLinha = "Lote: " . $item->citarpnuml;
            $descricaoLinha .= "; Ordem: " . $ordenacao;
            $descricaoLinha .= "; Tipo: " . $tipo;
            $descricaoLinha .= "; Cod reduzido: " . $valorCodigo;
            $descricaoLinha .= "; Descrição: " . $valorDescricao;
            $descricaoLinha .= "; Descrição detalhada: " . $valorDescricaoDetalhada;
            $this->getTemplate()->DESCRICAO_LINHA = $descricaoLinha;
            $this->getTemplate()->VALOR_QTD_TOTAL       = converte_valor_estoques($saldoQuantidadeTotal * $percetualAdesao);
            $this->getTemplate()->VALOR_QTD_MAX_CARONA  = converte_valor_estoques($valorMaximoCarona);
            $this->getTemplate()->VALOR_CARONA_INTERNO  = converte_valor_estoques($calculoQuantidateUtilizada);
            $this->getTemplate()->VALOR_CARONA_SCC      = converte_valor_estoques($calculoQuantidateUtilizadaScc);
            $this->getTemplate()->VALOR_CARONA_EXTERNA  = converte_valor_estoques($calculoCaronaExterna);
            $this->getTemplate()->VALOR_TOTAL_ATA_A     = converte_valor_estoques($calculoQuantidateUtilizadaA + $calculoQuantidateUtilizadaSccA + $calculoCaronaExternaA);
            $this->getTemplate()->VALOR_TOTAL           = converte_valor_estoques($total);
            $this->getTemplate()->SALDO                 = converte_valor_estoques($saldoGeral);
            $this->getTemplate()->block("BLOCO_ITEM");
            $this->getTemplate()->block("BLOCO_RESULTADO_ATAS");
            $this->getTemplate()->block("BLOCO_ITEM_TOTAL");
        }
    }

    public function valorAtaMontado($orgao, $ata)
    {
        $consultarfor = new RegistroPreco_Dados_CadMigracaoAddCarona();
        $dto = $consultarfor->consultarDCentroDeCustoUsuario($ata->cgrempcodi, $ata->cusupocodi, $orgao);
        $objeto = current($dto);
        $numeroAtaFormatado = $objeto->ccenpocorg . str_pad($objeto->ccenpounid, 2, '0', STR_PAD_LEFT);
        //madson o ano buscado era o de processo da ata ->  $numeroAtaFormatado .= "." . str_pad($ata->carpincodn, 4, "0", STR_PAD_LEFT) . "/" . $ata->alicpoanop;
        $numeroAtaFormatado .= "." . str_pad($ata->carpincodn, 4, "0", STR_PAD_LEFT) . "/" . $ata->aarpinanon;

        return $numeroAtaFormatado;
    }

    /**
     *
     * @return boolean
     */
    private function validarOrgao()
    {
        $this->orgao = isset($_GET['orgao']) ? filter_var($_GET['orgao'], FILTER_SANITIZE_NUMBER_INT) : null;
        $_SESSION['orgao'] = ($this->orgao != null) ? $this->orgao : $_SESSION['orgao'];
        $this->orgao = $_SESSION['orgao'];
        if (!filter_var($this->orgao, FILTER_VALIDATE_INT)) {
            $_SESSION['mensagemFeedback'] = 'Órgão não foi informado';
            $_SESSION['conferePagina'] = 'carona';
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
        $_SESSION['ano'] = ($this->ano != null) ? $this->ano : $_SESSION['ano'];
        $this->ano = $_SESSION['ano'];
        if (!filter_var($this->ano, FILTER_VALIDATE_INT)) {
            $_SESSION['mensagemFeedback'] = 'Ano não foi informado';
            $_SESSION['conferePagina'] = 'carona';
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
        $_SESSION['processo'] = ($this->processo != null) ? $this->processo : $_SESSION['processo'];

        $this->processo = $_SESSION['processo'];
        if (!filter_var((int)$this->processo, FILTER_VALIDATE_INT)) {
            $_SESSION['mensagemFeedback'] = 'Processo não foi informado';
            $_SESSION['conferePagina'] = 'carona';
            return false;
        }

        $this->codigoGrupo = isset($_SESSION['grupocodigo']) ? filter_var($_SESSION['grupocodigo'], FILTER_SANITIZE_NUMBER_INT) : null;
        $_SESSION['grupocodigo'] = ($this->codigoGrupo != null) ? $this->codigoGrupo : $_SESSION['grupocodigo'];
        $this->codigoGrupo = $_SESSION['grupocodigo'];
        if (!filter_var((int) $this->codigoGrupo, FILTER_VALIDATE_INT)) {
            $_SESSION['mensagemFeedback'] = 'Processo não foi informado';
            $_SESSION['conferePagina'] = 'carona';
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
        $_SESSION['tipo'] = ($this->tipo != null) ? $this->tipo : $_SESSION['tipo'];
        $this->tipo = $_SESSION['tipo'];
        if (!$this->tipo) {
            $_SESSION['mensagemFeedback'] = 'Tipo não foi informado';
            $_SESSION['conferePagina'] = 'carona';
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
        $_SESSION['fornecedor'] = ($this->fornecedor != null) ? $this->fornecedor : $_SESSION['fornecedor'];
        $this->fornecedor = $_SESSION['fornecedor'];
        if (!$this->fornecedor) {
            $_SESSION['mensagemFeedback'] = 'Fornecedor não foi informado';
            $_SESSION['conferePagina'] = 'carona';
            return false;
        }

        return true;
    }

    /**
     *
     * @return boolean
     */
    private function validarAta()
    {

        $this->ata = isset($_GET['ata']) ? filter_var($_GET['ata'], FILTER_SANITIZE_NUMBER_INT) : null;
        $_SESSION['ata'] = ($this->ata != null) ? $this->ata : $_SESSION['ata'];
        $this->ata = $_SESSION['ata'];
        if (!$this->ata) {
            $_SESSION['mensagemFeedback'] = 'Fornecedor não foi informado';
            $_SESSION['conferePagina'] = 'carona';
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
            !$this->validarFornecedor() ||
            !$this->validarAta()
        ) {
            return false;
        }
    }



    /**
     */
    public function __construct()
    {
        $template = new TemplateNovaJanela("templates/CadManterEspecialCarona.html", "Registro de Preço > Migração > Adicionar Carona", true);
        $template->NOMEPROGRAMA = 'CadManterEspecialCarona';
        $template->TITULO_PAGINA = 'MANTER ESPECIAL - ATA INTERNA - CARONA';
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
        $this->setAdaptacao(new RegistroPreco_Adaptacao_CadMigracaoAddCarona());
        return parent::getAdaptacao();
    }

    public function processVoltar()
    {
        $uri = "CadManterEspecial.php?tipo=" . $_SESSION['tipo'] . "&ano=" . $_SESSION['ano'] . "&processo=" . $_SESSION['processo'] . "&orgao=" . $_SESSION['orgao'] . "&fornecedor=" . $_SESSION['fornecedor'] . "&comissaocodigo=" . $_SESSION['processo'] . "&grupocodigo=" . $_SESSION['grupocodigo'];
        header('location: ' . $uri);
    }


    /**
     * Processo principal
     *
     * Processo inicial para montar os dados do programa
     *
     * @return void
     */
    public function proccessPrincipal()
    {

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
        $ata = $this->getAdaptacao()->consultarAtaPorChave($this->processo, $this->ano, $this->orgao, $this->ata);

        if (isset($ata->carpnosequ)) {

            $this->plotarBlocoLicitacao($licitacao, $ata, null, null);


            $itensAta = $this->getAdaptacao()->consultarItensAtaParticipante(
                $ata->carpnosequ
            );

            if ($itensAta == null || $itensAta == '') {
                $itensAta = $this->getAdaptacao()->consultarItensAta(
                    $this->ano,
                    $ata->carpnosequ
                );
            } else {
                $itensAdicionadosAposCriarParticipantes = $this->getAdaptacao()->consultarItensAtaNotIn(
                    $this->ano,
                    $ata->carpnosequ
                );

                foreach ($itensAdicionadosAposCriarParticipantes as $key => $itemParaAdicionar) {
                    array_push($itensAta, $itemParaAdicionar);
                }

                $ataParticipante = $this->getAdaptacao()->consultarAtaParticipanteChave($ata);
                foreach ($ataParticipante as $orgao) {
                    $_SESSION['orgaos_c'][$orgao->corglicodi] = $orgao->eorglidesc;
                }
            }
        }

        $limiteMaximoCarona = $this->getAdaptacao()->consultarLimiteCarona();
        if ($limiteMaximoCarona != null) {
            $limiteMaximoCarona = $limiteMaximoCarona->qpargecaro;
        }

        $totalSessionOrgao = sizeof($_SESSION['orgaos_c']);

        $this->plotarBlocoItemAta($itensAta, $ata);
    } //end proccessPrincipal()



    /**
     *
     * @param stdClass $licitacao
     * @param stdClass $ata
     * @param unknown $dataInformada
     * @param unknown $vigenciaInformada
     */
    private function plotarBlocoLicitacao($licitacao, $ata, $dataInformada, $vigenciaInformada)
    {
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
    public function salvar()
    {
        if (!$this->getAdaptacao()->salvar()) {
            $this->proccessPrincipal();
            return;
        }

        $_SESSION['mensagemFeedbackTipo']   = 1;
        $_SESSION['mensagemFeedback']       = 'Dados salvos com sucesso';
        $_SESSION['conferePagina'] = 'carona';

        $uri = "CadManterEspecialCarona.php?tipo=" . $_SESSION['tipo'] . "&ano=" . $_SESSION['ano'] . "&processo=" . $_SESSION['processo'] . "&orgao=" . $_SESSION['orgao'] . "&fornecedor=" . $_SESSION['fornecedor'] . "&comissaocodigo=" . $_SESSION['processo'] . "&grupocodigo=" . $_SESSION['grupocodigo'];
        header('location: ' . $uri);
        exit();
    } //end salvar()


    /**
     * UI. Salvar.
     *
     * @return void
     */
    public function retirarCarona()
    {
        // Verificar se selecionou o órgão
        if (!isset($_POST['columnOrgao'])) {
            $_SESSION['mensagemFeedbackTipo']   = 0;
            $_SESSION['mensagemFeedback'] = 'Selecione uma Carona';
            $_SESSION['conferePagina'] = 'carona';
            $uri = "CadManterEspecialCarona.php?tipo=" . $_SESSION['tipo'] . "&ano=" . $_SESSION['ano'] . "&processo=" . $_SESSION['processo'] . "&orgao=" . $_SESSION['orgao'] . "&fornecedor=" . $_SESSION['fornecedor'] . "&comissaocodigo=" . $_SESSION['processo'] . "&grupocodigo=" . $_SESSION['grupocodigo'];
            header('location: ' . $uri);
            exit();
        }

        // Verificar Scc antes de remove o carona
        $verificarRemover = $this->getAdaptacao()->verificarExcluirCarona($_SESSION['ata'], $_POST['columnOrgao']);
        if ($verificarRemover->count > 0) {
            $_SESSION['mensagemFeedbackTipo'] = 0;
            $_SESSION['mensagemFeedback']     = 'Este carona possui SCC cadastrada e não pode ser retirado';
            $_SESSION['conferePagina'] = 'carona';
            $uri = "CadManterEspecialCarona.php?tipo=" . $_SESSION['tipo'] . "&ano=" . $_SESSION['ano'] . "&processo=" . $_SESSION['processo'] . "&orgao=" . $_SESSION['orgao'] . "&fornecedor=" . $_SESSION['fornecedor'] . "&comissaocodigo=" . $_SESSION['processo'] . "&grupocodigo=" . $_SESSION['grupocodigo'];
            header('location: ' . $uri);
            exit();
        }

        // Remover caronas
        if (!$this->getAdaptacao()->retirarCarona()) {
            $_SESSION['mensagemFeedbackTipo']   = 0;
            $_SESSION['mensagemFeedback']       = 'Selecione uma Carona';
            $_SESSION['conferePagina'] = 'carona';

            $uri = "CadManterEspecialCarona.php?tipo=" . $_SESSION['tipo'] . "&ano=" . $_SESSION['ano'] . "&processo=" . $_SESSION['processo'] . "&orgao=" . $_SESSION['orgao'] . "&fornecedor=" . $_SESSION['fornecedor'] . "&comissaocodigo=" . $_SESSION['processo'] . "&grupocodigo=" . $_SESSION['grupocodigo'];
            header('location: ' . $uri);
            exit();
        }
        $_SESSION['mensagemFeedbackTipo']   = 1;
        $_SESSION['mensagemFeedback']       = 'Carona removido com sucesso';
        $_SESSION['conferePagina'] = 'carona';

        //$this->proccessPrincipal();
        $uri = "CadManterEspecialCarona.php?tipo=" . $_SESSION['tipo'] . "&ano=" . $_SESSION['ano'] . "&processo=" . $_SESSION['processo'] . "&orgao=" . $_SESSION['orgao'] . "&fornecedor=" . $_SESSION['fornecedor'] . "&comissaocodigo=" . $_SESSION['processo'] . "&grupocodigo=" . $_SESSION['grupocodigo'];
        header('location: ' . $uri);
        exit();
    } //end retirarCarona()

    /**
     * UI. Salvar.
     *
     * @return void
     */
    public function ativarCarona()
    {
        if (is_string($this->getAdaptacao()->ativarCarona())) {
            $_SESSION['mensagemFeedbackTipo']   = 0;
            $_SESSION['mensagemFeedback']       = 'Clique em salvar para depois alterar a situação';
            $_SESSION['conferePagina'] = 'carona';

            $uri = "CadManterEspecialCarona.php?tipo=" . $_SESSION['tipo'] . "&ano=" . $_SESSION['ano'] . "&processo=" . $_SESSION['processo'] . "&orgao=" . $_SESSION['orgao'] . "&fornecedor=" . $_SESSION['fornecedor'] . "&comissaocodigo=" . $_SESSION['processo'] . "&grupocodigo=" . $_SESSION['grupocodigo'];
            header('location: ' . $uri);
            exit();
        }

        if (!$this->getAdaptacao()->ativarCarona()) {
            $_SESSION['mensagemFeedbackTipo']   = 0;
            $_SESSION['mensagemFeedback']       = 'Selecione uma Carona';
            $_SESSION['conferePagina'] = 'carona';

            $uri = "CadManterEspecialCarona.php?tipo=" . $_SESSION['tipo'] . "&ano=" . $_SESSION['ano'] . "&processo=" . $_SESSION['processo'] . "&orgao=" . $_SESSION['orgao'] . "&fornecedor=" . $_SESSION['fornecedor'] . "&comissaocodigo=" . $_SESSION['processo'] . "&grupocodigo=" . $_SESSION['grupocodigo'];
            header('location: ' . $uri);
            exit();
        }

        $_SESSION['mensagemFeedbackTipo']   = 1;
        $_SESSION['mensagemFeedback']       = 'Alteração da Situação do Órgão Carona executada com sucesso';
        $_SESSION['conferePagina'] = 'carona';

        $uri = "CadManterEspecialCarona.php?tipo=" . $_SESSION['tipo'] . "&ano=" . $_SESSION['ano'] . "&processo=" . $_SESSION['processo'] . "&orgao=" . $_SESSION['orgao'] . "&fornecedor=" . $_SESSION['fornecedor'] . "&comissaocodigo=" . $_SESSION['processo'] . "&grupocodigo=" . $_SESSION['grupocodigo'];
        header('location: ' . $uri);
        exit();
    } //end ativarCarona()


    /**
     * UI. Salvar.
     *
     * @return void
     */
    public function inativarCarona()
    {
        if (is_string($this->getAdaptacao()->inativarCarona())) {
            $_SESSION['mensagemFeedbackTipo']   = 0;
            $_SESSION['mensagemFeedback']       = 'Clique em salvar para depois alterar a situação';
            $_SESSION['conferePagina'] = 'carona';

            $uri = "CadManterEspecialCarona.php?tipo=" . $_SESSION['tipo'] . "&ano=" . $_SESSION['ano'] . "&processo=" . $_SESSION['processo'] . "&orgao=" . $_SESSION['orgao'] . "&fornecedor=" . $_SESSION['fornecedor'] . "&comissaocodigo=" . $_SESSION['processo'] . "&grupocodigo=" . $_SESSION['grupocodigo'];
            header('location: ' . $uri);
            exit();
        }

        if (!$this->getAdaptacao()->inativarCarona()) {
            $_SESSION['mensagemFeedbackTipo']   = 0;
            $_SESSION['mensagemFeedback']       = 'Selecione uma Carona';
            $_SESSION['conferePagina'] = 'carona';

            $uri = "CadManterEspecialCarona.php?tipo=" . $_SESSION['tipo'] . "&ano=" . $_SESSION['ano'] . "&processo=" . $_SESSION['processo'] . "&orgao=" . $_SESSION['orgao'] . "&fornecedor=" . $_SESSION['fornecedor'] . "&comissaocodigo=" . $_SESSION['processo'] . "&grupocodigo=" . $_SESSION['grupocodigo'];
            header('location: ' . $uri);
            exit();
        }

        $_SESSION['mensagemFeedbackTipo']   = 1;
        $_SESSION['mensagemFeedback']       = 'Alteração da Situação do Órgão Carona executada com sucesso';
        $_SESSION['conferePagina'] = 'carona';

        $uri = "CadManterEspecialCarona.php?tipo=" . $_SESSION['tipo'] . "&ano=" . $_SESSION['ano'] . "&processo=" . $_SESSION['processo'] . "&orgao=" . $_SESSION['orgao'] . "&fornecedor=" . $_SESSION['fornecedor'] . "&comissaocodigo=" . $_SESSION['processo'] . "&grupocodigo=" . $_SESSION['grupocodigo'];
        header('location: ' . $uri);
        exit();
    } //end inativarCarona()

}


$programa   = new RegistroPreco_UI_CadMigracaoAddCarona();
$botao      = isset($_POST['Botao']) ? $_POST['Botao'] : 'Principal';
switch ($botao) {
    case 'Voltar':
        $programa->processVoltar();
        break;
    case 'retirarCarona':
        $_SESSION['post_itens_armazenar_tela_c'] = $_POST['itemOrgao'];
        $programa->retirarCarona();
        $programa->proccessPrincipal();
        break;
    case 'ativarCarona':
        $_SESSION['post_itens_armazenar_tela_c'] = $_POST['itemOrgao'];
        $programa->ativarCarona();
        $programa->proccessPrincipal();
        break;
    case 'inativarCarona':
        $_SESSION['post_itens_armazenar_tela_c'] = $_POST['itemOrgao'];
        $programa->inativarCarona();
        $programa->proccessPrincipal();
        break;
    case 'Salvar':
        $programa->salvar();
        $programa->proccessPrincipal();
        break;
    case 'RetirarItem':
        $_SESSION['post_itens_armazenar_tela_c'] = $_POST['itemOrgao'];
        $programa->proccessPrincipal();
        break;
    case 'RetirarDocumento':
        $_SESSION['post_itens_armazenar_tela_c'] = $_POST['itemOrgao'];
        $programa->proccessPrincipal();
        break;
    case 'Inserir':
        $_SESSION['post_itens_armazenar_tela_c'] = $_POST['itemOrgao'];
        $programa->adicionarDocumento();
    case 'Principal':
    default:
        $programa->proccessPrincipal();
        break;
}
echo $programa->getTemplate()->show();

if (isset($_SESSION['post_itens_armazenar_tela_c'])) {
    unset($_SESSION['post_itens_armazenar_tela_c']);
}

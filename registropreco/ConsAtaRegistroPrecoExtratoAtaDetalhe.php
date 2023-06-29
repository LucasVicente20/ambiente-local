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
 */
#-------------------------------------------------------------------------
# Alterado: Caio Coutinho
# Data: 04/07/2018
# Objetivo: Tarefa Redmine #198149
#-------------------------------------------------------------------------
# Alterado: Caio Coutinho - Pitang Agile TI
# Data:     21/08/2018
# Objetivo: Tarefa Redmine 201674
#-------------------------------------------------------------------------
# Alterado: Caio Coutinho - Pitang Agile TI
# Data:     14/02/2019
# Objetivo: Tarefa Redmine 210926
#-----------------------------------------------------------------------

// 220038--

if (! @require_once dirname(__FILE__) . "/../bootstrap.php") {
    throw new Exception("Error Processing Request - Bootstrap", 1);
}

/**
 * A camada de Dados conterá o código que irá realizar todo o acesso aos dados.
 *
 * @author rlfo
 *
 */
class RegistroPreco_Dados_ConsAtaRegistroPrecoExtratoAtaDetalhe extends Dados_Abstrata
{

    public function sqlFornecedorDaAtaInterna($sequencialAta)
    {
        $sql = "select forn.* ";
        $sql .= " from sfpc.tbataregistroprecointerna arp";
        $sql .= " join sfpc.tbfornecedorcredenciado forn";
        $sql .= " on forn.aforcrsequ = arp.aforcrsequ and arp.carpnosequ = $sequencialAta";

        return $sql;
    }

    public function sqlFornecedorDaAtaExterna($sequencialAta)
    {
        $sql = "select arp.* ";
        $sql .= " from sfpc.tbfornecedorcredenciado arp";
        $sql .= " where arp.aforcrsequ = $sequencialAta";

        return $sql;
    }

    public function sqlOrgaoParticipante()
    {
        $sql = "select distinct ol.corglicodi, ol.eorglidesc from sfpc.tbparticipanteatarp parp";
        $sql .= " join sfpc.tborgaolicitante ol";
        $sql .= " on ol.corglicodi = parp.corglicodi";

        return $sql;
    }

    public function sqlselectFornecedor($fornecedor)
    {
        $sql = "select fc.aforcrccgc, fc.aforcrccpf, fc.nforcrrazs,fc.eforcrlogr,fc.aforcrnume,fc.eforcrbair,fc.nforcrcida,fc.cforcresta from sfpc.tbfornecedorcredenciado fc";
        $sql .= " where fc.aforcrsequ = " . $fornecedor;

        return $sql;
    }

    public function sqlComissao($comissao)
    {
        $sql = "select cl.ecomlidesc from sfpc.tbcomissaolicitacao cl";
        $sql .= " where cl.ccomlicodi = " . $comissao;

        return $sql;
    }

    public function sqlModalidade($modalidade)
    {
        $sql = "select * from sfpc.tbmodalidadelicitacao ml";
        $sql .= " where ml.cmodlicodi = " . $modalidade;

        return $sql;
    }

    public function sqlDocumentos($ata)
    {
        $sql = "select encode(idocatarqu, 'base64') as arquivo, carpnosequ, cdocatsequ, edocatnome, cusupocodi from sfpc.tbdocumentoatarp darp";
        $sql .= " where darp.carpnosequ = " . $ata;

        return $sql;
    }

    public function sqlDocumentosTeste()
    {
        $sql = "select * from sfpc.tbdocumentoanexo darp";
        $sql .= " where darp.cdocatsequ = 421 ";

        return $sql;
    }

    /**
     * [sqlOrgaoAtaGerada description]
     *
     * @return [type] [description]
     */
    public function sqlOrgaoGerenciador()
    {
        $sql = "
        SELECT distinct o.corglicodi, o.eorglidesc
        FROM  sfpc.tborgaolicitante o
        INNER JOIN sfpc.tbataregistroprecointerna a ON o.corglicodi = a.corglicodi
            ORDER BY o.eorglidesc ASC
        ";
        return $sql;
    }

    public function sqlModalidadePorLicitacaoPortal($codigoProcesso, $anoLicitacao, $codigoGrupo, $codigoComissao, $codigoOrgaoLicitante)
    {
        $sql = "SELECT lic.clicpoproc, lic.alicpoanop, lic.cgrempcodi,
    	lic.ccomlicodi, lic.corglicodi, lic.cmodlicodi, moda.emodlidesc
    	FROM sfpc.tblicitacaoportal lic
    	INNER JOIN sfpc.tbmodalidadelicitacao moda ON moda.cmodlicodi = lic.cmodlicodi
    	AND lic.clicpoproc = $codigoProcesso AND lic.alicpoanop = $anoLicitacao
    	AND lic.cgrempcodi = $codigoGrupo AND lic.ccomlicodi = $codigoComissao
    	AND lic.corglicodi = $codigoOrgaoLicitante";

        return $sql;
    }

    public function sqlParticipantesAta($codigoAta)
    {
        $sql = "SELECT
				    par.carpnosequ,
				    par.corglicodi,
				    par.fpatrpexcl,
				    par.cusupocodi,
				    par.tpatrpulat,
				    org.eorglidesc
				FROM
				    sfpc.tbparticipanteatarp par
				    INNER JOIN sfpc.tborgaolicitante org ON org.corglicodi = par.corglicodi
				WHERE
					par.carpnosequ = $codigoAta";

        return $sql;
    }

    public function sqlItensAtaRegistroPreco($codigoAtam, $removerInativos = false)
    {
        $sql = " select * from sfpc.tbitemataregistropreconova iarpn ";
        $sql .= " LEFT OUTER JOIN sfpc.tbmaterialportal m ON iarpn.cmatepsequ = m.cmatepsequ ";
        $sql .= " LEFT OUTER JOIN sfpc.tbservicoportal s ON iarpn.cservpsequ = s.cservpsequ ";
        $sql .= " where iarpn.carpnosequ = $codigoAtam";
        if($removerInativos) {
            $sql .= " and iarpn.fitarpsitu = 'A'";
        }

        return $sql;
    }

    /**
     *
     * @param unknown $ata
     * @param unknown $codigoItem
     * @param unknown $tipoItem
     * @param unknown $comparacao
     * @param unknown $orgao
     */
    public function sqlConsultarSCCDoProcesso($ata, $codigoItem, $tipoItem, $comparacao, $orgao = null)
    {

        $sql = "    SELECT DISTINCT ATAI.carpnosequ, SOL.csolcosequ, ITEMS.aitescqtso, SOL.tsolcodata, ol.eorglidesc, SOL.clicpoproc, SOL.alicpoanop, SOL.cgrempcodi, SOL.ccomlicodi,	 SOL.corglicodi as orgao_agrupamento, SOL.corglicod1 as orgao_gestor
                FROM sfpc.tbataregistroprecointerna ATAI, sfpc.tbitemataregistropreconova ITEMA,
                    sfpc.tbsolicitacaocompra SOL, sfpc.tbitemsolicitacaocompra ITEMS, sfpc.tborgaolicitante ol ";


        $sql .= "  WHERE  1=1
                AND ATAI.carpnosequ  = $ata
                AND ATAI.carpnosequ  = SOL.carpnosequ
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

    public function sqlConsultarItensAtaCarona($carpnosequ, $itemCodigo, $tipoItem, $seqItem)
    {
        $sql  = "SELECT * ";
        $sql .= "  FROM ";
        $sql .= "   sfpc.tbcaronainternaatarp arpi ";
        $sql .= "    INNER JOIN sfpc.tbitemcaronainternaatarp ipa ON  ";
        $sql .= "       ipa.carpnosequ = arpi.carpnosequ  ";
        $sql .= "       AND ipa.corglicodi = arpi.corglicodi ";
        $sql .= "    INNER JOIN sfpc.tbitemataregistropreconova i ON ";
        $sql .= "       i.carpnosequ = arpi.carpnosequ ";
        $sql .= "       AND i.citarpsequ = ipa.citarpsequ ";
        $sql .= "    LEFT outer JOIN sfpc.tbmaterialportal m ON ";
        $sql .= "       i.cmatepsequ = m.cmatepsequ  ";
        $sql .= "    LEFT outer JOIN sfpc.tbunidadedemedida ump ON ";
        $sql .= "        ump.cunidmcodi = m.cunidmcodi  ";
        $sql .= "    LEFT outer JOIN sfpc.tbservicoportal s ON ";
        $sql .= "        i.cservpsequ = s.cservpsequ ";
        $sql .= "    INNER JOIN sfpc.tborgaolicitante o ON ";
        $sql .= "        o.corglicodi = arpi.corglicodi ";
        $sql .= "  WHERE ";
        $sql .= "    i.carpnosequ = %d ";

        if ($tipoItem == 'M') {
            $sql .= " and i.cmatepsequ = " . $itemCodigo;
        } else {
            $sql .= " and i.cservpsequ = " . $itemCodigo;
        }

        $sql .= " and i.citarpsequ = " . $seqItem;

        $sql .= "         order by ipa.corglicodi asc  ";

        return sprintf($sql, $carpnosequ);

    }//end sqlConsultarItensAta()

    public function sqlConsultarItensAtaCaronaExterno($carpnosequ, $itemCodigo, $tipoItem, $seqItem)
    {
        $sql  = "SELECT * ";
        $sql .= "  FROM ";
        $sql .= "   sfpc.tbcaronaorgaoexterno coe ";
        $sql .= "    INNER JOIN sfpc.tbcaronaorgaoexternoitem coei ON  ";
        $sql .= "       coei.carpnosequ = coe.carpnosequ  ";
        $sql .= "       AND coei.ccaroesequ = coe.ccaroesequ ";
        $sql .= "    INNER JOIN sfpc.tbitemataregistropreconova i ON ";
        $sql .= "       i.carpnosequ = coe.carpnosequ ";
        $sql .= "       AND i.citarpsequ = coei.citarpsequ ";
        $sql .= "    LEFT outer JOIN sfpc.tbmaterialportal m ON ";
        $sql .= "       i.cmatepsequ = m.cmatepsequ  ";
        $sql .= "    LEFT outer JOIN sfpc.tbunidadedemedida ump ON ";
        $sql .= "        ump.cunidmcodi = m.cunidmcodi  ";
        $sql .= "    LEFT outer JOIN sfpc.tbservicoportal s ON ";
        $sql .= "        i.cservpsequ = s.cservpsequ ";
        $sql .= "  WHERE ";
        $sql .= "    i.carpnosequ = %d ";

        if ($tipoItem == 'M') {
            $sql .= " and i.cmatepsequ = " . $itemCodigo;
        } else {
            $sql .= " and i.cservpsequ = " . $itemCodigo;
        }

        $sql .= " and i.citarpsequ = " . $seqItem;

        $sql .= "         order by coe.ecaroeorgg asc  ";

        return sprintf($sql, $carpnosequ);
    }

    public function sqlConsultarItensAtaCaronaSCC($carpnosequ, $itemCodigo, $tipoItem, $seqItem)
    {
        $sql  = "SELECT * ";
        $sql .= "  FROM ";
        $sql .= "   sfpc.tbsolicitacaocompra sc ";
        $sql .= "    INNER JOIN sfpc.tbitemsolicitacaocompra isc ON  ";
        $sql .= "       isc.carpnosequ = sc.carpnosequ  ";
        $sql .= "       AND isc.csolcosequ = sc.csolcosequ ";
        $sql .= "    INNER JOIN sfpc.tbitemataregistropreconova i ON ";
        $sql .= "       i.carpnosequ = sc.carpnosequ ";
        $sql .= "       AND i.citarpsequ = isc.citarpsequ ";
        $sql .= "    LEFT outer JOIN sfpc.tbmaterialportal m ON ";
        $sql .= "       i.cmatepsequ = m.cmatepsequ  ";
        $sql .= "    LEFT outer JOIN sfpc.tbunidadedemedida ump ON ";
        $sql .= "        ump.cunidmcodi = m.cunidmcodi  ";
        $sql .= "    LEFT outer JOIN sfpc.tbservicoportal s ON ";
        $sql .= "        i.cservpsequ = s.cservpsequ ";
        $sql .= "    INNER JOIN sfpc.tborgaolicitante o ON ";
        $sql .= "        o.corglicodi = sc.corglicodi ";
        $sql .= "  WHERE ";
        $sql .= "    i.carpnosequ = %d ";

        if ($tipoItem == 'M') {
            $sql .= " and i.cmatepsequ = " . $itemCodigo;
        } else {
            $sql .= " and i.cservpsequ = " . $itemCodigo;
        }

        $sql .= " and i.citarpsequ = " . $seqItem;

        $sql .= "         order by o.eorglidesc asc  ";

        return sprintf($sql, $carpnosequ);
    }


    public function sqlConsultarItensAtaParticipante($carpnosequ, $itemCodigo, $tipoItem, $seqItem)
    {
        $sql  = "SELECT * ";
        $sql .= "         FROM ";
        $sql .= "             sfpc.tbparticipanteatarp arpi ";
        $sql .= "             inner join sfpc.tbparticipanteitematarp ipa on  ";
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

    public function sqlAtaPorchave($processo, $orgao, $ano, $chaveAta)
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
        // $db = Conexao();
        $sql = $this->sqlConsultarCentroDeCustoUsuario($cgrempcodi, $cusupocodi, $corglicodi);

        $res = executarSQL(ClaDatabasePostgresql::getConexao(), $sql);

        $itens = array();
        $item = null;
        while ($res->fetchInto($item, DB_FETCHMODE_OBJECT)) {
            $itens[] = $item;
        }
        $this->hasError($res);
        // $db->disconnect();
        return $itens;
    }
}

/**
 * A camada de Negócio conterá o código que irá implementar todas as regras de negócio do sistema.
 *
 * Utiliza serviços da camada de Dados.
 *
 * @author rlfo
 *
 */
class RegistroPreco_Negocio_ConsAtaRegistroPrecoExtratoAtaDetalhe extends Negocio_Abstrata
{

    /**
     *
     * @param integer $ata
     * @param integer $processo
     * @param integer $orgao
     * @param integer $ano
     * @param integer $tipo
     * @return NULL
     */
    private function consultarValoresAta($ata, $processo, $orgao, $ano, $tipo)
    {
        $repositorio = new Negocio_Repositorio_AtaRegistroPrecoNova();

        return $repositorio->consultarAta(array(
            'ata'       => $ata,
            'processo'  => $processo,
            'orgao'     => $orgao,
            'ano'       => $ano,
            'tipo'      => $tipo
        ));
    }

    public function __construct()
    {
        $this->setDados(new RegistroPreco_Dados_ConsAtaRegistroPrecoExtratoAtaDetalhe());
    }

    public function consultarFornecedor($fornecedor)
    {
        $sql = $this->getDados()->sqlselectFornecedor($fornecedor);

        $res = ClaDatabasePostgresql::executarSQL($sql);

        ClaDatabasePostgresql::hasError($res);

        return $res;
    }

    /**
     *
     * @param unknown $comissao
     */
    public function consultarComissao($comissao)
    {
        assercao(! empty($comissao), 'Informe a comissão');

        $sql = $this->getDados()->sqlComissao($comissao);

        $res = ClaDatabasePostgresql::executarSQL($sql);

        ClaDatabasePostgresql::hasError($res);

        return $res;
    }

    public function consultarModalidade($modalidade)
    {
        $sql        = $this->getDados()->sqlModalidade($modalidade);
        $resultado  = executarSQL(ClaDatabasePostgresql::getConexao(), $sql);
        $resultado->fetchInto($modalidadeObj, DB_FETCHMODE_OBJECT);
        return $modalidadeObj;
    }

    /**
     *
     * @param unknown $ata
     */
    public function consultarDocumentos($ata)
    {
        $sql = $this->getDados()->sqlDocumentos($ata);
        $res = ClaDatabasePostgresql::executarSQL($sql);
        ClaDatabasePostgresql::hasError($res);
        return $res;
    }

    public function consultarOrgaosParticipantesAtas()
    {
        $sql        = $this->getDados()->sqlOrgaoParticipante();
        $resultados = array();
        $resultado  = executarSQL(ClaDatabasePostgresql::getConexao(), $sql);
        while ($resultado->fetchInto($orgao, DB_FETCHMODE_OBJECT)) {
            array_push($resultados, $orgao);
        }
        return $resultados;
    }

    public function consultarOrgaosGerenciador()
    {
        $sql        = $this->getDados()->sqlOrgaoGerenciador();
        $resultados = array();
        $resultado  = executarSQL(ClaDatabasePostgresql::getConexao(), $sql);
        while ($resultado->fetchInto($orgao, DB_FETCHMODE_OBJECT)) {
            array_push($resultados, $orgao);
        }
        return $resultados;
    }

    public function carregarValoresTela(ArrayObject $dto)
    {
        $valores    = $this->consultarValoresAta($ata, $processo, $orgao, $ano, $tipo);
        $valores    = current($valores);
        $comissao   = $this->consultarComissao($valores->ccomlicodi);
        $modalidade = $this->consultarModalidadePorLicitacaoPortal($valores->clicpoproc, $valores->alicpoanop, $valores->cgrempcodi, $valores->ccomlicodi, $valores->corglicodi);

        $valores->comissao      = $comissao->ecomlidesc;
        $valores->modalidade    = $modalidade->emodlidesc;
        $valores->tipoAta       = $tipo;
        return $this->getDtoPadrao($valores);
    }

    public function getDtoPadrao($ataRegistroPreco)
    {
        $dadosAta = new stdClass();

        $dadosAta->sequencialAta                = $ataRegistroPreco->carpnosequ;
        $dadosAta->codigoProcessoLicitatorio    = $ataRegistroPreco->clicpoproc;
        $dadosAta->anoProcessoLicitatorio       = $ataRegistroPreco->alicpoanop;
        $dadosAta->comissao                     = $ataRegistroPreco->comissao;
        $dadosAta->modalidade                   = $ataRegistroPreco->modalidade;
        $dadosAta->anoLicitacao                 = $ataRegistroPreco->alicpoanol;
        $dadosAta->codigoLicitacao              = $ataRegistroPreco->clicpocodl;
        $dadosAta->descricaoOrgao               = $ataRegistroPreco->eorglidesc;
        $dadosAta->objeto                       = $ataRegistroPreco->earpinobje;
        $dadosAta->dataInicialAta               = $ataRegistroPreco->tarpindini;
        $dadosAta->vigenciaAta                  = $ataRegistroPreco->aarpinpzvg;
        $dadosAta->fornecedorOriginal           = $ataRegistroPreco->nforcrrazs;
        $dadosAta->sequencialAtaFornecedorAtual = $ataRegistroPreco->carpnoseq1;
        $dadosAta->fornecedorAtual              = $ataRegistroPreco->aforcrseq1;
        $dadosAta->codigoGrupo                  = $ataRegistroPreco->cgrempcodi;
        $dadosAta->codigoComissao               = $ataRegistroPreco->ccomlicodi;
        $dadosAta->codigoOrgaoLicitante         = $ataRegistroPreco->corglicodi;
        $dadosAta->tipoAta                      = $ataRegistroPreco->tipoAta;
        $dadosAta->numeroInscricaoFornecedor    = (empty($ataRegistroPreco->aforcrccgc)) ? $ataRegistroPreco->aforcrccpf : $ataRegistroPreco->aforcrccgc;
        $dadosAta->logradouroFornecedor         = $ataRegistroPreco->eforcrlogr;
        $dadosAta->numeroLogradouroFornecedor   = $ataRegistroPreco->aforcrnume;
        $dadosAta->bairroFornecedor             = $ataRegistroPreco->eforcrbair;
        $dadosAta->cidadeFornecedor             = $ataRegistroPreco->nforcrcida;
        $dadosAta->estadoFornecedor             = $ataRegistroPreco->cforcresta;
        $dadosAta->cusupocodi                   = $ataRegistroPreco->cusupocodi;
        $dadosAta->carpincodn                   = $ataRegistroPreco->carpincodn;

        return $dadosAta;
    }

    /**
     *
     * @param integer $codigoProcesso
     * @param integer $anoLicitacao
     * @param integer $codigoGrupo
     * @param integer $codigoComissao
     * @param integer $codigoOrgaoLicitante
     */
    public function consultarModalidadePorLicitacaoPortal($codigoProcesso, $anoLicitacao, $codigoGrupo, $codigoComissao, $codigoOrgaoLicitante)
    {
        $sql = $this->getDados()->sqlModalidadePorLicitacaoPortal($codigoProcesso, $anoLicitacao, $codigoGrupo, $codigoComissao, $codigoOrgaoLicitante);

        $res = ClaDatabasePostgresql::executarSQL($sql);

        ClaDatabasePostgresql::hasError($res);

        return $res;
    }

    /**
     *
     * @param unknown $codigoAta
     */
    public function consultarParticipantesAta($codigoAta)
    {
        $sql = $this->getDados()->sqlParticipantesAta($codigoAta);

        $res = ClaDatabasePostgresql::executarSQL($sql);
        ClaDatabasePostgresql::hasError($res);
        return $res;
    }

    public function consultarFornecedorDaAtaInterna($sequencialAta)
    {
        $sql = $this->getDados()->sqlFornecedorDaAtaInterna($sequencialAta);
        $resultado = executarSQL(ClaDatabasePostgresql::getConexao(), $sql);
        $resultado->fetchInto($fornecedor, DB_FETCHMODE_OBJECT);
        return $fornecedor;
    }

    public function consultarFornecedorDaAtaExterna($sequencialAta)
    {
        $sql = $this->getDados()->sqlFornecedorDaAtaExterna($sequencialAta);
        $resultado = executarSQL(ClaDatabasePostgresql::getConexao(), $sql);
        $resultado->fetchInto($fornecedor, DB_FETCHMODE_OBJECT);
        return $fornecedor;
    }

    public function consultarItensAtaRegistroPreco($codigoAta, $removerInativos = false)
    {
        $itensAta = array();
        $sql = $this->getDados()->sqlItensAtaRegistroPreco($codigoAta, $removerInativos);
        $resultado = executarSQL(ClaDatabasePostgresql::getConexao(), $sql);
        while ($resultado->fetchInto($item, DB_FETCHMODE_OBJECT)) {
            $itensAta[] = $item;
        }
        return $itensAta;
    }

    /**
     *
     * @param unknown $ata
     * @param unknown $item
     * @param unknown $tipoItem
     * @param unknown $comparacao
     * @param unknown $orgao
     */
    public function consultarSccDoProcesso($ata, $item, $tipoItem, $comparacao, $orgao)
    {
        $sql = $this->getDados()->sqlConsultarSCCDoProcesso($ata, $item, $tipoItem, $comparacao, $orgao);
        $resultado = ClaDatabasePostgresql::executarSQL($sql);

        ClaDatabasePostgresql::hasError($resultado);

        return $resultado;
    }

    public function consultarItensAtaCarona($carpnosequ, $itemCodigo, $tipoItem, $seqItem)
    {
        $db = Conexao();
        $sql = $this->getDados()->sqlConsultarItensAtaCarona($carpnosequ, $itemCodigo, $tipoItem, $seqItem);
        $sql_2 = $this->getDados()->sqlConsultarItensAtaCaronaExterno($carpnosequ, $itemCodigo, $tipoItem, $seqItem);
        $sql_3 = $this->getDados()->sqlConsultarItensAtaCaronaSCC($carpnosequ, $itemCodigo, $tipoItem, $seqItem);

        $res = executarSQL($db, $sql);
        $itens = array();
        $item = null;
        $itemTipo = new stdClass();
        while ($res->fetchInto($item, DB_FETCHMODE_OBJECT)) {
            $item->tipoItem = $itemTipo->tipoItem;
            $itens[] = $item;
        }

        if(!empty($itens)) {
            return $itens;
        }

        $res = executarSQL($db, $sql_2);
        $itemTipo = new stdClass();
        while ($res->fetchInto($item, DB_FETCHMODE_OBJECT)) {
            $item->tipoItem = $itemTipo->tipoItem;
            $itens[] = $item;
        }

        if(!empty($itens)) {
            return $itens;
        }

        $res = executarSQL($db, $sql_3);
        $itemTipo = new stdClass();
        while ($res->fetchInto($item, DB_FETCHMODE_OBJECT)) {
            $item->tipoItem = $itemTipo->tipoItem;
            $itens[] = $item;
        }

        return $itens;
    }//end consultarItensAta()

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


    public function consultarDCentroDeCustoUsuario($cgrempcodi, $cusupocodi, $corglicodi)
    {
        return $this->getDados()->consultarDCentroDeCustoUsuario($cgrempcodi, $cusupocodi, $corglicodi);
    }

    public function consultarAtaPorChave($processo, $orgao, $ano, $numeroAta)
    {
        $db = Conexao();
        $sql = $this->getDados()->sqlAtaPorchave($processo, $orgao, $ano, $numeroAta);
        $resultado = executarSQL($db, $sql);
        $resultado->fetchInto($ata, DB_FETCHMODE_OBJECT);
        return $ata;
    }
}

/**
 * A camada de Adaptação e Transformação conterá o código que tratará a lógica de apresentação dos resultados
 * das requisições dos usuários e a troca de dados com sistemas externos.
 *
 * Utiliza serviços da camada de Negócio.
 *
 * @author rlfo
 *
 */
class RegistroPreco_Adaptacao_ConsAtaRegistroPrecoExtratoAtaDetalhe extends Adaptacao_Abstrata
{

    public function __construct()
    {
        $this->setNegocio(new RegistroPreco_Negocio_ConsAtaRegistroPrecoExtratoAtaDetalhe());
    }

    public function consultarOrgaoParticipantes()
    {
        $orgaos = $this->getNegocio()->consultarOrgaosParticipantesAtas();
        $this->plotarBlocoOrgao($orgaos);
    }

    public function consultarOrgaoGerenciado()
    {
        $orgaos = $this->getNegocio()->consultarOrgaosGerenciador();
        $this->plotarBlocoOrgaoGerenciador($orgaos);
    }

    public function recuperarDadosTela()
    {
        $this->getTemplate()->CHECK_ATA_INTERNA = $_POST['tipoAta'] == 'I' ? 'CHECKED' : '';
        $this->getTemplate()->CHECK_ATA_EXTERNA = $_POST['tipoAta'] == 'E' ? 'CHECKED' : '';
        $this->getTemplate()->NRATA             = $_POST['numeroAta'];
        $this->getTemplate()->PROCESSO          = $_POST['processo'];
        $this->getTemplate()->ANO               = $_POST['ano'];
        $this->getTemplate()->CHECK_CNPJ        = $_POST['cpfcnpj'] == 'cnpj' ? 'CHECKED' : '';
        $this->getTemplate()->CHECK_CPF         = $_POST['cpfcnpj'] == 'cpf' ? 'CHECKED' : '';
        $this->getTemplate()->FORNECEDOR        = $_POST['fornecedor'];
        $this->getTemplate()->CHECK_COD_M       = $_POST['pesquisaMaterial'] == '0' ? 'selected' : '';
        $this->getTemplate()->CHECK_DEC_M       = $_POST['pesquisaMaterial'] == '1' ? 'selected' : '';
        $this->getTemplate()->CHECK_DI_M        = $_POST['pesquisaMaterial'] == '2' ? 'selected' : '';
        $this->getTemplate()->MATERIAL          = $_POST['material'];
        $this->getTemplate()->CHECK_COD_S       = $_POST['pesquisaServico'] == '0' ? 'selected' : '';
        $this->getTemplate()->CHECK_DEC_S       = $_POST['pesquisaServico'] == '1' ? 'selected' : '';
        $this->getTemplate()->CHECK_DI_S        = $_POST['pesquisaServico'] == '2' ? 'selected' : '';
        $this->getTemplate()->SERVICO           = $_POST['servico'];
        $this->getTemplate()->CHECK_M           = $_POST['identificadorGrupo'] == 'M' ? 'CHECKED' : '';
        $this->getTemplate()->CHECK_S           = $_POST['identificadorGrupo'] == 'S' ? 'CHECKED' : '';
        $this->getTemplate()->CHECK_INATIVOS    = $_POST['inativos'] == 'I' ? 'CHECKED' : '';
        $this->getTemplate()->CHECK_VIGENTES    = $_POST['vigentes'] == 'V' ? 'CHECKED' : '';
    }

    /**
     *
     * @param UI_Interface $gui
     * @param ArrayObject $dto
     */
    public function consultarValoresAta(UI_Interface $gui, ArrayObject $dto)
    {
        $valores        = $this->getNegocio()->carregarValoresTela($dto);
        $documentos     = $this->getNegocio()->consultarDocumentos($dto['ata']);
        $participantes  = $this->getNegocio()->consultarParticipantesAta($dto['ata']);
        $this->plotarValoresLicitacaoAta($gui, $valores, $documentos, $participantes);
        $this->consultarItensAtaRegistroPreco($gui, $dto['ata']);
    }

    /**
     *
     * @param Negocio_ValorObjeto_Carpnosequ $valorObjeto
     */
    public function getEntidade(Negocio_ValorObjeto_Carpnosequ $valorObjeto)
    {
        $repositorioAtaRPNova   = new Negocio_Repositorio_AtaRegistroPrecoNova();
        $entidadeAta            = $repositorioAtaRPNova->procurar($valorObjeto);
        $dtoArray               = (array) $entidadeAta;


        if ($dtoArray['carpnotiat'] == 'I') {
            $repositorioAtaInterna  = new Negocio_Repositorio_AtaRegistroPrecoInterna();
            $dtoArray               = array_merge($dtoArray, (array) $repositorioAtaInterna->procurar($valorObjeto));



            $repositorioLicitacao   = new Negocio_Repositorio_LicitacaoPortal();

            $clicpoproc = new Negocio_ValorObjeto_Clicpoproc($dtoArray['clicpoproc']);
            $alicpoanop = new Negocio_ValorObjeto_Alicpoanop($dtoArray['alicpoanop']);
            $cgrempcodi = new Negocio_ValorObjeto_Cgrempcodi($dtoArray['cgrempcodi']);
            $ccomlicodi = new Negocio_ValorObjeto_Ccomlicodi($dtoArray['ccomlicodi']);
            $corglicodi = new Negocio_ValorObjeto_Corglicodi($dtoArray['corglicodi']);
            $dtoArray   = array_merge($dtoArray, (array) $repositorioLicitacao->procurar($clicpoproc, $alicpoanop, $cgrempcodi, $ccomlicodi, $corglicodi));

            $repositorioModalidade = new Negocio_Repositorio_ModalidadeLicitacao();
            $dtoArray = array_merge(
                $dtoArray,
                (array) $repositorioModalidade->consultarPorCodigo(
                    new Negocio_ValorObjeto_Cmodlicodi($dtoArray['cmodlicodi'])
                )
            );
            $repositorioOrgaoLicitante = new Negocio_Repositorio_OrgaoLicitante();
            $dtoArray = array_merge(
                $dtoArray,
                (array) current($repositorioOrgaoLicitante->selecionaDescricaoOrgaoLicitante($corglicodi->getValor()))
            );
            $repositorioComissao = new Negocio_Repositorio_ComissaoLicitacao();
            $dtoArray = array_merge(
                $dtoArray,
                (array) $repositorioComissao->procurar($ccomlicodi)
            );
            $repositorioFornecedor = new Negocio_Repositorio_FornecedorCredenciado();
            $dtoArray = array_merge(
                $dtoArray,
                (array) $repositorioFornecedor->procurar(new Negocio_ValorObjeto_Aforcrsequ($dtoArray['aforcrsequ']))
            );
            $dtoArray['ccomlicodi'] = $ccomlicodi->getValor();

            //$repositorioDocumento   = new Negocio_Repositorio_DocumentoAtaRP();
            //$dtoArray['documentos'] = $repositorioDocumento->procurarPorCarpnosequ(new Negocio_ValorObjeto_Carpnosequ($dtoArray['carpnosequ']));

            $dtoArray['documentos'] = $this->getNegocio()->consultarDocumentos($dtoArray['carpnosequ']);

            $repositorioParticipante    = new Negocio_Repositorio_ParticipanteAtaRP();
            $listaParticipantes         = $repositorioParticipante->procurarPorCarpnosequ(new Negocio_ValorObjeto_Carpnosequ($dtoArray['carpnosequ']));

            foreach ($listaParticipantes as $participante) {
                $objeto                         = current($repositorioOrgaoLicitante->selecionaDescricaoOrgaoLicitante($participante->corglicodi));
                $participante->eorglidesc       = $objeto->eorglidesc;
                $dtoArray['participantes'][]    = $participante;
            }
        } else {
            $repositorioAtaExterna  = new Negocio_Repositorio_AtaRegistroPrecoExterna();
            $dtoArray               = array_merge($dtoArray, (array) $repositorioAtaExterna->procurar($valorObjeto));

            $repositorioModalidade = new Negocio_Repositorio_ModalidadeLicitacao();
            $dtoArray = array_merge(
                $dtoArray,
                (array) $repositorioModalidade->consultarPorCodigo(
                    new Negocio_ValorObjeto_Cmodlicodi($dtoArray['cmodlicodi'])
                )
            );

            $dtoArray['documentos'] = $this->getNegocio()->consultarDocumentos($dtoArray['carpnosequ']);


            $repositorioFornecedor = new Negocio_Repositorio_FornecedorCredenciado();
            $dtoArray = array_merge(
                $dtoArray,
                (array) $repositorioFornecedor->procurar(new Negocio_ValorObjeto_Aforcrsequ($dtoArray['aforcrsequ']))
            );


            $repositorioParticipante    = new Negocio_Repositorio_ParticipanteAtaRP();
            $listaParticipantes         = $repositorioParticipante->procurarPorCarpnosequ(new Negocio_ValorObjeto_Carpnosequ($dtoArray['carpnosequ']));

            foreach ($listaParticipantes as $participante) {
                $objeto                         = current($repositorioOrgaoLicitante->selecionaDescricaoOrgaoLicitante($participante->corglicodi));
                $participante->eorglidesc       = $objeto->eorglidesc;
                $dtoArray['participantes'][]    = $participante;
            }


        }

        return new ArrayObject($dtoArray);
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
class RegistroPreco_UI_ConsAtaRegistroPrecoExtratoAtaDetalhe extends UI_Abstrata
{

    /**
     *
     * @param unknown $itensAta
     */
    private function plotarValoresItensAta($itensAta)
    {
        $somatorio = 0;
        foreach ($itensAta as $item) {
            // $descricao = $item->eitarpdescse;
            // // Caso seja material e a descrição detalhada esteja vazia, deverá mostrar os três traços
            // if ($item->cmatepsequ != null) {
            //     $descricao = $item->eitarpdescmat == null ? '<center> --- <center>' : $item->eitarpdescmat;
            // }

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

            //var_dump($item);exit;

            // sITUAÇÃO
            $valorSituacao = ($item->fitarpsitu == 'A') ? 'ATIVO' : 'INATIVO';

            // Descrição do item
            $valorDescricao = $item->ematepdesc;
            $valorDescricaoDet = $item->eitarpdescmat;
            if ($tipo === 'servico') {
                $valorDescricao = $item->eservpdesc;
                $valorDescricaoDet = $item->eitarpdescse;
            }

            $valorTotalatual = $item->vitarpvatu * $item->aitarpqtat;

            $this->getTemplate()->ORDEM                 = $item->aitarporde;
            $this->getTemplate()->ITEMVALOR             = $item->cmatepsequ != null ? $item->cmatepsequ : $item->cservpsequ;
            $this->getTemplate()->ITEMVALOR2            = $item->citarpsequ;
            $this->getTemplate()->TIPOITEM              = $item->cmatepsequ != null ? 'M' : 'S';
            $this->getTemplate()->CAD                   = $item->cmatepsequ != null ? 'CADUM' : 'CADUS';
            $this->getTemplate()->COD_REDUZIDO          = $valorCodigo;
            $this->getTemplate()->DESCRICAO             = $valorDescricao;
            $this->getTemplate()->DESCRICAO_DETALHADA   = !empty($valorDescricaoDet) ? $valorDescricaoDet : '-';
            $this->getTemplate()->UNIDADE               = 'UN';
            $this->getTemplate()->VALOR_MARCA           = ($item->eitelpmarc == null) ? $item->eitarpmarc : $item->eitelpmarc;
            $this->getTemplate()->VALOR_MODELO          = ($item->eitelpmode == null) ? $item->eitarpmode : $item->eitelpmode;
            $this->getTemplate()->VALOR_SITUACAO_ITEM   = $valorSituacao;

            $this->getTemplate()->QTD_ORIGINAL          = converte_valor_licitacao($item->aitarpqtor);
            $this->getTemplate()->VALOR_ORIGINAL_UNIT   = converte_valor_licitacao($item->vitarpvori);
            $this->getTemplate()->VALOR_TOTAL           = converte_valor_licitacao($item->aitarpqtor * $item->vitarpvori);
            $this->getTemplate()->LOTE                  = $item->citarpnuml;
            $this->getTemplate()->QTD_ATUAL             = ($item->aitarpqtat == "0") ? "---"  : converte_valor_licitacao($item->aitarpqtat);
            $this->getTemplate()->VALOR_UNITARIO_ATUAL  = ($item->vitarpvatu == "0") ? "---"  : converte_valor_licitacao($item->vitarpvatu);
            $this->getTemplate()->VALOR_TOTAL_ATUAL     = ($valorTotalatual == "0") ? "---" : converte_valor_licitacao($valorTotalatual);
            $this->getTemplate()->block("BLOCO_ITEM");
            $somatorio = $item->vitarpvatu * $item->aitarpqtat + $somatorio;
        }
        //$this->getTemplate()->VALOR_TOTAL_SOMA = converte_valor_licitacao($somatorio);
    }

    private function getDadosDoFornecedorDaAta($fornecedor)
    {
        $cpfCnpj = ($fornecedor['aforcrccgc'] == '') ? $fornecedor['aforcrccpf'] : $fornecedor['aforcrccgc'];
        $cpfCnpjFormatado = (strlen($cpfCnpj) == 11) ? FormataCPF($cpfCnpj) : FormataCNPJ($cpfCnpj);
        $dadosFornecedor = $cpfCnpjFormatado . ' - ' . $fornecedor['nforcrrazs'];
        if (! empty($fornecedor['eforcrlogr'])) {
            $dadosFornecedor .= '<br>' . $fornecedor['eforcrlogr'] . ', ';
            $dadosFornecedor .= $fornecedor['aforcrnume'] . ', ';
            $dadosFornecedor .= $fornecedor['eforcrbair']. ', ';
            $dadosFornecedor .= $fornecedor['nforcrcida'] . ' / ' . $fornecedor['cforcresta'];
        }

        return $dadosFornecedor;
    }

    /**
     *
     * @param ArrayObject $dto
     */
    private function plotarValoresLicitacaoAta(ArrayObject $dto)
    {
        $this->getTemplate()->ORGAOURL      = $dto['corglicodi'];
        $this->getTemplate()->ATAURL        = $dto['carpnosequ'];
        $this->getTemplate()->PROCESSOURL   = $dto['clicpoproc'];
        $this->getTemplate()->ANOURL        = $dto['alicpoanop'];
        $this->getTemplate()->TIPOURL       = $dto['carpnotiat'];

        $this->getTemplate()->MODALIDADE        = $dto['cmodlicodi'];
        $this->getTemplate()->LICITACAO         = $dto['carpexcodn'];
        $this->getTemplate()->ANOLICITACAO      = $dto['aarpexanon'];
        $this->getTemplate()->ORGAOLICITANTE    = $dto['earpexorgg'];

        $this->getTemplate()->OBJETO            = (empty($dto['earpinobje'])) ? $dto['earpexobje'] : $dto['earpinobje'];
        $this->getTemplate()->DATAINICIALATA    = (empty($dto['tarpexdini'])) ? '' : ClaHelper::converterDataBancoParaBr($dto['tarpexdini']);
        $this->getTemplate()->VIGENCIAATA       = $dto['aarpexpzvg'] . ' Meses';

        $this->getTemplate()->FORNECEDORATUAL = '';

        $this->getTemplate()->TIPOCONTROLE = tipoControle($dto['farpnotsal']);

        $_SESSION['tp_ata'] = $dto['carpnotiat'];

        $this->getTemplate()->ATA_ORIGEM = '';

        if ($dto['carpnotiat'] == 'I') {

            $this->getTemplate()->STYLE       = '';

            $consultarfor = new RegistroPreco_Dados_ConsAtaRegistroPrecoExtratoAtaDetalhe();
            $consultaAta = $this->getAdaptacao()->getNegocio()->consultarAtaPorChave($dto['aarpinanon'], $dto['clicpoproc'], $dto['corglicodi'], $dto['carpnosequ']);

            $dtoConsulta = $consultarfor->consultarDCentroDeCustoUsuario(
                $consultaAta->cgrempcodi,
                $consultaAta->cusupocodi,
                $consultaAta->corglicodi
            );

            $objetoDado = current($dtoConsulta);

            $numeroAtaFormatado = $objetoDado->ccenpocorg . str_pad($objetoDado->ccenpounid, 2, '0', STR_PAD_LEFT);

            $numeroAtaFormatado .= "." . str_pad($dto['carpincodn'], 4, "0", STR_PAD_LEFT) . "/" . $dto['aarpinanon'];

            $this->getTemplate()->ATA = $numeroAtaFormatado;

            //if($dto['carpnoseq1'] != '' || $dto['carpnoseq1'] != null){
            $valorObjetoAta = new Negocio_ValorObjeto_Carpnosequ($dto['carpnosequ']);
            $entidade = $this->getAdaptacao()->getEntidade($valorObjetoAta);

            $repositorioAtaInterna  = new Negocio_Repositorio_AtaRegistroPrecoInterna();
            $entidadeAtaInterna     = (array) $repositorioAtaInterna->procurarOrigem($valorObjetoAta);
            if(!empty($entidadeAtaInterna['carpnosequ'])){
                $consultarforOrigem = new RegistroPreco_Dados_ConsAtaRegistroPrecoExtratoAtaDetalhe();

                $dtoConsultaAtaOrigem = $consultarforOrigem->consultarDCentroDeCustoUsuario($entidadeAtaInterna['cgrempcodi'], $entidadeAtaInterna['cusupocodi'], $entidadeAtaInterna['corglicodi']);

                $objetoDadoOrigem = current($dtoConsultaAtaOrigem);

                $numeroAtaFormatadoOrigem = $objetoDadoOrigem->ccenpocorg . str_pad($objetoDadoOrigem->ccenpounid, 2, '0', STR_PAD_LEFT);

                $numeroAtaFormatadoOrigem .= "." . str_pad($entidadeAtaInterna['carpincodn'], 4, "0", STR_PAD_LEFT) . "/" . $entidadeAtaInterna['alicpoanop'];

                $this->getTemplate()->ATA_ORIGEM = $numeroAtaFormatadoOrigem;
            }else{
                $this->getTemplate()->STYLE       = 'style="display:none"';
            }

            $this->getTemplate()->PROCESSO          = str_pad($dto['clicpoproc'], 4, '0', STR_PAD_LEFT);
            $this->getTemplate()->ANOPROCESSO       = $dto['alicpoanop'];
            $this->getTemplate()->COMISSAO          = $dto['ecomlidesc'];
            $this->getTemplate()->DISPLAY           = '';
            $this->getTemplate()->MODALIDADE        = $dto['emodlidesc'];
            $this->getTemplate()->ORGAOLICITANTE    = $dto['eorglidesc'];

            $this->getTemplate()->OBJETO            = ($dto['earpinobje'] == '') ? $dto['xlicpoobje'] : $dto['earpinobje'];;
            $this->getTemplate()->DATAINICIALATA    = (empty($dto['tarpindini'])) ? '' : ClaHelper::converterDataBancoParaBr($dto['tarpindini']);
            $this->getTemplate()->VIGENCIAATA       = $dto['aarpinpzvg'] . ' Meses';

            $fornecedorOriginal = $this->getDadosDoFornecedorDaAta($dto);
            $this->getTemplate()->FORNECEDORORIGINAL = $fornecedorOriginal;

            if (isset($dto['carpnoseq1']) && $dto['carpnoseq1'] != "") {
                $objFornecedor = $this->getAdaptacao()->getNegocio()->consultarFornecedorDaAtaInterna($dto['carpnoseq1']);
                $this->getTemplate()->FORNECEDORATUAL = $this->getDadosDoFornecedorDaAta((array) $objFornecedor);
            }

            if (isset($dto['participantes'])) {
                foreach ($dto['participantes'] as $participante) {
                    $this->getTemplate()->PARTICIPANTES = $participante->eorglidesc;
                    $this->getTemplate()->block("BLOCO_PARTICIPANTES");
                }
            }

            foreach ($dto['documentos'] as $key => $documento) {
                $_SESSION['documento'.$ata->carpnosequ.'arquivo'.$key] = $documento->idocatarqu;
                $this->getTemplate()->VALOR_DOCUMENTO_KEY = 'documento'.$ata->carpnosequ.'arquivo'.$key;

                $documentoDecodificado = base64_decode($documento->arquivo);

                $this->getTemplate()->HEX_DOCUMENTO  = $documentoDecodificado;

                $this->getTemplate()->VALOR_DOCUMENTO = $documento->edocatnome;

                $this->getTemplate()->block("BLOCO_DOCUMENTOS");
            }

            $this->getTemplate()->block('BLOCO_BOTAO_PARTICIPANTE_INTERNO');
        }else{

            $numeroAtaFormatado                     = $dto['carpexcodn'] . "/" . $dto['aarpexanon'];
            $this->getTemplate()->STYLE             = 'style="display:none"';
            $this->getTemplate()->DISPLAY           = 'style="display:none"';
            $this->getTemplate()->ANOPROCESSO       = $dto['aarpexanon'];
            $this->getTemplate()->ATA               = $numeroAtaFormatado;
            $this->getTemplate()->PROCESSO          = $dto['earpexproc'];
            $this->getTemplate()->MODALIDADE        = $dto['emodlidesc'];
            $this->getTemplate()->OBJETO            = ($dto['earpinobje'] == '') ? $dto['earpexobje'] : $dto['earpinobje'];;
            $fornecedorOriginal = $this->getDadosDoFornecedorDaAta($dto);
            $this->getTemplate()->FORNECEDORORIGINAL = $fornecedorOriginal;

            if (isset($dto['aforcrseq1']) && $dto['aforcrseq1'] != "") {
                $objFornecedor = $this->getAdaptacao()->getNegocio()->consultarFornecedorDaAtaExterna($dto['aforcrseq1']);
                $this->getTemplate()->FORNECEDORATUAL = $this->getDadosDoFornecedorDaAta((array) $objFornecedor);
            }


            foreach ($dto['documentos'] as $key => $documento) {
                $_SESSION['documento'.$ata->carpnosequ.'arquivo'.$key] = $documento->idocatarqu;
                $this->getTemplate()->VALOR_DOCUMENTO_KEY = 'documento'.$ata->carpnosequ.'arquivo'.$key;

                $documentoDecodificado = base64_decode($documento->arquivo);

                $this->getTemplate()->HEX_DOCUMENTO  = $documentoDecodificado;

                $this->getTemplate()->VALOR_DOCUMENTO = $documento->edocatnome;

                $this->getTemplate()->block("BLOCO_DOCUMENTOS");
            }

        }
    }

    /**
     * [__construct description]
     */
    public function __construct()
    {
        if($_GET['window']==1 || $_POST['window']==1){
            $this->setTemplate(new TemplateNovaJanela("templates/ConsAtaRegistroPrecoExtratoAtaDetalhe.html", "Registro de Preço > Extrato Atas"));
            $this->getTemplate()->BOTAO_VOLTAR  = "javascript:window.close()";
            $this->getTemplate()->WINDOW  = "1";
        }else{
            $this->setTemplate(new TemplatePaginaPadrao("templates/ConsAtaRegistroPrecoExtratoAtaDetalhe.html", "Registro de Preço > Extrato Atas"));
            $this->getTemplate()->BOTAO_VOLTAR =  "javascript:enviar('Voltar')";
            $this->getTemplate()->WINDOW  = "0";
        }

        $this->setAdaptacao(new RegistroPreco_Adaptacao_ConsAtaRegistroPrecoExtratoAtaDetalhe());
        $this->getTemplate()->NOME_PROGRAMA = "ConsAtaRegistroPrecoExtratoAtaDetalhe";
    }

    /**
     */
    public function proccessPrincipal()
    {
        unset($_SESSION['tp_ata']);
        if (isset($_REQUEST['ata'])) {
            $carpnosequ = filter_var($_REQUEST['ata'], FILTER_SANITIZE_NUMBER_INT);
            $_SESSION['ataRequest'] = $carpnosequ;
        }

        if (isset($_REQUEST['carpnosequ'])) {
            $carpnosequ = filter_var($_REQUEST['carpnosequ'], FILTER_SANITIZE_NUMBER_INT);
            $_SESSION['ataRequest'] = $carpnosequ;
        }

        $removerInativos = false;
        if (isset($_GET['inativos']) && $_GET['inativos'] == 1) {
            $removerInativos = true;
        }


        $carpnosequ = $_SESSION['ataRequest'];

        $valorObjetoAta = new Negocio_ValorObjeto_Carpnosequ($carpnosequ);
        $entidade = $this->getAdaptacao()->getEntidade($valorObjetoAta);

        $this->plotarValoresLicitacaoAta($entidade);

        $itensAta = $this->getAdaptacao()
            ->getNegocio()
            ->consultarItensAtaRegistroPreco($valorObjetoAta->getValor(), $removerInativos);

        $this->plotarValoresItensAta($itensAta);
        $this->getTemplate()->block("BLOCO_RESULTADO_ATA");
    }

    public function processVoltar()
    {
        $uri = 'ConsAtaRegistroPrecoExtratoAta.php';
        header('Location: ' . $uri);
        exit();
    }

    public function processHistorico()
    {
        $posicaoCorreta = - 1;
        $redireciona    = false;
        $itens          = $_POST['item'];
        $itemValor      = $_POST['ITEMVALOR'];


        if (!empty($itens)) {


            $pieces = explode("/", $itens);

            $itens = $pieces[0];

            $seqItem = $pieces[1];

            $itemTipo = $_POST['tipoItem'][$itens][$seqItem];


            $sccGestor = $this->getAdaptacao()
                ->getNegocio()
                ->consultarSccDoProcesso($_REQUEST['ata'], $itens, $itemTipo, '=', $_REQUEST['orgao']);

            $sccParticipante = $this->getAdaptacao()
                ->getNegocio()
                ->consultarSccDoProcesso($_REQUEST['ata'], $itens, $itemTipo, '!=', $_REQUEST['orgao']);

            $itensAtaParticipante
                = $this
                ->getAdaptacao()
                ->getNegocio()
                ->consultarItensAtaParticipante(
                    $_REQUEST['ata'],
                    $itens,
                    $itemTipo,
                    $seqItem
                );


            if (empty($sccGestor) && empty($sccParticipante) && empty($itensAtaParticipante)) {
                $mensagem = ExibeMensStr('Não existem informações relacionadas com este item', 1, 1);
                $this->getTemplate()->MENSAGEM_ERRO = $mensagem;
                $this->getTemplate()->block('BLOCO_ERRO', true);
                $this->proccessPrincipal();
            }else{
                $uri = "ConsDetalheHistoricoParticipanteExtratoAta.php?window=".$_REQUEST['window']."&ata=" . $_REQUEST['ata'] . "&tipo=" . $_REQUEST['tipo'] . "&orgao=" . $_REQUEST['orgao'] . "&item=" . $itens . "&tipoItem=" . $itemTipo . "&seqItem=" . $seqItem;
                header('Location: ' . $uri);
                exit();
            }

        } else {
            $orgao      = $_REQUEST['orgao'];
            $ata        = $_REQUEST['ata'];
            $processo   = $_REQUEST['processo'];
            $ano        = $_REQUEST['ano'];
            $tipo       = $_REQUEST['tipo'];

            $this->getTemplate()->ORGAOURL      = $_REQUEST['orgao'];
            $this->getTemplate()->ATAURL        = $_REQUEST['ata'];
            $this->getTemplate()->PROCESSOURL   = $_REQUEST['processo'];
            $this->getTemplate()->ANOURL        = $_REQUEST['ano'];
            $this->getTemplate()->TIPOURL       = $_REQUEST['tipo'];

            $mensagem = ExibeMensStr('É preciso selecionar um item da ata', 1, 1);
            $this->getTemplate()->MENSAGEM_ERRO = $mensagem;
            $this->getTemplate()->block('BLOCO_ERRO', true);
            $this->proccessPrincipal();

            //$uri = 'ConsAtaRegistroPrecoExtratoAtaDetalhe.php?processo=' . $processo . '&ano=' . $ano . '&ata=' . $ata . '&tipo=' . $tipo . '&orgao=' . $orgao;
            //header('Location: ' . $uri);

        }
    }

    public function processHistoricoCarona()
    {
        $posicaoCorreta = - 1;
        $redireciona    = false;
        $itens          = $_POST['item'];
        $itemValor      = $_POST['ITEMVALOR'];

        if (!empty($itens)) {

            $pieces = explode("/", $itens);
            $itens = $pieces[0];
            $seqItem = $pieces[1];
            $itemTipo = $_POST['tipoItem'][$itens][$seqItem];

            $sccGestor = $this->getAdaptacao()->getNegocio()->consultarSccDoProcesso(
                $_REQUEST['ata'],
                $itens, $itemTipo,
                '=',
                $_REQUEST['orgao']
            );

            $sccParticipante = $this->getAdaptacao()->getNegocio()->consultarSccDoProcesso(
                $_REQUEST['ata'],
                $itens,
                $itemTipo,
                '!=',
                $_REQUEST['orgao']
            );

            $itensAtaCarona = $this->getAdaptacao()->getNegocio()->consultarItensAtaCarona(
                $_REQUEST['ata'],
                $itens,
                $itemTipo,
                $seqItem
            );

            if (empty($sccGestor) && empty($sccParticipante) && empty($itensAtaCarona)) {
                $mensagem = ExibeMensStr('Não existem informações relacionadas com este item', 1, 1);
                $this->getTemplate()->MENSAGEM_ERRO = $mensagem;
                $this->getTemplate()->block('BLOCO_ERRO', true);
                $this->proccessPrincipal();
            } else {
                $uri = "ConsDetalheHistoricoCaronaAtaExtratoAta.php?window=".$_REQUEST['window']."&ata=" . $_REQUEST['ata'] . "&tipo=" . $_REQUEST['tipo'] . "&orgao=" . $_REQUEST['orgao'] . "&item=" . $itens . "&tipoItem=" . $itemTipo . "&seqItem=" . $seqItem. "&inativos=" . $_GET['inativos'];
                header('Location: ' . $uri);
                exit();
            }

        } else {
            $orgao      = $_REQUEST['orgao'];
            $ata        = $_REQUEST['ata'];
            $processo   = $_REQUEST['processo'];
            $ano        = $_REQUEST['ano'];
            $tipo       = $_REQUEST['tipo'];

            $this->getTemplate()->ORGAOURL      = $_REQUEST['orgao'];
            $this->getTemplate()->ATAURL        = $_REQUEST['ata'];
            $this->getTemplate()->PROCESSOURL   = $_REQUEST['processo'];
            $this->getTemplate()->ANOURL        = $_REQUEST['ano'];
            $this->getTemplate()->TIPOURL       = $_REQUEST['tipo'];

            //$uri = 'ConsAtaRegistroPrecoExtratoAtaDetalhe.php?processo=' . $processo . '&ano=' . $ano . '&ata=' . $ata . '&tipo=' . $tipo . '&orgao=' . $orgao;
            //header('Location: ' . $uri);
            $mensagem = ExibeMensStr('É preciso selecionar um item da da ata', 1, 1);
            $this->getTemplate()->MENSAGEM_ERRO = $mensagem;
            $this->getTemplate()->block('BLOCO_ERRO', true);
            $this->proccessPrincipal();

        }

    }

    public function imprimir() {

        $ata        = $_REQUEST['ata'];
        $orgao      = $_REQUEST['orgao'];
        $item       = $_REQUEST['item'];

        if (!empty($ata) === true) {
            $valorTipo = $_SESSION['tp_ata'];
            $uri = 'PdfVisualizarExtratoAtaCompleta.php?ata=' . $ata . '&tipoAta='. $valorTipo;
            header('Location: ' . $uri);

        } else {

            $mensagem = ExibeMensStr('É preciso informa uma ata', 1, 1);
            $this->getTemplate()->MENSAGEM_ERRO = $mensagem;
            $this->getTemplate()->block('BLOCO_ERRO', true);

        }



    }
}
$app = new RegistroPreco_UI_ConsAtaRegistroPrecoExtratoAtaDetalhe();

$acao = filter_input(INPUT_POST, 'Botao', FILTER_SANITIZE_STRING);

switch ($acao) {
    case 'Voltar':
        $app->processVoltar();
        break;
    case 'Pesquisar':
        $app->consultarExtratoAta();
        $app->proccessPrincipal();
        break;
    case 'Grupo':
        $app->processGrupos();
        $app->proccessPrincipal();
        break;
    case 'PesquisaFornecedor':
        $app->processFornecedor();
        $app->proccessPrincipal();
        break;
    case 'PesquisaMaterial':
        $app->processMaterial();
        $app->proccessPrincipal();
        break;
    case 'PesquisaServico':
        $app->processServico();
        $app->proccessPrincipal();
        break;
    case 'HistoricoParticipante':
        $app->processHistorico();
        break;
    case 'HistoricoCarona':
        $app->processHistoricoCarona();
        break;
    case 'Imprimir':
        $app->imprimir();
        break;
    default:
        $app->proccessPrincipal();
        break;
}

echo $app->getTemplate()->show();

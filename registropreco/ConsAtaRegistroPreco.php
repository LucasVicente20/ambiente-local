<?php

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
 */

 // 220038--
 
if (! require_once dirname(__FILE__) . '/../bootstrap.php') {
    throw new Exception('Error Processing Request - Autoload', 1);
}

/**
 */
class RegistroPreco_Dados_ConsAtaRegistroPreco extends Dados_Abstrata
{

    /**
     * [consultarOrgaosParticipantesAtas description].
     *
     * @return array
     */
    public function consultarOrgaosParticipantesAtas()
    {
        $res = $this->executarSQL(Dados_Sql_Orgao::sqlOrgaoParticipante());

        $this->hasError($res);

        return $res;
    }

    /**
     * [consultarOrgaosGerenciador description].
     *
     * @return array
     */
    public function consultarOrgaosGerenciador()
    {
        $res = $this->executarSQL(Dados_Sql_Orgao::sqlOrgaoGerenciadorComAtaInterna());
        $this->hasError($res);
        return $res;
    }

    /**
     * [selecionarAta description]
     *
     * @param [type] $entidade
     *            [description]
     * @return [type] [description]
     */
    public function selecionarAta($entidade)
    {
        $identificadorGrupo = $entidade['identificadorGrupo'];

        $vigencia = $entidade['tipoAta'] == 'I' ? 'arpi.aarpinpzvg' : 'arpi.aarpexpzvg';
        $situacao = $entidade['tipoAta'] == 'I' ? 'arpi.farpinsitu' : 'arpi.farpexsitu';

        $sql = "SELECT DISTINCT (arpn.tarpnoincl + ($vigencia || ' month')::INTERVAL) AS vigencia, arpn.carpnosequ, $situacao";

        if ($entidade['tipoAta'] == 'I') {
            $sql .= ",arpi.cgrempcodi, arpi.clicpoproc,arpi.alicpoanop,arpi.ccomlicodi,arpi.earpinobje,arpi.corglicodi,cl.ecomlidesc, ol.eorglidesc,";
        } elseif ($entidade['tipoAta'] == 'E') {
            $sql .= ",arpi.earpexorgg,arpi.earpexproc, arpi.aarpexanon,arpi.earpexobje,";
        }

        $sql .= "arpn.carpnotiat FROM sfpc.tbataregistropreconova arpn";

        if ($entidade['tipoAta'] == 'I') {
            $sql .= " JOIN sfpc.tbataregistroprecointerna arpi";
            $sql .= " ON arpi.carpnosequ = arpn.carpnosequ";
        } elseif ($entidade['tipoAta'] == 'E') {
            $sql .= " JOIN sfpc.tbataregistroprecoexterna arpi";
            $sql .= " ON arpi.carpnosequ = arpn.carpnosequ";
        }

        $sql .= " LEFT JOIN sfpc.tbparticipanteatarp parp";
        $sql .= " ON parp.carpnosequ = arpn.carpnosequ";
        $sql .= " LEFT JOIN sfpc.tbitemataregistropreconova iarpn";
        $sql .= " ON iarpn.carpnosequ = arpn.carpnosequ";

        if (! empty($entidade['material'])) {
            $sql .= " LEFT JOIN sfpc.tbmaterialportal mp";
            $sql .= " ON mp.cmatepsequ = iarpn.cmatepsequ";

            if ($identificadorGrupo == 'M') {
                $sql .= " JOIN sfpc.tbsubclassematerial scm";
                $sql .= " ON scm.csubclsequ = mp.csubclsequ";
                $sql .= " JOIN sfpc.tbclassematerialservico cms";
                $sql .= " ON (cms.cclamscodi = scm.cclamscodi)";
            }
        }
        if (! empty($entidade['servico'])) {
            $sql .= " JOIN sfpc.tbservicoportal sp";
            $sql .= " ON sp.cservpsequ = iarpn.cservpsequ";
            if ($identificadorGrupo == 'S') {
                $sql .= " JOIN sfpc.tbsubclassematerial scm";
                $sql .= " ON scm.csubclsequ = mp.csubclsequ";
            }
        }

        if ($entidade['tipoAta'] == 'I') {
            $sql .= " JOIN sfpc.tbcomissaolicitacao cl";
            $sql .= " ON cl.ccomlicodi = arpi.ccomlicodi";
            $sql .= " JOIN sfpc.tborgaolicitante ol";
            $sql .= " ON ol.corglicodi = arpi.corglicodi";
        }
        $sql .= " WHERE 1 = 1";
        if (! empty($entidade['tipoAta'])) {
            $sql .= " AND arpn.carpnotiat = '%s'";
            $sql = sprintf($sql, $entidade['tipoAta']);
        }
        if (! empty($entidade['numeroAta'])) {
            $sql .= " AND arpn.carpnosequ = %d ";
            $sql = sprintf($sql, $entidade['numeroAta']);
        }

        if (! empty($entidade['processo'])) {
            $sql .= " AND arpi.clicpoproc = %d ";
            $sql = sprintf($sql, $entidade['processo']);
        }

        if (! empty($entidade['ano'])) {
            $sql .= " AND arpi.alicpoanop = %s ";
            $sql = sprintf($sql, $entidade['ano']);
        }

        if (! empty($entidade['orgaoGerenciador'])) {
            $sql .= " AND arpi.corglicodi = %d ";
            $sql = sprintf($sql, $entidade['orgaoGerenciador']);
        }

        if (! empty($entidade['orgaoParticipante'])) {
            $sql .= " AND parp.corglicodi = %d ";
            $sql = sprintf($sql, $entidade['orgaoParticipante']);
        }

        if (! empty($entidade['fornecedor'])) {
            $sql .= " AND arpi.aforcrsequ = %d ";
            $sql = sprintf($sql, $entidade['fornecedor']);
        }

        if (! empty($entidade['material'])) {
            $sql .= " AND iarpn.cmatepsequ = %d ";
            $sql = sprintf($sql, $entidade['material']);
        }

        if (! empty($entidade['servico'])) {
            $sql .= " AND iarpn.cservpsequ = %d ";
            $sql = sprintf($sql, $entidade['servico']);
        }

        if (! empty($entidade['identificadorGrupo']) && (! empty($entidade['material']) || ! empty($entidade['servico']))) {
            $sql .= " AND cms.cgrumscodi = %d ";
            $sql = sprintf($sql, $entidade['identificadorGrupo']);
        }

        if (empty($isativo) && ! empty($entidade['material'])) {
            $sql .= " AND mp.cmatepsitu = 'A'";
        }

        if (empty($isativo) && ! empty($entidade['servico'])) {
            $sql .= " AND sp.cservpsitu = 'A'";
        }

        if (! empty($entidade['vigentes'])) {
            $sql .= " AND CAST((EXTRACT(DAY FROM NOW() - arpn.tarpnoincl)/365)*12 AS int) < $vigencia";
        }

        if ($entidade['tipoAta'] == 'I') {
            $sql .= " GROUP BY arpi.cgrempcodi,arpi.corglicodi,ol.eorglidesc,arpn.carpnotiat,arpi.clicpoproc,arpi.farpinsitu,vigencia,arpi.alicpoanop,arpi.ccomlicodi,arpi.earpinobje,arpn.carpnosequ,cl.ecomlidesc";
        } else {
            $sql .= " GROUP BY arpi.earpexorgg,arpi.earpexproc, arpn.carpnotiat,arpi.aarpexanon,arpi.earpexobje,vigencia,arpn.carpnosequ,$situacao";
        }
        $sql .= " ORDER BY arpn.carpnosequ";

        return ClaDatabasePostgresql::executarSQL($sql);
    }

    /**
     * [consultarAta description]
     *
     * @param [type] $param
     *            [description]
     * @return [type] [description]
     */
    public function consultarAta($param)
    {
        $sql = "SELECT arpn.*, arp.*, ";
        if ($param['tipo'] == 'I') {
            $sql .= "ol.*, ";
        }
        $sql .= " forn.nforcrrazs, forn.aforcrccgc, forn.aforcrccpf, forn.eforcrlogr, ";
        $sql .= " forn.aforcrnume, forn.eforcrbair, forn.nforcrcida, forn.cforcresta ";
        if ($param['tipo'] == 'I') {
            $sql .= " ,lic.clicpocodl, lic.alicpoanol";
        }

        $sql .= " FROM sfpc.tbataregistropreconova arpn";

        if ($param['tipo'] == 'I') {
            $sql .= " JOIN sfpc.tbataregistroprecointerna arp";
            $sql .= " ON arp.carpnosequ = arpn.carpnosequ";
            $sql .= " JOIN sfpc.tborgaolicitante ol";
            $sql .= " ON ol.corglicodi = arp.corglicodi";

            $sql .= " JOIN sfpc.tblicitacaoportal lic";
            $sql .= " ON lic.clicpoproc = arp.clicpoproc";
            $sql .= " AND lic.alicpoanop = arp.alicpoanop";
            $sql .= " AND lic.cgrempcodi = arp.cgrempcodi";
            $sql .= " AND lic.ccomlicodi = arp.ccomlicodi";
            $sql .= " AND lic.corglicodi = arp.corglicodi";
        }
        if ($param['tipo'] == 'E') {
            $sql .= " JOIN sfpc.tbataregistroprecoexterna arp";
            $sql .= " ON arp.carpnosequ = arpn.carpnosequ";
        }

        $sql .= " JOIN sfpc.tbfornecedorcredenciado forn";
        $sql .= " ON forn.aforcrsequ = arp.aforcrsequ";

        $sql .= " WHERE 1 =1";
        $sql .= " AND arpn.carpnosequ =" . intval($param['ata']);

        if ($param['tipo'] == 'I') {
            $sql .= " AND  arp.clicpoproc =" . intval($param['processo']);
            $sql .= " AND  arp.corglicodi =" . intval($param['orgao']);
            $sql .= " AND  arp.alicpoanop =" . intval($param['ano']);
        }
        if ($param['tipo'] == 'E') {
            $sql .= " AND arp.earpexproc LIKE  '" . $param['processo'] . "' ";
        }

        return ClaDatabasePostgresql::executarSQL($sql);
    }

    /**
     *
     * @param integer $carpnosequ
     *
     * @return array
     */
    public function consultarItensAta(Negocio_ValorObjeto_Carpnosequ $carpnosequ)
    {
        $sql = "
            SELECT iarpn.carpnosequ, iarpn.citarpsequ, iarpn.aitarporde,
                   iarpn.cmatepsequ, mp.ematepdesc, iarpn.eitarpdescmat,
                   iarpn.cservpsequ, sp.eservpdesc, iarpn.eitarpdescse,
                   iarpn.aitarpqtor, iarpn.vitarpvori,
                   iarpn.aitarpqtat, iarpn.vitarpvatu,
                   iarpn.citarpnuml, iarpn.eitarpmarc, iarpn.eitarpmode, iarpn.fitarpsitu
              FROM sfpc.tbitemataregistropreconova iarpn
                   LEFT JOIN sfpc.tbmaterialportal mp ON mp.cmatepsequ = iarpn.cmatepsequ
                   LEFT JOIN sfpc.tbservicoportal sp ON sp.cservpsequ = iarpn.cservpsequ
             WHERE iarpn.carpnosequ = %d
        ";

        return ClaDatabasePostgresql::executarSQL(sprintf($sql, intval($carpnosequ->getValor())));
    }

    /**
     *
     * @param integer $ccomlicodi
     *
     * @return array
     */
    public function consultarComissao($ccomlicodi)
    {
        $sql = Dados_Sql_ComissaoLicitacao::selecionaComissaoLicitacaoPorComissao($ccomlicodi);

        return ClaDatabasePostgresql::executarSQL($sql);
    }

    /**
     *
     * @param integer $cmodlicodi
     *
     * @return array
     *
     */
    public function consultarModalidade($cmodlicodi)
    {
        $sql = Dados_Sql_ModalidadeLicitacao::selecionaModalideLicitacaoPorComissao($cmodlicodi);

        return ClaDatabasePostgresql::executarSQL($sql);
    }

    /**
     *
     * @param Negocio_ValorObjeto_LicitacaoPortal $licitacao
     *
     * @return array
     */
    public function consultarModalidadeDaLicitacao(Negocio_ValorObjeto_LicitacaoPortal $licitacao)
    {
        $sql = Dados_Sql_ModalidadeLicitacao::selecionaModalidadePorLicitacaoPortal($licitacao);

        return ClaDatabasePostgresql::executarSQL($sql);
    }

    /**
     * [consultarParticipantesAtaPeloCodigoAta description]
     *
     * @param Negocio_ValorObjeto_Carpnosequ $carpnosequ
     *            [description]
     * @return [type] [description]
     */
    public function consultarParticipantesAtaPeloCodigoAta(Negocio_ValorObjeto_Carpnosequ $carpnosequ)
    {
        $sql = "
            SELECT DISTINCT ol.corglicodi, ol.eorglidesc
              FROM sfpc.tbparticipanteatarp parp
                   INNER JOIN sfpc.tborgaolicitante ol
                           ON ol.corglicodi = parp.corglicodi
             WHERE parp.carpnosequ = %d
        ";

        $resultado = ClaDatabasePostgresql::executarSQL(sprintf($sql, intval($carpnosequ->getValor())));

        ClaDatabasePostgresql::hasError($resultado);

        return $resultado;
    }

    /**
     *
     * @param Negocio_ValorObjeto_AtaRegistroPrecoNova $valorAta
     * @return array
     */
    public function consultarDocumentosAtaPeloCodigoAta(Negocio_ValorObjeto_Carpnosequ $carpnosequ)
    {
        $sql = new Dados_Sql_DocumentoAtaRP();

        return ClaDatabasePostgresql::executarSQL($sql->selecionaDocumentoPeloCodigoAta($carpnosequ));
    }

    /**
     * [getNumeroSolicitacaoLicitacaoCompra description]
     *
     * @param [type] $codigoProcesso
     *            [description]
     * @param [type] $anoLicitacao
     *            [description]
     * @param [type] $codigoGrupo
     *            [description]
     * @param [type] $codigoComissao
     *            [description]
     * @param [type] $codigoOrgaoLicitante
     *            [description]
     * @return [type] [description]
     */
    public function getNumeroSolicitacaoLicitacaoCompra($codigoProcesso, $anoLicitacao, $codigoGrupo, $codigoComissao, $codigoOrgaoLicitante)
    {
        $sql = sprintf("
            SELECT
                    csolcosequ, clicpoproc, alicpoanop, cgrempcodi, ccomlicodi, corglicodi, cusupocodi, tsolclulat
              FROM
                    sfpc.tbsolicitacaolicitacaoportal
             WHERE
                    clicpoproc =  %d
                    AND alicpoanop = %d
                    AND cgrempcodi = %d
                    AND ccomlicodi = %d
                    AND corglicodi = %d
            ", $codigoProcesso, $anoLicitacao, $codigoGrupo, $codigoComissao, $codigoOrgaoLicitante);
        return ClaDatabasePostgresql::executarSQL($sql);
    }

    /**
     * [getLicitacaoPortal description]
     *
     * @param [type] $clicpoproc
     *            [description]
     * @param [type] $alicpoanop
     *            [description]
     * @return [type] [description]
     */
    public function getLicitacaoPortal($clicpoproc, $alicpoanop)
    {
        $sql = Dados_Sql_LicitacaoPortal::getInstancia();
        $resultado = ClaDatabasePostgresql::executarSQL($sql->getEntidade(new Negocio_ValorObjeto_Clicpoproc($clicpoproc), new Negocio_ValorObjeto_Alicpoanop($alicpoanop)));

        ClaDatabasePostgresql::hasError($resultado);

        return current($resultado);
    }

    /**
     * [consultarModalidadePorCodigo description]
     *
     * @param Negocio_ValorObjeto_Cmodlicodi $cmodlicodi
     *            [description]
     * @return [type] [description]
     */
    public function consultarModalidadePorCodigo(Negocio_ValorObjeto_Cmodlicodi $cmodlicodi)
    {
        $entidade = new Negocio_Repositorio_ModalidadeLicitacao();
        return $entidade->consultarPorCodigo($cmodlicodi);
    }
}

/**
 */
class RegistroPreco_Negocio_ConsAtaRegistroPreco extends Negocio_Abstrata
{

    /**
     * [__construct description].
     */
    public function __construct()
    {
        if (! $this->getDados() instanceof RegistroPreco_Dados_ConsAtaRegistroPreco) {
            $this->setDados(new RegistroPreco_Dados_ConsAtaRegistroPreco());
        }
    }

    /**
     *
     * @param ArrayObject $entidade
     */
    public function selecionarAta($entidade)
    {
        return $this->getDados()->selecionarAta($entidade);
    }

    /**
     *
     * @param array $param
     */
    public function consultarAta(array $param)
    {
        return $this->getDados()->consultarAta($param);
    }

    /**
     *
     * @param integer $carpnosequ
     */
    public function consultarItensAta(Negocio_ValorObjeto_Carpnosequ $carpnosequ)
    {
        return $this->getDados()->consultarItensAta($carpnosequ);
    }

    /**
     *
     * @param integer $ccomlicodi
     */
    public function consultarComissao($ccomlicodi)
    {
        return $this->getDados()->consultarComissao($ccomlicodi);
    }

    /**
     *
     * @param integer $cmodlicodi
     */
    public function consultarModalidade($cmodlicodi)
    {
        return $this->getDados()->consultarModalidade($cmodlicodi);
    }

    /**
     *
     * @param Negocio_ValorObjeto_LicitacaoPortal $licitacao
     */
    public function consultarModalidadeDaLicitacao(Negocio_ValorObjeto_LicitacaoPortal $licitacao)
    {
        return $this->getDados()->consultarModalidadeDaLicitacao($licitacao);
    }

    /**
     * [consultarParticipantesAtaPeloCodigoAta description]
     *
     * @param Negocio_ValorObjeto_Carpnosequ $carpnosequ
     *            [description]
     * @return [type] [description]
     */
    public function consultarParticipantesAtaPeloCodigoAta(Negocio_ValorObjeto_Carpnosequ $carpnosequ)
    {
        return $this->getDados()->consultarParticipantesAtaPeloCodigoAta($carpnosequ);
    }

    /**
     * [consultarDocumentosAtaPeloCodigoAta description]
     *
     * @param Negocio_ValorObjeto_Carpnosequ $carpnosequ
     *            [description]
     * @return [type] [description]
     */
    public function consultarDocumentosAtaPeloCodigoAta(Negocio_ValorObjeto_Carpnosequ $carpnosequ)
    {
        // @todo validar a chave primaria
        return $this->getDados()->consultarDocumentosAtaPeloCodigoAta($carpnosequ);
    }

    /**
     * [consultarModalidadePorCodigo description]
     *
     * @param Negocio_ValorObjeto_Cmodlicodi $cmodlicodi
     *            [description]
     * @return [type] [description]
     */
    public function consultarModalidadePorCodigo(Negocio_ValorObjeto_Cmodlicodi $cmodlicodi)
    {
        return $this->getDados()->consultarModalidadePorCodigo($cmodlicodi);
    }
}

/**
 */
class RegistroPreco_Adaptacao_ConsAtaRegistroPreco extends Adaptacao_Abstrata
{

    /**
     */
    public function __construct()
    {
        if (! $this->getNegocio() instanceof RegistroPreco_Negocio_ConsAtaRegistroPreco) {
            $this->setNegocio(new RegistroPreco_Negocio_ConsAtaRegistroPreco());
        }
    }

    /**
     * [selecionarAta description].
     *
     * @return array [description]
     */
    public function selecionarAta()
    {
        $entidade = new ArrayObject(array(
            'tipoAta' => filter_var($_POST['tipoAta'], FILTER_SANITIZE_STRING),
            'numeroAta' => filter_var($_POST['numeroAta'], FILTER_SANITIZE_STRING),
            'processo' => filter_var($_POST['processo'], FILTER_SANITIZE_STRING),
            'ano' => filter_var($_POST['ano'], FILTER_SANITIZE_STRING),
            'orgaoGerenciador' => filter_var($_POST['orgaoGerenciador'], FILTER_SANITIZE_STRING),
            'orgaoParticipante' => filter_var($_POST['orgaoParticipante'], FILTER_SANITIZE_STRING),
            'cpfcnpj' => filter_var($_POST['cpfcnpj'], FILTER_SANITIZE_STRING),
            'fornecedor' => filter_var($_POST['fornecedor'], FILTER_SANITIZE_STRING),
            'fornecedorCod' => filter_var($_POST['fornecedorCod'], FILTER_SANITIZE_STRING),
            'pesquisaMaterial' => filter_var($_POST['pesquisaMaterial'], FILTER_SANITIZE_STRING),
            'material' => filter_var($_POST['material'], FILTER_SANITIZE_STRING),
            'codMaterial' => filter_var($_POST['codMaterial'], FILTER_SANITIZE_STRING),
            'pesquisaServico' => filter_var($_POST['pesquisaServico'], FILTER_SANITIZE_STRING),
            'servico' => filter_var($_POST['servico'], FILTER_SANITIZE_STRING),
            'codServico' => filter_var($_POST['codServico'], FILTER_SANITIZE_STRING),
            'vigentes' => $_POST['vigentes'],
            'identificadorGrupo' => $_POST['identificadorGrupo']
        ));

        return $this->getNegocio()->selecionarAta($entidade);
    }

    /**
     *
     * @param array $valores
     */
    public function consultarAta(array $valores)
    {
        $chaves = array(
            'processo',
            'ano',
            'ata',
            'orgao',
            'tipo'
        );

        return $this->getNegocio()->consultarAta(array_combine($chaves, $valores));
    }

    /**
     *
     * @param integer $carpnosequ
     */
    public function consultarItensAta($carpnosequ)
    {
        return $this->getNegocio()->consultarItensAta(new Negocio_ValorObjeto_Carpnosequ($carpnosequ));
    }

    /**
     *
     * @param integer $ccomlicodi
     */
    public function consultarComissao($ccomlicodi)
    {
        return $this->getNegocio()->consultarComissao($ccomlicodi);
    }

    /**
     * Consultar a Modalidade da Licitação utilizando os dados da Licitação
     *
     * @param stdClass $dados
     *
     * @return stdClass
     */
    public function consultarModalidadeDaLicitacao($dados)
    {
        if ($dados->carpnotiat == 'I') {
            assercao(! empty($dados->clicpoproc), 'clicpoproc requirido');
            assercao(! empty($dados->alicpoanop), 'alicpoanop requirido');
            assercao(! empty($dados->cgrempcodi), 'cgrempcodi requirido');
            assercao(! empty($dados->ccomlicodi), 'ccomlicodi requirido');
            assercao(! empty($dados->corglicodi), 'corglicodi requirido');

            $valorObjeto = new Negocio_ValorObjeto_LicitacaoPortal();
            $valorObjeto->setClicpoproc($dados->clicpoproc);
            $valorObjeto->setAlicpoanop($dados->alicpoanop);
            $valorObjeto->setCgrempcodi($dados->cgrempcodi);
            $valorObjeto->setCcomlicodi($dados->ccomlicodi);
            $valorObjeto->setCorglicodi($dados->corglicodi);

            return $this->getNegocio()->consultarModalidadeDaLicitacao($valorObjeto);
        } else {
            return $this->getNegocio()->consultarModalidadePorCodigo(new Negocio_ValorObjeto_Cmodlicodi($dados->cmodlicodi));
        }
    }

    /**
     *
     * @param integer $carpnosequ
     */
    public function consultarParticipantesAtaPeloCodigoAta($carpnosequ)
    {
        assercao(! empty($carpnosequ), '$carpnosequ requirido');
        return $this->getNegocio()->consultarParticipantesAtaPeloCodigoAta(new Negocio_ValorObjeto_Carpnosequ($carpnosequ));
    }

    /**
     *
     * @param integer $carpnosequ
     */
    public function consultarDocumentosAtaPeloCodigoAta($carpnosequ)
    {
        assercao(! empty($carpnosequ), '$carpnosequ requirido');
        return $this->getNegocio()->consultarDocumentosAtaPeloCodigoAta(new Negocio_ValorObjeto_Carpnosequ($carpnosequ));
    }

    public function getNumeroSolicitacaoLicitacaoCompra($ata)
    {
        if (empty($ata->clicpoproc) || empty($ata->alicpoanop) || empty($ata->cgrempcodi) || empty($ata->ccomlicodi) || empty($ata->corglicodi)) {
            throw new Excecao('$ata não possuem informações suficientes');
            return false;
        }

        $solicitacao = $this->getNegocio()
            ->getDados()
            ->getNumeroSolicitacaoLicitacaoCompra($ata->clicpoproc, $ata->alicpoanop, $ata->cgrempcodi, $ata->ccomlicodi, $ata->corglicodi);
        $solicitacao = current($solicitacao);

        return $solicitacao;
    }
}

/**
 */
class RegistroPreco_UI_ConsAtaRegistroPreco extends UI_Abstrata
{

    /**
     * [plotarBlocoOrgao description].
     *
     * @param [type] $orgaos
     *            [description]
     *
     * @return void [description]
     */
    private function plotarBlocoOrgaoParticipantesAtas()
    {
        $orgaos = $this->getAdaptacao()
            ->getNegocio()
            ->getDados()
            ->consultarOrgaosParticipantesAtas();
        $orgaoNumeracao = filter_input(INPUT_POST, 'orgaoParticipante', FILTER_VALIDATE_INT);

        foreach ($orgaos as $orgao) {
            $this->getTemplate()->ORGAO_VALUE = $orgao->corglicodi;

            $this->getTemplate()->ORGAO_TEXT = $orgao->eorglidesc;

            // Vendo se a opção atual deve ter o atributo "selected"
            if ($orgaoNumeracao == $orgao->corglicodi) {
                $this->getTemplate()->ORGAO_SELECTED = 'selected';
            } else {
                $this->getTemplate()->clear('ORGAO_SELECTED');
            }

            $this->getTemplate()->block('BLOCO_CONSULTAR_FORM_ORGAO_PARTICIPANTE');
        }
    }

    /**
     * [plotarBlocoOrgaoGerenciador description].
     *
     * @return array [description]
     */
    private function plotarBlocoOrgaoGerenciador()
    {
        $orgaos = $this->getAdaptacao()
            ->getNegocio()
            ->getDados()
            ->consultarOrgaosGerenciador();

        $orgaoNumeracao = filter_input(INPUT_POST, 'orgaoGerenciador', FILTER_VALIDATE_INT);

        foreach ($orgaos as $orgao) {
            $this->getTemplate()->ORGAO_VALUE_GERENCIADOR = $orgao->corglicodi;

            $this->getTemplate()->ORGAO_TEXT_GERENCIADOR = $orgao->eorglidesc;

            // Vendo se a opção atual deve ter o atributo "selected"
            if ($orgaoNumeracao == $orgao->corglicodi) {
                $this->getTemplate()->ORGAO_SELECTED_GERENCIADOR = 'selected';
            } else {
                $this->getTemplate()->clear('ORGAO_SELECTED_GERENCIADOR');
            }

            $this->getTemplate()->block('BLOCO_CONSULTAR_FORM_ORGAO_GERENCIADOR');
        }
    }

    /**
     *
     * @param stdClass $entidadeAta
     */
    private function plotarBlocoDetalhe($entidadeAta)
    {
        if ($entidadeAta->carpnotiat == 'I') {
            $codigoSolicitacaoCompra = Helper_RegistroPreco::getNumeroSolicitacaoLicitacaoCompra($entidadeAta->clicpoproc, $entidadeAta->alicpoanop, $entidadeAta->cgrempcodi, $entidadeAta->ccomlicodi, $entidadeAta->corglicodi);

            $this->getTemplate()->ATA = Helper_RegistroPreco::getNumeroAta($entidadeAta->corglicodi, $codigoSolicitacaoCompra->csolcosequ, $entidadeAta->carpnosequ, $entidadeAta->alicpoanop);

            $this->getTemplate()->PROCESSO = str_pad($entidadeAta->clicpoproc, 4, '0', STR_PAD_LEFT);
            $this->getTemplate()->ANOPROCESSO = $entidadeAta->alicpoanop;
            $descComissao = end($this->getAdaptacao()
                ->getNegocio()
                ->getDados()
                ->consultarComissao($entidadeAta->ccomlicodi));
            $this->getTemplate()->COMISSAO = $descComissao->ecomlidesc;
            $entidadeModalidade = $this->getAdaptacao()->consultarModalidadeDaLicitacao($entidadeAta);

            $this->getTemplate()->MODALIDADE = $entidadeModalidade->emodlidesc;
            $this->getTemplate()->LICITACAO = str_pad($entidadeAta->clicpocodl, 4, '0', STR_PAD_LEFT);
            $this->getTemplate()->ANOLICITACAO = $entidadeAta->alicpoanol;
            $this->getTemplate()->ORGAOLICITANTE = $entidadeAta->eorglidesc;
            $valorObjeto = $this->getAdaptacao()
                ->getNegocio()
                ->getDados()
                ->getLicitacaoPortal($entidadeAta->clicpoproc, $entidadeAta->alicpoanop);
            $this->getTemplate()->OBJETO = $valorObjeto->xlicpoobje;
            $this->getTemplate()->DATAINICIALATA = (empty($entidadeAta->tarpindini)) ? '' : ClaHelper::converterDataBancoParaBr($entidadeAta->tarpindini);
            $this->getTemplate()->VIGENCIAATA = $entidadeAta->aarpinpzvg . ' Meses';

            $fornecedorOriginal = RegistroPreco_UI_Helper_Fornecedor::mapear(Helper_RegistroPreco::getFornecedorDaAtaInterna($entidadeAta->carpnosequ));
            $this->getTemplate()->FORNECEDORORIGINAL = RegistroPreco_UI_Helper_Fornecedor::trataDadosDoFornecedorDaAta($fornecedorOriginal);

            if (isset($entidadeAta->carpnoseq1) && $entidadeAta->carpnoseq1 != "") {
                $fornecedorAtual = RegistroPreco_UI_Helper_Fornecedor::mapear(Helper_RegistroPreco::getFornecedorDaAtaInterna($entidadeAta->carpnoseq1));
                $this->getTemplate()->FORNECEDORATUAL = RegistroPreco_UI_Helper_Fornecedor::trataDadosDoFornecedorDaAta($fornecedorAtual);
            }

            if (isset($entidadeAta->aforcrseq1) && $entidadeAta->aforcrseq1 != "") {
            }
        } else {
            $this->getTemplate()->ATA = $entidadeAta->carpexcodn;
            $this->getTemplate()->PROCESSO = $entidadeAta->earpexproc;
            $this->getTemplate()->ANOPROCESSO = $entidadeAta->aarpexanon;
            $entidadeModalidade = $this->getAdaptacao()->consultarModalidadeDaLicitacao($entidadeAta);

            $this->getTemplate()->MODALIDADE = $entidadeModalidade->emodlidesc;
            $this->getTemplate()->ORGAOLICITANTE = $entidadeAta->earpexorgg;
            $this->getTemplate()->OBJETO = $entidadeAta->earpexobje;
            $this->getTemplate()->DATAINICIALATA = (empty($entidadeAta->tarpexdini)) ? '' : ClaHelper::converterDataBancoParaBr($entidadeAta->tarpexdini);
            $this->getTemplate()->VIGENCIAATA = $entidadeAta->aarpexpzvg . ' Meses';

            $fornecedorOriginal = new stdClass();
            $fornecedorOriginal->fornecedorOriginal = $entidadeAta->nforcrrazs;
            $fornecedorOriginal->numeroInscricaoFornecedor = (empty($entidadeAta->aforcrccgc)) ? $entidadeAta->aforcrccpf : $entidadeAta->aforcrccgc;
            $fornecedorOriginal->logradouroFornecedor = $entidadeAta->eforcrlogr;
            $fornecedorOriginal->numeroLogradouroFornecedor = $entidadeAta->aforcrnume;
            $fornecedorOriginal->bairroFornecedor = $entidadeAta->eforcrbair;
            $fornecedorOriginal->cidadeFornecedor = $entidadeAta->nforcrcida;
            $fornecedorOriginal->estadoFornecedor = $entidadeAta->cforcresta;
            $this->getTemplate()->FORNECEDORATUAL = RegistroPreco_UI_Helper_Fornecedor::trataDadosDoFornecedorDaAta($fornecedorOriginal);
        }

        $participantes = $this->getAdaptacao()->consultarParticipantesAtaPeloCodigoAta($entidadeAta->carpnosequ);
        foreach ($participantes as $participante) {
            $this->getTemplate()->PARTICIPANTES = $participante->eorglidesc;
            $this->getTemplate()->block("BLOCO_DETALHE_PARTICIPANTE");
        }
        $documentos = $this->getAdaptacao()->consultarDocumentosAtaPeloCodigoAta($entidadeAta->carpnosequ);
        $path = $GLOBALS["DNS_UPLOADS"] . "registropreco/";
        foreach ($documentos as $documento) {
            $this->getTemplate()->ENDERECO_DOWNLOAD_DOCUMENTO = $path . $documento->edocatnome;
            $this->getTemplate()->NOME_DOCUMENTO = $documento->edocatnome;
            $this->getTemplate()->block("BLOCO_DETALHE_DOCUMENTOS");
        }
    }

    /**
     *
     * @param unknown $itens
     */
    private function plotarBlocoDetalheResultadoAta($itens)
    {
        if (sizeof($itens) > 0) {
            $somatorio = 0;
            foreach ($itens as $item) {
                $descricao = '';
                $descricaoDetalhada = '';
                // Caso seja material e a descrição detalhada esteja vazia, deverá mostrar os três traços
                if ($item->cmatepsequ != null) {
                    $descricao = $item->ematepdesc;
                    $descricaoDetalhada = $item->eitarpdescmat == null ? '<center> --- <center>' : $item->eitarpdescmat;
                } else {
                    $descricao = $item->eservpdesc;
                    $descricaoDetalhada = $item->eitarpdescse;
                }

                $this->getTemplate()->ORDEM = $item->aitarporde;
                $this->getTemplate()->CAD = $item->cmatepsequ != null ? 'CADUM' : 'CADUS';
                $this->getTemplate()->DESCRICAO = $descricao;
                $this->getTemplate()->UNIDADE = 'UN';
                $this->getTemplate()->QTD_ORIGINAL = converte_valor_licitacao($item->aitarpqtor);
                $this->getTemplate()->VALOR_ORIGINAL_UNIT = converte_valor_licitacao($item->vitarpvori);
                $this->getTemplate()->VALOR_TOTAL = converte_valor_licitacao($item->aitarpqtor * $item->vitarpvori);
                $this->getTemplate()->LOTE = $item->citarpnuml;
                $this->getTemplate()->QTD_ATUAL = converte_valor_licitacao($item->aitarpqtat);
                $this->getTemplate()->VALOR_UNITARIO_ATUAL = converte_valor_licitacao($item->vitarpvatu);
                $this->getTemplate()->VALOR_TOTAL_ATUAL = converte_valor_licitacao($item->vitarpvatu * $item->aitarpqtat);
                $this->getTemplate()->block("BLOCO_DETALHE_ITEM");

                $somatorio = ($item->vitarpvatu * $item->aitarpqtor) + $somatorio;
                if ($item->aitarpqtat > 0) {
                    $somatorio = ($item->vitarpvatu * $item->aitarpqtat) + $somatorio;
                }
            }
            $this->getTemplate()->VALOR_TOTAL_SOMA = converte_valor_licitacao($somatorio);
            $this->getTemplate()->block('BLOCO_DETALHE_RESULTADO_ATA');
        }
    }

    /**
     * [__construct description].
     */
    public function __construct()
    {
        if (! $this->getAdaptacao() instanceof RegistroPreco_Adaptacao_ConsAtaRegistroPreco) {
            $this->setAdaptacao(new RegistroPreco_Adaptacao_ConsAtaRegistroPreco());
        }

        $this->setTemplate(new TemplatePaginaPadrao('templates/ConsAtaRegistroPreco.html', 'Registro de Preço > Consultar'));

        $this->getTemplate()->NOME_PROGRAMA = 'ConsAtaRegistroPreco';

        $this->getTemplate()->CHECK_ATA_INTERNA = 'checked';
        $this->getTemplate()->CHECK_VIGENTES = 'checked';

        if ($_SERVER['HTTP_REQUEST'] == 'POST') {
            $this->getTemplate()->CHECK_ATA_INTERNA = $this->tenario(filter_input(INPUT_POST, 'tipoAta') == 'I', 'checked', '');
            $this->getTemplate()->CHECK_ATA_EXTERNA = $this->tenario(filter_input(INPUT_POST, 'tipoAta') == 'E', 'checked', '');
            $this->getTemplate()->NRATA = filter_input(INPUT_POST, 'numeroAta', FILTER_VALIDATE_INT);
            $this->getTemplate()->PROCESSO = $_POST['processo'];
            $this->getTemplate()->LINK_ATA = $_POST['linkAta'];
            $this->getTemplate()->ANO = $_POST['ano'];
            $this->getTemplate()->CHECK_CNPJ = $this->tenario($_POST['cpfcnpj'] == 'cnpj', 'checked', '');
            $this->getTemplate()->CHECK_CPF = $this->tenario($_POST['cpfcnpj'] == 'cpf', 'checked', '');
            $this->getTemplate()->FORNECEDOR = $_POST['fornecedor'];
            $this->getTemplate()->CHECK_COD_M = $this->tenario($_POST['pesquisaMaterial'] == '0', 'selected', '');
            $this->getTemplate()->CHECK_DEC_M = $this->tenario($_POST['pesquisaMaterial'] == '1', 'selected', '');
            $this->getTemplate()->CHECK_DI_M = $this->tenario($_POST['pesquisaMaterial'] == '2', 'selected', '');
            $this->getTemplate()->MATERIAL = $_POST['material'];
            $this->getTemplate()->CHECK_COD_S = $this->tenario($_POST['pesquisaServico'] == '0', 'selected', '');
            $this->getTemplate()->CHECK_DEC_S = $this->tenario($_POST['pesquisaServico'] == '1', 'selected', '');
            $this->getTemplate()->CHECK_DI_S = $this->tenario($_POST['pesquisaServico'] == '2', 'selected', '');
            $this->getTemplate()->SERVICO = $_POST['servico'];
            $this->getTemplate()->CHECK_M = $this->tenario($_POST['identificadorGrupo'] == 'M', 'checked', '');
            $this->getTemplate()->CHECK_S = $this->tenario($_POST['identificadorGrupo'] == 'S', 'checked', '');
            $this->getTemplate()->CHECK_VIGENTES = 'checked';
            $this->getTemplate()->CHECK_VIGENTES = $this->tenario($_POST['vigentes'] == 'V', 'checked', '');
        }

        $this->plotarBlocoOrgaoParticipantesAtas();
        $this->plotarBlocoOrgaoGerenciador();
    }

    /**
     * [proccessPrincipal description].
     *
     * @return void [description]
     */
    public function proccessPrincipal()
    {
        $this->getTemplate()->block('BLOCO_CONSULTAR_FORM');
    }

    /**
     * [selecionarAta description].
     *
     * @return void [description]
     */
    public function selecionarAta()
    {
        $atas = $this->getAdaptacao()->selecionarAta();
        $ultimoOrgaoPlotado = '';
        $ultimoTipoAtaPlotado = '';

        foreach ($atas as $ata) {
            if ($ultimoTipoAtaPlotado != $ata->carpnotiat) {
                $this->getTemplate()->TIPO_ATA = $ata->carpnotiat == 'I' ? 'INTERNAS' : 'EXTERNAS';
                $this->getTemplate()->block("BLOCO_CONSULTAR_TIPO_ATA");
                $ultimoTipoAtaPlotado = $ata->carpnotiat;
            }

            if ($ultimoOrgaoPlotado != $ata->corglicodi) {
                $this->getTemplate()->ORGAO_ATA = $ata->eorglidesc;
                $this->getTemplate()->block("BLOCO_CONSULTAR_ORGAO_ATA");
                $this->getTemplate()->block("BLOCO_CONSULTAR_TITULO_RESULTADO");
                $ultimoOrgaoPlotado = $ata->corglicodi;
            }

            $mes = substr($ata->vigencia, 5, 2);
            $dia = substr($ata->vigencia, 8, 2);
            $ano = substr($ata->vigencia, 0, 4);
            $this->getTemplate()->VALOR_VIGENCIA = $dia . '/' . $mes . '/' . $ano;

            if ($ata->carpnotiat == 'I') {
                $this->getTemplate()->LINK_ATA = $ata->clicpoproc . '-' . $ata->alicpoanop . '-' . $ata->carpnosequ . '-' . $ata->corglicodi . '-' . $ultimoTipoAtaPlotado;
                $solicitacao = $this->getAdaptacao()->getNumeroSolicitacaoLicitacaoCompra($ata);
                $numeroAta = getNumeroSolicitacaoCompra(ClaDatabasePostgresql::getConexao(), $solicitacao->csolcosequ);
                $valoresExploded = explode(".", $numeroAta);
                $valorUnidadeOrc = substr($valoresExploded[0], 2, 2);

                $this->getTemplate()->VALOR_NUMERO_ATA = str_pad($ata->corglicodi, 2, '0', STR_PAD_LEFT) . $valorUnidadeOrc . "." . str_pad($ata->carpnosequ, 4, '0', STR_PAD_LEFT) . '/' . $ata->alicpoanop;

                $this->getTemplate()->VALOR_PROCESSO = str_pad($ata->clicpoproc, 4, "0", STR_PAD_LEFT) . '/' . $ata->alicpoanop . ' ' . $ata->ecomlidesc;
                $valorObjeto = $this->getAdaptacao()
                    ->getNegocio()
                    ->getDados()
                    ->getLicitacaoPortal($ata->clicpoproc, $ata->alicpoanop);
                $this->getTemplate()->VALOR_OBJETO = $valorObjeto->xlicpoobje;
            } else {
                $this->getTemplate()->LINK_ATA = $ata->earpexproc . '-0-' . $ata->carpnosequ . '-0-E';
                $this->getTemplate()->VALOR_NUMERO_ATA = $ata->carpexcodn . $ata->aarpexanon;
                $this->getTemplate()->VALOR_PROCESSO = $ata->earpexproc;
                $this->getTemplate()->VALOR_OBJETO = $ata->earpexobje;
            }

            $this->getTemplate()->VALOR_SITUACAO = $ata->farpinsitu == "A" ? "ATIVO" : 'INATIVO';
            $this->getTemplate()->block('BLOCO_CONSULTAR_RESULTADO');
            $this->getTemplate()->block('BLOCO_CONSULTAR_SUB_TITULO');
        }
        $this->getTemplate()->block('BLOCO_CONSULTAR_RESULTADO_ATA');
        $this->getTemplate()->block('BLOCO_CONSULTAR_FORM');
    }

    /**
     * [detalharAta description]
     *
     * @return [type] [description]
     */
    public function detalharAta()
    {
        $tipoSelecionado = filter_input(INPUT_POST, 'tipoSelecionado', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
        if (! is_array($tipoSelecionado) && ! isset($tipoSelecionado[0])) {
            $this->proccessPrincipal();
            return;
        }
        $formata = end(filter_var($_POST['linkAta'], FILTER_DEFAULT, FILTER_REQUIRE_ARRAY));
        $infoAta = explode('-', $formata);

        $entidadeAta = end($this->getAdaptacao()->consultarAta($infoAta));

        $this->plotarBlocoDetalhe($entidadeAta);

        $itens = $this->getAdaptacao()->consultarItensAta($entidadeAta->carpnosequ);

        $this->plotarBlocoDetalheResultadoAta($itens);
        $_SESSION['objetoAta'] = $entidadeAta;
        $this->getTemplate()->block('BLOCO_DETALHAR');
    }

    public function imprimirAta()
    {
        $pdf = new PdfConsAtaRegistroPrecoAta();
        $pdf->setEntidadeAta($_SESSION['objetoAta']);
        $pdf->gerarRelatorio();
    }
}

/**
 */
class ConsAtaRegistroPreco extends ProgramaAbstrato
{

    /**
     */
    public function __construct()
    {
        $this->setUI(new RegistroPreco_UI_ConsAtaRegistroPreco());
    }

    /**
     * (non-PHPdoc).
     *
     * @see ProgramaAbstrato::frontController()
     */
    protected function frontController()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'GET') {
            $this->getUI()->proccessPrincipal();
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $acao = filter_var($_POST['Botao'], FILTER_SANITIZE_STRING);
            switch ($acao) {
                case 'Voltar':
                    $this->getUI()->proccessPrincipal();
                    break;
                case 'Selecionar':
                    $this->getUI()->selecionarAta();
                    break;
                case 'Detalhe':
                    $this->getUI()->detalharAta();
                    break;
                case 'Extrato':
                    $this->getUI()->imprimirAta();
                    break;
            }
        }
    }

    /**
     * (non-PHPdoc).
     *
     * @see ProgramaAbstrato::configuracao()
     */
    protected function configuracao()
    {
    }
}

ProgramaAbstrato::iniciar(new ConsAtaRegistroPreco());

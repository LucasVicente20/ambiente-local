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
 * @version   GIT: EMPREL-SAD-PORTAL-COMPRAS-REGISTRO-PRECO-BL-FUNC-20160601-1550
 */

 // 220038--
 
if (! @require_once dirname(__FILE__) . "/../bootstrap.php") {
    throw new Exception("Error Processing Request - Bootstrap", 1);
}

Seguranca();

global $SimboloConcatenacaoArray, $SimboloConcatenacaoDesc;

class RegistroPreco_Dados_CadMigracaoAtaExternaAlterar extends Dados_Abstrata
{

    /**
     *
     * @param Negocio_ValorObjeto_Carpnosequ $carpnosequ
     * @return string
     */
    public function sqlAtaPorchave(Negocio_ValorObjeto_Carpnosequ $carpnosequ)
    {
        $sql = "
            select a.carpnosequ, a.aarpexanon, a.carpexcodn, a.earpexproc,	a.cmodlicodi, a.earpexorgg,
                   a.earpexobje, a.tarpexdini, a.aarpexpzvg, a.aforcrsequ, a.aforcrseq1, a.farpexsitu,
                   f.nforcrrazs, f.aforcrccgc, f.aforcrccpf, f.eforcrlogr, f.aforcrnume, f.eforcrbair,
                   f.nforcrcida, f.cforcresta, ml.emodlidesc, fa.nforcrrazs as razaoFornecedorAtual,
                   fa.aforcrccgc as cgcFornecedorAtual,
                   fa.aforcrccpf as cpfFornecedorAtual,
                   fa.eforcrlogr as logradouroFornecedorAtual,
                   fa.aforcrnume as numeroEnderecoFornecedorAtual,
                   fa.eforcrbair as bairroFornecedorAtual,
                   fa.nforcrcida as cidadeFornecedorAtual,
                   fa.cforcresta as estadoFornecedorAtual
              from sfpc.tbataregistroprecoexterna a
                   left outer join sfpc.tbfornecedorcredenciado f
                                on f.aforcrsequ = a.aforcrsequ
                   inner join sfpc.tbmodalidadelicitacao ml
                           ON ml.cmodlicodi = a.cmodlicodi
                   left outer join sfpc.tbfornecedorcredenciado fa
                                on fa.aforcrsequ = a.aforcrsequ
              where a.carpnosequ = %d
              ";

        return sprintf($sql, $carpnosequ->getValor());
    }

    public function sqlSelectOrgaosPorItem($sequencialAta, $sequencialItemAta)
    {
        $sql = "SELECT
				    ol.eorglidesc as descricao,
				    piarp.apiarpqtat as quantidade,
                    piarp.fpiarpsitu as situacao
				FROM
				    sfpc.tbparticipanteitematarp piarp
				    INNER JOIN sfpc.tborgaolicitante ol
				    	ON piarp.corglicodi = ol.corglicodi
				    INNER JOIN sfpc.tbataregistroprecoexterna arpe
				    	ON piarp.carpnosequ = arpe.carpnosequ
				WHERE
				    piarp.carpnosequ = %d
				    AND piarp.citarpsequ = %d";

        return sprintf($sql, $sequencialAta, $sequencialItemAta);
    }

    public function sqlCodigoMaximoDocumento($processo, $orgao, $ano, $grupo)
    {
        $sql = "select max(d.cdoclicodi) from sfpc.tbdocumentolicitacao d";
        $sql .= "where d.clicpoproc =" . $processo;
        $sql .= "and d.cgrempcodi =" . $grupo;
        $sql .= "and d.corglicodi =" . $orgao;
        $sql .= "and d.alicpoanop =" . $ano;

        return $sql;
    }

    public function sqlInsereDocumento($documento)
    {
        $codigoUsuario = $this->getCodigoUsuarioLogado();
        $sql = "INSERT INTO sfpc.tbdocumentoatarp";
        $sql .= " (carpnosequ, cdocatsequ, edocatnome, idocatarqu, tdocatcada, cusupocodi, tdocatulat)";
        $sql .= " VALUES($documento->carpnosequ, $documento->cdocatsequ, '$documento->edocatnome', $documento->idocatarqu, clock_timestamp(), $codigoUsuario, clock_timestamp())";

        return $sql;
    }

    public function sqlConsultarDocumento(Negocio_ValorObjeto_Carpnosequ $carpnosequ)
    {
        $sql = " select * from sfpc.tbdocumentoatarp darp where darp.carpnosequ = %d";
        return sprintf($sql, $carpnosequ->getValor());
    }

    public function sqlConsultarItemAta(Negocio_ValorObjeto_Carpnosequ $carpnosequ)
    {
        $sql = "
        select
        	iarpn.carpnosequ,
        	iarpn.citarpsequ,
        	iarpn.aitarporde,
        	iarpn.cmatepsequ,
            mp.ematepdesc,
            mp.cunidmcodi,
            u.eunidmdesc,
            u.eunidmsigl,
        	iarpn.cservpsequ,
            sp.eservpdesc,
        	iarpn.aitarpqtor,
        	iarpn.aitarpqtat,
        	iarpn.vitarpvori,
        	iarpn.vitarpvatu,
        	iarpn.citarpnuml,
        	iarpn.eitarpmarc,
        	iarpn.eitarpmode,
        	iarpn.eitarpdescmat,
        	iarpn.eitarpdescse,
        	iarpn.fitarpsitu,
        	iarpn.fitarpincl,
        	iarpn.fitarpexcl,
        	iarpn.titarpincl
        from
        	sfpc.tbitemataregistropreconova iarpn
        	left join sfpc.tbmaterialportal mp on mp.cmatepsequ = iarpn.cmatepsequ
        	left join sfpc.tbservicoportal sp on sp.cservpsequ = iarpn.cservpsequ
        	left join sfpc.tbunidadedemedida u on u.cunidmcodi = mp.cunidmcodi
        where
        	iarpn.carpnosequ = %d
        ";

        return sprintf($sql, $carpnosequ->getValor());
    }
}

class RegistroPreco_Negocio_CadMigracaoAtaExternaAlterar extends Negocio_Abstrata
{

    private function inserirDocumentoAta($conexao, $carpnosequ)
    {
        $conexao->query(sprintf("DELETE FROM sfpc.tbdocumentoatarp WHERE carpnosequ = %d", $carpnosequ));

        $documento = $conexao->getRow('SELECT MAX(cdocatsequ) FROM sfpc.tbdocumentoatarp WHERE carpnosequ = ?', array(
            (int) $carpnosequ
        ), DB_FETCHMODE_OBJECT);
        $valorMax = (int) $documento->max + 1;
        $tamanho = count($_SESSION['Arquivos_Upload']['nome']);

        $nomeTabela = 'sfpc.tbdocumentoatarp';
        $entidade = ClaDatabasePostgresql::getEntidade($nomeTabela);
        for ($i = 0; $i < $tamanho; $i ++) {
            $entidade->carpnosequ = (int) $carpnosequ;
            $entidade->cdocatsequ = (int) $valorMax;
            $entidade->edocatnome = $_SESSION['Arquivos_Upload']['nome'][$i];
            $entidade->idocatarqu = bin2hex($_SESSION['Arquivos_Upload']['conteudo'][$i]);
            $entidade->tdocatcada = 'NOW()';
            $entidade->cusupocodi = (int) $_SESSION['_cusupocodi_'];
            $entidade->tdocatulat = 'NOW()';
            $conexao->autoExecute($nomeTabela, (array) $entidade, DB_AUTOQUERY_INSERT);
            $valorMax ++;
        }
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see Negocio_Abstrata::getDados()
     */
    public function getDados()
    {
        $this->setDados(new RegistroPreco_Dados_CadMigracaoAtaExternaAlterar());
        return parent::getDados();
    }

    /**
     *
     * @param string $processo
     */
    public function consultarAtaExterna($processo)
    {
        $sql = $this->getDados()->sqlAtaParametrosTela(new Negocio_ValorObjeto_Carpnosequ($processo));
        $res = $this->getDados()->executarSQL($sql);
        $this->getDados()->hasError($res);
        return current($res);
    }

    /**
     *
     * @param integer $carpnosequ
     * @return array
     */
    public function consultarAtaPorChave($carpnosequ)
    {
        $sql = $this->getDados()->sqlAtaPorchave(new Negocio_ValorObjeto_Carpnosequ($carpnosequ));
        $res = $this->getDados()->executarSQL($sql);
        $this->getDados()->hasError($res);
        return $res;
    }

    public function consultarDocumento($carpnosequ)
    {
        $sql = $this->getDados()->sqlConsultarDocumento(new Negocio_ValorObjeto_Carpnosequ($carpnosequ));
        $res = $this->getDados()->executarSQL($sql);
        $this->getDados()->hasError($res);
        return $res;
    }

    public function consultarItemAta($carpnosequ)
    {
        $sql = $this->getDados()->sqlConsultarItemAta(new Negocio_ValorObjeto_Carpnosequ($carpnosequ));
        $res = $this->getDados()->executarSQL($sql);
        $this->getDados()->hasError($res);
        return $res;
    }


    /**
     * Negócio. Salvar.
     *
     * @param array $entidade Entidade
     * @param array $itemAta  Itens da ata
     *
     * @return boolean
     */
    public function salvar($entidade, $itemAta)
    {
        $db = $this->getDados()->getConexao();
        $db->autoCommit(false);
        $db->query("BEGIN TRANSACTION");

        try {
            $campos = (array) $entidade;
            $campos['tarpexdini'] = ClaHelper::converterDataBrParaBanco($campos['tarpexdini']);

            unset(
                $campos['aforcrsequ'],
                $campos['aforcrseq1'],
                $campos['farpexsitu'],
                $campos['cusupocodi'],
                $campos['tarpinulat']
            );
            $db->autoExecute('sfpc.tbataregistroprecoexterna', $campos, DB_AUTOQUERY_UPDATE, "carpnosequ = " . $campos['carpnosequ']);

            if (isset($_POST['itemAta'])) {
                foreach ($_POST['itemAta'] as $key => $value) {
                    $camposItemAta['carpnosequ'] = $value['itemId'];
                    $camposItemAta['aitarpqtor'] = moeda2float($value['quantidadeOriginalItem']);
                    $camposItemAta['vitarpvori'] = moeda2float($value['valorUnitarioOriginalItem']);
                    $camposItemAta['aitarpqtat'] = moeda2float($value['quantidadeAtualItem']);
                    $camposItemAta['vitarpvatu'] = moeda2float($value['valorUnitarioAtualItem']);
                    $camposItemAta['fitarpsitu'] = $value['situacaoAta'];

                    $db->autoExecute('sfpc.tbitemataregistropreconova', $camposItemAta, DB_AUTOQUERY_UPDATE, "carpnosequ = " . $camposItemAta['carpnosequ']);
                }
            }

        } catch (Exception $e) {
            $db->query("ROLLBACK");
            $_SESSION['mensagemFeedback'] = $e->getMessage();
            return false;
        }
        $db->query("COMMIT");
        $db->query("END TRANSACTION");

        $db->autoCommit(false);
        $db->query("BEGIN TRANSACTION");

        try {
            $campos = (array) $entidade;
            $this->inserirDocumentoAta($db, $campos['carpnosequ']);
        } catch (Exception $e) {
            $db->query("ROLLBACK");
            $_SESSION['mensagemFeedback'] = $e->getMessage();
            return false;
        }
        $db->query("COMMIT");
        $db->query("END TRANSACTION");

        return true;

    }//end salvar()

}

class RegistroPreco_Adaptacao_CadMigracaoAtaExternaAlterar extends Adaptacao_Abstrata
{

    /**
     *
     * {@inheritdoc}
     *
     * @see Adaptacao_Abstrata::getNegocio()
     */
    public function getNegocio()
    {
        $this->setNegocio(new RegistroPreco_Negocio_CadMigracaoAtaExternaAlterar());
        return parent::getNegocio();
    }

    /**
     *
     * @param integer $processo
     * @throws InvalidArgumentException
     */
    public function consultarAtaExterna($processo)
    {
        if (! filter_var($processo, FILTER_VALIDATE_INT)) {
            $_SESSION['mensagemFeedback'] = ExibeMensStr('Processo Externo não foi informado', 1, 1);
            throw new InvalidArgumentException();
        }

        return $this->getNegocio()->consultarAtaExterna($processo);
    }

    /**
     *
     * @param unknown $ata
     */
    public function consultarAtaPorChave($ata)
    {
        return $this->getNegocio()->consultarAtaPorChave($ata);
    }

    public function consultarDocumento($carpnosequ)
    {
        return $this->getNegocio()->consultarDocumento($carpnosequ);
    }

    public function consultarItemAta($carpnosequ)
    {
        return $this->getNegocio()->consultarItemAta($carpnosequ);
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
            ->getEntidade('sfpc.tbataregistroprecoexterna');

        if (isset($_POST['processo'])) {
            $entidade->carpnosequ = (int) filter_var($_POST['processo'], FILTER_SANITIZE_STRING);
            if (empty($entidade->carpnosequ)) {
                $_SESSION['mensagemFeedback'] = 'Código sequencial da ata de registro de preço não foi informado';
                return;
            }
        }

        if (isset($_POST['numeroAtaExterna'])) {
            $entidade->carpexcodn = filter_var($_POST['numeroAtaExterna'], FILTER_SANITIZE_STRING);
            if (empty($entidade->carpexcodn)) {
                $_SESSION['mensagemFeedback'] = 'Código da numeração da ata para o ano não foi informado';
                return;
            }
        }

        if (isset($_POST['anoAtaExterna'])) {
            $entidade->aarpexanon = (int) filter_var($_POST['anoAtaExterna'], FILTER_SANITIZE_NUMBER_INT);
            if (! filter_var($entidade->aarpexanon, FILTER_VALIDATE_INT)) {
                $_SESSION['mensagemFeedback'] = 'Ano da numeração da ata não foi informada';
                return;
            }
        }

        if (isset($_POST['processoLicitatorio'])) {
            $entidade->earpexproc = filter_var($_POST['processoLicitatorio'], FILTER_SANITIZE_STRING);
            if (empty($entidade->earpexproc)) {
                $_SESSION['mensagemFeedback'] = 'Processo Licitatório Externo não foi informado';
                return;
            }
        }

        if (isset($_POST['Modalidade'])) {
            $entidade->cmodlicodi = (int) filter_var($_POST['Modalidade'], FILTER_SANITIZE_NUMBER_INT);
            if (! filter_var($entidade->cmodlicodi, FILTER_VALIDATE_INT)) {
                $_SESSION['mensagemFeedback'] = 'Código da Modalidade não foi informado';
                return;
            }
        }

        if (isset($_POST['orgaoLicitante'])) {
            $entidade->earpexorgg = filter_var($_POST['orgaoLicitante'], FILTER_SANITIZE_STRING);
            if (empty($entidade->earpexorgg)) {
                $_SESSION['mensagemFeedback'] = 'Órgão Gestor não foi informado';
                return;
            }
        }

        if (isset($_POST['objetoProcesso'])) {
            $entidade->earpexobje = filter_var($_POST['objetoProcesso'], FILTER_SANITIZE_STRING);
            if (empty($entidade->earpexobje)) {
                $_SESSION['mensagemFeedback'] = 'Objeto do Processo Licitatório não foi informado';
                return;
            }
        }

        if (isset($_POST['dataInicialProcesso'])) {
            $entidade->tarpexdini = filter_var($_POST['dataInicialProcesso'], FILTER_SANITIZE_STRING);
            if (empty($entidade->tarpexdini)) {
                $_SESSION['mensagemFeedback'] = 'Data e Hora Inicial da ata não foi informado';
                return;
            }
        }

        if (isset($_POST['vigenciaProcesso'])) {
            $entidade->aarpexpzvg = (int) filter_var($_POST['vigenciaProcesso'], FILTER_SANITIZE_NUMBER_INT);
            if (! filter_var($entidade->aarpexpzvg, FILTER_VALIDATE_INT)) {
                $_SESSION['mensagemFeedback'] = 'Prazo de Vigência em Meses não foi informado';
                return;
            }
        }

        return $this->getNegocio()->salvar($entidade, $_POST['itemAta']);
    }

    /**
     *
     * @param unknown $carpnosequ
     * @return NULL[]
     */
    public function consultarFornecedoresAtaExterna($carpnosequ)
    {
        $fornecedores = array();
        $fornecedores['original'] = current(FornecedorService::getFornecedorOriginalAtaExterna($carpnosequ));
        $fornecedores['atual'] = current(FornecedorService::getFornecedorAtualAtaExterna($carpnosequ));

        return $fornecedores;
    }

    public function multiexplode ($delimiters,$string) {

        $ready = str_replace($delimiters, $delimiters[0], $string);
        $launch = explode($delimiters[0], $ready);
        return  $launch;
    }

    public function consultarItensAtaExterna()
    {
        $item = $_SESSION['item'];

        if (!empty($item)) {
            // Percorre os itens que foram adicionados
            for ($i = 0; $i < count($item); $i ++) {
                // Se a posição do item existe, armazena o código do material
                if (isset($item[$i]) === true) {
                    $materialCodigo = $item[$i]['cmatepsequ'];
                }

                // Se o código do material não estiver vazio
                if (!empty($materialCodigo)) {
                    // verificando se item já existe
                    $itemJaExiste = false;

                    // SQL que consulta o material
                    $sql  =  "select";
                    $sql .= " m.ematepdesc,";
                    $sql .= " u.eunidmsigl";
                    $sql .= " from";
                    $sql .= " SFPC.tbmaterialportal m,";
                    $sql .= " SFPC.tbunidadedemedida u";
                    $sql .= " where";
                    $sql .= " m.cmatepsequ = " . $materialCodigo;
                    $sql .= " and u.cunidmcodi = m.cunidmcodi";

                    $database = Conexao();
                    $res = $database->query($sql);
                    if (PEAR::isError($res)) {
                        EmailErroSQL("Erro em SQL", __FILE__, __LINE__, "Erro em SQL", $sql, $res);
                    }

                    $Linha              = $res->fetchRow();

                    $MaterialDescricao  = $Linha[0];
                    $MaterialUnidade    = $Linha[1];

                    $pos        = count($materiais);
                    $materiais  = new stdClass();

                    $materiais->cgrumscodi      = $item[$i]['cgrumscodi'];
                    $materiais->egrumsdesc      = $item[$i]['egrumsdesc'];
                    $materiais->cclamscodi      = $item[$i]['cclamscodi'];
                    $materiais->eclamsdesc      = $item[$i]['eclamsdesc'];
                    $materiais->fgrumstipo      = $item[$i]['fgrumstipo'];
                    $materiais->csubclsequ      = $item[$i]['csubclsequ'];
                    $materiais->esubcldesc      = $item[$i]['esubcldesc'];
                    $materiais->eunidmsigl      = $item[$i]['eunidmsigl'];
                    $materiais->fgrumstipm      = $item[$i]['fgrumstipm'];
                    $materiais->cunidmcodi      = $item[$i]['cunidmcodi'];
                    $materiais->eunidmdesc      = $item[$i]['eunidmdesc'];
                    $materiais->carpnosequ      = $item[$i]['carpnosequ'];
                    $materiais->citarpsequ      = $item[$i]['citarpsequ'];
                    $materiais->aitarporde      = $item[$i]['aitarporde'];
                    if (isset($item[$i]['cmatepsequ']) === true) {
                        $materiais->cmatepsequ      = $item[$i]['cmatepsequ'];
                    }
                    if (isset($item[$i]['ematepdesc']) === true) {
                        $materiais->ematepdesc      = $item[$i]['ematepdesc'];
                    }
                    $materiais->eunidmdesc      = $item[$i]['eunidmdesc'];
                    if (isset($item[$i]['cservpsequ']) === true) {
                        $materiais->cservpsequ = $item[$i]['cservpsequ'];
                    }
                    if (isset($item[$i]['eservpdesc']) === true) {
                        $materiais->eservpdesc = $item[$i]['eservpdesc'];
                    }

                    $materiais->citarpnuml      = $item[$i]['citarpnuml'];
                    $materiais->eitarpmarc      = $item[$i]['eitarpmarc'];
                    $materiais->eitarpmode      = $item[$i]['eitarpmode'];
                    $materiais->eitarpdescmat   = $item[$i]['eitarpdescmat'];
                    $materiais->eitarpdescse    = $item[$i]['eitarpdescse'];
                    $materiais->fitarpsitu      = $item[$i]['fitarpsitu'];
                    $materiais->fitarpincl      = $item[$i]['fitarpincl'];
                    $materiais->fitarpexcl      = $item[$i]['fitarpexcl'];
                    $materiais->titarpincl      = $item[$i]['titarpincl'];
                    $materiais->descricao       = $item[$i]['ematepdesc'];
                    $materiais->tipo            = ($item[$i]['cmatepsequ'] == null) ? 'CADUS' : 'CADUM';
                    $materiais->codigoReduzido  = $item[$i]['cmatepsequ'];
                    $materiais->siglaUnidade    = $item[$i]['eunidmsigl'];
                    $materiais->aitarpqtor      = $item[$i]['aitarpqtor'];
                    $materiais->aitarpqtat      = $item[$i]['aitarpqtat'];
                    $materiais->vitarpvori      = $item[$i]['vitarpvori'];
                    $materiais->vitarpvatu      = $item[$i]['vitarpvatu'];

                    // echo '<pre>';
                    // print_r($_SESSION['item']);
                    // echo '<br>---materiais---<br>';
                    // print_r($materiais);
                    // echo '<br>---session itens---<br>';
                    // print_r($_SESSION['itens']);
                    // die('x__x');

                    $_SESSION['itens'][] = $materiais;

                } else {
                    $itemJaExiste = false;
                    $qtdeServicos = count($_SESSION['itens']);

                    for ($i2 = 0; $i2 < $qtdeServicos; $i2 ++) {
                        if ($materialCodigo == $_SESSION['itens']->codigoReduzidoi) {
                            $itemJaExiste = true;
                        }
                    }

                    // incluindo item
                    if (!$itemJaExiste) {
                        $sql  = " select";
                        $sql .= " m.eservpdesc";
                        $sql .= " from SFPC.TBservicoportal m";
                        $sql .= " where m.cservpsequ = " . $materialCodigo;

                        $database = Conexao();
                        $res = $database->query($sql);
                        if (PEAR::isError($res)) {
                            EmailErroSQL("Erro em SQL", __FILE__, __LINE__, "Erro em SQL", $sql, $res);
                        }

                        $Linha      = $res->fetchRow();
                        $Descricao  = $Linha[0];
                        $servicos   = new stdClass();

                        $servicos->ordem            = $pos + 1;
                        $servicos->descricao        = $Descricao;
                        $servicos->tipo             = 'CADUS';
                        $servicos->codigoReduzido   = $materialCodigo;
                        $servicos->lote             = 1;
                        $servicos->siglaUnidade     = 'UN';
                        $servicos->quantidadeTotal  = converte_valor_estoques(0);
                        $servicos->valorUnitario    = $DadosSessao[4];

                        $_SESSION['itens'][] = $servicos;
                    }
                }
            }

            unset($_SESSION['item']);
        }
    }
}

/**
 */
class RegistroPreco_UI_CadMigracaoAtaExternaAlterar extends UI_Abstrata
{

    private $aarpexanon;

    private $carpnosequ;

    private $ataRegistroPrecoExterna;

    private function transformarItem($item)
    {
        $item->descricao = ($item->cmatepsequ == null) ? $item->eservpdesc : $item->ematepdesc;
        $item->tipo = ($item->cmatepsequ == null) ? 'CADUS' : 'CADUM';
        $item->codigoReduzido = ($item->cmatepsequ == null) ? $item->cservpsequ : $item->cmatepsequ;
        $item->siglaUnidade = ($item->cmatepsequ == null) ? 'UN' : $item->eunidmsigl;
        $item->aitarpqtor = converte_valor_estoques($item->aitarpqtor);
        $item->aitarpqtat = converte_valor_estoques($item->aitarpqtat);
        $item->vitarpvatu = converte_valor_licitacao($item->vitarpvatu);
        $item->vitarpvori = converte_valor_licitacao($item->vitarpvori);
        // $item->participantes = $this->listarOrgaosPorItem($ata, $item->citarpsequ);

        return $item;
    }

    /**
     */
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
        }
    }

    /**
     */
    private function plotarBlocoDocumentos()
    {
        if (empty($_SESSION['Arquivos_Upload'])) {
            $documentos = $this->getAdaptacao()->consultarDocumento($this->ataRegistroPrecoExterna->carpnosequ);

            if (! empty($documentos)) {
                foreach ($documentos as $documento) {
                    $_SESSION['Arquivos_Upload']['nome'][] = $documento->edocatnome;
                    $_SESSION['Arquivos_Upload']['conteudo'][] = $documento->idocatarqu;
                }
            }
        }

        $this->coletarDocumentosAdicionados();
        $this->getTemplate()->block('BLOCO_FILE');
    }

    /**
     *
     * @param unknown $valorAtual
     */
    private function plotarBlocoSituacao($valorAtual)
    {
        $situacoes = array();
        $situacoes['A'] = 'ATIVO';
        $situacoes['I'] = 'INATIVO';
        foreach ($situacoes as $VALUE => $TEXT) {
            $this->getTemplate()->VALOR_SITUACAO = $VALUE;
            $this->getTemplate()->SITUACAO_TEXT = $TEXT;

            // Vendo se a opção atual deve ter o atributo "selected"
            if ($valorAtual == $VALUE) {
                $this->getTemplate()->SITUACAO_SELECTED = 'selected';
            } else {
                // Caso esta não seja a opção atual, limpamos o valor da variável SELECTED
                $this->getTemplate()->clear('SITUACAO_SELECTED');
            }

            $this->getTemplate()->block('BLOCO_SITUACAO');
        }
    }

    /**
     *
     * @param unknown $itens
     */
    private function plotarBlocoItemAta()
    {
        $this->getAdaptacao()->consultarItensAtaExterna();

        if (empty($_SESSION['itens'])) {
            $itens = $this->getAdaptacao()->consultarItemAta($this->ataRegistroPrecoExterna->carpnosequ);
            foreach ($itens as $item) {
                $itemNovo = $this->transformarItem($item, $this->ataRegistroPrecoExterna->carpnosequ);
                $_SESSION['itens'][] = $itemNovo;
            }
        }

        $itens      = $_SESSION['itens'];
        $contador   = 1;

        foreach ($itens as $key => $item) {
            $this->getTemplate()->ITEM_ORD              = $key;
            $this->getTemplate()->VALOR_ITEM_ID         = $item->carpnosequ;
            $this->getTemplate()->VALOR_ORDEM           = $item->aitarporde;
            $this->getTemplate()->VALOR_DESCRICAO_ITEM  = $item->descricao;
            $this->getTemplate()->VALOR_TIPO            = $item->tipo;
            $this->getTemplate()->VALOR_CODIGO_REDUZIDO = $item->codigoReduzido;
            $this->getTemplate()->VALOR_LOTE            = $item->citarpnuml;
            $this->getTemplate()->VALOR_UND             = $item->siglaUnidade;
            $this->getTemplate()->VALOR_QTD_ORIGINAL    = converte_valor_licitacao($item->aitarpqtor);
            $this->getTemplate()->VALOR_ORIGINAL_UNIT   = converte_valor_licitacao($item->vitarpvori);
            $this->getTemplate()->VALOR_TOTAL_ORIGINAL  = converte_valor_licitacao(moeda2float($item->aitarpqtor) * moeda2float($item->vitarpvori));
            $this->getTemplate()->VALOR_QTD_ATUAL       = converte_valor_licitacao($item->aitarpqtat);
            $this->getTemplate()->VALOR_ATUAL_UNIT      = converte_valor_licitacao($item->vitarpvatu);
            $this->getTemplate()->VALOR_TOTAL_ATUAL     = converte_valor_licitacao($item->aitarpqtat * $item->vitarpvatu);

            $this->plotarBlocoSituacao($item->fitarpsitu);
            $this->getTemplate()->block("BLOCO_ITEM");

            $this->getTemplate()->block("BLOCO_RESULTADO_ATAS");
            $contador ++;
        }

        $this->getTemplate()->block("BLOCO_ITEM_TOTAL");
    }

    /**
     *
     * @param unknown $processo
     * @param unknown $ano
     * @param unknown $ata
     */
    private function plotarBlocoBotao($processo, $ano, $ata)
    {
        $this->getTemplate()->VALOR_ANO_SESSAO = $ano;
        $this->getTemplate()->VALOR_ORGAO_SESSAO = 'Externo';
        $this->getTemplate()->VALOR_PROCESSO_SESSAO = $processo;
        $this->getTemplate()->VALOR_ATA_SESSAO = $ata;
        $this->getTemplate()->block("BLOCO_BOTAO");
    }

    /**
     *
     * @param unknown $cmodlicodi
     */
    private function plotarBlocoModalidade($cmodlicodi)
    {
        $repositorio = new Negocio_Repositorio_ModalidadeLicitacao();
        $res = $repositorio->listarTodosAtivos();
        foreach ($res as $modalidade) {
            $this->getTemplate()->VALOR_MODALIDADE = $modalidade->cmodlicodi;
            $this->getTemplate()->DESCICAO_MODALIDADE = $modalidade->emodlidesc;

            $this->getTemplate()->clear("VALOR_MODALIDADE_SELECIONADO");
            if ($cmodlicodi == $modalidade->cmodlicodi) {
                $this->getTemplate()->VALOR_MODALIDADE_SELECIONADO = "selected";
            }
            $this->getTemplate()->block("BLOCO_MODALIDADE");
        }
    }

    /**
     *
     * @param unknown $fornecedorOrigin
     */
    private function plotarBlocoFornecedorOriginal($fornecedorOrigin)
    {
        $numeroCnpjOrCpfOriginal = '';
        $aforcrsequ = '';

        $fornecedorOrigDoc = isset($_POST['fornecedorOrigDoc']) ? filter_var($_POST['fornecedorOrigDoc']) : null;
        $fornecedorOriginalProcesso = isset($_POST['fornecedorOriginalProcesso']) ? filter_var($_POST['fornecedorOriginalProcesso']) : null;

        $numeroCnpjOrCpfOriginal = $fornecedorOrigin->aforcrccgc != null ? $fornecedorOrigin->aforcrccgc : $fornecedorOrigin->aforcrccpf;
        $aforcrsequ = $fornecedorOrigin->aforcrsequ;

        if ($fornecedorOrigDoc && $fornecedorOriginalProcesso) {
            $fornecedorOrigDoc = ($fornecedorOrigDoc == 'CNPJ') ? 1 : 0;
            $fornecedorOrigin = current(FornecedorService::verificarFornecedorCredenciado($fornecedorOrigDoc, $fornecedorOriginalProcesso));

            $aforcrsequ = $fornecedorOrigin->aforcrsequ;
            $numeroCnpjOrCpfOriginal = preg_replace('/[^0-9]/', '', $fornecedorOriginalProcesso);
        }

        $this->getTemplate()->FORNECEDOR_ORIGINAL_ATA_EXTERNA = $numeroCnpjOrCpfOriginal;
        $this->getTemplate()->CODIGO_FORNECEDOR_ORIGINAL = $aforcrsequ;
        $this->getTemplate()->VALOR_DESCRITIVO_FORNECEDOR_ORIGINAL = FormataCpfCnpj($numeroCnpjOrCpfOriginal) . ' - ' . $fornecedorOrigin->nforcrrazs;
        $this->getTemplate()->VALOR_DESCRITIVO_FORNECEDOR_LOGRADOURO = $fornecedorOrigin->eforcrlogr;
    }

    /**
     *
     * @param unknown $fornecedorAtual
     */
    private function plotarBlocoFornecedorAtual($fornecedorAtual)
    {
        $numeroCnpjOrCpfAtual = '';
        $aforcrsequ = '';

        $fornecedorAtualdoc = isset($_POST['fornecedorAtualdoc']) ? filter_var($_POST['fornecedorAtualdoc']) : null;
        $fornecedorAtualProcesso = isset($_POST['fornecedorAtualProcesso']) ? filter_var($_POST['fornecedorAtualProcesso'], FILTER_SANITIZE_NUMBER_INT) : null;

        if ($fornecedorAtual instanceof stdClass) {
            $numeroCnpjOrCpfAtual = $fornecedorAtual->aforcrccgc != null ? $fornecedorAtual->aforcrccgc : $fornecedorAtual->aforcrccpf;
            $aforcrsequ = $fornecedorAtual->aforcrsequ;
        }

        if ($fornecedorAtualdoc && $fornecedorAtualProcesso) {
            $fornecedorAtualdoc = ($fornecedorAtualdoc == 'CNPJ') ? 1 : 0;
            $fornecedorAtual = current(FornecedorService::verificarFornecedorCredenciado($fornecedorAtualdoc, $fornecedorAtualProcesso));

            $aforcrsequ = $fornecedorAtual->aforcrsequ;
            $numeroCnpjOrCpfAtual = preg_replace('/[^0-9]/', '', $fornecedorAtualProcesso);
        }

        $this->getTemplate()->FORNECEDOR_ATUAL_ATA_EXTERNA = $numeroCnpjOrCpfAtual;
        $this->getTemplate()->CODIGO_FORNECEDOR_ATUAL = $aforcrsequ;
        $this->getTemplate()->VALOR_DESCRITIVO_FORNECEDOR_ATUAL = FormataCpfCnpj($numeroCnpjOrCpfAtual) . ' - ' . $fornecedorAtual->nforcrrazs;
        $this->getTemplate()->VALOR_DESCRITIVO_FORNECEDOR_ATUAL_LOGRADOURO = $fornecedorAtual->eforcrlogr;
    }

    /**
     *
     * @param unknown $ata
     */
    private function plotarBlocoLicitacao($ata)
    {
        $dataHota = new DataHora($ata->tarpexdini);

        $this->getTemplate()->VALOR_ATA = isset($_POST['numeroAtaExterna']) ? filter_var($_POST['numeroAtaExterna'], FILTER_SANITIZE_STRING) : $ata->carpexcodn;

        $this->getTemplate()->PROCESSO_LICITATORIO = isset($_POST['processoLicitatorio']) ? filter_var($_POST['processoLicitatorio']) : $ata->earpexproc;
        $this->getTemplate()->VALOR_ANO = isset($_POST['anoAtaExterna']) ? filter_var($_POST['anoAtaExterna']) : $ata->aarpexanon;
        $this->getTemplate()->VALOR_DATA = isset($_POST['dataInicialProcesso']) ? filter_var($_POST['dataInicialProcesso']) : $dataHota->formata('d/m/Y');
        $this->getTemplate()->VALOR_VIGENCIA = isset($_POST['vigenciaProcesso']) ? filter_var($_POST['vigenciaProcesso']) : $ata->aarpexpzvg;

        $this->plotarBlocoModalidade($ata->cmodlicodi);

        $this->getTemplate()->VALOR_ORGAO = isset($_POST['orgaoLicitante']) ? filter_var($_POST['orgaoLicitante']) : $ata->earpexorgg;
        $this->getTemplate()->VALOR_OBJETO = isset($_POST['objetoProcesso']) ? filter_var($_POST['objetoProcesso']) : $ata->earpexobje;

        $this->plotarBlocoDocumentos();

        $fornecedores = $this->getAdaptacao()->consultarFornecedoresAtaExterna($ata->carpnosequ);
        $this->plotarBlocoFornecedorOriginal($fornecedores['original']);
        $this->plotarBlocoFornecedorAtual($fornecedores['atual']);

        $this->getTemplate()->block("BLOCO_LICITACAO");
    }

    public function __construct()
    {
        $template = new TemplatePaginaPadrao("templates/CadMigracaoAtaExternaAlterar.html", "Registro de Preço > Migração > Manter");
        $template->NOMEPROGRAMA = 'CadMigracaoAtaExternaAlterar';
        $template->PROCESSO     = $_GET['processo'];

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
        $this->setAdaptacao(new RegistroPreco_Adaptacao_CadMigracaoAtaExternaAlterar());
        return parent::getAdaptacao();
    }

    /**
     *
     * @throws Exception
     */
    public function retirarDocumento()
    {
        $idDocumento = filter_var($_POST['documentoExcluir'], FILTER_VALIDATE_INT);

        if (! is_int($idDocumento)) {
            throw new Exception("Error Processing Request", 1);
        }

        unset($_SESSION['Arquivos_Upload']['conteudo'][$idDocumento]);
        unset($_SESSION['Arquivos_Upload']['nome'][$idDocumento]);
        $_SESSION['Arquivos_Upload']['nome'] = array_values($_SESSION['Arquivos_Upload']['nome']);
        $_SESSION['Arquivos_Upload']['conteudo'] = array_values($_SESSION['Arquivos_Upload']['conteudo']);
    }

    public function processVoltar()
    {
        $uri = 'CadMigracaoAta.php';
        header('Location: ' . $uri);
        exit();
    }

    public function processCarona()
    {
        $ata = $_REQUEST['ata'];
        $uri = 'CadMigracaoAtaExternaCarona.php?ata=' . $ata;

        header('Location: ' . $uri);
        exit();
    }

    public function adicionarDocumento()
    {
        $arquivo = new Arquivo();
        $arquivo->setExtensoes('doc,odt');
        $arquivo->setTamanhoMaximo(20000000000000000);
        $arquivo->configurarArquivo();
    }

    /**
     * [proccessPrincipal description]
     *
     * @param [type] $variablesGlobals
     *            [description]
     * @return [type] [description]
     */
    public function proccessPrincipal()
    {
        $this->imprimeBlocoMensagem();
        // Se a chamada for GET, então
        // pressume-se que é a primeira vez na tela, então limpa arquivos
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $this->garbageCollection();
        }

        $this->aarpexanon = isset($_REQUEST['ano']) ? filter_var($_REQUEST['ano'], FILTER_SANITIZE_NUMBER_INT) : null;
        $this->carpnosequ = isset($_REQUEST['processo']) ? filter_var($_REQUEST['processo'], FILTER_SANITIZE_NUMBER_INT) : null;

        if (empty($this->aarpexanon)) {
            $_SESSION['mensagemFeedback'] = 'Selecione o ano da ata externa';
            header('Location: CadMigracaoAta.php');
            exit();
            return;
        }

        if (empty($this->carpnosequ)) {
            $_SESSION['mensagemFeedback'] = 'Selecione o processo da ata externa';
            header('Location: CadMigracaoAta.php');
            exit();
            return;
        }

        $this->ataRegistroPrecoExterna = current($this->getAdaptacao()->consultarAtaPorChave($this->carpnosequ));

        $this->plotarBlocoLicitacao($this->ataRegistroPrecoExterna);

        $this->plotarBlocoBotao($this->carpnosequ, $this->aarpexanon, $this->ataRegistroPrecoExterna);

        $this->plotarBlocoItemAta();

        // $this->getTemplate()->ANO = $this->ano;
        // $this->getTemplate()->PROCESSO = $this->processo;
        // // $this->getTemplate()->ORGAO = $this->orgao;
        // $this->getTemplate()->ATA = $ata;
        // $uriAdicionarParticipante = "../registropreco/CadAdicionarParticipante.php?orgao=$orgao&ano=$ano&processo=$processo&ata=$ata";

        // $this->getTemplate()->JANELA_ADICIONAR_PARTICIPANTE = $uriAdicionarParticipante;
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

        header('Location: CadMigracaoAta.php');
        exit();

    }//end salvar()

}

class cadMigracaoAtaAlterar
{

    private function getCodigoUsuarioLogado()
    {
        return (integer) $_SESSION['_cusupocodi_'];
    }

    private function montarTela()
    {
        $ano = $this->variables['post']['ano'];
        $orgao = $this->variables['post']['orgao'];
        $processo = $this->variables['post']['processo'];
        $ata = $this->variables['post']['ata'];

        $this->plotarBlocoBotao($ano, $orgao, $processo, $ata);

        $atas = $this->consultarAtaPorChave($ano, $processo, $orgao, $ata);
        $licitacao = $this->consultarLicitaçãoAtaInterna($ano, $processo, $orgao);

        $this->plotarBlocoLicitacao($licitacao, $atas);
    }

    private function insereDocumento()
    {
        $file = $_REQUEST["fileArquivo"];
        $this->files = $_SESSION['files'];

        if ($this->files == null) {
            $this->files = array();
        }

        array_push($this->files, $file);
        $_SESSION['files'] = $this->files;

        $this->montarTela();
    }

    private function removeDocumento()
    {
        $this->files = $_SESSION['files'];
        array_pop($this->files);

        $_SESSION['files'] = $this->files;
    }

    private function retirarItem()
    {
        $itens = $_POST['idItem'];
        $itens_Sessao = $_SESSION['itens'];

        $provisorio = array();

        $contador_Sessao = count($itens_Sessao);
        for ($i = 0; $i < $contador_Sessao; $i ++) {
            if ($itens_Sessao[$i]->codigoReduzido != $itens) {
                $provisorio[] = $itens_Sessao[$i];
            }
        }

        $_SESSION['itens'] = $provisorio;
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

        $resultado = $db->query($sql);
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

        $itemAtual = $itemNoBanco[0];

        if ($itemAtual->carpnosequ == null) {
            $resultado = $this->inserirItem($db, $ata, $item);
        } else {
            $resultado = $this->atualizarItem($db, $ata, $item);
        }

        if (PEAR::isError($resultado)) {
            throw new RuntimeException($resultado->getMessage());
        }
    }

    /**
     * [insereDocumento description]
     *
     * @return [type] [description]
     */
    private function inserirItem($db, $ata, $item)
    {
        $sequencialMaterial = 'null';
        $sequencialServico  = 'null';
        $quantidadeTotal    = moeda2float($item->quantidadeTotal);
        $valorUnitario      = moeda2float($item->valorUnitario);
        $codigoUsuario      = $this->getCodigoUsuarioLogado();

        if ($item->tipo == 'CADUM') {
            $sequencialMaterial = $item->codigoReduzido;
        } else {
            $sequencialServico = $item->codigoReduzido;
        }

        $sql = "INSERT INTO
    				sfpc.tbitemataregistropreconova
					(carpnosequ, citarpsequ,
    				aitarporde, cmatepsequ,
    				cservpsequ, aitarpqtor,
    				aitarpqtat, vitarpvori,
    				vitarpvatu, citarpnuml,
    				fitarpsitu, fitarpincl,
    				fitarpexcl, titarpincl,
    				cusupocodi, titarpulat)
				VALUES
    				($ata, $item->sequencial,
    				$item->ordem, $sequencialMaterial,
    				$sequencialServico, $quantidadeTotal,
    				$quantidadeTotal, $valorUnitario,
    				$valorUnitario, $item->lote,
    				'A', 'S',
    				'N', now(),
    				$codigoUsuario, now())";

        $resultado = $db->query($sql);
        return $resultado;
    }

    private function atualizarItem($db, $ata, $item)
    {
        $quantidadeTotal = moeda2float($item->quantidadeTotal);
        $codigoUsuario = $this->getCodigoUsuarioLogado();
        $valorUnitario = moeda2float($item->valorUnitario);

        $sql = "UPDATE
    				sfpc.tbitemataregistropreconova
				SET
    				aitarpqtor=$quantidadeTotal, fitarpsitu='$item->situacao',
	    			cusupocodi=$codigoUsuario,
	    			titarpulat=now()
				WHERE
    				carpnosequ=$ata
    				AND citarpsequ=$item->sequencial";

        $resultado = $db->query($sql);
        return $resultado;
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

    private function atualizarItemDoParticipante($db, $ata, $participante, $item)
    {
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

    private function exibirPaginaCarona()
    {
        $uri = "";
    }

    private function consultarLicitacaoAtaInterna($ano, $processo, $orgaoUsuario)
    {
        $db = Conexao();
        $sql = $this->sqlLicitacaoAtaInterna($ano, $processo, $orgaoUsuario);
        $resultado = executarSQL($db, $sql);
        $resultado->fetchInto($licitacao, DB_FETCHMODE_OBJECT);
        return $licitacao;
    }

    private function consultarValorMaximoDocumento($processo, $ano)
    {
        $db = Conexao();
        $sql = $this->sqlCodigoMaximoDocumento($processo, $ano);
        $resultado = executarSQL($db, $sql);
        $resultado->fetchInto($documento, DB_FETCHMODE_OBJECT);
        return $documento;
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

    private function baixarDocumento()
    {
        $arquivo = $_REQUEST['documentoSessao'];

        $arquivoEscolhido = '/tmp/';

        $arquivoEscolhido .= $_SESSION['Arquivos_Upload']['nome'][$arquivo - 1];
        $conteudo = $_SESSION['Arquivos_Upload']['conteudo'][$arquivo - 1];
        file_put_contents($arquivoEscolhido, $conteudo);

        // Envia o arquivo para o cliente
        HelperPitang::baixarArquivo($arquivoEscolhido);
    }
}

$programa = new RegistroPreco_UI_CadMigracaoAtaExternaAlterar();

$botao = isset($_POST['Botao']) ? $_POST['Botao'] : 'Principal';

switch ($botao) {
    case 'Voltar':
        $programa->processVoltar();
        break;
    case 'Salvar':
        $programa->salvar();
        break;
    case 'Carona':
        $programa->processCarona();
        break;
    case 'RetirarItem':
        $programa->retirarItem();
        $programa->proccessPrincipal();
        break;
    case 'InserirDocumento':
        $programa->baixarDocumento();
        break;
    case 'RetirarDocumento':
        $programa->RetirarDocumento();
        $programa->proccessPrincipal();
        break;
    case 'Inserir':
        $programa->adicionarDocumento();
    case 'Principal':
    default:
        $programa->proccessPrincipal();
        break;
}

echo $programa->getTemplate()->show();

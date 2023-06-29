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
 * @author     Pitang Agile TI <contato@pitang.com>
 * @copyright  2014 EMPRESA MUNICIPAL DE INFORMÁTICA - EMPREL
 * @license    http://www.php.net/license/3_01.txt PHP License 3.01
 *
 * @version    GIT: EMPREL-SAD-PORTAL-COMPRAS-REGISTRO-PRECO-BL-FUNC-20160603-1050
 */

#---------------------------------------------------------------------
# Alterado: Pitang Agile TI - Caio Coutinho
# data: 28/06/2018
# Objetivo: Tarefa Redmine 197604
#---------------------------------------------------------------------
# Alterado: Pitang Agile TI - Caio Coutinho
# data: 11/02/2019
# Objetivo: Tarefa Redmine 210654
#---------------------------------------------------------------------

// 220038--

if (! require_once dirname(__FILE__) . '/../bootstrap.php') {
    throw new Exception('Error Processing Request - Bootstrap', 1);
}

/**
 */
class RegistroPreco_Dados_CadRegistroPrecoIntencaoIncluir extends Dados_Abstrata
{

    /**
     * Consulta todos os orgãos gerenciador do sistema.
     *
     * @return @resultados array lista com todos os orgaos gerenciadores do sistema
     */
    public function consultarOrgaosGerenciador()
    {
        return ClaDatabasePostgresql::executarSQL(Dados_Sql_Orgao::sqlOrgaoGerenciador());
    }

    /**
     * Checa se o material informado é genérico.
     *
     * @param int $codigo
     *
     * @return bool
     */
    public function isMaterialGenerico($codigo)
    {
        return ClaDatabasePostgresql::executarSQL(Dados_Sql_MaterialPortal::isMaterialGenerico($codigo));
    }

    /**
     * Insere Intenção de RP.
     *
     * @param stdClass $entidade
     *
     * @return bool
     */
    public function inserirIntencaoRP(stdClass $entidade)
    {
        $database = ClaDatabasePostgresql::getConexao();
        $database->autoCommit(false);
        
        $repoIntencaoRP = new Negocio_Repositorio_IntencaoRegistroPreco($database);
        $intencaoRP = $repoIntencaoRP->inserir($entidade);
        $contaOrgao = count($entidade->listOrgao);
        for ($i = 0; $i < $contaOrgao; ++ $i) {
            $orgao = $entidade->listOrgao[$i];
            $orgao->cintrpsequ = $intencaoRP->cintrpsequ;
            $orgao->cintrpsano = $intencaoRP->cintrpsano;
            $orgao->tinrpoulat = $intencaoRP->tintrpulat;
        }

        $repoIntencaoRPOrgao = new Negocio_Repositorio_IntencaoRPOrgao($database);
        $intencaoRPOrgao = $repoIntencaoRPOrgao->inserir($entidade->listOrgao);
        if (! $intencaoRPOrgao) {
            return false;
        }

        $contaItem = count($entidade->listItens);
        for ($i = 0; $i < $contaItem; ++ $i) {
            $item = $entidade->listItens[$i];
            $item->cintrpsequ = $intencaoRP->cintrpsequ;
            $item->cintrpsano = $intencaoRP->cintrpsano;
            $item->citirpsequ = $i + 1;
            $item->tintrpdcad = $intencaoRP->tintrpulat;
            $item->titirpulat = $intencaoRP->tintrpulat;
            $item->vitirpvues = (float) moeda2float($item->vitirpvues);
        }

        $repositorioItemIRP = new Negocio_Repositorio_ItemIntencaoRegistroPreco($database);
        $itemIntencaoRP = $repositorioItemIRP->inserir($entidade->listItens);
        if (! $itemIntencaoRP) {
            return false;
        }

        // Inserir Documentos aqui
        if (!$this->inserirDocumentoIntencaoRP($database, $intencaoRP)) {
            $database->rollback();
            return false;
        }

        $database->commit();

        return true;
    }

    public function inserirDocumentoIntencaoRP($database, $entidade) {
        $documento = $database->getRow('SELECT MAX(cintrasequ) FROM sfpc.tbintencaoregistroprecoanexo', DB_FETCHMODE_OBJECT);
        $valorMax = (int) $documento->max + 1;
        $tamanho = count($_SESSION['Arquivos_Upload']['nome']);

        $nomeTabela = 'sfpc.tbintencaoregistroprecoanexo';
        $entidadeDoc = ClaDatabasePostgresql::getEntidade($nomeTabela);
        for ($i = 0; $i < $tamanho; $i ++) {
            $entidadeDoc->cintrpsequ = (int) $entidade->cintrpsequ;
            $entidadeDoc->cintrpsano = (int) $entidade->cintrpsano;
            $entidadeDoc->cintrasequ = (int) $valorMax;
            $entidadeDoc->eintranome = $_SESSION['Arquivos_Upload']['nome'][$i];
            $entidadeDoc->iintraarqu = bin2hex($_SESSION['Arquivos_Upload']['conteudo'][$i]);
            $entidadeDoc->cusupocodi = (int) $_SESSION['_cusupocodi_'];
            $entidadeDoc->tintraulat = 'NOW()';

            $result = $database->autoExecute($nomeTabela, (array) $entidadeDoc, DB_AUTOQUERY_INSERT);
            $valorMax ++;

            if(!is_int($result)) {
                return false;
            }
        }

        return true;
    }
}

class RegistroPreco_Negocio_CadRegistroPrecoIntencaoIncluir extends Negocio_Abstrata
{

    const COLLECTION_NAME = 'collectionIntencaoDocumento';

    /**
     * [__construct description].
     */
    public function __construct()
    {
        $this->setDados(new RegistroPreco_Dados_CadRegistroPrecoIntencaoIncluir());
    }

    /**
     *
     * @return array $resultados
     */
    public function consultarOrgaosGerenciador()
    {
        return $this->getDados()->consultarOrgaosGerenciador();
    }

    /**
     * [validarIntencao description].
     *
     * @param stdClass $intencao
     *            [description]
     *
     * @return [type] [description]
     */
    public function validarIntencao(stdClass $intencao)
    {
        if (! is_string($intencao->xintrpobje)) {
            return false;
        }

        if (strlen($intencao->xintrpobje) > Dados_ParametrosGerais::consultarParametrosGerais()->qpargetmaobjeto) {
            return false;
        }

        if (! is_object($intencao->listOrgao)) {
            return false;
        }

        if (! is_object($intencao->listItens)) {
            return false;
        }

        return true;
    }

    /**
     * Se o material é generico.
     *
     * @param int $codigo
     *            Código do Material (CADUM)
     *
     * @return bool true = para sim e false = para não
     */
    public function isMaterialGenerico($codigo)
    {
        $res = $this->getDados()->isMaterialGenerico($codigo);

        return count($res) > 0 ? true : false;
    }

    /**
     * Inserir uma Intenção de Registro de Preço.
     *
     * @param stdClass $entidade
     *            Entidade com os dados para serem armazenados
     *
     * @return mixed false = se a validação deu ruim
     */
    public function inserirIntencaoRP(stdClass $entidade)
    {
        if (! $this->validarIntencao($entidade)) {
            return false;
        }

        return $this->getDados()->inserirIntencaoRP($entidade);
    }
}

class RegistroPreco_Adaptacao_CadRegistroPrecoIntencaoIncluir extends Adaptacao_Abstrata
{

    /**
     *
     * @return stdClass
     */
    private function mapearEntidadeIntencao()
    {
        $entidade = ClaDatabasePostgresql::getEntidade('sfpc.tbintencaoregistropreco');

        $entidade->tintrpdlim = filter_var($_POST['DataLimite'], FILTER_SANITIZE_STRING, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $entidade->xintrpobje = strtoupper(html_entity_decode(filter_var($_POST['Objeto'], FILTER_SANITIZE_STRING, FILTER_SANITIZE_FULL_SPECIAL_CHARS)));
        $entidade->xintrpobse = strtoupper(html_entity_decode(filter_var($_POST['Observacao'], FILTER_SANITIZE_STRING, FILTER_SANITIZE_FULL_SPECIAL_CHARS)));
        $entidade->cusupocodi = (int) $_SESSION['_cusupocodi_'];

        $entidade->listOrgao = new ArrayObject();
        if (count($_POST['Orgaos']) > 0) {
            foreach ($_POST['Orgaos'] as $value) {
                $orgao = ClaDatabasePostgresql::getEntidade('sfpc.tbintencaorporgao');
                $orgao->corglicodi = (int) $value;
                $orgao->cusupocodi = $entidade->cusupocodi;
                $orgao->finrpositu = "A";
                $entidade->listOrgao->offSetSet(null, $orgao);
            }
        }

        $entidade->listItens = new ArrayObject();
        $tamanhoItem = count($_POST['Item']);
        for ($i = 0; $i < $tamanhoItem; ++ $i) {
            $item = ClaDatabasePostgresql::getEntidade('sfpc.tbitemintencaoregistropreco');

            if (strtoupper($_POST['Tipo'][$i]) == 'CADUM') {
                $item->cmatepsequ = (int) filter_var($_POST['CodigoReduzido'][$i], FILTER_SANITIZE_NUMBER_INT);
                $item->eitirpdescmat = strtoupper2(html_entity_decode(filter_var($_POST['DescricaoDetalhada'][$i], FILTER_SANITIZE_STRING, FILTER_SANITIZE_FULL_SPECIAL_CHARS)));
            } else {
                $item->cservpsequ = (int) filter_var($_POST['CodigoReduzido'][$i], FILTER_SANITIZE_NUMBER_INT);
                $item->eitirpdescse = strtoupper2(html_entity_decode(filter_var($_POST['DescricaoDetalhada'][$i], FILTER_SANITIZE_STRING, FILTER_SANITIZE_FULL_SPECIAL_CHARS)));
            }
            $item->aitirporde = (int) filter_var($_POST['Item'][$i], FILTER_SANITIZE_NUMBER_INT);

            $item->vitirpvues = 0;
            if (! empty($_POST['ValorUnitarioEstimado'][$i])) {
                $item->vitirpvues = $_POST['ValorUnitarioEstimado'][$i];
            }
            $item->cusupocodi = $entidade->cusupocodi;
            $entidade->listItens->offSetSet(null, $item);
        }

        return $entidade;
    }

    /**
     */
    public function __construct()
    {
        $this->setNegocio(new RegistroPreco_Negocio_CadRegistroPrecoIntencaoIncluir());
    }

    /**
     * Consultar Orgaos gerenciadores.
     *
     * @return array $resultados
     */
    public function consultarOrgaosGerenciador()
    {
        return $this->getNegocio()->consultarOrgaosGerenciador();
    }

    /**
     *
     * @param
     *            [type]
     *
     * @return bool
     */
    public function isMaterialGenerico($codigo)
    {
        if (! filter_var($codigo, FILTER_VALIDATE_INT)) {
            throw new InvalidArgumentException('Error Processing Request', 1);
        }

        return $this->getNegocio()->isMaterialGenerico($codigo);
    }

    /**
     *
     * @return [type]
     */
    public function inserirIntencaoRP()
    {
        return $this->getNegocio()->inserirIntencaoRP($this->mapearEntidadeIntencao());
    }
}

class RegistroPreco_UI_CadRegistroPrecoIntencaoIncluir extends UI_Abstrata
{

    /**
     */
    const COLLECTION_NAME = 'colecaoItemIntencao';

    const CAMINHO_TEMPLATE = 'templates/CadRegistroPrecoIntencaoIncluir.html';

    /**
     * Recuperar dados informados pelo o usuário no formulário.
     */
    private function recuperarDadosInformados()
    {
        if (isset($_POST['DataLimite'])) {
            $this->getTemplate()->VALOR_DATA_LIMITE = filter_var($_POST['DataLimite']);
        }

        if (isset($_POST['Objeto'])) {
            $this->getTemplate()->VALOR_OBJETO = strtoupper2(filter_var($_POST['Objeto'], FILTER_SANITIZE_STRING, FILTER_SANITIZE_FULL_SPECIAL_CHARS));
            $this->getTemplate()->VALOR_NCARACATERES0 = strlen($this->getTemplate()->VALOR_OBJETO);
        }

        if (isset($_POST['Observacao'])) {
            $this->getTemplate()->VALOR_OBSERVACAO = strtoupper2(filter_var($_POST['Observacao'], FILTER_SANITIZE_STRING, FILTER_SANITIZE_FULL_SPECIAL_CHARS));
            $this->getTemplate()->VALOR_NCARACATERES1 = strlen($this->getTemplate()->VALOR_OBSERVACAO);
        }

        if (is_array($_POST['Orgaos'])) {
            $this->carregarOrgaos($_POST['Orgaos']);
        } else {
            $this->carregarOrgaos();
        }

        $this->getTemplate()->block("BLOCO_FILE");
        $this->coletarDocumentosAdicionados();

        $this->recuperarListaItemIntencao();
    }

    /**
     * Valida data limite.
     *
     * @param string $dataLimite
     *            Data Limite para responder a intenção de Registro de Preço
     *
     * @return bool
     */
    private function validarDataLimite($dataLimite)
    {
        if (! isset($dataLimite) || empty($dataLimite)) {
            $_SESSION['colecaoMensagemErro'][] = "
                <a href='javascript:document.getElementById(\"DataLimite\").focus();' class='titulo2'>
                    Data Limite não informado
                </a>";

            return false;
        }

        return true;
    }

    public function validarObjeto($objeto)
    {
        $retorno = true;

        if (! isset($objeto) || empty($objeto)) {
            $_SESSION['colecaoMensagemErro'][] = "
                <a href='javascript:document.getElementById(\"Objeto\").focus();' class='titulo2'>
                    Objeto não informado
                </a>";
            $retorno = false;
        }

        if (strlen($objeto) > Dados_ParametrosGerais::consultarParametrosGerais()->qpargetmaobjeto) {
            $_SESSION['colecaoMensagemErro'][] = "
                <a href='javascript:document.getElementById(\"Objeto\").focus();' class='titulo2'>
                    Objeto informado é maior que 500 caracteres.
                </a>";
            $retorno = false;
        }

        return $retorno;
    }

    private function validarOrgaosLicitanteParticipantes($orgaos)
    {
        $retorno = true;
        if (count($orgaos) < 1 || empty($orgaos) === true) {
            $_SESSION['colecaoMensagemErro'][] = "
                <a href='javascript:document.getElementById(\"Orgaos\").focus();' class='titulo2'>
                    Órgãos não informados
                </a>";

            $retorno = false;
        }

        return $retorno;
    }

    /**
     * [validaFormulario description].
     *
     * @return [type] [description]
     */
    private function validaFormulario()
    {
        $dataLimite = filter_var($_POST['DataLimite'], FILTER_SANITIZE_STRING);
        $objeto = filter_var($_POST['Objeto'], FILTER_SANITIZE_STRING);
        $orgaos = $_POST['Orgaos'];
        $retorno = array();
        $retorno['dataLimite'] = $this->validarDataLimite($dataLimite);
        $retorno['objeto'] = $this->validarObjeto($objeto);
        $retorno['orgaosLicitantes'] = $this->validarOrgaosLicitanteParticipantes($orgaos);

        $countItem = count($_SESSION[self::COLLECTION_NAME]);
        if ($countItem <= 0) {
            $_SESSION['colecaoMensagemErro'][] = 'Nenhum item foi adicionado para a Intenção de Registro de Preço';

            $retorno['item'] = false;
        }
        if ($countItem > 0) {
            for ($i = 0; $i < $countItem; ++ $i) {
                $itemOrd = $i + 1;
                $descricao = $_POST['DescricaoDetalhada'][$i];
                if (! isset($descricao) || empty($descricao)) {
                    $_SESSION['colecaoMensagemErro'][] = "Descrição Detalhada do item $itemOrd não informado";
                    $retorno['item'][$itemOrd]['descricao'] = false;
                }

                $ValorUnitarioEstimado = filter_var($_POST['ValorUnitarioEstimado'][$i], FILTER_SANITIZE_NUMBER_FLOAT);
                if ($ValorUnitarioEstimado > 1e+14) {
                    $_SESSION['colecaoMensagemErro'][] = "Valor Unitario Estimado do item $itemOrd deve ser menor que 17 digitos";
                    $retorno['item'][$itemOrd]['valorUnitario'] = false;
                }
            }
        }

        return $retorno;
    }

    /**
     * [carregarOrgaos description].
     *
     * @param array $atual
     *            [description]
     *
     * @return [type] [description]
     */
    private function carregarOrgaos($atual = array())
    {
        $colecao = $this->getAdaptacao()->consultarOrgaosGerenciador();
        $row = null;

        foreach ($colecao as $row) {
            $this->getTemplate()->VALOR_ITEM_ORGAO = $row->corglicodi;
            $this->getTemplate()->ITEM_ORGAO = $row->eorglidesc;
            // Checando se a opção atual deve ter o atributo "selected"
            if (in_array($row->corglicodi, $atual)) {
                $this->getTemplate()->ITEM_ORGAO_SELECIONADO = 'selected';
            } else {
                // Caso esta não seja a opção atual, limpamos o valor da variável ITEM_ORGAO_SELECIONADO
                $this->getTemplate()->clear('ITEM_ORGAO_SELECIONADO');
            }
            $this->getTemplate()->block('BLOCO_ITEM_ORGAO');
        }
    }

    private function limparDadosItemSessao()
    {
        if (isset($_SESSION[self::COLLECTION_NAME])) {
            Servico_Item::clean(self::COLLECTION_NAME);
        }
    }

    private function recuperarListaItemIntencao()
    {
        global $SimboloConcatenacaoArray, $SimboloConcatenacaoDesc;
        Servico_Item::collectorSessionItem(self::COLLECTION_NAME);
        $countItem = count($_SESSION[self::COLLECTION_NAME]);
        if ($countItem <= 0) {
            return;
        }
        for ($i = 0; $i < $countItem; ++ $i) {
            $this->getTemplate()->VALOR_UNITARIO_ESTIMADO = '0,0000';
            $this->getTemplate()->VALOR_ESTIMADO_TRP = '---';
            $dados = explode($SimboloConcatenacaoArray, $_SESSION[self::COLLECTION_NAME][$i]);
            $this->getTemplate()->VALOR_ITEM = $i + 1;
            $this->getTemplate()->VALOR_MATERIAL = $dados[1];
            $descricao = explode($SimboloConcatenacaoDesc, $dados[0]);
            $this->getTemplate()->VALOR_DESCRICAO = strtoupper2($descricao[0]);
            $this->getTemplate()->VALOR_CODIGO_REDUZIDO = $dados[1];
            if ('S' == $dados[3]) {
                $this->getTemplate()->VALOR_TIPO = 'CADUS';
                $this->getTemplate()->VALOR_DESCRICAO_DETALHADA = '';
                if (! empty($_POST['DescricaoDetalhada'][$i])) {
                    $this->getTemplate()->VALOR_DESCRICAO_DETALHADA = strtoupper2(filter_var($_POST['DescricaoDetalhada'][$i], FILTER_SANITIZE_STRING, FILTER_SANITIZE_FULL_SPECIAL_CHARS));
                }
                $this->getTemplate()->block('BLOCO_TEXTAREA_DESCRICAO_DETALHADA');
            } else {
                $this->getTemplate()->VALOR_TIPO = 'CADUM';
                $valorTRP = calcularValorTrp(Conexao(), 2, (int) $this->getTemplate()->VALOR_CODIGO_REDUZIDO);
                $this->getTemplate()->VALOR_ESTIMADO_TRP = empty($valorTRP) ? '0,0000' : converte_valor_estoques($valorTRP);

                if (! $this->getAdaptacao()->isMaterialGenerico((int) $this->getTemplate()->VALOR_CODIGO_REDUZIDO)) {
                    $this->getTemplate()->VALOR_DESCRICAO_DETALHADA = '---';
                    $this->getTemplate()->block('BLOCO_SEM_DESCRICAO_DETALHADA');
                } else {
                    $this->getTemplate()->VALOR_DESCRICAO_DETALHADA = strtoupper2(filter_var($_POST['DescricaoDetalhada'][$i], FILTER_SANITIZE_STRING, FILTER_SANITIZE_FULL_SPECIAL_CHARS));
                    $this->getTemplate()->block('BLOCO_TEXTAREA_DESCRICAO_DETALHADA');
                }
            }
            if (isset($_POST['ValorUnitarioEstimado'][$i])) {
                $this->getTemplate()->VALOR_UNITARIO_ESTIMADO = $_POST['ValorUnitarioEstimado'][$i];
            }
            $this->getTemplate()->block('BLOCO_LISTAGEM_ITEM');
        }
        $this->getTemplate()->block('BLOCO_HEADER_LISTAGEM_ITEM');
    }

    public function __construct()
    {
        $this->setAdaptacao(new RegistroPreco_Adaptacao_CadRegistroPrecoIntencaoIncluir());

        $this->setTemplate(new TemplatePaginaPadrao(self::CAMINHO_TEMPLATE, 'Registro de Preço > Intenção > Incluir'));

        $this->getTemplate()->VALOR_OBJETO = '';
        $this->getTemplate()->VALOR_DATA_LIMITE = date('d/m/Y');
        $this->getTemplate()->VALOR_OBSERVACAO = '';
        $this->getTemplate()->VALOR_NCARACATERES0 = 0;
        $this->getTemplate()->VALOR_NCARACATERES1 = 0;
        $this->getTemplate()->VALOR_TAMANHO_MAX_OBJETO = Dados_ParametrosGerais::consultarParametrosGerais()->qpargetmaobjeto;
    }

    public function plotarTelaInicial()
    {
        $this->carregarOrgaos();
        $this->getTemplate()->block("BLOCO_FILE");
        $this->limparDadosItemSessao();
    }

    public function incluirIntencaoRP()
    {
        $arrValidacao = $this->validaFormulario();

        if (InArrayRecursive::run(false, $arrValidacao, true)) {
            $this->mensagemSistema(implode(', ', $_SESSION['colecaoMensagemErro']), 1, 1);
            $this->recuperarDadosInformados();

            return;
        }

        if (! $this->getAdaptacao()->inserirIntencaoRP()) {
            $this->mensagemSistema('Intenção não adicionada!', 1, 1);
            $this->recuperarDadosInformados();

            return;
        }

        Servico_Item::clean(self::COLLECTION_NAME);
        $this->mensagemSistema('Intenção Incluída com Sucesso', 1, 0);
        unset($_SESSION['Arquivos_Upload']);
        $this->plotarTelaInicial();
    }

    public function incluirDocumentoRP()
    {
        $arquivoInformado = $_FILES['fileArquivo'];

        if ($arquivoInformado['size'] == 0) {
            $this->mensagemSistema("É preciso Informar um Arquivo", 1, 0);
        }

        $arquivo = new Arquivo();
        $arquivo->setExtensoes('pdf');
        $arquivo->setTamanhoMaximo(2000000000000);

        $arquivo->configurarArquivo();
        $this->recuperarDadosInformados();
    }

    public function removerDocumentoRP()
    {
        $idDocumento = filter_input(INPUT_POST, 'documentoExcluir', FILTER_VALIDATE_INT);
        if (! is_int($idDocumento)) {
            throw new Exception("Error Processing Request", 1);
        }

        unset($_SESSION['Arquivos_Upload']['conteudo'][$idDocumento]);
        unset($_SESSION['Arquivos_Upload']['nome'][$idDocumento]);
        $_SESSION['Arquivos_Upload']['nome'] = array_values($_SESSION['Arquivos_Upload']['nome']);
        $_SESSION['Arquivos_Upload']['conteudo'] = array_values($_SESSION['Arquivos_Upload']['conteudo']);
        $this->recuperarDadosInformados();
    }

    public function coletarDocumentosAdicionados()
    {
        if (!empty($_SESSION['Arquivos_Upload']['nome'])) {
            $qtdeDocumentos = sizeof($_SESSION['Arquivos_Upload']['nome']);
            for ($i = 0; $i < $qtdeDocumentos; $i ++) {
                $this->getTemplate()->ID_DOCUMENTO = $i;
                $this->getTemplate()->NOME_DOCUMENTO = $_SESSION['Arquivos_Upload']['nome'][$i];
                $this->getTemplate()->block('BLOCO_DOCUMENTO');
            }
        }
    }

    public function retirarItem()
    {
        if (isset($_POST['CheckItem'])) {
            $item = $_POST['CheckItem'];
            Servico_Item::removeItemLista($item, self::COLLECTION_NAME);
            
            foreach (array_keys($_POST['CheckItem']) as $key) {
                unset($_POST['Item'][$key], $_POST['Material'][$key], $_POST['Descricao'][$key], $_POST['DescricaoDetalhada'][$key], $_POST['Tipo'][$key], $_POST['CodigoReduzido'][$key], $_POST['ValorEstimadoTRP'][$key], $_POST['ValorUnitarioEstimado'][$key]);
            }
        }

        $this->recuperarDadosInformados();
    }

    public function incluirItem()
    {
        $this->recuperarDadosInformados();
    }
}

Seguranca();

AddMenuAcesso('/estoques/CadIncluirItem.php');
AddMenuAcesso('/estoques/CadItemDetalhe.php');

$gui = new RegistroPreco_UI_CadRegistroPrecoIntencaoIncluir();

// Quando o programa for chamado
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $_GET = filter_var_array($_GET, FILTER_SANITIZE_SPECIAL_CHARS);
    $gui->plotarTelaInicial();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $_POST = filter_var_array($_POST, FILTER_SANITIZE_SPECIAL_CHARS);
    switch ($_POST['Botao']) {
        case 'RetirarItem':
            $gui->retirarItem();
            break;
        case 'Incluir':
            $gui->incluirIntencaoRP();
            break;
        case 'InserirDocumento':
            $gui->incluirDocumentoRP();
            break;
        case 'Remover':
            $gui->removerDocumentoRP();
            break;
        default:
            $gui->incluirItem();
            break;
    }
}

echo $gui->getTemplate()->show();

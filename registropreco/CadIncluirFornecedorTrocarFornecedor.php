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
 */

 // 220038--
 
if (!@require_once dirname(__FILE__)."/../bootstrap.php") {
    throw new Exception("Error Processing Request - Bootstrap", 1);
}


class CadIncluirFornecedor
{
    /**
     * [$template description]
     * @var \TemplatePaginaPadrao
     */
    private $template;
    /**
     * [$variables description]
     * @var \ArrayObject
     */
    private $variables;
     /**
     * Gets the value of template.
     *
     * @return mixed
     */
    private function getTemplate()
    {
        return $this->template;
    }
    /**
     * Sets the value of template.
     *
     * @param TemplatePaginaPadrao $template the template
     *
     * @return self
     */
    private function setTemplate(TemplatePortal $template)
    {
        $this->template = $template;

        return $this;
    }

    private function sqlSelectFornecedor($cnpj)
    {
        $sql  = " SELECT aforcrccgc, nforcrrazs";
        $sql .= " FROM sfpc.tbfornecedorcredenciado";
        $sql .= " WHERE aforcrccgc = '" . $cnpj . "'";

        return $sql;
    }

    /**
     * [proccessPrincipal description]
     * @param  [type] $variablesGlobals [description]
     * @return [type] [description]
     */
    private function proccessPrincipal()
    {
    }

    private function proccessPesquisar()
    {
        // Número cnpj
            $cnpj = preg_replace('/[^A-Za-z0-9]/', '', $this->variables['post']['cnpj']);

        $database = & Conexao();

        if ($cnpj == "") {
            $sql = "SELECT * FROM sfpc.tbfornecedorcredenciado";
        } else {
            $sql = $this->sqlSelectFornecedor($cnpj);
        }

        $resultSet = executarSQL($database, $sql);

        while ($resultSet->fetchInto($row, DB_FETCHMODE_OBJECT)) {
            $this->getTemplate()->VALOR_CNPJ = $row->aforcrccgc;
            $this->getTemplate()->VALOR_RAZAO_SOCIAL = $row->nforcrrazs;

            $this->getTemplate()->block("BLOCO_LISTAGEM_ITEM");
        }

        if ($resultSet->numRows() > 0) {
            $this->getTemplate()->block("BLOCO_HEADER_LISTAGEM_ITEM");
            $this->getTemplate()->block("BLOCO_RESULTADO_PEQUISA");
        } else {
            $this->getTemplate()->MENSAGEM_ERRO = ExibeMensStr('Não existem dados para os filtros informados', 1, 1);
            $this->getTemplate()->block('BLOCO_ERRO', true);
        }

            // Último filtro realizado
            /*$this->variables['session']['ultimoFiltro'] = array(
    															'NumeroIntencao' => $this->variables['post']['NumeroIntencao'],
    															'DataInicioCadastro' => $this->variables['post']['DataInicioCadastro'],
    															'DataFimCadastro' => $this->variables['post']['DataFimCadastro']
    															);*/
    }
    /**
     * Configuration Initial
     *
     */
    private function configInitial()
    {
        $dataMes = DataMes();

        $this->getTemplate()->VALOR_NUMERO_INTENCAO = '';
        $this->getTemplate()->VALOR_DATA_INICIO_CADASTRO = $dataMes[0];
        $this->getTemplate()->VALOR_DATA_FIM_CADASTRO = $dataMes[1];

        // Caso o usuário clicou em voltar na tela de manter recarrega a pesquisa
        if (isset($this->variables['session']['voltarPesquisa'])) {
            $ultimoFiltro = $this->variables['session']['ultimoFiltro'];

            $this->variables['post']['NumeroIntencao'] = $ultimoFiltro['NumeroIntencao'];
            $this->variables['post']['DataInicioCadastro'] = $ultimoFiltro['DataInicioCadastro'];
            $this->variables['post']['DataFimCadastro'] = $ultimoFiltro['DataFimCadastro'];

            unset($this->variables['session']['voltarPesquisa']);

            $this->proccessPesquisar();
        }
    }

    /**
     * [frontController description]
     * @return [type] [description]
     */
    private function frontController()
    {
        $botao = isset($this->variables['post']['Botao'])
            ? $this->variables['post']['Botao']
            : 'Principal';
        
        switch ($botao) {
            case 'Pesquisar':
                $this->proccessPesquisar();
                $this->proccessPrincipal();
                break;
            case 'Principal':
            default:
                $this->proccessPrincipal();
                break;
        }
    }
    /**
     * [__construct description]
     * @param TemplatePaginaPadrao $template [description]
     * @param ArrayObject          $session  [description]
     */
    public function __construct(
        TemplatePortal $template,
        ArrayObject $variablesGlobals
    ) {
        /**
         * Settings
         */
        $this->setTemplate($template);
        $this->variables = $variablesGlobals;
        /**
         * Front Controller for action
         */
        $this->frontController();
    }
    /**
     * Running the application
     */
    public function run()
    {
        /**
         * Rendering the application
         */
        return $this->getTemplate()->show();
    }
}

/**
 * Bootstrap application
 */
function bootstrap()
{
    global $SimboloConcatenacaoArray, $SimboloConcatenacaoDesc;

    
    // Adiciona páginas no MenuAcesso #
    AddMenuAcesso('/estoques/CadIncluirItem.php');
    AddMenuAcesso('/estoques/CadItemDetalhe.php');

    $template = new TemplatePortal("templates/CadIncluirFornecedorTrocarFornecedor.html");

    $arrayGlobals = new ArrayObject();
    $arrayGlobals['session'] = $_SESSION;
    $arrayGlobals['server'] = $_SERVER;
    $arrayGlobals['separatorArray'] = $SimboloConcatenacaoArray;
    $arrayGlobals['separatorDesc'] = $SimboloConcatenacaoDesc;

    if ($arrayGlobals['server']['REQUEST_METHOD'] == "POST") {
        $arrayGlobals['post'] = $_POST;
    }

    if ($arrayGlobals['server']['REQUEST_METHOD'] == 'GET') {
        $arrayGlobals['get'] = $_GET;
    }

    $app = new CadIncluirFornecedor($template, $arrayGlobals);
    echo $app->run();

    unset($app, $template, $arrayGlobals);
}

bootstrap();

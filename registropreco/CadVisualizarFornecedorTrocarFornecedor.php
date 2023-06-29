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

class CadVisualizarFornecedorTrocaFornecedor
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
     * [$intencao description]
     * @var unknown
     */
    private $numero;
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

    private function sqlSelectFornecedor($numero)
    {
        $sql  = "select f.aforcrccgc,f.nforcrrazs,f.aforcrsequ from sfpc.tbfornecedorcredenciado f";
        $sql .= " where f.aforcrccgc = '$numero'";

        return $sql;
    }

    /**
     * [proccessPrincipal description]
     * @param  [type] $variablesGlobals [description]
     * @return [type] [description]
     */
    private function proccessPrincipal()
    {
        $this->numero =  $this->variables['get']['numero'];
        $this->configInitial();
    }

    private function listarFornecedor()
    {
        $sql = $this->sqlSelectFornecedor($this->numero);

        $database = & Conexao();
        $resultado = executarSQL($database, $sql);

        $resultado->fetchInto($fornecedor, DB_FETCHMODE_OBJECT);

        return $fornecedor;
    }

    /**
     * Configuration Initial
     */
    private function configInitial()
    {
        if (is_null($this->numero)) {
            return;
        }

        $fornecedor = $this->listarFornecedor();

        $this->getTemplate()->VALOR_CODIGO = $fornecedor->aforcrsequ;
        $this->getTemplate()->VALOR_RAZAO_SOCIAL = $fornecedor->nforcrrazs;
        $this->getTemplate()->VALOR_CGC_CPF = $fornecedor->aforcrccgc;

        $this->getTemplate()->block("BLOCO_FORMULARIO_MANTER");
    }

    private function adicionarFornecedorSessao()
    {
        $numeroFornecedor = $this->variables['post']['NumeroFornecedor'];

        $_SESSION["codigoFornecedor"] = $this->variables['post']['NumeroFornecedor'];
        echo "<script>window.opener.document.CadAtaRegistroPrecoInternaTrocarFornecedorAtasAlterar.fornecedorSelecionado.value=$numeroFornecedor</script>";
        echo "<script>window.opener.document.CadAtaRegistroPrecoInternaTrocarFornecedorAtasAlterar.submit()</script>";
        echo "<script>self.close()</script>";
    }

    private function processVoltar()
    {
        // Flag que indica o botão voltar
        $this->variables['session']['voltarPesquisa'] = true;

        header('location: CadIncluirFornecedorTrocarFornecedor.php');
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
            case 'Voltar':
                $this->processVoltar();
                break;
            case 'selecionarFornecdor':
                $this->adicionarFornecedorSessao();
                break;
            case 'Principal':
            default:
                $this->proccessPrincipal();
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

    $template = new TemplatePortal("templates/CadVisualizarFornecedorTrocarFornecedor.html");

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

    $app = new CadVisualizarFornecedorTrocaFornecedor($template, $arrayGlobals);
    echo $app->run();

    unset($app, $template, $arrayGlobals);
}

bootstrap();

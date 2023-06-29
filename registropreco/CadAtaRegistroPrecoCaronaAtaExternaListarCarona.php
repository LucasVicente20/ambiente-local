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


class CadAtaRegistroPrecoCaronaAtaExternaListarCarona
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
    private function setTemplate(\TemplatePortal $template)
    {
        $this->template = $template;

        return $this;
    }

   /**
     * [proccessPrincipal description]
     * @param  [type] $variablesGlobals [description]
     * @return [type] [description]
     */
    private function proccessPrincipal()
    {
        $ata =      $this->variables['get']['ata'];

        $numeroAta = $this->recuperarSeguencialAta($ata);

        //$ata =      $this->variables['get']['processo'];

        $processo= substr($ata, 0, 2);

        $itens = $this->consultarItemAta($numeroAta);
        $this->plotarBlocoItemAta($itens);

        $this->getTemplate()->ATA = $ata;
        $this->getTemplate()->block("BLOCO_FORMULARIO_MANTER");
    }

    private function recuperarSeguencialAta($ata)
    {
        $numeroAta = $ata;

        $numeroAtaExp = explode(".", $numeroAta);
        $numeroAtaCodi = explode("/", $numeroAtaExp[1]);
        $codigo = (int)$numeroAtaCodi;

        return $codigo;
    }

    private function proccessVoltar()
    {
        $orgao = $_SESSION["orgao"];
        $ano = $_SESSION["ano"];
        $processo = $_SESSION["processo"];

        $uri  = 'CadAtaRegistroPrecoCaronaAtaExternaManterListar.php?ano='.$ano.'&processo='.$processo.'&orgao='.$orgao;
        header('location: ' . $uri);
    }

    private function plotarBlocoItemAta($itens)
    {
        if ($itens == null) {
            foreach ($itens as $item) {
                $this->getTemplate()->VALOR_ORDEM                 = $item->aitarporde;
                $this->getTemplate()->VALOR_TIPO                  = $item->cmatepsequ == null ?$item->cservpsequ :$item->cmatepsequ;
                $this->getTemplate()->VALOR_DESCRICAO             = $item->cmatepsequ == null ? $item->eitarpdescse:$item->eitarpdescmat;
                $this->getTemplate()->VALOR_UND                   = "UN";
                $this->getTemplate()->VALOR_QTD_ORIGINAL          = $item->aitarpqtor;
                $this->getTemplate()->VALOR_ORIGINAL              = $item->vitarpvori;
                $this->getTemplate()->VALOR_TOTAL_ORIGINAL        = $item->vitarpvori*$item->aitarpqtor;
                $this->getTemplate()->VALOR_LOTE                  = $item->citarpnuml;
                $this->getTemplate()->VALOR_QTD_ATUAL             = $item->aitarpqtat;
                $this->getTemplate()->VALOR_VALOR_ATUAL           = $item->vitarpvatu;
                $this->getTemplate()->VALOR_TOTAL_ATUAL           = $item->vitarpvatu*$item->aitarpqtat;

                $this->getTemplate()->block("BLOCO_RESULTADO_ATAS");
            }
        }
    }

    private function consultarItemAta($numeroAta)
    {
        $resultados = array();
        $db     = Conexao();
        $sql = $this->sqlItemAtaNova($numeroAta);
        $resultado = executarSQL($db, $sql);
        while ($resultado->fetchInto($item, DB_FETCHMODE_OBJECT)) {
            array_push($resultados, $item);
        }
        return $resultados;
    }

    private function sqlItemAtaNova($sequencialAta)
    {
        $sql ="select i.aitarporde,";
        $sql.="i.eitarpdescmat,i.eitarpdescse,i.cmatepsequ,";
        $sql.="i.cmatepsequ, i.citarpnuml,u.eunidmdesc,";
        $sql.="i.aitarpqtor, i.vitarpvatu";
        $sql.=" from sfpc.tbitemataregistropreconova i";
        $sql.=" left outer join sfpc.tbmaterialPortal m";
        $sql.=" on m.cmatepsequ = i.cmatepsequ";
        $sql.=" left outer join sfpc.tbunidadedeMedida u";
        $sql.=" on m.cunidmcodi = u.cunidmcodi";
        $sql.=" and m.cmatepsequ = i.cmatepsequ";
        $sql.=" where i.carpnosequ=".$sequencialAta;
        ;

        return $sql;
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
            case 'salvar':
                $this->proccessSalvar();
                break;
            case 'voltar':
                $this->proccessVoltar();
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

    if (isset($_SESSION['LOCAL_SISTEMA'])
        && filter_var($_SESSION['LOCAL_SISTEMA']) == 'DESENVOLVIMENTO_'
    ) {
        ini_set('display_errors', 1);
        error_reporting(E_ALL ^ E_NOTICE);
        date_default_timezone_set('America/Recife');
    }

    // Executa o controle de segurança
    session_start();

    /**
     * Initialize application
     */
    Seguranca();
    // Adiciona páginas no MenuAcesso #

    $template = new TemplatePaginaPadrao("templates/CadAtaRegistroPrecoCaronaAtaExternaListarCarona.html", "");

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

    $app = new CadAtaRegistroPrecoCaronaAtaExternaListarCarona($template, $arrayGlobals);
    echo $app->run();

    unset($app, $template, $arrayGlobals);
}

bootstrap();

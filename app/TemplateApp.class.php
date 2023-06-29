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
 * @version   Git: 20150224_111500-69-g6a82bc5
 *
 * HISTORICO DE ALTERAÇÔES
 * -----------------------------------------------------------------------
 *  Alterado: Pitang Agile TI
 *  Data:     13/03/2015
 *  Objetivo: Bug CR 109036 - Legislação Consultar - link com problema
 *  Versão:   v1.29.3-2-g6a5621a
 * -----------------------------------------------------------------------
 * 	Alterado: Lucas Baracho
 * 	Data:	  24/10/2018
 *  Objetivo: Tarefa Redmine 73662
 * -----------------------------------------------------------------------
 */

if (!@require_once dirname(__FILE__).'/../funcoes.php') {
    throw new Exception('Error Processing Request - funcoes', 1);
}

if (!@require_once dirname(__FILE__).'/../import/Template.class.php') {
    throw new Exception('Error Processing Request - Template.class', 1);
}

/*
 *
 * @version 1.27.1-1-g59aaca1
 */
if (!@require_once dirname(__FILE__).'/../vendor/autoload.php') {
    throw new Exception('Error Processing Request - autoload.php', 1);
}

session_start();
Seguranca();

/**
 * HelperPitang.
 */
class HelperPitang
{
    /**
     * Um construtor privado; previne a criação direta do objeto.
     */
    private function __construct()
    {
    }

    /**
     * Previne que o usuário clone a instância.
     */
    public function __clone()
    {
        trigger_error('Clone is not allowed.', E_USER_ERROR);
    }

    /**
     * Debug application.
     *
     * @param mixed  $data
     * @param string $text
     *                     [optional]
     */
    public static function debug($data, $text = null)
    {
        echo "<script>\r\n//<![CDATA[\r\nif (!console) {var console={log:function () {}}}";
        $output = explode("\n", var_export($data, true));
        foreach ($output as $line) {
            if (trim($line)) {
                $line = addslashes($line);
                if (isset($text)) {
                    $line = $text.' : '.$line;
                }
                echo "console.log(\"{$line}\");";
            }
        }
        echo "\r\n//]]>\r\n</script>";
    }

    /**
     * [setErrors description].
     */
    public static function setErrors()
    {
        ini_set('display_errors', 0);
        error_reporting(E_ALL ^ E_NOTICE);
    }

    /**
     * Get URL base.
     *
     * @return string the url base of application
     */
    public static function getUrlBase()
    {
        $phpSelf = filter_var($_SERVER['PHP_SELF'], FILTER_SANITIZE_STRING);
        $pattern = substr($phpSelf, 0, strrpos($phpSelf, '/') + 1);
        $phpHttpHost = filter_var($_SERVER['HTTP_HOST'], FILTER_SANITIZE_STRING);
        return "http://$phpHttpHost$pattern";
    }
}

/**
 * creates an html element, like in js.
 *
 * @see http://davidwalsh.name/create-html-elements-php-htmlelement-class
 *
 * @uses test case - simple link
 *       $my_anchor = new html_element('a');
 *       $my_anchor->set('href','http://davidwalsh.name');
 *       $my_anchor->set('title','David Walsh Blog');
 *       $my_anchor->set('text','Click here!');
 *       $my_anchor->output();
 *       //<a href="http://davidwalsh.name" title="David Walsh Blog">Click here!</a>
 *
 *       test case - br tag
 *       echo '<pre>';
 *       $my_anchor = new html_element('br');
 *       $my_anchor->output();
 *       //<br />
 *
 *       test case - sending an array to set
 *       echo '<pre>';
 *       $my_anchor = new html_element('a');
 *       $my_anchor->set('href','http://davidwalsh.name');
 *       $my_anchor->set(array('href'=>'http://cnn.com','text'=>'CNN'));
 *       $my_anchor->output();
 *       //<a href="http://cnn.com">CNN</a>
 *
 *       test case - injecting another element
 *       echo '<pre>';
 *       $my_image = new html_element('img');
 *       $my_image->set('src','cnn-logo.jpg');
 *       $my_image->set('border','0');
 *       $my_anchor = new html_element('a');
 *       $my_anchor->set(array('href'=>'http://cnn.com','title'=>'CNN'));
 *       $my_anchor->inject($my_image);
 *       $my_anchor->output();
 *       //<a href="http://cnn.com" title="CNN"><img src="cnn-logo.jpg" border="0" /></a>
 */
class Element
{
    /* vars */
    public $type;

    public $attributes;

    public $selfClosers;

    /**
     * Construct.
     *
     * @param string $type
     *                            Name of element HTML
     * @param array  $selfClosers
     *                            Default
     */
    public function __construct($type, $selfClosers = array('input', 'img', 'hr', 'br', 'meta', 'link'))
    {
        $this->type = strtolower($type);
        $this->selfClosers = $selfClosers;
    }

    /**
     * Get Attribute.
     *
     * @param string $attribute
     *
     * @return string
     */
    public function get($attribute)
    {
        return $this->attributes[$attribute];
    }

    /* set -- array or key,value */
    public function set($attribute, $value = '')
    {
        if (!is_array($attribute)) {
            $this->attributes[$attribute] = $value;
        } else {
            $this->attributes = array_merge($this->attributes, $attribute);
        }
    }

    /* remove an attribute */
    public function remove($att)
    {
        if (isset($this->attributes[$att])) {
            unset($this->attributes[$att]);
        }
    }

    /* clear */
    public function clear()
    {
        $this->attributes = array();
    }

    /* inject */
    public function inject($object)
    {
        if (@get_class($object) == __class__) {
            $this->attributes['text'] .= $object->build();
        }
    }

    /* build */
    public function build()
    {
        // start
        $build = '<'.$this->type;

        // add attributes
        if ((is_array($this->attributes)?count($this->attributes):false)) {
            foreach ($this->attributes as $key => $value) {
                if ($key != 'text') {
                    $build .= ' '.$key.'="'.$value.'"';
                }
            }
        }

        // closing
        if (!in_array($this->type, $this->selfClosers)) {
            $build .= '>'.$this->attributes['text'].'</'.$this->type.'>';
        } else {
            $build .= ' />';
        }

        // return it
        return $build;
    }

    /* spit it out */
    public function output()
    {
        echo $this->build();
    }
}

/**
 * Class Menu.
 */
class MenuFactory
{
    /**
     * [trataMenu description].
     *
     * @param [type] $key
     *                    [description]
     *
     * @return [type] [description]
     */
    private static function trataMenu($key)
    {
        return str_replace(';', '', str_replace(')', '', str_replace('new Array(', '', trim($key[1]))));
    }

    /**
     * [getEnv description].
     *
     * @return [type] [description]
     */
    public static function getEnv()
    {
        if (isset($GLOBALS['PASTA_SISTEMA'])) {
            return '';
        }

        $scriptUrl = $_SERVER['PHP_SELF'];
        $scriptUrlPartes = explode('/', $scriptUrl);
        
        if (is_array($scriptUrlPartes) && isset($scriptUrlPartes[1]) && $scriptUrlPartes[1] == 'sfpc') {
            return $scriptUrlPartes[1];
        }

        return '';
    }

    /**
     * Tratamento da String que é gerada para o carregamento do antigo menu do sistema, a ideia principal
     * é de criar uma estrutura melhor para trabalhar, já o objetivo inicial foi de criar uma lista de array
     * para que o javascript montasse o menu.
     *
     * @return array
     */
    public static function getMenuList()
    {
        $novoLayout = getPathNovoLayout();

        // clone do usuario session
        $_SESSION['backup'] = array(
            '_cgrempcodi2_' => $_SESSION['_cgrempcodi_'],
            '_cusupocodi2_' => $_SESSION['_cusupocodi_'],
            '_cperficodi2_' => $_SESSION['_cperficodi_'],
            '_eusupologi2_' => $_SESSION['_eusupologi_'],
            '_eacepocami2_' => $_SESSION['_eacepocami_'],
        );
        // reset usuario session para INTERNET
        $_SESSION['_cgrempcodi_'] = 0;
        $_SESSION['_cusupocodi_'] = 0;
        $_SESSION['_cperficodi_'] = 0;
        $_SESSION['_eusupologi_'] = 'INTERNET';
        $_SESSION['_eacepocami_'] = array();
        $_SESSION['_MENU_'] = null;
        // GERAR Menu
        $menu = MenuAcessoStr();

        $menuList = array();
        $menu = explode('Menu', $menu);
        unset($menu[0], $menu[1]);
        

        $identifyFilho = '';
        foreach ($menu as $row) {
            $key = explode('=', $row);
            
            $partes = explode(',', self::trataMenu($key));
            $keyString = (string) trim($key[0]);

            if ($partes[0] == '"Cartilhas') {
                $partes[0] = $partes[0]. ',' . $partes[1];
                $partes[1] = '';
            }
            
            $url = str_replace('"', '', $partes[1]);
            

            $urlBad = end(explode('/', $url));

            $urlHomeNoTratada = '/'.self::getEnv()."$novoLayout/".$urlBad;

            $urlHome = str_replace('//', '/', $urlHomeNoTratada);
            $row = array(
                $keyString,
                str_replace('"', '', $partes[0]),
                $url,
            );

            $pai = self::getPai($row);

            if ($pai == $keyString) {
                $menuList[$pai] = array(
                    'descricao' => str_replace('"', '', $partes[0]),
                    'url' => $url,
                );
            } else {
                $contLevel = substr_count($row[0], '_');

                if ($contLevel == 1) {
                    $menuList[$pai]['filho'][$keyString] = array(
                        'descricao' => str_replace('"', '', $partes[0]),
                        'url' => $urlHome,
                    );
                    $identifyFilho = $keyString;
                } else {
                    $menuList[$pai]['filho'][$identifyFilho]['filho'][$keyString] = array(
                        'descricao' => str_replace('"', '', $partes[0]),
                        'url' => $urlHome,
                    );
                }
            }
        }
        // Recupera o usuario antigo da session
        $_SESSION['_cgrempcodi_'] = $_SESSION['backup']['_cgrempcodi2_'];
        $_SESSION['_cusupocodi_'] = $_SESSION['backup']['_cusupocodi2_'];
        $_SESSION['_cperficodi_'] = $_SESSION['backup']['_cperficodi2_'];
        $_SESSION['_eusupologi_'] = $_SESSION['backup']['_eusupologi2_'];
        $_SESSION['_eacepocami_'] = array();
        $_SESSION['_MENU_'] = null;
        // gerar menu
        MenuAcessoStr();

        foreach ($_SESSION['_eacepocami_'] as $key => $value) {
            $file = explode('/', trim($value));
            $_SESSION['_eacepocami_'][$key] = '/'.$file[1]."/$novoLayout/".$file[3];
        }

        self::adicionarAcessoMenuNovoLayout();

        return $menuList;
    }

    /**
     * Adiciona acesso aos arquivos públicos presentes no diretório app.
     */
    private static function adicionarAcessoMenuNovoLayout()
    {
        $novoLayout = getPathNovoLayout();
        $pathNovoLayout = "../$novoLayout";

        if (is_dir($pathNovoLayout)) {
            $arrayArquivosNaBlackList = self::getArquivosNaBlackList();
            $arrayArquivos = scandir($pathNovoLayout);

            foreach ($arrayArquivos as $arquivo) {
                $extensaoArquivo = substr($arquivo, -3);

                if ('php' == $extensaoArquivo && !in_array($arquivo, $arrayArquivosNaBlackList)) {
                    AddMenuAcesso("/$novoLayout/$arquivo");
                }
            }
        }

        AddMenuAcesso('/fornecedores/CadIncluirCertidaoComplementar.php');
        AddMenuAcesso('/fornecedores/CadIncluirGrupos.php');
        AddMenuAcesso('/fornecedores/CadIncluirAutorizacao.php');
        AddMenuAcesso('/fornecedores/RotVerificaEmail.php');
        AddMenuAcesso('/fornecedores/RelReciboInscritoPdf.php');
        AddMenuAcesso('/oracle/fornecedores/RotDebitoCredorConsulta.php');
        AddMenuAcesso('/oracle/fornecedores/RotConsultaInscricaoMercantil.php');

        AddMenuAcesso('/licitacoes/ConsAcompDownloadAtas.php');
        AddMenuAcesso('/licitacoes/ConsAcompDownloadDoc.php');
        AddMenuAcesso('/fornecedores/RelConsInscritoPdf.php');
    }

    /**
     * Array com os nomes dos arquivos que não devem ser
     * habilitados para acesso no array de menu da sessão.
     *
     * @return array
     */
    private static function getArquivosNaBlackList()
    {
        return array(
            'padrao.template.php',
            'TemplateAppPopup.php',
            'TemplateAppPadrao.php',
            'TemplateApp.class.php',
        );
    }

    /**
     * @param array $row
     *
     * @throws \BadMethodCallException
     *
     * @return int
     */
    public static function getPai($row)
    {
        $pai = explode('_', $row[0]);

        if (!is_array($pai) || !isset($pai[0])) {
            throw new BadMethodCallException('Error Processing Request', 1);
        }

        return $pai[0];
    }
}

/**
 * TemplateApp implementation of Template.
 */
class TemplateApp extends Template
{
    public function __construct($file)
    {
        parent::__construct($file);
    }

    public function exibirMensagemFeedback($mensagem, $tipo)
    {
        unset($_SESSION['Mensagem']);
        unset($_SESSION['Mens']);

        $this->MENSAGEM = $mensagem;
        $this->TIPOALERT = ($tipo == 1) ? 'alert-info' : 'alert-error';
        $this->block('BLOCO_MENSAGEM');
    }
}

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
 
if (! @require_once dirname(__FILE__) . "/../bootstrap.php") {
    throw new Exception("Error Processing Request - Bootstrap", 1);
}

class CadAtaRegistroPrecoCaronaAtaExternaManter
{
    /**
     * [$template description]
     *
     * @var \TemplatePaginaPadrao
     */
    private $template;

    /**
     * [$variables description]
     *
     * @var \ArrayObject
     */
    private $variables;

    /**
     * [$intencao description]
     *
     * @var unknown
     */
    private $intencao;

    /**
     * Gets the value of template.
     *
     * @return mixed
     */
    private function proccessPrincipal()
    {
        $anos = $this->carregarAno();
        $this->plotarBlocoAno($anos);
        
        $orgaos = $this->consultarOrgaoComNumeracaoGerada();
        $this->plotarBlocoOrgao($orgaos);
        
        $processos = $this->consultarProcessoDoOrgaoNoAno($orgaos[0]->corglicodi, $anos[0]);
        $this->plotarBlocoProcesso($processos);
    }

    private function consultarOrgaoComNumeracaoGerada()
    {
        $resultados = array();
        
        $db = Conexao();
        $sql = $this->sqlOrgaoAtaGerada();
        $resultado = executarSQL($db, $sql);
        while ($resultado->fetchInto($orgaos, DB_FETCHMODE_OBJECT)) {
            array_push($resultados, $orgaos);
        }
        
        return $resultados;
    }

    private function consultarProcessoDoOrgaoNoAno($orgao, $ano)
    {
        $resultados = array();
        
        $db = Conexao();
        $sql = $this->sqlProcessoRegistroPrecoEmHomologacao($orgao, $ano);
        $resultado = executarSQL($db, $sql);
        while ($resultado->fetchInto($processos, DB_FETCHMODE_OBJECT)) {
            array_push($resultados, $processos);
        }
        return $resultados;
    }

    private function carregarAno()
    {
        $anoAtual = (int) date('Y');
        $anos = array();
        for ($i = 0; $i < 3; $i ++) {
            array_push($anos, strval($anoAtual - $i));
        }
        return $anos;
    }

    private function processSelecionar()
    {
        $orgao = $this->variables['post']['orgaoGestor'];
        $ano = $this->variables['post']['valorAno'];
        $processo = $this->variables['post']['GerarNumeracaoProcesso'];
        
        $anos = $this->carregarAno();
        
        $uri = 'CadAtaRegistroPrecoCaronaAtaExternaManterListar.php?ano=' . $anos[$ano] . '&processo=' . $processo . '&orgao=' . $orgao;
        header('location: ' . $uri);
    }

    private function atualizaProcessos()
    {
        $orgao = $this->variables['post']['orgaoGestor'];
        $ano = $this->variables['post']['valorAno'];
        $anos = $this->carregarAno();
        
        if ($orgao != null && $ano != null) {
            $processos = $this->consultarProcessoDoOrgaoNoAno($orgao, $anos[$ano]);
            $this->plotarBlocoProcesso($processos);
        }
        
        $anos = $this->carregarAno();
        $this->plotarBlocoAno($anos);
        
        $orgaos = $this->consultarOrgaoComNumeracaoGerada();
        $this->plotarBlocoOrgao($orgaos);
    }

    private function plotarBlocoAno(array $anos)
    {
        $gerarNumeracao = $this->variables['post']['valorAno'];
        foreach ($anos as $value => $text) {
            $this->getTemplate()->ANO_VALUE = $value;
            $this->getTemplate()->ANO_TEXT = $text;
            
            // Vendo se a opção atual deve ter o atributo "selected"

            if ($gerarNumeracao != null) {
                if ($value == $gerarNumeracao) {
                    $this->getTemplate()->ANO_SELECTED = "selected";
                }
            }
            
            if ($gerarNumeracao == $value) {
                $this->getTemplate()->ANO_SELECTED = "selected";
            }

            // Caso esta não seja a opção atual, limpamos o valor da variável SELECTED
            else {
                $this->getTemplate()->clear("ANO_SELECTED");
            }
            
            $this->getTemplate()->block("BLOCO_ANO");
        }
    }

    private function plotarBlocoOrgao($orgaos)
    {
        $orgaoNumeracao = $this->variables['post']['orgaoGestor'];
        if ($orgaos == null) {
            return;
        }
        foreach ($orgaos as $orgao) {
            $posicao = 0;
            $this->getTemplate()->ORGAO_VALUE = $orgao->corglicodi;
            
            $this->getTemplate()->ORGAO_TEXT = $orgao->eorglidesc;
            
            $this->getTemplate()->ORGAO_TEXT = $this->getTemplate()->ORGAO_TEXT;
            
            // Vendo se a opção atual deve ter o atributo "selected"
            if ($orgaoNumeracao == $value) {
                $this->getTemplate()->ORGAO_SELECTED = "selected";
            }

            // Caso esta não seja a opção atual, limpamos o valor da variável SELECTED
            else {
                $this->getTemplate()->clear("ORGAO_SELECTED");
            }
            
            $this->getTemplate()->block("BLOCO_ORGAO");
        }
    }

    private function plotarBlocoProcesso($processos)
    {
        $processoNumeracao = $this->variables['post']['GerarNumeracaoProcess'];
        
        if ($processos == null) {
            return;
        }
        foreach ($processos as $processo) {
            $posicao = 0;
            $this->getTemplate()->{PROCESSO_VALUE} = $processo->clicpoproc;
            
            $this->getTemplate()->TEXTO_PROCESSO = str_pad($processos->clicpoproc, 4, '0', STR_PAD_LEFT) . '/' . $processo->alicpoanop;
            
            $this->getTemplate()->TEXTO_PROCESSO = $this->getTemplate()->TEXTO_PROCESSO;
            
            // Vendo se a opção atual deve ter o atributo "selected"
            if ($processoNumeracao == $value) {
                $this->getTemplate()->PROCESSO_SELECTED = "selected";
            }

            // Caso esta não seja a opção atual, limpamos o valor da variável SELECTED
            else {
                $this->getTemplate()->clear("PROCESSO_SELECTED");
            }
            
            $this->getTemplate()->block("BLOCK_PROCESSO");
        }
    }

    private function sqlProcessoRegistroPrecoEmHomologacao($orgao, $ano)
    {
        $LICITACAO_EM_HOMOLOGACAO = 13;
        
        $sql = "SELECT DISTINCT A.CLICPOPROC , A.ALICPOANOP , A.CCOMLICODI , A.CGREMPCODI , A.CORGLICODI , B.ECOMLIDESC,";
        $sql .= "e.cfasescodi FROM SFPC.TBLICITACAOPORTAL A";
        $sql .= " INNER JOIN SFPC.TBCOMISSAOLICITACAO B ON A.CCOMLICODI = B.CCOMLICODI";
        $sql .= " INNER JOIN SFPC.TBUSUARIOCOMIS D ON A.CGREMPCODI = D.CGREMPCODI AND D.CCOMLICODI = A.CCOMLICODI";
        $sql .= " INNER JOIN SFPC.tbfaselicitacao E ON A.CLICPOPROC = e.clicpoproc";
        $sql .= " WHERE  A.CORGLICODI =" . $orgao;
        $sql .= " AND A.ALICPOANOP =" . $ano . " AND a.flicporegp LIKE 'S'";
        $sql .= " AND e.cfasescodi = " . $LICITACAO_EM_HOMOLOGACAO;
        $sql .= " ORDER BY B.ECOMLIDESC ASC , A.ALICPOANOP DESC , A.CLICPOPROC DESC";
        return $sql;
    }

    private function sqlOrgaoAtaGerada()
    {
        $sql = "select o.corglicodi, o.eorglidesc from  sfpc.tborgaolicitante o";
        $sql .= " inner join sfpc.tbataregistroprecointerna a";
        $sql .= " on o.corglicodi = a.corglicodi";
        
        return $sql;
    }

    private function getTemplate()
    {
        return $this->template;
    }

    /**
     * Sets the value of template.
     *
     * @param TemplatePaginaPadrao $template
     *            the template
     *
     * @return self
     */
    private function setTemplate(TemplatePaginaPadrao $template)
    {
        $this->template = $template;
        
        return $this;
    }

    /**
     * [frontController description]
     *
     * @return [type] [description]
     */
    private function frontController()
    {
        $botao = isset($this->variables['post']['Botao']) ? $this->variables['post']['Botao'] : 'Principal';
        
        switch ($botao) {
            case 'Voltar':
                $this->processVoltar();
                break;
            case 'Selecionar':
                $this->processSelecionar();
                break;
            case 'Principal':
            default:
                $this->proccessPrincipal();
        }
    }

    /**
     * [__construct description]
     *
     * @param TemplatePaginaPadrao $template
     *            [description]
     * @param ArrayObject $session
     *            [description]
     */
    public function __construct(TemplatePaginaPadrao $template, ArrayObject $variablesGlobals)
    {
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
    
    if (isset($_SESSION['LOCAL_SISTEMA']) && filter_var($_SESSION['LOCAL_SISTEMA']) == 'DESENVOLVIMENTO_') {
        ini_set('display_errors', 1);
        error_reporting(E_ALL ^ E_NOTICE ^ E_DEPRECATED);
        date_default_timezone_set('America/Recife');
    }
    
    // Executa o controle de segurança
    session_start();
    
    /**
     * Initialize application
     */
    Seguranca();
    
    /**
     */
    $template = new TemplatePaginaPadrao("templates/CadAtaRegistroPrecoCaronaAtaExternaManter.html", "Registro de Preço > Ata Interna > Manter");
    
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
    
    $app = new CadAtaRegistroPrecoCaronaAtaExternaManter($template, $arrayGlobals);
    echo $app->run();
    
    unset($app, $template, $arrayGlobals);
}

bootstrap();

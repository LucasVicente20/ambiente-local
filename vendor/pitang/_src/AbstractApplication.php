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
 * Abstract Application.
 */
abstract class AbstractApplication
{
    /**
     * Front Controller.
     *
     * Implements flow application
     */
    abstract protected function frontController();

    /**
     * object of TemplatePaginaPadrao.
     *
     * @var \TemplatePaginaPadrao
     */
    protected $template;

    /**
     * object of array.
     *
     * @var array
     */
    protected $variables;

    /**
     * Gets the value of template.
     *
     * @return mixed
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * Sets the value of template.
     *
     * @param Template $template
     *            the template
     *            
     * @return self
     */
    public function setTemplate(Template $template)
    {
        $this->template = $template;
        
        return $this;
    }

    /**
     *
     * @param array $variables            
     *
     * @return \Pitang\AbstractApplication
     */
    public function setVariables(array $variables)
    {
        $this->variables = $variables;
        
        return $this;
    }

    /**
     *
     * @return array
     */
    public function getVariables()
    {
        return $this->variables;
    }

    /**
     * Running the application.
     */
    protected function run()
    {
        if (! $this->getTemplate() instanceof Template) {
            throw new LogicException('Instance of Template not found!');
        }
        /*
         * Rendering the application
         */
        
        return $this->getTemplate()->show();
    }

    /**
     * Filter Sanitize input data POST.
     *
     * @return array [description]
     */
    protected static function filterSanitizePOST()
    {
        return array();
    }

    /**
     * Filter Sanitize input data GET.
     *
     * @return array [description]
     */
    protected static function filterSanitizeGET()
    {
        return array();
    }

    /**
     * Bootstrap application.
     */
    protected static function setup()
    {
        global $SimboloConcatenacaoArray, $SimboloConcatenacaoDesc;
        
        /*
         * Lista de variaveis globais que serão utilizada na implementação da classe
         *
         * @var array
         */
        $arrayGlobals = array();
        
        $arrayGlobals['session'] = &$_SESSION;
        $arrayGlobals['server'] = &$_SERVER;
        
        if ($arrayGlobals['server']['REQUEST_METHOD'] == 'POST') {
            $arrayGlobals['post'] = &$_POST;
        }
        
        if ($arrayGlobals['server']['REQUEST_METHOD'] == 'GET') {
            $arrayGlobals['get'] = &$_GET;
        }
        
        $arrayGlobals['separatorArray'] = $SimboloConcatenacaoArray;
        $arrayGlobals['separatorDesc'] = $SimboloConcatenacaoDesc;
        
        return $arrayGlobals;
    }

    /**
     * Get POST variable.
     *
     * @param string $field            
     *
     * @return mixed
     */
    protected function getPost($field)
    {
        if (isset($this->variables['post'][$field])) {
            return $this->variables['post'][$field];
        }
        
        return;
    }

    /**
     * Get grupo codigo do usuario logado no sistema.
     */
    protected function getGrupoCodigoSession()
    {
        return (integer) $_SESSION['_cgrempcodi_'];
    }

    /**
     * Set value template.
     *
     * <<<<<<< HEAD
     *
     * @param string $field            
     * @param mixed $value
     *            =======
     * @param string $field            
     * @param mixed $value
     *            >>>>>>> 4b54f730836544af886a07be0f9cbb8a1012384d
     *            
     * @throws InvalidArgumentException
     */
    protected function setValueTemplate($field, $value)
    {
        if (empty($field)) {
            throw new InvalidArgumentException('Error proccessing $field');
        }
        
        $field = strtoupper($field);
        
        if (! $this->getTemplate() instanceof Template) {
            throw new LogicException('Instance of \Template not found!');
        }
        
        $this->getTemplate()->$field = $value;
        
        return $this;
    }

    /**
     * Get codigo usuario in session.
     *
     * @return number
     */
    protected function getUsuarioCodigo()
    {
        return (integer) $this->variables['session']['_cusupocodi_'];
    }

    /**
     * Get Orgao Licitante Codigo pelo Grupo Codigo do usuário logado.
     *
     * @param int $grupoCodigo            
     *
     * @return int
     */
    protected function getOrgaoLicitanteCodigo($grupoCodigo, $perfilCorporativo = false)
    {
        $sql = '
          SELECT x.corglicodi FROM sfpc.tbgrupoorgao x WHERE x.cgrempcodi = ?
        ';
        $database = ClaDatabase::getConexao();
        $res = $database->getOne($sql, $grupoCodigo);
        
        ClaDatabase::hasError($res);
        
        return (integer) $res;
    }
}

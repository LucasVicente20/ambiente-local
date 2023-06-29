<?php
// 220038--
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
 * @category  Pitang
 * @author    Pitang Agile TI <contato@pitang.com>
 * @copyright 2014 EMPRESA MUNICIPAL DE INFORMÁTICA - EMPREL
 * @license   http://www.php.net/license/3_01.txt PHP License 3.01
 * @version   GIT: 1.27.0-2-g133f78b
 */
class LoggerPortalCompras
{

    /**
     *
     * @var string
     */
    private $mensagem;

    /**
     *
     * @param string $caminho_arquivo            
     */
    public function __construct($caminho_arquivo)
    {
        $arquivoExiste = file_exists($caminho_arquivo);
        $this->mensagem = '';
        
        if (! $arquivoExiste) {
            $sessionValues = array();
            foreach ($_SESSION as $key => $value) {
                if ($key == '_MENU_') {
                    continue;
                }
                
                $sessionValues[$key] = $value;
            }
            
            $serverValues = array();
            $keysServerNotAllowed = array(
                'PHP_AUTH_USER',
                'PHP_AUTH_PW',
                'HTTP_COOKIE'
            );
            foreach ($_SERVER as $key => $value) {
                if (in_array($key, $keysServerNotAllowed)) {
                    continue;
                }
                
                $serverValues[$key] = $value;
            }
            
            $arrayGlobals = array();
            $arrayGlobals['session'] = $sessionValues;
            $arrayGlobals['server'] = $serverValues;
            
            if ($arrayGlobals['server']['REQUEST_METHOD'] == "POST") {
                $arrayGlobals['post'] = $_POST;
            }
            
            if ($arrayGlobals['server']['REQUEST_METHOD'] == 'GET') {
                $arrayGlobals['get'] = $_GET;
            }
            
            $this->mensagem = var_export($arrayGlobals, true);
        }
    }

    /**
     *
     * @return string
     */
    public function getMensagem()
    {
        return $this->mensagem;
    }
}

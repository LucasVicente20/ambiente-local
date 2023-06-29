<?php

/**
 * Portal da DGCO
 *
 * PHP version 5
 *
 * LICENSE: This source file is subject to version 3.01 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_01.txt. If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @package Novo Layout
 * @author Pitang Agile TI <contato@pitang.com>
 * @copyright 2014 EMPRESA MUNICIPAL DE INFORMÁTICA - EMPREL
 * @license http://www.php.net/license/3_01.txt PHP License 3.01
 * @version GIT: 1.27.1-1-g59aaca1
 */
class Pitang_GetUrl
{

    /**
     * Adiciona a URL para Sessão GetUrl
     *
     * @param string $url            
     * @param boolean $redireciona            
     */
    public static function run($url, $redireciona = false)
    {
        if (! in_array($url, $_SESSION['GetUrl'])) {
            $_SESSION['GetUrl'][] = $url;
        }
        
        if ($redireciona) {
            header("Location: " . $url);
            exit();
        }
    }
}
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
 * @category  Pitang Registro Preco
 *
 * @author    Pitang Agile TI <contato@pitang.com>
 * @copyright 2014 EMPRESA MUNICIPAL DE INFORMÁTICA - EMPREL
 * @license   http://www.php.net/license/3_01.txt PHP License 3.01
 *
 * @version   GIT: v1.21.0-18-gc622221
 *
 * -----------------------------------------------------------------------------
 * HISTORICO DE ALTERAÇÕES NO PROGRAMA
 * -----------------------------------------------------------------------------
 * Alterado:  Pitang Agile IT
 * Data:      21/07/2015
 * Objetivo:  CR76836 - Licitações Concluídas
 * -----------------------------------------------------------------------------
 */

 // 220038--
 
require_once dirname(__FILE__).'/../funcoes.php';

//spl_autoload_register('autoloadRegistroPreco');
spl_autoload_register('autoloadLib');

/**
 * autoload.
 *
 * @author Joe Sexton <joe.sexton@bigideas.com>
 *
 * @param string $class
 * @param string $dir
 *
 * @return bool
 */
function autoloadLib($class, $dir = null)
{
    if (is_null($dir)) {
        $dir = dirname(__FILE__).'/pitang/src/';
    }

    foreach (scandir($dir) as $file) {
        // directory?
        if (is_dir($dir.$file) && substr($file, 0, 1) !== '.') {
            autoloadLib($class, $dir.$file.'/');
        }

        // php file?
        if (substr($file, 0, 2) !== '._' && preg_match('/.php$/i', $file)) {
            // filename matches class?
            if (str_replace('.php', '', $file) == $class || str_replace('.class.php', '', $file) == $class) {
                require $dir.$file;
            }
        }
    }
}

// function autoloadRegistroPreco($class, $dir = null)
// {
//     autoloadLib($class, dirname(__FILE__).'/../registropreco/vendor/pitang/src/');
// }

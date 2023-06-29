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
 * @category  Pitang_Registro_Preco
 * @package   registropreco
 * @author    Pitang Agile TI <contato@pitang.com>
 * @copyright 2014 EMPRESA MUNICIPAL DE INFORMÁTICA - EMPREL
 * @license   http://www.php.net/license/3_01.txt PHP License 3.01
 * @version   GIT: v1.18.0-17-g9920068
 */

/**
 */
class Pitang_Debug
{

    /**
     * Um construtor privado; previne a criação direta do objeto
     */
    private function __construct()
    {}

    /**
     * Previne que o usuário clone a instância
     */
    public function __clone()
    {
        trigger_error('Clone is not allowed.', E_USER_ERROR);
    }

    /**
     * Debug application
     *
     * @param mixed $data
     * @param string $text
     *            [optional]
     */
    public static function debug($data, $text = null)
    {
        echo "<script>\r\n//<![CDATA[\r\nif (!console) {var console={log:function () {}}}";
        $output = explode("\n", var_export($data, true));
        foreach ($output as $line) {
            if (trim($line)) {
                $line = addslashes($line);
                if (isset($text)) {
                    $line = $text . " : " . $line;
                }
                echo "console.log(\"{$line}\");";
            }
        }
        echo "\r\n//]]>\r\n</script>";
    }

    /**
     * [setErrors description]
     */
    public static function setErrors()
    {
        ini_set('display_errors', 0);
        error_reporting(E_ALL ^ E_NOTICE);
    }

    /**
     *
     * @param mixed $value
     */
    public static function varDump($value)
    {
        echo '<pre>';
        var_dump($value);
        die();
    }
}

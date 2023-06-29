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
 * @category  Pitang Registro Preço
 * @package   registropreco
 * @author    Pitang Agile TI <contato@pitang.com>
 * @copyright 2014 EMPRESA MUNICIPAL DE INFORMÁTICA - EMPREL
 * @license   http://www.php.net/license/3_01.txt PHP License 3.01
 * 
 * @version GIT: EMPREL-SAD-PORTAL-COMPRAS-BL-FUNC-20160426-1545-5-g272ac8d
 */
#-------------------------------------------------------------------------
# Alterado: Caio Coutinho - Pitang Agile TI
# Data:     04/04/2019
# Objetivo: Tarefa Redmine 214306
#-------------------------------------------------------------------------
class HelperPitang
{

    /**
     * Um construtor privado; previne a criação direta do objeto.
     */
    private function __construct()
    {}

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
                    $line = $text . ' : ' . $line;
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
        $pattern = substr($_SERVER['PHP_SELF'], 0, strrpos($_SERVER['PHP_SELF'], '/') + 1);
        
        return "http://$_SERVER[HTTP_HOST]$pattern";
    }

    /**
     * [carregarAno description].
     *
     * @return [type] [description]
     */
    public static function carregarAno()
    {
        $anoAtual = (int) date('Y');
        $anos = array();
        
        for ($i = 0; $i < 4; ++ $i) {
            $anos[] = strval($anoAtual - $i);
        }
        
        return $anos;
    }

    public static function criarArquivoFromBytes($nomeArquivo, $data)
    {
        file_put_contents($nomeArquivo, $data);
    }

    public static function deletarArquivo($nomeArquivo)
    {
        unlink($nomeArquivo);
    }

    public static function baixarArquivo($arquivo)
    {
        if (isset($arquivo) && file_exists($arquivo)) {
            switch (strtolower(substr(strrchr(basename($arquivo), "."), 1))) {
                case "pdf":
                    $tipo = "application/pdf";
                    break;
                case "exe":
                    $tipo = "application/octet-stream";
                    break;
                case "zip":
                    $tipo = "application/zip";
                    break;
                case "doc":
                    $tipo = "application/msword";
                    break;
                case "xls":
                    $tipo = "application/vnd.ms-excel";
                    break;
                case "ppt":
                    $tipo = "application/vnd.ms-powerpoint";
                    break;
                case "gif":
                    $tipo = "image/gif";
                    break;
                case "png":
                    $tipo = "image/png";
                    break;
                case "jpg":
                    $tipo = "image/jpg";
                    break;
                case "mp3":
                    $tipo = "audio/mpeg";
                    break;
                case "php":
                case "htm":
                case "html":
            }
            header("Content-Type: " . $tipo);
            header("Content-Length: " . filesize($arquivo));
            header("Content-Disposition: attachment; filename=" . basename($arquivo));
            readfile($arquivo);
            exit();
        }
    }
}

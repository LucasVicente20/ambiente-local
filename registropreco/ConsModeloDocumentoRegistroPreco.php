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
 * @category  Pitang Novo Layout
 * @package   App
 * @author    Pitang Agile TI <contato@pitang.com>
 * @copyright 2014 EMPRESA MUNICIPAL DE INFORMÁTICA - EMPREL
 * @license   http://www.php.net/license/3_01.txt PHP License 3.01
 * @version   Git: $Id:$
 */

 

// if (!require_once dirname(__FILE__)."/TemplateAppPadrao.php") {
//     throw new Exception("Error Processing Request - TemplateAppPadrao.php", 1);
// }
require_once "../app/TemplateAppPadrao.php";

/**
 * [proccessPrincipal description]
 * @return [type] [description]
 */
function proccessPrincipal()
{
    $tpl = new TemplatePaginaPadrao("../app/templates/ConsModeloDocumentoRegistroPreco.html", "ConsModeloDocumentoRegistroPreco");
    // $tpl = new TemplateAppPadrao("../app/templates/ConsModeloDocumentoRegistroPreco.html", "ConsModeloDocumentoRegistroPreco");

    $tpl->show();
}
/**
 * [frontController description]
 * @return [type] [description]
 */
function frontController()
{
    $botao = isset($_REQUEST['BotaoAcao'])  ? $_REQUEST['BotaoAcao']  : 'Principal';

    switch ($botao) {
        case 'Pesquisar':
            processPesquisar();
            break;
        case 'LimparTela':
            proccessPrincipal();
            break;
        case 'Principal':
        default:
            proccessPrincipal();
    }
}

frontController();
?>
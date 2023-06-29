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
 * @category  Sql
 * @package   Dados_Sql
 * @author    Pitang Agile TI <contato@pitang.com>
 * @copyright 2014 EMPRESA MUNICIPAL DE INFORMÁTICA - EMPREL
 * @license   http://www.php.net/license/3_01.txt PHP License 3.01
 */

/**
 */
class Dados_Sql_DocumentoAtaRP
{
    /**
     * [selecionaDocumentoPeloCodigoAta description]
     * @param  Negocio_ValorObjeto_Carpnosequ $carpnosequ [description]
     * @return [type]                                     [description]
     */
    public function selecionaDocumentoPeloCodigoAta(Negocio_ValorObjeto_Carpnosequ $carpnosequ)
    {
        $sql = "
            SELECT *
              FROM sfpc.tbdocumentoatarp darp
             WHERE darp.carpnosequ = %d
        ";

        return sprintf($sql, $carpnosequ->getValor());
    }
}

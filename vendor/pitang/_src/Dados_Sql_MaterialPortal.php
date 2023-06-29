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
 *
 */
class Dados_Sql_MaterialPortal
{
    /**
     * Checa se o material informado é genérico
     *
     * @param integer $codigo
     *
     * @return string $sql Consulta SQL para verificar se o material informado é genérico
     */
    public static function isMaterialGenerico($codigo)
    {
        assercao(is_integer($codigo), 'Código do Material deve ser informado!');

        $sql = "
            SELECT cmatepsequ
            FROM sfpc.tbmaterialportal
            WHERE cmatepsequ = %d
                AND fmatepgene LIKE 'S'
        ";

        return sprintf($sql, $codigo);
    }
}

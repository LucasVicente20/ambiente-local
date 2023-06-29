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
class Negocio_Entidade
{
    /**
     * [singleton description]
     * @param  [type] $tabela [description]
     * @return [type]         [description]
     */
    public static function singleton($tabela)
    {
        $dao = Conexao();
        $informacaoTabela = $dao->tableInfo($tabela);
        $entidade = array();

        foreach ($informacaoTabela as $value) {
            $name = $value['name'];
            $entidade[$name] = null;
        }

        return (object) $entidade;
    }
}

<?php
// 220038--
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
 * @category   PortalDGCO
 *
 * @author     Pitang Agile TI <contato@pitang.com>
 * @copyright  2014 EMPRESA MUNICIPAL DE INFORMÁTICA - EMPREL
 * @license    http://www.php.net/license/3_01.txt PHP License 3.01
 *
 * @version    GIT: v1.30.1
 */

/**
 */
class Negocio_Repositorio_GrupoOrgao extends Negocio_Repositorio_Abstrato
{

    /**
     * Nome da tabela no Schema
     *
     * @var string
     */
    const NOME_TABELA = 'sfpc.tbgrupoorgao';

    /**
     *
     * {@inheritDoc}
     *
     * @see Negocio_Repositorio_Interface::listarTodos()
     */
    public function listarTodos()
    {
        $sql = "
            SELECT * FROM " . self::NOME_TABELA . "
        ";
        
        $res = ClaDatabasePostgresql::executarSQL($sql);
        
        ClaDatabasePostgresql::hasError($res);
        
        return $res;
    }
}

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
 * @category   PortalDGCO
 *
 * @author     Pitang Agile TI <contato@pitang.com>
 * @copyright  2014 EMPRESA MUNICIPAL DE INFORMÁTICA - EMPREL
 * @license    http://www.php.net/license/3_01.txt PHP License 3.01
 * 
 * @version   GIT: EMPREL-SAD-PORTAL-COMPRAS-REGISTRO-PRECO-BL-20160517-1738
 */
abstract class Dados_Abstrata implements Dados_Interface
{

    /**
     *
     * {@inheritDoc}
     *
     * @see Dados_Interface::getConexao()
     */
    public function getConexao()
    {
        return ClaDatabasePostgresql::getConexao();
    }
    /**
     * 
     * @param unknown $res
     * @return boolean|string
     */
    public function hasError($res)
    {
        return ClaDatabasePostgresql::hasError($res);
    }
    /**
     * 
     * @param string $sql
     * @param unknown $database
     */
    public function executarSQL($sql, $database = null)
    {
        return ClaDatabasePostgresql::executarSQL($sql, $database);
    }
    /**
     * 
     * @param string $nomeTabela
     */
    public function getEntidade($nomeTabela)
    {
        return ClaDatabasePostgresql::getEntidade($nomeTabela);
    }
}

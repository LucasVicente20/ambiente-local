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
 */

/**
 */
abstract class Negocio_Repositorio_Abstrato implements Negocio_Repositorio_Interface
{

    /**
     *
     * @var DB_pgsql
     */
    private $database;

    /**
     *
     * @param DB_pgsql $database            
     */
    public function __construct(DB_pgsql $database = null)
    {
        $this->database = ! ($database instanceof DB_pgsql) ? ClaDatabasePostgresql::getConexao() : $database;
    }

    /**
     *
     * {@inheritDoc}
     *
     * @see Dados_Repositorio_Interface::getConexao()
     *
     * @return DB_pgsql
     */
    public function getConexao()
    {
        return $this->database;
    }
}

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
 * @version    GIT: v1.30.1
 */

/**
 */
class Negocio_Repositorio_OrgaoLicitante extends Negocio_Repositorio_Abstrato
{

    /**
     * Nome da tabela no Schema
     *
     * @var string
     */
    const NOME_TABELA = 'sfpc.tborgaolicitante';

    /**
     * Consulta se o usuário possui mais de um orgão licitante.
     *
     * @return DB_common::getAll() array, string or numeric data to be added to the prepared statement.
     *         Quantity of items passed must match quantity of placeholders in the prepared statement:
     *         meaning 1 placeholder for non-array parameters or 1 placeholder per array element.
     */
    public function consultaOrgaosLicitantesUsuario()
    {
        $sql = "
        SELECT
            DISTINCT c.CORGLICODI, ol.eorglidesc
        FROM
            SFPC.TBCENTROCUSTOPORTAL c
        INNER JOIN " . self::NOME_TABELA . " ol
            ON ol.corglicodi = c.corglicodi
        WHERE c.CORGLICODI IS NOT NULL AND c.ACENPOANOE = ?
        AND c.FCENPOSITU <> 'I' AND c.CCENPOSEQU IN
        (SELECT USU.CCENPOSEQU FROM SFPC.TBUSUARIOCENTROCUSTO USU
            WHERE USU.CUSUPOCODI = ? AND USU.fusucctipo = 'C')
        ";

        return $this->getConexao()->getAll($sql, array(
            date('Y'),
            (int) $_SESSION['_cusupocodi_']
        ), DB_FETCHMODE_OBJECT);
    }

    /**
     * Seleciona a descrição de um Órgão Licitante informado
     *
     * @param integer $orgaoLicitante
     *
     * @return DB_common::getAll() array, string or numeric data to be added to the prepared statement.
     *         Quantity of items passed must match quantity of placeholders in the prepared statement:
     *         meaning 1 placeholder for non-array parameters or 1 placeholder per array element.
     */
    public function selecionaDescricaoOrgaoLicitante($orgaoLicitante)
    {
        $sql = "
            SELECT ol.eorglidesc
            FROM " . self::NOME_TABELA . " ol
            WHERE ol.corglicodi = ?
        ";

        return $this->getConexao()->getAll($sql, array(
            $orgaoLicitante
        ), DB_FETCHMODE_OBJECT);
    }

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

    public function consultaOrgaos()
    {
        $sql = "
            SELECT CORGLICODI, EORGLIDESC
            FROM " . self::NOME_TABELA . "
            ORDER BY EORGLIDESC
        ";

        $res = ClaDatabasePostgresql::executarSQL($sql);

        ClaDatabasePostgresql::hasError($res);

        return $res;
    }
}

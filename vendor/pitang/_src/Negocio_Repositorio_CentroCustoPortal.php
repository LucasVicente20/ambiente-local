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
class Negocio_Repositorio_CentroCustoPortal
{
    /**
     * Nome da tabela no Schema
     *
     * @var string
     */
    const NOME_TABELA = 'sfpc.tbcentrocustoportal';

    /**
     * [getCentroCustoUsuario description]
     * @param  [type] $cgrempcodi [description]
     * @param  [type] $cusupocodi [description]
     * @return [type]             [description]
     */
    public function getCentroCustoUsuario($cgrempcodi, $cusupocodi)
    {
        $sql = "
            SELECT
                   ccp.ccenpocorg, ccp.ccenpounid
              FROM sfpc.tbcentrocustoportal ccp
                   INNER JOIN sfpc.tbusuariocentrocusto ucc
                           ON ucc.ccenposequ = ccp.ccenposequ
             WHERE ucc.cgrempcodi = %d
                   AND ucc.cusupocodi = %d
                   AND ucc.fusucctipo LIKE 'C'
        ";

        $resultado = ClaDatabasePostgresql::executarSQL(sprintf($sql, $cgrempcodi, $cusupocodi));

        ClaDatabasePostgresql::hasError($resultado);

        return $resultado;
    }
}

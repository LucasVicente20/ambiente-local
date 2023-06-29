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
 * @category  Pitang Registro Preço
 * @package   registropreco
 * @author    Pitang Agile TI <contato@pitang.com>
 * @copyright 2014 EMPRESA MUNICIPAL DE INFORMÁTICA - EMPREL
 * @license   http://www.php.net/license/3_01.txt PHP License 3.01
 * 
 * @version GIT: EMPREL-SAD-PORTAL-COMPRAS-BL-FUNC-20160426-1545-5-g272ac8d
 */
class Service
{

    /**
     * Get Valor Estimado TRP.
     *
     * @param int $codigo            
     * @param string $tipo            
     *
     * @return string
     */
    public static function getValorEstimadoTRP($codigo, $tipo)
    {
        if (empty($codigo)) {
            throw new InvalidArgumentException('Error Processing Request', 1);
        }
        
        if (empty($tipo)) {
            throw new InvalidArgumentException('Error Processing Request', 1);
        }
        
        if ('CADUM' == strtoupper($tipo)) {
            $res = ClaDatabasePostgresql::getConexao();
            $res->setFetchMode(DB_FETCHMODE_ASSOC);
            
            return converte_valor_estoques(calcularValorTrp($res, 2, (integer) $codigo));
        }
        
        return '---';
    }

    /**
     * Check if Material Generico.
     *
     * @param int $codigo
     *            [description]
     *            
     * @return bool [description]
     */
    public static function isMaterialGenerico($codigo)
    {
        $sql = "
            SELECT cmatepsequ
            FROM sfpc.tbmaterialportal
            WHERE cmatepsequ = %d
                AND fmatepgene LIKE 'S'
        ";
        
        $res = executarSQL(ClaDatabasePostgresql::getConexao(), sprintf($sql, $codigo));
        
        return ($res->numRows() > 0) ? true : false;
    }
}
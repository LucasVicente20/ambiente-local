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
 */

/**
 */
class Negocio_Repositorio_ModalidadeLicitacao
{

    /**
     * [$tabela description]
     *
     * @var string
     */
    private $tabela = 'sfpc.tbmodalidadelicitacao';
    /**
     * [consultarPorCodigo description]
     * @param  Negocio_ValorObjeto_Cmodlicodi $cmodlicodi [description]
     * @return [type]                                     [description]
     */
    public function consultarPorCodigo(Negocio_ValorObjeto_Cmodlicodi $cmodlicodi)
    {
        $sql = "
            SELECT mod.*
              FROM %s mod
             WHERE mod.cmodlicodi = %d
        ";

        $resultado = ClaDatabasePostgresql::executarSQL(sprintf($sql, $this->tabela, $cmodlicodi->getValor()));

        ClaDatabasePostgresql::hasError($resultado);

        return current($resultado);
    }
    /**
     * 
     */
    public function listarTodosAtivos()
    {
        $sql = "
            select cmodlicodi, amodliorde, emodlidesc 
              from %s 
              order by amodliorde, cmodlicodi
        ";
        
        $res = ClaDatabasePostgresql::executarSQL(sprintf($sql, $this->tabela));
        
        ClaDatabasePostgresql::hasError($res);
        
        return $res;
    }
}

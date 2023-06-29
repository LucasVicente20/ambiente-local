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
 * @author     Pitang Agile TI <contato@pitang.com>
 * @copyright  2014 EMPRESA MUNICIPAL DE INFORMÁTICA - EMPREL
 * @license    http://www.php.net/license/3_01.txt PHP License 3.01
 *
 */
class Negocio_Repositorio_CaronaOrgaoExternoItem extends Negocio_Repositorio_Abstrato
{

    /**
     *
     * @var string
     */
    const NOME_TABELA = 'sfpc.tbcaronaorgaoexternoitem';

    /**
     *
     * @param Negocio_ValorObjeto_Carpnosequ $carpnosequ            
     * @param Negocio_ValorObjeto_Ccaroesequ $ccaroesequ            
     */
    public function listarTodosItemPeloCarpnosequECcaroesequ(Negocio_ValorObjeto_Carpnosequ $carpnosequ, Negocio_ValorObjeto_Ccaroesequ $ccaroesequ)
    {
        $sql = "
            SELECT
                *
            FROM
                sfpc.tbcaronaorgaoexternoitem ca
                LEFT OUTER JOIN sfpc.tbitemataregistropreconova iarpn
                    ON iarpn.citarpsequ = ca.citarpsequ
            WHERE
                ca.ccaroesequ = %d
                AND iarpn.carpnosequ = %d
        ";
        
        $resultado = ClaDatabasePostgresql::executarSQL(sprintf($sql, $ccaroesequ->getValor(), $carpnosequ->getValor()));
        
        ClaDatabasePostgresql::hasError($resultado);
        
        return $resultado;
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
          SELECT *
            FROM %s
        ";
        
        $res = ClaDatabasePostgresql::executarSQL(sprintf($sql, self::NOME_TABELA));
        
        ClaDatabasePostgresql::hasError($res);
        
        return $res;
    }
}

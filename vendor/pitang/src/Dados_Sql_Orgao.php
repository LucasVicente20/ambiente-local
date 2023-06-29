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
 * @author     Pitang Agile TI <contato@pitang.com>
 * @copyright  2014 EMPRESA MUNICIPAL DE INFORMÁTICA - EMPREL
 * @license    http://www.php.net/license/3_01.txt PHP License 3.01
 */
class Dados_Sql_Orgao
{
    /**
     * Comando SQL para consultar os orgaos participantes de uma ata de
     * Registro de preço.
     *
     * @return string $sql Comando SQL
     */
    public static function sqlOrgaoParticipante()
    {
        $sql = '
            SELECT DISTINCT ol.corglicodi, ol.eorglidesc
            FROM sfpc.tbparticipanteatarp parp
            JOIN sfpc.tborgaolicitante ol ON ol.corglicodi = parp.corglicodi
        ';

        return $sql;
    }

    /**
     * Comando SQL para consultar os orgaos gerenciadores e qual a sua
     * ata interna.
     *
     * @return string $sql Comando SQL
     */
    public static function sqlOrgaoGerenciadorComAtaInterna()
    {
        $sql = '
            SELECT DISTINCT o.corglicodi, o.eorglidesc
            FROM  sfpc.tborgaolicitante o
            INNER JOIN sfpc.tbataregistroprecointerna a ON o.corglicodi = a.corglicodi
            ORDER BY o.eorglidesc ASC
        ';

        return $sql;
    }

    /**
     * Comando SQL para.
     *
     * @return [type] [description]
     */
    public static function sqlOrgaoGerenciador()
    {
        $sql = '
            SELECT DISTINCT o.corglicodi, o.eorglidesc
            FROM  sfpc.tborgaolicitante o
            INNER JOIN sfpc.tbcentrocustoportal ccp ON ccp.corglicodi = o.corglicodi
            ORDER BY o.eorglidesc ASC
        ';

        return $sql;
    }
}

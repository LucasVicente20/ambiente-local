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
class Negocio_Repositorio_LicitacaoPortal
{

    /**
     * Nome da tabela no Schema.
     *
     * @var string
     */
    const NOME_TABELA = 'sfpc.tblicitacaoportal';

    /**
     *
     * @param Negocio_ValorObjeto_Clicpoproc $clicpoproc
     * @param Negocio_ValorObjeto_Alicpoanop $alicpoanop
     * @param Negocio_ValorObjeto_Cgrempcodi $cgrempcodi
     * @param Negocio_ValorObjeto_Ccomlicodi $ccomlicodi
     * @param Negocio_ValorObjeto_Corglicodi $corglicodi
     */
    public function procurar(Negocio_ValorObjeto_Clicpoproc $clicpoproc, Negocio_ValorObjeto_Alicpoanop $alicpoanop, Negocio_ValorObjeto_Cgrempcodi $cgrempcodi, Negocio_ValorObjeto_Ccomlicodi $ccomlicodi, Negocio_ValorObjeto_Corglicodi $corglicodi)
    {
        $sql = sprintf("
            SELECT *
              FROM " . self::NOME_TABELA . "
             WHERE 1 = 1
                   AND clicpoproc = %d
                   AND alicpoanop = %d
                   AND cgrempcodi = %d
                   AND ccomlicodi = %d
                   AND corglicodi = %d
            ", $clicpoproc->getValor(), $alicpoanop->getValor(), $cgrempcodi->getValor(), $ccomlicodi->getValor(), $corglicodi->getValor());

        $res = ClaDatabasePostgresql::executarSQL($sql);
        ClaDatabasePostgresql::hasError($res);
        return current($res);
    }
}

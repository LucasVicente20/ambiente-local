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
 * @category  Pitang_Registro_Preco
 * @package   registropreco
 * @author    Pitang Agile TI <contato@pitang.com>
 * @copyright 2014 EMPRESA MUNICIPAL DE INFORMÁTICA - EMPREL
 * @license   http://www.php.net/license/3_01.txt PHP License 3.01
 */
class Dados_Sql_AtaRegistroPrecoInterna
{

    public static function procurarPorProcessoLicitatorioOrgao(Negocio_ValorObjeto_Clicpoproc $clicpoproc, Negocio_ValorObjeto_Alicpoanop $alicpoanop, Negocio_ValorObjeto_Corglicodi $corglicodi)
    {
        $sql = "
         SELECT
            a.farpinsitu,
            a.aarpinpzvg,
            a.tarpinulat,
            a.carpnosequ,
            a.carpnoseq1,
            a.alicpoanop,
            a.corglicodi,
            s.csolcosequ,
            d.edoclinome,
            a.aarpinanon,
            a.cgrempcodi, 
            a.cusupocodi
        FROM
            sfpc.tbataregistroprecointerna a
        LEFT OUTER JOIN
            sfpc.tbsolicitacaolicitacaoportal s
            ON (
                s.clicpoproc = a.clicpoproc
                AND s.alicpoanop = a.alicpoanop
                AND s.ccomlicodi = a.ccomlicodi
                AND s.corglicodi = a.corglicodi
            )
        LEFT OUTER JOIN
            sfpc.tbdocumentolicitacao d
            ON (
                d.clicpoproc = a.clicpoproc
                AND d.alicpoanop = a.alicpoanop
                AND d.ccomlicodi = a.ccomlicodi
                AND d.corglicodi = a.corglicodi
            )
         WHERE
            a.clicpoproc = %d AND a.alicpoanop = %d AND a.corglicodi = %d order by a.carpnosequ
        ";

        return sprintf($sql, $clicpoproc->getValor(), $alicpoanop->getValor(), $corglicodi->getValor());
    }
}

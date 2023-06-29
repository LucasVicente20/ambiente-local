<?php
// 220038--
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
class Dados_Sql_ItemAtaRegistroPrecoNova
{

    /**
     *
     * @param integer $carpnosequ
     * @throws InvalidArgumentException
     */
    public static function sqlFind(Negocio_ValorObjeto_Carpnosequ $carpnosequ)
    {
        $sql = sprintf("
            SELECT
                iarpn.carpnosequ,
                iarpn.citarpsequ,
                iarpn.aitarporde,
                iarpn.cmatepsequ,
                m.ematepdesc,
                um.eunidmsigl,
                iarpn.eitarpdescmat,
                iarpn.cservpsequ,
                s.eservpdesc,
                iarpn.eitarpdescse,
                iarpn.aitarpqtor,
                iarpn.aitarpqtat,
                iarpn.vitarpvori,
                iarpn.vitarpvatu,
                iarpn.citarpnuml,
                iarpn.fitarpsitu,
                iarpn.eitarpmarc,
                iarpn.eitarpmode,
                m.fmatepgene
            FROM
                sfpc.tbitemataregistropreconova iarpn
                LEFT JOIN sfpc.tbmaterialportal m ON iarpn.cmatepsequ = m.cmatepsequ
                LEFT JOIN sfpc.tbservicoportal s ON iarpn.cservpsequ = s.cservpsequ
                LEFT JOIN sfpc.tbunidadedemedida um ON um.cunidmcodi = m.cunidmcodi
            WHERE
                iarpn.carpnosequ = %d
            ORDER BY
                iarpn.aitarporde

            ", $carpnosequ->getValor());
        return $sql;
    }

    /**
     *
     * @param Negocio_ValorObjeto_Clicpoproc $processo
     * @param Negocio_ValorObjeto_Alicpoanop $anoProcesso
     * @param Negocio_ValorObjeto_Corglicodi $orgao
     * @return string
     */
    public static function sqlLicitacaoAtaInterna(Negocio_ValorObjeto_Clicpoproc $clicpoproc, Negocio_ValorObjeto_Alicpoanop $alicpoanop, Negocio_ValorObjeto_Corglicodi $corglicodi)
    {
        $sql = "
            SELECT DISTINCT
                l.clicpoproc,
                l.alicpoanop,
                l.xlicpoobje,
                l.ccomlicodi,
                c.ecomlidesc,
                o.corglicodi,
                o.eorglidesc,
                m.emodlidesc,
                l.clicpocodl,
                l.cgrempcodi,
                l.alicpoanol
            FROM
                sfpc.tblicitacaoportal l
            INNER JOIN
                sfpc.tborgaolicitante o
                ON o.corglicodi= %d AND l.corglicodi = o.corglicodi
            INNER JOIN
                sfpc.tbcomissaolicitacao c
                ON l.ccomlicodi = c.ccomlicodi
            INNER JOIN
                sfpc.tbmodalidadelicitacao m
                ON l.cmodlicodi = m.cmodlicodi
            WHERE
                l.alicpoanop = %d AND l.clicpoproc = %d
        ";

        return sprintf($sql, $corglicodi->getValor(), $alicpoanop->getValor(), $clicpoproc->getValor());
    }
}

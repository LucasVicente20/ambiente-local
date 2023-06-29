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
class Negocio_Repositorio_ItemAtaRegistroPrecoNova
{
    /**
     *
     * @param integer $carpnosequ
     * @return NULL
     */
    public function find($carpnosequ)
    {
        $sql = Dados_Sql_ItemAtaRegistroPrecoNova::sqlFind(new Negocio_ValorObjeto_Carpnosequ($carpnosequ));
        return ClaDatabasePostgresql::executarSQL($sql);
    }

    /**
     *
     * @param integer $carpnosequ
     * @param integer $item
     */
    public function getQuantidadeItemAtaMaterial($carpnosequ, $item)
    {
        $sql = "
            SELECT
                ia.carpnosequ,
                ia.cmatepsequ,
                ia.cservpsequ,
                ia.aitarpqtor,
                ia.aitarpqtat
            FROM
                sfpc.tbitemataregistropreconova ia
            WHERE
            	ia.carpnosequ = $carpnosequ
                AND ia.cmatepsequ = $item
                OR ia.cservpsequ = $item
        ";

        return ClaDatabasePostgresql::executarSQL($sql);
    }

    public function getDadosAta($carpnosequ)
    {
        $sql = "SELECT
                    i.clicpoproc,
                    i.alicpoanop,
                    i.corglicodi,
                    c.ecomlidesc,
                    o.eorglidesc,
                    i.aarpinpzvg
                FROM
                    sfpc.tbitemataregistropreconova n
                    INNER JOIN sfpc.tbataregistroprecointerna i
                        ON n.carpnosequ = i.carpnosequ
                    INNER JOIN sfpc.tblicitacaoportal l
                        ON l.corglicodi = i.corglicodi
                        AND l.clicpoproc = i.clicpoproc
                        AND l.alicpoanop = i.alicpoanop
                    INNER JOIN sfpc.tborgaolicitante o
                        ON o.corglicodi = i.corglicodi
                    INNER JOIN sfpc.tbcomissaolicitacao c
                        ON i.ccomlicodi = c.ccomlicodi
                WHERE
                    n.carpnosequ = $carpnosequ";

        return ClaDatabasePostgresql::executarSQL($sql);
    }
}

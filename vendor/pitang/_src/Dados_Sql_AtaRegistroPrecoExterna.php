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

/**
 */
class Dados_Sql_AtaRegistroPrecoExterna
{

    /**
     *
     * @param integer $ano
     */
    public function sqlSelecionaTodasAtasPeloAno($ano)
    {
        $sql = "
            SELECT
                a.carpnosequ,
                a.aarpexanon,
                a.carpexcodn,
                a.earpexproc,
                b.nforcrrazs as original,
                c.nforcrrazs as atual
            FROM
                sfpc.tbataregistroprecoexterna A
                LEFT JOIN sfpc.tbfornecedorcredenciado B
                    ON a.aforcrsequ = b.aforcrsequ
                LEFT JOIN sfpc.tbfornecedorcredenciado C
                    ON a.aforcrseq1 = c.aforcrsequ
            WHERE
                a.aarpexanon = %d
        ";

        return sprintf($sql, $ano);
    }

    /**
     * [sqlProcessoExterno description]
     *
     * @param integer $aarpexanon
     *            Ano da numeração da ata
     * @param integer $carpnosequ
     *            Código sequencial da ata de registro de preço
     * @return [type] [description]
     */
    public function sqlSelecionaPorAnoNumeracaoECodigoSequencial($aarpexanon, $carpnosequ)
    {
        $sql = "
        SELECT
            a.carpnosequ,
            a.aarpexanon,
            a.carpexcodn,
            a.earpexproc,
            a.cmodlicodi,
            a.earpexorgg,
            a.earpexobje,
            a.tarpexdini,
            a.aarpexpzvg,
            a.aforcrsequ,
            a.aforcrseq1,
            a.farpexsitu,
            a.cusupocodi,
            a.tarpinulat
        FROM
            sfpc.tbataregistroprecoexterna A
        WHERE
            a.aarpexanon = %d
            AND a.carpnosequ = %d
        ";

        return sprintf($sql, $aarpexanon, $carpnosequ);
    }

    /**
     *
     * @param Negocio_Entidade_AtaRegistroPrecoExterna $entidade
     */
    public function filtrarAtaExterna(Negocio_Entidade_AtaRegistroPrecoExterna $entidade)
    {
        $sql = "
            SELECT *
              FROM sfpc.tbataregistroprecoexterna arpe
             WHERE 1 = 1
        ";

        // if (! empty($entidade->getCarpnosequ())) {
        // $sql .= sprintf(" AND carpnosequ = %d ", $entidade->getCarpnosequ());
        // }

        // if (! empty($entidade->getAarpexanon())) {
        // $sql .= sprintf(" AND aarpexanon = %d ", $entidade->getAarpexanon());
        // }

        return $sql;
    }
}

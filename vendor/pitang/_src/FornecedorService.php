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
class FornecedorService
{

    /**
     * Verifica se o CNPJ ou CPF Existe no cadastro de Fornecedores Credenciados.
     *
     * @param integer $cnpjOrCpf
     * @param integer $numeroCnpjOrCpf
     * @throws InvalidArgumentException
     * @return NULL
     */
    public static function verificarFornecedorCredenciado($cnpjOrCpf, $numeroCnpjOrCpf)
    {
        if (empty($cnpjOrCpf)) {
            throw new InvalidArgumentException('$cnpjOrCpf não foi informado', 1);
        }

        if (empty($numeroCnpjOrCpf)) {
            throw new InvalidArgumentException('$numeroCnpjOrCpf não foi informado', 1);
        }

        $numeroCnpjOrCpf = preg_replace('/[^0-9]/', '', $numeroCnpjOrCpf);
            
        $sql = '
            SELECT
                aforcrsequ,
                nforcrrazs,
                eforcrlogr,
                aforcrnume,
                eforcrcomp,
                eforcrbair,
                nforcrcida,
                cforcresta
            FROM sfpc.tbfornecedorcredenciado
            WHERE ';

        $sql .= ($cnpjOrCpf == 1) ? " aforcrccgc = '%s' " : " aforcrccpf = '%s' ";

        $res = ClaDatabasePostgresql::executarSQL(sprintf($sql, $numeroCnpjOrCpf));
        return $res;
    }

    public static function getFornecedorPorId($aforcrsequ) 
    {
        if (empty($aforcrsequ)) {
            throw new InvalidArgumentException('$cnpjOrCpf não foi informado', 1);
        }

        $sql = '
        SELECT
            aforcrsequ,
            aforcrccgc,
            aforcrccpf,
            cceppocodi,
            nforcrrazs,
            eforcrlogr,
            aforcrnume,
            eforcrcomp,
            eforcrbair,
            nforcrcida,
            cforcresta
        FROM sfpc.tbfornecedorcredenciado
        WHERE aforcrsequ = %s';

        $res = ClaDatabasePostgresql::executarSQL(sprintf($sql, $aforcrsequ));
        ClaDatabasePostgresql::hasError($res);
        
                return $res;
    }

    /**
     * Consulta o fornecedor Original da Ata Externa
     *
     * @param integer $carpnosequ
     * @return NULL
     */
    public static function getFornecedorOriginalAtaExterna($carpnosequ)
    {
        assercao(! is_null($carpnosequ), "Parâmetro '$carpnosequ' requerido");
        $sql = "
            SELECT
    				forn.*
			FROM
			    sfpc.tbataregistroprecoexterna arp
			INNER JOIN sfpc.tbfornecedorcredenciado forn ON forn.aforcrsequ = arp.aforcrsequ
			WHERE arp.carpnosequ = %d";

        $res = ClaDatabasePostgresql::executarSQL(sprintf($sql, $carpnosequ));

        ClaDatabasePostgresql::hasError($res);

        return $res;
    }

    /**
     * Consulta o fornecedor Atual da Ata Externa
     *
     * @param integer $carpnosequ
     * @return NULL
     */
    public static function getFornecedorAtualAtaExterna($carpnosequ)
    {
        assercao(! is_null($carpnosequ), "Parâmetro '$carpnosequ' requerido");
        $sql = "
            SELECT
    				forn.*
			FROM
			    sfpc.tbataregistroprecoexterna arp
			INNER JOIN sfpc.tbfornecedorcredenciado forn ON forn.aforcrsequ = arp.aforcrseq1
            WHERE arp.carpnosequ = %d";

        $res = ClaDatabasePostgresql::executarSQL(sprintf($sql, $carpnosequ));

        ClaDatabasePostgresql::hasError($res);

        return $res;
    }
}
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
class Dados_Sql_FornecedorCredenciado
{

    /**
     * Guarda uma instância da classe.
     *
     * @var Dados_Sql_FornecedorCredenciado
     */
    private static $instance;

    /**
     * Um construtor privado; previne a criação direta do objeto.
     */
    private function __construct()
    {
    }

    /**
     * Previne que o usuário clone a instância.
     */
    public function __clone()
    {
        trigger_error('Clone is not allowed.', E_USER_ERROR);
    }

    /**
     * Get Dados_Sql_FornecedorCredenciado.
     *
     * Interface para uso da conexao implementada pela EMPREL
     *
     * @return Dados_Sql_FornecedorCredenciado
     */
    public static function getInstancia()
    {
        if (! isset(self::$instance)) {
            self::$instance = new Dados_Sql_FornecedorCredenciado();
        }
        
        return self::$instance;
    }

    /**
     *
     * @param integer $aforcrsequ
     *            Código do Fornecedor
     */
    public function selecionarFornecedorPorCodigo($aforcrsequ)
    {
        $sql = "
            SELECT
                f.aforcrccgc,
                f.aforcrsequ,
                f.aforcrccpf,
                f.nforcrrazs,
                f.eforcrlogr
            FROM
                sfpc.tbfornecedorcredenciado f
            WHERE
                f.aforcrsequ = %d;
        ";
        
        return sprintf($sql, $aforcrsequ);
    }
}

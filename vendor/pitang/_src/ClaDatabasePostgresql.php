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
 * @version    GIT: v1.29.3-3-gec130ae
 */
class ClaDatabasePostgresql implements Conexao_Interface
{

    /**
     * Guarda uma instância da classe.
     *
     * @var DB_pgsql
     */
    private static $instance;

    /**
     * Um construtor privado; previne a criação direta do objeto.
     */
    private function __construct()
    {}

    /**
     * Previne que o usuário clone a instância.
     */
    public function __clone()
    {
        trigger_error('Clone is not allowed.', E_USER_ERROR);
    }

    /**
     * Get Conexao.
     *
     * Interface para uso da conexao implementada pela EMPREL
     *
     * @return DB_pgsql
     */
    public static function getConexao()
    {
        if (! isset(self::$instance)) {
            self::$instance = &Conexao();
        }
        
        return self::$instance;
    }

    /**
     * Tem erro no resource.
     *
     * Checa se o resource tem erro
     *
     * @param DB_result $res            
     */
    public static function hasError($res)
    {
        if (PEAR::isError($res)) {
            $msg = __FILE__ . "\n
                Linha: " . __LINE__ . "\n
                Sql: " . $res->getMessage();
            
            // ExibeErroBD($msg);
            
            return $msg;
        }
        
        return false;
    }

    /**
     * Executa um comando SQL.
     *
     * @param string $sql            
     * @param resource $database            
     *
     * @return NULL or []
     */
    public static function executarSQL($sql, $database = null)
    {
        if (is_null($database)) {
            $database = ClaDatabasePostgresql::getConexao();
        }
        
        if (! $database instanceof DB_pgsql) {
            return false;
        }
        
        $resultados = array();
        $resultado = executarSQL($database, $sql);
        $linha = null;
        while ($resultado->fetchInto($linha, DB_FETCHMODE_OBJECT)) {
            $resultados[] = $linha;
        }
        
        return $resultados;
    }

    /**
     * Retorna uma entidade.
     *
     * @param string $nomeTabela
     *            informe schema + tabela (sfpc.tbxxxx)
     *            
     * @return StdClass
     */
    public static function getEntidade($nomeTabela)
    {
        $informacaoTabela = self::getConexao()->tableInfo($nomeTabela);
        $entidade = array();
        
        foreach ($informacaoTabela as $value) {
            $name = $value['name'];
            $entidade[$name] = null;
        }
        
        return (object) $entidade;
    }
}

<?php
// 220038--
/**
 *
 * @author jfsi
 */
class ClaDatabaseOracle implements Conexao_Interface
{

    /**
     * Guarda uma instância da classe.
     *
     * @var \DB_pgsql
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
     * Get Conexao Oracle.
     *
     * Interface para uso da conexao implementada pela EMPREL
     *
     * @return \DB_common
     */
    public static function getConexao()
    {
        if (! isset(self::$instance)) {
            self::$instance = &ConexaoOracle();
        }
        
        return self::$instance;
    }

    /**
     * has Error.
     *
     * Check if the resource has error
     *
     * @param DB_result $res            
     */
    public static function hasError($res)
    {
        if (PEAR::isError($res)) {
            $msg = __FILE__ . "\n
                Linha: " . __LINE__ . "\n
                Sql: " . $res->getMessage();
            
            ExibeErroBD($msg);
            
            return $msg;
        }
        
        return true;
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
            $database = ClaDatabaseOracle::getConexao();
        }
        
        $resultados = array();
        $resultado = executarSQL($database, $sql);
        $linha = null;
        while ($resultado->fetchInto($linha, DB_FETCHMODE_OBJECT)) {
            $resultados[] = $linha;
        }
        
        return $resultados;
    }
}

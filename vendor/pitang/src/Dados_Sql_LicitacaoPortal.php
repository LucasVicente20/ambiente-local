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

/**
 */
class Dados_Sql_LicitacaoPortal extends Dados_Sql_Abstrata
{
    /**
     * Guarda uma instância da classe.
     *
     * @var __CLASS__
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
     * Get __CLASS__
     *
     * @return __CLASS__
     */
    public static function getInstancia()
    {
        if (! isset(self::$instance)) {
            self::$instance = new Dados_Sql_LicitacaoPortal();
        }

        return self::$instance;
    }
    /**
     * [getEntidade description]
     * @param  Negocio_ValorObjeto_Clicpoproc $clicpoproc [description]
     * @param  Negocio_ValorObjeto_Alicpoanop $alicpoanop [description]
     * @return [type]                                     [description]
     */
    public function getEntidade(Negocio_ValorObjeto_Clicpoproc $clicpoproc, Negocio_ValorObjeto_Alicpoanop $alicpoanop)
    {
        $sql = "
            SELECT l.*
              FROM sfpc.tblicitacaoportal l
             WHERE 1 = 1
                   AND l.clicpoproc = %d
                   AND l.alicpoanop = %d
        ";

        return sprintf($sql, $clicpoproc->getValor(), $alicpoanop->getValor());
    }
}

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
 * @version   GIT: v1.18.0-17-g9920068
 */

/**
 */
class Dados_Sql_ModalidadeLicitacao
{

    /**
     * Guarda uma instância da classe.
     *
     * @var Dados_Sql_ModalidadeLicitacao
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
     * Get Dados_Sql_ModalidadeLicitacao.
     *
     * Interface para uso da conexao implementada pela EMPREL
     *
     * @return Dados_Sql_ModalidadeLicitacao
     */
    public static function getInstancia()
    {
        if (! isset(self::$instance)) {
            self::$instance = new Dados_Sql_ModalidadeLicitacao();
        }

        return self::$instance;
    }

    public function sqlSelecionaTodas()
    {
        $sql = "
            SELECT
                m.cmodlicodi,
                m.emodlidesc
            FROM
                sfpc.tbmodalidadelicitacao m
            ORDER BY
                m.emodlidesc
        ";

        return sprintf($sql);
    }

    /**
     *
     * @param integer $ccomlicodi
     *
     * @return string
     */
    public static function selecionaModalideLicitacaoPorCodigo($cmodlicodi)
    {
        assercao(is_null($cmodlicodi), 'Requerido');

        $sql = "SELECT * FROM sfpc.tbmodalidadelicitacao ml";
        $sql .= " WHERE ml.cmodlicodi = %d";

        return sprintf($sql, $cmodlicodi);
    }

    /**
     * Seleciona Modalidade da Licitação por Licitação Portal
     *
     * @param Negocio_ValorObjeto_LicitacaoPortal $licitacao
     *
     * @return string
     */
    public static function selecionaModalidadePorLicitacaoPortal(Negocio_ValorObjeto_LicitacaoPortal $licitacao)
    {
        $sql = "
            SELECT
                lic.clicpoproc,
                lic.alicpoanop,
                lic.cgrempcodi,
                lic.ccomlicodi,
                lic.corglicodi,
                lic.cmodlicodi,
                moda.emodlidesc
            FROM
                sfpc.tblicitacaoportal lic
            INNER JOIN
                sfpc.tbmodalidadelicitacao moda
                ON moda.cmodlicodi = lic.cmodlicodi
                    AND lic.clicpoproc = %d
                    AND lic.alicpoanop = %d
                    AND lic.cgrempcodi = %d
                    AND lic.ccomlicodi = %d
                    AND lic.corglicodi = %d
        ";

        return sprintf($sql, $licitacao->getClicpoproc(), $licitacao->getAlicpoanop(), $licitacao->getCgrempcodi(), $licitacao->getCcomlicodi(), $licitacao->getCorglicodi());
    }
}

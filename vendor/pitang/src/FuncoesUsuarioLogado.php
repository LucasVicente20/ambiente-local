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
 * @category  Pitang Registro Preço
 *
 * @author    Pitang Agile TI <contato@pitang.com>
 * @copyright 2014 EMPRESA MUNICIPAL DE INFORMÁTICA - EMPREL
 * @license   http://www.php.net/license/3_01.txt PHP License 3.01
 *
 * @version   GIT: v1.29.3-3-gec130ae
 */
class FuncoesUsuarioLogado
{

    /**
     */
    public function __construct()
    {
    }

    /**
     *
     * @param integer $codigoUsuarioLogado            
     * @param integer $anoAtual            
     */
    public static function getOrgaoLicitanteCodigo($codigoUsuarioLogado, $anoAtual, $centroCusto = null)
    {
        $sql = " SELECT DISTINCT c.CORGLICODI ";
        $sql .= " FROM SFPC.TBCENTROCUSTOPORTAL c";
        $sql .= " WHERE c.CORGLICODI IS NOT NULL AND c.ACENPOANOE =" . $anoAtual;
        $sql .= " AND c.FCENPOSITU <> 'I'";
        if (null === $centroCusto || empty($centroCusto)) {
            $sql .= " AND c.CCENPOSEQU IN";
            $sql .= " (SELECT USU.CCENPOSEQU FROM SFPC.TBUSUARIOCENTROCUSTO USU";
            $sql .= " WHERE USU.CUSUPOCODI =" . $codigoUsuarioLogado;
            $sql .= " AND USU.fusucctipo = 'C')";
        } else {
            $sql .= " AND c.CCENPOSEQU = " . $centroCusto;
        }
        
        $resultado = executarSQL(ClaDatabasePostgresql::getConexao(), $sql);
        $orgaoUsuario = null;
        $resultado->fetchInto($orgaoUsuario, DB_FETCHMODE_OBJECT);
        
        return (integer) $orgaoUsuario->corglicodi;
    }

    /**
     */
    public static function obterOrgaoUsuarioLogado()
    {
        $anoCorrente = (int) date('Y');
        $codigoUsuarioLogado = (int) $_SESSION['_cusupocodi_'];
        // Recupera o orgão do usuário logado
        return self::getOrgaoLicitanteCodigo($codigoUsuarioLogado, $anoCorrente);
    }
}

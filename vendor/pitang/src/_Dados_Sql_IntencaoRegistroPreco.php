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
 * @version   GIT: v1.18.0-17-g9920068
 */

/**
 *
 */
class Dados_Sql_IntencaoRegistroPreco
{
    /**
     * Checa se o material informado é genérico
     *
     * @param integer $codigo
     *
     * @return string $sql Consulta SQL para verificar se o material informado é genérico
     */
    public static function isMaterialGenerico($codigo)
    {
        assercao(is_integer($codigo), 'Código do Material deve ser informado!');

        $sql = "
            SELECT cmatepsequ
            FROM sfpc.tbmaterialportal
            WHERE cmatepsequ = %d
                AND fmatepgene LIKE 'S'
        ";

        return sprintf($sql, $codigo);
    }
    /**
     * Retorna o ultimo código sequencial da intenção de registro de preco
     *
     * @param  int $cintrpsano Ano da intenção de registro de preço
     *
     * @return string comando SQL para retorna o ultimo código sequencial da intenção de registro de preço
     */
    public static function ultimoCodigoSequencialIntencaoRP($cintrpsano)
    {
        assercao(is_integer($cintrpsano), 'Ano da intenção de registro de preço deve ser informado!');

        $sql = '
            SELECT MAX(cintrpsequ)
            FROM sfpc.tbintencaoregistropreco
            WHERE cintrpsano = %d
        ';

        return sprintf($sql, $cintrpsano);
    }

    /**
     * Sql Select Intencao.
     *
     * @param int $sequencialIntencao            
     * @param int $anoIntencao            
     * @param string $dataInicio            
     * @param string $dataFim            
     *
     * @return string [description]
     */
    public static function sqlSelectIntencaoByDataInicioAndDataFimAndGrupoUsuario($sequencialIntencao = null, $anoIntencao = null, $dataInicio = null, $dataFim = null, $grupoUsuario = null)
    {
        $codigoUsuario = $_SESSION['_cusupocodi_'];
        $anoAtual = date('Y');
        $sql = "
            SELECT DISTINCT a.cintrpsequ, a.cintrpsano, a.tintrpdlim, a.xintrpobje, a.tintrpdcad, a.cusupocodi,
                a.xintrpobse
            FROM
                sfpc.tbintencaoregistropreco a
            INNER JOIN
                sfpc.tbintencaorporgao b
                ON a.cintrpsequ = b.cintrpsequ
                    AND a.cintrpsano = b.cintrpsano
                     AND B.CORGLICODI IN
    (SELECT DISTINCT c.CORGLICODI
    FROM SFPC.TBCENTROCUSTOPORTAL c
    WHERE c.CORGLICODI IS NOT NULL AND c.ACENPOANOE = $anoAtual
    AND c.FCENPOSITU <> 'I'
    AND c.CCENPOSEQU IN
        (SELECT USU.CCENPOSEQU FROM SFPC.TBUSUARIOCENTROCUSTO USU
         WHERE USU.CUSUPOCODI =$codigoUsuario
         AND USU.fusucctipo = 'C' ORDER BY 1)
    )
            WHERE
                1 = 1
        ";
        
        if (! is_null($sequencialIntencao)) {
            $sql .= " AND a.cintrpsequ = $sequencialIntencao ";
        }
        
        if (! is_null($anoIntencao)) {
            $sql .= " AND a.cintrpsano = $anoIntencao ";
        }
        
        if (! is_null($dataInicio) && is_null($dataFim)) {
            $sql .= " AND a.tintrpdcad >= '$dataInicio' ";
        }
        
        if (! is_null($dataFim) && is_null($dataInicio)) {
            $sql .= " AND a.tintrpdcad <= '$dataFim' ";
        }
        
        if (! is_null($dataInicio) && ! is_null($dataFim)) {
            $sql .= " AND a.tintrpdcad >= '$dataInicio' ";
            $sql .= " AND a.tintrpdcad <= '$dataFim' ";
        }
        
        $sql .= " AND a.fintrpsitu LIKE 'A' ";
        
        return $sql;
    }
}

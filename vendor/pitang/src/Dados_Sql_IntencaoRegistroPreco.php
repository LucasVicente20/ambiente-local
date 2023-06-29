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
 * @category  Pitang_Registro_Preco
 *
 * @author    Pitang Agile TI <contato@pitang.com>
 * @copyright 2014 EMPRESA MUNICIPAL DE INFORMÁTICA - EMPREL
 * @license   http://www.php.net/license/3_01.txt PHP License 3.01
 */

#---------------------------------------------------------------------
# Alterado: Pitang Agile TI - Caio Coutinho
# data: 12/03/2019
# Objetivo: Tarefa Redmine 212543
#---------------------------------------------------------------------

/**
 */
class Dados_Sql_IntencaoRegistroPreco
{
    /**
     * Checa se o material informado é genérico.
     *
     * @param int $codigo
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
     * Retorna o ultimo código sequencial da intenção de registro de preco.
     *
     * @param int $cintrpsano
     *                        Ano da intenção de registro de preço
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
     * @param int    $sequencialIntencao
     * @param int    $anoIntencao
     * @param string $dataInicio
     * @param string $dataFim
     *
     * @return string [description]
     */
    public static function sqlSelectIntencaoByDataInicioAndDataFimAndGrupoUsuario($sequencialIntencao = null, $anoIntencao = null, $dataInicio = null, $dataFim = null, $grupoUsuario = null, $situacao = null)
    {
        $codigoUsuario = $_SESSION['_cusupocodi_'];
        date_default_timezone_set('America/Recife');
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
         and c.corglicodi = $grupoUsuario
    )
            WHERE
                1 = 1
        ";

        if (!is_null($sequencialIntencao)) {
            $sql .= " AND a.cintrpsequ = $sequencialIntencao ";
        }

        if (!is_null($anoIntencao)) {
            $sql .= " AND a.cintrpsano = $anoIntencao ";
        }

        if (!is_null($dataInicio) && is_null($dataFim)) {
            $sql .= " AND to_char(a.tintrpdcad, 'YYYY-MM-DD') >= '$dataInicio' ";
        }

        if (!is_null($dataFim) && is_null($dataInicio)) {
            $sql .= " AND to_char(a.tintrpdcad, 'YYYY-MM-DD') <= '$dataFim' ";
        }

        if (!is_null($dataInicio) && !is_null($dataFim)) {
            $sql .= " AND to_char(a.tintrpdcad, 'YYYY-MM-DD') BETWEEN '$dataInicio' AND '$dataFim' ";
        }

        if (!is_null($situacao)) {
            $sql .= " AND (b.finrpositu IS NULL OR b.finrpositu <> 'I') ";
        }

        $sql .= " AND to_char(a.tintrpdlim, 'YYYY-MM-DD') >= '".date('Y-m-d')."' ";

        $sql .= " AND a.fintrpsitu LIKE 'A' ";

        $sql .= " ORDER BY a.cintrpsano DESC, a.cintrpsequ ASC ";

        return $sql;
    }

    /**
     * @param Negocio_ValorObjeto_Cintrpsequ $cintrpsequ
     * @param Negocio_ValorObjeto_Cintrpsano $cintrpsano
     * @param unknown                        $dataInicioCadastro
     * @param unknown                        $dataFimCadastro
     * @param unknown                        $centroCusto
     */
    public function sqllistarTodasIRPRespondidas($dataInicioCadastro, $dataFimCadastro, Negocio_ValorObjeto_Cintrpsequ $cintrpsequ = null, Negocio_ValorObjeto_Cintrpsano $cintrpsano = null, $centroCusto = null)
    {
        $codigoUsuario = (int) $_SESSION['_cusupocodi_'];
        $cgrempcodi = (int) $_SESSION['_cgrempcodi_'];
        date_default_timezone_set('America/Recife');
        $anoAtual = date('Y');

        $sql = "
            SELECT
                DISTINCT a.cintrpsequ,
                a.cintrpsano,
                a.tintrpdlim,
                a.xintrpobje,
                a.xintrpobse,
                a.fintrpsitu,
                a.tintrpdcad,
                a.cusupocodi,
                a.tintrpulat
            FROM
                sfpc.tbintencaoregistropreco a

                INNER JOIN SFPC.tbrespostaintencaorp rirp
                    ON a.cintrpsequ = rirp.cintrpsequ
                       AND a.cintrpsano = rirp.cintrpsano
                       AND rirp.frinrpsitu = 'A'

                INNER JOIN sfpc.tbintencaorporgao b
                    ON a.cintrpsequ = b.cintrpsequ
                        AND a.cintrpsano = b.cintrpsano
                        AND b.corglicodi IN (
                            SELECT
                                DISTINCT c.corglicodi
                            FROM
                                sfpc.tbcentrocustoportal c
                            WHERE
                                c.corglicodi IS NOT NULL
                                AND c.acenpoanoe = $anoAtual
                                AND c.fcenpositu <> 'I'
                                AND c.ccenposequ IN(
                                    SELECT
                                        usu.ccenposequ
                                    FROM
                                        sfpc.tbusuariocentrocusto usu
                                    WHERE
                                        usu.cusupocodi = $codigoUsuario
                                        AND usu.fusucctipo = 'C'
                                        AND usu.cgrempcodi =  $cgrempcodi ".
        // if (! is_null($centroCusto)) {
        // $sql .= "
        // AND usu.ccenposequ = $centroCusto
        // ";
        // }
        $sql .= '
                                    ORDER BY
                                        1
                                )
                        )
           WHERE
                1 = 1
        ';

        if ($cintrpsequ instanceof Negocio_ValorObjeto_Cintrpsequ) {
            $sql .= ' AND a.cintrpsequ = '.$cintrpsequ->getValor();
        }

        if ($cintrpsano instanceof Negocio_ValorObjeto_Cintrpsano) {
            $sql .= ' AND a.cintrpsano = '.$cintrpsano->getValor();
        }

        if (!is_null($dataInicioCadastro) && is_null($dataFimCadastro)) {
            $sql .= " AND to_char(a.tintrpdcad, 'YYYY-MM-DD') >= '$dataInicioCadastro' ";
        }

        if (!is_null($dataFimCadastro) && is_null($dataInicioCadastro)) {
            $sql .= " AND to_char(a.tintrpdcad, 'YYYY-MM-DD') <= '$dataFimCadastro' ";
        }

        if (!is_null($dataInicioCadastro) && !is_null($dataFimCadastro)) {
            $sql .= " AND to_char(a.tintrpdcad, 'YYYY-MM-DD') >= '$dataInicioCadastro' ";
            $sql .= " AND to_char(a.tintrpdcad, 'YYYY-MM-DD') <= '$dataFimCadastro' ";
        }

        $sql .= " AND a.fintrpsitu LIKE 'A' ";

        return $sql;
    }
}

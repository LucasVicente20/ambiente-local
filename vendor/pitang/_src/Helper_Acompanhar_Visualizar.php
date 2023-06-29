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
 * @category  Pitang Registro Preço
 * @package   registropreco
 * @author    Pitang Agile TI <contato@pitang.com>
 * @copyright 2014 EMPRESA MUNICIPAL DE INFORMÁTICA - EMPREL
 * @license   http://www.php.net/license/3_01.txt PHP License 3.01
 */

/**
 * -----------------------------------------------------------------------------
 * Alterado: Lucas Baracho
 * Data:     24/09/2018
 * Objetivo: Tarefa Redmine 203923
 * -----------------------------------------------------------------------------
*/

class Helper_Acompanhar_Visualizar
{

    /**
     *
     * @param unknown $codigo
     * @param unknown $tipo
     * @return string
     */
    public static function getValorEstimadoTRP($codigo, $tipo)
    {
        return Service::getValorEstimadoTRP($codigo, $tipo);
    }

    /**
     *
     * @param unknown $item
     * @return StdClass
     */
    public static function unificarChavesItem($item)
    {
        $novoItem = array();

        // Atributos em comum
        $novoItem['sequencialItemIntencao'] = $item->citirpsequ;
        $novoItem['sequencialIntencao'] = $item->cintrpsequ;
        $novoItem['anoIntencao'] = $item->cintrpsano;
        $novoItem['quantidadeConsolidada'] = $item->airirpqtpr;
        $novoItem['ordem'] = $item->aitirporde;
        $novoItem['valorUnitario'] = $item->vitirpvues;

        // Serviço
        $novoItem['tipo'] = 'CADUS';
        $novoItem['generico'] = false;
        $novoItem['descricao'] = $item->eservpdesc;
        $novoItem['descricaoDetalhada'] = $item->eitirpdescse;
        $novoItem['sequencialItem'] = $item->cservpsequ;
        $novoItem['valorTrpUnitario'] = '---';

        // Material
        if (isset($item->cmatepsequ)) {
            $novoItem['tipo'] = 'CADUM';
            $novoItem['generico'] = ($item->fmatepgene == 'S') ? true : false;
            $novoItem['descricao'] = $item->ematepdesc;
            $novoItem['descricaoDetalhada'] = $item->eitirpdescmat;
            $novoItem['sequencialItem'] = $item->cmatepsequ;
            $novoItem['valorTrpUnitario'] = Service::getValorEstimadoTRP($item->cmatepsequ, 'CADUM');
        }

        return (object) $novoItem;
    }

    public static function sqlSelectItemIntencao($sequencialIntencao, $anoIntencao, $centroCusto)
    {
        $codigoUsuario = $_SESSION['_cusupocodi_'];
        $anoAtual = date('Y');

        // Caso a centro de custo foi informado, trás o órgão referente a este centro
        // Há casos de existir mais de um órgão para o usuário logado
        $orgaoUsuarioLogado = FuncoesUsuarioLogado::getOrgaoLicitanteCodigo($codigoUsuario, $anoAtual, $centroCusto);

        $sql = "
            SELECT
            irirp.cintrpsequ,
            irirp.cintrpsano,
            irirp.citirpsequ,
            irirp.corglicodi,
            irirp.airirpqtpr,
            iirp.cmatepsequ,
            m.ematepdesc,
            iirp.cservpsequ,
            s.eservpdesc,
            iirp.EITIRPDESCSE,
            iirp.AITIRPORDE,
            iirp.VITIRPVUES,
            m.FMATEPGENE
        FROM
            sfpc.tbitemrespostaintencaorp irirp
            INNER JOIN sfpc.tbitemintencaoregistropreco iirp
                ON iirp.cintrpsequ = irirp.cintrpsequ
        		    AND iirp.cintrpsano = irirp.cintrpsano
        		    AND iirp.citirpsequ = irirp.citirpsequ
            LEFT JOIN sfpc.tbmaterialportal m ON m.cmatepsequ = iirp.cmatepsequ
            LEFT JOIN sfpc.tbservicoportal s ON s.cservpsequ = iirp.cservpsequ
            INNER JOIN sfpc.tbintencaoregistropreco irp
        	    ON iirp.cintrpsequ = irp.cintrpsequ
        	        AND iirp.cintrpsano = irp.cintrpsano
        	        AND irp.cintrpsequ = iirp.cintrpsequ
        	        AND irp.cintrpsano = iirp.cintrpsano
        	        AND irp.tintrpdlim < clock_timestamp()
        	        AND irp.fintrpsitu LIKE 'A'
        WHERE
            irirp.cintrpsequ = $sequencialIntencao
            AND irirp.cintrpsano = $anoIntencao
            AND irirp.corglicodi = $orgaoUsuarioLogado
            AND irirp.airirpqtpr > 0
        ORDER BY
            iirp.aitirporde
        ";

        return $sql;
    }

    /**
     *
     * @param unknown $sequencialIntencao
     * @param unknown $anoIntencao
     * @return string
     */
    public static function sqlSelectItensIntencao($sequencialIntencao, $anoIntencao)
    {
        $sql = "
            SELECT DISTINCT I.CITIRPSEQU, I.CINTRPSEQU, I.CINTRPSANO, SUM(R.AIRIRPQTPR) AS QTDCONSOLIDADA,
                I.CMATEPSEQU, M.EMATEPDESC, I.EITIRPDESCMAT, S.ESERVPDESC, I.CSERVPSEQU, I.EITIRPDESCSE, I.AITIRPORDE,
                I.VITIRPVUES, M.FMATEPGENE
            FROM
                SFPC.TBITEMRESPOSTAINTENCAORP R inner join SFPC.TBITEMINTENCAOREGISTROPRECO I on
                R.citirpsequ = I.citirpsequ
                and I.cintrpsequ = R.cintrpsequ
                and I.cintrpsano = R.cintrpsano inner join SFPC.TBRESPOSTAINTENCAORP RI on
                RI.corglicodi = R.corglicodi
                and RI.CINTRPSEQU = R.CINTRPSEQU
                and RI.CINTRPSANO = R.CINTRPSANO
                and RI.frinrpsitu = 'A' left join SFPC.TBMATERIALPORTAL M on
                I.CMATEPSEQU = M.CMATEPSEQU left join SFPC.TBSERVICOPORTAL S on
                I.CSERVPSEQU = S.CSERVPSEQU
            WHERE
                R.CINTRPSEQU = $sequencialIntencao
                AND R.CINTRPSANO = $anoIntencao
                --AND I.CMATEPSEQU IS NOT NULL
                --OR I.CSERVPSEQU IS NOT NULL
            GROUP BY
                I.CITIRPSEQU, I.CINTRPSEQU, I.CINTRPSANO, I.CMATEPSEQU, M.EMATEPDESC,  I.EITIRPDESCMAT, S.ESERVPDESC,
                I.CSERVPSEQU,  I.EITIRPDESCSE, I.AITIRPORDE, I.VITIRPVUES, M.FMATEPGENE
            ORDER BY
                I.AITIRPORDE
        ";

        return $sql;
    }

    /**
     *
     * @param unknown $sequencialIntencao
     * @param unknown $anoIntencao
     * @return string
     */
    public static function sqlSelectItensIntencaoTodasSituacoes($sequencialIntencao, $anoIntencao)
    {
        $sql = "
            SELECT DISTINCT I.CITIRPSEQU, I.CINTRPSEQU, I.CINTRPSANO, SUM(R.AIRIRPQTPR) AS QTDCONSOLIDADA,
                I.CMATEPSEQU, M.EMATEPDESC, I.EITIRPDESCMAT, S.ESERVPDESC, I.CSERVPSEQU, I.EITIRPDESCSE, I.AITIRPORDE,
                I.VITIRPVUES, M.FMATEPGENE
            FROM
                SFPC.TBITEMRESPOSTAINTENCAORP R
                INNER JOIN SFPC.TBITEMINTENCAOREGISTROPRECO I
                    ON R.citirpsequ = I.citirpsequ AND I.CINTRPSEQU = $sequencialIntencao AND I.CINTRPSANO = $anoIntencao
                INNER JOIN SFPC.TBRESPOSTAINTENCAORP RI
                    ON RI.corglicodi = R.corglicodi
                LEFT OUTER JOIN SFPC.TBMATERIALPORTAL M ON I.CMATEPSEQU = M.CMATEPSEQU
                LEFT OUTER JOIN SFPC.TBSERVICOPORTAL S ON I.CSERVPSEQU = S.CSERVPSEQU
            WHERE
                R.CINTRPSEQU = $sequencialIntencao
                AND R.CINTRPSANO = $anoIntencao
                AND I.CMATEPSEQU IS NOT NULL
                OR I.CSERVPSEQU IS NOT NULL
            GROUP BY
                I.CITIRPSEQU, I.CINTRPSEQU, I.CINTRPSANO, I.CMATEPSEQU, M.EMATEPDESC,  I.EITIRPDESCMAT, S.ESERVPDESC,
                I.CSERVPSEQU,  I.EITIRPDESCSE, I.AITIRPORDE, I.VITIRPVUES, M.FMATEPGENE
            ORDER BY
                I.AITIRPORDE
        ";

        return $sql;
    }

    /**
     *
     * @param unknown $sequencialIntencao
     * @param unknown $anoIntencao
     * @param string $ordem
     * @return string
     */
    public static function sqlSelectOrgaosIntencao($sequencialIntencao, $anoIntencao, $ordem = 'nome', $situacao = null)
    {
        $orderBy = ' b.eorglidesc ';
        if ($ordem == 'situacao') {
            $orderBy = ' c.frinrpsitu ';
        }

        $sql = ' SELECT a.cintrpsequ, a.cintrpsano, a.corglicodi, b.eorglidesc, c.frinrpsitu, c.trinrpulat ';
        $sql .= ' FROM sfpc.tbintencaorporgao a ';
        $sql .= ' INNER JOIN sfpc.tborgaolicitante b ON a.corglicodi = b.corglicodi ';
        $sql .= ' LEFT OUTER JOIN sfpc.tbrespostaintencaorp c ON a.corglicodi = c.corglicodi ';
        $sql .= " AND c.cintrpsequ = $sequencialIntencao AND c.cintrpsano = $anoIntencao ";
        $sql .= ' WHERE ';
        $sql .= "      a.cintrpsequ = $sequencialIntencao ";
        $sql .= "      AND a.cintrpsano = $anoIntencao ";

        if(!is_null($situacao)) {
            $sql .= "      AND (a.finrpositu IS NULL OR a.finrpositu <> 'I') ";
        }
        
        $sql .= " ORDER BY $orderBy ";

        return $sql;
    }

    /**
     *
     * @param unknown $sequencialIntencao
     * @param unknown $anoIntencao
     * @param string $ordem
     * @return string
     */
    public static function sqlSelectOrgaosIntencaoInformada($sequencialIntencao, $anoIntencao, $ordem = 'nome')
    {
        $orderBy = ' b.eorglidesc ';
        if ($ordem == 'situacao') {
            $orderBy = ' c.frinrpsitu ';
        }

        $sql = ' SELECT a.cintrpsequ, a.cintrpsano, a.corglicodi, b.eorglidesc, c.frinrpsitu, c.trinrpulat ';
        $sql .= ' FROM sfpc.tbintencaorporgao a ';
        $sql .= '      INNER JOIN sfpc.tborgaolicitante b ON a.corglicodi = b.corglicodi ';
        $sql .= "      INNER JOIN sfpc.tbrespostaintencaorp c ON a.corglicodi = c.corglicodi and c.frinrpsitu = 'A' ";
        $sql .= "      AND c.cintrpsequ = $sequencialIntencao AND c.cintrpsano = $anoIntencao ";
        $sql .= ' WHERE ';
        $sql .= "      a.cintrpsequ = $sequencialIntencao ";
        $sql .= "      AND a.cintrpsano = $anoIntencao ";
        $sql .= " ORDER BY $orderBy ";

        return $sql;
    }

    /**
     *
     * @param unknown $sequencialIntencao
     * @param unknown $anoIntencao
     * @return string
     */
    public static function sqlSelectIntencao($sequencialIntencao, $anoIntencao, $orgao = null) {
        $sql = "SELECT  DISTINCT    A.CINTRPSEQU, A.CINTRPSANO, A.TINTRPDLIM, A.XINTRPOBJE,
                                    A.XINTRPOBSE, A.FINTRPSITU, A.TINTRPDCAD, A.CUSUPOCODI,
                                    A.TINTRPULAT, OL.EORGLIDESC, UP.EUSUPORESP ";

            if (!is_null($orgao)) {
                $sql .= ", RI.TRINRPULAT ";
            }

        $sql .= "FROM  SFPC.TBINTENCAOREGISTROPRECO A
                        INNER JOIN SFPC.TBINTENCAORPORGAO IO ON A.CINTRPSEQU = IO.CINTRPSEQU AND A.CINTRPSANO = IO.CINTRPSANO
                        INNER JOIN SFPC.TBORGAOLICITANTE OL ON IO.CORGLICODI = OL.CORGLICODI ";

            if(!is_null($orgao)) {
                $sql .= 'LEFT JOIN SFPC.TBRESPOSTAINTENCAORP RI ON  A.CINTRPSEQU = RI.CINTRPSEQU AND  A.CINTRPSANO = RI.CINTRPSANO AND RI.CORGLICODI = ' . $orgao;
            }

        $sql .= "       LEFT JOIN SFPC.TBUSUARIOPORTAL UP ON RI.CUSUPOCODI = UP.CUSUPOCODI
                WHERE   A.CINTRPSEQU = $sequencialIntencao
                        AND A.CINTRPSANO = $anoIntencao ";

            if(!is_null($orgao)) {
                $sql .= "AND OL.CORGLICODI = " . $orgao;
            }

        return $sql;
    }

    /**
     *
     * @param unknown $sequencialIntencao
     * @param unknown $anoIntencao
     * @param unknown $sequencialItemIntencao
     * @return string
     */
    public static function sqlSelectOrgaosPorItem($sequencialIntencao, $anoIntencao, $sequencialItemIntencao)
    {
        $sql = "
            SELECT
                o.eorglidesc,
                sum(ir.airirpqtpr) AS airirpqtpr
            FROM
                sfpc.tbitemrespostaintencaorp ir
                INNER JOIN sfpc.tbrespostaintencaorp r ON r.cintrpsequ = ir.cintrpsequ AND r.cintrpsano = ir.cintrpsano AND r.corglicodi = ir.corglicodi
                INNER JOIN sfpc.tborgaolicitante o ON o.corglicodi = ir.corglicodi
            WHERE
                ir.cintrpsequ = $sequencialIntencao
                AND ir.cintrpsano = $anoIntencao
                AND ir.citirpsequ = $sequencialItemIntencao
                AND r.frinrpsitu = 'A'
            GROUP BY
            	o.eorglidesc
        ";

        return $sql;
    }
}

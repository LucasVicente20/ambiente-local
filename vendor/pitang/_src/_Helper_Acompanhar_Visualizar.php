<?php

/**
 * @author jfsi
 */
class Helper_Acompanhar_Visualizar
{
    public static function getValorEstimadoTRP($codigo, $tipo)
    {
        return Service::getValorEstimadoTRP($codigo, $tipo);
    }

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

    public static function sqlSelectItensIntencao($sequencialIntencao, $anoIntencao)
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
                ON RI.corglicodi = R.corglicodi AND RI.frinrpsitu = 'A'
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

    public static function sqlSelectOrgaosIntencao($sequencialIntencao, $anoIntencao, $ordem = 'nome')
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
        $sql .= " ORDER BY $orderBy ";
        
        return $sql;
    }

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

    public static function sqlSelectIntencao($sequencialIntencao, $anoIntencao)
    {
        $sql = ' SELECT DISTINCT a.cintrpsequ,  a.cintrpsano, a.tintrpdlim, a.xintrpobje, ';
        $sql .= '        a.xintrpobse, a.fintrpsitu, a.tintrpdcad, a.cusupocodi, a.tintrpulat ';
        $sql .= ' FROM ';
        $sql .= '        sfpc.tbintencaoregistropreco a ';
        $sql .= ' WHERE ';
        $sql .= "       a.cintrpsequ = $sequencialIntencao AND a.cintrpsano = $anoIntencao ";
        
        return $sql;
    }

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

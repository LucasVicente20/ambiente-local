<?php
#-----------------------------------------------------------------
# Alterado: Pitang Agile TI - Caio Coutinho
# Data: 04/09/2018
# Objetivo: Tarefa Redmine 201677
#-----------------------------------------------------------------
# Alterado : Osmar Celestino
# Data: 17/02/2022
# Objetivo: CR #259058
#---------------------------------------------------------------------------

/**
 * Sql para recuperar os dados da scc
 * 
 * @param $scc número da solicitação
 * @return String
 */
function sqlRecuperarDadosScc($scc) {
    $sql = ' SELECT sc.ccenposequ, sc.esolcoobse, sc.esolcoobje, sc.esolcojust, sc.asolcoanos,
                sc.ctpcomcodi, sc.tsolcodata, sc.clicpoproc, sc.alicpoanop, sc.ccomlicodi, sc.corglicod1, 
                sc.cgrempcodi, sc.dsolcodpdo, sc.ctpleitipo, sc.cleiponume, sc.cartpoarti, sc.cincpainci, 
                sc.fsolcorgpr, sc.fsolcorpcp, sc.fsolcocont, sc.csolcotipcosequ, arpn.carpnotiat, arpn.carpnosequ, 
                fc.aforcrccgc, fc.aforcrccpf, arpi.carpincodn, arpi.aarpinanon, arpe.carpexcodn, arpe.aarpexanon        
            FROM SFPC.TBsolicitacaocompra sc
            LEFT JOIN SFPC.TBataregistropreconova arpn 
                ON arpn.carpnosequ = sc.carpnosequ
            LEFT JOIN SFPC.TBitemsolicitacaocompra isc 
                ON isc.csolcosequ = sc.csolcosequ
            LEFT JOIN SFPC.TBfornecedorcredenciado fc 
                ON fc.aforcrsequ = isc.aforcrsequ
            LEFT JOIN SFPC.TBataregistroprecointerna arpi 
                ON arpi.carpnosequ = sc.carpnosequ
            LEFT JOIN SFPC.TBataregistroprecoexterna arpe 
                ON arpe.carpnosequ = sc.carpnosequ
            WHERE sc.csolcosequ = ' . $scc;
    
    return $sql;
}

/**
 * Sql para recuperar os dados do material
 * 
 * @param $material
 * @return String
 */
function sqlDadosMaterial($material) {
    $sql = ' SELECT M.EMATEPDESC,  U.EUNIDMSIGL, I.EITESCDESCMAT
        FROM  SFPC.TBMATERIALPORTAL M
            LEFT JOIN SFPC.TBUNIDADEDEMEDIDA U 
                ON U.CUNIDMCODI = M.CUNIDMCODI
            LEFT JOIN SFPC.TBITEMSOLICITACAOCOMPRA I 
                ON M.CMATEPSEQU = I.CMATEPSEQU
        WHERE
            M.CMATEPSEQU = %d
        GROUP BY
            M.EMATEPDESC,
            U.EUNIDMSIGL,
            I.EITESCDESCMAT
        ORDER BY
            M.EMATEPDESC,
            U.EUNIDMSIGL,
            I.EITESCDESCMAT ';

    return sprintf($sql, $material);
}

/**
 * Sql para recuperar os dados do usuário
 * 
 * @param $dataAtual
 * @return String
 */
function sqlDadosUsuario($dataAtual) {
    $sql = " SELECT USUCEN.FUSUCCTIPO, CENCUS.CORGLICODI
            FROM SFPC.TBUSUARIOCENTROCUSTO USUCEN, SFPC.TBCENTROCUSTOPORTAL CENCUS
            WHERE
                USUCEN.CCENPOSEQU = CENCUS.CCENPOSEQU
                AND USUCEN.FUSUCCTIPO IN ('C')
                AND (
                    (
                        USUCEN.CUSUPOCODI = " . $_SESSION['_cusupocodi_'] . '
                        AND USUCEN.CGREMPCODI = ' . $_SESSION['_cgrempcodi_'] . '
                    ) OR (
                        USUCEN.CUSUPOCOD1 = ' . $_SESSION['_cusupocodi_'] . ' AND
                        USUCEN.CGREMPCOD1 = ' . $_SESSION['_cgrempcodi_'] . " AND
                        '$dataAtual ' BETWEEN DUSUCCINIS AND DUSUCCFIMS
                    )
                ) AND USUCEN.FUSUCCTIPO = 'C'
                AND CENCUS.FCENPOSITU <> 'I'
            GROUP BY
                USUCEN.FUSUCCTIPO,
                CENCUS.CORGLICODI ";

    return $sql;
}

function sqlDadosScc($scc) {
    $sql = " SELECT 
                a.CPRESOSEQU AS numero, 
                a.APRESOANOE AS ano, 
                to_char(a.TPRESOGERA,'DD/MM/YYYY HH:MI') AS datahora,
                a.APRESONBLOQ AS bloqueio, 
                a.APRESOANOB AS anobloqueio,
                c.aforcrccgc AS cgc, 
                c.aforcrccpf AS cpf, 
                c.nforcrrazs AS razao, 
                a.CMOTNICODI AS idmotivo, 
                d.emotnidesc AS descricao, 
                a.apresonues AS numeroemp, 
                a.apresonues AS anoemp, 
                to_char(a.TPRESOULAT,'DD/MM/YYYY') AS datault,
                to_char(a.TPRESOIMPO,'DD/MM/YYYY') AS dataimportacao,
                to_char(a.DPRESOCSEM,'DD/MM/YYYY') AS datacancel,
                to_char(a.DPRESOGERE,'DD/MM/YYYY') AS datageracao,
                a.APRESONUES AS numemp,
                a.APRESOANES AS anoemp,
                to_char(a.DPRESOANUE,'DD/MM/YYYY') AS dataanulacao,
                a.VPRESOANUE AS valoranulado, 
                sum(b.VIPRESEMPN) AS soma 
        FROM  sfpc.tbpresolicitacaoempenho a 
        LEFT JOIN sfpc.tbfornecedorcredenciado c 
            ON c.aforcrsequ  = a.aforcrsequ
        LEFT JOIN sfpc.TBITEMPRESOLICITACAOEMPENHO b 
            ON (a.CPRESOSEQU  = b.CPRESOSEQU AND  a.APRESOANOE=b.APRESOANOE )
        LEFT JOIN sfpc.tbmotivonaoimportacao d 
            ON d.cmotnicodi = a.cmotnicodi
     WHERE  a.CSOLCOSEQU = %d AND 
          a.CPRESOSEQU = b.CPRESOSEQU 
        AND a.APRESOANOE = b.APRESOANOE 
        AND a.aforcrsequ = c.aforcrsequ 
        GROUP BY 1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20  ";

    return sprintf($sql, $scc);
}

function verificarTipoControle($ata) {
    $sql = "
        SELECT arpn.farpnotsal 
        FROM sfpc.tbataregistropreconova arpn
        WHERE arpn. carpnosequ = %d";

    $resultado = resultLinhaUnica(executarSQL(ClaDatabasePostgresql::getConexao(), sprintf($sql, $ata)));

    return $resultado;
}
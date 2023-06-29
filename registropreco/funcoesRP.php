<?php
# ------------------------------------------------------------------------------------------------------
# Prefeitura do Recife
# Portal de Compras
# Programa: funcoesRP.php
# Autor: Pitang Agile TI - Caio Coutinho
# Data: 30/11/2018
# Objetivo: Tarefa #207373
# ------------------------------------------------------------------------------------------------------
# Alterado: Caio Coutinho - Pitang Agile TI
# Data:     19/12/2018
# Objetivo: Tarefa Redmine 206574
# ------------------------------------------------------------------------------------------------------
# Alterado: Lucas Baracho
# Data:     13/05/2019
# Objetivo: Tarefa Redmine 216434
# ------------------------------------------------------------------------------------------------------
# Alterado: Lucas Vicente 
# Data: 22/07/2022
# Obejtivo CR 231271

// 220038--

function getQtdTotalOrgaoCaronaInterna($db, $centroCusto = null, $ata, $item, $field = 'aitescqtso', $valorOrgao = null) {
    if(!is_null($centroCusto)) {
        $orgao = getOrgaoCentroCusto($db, $centroCusto);
    } else if(!is_null($valorOrgao)) {
        $orgao = $valorOrgao;
    }

    $sql = "SELECT SUM(COALESCE(ISC.$field,0)) AS qtdtotal FROM";
    $sql .= "   SFPC.TBITEMSOLICITACAOCOMPRA ISC  ";
    $sql .= "   INNER JOIN SFPC.TBSOLICITACAOCOMPRA SC ON ISC.CSOLCOSEQU = SC.CSOLCOSEQU"; 
    $sql .= "   INNER JOIN SFPC.TBITEMATAREGISTROPRECONOVA IARPN ON ISC.CARPNOSEQU = IARPN.CARPNOSEQU
                    AND ISC.CITARPSEQU = IARPN.CITARPSEQU";     
    $sql .= "   WHERE 1=1 ";
    $sql .= "   AND ISC.CARPNOSEQU = " . $ata;
    $sql .= "   AND ISC.CITARPSEQU = " . $item;
    $sql .= "   AND SC.FSOLCORPCP = 'C' ";
    $sql .= "   AND SC.FSOLCOAUTC= 'S'  ";
    if(!is_null($centroCusto) || !is_null($valorOrgao)) {
        $sql .= "    AND  SC.CORGLICODI = " .$orgao;
    }
    $sql .= "   AND IARPN.FITARPSITU = 'A' ";
    
    $linha = resultLinhaUnica(executarPGSQL($sql));
    $resultado = is_null($linha[0]) ? 0 : $linha[0];

    return $resultado;             
}

function getQtdTotalOrgaoCaronaInternaInclusaoDireta($db, $ata, $item, $centroCusto = null, $field = 'aitcrpqtut', $valorOrgao = null) {
    if(!is_null($centroCusto)) {
        $orgao = getOrgaoCentroCusto($db, $centroCusto);
    } else if(!is_null($valorOrgao)) {
        $orgao = $valorOrgao;
    }

    $sql = "SELECT SUM(COALESCE(ICIARP.$field,0)) AS qtdtotal FROM";
    $sql .= "   SFPC.TBITEMCARONAINTERNAATARP ICIARP  ";
    $sql .= "   INNER JOIN SFPC.TBCARONAINTERNAATARP CIARP
                    ON ICIARP.CARPNOSEQU = CIARP.CARPNOSEQU
                    AND ICIARP.CORGLICODI = CIARP.CORGLICODI"; 
    $sql .= "   INNER JOIN SFPC.TBITEMATAREGISTROPRECONOVA IARPN
                    ON ICIARP.CARPNOSEQU = IARPN.CARPNOSEQU
                    AND ICIARP.CITARPSEQU = IARPN.CITARPSEQU";     
    $sql .= "   WHERE 1=1 ";
    $sql .= "   AND ICIARP.CARPNOSEQU = " . $ata;
    $sql .= "   AND ICIARP.CITARPSEQU = " . $item;
    $sql .= "   AND ICIARP.FITCRPSITU = 'A' ";
    $sql .= "   AND CIARP.FCARRPSITU = 'A' ";
    $sql .= "   AND IARPN.FITARPSITU = 'A'  ";

    if(!is_null($centroCusto) || !is_null($valorOrgao)) {
        $sql .= "    AND  CIARP.CORGLICODI = " .$orgao;
    }

    $linha = resultLinhaUnica(executarPGSQL($sql));
    $resultado = is_null($linha[0]) ? 0 : $linha[0];

    return $resultado;             
}

function getQtdTotalItensAta($db, $ata, $item) {
    $sql = "   SELECT  iarpn.aitarpqtat, iarpn.aitarpqtor ";
    $sql .= "   FROM    sfpc.tbitemataregistropreconova iarpn ";
    $sql .= "   WHERE   iarpn.fitarpsitu = 'A' ";
    $sql .= "   and iarpn.carpnosequ = " . $ata;
    $sql .= "   and iarpn.citarpsequ = " . $ata;
    $linha = resultLinhaUnica(executarSQL($db, $sql));
    $resultado = is_null($linha[0]) ? 0 : $linha;

    if(is_array($resultado)) {
        $resultado = (int) $resultado[0] != 0 ? (int) $resultado[0] : (int) $resultado[1];
    }

    return $resultado;
}

function getQtdTotalOrgaoParticipanteInterna($db, $centroCusto = null, $ata, $item, $field_1 = 'apiarpqtut', $field_2 = 'aitescqtso', $valorOrgao = null) {
    if(!is_null($centroCusto)) {
        $orgao = getOrgaoCentroCusto($db, $centroCusto);
    } else if(!is_null($valorOrgao)) {
        $orgao = $valorOrgao;
    }

    $sql = "SELECT SUM(COALESCE(ITEMS.$field_2,0)) AS qtdtotalorgaoparticipante FROM ";
    $sql .= "   SFPC.TBITEMATAREGISTROPRECONOVA ITEMA  ";
    $sql .= "   LEFT JOIN SFPC.TBPARTICIPANTEITEMATARP ITEMPA ON ITEMA.CARPNOSEQU = ITEMPA.CARPNOSEQU and ITEMA.CITARPSEQU = ITEMPA.CITARPSEQU";
    $sql .= "   LEFT JOIN SFPC.TBSOLICITACAOCOMPRA ITEMSC ON ITEMA.CARPNOSEQU = ITEMSC.CARPNOSEQU  ";    // TBSOLICITACAOCOMPRA
    $sql .= "   LEFT JOIN SFPC.TBITEMSOLICITACAOCOMPRA ITEMS ON ITEMSC.CSOLCOSEQU = ITEMS.CSOLCOSEQU AND ITEMA.CITARPSEQU = ITEMS.CITARPSEQU "; // TBITEMSOLICITACAOCOMPRA
    $sql .= "   WHERE 1=1 AND ITEMA.CARPNOSEQU = " . $ata;
    $sql .= "   AND ITEMA.CITARPSEQU = " . $item;
    $sql .= "   AND ITEMSC.csitsocodi IN (3,4,5) ";
    $sql .= "   AND ITEMPA.fpiarpsitu = 'A' ";
    $sql .= "   AND ITEMSC.fsolcorpcp = 'P' ";

    if(!is_null($centroCusto) || !is_null($valorOrgao)) {
        $sql .= "   AND ITEMPA.CORGLICODI = ".$orgao;
    }

    $linha = resultLinhaUnica(executarSQL($db, $sql));
    $resultado = is_null($linha[0]) ? 0 : $linha[0];

    // SCC
    $sql2 = " SELECT $field_1 from sfpc.tbparticipanteitematarp ";
    $sql2 .= "    WHERE CARPNOSEQU = " . $ata;
    $sql2 .= "    AND CITARPSEQU = " . $item;
    if(!is_null($centroCusto) || !is_null($valorOrgao)) {
        $sql2 .= "   AND CORGLICODI = ".$orgao;
    }

    $linha2 = resultLinhaUnica(executarSQL($db, $sql2));
    $resultado2 = is_null($linha2[0]) ? 0 : $linha2[0];

    $resultado = is_null($linha2[0]) ? $resultado : ($resultado + $resultado2);

    return $resultado;             
}

function getQtdTotalOrgaoParticipanteInternaScc($db, $centroCusto = null, $ata, $item, $field_1 = 'apiarpqtut', $field_2 = 'aitescqtso', $valorOrgao = null) {
    if(!is_null($centroCusto)) {
        $orgao = getOrgaoCentroCusto($db, $centroCusto);
    } else if(!is_null($valorOrgao)) {
        $orgao = $valorOrgao;
    }

    $sql = "SELECT SUM(COALESCE(ITEMS.$field_2,0)) AS qtdtotalorgaoparticipante FROM ";
    $sql .= "   SFPC.TBITEMATAREGISTROPRECONOVA ITEMA  ";
    $sql .= "   LEFT JOIN SFPC.TBPARTICIPANTEITEMATARP ITEMPA ON ITEMA.CARPNOSEQU = ITEMPA.CARPNOSEQU and ITEMA.CITARPSEQU = ITEMPA.CITARPSEQU";
    $sql .= "   LEFT JOIN SFPC.TBSOLICITACAOCOMPRA ITEMSC ON ITEMA.CARPNOSEQU = ITEMSC.CARPNOSEQU  ";    // TBSOLICITACAOCOMPRA
    $sql .= "   LEFT JOIN SFPC.TBITEMSOLICITACAOCOMPRA ITEMS ON ITEMSC.CSOLCOSEQU = ITEMS.CSOLCOSEQU AND ITEMA.CITARPSEQU = ITEMS.CITARPSEQU "; // TBITEMSOLICITACAOCOMPRA
    $sql .= "   WHERE 1=1 AND ITEMA.CARPNOSEQU = " . $ata;
    $sql .= "   AND ITEMA.CITARPSEQU = " . $item;
    $sql .= "   AND ITEMSC.csitsocodi IN (3,4,5) ";
    $sql .= "   AND ITEMPA.fpiarpsitu = 'A' ";
    $sql .= "   AND ITEMSC.fsolcorpcp = 'P' ";

    if(!is_null($centroCusto) || !is_null($valorOrgao)) {
        $sql .= "   AND ITEMPA.CORGLICODI = ".$orgao;
    }

    $linha = resultLinhaUnica(executarSQL($db, $sql));
    $resultado = is_null($linha[0]) ? 0 : $linha[0];    

    return $resultado;             
}

// ALTERADO
function getQtdTotalOrgaoCaronaExterna($db, $ata, $item, $field = 'acoeitqtat') {
    $sql = "  SELECT SUM(COALESCE(coei.$field,0)) as qtdtotal ";
    $sql .= "  FROM	sfpc.tbcaronaorgaoexternoitem COEI ";
    $sql .= "   INNER JOIN  sfpc.tbitemataregistropreconova IARPN  ";
    $sql .= "       ON  COEI.carpnosequ = IARPN.carpnosequ  ";
    $sql .= "           AND COEI.citarpsequ = IARPN.citarpsequ  ";
    $sql .= "  WHERE	";
    $sql .= "  COEI.carpnosequ = " . $ata;
    $sql .= "  AND COEI.citarpsequ = " . $item;        
    $sql .= "  AND IARPN.fitarpsitu = 'A'";        

    $linha = resultLinhaUnica(executarPGSQL($sql));
    $resultado = is_null($linha[0]) ? 0 : $linha[0];

    return $resultado; 
            
}

function getTotalQtdCaronaInternaAtaExterna($db, $centroCusto, $ata, $item, $field = 'AITESCQTSO', $valorOrgao = null) {
    if(!is_null($centroCusto)) {
        $orgao = getOrgaoCentroCusto($db, $centroCusto);
    } else if(!is_null($valorOrgao)) {
        $orgao = $valorOrgao;
    }

    $sql = "  SELECT SUM(COALESCE(ITEMS.$field,0)) as qtdtotal   ";
    $sql .= "  FROM SFPC.TBITEMATAREGISTROPRECONOVA ITEMA   ";
    $sql .= "  LEFT JOIN (SFPC.TBITEMSOLICITACAOCOMPRA ITEMS INNER JOIN SFPC.TBSOLICITACAOCOMPRA SOLCO  ";
    $sql .= "  ON ITEMS.CSOLCOSEQU = SOLCO.CSOLCOSEQU AND SOLCO.CTPCOMCODI = 5  ";
    $sql .= "  AND SOLCO.FSOLCORPCP = 'C'";
    $sql .= "  AND SOLCO.CSITSOCODI IN (3,4))   ";
    $sql .= "  ON ITEMA.CARPNOSEQU = ITEMS.CARPNOSEQU AND ITEMS.CITARPSEQU= ITEMA.CITARPSEQU    ";
    $sql .= "  WHERE 1=1    ";
    $sql .= "  AND ITEMA.CARPNOSEQU = " . $ata;
    $sql .= "  AND ITEMA.CITARPSEQU = " . $item;
    
    if(!is_null($centroCusto) || !is_null($valorOrgao)) {
        $sql .= "  AND SOLCO.CORGLICODI = " . $orgao;
    }

    $linha = resultLinhaUnica(executarSQL($db, $sql));
    $resultado = is_null($linha[0]) ? 0 : $linha[0];

    return $resultado; 
}

function getFatorQtdMaxCarona($db) {
    $sql = 'SELECT p.qpargecaro FROM sfpc.tbparametrosgerais p';

    $linha = resultLinhaUnica(executarSQL($db, $sql));
    $resultado = is_null($linha[0]) ? 1 : $linha[0];

    return $resultado;
}

function verificarAtaCorporativa($db, $ata) {
    $sql = 'SELECT farpincorp FROM sfpc.tbataregistroprecointerna WHERE carpnosequ = ' . $ata;
    $linha = resultLinhaUnica(executarSQL($db, $sql));

    return !is_null($linha[0]) ? $linha[0] : 'N';
}

function getPercentualAdesao($db, $corporativa) {
    $sql = 'SELECT qpargepagc, qpargepacc FROM sfpc.tbparametrosgerais';
    $linha = resultLinhaUnica(executarSQL($db, $sql));
    $valor = ($corporativa == 'S') ? $linha[0] : $linha[1];

    return $valor/100;
}

function getOrgaoCentroCusto($db, $centroCusto) {
    $sql = "SELECT CCPORT.CCENPOSEQU, CCPORT.CORGLICODI ";
    $sql .= "   FROM SFPC.TBCENTROCUSTOPORTAL CCPORT ";
    $sql .= "   WHERE CCPORT.CCENPOSEQU = " . $centroCusto;

    $linha = resultLinhaUnica(executarSQL($db, $sql));
    $resultado = $linha[1];
    
    return $resultado; 
}

# scc's do participante na ata anterior
function sccParticipanteAtaAnterior($db, $ata, $item, $orgao = null) {

    $sql = "  SELECT SUM(isc.aitescqtso) ";
    $sql .= " FROM sfpc.tbitemsolicitacaocompra isc ";
    $sql .= " INNER JOIN sfpc.tbsolicitacaocompra sc ON ";
    $sql .= "   sc.csolcosequ = isc.csolcosequ ";
    $sql .= "   AND sc.carpnosequ = isc.carpnosequ ";
    $sql .= " INNER JOIN sfpc.tbataregistroprecointerna arpi ON ";
    $sql .= "   arpi.carpnosequ = isc.carpnosequ ";
    $sql .= "   AND arpi.carpnosequ = sc.carpnosequ ";
    $sql .= " WHERE arpi.carpnoseq1 = " . $ata;
    $sql .= "   AND isc.citarpsequ = " .$item;

    if(!is_null($orgao)) {
        $sql .= "   AND sc.corglicodi = " . $orgao;
    }

    $sql .= "   AND sc.fsolcorpcp = 'P' ";

    $linha = resultLinhaUnica(executarSQL($db, $sql));
    $resultado = $linha[0];

    return $resultado;
}

# scc's do carona interno na ata anterior
function sccCaronaInternoAtaAnterior($db, $ata, $item, $orgao = null) {

    $sql = "  SELECT sum(isc.aitescqtso) ";
    $sql .= " FROM sfpc.tbitemsolicitacaocompra isc ";
    $sql .= " LEFT JOIN sfpc.tbsolicitacaocompra sc ON ";
    $sql .= "   sc.csolcosequ = isc.csolcosequ ";
    $sql .= "   AND sc.carpnosequ = isc.carpnosequ ";
    $sql .= " LEFT JOIN sfpc.tbataregistroprecointerna arpi ON ";
    $sql .= "   arpi.carpnosequ = isc.carpnosequ ";
    $sql .= "   AND arpi.carpnosequ = sc.carpnosequ ";
    $sql .= " WHERE arpi.carpnoseq1 = " . $ata;
    $sql .= "   AND isc.citarpsequ = " . $item;
    $sql .= "   AND sc.fsolcorpcp = 'C' ";
    $sql .= "   AND sc.fsolcoautc = 'S' ";

    if(!is_null($orgao)) {
        $sql .= "   AND sc.corglicodi = " . $orgao;
    }

    $linha = resultLinhaUnica(executarSQL($db, $sql));
    $resultado = $linha[0];

    return $resultado;
}

# todas as scc's de caronas internas na ata anterior
function todasSccsCaronaInternoAtaAnterior($db) {

    $sql = "  SELECT isc.csolcosequ, SUM(isc.aitescqtso) ";
    $sql .= " FROM sfpc.tbitemsolicitacaocompra isc ";
    $sql .= " LEFT JOIN sfpc.tbsolicitacaocompra sc ON ";
    $sql .= "   sc.csolcosequ = isc.csolcosequ ";
    $sql .= "   AND sc.carpnosequ = isc.carpnosequ ";
    $sql .= " LEFT JOIN sfpc.tbataregistroprecointerna arpi ON ";
    $sql .= "   arpi.carpnosequ = isc.carpnosequ ";
    $sql .= "   AND arpi.carpnosequ = sc.carpnosequ ";
    $sql .= " WHERE arpi.carpnoseq1 = 44 ";
    $sql .= "   AND isc.citarpsequ = 1 ";
    $sql .= "   AND sc.fsolcorpcp = 'C' ";
    $sql .= "   AND sc.fsolcoautc = 'S' ";
    $sql .= " GROUP BY isc.csolcosequ ";

    $linha = resultLinhaUnica(executarSQL($db, $sql));
    $resultado = $linha[1];

    return $resultado;
}

# caronas externas na ata anterior
function caronaExternaAtaAnterior($db, $ata, $item) {

    $sql = "  SELECT SUM(coei.acoeitqtat) ";
    $sql .= " FROM sfpc.tbcaronaorgaoexternoitem coei ";
    $sql .= " INNER JOIN sfpc.tbataregistroprecointerna arpi ON ";
    $sql .= "   arpi.carpnosequ = coei.carpnosequ ";
    $sql .= " WHERE arpi.carpnoseq1 = " . $ata;
    $sql .= "   AND coei.citarpsequ = " . $item;

    $linha = resultLinhaUnica(executarSQL($db, $sql));
    $resultado = $linha[0];

    return $resultado;
}

# inclusão direta do órgão carona interno
function inclusaoDiretaOrgaoCaronaInterno($db, $ata, $item, $orgao = null) {
    $sql = "  SELECT SUM(iciarp.aitcrpqtut) ";
    $sql .= " FROM sfpc.tbitemcaronainternaatarp iciarp ";
    $sql .= " LEFT JOIN sfpc.tbataregistroprecointerna arpi ON ";
    $sql .= "   iciarp.carpnosequ = arpi.carpnosequ ";
    $sql .= " WHERE arpi.carpnoseq1 = " . $ata;
    $sql .= "   AND iciarp.citarpsequ = " . $item;

    if(!is_null($orgao)) {
        $sql .= "   AND iciarp.corglicodi = " . $orgao;
    }

    $sql .= " GROUP BY iciarp.carpnosequ; ";

    $linha = resultLinhaUnica(executarSQL($db, $sql));
    $resultado = $linha[0];

    return $resultado;
}

# todas os caronas internos por inclusão direta
function caronasInternasInclusaoDireta($db) {
    $sql = "  SELECT iciarp.carpnosequ, SUM(iciarp.aitcrpqtut) ";
    $sql .= " FROM sfpc.tbitemcaronainternaatarp iciarp ";
    $sql .= " LEFT JOIN sfpc.tbataregistroprecointerna arpi ON ";
    $sql .= "   iciarp.carpnosequ = arpi.carpnosequ ";
    $sql .= " WHERE arpi.carpnoseq1 = 32 ";
    $sql .= "   AND iciarp.citarpsequ = 2 ";
    $sql .= " GROUP BY iciarp.carpnosequ; ";

    $linha = resultLinhaUnica(executarSQL($db, $sql));
    $resultado = $linha[1];

    return $resultado;
}

# ------------------------------------------------------------- #

function selectTipoControle() {
    return array(
        '0' => 'SALDO POR QUANTIDADE',
        '1' => 'SALDO POR VALOR',
        '2' => 'SALDO POR QUANTIDADE COM VALOR VARIÁVEL'
    );
}

function tipoControle($tipo) {
    $tipoControle = selectTipoControle();
    $tipo = is_null($tipo) ? 0 : $tipo;

    return $tipoControle[$tipo];
}

function valorItemAta($db, $ata, $item, $field_1 = 'AITARPQTAT', $field_2 = 'AITARPQTOR') {
    $sql = "  SELECT IARPN.$field_1, IARPN.$field_2
              FROM SFPC.TBITEMATAREGISTROPRECONOVA IARPN
              WHERE IARPN.CARPNOSEQU = " . $ata . " 
              AND IARPN.CITARPSEQU = " . $item;
    $linha = resultLinhaUnica(executarSQL($db, $sql));
    
    if(!is_null($linha[0]) && $linha[0] != 0) {
        $valorItemAta = $linha[0];
    } else {
        $valorItemAta = $linha[1];
    }

    return $valorItemAta;
}

function ataAnterior($db, $ata) {
    $sql = "  SELECT ARPI.CARPNOSEQU
              FROM SFPC.TBATAREGISTROPRECOINTERNA ARPI
              WHERE ARPI.CARPNOSEQ1 = ". $ata;
    
    $linha = resultLinhaUnica(executarSQL($db, $sql));
    $resultado = $linha[0];

    return $resultado;
}

function solicitadoParticipante($db, $ata, $item, $field='APIARPQTAT', $orgaoParticipante) {
    $sql = "  SELECT PIARP.$field
              FROM SFPC.TBPARTICIPANTEITEMATARP PIARP
              WHERE PIARP.CARPNOSEQU = $ata
              AND PIARP.CITARPSEQU = $item
              AND PIARP.CORGLICODI = " . $orgaoParticipante ;

    $linha = resultLinhaUnica(executarSQL($db, $sql));
    $resultado = $linha[0];

    return $resultado;
}

function utilizadoParticipanteAtaAtual($db, $ata, $item, $field='APIARPQTUT', $orgaoParticipante = null) {
    $sql  = " SELECT SUM(PIARP.$field) ";
    $sql .= "            FROM SFPC.TBPARTICIPANTEITEMATARP PIARP";
    $sql .= "   WHERE PIARP.CARPNOSEQU = $ata ";
    $sql .= "         AND PIARP.CITARPSEQU = " . $item;

    if(!empty($orgaoParticipante)) {
        $sql .= "         AND PIARP.CORGLICODI = " . $orgaoParticipante;
    }

    $linha = resultLinhaUnica(executarSQL($db, $sql));
    $utilizadoIncDir = $linha[0];

    return $utilizadoIncDir;
}

function utilizadoParticipanteAtaAtualScc($db, $ata, $item, $field='AITESCQTSO', $orgaoParticipante = null) {
    $sql  = "  SELECT SUM (ISC.$field) ";
    $sql .= "            FROM SFPC.TBITEMSOLICITACAOCOMPRA ISC ";
    $sql .= "            LEFT JOIN SFPC.TBSOLICITACAOCOMPRA SC ON ISC.CSOLCOSEQU = SC.CSOLCOSEQU ";
    $sql .= "                WHERE ISC.CARPNOSEQU = $ata ";
    $sql .= "                AND ISC.CITARPSEQU = $item  ";
    $sql .= "                AND SC.FSOLCORPCP = 'P' ";
    $sql .= "                AND SC.CSITSOCODI IN (3, 4, 5)";

    if(!empty($orgaoParticipante)) {
        $sql .= "                AND SC.CORGLICODI = $orgaoParticipante ";
    }

    $linha = resultLinhaUnica(executarSQL($db, $sql));
    $utilizadoScc = $linha[0];

    /*print_r($sql);
    echo "</br>";
    echo "</br>";
    echo "</br>";*/

    return $utilizadoScc;
}

function utilizadoParticipanteAtaAnterior($db, $ata, $item, $field='APIARPQTUT', $orgaoParticipante = null) {
    $sql = "  SELECT SUM(PIARP.$field) ";
    $sql .= "            FROM SFPC.TBPARTICIPANTEITEMATARP PIARP ";
    $sql .= "            WHERE PIARP.CARPNOSEQU = $ata ";
    $sql .= "               AND PIARP.CITARPSEQU = $item ";

    if(!empty($orgaoParticipante)) {
        $sql .= "               AND PIARP.CORGLICODI = " . $orgaoParticipante;
    }

    $linha = resultLinhaUnica(executarSQL($db, $sql));
    $utilizadoIncDirAnterior = $linha[0];

    return $utilizadoIncDirAnterior;
}


function utilizadoParticipanteAtaAnteriorScc($db, $ata, $item, $field='AITESCQTSO', $orgaoParticipante = null) {
    $sql = "  SELECT SUM (ISC.$field) ";
    $sql .= "            FROM SFPC.TBITEMSOLICITACAOCOMPRA ISC ";
    $sql .= "            LEFT JOIN SFPC.TBSOLICITACAOCOMPRA SC ON ISC.CSOLCOSEQU = SC.CSOLCOSEQU ";
    $sql .= "            WHERE ISC.CARPNOSEQU = $ata ";
    $sql .= "                   AND ISC.CITARPSEQU = $item  ";
    $sql .= "                   AND SC.FSOLCORPCP = 'P' ";
    $sql .= "                   AND SC.CSITSOCODI IN (3, 4, 5)";

    if(!empty($orgaoParticipante)) {
        $sql .= "                   AND SC.CORGLICODI = $orgaoParticipante ";
    }

    $linha = resultLinhaUnica(executarSQL($db, $sql));
    $utilizadoSccAnterior = $linha[0];

    return $utilizadoSccAnterior;
}

function utilizadoParticipanteTotal($db, $ata, $item, $field_d='APIARPQTUT', $field_scc='AITESCQTSO', $orgaoParticipante = null) {
    $utilizadoParticipanteAtaAtual = utilizadoParticipanteAtaAtual($db, $ata, $item, $field_d, $orgaoParticipante);
    $utilizadoParticipanteAtaAtualScc = utilizadoParticipanteAtaAtualScc($db, $ata, $item, $field_scc, $orgaoParticipante);

    $utilizadoParticipanteAtaAnterior = utilizadoParticipanteAtaAnterior($db, $ata, $item, $field_d, $orgaoParticipante);
    $utilizadoParticipanteAtaAnteriorScc = utilizadoParticipanteAtaAnteriorScc($db, $ata, $item, $field_scc, $orgaoParticipante);

    return $utilizadoParticipanteAtaAtual + $utilizadoParticipanteAtaAtualScc + $utilizadoParticipanteAtaAnterior + $utilizadoParticipanteAtaAnteriorScc;
}

function saldoParticipante($db, $ata, $item, $field_d='APIARPQTUT', $field_scc='AITESCQTSO', $orgaoParticipante) {    
    $saldoParticipante = 0;
    $sql = "  SELECT PARP.FPATRPSITU
              FROM SFPC.TBPARTICIPANTEATARP PARP
              WHERE PARP.CARPNOSEQU = $ata
              AND PARP.CORGLICODI = " . $orgaoParticipante ;
    $sql .= " AND PARP.CITARPSEQU = " . $item ;

    $linha = resultLinhaUnica(executarSQL($db, $sql));
    $situacaoParticipante = $linha[0];

    if($situacaoParticipante == 'A') {
        $solicitadoParticipante     = solicitadoParticipante($db, $ata, $item, $field_d, $orgaoParticipante);
        $utilizadoParticipanteTotal = utilizadoParticipanteTotal($db, $ata, $item, $field_d, $field_scc, $orgaoParticipante);
        $saldoParticipante = $solicitadoParticipante - $utilizadoParticipanteTotal;
    }

    return $saldoParticipante;
}

function valorMaximoCarona($db, $ata, $item, $field_1 = 'AITARPQTAT', $field_2 = 'AITARPQTOR', $corporativa = 'N') {
    $valorItemAta = valorItemAta($db, $ata, $item, $field_1, $field_2);
    $percentualAdesao = getPercentualAdesao($db, $corporativa);
    $valorItemAta *= $percentualAdesao;

    $sql = "  SELECT PG.QPARGECARO, PG.QPARGECAR1 FROM SFPC.TBPARAMETROSGERAIS PG ";

    $linha = resultLinhaUnica(executarSQL($db, $sql));

    if($corporativa == 'S') {
        $fatorCarona = $linha[1];
    } else {
        $fatorCarona = $linha[0];
    }

    $valorMaximoCarona = $valorItemAta * $fatorCarona;

    return $valorMaximoCarona;;
}

function utilizadoCaronaInclusaoDiretaGeralAtaAtual($db, $ata, $item, $field='AITCRPQTUT') {
    $sql = "  SELECT SUM(ICIARP.$field)
              FROM SFPC.TBITEMCARONAINTERNAATARP ICIARP
              WHERE ICIARP.CARPNOSEQU = $ata
              AND ICIARP.CITARPSEQU = $item";

    $linha = resultLinhaUnica(executarSQL($db, $sql));
    $utilizadoIncDirGeralAtual = $linha[0];

    return $utilizadoIncDirGeralAtual;
}

function UtilizadoCaronaInclusaoDiretaGeralAtaAnterior($db, $ata, $item, $field='AITCRPQTUT') {
    $ataAnterior = ataAnterior($db, $ata);
    $utilizadoIncDirGeralAnterior = 0;

    if(!empty($ataAnterior)) {
        $sql = "  SELECT SUM(ICIARP.$field)
                  FROM SFPC.TBITEMCARONAINTERNAATARP ICIARP
                  WHERE ICIARP.CARPNOSEQU = $ataAnterior
                  AND ICIARP.CITARPSEQU = $item";

        $linha = resultLinhaUnica(executarSQL($db, $sql));
        $utilizadoIncDirGeralAnterior = $linha[0];
    }

    return $utilizadoIncDirGeralAnterior;
}

function utilizadoCaronaSccGeralAtaAtual($db, $ata, $item, $field='AITESCQTSO') {
    $sql = "  SELECT SUM (ISC.$field)
              FROM SFPC.TBITEMSOLICITACAOCOMPRA ISC
              LEFT JOIN SFPC.TBSOLICITACAOCOMPRA SC ON ISC.CSOLCOSEQU = SC.CSOLCOSEQU
              WHERE ISC.CARPNOSEQU = $ata
              AND ISC.CITARPSEQU = $item
              AND SC.FSOLCORPCP = 'C'
              AND SC.FSOLCOAUTC = 'S' ";

    $linha = resultLinhaUnica(executarSQL($db, $sql));
    $utilizadoSccAutorizadoAtual = $linha[0];

    return $utilizadoSccAutorizadoAtual;
}

function utilizadoCaronaSccGeralAtaAnterior($db, $ata, $item, $field='AITESCQTSO') {
    $ataAnterior = ataAnterior($db, $ata);
    $utilizadoSccAutorizadoAnterior = 0;

    if(!empty($ataAnterior)) {
        $sql = "  SELECT SUM (ISC.$field)
              FROM SFPC.TBITEMSOLICITACAOCOMPRA ISC
              LEFT JOIN SFPC.TBSOLICITACAOCOMPRA SC ON ISC.CSOLCOSEQU = SC.CSOLCOSEQU
              WHERE ISC.CARPNOSEQU = $ataAnterior
              AND ISC.CITARPSEQU = $item
              AND SC.FSOLCORPCP = 'C'
              AND SC.FSOLCOAUTC = 'S' ";

        $linha = resultLinhaUnica(executarSQL($db, $sql));
        $utilizadoSccAutorizadoAnterior = $linha[0];
    }

    return $utilizadoSccAutorizadoAnterior;
}

function utilizadoCaronaOrgaoExternoGeralAtaAtual($db, $ata, $item, $field='ACOEITQTAT') {
    $sql = "  SELECT SUM(COEI.$field)
              FROM SFPC.TBCARONAORGAOEXTERNOITEM COEI
              WHERE COEI.CARPNOSEQU = " . $ata;
    $sql .= " AND COEI.CITARPSEQU = " . $item;

    $linha = resultLinhaUnica(executarSQL($db, $sql));
    $utilizadoCaronaOrgaoExternoGeralAtual = $linha[0];

    return $utilizadoCaronaOrgaoExternoGeralAtual;
}

function utilizadoCaronaOrgaoExternoGeralAtaAnterior($db, $ata, $item, $field='ACOEITQTAT') {
    $ataAnterior = ataAnterior($db, $ata);
    $utilizadoCaronaOrgaoExternoGeralAnterior = 0;

    if(!empty($ataAnterior)) {
        $sql = "  SELECT SUM(COEI.$field)
              FROM SFPC.TBCARONAORGAOEXTERNOITEM COEI
              WHERE COEI.CARPNOSEQU = " . $ataAnterior;
        $sql .= " AND COEI.CITARPSEQU = " . $item;

        $linha = resultLinhaUnica(executarSQL($db, $sql));
        $utilizadoCaronaOrgaoExternoGeralAnterior = $linha[0];
    }

    return $utilizadoCaronaOrgaoExternoGeralAnterior;
}

function utilizadoCaronaGeral($db, $ata, $field_d='AITCRPQTUT', $field_scc='AITESCQTSO', $field_c='ACOEITQTAT') {
    // Inclusão direta
    $utilizadoCaronaInclusaoDiretaGeralAtaAtual = utilizadoCaronaInclusaoDiretaGeralAtaAtual($db, $ata, $field_d);
    $utilizadoCaronaInclusaoDiretaGeralAtaAnterior = utilizadoCaronaInclusaoDiretaGeralAtaAnterior($db, $ata, $field_d);

    // Scc
    $utilizadoCaronaSccGeralAtaAtual = utilizadoCaronaSccGeralAtaAtual($db, $ata, $field_scc);
    $utilizadoCaronaSccGeralAtaAnterior = utilizadoCaronaSccGeralAtaAnterior($db, $ata, $field_scc);

    // Carona
    $utilizadoCaronaOrgaoExternoGeralAtaAtual = utilizadoCaronaOrgaoExternoGeralAtaAtual($db, $ata, $field_c);
    $utilizadoCaronaOrgaoExternoGeralAtaAnterior = utilizadoCaronaOrgaoExternoGeralAtaAnterior($db, $ata, $field_c);

    $utilizadoCaronaGeral = ($utilizadoCaronaInclusaoDiretaGeralAtaAtual +
                            $utilizadoCaronaInclusaoDiretaGeralAtaAnterior +
                            $utilizadoCaronaSccGeralAtaAtual +
                            $utilizadoCaronaSccGeralAtaAnterior +
                            $utilizadoCaronaOrgaoExternoGeralAtaAtual +
                            $utilizadoCaronaOrgaoExternoGeralAtaAnterior);

    return $utilizadoCaronaGeral;
}

function saldoGeralCarona($db, $ata, $item, $field_1 = 'AITARPQTOR', $field_2 = 'AITARPQTAT', $field_3='AITCRPQTUT', $field_4='AITESCQTSO', $field_5='ACOEITQTAT') {
    $valorMaximoCarona = valorMaximoCarona($db, $ata, $item, $field_1, $field_2);
    $utilizadoCaronaGeral = utilizadoCaronaGeral($db, $ata, $field_3, $field_4, $field_5);
    $saldoGeralCarona = $valorMaximoCarona - $utilizadoCaronaGeral;

    return $saldoGeralCarona;
    
}

function utilizadoCaronaInclusaoDiretaOrgaoAtaAtual($db, $ata, $item, $field='AITCRPQTUT', $orgaoCaronaInterno) {
    $sql = "  SELECT ICIARP.$field
              FROM SFPC.TBITEMCARONAINTERNAATARP ICIARP
              LEFT JOIN SFPC.TBCARONAINTERNAATARP CIARP ON ICIARP.CARPNOSEQU = CIARP.CARPNOSEQU
              WHERE ICIARP.CARPNOSEQU = $ata
              AND ICIARP.CITARPSEQU = $item
              AND CIARP.FCARRPSITU = 'A'
              AND CIARP.CORGLICODI = " . $orgaoCaronaInterno;

    $linha = resultLinhaUnica(executarSQL($db, $sql));
    $utilizadoCaronaInclusaoDiretaOrgaoAtaAtual = $linha[0];

    return $utilizadoCaronaInclusaoDiretaOrgaoAtaAtual;
}

function utilizadoCaronaInclusaoDiretaOrgaoAtaAnterior($db, $ata, $item, $field='AITCRPQTUT', $orgaoCaronaInterno) {
    $ataAnterior = ataAnterior($db, $ata);
    $utilizadoCaronaInclusaoDiretaOrgaoAtaAnterior = 0;

    if(!empty($ataAnterior)) {
        $sql = "  SELECT ICIARP.$field
              FROM SFPC.TBITEMCARONAINTERNAATARP ICIARP
              LEFT JOIN SFPC.TBCARONAINTERNAATARP CIARP ON ICIARP.CARPNOSEQU = CIARP.CARPNOSEQU
              WHERE ICIARP.CARPNOSEQU = $ataAnterior
              AND ICIARP.CITARPSEQU = $item
              AND CIARP.FCARRPSITU = 'A'
              AND CIARP.CORGLICODI = " . $orgaoCaronaInterno;

        $linha = resultLinhaUnica(executarSQL($db, $sql));
        $utilizadoCaronaInclusaoDiretaOrgaoAtaAnterior = $linha[0];
    }

    return $utilizadoCaronaInclusaoDiretaOrgaoAtaAnterior;
}

function utilizadoCaronaSccOrgaoAtaAtual($db, $ata, $item, $field='AITESCQTSO', $orgaoCaronaInterno){
    $sql = "SELECT SUM(ISC.$field)
            FROM SFPC.TBITEMSOLICITACAOCOMPRA ISC
            LEFT JOIN SFPC.TBSOLICITACAOCOMPRA SC ON ISC.CSOLCOSEQU = SC.CSOLCOSEQU 
            LEFT JOIN SFPC.TBCARONAINTERNAATARP CIRP ON CIRP.CARPNOSEQU = SC.CARPNOSEQU
            WHERE ISC.CARPNOSEQU = $ata
            AND ISC.CITARPSEQU = $item
            AND SC.FSOLCORPCP = 'C'
            AND SC.FSOLCOAUTC = 'S'
            AND CIRP.CORGLICODI = " . $orgaoCaronaInterno;

    $linha = resultLinhaUnica(executarSQL($db, $sql));
    $utilizadoCaronaSccOrgaoAtaAtual = $linha[0];
    
    
    return $utilizadoCaronaSccOrgaoAtaAtual;
}


function utilizadoCaronaSccOrgaoAtaAnterior($db, $ata, $item, $field='AITESCQTSO', $orgaoCaronaInterno) {
    $ataAnterior = ataAnterior($db, $ata);
    $utilizadoCaronaSccOrgaoAtaAnterior = 0;

    if(!empty($ataAnterior)) {
        $sql = "SELECT SUM(ISC.$field)
            FROM SFPC.TBITEMSOLICITACAOCOMPRA ISC
            LEFT JOIN SFPC.TBSOLICITACAOCOMPRA SC ON ISC.CSOLCOSEQU = SC.CSOLCOSEQU
            LEFT JOIN SFPC.TBCARONAINTERNAATARP CIRP ON CIRP.CARPNOSEQU = SC.CARPNOSEQU
            WHERE ISC.CARPNOSEQU = $ataAnterior
            AND ISC.CITARPSEQU = $item
            AND SC.FSOLCORPCP = 'C'
            AND SC.FSOLCOAUTC = 'S'
            AND CIRP.CORGLICODI = " . $orgaoCaronaInterno;

        $linha = resultLinhaUnica(executarSQL($db, $sql));
        $utilizadoCaronaSccOrgaoAtaAnterior = $linha[0];
    }

    return $utilizadoCaronaSccOrgaoAtaAnterior;
}

function utilizadoCaronaOrgaoInterno($db, $ata, $item, $field_1='AITCRPQTUT', $field_2='AITESCQTSO', $orgaoCaronaInterno){
    $utilizadoCaronaInclusaoDiretaOrgaoAtaAtual    = UtilizadoCaronaInclusaoDiretaOrgaoAtaAtual($db, $ata, $item, $field_1, $orgaoCaronaInterno);
    $utilizadoCaronaInclusaoDiretaOrgaoAtaAnterior = UtilizadoCaronaInclusaoDiretaOrgaoAtaAnterior($db, $ata, $item, $field_1, $orgaoCaronaInterno);
    $utilizadoCaronaSccOrgaoAtaAtual               = UtilizadoCaronaSccOrgaoAtaAtual($db, $ata, $item, $field_2, $orgaoCaronaInterno);
    $utilizadoCaronaSccOrgaoAtaAnterior            = UtilizadoCaronaSccOrgaoAtaAnterior($db, $ata, $item, $field_2, $orgaoCaronaInterno);

    $utilizadoCaronaOrgaoInterno = ($utilizadoCaronaInclusaoDiretaOrgaoAtaAtual +
                                    $utilizadoCaronaInclusaoDiretaOrgaoAtaAnterior +
                                    $utilizadoCaronaSccOrgaoAtaAtual +
                                    $utilizadoCaronaSccOrgaoAtaAnterior);

    return $utilizadoCaronaOrgaoInterno;
}

function saldoOrgaoCaronaInterno($db, $ata, $item, $field_1='AITARPQTOR', $field_2='AITARPQTAT', $field_3='AITCRPQTUT', $field_4='AITESCQTSO', $orgaoCaronaInterno){
    $valorItemAta = valorItemAta($db, $ata, $item, $field_1, $field_2);
    $utilizadoCaronaOrgaoInterno = utilizadoCaronaOrgaoInterno($db, $ata, $item, $field_3, $field_4, $orgaoCaronaInterno);
    $saldoOrgaoCaronaInterno = $valorItemAta - $utilizadoCaronaOrgaoInterno;

    return $saldoOrgaoCaronaInterno;
}

function utilizadoCaronaOrgaoExternoOrgaoAtaAtual($db, $ata, $item, $field='ACOEITQTAT', $descOrgaoCaronaExterno) {
    $sql = "SELECT SUM(COEI.$field)
            FROM SFPC.TBCARONAORGAOEXTERNOITEM COEI
            LEFT JOIN SFPC.TBCARONAORGAOEXTERNO COE ON COEI.CARPNOSEQU = COE.CARPNOSEQU AND COEI.CCAROESEQU = COE.CCAROESEQU
            WHERE COEI.CARPNOSEQU = $ata
            AND COEI.CITARPSEQU = $item
            AND COE.ECAROEORGG = '$descOrgaoCaronaExterno'";

    $linha = resultLinhaUnica(executarSQL($db, $sql));
    $utilizadoCaronaOrgaoExternoGeralAtaAtual = $linha[0];

    return $utilizadoCaronaOrgaoExternoGeralAtaAtual;
}

function utilizadoCaronaOrgaoExternoOrgaoAtaAnterior($db, $ata, $item, $field='ACOEITQTAT', $descOrgaoCaronaExterno) {
    $ataAnterior = ataAnterior($db, $ata);
    $utilizadoCaronaOrgaoExternoGeralAtaAnterior = 0;

    if(!empty($ataAnterior)) {
        $sql = "SELECT SUM(COEI.$field)
            FROM SFPC.TBCARONAORGAOEXTERNOITEM COEI
            LEFT JOIN SFPC.TBCARONAORGAOEXTERNO COE ON COEI.CARPNOSEQU = COE.CARPNOSEQU AND COEI.CCAROESEQU = COE.CCAROESEQU
            WHERE COEI.CARPNOSEQU = $ataAnterior
            AND COEI.CITARPSEQU = $item
            AND COE.ECAROEORGG = '$descOrgaoCaronaExterno'";

        $linha = resultLinhaUnica(executarSQL($db, $sql));
        $utilizadoCaronaOrgaoExternoGeralAtaAnterior = $linha[0];
    }

    return $utilizadoCaronaOrgaoExternoGeralAtaAnterior;
}

function utilizadoCaronaOrgaoExterno($db, $ata, $item, $field='ACOEITQTAT', $descOrgaoCaronaExterno){
    $utilizadoCaronaOrgaoExternoOrgaoAtaAtual    = utilizadoCaronaOrgaoExternoOrgaoAtaAtual($db, $ata, $item, $field, $descOrgaoCaronaExterno);
    $utilizadoCaronaOrgaoExternoOrgaoAtaAnterior = utilizadoCaronaOrgaoExternoOrgaoAtaAnterior($db, $ata, $item, $field, $descOrgaoCaronaExterno);

    $utilizadoCaronaOrgaoExterno = $utilizadoCaronaOrgaoExternoOrgaoAtaAtual + $utilizadoCaronaOrgaoExternoOrgaoAtaAnterior;

    return $utilizadoCaronaOrgaoExterno;
}

function saldoOrgaoCaronaExterno($db, $ata, $item, $field_1='AITARPQTOR', $field_2='AITARPQTAT', $field_3='AITCRPQTUT', $descOrgaoCaronaExterno)
{
    $valorItemAta = valorItemAta($db, $ata, $item, $field_1, $field_2);
    $utilizadoCaronaOrgaoExterno = utilizadoCaronaOrgaoExterno($db, $ata, $item, $field_3, $descOrgaoCaronaExterno);
    $saldoOrgaoCaronaExterno = $valorItemAta + $utilizadoCaronaOrgaoExterno;

    return $saldoOrgaoCaronaExterno;

}

function saldoCaronaInternaTotal($db, $ata, $item, $field_1 = 'AITARPQTOR', $field_2 = 'AITARPQTAT', $field_3='AITCRPQTUT', $field_4='AITESCQTSO', $field_5='ACOEITQTAT', $orgaoCaronaInterno){
    $saldoGeralCarona        = saldoGeralCarona($db, $ata, $item, $field_1, $field_2, $field_3, $field_4, $field_5);
    $saldoOrgaoCaronaInterno = saldoOrgaoCaronaInterno($db, $ata, $item, $field_1, $field_2, $field_3, $field_4, $orgaoCaronaInterno);

    $saldoCaronaInternaTotal = $saldoGeralCarona;
    if($saldoGeralCarona > $saldoOrgaoCaronaInterno) {
        $saldoCaronaInternaTotal = $saldoOrgaoCaronaInterno;
    }

    return $saldoCaronaInternaTotal;
}

function saldoCaronaExternaTotal($db, $ata, $item, $field_1 = 'AITARPQTOR', $field_2 = 'AITARPQTAT', $field_3='AITCRPQTUT', $field_4='AITESCQTSO', $field_5='ACOEITQTAT', $descOrgaoCaronaExterno){
    $saldoGeralCarona        = saldoGeralCarona($db, $ata, $item, $field_1, $field_2, $field_3, $field_4, $field_5);
    $saldoOrgaoCaronaExterno = saldoOrgaoCaronaExterno($db, $ata, $item, $field_1, $field_2, $field_3, $descOrgaoCaronaExterno);
    $saldoCaronaExternaTotal = $saldoGeralCarona;

    if($saldoGeralCarona > $saldoOrgaoCaronaExterno) {
        $saldoCaronaExternaTotal = $saldoOrgaoCaronaExterno;
    }

    return $saldoCaronaExternaTotal;
}


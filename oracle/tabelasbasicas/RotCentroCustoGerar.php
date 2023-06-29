<?php

#-------------------------------------------------------------------------------
# Portal da DGCO
# Programa: RotCentroCustoGerar.php
# Autor:    Roberta Costa
# Data:     03/08/2005
# Alterado: Marcus Thiago
# Data:     26/12/2005
# Alterado: Álvaro Faria
# Data:     03/07/2006 - Uso do Pear / Mudanças para rodar em Cohab/Varzea / Correções
# Alterado: Álvaro Faria
# Data:     02/01/2007 - Correções para mudança de ano
# Objetivo: Programa de Migra os Centros de Custo do Oracle para o Postgre
#--------------------------------------------------------------------------------------------
# Alterado: Pitang Agile IT
# Data:     03/07/2015
# Objetivo: CR73618 - Erro de Acentuação em diversos lugares no Portal de Compras - Produção
# Versão:   v1.22.0-5-g50d5f65
#--------------------------------------------------------------------------------------------
# Alterado: Pitang Agile IT - Caio Coutinho
# Data:     18/12/2018
# Objetivo: 207375
#--------------------------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include '../../funcoes.php';

# Executa o controle de segurança #
session_start();
Seguranca();

# Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $Centros = urldecode($_GET['Centros']);
}
# Transforma concatenação do TabCentroCustoGerar novamente em array
if ($Centros) {
    $CentroCusto = explode('æ', $Centros);
} else {
    $Mensagem = urlencode('Selecione pelo menos uma Unidade Orçamentária antes de Gerar');
    $Url = "tabelasbasicas/TabCentroCustoGerar.php?Mens=1&Tipo=1&Mensagem=$Mensagem";
    if (!in_array($Url, $_SESSION['GetUrl'])) {
        $_SESSION['GetUrl'][] = $Url;
    }
    RedirecionaPost($Url);
}

# Ano Atual do Exercicio #
$AnoExercicio = date('Y');

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

# Abre a Conexão com o Oracle #
$dbora = ConexaoOracle();

# Pega o os dados do Centro de Custo - Oracle #
$sql = 'SELECT DISTINCT A.DEXERCANOR, A.CORGORCODI, A.CRPAAACODI, A.CUNDORCODI, ';
$sql .= '       A.CCENCPCODI, A.CDETCPCODI, B. ECENCPDESC, C.EDETCPDESC';
$sql .= '  FROM SFCP.TBESTRUTURACUSTO A, SFCP.TBCENTROCUSTOPUBLICO B, SFCP.TBDETALHAMENTOCUSTO C';
$sql .= " WHERE A.DEXERCANOR = $AnoExercicio AND A.CCENCPCODI = B.CCENCPCODI ";
$sql .= '   AND A.CDETCPCODI = C.CDETCPCODI ';
$sql .= '   AND ( ';
$i = 0;
foreach ($CentroCusto as $Centro) {
    $CentroSplit = explode('_', $Centro);
    $Org = $CentroSplit[0];
    $Uni = $CentroSplit[1];
    if ($i == 0) {
        $sql .= '       ( A.CORGORCODI = '.$Org.' AND A.CUNDORCODI = '.$Uni.' ) ';
        $i = 1;
    } else {
        $sql .= '    OR ( A.CORGORCODI = '.$Org.' AND A.CUNDORCODI = '.$Uni.' ) ';
    }
}
$sql .= ' ) ';
$sql .= ' ORDER BY A.DEXERCANOR, A.CORGORCODI, A.CRPAAACODI, A.CUNDORCODI, A.CCENCPCODI, A.CDETCPCODI ';

$res = $dbora->query($sql);
if (PEAR::isError($res)) {
    $dbora->disconnect;
    ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
    exit;
} else {
    while ($Linha = $res->fetchRow()) {
        ++$cont;
        $Exercicio = $Linha[0];
        $Orgao = $Linha[1];
        $RPA = $Linha[2];
        $Unidade = $Linha[3];
        $CentroCusto = $Linha[4];
        $Detalhamento = $Linha[5];
        $Descricao = str_replace("'", '', $Linha[6]);
        $DescDetalhamento = str_replace("'", '', $Linha[7]);

        if ($Exercicio != '') {
            # Conecta com o banco Postgre #
                $db = Conexao();
            $db->query('BEGIN TRANSACTION');
            $sqlcc = 'SELECT COUNT(*) FROM SFPC.TBCENTROCUSTOPORTAL ';
            $sqlcc .= " WHERE ACENPOANOE = $Exercicio AND CCENPOCORG = $Orgao ";
            $sqlcc .= "   AND CCENPONRPA = $RPA AND CCENPOUNID = $Unidade ";
            $sqlcc .= "   AND CCENPOCENT = $CentroCusto AND CCENPODETA = $Detalhamento";
            $rescc = $db->query($sqlcc);
            if (PEAR::isError($rescc)) {
                $Rollback = 1;
                $db->query('END TRANSACTION');
                $db->disconnect;
                $dbora->disconnect;
                ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlcc");
                exit;
            } else {
                $Qtd = $rescc->fetchRow();
                if ($Qtd[0] == 0) {
                    # Insere os dados do Centro de Custo - PostGre #
                        $sqlcc = 'INSERT INTO SFPC.TBCENTROCUSTOPORTAL ( ';
                    $sqlcc .= 'CCENPOSEQU, CORGLICODI, ACENPOANOE, CCENPOCORG, CCENPOUNID, ';
                    $sqlcc .= 'CCENPONRPA, CCENPOCENT, CCENPODETA, ECENPODESC, ECENPODETA, ';
                    $sqlcc .= 'FCENPOSITU, TCENPOULAT, CUSUPOCODI ';
                    $sqlcc .= ') VALUES (';
                    $sqlcc .= "nextval('sfpc.tbcentrocustoportal_ccenposequ_seq'), NULL, $Exercicio, $Orgao, $Unidade, ";
                    $sqlcc .= "$RPA, $CentroCusto, $Detalhamento, '$Descricao', ";
                    $sqlcc .= "'$DescDetalhamento', 'A', '".date('Y-m-d H:i:s')."',".$_SESSION['_cusupocodi_'].")";
                    $rescc = $db->query($sqlcc);
                    if (PEAR::isError($rescc)) {
                        $Rollback = 1;
                        $db->query('ROLLBACK');
                        $db->query('END TRANSACTION');
                        $db->disconnect;
                        $dbora->disconnect;
                        ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlcc");
                        exit;
                    }
                    ++$Inseriu;
                } else {
                    # Atualiza os dados do Centro de Custo - PostGre #
                        $sqlcc = 'UPDATE SFPC.TBCENTROCUSTOPORTAL ';
                    $sqlcc .= "   SET ECENPODESC = '$Descricao', ECENPODETA = '$DescDetalhamento', ";
                    $sqlcc .= "       TCENPOULAT = '".date('Y-m-d H:i:s')."', CUSUPOCODI = " . $_SESSION['_cusupocodi_'];
                    $sqlcc .= " WHERE ACENPOANOE = $Exercicio AND CCENPOCORG = $Orgao ";
                    $sqlcc .= "   AND CCENPONRPA = $RPA AND CCENPOUNID = $Unidade ";
                    $sqlcc .= "   AND CCENPOCENT = $CentroCusto AND CCENPODETA = $Detalhamento";
                    $rescc = $db->query($sqlcc);
                    if (PEAR::isError($rescc)) {
                        $Rollback = 1;
                        $db->query('ROLLBACK');
                        $db->query('END TRANSACTION');
                        $db->disconnect;
                        $dbora->disconnect;
                        ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlcc");
                        exit;
                    }
                    ++$Alterou;
                }
            }
        }
    }
}

if ($cont > 0) {
    if ($Rollback != 1) {
        $db->query('COMMIT');
        $db->query('END TRANSACTION');
        $db->disconnect;
    }
} else {
    $Mensagem = urlencode('A Unidade Orçamentária não retornou nenhum Centro de Custo');
    $Url = "tabelasbasicas/TabCentroCustoGerar.php?Mens=1&Tipo=1&Mensagem=$Mensagem";
    if (!in_array($Url, $_SESSION['GetUrl'])) {
        $_SESSION['GetUrl'][] = $Url;
    }
    RedirecionaPost($Url);
}

$dbora->disconnect;

if ($Exercicio != '') {
    if ($Inseriu == '' and $Alterou == '') {
        $Mensagem = urlencode('Nenhuma Atualização a ser Efetuada');
    } else {
        $Mensagem = urlencode('Atualização Efetuada com Sucesso');
    }
    $Url = "tabelasbasicas/TabCentroCustoGerar.php?Mens=1&Tipo=1&Mensagem=$Mensagem";
    if (!in_array($Url, $_SESSION['GetUrl'])) {
        $_SESSION['GetUrl'][] = $Url;
    }
    RedirecionaPost($Url);
} else {
    $Mensagem = urlencode('Nenhuma nova Ocorrência de CENTRO DE CUSTO Encontrada');
    $Url = "tabelasbasicas/TabCentroCustoGerar.php?Mens=1&Tipo=1&Mensagem=$Mensagem";
    if (!in_array($Url, $_SESSION['GetUrl'])) {
        $_SESSION['GetUrl'][] = $Url;
    }
    RedirecionaPost($Url);
}

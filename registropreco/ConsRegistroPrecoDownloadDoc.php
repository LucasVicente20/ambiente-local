<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: ConsRegistroPrecoDownloadDoc.php.php
# Autor:    Rossana Lira
# Data:     28/03/2007
# Objetivo: Programa de Download dos Documentos da Licitação
#           Apaga arquivo temporário anterior apenas se ele
#                        foi criado a mais de 10 minutos
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------

// 220038--

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD'] == "GET") {
    $GrupoCodigo          = $_GET['GrupoCodigo'];
    $Processo             = $_GET['Processo'];
    $ProcessoAno          = $_GET['ProcessoAno'];
    $ComissaoCodigo       = $_GET['ComissaoCodigo'];
    $OrgaoLicitanteCodigo = $_GET['OrgaoLicitanteCodigo'];
    $DocCodigo            = $_GET['DocCodigo'];
} else {
    $Arquivo              = $_GET['Arquivo'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "ConsRegistroPrecoDownloadDoc.php";

if (($GrupoCodigo != "") && ($ComissaoCodigo != "") && ($Processo != "") && ($ProcessoAno != "")&& ($DocCodigo != "")) {
    $db     = Conexao();
    $sql = "SELECT EATARPNOME ";
    $sql.= "FROM SFPC.TBATAREGISTROPRECO WHERE CLICPOPROC = $Processo AND ";
    $sql.= "ALICPOANOP = $ProcessoAno AND CCOMLICODI = $ComissaoCodigo AND ";
    $sql.= "CGREMPCODI = $GrupoCodigo AND CATARPCODI = $DocCodigo";
    $result = $db->query($sql);
    if (PEAR::isError($result)) {
        ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
    }
    while ($Linha = $result->fetchRow()) {
        $NomeArquivo = $Linha[0];
    }
    $db->disconnect();

    $ArquivoNomeServidor = "registropreco/"."ATAREGISTROPRECO".$GrupoCodigo."_".$Processo."_".$ProcessoAno."_".$ComissaoCodigo."_".$OrgaoLicitanteCodigo."_".$DocCodigo;
    $Arquivo = $GLOBALS["CAMINHO_UPLOADS"].$ArquivoNomeServidor;
    if (file_exists($Arquivo)) {
        $url = "../carregarArquivo.php?arq=".urlencode($ArquivoNomeServidor)."&arq_nome=".urlencode($NomeArquivo);

        header("Location: " . $url);
        exit();
    }
}

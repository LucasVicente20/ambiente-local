<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadAtaRegistroPrecoExternaDownloadDoc.php
# Autor:    Carlos Abreu
# Data:     19/06/2007
# Alterado: Rodrigo Melo
# Data:     21/01/2009 	- Fazendo alterações no programa para se adequar
#                         ao modelo de dados e disponibilizar a funcionalidade
#                         para Ata de Registro de Preço Externa.
# Objetivo: Programa de Download dos Documentos das Atas de Registro de Preco Externas
# OBS.:     Tabulação 2 espaços
#           Apaga arquivo temporário anterior apenas se ele
#                        foi criado a mais de 10 minutos
#-------------------------------------------------------------------------

// 220038--

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD'] == "GET") {
    $AtaRegistroPrecoCod = $_GET['AtaRegistroPrecoCod'];
    $AtaRegistroPrecoDoc = $_GET['AtaRegistroPrecoDoc'];
}

session_start();

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "CadAtaRegistroPrecoExternaDownloadDoc.php";

if (($AtaRegistroPrecoCod != "") && ($AtaRegistroPrecoDoc != "")) {
    $db = Conexao();
    $sql  = "SELECT EARPEDNOMS ";
    $sql .= "  FROM SFPC.TBATAREGISTROPRECOEXTERNADOC ";
    $sql .= " WHERE CARPETCODI = $AtaRegistroPrecoCod AND CARPEDCODI = $AtaRegistroPrecoDoc ";
    $result = $db->query($sql);
    if (PEAR::isError($result)) {
        ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
    }
    while ($Linha = $result->fetchRow()) {
        $NomeArquivo = $Linha[0];
    }
    $db->disconnect();

    $ArquivoNomeServidor = "registropreco/ATAREGISTROPRECOEXTERNA_".$AtaRegistroPrecoCod."_".$AtaRegistroPrecoDoc;
    $Arquivo = $GLOBALS["CAMINHO_UPLOADS"].$ArquivoNomeServidor;
    if (file_exists($Arquivo)) {
        $url = "../carregarArquivo.php?arq=".urlencode($ArquivoNomeServidor)."&arq_nome=".urlencode($NomeArquivo);
        
        header("Location: " . $url);
        exit();
    }
}

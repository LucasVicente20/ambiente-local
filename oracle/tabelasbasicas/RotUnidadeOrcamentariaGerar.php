<?php

#-------------------------------------------------------------------------------
# Portal da DGCO
# Programa: RotUnidadeOrcamentariaGerar.php
# Objetivo: Programa de Migra a Unidade Orçamentária do Oracle para o Postgre
# Autor:    Roberta Costa
# Data:     21/12/2004
#-----------------------------------
# Alterado: Álvaro Faria
# Data:     03/07/2006 - Uso do Pear / Mudanças para rodar em Cohab/Varzea / Correções
# Alterado: Álvaro Faria
# Data:     02/01/2007 - Correções para mudança de ano
#                        Identação
# Alterado: Rossana Lira
# Data:     21/12/2007 - Mudança do $Ano para 2008, forçando a integração em 2007
# Alterado: Rossana Lira
# Data:     06/05/2008 - Filtro para não buscar unidades orçamentárias desativadas no ano corrente
# Alterado: Rossana Lira
# Data:     20/05/2008 - Acrescentar nova unidade orçamentária no filtro para não buscar unidades
#                        orçamentárias desativadas no ano corrente
# Alterado: Ariston Cordeiro
# Data:     05/01/2009 - Remover no SQL condições do ano passado e passar a checar o campo fundoruina, que é populado pelo usuário do sistema SPOD.
# 										 - Mudança do $Ano para 2009.
# Alterado: Ariston Cordeiro
# Data:     29/12/2009 - $Ano agora receberá o ano via sessão (definido no arquivo TabUnidadeOrcamentariaGerar.php)
# Alterado: Ariston Cordeiro
# Data:     30/12/2010 - Campo FUNDORUINA agora ignora '1' ou 'I'. (Padrão é 'I', mas estava sendo usado '1')
#--------------------------------------------------------------------------------------------
# Alterado: Pitang Agile IT
# Data:     03/07/2015
# Objetivo: CR73618 - Erro de Acentuação em diversos lugares no Portal de Compras - Produção
# Versão:   v1.22.0-5-g50d5f65
#--------------------------------------------------------------------------------------------
# Acesso ao arquivo de funções #
include '../../funcoes.php';

# Executa o controle de segurança #
session_start();
Seguranca();

# Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $NomePrograma = urldecode($_GET['NomePrograma']);
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

# Dasos para mensagens de erro

$Assunto = 'Rotina de geração de Unidades Orçamentárias';
$Arquivo = 'RotUnidadeOrcamentariaGerar.php';
$Inicio = 'Início: '.date('d/m/Y H:i:s');

# Recebe ano que será usado nas querys como Ano de Exercício

$Ano = $_SESSION['AnoGeracaoUnidadeOrcamentaria'];
$AnoAtual = date('Y');

// Não permitir anos incorretos
if ((is_null($Ano)) or ($Ano < $AnoAtual) or ($Ano > $AnoAtual + 1)) {
    EmailErro($Assunto, $Arquivo, __LINE__, "Erro: Ano inválido. Ano informado: '".$Ano."'");
    exit(0);
}

# Abre a Conexão com o Postgree #
$db = Conexao();

$sql = 'BEGIN TRANSACTION';
$res = $db->query($sql);

# Verificando se existe Unidade Orçamentária integrada para o ano de exercicio #
$sql = 'SELECT COUNT(*) FROM SFPC.TBUNIDADEORCAMENTPORTAL ';
$sql .= " WHERE TUNIDOEXER = $Ano";
$res = $db->query($sql);
if (PEAR::isError($res)) {
    $db->disconnect;
    ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
    $sql = 'ROLLBACK';
    $res = $db->query($sql);
    exit;
} else {
    $Linha = $res->fetchRow();
    $Qtd = $Linha[0];
    if ($Qtd > 0) {
        # Apagando as Ocorrências do ano em Exercício #
        $sql = 'DELETE FROM SFPC.TBUNIDADEORCAMENTPORTAL ';
        $sql .= " WHERE TUNIDOEXER = $Ano";
        $res = $db->query($sql);
        if (PEAR::isError($res)) {
            $db->disconnect;
            ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
            $sql = 'ROLLBACK';
            $res = $db->query($sql);
            exit;
        }
    }

        # Abre a Conexão com o Oracle #
    $dbora = ConexaoOracle();
    $Sql = 'SELECT DISTINCT DEXERCANOR, CORGORCODI, CUNDORCODI, NUNDORNOME ';
    $Sql    .= '  FROM SPOD.TBUNIDADEORCAMENT ';
    $Sql    .= " WHERE DEXERCANOR = $Ano ";
    $Sql    .= " AND  ( (FUNDORUINA <> '1' AND FUNDORUINA <> 'I') OR FUNDORUINA IS NULL) "; // Não gerar unidades orçamentárias desativadas

    $resora = $dbora->query($Sql);
    if (PEAR::isError($resora)) {
        $dbora->disconnect;
        $db->disconnect;
        ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $Sql");
        $sql = 'ROLLBACK';
        $res = $db->query($sql);
        exit;
    } else {
        while ($Linha = $resora->fetchRow()) {
            $Exercicio = $Linha[0];
            $Orgao = $Linha[1];
            $Unidade = $Linha[2];
            $Nome = strtoupper2(substr($Linha[3], 0, 100));
            if ($Exercicio == $Ano) {
                # Inserindo na tabela SFCP.TBUNIDADEORCAMENTPORTAL - Post DoisUnidos #
                $sql = 'INSERT INTO SFPC.TBUNIDADEORCAMENTPORTAL ( ';
                $sql .= 'TUNIDOEXER, CUNIDOORGA, CUNIDOCODI, ';
                $sql .= 'EUNIDODESC, TUNIDOULAT ';
                $sql .= ') VALUES ( ';
                $sql .= "$Exercicio, $Orgao, $Unidade, ";
                $sql .= "'$Nome', '".date('Y-m-d H:i:s')."')";
                $res = $db->query($sql);
                if (PEAR::isError($res)) {
                    $dbora->disconnect;
                    $db->disconnect;
                    ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
                    $sql = 'ROLLBACK';
                    $res = $db->query($sql);
                    exit;
                }
            }
        }
    }
    $dbora->disconnect();

    $sql = 'COMMIT';
    $res = $db->query($sql);
            # Redireciona para Unidade Orçamentária #
    $Mensagem = urlencode("Geração Realizado com Sucesso para este Ano de Exercício - $Ano");
    $Url = "tabelasbasicas/$NomePrograma?Mensagem=$Mensagem&Mens=1&Tipo=1";
    if (!in_array($Url, $_SESSION['GetUrl'])) {
        $_SESSION['GetUrl'][] = $Url;
    }
    RedirecionaPost($Url);
}

$db->disconnect;

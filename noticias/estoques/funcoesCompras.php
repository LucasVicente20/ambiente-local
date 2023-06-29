<?php

# ----------------------------------------------------------------------------------------------------------------------
// Portal da DGCO
// Programa: funcoesCompras.php
// Objetivo: funções com regras do módulo Compras
// Autor: Ariston Cordeiro
# ----------------------------------------------------------------------------------------------------------------------
// Autor da manutenção: Heraldo Botelho
// Data: 08/11/2012
// MOtivo: Só gravar na tabela TRP se o indicador de não gravação na
// TRP da tabela de materiais, estiver ="N" ou nulo
# ----------------------------------------------------------------------------------------------------------------------
// Autor da manutenção: Heraldo Botelho
// Data: 02/11/2013
// Trecho inserido por Heraldo em (2/4/2013)
// Alterar a função validarReservaOrcamentaria() para só fazer a critica de
// bloqueiosValoresTotais<$valorTotalScc se
// Tipo de Reserva = "TIPO_RESERVA_ORCAMENTARIA_DOTACAO" e
// Tipo de Compra = "2" (Licitação) e
// Registro Preço = "S"
# ----------------------------------------------------------------------------------------------------------------------
// Autor: Pitang Agile TI
// Data: 08/04/2015 - CR307 - TRP - Fase Licitação Incluir - Nova regra para a TRP
// Motivo: Verificar para cada item se não há nenhum registro do mesmo material (CMATEPSEQU)
// na TRP do tipo com processo licitatório (CLICPOPROC <> nulo)
// ou pesquisa de mercado (CPESPMSEQU <> nulo)
// ou quando a média TRP estiver zerada (preços com data de referência maior que o limite do parâmetro).
// Em caso afirmativo, gravar este preço na TRP, só que com o campo FTRPREVALI = 'A'.
// Em caso negativo, o sistema deve calcular a média TRP (função alterada na CR 306)
// e verificar de acordo com o parâmetro sfpc.tbparametrosgerais.vpargeprel se está dentro do limite.
// Se estiver dentro do limite gravar na TRP só que com o campo FTRPREVALI = 'A'.
// Porém, se estiver fora do limite, gravar na TRP da mesma forma atual, só que com o campo FTRPREVALI como nulo;
//
// Corrigir também a forma de gravação atual para gravar no campo ctrpreulat a data da fase de homologação
// através do campos sfpc.tbfaselicitacao.tfaseldata (antes era tfaselulat)
# ----------------------------------------------------------------------------------------------------------------------
// Alterado: Pitang Agile TI
// Data: 14/07/2015
// Objetivo: Requisito 73664 - Materiais > TRP > Consultar
# ----------------------------------------------------------------------------------------------------------------------
# Alterado: Lucas Baracho
# Data:     27/07/2018
# Objetivo: Tarefa Redmine 189456
// ############################################################################
# Autor:    Pitang Agile TI
# Data :    05/04/2018
# Objetivo: CR187219 - Alteração de cálculo na homologação de uma licitação
# ------------------------------------------------------------------------------
# Autor:    Pitang Agile TI
# Data :    24/04/2018
# Objetivo: CR165624 - Não gravar TRP #538
# ------------------------------------------------------------------------------
# Alterado: Pitang Agile TI - Caio Coutinho
# Data: 28/06/2018
# Objetivo: Tarefa #197622
# ------------------------------------------------------------------------------
# Alterado: Pitang Agile TI - Caio Coutinho
# Data: 06/07/2018
# Objetivo: Tarefa #194536
# ------------------------------------------------------------------------------
# Alterado: Rossana Lira
# Data: 03/10/2019
# Objetivo: Tarefa #224495
# ------------------------------------------------------------------------------# ------------------------------------------------------------------------------
# Alterado: Eliakim Ramos
# Data: 31/08/2020
# Objetivo: Tarefa #237312
# ------------------------------------------------------------------------------
# Alterado: João Madson
# Data: 17/02/2021
# Objetivo: Tarefa #243828
# ------------------------------------------------------------------------------
# Alterado : Osmar Celestino
# Data: 10/11/2021
# Objetivo: CR #255563
#---------------------------------------------------------------------------
# Alterado : Lucas Vicente
# Data: 14/07/2022
# Objetivo: CR #231121
#---------------------------------------------------------------------------

// arquivo geral de funcoes
require_once dirname(__FILE__) . '/../funcoes.php';

// funcoes usadas no módulo de compras
require_once CAMINHO_SISTEMA . 'fornecedores/funcoesFornecedores.php';
require_once CAMINHO_SISTEMA . 'materiais/funcoesMateriais.php';
require_once CAMINHO_SISTEMA . 'licitacoes/funcoesLicitacoes.php';
require_once CAMINHO_SISTEMA . 'estoques/funcoesEstoques.php';

require_once CAMINHO_SISTEMA . 'geral/Excecao.class.php';
// constantes para o módulo compras

// situações de solicitacao de compra
$GLOBALS['TIPO_SITUACAO_SCC_EM_CADASTRAMENTO'] = 1;
$GLOBALS['TIPO_SITUACAO_SCC_CONCLUIDA'] = 2;
$GLOBALS['TIPO_SITUACAO_SCC_PENDENTE_DE_EMPENHO'] = 3;
$GLOBALS['TIPO_SITUACAO_SCC_PENDENTE_DE_CONTRATO'] = 4;
$GLOBALS['TIPO_SITUACAO_SCC_PENDENTE_DE_AUTORIZACAO_SARP'] = 5;
$GLOBALS['TIPO_SITUACAO_SCC_EM_ANALISE'] = 6;
$GLOBALS['TIPO_SITUACAO_SCC_PARA_ENCAMINHAMENTO'] = 7;
$GLOBALS['TIPO_SITUACAO_SCC_ENCAMINHADA'] = 8;
$GLOBALS['TIPO_SITUACAO_SCC_EM_LICITACAO'] = 9;
$GLOBALS['TIPO_SITUACAO_SCC_CANCELADA'] = 10;

// constantes. Alterar variaveis acima para as constantes abaixo (tomar cuidado com a parte do Bank System)
define('TIPO_SITUACAO_SCC_EM_CADASTRAMENTO', 1);
define('TIPO_SITUACAO_SCC_CONCLUIDA', 2); // nome alterado para REGISTRO DE PREÇOS CONCLUÍDO
define('TIPO_SITUACAO_SCC_PENDENTE_DE_EMPENHO', 3); // nome mudado para PSE GERADA
define('TIPO_SITUACAO_SCC_PENDENTE_DE_CONTRATO', 4);
define('TIPO_SITUACAO_SCC_PENDENTE_DE_AUTORIZACAO_SARP', 5);
define('TIPO_SITUACAO_SCC_EM_ANALISE', 6);
define('TIPO_SITUACAO_SCC_PARA_ENCAMINHAMENTO', 7);
define('TIPO_SITUACAO_SCC_ENCAMINHADA', 8);
define('TIPO_SITUACAO_SCC_EM_LICITACAO', 9);
define('TIPO_SITUACAO_SCC_CANCELADA', 10);

// tipo de compra
define('TIPO_COMPRA_DIRETA', 1);
define('TIPO_COMPRA_LICITACAO', 2);
define('TIPO_COMPRA_DISPENSA', 3);
define('TIPO_COMPRA_INEXIGIBILIDADE', 4);
define('TIPO_COMPRA_SARP', 5);

$GLOBALS['TIPO_COMPRA_DIRETA'] = 1;
$GLOBALS['TIPO_COMPRA_LICITACAO'] = 2;
$GLOBALS['TIPO_COMPRA_DISPENSA'] = 3;
$GLOBALS['TIPO_COMPRA_INEXIGIBILIDADE'] = 4;
$GLOBALS['TIPO_COMPRA_SARP'] = 5;

// reserva
define('TIPO_RESERVA_ORCAMENTARIA_BLOQUEIO', 1);
define('TIPO_RESERVA_ORCAMENTARIA_DOTACAO', 2);

$GLOBALS['TIPO_RESERVA_ORCAMENTARIA_BLOQUEIO'] = 1;
$GLOBALS['TIPO_RESERVA_ORCAMENTARIA_DOTACAO'] = 2;

/**
 * NOTA: Evitar usar esta função fora deste arquivo! Esta função apenas serve para auxiliar as outras funções.
 * Converte bloqueio no formato string para um padrão em array.
 *
 * @param string $bloqueioStr
 *            String com o número do bloqueio
 *
 * @return array [description]
 */
function converterBloqueioStringToArray($bloqueioStr)
{
    assercao(! is_null($bloqueioStr), "Parâmetro 'bloqueioStr' requerido");
    $bloqueioArray = explode('.', $bloqueioStr);
    assercao(count($bloqueioArray) == 5, 'Bloqueio em array com tamanho inválido');
    $resultado = array();
    $resultado['ano'] = $bloqueioArray[0];
    $resultado['orgao'] = $bloqueioArray[1];
    $resultado['unidade'] = $bloqueioArray[2];
    $resultado['destinacao'] = $bloqueioArray[3];
    $resultado['sequencial'] = $bloqueioArray[4];

    return $resultado;
}

/**
 * NOTA: Evitar usar esta função fora deste arquivo! Esta função apenas serve para auxiliar as outras funções.
 * Converte dotação no formato string para um padrão em array.
 */
function converterDotacaoStringToArray($dotacaoStr)
{
    assercao(! is_null($dotacaoStr), "Parâmetro 'dotacaoStr' requerido");
    $dotacaoArray = explode('.', $dotacaoStr);
    $nos = count($dotacaoArray);
    assercao($nos == 13 or $nos == 12, "Dotação em array com tamanho inválido. /n Dotação= '" . $dotacaoStr . "'");

    $resultado = array();

    if ($nos == 13) {
        // formato antigo
        $resultado['ano'] = $dotacaoArray[0];
        $resultado['orgao'] = substr($dotacaoArray[1], 0, 2);
        $resultado['unidade'] = substr($dotacaoArray[1], 2, 2);
        $resultado['funcao'] = $dotacaoArray[2];
        $resultado['subfuncao'] = $dotacaoArray[3] . $dotacaoArray[4];
        $resultado['programa'] = $dotacaoArray[5];
        $resultado['tipoProjetoAtividade'] = $dotacaoArray[6];
        $resultado['projetoAtividade'] = $dotacaoArray[7];
        $resultado['elemento1'] = $dotacaoArray[8];
        $resultado['elemento2'] = $dotacaoArray[9];
        $resultado['elemento3'] = $dotacaoArray[10];
        $resultado['elemento4'] = $dotacaoArray[11];
        $resultado['fonte'] = $dotacaoArray[12];
    } elseif ($nos == 12) {
        // formato novo
        $resultado['ano'] = $dotacaoArray[0];
        $resultado['orgao'] = substr($dotacaoArray[1], 0, 2);
        $resultado['unidade'] = substr($dotacaoArray[1], 2, 2);
        $resultado['funcao'] = $dotacaoArray[2];
        $resultado['subfuncao'] = $dotacaoArray[3];
        $resultado['programa'] = $dotacaoArray[4];
        $resultado['tipoProjetoAtividade'] = $dotacaoArray[5];
        $resultado['projetoAtividade'] = $dotacaoArray[6];
        $resultado['elemento1'] = $dotacaoArray[7];
        $resultado['elemento2'] = $dotacaoArray[8];
        $resultado['elemento3'] = $dotacaoArray[9];
        $resultado['elemento4'] = $dotacaoArray[10];
        $resultado['fonte'] = $dotacaoArray[11];
    }

    return $resultado;
}

/**
 * Retorna todos os dados do bloqueio usados pelo portal apartir do bloqueio em string.
 *
 * @param resource $dbOracle
 *            Objeto de conexão ao banco de Dados Oracle
 * @param string $bloqueio
 *            [description]
 *
 * @return [type] [description]
 */
function getDadosBloqueio($dbOracle, $bloqueio)
{
    $bloqueioArray = converterBloqueioStringToArray($bloqueio);

    return getDadosBloqueioFromArray($dbOracle, $bloqueioArray);
}

/**
 * Retorna todos os dados do bloqueio usados pelo portal a partir da chave do bloqueio (ano e sequencial).
 *
 * @param [type] $dbOracle
 *            [description]
 * @param [type] $bloqueioAno
 *            [description]
 * @param [type] $bloqueioSequencial
 *            [description]
 *
 * @return [type] [description]
 */
function getDadosBloqueioFromChave($dbOracle, $bloqueioAno, $bloqueioSequencial)
{
    assercao(! is_null($dbOracle), 'Variável de banco de dados Oracle não foi inicializado');
    assercao(! is_null($bloqueioAno), "Parâmetro 'bloqueioAno' requerido");
    assercao(! is_null($bloqueioSequencial), "Parâmetro 'bloqueioSequencial' requerido");
    $resultado = array();
    $resultado['orgao'] = null;
    $resultado['unidade'] = null;
    $resultado['destinacao'] = null;
    $resultado['ano'] = null;
    $resultado['sequencial'] = null;
    $resultado['anoChave'] = $bloqueioAno;
    $resultado['sequencialChave'] = $bloqueioSequencial;
    $resposta = getDadosBloqueioFromArray($dbOracle, $resultado);

    return $resposta;
}

/**
 * NOTA: Evitar usar esta função fora deste arquivo! Esta função apenas serve para auxiliar as outras funções.
 * Retorna todos os dados do bloqueio usados pelo portal a partir do bloqueio no formato de array
 * O array deve conter ou a chave do bloqueio (sequencial de chave e ano da chave),
 * ou os dados do número do bloqueio (ano, sequencial, orgão, unidade, destinação).
 *
 * @param [type] $dbOracle
 *            [description]
 * @param [type] $bloqueioArray
 *            [description]
 *
 * @return [type] [description]
 */
function getDadosBloqueioFromArray($dbOracle, $bloqueioArray)
{
    assercao(! is_null($dbOracle), 'Variável de banco de dados Oracle não foi inicializado');
    // assercao(!is_null($bloqueioArray), "Parâmetro 'bloqueioArray' requerido");
    $sql = '
        SELECT
            CELED1ELE1, CELED2ELE2, CELED3ELE3, CELED4ELE4, CSUBEDELEM,	--4
                VBLOQUBLOQ, VBLOQUANO1, VBLOQUANO2, VBLOQUANO3, VBLOQUANO4,	--9
                VBLOQUANO5, FBLOQUHOML, CFUNCACODI, CPRGORCODI, CSUBPOCODI,	--14
                CCAPATCODI, APRJATORDE, CFONTERECU, ABLOQUANOB, ABLOQUNUMC, --19
                DEXERCANOR, CORGORCODI, CUNDORCODI, CDSTBLCODI, ABLOQUSEQU, --24
                VBLOQUDESB
        FROM SFCO.TBBLOQUEIO
        WHERE
        ';
    if (! is_null($bloqueioArray['ano']) and ! is_null($bloqueioArray['sequencial']) and ! is_null($bloqueioArray['orgao']) and ! is_null($bloqueioArray['unidade']) and ! is_null($bloqueioArray['destinacao'])) {
        $sql .= '
            DEXERCANOR = ' . $bloqueioArray['ano'] . '
            AND ABLOQUSEQU = ' . $bloqueioArray['sequencial'] . '
            AND CORGORCODI = ' . $bloqueioArray['orgao'] . '
            AND CUNDORCODI = ' . $bloqueioArray['unidade'] . '
            AND CDSTBLCODI = ' . $bloqueioArray['destinacao'] . '
            ';
    } elseif (! is_null($bloqueioArray['anoChave']) and ! is_null($bloqueioArray['sequencialChave'])) {
        $sql .= '
            ABLOQUANOB = ' . $bloqueioArray['anoChave'] . '
            AND ABLOQUNUMC = ' . $bloqueioArray['sequencialChave'] . '
        ';
    } else {
        assercao(false, "array de bloqueio não possui os dados necessários. Segue array:\n" );
    }
    $obj = resultObjetoUnico(executarSQL($dbOracle, $sql));
    $resposta = null;
    if (! is_null($obj)) {
        $resposta = array();
        // chave
        $resposta['anoChave'] = $obj->ABLOQUANOB;
        $resposta['sequencialChave'] = $obj->ABLOQUNUMC;

        // dados do numero do bloqueio
        $resposta['ano'] = $obj->DEXERCANOR;
        $resposta['orgao'] = $obj->CORGORCODI;
        $resposta['unidade'] = $obj->CUNDORCODI;
        $resposta['destinacao'] = $obj->CDSTBLCODI;
        $resposta['sequencial'] = $obj->ABLOQUSEQU;
        $resposta['bloqueio'] = sprintf('%04s', $resposta['ano']) . '.' . sprintf('%02s', $resposta['orgao']) . '.' . sprintf('%02s', $resposta['unidade']) . '.' . sprintf('%01s', $resposta['destinacao']) . '.' . sprintf('%04s', $resposta['sequencial']);

        // dados da dotacao do bloqueio
        $resposta['funcao'] = $obj->CFUNCACODI;
        $resposta['subfuncao'] = $obj->CPRGORCODI; // campos subfunção e programa trocados, conforme definição
        $resposta['programa'] = $obj->CSUBPOCODI;
        $resposta['tipoProjetoAtividade'] = $obj->CCAPATCODI;
        $resposta['projetoAtividade'] = $obj->APRJATORDE;
        $resposta['fonte'] = $obj->CFONTERECU;

        // sub elemento de despesa
        $resposta['elemento1'] = $obj->CELED1ELE1;
        $resposta['elemento2'] = $obj->CELED2ELE2;
        $resposta['elemento3'] = sprintf('%02s', $obj->CELED3ELE3);
        $resposta['elemento4'] = sprintf('%02s', $obj->CELED4ELE4);
        $resposta['subElemento'] = $obj->CSUBEDELEM;
        $resposta['elementoDespesa'] = $resposta['elemento1'] . '.' . $resposta['elemento2'] . '.' . $resposta['elemento3'] . '.' . $resposta['elemento4']; // elemento por extenso
        if (! is_null($resposta['subElemento']) and $resposta['subElemento'] != '') {
            $resposta['elementoDespesa'] .= '.' . $resposta['subElemento']; // sub elemento
        }
        // valores bloqueio

        $resposta['valorExercicio'] = $obj->VBLOQUBLOQ;
        $resposta['valorAno1'] = $obj->VBLOQUANO1;
        $resposta['valorAno2'] = $obj->VBLOQUANO2;
        $resposta['valorAno3'] = $obj->VBLOQUANO3;
        $resposta['valorAno4'] = $obj->VBLOQUANO4;
        $resposta['valorAno5'] = $obj->VBLOQUANO5;
        // $resposta['valorTotal'] = ($obj->VBLOQUBLOQ - $obj->VBLOQUDESB) + $obj->VBLOQUANO1 + $obj->VBLOQUANO2 + $obj->VBLOQUANO3 + $obj->VBLOQUANO4 + $obj->VBLOQUANO5;
        $resposta['valorTotal'] = $obj->VBLOQUBLOQ + $obj->VBLOQUANO1 + $obj->VBLOQUANO2 + $obj->VBLOQUANO3 + $obj->VBLOQUANO4 + $obj->VBLOQUANO5;

        if ($obj->FBLOQUHOML == 'S' or is_null($obj->FBLOQUHOML)) {
            $resposta['homologado'] = 'N';
        } else {
            $resposta['homologado'] = 'S';
        }
    }

    return $resposta;
}

/**
 * Retorna todos os dados de dotação usados pelo portal a partir de dotação em string.
 */
function getDadosDotacaoOrcamentaria($dbOracle, $dotacao)
{
    $dotacaoArray = converterDotacaoStringToArray($dotacao);

    return getDadosDotacaoOrcamentariaFromArray($dbOracle, $dotacaoArray);
}

/**
 * Retorna todos os dados de dotação usados pelo portal a partir da chave da dotação.
 */
function getDadosDotacaoOrcamentariaFromChave($dbOracle, $dotacaoAno, $dotacaoOrgao, $dotacaoUnidade, $dotacaoTipoProjeto, $dotacaoProjeto, $dotacaoE1, $dotacaoE2, $dotacaoE3, $dotacaoE4, $dotacaoFonte)
{
    assercao(! is_null($dbOracle), 'Variável de banco de dados Oracle não foi inicializado');
    assercao(! is_null($dotacaoAno), "Parâmetro 'dotacaoAno' requerido");
    assercao(! is_null($dotacaoOrgao), "Parâmetro 'dotacaoOrgao' requerido");
    assercao(! is_null($dotacaoUnidade), "Parâmetro 'dotacaoUnidade' requerido");
    assercao(! is_null($dotacaoTipoProjeto), "Parâmetro 'dotacaoTipoProjeto' requerido");
    assercao(! is_null($dotacaoProjeto), "Parâmetro 'dotacaoProjeto' requerido");
    assercao(! is_null($dotacaoE1), "Parâmetro 'dotacaoE1' requerido");
    assercao(! is_null($dotacaoE2), "Parâmetro 'dotacaoE2' requerido");
    assercao(! is_null($dotacaoE3), "Parâmetro 'dotacaoE3' requerido");
    assercao(! is_null($dotacaoE4), "Parâmetro 'dotacaoE4' requerido");
    assercao(! is_null($dotacaoFonte), "Parâmetro 'dotacaoFonte' requerido");
    $resposta = array();
    $resultado['ano'] = $dotacaoAno;
    $resultado['orgao'] = $dotacaoOrgao;
    $resultado['unidade'] = $dotacaoUnidade;
    $resultado['funcao'] = null;
    $resultado['subfuncao'] = null;
    $resultado['programa'] = null;
    $resultado['tipoProjetoAtividade'] = $dotacaoTipoProjeto;
    $resultado['projetoAtividade'] = $dotacaoProjeto;
    $resultado['elemento1'] = $dotacaoE1;
    $resultado['elemento2'] = $dotacaoE2;
    $resultado['elemento3'] = $dotacaoE3;
    $resultado['elemento4'] = $dotacaoE4;
    $resultado['fonte'] = $dotacaoFonte;

    return getDadosDotacaoOrcamentariaFromArray($dbOracle, $resultado);
}

/**
 * NOTA: Evitar usar esta função fora deste arquivo! Esta função apenas serve para auxiliar as outras funções.
 * Retorna todos os dados de dotação usados pelo portal a partir de dotação no formato de array.
 */
function getDadosDotacaoOrcamentariaFromArray($dbOracle, $dotacaoArray)
{
    assercao(! is_null($dbOracle), 'Variável de banco de dados Oracle não foi inicializado');
    assercao(! is_null($dotacaoArray), "Parâmetro 'dotacaoArray' requerido");
    // aitcdounidoexer, citcdounidoorga, citcdounidocodi, citcdotipa, aitcdoordt, citcdoele1, citcdoele2, citcdoele3, citcdoele4, citcdofont
    $sql = '
        SELECT
        CELED1ELE1, CELED2ELE2, CELED3ELE3, CELED4ELE4, VSLORCINOR, --4
        VSLORCINEX, VSLORCSUPL, VSLORCREDU, VSLORCCRES, VSLORCCREX, --9
        VSLORCEMRE, VSLORCEMAN, VSLORCBLEM, VSLORCBLSU, VSLORCESPE, --14
        VSLORCEXTR, VSLORCBLLP, VSLORCBLGE, VSLORCACRE, VSLORCACRI, --19
        VSLORCRETI, VSLORCBLRE, CFUNCACODI, CPRGORCODI, CSUBPOCODI --24

        FROM SFCO.TBSALDOORCAMENTARI
        WHERE
            DEXERCANOR = ' . $dotacaoArray['ano'] . '
            AND CORGORCODI = ' . $dotacaoArray['orgao'] . '
            AND CUNDORCODI = ' . $dotacaoArray['unidade'] . '
            '/* -- Função, subfunção e programa não fazem parte da chave
            AND CFUNCACODI = ".$dotacaoArray['funcao']."
            AND CPRGORCODI = ".$dotacaoArray['subfuncao']."
            AND CSUBPOCODI = ".$dotacaoArray['programa']."*/.'
            AND CCAPATCODI = ' . $dotacaoArray['tipoProjetoAtividade'] . '
            AND APRJATORDE = ' . $dotacaoArray['projetoAtividade'] . '
            AND CELED1ELE1 = ' . $dotacaoArray['elemento1'] . '
            AND CELED2ELE2 = ' . $dotacaoArray['elemento2'] . '
            AND CELED3ELE3 = ' . $dotacaoArray['elemento3'] . '
            AND CELED4ELE4 = ' . $dotacaoArray['elemento4'] . '
            AND CFONTERECU = ' . $dotacaoArray['fonte'] . '
    ';
    $linha = resultLinhaUnica(executarSQL($dbOracle, $sql));

    if (! is_null($linha)) {
        // dados da dotacao - MASCARA_DOTACAO = "9999.9999.99.9999.9999.9.9.99.99.9999";
        $dotacaoArray['funcao'] = $linha[22];
        $dotacaoArray['subfuncao'] = $linha[23]; // campos subfunção e programa trocados, conforme definição
        $dotacaoArray['programa'] = $linha[24];
        // separar subfunção com ponto
        // $subfuncaoTmp = sprintf("%04s",$dotacaoArray['subfuncao']);
        // $subfuncaoQuebrada = $subfuncaoTmp[0].$subfuncaoTmp[1].$subfuncaoTmp[2].".".$subfuncaoTmp[3];

        $dotacaoArray['dotacao'] = sprintf('%04s', $dotacaoArray['ano']) . '.' . sprintf('%02s', $dotacaoArray['orgao']) . sprintf('%02s', $dotacaoArray['unidade']) . '.' . sprintf('%02s', $dotacaoArray['funcao']) . '.' . sprintf('%04s', $dotacaoArray['subfuncao']) . '.' . sprintf('%04s', $dotacaoArray['programa']) . '.' . sprintf('%01s', $dotacaoArray['tipoProjetoAtividade']) . '.' . sprintf('%03s', $dotacaoArray['projetoAtividade']) . '.' . sprintf('%01s', $dotacaoArray['elemento1']) . '.' . sprintf('%01s', $dotacaoArray['elemento2']) . '.' . sprintf('%02s', $dotacaoArray['elemento3']) . '.' . sprintf('%02s', $dotacaoArray['elemento4']) . '.' . sprintf('%04s', $dotacaoArray['fonte']);

        // sub elemento de despesa
        $dotacaoArray['elemento1'] = $linha[0];
        $dotacaoArray['elemento2'] = $linha[1];
        $dotacaoArray['elemento3'] = $linha[2];
        $dotacaoArray['elemento4'] = $linha[3];
        $dotacaoArray['elementoDespesa'] = $linha[0] . '.' . $linha[1] . '.' . $linha[2] . '.' . $linha[3]; // sub elemento por extenso

        // valores da dotacao
        $dotacaoArray['valorInicialOrcado'] = $linha[4];
        $dotacaoArray['valorInicialExecucao'] = $linha[5];

        $dotacaoArray['VSLORCSUPL'] = $linha[6];
        $dotacaoArray['valorReduzido'] = $linha[7];
        $dotacaoArray['valorSuplementadoCreditoEspecial'] = $linha[8];
        $dotacaoArray['valorSuplementadoCreditoExtraordinario'] = $linha[9];
        $dotacaoArray['valorEmpenhadoRealizado'] = $linha[10];
        $dotacaoArray['valorEmpenhadoAnulado'] = $linha[11];
        $dotacaoArray['valorBloqueadoEmpenhado'] = $linha[12];
        $dotacaoArray['valorBloqueadoSuplementacao'] = $linha[13];
        $dotacaoArray['valorReduzidoCreditoEspecial'] = $linha[14];
        $dotacaoArray['valorReduzidoCreditoExtraordinario'] = $linha[15];
        $dotacaoArray['VSLORCBLLP'] = $linha[16];
        $dotacaoArray['VSLORCBLGE'] = $linha[17];
        $dotacaoArray['valorAcrescimoRemanejamento'] = $linha[18];
        $dotacaoArray['valorInicialRemanejamento'] = $linha[19];
        $dotacaoArray['valorReducaoRemanejamento'] = $linha[20];
        $dotacaoArray['valorBloqueioRemanejamento'] = $linha[21];

        // Calculando valor disponível da dotação
        $dotacaoArray['valorDisponivel'] = $dotacaoArray['valorInicialOrcado'] + $dotacaoArray['valorInicialExecucao'] + $dotacaoArray['VSLORCSUPL'] - $dotacaoArray['valorReduzido'] + $dotacaoArray['valorSuplementadoCreditoEspecial'] + $dotacaoArray['valorSuplementadoCreditoExtraordinario'] - $dotacaoArray['valorEmpenhadoRealizado'] + $dotacaoArray['valorEmpenhadoAnulado'] - $dotacaoArray['valorBloqueadoEmpenhado'] - $dotacaoArray['valorBloqueadoSuplementacao'] - $dotacaoArray['valorReduzidoCreditoEspecial'] - $dotacaoArray['valorReduzidoCreditoExtraordinario'] - $dotacaoArray['VSLORCBLLP'] - $dotacaoArray['VSLORCBLGE'] + $dotacaoArray['valorAcrescimoRemanejamento'] + $dotacaoArray['valorInicialRemanejamento'] - $dotacaoArray['valorReducaoRemanejamento'] - $dotacaoArray['valorBloqueioRemanejamento'];
    } else {
        $dotacaoArray = null;
    }

    return $dotacaoArray;
}

/**
 * Retorna true se string é um (sub) elemento de despesa formatado.
 */
function isElementoDespesa($elementoDespesa)
{
    $numeros = explode('.', $elementoDespesa);
    $resultado = true;

    $qtdeNumeros = count($numeros);
    if ($qtdeNumeros != 5 and $qtdeNumeros != 4) {
        $resultado = false;
    }

    foreach ($numeros as $numero) {
        if (! is_numeric($numero)) {
            $resultado = false;
        }
        ;
    }

    return $resultado;
}

/**
 * Monta o número de solicitação de compra mostrado para o usuário a partir do id
 * O resultado é uma string com o número da solicitação.
 */
function getNumeroSolicitacaoCompra($db, $idSolicitacao)
{
    assercao(! is_null($db), 'Variável de banco de dados não foi inicializada');
    assercao(! is_null($idSolicitacao), "Parâmetro 'idSolicitacao' requerido");
    $sql = "
        select distinct ccenpocorg, ccenpounid, csolcocodi, asolcoanos
          from sfpc.tbsolicitacaocompra scc, SFPC.TBcentrocustoportal cc
         where scc.ccenposequ = cc.ccenposequ
             and csolcosequ = $idSolicitacao
    ";

    $linha = resultObjetoUnico(executarSQL($db, $sql));

    $resposta                       = array();
    $resposta['orgaoSofin']         = $linha->ccenpocorg;
    $resposta['unidadeSofin']       = $linha->ccenpounid;
    $resposta['solicitacao']        = $linha->csolcocodi;
    $resposta['anoSolicitacao']     = $linha->asolcoanos;
    $resposta['numeroSolicitacao']  = sprintf('%02s', $resposta['orgaoSofin']) . sprintf('%02s', $resposta['unidadeSofin']) . '.' . sprintf('%04s', $resposta['solicitacao']) . '.' . $resposta['anoSolicitacao'];

    return $resposta['numeroSolicitacao'];
}

/**
 * Verifica se o número informado está no padrão do número de uma solicitação de compra
 * O resultado é TRUE saso seja um número de uma solicitação de compra, FALSE caso contrário.
 */
function isNumeroSCCValido($numSolicitacao)
{
    assercao(! is_null($numSolicitacao), "Parâmetro 'numSolicitacao' requerido");
    $numSccDecomposto = explode('.', $numSolicitacao);
    $resultado = false;
    if (count($numSccDecomposto) == 3 and strlen($numSccDecomposto[0]) == 4 and strlen($numSccDecomposto[1]) == 4 and strlen($numSccDecomposto[2]) == 4) {
        $resultado = true;
    }

    return $resultado;
}

/**
 * Encontra o sequencial de solicitação de compra a partir do número
 * O resultado é uma string com o sequencial da solicitação.
 * Retornará NULL caso não seja encontrada nenhuma SCC
 * OBS.: Função abortará o PHP caso o numero seja num formato inválido. Para verificar se o formato é válido, usar antes 'isNumeroSCCValido()'.
 */
function getSequencialSolicitacaoCompra($db, $numSolicitacao)
{
    assercao(! is_null($db), 'Variável de banco de dados não foi inicializada');
    assercao(! is_null($numSolicitacao), "Parâmetro 'numSolicitacao' requerido");
    assercao(isNumeroSCCValido($numSolicitacao), "Número de solicitação com formato inválido. /n Número= '" . $numSolicitacao . "'");

    $numSccDecomposto = explode('.', $numSolicitacao);
    $numSccArray = array();
    $numSccArray['numeroSolicitacao'] = $numSolicitacao;
    $numSccArray['orgaoSofin'] = substr($numSccDecomposto[0], 0, 2);
    $numSccArray['unidadeSofin'] = substr($numSccDecomposto[0], 2, 2);
    $numSccArray['solicitacao'] = $numSccDecomposto[1];
    $numSccArray['anoSolicitacao'] = $numSccDecomposto[2];

    $sql = '
        select distinct scc.csolcosequ
        from sfpc.tbsolicitacaocompra scc, SFPC.TBcentrocustoportal cc
        where
            scc.ccenposequ = cc.ccenposequ and
            cc.ccenpocorg = ' . $numSccArray['orgaoSofin'] . ' and
            cc.ccenpounid = ' . $numSccArray['unidadeSofin'] . ' and
            scc.csolcocodi = ' . $numSccArray['solicitacao'] . ' and
            scc.asolcoanos = ' . $numSccArray['anoSolicitacao'] . '
    ';
    $sequencial = resultValorUnico(executarSQL($db, $sql));

    return $sequencial;
}

/**
 * Verifica se uma SCC possui pre-solicitações de empenho e se alguma foi importada pelo SOFIN.
 */
function hasPSEImportadaSofin($db, $idSolicitacao)
{
    assercao(! is_null($db), 'Variável de banco de dados não foi inicializada');
    assercao(! is_null($idSolicitacao), "Parâmetro 'idSolicitacao' requerido");
    $sql = '
        select count(*)
          from sfpc.tbpresolicitacaoempenho
         where csolcosequ = ' . $idSolicitacao . '
             and tpresoimpo is not null
    ';
    $qtdePSEImportadas = resultValorUnico(executarSQL($db, $sql));
    $resposta = true;
    if ($qtdePSEImportadas == 0) {
        $resposta = false;
    }

    return $resposta;
}

/**
 * Verifica se uma SCC possui pre-solicitações de empenho e se alguma foi importada pelo SOFIN.
 */
function hasSSCContrato($db, $idSolicitacao)
{
    assercao(! is_null($db), 'Variável de banco de dados não foi inicializada');
    assercao(! is_null($idSolicitacao), "Parâmetro 'idSolicitacao' requerido");
    $sql = '
        select count(*)
          from sfpc.tbcontratosfpc
         where csolcosequ = ' . $idSolicitacao . '
    ';
    $qtdeContratos = resultValorUnico(executarSQL($db, $sql));
    $resposta = false;
    if ($qtdeContratos > 0) {
        $resposta = true;
    }

    return $resposta;
}

/**
 * Verifica se uma SCC foi autorizada para SARP.
 */
function isAutorizadoSarp($db, $idSolicitacao)
{
    assercao(! is_null($db), 'Variável de banco de dados não foi inicializada');
    assercao(! is_null($idSolicitacao), "Parâmetro 'idSolicitacao' requerido");

    // recuperando dados da SCC
    $sql = '
        select ctpcomcodi, csitsocodi, fsolcocont, fsolcorpcp, fsolcoautc
          from sfpc.tbsolicitacaocompra
         where csolcosequ = ' . $idSolicitacao . '
    ';
    $linha = resultLinhaUnica(executarSQL($db, $sql));
    $tipoScc = $linha[0];
    $situacaoScc = $linha[1];
    $geraContrato = $linha[2];
    $caronaParticipante = $linha[3];
    $isSccAutorizadoSarp = $linha[4];

    $resultado = false;

    if ($tipoScc == TIPO_COMPRA_SARP) {
        if ($caronaParticipante == 'C') {
            // tipo carona. verificando se scc foi autorizada pra sarp
            if ($isSccAutorizadoSarp == 'S') {
                $resultado = true;
            }
        } elseif ($caronaParticipante == 'P') {
            // tipo participante. verificando se licitação da SCC foi autorizada pra sarp
            $sql = '
                select  clicpoproc as pro, alicpoanop as ano, cgrempcodi as gru, ccomlicodi as com , corglicodi as org
                from sfpc.tbsolicitacaolicitacaoportal
                where csolcosequ = ' . $idSolicitacao . '
            ';

            $result = executarTransacao($db, $sql);

            if ($row = $result->fetchRow(DB_FETCHMODE_OBJECT)) {
                $pro = $row->pro;
                $ano = $row->ano;
                $gru = $row->gru;
                $com = $row->com;
                $org = $row->org;
                // licitação encontrada
                $sql = "
                    select flicpoautp as indsarp
                      from sfpc.tblicitacaoportal
                     where clicpoproc=$pro
                       and alicpoanop=$ano
                       and cgrempcodi=$gru
                       and ccomlicodi=$com
                       and corglicodi=$org
                ";

                $result = executarTransacao($db, $sql);
                $row = $result->fetchRow(DB_FETCHMODE_OBJECT);

                // $isLicitacaoAutorizadaSarp = resultValorUnico( executarSQL($db, $sql ) );
                if ($row->indsarp == 'S') {
                    $resultado = true;
                }
            }
        }
    }

    return $resultado;
}

/**
 * classes para auxiliar a geração de PSE.
 */

/**
 * classe que garda estrutura de item de empenho de scc.
 */
class ItemPreSccEmpenho
{

    public $ano;

    public $sequencialPreSolicitacaoEmpenho;

    public $sequencialItemPreSolicitacaoEmpenho;

    public $sequencialMaterial;

    public $sequencialServico;

    public $quantidadeItemScc;

    public $valorUnitarioItemScc;

    public $quantidadeEmpenhoItemScc;

    public $valorEmpenhoItemScc;

    public function __construct($ano, $sequencialPreSolicitacaoEmpenho, $sequencialItemPreSolicitacaoEmpenho, $sequencialMaterial, $sequencialServico, $quantidadeItemScc, $valorUnitarioItemScc, $quantidadeEmpenhoItemScc, $valorEmpenhoItemScc)
    {
        assercao(! is_null($ano), 'Variável ano não pode ser nula');
        assercao(! is_null($sequencialPreSolicitacaoEmpenho), 'Variável sequencialPreSolicitacaoEmpenho não pode ser nula');
        assercao(! is_null($sequencialItemPreSolicitacaoEmpenho), 'Variável sequencialItemPreSolicitacaoEmpenho não pode ser nula');
        assercao(! is_null($sequencialMaterial) or ! is_null($sequencialServico), 'Material ou serviço devem ser informados');
        assercao(! is_null($quantidadeItemScc), 'Variável quantidadeItemScc não pode ser nula');

        $this->ano = $ano;
        $this->sequencialPreSolicitacaoEmpenho = $sequencialPreSolicitacaoEmpenho;
        $this->sequencialItemPreSolicitacaoEmpenho = $sequencialItemPreSolicitacaoEmpenho;
        $this->sequencialMaterial = $sequencialMaterial;
        $this->sequencialServico = $sequencialServico;
        $this->quantidadeItemScc = $quantidadeItemScc;
        $this->valorUnitarioItemScc = $valorUnitarioItemScc;
        $this->quantidadeEmpenhoItemScc = $quantidadeEmpenhoItemScc;
        $this->valorEmpenhoItemScc = $valorEmpenhoItemScc;
    }
}

/**
 * Classe que garda estrutura do empenho de scc.
 */
class PreSccEmpenho
{

    public $ano;

    public $sequencialPreSolicitacaoEmpenho;

    public $sccSequencial;

    public $fornecedorSequencial;

    public $grupoEmpresa;

    public $licitacaoProcesso;

    public $licitacaoAno;

    public $licitacaoComissao;

    public $licitacaoOrgao;

    public $licitacaoModalidade;

    public $sccRegistroPreco;

    public $codigoDocumento;

    public $descricaoTipoEmpenhamento;

    public $isSaldoBloqueioLiberado;

    public $textoComplementar;

    public $dataCancelamento;

    public $bloqueioNormalComplementar;

    public $dataImportacaoSofin;

    public $dataMotivoNaoImportacaoSofin;

    public $dataGeracaoEmpenho;

    public $dataAnulacaoEmpenho;

    public $valorAnulado;

    public $sofinNumEmpenho;

    public $sofinAnoEmpenho;

    public $sofinNumEmpenhoPorOrgao;

    public $valdoEmpenho;

    public $vigenciaDispensaInexigibidade;

    public $tipoContrato;

    public $contratoSequencial;

    public $AnoContrato;

    public $bloqueioSequencial;

    public $bloqueioAno;

    public $itemPreSccEmpenhos;

    public function __construct($ano, $sccSequencial, $sequencialPreSolicitacaoEmpenho, $fornecedorSequencial, $grupoEmpresa, $licitacaoProcesso, $licitacaoAno, $licitacaoComissao, $licitacaoOrgao, $licitacaoModalidade, $sccRegistroPreco, $codigoDocumento, $descricaoTipoEmpenhamento, $isSaldoBloqueioLiberado, $textoComplementar, $dataCancelamento, $bloqueioNormalComplementar, $dataImportacaoSofin, $dataMotivoNaoImportacaoSofin, $dataGeracaoEmpenho, $dataAnulacaoEmpenho, $valorAnulado, $sofinNumEmpenho, $sofinAnoEmpenho, $sofinNumEmpenhoPorOrgao, $valdoEmpenho, $vigenciaDispensaInexigibidade, $tipoContrato, $contratoSequencial, $AnoContrato, $bloqueioNumero, $bloqueioAno)
    {
        $this->ano = $ano;
        $this->$sccSequencial = $sccSequencial;
        $this->sequencialPreSolicitacaoEmpenho = $sequencialPreSolicitacaoEmpenho;
        $this->fornecedorSequencial = $fornecedorSequencial;
        $this->grupoEmpresa = $grupoEmpresa;
        $this->licitacaoProcesso = $licitacaoProcesso;
        $this->licitacaoAno = $licitacaoAno;
        $this->licitacaoComissao = $licitacaoComissao;
        $this->licitacaoOrgao = $licitacaoOrgao;
        $this->licitacaoModalidade = $licitacaoModalidade;
        $this->sccRegistroPreco = $sccRegistroPreco;
        $this->codigoDocumento = $codigoDocumento;
        $this->descricaoTipoEmpenhamento = $descricaoTipoEmpenhamento;
        $this->isSaldoBloqueioLiberado = $isSaldoBloqueioLiberado;
        $this->textoComplementar = $textoComplementar;
        $this->dataCancelamento = $dataCancelamento;
        $this->bloqueioNormalComplementar = $bloqueioNormalComplementar;
        $this->dataImportacaoSofin = $dataImportacaoSofin;
        $this->dataMotivoNaoImportacaoSofin = $dataMotivoNaoImportacaoSofin;
        $this->dataGeracaoEmpenho = $dataGeracaoEmpenho;
        $this->dataAnulacaoEmpenho = $dataAnulacaoEmpenho;
        $this->valorAnulado = $valorAnulado;
        $this->sofinNumEmpenho = $sofinNumEmpenho;
        $this->sofinAnoEmpenho = $sofinAnoEmpenho;
        $this->sofinNumEmpenhoPorOrgao = $sofinNumEmpenhoPorOrgao;
        $this->valdoEmpenho = $valdoEmpenho;
        $this->vigenciaDispensaInexigibidade = $vigenciaDispensaInexigibidade;
        $this->tipoContrato = $tipoContrato;
        $this->contratoSequencial = $contratoSequencial;
        $this->AnoContrato = $AnoContrato;
        $this->bloqueioSequencial = $bloqueioNumero;
        $this->bloqueioAno = $bloqueioAno;
        $this->itemPreSccEmpenhos = array();
    }

    public function addItem(ItemPreSccEmpenho $item)
    {
        $this->itemPreSccEmpenhos[count($this->itemPreSccEmpenhos)] = $item;
    }
}

/**
 * Classe que guarda todos empenhos e itens de uma scc.
 */
class PreSccEmpenhos
{

    public $preSccEmpenhos;

    public function __construct()
    {
        $this->preSccEmpenhos = array();
    }

    public function addEmpenho(PreSccEmpenho $item)
    {
        $this->preSccEmpenhos[count($this->preSccEmpenhos)] = $item;
    }

    /**
     * Retorna um empenho pelo fornecedor e bloqueio.
     */
    public function getEmpenhoPorFornecedorBloqueio($fornecedorSequencial, $bloqueioAno, $bloqueioSequencial)
    {
        $resultado = null;
        foreach ($this->preSccEmpenhos as $empenho) {
            if ($empenho->fornecedorSequencial == $fornecedorSequencial and $empenho->bloqueioAno == $bloqueioAno and $empenho->bloqueioSequencial == $bloqueioSequencial) {
                $resultado = $empenho;
            }
        }

        return $resultado;
    }

    /**
     * Retorna um item de empenho pelo fornecedor, bloqueio e material/servico.
     */
    public function getItemEmpenhoPorFornecedorBloqueioItemScc($fornecedorSequencial, $bloqueioAno, $bloqueioSequencial, $materialSequencial, $servicoSequencial)
    {
        assercao((is_null($materialSequencial) and ! is_null($servicoSequencial)) or (! is_null($materialSequencial) and is_null($servicoSequencial)), 'um item de material ou um item de serviço deve ser informado.');
        $resultado = null;
        $empenho = $this->getEmpenhoPorFornecedorBloqueio($fornecedorSequencial, $bloqueioAno, $bloqueioSequencial);

        if (! is_null($empenho)) { // se empenho já está cadastrado
            if (! is_null($empenho->itemPreSccEmpenhos)) { // se empenho possui itens de empenhos cadastrados
                foreach ($empenho->itemPreSccEmpenhos as $itemEmpenho) {
                    if (! is_null($materialSequencial) and $itemEmpenho->sequencialMaterial == $materialSequencial) {
                        $resultado = $itemEmpenho;
                    } elseif (! is_null($servicoSequencial) and $itemEmpenho->sequencialServico == $servicoSequencial) {
                        $resultado = $itemEmpenho;
                    }
                }
            }
        }
        return $resultado;
    }

    /**
     * Calcula todo valor empenhado para um bloqueio, para toda SCC.
     */
    public function getValorBloqueioEmpenhado($bloqueioAno, $bloqueioSequencial)
    {
        $valorBloqueioEmpenhado = 0;
        // soma todas as pre-sccs já calculadas com o bloqueio informado
        foreach ($this->preSccEmpenhos as $empenho) {
            if ($empenho->bloqueioAno == $bloqueioAno and $empenho->bloqueioSequencial == $bloqueioSequencial) {
                foreach ($empenho->itemPreSccEmpenhos as $itemEmpenho) {
                    $valorBloqueioEmpenhado += $itemEmpenho->valorEmpenhoItemScc;
                }
            }
        }

        return $valorBloqueioEmpenhado;
    }

    /**
     * Retorna a quantidade do item da SCC de um fornecedor que já foi calculado para item PSE (para PSEs que tem item).
     */
    public function getQtdeItemScc($materialSequencial, $servicoSequencial, $fornecedorSequencial)
    {
        $qtdeItemSccEmpenhado = 0;
        foreach ($this->preSccEmpenhos as $empenho) {
            if ($empenho->fornecedorSequencial = $fornecedorSequencial) {
                foreach ($empenho->itemPreSccEmpenhos as $itemEmpenho) {
                    if ($itemEmpenho->sequencialMaterial == $materialSequencial and $itemEmpenho->sequencialServico == $servicoSequencial) {
                        $qtdeItemSccEmpenhado += $itemEmpenho->quantidadeItemScc;
                    }
                }
            }
        }

        return $qtdeItemSccEmpenhado;
    }

    /**
     * Retorna a quantidade do item da SCC que será pago pelo próximo item de PSE a ser calculado,
     * levando em consideração um bloqueio e as quantidades já pagas deste item SCC (por itens de PSE já calculados).
     */
    public function calcularQtdeItemSccEmpenho($dbOracle, $bloqueioAno, $bloqueioSequencial, $fornecedorSequencial, $materialSequencial, $servicoSequencial, $valorUnitario, $quantidadeItemScc)
    {
        $bloqueio = getDadosBloqueioFromChave($dbOracle, $bloqueioAno, $bloqueioSequencial);
        $valorBloqueio = $bloqueio['valorTotal'];
       
        $valorBloqueioUsado = $this->getValorBloqueioEmpenhado($bloqueioAno, $bloqueioSequencial); // valor do bloqueio usado nos itens das PSEs anteriores
        $valorBloqueioRestante = $valorBloqueio - $valorBloqueioUsado; // valor do bloqueio disponível para os itens PSEs restantes
        $qtdeItemSccUsada = $this->getQtdeItemScc($materialSequencial, $servicoSequencial, $fornecedorSequencial); // quantidade do material/servico que já foram calculados usando bloqueios anteriores
        $qtdeItemSccRestante = $quantidadeItemScc - $qtdeItemSccUsada; // quantidade do material/serviço que ainda falta para gerar PSEs
        $qtdeItemSccEmpenho = $valorBloqueioRestante / $valorUnitario; // calcula quantos materiais o bloqueio pode pagar
        $quantidadeItemPSE = $qtdeItemSccRestante;
        if ($qtdeItemSccRestante > $qtdeItemSccEmpenho) { // se bloqueio não pode pagar os itens restantes...
            $quantidadeItemPSE = $qtdeItemSccEmpenho; // ...informe a quantidade que o bloqueio pode pagar
        }
        return $quantidadeItemPSE;
    }
}

/**
 * Excessão usada caso uma função foi mal sucedida em desempenhar sua função, devido a pendências do usuário (bloqueio inválido, fornecedor inabilitado, etc)
 * NOTA:
 * Deve-se usar esta excessão em funções e métodos que detectaram uma pendência que deve ser informada ao usuário.
 * Ela deve ser usada apenas em erros tratados que são informados ao usuário.
 * Funções que usar esta excessão devem ser usadas com try-catch. A mensagem da excessão deve ser informada ao usuário.
 */
class ExcecaoPendenciasUsuario extends Excecao
{

    public function __construct($mensagem)
    {
        parent::__construct($mensagem);
    }
}

/**
 * Excessão usada caso uma função foi mal sucedida pois a validação de um dos bloqueios ou dotações falhou.
 * A Excessão informa também qual item de SCC e qual bloqueio/dotação
 * NOTA: Usada apenas na função validarReservaOrcamentariaScc.
 */
class ExcecaoReservaInvalidaEmItemScc extends ExcecaoPendenciasUsuario
{

    public $posicaoItemArray;
    // Posição do item da solicitação de compra no array HTML
    public $tipoItem;
    // se o item é material ou serviço
    public $codMaterialServicoItem;
    // código do material ou servico
    public $noReserva;
    // número do bloqueio ou dotação
    public $tipoReserva;
    // se reserva é bloqueio ou dotacao
    public function __construct($mensagem, $posicaoItem, $tipoItem, $codMaterialServicoItem, $noReserva, $tipoReserva)
    {
        parent::__construct($mensagem);
        $this->posicaoItemArray = $posicaoItem;
        $this->tipoItem = $tipoItem;
        $this->codMaterialServicoItem = $codMaterialServicoItem;
        $this->noReserva = $noReserva;
        $this->tipoReserva = $tipoReserva;
    }
}

/**
 * Gera pre-solicitação de empenho.
 * Parâmetros:
 * $db - Conexão com o banco do portal. Já deve estar conectado para uso da função
 * $dbOracle - Conexão com o banco do SOFIN. Já deve estar conectado para uso da função
 * $idSolicitacao - id da solicitação em que deve ser gerado os pre-empenhos
 * Joga excessão: ExcecaoPendenciasUsuario
 * OBSERVAÇÕES:
 * * A função joga excessão ExcecaoPendenciasUsuario caso não seja possível gerar os pré-empenhos,
 * portando deve-se usar esta função usando try-catch.
 * * Esta função deve fazer parte de uma transação maior, como por exemplo inclusão de solicitação de compra. Portanto,
 * é necessário que uma transação de banco ja tenha iniciado, e seja finalizada fora desta função.
 * Esta função espera 3 condições: que o comando SQL 'BEGIN TRANSACTION' já tenha sido executado, que a variável
 * global 'iniciouTransacaoBanco' seja TRUE, e que a transação seja finalizada fora da função. falta de uma destas 3 condições
 * pode gerar erros na transação.
 */

/**
 * Geração de PSE INABILITADA! Tarefa Redmine #26888.
 */
function gerarPreSolicitacaoEmpenho($db, $dbOracle, $idSolicitacao)
{
    $resposta['erro'] = 0;
    /*
     * assercao(!is_null($db), "Variável do banco de dados PostgreSQL não foi inicializada");
     * assercao(!is_null($dbOracle), "Variável do banco de dados Oracle não foi inicializada");
     * assercao(!is_null($idSolicitacao), "Parâmetro 'idSolicitacao' requerido");
     * assercao( $GLOBALS["iniciouTransacaoBanco"],
     * "Variável global 'iniciouTransacaoBanco' é requerida. NOTE: esta função executa comandos de transação e foi feita para ser executada como parte de outras transações. Portanto, a transação deve
     * ser criada e fechada fora desta função via funções 'executarTransacao()' e 'finalizarTransacao()'"
     * );
     * # Recuperando dados da SCC informada
     * $sql = "
     * select ctpcomcodi, csitsocodi, fsolcocont, fsolcorgpr, ccenposequ, csolcotipcosequ, dsolcodpdo
     * from sfpc.tbsolicitacaocompra
     * where csolcosequ = ".$idSolicitacao."
     * ";
     * $obj = resultObjetoUnico( executarSQL($db, $sql ) );
     * $tipoScc = $obj->ctpcomcodi;
     * $tipoCompraPSE = $tipoScc;
     * $situacaoScc = $obj->csitsocodi;
     * $geraContrato = $obj->fsolcocont;
     * $registroPreco = $obj->fsolcorgpr;
     * $centroCusto = $obj->ccenposequ;
     * $idSCCAnoOrgaoTipo = $obj->csolcotipcosequ;
     * $dataDOM = $obj->dsolcodpdo;
     *
     * $objetoCC = getCentroCusto($db, $centroCusto);
     * $orgaoSofin = $objetoCC['orgaoSofin'];
     * $unidadeSofin = $objetoCC['unidadeSofin'];
     *
     * # dado da licitação pra ser gravado no banco. Serão NULL exceto o tipo licitação sem registro de preço
     * $licitacaoProcesso = 'null';
     * $licitacaoAno = 'null';
     * $licitacaoGrupoEmpresa = 'null';
     * $licitacaoComissao = 'null';
     * $licitacaoOrgao = 'null';
     * $licitacaoModalidade = 'null';
     *
     * $camposChaveLicitacao = '(clicpoproc, alicpoanop, cgrempcodi, ccomlicodi, corglicodi)'; // campos da chave de uma licitação
     * $chaveLicitacao = null;
     * $licitacaoDataAbertura = 'null';
     * $licitacaoDataFaseHomologacao = 'null';
     * $licitacaoDataVigencia = 'null';
     *
     * $isSarp='N';
     * $dataPublicacaoDispensaInex = 'null';
     * $dataVigenciaDispensaInex = 'null';
     *
     * $modo = 'S'; // modo da geração da PSE. 'S' para SCC e 'L' para licitação
     * $isDotacao = false;
     * if ($tipoScc==TIPO_COMPRA_SARP) {
     * $isSarp='S';
     * }
     * if ($tipoScc==TIPO_COMPRA_DISPENSA or $tipoScc==TIPO_COMPRA_INEXIGIBILIDADE) {
     * $dataPublicacaoDispensaInex = $dataDOM;
     * $data = new DateTime($dataDOM);
     * $data->setDate($data->format("Y"),$data->format("m"),$data->format("d") - diasVigenciaLicitacao($db));
     * $dataVigenciaDispensaInex = "'".$data->format("m-d-Y")."'";
     * $dataPublicacaoDispensaInex = "'".$dataPublicacaoDispensaInex."'";
     * }
     * $sql = "
     * select epargesubo, epargesubm
     * from sfpc.tbparametrosgerais
     * ";
     * $objprm = resultObjetoUnico( executarSQL($db, $sql ) );
     * $subElementosObras = split(',',$objprm->epargesubo);
     * $subElementosMenor = split(',',$objprm->epargesubm);
     *
     * if (is_null($objprm)) {
     * throw new ExcecaoPendenciasUsuario('Tabela de parâmetros gerais está vazia');
     * } elseif (is_null($obj)) {
     * throw new ExcecaoPendenciasUsuario('Solicitação não foi encontrada');
     * } elseif ($situacaoScc != TIPO_SITUACAO_SCC_PENDENTE_DE_EMPENHO) {
     * throw new ExcecaoPendenciasUsuario('Não é possivel gerar pre-solicitação de empenho pois a solicitação não é pendente de empenho');
     * } elseif ($geraContrato == 'S') {
     * throw new ExcecaoPendenciasUsuario('Não é possivel gerar pre-solicitação de empenho pois a solicitação ainda deve gerar contrato');
     * } elseif ($tipoScc == TIPO_COMPRA_SARP) {
     * if (!isAutorizadoSarp($db, $idSolicitacao)) {
     * throw new ExcecaoPendenciasUsuario('Não é possivel gerar pre-solicitação de empenho pois a solicitação é tipo SARP mas não foi autorizada');
     * }
     * } elseif ($tipoScc == TIPO_COMPRA_LICITACAO) {
     * if ($registroPreco=='S') {
     * throw new ExcecaoPendenciasUsuario('Não é possivel gerar pre-solicitação de empenho pois a solicitação é tipo LICITAÇÃO com registro de preço');
     * } else {
     * $modo = 'L';
     *
     * //Verificar se licitação é fase de homologação
     * $sql = "
     * select
     * ".$camposChaveLicitacao." as chave,
     * clicpoproc, alicpoanop, cgrempcodi, ccomlicodi, corglicodi
     * from sfpc.tbsolicitacaolicitacaoportal
     * where csolcosequ = ".$idSolicitacao."
     * ";
     *
     * $res = executarSQL($db, $sql );
     * if ($res->numRows()<=0) {
     * throw new ExcecaoPendenciasUsuario('Não é possivel gerar pre-solicitação de empenho pois a solicitação é tipo LICITAÇÃO mas não possui uma licitação associada');
     * } else {
     * $objeto = $res->fetchRow(DB_FETCHMODE_OBJECT);
     * $chaveLicitacao = $objeto->chave;
     * $licitacaoProcesso = $objeto->clicpoproc;
     * $licitacaoAno = $objeto->alicpoanop;
     * $licitacaoGrupoEmpresa = $objeto->cgrempcodi;
     * $licitacaoComissao = $objeto->ccomlicodi;
     * $licitacaoOrgao = $objeto->corglicodi;
     *
     * # pegando dados da licitação
     * $sql = "
     * select cmodlicodi, TLICPODHAB
     * from sfpc.tblicitacaoportal
     * where ".$camposChaveLicitacao." in (".$chaveLicitacao.")
     * ";
     * $obj= resultObjetoUnico( executarSQL($db, $sql ) );
     * $licitacaoDataAbertura = $obj->tlicpodhab;
     * $licitacaoModalidade = $obj->cmodlicodi;
     * $licitacaoDataAbertura = "'".$licitacaoDataAbertura."'";
     *
     * # pegando a ultima fase da licitação
     * $sql="
     * select cfasescodi, tfaseldata
     * from sfpc.tbfaselicitacao
     * where ".$camposChaveLicitacao." in (".$chaveLicitacao.")
     * and tfaselulat = (
     * select max(tfaselulat)
     * from sfpc.tbfaselicitacao
     * where ".$camposChaveLicitacao." in (".$chaveLicitacao.")
     * )
     * ";
     * $result = executarSQL($db, $sql );
     * $row = $result->fetchRow(DB_FETCHMODE_OBJECT);
     * $codFaseLicitacao = $row->cfasescodi;
     *
     * if ($codFaseLicitacao != $GLOBALS['FASE_LICITACAO_HOMOLOGACAO']) {
     * throw new ExcecaoPendenciasUsuario('Não é possivel gerar pre-solicitação de empenho pois a solicitação é tipo LICITAÇÃO mas a licitação não está na fase de homologada');
     * } else {
     * //$licitacaoDataFaseHomologacao = $obj->tfaseldata;
     * $licitacaoDataFaseHomologacao = $row->tfaseldata;
     *
     * $data = new DateTime($licitacaoDataFaseHomologacao);
     * $data->setDate($data->format("Y"),$data->format("m"),$data->format("d") - diasVigenciaLicitacao($db));
     * $licitacaoDataVigencia = $data->format("Y-m-d");
     * $licitacaoDataFaseHomologacao = "'".$licitacaoDataFaseHomologacao."'";
     * $licitacaoDataVigencia = "'".$licitacaoDataVigencia."'";
     * }
     * }
     * }
     * } else if(
     * (
     * $tipoScc == TIPO_COMPRA_DIRETA
     * or $tipoScc == TIPO_COMPRA_DISPENSA
     * or $tipoScc == TIPO_COMPRA_INEXIGIBILIDADE
     * )
     * ){
     * //area reservada para restrições específicas para compra direta, dispensa ou inexib.
     * } else {
     * throw new ExcecaoPendenciasUsuario('Não é possivel gerar pre-solicitação de empenho pois a solicitação não é de um tipo permitido');
     * }
     *
     * if (hasPSEImportadaSofin($db, $idSolicitacao)) {
     * throw new ExcecaoPendenciasUsuario('Pre-solicitação de empenho já foi gerada para solicitação de compra e SOFIN já efetuou a importação dos dados.');
     * }
     *
     * //if (!$resposta['erro']) {
     * #############################################################
     * #### Neste ponto a solicitação deve estar apta para geração de pré-empenho e não deve possuir PSEs já processadas pelo SOFIN.
     * #### Iniciando geração de PSE.
     * #############################################################
     *
     * # setando modo (S- solicitação, L - licitação), e preenchendo variáveis necessárias
     * if ($modo=='S') {
     * $strSolicitacao = $idSolicitacao;
     * $condicaoFonte = " csolcosequ = ".$idSolicitacao." "; // informa qual a condição where para associar á fonte dos dados. licitação ou scc
     * } elseif ($modo=='L') {
     * $strSolicitacao = 'null';
     * $condicaoFonte = " ".$camposChaveLicitacao." in (".$chaveLicitacao.") ";
     * } else {
     * assercao(false, 'modo inválido. abortando.');
     * }
     *
     * //$preSccEmpenhos = new PreSccEmpenhos();
     *
     * $ultimoItem = null;
     * $ultimoFornecedor = null;
     * $ultimoBloqueio = null;
     *
     *
     * # Apagando PSEs para re-gerar
     * $sql="
     * select
     * '( '||apresoanoe||', '||cpresosequ||')' as chave
     * from SFPC.TBPRESOLICITACAOEMPENHO
     * where ".$condicaoFonte."
     * ";
     * $resPSE = executarSQL($db, $sql );
     * while ($pse = $resPSE->fetchRow(DB_FETCHMODE_OBJECT)) {
     * $sql="DELETE FROM SFPC.TBITEMPRESOLICITACAOEMPENHO WHERE (apresoanoe, cpresosequ) = ".$pse->chave."";
     * executarTransacao($db, $sql);
     * }
     *
     * $sql="DELETE FROM SFPC.TBPRESOLICITACAOEMPENHO WHERE ".$condicaoFonte."";
     * executarTransacao($db, $sql);
     *
     * # fornecedores e valor de cada um
     * if ($modo=='L') {
     * # para licitação, pegar apenas itens logrados e ignorar os não-logrados
     * $sql="
     * select distinct aforcrsequ, sum( vitelpunit * aitelpqtso ) as valor
     * from sfpc.tbitemlicitacaoportal
     * where ".$condicaoFonte." and fitelplogr = 'S'
     * group by aforcrsequ
     * order by aforcrsequ
     * ";
     * } else {
     * $sql="
     * select distinct aforcrsequ, sum(vitescunit * aitescqtso ) as valor
     * from sfpc.tbitemsolicitacaocompra
     * where ".$condicaoFonte."
     * group by aforcrsequ
     * order by aforcrsequ
     * ";
     * }
     *
     *
     * $resItem = executarSQL($db, $sql );
     * $ano = Date('Y');
     *
     * if ($resItem->numRows()<=0) {
     * cancelarTransacao($db);
     * throw new ExcecaoPendenciasUsuario('Falha ao gerar pre-solicitação de empenho pois a fonte não possui nenhum item');
     * }
     * //assercao($resItem->numRows()>0, "Fonte não possui itens!");
     *
     * # Para cada fornecedor...
     * $preSccEmpenhoSeq = -1;
     *
     * while ($itemScc = $resItem->fetchRow(DB_FETCHMODE_OBJECT)) {
     * $fornecedorSeq = $itemScc->aforcrsequ;
     * if (is_null($fornecedorSeq)) {
     * cancelarTransacao($db);
     * throw new ExcecaoPendenciasUsuario('Falha ao gerar pre-solicitação de empenho pois a solicitação/licitação possui 1 ou mais itens sem fornecedor e o empenho deve possuir um fornecedor');
     * }
     *
     * $fornecedorValor = $itemScc->valor;
     *
     * $objFornecedor = getFornecedor($db, $fornecedorSeq);
     *
     * $fornecedorCPF = $objFornecedor['CPF'];
     * $fornecedorCNPJ = $objFornecedor['CNPJ'];
     *
     * $sql="
     * select max(cpresosequ)
     * from sfpc.tbpresolicitacaoempenho
     * where apresoanoe = ".$ano."
     * ";
     * $preSccEmpenhoSeq = resultValorUnico( executarSQL($db, $sql ));
     * if (is_null($preSccEmpenhoSeq)) {
     * $preSccEmpenhoSeq = 1;
     * } else {
     * $preSccEmpenhoSeq ++;
     * }
     * $sql ="
     * INSERT INTO sfpc.tbpresolicitacaoempenho(
     * apresoanoe, cpresosequ, csolcosequ, aforcrsequ, cgrempcodi, clicpoproc,
     * alicpoanop, ccomlicodi, corglicodi, cmodlicodi, cdocpcsequ, epresodese,
     * epresotexc, fpresobloq, tpresoimpo, cmotnicodi, dpresogere, dpresoanue,
     * vpresoanue, apresonues, apresoanes, apresoseqe, vpresosald, dpresodvig,
     * cpresotipc, cpresonume, apresoanoc, cusupocodi, tpresoulat, apresonbloq,
     * apresoanob, dpresocsem, cpresoorga, cpresounid, cpresocscc, cpresocnpj,
     * cpresoccpf, dpresoaber, dpresohomo, dpresovige, fpresoobra, vpresoperr,
     * fpresoindv, fpresosarp, dpresopubl, cdocpcseq1, cdocpcseq2, cpresonumf,
     * dpresoinic, dpresovigc, tpresogera, CTPCOMCODI, vpresoempn)
     * VALUES (
     * $ano, $preSccEmpenhoSeq, $strSolicitacao, $fornecedorSeq, $licitacaoGrupoEmpresa, $licitacaoProcesso,
     * $licitacaoAno, $licitacaoComissao, $licitacaoOrgao, $licitacaoModalidade, null, null,
     * null, null, null, null, null, null,
     * null, null, null, null, null, $dataVigenciaDispensaInex,
     * null, null, null, ".$_SESSION['_cusupocodi_'].", now(), null,
     * null , null, $orgaoSofin, $unidadeSofin, $idSCCAnoOrgaoTipo, ".converterValorParaSql($fornecedorCNPJ).",
     * ".converterValorParaSql($fornecedorCPF).", $licitacaoDataAbertura, $licitacaoDataFaseHomologacao, $licitacaoDataVigencia, null , 99.99,
     * null, ".converterValorParaSql($isSarp).", $dataPublicacaoDispensaInex, null, null, null,
     * null, null, now(), $tipoCompraPSE, $fornecedorValor
     * );
     * ";
     *
     * executarTransacao($db, $sql);
     *
     * }
     * finalizarTransacao($db);
     *
     * return $resposta;
     */
}

/**
 * retorna resposta ao usuário.
 * caso $respostaComoMensagem = false, função retorna como throw, e deve ser tratado por fora, e só mostra 1 mensagem por vez.
 */
function erroUsuario($respostaComoMensagem, $nomeCampo, $mensagem, $posicaoItem = null, $tipoItem = null, $codMaterialServicoItem = null, $noReserva = null, $tipoReserva = null)
{
    if (is_null($posicaoItem)) {
        // mensagem não-associada a um item de compra
        if (! $respostaComoMensagem) {
            throw new ExcecaoReservaInvalidaEmItemScc($mensagem, $posicaoItem, $tipoItem, $codMaterialServicoItem, $noReserva, $tipoReserva);
        } else {
            if (is_null($nomeCampo)) {
                adicionarMensagem($mensagem, $GLOBALS['TIPO_MENSAGEM_ERRO']);
            } else {
                adicionarMensagem("<a href=\"javascript:document.getElementById('" . $nomeCampo . "').focus();\" class='titulo2'>" . $mensagem . '</a>', $GLOBALS['TIPO_MENSAGEM_ERRO']);
            }
        }
    } else {
        // mensagem associada a um item de compra
        if (! $respostaComoMensagem) {
            throw new ExcecaoReservaInvalidaEmItemScc($mensagem, $posicaoItem, $tipoItem, $codMaterialServicoItem, $noReserva, $tipoReserva);
        } else {
            adicionarMensagem("<a href=\"javascript:document.getElementById('" . $nomeCampo . '_' . $posicaoItem . "').focus();\" class='titulo2'>" . $mensagem . '</a>', $GLOBALS['TIPO_MENSAGEM_ERRO']);
        }
    }
}

/**
 * valida bloqueios e dotações de itens de solicitação de compra ou licitação
 * Parâmetros:
 * $db - Conexão com o banco do portal.
 * Já deve estar conectado para uso da função
 * $dbOracle - Conexão com o SOFIN. Já deve estar conectado para uso da função
 * $tipoReserva - Se é bloqueio ou dotação
 * $arrayReservas - Array com os bloqueios ou dotações.
 * $arrayItens - Array com os dados dos itens da SCC ou licitação, incluindo os bloqueios/dotações que estes itens tem.
 * $nomeCampo - Nome do campo dos bloqueios/dotações, para a mensagem de erro colocar no foco quando for clicada
 * A estrutura esperada do array esperada é:
 * $arrayItens
 * +- ['posicao'] - posição do item no ARRAY (no formulario HTML) dos itens da scc, separadas por material ou serviço
 * +- ['quantidadeItem'] - quantidade do item
 * +- ['valorItem'] - valor do item
 * Retorna: nada.
 * Quando a validação detecta um erro, é usado a função adicionarMensagem(),
 * que grava nas variáveis globais Mensagem e Mens. Esta mensagem deve ser mostrada ao usuário, na tela, conforme ocorre em todas as páginas.).
 */
function validarReservaOrcamentaria($db, $dbOracle, $tipoReserva, $arrayReservas, $arrayItens, $nomeCampo, $TipoCompra = '', $codigoUsuarioPerfil, $CompromissoValor, $RegistroPreco = '', $field='valorItem')
{
    $respostaComoMensagem = true;

    assercao(! is_null($db), 'Variável do banco de dados não foi inicializada');
    assercao(! is_null($dbOracle), 'Variável do banco de dados Oracle não foi inicializada');
    assercao(! is_null($nomeCampo), "Parâmetro 'nomeCampo' requerido");
    assercao(! is_null($tipoReserva), "Parâmetro 'tipoReserva' requerido");
    // assercao(!is_null($arrayReservas), "Parâmetro 'arrayReservas' requerido");
    assercao(is_null($arrayReservas) or is_array($arrayReservas), "Parâmetro 'arrayReservas' deve ser um array");
    assercao(! is_null($arrayItens), "Parâmetro 'arrayItens' requerido");
    assercao(is_array($arrayItens), "Parâmetro 'arrayItens' deve ser um array");

    $valorTotalScc = 0;
    $bloqueiosValoresTotais = 0;
    $arrayPos = - 1;
    //Madson Adaptção para permitir que Compra direta não obrigue a inserção de Bloqueio CR #243828
    $valida = False;
   
    if($codigoUsuarioPerfil != 2){
         if($TipoCompra != '1' and $CompromissoValor != 'S'){
             if ((count($arrayReservas) == 0 or is_null($arrayReservas))) {
                
                erroUsuario($respostaComoMensagem, $nomeCampo, 'Pelo menos um bloqueio ou dotação');
            $valida = True;
            }
        }
         } if(count($arrayReservas) != 0 or !is_null($arrayReservas)){
                    foreach ($arrayReservas as $reserva) {
                        ++ $arrayPos;
                        if ($tipoReserva == TIPO_RESERVA_ORCAMENTARIA_DOTACAO) {
                            $reservaArray = getDadosDotacaoOrcamentaria($dbOracle, $reserva);
                            $bloqueiosValoresTotais += $reservaArray['valorDisponivel'];
                        } elseif ($tipoReserva == TIPO_RESERVA_ORCAMENTARIA_BLOQUEIO) {
                                $reservaArray = getDadosBloqueio($dbOracle, $reserva);
                                $bloqueiosValoresTotais += ($reservaArray['valorTotal']) + 2;
                                // Bloqueio é homologado?
                          if (! is_null($reservaArray) and $reservaArray['homologado'] == 'S') {
                                erroUsuario($respostaComoMensagem, $nomeCampo, 'Bloqueio ' . $reserva . ' é homologado e não pode ser usado');
                             }
                        }else {
                            assercao(false, 'Tipo de reserva desconhecida');
                        }
                        if (is_null($reservaArray)) {
                            erroUsuario($respostaComoMensagem, $nomeCampo, "Bloqueio/Dotação '" . $reserva . "' não existe");
                         }
                    }
        }
        foreach ($arrayItens as $itemSCC) {
                ++ $arrayPos;
                assercao(! is_null($itemSCC['posicao']), "Variável 'posicao' está faltando no item na posição do array '" . $arrayPos . "'");
                // assercao(!is_null($itemSCC['codigo']), "Variável 'codigo' do item requerida em ".$tipoItem." ord ".$itemSCC['posicaoItem']."");
                // assercao(!is_null($itemSCC['tipo']), "Variável 'tipo' do item requerida em ".$tipoItem." ord ".$itemSCC['posicaoItem']."");
                assercao(! is_null($itemSCC['quantidadeItem']), "Variável 'quantidadeItem' do item requerida em " . $tipoItem . ' em posição ' . $itemSCC['posicao'] . '');
                assercao(! is_null($itemSCC[$field]), "Variável 'valorItem' do item requerida em " . $tipoItem . ' ord ' . $itemSCC['posicao'] . '');

                $valorTotalScc += $itemSCC['quantidadeItem'] * $itemSCC[$field];
        }

        if (comparaFloat($bloqueiosValoresTotais, '<', $valorTotalScc, 4) and $valida != False) {
         echo "madson";
            // Trecho inserido por Heraldo em (2/4/2013)
            // Não levar critica em consideração se
            // 1) Tipo de Reserva = "TIPO_RESERVA_ORCAMENTARIA_DOTACAO" e
            // 2) Tipo de Compra = "2" (Licitação) e
            // 3) Registro Preço = "S"

            //Madson Adaptção para permitir que Compra direta não obrigue a inserção de Bloqueio CR #243828
         if($TipoCompra == '1' && ($bloqueiosValoresTotais == 0 || is_null($bloqueiosValoresTotais))){
            
            }else{
                if (!($tipoReserva == TIPO_RESERVA_ORCAMENTARIA_DOTACAO and $TipoCompra == TIPO_COMPRA_LICITACAO and  $CompromissoValor != 'N')) {
                    erroUsuario($respostaComoMensagem, $nomeCampo, 'Valor total da Solicitação é maior que a soma de todos bloqueios ou dotações');
                }
            }   
        }
}

/**
 * Incluir na Tabela de Registro de Preços.
 */
function IncluirNaTRP($vetorDeColunas, $db)
{
    assercao(! is_null($db), 'Variável do banco de dados Oracle não foi inicializada');
    assercao(! is_null($vetorDeColunas), "Variável 'vetorDeColunas' é necessária");

    $tamanho = count($vetorDeColunas);
    $seqTRP = ProximoSequencialTRP($db);
    if ($tamanho == 4) {
        // ----------------------------------------------
        // Inserir registro de Solicitacao de Compra
        // ----------------------------------------------
        $seqCompra = $vetorDeColunas[0];
        $seqItem = $vetorDeColunas[1];
        $codMaterial = $vetorDeColunas[2];
        $valorPreco = $vetorDeColunas[3];

        $naoGravaTRP = VerificaSeGravaTRP($codMaterial, $db);
		//rossana
        if (! $naoGravaTRP) {
			//rossana
            $sql = ' insert into sfpc.tbtabelareferencialprecos ';
            $sql .= ' ( ctrprecodi, csolcosequ, citescsequ,cmatepsequ,vtrprevalo,cusupocodi)  ';
            $sql .= ' values ';
            $sql .= " ( $seqTRP, $seqCompra, $seqItem, $codMaterial, $valorPreco," . $_SESSION['_cusupocodi_'] . ')   ';
            $result = executarTransacao($db, $sql);
        }
    } elseif ($tamanho == 8) {
        // ----------------------------------------------
        // Inserir registro de Licitação
        // ----------------------------------------------
        $codLic = $vetorDeColunas[0];
        $anoLic = $vetorDeColunas[1];
        $grupoLic = $vetorDeColunas[2];
        $comissaoLic = $vetorDeColunas[3];
        $orgaoLic = $vetorDeColunas[4];
        $codItemLic = $vetorDeColunas[5];
        $codMaterialLic = $vetorDeColunas[6];
        $valorMaterialLic = $vetorDeColunas[7];

        $naoGravaTRP = VerificaSeGravaTRP($codMaterialLic, $db);
        if (! $naoGravaTRP) {
						
            $sql = ' insert into sfpc.tbtabelareferencialprecos ';
            $sql .= ' (ctrprecodi, clicpoproc, alicpoanop, cgrempcodi, ccomlicodi, corglicodi, citelpsequ, cmatepsequ, vtrprevalo, cusupocodi )';
            $sql .= ' values  ';
            $sql .= " ( $seqTRP,$codLic,$anoLic,$grupoLic, $comissaoLic,$orgaoLic,$codItemLic,$codMaterialLic,$valorMaterialLic," . $_SESSION['_cusupocodi_'] . ')   ';
            $result = executarTransacao($db, $sql);
        }
    } else {
        assercao(false, "Variável 'vetorDeColunas' com tamanho invalido");
    }
}

/**
 * Verificar se o material pode ser gravado na TRP.
 */
function VerificaSeGravaTRP($codMaterial, $db)
{
	//rossana;
    $sql = ' select fmatepntrp as naogravatrp ';
    $sql .= ' from sfpc.tbmaterialportal portal ';
    $sql .= ' where ';
    $sql .= " cmatepsequ = $codMaterial ";
    $result = executarTransacao($db, $sql);
    $row = $result->fetchRow(DB_FETCHMODE_OBJECT);
    $naogravatrp = $row->naogravatrp;
    if (($naogravatrp == 'N') AND ($naogravatrp <> NULL)){  
		//Rossana
        return true;
    } else {
        return false;
    }

	//antes de eu alterar - rossana
  /*   if (! $naogravatrp) {
        if ($naogravatrp == 'N') {
            return true;
        } else {
            return false;
        }
    } */
}
	

/**
 * Incluir na Tabela de Registro de Preços.
 */
function IncluirPesquisaNaTRP($codPesquisa, $codMaterial, $valor, $db)
{
    $naoGravaTRP = VerificaSeGravaTRP($codMaterial, $db);
    if (! $naoGravaTRP) {
        $seqTRP = ProximoSequencialTRP($db);
        $sql = ' insert into sfpc.tbtabelareferencialprecos';
        $sql .= ' (ctrprecodi, cpesqmsequ, cmatepsequ, vtrprevalo, cusupocodi )';
        $sql .= " values ( $seqTRP,$codPesquisa, $codMaterial, $valor," . $_SESSION['_cusupocodi_'] . ')';
        $result = executarTransacao($db, $sql);
    }
}

/**
 * Pegar último sequencial da tabela TRP.
 */
function ProximoSequencialTRP($db)
{
    $sql = ' select max(ctrprecodi) as max from sfpc.tbtabelareferencialprecos ';
    $result = executarSQL($db, $sql);
    $row = $result->fetchRow(DB_FETCHMODE_OBJECT);

    return $row->max + 1;
}

/**
 * Verificar o numero da ata
 */
function numeroAtaSarpInterna($db, $corglicodi, $carpincodn = null, $aarpinanon = null)
{
    $sql = " SELECT ccp.ccenpocorg, ccp.ccenpounid, ccp.corglicodi ";
    $sql .= " FROM sfpc.tbcentrocustoportal ccp ";
    $sql .= " WHERE true";
    $sql .= " AND ccp.corglicodi = " . $corglicodi;
    
    $result = executarSQL($db, $sql);
    $row = $result->fetchRow(DB_FETCHMODE_OBJECT);

    $numeroAtaRP = "";
    $numeroAtaRP .= $row->ccenpocorg . str_pad($row->ccenpounid, 2, '0', STR_PAD_LEFT);

    if(!is_null($carpincodn)) {
        $numeroAtaRP .= '.' . str_pad($carpincodn, 4, '0', STR_PAD_LEFT);
    }

    if(!is_null($aarpinanon)) {
        $numeroAtaRP .= '/' .$aarpinanon;
    }

    return $numeroAtaRP;
}



/**
 * Classe Para resolver a CR 307.
 */
class CR307
{

    private $conexao;

    private $processo;

    private $ano;

    private $grupo;

    private $comissao;

    private $orgao;

    private $codigoItem;

    private $codigoMaterial;

    private $valor;

    private static $mediaTRP;

    public function getCodigoMaterial()
    {
        return $this->codigoMaterial;
    }

    /**
     * [__construct description].
     *
     * @param array $vetor
     *            [description]
     */
    public function __construct($conexao, $vetor)
    {
        $this->conexao = $conexao;
        self::$mediaTRP = null;
        $this->processo = $vetor[0];
        $this->ano = $vetor[1];
        $this->grupo = $vetor[2];
        $this->comissao = $vetor[3];
        $this->orgao = $vetor[4];
        $this->codigoItem = $vetor[5];
        $this->codigoMaterial = $vetor[6];
        $this->valor = $vetor[7];
    }

    /**
     * [verificarSeNaoExisteMaterialNaTRP description].
     *
     * @return bool [description]
     */
    private function verificarSeNaoExisteMaterialNaTRP()
    {
        // Kim
        $sql = '
            SELECT count(ctrprecodi)
            FROM
                sfpc.tbtabelareferencialprecos
            WHERE
                cmatepsequ = %d
                AND clicpoproc <> NULL
                OR cpesqmsequ <> NULL
        ';
        $total = resultValorUnico(executarSQL($this->conexao, sprintf($sql, $this->getCodigoMaterial())));
        
        return $total;
        // return $total == 0 ? true : false;
    }

    /**
     * [getTipoCompra description].
     *
     * @return [type] [description]
     */
    private function getTipoCompra()
    {
        $sql = '
            SELECT
                l.cmodlicodi
            FROM
                sfpc.tblicitacaoportal l
            INNER JOIN sfpc.tbmodalidadelicitacao m ON m.cmodlicodi = l.cmodlicodi
            WHERE
                l.clicpoproc = %d
                AND l.alicpoanop = %d
        		AND cgrempcodi = %d
			    AND ccomlicodi = %d
			    AND corglicodi = %d
        ';

        return resultValorUnico(executarSQL($this->conexao, sprintf($sql, $this->processo, $this->ano, $this->grupo, $this->comissao, $this->orgao)));
    }

    /**
     * [factoryInserirNaTRP description].
     *
     * @param array $vetor
     *            [description]
     *
     * @return [type] [description]
     */
    private function factoryInserirNaTRP(array $vetor)
    {

        $entidade = array(
            'clicpoproc',
            'alicpoanop',
            'cgrempcodi',
            'ccomlicodi',
            'corglicodi',
            'citelpsequ',
            'cmatepsequ',
            'vtrprevalo',
            'ctrpreulat',
            'ftrprevali'
        );

        $entidade = array_combine($entidade, $vetor);
		

		
		
		
		
		// coloquei o comentário baixo só para não gravar para testes, depois retirar
		// kim
        $entidade['ctrprecodi'] = ProximoSequencialTRP($this->conexao);
        $entidade['cusupocodi'] = $_SESSION['_cusupocodi_'];
        $resultado = $this->conexao->autoExecute('sfpc.tbtabelareferencialprecos', $entidade, DB_AUTOQUERY_INSERT);

        if (PEAR::isError($resultado)) {
            die($resultado->getMessage());
        }
		
		return true;
		
    }
    //
    /**
     * Retornar o valor do percentual limite de preços TRP.
     *
     * @return int vpargeperl - Percentual limite de preços TRP em relação a média
     */
    private static function getPercentualLimitePrecoTRP()
    {
        $sql = '
            SELECT
                p.vpargeperl
            FROM
                sfpc.tbparametrosgerais p
        ';

        return resultValorUnico(executarSQL(Conexao(), $sql));
    }

    /**
     * [valorEstarNoLimite description].
     *
     * @return bool [description]
     */
    private function valorEstarNoLimite($valor, $mediaTRP = null)
    {
		//rossana / kim
        self::$mediaTRP = $mediaTRP != null ? $mediaTRP : self::$mediaTRP;
        $percentual = self::getPercentualLimitePrecoTRP();
        $fator = self::$mediaTRP * $percentual / 100;
        $valorMaximo = self::$mediaTRP + $fator;
        $valorMinimo = self::$mediaTRP - $fator;
		
        if ($valor > $valorMinimo && $valor < $valorMaximo) {
            return true;
        }

        return false;
    }

    /**
     * [verificarNovaRegraIncluirTRP description].
     *
     * @param array $vetor
     *            array($processo, $ano, $grupo, $comissao, $orgao, $codItem, $codMaterial, $valor );
     *
     * @return bool [description]
     * funcção feita e alterada por Rossana e testada e analisada por Eliakim 
     * para a CR#224495
     */
    public static function verificarNovaRegraIncluirTRP($conexao, $vetor)
    {
		//rossana  MUDEI TUDO / kim
		$regra = new CR307($conexao, $vetor);
        $teste = $regra->verificarSeNaoExisteMaterialNaTRP();
		// if ($regra->verificarSeNaoExisteMaterialNaTRP() == false ){
		if ($teste == false ){
			// buscar o último preço válido do material 
			//$db = Conexao();
			// FALTA COLOCAR PARA NÃO PEGAR O MESMO PROCESSO E AINDA VERIFICAR SE O CAMPO NÃOGRAVARTRP ESTÁ FUNCIONANDO
			$sql = "SELECT  VTRPREVALO as qtd
				   FROM    SFPC.TBTABELAREFERENCIALPRECOS TRP
				   WHERE   CMATEPSEQU = $vetor[6]
				   AND 	FTRPREVALI = 'A'
				   ORDER BY CTRPRECODI DESC 
				   LIMIT 1";
			$result = executarTransacao($conexao, $sql);
            $row = $result->fetchRow(DB_FETCHMODE_OBJECT);
            $ultimoValor = $row->qtd;
			if (($ultimoValor == 0) or is_null($ultimoValor)) {
				return true;
			}  else {
				$percentual = self::getPercentualLimitePrecoTRP();
				$fator = $ultimoValor * $percentual / 100;
				$valorMaximo = $ultimoValor + $fator;
				$valorMinimo = $ultimoValor - $fator;
				//Rossana
				// exit;
			    if ($regra->valor > $valorMinimo && $regra->valor < $valorMaximo) {
					return true;
				}	
			} 				
        }

		//rossana	
		
        return false;
	}
		//$mediaZerada = $mediaTRP == 0 ? true : false;
        // Vetor[7] é o valor do material para a trp
        /* if (($regra->verificarSeNaoExisteMaterialNaTRP() || $mediaZerada) || self::valorEstarNoLimite($vetor[7], $mediaTRP)) {
			// rossana
			echo aqui3; 
			echo $vetor[7];
			
            return true;
        }
 */
	    /**
     * [gravaPrecoNaTRPAceite description].
     *
     * @param resource $conexao
     *            [description]
     * @param array $vetor
     *            [description]
     *
     * @return [type] [description]
     */
    public static function gravaPrecoNaTRPAceite($conexao, array $vetor)
    {
        // kim 
        $regra = new CR307($conexao, $vetor);
        $vetor[] = 'A';
        $regra->factoryInserirNaTRP($vetor);
    }

    /**
     * [gravaPrecoNaTRPNulo description].
     *
     * @param resource $conexao
     *            [description]
     * @param array $vetor
     *            [description]
     *
     * @return [type] [description]
     */
    public static function gravaPrecoNaTRPNulo($conexao, array $vetor)
    {
		//rossana CR#224495
        $regra = new CR307($conexao, $vetor);
        // $vetor[] = foi alterado para nulo, pois antes estava com 'A';
        $vetor[] = '';
        $regra->factoryInserirNaTRP($vetor);
    }
}

/**
 * Inserir itens da licitacao na tabela TRP.
 *
 * @param int $processo
 *            [description]
 * @param int $ano
 *            [description]
 * @param int $grupo
 *            [description]
 * @param int $comissao
 *            [description]
 * @param int $orgao
 *            [description]
 * @param resource $db
 *            [description]
 */
function inserirItensLicitacaoNaTrp($processo, $ano, $grupo, $comissao, $orgao, $datafase = null, $db)
{
	
	//rossana  - esta função pode ser alterada pois só aparece em  Fase licitação Incluir
    // Verificar itens de material da licitacao
    $sql = ' SELECT citelpsequ,cmatepsequ,vitelpvlog  FROM sfpc.tbitemlicitacaoportal ';
    $sql .= ' WHERE ';
    $sql .= " clicpoproc=$processo AND";
    $sql .= " alicpoanop=$ano AND ";
    $sql .= " cgrempcodi=$grupo AND ";
    $sql .= " ccomlicodi=$comissao AND ";
    $sql .= " corglicodi=$orgao AND";
    $sql .= ' cmatepsequ IS NOT NULL AND ';
    $sql .= ' vitelpvlog > 0 ';
    // Varrer itens e atualizar na TRP 
    $result = executarTransacao($db, $sql);
    while ($row = $result->fetchRow(DB_FETCHMODE_OBJECT)) {
        $codItem = $row->citelpsequ;
        $codMaterial = $row->cmatepsequ;
        $valor = $row->vitelpvlog;
        $vetor = array(
            $processo,
            $ano,
            $grupo,
            $comissao,
            $orgao,
            $codItem,
            $codMaterial,
            $valor,
            $datafase
        );
        /**
         * CR 307 verificar Nova Regra para incluir na TRP
         * IncluirNaTRP($vetor, $db);.
         */
        $naoGravaTRP = VerificaSeGravaTRP($codMaterial, $db);
            //Kim CR#224495&CR#237312
        if (!$naoGravaTRP or !is_null($naoGravaTRP)) {
			if (CR307::verificarNovaRegraIncluirTRP($db, $vetor)) {	
                CR307::gravaPrecoNaTRPAceite($db, $vetor);				
			} else {
                //rossana CR#224495
				CR307::gravaPrecoNaTRPNulo($db, $vetor);
			}
 		}else{
                die("Deu error aqui");
         }	
    }
}

/**
 * Inserir itens da solicitacao na tabela TRP.
 */
function inserirItensSCCNaTrp($seqSolic, $db)
{
    assercao(! is_null($db), 'Variável do banco de dados Oracle não foi inicializada');
    assercao(! is_null($seqSolic), "Variável 'seqSolic' é necessária");

    // Verificar condições da solicitação de compras
    $sql = ' select count(*) as qtd ';
    $sql .= ' from sfpc.tbsolicitacaocompra sol ';
    $sql .= ' where ';
    $sql .= ' sol.ctpcomcodi = 1 and '; // compra do tipo direta
    $sql .= " sol.fsolcocont <> 'S'  and "; // sem contrato
    $sql .= ' sol.csitsocodi = 3     and '; // pendente de empenho (atual PSE Gerada)
    $sql .= " sol.csolcosequ = $seqSolic ";
    $result = executarTransacao($db, $sql);
    $row = $result->fetchRow(DB_FETCHMODE_OBJECT);
    $qtdRecs = $row->qtd;
    if ($qtdRecs > 0) {
        // Verificar itens de material da licitacao
        $sql = ' select citescsequ,cmatepsequ,vitescunit ';
        $sql .= ' from  sfpc.tbitemsolicitacaocompra ';
        $sql .= " where csolcosequ=$seqSolic  and ";
        $sql .= ' cmatepsequ is not null ';

        // Varrer itens e atualizar na TRP
        $result = executarTransacao($db, $sql);
        // Se for servico a função não deve dar erro
        assercao($result->numRows() > 0, 'Solicitação ou item de solicitação não existem. seqSolic = ' . $seqSolic);
        while ($row = $result->fetchRow(DB_FETCHMODE_OBJECT)) {
            $codItem = $row->citescsequ;
            $codMaterial = $row->cmatepsequ;
            $valor = $row->vitescunit;
            $vetor = array(
                $seqSolic,
                $codItem,
                $codMaterial,
                $valor
            );
            IncluirNaTRP($vetor, $db);
        }
    }
}

/**
 * Retorna data mínima de validade dos registros da tabela referencial de preços, em DateTime
 * (para comparar com datas do banco, usar DateTime->format('Y-m-d')).
 */
function prazoValidadeTrp($db, $tipoCompra)
{
    assercao(! is_null($db), 'Variável do banco de dados Postgresql não foi inicializada');
    assercao(! is_null($tipoCompra), "Variável 'tipoCompra' é necessária");
    $dataCampo = 'qpargevcd';
    if ($tipoCompra == TIPO_COMPRA_LICITACAO or $tipoCompra == TIPO_COMPRA_DISPENSA or $tipoCompra == TIPO_COMPRA_INEXIGIBILIDADE) {
        $dataCampo = 'qpargevli';
    }
    $sql = 'select ' . $dataCampo . ' from sfpc.tbparametrosgerais';
    $diasPrazo = resultValorUnico(executarSQL($db, $sql));
    $dataHoje = new DateTime();
    $data_menos_prazo = new DateTime();
    $data_menos_prazo->setDate($dataHoje->format('Y'), $dataHoje->format('m'), $dataHoje->format('d') - $diasPrazo);

    return $data_menos_prazo;
}

/**
 * Retorna data mínima de validade da data DOM, em DateTime
 * (para comparar com datas do banco, usar DateTime->format('Y-m-d')).
 */
function prazoDOM($db)
{
    assercao(! is_null($db), 'Variável do banco de dados não foi inicializada');
    $sql = 'select qpargeqdvi from sfpc.tbparametrosgerais';
    $dias = resultValorUnico(executarSQL($db, $sql));

    return $dias;
}

/**
 * Retorna data mínima de validade dos registros da tabela referencial de preços, em DateTime
 * (para comparar com datas do banco, usar DateTime->format('Y-m-d')).
 */
function diasVigenciaLicitacao($db)
{
    assercao(! is_null($db), 'Variável do banco de dados não foi inicializada');
    $sql = 'select qpargevili from sfpc.tbparametrosgerais';
    $dias = resultValorUnico(executarSQL($db, $sql));

    return $dias;
}

/**
 * Retorna data mínima de validade dos registros de pesquisa de mercado, em DateTime
 * (para comparar com datas do banco, usar DateTime->format('Y-m-d')).
 */
function prazoValidadePesquisaMercado()
{
    $dataHoje = new DateTime();
    $data_menos_prazo = new DateTime();
    $data_menos_prazo->setDate($dataHoje->format('Y'), $dataHoje->format('m') - 12, $dataHoje->format('d'));

    return $data_menos_prazo;
}

/**
 * Retorna valor do último preço da SCC.
 */
function calcularValorUltimoPreco($db, $scc, $codMaterial)
{
    assercao(! is_null($db), 'Variável do banco de dados Oracle não foi inicializada');
    assercao(! is_null($scc), "Variável 'scc' é necessária");
    assercao(! is_null($codMaterial), "Variável 'codMaterial' é necessária");

    $campoCodigoItem = '';

    $sql = "SELECT  S.CTPCOMCODI, S.TSOLCODATA
            FROM    SFPC.TBSOLICITACAOCOMPRA S
            WHERE   S.CSOLCOSEQU = $scc
                    AND S.CSITSOCODI <> 10 ";
    
    $obj = resultObjetoUnico(executarSQL($db, $sql));
    
    assercao(!is_null($obj), 'SCC não existe ou está cancelada. csolcosequ = ' . $scc);

    $tipoCompra = $obj->ctpcomcodi;
    $dataScc = $obj->tsolcodata;

    $valor = null;

    if ($tipoCompra == TIPO_COMPRA_DIRETA) {
        // Pegar valor da ultima SCC não-cancelada com mesmo item e mesmo tipo de compra
        $sql = "
            select iscc.vitescunit
            from
                sfpc.tbitemsolicitacaocompra iscc, sfpc.tbsolicitacaocompra scc
            where
                iscc.cmatepsequ = $codMaterial
                and iscc.csolcosequ = scc.csolcosequ
                and scc.ctpcomcodi = $tipoCompra
                and not scc.csolcosequ = $scc
                and scc.tsolcodata <= '" . $dataScc . "'
                and not scc.csitsocodi = " . TIPO_SITUACAO_SCC_CANCELADA . '
            order by
                scc.tsolcodata DESC,
                scc.csolcosequ DESC
            LIMIT 1
        ';
        $res = executarSQL($db, $sql);
        $obj = resultObjetoUnico(executarSQL($db, $sql));
        if (! is_null($obj)) {
            $valor = $obj->vitescunit;
        }
    } elseif ($tipoCompra == TIPO_COMPRA_SARP) {
        $valor = null; // SARP não tem ultimo preço
    } else { // licitação, dispensa, inex
             // pegar ultimo valor TRP do item
             $sql = "
            select vtrprevalo
            from sfpc.tbtabelareferencialprecos
            where
                cmatepsequ = $codMaterial
            order by
                -- ctrprecodi DESC,
                ctrpreulat DESC "/* ulat pode ser alteração, por isso que o sequencial é a 1a ordem */.'
            LIMIT 1
        ';
        $res = executarSQL($db, $sql);
        $obj = resultObjetoUnico(executarSQL($db, $sql));
        if (! is_null($obj)) {
            $valor = $obj->vtrprevalo;
        }
    }

    return $valor;
}

/**
 * Validação completa do fornecedor (validação tipo licitação).
 */
function validadacaoCompleta($db, $CPFCNPJ)
{
    $resposta = checaSituacaoFornecedor($db, $CPFCNPJ);
    if ($resposta['erro']) {
        $mensagem = $resposta['mensagem'];
    } elseif ($resposta['inabilitadoPorDebitoCadastroMercantil'] == true) {
        $tipo = 1;
    } elseif ($resposta['inabilitadoPorCHFVencido'] == true) {
        $tipo = 2;
    } elseif ($resposta['inabilitadoPorCertidaoNegFalenciaVencida'] == true) {
        $tipo = 2;
    } elseif ($resposta['inabilitadoPorCertidaoVencida'] == true) {
        $tipo = 2;
    }
    if ($tipo == 1) {
        $mensagem = 'fornecedor com débito no cadastro mercantil';
    } elseif ($tipo == 2) {
        $mensagem = 'fornecedor com pendências no SICREF';
    }
    if (! empty($mensagem)) {
        throw new ExcecaoPendenciasUsuario($mensagem);
    }
}

/**
 * Validação simplificada do fornecedor (validação tipo compra direta).
 */
function validadacaoDebito($db, $CPFCNPJ)
{
    $resposta = checaSituacaoFornecedor($db, $CPFCNPJ);
    if ($resposta['erro']) {
        $mensagem = $resposta['mensagem'];
    } elseif ($resposta['inabilitadoPorDebitoCadastroMercantil'] == true) {
        $mensagem = 'fornecedor com débito no cadastro mercantil';
    }
    if (! empty($mensagem)) {
        throw new ExcecaoPendenciasUsuario($mensagem);
    }
}

/**
 * Valida fornecedor para um material/servico da SCC.
 */
function validaFornecedorItemSCC($db, $CPFCNPJ, $tipoSCC, $materialServico, $tipoMaterialServico)
{
    $CPFCNPJ = removeSimbolos($CPFCNPJ);
    assercao(! is_null($db), 'Variável do banco de dados Oracle não foi inicializada');
    assercao(! is_null($CPFCNPJ), "Parametro 'CPFCNPJ' é necessário");
    assercao(! is_null($tipoSCC), "Parametro 'tipoSCC' é necessário");
    assercao(! is_null($materialServico), "Parametro 'materialServico' é necessário");
    assercao(! is_null($tipoMaterialServico), "Parametro 'tipoMaterialServico' é necessário");

    $tipoCadastro = tipoCadastroFornecedor($CPFCNPJ);

    $fornecedor = getSequencialFromCpfCnpj($db, $CPFCNPJ);

    if (is_null($fornecedor)) {
        throw new ExcecaoPendenciasUsuario('Fornecedor não existe');
    }

    // ---------------------------------------------------------------------
    // - 1 se Tipo de Compra = "Compra Direta/Dispensa/Inelegibilidade/SARP"
    // ---------------------------------------------------------------------
    /*
     * if ($tipoSCC==TIPO_COMPRA_DIRETA) {
     * validadacaoDebito($db,$CPFCNPJ );
     * }
     */
    if ($tipoSCC == TIPO_COMPRA_DIRETA or $tipoSCC == TIPO_COMPRA_DISPENSA or $tipoSCC == TIPO_COMPRA_INEXIGIBILIDADE or $tipoSCC == TIPO_COMPRA_SARP) {
        validadacaoDebito($db, $CPFCNPJ);
    }
    /*
     * //------------------------------------------------------
     * //- 2 se Tipo de Compra = "Dispensa/Inelegibilidade"
     * //------------------------------------------------------
     * if ($tipoSCC==TIPO_COMPRA_DISPENSA or $tipoSCC==TIPO_COMPRA_INEXIGIBILIDADE) {
     * // Se for pessoal jurídica
     * if ( strlen($CPFCNPJ)==14 ) {
     * validadacaoCompleta($db,$CPFCNPJ);
     * } else {
     * validadacaoDebito($db,$CPFCNPJ );
     * }
     * }
     */
    // ------------------------------------------------------
    // - 3 se Tipo de Compra = "Licitacao"
    // ------------------------------------------------------
    if ($tipoSCC == TIPO_COMPRA_LICITACAO /*or $tipoSCC==TIPO_COMPRA_SARP*/) {
        if ($tipoCadastro == TIPO_CADASTRO_CPF) {
            throw new ExcecaoPendenciasUsuario('Tipo de compra não aceita pessoa física');
        }
        validadacaoCompleta($db, $CPFCNPJ);
    }
    // ------------------------------------------------------
    // - 3 se Tipo de Compra = "Licitacao/SARP"
    // ------------------------------------------------------
    $sql = 'select  fforcrtipo as tipo from sfpc.tbfornecedorcredenciado';
    $sql .= ' where ';
    $sql .= ' aforcrsequ = ' . $fornecedor;
    $result = executarTransacao($db, $sql);
    $row = $result->fetchRow(DB_FETCHMODE_OBJECT);
    $tipoSICREF = $row->tipo;
    if (($tipoCadastro == TIPO_CADASTRO_CNPJ) and ($tipoSCC != TIPO_COMPRA_DIRETA) and ($tipoSCC != TIPO_COMPRA_DISPENSA) and ($tipoSCC != TIPO_COMPRA_INEXIGIBILIDADE) and ($tipoSCC != TIPO_COMPRA_SARP) and ($tipoSICREF != 'L')) {
        throw new ExcecaoPendenciasUsuario('Para este fornecedor participar deste tipo de compra precisa ser cadastrado no SICREF como tipo Licitação');
    }
    // ------------------------------------------------------
    // - Verificar se fornecedor tem material/servico cadastrado
    // ------------------------------------------------------
    $forneceItem = forneceMaterialServico($db, $fornecedor, $materialServico, $tipoMaterialServico);
    if (! $forneceItem) {
        throw new ExcecaoPendenciasUsuario('Material/servico não cadastrado para fornecedor');
    }
}

/**
 * Calcula o valor trp.
 *
 * @param resource $db
 *            [description]
 * @param int $tipoCompra
 *            [description]
 * @param int $codMaterial
 *            [description]
 *
 * @return null|float [description]
 */
function calcularValorTrp($db, $tipoCompra, $codMaterial)
{
	
	//rossana  CR#224495
    assercao(! is_null($db), 'Variável do banco de dados Oracle não foi inicializada');
    assercao(! is_null($tipoCompra), "Variável 'tipoCompra' é necessária");
    assercao(! is_null($codMaterial), "Variável 'codMaterial' é necessária");

    $resultado = null;

    $dataMinimaValidaTrp = prazoValidadeTrp($db, $tipoCompra)->format('Y-m-d');

    if ($tipoCompra == TIPO_COMPRA_DIRETA) {
        // mostra o menor valor trp no prazo válido do material
        $sql = '
            select min(vtrprevalo)
              from sfpc.tbtabelareferencialprecos
             where cmatepsequ = ' . $codMaterial . "
               and CTRPREULAT >= '" . $dataMinimaValidaTrp . "'
               and CSOLCOSEQU is not null
               and ( ftrprevali = 'A'
               or ftrprevali is null )
        ";
        $resultado = resultValorUnico(executarSQL($db, $sql));
    }
    if ($tipoCompra == TIPO_COMPRA_LICITACAO or $tipoCompra == TIPO_COMPRA_DISPENSA or $tipoCompra == TIPO_COMPRA_INEXIGIBILIDADE) {
        $dataMinimaValidaPesquisaMercado = prazoValidadePesquisaMercado()->format('Y-m-d');

        $sql = "SELECT AVG(TRP.VTRPREVALO) FROM";
        $sql .= " SFPC.TBTABELAREFERENCIALPRECOS TRP";
        $sql .= " WHERE TRP.CMATEPSEQU = $codMaterial";
        $sql .= " AND( ( TRP.CLICPOPROC IS NOT NULL";
        $sql .= " AND TRP.CTRPREULAT >= '" . $dataMinimaValidaTrp . "'";
        $sql .= " AND TRP.FTRPREVALI = 'A')";
        $sql .= " OR ( TRP.CPESQMSEQU IS NOT NULL";
        $sql .= " AND TRP.CPESQMSEQU IN(";
        $sql .= " SELECT PPM.CPESQMSEQU FROM";
        $sql .= " SFPC.TBPESQUISAPRECOMERCADO PPM";
        $sql .= " WHERE PPM.DPESQMREFE >= '" . $dataMinimaValidaPesquisaMercado . "' ) ))";
        $resultado = resultValorUnico(executarSQL($db, $sql));
    }
    return $resultado;
}

/**
 * Verifica se algum campo de dotação foi preenchido, na tela da inclusão/manutenção de SCC.
 */
function campoDotacaoNulo()
{
    $todosNulo = true;
    global $DotacaoAno, $DotacaoOrgao, $DotacaoUnidade, $DotacaoFuncao, $DotacaoSubfuncao, $DotacaoPrograma, $DotacaoTipoProjetoAtividade, $DotacaoProjetoAtividade, $DotacaoElemento1, $DotacaoElemento2, $DotacaoElemento3, $DotacaoElemento4, $DotacaoFonte;
    if (! empty($DotacaoAno)) {
        $todosNulo = false;
    }
    if (! empty($DotacaoOrgao)) {
        $todosNulo = false;
    }
    if (! empty($DotacaoUnidade)) {
        $todosNulo = false;
    }
    if (! empty($DotacaoFuncao)) {
        $todosNulo = false;
    }
    if (! empty($DotacaoSubfuncao)) {
        $todosNulo = false;
    }
    if (! empty($DotacaoPrograma)) {
        $todosNulo = false;
    }
    if (! empty($DotacaoTipoProjetoAtividade)) {
        $todosNulo = false;
    }
    if (! empty($DotacaoProjetoAtividade)) {
        $todosNulo = false;
    }
    if (! empty($DotacaoElemento1)) {
        $todosNulo = false;
    }
    if (! empty($DotacaoElemento2)) {
        $todosNulo = false;
    }
    if (! empty($DotacaoElemento3)) {
        $todosNulo = false;
    }
    if (! empty($DotacaoElemento4)) {
        $todosNulo = false;
    }
    if (! empty($DotacaoFonte)) {
        $todosNulo = false;
    }

    return $todosNulo;
}

/**
 * OBSOLETO.
 * USAR validarReservaOrcamentaria
 * validarReservaOrcamentaria($db, $dbOracle, $tipoReserva, $arrayReservas, $arrayItens, $nomeCampo).
 */
function validarReservaOrcamentariaScc($db, $dbOracle, $idSolicitacao, $tipoReserva, $arrayItens, $isLicitacao = false, $respostaComoMensagem = false, $nomeCampo = null, $permitirSemBloqueios = false)
{
    assercao(false, 'Devido à mudança da forma em que bloqueios são tratados, esta função é obsoleta. Usar no lugar validarReservaOrcamentaria()');
}

/* Função para capturar a chave do processo em vetor */
function getChaveLicitacao($solicitacao, $db)
{
    $sql = ' select ';
    $sql .= ' lic.clicpoproc as processo, lic.alicpoanop as ano, lic.cgrempcodi as grupo, lic.ccomlicodi as comissao, lic.corglicodi as orgao';
    $sql .= ' from ';
    $sql .= ' sfpc.tbsolicitacaolicitacaoportal sol, sfpc.tblicitacaoportal lic ';
    $sql .= ' where ';
    $sql .= " sol.csolcosequ = $solicitacao ";
    $sql .= ' and  sol.clicpoproc = lic.clicpoproc ';
    $sql .= ' and  sol.alicpoanop = lic.alicpoanop ';
    $sql .= ' and  sol.cgrempcodi = lic.cgrempcodi ';
    $sql .= ' and  sol.ccomlicodi  = lic.ccomlicodi ';
    $sql .= ' and  sol.corglicodi = lic.corglicodi ';
    $result = executarTransacao($db, $sql);
    $row = $result->fetchRow(DB_FETCHMODE_OBJECT);
    if (empty($row->processo)) {
        $vetor[0] = 999;
        $vetor[1] = 999;
        $vetor[2] = 999;
        $vetor[3] = 999;
        $vetor[4] = 999;
    } else {
        $vetor[0] = $row->processo;
        $vetor[1] = $row->ano;
        $vetor[2] = $row->grupo;
        $vetor[3] = $row->comissao;
        $vetor[4] = $row->orgao;
    }

    return $vetor;
}

/* Função para capturar descrição da comissão de licitação */
function getDescComissao($comissao, $db)
{
    $grupo = trim($grupo);
    $sql = ' select ';
    $sql .= ' ecomlidesc as desc ';
    $sql .= ' from ';
    $sql .= ' sfpc.tbcomissaolicitacao ';
    $sql .= ' where ';
    $sql .= " ccomlicodi = $comissao ";
    $result = executarTransacao($db, $sql);
    $row = $result->fetchRow(DB_FETCHMODE_OBJECT);

    return $row->desc;
}

function getDadosSolicitacao($solicitacao, $db)
{
    $sql = ' select ccomlicod1 as comissao ';
    $sql .= ' from ';
    $sql .= ' sfpc.tbsolicitacaocompra  ';
    $sql .= ' where ';
    $sql .= " csolcosequ = $solicitacao ";
    $result = executarTransacao($db, $sql);
    $row = $result->fetchRow(DB_FETCHMODE_OBJECT);

    return $row;
}

/* Retorna SCCs agrupadas em relação a um código scc informado */
function getNumeroSolicitacaoCompraAgrupadas($db, $Solicitacao)
{
    $sql = 'select csolcosequ ';
    $sql .= 'from sfpc.tbagrupasolicitacao ';
    $sql .= 'where cagsolsequ = ';
    $sql .= "(select cagsolsequ from sfpc.tbagrupasolicitacao where csolcosequ = $Solicitacao) ";
    $sql .= "and csolcosequ <> $Solicitacao ";
    $sql .= 'order by csolcosequ asc;';
    $data = &$db->getAll($sql, array(), DB_FETCHMODE_ORDERED);

    if (PEAR::isError($data)) {
        $CodErroEmail = $data->getCode();
        $DescErroEmail = $data->getMessage();
        ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
    } else {
        // Deixa o array multidimensional para um dimensional
        $final_array = array();
        foreach ($data as $val) {
            foreach ($val as $val2) {
                $final_array[] = $val2;
            }
        }
    }

    $scc = array();
    foreach ($final_array as $idSolicitacao) {
        $sql = "
        select distinct ccenpocorg, ccenpounid, csolcocodi, asolcoanos
          from sfpc.tbsolicitacaocompra scc, SFPC.TBcentrocustoportal cc
         where scc.ccenposequ = cc.ccenposequ
             and csolcosequ = $idSolicitacao
        ";

        $linha = resultLinhaUnica(executarSQL($db, $sql));
        $resposta = array();
        $resposta['orgaoSofin'] = $linha[0];
        $resposta['unidadeSofin'] = $linha[1];
        $resposta['solicitacao'] = $linha[2];
        $resposta['anoSolicitacao'] = $linha[3];
        $resposta['numeroSolicitacao'] = sprintf('%02s', $resposta['orgaoSofin']) . sprintf('%02s', $resposta['unidadeSofin']) . '.' . sprintf('%04s', $resposta['solicitacao']) . '.' . $resposta['anoSolicitacao'];
        $scc[] = $resposta['numeroSolicitacao'];
    }
    foreach ($scc as $sccs) {
        $item .= $sccs . '  ';
    }

    return $item;
}

/**
 * Verifica se o item selecionado possui o campo SFPC.TBMATERIALPORTAL.FMATEPGENE igual a 'S'.
 * Se houver exibir o campo descrição detalhada depois da coluna de UND semelhante ao caso de serviço,.
 *
 * @see [CR123140]: REDMINE 21 (P3)
 *
 * @param resource $dbi
 *            [description]
 * @param int $intCodigoMaterial
 *            [description]
 *
 * @return bool [description]
 */
function hasIndicadorCADUM($dbi, $intCodigoMaterial)
{
    assercao(! is_null($dbi), 'Variável de banco de dados não foi inicializada');
    assercao(! is_null($intCodigoMaterial), "Parâmetro 'intCodigoMaterial' requerido");

    $sql = '
        SELECT count(*)
        FROM sfpc.tbmaterialportal
        WHERE cmatepsequ = ' . $intCodigoMaterial . " AND fmatepgene = 'S' ";

    $qtdeIndicador = resultValorUnico(executarSQL($dbi, $sql));

    return ($qtdeIndicador > 0) ? true : false;
}

/**
 * [checkPositionNotEmpty description].
 *
 * @param array $array
 *            [description]
 * @param string $index
 *            [description]
 *
 * @return bool [description]
 */
function checkPositionNotEmpty($array, $index)
{
    $retorno = false;

    foreach ($array as $value) {
        if (! empty($value[$index])) {
            $retorno = true;
            break;
        }
    }

    return $retorno;
}

function exibirMediaTRP($material, $DataIni, $DataFim)
{
    $db = Conexao();
    if ((empty($DataIni)) && (empty($DataFim))) {
        $DataIni = prazoValidadeTrp($db, TIPO_COMPRA_LICITACAO)->format('Y-m-d');
        $DataFim = prazoValidadePesquisaMercado()->format('Y-m-d');
    }

    $range = resultValorUnico(executarSQL($db, 'SELECT VPARGEPERL FROM SFPC.TBPARAMETROSGERAIS'));

    $sql = "SELECT
                GRUM.FGRUMSTIPM ,
                MAT.EMATEPDESC ,
                REFP.CMATEPSEQU ,
                UNID.EUNIDMSIGL ,
                REFP.VTRPREVALO ,
                REFP.CTRPREULAT ,
                REFP.CLICPOPROC ,
                REFP.CPESQMSEQU ,
                REFP.FTRPREVALI ,
                (
                    SELECT
                        AVG(TRP2.VTRPREVALO)
                    FROM
                        SFPC.TBTABELAREFERENCIALPRECOS TRP2
                    WHERE
                        TRP2.CMATEPSEQU = REFP.CMATEPSEQU
                        AND TRP2.CTRPREULAT >= '" . $DataIni . "'
                        AND(
                            (
                                TRP2.CLICPOPROC IS NOT NULL
                            )
                            OR(
                                TRP2.CPESQMSEQU IS NOT NULL
                                AND TRP2.CPESQMSEQU IN(
                                    SELECT
                                        PPM.CPESQMSEQU
                                    FROM
                                        SFPC.TBPESQUISAPRECOMERCADO PPM
                                    WHERE
                                        PPM.DPESQMREFE >= '" . $DataFim . "'
                                )
                            )
                        )
                ) AS MEDIATRP ,
                ILP.VITELPUNIT
            FROM
                SFPC.TBTABELAREFERENCIALPRECOS REFP JOIN SFPC.TBMATERIALPORTAL MAT
                    ON REFP.CMATEPSEQU = MAT.CMATEPSEQU JOIN SFPC.TBSUBCLASSEMATERIAL SUBM
                    ON MAT.CSUBCLSEQU = SUBM.CSUBCLSEQU JOIN SFPC.TBUNIDADEDEMEDIDA UNID
                    ON MAT.CUNIDMCODI = UNID.CUNIDMCODI JOIN SFPC.TBGRUPOMATERIALSERVICO GRUM
                    ON SUBM.CGRUMSCODI = GRUM.CGRUMSCODI LEFT JOIN SFPC.TBITEMLICITACAOPORTAL ILP
                    ON(
                    ILP.CLICPOPROC = REFP.CLICPOPROC
                    AND ILP.ALICPOANOP = REFP.ALICPOANOP
                    AND ILP.CGREMPCODI = REFP.CGREMPCODI
                    AND ILP.CCOMLICODI = REFP.CCOMLICODI
                    AND ILP.CORGLICODI = REFP.CORGLICODI
                    AND ILP.CITELPSEQU = REFP.CITELPSEQU
                    AND ILP.CMATEPSEQU = REFP.CMATEPSEQU
                )
            WHERE
                REFP.CTRPREULAT =(
                    SELECT
                        MAX(CTRPREULAT)
                    FROM
                        SFPC.TBTABELAREFERENCIALPRECOS
                    WHERE
                        CMATEPSEQU = REFP.CMATEPSEQU
                        AND(
                            CLICPOPROC IS NOT NULL
                            OR CPESQMSEQU IS NOT NULL
                        )
                        AND(
                            FTRPREVALI <> 'E'
                            OR FTRPREVALI IS NULL
                        )
                )
                AND REFP.CMATEPSEQU = '" . $material . "'
            ORDER BY
                GRUM.FGRUMSTIPM ,
                MAT.EMATEPDESC ,
                REFP.CTRPREULAT DESC
            ;";
    $res = $db->query($sql);

    while ($Linha = $res->fetchRow()) {
        $valor1 = $Linha[9] - (($Linha[9] * $range) / 100);
        $valor2 = $Linha[9] + (($Linha[9] * $range) / 100);
        if (((($Linha[10] <= $valor2) && ($Linha[10] >= $valor1))) || ((($Linha[10] < $valor1) || ($Linha[10] > $valor2)) && ($Linha[8] == 'A'))) {
            // return 1 . " $DataIni" . " , " . "$DataFim";
            return true;
        } else {
            // return 0 . " $DataIni" . " , " . "$DataFim";
            return false;
        }
    }
}

// Retorna um array de SCCs com nomenclatura já formatada
function transformToSCC($db, $CodSeqSCC)
{
    $sqlItens = "SELECT DISTINCT
                    CCENPOCORG ,
                    CCENPOUNID ,
                    CSOLCOCODI ,
                    ASOLCOANOS
                FROM
                    SFPC.TBSOLICITACAOCOMPRA SCC ,
                    SFPC.TBCENTROCUSTOPORTAL CC
                WHERE
                    SCC.CCENPOSEQU = CC.CCENPOSEQU
                    AND CSOLCOSEQU = $CodSeqSCC";
    $resItens = &$db->query($sqlItens);
    while ($Linha = &$resItens->fetchRow()) {
        $scc = array();
        $scc['orgaoSofin'] = $Linha[0];
        $scc['unidadeSofin'] = $Linha[1];
        $scc['solicitacao'] = $Linha[2];
        $scc['anoSolicitacao'] = $Linha[3];
        $scc['numeroSolicitacao'] = sprintf('%02s', $scc['orgaoSofin']) . sprintf('%02s', $scc['unidadeSofin']) . '.' . sprintf('%04s', $scc['solicitacao']) . '.' . $scc['anoSolicitacao'];
    }

    return $scc['numeroSolicitacao'];
}
function InteiroRomano($Str)
{
    $n = $Str;
    $Str = '';
    while (($n / 10) >= 1) {
        $Str .= 'X';
        $n -= 10;
    }
    if (($n / 9) >= 1) {
        $Str .= 'IX';
        $n -= 9;
    }
    if (($n / 5) >= 1) {
        $Str .= 'V';
        $n -= 5;
    }
    if (($n / 4) >= 1) {
        $Str .= 'IV';
        $n -= 4;
    }

    while ($n >= 1) {
        $Str .= 'I';
        $n -= 1;
    } 
    
    return $Str;

}

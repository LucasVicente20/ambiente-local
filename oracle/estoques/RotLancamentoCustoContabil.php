<?php
/**
 * Prefeitura do Recife
 * Portal de Compras
 * 
 * Programa: RotLancamentoCustoContabil.php
 * Autor:    Rodrigo Melo
 * Data:     17/04/2008
 * Objetivo: Programa responsável por incluir lançamentos contábeis referentes aos movimentos dos materiais cadastrados no portal de compras.
 * -------------------------------------------------------------------------------------------------------------------------------------------------------
 * Alterado: Rodrigo Melo / Ariston
 * Data:     24/07/2008
 * Objetivo: Alteração para colocar o caminho absoluto ao invés do caminho relativo.
 * -------------------------------------------------------------------------------------------------------------------------------------------------------
 * Alterado: Ariston
 * Data:     25/07/2008
 * Objetivo: Correção de bug em que, caso o 1o item não foi atendido, o programa considera como se nenhum item tivesse sido atendido e retorna erro, impossibilitando baixar a requisição.
 *           Correção referente ao lançamento de custo.
 *           Correção do redirecionamento errado em produção
 * -------------------------------------------------------------------------------------------------------------------------------------------------------
 * Alterado: Rodrigo Melo
 * Data:     30/07/2008
 * Objetivo: Correção de bug onde, se algum item não tenha sido atendido, o programa considera como se nenhum item tivesse sido atendido e retorna erro, impossibilitando baixar a requisição.
 *           Correção referente ao lançamento de contábil.
 * -------------------------------------------------------------------------------------------------------------------------------------------------------
 * Alterado: Pitang Agile TI
 * Data:     03/07/2015
 * Objetivo: Tarefa Redmine 73618
 * -------------------------------------------------------------------------------------------------------------------------------------------------------
 * Alterado: Lucas Baracho
 * Data:     17/01/2023
 * Objetivo: Tarefa Redmine 277686
 * -------------------------------------------------------------------------------------------------------------------------------------------------------
 */

//Váriaveis globais e configurações gerais
$AlmoxarifadosInvalidos = array(34); //Refere-se ao código do almoxarifado em CALMPOCODI da tabela SFPC.TBALMOXARIFADOPORTAL. 34 - ALMOXARIFADO DA CÂMARA

#Movimentações especiais
$MOVIMENTACAO_ENTRADA_POR_GERACAO_DE_INVENTARIO = 33;
$MOVIMENTACAO_SAIDA_POR_GERACAO_DE_INVENTARIO = 34;

//Fim das Váriaveis globais e configurações gerais

/**
 * Função responsável por gerar a URL de resposta para enviar uma mensagem de erro ou notificação para o programa destino.
 * parâmetros:
 * $ProgramaDestino - Programa onde será enviado a mensagem de erro ou notificação.
 * $Mensagem - Mensagem de erro ou notificação
 * $Tipo - Tipo de mensagem (1 - Notificação ou 2 - Erro).
 */
function EnviarMensagem($ProgramaDestino, $Mensagem, $Tipo)
{
    //$ServerName = $_SERVER['SERVER_NAME'];

    /*$DNS="";
    switch($ServerName){
        case "varzea.recife":
          case "www.recife.pe.gov.br":
        $DNS = "http://www.recife.pe.gov.br/portalcompras/";
        break;

        case "cdu.recife":
          case "portal.homolog.recife":
          case "intranet.homolog.recife":
        $DNS = "http://cdu.recife/pr/secfinancas/portalcompras/programas/";
        break;

          case "cohab.recife":
          case "portal.emprel.recife":
          case "intranet.emprel.recife":
        $DNS = "http://cohab.recife/pr/secfinancas/portalcompras/programas/";
        break;
    }*/
    if ($ProgramaDestino == 'CadMovimentacaoConfirmar.php') {
        $Arquivo = "CadMovimentacaoIncluir.php?Mens=1&Tipo=$Tipo&Mensagem=".urlencode($Mensagem);
    } elseif ($ProgramaDestino == 'CadMovimentacaoAlterar.php') {
        $Arquivo = "CadMovimentacaoSelecionar.php?Mens=1&Tipo=$Tipo&Mensagem=".urlencode($Mensagem);
    } elseif ($ProgramaDestino == 'CadRequisicaoBaixa.php') {
        $Arquivo = "CadRequisicaoBaixaSelecionar.php?Mens=1&Tipo=$Tipo&Mensagem=".urlencode($Mensagem);
    } elseif ($ProgramaDestino == 'CadInventarioPeriodicoFechamento.php') {
        $Arquivo = "CadInventarioPeriodicoFechamento.php?Mens=1&Tipo=$Tipo&Mensagem=".urlencode($Mensagem);
    } elseif ($ProgramaDestino == 'CadInventarioInicialFechamento.php') {
        $Arquivo = "CadInventarioInicialFechamento.php?Mens=1&Tipo=$Tipo&Mensagem=".urlencode($Mensagem);
    }
    //$Url = $DNS."estoques/".$Arquivo;

    return $Arquivo;
}

function redirecionaPagina($Url)
{
    $Url2 = 'estoques/'.$Url;
    if (!in_array($Url2, $_SESSION['GetUrl'])) {
        $_SESSION['GetUrl'][] = $Url2;
    }
    header('location: '.$Url);
    exit;
}

/**
 * Função responsável por cancelar as alterações no oracle e no postgresql.
 * Parâmetros:
 * $dbora - Conexão oracle
 * $db - Conexão Postgresql
 * Retorno: Não possui retorno.
 */
function CancelarAlteracoes($dbora, $db)
{
    # Desfaz alterações no Oracle #
    $dbora->query('ROLLBACK');
    $dbora->query('END TRANSACTION');
    $dbora->disconnect();

    # Desfaz alterações no Postgresql #
    $db->query('ROLLBACK');
    $db->query('END TRANSACTION');
    $db->disconnect();
}

/**
 * Função responsável por confirmar as alterações no oracle e no postgresql.
 * Parâmetros:
 * $dbora - Conexão oracle
 * $db - Conexão Postgresql
 * Retorno: Não possui retorno.
 */
function ConfirmarAlteracoes($dbora, $db)
{
    # Commita alterações no Oracle #
    $dbora->query('COMMIT');
    $dbora->query('END TRANSACTION');
    $dbora->disconnect();

    # Commita alterações no Postgre #
    $db->query('COMMIT');
    $db->query('END TRANSACTION');
    $db->disconnect();
}

/**
 * Função que resgata o último valor do sequencial da tabela SFCT.TBMOVCONTABILALMOXARIFADO no oracle, conforme o ano, mês e dia da movimentação.
 * Parâmetros:
 * $AnoBaixa - Ano corrente (de exercicio) em que a movimentação foi lançada
 * $MesBaixa - Mês corrente em que a movimentação foi lançada.
 * $DiaBaixa -  Dia corrente em que a movimentação foi lançada.
 * $dbora - Conexão oracle
 * $db - Conexão Postgresql
 * Retorno: retorna o (sequencial + 1);.
 */
function SequMaxContabil($AnoBaixa, $MesBaixa, $DiaBaixa, $dbora, $db)
{
    $sql = 'SELECT MAX(AMVCALSEQU) FROM SFCT.TBMOVCONTABILALMOXARIFADO ';
    $sql .= " WHERE APLCTAANOC = $AnoBaixa AND AMVCALMESM = $MesBaixa AND AMVCALDIAM = $DiaBaixa ";
    $res = $dbora->query($sql);
    if (PEAR::isError($res)) {
        $CodErroEmail = $res->getCode(); //Obtém o código de erro do Banco de dados oracle
        $DescErroEmail = $res->getMessage(); //Obtém a descrição do erro do Banco de dados oracle

        CancelarAlteracoes($dbora, $db);

        ExibeErroBDRotinas(__FILE__."\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
        exit;
    } else {
        $Linha = $res->fetchRow();
        $Sequencial = $Linha[0] + 1;

        return $Sequencial;
    }
}

/**
 * Função que resgata o último valor do sequencial da tabela SFCT.TBMOVCUSTOALMOXARIFADO no oracle, conforme o ano, mês e dia da movimentação.
 * Parâmetros:
 * $AnoBaixa - Ano corrente (de exercicio) em que a movimentação foi lançada
 * $MesBaixa - Mês corrente em que a movimentação foi lançada.
 * $DiaBaixa -  Dia corrente em que a movimentação foi lançada.
 * $dbora - Conexão oracle
 * $db - Conexão Postgresql
 * Retorno: retorna o (sequencial + 1);.
 */
function SequMaxCusto($AnoBaixa, $MesBaixa, $DiaBaixa, $dbora, $db)
{
    $sql = 'SELECT MAX(CMOVCUSEQU) FROM SFCP.TBMOVCUSTOALMOXARIFADO ';
    $sql .= " WHERE DEXERCANOR = $AnoBaixa AND AMOVCUMESM = $MesBaixa AND AMOVCUDIAM = $DiaBaixa ";
    $res = $dbora->query($sql);
    if (PEAR::isError($res)) {
        $CodErroEmail = $res->getCode(); //Obtém o código de erro do Banco de dados oracle
        $DescErroEmail = $res->getMessage(); //Obtém a descrição do erro do Banco de dados oracle

        CancelarAlteracoes($dbora, $db);

        ExibeErroBDRotinas(__FILE__."\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
        exit;
    } else {
        $Linha = $res->fetchRow();
        $Sequencial = $Linha[0] + 1;

        return $Sequencial;
    }
}

/**
 * Função responsável por obter a descrição do tipo da movimentação conforme o código do mesmo (exemplo: 11 - ENTRADA POR TROCA; 15 - SAÍDA POR TROCA; etc).
 * Parâmetros:
 * $Movimentacao - Refere-se ao código do tipo movimentação do material que gera lançamentos contábeis.
 * $dbora - Conexão oracle
 * $db - Conexão Postgresql
 * Retorno: Retorna a descrição da movimentação.
 */
function MovDesc($Movimentacao, $dbora, $db)
{
    $sql = 'SELECT ETIPMVDESC FROM SFPC.TBTIPOMOVIMENTACAO ';
    $sql   .= " WHERE CTIPMVCODI = $Movimentacao ";
    $res = $db->query($sql);
    if (PEAR::isError($res)) {
        $CodErroEmail = $res->getCode(); //Obtém o código de erro do Banco de dados oracle
        $DescErroEmail = $res->getMessage(); //Obtém a descrição do erro do Banco de dados oracle

        CancelarAlteracoes($dbora, $db);

        ExibeErroBDRotinas(__FILE__."\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
        exit;
    } else {
        $Linha = $res->fetchRow();

        return $Linha[0];
    }
}

/**
 * Função responsável por corrigir a movimentação 34 (Saída por geração de inventário) quando necessário.
 * Parâmetros:
 * $Movimentacao - Refere-se ao código do tipo movimentação do material que gera lançamentos contábeis,
 * exemplo: 4 - SAÍDA POR REQUISIÇÃO, 10 - ENTRADA POR DOAÇÃO EXTERNA, etc.
 * $Valor - Valor da movimentação dos materiais
 * Retorno:  Retorna a movimentação corrigida.
 */
function CorrigirMovimentacao($Movimentacao, $Valor)
{
    global $MOVIMENTACAO_SAIDA_POR_GERACAO_DE_INVENTARIO;
    global $MOVIMENTACAO_ENTRADA_POR_GERACAO_DE_INVENTARIO;

    $MovimentacaoCorrigida = $Movimentacao;

    /*
       ESTA CORREÇÃO É APENAS PARA AS MOVIMENTAÇÕES 33 (ENTRADA POR GERAÇÃO DE INVENTÁRIO) E
       34 (SAÍDA POR GERAÇÃO DE INVENTÁRIO). POIS, AO REALIZAR O FECHAMENTO POR INVENTÁRIO A MOVIMENTAÇÃO
       A SER EFETUADA É, POR DEFAULT, A MOVIMENTAÇÃO 34, LOGO, SE O VALOR DA MOVIMENTAÇÃO FOR POSITIVO ESTA
       MOVIMENTAÇÃO DEVE SER CORRIGIDA, GERANDO A MOVIMENTAÇÃO 33. NO ENTANTO, SE O VALOR É NEGATIVO OU
       A MOVIMENTAÇÃO É DIFERENTE DE 34 ESTA CORREÇÃO DEVE SER IGNORADA.
     */
    if ($Movimentacao == $MOVIMENTACAO_SAIDA_POR_GERACAO_DE_INVENTARIO && $Valor > 0) {
        $MovimentacaoCorrigida = $MOVIMENTACAO_ENTRADA_POR_GERACAO_DE_INVENTARIO;
    }

    return $MovimentacaoCorrigida;
}

/**
 * Função responsável por incluir os lançamentos contábeis no oracle.
 * Parâmetros:
 * $Movimentacao - Refere-se ao código do tipo movimentação do material que gera lançamentos contábeis. Exemplo:
 * 4 - SAÍDA POR REQUISIÇÃO, 10 - ENTRADA POR DOAÇÃO EXTERNA, etc.
 * $EspecificacoesContabeis - Array que se refere aos tipos de material. Seus valores podem ser "C" - Consumo ou "P" - Permanente.
 * $AnoBaixa - Ano corrente (de exercicio) em que a movimentação foi lançada
 * $MesBaixa - Mês corrente em que a movimentação foi lançada.
 * $DiaBaixa -  Dia corrente em que a movimentação foi lançada.
 * $ValoresContabeis - Array que contém os Valores da movimentação dos materiais separados pelo tipo do material (consumo ou permanente). Este valor é calculado através da "quantidade do material" * "valor médio",
 * para todos os tipos de movimentações, exceto as que envolvem dois almoxarifados, como:
 * 6 - ENTRADA POR EMPRÉSTIMO ENTRE ÓRGÃOS
 * 12 - SAÍDA POR EMPRÉSTIMO ENTRE ÓRGÃOS
 * 9 - ENTRADA POR DEVOLUÇÃO DE EMPRÉSTIMO
 * 13 - SAÍDA POR DEVOLUÇÃO DE EMPRÉSTIMO
 * 11 - ENTRADA POR TROCA
 * 15 - SAÍDA POR TROCA
 * 29 - ENTRADA POR DOAÇÃO ENTRE ALMOXARIFADOS
 * 30 - SAÍDA POR DOAÇÃO ENTRE ALMOXARIFADOS.
 * Já para as movimentações que envolvem dois almoxarifados o valor é dado pela "quantidade do material" * "valor médio" do
 * almoxarifado que realiza a saida para as movimentações de saída(12,13,15,30) e "quantidade do material" * "valor médio" do
 * almoxarifado que realiza a entrada para as movimentações de entrada (6,9,11,29).
 * $Orgao - Refere-se ao órgão do almoxarifado
 * $Unidade - Refere-se a unidade do almoxarifado
 * $Matricula - Refere-se a matricula do almoxarifado
 * $Responsavel - Refere-se ao responsável pela movimentação
 * $SeqRequisicao - Sequencial da requisição (CREQMASEQU em SFPC.TBREQUISICAOMATERIAL)
 * $Almoxarifado - Código do almoxarifado (CALMPOCODI em SFPC.TBALMOXARIFADOPORTAL)
 * $CodigoMovimentacao - Código sequencial do movimento num determinado almoxarifado no ano (CMOVMACODT em SFPC.TBMOVIMENTACAOMATERIAL)
 * $AnoMovimentacao - Ano da movimentação (AMOVMAANOM em SFPC.TBMOVIMENTACAOMATERIAL)
 * $ProgramaDestino - Nome do programa a ser enviado as mensagens de notificação (erro ou sucesso). O formato deve ser: NOME_DO_PROGRAMA.php
 * $dbora - Conexão oracle
 * $db - Conexão Postgresql
 * Retorno: Não possui retorno.
 */
function InsereContabil($Movimentacao, $EspecificacoesContabeis, $AnoBaixa, $MesBaixa, $DiaBaixa, $ValoresContabeis, $Orgao, $Unidade, $Matricula, $Responsavel, $SeqRequisicao, $Almoxarifado, $CodigoMovimentacao, $AnoMovimentacao, $ProgramaDestino, $dbora, $db)
{
    $Responsavel = to_iso($Responsavel);
    global $AlmoxarifadosInvalidos;
    //Apenas gera lançamentos contábeis para almoxarifados válidos
    if (!in_array($Almoxarifado, $AlmoxarifadosInvalidos)) {
        $TodosValoresZerados = true;

        for ($i = 0; $i < count($EspecificacoesContabeis); ++$i) {
            //Permite realizar lançamentos contábeis APENAS com valores diferentes de 0 (zero).
            if ($ValoresContabeis[$i] != 0) {
                $TodosValoresZerados = false;
                $Movimentacao = CorrigirMovimentacao($Movimentacao, $ValoresContabeis[$i]); //Corrige as movimentações 33 e 34 (Entrada e saída por geração de inventário, respectivamente).
                $ValoresContabeis[$i] = abs($ValoresContabeis[$i]); //Corrige valores contábeis

                # Obtém os parâmetros para inclusão #
                $sqlPara = ' SELECT AMVCPMLOTE, AMVCPMTPMC, AMVCPMHIST, AMVCPMCONT, FMVCPMDBCD ';
                $sqlPara .= '   FROM SFPC.TBMOVCONTABILALMOXARIFADOPARAM ';
                $sqlPara .= "  WHERE CTIPMVCODI = $Movimentacao ";
                $sqlPara .= "    AND FMVCPMTIPM = '$EspecificacoesContabeis[$i]' ";
                $sqlPara .= "    AND AMVCPMANOC = $AnoBaixa ";
                $resPara = $db->query($sqlPara);
                if (PEAR::isError($resPara)) {
                    $CodErroEmail = $resPara->getCode(); //Obtém o código de erro do Banco de dados oracle
                    $DescErroEmail = $resPara->getMessage(); //Obtém a descrição do erro do Banco de dados oracle

                    CancelarAlteracoes($dbora, $db);

                    ExibeErroBDRotinas(__FILE__."\nLinha: ".__LINE__."\nSql: $sqlPara\n\n$DescErroEmail ($CodErroEmail)");
                    exit;
                } else {
                    $qtdres = $resPara->numRows();

                    //if($qtdres == 0){
                      # Caso não sejam encontrados parâmetros contábeis exibe mensagens de erro e cancela as alterações no banco #
                      //CancelarAlteracoes($dbora, $db);

                      //$Tipo = 2;
                      //$Mensagem = "Falta cadastrar o parâmetro contábil para esta movimentação";
                      //$Url = EnviarMensagem($ProgramaDestino, $Mensagem, $Tipo);

                      //if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
                      //header("location: ".$Url);
                      //exit;

                    //}else{
                    if ($qtdres > 0) {
                        while ($LinhaPara = $resPara->fetchRow()) {
                            $Sequencial = SequMaxContabil($AnoBaixa, $MesBaixa, $DiaBaixa, $dbora, $db);
                            $Lote = $LinhaPara[0];
                            $TipoMovCont = $LinhaPara[1];
                            $Historico = $LinhaPara[2];
                            $NumeroConta = $LinhaPara[3];
                            $Natureza = $LinhaPara[4];
                            $DescMovimentacao = MovDesc($Movimentacao, $dbora, $db);

                            $DescMovimentacao = to_iso($DescMovimentacao);

                            # Insere as informações no oracle para o lançamento contábil de acordo com os parâmetros já cadastrados no portal de compras #
                            $sqlCont = 'INSERT INTO SFCT.TBMOVCONTABILALMOXARIFADO ';
                            $sqlCont .= '(APLCTAANOC, AMVCALMESM, AMVCALDIAM, AMVCALSEQU, VMVCALVALR, ';
                            $sqlCont .= ' AMVCALLOTE, CTIPMOCODI, AHMOVINUME, APLCTACONT, FMVCALDBCD, ';
                            $sqlCont .= ' CORGORCODI, DEXERCANOR, CUNDORCODI, AMVCALMATR, NMVCALRECE, EMVCALDESC, ';
                            $sqlCont .= ' CMVCALREQU, CMVCALALMO, CMVCALCODI, TMVCALULAT, DMVCALAMVA ';
                            $sqlCont .= ')VALUES(';
                            $sqlCont .= " $AnoBaixa, $MesBaixa, $DiaBaixa, $Sequencial, ".round($ValoresContabeis[$i], 2).', ';
                            $sqlCont .= " $Lote, $TipoMovCont, $Historico, $NumeroConta, '$Natureza', ";
                            $sqlCont .= " $Orgao, $AnoBaixa, $Unidade, $Matricula, '$Responsavel', '$DescMovimentacao', ";
                            $sqlCont .= " $SeqRequisicao, $Almoxarifado, $CodigoMovimentacao, SYSDATE, $AnoMovimentacao ";
                            $sqlCont .= ')';
                            $resCont = $dbora->query($sqlCont);

                            if (PEAR::isError($resCont)) {
                                $CodErroEmail = $resCont->getCode(); //Obtém o código de erro do Banco de dados oracle
                                $DescErroEmail = $resCont->getMessage(); //Obtém a descrição do erro do Banco de dados oracle

                                CancelarAlteracoes($dbora, $db);

                                ExibeErroBDRotinas(__FILE__."\nLinha: ".__LINE__."\nSql: $sqlCont\n\n$DescErroEmail ($CodErroEmail)");
                                exit;
                            }
                        }
                    }
                }
            }
        }

        if ($TodosValoresZerados) {
            # Caso não sejam encontrados parâmetros contábeis exibe mensagens de erro e cancela as alterações no banco #
            CancelarAlteracoes($dbora, $db);

            $Tipo = 2;
            $Mensagem = 'Valor do lançamento contábil igual a zero';
            $Url = EnviarMensagem($ProgramaDestino, $Mensagem, $Tipo);

            redirecionaPagina($Url);
        }
    }
}

/**
 * Função responsável por incluir os lançamentos de custo no oracle.
 * Parâmetros:
 * $SubElementosDespesa - Array que se refere aos sub-elementos de despesa do(s) material(is). Seus valores NORMALMENTE são: 3.3.90.30.XX (para materiais de Consumo) ou 4.4.90.52.XX (para materiais Permanentes).
 * $ValoresSubelementos -  Array que contém os Valores da movimentação dos materiais separados pelo sub-elemento (3.3.90.30.XX, 4.4.90.52.XX ou outro). Este valor é calculado através da "quantidade do material" * "valor médio",
 * $Orgao - Refere-se ao órgão do almoxarifado
 * $Unidade - Refere-se a unidade do almoxarifado
 * $RPA - Refere-se a RPA do órgão do almoxarifado que está realizando a movimentação.
 * $CentroCusto - Refere-se ao centro de custo do almoxarifado (800 para materiais permanentes ou 799 para materiais de consumo) ou do solicitante.
 * Para as movimentações: 6, 9, 10, 11, 12, 13, 14, 15, 16, 17, 23, 24, 25, 26, 27, 28, 29, 30, 32 o parâmetro é o centro de custo do almoxarifado (800 ou 799).
 * Para as movimentações: 2, 4, 21, 22 o parâmetro é o centro de custo do solicitante.
 * $Detalhamento - Refere-se ao detalhamento (Função do governo)
 * $AnoBaixa - Ano corrente (de exercicio) em que a movimentação foi lançada
 * $MesBaixa - Mês corrente em que a movimentação foi lançada.
 * $DiaBaixa -  Dia corrente em que a movimentação foi lançada.
 * $Movimentacao - Refere-se ao código do tipo movimentação do material que gera lançamentos contábeis. Exemplo:
 * 4 - SAÍDA POR REQUISIÇÃO, 10 - ENTRADA POR DOAÇÃO EXTERNA, etc.
 * $Matricula - Refere-se a matricula do almoxarifado
 * $Responsavel - Refere-se ao responsável pela movimentação
 * $SeqRequisicao - Sequencial da requisição (CREQMASEQU em SFPC.TBREQUISICAOMATERIAL)
 * $Almoxarifado - Código do almoxarifado (CALMPOCODI em SFPC.TBALMOXARIFADOPORTAL)
 * $CodigoMovimentacao - Código sequencial do movimento num determinado almoxarifado no ano (CMOVMACODT em SFPC.TBMOVIMENTACAOMATERIAL)
 * $AnoMovimentacao - Ano da movimentação (AMOVMAANOM em SFPC.TBMOVIMENTACAOMATERIAL)
 * $ProgramaDestino - Nome do programa a ser enviado as mensagens de notificação (erro ou sucesso). O formato deve ser: NOME_DO_PROGRAMA.php
 * $dbora - Conexão oracle
 * $db - Conexão Postgresql
 * Retorno: Não possui retorno.
 */
function InsereCusto($SubElementosDespesa, $ValoresSubelementos, $Orgao, $Unidade, $RPA, $CentroCusto, $Detalhamento, $AnoBaixa, $MesBaixa, $DiaBaixa, $Movimentacao, $Matricula, $Responsavel, $SeqRequisicao, $Almoxarifado, $CodigoMovimentacao, $AnoMovimentacao, $ProgramaDestino, $dbora, $db)
{
    $Detalhamento = to_iso($Detalhamento);
    $Responsavel = to_iso($Responsavel);

    /*
      OBSERVAÇÕES:
        LEGENDA: A -> Almoxarifado
                        CC -> Centro de custo Solicitante
                        E -> Entrada
                        S -> Saída

         MOVIMENTACOES DE ENTRADA:
              1 - A: E - (CadInventarioInicialFechamento.php)
              2 - A: E / CC: S - (CadMovimentacaoConfirmar.php)
              6 - A: E  - (CadMovimentacaoConfirmar.php)
              9 - A: E  - (CadMovimentacaoConfirmar.php)
              10 - A: E - (CadMovimentacaoConfirmar.php)
              11 - A1: E-M2 / A2: E-M1 - (CadMovimentacaoConfirmar.php)
              21 - A: E / CC: S - (CadMovimentacaoConfirmar.php)
              26 - A: E - (CadMovimentacaoConfirmar.php)
              28 - A: E - (CadMovimentacaoConfirmar.php)
              29 - A: E - (CadMovimentacaoConfirmar.php)
              32 - A: E - (CadMovimentacaoConfirmar.php)
              33 - A: E - (CadInventarioPeriodicoFechamento.php)

         MOVIMENTACOES DE SAÍDA:
              4 - A: S / CC: E - (CadRequisicaoBaixa.php)
              12 - A: S - (CadMovimentacaoConfirmar.php) - SÓ OCORRE NA MOVIMENTAÇÃO CORRESPODENTE (MOV. 6)
              13 - A: S - (CadMovimentacaoConfirmar.php) - SÓ OCORRE NA MOVIMENTAÇÃO CORRESPODENTE (MOV. 9)
              14 - A: S - (CadMovimentacaoConfirmar.php)
              15 - A1: S-M1 / A2: S-M2 - (CadMovimentacaoConfirmar.php) - SÓ OCORRE NA MOVIMENTAÇÃO CORRESPODENTE (MOV. 11)
              16 - A: S - (CadMovimentacaoConfirmar.php)
              17 - A: S - (CadMovimentacaoConfirmar.php)
              22 - A: S / CC: E - (CadMovimentacaoConfirmar.php)
              23 - A: S - (CadMovimentacaoConfirmar.php)
              24 - A: S - (CadMovimentacaoConfirmar.php)
              25 - A: S - (CadMovimentacaoConfirmar.php)
              27 - A: S - (CadMovimentacaoConfirmar.php)
              30 - A: S - (CadMovimentacaoConfirmar.php) - SÓ OCORRE NA MOVIMENTAÇÃO CORRESPODENTE (MOV. 29)
              34 - A: S - (CadInventarioPeriodicoFechamento.php)
    */

    //Validando a estrutura de custo para realizar o lançamento de custo.
    $sql = 'SELECT  COUNT(*)  FROM  SFCP.TBESTRUTURACUSTO ESTC ';
    $sql .= " WHERE ESTC.DEXERCANOR = $AnoBaixa AND CORGORCODI = $Orgao AND ESTC.CRPAAACODI = $RPA ";
    $sql .= " AND ESTC.CUNDORCODI = $Unidade AND ESTC.CCENCPCODI = $CentroCusto AND ESTC.CDETCPCODI = $Detalhamento ";

    $res = $dbora->query($sql);

    if (PEAR::isError($res)) {
        $CodErroEmail = $res->getCode(); //Obtém o código de erro do Banco de dados oracle
        $DescErroEmail = $res->getMessage(); //Obtém a descrição do erro do Banco de dados oracle
        CancelarAlteracoes($dbora, $db);
        ExibeErroBDRotinas(__FILE__."\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
        exit;
    } else {
        $Linha = $res->fetchRow();
        $qtdrec = $Linha[0];
        if ($qtdrec == 0) {
            CancelarAlteracoes($dbora, $db);
            $MensagemErro = "\nErro ao efetuar lançamento contábil. Não existe estrutura de custo (SFCP.'TBESTRUTURACUSTO - Oracle) com os parâmetros informados:";
            $MensagemErro .= "\nDEXERCANOR = $AnoBaixa\nCORGORCODI = $Orgao\nCRPAAACODI = $RPA";
            $MensagemErro .= "\nCUNDORCODI = $Unidade\nCCENCPCODI = $CentroCusto\nCDETCPCODI = $Detalhamento";
            ExibeErroBDRotinas(__FILE__."\nLinha: ".__LINE__.$MensagemErro);
            exit;
        } else {
            /*
            O código da RPA do almoxarifado não depende do centro de custo (SFPC.TBCENTROCUSTOPORTAL), pois pode haver erro ao divir os centro
            de custos ou mais de um almoxarifado por órgão que podem estar em RPAs distintas.
            */
            $sql = 'SELECT CALMPONRPA ';
            $sql .= '  FROM SFPC.TBALMOXARIFADOPORTAL ';
            $sql .= " WHERE CALMPOCODI = $Almoxarifado ";
            $res = $db->query($sql);

            if (PEAR::isError($res)) {
                $CodErroEmail = $res->getCode(); //Obtém o código de erro do Banco de dados oracle
                $DescErroEmail = $res->getMessage(); //Obtém a descrição do erro do Banco de dados oracle

                CancelarAlteracoes($dbora, $db);

                ExibeErroBDRotinas(__FILE__."\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
                exit;
            } else {
                $Linha = $res->fetchRow();
                $RPAAlmox = $Linha[0];
                $ItensGasto = array();   //Onde, este array tera como indice o item de gasto e o valor será o valor para aquele item de gasto.
                $ValoresItensGasto = array(); //Refere-se a um array que contém os valores dos materiais (valor Unitario * Qtde), separados por item de gasto.

                for ($i = 0; $i < count($SubElementosDespesa); ++$i) {
                    $Codigos = explode('.', $SubElementosDespesa[$i]);
                    $CELED1ELE1 = $Codigos[0];
                    $CELED1ELE2 = $Codigos[1];
                    $CELED1ELE3 = $Codigos[2];
                    $CELED1ELE4 = $Codigos[3];
                    $CSUBEDELEM = $Codigos[4];

                  //OBTENDO OS ITENS DE GASTO COM BASE NOS SUB-ELEMENTOS DE DESPESA.
                    $sql = 'SELECT CESPCPCODI FROM SFCP.TBESPECIFICACAOSUBELEMENTO  ';
                    $sql .= " WHERE DEXERCANOR = $AnoBaixa AND CELED1ELE1 = $CELED1ELE1 AND CELED2ELE2 = $CELED1ELE2 ";
                    $sql .= " AND CELED3ELE3 = $CELED1ELE3 AND CELED4ELE4 = $CELED1ELE4 ";
                    $sql .= " AND CSUBEDELEM = $CSUBEDELEM ";

                    $res = $dbora->query($sql);

                    if (PEAR::isError($res)) {
                        $CodErroEmail = $res->getCode(); //Obtém o código de erro do Banco de dados oracle
                        $DescErroEmail = $res->getMessage(); //Obtém a descrição do erro do Banco de dados oracle

                        CancelarAlteracoes($dbora, $db);

                        ExibeErroBDRotinas(__FILE__."\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
                        exit;
                    } else {
                        $Linha = $res->fetchRow();
                        $ItemGasto = $Linha[0];
                        $ValorItemGasto = $ValoresSubelementos[$i];

                        if ($ItemGasto == null) {
                            # Caso não sejam encontrados parâmetros contábeis exibe mensagens de erro e cancela as alterações no banco #
                            CancelarAlteracoes($dbora, $db);

                            $Tipo = 2;
                            $Mensagem = "Falta cadastrar o item de gasto para o sub-elemento de despesa: $SubElementosDespesa[$i]";
                            $Url = EnviarMensagem($ProgramaDestino, $Mensagem, $Tipo);
                            redirecionaPagina($Url);
                        } else {
                            if (!in_array($ItemGasto, $ItensGasto)) {
                                $indice = count($ItensGasto);
                                $ItensGasto[$indice] = $ItemGasto;
                                $ValoresItensGasto[$indice] = $ValorItemGasto;
                            } else {
                                $indExist = array_search($ItemGasto, $ItensGasto); //Equivale ao indExist: indice existente.
                                $ValoresItensGasto[$indExist] = $ValoresItensGasto[$indExist] + $ValorItemGasto;
                            }
                        }
                    }
                }

                if ($ItensGasto == null || count($ItensGasto) == 0) {
                    # Caso não sejam encontrados parâmetros contábeis exibe mensagens de erro e cancela as alterações no banco #
                    CancelarAlteracoes($dbora, $db);

                    $Tipo = 2;
                    $Mensagem = 'Falta cadastrar o item de gasto para os subelementos';
                    $Url = EnviarMensagem($ProgramaDestino, $Mensagem, $Tipo);
                    redirecionaPagina($Url);
                } else {
                    $TIPO_MOVIMENTACAO_ENTRADA = 'E';
                    $TIPO_MOVIMENTACAO_SAIDA = 'S';
                    $ESPECIFICACAO_CUSTO_PERMANENTE = 27;
                    $CENTRO_CUSTO_PATRIMONIO = 800;
                    $CENTRO_CUSTO_ALMOXARIFADO = 799;
                    $DETALHAMENTO_ALMOXARIFADO_EXTRA_ATIVIDADE = 77;

                    $TodosValoresZerados = true;
                    for ($i = 0; $i < count($ItensGasto); ++$i) {
                        //Permite realizar lançamentos de custo APENAS com valores diferentes de 0 (zero).
                        if ($ValoresItensGasto[$i] != 0) {
                            $TodosValoresZerados = false;
                            $Sequencial = SequMaxCusto($AnoBaixa, $MesBaixa, $DiaBaixa, $dbora, $db);
                            $MovimentacaoCorrigida = CorrigirMovimentacao($Movimentacao, $ValoresItensGasto[$i]); //Corrige as movimentações 33 e 34 (Entrada e saída por geração de inventário, respectivamente).
                            $ValorCusto = abs($ValoresItensGasto[$i]); //Corrige valores de custo
                            $EspCusto = $ItensGasto[$i];
                            $DescMovimentacao = MovDesc($MovimentacaoCorrigida, $dbora, $db);
                            $DescMovimentacao = to_iso($DescMovimentacao);

                            $sql = "SELECT FTIPMVTIPO FROM SFPC.TBTIPOMOVIMENTACAO WHERE CTIPMVCODI = $MovimentacaoCorrigida";
                            $res = $db->query($sql);

                            if (PEAR::isError($res)) {
                                $CodErroEmail = $res->getCode(); //Obtém o código de erro do Banco de dados oracle
                                $DescErroEmail = $res->getMessage(); //Obtém a descrição do erro do Banco de dados oracle

                                CancelarAlteracoes($dbora, $db);

                                ExibeErroBDRotinas(__FILE__."\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
                                exit;
                            } else {
                                $Linha = $res->fetchRow(); //Refere-se ao tipo de movimentação (E - Entrada ou S - Saída) do almoxarifado
                                $TipoMovAlmox = $Linha[0];
                                $TipoMovSolic = null; //Refere-se ao tipo de movimentação (E - Entrada ou S - Saída) do centro de custo solicitante

                                /*
                                Para as movimentações que possuem o sequencial da requisição possuem o centro de custo solicitante,
                                tais como as seguintes movimentações: 2, 4, 21, 22. Assim, caso o tipo de movimento para o almoxarifado
                                seja de saída, o tipo do movimento será de entrada para o centro de custo solicitante e vice-versa.
                                */
                                if ($SeqRequisicao != 'NULL') {
                                    if ($TipoMovAlmox == $TIPO_MOVIMENTACAO_ENTRADA) {
                                        $TipoMovSolic = $TIPO_MOVIMENTACAO_SAIDA;
                                    } else {
                                        $TipoMovSolic = $TIPO_MOVIMENTACAO_ENTRADA;
                                    }

                                    if ($EspCusto == $ESPECIFICACAO_CUSTO_PERMANENTE) {
                                        $CentroCustoAlmox = $CENTRO_CUSTO_PATRIMONIO;
                                    } else {
                                        $CentroCustoAlmox = $CENTRO_CUSTO_ALMOXARIFADO;
                                    }
                                    $DetalhamentoAlmox = $DETALHAMENTO_ALMOXARIFADO_EXTRA_ATIVIDADE;
                                } else {
                                    //Não possui Requisição logo, o Centro de custo e o detalhamento do almoxarifado são passados por parametro.
                                    $CentroCustoAlmox = $CentroCusto;
                                    $DetalhamentoAlmox = $Detalhamento;
                                }

                                $sql = 'INSERT INTO SFCP.TBMOVCUSTOALMOXARIFADO ( ';
                                $sql .= 'DEXERCANOR, AMOVCUMESM, AMOVCUDIAM, CMOVCUSEQU, CRPAAACODI, ';
                                $sql .= 'CCENCPCODI, CESPCPCODI, CDETCPCODI, CORGORCODI, CUNDORCODI, ';
                                $sql .= 'AMOVCUMATR, NMOVCURECE, CORGORCOD1, CUNDORCOD1, VMOVCUREQU, ';
                                $sql .= 'TMOVCUULAT, FMOVCULANC, EMOVCUDESC, CMOVCUREQU, CMOVCUALMO, ';
                                $sql .= 'CMOVCUCODI, DMOVCUAMVA ';
                                $sql .= ') VALUES ( ';
                                $sql .= "$AnoBaixa, $MesBaixa, $DiaBaixa, $Sequencial, $RPAAlmox, ";
                                $sql .= "$CentroCustoAlmox, $EspCusto, $DetalhamentoAlmox, $Orgao, $Unidade, ";
                                $sql .= "$Matricula, '$Responsavel', $Orgao, $Unidade, ".round($ValorCusto, 2).', ';
                                $sql .= "SYSDATE, '$TipoMovAlmox', '$DescMovimentacao', $SeqRequisicao, $Almoxarifado, ";
                                $sql .= "$CodigoMovimentacao, $AnoMovimentacao )";

                                $res = $dbora->query($sql);

                                if (PEAR::isError($res)) {
                                    $CodErroEmail = $res->getCode(); //Obtém o código de erro do Banco de dados oracle
                                    $DescErroEmail = $res->getMessage(); //Obtém a descrição do erro do Banco de dados oracle

                                    CancelarAlteracoes($dbora, $db);

                                    ExibeErroBDRotinas(__FILE__."\nLinha: ".__LINE__."\nSql: ".($sql)."\n\n$DescErroEmail ($CodErroEmail)");
                                    exit;
                                } else {
                                    if ($SeqRequisicao != 'NULL') {
                                        $Sequencial = SequMaxCusto($AnoBaixa, $MesBaixa, $DiaBaixa, $dbora, $db);

                                        $sql = 'INSERT INTO SFCP.TBMOVCUSTOALMOXARIFADO ( ';
                                        $sql .= 'DEXERCANOR, AMOVCUMESM, AMOVCUDIAM, CMOVCUSEQU, CRPAAACODI, ';
                                        $sql .= 'CCENCPCODI, CESPCPCODI, CDETCPCODI, CORGORCODI, CUNDORCODI, ';
                                        $sql .= 'AMOVCUMATR, NMOVCURECE, CORGORCOD1, CUNDORCOD1, VMOVCUREQU, ';
                                        $sql .= 'TMOVCUULAT, FMOVCULANC, EMOVCUDESC, CMOVCUREQU, CMOVCUALMO, ';
                                        $sql .= 'CMOVCUCODI, DMOVCUAMVA ';
                                        $sql .= ') VALUES ( ';
                                        $sql .= "$AnoBaixa, $MesBaixa, $DiaBaixa, $Sequencial, $RPA, ";
                                        $sql .= "$CentroCusto, $EspCusto, $Detalhamento, $Orgao, $Unidade, ";
                                        $sql .= "$Matricula, '$Responsavel', $Orgao, $Unidade, ".round($ValorCusto, 2).', ';
                                        $sql .= "SYSDATE, '$TipoMovSolic', '$DescMovimentacao', $SeqRequisicao, $Almoxarifado, ";
                                        $sql .= "$CodigoMovimentacao, $AnoMovimentacao )";

                                        $res = $dbora->query($sql);

                                        if (PEAR::isError($res)) {
                                            $CodErroEmail = $res->getCode(); //Obtém o código de erro do Banco de dados oracle
                                            $DescErroEmail = $res->getMessage(); //Obtém a descrição do erro do Banco de dados oracle

                                            CancelarAlteracoes($dbora, $db);

                                            ExibeErroBDRotinas(__FILE__."\nLinha: ".__LINE__."\nSql: ".($sql)."\n\n$DescErroEmail ($CodErroEmail)");
                                            exit;
                                        }
                                    }
                                }
                            }
                        }
                    }
                    if ($TodosValoresZerados) {
                        # Caso o valor do lançamento para custo seja igual a zero cancela as alterações no banco #
                        CancelarAlteracoes($dbora, $db);

                        $Tipo = 2;
                        $Mensagem = "Valor do lançamento para custo igual a zero (item de gasto = $ItensGasto[$i])";
                        $Url = EnviarMensagem($ProgramaDestino, $Mensagem, $Tipo); //TESTE

                        redirecionaPagina($Url);
                    }
                }
            }
        }
    }
}

/**
 * Função responsável por gerar os lançamentos contábeis de acordo com a movimentação.
 * Parâmetros:
 * $Orgao - Refere-se ao órgão do almoxarifado
 * $RPA - Refere-se a RPA do órgão do almoxarifado que está realizando a movimentação.
 * $Unidade - Refere-se a unidade do almoxarifado
 * $CentroCusto - Refere-se ao centro de custo do almoxarifado (800 para materiais permanentes ou 799 para materiais de consumo) ou do solicitante.
 * Para as movimentações: 6, 9, 10, 11, 12, 13, 14, 15, 16, 17, 23, 24, 25, 26, 27, 28, 29, 30, 32 o parâmetro é o centro de custo do almoxarifado (800 ou 799).
 * Para as movimentações: 2, 4, 21, 22 o parâmetro é o centro de custo do solicitante.
 * $Detalhamento - Refere-se ao detalhamento (Função do governo)
 * $Movimentacao - Refere-se ao código do tipo movimentação do material que gera lançamentos contábeis. Exemplo:
 * 4 - SAÍDA POR REQUISIÇÃO, 10 - ENTRADA POR DOAÇÃO EXTERNA, etc.
 * $AnoBaixa - Ano corrente (de exercicio) em que a movimentação foi lançada
 * $MesBaixa - Mês corrente em que a movimentação foi lançada.
 * $DiaBaixa -  Dia corrente em que a movimentação foi lançada.
 * $EspecificacoesContabeis - Array que se refere aos tipos de material. Seus valores podem ser "C" - Consumo ou "P" - Permanente.
 * $ValoresContabeis - Array que contém os Valores da movimentação dos materiais separados pelo tipo do material (consumo ou permanente). Este valor é calculado através da "quantidade do material" * "valor médio",
 * para todos os tipos de movimentações, exceto as que envolvem dois almoxarifados, como:
 * 6 - ENTRADA POR EMPRÉSTIMO ENTRE ÓRGÃOS
 * 12 - SAÍDA POR EMPRÉSTIMO ENTRE ÓRGÃOS
 * 9 - ENTRADA POR DEVOLUÇÃO DE EMPRÉSTIMO
 * 13 - SAÍDA POR DEVOLUÇÃO DE EMPRÉSTIMO
 * 11 - ENTRADA POR TROCA
 * 15 - SAÍDA POR TROCA
 * 29 - ENTRADA POR DOAÇÃO ENTRE ALMOXARIFADOS
 * 30 - SAÍDA POR DOAÇÃO ENTRE ALMOXARIFADOS.
 * Já para as movimentações que envolvem dois almoxarifados o valor é dado pela "quantidade do material" * "valor médio" do
 * almoxarifado que realiza a saida para as movimentações de saída(12,13,15,30) e "quantidade do material" * "valor médio" do
 * almoxarifado que realiza a entrada para as movimentações de entrada (6,9,11,29).
 * $SubElementosDespesa - Array que se refere aos sub-elementos de despesa do(s) material(is). Seus valores NORMALMENTE são: 3.3.90.30.XX (para materiais de Consumo) ou 4.4.90.52.XX (para materiais Permanentes).
 * $ValoresSubelementos - Array que contém os Valores da movimentação dos materiais separados pelo sub-elemento (3.3.90.30.XX, 4.4.90.52.XX ou outro). Este valor é calculado através da "quantidade do material" * "valor médio",
 * $Matricula - Refere-se a matricula do almoxarifado
 * $Responsavel - Refere-se ao responsável pela movimentação
 * $SeqRequisicao - Sequencial da requisição (CREQMASEQU em SFPC.TBREQUISICAOMATERIAL)
 * $Almoxarifado - Código do almoxarifado (CALMPOCODI em SFPC.TBALMOXARIFADOPORTAL)
 * $CodigoMovimentacao - Código sequencial do movimento num determinado almoxarifado no ano (CMOVMACODT em SFPC.TBMOVIMENTACAOMATERIAL)
 * $AnoMovimentacao - Ano da movimentação (AMOVMAANOM em SFPC.TBMOVIMENTACAOMATERIAL)
 * $ProgramaDestino - Nome do programa a ser enviado as mensagens de notificação (erro ou sucesso). O formato deve ser: NOME_DO_PROGRAMA.php
 * $dbora - Conexão oracle
 * $db - Conexão Postgresql
 * $ConfirmarInclusao - Confirma a inclusão das inserções no oracle e enviar mensagem de sucesso ao usuário.
 *
 * Retorno: Não possui retorno
 */
function GerarLancamentoCustoContabil(
    $Orgao,
    $RPA,
    $Unidade,
    $CentroCusto,
    $Detalhamento,
    $Movimentacao,
    $AnoBaixa,
    $MesBaixa,
    $DiaBaixa,
    $EspecificacoesContabeis,
    $ValoresContabeis,
    $SubElementosDespesa,
    $ValoresSubelementos,
    $Matricula,
    $Responsavel,
    $SeqRequisicao,
    $Almoxarifado,
    $CodigoMovimentacao,
    $AnoMovimentacao,
    $ProgramaDestino,
    $dbora,
    $db,
    $ConfirmarInclusao
) {
    $Responsavel = to_iso($Responsavel);
    $Detalhamento = to_iso($Detalhamento);

    /*
        - O CodigoMovimentacao deve ser NULL, caso seja, a movimentação SAÍDA POR REQUISIÇÃO (Cod. 4), caso contrário, este deve ser informado.
        - Apenas terá o código da movimentação (Código sequencial do movimento no ano) as movimentações: 1,2,10,21,26,28,32,14,16,17,22,23,24,25,27,6,9,11,29.
      */
    if (!$CodigoMovimentacao || $CodigoMovimentacao == null) {
        $CodigoMovimentacao = 'NULL';
    }

    //Apenas terá sequencial da requisição as seguintes movimentações: 2, 21 e 22
    if (!$SeqRequisicao || $SeqRequisicao == null) {
        $SeqRequisicao = 'NULL';
    }

    //ALTERAR PARA ESTE IF QUANDO TODOS ESTIVEREM COM ARRAY DE ESPECIFICAÇÕES_CONTABEIS (TIPOS_DE_MATERIAL), VALORES_CONTABEIS, SUBELEMENTOS, VALORES_SUBELEMENTOS.....
    if (((count($EspecificacoesContabeis) > 0 && count($ValoresContabeis) > 0)) && (count($SubElementosDespesa) > 0 && count($ValoresSubelementos) > 0)) {
        InsereContabil(
            $Movimentacao,
            $EspecificacoesContabeis,
            $AnoBaixa,
            $MesBaixa,
            $DiaBaixa,
            $ValoresContabeis,
            $Orgao,
            $Unidade,
            $Matricula,
            $Responsavel,
            $SeqRequisicao,
            $Almoxarifado,
            $CodigoMovimentacao,
            $AnoMovimentacao,
            $ProgramaDestino,
            $dbora,
            $db
        );

        InsereCusto(
            $SubElementosDespesa,
            $ValoresSubelementos,
            $Orgao,
            $Unidade,
            $RPA,
            $CentroCusto,
            $Detalhamento,
            $AnoBaixa,
            $MesBaixa,
            $DiaBaixa,
            $Movimentacao,
            $Matricula,
            $Responsavel,
            $SeqRequisicao,
            $Almoxarifado,
            $CodigoMovimentacao,
            $AnoMovimentacao,
            $ProgramaDestino,
            $dbora,
            $db
        );
    } else {
        //Cancelar o lançamento caso não existe os subelementos de despesa e seus valores,
        // Não é efetuado nenhum laçamento custo/contábil.
        CancelarAlteracoes($dbora, $db);

        $Tipo = 2;
        $Mensagem = 'Valores Custo/Contábil inválidos';
        $Url = EnviarMensagem($ProgramaDestino, $Mensagem, $Tipo);
        redirecionaPagina($Url);
    }

    if ($ConfirmarInclusao) {
        //CASO TENHA INSERIDO O LANÇAMENTO CUSTO/CONTABIL COM SUCESSO, REALIZA CONFIRMA AS INCLUSÕES NO POSTGRESQL E NO ORACLE.
        ConfirmarAlteracoes($dbora, $db);

        $Tipo = 1; //Notificação de sucesso ao usuário.

        # Trabalha retorno de acordo com o programa que chamou a rotina, ou seja, enviando mensagem de sucesso ao usuário #
        if ($ProgramaDestino == 'CadMovimentacaoConfirmar.php') {
            # Grava dados para controle de F5 # #OBS: Está função já tinha sido definida, logo, apenas repliquei aqui, para evitar impactos ao realizar os lançamentos de custo e contábil.
            GravaSessionChkF5($Almoxarifado, $AnoBaixa, $Movimentacao, $Material, $QtdMovimentada, $GrupoEmp, $Usuario, $DataGravacao);
            $Mensagem = 'Movimentação Incluída com Sucesso';
            $Url = EnviarMensagem($ProgramaDestino, $Mensagem, $Tipo);
        } elseif ($ProgramaDestino == 'CadMovimentacaoAlterar.php') {
            $Mensagem = 'Movimentação Alterada com Sucesso';
            $Url = EnviarMensagem($ProgramaDestino, $Mensagem, $Tipo);
        } elseif ($ProgramaDestino == 'CadRequisicaoBaixa.php') {
            $Mensagem = 'Baixa da Requisição efetuada com Sucesso';
            $Url = EnviarMensagem($ProgramaDestino, $Mensagem, $Tipo);
        } elseif ($ProgramaDestino == 'CadInventarioPeriodicoFechamento.php') {
            $Mensagem = 'Inventário Periódico Concluído com Sucesso';
            $Url = EnviarMensagem($ProgramaDestino, $Mensagem, $Tipo);
        } elseif ($ProgramaDestino == 'CadInventarioInicialFechamento.php') {
            $Mensagem = 'Inventário Inicial Concluído com Sucesso';
            $Url = EnviarMensagem($ProgramaDestino, $Mensagem, $Tipo);
        }

        redirecionaPagina($Url);
    }
}
//FIM DAS FUNÇÕES


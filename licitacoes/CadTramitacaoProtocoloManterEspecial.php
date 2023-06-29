<?php
/**
 * Portal de Compras
 * 
 * Programa: CadTramitacaoProtocoloManterEspecial.php
 * ------------------------------------------------------------------------------
 * Alterado: Pitang Agile TI - Caio Coutinho
 * Data:     08/04/2019
 * Objetivo: Tarefa Redmine 213474
 * ------------------------------------------------------------------------------
 * Alterado: Lucas Baracho
 * Data:     29/05/2019
 * Objetivo: Tarefa Redmine 217442
 * ------------------------------------------------------------------------------
 * Alterado: Lucas Baracho
 * Data:     02/07/2019
 * Objetivo: Tarefa Redmine 219492
 * ------------------------------------------------------------------------------
 * Alterado: Lucas Baracho
 * Data:     12/07/2019
 * Objetivo: Tarefa Redmine 220301
 * ------------------------------------------------------------------------------
 * Alterado: Lucas Baracho
 * Data:     12/07/2019
 * Objetivo: Tarefa Redmine 220312
 * ------------------------------------------------------------------------------
 * Alterado: Lucas Baracho
 * Data:     12/07/2019
 * Objetivo: Tarefa Redmine 216152
 * ------------------------------------------------------------------------------
 * Alterado: Lucas Baracho
 * Data:     12/07/2019
 * Objetivo: Tarefa Redmine 220495
 * ------------------------------------------------------------------------------
 * Alterado: João Madson   
 * Data:     24/10/2019
 * Objetivo: Tarefa Redmine 224909
 * ------------------------------------------------------------------------------
 * Alterado: Lucas Vicente   
 * Data:     21/09/2022
 * Objetivo: Tarefa Redmine 235738
 * ------------------------------------------------------------------------------
 */

# Acesso ao arquivo de funções #
include "../funcoes.php";
include "./funcoesTramitacao.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD']  == "POST") {
    $botao                    = $_POST['Botao'];
    $protocoloAtual           = $_POST['tramitacaoProtocolo'];
    $grupoAtual               = $_POST['tramitacaoGrupo'];
    $orgaoAtual               = $_POST['tramitacaoOrgao'];
    $objetoAtual              = strtoupper2($_POST['tramitacaoObjeto']);
    $numeroCIAtual            = strtoupper2($_POST['tramitacaoNumeroCI']);
    $numeroOficioAtual        = strtoupper2($_POST['tramitacaoNumeroOficio']);
    $comissaoLicitacaoAtual    = $_POST['tramitacaoComissaoLicitacao'];
    $processoNumeroAtual      = $_POST['tramitacaoProcessoNumero'];
    $processoAnoAtual         = $_POST['tramitacaoProcessoAno'];
    $dataEntradaAtual         = $_POST['tramitacaoDataEntrada'];
    $valorEstimatoTotalAtual  = $_POST['tramitacaoValorEstimadoTotal'];
    $observacaoAtual          = strtoupper2($_POST['tramitacaoObservacao']);
    $monitoramentoAtual       = strtoupper2($_POST['tramitacaoMonitoramento']);
    $solicitacaoAtual         = $_POST['seqSolicitacao'];
    $numeroProtocoloAtual     = $_POST['tramitacaoNumeroProtocolo'];
    $anoProtocoloAtual        = $_POST['tramitacaoAno'];
    $DDocumento               = $_POST['DDocumento'];
    $DDocumentoTram           = $_POST['DDocumentoTram'];
    $orgaoRetorno             = $_POST['tramitacaoOrgaoRetorno'];
    $numeroProtocoloRetorno   = $_POST['tramitacaoNumeroProtocoloRetorno'];
    $anoProtocoloRetorno      = $_POST['tramitacaoAnoProtocoloRetorno'];
    $dataEntradaRetornoInicio = $_POST['tramitacaoDataEntradaInicioRetorno'];
    $dataEntradaRetornoFim    = $_POST['tramitacaoDataEntradaFimRetorno'];
    $grupoRetorno             = $_POST['tramitacaoGrupoRetorno'];
    $acao                     = $_POST['acao'];
    $agenteDestino            = $_POST['agenteDestino'];
    $dataEntradaTramitacao    = $_POST['DataEntradaTramitacao'];
    $horaTramitacao           = $_POST['HoraTramitacao'];
    $observacao               = $_POST['observacao'];
    $sequencialTramitacao     = $_POST['seqTramitacao'];
    $usuarioResponsavel       = $_POST['usuarioResponsavel'];
    $prazoAcao                = $_POST['prazoAcao'];
    $comissaoAtual            = $_POST['comissaoAtual'];
    $dadosDoBanco             = 0;
} else {
    $protocoloAtual           = $_GET['protocolo'];
    $Critica                  = $_GET['Critica'];
    $Mensagem                 = urldecode($_GET['Mensagem']);
    $Mens                     = $_GET['Mens'];
    $Tipo                     = $_GET['Tipo'];
    $orgaoRetorno             = $_GET['tramitacaoOrgao'];
    $numeroProtocoloRetorno   = $_GET['tramitacaoNumeroProtocolo'];
    $anoProtocoloRetorno      = $_GET['tramitacaoAnoProtocolo'];
    $dataEntradaRetornoInicio = $_GET['tramitacaoDataEntradaInicio'];
    $dataEntradaRetornoFim    = $_GET['tramitacaoDataEntradaFim'];
    $grupoRetorno             = $_GET['tramitacaoGrupo'];
    $protocolo                = getProtocolo($protocoloAtual);
    $documentos               = getProtocoloAnexos($protocoloAtual);
    $arrUltimoPasso           = getTramitacaoUltimoPasso($protocoloAtual);

    if ($arrUltimoPasso[11]) {
        $documentosTram = getTramitacaoLicitacaoAnexos($arrUltimoPasso[11], $protocoloAtual);
    }

    $dadosDoBanco = 1;

    if (!empty($protocolo)) {
        $grupoAtual              = $protocolo[1];
        $numeroProtocoloAtual    = $protocolo[2];
        $anoProtocoloAtual       = $protocolo[3];
        $orgaoAtual              = $protocolo[4];
        $objetoAtual             = $protocolo[5];
        $numeroCIAtual           = $protocolo[6];
        $numeroOficioAtual       = $protocolo[7];
        $solicitacaoAtual        = $protocolo[8];
        $comissaoLicitacaoAtual  = $protocolo[12];
        $processoNumeroAtual     = $protocolo[9];
        $processoAnoAtual        = $protocolo[10];
        $dataEntradaAtual        = substr($protocolo[14], 8, 2) . "/" . substr($protocolo[14], 5, 2) . "/" . substr($protocolo[14], 0, 4);
        $sequencialTramitacao    = $arrUltimoPasso[11]; 
        $acao                    = $arrUltimoPasso[2]."_".$arrUltimoPasso[13]."_".$arrUltimoPasso[14]."_".$arrUltimoPasso[15]."_".$arrUltimoPasso[16];
        $agenteDestino           = $arrUltimoPasso[3]."_".$arrUltimoPasso[10];
        $comissaoAtual           = $arrUltimoPasso[17];
        $usuarioResponsavel      = $arrUltimoPasso[12];   
        $calcDateTime            = $arrUltimoPasso[5];
        $calcDateTime            = explode(' ',$calcDateTime);
        $calcDate                = $calcDateTime[0];
        $calcDate                = explode('-', $calcDate);
        $dataEntradaTramitacao   = $calcDate[2]."/".$calcDate[1]."/".$calcDate[0];
        $calcTime                = $calcDateTime[1];
        $calcTime                = explode(':', $calcTime);
        $horaTramitacao          = $calcTime[0].":".$calcTime[1];
        $observacao              = $arrUltimoPasso[8];
        $valorEstimatoTotalAtual = $protocolo[15];
        $observacaoAtual         = $protocolo[16];
        $monitoramentoAtual      = $protocolo[17];

        if (!empty($solicitacaoAtual)) {                
            $arrProcesso = getProcessoScc($solicitacaoAtual);

            if (!empty($arrProcesso)) {
                $arrProcesso = $arrProcesso[0];
            }

            if (!empty($arrProcesso[0])) {
                $processo = str_pad($arrProcesso[0], 4, "0", STR_PAD_LEFT) . '/' . $arrProcesso[1]. ' - '. $arrProcesso[5];
                $_SESSION['sccProcessoLic'] = $arrProcesso[0].'-'.$arrProcesso[1].'-'.$arrProcesso[2];
                $objProcesso = $arrProcesso[6];
            } else {
                $objProcesso = '';
                $_SESSION['sccProcessoLic'] = '';
            }

            $_SESSION['sccTramitacao'] = $solicitacaoAtual.'-'.getNumeroSolicitacaoCompra($db, $solicitacaoAtual).'-'.$objProcesso; 
        } else {
            $_SESSION['sccTramitacao'] = '';
            $_SESSION['sccProcessoLic'] = '';
        }        

        $_SESSION['grupo_selecionado_protocolo'] = $protocolo[1] ;

        if (!empty($documentos)) {
            $_SESSION['Arquivos_Upload'] = $documentos;
        }
        
        if (!empty($documentosTram)) {
            $_SESSION['Arquivos_Upload_Tramitacao'] = $documentosTram;
        }
    } else {
        $Mensagem = "Protocolo de Tramitação Inválido";
        header('Location: CadTramitacaoProtocoloPesquisarEspecial.php?Mens=1&Tipo=1&Mensagem=' . $Mensagem);
    }
}

if (empty($processoAnoAtual) && empty($processoNumeroAtual) && empty($solicitacaoAtual)) {
    $comissaoLicitacaoAtual = '';
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "CadTramitacaoProtocoloManterEspecial.php";

$parametrosGerais = dadosParametrosGerais();
$tamanhoObjeto           = $parametrosGerais[0];
$tamanhoJustificativa    = $parametrosGerais[1];
$tamanhoDescricaoServico = strlen($parametrosGerais[2]);
$subElementosEspeciais   = explode(',', $parametrosGerais[3]);
$tamanhoArquivo          = $parametrosGerais[4];
$tamanhoNomeArquivo      = $parametrosGerais[5];
$extensoesArquivo        = $parametrosGerais[6];

//Retorna os Dados das Ações
$htmlAcao = '';

$sql  = "SELECT CTACAOSEQU, ETACAODESC, ATACAOPRAZ, FTACAOCOMI, FTACAOANEX, ";
$sql .= "       FTACAOTUSU ";
$sql .= "FROM   SFPC.TBTRAMITACAOACAO ";
$sql .= "WHERE  FTACAOSITU = 'A' ";
$sql .= "       AND CGREMPCODI = " . $_SESSION['_cgrempcodi_'];
$sql .= " ORDER BY ATACAOORDE ASC ";

$result = $db->query($sql);

if (PEAR::isError($result)) {
    ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
} else {
    while ($Linha = $result->fetchRow()) {
        if ($Linha[0]."_".$Linha[2]."_".$Linha[3]."_".$Linha[4]."_".$Linha[5] == $acao) {
            $htmlAcao.= "<option selected='selected' value=\"$Linha[0]_$Linha[2]_$Linha[3]_$Linha[4]_$Linha[5]\">$Linha[1]</option>\n";
            $acaoDesc = $Linha[1];
        } else {
            $htmlAcao.= "<option value=\"$Linha[0]_$Linha[2]_$Linha[3]_$Linha[4]_$Linha[5]\">$Linha[1]</option>\n";
        }
    }
}

//Retorna os Dados dos Agentes Destino
$htmlAgenteDestino = '';

if (!$receberProtocolo) {
    $sql = "SELECT CTAGENSEQU, ETAGENDESC, FTAGENTIPO FROM SFPC.TBTRAMITACAOAGENTE WHERE FTAGENSITU = 'A' AND CGREMPCODI = ".$_SESSION['_cgrempcodi_'];

    if ($ultimoPasso) {
        $sql .= " AND CTAGENSEQU <> ".$ultimoPasso[3];
    }
} else {
    $sql  = "SELECT CTAGENSEQU, ETAGENDESC, FTAGENTIPO ";
    $sql .= "FROM   SFPC.TBTRAMITACAOAGENTE ";
    $sql .= "WHERE  CGREMPCODI = " . $_SESSION['_cgrempcodi_'];
    $sql .= "       AND CTAGENSEQU IN ( SELECT  AGUSU.CTAGENSEQU ";
    $sql .= "                           FROM    SFPC.TBTRAMITACAOAGENTEUSUARIO AGUSU ";
    $sql .= "                                   JOIN SFPC.TBUSUARIOPORTAL USU ON USU.CUSUPOCODI = AGUSU.CUSUPOCODI ";
    $sql .= "                           WHERE   USU.CUSUPOCODI =".$_SESSION['_cusupocodi_']." ) ";
}

$sql .= " ORDER BY ETAGENDESC ASC";

$result = $db->query($sql);

if (PEAR::isError($result)) {
    ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
} else {
    while ($Linha = $result->fetchRow()) {
        if ("$Linha[0]_$Linha[2]" == $agenteDestino) {
            $htmlAgenteDestino.= "<option selected='selected' value=\"$Linha[0]_$Linha[2]\">$Linha[1]</option>\n";
            $destinoDesc = $Linha[1];
        } else {
            $htmlAgenteDestino.= "<option value=\"$Linha[0]_$Linha[2]\">$Linha[1]</option>\n";
        }
    }
}

//Retorna os Dados dos Usuários do agente
$htmlUsuarioResponsavel = '';

$sql  = "SELECT USU.CUSUPOCODI, USU.EUSUPORESP, AGUSU.CTAGENSEQU ";
$sql .= "FROM   SFPC.TBTRAMITACAOAGENTEUSUARIO AGUSU ";
$sql .= "       JOIN SFPC.TBUSUARIOPORTAL USU ON USU.CUSUPOCODI = AGUSU.CUSUPOCODI ";
$sql .= "WHERE  1 = 1 ";
$sql .= "ORDER BY USU.EUSUPORESP ";

$result = $db->query($sql);

if (PEAR::isError($result)) {
    ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
} else {
    while ($Linha = $result->fetchRow()) {
        if ($Linha[0] == $usuarioResponsavel) {
            $htmlUsuarioResponsavel.= "<option selected='selected' value=\"$Linha[0]\">$Linha[1]</option>\n";
        } else {
            $htmlUsuarioResponsavel.= "<option value=\"$Linha[0]\">$Linha[1]</option>\n";
        }

        $arrUsuariosAgente[] = $Linha;
    }
}

if ($botao == 'SelecionarGrupo') {
    if ($grupoAtual != '') {
        $numeroProtocoloAtual = getNumeroProtocolo($grupoAtual);
    }

    //passagem de parametro para não dar erro
    $valorEstimatoTotalAtual = str_replace(',','.',str_replace('.','',$valorEstimatoTotalAtual));
} elseif ($botao == 'Alterar') {
    $validar = true;

    if (!empty($processoNumeroAtual)) {
        if (!SoNumeros($processoNumeroAtual)) {
            adicionarMensagem("<a href='javascript:document.getElementById(\"inputProtocolo\").focus();' class='titulo2'>Número do processo deve ser numérico</a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
            $validar = false;
        }
    }

    if (!empty($processoAnoAtual)) {
        if (!SoNumeros($processoAnoAtual)) {
            adicionarMensagem("<a href='javascript:document.getElementById(\"inputProtocoloAno\").focus();' class='titulo2'>Ano do processo deve ser numérico</a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
            $validar = false;
        }
    }

    if ($grupoAtual == '') {
        adicionarMensagem("<a href='javascript:document.getElementById(\"inputGrupo\").focus();' class='titulo2'>Grupo</a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
        $validar = false;
    } else {
        // Verificar Acao
        if (empty($acao)) {
            adicionarMensagem("<a href='javascript:document.getElementById(\"inputEntrada\").focus();' class='titulo2'>Nenhuma ação inicial cadastrada para o Grupo</a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
            $validar = false;
        }

        // Verificar Agente Inicial
        $maxAgente = getInicialAgente($grupoAtual);

        if (empty($maxAgente)) {
            adicionarMensagem("<a href='javascript:document.getElementById(\"inputEntrada\").focus();' class='titulo2'>Nenhum Agente Inicial Cadastrado Para o Grupo</a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
            $validar = false;
        }
    }

    if ($orgaoAtual == '') {
        adicionarMensagem("<a href='javascript:document.getElementById(\"inputOrgao\").focus();' class='titulo2'>Órgão</a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
        $validar = false;
    }

    if ($objetoAtual == '') {
        adicionarMensagem("<a href='javascript:document.getElementById(\"inputObjeto\").focus();' class='titulo2'>Objeto</a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
        $validar = false;
    }

    if ($dataEntradaAtual == '') {
        adicionarMensagem("<a href='javascript:document.getElementById(\"inputEntrada\").focus();' class='titulo2'>Data de Entrada</a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
        $validar = false;
    } else {
        $DataEntradaCheck = explode("/",$dataEntradaAtual);

        if (!checkdate($DataEntradaCheck[1],$DataEntradaCheck[0],$DataEntradaCheck[2])) {
            adicionarMensagem("<a href='javascript:document.getElementById(\"inputEntrada\").focus();' class='titulo2'>Data de Entrada Inválida</a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
            $validar = false;
        }

        $dateTimeIni = strtotime($DataEntradaCheck[2].'-'.$DataEntradaCheck[1].'-'.$DataEntradaCheck[0]);
        $hoje = strtotime(date('Y/m/d'));
         
        if ($dateTimeIni > $hoje) {
            adicionarMensagem("<a href='javascript:document.getElementById(\"inputEntrada\").focus();' class='titulo2'>Data de Entrada deve ser menor ou igual a de hoje</a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
            $validar = false;
        }
    }

    // Verificar CI e Oficio - Não podem estar os dois vazios (pelo menos um deve ser preenchido pelo usuario)
    if (empty($numeroCIAtual ) && empty($numeroOficioAtual)) {
        adicionarMensagem("<a href='javascript:document.getElementById(\"tramitacaoNumeroCI\").focus();' class='titulo2'>Os campos CI e OFICIO estão vazios, você deve preencher pelo menos um dos dois campos</a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
        $validar = false;
    }

    if (!empty($numeroOficioAtual) && (!empty($orgaoAtual))) {
        $jaExisteOficioEmOutroProtocolo =  verificaOficioEmProtocolos($numeroOficioAtual, $orgaoAtual, $protocoloAtual);

        if ($jaExisteOficioEmOutroProtocolo) {
            adicionarMensagem("<a href='javascript:document.getElementById(\"inputProcesso\").focus();' class='titulo2'>O ofício já está cadastrado no protocolo ".str_pad($jaExisteOficioEmOutroProtocolo[0], 4, "0", STR_PAD_LEFT)."/".$jaExisteOficioEmOutroProtocolo[1]."</a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
            $validar = false;
        }
    }

    if ((!empty($processoNumeroAtual)) || (!empty($processoAnoAtual)) || (!empty($comissaoLicitacaoAtual))) {
        if ((empty($processoNumeroAtual)) || (empty($processoAnoAtual)) || (empty($comissaoLicitacaoAtual))) {
            adicionarMensagem("<a href='javascript:document.getElementById(\"inputProcesso\").focus();' class='titulo2'>Os dados de comissão / processo / ano do processo licitatório precisam estar preenchidos. Caso contrário devem ficar em branco</a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
            $validar = false;
        }
    }

    // Verificar processo
    $grupoProcesso = 'null';
    $orgaoProcesso = 'null';

    if ((!empty($processoNumeroAtual)) && (!empty($processoAnoAtual)) && (!empty($comissaoLicitacaoAtual))) {
        $processo = getProcesso($processoNumeroAtual, $processoAnoAtual, $comissaoLicitacaoAtual);

        if (!empty($processo)) {
            $jaExisteEmOutroProtocolo =  verificaProcessoEmProtocolos($processoNumeroAtual, $processoAnoAtual, $comissaoLicitacaoAtual, $protocoloAtual);

            if (!$jaExisteEmOutroProtocolo) {
                $grupoProcesso = $processo[1];
                $orgaoProcesso = $processo[0];
            } else {
                if ($_SESSION['_cperficodi_'] != 2) {
                    adicionarMensagem("<a href='javascript:document.getElementById(\"inputProcesso\").focus();' class='titulo2'>O processo/ano já está cadastrado no protocolo ".$jaExisteEmOutroProtocolo[0]."/".$jaExisteEmOutroProtocolo[1]."</a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
                    $validar = false;
                }
            }
        } else {
            adicionarMensagem("<a href='javascript:document.getElementById(\"inputProcesso\").focus();' class='titulo2'>O processo para esta comissão é inexistente</a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
            $validar = false;
        }
    }

    $txtSccCadastrada = '';

    if ($solicitacaoAtual) {
        $sccJaCadastrada = verificaSccJaCadastrada($solicitacaoAtual);

        if ($sccJaCadastrada) {
            $txtSccCadastrada = 'A SCC cadastrada já está associada a outro protocolo';
        }
    }

    //Verifica se a acao foi preenchida
    if ($acao == '0_0_0_0_0') {
        adicionarMensagem("<a href='javascript:document.getElementById(\"acao\").focus();' class='titulo2'>Próxima Ação</a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
        $validar = false;
    }

    $arrAcao = explode("_",$acao);

    if ($arrAcao[2] == 'S') {
        if ($comissaoAtual == '0') {
            adicionarMensagem("<a href='javascript:document.getElementById(\"comissaoAtual\").focus();' class='titulo2'>Comissão de Licitação da Tramitação</a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
            $validar = false;
        }
    }

    //Verifica se o agente de Destino foi selecionado
    if ($agenteDestino == '0_0' ) {
        adicionarMensagem("<a href='javascript:document.getElementById(\"agenteDestino\").focus();' class='titulo2'>Agente de Tramitação Destino</a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
        $validar = false;
    }

    $arrAgDestVer = explode("_",$agenteDestino);

    //Verifica se o agente de Destino foi selecionado e se o responsável precisa ser preenchido
    if ($arrAgDestVer[1] == 'I' && $usuarioResponsavel <= 0 && $arrAcao[4] != 'S') { 
        adicionarMensagem("<a href='javascript:document.getElementById(\'usuarioResponsavel\').focus();' class='titulo2'>Usuário Responsável da Tramitação</a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
        $validar = false;
    }

    if ($dataEntradaTramitacao == '') {
        adicionarMensagem("<a href='javascript:document.getElementById(\"inputEntradaTramitacao\").focus();' class='titulo2'>Data de Entrada Tramitação</a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
        $validar = false;
    } else {
        $DataEntradaTramCheck = explode("/",$dataEntradaTramitacao);

        if (!checkdate($DataEntradaTramCheck[1],$DataEntradaTramCheck[0],$DataEntradaTramCheck[2])) {
            adicionarMensagem("<a href='javascript:document.getElementById(\"inputEntradaTramitacao\").focus();' class='titulo2'>Data de Entrada Tramitação Inválida</a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
            $validar = false;
        }

        $dateTimeIniTram = strtotime($DataEntradaTramCheck[2].'-'.$DataEntradaTramCheck[1].'-'.$DataEntradaTramCheck[0]);
        $hoje= strtotime(date('Y/m/d'));

        if ($dateTimeIniTram > $hoje) {
            adicionarMensagem("<a href='javascript:document.getElementById(\"inputEntradaTramitacao\").focus();' class='titulo2'>Data de Entrada Tramitação deve ser menor ou igual a de hoje</a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
            $validar = false;
        }
    }

    $ValidaHora = ValidaHora($horaTramitacao);

    if ($ValidaHora != "") {
		if ($Mens == 1) {
            $Mensagem .= ", ";
        }
        
        $Mens      = 1;
		$Tipo      = 2;
		$Mensagem .= "<a href=\"javascript:document.Licitacao.HoraTramitacao.focus();\" class=\"titulo2\">Hora Válida</a>";
	} else {
		$HhMm = explode(":",$horaTramitacao);
		$Hh   = substr($HhMm[0] + 100,1);
		$Mm   = substr($HhMm[1] + 100,1);
		$horaTramitacao = $Hh .":". $Mm;
	}

    if ($validar) {
        $numeroProtocoloAtual    = !empty($numeroProtocoloAtual) ? $numeroProtocoloAtual : 'null';
        $anoProtocoloAtual       = !empty($anoProtocoloAtual) ? $anoProtocoloAtual : 'null';
        $numeroCIAtual           = !empty($numeroCIAtual) ?  strtoupper2($numeroCIAtual) : '';
        $numeroOficioAtual       = !empty($numeroOficioAtual) ?  strtoupper2($numeroOficioAtual)  : '';
        $comissaoLicitacaoAtual   = !empty($comissaoLicitacaoAtual) ? $comissaoLicitacaoAtual : 'null';
        $processoNumeroAtual     = !empty($processoNumeroAtual) ? $processoNumeroAtual : 'null';
        $processoAnoAtual        = !empty($processoAnoAtual) ? $processoAnoAtual : 'null';
        $solicitacaoAtual        = !empty($solicitacaoAtual) ? $solicitacaoAtual :'null';
        $valorEstimatoTotalAtual = !empty($valorEstimatoTotalAtual) ? moeda2float($valorEstimatoTotalAtual) : 'null';
        $observacaoAtual         = !empty($observacaoAtual) ? strtoupper2($observacaoAtual) : '';
        $monitoramentoAtual      = !empty($monitoramentoAtual) ? strtoupper2($monitoramentoAtual) : '';
        $objetoAtual             = !empty($objetoAtual) ?  strtoupper2($objetoAtual) :'';

        // Verificar processo
        $grupoProcesso = 'null';
        $orgaoProcesso = 'null';

        if ((!empty($processoNumeroAtual) && $processoNumeroAtual != 'null') && (!empty($processoAnoAtual) && $processoAnoAtual != 'null') && (!empty($comissaoLicitacaoAtual) && $comissaoLicitacaoAtual != 'null')) {
            $processo = getProcesso($processoNumeroAtual, $processoAnoAtual, $comissaoLicitacaoAtual);

            if (!empty($processo)) {
                $grupoProcesso = $processo[1];
                $orgaoProcesso = $processo[0];
            } else {
                $processoNumeroAtual = $processoAnoAtual = $grupoProcesso = $comissaoLicitacaoAtual = $orgaoProcesso = 'null';
            }
        } else {
            $processoNumeroAtual = $processoAnoAtual = $grupoProcesso = $comissaoLicitacaoAtual = $orgaoProcesso = 'null';
        }

        $sql_update_p  = "UPDATE    SFPC.TBTRAMITACAOPROTOCOLO ";
        $sql_update_p .= "SET       CGREMPCOD1 = " . $grupoAtual;
        $sql_update_p .= " ,        CORGLICOD1 = " . $orgaoAtual;
        $sql_update_p .= " ,        XPROTCOBJE = '" . strtoupper2($objetoAtual) . "'";
        $sql_update_p .= " ,        EPROTCNUCI = '" .  strtoupper2($numeroCIAtual) . "'";
        $sql_update_p .= " ,        EPROTCNUOF = '" .  strtoupper2($numeroOficioAtual) . "'";
        $sql_update_p .= " ,        CSOLCOSEQU = " . $solicitacaoAtual;
        $sql_update_p .= " ,        CLICPOPROC = " . $processoNumeroAtual;
        $sql_update_p .= " ,        ALICPOANOP = " . $processoAnoAtual;
        $sql_update_p .= " ,        CGREMPCODI = " . $grupoProcesso;
        $sql_update_p .= " ,        CCOMLICODI = " . $comissaoLicitacaoAtual;
        $sql_update_p .= " ,        CORGLICODI = " . $orgaoProcesso;
        $sql_update_p .= " ,        TPROTCENTR = '" . DataInvertida($dataEntradaAtual) ." ".date('H:i:s')."' ";
        $sql_update_p .= " ,        VPROTCVALE = " . $valorEstimatoTotalAtual;
        $sql_update_p .= " ,        XPROTCOBSE = '" . $observacaoAtual. "'";
        $sql_update_p .= " ,        XPROTCMONI = '" . $monitoramentoAtual. "'";
        $sql_update_p .= " ,        CUSUPOCOD1 = " . $_SESSION['_cusupocodi_'];
        $sql_update_p .= " WHERE    CPROTCSEQU = " . $protocoloAtual;

        executarTransacao($db, $sql_update_p);

        //Deleta  
        $sqlDeletar = $_SESSION['comandaDeletar'];
        
        if($sqlDeletar != null || $sqlDeletar != ''){
            executarTransacao($db, $sqlDeletar);
            finalizarTransacao($db);   
            $_SESSION['comandaDeletar'] = '';
        }
        
        if (!empty($_SESSION['Arquivos_Upload'])) {
            // Quantidade anexo
            $sql = ' SELECT count(*) FROM SFPC.TBTRAMITACAOPROTOCOLOANEXO WHERE  cprotcsequ ='.$protocoloAtual.' ';
            $checaSeq = checaProtocolo($protocoloAtual);
            $doc_seq = resultValorUnico(executarTransacao($db, $sql)) + 1;
            
            if($doc_seq >= 1){
                $verMaior = 0;
                for($j = 0; $j <= count($checaSeq['seqAnexo']); $j++){
                    if($doc_seq <= $checaSeq['seqAnexo'][$j]){
                        $doc_seq = $checaSeq['seqAnexo'][$j] + 1;
                    }
                    if($doc_seq > $verMaior){
                        $verMaior = $doc_seq;
                    }
                }
                $doc_seq = $verMaior;
             }

            $dirdestino = $GLOBALS['CAMINHO_UPLOADS'] . 'licitacoes/';

            for ($i = 0; $i < count($_SESSION['Arquivos_Upload']['conteudo']); ++$i) {
                if ($_SESSION['Arquivos_Upload']['situacao'][$i] == 'novo') {
                    $NomeDocto = 'DOC_' . $protocoloAtual . '_' . $doc_seq . '_' . $_SESSION['Arquivos_Upload']['nome'][$i];
                    $arquivo =  bin2hex($_SESSION['Arquivos_Upload']['conteudo'][$i]); 
                    //SQL de inserção do arquivo na tabela TBTRAMITACAOPROTOCOLOANEXO
                     $sql = " INSERT INTO SFPC.TBTRAMITACAOPROTOCOLOANEXO(
                        CPROTCSEQU, CPANEXSEQU, EPANEXNOME, IPANEXARQU, TPANEXCADA, CUSUPOCODI, TPANEXULAT
                    ) VALUES(
                        $protocoloAtual, $doc_seq, '" . $NomeDocto . "', decode('".$arquivo."','hex'), NOW(),  " . $_SESSION['_cusupocodi_'] . ",  NOW()
                    )";
                    executarTransacao($db, $sql); 
                    finalizarTransacao($db);
                    
                    $doc_seq++; 
                }
            }

            finalizarTransacao($db);
        }

        $sequencial = $protocoloAtual;
        
        // UPDATE DA ULTIMA TRAMITACAO (PASSO)
        $arrAcao       = explode('_', $acao);
        $arrAgenteDest = explode('_', $agenteDestino);

        // verifica se a ação exige usuário ou pode ser enviada para todos do grupo
        if ($arrAcao[4] != 'S' ) {
            //pega o primeiro usuario cadastrado no agente inicial
            if ($arrAgDestVer[1] != 'I') {
                $usuarioResponsavel = 0;
            } else {
                if ($usuarioResponsavel <= 0) {
                    $responsaveisAgenteInicial = getResponsaveisAgente($db, $arrAgenteDest[0]);
                    $primeiroDaLista = $responsaveisAgenteInicial[0];
                    
                    if (count($primeiroDaLista) > 0) {
                        $usuarioResponsavel = $primeiroDaLista[1];
                    } else {
                        $usuarioResponsavel = 0;
                    }
                }
            }
        } else {
            $usuarioResponsavel = 0;
        }

        if ($arrAgDestVer[1] != 'I') {
            $sqlAgente = "SELECT CTAGENSEQU FROM SFPC.TBTRAMITACAOAGENTE WHERE CGREMPCODI = " . $grupoAtual . " AND FTAGENALTE = 'S' ";

            $agenteDestinoTram = resultValorUnico(executarTransacao($db, $sqlAgente));
        } else {
            $agenteDestinoTram = $arrAgenteDest[0];
        }
        
        if ($arrAcao[2] == 'S' ) {
            $txtComissao = ' ccomlicodi = '.$comissaoAtual.', ';
        } else {
            $txtComissao = ' ';
        }

        $dataEntradaTramitacao =  DataInvertida($dataEntradaTramitacao);
        $dataHoraTramitacao    = "$dataEntradaTramitacao $horaTramitacao:00";

        // Verifica se a ação é para enviar para comissão e se é uma ação final
        // Se for uma ação final, a data de saída receberá o mesmo valor da de entrada, caso contrário será null
        // Se não for para enviar para comissão, será atribuído null a $comissaoAtual
        $sql = "SELECT FTACAOFINA, FTACAOCOMI FROM SFPC.TBTRAMITACAOACAO WHERE CTACAOSEQU = " . $arrAcao[0];

        $res = $db->query($sql);

        $res = $res->fetchRow();

        $sql_update_tram  = "UPDATE SFPC.TBTRAMITACAOLICITACAO ";
        $sql_update_tram .= "SET    CTACAOSEQU = " . $arrAcao[0];
        $sql_update_tram .= " ,     CTAGENSEQU = " . $agenteDestinoTram;
        $sql_update_tram .= " ,     CUSUPOCODI = " . $usuarioResponsavel;
        $sql_update_tram .= " ,     TTRAMLENTR = '" . $dataHoraTramitacao . "'";

            if ($res[0] == 'S') {
                $sql_update_tram .= " ,     TTRAMLSAID = '" . $dataHoraTramitacao . "'";
            } else {
                $sql_update_tram .= " ,     TTRAMLSAID = NULL ";
            }

        $sql_update_tram .= " ,     ATRAMLPRAZ = " . $arrAcao[1];

            if ($res[1] == 'S') {
                $sql_update_tram .= " ,     CCOMLICODI = '" . $comissaoAtual . "'";
            } else {
                $sql_update_tram .= " ,     CCOMLICODI = NULL ";
            }

        $sql_update_tram .= " ,     XTRAMLOBSE = '" . $observacao . "'"; 
        $sql_update_tram .= " ,     FTRAMLSITU = 'A' ";
        $sql_update_tram .= " ,     CUSUPOCOD1 = " . $_SESSION['_cusupocodi_'];
        $sql_update_tram .= " ,     TTRAMLULAT = NOW() ";
        $sql_update_tram .= " WHERE  CPROTCSEQU = " . $sequencial;
        $sql_update_tram .= "       AND CTRAMLSEQU = " . $sequencialTramitacao;

        executarTransacao($db, $sql_update_tram);

        // alteração dos anexos do Último passo
        $sequencialProtocolo = $sequencial;

        // inserir documentos
        if (!empty($_SESSION['Arquivos_Upload_Tramitacao'])) {
            // Quantidade anexo
            $sql = 'SELECT COUNT(*) FROM SFPC.TBTRAMITACAOLICITACAOANEXO WHERE 1 = 1 ';

            $doc_seq = resultValorUnico(executarTransacao($db, $sql)) + 1;

            $dirdestino = $GLOBALS['CAMINHO_UPLOADS'] . 'licitacoes/';

            for ($i = 0; $i < count($_SESSION['Arquivos_Upload_Tramitacao']['conteudo']); ++$i) {
                $NomeDocto = 'DOC_' . $sequencialProtocolo . '_' . $doc_seq . '_' . $_SESSION['Arquivos_Upload_Tramitacao']['nome'][$i];

                if ($_SESSION['Arquivos_Upload_Tramitacao']['situacao'][$i] == 'novo') {
                    $arquivo =  bin2hex($_SESSION['Arquivos_Upload_Tramitacao']['conteudo'][$i]);

                    $sql  = "INSERT INTO SFPC.TBTRAMITACAOLICITACAOANEXO ( ";
                    $sql .= "CTRAMASEQU, CPROTCSEQU, CTRAMLSEQU, ETRAMANOME, ITRAMAARQU, TTRAMACADA, CUSUPOCODI, TTRAMAULAT ";
                    $sql .= " ) VALUES ( ";
                    $sql .= " $doc_seq, $sequencialProtocolo, $sequencialTramitacao, '" . $NomeDocto . "', decode('".$arquivo."','hex'), NOW(),  " . $_SESSION['_cusupocodi_'] . ",  NOW() )";

                    executarTransacao($db, $sql);

                    $doc_seq++;
                }
            }

            finalizarTransacao($db);
        }

        finalizarTransacao($db);

        // Remover dados da sessão
        unset($_SESSION['Arquivos_Upload']);
        unset($_SESSION['Arquivos_Upload_Tramitacao']);
        unset($_SESSION['sccTramitacao']);

        if($txtSccCadastrada=='A SCC cadastrada já está associada a outro protocolo'){
        //$Mensagem  = $txtSccCadastrada;//aparece apenas se tiver scc associada a outro protocolo.
        adicionarMensagem("<a href='javascript:document.getElementById(\"tdScc\").focus();' class='titulo2'>A SCC cadastrada já está associada a outro protocolo</a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
        }else{
            $Mensagem = "Tramitação alterada com sucesso";
            header('Location: CadTramitacaoProtocoloPesquisarEspecial.php?Mens=1&Tipo=1&Mensagem=' . $Mensagem);
        }
    } else {
        if ($valorEstimatoTotalAtual != 'null') {
            //passagem de parametro para não dar erro
            $valorEstimatoTotalAtual = str_replace(',','.',str_replace('.','',$valorEstimatoTotalAtual));
        } else {
            $valorEstimatoTotalAtual = '';
        }
    }
} elseif ($botao == 'Incluir_Documento') {
    if ($_FILES['Documentacao']['tmp_name']) {
        $_FILES['Documentacao']['name'] = tratarNomeArquivo($_FILES['Documentacao']['name']);

        $extensoesArquivo .= ', .zip, .xlsm, .xls, .ods, .pdf, .doc, .odt';

        $extensoes = explode(',', strtolower2($extensoesArquivo));
        array_push($extensoes, '.zip', '.xlsm');

        $noExtensoes = count($extensoes);
        $isExtensaoValida = false;

        for ($itr = 0; $itr < $noExtensoes; ++ $itr) {
            if (preg_match('/\\' . trim($extensoes[$itr]) . '$/', strtolower2($_FILES['Documentacao']['name']))) {
                $isExtensaoValida = true;
            }
        }

        if (! $isExtensaoValida) {
            $Mens      = 1;
            $Tipo      = 2;
            $Mensagem .= 'Selecione somente documento com a(s) extensão(ões) ' . $extensoesArquivo;
        }

        if (strlen($_FILES['Documentacao']['name']) > $tamanhoNomeArquivo) {
            if ($Mens == 1) {
                $Mensagem .= ', ';
            }

            $Mens      = 1;
            $Tipo      = 2;
            $Mensagem .= 'Nome do Arquivo com até ' . $tamanhoNomeArquivo . ' Caracateres ( atualmente com ' . strlen($_FILES['Documentacao']['name']) . ' )';
        }

        $Tamanho = $tamanhoArquivo * 1024;

        if (($_FILES['Documentacao']['size'] > $Tamanho) || ($_FILES['Documentacao']['size'] == 0)) {
            if ($Mens == 1) {
                $Mensagem .= ', ';
            }

            $Kbytes    = $tamanhoArquivo;
            $Kbytes    = (int) $Kbytes;
            $Mens      = 1;
            $Tipo      = 2;
            $Mensagem .= "Este arquivo ou é muito grande ou está vazio. Tamanho Máximo: $Kbytes Kb";
        }

        if ($Mens == '') {
            if (! ($_SESSION['Arquivos_Upload']['conteudo'][] = file_get_contents($_FILES['Documentacao']['tmp_name']))) {
                $Mens     = 1;
                $Tipo     = 2;
                $Mensagem = 'Caminho da Documentação Inválido';
            } else {
                $_SESSION['Arquivos_Upload']['nome'][] = $_FILES['Documentacao']['name'];
                $_SESSION['Arquivos_Upload']['situacao'][] = 'novo'; // situacao pode ser: novo, existente, cancelado e excluido
                $_SESSION['Arquivos_Upload']['codigo'][] = ''; // como é um arquivo novo, ainda nao possui código
            }
        }
    } else {
        $Mens     = 1;
        $Tipo     = 2;
        $Mensagem = 'Documentação Inválida';
    }

    //passagem de parametro para não dar erro
    $valorEstimatoTotalAtual = str_replace(',','.',str_replace('.','',$valorEstimatoTotalAtual));
} elseif ($botao == 'Retirar_Documento') {
    foreach ($DDocumento as $valor) {
        //|MADSON| substituido pelo codigo novo com montagem de sql para exclusão do banco CR #224909

        // array_splice($_SESSION['Arquivos_Upload']['conteudo'],$valor,1);
        // array_splice($_SESSION['Arquivos_Upload']['nome'],$valor,1);
        // array_splice($_SESSION['Arquivos_Upload']['situacao'],$valor,1);

        // //passagem de parametro para não dar erro
        // $valorEstimatoTotalAtual = str_replace(',','.',str_replace('.','',$valorEstimatoTotalAtual));
       
        
        $countDel = 1;
        foreach ($DDocumento as $valor) {
        
            if(!empty($_SESSION['Arquivos_Upload']['codigo'][$valor])){
                $sqlMonta .= $_SESSION['Arquivos_Upload']['codigo'][$valor];
                if($countDel != count($DDocumento)){
                    $sqlMonta .= ',';
                }
           }
            array_splice($_SESSION['Arquivos_Upload']['conteudo'],$valor,1);
            array_splice($_SESSION['Arquivos_Upload']['nome'],$valor,1);
            array_splice($_SESSION['Arquivos_Upload']['situacao'],$valor,1);
            //passagem de parametro para não dar erro
            $valorEstimatoTotalAtual = str_replace(',','.',str_replace('.','',$valorEstimatoTotalAtual));
        $countDel++;
        } 
        if(!empty($sqlMonta) || !is_null($sqlMonta)){
            $sqlDel =  'Delete FROM SFPC.TBTRAMITACAOPROTOCOLOANEXO AN where AN.CPROTCSEQU = '.$protocoloAtual.' AND AN.CPANEXSEQU IN ('.$sqlDeletar.$sqlMonta.')';
        }
        $_SESSION['comandaDeletar'] = $sqlDel;
    }
} elseif ($botao == 'Incluir_Documento_Tram') {
    if ($_FILES['Documentacao_Tram']['tmp_name']) {
        $_FILES['Documentacao_Tram']['name'] = tratarNomeArquivo($_FILES['Documentacao_Tram']['name']);

        $extensoesArquivo .= ', .zip, .xlsm, .xls, .ods, .pdf';

        $extensoes = explode(',', strtolower2($extensoesArquivo));
        array_push($extensoes, '.zip', '.xlsm', '.xlsx');

        $noExtensoes = count($extensoes);
        $isExtensaoValida = false;

        for ($itr = 0; $itr < $noExtensoes; ++ $itr) {
            if (preg_match('/\\' . trim($extensoes[$itr]) . '$/', strtolower2($_FILES['Documentacao_Tram']['name']))) {
                $isExtensaoValida = true;
            }
        }

        if (! $isExtensaoValida) {
            $Mens      = 1;
            $Tipo      = 2;
            $Mensagem .= 'Selecione somente documento com a(s) extensão(ões) ' . $extensoesArquivo;
        }

        if (strlen($_FILES['Documentacao_Tram']['name']) > $tamanhoNomeArquivo) {
            if ($Mens == 1) {
                $Mensagem .= ', ';
            }

            $Mens      = 1;
            $Tipo      = 2;
            $Mensagem .= 'Nome do Arquivo com até ' . $tamanhoNomeArquivo . ' Caracateres ( atualmente com ' . strlen($_FILES['Documentacao_Tram']['name']) . ' )';
        }

        $Tamanho = $tamanhoArquivo * 1024;

        if (($_FILES['Documentacao_Tram']['size'] > $Tamanho) || ($_FILES['Documentacao_Tram']['size'] == 0)) {
            if ($Mens == 1) {
                $Mensagem .= ', ';
            }

            $Kbytes    = $tamanhoArquivo;
            $Kbytes    = (int) $Kbytes;
            $Mens      = 1;
            $Tipo      = 2;
            $Mensagem .= "Este arquivo ou é muito grande ou está vazio. Tamanho Máximo: $Kbytes Kb";
        }

        if ($Mens == '') {
            if (! ($_SESSION['Arquivos_Upload_Tramitacao']['conteudo'][] = file_get_contents($_FILES['Documentacao_Tram']['tmp_name']))) {
                $Mens     = 1;
                $Tipo     = 2;
                $Mensagem = 'Caminho da Documentação Inválido';
            } else {
                $_SESSION['Arquivos_Upload_Tramitacao']['nome'][] = $_FILES['Documentacao_Tram']['name'];
                $_SESSION['Arquivos_Upload_Tramitacao']['situacao'][] = 'novo'; // situacao pode ser: novo, existente, cancelado e excluido
                $_SESSION['Arquivos_Upload_Tramitacao']['codigo'][] = ''; // como é um arquivo novo, ainda nao possui código
            }
        }
    } else {
        $Mens     = 1;
        $Tipo     = 2;
        $Mensagem = 'Documentação Inválida';
    }

    //passagem de parametro para não dar erro
    $valorEstimatoTotalAtual = str_replace(',','.',str_replace('.','',$valorEstimatoTotalAtual));
} elseif ($botao == 'Retirar_Documento_Tram') {
    foreach ($DDocumentoTram as $valor) {
        if ($_SESSION['Arquivos_Upload_Tramitacao']['situacao'][$valor] == 'novo') {
            $_SESSION['Arquivos_Upload_Tramitacao']['situacao'][$valor] = 'cancelado'; // cancelado- quando o usuário incluiu um arquivo novo mas desistiu
        } elseif ($_SESSION['Arquivos_Upload_Tramitacao']['situacao'][$valor] == 'existente') {
            $_SESSION['Arquivos_Upload_Tramitacao']['situacao'][$valor] = 'excluido'; // excluído- quando o arquivo já existe e deve ser excluido no sistema
        }
    }

    //passagem de parametro para não dar erro
    $valorEstimatoTotalAtual = str_replace(',','.',str_replace('.','',$valorEstimatoTotalAtual));   
} elseif ($botao == 'Excluir') {
    // Exclusão do protocolo e da tramitacao
    $sql = 'SELECT COUNT(*) FROM SFPC.TBTRAMITACAOLICITACAO WHERE CPROTCSEQU = ' . $protocoloAtual;

    $resultTramExc = resultValorUnico(executarTransacao($db, $sql));

    finalizarTransacao($db);

    // exclui as solicitacoes do protocolo
    $sqlSolicitacao = 'Delete FROM SFPC.tbtramitacaoprotocolosolicitacao WHERE CPROTCSEQU = '.$protocoloAtual;
    executarTransacao($db, $sqlSolicitacao);
    finalizarTransacao($db); 

    // exclui os anexos da Tramitacao
    $sqlDeleteAnexTram = 'Delete FROM SFPC.TBTRAMITACAOLICITACAOANEXO WHERE CPROTCSEQU = '.$protocoloAtual;
    executarTransacao($db, $sqlDeleteAnexTram);
    finalizarTransacao($db); 

    // pode excluir Tramitações associadas
    $sqlDelete = 'Delete FROM SFPC.TBTRAMITACAOLICITACAO WHERE CPROTCSEQU = '.$protocoloAtual;
    executarTransacao($db, $sqlDelete);
    finalizarTransacao($db);

    // exclui anexos do protocolo
    $sqlDeleteAnexProt = 'Delete FROM SFPC.TBTRAMITACAOPROTOCOLOANEXO WHERE CPROTCSEQU = '.$protocoloAtual;
    executarTransacao($db, $sqlDeleteAnexProt);
    finalizarTransacao($db); 

    // exclui o protocolo de fato
    $sqlDelProtocolo = 'Delete FROM SFPC.TBTRAMITACAOPROTOCOLO WHERE CPROTCSEQU = '.$protocoloAtual;
    executarTransacao($db, $sqlDelProtocolo);
    finalizarTransacao($db);  

    $url  = '?tramitacaoGrupo='.$grupoRetorno;
    $url .= '&tramitacaoOrgao='.$orgaoRetorno;
    $url .= '&tramitacaoNumeroProtocolo='.$numeroProtocoloRetorno;
    $url .= '&tramitacaoAnoProtocolo='.$anoProtocoloRetorno;
    $url .= '&tramitacaoDataEntradaInicio='.$dataEntradaRetornoInicio;
    $url .= '&tramitacaoDataEntradaFim='.$dataEntradaRetornoFim;
    $url .= '&botao=Pesquisar&Exclusao=1';

    header('Location: CadTramitacaoProtocoloPesquisarEspecial.php'.$url);
}elseif ($botao == 1) {
    if ($valorEstimatoTotalAtual != 'null') {
        //passagem de parametro para não dar erro
        $valorEstimatoTotalAtual = str_replace(',','.',str_replace('.','',$valorEstimatoTotalAtual));
    } else { 
        $valorEstimatoTotalAtual = '';
    }
} elseif ($botao == 'excluirScc') {
    $_SESSION['sccTramitacao'] = '';
    $_SESSION['sccProcessoLic'] = '';

    if ($valorEstimatoTotalAtual != 'null') {
        //passagem de parametro para não dar erro
        $valorEstimatoTotalAtual = str_replace(',','.',str_replace('.','',$valorEstimatoTotalAtual));
    } else {
        $valorEstimatoTotalAtual = '';
    }
} elseif ($botao == 'Voltar') {
    $url  = '?tramitacaoGrupo='.$grupoRetorno;
    $url .= '&tramitacaoOrgao='.$orgaoRetorno;
    $url .= '&tramitacaoNumeroProtocolo='.$numeroProtocoloRetorno;
    $url .= '&tramitacaoAnoProtocolo='.$anoProtocoloRetorno;
    $url .= '&tramitacaoDataEntradaInicio='.$dataEntradaRetornoInicio;
    $url .= '&tramitacaoDataEntradaFim='.$dataEntradaRetornoFim;
    $url .= '&botao=Pesquisar';

    header('Location: CadTramitacaoProtocoloPesquisarEspecial.php'.$url);
}

# Critica dos Campos #
if ($Critica == 1) {
    $Mens     = 0;
    $Mensagem = "Informe: ";
    
    if ($LicitacaoProcessoAnoComissao == "") {
        $Mens      = 1;
        $Tipo      = 2;
        $Mensagem .= "<a href=\"javascript: document.Licitacao.LicitacaoCodigo.focus();\" class=\"titulo2\">Selecione um Processo (Processo/Ano)</a>";
    } else {
        $NProcessoAnoComissao = explode("_",$LicitacaoProcessoAnoComissao);
        $Processo       = substr($NProcessoAnoComissao[0] + 10000,1);
        $ProcessoAno    = $NProcessoAnoComissao[1];
        $ComissaoCodigo = $NProcessoAnoComissao[2];
        $novaTela 		= $NProcessoAnoComissao[3];

        if ($novaTela=="1") {
            $Url = "CadLicitacaoAlterarNovo.php?Processo=$Processo&ProcessoAno=$ProcessoAno&ComissaoCodigo=$ComissaoCodigo";
        } else {
            $Url = "CadLicitacaoAlterar.php?Processo=$Processo&ProcessoAno=$ProcessoAno&ComissaoCodigo=$ComissaoCodigo";
        }

        if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
        header("location: ".$Url);
        exit;
    }
}

$codComissao = $comissaoLicitacaoAtual;
$numSccFormatado = '';

if (!empty($_SESSION['sccTramitacao'])) {
    $sccTramitacao = explode("-", $_SESSION['sccTramitacao']);
    $solicitacaoAtual = $sccTramitacao[0];
    $numSccFormatado  = $sccTramitacao[1];
    $objetoScc        = $sccTramitacao[2];

}

// Verifica se existe processo ligado à SCC
if ((!empty($solicitacaoAtual))) {
    $dadosProcesso = getProcessoScc($solicitacaoAtual);
    $processoNumeroAtual    = $dadosProcesso[0][0];
    $processoAnoAtual       = $dadosProcesso[0][1];
    $comissaoLicitacaoAtual = $dadosProcesso[0][2];
    $txtComissao            = $dadosProcesso[0][5];
}

if (empty($numSccFormatado) && !empty($solicitacaoAtual) ) {
    $numSccFormatado = getNumeroSolicitacaoCompra($db, $solicitacaoAtual);
}

// Formata o número do processo
if (!empty($processoNumeroAtual)) {
    $processoNumeroAtual = str_pad($processoNumeroAtual, 4, "0", STR_PAD_LEFT);
}
?>
<html>
<?php
# Carrega o layout padrão #
layout();
?>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
    <script language="JavaScript" src="../menu.js"></script>
    <script language="javascript" src="../import/jquery/jquery-1.7.2.min.js" type="text/javascript"></script>
    <script language="javascript" src="../import/jquery/jquery.maskmoney.js" type="text/javascript"></script>
    <script language="javascript" src="../import/jquery/jquery.maskedinput.js" type="text/javascript"></script>
    <script language="javascript" type="">
        <?php MenuAcesso(); ?>

        var arrUsuariosAgente = <?php echo json_encode($arrUsuariosAgente)?>;

        function enviar(valor) {
            if (valor == 'Excluir') {
                pergunta = "Este protocolo pode possuir tramitações e documentos associados. Deseja excluir este protocolo e tudo associado?";

                var r = confirm(pergunta);
                
                if (r == true) {
                    document.CadTramitacaoProtocoloManter.Botao.value=valor;
                    document.CadTramitacaoProtocoloManter.submit();
                }
            } else {
                document.CadTramitacaoProtocoloManter.Botao.value=valor;
                document.CadTramitacaoProtocoloManter.submit();
            }
        }

        function CaracteresObjeto(text,campo) {
            input = document.getElementById(campo);
            input.value = text.value.length;
        }

        function AbreJanela(url,largura,altura) {
            window.open(url,'paginadetalhe','status=no,scrollbars=yes,left=40,top=120,width='+largura+',height='+altura);
        }

        function escolherComissao() {
            $('#inputComissao').val($('#comissSelect').val()); 
        }

        function removerScc() {
            $('#inputScc').val('');
            $('#inputProcesso').val('');
            $('#inputProcessoAno').val('');
            $('#inputComissao').val('');
            $('#inputProcesso').attr('readonly','');
            $('#inputProcessoAno').attr('readonly','');
            $('#inputProcesso').prop('readonly','');
            $('#inputProcessoAno').prop('readonly','');
            $('#tdScc').html("<input id=\"inputScc\" type=\"hidden\" name=\"seqSolicitacao\" value=\"\"><a href=\"javascript:AbreJanela('./JanelaLicitacaoIncluir.php?ProgramaOrigem=CadTramitacaoProtocoloManter&amp;TipoUsuario=C',1200,540);\" id=\"CentroCustoLink\"><img src=\"../midia/lupa.gif\" border=\"0\"> </a>");
        
            document.CadTramitacaoProtocoloManter.Botao.value='excluirScc';
            document.CadTramitacaoProtocoloManter.submit();
        }

        function exibeUsuarioResponsavel(numAgenteDestino) {
            var arrAgenteDestino = numAgenteDestino.explode("_"),
            tipoAgenteDestino = arrAgenteDestino[1],
            dadosAcao = $('#acao').val(),
            arrAcao = dadosAcao.explode("_"),
            apresentaUsuario = arrAcao[4],
            apresentaComissao = arrAcao[2];

            preencheUsuarioAgente(arrAgenteDestino[0]);

            if (tipoAgenteDestino != 'I') {
                $('.usuarioResp').hide();
            } else {
                if (apresentaUsuario =='S') {
                    $('.usuarioResp').hide();
                } else {
                    $('.usuarioResp').show();
                }
            }
        }

        function preencheUsuarioAgente(codAgente) {
            var usuarioAtual = <?php echo $usuarioResponsavel ?>;
            var htmlUsuarios = '';
        
            htmlUsuarios ='<option value="0">Selecione um usuário...</option>';
            i=0;
            $.each(arrUsuariosAgente, function (key, value) {
                arrUsuarios = arrUsuariosAgente[i];

                if (codAgente == arrUsuarios[2]) {
                    if (usuarioAtual == arrUsuarios[0]) {
                        htmlUsuarios +='<option selected="selected" value="'+arrUsuarios[0]+'">'+arrUsuarios[1]+'</option>';
                    } else {
                        htmlUsuarios +='<option value="'+arrUsuarios[0]+'">'+arrUsuarios[1]+'</option>';
                    }
                }
                
                i++;
            });

            $('#usuarioResponsavel').html(htmlUsuarios);
        }

        function preenchePrazoDaAcao(numAcao) {
            var arrAcao = numAcao.explode("_"),
            prazoAcao = arrAcao[1],
            apresentaComissao = arrAcao[2],
            escondeUsuario= arrAcao[4],
            dadosAgente = $('#agenteDestino').val(),
            arrAgente = dadosAgente.explode("_"),
            tipoAgenteDestino = arrAgente[1];

            document.CadTramitacaoProtocoloManter.prazoAcao.value = prazoAcao;

            if (apresentaComissao=='S') {
                apresentarComissao();
                $('.usuarioResp').hide();
            } else {
                esconderComissao();

                if (tipoAgenteDestino != 'I') {
                    $('.usuarioResp').hide();
                } else {
                    if (escondeUsuario =='S') {
                        $('.usuarioResp').hide();
                    } else {
                        $('.usuarioResp').show();
                    }
                }
            }
        }

        function apresentarComissao() {
            $('.comissaoLic').show();
        }

        function esconderComissao() {
            $('.comissaoLic').hide();
        }

        $(document).ready(function() {
            <?php
            $arrAgenteDestinoVer = explode("_",$agenteDestino);
            $arrAcaoVer = explode("_",$acao);

            if (empty($agenteDestino) || $agenteDestino == '0_0' || $arrAgenteDestinoVer[1] == 'E' || $arrAcaoVer[4] == 'S') {
                ?>
                $('.usuarioResp').hide();
                <?php
            }

            if (empty($acao) || $acao == '0_0_0_0_0' || $arrAcaoVer[2] != 'S') {
                ?>
                $('.comissaoLic').hide();
                <?php
            }
            ?>

            // aquisição apenas para carregar a tela no manter
            preenchePrazoDaAcao( '<?php echo $acao ?>'); 
            exibeUsuarioResponsavel('<?php echo $agenteDestino ?>');   

            <?php
            if (!empty($_SESSION['sccTramitacao'])) {
                ?>
                $('#inputProcesso').attr('readonly','true');
                $('#inputProcessoAno').attr('readonly','true');
                $('#inputProcesso').prop('readonly','true');
                $('#inputProcessoAno').prop('readonly','true');
                <?php
            }
            ?>

            $(".capturarValorAcaoGrupo").change(function() {
                var acao  = $(this).attr('data-acao');
                document.CadTramitacaoProtocoloManter.Botao.value = acao;
                document.CadTramitacaoProtocoloManter.submit();
            });

            jQuery(document).ready(function($) {
                jQuery(".mascara-hora").mask("99:99");

                jQuery(".reset").click(function(event) {
                    event.preventDefault();

                jQuery(":text").each(function() {
                    jQuery(this).val("");
                });

                jQuery(":radio").each(function() {
                    jQuery(this).prop({
                        checked : false
                    })
                });

                jQuery(":checkbox").each(function() {
                    jQuery(this).prop({
                        checked : false
                    })
                });

                jQuery("select").each(function() {
                    jQuery(this).val("");
                });
            });
        });
    });
    </script>
    <script language="JavaScript">Init();</script>
    <form action="CadTramitacaoProtocoloManterEspecial.php?protocolo=<?php echo $protocoloAtual; ?>" method="POST" name="CadTramitacaoProtocoloManter" enctype="multipart/form-data">
        <input type="hidden" name="tramitacaoProtocolo" value="<?php echo $protocoloAtual; ?>">
        <br><br><br><br><br>
            <table cellpadding="3" border="0" summary="">
                <!-- Caminho -->
                <tr>
                    <td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
                    <td align="left" class="textonormal" colspan="2">
                        <font class="titulo2">|</font>
                        <a href="../index.php"><font color="#000000">Página Principal</font></a> > Licitações > Tramitação > Número Protocolo > Manter Especial
                    </td>
                </tr>
                <!-- Fim do Caminho-->
                <!-- Erro -->
                <?php
                if ($Mens == 1) {
                    ?>
                    <tr>
                        <td width="150"></td>
                        <td align="left" colspan="2"><?php ExibeMens($Mensagem,$Tipo,1); ?></td>
                    </tr>
                    <?php
                }
                ?>
                <!-- Fim do Erro -->
                <!-- Corpo -->
                <tr>
                    <td width="150"></td>
                    <td class="textonormal">
                        <table width="100%" border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal"  bgcolor="#FFFFFF">
                            <tr>
                                <td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
                                    MANTER ESPECIAL - NÚMERO PROTOCOLO PARA PROCESSOS LICITATÓRIOS
                                </td>
                            </tr>
                            <tr>
                                <td class="textonormal">
                                    <p align="justify">
                                    Preencha os dados abaixo e clique no botão 'Manter'. Os itens obrigatórios estão com *. <br>Pode-se anexar documentos em pdf.
                                    </p>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <table border="0" summary="" width="100%">
                                        <tr>
                                            <td class="textonormal" bgcolor="#DCEDF7">Grupo </td>
                                            <td class="textonormal"><input type="hidden" name="Critica" value="1"></td>
                                            <td class="textonormal">
                                                <?php
                                                $db = Conexao();

                                                $sql = "SELECT EGREMPDESC FROM SFPC.TBGRUPOEMPRESA WHERE CGREMPCODI = " . $grupoAtual;

                                                $res = $db->query($sql);

                                                $res = $res->fetchRow();

                                                $descGrupo = $res[0];
                                                ?>
                                                <input type="hidden" name="tramitacaoGrupo" value="<?php echo $grupoAtual ?>">
                                                <?php echo $descGrupo; ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="textonormal" bgcolor="#DCEDF7">Número do Protocolo </td>
                                            <td class="textonormal"><input type="hidden" name="Critica" value="1"></td>
                                            <td class="textonormal">
                                                <?php echo !empty($numeroProtocoloAtual) ? $numeroProtocoloAtual : ''; ?>
                                                <input id="inputProtocolo" type="hidden" readonly value="<?php echo !empty($numeroProtocoloAtual) ? $numeroProtocoloAtual : ''; ?>" name="tramitacaoNumeroProtocolo" class="textonormal" />
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="textonormal" bgcolor="#DCEDF7">Ano do Protocolo </td>
                                            <td class="textonormal"><input type="hidden" name="Critica" value="1"></td>
                                            <td class="textonormal">
                                                <?php echo date('Y')?>
                                                <input id="inputAnoProtocolo" type="hidden" readonly value="<?php echo date('Y')?>" name="tramitacaoAno" class="textonormal" />
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="textonormal" bgcolor="#DCEDF7">Órgão Demandante </td>
                                            <td class="textonormal"><input type="hidden" name="Critica" value="1"></td>
                                            <td class="textonormal">
                                                <?php
                                                $db = Conexao();

                                                $sql = "SELECT EORGLIDESC FROM SFPC.TBORGAOLICITANTE WHERE CORGLICODI = " . $orgaoAtual;

                                                $res = $db->query($sql);

                                                $res = $res->fetchRow();

                                                $descOrgao = $res[0];
                                                ?>
                                                <input type="hidden" name="tramitacaoOrgao" value="<?php echo $orgaoAtual ?>">
                                                <?php echo $descOrgao; ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="textonormal" bgcolor="#DCEDF7">Objeto </td>
                                            <td class="textonormal"><input type="hidden" name="Critica" value="1"></td>
                                            <td class="textonormal"><font class="textonormal">máximo de 400 caracteres</font>
                                                <input type="text" id="NCaracteresObjeto" name="NCaracteresObjeto" readonly="" size="3" value="0" class="textonormal"><br>
                                                <textarea id="inputObjeto" name="tramitacaoObjeto"
                                                    cols="50"
                                                    rows="4"
                                                    maxlength = "400" 
                                                    onkeyup="javascript:CaracteresObjeto(this,'NCaracteresObjeto')" onblur="javascript:CaracteresObjeto(this,'NCaracteresObjeto')"
                                                    onselect="javascript:CaracteresObjeto(this,'NCaracteresObjeto')"
                                                    class="textonormal"><?php 

                                                    if (!empty($_SESSION['sccTramitacao'])) { 
                                                        $sccSessao = explode('-', $_SESSION['sccTramitacao']);
                                                        
                                                        if($sccSessao[2]){
                                                            echo $sccSessao[2];
                                                        }else{
                                                            echo (!empty($objetoAtual)) ? $objetoAtual : ''; 
                                                        }
                                                        
                                                       
                                                    }else{
                                                        
                                                        echo (!empty($objetoAtual)) ? $objetoAtual : ''; 
                                                    }
                                                    ?></textarea>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="textonormal" bgcolor="#DCEDF7">Número CI </td>
                                            <td class="textonormal"><input type="hidden" name="Critica" value="1"></td>
                                            <td class="textonormal">
                                                <input type="text" id="numeroCi" maxlength="30" value="<?php echo (!empty($numeroCIAtual)) ? $numeroCIAtual : ''; ?>" name="tramitacaoNumeroCI" class="textonormal" />
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="textonormal" bgcolor="#DCEDF7">Número Ofício </td>
                                            <td class="textonormal"><input type="hidden" name="Critica" value="1"></td>
                                            <td class="textonormal">
                                                <input type="text" id="numeroOf" maxlength="30" value="<?php echo (!empty($numeroOficioAtual)) ? $numeroOficioAtual : ''; ?>" name="tramitacaoNumeroOficio" class="textonormal" />
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="textonormal" bgcolor="#DCEDF7">Número da SCC </td>
                                            <td class="textonormal"><input type="hidden" name="Critica" value="1"></td>
                                            <td class="textonormal" id="tdScc" name="tdScc">
                                                <?php
                                                if (!empty($_SESSION['sccTramitacao'])) { ?>
                                                    <?php
                                                    echo $sccSessao[1];
                                                    ?>
                                                    <input id="inputScc" type="hidden" name="seqSolicitacao" value="<?php echo $sccSessao[0];?>">
                                                    <?php
                                                }
                                                ?>
                                                <a href="javascript:AbreJanela('./JanelaLicitacaoIncluir.php?ProgramaOrigem=CadTramitacaoProtocoloManter&amp;TipoUsuario=C',1200,540);" id="CentroCustoLink">
                                                <img src="../midia/lupa.gif" border="0"></a>&nbsp;
                                                <?php
                                                if ($sccSessao[1]) {
                                                    ?>
                                                    <input type="button" value="Remover" class="botao" name="removerNumScc" id="removerNumScc" onclick="javascript:removerScc();">
                                                    <?php
                                                }
                                                ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="textonormal" bgcolor="#DCEDF7">Comissão de Licitação</td>
                                            <td class="textonormal"><input type="hidden" name="Critica" value="1"></td>
                                            <td class="textonormal">
                                                <?php
                                                if ((empty($_SESSION['sccTramitacao'])) && empty($solicitacaoAtual))  {
                                                    ?>
                                                    <select id="comissSelect" name="comissSelect" class="textonormal" onChange="escolherComissao()">
                                                        <option value="">Selecione a comissão de licitação...</option>
                                                        <?php
                                                        // Busca as comissões de licitação
                                                        if (!empty($grupoAtual)) {
                                                            $comissoes = getComissaoLicitacao($grupoAtual);

                                                            while ($comissao = $comissoes->fetchRow()) {
                                                                $comissaoLicitacaoAtual = $comissao[0];
                                                                $txtComissao            = $comissao[1];
                                                                ?>
                                                                <option value="<?php echo $comissaoLicitacaoAtual; ?>" <?php echo $comissao[0] == $codComissao ? 'selected' : ''; ?>><?php echo $txtComissao; ?></option>
                                                                <?php
                                                            }
                                                        }
                                                        ?>
                                                    </select> 
                                                    <?php
                                                }
                                                ?>
                                                <input type="hidden" name="tramitacaoComissaoLicitacao" id="inputComissao" value="<?php echo $comissaoLicitacaoAtual; ?>">
                                                <?php if (!empty($_SESSION['sccTramitacao']) or !empty($solicitacaoAtual)) { echo $txtComissao; } ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="textonormal" bgcolor="#DCEDF7">Processo Licitatório </td>
                                            <td class="textonormal"><input type="hidden" name="Critica" value="1"></td>
                                            <td class="textonormal">
                                                <input id="inputProcesso" type="text" value="<?php if (!empty($processoNumeroAtual)) { echo $processoNumeroAtual; } else { echo ''; } ?>" size="3" maxlength="4" name="tramitacaoProcessoNumero" class="textonormal" /> /
                                                <input id="inputProcessoAno" type="text" value="<?php if (!empty($processoAnoAtual)) { echo $processoAnoAtual; } else { echo ''; } ?>" size="3" maxlength="4" name="tramitacaoProcessoAno" class="textonormal" />
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="textonormal" bgcolor="#DCEDF7">Data de Entrada </td>
                                            <td class="textonormal"><input type="hidden" name="Critica" value="1"></td>
                                            <td class="textonormal">
                                                <input id="inputEntrada" type="text" maxlength="10" value="<?php echo (!empty($dataEntradaAtual)) ? $dataEntradaAtual : ''; ?>" name="tramitacaoDataEntrada" class="textonormal" />
                                                <a href="javascript:janela('../calendario.php?Formulario=CadTramitacaoProtocoloManter&Campo=tramitacaoDataEntrada','Calendario',220,170,1,0)">
                                                <img src="../midia/calendario.gif" border="0" alt=""></a>&nbsp;<font class="textonormal">dd/mm/aaaa</font>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="textonormal" bgcolor="#DCEDF7">Valor Estimado Total (Informado pelo Área Demandante)</td>
                                            <td class="textonormal"><input type="hidden" name="Critica" value="1"></td>
                                            <td class="textonormal">
                                                <?php
                                                if (!empty($valorEstimatoTotalAtual)) {
                                                    if ($dadosDoBanco = 0) {
                                                        $valorEstimatoTotalAtual = $valorEstimatoTotalAtual;
                                                    } else {
                                                        $valorEstimatoTotalAtual = number_format($valorEstimatoTotalAtual, 2, ',', '.');
                                                    }
                                                } else {
                                                    $valorEstimatoTotalAtual = '';
                                                }
                                                ?>
                                                <input id="inputValor" type="text" maxlength="16" value="<?php echo $valorEstimatoTotalAtual ?>" name="tramitacaoValorEstimadoTotal" class="dinheiro textonormal" />
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="textonormal" bgcolor="#DCEDF7">Observações </td>
                                            <td class="textonormal"><input type="hidden" name="Critica" value="1"></td>
                                            <td class="textonormal"><font class="textonormal">máximo de 400 caracteres</font>
                                                <input type="text" id="NCaracteresObs" name="NCaracteresObs" readonly="" size="3" value="0" class="textonormal"><br>
                                                <textarea name="tramitacaoObservacao"
                                                    cols="50"
                                                    rows="4"
                                                    maxlength = "400" 
                                                    id="inputObservacao"
                                                    onkeyup="javascript:CaracteresObjeto(this,'NCaracteresObs')"
                                                    onblur="javascript:CaracteresObjeto(this,'NCaracteresObs')"
                                                    onselect="javascript:CaracteresObjeto(this,'NCaracteresObs')"
                                                    class="textonormal"><?php echo (!empty($observacaoAtual)) ? $observacaoAtual : ''; ?></textarea>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="textonormal" bgcolor="#DCEDF7">Monitoramento </td>
                                            <td class="textonormal"><input type="hidden" name="Critica" value="1"></td>
                                            <td class="textonormal"><font class="textonormal">máximo de 1000 caracteres</font>
                                                <input type="text" id="NCaracteresMon" name="NCaracteresMon" readonly="" size="3" value="0" class="textonormal"><br>
                                                <textarea name="tramitacaoMonitoramento"
                                                    cols="50"
                                                    rows="4"
                                                    maxlength="1000" 
                                                    id="inputMonitoramento"
                                                    onkeyup="javascript:CaracteresObjeto(this,'NCaracteresMon')"
                                                    onblur="javascript:CaracteresObjeto(this,'NCaracteresMon')"
                                                    onselect="javascript:CaracteresObjeto(this,'NCaracteresMon')"
                                                    class="textonormal"><?php echo (!empty($monitoramentoAtual)) ? $monitoramentoAtual : ''; ?></textarea>
                                            </td>
                                        </tr> 
                                        <tr>
                                            <td align="center" class="textonormal" bgcolor="#75ADE6" colspan="3"><b>Anexação de Documentos</b></td>
                                        </tr>
                                        <tr>
                                            <td class="textonormal" bgcolor="#DCEDF7">Anexação de Documento(s) </td>
                                            <td class="textonormal"><input type="hidden" name="Critica" value="1"></td>
                                            <td class="textonormal">
                                                <input type="file" name="Documentacao" class="textonormal" />
                                                <table width="100%" border="0" cellpadding="0" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal"  bgcolor="#FFFFFF">
                                                    <?php
                                                    $DTotal = count($_SESSION['Arquivos_Upload']['conteudo']);

                                                    for ($Dcont = 0; $Dcont < $DTotal; ++ $Dcont) {
                                                        if ($_SESSION['Arquivos_Upload']['situacao'][$Dcont] == 'novo' || $_SESSION['Arquivos_Upload']['situacao'][$Dcont] == 'existente') {
                                                            echo '<tr>';

                                                            if (! $ocultarCamposEdicao) {
                                                                echo "<td align='center' ><input type='checkbox' name='DDocumento[$Dcont]' value='$Dcont' ></td>\n";
                                                                echo "<td></td>";
                                                            }

                                                            echo "<td class='textonormal' >";

                                                            if (! $ocultarCamposEdicao) {
                                                                echo $_SESSION['Arquivos_Upload']['nome'][$Dcont];
                                                            } else {
                                                                $arquivo = 'compras/' . $_SESSION['Arquivos_Upload']['nome'][$Dcont];
                                                                addArquivoAcesso($arquivo);

                                                                echo "<a href='../carregarArquivo.php?arq=" . urlencode($arquivo) . "'>" . $_SESSION['Arquivos_Upload']['nome'][$Dcont] . '</a>';
                                                            }

                                                            echo '</td></tr>';
                                                        }
                                                    }
                                                    ?>
                                                </table>
                                                <input type="button" name="IncluirDocumento" value="Incluir Documento" class="botao" onclick="javascript:enviar('Incluir_Documento');">
                                                <input type="button" name="RetirarDocumento" value="Retirar Documento" class="botao" onclick="javascript:enviar('Retirar_Documento');">
                                            </td>
                                        </tr>
                                    </table>
                                    <!-- DADOS DA TRAMITACAO INICIAL -->  
                                    <table border="0" summary="" width="100%" >    
                                        <tr>
                                            <td align="center" class="textonormal titulo3" bgcolor="#75ADE6" colspan="3"><b>Tramitação de Documentos - Última Ação</b></td>
                                        </tr>                          
                                        <tr>
                                            <td class="textonormal" bgcolor="#DCEDF7">Próxima Ação*</td>
                                            <td></td>
                                            <td class="textonormal">
                                                <input type="hidden" name="prazoAcao" value="0">
                                                <select name="acao" id="acao" class="textonormal tamanho_campo" onclick='preenchePrazoDaAcao(this.value)' >
                                                    <option value="0_0_0_0_0">Selecione uma Ação...</option>
                                                    <?php echo $htmlAcao ?>
                                                </select>
                                            </td>
                                        </tr>
                                        <tr class = "comissaoLic">
                                            <td class="textonormal" bgcolor="#DCEDF7">Comissão de Licitação*</td>
                                            <td></td>
                                            <td class="textonormal">
                                                <select id="comissaoAtual" name="comissaoAtual" class="textonormal tamanho_campo" class="textonormal">
                                                    <option value="0">Selecione a comissão de licitação...</option>
                                                    <?php
                                                    $grupoAtual = $_SESSION['_cgrempcodi_'];

                                                    if (!empty($grupoAtual)) {
                                                        $comissoes = getComissaoLicitacao($grupoAtual);
                                                
                                                        while ($comissao = $comissoes->fetchRow()) {
                                                            ?>
                                                            <option <?php echo (!empty($comissaoAtual) && $comissaoAtual == $comissao[0]) ? 'selected' : ''?> value="<?php echo $comissao[0]; ?>"><?php echo $comissao[1]; ?></option>
                                                            <?php
                                                        }
                                                    }
                                                    ?>
                                                </select>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="textonormal" bgcolor="#DCEDF7"> Agente de Tramitação Destino*</td>
                                            <td></td>
                                            <td class="textonormal">
                                                <select name="agenteDestino" id="agenteDestino" class="textonormal tamanho_campo"  onclick='exibeUsuarioResponsavel(this.value)'>
                                                    <option value="0_0">Selecione um Agente de Destino...</option>
                                                    <?php echo $htmlAgenteDestino ?>
                                                </select>
                                            </td>
                                        </tr>
                                        <tr class = "usuarioResp">
                                            <td class="textonormal usuarioResp" bgcolor="#DCEDF7"> Usuário Responsável</td>
                                            <td></td>
                                            <td class="textonormal usuarioResp">
                                                <select name="usuarioResponsavel" id="usuarioResponsavel" class="textonormal tamanho_campo">
                                                    <option value="0">Selecione um usuário...</option>
                                                    <?php echo $htmlUsuarioResponsavel ?>
                                                </select>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="textonormal" bgcolor="#DCEDF7">Data de Entrada da Tramitação* </td>
                                            <td class="textonormal"><input type="hidden" name="Critica" value="1"></td>
                                            <td class="textonormal">
                                                <input id="inputEntradaTramitacao" type="text" maxlength="10" value="<?php echo (!empty($dataEntradaTramitacao)) ? $dataEntradaTramitacao : ''; ?>" name="DataEntradaTramitacao" class="textonormal" />
                                                <a href="javascript:janela('../calendario.php?Formulario=CadTramitacaoProtocoloManter&Campo=DataEntradaTramitacao','Calendario',220,170,1,0)">
                                                <img src="../midia/calendario.gif" border="0" alt=""></a>&nbsp;<font class="textonormal">dd/mm/aaaa</font>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="textonormal" bgcolor="#DCEDF7">Hora de entrada da Tramitação*</td>
                                            <td class="textonormal"><input type="hidden" name="Critica" value="1"></td>
                                            <td class="textonormal">
                                                <input id="HoraTramitacao" name="HoraTramitacao" type="text" size="10" maxlength="6" value="<?php echo (!empty($horaTramitacao)) ? $horaTramitacao : ''; ?>" class="textonormal mascara-hora">
                                                hh:mm
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="textonormal" bgcolor="#DCEDF7"> Observação</td>
                                            <td></td>
                                            <td class="textonormal">
                                                <font class="textonormal">máximo de 500 caracteres</font>
                                                <input type="text" name="NCaracteres" disabled="" readonly="" size="3" value="0" class="textonormal"><br>
                                                <textarea id="observacao" name="observacao" maxlength="500" cols="50" rows="4" onkeyup="javascript:CaracteresObservacao(1)" onblur="javascript:CaracteresObservacao(0)" onselect="javascript:CaracteresObservacao(1)" class="textonormal"><?php echo $observacao; ?></textarea>
                                                <script language="javascript" type="">
                                                    function CaracteresObservacao(valor){
                                                        CadTramitacaoProtocoloManter.NCaracteres.value = '' +  CadTramitacaoProtocoloManter.observacao.value.length;
                                                    }
                                                </script>
                                                <!--<input type="text" name="DianDescricao" value="<?php echo $observacao; ?>" size="45" maxlength="400" class="textonormal"> -->
                                            </td>
                                        </tr>
                                        <tr>
                                            <td align="center" class="textonormal titulo3" bgcolor="#75ADE6" colspan="3"><b>Anexação de Documentos - Última Ação</b></td>
                                        </tr>
                                        <tr>
                                            <?php $DTotal = count($_SESSION['Arquivos_Upload_Tramitacao']['conteudo']);?>
                                            <td class="textonormal" bgcolor="#DCEDF7" rowspan="<?php echo $DTotal?>">Anexação de Documento(s) </td>
                                            <td></td>
                                            <td class="textonormal" >
                                                <input type="file" name="Documentacao_Tram" class="textonormal" />
                                                <table width="100%" border="0" cellpadding="0" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal"  bgcolor="#FFFFFF">
                                                    <?php
                                                    for ($Dcont = 0; $Dcont < $DTotal; ++ $Dcont) {
                                                        if ($_SESSION['Arquivos_Upload_Tramitacao']['situacao'][$Dcont] == 'novo' || $_SESSION['Arquivos_Upload_Tramitacao']['situacao'][$Dcont] == 'existente') {
                                                            echo '<tr>';

                                                            if (! $ocultarCamposEdicao) {
                                                                echo "<td align='center' width='10%'><input type='checkbox' name='DDocumentoTram[$Dcont]' value='$Dcont' ></td>\n";
                                                            }

                                                            echo "<td class='textonormal' >";

                                                            if (! $ocultarCamposEdicao) {
                                                                echo $_SESSION['Arquivos_Upload_Tramitacao']['nome'][$Dcont];
                                                            } else {
                                                                $arquivo = 'compras/' . $_SESSION['Arquivos_Upload_Tramitacao']['nome'][$Dcont];
                                                                addArquivoAcesso($arquivo);

                                                                echo "<a href='../carregarArquivo.php?arq=" . urlencode($arquivo) . "'>" . $_SESSION['Arquivos_Upload_Tramitacao']['nome'][$Dcont] . '</a>';
                                                            }

                                                            echo '</td></tr>';
                                                        }
                                                    }
                                                    ?>
                                                </table>
                                                <input type="button" name="IncluirDocumentoTram" value="Incluir Documento" class="botao" onclick="javascript:enviar('Incluir_Documento_Tram');">
                                                <input type="button" name="RetirarDocumentoTram" value="Retirar Documento" class="botao" onclick="javascript:enviar('Retirar_Documento_Tram');">
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                            <tr>
                                <td class="textonormal" align="right">
                                    <input type="submit" value="Alterar" onclick="javascript:enviar('Alterar');" class="botao">
                                    <input type="submit" value="Excluir" onclick="javascript:enviar('Excluir');" class="botao">
                                    <input type="submit" value="Voltar" onclick="javascript:enviar('Voltar');" class="botao">
                                    <input type="hidden" name="tramitacaoGrupoRetorno" value="<?php echo $grupoRetorno?>" />
                                    <input type="hidden" name="tramitacaoOrgaoRetorno" value="<?php echo $orgaoRetorno?>" />
                                    <input type="hidden" name="tramitacaoNumeroProtocoloRetorno" value="<?php echo $numeroProtocoloRetorno?>" />
                                    <input type="hidden" name="tramitacaoAnoProtocoloRetorno" value="<?php echo $anoProtocoloRetorno?>" />
                                    <input type="hidden" name="tramitacaoDataEntradaInicioRetorno" value="<?php echo $dataEntradaRetornoInicio?>" />
                                    <input type="hidden" name="tramitacaoDataEntradaFimRetorno" value="<?php echo $dataEntradaRetornoFim?>" />
                                    <input type="hidden" name="seqTramitacao" value="<?php echo $sequencialTramitacao?>" />
                                    <input type="hidden" name="Botao" value="" />
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <!-- Fim do Corpo -->
            </table>
        </form>
    </body>
</html>
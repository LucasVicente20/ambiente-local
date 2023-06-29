<?php
/**
 * Portal de Compras
 * 
 * Programa: CadTramitacaoProtocoloIncluirEspecial.php
 * ----------------------------------------------------------------------------------
 * Alterado: Pitang Agile TI - Caio Coutinho
 * Data:     08/04/2019
 * Objetivo: Tarefa Redmine 213474
 * ----------------------------------------------------------------------------------
 * Alterado: Lucas Baracho
 * Data:     29/05/2019
 * Objetivo: Tarefa Redmine 217242
 * ----------------------------------------------------------------------------------
 * Alterado: Lucas Baracho
 * Data:     02/07/2019
 * Objetivo: Tarefa Redmine 219492
 * ----------------------------------------------------------------------------------
 * Alterado: Lucas Baracho
 * Data:     12/07/2019
 * Objetivo: Tarefa Redmine 220301
 * ----------------------------------------------------------------------------------
 * Alterado: João Madson
 * Data:     15/07/2020
 * Objetivo: Tarefa Redmine 224818
 * ----------------------------------------------------------------------------
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
    $botao                   = $_POST['Botao'];
    $grupoAtual              = $_POST['tramitacaoGrupo'];
    $orgaoAtual              = $_POST['tramitacaoOrgao'];
    $objetoAtual             = strtoupper2($_POST['tramitacaoObjeto']);
    $numeroCIAtual           = strtoupper2($_POST['tramitacaoNumeroCI']);
    $numeroOficioAtual       = strtoupper2($_POST['tramitacaoNumeroOficio']);
    $comissaoLicitacaoAtual   = $_POST['tramitacaoComissaoLicitacao'];
    $processoNumeroAtual     = $_POST['tramitacaoProcessoNumero'];
    $processoAnoAtual        = $_POST['tramitacaoProcessoAno'];
    $dataEntradaAtual        = $_POST['tramitacaoDataEntrada'];
    $valorEstimatoTotalAtual = $_POST['tramitacaoValorEstimadoTotal'];
    $observacaoAtual         = strtoupper2($_POST['tramitacaoObservacao']);
    $monitoramentoAtual      = strtoupper2($_POST['tramitacaoMonitoramento']);
    $solicitacaoAtual        = $_POST['seqSolicitacao'];
    $anoProtocoloAtual       = $_POST['tramitacaoAno'];
    $DDocumento              = $_POST['DDocumento'];
    $DDocumentoTram          = $_POST['DDocumentoTram'];
    $acao                    = $_POST['acao'];
    $agenteDestino           = $_POST['agenteDestino'];
    $dataEntradaTramitacao   = $_POST['DataEntradaTramitacao'];
    $horaTramitacao          = $_POST['HoraTramitacao'];
    $observacao              = $_POST['observacao'];
    $usuarioResponsavel      = $_POST['usuarioResponsavel'];
    $prazoAcao               = $_POST['prazoAcao'];
    $comissaoAtual           = $_POST['comissaoAtual'];
} else {
    $Critica  = $_GET['Critica'];
    $Mensagem = urldecode($_GET['Mensagem']);
    $Mens     = $_GET['Mens'];
    $Tipo     = $_GET['Tipo'];

    $dataEntradaAtual = date('d/m/Y') ;

    unset($_SESSION['Arquivos_Upload']);
    unset($_SESSION['Arquivos_Upload_Tramitacao']);
    unset($_SESSION['sccTramitacao']);
    unset($_SESSION['sccProcessoLic']);
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "CadTramitacaoProtocoloIncluirEspecial.php";

$parametrosGerais = dadosParametrosGerais();
$tamanhoObjeto           = $parametrosGerais[0];
$tamanhoJustificativa    = $parametrosGerais[1];
$tamanhoDescricaoServico = strlen($parametrosGerais[2]);
$subElementosEspeciais   = explode(',', $parametrosGerais[3]);
$tamanhoArquivo          = $parametrosGerais[4];
$tamanhoNomeArquivo      = $parametrosGerais[5];
$extensoesArquivo        = $parametrosGerais[6];

// Retorna os dados das ações
$htmlAcao = '';

$sql  = "SELECT CTACAOSEQU, ETACAODESC, ATACAOPRAZ, FTACAOCOMI, FTACAOANEX, ";
$sql .= "       FTACAOTUSU ";
$sql .= "FROM   SFPC.TBTRAMITACAOACAO ";
$sql .= "WHERE  FTACAOSITU = 'A' ";
$sql .= "       AND CGREMPCODI = " . $_SESSION['_cgrempcodi_'] . " ";
$sql .= "ORDER BY ATACAOORDE ASC ";

$result = $db->query($sql);

if (PEAR::isError($result)) {
    ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
} else {
    while ($Linha = $result->fetchRow()) {
        if ($Linha[0] . "_" . $Linha[2] . "_" . $Linha[3] . "_" . $Linha[4] . "_" . $Linha[5] == $acao) {
            $htmlAcao .= "<option selected='selected' value=\"$Linha[0]_$Linha[2]_$Linha[3]_$Linha[4]_$Linha[5]\">$Linha[1]</option>\n";

            $acaoDesc = $Linha[1];
        } else {
            $htmlAcao.= "<option value=\"$Linha[0]_$Linha[2]_$Linha[3]_$Linha[4]_$Linha[5]\">$Linha[1]</option>\n";
        }
    }
}

// Retorna os dados dos agentes destino
$htmlAgenteDestino = '';

$sql  = "SELECT CTAGENSEQU, ETAGENDESC, FTAGENTIPO ";
$sql .= "FROM   SFPC.TBTRAMITACAOAGENTE ";
$sql .= "WHERE  FTAGENSITU = 'A' ";
$sql .= "       AND CGREMPCODI = " . $_SESSION['_cgrempcodi_'];

if ($ultimoPasso) {
    $sql .= " AND CTAGENSEQU <> ".$ultimoPasso[3];
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

// Retorna os dados dos usuários do agente
$htmlUsuarioResponsavel = '';

if ($agenteDestino) {
    $arrAgenDest = explode('_', $agenteDestino);

    if ($arrAgenDest[0] > 0) {
        $txtAgenteDestSql= " AND AGUSU.CTAGENSEQU = " . $arrAgenDest[0];
    } else {
        $txtAgenteDestSql= "";
    }
} else {
    $txtAgenteDestSql= "";
}

$sql  = "SELECT USU.CUSUPOCODI, USU.EUSUPORESP, AGUSU.CTAGENSEQU ";
$sql .= "FROM   SFPC.TBTRAMITACAOAGENTEUSUARIO AGUSU ";
$sql .= "       JOIN SFPC.TBUSUARIOPORTAL USU ON USU.CUSUPOCODI = AGUSU.CUSUPOCODI ";
$sql .= "WHERE  1 = 1 ";
$sql .= $txtAgenteDestSql;
$sql .= " ORDER BY USU.EUSUPORESP ";

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
        $ano = date('Y');
    }

    $_SESSION['grupo_selecionado_protocolo'] = $grupoAtual;

    //passagem de parametro para não dar erro
    $valorEstimatoTotalAtual = str_replace(',','.',str_replace('.','',$valorEstimatoTotalAtual));
} elseif ($botao == 'Incluir') {
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
        if (empty($acao)) {
            adicionarMensagem("<a href='javascript:document.getElementById(\"inputEntrada\").focus();' class='titulo2'>Nenhuma ação inicial cadastrada para o Grupo</a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);

            $validar = false;
        }

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
        $hoje        = strtotime(date('Y/m/d'));

        if ($dateTimeIni > $hoje){
            adicionarMensagem("<a href='javascript:document.getElementById(\"inputEntrada\").focus();' class='titulo2'>Data de Entrada deve ser menor ou igual a de hoje</a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);

            $validar = false;
        }
    }

    // Verificar CI e Oficio - Não podem estar os dois vazios (pelo menos um deve ser preenchido pelo usuario)
    if (empty($numeroCIAtual) && empty($numeroOficioAtual)) {
        adicionarMensagem("<a href='javascript:document.getElementById(\"tramitacaoNumeroCI\").focus();' class='titulo2'>Os campos CI e OFICIO estão vazios, você deve preencher pelo menos um dos dois campos</a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);

        $validar = false;
    }

    if (!empty($numeroOficioAtual) && (!empty($orgaoAtual))) {
        $jaExisteOficioEmOutroProtocolo =  verificaOficioEmProtocolos($numeroOficioAtual, $orgaoAtual);

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

    $grupoProcesso = 'null';
    $orgaoProcesso = 'null';
    
    if ((!empty($processoNumeroAtual)) && (!empty($processoAnoAtual)) && (!empty($comissaoLicitacaoAtual))) {
        $processo = getProcesso($processoNumeroAtual, $processoAnoAtual, $comissaoLicitacaoAtual);

        if (!empty($processo)) {
            $jaExisteEmOutroProtocolo =  verificaProcessoEmProtocolos($processoNumeroAtual, $processoAnoAtual, $comissaoLicitacaoAtual);

            if (!$jaExisteEmOutroProtocolo) {
                $grupoProcesso = $processo[1];
                $orgaoProcesso = $processo[0];
            } else {
                adicionarMensagem("<a href='javascript:document.getElementById(\"inputProcesso\").focus();' class='titulo2'>O processo/ano já está cadastrado no protocolo ".$jaExisteEmOutroProtocolo[0]."/".$jaExisteEmOutroProtocolo[1]."</a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);

                $validar = false;
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

    // Verifica se a ação foi preenchida
    if ($acao == '0_0_0_0_0') {
        adicionarMensagem("<a href='javascript:document.getElementById(\"acao\").focus();' class='titulo2'>Próxima Ação</a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);

        $validar = false;    
    }

    $arrAcao = explode("_",$acao);

    if ($arrAcao[2] == 'S') {
        if ($comissaoAtual == '0') { // Validação da Comissão
            adicionarMensagem("<a href='javascript:document.getElementById(\"comissaoAtual\").focus();' class='titulo2'>Comissão de Licitação da Tramitação</a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);

            $validar = false;
        }
    }

    //Verifica se o agente de Destino foi selecionado
    if ($agenteDestino == '0_0' ) {
        adicionarMensagem("<a href='javascript:document.getElementById(\"agenteDestino\").focus();' class='titulo2'>Agente de Tramitação Destino</a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);

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
        //$hoje            = strtotime(date('Y/m/d'));

        if ($dateTimeIniTram > $hoje){
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

    // A variável arrAcao está mais acima na validação da comissão.
    $arrAgDestVer = explode("_",$agenteDestino);

    //Verifica se o agente de Destino foi selecionado
    if ($arrAgDestVer[1] == 'I' && $usuarioResponsavel <= 0 && $arrAcao[4] != 'S') {
        adicionarMensagem("<a href='javascript:document.getElementById(\"usuarioResponsavel\").focus();' class='titulo2'>Usuário Responsável da Tramitação</a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);

        $validar = false;    
    }

    if ($validar) {
        $anoProtocoloAtual       = !empty($anoProtocoloAtual) ? $anoProtocoloAtual : 'null';
        $numeroCIAtual           = !empty($numeroCIAtual) ?   strtoupper2($numeroCIAtual)  : '';
        $numeroOficioAtual       = !empty($numeroOficioAtual) ?    strtoupper2($numeroOficioAtual) : '';
        $comissaoLicitacaoAtual   = !empty($comissaoLicitacaoAtual) ? $comissaoLicitacaoAtual : 'null';
        $processoNumeroAtual     = !empty($processoNumeroAtual) ? $processoNumeroAtual : 'null';
        $processoAnoAtual        = !empty($processoAnoAtual) ? $processoAnoAtual : 'null';
        $solicitacaoAtual        = !empty($solicitacaoAtual) ? $solicitacaoAtual : 'null';
        $valorEstimatoTotalAtual = !empty($valorEstimatoTotalAtual) ? moeda2Float($valorEstimatoTotalAtual) : 'null';
        $observacaoAtual         = !empty($observacaoAtual) ? strtoupper2($observacaoAtual): '';
        $monitoramentoAtual      = !empty($monitoramentoAtual) ? strtoupper2($monitoramentoAtual): '';
        $objetoAtual             = !empty($objetoAtual) ? strtoupper2($objetoAtual): '';

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

        $numeroProtocoloAtual = getNumeroProtocolo($grupoAtual, date('Y'));
        $sequencial           = getSequencial();
        
        $tramitacao = array(
            $sequencial,
            $grupoAtual,
            $numeroProtocoloAtual,
            $anoProtocoloAtual,
            $orgaoAtual,
            "'" .strtoupper2($objetoAtual) . "'",
            "'" .strtoupper2($numeroCIAtual). "'",
            "'" .strtoupper2($numeroOficioAtual). "'",
            $solicitacaoAtual,
            $processoNumeroAtual,
            $processoAnoAtual,
            $grupoProcesso,
            $comissaoLicitacaoAtual,
            $orgaoProcesso,
            "'" . DataInvertida($dataEntradaAtual) ." ".date('H:i:s')."' ",
            $valorEstimatoTotalAtual,
            "'" .$observacaoAtual. "'",
            "'" .$monitoramentoAtual. "'",
            $_SESSION['_cusupocodi_'],
            $_SESSION['_cusupocodi_']
        );

        // Insert tramitação protocolo       
        $sql_insert_p = " INSERT INTO SFPC.TBTRAMITACAOPROTOCOLO
            (
              cprotcsequ,
              cgrempcod1,
              cprotcnump,
              aprotcanop,
              corglicod1,
              xprotcobje,
              eprotcnuci,
              eprotcnuof,
              csolcosequ,
              clicpoproc,
              alicpoanop,
              cgrempcodi,
              ccomlicodi,
              corglicodi,
              TPROTCENTR,
              vprotcvale,
              xprotcobse,
              xprotcmoni,
              cusupocodi,
              cusupocod1
            ) values (" . implode(',', $tramitacao) . ")";

        executarTransacao($db, $sql_insert_p);        

        $arrAcao       = explode('_', $acao);
        $arrAgenteDest = explode('_', $agenteDestino);

        // Verifica se a ação exige usuário ou pode ser enviada para todos do grupo
        if ($arrAcao[4] != 'S' ) {
            // Pega o primeiro usuario cadastrado no agente inicial
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
            $sqlAgente = " SELECT CTAGENSEQU FROM SFPC.TBTRAMITACAOAGENTE WHERE CGREMPCODI = " . $grupoAtual . " AND FTAGENALTE = 'S' ";

            $agenteDestinoTram = resultValorUnico(executarTransacao($db, $sqlAgente));
        } else {
            $agenteDestinoTram = $arrAgenteDest[0];
        }

        if ($arrAcao[2] == 'S' ) {
            $comissaoTramitacao = $comissaoAtual;
        } else {
            $comissaoTramitacao = 'NULL';
        }
        $dataEntradaTramitacao =  DataInvertida($dataEntradaTramitacao);
        $dataHoraTramitacao = "$dataEntradaTramitacao $horaTramitacao:00";

        if (!empty($agenteDestino)) {
            $sequencialTramitacao = getSequencialTramitacaoLicitacao($sequencial);

            $array_licitacao = array(
                $sequencial,
                $sequencialTramitacao,
                $arrAcao[0],
                $agenteDestinoTram,
                $usuarioResponsavel,
                $comissaoTramitacao,
                "'".$dataHoraTramitacao."'",
                'NULL',
                $arrAcao[1],
                "'".$observacao."'",
                "'A'",
                $_SESSION['_cusupocodi_'],
                'NOW()'
            );

            $sql_insert_tl = " INSERT INTO SFPC.TBTRAMITACAOLICITACAO
                (
                cprotcsequ,
                ctramlsequ,
                ctacaosequ,
                ctagensequ,
                cusupocodi,
                ccomlicodi,
                ttramlentr,
                ttramlsaid,
                atramlpraz,
                xtramlobse,
                ftramlsitu,
                cusupocod1,
                ttramlulat
                ) VALUES (" . implode(',', $array_licitacao) . ")";

            executarTransacao($db, $sql_insert_tl);
        }

        // INSERE OS ANEXOS DA TRAMITAÇÃO
        // Buscar o id da licitação para usar no ANEXO
        $sequencialProtocolo = $sequencial;

        // inserir documentos
        if (!empty($_SESSION['Arquivos_Upload_Tramitacao'])) {
            // Quantidade anexo
            $sql = ' SELECT count(*) FROM SFPC.TBTRAMITACAOLICITACAOANEXO WHERE  1=1';
            $doc_seq = resultValorUnico(executarTransacao($db, $sql)) + 1;

            $dirdestino = $GLOBALS['CAMINHO_UPLOADS'] . 'licitacoes/';
            for ($i = 0; $i < count($_SESSION['Arquivos_Upload_Tramitacao']['conteudo']); ++$i) {
                $NomeDocto = 'DOC_' . $sequencialProtocolo . '_' . $doc_seq . '_' . $_SESSION['Arquivos_Upload_Tramitacao']['nome'][$i];
                if ($_SESSION['Arquivos_Upload_Tramitacao']['situacao'][$i] == 'novo') {
                    
                    $arquivo =  bin2hex($_SESSION['Arquivos_Upload_Tramitacao']['conteudo'][$i]);

                    $sql = " INSERT INTO SFPC.tbtramitacaolicitacaoanexo
                            (ctramasequ, cprotcsequ, ctramlsequ, etramanome, itramaarqu, ttramacada, cusupocodi, ttramaulat
                        ) VALUES(
                            $doc_seq, $sequencialProtocolo, $sequencialTramitacao, '" . $NomeDocto . "', decode('".$arquivo."','hex'), NOW(),  " . $_SESSION['_cusupocodi_'] . ",  NOW()
                        )";
                    executarTransacao($db, $sql);
                    $doc_seq++;
                }
            }
            finalizarTransacao($db);
        }

        // Protocolo Solicitação
        if (!empty($solicitacaoAtual) && $solicitacaoAtual != 'null') {
            $tramitacao_solicitacao_ = array($sequencial, $solicitacaoAtual, $_SESSION['_cusupocodi_']);

            $sql_insert_ps = " INSERT INTO SFPC.TBTRAMITACAOPROTOCOLOSOLICITACAO
                        (
                            cprotcsequ,
                            csolcosequ,
                            cusupocodi
                        ) values (" . implode(',', $tramitacao_solicitacao_). ")";
            executarTransacao($db, $sql_insert_ps);
        }

        finalizarTransacao($db);

        // Buscar o last inserted id
        $sql = ' SELECT cprotcsequ FROM SFPC.TBTRAMITACAOPROTOCOLO WHERE  1=1 ORDER BY cprotcsequ DESC LIMIT 1';
        $sequencialProtocolo = resultValorUnico(executarTransacao($db, $sql));

        // inserir documentos
        if (!empty($_SESSION['Arquivos_Upload'])) {
            // Quantidade anexo
            $sql = ' SELECT count(*) FROM SFPC.TBTRAMITACAOPROTOCOLOANEXO WHERE  1=1';
            $doc_seq = resultValorUnico(executarTransacao($db, $sql)) + 1;

            $dirdestino = $GLOBALS['CAMINHO_UPLOADS'] . 'licitacoes/';

            for ($i = 0; $i < count($_SESSION['Arquivos_Upload']['conteudo']); ++$i) {
                $NomeDocto = 'DOC_' . $sequencialProtocolo . '_' . $doc_seq . '_' . $_SESSION['Arquivos_Upload']['nome'][$i];

                if ($_SESSION['Arquivos_Upload']['situacao'][$i] == 'novo') {
                    $arquivo =  bin2hex($_SESSION['Arquivos_Upload']['conteudo'][$i]);

                    $sql = " INSERT INTO SFPC.TBTRAMITACAOPROTOCOLOANEXO(
                            CPROTCSEQU, CPANEXSEQU, EPANEXNOME, IPANEXARQU, TPANEXCADA, CUSUPOCODI, TPANEXULAT
                        ) VALUES(
                            $sequencialProtocolo, $doc_seq, '" . $NomeDocto . "', decode('".$arquivo."','hex'), NOW(),  " . $_SESSION['_cusupocodi_'] . ",  NOW()
                        )";
                    executarTransacao($db, $sql);
                    $doc_seq++;
                }
            }

            finalizarTransacao($db);
        }

        $protcoloTexto = $numeroProtocoloAtual.'/'.date('Y');
        if($txtSccCadastrada=='A SCC cadastrada já está associada a outro protocolo'){
            //$Mensagem = $txtSccCadastrada;//aparece apenas se tiver scc associada a outro protocolo.        
            //$Mensagem .= "Protocolo ".$protcoloTexto." cadastrado com Sucesso";
            adicionarMensagem("<a href='javascript:document.getElementById(\"tdScc\").focus();' class='titulo2'>A SCC cadastrada já está associada a outro protocolo</a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
            
        }else{
            $Mensagem = "Protocolo ".$protcoloTexto." cadastrado com Sucesso";
            header('Location: CadTramitacaoProtocoloIncluirEspecial.php?Mens=1&Tipo=1&Mensagem='.  $Mensagem);
        }
    } else {
        //$numeroProtocoloAtual    = ($numeroProtocoloAtual != 'null') ? $numeroProtocoloAtual : '';
        $anoProtocoloAtual       = ($anoProtocoloAtual != 'null') ? $anoProtocoloAtual : '';
        $numeroCIAtual           = ($numeroCIAtual != 'null') ? $numeroCIAtual : '';
        $numeroOficioAtual       = ($numeroOficioAtual != 'null') ? $numeroOficioAtual: '';
        $comissaoLicitacaoAtual   = ($comissaoLicitacaoAtual != 'null') ? $comissaoLicitacaoAtual : '';
        $processoNumeroAtual     = ($processoNumeroAtual != 'null') ? $processoNumeroAtual : '';
        $processoAnoAtual        = ($processoAnoAtual != 'null') ? $processoAnoAtual : '';
        $solicitacaoAtual        = ($solicitacaoAtual != 'null') ? $solicitacaoAtual : '';

        if ($valorEstimatoTotalAtual != 'null') {
            //passagem de parametro para não dar erro
            $valorEstimatoTotalAtual = str_replace(',','.',str_replace('.','',$valorEstimatoTotalAtual));
        } else { 
            $valorEstimatoTotalAtual = '';
        }

        $observacaoAtual         = ($observacaoAtual != 'null') ?strtoupper2($observacaoAtual): '';
        $monitoramentoAtual         = ($monitoramentoAtual != 'null') ?strtoupper2($monitoramentoAtual): '';
    }
} elseif ($botao == 'Incluir_Documento') {
    if ($_FILES['Documentacao']['tmp_name']) {
        $_FILES['Documentacao']['name'] = tratarNomeArquivo($_FILES['Documentacao']['name']);

        $extensoesArquivo .= ', .zip, .xlsm, .xls, .ods, .pdf, .doc, .odt';

        $extensoes = explode(',', strtolower2($extensoesArquivo));
        array_push($extensoes, '.zip', '.xlsm', '.xlsx');

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
        if ($_SESSION['Arquivos_Upload']['situacao'][$valor] == 'novo') {
            $_SESSION['Arquivos_Upload']['situacao'][$valor] = 'cancelado'; // cancelado- quando o usuário incluiu um arquivo novo mas desistiu
        } elseif ($_SESSION['Arquivos_Upload']['situacao'][$valor] == 'existente') {
            $_SESSION['Arquivos_Upload']['situacao'][$valor] = 'excluido'; // excluído- quando o arquivo já existe e deve ser excluido no sistema
        }
    }

    //passagem de parametro para não dar erro
    $valorEstimatoTotalAtual = str_replace(',','.',str_replace('.','',$valorEstimatoTotalAtual));
} elseif ($botao == 1) {
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
        $Processo             = substr($NProcessoAnoComissao[0] + 10000,1);
        $ProcessoAno          = $NProcessoAnoComissao[1];
        $ComissaoCodigo       = $NProcessoAnoComissao[2];
        $novaTela 			  = $NProcessoAnoComissao[3];

        if ($novaTela=="1") {
            $Url = "CadLicitacaoAlterarNovo.php?Processo=$Processo&ProcessoAno=$ProcessoAno&ComissaoCodigo=$ComissaoCodigo";
        } else {
            $Url = "CadLicitacaoAlterar.php?Processo=$Processo&ProcessoAno=$ProcessoAno&ComissaoCodigo=$ComissaoCodigo";
        }

        if (!in_array($Url,$_SESSION['GetUrl'])) { $_SESSION['GetUrl'][] = $Url; }
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
            document.CadTramitacaoProtocoloIncluirEspecial.Botao.value=valor;
            document.CadTramitacaoProtocoloIncluirEspecial.submit();
        }

        function CaracteresObjeto(text,campo) {
            input = document.getElementById(campo);
            input.value = text.value.length;
        }

        function AbreJanela(url,largura,altura) {
            if ($('#inputGrupo').val() > 0) {
                window.open(url,'paginadetalhe','status=no,scrollbars=yes,left=40,top=120,width='+largura+',height='+altura);
            } else {
                alert('Primeiro você deve escolher o grupo.');
            }
        }

        function janela(url,largura,altura) {
            window.open(url,'paginadetalhe','status=no,scrollbars=yes,left=300,top=300,width=220,height=140');
        }

        function escolherComissao() {
            $('#inputComissao').val($('#comissSelect').val()); 
        }

        function removerScc() {
            $('#inputScc').val('');
            $('#inputObjeto').val('');
            $('#inputProcesso').val('');
            $('#inputProcessoAno').val('');
            $('#inputComissao').val('');
            $('#inputProcesso').attr('readonly','');
            $('#inputProcessoAno').attr('readonly','');
            $('#inputProcesso').prop('readonly','');
            $('#inputProcessoAno').prop('readonly','');
            $('#tdScc').html("<input id=\"inputScc\" type=\"hidden\" name=\"seqSolicitacao\" value=\"\"><a href=\"javascript:AbreJanela('./JanelaLicitacaoIncluir.php?ProgramaOrigem=CadTramitacaoProtocoloIncluirEspecial&amp;TipoUsuario=C',1200,540);\" id=\"CentroCustoLink\"><img src=\"../midia/lupa.gif\" border=\"0\"> </a>");

            document.CadTramitacaoProtocoloIncluirEspecial.Botao.value='excluirScc';
            document.CadTramitacaoProtocoloIncluirEspecial.submit();
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
            var options = $('#usuarioResponsavel');

            options.find('option').remove();

            $('<option>').val('0').text('Selecione um usuário...').appendTo(options);

            i=0;
            $.each(arrUsuariosAgente, function (key, value) {
                arrUsuarios = arrUsuariosAgente[i];

                if (codAgente == arrUsuarios[2]) {
                    $('<option>').val(arrUsuarios[0]).text(arrUsuarios[1]).appendTo(options);
                }    

                i++;
            });
        }

        function preenchePrazoDaAcao(numAcao) {
            var arrAcao = numAcao.explode("_"),
                prazoAcao = arrAcao[1],
                apresentaComissao = arrAcao[2],
                escondeUsuario= arrAcao[4],
                dadosAgente = $('#agenteDestino').val(),
                arrAgente = dadosAgente.explode("_"),
                tipoAgenteDestino = arrAgente[1];

            document.CadTramitacaoProtocoloIncluirEspecial.prazoAcao.value = prazoAcao;

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
                document.CadTramitacaoProtocoloIncluirEspecial.Botao.value = acao;
                document.CadTramitacaoProtocoloIncluirEspecial.submit();
            });
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
    </script>
    <script language="JavaScript">Init();</script>
    <form action="CadTramitacaoProtocoloIncluirEspecial.php" method="POST" name="CadTramitacaoProtocoloIncluirEspecial" enctype="multipart/form-data">
        <br><br><br><br><br>
        <table cellpadding="3" border="0" summary="">
            <!-- Caminho -->
            <tr>
                <td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
                <td align="left" class="textonormal" colspan="2">
                    <font class="titulo2">|</font>
                    <a href="../index.php"><font color="#000000">Página Principal</font></a> > Licitações > Tramitação > Número Protocolo > Incluir Especial
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
            } ?>
            <!-- Fim do Erro -->
            <!-- Corpo -->
            <tr>
                <td width="150"></td>
                <td class="textonormal">
                    <table width="100%" border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal"  bgcolor="#FFFFFF">
                        <tr>
                            <td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
                                INCLUIR ESPECIAL - NÚMERO PROTOCOLO PARA PROCESSOS LICITATÓRIOS
                            </td>
                        </tr>
                        <tr>
                            <td class="textonormal">
                                <p align="justify">
                                    Preencha os dados abaixo e clique no botão 'Incluir'. Os itens obrigatórios estão com *. <br>Pode-se anexar documentos em pdf.
                                </p>
                            </td>
                        </tr>
                        <tr style="border-bottom:0px solid #ffffff">
                            <td>
                                <table border="0" summary="" width="100%">
                                    <tr>
                                        <td class="textonormal" bgcolor="#DCEDF7">Grupo* </td>
                                        <td class="textonormal"><input type="hidden" name="Critica" value="1"></td>
                                        <td class="textonormal">
                                            <select name="tramitacaoGrupo" id="inputGrupo" class="textonormal capturarValorAcaoGrupo" data-acao="SelecionarGrupo">
                                                <option value="">Selecione o grupo...</option>
                                                <?php
                                                $cgrempcodi = ($_SESSION['_fperficorp_'] != 'S') ? $_SESSION['_cgrempcodi_'] : null;
                                                $grupos = getGrupos($cgrempcodi);

                                                while ($grupo = $grupos->fetchRow()) {
                                                    ?>
                                                    <option <?php echo (isset($grupoAtual) && $grupoAtual == $grupo[0]) ? 'selected' : ''?> value="<?php echo $grupo[0]; ?>"><?php echo $grupo[1]; ?></option>
                                                    <?php
                                                }
                                                ?>
                                            </select>
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
                                        <td class="textonormal" bgcolor="#DCEDF7">Órgão Demandante* </td>
                                        <td class="textonormal"><input type="hidden" name="Critica" value="1"></td>
                                        <td class="textonormal">
                                            <select id="inputOrgao" name="tramitacaoOrgao" class="textonormal">
                                                <option value="">Selecione o órgão...</option>
                                                <?php
                                                if (!empty($grupoAtual)) {
                                                    $orgaos = getOrgaos($grupoAtual);

                                                    while ($orgao = $orgaos->fetchRow()) {
                                                        ?>
                                                        <option <?php echo (isset($orgaoAtual) && $orgaoAtual == $orgao[0]) ? 'selected' : ''?> value="<?php echo $orgao[0]; ?>"><?php echo $orgao[1]; ?></option>
                                                        <?php
                                                    }
                                                }
                                                ?>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="textonormal" bgcolor="#DCEDF7">Objeto* </td>
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
                                                        echo $sccSessao[2];
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
                                            if (!empty($_SESSION['sccTramitacao'])) {
                                                ?>
                                                <?php
                                                echo $sccSessao[1];
                                                ?>
                                                <input id="inputScc" type="hidden" name="seqSolicitacao" value="<?php echo $sccSessao[0];?>">
                                                <?php
                                            }
                                            ?>
                                            <a href="javascript:AbreJanela('./JanelaLicitacaoIncluir.php?ProgramaOrigem=CadTramitacaoProtocoloIncluirEspecial&amp;TipoUsuario=C',1200,540);" id="CentroCustoLink">
                                            <img src="../midia/lupa.gif" border="0"></a>
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
                                                            $comissaoLicitacaoOpcao = $comissao[0];
                                                            $txtComissao            = $comissao[1];
                                                            ?>
                                                            <option value="<?php echo $comissaoLicitacaoOpcao; ?>" <?php echo $comissao[0] == $codComissao ? 'selected' : ''; ?>><?php echo $txtComissao; ?></option>
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
                                        <td class="textonormal" bgcolor="#DCEDF7">Data de Entrada* </td>
                                        <td class="textonormal"><input type="hidden" name="Critica" value="1"></td>
                                        <td class="textonormal">
                                            <input id="inputEntrada" type="text" maxlength="10" value="<?php echo (!empty($dataEntradaAtual)) ? $dataEntradaAtual : ''; ?>" name="tramitacaoDataEntrada" class="textonormal" />
                                            <a href="javascript:janela('../calendario.php?Formulario=CadTramitacaoProtocoloIncluirEspecial&Campo=tramitacaoDataEntrada','Calendario',220,170,1,0)">
                                            <img src="../midia/calendario.gif" border="0" alt=""></a>&nbsp;<font class="textonormal">dd/mm/aaaa</font>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="textonormal" bgcolor="#DCEDF7">Valor Estimado Total (Informado pelo Área Demandante)</td>
                                        <td class="textonormal"><input type="hidden" name="Critica" value="1"></td>
                                        <td class="textonormal">
                                            <input id="inputValor" type="text" maxlength="16" value="<?php echo (!empty($valorEstimatoTotalAtual)) ? number_format($valorEstimatoTotalAtual, 2, ',', '.') : ''; ?>" name="tramitacaoValorEstimadoTotal" class="dinheiro textonormal" />
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
                                                  maxlength="400" 
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
                                        <td align="center" class="textonormal titulo3" bgcolor="#75ADE6" colspan="3"><b>Anexação de Documentos</b></td>
                                    </tr>
                                    <tr>
                                        <?php $DTotal = count($_SESSION['Arquivos_Upload']['conteudo']);?>
                                        <td class="textonormal" bgcolor="#DCEDF7" rowspan="<?php echo $DTotal?>">Anexação de Documento(s) </td>
                                        <td></td>
                                        <td class="textonormal" >
                                            <input type="file" name="Documentacao" class="textonormal" />
                                            <table width="100%" border="0" cellpadding="0" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal"  bgcolor="#FFFFFF">
                                                <?php
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
                                            <input id="inputEntradaTramitacao" type="text" size="10" maxlength="10" value="<?php echo (!empty($dataEntradaTramitacao)) ? $dataEntradaTramitacao : ''; ?>" name="DataEntradaTramitacao" class="textonormal" />
                                            <a href="javascript:janela('../calendario.php?Formulario=CadTramitacaoProtocoloIncluirEspecial&Campo=DataEntradaTramitacao','Calendario',220,170,1,0)"><img src="../midia/calendario.gif" border="0" alt=""></a>&nbsp;<font class="textonormal">dd/mm/aaaa</font>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="textonormal" bgcolor="#DCEDF7">Hora de entrada da Tramitação*</td>
                                        <td class="textonormal"><input type="hidden" name="Critica" value="1"></td>
                                        <td class="textonormal">
                                            <input id="HoraTramitacao" name="HoraTramitacao" type="text" size="10" maxlength="6" value="<?php echo $horaTramitacao ?>" class="textonormal mascara-hora">
                                            hh:mm
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="textonormal" bgcolor="#DCEDF7">Observação</td>
                                        <td></td>
                                        <td class="textonormal">
                                            <font class="textonormal">máximo de 500 caracteres</font>
                                            <input type="text" name="NCaracteres" disabled="" readonly="" size="3" value="0" class="textonormal"><br>
                                            <textarea id="observacao" name="observacao" maxlength="500" cols="50" rows="4" onkeyup="javascript:CaracteresObservacao(1)" onblur="javascript:CaracteresObservacao(0)" onselect="javascript:CaracteresObservacao(1)" class="textonormal"><?php echo $observacao; ?></textarea>
                                            <script language="javascript" type="">
                                                function CaracteresObservacao(valor) {
                                                    CadTramitacaoProtocoloIncluirEspecial.NCaracteres.value = '' +  CadTramitacaoProtocoloIncluirEspecial.observacao.value.length;
                                                }
                                            </script>
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
                                <input type="submit" value="Incluir" onclick="javascript:enviar('Incluir');" class="botao">
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
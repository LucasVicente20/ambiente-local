<?php
/**
 * Portal de Compras
 * 
 * Programa: RelGerencialTramitacao.php
 * Autor:    Pitang Agile TI - Ernesto Ferreira
 * -------------------------------------------------------------------------------------
 * Alterado: Lucas Baracho
 * Data:     19/06/2019
 * Objetivo: Tarefa Redmine 218203
 * -------------------------------------------------------------------------------------
 */

# Acesso ao arquivo de funções #
include "./funcoesTramitacao.php";

# Acesso ao arquivo de funções #
require_once '../compras/funcoesCompras.php';

# Executa o controle de segurança #
session_start();
Seguranca();

# Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD']  == "POST") {
    $botao                  = $_POST['Botao'];
    $numeroProtocoloAtual   = $_POST['tramitacaoNumeroProtocolo'];
    $anoProtocoloAtual      = $_POST['tramitacaoAnoProtocolo'];
    $grupoAtual             = $_POST['tramitacaoGrupo'];
    $grupoDescricao         = $_POST['grupoDescricao'];
    $orgaoAtual             = $_POST['tramitacaoOrgao'];
    $orgaoDescricao         = $_POST['orgaoDescricao'];
    $objetoAtual            = strtoupper2($_POST['tramitacaoObjeto']);
    $numeroCIAtual          = strtoupper2($_POST['tramitacaoNumeroCI']);
    $numeroOficioAtual      = strtoupper2($_POST['tramitacaoNumeroOficio']);
    $numeroSccAtual         = $_POST['tramitacaoNumeroScc'];
    $comissaoLicitacaoAtual = $_POST['tramitacaoComissaoLicitacao'];
    $comissaoDescricao      = $_POST['comissaoDescricao'];
    $acaoAtual              = $_POST['tramitacaoAcao'];
    $acaoDescricao          = $_POST['acaoDescricao'];
    $agenteAtual            = $_POST['tramitacaoAgenteDestino'];
    $agenteDescricao        = $_POST['agenteDescricao'];    
    $modalidade             = $_POST['inputModalidade'];
    $processoNumeroAtual    = $_POST['tramitacaoProcessoNumero'];
    $processoAnoAtual       = $_POST['tramitacaoProcessoAno'];
    $dataEntradaInicioAtual = $_POST['tramitacaoDataEntradaInicio'];
    $dataEntradaFimAtual    = $_POST['tramitacaoDataEntradaFim'];
    $situacaoAtual          = $_POST['tramitacaoSituacao'];
    $ordemAtual             = $_POST['tramitacaoOrdem'];
    $atrasoAtual            = $_POST['tramitacaoAtraso'];
} else {
    $botao                   = $_GET['Botao'];
    $numeroProtocoloAtual    = $_GET['tramitacaoNumeroProtocolo'];
    $anoProtocoloAtual       = $_GET['tramitacaoAnoProtocolo'];
    $grupoAtual              = $_GET['tramitacaoGrupo'];
    $grupoDescricao          = $_GET['grupoDescricao'];
    $orgaoAtual              = $_GET['tramitacaoOrgao'];
    $orgaoDescricao          = $_GET['orgaoDescricao'];
    $objetoAtual             = strtoupper2($_GET['tramitacaoObjeto']);
    $numeroCIAtual           = strtoupper2($_GET['tramitacaoNumeroCI']);
    $numeroOficioAtual       = strtoupper2($_GET['tramitacaoNumeroOficio']);
    $numeroSccAtual          = $_GET['tramitacaoNumeroScc'];
    $comissaoLicitacaoAtual  = $_GET['tramitacaoComissaoLicitacao'];
    $comissaoDescricao       = $_GET['comissaoDescricao'];
    $acaoAtual               = $_GET['tramitacaoAcao'];
    $acaoDescricao           = $_GET['acaoDescricao'];
    $agenteAtual             = $_GET['tramitacaoAgenteDestino'];
    $agenteDescricao         = $_GET['agenteDescricao'];    
    $modalidade              = $_GET['inputModalidade'];
    $processoNumeroAtual     = $_GET['tramitacaoProcessoNumero'];
    $processoAnoAtual        = $_GET['tramitacaoProcessoAno'];
    $dataEntradaInicioAtual  = $_GET['tramitacaoDataEntradaInicio'];
    $dataEntradaFimAtual     = $_GET['tramitacaoDataEntradaFim'];
    $situacaoAtual           = $_GET['tramitacaoSituacao'];
    $ordemAtual              = $_GET['tramitacaoOrdem'];
    $atrasoAtual             = $_GET['tramitacaoAtraso'];

    $Mensagem = urldecode($_GET['Mensagem']);

    $Mens     = $_GET['Mens'];
    $Tipo     = $_GET['Tipo'];

    unset($_SESSION['Arquivos_Upload']);
    unset($_SESSION['sccTramitacao']);
}                                               

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "RelGerencialTramitacao.php";

$parametrosGerais = dadosParametrosGerais();
$tamanhoObjeto           = $parametrosGerais[0];
$tamanhoJustificativa    = $parametrosGerais[1];
$tamanhoDescricaoServico = strlen($parametrosGerais[2]);
$subElementosEspeciais   = explode(',', $parametrosGerais[3]);
$tamanhoArquivo          = $parametrosGerais[4];
$tamanhoNomeArquivo      = $parametrosGerais[5];
$extensoesArquivo        = $parametrosGerais[6];

if ($botao == 'Pesquisar' || $botao == 'Planilha' || $buscar_retorno['botao']=='Pesquisar') {
    $validar = true;

    $protocolo = array();
    $buscar = array(
        'grupo'          => $grupoAtual,
        'grupoDesc'      => $grupoDescricao,
        'orgao'          => $orgaoAtual,
        'orgaoDesc'      => $orgaoDescricao,
        'objeto'         => $objetoAtual,
        'numeroCI'       => $numeroCIAtual,
        'numeroOficio'   => $numeroOficioAtual,
        'numeroScc'      => $numeroSccAtual,
        'comissao'       => $comissaoLicitacaoAtual,
        'comissaoDesc'   => $comissaoDescricao,
        'processoNumero' => $processoNumeroAtual,
        'processoAno'    => $processoAnoAtual,
        'dataInicio'     => $dataEntradaInicioAtual,
        'dataFim'        => $dataEntradaFimAtual,
        'situacao'       => $situacaoAtual,
        'acao'           => $acaoAtual,
        'acaoDesc'       => $acaoDescricao,
        'agente'         => $agenteAtual,
        'agenteDesc'     => $agenteDescricao,
        'ordem'          => $ordemAtual,
        'atraso'         => $atrasoAtual,
        'protocolo'      => $numeroProtocoloAtual,
        'anoProtocolo'   => $anoProtocoloAtual,
        'modalidade'     => $modalidade
    );

    if (!empty($buscar['protocolo'])) {
        if (!SoNumeros($buscar['protocolo'])) {
            adicionarMensagem("<a href='javascript:document.getElementById(\"inputProtocolo\").focus();' class='titulo2'>Número do protocolo deve ser numérico</a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
            $validar = false;
        }
    }

    if (!empty($buscar['anoProtocolo'])) {
        if (!SoNumeros($buscar['anoProtocolo'])) {
            adicionarMensagem("<a href='javascript:document.getElementById(\"inputProtocoloAno\").focus();' class='titulo2'>Ano do protocolo deve ser numérico</a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
            $validar = false;
        }
    }

    if (!empty($buscar['processoNumero'])) {
        if (!SoNumeros($buscar['processoNumero'])) {
            adicionarMensagem("<a href='javascript:document.getElementById(\"inputProtocolo\").focus();' class='titulo2'>Número do processo deve ser numérico</a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
            $validar = false;
        }
    }

    if (!empty($buscar['processoAno'])) {
        if (!SoNumeros($buscar['processoAno'])) {
            adicionarMensagem("<a href='javascript:document.getElementById(\"inputProtocoloAno\").focus();' class='titulo2'>Ano do processo deve ser numérico</a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
            $validar = false;
        }
    }

    if (!empty($buscar['dataInicio']) && ValidaData($buscar['dataInicio'])) {
        adicionarMensagem("<a href='javascript:document.getElementById(\"inputEntrada\").focus();' class='titulo2'>Data de Inicio</a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
        $validar = false;
    }

    if (!empty($buscar['dataFim']) && ValidaData($buscar['dataFim'])) {
        adicionarMensagem("<a href='javascript:document.getElementById(\"inputFim\").focus();' class='titulo2'>Data Fim</a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
        $validar = false;
    }

    if ($validar) {
        $dados          = relatorioGerencialTramitacao($buscar, 'relMonitoramento');
        $arrModalidades = getModalidadesRelGerencialTramitacao($buscar, '');
        $arrAcoes       = getAcoesRelGerencial(null, $buscar);

        $htmlAcoesTit = '';
        $totalAcoes = 4;

        foreach ($arrAcoes as $objAcao) {
            $htmlAcoesTit .= '<td class="titulo3" bgcolor="#DCEDF7" class="textonormal" align="center">'.$objAcao[0].'</td>';
            $totalAcoes++;
        }

        // Adicionar último passo de cada protocolo
        if (!empty($dados)) {
            $_REQUEST['rotina'] = 'Monitoramento';
            $_SESSION['origemPesquisa'] = $_REQUEST;
        }

        if ($botao == 'Planilha') {
            gerarCsvRelGeralTramitacao($dados, $arrModalidades, $arrAcoes, 'MODALIDADE');
        }
    } elseif ($botao == 'Imprimir') {
        $validar = true;
        $buscar = array(
            'grupo'          => $grupoAtual,
            'grupoDesc'      => $grupoDescricao,
            'orgao'          => $orgaoAtual,
            'orgaoDesc'      => $orgaoDescricao,
            'objeto'         => $objetoAtual,
            'numeroCI'       => $numeroCIAtual,
            'numeroOficio'   => $numeroOficioAtual,
            'numeroScc'      => $numeroSccAtual,
            'comissao'       => $comissaoLicitacaoAtual,
            'comissaoDesc'   => $comissaoDescricao,
            'processoNumero' => $processoNumeroAtual,
            'processoAno'    => $processoAnoAtual,
            'dataInicio'     => $dataEntradaInicioAtual,
            'dataFim'        => $dataEntradaFimAtual,
            'situacao'       => $situacaoAtual,
            'acao'           => $acaoAtual,
            'acaoDesc'       => $acaoDescricao,
            'agente'         => $agenteAtual,
            'agenteDesc'     => $agenteDescricao,
            'ordem'          => $ordemAtual,
            'atraso'         => $atrasoAtual,
            'protocolo'      => $numeroProtocoloAtual,
            'anoProtocolo'   => $anoProtocoloAtual,
            'modalidade'     => $modalidade,
            'relatorio'      => 'inicial'
        );

        if (!empty($buscar['dataInicio']) && ValidaData($buscar['dataInicio'])) {
            adicionarMensagem("<a href='javascript:document.getElementById(\"inputEntrada\").focus();' class='titulo2'>Data de Inicio</a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
            $validar = false;
        }

        if (!empty($buscar['dataFim']) && ValidaData($buscar['dataFim'])) {
            adicionarMensagem("<a href='javascript:document.getElementById(\"inputFim\").focus();' class='titulo2'>Data Fim</a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
            $validar = false;
        }

        if (!empty($buscar['processoNumero'])) {
            if (!SoNumeros($buscar['processoNumero'])) {
                adicionarMensagem("<a href='javascript:document.getElementById(\"inputProtocolo\").focus();' class='titulo2'>Número do processo deve ser numérico</a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
                $validar = false;
            }
        }

        if (!empty($buscar['processoAno'])) {
            if (!SoNumeros($buscar['processoAno'])) {
                adicionarMensagem("<a href='javascript:document.getElementById(\"inputProtocoloAno\").focus();' class='titulo2'>Ano do processo deve ser numérico</a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
                $validar = false;
            }
        }

        if ($validar) {
            $_SESSION['buscar'] = $buscar;
            header('Location: RelGerencialTramitacaoPdf.php');
            exit();
        }
    } elseif ($botao == 'Limpar') {
        $numeroProtocoloAtual    = '';
        $anoProtocoloAtual       = '';
        $grupoAtual              = '';
        $grupoDescricao          = '';
        $orgaoAtual              = '';
        $orgaoDescricao          = '';
        $objetoAtual             = '';
        $numeroCIAtual           = '';
        $numeroOficioAtual       = '';
        $numeroSccAtual          = '';
        $comissaoLicitacaoAtual  = '';
        $comissaoDescricao       = '';
        $acaoAtual               = '';
        $acaoDescricao           = '';
        $agenteAtual             = '';
        $agenteDescricao         = '';    
        $modalidade              = '';
        $processoNumeroAtual     = '';
        $processoAnoAtual        = '';
        $dataEntradaInicioAtual  = '';
        $dataEntradaFimAtual     = '';
        $situacaoAtual           = '';
        $ordemAtual              = '';
        $atrasoAtual             = '';

        header('Location: RelGerencialTramitacao.php');
    } elseif (!empty($_SESSION['ultima_pesquisa_relger'])) {
        $buscar = $_SESSION['ultima_pesquisa_relger'];

        $dados = relatorioGerencialTramitacao($buscar, 'relMonitoramento');
        $arrModalidades = getModalidadesRelGerencialTramitacao($buscar, '');
        $arrAcoes = getAcoesRelGerencial(null, $buscar);
    
        $htmlAcoesTit = '';
        $totalAcoes = 4;

        foreach ($arrAcoes as $objAcao) {
            $htmlAcoesTit .= '<td class="titulo3" bgcolor="#DCEDF7" class="textonormal" align="center">'.$objAcao[0].'</td>';
            $totalAcoes++;
        }

        // Adicionar último passo de cada protocolo
        if(!empty($dados)) {
            $_REQUEST['rotina'] = 'Monitoramento';
            $_SESSION['origemPesquisa'] = $_REQUEST;       
        }
    }
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
        function enviaForm(valor) {
            document.RelTramitacaoGerencial.action = 'RelGerencialTramitacaoModalidade.php';
            document.RelTramitacaoGerencial.codModalidade.value = valor; 
            document.RelTramitacaoGerencial.Botao.value = valor;
            urldata = $('#RelTramitacaoGerencial').serialize();    
            //document.RelTramitacaoGerencial.submit();
            window.location.href = "RelGerencialTramitacaoModalidade.php?"+urldata; 
        }

        function enviar(valor){
            preencherDados();
            document.RelTramitacaoGerencial.Botao.value=valor;
            urldata = $('#RelTramitacaoGerencial').serialize();

            if (valor != 'Limpar') {
                window.location.href = "RelGerencialTramitacao.php?"+urldata; 
            } else {
                window.location.href = "RelGerencialTramitacao.php"; 
            }
        }

        function preencherDados() {
            $('#grupoDescricao').val($('#inputGrupo option:selected').text());
            $('#orgaoDescricao').val($('#inputOrgao option:selected').text());
            $('#comissaoDescricao').val($('#inputComissao option:selected').text());
            $('#acaoDescricao').val($('#inputAcao option:selected').text());
            $('#agenteDescricao').val($('#inputAgenteDestino option:selected').text());
        }

        function CaracteresObjeto(text,campo){
            input = document.getElementById(campo);
            input.value = text.value.length;
        }

        function AbreJanela(url,largura,altura) {
            window.open(url,'paginadetalhe','status=no,scrollbars=yes,left=40,top=120,width='+largura+',height='+altura);
        }

        $(document).ready(function() {
            $(".capturarValorAcaoGrupo").change(function() {
                var acao  = $(this).attr('data-acao');
                document.RelTramitacaoGerencial.Botao.value = acao;
                document.RelTramitacaoGerencial.submit();
            });

            //$('#numeroAno').mask('9999/9999');  
            $('#numeroScc').mask('9999.9999/9999');  
            $('#inputEntradaInicio').mask('99/99/9999');
            $('#inputEntradaFim').mask('99/99/9999');
        });
    </script>
    <script language="JavaScript">Init();</script>
    <form action="RelGerencialTramitacao.php" method="POST" id="RelTramitacaoGerencial" name="RelTramitacaoGerencial" enctype="multipart/form-data" >
        <br><br><br><br><br>
        <table cellpadding="3" border="0" summary="">
            <!-- Caminho -->
            <tr>
                <td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
                <td align="left" class="textonormal" colspan="2">
                    <font class="titulo2">|</font>
                    <a href="../index.php"><font color="#000000">Página Principal</font></a> > Licitações > Tramitação > Relatórios > Relatório Gerencial de Tramitação
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
                <td class="textonormal" >
                    <table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal"  bgcolor="#FFFFFF">
                        <tr>
                            <td align="center" colspan="<?php echo $totalAcoes ?>" bgcolor="#75ADE6" valign="middle" class="titulo3">
                                RELATÓRIO GERENCIAL DE TRAMITAÇÃO
                            </td>
                        </tr>                    
                        <tr>
                            <td colspan="<?php echo $totalAcoes ?>">
                                <table border="0" summary="">
                                    <tr>
                                        <td class="textonormal" bgcolor="#DCEDF7">Grupo</td>
                                        <td class="textonormal">
                                            <select name="tramitacaoGrupo" id="inputGrupo" class="textonormal capturarValorAcaoGrupo" data-acao="SelecionarGrupo">
                                                <option value="">Selecione o grupo...</option>
                                                <?php
                                                $cgrempcodi = ($_SESSION['_fperficorp_'] != 'S') ? $_SESSION['_cgrempcodi_'] : null;
                                                $grupos = getGrupos($cgrempcodi);

                                                while ($grupo = $grupos->fetchRow()) {
                                                    ?>
                                                    <option
                                                        <?php 
                                                        if (isset($grupoAtual)) {
                                                            if ($grupoAtual == $grupo[0]) {
                                                                echo 'selected';
                                                            }
                                                        } else {
                                                            if ($_SESSION['_fperficorp_'] == 'S') {
                                                                if ($grupo[0] == 1) {
                                                                    $grupoAtual = 1;
                                                                    echo 'selected';
                                                                }
                                                            }
                                                        }
                                                        ?> value="<?php echo $grupo[0]; ?>"><?php echo $grupo[1]; ?>
                                                    </option>
                                                    <?php
                                                }
                                                ?>
                                            </select>
                                            <input type="hidden" id="grupoDescricao" name="grupoDescricao" value="" />
                                        </td>
                                    </tr>                                
                                    <tr>
                                        <td class="textonormal" bgcolor="#DCEDF7">Órgão</td>
                                        <td class="textonormal">
                                            <select id="inputOrgao" name="tramitacaoOrgao" class="textonormal" style="width:100%;">
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
                                            <input type="hidden" id="orgaoDescricao" name="orgaoDescricao" value="" />
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="textonormal" bgcolor="#DCEDF7">Modalidade</td>
                                        <td class="textonormal">
                                            <select id="inputModalidade" name="inputModalidade" class="textonormal" width="100%">
                                                <option value="">Selecione a modalidade...</option>
                                                <?php
                                                $modalidades = getModalidades();

                                                foreach ($modalidades as $objmodalidade) {
                                                    ?>
                                                    <option <?php echo (!empty($modalidade) && $modalidade == $objmodalidade[0]) ? 'selected' : ''?> value="<?php echo $objmodalidade[0]; ?>"><?php echo $objmodalidade[1]; ?></option>
                                                    <?php
                                                }
                                                ?>
                                            </select>
                                            <input type="hidden" id="modalidadeDescricao" name="modalidadeDescricao" value="" />
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="textonormal" bgcolor="#DCEDF7">Comissão</td>
                                        <td class="textonormal">
                                            <select id="inputComissao" name="tramitacaoComissaoLicitacao" class="textonormal">
                                                <option value="">Selecione a comissão...</option>
                                                <?php
                                                if (!empty($grupoAtual)) {
                                                    $comissoes = getComissaoLicitacao($grupoAtual);

                                                    while ($comissao = $comissoes->fetchRow()) {
                                                        ?>
                                                        <option <?php echo (!empty($comissaoLicitacaoAtual) && $comissaoLicitacaoAtual == $comissao[0]) ? 'selected' : ''?> value="<?php echo $comissao[0]; ?>"><?php echo $comissao[1]; ?></option>
                                                        <?php
                                                    }
                                                }
                                                ?>
                                            </select>
                                            <input type="hidden" id="comissaoDescricao" name="comissaoDescricao" value="" />
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="textonormal" bgcolor="#DCEDF7">Processo Licitatório </td>
                                        <td class="textonormal">
                                            <input id="inputProcesso" type="text" value="<?php echo (!empty($processoNumeroAtual)) ? $processoNumeroAtual : ''; ?>" size="3" maxlength="4"  name="tramitacaoProcessoNumero" class="textonormal" /> /
                                            <input id="inputProcessoAno" type="text" value="<?php echo (!empty($processoAnoAtual)) ? $processoAnoAtual : ''; ?>" size="3" maxlength="4" name="tramitacaoProcessoAno" class="textonormal" />
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="textonormal" bgcolor="#DCEDF7">Período de Entrada do Protocolo </td>
                                        <td class="textonormal">
                                            <input id="inputEntradaInicio" type="text" maxlength="10" value="<?php echo (!empty($dataEntradaInicioAtual)) ? $dataEntradaInicioAtual : ''; ?>" name="tramitacaoDataEntradaInicio" class="textonormal" />
                                            <a href="javascript:janela('../calendario.php?Formulario=RelTramitacaoGerencial&Campo=tramitacaoDataEntradaInicio','Calendario',220,170,1,0)">
                                                <img src="../midia/calendario.gif" border="0" alt=""></a>&nbsp;a
                                            <input id="inputEntradaFim" type="text" maxlength="10" value="<?php echo (!empty($dataEntradaFimAtual)) ? $dataEntradaFimAtual : ''; ?>" name="tramitacaoDataEntradaFim" class="textonormal" />
                                            <a href="javascript:janela('../calendario.php?Formulario=RelTramitacaoGerencial&Campo=tramitacaoDataEntradaFim','Calendario',220,170,1,0)">
                                                <img src="../midia/calendario.gif" border="0" alt="">
                                            </a> 
                                        </td>
                                    </tr>  
                                    <tr>
                                        <td class="textonormal" bgcolor="#DCEDF7">Ação</td>
                                        <td class="textonormal">
                                            <select id="inputAcao" name="tramitacaoAcao" class="textonormal">
                                                <option value="">Selecione Ação..</option> 
                                                <?php
                                                if (!empty($grupoAtual)) {
                                                    $acoes = getAcoes($grupoAtual, null);

                                                    foreach ($acoes as $acao) {
                                                        ?>
                                                        <option <?php echo (isset($acaoAtual) && $acaoAtual == $acao[0]) ? 'selected' : ''?> value="<?php echo $acao[0]; ?>"><?php echo $acao[1]; ?></option>
                                                        <?php
                                                    }
                                                }
                                                ?>
                                            </select>
                                            <input type="hidden" id="acaoDescricao" name="acaoDescricao" value="" />
                                        </td>
                                    </tr>  
                                    <tr>
                                        <td class="textonormal" bgcolor="#DCEDF7">Situação dos Processos Licitatórios</td>
                                        <td class="textonormal">
                                            <select name="tramitacaoSituacao" class="textonormal">
                                                <option <?php echo ($situacaoAtual == 'andamento') ? 'selected' : ''; ?> value="andamento">Em Andamento</option>    
                                                <option <?php echo ($situacaoAtual == 'todas') ? 'selected' : ''; ?> value="todas">Todas</option> 
                                                <option <?php echo ($situacaoAtual == 'concluidas') ? 'selected' : ''; ?> value="concluidas">Concluidas</option>                                            
                                            </select>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td class="textonormal" align="right" colspan="<?php echo $totalAcoes ?>">
                                <input type="button" value="Pesquisar" onclick="javascript:enviar('Pesquisar');" class="botao">
                                <input type="button" value="Limpar" onclick="javascript:enviar('Limpar');" class="botao">
                                <input type="hidden" name="codModalidade"  value="">
                                <input type="hidden" name="Botao" value="" />
                            </td>
                        </tr>
                        <?php
                        if (!empty($arrModalidades)) { 
                            echo '
                                <tr>
                                    <td rowspan="2" class="titulo3" bgcolor="#DCEDF7" class="textonormal" align="center">MODALIDADE</td>
                                    <td colspan="'.($totalAcoes-1).'" class="titulo3" bgcolor="#DCEDF7" class="textonormal" align="center">MÉDIA EM DIAS POR AÇÃO</td>
                                </tr>
                                <tr>';
                                    echo $htmlAcoesTit;
                                    echo '  <td class="titulo3" bgcolor="#DCEDF7" class="textonormal" align="center">PRAZO<br>PREVISTO</td>
                                            <td class="titulo3" bgcolor="#DCEDF7" class="textonormal" align="center">PRAZO<br>REALIZADO</td>
                                            <td class="titulo3" bgcolor="#DCEDF7" class="textonormal" align="center">ATRASO</td>
                                </tr>';

                                foreach ($arrModalidades as $value) {
                                    $mediaRealizado = 0;
                                    $mediaPrevisto = 0;
                                    $htmlAcoesDados = '';

                                    foreach ($arrAcoes as $objAcao) {
                                        $arrMedia = getMediaDiasAcao($objAcao[1], $value[1], $dados);

                                        if (!is_int($arrMedia[0])) {
                                            $media = (int)$arrMedia[0];
                                        } else {
                                            $media = $arrMedia[0];
                                        }

                                        $mediaRealizado = $mediaRealizado + $arrMedia[0];
                                        $mediaPrevisto = $mediaPrevisto + $arrMedia[1];

                                        if ($media <= 0) {
                                            $media = '-';
                                        }

                                        $htmlAcoesDados .= '<td align="center">'.$media .'</td>';
                                    }
                                    ?>
                                    <tr>    
                                        <td align="center"><a href="javascript: enviaForm(<?php echo $value[1] ?>)"><?php echo $value[0]; //modalidade ?></a></td>
                                            <?php echo $htmlAcoesDados; ?>
                                        <td align="center">
                                            <?php
                                            if (!is_int($mediaPrevisto)) {
                                                echo (int)$mediaPrevisto;
                                            } else {
                                                echo $mediaPrevisto ;
                                            }
                                            ?>
                                        </td>
                                        <td align="center">
                                            <?php
                                            if (!is_int($mediaRealizado)) {
                                                echo (int)$mediaRealizado;
                                            } else {
                                                echo $mediaRealizado ;
                                            }
                                            ?>
                                        </td>
                                        <td align="center">
                                            <?php 
                                            $atraso = $mediaRealizado - $mediaPrevisto;

                                            if ($atraso > 0) {
                                                if (!is_int($atraso)) {
                                                    echo (int)$atraso;
                                                } else {
                                                    echo $atraso ;
                                                }
                                            } else {
                                                echo '0';
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                    <?php
                                }
                                ?>
                                <tr>
                                    <td colspan="<?php echo $totalAcoes ?>" class="textonormal" align="right">
                                        <input type="button" value="Gerar Planilha" onclick="javascript:enviar('Planilha');" class="botao">
                                        <input type="button" value="Gerar Gráfico" onclick="javascript:enviar('Grafico');" class="botao">
                                        <input type="button" value="Imprimir" onclick="javascript:enviar('Imprimir');" class="botao">
                                    </td>
                                </tr>
                            </td>
                        </tr>
                        <?php
                    } elseif (empty($dados) && $botao == 'Pesquisar') {
                        ?>
                        <tr>
                            <td>
                                Nenhuma Ocorrência Encontrada
                            </td>
                        </tr>
                        <?php
                    }
                    ?>
                    </table>
                </td>            
            </tr>
            <!-- Fim do Corpo -->
        </table>
    </form>
</body>
</html>
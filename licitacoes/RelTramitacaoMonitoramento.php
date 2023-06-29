<?php
/**
 * Portal de Compras
 * 
 * Programa: RelTramitacaoMonitoramento.php
 * Autor:    Caio Coutinho
 * Data:     03/08/2018
 * Objetivo: Tarefa Redmine 199438
 * ---------------------------------------------------------------------------------------
 * Alterado: Lucas Baracho
 * Data:     17/05/2019
 * Objetivo: Tarefa Redmine 216897
 * ---------------------------------------------------------------------------------------
 * Alterado: Lucas Baracho
 * Data:     24/05/2019
 * Objetivo: Tarefa Redmine 217487
 * ---------------------------------------------------------------------------------------
 * Alterado: João Madson
 * Data:     20/11/2019 
 * Objetivo: Tarefa Redmine 225660
 * ---------------------------------------------------------------------------------------
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
    $numeroProtocoloAtual    = $_POST['tramitacaoNumeroProtocolo'];
    $anoProtocoloAtual       = $_POST['tramitacaoAnoProtocolo'];
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
    $processoNumeroAtual    = $_POST['tramitacaoProcessoNumero'];
    $processoAnoAtual       = $_POST['tramitacaoProcessoAno'];
    $dataEntradaInicioAtual = $_POST['tramitacaoDataEntradaInicio'];
    $dataEntradaFimAtual    = $_POST['tramitacaoDataEntradaFim'];
    $situacaoAtual          = $_POST['tramitacaoSituacao'];
    $ordemAtual             = $_POST['tramitacaoOrdem'];
    $atrasoAtual            = $_POST['tramitacaoAtraso'];

    
} else {
    $Critica  = $_GET['Critica'];
    $botao                   = $_GET['Botao'];
    $numeroProtocoloAtual    = $_GET['tramitacaoNumeroProtocolo'];
    $anoProtocoloAtual       = $_GET['tramitacaoAnoProtocolo'];
    $grupoAtual              = $_GET['tramitacaoGrupo'];
    $orgaoAtual              = $_GET['tramitacaoOrgao'];
    $objetoAtual             = strtoupper2($_GET['tramitacaoObjeto']);
    $numeroCIAtual           = strtoupper2($_GET['tramitacaoNumeroCI']);
    $numeroOficioAtual       = strtoupper2($_GET['tramitacaoNumeroOficio']);
    $numeroSccAtual          = $_GET['tramitacaoNumeroScc'];
    $comissaoLicitacaoAtual  = $_GET['tramitacaoComissaoLicitacao'];
    $acaoAtual  = $_POST['tramitacaoAcao'];
    $agenteAtual  = $_POST['tramitacaoAgenteDestino'];
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
$ErroPrograma = "RelTramitacaoMonitoramento.php";

$parametrosGerais = dadosParametrosGerais();
$tamanhoObjeto = $parametrosGerais[0];
$tamanhoJustificativa = $parametrosGerais[1];
$tamanhoDescricaoServico = strlen($parametrosGerais[2]);
$subElementosEspeciais = explode(',', $parametrosGerais[3]);
$tamanhoArquivo = $parametrosGerais[4];
$tamanhoNomeArquivo = $parametrosGerais[5];
$extensoesArquivo = $parametrosGerais[6];

if($botao == 'Pesquisar' || $botao == 'Planilha' || $botao == 'xls') {
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
        'anoProtocolo'   => $anoProtocoloAtual
    );

    if(!empty($buscar['protocolo'])){
        if(!SoNumeros($buscar['protocolo'])) {
            adicionarMensagem("<a href='javascript:document.getElementById(\"inputProtocolo\").focus();' class='titulo2'>Número do protocolo deve ser numérico</a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
            $validar = false;
        }
    }
    if(!empty($buscar['anoProtocolo'])){
        if(!SoNumeros($buscar['anoProtocolo'])) {
            adicionarMensagem("<a href='javascript:document.getElementById(\"inputProtocoloAno\").focus();' class='titulo2'>Ano do protocolo deve ser numérico</a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
            $validar = false;
        }
    }


    if(!empty($buscar['processoNumero'])){
        if(!SoNumeros($buscar['processoNumero'])) {
            adicionarMensagem("<a href='javascript:document.getElementById(\"inputProtocolo\").focus();' class='titulo2'>Número do processo deve ser numérico</a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
            $validar = false;
        }
    }
    if(!empty($buscar['processoAno'])){
        if(!SoNumeros($buscar['processoAno'])) {
            adicionarMensagem("<a href='javascript:document.getElementById(\"inputProtocoloAno\").focus();' class='titulo2'>Ano do processo deve ser numérico</a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
            $validar = false;
        }
    }

    if(!empty($buscar['dataInicio']) && ValidaData($buscar['dataInicio'])) {
        adicionarMensagem("<a href='javascript:document.getElementById(\"inputEntrada\").focus();' class='titulo2'>Data de Inicio</a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
        $validar = false;
    }

    if(!empty($buscar['dataFim']) && ValidaData($buscar['dataFim'])) {
        adicionarMensagem("<a href='javascript:document.getElementById(\"inputFim\").focus();' class='titulo2'>Data Fim</a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
        $validar = false;
    }

    if($validar) {
        $dados = protocoloPesquisar($buscar, 'relMonitoramento');


        // Adicionar último passo de cada protocolo
        if(!empty($dados)) {

            $_REQUEST['rotina'] = 'Monitoramento';
            $_SESSION['origemPesquisa'] = $_REQUEST;

            foreach($dados as $key => $value) {  
                
               
                $passos_ = getTramitacaoPassos($value[0]);
                            
                if($passos_[0][3]){
                    //código novo de atraso
                    $saida = substr($passos_[0][5],0,10);

                    $arrEntrada = explode("-",substr($passos_[0][3],0,10));

                    $dataHoraEntrada = $arrEntrada[2]."/".$arrEntrada[1]."/".$arrEntrada[0];

                    $previsto = calcularTramitacaoSaida($dataHoraEntrada, $passos_[0][4]);
                    $arrPrevisto = explode("/",$previsto);
                    $dataPrevista = $arrPrevisto[2]."-".$arrPrevisto[1]."-".$arrPrevisto[0];                                        
                   
                    if($saida){   
                       
                        if(strtotime($saida) > strtotime($dataPrevista)) { 
                            
                            $diffDias = calcularTramitacaoDiasUteisAtraso($dataPrevista, $saida);
                            //$diffDias = calculaDias2($dataPrevista, $saida);

                            $exibirAtrasados = true;
                            $dia = ($diffDias > 1) ? ' dias' : ' dia';
                            $passos_[0]['atraso'] = $diffDias. $dia;
                            
                        } else {
                            $passos_[0]['atraso'] = ' - ';
                        }

                    }else{
                        $atual = date('Y-m-d');
                        
                        if(strtotime($atual) > strtotime($dataPrevista)) { 
                            
                            $diffDias = calcularTramitacaoDiasUteisAtraso($dataPrevista, $atual);
                            //$diffDias = calculaDias2($dataPrevista, $atual); 

                            $exibirAtrasados = true;
                            $dia = ($diffDias > 1) ? ' dias' : ' dia';
                            $passos_[0]['atraso'] = $diffDias. $dia;;
                        } else {
                            $passos_[0]['atraso'] = ' - ';
                        }
                    }
                    

                    $dados[$key]['ultimo_passo'] = $passos_[0];
                    
                    // Remover os não atrasados
                    if(!empty($atrasoAtual) && $atrasoAtual == 'S' && strpos($passos_[0]['atraso'], 'dia') === false) {
                        unset($dados[$key]);
                    }
                }
            }
        



        }

        if($botao == 'Planilha'){
            gerarCsv($dados);
        }

        if($botao == 'xls'){
            gerarXls($dados);
        }


    }
} else if($botao == 'Imprimir') {
    $validar = true;
    $buscar = array(
        'grupo'          => $grupoAtual,
        'grupoDesc' => $grupoDescricao,
        'orgao'          => $orgaoAtual,
        'orgaoDesc' => $orgaoDescricao,
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
        'anoProtocolo'   => $anoProtocoloAtual
    );

    /*if(!empty($numeroAnoAtual)) {
        $protocolo = explode('/', $numeroAnoAtual);
        $buscar['protocolo'] = $protocolo[0];
        $buscar['protocoloAno'] = $protocolo[1];
    }*/

    if(!empty($buscar['dataInicio']) && ValidaData($buscar['dataInicio'])) {
        adicionarMensagem("<a href='javascript:document.getElementById(\"inputEntrada\").focus();' class='titulo2'>Data de Inicio</a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
        $validar = false;
    }

    if(!empty($buscar['dataFim']) && ValidaData($buscar['dataFim'])) {
        adicionarMensagem("<a href='javascript:document.getElementById(\"inputFim\").focus();' class='titulo2'>Data Fim</a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
        $validar = false;
    }

    if(!empty($buscar['processoNumero'])){
        if(!SoNumeros($buscar['processoNumero'])) {
            adicionarMensagem("<a href='javascript:document.getElementById(\"inputProtocolo\").focus();' class='titulo2'>Número do processo deve ser numérico</a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
            $validar = false;
        }
    }
    if(!empty($buscar['processoAno'])){
        if(!SoNumeros($buscar['processoAno'])) {
            adicionarMensagem("<a href='javascript:document.getElementById(\"inputProtocoloAno\").focus();' class='titulo2'>Ano do processo deve ser numérico</a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
            $validar = false;
        }
    }


    if($validar) {
        $_SESSION['tramitacaoProtocolo'] = $buscar;
        header('Location: RelTramitacaoMonitoramentoPdf.php');
        exit();
    }

} else if($botao == 'Limpar') {
    header('Location: RelTramitacaoMonitoramento.php');
} 



?>
<html>
<?php
# Carrega o layout padrão #
layout();
?>
<link rel="stylesheet" type="text/css" href="../estilo.css?v=<?php echo mt_rand(); ?>">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="javascript" src="../import/jquery/jquery-1.7.2.min.js" type="text/javascript"></script>
<script language="javascript" src="../import/jquery/jquery.maskmoney.js" type="text/javascript"></script>
<script language="javascript" src="../import/jquery/jquery.maskedinput.js" type="text/javascript"></script>
<script language="javascript" type="">
    <?php MenuAcesso(); ?>

    function enviar(valor){
        preencherDados();
        document.RelTramitacaoMonitoramento.Botao.value=valor;
        if(valor == 'xls' || valor == 'Planilha'){
            modal.style.display = "none";
        }
        document.RelTramitacaoMonitoramento.submit();
    }

    function preencherDados(){

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
            document.RelTramitacaoMonitoramento.Botao.value = acao;
            document.RelTramitacaoMonitoramento.submit();
        });
        
        //$('#numeroAno').mask('9999/9999');  
        $('#numeroScc').mask('9999.9999/9999');  
        $('#inputEntradaInicio').mask('99/99/9999');
        $('#inputEntradaFim').mask('99/99/9999');
    });
</script>
<script language="JavaScript">Init();</script>
<form action="RelTramitacaoMonitoramento.php" method="POST" name="RelTramitacaoMonitoramento" enctype="multipart/form-data" >
    <br><br><br><br><br>
    <table cellpadding="3" border="0" summary="">
        <!-- Caminho -->
        <tr>
            <td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
            <td align="left" class="textonormal" colspan="2">
                <font class="titulo2">|</font>
                <a href="../index.php"><font color="#000000">Página Principal</font></a> > Licitações > Tramitação > Relatórios > Relatório de Monitoramento
            </td>
        </tr>
        <!-- Fim do Caminho-->

        <!-- Erro -->
        <?php if ( $Mens == 1 ) {?>
            <tr>
                <td width="150"></td>
                <td align="left" colspan="2"><?php ExibeMens($Mensagem,$Tipo,1); ?></td>
            </tr>
        <?php } ?>
        <!-- Fim do Erro -->

        <!-- Corpo -->
        <tr>
            <td width="150"></td>
            <td class="textonormal" >
                <table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal"  bgcolor="#FFFFFF">
                    <tr>
                        <td align="center" colspan="25" bgcolor="#75ADE6" valign="middle" class="titulo3">
                            RELATÓRIO DE MONITORAMENTO DA TRAMITAÇÃO
                        </td>
                    </tr>                    
                    <tr>
                        <td colspan="25">
                            <table border="0" summary="">
                                <tr>
                                    <td class="textonormal" bgcolor="#DCEDF7">Número/Ano <br /> Protocolo do Processo Licitatório </td>
                                    <td class="textonormal">
                                        <!--<input type="text" id="numeroAno" value="<?php echo (!empty($numeroAnoAtual)) ? $numeroAnoAtual : ''; ?>" name="tramitacaoNumeroAno" class="textonormal" />-->
                                        <input id="inputProtocolo" size="3" maxlength="4" type="text" value="<?php echo !empty($numeroProtocoloAtual) ? $numeroProtocoloAtual : ''; ?>" name="tramitacaoNumeroProtocolo" class="textonormal" />
                                        &nbsp;&nbsp;/&nbsp;&nbsp;
                                        <input id="inputProtocoloAno" size="3" maxlength="4" type="text" value="<?php echo !empty($anoProtocoloAtual) ? $anoProtocoloAtual : ''; ?>" name="tramitacaoAnoProtocolo" class="textonormal" />
                                    </td>
                                </tr>
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
                                                    <option <?php 

                                                if(isset($grupoAtual)){
                                                    if( $grupoAtual == $grupo[0]){
                                                        echo 'selected';
                                                    }
                                                }else{
                                                    if($_SESSION['_fperficorp_'] == 'S'){
                                                        if( $grupo[0] == 1 ){
                                                            $grupoAtual = 1;
                                                            echo 'selected';
                                                        }
                                                    }
                                                }
                                                //echo (isset($grupoAtual) && $grupoAtual == $grupo[0]) ? 'selected' : ''
                                                
                                                ?> value="<?php echo $grupo[0]; ?>"><?php echo $grupo[1]; ?></option>
                                            <?php
                                                }
                                            ?>
                                        </select>
                                        <input type="hidden" id="grupoDescricao" name="grupoDescricao" value="" />
                                    </td>
                                </tr>                                
                                <tr>
                                    <td class="textonormal" bgcolor="#DCEDF7">Órgão Demandante </td>
                                    <td class="textonormal">
                                        <select id="inputOrgao" name="tramitacaoOrgao" class="textonormal" style="width:100%;">
                                            <option value="">Selecione o órgão...</option>
                                            <?php
                                            if(!empty($grupoAtual)) {
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
                                    <td class="textonormal" bgcolor="#DCEDF7">Objeto </td>
                                    <td class="textonormal"><font class="textonormal">máximo de 400 caracteres</font>
                                        <input type="text" id="NCaracteresObjeto" name="NCaracteresObjeto" readonly="" size="3" value="0" class="textonormal"><br>
                                        <textarea id="inputObjeto" name="tramitacaoObjeto"
                                                cols="50"
                                                rows="4"
                                                maxlength="400" 
                                                onkeyup="javascript:CaracteresObjeto(this,'NCaracteresObjeto')" onblur="javascript:CaracteresObjeto(this,'NCaracteresObjeto')"
                                                onselect="javascript:CaracteresObjeto(this,'NCaracteresObjeto')"
                                                class="textonormal"><?php echo (!empty($objetoAtual)) ? $objetoAtual : ''; ?></textarea>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="textonormal" bgcolor="#DCEDF7">Número CI </td>
                                    <td class="textonormal">
                                        <input type="text" id="numeroCi" value="<?php echo (!empty($numeroCIAtual)) ? $numeroCIAtual : ''; ?>" name="tramitacaoNumeroCI" class="textonormal" />
                                    </td>
                                </tr>
                                <tr>
                                    <td class="textonormal" bgcolor="#DCEDF7">Número Ofício </td>
                                    <td class="textonormal">
                                        <input type="text" id="numeroOf" value="<?php echo (!empty($numeroOficioAtual)) ? $numeroOficioAtual : ''; ?>" name="tramitacaoNumeroOficio" class="textonormal" />
                                    </td>
                                </tr>
                                <tr>
                                    <td class="textonormal" bgcolor="#DCEDF7">Número da SCC </td>
                                    <td class="textonormal">
                                    <input type="text" id="numeroScc" value="<?php echo (!empty($numeroSccAtual)) ? $numeroSccAtual : ''; ?>" name="tramitacaoNumeroScc" class="textonormal" />
                                    </td>
                                </tr>
                                <tr>
                                    <td class="textonormal" bgcolor="#DCEDF7">Comissão de Licitação</td>
                                    <td class="textonormal">
                                        <select id="inputComissao" name="tramitacaoComissaoLicitacao" class="textonormal">
                                            <option value="">Selecione a comissão de licitação...</option>
                                            <?php
                                            if(!empty($grupoAtual)) {
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
                                    <td class="textonormal" bgcolor="#DCEDF7">Ação</td>
                                    <td class="textonormal">
                                        <select id="inputAcao" name="tramitacaoAcao" class="textonormal">
                                            <option value="">Selecione Ação..</option> 
                                            <?php
                                            
                                            if(!empty($grupoAtual)) {
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
                                    <td class="textonormal" bgcolor="#DCEDF7">Agente Destino</td>
                                    <td class="textonormal">
                                        <select id="inputAgenteDestino" name="tramitacaoAgenteDestino" class="textonormal">
                                            <option value="">Selecione o Agente de Destino..</option>                                            

                                            <?php
                                            if(!empty($grupoAtual)) {
                                                $agentes = getAgente($grupoAtual);
                                                foreach ($agentes as $agente) {
                                                    ?>
                                                        <option <?php echo (isset($agenteAtual) && $agenteAtual == $agente[0]) ? 'selected' : ''?> value="<?php echo $agente[0]; ?>"><?php echo $agente[1]; ?></option>
                                                    <?php
                                                    }
                                                }
                                            
                                            ?>
                                        </select>

                                        <input type="hidden" id="agenteDescricao" name="agenteDescricao" value="" />
                                    </td>
                                </tr>                                
                                <tr>
                                    <td class="textonormal" bgcolor="#DCEDF7">Período de Entrada do Protocolo </td>
                                    <td class="textonormal">
                                        <input id="inputEntradaInicio" type="text" maxlength="10" value="<?php echo (!empty($dataEntradaInicioAtual)) ? $dataEntradaInicioAtual : ''; ?>" name="tramitacaoDataEntradaInicio" class="textonormal" />
                                        <a href="javascript:janela('../calendario.php?Formulario=RelTramitacaoMonitoramento&Campo=tramitacaoDataEntradaInicio','Calendario',220,170,1,0)">
                                            <img src="../midia/calendario.gif" border="0" alt=""></a>&nbsp;a
                                        <input id="inputEntradaFim" type="text" maxlength="10" value="<?php echo (!empty($dataEntradaFimAtual)) ? $dataEntradaFimAtual : ''; ?>" name="tramitacaoDataEntradaFim" class="textonormal" />
                                        <a href="javascript:janela('../calendario.php?Formulario=RelTramitacaoMonitoramento&Campo=tramitacaoDataEntradaFim','Calendario',220,170,1,0)">
                                            <img src="../midia/calendario.gif" border="0" alt="">
                                        </a> 
                                    </td>
                                </tr>    
                                <tr>
                                    <td class="textonormal" bgcolor="#DCEDF7">Situação dos Processos Licitatórios</td>
                                    <td class="textonormal">
                                        <select name="tramitacaoSituacao" class="textonormal">
                                            <option <?php echo ($situacaoAtual == 'todas') ? 'selected' : ''; ?> value="todas">Todas</option> 
                                            <option <?php echo ($situacaoAtual == 'andamento') ? 'selected' : ''; ?> value="andamento">Em Andamento</option>    
                                            <option <?php echo ($situacaoAtual == 'concluidas') ? 'selected' : ''; ?> value="concluidas">Concluidas</option>                                            
                                        </select>
                                    </td>
                                </tr>  
                                <tr>
                                    <td class="textonormal" bgcolor="#DCEDF7">Ordem de exibição</td>
                                    <td class="textonormal">
                                        <select name="tramitacaoOrdem" class="textonormal">
                                            <option <?php echo ($ordemAtual == 'numAnoDesc') ? 'selected' : ''; ?> value="numAnoDesc">Número / Ano do Protocolo (DESC)</option>    
                                            <option <?php echo ($ordemAtual == 'orgao') ? 'selected' : ''; ?> value="orgao">Órgão demandante (ASC)</option>                                   
                                        </select>
                                    </td>
                                </tr>   
                                <tr>
                                    <td class="textonormal" bgcolor="#DCEDF7">Só Ações em Atraso</td>
                                    <td class="textonormal">
                                        <input <?php echo (!empty($atrasoAtual) && $atrasoAtual == 'S') ? 'checked' : ''; ?> type="checkbox" name="tramitacaoAtraso" value="S">
                                    </td>
                                </tr>                                                                                                                                                  
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td class="textonormal" align="right" colspan="25">
                            <input type="button" value="Pesquisar" onclick="javascript:enviar('Pesquisar');" class="botao" style="float:left; margin-right:5px;  margin-left:2px">
                            <input type="button" value="Limpar" onclick="javascript:enviar('Limpar');" class="botao" style="float:left; ">
                            <input type="hidden" name="Botao" value="" />
                        </td>
                    </tr>
                    <?php if(!empty($dados)) { 
                        
 

                                 $TotalRegistros = 0;
                                foreach($dados as $value) { 
                                    

                                    if(!empty($value[8])){
                                        //APRESENTAR DADOS DO PROCESSO ASSOCIADO A SCC;
                                        
                                        
                                        $arrFase = getFaseLicitacaoScc($value[8]);
                                        $arrProcesso = getProcessoScc($value[8]);

                                        if(!empty($arrFase)){
                                            $arrFase = $arrFase[0];
                                        }

                                        if (!empty($buscar['situacao'])) {
                                            if ($buscar['situacao'] == 'concluidas') {

                                                $arraySituacoesConcluidas = getIdFasesConcluidas($db);

                                                if(!in_array($arrFase[2],$arraySituacoesConcluidas)) {
                                                    continue;
                                                }
                                            } elseif ($buscar['situacao'] == 'andamento') {
                                               
                                                $arraySituacoesEmAndamento = getIdFasesEmAndamento($db);
    
                                                if(!in_array($arrFase[2],$arraySituacoesEmAndamento)) {
                                                    continue;
                                                }
    
                                            }
                                        }

                                    }else{
                                        if (!empty($buscar['situacao']) ) {
                                            if ($buscar['situacao'] == 'concluidas') {

                                                $arraySituacoesConcluidas = getIdFasesConcluidas($db);

                                                if(!in_array($value[22],$arraySituacoesConcluidas)) {
                                                    continue;
                                                }
                                            } elseif ($buscar['situacao'] == 'andamento') {
                                               
                                                $arraySituacoesEmAndamento = getIdFasesEmAndamento($db);
    
                                                if(!in_array($value[22],$arraySituacoesEmAndamento)) {
                                                    continue;
                                                }
    
                                            }
                                        }
                                    }

                                    $TotalRegistros++;

                                    if( $TotalRegistros<=1 ){
                                    echo strtoupper2('
                                    <tr>
                                        <td width="25" rowspan="2" class="titulo3" bgcolor="#DCEDF7" class="textonormal" align="center">Número/Ano Protocolo do Processo</td>
                                        <td rowspan="2" class="titulo3" bgcolor="#DCEDF7" class="textonormal" align="center">Órgão Demandante</td>
                                        <td rowspan="2" class="titulo3" bgcolor="#DCEDF7" class="textonormal" align="center">Objeto</td>
                                        <td rowspan="2" class="titulo3" bgcolor="#DCEDF7" class="textonormal" align="center">Número CI</td>
                                        <td rowspan="2" class="titulo3" bgcolor="#DCEDF7" class="textonormal" align="center">Número Ofício</td>
                                        <td rowspan="2" class="titulo3" bgcolor="#DCEDF7" class="textonormal" align="center">Data Entrada Protocolo</td>
                                        <td rowspan="2" class="titulo3" bgcolor="#DCEDF7" class="textonormal" align="center">Monitoramento</td>
                                        <td width="75" rowspan="2" class="titulo3" bgcolor="#DCEDF7" class="textonormal" align="center">SCC</td>
                                        <td rowspan="2" class="titulo3" bgcolor="#DCEDF7" class="textonormal" align="center">Processo</td>
                                        <td rowspan="2" class="titulo3" bgcolor="#DCEDF7" class="textonormal" align="center">Última Fase</td>
                                        <td rowspan="2" class="titulo3" bgcolor="#DCEDF7" class="textonormal" align="center">Data</td>
                                        <td rowspan="2" class="titulo3" bgcolor="#DCEDF7" class="textonormal" align="center">Hora</td>
                                        <td colspan="7" class="titulo3" bgcolor="#DCEDF7" class="textonormal" align="center">Último Passo</td>
                                        <td colspan="6" class="titulo3" bgcolor="#DCEDF7" class="textonormal" align="center">Comparativo Valores Totais Processo Licitatório</td>
                                        </tr>
                                        <tr>
                                            <td class="titulo3" bgcolor="#DCEDF7" class="textonormal" align="center">Ação</td>
                                            <td class="titulo3" bgcolor="#DCEDF7" class="textonormal" align="center">Entrada</td>
                                            <td class="titulo3" bgcolor="#DCEDF7" class="textonormal" align="center">Saída</td>
                                            <td class="titulo3" bgcolor="#DCEDF7" class="textonormal" align="center">Atraso</td>
                                            <td class="titulo3" bgcolor="#DCEDF7" class="textonormal" align="center">Agente de Tramitaçao</td>
                                            <td class="titulo3" bgcolor="#DCEDF7" class="textonormal" align="center">Usuário Responsável</td>
                                            <td class="titulo3" bgcolor="#DCEDF7" class="textonormal" align="center">Observação</td>
                                            <td class="titulo3" bgcolor="#DCEDF7" class="textonormal" align="center">Entrada Protocolo</td>
                                            <td class="titulo3" bgcolor="#DCEDF7" class="textonormal" align="center">Estimado Licitação</td>
                                            <td class="titulo3" bgcolor="#DCEDF7" class="textonormal" align="center">Economicidade %</td>
                                            <td class="titulo3" bgcolor="#DCEDF7" class="textonormal" align="center">Estimado (Itens que Lograram Êxito)</td>
                                            <td class="titulo3" bgcolor="#DCEDF7" class="textonormal" align="center">Homologado (Itens que Lograram Êxito)</td>
                                            <td class="titulo3" bgcolor="#DCEDF7" class="textonormal" align="center">Economicidade %</td>
                                        </tr>
                                             ');
                                    }


                                    ?>

                                    
                                    <tr>
                                        <td align="center"><?php 
                                            echo '<a href="CadTramitacaoDetalhe.php?protsequ='.$value[0].'&numprotocolo='.$value[1].'&anoprotocolo='.$value[5].'&origemTramitacao=2">'.str_pad($value[1], 4, "0", STR_PAD_LEFT).'/'.$value[5]."</a>";
                                        ?></td>
                                        <td><?php echo $value[2]; ?></td>
                                        <td><?php echo $value[4]; ?></td>
                                        <td align="center"><?php echo (!empty($value[6])) ? $value[6] : ' - '; ?></td>
                                        <td align="center"><?php echo (!empty($value[7])) ? $value[7] : ' - '; ?></td>
                                        <td align="center"><?php echo DataBarra($value[3]); ?></td>
                                        <td><?php echo $value[23] ?></td>
                                        <td align="center"><?php 
                                            $processo = ''; 
                                            $fase = '';

                                            if(!empty($value[8])){ 
                                                echo "<a href='../compras/ConsAcompSolicitacaoCompra.php?SeqSolicitacao=".$value[8]."&origemTramitacao=2'>".getNumeroSolicitacaoCompra($db, $value[8])."</a>";
                                                 
                                                    //APRESENTAR DADOS DO PROCESSO ASSOCIADO A SCC;
                                                    
                                                    $arrFase = getFaseLicitacaoScc($value[8]);
                                                    $arrProcesso = getProcessoScc($value[8]);

                                                    if(!empty($arrFase)){
                                                        $arrFase = $arrFase[0];
                                                    }
                                                    if(!empty($arrProcesso)){
                                                        $arrProcesso = $arrProcesso[0];
                                                    }

                                                    ////($arrProcesso);
                                                
                                                    if(!empty($arrProcesso[0])) {
                                                        $processo = "<a href='ConsHistoricoDetalhes.php?LicitacaoProcessoDet=".str_pad($arrProcesso[0], 4, '0', STR_PAD_LEFT)."&LicitacaoAnoDet=".$arrProcesso[1]."&ComissaoCodigoDet=".$arrProcesso[2]."&GrupoCodigoDet=".$arrProcesso[3]."&OrgaoLicitanteCodigoDet=".$arrProcesso[4]."&origemTramitacao=2'>".str_pad($arrProcesso[0], 4, "0", STR_PAD_LEFT) . '/' . $arrProcesso[1]. ' - '. $arrProcesso[5]."</a>";
                                                        $fase = $arrFase[1];
                                                    }else{
                                                        $processo = "-";
                                                        $fase = "-";
                                                    }

                                            }else{
                                                echo ' - ';

                                                //verifica se existe processo cadastrado 
                                                if(!empty($value[17])) {
                                                    $processo = "<a href='ConsHistoricoDetalhes.php?LicitacaoProcessoDet=".str_pad($value[17], 4, '0', STR_PAD_LEFT)."&LicitacaoAnoDet=".$value[18]."&ComissaoCodigoDet=".$value[20]."&GrupoCodigoDet=".$value[19]."&OrgaoLicitanteCodigoDet=".$value[21]."&origemTramitacao=2'>".str_pad($value[17], 4, "0", STR_PAD_LEFT) . '/' . $value[18]. ' - '. $value[16]."</a>";
                                                    $fase = $value[15];
                                                }

                                            }
                                        
                                        ?></td>
                                        <?php
                                        if($value[24]){
                                            $LicitacaoDtAbertura = substr($value[24], 8, 2) . '/' . substr($value[24], 5, 2) . '/' . substr($value[24], 0, 4);
                                            $LicitacaoHoraAbertura = substr($value[24], 11, 5).'h';   
                                         } 

                                        ?>
                                        <td align="center"><?php echo (!empty($processo)) ? $processo : ' - '; ?></td>
                                        <td align="center"><?php echo (!empty($fase)) ? $fase : ' - '; ?></td>
                                        <td align="center"><?php echo (!empty($LicitacaoDtAbertura)) ? $LicitacaoDtAbertura : ' - '; ?></td>
                                        <td align="center"><?php echo (!empty($LicitacaoHoraAbertura)) ? $LicitacaoHoraAbertura : ' - '; ?></td>
                                        <td><?php echo $value['ultimo_passo'][2]; ?></td>
                                        <td align="center"><?php echo DataBarra($value['ultimo_passo'][3]); ?></td>
                                        <td align="center"><?php echo (!empty($value['ultimo_passo'][5])) ? DataBarra($value['ultimo_passo'][5]) : ' - '; ?></td>
                                        <td align="center"><font color="red"><?php echo $value['ultimo_passo']['atraso']; ?></font></td>
                                        <td class='apresentaHintAgente' id ='<?php echo $value['ultimo_passo'][18]; ?>'><?php echo strtoupper2($value['ultimo_passo'][0]); ?></td>
                                        <td>
                                        <?php 
                                        // usuario
                                        $usuarioDesc = '';
                                        if($value['ultimo_passo'][17]=='S'){
                                                                        
                                            if($value['ultimo_passo'][8] <= 0 ){
                                                $usuarioDesc = $value['ultimo_passo'][0];
                                            }else{
                                                $usuarioDesc = $value['ultimo_passo'][1];
                                            }
                                        }else{
                                            if($value['ultimo_passo'][8] <= 0){
                                                if($value['ultimo_passo'][9]=='I'){
                                                    $usuarioDesc = $value['ultimo_passo'][0];
                                                }else{
                                                    $usuarioDesc = 'ÓRGÃO EXTERNO';
                                                }
                                            }else{
                                                $usuarioDesc = $value['ultimo_passo'][1];
                                            }
                                        }
                                        echo strtoupper2($usuarioDesc);
                                        
                                        ?></td>
                                        <td><?php echo $value['ultimo_passo'][6]; ?></td>
                                        <td align="center"><?php echo 'R$ '. converte_valor_estoques2($value[9]); ?></td>
                                        <td align="center"><?php echo 'R$ '. converte_valor_estoques2($value[10]); ?></td>
                                        <?php 
                                            $diferenca_1 = floatval($value[9]) - floatval($value[10]);
                                            $economicidade_1 = ( ($diferenca_1 != 0) && ($value[9] != 0)) ? number_format(((($diferenca_1 * 100) / $value[9])), 2, ',', '.') : '0';
                                            if($value[10] <= 0){
                                                $economicidade_1 = '-';
                                            }else{
                                                $economicidade_1 = $economicidade_1 . ' %';
                                            }
                                        ?>
                                        <td align="center"><?php echo $economicidade_1 ; ?></td>
                                        <td align="center"><?php echo converte_valor_estoques2($value[11]) ?></td>
                                        <td align="center"><?php echo converte_valor_estoques2($value[12]) ?></td>
                                        <?php 
                                            $diferenca_2 = floatval($value[11]) - floatval($value[12]);
                                            $economicidade_2 = ($diferenca_2 != 0) ?number_format(((($diferenca_2 * 100) / $value[11])), 2, ',', '.') : '0';
                                        ?>
                                        <td align="center"><?php echo $economicidade_2 . ' %'; ?></td>
                                    </tr>
                                <?php } 

                                 if($TotalRegistros>0){
                                ?>
                                 
                                <tr>
                                    <td colspan="25" class="textonormal" align="right">
                                        <input type="button" value="Imprimir" onclick="javascript:enviar('Imprimir');" class="botao" style="float:left; margin-right:5px;  margin-left:2px">
                                        <!--<input type="button" value="Gerar Planilha" onclick="javascript:enviar('Planilha');" class="botao" style="float:left; float:left; margin-right:5px;">-->
                                        <input id="myBtn" type="button" value="Gerar Planilha" class="botao" style="float:left; float:left; margin-right:5px;">
                                        <input type="button" value="Limpar" onclick="javascript:enviar('Limpar');" class="botao" style="float:left; ">
                                    </td>
                                </tr>
                                <?php }else{ ?>
                                <tr>
                                    <td>
                                        Nenhuma Ocorrência Encontrada
                                    </td>
                                </tr>
                                <?php } ?>
                            
                        </td>
                    </tr>
                    <?php } elseif(empty($dados) && $botao == 'Pesquisar') { ?>
                    <tr>
                        <td>
                            Nenhuma Ocorrência Encontrada
                        </td>
                    </tr>
                    <?php } ?>
                </table>
            </td>            
        </tr>
        <!-- Fim do Corpo -->
    </table>
    <div id='hintAgente' class='hint textonormal' style='display: none;'>Usuários do Agente:</div>
    <!--MODAL   onclick="javascript:enviar('xls');"--> 

        <div id="myModal" class="modal">

         
        <!-- Modal content -->
        <div class="modal-content">
        <div class="modal-title"><span align="center">Gerar Planilha</span><span class="close" align="right" style="height:20px;width:20px;padding:0px;color:#75ade6;background-color:#fff;text-align:center;font-size: 20px;">&times;</span></div>   
            
            <table style="border: 1px solid #75ade6; margin:2px;">
                <tr>
                    <td class="textonormal" align="center" style="width:10%"> 
                        <input type="button" value="Gerar XLS" onclick="javascript:enviar('xls');" class="botao" style="float:left;  margin:10px; width: 100px; height: 50px;">
                    </td>
                    <td class="textonormal">
                        Para abrir no Microsoft Office Excel
                    </td>    
                </tr>
                <tr>
                    <td class="textonormal" align="center">
                        <input type="button" value="Gerar CSV" onclick="javascript:enviar('Planilha');" class="botao" style="float:left;margin:10px;width: 100px; height: 50px;">
                    </td>
                    <td class="textonormal">
                        Para abrir em outros programas 
                    </td>    
                </tr>
                <tr>
                    <td class="textonormal" colspan="2" style="margin:5px;border-top: 1px solid #75ade6;width:100%;">
                    Em caso de problemas ou se for exibida uma janela para Importação do Texto, verificar se estão selecionadas as seguintes opções:
                    Conjunto de caracteres: Unicode (UTF-8)
                    Idioma: Padrão - Português (Brasil)
                    Opções de separadores: Separado por Tabulação, Vírgula e Ponto-e-vírgula 
                    </td>
                </tr>

        </div>

        </div>

    <!--MODAL END-->
</form>
<?php 

$usuariosAgentes = getUsuariosAgentes(Conexao());
$usuariosPorAgente = array();
foreach($usuariosAgentes as $usuario){

    $usuariosPorAgente[$usuario[0]][] = $usuario[2];

}
?>
<script language="javascript" type="">

// Get the modal
var modal = document.getElementById('myModal');

// Get the button that opens the modal
var btn = document.getElementById("myBtn");

// Get the <span> element that closes the modal
var span = document.getElementsByClassName("close")[0];

// When the user clicks on the button, open the modal 
btn.onclick = function() {
  modal.style.display = "block";
}

// When the user clicks on <span> (x), close the modal
span.onclick = function() {
  modal.style.display = "none";
}

// When the user clicks anywhere outside of the modal, close it
window.onclick = function(event) {
  if (event.target == modal) {
    modal.style.display = "none";
  }
}

var usuariosAgentes = <?php echo json_encode($usuariosPorAgente) ?>;
if(usuariosAgentes){
  $( ".apresentaHintAgente" ).mouseover(function() {
      var e = e ||  window.event;
      
      text = "Usuários do Agente:<br>";
      var i;
      for (i = 0; i < usuariosAgentes[this.id].length; i++) { 
          if(usuariosAgentes[this.id][i] == null){
            text += "Nenhum usuário associado.";
          }else{
            text += "<b> - "+ usuariosAgentes[this.id][i] + "</b><br>";
          }
      }

      $('#hintAgente').css({'top':e.pageY-80,'left':e.pageX-400, 'padding':'5px', 'font-size': '12px'});
      $('#hintAgente').html(text);
      $('#hintAgente').show();

  }).mouseout(function() {

      $('#hintAgente').hide();

  });
}
</script>
</body>
</html>

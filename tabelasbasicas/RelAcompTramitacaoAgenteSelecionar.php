<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: RelTramitacaoMonitoramento.php
#-------------------------------------------------------------------------
# Alterado: Caio Coutinho
# Data:		14/08/2018
# Objetivo:	Tarefa Redmine 199439
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "./funcoesTramitacao.php";

# Acesso ao arquivo de funções #
require_once '../compras/funcoesCompras.php';

# Executa o controle de segurança #
session_start();
Seguranca();

$db = Conexao();

// TODO buscar por ação em atraso

$grupo        = $_SESSION['_cgrempcodi_'];
$agentes      = getAgentes($db, $grupo);
$responsaveis = array();

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD']  == "POST"){
    $botao                  = $_POST['Botao'];
    $agenteAtual            = $_POST['tramitacaoAgente'];
    $responsavelAtual       = $_POST['tramitacaoResponsavel'];
    $dataEntradaInicioAtual = $_POST['tramitacaoDataEntradaInicio'];
    $dataEntradaFimAtual    = $_POST['tramitacaoDataEntradaFim'];
    $situacaoAtual          = $_POST['tramitacaoSituacao'];
    $atrasoAtual            = $_POST['tramitacaoAtraso'];
} else {
    $Critica  = $_GET['Critica'];
    $Mensagem = urldecode($_GET['Mensagem']);
    $Mens     = $_GET['Mens'];
    $Tipo     = $_GET['Tipo'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "RelAcompTramitacaoAgenteSelecionar.php";

if($botao == 'Agente') {
    $agenteAtual = $_POST['tramitacaoAgente'];    
    $agenteSelecionado = getAgenteById($db, $agenteAtual);
    if(!empty($agenteAtual) && $agenteSelecionado[3] == 'I') {
        $responsaveis = getResponsaveisAgente($db, $agenteAtual);
    }    
} else if($botao == 'Pesquisar') {
    $validar = true;
    $protocolo = array();   
    
    $buscar = array(
        'agente'      => $agenteAtual,
        'responsavel' => $responsavelAtual,
        'dataInicio'  => $dataEntradaInicioAtual,
        'dataFim'     => $dataEntradaFimAtual,
        'situacao'    => $situacaoAtual,
        'atraso'      => $atrasoAtual
    );

    if(empty($buscar['agente'])) {
        adicionarMensagem("<a href='javascript:document.getElementById(\"inputAgente\").focus();' class='titulo2'>Agente de Tramitação</a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
        $validar = false;
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
        $dados = protocoloPesquisarAgentes($db, $buscar);

        // Adicionar último passo de cada protocolo
        if(!empty($dados)) {
            foreach($dados as $key => $value) {                
                $atual = date('d/m/Y');
                $passos_ = getTramitacaoPassos($value[0]);
                $passos_[0][3] = substr($passos_[0][3], 0, 10);
                $passos_[0][5] = !empty($passos_[0][5]) ? substr($passos_[0][5], 0, 10) : '';
                $calcSaida = calcularTramitacaoSaida(DataBarra($passos_[0][3]), $passos_[0][4]);       

                if (empty($passos_[0][5])) { // Saída não realizada
                    $diffDias = calculaDias($calcSaida, $atual);                    
                    if(DataInvertida($atual) <= DataInvertida($calcSaida)) {
                        $passos_[0]['atraso'] = ' - ';
                    } else {
                        $exibirAtrasados = true;
                        $dia = ($diffDias > 1) ? ' dias' : ' dia';
                        $passos_[0]['atraso'] = $diffDias . $dia;
                    }
                } else { // Saída ralizada
                    $diffDias = calculaDias(DataBarra($passos_[0][5]), $calcSaida);
                    if($passos_[0][5] <= DataInvertida($calcSaida)) {
                        $passos_[0]['atraso'] = ' - ';
                    } else {
                        $exibirAtrasados = true;
                        $dia = ($diffDias > 1) ? ' dias' : ' dia';
                        $passos_[0]['atraso'] = $diffDias . $dia;
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

    // Responsáveis
    $agenteAtual       = $_POST['tramitacaoAgente'];    
    $agenteSelecionado = getAgenteById($db, $agenteAtual);
    if(!empty($agenteAtual) && $agenteSelecionado[3] == 'I') {
        $responsaveis = getResponsaveisAgente($db, $agenteAtual);
    }    
} else if($botao == 'Imprimir') {
    $validar = true;
    $protocolo = array();   
    
    $buscar = array(
        'agente'      => $agenteAtual,
        'responsavel' => $responsavelAtual,
        'dataInicio'  => $dataEntradaInicioAtual,
        'dataFim'     => $dataEntradaFimAtual,
        'situacao'    => $situacaoAtual,
        'atraso'      => $atrasoAtual
    );

    if(empty($buscar['agente'])) {
        adicionarMensagem("<a href='javascript:document.getElementById(\"inputAgente\").focus();' class='titulo2'>Agente de Tramitação</a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
        $validar = false;
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
        $_SESSION['relTramitacaoMonitoramento'] = $buscar;
        header('Location: RelAcompTramitacaoAgentePdf.php');
        exit();
    }

} else if($botao == 'Limpar') {
    header('Location: RelAcompTramitacaoAgenteSelecionar.php');
}

?>
<html>
<?
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

    function enviar(valor){

        document.RelAcompTramitacaoAgenteSelecionar.Botao.value=valor;
        document.RelAcompTramitacaoAgenteSelecionar.submit();
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
            document.RelAcompTramitacaoAgenteSelecionar.Botao.value = acao;
            document.RelAcompTramitacaoAgenteSelecionar.submit();
        });
 
        $('#inputEntradaInicio').mask('99/99/9999');
        $('#inputEntradaFim').mask('99/99/9999');
    });
</script>
<script language="JavaScript">Init();</script>
<form action="RelAcompTramitacaoAgenteSelecionar.php" method="POST" name="RelAcompTramitacaoAgenteSelecionar">
    <br><br><br><br><br>
    <table cellpadding="3" border="0" summary="">
        <!-- Caminho -->
        <tr>
            <td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
            <td align="left" class="textonormal" colspan="2">
                <font class="titulo2">|</font>
                <a href="../index.php"><font color="#000000">Página Principal</font></a> > Tabelas > Licitações > Agente > Relatório de Acompanhamento de Tramitação por Agente 
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
            <td class="textonormal">
                <table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal"  bgcolor="#FFFFFF">
                    <tr>
                        <td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
                            RELATÓRIO DE ACOMPANHAMENTO DE TRAMITAÇÃO POR AGENTE
                        </td>
                    </tr>
                    <tr>
                        <td class="textonormal">
                            <p align="justify">
                                Preencha os dados abaixo e clique no botão 'Pesquisar'.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <table border="0" summary="">                                
                                <tr>
                                    <td class="textonormal" bgcolor="#DCEDF7">Agente de Tramitação*</td>
                                    <td class="textonormal">
                                        <select id="inputAgente" name="tramitacaoAgente" class="textonormal" onclick="javascript:enviar('Agente');">
                                            <option value="">Selecione o Agente de Tramitação..</option>                                            
                                            <?php foreach($agentes as $value) { ?>
                                                <option <?php echo ($agenteAtual == $value[0] ? 'selected' : '');?> value="<?php echo $value[0]; ?>"><?php echo $value[1]; ?></option>                                            
                                            <?php } ?>
                                        </select>
                                    </td>
                                </tr>
                                <?php if(!empty($responsaveis)) { ?>
                                <tr>
                                    <td class="textonormal" bgcolor="#DCEDF7">Responsável</td>
                                    <td class="textonormal">
                                        <select id="inputResponsavel" name="tramitacaoResponsavel" class="textonormal">
                                            <option value="">Selecione o Responsável..</option>     
                                            <?php foreach($responsaveis as $value) { ?>
                                                <option <?php echo ($responsavelAtual == $value[1] ? 'selected' : '');?> value="<?php echo $value[1]; ?>"><?php echo $value[2]; ?></option>                                            
                                            <?php } ?>                                       
                                        </select>
                                    </td>
                                </tr>   
                                    <?php } ?>                             
                                <tr>
                                    <td class="textonormal" bgcolor="#DCEDF7">Data de Entrada </td>
                                    <td class="textonormal">
                                        <input id="inputEntradaInicio" type="text" maxlength="10" value="<?php echo (!empty($dataEntradaInicioAtual)) ? $dataEntradaInicioAtual : ''; ?>" name="tramitacaoDataEntradaInicio" class="textonormal" />
                                        <a href="javascript:janela('../calendario.php?Formulario=RelAcompTramitacaoAgenteSelecionar&Campo=tramitacaoDataEntradaInicio','Calendario',220,170,1,0)">
                                            <img src="../midia/calendario.gif" border="0" alt="">
                                        </a>
                                        A
                                        <input id="inputEntradaFim" type="text" maxlength="10" value="<?php echo (!empty($dataEntradaFimAtual)) ? $dataEntradaFimAtual : ''; ?>" name="tramitacaoDataEntradaFim" class="textonormal" />
                                        <a href="javascript:janela('../calendario.php?Formulario=RelAcompTramitacaoAgenteSelecionar&Campo=tramitacaoDataEntradaFim','Calendario',220,170,1,0)">
                                            <img src="../midia/calendario.gif" border="0" alt="">
                                        </a> 
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
                        <td class="textonormal" align="right">
                            <input type="button" value="Pesquisar" onclick="javascript:enviar('Pesquisar');" class="botao">
                            <input type="hidden" name="Botao" value="" />
                        </td>
                    </tr>
                    <?php if(!empty($dados)) { ?>
                    <tr>
                        <td>
                            <table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" bgcolor="#FFFFFF">
                                <tr>                                                                        
                                    <td rowspan="2" class="titulo3" bgcolor="#DCEDF7" class="textonormal" align="center">Agente de Tramitação</td>
                                    <td rowspan="2" class="titulo3" bgcolor="#DCEDF7" class="textonormal" align="center">Responsável</td>
                                    <td rowspan="2" class="titulo3" bgcolor="#DCEDF7" class="textonormal" align="center">Número do Protocolo</td>
                                    <td colspan="5" class="titulo3" bgcolor="#DCEDF7" class="textonormal" align="center">Último Passo</td>
                                </tr>
                                <tr>
                                    <td class="titulo3" bgcolor="#DCEDF7" class="textonormal" align="center">Ação</td>
                                    <td class="titulo3" bgcolor="#DCEDF7" class="textonormal" align="center">Entrada</td>
                                    <td class="titulo3" bgcolor="#DCEDF7" class="textonormal" align="center">Saída</td>
                                    <td class="titulo3" bgcolor="#DCEDF7" class="textonormal" align="center">Atraso</td>
                                    <td class="titulo3" bgcolor="#DCEDF7" class="textonormal" align="center">Observação</td>
                                </tr>
                                <?php foreach($dados as $value) { ?>
                                    <tr>
                                        <td align="center"><?php echo $value[1]; ?></td>
                                        <td><?php echo $value[2]; ?></td>
                                        <td align="center">
                                            <a href="../licitacoes/CadTramitacaoDetalhe.php?protsequ=<?php echo $value[0]; ?>&numprotocolo=<?php echo $value[3]; ?>&anoprotocolo=<?php echo $value[4]; ?>">
                                                <?php echo str_pad($value[3], 4, "0", STR_PAD_LEFT).'/'.$value[4]; ?>
                                            </a>
                                        </td>
                                        <td><?php echo $value['ultimo_passo'][2]; ?></td>
                                        <td><?php echo DataBarra($value['ultimo_passo'][3]); ?></td>
                                        <td align="center"><?php echo (!empty($value['ultimo_passo'][5])) ? DataBarra($value['ultimo_passo'][5]) : ' - '; ?></td>
                                        <td align="center"><?php echo $value['ultimo_passo']['atraso']; ?></td>                                        
                                        <td><?php echo $value['ultimo_passo'][6]; ?></td>                                        
                                    </tr>
                                <?php } ?>
                                <tr>
                                    <td colspan="22" class="textonormal" align="right">
                                        <input type="button" value="Imprimir" onclick="javascript:enviar('Imprimir');" class="botao">
                                        <input type="button" value="Limpar" onclick="javascript:enviar('Limpar');" class="botao">
                                    </td>
                                </tr>
                            </table>
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
</form>
</body>
</html>

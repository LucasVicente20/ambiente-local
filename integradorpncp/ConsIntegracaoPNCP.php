<?php
/**
 * Portal de Compras
 * Programa: ConsIntegracaoPNCP.php
 * Autor: José Rodrigo
 * Data: 09/02/2023
 * Objetivo: Programa para integrar manualmente os microsserviços
 * Tarefa Redmine: CR275895
 * -------------------------------------------------------------------
 * Alterado:    
 * Data:        
 * Tarefa:      
 * -------------------------------------------------------------------
 */

# Acesso ao arquivo de funções #
require_once '../funcoes.php';
include "../app/export/ExportaCSV.php";
include "../app/export/ExportaXLS.php";

# Executa o controle de segurança	#
session_start();
Seguranca();

if ($_SERVER['REQUEST_METHOD'] == "GET") {
    unset($_SESSION['item']);
}

if ($_SERVER['REQUEST_METHOD'] == "POST") {

    $_SESSION['dadosDaPagina']['selectAnoPCA'] = $_POST['selectAnoPCA'];
    $_SESSION['dadosDaPagina']['selectAeaReq'] = $_POST['selectAreaReq'];
    $_SESSION['dadosDaPagina']['descSuDemanda'] = $_POST['descSuDemanda'];
    $_SESSION['dadosDaPagina']['justContratacao'] = $_POST['justContratacao'];
    $_SESSION['dadosDaPagina']['estValorContratacao'] = $_POST['estValorContratacao'];
    $_SESSION['dadosDaPagina']['tpProcContratacao'] = $_POST['tpProcContratacao'];
    $_SESSION['dadosDaPagina']['dtPretendidaConc'] = $_POST['dtPretendidaConc'];
    $_SESSION['dadosDaPagina']['grauPrioridade'] = $_POST['grauPrioridade'];
    $_SESSION['dadosDaPagina']['justPriAlta'] = $_POST['justPriAlta'];
    $_SESSION['dadosDaPagina']['contratCorp'] = $_POST['contratCorp'];

    if (!empty($_SESSION['item']) && ($_SESSION['dadosDaPagina']['item'] != $_SESSION['item'])) {
        for ($s = 0; $s < count($_SESSION['item']); $s++) {
            $_SESSION['dadosDaPagina']['item'][] = $_SESSION['item'][$s];
        }
        $matOuServ = $_SESSION['dadosDaPagina']['item']['TipoGrupoBanco'][0];
        unset($_SESSION['item']);
    }

    if (!empty($_SESSION['classe']) && !is_null($_POST['radioClasse'])) {
        $selecClasse = $_POST['radioClasse'];
        $classe = $_SESSION['classe'][$selecClasse];
        $_SESSION['classeSelecionadada'] = $classe;
        unset($_SESSION['classe']);
    }

    // Verifica se itens veio e salva na sessão, caso o item esteja limpo ele pega da sessão para evitar perca de dados
    if ((!empty($_SESSION['dadosDaPagina']['classe']) && !empty($classe))) {
        $_SESSION['dadosDaPagina']['classe'] = $classe;
    } elseif (empty($_SESSION['dadosDaPagina']['classe']) && !empty($classe)) {
        $_SESSION['dadosDaPagina']['classe'] = $classe;
    } elseif (!empty($_SESSION['dadosDaPagina']['classe']) && empty($classe)) {
        $classe = $_SESSION['dadosDaPagina']['classe'];
    }

}

?>

<html>
<?php
# Carrega o layout padrão #
layout();
?>
<script language="javascript" src="../import/jquery/jquery-1.7.2.min.js" type="text/javascript"></script>
<script language="javascript" src="../import/jquery/jquery.maskmoney.js" type="text/javascript"></script>
<script language="javascript" src="../import/jquery/jquery.maskedinput.js" type="text/javascript"></script>​
<script language="javascript">

    <?php MenuAcesso(); ?>
    $(document).ready(function () {
        //Preenche o selectbox do sistema de origem
        /*$.post("PostDadosIntegracaoAutomaticaPNCP.php", { op: "getSistemaOrigem" }, function (data) {
            $('#selectSitOrigem').html(data);
        });*/


        // Função para o botão limpar
        $("#limparConsulta").on('click', function () {
            $('#formIntegracaoManual').trigger("reset")
        });

        $("#pesquisarConsulta").live("click", function () {

            var idIntegracao = $("#idIntegracao").val();
            var descricao = $("#descricao").val();
            var dataIni = $("#dataIni").val();
            var dataFim = $("#dataFim").val();
            var selectSitOrigem = $("#selectSitOrigem :selected").val();
            var servico = $("#servico :selected").val();
            var tipoOperacao = $("#tipoOperacao :selected").val();
            var tipoIntegracao = $("#tipoIntegracao :selected").val();

            if (idIntegracao === "" && dataIni === "" && dataFim === "" && descricao === ""
                && selectSitOrigem === "" && servico === "" && tipoOperacao === "" 
                && statusProcessamento === "" && tipoIntegracao === ""
            ) {
                return alert("Necessário Preencher Todos os Campos!");
            }

            if(dataIni !== "" && dataFim == ""){
                return alert("Necessário preencher a data final");
            }

            if(dataIni == "" && dataFim !== ""){
                return alert("Necessário preencher a data inicial");
            }

            $("#tdload").show();
            $("#op").val("getDadosIntegracao");
            $.post("PostDadosIntegracaoAutomaticaPNCP.php", $("#formIntegracaoManual").serialize(), function(data) { 
                const response = JSON.parse(data);
                if (response.status == false) {
                    $("#tdload").hide();
                    $("#tdmensagem").show();
                    $('html, body').animate({scrollTop:0}, 'slow');
                    $(".mensagem-texto").html(response.msm);
                } else {
                    $("#tdload").hide();
                    $("#tdmensagem").hide();
                    $("#exportarPDF").show();
                    $("#exportarXLS").show();
                    $("#exportarCSV").show();
                    $(".mensagem-texto").html('');
                    $("#resultadoHTML").html(response.html);
                }
            });
        });        
    })
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css?v=<?php echo time(); ?>">

<style>
    td {
        font-size: 10.6667px;
    }

    input,
    select {
        font-size: 10.6667px;
        /* text-transform: uppercase; */
    }

    #labels {
        width: 250px;
        background-color: #DCEDF7;
    }

    .botao {
        float: right;
        margin: 0 2px;
    }
</style>

<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
    <script language="JavaScript" src="../menu.js"></script>
    <script language="JavaScript">Init();</script>
    <form action="ConsIntegracaoManualPNCP.php" method="post" id="formIntegracaoManual" name="ConsIntegracaoManualPNCP">
        <input type="hidden" name="op" id="op" value="">
        <input type="hidden" name="formatoExport" id="formatoExport" value="">
        <br><br><br><br>
        <table cellpadding="3" border="0" summary="">

            <!-- Caminho -->
            <tr>
                <td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
                <td align="left" class="textonormal" colspan="2" style="font-size: 8pt;">
                    <font class="titulo2">|</font>
                    <a href="../index.php">
                        <font color="#000000">Página Principal</font>
                    </a> > PNCP > Integração > Consultar Integração Automática
                </td>
            </tr>
            <!-- Fim do Caminho-->

            <!-- Erro -->
            <tr>
                <td width="150"></td>
                <td align="left" colspan="2" id="tdmensagem">
                    <div class="mensagem">
                        <div class="error">
                            Erro
                        </div>
                        <span class="mensagem-texto">
                        </span>
                    </div>
                </td>
            </tr>
            <!-- Fim do Erro -->

            <!-- loading -->
            <tr>
                <td width="150"></td>
                <td align="left" colspan="2" id="tdload" style="display:none;">
                    <div class="load" id="load">
                        <div class="load-content">
                            <img src="../midia/loading.gif" alt="Carregando">
                            <spam>Carregando...</spam>
                        </div>
                    </div>
                </td>
            </tr>
            <!-- Fim do loading -->

            <!-- Corpo -->
            <tr>
                <td width="100"></td>
                <td class="textonormal">
                    <table border="0" cellspacing="0" cellpadding="3" summary="" width="1024px" bgcolor="#FFFFFF">
                        <tr>
                            <td class="textonormal" border="0" bordercolor="#75ADE6">
                                <table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary=""
                                    class="textonormal" bgcolor="#FFFFFF">
                                    <thead>
                                        <td class="titulo3" colspan="17" align="center" bgcolor="#75ADE6"
                                            valign="middle"> <b>Consultar Integração Automática PNCP</b>
                                        </td>
                                    </thead>
                                    <tr>
                                        <td align="left">
                                            <table cellpadding="0" cellspacing="0" border="0" bordercolor="#75ADE6"
                                                width="100%" summary="">
                                                <tr bgcolor="#bfdaf2">
                                                    <table class="textonormal" id="tableSelecionarAtualizarDFD"
                                                        summary="" width="100%">
                                                        <tbody>
                                                            <tr>
                                                                <td class="textonormal" id="labels">
                                                                    Id da Integração
                                                                </td>
                                                                <td>
                                                                    <input type="text" name="idIntegracao"
                                                                        id="idIntegracao" style="width:340px;"
                                                                        onkeypress="return ((event.charCode >= 48 && event.charCode <= 57) || ( event.charCode == 57))"
                                                                        placeholder="Informe o id da integração"
                                                                        autocomplete="off">
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td class="textonormal" id="labels">
                                                                    Descrição
                                                                </td>
                                                                <td>
                                                                    <input type="text" name="descricao" id="descricao"
                                                                        style="width:340px;"
                                                                        placeholder="Informe a descrição"
                                                                        autocomplete="off">
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td class="textonormal" id="labels">
                                                                    Sistema de Origem
                                                                </td>
                                                                <td>
                                                                    <select name="selectSitOrigem" id="selectSitOrigem"
                                                                        style="width:340px;" required>
                                                                        <option value="PC" selected>Portal de Compras</option>
                                                                    </select>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td class="textonormal" id="labels">
                                                                    Serviço
                                                                </td>
                                                                <td>
                                                                    <select name="servico" id="servico"
                                                                        style="width:340px;" required>
                                                                        <option value=" ">Selecione</option>
                                                                        <option value="usuario">Usuário</option>
                                                                        <option value="orgao">Orgão</option>
                                                                        <option value="unidade">Unidade</option>
                                                                        <option value="compraeditalaviso">Compra
                                                                        </option>
                                                                        <option value="ata">Ata</option>
                                                                        <option value="contrato">Contrato</option>
                                                                        <option value="termodecontrato">Termo de
                                                                            Contrato
                                                                        </option>
                                                                        <option value="planodecontratacoes">Plano de
                                                                            Contratação</option>
                                                                        <option value="todos">Todos os serviços</option>
                                                                    </select>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td class="textonormal" bgcolor="#DCEDF7" width="30%"
                                                                    height="20">Período</td>
                                                                <td class="textonormal">
                                                                    <?php
                                                                    $DataMes = DataMes();

                                                                    if ($DataIni == "" || is_null($DataIni)) {
                                                                        //$DataIni = $DataMes[0];
                                                                        $DataIni = "";
                                                                    }

                                                                    if ($DataFim == "" || is_null($DataFim)) {
                                                                        //$DataFim = $DataMes[1];
                                                                        $DataFim = "";
                                                                    }

                                                                    $URLIni = "../calendario.php?Formulario=ConsPesquisarDFD&Campo=DataIni";
                                                                    $URLFim = "../calendario.php?Formulario=ConsPesquisarDFD&Campo=DataFim";
                                                                    ?>

                                                                    <input class="data textonormal" type="text"
                                                                        name="dataIni" id="dataIni" size="10"
                                                                        maxlength="10" value="<?php echo $DataIni; ?>">
                                                                    <a
                                                                        href="javascript:janela('<?php echo $URLIni ?>','Calendario',220,170,1,0)"><img
                                                                            src="../midia/calendario.gif" border="0"
                                                                            alt=""></a>
                                                                    &nbsp;a&nbsp;
                                                                    <input class="data textonormal" type="text"
                                                                        name="dataFim" id="dataFim" size="10"
                                                                        maxlength="10" value="<?php echo $DataFim; ?>">
                                                                    <a
                                                                        href="javascript:janela('<?php echo $URLFim ?>','Calendario',220,170,1,0)"><img
                                                                            src="../midia/calendario.gif" border="0"
                                                                            alt=""></a>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td class="textonormal" id="labels">
                                                                    Tipo de Operação
                                                                </td>
                                                                <td>
                                                                    <select name="tipoOperacao" id="tipoOperacao"
                                                                        style="width:340px;" required>
                                                                        <option value=" ">Todas as operações
                                                                        </option>
                                                                        <option value="I">Inclusão de novos
                                                                            registros no PNCP</option>
                                                                        <option value="A">Ajuste de registros já
                                                                            enviados ao PNCP</option>
                                                                        <option value="E">Exclusão de registros já
                                                                            enviados ao PNCP</option>
                                                                    </select>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td class="textonormal" id="labels">
                                                                    Tipo de integração
                                                                </td>
                                                                <td>
                                                                    <select name="tipoIntegracao"
                                                                        id="tipoIntegracao" style="width:340px;"
                                                                        required>
                                                                        <option value="A">Automática</option>
                                                                        <option value="M">Manual</option>
                                                                    </select>
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>

                                                <tr>
                                                    <td colspan="8">
                                                        <button type="button" name="limparConsulta" class="botao"
                                                            id="limparConsulta">Limpar</button>
                                                        <button type="button" name="pesquisarConsulta" class="botao"
                                                            id="pesquisarConsulta">Pesquisar</button>
                                                        <input type="hidden" name="InicioPrograma" value="1">
                                                    </td>
                                                </tr>
                                                <tr id="resultadoHTML">
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
        </td>
        </tr>
        <!-- FIM CORPO -->
        </table>
    </form>
</body>

</html>
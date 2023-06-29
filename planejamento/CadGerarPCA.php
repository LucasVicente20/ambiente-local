<?php 
/**
* Portal de Compras
* Programa: CadGerarPCA.php
* Autor: João Madson
* Data: 03/02/2023
* Objetivo: Programa para GerarPCA
* -------------------------------------------------------------------
*/

# Acesso ao arquivo de funções #
require_once "../funcoes.php";
include '../app/export/ExportaCSV.php';
# Executa o controle de segurança	#
session_start();
Seguranca();

if ($_SERVER['REQUEST_METHOD'] == "GET") {
    unset($_SESSION['Export']);
}
if ($_SERVER['REQUEST_METHOD'] == "POST") {    
    if($_SESSION['Export']['gerar'] == true){
        $nomeArquivo    =   $_SESSION['Export']['nomeArquivo'];
        $resultados     =   $_SESSION['Export']['resultados'];
        $cabecalho      =   $_SESSION['Export']['cabecalho'];
        $formatoExport  =   $_SESSION['Export']['formatoExport'];
        unset($_SESSION['Export']);
        switch($formatoExport){
            case 'csv':
                $nomeArquivo.= '.csv';
                $export = new ExportaCSV($nomeArquivo, ';', $cabecalho, $resultados);
            break;
        }
    
        $export->download();
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
    <script language="javascript" src="../import/jquery/jquery.maskedinput.js" type="text/javascript"></script>
    <script language="javascript" >
    <?php MenuAcesso(); ?>

    $(document).ready(function() {
        $.post("PostDadosGerarPCA.php",{op:"anosDFD"}, function(data) {
            $('#selectAnoPCA').html(data);
        })
        $("#GerarPCA").live("click", function(){
            $("#tdload").show();
            $("#op").val("geraPCA");
            $.post("PostDadosGerarPCA.php", $("#CadGerarPCA").serialize(), function(data) {
                const response = JSON.parse(data);
                if (response.status == false) {
                    $("#tdload").hide();
                    $("#tdmensagem").show();
                    $('html, body').animate({scrollTop:0}, 'slow');
                    $(".mensagem-texto").html(response.msm);
                } else {
                    if(response.tipo == "pdf"){
                        $("#tdload").hide();
                        window.open('../dompdf/GeraPdf.php', '_blank');                        
                    }
                    if(response.tipo == "csv"){
                        $("#tdload").hide();
                        $("[name='CadGerarPCA']").submit();
                    }
                }
            })
        })
        $("#limpar").live("click", function(){
            $("#tdload").show();
            $("#op").val("limpar");
            $.post("PostDadosGerarPCA.php", $("#CadGerarPCA").serialize(), function(data) {
                const response = JSON.parse(data);
                if(response.status == true){
                    $('html, body').animate({scrollTop:0}, 'slow');
                    setTimeout(function(){
                        window.location.href = "./CadGerarPCA.php?";
                    }, 2000);
                }

            })
        })
        $("#tipoPCAC").live("click", function(){
            $("#trautoridade").hide();
            $("#trcargo").hide();
        })
        $("#tipoPCAA").live("click", function(){
            $("#trautoridade").show();
            $("#trcargo").show();
        })
        $("#tipoPCAP").live("click", function(){
            $("#trautoridade").show();
            $("#trcargo").show();
        })
    })
    </script>
    <link rel="stylesheet" type="text/css" href="../estilo.css?v=<?php echo time();?>">

    <style>
        td {
            font-size: 10.6667px;
        }

        input, select{
            font-size: 10.6667px; 
            /* text-transform: uppercase; */
        }

        #labels{
            width: 250px;
            background-color:#DCEDF7;
        }

        .botao {
            float: right;
            margin: 0 2px;
        }

    </style>

    <body background="../midia/bg.gif" marginwidth="0" marginheight="0">
    <script language="JavaScript" src="../menu.js"></script>
    <script language="JavaScript">Init();</script>
    <form action="CadGerarPCA.php" method="post" id="CadGerarPCA" name="CadGerarPCA">
    <input type="hidden" name="op" id="op" value="">
    <br><br><br><br><br>
    <table cellpadding="3" border="0" summary="">
        <!-- Caminho -->
        <tr>
            <td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
            <td align="left" class="textonormal" colspan="2" style="font-size: 8pt;">
                <font class="titulo2">|</font>
                <a href="../index.php"><font color="#000000">Página Principal</font></a> > Planejamento > PCA > Gerar PCA
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
                    <div class="load-content" >
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
                <table  border="0" cellspacing="0" cellpadding="3" summary="" width="1024px" bgcolor="#FFFFFF">
                    <tr>
                        <td class="textonormal" border="0" bordercolor="#75ADE6">
                            <table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" bgcolor="#FFFFFF">
                                <thead>
                                    <td class="titulo3" colspan="17" align="center" bgcolor="#75ADE6" valign="middle"> <b>GERAR - PLANO DE CONTRATAÇÕES ANUAL (PCA)</b>
                                    </td>
                                </thead>
                                <tr>
                                    <td>
                                        <table border="0" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" bgcolor="#FFFFFF" style="width: 633px;">
                                            <tr>
                                                <td>Preencha os dados abaixo e clique no botão "Gerar PCA". Os itens obrigatórios estão com *. </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td align="left">
                                        <table cellpadding="0" cellspacing="0" border="0" bordercolor="#75ADE6" width="100%" summary="">
                                            <tr bgcolor="#bfdaf2">                                                    
                                                <!-- <td colspan="4"> -->    
                                                <table class="textonormal" id="tablePesquisarDFD" summary="" width="100%">
                                                    <!-- style="border: 1px solid #75ade6; border-radius: 4px;" -->
                                                    <tbody>  
                                                        <tr>
                                                            <td class="textonormal" id="labels">
                                                                Ano do PCA*
                                                            </td>
                                                            <td>
                                                                <select name="selectAnoPCA" id="selectAnoPCA" style="width:auto;">     
                                                                    <option value="">Selecione o Ano do PCA...</option>
                                                                </select>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="textonormal" id="labels">
                                                                Tipo do relatório*
                                                            </td>
                                                            <td>
                                                                <input type="radio" name="tipoPCA" id="tipoPCAC" value="C">PCA Completo</input>
                                                                <input type="radio" name="tipoPCA" id="tipoPCAA" value="A">PCA para Aprovação</input>
                                                                <input type="radio" name="tipoPCA" id="tipoPCAP" value="P">PCA para Publicação</input>
                                                            </td>
                                                        </tr> 
                                                        <tr id="trautoridade" style="display:none;">
                                                            <td class="textonormal" id="labels">
                                                                Autoridade Competente*
                                                            </td>
                                                            <td>
                                                                <input type="text" name="autoridade" id="autoridade" maxlength="200" size="50">
                                                            </td>
                                                        </tr>
                                                        <tr id="trcargo" style="display:none;">
                                                            <td class="textonormal" id="labels">
                                                                Cargo*
                                                            </td>
                                                            <td>
                                                                <input type="text" name="cargo" id="cargo" maxlength="200" size="50">
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                                <tr>
                                                    <td colspan="8">
                                                        <button type="button" name="limpar" class="botao" id="limpar">Limpar</button> 
                                                        <button type="button" name="GerarPCA" class="botao" id="GerarPCA">Gerar PCA</button>
                                                    </td>
                                                </tr>
                                                <tr id="resultadoHTML">
                                                </tr>
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
        <div class="modal" id="modal">
            <div class="modal-content" >
            
            </div>
        </div>
    </table>
</html>

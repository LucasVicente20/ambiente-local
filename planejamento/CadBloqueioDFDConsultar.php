<?php 
/**
* Portal de Compras
* Programa: CadBloqueioDFDConsultar.php
* Autor: João Madson
* Data: 03/04/2023
* Objetivo: Programa para Bloquear 
* -------------------------------------------------------------------
*/

# Acesso ao arquivo de funções #
require_once "../funcoes.php";
# Executa o controle de segurança	#
session_start();
Seguranca();

if ($_SERVER['REQUEST_METHOD'] == "GET") {
}
if ($_SERVER['REQUEST_METHOD'] == "POST") {    
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
        $.post("PostDadosBloqueioConsultar.php",{op:"anosDFD"}, function(data) {
            $('#selectAnoPCA').html(data);
        })
        $.post("PostDadosBloqueioConsultar.php",{op:"getOrgao"}, function(data) {
            const response = JSON.parse(data);
            $('#selectAreaReq').html(response.options);
        })
        $("#limpar").live("click", function(){
            $("#tdload").show();
            $("#op").val("limpar");
            $.post("PostDadosBloqueioConsultar.php", $("#CadBloqueioDFDConsultar").serialize(), function(data) {
                const response = JSON.parse(data);
                if(response.status == true){
                    $('html, body').animate({scrollTop:0}, 'slow');
                    setTimeout(function(){
                        window.location.href = "./CadBloqueioDFDConsultar.php?";
                    }, 2000);
                }

            })
        })
        $("#pesquisar").live("click", function(){
            $("#tdload").show();
            $("#tdmensagem").hide();
            $(".mensagem-texto").html();
            
            $("#op").val("pesquisaLib");
            $.post("PostDadosBloqueioConsultar.php", $("#CadBloqueioDFDConsultar").serialize(), function(data) {
                const response = JSON.parse(data);
                if(response.status == true){
                    $('#resultadoHTML').html(response.html);
                    $("#tdload").hide();
                }else{
                    $("#tdload").hide();
                    $("#tdmensagem").show();
                    $('html, body').animate({scrollTop:0}, 'slow');
                    $(".mensagem-texto").html(response.msm);
                }

            })
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
    <form action="CadBloqueioDFDConsultar.php" method="post" id="CadBloqueioDFDConsultar" name="CadBloqueioDFDConsultar">
    <input type="hidden" name="op" id="op" value="">
    <br><br><br><br><br>
    <table cellpadding="3" border="0" summary="">
        <!-- Caminho -->
        <tr>
            <td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
            <td align="left" class="textonormal" colspan="2" style="font-size: 8pt;">
                <font class="titulo2">|</font>
                <a href="../index.php"><font color="#000000">Página Principal</font></a> > Planejamento > DFD > Consultar Liberação de DFD
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
                                    <td class="titulo3" colspan="17" align="center" bgcolor="#75ADE6" valign="middle"> <b>CONSULTA DE LIBERAÇÃO DE DFD NO PERÍODO</b>
                                    </td>
                                </thead>
                                <tr>
                                    <td>
                                        <table border="0" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" bgcolor="#FFFFFF" style="width: 633px;">
                                            <tr>
                                                <td>Para Exibir os DFDs, selecione as opções e clique em “Pesquisar”.</td>
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
                                                                Área requisitante
                                                            </td>
                                                            <td>
                                                                <select id='selectAreaReq' name='selectAreaReq' size='1' style='width:auto; font-size: 10.6667px;'>
                                                                <span id="areaReq"></span></select>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                                <tr>
                                                    <td colspan="8">
                                                        <button type="button" name="limpar" class="botao" id="limpar">Limpar</button> 
                                                        <button type="button" name="pesquisar" class="botao" id="pesquisar">Pesquisar</button>
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

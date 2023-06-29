<?php 
/**
* Portal de Compras
* Programa: CadBloqueioDFDIncluir.php
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
        // $.post("PostDadosBloqueioIncluir.php",{op:"anosDFD"}, function(data) {
        //     $('#selectAnoPCA').html(data);
        // })
        // o evento change do jQuery para detectar quando o selectAnoPCA é alterado
        $("#selectAnoPCA").change(function(){
            $("#tdload").show();
            $("#tdmensagem").hide();
            const anoPCA = $("#selectAnoPCA").val();
            //Usei length pra garantir que a variavel tem algo
            if(anoPCA.length !== 0){
                $("#op").val("buscaBloqueio");
                $.post("PostDadosBloqueioIncluir.php", $("#CadBloqueioDFDIncluir").serialize(), function(data) {
                    const resp = JSON.parse(data);
                    console.log(resp);
                    if(resp.status == true){
                        $('input[name="DataIni"]').val(resp.dataIni);
                        $('input[name="DataFim"]').val(resp.dataFim);
                        $("#tdload").hide();
                    }else{
                        $('input[name="DataIni"]').val("");
                        $('input[name="DataFim"]').val("");
                        $("#tdload").hide();
                    }
                })
            }else{
                $("#tdload").hide();
            }
        })
        $("#salvar").live("click", function(){
            $("#tdload").show();
            $("#op").val("salvarPeriodo");
            $.post("PostDadosBloqueioIncluir.php", $("#CadBloqueioDFDIncluir").serialize(), function(data) {
                const response = JSON.parse(data);
                // if (response.status == false) {
                    $("#tdload").hide();
                    $("#tdmensagem").show();
                    $('html, body').animate({scrollTop:0}, 'slow');
                    $(".error").html('<blink class="titulo2">Atenção</span>');
                    $(".mensagem-texto").html(response.msm);
                // }else{
                // }
            })
        })
        $("#limpar").live("click", function(){
            $("#tdload").show();
            $("#op").val("limpar");
            $.post("PostDadosBloqueioIncluir.php", $("#CadBloqueioDFDIncluir").serialize(), function(data) {
                const response = JSON.parse(data);
                if(response.status == true){
                    $('html, body').animate({scrollTop:0}, 'slow');
                    setTimeout(function(){
                        window.location.href = "./CadBloqueioDFDIncluir.php?";
                    }, 2000);
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
    <form action="CadBloqueioDFDIncluir.php" method="post" id="CadBloqueioDFDIncluir" name="CadBloqueioDFDIncluir">
    <input type="hidden" name="op" id="op" value="">
    <br><br><br><br><br>
    <table cellpadding="3" border="0" summary="">
        <!-- Caminho -->
        <tr>
            <td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
            <td align="left" class="textonormal" colspan="2" style="font-size: 8pt;">
                <font class="titulo2">|</font>
                <a href="../index.php"><font color="#000000">Página Principal</font></a> > Planejamento > DFD > Bloquear Período
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
                                    <td class="titulo3" colspan="17" align="center" bgcolor="#75ADE6" valign="middle"> <b>BLOQUEIO DE DFD NO PERÍODO</b>
                                    </td>
                                </thead>
                                <tr>
                                    <td>
                                        <table border="0" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" bgcolor="#FFFFFF" style="width: 633px;">
                                            <tr>
                                                <td>Preencha os dados abaixo e clique no botão "Salvar". Os itens obrigatórios estão com *. </td>
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
                                                            <?php
                                                                $anoAtual = intval(date("Y"));
                                                                $anoSeguinte = $anoAtual+1;
                                                                $options = "";
                                                                // $options .= '';
                                                                // $options .= '';
                                                                echo $options;
                                                            ?>
                                                            <td>
                                                                <select name="selectAnoPCA" id="selectAnoPCA" style="width:auto;">     
                                                                    <option value="">Selecione o Ano do PCA...</option>
                                                                    <option value="<?php echo $anoAtual; ?>"><?php echo $anoAtual; ?></option>
                                                                    <option value="<?php echo $anoSeguinte; ?>"><?php echo $anoSeguinte; ?></option>
                                                                </select>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="textonormal" id="labels">
                                                                Período de Bloqueio*
                                                            </td>
                                                            <?php
                                                                $URLIni = "../calendario.php?Formulario=CadBloqueioDFDIncluir&Campo=DataIni";
                                                                $URLFim = "../calendario.php?Formulario=CadBloqueioDFDIncluir&Campo=DataFim";
                                                            ?>
                                                            <td>
                                                                <input class="data" type="text" name="DataIni" size="10" maxlength="10" value="<?php echo $DataIni;?>">
                                                                <a href="javascript:janela('<?php echo $URLIni ?>','Calendario',220,170,1,0)"><img src="../midia/calendario.gif" border="0" alt=""></a>
                                                                &nbsp;a&nbsp;
                                                                <input class="data" type="text" name="DataFim" size="10" maxlength="10" value="<?php echo $DataFim;?>">
                                                                <a href="javascript:janela('<?php echo $URLFim ?>','Calendario',220,170,1,0)"><img src="../midia/calendario.gif" border="0" alt=""></a>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                                <tr>
                                                    <td colspan="8">
                                                        <button type="button" name="limpar" class="botao" id="limpar">Limpar</button> 
                                                        <button type="button" name="salvar" class="botao" id="salvar">Salvar</button>
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

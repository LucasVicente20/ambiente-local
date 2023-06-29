<?php 
 /**
 * Portal de Compras
 * Programa: cadSelecionarAgruparDFD.php
 * Autor: Diógenes Dantas
 * Data: 22/11/2022
 * Objetivo: Programa para pesquisar DFD
 * Tarefa Redmine: #275345
 * -------------------------------------------------------------------
  * Alterado: Osmar Celestino
  * Data: 20/12/2022
  * Tarefa: 276459
  * -------------------------------------------------------------------
  */

# Acesso ao arquivo de funções #
require_once "../funcoes.php";

# Executa o controle de segurança	#
session_start();
Seguranca();

if ($_SERVER['REQUEST_METHOD'] == "GET") {
    unset($_SESSION['item']);
}
if ($_SERVER['REQUEST_METHOD'] == "POST") {    
    if (!empty($_SESSION['item'])) {
        $itens = $_SESSION['item'];
        unset($_SESSION['item']);
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
    <script language="javascript" >

    <?php MenuAcesso(); ?>

    

    $(document).ready(function() {
        $('.data').mask('99/99/9999');
        $('#idDFD').mask('9999.9999/9999');

        $.post("PostDadosConsultaDFD.php",{op:"getOrgao"}, function(data) {
            $('#areaReq').html(data);
        })

        $.post("PostDadosConsultaDFD.php",{op:"getSituacaoDFD"}, function(data) {
            $('#selectSitDFD').html(data);
        })
        $.post("PostDadosConsultaDFD.php",{op:"anosDFD"}, function(data) {
            $('#recebeSelectAno').html(data);
        })

        $("#lupaCodClasse").on('click', function(data){
            $.post("PostDadosDFD.php",{op:"modalPesqClasse"}, function(data){
                $(".modal-content").html(data);
                $(".modal-content").attr("style","min-height: 119px;width: 853px;");
                $("#modal").show();
            });
        });
       
        
        $("#lupaClasse").live('click',function(){
            $("#LoadPesqScc").show();
            //Limpa a mensagem quando refaz a pesquisa
            $("#tdmensagemM").hide();
            var opPesq = $("#OpcaoPesquisaClasse").val();
            var dadoPesquisar = $("#ClasseDescricaoDireta").val();
            $.post("PostDadosDFD.php",{
                    op:"PesqClasse",
                    tipoPesq:"ClasseDescricaoDireta",
                    opcaoPesq: opPesq,
                    dadoPesq: dadoPesquisar},
                function(data){
                    const response = JSON.parse(data);
                    if(response.status == 404){
                        $("#LoadPesqScc").hide();
                        $("#tdmensagemM").show();
                        $('html, body').animate({scrollTop:0}, 'slow');
                        $(".mensagem-textoM").html(response.msm);
                    }else{
                        $("#LoadPesqScc").hide();
                        $("#pesqDivModal").html(response.msm);
                    }
                }
            );
        });

        $("#Exportar").live("click", function(){
            $("#tdload").show();
            $("#op").val("abrirJanelaAgrupar");
            // Serializei o formulário para o post, dessa forma tudo vai chegar no switch, por isso na linha acima, 
            // o op foi colocado direto em um input hidden dentro do form
            $.post("PostDadosConsultaDFD.php", $("#formPesquisarDFD").serialize(), function(data) { 
                const response = JSON.parse(data);
                if (response.status == false) {
                    $("#tdload").hide();
                    $("#tdmensagem").show();
                    $('html, body').animate({scrollTop:0}, 'slow');
                    $(".mensagem-texto").html(response.msm);
                } else {
                    var codigoGrupo = response.data;
                    window.location.href='CadAgruparDFD.php?CodigoGrupo='+codigoGrupo;
                }
            });
        });


       
    
        $("#lupaMaterial").live('click',function(){
            $("#LoadPesqScc").show();
            //Limpa a mensagem quando refaz a pesquisa
            $("#tdmensagemM").hide();
            var opPesq = $("#OpcaoPesquisaMaterial").val();
            var dadoPesquisar = $("#MaterialDescricaoDireta").val();
            $.post("PostDadosDFD.php",
                {op:"PesqClasse",
                    tipoPesq:"MaterialDescricaoDireta",
                    opcaoPesq: opPesq,
                    dadoPesq: dadoPesquisar},
                function(data){
                    const response = JSON.parse(data);
                    if(response.status == 404){
                        $("#LoadPesqScc").hide();
                        $("#tdmensagemM").show();
                        $('html, body').animate({scrollTop:0}, 'slow');
                        $(".mensagem-textoM").html(response.msm);
                    }else{
                        $("#LoadPesqScc").hide();
                        $("#pesqDivModal").html(response.msm);
                    }
                }
            );
        });
      

        $("#lupaServico").live('click',function(){
            $("#LoadPesqScc").show();
            //Limpa a mensagem quando refaz a pesquisa
            $("#tdmensagemM").hide();
            var opPesq = $("#OpcaoPesquisaServico").val();
            var dadoPesquisar = $("#ServicoDescricaoDireta").val();
            $.post("PostDadosDFD.php",
                {op:"PesqClasse",
                    tipoPesq:"OpcaoPesquisaServico",
                    opcaoPesq: opPesq,
                    dadoPesq: dadoPesquisar},
                function(data){
                    const response = JSON.parse(data);
                    if(response.status == 404){
                        $("#LoadPesqScc").hide();
                        $("#tdmensagemM").show();
                        $('html, body').animate({scrollTop:0}, 'slow');
                        $(".mensagem-textoM").html(response.msm);
                    }else{
                        $("#LoadPesqScc").hide();
                        $("#pesqDivModal").html(response.msm);
                    }
                }
            );
        });

        $(".btn-fecha-modal").live('click', function(){
            $("#modal").hide();
            window.localStorage.clear();
        });

        // Função para o botão limpar
        $("#limparConsulta").on('click', function(){
            $('#formPesquisarDFD').trigger("reset")
            document.CadSelecionarAgruparDFD.submit();
        });

        $("#pesquisarConsulta").live("click", function(){
            $("#tdload").show();
            $("#op").val("getDadosDFDAgrupar");
            // Serializei o formulário para o post, dessa forma tudo vai chegar no switch, por isso na linha acima, 
            // o op foi colocado direto em um input hidden dentro do form
            $.post("PostDadosConsultaDFD.php", $("#formPesquisarDFD").serialize(), function(data) { 
                const response = JSON.parse(data);
                if (response.status == false) {
                    $("#tdload").hide();
                    $("#tdmensagem").show();
                    $('html, body').animate({scrollTop:0}, 'slow');
                    $(".mensagem-texto").html(response.msm);
                } else {
                    $("#tdload").hide();
                    $("#mensagemFinal").hide();
                    $("#tdmensagem").hide();
                    $(".mensagem-texto").html('');
                    $("#resultadoHTML").html(response.html);
                    
                }
            });
        });
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

        .mensagem-texto-fim {
            color: #007fff;
            font-weight: bolder;
            font-family: Verdana,sans-serif,Arial;
            font-size: 8pt;
            font-style: normal;
        }

    </style>

    <body background="../midia/bg.gif" marginwidth="0" marginheight="0">
    <script language="JavaScript" src="../menu.js"></script>
    <script language="JavaScript">Init();</script>
    <form action="CadSelecionarAgruparDFD.php" method="post" id="formPesquisarDFD" name="CadSelecionarAgruparDFD">
    <input type="hidden" name="op" id="op" value="">
    <br><br><br><br>
    <table cellpadding="3" border="0" summary="">
        <!-- Caminho -->
        <tr>
            <td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
            <td align="left" class="textonormal" colspan="2" style="font-size: 8pt;">
                <font class="titulo2">|</font>
                <a href="../index.php"><font color="#000000">Página Principal</font></a> > Planejamento > DFD > Agrupar
            </td>
        </tr>
        <!-- Fim do Caminho-->
        
        <!-- Erro -->
        <tr>
            <td width="150"></td>
            <td align="left" colspan="2" id="tdmensagem">
                <div class="mensagem">
                    <div class="error">
                    Erro!
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
                <?php if($_SESSION['Sucesso']){?>
                
                <td align="left" colspan="2" id="mensagemFinal">
                    <div class="mensagem">
                        <div class="mensagem" width="600px;">
                            <div class="error">
                            <blink class="titulo2">Atenção</span>
                            </div>
                            <span class="mensagem-texto-fim">
                                DFD(S) Agrupada(s) com Sucesso!
                            </span>
                        </div>
                </td>

                <?php }
                unset($_SESSION['Sucesso'])?>
                    <tr>
                        <td class="textonormal" border="0" bordercolor="#75ADE6">
                            <table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" width="820px" summary="" class="textonormal" bgcolor="#FFFFFF">
                                <thead>
                                    <td class="titulo3" colspan="17" align="center" bgcolor="#75ADE6" valign="middle"> <b>AGRUPAR - DOCUMENTO DE FORMALIZAÇÃO DE DEMANDA (DFD)</b>
                                    </td>
                                </thead>
                                <tr>
                                    <td>
                                        <table border="0" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" bgcolor="#FFFFFF" style="width: autox;">
                                            <tr>
                                                <td>Preencha os dados abaixo e clique no botão "Pesquisar". Só serão exibidos os DFD´s que possuem a situação selecionada com mais de uma ocorrência para poder realizar o Agrupamento.</td>
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
                                                                Ano do PCA
                                                            </td>
                                                            <td id="recebeSelectAno">
                                                                
                                                            </td>
                                                        </tr>
                                                        <!-- <tr>
                                                            <td class="textonormal" id="labels">
                                                                Situação do DFD
                                                            </td>
                                                            <td>
                                                                <span id="selectSitDFD"></span>
                                                            </td>
                                                        </tr> -->
                                                       
                                                    </tbody>
                                                </table>
                                                <tr>
                                                    <td colspan="8">
                                                        <button type="button" name="limparConsulta" class="botao" id="limparConsulta">Limpar</button>
                                                        <button type="button" name="pesquisarConsulta" class="botao" id="pesquisarConsulta">Pesquisar</button>
                                                        <input type="hidden" name="InicioPrograma" value="1">
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

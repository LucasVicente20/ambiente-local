<?php 
 /**
 * Portal de Compras
 * Programa: ConsConsolidarDFD.php
 * Autor: Lucas Vicente
 * Data: 05/01/2023
 * Objetivo: Programa para pesquisar DFD
 * Tarefa Redmine: #
 * -------------------------------------------------------------------
 */

# Acesso ao arquivo de funções #
require_once "../funcoes.php";
include '../app/export/ExportaCSV.php';
include '../app/export/ExportaXLS.php';

# Executa o controle de segurança	#
session_start();
Seguranca();

if ($_SERVER['REQUEST_METHOD'] == "GET") {
    unset($_SESSION['item']);
    unset($_SESSION['consolidarDFDSequ']);
}
if ($_SERVER['REQUEST_METHOD'] == "POST") {    
    if (!empty($_SESSION['item'])) {
        $itens = $_SESSION['item'];
        unset($_SESSION['item']);
    }
    if($_SESSION['Export']['gerar'] == true){
        $nomeArquivo    =   $_SESSION['Export']['nomeArquivo'];
        $resultados     =   $_SESSION['Export']['resultados'];
        $cabecalho      =   $_SESSION['Export']['cabecalho'];
        $formatoExport  =   $_SESSION['Export']['formatoExport'];
        unset($_SESSION['Export']);
        switch($formatoExport){
            case 'xls':
                $nomeArquivo.= '.xls';
                $export = new ExportaXLS($nomeArquivo, $cabecalho, $resultados);
            break; 
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
    <script language="javascript" src="../import/jquery/jquery.maskedinput.js" type="text/javascript"></script>​
    <script language="javascript" >

    <?php MenuAcesso(); ?>

    function AbreJanelaItem(url,largura,altura){
        window.open(url,'paginaitem','status=no,scrollbars=yes,left=90,top=150,width='+largura+',height='+altura);
    }

    $(document).ready(function() {
        $('.data').mask('99/99/9999');
        $('#idDFD').mask('9999.9999/9999');

        $.post("PostDadosConsolidar.php",{op:"getOrgao"}, function(data) {
            $('#areaReq').html(data);
        })

        $.post("PostDadosConsolidar.php",{op:"getSituacaoDFD"}, function(data) {
            $('#selectSitDFD').html(data);
        })
        $.post("PostDadosConsolidar.php",{op:"anosDFD"}, function(data) {
            $('#recebeSelectAno').html(data);
        })

        $("#lupaCodClasse").on('click', function(){
            $.post("PostDadosDFD.php",{op:"modalPesqClasse"}, function(data){
                $(".modal-content").html(data);
                $(".modal-content").attr("style","min-height: 119px;width: 853px;");
                $("#modal").show();
            });
        });
        $("#lupaVincularDFD").on('click', function(){
            $.post("PostDadosDFD.php",{op:"vinculaDFD"}, function(data){
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
            $.post("PostDadosDFD.php",{op:"Limpar"}, function(data) {
                const response = JSON.parse(data); 
            });
           $("[name='ConsConsolidarDFD']").submit();
        });

        $("#pesquisarConsulta").live("click", function(){
            $("#tdload").show();
            $("#op").val("getDadosDFD");
            // Serializei o formulário para o post, dessa forma tudo vai chegar no switch, por isso na linha acima, 
            // o op foi colocado direto em um input hidden dentro do form
            $.post("PostDadosConsolidar.php", $("#formConsolidarDFD").serialize(), function(data) { 
                const response = JSON.parse(data);
                if (response.status == false) {
                    $("#tdload").hide();
                    $("#tdmensagem").show();
                    $('html, body').animate({scrollTop:0}, 'slow');
                    $(".mensagem-texto").html(response.msm);
                } else {
                    $("#tdload").hide();
                    $("#tdmensagem").hide();
                    $(".mensagem-texto").html('');
                    $("#resultadoHTML").html(response.html);
                }
            });
        });
        $("#exportarPDF").live("click", function(){
            $("#tdload").show();
            $("#op").val("getDadosExportarDFD");
            $("#formatoExport").val("pdf");
            $.post("PostDadosConsolidar.php", $("#formConsolidarDFD").serialize(), function(data) { 
                const response = JSON.parse(data);
                console.log(response);
                if (response.status == true) {
                    $("#tdload").hide();
                    window.open('../dompdf/GeraPdf.php', '_blank');
                }
            });
        });
        $("#exportarXLS").live("click", function(){
            $("#tdload").show();
            $("#op").val("getDadosExportarDFD");
            $("#formatoExport").val("xls");
            // Serializei o formulário para o post, dessa forma tudo vai chegar no switch, por isso na linha acima, 
            // o op foi colocado direto em um input hidden dentro do form
            $.post("PostDadosConsolidar.php", $("#formConsolidarDFD").serialize(), function(data) { 
                const response = JSON.parse(data);
                if (response.status == true) {
                    $("#tdload").hide();
                    $("[name='ConsConsolidarDFD']").submit();
                }
            });
        });
        $("#exportarCSV").live("click", function(){
            $("#tdload").show();
            $("#op").val("getDadosExportarDFD");
            $("#formatoExport").val("csv");
            // Serializei o formulário para o post, dessa forma tudo vai chegar no switch, por isso na linha acima, 
            // o op foi colocado direto em um input hidden dentro do form
            $.post("PostDadosConsolidar.php", $("#formConsolidarDFD").serialize(), function(data) { 
                const response = JSON.parse(data);
                if (response.status == true) {
                    $("#tdload").hide();
                    $("[name='ConsConsolidarDFD']").submit();
                }
            });
        });
        $("#consolidarDFD").live("click", function(){
            if(confirm("Tem certeza que deseja consolidar o(s) DFD(s)?")){
                $("#tdload").show();
                $("#op").val("consolidarDFD");
                $.post("PostDadosConsolidar.php", $("#formConsolidarDFD").serialize(), function(data){
                    const response = JSON.parse(data);
                    if(response.status==true){
                        $("#tdload").hide();
                        $("#tdmensagem").show();
                        $(".error").html("<blink class='titulo2'>Atenção</blink>");
                        $(".mensagem-texto").html(response.msm);
                        
                    }
                })
            }
            
        })
        $("#numDFDAll").live("click", function(){
            const SelectAll = $("#numDFDAll").prop("checked");
            if(SelectAll == true){
                $(".CBXNumDFD").prop("checked", true);
            }
            if(SelectAll == false){
                $(".CBXNumDFD").prop("checked", false);
            }
        })
        $(".CBXNumDFD").live("click", function(){
            const SelectAll = $("#numDFDAll").prop("checked");
            if(SelectAll == true){
                $("#numDFDAll").prop("checked", false)
            }
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
    <form action="ConsConsolidarDFD.php" method="post" id="formConsolidarDFD" name="ConsConsolidarDFD">
    <input type="hidden" name="op" id="op" value="">
    <input type="hidden" name="formatoExport" id="formatoExport" value="">
    <br><br><br><br>
    <table cellpadding="3" border="0" summary="">
        <!-- Caminho -->
        <tr>
            <td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
            <td align="left" class="textonormal" colspan="2" style="font-size: 8pt;">
                <font class="titulo2">|</font>
                <a href="../index.php"><font color="#000000">Página Principal</font></a> > Planejamento > DFD > Consolidar
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
                                    <td class="titulo3" colspan="17" align="center" bgcolor="#75ADE6" valign="middle"> <b>CONSOLIDAR - DOCUMENTO DE FORMALIZAÇÃO DE DEMANDAS (DFD)</b>
                                    </td>
                                </thead>
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
                                                                Número do DFD
                                                            </td>
                                                            <td>
                                                                <input type="text" name="idDFD" id="idDFD" size=30>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="textonormal" id="labels">
                                                                Ano do PCA
                                                            </td>
                                                            <td id="recebeSelectAno">
                                                                
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="textonormal" id="labels">
                                                                Área requisitante
                                                            </td>
                                                            <td>
                                                                <span id="areaReq"></span>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="textonormal" id="labels">
                                                                Grau de Prioridade
                                                            </td>
                                                            <td>
                                                                <select class="textonormal" name="grauPrioridade" id="grauPrioridade" style="width:340px;">
                                                                    <option value="">Escolha o grau</option>
                                                                    <option value="1">ALTO</option>
                                                                    <option value="2">MÉDIO</option>
                                                                    <option value="3">BAIXO</option>
                                                                </select>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="textonormal" id="labels">
                                                                Código da Classe
                                                            </td>
                                                            <td>
                                                                <img id="lupaCodClasse" src="../midia/lupa.gif" border="0">
                                                                </br>
                                                                <span class="textonormal"><?php echo "$classe->cclamscodi - $classe->ematepdesc"; ?></span>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="textonormal" id="labels">
                                                                Descrição da Demanda
                                                            </td>
                                                            <td>
                                                                <input type="text" name="descDemanda" class="textonormal" id="descDemanda" size=30>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="textonormal" bgcolor="#DCEDF7" width="30%" height="20">Período de Conclusão</td>
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

                                                                <input class="data textonormal" type="text" name="DataIni" size="10" maxlength="10" value="<?php echo $DataIni;?>">
                                                                <a href="javascript:janela('<?php echo $URLIni ?>','Calendario',220,170,1,0)"><img src="../midia/calendario.gif" border="0" alt=""></a>
                                                                &nbsp;a&nbsp;
                                                                <input class="data textonormal" type="text" name="DataFim" size="10" maxlength="10" value="<?php echo $DataFim;?>">
                                                                <a href="javascript:janela('<?php echo $URLFim ?>','Calendario',220,170,1,0)"><img src="../midia/calendario.gif" border="0" alt=""></a>
                                                            </td>
                                                        </tr>
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

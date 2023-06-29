<?php
/**
 * Portal de Compras
 * Programa: janelaVincular.php
 * Autor: Diógenes Dantas
 * Data: 22/11/2022
 * Objetivo: Programa para pesquisar DFD
 * Tarefa Redmine: #275345
 * -------------------------------------------------------------------
 * Alterado: Osmar Celestino
 * Data: 20/12/2022
 * Tarefa: 276459
 * -------------------------------------------------------------------
  * Alterado: João Madson | Lucas Vicente  
  * Data: 16/01/2023
  * Tarefa: Relatório de correções Nº3 Incluir DFD
 */

# Acesso ao arquivo de funções #
require_once "../funcoes.php";

# Executa o controle de segurança	#
session_start();
Seguranca();

if ($_SERVER['REQUEST_METHOD'] == "GET") {
    unset($_SESSION['item']);
    $areaReq = $_GET['areaReq'];
    $dfdsequ = $_GET['dfdsequ'];
}
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $areaReq = $_POST['areaReq'];
    $dfdsequ = $_POST['dfdsequ'];
    if (!empty($_SESSION['item'])) {
        $itens = $_SESSION['item'];
        unset($_SESSION['item']);
    }

    if (!empty($_SESSION['classe']) && !is_null($_POST['radioClasse'])) {
        $selecClasse = $_POST['radioClasse'];
        $classe = $_SESSION['classe'][$selecClasse];
        $_SESSION['classeSelecionadada'] = $classe;
        unset($_SESSION['classe']);
    }
}
if($_REQUEST['Programa'] == 'Manter'){
    $sessaoVincular = 'pesquisarConsultaManter';
}else{
    $sessaoVincular = 'pesquisarConsulta';
}

?>

<html>

<script language="javascript" src="../import/jquery/jquery-1.7.2.min.js" type="text/javascript"></script>
<script language="javascript" src="../import/jquery/jquery.maskmoney.js" type="text/javascript"></script>
<script language="javascript" src="../import/jquery/jquery.maskedinput.js" type="text/javascript"></script>​
<script language="javascript" src="../funcoes.js" type="text/javascript"></script>​
<script language="javascript" >
     function AbreJanela(url,largura,altura) {
        window.open(url,'pagina','status=no,scrollbars=no,left=20,top=150,width='+largura+',height='+altura);
    }
    $(document).ready(function() {
        $('.data').mask('99/99/9999');
        $('#idDFD').mask('9999.9999/9999');

        const areaReq = $('#areaReq').val();
        $.post("PostDadosConsultaDFD.php",{op:"getOrgaoDesc", codArea:areaReq}, function(data) {
            $('#areaReqRetorno').html(data);
        })

        $.post("PostDadosConsultaDFD.php",{op:"getSituacaoDFD"}, function(data) {
            $('#selectSitDFD').html(data);
        })
        // $.post("PostDadosConsultaDFD.php",{op:"anosDFD"}, function(data) {
        //     $('#recebeSelectAno').html(data);
        // })

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
            const formAction = $("#formAction").val();
            $("#LoadPesqScc").show();
            //Limpa a mensagem quando refaz a pesquisa
            $("#tdmensagemM").hide();
            var opPesq = $("#OpcaoPesquisaClasse").val();
            var dadoPesquisar = $("#ClasseDescricaoDireta").val();
            $.post("PostDadosDFD.php",{
                    op:"PesqClasse",
                    tipoPesq:"ClasseDescricaoDireta",
                    opcaoPesq: opPesq,
                    dadoPesq: dadoPesquisar,
                    action: formAction},
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
           const formAction = $("#formAction").val();
            $("#LoadPesqScc").show();
            //Limpa a mensagem quando refaz a pesquisa
            $("#tdmensagemM").hide();
            var opPesq = $("#OpcaoPesquisaMaterial").val();
            var dadoPesquisar = $("#MaterialDescricaoDireta").val();
            $.post("PostDadosDFD.php",
                {op:"PesqClasse",
                    tipoPesq:"MaterialDescricaoDireta",
                    opcaoPesq: opPesq,
                    dadoPesq: dadoPesquisar,
                    action: formAction},
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
        $("#radioClasse").live("click", function(){
            $("#incluirClasse").prop("disabled", false);
        })

        $("#lupaServico").live('click',function(){
            const formAction = $("#formAction").val();
            $("#LoadPesqScc").show();
            //Limpa a mensagem quando refaz a pesquisa
            $("#tdmensagemM").hide();
            var opPesq = $("#OpcaoPesquisaServico").val();
            var dadoPesquisar = $("#ServicoDescricaoDireta").val();
            $.post("PostDadosDFD.php",
                {op:"PesqClasse",
                    tipoPesq:"OpcaoPesquisaServico",
                    opcaoPesq: opPesq,
                    dadoPesq: dadoPesquisar,
                    action: formAction},
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
            $.post("PostDadosConsultaDFD.php",{op:"limparVinc"}, function(data) {
                const response = JSON.parse(data); 
                if(response.status == true){
                    $("#idDFD").val("");
                    $("#grauPrioridade").val("");
                    $("#descDemanda").val("");
                    $("#formJanelaVincular").submit();
                }
            });
            
        });

        $("#pesquisarConsulta").live("click", function(){
            $("#tdload").show();
            $("#op").val("getDadosVincularDFD");
            // Serializei o formulário para o post, dessa forma tudo vai chegar no switch, por isso na linha acima,
            // o op foi colocado direto em um input hidden dentro do form
            $.post("PostDadosConsultaDFD.php", $("#formJanelaVincular").serialize(), function(data) {
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
        $("#pesquisarConsultaManter").live("click", function(){
            $("#tdload").show();
            $("#op").val("getDadosVincularDFDManter");
            // Serializei o formulário para o post, dessa forma tudo vai chegar no switch, por isso na linha acima,
            // o op foi colocado direto em um input hidden dentro do form
            $.post("PostDadosConsultaDFD.php", $("#formJanelaVincular").serialize(), function(data) {
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

        $("#JanelaVincular").live("click", function(){
            $("#tdload").show();
            $("#op").val("sessaoVincularDFD");
            // Serializei o formulário para o post, dessa forma tudo vai chegar no switch, por isso na linha acima,
            // o op foi colocado direto em um input hidden dentro do form
            $.post("PostDadosConsultaDFD.php", $("#formJanelaVincular").serialize(), function(data) {
                const response = JSON.parse(data);
                if (response.status == false) {
                    $("#tdload").hide();
                    $("#tdmensagem").show();
                    $('html, body').animate({scrollTop:0}, 'slow');
                    $(".mensagem-texto").html(response.msm);
                } else {
                    opener.document.CadIncluirDFD.submit()
                    window.close();
                }
            });
        })

            $("#janelaVincularManter").live("click", function(){
            $("#tdload").show();
            $("#op").val("sessaoVincularDFDManter");
            // Serializei o formulário para o post, dessa forma tudo vai chegar no switch, por isso na linha acima,
            // o op foi colocado direto em um input hidden dentro do form
            $.post("PostDadosConsultaDFD.php", $("#formJanelaVincular").serialize(), function(data) {
                const response = JSON.parse(data);
                if (response.status == false) {
                    $("#tdload").hide();
                    $("#tdmensagem").show();
                    $('html, body').animate({scrollTop:0}, 'slow');
                    $(".mensagem-texto").html(response.msm);
                } else {
                    opener.document.CadManterDFD.submit()
                    window.close();
                }
            });

            $("CadIncluirDFD").submit();
            $("ConsDFD").submit();
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

</style>

<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="JanelaVincular.php" method="post" id="formJanelaVincular" name="JanelaVincular">
    <input type="hidden" name="op" id="op" value="">
    <input type="hidden" name="areaReq" id="areaReq" value="<?php echo $areaReq;?>">
    <input type="hidden" name="dfdsequ" id="dfdsequ" value="<?php echo $dfdsequ;?>">
    <input type="hidden" name="formAction" id="formAction" value="JanelaVincular.php">
    <table cellpadding="3" border="0" summary="">

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
            
            <td class="textonormal">
                <table  border="0" cellspacing="0" cellpadding="3" summary="" width="700px" bgcolor="#FFFFFF">
                    <tr>
                        <td class="textonormal" border="0" bordercolor="#75ADE6">
                            <table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" bgcolor="#FFFFFF">
                                <thead>
                                <td class="titulo3" colspan="17" align="center" bgcolor="#75ADE6" valign="middle"> <b>CONSULTAR - DOCUMENTO DE FORMALIZAÇÃO DE DEMANDAS (DFD)</b>
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
                                                            <input type="text" name="idDFD" id="idDFD" size=30 value="<?php echo !empty($_POST['idDFD'])?$_POST['idDFD']:"";?>">
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="textonormal" id="labels">
                                                            Ano do PCA
                                                        </td>
                                                        <td>
                                                            <span>2024</span> 
                                                            <input type="hidden" name="selectAnoPCA" id="selectAnoPCA" value="2024">
                                                        </td>

                                                        <!-- <td id="recebeSelectAno">
                                                            
                                                        </td> -->
                                                    </tr>
                                                    <tr>
                                                        <td class="textonormal" id="labels">
                                                            Área requisitante
                                                        </td>
                                                        <td>
                                                            <span id="areaReqRetorno"></span>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="textonormal" id="labels">
                                                            Situação do DFD
                                                        </td>
                                                        <td>
                                                            <span id="selectSitDFD"></span>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="textonormal" id="labels">
                                                            Grau de Prioridade
                                                        </td>
                                                        <td>
                                                            <select class="textonormal" name="grauPrioridade" id="grauPrioridade" style="width:340px;">
                                                                <option value="">Escolha o Grau de Prioridade...</option>
                                                                <option value="1">ALTO</option>
                                                                <option value="2">MÉDIO</option>
                                                                <option value="3">BAIXO</option>
                                                            </select>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="textonormal" id="labels">
                                                            Classe
                                                        </td>
                                                        <td>
                                                            <img id="lupaCodClasse" src="../midia/lupa.gif" border="0">
                                                            </br>
                                                            <span class="textonormal"><?php echo !empty($classe->eclamsdesc)? "$classe->eclamsdesc":""; ?></span>
                                                            <input type="hidden" name="cclamscodi" id="cclamscodi" value="<?php echo $classe->cclamscodi;?>">
                                                            <input type="hidden" name="cgrumscodi" id="cgrumscodi" value="<?php echo $classe->cgrumscodi;?>">   
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="textonormal" id="labels">
                                                            Descrição Sucinta da Demanda
                                                        </td>
                                                        <td>
                                                            <input type="text" name="descDemanda" class="textonormal" id="descDemanda" size=30 value="<?php echo !empty($_POST['descDemanda'])? $_POST['descDemanda']:""; ?>">
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

                                                            $URLIni = "../calendario.php?Formulario=janelaVincular&Campo=DataIni";
                                                            $URLFim = "../calendario.php?Formulario=janelaVincular&Campo=DataFim";
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
                                                    <button type="button" name="pesquisarConsulta" class="botao" id="<?php echo $sessaoVincular ?>">Pesquisar</button>
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

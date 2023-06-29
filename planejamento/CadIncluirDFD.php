<?php 
 /**
 * Portal de Compras
 * Programa: CadIncluirDFD.php
 * Autor: Diógenes Dantas | Madson Felix
 * Data: 17/11/2022
 * Objetivo: Programa para inclusão de DFD
 * Tarefa Redmine: #275243
 * -------------------------------------------------------------------
  * Alterado: Osmar Celestino
  * Data: 20/12/2022
  * Tarefa: 276459
  * -------------------------------------------------------------------
  * Alterado: Lucas Vicente
  * Data: 05/01/2023
  * Tarefa: #277231
  * Alterado: Lucas Vicente
  * Data:09/01/2023
  * Tarefa: Ajuste na regra do Configurador DFD
  * -------------------------------------------------------------------
  * Alterado: João Madson   
  * Data: 09/01/2023
  * Tarefa: #277372
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
    unset($_SESSION['classe']);
    unset($_SESSION['dadosDaPagina']);
    unset($_SESSION['item']);
    unset($_SESSION['dadosDaPagina']['item']);
    unset($_SESSION["vincularDFD"]);
    unset($_SESSION['KeepOrgaoSelect']);
    unset($_SESSION['servico']);
    unset($_SESSION['AreaReqCod']);
    unset($_SESSION['Bloqueio']); 
}
if ($_SERVER['REQUEST_METHOD'] == "POST") { 

    $_SESSION['dadosDaPagina']['selectAnoPCA']          = $_POST['selectAnoPCA'];
    $_SESSION['dadosDaPagina']['selectAeaReq']          = $_POST['selectAreaReq'];
    $_SESSION['dadosDaPagina']['descSuDemanda']         = $_POST['descSuDemanda'];
    $_SESSION['dadosDaPagina']['justContratacao']       = $_POST['justContratacao'];
    $_SESSION['dadosDaPagina']['estValorContratacao']   = $_POST['estValorContratacao'];
    $_SESSION['dadosDaPagina']['tpProcContratacao']     = $_POST['tpProcContratacao'];
    $_SESSION['dadosDaPagina']['dtPretendidaConc']      = $_POST['dtPretendidaConc'];
    $_SESSION['dadosDaPagina']['grauPrioridade']        = $_POST['grauPrioridade'];
    $_SESSION['dadosDaPagina']['justPriAlta']           = $_POST['justPriAlta'];
    $_SESSION['dadosDaPagina']['contratCorp']           = $_POST['contratCorp'];
    
    if($_SESSION["vincularDFD"][0]->corglicodi != $_SESSION['dadosDaPagina']['selectAeaReq']){
        // unset($_SESSION["vincularDFD"]);
    }
    if(!empty($_SESSION['item']) && ($_SESSION['dadosDaPagina']['item'] != $_SESSION['item'])){
        for($s=0; $s<count($_SESSION['item']); $s++){
            $_SESSION['dadosDaPagina']['item'][] = $_SESSION['item'][$s];
        }
        $aux = array_unique($_SESSION['dadosDaPagina']['item'], SORT_REGULAR);
        $_SESSION['dadosDaPagina']['item'] = $aux;
        $matOuServ = $_SESSION['dadosDaPagina']['item']['TipoGrupoBanco'][0];
        if(empty($_SESSION['classe']) && is_null($_POST['radioClasse'])){
            $_SESSION['classe'][0]->cclamscodi = $_SESSION['dadosDaPagina']['item'][0]['CodClasse'];
            $_SESSION['classe'][0]->eclamsdesc = $_SESSION['dadosDaPagina']['item'][0]['DescClasse'];
            $_SESSION['classe'][0]->cgrumscodi = $_SESSION['dadosDaPagina']['item'][0]['CodGrupo'];
        }
        unset($_SESSION['item']);
    }
    // var_dump($_SESSION["dadosDaPagina"]["item"]);exit;
    if (!empty($_SESSION['classe']) && !is_null($_POST['radioClasse'])) {
        $selecClasse = $_POST['radioClasse'];
        if($_SESSION['classe'][$selecClasse]->cclamscodi != $_SESSION['dadosDaPagina']['item'][0]['CodClasse'] || $_SESSION['classe'][$selecClasse]->cgrumscodi != $_SESSION['dadosDaPagina']['item'][0]['CodGrupo']){
            unset($_SESSION['dadosDaPagina']['item']);
        }
        $classe = $_SESSION['classe'][$selecClasse];
        $_SESSION['classeSelecionadada'] = $classe;
        unset($_SESSION['classe']);
    }
    //vai entrar aqui se vier direto do item
    if (!empty($_SESSION['classe']) && is_null($_POST['radioClasse'])) {
        $selecClasse = 0;
        $classe = $_SESSION['classe'][$selecClasse];
        $_SESSION['classeSelecionadada'] = $classe;
        unset($_SESSION['classe']);
    } 
    
    $tipoItem = !empty($_SESSION['servico'])?$_SESSION['servico']:"C";


    // Verifica se itens veio e salva na sessão, caso o item esteja limpo ele pega da sessão para evitar perca de dados
    if((!empty($_SESSION['dadosDaPagina']['classe']) && !empty($classe))){
        $_SESSION['dadosDaPagina']['classe'] = $classe;
    }else if(empty($_SESSION['dadosDaPagina']['classe']) && !empty($classe)){
        $_SESSION['dadosDaPagina']['classe'] = $classe;
    }else if(!empty($_SESSION['dadosDaPagina']['classe']) && empty($classe)){
        $classe = $_SESSION['dadosDaPagina']['classe'];
    }
    // if($_SESSION['PeriodoBloqueado']['status'] == true){
    //         $mensagemPeriodoBloqueado =
    //         '<!-- Erro -->
    //         <tr>
    //             <td width="150"></td>
    //             <td align="left" colspan="2" id="mensagembloq" display:block;>
    //                 <div class="mensagem" display:block;>
    //                     <div class="error" display:block;>
    //                     Erro
    //                     </div>
    //                     <span class="mensagem-texto-bloq" display:block;>
    //                     '.$_SESSION['PeriodoBloqueado']['msm'].'
    //                     </span>
    //                 </div>
    //             </td>
    //         </tr>
    //         <!-- Fim do Erro -->';
    // }else{
    //     $mensagemPeriodoBloqueado = '';
    // }
}

// $anoAtual = date('Y');
// $anoAnterior = date('Y', strtotime('-1 Year', strtotime($anoAtual)));
// $anoPosterior = date('Y', strtotime('+1 Year', strtotime($anoAtual)));
// //mecanica para marcar o ano selecionado em caso de post
// $anoSelected0 = "";
// $anoSelected1 = "";
// $anoSelected2 = "";
// if($_SESSION['dadosDaPagina']['selectAnoPCA'] == $anoAnterior){$anoSelected0 = "selected" ;}
// else if($_SESSION['dadosDaPagina']['selectAnoPCA'] == $anoAtual){$anoSelected1 = "selected" ;}
// else if($_SESSION['dadosDaPagina']['selectAnoPCA'] == $anoPosterior){$anoSelected2 = "selected" ;}
// $optionsAno = '
//     <option value="">Selecione</option>
//     <option value="'.$anoAnterior.'" '.$anoSelected0.'>'.$anoAnterior.'</option>
//     <option value="'.$anoAtual.'" '.$anoSelected1.'>'.$anoAtual.'</option>
//     <option value="'.$anoPosterior.'" '.$anoSelected2.'>'.$anoPosterior.'</option>
// ';

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
   
    function AbreJanelaItem(url,largura,altura){
        window.open(url,'paginaitem','status=no,scrollbars=yes,left=90,top=150,width='+largura+',height='+altura);
    }
    function AbreJanela(url,largura,altura) {
        window.open(url,'pagina','status=no,scrollbars=no,left=20,top=150,width='+largura+',height='+altura);
    }
    function ClasseNaoInformada(msm){
        $("#tdmensagem").show();
        $("#mensagemFinal").hide();
        $('html,  corpo').animate({scrollTop:0},  'lento');
        $(".mensagem-texto").html(msm); 
    }
    function limpar() {
        $.post("PostDadosDFD.php",{op:"Limpar"}, function(data) {
           const response = JSON.parse(data); 
        });selectAreaReq
        document.CadIncluirDFD.rascunho.value = "";
        document.CadIncluirDFD.selectAnoPCA.value = "";
        document.CadIncluirDFD.cclamscodi.value = "";
        document.CadIncluirDFD.ematepdesc.value = "";
        document.CadIncluirDFD.descSuDemanda.value = "";
        document.CadIncluirDFD.estValorContratacao.value = "";
        document.CadIncluirDFD.tpProcContratacao.value = "";
        document.CadIncluirDFD.dtPretendidaConc.value = "";
        document.CadIncluirDFD.grauPrioridade.value = "";
        document.CadIncluirDFD.justContratacao.value = "";
        document.CadIncluirDFD.justPriAlta.value = "";
        document.CadIncluirDFD.contratCorp.value = "";
        document.CadIncluirDFD.submit();
    }
    $(document).ready(function() {
        $('.data').mask('99/99/9999');
        $.post("PostDadosDFD.php",{op:"getOrgao"}, function(data) {
            const response = JSON.parse(data);
            if(response.status == 200 && response.multiplos == true){
                $('#AreaReqUnico').hide();
                $('#selectAreaReq').html(response.htmlOrgao);
            }
            if(response.status == 200 && response.multiplos == false){
                $('#AreaReqMult').hide();
                $('#areaReq').html(response.htmlOrgao);
            }
            if(response.htmlCnpj != ""){ // Se apenas um cnpj veio, vai ser criado o html no Post e vem pronto, senão, vai ser feita dinamica para funcionar com select
                $("#cnpjAreaReq").html(response.htmlCNPJ);
                $("#cnpjAreaReq").show(response.htmlCNPJ);
            }
        })
        //Varifica liberação de DFD assim que as areas requisitantes são carregadas
        $("#op").val("checaBloqueio");
        $.post("PostDadosDFD.php", $("#formIncluirDFD").serialize(), function(data){
            const response = JSON.parse(data);
            if(response.status == true){
                console.log("AQUI");
                $("#tdmensagem").show();
                $("#mensagemFinal").hide();
                $('html, body').animate({scrollTop:0}, 'slow');
                $(".error").html('<blink class="titulo2">Atenção</span>');
                $(".mensagem-texto").html(response.msm);
                $(".mensagem-texto").show();
                $("#salvarRascunhoDFD").prop('disabled', true);
                $('#salvarRascunhoDFD').css('background-color', '#B5D2E8');
                $("#salvaDFD").prop('disabled', true);
                $('#salvaDFD').css('background-color', '#B5D2E8');
            }else{
                $("#tdmensagem").hide();
                // $("#mensagembloq").hide();
                // $(".mensagem-texto").hide();
                $(".error").html('');
                $("#salvarRascunhoDFD").prop('disabled', false);
                $('#salvarRascunhoDFD').css('background-color', 'none');
                $('#salvarRascunhoDFD').css('background-color', '#75ade6');
                $("#salvaDFD").prop('disabled', false);
                $('#salvaDFD').css('background-color', 'none');
                $('#salvaDFD').css('background-color', '#75ade6');
            }
        })
        $('#selectAreaReq').live("change", function(){ 
            const orgSelecionado = $('#selectAreaReq').val();
            const orgVinculacao = $("#orgvinculo").val();
            $.post("PostDadosDFD.php",{op:"SelecionaCNPJ", OrgSelecionado: orgSelecionado}, function(data){
                const response = JSON.parse(data);
                $("#cnpjAreaReq").hide(response.htmlCNPJ);
                $("#cnpjAreaReq").html(response.htmlCNPJ);
                $("#cnpjAreaReq").show(response.htmlCNPJ);
            })
            
            //Varifica liberação de DFD quando alterado
            // $("#op").val("checaBloqueio");
            // $.post("PostDadosDFD.php", $("#formIncluirDFD").serialize(), function(data){
            //     const response = JSON.parse(data);
            //     if(response.status == true){
            //         $("#tdmensagem").show();
            //         $("#mensagemFinal").hide();
            //         $('html, body').animate({scrollTop:0}, 'slow');
            //         $(".mensagem-texto").html(response.msm);
            //         $(".mensagem-texto").show();
            //         $("#salvarRascunhoDFD").prop('disabled', true);
            //         $('#salvarRascunhoDFD').css('background-color', '#B5D2E8');
            //         $("#salvaDFD").prop('disabled', true);
            //         $('#salvaDFD').css('background-color', '#B5D2E8');
            //     }else{
            //         $("#tdmensagem").hide();
            //         $("#mensagemFinal").hide();
            //         $(".mensagem-texto").hide();
            //         $("#salvarRascunhoDFD").prop('disabled', false);
            //         $('#salvarRascunhoDFD').css('background-color', 'none');
            //         $('#salvarRascunhoDFD').css('background-color', '#75ade6');
            //         $("#salvaDFD").prop('disabled', false);
            //         $('#salvaDFD').css('background-color', 'none');
            //         $('#salvaDFD').css('background-color', '#75ade6');
            //     }
            // })
            $("#formIncluirDFD").submit();
        })
        //Para quando o ano for alteravel
        // $('#selectAnoPCA').live("change", function(){ 
        //     const orgSelecionado = $('#selectAreaReq').val();
        //     const orgVinculacao = $("#orgvinculo").val();            
        //     //Varifica liberação de DFD quando alterado
        //     $("#op").val("checaBloqueio");
        //     $.post("PostDadosDFD.php", $("#formIncluirDFD").serialize(), function(data){
        //         const response = JSON.parse(data);
        //         if(response.status == true){
        //             $("#tdmensagem").show();
        //             $("#mensagemFinal").hide();
        //             $('html, body').animate({scrollTop:0}, 'slow');
        //             $(".mensagem-texto").html(response.msm);
        //             $(".mensagem-texto").show();
        //             $("#salvarRascunhoDFD").prop('disabled', true);
        //             $("#salvaDFD").prop('disabled', true);
        //         }else{
        //             $("#tdmensagem").hide();
        //             $("#mensagemFinal").hide();
        //             $(".mensagem-texto").hide();
        //         }
        //     })
        //     // $("#formIncluirDFD").submit();
        // })

        $('#grauPrioridade').on('change', function() {
            const grauPrioridade = $('#grauPrioridade').val();
            if (grauPrioridade == 1) {
                $('#trJustPrioridade').show();
            } else {
                $('#trJustPrioridade').hide();
            }
        })

        $("#lupaCodClasse").on('click', function(){
            $.post("PostDadosDFD.php",{op:"modalPesqClasse"}, function(data){
                $(".modal-content").html(data);
                $(".modal-content").attr("style","min-height: 119px;width: 853px;");
                $("#modal").show();
            });
        });
        // $("#lupaVincularDFD").on('click', function(){
        //     $.post("PostDadosDFD.php",{op:"modalViculaDFD"}, function(data){
        //         $(".modal-content").html(data);
        //         $(".modal-content").attr("style","min-height: 119px;width: 853px;");
        //         $("#modal").show();
        //     });selectAreaReq
        // });

        $(".btn-fecha-modal").live('click', function(){
            $("#modal").hide();
            window.localStorage.clear();
        });
        
        $("#RetirarItem").live('click', function(){
            $("#op").val("removeItem");
            $.post("PostDadosDFD.php", $("#formIncluirDFD").serialize(), function(data){
                const response = JSON.parse(data);
                if(response.status == true){
                    $("#op").val("");
                    $("#formIncluirDFD").submit();
                }
            });
        });
        $("#lupaJanelaVincular").live("click", function(){
            const areaReqSelect = $("#selectAreaReq").val()
            const AreaReqCod = $("#AreaReqCod").val()
            // var areaReq = (areaReqSelect != -1) ? areaReqSelect : AreaReqCod;
            const areaReq = (!AreaReqCod) ? areaReqSelect : AreaReqCod;
            console.log();
            // if(areaReqSelect == -1 && (AreaReqCod == null && AreaReqCod === undefined)){
            if(areaReqSelect == -1 && (AreaReqCod == null || AreaReqCod === undefined)){
                $("#tdmensagem").show();
                $("#mensagemFinal").hide();
                $('html, body').animate({scrollTop:0}, 'slow');
                $(".mensagem-texto").html("É necessário informar a Área Requisitante antes de Vincular um DFD");
            }else{
                $("#tdmensagem").hide();
                AbreJanela('JanelaVincular.php?areaReq='+areaReq,700,340)
            }
            
        })
        $("#desvincular").live("click", function(){
            $("#tdload").show();
            $("#op").val("desvinculaDFDIncluir");
            // Serializei o formulário para o post, dessa forma tudo vai chegar no switch, por isso na linha acima,
            // o op foi colocado direto em um input hidden dentro do form
            $.post("PostDadosDFD.php", $("#formIncluirDFD").serialize(), function(data) {
                const response = JSON.parse(data);
                if (response.status == false) {
                    $("#tdload").hide();
                    $("#tdmensagem").show();
                    $("#mensagemFinal").hide();
                    $('html, body').animate({scrollTop:0}, 'slow');
                    $(".mensagem-texto").html(response.msm);
                } else {
                    $("#op").val("");
                    $("#formIncluirDFD").submit();
                }
            });
            // window.close();
            $("CadIncluirDFD").submit();
            $("ConsDFD").submit();
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

        $("#radioClasse").live("click", function(){
            $("#incluirClasse").prop("disabled", false);
        })
        $("#Limpar").live('click', function(){
            $("#tdload").show();
            $("#op").val("Limpar");
            $("#rascunho").val("False");
            $.post("PostDadosDFD.php", $("#formIncluirDFD").serialize(), function(data){
                const response = JSON.parse(data);
                if(response.status == 404){
                    $('html, body').animate({scrollTop:0}, 'slow');
                    $('#tdload').hide();
                    $(".mensagem-texto").html(response.msm);
                    $(".error").html("Erro!");
                    $("#tdmensagem").show();
                    $("#mensagemFinal").hide();

                }else{
                    // $("#formIncluirDFD").reset();
                    $('html, body').animate({scrollTop:0}, 'slow');
                    // $('#tdload').hide();
                    setTimeout(function(){
                        window.location.href = "./CadIncluirDFD.php?";
                    }, 2000);
                }
            })
        })
        $("#salvaDFD").live('click', function(){
            $("#salvarRascunhoDFD").prop('disabled', true);
            $("#salvaDFD").prop('disabled', true);
            $("#tdload").show();
            $("#op").val("salvarDFD");
            $("#rascunho").val("False");
            $.post("PostDadosDFD.php", $("#formIncluirDFD").serialize(), function(data){
                const response = JSON.parse(data);
                if(response.status == 404){
                    $('html, body').animate({scrollTop:0}, 'slow');
                    $('#tdload').hide();
                    $(".mensagem-texto").html(response.msm);
                    $(".mensagem-texto").show();
                    $(".error").html("Erro!");
                    $("#tdmensagem").show();
                    $("#mensagemFinal").hide();
                    $("#salvarRascunhoDFD").prop('disabled', false);
                    $("#salvaDFD").prop('disabled', false);
                }else{
                    // $("#formIncluirDFD").reset();
                    $('html, body').animate({scrollTop:0}, 'slow');
                    // $('#tdload').hide();
                    setTimeout(function(){ 
                        window.location.href = "./CadIncluirDFD.php?";
                    }, 2000);
                }
            })
        })
        $("#salvarRascunhoDFD").live('click', function(){
            $("#salvarRascunhoDFD").prop('disabled', true);
            $("#salvaDFD").prop('disabled', true);
            $("#tdload").show();
            $("#op").val("salvarRascunhoDFD");
            $("#rascunho").val("true");
            $.post("PostDadosDFD.php", $("#formIncluirDFD").serialize(), function(data){
                const response = JSON.parse(data);
                if(response.status == 404){
                    $('html, body').animate({scrollTop:0}, 'slow');
                    $('#tdload').hide();
                    $(".mensagem-texto").html(response.msm);
                    $(".mensagem-texto").show();
                    $(".error").html("Erro!");
                    $("#tdmensagem").show();
                    $("#mensagemFinal").hide();
                    $("#salvarRascunhoDFD").prop('disabled', false);
                    $("#salvaDFD").prop('disabled', false);
                }else{
                    $('html, body').animate({scrollTop:0}, 'slow');
                    // $('#tdload').hide();
                    setTimeout(function(){ 
                        window.location.href = "./CadIncluirDFD.php?";
                    }, 2000);
                }
            })
        })
        
    })

        
    </script>
    <link rel="stylesheet" type="text/css" href="../estilo.css?v=<?php echo time();?>">

    <style>
        #labels{
            width: 250px;
            background-color:#DCEDF7;
        }
        input {
            font-size: 10.6667px; 
            text-transform: uppercase;
        }
        select {
            font-size: 10.6667px; 
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
    <form action="CadIncluirDFD.php" method="post" id="formIncluirDFD" name="CadIncluirDFD">
     <input type="hidden" name="op" id="op" value="">   
     <input type="hidden" name="rascunho" id="rascunho" value="">
    <br><br><br><br>
    <table cellpadding="3" border="0" summary="">
        <!-- Caminho -->
        <tr>
            <td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
            <td align="left" class="textonormal" colspan="2">
                <font class="titulo2">|</font>
                <a href="../index.php"><font color="#000000">Página Principal</font></a> > Planejamento > DFD > Incluir
            </td>
        </tr>
        <!-- Fim do Caminho-->
        
        <?php
            if(!empty($_SESSION['MensagemFinal'])){
                echo 
                '<!-- Erro -->
                <tr>
                    <td width="150"></td>
                    <td align="left" colspan="2" id="mensagemFinal">
                        <div class="mensagem">
                            <div class="error">
                            <blink class="titulo2">Atenção</span>
                            </div>
                            <span class="mensagem-texto-fim">
                            '.$_SESSION['MensagemFinal'].'
                            </span>
                        </div>
                    </td>
                </tr>
                <!-- Fim do Erro -->';

                unset($_SESSION['MensagemFinal']);
            }
            
            if(!empty($mensagemPeriodoBloqueado)){
                echo $mensagemPeriodoBloqueado;
            }
        ?>

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
                                    <td class="titulo3" colspan="17" align="center" bgcolor="#75ADE6" valign="middle"> <b>INCLUIR - DOCUMENTO DE FORMALIZAÇÃO DE DEMANDA (DFD)</b>
                                    </td>
                                </thead>
                                <th class="textonormal" colspan="17" align="left" style="font-size: 10.6667px;">
                                Preencha os dados abaixo e clique no botão "Incluir". Os itens obrigatórios estão com *.
                                O valor estimado refere-se ao valor estimado de cada classe.
                                </th>
                                <tr>
                                    <td align="left">
                                        <table cellpadding="0" cellspacing="0" border="0" bordercolor="#75ADE6" width="100%" summary="">
                                            <tr bgcolor="#bfdaf2">                                                    
                                                <!-- <td colspan="4"> -->    
                                                <table class="textonormal" id="scc_material" summary="" width="100%">
                                                    <!-- style="border: 1px solid #75ade6; border-radius: 4px;" -->
                                                    <tbody>                                                               
                                                        <tr>
                                                            <td class="textonormal" id="labels">
                                                                Ano do PCA*
                                                            </td>
                                                            <td>
                                                                <span>2024</span> 
                                                                <input type="hidden" name="selectAnoPCA" id="selectAnoPCA" value="2024">
                                                                <!-- Foi fixado o ano 2024 para correção futura da mecanica dos anos PCA, o hidden fixa o ano para o tratamento de dados selectAnoPCA-->
                                                                <!-- <select name="selectAnoPCA" id="selectAnoPCA" style="width:100px;">
                                                                    <?php //echo $optionsAno; ?>
                                                                </select> -->
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="textonormal" id="labels">
                                                                Área Requisitante*
                                                            </td>
                                                            <td id="AreaReqMult">
                                                                <select id='selectAreaReq' name='selectAreaReq' size='1' style='width:auto; font-size: 10.6667px;'>
                                                                </select>
                                                            </td>
                                                            <td id="AreaReqUnico"><span id="areaReq"></span></td>
                                                        </tr>
                                                        <tr>
                                                            <td class="textonormal" id="labels">
                                                                CNPJ*
                                                            </td>
                                                            <td>
                                                                <span name="cnpjAreaReq" id="cnpjAreaReq" value ="" style="display:none;">
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="textonormal" id="labels">
                                                                Classe*
                                                            </td>
                                                            <td>
                                                                <img id="lupaCodClasse" src="../midia/lupa.gif" border="0">
                                                                </br>
                                                                <span><?php echo ($classe->eclamsdesc) ? "$classe->eclamsdesc":''; ?></span>
                                                                <input type="hidden" name="cclamscodi" id="cclamscodi" value="<?php echo $classe->cclamscodi;?>">
                                                                <input type="hidden" name="ematepdesc" id="ematepdesc" value="<?php echo $classe->eclamsdesc;?>">
                                                            </td>
                                                        </tr>
                                                        <!-- <tr>
                                                            <td class="textonormal" id="labels">
                                                                Descrição da Classe
                                                            </td>
                                                            <td>
                                                                <span id="descricaoClasse" name="descricaoClasse"> <?php echo ($classe->eclamsdesc)?"$classe->eclamsdesc":''?></span>
                                                            </td>
                                                        </tr> -->
                                                        <tr>
                                                            <td class="textonormal" id="labels">
                                                                Descrição Sucinta da Demanda*
                                                            </td>
                                                            <td>
                                                                <textarea class="textonormal" style="font-size: 10.6667px; text-transform: uppercase;" id="descSuDemanda" name="descSuDemanda" cols=50 rows="4" maxlength="200"><?php echo $_POST['descSuDemanda']; ?></textarea>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="textonormal" id="labels">
                                                                Justificativa da Necessidade de Contratação*
                                                            </td>
                                                            <td>
                                                                <textarea class="textonormal" style="font-size: 10.6667px; text-transform: uppercase;" id="justContratacao" name="justContratacao" cols="50" rows="4" maxlength="1000"><?php echo $_POST['justContratacao']; ?></textarea>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="textonormal" id="labels">
                                                                Estimativa Preliminar do Valor da Contratação*
                                                            <td>
                                                                <input type="text" name="estValorContratacao" id="estValorContratacao" class="dinheiro4casas" value="<?php echo $_POST['estValorContratacao']; ?>" size=15 maxlength="18">
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="textonormal" id="labels">
                                                                Tipo de Processo de Contratação*
                                                            </td>
                                                            <td>
                                                                <select name="tpProcContratacao" id="tpProcContratacao" style="width:175px;">
                                                                    <option value=""  >Selecione o Tipo...</option>
                                                                    <option value="D" <?php echo ($_POST["tpProcContratacao"] == "D")? "selected":"" ; ?>>CONTRATAÇÃO DIRETA</option>
                                                                    <option value="L" <?php echo ($_POST["tpProcContratacao"] == "L")? "selected":"" ; ?>>LICITAÇÃO</option>
                                                                </select>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="textonormal" id="labels">
                                                                Data Estimada para Conclusão*
                                                            </td>
                                                            <td>
                                                                <input type="text" name="dtPretendidaConc" class="data" id="dtPretendidaConc" size=15 value="<?php echo !empty($_POST['dtPretendidaConc'])?$_POST['dtPretendidaConc']:'';?>">
                                                                <a id="calendarioDtPC" style="text-decoration: none" href="javascript:janela('../calendario.php?Formulario=CadIncluirDFD&amp;Campo=dtPretendidaConc','Calendario',220,170,1,0)"> 
                                                                    <img src="../midia/calendario.gif" border="0" alt="">
		                                                        </a>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="textonormal" id="labels">
                                                                Grau de Prioridade*
                                                            </td>
                                                            <td>
                                                                <select name="grauPrioridade" id="grauPrioridade" style="width:340px;">
                                                                    <option value="">Selecione o Grau de Prioridade...</option>
                                                                    <option value="1" <?php echo ($_POST['grauPrioridade'] == "1")? "selected":"" ; ?>>ALTO</option>
                                                                    <option value="2" <?php echo ($_POST['grauPrioridade'] == "2")? "selected":"" ; ?>>MÉDIO</option>
                                                                    <option value="3" <?php echo ($_POST['grauPrioridade'] == "3")? "selected":"" ; ?>>BAIXO</option>
                                                                </select>
                                                            </td>
                                                        </tr>
                                                        <!-- PRIORIDADE ALTA -->
                                                        <!-- ESSE CAMPO APARECERÁ SE O GRAU DE PRIORIDADE FOR ALTO -->
                                                        <tr id="trJustPrioridade" style="<?php echo ($_POST['grauPrioridade'] == "1")? "":"display:none;";?>">
                                                            <td class="textonormal" id="labels">
                                                                Justificativa para Prioridade Alta*
                                                            </td>
                                                            <td>
                                                                <textarea class="textonormal" style="font-size: 10.6667px; text-transform: uppercase;" id="justPriAlta" name="justPriAlta" cols="50" rows="4" maxlength="400"><?php echo $_POST['justPriAlta']; ?></textarea>
                                                            </td>
                                                        </tr>
                                                        <!-- FIM PRIORIDADE ALTA -->
                                                        <tr>
                                                            <td class="textonormal" id="labels">
                                                                DFDs vinculados
                                                            </td>
                                                            <td>
                                                                <a>
                                                                    <img id="lupaJanelaVincular" src="../midia/lupa.gif" border="0">
                                                                </a>
                                                            </td>
                                                            <!-- <td>
                                                                <a href="javascript:AbreJanela('JanelaVincular.php',1320,800);">
                                                                    <img src="../midia/lupa.gif" border="0">
                                                                </a>
                                                            </td> -->
                                                        </tr>
                                                        <?php if($_POST['selectAreaReq'] == 18 || $_SESSION['AreaReqCod'] == 18){ ?>
                                                        <tr name="compraCorporativa" id="compraCorporativa">
                                                            <td class="textonormal" id="labels">
                                                                Compra Corporativa
                                                            </td>
                                                            <td>
                                                                <select name="contratCorp" id="contratCorp" style="width:175px;">
                                                                    <option value="S" <?php echo ($_POST['contratCorp'] == "S")? "selected":"" ; ?>>SIM</option>
                                                                    <option value="N" <?php echo ($_POST['contratCorp'] == "N" || empty($_POST['contratCorp']))? "selected":"" ; ?>>NÃO</option>
                                                                </select>
                                                            </td>
                                                        </tr>
                                                            <?php }?>
                                                    </tbody>
                                                    <?php if($_SESSION['vincularDFD']){ ?>
                                                    <table id="vincularDFD" summary="" bgcolor="#bfdaf2" border="1" bordercolor="#75ADE6" width="100%">
                                                        <tbody>
                                                        <tr>
                                                            <td colspan="17" class="titulo3 itens_material" align="center" bgcolor="#75ADE6" valign="middle"> DFDS VINCULADOS</td>
                                                        </tr>
                                                        <!-- Headers ITENS DA SOLICITAÇÃO DE MATERIAL  -->
                                                        <tr class="head_principal">

                                                            <!-- <td class="textonormal" align="center" bgcolor="#DCEDF7" width="7%"><br /> ORD </td> -->

                                                            <?php
                                                            echo '     
                                                                <td class="textonormal" align="center" bgcolor="#DCEDF7" width="5%"><img src="../midia/linha.gif" alt="" border="0" height="1px"/> </td>                                                           
                                                                <td class="textonormal" align="center" bgcolor="#DCEDF7" width="12%" ><img src="../midia/linha.gif" alt="" border="0" height="1px"/> <br /> NÚMERO DO DFD</td>
                                                                <td class="textonormal" align="center" bgcolor="#DCEDF7" width="5%" ><img src="../midia/linha.gif" alt="" border="0" height="1px"/> <br /> ANO</td>
                                                                <td class="textonormal" align="center" bgcolor="#DCEDF7" width="35%" ><img src="../midia/linha.gif" alt="" border="0" height="1px"/> <br /> CLASSE</td>
                                                                <td class="textonormal" align="center" bgcolor="#DCEDF7" width="10%" ><img src="../midia/linha.gif" alt="" border="0" height="1px"/> <br /> SITUAÇÃO</td>';
                                                            ?>
                                                        </tr>
                                                        <?php
                                                        if(!empty($_SESSION["vincularDFD"])){
                                                            $i=0;
                                                            foreach($_SESSION["vincularDFD"] as $dfd){
                                                                echo '<tr class="head_principal">  
                                                                        <td class="textonormal" align="center" style="width:5%;" ><img src="../midia/linha.gif" alt="" bgcolor="#DCEDF7" border="0" height="1px"/> <br /><input type="checkbox" name="checkVincular[]" id="checkVincular" value="'.$dfd->cpldfdsequ.'"></td>                                                                                                                                          
                                                                        <td class="textonormal" align="center"  width="12%" ><img src="../midia/linha.gif" alt="" bgcolor="#DCEDF7" border="0" height="1px"/> <br />'.$dfd->cpldfdnumf.'</td>
                                                                        <td class="textonormal" align="center"  width="5%" ><img src="../midia/linha.gif" alt="" bgcolor="#DCEDF7" border="0" height="1px"/> <br />'.$dfd->apldfdanod.'</td>
                                                                        <td class="textonormal" align="center"  width="35%" ><img src="../midia/linha.gif" alt="" bgcolor="#DCEDF7" border="0" height="1px"/> <br />'.$dfd->descclasse.'</td>
                                                                        <td class="textonormal" align="center"  width="10%" ><img src="../midia/linha.gif" alt="" bgcolor="#DCEDF7" border="0" height="1px"/> <br />'.$dfd->eplsitnome.'</td>                                                                    
                                                                    </tr>';
                                                            }

                                                        }?>
                                                        <tr>
                                                            <td class="textonormal" colspan="7" align="center">
                                                            <button id="desvincular" name="Remover" value="Desvincular DFD" class="botao"  type="button">Desvincular DFD</button>
                                                            </td>
                                                        </tr>
                                                        </tbody>
                                                    </table>
                                                    <?php } ?>
                                
                                                    <table id="scc_material" summary="" bgcolor="#bfdaf2" border="1" bordercolor="#75ADE6" width="100%">
                                                        
                                                        <tbody>
                                                            <tr>
                                                                <td colspan="17" class="titulo3 itens_material" align="center" bgcolor="#75ADE6" valign="middle">ITENS DO DOCUMENTO</td>
                                                            </tr>
                                                            <!-- Headers ITENS DA SOLICITAÇÃO DE MATERIAL  -->
                                                            <tr class="head_principal">

                                                                <!-- <td class="textonormal" align="center" bgcolor="#DCEDF7" width="7%"><br /> ORD </td> -->
                                                                
                                                                <?php
                                                                echo '     
                                                                <td class="textonormal" align="center" bgcolor="#DCEDF7" width="5%"><img src="../midia/linha.gif" alt="" border="0" height="1px"/> </td>                                                           
                                                                <td class="textonormal" align="center" bgcolor="#DCEDF7" width="35%" ><img src="../midia/linha.gif" alt="" border="0" height="1px"/> <br /> DESCRIÇÃO</td>
                                                                <td class="textonormal" align="center" bgcolor="#DCEDF7" width="10%" ><img src="../midia/linha.gif" alt="" border="0" height="1px"/> <br /> CÓDIGO</td>';
                                                                ?>
                                                            </tr>
                                                            <?php 
                                                            if(!empty($_SESSION["dadosDaPagina"]["item"])){
                                                                $i = 0;
                                                                foreach($_SESSION["dadosDaPagina"]["item"] as $item){
                                                                    echo '<tr class="head_principal">  
                                                                        <td class="textonormal" align="center" style="width:5%;" ><img src="../midia/linha.gif" alt="" bgcolor="#DCEDF7" border="0" height="1px"/> <br /><input type="checkbox" name="chkbxItem[]" id="chkbxItem" value="'.$i.'"></td>                                                                      
                                                                    <td class="textonormal" align="center"  width="35%" ><img src="../midia/linha.gif" alt="" bgcolor="#DCEDF7" border="0" height="1px"/> <br />'.$item["DescricaoMaterialServicoBanco"].'</td>
                                                                    <td class="textonormal" align="center"  width="10%" ><img src="../midia/linha.gif" alt="" bgcolor="#DCEDF7" border="0" height="1px"/> <br />'.$item["CodRedMaterialServicoBanco"].'</td>
                                                                    </tr>';
                                                                    $i++;
                                                                }
                                                                }
                                                                
                                                                if (!empty($classe->cclamscodi) && !empty($classe->cgrumscodi)){
                                                                    $funcaoJS = "javascript:AbreJanelaItem('CadIncluirItemPlanejamento.php?ProgramaOrigem=CadIncluirDFD&amp;PesqApenas=C&amp;classePredefinida=".$classe->cclamscodi."&amp;Grupo=".$classe->cgrumscodi."&amp;TipoGrupo=".$tipoItem."', 700, 350);";
                                                                }else{
                                                                    $funcaoJS = "javascript:ClasseNaoInformada('Informe: Classe.');";
                                                                }
                                                                
                                                                ?>

                                                            <tr>
                                                                <td class="textonormal" colspan="7" align="center">
                                                                        <button id="IncluirItem" name="IncluirItem" value="Incluir Item" class="botao" onclick="<?php echo $funcaoJS; ?>" type="button">Incluir Item</button>
                                                                        <button id="RetirarItem" name="RetirarItem" value="Retirar Item" class="botao" type="button">Retirar Item</button>
                                                                </td>
                                                            </tr>
                                                                
                                                        </tbody>
                                                    </table>
                                                    <tr>
                                                        <td colspan="8">
                                                            <button type="button" name="salvarRascunhoDFD" class="botao" id="salvarRascunhoDFD" style="float:right; margin-left: 3px">Salvar Rascunho</button>
                                                            <button type="button" name="salvaDFD" class="botao" id="salvaDFD" style="float:right; margin-left 3px">Salvar</button>
                                                            <button type="button" name="Limpar" class="botao" id="Limpar" onclick="limpar()" style="float:right; margin-right: 3px">Limpar</button>
                                                        </td>
                                                    </tr>
                                                </table>
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
        <div class="modal" id="modal"> 
            <div class="modal-content" >
            
            </div>
        </div> 
    </table>    

</html>
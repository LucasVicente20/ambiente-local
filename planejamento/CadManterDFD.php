<?php
/**
 * Portal de Compras
 * Programa: CadManterDFD.php
 * Autor: Diógenes Dantas | João Madson
 * Data: 12/12/2022
 * Objetivo: Programa de consulta de DFD
 * Tarefa Redmine: #275345
 * -------------------------------------------------------------------
  * Alterado: Lucas Vicente
  * Data: 05/01/2023
  * Tarefa: #277231
*--------------------------------------------------------------------
  * Alterado: Lucas Vicente
  * Data:09/01/2023
  * Tarefa: Ajuste na regra do Configurador DFD
 * -------------------------------------------------------------------
   * Alterado: João Madson   
  * Data: 09/01/2023
  * Tarefa: #277372
 */

# Acesso ao arquivo de funções #
include "../funcoes.php";
include "FuncoesAbasPlanejamento.php";

session_start();
include "ClassPlanejamento.php";
$objPlanejamento = new Planejamento();

    if ($_SERVER['REQUEST_METHOD'] == "GET") {
        $DFD = $_GET['dfdSelected'];
        unset($_SESSION['DFD']);
        unset($_SESSION['agrupado']);
        unset($_SESSION['historico']);
        unset($_SESSION['itensManter']);
        unset($_SESSION['item']);
        unset($_SESSION['MensagemManter']);
        unset($_SESSION['classeSelecionadada']);
        unset($_SESSION['itensDFD']);
        unset($_SESSION['classeAlterada']);
        unset($_SESSION['servico']);
        unset($_SESSION['vincularDFD']);
        unset($_SESSION['classe']);
        unset($_SESSION['novoSequ']);
        unset($_SESSION['ultHist']);
        unset($_SESSION['Bloqueio']); 
    } else if ($_SERVER['REQUEST_METHOD'] == "POST") {
        $DFD = $_POST['dfdSelected'];
        if (!empty($_SESSION['classe']) && !is_null($_POST['radioClasse'])) {
            $selecClasse = $_POST['radioClasse'];
            $classe = $_SESSION['classe'][$selecClasse];
            $classe->alteraClasse = '<input type="hidden" name="chaveNovaClasse" id="chaveNovaClasse" value="true">';
            $classe->fgrumstipo = $_SESSION['servico'];
            $_SESSION['classeSelecionadada'] = $classe;
            $_SESSION['classeAlterada'] = true;
            unset($_SESSION['classe']);
        }
        if(!empty($_SESSION['classeSelecionadada'])){
            $classe = $_SESSION['classeSelecionadada'];
        }

    }
    
    if (!empty($DFD) && empty($_SESSION['DFD'])) {

        $_SESSION['DFD'] = $dadosDFD = $objPlanejamento->consultaDFD($DFD);
        $_SESSION['agrupado'] = $agrupamento = $objPlanejamento->consultaDFDAgrupamento($dadosDFD->cpldfdsequ);
        $_SESSION['classeSelecionadada'] = $dadosClasseOriginal = $objPlanejamento->consultaClasseMaterial($dadosDFD->cclamscodi, $dadosDFD->cgrumscodi);
        $classe->cclamscodi = $dadosClasseOriginal->cclamscodi;
        $classe->cgrumscodi = $dadosClasseOriginal->cgrumscodi;
        $classe->eclamsdesc = $dadosClasseOriginal->eclamsdesc;
        $classe->fgrumstipo = $dadosClasseOriginal->fgrumstipo;
        
        if(empty($_SESSION['vincularDFD']) && !empty($dadosDFD->cplvincodi)){
            $_SESSION['vincularDFD'] = $objPlanejamento->consultaDFDcodigoVinculo($dadosDFD->cpldfdsequ, $dadosDFD->cplvincodi);
        }
        $itens = $objPlanejamento->consultaItens($DFD);
        //pega o histórico da sessão sempre que recarregar a página
        $_SESSION['historico'] = $objPlanejamento->consultaHistorico($DFD);
        $_SESSION['ultHist'] = $ultimoHist = $objPlanejamento->consultaUltimoHistorico($DFD);

        $_SESSION['itensManter'] = $itens = $objPlanejamento->consultaItens($DFD);
    } else if (!empty($_SESSION['DFD'])) {
        $dadosDFD = $_SESSION['DFD'];
        $agrupamento = $_SESSION['agrupado'];
        $ultimoHist = $_SESSION['ultHist'];
        $itens = $_SESSION['itensDFD'];
        $codigoVinculo =  $objPlanejamento->consultaCodigoVinculo($dadosDFD->cpldfdsequ);
        //A sessão de retirada precisa ser checada para garantir que a query de vinculo não existia previamente.
        if(empty($_SESSION['vincularDFD']) && !empty($codigoVinculo->cplvincodi) && empty($_SESSION['retirarVinculoBanco'])){
            $_SESSION['vincularDFD'] = $objPlanejamento->consultaDFDcodigoVinculo($dadosDFD->cpldfdsequ, $codigoVinculo->cplvincodi);
            
        }
    }
    
    //verifica se a classe foi alterada para remover os itens da sessão
    if($_SESSION['classeAlterada'] == true){
        //verifica se a classe é igual a anterior ou não, se for coloca para remover, senão remove a chave de remossao do array
        foreach($itens as $key=>$item){
            if($classe->cclamscodi != $dadosDFD->cclamscodi || $classe->cgrumscodi != $dadosDFD->cgrumscodi){
                unset($itens);
                unset($_SESSION['itensDFD']);
                unset($_SESSION['itensManter']);
                $_SESSION['classeAlterada'] = false; // para parar de entrar aqui
            }
        }
    }
    //caso venha item novo
    if(!empty($_SESSION['item'])){
        if(!empty($itens)){
            foreach($itens as $key => $item){
                $posicao = $key;//na ultima ocorrencia pega o maior
            }
            $posicao++;
        }else{
            $posicao = 0;
        }
        if(empty($_SESSION['novoSequ'])){
        $novoSequ = $objPlanejamento->maxSeqItem($dadosDFD->cpldfdsequ);
        }else{
            $novoSequ = $_SESSION['novoSequ'];
        }
        $novoSequ++;
        $itemConta = $itens;
        foreach($_SESSION['item'] as $key=>$itemSessao){
            $matOuServ =  $itemSessao['TipoGrupoBanco'];
            $novoSeqMatServ = $itemSessao['CodRedMaterialServicoBanco'];
            if(!empty($itens)){
                if($matOuServ == "M"){
                    if(!empty($novoSeqMatServ)){
                        foreach($itemConta as $key => $item){
                            if($novoSeqMatServ == $item->cmatepsequ){
                                $validaNovoItem = false;
                                break;
                            }else{
                                $validaNovoItem = true;
                            }
                        }
                    }
                }
                if($matOuServ == "S"){
                    if(!empty($novoSeqMatServ)){
                        foreach($itemConta as $key => $item){
                            if($novoSeqMatServ == $item->cservpsequ){
                                $validaNovoItem = false;
                                break;
                            }else{
                                $validaNovoItem = true;
                            }
                        }
                    }
                }
            }else{
                $validaNovoItem = true;
            }
            if($validaNovoItem == true){
                if($matOuServ == "M"){
                    $itens[$posicao]->cplitecodi =  $novoSequ;
                    $itens[$posicao]->cmatepsequ =  $itemSessao['CodRedMaterialServicoBanco'];
                    $itens[$posicao]->ematepdesc =  $itemSessao['DescricaoMaterialServicoBanco'];
                }else{
                    $itens[$posicao]->cplitecodi =  $novoSequ;
                    $itens[$posicao]->cservpsequ =  $itemSessao['CodRedMaterialServicoBanco'];
                    $itens[$posicao]->eservpdesc =  $itemSessao['DescricaoMaterialServicoBanco'];
                }
            }
            $posicao++;
            $_SESSION['novoSequ'] = $novoSequ++;
        }
        unset($_SESSION['item']);
    }
    $_SESSION['itensDFD'] = array_unique($itens, SORT_REGULAR);//Pega tudo do Item para a SESSÂO
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

function Submete(Destino) {
    document.ConsDFD.Destino.value = Destino;
    document.ConsDFD.submit();
}
function AbreJanela(url,largura,altura) {
    window.open(url,'pagina','status=no,scrollbars=no,left=20,top=150,width='+largura+',height='+altura);
}
function AbreJanelaItem(url,largura,altura){
    window.open(url,'paginaitem','status=no,scrollbars=yes,left=90,top=150,width='+largura+',height='+altura);
}
$(document).ready(function() { 
    $('.data').mask('99/99/9999');

    //Varifica liberação de DFD quando alterado
    $("#op").val("checaBloqueio");
    $.post("PostDadosManterDFD.php", $("#formCadManterDFD").serialize(), function(data){
        const response = JSON.parse(data);
        if(response.status == true){
            $("#tdmensagem").show();
            $("#mensagemFinal").hide();
            $('html, body').animate({scrollTop:0}, 'slow');
            $(".error").html('<blink class="titulo2">Atenção</span>');
            $(".mensagem-texto").html(response.msm);
            $(".mensagem-texto").show();
            $('#Manter').attr('disabled', true);
            $('#Manter').css('background-color', 'none');
            $('#Manter').css('background-color', '#B5D2E8');
            $('#ManterRascunho').attr('disabled', true);
            $('#ManterRascunho').css('background-color', 'none');
            $('#ManterRascunho').css('background-color', '#B5D2E8');
            $('#Excluir').attr('disabled', true);
            $('#Excluir').css('background-color', 'none');
            $('#Excluir').css('background-color', '#B5D2E8');
        }else{
            $("#tdmensagem").hide();
            $("#mensagemFinal").hide();
            $(".mensagem-texto").hide();
            $(".error").html('');
            $('#Manter').attr('disabled', false);
            $('#Manter').css('background-color', 'none');
            $('#Manter').css('background-color', '#75ade6');
            $('#ManterRascunho').attr('disabled', false);
            $('#ManterRascunho').css('background-color', 'none');
            $('#ManterRascunho').css('background-color', '#75ade6');
            $('#Excluir').attr('disabled', false);
            $('#Excluir').css('background-color', 'none');
            $('#Excluir').css('background-color', '#75ade6');
        }
    })
    
    $('#grauPrioridade').on('change', function() {
        const grauPrioridade = $('#grauPrioridade').val();
        if (grauPrioridade == 1) {
            $('#trJustPrioridade').show();
        } else {
            $('#trJustPrioridade').hide();
        }
    })
     $("#Manter").live('click', function(){
        $('#Manter').attr('disabled', true);
        $('#ManterRascunho').attr('disabled', true);
        $('#Excluir').attr('disabled', true);
        $('html, body').animate({scrollTop:0}, 'slow');
        $("#tdload").show();
        $("#op").val("AlterarDFD");
        $("#rascunho").val("false");
        $.post("PostDadosManterDFD.php", $("#formCadManterDFD").serialize(), function(data){
            const response = JSON.parse(data);
            if(response.status == false){
                $('#tdload').hide();
                $(".mensagem-texto").html(response.msm);
                $(".mensagem-texto").show();
                $(".error").html("Erro!");
                $("#tdmensagem").show();
                $('#Manter').attr('disabled', false);
                $('#ManterRascunho').attr('disabled', false);
                $('#Excluir').attr('disabled', false);
            }else{
                window.location.href = "./ConsSelecionarManterDFD.php?";
            }
        })
    })
     $("#ManterRascunho").live('click', function(){
        $('#Manter').attr('disabled', true);
        $('#ManterRascunho').attr('disabled', true);
        $('#Excluir').attr('disabled', true);
        $('html, body').animate({scrollTop:0}, 'slow');
        $("#tdload").show();
        $("#op").val("AlterarDFD");
        $("#rascunho").val("true");
        $.post("PostDadosManterDFD.php", $("#formCadManterDFD").serialize(), function(data){
            const response = JSON.parse(data);
            if(response.status == false){
                $('#tdload').hide();
                $(".mensagem-texto").html(response.msm);
                $(".mensagem-texto").show();
                $(".error").html("Erro!");
                $("#tdmensagem").show();
                $('#Manter').attr('disabled', false);
                $('#ManterRascunho').attr('disabled', false);
                $('#Excluir').attr('disabled', false);
            }else{
                window.location.href = "./ConsSelecionarManterDFD.php?";
            }
        })
    })
    $("#Excluir").live('click', function(){
        var confirmation = confirm("Tem certeza que deseja excluir o DFD?");
        
        if(confirmation == true){   
            $('#Manter').attr('disabled', true);
            $('#ManterRascunho').attr('disabled', true);
            $('#Excluir').attr('disabled', true);

            $("#tdload").show();
            $("#op").val("ExcluirDFD");
            $.post("PostDadosManterDFD.php", $("#formCadManterDFD").serialize(), function(data){
                const response = JSON.parse(data);
                if(response.status == false){
                    $('html, body').animate({scrollTop:0}, 'slow');
                    $('#tdload').hide();
                    $(".mensagem-texto").html(response.msm);
                    $(".mensagem-texto").show();
                    $(".error").html("Erro!");
                    $("#tdmensagem").show();
                    $('#Manter').attr('disabled', false);
                    $('#ManterRascunho').attr('disabled', false);
                    $('#Excluir').attr('disabled', false);
                }else{
                    window.location.href = "./ConsSelecionarManterDFD.php?";
                }
            })
        }
    })
    $("#Voltar").live('click', function(){
       window.location.href = "./ConsSelecionarManterDFD.php?";
    })

    $("#RetirarItem").live('click', function(){
        $('#tdload').show();
        $('#op').val('retirarItem');
        $.post("PostDadosManterDFD.php", $("#formCadManterDFD").serialize(), function(data){
            const response = JSON.parse(data);
            if(response.status == true){
                $('html, body').animate({scrollTop:0}, 'slow');
                $('#tdload').hide();
                $(".mensagem-texto").html(response.msm);
                $(".mensagem-texto").show();
                $(".error").html("Atenção!");
                $("#tdmensagem").show();
                $("#op").val("");
                $("#formCadManterDFD").submit();
            }
        })
    })
    $("#lupaCodClasse").on('click', function(){
            $.post("PostDadosManterDFD.php",{op:"modalPesqClasse"}, function(data){
                $(".modal-content").html(data);
                $(".modal-content").attr("style","min-height: 119px;width: 853px;");
                $("#modal").show();
            });
        });

        $(".btn-fecha-modal").live('click', function(){
            $("#modal").hide();
            window.localStorage.clear();
        });
    $("#lupaClasse").live('click',function(){
        const formAction = $("#formAction").val();
        $("#LoadPesqScc").show();
        //Limpa a mensagem quando refaz a pesquisa
        $("#tdmensagemM").hide();
        var opPesq = $("#OpcaoPesquisaClasse").val();
        var dadoPesquisar = $("#ClasseDescricaoDireta").val();
        $.post("PostDadosManterDFD.php",{
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
                    $(".mensagem-texto").show();
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
        $.post("PostDadosManterDFD.php",
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
                    $(".mensagem-texto").show();
                }else{
                    $("#LoadPesqScc").hide();
                    $("#pesqDivModal").html(response.msm);
                }
            }
        );
    });
    $("#lupaServico").live('click',function(){
        const formAction = $("#formAction").val();
        $("#LoadPesqScc").show();
        //Limpa a mensagem quando refaz a pesquisa
        $("#tdmensagemM").hide();
        var opPesq = $("#OpcaoPesquisaServico").val();
        var dadoPesquisar = $("#ServicoDescricaoDireta").val();
        $.post("PostDadosManterDFD.php",
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
                    $(".mensagem-texto").show();
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
})

    $("#lupaVincularDFD").on('click', function(){
        $.post("PostDadosDFD.php",{op:"modalDFD"}, function(data){
            $(".modal-content").html(data);
            $(".modal-content").attr("style","min-height: 119px;width: 853px;");
            $("#modal").show();
        });
    });
    $("#desvincular").live("click", function(){
        $("#tdload").show();
        $("#op").val("desvinculaDFD");
        // Serializei o formulário para o post, dessa forma tudo vai chegar no switch, por isso na linha acima,
        // o op foi colocado direto em um input hidden dentro do form
        $.post("PostDadosManterDFD.php", $("#formCadManterDFD").serialize(), function(data) {
            const response = JSON.parse(data);
            if (response.status == false) {
                $("#tdload").hide();
                $("#tdmensagem").show();
                $('html, body').animate({scrollTop:0}, 'slow');
                $(".mensagem-texto").html(response.msm);
                $(".mensagem-texto").show();
            } else {
                $("#op").val("");
                $("#formCadManterDFD").submit();
            }
        });
    });

    $("#AlterarDFD").live('click', function(){
        $('html, body').animate({scrollTop:0}, 'slow');
        $("#tdload").show();
        $("#op").val("AlterarDFD");
        $.post("PostDadosManterDFD.php", $("#formCadManterDFD").serialize(), function(data){
            const response = JSON.parse(data);
            if(response.status == 404){
                $('#tdload').hide();
                $(".mensagem-texto").html(response.msm);
                $(".mensagem-texto").show();
                $(".error").html("Erro!");
                $("#tdmensagem").show();
            }else{
                setTimeout(function(){ 
                    window.location.href = "./ConsSelecionarManterDFD.php?";
                }, 2000);
            }
        })
    })
    $("#Voltar").live('click', function(){
       window.location.href = "./ConsSelecionarManterDFD.php?";
    })

<?php MenuAcesso(); ?>
    
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css?v=<?php echo time();?>">

<style>
    #labels{
        width: 250px;
        background-color:#DCEDF7;
    }
</style>

<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="CadManterDFD.php" method="post" id="formCadManterDFD" name="CadManterDFD">
<input type="hidden" name="op" id="op" value="">   
<input type="hidden" name="rascunho" id="rascunho" value="">   
<input type="hidden" name="Destino" id="Destino" value="">
<input type="hidden" name="dfdSelected" id="dfdSelected" value="<?php echo $DFD;?>">
<input type="hidden" name="formAction" id="formAction" value="CadManterDFD.php">
<input type="hidden" name="orgDfd" id="orgDfd" value=<?php echo $dadosDFD->corglicodi;?>>
<input type="hidden" name="anoDfd" id="anoDfd" value=<?php echo $dadosDFD->apldfdanod;?>>
<br><br><br><br>
<table cellpadding="3" border="0" summary="">
    <!-- Caminho -->
    <tr>
        <td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
        <td align="left" class="textonormal" colspan="2">
            <font class="titulo2">|</font>
            <a href="../index.php"><font color="#000000">Página Principal</font></a> > Planejamento > DFD > Manter
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
        <td width="100" display="none"></td>
        <td class="textonormal">
            <table cellspacing="0" cellpadding="3" summary="" width="1024px">
                <tr>
                    <td class="textonormal">
                        <table cellpadding="3" cellspacing="0" summary="" class="textonormal" border="1" bordercolor="#75ADE6" bgcolor="#FFFFFF" width="1024px">
                            <tr>
                                <td class="titulo3" colspan="17" align="center" bgcolor="#75ADE6" valign="middle"> <b>MANTER - DOCUMENTO DE FORMALIZAÇÃO DA DEMANDA (DFD)</b>
                                </td>
                            </tr>
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
                                                            <?php 
                                                                echo "<span>$dadosDFD->apldfdanod</span>";
                                                            ?>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="textonormal" id="labels">
                                                            Número do DFD*
                                                        </td>
                                                        <td>
                                                            <span><?php echo $dadosDFD->cpldfdnumf;?></span>
                                                        </td>
                                                    </tr>
                                                    <?php
                                                        if(!empty($agrupamento->cplagdsequ)){
                                                            echo '<tr>
                                                                    <td class="textonormal" id="labels">
                                                                        DFD Agrupado
                                                                    </td>
                                                                    <td>
                                                                        <span>
                                                                            SIM
                                                                        </span>
                                                                    </td>
                                                                </tr>';
                                                            echo '<tr>
                                                                    <td class="textonormal" id="labels">
                                                                        Motivo do Agrupamento
                                                                    </td>
                                                                    <td>
                                                                        <span style="text-transform: uppercase;">'.$agrupamento->eplagdmoti.'</span>
                                                                    </td>
                                                                </tr>';
                                                        }
                                                    ?>
                                                    <?php
                                                        if($ultimoHist[0]->cplsitcodi == 4){
                                                            echo '<tr>
                                                                    <td class="textonormal" id="labels">
                                                                        DFD Devolvido
                                                                    </td>
                                                                    <td>
                                                                        <span>
                                                                            SIM
                                                                        </span>
                                                                    </td>
                                                                </tr>';
                                                            echo '<tr>
                                                                    <td class="textonormal" id="labels">
                                                                        Motivo da devolução
                                                                    </td>
                                                                    <td>
                                                                        <span style="text-transform: uppercase;">'.$ultimoHist[0]->eplhsijust.'</span>
                                                                    </td>
                                                                </tr>';
                                                        }
                                                    ?>
                                                    <tr>
                                                        <td class="textonormal" id="labels">
                                                            Área Requisitante*
                                                        </td>
                                                        <td>
                                                            <span id="areaReq"><?php echo $dadosDFD->descorgao;?></span>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="textonormal" id="labels">
                                                            CNPJ*
                                                        </td>
                                                        <td>
                                                            <span name="cnpjAreaReq" id="cnpjAreaReq">
                                                            <?php echo $objPlanejamento->MascarasCPFCNPJ($dadosDFD->cnpjorgao);?>
                                                            </span>
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
                                                            <input type="hidden" name="cgrumscodi" id="cgrumscodi" value="<?php echo $classe->cgrumscodi;?>">
                                                            <?php echo $classe->alteraClasse;?>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="textonormal" id="labels">
                                                            Descrição Sucinta da Demanda*
                                                        </td>
                                                        <td>
                                                            <textarea class="textonormal" style="font-size: 10.6667px; text-transform: uppercase;" id="descSuDemanda" name="descSuDemanda" cols=50 rows="4" maxlength="200"><?php  if($_POST['descSuDemanda'] == "" && !is_null($_POST['descSuDemanda'])){echo $_POST['descSuDemanda'];}else{echo !empty($_POST['descSuDemanda']) ? $_POST['descSuDemanda'] : $dadosDFD->epldfddesc;}; ?></textarea>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="textonormal" id="labels">
                                                            Justificativa da Necessidade de Contratação*
                                                        </td>
                                                        <td>
                                                            <textarea class="textonormal" style="font-size: 10.6667px; text-transform: uppercase;" id="justContratacao" name="justContratacao" cols="50" rows="4" maxlength="1000"><?php if($_POST['justContratacao'] == "" && !is_null($_POST['justContratacao'])){echo $_POST['justContratacao'];}else{echo !empty($_POST['justContratacao']) ? $_POST['justContratacao'] : $dadosDFD->epldfdjust;}; ?></textarea>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="textonormal" id="labels">
                                                            Estimativa Preliminar do Valor da Contratação*
                                                        </td>
                                                        <td>
                                                            <input class="dinheiro4casas" style="font-size: 10.6667px;" type="text" name="estValorContratacao" id="estValorContratacao"  value="<?php echo !empty($_POST['estValorContratacao'])? $_POST['estValorContratacao']: number_format($dadosDFD->cpldfdvest,4,',','.'); ?>" size=15 maxlength="18">
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="textonormal" id="labels">
                                                            Tipo de Processo de Contratação*
                                                        </td>
                                                        <td>
                                                            <?php $tpProcContratacao = !empty($_POST["tpProcContratacao"]) ? $_POST["tpProcContratacao"] : $dadosDFD->fpldfdtpct; ?>
                                                            <select name="tpProcContratacao" id="tpProcContratacao" style="width:175px; font-size: 10.6667px;">
                                                                <option  style="font-size: 10.6667px;" value=""  >Selecione o tipo</option>
                                                                <option  style="font-size: 10.6667px;" value="D" <?php echo ($tpProcContratacao == "D")? "selected":"" ; ?>>CONTRATAÇÃO DIRETA</option>
                                                                <option  style="font-size: 10.6667px;" value="L" <?php echo ($tpProcContratacao == "L")? "selected":"" ; ?>>LICITAÇÃO</option>
                                                            </select>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="textonormal" id="labels">
                                                            Data Estimada para Conclusão
                                                        </td>
                                                        <td>
                                                            <input style="font-size: 10.6667px;" type="text" name="dtPretendidaConc" class="data" id="dtPretendidaConc" size=15 value="<?php 
                                                            $dataEst = !empty($dadosDFD->dpldfdpret)?date('d/m/Y', strtotime($dadosDFD->dpldfdpret)):"";
                                                            echo !empty($_POST['dtPretendidaConc'])?$_POST['dtPretendidaConc']:$dataEst;?>">
                                                            <a id="calendarioVig" style="text-decoration: none" href="javascript:janela('../calendario.php?Formulario=CadManterDFD&amp;Campo=dtPretendidaConc','Calendario',220,170,1,0)"> 
                                                                <img src="../midia/calendario.gif" border="0" alt="">
                                                            </a>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="textonormal" id="labels">
                                                            Grau de Prioridade*
                                                        </td>
                                                        <td>
                                                            <?php $grauPrioridade = !empty($_POST['grauPrioridade'])? $_POST['grauPrioridade']: $dadosDFD->fpldfdgrau;?>
                                                            <select name="grauPrioridade" id="grauPrioridade" style="width:340px; font-size: 10.6667px;">
                                                                <option style="font-size: 10.6667px;" value="">Selecione o grau de prioridade</option>
                                                                <option style="font-size: 10.6667px;" value="1" <?php echo ($grauPrioridade == "1")? "selected":"" ; ?>>ALTO</option>
                                                                <option style="font-size: 10.6667px;" value="2" <?php echo ($grauPrioridade == "2")? "selected":"" ; ?>>MÉDIO</option>
                                                                <option style="font-size: 10.6667px;" value="3" <?php echo ($grauPrioridade == "3")? "selected":"" ; ?>>BAIXO</option>
                                                            </select>
                                                        </td>
                                                    </tr>
                                                    <!-- PRIORIDADE ALTA -->
                                                    <!-- ESSE CAMPO APARECERÁ SE O GRAU DE PRIORIDADE FOR ALTO -->
                                                    <tr id="trJustPrioridade" style="<?php echo ($grauPrioridade == "1")? "":"display:none;";?>">
                                                        <td class="textonormal" id="labels">
                                                            Justificativa para Prioridade Alta*
                                                        </td>
                                                        <td>
                                                            <textarea class="textonormal" style="font-size: 10.6667px; text-transform: uppercase;" id="justPriAlta" name="justPriAlta" cols="50" rows="4" maxlength="400"><?php if($_POST['justPriAlta'] == "" && !is_null($_POST['justContratacao'])){echo $_POST['justPriAlta'];}else{echo !empty($_POST['justPriAlta'])? $_POST['justPriAlta']: $dadosDFD->epldfdjusp;} ?></textarea>
                                                        </td>
                                                    </tr>
                                                    <!-- FIM PRIORIDADE ALTA -->
                                                    <tr>
                                                        <td class="textonormal" id="labels">
                                                            DFDs vinculados
                                                        </td>
                                                        <td>
                                                            <a href="javascript:AbreJanela('JanelaVincular.php?&Programa=Manter&amp;areaReq=<?php echo $dadosDFD->corglicodi; ?>&amp;dfdsequ=<?php echo $dadosDFD->cpldfdsequ; ?>',700,370);">
                                                                <img src="../midia/lupa.gif" border="0">
                                                            </a>
                                                        </td>
                                                    </tr>
                                                    <tr <?php echo ($dadosDFD->corglicodi != 18)? 'style = "display:none;"':''?>>
                                                        <td class="textonormal" id="labels">
                                                            Compra Corporativa
                                                        </td>
                                                        <td>
                                                            <?php  $grauPrioridade = !empty($_POST['contratCorp'])? $_POST['contratCorp'] : $dadosDFD->fpldfdcorp; ?>
                                                            <select name="contratCorp" id="contratCorp" style="width:175px; font-size: 10.6667px;">
                                                                <option value="S" style="font-size: 10.6667px;" <?php echo ($grauPrioridade == "S")? "selected":"" ; ?>>SIM</option>
                                                                <option value="N" style="font-size: 10.6667px;"<?php echo ($grauPrioridade == "N")? "selected":"" ; ?>>NÃO</option>
                                                            </select>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <?php if($_SESSION['vincularDFD']){ ?>
                                                            <table id="vincularDFD" summary="" bgcolor="#bfdaf2" border="1" bordercolor="#75ADE6" width="100%">
                                                                <tbody>
                                                                <tr>
                                                                    <td colspan="17" class="titulo3 itens_material" align="center" bgcolor="#75ADE6" valign="middle">DFDS VINCULADOS</td>
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
                                                                        <input id="desvincular" name="Remover" value="Desvincular DFD" class="botao"  type="button">
                                                                    </td>
                                                                </tr>
                                                                </tbody>
                                                            </table>
                                                        <?php } ?>
                                                        <table id="scc_material" summary="" bgcolor="#bfdaf2" border="1" bordercolor="#75ADE6" width="100%">                                                            <tbody>
                                                                <tr>
                                                                    <td colspan="17" class="titulo3 itens_material" align="center" bgcolor="#75ADE6" valign="middle">ITENS DO DOCUMENTO</td>
                                                                </tr>
                                                                <!-- Headers ITENS DA SOLICITAÇÃO DE MATERIAL  -->
                                                                <tr class="head_principal">

                                                                    <!-- <td class="textonormal" align="center" bgcolor="#DCEDF7" width="7%"><br /> ORD </td> -->
                                                                    
                                                                    <?php 
                                                                    if(!empty($itens)){
                                                                        echo '
                                                                            <td class="textonormal" align="center" bgcolor="#DCEDF7" width="5%" ><img src="../midia/linha.gif" alt="" border="0" height="1px"/> <br /></td>
                                                                            
                                                                            <td class="textonormal" align="center" bgcolor="#DCEDF7" width="35%" ><img src="../midia/linha.gif" alt="" border="0" height="1px"/> <br /> DESCRIÇÃO </td>

                                                                            <td class="textonormal" align="center" bgcolor="#DCEDF7" width="10%" ><img src="../midia/linha.gif" alt="" border="0" height="1px"/> <br /> CÓDIGO </td>';
                                                                        
                                                                        foreach($itens as $key=>$item){
                                                                                if(!empty($item->cmatepsequ)){
                                                                                    echo '<tr class="head_principal">

                                                                                        <td class="textonormal" align="center" style="width:5%;" ><img src="../midia/linha.gif" alt="" bgcolor="#DCEDF7" border="0" height="1px"/> <br /><input type="checkbox" name="chkbxItem[]" id="chkbxItem" value="'.$item->cplitecodi.'"></td>
                                                                                        
                                                                                            <td class="textonormal" align="center"  width="35%" ><img src="../midia/linha.gif" alt="" bgcolor="#DCEDF7" border="0" height="1px"/> <br />'.$item->ematepdesc.'</td>

                                                                                            <td class="textonormal" align="center"  width="10%" ><img src="../midia/linha.gif" alt="" bgcolor="#DCEDF7" border="0" height="1px"/> <br />'.$item->cmatepsequ.'</td>
                                                                                    
                                                                                    </tr>';
                                                                                }
                                                                                if(!empty($item->cservpsequ)){
                                                                                    echo '<tr class="head_principal">
        
                                                                                        <td class="textonormal" align="center" style="width:5%;" ><img src="../midia/linha.gif" alt="" bgcolor="#DCEDF7" border="0" height="1px"/> <br /><input type="checkbox" name="chkbxItem[]" id="chkbxItem" value="'.$item->cplitecodi.'"></td>
                                                                                        
                                                                                        <td class="textonormal" align="center"  width="35%" ><img src="../midia/linha.gif" alt="" bgcolor="#DCEDF7" border="0" height="1px"/> <br />'.$item->eservpdesc.'</td>
        
                                                                                        <td class="textonormal" align="center"  width="10%" ><img src="../midia/linha.gif" alt="" bgcolor="#DCEDF7" border="0" height="1px"/> <br />'.$item->cservpsequ.'</td>
                                                                                    
                                                                                    </tr>';
                                                                                }   
                                                                
                                                                        }
                                                                    }
                                                                //     if($servico == true){
                                                                //     echo '
                                                                //         <td class="textonormal" align="center" bgcolor="#DCEDF7" width="5%"><img src="../midia/linha.gif" alt="" border="0" height="1px"/> <br /> ORD. </td>
                                                                    
                                                                //         <td class="textonormal" align="center" bgcolor="#DCEDF7" width="35%"><img src="../midia/linha.gif" alt="" border="0" height="1px"/> <br /> DESCRIÇÃO DO SERVIÇO </td>

                                                                //         <td class="textonormal" align="center" bgcolor="#DCEDF7" width="10%"><img src="../midia/linha.gif" alt="" border="0" height="1px"/> <br /> CÓDIGO SERVIÇO </td>';
                                                                //     foreach($itens as $item){
                                                                //         if($item->remover != true && !empty($item->cservpsequ)){
                                                                //             echo '<tr class="head_principal">

                                                                //                 <td class="textonormal" align="center" style="width:5%;" ><img src="../midia/linha.gif" alt="" bgcolor="#DCEDF7" border="0" height="1px"/> <br /><input type="checkbox" name="chkbxItem[]" id="chkbxItem" value="'.$item->cplitecodi.'"></td>
                                                                                
                                                                //                 <td class="textonormal" align="center"  width="35%" ><img src="../midia/linha.gif" alt="" bgcolor="#DCEDF7" border="0" height="1px"/> <br />'.$item->eservpdesc.'</td>

                                                                //                 <td class="textonormal" align="center"  width="10%" ><img src="../midia/linha.gif" alt="" bgcolor="#DCEDF7" border="0" height="1px"/> <br />'.$item->cservpsequ.'</td>
                                                                            
                                                                //             </tr>';
                                                                //         }   
                                                                //     }
                                                                // }
                                                                ?>
                                                                </tr>
                                                                <tr>
                                                                
                                                                    <td class="textonormal" colspan="7" align="center">
                                                                        <input name="IncluirItem" value="Incluir Item" class="botao" onclick="javascript:AbreJanelaItem('CadIncluirItemPlanejamento.php?ProgramaOrigem=CadManterDFD&amp;PesqApenas=C&amp;classePredefinida=<?php echo ($classe->cclamscodi)? $classe->cclamscodi:$dadosDFD->cclamscodi;?>&amp;Grupo=<?php echo ($classe->cgrumscodi)? $classe->cgrumscodi:$dadosDFD->cgrumscodi;?>&amp;TipoGrupo=<?php echo ($_SESSION['servico'])? $_SESSION['servico']:$classe->fgrumstipo;?>', 700, 350);" type="button">
                                                                        <input id="RetirarItem" name="RetirarItem" value="Retirar Item" class="botao"  type="button">
                                                                    </td>
                                                                </tr>
                                                                <table style="padding-left: 660px;">
                                                                    <tr>
                                                                    <td>
                                                                            <input type="button" class="botao" id="Manter" style="float:right; width:90px; text-align:center; margin-right:1px;" name="Manter" value="Alterar">
                                                                    </td>
                                                                    <td>
                                                                            <input type="button" class="botao" id="ManterRascunho" style="float:right; width:130px; text-align:center; margin-right:1px;" name="Manter Rascunho" value="Alterar Rascunho">
                                                                    </td>
                                                                    <td>
                                                                            <input type="button" class="botao" id="Excluir" style="float:right; width:60px; text-align:center; margin-right:3px;" name="Excluir" value="Excluir">
                                                                        </td>
                                                                        <td>
                                                                            <input type="button" class="botao" id="Voltar" style="float:right; width:60px; text-align:center; margin-right:3px;" name="Voltar" value="Voltar">
                                                                    </td>
                                                                </tr>
                                                                </table>
                                                            </tbody>
                                                        </table>
                                                    </tr>
                                                    
                                                </tbody>
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
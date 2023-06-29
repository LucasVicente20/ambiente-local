<?php
/**
 * Portal de Compras
 * Programa: CadAtualizarDFD.php
 * Autor: Diógenes Dantas
 * Data: 03/01/2023
 * Objetivo: Programa para Atualizar DFD
 * Tarefa Redmine: CR277065
 * -------------------------------------------------------------------
 * Alterado:
 * Data:
 * Tarefa:
 * -------------------------------------------------------------------
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
        unset($_SESSION['historico']);
        unset($_SESSION['itensManter']);
        unset($_SESSION['item']);
    } elseif ($_SERVER['REQUEST_METHOD'] == "POST") {
        $DFD = $_POST['dfdSelected'];
        if (!empty($_SESSION['classe']) && !is_null($_POST['radioClasse'])) {
            $selecClasse = $_POST['radioClasse'];
            $classe = $_SESSION['classe'][$selecClasse];
            $_SESSION['classeSelecionadada'] = $classe;
            unset($_SESSION['classe']);
        }
    }
    
    if (!empty($DFD) && empty($_SESSION['DFD'])) {
        $_SESSION['DFD'] = $dadosDFD = $objPlanejamento->consultaDFD($DFD);
        $codigoVinculo =  $objPlanejamento->consultaCodigoVinculo($dadosDFD->cpldfdsequ);
        $itens = $objPlanejamento->consultaItens($DFD);
        if(empty($_SESSION['vincularDFD']) && !empty($codigoVinculo)){
            $_SESSION['vincularDFD'] = $objPlanejamento->consultaDFDcodigoVinculo($dadosDFD->cpldfdsequ, $codigoVinculo->cplvincodi);
        }
        //pega o histórico da sessão sempre que recarregar a página
        $_SESSION['historico'] = $objPlanejamento->consultaHistorico($DFD);

        $_SESSION['itensManter'] = $itens = $objPlanejamento->consultaItens($DFD);

    } else if (!empty($_SESSION['DFD'])) {
        $dadosDFD = $_SESSION['DFD'];
        $itens = $_SESSION['itensManter'];
    }
    //verifica se a classe foi alterada para remover os itens da sessão
    if(!empty($classe)){
        //verifica se a classe é igual a anterior ou não, se for colcoa para remover, senão remove a chave de remossao do array
        if($classe->cclamscodi != $dadosDFD->cclamscodi){
            for($i=0; $i<count($itens); $i++ ){
                $itens[$i]->remover = true;
            }
        }else{
            for($i=0; $i<count($itens); $i++ ){
                $itens[$i]->remover = false;
                if($itens[$i]->Novo == true){
                    unset($itens[$i]);
                }
            }
        }
    }
    $matOuServ =  !empty($itens[0]->cmatepsequ)? "M":"S";
    
    //caso venha item novo
    if(!empty($_SESSION['item'])){
        //Verifica se o item repete com o que ja vem do banco
        for($i=0;$i<count($_SESSION['item']);$i++){
            $novoSeqMatServ = $_SESSION['item'][0]['CodRedMaterialServicoBanco'];
            if(!empty($itens)){
                if($matOuServ == "M"){
                    if(!empty($novoSeqMatServ)){
                        for($j=0;$j<count($itens);$j++){
                            if($novoSeqMatServ == $itens[$j]->cmatepsequ){
                                if($itens[$j]->remover == true){
                                    $itens[$j]->remover = false;
                                }
                                $validaNovoItem = false;
                            }else{
                                $validaNovoItem = true;
                            }
                        }
                    }
                }
                if($matOuServ == "S"){
                    if(!empty($novoSeqMatServ)){
                        for($j=0;$j<count($itens);$j++){
                            if($novoSeqMatServ == $itens[$j]->cservpsequ){
                                if($itens[$j]->remover == true){
                                    $itens[$j]->remover = false;
                                }
                                $validaNovoItem = false;
                            }else{
                                $validaNovoItem = true;
                            }
                        }
                    }
                }
            }else{
                $validaNovoItem = true;
                $matOuServ =  $_SESSION['item'][0]['TipoGrupoBanco'];
            }
            if($validaNovoItem == true){
                $posicao = count($itens);
                if($matOuServ == "M"){
                    $itens[$posicao]->cplitecodi = $posicao;
                    $itens[$posicao]->cmatepsequ =  $_SESSION['item'][$i]['CodRedMaterialServicoBanco'];
                    $itens[$posicao]->ematepdesc =  $_SESSION['item'][$i]['DescricaoMaterialServicoBanco'];
                    $itens[$posicao]->Novo =  true;
                }else{
                    $itens[$posicao]->cplitecodi = $posicao;
                    $itens[$posicao]->cservpsequ =  $_SESSION['item'][$i]['CodRedMaterialServicoBanco'];
                    $itens[$posicao]->eservpdesc =  $_SESSION['item'][$i]['DescricaoMaterialServicoBanco'];
                    $itens[$posicao]->Novo =  true;
                }
            }
        }
        unset($_SESSION['item']);
    }
    $_SESSION['itensManter'] = $itens; //Pega tudo do Item para a SESSÂO

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
    
    $('#grauPrioridade').on('change', function() {
        const grauPrioridade = $('#grauPrioridade').val();
        if (grauPrioridade == 1) {
            $('#trJustPrioridade').show();
        } else {
            $('#trJustPrioridade').hide();
        }
    })

    $("#Manter").live('click', function(){
        var confirmation = confirm("Tem certeza que deseja ALTERAR o DFD?")
        if (confirmation == true) {
            $("#tdload").show();
            $("#op").val("ManterDFD");
            $.post("PostDadosAtualizarDFD.php", $("#formCadAtualizarDFD").serialize(), function(data){
                const response = JSON.parse(data);
                if(response.status == false){
                    $('html, body').animate({scrollTop:0}, 'slow');
                    $('#tdload').hide();
                    $(".mensagem-texto").html(response.msm);
                    $(".error").html("Erro!");
                    $("#tdmensagem").show();
                }else{
                    window.location.href = "./ConsSelecionarAtualizarDFD.php?";
                }
            })
        }
    })

    $("#Excluir").live('click', function(){
        var confirmation = confirm("Tem certeza que deseja excluir o DFD?");
        if(confirmation == true){
            $("#tdload").show();
            $("#op").val("ExcluirDFD");
            $.post("PostDadosAtualizarDFD.php", $("#formCadAtualizarDFD").serialize(), function(data){
                const response = JSON.parse(data);
                if(response.status == false){
                    $('html, body').animate({scrollTop:0}, 'slow');
                    $('#tdload').hide();
                    $(".mensagem-texto").html(response.msm);
                    $(".error").html("Erro!");
                    $("#tdmensagem").show();
                }else{
                    window.location.href = "./ConsSelecionarAtualizarDFD.php?";
                }
            })
        }
    })

    $("#Voltar").live('click', function(){
       window.location.href = "./ConsSelecionarAtualizarDFD.php?";
    })

    $("#RetirarItem").live('click', function(){
        $('#tdload').show();
        var ordSelected = $('input[name=radioItem]:checked').val();
        console.log(ordSelected);
        $.post("PostDadosAtualizarDFD.php", {op: "retirarItem", itemRetirar:ordSelected}, function(data){
            const response = JSON.parse(data);
            if(response.status == true){
                $('html, body').animate({scrollTop:0}, 'slow');
                $('#tdload').hide();
                $(".mensagem-texto").html(response.msm);
                $(".error").html("Atenção!");
                $("#tdmensagem").show();
                $("#op").val("");
                $("#formCadAtualizarDFD").submit();
            }
        })
    })

    $("#lupaCodClasse").on('click', function(){
        $.post("PostDadosAtualizarDFD.php",{op:"modalPesqClasse"}, function(data){
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
        $("#LoadPesqScc").show();
        //Limpa a mensagem quando refaz a pesquisa
        $("#tdmensagemM").hide();
        var opPesq = $("#OpcaoPesquisaClasse").val();
        var dadoPesquisar = $("#ClasseDescricaoDireta").val();
        $.post("PostDadosAtualizarDFD.php",{
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
        $.post("PostDadosAtualizarDFD.php",
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
        $.post("PostDadosAtualizarDFD.php",
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
        $.post("PostDadosDFD.php", $("#formCadAtualizarDFD").serialize(), function(data) {
            const response = JSON.parse(data);
            if (response.status == false) {
                $("#tdload").hide();
                $("#tdmensagem").show();
                $('html, body').animate({scrollTop:0}, 'slow');
                $(".mensagem-texto").html(response.msm);
            } else {
                $("#op").val("");
                $("#formCadAtualizarDFD").submit();
            }
        });
    });

    $("#AlterarDFD").live('click', function(){
        $("#tdload").show();
        $("#op").val("AlterarDFD");
        $.post("PostDadosDFD.php", $("#formCadAtualizarDFD").serialize(), function(data){
            const response = JSON.parse(data);
            if(response.status == 404){
                $('html, body').animate({scrollTop:0}, 'slow');
                $('#tdload').hide();
                $(".mensagem-texto").html(response.msm);
                $(".error").html("Erro!");
                $("#tdmensagem").show();
            }else{
                setTimeout(function(){ 
                    window.location.href = "./ConsSelecionarAtualizarDFD.php?";
                }, 2000);
            }
        })
    })

    $("#Voltar").live('click', function(){
       window.location.href = "./ConsSelecionarAtualizarDFD.php?";
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
<form action="CadAtualizarDFD.php" method="post" id="formCadAtualizarDFD" name="CadAtualizarDFD">
<input type="hidden" name="op" id="op" value="">
<input type="hidden" name="rascunho" id="rascunho" value="">
<input type="hidden" name="Destino" id="Destino" value="">
<input type="hidden" name="dfdSelected" id="dfdSelected" value="<?php echo $DFD;?>">
<br><br><br><br>
<table cellpadding="3" border="0" summary="">
    <!-- Caminho -->
    <tr>
        <td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
        <td align="left" class="textonormal" colspan="2">
            <font class="titulo2">|</font>
            <a href="../index.php"><font color="#000000">Página Principal</font></a> > Planejamento > DFD > Atualizar
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
            <table   border="1" bordercolor="#75ADE6" cellspacing="0" cellpadding="3" summary="" width="1024px" bgcolor="#FFFFFF">
                <tr>
                    <td class="textonormal">
                        <table cellpadding="3" cellspacing="0" summary="" class="textonormal" bgcolor="#FFFFFF" width="1024px">
                            <tr>
                                <td class="titulo3" colspan="17" align="center" bgcolor="#75ADE6" valign="middle"> <b>ATUALIZAR DOCUMENTO DE FORMALIZAÇÃO DE DEMANDA (DFD)</b>
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
                                                            Ano PCA
                                                        </td>
                                                        <td>
                                                            <?php 
                                                                echo "<span>$dadosDFD->apldfdanod</span>";
                                                            ?>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="textonormal" id="labels">
                                                            Número do DFD
                                                        </td>
                                                        <td>
                                                            <span><?php echo $dadosDFD->cpldfdnumf;?></span>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="textonormal" id="labels">
                                                            Nome da Área Requisitante
                                                        </td>
                                                        <td>
                                                            <span id="areaReq"><?php echo $dadosDFD->descorgao;?></span>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="textonormal" id="labels">
                                                            CNPJ
                                                        </td>
                                                        <td>
                                                            <span name="cnpjAreaReq" id="cnpjAreaReq">
                                                            <?php echo $objPlanejamento->MascarasCPFCNPJ($dadosDFD->cnpjorgao);?>
                                                            </span>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="textonormal" id="labels">
                                                            Código da Classe*
                                                        </td>
                                                        <td>
                                                            <img id="lupaCodClasse" src="../midia/lupa.gif" border="0">
                                                            </br>
                                                            <span><?php echo ($classe->cclamscodi) ? "$classe->cclamscodi":$dadosDFD->cclamscodi; ?></span>
                                                            <input type="hidden" name="cclamscodi" id="cclamscodi" value="<?php echo ($classe->cclamscodi)? $classe->cclamscodi:$dadosDFD->cclamscodi;?>">
                                                            <input type="hidden" name="cgrumscodi" id="cgrumscodi" value="<?php echo ($classe->cgrumscodi)? $classe->cgrumscodi:$dadosDFD->cgrumscodi;?>">
                                                            <input type="hidden" name="chaveNovaClasse" id="chaveNovaClasse" value="<?php echo ($classe->cclamscodi)? "true":"false";?>">
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="textonormal" id="labels">
                                                            Descrição da Classe*
                                                        </td>
                                                        <td>
                                                            <span id="descricaoClasse" name="descricaoClasse"> <?php echo ($classe->eclamsdesc)?"$classe->eclamsdesc":$dadosDFD->descclasse;?></span>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="textonormal" id="labels">
                                                            Descrição Sucinta da Demanda*
                                                        </td>
                                                        <td>
                                                            <textarea class="textonormal" style="font-size: 10.6667px; text-transform: uppercase;" id="descSuDemanda" name="descSuDemanda" cols=50 rows="4" maxlength="200"><?php echo !empty($_POST['descSuDemanda']) ? $_POST['descSuDemanda'] : $dadosDFD->epldfddesc; ?></textarea>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="textonormal" id="labels">
                                                            Justificativa da necessidade de contratação*
                                                        </td>
                                                        <td>
                                                            <textarea class="textonormal" style="font-size: 10.6667px; text-transform: uppercase;" id="justContratacao" name="justContratacao" cols="50" rows="4" maxlength="1000"><?php echo !empty($_POST['justContratacao']) ? $_POST['justContratacao'] : $dadosDFD->epldfdjust;?></textarea>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="textonormal" id="labels">
                                                            Estimativa preliminar de valor contratação*
                                                        </td>
                                                        <td>
                                                            <input type="text" name="estValorContratacao" id="estValorContratacao" value="<?php echo !empty($_POST['estValorContratacao'])? $_POST['estValorContratacao']: $dadosDFD->cpldfdvest; ?>" size=15>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="textonormal" id="labels">
                                                            Tipo de Processo de contratação*
                                                        </td>
                                                        <td>
                                                            <?php $tpProcContratacao = !empty($_POST["tpProcContratacao"]) ? $_POST["tpProcContratacao"] : $dadosDFD->fpldfdtpct; ?>
                                                            <select name="tpProcContratacao" id="tpProcContratacao" style="width:175px;">
                                                                <option value=""  >Selecione o tipo</option>
                                                                <option value="D" <?php echo ($tpProcContratacao == "D") ? "selected" : "" ; ?>>CONTRATAÇÃO DIRETA</option>
                                                                <option value="L" <?php echo ($tpProcContratacao == "L") ? "selected" : "" ; ?>>LICITAÇÃO</option>
                                                            </select>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="textonormal" id="labels">
                                                            Data prevista para conclusão
                                                        </td>
                                                        <td>
                                                            <input type="text" name="dtPretendidaConc" class="data" id="dtPretendidaConc" size=15 value="<?php echo !empty($_POST['dtPretendidaConc'])?$_POST['dtPretendidaConc']:date('d/m/Y', strtotime($dadosDFD->dpldfdpret));?>">
                                                            <a id="calendarioVig" style="text-decoration: none" href="javascript:janela('../calendario.php?Formulario=CadAtualizarDFD&amp;Campo=dtPretendidaConc','Calendario',220,170,1,0)"> 
                                                                <img src="../midia/calendario.gif" border="0" alt="">
                                                            </a>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="textonormal" id="labels">
                                                            Grau de Prioridade*
                                                        </td>
                                                        <td>
                                                            <?php $grauPrioridade = !empty($_POST['grauPrioridade']) ? $_POST['grauPrioridade'] : $dadosDFD->fpldfdgrau;?>
                                                            <select name="grauPrioridade" id="grauPrioridade" style="width:340px;">
                                                                <option value="">Selecione o grau de prioridade</option>
                                                                <option value="1" <?php echo ($grauPrioridade == "1") ? "selected" : "" ; ?>>ALTO</option>
                                                                <option value="2" <?php echo ($grauPrioridade == "2") ? "selected" : "" ; ?>>MÉDIO</option>
                                                                <option value="3" <?php echo ($grauPrioridade == "3") ? "selected" : "" ; ?>>BAIXO</option>
                                                            </select>
                                                        </td>
                                                    </tr>
                                                    <!-- PRIORIDADE ALTA -->
                                                    <!-- ESSE CAMPO APARECERÁ SE O GRAU DE PRIORIDADE FOR ALTO -->
                                                    <tr id="trJustPrioridade" style="<?php echo ($grauPrioridade == "1")? "":"display:none;";?>">
                                                        <td class="textonormal" id="labels">
                                                            Justificativa para prioridade alta*
                                                        </td>
                                                        <td>
                                                            <textarea class="textonormal" style="font-size: 10.6667px; text-transform: uppercase;" id="justPriAlta" name="justPriAlta" cols="50" rows="4" maxlength="400"><?php echo !empty($_POST['justPriAlta'])? $_POST['justPriAlta']: $dadosDFD->epldfdjusp; ?></textarea>
                                                        </td>
                                                    </tr>
                                                    <!-- FIM PRIORIDADE ALTA -->
                                                    <tr>
                                                        <td class="textonormal" id="labels">
                                                            Vinculação outro DFD
                                                        </td>
                                                        <td>
                                                            <a href="javascript:AbreJanela('JanelaVincular.php?&Programa=Manter',700,370);">
                                                                <img src="../midia/lupa.gif" border="0">
                                                            </a>
                                                        </td>
                                                    </tr>
                                                    <tr <?php echo ($dadosDFD->corglicodi != 18) ? 'style = "display:none;"' : ''?>>
                                                        <td class="textonormal" id="labels">
                                                            Compra Corporativa
                                                        </td>
                                                        <td>
                                                            <?php $grauPrioridade = !empty($_POST['grauPrioridade']) ? $_POST['grauPrioridade'] : $dadosDFD->fpldfdcorp; ?>
                                                            <select name="contratCorp" id="contratCorp" style="width:175px;">
                                                                <option value="S" <?php echo ($grauPrioridade == "S") ? "selected" : "" ; ?>>SIM</option>
                                                                <option value="N" <?php echo ($grauPrioridade == "N" || empty($_POST['grauPrioridade'])) ? "selected" : "" ; ?>>NÃO</option>
                                                            </select>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <?php if($_SESSION['vincularDFD']){ ?>
                                                            <table id="vincularDFD" summary="" bgcolor="#bfdaf2" border="1" bordercolor="#75ADE6" width="100%">
                                                                <tbody>
                                                                <tr>
                                                                    <td colspan="17" class="titulo3 itens_material" align="center" bgcolor="#75ADE6" valign="middle"> DFDS VINCULADAS</td>
                                                                </tr>
                                                                <!-- Headers ITENS DA SOLICITAÇÃO DE MATERIAL  -->
                                                                <tr class="head_principal">
                                                                    <?php
                                                                    echo '     
                                                                <td class="textonormal" align="center" bgcolor="#DCEDF7" width="5%"><img src="../midia/linha.gif" alt="" border="0" height="1px"/> </td>                                                           
                                                                <td class="textonormal" align="center" bgcolor="#DCEDF7" width="35%" ><img src="../midia/linha.gif" alt="" border="0" height="1px"/> <br /> NÚMERO DO DFD</td>
                                                                <td class="textonormal" align="center" bgcolor="#DCEDF7" width="35%" ><img src="../midia/linha.gif" alt="" border="0" height="1px"/> <br /> ANO</td>
                                                                <td class="textonormal" align="center" bgcolor="#DCEDF7" width="35%" ><img src="../midia/linha.gif" alt="" border="0" height="1px"/> <br /> CLASSE</td>
                                                                <td class="textonormal" align="center" bgcolor="#DCEDF7" width="10%" ><img src="../midia/linha.gif" alt="" border="0" height="1px"/> <br /> SITUAÇÃO</td>';
                                                                    ?>
                                                                </tr>
                                                                <?php
                                                                if(!empty($_SESSION["vincularDFD"])) {
                                                                    foreach($_SESSION["vincularDFD"] as $dfd) {
                                                                        echo '
                                                                        <tr class="head_principal">  
                                                                            <td class="textonormal" align="center" style="width:5%;" ><img src="../midia/linha.gif" alt="" bgcolor="#DCEDF7" border="0" height="1px"/> <br /><input type="checkbox" name="checkVincular[]" id="checkVincular" value="'.$dfd->cpldfdsequ.'"></td>                                                                                                                                          
                                                                            <td class="textonormal" align="center"  width="35%" ><img src="../midia/linha.gif" alt="" bgcolor="#DCEDF7" border="0" height="1px"/> <br />'.$dfd->cpldfdnumf.'</td>
                                                                            <td class="textonormal" align="center"  width="35%" ><img src="../midia/linha.gif" alt="" bgcolor="#DCEDF7" border="0" height="1px"/> <br />'.$dfd->apldfdanod.'</td>
                                                                            <td class="textonormal" align="center"  width="10%" ><img src="../midia/linha.gif" alt="" bgcolor="#DCEDF7" border="0" height="1px"/> <br />'.$dfd->descclasse.'</td>
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
                                                                    <?php if($matOuServ=="M"){
                                                                    echo '
                                                                        <td class="textonormal" align="center" bgcolor="#DCEDF7" width="5%" ><img src="../midia/linha.gif" alt="" border="0" height="1px"/> <br /> ORD. </td>
                                                                        <td class="textonormal" align="center" bgcolor="#DCEDF7" width="35%" ><img src="../midia/linha.gif" alt="" border="0" height="1px"/> <br /> DESCRIÇÃO DO MATERIAL </td>
                                                                        <td class="textonormal" align="center" bgcolor="#DCEDF7" width="10%" ><img src="../midia/linha.gif" alt="" border="0" height="1px"/> <br /> CÓDIGO MATERIAL </td>';
                                                                    } else {
                                                                    echo '
                                                                        <td class="textonormal" align="center" bgcolor="#DCEDF7" width="5%"><img src="../midia/linha.gif" alt="" border="0" height="1px"/> <br /> ORD. </td>
                                                                        <td class="textonormal" align="center" bgcolor="#DCEDF7" width="35%"><img src="../midia/linha.gif" alt="" border="0" height="1px"/> <br /> DESCRIÇÃO DO SERVIÇO </td>
                                                                        <td class="textonormal" align="center" bgcolor="#DCEDF7" width="10%"><img src="../midia/linha.gif" alt="" border="0" height="1px"/> <br /> CÓDIGO SERVIÇO </td>';
                                                                    }?>
                                                                </tr>
                                                                <?php 
                                                                if($matOuServ=="M"){
                                                                    for($i=0; $i<count($itens); $i++) {
                                                                        if($itens[$i]->remover != true) {
                                                                        echo '
                                                                            <tr class="head_principal">
                                                                                <td class="textonormal" align="center" style="width:5%;" ><img src="../midia/linha.gif" alt="" bgcolor="#DCEDF7" border="0" height="1px"/> <br /><input type="radio" name="radioItem" id="radioItem" value="'.$i.'"></td>
                                                                                <td class="textonormal" align="center"  width="35%" ><img src="../midia/linha.gif" alt="" bgcolor="#DCEDF7" border="0" height="1px"/> <br />'.$itens[$i]->ematepdesc.'</td>
                                                                                <td class="textonormal" align="center"  width="10%" ><img src="../midia/linha.gif" alt="" bgcolor="#DCEDF7" border="0" height="1px"/> <br />'.$itens[$i]->cmatepsequ.'</td>
                                                                            </tr>';
                                                                        }
                                                                    }
                                                                } else {
                                                                    for($i=0; $i<count($itens); $i++){
                                                                        if($itens[$i]->remover != true){
                                                                            echo '
                                                                            <tr class="head_principal">
                                                                                <td class="textonormal" align="center" style="width:5%;" ><img src="../midia/linha.gif" alt="" bgcolor="#DCEDF7" border="0" height="1px"/> <br /><input type="radio" name="radioItem" id="radioItem" value="'.$i.'"></td>
                                                                                <td class="textonormal" align="center"  width="35%" ><img src="../midia/linha.gif" alt="" bgcolor="#DCEDF7" border="0" height="1px"/> <br />'.$itens[$i]->eservpdesc.'</td>
                                                                                <td class="textonormal" align="center"  width="10%" ><img src="../midia/linha.gif" alt="" bgcolor="#DCEDF7" border="0" height="1px"/> <br />'.$itens[$i]->cservpsequ.'</td>
                                                                            </tr>';
                                                                        }   
                                                                    }
                                                                }?>
                                                                <tr>
                                                                    <td class="textonormal" colspan="7" align="center">
                                                                        <input name="IncluirItem" value="Incluir Item" class="botao" onclick="javascript:AbreJanelaItem('CadIncluirItemPlanejamento.php?ProgramaOrigem=CadAtualizarDFD&amp;PesqApenas=C&amp;classePredefinida=<?php echo ($classe->cclamscodi)? $classe->cclamscodi:$dadosDFD->cclamscodi;?>&amp;Grupo=<?php echo ($classe->cgrumscodi)? $classe->cgrumscodi:$dadosDFD->cgrumscodi;?>', 700, 350);" type="button">
                                                                        <input id="RetirarItem" name="RetirarItem" value="Retirar Item" class="botao"  type="button">
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </tr>
                                                    <table style="padding-left: 796px;">
                                                        <tr>
                                                            <td><input type="button" class="botao" id="Manter" style="float:right; width:90px; text-align:center; margin-right:1px;" name="Manter" value="Manter"></td>
                                                            <td><input type="button" class="botao" id="Excluir" style="float:right; width:60px; text-align:center; margin-right:3px;" name="Excluir" value="Excluir"></td>
                                                            <td><input type="button" class="botao" id="Voltar" style="float:right; width:60px; text-align:center; margin-right:3px;" name="Voltar" value="Voltar"></td>
                                                        </tr>
                                                    </table>
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
<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadAditivoManter.php
# Autor:    Eliakim Ramos | João Madson | Edson Dionisio
# Data:     17/04/2020
# Objetivo: Programa de incluir contrato
#-------------------------------------------------------------------------
# Autor:    Madson Felix
# Data:     28/04/2021
# CR #246939
# -------------------------------------------------------------------------
# Alterado : Osmar Celestino
# Data: 26/11/2021
# Objetivo: CR #251985
#---------------------------------------------------------------------------
# Autor:    Lucas Vicente
# Data:     19/08/2022
# CR #267744
# -------------------------------------------------------------------------
# Alterado : João Madson 
# Data: 31/08/2022
# Objetivo: CR #268222
#---------------------------------------------------------------------------
# Acesso ao arquivo de funções #
include "../funcoes.php";
require_once "ClassAditivo.php";


# Executa o controle de segurança	#
session_start();
Seguranca();


$ObjClassAditivo = new ClassAditivo();
//!empty($_POST['idregistro'])?$_POST['idregistro']:$_GET['idregistro']
$idContrato = $_POST['idregistro'];
//coloquei o id registro na sessão e assim resolve o problema referente a cr
if(empty($idContrato)){
    $idContrato = $_SESSION['idregistro'];
} else {
    $_SESSION['idregistro'] = $idContrato;
}

$contrato_pesq = $ObjClassAditivo->getContrato($idContrato);
$ectrpcnumf = $contrato_pesq[0]->ectrpcnumf;
$ectrpcobj = $contrato_pesq[0]->ectrpcobje;
$cdocpcsequ = $contrato_pesq[0]->cdocpcsequ;
$valorGlobaladt = $contrato_pesq[0]->vctrpcglaa;

$aditivos = $ObjClassAditivo->getAditivosContrato($idContrato);
//var_dump($aditivos);die;

$html = "";

foreach($aditivos as $aditivo){
    
    $prazo_adicional = $aditivo->aaditiapea;

    if($aditivo->vaditivtad != 0){
        $valoraditivo = number_format((floatval($aditivo->vaditivtad)),4,',','.');
    } else{
        $valoraditivo = number_format((floatval('0')),4,',','.');
    }

    if(!empty($aditivo->fase) && intval($aditivo->fase) == 4){
        $status = "EM EXECUÇÃO";
    }else{
        $status = "CADASTRADO";
    }

    $dtInicialExec = explode("-", $aditivo->daditiinex);
    $dtInicialExec = array_reverse($dtInicialExec);
    $dtInicialExec = $dtInicialExec[0] . "/" . $dtInicialExec[1] . "/" . $dtInicialExec[2];

    $dtInicialVig = explode("-", $aditivo->daditiinvg);
    $dtInicialVig = array_reverse($dtInicialVig);
    $dtInicialVig = $dtInicialVig[0] . "/" . $dtInicialVig[1] . "/" . $dtInicialVig[2];

    $dtFinalVig = explode("-", $aditivo->daditifivg);
    $dtFinalVig = array_reverse($dtFinalVig);
    $dtFinalVig = $dtFinalVig[0] . "/" . $dtFinalVig[1] . "/" . $dtFinalVig[2];
    #MADSON| Precisei alterar essa parte segundo a cr 268222, estava uma completa  bagunça, calculando a data na query, 
    //calculando aqui e ainda assim usando a da query para a data de execução e pior usando para a vigencia também mas em um extra calculo.
    // Precisei corrigir a query para mudar o nome do campo e trazer a data de execução correta, na atual lógica a data de fim de vigencia trás os dados de vigência
    // e a data de fim de execução tras caso haja a data de fim de execução, senão utiliza a calculada pela query.
    if(!empty($aditivo->daditifiex)){
        $dtFinalExec = explode("-", $aditivo->daditifiex); 
        $dtFinalExec = array_reverse($dtFinalExec);
        $dtFinalExec = $dtFinalExec[0] . "/" . $dtFinalExec[1] . "/" . $dtFinalExec[2];
    }else{
        $dtFinalExec = $aditivo->data_fim_execucao_calculada;
    }

    if($dtInicialVig != "//" && $dtFinalVig != "//"){
        $data_vigencia = $dtInicialVig . " à " . $dtFinalVig;
    }else{
        $data_vigencia = "";
    }

    if($dtInicialExec != "//"){
        $data_execucao = $dtInicialExec . " à " . $dtFinalExec;
    }else{
        $data_execucao = "";
    }
    
    $html .= "<tr>";
    $html .= "<td> <input type=\"radio\" id=\"$aditivo->cdocpcsequ\" name=\"cdocpcsequ\" value=\"$aditivo->cdocpcsequ\" /> </td>";
    $html .= "<td valign=\"top\" >";
    $html .= str_pad($aditivo->aaditinuad, 4 , '0' , STR_PAD_LEFT);
    $html .= "</td>";
    $html .= "<td valign=\"top\" >";
    $html .=  $data_vigencia;
    $html .= "</td>";
    $html .= "<td valign=\"top\" >";
    $html .=  $data_execucao;
    $html .= "</td>";
    $html .= "<td valign=\"top\" >";
    $html .= $valoraditivo;
    $html .= "</td>";
    $html .= "<td valign=\"top\" >";
    $html .= $status;
    $html .= "</td>";
    $html .= "</tr>";
    
}
?>
<html>
    <?php
        # Carrega o layout padrão
        layout();
    ?>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script language="javascript" src="../janela.js" type="text/javascript"></script>
    <script language="javascript" src="../import/jquery/jquery-1.7.2.min.js" type="text/javascript"></script>
    <script language="javascript" src="../import/jquery/jquery.maskmoney.js" type="text/javascript"></script>
    <script language="javascript" src="../import/jquery/jquery.maskedinput.js" type="text/javascript"></script>
    <script language="javascript" type="">
        $(document).ready(function() {
            const mensagem = '<?php echo $_POST['mensagem'];?>';
            if(mensagem != ''){
                $('html, body').animate({scrollTop:0}, 'slow');
                 $(".mensagem-texto").html(mensagem);
                 $(".error").css("color","#007fff");
                 $(".error").html("Atenção!");
                 $("#tdmensagem").show();
            }
            $("#formTableMedicoes").show();

            $("#btnVoltar").on('click', function(){
                window.location.href="./CadAditivoManterPesquisar.php";
            });
            $("#btnVoltar1").on('click', function(){
                window.location.href="./CadAditivoManterPesquisar.php";
            });

            $("#btnIncluir").on('click', function(){
                    const codContrato =$("#idContrato").val();
                    window.location.href="./CadAditivoIncluir.php?codcontrato="+codContrato;
            });


            $("#btnAlterar").on('click', function(){
                    const codContrato = $("#idregistro").val();
                    const documento = $("input[name='cdocpcsequ']:checked").val();

                    if(documento > 0){
                        window.location.href="./CadAditivoAlterar.php?codcontrato="+codContrato+"&rg="+documento;
                    }else{
                        $('html, body').animate({scrollTop:0}, 'slow');
                        $(".mensagem-texto").html("Informe: Aditivo selecionado.");
                        $(".error").html("Erro!");
                        $("#tdmensagem").show();
                    }
                    
            });

            $("#btnExcluir").on('click', function(){
                const codContrato = $("#idregistro").val();
                const documento = $("input[name='cdocpcsequ']:checked").val();

                var resposta = confirm("Deseja remover esse registro?");
                if (resposta == true) {
                    $.post("postDadosAditivoGeral.php", {op:"ExcluirAditivo", contrato: codContrato, doc: documento})
                        .done(function(data){
                            //ObjJson = JSON.parse(data);
                            $("#mensagem").val("Aditivo removido com sucesso.");
                            $("#formulario").attr("action","CadAditivoManter.php");
                            $("#formulario").submit();

                        }).fail(function(data){
                            $('html, body').animate({scrollTop:0}, 'slow');
                            $(".mensagem-texto").html("Não foi possível remover o registro.");
                            $(".error").html("Erro!");
                            $("#tdmensagem").show();
                        });                
                }
           
            });

        });
        <?php MenuAcesso(); ?>
    </script>
    <link rel="stylesheet" type="text/css" href="../estilo.css?v=<?php echo time();?>">
    <body background="../midia/bg.gif" marginwidth="0" marginheight="0">
        <script language="JavaScript" src="../menu.js"></script>
        <script language="JavaScript">Init();</script>
    
	
	<br><br>
        <form action="<?=$programa?>" method="post" name="formulario" id="formulario">
            <input type="hidden" name="mensagem" id="mensagem">
            <input type="hidden" name="idregistro" id="idregistro" value="<?php echo $idContrato;?>">
            <br><br><br><br><br>
            <table cellpadding="3" border="0" summary="">
                <!-- Caminho -->
                    <tr>
                        <td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
                        <td align="left" class="textonormal" colspan="2">
                            <font class="titulo2">|</font>			
                            <a href="../index   .php">
                                    <font color="#000000">Página Principal</font>
                            </a> > Contrato > Aditivo > Manter
                        </td>
                    </tr>
                <!-- Fim do Caminho-->
                <!-- Erro -->
                    <tr>
                        <td width="150"></td>
                        <td align="left" colspan="2" id="tdmensagem">
                            <div class="mensagem">
                                <div class="error">
                                Error!
                                </div>
                                <span class="mensagem-texto">
                                Informe: Centro de Custo e Tipo de Compra .
                                </span>
                            </div>
                        </td>
                    </tr>
                <!-- Fim do Erro -->

                <!-- Corpo -->
                <tr >
                    <td width="150"></td>
                    <td class="textonormal" width="89%">
                    <table width="100%" border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal"  bgcolor="#FFFFFF">
                        <tr>
                            <td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3" colspan="4">
                                            MANTER ADITIVO
                            </td>
                        </tr>
                        <tr>
                            <td colspan="8">
                                <table border="0" width="100%" summary="">
                                    <tr>
                                        <td class="textonormal" bgcolor="#DCEDF7" width="10%">Número do Contrato/Ano</td>
                                        <td class="textonormal"  width="50%">
                                            <?php
                                                echo $ectrpcnumf;
                                            ?>
                                        </td>
                                    </tr>
                                
                                </table>
                            </td>
                        </tr>
                    </table>
                    </td>
                </tr>

                
                    <tr style="<?php echo empty($aditivos)?"display:none":'';?>">
                        <td width="150"></td>
                        <td class="textonormal" width="89%">
                            <table width="100%" border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal"  bgcolor="#FFFFFF">
                                
                                    <tr>
                                        <td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3" colspan="4">
                                                    ADITIVOS DO CONTRATO
                                        </td>
                                    </tr>
                                    <tr>
                                        <td align="center">
                                            <form name="formTableMedicoes" id="formTableMedicoes" style="display:none;" >                                    
                                                <table width="100%" id="tablePesquisaMedicoes" border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal"  bgcolor="#FFFFFF">
                                                    <thead>
                                                        <tr>
                                                            <td align="center" bgcolor="#DDECF9"  class="titulo3" style="width: 2%"></td>
                                                            <td align="center" bgcolor="#DDECF9"  class="titulo3">
                                                            ADITIVO
                                                            </td>
                                                            <td align="center" bgcolor="#DDECF9"  class="titulo3">
                                                            PERÍODO DE VIGÊNCIA
                                                            </td>
                                                            <td align="center" bgcolor="#DDECF9"  class="titulo3">
                                                            PERÍODO DE EXECUÇÃO
                                                            </td>
                                                            <td align="center" bgcolor="#DDECF9"  class="titulo3">
                                                            VALOR DO ADITIVO
                                                            </td>
                                                            <td align="center" bgcolor="#DDECF9"  class="titulo3">
                                                            SITUAÇÃO
                                                            </td>

                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php echo $html;?>
                                                    </tbody>
                                                    <tr>
                                                        <td class="textonormal" align="right" colspan="6">
                                                            <!-- <input type="button" name="IncluirMedicao" title="Incluir" value="Incluir" class="botao" id="btnIncluir"> -->
                                                            <input type="button" name="AlterarAditivo" title="Alterar" value="Alterar" class="botao" id="btnAlterar">
                                                            <input type="button" name="ExcluirAditivo" title="Excluir" value="Excluir" class="botao" id="btnExcluir">
                                                            <input type="button" name="btnVoltar" title="Voltar" value="Voltar" class="botao" id="btnVoltar">
                                                            <input type="hidden" name="Botao" value="">
                                                        </td>
                                                    </tr>
                                                </table>

                                            </form>
                                        </td>
                                
                                    </tr>
                                
                            
                            </table>
                        </td>                    
                    </tr>
                
                <tr style="<?php echo empty($aditivos)?'':"display:none";?>">
                    <td width="150"></td>
                    <td class="textonormal" width="89%">
                        <table width="100%" border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal"  bgcolor="#FFFFFF">
                            
                                <tr>
                                    <td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3" colspan="6">
                                        RESULTADO DA PESQUISA
                                    </td>
                                    <tr>
                                        <td align="center"  colspan="4" >
                                            Não há aditivos cadastrados neste contrato.   
                                        </td> 
                                    </tr>   
                                </tr>
                        </table>
                    </td>
                </tr>
                <tr style="<?php echo empty($aditivos)?'':"display:none";?>">
                    <td colspan="8"><button type="button" name="btnVoltar1" class="botao" id="btnVoltar1" style="float:right">Voltar</button></td>
                </tr>
                    <input type="hidden" id="btnVoltar1" name="btnVoltar1">
                
            </table>
            
        </form>
        

	    <br><br>
    </body>
</html>
<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadMedicaoManter.php
# Autor:    Eliakim Ramos | João Madson | Edson Dionisio
# Data:     17/04/2020
# Objetivo: Programa de incluir contrato
#-------------------------------------------------------------------------
# Autor:    Madson Felix
# Data:     28/04/2021
# CR #246939
# -------------------------------------------------------------------------


# Acesso ao arquivo de funções #
include "../funcoes.php";
require_once "ClassMedicao.php";
require_once "ClassContratosFuncoesGerais.php";


# Executa o controle de segurança	#
session_start();
Seguranca();


$objContrato = new ClassMedicao();
$objFuncoesGerais = new ContratosFuncoesGerais();


if(empty($_SESSION['idContratoMedMant'])){
    $idContrato = $_POST['idregistro'];
    $_SESSION['idContratoMedMant'] = $idContrato;
}else{
    $idContrato = $_SESSION['idContratoMedMant'];
}

$contrato_pesq = $objContrato->getContrato($idContrato);
$ectrpcnumf = $contrato_pesq[0]->ectrpcnumf;
$ectrpcobj = $contrato_pesq[0]->ectrpcobje;
$cdocpcsequ = $contrato_pesq[0]->cdocpcsequ;

$valorTotalMedicao = $objContrato->getValorTotalMedicao($idContrato);

$medicoes = $objContrato->getMedicoesContrato($idContrato);

$html = "";
foreach($medicoes as $medicao){

    if($medicao->vmedcovalm != 0){
        $valorMedicao = number_format((floatval($medicao->vmedcovalm)),4,',','.');
    } else{
        $valorMedicao = number_format((floatval('0')),4,',','.');
    }


    $dtInicial = explode("-", $medicao->dmedcoinic);
    $dtInicial = array_reverse($dtInicial);
    $dtInicial = $dtInicial[0] . "/" . $dtInicial[1] . "/" . $dtInicial[2];

    $dtFinal = explode("-", $medicao->dmedcofinl);
    $dtFinal = array_reverse($dtFinal);
    $dtFinal = $dtFinal[0] . "/" . $dtFinal[1] . "/" . $dtFinal[2];

    $html .= "<tr>";
    $html .= "<td> <input type=\"radio\" id=\"amedconume$medicao->amedconume\" name=\"amedconume\" value=\"$medicao->cmedcosequ\" /> </td>";
    $html .= "<td valign=\"top\" >";
    $html .= str_pad($medicao->amedconume, 4 , '0' , STR_PAD_LEFT);
    $html .= "</td>";
    $html .= "<td valign=\"top\" >";
    $html .=  $dtInicial . " à " . $dtFinal ;
    $html .= "</td>";
    $html .= "<td valign=\"top\" >";
    $html .= $valorMedicao;
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
                window.location.href="./CadMedicaoPesquisar.php";
            });
            $("#btnVoltar1").on('click', function(){
                window.location.href="./CadMedicaoPesquisar.php";
            });

            $("#btnIncluir").on('click', function(){
                    const codContrato =$("#idContrato").val();
                    window.location.href="./CadMedicaoIncluir.php?codcontrato="+codContrato;
            });


            $("#btnAlterar").on('click', function(){
                    const codContrato =$("#idContrato").val();
                    const documento = $("input[name='amedconume']:checked").val();
                    if(documento > 0){
                        window.location.href="./CadMedicaoAlterar.php?codcontrato="+codContrato+"&rg="+documento;
                    }else{
                        $('html, body').animate({scrollTop:0}, 'slow');
                        $(".mensagem-texto").html("Informe: Medição selecionada.");
                        $(".error").html("Erro!");
                        $("#tdmensagem").show();
                    }
                    
                    
            });

            $("#btnExcluir").on('click', function(){
                const codContrato =$("#idContrato").val();
                const documento = $("input[name='amedconume']:checked").val();

                var resposta = confirm("Deseja remover esse registro?");
                if (resposta == true) {
                    $.post("postDadosMedicao.php", {op:"ExcluirMedicao", contrato: codContrato, doc: documento})
                        .done(function(data){
                            
                            $("#mensagem").val("Medição removida com sucesso.");
                            $("#formulario").attr("action","CadMedicaoManter.php");
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
            <input type="hidden" name="idregistro" value="<?php echo $idContrato;?>">
            <br><br><br><br><br>
            <table cellpadding="3" border="0" summary="">
                <!-- Caminho -->
                    <tr>
                        <td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
                        <td align="left" class="textonormal" colspan="2">
                            <font class="titulo2">|</font>			
                            <a href="../index   .php">
                                    <font color="#000000">Página Principal</font>
                            </a> > Contratos  > Medição > Manter
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
                <tr>
                    <td width="150"></td>
                    <td class="textonormal" width="89%">
                    <table width="100%" border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal"  bgcolor="#FFFFFF">
                        <tr>
                            <td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3" colspan="4">
                                            MANTER MEDIÇÃO CONTRATO
                            </td>
                        </tr>
                        <tr>
                            <td colspan="8">
                                <table border="0" width="100%" summary="">
                                    <tr>
                                        <td class="textonormal" bgcolor="#DCEDF7" width="50%">Número do Contrato/Ano</td>
                                        <td class="textonormal"  width="50%">
                                            <?php
                                                echo $ectrpcnumf;
                                            ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="textonormal" bgcolor="#DCEDF7" width="50%" height="20">Objeto</td>
                                        <td class="textonormal" width="50%">
                                            <?php
                                                echo $ectrpcobj;
                                            ?>
                                        </td>
                                    </tr>                                
                                    <tr>
                                        <td class="textonormal" bgcolor="#DCEDF7" width="50%" height="20">Valor Global com Aditivos/Apostilamentos</td>
                                        <td class="textonormal" width="50%">
                                            <?php
                                                echo $objFuncoesGerais->valorGlobal($idContrato);
                                            ?>
                                        </td>
                                    </tr>                                
                                    <tr>
                                        <td class="textonormal" bgcolor="#DCEDF7" width="50%">
                                            Valor Total da Medições
                                        </td>     
                                        <td class="textonormal" width="50%">
                                            <?php
                                                echo $valorTotalMedicao;
                                            ?>
                                        </td>                                  
                                    </tr>
                                    <tr>
                                        <td class="textonormal" bgcolor="#DCEDF7" width="50%">
                                            Saldo a Executar
                                        </td>
                                        <td class="textonormal" width="50%">
                                            <?php
                                                echo $objFuncoesGerais->saldoAExecutar($idContrato);
                                            ?>
                                        </td>                                       
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                    </td>
                </tr>

                <tr style="<?php echo empty($medicoes)?"display:none":'';?>">
                    <td width="150"></td>
                    <td class="textonormal" width="89%">
                        <table width="100%" border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal"  bgcolor="#FFFFFF">
                            
                                <tr>
                                    <td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3" colspan="4">
                                                    MEDIÇÕES DO CONTRATO
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
                                                        Nº DA MEDIÇÃO
                                                        </td>
                                                        <td align="center" bgcolor="#DDECF9"  class="titulo3">
                                                        PERÍODO DE MEDIÇÃO
                                                        </td>
                                                        <td align="center" bgcolor="#DDECF9"  class="titulo3">
                                                        VALOR DA MEDIÇÃO
                                                        </td>
                                                        
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php echo $html;?>
                                                </tbody>
                                                <tr>
                                                    <td class="textonormal" align="right" colspan="6">
                                                        <!-- <input type="button" name="IncluirMedicao" title="Incluir" value="Incluir" class="botao" id="btnIncluir"> -->
                                                        <input type="button" name="AlterarMedicao" title="Alterar" value="Alterar" class="botao" id="btnAlterar">
                                                        <input type="button" name="ExcluirMedicao" title="Excluir" value="Excluir" class="botao" id="btnExcluir">
                                                        <input type="button" name="btnVoltar" title="Voltar" value="Voltar" class="botao" id="btnVoltar">
                                                        <input type="hidden" name="idContrato" id="idContrato" value="<?php echo $idContrato ?> "> 
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
                <tr style="<?php echo empty($medicoes)?'':"display:none";?>">
                    <td width="150"></td>
                    <td class="textonormal" width="89%">
                        <table width="100%" border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal"  bgcolor="#FFFFFF">
                            
                                <tr>
                                    <td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3" colspan="6">
                                        RESULTADO DA PESQUISA
                                    </td>
                                    <tr>
                                        <td align="center"  colspan="4" >
                                            Não há medições cadastradas neste contrato.                                            
                                        </td>                                    
                                    </tr>   
                                </tr>
                        </table>
                    </td>
                </tr>
                <tr style="<?php echo empty($medicoes)?'':"display:none";?>">
                    <td colspan="8"><button type="button" name="btnVoltar1" class="botao" id="btnVoltar1" style="float:right">Voltar</button></td>
                </tr>
                    <input type="hidden" id="btnVoltar1" name="btnVoltar1">
            </table>
        </form>

	    <br><br>
    </body>
</html>
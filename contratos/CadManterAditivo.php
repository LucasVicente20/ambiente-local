<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadPregaoPresencialSessaoPublica.php
# Autor:    Eliakim Ramos | João Madson | Edson Dionisio
# Data:     17/04/2020
# Objetivo: Programa de incluir contrato
#-------------------------------------------------------------------------


# Acesso ao arquivo de funções #
include "../funcoes.php";
require_once "ClassMedicao.php";


# Executa o controle de segurança	#
session_start();
Seguranca();


$objContrato = new ClassMedicao();
$idContrato = $_POST['idregistro'];

$contrato_pesq = $objContrato->getContrato($idContrato);
$ectrpcnumf = $contrato_pesq[0]->ectrpcnumf;

$medicoes = $objContrato->getAditivosContrato($idContrato);

$html = "";
foreach($medicoes as $medicao){

    $dtInicial_vigencia = explode("-", $medicao->daditiinvg);
    $dtInicial_vigencia = array_reverse($dtInicial_vigencia);
    $dtInicial_vigencia = $dtInicial_vigencia[0] . "/" . $dtInicial_vigencia[1] . "/" . $dtInicial_vigencia[2];

    $dtFinal_vigencia = explode("-", $medicao->daditifivg);
    $dtFinal_vigencia = array_reverse($dtFinal_vigencia);
    $dtFinal_vigencia = $dtFinal_vigencia[0] . "/" . $dtFinal_vigencia[1] . "/" . $dtFinal_vigencia[2];

    $dtInicial_execucao = explode("-", $medicao->daditiinex);
    $dtInicial_execucao = array_reverse($dtInicial_execucao);
    $dtInicial_execucao = $dtInicial_execucao[0] . "/" . $dtInicial_execucao[1] . "/" . $dtInicial_execucao[2];

    $html .= "<tr>";
    $html .= "<td valign=\"top\"> <input data-id=\"$medicao->aaditinuad\" type=\"hidden\" name=\"aaditinuad\" value=\"$medicao->aaditinuad\" />";
    $html .= str_pad($medicao->aaditinuad, 4 , '0' , STR_PAD_LEFT);
    $html .= "</td>";    
    $html .= "<td valign=\"top\">";
    $html .= $medicao->xaditijust;
    $html .= "</td>";    
    $html .= "<td style=\"width:10%\">";
    $html .= $medicao->esitdcdesc;
    $html .= "</td>";
    $html .= "<td valign=\"top\" > <input class=\"aditivos\" type=\"text\" id=\"vigenciaDataInicio$medicao->aaditinuad\" name=\"vigenciaDataInicio$medicao->aaditinuad\" value=\"$dtInicial_vigencia\" maxlength=\"10\" size=\"7\" class=\"data\" />";
    $html .= "<a id=\"calendarioExecIni\" style=\"text-decoration: none\" href=\"javascript:janela('../calendario.php?Formulario=CadAditivoManter&amp;Campo=vigenciaDataInicio$medicao->aaditinuad','Calendario',220,170,1,0)\">";
    $html .= "<img src=\"../midia/calendario.gif\" border=\"0\" alt=\"\"></a>";
    
    
    $html .= "</td>";
    $html .= "<td valign=\"top\" > <input class=\"aditivos\" type=\"text\" id=\"vigenciaDataFinal$medicao->aaditinuad\" name=\"vigenciaDataFinal$medicao->aaditinuad\" onfocusout=\"focusFunction($medicao->aaditinuad)\" value=\"$dtFinal_vigencia\" maxlength=\"10\" size=\"7\" class=\"data\" />";
    $html .= "<a id=\"calendarioExecTerm\" style=\"text-decoration: none\" href=\"javascript:janela('../calendario.php?Formulario=CadAditivoManter&amp;Campo=vigenciaDataFinal$medicao->aaditinuad','Calendario',220,170,1,0)\">";
    $html .= "<img src=\"../midia/calendario.gif\" border=\"0\" alt=\"\"></a>";
    $html .= "</td>";
    $html .= "<td valign=\"top\" > <input class=\"aditivos\" type=\"text\" id=\"execucaoDataInicio$medicao->aaditinuad\" name=\"execucaoDataInicio$medicao->aaditinuad\" value=\"$dtInicial_execucao\" maxlength=\"10\" size=\"7\" class=\"data\" />";
    $html .= "<a id=\"calendarioExecIni\" style=\"text-decoration: none\" href=\"javascript:janela('../calendario.php?Formulario=CadAditivoManter&amp;Campo=execucaoDataInicio$medicao->aaditinuad','Calendario',220,170,1,0)\">";
    $html .= "<img src=\"../midia/calendario.gif\" border=\"0\" alt=\"\"></a>";
    
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

            function focusFunction(id){
                    dei = $("#vigenciaDataInicio"+id).val();
                    def = $("#vigenciaDataFinal"+id).val();
                    dexec = $("#execucaoDataInicio"+id).val();
                    
                    dataVigenciaInicial = dei.split('/');
                    dataVigenciaFinal    = def.split('/');
                    dataExecInicial    = dexec.split('/');

                    novaData = new Date(parseInt(dataVigenciaInicial[2]),parseInt(dataVigenciaInicial[1]-1),parseInt(dataVigenciaInicial[0]));
                    novaDataFim = new Date(parseInt(dataVigenciaFinal[2]),parseInt(dataVigenciaFinal[1]-1),parseInt(dataVigenciaFinal[0]));
                   // novaDataExecucaoInicial = new Date(parseInt(dataExecInicial[2]),parseInt(dataExecInicial[1]-1),parseInt(dataExecInicial[0]));
                    
                    if(novaData > novaDataFim){
                        $('html, body').animate({scrollTop:0}, 'slow');
                        $(".mensagem-texto").html("A data final não pode ser menor que a data inicial.");
                        $(".error").html("Erro!");
                        $("#tdmensagem").show();
//                        alert("A data final não pode ser menor que a data inicial");
                    }
            }

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
                window.location.href="./CadAditivoPesquisar.php";
            });

            $("#btnAlterar").on('click', function(){
                contrato = "<?php echo $ectrpcnumf; ?>";
                
                var retorno = $('.aditivos').closest("tr").map(function(){
                    return $(this).find("input:eq(0)").val() + "." +
                    $(this).find("input:eq(1)").val() + "." +
                    $(this).find("input:eq(2)").val() + "." +
                    $(this).find("input:eq(3)").val();
                })
                .toArray()
                .join(',');

                $.post("postDadosMedicao.php", {op:'validaDatas', datas: retorno}).
                done(function(data){
                    ObjJson = JSON.parse(data);
                    if(ObjJson.status == true){
                        $.post("postDadosMedicao.php", {op:'AlterarAditivo', numcontrato: contrato, resultados: retorno})
                            .done(function(data){
                                ObjJson = JSON.parse(data);
                                $("#mensagem").val("Aditivo alterado com sucesso.");
                                $("#CadAditivoManter").attr("action","CadAditivoPesquisar.php");
                                $("#CadAditivoManter").submit();

                            }).fail(function(data){
                                $('html, body').animate({scrollTop:0}, 'slow');
                                $(".mensagem-texto").html("Não foi possível alterar o registro.");
                                $(".error").html("Erro!");
                                $("#tdmensagem").show();
                            });
                    }else{
                        $('html, body').animate({scrollTop:0}, 'slow');
                        $(".mensagem-texto").html(ObjJson.msm);
                        $(".error").html("Erro!");
                        $("#tdmensagem").show();
                    }
                }).fail(function(data){
                    $('html, body').animate({scrollTop:0}, 'slow');
                    $(".mensagem-texto").html("Não foi possível alterar o registro.");
                    $(".error").html("Erro!");
                    $("#tdmensagem").show();
                });                
            });
        });
        <?php MenuAcesso(); ?>
    </script>
    <link rel="stylesheet" type="text/css" href="../estilo.css?v=<?php echo time();?>">
    <body background="../midia/bg.gif" marginwidth="0" marginheight="0">
        <script language="JavaScript" src="../menu.js"></script>
        <script language="JavaScript">Init();</script>
    
	
	<br><br>
        <form action="<?=$programa?>" method="post" name="CadAditivoManter" id="CadAditivoManter">
            <input type="hidden" name="op" value="AlterarAditivo">
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
                            </a> > Contrato > Aditivos > Manter
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
                                            ADITIVOS DO CONTRATO
                            </td>
                        </tr>
                        <tr>
                            <td colspan="8">
                                <table border="0" width="100%" summary=""class="table">
                                    <tr>
                                        <td class="textonormal" bgcolor="#DCEDF7" width="15%"><strong>Número do Contrato/Ano : </strong></td>
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

                <tr>
                    <td width="150"></td>
                    <td class="textonormal" width="89%">
                        <table width="100%" border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal"  bgcolor="#FFFFFF">
                            
                                <tr>
                                    <td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3" colspan="4">
                                                    ADITIVOS - ALTERAR DATAS
                                    </td>
                                </tr>
                                <tr>
                                    <td align="center">
                                        <form name="formTableMedicoes" id="formTableMedicoes" style="display:none;" >                                    
                                            <table width="100%" id="tablePesquisaMedicoes" border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal"  bgcolor="#FFFFFF">
                                                <thead>
                                                    <tr>
                                                        <td align="center" bgcolor="#DDECF9"  class="titulo3">
                                                        ADITIVO
                                                        </td>
                                                        <td align="center" bgcolor="#DDECF9"  class="titulo3">
                                                        JUSTIFICATIVA DO ADITIVO
                                                        </td>
                                                        <td align="center" bgcolor="#DDECF9"  class="titulo3">
                                                        SITUAÇÃO
                                                        </td>
                                                        <td align="center" bgcolor="#DDECF9"  class="titulo3">
                                                        DATA INICIAL DE VIGÊNCIA
                                                        </td>
                                                        <td align="center" bgcolor="#DDECF9"  class="titulo3">
                                                        DATA FINAL DE VIGÊNCIA
                                                        </td>
                                                        <td align="center" bgcolor="#DDECF9"  class="titulo3">
                                                        DATA INICIAL DE EXECUÇÃO
                                                        </td>

                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php echo $html;?>
                                                </tbody>
                                                <tr>
                                                    <td class="textonormal" align="right" colspan="8">
                                                        <input type="button" name="AlterarMedicao" title="Salvar" value="Salvar" class="botao" id="btnAlterar">
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
            </table>
        </form>

	    <br><br>
    </body>
</html>
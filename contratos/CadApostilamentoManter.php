<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadApostilamentoManter.php
# Autor:    Eliakim Ramos | João Madson | Edson Dionisio
# Data:     17/04/2020
# Objetivo: Programa de incluir contrato
#-------------------------------------------------------------------------
# Autor:    Madson Felix
# Data:     28/04/2021
# CR #246939
# -------------------------------------------------------------------------
# Autor:    Lucas Vicente
# Data:     19/08/2022
# CR #267744
# -------------------------------------------------------------------------


# Acesso ao arquivo de funções #
include "../funcoes.php";
require_once "ClassApostilamento.php";


# Executa o controle de segurança	#
session_start();
Seguranca();

$objContrato = new ClassApostilamento();
//!empty($_POST['idregistro'])?$_POST['idregistro']:$_GET['idregistro']
$idcontrato = !empty($_POST['idcontrato'])? $_POST['idcontrato'] : $_POST['idregistro'];

if (isset($idcontrato)) {
    $_SESSION['idregistro'] = $idcontrato;
} else {
    $idcontrato = $_SESSION['idregistro'];
}

$contrato_pesq = $objContrato->getContrato($idcontrato);
$ectrpcnumf = $contrato_pesq[0]->ectrpcnumf;

$apostilamentos = $objContrato->getApostilamentosContrato($idcontrato);

$html = "";
foreach($apostilamentos as $apostilamento){
    if($apostilamento->vapostvtap != 0){
        $valorApostilamento = number_format((floatval($apostilamento->vapostvtap)),4,',','.');
    } else{
        $valorApostilamento = number_format((floatval('0')),4,',','.');
    }
    
    $tipo_apostilamento = $objContrato->getApostilamentoNome($apostilamento->ctpaposequ);
    $tipo = $tipo_apostilamento[0]->etpapodesc;
    
    
    if($tipo == 'ALTERAÇÃO DOS DADOS DO PREPOSTO, GESTOR OU FISCAL'){
        $tipo = 'ALTERAÇÃO DOS DADOS DO GESTOR E/OU FISCAL';
    }
    
    $status = $apostilamento->situacao;

    $dtInicial = explode("-", $apostilamento->dapostcada);
    $dtInicial = array_reverse($dtInicial);
    $dtInicial = $dtInicial[0] . "/" . $dtInicial[1] . "/" . $dtInicial[2];

    $html .= "<tr>";
    $html .= "<td> <input type=\"radio\" id=\"cdocpcsequ$apostilamento->cdocpcsequ\" name=\"cdocpcsequ\" value=\"$apostilamento->cdocpcsequ\" /> </td>";
    $html .= "<td valign=\"top\" >";
    $html .= str_pad($apostilamento->aapostnuap, 4 , '0' , STR_PAD_LEFT);
    $html .= "</td>";
    $html .= "<td valign=\"top\" >";
    $html .=  $tipo ;
    $html .= "</td>";
    $html .= "<td valign=\"top\" >";
    $html .=  $dtInicial ;
    $html .= "</td>";
    $html .= "<td valign=\"top\" >";
    $html .= $valorApostilamento;
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
                window.location.href="./CadApostilamentoManterPesquisar.php";
            });
            $("#btnVoltar1").on('click', function(){
                window.location.href="./CadApostilamentoManterPesquisar.php";
            });

            $("#btnAlterar").on('click', function(){
                    const codContrato =$("#idregistro").val();
                    const codigo = $("input[name='cdocpcsequ']:checked").val();
                    if(codigo > 0){
                        window.location.href="./CadApostilamentoAlterar.php?idregistro="+codigo;
                    }else{
                        $('html, body').animate({scrollTop:0}, 'slow');
                        $(".mensagem-texto").html("Informe: Apostilamento selecionado.");
                        $(".error").html("Erro!");
                        $("#tdmensagem").show();
                    }
                    
                    
            });

            $("#btnExcluir").on('click', function(){
                const codContrato = $("#idregistro").val();
                const codigo = $("input[name='cdocpcsequ']:checked").val();

                var resposta = confirm("Deseja remover esse registro?");
                if (resposta == true) {
                    $.post("postDadosApostilamento.php", {op:"ExcluirApostilamento", contrato: codigo})
                        .done(function(data){                            
                            $('html, body').animate({scrollTop:0}, 'slow');
                            $("#mensagem").val("Apostilamento removido com sucesso.");
                            $("#formulario").attr("action","CadApostilamentoManter.php");
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
            <input type="hidden" name="idcontrato" value="<?php echo $idContrato;?>">
            <br><br><br><br><br>
            <table cellpadding="3" border="0" summary="">
                <!-- Caminho -->
                    <tr>
                        <td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
                        <td align="left" class="textonormal" colspan="2">
                            <font class="titulo2">|</font>			
                            <a href="../index   .php">
                                    <font color="#000000">Página Principal</font>
                            </a> > Contratos  > Apostilamento > Manter
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
                                            MANTER APOSTILAMENTO CONTRATO
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

                <tr style="<?php echo empty($apostilamentos)?"display:none":'';?>">
                    <td width="150"></td>
                    <td class="textonormal" width="89%">
                        <table width="100%" border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal"  bgcolor="#FFFFFF">
                            
                                <tr>
                                    <td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3" colspan="4">
                                                    APOSTILAMENTOS DO CONTRATO
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
                                                        APOSTILAMENTO
                                                        </td>
                                                        <td align="center" bgcolor="#DDECF9"  class="titulo3">
                                                        TIPO DO APOSTILAMENTO
                                                        </td>
                                                        <td align="center" bgcolor="#DDECF9"  class="titulo3">
                                                        DATA DO APOSTILAMENTO
                                                        </td>
                                                        <td align="center" bgcolor="#DDECF9"  class="titulo3">
                                                        VALOR DO APOSTILAMENTO
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
                                                        <input type="button" name="AlterarApostilamento" title="Alterar" value="Alterar" class="botao" id="btnAlterar">
                                                        <input type="button" name="ExcluirApostilamento" title="Excluir" value="Excluir" class="botao" id="btnExcluir">
                                                        <input type="button" name="btnVoltar" title="Voltar" value="Voltar" class="botao" id="btnVoltar">
                                                        <input type="hidden" name="idcontrato" id="idcontrato" value="<?php echo $idcontrato ?> "> 
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
                <tr style="<?php echo empty($apostilamentos)?'':"display:none";?>">
                    <td width="150"></td>
                    <td class="textonormal" width="89%">
                        <table width="100%" border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal"  bgcolor="#FFFFFF">
                            
                                <tr>
                                    <td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3" colspan="6">
                                        RESULTADO DA PESQUISA
                                    </td>
                                    <tr>
                                        <td align="center"  colspan="4" >
                                            Não há apostilamentos cadastrados neste contrato.                                            
                                        </td>                                    
                                    </tr>   
                                </tr>
                        </table>
                    </td>
                </tr>
                <tr style="<?php echo empty($apostilamentos)?'':"display:none";?>">
                    <td colspan="8"><button type="button" name="btnVoltar1" class="botao" id="btnVoltar1" style="float:right">Voltar</button></td>
                </tr>
                    <input type="hidden" id="btnVoltar1" name="btnVoltar1">
            </table>
        </form>

	    <br><br>
    </body>
</html>
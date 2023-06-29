<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadPregaoPresencialSessaoPublica.php
# Autor:    Eliakim Ramos | João Madson | Edson Dionisio
# Data:     17/04/2020
# Objetivo: Programa de incluir contrato
#-------------------------------------------------------------------------
# Autor:    Madson Felix
# Data:     07/05/2021
# CR #246939
# -------------------------------------------------------------------------
# Autor:    Madson Felix
# Data:     12/05/2021
# CR #248182
# -------------------------------------------------------------------------

function ExibeAbaContratoMedicao(){
    # Acesso ao arquivo de funções #
    $objContrato = new ContratoConsolidado();
    //!empty($_POST['idregistro'])?$_POST['idregistro']:$_GET['idregistro']
    $idContrato = $_POST['idregistro'];
    //var_dump($idContrato);die;
    $contrato_pesq = $objContrato->getContrato($idContrato);
    $ectrpcnumf = $contrato_pesq[0]->ectrpcnumf;
    $ectrpcobj = $contrato_pesq[0]->ectrpcobje;
    $cdocpcsequ = $contrato_pesq[0]->cdocpcsequ;
    $valorGlobaladt = !empty($contrato_pesq[0]->vctrpcglaa)?$contrato_pesq[0]->vctrpcglaa:$contrato_pesq[0]->vctrpcvlor;
    $AllAditivos = $objContrato->getAditivosByContratoConsolidado($idContrato);
    $AllMedicao  = $objContrato->getMedicoesContrato($idContrato);
    $AllApostilamento  = $objContrato->getApostilamentosContrato($idContrato);
    
    $valorContratoAntigo = $contrato_pesq[0]->vctrpcglaa;

    $valorTotalMedicao = $objContrato->getValorTotalMedicao($idContrato);


    $vtAditivo = $objContrato->GetValorTotalAdtivo($idContrato);
    //  var_dump($vtAditivo);die;
    $vtApost = $objContrato->GetValorTotalApostilamento($idContrato);
    // var_dump($vtApost);die;
    $valorTotalMedicao = $objContrato->getValorTotalMedicao($idContrato);
    $saldoexecutadocontantico = !empty($contrato_pesq[0]->saldoexecutadocontantico)?floatval($contrato_pesq[0]->saldoexecutadocontantico)+floatval(str_replace(',','.',str_replace('.','',$valorTotalMedicao))):0;
    $saldoexecutadocontanticosemnovamedicao = !empty($contrato_pesq[0]->saldoexecutadocontantico)?floatval($contrato_pesq[0]->saldoexecutadocontantico):0;
    
    $valororiginal = floatval($contrato_pesq[0]->valororiginal);
    // var_dump($valororiginal);
    $SaldoExec = 0;
    $vtAd = floatval( $vtAditivo[0]->vtaditivo);
    // var_dump($vtAd);
    $vtAp = floatval($vtApost[0]->vtapost);
    // var_dump($vtAp);
    $valorTotal = $contrato_pesq[0]->valortotalcontrato;
    // var_dump($valorTotal);
    $vtOgAdAp = ((floatval($valororiginal) + $vtAd) + $vtAp);
    // var_dump($vtOgAdAp);

    $SCC = $contrato_pesq[0]->scc;
    if(!empty($SCC)){
        $SaldoExec = $objContrato->SaldoAExecutar($idContrato, $vtOgAdAp);
        // var_dump($SaldoExec);
        $valorGlobal = !empty($SaldoExec) ? $SaldoExec : $objContrato->SaldoAExecutar($idContrato, ($valororiginal + floatval($vtAditivo[0]->vtaditivo) + floatval($vtApost[0]->vtapost)));
    }else{
       
        // $valorGlobal = $objContrato->SaldoAExecutar($idContrato, ($valorContratoAntigo+floatval($vtAditivo[0]->vtaditivo) + floatval($vtApost[0]->vtapost)));  
        $valorGlobal = $objContrato->SaldoAExecutar($idContrato, (floatval($valorContratoAntigo)+ floatval($vtAditivo[0]->vtaditivo) + floatval($vtApost[0]->vtapost)-$saldoexecutadocontanticosemnovamedicao));  
    }
    //var_dump($SCC);die;
    


    //$valor_total = number_format((floatval($valorGlobal - $valorTotalMedicao) - floatval('0.0000')),4,',','.');

    $medicoes = $objContrato->getMedicoesContrato($idContrato);

    $html = "";
    foreach($medicoes as $medicao){

        if($medicao->vmedcovalm != 0){
            $valorMedicao = number_format((floatval($medicao->vmedcovalm)),4,',','.');
        } else{
            $valorMedicao = number_format((floatval('0')),4,',','.');
        }

        /*if(!empty($medicao->dmedcopapr)){
            $status = "APROVADO";
        }else{
            $status = "CADASTRADO";
        }*/

        $dtInicial = explode("-", $medicao->dmedcoinic);
        $dtInicial = array_reverse($dtInicial);
        $dtInicial = $dtInicial[0] . "/" . $dtInicial[1] . "/" . $dtInicial[2];

        $dtFinal = explode("-", $medicao->dmedcofinl);
        $dtFinal = array_reverse($dtFinal);
        $dtFinal = $dtFinal[0] . "/" . $dtFinal[1] . "/" . $dtFinal[2];

        $html .= "<tr>";
        // $html .= "<td> <input type=\"radio\" id=\"amedconume$medicao->amedconume\" name=\"amedconume\" value=\"$medicao->cmedcosequ\" /> </td>";
        $html .= "<td valign=\"top\" >";
        $html .= str_pad($medicao->amedconume, 4 , '0' , STR_PAD_LEFT);
        $html .= "</td>";
        $html .= "<td valign=\"top\" >";
        $html .=  $dtInicial . " à " . $dtFinal ;
        $html .= "</td>";
        $html .= "<td valign=\"top\" >";
        $html .= $valorMedicao;
        $html .= "</td>";
        /*$html .= "<td valign=\"top\" >";
        //$html .= $status;
        $html .= "</td>";*/
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
           function Submete(Destino, id=null){
            document.CadContratoConsolidado.Destino.value = Destino;
            document.CadContratoConsolidado.idAditivo.value = id;
            document.CadContratoConsolidado.submit();
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
                                //return false;
                                //console.log(data);
                                //console.log(codContrato);
                                //console.log(documento);
                                //ObjJson = JSON.parse(data);
                                //console.log(ObjJson);
                                
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
                // $.post("postDadosMedicao.php",{op:"ExcluirMedicao", contrato: codContrato, doc: documento}, function(data){
                //         ObjJson = JSON.parse(data);
                //         console.log(ObjJson);
                //  });
                });

            });
            <?php MenuAcesso(); ?>
        </script>
        <link rel="stylesheet" type="text/css" href="../estilo.css?v=<?php echo time();?>">
        <body background="../midia/bg.gif" marginwidth="0" marginheight="0">
            <script language="JavaScript" src="../menu.js"></script>
            <script language="JavaScript">Init();</script>
        
        
        <br><br>
            <form action="CadContratoConsolidado.php" method="post" id="FormContrato" name="CadContratoConsolidado">
                <input type="hidden" name="mensagem" id="mensagem">
                <input type="hidden" name="idregistro" value="<?php echo $idContrato;?>">
                <br><br><br><br><br>
                <table width="100%" cellpadding="3" class="textonormal" summary="">
                <!-- Caminho -->
                <tr>
                    <td width="80"><img border="0" src="../midia/linha.gif" alt=""></td>
                    <td align="left" class="textonormal" colspan="2">
                        <font class="titulo2">|</font>
                        <a href="../index.php"><font color="#000000">Página Principal</font></a> > Contratos > Contrato Consolidado
                    </td>
                </tr>
                <!-- Fim do Caminho-->
                <!-- Corpo -->
                <tr>
                    <td width="80"></td>
                    <td class="textonormal">
                        <table  cellspacing="0" cellpadding="3" summary=""  width="100%" >
                            <tr>
                                <td class="textonormal">
                                <?php echo NavegacaoAbasConsolidadoComMedicaoEAditivo(on,off,off,count($AllMedicao),off,$AllAditivos,off,$AllApostilamento); ?>
                                    <table id="scc_material" summary="" style="border: 1px solid #75ade6; border-radius: 4px;"  width="100%" class="textonormal">
                                        <!-- Corpo -->
                                        <tr>
                                            <td class="textonormal" width="89%">
                                            <table width="100%" border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal"  bgcolor="#FFFFFF">
                                                <tr>
                                                    <td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3" colspan="4">
                                                                    MEDIÇÃO CONTRATO CONSOLIDADO
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td colspan="8">
                                                        <table border="0" width="100%" summary="">
                                                            <tr>
                                                                <td class="textonormal" bgcolor="#DCEDF7" width="50%">Número do Contrato/Ano  </td>
                                                                <td class="textonormal"  width="50%">
                                                                    <?php
                                                                        echo $ectrpcnumf;
                                                                    ?>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td class="textonormal" bgcolor="#DCEDF7" width="50%" height="20">Objeto </td>
                                                                <td class="textonormal" width="50%">
                                                                    <?php
                                                                        echo $ectrpcobj;
                                                                    ?>
                                                                </td>
                                                            </tr>                                
                                                            <tr>
                                                                <td class="textonormal" bgcolor="#DCEDF7" width="50%">
                                                                    Valor Total da Medições 
                                                                </td>     
                                                                <td class="textonormal" width="50%">
                                                                    <?php
                                                                        
                                                                        echo number_format(str_replace('.','',$valorTotalMedicao),4,',','.');
                                                                    ?>
                                                                </td>                                  
                                                            </tr>
                                                            <tr>
                                                                <td class="textonormal" bgcolor="#DCEDF7" width="50%">
                                                                    Saldo a Executar 
                                                                </td>
                                                                <td class="textonormal" width="50%">
                                                                    <?php
                                                                        echo $valorGlobal;
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
                                                                                <td align="center" bgcolor="#DDECF9"  class="titulo3">
                                                                                Nº DA MEDIÇÃO
                                                                                </td>
                                                                                <td align="center" bgcolor="#DDECF9"  class="titulo3">
                                                                                PERÍODO DE MEDIÇÃO
                                                                                </td>
                                                                                <td align="center" bgcolor="#DDECF9"  class="titulo3">
                                                                                VALOR DA MEDIÇÃO
                                                                                </td>
                                                                              <!--  <td align="center" bgcolor="#DDECF9"  class="titulo3">
                                                                                SITUAÇÃO DA MEDIÇÃO2
                                                                                </td> -->

                                                                            </tr>
                                                                        </thead>
                                                                        <tbody>
                                                                            <?php echo $html;?>
                                                                        </tbody>
                                                                        <tr>
                                                                            <td class="textonormal" align="right" colspan="6">
                                                                                <!-- <input type="button" name="IncluirMedicao" title="Incluir" value="Incluir" class="botao" id="btnIncluir"> -->
                                                                                <input type="button" value="Voltar" class="botao" id="btnvoltar">
                                                                                <input type="hidden" name="Botao" value="">
                                                                                <input type="hidden" name="Origem" value="A">
                                                                                <input type="hidden" name="Destino">
                                                                                <input type="hidden" name="idAditivo">
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
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <!-- Fim do Corpo -->
            </table>
            </form>

            <br><br>
        </body>
    </html>
<?php 
}
?>
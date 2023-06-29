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
# Autor:    Osmar Celestino
# Data:     02/06/2021
# CR #248619
# -------------------------------------------------------------------------

function ExibeAbaContratoAditivo($id){
    # Acesso ao arquivo de funções #
    $objContrato = new ContratoConsolidado();
    $ObjContratoManter = new ContratoManter();
    //!empty($_POST['idregistro'])?$_POST['idregistro']:$_GET['idregistro']
    $idContrato = $_POST['idregistro'];
    //var_dump($idContrato);die;
    $contrato_pesq = $objContrato->getContrato($idContrato);
    
    $aditivo = $objContrato->getAditivosByContratoConsolidado($idContrato);
    $tipos_aditivo = $objContrato->GetDescricaoTipoAditivo($aditivo[$id]->ctpadisequ);
    $situacaoAditivo = $objContrato->situacaoAditivo();
    $AllAditivos = $objContrato->getAditivosByContratoConsolidado($idContrato);
    $AllMedicao  = $objContrato->getMedicoesContrato($idContrato);
    $AllApostilamento  = $objContrato->getApostilamentosContrato($idContrato);
    $ectrpcnumf = $contrato_pesq[0]->ectrpcnumf;
    $ectrpcobj = $contrato_pesq[0]->ectrpcobje;
    $cdocpcsequ = $contrato_pesq[0]->cdocpcsequ;
    $valorGlobaladt = !empty($contrato_pesq[0]->vctrpcglaa)?$contrato_pesq[0]->vctrpcglaa:$contrato_pesq[0]->vctrpcvlor;
    
    $valorContratoAntigo = $contrato_pesq[0]->saldoaexecutarcontantico;

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
        $valorGlobal = $objContrato->SaldoAExecutar($idContrato, (floatval($valorGlobaladt)+$valorContratoAntigo)-($saldoexecutadocontanticosemnovamedicao));  
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
           function Submete(Destino, id=null, idApos=null){
            document.CadContratoConsolidado.Destino.value = Destino;
            document.CadContratoConsolidado.idAditivo.value = id;
            document.CadContratoConsolidado.idApostilamento.value = idApos;
            document.CadContratoConsolidado.submit();
           }
           function voltarTelaInicial(){
                    window.location.href = "./CadContratoConsolidadoPesquisar.php";
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
                    console.log("teste");
                    window.location.href = "./CadContratoConsolidadoPesquisar.php";
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
                                <table border="0" cellspacing="0" cellpadding="3" summary="" width="1024px" bgcolor="#FFFFFF">
                                    <tr>
                                        <td class="textonormal" border="3px" bordercolor="#75ADE6">
                                            <table border="1" cellpadding="3" cellspacing="0" bordercolor="white" summary="" class="textonormal" bgcolor="#FFFFFF">
                                                <tr>
                                                    <td align="left">
                                                        <table cellpadding="0" cellspacing="0" border="0" bordercolor="#75ADE6" width="1024px" summary="">
                                                            <tr bgcolor="#bfdaf2">
                                                                <!-- <td colspan="4"> -->
                                                                <table class="textonormal" id="scc_material" summary="" bordercolor="#75ADE6" style="border: 1px solid #75ade6; border-radius: 4px;" width="100%">
                                                                    <thead>
                                                                        <td class="titulo3" colspan="17" align="center" bgcolor="#75ADE6" valign="middle"> <b>ADITIVO</b></td>
                                                                    </thead>
                                                                    <tbody>

                                                                        <tr>
                                                                            <td bgcolor="#DCEDF7" class="textonormal" width="225px">Número do Contrato/Ano 
                                                                            <td style="font-size: 10.6667px;" bgcolor="White">
                                                                                <?php
                                                                                    echo $ectrpcnumf;
                                                                                ?>
                                                                            </td>
                                                    </td>
                                                </tr>

                                                <tr>
                                                    <td bgcolor="#DCEDF7" class="textonormal" width="225px">Número do Aditivo * 
                                                    <td class="textonormal">
                                                        <?php echo $aditivo[$id]->aaditinuad; ?>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td bgcolor="#DCEDF7" class="textonormal" width="225px">Tipo de Aditivo (SAGRES TCE)* </td>
                                                    <td class="textonormal">
                                                            <?php
                                                                    echo $tipos_aditivo->etpadidesc; 
                                                            ?>
                                                    </td>
                                                </tr>

                                                
                                                </tr>
                                                </tr>
                                                <tr id="linhasresForn" <?php 
                                                
                                                echo ($aditivo[$id]->ctpadisequ != 13)? 'style="display:none;"':"" 
                                                
                                                ?> >
                                                    <td bgcolor="#DCEDF7" class="textonormal"  width="225px">
                                                    Contratado*
                                                    <td class="textonormal">
                                                        <table id="_gridContratadoNovo">
                                                            <tbody><tr><td class="labels">
                                                                <span id="_panelLblCpfCnpj">
                                                                <label for="" style=";" class="textonormal" ><?php 
                                                                function validaCPF($CnpjCpf) {
                                                                        
                                                                        $CnpjCpf = preg_replace( '/[^0-9]/is', '', $CnpjCpf );

                                                                        if (strlen($CnpjCpf) != 11) {
                                                                            return false;
                                                                        }


                                                                        if (preg_match('/(\d)\1{10}/', $CnpjCpf)) {
                                                                            return false;
                                                                        }

                                                                        for ($t = 9; $t < 11; $t++) {
                                                                            for ($d = 0, $c = 0; $c < $t; $c++) {
                                                                                $d += $CnpjCpf[$c] * (($t + 1) - $c);
                                                                            }
                                                                            $d = ((10 * $d) % 11) % 10;
                                                                            if ($CnpjCpf[$c] != $d) {
                                                                                return false;
                                                                            }
                                                                        }
                                                                        return true;

                                                                        }
                                                                        if(!empty($aditivo[$id]->eaditicpfc) ){
                                                                            $cpfCNPJ = $ObjContratoManter->MascarasCPFCNPJ(!empty($aditivo[$id]->eaditicpfc)?$aditivo[$id]->eaditicpfc:$aditivo[$id]->eaditicgcc);
                                                                            //var_dump($fornAltAditivo[0]->cnpj);die;  
                                                                        }
                                                                        else
                                                                        { 
                                                                            $cpfCNPJ =  !empty($aditivo[$id]->eaditicpfc)?$aditivo[$id]->eaditicpfc:$aditivo[$id]->eaditicgcc;
                                                                          
                                                                        }

                                                                        $validaCpfCnpj = validaCPF($CnpjCpf_forn);

                                                                        if($validaCpfCnpj == true)
                                                                        {
                                                                        
                                                                        echo  'CPF do Contratado: ';
                                                                        }
                                                                        else {
                                                                        echo  'CNPJ do Contratado: ' ;
                                                                        }

                                                                        ?> 
                                                                </label></span></td><td class="textonormal" colspan="3"><div id="_panelInputCpfCnpj" name="_panelInputCpfCnpj"><?php echo $ObjContratoManter->MascarasCPFCNPJ(!empty($aditivo[$id]->eaditicpfc)?$aditivo[$id]->eaditicpfc:$aditivo[$id]->eaditicgcc); ?>
                                                        <?php  $CnpjCpf_forn= !empty($aditivo[$id]->eaditicpfc)?$aditivo[$id]->eaditicpfc:$aditivo[$id]->eaditicgcc ?> <label>
                                                                    </label></div></td></tr>
                                                                    <tr><td class="labels"><label for="" style=";" class="textonormal">Razão Social :</label></td>
                                                                    <td class="textonormal" colspan="3" ><div id="_panelGroupRazao"><span style="font-size: 10.6667px;" id="_razaoSocialfornecedor" name="razao"><?php if(!is_null($aditivo[$id]->naditirazs)){ echo $aditivo[$id]->naditirazs; } ?></span></div></td></tr>
                                                                    <tr><td class="labels"><label for="" style=";" class="textonormal">Logradouro :</label></td>
                                                                    <td class="textonormal" colspan="3"><span style="font-size: 10.6667px;" id="_logradourofornecedor"><?php if(!is_null($aditivo[$id]->eaditilogr)){  echo $aditivo[$id]->eaditilogr; } ?></span></td></tr>
                                                                    <tr><td class="labels"><label for="" style=";" class="textonormal">Complemento :</label></td>
                                                                    <td class="textonormal"><span style="font-size: 10.6667px;" id="_complementoLogradourofornecedor"><?php if(!is_null($aditivo[$id]->eaditicomp)){  echo $aditivo[$id]->eaditicomp; } ?></span></td>
                                                                    <td class="labels"><label for="" style=";" class="textonormal">Bairro:</label></td>
                                                                    <td class="textonormal"><span id="_bairrofornecedor"><?php if(!is_null($aditivo[$id]->eaditibair)){  echo $aditivo[$id]->eaditibair; } ?></span></td></tr>
                                                                    <tr><td class="labels"><label for="" style=";" class="textonormal">Cidade :</label></td>
                                                                    <td class="textonormal"><span style="font-size: 10.6667px;" id="_cidadefornecedor"><?php if(!is_null($aditivo[$id]->naditicida)){  echo $aditivo[$id]->naditicida; } ?></span></td>
                                                                    <td class="labels"><label for="" style=";" class="textonormal">UF:</label></td>
                                                                    <td class="textonormal"><span id="_estadofornecedor"><?php if(!is_null($aditivo[$id]->caditiesta)){  echo $aditivo[$id]->caditiesta; } ?></span></td></tr>
                                                                    </tbody></table>
                                                    </td>
                                                </td>
                                            </tr>

                                                <tr>
                                                    <td bgcolor="#DCEDF7" class="textonormal" width="225px">Situação do Aditivo * </td>
                                                    <td class="textonormal">
                                                            <?php
                                                                foreach ($situacaoAditivo as $key => $situacao) {                                                        
                                                                     echo intval($situacao->cfasedsequ) == intval($aditivo[$id]->fase) ? $situacao->esitdcdesc : ''; 
                                                                }
                                                            ?>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td bgcolor="#DCEDF7" class="textonormal" width="225px">Justificativa do Aditivo * 
                                                    <td style="font-size: 10.6667px;" class="textonormal">
                                                        <?php echo strtoupper($aditivo[$id]->xaditijust); ?>
                                                    </td>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td bgcolor="#DCEDF7" class="textonormal" width="225px">Há Alteração de Prazo* </td>
                                        <td>
                                            <table id="alteracao_prazo">
                                                <tbody>
                                                    <tr>
                                                        <td style="font-size:10.6667px;">
                                                             <?php echo $aditivo[$id]->faditialpz; ?>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </td>
                            </td>
                        </tr>

                        <tr>
                            <td bgcolor="#DCEDF7" class="textonormal" width="225px">Há Alteração de Valor* </td>
                            <td class="textonormal">
                                <table id="alteracao_valor">
                                    <tbody>
                                        <tr>
                                            <td style="font-size:10.6667px;">
                                                <?php echo $aditivo[$id]->faditialvl; ?> 
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td bgcolor="#DCEDF7" class="textonormal" width="225px">Tipo de Alteração de Valor * 
                            <td bgcolor="White">
                                <?php switch (trim($aditivo[$id]->cadititalv)){
                                            case 'RP':
                                             echo "REPACTUAÇÃO";
                                            break;
                                            case 'RQ': 
                                                echo "REEQUILÍBRIO";
                                            break;
                                            case 'QT': 
                                                echo "QUANTITATIVO";
                                            break;
                                            case 'QRP':
                                              echo "QUANTITATIVO COM REPACTUAÇÃO";
                                            break;
                                            case 'QRQ': 
                                                echo "QUANTITATIVO COM REEQUILÍBRIO";
                                            break;
                                      }  
                                ?>
                            </td>
                            </td>
                        </tr>
                        <tr>
                            <td bgcolor="#DCEDF7" class="textonormal" width="225px">Valor Retroativo Repactuação /Reequilíbrio </td>
                            <td class="textonormal">
                               <?php echo !empty($aditivo[$id]->vaditireqc)?  number_format($aditivo[$id]->vaditireqc,4,',','.'): "";?>
                            </td>
                        </tr>
                        <tr>
                            <td bgcolor="#DCEDF7" class="textonormal" width="225px">Valor Total do Aditivo </td>
                            <td class="textonormal">
                                <?php echo!empty($aditivo[$id]->vaditivtad)? number_format($aditivo[$id]->vaditivtad,4,',','.'): "";?>
                            </td>
                        </tr>
                        <tr>
                            <td bgcolor="#DCEDF7" class="textonormal" width="225px">Acréscimo do Prazo de Execução do Aditivo* </td>
                            <td class="textonormal">
                                <?php echo!empty($aditivo[$id]->aaditiapea)? $aditivo[$id]->aaditiapea: "";?>
                            </td>
                        </tr>

                        <tr id="linhaTabelaOS">
                            <td id="colunaVaziaOS" width="225px" bgcolor="#bfdaf2"></td>
                            <td id="colunaDataInicioOS" bgcolor="#bfdaf2" style="width:415px;">
                                <table id="panelDataInicioOrdemServico" class="colorBlue">
                                    <thead>
                                        <tr>
                                            <th class="titulo3" colspan="1" scope="colgroup">
                                                <span style="font-size: 10.6667px;" id="labelDataInicioOrdemServico">Data de Início</span>
                                            </th>
                                        </tr>
                                    </thead>
                                </table>
                            </td>
                            <td id="colunaDataTerminoOS" bgcolor="#bfdaf2" style="width: 256px;">
                                <table id="panelDataTerminoOrdemServico" class="colorBlue">
                                    <thead>
                                        <tr>
                                            <th class="titulo3" colspan="1" scope="colgroup">
                                                <span style="font-size: 10.6667px;" id="labelDataTerminoOrdemServico">Data de Término</span>
                                            </th>
                                        </tr>
                                    </thead>
                                </table>
                            </td>
                        </tr>
                        </tr>
                        <tr>
                            <td bgcolor="#DCEDF7" class="textonormal" width="225px">Vigência * </td>
                            <td class="textonormal">
                                <span id="vigenciaGroup">
                                   <?php 
                                   
                                if($aditivo[$id]->faditialpz == "NAO"){
                                    echo " ";
                                }else{
                                    echo !empty($aditivo[$id]->daditiinvg)? DataBarra($aditivo[$id]->daditiinvg): ''; 
                                }
                                   
                                   ?>
                                </spam>
                            </td>
                            <td class="textonormal">
                                <span id="vigenciaGroup">
                                    <?php 
                                    
                                    if($aditivo[$id]->faditialpz == "NAO"){
                                        echo " ";
                                    }else{
                                        echo !empty($aditivo[$id]->daditifivg)? DataBarra($aditivo[$id]->daditifivg): ''; 
                                    }
                                    
                                    ?>
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td bgcolor="#DCEDF7" class="textonormal" width="225px">Execução * </td>
                                <td class="textonormal">
                                    <span id="execucaoGroup">
                                        <?php
                                        
                                        if($aditivo[$id]->faditialpz == "NAO"){
                                            echo " ";
                                        }else{

                                        echo !empty($aditivo[$id]->daditiinex)? DataBarra($aditivo[$id]->daditiinex): ''; 
                                        }
                                        
                                        ?>
                                    </span>
                                </td>
                            <!-- </span> -->
                                <td class="textonormal">
                                    <span id="execucaoGroup">
                                        <?php 
                                        if(!empty($aditivo[$id]->daditifiex)){
                                            echo DataBarra($aditivo[$id]->daditifiex);
                                        }else if($aditivo[$id]->faditialpz == "NAO"){                              
                                            echo " ";
                                        }
                                        else{
                                            if(!empty($aditivo[$id]->daditiinex)){
                                                //var_dump($aditivo[$id]->daditiinex,$contrato_pesq[0]->cctrpcopex,$aditivo[$id]->aaditiapea);
                                            $DATAEXECUCAOFIM = $ObjContratoManter->CalculaDataFinalDeExecucao($aditivo[$id]->daditiinex,$contrato_pesq[0]->cctrpcopex,$aditivo[$id]->aaditiapea);
                                            $DATAEXECUCAOFIM = explode('/', $DATAEXECUCAOFIM);
                                                $day   = $DATAEXECUCAOFIM[0];
                                                $month = $DATAEXECUCAOFIM[1];
                                                $year  = $DATAEXECUCAOFIM[2];
                                                $DATAEXECUCAOFIM = $day. "-". $month . "-". $year;

                                                $dataFimExec = new DateTime($DATAEXECUCAOFIM);
                                                $dataFimExec->modify('-1 day');
                                                echo $dataFimExec->format('d/m/Y');

                                            // echo $DATAEXECUCAOFIM;
                                            }
                                        }
                                        ?>                                        
                                    </span>
                                </td>
                            <!-- </span> -->
                            </td>
                        </tr>
                        <tr>
                            <td bgcolor="#DCEDF7" class="textonormal" width="225px">Observação do Aditivo </td>
                            <td style="font-size: 10.6667px;" class="textonormal">
                                    <?php echo !empty($aditivo[$id]->xaditiobse)? strtoupper($aditivo[$id]->xaditiobse): ''; ?>
                            </td>
                        </tr>

                        <tr>
                            <thead bgcolor="#bfdaf2">
                                <tr>
                                    <th class="titulo3" colspan="3" scope="colgroup">Representante legal</th>
                                </tr>
                            </thead>
                        </tr>
                        <tr>
                            <td bgcolor="#DCEDF7" class="textonormal" width="225px">Nome 
                            <td bgcolor="White">
                                <?php echo !empty($aditivo[$id]->naditinmrl) ? strtoupper($aditivo[$id]->naditinmrl) : ''; ?>
                            </td>
                            </td>
                        </tr>
                        <tr>
                            <td bgcolor="#DCEDF7" class="textonormal" width="225px">CPF 
                            <td bgcolor="White">
                                <?php echo !empty($aditivo[$id]->eaditicpfr) ? strtoupper($aditivo[$id]->eaditicpfr) : ''; ?>
                            </td>
                            </td>
                        </tr>
                        <tr>
                            <td bgcolor="#DCEDF7" class="textonormal" width="225px">Cargo 
                            <td bgcolor="White">
                                <?php echo !empty($aditivo[$id]->eaditicgrl) ? strtoupper($aditivo[$id]->eaditicgrl) : ''; ?>
                            </td>
                            </td>
                        </tr>
                        <tr>
                            <td bgcolor="#DCEDF7" class="textonormal" width="225px">Identidade 
                            <td bgcolor="White">
                                <?php echo !empty($aditivo[$id]->eaditiidrl) ? $aditivo[$id]->eaditiidrl : ''; ?>
                            </td>
                            </td>
                        </tr>
                        <tr>
                            <td bgcolor="#DCEDF7" class="textonormal" width="225px">Órgão Emissor 
                            <td bgcolor="White">
                                <?php echo !empty($aditivo[$id]->naditioerl) ? strtoupper($aditivo[$id]->naditioerl) : ''; ?>
                            </td>
                            </td>
                        </tr>
                        <tr>
                            <td bgcolor="#DCEDF7" class="textonormal" width="225px">UF da Identidade 
                            <td bgcolor="White">
                                <?php echo !empty($aditivo[$id]->naditiufrl) ? strtoupper($aditivo[$id]->naditiufrl) : ''; ?>
                            </td>
                            </td>
                        </tr>
                        <tr>
                            <td bgcolor="#DCEDF7" class="textonormal" width="250px">Cidade de Domicílio 
                            <td bgcolor="White">
                                <?php echo !empty($aditivo[$id]->naditicdrl) ? strtoupper($aditivo[$id]->naditicdrl) : ''; ?>
                            </td>
                            </td>
                        </tr>
                        <tr>
                            <td bgcolor="#DCEDF7" class="textonormal" width="225px">Estado de Domicílio 
                            <td bgcolor="White">
                                <?php echo !empty($aditivo[$id]->naditiedrl) ? strtoupper($aditivo[$id]->naditiedrl) : ''; ?>
                            </td>
                            </td>
                        </tr>
                        <tr>
                            <td bgcolor="#DCEDF7" class="textonormal" width="225px">Nacionalidade 
                            <td bgcolor="White">
                                <?php echo !empty($aditivo[$id]->naditinarl) ? strtoupper($aditivo[$id]->naditinarl) : ''; ?>
                            </td>
                            </td>
                        </tr>
                        <tr>
                            <td bgcolor="#DCEDF7" class="textonormal" width="225px">Estado Civil 
                            <td bgcolor="White">
                                 <?php 
                                    switch($aditivo[$id]->caditiecrl){
                                        case 'S':
                                            echo strtoupper("Solteiro");
                                        break;
                                        case 'C':
                                            echo strtoupper("Casado");
                                        break;
                                        case 'D':
                                            echo strtoupper("Divorciado");
                                        break;
                                        case 'V':
                                            echo strtoupper("Viúvo");
                                        break;
                                        case 'O':
                                            echo strtoupper("Outros");
                                        break;
                                    } 
                                ?> 
                            </td>
                            </td>
                        </tr>
                        <tr>
                            <td bgcolor="#DCEDF7" class="textonormal" width="225px">Profissão 
                            <td class="textonormal">
                                <?php echo !empty($aditivo[$id]->naditiprrl) ? strtoupper($aditivo[$id]->naditiprrl) : ''; ?>
                            </td>
                            </td>
                        </tr>
                        <tr>
                            <td bgcolor="#DCEDF7" class="textonormal" width="225px">E-mail 
                            <td bgcolor="White" class="textonormal">
                                 <?php echo !empty($aditivo[$id]->naditimlrl) ? $aditivo[$id]->naditimlrl : ''; ?>
                            </td>
                            </td>
                        </tr>
                        <tr>
                            <td bgcolor="#DCEDF7" class="textonormal" width="225px">Telefone(s) 
                            <td bgcolor="White" class="textonormal">
                                    <?php echo !empty($aditivo[$id]->eadititlrl) ? $aditivo[$id]->eadititlrl : ''; ?>
                            </td>
                            </td>
                        </tr>
                        </tr>
                        <tr>
                            <thead class="titulo3" bgcolor="#bfdaf2">
                                <tr>
                                    <th colspan="3" scope="colgroup">DOCUMENTOS ANEXOS</th>
                                </tr>
                            </thead>
                        </tr>
                        <tr>
                            <td colspan="3">
                                <table id="tabelaficais" bgcolor="#bfdaf2" class="textonormal" width="100%">
                                    <tfoot id="FootDOcFiscal">
                                        <tr class="FootFiscaisDoc">
                                            <td colspan="4">ARQUIVO</td>
                                            <td colspan="4">DATA DA INCLUSÃO</td>
                                        </tr>
                                        <?php
                                        $DadosDocAnexo= $objContrato->GetDocumentosAnexosAdtivo($aditivo[$id]->cdocpcseq1,$aditivo[$id]->aaditinuad);
                                        if (!empty($DadosDocAnexo)) {
                                            $k=0;
                                            foreach($DadosDocAnexo as $key => $anexo){
                                                
                                                $_SESSION['arquivo_download'][$k] = $anexo->arquivo;
                                        ?>
                                                <tr bgcolor="#ffffff">
                                                    <td colspan="4">
                                                        <a class="" href="downloadDocContratoConsolidado.php?arquivo=<?php echo $k;?>&nome=<?php echo $anexo->nomearquivo;?>" id="documento<?php echo $k;?>" rel="<?php echo $anexo->nomearquivo;?>"><?php echo $anexo->nomearquivo; ?></a>
                                                        
                                                    </td>
                                                    <td colspan="4"> <?php echo $anexo->datacadasarquivo; ?></td>

                                                </tr>
                                        <?php }
                                        } else {
                                            echo ' <tr bgcolor="#ffffff">';
                                            echo ' <td colspan="8" bgcolor="#ffffff">Nenhum documento informado</td>';
                                            echo ' </tr>';
                                        }
                                        ?>
                                    </tfoot>
                                </table>
                                </br>
                        <tr>
                            <td colspan="4" align="right">
                                <input type="button" name="btnVoltar" title="Voltar" value="Voltar" onclick="voltarTelaInicial()" class="botao" id="btnvoltar">
                            </td>
                        </tr>
                        <input type="hidden" id="Destino" name="Destino">
                        <input type="hidden" id="idAditivo" name="idAditivo">
                        <input type="hidden" id="idApostilamento" name="idApostilamento">
                        <input type="hidden" name="Botao" value="" id="Botao">                       
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
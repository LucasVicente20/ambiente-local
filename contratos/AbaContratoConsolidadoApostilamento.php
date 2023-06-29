<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: AbaContratoConsolidadoApostilamento.php
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

    
function ExibeAbaContratoApostilamento($id){
    # Acesso ao arquivo de funções #
    $objContrato = new ContratoConsolidado();
    $ObjContratoManter = new ContratoManter();
    //!empty($_POST['idregistro'])?$_POST['idregistro']:$_GET['idregistro']
    $idContrato = $_POST['idregistro'];
    //var_dump($idContrato);die;
    $apostilamento = $objContrato->getApostilamentosContrato($idContrato);
    $contrato_pesq = $objContrato->getContrato($idContrato);
    $situacaoAditivo = $objContrato->situacaoAditivo();
    $AllAditivos = $objContrato->getAditivosByContratoConsolidado($idContrato);
    $AllMedicao  = $objContrato->getMedicoesContrato($idContrato);
    $AllApostilamento  = $objContrato->getApostilamentosContrato($idContrato);
    if(!empty($AllApostilamento[$id]->aforcrsequ)){
        $dadosFornecedor = $objContrato->getFornecedorCredenciado($AllApostilamento[$id]->aforcrsequ);
    }
    
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
    
     // var_dump($vtOgAdAp);

     $DDFF = $objContrato->getDocumentosFicaisEFical( $AllApostilamento[$id]->cdocpcseq2);
     $i=0;
     foreach($DDFF as $k){
             $DadosDocFiscaisFiscal[] = (object)  array(
                             'tipofiscal'      => $k->tipofiscal,
                             'fiscalnome'      => $k->fiscalnome,
                             'fiscalmatricula' => $k->fiscalmatricula,
                             'fiscalcpf'       => $objContrato->MascarasCPFCNPJ($k->fiscalcpf),
                             'fiscalemail'     => $k->fiscalemail,
                             'fiscaltel'       => $k->fiscaltel,
                             'docsequ'         =>  $k->docsequ,
                             'registro'         =>  $k->registro,
                             'ent'         =>  $k->entidade,
                             'docsituacao'     => 'ATIVO',
                             'remover'         =>'N'
             );                      
     }
     $dadosAlteradosDoc = $objContrato->GetDocumentosAnexosApostilamentoAlterado($idContrato);
            
     if(!empty($dadosAlteradosDoc)){
         $DadosDocAnexoApostilamento = $objContrato->GetDocumentosAnexosApostilamentoAlterado($AllApostilamento[$id]->cdocpcseq2, $AllApostilamento[$id]->aapostnuap);
     }else{
         $DadosDocAnexoApostilamento = $objContrato->GetDocumentosAnexosApostilamento($AllApostilamento[$id]->cdocpcseq2, $AllApostilamento[$id]->aapostnuap);
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
                        <a href="../index.php"><font color="#000000">Página Principal</font></a> Contratos > Contratos  Consolidado
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
                                                                                        <td class="titulo3" colspan="17" align="center" bgcolor="#75ADE6" valign="middle"> <b>APOSTILAMENTO</b></td>
                                                                                    </thead>
                                                                                    <tbody>
                                                                                        <tr>
                                                                                            <td bgcolor="#DCEDF7" class="textonormal" width="225px">Número do Contrato/Ano 
                                                                                            <td bgcolor="White">
                                                                                                <?php echo $ectrpcnumf; ?>
                                                                                            </td>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td bgcolor="#DCEDF7" class="textonormal" width="225px">Número do Apostilamento 
                                                                    <td class="textonormal" class="textonormal">
                                                                        <?php 
                                                                            echo $AllApostilamento[$id]->aapostnuap; 
                                                                        ?>
                                                                    </td>
                                                        </td>
                                                    </tr>

                                                    <tr>
                                                        <td bgcolor="#DCEDF7">Tipo de Apostilamento* </td>
                                                        <td class="inputs">
                                                        <?php 
                                                            $dadosTiposApostilamento = $objContrato->GetDescricaoTipoApostilamento($AllApostilamento[$id]->ctpaposequ);
                                                            echo $dadosTiposApostilamento->etpapodesc; 
                                                        ?>
                                                        </td>
                                                    </tr>
                                            <tr id="linhasresForn" <?php     
                                                        echo ($AllApostilamento[$id]->ctpaposequ != 5)? 'style="display:none;"':"" 
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
                                                                    
                                                                                if(!empty($dadosFornecedor[0]->aforcrccpf) ){
                                                                                    $cpfCNPJ = $ObjContratoManter->MascarasCPFCNPJ(!empty($dadosFornecedor[0]->aforcrccpf)?$dadosFornecedor[0]->aforcrccpf:$dadosFornecedor[0]->aforcrccgc);
                                                                                }
                                                                                else
                                                                                { 
                                                                                    $cpfCNPJ =  !empty($dadosFornecedor[0]->aforcrccpf)?$dadosFornecedor[0]->aforcrccpf:$dadosFornecedor[0]->aforcrccgc;
                                                                                }

                                                                                $validaCpfCnpj = validaCPF($cpfCNPJ);

                                                                                if($validaCpfCnpj == true)
                                                                                {
                                                                                    echo  'CPF do Contratado: ';
                                                                                }
                                                                                else {
                                                                                    echo  'CNPJ do Contratado: ' ;
                                                                                }
                                                                
                                                                                ?> 
                                                                        </label></span></td><td class="textonormal" colspan="3"><div id="_panelInputCpfCnpj" name="_panelInputCpfCnpj"><?php echo $ObjContratoManter->MascarasCPFCNPJ(!empty($dadosFornecedor[0]->aforcrccpf)?$dadosFornecedor[0]->aforcrccpf:$dadosFornecedor[0]->aforcrccgc); ?>
                                                                            <?php  $CnpjCpf_forn= !empty($dadosFornecedor[0]->aforcrccpf)?$dadosFornecedor[0]->aforcrccpf:$dadosFornecedor[0]->aforcrccgc ?> <label>
                                                                            </label></div></td></tr>
                                                                            <tr><td class="labels"><label for="" style=";" class="textonormal">Razão Social :</label></td>
                                                                            <td class="textonormal" colspan="3" ><div id="_panelGroupRazao"><span style="font-size: 10.6667px;" id="_razaoSocialfornecedor" name="razao"><?php if(!is_null($dadosFornecedor[0]->nforcrrazs)){ echo $dadosFornecedor[0]->nforcrrazs; } ?></span></div></td></tr>
                                                                            <tr><td class="labels"><label for="" style=";" class="textonormal">Logradouro :</label></td>
                                                                            <td class="textonormal" colspan="3"><span style="font-size: 10.6667px;" id="_logradourofornecedor"><?php if(!is_null($dadosFornecedor[0]->eforcrlogr)){  echo $dadosFornecedor[0]->eforcrlogr; } ?></span></td></tr>
                                                                            <tr><td class="labels"><label for="" style=";" class="textonormal">Complemento :</label></td>
                                                                            <td class="textonormal"><span style="font-size: 10.6667px;" id="_complementoLogradourofornecedor"><?php if(!is_null($dadosFornecedor[0]->eforcrcomp)){  echo $dadosFornecedor[0]->eforcrcomp; } ?></span></td>
                                                                            <td class="labels"><label for="" style=";" class="textonormal">Bairro:</label></td>
                                                                            <td class="textonormal"><span id="_bairrofornecedor"><?php if(!is_null($dadosFornecedor[0]->eforcrbair)){  echo $dadosFornecedor[0]->eforcrbair; } ?></span></td></tr>
                                                                            <tr><td class="labels"><label for="" style=";" class="textonormal">Cidade :</label></td>
                                                                            <td class="textonormal"><span style="font-size: 10.6667px;" id="_cidadefornecedor"><?php if(!is_null($dadosFornecedor[0]->nforcrcida)){  echo $dadosFornecedor[0]->nforcrcida; } ?></span></td>
                                                                            <td class="labels"><label for="" style=";" class="textonormal">UF:</label></td>
                                                                            <td class="textonormal"><span id="_estadofornecedor"><?php if(!is_null($dadosFornecedor[0]->cforcresta)){  echo $dadosFornecedor[0]->cforcresta; } ?></span></td></tr>
                                                                            </tbody></table>
                                                            </td>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                            <td bgcolor="#DCEDF7" class="textonormal" width="225px">Situação do Apostilamento 
                                                            <td class="inputs" style="font-size: 10.6667px;">
                                                                <?php echo $AllApostilamento[$id]->esitdcdesc?>
                                                            </td>
                                                    </tr>

                                                    <tr>
                                                        <td bgcolor="#DCEDF7" class="textonormal" width="50px">Valor retroativo Apostilamento </td>
                                                        <td class="textonormal" class="textonormal">
                                                            <?php echo ($AllApostilamento[$id]->vapostretr)?number_format($AllApostilamento[$id]->vapostretr,4,',','.'):"0,0000"; ?>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td bgcolor="#DCEDF7" class="textonormal" width="50px">Valor do Apostilamento </td>
                                                        <td class="textonormal" class="textonormal">
                                                            <?php echo ($AllApostilamento[$id]->vapostvtap)?number_format($AllApostilamento[$id]->vapostvtap,4,',','.'):"0,0000"; ?>

                                                        </td>
                                                    </tr>

                                        <tr>
                                            <td bgcolor="#DCEDF7" class="textonormal" width="225px">Data do Apostilamento </td>
                                            <td class="textonormal">
                                                <?php echo  DataBarra($AllApostilamento[$id]->daditiinex); ?>
                                                
                                            </td>
                                        </tr>



                                        <tr>
                                            <thead bgcolor="#bfdaf2">
                                                <tr>
                                                    <th class="titulo3" colspan="3" <?php echo $bloqueiacampo ? 'disabled="disabled"' : ''; ?> scope="colgroup">GESTOR</th>
                                                </tr>
                                            </thead>
                                        </tr>
                                        <tr>
                                            <td bgcolor="#DCEDF7" class="textonormal" width="225px">Nome 
                                            <td bgcolor="White" class="textonormal">
                                                <?php echo strtoupper($AllApostilamento[$id]->napostnmgt); ?>
                                            </td>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td bgcolor="#DCEDF7" class="textonormal" width="225px">Matrícula 
                                            <td bgcolor="White" class="textonormal">
                                                <?php echo $AllApostilamento[$id]->napostmtgt; ?>
                                            </td>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td bgcolor="#DCEDF7" class="textonormal" width="225px">CPF 
                                            <td bgcolor="White" class="textonormal">
                                                <?php echo $ObjContratoManter->MascarasCPFCNPJ(!empty($AllApostilamento[$id]->napostcpfg)?$AllApostilamento[$id]->napostcpfg:""); ?>
                                            </td>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td bgcolor="#DCEDF7" class="textonormal" width="225px">E-mail 
                                            <td bgcolor="White" class="textonormal">
                                                <?php echo strtoupper($AllApostilamento[$id]->napostmlgt); ?>
                                            </td>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td bgcolor="#DCEDF7" class="textonormal" width="225px">Telefone(s) 
                                            <td bgcolor="White" class="textonormal">
                                                <?php echo $AllApostilamento[$id]->eaposttlgt; ?>
                                            </td>
                                            </td>
                                        </tr>
                                        <tr>
                                            <thead bgcolor="#bfdaf2">
                                                <tr>
                                                    <th class="titulo3" colspan="3" <?php echo $bloqueiacampo ? 'disabled="disabled"' : ''; ?> scope="colgroup">FISCAL(IS)*</th>
                                                </tr>
                                            </thead>
                                        </tr>
                                        <tr>
                                            <!-- Eliakim Ramos 05032019 -->
                                            <td colspan="3" >
                                                <table style="width:100%; border:1px solid #bfdaf2;"  id="tabelaficais" class="textonormal">
                                                        <tr bgcolor="#DCEDF7" style="font-weight: bold;">
                                                            <td colspan="1">Tipo Fiscal</td>
                                                            <td colspan="1">Nome</td>
                                                            <td colspan="1">Matrícula</td>
                                                            <td colspan="1">CPF</td>
                                                            <td colspan="1">Ent. Compet.</td>
                                                            <td colspan="1"> Registro ou INSC.</td>
                                                            <td colspan="1">E-mail</td>
                                                            <td colspan="1">Telefone.</td>
                                                        </tr>
                                                    <tbody id="mostrartbfiscais">
                                                    
                                                    <?php 
                                                        $auxAnt = array();
                                                        if(!empty($DadosDocFiscaisFiscal)){
                                                            foreach($DadosDocFiscaisFiscal as $fiscal){ 
                                                                
                                                                $situacao = $fiscal->docsituacao; 
                                                                
                                                                if( strtoupper($fiscal->remover) == "N"){
                                                                        if(!in_array($fiscal->fiscalcpf,$auxAnt)){
                                                    ?>
                                                                <tr>
                                                                    <td><?php echo $fiscal->tipofiscal;?></td>
                                                                    <td><?php echo $fiscal->fiscalnome;?></td>
                                                                    <td><?php echo $fiscal->fiscalmatricula;?></td>
                                                                    <td><?php echo $fiscal->fiscalcpf;?></td>
                                                                    <td><?php echo !empty($fiscal->ent) ? $fiscal->ent : ""; ?></td>
                                                                    <td><?php echo !empty($fiscal->registro) ? $fiscal->registro : ""; ?></td>
                                                                    <td><?php echo strtoupper($fiscal->fiscalemail);?></td>
                                                                    <td><?php echo $fiscal->fiscaltel;?></td>
                                                                </tr>
                                                    <?php          
                                                                        } 
                                                                    }
                                                                    $auxAnt[] = $fiscal->fiscalcpf;
                                                                }
                                                            }
                                                    ?> 
                                                    </tbody>
                                                </table>
                                    </td>
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
                                                    <tfoot id="FootDOcFiscal" >
                                                        <tr class="FootFiscaisDoc">
                                                            <td colspan="4">ARQUIVO</td>
                                                            <td colspan="4">DATA DA INCLUSÃO</td>
                                                        </tr>

                                                        <?php 
                                                        $DadosDocAnexoApostilamento = $objContrato->GetDocumentosAnexosApostilamento($AllApostilamento[$id]->cdocpcseq2, $AllApostilamento[$id]->aapostnuap);
                                                        if(!empty($DadosDocAnexoApostilamento)){
                                                            $k=0;
                                                            foreach($DadosDocAnexoApostilamento as $key => $anexo){
                                                                $_SESSION['arquivo_download'][$k] = $anexo->arquivo;
                                                        ?>
                                                        <tr bgcolor="#ffffff">
                                                        
                                                            <td colspan="4"> 
                                                                <a class="" href="downloadDocContratoConsolidado.php?arquivo=<?php echo $k;?>&nome=<?php echo $anexo->nomearquivo;?>" id="documento<?php echo $k;?>" rel="<?php echo $anexo->nomearquivo;?>"><?php echo $anexo->nomearquivo; ?></a>
                                                            </td>
                                                            <td colspan="4"> <?php echo $anexo->datacadasarquivo; ?></td>
                                                        
                                                        </tr>
                                                        <?php }
                                                                }else{
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
                        <input type="hidden" id="Destino" name="Destino">
                        <input type="hidden" id="idAditivo" name="idAditivo">
                        <input type="hidden" id="idApostilamento" name="idApostilamento">
                        <input type="hidden" name="Botao" value="" id="Botao">
            </form>

            <br><br>
        </body>
    </html>
<?php 
}
?>

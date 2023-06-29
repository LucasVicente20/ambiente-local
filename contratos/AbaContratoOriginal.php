<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: AbaContrato.php
# Autor:    Eliakim Ramos | João Madson
# Data:     10/12/2019
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
# Autor: Lucas Vicente
# Data:  15/02/2023
# Objetivo: CR #275671
# -------------------------------------------------------------------------
# Exibe Aba Membro de Comissão - Formulário A #
function ExibeAbaContratoOriginal(){ 
    $ObjContrato = new ContratoManter();
    $ObjContratoInc = new Contrato();
    $objMedicao = new ClassMedicao();
    $dadosContratos = (object) array();
    $objConstratoConsolidado = new ContratoConsolidado();
    $dadosGarantia = $ObjContrato->GetListaGarantiaDocumento();
    $idRegistro    = '';
    if ($_SERVER['REQUEST_METHOD'] == "POST") {
       if(!empty($_POST['idregistro'])){
            $_SESSION['idregistro'] = $_POST['idregistro'];
       }
        $idRegistro = !empty($_POST['idregistro'])? $_POST['idregistro']:$_SESSION['idregistro'];
        $dadosItensContrato = $ObjContrato->GetItensContrato($idRegistro);
        $AllMedicao  = $objConstratoConsolidado->getMedicoesContrato($idRegistro);
        $AllAditivos = $objConstratoConsolidado->getAditivosByContratoConsolidado($idRegistro);
        $AllApostilamento  = $objConstratoConsolidado->getApostilamentosContrato($idRegistro);
        $csolcosequ ="";
        $dadosContratos     = $ObjContrato->GetDadosContratoSelecionado($idRegistro);
        if(!empty($dadosContratos->categoriaprocesso)){
            $categoriaProcesso = $ObjContrato->GetDadosCategoriaProcesso($dadosContratos->categoriaprocesso);
        }
        $Aditivos = $ObjContrato->GetAditivos($idRegistro);
        $Apostilamento = $ObjContrato->GetApostilamento($idRegistro);
        $Medicao = $ObjContrato->GetMedicao($idRegistro);
        $vtAditivo = $ObjContrato->GetValorTotalAdtivo($idRegistro);
        $vtApost = $ObjContrato->GetValorTotalApostilamento($idRegistro);
         $valorTotalMedicao = $objMedicao->getValorTotalMedicao($idRegistro);
         $valororiginal = !empty($valorCalculado)?$valorCalculado:floatval($dadosContratos->valororiginal);
        $valorGlobal = $objMedicao->SaldoAExecutar($idRegistro,($valororiginal+floatval( $vtAditivo[0]->vtaditivo)+floatval($vtApost[0]->vtapost)));
        $situacaoContrato = $ObjContrato->GetSituacaoContrato($dadosContratos->codsequfasedoc,$dadosContratos->codsequsituacaodoc);  //colocar quando achar uma situação que se encaxe 
        $temAditivo = count($Aditivos) > 0? true:false;
        $temApostilamento = count($Apostilamento) > 0? true:false;
        $temMedicao = count($Medicao) > 0? true:false;
        $bloqueiacampo = false;
        $_SESSION['doc_fiscal'] = $dadosContratos->seqdocumento;
       
        if(!empty($dadosContratos->orgao) && !empty($dadosContratos->unidade) && !empty($dadosContratos->codisolicitacao) && !empty($dadosContratos->anos)){
            $SCC       = sprintf('%02s', $dadosContratos->orgao) . sprintf('%02s', $dadosContratos->unidade) . '.' . sprintf('%04s', $dadosContratos->codisolicitacao) . '/' . $dadosContratos->anos;
        }
        $TipoCOmpra    = $ObjContrato->GetTipoCompra($dadosContratos->codicompra);
        $cpfCNPJ        = (!empty($dadosContratos->cnpj))?$dadosContratos->cnpj:$dadosContratos->cpf;
        $DadosDocFiscaisFiscal = array();
        if(!empty($_SESSION['fiscal_selecionado'])){
            $DDFF = $ObjContrato->getDocumentosFicaisEFical($dadosContratos->seqdocumento);
            $i=0;
            foreach($DDFF as $k){
              foreach($_SESSION['fiscal_selecionado'] as $f){
                    if($k->fiscalcpf == $f->fiscalcpf){
                        unset($_SESSION['fiscal_selecionado'][$i]);
                    }else{
                        $fiscalselecionado[] = (object)  array(
                            'tipofiscal'      => $f->tipofiscal,
                            'fiscalnome'      => $f->fiscalnome,
                            'fiscalmatricula' => $f->fiscalmatricula,
                            'fiscalcpf'       => $f->fiscalcpf,
                            'fiscalemail'     => $f->fiscalemail,
                            'fiscaltel'       => $f->fiscaltel,
                            'docsequ'         =>  $f->docsequ,
                            'registro'         =>  $f->registro,
                            'ent'         =>  $f->ent,
                            'docsituacao'     => 'ATIVO',
                            'remover'         =>'N'
                         ); 
                    }
                    $i++;
              }
                   $fiscalselecionado[] = (object)  array(
                                    'tipofiscal'      => $k->tipofiscal,
                                    'fiscalnome'      => $k->fiscalnome,
                                    'fiscalmatricula' => $k->fiscalmatricula,
                                    'fiscalcpf'       => $k->fiscalcpf,
                                    'fiscalemail'     => $k->fiscalemail,
                                    'fiscaltel'       => $k->fiscaltel,
                                    'docsequ'         =>  $k->docsequ,
                                    'registro'         =>  $k->registro,
                                    'ent'         =>  $k->ent,
                                    'docsituacao'     => 'ATIVO',
                                    'remover'         =>'N'
                    );                      
            }
             unset( $_SESSION['fiscal_selecionado']);
            $_SESSION['fiscal_selecionado'] = $fiscalselecionado;
            $DadosDocFiscaisFiscal = $_SESSION['fiscal_selecionado'];
        }else{
            $DDFF = $ObjContrato->getDocumentosFicaisEFical($dadosContratos->seqdocumento);
            $i=0;
            foreach($DDFF as $k){
                    $fiscalselecionado[] = (object)  array(
                                    'tipofiscal'      => $k->tipofiscal,
                                    'fiscalnome'      => $k->fiscalnome,
                                    'fiscalmatricula' => $k->fiscalmatricula,
                                    'fiscalcpf'       => $k->fiscalcpf,
                                    'fiscalemail'     => $k->fiscalemail,
                                    'fiscaltel'       => $k->fiscaltel,
                                    'docsequ'         =>  $k->docsequ,
                                    'registro'         =>  $k->registro,
                                    'ent'         =>  $k->ent,
                                    'docsituacao'     => 'ATIVO',
                                    'remover'         =>'N'
                    );                      
            }
             unset( $_SESSION['fiscal_selecionado']);
            $_SESSION['fiscal_selecionado'] = $fiscalselecionado;
            $DadosDocFiscaisFiscal = $_SESSION['fiscal_selecionado'];
        }
            if(!empty($_SESSION['documento_anexo'])){
                foreach($_SESSION['documento_anexo'] as $doc){
                    $DadosDocAnexo[]  =  (object) array(
                                                'nomearquivo'       =>$doc['nome_arquivo'],
                                                'arquivo'           => $doc['arquivo'],
                                                'sequdocumento'     => $doc['sequdoc'],
                                                'sequdocanexo'     => $doc['sequarquivo'],
                                                'datacadasarquivo'  => $doc['data_inclusao'],
                                                'usermod'           => $doc['usermod'],
                                                'arquivo'           => $doc['arquivo'],
                                                'ativo'             => 'S'
                                            );
                }
            }else{
                unset($_SESSION['documento_anexo']);
                $DadosDocAnexo= $ObjContrato->GetDocumentosAnexos($dadosContratos->seqdocumento);
                foreach($DadosDocAnexo as $doc){
                    echo  $doc->sequdocanexo;
                    $_SESSION['documento_anexo'][]  =  array(
                                                'nome_arquivo'       =>$doc->nomearquivo,
                                                'arquivo'           => $doc->arquivo,
                                                'sequdoc'     => $doc->sequdocumento,
                                                'sequarquivo'     => $doc->sequdocanexo,
                                                'data_inclusao'  => $doc->datacadasarquivo,
                                                'usermod'           => $doc->usermod,
                                                // 'arquivo'           => $doc->arquivo,
                                                'ativo'             => 'S'
                                            );
                }
            } 
    }
    ?>
    <html>
    <?php
        # Carrega o layout padrão #
        layout();
    ?>
    <script language="javascript" src="../janela.js" type="text/javascript"></script>
    <script language="javascript" src="../import/jquery/jquery-1.7.2.min.js" type="text/javascript"></script>
    <script language="javascript" src="../import/jquery/jquery.maskmoney.js" type="text/javascript"></script>
    <script language="javascript" src="../import/jquery/jquery.maskedinput.js" type="text/javascript"></script>​
    <script language="javascript" type="">
        function CriatableView(objJson){
                    tabelaHtml = '<table border="1" class="textonormal" bordercolor="#75ADE6">';
                    tabelaHtml += '<thead>';
                    tabelaHtml += '<tr bgcolor="#DCEDF7" style="font-weight: bold;">';
                    tabelaHtml += '<td>';
                    tabelaHtml += 'Identificador do Contratado';
                    tabelaHtml += '</td>';
                    tabelaHtml += '<td>';
                    tabelaHtml += 'Razão Social';
                    tabelaHtml += '</td>';
                    tabelaHtml += '</tr>';
                    tabelaHtml += '</thead>';
                    tabelaHtml += '<tbody>';
                    for(i in objJson){
                        if(objJson[i]){
                            if(objJson[i].remover == 'N'){
                                tabelaHtml += '<tr>';
                                tabelaHtml += '<td>';
                                tabelaHtml += '<input type="hidden" name="codFornecedorModalPesquisa[]" value="'+objJson[i].aforcrsequ+'">';
                                tabelaHtml +=  (objJson[i].aforcrccpf == '')?objJson[i].aforcrccpf:objJson[i].aforcrccgc;
                                tabelaHtml += '</td>';
                                tabelaHtml += '<td>';
                                tabelaHtml += objJson[i].nforcrrazs;
                                tabelaHtml += '</td>';
                                tabelaHtml += '</tr>';
                            }
                        }
                    }
                    tabelaHtml += '</tbody>';
                    tabelaHtml += '</table>';
                return tabelaHtml;
        }
        function Submete(Destino, id=null, idApos=null){
            document.CadContratoConsolidado.Destino.value = Destino;
            document.CadContratoConsolidado.idAditivo.value = id;
            document.CadContratoConsolidado.idApostilamento.value = idApos; 
            document.CadContratoConsolidado.submit();
        }
        $(document).ready(function() {
            if($("#addFornecedor").is(':visible')){
                $.post("postDadosManter.php",{op:"ExibeFornecedorExtra",idregistro:$("input:[name=idregistro]").val()}, function(data){
                    ObjJson = JSON.parse(data);
                    $("#shownewfornecedores").html(CriatableView(ObjJson));
			    });
            }
            $("#btnvoltar").on('click', function(){
                window.location.href = "./CadContratoConsolidadoPesquisar.php";
            });
        });
        <?php MenuAcesso(); ?>
       
    </script>
    <script language="JavaScript" src="../menu.js"></script>
    <script language="JavaScript">Init();</script>
    <link rel="stylesheet" type="text/css" href="../estilo.css?v=<?php echo time();?>">
    <style>
        #tabelaficais thead tr td{
            align-items: center;
            white-space: nowrap;
            -webkit-user-modify: read-write-plaintext-only;
        }
        #tabelaficais tfoot tr td{
            align-items: center;
            white-space: nowrap;
        }
        #tabelaficais tfoot tr.FootFiscaisDoc {
            align-items: center;
            white-space: nowrap;
            text-align : center;
            background-color: #bfdaf2;
        }
        .msg {
              text-align: center;
               font-size: larger;
             font-weight: 600;
                   color: #75ade6;
        }
        .caixaalta {
            text-transform: uppercase;
        }
    </style>
    <body background="../midia/bg.gif" marginwidth="0" marginheight="0">
    <form action="CadContratoConsolidado.php" method="post" id="FormContrato" name="CadContratoConsolidado">
            <input type="hidden" name="idregistro" id="idRegistro" value="<?php echo $idRegistro;?>">
            <input type="hidden" name="coditipodoc" value="<?php echo $dadosContratos->coditipodoc;?>">
            <input type="hidden" name="codsequsituacaodoc" value="<?php echo $dadosContratos->codsequsituacaodoc;?>">
            <input type="hidden" name="codsequfasedoc" value="<?php echo $dadosContratos->codsequfasedoc;?>">
            <input type="hidden" name="codmodeldoc" value="<?php echo $dadosContratos->codmodeldoc;?>">
            <input type="hidden" name="dataultmaatualizacao" value="<?php echo $dadosContratos->dataultmaatualizacao;?>">
            <input type="hidden" name="codisequtipodoc" value="<?php echo $dadosContratos->codisequtipodoc;?>">
            <input type="hidden" name="codisequfuncao" value="<?php echo $dadosContratos->codisequfuncao;?>">
            <input type="hidden" name="codisequckecklist" value="<?php echo $dadosContratos->codisequckecklist;?>">
            <input type="hidden" name="seqscc" value="<?php echo !empty($csolcosequ)?$csolcosequ:$dadosContratos->seqscc;?>">
            <input type="hidden" name="corglicodi" value="<?php echo !empty($corglicodi)?$corglicodi:$dadosContratos->codorgao;?>">
            <input type="hidden" name="vctrpcvlor" value="<?php echo $valororiginal; ?>">
            <input type="hidden" name="op" id="op" value="UpdateContrato">
            <br><br><br><br><br>
            <table width="100%" cellpadding="3" class="textonormal" summary="">
                <!-- Caminho -->
                <tr>
                    <td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
                    <td align="left" class="textonormal" colspan="2">
                        <font class="titulo2">|</font>
                        <a href="../index.php"><font color="#000000">Página Principal</font></a> > Contratos > Contrato Consolidado
                    </td>
                </tr>
                <!-- Fim do Caminho-->
                <!-- Corpo -->
                <tr>
                    <td width="150"></td>
                    <td class="textonormal">
                        <table  cellspacing="0" cellpadding="3" summary=""  width="100%" >
                            <tr>
                                <td class="textonormal">
                                <?php echo NavegacaoAbasConsolidadoComMedicaoEAditivo(on,off,off,count($AllMedicao),off,$AllAditivos,off,$AllApostilamento); ?>
                                    <table id="scc_material" summary="" style="border: 1px solid #75ade6; border-radius: 4px;"  width="100%" class="textonormal">
                                        <tbody border="0">
                                            <tr>
                                                <td align="left" colspan="4" > 
                                                    <!-- <table id="scc_material" summary="" bordercolor="#75ADE6"  style="border: 3px solid #75ade6; border-radius: 4px;" width="100%"> -->
                                                            <tr>
                                                                    <td colspan="3" class="titulo3 itens_material" align="center"  bgcolor="#75ADE6" valign="middle"> 
                                                                        CONTRATO ORIGINAL
                                                                    </td>
                                                            </tr>
                                                    
                                                            <tr>
                                                                <td  bgcolor="#DCEDF7">Número do Contrato/Ano </td>
                                                                <td >
                                                                        <?php echo $dadosContratos->ncontrato;?>
                                                                </td>
                                                            </tr>
                                                                <?php
                                                                    $modificadisplayScc = $numScc;
                                                                    if(empty($modificadisplayScc)){
                                                                        $modificadisplayScc = $SCC;
                                                                    }
                                                                if(empty($modificadisplayScc)){
                                                                        $display ='none';
                                                                    }?>
                                                            <tr style = "display: <?php echo $display?>">
                                                                <td  bgcolor="#DCEDF7">Solici. de Compra/Contratação-SCC * </td>
                                                                <td class="inputs">
                                                                    <span id="panelGroupSolicitacao">
                                                                        <?php echo !empty($numScc)?$numScc:$SCC;?>
                                                                    </span>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td bgcolor="#DCEDF7">Origem </td>
                                                                <td class="inputs">
                                                                    <span id="origem" class="caixaalta"> <?php echo !empty($origemScc)?$origemScc:$TipoCOmpra->etpcomnome; ?></span>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td bgcolor="#DCEDF7">Órgão Contratante Responsável </td>
                                                                <td class="inputs">
                                                                    <span id="orgao" class="caixaalta"><?php echo !empty($orgLicitante)?$orgLicitante:$dadosContratos->orgaocontratante;?></span>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td bgcolor="#DCEDF7">Objeto * </td>
                                                                <td class="inputs caixaalta">
                                                                            <?php echo !empty($objetoDesc)?$objetoDesc:$dadosContratos->objetivocontrato;?>
                                                                </td>
                                                            </tr>                                                                
                                                            <tr>
                                                                <td bgcolor="#DCEDF7">Contratado </td>
                                                                <td class="inputs">
                                                                    <table id="_gridContratadoNovo" class="textonormal">
                                                                        <tbody>
                                                                            <tr>
                                                                                <td class="labels">
                                                                                    <span id="_panelLblCpfCnpj">
                                                                                            <label for=""  class="">
                                                                                                <!-- CNPJ do Contratado : -->
                                                                                                <?php
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
                                                                                                if(!empty($fornAltAditivo[0]->cpf) || !empty($fornAltAditivo[0]->cnpj)){
                                                                                                    $cpfCNPJ =  !empty($fornAltAditivo[0]->cnpj) ? 
                                                                                                    $ObjContrato->MascarasCPFCNPJ($fornAltAditivo[0]->cnpj) : $ObjContrato->MascarasCPFCNPJ($fornAltAditivo[0]->cpf);
                                                                                                    //var_dump($fornAltAditivo[0]->cnpj);die;  
                                                                                                }
                                                                                                else
                                                                                                { 
                                                                                                    $pfCNPJ =  $CpfCnpj?$CpfCnpj:$ObjContrato->MascarasCPFCNPJ($cpfCNPJ);
                                                                                                  
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
                                                                                            </label>
                                                                                    </span>
                                                                                </td>
                                                                                <td class="inputs" colspan="3">
                                                                                    <div id="_panelInputCpfCnpj">
                                                                                        <label>
                                                                                            <?php echo !empty($CpfCnpj)?$CpfCnpj:$ObjContrato->MascarasCPFCNPJ($cpfCNPJ);?>
                                                                                        </label>
                                                                                    </div>
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td class="labels">
                                                                                    <label for="" style=";" class="">
                                                                                        Razão Social :
                                                                                    </label>
                                                                                </td>
                                                                                <td class="inputs" colspan="3">
                                                                                    <div id="_panelGroupRazao">
                                                                                        <span id="_razaoSocialfornecedor" class="caixaalta"> 
                                                                                            <?php echo !empty($razSocial)?$razSocial:$dadosContratos->razao;?>
                                                                                        </span>
                                                                                    </div>
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td class="labels">
                                                                                    <label for="" style=";" class="">
                                                                                        Logradouro :
                                                                                    </label>
                                                                                </td>
                                                                                <td class="inputs" colspan="3">
                                                                                    <span id="_logradourofornecedor" class="caixaalta">
                                                                                        <?php echo !empty($Rua)?$Rua:$dadosContratos->endereco;?>
                                                                                    </span>
                                                                                </td>
                                                                            </tr>
                                                                                <tr>
                                                                                    <td class="labels">
                                                                                        <label for="" style=";" class="">
                                                                                            Número :
                                                                                        </label>
                                                                                    </td>
                                                                                    <td class="inputs" colspan="3">
                                                                                        <span id="_numerofornecedor">
                                                                                            <?php echo !empty($numEnd)?$numEnd:$dadosContratos->numerofornecedor;?>
                                                                                        </span>
                                                                                    </td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td class="labels">
                                                                                        <label for="" style=";" class="">
                                                                                            Complemento :
                                                                                        </label>
                                                                                    </td>
                                                                                    <td class="inputs">
                                                                                        <span id="_complementoLogradourofornecedor" class="caixaalta"> 
                                                                                            <?php echo !empty($complEnd)?$complEnd:$dadosContratos->complementofornecedor;?>
                                                                                        </span>
                                                                                    </td>
                                                                                    <td class="labels">
                                                                                        <label for="" style=";" class="">
                                                                                            Bairro :
                                                                                        </label>
                                                                                    </td>
                                                                                    <td class="inputs">
                                                                                        <span id="_bairrofornecedor" class="caixaalta">
                                                                                            <?php echo !empty($Bairro)?$Bairro: $dadosContratos->bairrofornecedor;?>
                                                                                        </span>
                                                                                    </td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td class="labels">
                                                                                        <label for="" style=";" class="">
                                                                                            Cidade :
                                                                                        </label>
                                                                                    </td>
                                                                                    <td class="inputs">
                                                                                        <span id="_cidadefornecedor" class="caixaalta">
                                                                                            <?php echo !empty($Cidade)?$Cidade:$dadosContratos->cidadefornecedor;?>
                                                                                        </span>
                                                                                    </td>
                                                                                    <td class="labels">
                                                                                        <label for="" style=";" class="">
                                                                                                UF :
                                                                                        </label>
                                                                                    </td>
                                                                                    <td class="inputs">
                                                                                        <span id="_estadofornecedor" class="caixaalta">
                                                                                            <?php echo !empty($UF)?$UF:$dadosContratos->uffornecedor;?>
                                                                                        </span>
                                                                                    </td>
                                                                                </tr>
                                                                        </tbody>
                                                                    </table>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td bgcolor="#DCEDF7">Contínuo * </td>
                                                                <td class="inputs">
                                                                    <table id="fieldContinuo" class="textonormal">
                                                                        <tbody>
                                                                            <tr>
                                                                                 <td>
                                                                                    <?php echo $dadosContratos->econtinuo;?> 
                                                                                </td>
                                                                            </tr>
                                                                        </tbody>
                                                                    </table>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td bgcolor="#DCEDF7">Obra * </td>
                                                                <td class="inputs">
                                                                    <table id="obra" class="textonormal">
                                                                        <tbody>
                                                                            <tr>
                                                                                <td class="caixaalta">
                                                                                         <?php echo !empty($dadosContratos->obra)?$dadosContratos->obra:"";?> 
                                                                                </td>
                                                                            </tr>
                                                                        </tbody>
                                                                    </table>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td bgcolor="#DCEDF7">Modo de Fornecimento * </td>
                                                                <td class="inputs">
                                                                        <?php echo $dadosContratos->regexecoumodfornec; ?>
                                                                </td>
                                                            <tr> 
                                                                <td bgcolor="#DCEDF7">Opção de Execução do Contrato * </td>
                                                                <td class="inputs">
                                                                    <?php echo $dadosContratos->opexeccontrato == "D"?'DIAS':'MESES'; ?>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td bgcolor="#DCEDF7">Prazo de Execução do Contrato * </td>
                                                                <td class="inputs">
                                                                    <?php echo $dadosContratos->prazoexec;?>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td bgcolor="#DCEDF7">Categoria do processo </td>
                                                                <td class="inputs">
                                                                    <?php echo !empty($categoriaProcesso->epnccpnome) ? $categoriaProcesso->epnccpnome : ' - ';?>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td bgcolor="#DCEDF7">Data de Publicação no DOM </td>
                                                                <td class="inputs">
                                                                    <?php echo DataBarra($dadosContratos->datapublic);?>
                                                                </td>
                                                            </tr>
                                                            <tr id="linhaTabelaOS">
                                                                <td id="colunaVaziaOS" width="35%" bgcolor="#bfdaf2"></td>
                                                                <th id="colunaDataInicioOS"  width="32%" class="colorBlue" bgcolor="#bfdaf2">
                                                                        <span id="labelDataInicioOrdemServico">DATA DE INÍCIO</span>
                                                                </th>
                                                                <th id="colunaDataTerminoOS"  width="32%"  class="colorBlue" bgcolor="#bfdaf2">
                                                                        <span id="labelDataTerminoOrdemServico">DATA DE TÉRMINO</span>
                                                                </th>
                                                            </tr>
                                                            <tr>
                                                                <td bgcolor="#DCEDF7">Vigência * </td>
                                                                <td class="inputs">
                                                                    <span id="vigenciaGroup">
                                                                        <?php echo DataBarra($dadosContratos->datainivige);?>
                                                                    </span>
                                                                </td>
                                                                <td class="inputs">
                                                                    <span id="vigenciaGroup">
                                                                       <?php echo DataBarra($dadosContratos->datafimvige);?>
                                                                    </span>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td bgcolor="#DCEDF7">Execução </td>
                                                                <td class="inputs">
                                                                    <span id="execucaoGroup">
                                                                        <?php echo DataBarra($dadosContratos->datainiexec);?>
                                                                    </span>
                                                                </td>   
                                                                <td class="inputs">
                                                                    <span id="execucaoGroup">
                                                                       <?php
                                                                        
                                                                        echo DataBarra($dadosContratos->datafimexec);
                                                                       
                                                                        $DATAEXECUCAOFIM = DataBarra($dadosContratos->datafimexec);$DATAEXECUCAOFIM = explode('/', $DATAEXECUCAOFIM);
                                                                            $day   = $DATAEXECUCAOFIM[0]-1;
                                                                            $month = $DATAEXECUCAOFIM[1];
                                                                            $year  = $DATAEXECUCAOFIM[2];
                                                                            $DATAEXECUCAOFIM = $day. "/". $month . "/". $year;
                            
                                                                        //echo $DATAEXECUCAOFIM;
                                                                        
                                                                        ?>
                                                                    </span>
                                                                </td>   
                                                            </tr>
                                                            <tr>
                                                                <td bgcolor="#DCEDF7"> Valor Original</td>
                                                                <td><?php echo number_format($valororiginal,4,',','.');?></td>
                                                            </tr>
                                                            <tr >
                                                                <td bgcolor="#DCEDF7">Garantia </td>
                                                                <td class="inputs">
                                                                        <?php
                                                                        
                                                                        foreach($dadosGarantia as $garantia){ 
                                                                                
                                                                            if($garantia->codgarantia == $dadosContratos->codisequtipogarantia){
    
                                                                                $GARANTIA = $garantia->descricaogarantia;
                                                                                echo $GARANTIA;
                                                                                }elseif(empty($dadosContratos->codisequtipogarantia)){
                                                                                    $SEMGARANTIA = 'SEM GARANTIA';
                                                                                    echo $SEMGARANTIA;
                                                                                    break;
                                                                                }
                                                                            }
                                                                         
                                                                         ?>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td bgcolor="#DCEDF7"> Número de Parcelas</td>
                                                                <td><?php echo $dadosContratos->numerodeparcelas;?></td>
                                                            </tr>
                                                            <tr>
                                                                <td bgcolor="#DCEDF7"> Valor das Parcelas</td>
                                                                <td><?php echo number_format($dadosContratos->valordaparcela,4,',','.');?></td>
                                                            </tr>
                                                            <tr>
                                                                <th colspan="3" bgcolor="#bfdaf2">
                                                                    <span id="labelDataInicioOrdemServico">REPRESENTANTE LEGAL</span>
                                                                </th>
                                                            </tr>
                                                            <tr>
                                                                <td bgcolor="#DCEDF7">Nome * </td>
                                                                <td  class="caixaalta">
                                                                            <?php echo $dadosContratos->nomerepresenlegal;?>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td bgcolor="#DCEDF7">CPF *  </td>
                                                                <td >
                                                                            <?php echo $ObjContrato->MascarasCPFCNPJ($dadosContratos->cpfrepresenlegal);?>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td bgcolor="#DCEDF7">Cargo  </td>
                                                                <td  class="caixaalta">
                                                                            <?php echo $dadosContratos->cargorepresenlegal;?>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td bgcolor="#DCEDF7">Identidade  </td>
                                                                <td >
                                                                            <?php echo $dadosContratos->identidaderepreslegal;?>
                                                            </td>
                                                            </tr>
                                                            <tr>
                                                                <td bgcolor="#DCEDF7">Órgão Emissor </td>
                                                                <td class="caixaalta">
                                                                           <?php echo $dadosContratos->orgaoexpedrepreselegal;?>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td bgcolor="#DCEDF7">UF da Identidade </td>
                                                                <td class="caixaalta" >
                                                                           <?php echo $dadosContratos->ufrgrepresenlegal;?>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td bgcolor="#DCEDF7">Cidade de Domicílio </td>
                                                                <td class="caixaalta">
                                                                           <?php echo $dadosContratos->cidadedomrepresenlegal;?>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td bgcolor="#DCEDF7">Estado de Domicílio </td>
                                                                <td class="caixaalta">
                                                                           <?php echo $dadosContratos->estdomicrepresenlegal;?>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td bgcolor="#DCEDF7">Nacionalidade  </td>
                                                                <td class="caixaalta">
                                                                     <?php echo $dadosContratos->naciorepresenlegal;?>
                                                                 </td>
                                                            </tr>
                                                            <tr>
                                                                <td bgcolor="#DCEDF7">Estado Civil </td>
                                                                    <td class="caixaalta">
                                                                             <?php 
                                                                                    switch ($dadosContratos->estacivilrepresenlegal) {
                                                                                        case 'S':
                                                                                            echo "Solteiro";
                                                                                        break;
                                                                                        case 'C':
                                                                                            echo "Casado";
                                                                                        break;
                                                                                        case 'D':
                                                                                            echo "Divorciado";
                                                                                        break;
                                                                                        case 'V':
                                                                                            echo "Viúvo";
                                                                                        break;
                                                                                        case 'O':
                                                                                            echo "Outros";
                                                                                        break;
                                                                                    } 
                                                                                    ?>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td bgcolor="#DCEDF7">Profissão </td> 
                                                                <td class="caixaalta">
                                                                        <?php echo $dadosContratos->profirepresenlegal;?>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td bgcolor="#DCEDF7">E-mail </td>
                                                                <td >
                                                                           <?php echo $dadosContratos->emailrepresenlegal;?>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td bgcolor="#DCEDF7">Telefone(s) </td>
                                                                <td >
                                                                            <?php echo $dadosContratos->telrepresenlegal;?>
                                                                </td>
                                                            </tr>
                                                            <tr  bgcolor="#bfdaf2">
                                                                <th colspan="3" scope="colgroup">GESTOR</th>
                                                            </tr>
                                                            <tr>
                                                                    <td bgcolor="#DCEDF7">Nome </td>
                                                                    <td class="caixaalta">
                                                                            <?php echo $dadosContratos->nomegestor;?>
                                                                    </td>
                                                            </tr>
                                                            <tr>
                                                                    <td bgcolor="#DCEDF7">Matrícula </td>
                                                                    <td >
                                                                            <?php echo $dadosContratos->matgestor;?>
                                                                    </td>
                                                            </tr>
                                                            
                                                            <tr>
                                                                    <td bgcolor="#DCEDF7">CPF </td>
                                                                    <td >
                                                                               <?php echo $ObjContrato->MascarasCPFCNPJ($dadosContratos->cpfgestor);?>
                                                                    </td>
                                                            </tr>
                                                            
                                                            <tr>
                                                                    <td bgcolor="#DCEDF7">E-mail </td>
                                                                    <td >
                                                                               <?php echo $dadosContratos->emailgestor;?>
                                                                    </td>
                                                            </tr>
                                                            
                                                            <tr>
                                                                <td bgcolor="#DCEDF7">Telefone(s) </td>
                                                                <td >
                                                                    <?php echo $dadosContratos->fonegestor;?>
                                                                </td>
                                                            </tr>
                                                            <tr  bgcolor="#bfdaf2">
                                                                <th colspan="3" <?php echo $bloqueiacampo?'disabled="disabled"':'';?> scope="colgroup">FISCAL(IS)*</th>
                                                            </tr>
                                                            <tr>
                                                            <!-- Eliakim Ramos 05032019 -->
                                                                <td colspan="3" >
                                                                            <table style="width:100%; border:1px solid #bfdaf2;"  id="tabelaficais" class="textonormal">
                                                                                    <tr bgcolor="#DCEDF7" style="font-weight: bold; text-transform: uppercase;">
                                                                                        <td colspan="1">Tipo Fiscal</td>
                                                                                        <td colspan="1">Nome</td>
                                                                                        <td colspan="1">Matrícula</td>
                                                                                        <td colspan="1">CPF</td>
                                                                                        <td colspan="1">Ent. Compet.</td>
                                                                                        <td colspan="1"> Registro ou INSC.</td>
                                                                                        <td colspan="1">E-MAIL</td>
                                                                                        <td colspan="1">Telefone</td>
                                                                                    </tr>
                                                                                <tbody id="mostrartbfiscais">
                                                                                <?php 
                                                                                    $auxAnt = array();
                                                                                    if(!empty($DadosDocFiscaisFiscal)){
                                                                                        foreach($DadosDocFiscaisFiscal as $fiscal){ $situacao = $fiscal->docsituacao; 
                                                                                            if( strtoupper($fiscal->remover) == "N"){
                                                                                                    if(!in_array($fiscal->fiscalcpf,$auxAnt)){
                                                                                ?>
                                                                                            <tr>
                                                                                                <td><?php echo $fiscal->tipofiscal;?></td>
                                                                                                <td><?php echo $fiscal->fiscalnome;?></td>
                                                                                                <td><?php echo $fiscal->fiscalmatricula;?></td>
                                                                                                <td><?php echo $ObjContrato->MascarasCPFCNPJ($fiscal->fiscalcpf);?></td>
                                                                                                <td><?php echo $fiscal->ent;?></td>
                                                                                                <td><?php echo $fiscal->registro;?></td>
                                                                                                <td><?php echo $fiscal->fiscalemail;?></td>
                                                                                                <!-- <td><?php echo $fiscal->fiscaltipo;?></td> -->
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
                                                                                <tfoot>
                                                                                    <tr>
                                                                                        <td bgcolor="#bfdaf2" colspan="3" width="35%">Situação do contrato</td>
                                                                                        <td bgcolor="#FFFFFF" width="10%"><?php echo $situacaoContrato->esitdcdesc;?></td>
                                                                                    </tr>
                                                                                </tfoot>
                                                                            </table>
                                                                </td>
                                                            </tr>
                                                            <tr bgcolor="#bfdaf2">
                                                                <th colspan="3" scope="colgroup">ANEXAÇÃO DE  DOCUMENTO(S)</th>
                                                            </tr>
                                                            <tr>
                                                                <td colspan="3">
                                                                            <table id="tabelaficais" bgcolor="#bfdaf2" class="textonormal" width="100%">
                                                                                <tbody >
                                                                                        <tr class="FootFiscaisDoc" bgcolor="#DCEDF7" style="font-weight: bold;">
                                                                                                <td colspan="4">ARQUIVO</td>
                                                                                                <td colspan="4">DATA DA INCLUSÃO</td>
                                                                                            </tr>
                                                                                            <?php 
                                                                                            if(!empty($DadosDocAnexo)){
                                                                                                $k=0;
                                                                                                foreach($DadosDocAnexo as $key => $anexo){ 
                                                                                                    $_SESSION['arquivo_download'][$k] = $anexo->arquivo;
                                                                                                    ?>
                                                                                            <tr bgcolor="#ffffff">
                                                                                                <td colspan="4"> <a class="" href="downloadDocContratoConsolidado.php?arquivo=<?php echo $k;?>&nome=<?php echo $anexo->nomearquivo;?>" id="documento<?php echo $k;?>" rel="<?php echo $anexo->nomearquivo;?>"><?php echo $anexo->nomearquivo;?></a>
                                                                                                </td>
                                                                                                <td colspan="4"> <?php echo $anexo->datacadasarquivo;?></td>
                                                                                            </tr>
                                                                                            <?php  $k++;}
                                                                                                    }else{
                                                                                                        echo ' <tr bgcolor="#ffffff">';
                                                                                                        echo ' <td colspan="8" bgcolor="#ffffff">Nenhum documento informado</td>';
                                                                                                        echo ' </tr>';
                                                                                                    }
                                                                                            ?>
                                                                                </tbody>
                                                                            </table>
                                                                </td>
                                                            </tr>
                                                            <tr bgcolor="#bfdaf2">
                                                                    <th colspan="3" scope="colgroup">
                                                                                    <span>ITENS DO CONTRATO</span>
                                                                    </th>

                                                            </tr>
                                                            <tr>
                                                                    <td colspan="3">
                                                                            <table id="scc_material" summary="" bgcolor="#bfdaf2" border="1" bordercolor="#75ADE6" width="100%" >
                                                                                <tr class="head_principal">
                                                                                    <td class="textoabason" align="center" bgcolor="#DCEDF7" width="7%"><br /> ORD </td>
                                                                                    <td class="textoabason" align="center" bgcolor="#DCEDF7" width="15%"><img src="../midia/linha.gif" alt="" border="0" height="1px"/> <br /> DESC ITEM </td>
                                                                                    <td class="textoabason" align="center" bgcolor="#DCEDF7" width="25%"><img src="../midia/linha.gif" alt="" border="0" height="1px"/> <br /> COD REDUZIDO </td>
                                                                                    <td class="textoabason" align="center" bgcolor="#DCEDF7" width="5%"><img src="../midia/linha.gif" alt="" border="0" height="1px"/> <br /> UND </td>
                                                                                    <td class="textoabason" align="center" bgcolor="#DCEDF7" width="23%"><img src="../midia/linha.gif" alt="" border="0" height="1px"/> <br /> QTD. </td>
                                                                                    <td class="textoabason" align="center" bgcolor="#DCEDF7" width="10%"><img src="../midia/linha.gif" alt="" border="0" height="1px"/> <br /> VALOR UNITARIO </td>
                                                                                    <td class="textoabason" align="center" bgcolor="#DCEDF7" width="15%"><img src="../midia/linha.gif" alt="" border="0" height="1px"/> <br /> VALOR TOTAL </td>
                                                                                    <td class="textoabason" align="center" bgcolor="#DCEDF7" width="15%"><img src="../midia/linha.gif" alt="" border="0" height="1px"/> <br /> TIPO </td>
                                                                                </tr>
                                                                                <?php 
                                                                                        $valorFullTotal = 0;
                                                                                        if(!empty($dadosItensContrato)) {   
                                                                                            foreach($dadosItensContrato as $itens){
                                                                                                            $codeMaterial = !empty($itens->codreduzidomat)?$itens->codreduzidomat:$itens->codreduzidoserv;
                                                                                                            $tipoGrupo = !empty($itens->codreduzidomat)?'M':"S";
                                                                                                ?>
                                                                                        <tr>
                                                                                            <td class="textonormal" align="center" style="text-align: center">
                                                                                                <?php echo $itens->ord;?>
                                                                                            </td>
                                                                                            <td class="textonormal">
                                                                                                <a href="javascript:AbreJanela('../estoques/CadItemDetalhe.php?Material=<?php echo $codeMaterial;?>&amp;TipoGrupo=<?php echo  $tipoGrupo;?>&amp;ProgramaOrigem=',800,470);">
                                                                                                            <?php 
                                                                                                                if(!empty($itens->codreduzidomat)){
                                                                                                                    $descricaoMaterial = $ObjContrato->GetDescricaoMaterial($itens->codreduzidomat);
                                                                                                                    echo $descricaoMaterial[0]->ematepdesc;
                                                                                                                }
                                                                                                                if(!empty($itens->codreduzidoserv)){
                                                                                                                    $descricaoServico = $ObjContrato->GetDescricaoServicos($itens->codreduzidoserv);
                                                                                                                    echo $descricaoServico[0]->eservpdesc;
                                                                                                                }

                                                                                                            ?>
                                                                                                    </a>
                                                                                            </td>

                                                                                            <td class="textonormal" align="center">
                                                                                            <?php echo !empty($itens->codreduzidoserv)?$itens->codreduzidoserv:$itens->codreduzidomat;?>
                                                                                            </td>

                                                                                            <td class="textonormal" align="center"  style="cursor: help">
                                                                                            <?php echo !empty($itens->codreduzidoserv)?"":'und';?>
                                                                                            </td>
                                                                                            <td class="textonormal" align="center">
                                                                                                <?php echo number_format($itens->qtd,4,',','.');?>
                                                                                            </td>
                                                                                            <td class="textonormal" align="center">
                                                                                                <?php echo number_format($itens->valorunitario,4,',','.');?>

                                                                                            </td>
                                                                                            <!--  Coluna 7 = Situação-->
                                                                                            <td class="textonormal" style="text-align: center !important;">
                                                                                                <?php 
                                                                                                        $valor = floatval($itens->qtd) * floatval($itens->valorunitario);
                                                                                                        $valorFullTotal += $valor;
                                                                                                        echo number_format($valor,4,',','.');
                                                                                                ?>
                                                                                            </td>
                                                                                            <td class="textonormal" style="text-align: center !important;">
                                                                                            <?php echo !empty($itens->codreduzidoserv)?"Serviço":"Material";?>
                                                                                            </td>
                                                                                        </tr>
                                                                                    <?php       }
                                                                                        }else{
                                                                                    ?>
                                                                                        <tr>
                                                                                            <td class="textonormal itens_material" colspan="7" style="color: red">
                                                                                                    Não há item associado ao contrato
                                                                                            </td>
                                                                                        </tr>
                                                                                        <?php } ?>
                                                                            </table>
                                                                    </td>
                                                            </tr>
                                                        </tbody>
                                                        <tr>
                                                            <td colspan="4" align="right">
                                                                <input type="button" value="Voltar" class="botao" id="btnvoltar">
                                                                <input type="hidden" name="Botao" value="">
                                                                <input type="hidden" name="Origem" value="A">
                                                                <input type="hidden" name="Destino">
                                                                <input type="hidden" name="idAditivo"> 
                                                                <input type="hidden" name="idApostilamento"> 
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
        <div class="modal textonormal" id="modal"> 
            <div class="modal-content" style="min-height: 50%;width: 83%;">
            
            </div>
        </div> 
        <!-- Fim Modal -->
        <br>
    </body>
</html>
<?php
    exit;
}

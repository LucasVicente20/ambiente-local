<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: AbaContratoConsolidado.php
# Autor:    Eliakim Ramos | João Madson
# Data:     10/12/2019
# Objetivo: Programa de incluir contrato
#-------------------------------------------------------------------------
# Autor:    Madson Felix
# Data:     07/05/2021
# CR #246939
# -------------------------------------------------------------------------
# Autor:    Osmar Celestino
# Data:     25/05/2021
# CR #248619
# -------------------------------------------------------------------------
# Exibe Aba Membro de Comissão - Formulário A #
# -------------------------------------------------------------------------
# Portal da DGCO
# Programa: abaContratoConsolidado.html
# Autor:    Marcello Albuquerque
# Data:     19/07/2021
# Objetivo: Ajuste no download dos arquivos do aditivo CR #249865
#--------------------------------------------------------------------------->
# Autor:    Osmar Celestino | Lucas Vicente
# Data:     07/06/2022
# CR #244295
# -------------------------------------------------------------------------
# Autor: Lucas Vicente
# Data:  15/02/2023
# Objetivo: CR #275671
# -------------------------------------------------------------------------

require_once("ClassContratosFuncoesGerais.php");

function ExibeAbaContratoConsolidado(){
    $ObjContratoManter = new ContratoManter();
    $ObjContrato = new ContratoManter();
    $ObjContratoInc = new Contrato();
    $objMedicao = new ClassMedicao();
    $objConstratoConsolidado = new ContratoConsolidado();
    $objContratoFuncGeral = new ContratosFuncoesGerais();
    $dadosContratos = (object) array();
    $dadosGarantia = $ObjContrato->GetListaGarantiaDocumento();
    $idRegistro    = '';

    if(isset($_POST['submit'])){
         session_start();
         header('Location: CadContratoConsolidado.php');
         }
         
         
    if ($_SERVER['REQUEST_METHOD'] == "POST" || $_SERVER['REQUEST_METHOD'] == "GET") {
       if(!empty($_REQUEST['idregistro'])){
            $_SESSION['idregistro'] = $_REQUEST['idregistro'];
       }
        $idRegistro = !empty($_REQUEST['idregistro'])? $_REQUEST['idregistro']:$_SESSION['idregistro'];
        
        $AllAditivos = $objConstratoConsolidado->getAditivosByContratoConsolidado($idRegistro);
        $AllMedicao  = $objConstratoConsolidado->getMedicoesContrato($idRegistro);
        $AllApostilamento  = $objConstratoConsolidado->getApostilamentosContrato($idRegistro);
        $dadosItensContrato = $ObjContrato->GetItensContrato($idRegistro);
        $descricaoMaterial = "";
        $descricaoServico =""; 
        $dadosContratos     = $ObjContrato->GetDadosContratoSelecionado($idRegistro);
        $dadosGestorConsolidado = $ObjContrato->GetGestorContratoConsolidado($idRegistro);
        $dadosFiscaisConsolidado = $ObjContrato->getFicaisContratoConsolidado($idRegistro);
        $Aditivos = $ObjContrato->GetAditivos($idRegistro);
        $dataAditivos = $ObjContrato->PegaAlteracaoDataContratoPorAditivo($idRegistro);
        $Apostilamento = $ObjContrato->GetApostilamento($idRegistro);
        $Medicao = $ObjContrato->GetMedicao($idRegistro);
        $vtAditivo = $ObjContrato->GetValorTotalAdtivo($idRegistro);

        $gestorAltApostilamento = $ObjContrato->GetgestorAlteradoApostilamento($idRegistro);
        
        $fiscaisAlterado = $ObjContrato->getDocumentosFiscaisEFiscalAlterado($idRegistro);

        $repAltAditivo = $ObjContrato->GetRepresentateAlteradoAdtivo($idRegistro); //|MADSON| - verifica e busca caso tenha alteração do representante
        $fornAltAditivo = $ObjContrato->GetFornecedorAlteradoAdtivo($idRegistro); //|MADSON| - verifica e busca caso tenha alteração do fornecedor
        $vtApost = $ObjContrato->GetValorTotalApostilamento($idRegistro);
        $valorTotalMedicao = $objMedicao->getValorTotalMedicao($idRegistro);
        $valororiginal = floatval($dadosContratos->valororiginal);
        $SaldoExec = 0;
        $vtAd = floatval( $vtAditivo[0]->vtaditivo);
        $vtAp = floatval($vtApost[0]->vtapost);
        $valorTotal = $dadosContratos->valortotalcontrato;
        $vtOgAdAp = ((floatval($valororiginal)+$vtAd)+$vtAp);
        $SaldoExec = $objMedicao->SaldoAExecutar($idRegistro,$vtOgAdAp);
        $valorGlobal = !empty($SaldoExec)?$SaldoExec:$objMedicao->SaldoAExecutar($idRegistro,($valororiginal+floatval( $vtAditivo[0]->vtaditivo)+floatval($vtApost[0]->vtapost)));
        $situacaoContrato = $ObjContrato->GetSituacaoContrato($dadosContratos->codsequfasedoc,$dadosContratos->codsequsituacaodoc);  //colocar quando achar uma situação que se encaxe 
        $temAditivo = count($Aditivos) > 0? true:false;
        $temApostilamento = count($Apostilamento) > 0? true:false;
        $temMedicao = count($Medicao) > 0? true:false;
        $bloqueiacampo = false;
        $_SESSION['doc_fiscal'] = $dadosContratos->seqdocumento;
        $nomeBotao = "Salvar";
        $nomeBotaoEncerrar = "Encerrar";
        $funcaoEncerrar = 'encerraContrato()';
        $exibeBotao = '';
        $exibeBotaoEncerramento = '';
        $exibeLupa = "";
        $funcaoExcluir = "excluirContrato()";
        $funcaoCancelar = "cancelarContrato()";
        if($temAditivo || $temApostilamento || $temMedicao){
            $bloqueiacampo = true;
            $nomeBotao = "Salvar Anexo";
            $exibeLupa = 'style="display:none"';
            $funcaoExcluir = "avisoexclusao('Não é possível excluir o contrato, pois existe aditivo, apostilamento ou medição cadastrada.')";
        }
        if( $situacaoContrato->esitdcdesc=='CONCLUSO' ||  $situacaoContrato->esitdcdesc=='CANCELADO'){
            $exibeBotao = 'style="display:none"';
            $exibeLupa = 'style="display:none"';
            $bloqueiacampo = true;
            $funcaoCancelar = "avisoexclusao('Não é possível cancelar o contrato, por que ele ja foi concluído.')";
        }
        if( $situacaoContrato->esitdcdesc=='CANCELADO'){
            $exibeBotaoEncerramento = 'style="display:none"';
        }
        if( $situacaoContrato->esitdcdesc=='CONCLUSO'){
                $nomeBotaoEncerrar = "Desfazer Encerramento";
                $funcaoEncerrar = 'desfazEncerramentoContrato()';
        }
        if( $situacaoContrato->esitdcdesc!='CADASTRADO'  ){
            $funcaoExcluir = "avisoexclusao('Não é possivel excluir, o contrato não esta mais na fase de cadastro')";
        }
        $_SESSION['Botao'] = $_REQUEST['Botao'];
        $SCC            = "";
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
            $dadosAnexoAux = array();
            $dadosAposAnex = array();
            $dadosMedicaoAnex = array();
            $DadosDocAnexoAditivo = array();
            $apsAux = array();
            $medAux = array();
            $auxDados = array();
                $DadosDocAnexo= $ObjContrato->GetDocumentosAnexos($dadosContratos->seqdocumento);
                //var_dump($dadosContratos->seqdocumento);
                foreach($DadosDocAnexo as $doc){
                    if(!empty($doc->nomearquivo)){
                        $dadosAnexoAux[]  = (object) array(
                                                    'nome_arquivo'       =>$doc->nomearquivo,
                                                    'arquivo'           => $doc->arquivo,
                                                    'sequdoc'     => $doc->sequdocumento,
                                                    'sequarquivo'     => $doc->sequdocanexo,
                                                    'data_inclusao'  => $doc->datacadasarquivo,
                                                    'usermod'           => $doc->usermod,
                                                    "origem"            =>"CONTRATO",
                                                    'ativo'             => 'S'
                                                );
                    }
                }
                $DadosDocAnexo= $ObjContrato->GetDocumentosAnexosAdtivo($dadosContratos->seqdocumento);
                foreach($DadosDocAnexo as $doc){
                    if(!empty($doc->nomearquivo)){
                        $DadosDocAnexoAditivo[]  = (object) array(
                                                'nome_arquivo'       =>$doc->nomearquivo,
                                                'arquivo'           => $doc->arquivo,
                                                'sequdoc'     => $doc->sequdocumento,
                                                'sequarquivo'     => $doc->sequdocanexo,
                                                'data_inclusao'  => $doc->datacadasarquivo,
                                                'usermod'           => $doc->usermod,
                                                "origem"            => "ADTIVO",
                                                'ativo'             => 'S'
                                            );
                                            $qtdKeyArray++; 
                    }                    
                }

               
                $DadosDocAnexo= $ObjContrato->GetDocumentosAnexosApostilamento($dadosContratos->seqdocumento);
                foreach($DadosDocAnexo as $doc){
                    if(!empty($doc->nomearquivo)){
                        $dadosAposAnex[]  = (object) array(
                                                'nome_arquivo'       =>$doc->nomearquivo,
                                                'arquivo'           => $doc->arquivo,
                                                'sequdoc'     => $doc->sequdocumento,
                                                'sequarquivo'     => $doc->sequdocanexo,
                                                'data_inclusao'  => $doc->datacadasarquivo,
                                                'usermod'           => $doc->usermod,
                                                "origem"            => "APOSTILAMENTO",
                                                'ativo'             => 'S'
                                            );
                    }

                }
                $DadosDocAnexo= $ObjContrato->GetDocumentosAnexosMedicao($dadosContratos->seqdocumento);
                foreach($DadosDocAnexo as $doc){
                    if(!empty($doc->nomearquivo)){
                        $dadosMedicaoAnex[]  = (object) array(
                                                'nome_arquivo'       =>$doc->nomearquivo,
                                                'arquivo'           => $doc->arquivo,
                                                'sequdoc'     => $doc->sequdocumento,
                                                'sequarquivo'     => $doc->sequdocanexo,
                                                'data_inclusao'  => $doc->datacadasarquivo,
                                                'usermod'           => $doc->usermod,
                                                "origem"            => "MEDICAO",
                                                'ativo'             => 'S'
                                            );
                    }

                }
                
                $apsAux = array_merge($dadosAnexoAux,$DadosDocAnexoAditivo);
                $medAux =  array_merge($apsAux,$dadosAposAnex);
                $DadosDocAnexo = (object) array_merge($medAux,$dadosMedicaoAnex); //eliakim ramos 14082020
               // $DadosDocAnexo1 = array_merge($DadosDocAnexoAditivo);
                
               // $DadosDocAnexo2 = array_merge($dadosAposAnex); //eliakim ramos 14082020
               // $DadosDocAnexo3 = (object) array_merge($DadosDocAnexo2, $DadosDocAnexo1, $dadosAnexoAux);
    }
if(empty($_REQUEST['internet']) && empty($_GET['internet'])){
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
       
       function Submete(Destino, id=null, idApos=null){
            document.CadContratoConsolidado.Destino.value = Destino;
            document.CadContratoConsolidado.idAditivo.value = id;
            document.CadContratoConsolidado.idApostilamento.value = idApos;
            document.CadContratoConsolidado.submit();
        }
        function CriatableView(objJson){
                    tabelaHtml = '<table border="1"  class="textonormal"  bordercolor="#75ADE6">';
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
                        if(objJson[i].nforcrrazs){
                            if(objJson[i].remover == 'N'){
                                tabelaHtml += '<tr>';
                                tabelaHtml += '<td>';
                                tabelaHtml += '<input type="hidden" name="codFornecedorModalPesquisa[]" value="'+objJson[i].aforcrsequ+'">';
                                tabelaHtml +=  (objJson[i].aforcrccpf)?objJson[i].aforcrccpf:objJson[i].aforcrccgc;
                                tabelaHtml += '</td>';
                                tabelaHtml += '<td>';
                                tabelaHtml += objJson[i].nforcrrazs;
                                tabelaHtml += '</td>';
                                tabelaHtml += '</tr>';
                            }
                        }else{
                                tabelaHtml += '<tr>';
                                tabelaHtml += '<td colspan="2">';
                                tabelaHtml += 'Não registro deste fornecedor';
                                tabelaHtml += '</td>';
                                tabelaHtml += '</tr>';
                        }
                    }
                    tabelaHtml += '</tbody>';
                    tabelaHtml += '</table>';
                return tabelaHtml;
        }
        function clean_hex(input) {
            input = input.toUpperCase();
            input = input.replace(/[^A-Fa-f0-9]/g, "");

            return input;
        }
        function Convert(id, nomePdf) {
            var binValue    = $('#'+id).val();
            var filename    = nomePdf;
            var cleaned_hex = clean_hex(binValue);

            var ia = new Array();


            for (var i=0; i<cleaned_hex.length/2; i++) {
                var h = cleaned_hex.substr(i*2, 2);
                ia[i] = parseInt(h,16);
            }

            var byteArray = new Uint8Array(ia);

            // create a download anchor tag
            var downloadLink      = document.createElement('a');
            downloadLink.target   = '_blank';
            downloadLink.download = nomePdf;

            // convert downloaded data to a Blob
            var blob = new Blob([byteArray], { type: 'application/pdf' });

            // create an object URL from the Blob
            var URL = window.URL || window.webkitURL;
            var downloadUrl = URL.createObjectURL(blob);

            // set object URL as the anchor's href
            downloadLink.href = downloadUrl;

            // append the anchor to document body
            document.body.appendChild(downloadLink);

            // fire a click event on the anchor
            downloadLink.click();

            // cleanup: remove element and revoke object URL
            document.body.removeChild(downloadLink);
            URL.revokeObjectURL(downloadUrl);
        }

        $(document).ready(function() {
            $('#numcontrato').mask('9999.9999/9999');
            $('#cpfrepresenlegal').mask('999.999.999-99');
            $('#cnpj').mask('99.999.999/9999-99');
            $('#cpf').mask('999.999.999-99');
            $('#cpffiscal').mask('999.999.999-99');
            if($("#addFornecedor").is(':visible')){
                $.post("postDadosManter.php",{op:"ExibeFornecedorExtra",idregistro:$("input:[name=idregistro]").val()}, function(data){
                    ObjJson = JSON.parse(data);
                    $("#shownewfornecedores").html(CriatableView(ObjJson));
			    });
            }
            $("#cpfrepresenlegal").on("blur",function(){
                if(!TestaCPF($("#cpfrepresenlegal").val())){
                    avisoexclusao("Informe: Um CPF válido para o representante.");
                }
            });
            $("#cpfgestor").on("blur",function(){
                if(!TestaCPF($("#cpfgestor").val())){
                    avisoexclusao("Informe: Um CPF válido para o gestor.");
                }
            });
            $("#cnpj").live("focus",function(){
                $('#cnpj').mask('99.999.999/9999-99');
            });
            $("#cpffiscal").live("focus",function(){
                $('#cpffiscal').mask('999.999.999-99');
            });
            $("#telfiscal").live("blur",function(){
                var phone = $(this).val();
                if(phone.length == 14 || phone.length == 11 ){
                    $('#telfiscal').mask('(99)99999-9999');
                }else if(phone.length == 13 || phone.length == 10 ){
                    console.log('aquie');
                    $('#telfiscal').mask('(99)9999-9999');
                }else{
                    $(this).val('88987998');
                }
            });
            $("#radio-tipofiscal-interno").live('click', function(){
                $(".mostramatricula").show();
            });
            $("#radio-tipofiscal-externo").live('click', function(){
                $(".mostramatricula").hide();
            });
            $("#cpf").live("focus",function(){
                $('#cpf').mask('999.999.999-99');
            });
            if($("#obra0").prop("checked")){
                var selectHtml = '<option  value=""></option>';
                     selectHtml += '<option  <?php echo $dadosContratos->regexecoumodfornec == "PRECO GLOBAL"?'selected="selected"':''; ?> value="PRECO GLOBAL">EMPREITADA POR PREÇO GLOBAL</option>';
                     selectHtml += '<option <?php echo $dadosContratos->regexecoumodfornec == "EMPREITADA POR PRECO UNITARIO"?'selected="selected"':''; ?> value="EMPREITADA POR PRECO UNITARIO">EMPREITADA POR PREÇO UNITÁRIO</option>';
                     selectHtml += '<option <?php echo $dadosContratos->regexecoumodfornec == "TAREFA"?'selected="selected"':''; ?> value="TAREFA">TAREFA</option>';
                     selectHtml += '<option <?php echo $dadosContratos->regexecoumodfornec == "EMPREITADA INTEGRAL"?'selected="selected"':''; ?> value="EMPREITADA INTEGRAL">EMPREITADA INTEGRAL</option>';

                 $("#cmb_regimeExecucaoModoFornecimento1").html(selectHtml);
            }
            if($("#obra1").prop("checked")){
                var selectHtml = '<option  value=""></option>';
                     selectHtml += '<option  <?php echo $dadosContratos->regexecoumodfornec == "INTEGRAL"?'selected="selected"':''; ?> value="INTEGRAL">INTEGRAL</option>';
                     selectHtml += '<option <?php echo $dadosContratos->regexecoumodfornec == "PARCELADO"?'selected="selected"':''; ?> value="PARCELADO">PARCELADO</option>';
                 $("#cmb_regimeExecucaoModoFornecimento1").html(selectHtml);
            }
            $("#btnvoltar").on('click', function(){
                window.location.href = "./CadContratoConsolidadoPesquisar.php";
            });
            $("#fieldConsorcio0").on('click', function(){
                $("#addFornecedor").show();
            });
            $("#fieldConsorcio1").on('click', function(){
                $("#addFornecedor").hide();
            });
            $("#addFornecedores").on('click', function(){
                $.post("postDadosManter.php",{op:"ModalFornecedorCred"}, function(data){
                    $(".modal-content").html(data);
                    $(".modal-content").attr("style","min-height: 21%;width: 40%;");
                    $("#modal").show();
                    $.post("postDadosManter.php",{op:"ExibeFornecedorExtra",idregistro:$("input:[name=idregistro]").val()}, function(data){
                        ObjJson = JSON.parse(data);
                            //$(".dadosFornec").html(CriatableModal(ObjJson));
			        });
			    });
            });
            $("#manterfiscal").on('click', function(){
                $.post("postDadosManter.php",{op:"modalFiscal"}, function(data){
                    $(".modal-content").attr("style","min-height: 21%;width: 40%;");
                    $(".modal-content").html(data);
                    $("#modal").show();
			    });
            });
            $("#btn-fecha-modal").live('click', function(){
                $("#modal").hide();
            });
            $('#radio-cpf').live('click',function(){
                $("#cnpj").val('');
                $(".mostracnpj").hide();
                $(".mostracpf").show();
            });
            $('#radio-cnpj').live('click',function(){
                $(".mostracnpj").show();
                $("#cpf").val('');
                $(".mostracpf").hide();
            });
            $("#btnAdicionarModal").live('click',function(){
                $.post("postDadosManter.php",{op:"Fornecerdor2",cpf:$("#cpf").val(),cnpj:$("#cnpj").val()}, function(data){
                    ObjJson = JSON.parse(data);
                    if(ObjJson.status == false){
                        alert(ObjJson.msm);
                    }else{
                        $(".dadosFornec").html(CriatableModal(ObjJson));
                        $("#shownewfornecedores").html(CriatableView(ObjJson));
                        $("#modal").hide();
                    }
			    });
            });
            $(".botao_fechar").live('click',function(){
                $("#modal").hide();
            });
            $(".botao_fechar_fiscal").live('click',function(){
                $("#modal").hide();
            });
            $("#btn-fecha-modal-fiscal").live('click',function(){
                $("#modal").hide();
            });
            $("#opcaoExecucaoContrato").on("change",function(){
                if($("#opcaoExecucaoContrato").val() == ""){
                    $("#prazo").attr("disabled","disabled");
                }else{
                    $("#prazo").removeAttr("disabled");
                }
                //
            });
            $("#opcaoExecucaoContrato").on('change', function(){
                if($("#opcaoExecucaoContrato").val() == "D"){
                    prazo = ( $("#prazo").val() != '')?$("#prazo").val():0;
                    dataV = $("#vigenciaDataInicio").val();
                    prazo = parseInt(prazo);
                    dataSF = dataV.split("/");
                    novaData = new Date(parseInt(dataSF[2]),parseInt(dataSF[1]-1),parseInt(dataSF[0]));
                    novaData.setDate(novaData.getDate()+parseInt(prazo));
                    novaData.setMonth(novaData.getMonth());
                    novaData.setFullYear(novaData.getFullYear());
                    dia = novaData.getDate();
                    mes = parseInt(novaData.getMonth())+1;
                    if(dia.toString().length < 2){
                        dia = "0"+dia;
                    }
                    if(mes.toString().length < 2){
                        mes = "0"+mes;
                    }
                    impNovaData = dia+'/'+mes+'/'+parseInt(novaData.getFullYear());
                   $("#vigenciaDataTermino").html(impNovaData);
                   $("#execucaoDataTermino").html(impNovaData);
                }else if($("#opcaoExecucaoContrato").val() == "M"){
                    prazo = ( $("#prazo").val() != '')?$("#prazo").val():0;
                    dataV = $("#vigenciaDataInicio").val();
                    dataSF = dataV.split("/");
                    novaData = new Date(parseInt(dataSF[2]),parseInt(dataSF[1]-1),parseInt(dataSF[0]));
                    novaData.setDate(novaData.getDate());
                    novaData.setMonth(novaData.getMonth()+parseInt(prazo));
                    novaData.setFullYear(novaData.getFullYear());
                    dia = novaData.getDate();
                    mes = parseInt(novaData.getMonth())+1;
                    if(dia.toString().length < 2){
                        dia = "0"+dia;
                    }
                    if(mes.toString().length < 2){
                        mes = "0"+mes;
                    }
                    impNovaData = dia+'/'+mes+'/'+parseInt(novaData.getFullYear());
                   $("#vigenciaDataTermino").html(impNovaData);
                   $("#execucaoDataTermino").html(impNovaData);
                }
            });
            $("#prazo").on("blur",function(){
                if($("#opcaoExecucaoContrato").val() == "D"){
                    prazo = ( $("#prazo").val() != '')?$("#prazo").val():0;
                    dataV = $("#vigenciaDataInicio").val();
                    prazo = parseInt(prazo);
                    dataSF = dataV.split("/");
                    novaData = new Date(parseInt(dataSF[2]),parseInt(dataSF[1]-1),parseInt(dataSF[0]));
                    novaData.setDate(novaData.getDate()+parseInt(prazo));
                    novaData.setMonth(novaData.getMonth());
                    novaData.setFullYear(novaData.getFullYear());
                    dia = novaData.getDate();
                    mes = parseInt(novaData.getMonth())+1;
                    if(dia.toString().length < 2){
                        dia = "0"+dia;
                    }
                    if(mes.toString().length < 2){
                        mes = "0"+mes;
                    }
                    impNovaData = dia+'/'+mes+'/'+parseInt(novaData.getFullYear());
                   $("#vigenciaDataTermino").html(impNovaData);
                   $("#execucaoDataTermino").html(impNovaData);
                }else if($("#opcaoExecucaoContrato").val() == "M"){
                    prazo = ( $("#prazo").val() != '')?$("#prazo").val():0;
                    dataV = $("#vigenciaDataInicio").val();
                    dataSF = dataV.split("/");
                    novaData = new Date(parseInt(dataSF[2]),parseInt(dataSF[1]-1),parseInt(dataSF[0]));
                    novaData.setDate(novaData.getDate());
                    novaData.setMonth(novaData.getMonth()+parseInt(prazo));
                    novaData.setFullYear(novaData.getFullYear());
                    dia = novaData.getDate();
                    mes = parseInt(novaData.getMonth())+1;
                    if(dia.toString().length < 2){
                        dia = "0"+dia;
                    }
                    if(mes.toString().length < 2){
                        mes = "0"+mes;
                    }
                    impNovaData = dia+'/'+mes+'/'+parseInt(novaData.getFullYear());
                    $("#execucaoDataTermino").html(impNovaData);
                   $("#vigenciaDataTermino").html(impNovaData);
                   
                }
            });
            $("#calendariovdi").on('blur', function(){
                if($("#opcaoExecucaoContrato").val() == "D"){
                    prazo = ( $("#prazo").val() != '')?$("#prazo").val():0;
                    prazo = parseInt(prazo);
                    dvi = $("#vigenciaDataInicio").val();
                    dataVigenciaInicio = dvi.split('/');
                    novaData = new Date(parseInt(dataVigenciaInicio[2]),parseInt(dataVigenciaInicio[1]-1),parseInt(dataVigenciaInicio[0]));
                    novaData.setDate(novaData.getDate()+parseInt(prazo));
                    novaData.setMonth(novaData.getMonth());
                    novaData.setFullYear(novaData.getFullYear());
                    dia = novaData.getDate();
                    mes = parseInt(novaData.getMonth())+1;
                    if(dia.toString().length < 2){
                        dia = "0"+dia;
                    }
                    if(mes.toString().length < 2){
                        mes = "0"+mes;
                    }
                    impNovaData = dia+'/'+mes+'/'+parseInt(novaData.getFullYear());
                   $("#vigenciaDataTermino").html(impNovaData);
                   $("#execucaoDataTermino").html(impNovaData);
                   $("#execucaoDataInicio").html(dvi);
                }else if($("#opcaoExecucaoContrato").val() == "M"){
                    prazo = ( $("#prazo").val() != '')?$("#prazo").val():0;
                    dvi = $("#vigenciaDataInicio").val();
                    dataVigenciaInicio = dvi.split('/');
                    novaData = new Date(parseInt(dataVigenciaInicio[2]),parseInt(dataVigenciaInicio[1]-1),parseInt(dataVigenciaInicio[0]));
                    novaData.setDate(novaData.getDate());
                    novaData.setMonth(novaData.getMonth()+parseInt(prazo));
                    novaData.setFullYear(novaData.getFullYear());
                    dia = novaData.getDate();
                    mes = parseInt(novaData.getMonth())+1;
                    if(dia.toString().length < 2){
                        dia = "0"+dia;
                    }
                    if(mes.toString().length < 2){
                        mes = "0"+mes;
                    }
                    impNovaData = dia+'/'+mes+'/'+parseInt(novaData.getFullYear());
                    $("#vigenciaDataTermino").html(impNovaData);
                    $("#execucaoDataTermino").html(impNovaData);
                    $("#execucaoDataInicio").html(dvi);
                }

            });
            $("#vigenciaDataInicio").on('blur', function(){
                    dvi = $("#vigenciaDataInicio").val();
                    dvf = $("#vigenciaDataTermino").val();
                    dataVigenciaInicio = dvi.split('/');
                    dataVigenciaFim    = dvf.split('/');
                    novaData       = new Date(parseInt(dataVigenciaInicio[2]),parseInt(dataVigenciaInicio[1]-1),parseInt(dataVigenciaInicio[0]));
                    novaDataFim = new Date(parseInt(dataVigenciaFim[2]),parseInt(dataVigenciaFim[1]-1),parseInt(dataVigenciaFim[0]));
                    if(novaData > novaDataFim){
                        avisoexclusao("A data inicial não pode ser maior que a data final ");
                    }
            });
            $("#vigenciaDataTermino").on("blur",function(){
                    dvi = $("#vigenciaDataInicio").val();
                    dvf = $("#vigenciaDataTermino").val();
                    dataVigenciaInicio = dvi.split('/');
                    dataVigenciaFim    = dvf.split('/');
                    novaData       = new Date(parseInt(dataVigenciaInicio[2]),parseInt(dataVigenciaInicio[1]-1),parseInt(dataVigenciaInicio[0]));
                    novaDataFim = new Date(parseInt(dataVigenciaFim[2]),parseInt(dataVigenciaFim[1]-1),parseInt(dataVigenciaFim[0]));
                    if(novaData > novaDataFim){
                        avisoexclusao("A data final não pode ser menor que a data inicial ");
                    }
            });
            $("#execucaoDataInicio").on("blur", function(){
                    dei = $("#execucaoDataInicio").val();
                    def = $("#execucaoDataTermino").val();
                    dataExecucaoInicio = dei.split('/');
                    dataExecucaoFim    = def.split('/');
                    novaData       = new Date(parseInt(dataExecucaoInicio[2]),parseInt(dataExecucaoInicio[1]-1),parseInt(dataExecucaoInicio[0]));
                    novaDataFim = new Date(parseInt(dataExecucaoFim[2]),parseInt(dataExecucaoFim[1]-1),parseInt(dataExecucaoFim[0]));
                    if(novaData > novaDataFim){
                        avisoexclusao("A data final não pode ser menor que a data inicial ");
                    }
            });
            $("#execucaoDataTermino").on("blur", function(){
                    dei = $("#execucaoDataInicio").val();
                    def = $("#execucaoDataTermino").val();
                    dataExecucaoInicio = dei.split('/');
                    dataExecucaoFim    = def.split('/');
                    novaData       = new Date(parseInt(dataExecucaoInicio[2]),parseInt(dataExecucaoInicio[1]-1),parseInt(dataExecucaoInicio[0]));
                    novaDataFim = new Date(parseInt(dataExecucaoFim[2]),parseInt(dataExecucaoFim[1]-1),parseInt(dataExecucaoFim[0]));
                    if(novaData > novaDataFim){
                        avisoexclusao("A data final não pode ser menor que a data inicial ");
                    }
            });
            $("#btnPesquisaModal").live("click",function(){
                const tipo = $("input[name='tipofiscalr']:checked").val();
                $.post("postDadosManter.php",{op:"Fiscal",cpf:$("#cpffiscal").val(),tipo:tipo}, function(data){
                    ObjJson = JSON.parse(data);
                    if(ObjJson.status){
                        $(".modal-content").attr("style","min-height: 25%;width: 79%;");
                        $(".dadosFiscal").html(CriaTabelaFiscal(ObjJson.dados));
                        $("#cpffiscal").attr('disabled','disabled');
                        $("input[name='tipofiscalr']").attr('disabled','disabled');
                        $("#btnNewPesquisaModal").show();
                        $("#btnPesquisaModal").hide();
                        $("#tdmensagemM").hide();
                    }else if(!ObjJson.status){
                        $("#tdmensagemM").show();
					    $(".mensagem-textoM").html(ObjJson.msm);
                    }
			    });
            });
            $("#btnNewPesquisaModal").live('click', function(){
                    $(".modal-content").attr("style","min-height: 21%;width: 40%;");
                     $("#cpffiscal").removeAttr('disabled');
                     $("#cpffiscal").val('');
                     $("input[name='tipofiscalr']").removeAttr('disabled');
                     $("#btnNewPesquisaModal").hide();
                     $("#btnPesquisaModal").show();
                     $("#tdmensagemM").hide();
                     $(".dadosFiscal").html('');
            });
            $("#btnAdicionarFiscalModal").live("click",function(){
                $.post("postDadosManter.php",{op:"ModalInserirFiscal"}, function(data){
                    $(".modal-content").attr("style","min-height: 21%;width: 40%;");
                    $(".modal-content").html(data);
                    $("#modal").show();
			    });
            });
            $("#formFiscal").live("submit",function(){
                var formulario = $("#formFiscal").serialize();
               
                if(!TestaCPF($("#cpfrepresenlegal").val())){
                    avisoexclusao("Informe: Um CPF válido para o representante.");
                    return false;
                }
                if(!TestaCPF($("#cpfgestor").val())){
                    avisoexclusao("Informe: Um CPF válido para o gestor.");
                    return false;
                }
                $.post("postDadosManter.php",$("#formFiscal").serialize(),function(data){
                    ObjJson = JSON.parse(data);
                      if(!ObjJson.Sucess){
                        $("#tdmensagemM").show();
					    $(".mensagem-textoM").html(ObjJson.msm);
                      }else{
                        $("#modal").hide();
                        $(".dadosFiscal").html(CriaTabelaFiscalView(ObjJson.dados));
                        $("#mostrartbfiscais").html(CriaTabelaFiscalView(ObjJson.dados));
                        $("#btnPesquisaModal").hide();
                      }
                });
                return false;
            });
            $("#removefiscal").live('click',function(){
                const fiscalselec = $("input[name='fiscais']:checked").val();
                $.post("postDadosManter.php",{op:"RemoveFiscal",marcador:fiscalselec},function(data){
                     ObjJson = JSON.parse(data);
                     $("#mostrartbfiscais").html(CriaTabelaFiscalView(ObjJson));
                });
            });
            $("#btnRemoveAnexo").live("click",function(){
                const docanexselec = $("input[name='docanex']:checked").val();
                $.post("postDadosManter.php",{op:"RemoveDocAnex",marcador:docanexselec},function(data){
                     $("#FootDOcFiscal").html(data);
                });
            });
            $("#btnAlterarModal").live("click",function(){
                const docanexselec = $("input[name='cpfFiscal']:checked").val();
                $.post("postDadosManter.php",{op:"ModalAlterarFiscal",marcador:docanexselec},function(data){
                    $(".modal-content").attr("style","min-height: 21%;width: 40%;");
                    $(".modal-content").html(data);
                    $("#modal").show();
                });
            });
             $("#formAltFiscal").live("submit",function(){
                let formulario = $("#formAltFiscal").serialize();
                $.post("postDadosManter.php",$("#formAltFiscal").serialize(),function(data){
                    ObjJson = JSON.parse(data);
                      if(!ObjJson.Sucess){
                        $("#tdmensagemM").show();
					    $(".mensagem-textoM").html(ObjJson.msm);
                      }else{
                        $.post("postDadosManter.php",{op:"modalFiscal"}, function(data){
                                $(".modal-content").html(data);
                                 $("#modal").hide();
                                 $(".error").html("Atenção");
                                $(".error").css("color","#007fff");
                                avisoexclusao("Alteração realizada com sucesso");
			            });
                        
                      }
                });
                return false;
            });
            $("#btnExcluirModal").live("click", function(){
                    const cpfFiscal = $("input[name='cpfFiscal']:checked").val();
                    const tipofiscal = $("input[name='tipofiscalr']:checked").val();
                    const op           = "excluirFiscal";
                    $.post("postDadosManter.php", {op:op,cpf:cpfFiscal,tipo:tipofiscal}, function(data){
                                ObjJson = JSON.parse(data);
                            if(!ObjJson.Sucess){
                                $("#tdmensagemM").show();
                                $(".mensagem-textoM").html(ObjJson.msm);
                            }else{
                                $("#tdmensagemM").show();
                                $(".error").css("color","#007fff");
                                $(".error").html("Atenção");
                                $(".mensagem-textoM").html(ObjJson.msm);
                                $(".dadosFiscal").html(CriaTabelaFiscal(ObjJson.dados));
                            }
                    });
            });
            $(".btn-pesquisa-scc").on('click', function(){
                $.post("postDados.php",{op:"modalSccPesquisa"}, function(data){
                    $(".modal-content").html(data);
                    $("#modal").show();
                    $('#modalNScc').mask('9999.9999/9999');
                    //Montagem da data inicial e final, sugestão de pesquisa para tres meses
                    var hoje = new Date(); 
                    var mesRegular = ['01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12'];
                    diaComZero = hoje.getDate() <= 9 ? '0' + hoje.getDate() : hoje.getDate();
                    var mostrar = diaComZero + '/' + mesRegular[hoje.getMonth()] + '/' + hoje.getFullYear();
                    $('#DataFimPCS').val(mostrar);
                    hoje.setMonth(hoje.getMonth() -3);
                    diaComZero = hoje.getDate() <= 9 ? '0' + hoje.getDate() : hoje.getDate();
                    mostrar = diaComZero + '/' + mesRegular[hoje.getMonth()] + '/' + hoje.getFullYear();
                    $('#DataIniPCS').val(mostrar);
                });
            });
            $("#btnPesquisaModalSCC").live('click', function(){
                $(".modal-content").attr("style","min-height: 25%;width: 64%;");
                $.post("./postDados.php",
                        {
                            op               : "PesquisaModalScc",
                            numeroScc        : $("#modalNScc").val(),
                            CodTipoCompra    : $("#modal-origem").val(),
                            NumContrato      : $("#numcontrato").val(),
                            dataIni          : $("#DataIniPCS").val(),
                            dataFim          : $("#DataFimPCS").val()
                        },
                    function(data){
                        $("#selectDivModal").html(data);
                    });
            });
            if($("#solicitacaoCompra").val()){
                verificaContratoComSCC();
            }
            $('.classalinkpdf').on("click",function() {
                var nomePdf   = $(this).html().replace(/\<br>/g, '');
                var nomeClass = $(this).prop('id');
                Convert(nomeClass, nomePdf);
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
                        <a href="../index.php"><font color="#000000">Página Principal</font></a> > Contratos > Contratos  Consolidado
                    </td>
                </tr>
                <!-- Fim do Caminho-->
                <?php
                //ÚLTIMO GESTOR DO CONTRATO CONSOLIDADO
                if(!empty($dadosGestorConsolidado))
                {
                    //var_dump($dadosGestorConsolidado);die;
                    //nome napostnmgt
                    $tpl->NOMEGESTORCONS = $dadosGestorConsolidado->napostnmgt;
                    //matricula napostmtgt.
                    $tpl->MATRICULAGESTORCONS = $dadosGestorConsolidado->napostmtgt;
                    //CPF napostcpfg
                    $tpl->CPFGESTORCONS = $ObjContrato->MascarasCPFCNPJ($dadosGestorConsolidado->napostcpfg);
                    //e-mail napostmlgt
                    $tpl->EMAILGESTORCONS = $dadosGestorConsolidado->napostmlgt;
                    //telefone eaposttlgt
                    $tpl->TELEFONEGESTORCONS = $dadosGestorConsolidado->eaposttlgt;
                }
                //SE NÃO TIVER GESTOR NO CONTRATO CONSOLIDADO TRÁS O DO CONTRATO ORIGINAL
                        else if(!empty($dadosContratos))
                        {
                    $tpl->NOMEGESTORCONS = $dadosContratos->nomegestor;
                    $tpl->MATRICULAGESTORCONS = $dadosContratos->matgestor;
                    $tpl->CPFGESTORCONS = $ObjContrato->MascarasCPFCNPJ($dadosContratos->cpfgestor);
                    $tpl->EMAILGESTORCONS = $dadosContratos->emailgestor;
                    $tpl->TELEFONEGESTORCONS = $dadosContratos->fonegestor;
                    
        }
        
        
                ?>
                <!-- Corpo -->
                <tr>
                    <td width="150"></td>
                    <td class="textonormal">
                        <table  cellspacing="0" cellpadding="3" summary=""  width="100%" >
                            <tr>
                                <td class="textonormal">
                                <?php 
                                    echo NavegacaoAbasConsolidadoComMedicaoEAditivo(on,off,off,count($AllMedicao),off,$AllAditivos,off,$AllApostilamento);
                                ?>
                                    <table id="scc_material" summary="" style="border: 1px solid #75ade6; border-radius: 4px;"  width="100%" class="textonormal">
                                        <tbody border="0">
                                            <tr>
                                                <td align="left" colspan="4" > 
                                                    <!-- <table id="scc_material" summary="" bordercolor="#75ADE6"  style="border: 3px solid #75ade6; border-radius: 4px;" width="100%"> -->
                                                        
                                                            <tr>
                                                                    <td colspan="3" class="titulo3 itens_material" align="center"  bgcolor="#75ADE6" valign="middle"> 
                                                                        CONTRATO CONSOLIDADO
                                                                    </td>
                                                            </tr>
                                                    
                                                            <tr>
                                                                <td  bgcolor="#DCEDF7">Número do Contrato/Ano</td>
                                                                <td >
                                                                    <?php echo $dadosContratos->ncontrato;?>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                            <?php
                                                                $modificadisplayScc = $numScc;
                                                                if(empty($modificadisplayScc)){
                                                                    $modificadisplayScc = $SCC;
                                                                }
                                                             if(empty($modificadisplayScc)){
                                                                    $display ='none';
                                                                }?>
                                                                <td  bgcolor="#DCEDF7" style = "display: <?php echo $display?>">Solici. de Compra/Contratação-SCC *</td>
                                                                   <td class="inputs">
                                                                        <span id="panelGroupSolicitacao">
                                                                        <?php echo !empty($numScc)?$numScc:$SCC;?>
                                                                        </span>
                                                                    </td>
                                                            </tr>
                                                            <tr>
                                                                <td bgcolor="#DCEDF7">Origem </td>
                                                                <td class="inputs"> 
                                                                    <span id="origem" class="caixaalta"> <?php echo $TipoCOmpra->etpcomnome; ?></span>
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
                                                                <td class="inputs" class="caixaalta">
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
                                                                                            <label>
                                                                                                <?php
                                                                                                function validaCPF($cpfnpj) {
                                                                                                    
 
                                                                                                    $cpf = preg_replace( '/[^0-9]/is', '', $cpfnpj );
                                                                                        
                                                                                                    if (strlen($cpfnpj) != 11) {
                                                                                                        return false;
                                                                                                    }
                                                                                        
                                                                                        
                                                                                                    if (preg_match('/(\d)\1{10}/', $cpfnpj)) {
                                                                                                        return false;
                                                                                                    }
                                                                                        
                                                                                                    for ($t = 9; $t < 11; $t++) {
                                                                                                        for ($d = 0, $c = 0; $c < $t; $c++) {
                                                                                                            $d += $cpfnpj[$c] * (($t + 1) - $c);
                                                                                                        }
                                                                                                        $d = ((10 * $d) % 11) % 10;
                                                                                                        if ($cpfnpj[$c] != $d) {
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
                                                                                            <?php
                                                                                            if(!empty($fornAltAditivo[0]->cpf) || !empty($fornAltAditivo[0]->cnpj)){
                                                                                                echo !empty($fornAltAditivo[0]->cnpj) ? 
                                                                                                $ObjContrato->MascarasCPFCNPJ($fornAltAditivo[0]->cnpj) : $ObjContrato->MascarasCPFCNPJ($fornAltAditivo[0]->cpf);
                                                                                                //var_dump($fornAltAditivo[0]->cnpj);die;  
                                                                                            }
                                                                                            else
                                                                                            { 
                                                                                              echo !empty($CpfCnpj)?$CpfCnpj:$ObjContrato->MascarasCPFCNPJ($cpfCNPJ);
                                                                                              
                                                                                            }
                                                                                            ?>
                                                                                        </label>
                                                                                        
                                                                                    </div>
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td class="labels">
                                                                                    <label for="" style=";" class="">
                                                                                        Razão Social 
                                                                                    </label>
                                                                                </td>
                                                                                <td class="inputs" colspan="3">
                                                                                    <div id="_panelGroupRazao">
                                                                                        <span id="_razaoSocialfornecedor" class="caixaalta"> 
                                                                                            <?php echo !empty($fornAltAditivo[0]->razao) ? $fornAltAditivo[0]->razao:$dadosContratos->razao;?>
                                                                                        </span>
                                                                                    </div>
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td class="labels">
                                                                                    <label for="" style=";" class="">
                                                                                        Logradouro 
                                                                                    </label>
                                                                                </td>
                                                                                <td class="inputs" colspan="3">
                                                                                    <span id="_logradourofornecedor" class="caixaalta">
                                                                                        <?php if(!empty($fornAltAditivo[0]->rua)){
                                                                                                echo $fornAltAditivo[0]->rua;
                                                                                                }else{echo !empty($Rua)?$Rua:$dadosContratos->endereco;}?>
                                                                                    </span>
                                                                                </td>
                                                                            </tr>
                                                                                <tr>
                                                                                    <td class="labels">
                                                                                        <label for="" style=";" class="">
                                                                                            Número 
                                                                                        </label>
                                                                                    </td>
                                                                                    <td class="inputs" colspan="3">
                                                                                        <span id="_numerofornecedor">
                                                                                            <?php if(!empty($fornAltAditivo[0]->numero)){
                                                                                                echo $fornAltAditivo[0]->numero;
                                                                                                }else{echo !empty($numEnd)?$numEnd:$dadosContratos->numerofornecedor;}?>
                                                                                        </span>
                                                                                    </td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td class="labels">
                                                                                        <label for="" style=";" class="">
                                                                                            Complemento 
                                                                                        </label>
                                                                                    </td>
                                                                                    <td class="inputs">
                                                                                        <span id="_complementoLogradourofornecedor" class="caixaalta"> 
                                                                                            <?php if(!empty($fornAltAditivo[0]->complemento)){
                                                                                                echo $fornAltAditivo[0]->complemento;
                                                                                                }else{echo !empty($complEnd)?$complEnd:$dadosContratos->complementofornecedor;}?>
                                                                                        </span>
                                                                                    </td>
                                                                                    <td class="labels">
                                                                                        <label for="" style=";" class="">
                                                                                            Bairro 
                                                                                        </label>
                                                                                    </td>
                                                                                    <td class="inputs">
                                                                                        <span id="_bairrofornecedor" class="caixaalta">
                                                                                            <?php if(!empty($fornAltAditivo[0]->bairro)){
                                                                                                echo $fornAltAditivo[0]->bairro;
                                                                                                }else{echo !empty($Bairro)?$Bairro: $dadosContratos->bairrofornecedor;}?>
                                                                                        </span>
                                                                                    </td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td class="labels">
                                                                                        <label for="" style=";" class="">
                                                                                            Cidade 
                                                                                        </label>
                                                                                    </td>
                                                                                    <td class="inputs">
                                                                                        <span id="_cidadefornecedor" class="caixaalta">
                                                                                            <?php if(!empty($fornAltAditivo[0]->cidade)){
                                                                                                echo $fornAltAditivo[0]->cidade;
                                                                                                }else{echo !empty($Cidade)?$Cidade:$dadosContratos->cidadefornecedor;}?>
                                                                                        </span>
                                                                                    </td>
                                                                                    <td class="labels">
                                                                                        <label for="" style=";" class="">
                                                                                                UF 
                                                                                        </label>
                                                                                    </td>
                                                                                    <td class="inputs">
                                                                                        <span id="_estadofornecedor" class="caixaalta">
                                                                                            <?php if(!empty($fornAltAditivo[0]->estado)){
                                                                                                echo $fornAltAditivo[0]->estado;
                                                                                                }else{echo !empty($UF)?$UF:$dadosContratos->uffornecedor;}?>
                                                                                        </span>
                                                                                    </td>
                                                                                </tr>
                                                                        </tbody>
                                                                    </table>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td bgcolor="#DCEDF7">Consórcio / Matriz-Filial / Publicidade ?* </td>
                                                                <td class="inputs">
                                                                    <table id="fieldConsorcio" class="textonormal">
                                                                        <tbody>
                                                                            <tr>
                                                                                <td class="caixaalta">
                                                                                    <?php echo $dadosContratos->consocio;?>
                                                                                </td>
                                                                            </tr>
                                                                        </tbody>
                                                                    </table>
                                                                </td>
                                                            </tr>
                                                            <tr id="addFornecedor" <?php echo $dadosContratos->consocio == 'SIM'?'':'style="display:none;"';?> class="textonormal">
                                                                <td bgcolor="#DCEDF7">
                                                                    Fornecedores  
                                                                </td>
                                                                <td class="inputs" colspan="2">
                                                                    <div id="shownewfornecedores"></div>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td bgcolor="#DCEDF7">Contínuo * </td>
                                                                <td class="inputs">
                                                                    <table id="fieldContinuo" class="textonormal">
                                                                        <tbody>
                                                                            <tr>
                                                                                    <td class="caixaalta">
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
                                                                                    <?php echo $dadosContratos->obra;?> 
                                                                                </td>
                                                                            </tr>
                                                                        </tbody>
                                                                    </table>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td bgcolor="#DCEDF7"><?php echo (strtoupper($dadosContratos->obra) == "SIM")?"Regime de Execução *":"Modo de Fornecimento *";?> </td>
                                                                <td class="inputs caixaalta">
                                                                    <?php echo $dadosContratos->regexecoumodfornec; ?>
                                                                </td>
                                                            <tr> 
                                                                <td bgcolor="#DCEDF7">Opção de Execução do Contrato * </td>
                                                                <td class="inputs">
                                                                        <?php echo strtoupper($dadosContratos->opexeccontrato == "D"?'Dias':'Meses'); ?>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td bgcolor="#DCEDF7">Prazo de Execução do Contrato * </td>
                                                                <td class="inputs">
                                                                    <?php echo empty($dataAditivos->prazo)?$dadosContratos->prazoexec:$dataAditivos->prazo; ?>
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
                                                                        <?php echo DataBarra($dadosContratos->datainivige); ?>
                                                                        <!-- <?php //echo empty($dataAditivos->daditiinvg)?DataBarra($dadosContratos->datainivige):DataBarra($dataAditivos->daditiinvg); ?> -->
                                                                    </span>
                                                                </td>
                                                                <td class="inputs">
                                                                    <span id="vigenciaGroup">
                                                                        <?php echo empty($dataAditivos->daditifivg)?DataBarra($dadosContratos->datafimvige):DataBarra($dataAditivos->daditifivg); ?>
                                                                    </span>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td bgcolor="#DCEDF7">Execução </td>
                                                                <td class="inputs">
                                                                    <span id="execucaoGroup">
                                                                        <?php
                                                                        echo DataBarra($dadosContratos->datainiexec);
                                                                        ?>
                                                                    </span>
                                                                </td>   
                                                                <td class="inputs">
                                                                    <span id="execucaoGroup">
                                                                        <?php 

                                                                            if(empty($dataAditivos)){
                                                                               
                                                                                echo DataBarra($dadosContratos->datafimexec);
                                                                               
                                                                            }elseif(!empty($dataAditivos->data_fim_execucao)){
                                                                                
                                                                                echo DataBarra($dataAditivos->data_fim_execucao);
                                                                                
                                                                            }elseif(empty($dataAditivos->data_fim_execucao)){
                                                                                $DATAEXECUCAOFIM = $ObjContrato->CalculaDataFinalDeExecucao($dataAditivos->daditiinex,$dataAditivos->cctrpcopex,$dataAditivos->prazo); 
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
                                                                        ?>
                                                                    </span>
                                                                </td>   
                                                            </tr>
                                                            <tr>
                                                                <td bgcolor="#DCEDF7"> Valor Original</td>
                                                                <td><?php
                                                                         //echo number_format($valororiginal,4,',','.');
                                                                         echo $objContratoFuncGeral->valorOriginal($idRegistro)
                                                                    ?>
                                                                    </td>
                                                            </tr>
                                                            <tr>
                                                                <td bgcolor="#DCEDF7">Valor Global com Aditivos/Apostilamentos </td>
                                                                <td><?php
                                                                        
                                                                        echo $objContratoFuncGeral->valorGlobal($idRegistro);
                                                                           
                                                                    
                                                                ?></td>
                                                            </tr>
                                                            <tr>
                                                                <td bgcolor="#DCEDF7">Valor Executado Acumulado </td>
                                                                <td>
                                                                    <?php 
                                                                        
                                                                        echo $objContratoFuncGeral->valorExecutado($idRegistro);

                                                                    ?>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td bgcolor="#DCEDF7">Saldo a Executar </td>
                                                                <td>
                                                                    <?php  

                                                                        echo $objContratoFuncGeral->saldoAExecutar($idRegistro);
                                                                        
                                                                    ?>
                                                                </td>
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
                                                                        
                                                                        //foreach($dadosGarantia as $garantia){ 
                                                                         /*   if($dadosContratos->codgarantia == $dadosContratos->codisequtipogarantia){
                                                                    
                                                                            $GARANTIA = $dadosContratos->descricaogarantia;
                                                                            echo $GARANTIA;
                                                                            }elseif(empty($dadosContratos->codisequtipogarantia)) {
                                                                            echo 'SEM GARANTIA';
                                                                        }
                                                                        //}
                                                                        //echo ($dadosContratos->codgarantia == $dadosContratos->codisequtipogarantia)?$dadosContratos->descricaogarantia:'SEM GARANTIA';    
                                                                            
                                                                        /*if(!empty($dadosContratos->codisequtipogarantia)){
                                                                            echo $dadosContratos->descricaogarantia; 
                                                                         }else{
                                                                            echo 'SEM GARANTIA';
                                                                         }    */
                                                                        
                                                                        ?>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td bgcolor="#DCEDF7">Número de Parcelas</td>
                                                                <td name="dado">
                                                                    <?php echo $dadosContratos->numerodeparcelas?>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td bgcolor="#DCEDF7">Valor da Parcela</td>
                                                                <td name="dado">
                                                                    <?php echo number_format($dadosContratos->valordaparcela,4,',','.') ?>
                                                                </td>           
                                                            </tr>
                                                                <th colspan="3" bgcolor="#bfdaf2">
                                                                    <span id="labelDataInicioOrdemServico">REPRESENTANTE LEGAL</span>
                                                                </th>
                                                            </tr>
                                                            <tr>
                                                                <td bgcolor="#DCEDF7">Nome * </td>
                                                                <td class="caixaalta">
                                                                            <?php echo empty($repAltAditivo[0]->naditinmrl) ? $dadosContratos->nomerepresenlegal : $repAltAditivo[0]->naditinmrl;?>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td bgcolor="#DCEDF7">CPF *  </td>
                                                                <td  >
                                                                            <?php if(empty($repAltAditivo[0]->naditinmrl)){ echo $ObjContrato->MascarasCPFCNPJ($dadosContratos->cpfrepresenlegal);}else{echo $ObjContrato->MascarasCPFCNPJ($repAltAditivo[0]->eaditicpfr);}?>                                                                      
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td bgcolor="#DCEDF7">Cargo  </td>
                                                                <td  class="caixaalta">
                                                                           <?php echo empty($repAltAditivo[0]->naditinmrl) ? $dadosContratos->cargorepresenlegal : $repAltAditivo[0]->eaditicgrl;?>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td bgcolor="#DCEDF7">Identidade  </td>
                                                                <td >
                                                                         <?php echo empty($repAltAditivo[0]->naditinmrl) ? $dadosContratos->identidaderepreslegal : $repAltAditivo[0]->eaditiidrl;?>
                                                                 </td>
                                                            </tr>
                                                            <tr>
                                                                <td bgcolor="#DCEDF7">Órgão Emissor </td>
                                                                <td class="caixaalta">
                                                                        <?php echo empty($repAltAditivo[0]->naditinmrl) ? $dadosContratos->orgaoexpedrepreselegal : $repAltAditivo[0]->naditioerl;?>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td bgcolor="#DCEDF7">UF da Identidade </td>
                                                                <td class="caixaalta">
                                                            <?php echo empty($repAltAditivo[0]->naditinmrl) ? $dadosContratos->ufrgrepresenlegal : $repAltAditivo[0]->naditiufrl;?>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td bgcolor="#DCEDF7">Cidade de Domicílio </td>
                                                                <td class="caixaalta">
                                                                            <?php echo empty($repAltAditivo[0]->naditinmrl) ? $dadosContratos->cidadedomrepresenlegal : $repAltAditivo[0]->naditicdrl;?>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td bgcolor="#DCEDF7">Estado de Domicílio </td>
                                                                <td class="caixaalta">
                                                                         <?php echo empty($repAltAditivo[0]->naditinmrl) ? $dadosContratos->estdomicrepresenlegal : $repAltAditivo[0]->naditiedrl;?>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td bgcolor="#DCEDF7">Nacionalidade  </td>
                                                                <td class="caixaalta">
                                                                        <?php echo empty($repAltAditivo[0]->naditinmrl) ? $dadosContratos->naciorepresenlegal : $repAltAditivo[0]->naditinarl;?>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td bgcolor="#DCEDF7">Estado Civil </td>
                                                                    <td class="caixaalta">
                                                                        <?php       
                                                                                if (empty($repAltAditivo[0]->naditinmrl) || $repAltAditivo[0]->naditinmrl == "" || $repAltAditivo[0]->naditinmrl == " " || $repAltAditivo[0]->naditinmrl == null) {
                                                                                  switch ($dadosContratos->estacivilrepresenlegal) {
                                                                                            case 'S':
                                                                                                echo "SOLTEIRO";
                                                                                            break;
                                                                                            case 'C':
                                                                                                echo "CASADO";
                                                                                            break;
                                                                                            case 'D':
                                                                                                echo "DIVORCIADO";
                                                                                            break;
                                                                                            case 'V':
                                                                                                echo "VIÚVO";
                                                                                            break;
                                                                                            case 'O':
                                                                                                echo "OUTROS";
                                                                                            break;
                                                                                            } 
                                                                                        }
                                                                                    else{
                                                                                        switch ($repAltAditivo[0]->caditiecrl) {
                                                                                            case 'S':
                                                                                                echo "SOLTEIRO";
                                                                                            break;
                                                                                            case 'C':
                                                                                                echo "CASADO";
                                                                                            break;
                                                                                            case 'D':
                                                                                                echo "DIVORCIADO";
                                                                                            break;
                                                                                            case 'V':
                                                                                                echo "VIÚVO";
                                                                                            break;
                                                                                            case 'O':
                                                                                                echo "OUTROS";
                                                                                            break;
                                                                                            }
                                                                                        } 
                                                                        ?>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td bgcolor="#DCEDF7">Profissão </td> 
                                                                <td class="caixaalta">
                                                                            <?php echo empty($repAltAditivo[0]->naditiprrl) ? $dadosContratos->profirepresenlegal : $repAltAditivo[0]->naditiprrl;?>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td bgcolor="#DCEDF7">E-mail </td>
                                                                <td >
                                                                          <?php echo empty($repAltAditivo[0]->naditimlrl) ? $dadosContratos->emailrepresenlegal : $repAltAditivo[0]->naditimlrl;?>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td bgcolor="#DCEDF7">Telefone(s) </td>
                                                                <td >
                                                                       <?php echo empty($repAltAditivo[0]->eadititlrl) ? $dadosContratos->telrepresenlegal : $repAltAditivo[0]->eadititlrl;?>
                                                                </td>
                                                            </tr>
                                                            <tr  bgcolor="#bfdaf2">
                                                                <th colspan="3" scope="colgroup">GESTOR</th>
                                                            </tr>
                                                            <tr>
                                                                    <td bgcolor="#DCEDF7">Nome </td>
                                                                    <td class="caixaalta">
                                                                    <?php
                                                                    if(!empty($dadosGestorConsolidado))
                                                                    {
                                                                        echo $dadosGestorConsolidado->napostnmgt;
                                                                    }
                                                                    else
                                                                    {
                                                                        echo $dadosContratos->nomegestor;
                                                                    }
                                                                    ?>
                                                               
                                                                    </td>
                                                            </tr>
                                                            <tr>
                                                                    <td bgcolor="#DCEDF7">Matrícula </td>
                                                                    <td >
                                                                    <?php 
                                                                    if(!empty($dadosGestorConsolidado))
                                                                    {
                                                                        echo $dadosGestorConsolidado->napostmtgt;
                                                                    }
                                                                    else
                                                                    {
                                                                        echo $dadosContratos->matgestor;
                                                                    }
                                                                    ?>
                                                                    </td>
                                                            </tr>
                                                            
                                                            <tr>
                                                                    <td bgcolor="#DCEDF7">CPF </td>
                                                                    <td > 
                                                                    <?php 
                                                                    if(!empty($dadosGestorConsolidado))
                                                                    {
                                                                        echo $ObjContrato->MascarasCPFCNPJ($dadosGestorConsolidado->napostcpfg);
                                                                    }
                                                                    else
                                                                    {
                                                                        echo $ObjContrato->MascarasCPFCNPJ($dadosContratos->cpfgestor);
                                                                    }
                                                                    ?>
                                                                    </td>
                                                            </tr>
                                                            
                                                            <tr>
                                                                    <td bgcolor="#DCEDF7">E-mail </td>
                                                                    <td >
                                                                    <?php 
                                                                    if(!empty($dadosGestorConsolidado))
                                                                    {
                                                                        echo $dadosGestorConsolidado->napostmlgt;
                                                                    }
                                                                    else
                                                                    {
                                                                        echo $dadosContratos->emailgestor;
                                                                    }
                                                                    ?>
                                                                    </td>
                                                            </tr>
                                                            
                                                            <tr>
                                                                    <td bgcolor="#DCEDF7">Telefone(s) </td>
                                                                    <td >
                                                                    <?php 
                                                                     if(!empty($dadosGestorConsolidado))
                                                                     {
                                                                        echo $dadosGestorConsolidado->eaposttlgt;
                                                                     }
                                                                     else
                                                                     {
                                                                        echo $dadosContratos->fonegestor;
                                                                     }
                                                                    ?>
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
                                                                                
                                                                                if(!empty($dadosFiscaisConsolidado)){
                                                                                    foreach($dadosFiscaisConsolidado as $fiscal){ 
                                                                                ?>
                                                                                <tr>
                                                                                <td><?php echo $fiscal->nfiscdtipo;?></td>
                                                                                <td><?php echo $fiscal->nfiscdnmfs;?></td>
                                                                                <td><?php echo $fiscal->efiscdmtfs;?></td>
                                                                                <td><?php echo $ObjContrato->MascarasCPFCNPJ($fiscal->cfiscdcpff);?></td>
                                                                                <td><?php echo $fiscal->nfiscdencp;?></td>
                                                                                <td><?php echo $fiscal->efiscdrgic;?></td>
                                                                                <td><?php echo strtoupper($fiscal->nfiscdmlfs);?></td>
                                                                                <!-- <td><?php echo $fiscal->nfiscdtipo;?></td> -->
                                                                                <td><?php echo $fiscal->efiscdtlfs;?></td>
                                                                                </tr>
                                                                                
                                                                                <?php 
                                                                                    }
                                                                                }
                                                                                    else if(!empty($DadosDocFiscaisFiscal)){
                                                                                    foreach($DadosDocFiscaisFiscal as $fiscal){ 
                                                                                ?>
                                                                                <tr>
                                                                                <td><?php echo $fiscal->tipofiscal;?></td>
                                                                                <td><?php echo $fiscal->fiscalnome;?></td>
                                                                                <td><?php echo $fiscal->fiscalmatricula;?></td>
                                                                                <td><?php echo $ObjContrato->MascarasCPFCNPJ($fiscal->fiscalcpf);?></td>
                                                                                <td><?php echo $fiscal->ent;?></td>
                                                                                <td><?php echo $fiscal->registro;?></td>
                                                                                <td><?php echo strtoupper($fiscal->fiscalemail);?></td>
                                                                                <!-- <td><?php echo $fiscal->nfiscdtipo;?></td> -->
                                                                                <td><?php echo $fiscal->fiscaltel;?></td>
                                                                                </tr>
                                                                                
                                                                                <?php
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
                                                                <th colspan="3" scope="colgroup">ANEXAÇÃO DE DOCUMENTO(S)</th>
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
                                                                                                    //var_dump($k);
                                                                                                    ?>
                                                                                            <tr bgcolor="#ffffff">
                                                                                                <td colspan="4"> 
                                                                                                    <a class="" href="downloadDocContratoConsolidado.php?arquivo=<?php echo $k;?>&nome=<?php echo $anexo->nome_arquivo;?>" id="documento<?php echo $k;?>" rel="<?php echo $anexo->nome_arquivo;?>"><?php echo $anexo->nome_arquivo;?></a>
                                                                                                    <!-- <input type="hidden" id="documento<?php echo $k?>"value=""> 
                                                                                                    <?php //echo $anexo->arquivo;?> -->
                                                                                                </td> <!--eliakim ramos 14082020 -->
                                                                                                <td colspan="4"> <?php echo $anexo->data_inclusao;?></td>
                                                                                            </tr>
                                                                                            <?php $k++; }
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
}else{
    if (!@require_once dirname(__FILE__) . "/TemplateAppPadrao.php") {
		throw new Exception("Error Processing Request - TemplateAppPadrao.php", 1);
	}
    $tpl = new TemplateAppPadrao("abaContratoConsolidado.html","ConsLicitacoesAndamento");
    $arrayAba = array('Contrato Consolidado', 'Contrato Original');
    if(count($AllMedicao) > 0){
        array_push($arrayAba,"Medição");
    }
    if(count($AllAditivos) > 0){
        $k=1;
        foreach($AllAditivos as $aditivo){
            array_push($arrayAba,"Aditivo ".$k);
            $k++;
        }
    }
   
    if(count($AllApostilamento) > 0){
        $k=1;
        foreach($AllApostilamento as $apostilamento){
            array_push($arrayAba,"Apostilamento  ".$k);
            $k++;
        }
    }
    $ativo =0;
    $numerotab =1;
    foreach($arrayAba as $aba){
        if($ativo == 0){
            $tpl->CLASSEATIVO = "ui-state-active";
        }else{
            $tpl->CLASSEATIVO = "";
        }
        $tpl->REFABA ="#tabContrato_00".$numerotab;
        $tpl->TITULOABA = $aba;
        $tpl->block('CONTRATO_TAB');
        $ativo++;
        $numerotab++;
    }

    $tpl->NUMEROCONTRATO = $dadosContratos->ncontrato;
    $tpl->SCC = $SCC;
    $tpl->ORIGEM = $TipoCOmpra->etpcomnome;
    $tpl->ORGAOCONTRATANTE = $dadosContratos->orgaocontratante;
    $tpl->OBJETO = $dadosContratos->objetivocontrato;
    $tpl->CNPJCONTRATADO = $ObjContrato->MascarasCPFCNPJ($cpfCNPJ);
    
    if(!empty($fornAltAditivo[0]->cnpj)){
        $tpl->CNPJCONTRATADOCONS = $ObjContrato->MascarasCPFCNPJ($fornAltAditivo[0]->cnpj);
    }else{
        $tpl->CNPJCONTRATADOCONS = $ObjContrato->MascarasCPFCNPJ($cpfCNPJ);
    }

    //$tpl->CPFCNPJ = (strlen($cpfCNPJ) == 11) ? "CPF:" : "CNPJ:" ; 
    if(!empty($fornAltAditivo[0]->cnpj) and (strlen($fornAltAditivo[0]->cnpj) > 11)){
        $tpl->CPFCNPJ = "CNPJ"; 
    }elseif(!empty($fornAltAditivo[0]->cnpj) and (strlen($fornAltAditivo[0]->cnpj) <= 11)){
        $tpl->CPFCNPJ = "CPF:";  
    }elseif(strlen($cpfCNPJ) <= 11){
        $tpl->CPFCNPJ = "CPF"; 
    }elseif(strlen($cpfCNPJ) > 11){
        $tpl->CPFCNPJ = "CNPJ"; 
    }

    if(strlen($cpfCNPJ) == 11){
        $tpl->CPFCNPJORIGINAL = "CPF"; 
    }else{
        $tpl->CPFCNPJORIGINAL = "CNPJ"; 
    }
    
    $tpl->RAZAOSOCIAL = $dadosContratos->razao;

    if(!empty($fornAltAditivo[0]->razao)){
        $tpl->RAZAOSOCIALCONS = $fornAltAditivo[0]->razao;
    }else{
        $tpl->RAZAOSOCIALCONS = $dadosContratos->razao;
    }

    $tpl->LOGRADOURO = $dadosContratos->endereco;
    if(!empty($fornAltAditivo[0]->rua)){
        $tpl->LOGRADOUROCONS = $fornAltAditivo[0]->rua;
    }else{
        $tpl->LOGRADOUROCONS = $dadosContratos->endereco;
    }
    
    $tpl->NUMERO = $dadosContratos->numerofornecedor;
    
    if(!empty($fornAltAditivo[0]->numero)){
        $tpl->NUMEROCONS = $fornAltAditivo[0]->numero;
    }else{
        $tpl->NUMEROCONS = $dadosContratos->numerofornecedor;
    }

    $tpl->COMPLEMENTO = $dadosContratos->complementofornecedor;

    if(!empty($fornAltAditivo[0]->complemento)){
    $tpl->COMPLEMENTOCONS = $fornAltAditivo[0]->complemento;
    }else{
    $tpl->COMPLEMENTOCONS = $dadosContratos->complementofornecedor;
    }

    $tpl->BAIRRO = $dadosContratos->bairrofornecedor;

    if(!empty($fornAltAditivo[0]->bairro)){
    $tpl->BAIRROCONS = $fornAltAditivo[0]->bairro;
    }else{
    $tpl->BAIRROCONS = $dadosContratos->bairrofornecedor;  
    }

    $tpl->CIDADE = $dadosContratos->cidadefornecedor;

    if(!empty($fornAltAditivo[0]->cidade)){
    $tpl->CIDADECONS = $fornAltAditivo[0]->cidade;
    }else{
    $tpl->CIDADECONS = $dadosContratos->cidadefornecedor;
    }

    $tpl->UF_FORNECEDOR = $dadosContratos->uffornecedor;

    if(!empty($fornAltAditivo[0]->estado)){
    $tpl->UF_FORNECEDORCONS = $fornAltAditivo[0]->estado;
    }else{
    $tpl->UF_FORNECEDORCONS = $dadosContratos->uffornecedor;
    }
    
    $tpl->CONSOCIO = $dadosContratos->consocio;
    $tpl->EXIBEFORNECEDOREXTRA = 'style="display:none"';
    $tpl->EXIBEFORNECEDOREXTRAORIGI = 'style="display:none"';
    if($dadosContratos->consocio == "SIM"){
        $fornecedorContrato = $ObjContrato->GetFornecedorContrato($idRegistro);
            if(!empty($fornecedorContrato)){
                $tpl->EXIBEFORNECEDOREXTRA = '';
                $tpl->EXIBEFORNECEDOREXTRAORIGI = '';
                foreach($fornecedorContrato as $fonec){
                    $tpl->RAZAOFORNECEDORESTRA = $fonec->nforcrrazs;
                    $tpl->block('FORNECEDOREXTRARAZAO');
                    $tpl->block('FORNECEDOREXTRARAZAOORIGI');
                                                            
                    $tpl->CNPJFORNECEDORESEXTRA = !empty($fonec->aforcrccpf)? $ObjContrato->MascarasCPFCNPJ($fonec->aforcrccpf):$ObjContrato->MascarasCPFCNPJ($fonec->aforcrccgc);
                    $tpl->block('FORNECEDOREXTRACNPJ');
                    $tpl->block('FORNECEDOREXTRACNPJORIGI');
                }
            }
    }else{
        ?>
        <script type="text/javascript">

          
         $(function(){

          
            
            
         $("#fornecedor").hide();
         $("#idconsorcio").hide();
         $("#razaosocialconsorcio").hide();
         $("#cnpjfornecedor").hide();
         $("#razaofornecedor").hide(); 
         
         $("#fornecedororiginal").hide(); 
         $("#idconsorciooriginal").hide(); 
         $("#razaosocialconsorciooriginal").hide();
         $("#cnpjfornecedororiginal").hide();
         $("#razaofornecedororiginal").hide();

         });
        </script>
    <?php                   
    }
    $tpl->CONTINUO = $dadosContratos->econtinuo;

    $tpl->OBRA = $dadosContratos->obra;
    $tpl->TITULORELACIONADOAOBRA = ($dadosContratos->obra == "SIM")?"Regime de Execução":"Modo de Fornecimento";
    $tpl->MODO_FORNECIMENTO = $dadosContratos->regexecoumodfornec;
    $tpl->OP_EXEC_CONTRATO = $dadosContratos->opexeccontrato == "D"?'DIAS':'MESES';
    $tpl->PRAZO_EXEC_CONTRATO = empty($dataAditivos->prazo)?$dadosContratos->prazoexec:$dataAditivos->prazo;  
    $tpl->DATAPUBLICACAODOM = DataBarra($dadosContratos->datapublic);
    $tpl->DATAVIGENCIAINI = DataBarra($dadosContratos->datainivige);
    $tpl->DATAVIGENCIAFIM = empty($dataAditivos->daditifivg)?DataBarra($dadosContratos->datafimvige):DataBarra($dataAditivos->daditifivg);
    
    $tpl->DATAEXECUCAOINI = DataBarra($dadosContratos->datainiexec);
        
    if(empty($dataAditivos)){
    
    $tpl->DATAEXECUCAOFIM = DataBarra($dadosContratos->datafimexec);
    
    }elseif(!empty($dataAditivos->data_fim_execucao)){

        $tpl->DATAEXECUCAOFIM = DataBarra($dataAditivos->data_fim_execucao);
    
    }elseif(empty($dataAditivos->data_fim_execucao)){

        $DATAEXECUCAOFIM = $ObjContrato->CalculaDataFinalDeExecucao($dataAditivos->daditiinex,$dataAditivos->cctrpcopex,$dataAditivos->prazo); 
        $DATAEXECUCAOFIM = explode('/', $DATAEXECUCAOFIM);
        $day   = $DATAEXECUCAOFIM[0];
        $month = $DATAEXECUCAOFIM[1];
        $year  = $DATAEXECUCAOFIM[2];
        $DATAEXECUCAOFIM = $day. "-". $month . "-". $year;

        $dataFimExec = new DateTime($DATAEXECUCAOFIM);
        $dataFimExec->modify('-1 day');
        // echo $dataFimExec->format('d/m/Y');
        $tpl->DATAEXECUCAOFIM = $dataFimExec->format('d/m/Y');
    }

    $tpl->DATAVIGENCIAINIORIGI = DataBarra($dadosContratos->datainivige);
    $tpl->DATAVIGENCIAFIMORIGI = DataBarra($dadosContratos->datafimvige);
    $tpl->DATAEXECUCAOINIORIGI = DataBarra($dadosContratos->datainiexec);


    $DATAEXECUCAOFIMORIGI = DataBarra($dadosContratos->datafimexec);
    $DATAEXECUCAOFIMORIGI = explode('/', $DATAEXECUCAOFIMORIGI);
        $day   = $DATAEXECUCAOFIMORIGI[0]-1;
        $month = $DATAEXECUCAOFIMORIGI[1];
        $year  = $DATAEXECUCAOFIMORIGI[2];
        $DATAEXECUCAOFIM = $day. "/". $month . "/". $year;

    $tpl->DATAEXECUCAOFIMORIGI = DataBarra($dadosContratos->datafimexec);
    
    
    if(!empty($dadosContratos->vctrpcglaa)){
        
        //$tpl->VALORGLOBALADTIVOAPOSTOLAMENTO = number_format((($valorTotal +$vtAd)+$vtAp),4,',','.');
        //$tpl->VALORGLOBALADTIVOAPOSTOLAMENTO = $objContratoFuncGeral->valorOriginal($idRegistro);
    }else{
        // $tpl->VALORGLOBALADTIVOAPOSTOLAMENTO = number_format(($valororiginal+floatval( $vtAditivo[0]->vtaditivo)+floatval($vtApost[0]->vtapost)),4,',','.');
        
        //marcello
        //$tpl->VALORGLOBALADTIVOAPOSTOLAMENTO = number_format($vtOgAdAp,4,',','.');
        //$tpl->VALORGLOBALADTIVOAPOSTOLAMENTO = $objContratoFuncGeral->valorOriginal($idRegistro);
    }


    $tpl->VALORORIGINAL = $objContratoFuncGeral->valorOriginal($idRegistro);

    $tpl->VALORGLOBALADTIVOAPOSTOLAMENTO = $objContratoFuncGeral->valorGlobal($idRegistro);

    $tpl->VALOREXECACUMULADO = $objContratoFuncGeral->valorExecutado($idRegistro);


    $tpl->SALDOAEXECUTAR = $objContratoFuncGeral->saldoAExecutar($idRegistro);
   
   
    foreach($dadosGarantia as $garantia){ 
            
        if($garantia->codgarantia == $dadosContratos->codisequtipogarantia){

             $GARANTIA = $garantia->descricaogarantia;
             $tpl->GARANTIA = $GARANTIA;
            }elseif(empty($dadosContratos->codisequtipogarantia)){
            $tpl->GARANTIA = 'SEM GARANTIA';
        }
        }

    $tpl->NUMERODEPARCELAS = $dadosContratos->numerodeparcelas;
    $tpl->VALORDAPARCELA = number_format($dadosContratos->valordaparcela,4,',','.');

     //REPRESENTE LEGAL CONTRATO CONSOLIDADO, SE NÃO TIVER ADITIVO MOSTRA O  REPESENTANTE DO CONTRATO ORIGINAL
     if(!empty($aditivo->naditinmrl)) {
        $tpl->REPRESENTANTELEGALNOMECONS = strtoupper($aditivo->naditinmrl);
        $tpl->CPFREPRESENTANTELEGALCONS = $ObjContrato->MascarasCPFCNPJ(strtoupper($aditivo->eaditicpfr));
        $tpl->CARGOREPRESENTANTELEGALCONS = strtoupper($aditivo->eaditicgrl);
        $tpl->IDENTIDADEREPRESENTANTELEGALCONS = strtoupper($aditivo->eaditiidrl);
        $tpl->ORGAOREPRESENTANTELEGALCONS = strtoupper($aditivo->naditioerl);
        $tpl->UFIDENTIDADEREPRESENTANTELEGALCONS = strtoupper($aditivo->naditiufrl);
        $tpl->CIDADEDOMREPRESENTANTELEGALCONS = strtoupper($aditivo->naditicdrl);
        $tpl->ESTADODOMREPRESENTANTELEGALCONS = strtoupper($aditivo->naditiedrl);
        $tpl->NACIONALIDADEREPRESENTANTELEGALCONS = strtoupper( $aditivo->naditinarl);
        switch (strtoupper($aditivo->caditiecrl)) {
            case 'S':
                $tpl->ESTADOCIVILREPRESENTANTELEGALCONS = strtoupper("Solteiro");
            break;
            case 'C':
                $tpl->ESTADOCIVILREPRESENTANTELEGALCONS = strtoupper("Casado");
            break;
            case 'D':
                $tpl->ESTADOCIVILREPRESENTANTELEGALCONS = strtoupper("Divorciado");
            break;
            case 'V':
                $tpl->ESTADOCIVILREPRESENTANTELEGALCONS = strtoupper("Viúvo");
            break;
            case 'O':
                $tpl->ESTADOCIVILREPRESENTANTELEGALCONS = strtoupper("Outros");
            break;
            default:
                $tpl->ESTADOCIVILREPRESENTANTELEGALCONS ="";
            break;
        }

        $tpl->PROFISSAOREPRESENTANTELIEGALCONS = strtoupper($aditivo->naditiprrl);
        $tpl->EMAILREPRESENTANTELEGALCONS = strtoupper($aditivo->naditimlrl);
        $tpl->TELEFONEREPRESENTANTELEGALCONS = strtoupper($aditivo->eadititlrl);
     }
     elseif(empty($aditivo->naditinmrl)) {    

     $tpl->REPRESENTANTELEGALNOMECONS = $dadosContratos->nomerepresenlegal;
     $tpl->CPFREPRESENTANTELEGALCONS =  $ObjContrato->MascarasCPFCNPJ($dadosContratos->cpfrepresenlegal);
     $tpl->CARGOREPRESENTANTELEGALCONS = $dadosContratos->cargorepresenlegal;
     $tpl->IDENTIDADEREPRESENTANTELEGALCONS = $dadosContratos->identidaderepreslegal;
     $tpl->ORGAOREPRESENTANTELEGALCONS = $dadosContratos->orgaoexpedrepreselegal;
     $tpl->UFIDENTIDADEREPRESENTANTELEGALCONS = $dadosContratos->ufrgrepresenlegal;
     $tpl->CIDADEDOMREPRESENTANTELEGALCONS = $dadosContratos->cidadedomrepresenlegal;
     $tpl->ESTADODOMREPRESENTANTELEGALCONS =$dadosContratos->estdomicrepresenlegal;
     $tpl->NACIONALIDADEREPRESENTANTELEGALCONS = $dadosContratos->naciorepresenlegal;
     switch ($dadosContratos->estacivilrepresenlegal) {
        case 'S':
            $tpl->ESTADOCIVILREPRESENTANTELEGALCONS = "SOLTEIRO";
        break;
        case 'C':
            $tpl->ESTADOCIVILREPRESENTANTELEGALCONS = "CASADO";
        break;
        case 'D':
            $tpl->ESTADOCIVILREPRESENTANTELEGALCONS = "DIVORCIADO";
        break;
        case 'V':
            $tpl->ESTADOCIVILREPRESENTANTELEGALCONS = "VIÚVO";
        break;
        case 'O':
            $tpl->ESTADOCIVILREPRESENTANTELEGALCONS = "OUTROS";
        break;
    }
    $tpl->PROFISSAOREPRESENTANTELIEGALCONS = $dadosContratos->profirepresenlegal;
    $tpl->EMAILREPRESENTANTELEGALCONS = $dadosContratos->emailrepresenlegal;
    $tpl->TELEFONEREPRESENTANTELEGALCONS = $dadosContratos->telrepresenlegal;
    }

     $tpl->REPRESENTANTELEGALNOME = $dadosContratos->nomerepresenlegal;
     $tpl->CPFREPRESENTANTELEGAL =  $ObjContrato->MascarasCPFCNPJ($dadosContratos->cpfrepresenlegal);
     $tpl->CARGOREPRESENTANTELEGAL = $dadosContratos->cargorepresenlegal;
     $tpl->IDENTIDADEREPRESENTANTELEGAL = $dadosContratos->identidaderepreslegal;
     $tpl->ORGAOREPRESENTANTELEGAL = $dadosContratos->orgaoexpedrepreselegal;
     $tpl->UFIDENTIDADEREPRESENTANTELEGAL = $dadosContratos->ufrgrepresenlegal;
     $tpl->CIDADEDOMREPRESENTANTELEGAL = $dadosContratos->cidadedomrepresenlegal;
     $tpl->ESTADODOMREPRESENTANTELEGAL =$dadosContratos->estdomicrepresenlegal;
     $tpl->NACIONALIDADEREPRESENTANTELEGAL = $dadosContratos->naciorepresenlegal;
     switch ($dadosContratos->estacivilrepresenlegal) {
        case 'S':
            $tpl->ESTADOCIVILREPRESENTANTELEGAL = "SOLTEIRO";
        break;
        case 'C':
            $tpl->ESTADOCIVILREPRESENTANTELEGAL = "CASADO";
        break;
        case 'D':
            $tpl->ESTADOCIVILREPRESENTANTELEGAL = "DIVORCIADO";
        break;
        case 'V':
            $tpl->ESTADOCIVILREPRESENTANTELEGAL = "VIÚVO";
        break;
        case 'O':
            $tpl->ESTADOCIVILREPRESENTANTELEGAL = "OUTROS";
        break;
    }
    $tpl->PROFISSAOREPRESENTANTELIEGAL = $dadosContratos->profirepresenlegal;
    $tpl->EMAILREPRESENTANTELEGAL = $dadosContratos->emailrepresenlegal;
    $tpl->TELEFONEREPRESENTANTELEGAL = $dadosContratos->telrepresenlegal;

    //GESTOR
    $tpl->NOMEGESTOR = $dadosContratos->nomegestor;
    $tpl->MATRICULAGESTOR = $dadosContratos->matgestor;
    $tpl->CPFGESTOR = $ObjContrato->MascarasCPFCNPJ($dadosContratos->cpfgestor);
    $tpl->EMAILGESTOR = $dadosContratos->emailgestor;
    $tpl->TELEFONEGESTOR = $dadosContratos->fonegestor;
    $tpl->SITUACAOCONTRATO = $situacaoContrato->esitdcdesc; 
    $auxAnt = array();

    //ÚLTIMO GESTOR DO CONTRATO CONSOLIDADO
    if(!empty($dadosGestorConsolidado))
    {
        //var_dump($dadosGestorConsolidado);die;
        //nome napostnmgt
        $tpl->NOMEGESTORCONS = strtoupper($dadosGestorConsolidado->napostnmgt);
        //matricula napostmtgt.
        $tpl->MATRICULAGESTORCONS = $dadosGestorConsolidado->napostmtgt;
        //CPF napostcpfg
        $tpl->CPFGESTORCONS = $ObjContrato->MascarasCPFCNPJ($dadosGestorConsolidado->napostcpfg);
        //e-mail napostmlgt
        $tpl->EMAILGESTORCONS = strtoupper($dadosGestorConsolidado->napostmlgt);
        //telefone eaposttlgt
        $tpl->TELEFONEGESTORCONS = $dadosGestorConsolidado->eaposttlgt;
    }
    //SE NÃO TIVER GESTOR NO CONTRATO CONSOLIDADO TRÁS O DO CONTRATO ORIGINAL
    else if(!empty($dadosContratos))
    {
        $tpl->NOMEGESTORCONS = strtoupper($dadosContratos->nomegestor);
        $tpl->MATRICULAGESTORCONS = $dadosContratos->matgestor;
        $tpl->CPFGESTORCONS = $ObjContrato->MascarasCPFCNPJ($dadosContratos->cpfgestor);
        $tpl->EMAILGESTORCONS = strtoupper($dadosContratos->emailgestor);
        $tpl->TELEFONEGESTORCONS = $dadosContratos->fonegestor;
    }

    if(!empty($dadosFiscaisConsolidado)){
        foreach($dadosFiscaisConsolidado as $fiscal){ 
            $tpl->TIPOFISCALCONSOLIDADO = $fiscal->nfiscdtipo;
            $tpl->NOMEFISCALCONSOLIDADO = $fiscal->nfiscdnmfs;
            $tpl->MATRICULAFISCALCONSOLIDADO = $fiscal->efiscdmtfs;
            $tpl->CPFFISCALCONSOLIDADO = $ObjContrato->MascarasCPFCNPJ($fiscal->cfiscdcpff);
            $tpl->ENTIDADECOMPETENTEFISCALCONSOLIDADO = $fiscal->nfiscdencp;
            $tpl->RESGISTROINSCRICAOCONSOLIDADO = $fiscal->efiscdrgic;
            $tpl->EMAILFISCALCONSOLIDADO = strtoupper($fiscal->nfiscdmlfs);
            $tpl->TELEFONEFISCALCONSOLIDADO = $fiscal->efiscdtlfs;
            $tpl->block('FISCAL');
            }
        }
        else if(!empty($DadosDocFiscaisFiscal)){
        foreach($DadosDocFiscaisFiscal as $fiscal){ 
            $tpl->TIPOFISCALCONSOLIDADO = $fiscal->tipofiscal;
            $tpl->NOMEFISCALCONSOLIDADO = $fiscal->fiscalnome;
            $tpl->MATRICULAFISCALCONSOLIDADO = $fiscal->fiscalmatricula;
            $tpl->CPFFISCALCONSOLIDADO = $ObjContrato->MascarasCPFCNPJ($fiscal->fiscalcpf);
            $tpl->ENTIDADECOMPETENTEFISCALCONSOLIDADO = $fiscal->ent;
            $tpl->RESGISTROINSCRICAOCONSOLIDADO = $fiscal->registro;
            $tpl->EMAILFISCALCONSOLIDADO = strtoupper($fiscal->fiscalemail);
            $tpl->TELEFONEFISCALCONSOLIDADO = $fiscal->fiscaltel;
            $tpl->block('FISCAL');
          }
        }

        if(!empty($DadosDocFiscaisFiscal)){
            foreach($DadosDocFiscaisFiscal as $fiscal){ 
                $tpl->TIPOFISCAL = $fiscal->tipofiscal;
                $tpl->NOMEFISCAL = $fiscal->fiscalnome;
                $tpl->MATRICULAFISCAL = $fiscal->fiscalmatricula;
                $tpl->CPFFISCAL = $ObjContrato->MascarasCPFCNPJ($fiscal->fiscalcpf);
                $tpl->ENTIDADECOMPETENTEFISCAL = $fiscal->ent;
                $tpl->RESGISTROINSCRICAO = $fiscal->registro;
                $tpl->EMAILFISCAL = strtoupper($fiscal->fiscalemail);
                $tpl->TELEFONEFISCAL = $fiscal->fiscaltel;
                $tpl->block('FISCALORIGINAL');
              }
            }
        if(!empty($DadosDocAnexo)){
        $k=0;
        foreach($DadosDocAnexo as $key => $anexo){ 
            $_SESSION['arquivo_download'][$k] = $anexo->arquivo;
            $tpl->NOMEARQUIVO  = $anexo->nome_arquivo;
            $tpl->DATAINCLUSAO = $anexo->data_inclusao;
            $tpl->CONTATORK = $k;
            $tpl->block("DOCUMENTOANEXO");
            $k++;
        }
        foreach($dadosAnexoAux as $key => $anexo){ 
            $_SESSION['arquivo_download'][$k] = $anexo->arquivo;
            $tpl->NOMEARQUIVO  = $anexo->nome_arquivo;
            $tpl->DATAINCLUSAO = $anexo->data_inclusao;
            $tpl->CONTATORK = $k;
            $tpl->block("DOCUMENTOANEXOORIGINAL");
            $k++;
        }
    }

    $valorFullTotal = 0;
    if(!empty($dadosItensContrato)) {   
        foreach($dadosItensContrato as $itens){
            $codeMaterial = !empty($itens->codreduzidomat)?$itens->codreduzidomat:$itens->codreduzidoserv;
            $tipoGrupo = !empty($itens->codreduzidomat)?'M':"S";
            $tpl->ORDITEM = $itens->ord;
            $dadosDaDescricao = '';
             if(!empty($itens->codreduzidomat)){
                $descricaoMaterial= $ObjContrato->GetDescricaoMaterial($itens->codreduzidomat);
                $dadosDaDescricao.=$descricaoMaterial[0]->ematepdesc;
              }
              if(!empty($itens->codreduzidoserv)){
                $descricaoServico = $ObjContrato->GetDescricaoServicos($itens->codreduzidoserv);
                $dadosDaDescricao.=$descricaoServico[0]->eservpdesc;
              }
            $tpl->DESC_ITEM =$dadosDaDescricao;
            $tpl->COD_REDU_ITEM = !empty($itens->codreduzidoserv)?$itens->codreduzidoserv:$itens->codreduzidomat;
            $tpl->UNDITEM = !empty($itens->codreduzidoserv)?"":'und';
            $tpl->QTDITEM= number_format($itens->qtd,4,',','.');
            $tpl->VALOR_UNITARIO_ITEM = number_format($itens->valorunitario,4,',','.');
            $valor = floatval($itens->qtd) * floatval($itens->valorunitario);
            $valorFullTotal += $valor;
            $tpl->VALOR_TOTAL_ITEM = number_format($valor,4,',','.');
            $tpl->TIPOITEM= !empty($itens->codreduzidoserv)?"Serviço":"Material";
            $tpl->block("ITEMCONTRATO");
        }
    }else{
          //Não há item associado ao contrato
    } 
    $tpl->TOTALITENS = number_format($valorFullTotal,4,',','.');
    if(count($AllMedicao) > 0){
        $numerotab = 4;
    }else{
        $numerotab = 3;
    }
    // var_dump($AllAditivos);die;
    if(count($AllAditivos) > 0){
        $supressaoouacrescimo = "";
        foreach($AllAditivos as $aditivo){
            $dadosTiposAdtivo = $objConstratoConsolidado->GetDescricaoTipoAditivo($aditivo->ctpadisequ);
            $display = '';
            if($aditivo->ctpadisequ == 13){
                $dadosFornecedorAdit = $objConstratoConsolidado->getFornecedorCredenciado($aditivo->aforcrsequ);
                $cpf_cpnj = $ObjContrato->MascarasCPFCNPJ(!empty($dadosFornecedorAdit[0]->aforcrccpf)?$dadosFornecedorAdit[0]->aforcrccpf:$dadosFornecedorAdit[0]->aforcrccgc);
                $countCpf = $cpf_cpnj;
                $countCpf = preg_replace( '/[^0-9]/is', '', $countCpf );
                    if (strlen($countCpf) != 11) {
                        $validaCpfCnpj = false;
                    }else{
                        $validaCpfCnpj = true;
                    }
                if($validaCpfCnpj == true)
                {
                    $prefixCpfCnpj =   'CPF do Contratado: ';
                }
                else {
                    $prefixCpfCnpj =   'CNPJ do Contratado: ' ;
                }
                if(empty($aditivo->aforcrsequ)){
                    $existeFornecedor = True;
                }
            }
               
              
                $complementoAdit = !is_null($dadosFornecedorAdit[0]->eforcrcomp)? $dadosFornecedorAdit[0]->eforcrcomp : '' ;
                $complementoAdit = ($complemento == 'NULL')?$complemento = '' : $complemento = $complemento;
                if($aditivo->ctpadisequ != 13) { $display = 'style="display:none;';}else{ $display = '';}
                $tpl->NOMEFORN_ADIT = !is_null($dadosFornecedorAdit[0]->nforcrrazs)? $dadosFornecedorAdit[0]->nforcrrazs : '' ;
                
                $tpl->cpf_cnpj_adit =  $prefixCpfCnpj;
                $tpl->CPF_CNPJFORN_ADIT =  $cpf_cpnj;
                $tpl->LOGRAFORN_ADIT = !is_null($dadosFornecedorAdit[0]->eforcrlogr)? $dadosFornecedorAdit[0]->eforcrlogr : '' ;
                $tpl->UFORN_ADIT = !is_null($dadosFornecedorAdit[0]->caditiesta)? $dadosFornecedorAdit[0]->caditiesta : '' ;
                $tpl->COMPLEFORN_ADIT =  $complementoAdit;
                $tpl->CIDFORN_ADIT = !is_null($dadosFornecedorAdit[0]->nforcrcida)? $dadosFornecedorAdit[0]->nforcrcida : '' ;
                $tpl->BAIRROFORN_ADIT = !is_null($dadosFornecedorAdit[0]->eforcrbair)? $dadosFornecedorAdit[0]->eforcrbair : '' ;
        
            $tpl->DISPLAY_STYLEADIT =  $display;

            $tpl->TABID = "tabContrato_00".$numerotab;
            $tpl->NUMEROADITIVO = $aditivo->aaditinuad;
            $tpl->TIPOADITIVO = $dadosTiposAdtivo->etpadidesc;

            $tpl->JUSTIFICATIVAADITIVO = strtoupper($aditivo->xaditijust);
            $tpl->ISAJUSTEDEPRAZO = $aditivo->faditialpz;

            $tpl->TEMALTERACAOVALOR = $aditivo->faditialvl;
            if(!empty($aditivo->aaditipeac)){
                $supressaoouacrescimo = $aditivo->aaditipeac;
            }else if(!empty($aditivo->aaditipesu)){
                $supressaoouacrescimo = $aditivo->aaditipesu;
            }
            $datafimcalculada = "";
            if(!empty($aditivo->daditiinex)){
                $datauaxExec = explode("-",$aditivo->daditiinex);
                $datafimcalculada = date("d/m/Y", mktime(0, 0, 0, $datauaxExec[1], $datauaxExec[2]+$aditivo->aaditiapea, $datauaxExec[0]));
            }
            $tpl->ACRESCIMODOPRAZO = !empty($aditivo->aaditiapea)?$aditivo->aaditiapea:"";
            
            if($tpl->ISAJUSTEDEPRAZO == "NAO"){
     
                $tpl->DATAINIVIGENCIA = $aditivo->daditiinvg = " ";
                $tpl->DATAFIMVIGENCIA = $aditivo->daditifivg = " ";
                $tpl->DATAINIEXEC = $aditivo->daditiinex = " ";
           
            }else{

                $tpl->DATAINIVIGENCIA = DataBarra($aditivo->daditiinvg);
                $tpl->DATAFIMVIGENCIA = DataBarra($aditivo->daditifivg);
                $tpl->DATAINIEXEC = DataBarra($aditivo->daditiinex);

            }

            if(!empty($aditivo->daditifiex)){
                $tpl->DATAFIMEXEC = DataBarra($aditivo->daditifiex);
            
            }else if($tpl->ISAJUSTEDEPRAZO == "NAO"){

                $tpl->DATAFIMEXEC = $aditivo->daditifiex = " ";

            }else{
                if(!empty($aditivo->daditiinex)){
                $DATAFIMEXEC = $ObjContratoManter->CalculaDataFinalDeExecucao($aditivo->daditiinex,$dadosContratos->opexeccontrato,$aditivo->aaditiapea);                        
                $DATAFIMEXEC = explode('/', $DATAFIMEXEC);
                    $day   = $DATAFIMEXEC[0]-1;
                    $month = $DATAFIMEXEC[1];
                    $year  = $DATAFIMEXEC[2];
                    $DATAFIMEXEC = $day. "-". $month . "-". $year;

                    $dataFimExec = new DateTime($DATAFIMEXEC);
                    $dataFimExec->modify('-1 day');
                    // echo $dataFimExec->format('d/m/Y');
                    $DATAFIMEXEC = $dataFimExec->format('d/m/Y');
                    $tpl->DATAFIMEXEC = $DATAFIMEXEC;
                }
            }

            $tpl->VALORTOTALADITIVO = ($aditivo->vaditivtad)?number_format($aditivo->vaditivtad,4,',','.'):"0,0000";

            $tpl->NOMEREPRESENTANTEADV = strtoupper($aditivo->naditinmrl);
            $tpl->CPFREPRESENTANTEADV = $ObjContrato->MascarasCPFCNPJ(strtoupper($aditivo->eaditicpfr));
            $tpl->CARGOREPRESENTANTEADV = strtoupper($aditivo->eaditicgrl);
            $tpl->RGREPRESENTANTEADV = strtoupper($aditivo->eaditiidrl);
            $tpl->ORGAOREPRESENTANTEADV = strtoupper($aditivo->naditioerl);
            $tpl->UFREPRESENTANTEADV = strtoupper($aditivo->naditiufrl);
            $tpl->CIDADEREPRESENTANTEADV = strtoupper($aditivo->naditicdrl);
            $tpl->UFDOMREPRESENTANTEADV = strtoupper($aditivo->naditiedrl);
            $tpl->OBSERVACAOADV = strtoupper($aditivo->xaditiobse);
            $tpl->NACIONALIDADEREPRESENTANTEADV =strtoupper( $aditivo->naditinarl);
            switch (strtoupper($aditivo->caditiecrl)) {
                case 'S':
                    $tpl->ESTADOCIVILREPRESENTANTEADV = strtoupper("Solteiro");
                break;
                case 'C':
                    $tpl->ESTADOCIVILREPRESENTANTEADV = strtoupper("Casado");
                break;
                case 'D':
                    $tpl->ESTADOCIVILREPRESENTANTEADV = strtoupper("Divorciado");
                break;
                case 'V':
                    $tpl->ESTADOCIVILREPRESENTANTEADV = strtoupper("Viúvo");
                break;
                case 'O':
                    $tpl->ESTADOCIVILREPRESENTANTEADV = strtoupper("Outros");
                break;
                default:
                    $tpl->ESTADOCIVILREPRESENTANTEADV ="";
                break;
            }
            if(!empty($aditivo->fase) && intval($aditivo->fase) == 4){
                $tpl->FASEEXECUCAOADV = "EM EXECUÇÃO";
            }else{
                $tpl->FASEEXECUCAOADV = "CADASTRADO";
            }
            $tpl->PROFISSAOREPRESENTANTEADV = strtoupper($aditivo->naditiprrl);
            $tpl->EMAILREPRESENTANTEADV = strtoupper($aditivo->naditimlrl);
            $tpl->TELEFONEREPRESENTANTEADV = strtoupper($aditivo->eadititlrl);
            
            $DadosDocAnexoAditivo = $objConstratoConsolidado->GetDocumentosAnexosAdtivo($aditivo->cdocpcseq1,$aditivo->aaditinuad);
           if(!empty($DadosDocAnexoAditivo)){
                foreach($DadosDocAnexoAditivo as $key => $anexo){ 
                    $k = $anexo->aaditinuad;
                    $_SESSION['arquivo_download'][$k] = $anexo->arquivo;
                    $tpl->NOMEARQUIVOADV  = $anexo->nomearquivo;
                    $tpl->DATAINCLUSAOADV = $anexo->datacadasarquivo;
                    $tpl->CONTATORKADV = $k;
                    $tpl->block("DOCUMENTOANEXOADV");
                }
            }
            $tpl->block('ADITIVOTAB');
            $numerotab++;
        }
    }
    if(count($AllApostilamento) > 0){
        $supressaoouacrescimo = "";
        foreach($AllApostilamento as $apostilamento){

           
            $dadosTiposApostilamento = $objConstratoConsolidado->GetDescricaoTipoApostilamento($apostilamento->ctpaposequ);
            if(!empty($apostilamento->aforcrsequ)){
                $dadosFornecedor = $objConstratoConsolidado->getFornecedorCredenciado($apostilamento->aforcrsequ);
            }
            $cpf_cpnj = $ObjContrato->MascarasCPFCNPJ(!empty($dadosFornecedor[0]->aforcrccpf)?$dadosFornecedor[0]->aforcrccpf:$dadosFornecedor[0]->aforcrccgc);
            $countCpf = $cpf_cpnj;
            $countCpf = preg_replace( '/[^0-9]/is', '', $countCpf );
                if (strlen($countCpf) != 11) {
                    $validaCpfCnpj = false;
                }else{
                    $validaCpfCnpj = true;
                }
            if($validaCpfCnpj == true)
            {
                $prefixCpfCnpj =   'CPF do Contratado: ';
            }
            else {
                $prefixCpfCnpj =   'CNPJ do Contratado: ' ;
            }

            $complemento = !is_null($dadosFornecedor[0]->eforcrcomp)? $dadosFornecedor[0]->eforcrcomp : '' ;
            $complemento = ($complemento == 'NULL')?$complemento = '' : $complemento = $complemento;
            $sequencialApost = ($apostilamento->ctpaposequ != 5) ? $display = 'style="display:none;"': $display = '';
            $tpl->NOMEFORN_APOST = !is_null($dadosFornecedor[0]->nforcrrazs)? $dadosFornecedor[0]->nforcrrazs : '' ;
            $tpl->DISPLAY_STYLE =  $display;
            $tpl->cpf_cnpj =  $prefixCpfCnpj;
            $tpl->CPF_CNPJFORN_APOST =  $cpf_cpnj;
            $tpl->LOGRAFORN_APOST = !is_null($dadosFornecedor[0]->eforcrlogr)? $dadosFornecedor[0]->eforcrlogr : '' ;
            $tpl->UFORN_APOST = !is_null($dadosFornecedor[0]->caditiesta)? $dadosFornecedor[0]->caditiesta : '' ;
            $tpl->COMPLEFORN_APOST =  $complemento;
            $tpl->CIDFORN_APOST = !is_null($dadosFornecedor[0]->nforcrcida)? $dadosFornecedor[0]->nforcrcida : '' ;
            $tpl->BAIRROFORN_APOST = !is_null($dadosFornecedor[0]->eforcrbair)? $dadosFornecedor[0]->eforcrbair : '' ;
            $dadosFiscalApostilamento = $ObjContrato->getFiscalApostilamento($apostilamento->cdocpcsequ);
            $tpl->TABIDAP = "tabContrato_00".$numerotab;
            $tpl->NUMEROAPOSTILAMENTO = $apostilamento->aapostnuap;
            $tpl->TIPOAPOSTILAMENTO = $dadosTiposApostilamento->etpapodesc;
            $tpl->VALORRETROATIVOAPOSTILAMENTO = ($apostilamento->vapostretr)?number_format($apostilamento->vapostretr,4,',','.'):"0,0000";
            $tpl->NOMEGESTORAPOSTILAMENTO = strtoupper($apostilamento->napostnmgt);
            $tpl->MATRICULAGESTORAPOSTILAMENTO = $apostilamento->napostmtgt;
            $tpl->CPFGESTORAPOSTILAMNETO = $ObjContrato->MascarasCPFCNPJ(!empty($apostilamento->napostcpfg)?$apostilamento->napostcpfg:"");
            $tpl->EMAILGESTORAPOSTILAMENTO = strtoupper($apostilamento->napostmlgt);
            $tpl->TELEFONEGESTORAPOSTILAMENTO = $apostilamento->eaposttlgt;
            $tpl->DATAINIEXEC = DataBarra($apostilamento->daditiinex);

            $tpl->DATAFIMEXEC = DataBarra($apostilamento->daditifiex);
            
            $tpl->VALORTOTALAPOSTILAMENTO = ($apostilamento->vapostvtap)?number_format($apostilamento->vapostvtap,4,',','.'):"0,0000";
            $tpl->STATUSDOAPOSTILAMENTO = $apostilamento->esitdcdesc;
            $dadosAlteradosDoc = $ObjContrato->GetDocumentosAnexosApostilamentoAlterado($idRegistro);
          
            /*if(!empty($dadosAlteradosDoc)){
                $DadosDocAnexoApost = $ObjContrato->GetDocumentosAnexosApostilamentoAlterado($apostilamento->cdocpcseq2, $apostilamento->aapostnuap);
            }else{
                $DadosDocAnexoApostilamento = $objConstratoConsolidado->GetDocumentosAnexosApostilamento($apostilamento->cdocpcseq2, $apostilamento->aapostnuap);
            }*/

            //BUSCA ANEXOS POR APOSTILAMENTO   
            $DadosDocAnexoApostilamento = $objConstratoConsolidado->GetDocumentosAnexosApostilamento($apostilamento->cdocpcseq2, $apostilamento->aapostnuap);
        
            if(!empty($dadosFiscalApostilamento)){
                foreach($dadosFiscalApostilamento as $fiscal){ 
                    $tpl->TIPOFISCALAPOSTILAMENTO = $fiscal->tipofiscal;
                    $tpl->NOMEFISCALAPOSTILAMENTO = $fiscal->fiscalnome;
                    $tpl->MATRICULAFISCALAPOSTILAMENTO = $fiscal->fiscalmatricula;
                    $tpl->CPFFISCALAPOSTILAMENTO = $ObjContrato->MascarasCPFCNPJ($fiscal->fiscalcpf);
                    $tpl->ENTIDADECOMPETENTEFISCALAPOSTILAMENTO = $fiscal->ent;
                    $tpl->RESGISTROINSCRICAOAPOSTILAMENTO = $fiscal->registro;
                    $tpl->EMAILFISCALAPOSTILAMENTO = strtoupper($fiscal->fiscalemail);
                    $tpl->TELEFONEFISCALAPOSTILAMENTO = $fiscal->fiscaltel;
                    $tpl->block('FISCALAPOSTILAMENTO');
                }
            }

    if(!empty($DadosDocAnexoApostilamento)){
        foreach($DadosDocAnexoApostilamento as $key => $anexo){ 
            $k = $anexo->sequdocumento . $anexo->sequdocanexo;
            $_SESSION['arquivo_download'][$k] = $anexo->arquivo;
            $tpl->NOMEARQUIVOAPOS  = $anexo->nomearquivo;
            $tpl->DATAINCLUSAOAPOS = $anexo->datacadasarquivo;
            $tpl->CONTATORKAPOS = $k;
            $tpl->block("ARQUIVOSAPOSTILAMENTO");
            }
        }
        $tpl->block('APOSTILAMENTOTAB');
        $numerotab++;
        }
    }   
    if(count($AllMedicao) > 0){
        $tpl->IDTABMEDICAO = "tabContrato_003";
        $tpl->EXIBEMEDICAO = "";
        $tpl->VALORTOTALMEDICAO = $valorTotalMedicao;
        foreach($AllMedicao as $medicao){
            $dtInicial = explode("-", $medicao->dmedcoinic);
            $dtInicial = array_reverse($dtInicial);
            $dtInicial = $dtInicial[0] . "/" . $dtInicial[1] . "/" . $dtInicial[2];
            $dtFinal = explode("-", $medicao->dmedcofinl);
            $dtFinal = array_reverse($dtFinal);
            $dtFinal = $dtFinal[0] . "/" . $dtFinal[1] . "/" . $dtFinal[2];
            if($medicao->vmedcovalm != 0){
                $valorMedicao = number_format((floatval($medicao->vmedcovalm)),4,',','.');
            } else{
                $valorMedicao = number_format((floatval('0')),4,',','.');
            }
           /* if(!empty($medicao->dmedcopapr)){
                $status = "APROVADO";
            }else{
                $status = "CADASTRADO";
            }*/
        
            $tpl->PERIODODAMENDICAO = $dtInicial . " à " . $dtFinal ;
            $tpl->NUMEROMEDICAO = str_pad($medicao->amedconume, 4 , '0' , STR_PAD_LEFT);
            $tpl->VALORDAMEDICAO = $valorMedicao;
            //$tpl->STATUSDEMEDICAO = $status;
            $tpl->block("DADOSMEDICAO");
        }
    }else{
        $tpl->EXIBEMEDICAO = 'style="display:none;"';
        $tpl->IDTABMEDICAO = "";
    }
    $tpl->show();
}
    exit;
}
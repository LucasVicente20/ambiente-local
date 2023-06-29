<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: AbaContratoManterEspecial.php
# Autor:    Eliakim Ramos | João Madson
# Data:     10/12/2019
# Objetivo: Programa de incluir contrato
#-------------------------------------------------------------------------
# Exibe Aba Membro de Comissão - Formulário A #
function ExibeAbaContratoManterEspecial(){ 
    $ObjContrato = new ContratoManterEspecial();
    $ObjContratoInc = new Contrato();
    $objMedicao = new ClassMedicao();
    $dadosContratos = (object) array();
    $dadosGarantia = $ObjContrato->GetListaGarantiaDocumento();
    $tiposCompra = $ObjContratoInc->get_tipoCompraSemParametro();
    $orgaosLicitantes = $ObjContrato->GetOrgao();
    $idRegistro    = '';
    if ($_SERVER['REQUEST_METHOD'] == "POST") {
       if(!empty($_POST['idregistro'])){
            $_SESSION['idregistro'] = $_POST['idregistro'];
       }
        $idRegistro = !empty($_POST['idregistro'])? $_POST['idregistro']:$_SESSION['idregistro'];
        $csolcosequ ="";
        if(!empty($_POST['seqScc'])){
            $_SESSION['csolcosequ'] = $_POST['seqScc'];
            $csolcosequ = $_SESSION['csolcosequ'];
            $aforcrsequ = $_SESSION["fornsequ".$csolcosequ];
            $corglicodi = $_SESSION["org".$csolcosequ];
            $cctrpciden = $_SESSION["flagCPFPJ".$csolcosequ];
        }else{
            $csolcosequ = $_SESSION['csolcosequ'];
            $aforcrsequ = $_SESSION["fornsequ".$csolcosequ];
            $corglicodi = $_SESSION["org".$csolcosequ];
            $cctrpciden = $_SESSION["flagCPFPJ".$csolcosequ];
        }
        
        if(!empty($csolcosequ) && !empty($aforcrsequ)){
            $_SESSION['dadosContratado']  = $ObjContratoInc->getFornecedorDados($aforcrsequ);
            $_SESSION['dadosObjOrgao']    = $ObjContratoInc->GetOrgaoEDescObj($csolcosequ);
            $dadosSalvar['csolcosequ'] = $csolcosequ;
            $dadosSalvar['aforcrsequ'] = $aforcrsequ;
         }
         //Inicio da coleta dos dados selecionados em pesquisa via post  MADSON 
         if(!is_null($_POST['sccselec-'.$_POST['seqScc']])){
             $_SESSION['origemScc']       = $ObjContratoInc->corrigeString($_POST['origselec-'.$_POST['seqScc']]);
             $_SESSION['numScc']          = $_POST['sccselec-'.$_POST['seqScc']];
             $_SESSION['CpfCnpj']         = $_POST['cpfselec-'.$_POST['seqScc']];
         }
         $dadosSalvar['origemScc'] = $_SESSION['origemScc'];
         $dadosSalvar['numScc']    = $_SESSION['numScc']   ;
         $dadosSalvar['CpfCnpj']   = $_SESSION['CpfCnpj']  ;
         
         // Este if se encarrega de adicionar os campos abaixo no template caso a função objeto que é obrigatorio seja valida
         if(!is_null($dadosSalvar['numScc'])){
             $origemScc    =$dadosSalvar['origemScc'];
             $numScc       =$dadosSalvar['numScc'];
             $CpfCnpj      = $dadosSalvar['CpfCnpj'];
         }else{
             $origemScc    = '';
             $numScc       = '';
             $CpfCnpj      = "";
         }
         //Fim

         
        $orgLicitante = $_SESSION['dadosObjOrgao']->eorglidesc; //Usar para mostrar na tela qual deles é e para a Masc
        $objetoDesc   = $_SESSION['dadosObjOrgao']->esolcoobje;    
        $razSocial    = $_SESSION['dadosContratado']->nforcrrazs;
        $Rua          = $_SESSION['dadosContratado']->eforcrlogr;
        $numEnd       = $_SESSION['dadosContratado']->aforcrnume;
        $complEnd     = $_SESSION['dadosContratado']->eforcrcomp;
        $Bairro       = $_SESSION['dadosContratado']->eforcrbair;
        $UF           = $_SESSION['dadosContratado']->cforcresta;
        $Cidade       = $_SESSION['dadosContratado']->nforcrcida;
        $Cep          =  $_SESSION['dadosContratado']->cceppocodi;
        $telefone   = $_SESSION['dadosContratado']->aforcrtels;
         
        $dadosSalvar['corglicodi']      = $corglicodi;
        $dadosSalvar['ectrpcobje']      = $ObjContratoInc->corrigeString($objetoDesc)  ;
        $dadosSalvar['ectrpcraza']      = $ObjContratoInc->corrigeString($razSocial)   ;
        $dadosSalvar['ectrpclogr']      = $ObjContratoInc->corrigeString($Rua)         ;
        $dadosSalvar['actrpcnuen']      = $ObjContratoInc->corrigeString($numEnd)      ;
        $dadosSalvar['ectrpccomp']      = $ObjContratoInc->corrigeString($complEnd)    ;
        $dadosSalvar['ectrpcbair']      = $ObjContratoInc->corrigeString($Bairro)      ;
        $dadosSalvar['cctrpcesta']      = $ObjContratoInc->corrigeString($UF)          ;
        $dadosSalvar['nctrpccida']      = $ObjContratoInc->corrigeString($Cidade)      ;
        $dadosSalvar['ectrpctlct']      = $_SESSION['dadosContratado']->aforcrtels;  //telefone do contratado para inserir em tbcontratosfpc
        $dadosSalvar['cctrpcccep']      = $_SESSION['dadosContratado']->cceppocodi;  //CEP do contratado para inserir em tbcontratosfpc
         $_SESSION['dadosSalvar'] = $dadosSalvar;
         $valorCalculado = 0;
         if(!empty($_POST['seqScc'])){
                $valoresItems = $ObjContrato->selectsContratoIncluir($dadosSalvar);
                for($it = 0;  $it < count($valoresItems); $it++){
                    if($dadosSalvar['origemScc'] == "LICITAÇÃO"){
                       $valorUnitário = $valoresItems[$it]->vitelpvlog;
                        $quantItem     = $valoresItems[$it]->aitelpqtso;
                    }else{
                        $valorUnitário = $valoresItems[$it]->vitescunit;
                        $quantItem     = $valoresItems[$it]->aitescqtso;
                    }   
                      $valorCalculado += (floatval($valorUnitário) * floatval($quantItem));
                }
                
                $dadosSalvar['vctrpcvlor'] = $valorCalculado;
         }
        $dadosContratos     = $ObjContrato->GetDadosContratoSelecionado($idRegistro);
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
        $nomeBotao = "Salvar";
        $botaoNomeSalvarAnexo = 'false';
        $nomeBotaoEncerrar = "Encerrar";
        $funcaoEncerrar = 'encerraContrato()';
        $exibeBotao = '';
        $exibeBotaoEncerramento = '';
        $exibeLupa = "";
        $funcaoExcluir = "excluirContrato()";
        $funcaoCancelar = "cancelarContrato()";
               $_SESSION['Botao'] = $_POST['Botao'];
        $SCC            = "";
        if(!empty($dadosContratos->orgao) && !empty($dadosContratos->unidade) && !empty($dadosContratos->codisolicitacao) && !empty($dadosContratos->anos)){
            $SCC       = sprintf('%02s', $dadosContratos->orgao) . sprintf('%02s', $dadosContratos->unidade) . '.' . sprintf('%04s', $dadosContratos->codisolicitacao) . '/' . $dadosContratos->anos;
        }
        $TipoCOmpra    = $ObjContrato->GetTipoCompra($dadosContratos->codicompra);
        $cpfCNPJ        = (!empty($dadosContratos->cnpj))?$dadosContratos->cnpj:$dadosContratos->cpf;
        $DadosDocFiscaisFiscal = array();
            $CNPJ_CPF            = !empty($_POST['CNPJ_CPF']) ? $_POST['CNPJ_CPF']:1;
            
            if ($_POST['CnpjCpf'] != "") {
                if ($CNPJ_CPF == 2) {
                    $CnpjCpf = str_replace('.', '', str_replace('-', '', $_POST['CnpjCpf']));
                } else {
                    $CnpjCpf = str_replace('.', '', str_replace('-', '', str_replace('/', '', $_POST['CnpjCpf'])));
                   
                }
            } else {
                $CnpjCpf = $_POST['CnpjCpf'];
                //die('3');
            }
        $_SESSION['bloqueiaCampo'] = $bloqueiacampo;
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
         function TestaCPF(strCPF) {
            let Soma;
            let Resto;
            Soma = 0;
            strCPF = strCPF.replace(/[^\d]+/g,'');
            console.log(strCPF);
            if (strCPF == "00000000000") return false;
            if (strCPF == "11111111111") return false;
            if (strCPF == "22222222222") return false;
            if (strCPF == "33333333333") return false;
            if (strCPF == "44444444444") return false;
            if (strCPF == "55555555555") return false;
            if (strCPF == "66666666666") return false;
            if (strCPF == "77777777777") return false;
            if (strCPF == "88888888888") return false;
            if (strCPF == "99999999999") return false;
                
            for (i=1; i<=9; i++) Soma = Soma + parseInt(strCPF.substring(i-1, i)) * (11 - i);
            Resto = (Soma * 10) % 11;
            
                if ((Resto == 10) || (Resto == 11))  Resto = 0;
                if (Resto != parseInt(strCPF.substring(9, 10)) ) return false;
            
            Soma = 0;
                for (i = 1; i <= 10; i++) Soma = Soma + parseInt(strCPF.substring(i-1, i)) * (12 - i);
                Resto = (Soma * 10) % 11;
            
                if ((Resto == 10) || (Resto == 11))  Resto = 0;
                if (Resto != parseInt(strCPF.substring(10, 11) ) ) return false;
                return true;
        }
        function avisoModal(mensagem){
                 $("#tdmensagemM").show();
                $('html, body').animate({scrollTop:0}, 'slow');
				 $(".mensagem-textoM").html(mensagem);
        }
        function AbreJanela(url,largura,altura) {
            window.open(url,'detalhe','status=no,scrollbars=yes,left=70,top=130,width='+largura+',height='+altura);
        }
        function Submete(Destino){
            if(Destino = 'B'){
                $("#op").val('mantemDadosPost');
                $.post("postDadosManter.php",$("#FormContrato").serialize(), function(data){
                    // const response = JSON.parse(data);             
                });
            }
            document.CadContratoManter.Destino.value = Destino;
            document.CadContratoManter.submit();
        }
        function Dialog(mensagem){
            document.querySelector('#mensagemAlert').innerHTML=mensagem;
            document.querySelector('dialog').open=true;
        }
        function enviar(){
            const btnSalvarContrato = $("#btnSalvarContrato").val();
            const botaoNomeSalvarAnexo = $("#botaoNomeSalvarAnexo").val();
            const anexoInseridoOuRetirado = $("#anexoInseridoOuRetirado").val();
            if(btnSalvarContrato == "Salvar" && botaoNomeSalvarAnexo == 'false'){
                if(!TestaCPF($("#cpfrepresenlegal").val())){
                    avisoexclusao("Informe: Um CPF válido para o representante.");
                    return false;
                }
                if(!TestaCPF($("#cpfgestor").val())){
                    avisoexclusao("Informe: Um CPF válido para o gestor.");
                    return false;
                }
                $.post("postDadosManter.php",$("#FormContrato").serialize(), function(data){
                    const response = JSON.parse(data);
                    if(response.status){
                        window.location.href = "./CadContratoPesquisar.php?m="+response.msm+"&h=show";
                    }else if(!response.status){
                        $("#tdmensagem").show();
                        let elmnt = document.querySelector("body");
                              elmnt.scrollTop = -1000;
                              elmnt.scrollLeft = -1000;
					    $(".mensagem-texto").html(response.msm);
                    }
                    
			    });
            }else if(btnSalvarContrato == "Salvar" && botaoNomeSalvarAnexo == 'true' && anexoInseridoOuRetirado == 'true'){
                     $("#op").val('UpdateContratoAnexo');
                    $.post("postDadosManter.php",$("#FormContrato").serialize(), function(data){
                     const response = JSON.parse(data);
                        if(response.status){
                            window.location.href = "./CadContratoPesquisar.php?m="+response.msm+"&h=show";
                        }else if(!response.status){
                            $("#tdmensagem").show();
                            let elmnt = document.querySelector("body");
                                elmnt.scrollTop = -1000;
                                elmnt.scrollLeft = -1000;
                            $(".mensagem-texto").html(response.msm);
                        }
                     
			        });
            }
        }
        function avisoexclusao(mensagem){
                 $("#tdmensagem").show();
                 $('html, body').animate({scrollTop:0}, 'slow');
				 $(".mensagem-texto").html(mensagem);
        }
        function limpaMensagem(){
            $("#tdmensagem").hide();
            $("#tdmensagemM").hide();
        }
        function excluirContrato(){
           if(confirm("Deseja realmente excluir esse contrato?")){
                const codSequCont = $("#idRegistro").val();
                $.post("postDadosManter.php",{"codContrato":codSequCont, "op":"ExcluirContrato"}, function(data){
                        const response = JSON.parse(data);
                        if(response.status){
                            window.location.href = "./CadContratoPesquisar.php?m="+response.msm+"&h=show";
                        }else if(!response.status){
                            $("#tdmensagem").show();
                            let elmnt = document.querySelector("body");
                                elmnt.scrollTop = -1000;
                                elmnt.scrollLeft = -1000;
                            $(".mensagem-texto").html(response.msm);
                        }
                        
                });
           }
        }
        function cancelarContrato(){
           if(confirm("Deseja realmente cancelar esse contrato?")){
                const codSequCont = $("#idRegistro").val();
                $.post("postDadosManter.php",{"codContrato":codSequCont, "op":"CancelarContrato"}, function(data){
                        const response = JSON.parse(data);
                        if(response.status){
                            window.location.href = "./CadContratoPesquisar.php?m="+response.msm+"&h=show";
                        }else if(!response.status){
                            $("#tdmensagem").show();
                            let elmnt = document.querySelector("body");
                                elmnt.scrollTop = -1000;
                                elmnt.scrollLeft = -1000;
                            $(".mensagem-texto").html(response.msm);
                        }
                        
                });
           }
        }
        function encerraContrato(){
           if(confirm("Deseja realmente encerrar esse contrato?")){
                const codSequCont = $("#idRegistro").val();
                $.post("postDadosManter.php",{"codContrato":codSequCont, "op":"EncerrarContrato"}, function(data){
                        const response = JSON.parse(data);
                        if(response.status){
                            window.location.href = "./CadContratoPesquisar.php?m="+response.msm+"&h=show";
                        }else if(!response.status){
                            $("#tdmensagem").show();
                            let elmnt = document.querySelector("body");
                                elmnt.scrollTop = -1000;
                                elmnt.scrollLeft = -1000;
                            $(".mensagem-texto").html(response.msm);
                        }
                        
                });
           }
        }
        function desfazEncerramentoContrato(){
           if(confirm("Deseja realmente desfazer o encerramento desse contrato?")){
                const codSequCont = $("#idRegistro").val();
                $.post("postDadosManter.php",{"codContrato":codSequCont, "op":"DesfazerEncerramentoContrato"}, function(data){
                        const response = JSON.parse(data);
                        if(response.status){
                            window.location.href = "./CadContratoPesquisar.php?m="+response.msm+"&h=show";
                        }else if(!response.status){
                            $("#tdmensagem").show();
                            let elmnt = document.querySelector("body");
                                elmnt.scrollTop = -1000;
                                elmnt.scrollLeft = -1000;
                            $(".mensagem-texto").html(response.msm);
                        }
                        
                });
           }
        }
        function desfazEncerramentoDiretoContrato(){
                const codSequCont = $("#idRegistro").val();
                $.post("postDadosManter.php",{"codContrato":codSequCont, "op":"DesfazerEncerramentoContrato"}, function(data){
                        const response = JSON.parse(data);
                        if(response.status){
                            window.location.href = "./CadContratoPesquisar.php?m="+response.msm+"&h=show";
                        }else if(!response.status){
                            $("#tdmensagem").show();
                            let elmnt = document.querySelector("body");
                                elmnt.scrollTop = -1000;
                                elmnt.scrollLeft = -1000;
                            $(".mensagem-texto").html(response.msm);
                        }
                        
                });
        }
        function enviarDestino(valor, Destino){
            document.CadPregaoPresencialSessaoPublica.Destino.value = Destino;
            document.CadPregaoPresencialSessaoPublica.Botao.value = valor;
            document.CadPregaoPresencialSessaoPublica.submit();
        }
        function retiraFornecedor(dado){
            $.post("postDadosManter.php",{op:"ExcluirForneModal",info:dado}, function(data){
                    ObjJson = JSON.parse(data);
                    $(".dadosFornec").html(CriatableModal(ObjJson));
                    $("#shownewfornecedores").html(CriatableView(ObjJson));
			 });
        }
        function CriatableModal(objJson){
                    tabelaHtml = '<table border="1" bordercolor="#75ADE6">';
                    tabelaHtml += '<thead>';
                    tabelaHtml += '<tr>';
                    tabelaHtml += '<td>';
                    tabelaHtml += 'IDENTIFICADOR DO CONTRATO';
                    tabelaHtml += '</td>';
                    tabelaHtml += '<td>';
                    tabelaHtml += 'RAZÃO SOCIAL';
                    tabelaHtml += '</td>';
                    tabelaHtml += '<td>';
                    tabelaHtml += '';
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
                                tabelaHtml +=  (objJson[i].aforcrccpf != null)?objJson[i].aforcrccpf:objJson[i].aforcrccgc;
                                tabelaHtml += '</td>';
                                tabelaHtml += '<td>';
                                tabelaHtml += objJson[i].nforcrrazs;
                                tabelaHtml += '</td>';
                                tabelaHtml += '<td>';
                                tabelaHtml += '<button type="button" onclick="retiraFornecedor(\''+objJson[i].aforcrsequ+'\')"><img src="../midia/excluirfornec.png" alt="Excluir" /> </button>';
                                tabelaHtml += '</td>';
                                tabelaHtml += '</tr>';
                            }
                        }
                    }
                    tabelaHtml += '</tbody>';
                    tabelaHtml += '</table>';
                return tabelaHtml;
        }
        function CriatableView(objJson){
                    tabelaHtml = '<table border="1"  class="textonormal"  bordercolor="#75ADE6">';
                    tabelaHtml += '<thead>';
                    tabelaHtml += '<tr bgcolor="#DCEDF7" style="font-weight: bold;">';
                    tabelaHtml += '<td>';
                    tabelaHtml += 'IDENTIFICADOR DO CONTRATADO';
                    tabelaHtml += '</td>';
                    tabelaHtml += '<td>';
                    tabelaHtml += 'RAZÃO SOCIAL';
                    tabelaHtml += '</td>';
                    tabelaHtml += '</tr>';
                    tabelaHtml += '</thead>';
                    tabelaHtml += '<tbody>';
                    for(i in objJson){
                        if(objJson[i].aforcrsequ){
                            if(objJson[i].remover == 'N'){
                                tabelaHtml += '<tr>';
                                tabelaHtml += '<td>';
                                tabelaHtml += '<input type="hidden" name="codFornecedorModalPesquisa[]" value="'+objJson[i].aforcrsequ+'">';
                                tabelaHtml +=  (objJson[i].aforcrccpf != null)?objJson[i].aforcrccpf:objJson[i].aforcrccgc;
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
        function CriaTabelaFiscal(objJson){
            
            tabelaHtml = '<table border="1" width="100%" bordercolor="#75ADE6" class="textonormal">';
                    tabelaHtml += '<thead>';
                    tabelaHtml += '<tr> <td  class="titulo3" colspan="9"  align="center"  bgcolor="#75ADE6">RESULTADO DA PESQUISA</td></tr>';
                    tabelaHtml += '<tr style="background-color: #bfdaf2; text-align: center; font-weight: bold; color: #3165a5;">';
                    tabelaHtml += '<td>';
                    tabelaHtml += '</td>';
                    tabelaHtml += '<td>';
                    tabelaHtml += 'TIPO FISCAL';
                    tabelaHtml += '</td>';
                    tabelaHtml += '<td>';
                    tabelaHtml += 'NOME';
                    tabelaHtml += '</td>';
                    tabelaHtml += '<td>';
                    tabelaHtml += 'MATRÍCULA';
                    tabelaHtml += '</td>';
                    tabelaHtml += '<td>';
                    tabelaHtml += 'CPF';
                    tabelaHtml += '</td>';
                    tabelaHtml += '<td>';
                    tabelaHtml += 'ENT. COMPET.';
                    tabelaHtml += '</td>';
                    tabelaHtml += '<td>';
                    tabelaHtml += 'REGISTRO OU INSC.';
                    tabelaHtml += '</td>';
                    tabelaHtml += '<td>';
                    tabelaHtml += 'E-MAIL';
                    tabelaHtml += '</td>';
                    tabelaHtml += '<td>';
                    tabelaHtml += 'TEL.';
                    tabelaHtml += '</td>';
                    tabelaHtml += '</tr>';
                    tabelaHtml += '</thead>';
                    tabelaHtml += '<tbody>';
                    for(i in objJson){
                        if(objJson[i].cfiscdcpff){
                            tabelaHtml += '<tr>';
                            tabelaHtml += '<td>';
                            tabelaHtml += '<input type="radio" name="cpfFiscal" value="'+objJson[i].cfiscdcpff+'">';
                            tabelaHtml += '</td>';
                            tabelaHtml += '<td>';
                            tabelaHtml +=  (objJson[i].nfiscdtipo != '')?objJson[i].nfiscdtipo:'';
                            tabelaHtml += '</td>';
                            tabelaHtml += '<td>';
                            tabelaHtml +=  (objJson[i].nfiscdnmfs != '')?objJson[i].nfiscdnmfs:'';
                            tabelaHtml += '</td>';
                            tabelaHtml += '<td>';
                            tabelaHtml +=  (objJson[i].efiscdmtfs != '')?objJson[i].efiscdmtfs:'';
                            tabelaHtml += '</td>';
                            tabelaHtml += '<td>';
                            tabelaHtml +=  (objJson[i].cpfformatado != '')?objJson[i].cpfformatado.toUpperCase():'';
                            tabelaHtml += '</td>';
                            tabelaHtml += '<td>';
                            tabelaHtml +=  (objJson[i].nfiscdencp != '')?objJson[i].nfiscdencp:'';
                            tabelaHtml += '</td>';
                            tabelaHtml += '<td>';
                            tabelaHtml +=  (objJson[i].efiscdrgic != '')?objJson[i].efiscdrgic:'';
                            tabelaHtml += '</td>';
                            tabelaHtml += '<td>';
                            tabelaHtml +=  (objJson[i].nfiscdmlfs != '')?objJson[i].nfiscdmlfs.toUpperCase():'';
                            tabelaHtml += '</td>';
                            tabelaHtml += '<td>';
                            tabelaHtml +=  (objJson[i].efiscdtlfs != '')?objJson[i].efiscdtlfs:'';
                            tabelaHtml += '</td>';
                            tabelaHtml += '</tr>';
                        }
                    }
                    tabelaHtml += '</tbody>';
                    tabelaHtml += '<tfoot>';
                    tabelaHtml += '<tr>';
                    tabelaHtml += '<td colspan="9">';
                    tabelaHtml += '<input  type="button" name="excluir" id="btnExcluirModal" value="Excluir" style="float:right" title="Excluir" class="botao_excluir botao">';
                    tabelaHtml += '<input  type="button" name="alterar" id="btnAlterarModal" value="Alterar" style="float:right" title="Alterar" class="botao_Alterar botao">';
                    tabelaHtml += '<input  type="button" name="adicionarFiscal" id="btnAdicionarFiscalModal" value="Criar Novo Fiscal" style="float:right" title="Adicionar" class="botao_final botao">';
                    tabelaHtml += '<input  type="button" name="newselect" id="btnNewSelectModal" value="Selecionar" style="float:right;" onclick="SelecionarFiscalModal()" title="Selecionar" class="botao_New_Selecionar botao">';
                    tabelaHtml += '</td>';
                    tabelaHtml += '</tr>';
                    tabelaHtml += '</tfoot>';
                    tabelaHtml += '</table>';
                return tabelaHtml;
        }
        function CriaTabelaFiscalView(objJson){
            tabelaHtml = '';
            var arrayAux = new Array();
                    for(i in objJson){
                        if(objJson[i].fiscalcpf){
                             if(objJson[i].remover == "N"){ 
                                 if(arrayAux.indexOf(objJson[i].fiscalcpf) == -1){   
                                    tabelaHtml += '<tr>';
                                    tabelaHtml += '<td>';
                                    tabelaHtml += '<input type="radio" name="fiscais" value="'+objJson[i].fiscalcpf+'-'+objJson[i].docsequ+'">';
                                    tabelaHtml += '</td>';
                                    tabelaHtml += '<td>';
                                    tabelaHtml +=  (objJson[i].tipofiscal != '')?objJson[i].tipofiscal:'';
                                    tabelaHtml += '</td>';
                                    tabelaHtml += '<td>';
                                    tabelaHtml +=  (objJson[i].fiscalnome != '')?objJson[i].fiscalnome:'';
                                    tabelaHtml += '</td>';
                                    tabelaHtml += '<td>';
                                    tabelaHtml +=  (objJson[i].fiscalmatricula != '')?objJson[i].fiscalmatricula:'';
                                    tabelaHtml += '</td>';
                                    tabelaHtml += '<td>';
                                    tabelaHtml +=  (objJson[i].fiscalcpf != '')?objJson[i].fiscalcpf:'';
                                    tabelaHtml += '</td>';
                                    tabelaHtml += '<td>';
                                    tabelaHtml +=  (objJson[i].registro != '')?objJson[i].registro:'';
                                    tabelaHtml += '</td>';
                                    tabelaHtml += '<td>';
                                    tabelaHtml +=  (objJson[i].ent != '')?objJson[i].ent:'';
                                    tabelaHtml += '</td>';
                                    tabelaHtml += '<td>';
                                    tabelaHtml +=  (objJson[i].fiscalemail != '')?objJson[i].fiscalemail.toUpperCase():'';
                                    tabelaHtml += '</td>';
                                    tabelaHtml += '<td>';
                                    tabelaHtml +=  (objJson[i].fiscaltel != '')?objJson[i].fiscaltel:'';
                                    tabelaHtml += '</td>';
                                    tabelaHtml += '</tr>';
                                 }
                             }
                        }
                        arrayAux.push(objJson[i].fiscalcpf);
                    }
                return tabelaHtml;
        }
        function SelecionarFiscalModal(){
            const Doc = "<?php echo $dadosContratos->seqdocumento;?>";
            const cpf = $("input[name='cpfFiscal']:checked").val();
                $.post("postDadosManter.php",{op:"SelecFiscal",cpf:cpf,doc:Doc}, function(data){
                    ObjJson = JSON.parse(data);
                     $("#mostrartbfiscais").html(CriaTabelaFiscalView(ObjJson));
                     $("#cpffiscal").removeAttr('disabled');
                     $("#cpffiscal").val('');
                     $("input[name='tipofiscalr']").removeAttr('disabled');
                     $("#btnNewPesquisaModal").hide();
                     $("#btnPesquisaModal").show();
                     $(".dadosFiscal").html('');
                     $("#modal").hide();
			    });
        }
        //funcão que faz a funcão do botao do iframe
        function Subirarquivo(){
                window.top.frames['frameArquivo'].subirArquivo();
        }

        function verificaContrato(){
                const contrato = $("#numcontrato").val();
                const idregistro=$("input:[name=idregistro]").val();
                if(contrato !=""){
                    $.post("postDadosManter.php",{op:"VerificaSeTemNumeroContrato",'numcon':contrato,'idContrato':idregistro},function(data){
                                const objJson = JSON.parse(data);
                                if(objJson.status == false){
                                    avisoexclusao(objJson.msm);
                                    retorno = false;
                                    return retorno;
                                }else{
                                    limpaMensagem();
                                    retorno =  true;
                                    return retorno;
                                }
                    });
                }else{
                    retorno =false;
                    return retorno;
                }
        }
        function verificaContratoComSCC(){
                const scc = $("#solicitacaoCompra").val();
                const idregistro=$("input:[name=idregistro]").val();
                let retorno = true;
                if(scc !=""){
                    $.post("postDadosManter.php",{op:"VerificaSeTemNumeroContratoComSCC",'idContrato':idregistro},function(data){
                                const objJson = JSON.parse(data);
                                if(objJson.status == false){
                                    avisoexclusao(objJson.msm);
                                    retorno = false;
                                }else{
                                    limpaMensagem();
                                    retorno =  true;
                                }
                    });
                }else{
                    retorno =false;
                }
                return retorno;
        }
        function buscaFornecedor(){
            const numcpfcnpj = $("#CnpjCpf_forn").val();
            if(numcpfcnpj == ""){
                alert("Infome os dados da pesquisa de fornecedor correta");
            }
            const flagcpfcnpj = document.querySelector('input[name=CNPJ_CPF]:checked').value != null ? document.querySelector('input[name=CNPJ_CPF]:checked').value : "";
         
            if(numcpfcnpj != ""){
                $.post("postDadosManter.php",{op:"buscaFornecedor",'CPFCNPJ':numcpfcnpj, 'flagCpfCnpj':flagcpfcnpj},function(data){
                    const objJson = JSON.parse(data);
                   
                    if(objJson.status == false){
                        avisoexclusao(objJson.msm);
                        return false;
                    }else{ //madson
                        $('#labelCPFCNPJCont').html(numcpfcnpj);
                        $('#_razaoSocialfornecedor').html(objJson.RazaoSocial);
                        $('#_logradourofornecedor').html(objJson.logradouro);
                        $('#_numerofornecedor').html(objJson.numero);
                        $('#_complementoLogradourofornecedor').html(objJson.complemento);
                        $('#_bairrofornecedor').html(objJson.bairro);
                        $('#_cidadefornecedor').html(objJson.cidade);
                        $('#_estadofornecedor').html(objJson.estado);

                        $('input[name=CpfCnpContratado]').val(numcpfcnpj);
                        $('input[name=ectrpcraza]').val(objJson.RazaoSocial);
                        $('input[name=ectrpclogr]').val(objJson.logradouro);
                        $('input[name=actrpcnuen]').val(objJson.numero);
                        $('input[name=ectrpccomp]').val(objJson.complemento);
                        $('input[name=cctrpcccep]').val(objJson.cep);
                        $('input[name=ectrpcbair]').val(objJson.bairro);
                        $('input[name=ectrpctlct]').val(objJson.telefone);
                        $('input[name=nctrpccida]').val(objJson.cidade);
                        $('input[name=cctrpcesta]').val(objJson.estado);
                        $('input[name=aforcrsequCont]').val(objJson.aforcrsequ); // chave que define se vem da pesquisa pra salvar |Madson
                       
                       return true;    
                    }
                });
            }
        }

        $(document).ready(function() {
            var radio_cnpj_cpf = $("input[name='CNPJ_CPF']:checked").val();
            if(radio_cnpj_cpf == 1){
                $('#CnpjCpf_forn').mask('99.999.999/9999-99');
            }else if(radio_cnpj_cpf == 2){
                $('#CnpjCpf_forn').mask('999.999.999-99');
            }
            $("input[name='CNPJ_CPF']").on('click', function(){
                var radio_cnpj_cpf = $("input[name='CNPJ_CPF']:checked").val();
                if(radio_cnpj_cpf == 1){
                    $('#CnpjCpf_forn').mask('99.999.999/9999-99');
                }else if(radio_cnpj_cpf == 2){
                    $('#CnpjCpf_forn').mask('999.999.999-99');
                }
            });
            $('#numcontrato').mask('9999.9999/9999');
            $('#cpfrepresenlegal').mask('999.999.999-99');
            $('#cpfgestor').mask('999.999.999-99');
            $('#cnpj').mask('99.999.999/9999-99');
            $('#cpf').mask('999.999.999-99');
            $('#cpffiscal').mask('999.999.999-99');
            // $('.telefone').mask('(99)99999-9999');
            
            $("input.telefone")
            .mask("(99) 9999-9999?9")
            .focusout(function (event) {  
                var target, phone, element;  
                target = (event.currentTarget) ? event.currentTarget : event.srcElement;  
                phone = target.value.replace(/\D/g, '');
                element = $(target);  
                element.unmask();  
                if(phone.length > 10) {  
                    console.log(phone.length);
                    element.mask("(99) 99999-999?9");  
                } else {  
                    console.log(phone.length);
                    element.mask("(99) 9999-9999?9");  
                }  
            });

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
                     selectHtml += '<?php $dadosContratos->regexecoumodfornec = !empty($_SESSION['dadosManter']['cmb_regimeExecucaoModoFornecimento1']) ? $_SESSION['dadosManter']['cmb_regimeExecucaoModoFornecimento1']: $dadosContratos->regexecoumodfornec; ?>';
                     selectHtml += '<option  <?php echo $dadosContratos->regexecoumodfornec == "PRECO GLOBAL"?'selected="selected"':''; ?> value="PRECO GLOBAL">EMPREITADA POR PREÇO GLOBAL</option>';
                     selectHtml += '<option <?php echo $dadosContratos->regexecoumodfornec == "EMPREITADA POR PRECO UNITARIO"?'selected="selected"':''; ?> value="EMPREITADA POR PRECO UNITARIO">EMPREITADA POR PREÇO UNITÁRIO</option>';
                     selectHtml += '<option <?php echo $dadosContratos->regexecoumodfornec == "TAREFA"?'selected="selected"':''; ?> value="TAREFA">TAREFA</option>';
                     selectHtml += '<option <?php echo $dadosContratos->regexecoumodfornec == "EMPREITADA INTEGRAL"?'selected="selected"':''; ?> value="EMPREITADA INTEGRAL">EMPREITADA INTEGRAL</option>';
                
                 $("#modoFornec").hide();
                 $("#regimeExec").show();
                 $("#cmb_regimeExecucaoModoFornecimento1").html(selectHtml);
            }
            if($("#obra1").prop("checked")){
                var selectHtml = '<option  value=""></option>';
                     selectHtml += '<?php $dadosContratos->regexecoumodfornec = !empty($_SESSION['dadosManter']['cmb_regimeExecucaoModoFornecimento1']) ? $_SESSION['dadosManter']['cmb_regimeExecucaoModoFornecimento1']: $dadosContratos->regexecoumodfornec; ?>';
                     selectHtml += '<option  <?php echo $dadosContratos->regexecoumodfornec == "INTEGRAL"?'selected="selected"':''; ?> value="INTEGRAL">INTEGRAL</option>';
                     selectHtml += '<option <?php echo $dadosContratos->regexecoumodfornec == "PARCELADO"?'selected="selected"':''; ?> value="PARCELADO">PARCELADO</option>';
                
                 $("#modoFornec").show();
                 $("#regimeExec").hide();
                 $("#cmb_regimeExecucaoModoFornecimento1").html(selectHtml);
            }
            $("#btnvoltar").on('click', function(){
                window.location.href = "./CadContratoPesquisar.php";
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
                    $(".modal-content").attr("style","min-height: 93px;width: 533px;");
                    $("#modal").show();
                    $.post("postDadosManter.php",{op:"ExibeFornecedorExtra",idregistro:$("input:[name=idregistro]").val()}, function(data){
                        ObjJson = JSON.parse(data);
                            // $(".dadosFornec").html(CriatableModal(ObjJson));
			        });
			    });
            });
            $("#manterfiscal").on('click', function(){
                $.post("postDadosManter.php",{op:"modalFiscal"}, function(data){
                    $(".modal-content").html(data);
                    $(".modal-content").attr("style","min-height: 105px;width: 1100px;");
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
            $("#obra0").on('click', function(){
                 var selectHtml = '<option  value=""></option>';
                     selectHtml += '<?php $dadosContratos->regexecoumodfornec = !empty($_SESSION['dadosManter']['cmb_regimeExecucaoModoFornecimento1']) ? $_SESSION['dadosManter']['cmb_regimeExecucaoModoFornecimento1']: $dadosContratos->regexecoumodfornec; ?>';
                     selectHtml += '<option  <?php echo $dadosContratos->regexecoumodfornec == "PRECO GLOBAL"?'selected="selected"':''; ?> value="PRECO GLOBAL">EMPREITADA POR PREÇO GLOBAL</option>';
                     selectHtml += '<option <?php echo $dadosContratos->regexecoumodfornec == "EMPREITADA POR PRECO UNITÁRIO"?'selected="selected"':''; ?> value="EMPREITADA POR PRECO UNITÁRIO">EMPREITADA POR PREÇO UNITÁRIO</option>';
                     selectHtml += '<option <?php echo $dadosContratos->regexecoumodfornec == "TAREFA"?'selected="selected"':''; ?> value="TAREFA">TAREFA</option>';
                     selectHtml += '<option <?php echo $dadosContratos->regexecoumodfornec == "EMPREITADA INTEGRAL"?'selected="selected"':''; ?> value="EMPREITADA INTEGRAL">EMPREITADA INTEGRAL</option>';

                 $("#modoFornec").hide();
                 $("#regimeExec").show();
                 $("#cmb_regimeExecucaoModoFornecimento1").html(selectHtml);
            });
            $("#obra1").on('click', function(){
                 var selectHtml = '<option  value=""></option>';
                     selectHtml += '<?php $dadosContratos->regexecoumodfornec = !empty($_SESSION['dadosManter']['cmb_regimeExecucaoModoFornecimento1']) ? $_SESSION['dadosManter']['cmb_regimeExecucaoModoFornecimento1']: $dadosContratos->regexecoumodfornec; ?>';
                     selectHtml += '<option  <?php echo $dadosContratos->regexecoumodfornec == "INTEGRAL"?'selected="selected"':''; ?> value="INTEGRAL">INTEGRAL</option>';
                     selectHtml += '<option <?php echo $dadosContratos->regexecoumodfornec == "PARCELADO"?'selected="selected"':''; ?> value="PARCELADO">PARCELADO</option>';
                
                 $("#modoFornec").show();
                 $("#regimeExec").hide();
                 $("#cmb_regimeExecucaoModoFornecimento1").html(selectHtml);
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
                        // $(".modal-content").attr("style","min-height: 25%;width: 79%;");
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
                    $(".modal-content").attr("style","min-height: 105px;width: 1100px;");
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
                if(docanexselec != undefined){
                    $.post("postDadosManter.php",{op:"ModalAlterarFiscal",marcador:docanexselec},function(data){
                        $(".modal-content").attr("style","min-height: 105px;width: 1100px;");
                        $(".modal-content").html(data);
                        $("#modal").show();
                    });
                }else{
                    avisoModal("Selecione fiscal do contrato.");
                    $('div, .modal-body').animate({scrollTop:0}, 'slow');
                }
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
                    console.log(cpfFiscal);
            });
            $(".btn-pesquisa-scc").on('click', function(){
                $.post("postDados.php",{op:"modalSccPesquisa"}, function(data){
                    $(".modal-content").html(data);
                    $(".modal-content").attr("style","min-height: 130px;width: 853px;");
                    $(".modal-body").attr("style","min-height: 130px;width: 853px;");
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
                // $(".modal-content").attr("style","min-height: 25%;width: 64%;");
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
    </style>
    <body background="../midia/bg.gif" marginwidth="0" marginheight="0">
        <dialog>
                <span id="mensagemAlert"></span><br>
                <button class="botao" onclick="desfazEncerramentoDiretoContrato()">Sim</button>
                <button class="botao" onclick="document.querySelector('dialog').open=false">Não</button>
        </dialog>
    <form action="CadContratoManter.php" method="post" id="FormContrato" name="CadContratoManter">
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
            <table cellpadding="3" class="textonormal" summary="">
                <!-- Caminho -->
                <tr>
                    <td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
                    <td align="left" class="textonormal" colspan="2">
                        <font class="titulo2">|</font>
                        <a href="../index.php"><font color="#000000">Página Principal</font></a> > Contratos > Manter Especial
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

                <!-- Corpo -->
                <tr>
                    <td width="150"></td>
                    <td class="textonormal">
                        <table  border=1px bordercolor="#75ADE6" cellspacing="0" cellpadding="3" summary=""  width="1024px" >
                             <thead colspan="3" class="titulo3 itens_material" align="center"  bgcolor="#75ADE6" valign="middle">
                                <td> 
                                    MANTER CONTRATO ESPECIAL
                                </td>
                              </thead>
                            <tr>
                                <td class="textonormal">
                                
                                    <table id="scc_material" summary="" width="100%" class="textonormal">
                                        <tbody border="0">
                                            <tr>
                                                <td align="left" colspan="4" > 
                                                    <!-- <table id="scc_material" summary="" bordercolor="#75ADE6"  style="border: 3px solid #75ade6; border-radius: 4px;" width="100%"> -->
                                                        
                                                            <?php echo NavegacaoAbasManter(on,off); ?>                                                  
                                                            <tr>
                                                                <td  bgcolor="#DCEDF7">Número do Contrato/Ano:</td>
                                                                <td >
                                                                    <input id="numcontrato" type="text" name="numcontrato" class="numeroContrato" size="11" style="font-size: 10.6667px;" <?php echo $bloqueiacampo?'disabled="disabled"':'';?> value="<?php echo !empty($_SESSION['dadosManter']['numcontrato'])?$_SESSION['dadosManter']['numcontrato']:$dadosContratos->ncontrato;?>" onblur="verificaContrato()" maxlength="20" size="10">
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td bgcolor="#DCEDF7" >Órgão Contratante Responsável:</td>
                                                                <td class="inputs">
                                                                    <?php
                                                                    //     if(!empty($numScc) || !empty($SCC)){
                                                                    ?>
                                                                            <!-- <span id="orgao" class="textonormal"><?php echo //!empty($orgLicitante)?$orgLicitante:$dadosContratos->orgaocontratante;?></span> -->
                                                                    <?php   
                                                                      //  }else{
                                                                    ?>
                                                                                <select id="selectContrato" name="orgao_licitante" <?php echo $bloqueiacampo?'disabled="disabled"':'';?> size="1" title="Orgão Licitante" style="width:625px; font-size: 10.6667px; ">	
                                                                                    <option class="textonormal" style="font-size: 10.6667px;" value="" ></option>
                                                                                    <?php foreach($orgaosLicitantes as $orgao){ 
                                                                                        if(!empty($_SESSION['dadosManter']['orgao_licitante'])){
                                                                                            $selecionadoOrgao = ($orgao->corglicodi == $_SESSION['dadosManter']['orgao_licitante']) ? 'selected="selected"' : ''; 
                                                                                        }else{
                                                                                            $selecionadoOrgao = ($orgao->corglicodi == $dadosContratos->codorgao) ? 'selected="selected"' : '';     
                                                                                        }
                                                                                    ?>
                                                                                    <option class="textonormal" style="font-size: 10.6667px;" value="<?php echo $orgao->corglicodi;?>" <?php echo $selecionadoOrgao; ?> >
                                                                                        <?php echo $orgao->eorglidesc;?>
                                                                                    </option>
                                                                                <?php } ?>
                                                                                </select>                                                                    
                                                                    <?php
                                                                        //}
                                                                    ?>
                                                                </td>                 
                                                            </tr>
                                                            <tr>
                                                                <td colspan="4" align="right">
                                                                    <input type="button" value="<?php echo  $nomeBotao;?>" <?php echo $exibeBotao;?>  class="botao" id="btnSalvarContrato"onclick="javascript:enviar('A');">
                                                                    <input type="hidden" value="<?php echo $botaoNomeSalvarAnexo;?>" id="botaoNomeSalvarAnexo">
                                                                    <input type="button" value="Excluir " <?php echo $exibeBotao;?>  onclick="<?php echo  $funcaoExcluir;?>" class="botao" id="btnexcluir">
                                                                    <input type="button" value="Cancelar " <?php echo $exibeBotao;?>  onclick="<?php echo  $funcaoCancelar;?>" class="botao" id="btncancelar">
                                                                    <input type="button" value="<?php echo $nomeBotaoEncerrar;?> "  <?php echo $exibeBotaoEncerramento;?>  onclick="<?php echo  $funcaoEncerrar;?>" class="botao" id="btnencerrar">
                                                                    <input type="button" value="Voltar" class="botao" id="btnvoltar">
                                                                    <input type="hidden" name="Botao" value="">
                                                                    <input type="hidden" name="Origem" value="A">
                                                                    <input type="hidden" name="Destino">
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                        
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
        <div class="modal" id="modal"> 
            <div class="modal-content">
            
            </div>
        </div> 
        <!-- Fim Modal -->
        <br>
    </body>
</html>
<?php
    exit;
}

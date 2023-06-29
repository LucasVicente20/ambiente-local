<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: AbaContratoIncluir.php
# Autor:    Eliakim Ramos | João Madson
# Data:     10/12/2019
# Objetivo: Programa de incluir contrato
#-------------------------------------------------------------------------
# Autor:    Madson Felix
# Data:     28/04/2021
# CR #246939
# -------------------------------------------------------------------------
# Autor: Marcello Albuquerque
# Data:  29/09/2021
# Objetivo: CR #253475
# -------------------------------------------------------------------------
# Alterado : Osmar Celestino
# Data: 22/02/2022
# Objetivo: CR #259193
#---------------------------------------------------------------------------
# Alterado : Lucas Vicente 
# Data: 09/05/2022
# Objetivo: CR #263119
#---------------------------------------------------------------------------
# Alterado : Lucas Vicente 
# Data: 16/05/2022
# Objetivo: CR #263182
#---------------------------------------------------------------------------
# Alterado : Lucas Vicente
# Data: 24/05/2022
# Objetivo: CR #263119
#---------------------------------------------------------------------------
# Autor: Lucas Vicente
# Data:  15/02/2023
# Objetivo: CR #275671
# -------------------------------------------------------------------------

require_once "./ClassContratos.php";

session_start();

# Exibe Aba Membro de Comissão - Formulário A #
function ExibeAbaContratoIncluir(){ 
    
    $ObjContrato = new Contrato();
    if ($_SERVER['REQUEST_METHOD'] == "GET") {
        unset($_SESSION['dadosTabela']);
        unset($_SESSION['fiscal_selecionado_incluir']);
        unset($_SESSION['documento_anexo_incluir']);
        unset($_SESSION['dadosFornecedor']);
        unset($_SESSION['postObjDescAlterado']);
        unset($_SESSION['citelpnuml']);
    }
    if ($_SERVER['REQUEST_METHOD'] == "POST") {
        $seqSccPost = $_POST['seqScc'];
        for($s=0;$s<count($seqSccPost);$s++){
            $tiratracoscc[$s] = explode('-', $seqSccPost[$s]); //esse post trás, alem da scc, o lote. [0] SCC, [1] Lote
        }
        // Busca de dados do fornecedor 
        if(($_SESSION['csolcosequ'] != $tiratracoscc[0][0] && !empty($tiratracoscc[0][0] )) && $_SESSION['visitouItem'] != true){
            unset($_SESSION['dadosFornecedor']);
            unset($_SESSION['visitouItem']);
            unset($_SESSION['postObjDescAlterado']);
            unset($_SESSION['citelpnuml']);
        }else{
            unset($_SESSION['visitouItem']);
        }
        if(!empty($tiratracoscc[0][0] )){
            $_SESSION['csolcosequ'] = $tiratracoscc[0][0];
            unset($_SESSION['citelpnuml']);
            for($i=0;$i<count($tiratracoscc);$i++){
               $_SESSION['citelpnuml'][$i] = $tiratracoscc[$i][1]; //adaptar 
            }
            $csolcosequ = $_SESSION['csolcosequ'];
            $citelpnuml = $_SESSION['citelpnuml'];
            // $aforcrsequ = $_SESSION["fornsequ".$csolcosequ];
            $aforcrsequ = $_POST["fornecedorSCC"];
            if(!empty($_POST["fornecedorSCC"])){
               $_SESSION["fornsequ".$csolcosequ] = $_POST["fornecedorSCC"];
            }
            $corglicodi = $_SESSION["org".$csolcosequ];
            $cctrpciden = $_SESSION["flagCPFPJ".$csolcosequ];
        }else{
            $csolcosequ = $_SESSION['csolcosequ'];
            $citelpnuml = $_SESSION['citelpnuml'];
            $aforcrsequ = $_SESSION["fornsequ".$csolcosequ];
            $corglicodi = $_SESSION["org".$csolcosequ];
            $cctrpciden = $_SESSION["flagCPFPJ".$csolcosequ];
        }
        if(!empty($csolcosequ) && !empty($aforcrsequ)){
           $_SESSION['dadosContratado']  = $ObjContrato->getFornecedorDados($aforcrsequ);
           $_SESSION['dadosObjOrgao']    = $ObjContrato->GetOrgaoEDescObj($csolcosequ);
           if(!is_null($_SESSION['postObjDescAlterado'])){  // Este cód troca a descrição do banco pela alterada;
                $_SESSION['dadosObjOrgao']->esolcoobje = $_SESSION['postObjDescAlterado']; //troca o valor
            }
           $dadosSalvar['csolcosequ'] = $csolcosequ;
           $dadosSalvar['aforcrsequ'] = $aforcrsequ;
        }
        if(!empty($_POST['ctpcomcodi'])){
            $_SESSION["safectpcomcodi"] = $_POST['ctpcomcodi'];
            $dadosSalvar['ctpcomcodi'] = $_SESSION["safectpcomcodi"];
        }else{
            $dadosSalvar['ctpcomcodi'] = $_SESSION["safectpcomcodi"];
        }
        $_SESSION['Botao'] = $_POST['Botao'];
        //Inicio da coleta dos dados selecionados em pesquisa via post  MADSON 
        if(!is_null($_POST['sccselec-'.$csolcosequ ])){

            $_SESSION['origemScc']       = $ObjContrato->corrigeString($_POST['origselec-'.$csolcosequ ]);
            $_SESSION['numScc']          = $_POST['sccselec-'.$csolcosequ ];
            $_SESSION['CpfCnpj']         = $_POST['cpfselec-'.$csolcosequ ];
        }
        $dadosSalvar['origemScc'] = $_SESSION['origemScc'];
        $dadosSalvar['numScc']    = $_SESSION['numScc']   ;
        $dadosSalvar['CpfCnpj']   = $_SESSION['CpfCnpj']  ;
        $dadosSalvar['citelpnuml']   = $_SESSION['citelpnuml']  ;
        //var_dump($_SESSION['numScc']);
        // Este if se encarrega de adicionar os campos abaixo no template caso a função objeto que é obrigatorio seja valida
        if(!is_null($dadosSalvar['numScc'])){
            $origemScc    = '<span id="origem" class="numeroContrato" style="font-size: 10.6667px;">'.$dadosSalvar['origemScc'].'</span>';
            $numScc       = '<input class="numeroContrato" style="font-size: 10.6667px;" type="text" id="numeroscc" name="numeroscc" value="'.$dadosSalvar['numScc'].'" readonly disabled="disabled" size="11">';
            $CpfCnpj      = $dadosSalvar['CpfCnpj'];
        }else{
            $origemScc    = '<span id="origem" class="numeroContrato" style="font-size: 10.6667px;"></span>';
            $numScc       = '<input class="numeroContrato" style="font-size: 10.6667px;" type="text" id="numeroscc" name="numeroscc" value="" readonly disabled="disabled" size="11">';
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

        $dadosSalvar['corglicodi']      = $corglicodi;
        // $dadosSalvar['ectrpcobje']      = $ObjContrato->corrigeString($_POST['objetoDesc'])  ;
        $dadosSalvar['ectrpcraza']      = $ObjContrato->corrigeString($razSocial)   ;
        $dadosSalvar['ectrpclogr']      = $ObjContrato->corrigeString($Rua)         ;
        $dadosSalvar['actrpcnuen']      = $ObjContrato->corrigeString($numEnd)      ;
        $dadosSalvar['ectrpccomp']      = $ObjContrato->corrigeString($complEnd)    ;
        $dadosSalvar['ectrpcbair']      = $ObjContrato->corrigeString($Bairro)      ;
        $dadosSalvar['cctrpcesta']      = $ObjContrato->corrigeString($UF)          ;
        $dadosSalvar['nctrpccida']      = $ObjContrato->corrigeString($Cidade)      ;
        $dadosSalvar['ectrpctlct']      = $_SESSION['dadosContratado']->aforcrtels;  //telefone do contratado para inserir em tbcontratosfpc
        $dadosSalvar['cctrpcccep']      = $_SESSION['dadosContratado']->cceppocodi;  //CEP do contratado para inserir em tbcontratosfpc
        $_SESSION['dadosSalvar'] = $dadosSalvar;
        if(!empty($_SESSION['documento_anexo_incluir'])){
            foreach($_SESSION['documento_anexo_incluir'] as $doc){
                if($doc['remover'] != "S"){
                    $DadosDocAnexo[]  =  (object) array(
                                                'nomearquivo'       =>$doc['nome_arquivo'],
                                                'arquivo'           => $doc['arquivo'],
                                                'sequdocumento'     => $doc['sequdoc'],
                                                'datacadasarquivo'  => $doc['data_inclusao'],
                                                'usermod'           => $doc['usermod'],
                                                'arquivo'           => $doc['arquivo'],
                                                'ativo'             => 'S'
                                            );
                }
            }
        }
    }
   $teste = $_SESSION['dadosTabela'];
   $dadosGarantia = $ObjContrato->GetListaGarantiaDocumento(); // Foi colocado aqui pois só era iniciado na tela em metodo post
   $DadosDocFiscaisFiscal = array();
    if(!empty($_SESSION['fiscal_selecionado_incluir'])){
        foreach($_SESSION['fiscal_selecionado_incluir'] as $f){
               $fiscalselecionado[] = (object)  array(
                   'tipofiscal'      =>strtoupper( $f->tipofiscal),
                   'fiscalnome'      => strtoupper($f->fiscalnome),
                   //'fiscalmatricula' => strtoupper($f->fiscalmatricula),
                   'fiscalcpf'       => strtoupper($f->fiscalcpf),
                   'fiscalemail'     => $f->fiscalemail,
                   'fiscaltel'       =>strtoupper( $f->fiscaltel),
                   'docsequ'         => strtoupper( $f->docsequ),
                   'registro'         =>  strtoupper($f->registro),
                   'ent'         => strtoupper( $f->ent),
                   'docsituacao'     => 'ATIVO',
                   'remover'         =>'N'
           );
       }
       unset( $_SESSION['fiscal_selecionado_incluir']);
       $_SESSION['fiscal_selecionado_incluir'] = $fiscalselecionado;
       $DadosDocFiscaisFiscal = $_SESSION['fiscal_selecionado_incluir'];
    } 
    
     $categoriasProcesso = $ObjContrato->getCategoriaProcesso();
     
    ?>
    <html>
    <?php

    $dadosTipoCompra = $ObjContrato->ListTipoCompra();
        # Carrega o layout padrão #
        layout();
    ?>
    <script language="javascript" src="../import/jquery/jquery-1.7.2.min.js" type="text/javascript"></script>
    <script language="javascript" src="../import/jquery/jquery.maskmoney.js" type="text/javascript"></script>
    <script language="javascript" src="../import/jquery/jquery.maskedinput.js" type="text/javascript"></script>​
    <script language="javascript" type="">
        function TestaCPF(strCPF) {
            let Soma;
            let Resto;
            Soma = 0;
            strCPF = strCPF.replace(/[^\d]+/g,'');
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
        function AbreJanelaDocumentos(url, largura, altura) {
                    window.open(
                        url,
                        'pagina',
                        'status=no,scrollbars=yes,left=20,top=150,width=' + largura	+ ',height=' + altura
                    );
        }  
        function AbreJanela(url,largura,altura) {
            window.open(url,'detalhe','status=no,scrollbars=yes,left=70,top=130,width='+largura+',height='+altura);
        }
        function limpaMensagem(){
            $("#tdmensagem").hide();
            $("#tdmensagemM").hide();
        }
        function aviso(mensagem){
                 $("#tdmensagem").show();
                $('html, body').animate({scrollTop:0}, 'slow');
				 $(".mensagem-texto").html(mensagem);
        }
        function avisoModal(mensagem){
                 $("#tdmensagemM").show();
                $('html, body').animate({scrollTop:0}, 'slow');
				 $(".mensagem-textoM").html(mensagem);
        }
        function Submete(Destino){
            document.CadContratoIncluir.Destino.value = Destino;
            document.CadContratoIncluir.submit();
        }
        function retiraFornecedor(dado){
            $.post("postDados.php",{op:"ExcluirForneModal",info:dado}, function(data){
                    ObjJson = JSON.parse(data);
                    $(".dadosFornec").html(CriatableModal(ObjJson));
                    $("#shownewfornecedores").html(CriatableView(ObjJson));
			 });
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
                            tabelaHtml +=  ((objJson[i].efiscdrgic != "") || objJson[i].efiscdrgic != null || objJson[i].efiscdrgic != "null") ? objJson[i].efiscdrgic : "";
                            tabelaHtml += '</td>';
                            tabelaHtml += '<td>';
                            tabelaHtml +=  ((objJson[i].nfiscdmlfs != "") || objJson[i].nfiscdmlfs != null || objJson[i].nfiscdmlfs != "null") ? objJson[i].nfiscdmlfs.toString() : "";
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
                    tabelaHtml += '<input  type="button" name="newselect" id="btnNewSelectModal" value="Selecionar" style="float:right;" onclick="SelecionarFiscalModal()" title="Selecionar" class="botao_New_Selecionar botao">';
                    tabelaHtml += '</td>';
                    tabelaHtml += '</tr>';
                    tabelaHtml += '</tfoot>';
                    tabelaHtml += '</table>';
                return tabelaHtml;
        }

        //função javascript  para criar o modal do fornecedor credenciado
        function CriatableModal(objJson){
            for (j in objJson){
                    if(objJson[j].remover == 'N'){
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
                                tabelaHtml += '<button type="button" class="botao" onclick="retiraFornecedor(\''+objJson[i].aforcrsequ+'\')">Remover</button>';
                                tabelaHtml += '</td>';
                                tabelaHtml += '</tr>';
                            }
                        }
                    }
                    tabelaHtml += '</tbody>';
                    tabelaHtml += '</table>';
                }else{
                    tabelaHtml = '';
                }
                }
                return tabelaHtml;
        }

        //Função para criar  a visualização do fornecedor credenciado
        function CriatableView(objJson){
            for (j in objJson){
                    if(objJson[j].remover == 'N'){
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
                                tabelaHtml += '<td>';
                                tabelaHtml += '<button type="button" class="botao" onclick="retiraFornecedor(\''+objJson[i].aforcrsequ+'\')">Remover</button>';
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
                }
                }
                return tabelaHtml;
        }


        function CriaTabelaFiscalView(objJson){
            tabelaHtml = '';
            let situacao;
            var arrayAux = new Array();
                    for(i in objJson){
                        if(objJson[i].registro == null){
                            objJson[i].registro = "";
                        }
                        if(objJson[i].ent == null){
                            objJson[i].ent = "";
                        }
                        //console.log(objJson[i].registro);
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
                                    tabelaHtml +=  (objJson[i].fiscalcpf != '')?objJson[i].fiscalcpf:'';
                                    tabelaHtml += '</td>';
                                    tabelaHtml += '<td>';
                                    tabelaHtml +=  (objJson[i].ent != '' || objJson[i].ent != null) ? objJson[i].ent : '';
                                    tabelaHtml += '</td>';
                                    tabelaHtml += '<td>';
                                    tabelaHtml +=  (objJson[i].registro != '' || objJson[i].registro != null) ? objJson[i].registro : '';
                                    tabelaHtml += '</td>';
                                    tabelaHtml += '<td>';
                                    tabelaHtml +=  (objJson[i].fiscalemail != '')?objJson[i].fiscalemail.toString():'';
                                    tabelaHtml += '</td>';
                                    tabelaHtml += '<td>';
                                    tabelaHtml +=  (objJson[i].fiscaltel != '')?objJson[i].fiscaltel:'';
                                    tabelaHtml += '</td>';
                                    situacao = (objJson[i].docsituacao != '') ? objJson[i].docsituacao : '';
                                    tabelaHtml += '</tr>';
                                 }
                             }
                        }
                        arrayAux.push(objJson[i].fiscalcpf);
                    }
                return tabelaHtml;
        }
        function SelecionarFiscalModal(){
            const Doc = "";
            const cpf = $("input[name='cpfFiscal']:checked").val();
                $.post("postDados.php",{op:"SelecFiscal",cpf:cpf,doc:Doc}, function(data){
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
        function Subirarquivo(){
                window.top.frames['frameArquivo'].subirArquivo();
        }
        function verificaContrato(){
                const contrato = $("#numcontrato").val();
                if(contrato !=""){
                    $.post("postDados.php",{op:"VerificaSeTemNumeroContrato",'numcon':contrato},function(data){
                                const objJson = JSON.parse(data);
                                if(objJson.status == false){
                                    aviso(objJson.msm);
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
                const scc = $("#numeroscc").val();
                let retorno = true;
                if(scc !=""){
                    $.post("postDados.php",{op:"VerificaSeTemNumeroContratoComSCC"},function(data){
                                const objJson = JSON.parse(data);
                                if(objJson.status == false){
                                    aviso(objJson.msm);
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
        function selecionaScc(cont, objeto){
            let valor = objeto.value;
            if(typeof localstorage === "undefined" || localstorage === null){
                localstorage = window.localStorage;
            }
            document.querySelector('#k'+cont).removeAttribute('disabled');
            var aux = valor.split("-");
            var firstval = localstorage.getItem("firstval");
            var firstcont = localstorage.getItem("firstcont");
            if(firstval == null){
                localstorage.setItem("firstval", aux[0]);
                localstorage.setItem("firstcont", cont);
            }else if(firstval == aux[0] && firstcont == cont){
                localstorage.removeItem("firstval");
                localstorage.removeItem("firstcont");
                document.querySelector('#k'+firstcont).setAttribute('disabled', 'true');
                // objeto.checked=false;
            }
            else if(firstval != aux[0] && firstval != null){
                objeto.checked=false;
            }
        }


        $(document).ready(function() { 

            $('#numcontrato').mask('9999.9999/9999');
            $('.data').mask('99/99/9999');
            $('.cnpj').mask('99.999.999/9999-99');
            $('.CPF').mask('999.999.999-99');
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
                $.post("postDados.php",{op:"ExibeFornecedorExtra",idregistro:$("input:[name=idregistro]").val()}, function(data){
                    ObjJson = JSON.parse(data);
                    $("#shownewfornecedores").html(CriatableView(ObjJson));
                });
            }
            if($("#opcaoExecucaoContrato").val() != ""){
                $("#prazo").removeAttr("disabled");
            }
            $("#repCPF").on("blur",function(){
                if(!TestaCPF($("#repCPF").val())){
                    aviso("Informe: Um CPF  válido para o representante.");
                }
            });
            $("#gestorCPF").on("blur",function(){
                if(!TestaCPF($("#gestorCPF").val())){
                    aviso("Informe: Um CPF válido para o gestor.");
                }
            });
            $("#btnvoltar").on('click', function(){
                window.history.back();
            });
            $(".btn-pesquisa-scc").on('click', function(){
                window.localStorage.clear();
                $.post("postDados.php",{op:"modalSccPesquisa"}, function(data){
                    $(".modal-content").html(data);
                    $(".modal-content").attr("style","min-height: 119px;width: 853px;");
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
            $("#manterfiscal").on('click', function(){
                $.post("postDados.php",{op:"modalFiscal"}, function(data){
                    $(".modal-content").html(data);
                    $(".modal-content").attr("style","min-height: 105px;width: 1100px;");
                    $("#modal").show();
			    });
            });
            $("#btnAdicionarFiscalModal").live("click",function(){
                $.post("postDados.php",{op:"ModalInserirFiscal"}, function(data){
                    $(".modal-content").html(data);
                    $("#modal").show();
			    });
            });
            $("#cpffiscal").live("blur",function(){
                    if(!TestaCPF($("#cpffiscal").val())){
                        avisoModal("Informe: Um CPF válido!");
                        return false;
                    }else{
                        limpaMensagem();
                    }
            });
            $("#btnSalvarModal").live("click",function(){
                if(!TestaCPF($("#cpffiscal").val())){
                        avisoModal("Informe: Um CPF válido!");
                        return false;
                    }else{
                        limpaMensagem();
                    }
              
                    
                var formulario = {
                    'op' :$("#op").val(),
                    'tipofiscalr' : ($("#radio-tipofiscal-interno").prop("checked"))?$("#radio-tipofiscal-interno").val():$("#radio-tipofiscal-externo").val(),
                    'nomefiscal': $("#nomefiscal").val(),
                    'matfiscal'   : $("#matfiscal").val(),
                    'cpffiscal'    : $("#cpffiscal").val(),
                    'entidadefiscal': $("#entidadefiscal").val(),
                    'RegInsfiscal': $("#RegInsfiscal").val(),
                    'emailfiscal': $("#emailfiscal").val(),
                    'telfiscal': $("#telfiscal").val(),
                };
                $.post("postDados.php",formulario,function(data){
                    ObjJson = JSON.parse(data);
                    if(!ObjJson.Sucess){
                        $("#tdmensagemM").show();
					    $(".mensagem-textoM").html(ObjJson.msm);
                      }else{
                        $("#modal").hide();
                        $(".dadosFiscal").html(CriaTabelaFiscalView(ObjJson.dados));
                        $("#mostrartbfiscais").html(CriaTabelaFiscalView(ObjJson.dados));
                        $("#btnPesquisaModal").hide();
                        aviso("Informe: Fiscal incluído, com sucesso!");
                      }
                });
                return false;
            });
            $("#removefiscal").live('click',function(){
                const fiscalselec = $("input[name='fiscais']:checked").val();
                $.post("postDados.php",{op:"RemoveFiscal",marcador:fiscalselec},function(data){
                     ObjJson = JSON.parse(data);
                     $("#mostrartbfiscais").html(CriaTabelaFiscalView(ObjJson));
                });
            });
            $("#formAltFiscal").live("submit",function(){
                var formulario = $("#formAltFiscal").serialize();
                $.post("postDados.php",$("#formAltFiscal").serialize(),function(data){
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
            $("#cnpj").live('focus', function(){
                $('#cnpj').mask('99.999.999/9999-99');
            });
            $("#cpffiscal").live('focus', function(){
                $('#cpffiscal').mask('999.999.999-99');
            });
            $("#btnPesquisaModal").live("click",function(){
                const tipo = $("input[name='tipofiscalr']:checked").val();
                $('#LoadPesqFiscal').show();
                $.post("postDados.php",{op:"Fiscal",cpf:$("#cpffiscal").val(),tipo:tipo}, function(data){
                    ObjJson = JSON.parse(data);
                    if(ObjJson.status){
                        $('#LoadPesqFiscal').hide();
                        $(".dadosFiscal").html(CriaTabelaFiscal(ObjJson.dados));
                        $("#cpffiscal").attr('disabled','disabled');
                        $("input[name='tipofiscalr']").attr('disabled','disabled');
                        $("#btnNewPesquisaModal").show();
                        $("#btnPesquisaModal").hide();
                        $("#tdmensagemM").hide();
                    }else if(!ObjJson.status){
                        $('#LoadPesqFiscal').hide();
                        $("#tdmensagemM").show();
					    $(".mensagem-textoM").html(ObjJson.msm);
                    }
			    });
            });
            $("#btnNewPesquisaModal").live('click', function(){
                     $("#cpffiscal").removeAttr('disabled');
                     $("#cpffiscal").val('');
                     $("input[name='tipofiscalr']").removeAttr('disabled');
                     $("#btnNewPesquisaModal").hide();
                     $("#btnPesquisaModal").show();
                     $(".dadosFiscal").html('');
            });
            $("#btnPesquisaModalSCC").live('click', function(){
                window.localStorage.clear();
                $('#LoadPesqScc').show()
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
                        $('#LoadPesqScc').hide()
                        $("#selectDivModal").html(data);
                    });
            });
            $("#btnExcluirModal").live("click", function(){
                        const cpfFiscal = $("input[name='cpfFiscal']:checked").val();
                        const tipofiscal = $("input[name='tipofiscalr']:checked").val();
                        const op           = "excluirFiscal";
                        $.post("postDados.php", {op:op,cpf:cpfFiscal,tipo:tipofiscal}, function(data){
                                    ObjJson = JSON.parse(data);
                                if(!ObjJson.Sucess){
                                    $("#tdmensagemM").show();
                                    $(".mensagem-textoM").html(ObjJson.msm);
                                }else{
                                    $("#tdmensagemM").show();
                                    $(".error").css("color","#00ff08");
                                    $(".error").html("Sucesso");
                                    $(".mensagem-textoM").html(ObjJson.msm);
                                    $(".dadosFiscal").html(CriaTabelaFiscal(ObjJson.dados));
                                }
                        });
            });
            $("#adicionarFornecedorButton").on('click', function(){
                $.post("postDados.php",{op:"ModalFornecedorCred"}, function(data){
                    $(".modal-content").html(data);
                    $("#modal").show();
			    });
            });
            $("#btnAlterarModal").live("click",function(){
                const docanexselec = $("input[name='cpfFiscal']:checked").val();
                if(docanexselec != undefined){
                    $.post("postDados.php",{op:"ModalAlterarFiscal",marcador:docanexselec},function(data){
                        $(".modal-content").html(data);
                        $("#modal").show();
                        if($("#radio-tipofiscal-interno").prop("checked")){
                            $(".mostramatricula").show();
                        }
                        if($("#radio-tipofiscal-externo").prop("checked")){
                            $(".mostramatricula").hide();
                        }
                    });
                }else{
                    avisoModal("Selecione fiscal do contrato.");
                    $('div, .modal-body').animate({scrollTop:0}, 'slow');
                }
            });
            //fornecedor
            $('#radio-cpf').live('click',function(){
                $(".mostracnpj").hide();
                $(".mostracpf").show();
                $('#cpf').mask('999.999.999-99');
            });
            $('#radio-cnpj').live('click',function(){
                $(".mostracnpj").show();
                $(".mostracpf").hide();
            });
            $("#btnAdicionarModal").live('click',function(){
                $.post("postDados.php",{op:"Fornecerdor2",cpf:$("#cpf").val(),cnpj:$("#cnpj").val()}, function(data){
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
            const tabFornMostrar = $('#hid_Tabela_fornecedor').val();
         
            if(tabFornMostrar == 'true'){
                $.post("postDados.php",{op:"Fornecerdor2",repassaSessao:'true'}, function(data){
                    ObjJson = JSON.parse(data);
                    $("#fieldConsorcio0").prop('checked', true);
                    $("#addFornecedor").show();
                    $(".dadosFornec").html(CriatableModal(ObjJson));
                    $("#shownewfornecedores").html(CriatableView(ObjJson));
                });
            }
            $("#btn-fecha-modal").live('click', function(){
                $("#modal").hide();
                window.localStorage.clear();
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
            $("#fieldConsorcio0").on('click', function(){
                $("#addFornecedor").show();
            });
            $("#fieldConsorcio1").on('click', function(){
                $("#addFornecedor").hide();
            });
            $("#addFornecedores").on('click', function(){
                $.post("postDados.php",{op:"ModalFornecedorCred"}, function(data){
                    $(".modal-content").html(data);
                    $(".modal-content").attr("style","min-height: 93px;width: 533px;");
                    $("#modal").show();
                    $.post("postDados.php",{op:"ExibeFornecedorExtra",idregistro:$("input:[name=idregistro]").val()}, function(data){
                        ObjJson = JSON.parse(data);
                        $(".dadosFornec").html(CriatableModal(ObjJson));
			        });
			    });
            });
            if($("#obra0").prop("checked")){
                var selectHtml = '<option class="textonormal" style="font-size: 10.6667px;" value=""></option>';
                     selectHtml += '<option class="textonormal" style="font-size: 10.6667px;"  <?php echo $_POST['cmb_regimeExecucaoModoFornecimento1'] == "PRECO GLOBAL"?'selected="selected"':''; ?> value="PRECO GLOBAL">EMPREITADA POR PREÇO GLOBAL</option>';
                     selectHtml += '<option class="textonormal" style="font-size: 10.6667px;" <?php echo $_POST['cmb_regimeExecucaoModoFornecimento1'] == "EMPREITADA POR PRECO UNITÁRIO"?'selected="selected"':''; ?> value="EMPREITADA POR PRECO UNITÁRIO">EMPREITADA POR PREÇO UNITÁRIO</option>';
                     selectHtml += '<option class="textonormal" style="font-size: 10.6667px;" <?php echo $_POST['cmb_regimeExecucaoModoFornecimento1'] == "TAREFA"?'selected="selected"':''; ?> value="TAREFA">TAREFA</option>';
                     selectHtml += '<option class="textonormal" style="font-size: 10.6667px;" <?php echo $_POST['cmb_regimeExecucaoModoFornecimento1'] == "EMPREITADA INTEGRAL"?'selected="selected"':''; ?> value="EMPREITADA INTEGRAL">EMPREITADA INTEGRAL</option>';
                 
                 $("#modoFornec").hide();
                 $("#regimeExec").show();
                 $("#cmb_regimeExecucaoModoFornecimento1").html(selectHtml);
            }
            if($("#obra1").prop("checked")){
                var selectHtml = '<option class="textonormal" style="font-size: 10.6667px;" value=""></option>';
                     selectHtml += '<option class="textonormal" style="font-size: 10.6667px;" <?php echo $_POST['cmb_regimeExecucaoModoFornecimento1'] == "INTEGRAL"?'selected="selected"':''; ?> value="INTEGRAL">INTEGRAL</option>';
                     selectHtml += '<option class="textonormal" style="font-size: 10.6667px;" <?php echo $_POST['cmb_regimeExecucaoModoFornecimento1'] == "PARCELADO"?'selected="selected"':''; ?> value="PARCELADO">PARCELADO</option>';
                 
                 $("#modoFornec").show();
                 $("#regimeExec").hide();
                 $("#cmb_regimeExecucaoModoFornecimento1").html(selectHtml);
            }
            $("#obra0").on('click', function(){
                 var selectHtml = '<option class="textonormal" style="font-size: 10.6667px;" value="">Selecione o regime de execução...</option>';
                     selectHtml += '<option class="textonormal" style="font-size: 10.6667px;"  value="PRECO GLOBAL">EMPREITADA POR PREÇO GLOBAL</option>';
                     selectHtml += '<option class="textonormal" style="font-size: 10.6667px;"  value="EMPREITADA POR PRECO UNITÁRIO">EMPREITADA POR PREÇO UNITÁRIO</option>';
                     selectHtml += '<option class="textonormal" style="font-size: 10.6667px;"  value="TAREFA">TAREFA</option>';
                     selectHtml += '<option class="textonormal" style="font-size: 10.6667px;"  value="EMPREITADA INTEGRAL">EMPREITADA INTEGRAL</option>';
                 
                 $("#modoFornec").hide();
                 $("#regimeExec").show();
                 $("#cmb_regimeExecucaoModoFornecimento1").html(selectHtml);
            });
            $("#obra1").on('click', function(){
                 var selectHtml = '<option class="textonormal" style="font-size: 10.6667px;" value="">Selecione o modo de fornecimento...</option>';
                     selectHtml += '<option class="textonormal" style="font-size: 10.6667px;" value="INTEGRAL">INTEGRAL</option>';
                     selectHtml += '<option class="textonormal" style="font-size: 10.6667px;" value="PARCELADO">PARCELADO</option>';
                
                 $("#modoFornec").show();
                 $("#regimeExec").hide();
                 $("#cmb_regimeExecucaoModoFornecimento1").html(selectHtml);
            });
            $("#opcaoExecucaoContrato").on('change', function(){ // define se o campo de prazo é alteravel ou não
                var opExec = $("#opcaoExecucaoContrato").val();
                if(opExec != 0){
                    $("#prazo").prop("disabled", false);
                }else{
                    $("#prazo").prop("disabled", true);
                    $("#prazo").val("");
                }
            });
            $("#file").on('change', function(){
                var file = $("#file").val();
                $.post("postDados.php",{op:"InsereArquivo", arquivo:file}, function(data){
                });
            });
            $('#btnIncluirAnexo').live("click",()=>{
                $('#loadArquivo').show();
            })
            $("#btnRemoveAnexo").live("click",function(){
                $('#loadArquivo').show();
                const docanexselec = $("input[name='docanex']:checked").val();
                $.post("postDados.php",{op:"RemoveDocAnex",marcador:docanexselec},function(data){
                    $('#loadArquivo').hide();
                    $("#FootDOcFiscal").html(data);
                });
            });

            $("#salvaContrato").on('click', function(){
                $('html, body').animate({scrollTop:0}, 'slow');
                $('#tdload').show();
                let podesalvar = true;
                if(!TestaCPF($("#repCPF").val())){
                    $('#tdload').hide();
                    aviso("Informe: Um CPF  válido para o representante.");
                    $("#salvaContrato").prop("disabled", false);
                    podesalvar= false;
                }
                 if(!TestaCPF($("#gestorCPF").val())){
                    $('#tdload').hide();
                    aviso("Informe: Um CPF válido para o gestor.");
                    $("#salvaContrato").prop("disabled", false);
                    podesalvar= false;
                }
                    $.post("postDados.php",$("#formContratoIncluir").serialize(), function(data){ 
                                const response = JSON.parse(data);
                                if(!response.status){
                                    $('html, body').animate({scrollTop:0}, 'slow');
                                    $('#tdload').hide();
                                    $(".mensagem-texto").html(response.msm);
                                    $(".error").html("Erro!");
                                    $("#tdmensagem").show();
                                    $("#salvaContrato").prop("disabled", false);
                                }else{
                                    $('html, body').animate({scrollTop:0}, 'slow');
                                    $('#tdload').hide();
                                    $(".mensagem-texto").html(response.msm);
                                    $(".error").html("Atenção!");
                                    $(".error").css("color","#007fff");
                                    $("#tdmensagem").show();
                                    setTimeout(function(){ 
                                        window.location.href = "./CadContratoIncluir.php?";
                                    }, 2000);
                                }
                    });
            });
            $("#radio-tipofiscal-interno").live('click', function(){
                $(".mostramatricula").show();
            });
            $("#radio-tipofiscal-externo").live('click', function(){
                $(".mostramatricula").hide();
            });
            if($("#numeroscc").val()){
                verificaContratoComSCC();
            }
            // Este código envia a alteração do campo descrição/objeto para o post armazenar em sessão caso seja alterada.
            $("#objeto").on('change', function(){ 
                const descAlterada = $("#objeto").val();
                $.post("postDados.php",{op:"SalvaAlteracaoObjeto",objetoAlterado:descAlterada}, function(data){
                    const response = JSON.parse(data);
                    $("#objeto").val(response.dados);
                });
                
            });
            
        });

        <?php MenuAcesso(); ?>
       
    </script>
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
        .input{
            font-size: 10.6667px;
        }
    </style>
    <body background="../midia/bg.gif" marginwidth="0" marginheight="0">
    <script language="JavaScript" src="../menu.js"></script>
    <script language="JavaScript">Init();</script>
    <form action="CadContratoIncluir.php" method="post" id="formContratoIncluir" name="CadContratoIncluir">
    <input type="hidden" name="op" value="IncluirContrato">
    <input type="hidden" name="ctpcomcodi" value="<?php echo $dadosSalvar["ctpcomcodi"];?>">
        <br><br><br><br><br>
        <table cellpadding="3" border="0" summary="">
            <!-- Caminho -->
            <tr>
                <td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
                <td align="left" class="textonormal" colspan="2">
                    <font class="titulo2">|</font>
                    <a href="../index.php"><font color="#000000">Página Principal</font></a> > Contratos > Incluir
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
            <!-- loading -->
				<tr>
					<td width="150"></td>
                    <td align="left" colspan="2" id="tdload" style="display:none;">
                        <div class="load" id="load"> 
                            <div class="load-content" >
                            <img src="../midia/loading.gif" alt="Carregando">
                            <spam>Carregando...</spam>
                            </div>
                        </div> 
					</td>
				</tr>
			<!-- Fim do loading -->

            <!-- Corpo -->
            <tr>
                <td width="100"></td>
                <td class="textonormal">
                    <table  border="0" cellspacing="0" cellpadding="3" summary="" width="1024px" bgcolor="#FFFFFF">
                        <tr>
                            <td class="textonormal" border="0" bordercolor="#75ADE6">

                                <table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" bgcolor="#FFFFFF">
                                    <thead>
                                        <td class="titulo3" colspan="17" align="center" bgcolor="#75ADE6" valign="middle"> <b>INCLUIR CONTRATO</b>
                                        </td>
                                    </thead>
                                    <th class="textonormal" colspan="17" align="left" style="font-size: 10.6667px;">
                                                Preencha inicialmente o Número da SCC e clique na lupa, depois continue o preenchimento dos demais campos e clique no botão 'Incluir'. Os itens obrigatórios estão com *.
                                    </th> 
                                    <tr>
                                        <td align="left">
                                            
                                            <table cellpadding="0" cellspacing="0" border="0" bordercolor="#75ADE6" width="100%" summary="">
                                                <th colspan="3" >
                                                    <?php echo NavegacaoAbasIncluir(on,off); ?>
                                                </th>
                                                <tr bgcolor="#bfdaf2">                                                    
                                                    <!-- <td colspan="4"> -->
                                                        
                                                        <table class="textonormal" id="scc_material" summary="" width="100%">
                                                            <!-- style="border: 1px solid #75ade6; border-radius: 4px;" -->
                                                            
                                                            <tbody>                                                               
                                                                <tr>
                                                                    <td bgcolor="#DCEDF7" class="textonormal" width="225px">
                                                                        Solici. de Compra/Contratação-SCC*
                                                                    </td>
                                                                    <td>
                                                                        <?php
                                                                                echo $numScc;
                                                                        ?>
                                                                        <a href="#" class="btn-pesquisa-scc">
                                                                            <img src="../midia/lupa.gif" border="0">
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td bgcolor="#DCEDF7" class="textonormal" width="225px">Número do Contrato/Ano
                                                                        <td bgcolor="White" >
                                                                            <input id="numcontrato" type="text" name="numcontrato" class="numeroContrato" style="font-size: 10.6667px;" value="<?php echo !empty($_POST['numcontrato'])?$_POST['numcontrato']:'';?>" onblur="verificaContrato()" maxlength="20" size="11">
                                                                        </td>
                                                                    </td>
                                                                </tr>
                                                                
                                                                <tr>
                                                                     <td bgcolor="#DCEDF7" class="textonormal" width="225px">Origem
                                                                     <td class="textonormal" style="font-size: 10.6667px;">
                                                                     <?php 
                                                                         echo $origemScc; 
                                                                     ?>
                                                                     </td>
                                                                </tr>
                                                                <tr>
                                                                    <td bgcolor="#DCEDF7" class="textonormal" width="225px">Órgão Contratante Responsável</td>
                                                                    <td class="textonormal">
                                                                        <?php if(!is_null($orgLicitante)){  echo "$orgLicitante"; } ?>
                                                                        <span id="orgao" style="font-size: 10.6667px;"></span>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td bgcolor="#DCEDF7" title="Símbolos e caracteres especiais como 'Ç','~'','-' e acentos devem ser removidos do texto para evitar erro." class="textonormal"  width="225px">Objeto*
                                                                    <td>
                                                                        <textarea class="textonormal" style="font-size: 10.6667px; text-transform: uppercase;" id="objeto" name="objetoDesc" cols="50" rows="4" maxlength="1000" onkeyup="return limitChars(this,1000)" rows="8"><?php echo !is_null($objetoDesc)?$objetoDesc:$_POST['objetoDesc']; ?></textarea>
                                                                    </td>
                                                                    </td>
                                                                </tr>                                                                
                                                                <tr>
                                                                    <td bgcolor="#DCEDF7" class="textonormal"  width="225px">Contratado
                                                                    <td class="textonormal">
                                                                        <table id="_gridContratadoNovo">
                                                                            <tbody><tr><td class="labels">
                                                                                <span id="_panelLblCpfCnpj">
                                                                                <label for="" style=";" class="textonormal" ><?php echo (strlen($CpfCnpj) == 18)? "CNPJ do Contratado :": "CPF do Contratado :"; ?></label></span></td><td class="textonormal" colspan="3"><div id="_panelInputCpfCnpj"><?php if(!is_null($CpfCnpj)){  echo $CpfCnpj; } ?><label>
                                                                                    </label></div></td></tr>
                                                                                    <tr><td class="labels"><label for="" style=";" class="textonormal">Razão Social :</label></td><td class="textonormal" colspan="3"><div id="_panelGroupRazao"><span id="_razaoSocialfornecedor"><?php if(!is_null($razSocial)){  echo "$razSocial"; } ?></span></div></td></tr>
                                                                                    <tr><td class="labels"><label for="" style=";" class="textonormal">Logradouro :</label></td><td class="textonormal" colspan="3"><span id="_logradourofornecedor"><?php if(!is_null($Rua)){  echo "$Rua"; } ?></span></td></tr>
                                                                                    <tr><td class="labels"><label for="" style=";" class="textonormal">Número :</label></td><td class="textonormal" colspan="3"><span id="_numerofornecedor"><?php if(!is_null($numEnd)){  echo "$numEnd"; } ?></span></td></tr>
                                                                                    <tr><td class="labels"><label for="" style=";" class="textonormal">Complemento :</label></td><td class="textonormal"><span id="_complementoLogradourofornecedor"><?php if(!is_null($complEnd)){  echo "$complEnd"; } ?></span></td><td class="labels"><label for="" style=";" class="textonormal">Bairro:</label></td><td class="textonormal"><span id="_bairrofornecedor"><?php if(!is_null($Bairro)){  echo "$Bairro"; } ?></span></td></tr>
                                                                                    <tr><td class="labels"><label for="" style=";" class="textonormal">Cidade :</label></td><td class="textonormal"><span id="_cidadefornecedor"><?php if(!is_null($Cidade)){  echo "$Cidade"; } ?></span></td><td class="labels"><label for="" style=";" class="textonormal">UF:</label></td><td class="textonormal"><span id="_estadofornecedor"><?php if(!is_null($UF)){  echo "$UF"; } ?></span></td></tr>
                                                                                    </tbody></table>
                                                                    </td>
                                                                </td>
                                                                </tr>
                                                                <tr>
                                                                    <?php
                                                                        if($_SESSION['dadosFornecedor']){
                                                                            echo '<input type="hidden" name="hid_Tabela_fornecedor" id="hid_Tabela_fornecedor" value=true>';
                                                                        }else{
                                                                            echo '<input type="hidden" name="hid_Tabela_fornecedor" id="hid_Tabela_fornecedor" value=false>';
                                                                        };
                                                                    ?>
                                                                    <td bgcolor="#DCEDF7" class="textonormal"  width="225px">Consórcio / Matriz-Filial / Publicidade?*</td>
                                                                    <td>
                                                                        <table id="fieldConsorcio">
                                                                        <tbody><tr>
                                                                            <td style="font-size:10.6667px;">
                                                                                <input type="radio" name="fieldConsorcio" id="fieldConsorcio0" <?php echo ($_POST['fieldConsorcio'] == 'SIM' && $_SESSION['csolcosequ'] == $csolcosequ)?'checked="checked"':'';?> value="SIM" title="Consórcio / Matriz-Filial / Publicidade ?"><label for="fieldConsorcio0"> Sim</label></td>
                                                                            <td style="font-size:10.6667px;">
                                                                                <input type="radio" name="fieldConsorcio" id="fieldConsorcio1" value="NAO"  <?php echo $_POST['fieldConsorcio']== 'NAO'?'checked="checked"':'';?> title="Consórcio / Matriz-Filial / Publicidade ?"><label for="fieldConsorcio1"> Não</label></td>
                                                                            </tr>
                                                                    </tbody></table></td></td>
                                                                </tr>
                                                                <tr id="addFornecedor" style="display:none;" >
                                                                        <td bgcolor="#DCEDF7">
                                                                            Fornecedores* 
                                                                        </td>
                                                                        <td class="textonormal" colspan="2">
                                                                            <input type="button" id="addFornecedores" class="botao" value="Adicionar Fornecedor">
                                                                            <div id="shownewfornecedores"></div>
                                                                        </td>
                                                                    </tr>
                                                                <tr>
                                                                     <td bgcolor="#DCEDF7" class="textonormal" width="225px">Contínuo*</td>
                                                                     <td class="textonormal">
                                                                     <table id="fieldContinuo">
                                                                        <tbody><tr>
                                                                            <td style="font-size:10.6667px;">
                                                                            <input type="radio" name="fieldContinuo" id="fieldContinuo0" value="SIM" <?php echo $_POST['fieldContinuo']== 'SIM'?'checked="checked"':'';?> title="Contínuo"><label for="fieldContinuo0"> Sim</label></td>
                                                                            <td style="font-size:10.6667px;">
                                                                            <input type="radio" name="fieldContinuo" id="fieldContinuo1" value="NAO" <?php echo $_POST['fieldContinuo']== 'NAO'?'checked="checked"':'';?>  title="Contínuo"><label for="fieldContinuo1"> Não</label></td>
                                                                            </tr>
                                                                        </tbody>
                                                                    </table>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td bgcolor="#DCEDF7" class="textonormal" width="225px">Obra*</td>
                                                                    <td class="textonormal">
                                                                        <table id="obra">
                                                                            <tbody><tr>
                                                                                <td style="font-size:10.6667px;">
                                                                                    <input type="radio" name="obra" id="obra0"  <?php echo $_POST['obra'] == 'SIM'? 'checked="checked"':'';?> value="SIM"><label for="obra:0"> Sim</label></td>
                                                                                <td style="font-size:10.6667px;">
                                                                                    <input type="radio" name="obra" id="obra1" <?php echo $_POST['obra'] == 'NAO'? 'checked="checked"':'';?>  <?php //echo empty($_POST['obra'])? 'checked="checked"':'';?> value="NAO" ><label for="obra:1"> Não</label></td>
                                                                            </tr>
                                                                        </tbody>
                                                                        </table>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                        <td id="modoFornec"bgcolor="#DCEDF7" title="Modo de fornecimento trata de como será executado o fornecimento de objetos de compra quando atribuídos itens e lotes.">Modo de Fornecimento *</td>
                                                                        <td id="regimeExec"bgcolor="#DCEDF7" style="display:none;" title="Regime de fornecimento refere-se a como as partes executarão as prestações que lhe incumbem quando tratar de obras e serviços.">Regime de Execução *</td>
                                                                        <td class="inputs">
                                                                            <select class="selectContrato" id="cmb_regimeExecucaoModoFornecimento1" style="font-size: 10.6667px;"  name="cmb_regimeExecucaoModoFornecimento1"  value="<?php echo !empty($_POST['cmb_regimeExecucaoModoFornecimento1'])?$_POST['cmb_regimeExecucaoModoFornecimento1']:'';?>" size="1">	
                                                                                <option ></option>
                                                                            </select>
                                                                        </td>
                                                                 </tr>
                                                                 <tr>
                                                                    <td bgcolor="#DCEDF7" class="numdeparcela" width="225px">Número de Parcelas*</td>
                                                                    <td>
                                                                        <input type="number" name="NumDeParcelas" size="15" maxlength="18" value="<?php echo !empty($_POST['NumDeParcelas'])?$_POST['NumDeParcelas']:''; ?>">
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td bgcolor="#DCEDF7" class="valordaparcela" width="225px">Valor de Parcelas*</td>
                                                                    <td> 
                                                                        <input type="text" class="dinheiro4casas" name="ValorDaParcelas" size="18" maxlength="18" value="<?php echo !empty($_POST['ValorDaParcelas'])?$_POST['ValorDaParcelas']:''; ?>"> 
                                                                    </td>
                                                                </tr>
                                                                <!-- <tr>
                                                                        
                                                                        <td class="inputs">
                                                                            <select class="selectContrato" id="cmb_regimeExecucaoModoFornecimento1" style="font-size: 10.6667px;"  name="cmb_regimeExecucaoModoFornecimento1"  value="<?php echo !empty($_POST['cmb_regimeExecucaoModoFornecimento1'])?$_POST['cmb_regimeExecucaoModoFornecimento1']:'';?>" size="1">	
                                                                                <option ></option>
                                                                            </select>
                                                                        </td>
                                                                 </tr> 
                                                                <tr> -->
                                                                    <td bgcolor="#DCEDF7" class="textonormal"  width="225px">Opção de Execução do Contrato*</td>
                                                                    <td class="textonormal" >
                                                                        <select class="textonormal" style="font-size: 10.6667px;" id="opcaoExecucaoContrato" name="opcaoExecucaoContrato" size="1" title="Opção de Execução do Contrato " style="width:70px;">	
                                                                        <option class="textonormal" style="font-size: 10.6667px;" value="" <?php echo empty($_POST['opcaoExecucaoContrato'])?'selected="selected"':''; ?> >Selecione uma opção...</option>
                                                                        <option class="textonormal" style="font-size: 10.6667px;" value="D" <?php echo $_POST['opcaoExecucaoContrato'] == "D"?'selected="selected"':''; ?> >DIAS</option>
                                                                        <option class="textonormal" style="font-size: 10.6667px;" value="M" <?php echo $_POST['opcaoExecucaoContrato'] == "M"?'selected="selected"':''; ?> >MESES</option>
                                                                        </select>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td bgcolor="#DCEDF7" class="textonormal"  width="225px">Prazo de Execução do Contrato*</td>
                                                                    <td class="textonormal"><input id="prazo" type="text" name="prazo" value="<?php echo !empty($_POST['prazo'])?$_POST['prazo']:'';?>" class="inteiroPositivo" maxlength="4" size="6" style="font-size: 10.6667px;" disabled="disabled">
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td bgcolor="#DCEDF7" class="textonormal"  width="225px"> Categoria do Processo* </td>
                                                                    <td class="textonormal" >
                                                                        <select class="textonormal" style="font-size: 10.6667px;" id="opcaocategoriaprocesso" name="opcaocategoriaprocesso" size="1" title="Opção de Categoria processo " style="width:70px;">
                                                                            <option class="textonormal" style="font-size: 10.6667px;" value="" <?php echo empty($_POST['opcaoExecucaoContrato'])?'selected="selected"':''; ?> >Selecione uma opção...</option>
                                                                            <?php if(!empty($categoriasProcesso)){
                                                                                foreach($categoriasProcesso as $item){
                                                                            ?>
                                                                                    <option class="textonormal" style="font-size: 10.6667px;" value="<?php echo $item->cpnccpcodi;?>" <?php echo $_POST['opcaoExecucaoContrato'] == "D"?'selected="selected"':''; ?> ><?php echo $item->epnccpnome;?></option>
                                                                            <?php  }
                                                                                 }
                                                                            ?>
                                                                        </select>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td bgcolor="#DCEDF7" class="textonormal"  width="225px">Data de Publicação no DOM</td>
                                                                    <td class="textonormal">
                                                                        <input id="dataPublicacaoDom" type="text" name="dataPublicacaoDom"  value="<?php echo !empty($_POST['dataPublicacaoDom'])?$_POST['dataPublicacaoDom']:'';?>" class="data" maxlength="10" size="12" title="" style="font-size: 10.6667px;">
                                                                        <a style="text-decoration: none" href="javascript:janela('../calendario.php?Formulario=CadContratoIncluir&amp;Campo=dataPublicacaoDom','Calendario',220,170,1,0)"> 
			                                                                    <img src="../midia/calendario.gif" border="0" alt="">
		                                                                    </a>
                                                                    </td>
                                                                </tr>
                                                                <tr id="linhaTabelaOS">
				                                                    <td id="colunaVaziaOS" width="225px" bgcolor="#bfdaf2"></td>
				                                                    <td id="colunaDataInicioOS" bgcolor="#bfdaf2" style="width:415px;">
                                                                    <table id="panelDataInicioOrdemServico" class="colorBlue">
                                                                    <thead>
                                                                    <tr ><th class="titulo3" colspan="1" scope="colgroup"><span id="labelDataInicioOrdemServico">DATA DE INÍCIO</span></th></tr>
                                                                    </thead>
                                                                    </table>
                                                                    </td>
                                                                     <td id="colunaDataTerminoOS" bgcolor="#bfdaf2" style="width: 256px;" ><table id="panelDataTerminoOrdemServico" class="colorBlue">
                                                                    <thead>
                                                                    <tr><th class="titulo3" colspan="1" scope="colgroup"><span id="labelDataTerminoOrdemServico">DATA DE TÉRMINO</span></th></tr>
                                                                    </thead>
                                                                    </table>
                                                                    </td>
			                                                        </tr>
                                                                </tr>
                                                                <tr>
                                                                     <td bgcolor="#DCEDF7" class="textonormal" width="225px">Vigência*</td>
                                                                     <td class="textonormal">
                                                                     <span id="vigenciaGroup">
                                                                     <input id="vigenciaDataInicio" type="text" name="vigenciaDataInicio" value="<?php echo !empty($_POST['vigenciaDataInicio'])?$_POST['vigenciaDataInicio']:'';?>" class="data" maxlength="10" size="12" title="" style="font-size: 10.6667px;">
                                                                     <a id="calendarioVig" style="text-decoration: none" href="javascript:janela('../calendario.php?Formulario=CadContratoIncluir&amp;Campo=vigenciaDataInicio','Calendario',220,170,1,0)"> 
                                                                        <img src="../midia/calendario.gif" border="0" alt="">
		                                                                </a>
                                                                    <td>                
                                                                    <input id="vigenciaDataTermino" type="text" name="vigenciaDataTermino" value="<?php echo !empty($_POST['vigenciaDataTermino'])?$_POST['vigenciaDataTermino']:'';?>" class="data"  maxlength="10" size="12"style="font-size: 10.6667px;">
                                                                    <a id="calendarioVig" style="text-decoration: none" href="javascript:janela('../calendario.php?Formulario=CadContratoIncluir&amp;Campo=vigenciaDataTermino','Calendario',220,170,1,0)"> 
                                                                        <img src="../midia/calendario.gif" border="0" alt="">
		                                                                </a>
                                                                    </td>
                                                                    </span>   
                                                                        </td>
                                                                </tr>
                                                                <tr>
                                                                     <td bgcolor="#DCEDF7" class="textonormal" width="225px">Execução*</td>
                                                                     <td class="textonormal">
                                                                        <span id="execucaoGroup">              
                                                                            <input id="execucaoDataInicio" type="text" name="execucaoDataInicio" value="<?php echo !empty($_POST['execucaoDataInicio'])?$_POST['execucaoDataInicio']:'';?>" class="data"  maxlength="10" size="12" style="font-size: 10.6667px;">
                                                                            <a id="calendarioExecIni" style="text-decoration: none" href="javascript:janela('../calendario.php?Formulario=CadContratoIncluir&amp;Campo=execucaoDataInicio','Calendario',220,170,1,0)"> 
                                                                            <img src="../midia/calendario.gif" border="0" alt="">
		                                                                    </a>
                                                                            <td>                
                                                                                <input id="execucaoDataTermino" type="text" name="execucaoDataTermino" value="<?php echo !empty($_POST['execucaoDataTermino'])?$_POST['execucaoDataTermino']:'';?>" class="data"  maxlength="10" size="12" style="font-size: 10.6667px;">
                                                                                <a id="calendarioExecTerm" style="text-decoration: none" href="javascript:janela('../calendario.php?Formulario=CadContratoIncluir&amp;Campo=execucaoDataTermino','Calendario',220,170,1,0)"> 
                                                                                    <img src="../midia/calendario.gif" border="0" alt="">
                                                                                </a>
                                                                            </td>
                                                                            </span>
                                                                        </span>
                                                                    </td>   
                                                                </tr>
                                                                <tr>
                                                                     <td bgcolor="#DCEDF7" class="textonormal" width="225px">Garantia</td>
                                                                     <td class="textonormal">
                                                                        <select id="comboGarantia" name="comboGarantia" size="1" title="Garantia" style="width:315px; font-size: 10.6667px; ">	
                                                                            <?php foreach($dadosGarantia as $garantia){ 
                                                                                $selecionadoGarantia = ($garantia->codgarantia == $_POST['comboGarantia'])?'selected="selected"':'';    
                                                                        ?>
                                                                            <option class="textonormal" style="font-size: 10.6667px;" value="<?php echo $garantia->codgarantia;?>" <?php echo $selecionadoGarantia; ?> >
                                                                                <?php echo $garantia->descricaogarantia;?>
                                                                            </option>
                                                                        <?php } ?>
                                                                        </select>
                                                                    </td>
                                                                </tr>
                                                                
                                                                <!-- <tr id="linhaTabelaOS" bgcolor="#bfdaf2"> -->
				                                                    <!-- <td id="colunaDataInicioOS"><table id="panelDataInicioOrdemServico" class="colorBlue"> -->
                                                                
                                                                <tr bgcolor="#bfdaf2"><th class="titulo3" colspan="3" <?php echo $bloqueiacampo?'disabled="disabled"':'';?> scope="colgroup">REPRESENTANTE LEGAL</th>
                                                                </tr>
                                                                
                                                                <tr>
                                                                     <td bgcolor="#DCEDF7" class="textonormal" width="225px">Nome*<td bgcolor="White">
                                                                                <input type="text" name="repNome" value="<?php echo !empty($_POST['repNome'])?$_POST['repNome']:'';?>" maxlength="120" style="width:315px; font-size: 10.6667px;">
                                                                    </td></td>
                                                                </tr>
                                                                <tr>
                                                                     <td bgcolor="#DCEDF7" class="textonormal" width="225px">CPF*<td bgcolor="White">
                                                                                <input  class="CPF" type="text" name="repCPF"  value="<?php echo !empty($_POST['repCPF'])?$_POST['repCPF']:'';?>"id="repCPF" size="11" style="font-size: 10.6667px;">
                                                                    </td></td>
                                                                </tr>
                                                                <tr>
                                                                     <td bgcolor="#DCEDF7" class="textonormal" width="225px">Cargo<td bgcolor="White">
                                                                                <input type="text" name="repCargo"  value="<?php echo !empty($_POST['repCargo'])?$_POST['repCargo']:'';?>" maxlength="100" style="width:173px; font-size: 10.6667px;">
                                                                    </td></td>
                                                                </tr>
                                                                <tr>
                                                                     <td bgcolor="#DCEDF7" class="textonormal" width="225px">Identidade<td bgcolor="White">
                                                                                <input type="text" name="repRG" value="<?php echo !empty($_POST['repRG'])?$_POST['repRG']:'';?>" maxlength="9" size="11" style="font-size: 10.6667px;">
                                                                    </td></td>
                                                                </tr>
                                                                <tr>
                                                                     <td bgcolor="#DCEDF7" class="textonormal" width="225px">Órgão Emissor<td bgcolor="White">
                                                                                <input type="text" name="repRgOrgao"  value="<?php echo !empty($_POST['repRgOrgao'])?$_POST['repRgOrgao']:'';?>" maxlength="3" size="1" style="font-size: 10.6667px;">
                                                                    </td></td>
                                                                </tr>
                                                                <tr>
                                                                     <td bgcolor="#DCEDF7" class="textonormal" width="225px">UF da Identidade<td bgcolor="White">
                                                                                <input type="text" name="repRgUF" value="<?php echo !empty($_POST['repRgUF'])?$_POST['repRgUF']:'';?>" maxlength="2" size="1" style="text-transform: uppercase; font-size: 10.6667px;">
                                                                    </td></td>
                                                                </tr>
                                                                <tr>
                                                                     <td bgcolor="#DCEDF7" class="textonormal" width="225px">Cidade de Domicílio<td bgcolor="White">
                                                                                <input type="text" name="repCidade" value="<?php echo !empty($_POST['repCidade'])?$_POST['repCidade']:'';?>" maxlength="30" style="width:173px; font-size: 10.6667px;">
                                                                    </td></td>
                                                                </tr>
                                                                <tr>
                                                                     <td bgcolor="#DCEDF7" class="textonormal" width="225px">Estado de Domicílio<td bgcolor="White">
                                                                                <input type="text" name="repEstado" value="<?php echo !empty($_POST['repEstado'])?$_POST['repEstado']:'';?>" maxlength="2" size="1" style="text-transform: uppercase; font-size: 10.6667px;">
                                                                    </td></td>
                                                                </tr>
                                                                <tr>
                                                                     <td bgcolor="#DCEDF7" class="textonormal" width="225px">Nacionalidade<td bgcolor="White">
                                                                                <input type="text" name="repNacionalidade"  value="<?php echo !empty($_POST['repNacionalidade'])?$_POST['repNacionalidade']:'';?>" maxlength="50" style="width:173px; font-size: 10.6667px;">
                                                                    </td></td>
                                                                </tr>
                                                                <tr>
                                                                     <td bgcolor="#DCEDF7" class="textonormal" width="225px">Estado Civil<td bgcolor="White"> 
                                                                        <select id="repEstCiv" name="repEstCiv" size="1" style="width:173px; font-size: 10.6667px;">	
                                                                            <option class="textonormal" style="font-size: 10.6667px;" <?php echo $_POST['repEstCiv'] == 'Z'?'selected="selected"':'';?> value="Z">Selecione o estado civil...</option>
                                                                            <option class="textonormal" style="font-size: 10.6667px;" <?php echo $_POST['repEstCiv'] == 'S'?'selected="selected"':'';?> value="S">SOLTEIRO</option>
                                                                            <option class="textonormal" style="font-size: 10.6667px;" <?php echo $_POST['repEstCiv'] == 'C'?'selected="selected"':'';?> value="C">CASADO</option>
                                                                            <option class="textonormal" style="font-size: 10.6667px;" <?php echo $_POST['repEstCiv'] == 'C'?'selected="selected"':'';?> value="D">DIVORCIADO</option>
                                                                            <option class="textonormal" style="font-size: 10.6667px;" <?php echo $_POST['repEstCiv'] == 'V'?'selected="selected"':'';?> value="V">VIÚVO</option>
                                                                            <option class="textonormal" style="font-size: 10.6667px;" <?php echo $_POST['repEstCiv'] == 'O'?'selected="selected"':'';?> value="O">OUTROS</option>
                                                                        </select>
                                                                    </td></td>
                                                                </tr>
                                                                <tr>
                                                                    <td bgcolor="#DCEDF7" class="textonormal" width="225px">Profissão
                                                                    <td class="textonormal">
                                                                        <input id="Profissao" type="text" name="repProfissao" value="<?php echo !empty($_POST['repProfissao'])?$_POST['repProfissao']:'';?>" maxlength="50" style="width:173px; font-size: 10.6667px; text-transform: uppercase;">
                                                                    </td></td>
                                                                </tr> 
                                                                <tr>
                                                                     <td bgcolor="#DCEDF7" class="textonormal" width="225px">E-mail<td bgcolor="White" class="textonormal">
                                                                         <!-- foi retirado o toUpperCase -->
                                                                                <input type="text" name="repEmail" value="<?php echo !empty($_POST['repEmail'])?$_POST['repEmail']:'';?>" maxlength="60" style="width:173px; font-size: 10.6667px; text-transform: none;">
                                                                    </td></td>
                                                                </tr>
                                                                <tr>
                                                                     <td bgcolor="#DCEDF7" class="textonormal" width="225px">Telefone(s)<td bgcolor="White" class="textonormal">
                                                                                <input class="telefone" type="text" name="repTelefone" value="<?php echo !empty($_POST['repTelefone'])?$_POST['repTelefone']:'';?>" size="13" style="font-size: 10.6667px;">
                                                                    </td></td>
                                                                </tr>                                                        
                                                                <tr bgcolor="#bfdaf2">
                                                                        <th class="titulo3" colspan="3" <?php echo $bloqueiacampo?'disabled="disabled"':'';?> scope="colgroup">GESTOR</th>
                                                                </tr>
                                                                <tr>
                                                                        <td bgcolor="#DCEDF7" class="textonormal" width="225px">Nome*<td bgcolor="White" class="textonormal">
                                                                                    <input type="text" name="gestorNome" value="<?php echo !empty($_POST['gestorNome'])?$_POST['gestorNome']:'';?>" maxlength="120" style="width:315px; font-size: 10.6667px;">
                                                                        </td></td>
                                                                </tr>
                                                                <tr>
                                                                        <td bgcolor="#DCEDF7" class="textonormal" width="225px">Matrícula*<td bgcolor="White" class="textonormal">
                                                                                    <input type="text" name="gestorMatricula" value="<?php echo !empty($_POST['gestorMatricula'])?$_POST['gestorMatricula']:'';?>" maxlength="20" size="11" style="font-size: 10.6667px;">
                                                                        </td></td>
                                                                </tr>
                                                                
                                                                <tr>
                                                                        <td bgcolor="#DCEDF7" class="textonormal" width="225px">CPF*<td bgcolor="White" class="textonormal">
                                                                                    <input class="CPF" type="text" name="gestorCPF" value="<?php echo !empty($_POST['gestorCPF'])?$_POST['gestorCPF']:'';?>" id="gestorCPF" size="11"  style="font-size: 10.6667px;">
                                                                        </td></td>
                                                                </tr>
                                                                
                                                                <tr>
                                                                        <td bgcolor="#DCEDF7" class="textonormal" width="225px">E-mail* 
                                                                            <td bgcolor="White" class="textonormal">
                                                                                <!-- Foi retirado o toUpperCase -->
                                                                                <input type="text" name="gestorEmail" value="<?php echo !empty($_POST['gestorEmail'])?$_POST['gestorEmail']:'';?>" maxlength="60" style="width:173px; font-size: 10.6667px; text-transform: none;">
                                                                            </td>
                                                                        </td>
                                                                </tr>
                                                                
                                                                <tr>
                                                                    <td bgcolor="#DCEDF7" class="textonormal" width="225px">Telefone(s)* 
                                                                        <td bgcolor="White" class="textonormal">
                                                                            <input class="telefone" type="text" name="gestorTelefone" value="<?php echo !empty($_POST['gestorTelefone'])?$_POST['gestorTelefone']:'';?>" size="13" style="font-size: 10.6667px;">
                                                                        </td>
                                                                    </td>
                                                                </tr>
                                                                <tr bgcolor="#bfdaf2">
                                                                   <th class="titulo3" colspan="3" <?php echo $bloqueiacampo?'disabled="disabled"':'';?> scope="colgroup">FISCAL(IS)*</th>
                                                                </tr>
                                                                <tr>
                                                                <!-- Eliakim Ramos 05032019 -->
                                                                    <td colspan="3" >
                                                                                <table style="width:100%; border:1px solid #bfdaf2;"  id="tabelaficais">
                                                                                    <thead bgcolor="#bfdaf2" style="">
                                                                                        <tr>
                                                                                            <td class="titulo3" colspan="1"></td>
                                                                                            <td class="titulo3" colspan="1">TIPO FISCAL</td>
                                                                                            <td class="titulo3" colspan="1">NOME</td>                                              
                                                                                            <td class="titulo3" colspan="1">CPF</td>
                                                                                            <td class="titulo3" colspan="1">ENT. COMPET.</td>
                                                                                            <td class="titulo3" colspan="1">REGISTRO OU INSC.</td>
                                                                                            <td class="titulo3" colspan="1">E-MAIL</td>
                                                                                            <td class="titulo3" colspan="1">TELEFONE</td>
                                                                                        </tr>
                                                                                    </thead>
                                                                                    <tbody class="textonormal" id="mostrartbfiscais">
                                                                                    
                                                                                    <?php 
                                                                                        $auxAnt = array();
                                                                                        if(!empty($DadosDocFiscaisFiscal)){
                                                                                            foreach($DadosDocFiscaisFiscal as $fiscal){ $situacao = $fiscal->docsituacao; 
                                                                                                if( strtoupper($fiscal->remover) == "N"){
                                                                                                        if(!in_array($fiscal->fiscalcpf,$auxAnt)){                                                            
                                                                                    ?>
                                                                                                <tr>
                                                                                                    <td <?php echo $bloqueiacampo?'disabled="disabled"':'';?>> 
                                                                                                        <input type="radio" name="fiscais" <?php echo $bloqueiacampo?'disabled="disabled"':'';?> value="<?php echo $fiscal->fiscalcpf.'-'.$fiscal->docsequ;?>">
                                                                                                    </td>
                                                                                                    <td class="textonormal" style="font-size: 10.6667px;"><?php echo $fiscal->tipofiscal;?></td>
                                                                                                    <td class="textonormal" style="font-size: 10.6667px;"><?php echo $fiscal->fiscalnome;?></td>
                                                                                                    <td class="textonormal" style="font-size: 10.6667px;"><?php echo $fiscal->fiscalcpf;?></td>
                                                                                                    <td class="textonormal" style="font-size: 10.6667px;"><?php echo $fiscal->tipofical;?></td>
                                                                                                    <td class="textonormal" style="font-size: 10.6667px;"><?php echo $fiscal->tipofical;?></td>
                                                                                                    <td class="textonormal" style="font-size: 10.6667px;"><?php echo $fiscal->fiscalemail;?></td>
                                                                                                    <!-- <td><?php echo $fiscal->fiscaltipo;?></td> -->
                                                                                                    <td class="textonormal" style="font-size: 10.6667px;"><?php echo $fiscal->fiscaltel;?></td>
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
                                                                            <td  colspan="9" style="itens-align:right;">
                                                                                <button class="botao" type="button" id="removefiscal" style="float: right;">Remover Fiscal</button>
                                                                                <button class="botao" type="button" id="manterfiscal" style="float: right;">Manter Fiscal</button>
                                                                            </td>
                                                                        </tr>
                                                                        <!-- <tr>
                                                                            <td class="titulo3" bgcolor="#bfdaf2" colspan="4">Situação :</td>
                                                                            <td class="textonormal" id="situacaoFiscal"><?php echo $situacao;?></td>
                                                                        </tr> -->
                                                                    </tfoot>
                                                                  </table>
                                                                </td>
                                                                                                                          
                                                            <tr>
                                                                        <tr class="titulo3" bgcolor="#bfdaf2"><th colspan="3" scope="colgroup">ANEXAR DOCUMENTOS*</th></tr>
                                                                </tr>
                                                                <tr>
                                                                    <td colspan="3">
                                                                                <table id="tabelaficais" bgcolor="#bfdaf2" class="textonormal" width="100%">
                                                                                    <tbody >
                                                                                    <tr>
                                                                                        <td bgcolor="#DCEDF7">Anexação de Documentos</td>
                                                                                        <td colspan="1" style="border:none"> 
                                                                                            <iframe src="formupload.php" id="frameArquivo" height="39" width="520"  name="frameArquivo" frameborder="0"></iframe>
                                                                                        </td>
                                                                                    </tr>
                                                                                    <!-- Inicio upload carregando  -->
                                                                                    <div class="load" id="loadArquivo" style="display: none;"> 
                                                                                        <div class="load-content" >
                                                                                        <img src="../midia/loading.gif" alt="Carregando">
                                                                                        <spam>Carregando...</spam>
                                                                                        </div>
                                                                                    </div>    
                                                                                    <!-- Fim upload carregando  -->  
                                                                                    </tbody>
                                                                                    <tfoot id="FootDOcFiscal" >
                                                                                        <tr class="FootFiscaisDoc">
                                                                                            <td></td>
                                                                                            <td colspan="4">ARQUIVO</td>
                                                                                            <td colspan="4">DATA DA INCLUSÃO</td>
                                                                                        </tr>
                                                                                        <?php 
                                                                                        if(!empty($DadosDocAnexo)){
                                                                                            $k=0;
                                                                                        foreach($DadosDocAnexo as $anexo){ ?>
                                                                                        <tr bgcolor="#ffffff">
                                                                                            <td><input type="radio" name="docanex" value="<?php echo $anexo->sequdocumento.'*'.$anexo->nomearquivo;?>"></td>
                                                                                            <td colspan="4"> <?php echo $anexo->nomearquivo;?></td>
                                                                                            <td colspan="4"> <?php echo $anexo->datacadasarquivo;?></td>
                                                                                        </tr>
                                                                                        <?php $k++; }
                                                                                            }else{
                                                                                                echo ' <tr bgcolor="#ffffff">';
                                                                                                echo ' <td colspan="8" bgcolor="#ffffff">Nenhum documento informado</td>';
                                                                                                echo ' </tr>';
                                                                                            }
                                                                                         ?>
                                                                                        <tr bgcolor="#ffffff">
                                                                                            <td colspan="8" align="center">
                                                                                                <button type="button" class="botao" id="btnIncluirAnexo" onclick="Subirarquivo()">Incluir Documento</button>
                                                                                                <button type="button" class="botao" id="btnRemoveAnexo">Retirar Documento</button>
                                                                                            </td>
                                                                                        <tr>       
                                                                                    </tfoot>    
                                                                                </table>   
                                                                                </br>
                                                                                <tr>
                                                                                    <td colspan="8"><button type="button" name="salvaContrato" class="botao" id="salvaContrato" style="float:right">Salvar</button></td>
                                                                                </tr>
                                                                                <input type="hidden" id="Destino" name="Destino">
                                                                    </td>
                                                                </tr>
                                                            </table>
        </table>
    </form>
    <div class="modal" id="modal"> 
        <div class="modal-content" >
         
        </div>
    </div> 
   
    <!-- Fim Modal -->
    </body>
    </html>
    <?php
    exit;
}


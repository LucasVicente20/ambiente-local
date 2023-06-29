<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: AbaContrato.php
# Autor:    Eliakim Ramos | João Madson
# Data:     10/12/2019
# Objetivo: Programa de incluir contrato
#-------------------------------------------------------------------------
# Exibe Aba Membro de Comissão - Formulário A #
#-------------------------------------------------------------------------
#Alterado : Osmar Celestino
# Data: 29/03/2021
# Objetivo: CR CR #245212  Correção da  transformação da string e-mail pra Upper, manter como o cliente digitou.
#---------------------------------------------------------------------------
# Autor:    Madson Felix
# Data:     28/04/2021
# CR #246939
# -------------------------------------------------------------------------
# -------------------------------------------------------------------------
# Portal da Compras
# Programa: CadContratoConsolidadoPesquisar.php
# Autor:    Marcello Calvalcanti
# Data:     21/07/2021
# CR:       249907
# ------------------------------------------------------------------------
# -------------------------------------------------------------------------
# Portal da Compras
# Programa: AbaContratoManter.php
# Autor:    Marcello Calvalcanti
# Data:     30/07/2021
# CR:       251537
# Correção posterior feita por Madson |Botão Salvar não estava aparecendo para usuário
# ------------------------------------------------------------------------
# Alterado : Osmar Celestino
# Data: 03/12/2021
# Objetivo: CR #256381
#---------------------------------------------------------------------------
# ------------------------------------------------------------------------
# Alterado : Eliakim Ramos
# Data: 06/01/2022
# Objetivo: CR #257362
#---------------------------------------------------------------------------
# Alterado : Osmar Celestino
# Data: 22/02/2022
# Objetivo: CR #259193
#---------------------------------------------------------------------------
# Alterado : Lucas Vicente
# Data: 16/05/2022
# Objetivo: CR #263182
#---------------------------------------------------------------------------
# Alterado : Lucas Vicente
# Data: 24/05/2022
# Objetivo: CR #263119
#---------------------------------------------------------------------------
# Alterado : Lucas Vicente
# Data: 01/08/2022
# Objetivo: CR #252340
#---------------------------------------------------------------------------
# Alterado : Lucas Vicente
# Data: 15/08/2022
# Objetivo: CR #252254
#---------------------------------------------------------------------------
# Autor: Lucas Vicente
# Data:  15/02/2023
# Objetivo: CR #275671
# -------------------------------------------------------------------------
# Autor: Osmar Celestino
# Data:  11/04/2023
# Objetivo: CR #269872
# -------------------------------------------------------------------------

function ExibeAbaContratoManter(){ 
    $ObjContrato = new ContratoManter();
    $ObjContratoInc = new Contrato();
    $objMedicao = new ClassMedicao();
    $objContratoFuncGeral = new ContratosFuncoesGerais();
    $dadosContratos = (object) array();
    $dadosGarantia = $ObjContrato->GetListaGarantiaDocumento();
    $tiposCompra = $ObjContratoInc->get_tipoCompraSemParametro();
    $orgaosLicitantes = $ObjContrato->GetOrgao();
    $categoriasProcesso = $ObjContrato->getCategoriaProcesso();
    $idRegistro    = '';
    $URLPublicaDom = "../calendario.php?Formulario=CadContratoManter&Campo=dataPublicacaoDom";
    $URLTranscricao  = "../calendario.php?Formulario=CadContratoManter&Campo=dataTranscricao";
    $URLvigenciaDataInicio  = "../calendario.php?Formulario=CadContratoManter&Campo=vigenciaDataInicio";
    $URLvigenciaDataFim  = "../calendario.php?Formulario=CadContratoManter&Campo=vigenciaDataTermino";
    $URLExecucaoDataInicio  = "../calendario.php?Formulario=CadContratoManter&Campo=execucaoDataInicio";
    $URLExecucaoDataFim  = "../calendario.php?Formulario=CadContratoManter&Campo=execucaoDataTermino";
    
    if ($_SERVER['REQUEST_METHOD'] == "POST") {
       if(!empty($_POST['idregistro'])){
            $_SESSION['idregistro'] = $_POST['idregistro'];
       }
        $idRegistro = !empty($_POST['idregistro'])? $_POST['idregistro']:$_SESSION['idregistro'];
        $csolcosequ ="";
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
        if(!empty($tiratracoscc[0][0])){
            $_SESSION['csolcosequ'] = $tiratracoscc[0][0];
            unset($_SESSION['citelpnuml']);
            $csolcosequ = $_SESSION['csolcosequ'];
            // $aforcrsequ = $_SESSION["fornsequ".$csolcosequ];
            $aforcrsequ = $_SESSION["fornsequ".$csolcosequ];
            $corglicodi = $_SESSION["org".$csolcosequ];
            $cctrpciden = $_SESSION["flagCPFPJ".$csolcosequ];
            for($i=0;$i<count($tiratracoscc);$i++){
                $_SESSION['citelpnuml'][$i] = $tiratracoscc[$i][1]; //adaptar 
            }
            $citelpnuml = $_SESSION['citelpnuml'];
        }else{
            $_SESSION['dadosSalvar']['csolcosequ'] = $csolcosequ = $_SESSION['csolcosequ'];
            $_SESSION['dadosSalvar']['aforcrsequ'] = $aforcrsequ = $_SESSION["fornsequ".$csolcosequ];
            $corglicodi = $_SESSION["org".$csolcosequ];
            $cctrpciden = $_SESSION["flagCPFPJ".$csolcosequ];
            $citelpnuml = $_SESSION['citelpnuml'];
        }
        if(!empty($csolcosequ) && !empty($aforcrsequ)){
            $_SESSION['dadosContratado']  = $ObjContratoInc->getFornecedorDados($aforcrsequ);
            $_SESSION['dadosObjOrgao']    = $ObjContratoInc->GetOrgaoEDescObj($csolcosequ);
            $dadosSalvar['csolcosequ'] = $csolcosequ;
            $dadosSalvar['aforcrsequ'] = $aforcrsequ;
         }
         //Inicio da coleta dos dados selecionados em pesquisa via post  MADSON 
         if(!is_null($_POST['sccselec-'.$_SESSION['csolcosequ']])){
             $_SESSION['origemScc']       = $ObjContratoInc->corrigeString($_POST['origselec-'.$_SESSION['csolcosequ']]);
             $_SESSION['numScc']          = $_POST['sccselec-'.$_SESSION['csolcosequ']];
             $_SESSION['CpfCnpj']         = $_POST['cpfselec-'.$_SESSION['csolcosequ']];
         }
         $_SESSION['dadosSalvar']['origemScc'] = $dadosSalvar['origemScc'] = $_SESSION['origemScc'];
         $dadosSalvar['numScc']    = $_SESSION['numScc']   ;
         $_SESSION['dadosManter']['CpfCnpContratado'] = $dadosSalvar['CpfCnpj']   = $_SESSION['CpfCnpj']  ; 
         $_SESSION['dadosSalvar']['citelpnuml'] = $dadosSalvar['citelpnuml']   = $_SESSION['citelpnuml'];
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

        $ValorDaParcela = $_SESSION['ValorDaParcela'];
        // var_dump($ValorDaParcela);die;
        $_SESSION['dadosSalvar']['orgao_licitante'] = $orgLicitante = $_SESSION['dadosObjOrgao']->eorglidesc; //Usar para mostrar na tela qual deles é e para a Masc
        $_SESSION['dadosManter']['objeto'] = $objetoDesc   = $_SESSION['dadosObjOrgao']->esolcoobje; 
        $observDesc   = $_SESSION['dadosObservacao'] = $dadosContratos->obsenceramento;   
        $razSocial    = $_SESSION['dadosContratado']->nforcrrazs;
        $Rua          = $_SESSION['dadosContratado']->eforcrlogr;
        $numEnd       = $_SESSION['dadosContratado']->aforcrnume;
        $complEnd     = $_SESSION['dadosContratado']->eforcrcomp;
        $Bairro       = $_SESSION['dadosContratado']->eforcrbair;
        $UF           = $_SESSION['dadosContratado']->cforcresta;
        $Cidade       = $_SESSION['dadosContratado']->nforcrcida;
        $Cep          = $_SESSION['dadosContratado']->cceppocodi;
        $telefone     = $_SESSION['dadosContratado']->aforcrtels;
       
        $dadosSalvar['corglicodi']      = $corglicodi;
        $dadosSalvar['ectrpcobje']      = $ObjContratoInc->corrigeString($objetoDesc)  ;
        $_SESSION['dadosManter']['ectrpcraza'] = $dadosSalvar['ectrpcraza']      = $ObjContratoInc->corrigeString($razSocial)   ;
        $_SESSION['dadosManter']['ectrpclogr'] = $dadosSalvar['ectrpclogr']      = $ObjContratoInc->corrigeString($Rua)         ;
        $_SESSION['dadosManter']['actrpcnuen'] = $dadosSalvar['actrpcnuen']      = $ObjContratoInc->corrigeString($numEnd)      ;
        $_SESSION['dadosManter']['ectrpccomp'] = $dadosSalvar['ectrpccomp']      = $ObjContratoInc->corrigeString($complEnd)    ;
        $_SESSION['dadosManter']['ectrpcbair'] = $dadosSalvar['ectrpcbair']      = $ObjContratoInc->corrigeString($Bairro)      ;
        $_SESSION['dadosManter']['cforcresta'] = $dadosSalvar['cctrpcesta']      = $ObjContratoInc->corrigeString($UF)          ;
        $_SESSION['dadosManter']['nctrpccida'] = $dadosSalvar['nctrpccida']      = $ObjContratoInc->corrigeString($Cidade)      ;
        $dadosSalvar['ectrpctlct']      = $_SESSION['dadosContratado']->aforcrtels;  //telefone do contratado para inserir em tbcontratosfpc
        $dadosSalvar['cctrpcccep']      = $_SESSION['dadosContratado']->cceppocodi;  //CEP do contratado para inserir em tbcontratosfpc
         $_SESSION['dadosSalvar'] = $dadosSalvar;
         $valorCalculado = 0;
         if(!empty($_POST['seqScc'])){
                $valoresItems = $ObjContrato->selectsContratoIncluir($dadosSalvar);
                for($it = 0;  $it < count($valoresItems); $it++){
                    if($dadosSalvar['origemScc'] == "LICITAÇÃO" || $dadosSalvar['origemScc'] == "LICITACAO"){
                       $valorUnitário = $valoresItems[$it]->vitelpvlog;
                        $quantItem     = $valoresItems[$it]->aitelpqtso;
                    }else{
                        $valorUnitário = $valoresItems[$it]->vitescunit;
                        $quantItem     = $valoresItems[$it]->aitescqtso;
                    }   
                      $valorCalculado += (floatval($valorUnitário) * floatval($quantItem));
                      $_SESSION['valorCalculado'] = $valorCalculado;
                }
                
               $dadosSalvar['vctrpcvlor'] = $valorCalculado;
         }
        $dadosContratos     = $ObjContrato->GetDadosContratoSelecionado($idRegistro);
       
        $_SESSION['dadosSalvar']['numcontrato'] = !empty($_SESSION['dadosManter']['numcontrato'])?$_SESSION['dadosManter']['numcontrato']:$dadosContratos->ncontrato;
        $Aditivos = $ObjContrato->GetAditivos($idRegistro);
        $Apostilamento = $ObjContrato->GetApostilamento($idRegistro);
        $Medicao = $ObjContrato->GetMedicao($idRegistro);
        $vtAditivo = $ObjContrato->GetValorTotalAdtivo($idRegistro);
        $vtApost = $ObjContrato->GetValorTotalApostilamento($idRegistro);
         $valorTotalMedicao = $objMedicao->getValorTotalMedicao($idRegistro);
        if(!empty($_SESSION['valorCalculado'])){
            $valororiginal = $_SESSION['valorCalculado'];
        }else{
            $valororiginal = !empty($valorCalculado)?$valorCalculado:floatval($dadosContratos->valororiginal);
        }
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
        $funcaoDesfazerCancelamento = "desfazerCancelamento()";
        $cusupocodi = $_SESSION['_cusupocodi_'];
        $manterContratoEspecial = $ObjContrato->checaUsuarioManterEspecial($cusupocodi); // checa se o usuário tem acesso ao manter especial
        if($manterContratoEspecial == false && ($temAditivo || $temApostilamento || $temMedicao || $situacaoContrato->esitdcdesc=='ENCERRADO' || $situacaoContrato->esitdcdesc=='CONCLUSO')){
            $bloqueiacampo = true;
            $nomeBotao = "Salvar";  // Madson, Usuário solicitou a retirada de "Anexo" do nome.
            $botaoNomeSalvarAnexo = 'true'; // Esta variavel informa ao js se a alteração é apenas para o anexo ou para o form todo na função enviar, devido a nescessidade de retirar o 'Anexo' do nome do botão; 
            $exibeLupa = 'style="display:none"';
            $funcaoExcluir = "avisoexclusao('Não é possível excluir o contrato, pois existe aditivo, apostilamento ou medição cadastrada.')";
        }
        if($manterContratoEspecial == true && ($temAditivo || $temApostilamento || $temMedicao || $situacaoContrato->esitdcdesc=='ENCERRADO' || $situacaoContrato->esitdcdesc=='CONCLUSO')){
            $funcaoExcluir = "avisoexclusao('Não é possível excluir o contrato, pois existe aditivo, apostilamento ou medição cadastrada.')";
        }
        if( $situacaoContrato->esitdcdesc=='CONCLUSO' ||  $situacaoContrato->esitdcdesc=='CANCELADO'){
            $exibeBotao = 'style="display:none"';
            
            $exibeLupa = 'style="display:none"';
            if($manterContratoEspecial == false){
                $bloqueiacampo = true;
            }
            $funcaoCancelar = "avisoexclusao('Não é possível cancelar o contrato, por que ele ja foi concluído.')";
        }
        if( $situacaoContrato->esitdcdesc=='CANCELADO'){
            $exibeBotaoEncerramento = 'style="display:none"';
        }
        if( $situacaoContrato->esitdcdesc=='CONCLUSO' || $situacaoContrato->esitdcdesc=='ENCERRADO'){
                $nomeBotaoEncerrar = "Desfazer Encerramento";
                $funcaoEncerrar = 'desfazEncerramentoContrato()';
        }
        if( $situacaoContrato->esitdcdesc!='CADASTRADO'  ){
            $funcaoExcluir = "avisoexclusao('Não é possivel excluir, o contrato não esta mais na fase de cadastro')";
        }
        if($manterContratoEspecial == true){
            $bloqueiacampo = false;
        }   
        $_SESSION['Botao'] = $_POST['Botao'];
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
                            //'fiscalmatricula' => $f->fiscalmatricula,
                            'fiscalcpf'       => $ObjContrato->MascarasCPFCNPJ($f->fiscalcpf),
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
                                    //'fiscalmatricula' => $k->fiscalmatricula,
                                    'fiscalcpf'       => $ObjContrato->MascarasCPFCNPJ($k->fiscalcpf),
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
                                    //'fiscalmatricula' => $k->fiscalmatricula,
                                    'fiscalcpf'       => $ObjContrato->MascarasCPFCNPJ($k->fiscalcpf),
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
                    if($doc['remover'] != "S"){
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
    } else if(!empty($_SESSION['idregistro'])) {
       //coloquei esse if para que se o id registro stiver na sessão  mesmo sem o post ele tras  os dados  do contrato
        $idRegistro = !empty($_POST['idregistro'])? $_POST['idregistro']:$_SESSION['idregistro'];
        $csolcosequ ="";
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
        if(!empty($tiratracoscc[0][0])){
            $_SESSION['csolcosequ'] = $tiratracoscc[0][0];
            unset($_SESSION['citelpnuml']);
            $csolcosequ = $_SESSION['csolcosequ'];
            // $aforcrsequ = $_SESSION["fornsequ".$csolcosequ];
            $aforcrsequ = $_SESSION["fornsequ".$csolcosequ];
            $corglicodi = $_SESSION["org".$csolcosequ];
            $cctrpciden = $_SESSION["flagCPFPJ".$csolcosequ];
            for($i=0;$i<count($tiratracoscc);$i++){
                $_SESSION['citelpnuml'][$i] = $tiratracoscc[$i][1]; //adaptar 
            }
            $citelpnuml = $_SESSION['citelpnuml'];
        }else{
            $_SESSION['dadosSalvar']['csolcosequ'] = $csolcosequ = $_SESSION['csolcosequ'];
            $_SESSION['dadosSalvar']['aforcrsequ'] = $aforcrsequ = $_SESSION["fornsequ".$csolcosequ];
            $corglicodi = $_SESSION["org".$csolcosequ];
            $cctrpciden = $_SESSION["flagCPFPJ".$csolcosequ];
            $citelpnuml = $_SESSION['citelpnuml'];
        }
        if(!empty($csolcosequ) && !empty($aforcrsequ)){
            $_SESSION['dadosContratado']  = $ObjContratoInc->getFornecedorDados($aforcrsequ);
            $_SESSION['dadosObjOrgao']    = $ObjContratoInc->GetOrgaoEDescObj($csolcosequ);
            $dadosSalvar['csolcosequ'] = $csolcosequ;
            $dadosSalvar['aforcrsequ'] = $aforcrsequ;
         }
         //Inicio da coleta dos dados selecionados em pesquisa via post  MADSON 
         if(!is_null($_POST['sccselec-'.$_SESSION['csolcosequ']])){
             $_SESSION['origemScc']       = $ObjContratoInc->corrigeString($_POST['origselec-'.$_SESSION['csolcosequ']]);
             $_SESSION['numScc']          = $_POST['sccselec-'.$_SESSION['csolcosequ']];
             $_SESSION['CpfCnpj']         = $_POST['cpfselec-'.$_SESSION['csolcosequ']];
         }
         $_SESSION['dadosSalvar']['origemScc'] = $dadosSalvar['origemScc'] = $_SESSION['origemScc'];
         $dadosSalvar['numScc']    = $_SESSION['numScc']   ;
         $_SESSION['dadosManter']['CpfCnpContratado'] = $dadosSalvar['CpfCnpj']   = $_SESSION['CpfCnpj']  ; 
         $_SESSION['dadosSalvar']['citelpnuml'] = $dadosSalvar['citelpnuml']   = $_SESSION['citelpnuml'];
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

         
        $_SESSION['dadosSalvar']['orgao_licitante'] = $orgLicitante = $_SESSION['dadosObjOrgao']->eorglidesc; //Usar para mostrar na tela qual deles é e para a Masc
        $_SESSION['dadosManter']['objeto'] = $objetoDesc   = $_SESSION['dadosObjOrgao']->esolcoobje; 
        $observDesc   = $_SESSION['dadosObservacao'] = $dadosContratos->obsenceramento;   
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
        $_SESSION['dadosManter']['ectrpcraza'] = $dadosSalvar['ectrpcraza']      = $ObjContratoInc->corrigeString($razSocial)   ;
        $_SESSION['dadosManter']['ectrpclogr'] = $dadosSalvar['ectrpclogr']      = $ObjContratoInc->corrigeString($Rua)         ;
        $_SESSION['dadosManter']['actrpcnuen'] = $dadosSalvar['actrpcnuen']      = $ObjContratoInc->corrigeString($numEnd)      ;
        $_SESSION['dadosManter']['ectrpccomp'] = $dadosSalvar['ectrpccomp']      = $ObjContratoInc->corrigeString($complEnd)    ;
        $_SESSION['dadosManter']['ectrpcbair'] = $dadosSalvar['ectrpcbair']      = $ObjContratoInc->corrigeString($Bairro)      ;
        $_SESSION['dadosManter']['cforcresta'] = $dadosSalvar['cctrpcesta']      = $ObjContratoInc->corrigeString($UF)          ;
        $_SESSION['dadosManter']['nctrpccida'] = $dadosSalvar['nctrpccida']      = $ObjContratoInc->corrigeString($Cidade)      ;
        $dadosSalvar['ectrpctlct']      = $_SESSION['dadosContratado']->aforcrtels;  //telefone do contratado para inserir em tbcontratosfpc
        $dadosSalvar['cctrpcccep']      = $_SESSION['dadosContratado']->cceppocodi;  //CEP do contratado para inserir em tbcontratosfpc
         $_SESSION['dadosSalvar'] = $dadosSalvar;
         $valorCalculado = 0;
         if(!empty($_POST['seqScc'])){
                $valoresItems = $ObjContrato->selectsContratoIncluir($dadosSalvar);
                for($it = 0;  $it < count($valoresItems); $it++){
                    if($dadosSalvar['origemScc'] == "LICITAÇÃO" || $dadosSalvar['origemScc'] == "LICITACAO"){
                       $valorUnitário = $valoresItems[$it]->vitelpvlog;
                        $quantItem     = $valoresItems[$it]->aitelpqtso;
                    }else{
                        $valorUnitário = $valoresItems[$it]->vitescunit;
                        $quantItem     = $valoresItems[$it]->aitescqtso;
                    }   
                      $valorCalculado += (floatval($valorUnitário) * floatval($quantItem));
                      $_SESSION['valorCalculado'] = $valorCalculado;
                }
                
               $dadosSalvar['vctrpcvlor'] = $valorCalculado;
         }
        $dadosContratos     = $ObjContrato->GetDadosContratoSelecionado($idRegistro);
        $_SESSION['dadosSalvar']['numcontrato'] = !empty($_SESSION['dadosManter']['numcontrato'])?$_SESSION['dadosManter']['numcontrato']:$dadosContratos->ncontrato;
        $Aditivos = $ObjContrato->GetAditivos($idRegistro);
        $Apostilamento = $ObjContrato->GetApostilamento($idRegistro);
        $Medicao = $ObjContrato->GetMedicao($idRegistro);
        $vtAditivo = $ObjContrato->GetValorTotalAdtivo($idRegistro);
        $vtApost = $ObjContrato->GetValorTotalApostilamento($idRegistro);
         $valorTotalMedicao = $objMedicao->getValorTotalMedicao($idRegistro);
        if(!empty($_SESSION['valorCalculado'])){
            $valororiginal = $_SESSION['valorCalculado'];
        }else{
            $valororiginal = !empty($valorCalculado)?$valorCalculado:floatval($dadosContratos->valororiginal);
        }
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
        $funcaoDesfazerCancelamento = "desfazerCancelamento()";
        $cusupocodi = $_SESSION['_cusupocodi_'];
        $manterContratoEspecial = $ObjContrato->checaUsuarioManterEspecial($cusupocodi); // checa se o usuário tem acesso ao manter especial
        if($manterContratoEspecial == false && ($temAditivo || $temApostilamento || $temMedicao || $situacaoContrato->esitdcdesc=='ENCERRADO' || $situacaoContrato->esitdcdesc=='CONCLUSO')){
            $bloqueiacampo = true;
            $nomeBotao = "Salvar";  // Madson, Usuário solicitou a retirada de "Anexo" do nome.
            $botaoNomeSalvarAnexo = 'true'; // Esta variavel informa ao js se a alteração é apenas para o anexo ou para o form todo na função enviar, devido a nescessidade de retirar o 'Anexo' do nome do botão; 
            $exibeLupa = 'style="display:none"';
            $funcaoExcluir = "avisoexclusao('Não é possível excluir o contrato, pois existe aditivo, apostilamento ou medição cadastrada.')";
        }
        if($manterContratoEspecial == true && ($temAditivo || $temApostilamento || $temMedicao || $situacaoContrato->esitdcdesc=='ENCERRADO' || $situacaoContrato->esitdcdesc=='CONCLUSO')){
            $funcaoExcluir = "avisoexclusao('Não é possível excluir o contrato, pois existe aditivo, apostilamento ou medição cadastrada.')";
        }
        if( $situacaoContrato->esitdcdesc=='CONCLUSO' ||  $situacaoContrato->esitdcdesc=='CANCELADO'){
            $exibeBotao = 'style="display:none"';
            
            $exibeLupa = 'style="display:none"';
            if($manterContratoEspecial == false){
                $bloqueiacampo = true;
            }
            $funcaoCancelar = "avisoexclusao('Não é possível cancelar o contrato, por que ele ja foi concluído.')";
        }
        if( $situacaoContrato->esitdcdesc=='CANCELADO'){
            $exibeBotaoEncerramento = 'style="display:none"';
        }
        if( $situacaoContrato->esitdcdesc=='CONCLUSO' || $situacaoContrato->esitdcdesc=='ENCERRADO'){
                $nomeBotaoEncerrar = "Desfazer Encerramento";
                $funcaoEncerrar = 'desfazEncerramentoContrato()';
        }
        if( $situacaoContrato->esitdcdesc!='CADASTRADO'  ){
            $funcaoExcluir = "avisoexclusao('Não é possivel excluir, o contrato não esta mais na fase de cadastro')";
        }
        if($manterContratoEspecial == true){
            $bloqueiacampo = false;
        }   
        $_SESSION['Botao'] = $_POST['Botao'];
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
                            'fiscalcpf'       => $ObjContrato->MascarasCPFCNPJ($f->fiscalcpf),
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
                                    'fiscalcpf'       => $ObjContrato->MascarasCPFCNPJ($k->fiscalcpf),
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
                                    'fiscalcpf'       => $ObjContrato->MascarasCPFCNPJ($k->fiscalcpf),
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
                    if($doc['remover'] != "S"){
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
        function AbreJanelaDocumentos(url, largura, altura) {
		window.open(
			url,
			'pagina',
			'status=no,scrollbars=yes,left=20,top=150,width=' + largura	+ ',height=' + altura
		);
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
                $('html, body').animate({scrollTop:0}, 'slow');
                $('#tdload').show();
                if(!TestaCPF($("#cpfrepresenlegal").val())){
                    $('#tdload').hide();
                    avisoexclusao("Informe: Um CPF válido para o representante.");
                    return false;
                }
                if(!TestaCPF($("#cpfgestor").val())){
                    $('#tdload').hide();
                    avisoexclusao("Informe: Um CPF válido para o gestor.");
                    return false;
                }
                $.post("postDadosManter.php",$("#FormContrato").serialize(), function(data){
                    const response = JSON.parse(data);
                    if(response.status){
                        $('#tdload').hide();
                        window.location.href = "./CadContratoPesquisar.php?m="+response.msm+"&h=show";
                    }else if(!response.status){
                        $('html, body').animate({scrollTop:0}, 'slow');
                        $('#tdload').hide();
                        $("#tdmensagem").show();
                        let elmnt = document.querySelector("body");
                              elmnt.scrollTop = -1000;
                              elmnt.scrollLeft = -1000;
					    $(".mensagem-texto").html(response.msm);
                    }
                    
			    });
            }else if(btnSalvarContrato == "Salvar" ){
                     $("#op").val('UpdateContratoAnexoFiscal');
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
        $("#btnSalvarModal").live("click",function(){
                if(!TestaCPF($("#cpffiscal").val())){
                        avisoModal("Informe: Um CPF válido!");
                        return false;
                    }else{
                        limpaMensagem();
                    }
              
                    console.log('aqui');  
                var formulario = {
                 
                    'op' :'insertFiscal',
                    'tipofiscalr' : ($("#radio-tipofiscal-interno").prop("checked"))?$("#radio-tipofiscal-interno").val():$("#radio-tipofiscal-externo").val(),
                    'nomefiscal': $("#nomefiscal").val(),
                    'matfiscal'   : $("#matfiscal").val(),
                    'cpffiscal'    : $("#cpffiscal").val(),
                    'entidadefiscal': $("#entidadefiscal").val(),
                    'RegInsfiscal': $("#RegInsfiscal").val(),
                    'emailfiscal': $("#emailfiscal").val(),
                    'telfiscal': $("#telfiscal").val(),
                };
                $.post("postDadosManter.php",formulario,function(data){
                    ObjJson = JSON.parse(data);
                    if(!ObjJson.Sucess){
                        $("#tdmensagemM").show();
					    $(".mensagem-textoM").html(ObjJson.msm);
                      }else{
                        $("#modal").hide();
                        $(".dadosFiscal").html(CriaTabelaFiscalView(ObjJson.dados));
                        $("#mostrartbfiscais").html(CriaTabelaFiscalView(ObjJson.dados));
                        $("#btnPesquisaModal").hide();
                        $(".modal-content").html(data);
                        $("#modal").hide();
                        $(".error").html("Atenção");
                        $(".error").css("color","#007fff");
                        avisoexclusao("Informe: Fiscal incluído, com sucesso!");
                        
                      }
                });
                return false;
            });
              
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
            const just = $("#observacaoJust").val();
            $("#JustTR").css("display","block");
            $("#observacaoJust").css("display","block");
            
            if (just.length<1){
                avisoexclusao("Insira uma justificativa para cancelamento!");
                
                }else{  
                    if(confirm("Deseja realmente cancelar esse contrato?")){
                            const codSequCont = $("#idRegistro").val();
                            const obs = $("#observacaoJust").val();
                            $.post("postDadosManter.php",{"codContrato":codSequCont, "obs":obs, "op":"CancelarContrato"}, function(data){
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
                    
        }        

        function desfazerCancelamento(){
           if(confirm("Deseja realmente desfazer o cancelamento desse contrato?")){
                const codSequCont = $("#idRegistro").val();
                $.post("postDadosManter.php",{"codContrato":codSequCont, "op" :"DesfCancelarContrato"}, function(data){
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
            const just = $("#observacaoJust").val();
            $("#JustTR").css("display","block");
            $("#observacaoJust").css("display","block");
            
            
            if (just.length<1){
                avisoexclusao("Insira uma justificativa para o encerramento!");
                
            }else{
                if(confirm("Deseja realmente encerrar esse contrato?")){
                    const codSequCont = $("#idRegistro").val();
                    const obs = $("#observacaoJust").val();
                    $.post("postDadosManter.php",{"codContrato":codSequCont,"obs":obs, "op":"EncerrarContrato"}, function(data){
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
                    window.location.reload(false);			 
                });
        }
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
                                tabelaHtml +=  objJson[i].nforcrrazs;
                                tabelaHtml += '</td>';
                                tabelaHtml += '<td>';
                                tabelaHtml += '<button type="button" class="botao" onclick="retiraFornecedor(\''+objJson[i].aforcrsequ+'\')">Remover</button>';
                                tabelaHtml += '</td>';
                                tabelaHtml += '</tr>';
                            }
                        }else{
                                tabelaHtml += '<tr>';
                                tabelaHtml += '<td colspan="2">';
                                tabelaHtml += 'Não há registro deste fornecedor';
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
                   //tabelaHtml += '<td>';
                    //tabelaHtml += 'MATRÍCULA';
                   //tabelaHtml += '</td>';
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
                            tabelaHtml +=  (objJson[i].efiscdmtfs != '' && objJson[i].efiscdmtfs != null )?objJson[i].efiscdmtfs:''; //CR#257362 Colouqei uma validação para o null que parentemente ta vindo do banco
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
                            tabelaHtml +=  (objJson[i].nfiscdmlfs != '')?objJson[i].nfiscdmlfs.toString():'';
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
                                    tabelaHtml +=  (objJson[i].fiscalcpf != '')?objJson[i].fiscalcpf:'';
                                    tabelaHtml += '</td>';
                                    tabelaHtml += '<td>';
                                    tabelaHtml +=  (objJson[i].ent != '')?objJson[i].ent:'';
                                    tabelaHtml += '</td>';
                                    tabelaHtml += '<td>';
                                    tabelaHtml +=  (objJson[i].registro != '')?objJson[i].registro:'';
                                    tabelaHtml += '</td>';
                                    tabelaHtml += '<td>';
                                    tabelaHtml +=  (objJson[i].fiscalemail != '')?objJson[i].fiscalemail.toString():'';
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

                        var texto = '';
                        var xd = numcpfcnpj.length;
                        if(xd === 14){
                            texto = 'CPf Contratado: ';
                    
                        }
                        else{
                            texto = 'CNPJ Contratado: ';
                        }
                        $('#alteracpfcnpj').html(texto);
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
            var radio_cnpj_cpf = $("input[name='CNPJ_CPF']:checked").val();
            window.localStorage.clear();
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
                var selectHtml 
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
                var selectHtml 
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
                 var selectHtml 
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
                 var selectHtml 
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
            $('#btnIncluirAnexo').live("click",()=>{
                $('#loadArquivo').show();
            })
            $("#btnRemoveAnexo").live("click",function(){
                $('#loadArquivo').show();
                const docanexselec = $("input[name='docanex']:checked").val();
                $.post("postDadosManter.php",{op:"RemoveDocAnex",marcador:docanexselec},function(data){
                    $('#loadArquivo').hide();
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
                        <a href="../index.php"><font color="#000000">Página Principal</font></a> > Contratos > Manter
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
                    <td width="150"></td>
                    <td class="textonormal">
                        <table  border=1px bordercolor="#75ADE6" cellspacing="0" cellpadding="3" summary=""  width="1024px" >
                             <thead colspan="3" class="titulo3 itens_material" align="center"  bgcolor="#75ADE6" valign="middle">
                                <td> 
                                <?php echo $manterContratoEspecial == true?'MANTER CONTRATO':'MANTER CONTRATO';?>
                                    
                                </td>
                              </thead>
                            <tr>
                                <td class="textonormal">
                                
                                    <table id="scc_material" summary="" width="100%" class="textonormal">
                                        <tbody border="0">
                                            <tr>
                                                <td align="left" colspan="4" > 
                                                        
                                                            <?php echo NavegacaoAbasManter(on,off); ?>                                                  
                                                            <tr>
                                                                <td  bgcolor="#DCEDF7">Número do Contrato/Ano:</td>
                                                                <td >
                                                                    <input id="numcontrato" type="text" name="numcontrato" class="numeroContrato" size="11" style="font-size: 10.6667px;" <?php echo $bloqueiacampo == true?'disabled="disabled"': '';?> value="<?php echo !empty($_SESSION['dadosManter']['numcontrato'])?$_SESSION['dadosManter']['numcontrato']:$dadosContratos->ncontrato;?>" onblur="verificaContrato()" maxlength="20" size="10">
                                                                </td>
                                                            </tr>
                                                            <?php
                                                            if(!empty($numScc) || !empty($SCC)){ 
                                                            ?>
                                                            <tr>
                                                                <td  bgcolor="#DCEDF7">Solici. de Compra/Contratação-SCC*</td>
                                                                <td class="inputs">
                                                                    <span id="panelGroupSolicitacao">
                                                                        <input id="solicitacaoCompra" type="text" maxlength="20" size="11" name="solicitacaoCompra" style="font-size: 10.6667px;" value="<?php echo !empty($numScc)?$numScc:$SCC;?>" disabled="disabled" <?php //if($manterContratoEspecial == true || $bloqueiacampo == true){echo '';}else{echo '';} ?>>
                                                                    </span>
                                                                       <a href="#" class="btn-pesquisa-scc" <?php echo $exibeLupa;?> >
                                                                            <img  src="../midia/lupa.gif" border="0">
                                                                        </a>
                                                                    </td>
                                                            </tr>
                                                            <tr>
                                                                <td bgcolor="#DCEDF7">Origem:</td>
                                                                <td class="inputs">
                                                                    <span id="origem" class="textonormal"> <?php echo !empty($origemScc)?$origemScc:$TipoCOmpra->etpcomnome; ?></span>
                                                                </td>
                                                            </tr>
                                                            <?php
                                                            }else{
                                                            ?>
                                                            <tr>
                                                                <td bgcolor="#DCEDF7">Origem:</td>
                                                                <td class="inputs">
                                                                        <select name="origemContrato" <?php echo $bloqueiacampo?'disabled="disabled"':'';?> size="1" title="Origem" style="width:315px; font-size: 10.6667px; ">
                                                                            
                                                                            <?php foreach($tiposCompra as $tipo){ 
                                                                                if(!empty($_SESSION['dadosManter']['origemContrato'])){
                                                                                    $selecionadoTipo = ($tipo->ctpcomcodi == $_SESSION['dadosManter']['origemContrato']) ? 'selected="selected"' : '';
                                                                                }else{
                                                                                    $selecionadoTipo = ($tipo->ctpcomcodi == $dadosContratos->codicompra) ? 'selected="selected"' : '';    
                                                                                }
                                                                            ?>
                                                                            <option class="textonormal" style="font-size: 10.6667px;" value="<?php echo $tipo->ctpcomcodi;?>" <?php echo $selecionadoTipo; ?> >
                                                                                <?php echo $tipo->etpcomnome; ?>
                                                                            </option>
                                                                        <?php } ?>
                                                                        </select>
                                                                </td>
                                                            </tr>
                                                            <?php
                                                            }
                                                            ?>
                                                            <tr>
                                                                <td bgcolor="#DCEDF7" >Órgão Contratante Responsável:</td>
                                                                <td class="inputs">
                                                                    <?php
                                                                        if($manterContratoEspecial==false && (!empty($numScc) || !empty($SCC))){ 
                                                                    ?>
                                                                            <span id="orgao" class="textonormal"><?php echo !empty($orgLicitante)?$orgLicitante:$dadosContratos->orgaocontratante;?></span>
                                                                    <?php   
                                                                        }elseif($manterContratoEspecial==true && (!empty($numScc) || !empty($SCC))){
                                                                    ?>
                                                                                <select class="selectContrato" name="orgao_licitante" <?php echo $bloqueiacampo == true ? 'disabled="disabled"':'';?> size="1" title="Orgão Licitante" style="width:625px; font-size: 10.6667px; ">	
                                                                                    
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
                                                                        }else{
                                                                    ?>
                                                                                <select class="selectContrato" name="orgao_licitante" <?php echo $bloqueiacampo?'disabled="disabled"':'';?> size="1" title="Orgão Licitante" style="width:625px; font-size: 10.6667px; ">	
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
                                                                        }
                                                                    ?>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td bgcolor="#DCEDF7" title="Símbolos e caracteres especiais como 'Ç','~'','-' e acentos devem ser removidos do texto para evitar erro.">Objeto*</td>
                                                                <td class="inputs">
                                                                <textarea class="textonormal" id="objeto" name="objeto" <?php echo $bloqueiacampo?'disabled="disabled"':'';?> cols="50" rows="4" style="font-size: 10.6667px;"><?php if(!empty($_SESSION['dadosManter']['objeto'])){echo $_SESSION['dadosManter']['objeto'];}else{echo !empty($objetoDesc)?$objetoDesc:$dadosContratos->objetivocontrato;}?></textarea>
                                                                </td>
                                                            </tr>
                                                                                                                
                                                            <tr>
                                                                <td bgcolor="#DCEDF7">Contratado:</td>
                                                                <td class="inputs">
                                                                    <table id="_gridContratadoNovo" class="textonormal">
                                                                        <tbody>
                                                                        <?php
                                                                        if(empty($SCC)){
                                                                        ?>
                                                                            <tr>
                                                                                <td class="textonormal" colspan="4">
                                                                                    <?php $CNPJ_CPF = empty($_SESSION['dadosManter']['CNPJ_CPF']) ? $CNPJ_CPF : $_SESSION['dadosManter']['CNPJ_CPF'];?>
                                                                                    <input type="radio" name="CNPJ_CPF" id="CNPJ_CPF" value="1" <?php echo $bloqueiacampo?'disabled="disabled"':'';?> <?php if( $CNPJ_CPF == 1 ){ echo "checked"; }?>>CNPJ*
                                                                                    <input type="radio" name="CNPJ_CPF" id="CNPJ_CPF" value="2" <?php echo $bloqueiacampo?'disabled="disabled"':'';?> <?php if( $CNPJ_CPF == 2 ){ echo "checked"; }?>>CPF*
                                                                                    <input class="textonormal" type="text" name="CnpjCpf" <?php echo $bloqueiacampo?'disabled="disabled"':'';?> id="CnpjCpf_forn" size="18" style="font-size: 10.6667px;" value="<?php echo !empty($CnpjCpf) ? $CnpjCpf : ''; ?>">    
                                                                                
                                                                                    <a  href=<?php echo $bloqueiacampo?'javascript:(Alteração não permitida!)':"javascript:buscaFornecedor('');";?>><img src="../midia/lupa.gif" border="0"></a>
                                                                                </td>
                                                                            </tr>
                                                                        <?php
                                                                        }
                                                                        ?>        
                                                                            <tr>
                                                                                <td class="labels">
                                                                                    <span id="_panelLblCpfCnpj">
                                                                                            <label for="" style=";" class="" id="alteracpfcnpj">
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
                                                                                        <label id="labelCPFCNPJCont">
                                                                                            <?php  
                                                                                                if(!empty($_SESSION['dadosManter']['CpfCnpContratado'])){
                                                                                                    echo $_SESSION['dadosManter']['CpfCnpContratado'];
                                                                                                }else{
                                                                                                    echo !empty($CpfCnpj)?$CpfCnpj:$ObjContrato->MascarasCPFCNPJ($cpfCNPJ);
                                                                                                }?>
                                                                                        </label>
                                                                                        <input type="hidden" name="CpfCnpContratado" value="<?php if(!empty($_SESSION['dadosManter']['CpfCnpContratado'])){echo $_SESSION['dadosManter']['CpfCnpContratado'];}else{ echo !empty($CpfCnpj)?$CpfCnpj:$ObjContrato->MascarasCPFCNPJ($cpfCNPJ);}?>">
                                                                                    </div>
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td class="labels">
                                                                                    <label for="" style=";" class="">
                                                                                        Razão Social:
                                                                                    </label>
                                                                                </td>
                                                                                <td class="inputs" colspan="3">
                                                                                    <div id="_panelGroupRazao">
                                                                                        <span id="_razaoSocialfornecedor"> 
                                                                                            <?php if(!empty($_SESSION['dadosManter']['CpfCnpContratado'])){echo $_SESSION['dadosManter']['ectrpcraza'];}else{echo !empty($razSocial)?$razSocial:$dadosContratos->razao;}?>
                                                                                        </span>
                                                                                        <input type="hidden" name="ectrpcraza" value="<?php if(!empty($_SESSION['dadosManter']['CpfCnpContratado'])){echo $_SESSION['dadosManter']['ectrpcraza'];}else{echo !empty($razSocial)?$razSocial:$dadosContratos->razao;}?>">
                                                                                    </div>
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td class="labels">
                                                                                    <label for="" style=";" class="">
                                                                                        Logradouro:
                                                                                    </label>
                                                                                </td>
                                                                                <td class="inputs" colspan="3">
                                                                                    <span id="_logradourofornecedor">
                                                                                        <?php if(!empty($_SESSION['dadosManter']['CpfCnpContratado'])){echo $_SESSION['dadosManter']['ectrpclogr'];}else{echo !empty($Rua)?$Rua:$dadosContratos->endereco;}?>
                                                                                    </span>
                                                                                    <input type="hidden" name="ectrpclogr" value="<?php if(!empty($_SESSION['dadosManter']['CpfCnpContratado'])){echo $_SESSION['dadosManter']['ectrpclogr'];}else{echo !empty($Rua)?$Rua:$dadosContratos->endereco;}?>">
                                                                                </td>
                                                                            </tr>
                                                                                <tr>
                                                                                    <td class="labels">
                                                                                        <label for="" style=";" class="">
                                                                                            Número:
                                                                                        </label>
                                                                                    </td>
                                                                                    <td class="inputs" colspan="3">
                                                                                        <span id="_numerofornecedor">
                                                                                            <?php if(!empty($_SESSION['dadosManter']['CpfCnpContratado'])){echo $_SESSION['dadosManter']['actrpcnuen'];}else{echo !empty($numEnd)?$numEnd:$dadosContratos->numerofornecedor;}?>
                                                                                        </span>
                                                                                        <input type="hidden" name="actrpcnuen" value="<?php if(!empty($_SESSION['dadosManter']['CpfCnpContratado'])){echo $_SESSION['dadosManter']['actrpcnuen'];}else{echo !empty($numEnd)?$numEnd:$dadosContratos->numerofornecedor;}?>">
                                                                                    </td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td class="labels">
                                                                                        <label for="" style=";" class="">
                                                                                            Complemento:
                                                                                        </label>
                                                                                    </td>
                                                                                    <td class="inputs">
                                                                                        <span id="_complementoLogradourofornecedor"> 
                                                                                            <?php if(!empty($_SESSION['dadosManter']['CpfCnpContratado'])){echo $_SESSION['dadosManter']['ectrpccomp'];}else{echo !empty($complEnd)?$complEnd:$dadosContratos->complementofornecedor;}?>
                                                                                        </span>
                                                                                        <input type="hidden" name="ectrpccomp" value="<?php if(!empty($_SESSION['dadosManter']['CpfCnpContratado'])){echo $_SESSION['dadosManter']['ectrpccomp'];}else{echo !empty($complEnd)?$complEnd:$dadosContratos->complementofornecedor;}?>">
                                                                                        <input type="hidden" name="cctrpcccep" value="<?php if(!empty($_SESSION['dadosManter']['CpfCnpContratado'])){echo $_SESSION['dadosManter']['cctrpcccep'];}else{echo !empty($Cep)?$Cep:$dadosContratos->cep;}?>">
                                                                                    </td>
                                                                                    <td class="labels">
                                                                                        <label for="" style=";" class="">
                                                                                            Bairro :
                                                                                        </label>
                                                                                    </td>
                                                                                    <td class="inputs">
                                                                                        <span id="_bairrofornecedor">
                                                                                            <?php if(!empty($_SESSION['dadosManter']['CpfCnpContratado'])){echo $_SESSION['dadosManter']['ectrpcbair'];}else{echo !empty($Bairro)?$Bairro: $dadosContratos->bairrofornecedor;}?>
                                                                                        </span>
                                                                                        <input type="hidden" name="ectrpcbair" value="<?php if(!empty($_SESSION['dadosManter']['CpfCnpContratado'])){echo $_SESSION['dadosManter']['ectrpcbair'];}else{echo !empty($Bairro)?$Bairro: $dadosContratos->bairrofornecedor;}?>">
                                                                                        <input type="hidden" name="ectrpctlct" value="<?php if(!empty($_SESSION['dadosManter']['CpfCnpContratado'])){echo $_SESSION['dadosManter']['ectrpctlct'];}else{echo !empty($telefone)?$telefone:"";}?>">
                                                                                    </td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td class="labels">
                                                                                        <label for="" style=";" class="">
                                                                                            Cidade:
                                                                                        </label>
                                                                                    </td>
                                                                                    <td class="inputs">
                                                                                        <span id="_cidadefornecedor">
                                                                                            <?php if(!empty($_SESSION['dadosManter']['CpfCnpContratado'])){echo $_SESSION['dadosManter']['nctrpccida'];}else{ echo !empty($Cidade)?$Cidade:$dadosContratos->cidadefornecedor;}?>
                                                                                        </span>
                                                                                        <input type="hidden" name="nctrpccida" value="<?php if(!empty($_SESSION['dadosManter']['CpfCnpContratado'])){echo $_SESSION['dadosManter']['nctrpccida'];}else{echo !empty($Cidade)?$Cidade:$dadosContratos->cidadefornecedor;}?>">
                                                                                    </td>
                                                                                    <td class="labels">
                                                                                        <label for="" style=";" class="">
                                                                                                UF:
                                                                                        </label>
                                                                                    </td>
                                                                                    <td class="inputs">
                                                                                        <span id="_estadofornecedor">
                                                                                            <?php if(!empty($_SESSION['dadosManter']['CpfCnpContratado'])){echo $_SESSION['dadosManter']['cctrpcesta'];}else{echo !empty($UF)?$UF:$dadosContratos->uffornecedor;}?>
                                                                                        </span>
                                                                                        <input type="hidden" name="cctrpcesta" value="<?php if(!empty($_SESSION['dadosManter']['CpfCnpContratado'])){echo $_SESSION['dadosManter']['cctrpcesta'];}else{echo !empty($UF)?$UF:$dadosContratos->uffornecedor;}?>">
                                                                                        <input type="hidden" name="aforcrsequCont" value="<?php if(!empty($_SESSION['dadosManter']['CpfCnpContratado'])){echo $_SESSION['dadosManter']['aforcrsequCont'];} ?>"> <!-- MADSON| Este campo deve vir apenas da busca de fornecedor contrato antigo!!! -->
                                                                                    </td>
                                                                                </tr>
                                                                        </tbody>
                                                                    </table>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td bgcolor="#DCEDF7">Consórcio / Matriz-Filial / Publicidade?*</td>
                                                                <td class="inputs">
                                                                    <table id="fieldConsorcio" class="textonormal">
                                                                        <tbody>
                                                                            <tr>
                                                                                <?php if(!empty($_SESSION['dadosManter']['fieldConsorcio'])){$dadosContratos->consocio = $_SESSION['dadosManter']['fieldConsorcio'];} ?>
                                                                                <td style="font-size:10.6667px;">
                                                                                    <input type="radio" name="fieldConsorcio" id="fieldConsorcio0" <?php echo $bloqueiacampo?'disabled="disabled"':'';?> value="SIM" <?php echo $dadosContratos->consocio == 'SIM'?'checked="checked"':'';?> title="Consórcio / Matriz-Filial / Publicidade ?">
                                                                                    <label for="fieldConsorcio0"> Sim</label>
                                                                                </td>
                                                                                <td style="font-size:10.6667px;">
                                                                                    <input type="radio" name="fieldConsorcio" id="fieldConsorcio1" <?php echo $bloqueiacampo?'disabled="disabled"':'';?> value="NAO" <?php echo $dadosContratos->consocio == 'NAO'?'checked="checked"':'';?> title="Consórcio / Matriz-Filial / Publicidade ?">
                                                                                    <label for="fieldConsorcio1"> Não</label>
                                                                                </td>
                                                                            </tr>
                                                                        </tbody>
                                                                    </table>
                                                                </td>
                                                            </tr>
                                                            <tr id="addFornecedor" <?php echo $dadosContratos->consocio == 'SIM'?'':'style="display:none;"';?> class="textonormal">
                                                                <td bgcolor="#DCEDF7">
                                                                    Fornecedores* 
                                                                </td>
                                                                <td class="inputs" colspan="2">
                                                                    <input type="button" id="addFornecedores" <?php echo $bloqueiacampo?'disabled="disabled"':'';?> class="botao" value="Adicionar Fornecedor">
                                                                    <div id="shownewfornecedores" <?php echo $bloqueiacampo?'disabled="disabled"':'';?>></div>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td bgcolor="#DCEDF7">Contínuo*</td>
                                                                <td class="inputs">
                                                                    <table id="fieldContinuo" class="textonormal">
                                                                        <tbody><tr>
                                                                            <td style="font-size:10.6667px;">
                                                                            <?php $dadosContratos->econtinuo = !empty($_SESSION['dadosManter']['fieldContinuo']) ? $_SESSION['dadosManter']['fieldContinuo']: $dadosContratos->econtinuo; ?>
                                                                            <input type="radio" name="fieldContinuo" id="fieldContinuo:0" <?php echo $bloqueiacampo?'disabled="disabled"':'';?> value="SIM" <?php echo $dadosContratos->econtinuo== 'SIM'?'checked="checked"':'';?> title="Contínuo"><label for="fieldContinuo:0"> Sim</label></td>
                                                                            <td style="font-size:10.6667px;">
                                                                            <input type="radio" name="fieldContinuo" id="fieldContinuo:1" <?php echo $bloqueiacampo?'disabled="disabled"':'';?> value="NAO" <?php echo $dadosContratos->econtinuo== 'NAO'?'checked="checked"':'';?> title="Contínuo"><label for="fieldContinuo:1"> Não</label></td>
                                                                            </tr>
                                                                        </tbody>
                                                                    </table>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td bgcolor="#DCEDF7">Obra*</td>
                                                                <td class="inputs">
                                                                    <table id="obra" class="textonormal">
                                                                        <tbody>
                                                                            <tr>
                                                                                <?php $dadosContratos->obra = !empty($_SESSION['dadosManter']['obra']) ? $_SESSION['dadosManter']['obra']: $dadosContratos->obra;?>
                                                                                <td style="font-size:10.6667px;">
                                                                                    <input type="radio" name="obra" id="obra0" <?php echo $bloqueiacampo?'disabled="disabled"':'';?> value="SIM" <?php echo $dadosContratos->obra == 'SIM'? 'checked="checked"':'';?> title="Obra">
                                                                                    <label for="obra0"> Sim</label>
                                                                                </td>
                                                                                <td style="font-size:10.6667px;">
                                                                                    <input type="radio" name="obra" id="obra1" <?php echo $bloqueiacampo?'disabled="disabled"':'';?> value="NAO" <?php echo $dadosContratos->obra == 'NAO'? 'checked="checked"':'';?> title="Obra">
                                                                                    <label for="obra1"> Não</label>
                                                                                </td>
                                                                            </tr>
                                                                        </tbody>
                                                                    </table>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td id="modoFornec" bgcolor="#DCEDF7" title="Modo de fornecimento trata de como será executado o fornecimento de objetos de compra quando atribuídos itens e lotes.">Modo de Fornecimento*</td>
                                                                <td id="regimeExec" bgcolor="#DCEDF7" style="display:none;" title="Regime de fornecimento refere-se a como as partes executarão as prestações que lhe incumbem quando tratar de obras e serviços.">Regime de Execução*</td>
                                                                <td class="inputs">
                                                                    <select class="selectContrato" id="cmb_regimeExecucaoModoFornecimento1" style="font-size: 10.6667px;" name="cmb_regimeExecucaoModoFornecimento1" size="1" <?php echo $bloqueiacampo?'disabled="disabled"':'';?>>	
                                                                        <option value ="" ></option>
                                                                    </select>
                                                                </td>
                                                                <tr>
                                                                    <td bgcolor="#DCEDF7" class="numdeparcela" width="225px">Número de Parcelas*</td>
                                                                    <td>
                                                                        <input type="number" name="NumDeParcelas" size="15" maxlength="18" value="<?php echo $dadosContratos->numerodeparcelas; ?>">
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td bgcolor="#DCEDF7" class="valordaparcela" width="225px">Valor de Parcelas*</td>
                                                                    <td> 
                                                                        <input type="text" class="dinheiro4casas" name="ValorDaParcela" size="18" maxlength="18" value="<?php echo ($dadosContratos->valordaparcela =='null')?'':number_format($dadosContratos->valordaparcela,4,',','.')?>"> 
                                                                    </td>
                                                            </tr>
                                                                <tr> 
                                                                <td bgcolor="#DCEDF7">Opção de Execução do Contrato*</td>
                                                                <td class="inputs">
                                                                    <select class="selectContrato" id="opcaoExecucaoContrato" name="opcaoExecucaoContrato" <?php echo $bloqueiacampo?'disabled="disabled"':'';?> size="1" title="Opção de Execução do Contrato ">	
                                                                        
                                                                        <?php $dadosContratos->opexeccontrato = !empty($_SESSION['dadosManter']['opcaoExecucaoContrato']) ? $_SESSION['dadosManter']['opcaoExecucaoContrato']: $dadosContratos->opexeccontrato; ?>
                                                                        <option <?php echo $dadosContratos->opexeccontrato == "D"?'selected="selected"':''; ?> style="font-size: 10.6667px;" value="D">DIAS</option>
                                                                        <option <?php echo $dadosContratos->opexeccontrato == "M"?'selected="selected"':''; ?> style="font-size: 10.6667px;" value="M">MESES</option>
                                                                    </select>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td bgcolor="#DCEDF7">Prazo de Execução do Contrato*</td>
                                                                <td class="inputs">
                                                                    <?php $dadosContratos->prazoexec = !empty($_SESSION['dadosManter']['prazo']) ? $_SESSION['dadosManter']['prazo']: $dadosContratos->prazoexec; ?>
                                                                    <input id="prazo" type="text" name="prazo" class="inteiroPositivo" maxlength="4" value="<?php echo $dadosContratos->prazoexec;?>" size="4" style="font-size: 10.6667px;" <?php echo $bloqueiacampo?'disabled="disabled"':'';?>>
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
                                                                                    <option class="textonormal" style="font-size: 10.6667px;" value="<?php echo $item->cpnccpcodi;?>" <?php echo $item->cpnccpcodi == $dadosContratos->categoriaprocesso?'selected="selected"':''; ?> ><?php echo $item->epnccpnome;?></option>
                                                                            <?php  }
                                                                                 }
                                                                            ?>
                                                                        </select>
                                                                    </td>
                                                                </tr>
                                                            <tr>
                                                                <td bgcolor="#DCEDF7">Data de Publicação no DOM:</td>
                                                                <td class="inputs">
                                                                    <input id="dataPublicacaoDom" type="text" name="dataPublicacaoDom" <?php echo $bloqueiacampo?'disabled="disabled"':'';?> class="data" maxlength="10" size="12" title="" style="font-size: 10.6667px;" value="<?php if(!empty($_SESSION['dadosManter']['dataPublicacaoDom'])){echo $_SESSION['dadosManter']['dataPublicacaoDom'];}else{echo DataBarra($dadosContratos->datapublic);}?>">
                                                                    <a href="javascript:janela('<?php echo $URLPublicaDom ?>','Calendario',220,170,1,0)"><img src="../midia/calendario.gif" border="0" alt=""></a>
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
                                                                <td bgcolor="#DCEDF7">Vigência*</td>
                                                                <td class="inputs">
                                                                    <span id="vigenciaGroup">
                                                                        <input id="vigenciaDataInicio" type="text" name="vigenciaDataInicio" value="<?php if(!empty($_SESSION['dadosManter']['vigenciaDataInicio'])){echo $_SESSION['dadosManter']['vigenciaDataInicio'];}else{echo DataBarra($dadosContratos->datainivige);}?>" <?php echo $bloqueiacampo?'disabled="disabled"':'';?> size="12" class="data" maxlength="10"  title="" style="font-size: 10.6667px;">
                                                                        <a id="calendariovdi" href="javascript:janela('<?php echo $URLvigenciaDataInicio ?>','Calendario',220,170,1,0)"><img src="../midia/calendario.gif" border="0" alt=""></a>
                                                                    </span>
                                                                </td>
                                                                <td class="inputs">
                                                                    <span id="vigenciaGroup">
                                                                        <input type="text" name="datafimvige" <?php echo $bloqueiacampo?'disabled="disabled"':'';?>  id="vigenciaDataTermino" size="12" style="font-size: 10.6667px;" value="<?php if(!empty($_SESSION['dadosManter']['datafimvige'])){echo $_SESSION['dadosManter']['datafimvige'];}else{echo DataBarra($dadosContratos->datafimvige);}?>" />
                                                                        <a id="calendariovdi" href="javascript:janela('<?php echo $URLvigenciaDataFim; ?>','Calendario',220,170,1,0)"><img src="../midia/calendario.gif" border="0" alt=""></a>
                                                                    </span>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td bgcolor="#DCEDF7">Execução*</td>
                                                                <td class="inputs">
                                                                    <span id="execucaoGroup">
                                                                        <input type="text" name="datainiexec" <?php echo $bloqueiacampo?'disabled="disabled"':'';?> size="12" id="execucaoDataInicio" style="font-size: 10.6667px;" value="<?php if(!empty($_SESSION['dadosManter']['datainiexec'])){echo $_SESSION['dadosManter']['datainiexec'];}else{echo DataBarra($dadosContratos->datainiexec);}?>" />
                                                                        <a id="calendariovdi" href="javascript:janela('<?php echo $URLExecucaoDataInicio; ?>','Calendario',220,170,1,0)"><img src="../midia/calendario.gif" border="0" alt=""></a>
                                                                    </span>
                                                                </td>   
                                                                <td class="inputs">
                                                                    <span id="execucaoGroup">
                                                                        <input type="text" name="datafimexec" <?php echo $bloqueiacampo?'disabled="disabled"':'';?> id="execucaoDataTermino" size="12" style="font-size: 10.6667px;" value="<?php if(!empty($_SESSION['dadosManter']['datafimexec'])){echo $_SESSION['dadosManter']['datafimexec'];}else{echo DataBarra($dadosContratos->datafimexec);}?>" />
                                                                        <a id="calendariovdi" href="javascript:janela('<?php echo $URLExecucaoDataFim; ?>','Calendario',220,170,1,0)"><img src="../midia/calendario.gif" border="0" alt=""></a>
                                                                    </span>
                                                                </td>   
                                                            </tr>
                                                            <tr>
                                                                <td bgcolor="#DCEDF7"> Valor Original*</td>
                                                                <?php
                                                                    if(!empty($SCC)){
                                                                ?>
                                                                        <td style="font-size: 10.6667px;"><?php echo $objContratoFuncGeral->valorOriginal($idRegistro);?> </td>
                                                                <?php
                                                                    }else{
                                                                ?>
                                                                <td>
                                                                    <input type="text" class="dinheiro4casas"   <?php echo $bloqueiacampo?'disabled="disabled"':'';?> name="valOriginal" style="font-size: 10.6667px;" value="<?php echo $objContratoFuncGeral->valorOriginal($idRegistro);?>">
                                                                </td>
                                                                <?php
                                                                    }
                                                                ?>
                                                            </tr>
                                                            <tr>
                                                                <td bgcolor="#DCEDF7">Valor Global com Aditivos/Apostilamentos*</td>   
                                                                <?php
                                                                    if(!empty($SCC)){
                                                                ?>
                                                                        <td style="font-size: 10.6667px;">
                                                                            <?php 
                                                                                 echo $objContratoFuncGeral->valorGlobal($idRegistro);
                                                                            ?>
                                                                        </td>
                                                                <?php
                                                                    }else{
                                                                ?>   
                                                                        <td>
                                                                            <input class="dinheiro4casas" type="text" name="valGlobalAdApost" <?php echo $bloqueiacampo?'disabled="disabled"':'';?> style="font-size: 10.6667px;" value="<?php  echo $objContratoFuncGeral->valorGlobal($idRegistro); ?>">
                                                                        </td>
                                                                <?php
                                                                    }
                                                                ?>
                                                            </tr>
                                                            <tr>
                                                                <td bgcolor="#DCEDF7">Valor Executado Acumulado*</td>
                                                                <?php
                                                                    if(!empty($SCC)){
                                                                ?>
                                                                        <td style="font-size: 10.6667px;">
                                                                            <?php 
                                                                                echo $objContratoFuncGeral->valorExecutado($idRegistro);
                                                                            ?>
                                                                        </td>
                                                                <?php
                                                                    }else{
                                                                ?>
                                                                        <td>
                                                                            <input type="text" class="dinheiro4casas" name="valExecutado"  <?php echo $bloqueiacampo?'disabled="disabled"':'';?> style="font-size: 10.6667px;" value="<?php 
                                                                                echo $objContratoFuncGeral->valorExecutado($idRegistro);
                                                                            ?>">
                                                                        </td>
                                                                <?php
                                                                    }
                                                                ?>
                                                            </tr>
                                                            <tr>
                                                                <td bgcolor="#DCEDF7">Saldo a Executar</td>
                                                                <td style="font-size: 10.6667px;">
                                                                <?php 
                                                                    echo $objContratoFuncGeral->saldoAExecutar($idRegistro);
                                                                ?></td>
                                                            </tr>
                                                            <tr >
                                                                <td bgcolor="#DCEDF7">Garantia</td>
                                                                <td class="inputs">

                                                                    <select class="selectContrato" id="comboGarantia" <?php echo $bloqueiacampo?'disabled="disabled"':'';?> name="comboGarantia" size="1" title="Garantia">	
                                                                        <?php foreach($dadosGarantia as $garantia){ 
                                                                            if(!empty($_SESSION['dadosManter']['comboGarantia'])){
                                                                                $selecionadoGarantia = ($garantia->codgarantia == $_SESSION['dadosManter']['comboGarantia'])?'selected="selected"':'';  
                                                                            }else{  
                                                                                $selecionadoGarantia = ($garantia->codgarantia == $dadosContratos->codisequtipogarantia)?'selected="selected"':'';    
                                                                            }
                                                                        ?>
                                                                            <option style="font-size: 10.6667px;" value="<?php echo $garantia->codgarantia;?>" <?php echo $selecionadoGarantia; ?> >
                                                                                <?php echo $garantia->descricaogarantia;?>
                                                                            </option>
                                                                        <?php } ?>
                                                                    </select>
                                                                </td>
                                                            </tr>

                                                            <?php
                                                            if(empty($numScc) && empty($SCC)){
                                                                if(!empty($_SESSION['dadosManter']['numUltAditivo'])){
                                                                    $dadosContratos->numultimoaditivocontratoantigo = $_SESSION['dadosManter']['numUltAditivo'];
                                                                }

                                                                if(!empty($_SESSION['dadosManter']['numUltApost'])){
                                                                    $dadosContratos->numultimoapostilamentocontratoantigo = $_SESSION['dadosManter']['numUltApost'];
                                                                }
                                                            ?>
                                                                <tr>
                                                                    <td bgcolor="#DCEDF7">Número do último aditivo</td>
                                                                    <td >
                                                                        <input type="text" name="numUltAditivo" maxlength="3" size="4" style="font-size: 10.6667px;" <?php echo $bloqueiacampo?'disabled="disabled"':'';?> value="<?php echo !empty($dadosContratos->numultimoaditivocontratoantigo) ? $dadosContratos->numultimoaditivocontratoantigo: ""; ?>" maxlength="20" size="10">
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td bgcolor="#DCEDF7">Número do último apostilamento</td>
                                                                    <td >
                                                                        <input  type="text" name="numUltApost" maxlength="3" size="4" style="font-size: 10.6667px;" <?php echo $bloqueiacampo?'disabled="disabled"':'';?> value="<?php echo !empty($dadosContratos->numultimoapostilamentocontratoantigo) ? $dadosContratos->numultimoapostilamentocontratoantigo: ""; ?>" maxlength="20" size="10">
                                                                    </td>
                                                                </tr>
                                                            <?php
                                                            }
                                                            ?>
                                                            <tr>
                                                                <th colspan="3" bgcolor="#bfdaf2">
                                                                    <span id="labelDataInicioOrdemServico">REPRESENTANTE LEGAL</span>
                                                                </th>
                                                            </tr>
                                                            <tr>
                                                                <td bgcolor="#DCEDF7">Nome*</td>
                                                                <td >
                                                                            <input type="text" name="nomerepresentantelegal" maxlength="120" style="width:315px; font-size: 10.6667px;" <?php echo $bloqueiacampo?'disabled="disabled"':'';?> value="<?php if(!empty($_SESSION['dadosManter']['nomerepresentantelegal'])){echo $_SESSION['dadosManter']['nomerepresentantelegal'];}else{echo $dadosContratos->nomerepresenlegal;}?>">
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td bgcolor="#DCEDF7">CPF*</td>
                                                                <td >
                                                                            <input type="text" name="cpfrepresenlegal" id="cpfrepresenlegal" size="11" <?php echo $bloqueiacampo?'disabled="disabled"':'';?> value="<?php if(!empty($_SESSION['dadosManter']['cpfrepresenlegal'])){echo $_SESSION['dadosManter']['cpfrepresenlegal'];}else{echo $dadosContratos->cpfrepresenlegal;}?>" style="font-size: 10.6667px;">
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td bgcolor="#DCEDF7">Cargo</td>
                                                                <td >
                                                                            <input type="text" name="cargorepresenlegal" maxlength="100" style="width:173px; font-size: 10.6667px;" <?php echo $bloqueiacampo?'disabled="disabled"':'';?> value="<?php if(!empty($_SESSION['dadosManter']['cargorepresenlegal'])){echo $_SESSION['dadosManter']['cargorepresenlegal'];}else{echo $dadosContratos->cargorepresenlegal;}?>">
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td bgcolor="#DCEDF7">Identidade</td>
                                                                <td >
                                                                            <input type="text" name="identidaderepreslegal" maxlength="9" size="10"  <?php echo $bloqueiacampo?'disabled="disabled"':'';?> value="<?php if(!empty($_SESSION['dadosManter']['identidaderepreslegal'])){echo $_SESSION['dadosManter']['identidaderepreslegal'];}else{echo $dadosContratos->identidaderepreslegal;}?>" style="font-size: 10.6667px;">
                                                            </td>
                                                            </tr>
                                                            <tr>
                                                                <td bgcolor="#DCEDF7">Órgão Emissor</td>
                                                                <td >
                                                                            <input type="text" name="orgaoexpedrepreselegal" maxlength="3" size="1" <?php echo $bloqueiacampo?'disabled="disabled"':'';?> value="<?php if(!empty($_SESSION['dadosManter']['orgaoexpedrepreselegal'])){echo $_SESSION['dadosManter']['orgaoexpedrepreselegal'];}else{echo $dadosContratos->orgaoexpedrepreselegal;}?>" style="font-size: 10.6667px;">
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td bgcolor="#DCEDF7">UF da Identidade</td>
                                                                <td >
                                                                            <input type="text" name="ufrgrepresenlegal" maxlength="2" size="1" style="text-transform: uppercase; font-size: 10.6667px;" <?php echo $bloqueiacampo?'disabled="disabled"':'';?> value="<?php if(!empty($_SESSION['dadosManter']['ufrgrepresenlegal'])){echo $_SESSION['dadosManter']['ufrgrepresenlegal'];}else{echo $dadosContratos->ufrgrepresenlegal;}?>">
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td bgcolor="#DCEDF7">Cidade de Domicílio</td>
                                                                <td >
                                                                            <input type="text" name="cidadedomrepresenlegal" maxlength="30" style="width:173px; font-size: 10.6667px;"  <?php echo $bloqueiacampo?'disabled="disabled"':'';?> value="<?php if(!empty($_SESSION['dadosManter']['cidadedomrepresenlegal'])){echo $_SESSION['dadosManter']['cidadedomrepresenlegal'];}else{echo $dadosContratos->cidadedomrepresenlegal;}?>">
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td bgcolor="#DCEDF7">Estado de Domicílio</td>
                                                                <td >
                                                                            <input type="text" name="estdomicrepresenlegal"  maxlength="2" size="1" style="text-transform: uppercase; font-size: 10.6667px;" <?php echo $bloqueiacampo?'disabled="disabled"':'';?> value="<?php if(!empty($_SESSION['dadosManter']['estdomicrepresenlegal'])){echo $_SESSION['dadosManter']['estdomicrepresenlegal'];}else{echo $dadosContratos->estdomicrepresenlegal;}?>">
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td bgcolor="#DCEDF7">Nacionalidade</td>
                                                                <td >
                                                                            <input type="text" name="naciorepresenlegal" maxlength="50" style="width:173px; font-size: 10.6667px;" <?php echo $bloqueiacampo?'disabled="disabled"':'';?> value="<?php if(!empty($_SESSION['dadosManter']['naciorepresenlegal'])){echo $_SESSION['dadosManter']['naciorepresenlegal'];}else{echo $dadosContratos->naciorepresenlegal;}?>">
                                                            </td>
                                                            </tr>
                                                            <tr>
                                                                <td bgcolor="#DCEDF7">Estado Civil</td>
                                                                    <td >
                                                                            <select class="selectContrato" name="estacivilrepresenlegal" size="1" style="width:173px; font-size: 10.6667px;" title="Estado Civil" <?php echo $bloqueiacampo?'disabled="disabled"':'';?> id="estacivilrepresenlegal" >	
                                                                                <option value="Z">Selecione o estado civil...</option>
                                                                                <?php 
                                                                                    if(!empty($_SESSION['dadosManter']['estacivilrepresenlegal'])){
                                                                                        $dadosContratos->estacivilrepresenlegal = $_SESSION['dadosManter']['estacivilrepresenlegal'];
                                                                                    }
                                                                                ?>
                                                                                <option <?php echo $dadosContratos->estacivilrepresenlegal == 'S'?'selected="selected"':'';?> value="S">SOLTEIRO</option>
                                                                                <option <?php echo $dadosContratos->estacivilrepresenlegal == 'C'?'selected="selected"':'';?> value="C">CASADO</option>
                                                                                <option <?php echo $dadosContratos->estacivilrepresenlegal == 'D'?'selected="selected"':'';?> value="D">DIVORCIADO</option>
                                                                                <option <?php echo $dadosContratos->estacivilrepresenlegal == 'V'?'selected="selected"':'';?>  value="V">VIÚVO</option>
                                                                                <option <?php echo $dadosContratos->estacivilrepresenlegal == 'O'?'selected="selected"':'';?> value="O">OUTROS</option>
                                                                            </select>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td bgcolor="#DCEDF7">Profissão</td> 
                                                                <td >
                                                                            <input type="text" name="profirepresenlegal" maxlength="50" style="width:173px; text-transform: uppercase; font-size: 10.6667px;" <?php echo $bloqueiacampo?'disabled="disabled"':'';?> value="<?php if(!empty($_SESSION['dadosManter']['profirepresenlegal'])){echo $_SESSION['dadosManter']['profirepresenlegal'];}else{echo $dadosContratos->profirepresenlegal;}?>">
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td bgcolor="#DCEDF7">E-mail</td>
                                                                <td >
                                                                            <!-- Foi retirado o toUpperCase -->
                                                                            <input type="text" name="emailrepresenlegal" maxlength="60" style="width:173px; font-size: 10.6667px; text-transform:none;" <?php echo $bloqueiacampo?'disabled="disabled"':'';?>  value="<?php if(!empty($_SESSION['dadosManter']['emailrepresenlegal'])){echo $_SESSION['dadosManter']['emailrepresenlegal'];}else{echo $dadosContratos->emailrepresenlegal;}?>">
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td bgcolor="#DCEDF7">Telefone(s)</td>
                                                                <td >
                                                                            <input type="text" class="telefone" name="telrepresenlegal" size="13"  <?php echo $bloqueiacampo?'disabled="disabled"':'';?> value="<?php if(!empty($_SESSION['dadosManter']['telrepresenlegal'])){echo $_SESSION['dadosManter']['telrepresenlegal'];}else{echo $dadosContratos->telrepresenlegal;}?>" style="font-size: 10.6667px;">
                                                                </td>
                                                            </tr>
                                                            <tr  bgcolor="#bfdaf2">
                                                                <th colspan="3" scope="colgroup">GESTOR</th>
                                                            </tr>
                                                            <tr>
                                                                    <td bgcolor="#DCEDF7">Nome*</td>
                                                                    <td >
                                                                                <input type="text" maxlength="120" style="width:315px; font-size: 10.6667px;" <?php echo $bloqueiacampo?'disabled="disabled"':'';?> name="nomegestor" value="<?php if(!empty($_SESSION['dadosManter']['nomegestor'])){echo $_SESSION['dadosManter']['nomegestor'];}else{echo $dadosContratos->nomegestor;}?>">
                                                                    </td>
                                                            </tr>
                                                            <tr>
                                                                    <td bgcolor="#DCEDF7">Matrícula*</td>
                                                                    <td >
                                                                                <input type="text" maxlength="20" size="10" <?php echo $bloqueiacampo?'disabled="disabled"':'';?> name="matgestor" value="<?php if(!empty($_SESSION['dadosManter']['matgestor'])){echo $_SESSION['dadosManter']['matgestor'];}else{echo $dadosContratos->matgestor;}?>" style="font-size: 10.6667px;">
                                                                    </td>
                                                            </tr>
                                                            
                                                            <tr>
                                                                    <td bgcolor="#DCEDF7">CPF*</td>
                                                                    <td >
                                                                                <input type="text" size="11" <?php echo $bloqueiacampo?'disabled="disabled"':'';?> name="cpfgestor" id="cpfgestor" value="<?php if(!empty($_SESSION['dadosManter']['cpfgestor'])){echo $_SESSION['dadosManter']['cpfgestor'];}else{echo $dadosContratos->cpfgestor;}?>" style="font-size: 10.6667px;">
                                                                    </td>
                                                            </tr>
                                                            
                                                            <tr>
                                                                    <td bgcolor="#DCEDF7">E-mail*</td>
                                                                    <td >
                                                                                <input type="text" style="width:173px; font-size: 10.6667px; text-transform:none;" <?php echo $bloqueiacampo?'disabled="disabled"':'';?> name="emailgestor" value="<?php if(!empty($_SESSION['dadosManter']['emailgestor'])){echo $_SESSION['dadosManter']['emailgestor'];}else{echo $dadosContratos->emailgestor; }?>">
                                                                    </td>
                                                            </tr>
                                                            
                                                            <tr>
                                                                <td bgcolor="#DCEDF7">Telefone(s)*</td>
                                                                <td>
                                                                    <input type="text" class="telefone" size="13" <?php echo $bloqueiacampo?'disabled="disabled"':'';?> name="fonegestor" value="<?php if(!empty($_SESSION['dadosManter']['fonegestor'])){echo $_SESSION['dadosManter']['fonegestor'];}else{echo $dadosContratos->fonegestor;}?>" style="font-size: 10.6667px;">
                                                                </td>
                                                            </tr>
                                                            <tr  bgcolor="#bfdaf2">
                                                                <th colspan="3" <?php echo $bloqueiacampo?'disabled="disabled"':'';?> scope="colgroup">FISCAL(IS)*</th>
                                                            </tr>
                                                            <tr>
                                                            <!-- Eliakim Ramos 05032019 -->
                                                                <td colspan="3" >
                                                                            <table style="width:100%; border:1px solid #bfdaf2;"  id="tabelaficais" class="textonormal">
                                                                                    <tr bgcolor="#DCEDF7" style="font-weight: bold;">
                                                                                        <td class="titulo3" colspan="1"></td>
                                                                                        <td class="titulo3" colspan="1">TIPO FISCAL</td>
                                                                                        <td class="titulo3" colspan="1">NOME</td>
                                                                                        <td class="titulo3" colspan="1">CPF</td>
                                                                                        <td class="titulo3" colspan="1">ENT. COMPET.</td>
                                                                                        <td class="titulo3" colspan="1"> REGISTRO OU INSC.</td>
                                                                                        <td class="titulo3" colspan="1">E-MAIL</td>
                                                                                        <td class="titulo3" colspan="1">TELEFONE</td>
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
                                                                                                <td > 
                                                                                                    <!-- <input type="radio" name="fiscais" <?php //echo $bloqueiacampo?'disabled="disabled"':'';?> value="<?php //echo $fiscal->fiscalcpf.'-'.$fiscal->docsequ;?>"> -->
                                                                                                    <input type="radio" name="fiscais" value="<?php echo $fiscal->fiscalcpf.'-'.$fiscal->docsequ;?>">
                                                                                                </td>
                                                                                                <td><?php echo $fiscal->tipofiscal;?></td>
                                                                                                <td><?php echo $fiscal->fiscalnome;?></td>
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
                                                                                        <td  colspan="9" style="itens-align:right;">
                                                                                        <?php
                                                                                        $validaBotao = True;
                                                                                         if( $validaBotao == True){ ?>
                                                                                                <button class="botao" type="button" id="removefiscal" style="float: right;">Remover Fiscal</button>
                                                                                                <button class="botao" type="button" id="manterfiscal" style="float: right;">Manter Fiscal</button>                                                                                            
                                                                                        <?php } ?>
                                                                                        </td>
                                                                                    </tr>
                                                                                    
                                                                                    
                                                                                </tfoot>
                                                                            </table>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                            <!-- Eliakim Ramos 05032019 -->
                                                            <tr  bgcolor="#bfdaf2">
                                                                <th colspan="3" <?php echo $bloqueiacampo?'disabled="disabled"':'';?> scope="colgroup">HISTÓRICO DE FISCAIS</th>
                                                            </tr>
                                                                <td colspan="3" >
                                                                            <table style="width:100%; border:1px solid #bfdaf2;"  id="tabelaficais" class="textonormal">
                                                                                    <tr bgcolor="#DCEDF7" style="font-weight: bold;">
                                                                                        
                                                                                        <td class="titulo3" colspan="1">NOME</td>
                                                                                        <td class="titulo3" colspan="1">CPF</td>
                                                                                        <td class="titulo3" colspan="1">DATA DE INÍCIO</td>
                                                                                        <td class="titulo3" colspan="1">DATA DE TÉRMINO</td>
                                                                                        
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
                                                                                                
                                                                                                
                                                                                                <td><?php echo $fiscal->fiscalnome;?></td>
                                                                                                <td><?php echo $ObjContrato->MascarasCPFCNPJ($fiscal->fiscalcpf);?></td>
                                                                                                <td>
                                                                                                    <input id="fiscalDataInicio" type="text" name="fiscalDataInicio" value="<?php echo !empty($_POST['fiscalDataInicio'])?$_POST['fiscalDataInicio']:'';?>" class="data" maxlength="10" size="12" title="" style="font-size: 10.6667px;">
                                                                                                    
                                                                                                </td>
                                                                                                <td>
                                                                                                    <input id="fiscalDataTermino" type="text" name="<?php echo 'fiscalDataTermino'.$i ?>" value="<?php echo !empty($_POST['fiscalDataTermino'.$i])?$_POST['fiscalDataTermino'.$i]:'';?>" class="data"  maxlength="10" size="12"style="font-size: 10.6667px;">
                                                                                                    
                                                                                                </td>
                                                                                            </tr>
                                                                                <?php          
                                                                                                    } 
                                                                                                }
                                                                                                $auxAnt[] = $fiscal->fiscalcpf;
                                                                                            
                                                                                            }
                                                                                            
                                                                                        }
                                                                                       
                                                                                ?>
                                                                                
                                                                                <tr>
                                                                                        <td bgcolor="#bfdaf2" colspan="1" width="35%">Situação </td>
                                                                                        <td bgcolor="#FFFFFF" width="10%"><?php echo $situacaoContrato->esitdcdesc;?></td>
                                                                                    </tr>                                                                            
                                                                                </tbody>
                                                                                
                                                                                
                                                                            </table>
                                                                            
                                                                            
                                                                </td>
                                                            </tr>
                                                            
                                                                
                                                            <tr bgcolor="#bfdaf2">
                                                                <th colspan="3" scope="colgroup">ANEXAÇÃO DE DOCUMENTO(S)*</th>
                                                            </tr>
                                                            <tr>
                                                                <td colspan="3">
                                                                            <table id="tabelaficais" bgcolor="#bfdaf2" class="textonormal" width="100%">
                                                                                <tbody >
                                                                                    <tr >
                                                                                        <td bgcolor="#DCEDF7">Anexação de Documentos:</td>
                                                                                        <td colspan="1" style="border:none"> 
                                                                                            <iframe src="formuploadManter.php?idRegistro=<?php echo $idRegistro;?>" id="frameArquivo" height="39" width="520"  name="frameArquivo" frameborder="0"></iframe>
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
                                                                                        <td><input type="hidden" id="anexoInseridoOuRetirado"  name="anexoInseridoOuRetirado" value="<?php echo $_SESSION['anexoInseridoOuRetirado']; ?>"></td>
                                                                                    </tr>
                                                             
                                                                                    <?php 
                                                                                    if(!empty($DadosDocAnexo)){
                                                                                        $k=0;
                                                                                        foreach($DadosDocAnexo as $key => $anexo){ 
                                                                                            $_SESSION['arquivo_download'][$k] = $anexo->arquivo; 
                                                                                    ?>
                                                                                    <tr bgcolor="#ffffff">
                                                                                        <td>
                                                                                            <input type="radio" name="docanex" value="<?php echo $key; ?>">
                                                                                            <input type="hidden" name="nomedoc" value="<?php echo $anexo->sequdocumento . '*' . $anexo->nomearquivo; ?>">
                                                                                        </td>
                                                                                        <!-- <td><input type="radio" name="docanex" value="<?php echo $anexo->sequdocumento.'*'.$anexo->nomearquivo;?>"></td> -->
                                                                                        <!-- <td><input type="radio" name="docanex" value="<?php echo $key;?>"></td>
                                                                                        <td colspan="4"> <?php //echo $anexo->nomearquivo;?></td>
                                                                                        <td colspan="4"> <?php //echo $anexo->datacadasarquivo;?></td>  -->
                                                                                        <td colspan="4"> 
                                                                                            <a class="" href="downloadDocContratoConsolidado.php?arquivo=<?php echo $k;?>&nome=<?php echo $anexo->nomearquivo;?>" id="documento<?php echo $k;?>" rel="<?php echo $anexo->nomearquivo;?>"><?php echo $anexo->nomearquivo; ?></a>
                                                                                        </td>
                                                                                        <td colspan="4"> <?php echo $anexo->datacadasarquivo; ?></td>
                                                                                    </tr>
                                                                                    <?php $k++;
                                                                                    }
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
                                                                                    </tr>
                                                                                </tfoot>
                                                                            </table>
                                                                </td>
                                                            </tr>
                                                            
                                                                <tr bgcolor="#bfdaf2" id="addObservacao" style=<?php echo ($situacaoContrato->esitdcdesc == "CANCELADO")||($situacaoContrato->esitdcdesc == "ENCERRADO")||(!empty($dadosContratos->obsenceramento))?"display:block":"display:none";?>>
                                                                    <!-- <th colspan="3" id="obsTH" scope="colgroup">OBSERVAÇÕES</th> -->

                                                                    <tr style=<?php echo ($situacaoContrato->esitdcdesc == "CANCELADO")||($situacaoContrato->esitdcdesc == "ENCERRADO")||(!empty($dadosContratos->obsenceramento))?"display:block":"display:none";?> id="JustTR">
                                                                        <td bgcolor="#DCEDF7">Justificatica do Cancelamento/Encerramento</td>
                                                                        <td class="inputs">
                                                                        <textarea type="text" class="textonormal" style="font-size: 10.6667px; text-transform: uppercase;" id="observacaoJust" name="observDesc" cols="50" rows="4" maxlength="500" rows="8" ><?php if(!empty($_SESSION['dadosObservacao'])){echo $_SESSION['dadosObservacao'];}else{echo !empty($observDesc)?$observDesc:$dadosContratos->obsenceramento;}?></textarea>

                                                                        </td>
                                                                    </tr>
                                                                        
                                                                </tr>
                                                            
                                                                                                   
                                                            <tr style="display:none">
                                                                <td>
                                                                    <input id="removerDocumentoFiscalBotaoContrato" type="submit" name="removerDocumentoFiscalBotaoContrato" value="Remover Fiscal" style="float:right" title="Remover Fiscal" class="botao_final">
                                                                    <span id="espacoEmBrancoManterFiscalDocumento"> </span>
                                                                    <input id="manterFiscalBotaoContrato" type="submit" name="manterFiscalBotaoContrato" value="Manter Fiscal" style="float:right" title="Manter Fiscal" class="botao_final">
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td colspan="4" align="right">
                                                                    <input type="button" value="<?php echo  $nomeBotao;?>"  <?php 
                                                                    if($_SESSION['_fperficorp_'] != 'S' && ($situacaoContrato->esitdcdesc == "CANCELADO" || $situacaoContrato->esitdcdesc == "ENCERRADO")){
                                                                        $exibeBotaoEncerramento = 'style="display:none"';
                                                                        echo $exibeBotaoEncerramento;
                                                                    }else{
                                                                        $exibeBotaoEncerramento = '';
                                                                        echo $exibeBotaoEncerramento;
                                                                    }
                                                                    ?> class="botao" id="btnSalvarContrato"onclick="javascript:enviar('A');">
                                                                    <input type="hidden" value="<?php echo $botaoNomeSalvarAnexo;?>" id="botaoNomeSalvarAnexo">
                                                                    <input type="button" value="Excluir" <?php echo $exibeBotao;?>  onclick="<?php echo  $funcaoExcluir;?>" class="botao" id="btnexcluir">
                                                                    <input type="button" value="Cancelar" <?php echo $exibeBotao;?>  onclick="<?php echo  $funcaoCancelar; ?>" class="botao" id="btncancelar">
                                                                    <input type="button" value="<?php echo $nomeBotaoEncerrar;?> " 
                                                                    <?php 
                                                                    
                                                                    if($situacaoContrato->esitdcdesc == "CANCELADO"){
                                                                        $exibeBotaoEncerramento = 'style="display:none"';
                                                                        echo $exibeBotaoEncerramento;
                                                                    }else{
                                                                        $exibeBotaoEncerramento = '';
                                                                        echo $exibeBotaoEncerramento;
                                                                    }
                                                                    
                                                                    ?>  onclick="<?php echo  $funcaoEncerrar;?>" class="botao" id="btnencerrar">
                                                                    <input type="button" value="Desfazer Cancelamento" 
                                                                    <?php 
                                                                    if($situacaoContrato->esitdcdesc == "CADASTRADO"){
                                                                        $exibeBotaoEncerramento = 'style="display:none"';
                                                                        echo $exibeBotaoEncerramento;
                                                                    }else{
                                                                        $exibeBotaoEncerramento = '';
                                                                        echo $exibeBotaoEncerramento;
                                                                    }
                                                                    if($situacaoContrato->esitdcdesc == "ENCERRADO"){
                                                                        $exibeBotaoEncerramento = 'style="display:none"';
                                                                        echo $exibeBotaoEncerramento;
                                                                    }else{
                                                                        $exibeBotaoEncerramento = '';
                                                                        echo $exibeBotaoEncerramento;
                                                                    }
                                                                    ?>
                                                                    <?php echo $exibeBotaoEncerramento; ?> onclick="<?php echo $funcaoDesfazerCancelamento;?>" class="botao" id="btncancelar">
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

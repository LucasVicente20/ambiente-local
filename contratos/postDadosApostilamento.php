<?php
# -------------------------------------------------------------------------
# Portal da Compras
# Programa: postDadosApostilamento.php
# Autor:    Edson Dionisio
# Data:     28/04/2020
# -------------------------------------------------------------------------
# Autor:    Marcello Albuquerque
# Data:     09/11/2021
# CR #251686 
# -------------------------------------------------------------------------
session_start();
require_once dirname(__FILE__) . '/../funcoes.php';
require_once "ClassContratos.php";
require_once "ClassApostilamento.php";

$ObjContrato = new Contrato();
$ObjApostilamento = new ClassApostilamento();
$arrayTirar  = array('.',',','-','/');

function floatvalue($val){
    $val = str_replace(",",".",$val);
    $val = preg_replace('/\.(?=.*\.)/', '', $val);
    
    return floatval($val);
}

switch($_POST['op']){
    case "validarSeNumeroMedicaoExiste":
           $contrato = $_POST['contrato'];
           $amedconume = $_POST['amedconume'];           

            $dadosMedicao = array(
                'cdocpcsequ'=> $ObjApostilamento->anti_injection($contrato),
                'amedconume'=> $ObjApostilamento->anti_injection($amedconume)
            ); 
            $retorno = $ObjApostilamento->ValidarSeNumeroMedicaoExiste($dadosMedicao);
            
            if($retorno[0] == "1"){
                $response = array("status"=>true,"msm"=>"Esse número de medição já está atribuído a outra medição. Favor, informe outro número.");
                print(json_encode($response));
            }
            else{
                $response = array("status"=>false);
                print(json_encode($response));
            }

    break;
    case "RemoveFiscal":

        $MarcadorSeparado = explode('-',$_POST['marcador']);
        $sessaoFiscal = $_SESSION['fiscal_selecionado_incluir'];
 
        unset($_SESSION['fiscal_selecionado_incluir']);
        $i = 0;
        if(!empty($sessaoFiscal)){
            
            foreach($sessaoFiscal as $f){
                if($MarcadorSeparado[0] == $f->fiscalcpf){
                    $sessaoFiscal[$i]->remover = 'S';
                }
                $i++;
            }
        }else{
            echo "Error ao remover o fiscal";
        }
        $_SESSION['fiscal_selecionado_incluir'] = $sessaoFiscal;
        $ObjJS = json_encode($_SESSION['fiscal_selecionado_incluir']);
        print_r($ObjJS);
    break;

    case "alterarApostilamento":
       // var_dump($_SESSION);die;
        $documento = $_POST['idregistro'];
        $contrato = $_POST['idcontrato'];

        $fiscal = $_SESSION['fiscal_selecionado_incluir'];
        
        $tipoApostilamento = $_POST['tipo_apostilamento'];
        $situacao_apostilamento = $_POST['situacao_apostilamento'];

        $num_apostilamento = $_POST['num_apostilamento'];
        $dataApostilamento = $_POST['dataPublicacaoDom'];
        $gestorNome = $_POST['gestorNome'];
        $gestorMatricula = $_POST['gestorMatricula'];
        $gestorCPF = $_POST['gestorCPF'];
        $gestorEmail = $_POST['gestorEmail'];
        $gestorTelefone = $_POST['gestorTelefone'];
        $valor_retroativo = $ObjApostilamento->floatvalue($_POST['valor_retroativo_apostilamento']);
        $valor_apostilamento = $ObjApostilamento->floatvalue($_POST['valor_apostilamento']);
        
        $nome_anexo = $_SESSION['documento_anexo'][0]['nome_arquivo'];
        $bin_anexo = $_SESSION['documento_anexo'][0]['arquivo'];

        $dadosGestor = array(
            'nctrpcnmgt' => $ObjApostilamento->anti_injection($gestorNome),
            'nctrpcmtgt' => $ObjApostilamento->anti_injection($gestorMatricula),
            'nctrpccpfg' => $ObjApostilamento->anti_injection($ObjApostilamento->limpaCPF_CNPJ($gestorCPF)),
            'nctrpcmlgt' => $ObjApostilamento->anti_injection($gestorEmail),
            'ectrpctlgt' => $ObjApostilamento->anti_injection($gestorTelefone),
            'cdocpcsequ' => $ObjApostilamento->anti_injection($contrato)
        );

        $idtipoApostilamento = $ObjApostilamento->codigoTipoApostilamento($tipoApostilamento);

        $dados_validar = array(
            'tipo_apostilamento' => $idtipoApostilamento[0]->ctpaposequ,
            'data' => $dataApostilamento,
            'gestorNome' => $_POST['gestorNome'],
            'gestorMatricula' => $_POST['gestorMatricula'],
            'gestorCPF' => $_POST['gestorCPF'],
            'gestorEmail' => $_POST['gestorEmail'],
            'gestorTelefone' => $_POST['gestorTelefone'],
            'valor_apostilamento' => $_POST['valor_apostilamento'],
            'nome_arquivo' => $_SESSION['documento_anexo_incluir'][0]['nome_arquivo'],
            'arquivo' => $_SESSION['documento_anexo_incluir'][0]['arquivo']

        );
        $seExisteCodApostilamentoContrato = $ObjApostilamento->verificarSeExisteNumeroApostilamentoContrato($contrato, $num_apostilamento);
       
        $retorno_cod = 0; // Se não tiver registro na base
        
        if($seExisteCodApostilamentoContrato[0]->cdocpcsequ != $documento && ($seExisteCodApostilamentoContrato[0]->aapostnuap == $num_apostilamento)){
            $retorno_cod = 1;
            $response = array("status"=>false,"msm"=>"Esse número de apostilamento já está atribuído a outro registro. Favor, informe outro número.");
            print(json_encode($response));
            return false;
        }

        $validando = $ObjApostilamento->validarDados($dados_validar);

        if(empty($num_apostilamento) || $num_apostilamento == NULL){
            $resp = array("status" => false,"msm" => " Informe: Número do apostilamento.");
            print(json_encode($resp));
            return false;
        }

        if($validando != false){
            $id_tipo_apostilamento = intval($idtipoApostilamento[0]->ctpaposequ);

            $dadosMedicao = array(
                'CDOCPCSEQU'=> $ObjApostilamento->anti_injection($documento), // vem do documento
                'CDOCPCSEQ2'=> $ObjApostilamento->anti_injection($contrato), // vem do contrato
                'CTPAPOSEQU'=> $ObjApostilamento->anti_injection($id_tipo_apostilamento),
                'AAPOSTNUAP' => $ObjApostilamento->anti_injection($num_apostilamento),
                'DAPOSTCADA'=> $ObjApostilamento->date_transform($ObjApostilamento->anti_injection($dataApostilamento)),
                'NAPOSTNMGT'=> $ObjApostilamento->anti_injection($gestorNome),
                'NAPOSTCPFG'=> $ObjApostilamento->anti_injection($ObjApostilamento->limpaCPF_CNPJ($gestorCPF)),
                'NAPOSTMTGT'=> $ObjApostilamento->anti_injection($gestorMatricula),
                'NAPOSTMLGT'=> $ObjApostilamento->anti_injection($gestorEmail),
                'EAPOSTTLGT'=> $ObjApostilamento->anti_injection($gestorTelefone),
                'VAPOSTVTAP'=> $ObjApostilamento->anti_injection($valor_apostilamento),
                'VAPOSTRETR'=> $ObjApostilamento->anti_injection($valor_retroativo),
                'situacao_apost' => $situacao_apostilamento
            );
            
            
           if(intval($idtipoApostilamento[0]->ctpaposequ) == 2 || intval($idtipoApostilamento[0]->ctpaposequ) == 3){
          
                // inserir fiscal-----------------------------------------------------------------------
                if(!empty($_SESSION['fiscal_selecionado_incluir'])){
                    
                    $okFiscal =array();
                    foreach($_SESSION['fiscal_selecionado_incluir'] as $fiscal){
                        $existe  = $ObjApostilamento->VerificaSeExisteDocumentoFiscal($documento, $ObjApostilamento->limpaCPF_CNPJ($fiscal->fiscalcpf));
                        // kim começar daqui
                        if($fiscal->remover == "S"){
                            $retornoDelete = $ObjApostilamento->RemoveFiscaldoContrato($ObjApostilamento->limpaCPF_CNPJ($fiscal->fiscalcpf), $documento);
                            $okFiscal[] = false;
                        }else{
                            $okFiscal[] = true;
                        }
                        if(empty($existe) && $fiscal->remover != "S"){
                            $dadosFiscal = array( 
                            'cfiscdcpff' => $ObjApostilamento->limpaCPF_CNPJ($fiscal->fiscalcpf),
                            'cdocpcsequ' => $documento,
                            'cusupocodi' => $ObjContrato->anti_injection($_SESSION['_cusupocodi_']),
                            'tdocfiulat' => date('Y-m-d H:i:s.u')
                            );
                            
                            $retornoFS = $ObjApostilamento->InsertDocumentoFiscal($dadosFiscal);
                            if(empty($retornoFS)){
                                $response = array("status" => false, "msm" => "Error não foi possivel editar o contrato, tente novamente ");
                                print(json_encode($response));
                                exit;
                            }
                        }
                    }
                    
                    if(!in_array(true, $okFiscal) ){
                        $response = array("status"=>false,"msm"=>"Informe: Fiscal do contrato");
                                print(json_encode($response));
                                exit;
                            break;
                    }
                    unset($_SESSION['fiscal_selecionado_incluir']);
                }
                //-------------------------------------------------------------------------------------
            }


            // inserir anexo ----------------------------------------------------------------------
            if(!empty($_SESSION['documento_anexo_incluir'])){
                $i=0;
                $ok=array();
                foreach($_SESSION['documento_anexo_incluir'] as $docAnex){

                        $seExisteDocumentoAnexo = $ObjApostilamento->VerificaSeJaExisteDocumentoAnexo($documento, $docAnex['sequarquivo'], $docAnex['nome_arquivo']);
                        if($docAnex['remover'] == "S" && !empty($seExisteDocumentoAnexo)){
                            $ObjApostilamento->DeletaDocumentoAnexo($docAnex['sequdoc'],$docAnex['sequarquivo']);
                            $ok[]=false;
                        }else{
                            $ok []=true;
                                if(empty($seExisteDocumentoAnexo) && $docAnex['remover'] != "S"){
                                    $dadosDocAnex = array(
                                                        'cdocpcsequ'     => $documento,
                                                        'edcanxnome'     => $docAnex['nome_arquivo'],
                                                        'idcanxarqu'     => $docAnex['arquivo'],
                                                        'tdcanxcada'     => $docAnex['data_inclusao'],
                                                        'cusupocodi'     => $docAnex['usermod'],
                                                        'ativo'             => 'S'
                                                        );
                                    $retorno = $ObjApostilamento->InsereDocumentosAnexos($dadosDocAnex);
                                    if(empty($retorno)){
                                        $response = array("status"=>false,"msm"=>"Error não foi possivel editar o contrato, tente novamente");
                                        print(json_encode($response));
                                        exit;
                                    }
                                }
                            }
                        }

                unset($_SESSION['documento_anexo_incluir']);
                if(!in_array(true,$ok)){
                    $response = array("status"=>false,"msm"=>"Informe: Documento anexo ");
                    print(json_encode($response));
                    exit;
                    break;
                }
            }
            
            $retorno = $ObjApostilamento->alteraDadosApostilamento($dadosMedicao);
            
            unset($_SESSION['fiscal_selecionado_incluir']);
            unset($_SESSION['documento_anexo_incluir']);

            $response = array("status" => true,"msm" => "Apostilamento salvo com sucesso.");
            print(json_encode($response));
        }
        
    break;

    case "IncluirAditivo":
        $contrato = $_POST['idregistro'];

        if(empty($_POST['prazo'])){
            $prazo = 0;
        }

        $tipoAditivo = $_POST['tipo_aditivo'];
        $num_aditivo = $_POST['numero_registro'];

        $objeto = $_POST['objeto'];
        $alteracao_prazo = $_POST['alteracao_prazo'];
        $alteracao_valor = $_POST['alteracao_valor'];
        $tipoAlteracaoValor = $_POST['tipoAlteracaoValor'];
        $valor_retroativo = $ObjApostilamento->floatvalue($_POST['valor_retroativo']);
        $valor_total = $ObjApostilamento->floatvalue($_POST['valor_total']);
        $prazo = $_POST['prazo']  ;
        $vigenciaDataInicio = $ObjContrato->date_transform($_POST['vigenciaDataInicio']);
        $vigenciaDataTermino = $ObjContrato->date_transform($_POST['vigenciaDataTermino']);
        $execucaoDataInicio = $ObjContrato->date_transform($_POST['execucaoDataInicio']);
        $execucaoDataTermino = $ObjContrato->date_transform($_POST['execucaoDataFinal']);

        $observacao = $_POST['observacao'];
        
        $repNome = $_POST['repNome'];
        $repCPF = $ObjApostilamento->limpaCPF_CNPJ($_POST['repCPF']);
        $repCargo = $_POST['repCargo'];
        $repRG = $_POST['repRG'];
        $repRgOrgao = $_POST['repRgOrgao'];
        $repRgUF = $_POST['repRgUF'];
        $repCidade = $_POST['repCidade'];
        $repEstado = $_POST['repEstado'];
        $repNacionalidade = $_POST['repNacionalidade'];
        $repEstCiv = $_POST['repEstCiv'];
        $repProfissao = $_POST['repProfissao'];
        $repEmail = $_POST['repEmail'];
        $repTelefone = $_POST['repTelefone'];

        $tipo_forn = $_POST['CNPJ_CPF'];
        $CnpjCpf_forn = $ObjApostilamento->limpaCPF_CNPJ($_POST['CnpjCpf']);

        $razao_social_forn = $_POST['razaosocial'];
        $logradouro_forn = $_POST['logradouro'];
        $compl_forn = $_POST['compl'];
        $cidade_forn = $_POST['cidade'];
        $bairro_forn = $_POST['bairro'];
        $estado_forn = $_POST['estado'];
        

        if($tipo_forn == 1){
            $cnpj_forn = $ObjApostilamento->anti_injection($CnpjCpf_forn);
        }else{
            $cpf_forn = $ObjApostilamento->anti_injection($CnpjCpf_forn);
        }

        $nome_anexo = $_SESSION['documento_anexo_incluir'][0]['nome_arquivo'];
        $bin_anexo = $_SESSION['documento_anexo_incluir'][0]['arquivo'];

        $dadosAditivo = array(
            'CDOCPCSEQ1' => $ObjApostilamento->anti_injection($contrato),
            'CTPADISEQU' => $ObjApostilamento->date_transform($ObjApostilamento->anti_injection($tipoAditivo)),
            'AADITINUAD' => $ObjApostilamento->anti_injection($num_aditivo),
            'AADITIAPEA' => $ObjApostilamento->anti_injection($prazo),
            'XADITIJUST' => $ObjApostilamento->anti_injection($objeto),
            'FADITIALPZ' => $ObjApostilamento->anti_injection($alteracao_prazo),
            'FADITIALVL' => $ObjApostilamento->anti_injection($alteracao_valor),
            'FADITIALCT' => 'NAO', 
            'CADITITALV' => $ObjApostilamento->anti_injection($tipoAlteracaoValor),
            'VADITIREQC' => $ObjApostilamento->anti_injection($valor_retroativo),
            'VADITIVTAD' => $ObjApostilamento->anti_injection($valor_total),
            'DADITIINVG' => $ObjApostilamento->anti_injection($vigenciaDataInicio),
            'DADITIFIVG' => $ObjApostilamento->anti_injection($vigenciaDataTermino),
            'DADITIINEX' => $ObjApostilamento->anti_injection($execucaoDataInicio),
            'DADITIFIEX' => $ObjApostilamento->anti_injection($execucaoDataTermino),
            'XADITIOBSE' => $ObjApostilamento->anti_injection($observacao),
            'NADITINMRL' => $ObjApostilamento->anti_injection($repNome),
            'EADITICGRL' => $ObjApostilamento->anti_injection($repCargo),
            'EADITICPFR' => $ObjApostilamento->anti_injection($repCPF),
            'EADITIIDRL' => $ObjApostilamento->anti_injection($repRG),
            'NADITIOERL' => $ObjApostilamento->anti_injection($repRgOrgao),
            'NADITIUFRL' => $ObjApostilamento->anti_injection($repRgUF),
            'NADITIEDRL' => $ObjApostilamento->anti_injection($repEstado),
            'NADITICDRL' => $ObjApostilamento->anti_injection($repCidade),
            'NADITINARL' => $ObjApostilamento->anti_injection($repNacionalidade),
            'CADITIECRL' => $ObjApostilamento->anti_injection($repEstCiv),
            'NADITIPRRL' => $ObjApostilamento->anti_injection($repProfissao),
            'NADITIMLRL' => $ObjApostilamento->anti_injection($repEmail),
            'EADITITLRL' => $ObjApostilamento->anti_injection($repTelefone),
            'NADITIRAZS' => $ObjApostilamento->anti_injection($razao_social_forn),
            'EADITILOGR' => $ObjApostilamento->anti_injection($logradouro_forn),
            'EADITICOMP' => $ObjApostilamento->anti_injection($compl_forn),
            'EADITIBAIR' => $ObjApostilamento->anti_injection($bairro_forn),
            'NADITICIDA' => $ObjApostilamento->anti_injection($cidade_forn),
            'CADITIESTA' => $ObjApostilamento->anti_injection($estado_forn),
            'EADITICPFC' => !empty($cpf_forn) ? $cpf_forn : '',
            'EADITICGCC' => !empty($cnpj_forn) ? $cnpj_forn : '',
            'nome_arquivo' => $nome_anexo,
            'arquivo'  => $bin_anexo
        );

        $validando = $ObjApostilamento->validarDadosAditivo($dadosAditivo);
        
        if($validando != false){
            $retorno = $ObjApostilamento->insereDadosAditivo($dadosAditivo);
            
            $documento = $ObjApostilamento->getUltimoAditivoCod($contrato);
            
            // inserir anexo ----------------------------------------------------------------------
            if(!empty($_SESSION['documento_anexo_incluir'])){
               
                foreach($_SESSION['documento_anexo_incluir'] as $docAnex){
                
                        if($docAnex['remover'] != "S"){
                            $dadosDocAnex = array(
                                                'cdocpcsequ'     => $documento,
                                                'edcanxnome'     => $docAnex['nome_arquivo'],
                                                'idcanxarqu'     => $docAnex['arquivo'],
                                                'tdcanxcada'     => $docAnex['data_inclusao'],
                                                'cusupocodi'     => $docAnex['usermod'],
                                                'ativo'             => 'S'
                                                );
                            $retorno = $ObjApostilamento->InsereDocumentosAnexos($dadosDocAnex);
                            if(empty($retorno)){
                                $response = array("status"=>false,"msm"=>"Erro ao inserir documento, por favor, contate o suporte!");
                                print(json_encode($response));
                                exit;
                            }
                        }
                        $i++;
                }
                unset($_SESSION['documento_anexo_incluir']);
            }
            //-------------------------------------------------------------------------------------
            unset($_SESSION['documento_anexo_incluir']);
    
            $response = array("status" => true,"msm" => "Aditivo salvo com sucesso!");
            print(json_encode($response));
        }
    break;

    case "AlterarAditivo":

        $contrato = $_POST['idregistro'];
        
        $documento = $_POST['aditivo_sel'];
        
        $num_aditivo = $_POST['numero_registro'];

        $fase_aditivo = $_POST['situacao_aditivo'];
        $tipoAditivo = $_POST['tipo_aditivo'];
        $objeto = $_POST['objeto'];
        $alteracao_prazo = $_POST['alteracao_prazo'];
        $alteracao_valor = $_POST['alteracao_valor'];
        $tipoAlteracaoValor = $_POST['tipoAlteracaoValor'];
        $valor_retroativo = $ObjApostilamento->floatvalue($_POST['valor_retroativo']);
        $valor_total = $ObjApostilamento->floatvalue($_POST['valor_total']);
        $prazo = $_POST['prazo'];
        $vigenciaDataInicio = $ObjContrato->date_transform($_POST['vigenciaDataInicio']);
        $vigenciaDataTermino = $ObjContrato->date_transform($_POST['vigenciaDataTermino']);
        $execucaoDataInicio = $ObjContrato->date_transform($_POST['execucaoDataInicio']);
        $execucaoDataTermino = $ObjContrato->date_transform($_POST['execucaoDataFinal']);
        $observacao = $_POST['observacao'];
        
        $repNome = $_POST['repNome'];
        $repCPF = $ObjApostilamento->limpaCPF_CNPJ($_POST['repCPF']);
        $repCargo = $_POST['repCargo'];
        $repRG = $_POST['repRG'];
        $repRgOrgao = $_POST['repRgOrgao'];
        $repRgUF = $_POST['repRgUF'];
        $repCidade = $_POST['repCidade'];
        $repEstado = $_POST['repEstado'];
        $repNacionalidade = $_POST['repNacionalidade'];
        $repEstCiv = $_POST['repEstCiv'];
        $repProfissao = $_POST['repProfissao'];
        $repEmail = $_POST['repEmail'];
        $repTelefone = $_POST['repTelefone'];
        if(!empty($_POST['CNPJ_CPF'])){
            $tipo_forn = $_POST['CNPJ_CPF'];
        }elseif(!empty($_POST['flagcpfcnpjhid'])){
            $tipo_forn = $_POST['flagcpfcnpjhid'];
        }
        $CnpjCpf_forn = $ObjApostilamento->limpaCPF_CNPJ($_POST['cpfcnpjhid']);
        
        $razao_social_forn = $_POST['razaosocial'];
        $logradouro_forn = $_POST['logradouro'];
        $compl_forn = $_POST['compl'];
        $cidade_forn = $_POST['cidade'];
        $bairro_forn = $_POST['bairro'];
        $estado_forn = $_POST['estado'];
        
        if($tipo_forn == 1){
            $cnpj_forn = $ObjApostilamento->anti_injection($CnpjCpf_forn);
        }else{
            $cpf_forn = $ObjApostilamento->anti_injection($CnpjCpf_forn);
        }

        $nome_anexo = $_SESSION['documento_anexo_incluir'][0]['nome_arquivo'];
        $bin_anexo = $_SESSION['documento_anexo_incluir'][0]['arquivo'];

        $dadosAditivo = array(
            'CDOCPCSEQU' => $ObjApostilamento->anti_injection($documento), // vem do contrato
            'CDOCPCSEQ1' => $ObjApostilamento->anti_injection($contrato),
            'CTPADISEQU' => $ObjApostilamento->anti_injection($tipoAditivo),
            'AADITINUAD' => $ObjApostilamento->anti_injection($_POST['numero_registro']),
            'AADITIAPEA' => $ObjApostilamento->anti_injection($prazo),
            'XADITIJUST' => $ObjApostilamento->anti_injection($objeto),
            'FADITIALPZ' => $ObjApostilamento->anti_injection($alteracao_prazo),
            'FADITIALVL' => $ObjApostilamento->anti_injection($alteracao_valor),
            'FADITIALCT' => 'NAO', 
            'CADITITALV' => $ObjApostilamento->anti_injection($tipoAlteracaoValor),
            'VADITIREQC' => $ObjApostilamento->anti_injection($valor_retroativo),
            'VADITIVTAD' => $ObjApostilamento->anti_injection($valor_total),
            'DADITIINVG' => $ObjApostilamento->anti_injection($vigenciaDataInicio),
            'DADITIFIVG' => $ObjApostilamento->anti_injection($vigenciaDataTermino),
            'DADITIINEX' => $ObjApostilamento->anti_injection($execucaoDataInicio),
            'DADITIFIEX' => $ObjApostilamento->anti_injection($execucaoDataTermino),
            'XADITIOBSE' => $ObjApostilamento->anti_injection($observacao),
            'NADITINMRL' => $ObjApostilamento->anti_injection($repNome),
            'EADITICGRL' => $ObjApostilamento->anti_injection($repCargo),
            'EADITICPFR' => $ObjApostilamento->anti_injection($repCPF),
            'EADITIIDRL' => $ObjApostilamento->anti_injection($repRG),
            'NADITIOERL' => $ObjApostilamento->anti_injection($repRgOrgao),
            'NADITIUFRL' => $ObjApostilamento->anti_injection($repRgUF),
            'NADITIEDRL' => $ObjApostilamento->anti_injection($repEstado),
            'NADITICDRL' => $ObjApostilamento->anti_injection($repCidade),
            'NADITINARL' => $ObjApostilamento->anti_injection($repNacionalidade),
            'CADITIECRL' => $ObjApostilamento->anti_injection($repEstCiv),
            'NADITIPRRL' => $ObjApostilamento->anti_injection($repProfissao),
            'NADITIMLRL' => $ObjApostilamento->anti_injection($repEmail),
            'EADITITLRL' => $ObjApostilamento->anti_injection($repTelefone),
            'CFASEDSEQU' => $ObjApostilamento->anti_injection($fase_aditivo),
            'NADITIRAZS' => $ObjApostilamento->anti_injection($razao_social_forn),
            'EADITILOGR' => $ObjApostilamento->anti_injection($logradouro_forn),
            'EADITICOMP' => $ObjApostilamento->anti_injection($compl_forn),
            'EADITIBAIR' => $ObjApostilamento->anti_injection($bairro_forn),
            'NADITICIDA' => $ObjApostilamento->anti_injection($cidade_forn),
            'CADITIESTA' => $ObjApostilamento->anti_injection($estado_forn),
            'EADITICPFC' => !empty($cpf_forn) ? $cpf_forn : '',
            'EADITICGCC' => !empty($cnpj_forn) ? $cnpj_forn : '',
            'nome_arquivo' => $nome_anexo,
            'arquivo'  => $bin_anexo
        );
        
        $seExisteCodAditivoContrato = $ObjApostilamento->verificarSeExisteNumeroAditivoContrato($contrato, $_POST['numero_registro']);
        
        $retorno_cod = 0; // Se não tiver registro na base
        
        if($seExisteCodAditivoContrato[0]->cdocpcsequ != $documento && ($seExisteCodAditivoContrato[0]->aaditinuad == $_POST['numero_registro'])){
            $retorno_cod = 1;
            $response = array("status"=>false,"msm"=>"Esse número de aditivo já está atribuído a outro registro. Favor, informe outro número.");
            print(json_encode($response));
            return false;
        }
       
        $validando = $ObjApostilamento->validarDadosAditivo($dadosAditivo);

        if($validando != false && $retorno_cod == 0){
         
            $retorno = $ObjApostilamento->atualizaDadosAditivo($dadosAditivo);
            
            // inserir anexo ----------------------------------------------------------------------

            if(!empty($_SESSION['documento_anexo_incluir'])){
                $i=0;
                $ok=array();
                foreach($_SESSION['documento_anexo_incluir'] as $docAnex){
                
                    
                    if($docAnex['remover'] == "S"){
                        $ObjApostilamento->DeletaDocumentoAnexo($docAnex['sequdoc'],$docAnex['sequarquivo']);
                        $ok[]=false;
                    }else{
                        $ok []=true;
                     
                        $seExisteDocumentoAnexo = $ObjApostilamento->VerificaSeJaExisteDocumentoAnexo($documento, $docAnex['sequarquivo'], $docAnex['nome_arquivo']);
                        if(empty($seExisteDocumentoAnexo) && $docAnex['remover'] != "S"){
                            $dadosDocAnex = array(
                                                'cdocpcsequ'     => $documento,
                                                'edcanxnome'     => $docAnex['nome_arquivo'],
                                                'idcanxarqu'     => $docAnex['arquivo'],
                                                'tdcanxcada'     => $docAnex['data_inclusao'],
                                                'cusupocodi'     => $docAnex['usermod'],
                                                'ativo'             => 'S'
                                                );
                            $retorno = $ObjApostilamento->InsereDocumentosAnexos($dadosDocAnex);
                            if(empty($retorno)){
                                $response = array("status"=>false,"msm"=>"Error não foi possivel editar o contrato, tente novamente");
                                print(json_encode($response));
                                exit;
                            }
                            unset($_SESSION['documento_anexo_incluir']);
                        }
                    }
                }

                if(!in_array(true,$ok)){
                    $response = array("status"=>false,"msm"=>"Informe: Documento anexo ");
                    print(json_encode($response));
                    exit;
                    break;
                }
                unset($_SESSION['documento_anexo_incluir']);
            }
            
            //-------------------------------------------------------------------------------------
            unset($_SESSION['documento_anexo_incluir']);
    
            $response = array("status" => true,"msm" => "Aditivo atualizado com sucesso.");
            print(json_encode($response));
        } else{
            //die('2');
            $response = array("status"=>false);
            print(json_encode($response));
        }
    break;

    case "incluirApostilamento":
        $codFornecedor = $_SESSION['codigo_forn'];
        $contrato = $_POST['idregistro'];       
        $fiscal = $_SESSION['fiscal_selecionado_incluir'];
        $tipoApostilamento = $_POST['tipo_apostilamento'];        
        $preenche_gestor = $_POST['habilita_gestor'];

        $dataApostilamento = $_POST['dataPublicacaoDom'];
        $gestorNome = $_POST['gestorNome'];
        $gestorMatricula = $_POST['gestorMatricula'];
        $gestorCPF = $_POST['gestorCPF'];
        $gestorEmail = $_POST['gestorEmail'];
        $gestorTelefone = $_POST['gestorTelefone'];
        $valor_retroativo = $ObjApostilamento->floatvalue($_POST['valor_retroativo_apostilamento']);
        $valor_apostilamento = $ObjApostilamento->floatvalue($_POST['valor_apostilamento']);
        
        $nome_anexo = $_SESSION['documento_anexo_incluir'][0]['nome_arquivo'];
        $bin_anexo = $_SESSION['documento_anexo_incluir'][0]['arquivo'];

        $dadosGestor = array(
            'nctrpcnmgt' => $ObjApostilamento->anti_injection($gestorNome),
            'nctrpcmtgt' => $ObjApostilamento->anti_injection($gestorMatricula),
            'nctrpccpfg' => $ObjApostilamento->anti_injection($ObjApostilamento->limpaCPF_CNPJ($gestorCPF)),
            'nctrpcmlgt' => $ObjApostilamento->anti_injection($gestorEmail),
            'ectrpctlgt' => $ObjApostilamento->anti_injection($gestorTelefone),
            'cdocpcsequ' => $ObjApostilamento->anti_injection($contrato)
        );
        if(empty($tipoApostilamento)){
            $response = array("status"=>false,"msm"=>"Informe: Tipo de Apostilamento");
            print(json_encode($response));
            exit;
            break;
        }
        //$idtipoApostilamento = $ObjApostilamento->codigoTipoApostilamento($tipoApostilamento);
        $idtipoApostilamento = $tipoApostilamento;
        //var_dump($idtipoApostilamento);die;
        $contaAnexos = count($_SESSION['documento_anexo_incluir']);
        $validaAnexo = true;
        for($i=0; $i<$contaAnexos; $i++){
            if($_SESSION['documento_anexo_incluir'][$i]['remover'] == "S"){
                $validaAnexo = false;
            }
        }   
           //if(intval($idtipoApostilamento[0]->ctpaposequ) == 5){
           if(intval($idtipoApostilamento) == 5){
                // var_dump("teste fornece");die;
                if(empty($_SESSION['razao_social'])){ 
                    $response = array("status"=>false,"msm"=>"Informe: Fornecedor");
                    print(json_encode($response));
                    exit;
                    break;
                }
            }
            
            $cod_fornecedor = $_SESSION['codigo_forn'];

            $dadosFornecedor = array(
                $dadosMedicao['AFORCRSEQU'] = $ObjApostilamento->anti_injection($cod_fornecedor)
            ); 
               // var_dump($dadosFornecedor);die;
            //if(intval($idtipoApostilamento[0]->ctpaposequ) == 2 || intval($idtipoApostilamento[0]->ctpaposequ) == 3){
            if(intval($idtipoApostilamento) == 2 || intval($idtipoApostilamento) == 3){
                if(empty($_SESSION['fiscal_selecionado_incluir'])){ 
                    $response = array("status"=>false,"msm"=>"Informe: Fiscal do contrato");
                    print(json_encode($response));
                    exit;
                    break;
                }
            }
            $dados_validar = array(
                //'tipo_apostilamento' => intval($idtipoApostilamento[0]->ctpaposequ),
                'tipo_apostilamento' => intval($idtipoApostilamento),
                'data' => $dataApostilamento,
                'gestorNome' => $_POST['gestorNome'],
                'gestorMatricula' => $_POST['gestorMatricula'],
                'gestorCPF' => $_POST['gestorCPF'],
                'gestorEmail' => $_POST['gestorEmail'],
                'gestorTelefone' => $_POST['gestorTelefone'],
                'valor_apostilamento' => $_POST['valor_apostilamento'],
                'nome_arquivo' => $_SESSION['documento_anexo_incluir'][0]['nome_arquivo'],
                'arquivo' => $_SESSION['documento_anexo_incluir'][0]['arquivo'],
                'contaAnexos' =>  $contaAnexos,
                'validaAnexos' =>  $validaAnexo
            );
            
            $validando = $ObjApostilamento->validarDados($dados_validar);

        
        if($validando != false){
                
          
            $dadosMedicao = array(
                //'CDOCPCSEQU'=> $ObjApostilamento->anti_injection($documento1), // vem do documento
                'CDOCPCSEQ2'=> $ObjApostilamento->anti_injection($contrato), // vem do contrato
                //'CTPAPOSEQU'=> $idtipoApostilamento[0]->ctpaposequ,
                'CTPAPOSEQU'=> $idtipoApostilamento,
                'DAPOSTCADA'=> $ObjApostilamento->date_transform($ObjApostilamento->anti_injection($dataApostilamento)),
                'NAPOSTNMGT'=> $ObjApostilamento->anti_injection($gestorNome),
                'NAPOSTCPFG'=> $ObjApostilamento->anti_injection($ObjApostilamento->limpaCPF_CNPJ($gestorCPF)),
                'NAPOSTMTGT'=> $ObjApostilamento->anti_injection($gestorMatricula),
                'NAPOSTMLGT'=> $ObjApostilamento->anti_injection($gestorEmail),
                'EAPOSTTLGT'=> $ObjApostilamento->anti_injection($gestorTelefone),
                'VAPOSTVTAP'=> $ObjApostilamento->anti_injection($valor_apostilamento),
                'VAPOSTRETR'=> $ObjApostilamento->anti_injection($valor_retroativo),
                'AFORCRSEQU'=> $ObjApostilamento->anti_injection($dadosFornecedor[0])
            );
            if(intval($idtipoApostilamento) == 5){
                $aforcrseq1 = $ObjApostilamento->verificarSeExisteFornecedorNoContrato($contrato);

                if (empty($aforcrseq1[0]->aforcrseq1)) {
                    $dadosContratosUpdadete = array('aforcrseq1' => $ObjApostilamento->anti_injection($dadosFornecedor[0]), 'cdocpcsequ' => $contrato);
                    $ObjApostilamento->UpdateContrato($dadosContratosUpdadete);
                }
            }

            $retorno = $ObjApostilamento->insereDadosApostilamento($dadosMedicao);

            $documento = $ObjApostilamento->getUltimoApostilamentoCod($contrato);
            
            //if(intval($idtipoApostilamento[0]->ctpaposequ) == 1 || intval($idtipoApostilamento[0]->ctpaposequ) == 4){
            if(intval($idtipoApostilamento) == 1 || intval($idtipoApostilamento) == 4){
                $dadosMedicao['EAPOSTMEMC'] = $ObjApostilamento->anti_injection($nome_anexo);
                $dadosMedicao['IAPOSTMEMC'] = $ObjApostilamento->anti_injection($bin_anexo);
             }else{
                $dadosMedicao['EAPOSTNAGT'] = $ObjApostilamento->anti_injection($nome_anexo);
                $dadosMedicao['IAPOSTAQGT'] = $ObjApostilamento->anti_injection($bin_anexo);
            }
            //if(intval($idtipoApostilamento[0]->ctpaposequ) == 2 || intval($idtipoApostilamento[0]->ctpaposequ) == 3){
            if(intval($idtipoApostilamento) == 2 || intval($idtipoApostilamento) == 3){
                // inserir fiscal-----------------------------------------------------------------------
                $okFiscal =array();                    
                foreach($_SESSION['fiscal_selecionado_incluir'] as $fiscal){
                  
                    $existe  = $ObjContrato->VerificaSeExisteDocumentoFiscal($documento, $ObjApostilamento->limpaCPF_CNPJ($fiscal->fiscalcpf));
                    // kim começar daqui
                    if($fiscal->remover == "S"){
                        $retornoDelete = $ObjApostilamento->RemoveFiscaldoContrato($ObjApostilamento->limpaCPF_CNPJ($fiscal->fiscalcpf), $documento);
                        $okFiscal[] = false;
                    }else{
                        $okFiscal[] = true;
                    }
                    if(empty($existe) && $fiscal->remover != "S"){
                        $dadosFiscal = array( 
                                            'cfiscdcpff' => $ObjApostilamento->limpaCPF_CNPJ($fiscal->fiscalcpf),
                                            'cdocpcsequ' => $documento,
                                            'cusupocodi' => $ObjContrato->anti_injection($_SESSION['_cusupocodi_']),
                                            'tdocfiulat' => date('Y-m-d H:i:s.u')
                                            );
                        $retornoFS = $ObjContrato->InsertDocumentoFiscal($dadosFiscal);
                        if(empty($retornoFS)){
                            $response = array("status" => false, "msm" => "Error não foi possivel editar o contrato, tente novamente ");
                            print(json_encode($response));
                            exit;
                        }
                    }
                }
                if(!in_array(true, $okFiscal) ){
                    $response = array("status"=>false,"msm"=>"Informe: Fiscal do contrato");
                            print(json_encode($response));
                            exit;
                        break;
                }
                unset($_SESSION['fiscal_selecionado_incluir']);
            
                //-------------------------------------------------------------------------------------
            }       
            // inserir anexo ----------------------------------------------------------------------
            if(!empty($_SESSION['documento_anexo_incluir'])){
                foreach($_SESSION['documento_anexo_incluir'] as $docAnex){
                
                    if($docAnex['remover'] != "S"){
                        $dadosDocAnex = array(
                                            'cdocpcsequ'     => $documento,
                                            'edcanxnome'     => $docAnex['nome_arquivo'],
                                            'idcanxarqu'     => $docAnex['arquivo'],
                                            'tdcanxcada'     => $docAnex['data_inclusao'],
                                            'cusupocodi'     => $docAnex['usermod'],
                                            'ativo'             => 'S'
                                            );
                        $retorno = $ObjApostilamento->InsereDocumentosAnexos($dadosDocAnex);
                        if(empty($retorno)){
                            $response = array("status"=>false,"msm"=>"Erro ao inserir documento, por favor, contate o suporte!");
                            print(json_encode($response));
                            exit;
                        }
                    }
                    $i++;
                }
                unset($_SESSION['documento_anexo_incluir']);
            }
            //-------------------------------------------------------------------------------------
    
            unset($_SESSION['fiscal_selecionado_incluir']);
            unset($_SESSION['documento_anexo_incluir']);

            $response = array("status" => true,"msm" => "Apostilamento salvo com sucesso.");
            print(json_encode($response));
        }
        
    break;

    case "uploadArquivo":
        unset($_SESSION['documento_medicao']); //limpo a sessão para não conter nenhum lixo
        unset($_SESSION['documento_anexo_incluir']);
        unset($_SESSION['documento_anexo']);
        
        $parametros = $ObjContrato->GetParametrosGerais();
        $ex                = array('.pdf');
        $exDb              = explode(',',$parametros->epargetdov);
        $SizeMaxPermitido  = $parametros->qpargetmad * 1024;
        
        $extensoes = $ex; 
        
        if(!empty($_FILES['documento'])){
            
                // for($i=0; $i <= (count($_FILES['documento']['name'])); $i++){ 
                $extArq = explode('.',$_FILES['documento']['name']);
                $sizeArq = $_FILES['documento']['size'];
                if($sizeArq > $SizeMaxPermitido || $sizeArq == 0 || !in_array('.'.$extArq[count($extArq)-1],$extensoes) ){
                    print_r(json_encode(array('sucess'=> false,'msm'=>"Tipo de arquivo não suportado. Selecione somente documento com extensão .pdf | Este arquivo ou é muito grande ou está vazio. Tamanho Máximo: ".$SizeMaxPermitido." Kb. | Tamanho do seu Arquivo: ".strval($sizeArq)."kb.")));
                    exit;
                }

                $nomeArquivo = $_FILES['documento']['name'];                
                $arquivoBin  = bin2hex(file_get_contents($_FILES['documento']['tmp_name']));

                $_SESSION['documento_anexo_incluir'] = $_SESSION['documento_medicao']  = array(
                                    'nome_arquivo' =>$nomeArquivo,
                                    'arquivo'      => $arquivoBin,
                                    'sequarquivo'  => ((count($_SESSION['documento_medicao']))+1),
                                    // 'sequdoc'      => $_POST['idRegistro'],
                                    'data_inclusao'=> date("Y-m-d H:i:s.u"),
                                    'usermod'      =>$ObjContrato->anti_injection($_SESSION['_cusupocodi_']),
                                    'ativo'        => 'S',
                                    'remover'       => 'N'
                                );
          //  }
            print_r(json_encode(array('sucess'=> true,'msm'=>"Upload Completo.")));
        }
        
    break;
    case "GetDocAnex":
            //unset($_SESSION['documento_medicao']); //limpo a sessão para não conter nenhum lixo
            $html ='<tr class="FootFiscaisDoc">';
            $html .='<td></td>';
            $html .='<td colspan="4">ARQUIVO</td>';
            $html .='<td colspan="4">DATA DA INCLUSÃO</td>';
            $html .='</tr>';
            //echo 'aqui...';
            $anexo = $_SESSION['documento_medicao'] = $_SESSION['documento_anexo_incluir'];

           // foreach($_SESSION['documento_medicao'] as $key => $anexo){
                if( $anexo['remover'] != 'S'){  
                    $html .='<tr bgcolor="#ffffff">';
                    $html .='<td><input type="radio" name="docanex" value="1"></td>';
                    $html .='<td colspan="4">'.$anexo['nome_arquivo'].'</td>';
                    $html .='<td colspan="4">'.$anexo["data_inclusao"].'</td>';
                    $html .='</tr>';
                }
            //}
            $html .='<tr bgcolor="#ffffff">';
            $html .='<td colspan="8"align="center">';
            $html .='<button type="button" class="botao" onclick="Subirarquivo()">Incluir Documento</button>';
            $html .='<button type="button" class="botao" id="btnRemoveAnexo">Retirar Documento</button>';
            $html .='</td>';
            $html .='</tr>';
            print_r($html);
    break;
    case "RemoveDocAnex":
             unset($_SESSION['documento_medicao']); //limpo a sessão para não conter nenhum lixo
             unset($_SESSION['documento_anexo_incluir']);
             $html ='<tr class="FootFiscaisDoc">';
             $html .='<td></td>';
             $html .='<td colspan="4">ARQUIVO</td>';
             $html .='<td colspan="4">DATA DA INCLUSÃO</td>';
             $html .='</tr>';
             $html .='<tr bgcolor="#ffffff">';
             $html .='<td colspan="8" bgcolor="#ffffff">Nenhum documento informado</td>';
             $html .='</tr>';
             $html .='<tr bgcolor="#ffffff">';
             $html .='<td colspan="8"align="center">';
             $html .='<button type="button" class="botao" onclick="Subirarquivo()">Incluir Documento</button>';
             $html .='<button type="button" class="botao" id="btnRemoveAnexo">Retirar Documento</button>';
             $html .='</td>';
             $html .='</tr>';
             $_SESSION['documento_medicao'] = $SessaoDocumento;
             $_SESSION['documento_anexo_incluir'] = $SessaoDocumento;
             print_r($html);
    break;
    case "IncluirMedicao":
        $contrato = $_POST['contrato'];
        $valor_media = $ObjApostilamento->floatvalue($_POST['vmedcovalm']);
        $data_inicio = $_POST['execucaoDataInicio'];
        $data_termino = $_POST['execucaoDataTermino'];  
        $observacao = $_POST['emedcoobse'];
        $nome_anexo = $_SESSION['documento_medicao']['nome_arquivo'];
        $bin_anexo = $_SESSION['documento_medicao']['arquivo'];

        $dadosMedicao = array(
            'cdocpcsequ'=> $ObjApostilamento->anti_injection($contrato),
            'cmedconane'=> $ObjApostilamento->anti_injection($nome_anexo),
            'imedcoanex'=> $ObjApostilamento->anti_injection($bin_anexo),
            'dmedcoinic'=> $ObjContrato->date_transform($ObjApostilamento->anti_injection($data_inicio)),
            'dmedcofinl'=> $ObjContrato->date_transform($ObjApostilamento->anti_injection($data_termino)),
            'emedcoobse'=> $ObjContrato->anti_injection($observacao),
            'cusupocod1'=> $ObjApostilamento->anti_injection($contrato),
            'vmedcovalm'=> $ObjApostilamento->anti_injection($valor_media)
        );
         
        $retorno = $ObjApostilamento->insertsMedicaoIncluir($dadosMedicao);            
        $response = array("status"=>true,"msm"=>"Medição incluída com sucesso.");
        print(json_encode($response));
    break;
    case 'validaDatas':
        
        $array_dados = explode(",", $_POST['datas']);
      
        $retorno = null;
        $contador = 0;
        foreach ($array_dados as $key => $dado) {
            $dado_linha[$key] = explode(".", $dado);
            $resultado["daditiinvg"] = $ObjContrato->date_transform($ObjApostilamento->anti_injection($dado_linha[$key][1]));
            $resultado["daditifivg"] = $ObjContrato->date_transform($ObjApostilamento->anti_injection($dado_linha[$key][2]));
            if($resultado["daditiinvg"] > $resultado["daditifivg"]){
                $contador++;
            }
        }

        if($contador != 0){
            $response = array("status"=>false,"msm"=>"A data final não pode ser menor que a data inicial.");
            print(json_encode($response));
        }else{
            $response = array("status"=>true,"msm"=>"Aditivo alterado com Sucesso.");
            print(json_encode($response));
        }
       
    break;
    case "AlterarDatasAditivo":
        
        $contrato = $_POST['numcontrato'];
        
        $array_dados = explode(",", $_POST['resultados']);
        $enviado = false;
        $retorno = null;
        foreach ($array_dados as $key => $dado) {
            $dado_linha[$key] = explode(".", $dado);
            $resultado["ectrpcnumf"] = $ObjApostilamento->anti_injection($contrato);
            $resultado["aaditinuad"] = $ObjApostilamento->anti_injection($dado_linha[$key][0]);
            $resultado["daditiinvg"] = $ObjContrato->date_transform($ObjApostilamento->anti_injection($dado_linha[$key][1]));
            $resultado["daditifivg"] = $ObjContrato->date_transform($ObjApostilamento->anti_injection($dado_linha[$key][2]));
            $resultado["daditiinex"] = $ObjContrato->date_transform($ObjApostilamento->anti_injection($dado_linha[$key][3]));
            $resultado["daditifiex"] = $ObjContrato->date_transform($ObjApostilamento->anti_injection($dado_linha[$key][4]));

            $retorno = $ObjApostilamento->AlterarAditivoContrato($resultado);
        }
        if(!empty($retorno)){
            $response = array("status"=>true,"msm"=>"Aditivo alterado com Sucesso!");
            print(json_encode($response));
        }else{
            $response = array("status"=>false,"msm"=>"Erro! Não foi possivel editar o aditivo, tente novamente! Cod: 871");
            print(json_encode($response));
        }
    break;
    case "AlterarMedicao":
           $anoContrato   = !empty($_POST['numcontrato']) ? explode('/', $_POST['numcontrato']) : array(1=>0);
           $contrato = $_POST['contrato'];
           $registro = $_POST['registro'];

           //if(array_key_exists(0, $_SESSION['documento_medicao']))
           
           
           if(array_key_exists(0, $_SESSION['documento_medicao'])){
               $nome_anexo = $_SESSION['documento_medicao'][0]->nome_arquivo;
               $bin_anexo = $_SESSION['documento_medicao'][0]->arquivo;
            }else{
                $nome_anexo = $_SESSION['documento_medicao']['nome_arquivo'];
                $bin_anexo = $_SESSION['documento_medicao']['arquivo'];
           }
           
           $amedconume = $_POST['amedconume'];
           $valor_media = $ObjApostilamento->floatvalue($_POST['vmedcovalm']);
           $data_inicio = $_POST['execucaoDataInicio'];
           $data_termino = $_POST['execucaoDataTermino'];
           $observacao = $_POST['emedcoobse'];
           

            $dadosMedicao = array(
                'cdocpcsequ'=> $ObjApostilamento->anti_injection($contrato),
                'cmedconane'=> $ObjApostilamento->anti_injection($nome_anexo),
                'amedconume'=> $ObjApostilamento->anti_injection($amedconume),
                'imedcoanex'=> $ObjApostilamento->anti_injection($bin_anexo),
                'cmedcosequ'=> $ObjApostilamento->anti_injection($registro),
                'dmedcoinic'=> $ObjContrato->date_transform($ObjApostilamento->anti_injection($data_inicio)),
                'dmedcofinl'=> $ObjContrato->date_transform($ObjApostilamento->anti_injection($data_termino)),
                'emedcoobse'=> $ObjContrato->anti_injection($observacao),
                'cusupocod1'=> $ObjApostilamento->anti_injection($contrato),
                'vmedcovalm'=> $ObjApostilamento->anti_injection($valor_media)
            ); 
            
            // Retorna da base uma medição existente para o contrato e número informados
            $seExisteCodMedicaoContrato = $ObjApostilamento->ValidarSeNumeroMedicaoExiste($dadosMedicao);
            
            // se o número informado for igual ao registro utilizado -> salva normal
            // se o número informado for diferente do registro utilizado e for de outro registro do mesmo contrato, dá erro
            
            $retorno_cod = 0; // Se não tiver registro na base
               
            if(!empty($seExisteCodMedicaoContrato)){
            
                if($seExisteCodMedicaoContrato[0] == "1"){
                    $mesmo_registro = $ObjApostilamento->verificarSeExisteNumeroMedicaoContrato($dadosMedicao['cdocpcsequ'], $dadosMedicao['cmedcosequ']);
                    
                    if(intval($mesmo_registro[0]->amedconume) != intval($dadosMedicao['amedconume'])){
                        $retorno_cod = 1;
                        $response = array("status"=>false,"msm"=>"Esse número de medição já está atribuído a outro registro. Favor, informe outro número.");
                        print(json_encode($response));
                        return false;
                    }
                }
            }

                if($retorno_cod == 0){
                $retorno = $ObjApostilamento->AlterarMedicao($dadosMedicao);
                if(!empty($retorno)){
                    $dadosDocumentos = array(
                                            'ctipgasequ' => $ObjContrato->anti_injection($_POST['comboGarantia']),
                                            'ctidocsequ' => $ObjContrato->anti_injection($_POST['coditipodoc']),
                                            'csitdcsequ' => $ObjContrato->anti_injection($_POST['codsequsituacaodoc']),
                                            'cfasedsequ' => $ObjContrato->anti_injection($_POST['codsequfasedoc']),
                                            'cmodocsequ' => $ObjContrato->anti_injection($_POST['codmodeldoc']),
                                            'cusupocodi' => $ObjContrato->anti_injection($_SESSION['_cusupocodi_']),
                                            'tdocpculat' => date("Y-m-d H:i:s.u"),
                                            'ctidocseq1' => $ObjContrato->anti_injection($_POST['codisequtipodoc']),
                                            'cfuchcsequ' => $ObjContrato->anti_injection($_POST['codisequfuncao']),
                                            'cchelisequ' => $ObjContrato->anti_injection($_POST['codisequckecklist']),
                                            'cdocpcsequ'=> $cdocpcsequ,
                                            'cmedconane'=> $ObjApostilamento->anti_injection($nome_anexo),
                                            'imedcoanex'=> $bin_anexo
                                            );
                                            
                    $response = array("status"=>true,"msm"=>"Medição editada com sucesso.");
                    print(json_encode($response));
                }else{
                    $response = array("status"=>false,"msm"=>"Erro! Não foi possivel editar a medição, tente novamente! Cod: 871");
                    print(json_encode($response));
                }
            }
            
    break;
    case 'ExcluirApostilamento':
        $registro = $ObjContrato->anti_injection($_POST['contrato']);
        
        $retorno = $ObjApostilamento->ExcluirApostilamento($registro);
        
        if(empty($retorno)){
            $response = array("status"=>false,"msm"=>"Erro! Não foi possivel excluir o contrato, tente novamente! cod: 860");
            print(json_encode($response));
            exit;
        }
    break;
    case 'ExcluirMedicao':
        $contrato = $ObjContrato->anti_injection($_POST['contrato']);
        $registro = $ObjContrato->anti_injection($_POST['doc']);
        
        $item_medicao = $ObjApostilamento->ContarItemMedicao($contrato, $registro);
        $contador = intval($item_medicao->count);
        
        if($contador > 0){
            for ($i = $contador; $i > 0; $i--) {
                $remove_item = $ObjApostilamento->ExcluirItemMedicao($contrato, $registro);
            }
        }

        $retorno = $ObjApostilamento->ExcluirMedicao($contrato, $registro);
        if(empty($retorno)){
            $response = array("status"=>false,"msm"=>"Erro! Não foi possivel excluir o contrato, tente novamente! cod: 860");
            print(json_encode($response));
            exit;
        }
    break;
    case 'ExcluirAditivo':
        $contrato = $ObjContrato->anti_injection($_POST['contrato']);
        $registro = $ObjContrato->anti_injection($_POST['doc']);
        
        $retorno = $ObjApostilamento->ExcluirAditivo($contrato, $registro);
        if(empty($retorno)){
            $response = array("status"=>false,"msm"=>"Erro! Não foi possivel excluir o contrato, tente novamente! cod: 860");
            print(json_encode($response));
            exit;
        }
    break;
}
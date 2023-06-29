<?php
# -------------------------------------------------------------------------
# Portal da Compras
# Programa: CadContratoConsolidadoPesquisar.php
# Autor:    Eliakim Ramos | João Madson
# Data:     12/12/2019
# -------------------------------------------------------------------------
# -------------------------------------------------------------------------
# Portal da Compras
# Programa: CadContratoConsolidadoPesquisar.php
# Autor:    Marcello Calvalcanti
# Data:     26/05/2021
# CR:       248174 
# -------------------------------------------------------------------------
# -------------------------------------------------------------------------
# Portal da Compras
# Programa: CadContratoConsolidadoPesquisar.php
# Autor:    Marcello Calvalcanti
# Data:     26/05/2021
# CR:       248617
# -------------------------------------------------------------------------
# -------------------------------------------------------------------------
# Portal da Compras
# Programa: CadContratoConsolidadoPesquisar.php
# Autor:    Marcello Calvalcanti
# Data:     21/07/2021
# CR:       249907
# ------------------------------------------------------------------------
# Alterado : Osmar Celestino
# Data: 17/03/2022
# Objetivo: CR #260719
# ------------------------------------------------------------------------
# Alterado : Osmar Celestino
# Data: 31/03/2022
# Objetivo: CR #261423
#---------------------------------------------------------------------------


session_start();
require_once dirname(__FILE__) . '/../funcoes.php';
require_once "ClassContratoPesquisar.php";
$ObjContrato = new ContratoPesquisar();
$arrayTirar  = array('.',',','-','/');

switch($_POST['op']){
    case "Fornecerdor":
        $cnpj = "";
        $Cpf  = "";
        $tudook = true;
        if(empty($_POST['cpf']) && $_POST['doc'] == "CPF"){
            $objJS = json_encode(array("status"=>false,"msm"=>"O CPF não pode ser vazio."));
            print($objJS);
            $tudook =false;
            exit;
        }else if($_POST['doc'] == "CPF"){
            $Cpf = $ObjContrato->anti_injection(str_replace($arrayTirar,'',$_POST['cpf']));
        }
        if(empty($_POST['cnpj']) && $_POST['doc'] == "CNPJ"){
            $objJS = json_encode(array("status"=>false,"msm"=>"O CNPJ não pode ser vazio."));
            print($objJS);
            $tudook =false;
            exit;
            
        }else if($_POST['doc'] == "CNPJ"){
            $cnpj = $ObjContrato->anti_injection(str_replace($arrayTirar,'',$_POST['cnpj'])); 
        }

        if(!$ObjContrato->validaCPF($Cpf) && $_POST['doc'] == "CPF"){
            $objJS = json_encode(array("status"=>false,"msm"=>"O CPF informado não é válido. Informe corretamente."));
            print($objJS);
            $tudook =false;
            exit;
        }
        if(!$ObjContrato->valida_cnpj($cnpj) && $_POST['doc'] == "CNPJ"){
            $objJS = json_encode(array("status"=>false,"msm"=>"O CNPJ informado não é válido. Informe corretamente."));
            print($objJS);
            $tudook =false;
            exit;
        }
        if($tudook){
            
            $DadosFornecedor = $ObjContrato->GetFornecedor($Cpf,$cnpj);
            if(!empty($DadosFornecedor)){
                $_SESSION['seqFornecedor'] = $DadosFornecedor->aforcrsequ;
                $strResp  = $DadosFornecedor->nforcrrazs."<br>";
                $strResp .= $DadosFornecedor->eforcrlogr.",";
                $strResp .= $DadosFornecedor->aforcrnume.',';
                $strResp .= $DadosFornecedor->eforcrcomp.',';
                $strResp .= $DadosFornecedor->eforcrbair."-";
                $strResp .= $DadosFornecedor->nforcrcida;
                $ObjContrato->DesconectaBanco();
                $objJS = json_encode(array("status"=>true,"msm"=>$strResp));
                print($objJS);
            }else{
                $objJS = json_encode(array("status"=>false,"msm"=>"Fornecedor não encontrado na base de dados."));
                print($objJS);
                $tudook =false;
                exit; 
            }        
        }
    break;
    case "OrgaoGestor":
        $internet = !empty($_POST['internet'])?$_POST['internet']:"";
        $cusupocodi = $_SESSION['_cusupocodi_'];
        $dados = $ObjContrato->GetOrgao($internet, $cusupocodi);
        $option="";
        $arrayCode = array();
        foreach($dados as $orgao){
                $arrayCode[] = $orgao->corglicodi;
        }
        if(count($dados) > 1){ //'.implode(',',$arrayCode).'
            $option.= '<option value="">Selecione um órgão...</option>';
        }
        $selected ="";
        foreach($dados as $orgao){
            if(!empty($_POST['vol'])){
                $selected = ($_POST['vol'] == $orgao->corglicodi)?"selected":"eliakim";
            }
            $option.='<option value="'.$orgao->corglicodi.'" '.$selected.'>'.$orgao->eorglidesc.' </option>';
        }
        $ObjContrato->DesconectaBanco();
        print_r($option);
    break;
    case "PesquisaContrato":
        
        $vigente = $_POST['vigente'];
        $cnpj = str_replace($arrayTirar,'',$_POST['cnpj']);
        $Cpf  = str_replace($arrayTirar,'',$_POST['cpf']);
        $tudook = true;

        if(!$ObjContrato->validaCPF($Cpf) && $_POST['doc'] == "CPF" && !empty($_POST['cpf'])){
            $objJS = json_encode(array("status"=>false,"msm"=>"O CPF informado não é válido. Informe corretamente."));
            print($objJS);
            $tudook =false;
            exit;
        }
        if(!$ObjContrato->valida_cnpj($cnpj) && $_POST['doc'] == "CNPJ" && !empty($_POST['cnpj'])){
            $objJS = json_encode(array("status"=>false,"msm"=>"O CNPJ informado não é válido. Informe corretamente."));
            print($objJS);
            $tudook =false;
            exit;
        }

        if(empty($_POST['Orgao']) && empty($_POST['numerocontratoano']) && empty($_POST['numeroScc']) && empty($_POST['cnpj']) && empty($_POST['cpf'])){
            $objJS = json_encode(array("status"=>false,"msm"=>"Selecione um órgão!"));
            print($objJS);
            $tudook =false;
            exit;
        }
        
         $ArrayDados = array(
                            'numerocontratoano' => $_POST['numerocontratoano'],
                            'Orgao'             => $_POST['Orgao'],
                            'cnpj'              => $cnpj,
                            'origem'            => "1,2,3,4,5",
                            'cpf'               => $Cpf,
                            "numeroScc"         => str_replace($arrayTirar,'',$_POST['numeroScc']),
                            'vigente'           => $vigente,
                            'dataInicial'       => $_POST['dataInicial'],
                            'dataFinal'       => $_POST['dataFinal']
                          );

        $dadosTabela = $ObjContrato->Pesquisar($ArrayDados);
        $dadosOrgao = $ObjContrato->GetOrgaoById($ArrayDados);
        $tbHtmlOrgao = array();
        $orgao = '';
        foreach($dadosOrgao as $infOrgao){
                foreach($dadosTabela as $informacoesTable){
                    if($informacoesTable->eorglidesc == $infOrgao->eorglidesc){
                        $orgao = $infOrgao->eorglidesc;
                        $cpfCNPJ        = (!empty($informacoesTable->aforcrccgc))?$informacoesTable->aforcrccgc:$informacoesTable->aforcrccpf;
                        $SCC            = "";
                        if(!empty($informacoesTable->ccenpocorg) && !empty($informacoesTable->ccenpounid) && !empty($informacoesTable->csolcocodi) && !empty($informacoesTable->asolcoanos)){
                            $SCC       = sprintf('%02s', $informacoesTable->ccenpocorg) . sprintf('%02s', $informacoesTable->ccenpounid) . '.' . sprintf('%04s', $informacoesTable->csolcocodi) . '/' . $informacoesTable->asolcoanos;
                        }
                        $situacaoForne = $ObjContrato->GetSituacaoFornecedor($informacoesTable->aforcrsequ);
                        $TipoCompra    = $ObjContrato->GetTipoCompra($informacoesTable->ctpcomcodi);
                        $SituacaoContrato  = $ObjContrato->GetSituacaoContrato($informacoesTable->cfasedsequ,$informacoesTable->codsequsituacaodoc);
                        
                        $tableHtml[]    = array(
                                                'eorglidesc' => $informacoesTable->eorglidesc,
                                                'cdocpcsequ' => $informacoesTable->cdocpcsequ,
                                                'SCC'        => $SCC,
                                                'ectrpcnumf' => $informacoesTable->ectrpcnumf,
                                                'ectrpcobje' => wordwrap($informacoesTable->ectrpcobje, 30, "\n", true),
                                                'etpcomnome' => $TipoCompra->etpcomnome,
                                                'nforcrrazs' => $informacoesTable->nforcrrazs,
                                                'cpfCNPJ'    => $ObjContrato->MascarasCPFCNPJ($cpfCNPJ),
                                                'esitdcdesc' => $SituacaoContrato->esitdcdesc
                                                ); 
                        
                    }
                }
                $tbHtmlOrgao[] = array('eorglidesc'=>$orgao);
                $orgao ="";
        }
        unset($informacoesTable);
        unset($situacaoForne);
        unset($TipoCompra);
        unset($SituacaoContrato);
        unset($cpfCNPJ);
        unset($SCC);
        $ObjContrato->DesconectaBanco();
        
         print(json_encode(array('status'=>true,'dados'=>$tableHtml,'orgao'=>$tbHtmlOrgao)));
    break;
    case "Pesquisa":
        $vigente = $_POST['vigente'];
        $cnpj = str_replace($arrayTirar,'',$_POST['cnpj']);
        $Cpf  = str_replace($arrayTirar,'',$_POST['cpf']);
        $tudook = true;

        if(!$ObjContrato->validaCPF($Cpf) && $_POST['doc'] == "CPF" && !empty($_POST['cpf'])){
            $objJS = json_encode(array("status"=>false,"msm"=>"O CPF informado não é válido. Informe corretamente."));
            print($objJS);
            $tudook =false;
            exit;
        }
        if(!$ObjContrato->valida_cnpj($cnpj) && $_POST['doc'] == "CNPJ" && !empty($_POST['cnpj'])){
            $objJS = json_encode(array("status"=>false,"msm"=>"O CNPJ informado não é válido. Informe corretamente."));
            print($objJS);
            $tudook =false;
            exit;
        }
         $ArrayDados = array(
                            'numerocontratoano' => $_POST['numerocontratoano'],
                            'Orgao'             => $_POST['Orgao'],
                            'cnpj'              => $cnpj,
                            'cpf'               => $Cpf,
                            "numeroScc"         => str_replace($arrayTirar,'',$_POST['numeroScc']),
                            'vigente'           => $vigente
                          );
        
        $dadosTabela = $ObjContrato->Pesquisar($ArrayDados);
        $dadosOrgao = $ObjContrato->GetOrgaoById($ArrayDados);
        $tbHtmlOrgao = array();
        $orgao = '';
        foreach($dadosOrgao as $infOrgao){
                foreach($dadosTabela as $informacoesTable){
                    if($informacoesTable->eorglidesc == $infOrgao->eorglidesc){
                        $orgao = $infOrgao->eorglidesc;
                        $cpfCNPJ        = (!empty($informacoesTable->aforcrccgc))?$informacoesTable->aforcrccgc:$informacoesTable->aforcrccpf;
                        $SCC            = "";
                        if(!empty($informacoesTable->ccenpocorg) && !empty($informacoesTable->ccenpounid) && !empty($informacoesTable->csolcocodi) && !empty($informacoesTable->asolcoanos)){
                            $SCC       = sprintf('%02s', $informacoesTable->ccenpocorg) . sprintf('%02s', $informacoesTable->ccenpounid) . '.' . sprintf('%04s', $informacoesTable->csolcocodi) . '/' . $informacoesTable->asolcoanos;
                        }
                        $situacaoForne = $ObjContrato->GetSituacaoFornecedor($informacoesTable->aforcrsequ);
                        $TipoCompra    = $ObjContrato->GetTipoCompra($informacoesTable->ctpcomcodi);
                        $SituacaoContrato  = $ObjContrato->GetSituacaoContrato(null, $informacoesTable->cfasedsequ);
                        if($SituacaoContrato->esitdcdesc == "CADASTRADO"){
                        $tableHtml[]    = array(
                                                'eorglidesc' => $informacoesTable->eorglidesc,
                                                'cdocpcsequ' => $informacoesTable->cdocpcsequ,
                                                'SCC'        => $SCC,
                                                'ectrpcnumf' => $informacoesTable->ectrpcnumf,
                                                'ectrpcobje' => wordwrap($informacoesTable->ectrpcobje, 30, "\n", true),
                                                'etpcomnome' => $TipoCompra->etpcomnome,
                                                'nforcrrazs' => $informacoesTable->nforcrrazs,
                                                'cpfCNPJ'    => $ObjContrato->MascarasCPFCNPJ($cpfCNPJ),
                                                'esitdcdesc' => $SituacaoContrato->esitdcdesc
                                                ); 
                                            }
                        
                    }
                }
                $tbHtmlOrgao[] = array('eorglidesc'=>$orgao);
                $orgao ="";
        }
        unset($informacoesTable);
        unset($situacaoForne);
        unset($TipoCompra);
        unset($SituacaoContrato);
        unset($cpfCNPJ);
        unset($SCC);
        $ObjContrato->DesconectaBanco();
        print(json_encode(array('status'=>true,'dados'=>$tableHtml,'orgao'=>$tbHtmlOrgao)));
    break;
}
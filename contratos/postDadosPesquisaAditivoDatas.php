<?php
# -------------------------------------------------------------------------
# Portal da Compras
# Programa: CadContratoManter.php
# Autor:    Eliakim Ramos | João Madson
# Data:     12/12/2019
# -------------------------------------------------------------------------
session_start();
require_once dirname(__FILE__) . '/../funcoes.php';
require_once "ClassContratoPesquisar.php";
require_once "ClassAditivoDatasPesquisar.php";

$ObjContrato = new ContratoPesquisar();
$ObjAditivoDatas = new AditivoDatasPesquisar();
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
        $dados = $ObjContrato->GetOrgao();
        $option="";
        $arrayCode = array();
        foreach($dados as $orgao){
                $arrayCode[] = $orgao->corglicodi;
        }
        if(count($dados) > 1){
            $option.= '<option value="'.implode(',',$arrayCode).'">TODOS</option>';
        }
        foreach($dados as $orgao){
            $option.='<option value="'.$orgao->corglicodi.'">'.$orgao->eorglidesc.'</option>';
        }
        $ObjContrato->DesconectaBanco();
        print_r($option);
    break;
    case "Pesquisa":
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
                            "numeroScc"         => str_replace($arrayTirar,'',$_POST['numeroScc'])
                          );
        $dadosTabela = $ObjAditivoDatas->Pesquisar($ArrayDados);
        //var_dump($dadosTabela);die;
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
                        $SituacaoContrato  = $ObjAditivoDatas->GetSituacaoContrato($informacoesTable->cfasedsequ);
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
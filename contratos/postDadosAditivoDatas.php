<?php
# -------------------------------------------------------------------------
# Portal da Compras
# Programa: postDadosAditivoDatas.php
# Autor:    Edson Dionisio | Madson
# Data:     28/04/2020
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
# Programa: CadContratoManter.php
# Autor:    Marcello Calvalcanti
# Data:     26/05/2021
# CR:       248617
# -------------------------------------------------------------------------

session_start();
require_once dirname(__FILE__) . '/../funcoes.php';
require_once "funcoesContrato.php";

$objFuncoes = new funcoesContrato();
$arrayTirar  = array('.',',','-','/');


switch($_POST['op']){
    // Da pesquisa de contratos com Aditivos de alteração de datas
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
            $Cpf = $objFuncoes->anti_injection(str_replace($arrayTirar,'',$_POST['cpf']));
        }
        if(empty($_POST['cnpj']) && $_POST['doc'] == "CNPJ"){
            $objJS = json_encode(array("status"=>false,"msm"=>"O CNPJ não pode ser vazio."));
            print($objJS);
            $tudook =false;
            exit;
            
        }else if($_POST['doc'] == "CNPJ"){
            $cnpj = $objFuncoes->anti_injection(str_replace($arrayTirar,'',$_POST['cnpj'])); 
        }

        if(!$objFuncoes->validaCPF($Cpf) && $_POST['doc'] == "CPF"){
            $objJS = json_encode(array("status"=>false,"msm"=>"O CPF informado não é válido. Informe corretamente."));
            print($objJS);
            $tudook =false;
            exit;
        }
        if(!$objFuncoes->valida_cnpj($cnpj) && $_POST['doc'] == "CNPJ"){
            $objJS = json_encode(array("status"=>false,"msm"=>"O CNPJ informado não é válido. Informe corretamente."));
            print($objJS);
            $tudook =false;
            exit;
        }
        if($tudook){
            
            $DadosFornecedor = $objFuncoes->GetFornecedor($Cpf,$cnpj);
            if(!empty($DadosFornecedor)){
                $_SESSION['seqFornecedor'] = $DadosFornecedor->aforcrsequ;
                $strResp  = $DadosFornecedor->nforcrrazs."<br>";
                $strResp .= $DadosFornecedor->eforcrlogr.",";
                $strResp .= $DadosFornecedor->aforcrnume.',';
                $strResp .= $DadosFornecedor->eforcrcomp.',';
                $strResp .= $DadosFornecedor->eforcrbair."-";
                $strResp .= $DadosFornecedor->nforcrcida;
                $objFuncoes->DesconectaBanco();
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
        $dados = $objFuncoes->GetOrgao();
        $option="";
        $arrayCode = array();
        foreach($dados as $orgao){
                $arrayCode[] = $orgao->corglicodi;
        }
        if(count($dados) > 1){ //'.implode(',',$arrayCode).'
            $option.= '<option value="">Selecione um órgão...</option>';
        }
        foreach($dados as $orgao){
            $option.='<option value="'.$orgao->corglicodi.'">'.$orgao->eorglidesc.'</option>';
        }
        $objFuncoes->DesconectaBanco();
        print_r($option);
    break;
    case "Pesquisa":
        $cnpj = str_replace($arrayTirar,'',$_POST['cnpj']);
        $Cpf  = str_replace($arrayTirar,'',$_POST['cpf']);
        $tudook = true;

        if(!$objFuncoes->validaCPF($Cpf) && $_POST['doc'] == "CPF" && !empty($_POST['cpf'])){
            $objJS = json_encode(array("status"=>false,"msm"=>"O CPF informado não é válido. Informe corretamente."));
            print($objJS);
            $tudook =false;
            exit;
        }

        if(!$objFuncoes->valida_cnpj($cnpj) && $_POST['doc'] == "CNPJ" && !empty($_POST['cnpj'])){
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
                            'cpf'               => $Cpf,
                            "numeroScc"         => str_replace($arrayTirar,'',$_POST['numeroScc'])
                          );
        $dadosTabela = $objFuncoes->PesquisaAltDatasAditivos($ArrayDados);
        //var_dump($dadosTabela);die;
        $dadosOrgao = $objFuncoes->GetOrgaoById($ArrayDados);
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
                        $situacaoForne = $objFuncoes->GetSituacaoFornecedor($informacoesTable->aforcrsequ);
                        $TipoCompra    = $objFuncoes->GetTipoCompra($informacoesTable->ctpcomcodi);
                        $SituacaoContrato  = $objFuncoes->GetSituacaoContrato($informacoesTable->cfasedsequ, $informacoesTable->codsequsituacaodoc);
                        if($SituacaoContrato->esitdcdesc == "CADASTRADO"){
                        $tableHtml[]    = array(
                                                'eorglidesc' => $informacoesTable->eorglidesc,
                                                'cdocpcsequ' => $informacoesTable->cdocpcsequ,
                                                'SCC'        => $SCC,
                                                'ectrpcnumf' => $informacoesTable->ectrpcnumf,
                                                'ectrpcobje' => wordwrap($informacoesTable->ectrpcobje, 30, "\n", true),
                                                'etpcomnome' => $TipoCompra->etpcomnome,
                                                'nforcrrazs' => $informacoesTable->nforcrrazs,
                                                'cpfCNPJ'    => $objFuncoes->MascarasCPFCNPJ($cpfCNPJ),
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
        $objFuncoes->DesconectaBanco();
         print(json_encode(array('status'=>true,'dados'=>$tableHtml,'orgao'=>$tbHtmlOrgao)));
    break;

    // Da alteração de datas
    case 'validaDatas':
        
        $array_dados = explode(",", $_POST['datas']);
      
        $retorno = null;
        $contador = 0;
        foreach ($array_dados as $key => $dado) {
            $dado_linha[$key] = explode(".", $dado);
            $resultado["daditiinvg"] = $objFuncoes->date_transform($objFuncoes->anti_injection($dado_linha[$key][1]));
            $resultado["daditifivg"] = $objFuncoes->date_transform($objFuncoes->anti_injection($dado_linha[$key][2]));
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
            $resultado["ectrpcnumf"] = $objFuncoes->anti_injection($contrato);
            $resultado["aaditinuad"] = $objFuncoes->anti_injection($dado_linha[$key][0]);
            $resultado["daditiinvg"] = $objFuncoes->date_transform($objFuncoes->anti_injection($dado_linha[$key][1]));
            $resultado["daditifivg"] = $objFuncoes->date_transform($objFuncoes->anti_injection($dado_linha[$key][2]));
            $resultado["daditiinex"] = $objFuncoes->date_transform($objFuncoes->anti_injection($dado_linha[$key][3]));
            $resultado["daditifiex"] = $objFuncoes->date_transform($objFuncoes->anti_injection($dado_linha[$key][4]));

            $retorno = $objFuncoes->AlterarAditivoContrato($resultado);
        }
        if(!empty($retorno)){
            $response = array("status"=>true,"msm"=>"Aditivo alterado com Sucesso!");
            print(json_encode($response));
        }else{
            $response = array("status"=>false,"msm"=>"Erro! Não foi possivel editar o aditivo, tente novamente! Cod: 871");
            print(json_encode($response));
        }
    break;
}
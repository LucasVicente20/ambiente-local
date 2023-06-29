<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadFiscalIncluir.php
# Autor:   João Madson
# Data:     19/10/2021
# Objetivo: Programa de incluir Fiscal CR#254713
#-------------------------------------------------------------------------

require_once dirname(__FILE__) . '/../funcoes.php';
require_once "./funcoesContrato.php";
$Objfuncoes = new funcoesContrato();
session_start();
$arrayTirar  = array('.',',','-','/');

// var_dump($_POST);

switch($_POST['op']){
    case "incluir":
        #Variáveis de mensagens de erro.
        $informe = "INFORME: ";
        $reclame = "";
        $validado = true;

        $dados["tipofiscal"] = 		$_POST["tipofiscal"];
        $dados["Nome"]       =      strtoupper(trim($Objfuncoes->anti_injection($_POST['Nome'])));
        $dados["CPF"]        =      trim($Objfuncoes->anti_injection(str_replace($arrayTirar,'',$_POST['CPF'])));
        $dados["Entidade"] 	 = 		$_POST["Entidade"];
        $dados["RegInsc"] 	 = 		$_POST["RegInsc"];
        $dados["Email"]    = trim($Objfuncoes->anti_injection($_POST['Email']));
        $dados["Fone"] = strtoupper(trim($Objfuncoes->anti_injection($_POST['Fone'])));
        
        if(empty($dados["Nome"])){
            $informe .= "Nome, ";
            $validado = false;
        }
        if(!empty($dados["CPF"])){
            $existe  = $Objfuncoes->GetFiscal($dados["CPF"]);
            $evalido = $Objfuncoes->validaCPF($dados["CPF"]);
            if(!empty($existe)){
                $ObjJS = json_encode(array('status'=>false,'msm'=>"Fiscal já cadastrado. Informe outro!"));
                print($ObjJS);
                exit;
            }
            if(!$evalido){
                $informe .= "CPF valido, ";
                $validado = false;
            }
        }else{
            $informe .= "CPF valido, ";
            $validado = false;
        }
        if(empty($dados["Email"])){
            $informe .= "E-mail, ";
            $validado = false;
        }
        if(empty($dados["Fone"])){
            $informe .= "Telefone, ";
            $validado = false;
        }

        if($validado == false){
            $informe = substr_replace($informe, '.', strrpos($informe, ", "));
            $ObjJS = json_encode(array('status'=>false,'msm'=>$informe));
            print($ObjJS);
            exit;
        }
        
        $dadosFiscal = array(
            'cfiscdcpff'=> $dados["CPF"],
            'nfiscdnmfs'=> strtoupper($dados["Nome"]),
            'nfiscdtipo'=> strtoupper($Objfuncoes->anti_injection($dados["tipofiscal"])),
            'nfiscdencp'=> strtoupper($Objfuncoes->anti_injection($dados["Entidade"])),
            'efiscdrgic'=> strtoupper($Objfuncoes->anti_injection($dados["RegInsc"])),
            'nfiscdmlfs'=> $dados["Email"],
            'efiscdtlfs'=> $dados["Fone"],
            'cusupocodi'=> $Objfuncoes->anti_injection($_SESSION['_cusupocodi_']),
            );

        $retorno = $Objfuncoes->insertFiscal($dadosFiscal);

        if(!empty($retorno)){
            $ObjJS = json_encode(array('status'=>true,'msm'=>"Fiscal incluído com sucesso!"));
            print($ObjJS); 
            exit; 
        }else{
            $ObjJS = json_encode(array('status'=>false,'msm'=>"Erro ao incluir Fiscal!"));
            print($ObjJS);
            exit;
        }
    break;
    case "atualizar":
        $informe = "INFORME: ";
        $reclame = "";
        $validado = true;

        $dados["tipofiscal"] = 		$_POST["tipofiscal"];
        $dados["Nome"]       =      strtoupper(trim($Objfuncoes->anti_injection($_POST['Nome'])));
        $dados["CPF"]        =      trim($Objfuncoes->anti_injection(str_replace($arrayTirar,'',$_SESSION["CPFFiscUpdate"])));
        $dados["Entidade"] 	 = 		$_POST["Entidade"];
        $dados["RegInsc"] 	 = 		$_POST["RegInsc"];
        $dados["Email"]    = trim($Objfuncoes->anti_injection($_POST['Email']));
        $dados["Fone"] = strtoupper(trim($Objfuncoes->anti_injection($_POST['Fone'])));
        
        if(empty($dados["Nome"])){
            $informe .= "Nome, ";
            $validado = false;
        }
        if(!empty($dados["CPF"])){
            $existe  = $Objfuncoes->GetFiscal($dados["CPF"]);
            $evalido = $Objfuncoes->validaCPF($dados["CPF"]);
            if(!$evalido){
                $informe .= "CPF valido, ";
                $validado = false;
            }
        }else{
            $informe .= "CPF valido, ";
            $validado = false;
        }
        if(empty($dados["Email"])){
            $informe .= "E-mail, ";
            $validado = false;
        }
        if(empty($dados["Fone"])){
            $informe .= "Telefone, ";
            $validado = false;
        }

        if($validado == false){
            $informe = substr_replace($informe, '.', strrpos($informe, ", "));
            $ObjJS = json_encode(array('status'=>false,'msm'=>$informe));
            print($ObjJS);
            exit;
        }
        
        $dadosFiscal = array(
            'cfiscdcpff'=> $dados["CPF"],
            'nfiscdnmfs'=> strtoupper($dados["Nome"]),
            'nfiscdtipo'=> strtoupper($Objfuncoes->anti_injection($dados["tipofiscal"])),
            'nfiscdencp'=> strtoupper($Objfuncoes->anti_injection($dados["Entidade"])),
            'efiscdrgic'=> strtoupper($Objfuncoes->anti_injection($dados["RegInsc"])),
            'nfiscdmlfs'=> $dados["Email"],
            'efiscdtlfs'=> $dados["Fone"],
            'cusupocodi'=> $Objfuncoes->anti_injection($_SESSION['_cusupocodi_']),
            );
        
        $retorno = $Objfuncoes->UpdateFiscal($dadosFiscal);
        if(!empty($retorno)){
            print_r(json_encode(array('msm'=>'Fiscal alterado com sucesso!','status'=>true)));
        }else{
            print_r(json_encode(array('msm'=>'Erro! ao alterar o Fiscal!','status'=>false)));
        }
    break;
    case "excluirFiscal": 
        if(!empty($_POST['cpf'])){
            $cpf = $Objfuncoes->anti_injection(str_replace($arrayTirar,'',$_POST['cpf']));
            $tipo = $Objfuncoes->anti_injection($_POST['tipo']);
            $temAlgumContrato = $Objfuncoes->VerificaSeFiscalEstaEmAlgumDocumento($cpf);
            if(!$temAlgumContrato){
                    $retorno = $Objfuncoes->DeletaFiscal($cpf);
                    if($retorno){
                        $dadosFiscal = $Objfuncoes->GetFiscal(null, $tipo);
                        $response = array('status'=>true, 'msm' => 'Fiscal excluido com sucesso!', "dados"=>$dadosFiscal);
                        print(json_encode($response));
                    }else{
                            $response = array("status"=>false, "msm"=>"Erro ao excluir o fiscal!");
                            print(json_encode($response));
                    }
            }else{
                $response = array("status"=>false, "msm"=>"Erro ao excluir o fiscal- ele esta associado a um contrato");
                print(json_encode($response));
            }
        }else{
            $response = array("status"=>false, "msm"=>"Erro ao excluir o fiscal- o cpf não pode ser vazio");
            print(json_encode($response));
        }
    break;
}


?>
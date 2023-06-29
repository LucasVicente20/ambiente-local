<?php
# -------------------------------------------------------------------------
# Portal da Compras
# Programa: CadContratoManter.php
# Autor:    Eliakim Ramos | João Madson
# Data:     12/12/2019
# -------------------------------------------------------------------------
# Autor: João Madson
# Data:  20/05/2021
# Objetivo: CR #248279
# -------------------------------------------------------------------------
# Autor: Osmar Celestino
# Data:  24/05/2021
# Objetivo: CR #248623
# -------------------------------------------------------------------------
# Autor: Osmar Celestino
# Data:  25/05/2021
# Objetivo: CR #247164
# -------------------------------------------------------------------------
# Autor: Marcello Albuquerque
# Data:  29/09/2021
# Objetivo: CR #253475
# -------------------------------------------------------------------------
# Alterado : Osmar Celestino
# Data: 11/04/2022
# Objetivo: CR #255207
#---------------------------------------------------------------------------
# Autor: Lucas Vicente
# Data:  15/02/2023
# Objetivo: CR #275671
# -------------------------------------------------------------------------
# Autor: Lucas André
# Data:  22/06/2023
# Objetivo: CR #285145
# -------------------------------------------------------------------------

session_start();
require_once dirname(__FILE__) . '/../funcoes.php';
require_once "ClassContratos.php";

$ObjContrato = new Contrato();
$arrayTirar  = array('.',',','-','/');

function floatvalue($val){
    $val = str_replace(",",".",$val);
    $val = preg_replace('/\.(?=.*\.)/', '', $val);
    //var_dump($val);
    return floatval($val);
}

switch($_POST['op']){
    case "exibe_ornecedor_session":
        //var_dump($_SESSION['dados_fornecedor_incluir']);
        //if(!empty($_POST['idregistro'])){
       if(!empty($_SESSION['dados_fornecedor_incluir'])){
         // var_dump($_SESSION['dados_fornecedor_incluir']);
        
                $fornecedorContrato = $_SESSION['dados_fornecedor_incluir']; // $ObjContrato->GetFornecedorContrato($_POST['idregistro']);
                if(!empty($fornecedorContrato)){
                    foreach($fornecedorContrato as $fonec){
                       // var_dump($fornecedorContrato);
                            $listaFornecedor[] = (object) array(
                                                                'aforcrsequ'=>$fonec->aforcrsequ,
                                                                'nforcrrazs'=>$fonec->nforcrrazs,
                                                                'eforcrlogr'=>$fonec->eforcrlogr,
                                                                'aforcrnume'=>$fonec->aforcrnume,
                                                                'eforcrcomp'=>$fonec->eforcrcomp,
                                                                'eforcrbair'=>$fornec->eforcrbair,
                                                                'aforcrccpf'=>$fonec->aforcrccpf,
                                                                'aforcrccgc'=>$fonec->aforcrccgc,
                                                                'cforcresta'=>$fonec->cforcresta,
                                                                'remover'=>'N'
                                                            );
                    }
              //      var_dump($listaFornecedor);
                    unset($_SESSION['dados_fornecedor_incluir']);
                    $_SESSION['dados_fornecedor_incluir'] = $listaFornecedor;
                    
                    // $_SESSION['dados_fornecedor_incluir'] = array_values($_SESSION['dados_fornecedor_incluir']);
                    $ObjJS = json_encode($_SESSION['dados_fornecedor_incluir']);
                    $ObjContrato->DesconectaBanco();
                    print_r($ObjJS);
                }else{
                    $ObjJS = json_encode(array("status"=>true,"msm"=>"Não há daddos para mostrar!!"));
                    $ObjContrato->DesconectaBanco();
                    print_r($ObjJS);
                }
            }else{
                $ObjJS = json_encode($_SESSION['dadosFornecedor']);
                $ObjContrato->DesconectaBanco();
                print_r($ObjJS);        
            }
     //   }
    break;
    case "Fornecerdor2":
        if($_POST['repassaSessao'] != 'true'){
            $cnpj = "";
            $Cpf  = "";
            if(!empty($_POST['cpf'])){
                $Cpf = $ObjContrato->anti_injection(str_replace($arrayTirar,'',$_POST['cpf']));
            }
            if(!empty($_POST['cnpj'])){
                $cnpj = $ObjContrato->anti_injection(str_replace($arrayTirar,'',$_POST['cnpj']));
            }

            $DadosFornecedor = $ObjContrato->GetFornecedor($Cpf,$cnpj);
            if(is_null($DadosFornecedor->aforcrsequ)){
                $ObjJS = json_encode(array('status'=>false,'msm'=>"Fornecedor não encontrado!"));
                print($ObjJS);
            }else{
                if(!empty($Cpf) || !empty($cnpj)){

                    
                  //  var_dump($Cnpj);
                    $cpfCnpjFornmascarado = !is_null($DadosFornecedor->aforcrccgc) ? $ObjContrato->MascarasCPFCNPJ($DadosFornecedor->aforcrccgc) : $ObjContrato->MascarasCPFCNPJ($DadosFornecedor->aforcrccpf);
                    
                    for($i=0;$i<count($_SESSION['dadosFornecedor']);$i++){
                        if((($cpfCnpjFornmascarado == $_SESSION['dadosFornecedor'][$i]->aforcrccpf) || ($cpfCnpjFornmascarado == $_SESSION['dadosFornecedor'][$i]->aforcrccgc)) && $_SESSION['dadosFornecedor'][$i]->remover == "N"){
                            $validaRepeticao = true;
                        }    
                    }
                    
                    if($validaRepeticao == true){
                        $ObjJS = json_encode(array('status'=>false,'msm'=>"Este fornecedor já contém na lista!"));
                        print($ObjJS);
                    }else{
                        
                        $_SESSION['dadosFornecedor'][] = (object) array(
                                                                            'aforcrsequ'=>$DadosFornecedor->aforcrsequ,
                                                                            'nforcrrazs'=>$DadosFornecedor->nforcrrazs,
                                                                            'eforcrlogr'=>$DadosFornecedor->eforcrlogr,
                                                                            'aforcrnume'=>$DadosFornecedor->aforcrnume,
                                                                            'eforcrcomp'=>$DadosFornecedor->eforcrcomp,
                                                                            'eforcrbair'=>$DadosFornecedor->eforcrbair,
                                                                            'nforcrcida'=>$DadosFornecedor->nforcrcida,
                                                                            'aforcrccpf'=>!empty($DadosFornecedor->aforcrccpf) ? $ObjContrato->MascarasCPFCNPJ($DadosFornecedor->aforcrccpf):$DadosFornecedor->aforcrccpf, 
                                                                            'aforcrccgc'=>!empty($DadosFornecedor->aforcrccgc) ? $ObjContrato->MascarasCPFCNPJ($DadosFornecedor->aforcrccgc):$DadosFornecedor->aforcrccgc,               
                                                                            'cforcresta'=>$DadosFornecedor->cforcresta,
                                                                            'remover'=>'N'
                                                                        );                                            
                        $ObjJS = json_encode($_SESSION['dadosFornecedor']);
                        $ObjContrato->DesconectaBanco();
                        print_r($ObjJS);        
                    }
                }else{
                    $ObjJS = json_encode(array('status'=>false,'msm'=>"O campo não pode ser vazio!"));
                    print($ObjJS);
                }
            }
        }else{
            $ObjJS = json_encode($_SESSION['dadosFornecedor']);
                        print_r($ObjJS);
        }
    break;
    case "ExibeFornecedorExtra":
        if(!empty($_POST['idregistro'])){
            if(empty($_SESSION['dadosFornecedor'])){
                $fornecedorContrato = $ObjContrato->GetFornecedorContrato($_POST['idregistro']);
                if(!empty($fornecedorContrato)){
                    foreach($fornecedorContrato as $fonec){
                            $listaFornecedor[] = (object) array(
                                                                'aforcrsequ'=>$fonec->aforcrsequ,
                                                                'nforcrrazs'=>$fonec->nforcrrazs,
                                                                'eforcrlogr'=>$fonec->eforcrlogr,
                                                                'aforcrnume'=>$fonec->aforcrnume,
                                                                'eforcrcomp'=>$fonec->eforcrcomp,
                                                                'eforcrbair'=>$fornec->eforcrbair,
                                                                'aforcrccpf'=>$ObjContrato->MascarasCPFCNPJ($fonec->aforcrccpf),
                                                                'aforcrccgc'=>$ObjContrato->MascarasCPFCNPJ($fonec->aforcrccgc),
                                                                'cforcresta'=>$fonec->cforcresta,
                                                                'remover'=>'N'
                                                            );
                    }
                    unset($_SESSION['dadosFornecedor']);
                    $_SESSION['dadosFornecedor'] = $listaFornecedor;
                    // $_SESSION['dadosFornecedor'] = array_values($_SESSION['dadosFornecedor']);
                    $ObjJS = json_encode($_SESSION['dadosFornecedor']);
                    $ObjContrato->DesconectaBanco();
                    print_r($ObjJS);
                }else{
                    $ObjJS = json_encode(array("status"=>true,"msm"=>"Não há daddos para mostrar!!"));
                    $ObjContrato->DesconectaBanco();
                    print_r($ObjJS);
                }
            }else{
                $ObjJS = json_encode($_SESSION['dadosFornecedor']);
                $ObjContrato->DesconectaBanco();
                print_r($ObjJS);        
            }
        }
    break;
    case "Fiscal":
        $Cpf  = "";
        if(!empty($_POST['cpf'])){
            $Cpf = $ObjContrato->anti_injection(str_replace($arrayTirar,'',$_POST['cpf']));
            if(!$ObjContrato->validaCPF($Cpf)){
                $objJS = json_encode(array("status"=>false,"msm"=>"O CPF informado não é válido. Informe corretamente."));
                print($objJS);
                $tudook =false;
                exit;
            }
        }
        $tipo = $ObjContrato->anti_injection($_POST['tipo']);
        $DadosFiscal = $ObjContrato->GetFiscal($Cpf,$tipo);
        $response = array();
        if(empty($DadosFiscal)){
            $response = array("status"=>false,"msm"=>"Fiscal não encontrado!");
            print(json_encode($response));
            exit;
        }else{
            // $DadosFiscal->cfiscdcpff = $ObjContrato->MascarasCPFCNPJ($DadosFiscal->cfiscdcpff);
            
            for($i = 0; $i < count($DadosFiscal); $i++){
                $DadosFiscal[$i]->cpfformatado = $ObjContrato->MascarasCPFCNPJ($DadosFiscal[$i]->cfiscdcpff);
            }
            $response = array("status"=>true,"dados"=>$DadosFiscal);
            $ObjJS = json_encode($response);
            print_r($ObjJS);
        }
      $ObjContrato->DesconectaBanco();                
    break;
    case "SelecFiscal":
          $dados = array();
          if(!empty($_POST['cpf'])){
            $dados['cfiscdcpff'] = $_POST['cpf'];
            $f=$ObjContrato->GetFiscal($dados['cfiscdcpff']);
            $_SESSION['fiscal_selecionado_incluir'][] = (object)  array(
                                                    'tipofiscal'      =>strtoupper( $f[0]->nfiscdtipo),
                                                    'fiscalnome'      => strtoupper($f[0]->nfiscdnmfs),
                                                    'fiscalmatricula' => strtoupper($f[0]->efiscdmtfs),
                                                    'fiscalcpf'       => strtoupper($ObjContrato->MascarasCPFCNPJ($f[0]->cfiscdcpff)),
                                                    'fiscalemail'     => $f[0]->nfiscdmlfs,
                                                    'fiscaltel'      =>strtoupper( $f[0]->efiscdtlfs),
                                                    'docsequ' =>  $_POST['doc'],
                                                    'registro'         =>strtoupper( $f[0]->efiscdrgic),
                                                    'ent'         =>strtoupper( $f[0]->nfiscdencp),
                                                    'docsituacao' => 'ATIVO',
                                                    'remover'=>'N'
                                    );
            $DadosDocFiscaisFiscal = $_SESSION['fiscal_selecionado_incluir'];
            $ObjJS = json_encode($DadosDocFiscaisFiscal);
            print_r($ObjJS);
            exit;
          }else{
              $response = array('msm'=>'Erro! o CPF é obrigatorio!!');
              echo json_encode($response);
          }

    break;
    case "RemoveFiscal":
        $MarcadorSeparado = explode('-',$_POST['marcador']);
        $sessaoFiscal = $_SESSION['fiscal_selecionado_incluir'];
        unset($_SESSION['fiscal_selecionado_incluir']);
        $i = 0;
        if(!empty($sessaoFiscal)){
            foreach($sessaoFiscal as $f){
                $cpflimpo = explode('-', $f->fiscalcpf);
                if($MarcadorSeparado[0] == $cpflimpo[0]){
                    $sessaoFiscal[$i]->remover = 'S';
                }
                $i++;
            }
        }else{
            echo "Erro! ao remover o fiscal";
        }
        $_SESSION['fiscal_selecionado_incluir']=$sessaoFiscal;
        $ObjJS = json_encode($_SESSION['fiscal_selecionado_incluir']);
        print_r($ObjJS);
    break;
    case "ModalAlterarFiscal":
        if(!empty($_POST['marcador'])){
            $dados['cfiscdcpff'] = $_POST['marcador'];
            $f=$ObjContrato->GetFiscal($dados['cfiscdcpff']);
            $selectedInterno = ($f[0]->nfiscdtipo == 'INTERNO')?'checked="checked"':'';
            $selectedEXTERNO = ($f[0]->nfiscdtipo == 'EXTERNO')?'checked="checked"':'';
            $html  = '<div class="modal-title textonormal">';
            $html .= 'ALTERAR FISCAL';
            $html .= '<span class="close" id="btn-fecha-modal">[ X ]</span>';
            $html .= '</div>';
            $html .= '<div class="modal-body">';
            $html .= '<div class="msg"></div>';
            $html .= '<form method="post" id="formAltFiscal" name="SccDados">';
            $html .= '<input type="hidden" name="op" id="op"  value="alterarFiscal">';
            $html .= '<table class="textonormal">';
            $html .='<tr>';
            $html .='<td align="left" colspan="2" id="tdmensagemM">';
            $html .='<div class="mensagemM">';
            $html .='<div class="error" colspan="5">';
            $html .='Erro';
            $html .='</div>';
            $html .='<span class="mensagem-textoM">';
            $html .='</span>';
            $html .='</div>';
            $html .='</td>';
            $html .='</tr>';    
            $html .= '<tr>';
            $html .= '<td bgcolor="#DCEDF7" class="tdModal">';
            $html .= 'Tipo de Fiscal :';
            $html .= '</td>';
            $html .= '<td>';
            $html .= '<input type="radio" name="tipofiscalr" id="radio-tipofiscal-interno" value="INTERNO" '.$selectedInterno.' required><label for="radio-tipofiscal-interno">INTERNO</label>';
            $html .= '<input type="radio" name="tipofiscalr" id="radio-tipofiscal-externo" value="EXTERNO" '.$selectedEXTERNO.' required><label for="radio-tipofiscal-externo">EXTERNO</label>';
            $html .= '</td>';
            $html .= '</tr>';
            $html .= '<tr>';
            $html .= '<td bgcolor="#DCEDF7" class="tdModal" style="margin-right:52%;width:259px;" >';
            $html .= ' Nome* :';
            $html .= '</td>';
            $html .= '<td class="textonormal" style="width:259px;">';
            $html .= '<input type="text" name="nomefiscal" id="nomefiscal" size="40" value="'.$f[0]->nfiscdnmfs.'" required> </td></tr>';
            $html .= '<tr> <td bgcolor="#DCEDF7" class="tdModal" style="margin-right:52%;width:259px;" > CPF* : </td> <td class="textonormal " style="width:259px;">';
            $html .= '<input type="text" name="cpffiscal" id="cpffiscal" size="11" value="'.$ObjContrato->MascarasCPFCNPJ($f[0]->cfiscdcpff).'" readonly required> </td> </tr>';
            $html .= '<tr> <td bgcolor="#DCEDF7" class="tdModal" style="margin-right:52%;width:259px;" > Entidade Competente : </td> ';
            $html .= '<td class="textonormal " style="width:259px;"> <input type="text" name="entidadefiscal" size="25" value="'.$f[0]->nfiscdencp.'" id="entidadefiscal"> </td> </tr>';
            $html .= '<tr> <td bgcolor="#DCEDF7" class="tdModal" style="margin-right:52%;width:259px;" > Registro ou Inscrição : </td>';
            $html .= '<td class="textonormal " style="width:259px;"> <input type="text" name="RegInsfiscal" size="11" value="'.$f[0]->efiscdrgic.'" id="RegInsfiscal"> </td> </tr>';
            $html .= '<tr> <td bgcolor="#DCEDF7" class="tdModal" style="margin-right:52%;width:259px;" > E-mail* : </td>';
            $html .= '<td class="textonormal " style="width:259px;"> <input type="email" required email size="25" value="'.$f[0]->nfiscdmlfs.'" name="emailfiscal" id="emailfiscal" style="text-transform:none;"> </td> </tr>';
            $html .= '<tr> <td bgcolor="#DCEDF7" class="tdModal" style="margin-right:52%;width:259px;" > Telefone* :</td>';
            $html .= '<td class="textonormal " style="width:259px;"><input class="telefone" type="tel" required name="telfiscal" size="11" value="'.$f[0]->efiscdtlfs.'" id="telfiscal"> </td> </tr> ';
            $html .= '<tr> <td colspan="3"> <div> <input class="botao_fechar_fiscal botao"  name="botao_fechar" value="Voltar" type="button" style="float:right">';
            $html .= '<span id="formModal:txtModalSolicitacaoVazio"></span> <input  type="submit" name="salvar" id="btnSalvarModal" value="Salvar" style="float:right" title="Salva" class="botao_salvar botao">';
            $html .= '</div> </td> </tr>';
            $html .= '</table> </form> </div>';
            $html .= '
            <script type=\"text/javascript\">
            $(document).ready(function() { 
                if($("#btnSalvarModal")){
                    console.log("teste1123");
                    alert("!");
                }
            }
            </script>';
            print_r($html);
      }
    break;
    case "alterarFiscal":
        $cpf      = trim($ObjContrato->anti_injection(str_replace($arrayTirar,'',$_POST['cpffiscal'])));
        $nome     = trim($ObjContrato->anti_injection($_POST['nomefiscal']));
        $email    = trim($ObjContrato->anti_injection($_POST['emailfiscal']));
        $telefone = trim($ObjContrato->anti_injection($_POST['telfiscal']));
        if(!empty($cpf)){
            $evalido = $ObjContrato->validaCPF($cpf);
           if(!empty($existe)){
                print_r(json_encode(array('msm'=>'Fiscal já cadastrado. Informe outro','Sucess'=>false)));
                exit;
            }
            if(!$evalido){
                print_r(json_encode(array('msm'=>'Informe:CPF valido!','Sucess'=>false)));
                    exit;
            }
        }else{
            print_r(json_encode(array('msm'=>'Informe:  CPF.','Sucess'=>false)));
            exit;
        }
        if(empty($nome)){
            print_r(json_encode(array('msm'=>'Informe: Nome.','Sucess'=>false)));
            exit;
        }
        if(empty($email)){
            print_r(json_encode(array('msm'=>'Informe: E-mail.','Sucess'=>false)));
            exit;
        }
        if(empty($telefone)){
            print_r(json_encode(array('msm'=>'Informe:Telefone.','Sucess'=>false)));
            exit;
        }
        
        $dadosFiscal = array(
                            'cfiscdcpff'=> $cpf,
                            'nfiscdnmfs'=> $nome,
                            'efiscdmtfs'=> $ObjContrato->anti_injection($_POST['matfiscal']),
                            'nfiscdtipo'=> $ObjContrato->anti_injection($_POST['tipofiscalr']),
                            'nfiscdencp'=> $ObjContrato->anti_injection($_POST['entidadefiscal']),
                            'efiscdrgic'=> $ObjContrato->anti_injection($_POST['RegInsfiscal']),
                            'nfiscdmlfs'=> $email,
                            'efiscdtlfs'=> $telefone,
                            'cusupocodi'=> $ObjContrato->anti_injection($_SESSION['_cusupocodi_']),
                            );
        $retorno = $ObjContrato->UpdateFiscal($dadosFiscal);
        if(!empty($retorno)){
            $i =0;
            $f=$ObjContrato->GetFiscal( $cpf);
            if(!empty($_SESSION['fiscal_selecionado_incluir'])){
                    foreach($_SESSION['fiscal_selecionado_incluir'] as $k){
                        if($cpf == $k->fiscalcpf){
                            unset($_SESSION['fiscal_selecionado_incluir'][$i]);
                        }
                        $i++;
                }
            }
            $_SESSION['fiscal_selecionado_incluir'][] = (object)  array(
                'tipofiscal'      => $f[0]->nfiscdtipo,
                'fiscalnome'      => strtoupper($f[0]->nfiscdnmfs),
                'fiscalmatricula' => $f[0]->efiscdmtfs,
                'fiscalcpf'       => $f[0]->cfiscdcpff,
                'fiscalemail'     => $f[0]->nfiscdmlfs,
                'fiscaltel'      => $f[0]->efiscdtlfs,
                'docsequ' =>  $_POST['doc'],
                'registro'         => $f[0]->efiscdrgic,
                'ent'         => $f[0]->nfiscdencp,
                'docsituacao' => 'ATIVO',
                'remover'=>'N'
            );
            print_r(json_encode(array('dados'=>$_SESSION['fiscal_selecionado_incluir'],'Sucess'=>true)));
        }else{
            print_r(json_encode(array('msm'=>'Erro! ao alterar o Fiscal!','Sucess'=>false)));
        }
    break;
    case "excluirFiscal": 
        if(!empty($_POST['cpf'])){
            $cpf = $ObjContrato->anti_injection($_POST['cpf']);
            $tipo = $ObjContrato->anti_injection($_POST['tipo']);
            $temAlgumContrato = $ObjContrato->VerificaSeFiscalEstaEmAlgumDocumento($cpf);
            if(!$temAlgumContrato){
                    $retorno = $ObjContrato->DeletaFiscal($cpf);
                    if($retorno){
                        $dadosFiscal = $ObjContrato->GetFiscal(null, $tipo);
                        $response = array('Sucess'=>true, 'msm' => 'Registro excluido com sucesso!', "dados"=>$dadosFiscal);
                        print(json_encode($response));
                    }else{
                            $response = array("Sucess"=>false, "msm"=>"Erro! ao excluir o fiscal");
                            print(json_encode($response));
                    }
            }else{
                $response = array("Sucess"=>false, "msm"=>"Erro! ao excluir o fiscal- ele esta associado a um contrato");
                print(json_encode($response));
            }
        }else{
            $response = array("Sucess"=>false, "msm"=>"Erro! ao excluir o fiscal- o cpf não pode ser vazio");
            print(json_encode($response));
      }
    break;
    case "ExcluirForneModal":
        foreach($_SESSION['dadosFornecedor'] as $key => $FornecedorSession){
            if($_POST['info'] == $FornecedorSession->aforcrsequ){
                $_SESSION['dadosFornecedor'][$key]->remover = 'S';
            }
         }
         $ObjJS = json_encode($_SESSION['dadosFornecedor']);
         print_r($ObjJS);
    break;
    case "PesquisaModalScc":
                // Checa os inputs de datas para a pesquisa e caso nenhuma seja definida, busca os ultimos 3 meses.
                if(!empty($_POST['dataIni']) || !empty($_POST['dataFim'])){
                    if(!empty($_POST['dataIni'])){
                        $dataIni = $ObjContrato->date_transform($_POST['dataIni']);
                        $dataIni = "'".$dataIni."'";
                    }
                    if(!empty($_POST['dataFim'])){
                        $dataFim = $ObjContrato->date_transform($_POST['dataFim']);
                        $dataFim = "'".$dataFim."'";
                    }
                }else{
                    $hojeMenostresMeses = date('Y-m-d', strtotime('-3 months'));
                    $dataIni = "'".$hojeMenostresMeses."'";
                }
                $ArrayDados = array(
                                    'numerocontratoano' => $_POST['numerocontratoano'],
                                    'Orgao'             => $_POST['Orgao'],
                                    'cnpj'              => str_replace($arrayTirar,'',$_POST['cnpj']),
                                    'cpf'               => str_replace($arrayTirar,'',$_POST['cpf']),
                                    "numeroScc"         => str_replace($arrayTirar,'',$_POST['numeroScc']),
                                    "CodTipoCompra"     => $_POST['CodTipoCompra'],
                                    "dataIni"           => $dataIni,
                                    "dataFim"           => $dataFim
                                );
                $tabelaDados = $ObjContrato->PesquisarSCC($ArrayDados);
                
                // $dadosTabela = $ObjContrato->PesquisarSCC($ArrayDados);
                $auxDadosTabela = array();
                for($d=0;$d<count($tabelaDados);$d++){
                    $origemD = (intval($tabelaDados[$d]->ctpcomcodi) == 2) ? "LICITACAO" : "OUTRA";
                    $validaScc = $ObjContrato->VerificaSeExisteContratoComEssaSCC($tabelaDados[$d]->csolcosequ, $tabelaDados[$d]->aforcrsequ, $origemD, $tabelaDados[$d]->citelpnuml);
                    if($validaScc == false){
                        
                        $auxDadosTabela[] = $tabelaDados[$d];
                       
                    }
                    
                }
                $dadosTabela = $auxDadosTabela;
                $_SESSION['dadosScc'] = $dadosTabela;
                
                //verifica se a scc possui mais de um lote
                for($i=0;$i<count($tabelaDados);$i++){
                    $iaux = $i;
                    $iaux++;
                    if($tabelaDados[$i]->csolcosequ == $tabelaDados[$iaux]->csolcosequ and $tabelaDados[$i]->aforcrsequ == $tabelaDados[$iaux]->aforcrsequ){
                        $tabelaDados[$i]->multLote = true;
                        while($tabelaDados[$i]->csolcosequ == $tabelaDados[$iaux]->csolcosequ and $tabelaDados[$i]->aforcrsequ == $tabelaDados[$iaux]->aforcrsequ){
                            $iaux++;
                        }
                        if($tabelaDados[$i]->csolcosequ == $tabelaDados[$iaux]->csolcosequ and $tabelaDados[$i]->aforcrsequ != $tabelaDados[$iaux]->aforcrsequ){
                            $iaux = $iaux - 1;
                        }
                        $i = $iaux;
                        
                    }
                }

                //compara com a variavel filtrada
                for($j=0;$j<count($dadosTabela);$j++){
                    if($tabelaDados[$j]->csolcosequ == $dadosTabela[$j]->csolcosequ and $tabelaDados[$j]->aforcrsequ == $dadosTabela[$j]->aforcrsequ and $dadosTabela[$j]->citelpnuml == $tabelaDados[$j]->citelpnuml){
                        if($tabelaDados[$j]->multLote == true){
                            $dadosTabela[$j]->multLote = true;
                        }

                    }
                }

                $tableHtml  = "<form method='post' action='' name='formtablemodal' id='formtablemodal'>";
                $tableHtml  .= "<table border='1' bordercolor='#75ADE6' width='100%' class='textonormal'>";
                $tableHtml  .="<thead>";
                $tableHtml  .="<tr>";
                $tableHtml  .='<td colspan="6" style="text-align: center; background-color: #75ADE6; font-weight: bold;" >';
                $tableHtml  .="RESULTADO DA PESQUISA";
                $tableHtml  .="</td>";
                $tableHtml  .="</tr>";
                if(!empty($dadosTabela)){
                $tableHtml  .='<tr style="background-color: #bfdaf2; text-align: center; font-weight: bold; color: #3165a5;">';
                $tableHtml  .="<td></td>";
                $tableHtml  .="<td>SCC</td>";
                $tableHtml  .="<td>ORIGEM</td>";
                $tableHtml  .="<td>LOTE</td>";
                $tableHtml  .="<td>FORNECEDOR</td>";
                $tableHtml  .="<td>CNPJ/CPF</td>";
                $tableHtml  .="</tr>";
                $tableHtml  .="</thead>";
                $tableHtml  .="<TBody>";
                        $cont=1;
                        foreach($dadosTabela as $informacoesTable){ 
                            $cpfCNPJ        = (!empty($informacoesTable->aforcrccgc))?$informacoesTable->aforcrccgc:$informacoesTable->aforcrccpf;
                            $flagCPFPJ      = (!empty($informacoesTable->aforcrccgc))? 'CNPJ' : 'CPF';

                            $SCC            = "";
                            if(!empty($informacoesTable->ccenpocorg) && !empty($informacoesTable->ccenpounid) && !empty($informacoesTable->csolcocodi) && !empty($informacoesTable->asolcoanos)){
                                $SCC       = sprintf('%02s', $informacoesTable->ccenpocorg) . sprintf('%02s', $informacoesTable->ccenpounid) . '.' . sprintf('%04s', $informacoesTable->csolcocodi) . '/' . $informacoesTable->asolcoanos;
                            }
                            $LINKSCC = '\'ConsAcompSolicitacaoCompraVersaoContrato.php?SeqSolicitacao='.$informacoesTable->csolcosequ.'&programa=window&irp='.$dadosIRP[0].'&ano=\''.$dadosIRP[1];
                            // var_dump($informacoesTable->ctpcomcodi);
                            if($informacoesTable->multLote == true){
                                $TipoCompra    = $ObjContrato->GetTipoCompra($informacoesTable->ctpcomcodi);
                                $funccaoJs = "selecionaScc($cont, this)"; 
                                $tableHtml     .="<input type='hidden' name='ctpcomcodi' value='".$informacoesTable->ctpcomcodi."' />";
                                $tableHtml     .="<tr>";
                                $tableHtml     .='<td> <input id="checkscc'.$cont.'" type="checkbox" name="seqScc[]" onclick="'. $funccaoJs.'" value="'.$informacoesTable->csolcosequ.'-0" /> </td>';
                                $tableHtml     .='<td>  <input type="hidden" name="sccselec-'.$informacoesTable->csolcosequ.'" value="'.$SCC.'" />'.$SCC.'</td>';
                                $tableHtml     .='<td> <input type="hidden" name="origselec-'.$informacoesTable->csolcosequ.'" value="'.$TipoCompra->etpcomnome.'" />';
                                $tableHtml     .=' <a href="#" type="hidden" value="'.$SCC.'" name="sccselec-'.$informacoesTable->csolcosequ.'" onclick="javascript:AbreJanelaDocumentos('.$LINKSCC.',window.screen.width-100,window.screen.height-200);" >'.$SCC.'</a></td>';
                                $tableHtml     .='<td style="text-align: center;"> <input type="hidden" name="lote" value="0" />TODOS</td>';
                                $tableHtml     .='<td><input type="hidden" id="k'.$cont.'" name="fornecedorSCC" disabled="true" value="'.$informacoesTable->aforcrsequ .'" />'.$informacoesTable->nforcrrazs.'</td>';
                                $tableHtml     .='<td> <input type="hidden" name="cpfselec-'.$informacoesTable->csolcosequ.'" value="'.$ObjContrato->MascarasCPFCNPJ($cpfCNPJ).'" />'.$ObjContrato->MascarasCPFCNPJ($cpfCNPJ).'</td>';
                                $tableHtml     .="</tr>";
                                $_SESSION["fornsequ".$informacoesTable->csolcosequ] = $informacoesTable->aforcrsequ;
                                $_SESSION["org".$informacoesTable->csolcosequ] = $informacoesTable->corglicodi;
                                $cont++;
                            }

                            $TipoCompra    = $ObjContrato->GetTipoCompra($informacoesTable->ctpcomcodi);
                            $funccaoJs = "selecionaScc($cont, this)";
                            $tableHtml     .="<input type='hidden' name='ctpcomcodi' value='".$informacoesTable->ctpcomcodi."' />";
                            $tableHtml     .="<tr>";
                            $tableHtml     .='<td> <input id="checkscc'.$cont.'" type="checkbox" name="seqScc[]" onclick="'. $funccaoJs.'" value="'.$informacoesTable->csolcosequ.'-'.$informacoesTable->citelpnuml.'" /> </td>';
                            $tableHtml     .='<td>  <input type="hidden" name="sccselec-'.$informacoesTable->csolcosequ.'" value="'.$SCC.'" />';
                            $tableHtml     .=' <a href="#" type="hidden" value="'.$SCC.'" name="sccselec-'.$informacoesTable->csolcosequ.'" onclick="javascript:AbreJanelaDocumentos('.$LINKSCC.',window.screen.width-100,window.screen.height-200);" >'.$SCC.'</a></td>';
                            $tableHtml     .='<td> <input type="hidden" name="origselec-'.$informacoesTable->csolcosequ.'" value="'.$TipoCompra->etpcomnome.'" />'.$TipoCompra->etpcomnome.'</td>';
                            if(!empty($informacoesTable->citelpnuml)){
                                if($informacoesTable->multLote != true and $informacoesTable->citelpnuml == 1  ){
                                    $tableHtml     .='<td style="text-align: center;"> <input type="hidden" name="lote" value="'.$informacoesTable->citelpnuml.'" />ÚNICO</td>';
                                }else{
                                    $tableHtml     .='<td style="text-align: center;"> <input type="hidden" name="lote" value="'.$informacoesTable->citelpnuml.'" />'.$informacoesTable->citelpnuml.'</td>';
                                }
                            }else{
                                $tableHtml     .='<td style="text-align: center;"> <input type="hidden" name="lote" value="" />-</td>';
                            }
                           
                            $tableHtml     .='<td><input type="hidden" id="k'.$cont.'" name="fornecedorSCC" disabled="true" value="'.$informacoesTable->aforcrsequ .'" />'.$informacoesTable->nforcrrazs.'</td>';
                            $tableHtml     .='<td> <input type="hidden" name="cpfselec-'.$informacoesTable->csolcosequ.'" value="'.$ObjContrato->MascarasCPFCNPJ($cpfCNPJ).'" />'.$ObjContrato->MascarasCPFCNPJ($cpfCNPJ).'</td>';
                            $tableHtml     .="</tr>";
                            $_SESSION["fornsequ".$informacoesTable->csolcosequ] = $informacoesTable->aforcrsequ;
                            $_SESSION["org".$informacoesTable->csolcosequ] = $informacoesTable->corglicodi;
                            $cont++;
                        }
                $tableHtml .="</TBody>";
                $tableHtml .="<tfoot>";
                $tableHtml .="<tr>";
                $tableHtml .="<td colspan='8'>";
                $tableHtml .='<input type="submit" value="Selecionar" name="selecionar" id="selecionar" class="botao" style="float:right" />';
                $tableHtml .="</td>";
                $tableHtml .="</tr>";
                $tableHtml .="</tfoot>";
            }else{
                // $tableHtml  .="</thead>";
                // $tableHtml .="<tr>";
                $tableHtml .="<td colspan='8' style='border:none;'>";
                $tableHtml .='Pesquisa sem ocorrências.';
                $tableHtml .="</td>";
                // $tableHtml .="</tr>";
            }
                $tableHtml .="</table>";
                $tableHtml .="</form>";
                unset($informacoesTable);
                unset($situacaoForne);
                unset($TipoCompra);
                unset($SituacaoContrato);
                unset($cpfCNPJ);
                unset($SCC);
                $ObjContrato->DesconectaBanco();
                print_r($tableHtml);
    break;
    case "ModalFornecedorCred":
        $html  = '<div class="modal-title">';
        $html .= 'MANTER FORNECEDOR CREDENCIADO';
        $html .= '<span class="close" id="btn-fecha-modal">[ X ]</span>';
        $html .= '</div>';
        $html .= '<div class="modal-body">';
        $html .= '<form action="" method="post" name="SccDados">';
        $html .= '<table class="textonormal" border="1"  bordercolor="#75ADE6">';
        $html .= '<tr>';
        $html .= '<td bgcolor="#DCEDF7" class="tdModal">';
        $html .= 'Identificador do Contratado :';
        $html .= '</td>';
        $html .= '<td>';
        $html .= '<input type="radio" name="cpf-cnpj" id="radio-cpf" value="CPF" style="font-size: 10.6667px;"><label for="cpf">CPF</label>';
        $html .= '<input type="radio" name="cpf-cnpj" id="radio-cnpj" value="CNPJ" checked="checked" style="font-size: 10.6667px;"><label for="CNPJ">CNPJ</label>';
        $html .= '</td>';
        $html .= '</tr>';
        $html .= '<tr>';
        $html .= '<td bgcolor="#DCEDF7" class="tdModal mostracnpj" style="margin-right:52%;width:259px; font-size: 10.6667px;">';
        $html .= 'CNPJ do Contratado :';
        $html .= '</td>';
        $html .= '<td bgcolor="#DCEDF7" class="tdModal mostracpf" style="display:none;margin-right:52%;width:259px; font-size: 10.6667px;" style="display:none;">';
        $html .= 'CPF do Contratado :';
        $html .= '</td>';
        $html .= '<td class="textonormal mostracnpj" style="margin-right:52%;width:259px;">';
        $html .= ' <input class="textonormal" type="text" name="cnpj" id="cnpj" size="16" style="font-size: 10.6667px;"><br>';
        $html .= '</td>';
        $html .= '<td class="textonormal mostracpf" style="display:none;width:259px;"> <input class="textonormal" type="text" name="cpf" id="cpf" size="16" style="font-size: 10.6667px;"></td>';
        $html .= '</tr>';
        // $html .= '<tr> <td colspan="3"> <div class="dadosFornec"> </div> </td> </tr>';
        $html .= '<tr> <td colspan="2"><div><input class="botao_fechar botao"  name="botao_fechar" value="Fechar" type="button" style="float:right">';
        $html .= '<span id="formModal:txtModalSolicitacaoVazio"></span> <input  type="button" name="adicionar" id="btnAdicionarModal" value="Adicionar" style="float:right" title="Adicionar" class="botao_final botao"> ';
        $html .= '</div> </td> </tr></table> </form> </div>';
        print_r($html);
    break;
    case "modalFiscal":
        $html  = '<div class="modal-title textonormal">';
        $html .= 'MANTER FISCAL';
        $html .= '<span class="close textonormal" id="btn-fecha-modal">[ X ]</span>';
        $html .= '</div>';
        $html .= '<div class="modal-body">';
        $html .= '<form action="" method="post" name="SccDados">';
        $html .= '<table class="textonormal" border="1"  bordercolor="#75ADE6">'; 
        $html .='<tr>';
		$html .='<td align="left" colspan="2" id="tdmensagemM">';
		$html .='<div class="mensagemM">';
		$html .='<div class="error" colspan="5">';
		$html .='Erro';
		$html .='</div>';
		$html .='<span class="mensagem-textoM">';
		$html .='</span>';
		$html .='</div>';
		$html .='</td>';
		$html .='</tr>';
        $html .= '<tr>';
        $html .= '<td bgcolor="#DCEDF7" class="tdModal">';
        $html .= 'Tipo de Fiscal :';
        $html .= '</td>';
        $html .= '<td>';
        $html .= '<input type="radio" name="tipofiscalr" id="radio-tipofiscal-interno" value="INTERNO" checked><label for="radio-tipofiscal-interno">INTERNO</label>';
        $html .= '<input type="radio" name="tipofiscalr" id="radio-tipofiscal-externo" value="EXTERNO"><label for="radio-tipofiscal-externo">EXTERNO</label>';
        $html .= '</td>';
        $html .= '</tr>';
        $html .= '<tr>';
        $html .= '<td bgcolor="#DCEDF7" class="tdModal" style="width:170px;" >';
        $html .= ' CPF :';
        $html .= '</td>';
        $html .= '<td class="textonormal " style="width:959px;">';
        $html .= '<input type="text" name="cpffiscal" id="cpffiscal">';
        $html .= '</td></tr>';
        $html .= '<tr> <td colspan="3">';
        $html .= ' <div><input class="botao_fechar_fiscal botao"  name="botao_fechar" value="Voltar" type="button" style="float:right"><span id="formModal:txtModalSolicitacaoVazio"></span>';
        $html .= ' <input  type="button" name="adicionarFiscal" id="btnAdicionarFiscalModal" value="Criar Novo Fiscal" style="float:right" title="Adicionar" class="botao_final botao">';
        $html .= '<input  type="button" name="pesquisar" id="btnPesquisaModal" value="Pesquisar" style="float:right" title="Pesquisar" class="botao_Pesquisar botao">';
        $html .= '<input  type="button" name="newpesquisar" id="btnNewPesquisaModal" value="Nova Pesquisa" style="float:right; display:none" title="Nova Pesquisa" class="botao_New_Pesquisar botao"> ';
        $html .= '</div> </td> </tr>';
        $html .= '<tr style="border:none"> <td colspan="3" style="border:none"> ';
        $html .= ' <!-- loading -->';
        $html .= ' <div class="load" id="LoadPesqFiscal" style="display:none;">';
        $html .= ' <div class="load-content" >';
        $html .= ' <img src="../midia/loading.gif" alt="Carregando">';
        $html .= ' <spam>Carregando...</spam>';
        $html .= ' </div>';
        $html .= ' </div>';
		$html .= ' <!-- Fim do loading -->';
        $html .= '<div class="dadosFiscal"> </div> </td> </tr>';
        $html .= '</table> </form> </div>';
        print_r($html);
    break;
    case "modalSccPesquisa":
        $origim = $ObjContrato->ListTipoCompra();
        $html  = '<div class="modal-title textonormal" >';
        $html .= 'PESQUISAR - SOLICITAÇÃO DE COMPRAS ';
        $html .= '<span class="close" id="btn-fecha-modal">[ X ]</span>';
        $html .= '</div>';
        $html .= '<div class="modal-body">';
        $html .= '<form action="" method="post" name="CadContratoIncluir">';
        $html .= '<table class="textonormal" width="100%">';
        $html .= '<tr border=1>';
        $html .= '<td bgcolor="#DCEDF7" class="tdModal" style="font-size: 10.6667px;">';
        $html .= 'Número da Solicitação de Compra/Ano :';
        $html .= '</td>';
        $html .= '<td colspan="2" style="font-size: 10.6667px;">';
        $html .= '<input type="text" id="modalNScc" name="modalNScc" class="textonormal" size="15" style="font-size: 10.6667px;"  >';
        $html .= '</td>';
        $html .= '</tr>';
        $html .= '<tr>';
        $html .= '<td bgcolor="#DCEDF7" class="tdModal" style="margin-right:52%;width:259px;font-size:10.6667px;" >';
        $html .= ' Origem :';
        $html .= '</td>';
        $html .= '<td class="textonormal" style="width:259px;" colspan="2">';
        $html .= '<select name="modal-origem" id="modal-origem" style="font-size: 10.6667px;" >';
        $html .= '<option class="textonormal" style="font-size: 10.6667px;" value="">TODAS</option>';
        foreach($origim as $o){
            $html .= '<option class="textonormal" style="font-size: 10.6667px;" value="'.$o->ctpcomcodi.'">'.$o->etpcomnome.'</option>';
        }
        $html .= '</select>';
        $html .= '</td></tr>';
        $html .= '<tr>';
        $html .= '<td bgcolor="#DCEDF7" class="tdModal" style="font-size: 10.6667px;">';
        $html .= 'Período Cadastramento Solicitação :';
        $html .= '</td>';
        $html .= '<td>';
        $html .= '<input type="text" name="DataIniPCS" id="DataIniPCS" class="textonormal" size="15" style="font-size: 10.6667px;" >';
        $html .= '<a href="javascript:janela(\'../calendario.php?Formulario=CadContratoIncluir&Campo=DataIniPCS\',\'Calendario\',220,170,1,0)"><img src="../midia/calendario.gif" border="0" alt=""></a>';
        $html .= '</td>';
        $html .= '<td>';
        $html .= '<input type="text" name="DataFimPCS" id="DataFimPCS" class="textonormal" size="15" style="font-size: 10.6667px;" >';
        $html .= '<a href="javascript:janela(\'../calendario.php?Formulario=CadContratoIncluir&Campo=DataFimPCS\',\'Calendario\',220,170,1,0)"><img src="../midia/calendario.gif" border="0" alt=""></a>';
        $html .= '</td>';
        $html .= '</tr>';
        $html .= '<tr><table width="100%" bordercolor="#75ADE6" border="1" cellspacing="0"><tbody><tr> <td colspan="3">';
        $html .= ' <div><input class="botao_fechar_fiscal botao"  name="botao_voltar" value="Voltar" type="button" style="float:right">';
        $html .= '<input  type="button" name="pesquisar" id="btnPesquisaModalSCC" value="Pesquisar" style="float:right" title="Pesquisar" class="botao_final botao">';
        $html .= '</div> </td></tr></tbody></table> </tr>';
        $html .= '<tr> <td colspan="3" style="border:none;"> ';
        $html .= ' <!-- loading -->';
        $html .= ' <div class="load" id="LoadPesqScc" style="display:none;">';
        $html .= ' <div class="load-content" >';
        $html .= ' <img src="../midia/loading.gif" alt="Carregando">';
        $html .= ' <spam>Carregando...</spam>';
        $html .= ' </div>';
        $html .= ' </div>';
		$html .= ' <!-- Fim do loading -->';
        $html .= ' <div id="selectDivModal"> ';
        $html .= ' </div> </td> </tr>';
        $html .= '</table> </form> </div>';
        print_r($html);
    break;
    case "ModalInserirFiscal":
        $html  = '<div class="modal-title textonormal">';
        $html .= 'INSERIR FISCAL';
        $html .= '<span class="close" id="btn-fecha-modal">[ X ]</span>';
        $html .= '</div>';
        $html .= '<div class="modal-body">';
        $html .= '<div class="msg"></div>';
        $html .= '<form method="post" id="formFiscal" name="SccDados">';
        $html .= '<input type="hidden" name="op" id="op"  value="insertFiscal">';
        $html .= '<table class="textonormal">';
        $html .='<tr>';
		$html .='<td align="left" colspan="2" id="tdmensagemM">';
		$html .='<div class="mensagemM">';
		$html .='<div class="error" colspan="5">';
		$html .='Erro';
		$html .='</div>';
		$html .='<span class="mensagem-textoM">';
		$html .='</span>';
		$html .='</div>';
		$html .='</td>';
		$html .='</tr>';    
        $html .= '<tr>';
        $html .= '<td bgcolor="#DCEDF7" class="tdModal">';
        $html .= 'Tipo de Fiscal :';
        $html .= '</td>';
        $html .= '<td>';
        $html .= '<input type="radio" name="tipofiscalr" id="radio-tipofiscal-interno" value="INTERNO" required checked><label for="radio-tipofiscal-interno">INTERNO</label>';
        $html .= '<input type="radio" name="tipofiscalr" id="radio-tipofiscal-externo" value="EXTERNO" required><label for="radio-tipofiscal-externo">EXTERNO</label>';
        $html .= '</td>';
        $html .= '</tr>';
        $html .= '<tr>';
        $html .= '<td bgcolor="#DCEDF7" class="tdModal" style="margin-right:52%;width:259px;" >';
        $html .= ' Nome* :';
        $html .= '</td>';
        $html .= '<td class="textonormal" style="width:259px;">';
        $html .= '<input type="text" name="nomefiscal" id="nomefiscal" required> </td></tr>';
        $html .= '<tr class="mostramatricula" style="display:none;"><td  bgcolor="#DCEDF7" class="tdModal" style="margin-right:52%;width:259px;">Matrícula*</td>'; //teste
        $html .= '<td class="textonormal" style="width:259px;"><input type="text" name="matfiscal" id="matfiscal"> </td></tr>'; //teste
        $html .= '<tr> <td bgcolor="#DCEDF7" class="tdModal" style="margin-right:52%;width:259px;" > CPF* : </td> <td class="textonormal " style="width:259px;">';
        $html .= '<input type="text" name="cpffiscal" id="cpffiscal" required> </td> </tr>';
        $html .= '<tr> <td bgcolor="#DCEDF7" class="tdModal" style="margin-right:52%;width:259px;" > Entidade Competente : </td> ';
        $html .= '<td class="textonormal " style="width:259px;"> <input type="text" name="entidadefiscal" id="entidadefiscal"> </td> </tr>';
        $html .= '<tr> <td bgcolor="#DCEDF7" class="tdModal" style="margin-right:52%;width:259px;" > Registro ou Inscrição : </td>';
        $html .= '<td class="textonormal " style="width:259px;"> <input type="text" name="RegInsfiscal" id="RegInsfiscal"> </td> </tr>';
        $html .= '<tr> <td bgcolor="#DCEDF7" class="tdModal" style="margin-right:52%;width:259px;" > E-mail* : </td>';
        $html .= '<td class="textonormal " style="width:259px;"> <input type="email" required email name="emailfiscal" id="emailfiscal"> </td> </tr>';
        $html .= '<tr> <td bgcolor="#DCEDF7" class="tdModal" style="margin-right:52%;width:259px;" > Telefone* :</td>';
        $html .= '<td class="textonormal " style="width:259px;"><input type="tel"  required name="telfiscal" id="telfiscal"> </td> </tr> ';
        $html .= '<tr> <td colspan="3"> <div> <input class="botao_fechar_fiscal botao"  name="botao_fechar" value="Voltar" type="button" style="float:right">';
        $html .= '<span id="formModal:txtModalSolicitacaoVazio"></span> <input  type="button" name="salvar" id="btnSalvarModal" value="Salvar" style="float:right" title="Salva" class="botao_salvar botao">';
        $html .= '</div> </td> </tr>';
        $html .= '</table> </form> </div>';
        $html .= '
        <script type=\"text/javascript\">
        $(document).ready(function() { 
            if($("#btnSalvarModal")){
                alert("!");
            }
        }
        </script>';
        print_r($html);
    break;
    case "insertFiscal":
        
        $cpf      = trim($ObjContrato->anti_injection(str_replace($arrayTirar,'',$_POST['cpffiscal'])));
        $nome     = strtoupper(trim($ObjContrato->anti_injection($_POST['nomefiscal'])));
        $email    = trim($ObjContrato->anti_injection($_POST['emailfiscal']));
        $telefone = strtoupper(trim($ObjContrato->anti_injection($_POST['telfiscal'])));
       if(!empty($cpf)){
            $existe  = $ObjContrato->GetFiscal($cpf);
            $evalido = $ObjContrato->validaCPF($cpf);
            if(!empty($existe)){
                print_r(json_encode(array('msm'=>'Fiscal já cadastrado. Informe outro','Sucess'=>false)));
                exit;
            }
            if(!$evalido){
                print_r(json_encode(array('msm'=>'Informe:CPF valido!','Sucess'=>false)));
                    exit;
            }
        }else{
            print_r(json_encode(array('msm'=>'Informe:CPF.','Sucess'=>false)));
            exit;
        }
        if(empty($nome)){
            print_r(json_encode(array('msm'=>'Informe:Nome.','Sucess'=>false)));
            exit;
        }
        if(empty($email)){
            print_r(json_encode(array('msm'=>'Informe:E-mail.','Sucess'=>false)));
            exit;
        }
        if(empty($telefone)){
            print_r(json_encode(array('msm'=>'Informe:Telefone.','Sucess'=>false)));
            exit;
        }
        $dadosFiscal = array(
                            'cfiscdcpff'=> $cpf,
                            'nfiscdnmfs'=> strtoupper($nome),
                            'efiscdmtfs'=> $ObjContrato->anti_injection($_POST['matfiscal']),
                            'nfiscdtipo'=> strtoupper($ObjContrato->anti_injection($_POST['tipofiscalr'])),
                            'nfiscdencp'=> strtoupper($ObjContrato->anti_injection($_POST['entidadefiscal'])),
                            'efiscdrgic'=> strtoupper($ObjContrato->anti_injection($_POST['RegInsfiscal'])),
                            'nfiscdmlfs'=> $email,
                            'efiscdtlfs'=> $telefone,
                            'cusupocodi'=> $ObjContrato->anti_injection($_SESSION['_cusupocodi_']),
                            );

       // if($_SESSION['fiscal_selecionado_incluir'][0]->fiscalcpf == $cpf){
       // $retorno = $ObjContrato->insertFiscal($dadosFiscal);
       // echo 'teste1';
            
      //  }else{
        //echo 'teste2';
        $retorno = $ObjContrato->insertFiscal($dadosFiscal);
        
        //$retorno = $ObjContrato;
        if(!empty($retorno)){
            $DadosFiscal = $ObjContrato->GetFiscal($cpf);
            $_SESSION['fiscal_selecionado_incluir'][] = (object)  array(
                'tipofiscal'      =>strtoupper( $DadosFiscal[0]->nfiscdtipo),
                'fiscalnome'      => strtoupper($DadosFiscal[0]->nfiscdnmfs),
                'fiscalcpf'       => strtoupper($DadosFiscal[0]->cfiscdcpff),
                'fiscalmatricula' => strtoupper($f[0]->efiscdmtfs),
                'fiscalemail'     => $DadosFiscal[0]->nfiscdmlfs,
                'fiscaltel'      => strtoupper($DadosFiscal[0]->efiscdtlfs),
                'docsequ' =>  $_SESSION['doc_fiscal'],
                'registro'         => $DadosFiscal[0]->efiscdrgic,
                'ent'         => $DadosFiscal[0]->nfiscdencp,
                'docsituacao' => 'ATIVO',
                'remover'=>'N'
                );
                $ObjJS = json_encode(array("Sucess"=>true,"dados"=>$_SESSION['fiscal_selecionado_incluir']));
                $ObjContrato->DesconectaBanco();
                print_r($ObjJS); 
                exit;    
        }else{
            print_r(json_encode(array('msm'=>'Erro! ao cadastrar fiscal','Sucess'=>false)));
            exit;
        }  
    break;
    case "uploadArquivo":
        $parametros = $ObjContrato->GetParametrosGerais();
        $ex                = array('.pdf', '.PDF');
        $str = strtolower($str);
        $exDb              = explode(',',$parametros->epargetdov);
        $SizeMaxPermitido  = $parametros->qpargetmad * 1024;
        $extensoes = $ex; 
        if(!empty($_FILES['documento'])){
            for($i=0;$i<= (count($_FILES['documento']['name'])-1); $i++){ 
                $extArq = explode('.',$_FILES['documento']['name'][$i]);
                $sizeArq = $_FILES['documento']['size'][$i];
                if($sizeArq > $SizeMaxPermitido || $sizeArq == 0 || !in_array('.'.$extArq[count($extArq)-1],$extensoes) ){
                    print_r(json_encode(array('sucess'=> false,'msm'=>"Tipo de arquivo não suportado. Selecione somente documento com extensão .pdf | Este arquivo ou é muito grande ou está vazio. Tamanho Máximo: ".$SizeMaxPermitido." Kb. | Tamanho do seu Arquivo: ".strval($sizeArq)."kb.")));
                    exit;
                }
                
                if(strlen($_FILES['documento']['name'][$i]) > 100){
                    print_r(json_encode(array('sucess'=> false,'msm'=>"Nome do Arquivo deve ser menor que 100 caracetes.")));
                    exit;
                }
                $nomeArquivo = $_FILES['documento']['name'][$i];
                $arquivoBin  = bin2hex(file_get_contents($_FILES['documento']['tmp_name'][$i]));
                if(!empty($_SESSION['documento_anexo_incluir'])){
                    if(!in_array($nomeArquivo, $_SESSION['documento_anexo_incluir'])){
                                $_SESSION['documento_anexo_incluir'][]  = array(
                                                                        'nome_arquivo' =>$nomeArquivo,
                                                                        'arquivo'      => $arquivoBin,
                                                                        'sequarquivo'  => ((count($_SESSION['documento_anexo_incluir']))+1),
                                                                        'data_inclusao'=> date("Y-m-d H:i:s.u"),
                                                                        'usermod'      =>$ObjContrato->anti_injection($_SESSION['_cusupocodi_']),
                                                                        'ativo'        => 'S',
                                                                        'remover'       => 'N'
                                                                    );
                    }else{
                        foreach($_SESSION['documento_anexo_incluir'] as $key =>  $item){
                            if($item['nome_arquivo'] == $nomeArquivo && $item['remover'] == "S"){
                                $_SESSION['documento_anexo_incluir'][$key]['remover'] = "N";
                            }
                        }
                    }
                }else{
                        $_SESSION['documento_anexo_incluir'][]  = array(
                            'nome_arquivo' =>$nomeArquivo,
                            'arquivo'      => $arquivoBin,
                            'sequarquivo'  => ((count($_SESSION['documento_anexo_incluir']))+1),
                            // 'sequdoc'      => $_POST['idRegistro'],
                            'data_inclusao'=> date("Y-m-d H:i:s.u"),
                            'usermod'      =>$ObjContrato->anti_injection($_SESSION['_cusupocodi_']),
                            'ativo'        => 'S',
                            'remover'       => 'N'
                        );
                }
            }
            print_r(json_encode(array('sucess'=> true,'msm'=>"Upload completo.")));
        }
        
    break;
    case "GetDocAnex":
                $html ='<tr class="FootFiscaisDoc">';
                $html .='<td></td>';
                $html .='<td colspan="4">ARQUIVO</td>';
                $html .='<td colspan="4">DATA DA INCLUSÃO</td>';
                $html .='</tr>';
                foreach($_SESSION['documento_anexo_incluir'] as $key => $anexo){
                    if( $anexo['remover'] != 'S'){  
                        $html .='<tr bgcolor="#ffffff">';
                        $html .='<td><input type="radio" name="docanex" value="'.$key.'"></td>';
                        $html .='<td colspan="4">'.$anexo['nome_arquivo'].'</td>';
                        $html .='<td colspan="4">'.$anexo["data_inclusao"].'</td>';
                        $html .="<input type='hidden' id='loadArqOff' value='true'>";
                        $html .='</tr>';
                    }
                }
                $html .='<tr bgcolor="#ffffff">';
                $html .='<td colspan="8"align="center">';
                 $html .='<button type="button" class="botao" id="btnIncluirAnexo" onclick="Subirarquivo()">Incluir Documento</button>';
                 $html .='<button type="button" class="botao" id="btnRemoveAnexo">Retirar Documento</button>';
                $html .='</td>';
                $html .='</tr>';
            print_r($html);
    break;
    case "RemoveDocAnex":
             $aux = $_POST['marcador'];
             $SessaoDocumento = '';
             if(!empty( $_SESSION['documento_anexo_incluir'])){
                 $SessaoDocumento = $_SESSION['documento_anexo_incluir'];
                 $SessaoDocumento[$aux]['remover'] = "S";
             }
             unset($_SESSION['documento_anexo_incluir']); //limpo a sessão para não conter nenhum lixo
             $html ='<tr class="FootFiscaisDoc">';
             $html .='<td></td>';
             $html .='<td colspan="4">ARQUIVO</td>';
             $html .='<td colspan="4">DATA DA INCLUSÃO</td>';
             $html .='</tr>';
             
             foreach($SessaoDocumento as $key => $anexo){
                     if($anexo['remover'] != "S"){
                             $html .='<tr bgcolor="#ffffff">';
                             $html .='<td><input type="radio" name="docanex" value="'.$key.'"></td>';
                             $html .='<td colspan="4">'.$anexo['nome_arquivo'].'</td>';
                             $html .='<td colspan="4">'.$anexo["data_inclusao"].'</td>';
                             $html .='</tr>';
                     }
             }
             $html .='<tr bgcolor="#ffffff">';
             $html .='<td colspan="8"align="center">';
             $html .='<button type="button" class="botao" id="btnIncluirAnexo" onclick="Subirarquivo()">Incluir Documento</button>';
             $html .='<button type="button" class="botao" id="btnRemoveAnexo">Retirar Documento</button>';
             $html .='</td>';
             $html .='</tr>';
             
             $_SESSION['documento_anexo_incluir'] = $SessaoDocumento;
             print_r($html);
    break;
    case "IncluirContrato":
        try {
            //Dados do fornecedor estão vindo pela session $_SESSION['dadosSalvar']
            $dadosSalvar = $_SESSION['dadosSalvar'];
            //Pegar os valores dos campos
            $html = ' ';
            $arrayTipoCompra = array(
            'COMPRA DIRETA'  => 1,
            'LICITAÇÃO'      => 2,
            'DISPENSA'       => 3,
            'INEXIGIBILIDADE'=> 4,
            'SARP'           => 5
            );
            //Validação dos campos obrigatórios
            // Dados de numero do contrato 
            if(!empty($_POST['numcontrato'])){
                $charRetira = array(".", "/"); //Usada para limpar campos recebidos
                $numContratoF = $dadosSalvar['ectrpcnumf'] = $_POST['numcontrato']; /**numero contrato formatado */
                // $existeContrato = $ObjContrato->VerificaSeExisteContrato($_POST['numcontrato']);
                if($existeContrato){
                    $response = array("status"=>false,"msm"=>"Número de contrato já existe.");
                    print(json_encode($response));
                    exit;
                }
                $dadosSalvar['actrpcnumc']   = str_replace($charRetira, "", $numContratoF); /**Retirada da formatação */
                $dadosSalvar['actrpcanoc']   = str_replace($charRetira, "",(strstr($numContratoF, "/"))); /**Retira o ano do numero de contrato*/
                $dadosSalvar['ectrpnuf2']    = strstr($numContratoF, "/", true); /**Retira o ano do numero de contrato*/
            }else{
                $dadosSalvar['actrpcnumc'] = 'null';
                $dadosSalvar['actrpcanoc'] = 'null';
                $dadosSalvar['ectrpnuf2'] = 'null';
                $dadosSalvar['ectrpcnumf']='Aguardando Numeração';
            }
            //Dados da scc
            if(!empty($dadosSalvar['numScc'])){
                $dadosSalvar['numeroscc']   = $dadosSalvar['numScc'];
                $dadosSalvar['asolcoanos'] = substr($dadosSalvar['numScc'], -4);   //valores a partir da barra
                $dadosSalvar['csolcocodi'] = substr($dadosSalvar['numScc'], -9 , -5);
                $dadosSalvar['ccenpocorg'] = substr($dadosSalvar['numScc'], 0, 2);
                $dadosSalvar['ccenpounid'] = substr($dadosSalvar['numScc'], -12, -10);
                $numeroSCC = $_SESSION['csolcosequ'];
                $fornecedorScc = $dadosSalvar['aforcrsequ'];
                $origimSCC   = $_SESSION['origemScc'];
                $existeContrato = $ObjContrato->VerificaSeExisteContratoComEssaSCC($numeroSCC,$fornecedorScc,$origimSCC,$dadosSalvar['citelpnuml']);
                if($existeContrato == true){
                    $response = array("status"=>false,"msm"=>" Já existe contrato para esse item.");
                    print(json_encode($response));
                    exit;
                }
            }else{
                $html .= 'Número da solicitação de compra, ';
            }
            if(empty($_POST['objetoDesc'])){
                $html .='Objeto, ';
            }else{
                $dadosSalvar['ectrpcobje'] = RetiraAcentos(strtoupper($_POST['objetoDesc']));
            }
            if($_POST["fieldConsorcio"] == "SIM"){
                $dadosSalvar['fctrpccons'] = $ObjContrato->corrigeString($_POST["fieldConsorcio"]);
                if(!empty($_SESSION['dadosFornecedor'])){
                    $dadosSalvar['fornecedor'] = $_SESSION['dadosFornecedor'];
                }else{
                    $html .='Fornecedor para o consórcio, ';
                }
            }elseif($_POST["fieldConsorcio"] == "NAO"){
                $dadosSalvar['fctrpccons'] = $ObjContrato->corrigeString($_POST["fieldConsorcio"]);
            }else{
                $html .='Consórcio / Matriz-Filial / Publicidade ?, ';
            }

            if(!empty($_POST['fieldContinuo']) or !is_null($_POST['fieldContinuo'])){
                $dadosSalvar['fctrpcserc'] = $ObjContrato->corrigeString($_POST['fieldContinuo']);
            }else{
                $html .='Contínuo, ';
            }
           
            if(!empty($_POST["obra"])){
                $dadosSalvar['fctrpcobra'] = $ObjContrato->corrigeString($_POST["obra"]);
                if(!empty($_POST["cmb_regimeExecucaoModoFornecimento1"])){
                    $dadosSalvar['ectrpcremf'] = $ObjContrato->corrigeString($_POST["cmb_regimeExecucaoModoFornecimento1"]);
                }else{
                    if($dadosSalvar['fctrpcobra'] == 'SIM'){
                        $html.='Regime de execução, ';
                    }else{
                        $html.='Modo de fornecimento, ';
                    }
                }
            }else{
                $html.='Obra, ';
            }
            
            if($_POST["opcaoExecucaoContrato"] != ""){
                $dadosSalvar['cctrpcopex'] = $_POST["opcaoExecucaoContrato"] == "D" ? "D" : "M";
                $execCalc =  $_POST["opcaoExecucaoContrato"] == "D" ?  "days" : "month";
                if(!empty($_POST["prazo"])){
                    $dadosSalvar['actrpcpzec']        = $_POST["prazo"];
                }else{ 
                    $html .= 'O prazo de execução do contrato, '; 
                }    
            }else{
                $html .= 'Opção de execução do contrato, ';
            }   
            //Numero e valor de parcelas
            $NumDeParcelas = $_POST["NumDeParcelas"];
            $ValorDaParcelas = $_POST["ValorDaParcelas"];
            
            if($NumDeParcelas!=NULL && $ValorDaParcelas==NULL){
                $html .= 'Valor das parcelas, ';
            }elseif($NumDeParcelas==NULL && $ValorDaParcelas!=NULL){
                $html .= 'Número de parcelas, ';
            }elseif($NumDeParcelas==NULL && $ValorDaParcelas==NULL){
                $dadosSalvar['adocpcnupa']='null';
                $dadosSalvar['adocpcvapa']='null';
                $html .= 'Número de parcelas, ';
                $html .= 'Valor das parcelas, ';
            }else{
                $dadosSalvar['adocpcnupa'] = $NumDeParcelas;
                $dadosSalvar['adocpcvapa'] = moeda2float($ValorDaParcelas);

            }

            //retirando traços e pontos de cpf
             $retiraPonto = array(".", "-");             
            //dados representantes       
            $nomeRep = $ObjContrato->corrigeString($_POST["repNome"]);     
            if(!empty($nomeRep)){
                $dadosSalvar['nctrpcnmrl'] = $nomeRep;
            }else{
                $html .= 'Nome do representante legal, ';
            }
            if(!empty($_POST["repCPF"])){
                $dadosSalvar['ectrpccpfr'] = str_replace($retiraPonto, "", $_POST["repCPF"]);
               if(!$ObjContrato->validaCPF($dadosSalvar['ectrpccpfr'])){
                $html .= ' CPF válido para o representante legal, ';
               } 
            }else{
                $html .= 'CPF do representante legal, ';
            }
            //dados gestor   
            $nomeGestor = $ObjContrato->corrigeString($_POST["gestorNome"]);                    
            if(!empty($nomeGestor)){
                $dadosSalvar['nctrpcnmgt'] = $nomeGestor;
            }else{
                $html .= 'Nome do gestor, ';
            }
            if(!empty($_POST["gestorCPF"])){
                $dadosSalvar['nctrpccpfg'] = str_replace($retiraPonto, "", $_POST["gestorCPF"]);
                if(!$ObjContrato->validaCPF($dadosSalvar['nctrpccpfg'])){
                    $html .= ' CPF válido para o gestor, ';
                   } 
            }else{
                $html .= 'CPF do gestor, ';
            }
            
            $emailGestor = $ObjContrato->anti_injection($_POST["gestorEmail"]);
            if(!empty($emailGestor)){
                $dadosSalvar['nctrpcmlgt'] = $emailGestor;
            }else{
                $html .= 'E-mail do gestor, ';
            }
            $matriculaGestor = $ObjContrato->corrigeString($_POST["gestorMatricula"]);
            if(!empty($matriculaGestor)){
                $dadosSalvar['nctrpcmtgt'] = $matriculaGestor;
            }else{
                $html .= 'Matrícula do gestor, ';
            }
            $telefoneGestor = $ObjContrato->corrigeString($_POST["gestorTelefone"]);
            if(!empty($telefoneGestor)){
                $dadosSalvar['ectrpctlgt'] = $telefoneGestor;
            }else{
                $html .= 'Telefone do gestor, ';
            }
            //Fiscal
            if(empty($_SESSION['fiscal_selecionado_incluir'])){
                $html .= "Fiscal do contrato, ";
            }
            //Documento Anexo
            if(empty($_SESSION['documento_anexo_incluir'])){
                $html .= "Documento anexo, ";
            }else if($_SESSION['documento_anexo_incluir'][0]['remover'] == "S" && empty($_SESSION['documento_anexo_incluir'][1])){
                $html .= "Documento anexo, ";
            }

            $htmlD = "";
            if($_POST["vigenciaDataInicio"] != "" && $_POST["vigenciaDataTermino"] != ""){
                $vigIni  = explode("/", $_POST["vigenciaDataInicio"]);
                $checaVigIni = checkdate($vigIni[1], $vigIni[0], $vigIni[2]);
                $vigIni = mktime(00,00,00, $vigIni[1], $vigIni[0], $vigIni[2]);
                
                $vigTerm  = explode("/", $_POST["vigenciaDataTermino"]);
                $checaVigTerm = checkdate($vigTerm[1], $vigTerm[0], $vigTerm[2]);
                $vigTerm = mktime(00,00,00, $vigTerm[1], $vigTerm[0], $vigTerm[2]);
                if($checaVigIni == true && $checaVigTerm == true){
                 
                }else{
                    $htmlD .= "A data informada nos campos de vigência não é válida, ";

                }
            }else{
               if($_POST["vigenciaDataInicio"] == ""){
                    $htmlD .= 'Data de inicio de vigência, ';
                }if($_POST["vigenciaDataTermino"] == ""){
                    $htmlD .= 'Data de término de vigência, ';
                }
              
            }
            if($_POST["execucaoDataInicio"] == ""){
                $htmlD .= 'Data de início de Execução, ';
            }
            if($_POST["execucaoDataTermino"] == ""){
                $htmlD .= 'Data de término de execução, ';
            }

            if(empty($_POST['opcaocategoriaprocesso'])){
                $htmlD .= 'Categoria do processo, ';
            }else{
                $dadosSalvar['cpnccpcodi'] = $ObjContrato->anti_injection($_POST['opcaocategoriaprocesso']);
            }
            
            if($_POST["execucaoDataInicio"] != "" && $_POST["execucaoDataTermino"] != ""){
                $exeIni  = explode("/", $_POST["execucaoDataInicio"]);
                $checaExeIni = checkdate($exeIni[1], $exeIni[0], $exeIni[2]);
                $exeIni = mktime(00,00,00, $exeIni[1], $exeIni[0], $exeIni[2]);
                $exeTerm  = explode("/", $_POST["execucaoDataTermino"]);
                $checaExeTerm = checkdate($exeTerm[1], $exeTerm[0], $exeTerm[2]);
                $exeTerm = mktime(00,00,00, $exeTerm[1], $exeTerm[0], $exeTerm[2]);
                if($checaExeIni == true && $checaExeTerm == true){
                    if($exeIni > $exeTerm){
                        $htmlD .= "A data final de execução não pode ser menor que a inicial, ";
                    
                    }elseif($exeTerm < $exeIni){
                        $htmlD .= "A data final de execução não pode ser menor que a inicial, ";
                    }
                    $dadosSalvar['dctrpcinvg']                = "'".date("Y-m-d", $vigIni)." 00:00:00'";
                    $dadosSalvar['dctrpcfivg']                = "'".date("Y-m-d", $vigTerm)." 00:00:00'";
                    $dadosSalvar['dctrpcinex']                = "'".date("Y-m-d", $exeIni)." 00:00:00'";
                    $dadosSalvar['dctrpcfiex']                = "'".date("Y-m-d", $exeTerm)." 00:00:00'";
                }else{
                    $htmlD .= "A data informada nos campos de Execução não é válida, ";
                }  
            }
            // fim da validação dos campos obrigatórios
            //-------------------------------------------------------------------------------------------------------------
            // validação de campos não obrigatórios
            // if(!empty($_POST["dataPublicacaoDom"])){
                // $pubDom = explode("/", $_POST["dataPublicacaoDom"]);
                // $pubDom = mktime(00,00,00, $pubDom[1], $pubDom[0], $pubDom[2]);
                // $pubDom =  date("d-m-Y", $pubDom);
                // $dadosSalvar['dctrpcpbdm'] = "'".date("Y-m-d", $pubDom)." 00:00:00'";
                // $dadosSalvar['dctrpcpbdm'] = $ObjContrato->date_transform($_POST["dataPublicacaoDom"]);
            // }else{ $dadosSalvar['dctrpcpbdm'] = 'null' ;}
            $dadosSalvar['dctrpcpbdm'] =  $ObjContrato->date_transform($_POST["dataPublicacaoDom"]);
            $dadosSalvar['ctipgasequ'] =  strtoupper($ObjContrato->corrigeString($_POST["comboGarantia"]) )  ;
            $dadosSalvar['nctrpccgrl'] =  strtoupper($ObjContrato->corrigeString($_POST["repCargo"]) );
            $dadosSalvar['ectrpcidrl'] = strtoupper($_POST["repRG"]);
            $dadosSalvar['nctrpcoerl'] =  strtoupper($ObjContrato->corrigeString($_POST["repRgOrgao"]));
            $dadosSalvar['nctrpcufrl'] =  strtoupper($ObjContrato->corrigeString($_POST["repRgUF"]));
            $dadosSalvar['nctrpccdrl'] =  strtoupper($ObjContrato->corrigeString($_POST["repCidade"])) ;
            $dadosSalvar['nctrpcedrl'] = strtoupper( $ObjContrato->corrigeString($_POST["repEstado"]) );
            $dadosSalvar['nctrpcnarl'] =  strtoupper($ObjContrato->corrigeString($_POST["repNacionalidade"]));
            $dadosSalvar['cctrpcecrl'] =  strtoupper($ObjContrato->corrigeString($_POST["repEstCiv"]));
            $dadosSalvar['nctrpcprrl'] = strtoupper( $ObjContrato->corrigeString($_POST["repProfissao"]));
            $dadosSalvar['nctrpcmlrl'] =  $ObjContrato->anti_injection($_POST["repEmail"]);
            // $dadosSalvar['ctpcomcodi'] = $arrayTipoCompra[$_POST["ctpcomcodi"]];
            $dadosSalvar['ectrpctlrl'] = strtoupper($_POST["repTelefone"]);
            $dadosSalvar['cusupocodi'] = strtoupper($_SESSION['_cusupocodi_']);
            //Trata as mensagens de erro, para caso não prcise do INFORME: e para caso não venha nenhuma.
            if($html == "INFORME: " && $htmlD == ""){
                $html = "";
            }elseif($html == "INFORME: " && $htmlD != ""){
                $htmlD = substr_replace($htmlD, '.', strrpos($htmlD, ", "));
                $html = $htmlD;
            }else{
                $html .= $htmlD;
                $html = substr_replace($html, '.', strrpos($html, ", "));
            }
            if($html != "" && strlen($html) >1){
                $response = array("status"=>false,"msm"=>"INFORME : ".$html);
                print(json_encode($response));
            }else{
                // ||Madson||
                // Nesta parte foram divididos os selects de inserts, a primeira busca é feita por aqui onde é inserido o documento que retorna 'cdocpcsequ'
                // A partir daí a função de inserts solicita da função de selects quando nescessário;

                 //    //Inserir contrato e documento
                if($dadosSalvar['origemScc'] == "LICITACAO"){
                    $dadosSalvar['identificador'] = 'itemDocumento';  // Busca dos valores utilizando func selectsContratoIncluir()
                    $valoresItem = $ObjContrato->selectsContratoIncluir($dadosSalvar);
                }else{
                    $dadosSalvar['identificador'] = 'valorItens';  // Busca dos valores utilizando func selectsContratoIncluir()
                    $valoresItem = $ObjContrato->selectsContratoIncluir($dadosSalvar);
                }
                    $totalItens = 0;
                    foreach($valoresItem as $item){
                        if($dadosSalvar['origemScc'] == "LICITACAO"){
                            $valorUnitário =$item->vitelpvlog;
                            $quantItem     =$item->aitelpqtso;
                        }else{
                            $valorUnitário =$item->vitescunit;
                            $quantItem     =$item->aitescqtso;
                        }   
                        $valorCalculado = (floatval($valorUnitário) * floatval($quantItem));
                        $totalItens += $valorCalculado;
                }
                $dadosSalvar['vctrpcvlor'] = $totalItens;
                if(empty($dadosSalvar['cdocpcsequ'])){
                    $dadosSalvar['identificador'] = 'Documento'; //Sinalizador para Switch da função
                    $dadosSalvar['cdocpcsequ'] = $ObjContrato->selectsContratoIncluir($dadosSalvar);
                    if(!empty($dadosSalvar['cdocpcsequ'])){
                        $itemContrato = $ObjContrato->insertsContratoIncluir($dadosSalvar);
                        if($itemContrato != true && ($itemContrato == 1 || $itemContrato ==2 || $itemContrato == 3 || $itemContrato == 4)){
                            $ObjContrato->deletsContratoIncluir($itemContrato, $dadosSalvar['cdocpcsequ']);
                            $response = array("status"=>false,"msm"=>"ATENÇÃO: Erro ao inserir contrato, por favor, contate o suporte!");
                            print(json_encode($response));
                            exit;
                        }
                    }else{
                            $response = array("status"=>false,"msm"=>"ATENÇÃO: Erro ao inserir contrato, por favor, contate o suporte!");
                            print(json_encode($response));
                            exit;
                    }
                }else{
                            $response = array("status"=>false,"msm"=>"ATENÇÃO: Erro ao inserir contrato, por favor, contate o suporte! - dados cdocpsequ vazio");
                            print(json_encode($response));
                            exit;
                }
                if($itemContrato != true){
                    $response = array("status"=>false,"msm"=>"ATENÇÃO: Erro ao inserir contrato, por favor, contate o suporte!");
                    print(json_encode($response));
                    exit;
                }else{
                    // inserir fiscal-----------------------------------------------------------------------
                    if(!empty($_SESSION['fiscal_selecionado_incluir'])){
                        foreach($_SESSION['fiscal_selecionado_incluir'] as $fiscal){
                            // kim começar daqui
                            if($fiscal->remover != "S"){
                                $dadosFiscal = array( 
                                                    'cfiscdcpff'=>str_replace($arrayTirar,'',$fiscal->fiscalcpf),
                                                    'cdocpcsequ'=>$dadosSalvar['cdocpcsequ'],
                                                    'cusupocodi'=>$ObjContrato->anti_injection($_SESSION['_cusupocodi_']),
                                                    'tdocfiulat'=>date('Y-m-d H:i:s.u')
                                                    );
                                $retornoFS = $ObjContrato->InsertDocumentoFiscal($dadosFiscal);
                            }
                        }
                        if(empty($retornoFS)){
                            $response = array("status"=>false,"msm"=>"Informe: Fiscal do contrato");
                                        print(json_encode($response));
                                        exit;
                        }
                        unset($_SESSION['fiscal_selecionado_incluir']);
                    }
                    //-------------------------------------------------------------------------------------
                    // inserir fornecedor contrato
                    //$dadosSalvar['fornecedor'] $dadosSalvar['cdocpcsequ']
                    if(!empty($_SESSION['dadosFornecedor'])){  
                        foreach($_SESSION['dadosFornecedor'] as $FornecedorSession){
                            $seExisteFornecedorContratoCadastrado = $ObjContrato->VerificarSeExisteFornecedorContrato($dadosSalvar['cdocpcsequ'],$FornecedorSession->aforcrsequ);
                            if($FornecedorSession->remover == 'N'){
                                if(empty($seExisteFornecedorContratoCadastrado->aforcrsequ)){
                                    $dadosFornecedorContrato = array(
                                                                    'cdocpcsequ'    =>$dadosSalvar['cdocpcsequ'],
                                                                    'aforcrsequ'   =>$FornecedorSession->aforcrsequ,
                                                                    'cusupocodi'   => $ObjContrato->anti_injection($_SESSION['_cusupocodi_'])
                                                                    );
                                    
                                    $retornoFC = $ObjContrato->InsertFornecedorContrato($dadosFornecedorContrato);
                                    if(empty($retornoFC)){
                                        $response = array("status"=>false,"msm"=>"Error não foi possivel editar o contrato, tente novamente");
                                        print(json_encode($response));
                                        exit;
                                    }
                                }else{
                                    $response = array("status"=>false,"msm"=>"Error não foi possivel editar o contrato, tente novamente");
                                    print(json_encode($response));
                                    exit;
                                }
                            }else if($FornecedorSession->remover == 'S'){
                                $retornoFCDelete = $ObjContrato->DeleteFornecedorContrato($dadosSalvar['cdocpcsequ'],$FornecedorSession->aforcrsequ);
                            }
                        }
                        unset($_SESSION['dadosFornecedor']);
                    }

                    // inserir anexo ----------------------------------------------------------------------
                    if(!empty($_SESSION['documento_anexo_incluir'])){
                        $i=0;
                        foreach($_SESSION['documento_anexo_incluir'] as $docAnex){
                            if($docAnex)
                                if($docAnex['remover'] != "S"){
                                    $dadosDocAnex = array(
                                                        'cdocpcsequ'     => $dadosSalvar['cdocpcsequ'],
                                                        'edcanxnome'     => $docAnex['nome_arquivo'],
                                                        'idcanxarqu'     => $docAnex['arquivo'],
                                                        'cdcanxsequ'     => $docAnex['sequarquivo']+$i,
                                                        'tdcanxcada'     => $docAnex['data_inclusao'],
                                                        'cusupocodi'     => $docAnex['usermod'],
                                                        'ativo'             => 'S'
                                                        );
                                    $retorno = $ObjContrato->InsertDocumentosAnexos($dadosDocAnex);
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
                }
                    $response = array("status"=>true,"msm"=>"Contrato salvo com sucesso!");
                    print(json_encode($response));
            }
            

            
        } catch (Exception $ex) {
            print($ex);
        } 
    break;
    case "IncluirContratoAntigo":
        //Dados do fornecedor estão vindo pela session $_SESSION['dadosSalvar']
        $fiscal = $_SESSION['fiscal_selecionado_incluir'];
        $documentos = $_SESSION['documento_anexo_incluir'];
        $fornecedor = $_SESSION['dados_fornecedor_incluir'];

      //  var_dump($_SESSION['dados_fornecedor_incluir']);

        $codMat = explode('|', $_POST['codigo_mat']);
        $valorMat = explode('|', $_POST['valor_mat']);
        $qtdMat = explode('|', $_POST['qtd_mat']);

        $codServ = explode('|', $_POST['codigo_servico']);
        $valorServ = explode('|', $_POST['valor_estimado_servico']);
        $qtdServ = explode('|', $_POST['qtd_servico']);
       // $descServ = explode('|', $_POST['descricao_detalhada_servico']);

        $codigos = count($codMat);
        if($codigos > 0){
            for ($i = 0; $i < $codigos; $i++) {
                $item_material[$i]['mat_cod'] =  $codMat[$i];
                $item_material[$i]['mat_qtd'] =  $qtdMat[$i];
                $item_material[$i]['mat_valor'] =  $valorMat[$i];
            }

            $dadosSalvar['itens_material'] = $item_material;
        }

        $cod_servs = count($codServ);
        if($cod_servs > 0){
            for ($i = 0; $i < $cod_servs; $i++) {
                $item_servico[$i]['serv_cod'] =  $codServ[$i];
                $item_servico[$i]['valor_qtd'] =  $valorServ[$i];
                $item_servico[$i]['qtd_valor'] =  $qtdServ[$i];
              //  $item_servico[$i]['desc_valor'] =  $descServ[$i];
            }

            $dadosSalvar['itens_servico'] = $item_servico;
        }

       // var_dump( $dadosSalvar['itens_material']);
       // var_dump( $dadosSalvar['itens_servico']);die;

        $materiais = $_SESSION['MATERIAIS'];
        $servicos = $_SESSION['SERVICOS'];

        $dadosSalvar['materiais'] = $materiais;
        $dadosSalvar['servicos'] = $servicos;


        $numero_contrato = $_POST['numcontrato'];

        $origem = $_POST['origem'];
        $orgLicitante = $_POST['orgao_licitante']; //Usar para mostrar na tela qual deles é e para a Masc
        $objetoDesc   = $_POST['objeto'];

        $opcao_cpf = $_POST['CNPJ_CPF'];
        $opcao_informada = $_POST['CnpjCpf'];
        //var_dump($_POST['CnpjCpf']);

        $valor_cpf_cnpj = trim($opcao_informada);
        $valor_cpf_cnpj = str_replace(".", "", $valor_cpf_cnpj);
        $valor_cpf_cnpj = str_replace(",", "", $valor_cpf_cnpj);
        $valor_cpf_cnpj = str_replace("-", "", $valor_cpf_cnpj);
        $valor_cpf_cnpj = str_replace("/", "", $valor_cpf_cnpj);

        if($opcao_cpf == 1){
            
            $cod_fornecedor = $ObjContrato->GetFornecedor('', $valor_cpf_cnpj);
           // var_dump($cod_fornecedor->aforcrsequ);
        }else{
          //  die('2');
            $cod_fornecedor = $ObjContrato->GetFornecedor($valor_cpf_cnpj, '');
        }
        
        $opcao_consorcio = $_POST['fieldConsorcio'];
        $opcao_continuo = $_POST['fieldContinuo'];
        $obra = $_POST['obra'];
        $regimeExecucaoModoFornecimento = $_POST['cmb_regimeExecucaoModoFornecimento1'];
        $opcaoExecucaoContrato = $_POST['opcaoExecucaoContrato'];
        $prazo = $_POST['prazo'];
        $dataPublicacaoDom = $_POST['dataPublicacaoDom'];
        $vigenciaDataInicio = $_POST['vigenciaDataInicio'];
        $vigenciaDataTermino = $_POST['vigenciaDataTermino'];
        $execucaoDataInicio = $_POST['execucaoDataInicio'];
        $execucaoDataTermino = $_POST['execucaoDataTermino'];

        $valor_original = $ObjContrato->floatvalue($_POST['valor_original']);
        $valor_global = $ObjContrato->floatvalue($_POST['valor_global']);
        
        $valor_executado_acumulado = $ObjContrato->floatvalue($_POST['valor_executado_acumulado']);
        
        $saldo_executar = $_POST['saldo_executar'];

        $numero_ultimo_aditivo = $_POST['numero_ultimo_aditivo'];
        $numero_ultimo_apostilamento = $_POST['numero_ultimo_apostilamento'];
        $comboGarantia = $_POST['comboGarantia'];
        
        // Dados Representante
        $repCPF = $_POST['repCPF'];
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

        // Dados Gestor
        $gestorNome = $_POST['gestorNome'];
        $gestorMatricula = $_POST['gestorMatricula'];
        $gestorCPF = $_POST['gestorCPF'];
        $gestorEmail = $_POST['gestorEmail'];
        $gestorTelefone = $_POST['gestorTelefone'];

           //Pegar os valores dos campos
           $html = ' ';
           //Validação dos campos obrigatórios
           // Dados de numero do contrato 
           if(!empty($_POST['numcontrato'])){
               $charRetira = array(".", "/"); //Usada para limpar campos recebidos
               $numContratoF = $dadosSalvar['ectrpcnumf'] = $_POST['numcontrato']; /**numero contrato formatado */
               $existeContrato = $ObjContrato->VerificaSeExisteContrato($_POST['numcontrato']);
               if($existeContrato){
                   $response = array("status"=>false,"msm"=>"Número de contrato já existe.");
                   print(json_encode($response));
                   exit;
               }
               $dadosSalvar['actrpcnumc']   = str_replace($charRetira, "", $numContratoF); /**Retirada da formatação */
               $dadosSalvar['actrpcanoc']   = str_replace($charRetira, "",(strstr($numContratoF, "/"))); /**Retira o ano do numero de contrato*/
               $dadosSalvar['ectrpnuf2']    = strstr($numContratoF, "/", true); /**Retira o ano do numero de contrato*/
           }else{
               $dadosSalvar['actrpcnumc'] = 'null';
               $dadosSalvar['ectrpnuf2'] = 'null';
               $dadosSalvar['actrpcanoc'] = 'null';
               $dadosSalvar['ectrpcnumf']='Aguardando Numeração';
           }


        // Dados do formulário representados por seus nomes na base
       // var_dump($dadosSalvar['aforcrsequ']);
        $dadosSalvar['aforcrsequ'] = $ObjContrato->corrigeString($cod_fornecedor->aforcrsequ);
        $dadosSalvar['ctpcomcodi'] = $ObjContrato->corrigeString($origem);
        $dadosSalvar['cctrpciden'] = $ObjContrato->corrigeString($opcao_cpf);
        $dadosSalvar['ectrpccpfr'] = $ObjContrato->corrigeString($opcao_informada);
        $dadosSalvar['fctrpcobra'] = $ObjContrato->corrigeString($obra);
        $dadosSalvar['fctrpccons'] = $ObjContrato->corrigeString($opcao_consorcio);
        $dadosSalvar['fctrpcserc'] = $ObjContrato->corrigeString($opcao_continuo);
        
        $dadosSalvar['fctrpcremf'] = $ObjContrato->corrigeString($regimeExecucaoModoFornecimento);
        $dadosSalvar['cctrpcopex'] = $ObjContrato->corrigeString($opcaoExecucaoContrato);
        $dadosSalvar['actrpcpzec'] = $ObjContrato->corrigeString($prazo);
        $dadosSalvar['dctrpcpbdm'] = $ObjContrato->corrigeString($dataPublicacaoDom);
        $dadosSalvar['dctrpcinvg'] = $ObjContrato->corrigeString($vigenciaDataInicio);
        $dadosSalvar['dctrpcfivg'] = $ObjContrato->corrigeString($vigenciaDataTermino);
        $dadosSalvar['dctrpcinex'] = $ObjContrato->corrigeString($execucaoDataInicio);
        $dadosSalvar['dctrpcfiex'] = $ObjContrato->corrigeString($execucaoDataTermino);
        
        $dadosSalvar['vctrpcvlor'] = $ObjContrato->anti_injection($valor_original);
        $dadosSalvar['vctrpcsean'] = $ObjContrato->anti_injection($valor_global);
        $dadosSalvar['vctrpceant'] = $ObjContrato->anti_injection($valor_executado_acumulado);
        $dadosSalvar['actrpcnuad'] = $ObjContrato->corrigeString($numero_ultimo_aditivo);
        $dadosSalvar['actrpcnuap'] = $ObjContrato->corrigeString($numero_ultimo_apostilamento);
        $dadosSalvar['ctipgasequ'] = $ObjContrato->corrigeString($comboGarantia);

        $dadosSalvar['nctrpcnmgt'] = $ObjContrato->corrigeString($gestorNome);
        $dadosSalvar['nctrpcmtgt'] = $ObjContrato->corrigeString($gestorMatricula);
        $dadosSalvar['nctrpccpfg'] = $ObjContrato->corrigeString($gestorCPF);
        $dadosSalvar['nctrpcmlgt'] = $ObjContrato->anti_injection($gestorEmail);
        $dadosSalvar['ectrpctlgt'] = $ObjContrato->corrigeString($gestorTelefone);

        $dadosSalvar['ectrpccpfr'] = $ObjContrato->corrigeString($repCPF);
        $dadosSalvar['nctrpccgrl'] = $ObjContrato->corrigeString($repCargo);
        $dadosSalvar['ectrpcidrl'] = $ObjContrato->corrigeString($repRG);
        $dadosSalvar['nctrpcoerl'] = $ObjContrato->corrigeString($repRgOrgao);
        $dadosSalvar['nctrpcufrl'] = $ObjContrato->corrigeString($repRgUF);
        $dadosSalvar['nctrpccdrl'] = $ObjContrato->corrigeString($repCidade);
        $dadosSalvar['nctrpcedrl'] = $ObjContrato->corrigeString($repEstado);
        $dadosSalvar['nctrpcnarl'] = $ObjContrato->corrigeString($repNacionalidade);
        $dadosSalvar['cctrpcecrl'] = $ObjContrato->corrigeString($repEstCiv);
        $dadosSalvar['nctrpcprrl'] = $ObjContrato->corrigeString($repProfissao);
        $dadosSalvar['nctrpcmlrl'] = $ObjContrato->anti_injection($repEmail);
        $dadosSalvar['ectrpctlrl'] = $ObjContrato->corrigeString($repTelefone);
        
        $dadosSalvar['corglicodi'] = $orgLicitante;
        $dadosSalvar['ectrpcobje'] = $ObjContrato->corrigeString($objetoDesc);

        $dadosSalvar['ectrpcraza'] = $_SESSION['dadosSalvar']['ectrpcraza'];                        
        $dadosSalvar['ectrpclogr'] = $_SESSION['dadosSalvar']['ectrpclogr'];
        $dadosSalvar['actrpcnuen'] = $_SESSION['dadosSalvar']['actrpcnuen'];
        $dadosSalvar['ectrpccomp'] = $_SESSION['dadosSalvar']['ectrpccomp'];
        $dadosSalvar['ectrpcbair'] = $_SESSION['dadosSalvar']['ectrpcbair'];
        $dadosSalvar['cctrpcesta'] = $_SESSION['dadosSalvar']['cctrpcesta'];
        $dadosSalvar['nctrpccida'] = $_SESSION['dadosSalvar']['nctrpccida'];

        
            if(empty($_POST['numcontrato'])){
                $html .='Número do Contrato, ';
            }else{
                $numContratoF = $_POST['numcontrato'];
            }
          
            if(empty($_POST['CnpjCpf'])){
                $html .='Contratado, ';
            }else{
                $dadosSalvar['ectrpccpfr'] = $_POST['CnpjCpf'];
            }
      
            if(empty($_POST['origem'])){
                $html .='Origem, ';
            }else{
                $dadosSalvar['ctpcomcodi'] = $_POST['origem'];
            }

            if(empty($_POST['orgao_licitante'])){
                $html .='Orgão Contratante, ';
            }else{
                $dadosSalvar['corglicodi'] = $_POST['orgao_licitante'];
            }

            if(empty($objetoDesc)){
                $html .='Objeto, ';
            }else{
                $dadosSalvar['ectrpcobje'] = RetiraAcentos(strtoupper($objetoDesc));
            }
    
            if(empty($_POST['valor_original'])){
                $html .='Valor Original, ';
            }else{
                $dadosSalvar['vctrpcvlor'] = $_POST['valor_original'];
            }

            if(empty($_POST['valor_global'])){
                $html .='Valor Global com Aditivos/Apostilamentos, ';
            }else{
                $dadosSalvar['vctrpcsean'] = $_POST['valor_global'];
            }

            if(empty($_POST['valor_executado_acumulado'])){
                $html .='Valor Executado Acumulado, ';
            }else{
                $dadosSalvar['vctrpceant'] = $_POST['valor_executado_acumulado'];
            }

            if(empty($materiais) && empty($servicos)){
                $html .='Item(ns), ';
            }
    
            if(count($codMat) == 0 && count($codServ) == 0){
                $html .= "Informações adicionais do(s) Item(ns), ";
            }


            if(empty($_POST['opcaocategoriaprocesso'])){
                $html .= 'Categoria do processo, ';
            }else{
                $dadosSalvar['cpnccpcodi'] = $ObjContrato->anti_injection($_POST['opcaocategoriaprocesso']);
            }


            if($_POST["fieldConsorcio"] == "SIM"){
                $dadosSalvar['fctrpccons'] = $ObjContrato->corrigeString($_POST["fieldConsorcio"]);
                if(!empty($_SESSION['dadosFornecedor'])){
                    $dadosSalvar['fornecedor'] = $_SESSION['dadosFornecedor'];
                }else{
                    $html .='Fornecedor para o consórcio, ';
                }
            }elseif($_POST["fieldConsorcio"] == "NAO"){
                $dadosSalvar['fctrpccons'] = $ObjContrato->corrigeString($_POST["fieldConsorcio"]);
            }else{
                $html .='Consórcio / Matriz-Filial / Publicidade ?, ';
            }

            if(!empty($_POST['fieldContinuo']) or !is_null($_POST['fieldContinuo'])){
                $dadosSalvar['fctrpcserc'] = $ObjContrato->corrigeString($_POST['fieldContinuo']);
            }else{
                $html .='Contínuo, ';
            }
           
            if(!empty($_POST["cmb_regimeExecucaoModoFornecimento1"])){
                $dadosSalvar['ectrpcremf'] = $ObjContrato->corrigeString($_POST["cmb_regimeExecucaoModoFornecimento1"]);
            }else{
                $html.='modo de fornecimento, ';
            }
            
            if($_POST["opcaoExecucaoContrato"] != ""){
                $dadosSalvar['cctrpcopex'] = $_POST["opcaoExecucaoContrato"] == "D" ? "D" : "M";
                $execCalc =  $_POST["opcaoExecucaoContrato"] == "D" ?  "days" : "month";
                if(!empty($_POST["prazo"])){
                    $dadosSalvar['actrpcpzec']        = $_POST["prazo"];
                }else{ 
                    $html .= 'O prazo de execução do contrato, '; 
                }    
            }else{
                $html .= 'Opção de execução do contrato, ';
            }
            //Numero e valor de parcelas
            $NumDeParcelas = $_POST['NumDeParcelas'];
            $ValorDaParcelas = $_POST['ValorDaParcelas'];
            
            if($NumDeParcelas!=NULL && $ValorDaParcelas==NULL){
                $html .= 'Valor das parcelas, ';
            }elseif($NumDeParcelas==NULL && $ValorDaParcelas!=NULL){
                $html .= 'Número de parcelas, ';
            }elseif($NumDeParcelas==NULL && $ValorDaParcelas==NULL){
                $dadosSalvar['adocpcnupa']='null';
                $dadosSalvar['adocpcvapa']='null';
                $html .= 'Número de parcelas, ';
                $html .= 'Valor das parcelas, ';
            }else{
                $dadosSalvar['adocpcnupa'] = $NumDeParcelas;
                $dadosSalvar['adocpcvapa'] = moeda2float($ValorDaParcelas);

            }
            
            //retirando traços e pontos de cpf
             $retiraPonto = array(".", "-");             
            //dados representantes       
            $nomeRep = $ObjContrato->corrigeString($_POST["repNome"]);     
            if(!empty($nomeRep)){
                $dadosSalvar['nctrpcnmrl'] = $nomeRep;
            }else{
                $html .= 'Nome do representante legal, ';
            }
            if(!empty($_POST["repCPF"])){
                $dadosSalvar['ectrpccpfr'] = str_replace($retiraPonto, "", $_POST["repCPF"]);
               if(!$ObjContrato->validaCPF($dadosSalvar['ectrpccpfr'])){
                $html .= ' CPF válido para o representante legal, ';
               } 
            }else{
                $html .= 'CPF do representante legal, ';
            }
            //dados gestor   
            $nomeGestor = $ObjContrato->corrigeString($_POST["gestorNome"]);                    
            if(!empty($nomeGestor)){
                $dadosSalvar['nctrpcnmgt'] = $nomeGestor;
            }else{
                $html .= 'Nome do gestor, ';
            }
            if(!empty($_POST["gestorCPF"])){
                $dadosSalvar['nctrpccpfg'] = str_replace($retiraPonto, "", $_POST["gestorCPF"]);
                if(!$ObjContrato->validaCPF($dadosSalvar['nctrpccpfg'])){
                    $html .= ' CPF válido para o gestor, ';
                   } 
            }else{
                $html .= 'CPF do gestor, ';
            }

            
            if(empty($_POST["execucaoDataInicio"])){
                $html .= 'Data inicial de execução, ';
            }

            if(empty($_POST["execucaoDataTermino"])){
                $html .= 'Data de término de execução, ';
            }
            /*
                if(empty($_POST["vigenciaDataInicio"])){
                    $html .= 'Data inicial de vigência, ';
                }

                if(empty($_POST["vigenciaDataTermino"])){
                    $html .= 'Data término de vigência, ';
                }
            */
            
            $emailGestor = $ObjContrato->anti_injection($_POST["gestorEmail"]);
            if(!empty($emailGestor)){
                $dadosSalvar['nctrpcmlgt'] = $emailGestor;
            }else{
                $html .= 'E-mail do gestor, ';
            }
            $matriculaGestor = $ObjContrato->corrigeString($_POST["gestorMatricula"]);
            if(!empty($matriculaGestor)){
                $dadosSalvar['nctrpcmtgt'] = $matriculaGestor;
            }else{
                $html .= 'Matrícula do gestor, ';
            }
            $telefoneGestor = $ObjContrato->corrigeString($_POST["gestorTelefone"]);
            if(!empty($telefoneGestor)){
                $dadosSalvar['ectrpctlgt'] = $telefoneGestor;
            }else{
                $html .= 'Telefone do gestor, ';
            }
            //Fiscal
            if(empty($_SESSION['fiscal_selecionado_incluir'])){
                $html .= "Fiscal do contrato, ";
            }
            //Documento Anexo
            if(empty($_SESSION['documento_anexo_incluir'])){
                $html .= "Documento anexo, ";
            }else if($_SESSION['documento_anexo_incluir'][0]['remover'] == "S" && empty($_SESSION['documento_anexo_incluir'][1])){
                $html .= "Documento anexo, ";
            }

            $htmlD = "";
          
            if($_POST["vigenciaDataInicio"] != "" && $_POST["vigenciaDataTermino"] != ""){
                $vigIni  = explode("/", $_POST["vigenciaDataInicio"]);
                $checaVigIni = checkdate($vigIni[1], $vigIni[0], $vigIni[2]);
                $vigIni = mktime(00,00,00, $vigIni[1], $vigIni[0], $vigIni[2]);
                
                $vigTerm  = explode("/", $_POST["vigenciaDataTermino"]);
                $checaVigTerm = checkdate($vigTerm[1], $vigTerm[0], $vigTerm[2]);
                $vigTerm = mktime(00,00,00, $vigTerm[1], $vigTerm[0], $vigTerm[2]);
                if($checaVigIni == true && $checaVigTerm == true){
                    if($_POST["execucaoDataInicio"] != "" && $_POST["execucaoDataTermino"] != ""){
                       if($vigTerm < $vigIni){
                            $htmlD .= 'A data final de vigência não pode ser menor que a inicial, ';
                            if($_POST["execucaoDataInicio"] == "" && $_POST["execucaoDataTermino"] == ""){
                                $htmlD .= 'Período de execução, ';
                            }
                        }
                    }elseif(empty($_POST["execucaoDataTermino"])){
                        $htmlD .= 'Data de término de execução';
                    }elseif(empty($_POST["execucaoDataInicio"])){
                        $htmlD .= 'Data início de execução ';
                    }else{
                        $htmlD .= 'Período de execução, ';
                    }
                }else{
                    $htmlD .= "A data informada nos campos de vigência não é válida, ";

                    if(empty($_POST["execucaoDataInicio"]) && empty($_POST["execucaoDataTermino"])){
                        $htmlD .= 'Período de execução, ';
                    }elseif(empty($_POST["execucaoDataTermino"])){
                        $htmlD .= 'Data de término de execução';
                    }elseif(empty($_POST["execucaoDataInicio"])){
                        $htmlD .= 'Data início de execução ';
                    }
                }
            }else{
                if(empty($_POST["vigenciaDataInicio"])){
                    $htmlD .= 'Data inicial de vigência, ';
                }if(empty($_POST["vigenciaDataTermino"])){
                    $htmlD .= 'Data término de vigência, ';    
                }else{
                    $htmlD .= 'Período de vigência, ';
                }
            }

            if($_POST["execucaoDataInicio"] != "" && $_POST["execucaoDataTermino"] != ""){
                $exeIni  = explode("/", $_POST["execucaoDataInicio"]);
                $checaExeIni = checkdate($exeIni[1], $exeIni[0], $exeIni[2]);
                $exeIni = mktime(00,00,00, $exeIni[1], $exeIni[0], $exeIni[2]);
                $exeTerm  = explode("/", $_POST["execucaoDataTermino"]);
                $checaExeTerm = checkdate($exeTerm[1], $exeTerm[0], $exeTerm[2]);
                $exeTerm = mktime(00,00,00, $exeTerm[1], $exeTerm[0], $exeTerm[2]);
                if($checaExeIni == true && $checaExeTerm == true){
                    if($exeIni > $exeTerm){
                        $htmlD .= "A data final de execução não pode ser menor que a inicial, ";
                    
                    }elseif($exeTerm < $exeIni){
                        $htmlD .= "A data final de execução não pode ser menor que a inicial, ";
                    }
                    $dadosSalvar['dctrpcinvg']                = "'".date("Y-m-d", $vigIni)." 00:00:00'";
                    $dadosSalvar['dctrpcfivg']                = "'".date("Y-m-d", $vigTerm)." 00:00:00'";
                    $dadosSalvar['dctrpcinex']                = "'".date("Y-m-d", $exeIni)." 00:00:00'";
                    $dadosSalvar['dctrpcfiex']                = "'".date("Y-m-d", $exeTerm)." 00:00:00'";
                }else{
                    $htmlD .= "A data informada nos campos de Execução não é válida, ";
                }  
            }

            // fim da validação dos campos obrigatórios
            //-------------------------------------------------------------------------------------------------------------
            // validação de campos não obrigatórios
            if(!empty($_POST["dataPublicacaoDom"])){  
                $dadosSalvar['dctrpcpbdm'] = $ObjContrato->date_transform($_POST["dataPublicacaoDom"]);
            }else{ 
                $dadosSalvar['dctrpcpbdm'] = null;
            }
            //var_dump($dadosSalvar['dctrpcpbdm']);
            $dadosSalvar['ctipgasequ'] =  strtoupper($ObjContrato->corrigeString($_POST["comboGarantia"]) )  ;
            $dadosSalvar['nctrpccgrl'] =  strtoupper($ObjContrato->corrigeString($_POST["repCargo"]) );
            $dadosSalvar['ectrpcidrl'] = strtoupper($_POST["repRG"]);
            $dadosSalvar['nctrpcoerl'] =  strtoupper($ObjContrato->corrigeString($_POST["repRgOrgao"]));
            $dadosSalvar['nctrpcufrl'] =  strtoupper($ObjContrato->corrigeString($_POST["repRgUF"]));
            $dadosSalvar['nctrpccdrl'] =  strtoupper($ObjContrato->corrigeString($_POST["repCidade"])) ;
            $dadosSalvar['nctrpcedrl'] = strtoupper( $ObjContrato->corrigeString($_POST["repEstado"]) );
            $dadosSalvar['nctrpcnarl'] =  strtoupper($ObjContrato->corrigeString($_POST["repNacionalidade"]));
            $dadosSalvar['cctrpcecrl'] =  strtoupper($ObjContrato->corrigeString($_POST["repEstCiv"]));
            $dadosSalvar['nctrpcprrl'] = strtoupper( $ObjContrato->corrigeString($_POST["repProfissao"]));
            $dadosSalvar['nctrpcmlrl'] =  $ObjContrato->anti_injection($_POST["repEmail"]);
            $dadosSalvar['ectrpctlrl'] = strtoupper($_POST["repTelefone"]);
            $dadosSalvar['cusupocodi'] = strtoupper($_SESSION['_cusupocodi_']);

            //Trata as mensagens de erro, para caso não prcise do INFORME: e para caso não venha nenhuma.
            if($html == "INFORME: " && $htmlD == ""){
                $html = "";
            }elseif($html == "INFORME: " && $htmlD != ""){
                $htmlD = substr_replace($htmlD, '.', strrpos($htmlD, ", "));
                $html = $htmlD;
            }else{
                $html .= $htmlD;
                $html = substr_replace($html, '.', strrpos($html, ", "));
            }
            if($html != "" && strlen($html) >1){
                $response = array("status"=>false,"msm"=>"INFORME: ".$html);
                print(json_encode($response));
            }else{
                
                $dadosSalvar['identificador'] = 'valorItens';  // Busca dos valores utilizando func selectsContratoIncluir()
                //$valoresItem = $ObjContrato->selectsContratoIncluir($dadosSalvar);
                $totalItens = 0;
                /*
               foreach($valoresItem as $item){
                        if($dadosSalvar['origemScc'] == "LICITACAO"){
                            $valorUnitário =$item->vitelpvlog;
                            $quantItem     =$item->aitelpqtso;
                        }else{
                            $valorUnitário =$item->vitescunit;
                            $quantItem     =$item->aitescqtso;
                        }   
                        //$valorCalculado = (floatval($valorUnitário) * floatval($quantItem));
                       // $totalItens += $valorCalculado;
                }
                */
               // $dadosSalvar['vctrpcvlor'] = $totalItens;
                
                if(empty($dadosSalvar['cdocpcsequ'])){
                    $dadosSalvar['identificador'] = 'Documento'; //Sinalizador para Switch da função
                    $dadosSalvar['cdocpcsequ'] = $ObjContrato->selectsContratoIncluir($dadosSalvar);
                    
                    if(!empty($dadosSalvar['cdocpcsequ'])){
                        $itemContrato = $ObjContrato->insertsContratoAntigoIncluir($dadosSalvar);
                        
                        if($itemContrato != true && ($itemContrato == 1 || $itemContrato ==2 || $itemContrato == 3 || $itemContrato == 4)){
                            //deletsContratoIncluir($itemContrato, $dadosSalvar['cdocpcsequ']);
                            $response = array("status"=>false,"msm"=>"ATENÇÃO: Erro ao inserir contrato, por favor, contate o suporte!");
                            print(json_encode($response));
                            exit;
                        }
                    }
                }

                if($itemContrato != true){
                    $response = array("status"=>false,"msm"=>"ATENÇÃO: Erro ao inserir contrato, por favor, contate o suporte!");
                    print(json_encode($response));
                    exit;
                }else{
                    // inserir fiscal-----------------------------------------------------------------------
                    if(!empty($_SESSION['fiscal_selecionado_incluir'])){
                        $okFiscal = array();
                        foreach($_SESSION['fiscal_selecionado_incluir'] as $fiscal){
                            // kim começar daqui
                            if(empty($existe) && $fiscal->remover != "S"){
                                $dadosFiscal = array( 
                                                    'cfiscdcpff'=>str_replace($arrayTirar,'',$fiscal->fiscalcpf),
                                                    'cdocpcsequ'=>$dadosSalvar['cdocpcsequ'],
                                                    'cusupocodi'=>$ObjContrato->anti_injection($_SESSION['_cusupocodi_']),
                                                    'tdocfiulat'=>date('Y-m-d H:i:s.u')
                                                    );
                                $retornoFS = $ObjContrato->InsertDocumentoFiscal($dadosFiscal);
                            }
                        }
                        if(empty($retornoFS)){
                            $response = array("status"=>false,"msm"=>"Imforme: Fiscal do contrato");
                                        print(json_encode($response));
                                        exit;
                        }
                        unset($_SESSION['fiscal_selecionado_incluir']);
                    }
                    //-------------------------------------------------------------------------------------

                    // inserir anexo ----------------------------------------------------------------------
                    if(!empty($_SESSION['documento_anexo_incluir'])){
                        $i=0;
                        foreach($_SESSION['documento_anexo_incluir'] as $docAnex){
                            if($docAnex)
                                if($docAnex['remover'] != "S"){
                                    $dadosDocAnex = array(
                                                        'cdocpcsequ'     => $dadosSalvar['cdocpcsequ'],
                                                        'edcanxnome'     => $docAnex['nome_arquivo'],
                                                        'idcanxarqu'     => $docAnex['arquivo'],
                                                        'cdcanxsequ'     => $docAnex['sequarquivo']+$i,
                                                        'tdcanxcada'     => $docAnex['data_inclusao'],
                                                        'cusupocodi'     => $docAnex['usermod'],
                                                        'ativo'             => 'S'
                                                        );
                                    $retorno = $ObjContrato->InsertDocumentosAnexos($dadosDocAnex);
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
                }
                    $response = array("status"=>true,"msm"=>"Contrato salvo com sucesso!");
                    print(json_encode($response));
            }
            

            
    break;
    case "IncluirMedicao":
        //Dados do fornecedor estão vindo pela session $_SESSION['dadosSalvar']
            //$dadosSalvar = $_SESSION['dadosSalvar'];
            $contrato = $_POST['contrato'];
            $valor_media = $_POST['vmedcovalm'];
            $data_inicio = $_POST['execucaoDataInicio'];
            $data_termino = $_POST['execucaoDataTermino'];
            $observacao = $_POST['emedcoobse'];

            $dadosMedicao = array(
                'cdocpcsequ'=> $ObjMedicao->anti_injection($contrato),
                'dmedcoinic'=> $ObjContrato->date_transform($ObjMedicao->anti_injection($data_inicio)),
                'dmedcofinl'=> $ObjContrato->date_transform($ObjMedicao->anti_injection($data_termino)),
                'emedcoobse'=> $ObjContrato->anti_injection($observacao),
                'cusupocod1'=> $ObjMedicao->anti_injection($contrato),
                'vmedcovalm'=> $ObjMedicao->anti_injection($valor_media)
            );

            $retorno = $ObjMedicao->insertsMedicaoIncluir($dadosMedicao);
            //Pegar os valores dos campos
          //  $html = 'INFORME: ';
            //Validação dos campos obrigatórios
            
            // fim da validação dos campos obrigatórios
            //-------------------------------------------------------------------------------------------------------------
            
                // //inserir anexo
                // if(!empty($_SESSION['documento_anexo'])){
                //     // var_dump($_SESSION['documento_anexo']);die;
                //     foreach($_SESSION['documento_anexo'] as $docAnex){
                //         if($docAnex)
                //             $seExisteDocumentoAnexo = $ObjContrato->GetDocumentosAnexos($docAnex['sequdoc'],$docAnex['sequarquivo'],$docAnex['nome_arquivo']);
                //             if(empty($seExisteDocumentoAnexo)){
                //                 $dadosDocAnex = array(
                //                                     'cdocpcsequ'     =>$docAnex['sequdoc'],
                //                                     'edcanxnome'     =>$docAnex['nome_arquivo'],
                //                                     'idcanxarqu'     => $docAnex['arquivo'],
                //                                     'cdcanxsequ'     => $docAnex['sequarquivo'],
                //                                     'tdcanxcada'     => $docAnex['data_inclusao'],
                //                                     'cusupocodi'     => $docAnex['usermod'],
                //                                     'ativo'             => 'S'
                //                                     );
                //                 $retorno = $ObjContrato->InsertDocumentosAnexos($dadosDocAnex);
                //                 if(empty($retorno)){
                //                     $response = array("status"=>false,"msm"=>"Erro! Não foi possivel editar o contrato, tente novamente! cod: 860");
                //                     print(json_encode($response));
                //                     exit;
                //                 }
                //             }
                //     }
                //     unset($_SESSION['documento_anexo']);
                // }
                $response = array("status"=>true,"msm"=>"Medição Salva com Sucesso!");

                print(json_encode($response));
 
            
    break;
    case "VerificaSeTemNumeroContrato": 
        $numeroContrato = $_POST['numcon'];
        if(!empty($numeroContrato)){
            $existeContrato = $ObjContrato->VerificaSeExisteContrato($numeroContrato);
            if($existeContrato){
                 $response = array("status"=>false,"msm"=>"Número de contrato já existe.");
                 print(json_encode($response));
            }else{
                $response = array("status"=>true,"msm"=>"");
                print(json_encode($response));
            }
        }
    break;
    case "VerificaSeTemNumeroContratoComSCC": 
        $numeroSCC = $_SESSION['csolcosequ'];
        $ItemScc        = $_SESSION["fornsequ".$numeroSCC];
        $origimSCC   = $_SESSION['origemScc'];
        $lotes = $_SESSION['citelpnuml'];
        if(!empty($numeroSCC)){
            $existeContrato = $ObjContrato->VerificaSeExisteContratoComEssaSCC($numeroSCC,$ItemScc,$origimSCC,$lotes);
            // var_dump($existeContrato->totalitens);
            if($existeContrato == true){
                 $response = array("status"=>false,"msm"=>" Já existe contrato para esse item.");
                 print(json_encode($response));
            }else{
                $response = array("status"=>true,"msm"=>"");
                print(json_encode($response));
            }
        }
    break;


 }
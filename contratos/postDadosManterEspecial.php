<?php
# -------------------------------------------------------------------------
# Portal da Compras
# Programa: CadContratoManter.php
# Autor:    Eliakim Ramos | João Madson
# Data:     12/12/2019
# -------------------------------------------------------------------------
session_start();
require_once dirname(__FILE__) . '/../funcoes.php';
require_once "ClassContratoManterEspecial.php";

$ObjContrato = new ContratoManterEspecial();
$arrayTirar  = array('.',',','-','/');

switch($_POST['op']){
    
    case "Fornecerdor2":
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
                    if(($cpfCnpjFornmascarado == $_SESSION['dadosFornecedor'][$i]->aforcrccpf) || ($cpfCnpjFornmascarado == $_SESSION['dadosFornecedor'][$i]->aforcrccgc)){
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
                                                                        'aforcrccpf'=>$ObjContrato->MascarasCPFCNPJ($DadosFornecedor->aforcrccpf), 
                                                                        'aforcrccgc'=>$ObjContrato->MascarasCPFCNPJ($DadosFornecedor->aforcrccgc),               
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
            $response = array("status"=>false,"msm"=>"Fiscal não encontrado");
            print(json_encode($response));
            exit;
        }else{
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
            // unset($_SESSION['fiscal_selecionado']);
            $_SESSION['fiscal_selecionado'][] = (object)  array(
                                                                'tipofiscal'      => $f[0]->nfiscdtipo,
                                                                'fiscalnome'      => $f[0]->nfiscdnmfs,
                                                                'fiscalmatricula' => $f[0]->efiscdmtfs,
                                                                'fiscalcpf'       => $ObjContrato->MascarasCPFCNPJ($f[0]->cfiscdcpff),
                                                                'fiscalemail'     => $f[0]->nfiscdmlfs,
                                                                'fiscaltel'      => $f[0]->efiscdtlfs,
                                                                'docsequ' =>  $_POST['doc'],
                                                                'registro'         => $f[0]->efiscdrgic,
                                                                'ent'         => $f[0]->nfiscdencp,
                                                                'docsituacao' => 'ATIVO',
                                                                'remover'=>'N'
                                    );
            $DadosDocFiscaisFiscal = $_SESSION['fiscal_selecionado'];
            $ObjJS = json_encode($DadosDocFiscaisFiscal);
            print_r($ObjJS);
            exit;
          }else{
              $response = array('msm'=>'Error o CPF é obrigatorio');
              echo json_encode($response);
              die;
          }

    break;
    case "RemoveFiscal":
        $MarcadorSeparado = explode('-',$_POST['marcador']);
        $sessaoFiscal = $_SESSION['fiscal_selecionado'];
        unset($_SESSION['fiscal_selecionado']);
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
        $_SESSION['fiscal_selecionado']=$sessaoFiscal;
        $ObjJS = json_encode($_SESSION['fiscal_selecionado']);
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
            $html .= '<tr class="mostramatricula" style="display:none;"><td  bgcolor="#DCEDF7" class="tdModal" style="margin-right:52%;width:259px;">Matrícula*</td>';
            $html .= '<td class="textonormal" style="width:259px;"><input type="text" name="matfiscal" size="11" value="'.$f[0]->efiscdmtfs.'" id="matfiscal"> </td></tr>';
            $html .= '<tr> <td bgcolor="#DCEDF7" class="tdModal" style="margin-right:52%;width:259px;" > CPF* : </td> <td class="textonormal " style="width:259px;">';
            $html .= '<input type="text" name="cpffiscal" id="cpffiscal" size="11" value="'.$ObjContrato->MascarasCPFCNPJ($f[0]->cfiscdcpff).'" readonly required> </td> </tr>';
            $html .= '<tr> <td bgcolor="#DCEDF7" class="tdModal" style="margin-right:52%;width:259px;" > Entidade Competente : </td> ';
            $html .= '<td class="textonormal " style="width:259px;"> <input type="text" name="entidadefiscal" size="25" value="'.$f[0]->nfiscdencp.'" id="entidadefiscal"> </td> </tr>';
            $html .= '<tr> <td bgcolor="#DCEDF7" class="tdModal" style="margin-right:52%;width:259px;" > Registro ou Inscrição : </td>';
            $html .= '<td class="textonormal " style="width:259px;"> <input type="text" name="RegInsfiscal" size="11" value="'.$f[0]->efiscdrgic.'" id="RegInsfiscal"> </td> </tr>';
            $html .= '<tr> <td bgcolor="#DCEDF7" class="tdModal" style="margin-right:52%;width:259px;" > E-mail* : </td>';
            $html .= '<td class="textonormal " style="width:259px;"> <input type="email" required email size="25" value="'.$f[0]->nfiscdmlfs.'" name="emailfiscal" id="emailfiscal"> </td> </tr>';
            $html .= '<tr> <td bgcolor="#DCEDF7" class="tdModal" style="margin-right:52%;width:259px;" > Telefone* :</td>';
            $html .= '<td class="textonormal " style="width:259px;"><input class="telefone" type="tel" required name="telfiscal" size="11" value="'.$f[0]->efiscdtlfs.'" id="telfiscal"> </td> </tr> ';
            $html .= '<tr> <td colspan="3"> <div> <input class="botao_fechar_fiscal botao"  name="botao_fechar" value="Voltar" type="button" style="float:right">';
            $html .= '<span id="formModal:txtModalSolicitacaoVazio"></span> <input  type="submit" name="salvar" id="btnSalvarModal" value="Salvar" style="float:right" title="Salva" class="botao_salvar botao">';
            $html .= '</div> </td> </tr>';
            $html .= '</table> </form> </div>';
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
            if(!$evalido){
                print_r(json_encode(array('msm'=>'Digite um CPF valido','Sucess'=>false)));
                    exit;
            }
        }else{
            print_r(json_encode(array('msm'=>'O CPF é obrigatório','Sucess'=>false)));
            exit;
        }
        if(empty($nome)){
            print_r(json_encode(array('msm'=>'O Nome é obrigatório','Sucess'=>false)));
            exit;
        }
        if(empty($email)){
            print_r(json_encode(array('msm'=>'O E-mail é obrigatório','Sucess'=>false)));
            exit;
        }
        if(empty($telefone)){
            print_r(json_encode(array('msm'=>'O Telefone é obrigatório','Sucess'=>false)));
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
            print_r(json_encode(array('dados'=>$f[0]->nfiscdtipo,'Sucess'=>true)));
        }else{
            print_r(json_encode(array('msm'=>'Error ao alterar o fiscal','Sucess'=>false)));
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
                        $response = array('Sucess'=>true, 'msm' => 'Registro excluido com sucesso', "dados"=>$dadosFiscal);
                        print(json_encode($response));
                    }else{
                            $response = array("Sucess"=>false, "msm"=>"Error ao excluir o fiscal");
                            print(json_encode($response));
                    }
            }else{
                $response = array("Sucess"=>false, "msm"=>"Não é possível excluir o fiscal, pois ele está associado a um contrato.");
                print(json_encode($response));
            }
        }else{
            $response = array("Sucess"=>false, "msm"=>"Error ao excluir o fiscal- o cpf não pode ser vazio");
            print(json_encode($response));
      }
    break;
    case "ExcluirForneModal":
        foreach($_SESSION['dadosFornecedor'] as $key => $FornecedorSession){
            if($_POST['info'] == $FornecedorSession->aforcrsequ){
                 $_SESSION['dadosFornecedor'][$key]->remover = 'S';
             }
         }
        //  $_SESSION['dadosFornecedor'] = array_values($_SESSION['dadosFornecedor']);
         $ObjJS = json_encode($_SESSION['dadosFornecedor']);
         print_r($ObjJS);
    break;
    case "ModalFornecedorCred":
        $html  = '<div class="modal-title" style=" font-family: Verdana,sans-serif,Arial; font-size: 8pt; font-weight: normal; font-variant: normal; color: #000000; font-style: normal; line-height: normal; text-decoration: none; font-weight: bold;">';
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
        $html .= '<input  type="button" name="pesquisar" id="btnPesquisaModal" value="Pesquisar" style="float:right" title="Pesquisar" class="botao_Pesquisar botao">';
        $html .= '<input  type="button" name="newpesquisar" id="btnNewPesquisaModal" value="Nova Pesquisa" style="float:right; display:none" title="Nova Pesquisa" class="botao_New_Pesquisar botao"> ';
        $html .= '</div> </td> </tr>';
        $html .= '<tr style="border:none"> <td colspan="3" style="border:none"> <div class="dadosFiscal"> </div> </td> </tr>';
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
        $html .= '<tr class="mostramatricula" style="display:none;"><td  bgcolor="#DCEDF7" class="tdModal" style="margin-right:52%;width:259px;">Matrícula*</td>';
        $html .= '<td class="textonormal" style="width:259px;"><input type="text" name="matfiscal" id="matfiscal"> </td></tr>';
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
        print_r($html);
    break;
    case "insertFiscal":
        
        $cpf      = trim($ObjContrato->anti_injection(str_replace($arrayTirar,'',$_POST['cpffiscal'])));
        $nome     = trim($ObjContrato->anti_injection($_POST['nomefiscal']));
        $email    = trim($ObjContrato->anti_injection($_POST['emailfiscal']));
        $telefone = trim($ObjContrato->anti_injection($_POST['telfiscal']));
        if(!empty($cpf)){
            $existe  = $ObjContrato->GetFiscal($cpf);
            $evalido = $ObjContrato->validaCPF($cpf);
            if(!empty($existe)){
                print_r(json_encode(array('msm'=>'Fiscal já cadastrado. Informe outro fiscal','Sucess'=>false)));
                exit;
            }
            if(!$evalido){
                print_r(json_encode(array('msm'=>'Digite um CPF valido','Sucess'=>false)));
                    exit;
            }
        }else{
            print_r(json_encode(array('msm'=>'O CPF é obrigatório','Sucess'=>false)));
            exit;
        }
        if(empty($nome)){
            print_r(json_encode(array('msm'=>'O Nome é obrigatório','Sucess'=>false)));
            exit;
        }
        if(empty($email)){
            print_r(json_encode(array('msm'=>'O E-mail é obrigatório','Sucess'=>false)));
            exit;
        }
        if(empty($telefone)){
            print_r(json_encode(array('msm'=>'O Telefone é obrigatório','Sucess'=>false)));
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
        $retorno = $ObjContrato->insertFiscal($dadosFiscal);
        if(!empty($retorno)){
            $DadosFiscal = $ObjContrato->GetFiscal($cpf);
            $_SESSION['fiscal_selecionado'][] = (object)  array(
                'tipofiscal'      => $DadosFiscal[0]->nfiscdtipo,
                'fiscalnome'      => $DadosFiscal[0]->nfiscdnmfs,
                'fiscalmatricula' => $DadosFiscal[0]->efiscdmtfs,
                'fiscalcpf'       => $DadosFiscal[0]->cfiscdcpff,
                'fiscalemail'     => $DadosFiscal[0]->nfiscdmlfs,
                'fiscaltel'      => $DadosFiscal[0]->efiscdtlfs,
                'docsequ' =>  $_SESSION['doc_fiscal'],
                'registro'         => $DadosFiscal[0]->efiscdrgic,
                'ent'         => $DadosFiscal[0]->nfiscdencp,
                'docsituacao' => 'ATIVO',
                'remover'=>'N'
                );
            $ObjJS = json_encode(array("Sucess"=>true,"dados"=>$_SESSION['fiscal_selecionado']));
            $ObjContrato->DesconectaBanco();
            print_r($ObjJS);        
            // print_r(json_encode(array('msm'=>'Fiscal Cadastrado com sucesso','Sucess'=>true)));
            
            exit;
        }else{
            print_r(json_encode(array('msm'=>'Error ao cadastrar Fiscal','Sucess'=>false)));
            exit;
        }
    break;
    case "uploadArquivo":
        $parametros = $ObjContrato->GetParametrosGerais();
        $ex                = array('.pdf');
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
               
                $sequarquivo = $ObjContrato->GetSequencialDocAnexo($_POST['idRegistro']);

                $nomeArquivo = $_FILES['documento']['name'][$i];
                $arquivoBin  = bin2hex(file_get_contents($_FILES['documento']['tmp_name'][$i]));
                if(!empty($_SESSION['documento_anexo'])){
                        if(!in_array($nomeArquivo, $_SESSION['documento_anexo'])){
                                    $_SESSION['documento_anexo'][]  = array(
                                                                            'nome_arquivo' =>$nomeArquivo,
                                                                            'arquivo'      => $arquivoBin,
                                                                            'sequarquivo'  => ($sequarquivo->ultimosequncial+1),
                                                                            'sequdoc'      => $_POST['idRegistro'],
                                                                            'data_inclusao'=> date("Y-m-d H:i:s.u"),
                                                                            'usermod'      =>$ObjContrato->anti_injection($_SESSION['_cusupocodi_']),
                                                                            'ativo'        => 'S',
                                                                            'remover'       => 'N'
                                                                        );
                        }else{
                            foreach($_SESSION['documento_anexo'] as $key =>  $item){
                                if($item['nome_arquivo'] == $nomeArquivo && $item['remover'] == "S"){
                                    $_SESSION['documento_anexo'][$key]['remover'] = "N";
                                }
                            }
                        }
                }else{
                        $_SESSION['documento_anexo'][]  = array(
                            'nome_arquivo' =>$nomeArquivo,
                            'arquivo'      => $arquivoBin,
                            'sequarquivo'  => ($sequarquivo->ultimosequncial+1),
                            'sequdoc'      => $_POST['idRegistro'],
                            'data_inclusao'=> date("Y-m-d H:i:s.u"),
                            'usermod'      =>$ObjContrato->anti_injection($_SESSION['_cusupocodi_']),
                            'ativo'        => 'S',
                            'remover'       => 'N'
                        );
                }
            }
            $_SESSION['anexoInseridoOuRetirado'] = 'true';
            print_r(json_encode(array('sucess'=> true,'msm'=>"Carregando o arquivo na tela....")));
        }
        
    break;
    case "GetDocAnex":
        if(!empty($_POST['idRegistro'])){
            $anexoInseridoOuRetirado = $_SESSION['anexoInseridoOuRetirado'];
            $dadosArquivos = array();
                $html ='<tr class="FootFiscaisDoc">';
                $html .='<td></td>';
                $html .='<td colspan="4">ARQUIVO</td>';
                $html .='<td colspan="4">DATA DA INCLUSÃO</td>';
                $html .='<td><input type="hidden" id="anexoInseridoOuRetirado" name="anexoInseridoOuRetirado" value="'.$anexoInseridoOuRetirado.'"></td>';
                $html .='</tr>';
                if(!empty($_SESSION['documento_anexo'])){
                    foreach($_SESSION['documento_anexo'] as $key => $doc){
                        if(!in_array(array($doc['sequarquivo'], $doc['nome_arquivo']),  $dadosArquivos)){   
                                    $dadosArquivos[]  = array(
                                            'nome_arquivo' =>$doc['nome_arquivo'],
                                            'arquivo'      => $doc['arquivo'],
                                            'sequdoc'      => $doc['sequdoc'],
                                            'sequarquivo'  => $doc['sequarquivo'],
                                            'data_inclusao'=> $doc['data_inclusao'],
                                            'usermod'      => $doc['usermod'],
                                            'ativo'        => 'S',
                                            'remover'       =>  $doc['remover']
                                        );
                        }
                    }
                }
                unset($_SESSION['documento_anexo']);
                $_SESSION['documento_anexo'] =  $dadosArquivos;
                foreach($_SESSION['documento_anexo'] as $key => $anexo){
                    if( $anexo['remover'] != 'S'){  
                        $html .='<tr bgcolor="#ffffff">';
                        $html .='<td><input type="radio" name="docanex" value="'.$anexo['sequdoc'].'*'.$anexo['nome_arquivo'].'"></td>';
                        // $html .='<td><input type="radio" name="docanex" value="'.$key.'"></td>';
                        $html .='<td colspan="4">'.$anexo['nome_arquivo'].'</td>';
                        $html .='<td colspan="4">'.$anexo["data_inclusao"].'</td>';
                        $html .='</tr>';
                    }
                }
                $html .='<tr bgcolor="#ffffff">';
                $html .='<td colspan="8"align="center">';
                 $html .='<button type="button" class="botao" onclick="Subirarquivo()">Incluir Documento</button>';
                 $html .='<button type="button" class="botao" id="btnRemoveAnexo">Retirar Documento</button>';
                $html .='</td>';
                $html .='</tr>';
            print_r($html);
        }else{
                $html ='<tr class="FootFiscaisDoc">';
                $html .='<td></td>';
                $html .='<td colspan="4">ARQUIVO</td>';
                $html .='<td colspan="4">DATA DA INCLUSÃO</td>';
                $html .='<td><input type="hidden" id="anexoInseridoOuRetirado" name="anexoInseridoOuRetirado" value="'.$anexoInseridoOuRetirado.'"></td>';
                $html .='</tr>';
                foreach($_SESSION['documento_anexo'] as $key => $anexo){
                        $html .='<tr bgcolor="#ffffff">';
                        $html .='<td><input type="radio" name="docanex" value="'.$key.'"></td>';
                        $html .='<td colspan="4">'.$anexo['nome_arquivo'].'</td>';
                        $html .='<td colspan="4">'.$anexo["data_inclusao"].'</td>';
                        $html .='</tr>';
                }
                $html .='<tr bgcolor="#ffffff">';
                $html .='<td colspan="8"align="center">';
                $html .='<button type="button" class="botao" onclick="Subirarquivo()">Incluir Documento</button>';
                $html .='<button type="button" class="botao" id="btnRemoveAnexo">Retirar Documento</button>';
                $html .='</td>';
                $html .='</tr>';
                print_r($html);
        }

    break;
    case "RemoveDocAnex":
            $aux = $_POST['marcador'];
            $SessaoDocumento = '';
            $anexoInseridoOuRetirado = $_SESSION['anexoInseridoOuRetirado'] = 'true';
            if(!empty( $_SESSION['documento_anexo'])){
                $SessaoDocumento = $_SESSION['documento_anexo'];
                $SessaoDocumento[$aux]['remover'] = "S";
            }
            unset($_SESSION['documento_anexo']); //limpo a sessão para não conter nenhum lixo
            $html ='<tr class="FootFiscaisDoc">';
            $html .='<td></td>';
            $html .='<td colspan="4">ARQUIVO</td>';
            $html .='<td colspan="4">DATA DA INCLUSÃO</td>';
            $html .='<td><input type="hidden" id="anexoInseridoOuRetirado"  name="anexoInseridoOuRetirado" value="'.$anexoInseridoOuRetirado.'"></td>';
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
            $html .='<button type="button" class="botao" onclick="Subirarquivo()">Incluir Documento</button>';
            $html .='<button type="button" class="botao" id="btnRemoveAnexo">Retirar Documento</button>';
            $html .='</td>';
            $html .='</tr>';
            $_SESSION['documento_anexo'] = $SessaoDocumento;
            print_r($html);
    break;
    case "UpdateContrato":
          
           $anoContrato   = !empty($_POST['numcontrato'])?explode('/', $_POST['numcontrato']):array(1=>null);
           $numcontrato   = !empty($_POST['numcontrato'])?$_POST['numcontrato']:null;
          
           if($existeContrato){
                $response = array("status"=>false,"msm"=>"Número de contrato já existe.");
                print(json_encode($response));
                break;
            }
            
           $dadosContrato = array(
                                    'actrpcnumc'=> $ObjContrato->anti_injection(str_replace($arrayTirar,'',$numcontrato)),
                                    'ectrpcnumf'=> $ObjContrato->anti_injection($_POST['numcontrato']),
                                    'corglicodi'=>$ObjContrato->anti_injection(!empty($_POST['seqscc'])?$_POST['corglicodi']:$_POST['orgao_licitante']),
                                    'tctrpculat'=>date("Y-m-d"),
                                    'cdocpcsequ'=> $cdocpcsequ

           ); 

           //adicionando valores do contrato antigo
          
            $retorno = $ObjContrato->UpdateContrato($dadosContrato);
            if(empty($retorno)){}
                $response = array("status"=>false,"msm"=>"Error não foi possivel editar o contrato, tente novamente");
                print(json_encode($response));
            }
    break;
    case "UpdateContratoAnexo":
          if(empty($_SESSION['documento_anexo'])){
            $response = array("status"=>false,"msm"=>"Informe: Documento anexo ");
            print(json_encode($response));
            break;
          }else if($_SESSION['documento_anexo'][0]['remover'] == "S" && empty($_SESSION['documento_anexo'][1])){
            $response = array("status"=>false,"msm"=>"Informe: Documento anexo ");
            print(json_encode($response));
            break;
          }
           $cdocpcsequ    = $ObjContrato->anti_injection($_POST['idregistro']);
          
            if(!empty($cdocpcsequ )){
              if(!empty($_SESSION['documento_anexo'])){
                    $i=0;
                    foreach($_SESSION['documento_anexo'] as $docAnex){
                        if($docAnex)
                            $seExisteDocumentoAnexo = $ObjContrato->VerificaSeJaExisteDocumentoAnexo($docAnex['sequdoc'],$docAnex['sequarquivo'],$docAnex['nome_arquivo']);
                            if($docAnex['remover'] == "S" && !empty($seExisteDocumentoAnexo)){
                                $ObjContrato->DeletaDocumentoAnexo($docAnex['sequdoc'],$docAnex['sequarquivo']);
                            }
                            if(empty($seExisteDocumentoAnexo) && $docAnex['remover'] != "S"){
                                $dadosDocAnex = array(
                                                    'cdocpcsequ'     =>$docAnex['sequdoc'],
                                                    'edcanxnome'     =>$docAnex['nome_arquivo'],
                                                    'idcanxarqu'     => $docAnex['arquivo'],
                                                    'cdcanxsequ'     => $docAnex['sequarquivo']+$i,
                                                    'tdcanxcada'     => $docAnex['data_inclusao'],
                                                    'cusupocodi'     => $docAnex['usermod'],
                                                    'ativo'             => 'S'
                                                    );
                                $retorno = $ObjContrato->InsertDocumentosAnexos($dadosDocAnex);
                                if(empty($retorno)){
                                    $response = array("status"=>false,"msm"=>"Error não foi possivel editar o contrato, tente novamente");
                                    print(json_encode($response));
                                    exit;
                                }
                            }
                            $i++;
                    }
                    unset($_SESSION['documento_anexo']);
                }
                $response = array("status"=>true,"msm"=>"Contrato editado com sucesso");
                print(json_encode($response));
            }else{
                $response = array("status"=>false,"msm"=>"Error não foi possivel editar o contrato, tente novamente");
                print(json_encode($response));
            }
    break;
    case "ExcluirContrato": 
            $codSequContrato = $_POST['codContrato'];
            $existeBloqueio = $ObjContrato->VerificaSeTemPendenciaEmBloqueio($codSequContrato);
            if(count($existeBloqueio) > 0){
                $ObjContrato->DeletaItemDocumentoBloqueio($codSequContrato);
            }
            $existeFornecedor = $ObjContrato->VerificarSeExisteFornecedorContrato($codSequContrato);
            if(count($existeBloqueio) > 0){
                $ObjContrato->DeleteAllFornecedorContrato($codSequContrato);
            }

            if(!empty($codSequContrato)){
                    
                    $ObjContrato->RemoveAllFiscaldoContrato($codSequContrato);
                    $ObjContrato->DeleteAllItemDocumento($codSequContrato);
                    $retorno = $ObjContrato->DeleteContrato($codSequContrato);
                    $ObjContrato->DeletaAllDocumentoAnexo($codSequContrato);
                    $ObjContrato->DeleteAllDocumentos($codSequContrato);
                if(!empty($retorno)){
                    $response = array("status"=>true,"msm"=>"Exclusão realizada com sucesso.");
                    print(json_encode($response));
                }
            }else{
                $response = array("status"=>false,"msm"=>"Error ao tentar excluir o contrato tente novamente.");
                print(json_encode($response));
            }
    break;
    case "CancelarContrato": 
        $codSequContrato = $_POST['codContrato'];
        if(!empty($codSequContrato)){
            $retorno = $ObjContrato->CancelaContrato($codSequContrato);
            if(!empty($retorno)){
                $response = array("status"=>true,"msm"=>"Contrato cancelado com sucesso.");
                print(json_encode($response));
            }
        }else{
            $response = array("status"=>false,"msm"=>"Error ao tentar cancelar o contrato tente novamente.");
            print(json_encode($response));
        }
    break;
    case "EncerrarContrato": 
        $codSequContrato = $_POST['codContrato'];
        if(!empty($codSequContrato)){
            $retorno = $ObjContrato->EncerrarContrato($codSequContrato);
            if(!empty($retorno)){
                $response = array("status"=>true,"msm"=>"Contrato encerrado com sucesso.");
                print(json_encode($response));
            }
        }else{
            $response = array("status"=>false,"msm"=>"Error ao tentar encerrar o contrato tente novamente.");
            print(json_encode($response));
        }
    break;
    case "DesfazerEncerramentoContrato": 
        $codSequContrato = $_POST['codContrato'];
        if(!empty($codSequContrato)){
            $retorno = $ObjContrato->DesfazerEncerramentoContrato($codSequContrato);
            if(!empty($retorno)){
                $response = array("status"=>true,"msm"=>"Encerramento do contrato foi desfeito com sucesso.");
                print(json_encode($response));
            }
        }else{
            $response = array("status"=>false,"msm"=>"Error ao tentar desfazer o encerramento do contrato, tente novamente.");
            print(json_encode($response));
        }
    break;
    case "VerificaSeTemNumeroContrato": 
        $numeroContrato = $_POST['numcon'];
        $idContrato = $_POST['idContrato'];
        if(!empty($numeroContrato)){
            $existeContrato = $ObjContrato->VerificaSeExisteContrato($numeroContrato, $idContrato);
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
        if(!empty($_SESSION['csolcosequ'])){
            $numeroSCC = $_SESSION['csolcosequ'];
            $ItemScc        = $_SESSION["fornsequ".$numeroSCC];
            $origimSCC   = $_SESSION['origemScc'];
            $idContrato = $_POST['idContrato'];
            if(!empty($numeroSCC)){
                $existeContrato = $ObjContrato->VerificaSeExisteContratoComEssaSCC($numeroSCC,$ItemScc,$origimSCC, $idContrato);
                // var_dump($existeContrato->totalitens);
                if(!empty($existeContrato->totalitens)){
                    $response = array("status"=>false,"msm"=>" Já existe um contrato para o mesmo fornecedor/item.");
                    print(json_encode($response));
                }else{
                    $response = array("status"=>true,"msm"=>"");
                    print(json_encode($response));
                }
            }
        }
    break;
    case "buscaFornecedor":
        $CPFCNPJ = str_replace($arrayTirar, "", $_POST['CPFCNPJ']);
        $flagCpfCnpj = $_POST['flagCpfCnpj'];
        if($flagCpfCnpj == 1){
            $valida_cnpj = $ObjContrato->valida_cnpj($CPFCNPJ);
            if($valida_cnpj == false){ 
                $response = array("status"=>false,"msm"=>"O CNPJ informado não é válido");
                print(json_encode($response));
            }
        }
        if($flagCpfCnpj == 2){
            $valida_cpf = $ObjContrato->validaCPF($CPFCNPJ);
            if($valida_cpf == false){ 
                $response = array("status"=>false,"msm"=>"O CPF informado não é válido");
                print(json_encode($response));
            }
        }
        $resultsBuscaForn = $ObjContrato->buscaFornecedorContAnt($CPFCNPJ, $flagCpfCnpj);
        $_SESSION['fornecedorContratado'] = $resultsBuscaForn;
        print(json_encode($resultsBuscaForn));
        
    break;
    case "mantemDadosPost":
       $_SESSION['dadosManter'] = $_POST;
    break;

}
<?php
# -------------------------------------------------------------------------
# Portal da Compras
# Programa: CadAdesoesDeAtaPesquisa.php
# Autor:    Eliakim Ramos
# Data:     11/07/2022
# -------------------------------------------------------------------------
# Autor:    João Madson
# Data:     06/09/2022
# CR:		268479
# -------------------------------------------------------------------------

session_start();
require_once dirname(__FILE__) . '/../funcoes.php';
require_once "ClassAdesaoAtaPesquisa.php";

$ObjClassAdesaoAtaPesquisa = new ClassAdesaoAtaPesquisa();
$arrayTirar  = array('.',',','-','/');

function floatvalue($val){
    $val = str_replace(",",".",$val);
    $val = preg_replace('/\.(?=.*\.)/', '', $val);
    //var_dump($val);
    return floatval($val);
}

switch($_POST['op']){
    case "OrgaoGestor":
        
        $internet = !empty($_POST['internet'])?$_POST['internet']:"";
       // var_dump($internet);
        $dados = $ObjClassAdesaoAtaPesquisa->GetOrgao($internet);
        $isCorporativo =  $ObjClassAdesaoAtaPesquisa->checaUsuarioManterEspecial();
        $option="";
        $arrayCode = array();
        foreach($dados as $orgao){
                $arrayCode[] = $orgao->corglicodi;
        }
        if(count($arrayCode) > 1 && $isCorporativo){
            $option.= '<option value="'.implode(',',$arrayCode).'">Todos</option>';
        }else{
            $option.= '<option value="">Selecione um órgão..</option>';
        }
        $selected ="";
        foreach($dados as $orgao){
            if(!empty($_POST['vol'])){
                $selected = ($_POST['vol'] == $orgao->corglicodi)?"selected":"eliakim";
            }
            $option.='<option value="'.$orgao->corglicodi.'" '.$selected.'>'.$orgao->eorglidesc.' </option>';
        }
        $ObjClassAdesaoAtaPesquisa->DesconectaBanco();
        print_r($option);
    break;
    case "Pesquisa":
                $tabelaDados = array();

                $ArrayDados = array(
                    'orgao'             => $_POST['Orgao'],
                    "numeroScc"         => str_replace($arrayTirar,'',$_POST['numeroScc']),
                    "tipo_ata"          => $_POST['tipo_ata'],
                    "tipo_sarp"         => $_POST['tipo_sarp'],
                    "data_inicio"       => $ObjClassAdesaoAtaPesquisa->date_transform($_POST['Data_inicio']),
                    "data_fim"          => $ObjClassAdesaoAtaPesquisa->date_transform($_POST['Data_fim'])
                );

                $tabelaDados = $ObjClassAdesaoAtaPesquisa->PesquisarSCC($ArrayDados);

                $tipoAta = $_POST['tipo_ata'];
                $tipoSARP = ($_POST['tipo_sarp'] == "P") ? "PARTICIPANTE" : "CARONA";

                $auxDadosTabela = array();
                
                $tableHtml  = "<form method='post' action='' name='formtablemodal' id='formtablemodal'>";
                $tableHtml  .= "<table border='0' bordercolor='#75ADE6' width='100%' class='textonormal' id='tabelaresultado'>";
                $tableHtml  .="<thead>";
                $tableHtml  .="<tr>";
                $tableHtml  .='<td colspan="0" style="text-align: center; background-color: #75ADE6; font-weight: bold;" >';
                $tableHtml  .="RESULTADO DA PESQUISA";
                $tableHtml  .="</td>";
                $tableHtml  .="</tr>";
                $tableHtml  .="</thead>";
                $tableHtml  .="<TBody>";
                
                
                if(!empty($tabelaDados)){
                    $tableHtml  .="<tr>";
                    $tableHtml  .="<td>";
                    $tableHtml  .= "<table border='1' bordercolor='#75ADE6' width='850px' class='textonormal'>";
                    $tableHtml  .="<thead>";
                    $tableHtml  .='<tr style="background-color: #bfdaf2; text-align: center; font-weight: bold; color: #3165a5;">';
                    $tableHtml  .="<td>SOLICITAÇÃO</td>";
                    $tableHtml  .="<td>OBJETO</td>";
                    $tableHtml  .="<td>FORNECEDOR</td>";
                    $tableHtml  .="<td>TIPO DE ATA</td>";
                    $tableHtml  .="<td>TIPO DE SARP</td>";
                    $tableHtml  .="</tr>";
                    $tableHtml  .="</thead>";
                    $tableHtml  .="<TBody>";
                            $cont=1;
                            foreach($tabelaDados as $informacoesTable){ 
                                $SCC            = "";
                                $fornecedor = "";
                                if(!empty($_POST['numeroScc'])){
                                    if(!empty($informacoesTable->carpnosequ)){
                                        $retornoata =  $ObjClassAdesaoAtaPesquisa->GetAtaExterna($informacoesTable->carpnosequ);
                                        if(!empty($retornoata)){
                                            $fornecedor = $retornoata->nforcrrazs;
                                        }else{
                                            $retornoata = $ObjClassAdesaoAtaPesquisa->GetAtaInterna($informacoesTable->carpnosequ);
                                            if(!empty($retornoata)){
                                                $fornecedor = $retornoata->nforcrrazs;
                                            }
                                        }
                                    }else{
                                        $retornoSemAta = $ObjClassAdesaoAtaPesquisa->GetSemAtaNova($informacoesTable->csolcosequ);
                                        if(!empty($retornoSemAta)){
                                            $fornecedor = $retornoSemAta->nforcrrazs;
                                        }
                                    }
                                }else{
                                    $fornecedor = $informacoesTable->nforcrrazs;
                                }
                                if(!empty($informacoesTable->ccenpocorg) && !empty($informacoesTable->ccenpounid) && !empty($informacoesTable->csolcocodi) && !empty($informacoesTable->asolcoanos)){
                                    $SCC       = sprintf('%02s', $informacoesTable->ccenpocorg) . sprintf('%02s', $informacoesTable->ccenpounid) . '.' . sprintf('%04s', $informacoesTable->csolcocodi) . '/' . $informacoesTable->asolcoanos;
                                }
                                $LINKSCC = 'CadAdesoesDeAtaFileUpload.php?scc='.$SCC.'&ata='.$informacoesTable->tipo_ata.'&tipo='.$informacoesTable->fsolcorpcp.'&orgao='.$informacoesTable->corglicodi.'&carpnosequ='.$informacoesTable->carpnosequ.'&csolcosequ='.$informacoesTable->csolcosequ;
                                // var_dump($informacoesTable->ctpcomcodi);
                                    $sarpDesc = ($informacoesTable->fsolcorpcp == 'P') ? "PARTICIPANTE" : "CARONA";
                                    $tableHtml     .="<tr>";
                                    $tableHtml     .='<td> <a href="'.$LINKSCC.'" >'.$SCC.'</a></td>';
                                    $tableHtml     .='<td> '.$informacoesTable->esolcoobje.'</td>';
                                    $tableHtml     .='<td> '.$fornecedor.'</td>';
                                    $tableHtml     .='<td> '.$informacoesTable->tipo_ata.'</td>';
                                    $tableHtml     .='<td> '.$sarpDesc.'</td>';
                                    $tableHtml     .="</tr>";
                            }
                    $tableHtml .="</TBody>";
                    $tableHtml .="</table>";
                    $tableHtml  .="</td>";
                    $tableHtml  .="</tr>";
            }else{
                // $tableHtml  .="</thead>";
                $tableHtml .="<tr>";
                $tableHtml .="<td colspan='8' style='border:none;text-align: center;'>";
                $tableHtml .='Pesquisa sem ocorrências.';
                $tableHtml .="</td>";
                $tableHtml .="</tr>";
            }
                
                $tableHtml .="</table>";
                $tableHtml .="</form>";
                unset($informacoesTable);
                unset($SCC);
                $ObjClassAdesaoAtaPesquisa->DesconectaBanco();
                print_r($tableHtml);
    break;

 }
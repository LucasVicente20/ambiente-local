<?php 
 /**
 * Portal de Compras
 * Programa: CadIncluirDFD.php
 * Autor: Diógenes Dantas | Madson Felix
 * Data: 17/11/2022
 * Objetivo: Programa para inclusão de DFD
 * Tarefa Redmine: #275243
 * -------------------------------------------------------------------
  * Alterado: Osmar Celestino
  * Data: 20/12/2022
  * Tarefa: 276459
  * -------------------------------------------------------------------
  * Alterado: Lucas Vicente
  * Data:09/01/2023
  * Tarefa: Ajuste na regra do Configurador DFD
  * -------------------------------------------------------------------
  * Alterado: João Madson   
  * Data: 09/01/2023
  * Tarefa: #277372
  * -------------------------------------------------------------------
  * Alterado: João Madson | Lucas Vicente  
  * Data: 16/01/2023
  * Tarefa: Relatório de correções Nº3 Incluir DFD

  */
# Executa o controle de segurança	#
session_start();

# Acesso ao arquivo de funções #
require_once "../funcoes.php";
require_once "ClassPlanejamento.php";

$objPlanejamento = new Planejamento();
$arrayAlfabeto = array("a","b","c","d","e","f","g","h","i","j","k","l","m","n","o","p","q","r","s","t","u","v","w","x","y","z");

switch ($_POST['op']) {
    case 'Limpar':
        unset($_SESSION['dadosDaPagina']);
        unset($_SESSION['cnpjMultiplos']);
        unset($_SESSION['KeepOrgaoSelect']);
        unset($_SESSION['classe']);
        unset($_SESSION["vincularDFD"]);
        unset($_SESSION["servico"]);
        unset($_SESSION['AreaReqCod']);
        unset($_SESSION['Bloqueio']); 
        print_r(json_encode(array("status"=>true)));
    break;
    case 'getOrgao':
        //varifica se ja vem algo na sessão para marcar como selecionado e manter a consistencia dos dados
        $selectKeeper = !empty($_SESSION['KeepOrgaoSelect']) ? $_SESSION['KeepOrgaoSelect']:null;
        $orgaosUsuario = $objPlanejamento->getOrgao();
        $countOrgao = (!is_null($orgaosUsuario) && !empty($orgaosUsuario)) ? count($orgaosUsuario) : 0;

        if ($countOrgao > 1) {
            // $htmlAreaReq = "<select id='selectAreaReq' name='selectAreaReq' size='1' style='width:auto; font-size: 10.6667px;'>";
            $htmlAreaReq .= "<option value= -1>Selecione a Área Requisitante...</option>";
            
            foreach ($orgaosUsuario as $orgao) {
                if(!is_null($selectKeeper) && $selectKeeper == $orgao->corglicodi){
                    $htmlAreaReq .= "<option value='".$orgao->corglicodi."' selected>".$orgao->eorglidesc."</option>";
                    // $htmlCNPJ .= "<input type='text' name='cnpjAreaReq' id='cnpjAreaReq' value='".$objPlanejamento->MascarasCPFCNPJ($orgao->aorglicnpj)."' readonly>";
                    $htmlCNPJ = $objPlanejamento->MascarasCPFCNPJ($orgao->aorglicnpj);
                }else{
                    $htmlAreaReq .= "<option value='".$orgao->corglicodi."'>".$orgao->eorglidesc."</option>";
                }
                $_SESSION['cnpjMultiplos'][$orgao->corglicodi] = $orgao->aorglicnpj;
                
            }
            print_r(json_encode(array("status"=>200, "htmlOrgao"=>$htmlAreaReq, "htmlCNPJ"=>$htmlCNPJ, "cnpjMultiplos"=>$cnpjMultiplos, "multiplos"=>true)));

        } else {
            $_SESSION['AreaReqCod'] = $orgaosUsuario[0]->corglicodi;
            $htmlAreaReq = "<span id='SpanAreaReqDesc' style='font-size: 10.6667px;'>".$orgaosUsuario[0]->eorglidesc."</span>";
            $htmlAreaReq .= "<input type='hidden' name='AreaReqCod' id='AreaReqCod' value='".$orgaosUsuario[0]->corglicodi."'>";
            $htmlAreaReq .= "<input type='hidden' name='AreaReqDesc' id='AreaReqDesc' value='".$orgaosUsuario[0]->eorglidesc."'>";
            $htmlCNPJ = $objPlanejamento->MascarasCPFCNPJ($orgaosUsuario[0]->aorglicnpj);
            print_r(json_encode(array("status"=>200, "htmlOrgao"=>$htmlAreaReq, "htmlCNPJ"=>$htmlCNPJ, "multiplos"=>false)));

        }
    break;
    case "SelecionaCNPJ":
        $_SESSION['KeepOrgaoSelect'] = $_POST['OrgSelecionado']; //Armazena a informação para manter os dados do select do orgão e por consequencia, deste.

        $posicaoArray = $_POST['OrgSelecionado'];
        $cnpjSelecionado = $objPlanejamento->MascarasCPFCNPJ($_SESSION['cnpjMultiplos'][$posicaoArray]);
        unset($_SESSION['vincularDFD']);
        print_r(json_encode(array("status"=>200, "htmlCNPJ"=>$cnpjSelecionado)));
        exit;
    break;
    case 'modalPesqClasse':
        $html  = '<div class="modal-title textonormal" >';
        $html .= 'PESQUISAR - CLASSE MATERIAL/SERVIÇO ';
        $html .= '<span class="btn-fecha-modal close" >[ X ]</span>';
        $html .= '</div>';
        $html .= '<div class="modal-body">';
        $html .= '<form action="" method="post" name="CadIncluirDFD">';
        $html .= '<table class="textonormal" width="100%">';
        $html .= '<tr border=1>';
        $html .='<tr>';
        $html .='<td align="left" colspan="2" id="tdmensagemM">';
        $html .='<div class="mensagemM">';
        $html .='<div class="error" colspan="5">';
        $html .='Erro';
        $html .='</div>';
        $html .='<span id="mensagemErroModal" class="mensagem-textoM">';
        $html .='</span>';
        $html .='</div>';
        $html .='</td>';
        $html .='</tr>';    
        $html .='<tr>';
        $html .='<td class="textonormal" bgcolor="#DCEDF7" width="31%">Classe</td>';
        $html .='<td class="textonormal" colspan="5">';
        $html .='<select id="OpcaoPesquisaClasse" name="OpcaoPesquisaClasse" class="textonormal">';
        $html .='<option value="0">Código Reduzido</option>';
        $html .='<option value="1">Descrição contendo</option>';
        $html .='<option value="2">Descrição iniciada por</option>';
        $html .='</select>';
        $html .='<input type="text" id="ClasseDescricaoDireta" name="ClasseDescricaoDireta" value="" size="10" maxlength="10" class="textonormal">';
        $html .='<img id="lupaClasse" src="../midia/lupa.gif" border="0">';
        $html .='</td>';
        $html .='</tr>';
        $html .='<tr>';
        $html .='<td class="textonormal" bgcolor="#DCEDF7">Material</td>';
        $html .='<td class="textonormal" colspan="5">';
        $html .='<select id="OpcaoPesquisaMaterial" name="OpcaoPesquisaMaterial" class="textonormal">';
        $html .='<option value="0">Código Reduzido</option>';
        $html .='<option value="1">Descrição contendo</option>';
        $html .='<option value="2">Descrição iniciada por</option>';
        $html .='</select>';
        $html .='<input type="text" id="MaterialDescricaoDireta" name="MaterialDescricaoDireta" value="" size="10" maxlength="10" class="textonormal" >';
        $html .='<img id="lupaMaterial" src="../midia/lupa.gif" border="0">';
        $html .='</td>';
        $html .='</tr>';
		$html .='<tr>';
        $html .='<td class="textonormal" bgcolor="#DCEDF7" width="34%">Serviço</td>';
        $html .='<td class="textonormal" colspan="2">';
        $html .='<select id="OpcaoPesquisaServico" name="OpcaoPesquisaServico" class="textonormal">';
        $html .='<option value="0">Código Reduzido</option>';
        $html .='<option value="1">Descrição contendo</option>';
        $html .='<option value="2">Descrição iniciada por</option>';
        $html .='</select>';
        $html .='<input type="text" id="ServicoDescricaoDireta" name="ServicoDescricaoDireta" value="" size="10" maxlength="10" class="textonormal" >';
        $html .='<img id="lupaServico" src="../midia/lupa.gif" border="0" alt="0">';
        $html .='</td>';
        $html .='</tr>';
        $html .= '</tr>';
        $html .= '<tr><table width="100%" bordercolor="#75ADE6" border="1" cellspacing="0"><tbody><tr> <td colspan="3">';
        $html .= ' <div><button class="btn-fecha-modal botao"  name="botao_voltar" value="Voltar" type="button" style="float:right">Voltar</button>';
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
        $html .= ' <div id="pesqDivModal"> ';
        $html .= ' </div> </td> </tr>';
        $html .= '</table> </form> </div>';
        print_r($html);
    break;
    case 'PesqClasse':
        $tipoDePesquisa  = $_POST["tipoPesq"];
        $opcaoDePesquisa = $_POST["opcaoPesq"];
        $formAction      = $_POST["action"];
        $dadoAPesquisar  = $objPlanejamento->anti_injection($_POST["dadoPesq"]);
        //validar o que foi informado
        if(empty($dadoAPesquisar)){
            //retorna mensagem de erro para a tela usando json
            print_r(json_encode(array("status"=>404, "msm"=>"O campo não deve pode vazio para realizar a pesquisa.")));
            exit;
        }
        // fim da validação

        //Chama a função de busca
        $resultadoPesquisa = $objPlanejamento->pesquisaClasseMatServ($tipoDePesquisa, $opcaoDePesquisa, $dadoAPesquisar);
        $_SESSION['classe'] = $resultadoPesquisa;
        //Montagem do resultado
        $tableHtml  = "<form method='post' action='$formAction' name='formModalPesqClasse' id='formModalPesqClasse'>";
        $tableHtml  .= "<table border='1' bordercolor='#75ADE6' width='100%' class='textonormal'>";
        $tableHtml  .="<thead>";
        $tableHtml  .="<tr>";
        $tableHtml  .='<td colspan="6" style="text-align: center; background-color: #75ADE6; font-weight: bold;" >';
        $tableHtml  .="RESULTADO DA PESQUISA";
        $tableHtml  .="</td>";
        $tableHtml  .="</tr>";
        if(!empty($resultadoPesquisa)){
            $tableHtml  .= '<tr style="background-color: #bfdaf2; text-align: center; font-weight: bold; color: #3165a5;">';
            $tableHtml  .= "<td></td>";
            $tableHtml  .= "<td>CLASSE</td>";
            $tableHtml  .= "<td>DESCRIÇÃO DO ITEM</td>";
            $tableHtml  .= "<td>CÓD. REDUZIDO </td>";
            if(empty($resultadoPesquisa[0]->cservpsequ)){
            $tableHtml  .= "<td>UNIDADE</td>";
            }
            $tableHtml  .= "</tr>";
            $tableHtml  .= "</thead>";
            $tableHtml  .= "<TBody>";
            $ordinal = 0;
            foreach($resultadoPesquisa as $dados){ 
                $tableHtml  .= "<tr width='850px'>";
                $tableHtml  .= "<td><input type='radio' name='radioClasse' id='radioClasse' value='$ordinal'></td>";
                $tableHtml  .= "<td style='word-wrap: inherit; max-width: 210px;'>$dados->eclamsdesc</td>";
                
                if(!empty($dados->cmatepsequ)){
                    $tableHtml  .= "<td style='word-wrap: inherit; max-width: 450px;'>$dados->ematepdesc</td>";
                    $tableHtml  .= "<td>$dados->cmatepsequ</td>";
                }else if($dados->sequencialmatserv){
                    $tableHtml  .= "<td style='word-wrap: inherit; max-width: 450px;'>$dados->descricaomatserv</td>";
                    $tableHtml  .= "<td>$dados->sequencialmatserv</td>";
                }else{
                    $tableHtml  .= "<td style='word-wrap: inherit; max-width: 450px;'>$dados->eservpdesc</td>";
                    $tableHtml  .= "<td>$dados->cservpsequ</td>";    
                }
                if(empty($dados->cservpsequ)){
                $tableHtml  .= "<td>$dados->eunidmsigl </td>";
                }
                $tableHtml  .= "</tr>";
                $ordinal++;
            }
            $tableHtml .="</TBody>";
            $tableHtml .="<tfoot>";
            $tableHtml .="<tr>";
            $tableHtml .="<td colspan='8'>";
            $tableHtml .='<button type="submit" value="Incluir Classe" name="incluirClasse" id="incluirClasse" class="botao" disabled style="float:right">Incluir Classe</button>';
            $tableHtml .="</td>";
            $tableHtml .="</tr>";
            $tableHtml .="</tfoot>";
        }else{
            $tableHtml .="<td colspan='8' style='border:none;'>";
            $tableHtml .='Pesquisa sem ocorrências.';
            $tableHtml .="</td>";
        }
        $tableHtml .="</table>";
        $tableHtml .="</form>";

        print_r(json_encode(array("status"=>200, "msm"=>$tableHtml)));
        exit;
    break;
    case "salvarDFD":
        if($_SESSION['Bloqueio']['status'] == true){
            $mensagem = $_SESSION['Bloqueio']['msm'];
            print_r(json_encode(array("status"=>true, "msm"=>$mensagem)));
            exit;
        }
        $eclamsdesc = $_SESSION['classeSelecionadada']->eclamsdesc;
        $cgrumscodi = $_SESSION['classeSelecionadada']->cgrumscodi;
        // Carrega todos os dados da tela 

        //Verifica qual o metodo de area requisitante e salva o correto
        if(empty($_POST['AreaReqCod']) && !empty($_POST["selectAreaReq"])){
            $corglicodi = $_POST["selectAreaReq"];
        }else if(!empty($_POST['AreaReqCod']) && empty($_POST["selectAreaReq"])){
            $corglicodi = $_POST["AreaReqCod"];
        }else{
            $corglicodi = -1; // Padrão de area vazia.
        }

        
        $dadosDFD = array(
            'rascunho'                =>   $_POST["rascunho"],                
            'selectAnoPCA'            =>   $_POST["selectAnoPCA"],                
            'selectAreaReq'           =>   $corglicodi,               
            'cclamscodi'              =>   $_POST["cclamscodi"],                  
            'ematepdesc'              =>   $_POST["ematepdesc"],                  
            'cnpjAreaReq'             =>   $_POST["cnpjAreaReq"],                 
            'descSuDemanda'           =>   $objPlanejamento->anti_injection(removeSimbolos(RetiraAcentos($_POST["descSuDemanda"]))),               
            'justContratacao'         =>   $objPlanejamento->anti_injection(removeSimbolos(RetiraAcentos($_POST["justContratacao"]))),             
            'estValorContratacao'     =>   $_POST["estValorContratacao"],         
            'tpProcContratacao'       =>   $_POST["tpProcContratacao"],           
            'dtPretendidaConc'        =>   $_POST["dtPretendidaConc"],            
            'grauPrioridade'          =>   $_POST["grauPrioridade"],              
            'justPriAlta'             =>   $objPlanejamento->anti_injection(removeSimbolos(RetiraAcentos($_POST["justPriAlta"]))),                 
            'vincOutroDFD'            =>   $_POST["vincOutroDFD"],               
            'contratCorp'             =>   $_POST["contratCorp"],                            
            'eclamsdesc'              =>   $eclamsdesc,            
            'cgrumscodi'              =>   $cgrumscodi,
            'cplsitcodi'              => 2,
        );
        
                      
        //Organiza dados dos Itens
        // for($i=0; $i<count($_SESSION['dadosDaPagina']['item']) ;$i++){
        //     $dadosDFD['cplitecodi'][] = $i+1;
        //     if($_SESSION['dadosDaPagina']['item'][0]['TipoGrupoBanco'] == "M"){
        //         $dadosDFD['cmatepsequ'][] = $_SESSION['dadosDaPagina']['item'][$i]['CodRedMaterialServicoBanco'];
        //     }else{
        //         $dadosDFD['cservpsequ'][] = $_SESSION['dadosDaPagina']['item'][$i]['CodRedMaterialServicoBanco'];
        //     }
        // }
        $i=0;
        foreach($_SESSION['dadosDaPagina']['item'] as $item){
            $dadosDFD['itens'][$i]->cplitecodi = $i+1;
            if($item['TipoGrupoBanco'] == "M"){
                $dadosDFD['itens'][$i]->cmatepsequ = $item['CodRedMaterialServicoBanco'];
            }else{
                $dadosDFD['itens'][$i]->cservpsequ = $item['CodRedMaterialServicoBanco'];
            }
            $i++;
        }
        
        // Envia os dados para Validação e reporta o erro para a tela parando a execução do procedimento caso falte algo
        $validação = $objPlanejamento->validarInclusaoDFD($dadosDFD);
        if($validação['erro'] == true){
            print_r(json_encode(array("status"=>404, "msm"=>$validação['informe'])));
            exit;
        }

        //ATENÇÃO AS BUSCAS ABAIXO DEPENDEM DE ALGO QUE NÂO PODE PASSAR SEM SER VALIDADO
        
        // Gera o novo Sequencial
        $dadosDFD['cpldfdsequ'] = $objPlanejamento->novoSequencialPlanejamento();

        //Busca o centro de custo para montar numero da DFD
        $centroCusto = $objPlanejamento->getCenCustoUsuario($dadosDFD['selectAreaReq']);


        // monta a primeira parte do numero da DFD
        $ccenpocorg = (strlen($centroCusto[0]->ccenpocorg) == 2) ? $centroCusto[0]->ccenpocorg : "0".$centroCusto[0]->ccenpocorg;
        $ccenpounid = (strlen($centroCusto[0]->ccenpounid) == 2) ? $centroCusto[0]->ccenpounid : "0".$centroCusto[0]->ccenpounid;

        $dadosDFD['numDFDParteUm'] = $ccenpocorg . $ccenpounid;
        //cria codigo do grupo
        $sequencial = $objPlanejamento->gerarSequencialVincularDFD();
        $codigoGrupo = $objPlanejamento->gerarCodigoGrupolVincularDFD();
        if($_SESSION['vincularDFD']){
            $dadosDFD['cplvincodi'] = $codigoGrupo;
            $dadosDFD['cplvinsequ'] = $sequencial;
        }

        //Envia os dados para serem tratados
        $dadosTratados = $objPlanejamento->trataDadosDFDParaInsert($dadosDFD);
        $execucao = $objPlanejamento->insereDFD($dadosTratados);

        if($_SESSION['vincularDFD']){
            $sequencial = $objPlanejamento->gerarSequencialVincularDFD();
            $codigoGrupo = $objPlanejamento->gerarCodigoGrupolVincularDFD();
            // $objPlanejamento->insertDadosVinculo($sequencial,$codigoGrupo,$dadosDFD['cpldfdsequ'],$_SESSION['_cusupocodi_']);
            foreach ($_SESSION['vincularDFD'] as $dadosVincular) {
                $sequencial++;
                $objPlanejamento->insertDadosVinculo($sequencial,$codigoGrupo,$dadosVincular->cpldfdsequ,$_SESSION['_cusupocodi_']);
            }
        }
        
        unset( $_SESSION['vincularDFD']);
        print_r(json_encode(array("status"=>200)));
        $_SESSION['MensagemFinal'] = "DFD número ".$dadosTratados['cpldfdnumf']." incluído com sucesso.";
        unset($_SESSION['dadosDaPagina']);
        unset($_POST["selectAreaReq"]);
        exit;
    break;
    case "LimparDFD":
        unset($_SESSION['classeSelecionadada']);
        unset($_SESSION['classeSelecionadada']);
        unset($_SESSION['dadosDaPagina']);
        unset($_POST);
    break;
    case "salvarRascunhoDFD":
        if($_SESSION['Bloqueio']['status'] == true){
            $mensagem = $_SESSION['Bloqueio']['msm'];
            print_r(json_encode(array("status"=>true, "msm"=>$mensagem)));
            exit;
        }
        $eclamsdesc = $_SESSION['classeSelecionadada']->eclamsdesc;
        $cgrumscodi = $_SESSION['classeSelecionadada']->cgrumscodi;

        //Verifica qual o metodo de area requisitante e salva o correto
        if(empty($_POST['AreaReqCod']) && !empty($_POST["selectAreaReq"])){
            $corglicodi = $_POST["selectAreaReq"];
        }else if(!empty($_POST['AreaReqCod']) && empty($_POST["selectAreaReq"])){
            $corglicodi = $_POST["AreaReqCod"];
        }else{
            $corglicodi = -1; // Padrão de area vazia.
        }

        // Carrega todos os dados da tela
        $dadosDFD = array(
            'rascunho'                =>   $_POST["rascunho"],
            'selectAnoPCA'            =>   $_POST["selectAnoPCA"],
            'selectAreaReq'           =>   $corglicodi,
            'cclamscodi'              =>   $_POST["cclamscodi"],
            'ematepdesc'              =>   $_POST["ematepdesc"],
            'cnpjAreaReq'             =>   $_POST["cnpjAreaReq"],
            'descSuDemanda'           =>   $objPlanejamento->anti_injection(removeSimbolos(RetiraAcentos($_POST["descSuDemanda"]))),
            'justContratacao'         =>   $objPlanejamento->anti_injection(removeSimbolos(RetiraAcentos($_POST["justContratacao"]))),
            'estValorContratacao'     =>   $_POST["estValorContratacao"],
            'tpProcContratacao'       =>   $_POST["tpProcContratacao"],
            'dtPretendidaConc'        =>   $_POST["dtPretendidaConc"],
            'grauPrioridade'          =>   $_POST["grauPrioridade"],
            'justPriAlta'             =>   $objPlanejamento->anti_injection(removeSimbolos(RetiraAcentos($_POST["justPriAlta"]))),
            'vincOutroDFD'            =>   $_POST["vincOutroDFD"],
            'contratCorp'             =>   $_POST["contratCorp"],
            'eclamsdesc'              =>   $eclamsdesc,
            'cgrumscodi'              =>   $cgrumscodi,
            'cplsitcodi'              => 2,
        );



        //Organiza dados dos Itens
        // for($i=0; $i<count($_SESSION['dadosDaPagina']['item']) ;$i++){
        //     $dadosDFD['cplitecodi'][] = $i+1;
        //     if($_SESSION['dadosDaPagina']['item'][0]['TipoGrupoBanco'] == "M"){
        //         $dadosDFD['cmatepsequ'][] = $_SESSION['dadosDaPagina']['item'][$i]['CodRedMaterialServicoBanco'];
        //     }else{
        //         $dadosDFD['cservpsequ'][] = $_SESSION['dadosDaPagina']['item'][$i]['CodRedMaterialServicoBanco'];
        //     }
        // }
        $i=0;
        foreach($_SESSION['dadosDaPagina']['item'] as $item){
            $dadosDFD['itens'][$i]->cplitecodi = $i+1;
            if($item['TipoGrupoBanco'] == "M"){
                $dadosDFD['itens'][$i]->cmatepsequ = $item['CodRedMaterialServicoBanco'];
            }else{
                $dadosDFD['itens'][$i]->cservpsequ = $item['CodRedMaterialServicoBanco'];
            }
            $i++;
        }


        // Envia os dados para Validação e reporta o erro para a tela parando a execução do procedimento caso falte algo
        $validação = $objPlanejamento->validarRascunhoDFD($dadosDFD);
        if($validação['erro'] == true){
            print_r(json_encode(array("status"=>404, "msm"=>$validação['informe'])));
            exit;
        }

        //ATENÇÃO AS BUSCAS ABAIXO DEPENDEM DE ALGO QUE NÂO PODE PASSAR SEM SER VALIDADO

        // Gera o novo Sequencial
        $dadosDFD['cpldfdsequ'] = $objPlanejamento->novoSequencialPlanejamento();

        //Busca o centro de custo para montar numero da DFD
        $centroCusto = $objPlanejamento->getCenCustoUsuario($dadosDFD['selectAreaReq']);

        // monta a primeira parte do numero da DFD
        $ccenpocorg = (strlen($centroCusto[0]->ccenpocorg) == 2) ? $centroCusto[0]->ccenpocorg : "0".$centroCusto[0]->ccenpocorg;
        $ccenpounid = (strlen($centroCusto[0]->ccenpounid) == 2) ? $centroCusto[0]->ccenpounid : "0".$centroCusto[0]->ccenpounid;

        $dadosDFD['numDFDParteUm'] = $ccenpocorg . $ccenpounid;
        //cria codigo do grupo
        $sequencial = $objPlanejamento->gerarSequencialVincularDFD();
        $codigoGrupo = $objPlanejamento->gerarCodigoGrupolVincularDFD();
        if($_SESSION['vincularDFD']){
            $dadosDFD['cplvincodi'] = $codigoGrupo;
            $dadosDFD['cplvinsequ'] = $sequencial;
        }
        //Envia os dados para serem tratados
        $dadosTratados = $objPlanejamento->trataDadosRascunhoDFDParaInsert($dadosDFD);
        $execucao = $objPlanejamento->insereDFD($dadosTratados);
        
        if($_SESSION['vincularDFD']){
            // $objPlanejamento->insertDadosVinculo($sequencial,$codigoGrupo,$dadosDFD['cpldfdsequ'],$_SESSION['_cusupocodi_']);
            foreach ($_SESSION['vincularDFD'] as $dadosVincular) {
                $sequencial++;
                $objPlanejamento->insertDadosVinculo($sequencial,$codigoGrupo,$dadosVincular->cpldfdsequ,$_SESSION['_cusupocodi_']);
            }
        }
        unset( $_SESSION['vincularDFD']);

        print_r(json_encode(array("status"=>200)));
        $_SESSION['MensagemFinal'] = "Rascunho salvo com sucesso. Para finalizar o preenchimento do DFD, acesse a funcionalidade “Manter DFD” e digite o número do DFD ".$dadosTratados['cpldfdnumf'].". ";
        unset($_POST["selectAreaReq"]);
        exit;


        break;

    case "removeItem":
        $posicoesRemover = $_POST['chkbxItem'];
        $contaArray = $_SESSION['dadosDaPagina']['item'];
        $reorganizaItens = array();
        //Remover da sessão itens selecionados
        for($i=0; $i < count($posicoesRemover); $i++){
            unset($_SESSION['dadosDaPagina']['item'][$posicoesRemover[$i]]);
        }
        for($j=0; $j<count($contaArray); $j++){
            if(!empty($_SESSION['dadosDaPagina']['item'][$j])){
                $reorganizaItens[] = $_SESSION['dadosDaPagina']['item'][$j];
            }
        }
        unset($_SESSION['dadosDaPagina']['item']);
        $_SESSION['dadosDaPagina']['item'] = $reorganizaItens;
        print_r(json_encode(array("status"=>true)));
    break;
    case "desvinculaDFDIncluir":
        foreach ($_POST['checkVincular'] as $sequencial){
                foreach($_SESSION['vincularDFD'] as $key=>$vinculo){
                    if($sequencial == $_SESSION['vincularDFD'][$key]->cpldfdsequ){
                        unset($_SESSION['vincularDFD'][$key]);
                }
            }
        }
        print_r(json_encode(array("status"=>true)));
    break;

    case "checaBloqueio":
        //Verifica se veio pelo select, pelo label quando é apenas um orgão ou pela session de alteração de orgão
        if(!is_null($_SESSION['AreaReqCod'])){
            $corglicodi =  $_SESSION['AreaReqCod'];
        }else{
            if(!empty($_SESSION['KeepOrgaoSelect'])){
                $corglicodi = $_SESSION['KeepOrgaoSelect'];
            }else{
                $corglicodi = $_POST['selectAreaReq'];
            }
        }
        $validaOrgao = $objPlanejamento->checaFundo($corglicodi);
        if($validaOrgao == true){
            if(!is_null($corglicodi) && !is_null($_POST['selectAnoPCA'])){
                $hoje = date('Y-m-d');
                if(!empty($_POST['selectAnoPCA'])){
                    $checaBloq = $objPlanejamento->checaBloqueio($_POST['selectAnoPCA']);
                    if(is_null($checaBloq->cplblosequ) || (is_null($checaBloq->dplblodini) || is_null($checaBloq->dplblodfim))){ //Se não houver bloqueio, ou datas no bloqueio;
                        $_SESSION['PeriodoBloqueado']['status'] == false;
                        print_r(json_encode(array("status"=>false)));
                        exit;
                    }
                    //Se houver bloqueio
                    if(!empty($corglicodi)){
                        $checaLib = $objPlanejamento->checaLiberacao($_POST['selectAnoPCA'], $corglicodi);
                        //Se estiver dentro do periodo liberado permite  
                        if((!is_null($checaLib->dpllibdini) && !is_null($checaLib->dpllibdfim)) && (strtotime($checaLib->dpllibdini) <= strtotime($hoje) && strtotime($checaLib->dpllibdfim) >= strtotime($hoje))){
                            $_SESSION['Bloqueio']['status'] = false;
                            print_r(json_encode(array("status"=>false)));
                            exit;
                        }
                        
                    }
                    if(strtotime($checaBloq->dplblodini) <= strtotime($hoje) && strtotime($checaBloq->dplblodfim) >= strtotime($hoje)){
                        $mensagem = "No atual período, não está autorizada a inclusão de DFD para o Ano do PCA selecionado.";
                        $_SESSION['Bloqueio']['status'] = true;
                        $_SESSION['Bloqueio']['msm'] = $mensagem;
                        print_r(json_encode(array("status"=>true, "msm"=>$mensagem)));
                        exit;
                    }else{
                        $_SESSION['Bloqueio']['status'] = false;
                        print_r(json_encode(array("status"=>false)));
                        exit;
                    }
                }
            }
        }else{
            $mensagem = "O campo área requisitante deve ser preenchido com uma secretaria ou entidade da administração indireta. Sendo assim, não é possível criar um DFD no qual a área requisitante seja um Fundo Municipal.";
            $_SESSION['Bloqueio']['status'] = true; // A mensagem deve ser enviada
            $_SESSION['Bloqueio']['msm'] = $mensagem;
            print_r(json_encode(array("status"=>true, "msm"=>$mensagem)));
            exit;
        }
        
    break;
}
?>

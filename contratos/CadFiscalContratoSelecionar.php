<?php
/*
# -------------------------------------------------------------------------
# Portal da Compras
# Programa: CadFiscalContratoSelecionar.php
# Autor:    Lucas Vicente
# Data:     29/06/2023
# Objetivo: CR #285486
--------------------------------------------------------------------------
*/

# Acesso ao arquivo de funções #

require_once("../funcoes.php");
require_once "ClassContratoPesquisar.php";

session_start();
Seguranca();
error_reporting(E_ALL ^ E_NOTICE);

$ObjContrato = new ContratoPesquisar();
$dadosTipoCompra = $ObjContrato->ListTipoCompra();
$dadosSituacaoDocumento = $ObjContrato->ListaSituacaoDoc(); 
$arrayTirar  = array('.',',','-','/');
$internet = $_GET['portalCompras'];
if (!@require_once dirname(__FILE__) . "/TemplateAppPadrao.php") {
	throw new Exception("Error Processing Request - TemplateAppPadrao.php", 1);
}
$tpl = new TemplateAppPadrao("CadFiscalContratoSelecionar.html","CadFiscalContratoSelecionar");
$tpl->BASEURL_CONTRATO = "http://".$_SERVER['HTTP_HOST'].str_replace('app','',dirname($_SERVER['REQUEST_URI']));
$tpl->BLOCK_RESULTADO_PESQUISA = 'style="display:none;"';
$tpl->BTNPESQUISAR = "Pesquisar";
$tpl->BTNPESQUISAR_VALOR = "Pesquisar";
if(!empty($dadosTipoCompra)){
	$arrayTodos = array();
	foreach($dadosTipoCompra as $tipoCompra){
		$arrayTodos[] = $tipoCompra->ctpcomcodi;
		if($_POST['origem'] == $tipoCompra->ctpcomcodi && empty($tpl->SELECTEDORI)){
			$tpl->SELECTEDORI = "selected";
		}else{
			$tpl->SELECTEDORI = "";
		}
		$tpl->VALUE_ORIGEM = $tipoCompra->ctpcomcodi;
		$tpl->NOME_ORIGEM  = $tipoCompra->etpcomnome;
		$tpl->block('BLOCK_ORIGEM');
	}
	
	if(empty($_POST['origem'])){
		$tpl->SELECTEDTDORI ="selected";
	}
	$tpl->VALUE_TODOS_ORIGEM = implode(',',$arrayTodos);
}

if(!empty($dadosSituacaoDocumento)){
	$arrayTodas = array();
	foreach($dadosSituacaoDocumento as $situacaoDocumento){
		if($_POST['situacao'] == ($situacaoDocumento->cfasedsequ."-".$situacaoDocumento->csitdcsequ) && empty($tpl->SELECTEDSIT)){
			$tpl->SELECTEDSIT ="selected";
		}else{
			$tpl->SELECTEDSIT = "";
		}
		$tpl->VALUE_SITUACAO = $situacaoDocumento->cfasedsequ."-".$situacaoDocumento->csitdcsequ;
		$tpl->NOME_SITUACAO  = $situacaoDocumento->esitdcdesc;
		$tpl->block('BLOCK_SITUACAO');
	}
	if(empty($_POST['situacao'])){
		$tpl->SELECTEDTDSIT ="selected";
	}
	$tpl->VALUE_TODAS_SITUACAO = "";
}

if($_POST['Botao'] == "Pesquisar"){
$tpl->BLOCK_RESULTADO_PESQUISA = 'style="display:block;"';
	$tpl->NUMEROCONTRATO =$_POST['numerocontratoano'];
	$tpl->RAZAO =$_POST['razao'];
	$tpl->OBJETO =$_POST['objeto'];
	$tpl->READONLY = "readonly";
	$tpl->BTNPESQUISAR = "Nova Pesquisa";
	$tpl->BTNPESQUISAR_VALOR = "Nova Pesquisa";
	$tpl->VALUE_ORGAO_LICITANTE = $_POST['Orgao'];
	if($_POST['tprazao'] == "iniciado"){
		$tpl->SELECTEDINI ="selected";
	}
	if($_POST['tprazao'] == "contendo"){
		$tpl->SELECTEDCON ="selected";
	}
	if($_POST['vigente'] == "vigente"){
		$tpl->VIGENTE ="checked";
	}
	if($_POST['vigente'] == "nvigente"){
		$tpl->NVIGENTE ="checked";
	}
	$tpl->VALOR_DATA_INI = $_POST['dataInicial'];
	$tpl->VALOR_DATA_FIM = $_POST['dataFinal'];
	
	$cnpj = str_replace($arrayTirar,'',$_POST['cnpj']);
	$Cpf  = str_replace($arrayTirar,'',$_POST['cpf']);
	$tudook = true;
	if(!$ObjContrato->validaCPF($Cpf) && $_POST['doc'] == "CPF" && !empty($_POST['cpf'])){
		$tpl->exibirMensagemFeedback("O CPF informado não é válido. Informe corretamente.", 1);
		
	}
	if(!$ObjContrato->valida_cnpj($cnpj) && $_POST['doc'] == "CNPJ" && !empty($_POST['cnpj'])){
		$tpl->exibirMensagemFeedback("O CNPJ informado não é válido. Informe corretamente.", 1);
		
	}
	$ArrayDados = array(
						'numerocontratoano' => $_POST['numerocontratoano'],
						'Orgao'             => $_POST['Orgao'],
						'tipop'             => $_POST['tprazao'],
						'razao'             => $razao = strtoupper($_POST['razao']),
						"origem"         	=> $_POST['origem'],
						'situacao'			=> $_POST['situacao'],
						'objeto'			=> $_POST['objeto'],
						'vigente'			=> $_POST['vigente'],
						'dataInicial'       => $_POST['dataInicial'],
						'dataFinal'       	=> $_POST['dataFinal']
					);
	

	$dadosTabela = $ObjContrato->Pesquisar($ArrayDados);
	$dadosOrgao = $ObjContrato->GetOrgaoById($ArrayDados,$internet);
	arsort($dadosTabela);
	// var_dump($dadosTabela);die;
	$show = false;
	if(!empty($dadosOrgao)){
		foreach($dadosOrgao as $infOrgao){
			$tpl->NOME_ORGAO_PESQUISA = $infOrgao->eorglidesc;
			$qtd=0;
			foreach($dadosTabela as $informacoesTable){	
				$fiscalContrato = $ObjContrato->getDocumentosFicaisEFical($informacoesTable->cdocpcsequ);
					if($informacoesTable->eorglidesc == $infOrgao->eorglidesc){
						$show = true;
						
						$tpl->CDOCPSEQU  = $informacoesTable->cdocpcsequ;
						$tpl->ECTRPCNUMF  = $informacoesTable->ectrpcnumf;
						$tpl->ECTRPCOBJE = wordwrap($informacoesTable->ectrpcobje, 30, "\n", true);

						for($i=0;count($fiscalContrato)>$i;$i++){
							$nomeFiscal[$i] = $fiscalContrato[$i]->fiscalnome;
							$cpfFiscal[$i] = FormataCPF($fiscalContrato[$i]->fiscalcpf);
							$dadosFiscal[$i] = $nomeFiscal[$i]. ' (CPF:'.$cpfFiscal[$i].')';

						}
						// var_dump($dadosFiscal);die;
						$dadosFiscal = array_unique($dadosFiscal);
						$dadosFiscal = implode(" , ",$dadosFiscal);
						
						$tpl->DADOS_FISCAL = $dadosFiscal;
						
						$tpl->block('CONTRATO_RESULTADO');
						$qtd++;									
					}

					$tpl->QTDCONTRATOS = $qtd;
				}

				if(!empty($show)){
					$tpl->block('BLOCK_ORGAO');
					$show=false;
				}
		}
	}else{
		$tpl->block('BLOCK_SEM_OCORRENCIA');
	}
	unset($informacoesTable);

}
$tpl->show();
?>

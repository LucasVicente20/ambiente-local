<?php
/*
# -------------------------------------------------------------------------
# Portal da Compras
# Programa: RelFiscalContratoExport.php
# Autor:    Lucas Vicente
# Data:     29/06/2023
# Objetivo: CR #285486
--------------------------------------------------------------------------
*/

require(dirname(__FILE__).'/export/ExportaCSV.php');
require(dirname(__FILE__).'/export/ExportaODS.php');
require(dirname(__FILE__).'/export/ExportaXLS.php');
require_once("../funcoes.php");
require_once "ClassContratoPesquisar.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $formatoExport = isset($_REQUEST ['FormatoExport']) ? $_REQUEST ['FormatoExport'] : 'csv';
    $ObjContrato = new ContratoPesquisar();
	$arrayTirar  = array('.',',','-','/');
    $internet = $_POST['internet'];
    $dadosTipoCompra = $ObjContrato->ListTipoCompra();
    if(!empty($dadosTipoCompra)){
		$arrayTodos = array();
		foreach($dadosTipoCompra as $tipoCompra){
			$arrayTodos[] = $tipoCompra->ctpcomcodi;
		}
		if(empty($_POST['origem'])){
            $_POST['origem'] = implode(',',$arrayTodos);
        }
	}
    $ArrayDados = array(
                        'numerocontratoano' => $_POST['numerocontratoano'],
                        'Orgao'             => $_POST['Orgao'],
                        'tipop'             => $_POST['tprazao'],
                        'razao'             => $razao = strtoupper($_POST['razao']),
                        "origem"         	=> $_POST['origem'],
                        'situacao'			=> $_POST['situacao'],
                        'objeto'			=> $_POST['objeto'],
                        'vigente'           => $_POST['vigente']
                    );

    $dadosTabela = $ObjContrato->Pesquisar($ArrayDados);
    $dadosOrgao = $ObjContrato->GetOrgaoById($ArrayDados);
    $linhas = array();

    foreach($dadosOrgao as $infOrgao){
        foreach($dadosTabela as $informacoesTable){
            
            if($informacoesTable->eorglidesc == $infOrgao->eorglidesc){
                $fiscalContrato = $ObjContrato->getDocumentosFicaisEFical($informacoesTable->cdocpcsequ);

                for($i=0;count($fiscalContrato)>$i;$i++){
                    $nomeFiscal[$i] = $fiscalContrato[$i]->fiscalnome;
                    $cpfFiscal[$i] = FormataCPF($fiscalContrato[$i]->fiscalcpf);
                    $dadosFiscal[$i] = $nomeFiscal[$i]. ' (CPF:'.$cpfFiscal[$i].')';

                }

                $dadosFiscal = array_unique($dadosFiscal);
                $dadosFiscal = implode(" , ",$dadosFiscal);
                
                $linhas[] = array(
                    'ectrpcnumf' => utf8_decode (RetiraAcentos($informacoesTable->ectrpcnumf)),
                    'ectrpcobje' => utf8_decode (wordwrap(RetiraAcentos($informacoesTable->ectrpcobje), 30, "\n", true)),
                    'dados_fiscal' => utf8_decode (RetiraAcentos($dadosFiscal))
                );

            }
        }
    }
    
    $ObjContrato->DesconectaBanco();
    
    $nomeArquivo = 'pcr_portal_compras_relacao_fiscal_contrato';
    $cabecalho = array('CONTRATO','OBJETO','DADOS DO FISCAL');

    $export = null;
    switch($formatoExport){
        case 'xls':
            $nomeArquivo.= '.xls';
            $export = new ExportaXLS($nomeArquivo, $cabecalho, $linhas);
        break;
        case 'ods':
            $nomeArquivo.= '.ods';
            $export = new ExportaODS($nomeArquivo, $cabecalho, $linhas);
        break;
        case 'txt':
                $nomeArquivo.= '.txt';
                $export = new ExportaCSV($nomeArquivo, '|', $cabecalho, $linhas);
        break;  
        case 'csv':
        default:
            $nomeArquivo.= '.csv';
            $export = new ExportaCSV($nomeArquivo, ';', $cabecalho, $linhas);
    }

    $export->download();
}

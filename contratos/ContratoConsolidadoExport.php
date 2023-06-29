<?php

/**
 * Portal da DGCO.
 *
 * PHP version 5
 *
 * LICENSE: This source file is subject to version 3.01 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_01.txt. If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @author Pitang Agile TI <contato@pitang.com>
 * @copyright 2014 EMPRESA MUNICIPAL DE INFORMÁTICA - EMPREL
 * @license http://www.php.net/license/3_01.txt PHP License 3.01
 *
 * @version GIT: EMPREL-SAD-PORTAL-COMPRAS-HOMOLOGACAO-BL-COD-013603-14-g410a130
 */


//CAIO MELQUIADES - CLASSES EXPORT
require(dirname(__FILE__).'/export/ExportaCSV.php');
require(dirname(__FILE__).'/export/ExportaODS.php');
require(dirname(__FILE__).'/export/ExportaXLS.php');



require_once("../funcoes.php");
require_once "ClassContratoPesquisar.php";
// require_once "ClassMedicaoPesquisar.php"; comentado por arquivo em desuso CR #238763


// Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    //CAIO MELQUIADES - VARIAVEL QUE CONTROLA O TIPO DE ARQUIVO A SER EXPORTADO
    $formatoExport = isset($_REQUEST ['FormatoExport']) ? $_REQUEST ['FormatoExport'] : 'csv';
    $ObjContrato = new ContratoPesquisar();
	// $ObjMedicao = new MedicaoPesquisar();  comentado por arquivo em desuso CR #238763
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
    $orgao = '';
   //$show = false;
    //if(!empty($dadosOrgao)){
     foreach($dadosOrgao as $infOrgao){
            foreach($dadosTabela as $informacoesTable){
                
                if($informacoesTable->eorglidesc == $infOrgao->eorglidesc){
                        $orgao = $infOrgao->eorglidesc;
                        $cpfCNPJ        = (!empty($informacoesTable->aforcrccgc))?$informacoesTable->aforcrccgc:$informacoesTable->aforcrccpf;
                        $SCC            = "";
                    
                        if(!empty($informacoesTable->ccenpocorg) && !empty($informacoesTable->ccenpounid) && !empty($informacoesTable->csolcocodi) && !empty($informacoesTable->asolcoanos)){
                            $SCC       = sprintf('%02s', $informacoesTable->ccenpocorg) . sprintf('%02s', $informacoesTable->ccenpounid) . '.' . sprintf('%04s', $informacoesTable->csolcocodi) . '/' . $informacoesTable->asolcoanos;
                        }
                        $TipoCompra    = $ObjContrato->GetTipoCompra($informacoesTable->ctpcomcodi);
                        //$cpfCNPJ        = (!empty($informacoesTable->aforcrccgc))?$informacoesTable->aforcrccgc:$informacoesTable->aforcrccpf;
                        $SituacaoContrato  = $ObjContrato->GetSituacaoContrato($informacoesTable->cfasedsequ,$informacoesTable->codsequsituacaodoc);
                       // $Vigencia = $informacoesTable->daditifivg;
                       /* $db = conexao();
                        $sqlorg ="select org.eorglidesc from sfpc.tborgaolicitante as org 
                        inner join sfpc.tbcontratosfpc as con on (con.corglicodi = org.corglicodi)
                        where con.cdocpcsequ = ".$informacoesTable->cdocpcsequ ." order by ";
                        $resultadoorg = executarSql($db, $sqlorg);
                        $resultadoorg->fetchInto($retorno, DB_FETCHMODE_OBJECT);*/
                        
                        $linhas[] = array(
                            'eorglidesc' => utf8_decode (RetiraAcentos($informacoesTable->eorglidesc)),
                            'ectrpcnumf' => utf8_decode (RetiraAcentos($informacoesTable->ectrpcnumf)),
                            'SCC'        => RetiraAcentos(utf8_decode ($SCC)),
                            'ectrpcobje' => utf8_decode (wordwrap(RetiraAcentos($informacoesTable->ectrpcobje), 30, "\n", true)),
                            'etpcomnome' => utf8_decode (RetiraAcentos($TipoCompra->etpcomnome)),
                            'nforcrrazs' => utf8_decode (RetiraAcentos($informacoesTable->nforcrrazs)),
                            'cpfCNPJ'    => utf8_decode ($ObjContrato->MascarasCPFCNPJ($cpfCNPJ)),
                            'esitdcdesc' => utf8_decode (RetiraAcentos($SituacaoContrato->esitdcdesc))
                            
                        );
   
               }
            }
       }
    //}
    $ObjContrato->DesconectaBanco();
    
    $nomeArquivo = 'pcr_portal_compras_contratos_consolidados';
    $cabecalho = array('ORGAO','CONTRATO','SCC','OBJETO','ORIGEM','FORNECEDOR','CNPJ/CPF','SITUACAO');

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
    //$tpl->show();
}

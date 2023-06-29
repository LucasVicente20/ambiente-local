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
 * @version GIT: v1.21.0-16-g300d38d
 *
 * ----------------------------------------------------------------------------
 * HISTÓRICO DE ALTERAÇÃO
 * ----------------------------------------------------------------------------
 * Alterado: Pitang Agile IT
 * Data: 13/07/2015 - CR95756
 * Link: http://redmine.recife.pe.gov.br/issues/95756
 * Versão:
 * ----------------------------------------------------------------------------
 * Alterado: Pitang Agile TI
 * Data:     17/07/2015
 * Objetivo: [CR redmine 76836] Licitações Concluídas.
 * -----------------------------------------------------------------------------
 * Alterado: Pitang Agile TI <contato@pitang.com>
 * Data: 18/09/2015 - CR redmine 95697 - Todas as Licitações
 * Link: http://redmine.recife.pe.gov.br/issues/95697
 * Versão: 1.27.1-1-g59aaca1
 * ----------------------------------------------------------------------------
 * Alterado: Pitang Agile TI - Ernesto Ferreira
 * Data:     19/07/2018
 * Objetivo: CR 96103 - [INTERNET] Licitações Concluídas e Todas as Licitações - 
 * Criar campo de pesquisa por período
 * ----------------------------------------------------------------------------
 * Alterado: Lucas Baracho
 * Data:     01/10/2018
 * Objetivo: Tarefa Redmine 204347
 * -------------------------------------------------------------------------
 * Alterado: Lucas Baracho
 * Data:     11/09/2018
 * Objetivo: Tarefa Redmine 203345
 * -----------------------------------------------------------------------------
 * Alterado: Caio Coutinho
 * Data:     10/10/2018
 * Objetivo: Tarefa Redmine 205100
 * -----------------------------------------------------------------------------
 * Alterado: Lucas Baracho
 * Data:     28/12/2018
 * Objetivo: Tarefa Redmine 208807
 * -----------------------------------------------------------------------------
 * Alterado: Lucas Baracho
 * Data:     29/04/2019
 * Objetivo: Tarefa Redmine 215448
 * -----------------------------------------------------------------------------
 * Alterado: João Madson
 * Data:     16/09/2019
 * Objetivo: Tarefa Redmine 223800
 * -----------------------------------------------------------------------------
 * Alterado: Osmar Celestino
 * Data:     16/04/2021
 * Objetivo: Tarefa Redmine 246882
 * -----------------------------------------------------------------------------
 * Alterado: Osmar Celestino
 * Data:     07/05/2021
 * Objetivo: Tarefa Redmine #247960
 * -----------------------------------------------------------------------------
 * -----------------------------------------------------------------------------
 * Alterado: Osmar Celestino
 * Data:     10/06/2021
 * Objetivo: CR# 247475
 * -----------------------------------------------------------------------------
 * Alterado: Lucas Vicente
 * Data:     04/11/2022
 * Objetivo: CR 270290 - Mover botão de Exportar para tela de Resultados
 * -----------------------------------------------------------------------------
 */
//CAIO MELQUIADES - CLASSES EXPORT
require(dirname(__FILE__).'/export/ExportaCSV.php');
require(dirname(__FILE__).'/export/ExportaXLS.php');
require(dirname(__FILE__).'/export/ExportaODS.php');

if (! @require_once dirname(__FILE__) . '/../licitacoes/funcoesLicitacoes.php') {
    throw new Exception('Error Processing Request - funcoesLicitacoes.php', 1);
}
session_start();

// class Pitang_App_ConsTodasLicitacoesResultado
// {

// public static function validarRequestMethod()
// {
// if ($_SERVER['REQUEST_METHOD'] != 'POST') {
// Pitang_GetUrl::run('ConsTodasLicitacoes.php', true);
// }
// }
// }

// Pitang_App_ConsTodasLicitacoesResultado::validarRequestMethod();

$ErroPrograma = __FILE__;


//###################################################
//  Função pegar ultima fase de Licitacao
//###################################################
function mascara_cnpjcpf($valor){
    $cnpjFormatado = strripos($valor, "-");
    if($cnpjFormatado == true){
        return $valor;
    }
    if(strlen($valor) == 14){
        $mascara = "##.###.###/####-##";
        for($i =0; $i <= strlen($mascara); $i++){
            if($mascara[$i] == "#"){
                if(isset($valor[$k])){
                $maskared .= $valor[$k++];
                }
            }else{
                $maskared .= $mascara[$i];
            }
        }
        return $maskared;
	}
}	
if(strlen($valor) == 11){
	$mascara = "###.###.###-##";
	for($i =0; $i <= strlen($mascara); $i++){
		if($mascara[$i] == "#"){
			if(isset($valor[$k])){
			$maskared .= $valor[$k++];
			}
		}else{
			$maskared .= $mascara[$i];
		}
	}
	// //($maskared);
	return $maskared;
}function ultimaFaseLicitacao($processo,$ano,$grupo,$comissao,$orgao,$db) {
	
	$sql .= " select ";
	$sql .= " fl.cfasescodi as ultimafase ";
	$sql .= " from ";
	$sql .= " sfpc.tbfaselicitacao fl, sfpc.tbfases fa	 ";
	$sql .= " where ";
	$sql .= " fl.cfasescodi = fa.cfasescodi ";
	$sql .= " and fl.clicpoproc = $processo ";
	$sql .= " and fl.alicpoanop = $ano ";
	$sql .= " and fl.cgrempcodi = $grupo ";
	$sql .= " and fl.ccomlicodi = $comissao " ;
	$sql .= " and fl.corglicodi = $orgao ";

	$sql .= " and fl.tfaseldata = ( ";

	$sql .= " 	select ";
				
	$sql .= " 	max( a.tfaseldata ) as ultimafase ";
		
	$sql .= " 	from ";
	$sql .= " 		sfpc.tbfaselicitacao a ";
	$sql .= " 	where ";
	$sql .= " 		a.clicpoproc = $processo ";
	$sql .= " 		and a.alicpoanop = $ano ";
	$sql .= " 		and a.cgrempcodi = $grupo ";
	$sql .= " 		and a.ccomlicodi = $comissao " ;
	$sql .= " 		and a.corglicodi = $orgao ";

	$sql .= " 	) ";


    $sql .= " 	order by fa.afasesorde desc limit 1  ";
	
	
 	$result	= executarTransacao($db, $sql);
	$row	= $result->fetchRow(DB_FETCHMODE_OBJECT);
	return $row->ultimafase;

}

function numeroContrato($grupo,$comissao,$processo,$ano,$orgao,$db) {
    $db = Conexao();
    $sql .= " select ";
    $sql .= " ECTRPCNUMF as numerocont";
    $sql .= " from ";
    $sql .= " SFPC.TBCONTRATOSFPC CONT,";
    $sql .= " SFPC.TBSOLICITACAOLICITACAOPORTAL LIC ";
    $sql .= " where ";
    $sql .= " cont.csolcosequ = lic.csolcosequ ";
    $sql .= " and lic.cgrempcodi = $grupo ";
    $sql .= " and lic.ccomlicodi = $comissao " ;
    $sql .= " and lic.clicpoproc = $processo ";
    $sql .= " and lic.alicpoanop = $ano ";
    $sql .= " and lic.corglicodi = $orgao ";   
     $result	= executarTransacao($db, $sql);
    $row	= $result->fetchRow(DB_FETCHMODE_OBJECT);
    return $row->numerocont;
 }
//fução de numero de fornecedores
function fornecedorVencedor($grupo,$comissao,$processo,$ano,$orgao,$db){
    $db = Conexao();
    $sql .= " SELECT DISTINCT ITEML.AFORCRSEQU, FORN.AFORCRCCGC as cnpjfornecedor, FORN.aforcrccpf as cpffornecedor, FORN.NFORCRRAZS as razaosocial";
    $sql .= " FROM ";
    $sql .= " SFPC.tbfaselicitacao A ";
    $sql .= " INNER JOIN SFPC.TBITEMLICITACAOPORTAL ITEML ";
    $sql .= " ON A.cgrempcodi = iteml.cgrempcodi ";
    $sql .= " AND A.CCOMLICODI = ITEML.CCOMLICODI ";
    $sql .= " AND A.CLICPOPROC = ITEML.CLICPOPROC ";
    $sql .= " AND A.ALICPOANOP = ITEML.ALICPOANOP ";
    $sql .= " AND A.CORGLICODI = ITEML.CORGLICODI ";
    $sql .= " INNER JOIN SFPC.TBFORNECEDORCREDENCIADO FORN ";
    $sql .= " ON ITEML.AFORCRSEQU = FORN.AFORCRSEQU ";
    $sql .= " WHERE ";
    $sql .= " iteml.cgrempcodi = $grupo ";
    $sql .= " AND iteml.ccomlicodi = $comissao ";
    $sql .= " AND iteml.clicpoproc = $processo ";
    $sql .= " AND iteml.alicpoanop = $ano ";
    $sql .= " AND iteml.corglicodi = $orgao ";
    $result = executarTransacao($db, $sql);
    //print_r($sql);
    $row = $result->fetchRow(DB_FETCHMODE_OBJECT);
    ////($row);
    $cnpj = $row->cnpjfornecedor;
    $cpf = $row->cpffornecedor;
    $cpfcnpj = '';

    if(empty($cnpj)==true){
        $cpfcnpj = $cpf;
    }
    else{
        $cpfcnpj = $cnpj;
    }
    return $cpfcnpj;
}
function razaoSocial($grupo,$comissao,$processo,$ano,$orgao,$db){
    $db = Conexao();
    $sql .= " SELECT DISTINCT ITEML.AFORCRSEQU, FORN.NFORCRRAZS as razaosocial";
    $sql .= " FROM ";
    $sql .= " SFPC.tbfaselicitacao A ";
    $sql .= " INNER JOIN SFPC.TBITEMLICITACAOPORTAL ITEML ";
    $sql .= " ON A.cgrempcodi = iteml.cgrempcodi ";
    $sql .= " AND A.CCOMLICODI = ITEML.CCOMLICODI ";
    $sql .= " AND A.CLICPOPROC = ITEML.CLICPOPROC ";
    $sql .= " AND A.ALICPOANOP = ITEML.ALICPOANOP ";
    $sql .= " AND A.CORGLICODI = ITEML.CORGLICODI ";
    $sql .= " INNER JOIN SFPC.TBFORNECEDORCREDENCIADO FORN ";
    $sql .= " ON ITEML.AFORCRSEQU = FORN.AFORCRSEQU ";
    $sql .= " WHERE ";
    $sql .= " iteml.cgrempcodi = $grupo ";
    $sql .= " AND iteml.ccomlicodi = $comissao ";
    $sql .= " AND iteml.clicpoproc = $processo ";
    $sql .= " AND iteml.alicpoanop = $ano ";
    $sql .= " AND iteml.corglicodi = $orgao ";
    $result = executarTransacao($db, $sql);
    //print_r($sql);
    $row = $result->fetchRow(DB_FETCHMODE_OBJECT);
    ////($row);
    $razaoS = $row->razaosocial;
    return $razaoS;
}
 // Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $carregaProcesso = $_POST['carregaProcesso'];
    $Botao = ($_POST['Botao'] == 'carregaProcesso') ? $_POST['Botao'] : 'Pesquisar';
    
    if ($Botao == 'carregaProcesso') {
        // list ($_SESSION['GrupoCodigoDet'], $_SESSION['ProcessoDet'], $_SESSION['ProcessoAnoDet'], $_SESSION['ComissaoCodigoDet'], $_SESSION['OrgaoLicitanteCodigoDet']) = explode('-', $carregaProcesso);
        $data = explode('-', $carregaProcesso);
        $_SESSION['GrupoCodigoDet'] = $data[0];
        $_SESSION['ProcessoDet'] = $data[1];
        $_SESSION['ProcessoAnoDet'] = $data[2];
        $_SESSION['ComissaoCodigoDet'] = $data[3];
        $_SESSION['OrgaoLicitanteCodigoDet'] = $data[4];
        
        Pitang_GetUrl::run('ConsTodasLicitacoesDetalhes.php', true);
    }
    $Critica = $_POST['Critica'];
    
    $Objeto = strtoupper2($_POST['Objeto']);
    $OrgaoLicitanteCodigo = $_POST['OrgaoLicitanteCodigo'];
    $ComissaoCodigo = $_POST['ComissaoCodigo'];
    $ModalidadeCodigo = $_POST['ModalidadeCodigo'];
    $TipoItemLicitacao = $_POST['TipoItemLicitacao'];
    $Item = $_POST['ItemInput'];
    $LicitacaoAno = $_POST['LicitacaoAno'];
    $Pesquisa = $_POST['pesqPeriodo'];
    $datasValidas = false;



    if($Pesquisa == 1){
        
        $dataIniFormatado = $_POST['dataInicio'];
        $dataFimFormatado = $_POST['dataFim'];
        $arrDataIni = explode("/",$_POST['dataInicio']);
        $arrDataFim = explode("/",$_POST['dataFim']);
        $dataIni = $arrDataIni[2].'-'.$arrDataIni[1].'-'.$arrDataIni[0];
        $dataFim = $arrDataFim[2].'-'.$arrDataFim[1].'-'.$arrDataFim[0];

        $dataIniValida = checkdate((int) $arrDataIni[1],(int) $arrDataIni[0],(int) $arrDataIni[2]);
        $dataFimValida = checkdate((int)$arrDataFim[1], (int)$arrDataFim[0],(int) $arrDataFim[2]);
       
        if( !$dataIniValida and !$dataFimValida ){
    
            $_SESSION['Mensagem'] = 'As datas do período tem que ser válidas.';
            $_SESSION['Mens'] = 1;
            $_SESSION['Tipo'] = 1;
            
            Pitang_GetUrl::run('ConsTodasLicitacoes.php', true);
        }
        
        if( !$dataIniValida || !$dataFimValida ){
            $_SESSION['Mensagem'] = 'As datas do período tem que ser preenchidas.';
            $_SESSION['Mens'] = 1;
            $_SESSION['Tipo'] = 1;
           
            header('Location: ConsTodasLicitacoes.php');
            exit();
        }
    }else{
        $dataIni = '';
        $dataFim = '';
    }

    $_SESSION['Pesquisar'] = false;
    if ($Botao == 'Pesquisar') {
        $_SESSION['Pesquisar'] = true;
    }
    
    if ($Botao == 'Pesquisar') {
        $_SESSION['Objeto'] = $Objeto;
        $_SESSION['OrgaoLicitanteCodigo'] = $OrgaoLicitanteCodigo;
        $_SESSION['ComissaoCodigo'] = $ComissaoCodigo;
        $_SESSION['ModalidadeCodigo'] = $ModalidadeCodigo;
        $_SESSION['TipoItemLicitacao'] = $TipoItemLicitacao;
        $_SESSION['Item'] = $Item;
        $_SESSION['botao'] = $Botao;
        $_SESSION['LicitacaoAno'] = $LicitacaoAno;
        $_SESSION['Pesquisa'] = $Pesquisa;
        $_SESSION['dataInicio'] = $dataIniFormatado;
        $_SESSION['dataFim'] = $dataFimFormatado;
    }

    //CAIO MELQUIADES - VARIAVEL QUE CONTROLA O TIPO DE ARQUIVO A SER EXPORTADO
    $formatoExport = isset($_REQUEST ['FormatoExport']) ? $_REQUEST ['FormatoExport'] : 'csv';
}

$arraySituacoesConcluidas = getIdFasesConcluidas(); // Array com os ids das situações concluídas
$arraySituacoesEmAndamento = getIdFasesEmAndamento(); // Array com os ids das situações em andamento

// if ($_SESSION['botao'] == 'carregaProcesso' || $_SESSION['botao'] == 'Pesquisar') {

$Objeto = $_SESSION['Objeto'];
$OrgaoLicitanteCodigo = $_SESSION['OrgaoLicitanteCodigo'];
$ComissaoCodigo = $_SESSION['ComissaoCodigo'];
$ModalidadeCodigo = $_SESSION['ModalidadeCodigo'];
$TipoItemLicitacao = $_SESSION['TipoItemLicitacao'];
$Item = $_SESSION['Item'];
$Botao = $_SESSION['botao'];

// Prepara exibição dos filtros
$fragmentoSelect = ' SELECT LP.CLICPOPROC ';
$fragmentoFrom = ' FROM SFPC.TBLICITACAOPORTAL LP ';
$fragmentoWhere = ' WHERE 1 = 1 ';
$queryExiste = false;

if (empty($OrgaoLicitanteCodigo) === false) {
    $fragmentoSelect .= ' , OL.EORGLIDESC ';
    $fragmentoFrom .= ' , SFPC.TBORGAOLICITANTE OL ';
    $fragmentoWhere .= " AND OL.CORGLICODI = $OrgaoLicitanteCodigo ";
    $queryExiste = true;
}

if (empty($ComissaoCodigo) === false) {
    $fragmentoSelect .= ' , CL.ECOMLIDESC ';
    $fragmentoFrom .= ' , SFPC.TBCOMISSAOLICITACAO CL ';
    $fragmentoWhere .= " AND CL.CCOMLICODI = $ComissaoCodigo ";
    $queryExiste = true;
}

if (empty($ModalidadeCodigo) === false) {
    $fragmentoSelect .= ' , ML.EMODLIDESC ';
    $fragmentoFrom .= ' , SFPC.TBMODALIDADELICITACAO ML ';
    $fragmentoWhere .= " AND ML.CMODLICODI = $ModalidadeCodigo ";
    $queryExiste = true;
}

$descricaoOrgaoLicitante = 'Todos';
$descricaoComissao = 'Todas';
$descricaoModalidade = 'Todas';

// Verifica se é necessário executar consulta ao banco
if ($queryExiste) {
    $sql = $fragmentoSelect . $fragmentoFrom . $fragmentoWhere . ' LIMIT 1';
    $db = Conexao();
    $result = $db->query($sql);
    
    if (db::isError($result)) {
        ExibeErroBD(__FILE__ . "\nLinha: " . __LINE__ . "\nSql: $sql");
    }
    
    while ($linha = $result->fetchRow(DB_FETCHMODE_ASSOC)) {
        $descricaoOrgaoLicitante = (isset($linha['eorglidesc'])) ? $linha['eorglidesc'] : 'Todos';
        $descricaoComissao = (isset($linha['ecomlidesc'])) ? $linha['ecomlidesc'] : 'Todas';
        $descricaoModalidade = (isset($linha['emodlidesc'])) ? $linha['emodlidesc'] : 'Todas';
    }
    
    $db->disconnect();
}

// Recupera o tipo de item
$descricaoTipoItem = 'Todos';
if (empty($TipoItemLicitacao) === false) {
    $descricaoTipoItem = ($TipoItemLicitacao == 'Material') ? 'Material' : 'Serviço';
}

if($_SESSION['pesqLabel'] == 'Ano'){
    // Pesquisar pelo Ano
    $pesqLabel = $_SESSION['pesqLabel'];
    $pesqValue = ($_SESSION['pesqValue']!= '') ? $_SESSION['pesqValue'] : 'Todos';

}else{
    // Pesquisar pelo período
    $pesqLabel = 'Período';
    $pesqValue = $_SESSION['pesqValue'];
}

$filtroPesquisa = array(
    'Objeto' => ($_SESSION['Export']['Objeto'] == '') ? 'Todos' : $_SESSION['Export']['Objeto'],
    'Órgão Licitante' => $_SESSION['Export']['Órgão Licitante'],
    'Comissão' => $_SESSION['Export']['Comissão'],
    'Modalidade' => $_SESSION['Export']['Modalidade'],
    $pesqLabel => $pesqValue,
    /*'Tratamento diferenciado ME/EPP/MEI' => (isset($tipoEmpresa)) ? 'Marcado' : 'Desmarcado',*/
    'Item' => $_SESSION['Export']['Item'],
    'Descrição item' => ($Item != '') ? 'Todas' : $_SESSION['Export']['Descrição item'],
    'ModalidadeCodigo' => $_SESSION['Export']['ModalidadeCodigo'],
    'ComissaoCodigo' => $_SESSION['Export']['ComissaoCodigo'],
    'OrgaoLicitanteCodigo' => $_SESSION['Export']['OrgaoLicitanteCodigo'],
    'TipoItemLicitacao'=> $_SESSION['Export']['TipoItemLicitacao'],
    'dataInicio' => $_SESSION['Export']['dataInicio'],
    'dataFim' => $_SESSION['Export']['dataFim'],

);
// var_dump($filtroPesquisa[]);die;
    if($filtroPesquisa['dataInicio']!=NULL && $filtroPesquisa['dataFim']!=NULL){
        $filtroPesquisa['dataInicio'] = date('Y-m-d', strtotime(str_replace('/', '-', $filtroPesquisa['dataInicio']))); 
        $filtroPesquisa['dataFim'] = date('Y-m-d', strtotime(str_replace('/', '-', $filtroPesquisa['dataFim']))); 
        
    }

// Redireciona dados para ConsLicitacoesAndamento.php se Houve Erro #
if (($Item == '') and ($TipoItemLicitacao != '') ) {
    $_SESSION['Mensagem'] = 'Informe: Descrição do item';
    $_SESSION['Mens'] = 1;
    $_SESSION['Tipo'] = 2;
    $_SESSION['Objeto'] = $Objeto;
    $_SESSION['OrgaoLicitanteCodigo'] = $OrgaoLicitanteCodigo;
    $_SESSION['ComissaoCodigo'] = $ComissaoCodigo;
    $_SESSION['ModalidadeCodigo'] = $ModalidadeCodigo;
    $_SESSION['RetornoPesquisa'] = 1;
    $_SESSION['TipoItemLicitacao'] = $TipoItemLicitacao;
    $_SESSION['Item'] = $Item;
    $_SESSION['Pesquisa'] = $Pesquisa;
    $_SESSION['dataInicio'] = $dataIniFormatado;
    $_SESSION['dataFim'] = $dataFimFormatado;
    header('Location: ConsTodasLicitacoes.php');
    exit();
}

// Redireciona dados para ConsAcompPesquisaGeral.php #
if ($Botao == 'Pesquisa') {
    $_SESSION['RetornoPesquisa'] = null;
    
    header('Location: ConsTodasLicitacoes.php');
    exit();
}

$Mens = 0;

if ($Mens == 0) {
    $db = Conexao();
    $Data = date('Y-m-d');
    $novaSql = '';
    
    // SELECT
    $novaSql .= ' SELECT ';
    $novaSql .= ' DISTINCT A.CLICPOPROC, B.EORGLIDESC, CD.ECOMLIDESC, e.EFASESDESC, GE.EGREMPDESC, ML.EMODLIDESC, ';
    $novaSql .= ' A.ALICPOANOP, A.CLICPOCODL, A.ALICPOANOL, A.XLICPOOBJE, ';
    $novaSql .= ' A.TLICPODHAB, A.CGREMPCODI, A.CCOMLICODI, A.CORGLICODI, e.CFASESCODI, D.TFASELDATA ';
    
    // FROM
    $novaSql .= ' FROM ';
    
    if ($filtroPesquisa['TipoItemLicitacao'] == 'Material') {
        $novaSql .= ' SFPC.tbitemlicitacaoportal F, SFPC.tbmaterialportal G, ';
    }
    
    if ($filtroPesquisa['TipoItemLicitacao'] == 'Servico') {
        $novaSql .= ' SFPC.tbitemlicitacaoportal F, SFPC.tbservicoportal G, ';
    }
    
    $novaSql .= ' SFPC.TBFASES e,
	                        (
	                            SELECT
	                                l.CLICPOPROC AS Proc ,
	                                l.ALICPOANOP AS Ano ,
	                                l.CGREMPCODI AS Grupo ,
	                                l.CCOMLICODI AS Comis ,
	                                l.CORGLICODI AS Orgao ,
	                                MAX(o.AFASESORDE) AS Maior
	                            FROM
	                                SFPC.TBFASELICITACAO l ,
	                                SFPC.TBFASES o
	                            WHERE
	                                l.CFASESCODI = o.CFASESCODI
	                            GROUP BY
	                                l.CLICPOPROC ,
	                                l.ALICPOANOP ,
	                                l.CGREMPCODI ,
	                                l.CCOMLICODI ,
	                                l.CORGLICODI
	                        ) AS maiorordem, ';
    
    $novaSql .= ' SFPC.TBFASELICITACAO D, ';
    $novaSql .= ' SFPC.TBLICITACAOPORTAL A ';
    
    // JOIN Comissão licitação
    $novaSql .= ' INNER JOIN SFPC.TBCOMISSAOLICITACAO CD ';
    $novaSql .= ' ON CD.CCOMLICODI = A.CCOMLICODI ';
    
    // JOIN Grupo
    //$novaSql .= ' INNER JOIN SFPC.TBGRUPOORGAO GO ';
    //$novaSql .= ' ON GO.CGREMPCODI = A.CGREMPCODI AND GO.CORGLICODI = A.CORGLICODI ';
    $novaSql .= ' INNER JOIN SFPC.TBGRUPOEMPRESA GE ';
    $novaSql .= ' ON GE.CGREMPCODI = A.CGREMPCODI ';
    
    // JOIN Modalidade
    $novaSql .= ' INNER JOIN SFPC.TBMODALIDADELICITACAO ML ';
    $novaSql .= ' ON ML.CMODLICODI = A.CMODLICODI ';
    
    // JOIN Órgão licitante
    $novaSql .= ' INNER JOIN SFPC.TBORGAOLICITANTE B ';
    $novaSql .= ' ON B.CORGLICODI = A.CORGLICODI ';
    
    if ($filtroPesquisa['OrgaoLicitanteCodigo'] != '') {
        $OrgaoLicitanteCodigo = $filtroPesquisa['OrgaoLicitanteCodigo'];
        $novaSql .= " AND A.CORGLICODI = '$OrgaoLicitanteCodigo' ";
    }
    
    // WHERE
    $novaSql .= ' WHERE 1 = 1 ';
    $novaSql .= '     AND a.CLICPOPROC = maiorordem.Proc
	                  AND a.ALICPOANOP = maiorordem.Ano
	                  AND a.CGREMPCODI = maiorordem.Grupo
	                  AND a.CCOMLICODI = maiorordem.Comis
	                  AND a.CORGLICODI = maiorordem.Orgao
	                  AND e.AFASESORDE = maiorordem.Maior
	                  AND a.clicpoproc = d.clicpoproc
	                  AND a.alicpoanop = d.alicpoanop
	    	          AND a.cgrempcodi = d.cgrempcodi
	                  AND a.ccomlicodi = d.ccomlicodi
	                  AND a.corglicodi = d.corglicodi
	                  AND d.CFASESCODI = e.CFASESCODI
	                  AND a.CCOMLICODI NOT IN(41) ';
    
    // Objeto
    if ($filtroPesquisa['Objeto'] != '' && $filtroPesquisa['Objeto']!='Todos') {
        $Objeto = $filtroPesquisa['Objeto'];
        $novaSql .= " AND (A.XLICPOOBJE ILIKE '%$Objeto%' OR A.XLICPOOBJE ILIKE '%".RetiraAcentos($Objeto)."%')";
    }
    
    // Comissão
    if ($filtroPesquisa['ComissaoCodigo'] != '') {
        $ComissaoCodigo = $filtroPesquisa['ComissaoCodigo'];
        $novaSql .= " AND A.CCOMLICODI = $ComissaoCodigo ";
    }
    
    // Modalidade
    if ($filtroPesquisa['ModalidadeCodigo'] != '') {
        $ModalidadeCodigo = $filtroPesquisa['ModalidadeCodigo'];
        $novaSql .= " AND A.CMODLICODI = $ModalidadeCodigo ";
    }
 
    // Período
    if ($filtroPesquisa['dataInicio'] != '') {
        $dataIni = $filtroPesquisa['dataInicio'];
        $dataFim = $filtroPesquisa['dataFim'];
        $novaSql .= " AND D.TFASELDATA between '$dataIni' and '$dataFim' ";
    }else{
        // Ano
        if ($pesqValue != '') {
            $novaSql .= " AND EXTRACT(YEAR FROM D.TFASELDATA) = '$pesqValue' ";
        }
    }

    // Item
    if (($filtroPesquisa['TipoItemLicitacao'] == 'Material') or ($filtroPesquisa['TipoItemLicitacao'] == 'Servico')) {
        $novaSql .= ' AND A.CLICPOPROC = F.CLICPOPROC ';
        $novaSql .= ' AND A.ALICPOANOP = F.ALICPOANOP ';
        $novaSql .= ' AND A.CGREMPCODI = F.CGREMPCODI ';
        $novaSql .= ' AND A.CCOMLICODI = F.CCOMLICODI ';
        $novaSql .= ' AND A.CORGLICODI = F.CORGLICODI ';
    }
    
    // Descrição do item material
    if ($filtroPesquisa['TipoItemLicitacao'] == 'Material') {
        $Item = $filtroPesquisa['Descrição item'];
        $novaSql .= ' AND F.CMATEPSEQU = G.CMATEPSEQU ';
        $novaSql .= " AND (G.EMATEPDESC ILIKE '%$Item%') ";
    }
    
    // Descrição do item serviço
    if ($filtroPesquisa['TipoItemLicitacao'] == 'Servico') {
        $Item = $filtroPesquisa['Descrição item'];
        $novaSql .= ' AND F.CSERVPSEQU = G.CSERVPSEQU ';
        $novaSql .= " AND (G.ESERVPDESC ILIKE '%$Item%') ";
    }
    
    // Situação
    $idTodasSituacoes = array_merge($arraySituacoesConcluidas, $arraySituacoesEmAndamento);
    $strIdTodasSituacoes = implode(', ', $idTodasSituacoes);
    $novaSql .= " AND ((D.CFASESCODI IN ($strIdTodasSituacoes) AND A.TLICPODHAB < clock_timestamp()) OR (D.CFASESCODI = 24))";
    
    // ORDER BY
    $novaSql .= ' ORDER BY GE.EGREMPDESC, B.EORGLIDESC, CD.ECOMLIDESC, A.ALICPOANOP DESC, A.CLICPOPROC DESC';
    // var_dump($novaSql);die;

    $result = $db->query($novaSql);

    //print_r($novaSql);exit;
    
    if (db::isError($result)) {
        ExibeErroBD(__FILE__ . "\nLinha: " . __LINE__ . "\nSql: $novaSql");
    }
    
    if ($result->numRows() <= 0) {
        $_SESSION['Mensagem'] = 'Nenhuma ocorrência foi encontrada.';
        $_SESSION['Mens'] = 1;
        $_SESSION['Tipo'] = 1;
        
       header('Location: ConsTodasLicitacoes.php');
        exit();
    }
    
    $cont = 0;
    $GrupoDescricao = '';
    $ModalidadeDescricao = '';
    $ComissaoDescricao = '';
    $OrgaoDescricao = '';
    
    while ($cols = $result->fetchRow()) {
        // Query para recuperar todas as fases da licitação
        $sqlFases = "SELECT F.CFASESCODI FROM SFPC.TBFASELICITACAO F WHERE F.CLICPOPROC = $cols[0] AND F.ALICPOANOP = $cols[6]
	                     AND F.CGREMPCODI = $cols[11] AND F.CCOMLICODI = $cols[12] AND F.CORGLICODI = $cols[13]";
        
        $fasesLicitacao = $db->getCol($sqlFases);
        if (db::isError($fasesLicitacao)) {
            ExibeErroBD(__FILE__ . "\nLinha: " . __LINE__ . "\nSql: $sqlFases");
        }
        
        $licitacaoPublicada = in_array(2, $fasesLicitacao);
        //$dataAberturaMenorQueAtual = strtotime($cols[10]) < strtotime(date('Y-m-d H:i:s'));
        
        // Verifica se a licitação está publicada ou se a data de abertura é menor que a data atual
        if ($licitacaoPublicada /*&& $dataAberturaMenorQueAtual*/) {
            ++ $cont;
            $dados[$cont - 1] = $cols[4];
            $dados[$cont - 1] .= $SimboloConcatenacaoArray . $cols[5];
            $dados[$cont - 1] .= $SimboloConcatenacaoArray . $cols[2];
            $dados[$cont - 1] .= $SimboloConcatenacaoArray . $cols[0];
            $dados[$cont - 1] .= $SimboloConcatenacaoArray . $cols[6];
            $dados[$cont - 1] .= $SimboloConcatenacaoArray . $cols[7];
            $dados[$cont - 1] .= $SimboloConcatenacaoArray . $cols[8];
            $dados[$cont - 1] .= $SimboloConcatenacaoArray . $cols[9];
            $dados[$cont - 1] .= $SimboloConcatenacaoArray . $cols[10];
            $dados[$cont - 1] .= $SimboloConcatenacaoArray . $cols[1];
            $dados[$cont - 1] .= $SimboloConcatenacaoArray . $cols[11];
            $dados[$cont - 1] .= $SimboloConcatenacaoArray . $cols[12];
            $dados[$cont - 1] .= $SimboloConcatenacaoArray . $cols[13];
            $dados[$cont - 1] .= $SimboloConcatenacaoArray . $cols[14];
            $dados[$cont - 1] .= $SimboloConcatenacaoArray . $cols[3];
        }
    }
    
    $mudou = 0;


    $cabecalho = array('OBJETO', 'DATA ABERTURA', 'HORA ABERTURA', 'FASE', 'PROCESSO', 'MODALIDADE', 'LICITACAO','Nº CONTRATO','FORNECEDOR VENCEDOR');
    $linhas = array();

    for ($Row = 0; $Row < $cont; ++ $Row) {
        $Linha = explode($SimboloConcatenacaoArray, $dados[$Row]);

        $sqlSolicitacoesC  = " SELECT  csolcosequ ";
        $sqlSolicitacoesC .= " FROM SFPC.TBSOLICITACAOLICITACAOPORTAL SOL WHERE SOL.CLICPOPROC = $Linha[3] AND SOL.ALICPOANOP =" . $Linha[4];
        $sqlSolicitacoesC .= " AND SOL.CCOMLICODI = $Linha[11] AND SOL.cgrempcodi =" . $Linha[10];
        $resultSolic = executarTransacao($db, $sqlSolicitacoesC);
        if (db::isError($resultSolic)) {
            ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sqlSolicitacoesC");
        }
        $Solicitacao = '';
        $int = 0;
        $dispensa = '';
    
        while ($llinha = $resultSolic->fetchRow()) {
            $solicitacaosql  = " SELECT  fsolcocont ";
            $solicitacaosql .= " FROM SFPC.TBSOLICITACAOCOMPRA SC WHERE SC.CSOLCOSEQU = " .$llinha[0];
            $resultSolic = executarTransacao($db,$solicitacaosql);
            $dispensaContrato = $resultSolic->fetchRow();
            $geraContrato = $dispensaContrato[0];
            $int++;
        }
            
        $ComissaoDescricao = $Linha[2];
        $LicitacaoDtAbertura = substr($Linha[8], 8, 2) . '/' . substr($Linha[8], 5, 2) . '/' . substr($Linha[8], 0, 4);
        $LicitacaoHoraAbertura = substr($Linha[8], 11, 5);
        $idUltimaFaseLicitacao = ultimaFaseLicitacao($Linha[3], $Linha[4], $Linha[10], $Linha[11], $Linha[12], $db);
        $contraton = numeroContrato($Linha[10], $Linha[11], $Linha[3], $Linha[4], $Linha[12], $db);
        $contratonValidado = $contraton;
        //$valida = false;
        if(empty($contratonValidado) && $geraContrato == 'N'){
            $contratonValidado = "Termo de Contrato Dispensado";
          }
 //razão social fornecedores
 $razaoSocial = razaoSocial($Linha[10], $Linha[11], $Linha[3], $Linha[4], $Linha[12], $db);

    
 $grupoFornecedor = $Linha[10];
 $comissaoFornecedor = $Linha[11];
 $processoFornecedor = $Linha[3];
 $AnoFornecedor = $Linha[4];
 $orgaoFornecedor = $Linha[12];


         $sqlfornecedorRazao = " SELECT DISTINCT FORN.NFORCRRAZS";
         $sqlfornecedorRazao .= " FROM ";
         $sqlfornecedorRazao .= " SFPC.tbfaselicitacao A ";
         $sqlfornecedorRazao .= " INNER JOIN SFPC.TBITEMLICITACAOPORTAL ITEML ";
         $sqlfornecedorRazao .= " ON A.cgrempcodi = iteml.cgrempcodi ";
         $sqlfornecedorRazao .= " AND A.CCOMLICODI = ITEML.CCOMLICODI ";
         $sqlfornecedorRazao .= " AND A.CLICPOPROC = ITEML.CLICPOPROC ";
         $sqlfornecedorRazao .= " AND A.ALICPOANOP = ITEML.ALICPOANOP ";
         $sqlfornecedorRazao .= " AND A.CORGLICODI = ITEML.CORGLICODI ";
         $sqlfornecedorRazao .= " INNER JOIN SFPC.TBFORNECEDORCREDENCIADO FORN ";
         $sqlfornecedorRazao .= " ON ITEML.AFORCRSEQU = FORN.AFORCRSEQU ";
         $sqlfornecedorRazao .= " WHERE ";
         $sqlfornecedorRazao .= " iteml.cgrempcodi = '$grupoFornecedor' ";
         $sqlfornecedorRazao .= " AND iteml.ccomlicodi = '$comissaoFornecedor' ";
         $sqlfornecedorRazao .= " AND iteml.clicpoproc =  '$processoFornecedor' ";
         $sqlfornecedorRazao .= " AND iteml.alicpoanop = '$AnoFornecedor' ";
         $sqlfornecedorRazao .= " AND iteml.corglicodi = '$orgaoFornecedor' ";
         $resultFornecedorRazao = executarTransacao($db, $sqlfornecedorRazao);
         $numeroDeRazaoSocial = $resultFornecedorRazao->numRows();
         $razaoSocialFornecedor ='';
         $exibirRazaoSocial = '';
         $concatenaRazaoCnpj = '';
         

     
         $sqlfornecedor = " SELECT DISTINCT FORN.AFORCRCCGC, FORN.aforcrccpf";
         $sqlfornecedor .= " FROM ";
         $sqlfornecedor .= " SFPC.tbfaselicitacao A ";
         $sqlfornecedor .= " INNER JOIN SFPC.TBITEMLICITACAOPORTAL ITEML ";
         $sqlfornecedor .= " ON A.cgrempcodi = iteml.cgrempcodi ";
         $sqlfornecedor .= " AND A.CCOMLICODI = ITEML.CCOMLICODI ";
         $sqlfornecedor .= " AND A.CLICPOPROC = ITEML.CLICPOPROC ";
         $sqlfornecedor .= " AND A.ALICPOANOP = ITEML.ALICPOANOP ";
         $sqlfornecedor .= " AND A.CORGLICODI = ITEML.CORGLICODI ";
         $sqlfornecedor .= " INNER JOIN SFPC.TBFORNECEDORCREDENCIADO FORN ";
         $sqlfornecedor .= " ON ITEML.AFORCRSEQU = FORN.AFORCRSEQU ";
         $sqlfornecedor .= " WHERE ";
         $sqlfornecedor .= " iteml.cgrempcodi = '$grupoFornecedor' ";
         $sqlfornecedor .= " AND iteml.ccomlicodi = '$comissaoFornecedor' ";
         $sqlfornecedor .= " AND iteml.clicpoproc =  '$processoFornecedor' ";
         $sqlfornecedor .= " AND iteml.alicpoanop = '$AnoFornecedor' ";
         $sqlfornecedor .= " AND iteml.corglicodi = '$orgaoFornecedor' ";
         $resultFornecedor = executarTransacao($db, $sqlfornecedor);
         $numeroDeCnpj = $resultFornecedor->numRows();
         $cnpjFornecedor ='';
         $cnpjFornecedor = '';
         $cpfCnpjFornecedor = '';
         $exibir = '';

 for( $i2=0;$i2<$numeroDeCnpj;$i2++ ){
     $linhaFornecedor = $resultFornecedor->fetchRow();
     $cnpjFornecedor = mascara_cnpjcpf($linhaFornecedor[0]);
     $cpfFornecedor = mascara_cnpjcpf($linhaFornecedor[1]);
     $linhaFornecedorRazao = $resultFornecedorRazao->fetchRow();
     $razaoSocialFornecedor =  $linhaFornecedorRazao[0];            
     $exibirRazaoSocial  = $razaoSocialFornecedor;
             if(empty($cnpjFornecedor)){

                 $cpfCnpjFornecedor =  $cpfFornecedor;
                 $cpfCnpj='CPF: ';

             }else{

                 $cpfCnpjFornecedor =  $cnpjFornecedor;
                 $cpfCnpj='CNPJ: ';

             }
                 
             $concatenaRazaoCnpj .=  $cpfCnpj.$cpfCnpjFornecedor.'RAZAO SOCIAL: ' .$razaoSocialFornecedor.';';
         
 }
 
     if(empty($concatenaRazaoCnpj)){

         $concatenaRazaoCnpj = '-';

     }
            
           
 
            
 
 
        // Descrição da última fase
        $sqlFaseAtual = "SELECT F.EFASESDESC FROM SFPC.TBFASES F WHERE F.CFASESCODI = $idUltimaFaseLicitacao";
        $faseAtual = $db->query($sqlFaseAtual);
        if (db::isError($faseAtual)) {
            ExibeErroBD(__FILE__ . "\nLinha: " . __LINE__ . "\nSql: $sqlFaseAtual");
        }
        $descricaoUltimaFase = resultValorUnico($faseAtual);
       
        
        //$cabecalho = array('ADM DIRETA-INDIRETA', 'UNIDADE GESTORA', 'OBJETO', 'DATA ABERTURA', 'HORA ABERTURA', 'FASE', 'PROCESSO', 'MODALIDADE', 'LICITACAO');
        array_push($linhas, array(
            $Linha[7],
            $LicitacaoDtAbertura,
            $LicitacaoHoraAbertura,
            $descricaoUltimaFase,
            substr($Linha[3] + 10000, 1).'/'.$Linha[4],
            $Linha[1],
            substr($Linha[5] + 10000, 1).'/'.substr($Linha[6] + 10000, 1),
            $contratonValidado,
            $concatenaRazaoCnpj
        )
    );

    }
}

$db->disconnect();

$nomeArquivo = 'pcr_portal_compras_todas_licitacoes';

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
        unset($filtroPesquisa);
//}

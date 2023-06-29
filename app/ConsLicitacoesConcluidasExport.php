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
/**
 * -------------------------------------------------------------------------
 * Alterado: Pitang Agile IT
 * Data: 13/07/2015 - CR95756
 * Link: http://redmine.recife.pe.gov.br/issues/95756
 * Versão:
 * -------------------------------------------------------------------------.
 * Alterado: Pitang Agile TI
 * Data:     17/07/2015
 * Objetivo: [CR redmine 76836] Licitações Concluídas.
 * -------------------------------------------------------------------------
 * Alterado: Pitang Agile TI - Ernesto Ferreira
 * Data:     19/07/2018
 * Objetivo: CR 96103 - [INTERNET] Licitações Concluídas e Todas as Licitações - 
 * Criar campo de pesquisa por período
 * -------------------------------------------------------------------------
 * Alterado: Lucas Baracho
 * Data:     11/09/2018
 * Objetivo: Tarefa Redmine 203345
 * -----------------------------------------------------------------------------
 * Alterado: Caio Coutinho
 * Data:     10/10/2018
 * Objetivo: Tarefa Redmine 205100
 * -----------------------------------------------------------------------------
 * Alterado: João Madson
 * Data:     13/09/2019
 * Objetivo: Tarefa Redmine 223800
 * -----------------------------------------------------------------------------
 *  -----------------------------------------------------------------------------
 * Alterado: Osmar Celestino
 * Data:     16/04/2021
 * Objetivo: Tarefa Redmine 246882
 * -----------------------------------------------------------------------------
 * Alterado: Osmar Celestino
 * Data:     10/06/2021
 * Objetivo: CR# 247475
 * -----------------------------------------------------------------------------
 * 
 * Alterado: Osmar Celestino
 * Data:     21/07/2021
 * Objetivo: Ajustar o export de acordo com a pesquisa
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


include '../licitacoes/funcoesLicitacoes.php';
session_start();

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
}

function comissaoDescricao($db, $GrupoCodigo, $ComissaoCodigo, $ProcessoAno, $Processo, $OrgaoLicitanteCodigo){
    $db = Conexao();
    $sql  = "SELECT A.EGREMPDESC, B.EMODLIDESC, C.ECOMLIDESC as comissaodescricao, D.XLICPOOBJE, ";
    $sql .= "       E.EORGLIDESC, D.TLICPODHAB, D.CLICPOCODL, D.ALICPOANOP, ";
    $sql .= "       D.FLICPOREGP, B.CMODLICODI, D.VLICPOVALE, D.VLICPOVALH, ";
    $sql .= "       D.VLICPOTGES, D.FLICPOVFOR, C.NCOMLIPRES, C.ECOMLILOCA, ";
    $sql .= "       C.ACOMLIFONE, C.ACOMLINFAX, E.FORGLIEXVE,  C.CCOMLICODI ";
    $sql .= " FROM   SFPC.TBGRUPOEMPRESA A, SFPC.TBMODALIDADELICITACAO B, SFPC.TBCOMISSAOLICITACAO C, ";
    $sql .= "       SFPC.TBLICITACAOPORTAL D, SFPC.TBORGAOLICITANTE E ";
    $sql .= "WHERE  A.CGREMPCODI = D.CGREMPCODI ";
    $sql .= "       AND D.CGREMPCODI = $GrupoCodigo ";
    $sql .= "       AND D.CMODLICODI = B.CMODLICODI ";
    $sql .= "       AND C.CCOMLICODI = D.CCOMLICODI ";
    $sql .= "       AND D.CCOMLICODI = $ComissaoCodigo ";
    $sql .= "       AND D.ALICPOANOP = $ProcessoAno ";
    $sql .= "       AND D.CLICPOPROC = $Processo ";
    $sql .= "       AND E.CORGLICODI = D.CORGLICODI ";
    $sql .= "       AND D.CORGLICODI = $OrgaoLicitanteCodigo ";
    $result = executarTransacao($db,$sql);
    $row	= $result->fetchRow(DB_FETCHMODE_OBJECT);
    return $row->comissaodescricao;
}

function ultimaFaseLicitacao($processo,$ano,$grupo,$comissao,$orgao,$db) {
	
	$sql .= " select ";
	$sql .= " fl.cfasescodi as ultimafase ";
	$sql .= " from ";
	$sql .= " sfpc.tbfaselicitacao fl	 ";
	$sql .= " where ";
	$sql .= " fl.clicpoproc = $processo ";
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

    $sql .= " 	order by fl.tfaselulat desc limit 1  ";
	
	
 	$result	= executarTransacao($db, $sql);
	$row	= $result->fetchRow(DB_FETCHMODE_OBJECT);
	return $row->ultimafase;

}
function registroPreco($grupo,$comissao,$processo,$ano,$orgao,$db) {
    $db = Conexao();;
    $sql =  'SELECT D.FLICPOREGP as registropreco ';
    $sql .= '  FROM SFPC.TBGRUPOEMPRESA A, SFPC.TBMODALIDADELICITACAO B, SFPC.TBCOMISSAOLICITACAO C, ';
    $sql .= '       SFPC.TBLICITACAOPORTAL D, SFPC.TBORGAOLICITANTE E ';
    $sql .= " WHERE A.CGREMPCODI = D.CGREMPCODI AND D.CGREMPCODI = $grupo ";
    $sql .= '   AND D.CMODLICODI = B.CMODLICODI AND C.CCOMLICODI = D.CCOMLICODI ';
    $sql .= "   AND D.CCOMLICODI = $comissao AND D.ALICPOANOP = $ano ";
    $sql .= "   AND D.CLICPOPROC = $processo AND E.CORGLICODI = D.CORGLICODI ";
    $sql .= "   AND D.CORGLICODI = $orgao";
    $result	= executarTransacao($db, $sql);
    $row	= $result->fetchRow(DB_FETCHMODE_OBJECT);
    return $row->registropreco;
 }
 

function valorHomologado($grupo,$comissao,$processo,$ano,$orgao,$db) {
    $db = Conexao();;
    $sql =  'SELECT D.vlicpovalh as valorhomologado ';
    $sql .= '  FROM SFPC.TBGRUPOEMPRESA A, SFPC.TBMODALIDADELICITACAO B, SFPC.TBCOMISSAOLICITACAO C, ';
    $sql .= '       SFPC.TBLICITACAOPORTAL D, SFPC.TBORGAOLICITANTE E ';
    $sql .= " WHERE A.CGREMPCODI = D.CGREMPCODI AND D.CGREMPCODI = $grupo ";
    $sql .= '   AND D.CMODLICODI = B.CMODLICODI AND C.CCOMLICODI = D.CCOMLICODI ';
    $sql .= "   AND D.CCOMLICODI = $comissao AND D.ALICPOANOP = $ano ";
    $sql .= "   AND D.CLICPOPROC = $processo AND E.CORGLICODI = D.CORGLICODI ";
    $sql .= "   AND D.CORGLICODI = $orgao";
    $result	= executarTransacao($db, $sql);
    $row	= $result->fetchRow(DB_FETCHMODE_OBJECT);
    return $row->valorhomologado;
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


// Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $carregaProcesso = $_POST['carregaProcesso'];
    $Botao = ($_POST['Botao'] == 'carregaProcesso') ? $_POST['Botao'] : 'Pesquisar';
    $Critica = $_POST['Critica'];
    $Selecao = $_POST['Selecao'];
    $Objeto = strtoupper2($_POST['Objeto']);
    $OrgaoLicitanteCodigo = $_POST['OrgaoLicitanteCodigo'];
    $ComissaoCodigo = $_POST['ComissaoCodigo'];
    $ModalidadeCodigo = $_POST['ModalidadeCodigo'];
    $TipoItemLicitacao = $_POST['TipoItemLicitacao'];
    $Item = $_POST['ItemInput'];
    //$tipoEmpresa = (isset($_POST['tipoEmpresa'])) ? true : false;
    $LicitacaoAno = $_POST['LicitacaoAno'];
    $Pesquisa = $_POST['pesqPeriodo'];
    $datasValidas = false;

    

    //CAIO MELQUIADES - VARIAVEL QUE CONTROLA O TIPO DE ARQUIVO A SER EXPORTADO
    $formatoExport = isset($_REQUEST ['FormatoExport']) ? $_REQUEST ['FormatoExport'] : 'csv';

    if($Pesquisa == 1){
        //madson
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
        if( $dataIni == '--' or $dataFim == '--'){
    
            $_SESSION['Mensagem'] = 'As datas do período tem que ser preenchidas.';
            $_SESSION['Mens'] = 1;
            $_SESSION['Tipo'] = 1;
            header('Location: ConsLicitacoesConcluidas.php');
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
        //$_SESSION['tipoEmpresa'] = $tipoEmpresa;
        $_SESSION['botao'] = $Botao;
        $_SESSION['LicitacaoAno'] = $LicitacaoAno;
        $_SESSION['Pesquisa'] = $Pesquisa;
        $_SESSION['dataInicio'] = $dataIniFormatado;
        $_SESSION['dataFim'] = $dataFimFormatado;
    }

    if ($Botao == 'carregaProcesso') {
        list($_SESSION['GrupoCodigoDet'], $_SESSION['ProcessoDet'], $_SESSION['ProcessoAnoDet'], $_SESSION['ComissaoCodigoDet'], $_SESSION['OrgaoLicitanteCodigoDet']) = explode('-', $carregaProcesso);
        header('Location: ConsAcompDetalhes.php');
        exit();
    }

    if ($OrgaoLicitanteCodigo == '' && $ComissaoCodigo == '' && $ModalidadeCodigo == '' && $licitacaoSituacao == '' && $TipoItemLicitacao == '') {
        $Objeto = $_SESSION['Objeto'];
        $OrgaoLicitanteCodigo = $_SESSION['OrgaoLicitanteCodigo'];
        $ComissaoCodigo = $_SESSION['ComissaoCodigo'];
        $ModalidadeCodigo = $_SESSION['ModalidadeCodigo'];
        $Selecao = $_SESSION['Selecao'];
        // $_SESSION['RetornoPesquisa'] = 1;
        $TipoItemLicitacao = $_SESSION['TipoItemLicitacao'];
        $Item = $_SESSION['Item'];
        $adminDireta = $_SESSION['adminDireta'];
        //$tipoEmpresa = $_SESSION['tipoEmpresa'];
        $licitacaoSituacao = $_SESSION['licitacaoSituacao'];
    }
}

$arraySituacoesConcluidas = getIdFasesConcluidas(); // Array com os ids das situações concluídas
$arraySituacoesEmAndamento = getIdFasesEmAndamento(); // Array com os ids das situações em andamento

if ($_SESSION['botao'] == 'carregaProcesso' || $_SESSION['botao'] == 'Pesquisar') {
    $Objeto = $_SESSION['Objeto'];
    $OrgaoLicitanteCodigo = $_SESSION['OrgaoLicitanteCodigo'];
    $ComissaoCodigo = $_SESSION['ComissaoCodigo'];
    $ModalidadeCodigo = $_SESSION['ModalidadeCodigo'];
    $TipoItemLicitacao = $_SESSION['TipoItemLicitacao'];
    $Item = $_SESSION['Item'];
   // $tipoEmpresa = $_SESSION['tipoEmpresa'];
    $Botao = $_SESSION['botao'];
    $LicitacaoAno = $_SESSION['LicitacaoAno'];

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
        $sql = $fragmentoSelect.$fragmentoFrom.$fragmentoWhere.' LIMIT 1';
        $db = Conexao();
        $result = $db->query($sql);

        if (db::isError($result)) {
            ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
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
        'Item' => $_SESSION['Export']['Item'],
        'Descrição item' => ($Item != '') ? 'Todas' : $_SESSION['Export']['Descrição item'],
        'ModalidadeCodigo' => $_SESSION['Export']['ModalidadeCodigo'],
        'ComissaoCodigo' => $_SESSION['Export']['ComissaoCodigo'],
        'OrgaoLicitanteCodigo' => $_SESSION['Export']['OrgaoLicitanteCodigo'],
        'TipoItemLicitacao' => $_SESSION['Export']['TipoItemLicitacao'],
        'dataInicio' => $_SESSION['Export']['dataInicio'],
        'dataFim' => $_SESSION['Export']['dataFim'],
    );
    
    // var_dump($pesqValue);die;
        if($filtroPesquisa['dataInicio']!=NULL && $filtroPesquisa['dataFim']!=NULL){
        $filtroPesquisa['dataInicio'] = date('Y-m-d', strtotime(str_replace('/', '-', $filtroPesquisa['dataInicio']))); 
        $filtroPesquisa['dataFim'] = date('Y-m-d', strtotime(str_replace('/', '-', $filtroPesquisa['dataFim']))); 
        
        }

    // Redireciona dados para ConsLicitacoesAndamento.php se Houve Erro #
    if (($Item == '') and ($TipoItemLicitacao) != '') {
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
        //$_SESSION['tipoEmpresa'] = $tipoEmpresa;
        $_SESSION['Pesquisa'] = $Pesquisa;
        $_SESSION['dataInicio'] = $dataIniFormatado;
        $_SESSION['dataFim'] = $dataFimFormatado;
        header('Location: ConsLicitacoesConcluidas.php');
        exit();
    }
    // var_dump($filtroPesquisa['Descrição item']);die;

    // Redireciona dados para ConsAcompPesquisaGeral.php #
    if ($Botao == 'Pesquisa') {
        $_SESSION['RetornoPesquisa'] = null;
        header('Location: ConsLicitacoesConcluidas.php');
        exit();
    }
    // var_dump($filtroPesquisa['TipoItemLicitacao']);die;
    $Mens = 0;

    if ($Mens == 0) {
        $db = Conexao();
        $Data = date('Y-m-d');
        $novaSql = '';
        
        // SELECT
        $novaSql .= ' SELECT ';
        $novaSql .= ' distinct A.CLICPOPROC, B.EORGLIDESC, CD.ECOMLIDESC, e.EFASESDESC, GE.EGREMPDESC, ML.EMODLIDESC, ';
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

        // Microempresa, EPP ou MEI
        // if ($tipoEmpresa === true) {
        //     $novaSql .= " LEFT OUTER JOIN SFPC.TBITEMLICITACAOPORTAL ILP ";
        //     $novaSql .= " ON ILP.CLICPOPROC = A.CLICPOPROC ";
        //     $novaSql .= " AND ILP.ALICPOANOP = A.ALICPOANOP ";
        //     $novaSql .= " AND ILP.CGREMPCODI = A.CGREMPCODI ";
        //     $novaSql .= " AND ILP.CCOMLICODI = A.CCOMLICODI ";
        //     $novaSql .= " AND ILP.CORGLICODI = A.CORGLICODI INNER JOIN SFPC.TBFORNECEDORCREDENCIADO FC ";
        //     $novaSql .= " ON ILP.AFORCRSEQU = FC.AFORCRSEQU ";
        //     $novaSql .= " AND FC.FFORCRMEPP IS NOT NULL ";
        // }

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
        $novaSql .= ' AND a.CLICPOPROC = maiorordem.Proc
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
        // var_dump($novaSql);die;

        // Comissão
        if ($filtroPesquisa['ComissaoCodigo'] != '') {
            $ComissaoCodigo = $filtroPesquisa['ComissaoCodigo'];
            $novaSql .= " AND A.CCOMLICODI = $ComissaoCodigo ";
        }
        // var_dump($novaSql);die;

        // Modalidade
        if ($filtroPesquisa['ModalidadeCodigo'] != '') {
            $ModalidadeCodigo = $filtroPesquisa['ModalidadeCodigo'];
            $novaSql .= " AND A.CMODLICODI = $ModalidadeCodigo ";
        }
        // var_dump($novaSql);die;

        // Situação
        $strIdConcluida = implode(', ', $arraySituacoesConcluidas);

        $novaSql .= " AND D.CFASESCODI IN ($strIdConcluida) ";
        // Na fase interna não deve aparecer na internet (valor 1)
        $novaSql .= " AND D.CFASESCODI <> 1";

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

        // var_dump($dataFim);die;
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
        // var_dump($Item);die;
        unset($filtroPesquisa);
        //$novaSql .= " AND A.TLICPODHAB < clock_timestamp()";

        // ORDER BY
        $novaSql .= ' ORDER BY GE.EGREMPDESC, B.EORGLIDESC, CD.ECOMLIDESC, A.ALICPOANOP DESC, A.CLICPOPROC DESC';
        // var_dump($novaSql);die;
        $result = $db->query($novaSql);

        if (db::isError($result)) {
            ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $novaSql");
        }

        if ($result->numRows() <= 0) {
            $_SESSION['Mensagem'] = 'Nenhuma ocorrência foi encontrada.';
            $_SESSION['Mens'] = 1;
            $_SESSION['Tipo'] = 1;

            header('Location: ConsLicitacoesConcluidas.php');
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
                ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlFases");
            }

            $licitacaoPublicada = in_array(2, $fasesLicitacao);
            $dataAberturaMenorQueAtual = strtotime($cols[10]) < strtotime(date('Y-m-d H:i:s'));

            // Verifica se a licitação está publicada ou se a data de abertura é menor que a data atual
            if ($licitacaoPublicada || $dataAberturaMenorQueAtual) {
                ++$cont;
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
        $ComissaoSCC = $Linha[11];
            $ProcessoSCC = $Linha[3];
            $GrupoSCC = $Linha[10];
            $AnoSCC = $Linha[4];


        /*foreach ($filtroPesquisa as $nomeFiltro => $valor) {
            $tpl->NAME_SEARCH = $nomeFiltro;
            $tpl->VALUE_SEARCH = $valor;
            $tpl->block('BLOCO_SEARCH');
        }*/

        $cabecalho = array( 'OBJETO', 'DATA ABERTURA', 'HORA ABERTURA', 'FASE', 'PROCESSO', 'MODALIDADE', 'COMISSAO DE LICITACAO','LICITACAO','VALOR TOTAL ESTIMADO','VALOR TOTAL HOMOLOGADO', 'REGISTRO DE PREÇO','Nº SOLICITAÇÃO DE COMPRA','Nº CONTRATO','FORNECEDOR VENCEDOR');
        $linhas = array();

        $mudou = 0;
        for ($Row = 0; $Row < $cont; ++$Row) {
            $Linha = explode($SimboloConcatenacaoArray, $dados[$Row]);

            /*if ($Linha[0] != '' && $GrupoDescricao != $Linha[0]) {
                $tpl->GRUPO_DESCRICAO = $Linha[0];
                $GrupoDescricao = $Linha[0];
                $tpl->block('BLOCO_GRUPO');
                $mudou = 1;
            }*/

            if ($ModalidadeDescricao != $Linha[1]) {
                $ModalidadeDescricao = $Linha[1];
            }

            /*if ($OrgaoDescricao != $Linha[9]) {
                $ultimoOrgaoPlotado = $Linha[9];
                $tpl->ORGAO_DESCRICAO = $Linha[9];
                $tpl->block('BLOCO_ORGAO');
                $mudou = 1;
            }*/

            /*if ($OrgaoDescricao != $Linha[9]) {
                $tpl->block('BLOCO_CABECALHO');
                $OrgaoDescricao = $Linha[9];
                $mudou = 1;
            }

            if ($mudou == 1) {
                $tpl->block('BLOCO_TITULO');
                $mudou = 0;
            }*/

            $ComissaoDescricao = $Linha[2];
            $LicitacaoDtAbertura = substr($Linha[8], 8, 2).'/'.substr($Linha[8], 5, 2).'/'.substr($Linha[8], 0, 4);
            $LicitacaoHoraAbertura = substr($Linha[8], 11, 5);
            $valorEstimado = totalValorEstimado($db,  $Linha[3], $Linha[4], $Linha[10], $Linha[11], $Linha[12]);
            $valorHomologado = valorHomologado($Linha[10], $Linha[11], $Linha[3], $Linha[4], $Linha[12], $db);
            $valor_convertido = 'R$ '.converte_valor($valorEstimado);
            $valorHomologadoConvertido ='R$ '. converte_valor($valorHomologado);
            $idUltimaFaseLicitacao = ultimaFaseLicitacao($Linha[3], $Linha[4], $Linha[10], $Linha[11], $Linha[12], $db);
            $registoPreco = registroPreco($Linha[10], $Linha[11], $Linha[3], $Linha[4], $Linha[12], $db);
            $comissao_Descricao = comissaoDescricao($db, $Linha[10], $Linha[11], $Linha[4], $Linha[3], $Linha[12]);
            //chamada da função de nº contrato
            $contraton = numeroContrato($Linha[10], $Linha[11], $Linha[3], $Linha[4], $Linha[12], $db);
            $contratonValidado = $contraton;
            //$valida = false;
              

                $ComissaoSCC = $Linha[11];
                $ProcessoSCC = $Linha[3];
                $GrupoSCC = $Linha[10];
                $AnoSCC = $Linha[4];
    
    
            $sqlSolicitacoesC  = " SELECT  csolcosequ ,clicpoproc , alicpoanop , cgrempcodi ,ccomlicodi ,corglicodi ";
            $sqlSolicitacoesC .= " FROM SFPC.TBSOLICITACAOLICITACAOPORTAL SOL WHERE SOL.CLICPOPROC = $ProcessoSCC AND SOL.ALICPOANOP =" . $AnoSCC ;
            $sqlSolicitacoesC .= " AND SOL.CCOMLICODI = $ComissaoSCC AND SOL.cgrempcodi =" . $GrupoSCC;
    
            $resultSolic = executarTransacao($db, $sqlSolicitacoesC);
            if (db::isError($resultSolic)) {
                ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sqlSolicitacoesC");
            }
            $Solicitacao = '';
            $int = 0;
            while ($llinha = $resultSolic->fetchRow()) {
                $solicitacaosql  = " SELECT  fsolcocont ";
                $solicitacaosql .= " FROM SFPC.TBSOLICITACAOCOMPRA SC WHERE SC.CSOLCOSEQU = " .$llinha[0];
                $resultSolic = executarTransacao($db,$solicitacaosql);
                $dispensaContrato = $resultSolic->fetchRow();
                $dispensa = $dispensaContrato[0];
                if ($int > 0) {
                    $Solicitacao .= ' - ';
                }
                $Solicitacao .= getNumeroSolicitacaoCompra($db, $llinha[0]);
                $SeqSolicitacao = $llinha[0];
                $int++;
            }
            if(empty($contratonValidado) && $dispensa == 'S'){
                $contratonValidado = "Termo de Contrato Dispensado";
              }
            //razão social fornecedores
                                  //chamada fução fornecedores
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
                ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlFaseAtual");
            }
            $descricaoUltimaFase = resultValorUnico($faseAtual);
            

            //cabecalho = array('OBJETO', 'DATA ABERTURA', 'HORA ABERTURA', 'FASE', 'PROCESSO', 'MODALIDADE', 'LICITACAO');
            array_push($linhas, array(
                $Linha[7],
                $LicitacaoDtAbertura,
                $LicitacaoHoraAbertura,
                $descricaoUltimaFase,
                substr($Linha[3] + 10000, 1).'/'.$Linha[4],
                $Linha[1],
                $comissao_Descricao,
                substr($Linha[5] + 10000, 1).'/'.substr($Linha[6] + 10000, 1),
                $valor_convertido,
                $valorHomologadoConvertido,
                $registoPreco,
                $Solicitacao,
                $contratonValidado,
                $concatenaRazaoCnpj
            )
        );

           
        }
    }

    /* É preciso ter esse valores salvos para o caso de redirecionar */
    $_SESSION['Objeto'] = $Objeto;
    $_SESSION['OrgaoLicitanteCodigo'] = $OrgaoLicitanteCodigo;
    $_SESSION['ComissaoCodigo'] = $ComissaoCodigo;
    $_SESSION['ModalidadeCodigo'] = $ModalidadeCodigo;
    $_SESSION['Selecao'] = $Selecao;
    $_SESSION['RetornoPesquisa'] = 1;
    $_SESSION['TipoItemLicitacao'] = $TipoItemLicitacao;
    $_SESSION['Item'] = $Item;
    $_SESSION['adminDireta'] = $adminDireta;
    //$_SESSION['tipoEmpresa'] = $tipoEmpresa;
    $_SESSION['licitacaoSituacao'] = $licitacaoSituacao;
    $_SESSION['botaoVoltar'] = 'ConsLicitacoesConcluidasResultado.php';

    $db->disconnect();

    $nomeArquivo = 'pcr_portal_compras_licitacoes_concluidas';

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

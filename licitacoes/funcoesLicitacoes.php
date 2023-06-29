<?php
#----------------------------------------------------------------------------
# Portal da DGCO
# Programa: funcoesLicitacoes.php
# Objetivo: funções com regras do módulo Licitações
# Autor:    Ariston Cordeiro
#-----------------------
# Alterado:
# Data:
#---------------------------
# OBS.:     Tabulação 2 espaços
#----------------------------------------------------------------------------
# Alterado: Lucas Baracho  
# Data:     10/07/2018
# Objetivo: Tarefa Redmine 73631
#----------------------------------------------------------------------------
# Alterado: Lucas Baracho  
# Data:     10/08/2018
# Objetivo: Tarefa Redmine 200957
#----------------------------------------------------------------------------
# Alterado : Osmar Celestino
# Data: 21/09/2021
# Objetivo: CR #248922
#---------------------------------------------------------------------------

# arquivo geral de funcoes
require_once("../funcoes.php");

# funcoes de compras, usados no módulo de compras
require_once("../compras/funcoesCompras.php");

#fases de licitação usadas nos programas
$GLOBALS['FASE_LICITACAO_REVOGACAO'] = 11;
$GLOBALS['FASE_LICITACAO_ANULACAO'] = 12;
$GLOBALS['FASE_LICITACAO_HOMOLOGACAO'] = 13;
$GLOBALS['FASE_LICITACAO_CANCELAMENTO'] = 17;

define ('FASE_LICITACAO_PUBLICACAO', 2);
define ('FASE_LICITACAO_REVOGACAO', 11);
define ('FASE_LICITACAO_ANULACAO', 12);
define ('FASE_LICITACAO_HOMOLOGACAO', 13);
define ('FASE_LICITACAO_CANCELAMENTO', 17);

/** Indica se uma licitação está em uma situação que indica que ela não será mais feita
* (cancelada, revogada ou anulada) */
function isLicitacaoCancelada($faseLicitacao){
	assercao(!is_null($faseLicitacao), "Parâmetro 'faseLicitacao' requerido");
	$resultado = false;
	if(
		$faseLicitacao == $GLOBALS['FASE_LICITACAO_REVOGACAO'] or
		$faseLicitacao == $GLOBALS['FASE_LICITACAO_ANULACAO'] or
		$faseLicitacao == $GLOBALS['FASE_LICITACAO_CANCELAMENTO']
	){
		$resultado = true;
	}
	return $resultado;
}
/** Retorna a administração de um Órgão */
function administracaoOrgao($db, $idOrgao){
	assercao(!is_null($db), "Variável de banco de dados não foi inicializada");
	assercao(!is_null($idOrgao), "Parâmetro 'idOrgao' requerido");
	$sql = "
		select forglitipo
		from sfpc.tborgaolicitante
		where corglicodi = ".$idOrgao."
	";
	$administracao = resultValorUnico( executarSQL($db, $sql ) );
	assercao(!is_null($administracao), "Campo 'forglitipo' é nulo, ou órgão não foi encontrado. corglicodi = '".$idOrgao."'");
	assercao($administracao=="D" or $administracao=="I", "Campo 'forglitipo' deve ser 'D' ou 'I', mas está com valor inválido. corglicodi = '".$idOrgao."'");
	return $administracao;
}


//###############################################
//  Função pegar maior data de incusão das SCCs 
//###############################################
function maiorDataSolicitacoes($processo,$ano,$grupo,$comissao,$orgao,$db) {
  $sql  = " select max(sol.tsolcodata) as maxdata ";
  $sql .= " from ";
  $sql .= " sfpc.tbsolicitacaolicitacaoportal licsol, ";
  $sql .= " sfpc.tbsolicitacaocompra sol ";
  $sql .= " where ";
  $sql .= " licsol.clicpoproc=$processo ";
  $sql .= " and licsol.alicpoanop=$ano ";
  $sql .= " and licsol.cgrempcodi=$grupo ";
  $sql .= " and licsol.ccomlicodi=$comissao ";
  $sql .= " and licsol.corglicodi=$orgao ";  
  $sql .= " and licsol.csolcosequ=sol.csolcosequ  ";  
  $result	= executarTransacao($db, $sql);
  $row	= $result->fetchRow(DB_FETCHMODE_OBJECT);  
  return $row->maxdata;     
}

function consultaCriterio(){
	$db = Conexao();
	$sql = "SELECT cj.ccrjulcodi, cj.ecrjulnome FROM SFPC.tbcriteriojulgamento cj ";
	$sql.= "ORDER BY cj.ccrjulcodi ASC";
	$resultado = executarSQL($db, $sql);

	while($resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT)) {
		$dados[] = $retorno;
	}
	return $dados;
}

//###################################################
//  Função pegar maior data das fases da licitacao
//###################################################
function maiorDataFases($processo,$ano,$grupo,$comissao,$orgao,$db) {
  $sql  = " select max(tfaseldata) as maxdata ";
  $sql .= " from ";
  $sql .= " sfpc.tbfaselicitacao  fase  ";
  $sql .= " where ";
  $sql .= " fase.clicpoproc=$processo ";
  $sql .= " and fase.alicpoanop=$ano ";
  $sql .= " and fase.cgrempcodi=$grupo ";
  $sql .= " and fase.ccomlicodi=$comissao ";
  $sql .= " and fase.corglicodi=$orgao ";  
  
  $result	= executarTransacao($db, $sql);
  $row	= $result->fetchRow(DB_FETCHMODE_OBJECT);   
  return $row->maxdata;     
}

//###################################################
//  Excluir dados da licitacao na TRP 
//###################################################
function excluirLicitacaoNaTRP($processo,$ano,$grupo,$comissao,$orgao,$db) {
$sql  = " delete    ";
$sql .= " from ";
$sql .= " sfpc.tbtabelareferencialprecos  ";
$sql .= " where ";
$sql .= " clicpoproc=$processo ";
$sql .= " and  alicpoanop=$ano ";
$sql .= " and  cgrempcodi=$grupo ";
$sql .= " and  ccomlicodi=$comissao ";
$sql .= " and  corglicodi=$orgao ";
$result	= executarTransacao($db, $sql);

}

//###################################################
//  Excluir dados da licitacao na TRP
//###################################################
function excluirSolicitacaoNaTRP($processo,$ano,$grupo,$comissao,$orgao,$db) {
$sql  = " delete from sfpc.tbtabelareferencialprecos ";
$sql .= " where ";
$sql .= " csolcosequ in ( ";
$sql .= " select csolcosequ ";
$sql .= "	where ";
$sql .= "	clicpoproc = $processo ";
$sql .= "	and alicpoanop = $ano ";
$sql .= "	and cgrempcodi = $grupo ";
$sql .= "	and ccomlicodi = $comissao ";
$sql .= "	and corglicodi = $orgao ";
$sql .= " ) ";
$result	= executarTransacao($db, $sql);

}


//###################################################
//  Função pegar ultima fase
//###################################################
function ultimaFase($processo,$ano,$grupo,$comissao,$orgao,$db) {
	$db = Conexao();
	$sql  = "SELECT CFASESCODI as ultimaFase ";
    $sql .= "FROM   (SELECT A.CFASESCODI, A.TFASELDATA, B.AFASESORDE ";
    $sql .= "        FROM   SFPC.TBFASELICITACAO A ";
    $sql .= "               LEFT JOIN SFPC.TBFASES B ON A.CFASESCODI = B.CFASESCODI ";
    $sql .= "        WHERE  A.CLICPOPROC = $processo ";
    $sql .= "               AND A.ALICPOANOP = $ano ";
    $sql .= "               AND A.CGREMPCODI = $grupo "; 
    $sql .= "               AND A.CCOMLICODI = $comissao ";
    $sql .= "               AND A.CORGLICODI = $orgao ";
    $sql .= "        ORDER BY A.TFASELDATA DESC, B.AFASESORDE DESC ";
    $sql .= "        LIMIT 1) AS ultima ";

 	$result	= executarTransacao($db, $sql);
	$row	= $result->fetchRow(DB_FETCHMODE_OBJECT);
	
	return $row->ultimafase;
}


//###################################################
//  Função pegar maior data das fases da licitacao menos a última; 
//  obs.: usada para alterar data de licitação 
//###################################################
function maiorDataFases2($processo,$ano,$grupo,$comissao,$orgao,$db) {
  $sql  = " select to_char(tfaseldata,'dd/mm/yyyy') as datafase ";
  $sql .= " from ";
  $sql .= " sfpc.tbfaselicitacao  fase  ";
  $sql .= " where ";
  $sql .= " fase.clicpoproc=$processo ";
  $sql .= " and fase.alicpoanop=$ano ";
  $sql .= " and fase.cgrempcodi=$grupo ";
  $sql .= " and fase.ccomlicodi=$comissao ";
  $sql .= " and fase.corglicodi=$orgao ";
  $sql .= " order by datafase ";  
  $result	= executarTransacao($db, $sql);
  
  $i=1;
  while ( $row	= $result->fetchRow(DB_FETCHMODE_OBJECT) ) {
     $vetor[$i++]=$row->datafase;   
  }
  $tam = count($vetor);
  if ( $tam > 1 ) $retorno = $vetor[$tam-1]; else $retorno = $vetor[$tam];
  
  return $retorno;     
} 



//###################################################
//  Função formata mensagem 
//###################################################
function verificaUltimasDatas($DataFase,$Processo,$ProcessoAno,$grupo,$ComissaoCodigo,$OrgaoLicitanteCodigo,$db,$ind_alteracao=0) {
  if ( empty($Processo) || empty($ProcessoAno)  ) return ;
  
  
  $db     = Conexao();
  $maiorDataFases =  maiorDataFases($Processo,$ProcessoAno,$_SESSION['_cgrempcodi_'],$ComissaoCodigo,$OrgaoLicitanteCodigo,$db) ;
  
//  if ( $ind_alteracao == 0 ) { 
//    $maiorDataFases =  maiorDataFases($Processo,$ProcessoAno,$_SESSION['_cgrempcodi_'],$ComissaoCodigo,$OrgaoLicitanteCodigo,$db) ;
//  }
//  else {
//    $maiorDataFases =  maiorDataFases2($Processo,$ProcessoAno,$_SESSION['_cgrempcodi_'],$ComissaoCodigo,$OrgaoLicitanteCodigo,$db) ;
//  }
      
//  $maiorDataSolicitacoes =  maiorDataSolicitacoes($Processo,$ProcessoAno,$_SESSION['_cgrempcodi_'],$ComissaoCodigo,$OrgaoLicitanteCodigo,$db) ;
  $db->disconnect();

  $dataFase_aux = new DataHora($DataFase);
  
  
  if ( !empty($maiorDataFases) ) {
    
    $maiorDataFases = substr($maiorDataFases,8,2)."/".substr($maiorDataFases,5,2)."/".substr($maiorDataFases,0,4);
    $maiorDataFases_aux    =  new DataHora($maiorDataFases);
    if ( $dataFase_aux < $maiorDataFases_aux  ) {
        if( $Mens == 1 ){ $Mensagem.=", "; }
	    	$Mens      = 1;
	    	$Tipo      = 2;
	  	$Mensagem = "<a href=\"javascript:document.FaseLicitacao.DataFase.focus();\" class=\"titulo2\">Data da Fase deve ser maior ou igual a data última fase($maiorDataFases)</a>";
    }
  } 
 
 // $maiorDataSolicitacoes_aux    =  new DataHora($maiorDataSolicitacoes);
 
//  if ( $dataFase_aux < $maiorDataSolicitacoes_aux  ) {
//     if( $Mens == 1 ){ $Mensagem.=", "; }
//    	$Mens      = 1;
//	    $Tipo      = 2;
//		$Mensagem .= "<a href=\"javascript:document.FaseLicitacao.DataFase.focus();\" class=\"titulo2\">Data da Fase deve ser maior ou igual a data última solicitação($maiorDataSolicitacoes)</a>";
//  }
  
  return $Mensagem;
  
}						   


//--------------------------------------------------
//  Total Valor Logrado
//--------------------------------------------------
function getTotalValorLogrado($db,$processo,$ano,$grupo,$comissao,$orgao,$lote )	 {
	$sql  = " select ";
	$sql .= " sum ( aitelpqtso * vitelpvlog ) as soma ";
	$sql .= " from ";
	$sql .= " sfpc.tbitemlicitacaoportal ";
	$sql .= " where ";
	$sql .= " clicpoproc = $processo ";
	$sql .= " and alicpoanop = $ano ";
	$sql .= " and cgrempcodi = $grupo ";
	$sql .= " and ccomlicodi = $comissao ";
	$sql .= " and corglicodi = $orgao ";
	$sql .= " and citelpnuml = $lote ";
	$sql .= " and cmatepsequ is not null ";
	$sql .= " and vitelpvlog <> 0 ";
	$result	= executarTransacao($db, $sql);
	$row	= $result->fetchRow(DB_FETCHMODE_OBJECT);
	return  $row->soma;

}


function getTotalValorServico($db,$processo,$ano,$grupo,$comissao,$orgao,$lote )	 {
	$sql  = " select ";
	$sql .= " sum ( aitelpqtso * vitelpvlog ) as soma ";
	$sql .= " from ";
	$sql .= " sfpc.tbitemlicitacaoportal ";
	$sql .= " where ";
	$sql .= " clicpoproc = $processo ";
	$sql .= " and alicpoanop = $ano ";
	$sql .= " and cgrempcodi = $grupo ";
	$sql .= " and ccomlicodi = $comissao ";
	$sql .= " and corglicodi = $orgao ";
	$sql .= " and citelpnuml = $lote ";
	$sql .= " and cservpsequ is not null ";
	$sql .= " and vitelpvlog <> 0 ";
	$result	= executarTransacao($db, $sql);
	$row	= $result->fetchRow(DB_FETCHMODE_OBJECT);
	return  $row->soma;

}

function getDescricaoTratamentoDiferenciado($tratamentoDiferenciado) {
	switch ($tratamentoDiferenciado) {
		case 'N':
			$descricaoTratamentoDiferenciado = 'Não';
			break;
		case 'E':
			$descricaoTratamentoDiferenciado = 'Exclusivo';
			break;
		case 'C':
			$descricaoTratamentoDiferenciado = 'Cota Reservada';
			break;
		case 'S':
			$descricaoTratamentoDiferenciado = 'Subcontratação';
			break;
		case 'M':
			$descricaoTratamentoDiferenciado = 'Cota Reservada/Exclusiva';
			break;
		case 'A':
			$descricaoTratamentoDiferenciado = 'Ampla Concorrência/Exclusiva';
			break;
		default:
			$descricaoTratamentoDiferenciado = 'Todas as situações';
	}

	return $descricaoTratamentoDiferenciado;

}





?>

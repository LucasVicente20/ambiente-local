<?php

require_once "../funcoes.php";
require_once "TemplateAppPadrao.php";

session_start();

function proccessPrincipal()
{
	$tpl = new TemplateAppPadrao("templates/ConsSancoesSelecionar.html", "ConsSancoesPesquisar");
	
	$tpl->show();
}

function processPesquisar()
{
	$tpl = new TemplateAppPadrao("templates/ConsSancoesSelecionar.html", "ConsSancoesPesquisar");
	
	$Mensagem = "";
	$Tipo = 1;
	
	$Botao        	= $_POST['Botao'];
	$Todos        	= $_POST['Todos'];
	$ItemPesquisa 	= $_POST['ItemPesquisa'];
	$Argumento		= strtoupper2(trim($_POST['Argumento']));
	$Palavra		= $_POST['Palavra'];
	$PalavraTodos 	= false;
	
	if ($ItemPesquisa == "CNPJ")
	{
		$tpl->CNPJ_SELECTED = "selected";
	}else if ($ItemPesquisa == "CPF")
	{
		$TPL->CPF_SELECTED = "selected";
	}
	
	if ($Argumento)
	{
		$tpl->VALOR_ARGUMENTO = $_POST['Argumento'];
	}
	
	if ($Palavra)
	{
		$tpl->PALAVRA_CHECKED = "checked";
	}
	
	if ($Todos)
	{
		$tpl->TODOS_CHECKED = "checked";
	}
	
	//Mensagens
	if (($ItemPesquisa == "RAZAO") && !$Argumento && !$Palavra && !$Todos) {
		$Mensagem = "Selecione uma das opções, Palavra Exata ou Todos.";
		$Tipo = 2;
	} else if (($ItemPesquisa == "RAZAO") && !$Argumento && $Palavra && $Todos) {
		$Mensagem = "Selecione apenas uma das opções, Palavra Exata ou Todos.";
		$Tipo = 2;
	} else if (($ItemPesquisa == "RAZAO") && !$Argumento && $Palavra && !$Todos) {
		$Mensagem = "O argumento informado é invalido.";
		$Tipo = 2;
	} else if (($ItemPesquisa == "CNPJ") && !$Argumento && $Palavra && !$Todos) {
		$Mensagem = "O CNPJ informado é invalido.";
		$Tipo = 2;
	}
	
	if($Palavra && $Todos){
		$PalavraTodos = true;
	}
	
	if( ($Argumento != "") || ($Todos)){
		
		$db	    = Conexao();
		$sql = "
					SELECT
						CASE WHEN F.AFORCRCCGC IS NULL THEN F.AFORCRCCPF ELSE F.AFORCRCCGC END AS CGCCPF,
						F.NFORCRRAZS, FTS.EFORTSDESC , FS.DFORSISITU, FS.EFORSIMOTI, FS.DFORSIEXPI, F.AFORCRSEQU
					FROM
						SFPC.TBFORNECEDORCREDENCIADO F,
						SFPC.TBFORNSITUACAO FS,
						SFPC.TBFORNECEDORTIPOSITUACAO FTS
					WHERE
						F.AFORCRSEQU = FS.AFORCRSEQU AND
						FS.CFORTSCODI = FTS.CFORTSCODI AND
						(
							FS.CFORTSCODI = 2 OR --inabilitado
							FS.CFORTSCODI = 3 --suspenso
						) AND
						FS.DFORSISITU =
							( SELECT MAX(FS2.DFORSISITU)
							FROM SFPC.TBFORNSITUACAO FS2
							WHERE F.AFORCRSEQU = FS2.AFORCRSEQU )
				";
		
		if(!$Todos){
			if(!$Palavra){
				if ( $ItemPesquisa == "CPF" ){
					$sql .= " AND F.AFORCRCCPF = '".$Argumento."%' ";
				} else if ( $ItemPesquisa == "CNPJ" ){
					$sql .= " AND F.AFORCRCCGC LIKE '".$Argumento."%' ";
				} else if ( $ItemPesquisa == "RAZAO" ){
					$sql .= " AND ( F.NFORCRRAZS ILIKE '%".$Argumento."%' ) ";
				}
		}else{
				if ( $ItemPesquisa == "CPF" ){
					$sql .= " AND F.AFORCRCCPF = '".$Argumento."' ";
				} else if ( $ItemPesquisa == "CNPJ" ){
					$sql .= " AND F.AFORCRCCGC = '".$Argumento."' ";
				} else if ( $ItemPesquisa == "RAZAO" ){
					$sql .= " AND ( F.NFORCRRAZS = '".$Argumento."' ) ";
				}
			}
		}
	
		$sql .= "
					ORDER BY
						FTS.EFORTSDESC,
						F.NFORCRRAZS
				";
		$result 	= $db->query($sql);
		if( PEAR::isError($result) ){
		
		}
		$db->disconnect();
		
		//Mensagens
		if (($ItemPesquisa == "CNPJ") && ($result->numRows() > 0) && $Palavra && $Todos) {
			$Mensagem = "Selecione apenas uma das opções, Palavra Exata ou Todos.";
			$Tipo = 2;
		} else if (($ItemPesquisa == "CNPJ") && !($result->numRows() > 0) && !$Palavra && !$Todos) {
			$Mensagem = "Selecione uma das opções, Palavra Exata ou Todos.";
			$Tipo = 2;
		} else if (($ItemPesquisa == "CNPJ") && !($result->numRows() > 0) && $Palavra && !$Todos) {
			$Mensagem = "O CNPJ informado é invalido.";
			$Tipo = 2;
		}
		
		$DataExpiracaoMaiorqueAtual = "valorInicial";
		
		if( $result->numRows() > 0 && !$PalavraTodos ){
			$tpl->block("BLOCK_CABECALHO_PESQUISA");
			$SituacaoVelha = "";
			while( $Linha	= $result->fetchRow() ){
				$Cadastro  = $Linha[0]; // cpf ou cnpj
				$Razao	   = $Linha[1];
				$Situacao  = $Linha[2];
				
				if (substr($Linha[3],8,2)."/".substr($Linha[3],5,2)."/".substr($Linha[3],0,4) != "//")
				{
					$DataSituacao	= substr($Linha[3],8,2)."/".substr($Linha[3],5,2)."/".substr($Linha[3],0,4);
				}
				
				$MotivoSituacao  = $Linha[4];
				
				if (substr($Linha[5],8,2)."/".substr($Linha[5],5,2)."/".substr($Linha[5],0,4) != "//")
				{
					$DataExpiracao = substr($Linha[5],8,2)."/".substr($Linha[5],5,2)."/".substr($Linha[5],0,4);
					$DataExpiracaoAmericano = substr($Linha[5],0,4)."-".substr($Linha[5],5,2)."-".substr($Linha[5],8,2);
				}
				
				$Sequencial  = $Linha[6];
				
				
				if($SituacaoVelha == "")
				{
					$SituacaoVelha = $Situacao;
				}	

				else if($Situacao != $SituacaoVelha && $DataExpiracaoMaiorqueAtual != "") {
					$SituacaoVelha = $Situacao;
					$tpl->block("BLOCK_SITUACAO");
					$DataExpiracaoMaiorqueAtual = "valorInicial";
				}else{
					$SituacaoVelha = $Situacao;
				}
				
				$tpl->{VALOR_SITUACAO} = $Situacao;
				
				$pieces = explode("/", $_SERVER['SCRIPT_NAME']);
				
				$server = $_SERVER['SERVER_NAME'];
				if ('setubal.recife' === $server) {
				    $server .= ':8080';
				}
				
				#Verifica se a data de expiração é maior que a data atual. Caso seja, será plotada 
				if(date("Y-m-d",strtotime($DataExpiracaoAmericano)) > date("Y-m-d")){
				   $DataExpiracaoMaiorqueAtual = $DataExpiracao;
				   $tpl->{VALOR_SESSION_URL} = $server . "/" . $pieces[1];
				   $tpl->{VALOR_SEQUENCIAL} = $Sequencial;
				   $tpl->{VALOR_CADASTRO} = $Cadastro;
				   $tpl->{VALOR_RAZAO} = $Razao;
				   $tpl->{VALOR_DATA_SITUACAO} = $DataSituacao;
				   $tpl->{VALOR_MOTIVO_SITUACAO} = $MotivoSituacao;
				   $tpl->{VALOR_DATA_EXPIRACAO} = $DataExpiracao;
				   $tpl->block("BLOCK_LINHA");
				}
				else 
					if($DataExpiracaoMaiorqueAtual == "valorInicial"){
						$DataExpiracaoMaiorqueAtual = "";
					}
			}
			if($DataExpiracaoMaiorqueAtual != ""){
			  $tpl->block("BLOCK_SITUACAO");
			}
			
		} else {
			$Mensagem = "Nenhuma ocorrência foi encontrada.";
			$Tipo = 1;
		}
	} else {
		$Mensagem = "Selecione uma das opções, Palavra Exata ou Todos.";
        $Tipo = 2;
	}
	
	if (!empty($Mensagem)) {
		$tpl->exibirMensagemFeedback($Mensagem, $Tipo);
	}
	
	$tpl->show();
}

/**
 * [frontController description]
 * @return [type] [description]
 */
function frontController()
{
	$botao = isset($_REQUEST['BotaoAcao'])
	? $_REQUEST['BotaoAcao']
	: 'Principal';
	
	switch ($botao) {
		case 'Pesquisar':
			processPesquisar();
			break;
		case 'LimparTela':
			proccessPrincipal();
			break;
		case 'Principal':
		default:
			proccessPrincipal();
	}
}

frontController();

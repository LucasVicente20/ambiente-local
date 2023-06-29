<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadLicitacaoAlterarNovo.php
# Autor:    Raphael Borborema
# Data:     02/04/2012
# Objetivo: Alterar Licitacoes de Compra do sistema no novo formato
##########################################################################
# Autor:	Igor Duarte
# Data:		30/11/2012 - alteração do filtro utilizado em $VALOREXERCICIO
##########################################################################
# Autor:	Igor Duarte
# Data:		19/12/2012 - modificações (CR 19750)
##########################################################################
#-------------------------------------------------------------------------
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------

$programa = "CadLicitacaoAlterarNovo.php";

# Acesso ao arquivo de funções #
require_once("../compras/funcoesCompras.php");
require_once("funcoesComplementaresLicitacao.php");

# Executa o controle de segurança #
session_start();
Seguranca();

AddMenuAcesso('/compras/ConsAcompSolicitacaoCompra.php');
AddMenuAcesso('/compras/InfPreenchimentoBloqueios.php' );
AddMenuAcesso('/licitacoes/CadLicitacaoConsultarSolicitacao.php');

# Abrindo Conexão
$db = Conexao();
$dbOracle = ConexaoOracle();

#definindo data-hora atual
$DataAtual	= date("Y-m-d H:i:s");

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST"){
	$Botao                    	= $_POST['Botao'];
	$Processo                 	= $_POST['Processo'];
	$ProcessoAno              	= $_POST['ProcessoAno'];
	$ComissaoCodigo           	= $_POST['ComissaoCodigo'];
	$ComissaoDescricao        	= $_POST['ComissaoDescricao'];
	$ModalidadeCodigo         	= $_POST['ModalidadeCodigo'];
	$ModalidadeCodigoAntes    	= $_POST['ModalidadeCodigoAntes'];
	$RegistroPreco			  	= $_POST['RegistroPreco'];
	$Licitacao                	= $_POST['Licitacao'];
	$LicitacaoAntes           	= $_POST['LicitacaoAntes'];
	$LicitacaoAno             	= $_POST['LicitacaoAno'];
	$LicitacaoDtAbertura      	= trim($_POST['LicitacaoDtAbertura']);
	$LicitacaoHoraAbertura    	= trim($_POST['LicitacaoHoraAbertura']);
	$OrgaoLicitanteCodigo     	= $_POST['OrgaoLicitanteCodigo'];
	$OrgaoLicitanteDescricao  	= $_POST['OrgaoLicitanteDescricao'];
	$OrgaoLicitanteCodigoAntes 	= $_POST['OrgaoLicitanteCodigoAntes'];
	$LicitacaoUltAlteracao   	= $_POST['LicitacaoUltAlteracao'];
	$LicitacaoObjeto         	= strtoupper2(trim($_POST['LicitacaoObjeto']));
	$NCaracteres              	= $_POST['NCaracteres'];
	$ValorTotal               	= $_POST['ValorTotal'];
	$ValorTrpItem			  	= $_POST['ValorTrpItem'];
	$ValorTotalAntes          	= $_POST['ValorTotalAntes'];
	$Critica 				  	= $_POST['Critica'];
	$GeraContrato			  	= $_POST['GeraContrato'];
	$SeqSolicitacoes          	= $_POST["SeqSolicitacoes"];
	$idSolicitacao			  	= $_POST['idSolicitacao'];
	$SeqSolicitacoesAnterior  	= $_POST['SeqSolicitacoesAnterior'];
	$lote						= $_POST['lote'];

	$intCodUsuario	= $_SESSION['_cusupocodi_'];
	$Data          	= date("Y-m-d H:i:s");
	$isDotacao		= ($RegistroPreco == 'S')?true:false;

	#Bloqueio
	$Bloqueios 			= $_POST['Bloqueios'];
	$BloqueiosCheck 	= $_POST['BloqueiosCheck'];
	$BloqueioAno		= $_POST['BloqueioAno'];
	$BloqueioOrgao 		= $_POST['BloqueioOrgao'];
	$BloqueioUnidade 	= $_POST['BloqueioUnidade'];
	$BloqueioDestinacao = $_POST['BloqueioDestinacao'];
	$BloqueioSequencial = $_POST['BloqueioSequencial'];

	#Dotação
	$DotacaoAno 					= $_POST['DotacaoAno'];
	$DotacaoOrgao 					= $_POST['DotacaoOrgao'];
	$DotacaoUnidade 				= $_POST['DotacaoUnidade'];
	$DotacaoFuncao 					= $_POST['DotacaoFuncao'];
	$DotacaoSubfuncao 				= $_POST['DotacaoSubfuncao'];
	$DotacaoPrograma 				= $_POST['DotacaoPrograma'];
	$DotacaoTipoProjetoAtividade 	= $_POST['DotacaoTipoProjetoAtividade'];
	$DotacaoProjetoAtividade 		= $_POST['DotacaoProjetoAtividade'];
	$DotacaoElemento1 				= $_POST['DotacaoElemento1'];
	$DotacaoElemento2 				= $_POST['DotacaoElemento2'];
	$DotacaoElemento3 				= $_POST['DotacaoElemento3'];
	$DotacaoElemento4 				= $_POST['DotacaoElemento4'];
	$DotacaoFonte 					= $_POST['DotacaoFonte'];

	# Valores dos itens
	$arrOrdem 						= $_POST['ordem'];
	$arrQuantidadeItem				= $_POST['quantidadeItem'];
	$arrValorEstimadoItem			= $_POST["valorEstimadoItem"];
	$arrValorTotalItem  			= $_POST['valorTotalItem'];
	$arrQuantidadeExercicioItem  	= $_POST['quantidadeExercicioItem'];
	$arrValorExercicioItem  		= $_POST['valorExercicioItem'];
	$arrTipoItens					= $_POST['tipoItem'];
	$arrCodMaterialServico 			= $_POST['codRedItem'];
	$arrDotacaoBloqueio  			= $_POST['dotacaoBloqueio'];
}
else{
	$Processo		= $_GET['Processo'];
	$ProcessoAno    = $_GET['ProcessoAno'];
	$ComissaoCodigo = $_GET['ComissaoCodigo'];
	$acao 			= "Alterar";
}

if($Critica==0){
	/* Carregando dados da licitacao */
	$sql    = "SELECT A.CMODLICODI, A.CLICPOCODL, A.ALICPOANOL, A.TLICPODHAB, ";
	$sql   .= "       A.XLICPOOBJE, A.FLICPOSTAT, B.ECOMLIDESC, A.TLICPOULAT, ";
	$sql   .= "       A.CORGLICODI, C.EORGLIDESC, A.VLICPOVALE, A.FLICPOREGP , A.FLICPOCONT ";
	$sql   .= "  FROM SFPC.TBLICITACAOPORTAL A, SFPC.TBCOMISSAOLICITACAO B, SFPC.TBORGAOLICITANTE C ";
	$sql   .= " WHERE A.CLICPOPROC = $Processo AND A.ALICPOANOP = $ProcessoAno ";
	$sql   .= "   AND A.CCOMLICODI = $ComissaoCodigo AND A.CCOMLICODI = B.CCOMLICODI ";
	$sql   .= "   AND A.CGREMPCODI = ".$_SESSION['_cgrempcodi_']." AND A.CORGLICODI = C.CORGLICODI";
	$result = $db->query($sql);

	if( PEAR::isError($result) ){
		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
	}
	else{
		while( $Linha = $result->fetchRow() ){
			$ModalidadeCodigo        	= $Linha[0];
			$ModalidadeCodigoAntes   	= $ModalidadeCodigo;
			$Licitacao               	= substr($Linha[1] + 10000,1);
			$LicitacaoAntes          	= $Licitacao;
			$LicitacaoAno            	= $Linha[2];
			$LicitacaoDtAbertura     	= substr($Linha[3],8,2) ."/". substr($Linha[3],5,2) ."/". substr($Linha[3],0,4);
			$LicitacaoHoraAbertura   	= substr($Linha[3],11,5);
			$LicitacaoObjeto         	= strtoupper2(trim($Linha[4]));
			$NCaracteres             	= strlen($LicitacaoObjeto);
			$LicitacaoStatus         	= $Linha[5];
			$ComissaoDescricao       	= $Linha[6];
			$LicitacaoUltAlteracao   	= substr($Linha[7],8,2) ."/". substr($Linha[7],5,2) ."/". substr($Linha[7],0,4) ." ". substr($Linha[8],11,5);
			$OrgaoLicitanteCodigo    	= $Linha[8];
			$OrgaoLicitanteCodigoAntes  = $OrgaoLicitanteCodigo;
			$OrgaoLicitanteDescricao 	= $Linha[9];
			$ValorTotal              	= str_replace(".",",",$Linha[10]);//VERIFICAR AQUI - ValorTotal
			$ValorTotalAntes         	= $Linha[10];
			$RegistroPreco           	= $Linha[11];
			$GeraContrato			 	= $Linha[12];
		}
	}
}

if($Botao =="Excluir"){
	//Verificar a fase do processo licitatório
	//E se não houver fase para o processo licitarorio ????
	$sql    = "SELECT CFASESCODI  FROM SFPC.TBFASELICITACAO ";
	$sql   .= " WHERE CLICPOPROC = $Processo AND ALICPOANOP = $ProcessoAno ";
	$sql   .= "   AND CGREMPCODI = ".$_SESSION['_cgrempcodi_']." AND CCOMLICODI = $ComissaoCodigo ";
	$sql   .= "   AND CORGLICODI = $OrgaoLicitanteCodigoAntes ORDER BY TFASELULAT DESC ";

	$result = $db->query($sql);

	if( PEAR::isError($result) ){
		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
	}
	else{
		$Row = $result->fetchRow();
		//Se a ultima fase for diferente de 1 , mostras menssagem
		if($Row[0]!=null && $Row[0]!=1){
			$Mens      = 1;
			$Tipo      = 2;
			$Mensagem .= "A fase desta licitação não permite a exclusão ou alteração da solicitação de compra. Proceda a revogação, anulação ou cancelamento da Licitação";
		}
	}

	# Verifica se a Licitacao está relacionada com algum documento #
	$db     = Conexao();
    $sql    = "	SELECT COUNT(*) FROM SFPC.TBDOCUMENTOLICITACAO ";
    $sql   .= " WHERE CLICPOPROC = $Processo AND ALICPOANOP = $ProcessoAno ";
	$sql   .= " AND CCOMLICODI = $ComissaoCodigo AND CGREMPCODI = ".$_SESSION['_cgrempcodi_']."";
	$result = $db->query($sql);

	if( PEAR::isError($result) ){
			ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
	}
	else{
		while( $Linha = $result->fetchRow() ){
			$QtdDocumentos = $Linha[0];
		}
		if( $QtdDocumentos > 0 ){
			$Mens     = 1;
			$Tipo     = 2;
			$Mensagem = "Exclusão Cancelada!<br>Licitação Relacionada com ($QtdDocumentos) Documento(s)";
		}
	}

	# Verifica se a Licitacao está relacionada com alguma fase #
	$sql    = "SELECT COUNT(*) FROM SFPC.TBFASELICITACAO ";
	$sql   .= " WHERE CLICPOPROC = $Processo AND ALICPOANOP = $ProcessoAno ";
	$sql   .= "   AND CCOMLICODI = $ComissaoCodigo AND CGREMPCODI = ".$_SESSION['_cgrempcodi_']."";
	$result = $db->query($sql);

	if( PEAR::isError($result) ){
	    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
	}
	else{
		while( $Linha = $result->fetchRow() ){
			$QtdFases = $Linha[0];
		}
		if( $QtdFases > 0 ){
			if( $Mens == 1 ){
				$Mensagem .= "<br>";
			}
			else {
				$Mensagem .= "Exclusão Cancelada!<br>";
			}
			$Mens      = 1;
			$Mensagem .= "Licitação Relacionada com ($QtdFases) Fase(s)";
		}
	}

	# Verifica se a Licitacao está relacionada com algum resultado #
	$sql    = "SELECT COUNT(*) FROM SFPC.TBRESULTADOLICITACAO ";
	$sql   .= " WHERE CLICPOPROC = $Processo AND ALICPOANOP = $ProcessoAno ";
	$sql   .= "   AND CCOMLICODI = $ComissaoCodigo AND CGREMPCODI = ".$_SESSION['_cgrempcodi_']."";
	$result = $db->query($sql);

	if( PEAR::isError($result) ){
	    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
	}
	else{
		while( $Linha = $result->fetchRow() ){
			$QtdResultados = $Linha[0];
		}
		if( $QtdResultados > 0 ){
			if ($Mens == 1){
				$Mensagem .= "<br>";
			}
			else {
				$Mensagem .= "Exclusão Cancelada!<br>";
			}
			$Mens      = 1;
			$Mensagem .= "Licitação Relacionada com ($QtdResultados) Resultado(s)";
		}
	}

	# Verifica se a Licitacao está relacionada com alguma lista de solicitante #
	$sql    = "SELECT COUNT(*) AS Qtd FROM SFPC.TBLISTASOLICITAN ";
	$sql   .= " WHERE CLICPOPROC = $Processo AND ALICPOANOP = $ProcessoAno ";
	$sql   .= "   AND CCOMLICODI = $ComissaoCodigo AND CGREMPCODI = ".$_SESSION['_cgrempcodi_']."";
	$result = $db->query($sql);

	if( PEAR::isError($result) ){
	    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
	}
	else{
		while( $Linha = $result->fetchRow() ){
			$QtdLista = $Linha[0];
		}
		if( $QtdLista > 0 ){
			if ($Mens == 1){
				$Mensagem .= "<br>";
			}
			else {
				$Mensagem .= "Exclusão Cancelada!<br>";
			}
			$Mens = 1;$Tipo = 2;
			$Mensagem .= "Licitação Relacionada com ($QtdLista) Solicitante(s)";
		}
	}

	if($Mens==0){
		$db->query("BEGIN TRANSACTION");

		//Excluindo a fase ligada a essa licitacao
		$sql    = "DELETE FROM SFPC.TBFASELICITACAO ";
		$sql   .= " WHERE CLICPOPROC = $Processo AND ALICPOANOP = $ProcessoAno ";
		$sql   .= "   AND CGREMPCODI = ".$_SESSION['_cgrempcodi_']." AND CCOMLICODI = $ComissaoCodigo ";
		$sql   .= "   AND CORGLICODI = $OrgaoLicitanteCodigoAntes";
		$res = executarTransacao($db, $sql);

		if( PEAR::isError($res) ){
			cancelarTransacao($db);
			ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
		}

		//Atualisando as solicitacoes antigas para ENCAMINHADA
		$strSolicitacoesAnterior = implode(",",$SeqSolicitacoesAnterior);
		$sql = "UPDATE SFPC.TBSOLICITACAOCOMPRA SET CSITSOCODI = 8 WHERE  CSOLCOSEQU IN ($strSolicitacoesAnterior) ";

		$res = executarTransacao($db, $sql);

		if( PEAR::isError($res) ){
			cancelarTransacao($db);
			ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
		}
		// Atualizando o historico das licitacoes antigas
		foreach($SeqSolicitacoesAnterior as $CSOLCOSEQU){
			// Como vou fazer a limpeza do historioc TBHISTSITUACAOSOLICITACAO
			$sqlHist = "INSERT INTO
			             SFPC.TBHISTSITUACAOSOLICITACAO
						 (CSOLCOSEQU, THSITSDATA, CSITSOCODI, CUSUPOCODI)
						 VALUES
						($CSOLCOSEQU,'$DataAtual', 8, $intCodUsuario)";

			$resHist = executarTransacao($db, $sqlHist);

			if( PEAR::isError($resHist) ){
				cancelarTransacao($db);
				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlHist\n\n$DescErroEmail ($CodErroEmail)");
				$transacao = false;
			}
		}

		//Removendo as SOLICITACOES DA LICITACAO
		$sql = "DELETE FROM SFPC.TBSOLICITACAOLICITACAOPORTAL SOL
				WHERE  SOL.CLICPOPROC = $Processo AND SOL.ALICPOANOP = $ProcessoAno
				AND SOL.CCOMLICODI = $ComissaoCodigo AND SOL.cgrempcodi =". $_SESSION['_cgrempcodi_'] ;

		$res = executarTransacao($db, $sql);
		if( PEAR::isError($res) ){
			cancelarTransacao($db);
			ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
			$transacao = false;
		}

		//Removendo as dotacoes e bloqueios
		$sql = "DELETE FROM SFPC.TBITEMLICITACAODOTACAO ITEM WHERE
				ITEM.clicpoproc = $Processo
				AND    ITEM.alicpoanop = $ProcessoAno
				AND    ITEM.cgrempcodi = ".$_SESSION['_cgrempcodi_']."
				AND    ITEM.ccomlicodi = $ComissaoCodigo
				AND    ITEM.corglicodi = $OrgaoLicitanteCodigoAntes ";

		$res = executarTransacao($db, $sql);
		if( PEAR::isError($res) ){
			cancelarTransacao($db);
			ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
		}

		$sql = "DELETE FROM SFPC.TBITEMLICITACAOBLOQUEIO ITEM WHERE
				  ITEM.clicpoproc = $Processo
				AND    ITEM.alicpoanop = $ProcessoAno
				AND    ITEM.cgrempcodi = ".$_SESSION['_cgrempcodi_']."
				AND    ITEM.ccomlicodi = $ComissaoCodigo
				AND    ITEM.corglicodi = $OrgaoLicitanteCodigoAntes ";

		$res = executarTransacao($db, $sql);

		if( PEAR::isError($res) ){
			cancelarTransacao($db);
			ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
		}

		//Removendo os intens da licitacao
		$sql = "DELETE FROM SFPC.TBITEMLICITACAOPORTAL ITEM WHERE
				  ITEM.clicpoproc = $Processo
				AND    ITEM.alicpoanop = $ProcessoAno
				AND    ITEM.cgrempcodi = ".$_SESSION['_cgrempcodi_']."
				AND    ITEM.ccomlicodi = $ComissaoCodigo
				AND    ITEM.corglicodi = $OrgaoLicitanteCodigo ";

		$res = executarTransacao($db, $sql);
		if( PEAR::isError($res) ){
			cancelarTransacao($db);
			ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
		}

		$sql  	= "DELETE FROM  SFPC.TBLICITACAOPORTAL  ";
		$sql   .= " WHERE CLICPOPROC = $Processo AND ALICPOANOP = $ProcessoAno ";
		$sql   .= "   AND CCOMLICODI = $ComissaoCodigo AND CGREMPCODI = ".$_SESSION['_cgrempcodi_']." ";

		$res = executarTransacao($db, $sql);

		if( PEAR::isError($res) ){
			cancelarTransacao($db);
			ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
			$transacao = false;
		}

		$db->query("COMMIT");
		$db->query("END TRANSACTION");

		# Envia mensagem para página selecionar #
		$Mensagem = urlencode("Licitação Excluida com Sucesso");
		$Url = "CadLicitacaoSelecionar.php?Mensagem=$Mensagem&Mens=1&Tipo=1&Critica=0";
		if (!in_array($Url,$_SESSION['GetUrl'])){
			$_SESSION['GetUrl'][] = $Url;
		}
		header("location: $Url");
		exit;
	}
}
elseif( $Botao == "AlterarLicitacao"){
	$Mens		= 0;
	$Mensagem   = "Informe: ";

	if ( $OrgaoLicitanteCodigo == "" ){
		$Mens      = 1;
		$Tipo      = 2;
		$Mensagem .= "<a href=\"javascript:document.formulario.ComissaoCodigo.focus();\" class=\"titulo2\">Orgao Licitante</a><br>";
	}
	if ( $ComissaoCodigo == "" ){
		$Mens      = 1;
		$Tipo      = 2;
		$Mensagem .= "<a href=\"javascript:document.formulario.ComissaoCodigo.focus();\" class=\"titulo2\">Comissao<br></a>";
	}
	if ( $ModalidadeCodigo == "" ){
		$Mens      = 1;
		$Tipo      = 2;
		$Mensagem .= "<a href=\"javascript:document.formulario.ModalidadeCodigo.focus();\" class=\"titulo2\">Informe Modalidade<br></a>";
	}

	# O orgão licitante não pode mudar pois faz parte da chave
	if($OrgaoLicitanteCodigoAntes!=$OrgaoLicitanteCodigo){
		$Mens      = 1;
		$Tipo      = 2;
		$Mensagem .= "O orgão licitante não pode mudar.";
	}

	//Validando itens
	foreach($arrOrdem as $ordem){
		if($Mens==0){
			if(moeda2float($arrValorTotalItem[$ordem])==""||moeda2float($arrValorTotalItem[$ordem])<0){
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "O valor total estimado do item [$ordem] é inválido ";
			}

			if($arrQuantidadeExercicioItem[$ordem]){
				if(moeda2float($arrQuantidadeExercicioItem[$ordem])>moeda2float($arrQuantidadeItem[$ordem])){
					$Mens      = 1;
					$Tipo      = 2;
					$Mensagem .= "Quantidade no exercício não pode ser maior que a quantidade de itens, no item[$ordem] ";
				}
			}

			if($arrTipoItens[$ordem]=="CADUM"){
				$arrTipoCod[$ordem] = TIPO_ITEM_MATERIAL;
			}
			elseif($arrTipoItens[$ordem]=="CADUS"){
				$arrTipoCod[$ordem] = TIPO_ITEM_SERVICO;
			}
			else{
				adicionarMensagem("<a href=\"javascript:void(0);\" class='titulo2'>O tipo do item $ordem não foi definido </a><br>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
			}

			$arrayValiDotacaoBloqueio[$ordem]["posicaoItem"] = $ordem;
			$arrayValiDotacaoBloqueio[$ordem]["posicao"] = $ordem;
			$arrayValiDotacaoBloqueio[$ordem]["codigo"] = $arrCodMaterialServico[$ordem];
			$arrayValiDotacaoBloqueio[$ordem]["tipo"] = $arrTipoCod[$ordem];
			$arrayValiDotacaoBloqueio[$ordem]["quantidadeItem"] = moeda2float($arrQuantidadeItem[$ordem]);
			$arrayValiDotacaoBloqueio[$ordem]["valorItem"] = moeda2float($arrValorEstimadoItem[$ordem]);
			//$arrayValiDotacaoBloqueio[$ordem]["reservas"] = $arrDotacaoBloqueio[$ordem];
			$arrayValiDotacaoBloqueio[$ordem]["reservas"] = $arrDotacaoBloqueio;
		}
	}

	//Fazendo a validacao de bloqueio e dotacao
	if($RegistroPreco=="S"){
		$tipoReserva = TIPO_RESERVA_ORCAMENTARIA_DOTACAO;
		$isDotacao	= true;
	}
	else{
		$tipoReserva = TIPO_RESERVA_ORCAMENTARIA_BLOQUEIO;
		$isDotacao = false;
	}

	/*
	var_dump($arrDotacaoBloqueio);
	var_dump($arrayValiDotacaoBloqueio); die;
	*/
	/**
	 * VALIDAÇÃO DA DOTAÇÃO BLOQEUIO
	 */
	try{
		if(!is_null($arrayValiDotacaoBloqueio)){
			//validarReservaOrcamentaria($db, $dbOracle, $tipoReserva, $arrDotacaoBloqueio, $arrayValiDotacaoBloqueio, "selectDotBloq");
			validarReservaOrcamentariaBKS($db, $dbOracle, $tipoReserva, $arrDotacaoBloqueio, $arrayValiDotacaoBloqueio, "selectDotBloq");
		}
	}
	catch(ExcecaoReservaInvalidaEmItemScc $e){
		$pos = $e->posicaoItemArray;
		adicionarMensagem("<a href=\"javascript:document.formulario.getElementById('arrValorEstimadoItem[$pos]').focus();\" class='titulo2'>".$e->getMessage()."</a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
	}
	catch(ExcecaoPendenciasUsuario $e){
		adicionarMensagem($e->getMessage(), $GLOBALS["TIPO_MENSAGEM_ERRO"]);
	}
	catch (Exception $e){
		adicionarMensagem($e->getMessage(), $GLOBALS["TIPO_MENSAGEM_ERRO"]);
	}
	/**
	* VALIDAÇÃO DA DOTAÇÃO BLOQEUIO
	*/

	//Verificar a fase do processo licitatório
	$sql    = "SELECT CFASESCODI  FROM SFPC.TBFASELICITACAO ";
	$sql   .= " WHERE CLICPOPROC = $Processo AND ALICPOANOP = $ProcessoAno ";
	$sql   .= "   AND CGREMPCODI = ".$_SESSION['_cgrempcodi_']." AND CCOMLICODI = $ComissaoCodigo ";
	$sql   .= "   AND CORGLICODI = $OrgaoLicitanteCodigo ORDER BY TFASELULAT DESC ";

	$result = $db->query($sql);

	if( PEAR::isError($result) ){
		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
	}
	else{
		$Row = $result->fetchRow();
		//Se a ultima fase for diferente de 1 , ou não existir fase mostras menssagem
		if($Row[0]!=null && $Row[0]!=1){
	     $Mens      = 1;
		 $Tipo      = 2;
		 $Mensagem .= "A fase desta licitação não permite a exclusão ou alteração da solicitação de compra. Proceda a revogação, anulação ou cancelamento da Licitação";
		}
	}

	//Verificar o processo licitatório está cadastrado na tabela referencial de preços
	$sql    = "SELECT * FROM SFPC.TBTABELAREFERENCIALPRECOS ";
	$sql   .= " WHERE CLICPOPROC = $Processo AND ALICPOANOP = $ProcessoAno ";
	$sql   .= "   AND CGREMPCODI = ".$_SESSION['_cgrempcodi_']." AND CCOMLICODI = $ComissaoCodigo ";
	$sql   .= "   AND CORGLICODI = $OrgaoLicitanteCodigoAntes";

	$result = $db->query($sql);

	if( PEAR::isError($result) ){
		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
	}
	else{
		$Row = $result->numRows();
		//Se a ultima fase for diferente de 1 , ou não existir fase mostras menssagem
		if($Row > 0 && $Row != null){
			$Mens      = 1;
			$Tipo      = 2;
			$Mensagem .= "Esta alteração não pode ser executada, pois os Itens desta licitação já estão cadastrados na tabela TRP";
		}
	}

	//Se validou
	if($Mens==0){
		$db->query("BEGIN TRANSACTION");
		//Se são novas solicitacoes

		if($idSolicitacao!=""){
			//Atualizando as solicitacoes antigas para ENCAMINHADA
			$strSolicitacoesAnterior = implode(",",$SeqSolicitacoesAnterior);
			$sql = "UPDATE SFPC.TBSOLICITACAOCOMPRA SET CSITSOCODI = 8 WHERE  CSOLCOSEQU IN ($strSolicitacoesAnterior) ";
			$res = executarTransacao($db, $sql);

			if( PEAR::isError($res) ){
				cancelarTransacao($db);
				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
			}
			// Atualizando o historico das licitacoes antigas
			foreach($SeqSolicitacoesAnterior as $CSOLCOSEQU){
				// Como vou fazer a limpeza do historioc TBHISTSITUACAOSOLICITACAO
				 $sqlHist = "INSERT INTO
				             SFPC.TBHISTSITUACAOSOLICITACAO
							 (CSOLCOSEQU, THSITSDATA, CSITSOCODI, CUSUPOCODI)
							 VALUES
							($CSOLCOSEQU,'$DataAtual', 8, $intCodUsuario)";

				$resHist = executarTransacao($db, $sqlHist);
				if( PEAR::isError($resHist) ){
					cancelarTransacao($db);
					ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlHist\n\n$DescErroEmail ($CodErroEmail)");
					$transacao = false;
				}
			}
		}

		//Removendo as SOLICITACOES DA LICITACAO
		$sql = "DELETE FROM SFPC.TBSOLICITACAOLICITACAOPORTAL SOL
						WHERE  SOL.CLICPOPROC = $Processo AND SOL.ALICPOANOP = $ProcessoAno
						AND SOL.CCOMLICODI = $ComissaoCodigo AND SOL.cgrempcodi =". $_SESSION['_cgrempcodi_'] ;
		$res = executarTransacao($db, $sql);

		if( PEAR::isError($res) ){
			cancelarTransacao($db);
			ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
			$transacao = false;
		}

		//Removendo as dotacoes e bloqueios
		$sql = "DELETE FROM SFPC.TBITEMLICITACAODOTACAO ITEM WHERE
				  ITEM.clicpoproc = $Processo
				AND    ITEM.alicpoanop = $ProcessoAno
				AND    ITEM.cgrempcodi = ".$_SESSION['_cgrempcodi_']."
				AND    ITEM.ccomlicodi = $ComissaoCodigo
				AND    ITEM.corglicodi = $OrgaoLicitanteCodigoAntes ";
		$res = executarTransacao($db, $sql);

		if( PEAR::isError($res) ){
			cancelarTransacao($db);
			ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
		}

		$sql = "DELETE FROM SFPC.TBITEMLICITACAOBLOQUEIO ITEM WHERE
				  ITEM.clicpoproc = $Processo
				AND    ITEM.alicpoanop = $ProcessoAno
				AND    ITEM.cgrempcodi = ".$_SESSION['_cgrempcodi_']."
				AND    ITEM.ccomlicodi = $ComissaoCodigo
				AND    ITEM.corglicodi = $OrgaoLicitanteCodigoAntes ";


		$res = executarTransacao($db, $sql);

		if( PEAR::isError($res) ){
			cancelarTransacao($db);
			ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
		}

		//Removendo os intens da licitacao
		$sql = "DELETE FROM SFPC.TBITEMLICITACAOPORTAL ITEM WHERE
						  ITEM.clicpoproc = $Processo
						AND    ITEM.alicpoanop = $ProcessoAno
						AND    ITEM.cgrempcodi = ".$_SESSION['_cgrempcodi_']."
						AND    ITEM.ccomlicodi = $ComissaoCodigo
						AND    ITEM.corglicodi = $OrgaoLicitanteCodigoAntes ";

		$res = executarTransacao($db, $sql);

		//var_dump($sql);

		if( PEAR::isError($res) ){
			cancelarTransacao($db);
			ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
		}

		# atualizando Licitacao #
		$LicitacaoDtAberturaFinal = substr($LicitacaoDtAbertura,6,4)."-".substr($LicitacaoDtAbertura,3,2)."-".substr($LicitacaoDtAbertura,0,2);
		$DataHoraAbertura         = "$LicitacaoDtAberturaFinal $LicitacaoHoraAbertura:00";

		if( $ValorTotal != "" ){
			$ValorTotal = moeda2float($ValorTotal);
		}

		$sql  = "UPDATE SFPC.TBLICITACAOPORTAL SET ";
		$sql .= "CMODLICODI = $ModalidadeCodigo ,";/* CMODLICODI - Código da Modalidade		$ModalidadeCodigo */
		$sql .= "CLICPOCODL = $Licitacao  ,";/* CLICPOCODL - Código da Licitação de acordo com a Comissão de Licitação e Modalidade do Processo Licitatório 	$Licitacao */
		$sql .= "ALICPOANOL = $ProcessoAno ,";/* ALICPOANOL - Ano da Licitação de acordo com a Comissão de Licitação e Modalidade do Processo Licitatório		$AnoDaLicitacao */
		$sql .= "TLICPODHAB = '$DataHoraAbertura' , ";/* TLICPODHAB - Data de e Hora de Abertura do Processo Licitatório 	$DataHoraAbertura */
		$sql .= "CORGLICODI = $OrgaoLicitanteCodigo , ";/* CORGLICODI   */
		$sql .= "XLICPOOBJE = '$LicitacaoObjeto' , ";/* XLICPOOBJE - Objeto do Processo Licitatório		$Objeto */
		$sql .= "VLICPOVALE = $ValorTotal , ";/* VLICPOVALE - Valor Estimado do Processo Licitatório	$ValorTotalEstimado */
		$sql .= "CUSUPOCODI = ".$_SESSION['_cusupocodi_']." , ";/* CUSUPOCODI - Código do Usuário Responsável	$_SESSION['_cusupocodi_'] */
		$sql .= "TLICPOULAT = '$DataAtual' , ";/* TLICPOULAT - Data/Hora da Última Alteração	now() */
		$sql .= "FLICPOCONT = '$GeraContrato' , ";/* GERA CONTRATO  */

		if( $RegistroPreco == "S" ){
			$sql .= " FLICPOREGP = 'S' ";
		}
		else{
			$sql .= "  FLICPOREGP = 'N' ";
		}

		$sql   .= " WHERE CLICPOPROC = $Processo AND ALICPOANOP = $ProcessoAno ";
		$sql   .= "   AND CCOMLICODI = $ComissaoCodigo AND CGREMPCODI = ".$_SESSION['_cgrempcodi_']." ";
		$res 	= executarTransacao($db, $sql);

		if( PEAR::isError($res) ){
			cancelarTransacao($db);
			ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
			$transacao = false;
		}

		//Inserindo as novas solicitacoes na licitacao
		foreach($SeqSolicitacoes as $CSOLCOSEQU){
			$sql = "INSERT INTO SFPC.TBSOLICITACAOLICITACAOPORTAL
			(CSOLCOSEQU,  CLICPOPROC, 		 ALICPOANOP, 	  CGREMPCODI, 	 CCOMLICODI, 		CORGLICODI, 		   CUSUPOCODI, 				  TSOLCLULAT) VALUES
			($CSOLCOSEQU, $Processo, $ProcessoAno, ".$_SESSION['_cgrempcodi_'].", $ComissaoCodigo, $OrgaoLicitanteCodigo, $intCodUsuario, '$DataAtual')";

			$res = executarTransacao($db, $sql);
			if( PEAR::isError($res) ){
				cancelarTransacao($db);
				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
				$transacao = false;
			}
			//Atualizando o status da solicitacoes de compra
			$sqlUp = "UPDATE SFPC.TBSOLICITACAOCOMPRA
			SET CSITSOCODI = 9 WHERE CSOLCOSEQU = $CSOLCOSEQU";

			$resUp = executarTransacao($db, $sqlUp);
			if( PEAR::isError($resUp) ){
				cancelarTransacao($db);
				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlUp\n\n$DescErroEmail ($CodErroEmail)");
				$transacao = false;
			}
			//So insiro no historico caso sejam novas solicitacoes
			if($idSolicitacao!=""){
				$sqlHist = "INSERT INTO
				SFPC.TBHISTSITUACAOSOLICITACAO
				(CSOLCOSEQU, THSITSDATA, CSITSOCODI, CUSUPOCODI)
				VALUES
				($CSOLCOSEQU,'$DataAtual', 9, $intCodUsuario)";

				$resHist = executarTransacao($db, $sqlHist);
				if( PEAR::isError($resHist) ){
					cancelarTransacao($db);
					ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlHist\n\n$DescErroEmail ($CodErroEmail)");
					$transacao = false;
				}
			}
		}

		#Inserindo os itens
		foreach($arrOrdem as $ordem => $aitelporde){
			if($arrTipoItens[$ordem]=="CADUM"){
				$codMaterial = $arrCodMaterialServico[$ordem];
				$codServico = "null";
			}
			else{
				$codMaterial = "null";
				$codServico = $arrCodMaterialServico[$ordem];
			}
			//Se não tiver valor e quantidade exercicio colocar null ????
			//Oque eh pra inserir na tabela quando nao for registro de preco , o banco esta obrigando ter valor
			if(!isset($arrValorExercicioItem[$ordem])){
				$arrValorExercicioItem[$ordem] = 0.0;
			}
			else{
				$arrValorExercicioItem[$ordem] = moeda2float($arrValorExercicioItem[$ordem]);
			}

			if(!isset($arrQuantidadeExercicioItem[$ordem])){
				$arrQuantidadeExercicioItem[$ordem] = 0.0;
			}
			else{
				$arrQuantidadeExercicioItem[$ordem] = moeda2float($arrQuantidadeExercicioItem[$ordem]);
			}


			/*var_dump($ordem);
			var_dump($aitelporde);
			var_dump($arrOrdem );
			var_dump($lote);
			die('aqui');*/

			#Gravar os dados em SFPC.TBITEMLICITACAOPORTAL a partir de SFPC.TBITEMSOLICITACAOCOMPRA;
			$sql = "INSERT INTO SFPC.TBITEMLICITACAOPORTAL (";
			$sql .= "clicpoproc, "; // clicpoproc IS 'Código do Processo Licitatório';
			$sql .= "alicpoanop, "; // alicpoanop IS 'Ano do Processo Licitatório';
			$sql .= "cgrempcodi, "; // cgrempcodi IS 'Código do Grupo';
			$sql .= "ccomlicodi, "; // ccomlicodi IS 'Código da Comissão';
			$sql .= "corglicodi, "; // corglicodi IS 'Código do Órgão Licitante';
			$sql .= "citelpsequ, "; // citelpsequ IS 'Código sequencial dos itens da licitação';
			$sql .= "cmatepsequ, "; // cmatepsequ IS 'Código Sequencial do Material';
			$sql .= "cservpsequ, "; // cservpsequ IS 'Código sequencial do serviço';
			$sql .= "aitelporde, "; // aitelporde IS 'Ordem do item';
			$sql .= "aitelpqtso, "; // aitelpqtso IS 'Quantidade de material solicitada para a licitação';
			$sql .= "vitelpunit, "; // vitelpunit IS 'Valor unitário estimado';
			$sql .= "vitelpvexe, "; // vitelpvexe IS 'Valor no exercício do item';
			$sql .= "aitelpqtex, "; // 'Quantidade no exercício do item';

			if($lote[$ordem] != 0 && $lote[$ordem] != null){
				$sql .= "citelpnuml, "; // Número do Lote
			}

			$sql .= "cusupocodi, "; // cusupocodi IS 'Código do Usuário Responsável pela Última Alteração
			$sql .= "titelpulat "; // titelpulat IS 'Data/Hora da Última Alteração';
			$sql .= ") VALUES (";
			$sql .= "$Processo, ";
			$sql .= "$ProcessoAno, ";
			$sql .= $_SESSION['_cgrempcodi_']. ",";
			$sql .= "$ComissaoCodigo, ";
			$sql .= "$OrgaoLicitanteCodigo, ";
			$sql .= "$ordem, ";
			$sql .= "$codMaterial ,";
			$sql .= "$codServico ,";
			$sql .= "$aitelporde ,";
			$sql .= moeda2float($arrQuantidadeItem[$ordem]) . ",";
			$sql .= moeda2float($arrValorEstimadoItem[$ordem])  . ",";
			$sql .= $arrValorExercicioItem[$ordem] . ",";
			$sql .= $arrQuantidadeExercicioItem[$ordem] . ",";

			if($lote[$ordem] != 0 && $lote[$ordem] != null){
				$sql .= $lote[$ordem] . ", ";
			}
			$sql .= $_SESSION['_cusupocodi_']  ;
			$sql .= ", '$DataAtual' )";

			$resItens = executarTransacao($db, $sql);

			//var_dump($sql); die('aqui');

			if( PEAR::isError($resItens) ){
				cancelarTransacao($db);
				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
				$transacao = false;
			}
			else{
				if($RegistroPreco=="S"){
					foreach($arrDotacaoBloqueio as $strDotacaco){
						$arrayInfoDotacao = getDadosDotacaoOrcamentaria($dbOracle,$strDotacaco);
						$sqlDotacao = "INSERT INTO SFPC.TBITEMLICITACAODOTACAO (";
						$sqlDotacao .= "clicpoproc, "; 		// clicpoproc IS 'Código do Processo Licitatório';
						$sqlDotacao .= "alicpoanop, "; 		// alicpoanop IS 'Ano do Processo Licitatório';
						$sqlDotacao .= "cgrempcodi, "; 		// cgrempcodi IS 'Código do Grupo';
						$sqlDotacao .= "ccomlicodi, "; 		// ccomlicodi IS 'Código da Comissão';
						$sqlDotacao .= "corglicodi, "; 		// corglicodi IS 'Código do Órgão Licitante';
						$sqlDotacao .= "citelpsequ, "; 		// código sequencial dos itens da licitação';
						$sqlDotacao .= "citldounidoorga, "; // citldounidoorga IS Código do Órgão do orçamento';
						$sqlDotacao .= "citldounidocodi, "; // citldounidocodi IS Código da Unidade Orçamentária';
						$sqlDotacao .= "citldotipa, "; 		// citldo IS Tipo ( 1 = Projeto   2= Atividade )
						$sqlDotacao .= "aitldoordt, "; 		// aitldoordt IS Ordem do Projeto ou da Atividade';
						$sqlDotacao .= "citldoele1, "; 		// citldoele1 IS Elemento de Despesa 1 ';
						$sqlDotacao .= "citldoele2, "; 		// citldoele2 IS Elemento de Despesa 2 ';
						$sqlDotacao .= "citldoele3, "; 		// citldoele3 IS Elemento de Despesa 3 ';
						$sqlDotacao .= "citldoele4, "; 		// citldoele4 IS Elemento de Despesa 4 ';
						$sqlDotacao .= "citldofont, "; 		// citldofont IS Fonte de Recursos';
						$sqlDotacao .= "aitldounidoexer "; // aitldounidoexer IS Ano da Unidade Orçamentária';
						$sqlDotacao .= ") VALUES (";
						$sqlDotacao .= "$Processo, ";
						$sqlDotacao .= "$ProcessoAno, ";
						$sqlDotacao .= $_SESSION['_cgrempcodi_']. ",";
						$sqlDotacao .= "$ComissaoCodigo, ";
						$sqlDotacao .= "$OrgaoLicitanteCodigo, ";
						$sqlDotacao .= "$ordem, ";
						$sqlDotacao .= $arrayInfoDotacao['orgao'] ." , ";
						$sqlDotacao .= $arrayInfoDotacao['unidade'] ." , ";
						$sqlDotacao .= $arrayInfoDotacao['tipoProjetoAtividade'] ." , ";
						$sqlDotacao .= $arrayInfoDotacao['projetoAtividade'] ." , ";
						$sqlDotacao .= $arrayInfoDotacao['elemento1'] ." , ";
						$sqlDotacao .= $arrayInfoDotacao['elemento2'] ." , ";
						$sqlDotacao .= $arrayInfoDotacao['elemento3'] ." , ";
						$sqlDotacao .= $arrayInfoDotacao['elemento4'] ." , ";
						$sqlDotacao .= $arrayInfoDotacao['fonte'] ." , ";
						$sqlDotacao .= $arrayInfoDotacao['ano'] ." ) ";

						$resItensDotacao = executarTransacao($db, $sqlDotacao);
						if( PEAR::isError($resItensDotacao) ){
							$transacao = false;
							cancelarTransacao($db);
							ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlDotacao\n\n$DescErroEmail ($CodErroEmail)");
						}
					}
				}
				else{
					foreach($arrDotacaoBloqueio as $strBloqueio){
						$arryInfoBloqueio = getDadosBloqueio($dbOracle,$strBloqueio);

						$sqlBloqueio = "INSERT INTO SFPC.TBITEMLICITACAOBLOQUEIO  (";
						$sqlBloqueio .= "clicpoproc, "; 		// clicpoproc IS 'Código do Processo Licitatório';
						$sqlBloqueio .= "alicpoanop, "; 		// alicpoanop IS 'Ano do Processo Licitatório';
						$sqlBloqueio .= "cgrempcodi, "; 		// cgrempcodi IS 'Código do Grupo';
						$sqlBloqueio .= "ccomlicodi, "; 		// ccomlicodi IS 'Código da Comissão';
						$sqlBloqueio .= "corglicodi, "; 		// corglicodi IS 'Código do Órgão Licitante';
						$sqlBloqueio .= "citelpsequ, "; 		// código sequencial dos itens da licitação';
						$sqlBloqueio .= "aitlblanob, "; 		// Ano do bloqueio Orçamentário SFCO.TBBLOQUEIO
						$sqlBloqueio .= "aitlblnbloq "; 		// Número Sequencial do Bloqueio Orçamentário SFCO.TBBLOQUEIO
						$sqlBloqueio .= ") VALUES (";
						$sqlBloqueio .= "$Processo, ";
						$sqlBloqueio .= "$ProcessoAno, ";
						$sqlBloqueio .= $_SESSION['_cgrempcodi_']. ",";
						$sqlBloqueio .= "$ComissaoCodigo, ";
						$sqlBloqueio .= "$OrgaoLicitanteCodigo, ";
						$sqlBloqueio .= "$ordem, ";
						$sqlBloqueio .= $arryInfoBloqueio['anoChave'] ." , ";
						$sqlBloqueio .= $arryInfoBloqueio['sequencialChave'] ." ) ";

						$resItensBloqueio = executarTransacao($db, $sqlBloqueio);
						if( PEAR::isError($resItensBloqueio) ){
							cancelarTransacao($db);
							ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlBloqueio\n\n$DescErroEmail ($CodErroEmail)");
							$transacao = false;
						}
					}
				}
			}
		}//foreach de itens

		$db->query("COMMIT");
		$db->query("END TRANSACTION");

		# Envia mensagem para página selecionar #
		$Mensagem = urlencode("Licitação Alterada com Sucesso");
		$Url = "CadLicitacaoSelecionar.php?Mensagem=$Mensagem&Mens=1&Tipo=1&Critica=0";
		if (!in_array($Url,$_SESSION['GetUrl'])){
			$_SESSION['GetUrl'][] = $Url;
		}
		header("location: $Url");
		exit;
	}
}
elseif( $Botao == "Licitacao" || ($idSolicitacao !="")) {

	$Mens          = 0;
	$Mensagem      = "Informe: ";
	if( $OrgaoLicitanteCodigo == "" ){
		if ( $Mens == 1 ) {
			$Mensagem .= ", ";
		}
		$Mens      = 1;
		$Tipo      = 2;
		$Mensagem .= "<a href=\"javascript:document.formulario.OrgaoLicitanteCodigo.focus();\" class=\"titulo2\">Órgão Licitante</a>";
	}
	if( $ComissaoCodigo == "" ){
		$Mens      = 1;
		$Tipo      = 2;
		$Mensagem .= "<a href=\"javascript:document.formulario.ComissaoCodigo.focus();\" class=\"titulo2\">Comissão</a>";
	}
	if( $ModalidadeCodigo == "" ){
		if ( $Mens == 1 ) {
			$Mensagem .= ", ";
		}
		$Mens      = 1;
		$Tipo      = 2;
		$Mensagem .= "<a href=\"javascript:document.formulario.ModalidadeCodigo.focus();\" class=\"titulo2\">Modalidade</a>";
	}
	if( $Mens == 0 ){
		if($ModalidadeCodigo != $ModalidadeCodigoAntes){
			# Verifica o máximo número da Licitação #
			$db     = Conexao();
			$sql    = "SELECT MAX(CLICPOCODL) FROM SFPC.TBLICITACAOPORTAL ";
				$sql   .= " WHERE ALICPOANOP = $ProcessoAno AND CGREMPCODI = ".$_SESSION['_cgrempcodi_']." ";
				$sql   .= "   AND CCOMLICODI = $ComissaoCodigo AND CMODLICODI = $ModalidadeCodigo";

				$result = $db->query($sql);

			if( PEAR::isError($result) ){
					ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
			}
			else{
				$Linha = $result->fetchRow();
				if( $Linha[0] == 0 ){
					$Licitacao = 1;
				}
				else{
					$Licitacao = $Linha[0] + 1;
				}
				$Licitacao = substr($Licitacao + 10000,1);
			}
		}
		else{
			$Licitacao = $LicitacaoAntes;
		}
	}
	else{
		$ModalidadeCodigo = "";
	}
}

if( $Botao == "Voltar" ){
	header("location: CadLicitacaoSelecionar.php");
	exit;
}

/**
 * INCLUIR/REMOVER BLOQUEIO/DOTAÇÃO
 */

if($Botao=="IncluirBloqueio"){

	$BloqueioTodos="";

	if($isDotacao){
		if(is_null($DotacaoAno) or $DotacaoAno==""){
			adicionarMensagem("<a href=\"javascript:document.getElementById('DotacaoAno').focus();\" class='titulo2'>Ano da dotação</a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
		}
		if(is_null($DotacaoOrgao) or $DotacaoOrgao==""){
			adicionarMensagem("<a href=\"javascript:document.getElementById('DotacaoOrgao').focus();\" class='titulo2'>Orgão da dotação</a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
		}
		if(is_null($DotacaoUnidade) or $DotacaoUnidade==""){
			adicionarMensagem("<a href=\"javascript:document.getElementById('DotacaoUnidade').focus();\" class='titulo2'>Unidade da dotação</a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
		}
		if(is_null($DotacaoFuncao) or $DotacaoFuncao==""){
			adicionarMensagem("<a href=\"javascript:document.getElementById('DotacaoFuncao').focus();\" class='titulo2'>Função da dotação</a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
		}
		if(is_null($DotacaoSubfuncao) or $DotacaoSubfuncao==""){
			adicionarMensagem("<a href=\"javascript:document.getElementById('DotacaoSubfuncao').focus();\" class='titulo2'>Subfunção da dotação</a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
		}
		if(is_null($DotacaoPrograma) or $DotacaoPrograma==""){
			adicionarMensagem("<a href=\"javascript:document.getElementById('DotacaoPrograma').focus();\" class='titulo2'>Programa da dotação</a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
		}
		if(is_null($DotacaoTipoProjetoAtividade) or $DotacaoTipoProjetoAtividade==""){
			adicionarMensagem("<a href=\"javascript:document.getElementById('DotacaoTipoProjetoAtividade').focus();\" class='titulo2'>Tipo do projeto/Atividade da dotação</a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
		}
		if(is_null($DotacaoProjetoAtividade) or $DotacaoProjetoAtividade==""){
			adicionarMensagem("<a href=\"javascript:document.getElementById('DotacaoProjetoAtividade').focus();\" class='titulo2'>Projeto/Atividade da dotação</a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
		}
		if(is_null($DotacaoElemento1) or $DotacaoElemento1==""){
			adicionarMensagem("<a href=\"javascript:document.getElementById('DotacaoElemento1').focus();\" class='titulo2'>Elemento 1 da dotação</a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
		}
		if(is_null($DotacaoElemento2) or $DotacaoElemento2==""){
			adicionarMensagem("<a href=\"javascript:document.getElementById('DotacaoElemento2').focus();\" class='titulo2'>Elemento 2 da dotação</a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
		}
		if(is_null($DotacaoElemento3)  or $DotacaoElemento3==""){
			adicionarMensagem("<a href=\"javascript:document.getElementById('DotacaoElemento3').focus();\" class='titulo2'>Elemento 3 da dotação</a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
		}
		if(is_null($DotacaoElemento4)  or $DotacaoElemento4==""){
			adicionarMensagem("<a href=\"javascript:document.getElementById('DotacaoElemento4').focus();\" class='titulo2'>Elemento 4 da dotação</a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
		}
		if(is_null($DotacaoFonte)  or $DotacaoFonte==""){
			adicionarMensagem("<a href=\"javascript:document.getElementById('DotacaoFonte').focus();\" class='titulo2'>Fonte da dotação</a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
		}
		if($GLOBALS['Mens']!=1){
			$BloqueioTodos .= sprintf("%04s",$DotacaoAno);
			$BloqueioTodos .= ".".sprintf("%02s",$DotacaoOrgao);
			$BloqueioTodos .= sprintf("%02s",$DotacaoUnidade);
			$BloqueioTodos .= ".".sprintf("%02s",$DotacaoFuncao);
			$BloqueioTodos .= ".".sprintf("%04s",$DotacaoSubfuncao);
			$BloqueioTodos .= ".".sprintf("%04s",$DotacaoPrograma);
			$BloqueioTodos .= ".".sprintf("%01s",$DotacaoTipoProjetoAtividade);
			$BloqueioTodos .= ".".sprintf("%03s",$DotacaoProjetoAtividade);
			$BloqueioTodos .= ".".sprintf("%01s",$DotacaoElemento1);
			$BloqueioTodos .= ".".sprintf("%01s",$DotacaoElemento2);
			$BloqueioTodos .= ".".sprintf("%02s",$DotacaoElemento3);
			$BloqueioTodos .= ".".sprintf("%02s",$DotacaoElemento4);
			$BloqueioTodos .= ".".sprintf("%04s",$DotacaoFonte);
			$BloqueioTodosData = getDadosDotacaoOrcamentaria($dbOracle, $BloqueioTodos );
		}
	}
	else{
		if(is_null($BloqueioAno)  or $BloqueioAno==""){
			adicionarMensagem("<a href=\"javascript:document.getElementById('BloqueioAno').focus();\" class='titulo2'>Ano do Bloqueio</a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
		}
		if(is_null($BloqueioOrgao) or $BloqueioOrgao==""){
			adicionarMensagem("<a href=\"javascript:document.getElementById('BloqueioOrgao').focus();\" class='titulo2'>Orgão do Bloqueio</a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
		}
		if(is_null($BloqueioUnidade) or $BloqueioUnidade==""){
			adicionarMensagem("<a href=\"javascript:document.getElementById('BloqueiUnidade').focus();\" class='titulo2'>Unidade do Bloqueio</a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
		}
		if(is_null($BloqueioDestinacao) or $BloqueioDestinacao==""){
			adicionarMensagem("<a href=\"javascript:document.getElementById('BloqueioDestinacao').focus();\" class='titulo2'>Destinação do Bloqueio</a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
		}
		if(is_null($BloqueioSequencial) or $BloqueioSequencial==""){
			adicionarMensagem("<a href=\"javascript:document.getElementById('BloqueioSequencial').focus();\" class='titulo2'>Sequencial do Bloqueio</a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
		}
		if($GLOBALS['Mens']!=1){
			$BloqueioTodos .= sprintf("%04s",$BloqueioAno);
			$BloqueioTodos .= ".".sprintf("%02s",$BloqueioOrgao);
			$BloqueioTodos .= ".".sprintf("%02s",$BloqueioUnidade) ;
			$BloqueioTodos .= ".".sprintf("%01s",$BloqueioDestinacao) ;
			$BloqueioTodos .= ".".sprintf("%04s",$BloqueioSequencial) ;
			$BloqueioTodosData = getDadosBloqueio($dbOracle, $BloqueioTodos );
		}
	}

	if($isDotacao){
		$Foco = "DotacaoAno";
	}
	else{
		$Foco = "BloqueioAno";
	}

	if($GLOBALS['Mens']!=1){
		if(is_null($BloqueioTodosData)){
			adicionarMensagem("<a href=\"javascript:document.getElementById('".$Foco."').focus();\" class='titulo2'>Bloqueio/Dotação não existe</a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
		}
	}

	if($GLOBALS['Mens']!=1){

		if(is_null($Bloqueios)){
			$Bloqueios = array();
		}
		$isRepetido = false;
		foreach($Bloqueios as $bloqueio){
			if($bloqueio == $BloqueioTodos){
				$isRepetido = true;
				adicionarMensagem("<a href=\"javascript:document.getElementById('".$Foco."').focus();\" class='titulo2'>Bloqueio/Dotação repetido</a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
			}
		}
		if(!$isRepetido){
			array_push($Bloqueios,$BloqueioTodos);
		}
	}
}
elseif($Botao=="RetirarBloqueio"){
	if($isDotacao){
		$Foco = "DotacaoAno";
	}
	else{
		$Foco = "BloqueioAno";
	}

	$quantidade = count($Bloqueios);

	for($itr=0;$itr<$quantidade;$itr++){
		if($BloqueiosCheck[$itr]){
			unset($Bloqueios[$itr]);
		}
	}

	if($GLOBALS['Mens']!=1){
		if(count($Bloqueios) <= 0){
			adicionarMensagem("<a href=\"javascript:document.getElementById('".$Foco."').focus();\" class='titulo2'>A Licitação deve ter pelo menos um Bloqueio/Dotação </a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
		}
	}

	unset($BloqueiosCheck);
}

/**
 *
 */

/* Carregando dados das solicitacoes da licitacao*/
/* BUSCANDO OS ITENS DA LICITACAO */
$sql = "SELECT ITEM.CITELPSEQU , ITEM.CMATEPSEQU , ITEM.CSERVPSEQU , ITEM.AITELPORDE
		, ITEM.AITELPQTSO , ITEM.VITELPUNIT , ITEM.AITELPQTEX , ITEM.VITELPVEXE
		, MAT.EMATEPDESC, MAT.CUNIDMCODI , SERV.ESERVPDESC , UNIDADE.EUNIDMSIGL, ITEM.CITELPNUML
		FROM SFPC.TBITEMLICITACAOPORTAL ITEM ";
$sql .= " LEFT JOIN
			SFPC.TBMATERIALPORTAL MAT ON (MAT.CMATEPSEQU = ITEM.CMATEPSEQU)
		LEFT JOIN
			SFPC.TBSERVICOPORTAL SERV ON (SERV.CSERVPSEQU = ITEM.CSERVPSEQU)
		LEFT JOIN
		SFPC.TBUNIDADEDEMEDIDA UNIDADE ON (MAT.CUNIDMCODI = UNIDADE.CUNIDMCODI)

			WHERE  ITEM.clicpoproc = $Processo
			AND    ITEM.alicpoanop = $ProcessoAno
			AND    ITEM.cgrempcodi = ".$_SESSION['_cgrempcodi_']."
			AND    ITEM.ccomlicodi = $ComissaoCodigo
			AND    ITEM.corglicodi = $OrgaoLicitanteCodigo

			ORDER BY ITEM.CITELPSEQU

		";

$resItensLicitacao  = $db->query($sql);
$resItensLicitacaoLista  = $db->query($sql);

if(PEAR::isError($resItensLicitacao)){
	$CodErroEmail  = $resItensLicitacao->getCode();
		$DescErroEmail = $resItensLicitacao->getMessage();
		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
}
else{
	//Pegando a resposta para a listagens
	while ( $LinhasItem = $resItensLicitacao->fetchRow() ){
		//Pegando lista de DOTACAO E BLOQUEIO
		if($RegistroPreco=="S"){
			//Faco a busca pelos campos de Dotação AITCDOUNIDOEXER CITCDOUNIDOORGA CITCDOUNIDOCODI CITCDOTIPA AITCDOORDT  CITCDOELE1, CITCDOELE2, CITCDOELE3, CITCDOELE4, CITCDOFONT
			 $sql = " SELECT aitldounidoexer, citldounidoorga, citldounidocodi, citldotipa, aitldoordt, citldoele1, citldoele2, citldoele3, citldoele4, citldofont
					 FROM SFPC.tbitemlicitacaodotacao WHERE
					 clicpoproc = $Processo
			  		 AND alicpoanop = $ProcessoAno
			 		 AND cgrempcodi = ".$_SESSION['_cgrempcodi_']."
			  		 AND ccomlicodi = $ComissaoCodigo
			 		 AND corglicodi = $OrgaoLicitanteCodigo
					 AND citelpsequ = ". $LinhasItem[0];

			$res  = $db->query($sql);
			if( PEAR::isError($res) ){
				$CodErroEmail  = $res->getCode();
				$DescErroEmail = $res->getMessage();
				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
			}
			else{
				$valorTotalDisponivel = 0;
				while ( $linha = $res->fetchRow() ){
					$dotacaoArray = getDadosDotacaoOrcamentariaFromChave($dbOracle,$linha[0],$linha[1],$linha[2],$linha[3],$linha[4],$linha[5],$linha[6],$linha[7],$linha[8],$linha[9]);
					$valorDotacaoBloqueio[] = $dotacaoArray["dotacao"];
					$valorDotacaoBloqueioItem[$LinhasItem[3]][] = $dotacaoArray["dotacao"];
				}
			}
		}
		else{
			//Faco a busca pelos campos de Bloqueio
			$sql = " SELECT AITLBLNBLOQ , AITLBLANOB
			  		 FROM  SFPC.TBITEMLICITACAOBLOQUEIO WHERE
						 clicpoproc = $Processo
			  		 	 AND alicpoanop = $ProcessoAno
			 		 	 AND cgrempcodi = ".$_SESSION['_cgrempcodi_']."
			  		 	 AND ccomlicodi = $ComissaoCodigo
			 		 	 AND corglicodi = $OrgaoLicitanteCodigo
					 	 AND citelpsequ = ". $LinhasItem[0];

			$res  = $db->query($sql);
			if( PEAR::isError($res) ){
				$CodErroEmail  = $res->getCode();
				$DescErroEmail = $res->getMessage();
				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
			}
			else{
				while ( $linha = $res->fetchRow() ){
					$dotacaoArray = getDadosBloqueioFromChave($dbOracle,$linha[1],$linha[0]);
					$valorDotacaoBloqueio[] = $dotacaoArray["bloqueio"];
					$valorDotacaoBloqueioItem[$LinhasItem[3]][] = $dotacaoArray["bloqueio"];
				}
			}
		}
	}
}

//var_dump($valorDotacaoBloqueio); var_dump($valorDotacaoBloqueioItem); die;

//Caso seja as cadastradas na licitacao
$intQuantidadeSolicitacoes = 0;
$sqlSolicitacoes = " SELECT  csolcosequ ,clicpoproc , alicpoanop , cgrempcodi ,ccomlicodi ,corglicodi
				 	 FROM SFPC.TBSOLICITACAOLICITACAOPORTAL SOL WHERE SOL.CLICPOPROC = $Processo AND SOL.ALICPOANOP = $ProcessoAno
					 AND SOL.CCOMLICODI = $ComissaoCodigo AND SOL.cgrempcodi =". $_SESSION['_cgrempcodi_'] ;

$resultSoli = $db->query($sqlSolicitacoes);

if( PEAR::isError($resultSoli) ){
	ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlSolicitacoes");
}

while( $LinhaSoli = $resultSoli->fetchRow() ){
	$arrSolicitacoes[] = $LinhaSoli[0];
	$intQuantidadeSolicitacoes++;
}

//Caso esteja sendo uma alteraco de solicitacao
if($idSolicitacao!=""){
	$aux = explode("-", $idSolicitacao);
	$strId = $aux[0];//ID DO GRUPO OU DA SOLICITACAO DEPENDE DA FLAG
	$FlagTipo = $aux[1];//FLAG PARA DIZER SE É GRUPO (G) OU (I)

	if ( $FlagTipo == "I" ){
		$arrLinhas = listarIndividual("8","TODOS", "", "", $strId);
	}
	elseif( $FlagTipo == "G" ){
		$arrLinhas = listarGrupo("8","TODOS", "", "", "" ,$strId);
	}

	//Dados da primeira licitaçao que é a que tem a flag S
	$ComissaoDescricao 			= $arrLinhas[0]['DescComissaoLici'];
	$ComissaoCodigo 			= $arrLinhas[0]['CodComissaoLici'];
	$RegistroPreco 				= $arrLinhas[0]['TipoRegistroPreco'];
	$ComissaoCodigo 			= $arrLinhas[0]['CodComissaoLici'];
	$OrgaoLicitanteDescricao 	= $arrLinhas[0]['DescOrgao'];
	$OrgaoLicitanteCodigo 		= $arrLinhas[0]["CodOrgao"];
	$LicitacaoObjeto 			= $arrLinhas[0]['ObjetoSolicitacao'];
	$NCaracteres 				= strlen($arrLinhas[0]['ObjetoSolicitacao']);
	//basta apenas uma SCC ter o flag igual a 'S', para o campo ser preenchido com 'Sim'
	$strGeraContrato = "N";

	foreach ($arrLinhas as $linha){
		$arrSolicitacoesNovas[] = 	$linha["SeqSolicitacao"];

		if($linha['FlagGeraContrato']=="S"){
			$strGeraContrato = "S";
		}
	}

	//Buscando Itens das novas Solicitacoes
	$retorno = RecSolicitacoesBKS($arrLinhas,$RegistroPreco);
	//ARR DE ITENS ESTA NA POSICAO 0
	$listaNovosIntens = $retorno[0];
	#Se so tenho uma unica dotacao ou bloqueio para todos os itens imprimo o valor
	$valorDotacaoBloqueio = array();

	foreach($listaNovosIntens as $Itens){
		$arrCodMaterialServico[] = $Itens["codRed"];
		$arrTipoItens = $Itens["Tipo"];
		$arr = array_unique($Itens['DOTACAOBLOQUEIOS']);

		foreach($arr as $strDotacaoiten){
			$arrDotacaoBloqueio[] = $strDotacaoiten;
		}
	}
	$valorDotacaoBloqueio = $arrDotacaoBloqueio;
	//var_dump($arrDotacaoBloqueio); die;
}

?>
<html>
<?php
# Carrega o layout padrão #
layout();
?>
<script language="javascript" src="../janela.js" type="text/javascript"></script>
<script type="text/javascript">

function enviar(valor){
	if(valor == "PesquisarGeral"){
		document.formulario.action= "CadLicitacaoConsultarSolicitacao.php";
	}
	if(valor=="AlterarLicitacao"){
		var limiteCompra = moeda2float(document.getElementById('limiteCompra').value);
		var valorTotal = moeda2float(document.getElementById('ValorTotal').value) ;

		if(valorTotal>limiteCompra&&limiteCompra>0){
			if(!window.confirm("A soma dos valores dos itens ultrapassam o valor limite da modalidade, Deseja Continuar ?")){
				return false;
			}
		}
	}
	document.formulario.Botao.value = valor;
	document.formulario.submit();
}

function AbreJanela(url,largura,altura){
	window.open(url,'paginadetalhe','status=no,scrollbars=yes,left=15,top=15,width='+largura+',height='+altura);
}

function CaracteresObjeto(text,campo){
	input = document.getElementById(campo);
	input.value = text.value.length;
}

function mudarModalidade() {
	if(window.confirm (" Você tem certeza que deseja mudar a modalidade? \n\n Mudar a modalidade poderá mudar o número da licitação. Caso haja licitações posteriores cadastradas, e a alteração da modalidade for incorreta, poderá ser impossível reverter para o número antigo, pois, quando se altera a modalidade, o número da licitação será sempre maior que o maior número de todas licitações cadastradas, para aquela modalidade.")){
		enviar('Licitacao');
	}else{
		document.formulario.ModalidadeCodigo.value=<?php echo $ModalidadeCodigo?>;
	}
}

function AtualizarValorTotal(linha){

	//Pegando a quantidade do item na linha que foi alterada
	quantidadeItem = moeda2float(document.getElementById('quantidadeItem['+linha+']').value);
	//Pegando Valor do item na linha que foi alterada
	valorEstimadoItem = moeda2float(document.getElementById('valorEstimadoItem['+linha+']').value);
	//# SO FACO SE TIVER EXERCÍCIO
	if(document.getElementById('quantidadeExercicioItem['+linha+']')!=null){
		//Pegando Valor da quantidade do exercicio
		quantidadeExercicio = moeda2float(document.getElementById('quantidadeExercicioItem['+linha+']').value);
	}
	//Calculando o valor total
	valorTotalItem = quantidadeItem * valorEstimadoItem;
	//att o valor total do item na linha alterada no span e no imput
	document.getElementById('spanValorTotalItem['+linha+']').innerHTML = float2moeda(valorTotalItem);
	document.getElementById('valorTotalItem['+linha+']').value = float2moeda(valorTotalItem);
	quantidadeTotalItens = document.getElementById('quantidadeTotalItens').value;
	//# SO FACO SE TIVER EXERCÍCIO
	if(document.getElementById('valorExercicioItem['+linha+']')!=null){
		//calculando o valor do exercicio
		//valorExercicioItem = valorEstimadoItem * quantidadeExercicio;
		document.getElementById('spanValorExercicioItem['+linha+']').innerHTML = float2moeda(valorExercicioItem);
		document.getElementById('valorExercicioItem['+linha+']').value = float2moeda(valorExercicioItem);
		document.getElementById('spanValorDemaisExercicioItem['+linha+']').innerHTML = ((valorTotalItem-valorExercicioItem)>0)?float2moeda(valorTotalItem-valorExercicioItem):0;
	}

	var total = 0;
	//Calculando total geral , lendo todas as linhas
	for(linha= 1; linha<= quantidadeTotalItens; linha++){
		//alert(total + '+' +document.getElementById('valorTotalItem['+linha+']').value );
		total += moeda2float(document.getElementById('valorTotalItem['+linha+']').value);

	}

	document.getElementById('labelValorTotal').innerHTML = float2moeda(total);
	document.getElementById('ValorTotal').value = float2moeda(total);
	//alert(valorTotalItem-valorExercicioItem);
}
<?php MenuAcesso(); ?>
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<script language="JavaScript">
$(document).ready(function(){
	$(".detalhar").live("click", function() {
		var seq = $(this).attr("id");
		var valAtual = $(this).html();
		if(valAtual=="+"){
				$(this).html("-");
				$(".opdetalhe."+seq).show();
		}else{
				$(this).html("+");
				$(".opdetalhe."+seq).hide();
		}
	});
});
</script>
<form action="<?=$programa?>" method="post" name="formulario">
<input type="hidden" name="Botao" id="Botao" value="" />
<input type="hidden" name="ProgramaReferencia" value="<?php echo $programa;?>">
<input type="hidden" name="ModalidadeCodigoAntes" value="<?php echo $ModalidadeCodigoAntes;?>">
<input type="hidden" name="LicitacaoAntes" value="<?php echo $LicitacaoAntes;?>">
<input type="hidden" name="ComissaoCodigo" value="<?php echo $ComissaoCodigo; ?>">
<input type="hidden" name="ComissaoDescricao" value="<?php echo $ComissaoDescricao; ?>">
<input type="hidden" name="Processo" value="<?php echo $Processo; ?>">
<input type="hidden" name="ProcessoAno" value="<?php echo $ProcessoAno; ?>">
<input type="hidden" name="OrgaoLicitanteCodigo" value="<?php echo $OrgaoLicitanteCodigo; ?>">
<input type="hidden" name="OrgaoLicitanteCodigoAntes" value="<?php echo $OrgaoLicitanteCodigoAntes; ?>">
<input type="hidden" name="OrgaoLicitanteDescricao" value="<?php echo $OrgaoLicitanteDescricao; ?>">
<input type="hidden" name="LicitacaoUltAlteracao" value="<?php echo $LicitacaoUltAlteracao; ?>">
<input type="hidden" name="ValorTotalAntes" value="<?php echo $ValorTotalAntes; ?>">
<input type="hidden" name="Critica" value="1">
<input type="hidden" name="idSolicitacao" value="<?php echo $idSolicitacao;?>" />
<?php foreach ( $arrSolicitacoes as $seqSoli){ ?>
<input type="hidden" name="SeqSolicitacoesAnterior[]" value="<?php echo $seqSoli;?>"/>
<?php }?>

<input type="hidden" id="limiteCompra" name="limiteCompra" value="<?php if(($OrgaoLicitanteCodigo!="")&&($ModalidadeCodigo!="")&&(count($arrCodMaterialServico)>0)){ echo converte_valor_estoques(calculaLimiteCompra($OrgaoLicitanteCodigo, $ModalidadeCodigo, $arrCodMaterialServico, $arrTipoItens));}else{echo 0;}?>"/>
<br><br><br><br><br>
<table width="100%" cellpadding="3" border="0" summary="">
	<!-- Caminho -->
	<tr>
		<td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
		<td align="left" class="textonormal" colspan="2">
			<font class="titulo2">|</font>
			<a href="../index.php"><font color="#000000">Página Principal</font></a> > Licitações > Licitação > Manter
		</td>
	</tr>
	<!-- Fim do Caminho-->
	<!-- Erro -->
	<?php if($Mens == 1){?>
	<tr>
		<td width="150"></td>
		<td align="left" colspan="2"><?php ExibeMens($Mensagem,$Tipo,1); ?></td>
	</tr>
	<?php } ?>
	<!-- Fim do Erro -->
	<!-- Corpo -->
	<tr>
		<td width="150"></td>
		<td class="textonormal">
			<table width="100%" border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal"  bgcolor="#FFFFFF">
				<tr>
					<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3" colspan="4">
						MANTER - LICITAÇÃO
					</td>
				</tr>
			</table>
			<table width="100%" border="1" summary="" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" bgcolor="#FFFFFF">
				<tr>
					<td>
						<table border="0" width="100%" summary="">
							<tr>
								<td align="left" bgcolor="#DCEDF7" class="textonormal" colspan="1" >Solicitação de Compra/Contratação-SCC*:</td>
								<td align="left" class="textonormal" colspan="1" >
									<select name="solicticacoesLicitacao" style="width:200px;" multiple="multiple">
								 	<?php
								 	if(isset($arrSolicitacoesNovas)){
								 		$arrSolicitacoesLista = $arrSolicitacoesNovas;
								 	}
								 	else{
								 		$arrSolicitacoesLista = $arrSolicitacoes;
								 	}

								 	foreach($arrSolicitacoesLista as $seqSoli){
								 	?>
								  		<option selected="selected" value="<?php echo $seqSoli;?>" ><?php echo getNumeroSolicitacaoCompra($db,$seqSoli);?></option>
								  	<?php
								 	}
								 	?>
								 	</select>
									<?php foreach ( $arrSolicitacoesLista as $seqSoli){ ?>
									<input type="hidden" name="SeqSolicitacoes[]" value="<?php echo $seqSoli;?>"/>
									<?php }?>
									&nbsp;&nbsp;&nbsp;&nbsp;
									<img src="../midia/lupa.gif" onClick="javascript:enviar('PesquisarGeral')" />
								</td>
								<td align="left" class="textonormal" colspan="2" >
								</td>
							</tr>
							<tr>
								<td align="left" bgcolor="#DCEDF7" class="textonormal" colspan="1" >Comissão*</td>
								<td align="left" class="textonormal" colspan="3" >
									<label style="width:500px;"><?php echo $ComissaoDescricao;?></label>
								</td>
							</tr>
							<tr>
								<td align="left" bgcolor="#DCEDF7" class="textonormal" colspan="1" >Processo</td>
								<td align="left" class="textonormal" colspan="3" >
									<label><?php echo substr($Processo + 10000,1); ?></label>
									<input type="hidden" name="NumeroDoProcesso" value="<?php echo substr($Processo + 10000,1);?>" />
								</td>
							</tr>
							<tr>
								<td align="left" bgcolor="#DCEDF7" class="textonormal" colspan="1" >Ano</td>
								<td align="left" class="textonormal" colspan="3" >
									<label><?php echo $ProcessoAno; ?></label>
									<input type="hidden" name="AnoDoExercicio" value="<?php echo $ProcessoAno;?>" />
								</td>
							</tr>
							<tr>
								<td align="left" bgcolor="#DCEDF7" class="textonormal" colspan="1" >Modalidade*</td>
								<td align="left" class="textonormal" colspan="3" >
				                <select name="ModalidadeCodigo" class="textonormal" onChange="javascript:mudarModalidade();">
										<?php
									      $sql    = "SELECT CMODLICODI, EMODLIDESC FROM	SFPC.TBMODALIDADELICITACAO ORDER BY AMODLIORDE";
									      $result = $db->query($sql);
											if( PEAR::isError($result) ){
											    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
											}else{
												while( $Linha = $result->fetchRow() ){
												   	if( $Linha[0] == $ModalidadeCodigo ){
												    	echo "<option value=\"$Linha[0]\" selected>$Linha[1]</option>\n";
												   	}else{
												      	echo "<option value=\"$Linha[0]\">$Linha[1]</option>\n";
												   	}
										     	}
										   }
									    ?>
									</select>
								</td>
							</tr>
							<tr>
								<td align="left" bgcolor="#DCEDF7" class="textonormal" colspan="1" >Registro de Preço</td>
								<td align="left" class="textonormal" colspan="3" >
									<input type="hidden" value="<?php echo $RegistroPreco; ?>" name="RegistroPreco" />
									<?php
									 if($RegistroPreco=="S"){echo "Sim";}else{echo "Não";}
									?>
								</td>
							</tr>
							<tr>
								<td align="left" bgcolor="#DCEDF7" class="textonormal" colspan="1" >Licitação</td>
								<td align="left" class="textonormal" colspan="3" >
									<label><?php echo substr($Licitacao + 10000,1); ?></label>
									<input type="hidden" name="Licitacao" value="<?php echo substr($Licitacao + 10000,1);?>" />
								</td>
							</tr>
							<tr>
								<td align="left" bgcolor="#DCEDF7" class="textonormal" colspan="1" >Ano da Licitação</td>
								<td align="left" class="textonormal" colspan="3" >
									<label><?php echo $LicitacaoAno; ?></label>
									<input type="hidden" name="LicitacaoAno" value="<?php echo $LicitacaoAno;?>" />
								</td>
							</tr>
							<tr>
								<td align="left" bgcolor="#DCEDF7" class="textonormal" colspan="1" >Data de Abertura*</td>
								<td align="left" class="textonormal" colspan="3" >
									<?php
									$DataMes = DataMes();
									$URLLicitacao = "../calendario.php?Formulario=formulario&Campo=DataLicitacao";
									?>
									<input type="text" name="LicitacaoDtAbertura" size="10" maxlength="10" value="<?php echo $LicitacaoDtAbertura;?>" class="textonormal">
									<a href="javascript:janela('<?php echo $URLLicitacao ?>','Calendario',220,170,1,0)"><img src="../midia/calendario.gif" border="0" alt=""></a>
								</td>
							</tr>
							<tr>
								<td align="left" bgcolor="#DCEDF7" class="textonormal" colspan="1" >Hora de Abertura*</td>
								<td align="left" class="textonormal" colspan="3" >
									<input type="text" style="width:60px;" class="hora" name="LicitacaoHoraAbertura" maxlength="5" value="<?php  echo $LicitacaoHoraAbertura; ?>" /> hh:mm
								</td>
							</tr>
							<tr>
								<td align="left" bgcolor="#DCEDF7" class="textonormal" colspan="1" >Orgão Licitante*</td>
								<td align="left" class="textonormal" colspan="3" >
								<?php echo $OrgaoLicitanteDescricao;?>
								</td>
							</tr>
							<tr>
								<td align="left" bgcolor="#DCEDF7" class="textonormal" colspan="1" >Objeto*</td>
								<td align="left" class="textonormal" colspan="3" >
									<table>
										<tr>
											<td>
												<?php
												if(isset($FlagTipo)){
													if( $FlagTipo == "I" ){
														$qtdCaracteres = strlen($arrLinhas[0]['ObjetoSolicitacao']);
													}
													else{
														$qtdCaracteres = strlen($Objeto);
												?>
														<label class="textonormal" >máximo de 200 caracteres</label>
														<input type="text" name="NCaracteres" disabled id="NCaracteres"  size="3" readonly value="<?php  echo  $NCaracteres; ?>" class="textonormal">
												<?php
													}
												}
												?>
											</td>
										</tr>
										<tr>
											<td>
											<?php if ( (!isset($FlagTipo)) || ($FlagTipo == "I")) {?>
												<label class="textonormal"><?php echo $LicitacaoObjeto; ?></label>
												<input type="hidden" name="LicitacaoObjeto" id="LicitacaoObjeto" value="<?php echo $LicitacaoObjeto; ?>"/>
											<?php }
												else { ?>
													<textarea maxlength="200" name="LicitacaoObjeto" id="LicitacaoObjeto" OnKeyUp="javascript:CaracteresObjeto(this,'NCaracteresObjeto')"
													OnBlur="javascript:CaracteresObjeto(this,'NCaracteresObjeto')" OnSelect="javascript:CaracteresObjeto(this,'NCaracteresObjeto')"
													rows="3" cols="59" ><?php echo $LicitacaoObjeto; ?></textarea>
											<?php } ?>
											</td>
										</tr>
									</table>
								</td>
							</tr>
							<tr>
								<td align="left" bgcolor="#DCEDF7" class="textonormal" colspan="1" >Valor Total Estimado*</td>
								<td align="left" class="textonormal" colspan="3" >
									<label id="labelValorTotal" style="width:150px;"><?php echo converte_valor_estoques($ValorTotal); ?></label>
									<input type="hidden" id="ValorTotal" name="ValorTotal" value="<?php echo $ValorTotal; ?>" />
								</td>
							</tr>
							<tr>
								<td align="left" bgcolor="#DCEDF7" class="textonormal" colspan="1" >Gera Contrato</td>
								<td align="left" class="textonormal" colspan="3" >
									<select name="GeraContrato">
										<option <?php if($GeraContrato=="S"){ echo "selected='selected'";}?> value="S">SIM</option>
										<option <?php if($GeraContrato!="S"){ echo "selected='selected'";}?> value="N">NÃO</option>
									</select>
								</td>
							</tr>


						</table>
					</td>
				</tr>

				<!-- NOVO DOTAÇÃO/BLOQUEIO -->

				<tr>
					<td class="titulo3" align="center" bgcolor="#75ADE6" valign="middle">
						<span id="BloqueioTitulo"  colspan=2>BLOQUEIO OU DOTAÇÃO ORÇAMENTÁRIA</span>
					</td>
				</tr>
				<?
				$cntBloqueio = -1;

				//teste
				if(empty($Bloqueios)){
					$Bloqueios = array_unique($valorDotacaoBloqueio);
				}

				$isDotacao	= ($RegistroPreco == 'S')?true:false;
				//var_dump($isDotacao); die;

				if(!is_null($Bloqueios)){
					foreach($Bloqueios as $bloqueioItem){
						if(isset($bloqueioItem)){
							$cntBloqueio ++;?>
							<tr>
								<td class="textonormal">
									<?if(!$ocultarCamposEdicao){?>
										<input name="BloqueiosCheck[<?=$cntBloqueio?>]" type="checkbox" <?php if($BloqueiosCheck[$cntBloqueio]) echo "checked"; ?> />
									<?}?>
									<?=$bloqueioItem?>
									<input name="Bloqueios[<?=$cntBloqueio?>]" value="<?=$bloqueioItem?>" type="hidden" >
									<input name="dotacaoBloqueio[<?php echo $cntBloqueio;?>]" type="hidden" value="<?php echo $bloqueioItem; ?>" />
								</td>
							</tr>
					<?}
					}
				}
				?>
				<tr>
					<td class="textonormal" colspan=2 bgcolor="#ffffff">
						<table class="textonormal" border="0" align="left" width="100%" summary="">
							<tr>
								<td class="textonormal" bgcolor="#DCEDF7" width="200px">Novo <span id="BloqueioLabel">Bloqueio ou dotação</span>:</td>
								<td class="textonormal">
								<?if($isDotacao){?>

									Ano: <input name="DotacaoAno" id="DotacaoAno" size="4" maxlength="4" value="" type="text" value="<?=$DotacaoAno?>"/>
									Órgão: <input name="DotacaoOrgao" id="DotacaoOrgao" size="2" maxlength="2" value="" type="text" value="<?=$DotacaoAno?>"/>
									Unidade: <input name="DotacaoUnidade" id="DotacaoUnidade" size="2" maxlength="2" value="" type="text" value="<?=$DotacaoAno?>"/>
									Funcao: <input name="DotacaoFuncao" id="DotacaoFuncao" size="2" maxlength="2" value="" type="text" value="<?=$DotacaoFuncao?>"/>
									SubFunção: <input name="DotacaoSubfuncao" id="DotacaoSubfuncao" size="4" maxlength="4" value="" type="text" value="<?=$DotacaoSubfuncao?>"/>
									Programa: <input name="DotacaoPrograma" id="DotacaoPrograma" size="4" maxlength="4" value="" type="text" value="<?=$DotacaoSubfuncao?>"/>
									Tipo Projeto/Atividade: <input name="DotacaoTipoProjetoAtividade" id="DotacaoTipoProjetoAtividade" size="1" maxlength="1" value="" type="text" value="<?=$DotacaoTipoProjetoAtividade?>"/>
									Projeto/Atividade: <input name="DotacaoProjetoAtividade" id="DotacaoProjetoAtividade" size="3" maxlength="3" value="" type="text" value="<?=$DotacaoProjetoAtividade?>"/>
									Elemento1: <input name="DotacaoElemento1" id="DotacaoElemento1" size="1" maxlength="1" value="" type="text" value="<?=$DotacaoElemento1?>"/>
									Elemento2: <input name="DotacaoElemento2" id="DotacaoElemento2" size="1" maxlength="1" value="" type="text" value="<?=$DotacaoElemento2?>"/>
									Elemento3: <input name="DotacaoElemento3" id="DotacaoElemento3" size="2" maxlength="2" value="" type="text" value="<?=$DotacaoElemento3?>"/>
									Elemento4: <input name="DotacaoElemento4" id="DotacaoElemento4" size="2" maxlength="2" value="" type="text" value="<?=$DotacaoElemento4?>"/>
									Fonte: <input name="DotacaoFonte" id="DotacaoFonte" size="4" maxlength="4" value="" type="text" value="<?=$DotacaoFonte?>"/>

								<?}
								else{?>
									Ano: <input name="BloqueioAno" id="BloqueioAno" size="4" maxlength="4" value="" type="text" value="<?=$BloqueioAno?>"/>
									Órgão: <input name="BloqueioOrgao" id="BloqueioOrgao" size="2" maxlength="2" value="" type="text" value="<?=$BloqueioOrgao?>"/>
									Unidade: <input name="BloqueioUnidade" id="BloqueioUnidade" size="2" maxlength="2" value="" type="text" value="<?=$BloqueioUnidade?>"/>
									Destinação: <input name="BloqueioDestinacao" id="BloqueioDestinacao" size="1" maxlength="1" value="" type="text" value="<?=$BloqueioDestinacao?>"/>
									Sequencial: <input name="BloqueioSequencial" id="BloqueioSequencial" size="4" maxlength="4" value="" type="text" value="<?=$BloqueioSequencial?>"/>
								<?}?>
								</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td class="textonormal" align="center">
						<input name="BotaoIncluirBloqueioTodos" value="Incluir" class="botao" type="button" onClick="enviar('IncluirBloqueio')"/>
						<input name="BotaoRemoverBloqueioTodos" value="Remover" class="botao" type="button" onClick="enviar('RetirarBloqueio')"/>
					</td>
				</tr>
				<!-- NOVO BLOQUEIO/DOTAÇÃO -->

				<tr>
					<td align="center" bgcolor="#75ADE6" class="titulo3" colspan="4" >ITENS DA SOLICITAÇÃO</td>
				</tr>
				<?php
				$estilotd = 'class="titulo3" align="center" bgcolor="#F7F7F7"';
				$estiloClasstd = 'class="textonormal" align="center" bgcolor="#F7F7F7"';
				?>
				<tr>
					<td style="background-color:#F1F1F1;" colspan="4">
						<table bordercolor="#75ADE6" border="1" cellspacing="" bgcolor="bfdaf2" width="100%" class="textonormal">
							<tr class="linhainfo">
								<td <?php echo $estilotd;?>>ORD</td>
								<td <?php echo $estilotd;?>>DESCRIÇÃO</td>
								<td <?php echo $estilotd;?>>TIPO</td>
								<td <?php echo $estilotd;?>>CÓD.RED</td>
								<td <?php echo $estilotd;?>>UNIDADE</td>
								<td <?php echo $estilotd;?>>VALOR TRP</td>
								<td <?php echo $estilotd;?>>QUANTIDADE</td>
								<td <?php echo $estilotd;?>>VALOR ESTIMADO</td>
								<td <?php echo $estilotd;?>>VALOR TOTAL ESTIMADO</td>
								<?php if($RegistroPreco!="S"){?>
									<td <?php echo $estilotd;?>>QUANTIDADE NO EXERCÍCIO</td>
									<td <?php echo $estilotd;?>>VALOR DO  EXERCÍCIO</td>
									<td <?php echo $estilotd;?>>VALOR DEMAIS EXERCÍCIOS</td>
								<?php
								}
								?>
							</tr>
							<?php

						//Se não tiver itens novos mostro os antigos


						if(!is_array($listaNovosIntens)){
							$intQuantidadeItens = $resItensLicitacaoLista->numRows();
							//var_dump($intQuantidadeItens); die;

							$ORDEM = 0;
							while($listaIntens = $resItensLicitacaoLista->fetchRow()){
									$ORDEM++;
									$ORDEMITEM = $listaIntens[3];
									//Se for Material
									if($listaIntens[1]!=""){
										$TIPO = "CADUM";
										$DESCRICAO = $listaIntens[8];
										$CODRED = $listaIntens[1];
										$UNIDADE = $listaIntens[11];
										$VALORTRP = calculaValorTrp($CODRED);
									}else{
										$TIPO = "CADUS";
										$DESCRICAO = $listaIntens[10]." - ";
										$CODRED = $listaIntens[2];
										$UNIDADE = "";
										$VALORTRP = "";
									}
									$QUANTIDADE = $listaIntens[4];
									$VALORUNIT = $listaIntens[5];

									$VALORESTIMADO = $VALORUNIT;
									$VALORTOTALESTIMADO = $VALORESTIMADO * $QUANTIDADE;
									$QUANTIDADEEXERCICIO = $listaIntens[6];
									$VALOREXERCICIO = $listaIntens[7];
									$LOTEITEM			= $listaIntens[12];
							?>
							<tr>
								<td <?php echo $estiloClasstd;?>>&nbsp;<?php echo $listaIntens[0];?>
									<input type="hidden" name="ordem[<?php echo $ORDEM;?>]" value = "<?php echo $ORDEMITEM;?>"/>
									<input type="hidden" name="tipoItem[<?php echo $ORDEM;?>]" value = "<?php echo $TIPO;?>"/>
									<input type="hidden" name="codRedItem[<?php echo $ORDEM;?>]" value = "<?php echo $CODRED;?>"/>
									<input type="hidden" name="lote[<?php echo $ORDEM;?>]" value="<?php echo $LOTEITEM;?>"/>
								</td>
								<td <?php echo $estiloClasstd;?>>&nbsp;<?php echo $DESCRICAO;?></td>
								<td <?php echo $estiloClasstd;?>>&nbsp;<?php echo $TIPO;?></td>
								<td <?php echo $estiloClasstd;?>>&nbsp;<?php echo $CODRED;?></td>
								<td <?php echo $estiloClasstd;?>>&nbsp;<?php echo $UNIDADE;?></td>
								<td <?php echo $estiloClasstd;?>>&nbsp;
									<?php
										if($VALORTRP==""){
											echo "-";
										}
										else{
											echo converte_valor_estoques($VALORTRP);
										}
									?>
									<input name="ValorTrpItem[<?php echo $ORDEM;?>]" value="<?php echo $VALORTRP;?>" type="hidden"/>
								</td>
								<td <?php echo $estiloClasstd;?>>&nbsp;<?php echo converte_valor_estoques($QUANTIDADE);?>
									<input class="dinheiro4casas"  name="quantidadeItem[<?php echo $ORDEM;?>]" id="quantidadeItem[<?php echo $ORDEM;?>]" value="<?php echo converte_valor_estoques($QUANTIDADE);?>" type="hidden" />
								</td>

								<td <?php echo $estiloClasstd;?>>&nbsp;
									<?php
									//Se é individual mostra o valor sem pode ediar
									if($intQuantidadeSolicitacoes==1){?>
										<span id="spanValorEstimadoItem[<?php echo $ORDEM;?>]"><?php echo converte_valor_estoques($VALORESTIMADO)?></span>
										<input type="hidden" size="16" class="dinheiro4casas" maxlength="16" onKeyUp="AtualizarValorTotal('<?php echo $ORDEM;?>');" id="valorEstimadoItem[<?php echo $ORDEM;?>]" name="valorEstimadoItem[<?php echo $ORDEM;?>]" value="<?php echo converte_valor_estoques($VALORESTIMADO)?>" />
									<?php }
									else{?>
										<span style="display:none;" id="spanValorEstimadoItem[<?php echo $ORDEM;?>]"><?php echo converte_valor_estoques($VALORESTIMADO)?></span>
										<input type="text" size="16" class="dinheiro4casas" maxlength="16" onKeyUp="AtualizarValorTotal('<?php echo $ORDEM;?>');" name="valorEstimadoItem[<?php echo $ORDEM;?>]" id="valorEstimadoItem[<?php echo $ORDEM;?>]" value="<?php echo converte_valor_estoques($VALORESTIMADO)?>" />
									<?php } ?>
								</td>

								<td <?php echo $estiloClasstd;?>>&nbsp;
									<span id="spanValorTotalItem[<?php echo $ORDEM;?>]"><?php echo converte_valor_estoques($VALORTOTALESTIMADO); ?></span>
									<input  type="hidden" name="valorTotalItem[<?php echo $ORDEM;?>]" id="valorTotalItem[<?php echo $ORDEM;?>]" value="<?php echo converte_valor_estoques($VALORTOTALESTIMADO); ?>" />
								</td>

							<?php
							if($RegistroPreco!="S"){?>
								<td <?php echo $estiloClasstd;?>>&nbsp;
									<?php
									//Se for nao for agrupamento exibo o valor da tabela
									if($intQuantidadeSolicitacoes==1){?>
										<input type="hidden" name="quantidadeExercicioItem[<?php echo $ORDEM;?>]" id="quantidadeExercicioItem[<?php echo $ORDEM;?>]" value="<?php echo converte_valor_estoques($QUANTIDADEEXERCICIO); ?>"  />
										<span><?php echo converte_valor_estoques($QUANTIDADEEXERCICIO); ?></span>
								<?php }
									else{
										if($intQuantidadeItens==1 && $QUANTIDADE==1){?>
										<input type="hidden" name="quantidadeExercicioItem[<?php echo $ORDEM;?>]" id="quantidadeExercicioItem[<?php echo $ORDEM;?>]" value="<?php echo converte_valor_estoques($QUANTIDADEEXERCICIO); ?>"  />
										<span><?php echo converte_valor_estoques($QUANTIDADEEXERCICIO); ?></span>
								<?php 	}
									else{?>
										<input type="text" class="dinheiro4casas" onKeyUp="AtualizarValorTotal('<?php echo $ORDEM;?>');" name="quantidadeExercicioItem[<?php echo $ORDEM;?>]" id="quantidadeExercicioItem[<?php echo $ORDEM;?>]" value="<?php echo converte_valor_estoques($QUANTIDADEEXERCICIO); ?>"  />
								<?php 	}
									}?>
								</td>

								<td <?php echo $estiloClasstd;?>>&nbsp;
									<?php
									//if($intQuantidadeSolicitacoes==1){
										$VALOREXERCICIO = $VALOREXERCICIO;/*
									}else{
										$VALOREXERCICIO = $QUANTIDADEEXERCICIO * $VALORESTIMADO  ;
									}*/
									if($intQuantidadeSolicitacoes==1){
										$VALOREXERCICIO = $VALOREXERCICIO;?>
										<input type="hidden" name="valorExercicioItem[<?php echo $ORDEM;?>]" id="valorExercicioItem[<?php echo $ORDEM;?>]" value="<?php echo converte_valor_estoques($VALOREXERCICIO); ?>"  />
										<span  id="spanValorExercicioItem[<?php echo $ORDEM;?>]"><?php echo converte_valor_estoques($VALOREXERCICIO); ?></span>
									<?php }
									else{
										if($intQuantidadeItens==1 && $QUANTIDADE==1){?>
											<input type="text" onKeyUp="AtualizarValorTotal('<?php echo $ORDEM;?>');" name="valorExercicioItem[<?php echo $ORDEM;?>]" id="valorExercicioItem[<?php echo $ORDEM;?>]" value="<?php echo converte_valor_estoques($VALOREXERCICIO); ?>"  size="16" class="dinheiro4casas" maxlength="16"/>
									<?php
										}
										else{?>
											<input type="hidden" name="valorExercicioItem[<?php echo $ORDEM;?>]" id="valorExercicioItem[<?php echo $ORDEM;?>]" value="<?php echo converte_valor_estoques($VALOREXERCICIO); ?>"  />
											<span  id="spanValorExercicioItem[<?php echo $ORDEM;?>]"><?php echo converte_valor_estoques($VALOREXERCICIO); ?></span>
									<?php }
									}
									?>
								</td>

								<td <?php echo $estiloClasstd;?>>&nbsp;
									<span id="spanValorDemaisExercicioItem[<?php echo $ORDEM;?>]"> <?php if(($VALORTOTALESTIMADO - $VALOREXERCICIO)>0){ echo converte_valor_estoques(($VALORTOTALESTIMADO - $VALOREXERCICIO));} else{ echo converte_valor_estoques(0);} ?> </span>
								</td>
							<?php
								}?>
							</tr>
							<?php
							}
						}
						else{
							//Caso tenha solicitacoes novas mostrar os itens da novas solicitacoes
							$intQuantidadeItens = count($listaIntens);
							$ORDEM = 0;
							foreach ($listaNovosIntens as $Itens){
								$ORDEM++;
								if(count($arrSolicitacoesNovas)>1){
									$isAgrupamento = true;
								}
								else{
									$isAgrupamento = false;
								}?>
								<tr>
									<td <?php echo $estiloClasstd;?>>&nbsp;<?php echo $ORDEM;?>
										<input type="hidden" name="ordem[<?php echo $ORDEM;?>]" value = "<?php echo $ORDEM;?>"/>
										<input type="hidden" name="codRedItem[<?php echo $ORDEM;?>]" value = "<?php echo $Itens['codRed'];?>"/>
										<input type="hidden" name="tipoItem[<?php echo $ORDEM;?>]" value = "<?php echo $Itens['Tipo'];?>"/>
										<input type="hidden" name="lote[<?php echo $ORDEM;?>]" value="1"/>
									</td>
									<td <?php echo $estiloClasstd;?>>&nbsp;<?php echo $Itens['Descricao'];?></td>
									<td <?php echo $estiloClasstd;?>>&nbsp;<?php echo $Itens['Tipo'];?></td>
									<td <?php echo $estiloClasstd;?>>&nbsp;<?php echo $Itens['codRed'];?></td>
									<td <?php echo $estiloClasstd;?>>&nbsp;<?php echo $Itens['Unid'];?></td>
									<td <?php echo $estiloClasstd;?>>&nbsp;
									<?php
								if($Itens['Tipo']=="CADUM"){
									$valorTrp = calculaValorTrp($Itens['codRed']);
									if($valorTrp==""){
										echo "-";
									}
									else{
										echo converte_valor_estoques($valorTrp); //SERA CALCULADO
									}
								}
								else{
									$valorTrp = "";
									echo "-"; //SERA CALCULADO
								}
								?>
									</td>
									<td <?php echo $estiloClasstd;?>>&nbsp;<?php echo converte_valor_estoques($Itens['Quantidade']);?>
										<input class="dinheiro4casas"  name="quantidadeItem[<?php echo $ORDEM;?>]" id="quantidadeItem[<?php echo $ORDEM;?>]" value="<?php echo converte_valor_estoques($Itens['Quantidade']);?>" type="hidden" />
									</td>
									<td <?php echo $estiloClasstd;?>>&nbsp;
							<?php
								//Se é individual mostra o valor sem pode editar
									if(!$isAgrupamento){
										$valorEstimado = $Itens['ValorEstimado'];
										if(isset($arrValorEstimadoItem[$ORDEM])&&$arrValorEstimadoItem[$ORDEM]!=""){
											$valorEstimado = $arrValorEstimadoItem[$ORDEM];
										} ?>
										<span id="spanValorEstimadoItem[<?php echo $ORDEM;?>]"><?php echo converte_valor_estoques($valorEstimado)?></span>
										<input type="hidden" size="16" class="dinheiro4casas" maxlength="16" onKeyUp="AtualizarValorTotal('<?php echo $ORDEM;?>');" id="valorEstimadoItem[<?php echo $ORDEM;?>]" name="valorEstimadoItem[<?php echo $ORDEM;?>]" value="<?php echo converte_valor_estoques($valorEstimado)?>" />
									<?php 	}
									else{
									//Se nao tenho valor trp $valorEstimado é zero más pode ser editado
										if($valorTrp==""){
											$valorEstimado = 0;
										}
										else{
											$valorEstimado = $valorTrp;
										}

										if(isset($arrValorEstimadoItem[$ORDEM])&&$arrValorEstimadoItem[$ORDEM]!=""){
											$valorEstimado = $arrValorEstimadoItem[$ORDEM];
										}?>
										<span style="display:none;" id="spanValorEstimadoItem[<?php echo $ORDEM;?>]"><?php echo converte_valor_estoques($valorEstimado)?></span>
										<input type="text" size="16" class="dinheiro4casas" maxlength="16" onKeyUp="AtualizarValorTotal('<?php echo $ORDEM;?>');" name="valorEstimadoItem[<?php echo $ORDEM;?>]" id="valorEstimadoItem[<?php echo $ORDEM;?>]" value="<?php echo converte_valor_estoques($valorEstimado)?>" />
								<?php
									}
									$valorTotalItem = $valorEstimado * $Itens['Quantidade'];
								?>
									</td>
									<td <?php echo $estiloClasstd;?>>&nbsp;
										<span id="spanValorTotalItem[<?php echo $ORDEM;?>]"><?php echo converte_valor_estoques($valorTotalItem); ?></span>
										<input  type="hidden" name="valorTotalItem[<?php echo $ORDEM;?>]" id="valorTotalItem[<?php echo $ORDEM;?>]" value="<?php echo converte_valor_estoques($valorTotalItem); ?>" />
									</td>

						<?php  if($RegistroPreco!="S"){?>
									<td <?php echo $estiloClasstd;?>>&nbsp;
							<?php
							//Se for nao for agrupamento exibo o valor da tabela
									if(!$isAgrupamento){?>
										<input type="hidden" name="quantidadeExercicioItem[<?php echo $ORDEM;?>]" id="quantidadeExercicioItem[<?php echo $ORDEM;?>]" value="<?php echo converte_valor_estoques($Itens['QtdExercicio']); ?>"  />
										<span><?php echo converte_valor_estoques($Itens['QtdExercicio']); ?></span>
							<?php 	}
									else{
										if($intQuantidadeItens == 1 && $Itens['Quantidade'] == 1){?>
											<input type="hidden" name="quantidadeExercicioItem[<?php echo $ORDEM;?>]" id="quantidadeExercicioItem[<?php echo $ORDEM;?>]" value="<?php echo converte_valor_estoques($Itens['QtdExercicio']); ?>"  />
											<span><?php echo converte_valor_estoques($Itens['QtdExercicio']); ?></span>
							<?php 		}
										else{?>
											<input class="dinheiro4casas" onKeyUp="AtualizarValorTotal('<?php echo $ORDEM;?>');" type="text" name="quantidadeExercicioItem[<?php echo $ORDEM;?>]" id="quantidadeExercicioItem[<?php echo $ORDEM;?>]" value="<?php echo converte_valor_estoques($Itens['QtdExercicio']); ?>"  />
							<?php 		}
									}?>
									</td>

									<td <?php echo $estiloClasstd;?>>&nbsp;
							 <?php
									//Se for nao for agrupamento exibo o valor da tabela

									//$VALOREXERCICIO NO AGRUPAMENTO
									if(!$isAgrupamento){?>
										<input type="hidden" name="valorExercicioItem[<?php echo $ORDEM;?>]" id="valorExercicioItem[<?php echo $ORDEM;?>]" value="<?php echo converte_valor_estoques($Itens['ValorExercicio']); ?>"  />
										<span  id="spanValorExercicioItem[<?php echo $ORDEM;?>]"><?php echo converte_valor_estoques($Itens['ValorExercicio']); ?></span>
									<?php }
									else{
										if($intQuantidadeItens == 1 && $Itens['Quantidade'] == 1){?>
											<input type="text" onKeyUp="AtualizarValorTotal('<?php echo $ORDEM;?>');" name="valorExercicioItem[<?php echo $ORDEM;?>]" id="valorExercicioItem[<?php echo $ORDEM;?>]" value="<?php echo converte_valor_estoques($Itens['ValorExercicio']); ?>"  />
									<?php }
										else{?>
											<input type="hidden" name="valorExercicioItem[<?php echo $ORDEM;?>]" id="valorExercicioItem[<?php echo $ORDEM;?>]" value="<?php echo converte_valor_estoques($Itens['ValorExercicio']); ?>"  />
											<span  id="spanValorExercicioItem[<?php echo $ORDEM;?>]"><?php echo converte_valor_estoques($Itens['ValorExercicio']); ?></span>
									<?php }
									}?>
									</td>

									<td <?php echo $estiloClasstd;?>>&nbsp;
										<span id="spanValorDemaisExercicioItem[<?php echo $ORDEM;?>]"><?php echo converte_valor_estoques(($valorTotalItem - ($Itens['ValorExercicio']))); ?></span>
									</td>
							<?php }
							else{?>
								<input type="hidden" name="quantidadeExercicioItem[<?php echo $ORDEM;?>]" id="quantidadeExercicioItem[<?php echo $ORDEM;?>]" value="<?php echo converte_valor_estoques($Itens['QtdExercicio']); ?>"  />
								<input type="hidden" name="valorExercicioItem[<?php echo $ORDEM;?>]" id="valorExercicioItem[<?php echo $ORDEM;?>]" value="<?php echo converte_valor_estoques($Itens['ValorExercicio']); ?>"  />
							<?php }?>
							</tr>
							<?php
							}
						} ?>
						</table>
					</td>
				</tr>
				<tr>
					<td class="textonormal" align="right" colspan="4">
						<input type="hidden" id="quantidadeTotalItens" value="<?php echo $ORDEM; ?>" />
						<input type="button" name="Alterar" value="Alterar" class="botao" onClick="javascript:enviar('AlterarLicitacao')">
						<input type="button" name="Alterar" value="Excluir" class="botao" onClick="javascript:enviar('Excluir')">
						<input type="button" name="Alterar" value="Voltar" class="botao" onClick="javascript:enviar('Voltar')">
					</td>
				</tr>
				<script type="text/javascript">
					AtualizarValorTotal('1');
				</script>
			</table>
		</td>
	</tr>
	<!-- Fim do Corpo -->
</table>
</form>
</body>
<?php $db->disconnect(); ?>
</html>

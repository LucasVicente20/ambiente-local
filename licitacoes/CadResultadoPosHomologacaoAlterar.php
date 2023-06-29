<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadResultadoPosHomologacaoAlterar.php
# Autor:    Raphael Borborema
# Data:     19/03/2012
# Objetivo: Manutenção de resultado de licitação pós homologação
#-------------------------------------------------------------------------
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------

$programa = "CadResultadoPosHomologacaoAlterar.php";

# Acesso ao arquivo de funções #
require_once("../compras/funcoesCompras.php");

#incluindo funcoes de ajuda
require_once("funcoesComplementaresLicitacao.php");

# Executa o controle de segurança #
session_start();

Seguranca();

AddMenuAcesso('/compras/ConsAcompSolicitacaoCompra.php');
AddMenuAcesso('/compras/RotDadosFornecedor.php');


# Variáveis para teste
$desabilitarChecagemFornecedorSistemaMercantil = false; // correto é false. se true, permite inclusão de fornecedores que não passaram na checagem do cadastro mercantil

# Abrindo Conexão
$db = Conexao();
$dbOracle = ConexaoOracle();

function temValor($valor){
	if($valor==""){
		return false;
	}
	if($valor==null){
		return false;
	}
	if(moeda2float($valor)=="0"){
		return false;
	}
	
	return true;
}

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST" ){
		$Botao        					= $_POST['Botao'];
		
		$intSeqFornecedor 		= $_POST['intSeqFornecedor'];
		$novoCpfCnpj 			= $_POST['novoCpfCnpj'];
		$validaCnpj				= $_POST['validaCnpj'];
		
		
		$intCodUsuario 			        = $_SESSION['_cusupocodi_'];
		$perfilCorporativo  			= $_SESSION['_fperficorp_'];
		$GrupoUsuario					= $_SESSION['_cgrempcodi_'];
		
		$Processo             = $_POST['Processo'];
		$ProcessoAno          = $_POST['ProcessoAno'];
		$ComissaoCodigo       = $_POST['ComissaoCodigo'];
		$OrgaoLicitanteCodigo = $_POST['OrgaoLicitanteCodigo'];
		
		$arrTipo =  $_POST["arrTipo"];
		$arrQuantidadeExercicio = $_POST["arrQuantidadeExercicio"];
		$arrQuantidade = $_POST["arrQuantidade"];
		$arrCodRed	   =  $_POST["arrCodRed"];
		$arrValorLogrado = $_POST['arrValorLogrado'];
		$arrValorEstimado = $_POST['arrValorEstimado'];
		$arrValorLogradoExercicio = $_POST['arrValorLogradoExercicio'];
		$arrCpfCnpj				  = $_POST['arrCpfCnpj'];
		$arrSequencial = $_POST['arrSequencial'];
		$arrMarca = $_POST['arrMarca'];
		$arrModelo = $_POST['arrModelo'];
		$arrMotivos = $_POST['arrMotivos'];
		$arrDotacaoBloqueio = $_POST['arrDotacaoBloqueio'];
		
		
		
		
	
}else{
	
		if((!temValor($_GET['Processo']))||(!temValor($_GET['ProcessoAno']))||(!temValor( $_GET['ComissaoCodigo']))||(!temValor( $_GET['OrgaoLicitanteCodigo']))){
			
			$Mensagem = urlencode("Alguns dados não foram enviador corretamente para o programa.");
			$Url = "CadResultadoPosHomologacaoSelecionar.php?Mensagem=$Mensagem&Mens=1&Tipo=2&Critica=0";
			if (!in_array($Url,$_SESSION['GetUrl'])){
				$_SESSION['GetUrl'][] = $Url;
			}
			header("location: ".$Url);
			exit;
		}
		
		
		
		$Processo             = $_GET['Processo'];
		$ProcessoAno          = $_GET['ProcessoAno'];
		$ComissaoCodigo       = $_GET['ComissaoCodigo'];
		$OrgaoLicitanteCodigo = $_GET['OrgaoLicitanteCodigo'];

}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;



									
$db = Conexao();

//Pegando os dados da licitação
$sql  = "SELECT
			LIC.CCOMLICODI, COM.ECOMLIDESC,
			LIC.CLICPOPROC, LIC.ALICPOANOP,
			LIC.CMODLICODI, MOD.EMODLIDESC, 
			LIC.FLICPOREGP, LIC.CLICPOCODL, 
			LIC.ALICPOANOL, LIC.XLICPOOBJE,LIC.CORGLICODI
		FROM 
			SFPC.TBLICITACAOPORTAL LIC
		INNER JOIN
			SFPC.TBCOMISSAOLICITACAO COM
				ON LIC.CCOMLICODI = COM.CCOMLICODI
		INNER JOIN
			SFPC.TBMODALIDADELICITACAO MOD
				ON LIC.CMODLICODI = MOD.CMODLICODI
		WHERE 
			LIC.CLICPOPROC = $Processo
			AND LIC.ALICPOANOP = $ProcessoAno
			AND LIC.CCOMLICODI = $ComissaoCodigo
			AND LIC.corglicodi = $OrgaoLicitanteCodigo
		ORDER BY LIC.CCOMLICODI ASC";

$res = $db->query($sql);

if( PEAR::isError($res) ){
	$CodErroEmail  = $res->getCode();
	$DescErroEmail = $res->getMessage();
	var_export($DescErroEmail);
	ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
}else{
	$Linha = $res->fetchRow();
}
if ( isset($Linha[6]) && $Linha[6] != "" ){
	if ( $Linha[6] == "S"){
		$RegistroPreco = true;
	}else if ( $Linha[6] == "N"){
		$RegistroPreco = false;
	}
}

//Busacando e carregando array com as solicitacoes da licitacao
$sqlSolicitacoes = " SELECT  csolcosequ ,clicpoproc , alicpoanop , cgrempcodi ,ccomlicodi ,corglicodi
		FROM SFPC.TBSOLICITACAOLICITACAOPORTAL SOL WHERE SOL.CLICPOPROC = $Processo AND SOL.ALICPOANOP = $ProcessoAno
	AND SOL.CCOMLICODI = $ComissaoCodigo AND  SOL.corglicodi = $OrgaoLicitanteCodigo AND SOL.cgrempcodi =". $_SESSION['_cgrempcodi_'] ; 

$resultSoli = $db->query($sqlSolicitacoes);
$qtdSolicitacoes = $resultSoli->numRows();
if($qtdSolicitacoes>1){
	$isAgrupamento = true;
}else{
	$isAgrupamento = false;
}
if( PEAR::isError($resultSoli) ){
	ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlSolicitacoes");
}
while( $LinhaSoli = $resultSoli->fetchRow() ){
	$arrSolicitacoes[] = 	$LinhaSoli[0];
}

/* BUSCANDO OS ITENS DA LICITACAO */
$sql = "SELECT ITEM.CITELPSEQU , ITEM.CMATEPSEQU , ITEM.CSERVPSEQU , ITEM.AITELPORDE
, ITEM.AITELPQTSO , ITEM.VITELPUNIT , ITEM.AITELPQTEX , ITEM.VITELPVEXE
, MAT.EMATEPDESC, UNIDADE.EUNIDMSIGL , SERV.ESERVPDESC , ITEM.CITELPNUML  , ITEM.VITELPVLOG 
, ITEM.EITELPMARC , ITEM.EITELPMODE , ITEM.CMOTNLSEQU , ITEM.AFORCRSEQU
FROM 
SFPC.TBITEMLICITACAOPORTAL ITEM  LEFT JOIN SFPC.TBMATERIALPORTAL MAT ON (MAT.CMATEPSEQU = ITEM.CMATEPSEQU) 
LEFT JOIN  SFPC.TBSERVICOPORTAL SERV ON (SERV.CSERVPSEQU = ITEM.CSERVPSEQU)
LEFT JOIN  SFPC.TBUNIDADEDEMEDIDA UNIDADE ON (MAT.CUNIDMCODI = UNIDADE.CUNIDMCODI)
WHERE  ITEM.clicpoproc = $Processo
AND    ITEM.alicpoanop = $ProcessoAno
AND    ITEM.cgrempcodi = ".$_SESSION['_cgrempcodi_']."
AND    ITEM.ccomlicodi = $ComissaoCodigo
AND    ITEM.corglicodi = $OrgaoLicitanteCodigo ";


if($intSeqFornecedor!=""){
	
	$sql.= " AND ITEM.AFORCRSEQU = $intSeqFornecedor ";	
}

$sql.= " ORDER BY ITEM.CITELPNUML , ITEM.AITELPORDE , ITEM.CITELPSEQU";
$resItensLicitacao  = $db->query($sql);
$intQuantidadeItens = $resItensLicitacao->numRows();		
/*Consula lista de motivo não logrado*/
$sql = "SELECT  CMOTNLSEQU , EMOTNLNOME FROM  SFPC.TBMOTIVOITEMNAOLOGRADO ORDER BY EMOTNLNOME ASC ";
$resMotivo  = $db->query($sql);
$arrMotivosLista = array();
while($listaMotivos = $resMotivo->fetchRow()){
	$arrMotivosLista[$listaMotivos[0]] = $listaMotivos[1];
}





if($Botao=="Voltar"){
	$Url = "CadResultadoPosHomologacaoSelecionar.php";
	if (!in_array($Url,$_SESSION['GetUrl'])){
			$_SESSION['GetUrl'][] = $Url;
	}
	header("location: $Url");
	exit;
}else if($Botao=="Selecionar"){
		if($intSeqFornecedor==""){
			$Mens      = 1;
			$Tipo      = 2;
			$Mensagem .= "<a href=\"javascript:document.Licitacao.ComissaoCodigo.focus();\" class=\"titulo2\">Selecione um fornecedor.</a><br>";
			$Botao = "";
		}
}else if($Botao=="Totalizar" || $Botao=="Salvar"){
	//Removi validação para verificar se a licitação já está homologada

	//Validando o novo fornecedor antes de comecar a validar os itens
	$dadosFornecedor = checaSituacaoFornecedor($db,$novoCpfCnpj);
	if(!temValor($novoCpfCnpj)){
		
		adicionarMensagem("<a href=\"javascript:document.getElementById('novoCpfCnpj').focus();\" class='titulo2'>Novo Fornecedor inválido </a><br>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
	}else{
		
		$dadosFornecedor = checaSituacaoFornecedor($db,$novoCpfCnpj);
		
		if(($dadosFornecedor["erro"] != 0 or $dadosFornecedor["inabilitado"]) and !($desabilitarChecagemFornecedorSistemaMercantil and $dadosFornecedor["inabilitadoPorDebitoCadastroMercantil"])){
			if(!($desabilitarChecagemFornecedorSistemaMercantil and $dadosFornecedor["inabilitadoPorDebitoCadastroMercantil"]) and $codigoResposta==$GLOBALS["RETORNO_CADASTRO_IMOBILIARIO_ARQUIVO_NAO_ENCONTRADO"]){
				
				// ignorar erro caso esteja desabilitado checagem no cadastro mercantil
				mostrarMensagemErroUnica($dadosFornecedor["mensagem"]." no novo fornecedor.");
			}else{
				
				adicionarMensagem("<a href=\"javascript:document.getElementById('novoCpfCnpj').focus();\" class='titulo2'> '".$dadosFornecedor["mensagem"]."' </a><br>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
			}
		}
	}
	if($Mens ==0){
	//Validacões de Itens
	foreach ($arrCodRed as $key => $codRed) {	
		if($arrTipo[$key]=="CADUM"){
			$arrTipoCod[$key] = TIPO_ITEM_MATERIAL;
		}else if($arrTipo[$key]=="CADUS"){
			$arrTipoCod[$key] = TIPO_ITEM_SERVICO;
		}else{
			adicionarMensagem("<a href=\"javascript:void(0);\" class='titulo2'>O tipo do item $key não foi definido </a><br>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
		}
		//Atribuindo o valor do novo fornecedor
		$arrCpfCnpj[$key] = $novoCpfCnpj;
		
		//Caso tenha um motivo selecionado para o item, todos os outros campos nao podem ser preechidos 
		if($arrMotivos[$key]!=""){
			if((temValor($arrValorLogrado[$key]))||(temValor($arrCpfCnpj[$key]))||(temValor($arrMarca[$key]))||(temValor($arrModelo[$key]))||(temValor($arrQuantidadeExercicio[$key]))||(temValor($arrValorLogradoExercicio[$key]))){
				adicionarMensagem("<a href=\"javascript:document.getElementById('arrValorLogrado[$key]').focus();\" class='titulo2'>O preenchimento do motivo não logrado só é permitido quando as demais informações do item são nulas no item($key).</a><br>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
			}
		}else{
			//Caso contrario , todos os outros tem que ser preechidos	
			if(!temValor($arrValorLogrado[$key])){
				adicionarMensagem("<a href=\"javascript:document.getElementById('arrValorLogrado[$key]').focus();\" class='titulo2'>Valor Logrado inválido no item($key)</a><br>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
			}
			//Validando marca
			if(!temValor($arrMarca[$key])){
				adicionarMensagem("<a href=\"javascript:document.getElementById('arrMarca[$key]').focus();\" class='titulo2'>Marca inválida no item($key)</a><br>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
			}
			//Validando marca
			if(!temValor($arrModelo[$key])){
				adicionarMensagem("<a href=\"javascript:document.getElementById('arrModelo[$key]').focus();\" class='titulo2'>Modelo inválido no item($key)</a><br>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
			}

			//So faco validacao de exercicio se nao for registro de preco faz algumas validações
			if(!$RegistroPreco){
				if(!temValor($arrValorLogradoExercicio[$key])){
					adicionarMensagem("<a href=\"javascript:document.getElementById('arrValorLogradoExercicio[$key]').focus();\" class='titulo2'>Valor Logrado Exercicio inválido no item($key)</a><br>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
				}
				if(moeda2float($arrQuantidadeExercicio[$key]) > moeda2float($arrQuantidade[$key])){
					adicionarMensagem("<a href=\"javascript:document.getElementById('arrQuantidadeExercicio[$key]').focus();\" class='titulo2'>Informe quantidade no exercício menor ou igual a quantidade do item($key)</a><br>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
				}
				if(moeda2float($arrValorLogradoExercicio[$key]) > moeda2float($arrValorEstimado[$key])*moeda2float($arrQuantidade[$key])){
					adicionarMensagem("<a href=\"javascript:document.getElementById('arrValorLogradoExercicio[$key]').focus();\" class='titulo2'>Valor total do exercício é maior que o valor total do item ($key)</a><br>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
				}
			}
			
			
			
			
			
			//Sempre faco as validacoes do fornecedor
			# verificar se o fornecedor fornece o grupo ao qual o material pertence
			$arrSequecialFornecedor[$key] = getSequencialFromCpfCnpj($db, removeSimbolos($arrCpfCnpj[$key]));
			$mesmoGrupo = forneceMaterialServico($db,$arrSequecialFornecedor[$key], $arrTipoCod[$key], $TIPO_ITEM_MATERIAL);
			if($dadosFornecedor['tipo']=='E'){
				adicionarMensagem("<a href=\"javascript:document.getElementById('MaterialFornecedor[".$pos."]').focus();\" class='titulo2'> 'Fornecedor é do tipo Estoques e não pode fazer solicitação de compra' em fornecedor do material ord ".($key)."</a><br>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
			}elseif($TipoCompra!=$TIPO_COMPRA_DIRETA and $dadosFornecedor['tipo']=='D'){
				adicionarMensagem("<a href=\"javascript:document.getElementById('MaterialFornecedor[".$pos."]').focus();\" class='titulo2'> 'Fornecedor é do tipo Compra Direta e não pode fazer solicitação de compra que não seja Direta' em fornecedor do material ord ".($key)."</a><br>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
			}else if(!$mesmoGrupo){
				adicionarMensagem("<a href=\"javascript:document.getElementById('MaterialFornecedor[".$pos."]').focus();\" class='titulo2'> 'Fornecedor com grupo diferente de material' em fornecedor do material ord ".($key)."</a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
			}
			
			
			
			# 	+- ['posicaoArray'] - posição do item na lista de itens da scc, separadas por material ou serviço
			# 	+- ['posicao'] - posição do item no ARRAY (no formulario HTML) dos itens da scc, separadas por material ou serviço
			#		+- ['codigo'] - número do material ou serviço
			#		+- ['tipo'] - se o item é material ou serviço. Usar as constantes TIPO_ITEM_MATERIAL e TIPO_ITEM_SERVICO.
			#		+- ['quantidadeItem'] - quantidade do item
			#		+- ['valorItem'] - valor do item
			#		+- ['reservas'] - array com números dos bloqueios ou dotações, do item.
			//Montando array de itens para usar a funcao de validacao de bloqueio e dotacao
			
			$arrayValiDotacaoBloqueio[$key]["posicaoItem"] = $key;
			$arrayValiDotacaoBloqueio[$key]["posicao"] = $key;
			$arrayValiDotacaoBloqueio[$key]["codigo"] = $arrCodRed[$key];
			$arrayValiDotacaoBloqueio[$key]["tipo"] = $arrTipoCod[$key];
			$arrayValiDotacaoBloqueio[$key]["quantidadeItem"] = $arrQuantidade[$key];
			$arrayValiDotacaoBloqueio[$key]["valorItem"] = $arrValorLogrado[$key];
			$arrayValiDotacaoBloqueio[$key]["reservas"] = $arrDotacaoBloqueio[$key];
			
			
			
			
		}
		

	}
	
	
	
	//Fazendo a validacao de bloqueio e dotacao
	if($RegistroPreco){
		$tipoReserva = TIPO_RESERVA_ORCAMENTARIA_DOTACAO;
	}else{
		$tipoReserva = TIPO_RESERVA_ORCAMENTARIA_BLOQUEIO;
	}
	
	try{
		if(!is_null($arrayValiDotacaoBloqueio)){
			validarReservaOrcamentariaScc($db, $dbOracle, null, $tipoReserva, $arrayValiDotacaoBloqueio , true);
		}
	}catch(ExcecaoReservaInvalidaEmItemScc $e){
		$pos = $e->posicaoItemArray;
		adicionarMensagem("<a href=\"javascript:document.getElementById('arrValorLogrado[$pos]').focus();\" class='titulo2'>".$e->getMessage()."</a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
	}catch(ExcecaoPendenciasUsuario $e){
		adicionarMensagem($e->getMessage(), $GLOBALS["TIPO_MENSAGEM_ERRO"]);
	}
	
	}
	
	if($Mens==0 ){
		if($Botao=="Salvar"){
			
			$db->query("BEGIN TRANSACTION");
			
			
			//Atualizando a licitacao
			$sql = " UPDATE SFPC.TBLICITACAOPORTAL SET
			FLICPORESU = 'S' ,
			CUSUPOCODI = ".$_SESSION['_cusupocodi_'] ." ,
			TLICPOULAT = now()
			WHERE
			CLICPOPROC = $Processo
			AND ALICPOANOP = $ProcessoAno
			AND CCOMLICODI = $ComissaoCodigo
			AND corglicodi = $OrgaoLicitanteCodigo
			";
			
			$res = executarTransacao($db, $sql);
			if( PEAR::isError($res)){
				cancelarTransacao($db);
				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
			}
			
			foreach ($arrCodRed as $key => $codRed) {
			
			
			$flag = ($arrMotivos[$key]=="") ? "'S'" : 'NULL';
			
			$ValorLogrado = temValor($arrValorLogrado[$key]) ? moeda2float($arrValorLogrado[$key]) : 'NULL';
			$ValorLogradoExercicio = temValor($arrValorLogradoExercicio[$key]) ? moeda2float($arrValorLogradoExercicio[$key]) : 'NULL';
			$QuantidadeExercicio = temValor($arrQuantidadeExercicio[$key]) ? moeda2float($arrQuantidadeExercicio[$key]) : 0;
			$Marca = (temValor($arrMarca[$key])) ? "'".$arrMarca[$key]."'" : 'NULL';
			$Modelo = ($arrModelo[$key]!="") ? "'".$arrModelo[$key]."'" : 'NULL';
			$Motivos = ($arrMotivos[$key]!="") ? $arrMotivos[$key] : 'NULL';
				
			$SequecialFornecedor = ($arrSequecialFornecedor[$key]!="") ? $arrSequecialFornecedor[$key] : 'NULL';
				
			#Gravar os dados em SFPC.TBITEMLICITACAOPORTAL
			$sql = "UPDATE SFPC.TBITEMLICITACAOPORTAL SET
			CMOTNLSEQU = ".$Motivos ." ,
			VITELPVLOG = ".$ValorLogrado ." ,
			VITELPVEXE = ".$ValorLogradoExercicio ." ,
			AITELPQTEX = ".$QuantidadeExercicio ." ,
			AFORCRSEQU = ".$SequecialFornecedor ." ,
			EITELPMARC = ".$Marca ."  ,
			EITELPMODE = ".$Modelo ." ,
			CUSUPOCODI = ".$_SESSION['_cusupocodi_'] ." ,
			TITELPULAT =  now() ,
			FITELPLOGR = ".$flag."
			WHERE 
			clicpoproc = $Processo
			AND    alicpoanop = $ProcessoAno
			AND    cgrempcodi = ".$_SESSION['_cgrempcodi_']."
			AND    ccomlicodi = $ComissaoCodigo
			AND    corglicodi = $OrgaoLicitanteCodigo
			AND    citelpsequ = $arrSequencial[$key]
			;
			";
			
			$res = executarTransacao($db, $sql);
			if( PEAR::isError($res)){
				cancelarTransacao($db);
				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
			}
			
			
			
			
			
			}
			$db->query("COMMIT");
			$db->query("END TRANSACTION");
				
			# Envia mensagem para página selecionar #
			$Mensagem = urlencode("Resultado alterado com Sucesso");
						$Url = "CadResultadoPosHomologacaoSelecionar.php?Mensagem=$Mensagem&Mens=1&Tipo=1&Critica=0";
						if (!in_array($Url,$_SESSION['GetUrl'])){
			$_SESSION['GetUrl'][] = $Url;
			}
			header("location: $Url");
						exit;
		}
	
	}	
}
	

?>
<html>
<?php
# Carrega o layout padrão #
layout();
?>
<script language="javascript" src="../janela.js" type="text/javascript"></script>
<script language="javascript" type="">

function enviar(valor){
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
// Recupera os dados do fornecedor 
function validaFornecedor(nomeCampoCpfCnpj,nomeCampoResposta){
	cpfCnpj = limpaCPFCNPJ(document.getElementById(nomeCampoCpfCnpj).value);
	carregamentoDinamico("<?php echo $GLOBALS["DNS_SISTEMA"];?>compras/RotDadosFornecedor.php","CPFCNPJ="+cpfCnpj,nomeCampoResposta);
	document.getElementById(nomeCampoCpfCnpj).value = formataCpfCnpj(cpfCnpj);
	document.getElementById('validaCnpj').value = document.getElementById(nomeCampoResposta).innerHTML;
}
function AtualizarValorTotal(linha){
	//Pegando a quantidade do item na linha que foi alterada
	quantidadeItem = moeda2float(document.getElementById('arrQuantidade['+linha+']').value);
	//Pegando Valor do item na linha que foi alterada
	valorEstimadoItem = moeda2float(document.getElementById('arrValorEstimado['+linha+']').value);
	
	valorLogradoItem = moeda2float(document.getElementById('arrValorLogrado['+linha+']').value);


	//# SO FACO SE TIVER EXERCÍCIO
	if(document.getElementById('registroPreco').value!="S"){
		
		 
		//Pegando Valor da quantidade do exercicio
		quantidadeExercicio = moeda2float(document.getElementById('arrQuantidadeExercicio['+linha+']').value);
		valorExercicio = (valorLogradoItem * quantidadeExercicio);
		document.getElementById('arrValorLogradoExercicio['+linha+']').value = float2moeda(valorExercicio);
		document.getElementById('spanValorLogradoExercicio['+linha+']').innerHTML = float2moeda(valorExercicio);

		document.getElementById('spanValorLogradoDemaisExercicios['+linha+']').innerHTML = float2moeda((valorLogradoItem*quantidadeItem)-valorExercicio);
		document.getElementById('arrValorLogradoDemaisExercicios['+linha+']').value = float2moeda((valorLogradoItem*quantidadeItem)-valorExercicio);

	}
	
	
	

	
	document.getElementById('spanTotalLogrado['+linha+']').innerHTML = float2moeda(valorLogradoItem*quantidadeItem);
	
	
	
	
}
<?php MenuAcesso(); ?>

</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="<?=$programa?>" method="post" name="formulario">

<input type="hidden" name="Botao" id="Botao"value="">
<br><br><br><br><br>
<table width="100%" cellpadding="3" border="0" summary="">
	<!-- Caminho -->
	<tr>
		<td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
		<td align="left" class="textonormal" colspan="2">
			<font class="titulo2">|</font>
			<a href="../index.php"><font color="#000000">Página Principal</font></a> &gt; Licitações &gt; Resultados &gt; Manter 
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
						 MANTER - RESULTADO DE LICITAÇÃO 
					</td>
				</tr>
				<tr>
				<td class="textonormal">
				<p align="justify">
				Para atualizar o(s) Resultado(s) da licitação, preencha os dados abaixo e clique no botão "Totalizar". Para exibir o Mapa de Resultados, atualize e clique no botão "Exibir Mapa Resumo".Para indicar o Motivo não logrado, deixar os campos sem preenchimento e selecionar o motivo.
				</p>
				</td>
				</tr>
				
			
				
				<tr>
					<td>

					<input name="Processo" type="hidden" value="<?php echo $Processo; ?>"/>
					<input name="ProcessoAno"  type="hidden"  value="<?php echo $ProcessoAno; ?>"/>
					<input name="ComissaoCodigo" type="hidden" value="<?php echo $ComissaoCodigo; ?>"/>
					<input name="OrgaoLicitanteCodigo" type="hidden" value="<?php echo $OrgaoLicitanteCodigo;?>"/>
						<table border="0" width="100%" summary="">
							
							<tr>
								<td width="20%" align="left" bgcolor="#DCEDF7" class="textonormal" colspan="1" >Comissão*</td>
								<td align="left" class="textonormal" colspan="3" >
									<label style="width:500px;"><?php echo $Linha[1];?></label>
									<input type="hidden" name="CodigoDaComissao" value="<?php echo $Linha[0];?>" />
								</td>
							</tr>
							<tr>
								<td align="left" bgcolor="#DCEDF7" class="textonormal" colspan="1" >Processo</td>
								<td align="left" class="textonormal" colspan="3" >
									<label><?php echo substr($Linha[2] + 10000,1); ?></label>
									<input type="hidden" name="NumeroDoProcesso" value="<?php echo substr($Linha[2] + 10000,1);?>" />
								</td>
							</tr>
							<tr>
								<td align="left" bgcolor="#DCEDF7" class="textonormal" colspan="1" >Ano</td>
								<td align="left" class="textonormal" colspan="3" >
									<label><?php echo $Linha[3]; ?></label>
									<input type="hidden" name="AnoDoExercicio" value="<?php echo $Linha[3];?>" />
								</td>
							</tr>
							<tr>
								<td align="left" bgcolor="#DCEDF7" class="textonormal" colspan="1" >Modalidade*</td>
								<td align="left" class="textonormal" colspan="3" >
									<label><?php echo $Linha[5]; ?></label>
								</td>
							</tr>
							<tr>
								<td align="left" bgcolor="#DCEDF7" class="textonormal" colspan="1" >Registro de Preço</td>
								<td align="left" class="textonormal" colspan="3" >
									<input type="hidden" id="registroPreco" name="registroPreco" value="<?php echo $Linha[6];?>"/>
									<label>
									<?php 
										if ( $RegistroPreco){
											echo "Sim";
										}else{
											echo "Não";
										}
									?>
									</label>
								</td>
							</tr>
							<tr>
								<td align="left" bgcolor="#DCEDF7" class="textonormal" colspan="1" >Licitação</td>
								<td align="left" class="textonormal" colspan="3" >
									<label><?php echo substr($Linha[7] + 10000,1); ?></label>
									
								</td>
							</tr>
							<tr>
								<td align="left" bgcolor="#DCEDF7" class="textonormal" colspan="1" >Ano da Licitação</td>
								<td align="left" class="textonormal" colspan="3" >
									<label><?php echo $Linha[8]; ?></label>
								</td>
							</tr>
							
							<td align="left" bgcolor="#DCEDF7" class="textonormal" colspan="1" >
								Objeto:
							</td>
							<td>
												<label class="textonormal" style="word-wrap:break-word;" ><?php echo $Linha[9];?></label>
							</td>
							<tr>
								<td align="left" bgcolor="#DCEDF7" class="textonormal" colspan="1" >
									Solicitação de Compra/Contratação-SCC*:
								</td>
								<td align="left" class="textonormal" colspan="3" >
										<select style="width:200px;" multiple="multiple">
											<?php
											foreach($arrSolicitacoes as $seqSoli){
												?>
											<option selected="selected" value="<?php echo $seqSoli;?>" ><?php echo getNumeroSolicitacaoCompra($db,$seqSoli);?></option>
											<?php 
												}
											?>
										</select>
								</td>
							</tr>
							<?php
								if($Botao!=""){
							?>
							<tr>
								<td align="left" bgcolor="#DCEDF7" class="textonormal" colspan="1" >Fornecedor Atual:</td>
								<td align="left" class="textonormal" colspan="3" >
								<input type="hidden" name="intSeqFornecedor" value="<?php echo $intSeqFornecedor; ?>">
								<?php
								if($intSeqFornecedor!=""){
											$sql = "SELECT AFORCRCCGC , AFORCRCCPF, NFORCRRAZS FROM SFPC.TBFORNECEDORCREDENCIADO WHERE AFORCRSEQU = $intSeqFornecedor ";
											$res  = $db->query($sql);
											$linha = resultLinhaUnica($res);
											if($linha[0]!=""){$CPFCNPJANTERIOR = $linha[0];}elseif ($linha[1]!=""){$CPFCNPJANTERIOR = $linha[0];}else{$CPFCNPJANTERIOR="";}
											$razaoSocial = $linha[2];
								}
								?>
									<label><?php echo FormataCpfCnpj($CPFCNPJANTERIOR) . " ".$razaoSocial;?> </label>
								</td>
							</tr>
							<?php
								}
							?>
							<tr>
								<td align="left" bgcolor="#DCEDF7" class="textonormal" colspan="1" >Novo Fornecedor:</td>
								<td align="left" class="textonormal" colspan="3" >
								<input name="novoCpfCnpj" id="novoCpfCnpj"  onchange="validaFornecedor('novoCpfCnpj','spanNovoCpfCnpj');" value="<?php echo FormataCpfCnpj($novoCpfCnpj); ?>"  type="text" size="18" maxlength="18" class=""  />
								<input type="hidden" name="validaCnpj" id="validaCnpj" value="<?php echo $validaCnpj;?>" />
								<span   id="spanNovoCpfCnpj">
									<?php
									if(!is_null($novoCpfCnpj)){
										$CPFCNPJ = removeSimbolos($novoCpfCnpj);
										$materialServicoFornecido=null;
										$TipoMaterialServico=null;
										include('../compras/RotDadosFornecedor.php');
										$db   = Conexao();
									}
									
									?>
									</span>
								
								</td>
							</tr>
						</table>
					</td>
				</tr>
				<?php 
				$estilotd = 'class="titulo3" align="center" bgcolor="#F7F7F7"';
				$estiloClasstd = 'class="textonormal" align="center" bgcolor="#F7F7F7"';
			
				
				if($Botao==""){
				//Buscando os fornecedores da licitação que so tenha pse não importada OU canselada
				$sql  = " 
				SELECT FORN.aforcrsequ , FORN.nforcrrazs , FORN.aforcrccgc , FORN.aforcrccpf   FROM 
				sfpc.tbfornecedorcredenciado FORN ,
				SFPC.TBPRESOLICITACAOEMPENHO PRE
				WHERE     PRE.clicpoproc = $Processo
				AND    PRE.alicpoanop = $ProcessoAno
				AND    PRE.cgrempcodi = ".$_SESSION['_cgrempcodi_']."
				AND    PRE.ccomlicodi = $ComissaoCodigo
				AND    PRE.corglicodi = $OrgaoLicitanteCodigo
				AND ( (PRE.TPRESOIMPO IS NULL) OR (PRE.TPRESOIMPO IS NOT NULL  AND PRE.dpresocsem IS NOT NULL) OR (PRE.TPRESOIMPO IS NOT NULL AND PRE.dpresoanue IS NOT NULL) ) 
				AND FORN.aforcrsequ =  PRE.aforcrsequ
				";
				$resFornecedoresNãoImportados  = $db->query($sql);
				if( PEAR::isError($resFornecedoresNãoImportados) ){
					ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
				}
				$qtdFornecedorNaoImportados = $resFornecedoresNãoImportados->numRows();
				?>	
				
				
				<tr>
					<td style="background-color:#F1F1F1;" colspan="4">
						<table   width="100%" cellspacing="0" cellpadding="3" bordercolor="#75ADE6" border="1"  >
							<tbody>
							<tr>
								<td align="center" bgcolor="#75ADE6" class="titulo3" colspan="4" >FORNECEDORES DA LICITAÇÃO COM PSE NÃO IMPORTADA OU ANULADA PELO SOFIN</td>
							</tr>
							<tr class="linhainfo">
									<td <?php echo $estilotd;?>>CPF/CNPJ</td>
									<td <?php echo $estilotd;?>>RAZÃO SOCIAL</td>
							</tr>
							<?php
								if($qtdFornecedorNaoImportados>0){
									while($listaFornec = $resFornecedoresNãoImportados->fetchRow()){
										if($listaFornec[2]==""){
											$strCpfCnpj = $listaFornec[3];
										}else{
											$strCpfCnpj = $listaFornec[2];
										}
										
										$razaoSocial =$listaFornec[1];
								?>
								<tr>
										
										<td <?php echo $estiloClasstd;?>><input name="intSeqFornecedor" type="radio" value="<?php echo $listaFornec[0]; ?>" > <?php echo $strCpfCnpj;?></td>
										<td <?php echo $estiloClasstd;?>><?php echo $razaoSocial;?></td>
								</tr>	
								<?php
									}
								}else{
							?>
								<tr>
										<td valign="top" colspan="2" class="textonormal" bgcolor="FFFFFF">Nenhum fornecedor encontrado.</td>
								</tr>
							<?php 
								}
							?>
							</tbody>
						</table>
					</td>
				</tr>			
					
				<?	
				}else{
				?>
				<tr>
					<td align="center" bgcolor="#75ADE6" class="titulo3" colspan="4" >ITENS DA SOLICITAÇÃO</td>
				</tr>
				
				<tr>
					<td style="background-color:#F1F1F1;" colspan="4">
						<table bordercolor="#75ADE6" border="1" cellspacing="" bgcolor="bfdaf2" width="100%" class="textonormal">
							
							<?php 
							$ORDEM = 0;
							$LOTEANTERIOR = "";
							$VALORSOMATORIO = 0;
							$VALORSOMATORIOGERAL = 0;
							$QTD = 0 ;
							$QTDLOGRADOS = 0;
							while($listaIntens = $resItensLicitacao->fetchRow()){
									$ORDEM ++;
									$INDICE = $listaIntens[3];
									
									//Se for Material
									if($listaIntens[1]!=""){
										$TIPO = "CADUM";
										$DESCRICAO = $listaIntens[8];
										$CODRED = $listaIntens[1];
										$UNIDADE = $listaIntens[9];
										$VALORTRP = calculaValorTrp($CODRED);
									}else{
										$TIPO = "CADUS";
										$DESCRICAO = $listaIntens[10]." - ";
										$CODRED = $listaIntens[2];
										$UNIDADE = "";
										$VALORTRP ="-";
									}
									
									$SENQUENCIAL = $listaIntens[0];
									
									$QUANTIDADE = $listaIntens[4];
									$VALORUNIT = $listaIntens[5];
									
									
									$VALORESTIMADO = $VALORUNIT;
									$VALORTOTALESTIMADO = $VALORESTIMADO * $QUANTIDADE;
									
									
									if(isset($arrValorLogrado[$ORDEM])){
										$VALORLOGRADO  = moeda2float($arrValorLogrado[$ORDEM]);
									}else{
										$VALORLOGRADO  = $listaIntens[12];
									}
									
									
									
									$TOTALLOGRADO  = $VALORLOGRADO * $QUANTIDADE;
									
									
									//Se possuo quantidade de exercicio exibo ela , se não exibo o valor da quantidade
									if(!isset($arrQuantidadeExercicio[$ORDEM])){
										if($listaIntens[6]!=""){
											$QUANTIDADEEXERCICIO = $listaIntens[6];
										}else{
											$QUANTIDADEEXERCICIO = $QUANTIDADE;
										}
									}else{
										$QUANTIDADEEXERCICIO = moeda2float($arrQuantidadeExercicio[$ORDEM]);
									}
									
									//VALOR LOGRADO EXERCICIO
									if(!isset($arrValorLogradoExercicio[$ORDEM])){
										if(!$RegistroPreco){
											if($listaIntens[7]){
												$VALORLOGRADOEXERCICIO = $listaIntens[7];
											}else{
												$VALORLOGRADOEXERCICIO = $QUANTIDADEEXERCICIO * $VALORLOGRADO;
											}
										}else{
											$VALORLOGRADOEXERCICIO = "";
										}
									}else{
										$VALORLOGRADOEXERCICIO = moeda2float($arrValorLogradoExercicio[$ORDEM]);
									}
									//ValorDemaisExercicios
									if(!$RegistroPreco){
										$VALORLOGRADODEMAISEXERCICIOS = ($QUANTIDADE * $VALORLOGRADO) - $VALORLOGRADOEXERCICIO;
									}
									
									
									
									
									//Marca
									if(isset($arrMarca[$ORDEM])){
										$MARCA = $arrMarca[$ORDEM];
									}else{
										$MARCA = $listaIntens[13];
									}
									
									//Marca
									if(isset($arrModelo[$ORDEM])){
										$MODELO = $arrModelo[$ORDEM];
									}else{
										$MODELO = $listaIntens[14];
									}
									//Motivo
									if(isset($arrMotivos[$ORDEM])){
										$MOTIVO = $arrMotivos[$ORDEM];
									}else{
										$MOTIVO = $listaIntens[15];
									}
									
									
									$LOTE = $listaIntens[11];
									
									
									
									
									//Pegando lista de DOTACAO E BLOQUEIO
										
										if($RegistroPreco){
											//Faco a busca pelos campos de Dotação AITCDOUNIDOEXER CITCDOUNIDOORGA CITCDOUNIDOCODI CITCDOTIPA AITCDOORDT  CITCDOELE1, CITCDOELE2, CITCDOELE3, CITCDOELE4, CITCDOFONT
											$sql = " SELECT aitldounidoexer, citldounidoorga, citldounidocodi, citldotipa, aitldoordt, citldoele1, citldoele2, citldoele3, citldoele4, citldofont
																							 FROM SFPC.tbitemlicitacaodotacao WHERE 
														 clicpoproc = $Processo 
												  		 AND alicpoanop = $ProcessoAno 
												 		 AND cgrempcodi = ".$_SESSION['_cgrempcodi_']." 
												  		 AND ccomlicodi = $ComissaoCodigo 
												 		 AND corglicodi = $OrgaoLicitanteCodigo 
														 AND citelpsequ = ". $SENQUENCIAL;
											
												
											$res  = $db->query($sql);
											if( PEAR::isError($res) ){
												$CodErroEmail  = $res->getCode();
												$DescErroEmail = $res->getMessage();
												ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
											}else{
												$valorTotalDisponivel = 0;
												while ( $linha = $res->fetchRow() ){
													$dotacaoArray = getDadosDotacaoOrcamentariaFromChave($dbOracle,$linha[0],$linha[1],$linha[2],$linha[3],$linha[4],$linha[5],$linha[6],$linha[7],$linha[8],$linha[9]);
													$valorDotacaoBloqueio[] = $dotacaoArray["dotacao"];
													$valorDotacaoBloqueioItem[$SENQUENCIAL][] = $dotacaoArray["dotacao"];
												}
											}
										}else{
											//Faco a busca pelos campos de Bloqueio
											$sql = " SELECT aitlblnbloq , aitlblanob
															FROM  SFPC.tbitemlicitacaobloqueio WHERE
											clicpoproc = $Processo 
												  		 AND alicpoanop = $ProcessoAno 
												 		 AND cgrempcodi = ".$_SESSION['_cgrempcodi_']." 
												  		 AND ccomlicodi = $ComissaoCodigo 
												 		 AND corglicodi = $OrgaoLicitanteCodigo 
														 AND citelpsequ = ". $SENQUENCIAL;
											
											
											$res  = $db->query($sql);
											if( PEAR::isError($res) ){
												$CodErroEmail  = $res->getCode();
												$DescErroEmail = $res->getMessage();
												ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
											}else{
												$valorDotacaoBloqueio = array();
												$valorDotacaoBloqueioItem = array();
												while ( $linha = $res->fetchRow() ){
													$dotacaoArray = getDadosBloqueioFromChave($dbOracle,$linha[1],$linha[0]);
													$valorDotacaoBloqueio[] = $dotacaoArray["bloqueio"];
													$valorDotacaoBloqueioItem[$SENQUENCIAL][] = $dotacaoArray["bloqueio"];
												}
											}
										}
									
									
									
							?>


							<?PHP
							
								if($LOTE!=$LOTEANTERIOR && $LOTEANTERIOR!=""){
									
									
							?>
								<tr>
									<td >&nbsp;</td>
									<td >&nbsp;TOTAL LOGRADO  LOTE <?php echo $LOTE - 1?> </td>
									<td  colspan="17" class="textonormal" align="left" >&nbsp;<b><?php echo converte_valor_estoques($VALORSOMATORIO);?></b></td>
								</tr>
							<?php
							$VALORSOMATORIO = 0;
							}
							?>				<?php if($LOTE!=$LOTEANTERIOR){?>
							
							<tr>
								<td align="left" bgcolor="#75ADE6" class="titulo3" colspan="19" >Lote <?php echo $LOTE?></td>
							</tr>
							<tr class="linhainfo">
								<td <?php echo $estilotd;?>>ORD</td>
								<td <?php echo $estilotd;?>>DESCRIÇÃO MATERIAL/SERVIÇO</td>
								<td <?php echo $estilotd;?>>TIPO</td>
								<td <?php echo $estilotd;?>>CÓD.RED</td>
								<td <?php echo $estilotd;?>>UNIDADE</td>
								<td <?php echo $estilotd;?>>TRP</td>
								<td <?php echo $estilotd;?>>QUANTIDADE</td>
								<td <?php echo $estilotd;?>>VALOR ESTIMADO</td>
								<td <?php echo $estilotd;?>>VALOR TOTAL</td>
								<td <?php echo $estilotd;?>>VALOR<br>LOGRADO</td>
								<td <?php echo $estilotd;?>>TOTAL<br>LOGRADO</td>
								<?php if(!$RegistroPreco){?>
								<td <?php echo $estilotd;?>>QUANTIDADE<br>EXERCÍCIO</td>
								<td <?php echo $estilotd;?>>VALOR<br>LOGRADO<br>EXERCÍCIO</td>
								<td <?php echo $estilotd;?>>VALOR<br>LOGRADO<br>DEMAIS EXERCÍCIO</td>
								<?php }?>
								<td <?php echo $estilotd;?>>MARCA</td>
								<td <?php echo $estilotd;?>>MODELO</td>
								<td <?php echo $estilotd;?>><?php if($RegistroPreco){ echo "Dotacões";}else{ echo "Bloqueios";}?></td>
								<td <?php echo $estilotd;?>>MOTIVO NÃO LOGRADO</td>
							</tr>
							<?php }?>
							
							<tr>
								<td <?php echo $estiloClasstd;?>>&nbsp;<?php echo $INDICE;?><input name="arrSequencial[<?php echo $ORDEM;?>]" value="<?php echo $SENQUENCIAL;?>"  type="hidden" /> </td>
								<td <?php echo $estiloClasstd;?>>&nbsp;<?php echo $DESCRICAO;?><input name="arrDescricao[<?php echo $ORDEM;?>]" value="<?php echo $DESCRICAO;?>"  type="hidden" /></td>
								<td <?php echo $estiloClasstd;?>>&nbsp;<?php echo $TIPO;?><input name="arrTipo[<?php echo $ORDEM;?>]" value="<?php echo $TIPO;?>"  type="hidden" /></td>
								<td <?php echo $estiloClasstd;?>>&nbsp;<?php echo $CODRED;?><input name="arrCodRed[<?php echo $ORDEM;?>]" value="<?php echo $CODRED;?>"  type="hidden" /></td>
								<td <?php echo $estiloClasstd;?>>&nbsp;<?php echo $UNIDADE;?><input name="arrUnidade[<?php echo $ORDEM;?>]" value="<?php echo $UNIDADE;?>"  type="hidden" /></td>
								<td <?php echo $estiloClasstd;?>>&nbsp;<?php echo converte_valor_estoques($VALORTRP);?><input name="arrValorTrp[<?php echo $ORDEM;?>]" value="<?php echo converte_valor_estoques($VALORTRP);?>"  type="hidden" /></td>
								<td <?php echo $estiloClasstd;?>>&nbsp;<?php echo converte_valor_estoques($QUANTIDADE);?><input id="arrQuantidade[<?php echo $ORDEM;?>]" name="arrQuantidade[<?php echo $ORDEM;?>]" value="<?php echo converte_valor_estoques($QUANTIDADE);?>"  type="hidden" /></td>
								<td <?php echo $estiloClasstd;?>>&nbsp;<?php echo converte_valor_estoques($VALORESTIMADO);?><input id="arrValorEstimado[<?php echo $ORDEM;?>]" name="arrValorEstimado[<?php echo $ORDEM;?>]" value="<?php echo converte_valor_estoques($VALORESTIMADO);?>"  type="hidden" /></td>
								<td <?php echo $estiloClasstd;?>>&nbsp;<?php echo converte_valor_estoques($VALORTOTALESTIMADO);?><input name="arrValorTotalEstimado[<?php echo $ORDEM;?>]" value="<?php echo converte_valor_estoques($VALORTOTALESTIMADO);?>"  type="hidden" /></td>
								<td <?php echo $estiloClasstd;?>>&nbsp;
								<?php if($Botao!="ExibirMapaResumo"){ ?>
								<input value="<?php echo converte_valor_estoques($VALORLOGRADO)?>"  onKeyUp="AtualizarValorTotal('<?php echo $ORDEM;?>');" name="arrValorLogrado[<?php echo $ORDEM;?>]" id="arrValorLogrado[<?php echo $ORDEM;?>]"  type="text" size="16" class="dinheiro4casas" maxlength="16" />
								<?php }else{?>
									<?php echo converte_valor_estoques($VALORLOGRADO)?>
									<input value="<?php echo converte_valor_estoques($VALORLOGRADO)?>"   name="arrValorLogrado[<?php echo $ORDEM;?>]" id="arrValorLogrado[<?php echo $ORDEM;?>]"  type="hidden" size="16" class="dinheiro4casas" maxlength="16" />
								<?php }?>
								</td>
								<td <?php echo $estiloClasstd;?>>&nbsp;<span id="spanTotalLogrado[<?php echo $ORDEM;?>]"><?php echo converte_valor_estoques($TOTALLOGRADO);?></span></td>
								<?php if(!$RegistroPreco){?>
									<td <?php echo $estiloClasstd;?>>&nbsp;
									<?php if(($intQuantidadeItens==1&&$QUANTIDADE==1)||($Botao=="ExibirMapaResumo")){?>
										<input value="<?php echo converte_valor_estoques($QUANTIDADEEXERCICIO)?>"   name="arrQuantidadeExercicio[<?php echo $ORDEM;?>]"   type="hidden" size="16" maxlength="16" />
										<?php echo converte_valor_estoques($QUANTIDADEEXERCICIO); ?>
									<?php }else{?>
										<input class="dinheiro4casas" value="<?php echo converte_valor_estoques($QUANTIDADEEXERCICIO)?>"  onKeyUp="AtualizarValorTotal('<?php echo $ORDEM;?>');" id="arrQuantidadeExercicio[<?php echo $ORDEM;?>]" name="arrQuantidadeExercicio[<?php echo $ORDEM;?>]"   type="text" size="16" maxlength="16" />
									<?php }?>
									</td>
									<td <?php echo $estiloClasstd;?>>&nbsp;
									<?php if($intQuantidadeItens==1&&$QUANTIDADE==1&&$Botao!="ExibirMapaResumo"){?>
										<input value="<?php echo converte_valor_estoques($VALORLOGRADOEXERCICIO)?>"  onKeyUp="AtualizarValorTotal('<?php echo $ORDEM;?>');" name="arrValorLogradoExercicio[<?php echo $ORDEM;?>]" id="arrValorLogradoExercicio[<?php echo $ORDEM;?>]"  type="text" size="16" class="dinheiro4casas" maxlength="16" />
									<?php }else{?>
										
										<span id="spanValorLogradoExercicio[<?php echo $ORDEM;?>]" ><?php echo converte_valor_estoques($VALORLOGRADOEXERCICIO); ?></span>
										<input value="<?php echo converte_valor_estoques($VALORLOGRADOEXERCICIO)?>" name="arrValorLogradoExercicio[<?php echo $ORDEM;?>]"  id="arrValorLogradoExercicio[<?php echo $ORDEM;?>]"  type="hidden" size="16"  maxlength="16" />
									<?php }?>
									</td>
									<td <?php echo $estiloClasstd;?>>&nbsp;
										<span id="spanValorLogradoDemaisExercicios[<?php echo $ORDEM;?>]" > <?php echo converte_valor_estoques($VALORLOGRADODEMAISEXERCICIOS); ?></span>
										<input value="<?php echo converte_valor_estoques($VALORLOGRADODEMAISEXERCICIOS)?>"   id="arrValorLogradoDemaisExercicios[<?php echo $ORDEM;?>]"  type="hidden" size="16" class="dinheiro4casas" maxlength="16" />
									</td>
								<?php }?>
								
								<td <?php echo $estiloClasstd;?>>&nbsp;
									<?php if($Botao!="ExibirMapaResumo"){ ?>
									<input  value="<?php echo $MARCA?>"    name="arrMarca[<?php echo $ORDEM;?>]" id="arrMarca[<?php echo $ORDEM;?>]"  type="text" />
									<?php }else{ ?>
									<?php echo $MARCA?>
									<input  value="<?php echo $MARCA?>"    name="arrMarca[<?php echo $ORDEM;?>]" id="arrMarca[<?php echo $ORDEM;?>]"  type="hidden" />
									<?php }?>
								</td>
								<td <?php echo $estiloClasstd;?>>&nbsp;
									<?php if($Botao!="ExibirMapaResumo"){ ?>
									<input  value="<?php echo $MODELO?>"    name="arrModelo[<?php echo $ORDEM;?>]" id="arrModelo[<?php echo $ORDEM;?>]"  type="text" />
									<?php }else{ 
									echo $MODELO;
									?>
									
									<input  value="<?php echo $MODELO?>"    name="arrModelo[<?php echo $ORDEM;?>]" id="arrModelo[<?php echo $ORDEM;?>]"  type="hidden" />
									<?php }?>
									
								</td>
								<td <?php echo $estiloClasstd;?>>&nbsp;
								<?php 
								//Nao exibir dotacoes duplicadas
								if(isset($valorDotacaoBloqueioItem[$SENQUENCIAL])){
									$arrDotacaoBloqueio = $valorDotacaoBloqueioItem[$SENQUENCIAL];
									$arrDotacaoBloqueio = array_unique($arrDotacaoBloqueio);
									$strDotacaoBloqueio = implode(",<br/>", $arrDotacaoBloqueio);
									echo $strDotacaoBloqueio;
									foreach($arrDotacaoBloqueio as $strDotBloq){
									?>
									<input name="arrDotacaoBloqueio[<?php echo $ORDEM;?>][]" type="hidden" value="<?php echo $strDotBloq; ?>" />
									<?php 
									}
									
								}
								
								?>
								
								</td>
								<td <?php echo $estiloClasstd;?>>&nbsp;
								<?php if($Botao!="ExibirMapaResumo"){ ?>
									<select  name="arrMotivos[<?php echo $ORDEM;?>]">
									<option value="">Selecione..</option>
									<?php foreach($arrMotivosLista as $key => $motivo){?>
										<option <?php if($MOTIVO==$key){echo "selected='selected'";}?> value="<?php echo $key; ?>"><?php echo $motivo; ?></option>
									<?php }?>
									</select>
								<?php }else{?>
									<input type="hidden" value="<?php echo $MOTIVO;?>" name="arrMotivos[<?php echo $ORDEM;?>]" />
									<?php foreach($arrMotivosLista as $key => $motivo){?>
											<?php if($MOTIVO==$key){echo $motivo;}?> 
									<?php }?>
								<?php }?>
								</td>
							</tr>
							
							<?php 
							$LOTEANTERIOR = $LOTE;
							$VALORSOMATORIOGERAL += $VALORESTIMADO * $QUANTIDADE;
							
							$QTD++;
							if($MOTIVO==""){
								$QTDLOGRADOS++;
								$VALORTOTALLOGRADO += $VALORLOGRADO * $QUANTIDADE;
								$VALORTESTIMADO += $VALORESTIMADO * $QUANTIDADE;
								$VALORSOMATORIO += $VALORLOGRADO * $QUANTIDADE;
							}
							
							}
							?>
							<tr>
									<td >&nbsp;</td>
									<td >&nbsp;TOTAL LOGRADO LOTE <?php echo $LOTE?> </td>
									<td colspan="17" class="textonormal" align="left" >&nbsp;<b><?php echo converte_valor_estoques($VALORSOMATORIO);?></b></td>
							</tr>
							<tr>
								<td align="LEFT" bgcolor="#DCEDF7" class="titulo3" colspan="19" >TOTAL LOGRADO GERAL: <?php echo converte_valor_estoques($VALORTOTALLOGRADO);?></td>
							</tr>
							<?php if($Botao=="ExibirMapaResumo"){?>
							<tr>
								<td align="LEFT" bgcolor="#DCEDF7" class="titulo3" colspan="19" >TOTAL GERAL: <?php echo converte_valor_estoques($VALORSOMATORIOGERAL);?></td>
							</tr>
							<tr>
								<td align="LEFT" bgcolor="#DCEDF7" class="titulo3" colspan="19" >TOTAL ESTIMADO (ITENS QUE LOGRARAM ÊXITO): <?php echo converte_valor_estoques($VALORTESTIMADO);?></td>
							</tr>
							
							<tr>
								<td align="LEFT" bgcolor="#DCEDF7" class="titulo3" colspan="19" >TOTAL A SER HOMOLOGADO  (ITENS QUE LOGRARAM  ÊXITO): <?php echo converte_valor_estoques($VALORTOTALLOGRADO);?></td>
							</tr>

							<?php }?>
						</table>
					</td>
				</tr>
				<?php
				}
				?>
							
				<tr>
					<td class="textonormal" align="right" colspan="19">
					<?php if($Botao==""){?>
						<input type="button" name="Selecionar" value="Selecionar" class="botao" onClick="javascript:enviar('Selecionar')">
						<input type="button" name="Voltar" value="Voltar" class="botao" onClick="javascript:enviar('Voltar')">
					<?php }else{?>
						<input type="button" name="Totalizar" value="Totalizar" class="botao" onClick="javascript:enviar('Totalizar')">
						<input type="button" name="ConfirmarResultado" value="Salvar" class="botao" onClick="javascript:enviar('Salvar')">
						<input type="button" name="Voltar" value="Voltar" class="botao" onClick="javascript:enviar('')" />
					<?php }?>
					
					
					</td>
				</tr>
			
			</table>
		</td>
	</tr>
	<!-- Fim do Corpo -->
</table>
</form>
</body>
<?php $db->disconnect(); ?>
</html>

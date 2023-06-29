<?php 
require_once("../funcoes.php");
require_once (CAMINHO_SISTEMA . "app/TemplateAppPadrao.php");

$tpl = new TemplateAppPadrao("templates/CadRenovacaoCadastroIncluir.html","CadRenovacaoCadastroIncluir");

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/fornecedores/ConsAcompFornecedorSenha.php' );

if ($_SESSION["_eusupologi_"] == 'INTERNET') {
	$Origem = 'F';
}
else {
	$Origem = 'U';
}

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] != ""){
	$Sequencial 			= @$_REQUEST['Sequencial'];
	$Botao 					= @$_REQUEST['Botao'];
	$Critica 				= @$_REQUEST['Critica'];
	$Certidao   			= @$_REQUEST['Certidao'];
	$Certidao_Motivo 	    = @$_REQUEST['Certidao_Motivo'];
}


if (trim($Sequencial) == "") {
	header("location: ConsAcompFornecedorSenha.php?Desvio=CadRenovacaoCadastroIncluir");
	exit;
}


if( $Botao == "Voltar" ){
	if ($_SESSION["_eusupologi_"] == 'INTERNET')  {
		header("location: ConsAcompFornecedorSenha.php?Desvio=CadRenovacaoCadastroIncluir");
		exit;
	}
	else {
		header("location: ConsAcompFornecedorSelecionar2.php?Desvio=CadRenovacaoCadastroIncluir");
		exit;
	}
}


# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "CadRenovacaoCadastroIncluir.php";

if( $Critica == 1 ) {
	$Mens     = 0;
	$Mensagem = "Informe: ";

	# Critica dos Campos #
	if( $Certidao == null) {
		$Mens = 1;$Tipo = 2;
		$Mensagem .= "<a href=\"javascript:document.getElementById('Certidao_0').focus();\" class=\"titulo2\">Selecione uma Certidão</a>";
		$Virgula=1;
	}
	foreach ($Certidao as $i => $valor) {
		if ($Certidao[$i] != "" && $Certidao_Motivo[$i] == "") {
			$Mens = 1;$Tipo = 2;
			if ($Virgula==1)  $Mensagem .= ", ";
			$Mensagem .= "<a href=\"javascript:document.getElementById('Certidao_Motivo_".$i."').focus();\" class=\"titulo2\">uma descrição para a certidão</a>";
			$Virgula=1;
		}
	}

	//Se OK
	if( $Mens == 0 ){
		$db = Conexao();
		/*
		 # Verifica a Duplicidade de Comissão
		$sql = "SELECT COUNT(crecefcodi) FROM SFPC.TBCOMISSAOLICITACAO WHERE RTRIM(LTRIM(ECOMLIDESC)) = '$ComissaoDescricao'";
		$result = $db->query($sql);
		if (PEAR::isError($result)){
		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}
		while( $Linha = $result->fetchRow() ){
		$Qtd = $Linha[0];
		}
		if( $Qtd > 0 ) {
		$Mens = 1;$Tipo = 2;
		$Mensagem = "<a href=\"javascript:document.Comissao.ComissaoDescricao.focus();\" class=\"titulo2\">Comissão Já Cadastrada</a>";
		}else{
		*/

		# Recupera a última comissão e incrementa mais um #
		$db->query("BEGIN TRANSACTION");

		# Bloqueio da tabela #
		$sql = "LOCK TABLE sfpc.tbrenovacaocertidoesforn ";
		$result = $db->query($sql);
		if (PEAR::isError($result)) {
			ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}

		foreach ($Certidao as $codCertidao => $valor) {
			# Chave #
			$sql = "SELECT MAX(crecefcodi) FROM sfpc.tbrenovacaocertidoesforn ";
			$result = $db->query($sql);
			if (PEAR::isError($result)) {
				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
			}
			while ($Linha = $result->fetchRow()) {
				$Codigo = $Linha[0] + 1;
			}

			# Insere #
			$usuario_aux =	$_SESSION['_cusupocodi_'];
			$Data = date("Y-m-d H:i:s");
					$sql = "INSERT INTO sfpc.tbrenovacaocertidoesforn (";
							$sql.= "crecefcodi, aforcrsequ, drecefdreg, ctipcecodi, arecefmotc, ";
									$sql.= "freceforig, cusupocod1, crecefulat ";
									$sql.= ") VALUES (";
									$sql.= "$Codigo, $Sequencial, '$Data', $codCertidao, '".$Certidao_Motivo[$codCertidao]."', ";
									$sql.= "'$Origem', $usuario_aux, '$Data')";
									$result = $db->query($sql);
									if (PEAR::isError($result)) {
									$db->query("ROLLBACK");
					EmailErroDB("Erro de banco", "Ocorreu erro em banco", $result);
					$Mens = 1;$Tipo = 2;
					$Mensagem = "Erro ao cadastrar .....";
			}
			if ($Mens == 0) {
			$Mens = 1;$Tipo = 1;
			$Mensagem = ' Atenção! Intenção de Renovação de Certidões Registrada com Sucesso. <br>
					 Seu pedido será analisado e será enviado o resultado por e-mail';
		}
		}
		$db->query("COMMIT");
		$db->query("END TRANSACTION");
		$Certidao="";
		$Certidao_Motivo="";

				
			//}
		$db->disconnect();
	}
}

$sql = "SELECT ctipcecodi, etipcedesc FROM sfpc.tbtipocertidao WHERE ftipceobri = 'S' ORDER BY ctipcecodi ";
$result = $db->query($sql);
if (PEAR::isError($result)) {
	ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
}
$i = 0;
while ($Linha = $result->fetchRow()) {
	$i = $Linha[0];

    $tpl->LINHA            = $i;
    $tpl->DISPLAYCONDITION = $Certidao[$i]==''.$i ? 'block' : 'none';
    $tpl->CERTIDAOMOTIVO   = $Certidao_Motivo[$i];
    $tpl->CHECKEDCONDITION = $Certidao[$i]==''.$i ? "checked" : '';
    $tpl->block("bloco_campos_valores");
}


$tpl->show();
?>
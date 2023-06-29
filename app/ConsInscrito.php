<?php

require_once("../funcoes.php");
include "../fornecedores/funcoesFornecedores.php";
require_once( "../fornecedores/funcoesDocumento.php");

require_once (CAMINHO_SISTEMA . "app/TemplateAppPadrao.php");

$tpl = new TemplateAppPadrao("templates/ConsInscrito.html", "ConsInscrito");

if ($_SERVER['REQUEST_METHOD']	== "GET") {
	$Sequencial     = $_GET['Sequencial'];
	$Irregularidade = $_GET['Irregularidade'];
} else {
	$Botao      = $_POST['Botao'];
	$Sequencial = $_POST['Sequencial'];
	$Mensagem   = $_POST['Mensagem'];
	$codDownload = $_POST['codDownload'];
	$pesqAnoDoc = $_POST['pesqAnoDoc'];
	$Mens       = $_POST['Mens'];
	$Tipo       = $_POST['Tipo'];
}

$tpl->SEQUENCIAL = $Sequencial;

$ErroPrograma = __FILE__;

if ($Botao == "Voltar") {
	if ($_SESSION['_cperficodi_'] == 0) {
		header("location: ConsInscritoSenha.php");
		exit;
	} else {
		header("location: ConsInscritoSelecionar.php");
		exit;
	}
} else if ($Botao == "Imprimir") {
	$Url = "../fornecedores/RelConsInscritoPdf.php?Sequencial=$Sequencial&Mensagem=".urlencode($Mensagem)."&anoAnexacao=".$_POST['pesqAnoDoc'];
	
	if (!in_array($Url,$_SESSION['GetUrl'])) {
		$_SESSION['GetUrl'][] = $Url;
	}
	
	header("location: ".$Url);
	exit;

}elseif ($Botao == 'Download'){
	
	$db = Conexao();
	$sqlDown = "  SELECT doc.cfdocusequ, doc.aprefosequ, doc.aforcrsequ, doc.afdocuanoa, 
			   doc.cfdoctcodi, doc.efdocunome, doc.ifdocuarqu, doc.ffdocuforn, 
			   doc.tfdocuanex, doc.ffdocusitu, doc.cusupocodi, doc.tfdoctulat			   
		   FROM sfpc.tbfornecedordocumento doc
		   WHERE doc.aprefosequ = " . $Sequencial . " AND doc.cfdocusequ = " . $codDownload . " AND ffdocusitu = 'A' order by tfdoctulat DESC limit 1";


   $result = $db->query($sqlDown);
   if (db :: isError($result)) {
	   ExibeErroBD($ErroPrograma . "\nLinha: " . __LINE__ . "\nSql: $sqlDown");
   } else {

		while ($linha = $result->fetchRow()) {
			$arrNome = explode('.',$linha[5]);
			$extensao = $arrNome[1];

			$mimetype = 'application/octet-stream';

			header( 'Content-type: '.$mimetype ); 
			header( 'Content-Disposition: attachment; filename='.$linha[5] );   
			header( 'Content-Transfer-Encoding: binary' );
			header( 'Pragma: no-cache');

			echo pg_unescape_bytea($linha[6]);

			die();
		}


   }

	
}

$db	= Conexao();
if ($Critica == "") {
	$Mens     = 0;
	$Mensagem = "";

	# Pega os Dados do Fornecedor Inscrito #   erro -data contrato ou estatuto da tabela fornecedor credenciado
	$sql  = " SELECT APREFOSEQU, APREFOCCGC, APREFOCCPF, APREFOIDEN, NPREFOORGU, ";
	$sql .= "        NPREFORAZS, NPREFOFANT, CCEPPOCODI, CCELOCCODI, EPREFOLOGR, ";
	$sql .= "        APREFONUME, EPREFOCOMP, EPREFOBAIR, NPREFOCIDA, CPREFOESTA, ";
	$sql .= "        APREFOCDDD, APREFOTELS, APREFONFAX, NPREFOMAIL, APREFOCPFC, ";
	$sql .= "        NPREFOCONT, NPREFOCARG, APREFODDDC, APREFOTELC, APREFOREGJ, ";
	$sql .= "        DPREFOREGJ, APREFOINES, APREFOINME, APREFOINSM, VPREFOCAPS, ";
	$sql .= "        VPREFOCAPI, VPREFOPATL, VPREFOINLC, VPREFOINLG, DPREFOULTB, ";
	$sql .= "        DPREFOCNFC, NPREFOENTP, APREFOENTR, DPREFOVIGE, APREFOENTT, ";
	$sql .= "        DPREFOGERA, TPREFOULAT, ECOMLIDESC, DPREFOANAL, FPREFOMEPP, ";
	$sql .= "        VPREFOINDI, VPREFOINSO, NPREFOMAI2, FPREFOTIPO, DPREFOCONT  ";
	$sql .= "   FROM SFPC.TBPREFORNECEDOR PRE ";
	$sql .= "   LEFT OUTER JOIN SFPC.TBCOMISSAOLICITACAO COM ON PRE.CCOMLICODI = COM.CCOMLICODI ";
	$sql .= "  WHERE APREFOSEQU = $Sequencial ";
	$result = $db->query($sql);

	if (PEAR::isError($result)) {
		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
	} else {
		$Linha = $result->fetchRow();

		# Variáveis Formulário A #
		$Sequencial = $Linha[0];
		$CNPJ = $Linha[1];
		$CPF = $Linha[2];
		$MicroEmpresa = $Linha[44];
		$Identidade = $Linha[3];
		$OrgaoEmissorUF = $Linha[4];
		$RazaoSocial = $Linha[5];
		$NomeFantasia = $Linha[6];
		
		if ($Linha[7] != "") {
			$CEP = $Linha[7];
		} else {
			$CEP = $Linha[8];
		}
		
		$Logradouro = $Linha[9];
		$Numero = $Linha[10];
		$Complemento = $Linha[11];
		$Bairro = $Linha[12];
		$Cidade = $Linha[13];
		$UF = $Linha[14];
		$DDD = $Linha[15];
		$Telefone = $Linha[16];
		$Fax = $Linha[17];
		$Email = $Linha[18];
		$Email2 = $Linha[47];
		
		if ($Linha[19] <> "") {
			$CPFContato = substr($Linha[19],0,3).".".substr($Linha[19],3,3).".".substr($Linha[19],6,3)."-".substr($Linha[19],9,2);
		}

		$NomeContato = $Linha[20];
		$CargoContato = $Linha[21];
		$DDDContato = $Linha[22];
		$TelefoneContato = $Linha[23];
		$RegistroJunta = $Linha[24];
		
		if (!is_null($Linha[25]) or ($Linha[25]!="")) {
			$DataRegistro = substr($Linha[25],8,2)."/".substr($Linha[25],5,2)."/".substr($Linha[25],0,4);
		} else {
			$DataRegistro = "NÃO INFORMADO";
			$RegistroJunta = "NÃO INFORMADO";
		}

		# Variáveis Formulário B #
		$InscEstadual			= $Linha[26];
		$InscMercantil		= $Linha[27];
		$InscOMunic				= $Linha[28];

		# Variáveis Formulário C #
		$CapSocial				= converte_valor($Linha[29]);
		$CapIntegralizado	= converte_valor($Linha[30]);
		$Patrimonio				= converte_valor($Linha[31]);
		$IndLiqCorrente		= converte_valor($Linha[32]);
		$IndLiqGeral			= converte_valor($Linha[33]);
		$IndEndividamento = converte_valor($Linha[45]);
		$IndSolvencia     = converte_valor($Linha[46]);
		
		if ($Linha[34] <> "") {
			$DataUltBalanco		= substr($Linha[34],8,2)."/".substr($Linha[34],5,2)."/".substr($Linha[34],0,4);
		}
		
		if ($Linha[35] <> "") {
			$DataCertidaoNeg	= substr($Linha[35],8,2)."/".substr($Linha[35],5,2)."/".substr($Linha[35],0,4);
		}
		
		if ($Linha[49] <> "") {
			$DataContratoEstatuto	= substr($Linha[49],8,2)."/".substr($Linha[49],5,2)."/".substr($Linha[49],0,4);
		}

		# Variáveis Formulário D #
		$NomeEntidade			= $Linha[36];
		$RegistroEntidade	= $Linha[37];
		
		if ($Linha[38] <> "") {
			$DataVigencia	= substr($Linha[38],8,2)."/".substr($Linha[38],5,2)."/".substr($Linha[38],0,4);
		}
		
		$TecnicoEntidade	= $Linha[39];
		$DataInscricao		= substr($Linha[40],8,2)."/".substr($Linha[40],5,2)."/".substr($Linha[40],0,4);
		$DataAlteracao		= substr($Linha[41],8,2)."/".substr($Linha[41],5,2)."/".substr($Linha[41],0,4);
		$ComissaoResp		  = $Linha[42];
		
		if ($Linha[43] <> "") {
			$DataAnaliseDoc   = substr($Linha[43],8,2)."/".substr($Linha[43],5,2)."/".substr($Linha[43],0,4);
		}
		
		$HabilitacaoTipo = $Linha[48];
	}

	# Pega os Dados da Tabela de Situação #
	$sql    = "SELECT A.CPREFSCODI, A.EPREFOMOTI, B.EPREFSDESC ";
	$sql   .= "  FROM SFPC.TBPREFORNECEDOR A, SFPC.TBPREFORNTIPOSITUACAO B";
	$sql   .= " WHERE A.CPREFSCODI = B.CPREFSCODI AND A.APREFOSEQU = $Sequencial ";
	$result = $db->query($sql);
		
	if (PEAR::isError($result)) {
		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
	} else {
		$situacao     = $result->fetchRow();
		$Situacao     = $situacao[0];
		
		if ($Situacao == 5) {
			$Url = "ConsInscritoExcluido.php?Sequencial=$Sequencial";
			
			if (!in_array($Url,$_SESSION['GetUrl'])) {
				$_SESSION['GetUrl'][] = $Url;
			}
			
			header("location: ".$Url);
			exit;
		}
		
		$Motivo       = $situacao[1];
		$DescSituacao = $situacao[2];
	}

	# Verifica a Validação das Certidões do Fronecedor #
	$sql  = "SELECT A.CTIPCECODI, A.ETIPCEDESC, B.DPREFCVALI ";
	$sql .= "  FROM SFPC.TBTIPOCERTIDAO A, SFPC.TBPREFORNCERTIDAO B ";
	$sql .= " WHERE A.CTIPCECODI = B.CTIPCECODI AND A.FTIPCEOBRI = 'S' ";
	$sql .= "   AND B.APREFOSEQU = $Sequencial";
	$sql .= " ORDER BY 1";
	$result = $db->query($sql);
		
	if (PEAR::isError($result)) {
	    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
	} else {
		$Rows = $result->numRows();
		
		for ($i = 0; $i <= $Rows; $i++) {
			$DataHoje = date("Y-m-d");
			$Linha 	  = $result->fetchRow();
			
			if ($i == 0) {
				if ($Linha[2] < $DataHoje) {
					$Cadastrado = "INABILITADO";
				} else {
					$Cadastrado = "HABILITADO";
				}
			}
		}
	}

	# Busca os Dados da Tabela de Conta Bancária de acordo com o sequencial do Fornecedor #
	$sql    = "SELECT CPRECOBANC, CPRECOAGEN, CPRECOCONT, TPRECOULAT ";
	$sql   .= "  FROM SFPC.TBPREFORNCONTABANCARIA ";
	$sql   .= " WHERE APREFOSEQU = $Sequencial ";
	$sql   .= " ORDER BY TPRECOULAT";
	$result = $db->query($sql);
		
	if (PEAR::isError($result)) {
	    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
	} else {
		$Rows = $result->numRows();
		for ($i = 0; $i < $Rows; $i++) {
			$Linha 	= $result->fetchRow();
			
			if ($i == 0) {
				$Banco1					= $Linha[0];
				$Agencia1				= $Linha[1];
				$ContaCorrente1	= $Linha[2];
			} else {
				$Banco2					= $Linha[0];
				$Agencia2				= $Linha[1];
				$ContaCorrente2	= $Linha[2];
			}
		}
	}

	# Verifica se o Fornecedor está Regular na Prefeitura #
	/*if ($Irregularidade == "") {
		if ($CNPJ != "") {
			$TipoDoc  = 1;
			$CPF_CNPJ = $CNPJ;
		} else if($CPF != "") {
			$TipoDoc  = 2;
			$CPF_CNPJ = $CPF;
		}
		
		$NomePrograma = urlencode("ConsInscrito.php");
		$Url = "fornecedores/RotDebitoCredorConsulta.php?NomePrograma=$NomePrograma&TipoDoc=$TipoDoc&CPF_CNPJ=$CPF_CNPJ&Sequencial=$Sequencial";
		
		if (!in_array($Url,$_SESSION['GetUrl'])) {
			$_SESSION['GetUrl'][] = $Url;
		}
		
		Redireciona($Url);
		exit;
	}*/
}

if ($HabilitacaoTipo == "L") {
	# Mensagem para Fornecedor Inabilitado #
	if ($Irregularidade == "S") {
		$Mens     = 1;
		$Tipo     = 1;
		
		if ($Cadastrado == "INABILITADO") {
			$Mensagem .= "INSCRITO COM CERTIDÃO(ÕES) FORA DO PRAZO DE VALIDADE E COM SITUAÇÃO IRREGULAR NA PREFEITURA";
		} else {
			$Mensagem .= "INSCRITO COM SITUAÇÃO IRREGULAR NA PREFEITURA";
		}
	} else {
		if ($Cadastrado == "INABILITADO") {
			$Mens     = 1;
			$Tipo     = 1;
			$Mensagem .= "INSCRITO COM CERTIDÃO(ÕES) FORA DO PRAZO DE VALIDADE";
		}
		
		# Verifica se a data de balanço expirou, baseado no seguinte: se a data atual for maior que 01/05 do ano corrente só aceitar
		# a data de balanço com um ano a menos do ano atual, caso contrário aceitar com 2 anos a menos do ano atual
	
		if ($CNPJ <> 0) {
			if ((date("Y-m-d") <= date("Y")."04"."30")) {
				$AnoBalanco = date("Y") - 2;
				if (substr($DataUltBalanco,6,4) < $AnoBalanco) {
					if ($Mens == 0) {
						$Mensagem = "FORNECEDOR COM ";
					}
		
					if ($Mens == 1) {
						$Mensagem .=", ";
					}
					
					$Mens      = 1;
					$Tipo      = 1;
					$Virgula   = 1;
					$Mensagem .= " ANO DE VALIDADE DO BALANÇO MENOR QUE $AnoBalanco";
				}
			} else {
				$AnoBalanco = date("Y") - 1;
				
				if (substr($DataUltBalanco,6,4) < $AnoBalanco) {
					if( $Mens == 0 ){
						$Mensagem = "FORNECEDOR COM ";
					}
				
					if ($Mens == 1) {
						$Mensagem.=", ";
					}
					
					$Mens      = 1;
					$Tipo      = 1;
					$Virgula   = 1;
					$Mensagem .= " ANO DE VALIDADE DO BALANÇO MENOR QUE $AnoBalanco";
				}
			}
		}
	}
}

$tpl->MENSAGEM = $Mensagem;
$tpl->VALOR_SEQUENCIAL = $Sequencial;

if ($Mens == 1) {
	$tpl->exibirMensagemFeedback($Mensagem, $Tipo);
}

if ($HabilitacaoTipo == "L") {
	$tpl->block("BLOCO_TIPO_HABILITACAO_L");
}

$tpl->DESCRICAO_SITUACAO = $DescSituacao;

if ($Motivo != "") {
	$tpl->VALOR_MOTIVO = strtoupper2($Motivo);
	$tpl->block("BLOCO_MOTIVO");
}

$tpl->DATA_INSCRICAO = $DataInscricao;
$tpl->DATA_ALTERACAO = $DataAlteracao;
$tpl->COMISSAO_RESPONSAVEL = $ComissaoResp;
$tpl->DATA_ANALISE = $DataAnaliseDoc;

if ($HabilitacaoTipo == "D"){
	$tpl->HABILITACAO_FORNECEDOR = "COMPRA DIRETA";
} else if ($HabilitacaoTipo == "L") {
	$tpl->HABILITACAO_FORNECEDOR = "LICITAÇÃO";
} else if ($HabilitacaoTipo == "E") {
	$tpl->HABILITACAO_FORNECEDOR = "MÓDULO DE ESTOQUES";
}

if ($CNPJ <> 0) {
	$tpl->TIPO_PESSOA = "CNPJ";
	$CNPJCPFForm = substr($CNPJ,0,2).".".substr($CNPJ,2,3).".".substr($CNPJ,5,3)."/".substr($CNPJ,8,4)."-".substr($CNPJ,12,2);
	$tpl->NUMERO_INSCRICAO = $CNPJCPFForm;
	
	$tpl->PORTE_EMPRESA_TITULO = getDescPorteEmpresaTitulo();
	$tpl->PORTE_EMPRESA = getDescPorteEmpresa($MicroEmpresa);
	$tpl->block("BLOCO_PORTE_EMPRESA");
	
	$tpl->LABEL_DESCRICAO = "Razão Social";
} else {
	$tpl->TIPO_PESSOA = "CPF";
	$CNPJCPFForm = substr($CPF,0,3).".".substr($CPF,3,3).".".substr($CPF,6,3)."-".substr($CPF,9,2);
	$tpl->NUMERO_INSCRICAO = $CNPJCPFForm;
	
	$tpl->LABEL_DESCRICAO = "Nome";
}

if ($Identidade <> "") {
	if ($CNPJ <> 0) {
		$tpl->LABEL_IDENTIDADE = 'Identidade do Representante Legal';
	} else {
		$tpl->LABEL_IDENTIDADE = 'Identidade';
	}
	
	$tpl->VALOR_IDENTIDADE = $Identidade;
	$tpl->ORGAO_EMISSOR_UF = $OrgaoEmissorUF;
	$tpl->block("BLOCO_DOCUMENTOS");
}

$tpl->VALOR_DESCRICAO = $RazaoSocial;

if ($NomeFantasia != "") {
	$tpl->NOME_FANTASIA = $NomeFantasia; 
} else { 
	$tpl->NOME_FANTASIA = "NÃO INFORMADO";
}

$tpl->VALOR_CEP = $CEP;
$tpl->VALOR_LOGRADOURO = $Logradouro;

if ($Numero != "") {
	$tpl->VALOR_NUMERO = $Numero; 
} else { 
	$tpl->VALOR_NUMERO = "NÃO INFORMADO";
}

if ($Complemento != "") { 
	$tpl->VALOR_COMPLEMENTO = $Complemento; 
} else { 
	$tpl->VALOR_COMPLEMENTO = "NÃO INFORMADO";
}

$tpl->VALOR_BAIRRO = $Bairro;
$tpl->VALOR_CIDADE = $Cidade;
$tpl->VALOR_UF = $UF;

if ($DDD != "") { 
	$tpl->VALOR_DDD = $DDD; 
} else { 
	$tpl->VALOR_DDD = "NÃO INFORMADO";
}

if ($Telefone != "") { 
	$tpl->VALOR_TELEFONE = $Telefone; 
} else { 
	$tpl->VALOR_TELEFONE = "NÃO INFORMADO";
}

if ($Email != "") {
	$tpl->VALOR_EMAIL_1 = $Email; 
} else { 
	$tpl->VALOR_EMAIL_1 = "NÃO INFORMADO";
}

if ($Email2 != "") { 
	$tpl->VALOR_EMAIL_2 = $Email2; 
} else { 
	$tpl->VALOR_EMAIL_2 = "NÃO INFORMADO";
}

if ($Fax != "") { 
	$tpl->VALOR_FAX = $Fax; 
} else { 
	$tpl->VALOR_FAX = "NÃO INFORMADO";
}

$tpl->VALOR_REGISTRO_JUNTA = $RegistroJunta;
$tpl->VALOR_DATA_REGISTRO = $DataRegistro;

if ($NomeContato != "") {
	$tpl->VALOR_NOME_CONTATO = $NomeContato; 
} else { 
	$tpl->VALOR_NOME_CONTATO = "NÃO INFORMADO";
}

if ($CPFContato != "") { 
	$tpl->VALOR_CPF_CONTATO = $CPFContato; 
} else { 
	$tpl->VALOR_CPF_CONTATO = "NÃO INFORMADO";
}

if ($CargoContato != "") { 
	$tpl->VALOR_CARGO_CONTATO = $CargoContato; 
} else { 
	$tpl->VALOR_CARGO_CONTATO = "NÃO INFORMADO";
}

if ($DDDContato != "") { 
	$tpl->VALOR_DDD_CONTATO = $DDDContato; 
} else { 
	$tpl->VALOR_DDD_CONTATO = "NÃO INFORMADO";
}

if ($TelefoneContato != "") { 
	$tpl->VALOR_TELEFONE_CONTATO = $TelefoneContato; 
} else { 
	$tpl->VALOR_TELEFONE_CONTATO = "NÃO INFORMADO";
}

if ($CNPJ <> 0) {
	$sql  = "SELECT asoprecada, nsoprenome FROM SFPC.TBsocioprefornecedor WHERE aprefosequ = ".$Sequencial."";
	$res = $db->query($sql);

	if (PEAR::isError($res)) {
		EmailErroSQL('Erro ao obter sócios de fornecedor', __FILE__, __LINE__, 'Erro ao obter sócios de fornecedor', $sql, $res);
	} else {
		$Rows = $res->numRows();
		
		if ($Rows == 0) {
            $tpl->block("BLOCO_NENHUM_CADASTRO_SOCIO");
		} else {
			$tpl->block("BLOCO_CADASTRO_SOCIO");
			
			for ($itr = 0; $itr < $Rows; $itr++){
				$Linha = $res->fetchRow();
				$socioCPF = $Linha[0];
				$socioNome = $Linha[1];
				
				$tpl->VALOR_SOCIO_NOME = $socioNome;
				$tpl->VALOR_SOCIO_CPF = $socioCPF;
				$tpl->block("BLOCO_DADOS_SOCIO_CADASTRADO");
			}
		}
	}
}

if ($InscMercantil != "") { 
	$tpl->VALOR_INSCRICAO_MERCANTIL = $InscMercantil; 
} else { 
	$tpl->VALOR_INSCRICAO_MERCANTIL = "-"; 
}

if ($InscOMunic != "") { 
	$tpl->VALOR_INSCRICAO_MUNICIPAL = $InscOMunic; 
} else { 
	$tpl->VALOR_INSCRICAO_MUNICIPAL = "-"; 
}

if ($InscEstadual != "") { 
	$tpl->VALOR_INSCRICAO_ESTADUAL = $InscEstadual; 
} else {
	$tpl->VALOR_INSCRICAO_ESTADUAL = "NÃO INFORMADO";
}

if ($HabilitacaoTipo == "L") {
	$sql = "SELECT CTIPCECODI, ETIPCEDESC FROM SFPC.TBTIPOCERTIDAO WHERE FTIPCEOBRI = 'S' ORDER BY 1";
	$res = $db->query($sql);
	if (PEAR::isError($res)) {
		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
	} else {
		$Rows = $res->numRows();
		
		for ($i = 0; $i < $Rows; $i++) {
			$Linha = $res->fetchRow();
			$DescricaoOb = substr($Linha[1],0,75);
			$CertidaoOb  = $Linha[0];

			# Verifica se existem certidões obrigatórias cadastradas para o Inscrito #
			$sqlData  = "SELECT DPREFCVALI FROM SFPC.TBPREFORNCERTIDAO ";
			$sqlData .= " WHERE APREFOSEQU = $Sequencial AND CTIPCECODI = $CertidaoOb";
			$resData = $db->query($sqlData);
			
			if (PEAR::isError($resData)) {
				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
			} else {
				$LinhaData = $resData->fetchRow();
				
				if ($LinhaData[0] <> 0) {
					$DataCertidaoOb[$ob-1] = substr($LinhaData[0],8,2)."/".substr($LinhaData[0],5,2)."/".substr($LinhaData[0],0,4);
				} else {
					$DataCertidaoOb[$ob-1] = null;
				}
			}
			
			if ($LinhaData[0] < date("Y-m-d")) {
				$Validade = "titulo1";
			} else {
				$Validade = "textonormal";
			}
			
			$tpl->VALOR_VALIDADE = $Validade;
			$tpl->VALOR_DESCRICAO_OB = $DescricaoOb;

			if (is_null($DataCertidaoOb[$ob-1])) {
				$tpl->VALOR_DATA_CERTIDAO_OB = "NÃO INFORMADO";
			} else {
				$tpl->VALOR_DATA_CERTIDAO_OB = $DataCertidaoOb[$ob-1];
			}
		}
	}

	# Verifica se existem certidões complementares cadastradas para o Inscrito #
	$sql  = "SELECT A.DPREFCVALI, B.CTIPCECODI, B.ETIPCEDESC  ";
	$sql .= "  FROM SFPC.TBPREFORNCERTIDAO A, SFPC.TBTIPOCERTIDAO B ";
	$sql .= " WHERE A.APREFOSEQU = $Sequencial AND A.CTIPCECODI = B.CTIPCECODI";
	$sql .= "   AND B.FTIPCEOBRI = 'N' ORDER BY 2";
	$res = $db->query($sql);
	
	if (PEAR::isError($res)) {
		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
	} else {
		$Rows = $res->numRows();

		if ($Rows != 0) {
			# Mostra as certidões complementares cadastradas #
			for ($i = 0; $i < $Rows; $i++ ) {
				$Linha = $res->fetchRow();
				$DescricaoOp					= substr($Linha[2],0,75);
				$CertidaoOpCodigo			= $Linha[1];
				$CertidaoOpcional[$i] = $Linha[1];
				$DataCertidaoOp[$i]		= substr($Linha[0],8,2)."/".substr($Linha[0],5,2)."/".substr($Linha[0],0,4);
				
				if ($Linha[0] < date("Y-m-d")) {
					$Validade = "titulo1";
				} else {
					$Validade = "textonormal";
				}
				
				if ($i == 0) {
					$tpl->block("BLOCO_HEADER_CERTIDOES_COMPLEMENTARES");
				}
				
				$tpl->VALOR_DESCRICAO_OP = $DescricaoOp;
				$tpl->VALOR_DATA_CERTIDAO_OP = $DataCertidaoOp[$i];
				$tpl->block("BLOCO_VALORES_CERTIDOES_COMPLEMENTARES");
			}
		} else {
			$tpl->block("BLOCO_NAO_INFORMADO");
		}
		
		$tpl->block("BLOCO_CERTIDOES_COMPLEMENTARES");
	}
	
	$tpl->block("BLOCO_TIPO_HABILITACAO_L_QUALIFICACAO");
}

if ($CNPJ <> 0) {
	$tpl->VALOR_CAP_SOCIAL = $CapSocial;
	
	if ($CapIntegralizado != "") { 
		$tpl->VALOR_CAP_INTEGRALIZADO = $CapIntegralizado; 
	} else { 
		$tpl->VALOR_CAP_INTEGRALIZADO = "NÃO INFORMADO";
	}
	
	$tpl->VALOR_PATRIMONIO = $Patrimonio;
	
	if ($IndLiqCorrente != "") { 
		$tpl->VALOR_INDICE_LIQUIDEZ = $IndLiqCorrente;
	} else { 
		$tpl->VALOR_INDICE_LIQUIDEZ = "NÃO INFORMADO";
	}
	
	if ($IndLiqGeral != "") { 
		$tpl->VALOR_INDICE_LIQUIDEZ_GERAL = $IndLiqGeral; 
	} else { 
		$tpl->VALOR_INDICE_LIQUIDEZ_GERAL = "NÃO INFORMADO";
	}
	
	if ($IndEndividamento != "") { 
		$tpl->VALOR_INDICE_ENDIVIDAMENTO = $IndEndividamento; 
	} else { 
		$tpl->VALOR_INDICE_ENDIVIDAMENTO = "NÃO INFORMADO";
	}
	
	if ($IndSolvencia != "") { 
		$tpl->VALOR_INDICE_SOLVENCIA = $IndSolvencia; 
	} else { 
		$tpl->VALOR_INDICE_SOLVENCIA = "NÃO INFORMADO";
	}
	
	if ($DataUltBalanco != "") { 
		$tpl->VALOR_DATA_VALIDADE_BALANCO = $DataUltBalanco; 
	} else { 
		$tpl->VALOR_DATA_VALIDADE_BALANCO = "NÃO INFORMADO";
	}
	
	if ($DataCertidaoNeg != "") { 
		$tpl->VALOR_DATA_CERTIDAO_NEGATIVA = $DataCertidaoNeg; 
	} else {
		$tpl->VALOR_DATA_CERTIDAO_NEGATIVA = "NÃO INFORMADO"; 
	}
	
	if ($DataContratoEstatuto != "") { 
		$tpl->VALOR_DATA_ULTIMA_ALTERACAO =  $DataContratoEstatuto; 
	} else { 
		$tpl->VALOR_DATA_ULTIMA_ALTERACAO =  "NÃO INFORMADO"; 
	} 
}

if( $Banco1 != "" ) {
	$tpl->VALOR_BANCO_1 = $Banco1;
	$tpl->VALOR_AGENCIA_1 = $Agencia1;
	$tpl->VALOR_CC_1 = $ContaCorrente1;
	
	$tpl->block("BLOCO_BANCO_1");
}

if ($Banco2 != "") {
	$tpl->VALOR_BANCO_2 = $Banco2;
	$tpl->VALOR_AGENCIA_2 = $Agencia2;
	$tpl->VALOR_CC_2 = $ContaCorrente2;

	$tpl->block("BLOCO_BANCO_2");
}
 
if (($Banco1 == "") and ($Banco2 == "")) {
	$tpl->block("BLOCO_SEM_BANCO");
}

if ($HabilitacaoTipo == "L") {
	if ($NomeEntidade != "") {
		$tpl->VALOR_NOME_ENTIDADE = $NomeEntidade; 
	} else { 
		$tpl->VALOR_NOME_ENTIDADE = "NÃO INFORMADO"; 
	}
	
	if ($RegistroEntidade != "") { 
		$tpl->VALOR_REGISTRO_INSCRICAO = "$RegistroEntidade"; 
	} else { 
		$tpl->VALOR_REGISTRO_INSCRICAO = "NÃO INFORMADO"; 
	}
	
	if ($DataVigencia != "") { 
		$tpl->VALOR_DATA_VIGENCIA = "$DataVigencia"; 
	} else {
		$tpl->VALOR_DATA_VIGENCIA = "NÃO INFORMADO"; 
	}
	
	if ($TecnicoEntidade != "") { 
		$tpl->VALOR_REGISTRO_TECNICO = "$TecnicoEntidade"; 
	} else { 
		$tpl->VALOR_REGISTRO_TECNICO = "NÃO INFORMADO"; 
	}
	
	# Mostra as autorizações específicas do Inscrito cadatradas #
	$sql  = "SELECT APREFANUMA, NPREFANOMA, DPREFAVIGE FROM SFPC.TBPREFORNAUTORIZACAOESPECIFICA ";
	$sql .= " WHERE APREFOSEQU = $Sequencial";
	$res = $db->query($sql);
	
	if (PEAR::isError($res)) {
		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
	} else {
		$Rows = $res->numRows();
		
		if ($Rows <> 0) {
			$tpl->block("BLOCO_HEADER_AUTORIZACAO_ESPECIFICA");
			
			for ($i = 0; $i < $Rows; $i++) {
				$Linha				= $res->fetchRow();
				$RegistroAutor= $Linha[0];
				$NomeAutor		= $Linha[1];
				$DataVigAutor	= substr($Linha[2],8,2)."/".substr($Linha[2],5,2)."/".substr($Linha[2],0,4);
				
				$tpl->VALOR_REGISTRO_AUTOR = ($RegistroAutor != "") ? $RegistroAutor : "NÃO INFORMADO";
				$tpl->VALOR_NOME_AUTOR = ($NomeAutor != "") ? $NomeAutor : "NÃO INFORMADO";
				$tpl->VALOR_DATA_VIG_AUTOR = ($DataVigAutor != "") ? $DataVigAutor : "NÃO INFORMADO";
			}
			
			$tpl->block("BLOCO_RESULTADO_AUTORIZACAO_TECNICA");
		} else {
			$tpl->block("BLOCO_SEM_RESULTADO_AUTORIZACAO_TECNICA");
		}
		
		$tpl->block("BLOCO_AUTORIZACAO_ESPECIFICA");
	}
	
	$tpl->block("BLOCO_TIPO_HABILITACAO_L_TECNICA");
}

//
# Mostra os grupos de materiais já cadastrados do Inscrito #
$sql  = "SELECT A.APREFOSEQU, B.CGRUMSCODI, B.EGRUMSDESC ";
$sql .= "  FROM SFPC.TBGRUPOPREFORNECEDOR A, SFPC.TBGRUPOMATERIALSERVICO B ";
$sql .= " WHERE A.APREFOSEQU = $Sequencial AND A.CGRUMSCODI = B.CGRUMSCODI ";
$sql .= "   AND B.FGRUMSTIPO = 'M' ORDER BY 1,3";
$res = $db->query($sql);

if (PEAR::isError($res)) {
	ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
} else {
	$Rows = $res->numRows();
	
	if ($Rows <> 0) {
		$DescricaoGrupoAntes = "";
		for( $i=0; $i<$Rows;$i++ ){
			$Linha										= $res->fetchRow();
			$DescricaoGrupo   				= substr($Linha[2],0,75);
			$Materiais[$i]= "M#".$Linha[1];
			
			if( $DescricaoGrupoAntes <> $DescricaoGrupo ){
				$tpl->VALOR_DESCRICAO_GRUPO = $DescricaoGrupo;
				$tpl->block("BLOCO_DESCRICAO_ANTES");
			}
			
			$DescricaoGrupoAntes = $DescricaoGrupo;
		}
		
		$tpl->block("BLOCO_MATERIAL");
	}
}

# Mostra os Documentos Anexados do fornecedor
$sql = "SELECT doc.cfdocusequ, doc.aprefosequ, doc.aforcrsequ, doc.afdocuanoa, 
		doc.cfdoctcodi, doc.efdocunome, doc.ifdocuarqu, doc.ffdocuforn, 
		doc.tfdocuanex, doc.ffdocusitu, doc.cusupocodi, doc.tfdoctulat,
		(SELECT h.cfdocscodi
		FROM sfpc.tbfornecedordocumentohistorico h
		where h.cfdocusequ = doc.cfdocusequ order by h.tfdochulat desc limit 1) as situacao, 
		(SELECT h.efdochobse
		FROM sfpc.tbfornecedordocumentohistorico h
		where h.cfdocusequ = doc.cfdocusequ order by h.tfdochulat desc limit 1) as observacao, 

		t.efdoctdesc, 

		(SELECT h.cusupocodi
		FROM sfpc.tbfornecedordocumentohistorico h
		where h.cfdocusequ = doc.cfdocusequ order by h.tfdochulat desc limit 1) as usuarioUltimaAlt, 
		(SELECT u.eusuporesp
		FROM sfpc.tbfornecedordocumentohistorico h
		join sfpc.tbusuarioportal u on h.cusupocodi = u.cusupocodi
		where h.cfdocusequ = doc.cfdocusequ order by h.tfdochulat desc limit 1) as nomeUsuUltimaAlt, 
		
		(SELECT h.tfdochulat
		FROM sfpc.tbfornecedordocumentohistorico h
		where h.cfdocusequ = doc.cfdocusequ order by h.tfdochulat desc limit 1) as datahoraUltimaAlt, 
		u.eusuporesp,

		(SELECT s.efdocsdesc
		FROM sfpc.tbfornecedordocumentohistorico h
		join sfpc.tbfornecedordocumentosituacao s ON s.cfdocscodi = h.cfdocscodi
		where h.cfdocusequ = doc.cfdocusequ order by h.tfdochulat desc limit 1) as situacao_nome
		
		
	FROM sfpc.tbfornecedordocumento doc
	join sfpc.tbfornecedordocumentotipo t ON t.cfdoctcodi = doc.cfdoctcodi
	join sfpc.tbusuarioportal u on doc.cusupocodi = u.cusupocodi
	WHERE aprefosequ = " . $Sequencial . " AND ffdocusitu = 'A' order by tfdoctulat DESC";

$res = $db->query($sql);

if (PEAR::isError($res)) {
	ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
} else {
	$Rows = $res->numRows();
	


	if ($Rows > 0) {

		$htmlDocAnexo .= '
		<thead>
		<tr>
			<th colspan="12">
				<span class="text-center">DOCUMENTOS</span>
			</th>
		</tr>
		</thead>

		<table cellpadding="3" cellspacing="0" border="1" bordercolor="#75ADE6" width="100%" summary="">
		<tr>
			<td bgcolor="#DCEDF7" class="textonormal" colspan="8" align="left" height="20">
			Ano da anexação: <select name="pesqAnoDoc" id="pesqAnoDoc" class="tamanho_campo textonormal" onChange="javascript:enviar(\'PesquisaAnoDoc\');">';

		$arr = array();
		$anos = array();
		$arrDocs = array();


		while ($linha = $res->fetchRow()) {
			$arrDocs[] = $linha;
			$anos[] = $linha[3];
		}

		for($j = date(Y); $j > 2000; $j--){
			if(in_array($j, $anos)){
				$arr[] = $j;
			}
		}
		
		foreach ($arr as $value) {
			if( $value == $pesqAnoDoc ){
				$htmlDocAnexo .= '<option value="'.$value.'" selected>'.$value.'</option>';
			}else{
				$htmlDocAnexo .= '<option value="'.$value.'">'.$value.'</option>';
			}
		}
				
		$htmlDocAnexo .= '</select>
			</td>
		</tr>
		<tr>
		
			<td bgcolor="#DCEDF7" align="center" class="textonormal"> Tipo do documento</td>
			<td bgcolor="#DCEDF7" align="center" class="textonormal"> Nome</td>
			<td bgcolor="#DCEDF7" align="center" class="textonormal"> Responsável anexação</td>
			<td bgcolor="#DCEDF7" align="center" class="textonormal"> Data/Hora Anexação</td>
			<td bgcolor="#DCEDF7" align="center" class="textonormal"> Situação</td>
			<td bgcolor="#DCEDF7" align="center" class="textonormal"> Responsável última alteração</td>
			<td bgcolor="#DCEDF7" align="center" class="textonormal"> Data/Hora última alteração</td>
			<td bgcolor="#DCEDF7" align="center" class="textonormal"> Observação</td>
		</tr> ';

		foreach ($arrDocs as $linha) {
			
			
			if(!$pesqAnoDoc){
				$pesqAnoDoc = date('Y');
			}
			


			if($pesqAnoDoc == $linha[3]){
				
				//verifica se quem cadastrou foi PCR ou o próprio fornecedor
				$nomeUsuAnex = '';
				$nomeUsuUltAlt = '';
				
				if($linha[7] == 'S'){
					$nomeUsuAnex = $CNPJCPFForm;

					//Usuário que fez a última alteração
					if($linha[10]>0){
						$nomeUsuUltAlt = $linha[16];
					}else{
						$nomeUsuUltAlt = $CNPJCPFForm;
					}
				}else{
					$nomeUsuAnex = $linha[18];

					//Usuário que fez a última alteração
					$nomeUsuUltAlt = $linha[16];

				}



				$htmlDocAnexo .= '<tr>
				<td class="textonormal">'.$linha[14].'</td>
				<td class="textonormal"><a href=\'javascript: baixarArquivo('.$linha[0].');\'>'.$linha[5].'</a></td>
				<td class="textonormal" align="center">'.$nomeUsuAnex.'</td>
				<td class="textonormal" align="center">'.formatarDataHora($linha[8]).'</td>
				<td class="textonormal">'.$linha[19].'</td>
				<td class="textonormal" align="center">'.$nomeUsuUltAlt.'</td>
				<td class="textonormal" align="center">'.formatarDataHora($linha[17]).'</td>
				<td class="textonormal">'.$linha[13].'</td>
				</tr>';
				
			}
		}
		$htmlDocAnexo .= '</table>';
	}

	$tpl->htmlDocAnexo = $htmlDocAnexo;	
}



# Mostra os grupos de materiais já cadastrados do Inscrito #
$sql  = "SELECT A.APREFOSEQU, B.CGRUMSCODI, B.EGRUMSDESC ";
$sql .= "  FROM SFPC.TBGRUPOPREFORNECEDOR A, SFPC.TBGRUPOMATERIALSERVICO B  ";
$sql .= " WHERE A.APREFOSEQU = $Sequencial AND A.CGRUMSCODI = B.CGRUMSCODI ";
$sql .= "   AND B.FGRUMSTIPO = 'S' ORDER BY 1,3";
$res = $db->query($sql);

if (PEAR::isError($res)) {
	ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
} else {
	$Rows = $res->numRows();
	
	if ($Rows <> 0) {		
		$DescricaoGrupoAntes = "";
		
		for ($i=0; $i<$Rows;$i++ ) {
			$Linha = $res->fetchRow();
			$DescricaoGrupo   = substr($Linha[2],0,75);
			$Servicos[$i]= "S#".$Linha[1];
			
			if ($DescricaoGrupo <> $DescricaoGrupoAntes) {
				$tpl->VALOR_DESCRICAO_SERVICO_GRUPO = $DescricaoGrupo;
				$tpl->block("BLOCO_DESCRICAO_SERVICO_ANTES");
			}
			
			
			$DescricaoGrupoAntes = $DescricaoGrupo;
		}
		
		$tpl->block("BLOCO_SERVICO");
	}
}

$tpl->show();

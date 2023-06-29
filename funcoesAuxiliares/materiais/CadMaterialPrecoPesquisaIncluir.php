<?php
# ----------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadMaterialPrecoPesquisaIncluir.php
# Objetivo: Incluir registro da pesquisa de mercado
# Autor:    Heraldo Botelho
# Data:     AGO/2012
# ----------------------------------------------------------------------------
# Alterado: Lucas Baracho
# Data:     21/09/2018
# Objetivo: Tarefa Redmine 91454
# ----------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include_once "../funcoes.php";
include_once "../compras/funcoesCompras.php";

# Executa o controle de segurança#
session_start();
Seguranca();

# Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD'] == "POST") {
	$Botao            = $_POST['Botao'];
	$TipoMaterial     = $_POST['TipoMaterial'];
	$NovoPreco		  = $_POST['NovoPreco'];
	$Grupo            = $_POST['Grupo'];
	$DescGrupo        = $_POST['DescGrupo'];
	$Classe           = $_POST['Classe'];
	$DescClasse       = $_POST['DescClasse'];
	$Subclasse        = $_POST['Subclasse'];
	$DescSubclasse    = $_POST['DescSubclasse'];
	$DescUnidade      = $_POST['DescUnidade'];
	$Material         = $_POST['Material'];
	$NCaracteresM     = $_POST['NCaracteresM'];
	$NCaracteresC     = $_POST['NCaracteresC'];
	$NCaracteresO     = $_POST['NCaracteresO'];
	$DescMaterial     = $_POST['DescMaterial'];
	$DescMaterialComp = $_POST['DescMaterialComp'];
	$Observacao       = $_POST['Observacao'];
	$Obs              = strtoupper2(trim($_POST['Obs']));
	$NCaracteresOBS   = $_POST['NCaracteresOBS'];
	$ValorUnitario    = $_POST['ValorUnitario'] ;
	$Marca   		  = trim($_POST['Marca']);
	$Modelo           = trim($_POST['Modelo']);
	$CodFornecedor    = trim($_POST['CodFornecedor']);
	$CNPJFornecedor   = trim($_POST['CNPJFornecedor']);
	$RazaoSocial      = trim($_POST['RazaoSocial']);
	$DDD              = trim($_POST['DDD']);
	$Telefone		  = trim($_POST['Telefone']);
	$DataReferencia	  = trim($_POST['DataReferencia']);
	$Observacao2      = trim($_POST['Observacao2']);
} else {
	$Grupo     = $_GET['Grupo'];
	$Classe    = $_GET['Classe'];
	$Subclasse = $_GET['Subclasse'];
	$Material  = $_GET['Material'];
}
$vetorAux = array(".","/","-"); // Retirar mascara do CNPJ
$cnpjAux  = str_replace($vetorAux,"",$CNPJFornecedor);
$valorAux = str_replace(".","",$ValorUnitario);
$valorAux = str_replace(",",".",$valorAux);

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

if ($Botao == "Validar" ) {
	$db   = Conexao();

	$sql = "SELECT	AFORCRSEQU AS CODFORNECEDOR, AFORCRCDDD AS DDD,AFORCRTELS AS FONE, AFORCRDDDC AS DDDC, AFORCRTELC AS FONEC, NFORCRRAZS AS RAZAOSOCIAL
			FROM	SFPC.TBFORNECEDORCREDENCIADO
			WHERE ";

		if (empty($CNPJFornecedor)) {
	    	$cnpjAux = 0;
		} else {
			$vetor= array(".","/","-");
			$cnpjAux = str_replace($vetor,"",$CNPJFornecedor);
		}
		
	$sql .= "AFORCRCCGC = '$cnpjAux' ";
	
	$result = executarSQL($db, $sql);
	 
	if ($row = $result->fetchRow(DB_FETCHMODE_OBJECT)) {
		$CodFornecedor = $row->codfornecedor;
		$RazaoSocial   = $row->razaosocial;
 		$Telefone      = $row->fone;
 		$DDD           = $row->ddd;
		
		if (empty($Telefone)) {
			$DDD =$row->dddc;
 			$Telefone = $row->fonec;
        }
 	} else {
    	$indFocoEmRazaoSocial = true;
		$CodFornecedor        = "";
		$RazaoSocial          = "";
		$Telefone             = "";
		$DDD                  = "";
		$Mens                 = 1;
		$Tipo                 = 2;
		$Mensagem            .= "<a href=\"javascript:document.CadMaterialPrecoPesquisaIncluir.RazaoSocial.focus();\" class=\"titulo2\">Fornecedor não credenciado informe Razão Social</a>";
    }
	$db->disconnect();

	if (!valida_CNPJ($cnpjAux)) {
		if ($Mens == 1) {
			$Mensagem .= ", ";
		}
		$Mens      = 1;
		$Tipo      = 2;
		$Mensagem .= "<a href=\"javascript:document.CadMaterialPrecoPesquisaIncluir.CNPJFornecedor.focus();\" class=\"titulo2\">CNPJ Inválido</a>";
	}
} elseif ($Botao == "Voltar") {
	header("location: CadMaterialPrecoPesquisaSetarSelecionar1.php");
	exit;
} elseif ($Botao == "Historico") {
	$Url = "CadMaterialPrecoHistorico.php?Material=$Material";
	
	if (!in_array($Url,$_SESSION['GetUrl'])) {
		$_SESSION['GetUrl'][] = $Url;
	}
	header("location: ".$Url);
	exit;
} elseif ($Botao == "Confirmar") {
	$Mens     = 0;
	$Mensagem = "Informe: ";
	
	if ($valorAux == 0) {
		if ($Mens == 1) {
			$Mensagem .= ", ";
		}
		$Mens      = 1;
		$Tipo      = 2;
		$Mensagem .= "<a href=\"javascript:document.CadMaterialPrecoPesquisaIncluir.ValorUnitario.focus();\" class=\"titulo2\">Valor Unitário</a>";
	}

	if (empty($Marca) ) {
	    if ($Mens == 1) {
			$Mensagem .= ", ";
		}
		$Mens      = 1;
		$Tipo      = 2;
		$Mensagem .= "<a href=\"javascript:document.CadMaterialPrecoPesquisaIncluir.Marca.focus();\" class=\"titulo2\">Marca</a>";
	}
	
	if (empty($Modelo) ) {
		if ($Mens == 1) {
			$Mensagem .= ", ";
		}
		$Mens      = 1;
		$Tipo      = 2;
		$Mensagem .= "<a href=\"javascript:document.CadMaterialPrecoPesquisaIncluir.Modelo.focus();\" class=\"titulo2\">Modelo</a>";
	}

	if (empty($cnpjAux) ) {
		if ($Mens == 1) {
			$Mensagem .= ", ";
		}
		$Mens      = 1;
		$Tipo      = 2;
		$Mensagem .= "<a href=\"javascript:document.CadMaterialPrecoPesquisaIncluir.CNPJFornecedor.focus();\" class=\"titulo2\">CNPJ do fornecedor</a>";
	} elseif (!valida_CNPJ($cnpjAux)) {
		if ($Mens == 1) {
			$Mensagem .= ", ";
		}
		$Mens      = 1;
		$Tipo      = 2;
		$Mensagem .= "<a href=\"javascript:document.CadMaterialPrecoPesquisaIncluir.CNPJFornecedor.focus();\" class=\"titulo2\">CNPJ Inválido</a>";
	}

	if (empty($RazaoSocial) ) {
		if ($Mens == 1) {
			$Mensagem .= ", ";
		}
		$Mens      = 1;
		$Tipo      = 2;
		$Mensagem .= "<a href=\"javascript:document.CadMaterialPrecoPesquisaIncluir.RazaoSocial.focus();\" class=\"titulo2\">Razao Social</a>";
	}

	if (empty($DDD)) {
		if ($Mens == 1) {
			$Mensagem .= ", ";
		}
		$Mens      = 1;
		$Tipo      = 2;
		$Mensagem .= "<a href=\"javascript:document.CadMaterialPrecoPesquisaIncluir.DDD.focus();\" class=\"titulo2\">DDD</a>";
	}

	if (empty($Telefone)) {
		if ($Mens == 1) {
			$Mensagem .= ", ";
		}
		$Mens      = 1;
		$Tipo      = 2;
		$Mensagem .= "<a href=\"javascript:document.CadMaterialPrecoPesquisaIncluir.Telefone.focus();\" class=\"titulo2\">Telefone</a>";
	}

	if (empty($DataReferencia)) {
		if ($Mens == 1) {
			$Mensagem .= ", ";
		}
		$Mens      = 1;
		$Tipo      = 2;
		$Mensagem .= "<a href=\"javascript:document.CadMaterialPrecoPesquisaIncluir.DataReferencia.focus();\" class=\"titulo2\">Data Referência</a>";
	} elseif (ValidaData($DataReferencia)) {
		if ($Mens == 1) {
			$Mensagem .= ", ";
		}
		$Mens      = 1;
		$Tipo      = 2;
		$Mensagem .= "<a href=\"javascript:document.CadMaterialPrecoPesquisaIncluir.DataReferencia.focus();\" class=\"titulo2\">Data Referência Inválida</a>";
 	}
    $hoje                    = date("d/m/Y"); 
    $anoAux                  = substr($hoje,6,4);
    $umAnoAntesAux           = substr($hoje,0,2).'/'.substr($hoje,3,2).'/'.($anoAux-3);
    $umAnoAntesAux           = SomaData(1,$umAnoAntesAux);
   	$umAnoAntesInvertido     = DataInvertida($umAnoAntesAux);
   	$DataReferenciaInvertida = DataInvertida($DataReferencia);
	 
	if ($DataReferenciaInvertida<$umAnoAntesInvertido) {
		if ($Mens == 1) {
			$Mensagem .= ", ";
		}
		$Mens      = 1;
		$Tipo      = 2;
		$Mensagem .= "<a href=\"javascript:document.CadMaterialPrecoPesquisaIncluir.DataReferencia.focus();\" class=\"titulo2\">Informe data de referência com prazo igual ou inferior a 36 meses</a>";
 	}
    $hojeInvertido=DataInvertida($hoje);
	 
	if ($DataReferenciaInvertida>$hojeInvertido) {
		if ($Mens == 1) {
			$Mensagem .= ", ";
		}
		$Mens      = 1;
		$Tipo      = 2;
		$Mensagem .= "<a href=\"javascript:document.CadMaterialPrecoPesquisaIncluir.DataReferencia.focus();\" class=\"titulo2\">Informe Data de Referência menor ou igual a data atual</a>";
 	}

 	if (!empty($Observacao2) && strlen($Observacao2) > 300) {
		if ($Mens == 1) {
			$Mensagem .= ", ";
		}
		$Mens      = 1;
		$Tipo      = 2;
		$Mensagem .= "<a href=\"javascript:document.CadMaterialPrecoPesquisaIncluir.Observacao2.focus();\" class=\"titulo2\">Informe Observação com até 300 caracteres</a>";
 	}

 	if ($Mens == 0) {
 		$db = Conexao();
		 
		$sql = "SELECT MAX (CPESQMSEQU) AS ULTIMOSEQ FROM SFPC.TBPESQUISAPRECOMERCADO "; // calcular próximo sequencial
		 
		$result = $db->query($sql);
		
		if (PEAR::isError($result)) {
			ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}

	 	$result = executarTransacao($db, $sql);
		 
		$row = $result->fetchRow(DB_FETCHMODE_OBJECT);
		 
		$proximaChave = $row->ultimoseq+1;

		$DataHora = date("Y-m-d H:i:s");
		$Data     = date("Y-m-d");

		if (empty($Observacao2)) {
			$Observacao2Aux='null';
		} else {
			$Observacao2Aux="'$Observacao2'";
		}

		if (!empty($CodFornecedor)) {
		 	$RazaoSocialAux   = 'null';
		 	$TelefoneAux      = 'null';
		 	$codFornecedorAux = $CodFornecedor;
		 	$cnpjAux          = 'null';
		} else {
			$codFornecedorAux = 'null';
			$RazaoSocialAux   = "'$RazaoSocial'";
			$TelefoneAux      = "'$Telefone'";
		}
 		$sql = "INSERT INTO SFPC.TBPESQUISAPRECOMERCADO (CPESQMSEQU, CMATEPSEQU, CPESQMVALO, CPESQMMARC, CPESQMMODE, CPESQMCNPJ, NPESQMRAZS, NPESQMTELS, EPESQMOBSE, DPESQMREFE, AFORCRSEQU, NPESQMNDDD)
		 		VALUES ($proximaChave, $Material, $valorAux, '$Marca', '$Modelo', $cnpjAux, $RazaoSocialAux, $TelefoneAux,  $Observacao2Aux, to_date('$DataReferencia','DD/MM/YYYY'), $codFornecedorAux, $DDD) ";
		 
		$result = executarTransacao($db, $sql);
		 
		// Inserir na TRP
 		//   Esta CONSTRAINT não permite inserir na TRP
 		//   CONSTRAINT check_trpre01 CHECK (csolcosequ IS NOT NULL AND citescsequ IS NOT NULL AND clicpoproc IS NULL AND alicpoanop IS NULL AND cgrempcodi IS NULL AND ccomlicodi IS NULL AND corglicodi IS NULL AND citelpsequ IS NULL AND cpesqmsequ IS NULL OR csolcosequ IS NULL AND citescsequ IS NULL AND clicpoproc IS NOT NULL AND alicpoanop IS NOT NULL AND cgrempcodi IS NOT NULL AND ccomlicodi IS NOT NULL AND corglicodi IS NOT NULL AND citelpsequ IS NOT NULL AND cpesqmsequ IS NULL OR csolcosequ IS NULL AND citescsequ IS NULL AND clicpoproc IS NULL AND alicpoanop IS NULL AND cgrempcodi IS NULL AND ccomlicodi IS NULL AND corglicodi IS NULL AND citelpsequ IS NULL AND cpesqmsequ IS NOT NULL)

 		//$vetor = array ("null","null","null","null", "null","null",$Material,$valorAux );
        //IncluirNaTRP($vetor,$db);
        IncluirPesquisaNaTRP($proximaChave,$Material,$valorAux, $db  );

 		$result=executarTransacao($db, "commit");
		
		$db->disconnect();

		$Mens      = 1;
		$Tipo      = 1;
		$Mensagem  = "<a href=\"javascript:document.CadMaterialPrecoPesquisaIncluir.ValorUnitario.focus();\" class=\"titulo2\">Pesquisa de mercado incluída com sucesso</a>";

		$ValorUnitario  = "";
		$Marca   	    = "";
		$Modelo         = "";
		$CodFornecedor  = "";
		$CNPJFornecedor = "";
		$RazaoSocial    = "";
		$DDD            = "";
		$Telefone	    = "";
		$DataReferencia = "";
		$Observacao2    = "";
	}
}

if ($Botao == "") {
	# Pega os dados do Pré-Material de acordo com o código #
	$db   = Conexao();
	
	$sql = "SELECT	DISTINCT GRU.FGRUMSTIPM, GRU.EGRUMSDESC, CLA.ECLAMSDESC, SUB.ESUBCLDESC, MAT.EMATEPDESC, MAT.EMATEPCOMP,
					MAT.CUNIDMCODI, UND.EUNIDMDESC, UND.CUNIDMCODI,MAT.EMATEPOBSE,
					(SELECT EPRECMOBSE FROM SFPC.TBPRECOMATERIAL WHERE CMATEPSEQU = $Material AND TPRECMULAT IN (SELECT MAX(TPRECMULAT)
                 	 FROM SFPC.TBPRECOMATERIAL PREMAT WHERE PREMAT.CMATEPSEQU = MAT.CMATEPSEQU)) AS OBSPRECO
            FROM	SFPC.TBMATERIALPORTAL MAT, SFPC.TBGRUPOMATERIALSERVICO GRU, SFPC.TBCLASSEMATERIALSERVICO CLA,
                 	SFPC.TBSUBCLASSEMATERIAL SUB, SFPC.TBUNIDADEDEMEDIDA UND
            WHERE	SUB.CGRUMSCODI = CLA.CGRUMSCODI AND SUB.CCLAMSCODI = CLA.CCLAMSCODI
                 	AND CLA.CGRUMSCODI = GRU.CGRUMSCODI AND MAT.CSUBCLSEQU = SUB.CSUBCLSEQU
                 	AND MAT.CUNIDMCODI = UND.CUNIDMCODI  AND MAT.CMATEPSEQU = $Material  ";
	
	$res  = $db->query($sql);
	
	if (PEAR::isError($res)) {
		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
	} else {
		$Linha            = $res->fetchRow();
		$TipoMaterial     = $Linha[0];
		$DescGrupo        = $Linha[1];
		$DescClasse       = $Linha[2];
		$DescSubclasse    = $Linha[3];
		$DescMaterial     = $Linha[4];
		$NCaracteresM     = strlen($DescMaterial);
		$DescMaterialComp = $Linha[5];
		$NCaracteresC     = strlen($DescMaterialComp);
		$Unidade          = $Linha[6];
		$DescUnidade      = $Linha[7];
		$Unidade          = $Linha[8];
		$Observacao       = $Linha[9];
		$NCaracteresO     = strlen($Observacao);
		$UltimaObs        = $Linha[10];
		$Descricao        = substr($Linha[10],0,100);
	}
	$db->disconnect();
}
?>
<html>
	<?php	# Carrega o layout padrão #
			layout();
	?>
	<script language="javascript" type="">
	<!--
	function enviar(valor){
		document.CadMaterialPrecoPesquisaIncluir.Botao.value=valor;
		document.CadMaterialPrecoPesquisaIncluir.submit();
	}
	
	function ncaracteresobs(valor){
		document.CadMaterialPrecoPesquisaIncluir.NCaracteresOBS.value = '' +  document.CadMaterialPrecoManter.Obs.value.length;
		/* if( navigator.appName == 'Netscape' && valor ){ //Netscape Only
			document.CadMaterialPrecoPesquisaIncluir.Obs.focus();
		}*/
	}

	function validapesquisa(){
  		document.CadMaterialPrecoPesquisaIncluir.Botao.value = 'Validar';
  		document.CadMaterialPrecoPesquisaIncluir.submit();
	}

	function ncaracteres(valor){
		document.CadMaterialPrecoPesquisaIncluir.NCaracteres.value = '' +  document.CadMaterialPrecoPesquisaIncluir.Observacao2.value.length;
		if (navigator.appName == 'Netscape' && valor) { //Netscape Only
			document.CadMaterialPrecoPesquisaIncluir.NCaracteres.focus();
		}
	}
	<?php MenuAcesso(); ?>
	//-->
	</script>
	<link rel="stylesheet" type="text/css" href="../estilo.css">
	<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
		<script src="../menu.js"></script>
		<script language="JavaScript">Init();</script>
		<form action="CadMaterialPrecoPesquisaIncluir.php" method="post" name="CadMaterialPrecoPesquisaIncluir">
			<br><br><br><br><br>
			<table cellpadding="3" border="0" summary="">
				<!-- Caminho -->
				<tr>
					<td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
					<td align="left" class="textonormal" colspan="2">
						<font class="titulo2">|</font>
						<a href="../index.php">
							<font color="#000000">Página Principal</font>
						</a> > Materiais > Preço > Pesquisa de Mercado > Incluir
					</td>
				</tr>
				<!-- Fim do Caminho-->
				<!-- Erro -->
				<?php if ($Mens == 1) { ?>
				<tr>
					<td width="100"></td>
					<td align="left" colspan="2">
						<?php if ($Mens == 1) { ExibeMens($Mensagem,$Tipo,1); } ?>
					</td>
				</tr>
				<?php } ?>
				<!-- Fim do Erro -->
				<!-- Corpo -->
				<tr>
					<td width="100"></td>
					<td class="textonormal">
						<table border="0" cellspacing="0" cellpadding="3" bgcolor="#ffffff" summary="">
							<tr>
								<td class="textonormal">
									<table border="0" cellspacing="0" cellpadding="0" summary="">
										<tr>
											<td class="textonormal">
												<table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" bgcolor="#FFFFFF">
													<tr>
														<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
															MANUTENÇÃO DE PREÇOS DE MATERIAIS
														</td>
													</tr>
													<tr>
														<td class="textonormal">
															<p align="justify">
													 			Para incluir/atualizar um Preço de Material, preencha os dados abaixo e clique no botão "Confirmar".
															</p>
														</td>
													</tr>
													<tr>
														<td>
															<table border="0" cellpadding="0" cellspacing="0" bordercolor="#75ADE6" width="100%" summary="">
																<tr>
																	<td colspan="2">
																		<table class="textonormal" border="0" width="100%" summary="">
																			<tr>
																				<td class="textonormal" bgcolor="#DCEDF7" height="20">Tipo de Material</td>
																				<td class="textonormal">
																					<?php if ($TipoMaterial == "C") { echo "CONSUMO"; } else { echo "PERMANENTE"; } ?>
																				</td>
																			</tr>
																			<tr>
																				<td class="textonormal" bgcolor="#DCEDF7" height="20">Grupo</td>
																				<td class="textonormal"><?php echo $DescGrupo; ?></td>
																			</tr>
																			<tr>
																				<td class="textonormal" bgcolor="#DCEDF7" height="20">Classe</td>
																				<td class="textonormal"><?php echo $DescClasse; ?></td>
																			</tr>
																			<tr>
																				<td class="textonormal" bgcolor="#DCEDF7" height="20">Subclasse</td>
																				<td class="textonormal"><?php echo $DescSubclasse; ?></td>
																			</tr>
																			<tr>
																				<td class="textonormal" bgcolor="#DCEDF7" height="20">Código do Material</td>
																				<td class="textonormal"><?php echo $Material;?></td>
																			</tr>
																			<tr>
																				<td class="textonormal" bgcolor="#DCEDF7" height="20">Material</td>
																				<td class="textonormal"><?php echo $DescMaterial;?></td>
																			</tr>
																			<tr>
																				<td class="textonormal" bgcolor="#DCEDF7" height="20">Unidade de Medida</td>
																				<td class="textonormal"><?php echo $DescUnidade;?></td>
																			</tr>
																			<tr>
																				<td class="textonormal" bgcolor="#DCEDF7" height="20">Descrição Completa</td>
																				<td class="textonormal"><?php echo $DescMaterialComp;?></td>
																			</tr>
																			<tr>
																				<td class="textonormal" bgcolor="#DCEDF7">Valor Unitário*</td>
																				<td>
																					<input  type="text" class="dinheiro4casas" name="ValorUnitario" value="<?php echo $ValorUnitario;?>" size="18" maxlength="18">
																				</td>
																			</tr>
																			<tr>
																				<td class="textonormal" bgcolor="#DCEDF7">Marca*</td>
																				<td>
																					<input type="text" class="textonormal" name="Marca" value="<?php echo "$Marca";?>" size="20" maxlength="150">
																				</td>
																			</tr>
																			<tr>
																				<td class="textonormal" bgcolor="#DCEDF7">Modelo*</td>
																				<td>
																					<input type="text" class="textonormal" name="Modelo" value="<?php echo "$Modelo";?>" size="20" maxlength="150">
																				</td>
																			</tr>
																			<tr>
																				<td class="textonormal" bgcolor="#DCEDF7">CNPJ Fornecedor*</td>
																				<td>
																					<input class="cnpj" type="text"  name="CNPJFornecedor" value="<?php echo $CNPJFornecedor;?>"    >
															 	   	        		<a href="javascript:validapesquisa();"><img src="../midia/lupa.gif" border="0" alt="0"></a>
																				</td>
																			</tr>
																			<tr>
																				<td class="textonormal" bgcolor="#DCEDF7">Razão Social*</td>
																				<td>
																					<input type="text" class="textonormal" name="RazaoSocial" value="<?php echo $RazaoSocial;?>" size="40" maxlength="40"  <?php if (!empty($CodFornecedor)) echo "readonly";   ?>  >
																				</td>
																			</tr>
																			<?php if (!empty( $CodFornecedor)) { ?>
																			<tr>
																				<td class="textonormal" bgcolor="#DCEDF7">DDD</td>
																				<td>
																					<input type="text" class="textonormal" name="DDD" value="<?php echo $DDD;?>" size="5" maxlength="5">
																				</td>
																			</tr>
																			<?php } ?>
																			<tr>
																				<td class="textonormal" bgcolor="#DCEDF7">Telefone*</td>
																				<td>
																					<input type="text" class="textonormal" name="Telefone" value="<?php echo $Telefone;?>" size="33" maxlength="30">
																				</td>
																			</tr>
																			<tr>
																				<td class="textonormal" bgcolor="#DCEDF7">Data de Referência*</td>
																				<td>
																					<input class="data" type="text"  name="DataReferencia" value="<?php echo $DataReferencia;?>" size="10" maxlength="10">  dd/mm/aaaa
																				</td>
																			</tr>
																			<tr>
																				<td class="textonormal" bgcolor="#DCEDF7">Observação</td>
																				<td>máximo de 300 caracteres
																					<input type="text" name="NCaracteres" disabled size="3" value="<?php echo $NCaracteres ?>" class="textonormal"><br>
																					<textarea name="Observacao2" cols="39" rows="5" OnKeyUp="javascript:ncaracteres(1)" OnBlur="javascript:ncaracteres(0)" OnSelect="javascript:ncaracteres(1)" class="textonormal"><?php echo $Observacao2; ?></textarea>
																				</td>
																			</tr>
																		</table>
																	</td>
																</tr>
															</table>
														</td>
													</tr>
													<tr>
														<td colspan="2" align="right">
															<input type="hidden" name="TipoMaterial" value="<?php echo $TipoMaterial; ?>">
															<input type="hidden" name="Grupo" value="<?php echo $Grupo; ?>">
															<input type="hidden" name="DescGrupo" value="<?php echo $DescGrupo; ?>">
															<input type="hidden" name="Classe" value="<?php echo $Classe; ?>">
															<input type="hidden" name="DescClasse" value="<?php echo $DescClasse; ?>">
															<input type="hidden" name="Subclasse" value="<?php echo $Subclasse; ?>">
															<input type="hidden" name="DescSubclasse" value="<?php echo $DescSubclasse; ?>">
															<input type="hidden" name="Material" value="<?php echo $Material; ?>">
															<input type="hidden" name="DescUnidade" value="<?php echo $DescUnidade; ?>">
															<input type="hidden" name="DescMaterial" value="<?php echo $DescMaterial; ?>">
															<input type="hidden" name="UltimaObs" value="<?php echo $UltimaObs; ?>">
															<input type="hidden" name="DescMaterialComp" value="<?php echo $DescMaterialComp; ?>">
															<input type="hidden" name="Observacao" value="<?php echo $Observacao; ?>">
															<input type="hidden" name="CodFornecedor" value="<?php echo $CodFornecedor; ?>">
											    			<input type="button" value="Confirmar" class="botao" onclick="javascript:enviar('Confirmar');">
															<input type="button" value="Voltar" class="botao" onclick="javascript:enviar('Voltar');">
															<input type="hidden" name="Botao" value="">
														</td>
													</tr>
												</table>
											</td>
										</tr>
									</table>
								</td>
							</tr>
						</table>
					</td>
				</tr>
				<!-- Fim do Corpo -->
			</table>
		</form>
	</body>
</html>
<script language="JavaScript"></script>
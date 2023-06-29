<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: ConsInscrito.php
# Autor:    Roberta Costa
# Data:     09/09/04
# Objetivo: Programa que Exibe os dados do Fornecedor Inscrito
# Alterado: Rossana Lira
# Data:     14/05/2007 - O botão voltar só será disponibilizado na intranet
#                      - Exibir data de última alteração
#           25/05/2007 - Exibir informações da comissão e data da análise
# Data:     29/05/2007 - Alteração para chamar ConsInscritoExcluido sem parâmetro
#                        de nome de programa
# Alterado: Carlos Abreu
# Data:     18/06/2007 - Receber novo campo (índice de solvência)
# Alterado: Ariston Cordeiro
# Data:     09/06/2008 - Novo campo Email 2
# Alterado: Ariston
# Data:     09/08/2010	- Adicionado opção para incluir sócios
# Autor:    Everton Lino
# Data:     26/08/2010
# Alterado: Rodrigo Melo
# Data:     06/06/2011 - Tarefa Redmine: 2203 - Fornecedores devem estar associados a grupos de materiais
# Alterado: Rodrigo Melo
# Data:     06/06/2011 - Tarefa Redmine: 2727 - Incluir novos campos para preenchimento no SICREF - Dados do Representante Legal
# Alterado: Rodrigo Melo
# Data:     16/09/2011 - Tarefa Redmine: 3718 - Remoção de campos de Representante Legal no módulo de fornecedores (SICREF)
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------
# Alterado: Ernesto Ferreira
# Data:		01/11/2018
# Objetivo: Tarefa Redmine 201709
# -----------------------------------------------------------------------------------------------------------------------------------------------


# Acesso ao arquivo de funções #
include "../funcoes.php";
include "funcoesFornecedores.php";
require_once( "funcoesDocumento.php");

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso('/fornecedores/ConsInscritoSenha.php');
AddMenuAcesso('/fornecedores/ConsInscritoSelecionar.php');
AddMenuAcesso('/fornecedores/RelConsInscritoPdf.php');
AddMenuAcesso('/fornecedores/ConsInscritoExcluido.php');
AddMenuAcesso('/oracle/fornecedores/RotDebitoCredorConsulta.php');

# Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD']	== "GET") {
	$Sequencial     = $_GET['Sequencial'];
	$Irregularidade = $_GET['Irregularidade'];
	$Retorno        = $_GET['Retorno'];
	$docSituacao 	= $_GET['docSituacao'];
	$docInicio 	    = $_GET['docInicio'];
	$docFim 	    = $_GET['docFim'];
} else {
	$Botao      = $_POST['Botao'];
	$Sequencial = $_POST['Sequencial'];
	$Mensagem   = $_POST['Mensagem'];
	$codDownload = $_POST['codDownload'];
	$pesqAnoDoc = $_POST['pesqAnoDoc'];
	$Mens       = $_POST['Mens'];
	$Tipo       = $_POST['Tipo'];
	$Retorno     = $_POST['Retorno'];
	$docSituacao 	= $_POST['docSituacao'];
	$docInicio 	= $_POST['docInicio'];
	$docFim 	= $_POST['docFim'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

# Redireciona o programa de acordo com o botão voltar #
if( $Botao == "Voltar" ){
		if( $_SESSION['_cperficodi_'] == 0 ){
			  header("location: ConsInscritoSenha.php");
			  exit;
		} elseif ($Retorno == 'ConsDocsFornecedor') {


			header('Location: ConsDocsFornecedor.php?Botao=Pesquisar&docSituacao='.$docSituacao.'&docInicio='.$docInicio.'&docFim='.$docFim);
			exit;


		} elseif ($Retorno == 'CadAvaliacaoInscritoManter') {


			header('Location: CadAvaliacaoInscritoManter.php?ProgramaSelecao=CadAvaliacaoInscritoSelecionar.php&Sequencial='.$Sequencial);
			exit;

		}else{
				header("location: ConsInscritoSelecionar.php");
				exit;
		}
}elseif( $Botao == "Imprimir" ){
		$Url = "RelConsInscritoPdf.php?Sequencial=$Sequencial&Mensagem=".urlencode($Mensagem)."&anoAnexacao=".$_POST['pesqAnoDoc'];
		if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
		header("location: ".$Url);
		exit;

}elseif ($Botao == 'Download'){
	
	$db = Conexao();
	$sqlDown = "  SELECT doc.cfdocusequ, doc.aprefosequ, doc.aforcrsequ, doc.afdocuanoa, 
			   doc.cfdoctcodi, doc.efdocunome, doc.ifdocuarqu, doc.ffdocuforn, 
			   doc.tfdocuanex, doc.ffdocusitu, doc.cusupocodi, doc.tfdoctulat			   
		   FROM sfpc.tbfornecedordocumento doc
		   INNER JOIN SFPC.TBFORNECEDORDOCUMENTOTIPO T ON doc.cfdoctcodi = t.cfdoctcodi
		   WHERE doc.aprefosequ = " . $Sequencial . " AND doc.cfdocusequ = " . $codDownload . " AND ffdocusitu = 'A' order by t.afdoctorde asc, doc.tfdoctulat DESC limit 1";


   $result = $db->query($sqlDown);
   if (PEAR::isError($result)) {
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
			$CPFContato = substr($Linha[19],0,3).".".substr($Linha[19],3,3).".".substr($Linha[19],6,3)."-".substr($Linha[18],9,2);
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
		$InscEstadual  = $Linha[26];
		$InscMercantil = $Linha[27];
		$InscOMunic	   = $Linha[28];

		# Variáveis Formulário C #
		$CapSocial		  = converte_valor($Linha[29]);
		$CapIntegralizado = converte_valor($Linha[30]);
		$Patrimonio		  = converte_valor($Linha[31]);
		$IndLiqCorrente	  = converte_valor($Linha[32]);
		$IndLiqGeral	  = converte_valor($Linha[33]);
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
		$NomeEntidade	  = $Linha[36];
		$RegistroEntidade = $Linha[37];
		
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

		for ($i=0; $i<=$Rows;$i++) {
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

		for ($i=0; $i < $Rows;$i++) {
			$Linha 	= $result->fetchRow();

			if ($i == 0) {
				$Banco1			= $Linha[0];
				$Agencia1		= $Linha[1];
				$ContaCorrente1	= $Linha[2];
			} else {
				$Banco2			= $Linha[0];
				$Agencia2		= $Linha[1];
				$ContaCorrente2	= $Linha[2];
			}
		}
	}
	
	# Verifica se o Fornecedor está Regular na Prefeitura #
	if ($Irregularidade == "") {
		if ($CNPJ != "") {
			$TipoDoc  = 1;
			$CPF_CNPJ = $CNPJ;
		} elseif ($CPF != "") {
			$TipoDoc  = 2;
			$CPF_CNPJ = $CPF;
		}

		$NomePrograma = urlencode("ConsInscrito.php");
		$infoExtra = '&Retorno='.$Retorno.'&docSituacao='.$docSituacao.'&docInicio='.$docInicio.'&docFim='.$docFim;
		$Url = "fornecedores/RotDebitoCredorConsulta.php?NomePrograma=$NomePrograma&TipoDoc=$TipoDoc&CPF_CNPJ=$CPF_CNPJ&Sequencial=$Sequencial".$infoExtra;

		if (!in_array($Url,$_SESSION['GetUrl'])) {
			$_SESSION['GetUrl'][] = $Url;
		}
		// Redireciona($Url);
		// exit;
	}
}

if($HabilitacaoTipo == "L"){
	# Mensagem para Fornecedor Inabilitado #
	if( $Irregularidade == "S" ){
			$Mens     = 1;
			$Tipo     = 1;
			if( $Cadastrado == "INABILITADO" ){
					$Mensagem .= "INSCRITO COM CERTIDÃO(ÕES) FORA DO PRAZO DE VALIDADE E COM SITUAÇÃO IRREGULAR NA PREFEITURA";
			}else{
					$Mensagem .= "INSCRITO COM SITUAÇÃO IRREGULAR NA PREFEITURA";
			}
	}else{
			if( $Cadastrado == "INABILITADO" ){
					$Mens     = 1;
					$Tipo     = 1;
					$Mensagem .= "INSCRITO COM CERTIDÃO(ÕES) FORA DO PRAZO DE VALIDADE";
			}
			# Verifica se a data de balanço expirou, baseado no seguinte: se a data atual for maior que 01/05 do ano corrente só aceitar
			# a data de balanço com um ano a menos do ano atual, caso contrário aceitar com 2 anos a menos do ano atual

			if ( $CNPJ <> 0 ) {
					if (	(date("Y-m-d") <= date("Y")."04"."30") ) {
							$AnoBalanco = date("Y") - 2;
							if  (substr($DataUltBalanco,6,4) < $AnoBalanco) {
									if( $Mens == 0 ){
											$Mensagem = "FORNECEDOR COM ";
									}
									if( $Mens == 1 ){ $Mensagem .=", "; }
									$Mens      = 1;
									$Tipo      = 1;
									$Virgula   = 1;
									$Mensagem .= " ANO DE VALIDADE DO BALANÇO MENOR QUE $AnoBalanco";
							}
					}	else {
							$AnoBalanco = date("Y") - 1;
							if  (substr($DataUltBalanco,6,4) < $AnoBalanco) {
									if( $Mens == 0 ){
											$Mensagem = "FORNECEDOR COM ";
									}
									if( $Mens == 1 ){ $Mensagem.=", "; }
									$Mens      = 1;
									$Tipo      = 1;
									$Virgula   = 1;
									$Mensagem .= " ANO DE VALIDADE DO BALANÇO MENOR QUE $AnoBalanco";
							}
					}
			}
	}
}
?>
<html>
<?php
# Carrega o layout padrão #
layout();
?>
<script language="javascript" type="">


<!--
function enviar(valor){
	document.ConsInscrito.Botao.value = valor;
	document.ConsInscrito.submit();
}
function baixarArquivo(cod){
				document.ConsInscrito.codDownload.value = cod;
				enviar('Download');
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="Stylesheet" type="Text/Css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="ConsInscrito.php" method="post" name="ConsInscrito">
<br><br><br><br><br>
<table cellpadding="3" border="0" width="100%" summary="">
	<!-- Caminho -->
	<tr>
		<td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
		<td align="left" class="textonormal" colspan="2">
			<font class="titulo2">|</font><a href="../index.php"><font color="#000000">Página Principal</font></a> > Fornecedores > Inscrição > Consulta
		</td>
	</tr>
	<!-- Fim do Caminho-->

	<!-- Erro -->
	<tr>
	  <td width="100"></td>
	  <td align="left" colspan="2">
			<?php if( $Mens <> 0 ){ ExibeMens($Mensagem,$Tipo,$Virgula);	}?>
	 	</td>
	</tr>
	<!-- Fim do Erro -->

	<!-- Corpo -->
	<tr>
		<td width="100"></td>
		<td class="textonormal">
			<table  border="0" cellspacing="0" cellpadding="3" width="100%" summary="">
				<tr>
	      	<td class="textonormal">
	        	<table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" class="textonormal" bgcolor="#FFFFFF" width="100%" summary="">
	          	<tr>
	            	<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
		    					CONSULTA DE FORNECEDORES INSCRITOS
		          	</td>
		        	</tr>
	  	      	<tr>
	    	      	<td class="textonormal" >
									<p align="justify">
										Para imprimir os dados cadastrais abaixo clique no botão "Imprimir".<br>
	          	   	</p>
	          		</td>
		        	</tr>
        	    <tr>
								<td>
									<table cellpadding="0" cellspacing="0" border="0" width="100%" summary="">
										<tr>
											<td>
						  					<table class="textonormal" border="0" cellpadding="0" cellspacing="2" width="100%" summary="">
													<tr>
														<td class="textonormal" bgcolor="#DCEDF7" height="20">Código da Inscrição</td>
														<td class="textonormal"><?php echo $Sequencial; ?></td>
									  			</tr>
													<?php
													if($TipoHabilitacao == "L"){
													?>
														<tr>
															<td class="textonormal" bgcolor="#DCEDF7" height="20">Cumprimento Inc. XXXIII Art. 7º Cons. Fed.</td>
															<td class="textonormal">SIM</td>
														</tr>
													<?php
													}
													?>
													<tr>
														<td class="textonormal" bgcolor="#DCEDF7" height="20">Situação</td>
														<td class="textonormal"><?php echo $DescSituacao; ?></td>
									  			</tr>
													<?php if( $Motivo != "" ){ ?>
													<tr>
														<td class="textonormal" bgcolor="#DCEDF7" height="20">Motivo</td>
														<td class="textonormal"><?php echo strtoupper2($Motivo); ?></td>
									  			</tr>
									  			<?php } ?>
									  			<tr>
														<td class="textonormal" bgcolor="#DCEDF7" height="20" width="45%">Data de Cadastramento</td>
														<td class="textonormal"><?php echo $DataInscricao;?></td>
									  			</tr>
									  			<tr>
														<td class="textonormal" bgcolor="#DCEDF7" height="20" width="45%">Data da Última Alteração</td>
														<td class="textonormal"><?php echo $DataAlteracao;?></td>
									  			</tr>
									  			<tr>
														<td class="textonormal" bgcolor="#DCEDF7" height="20" width="45%">Comissão Responsável Análise Documentação</td>
														<td class="textonormal"><?php echo $ComissaoResp;?></td>
									  			</tr>
									  			<tr>
														<td class="textonormal" bgcolor="#DCEDF7" height="20" width="45%">Data da Análise </td>
														<td class="textonormal"><?php echo $DataAnaliseDoc;?></td>
									  			</tr>
									  			<tr>
														<td class="textonormal" bgcolor="#DCEDF7" height="20" width="45%">Habilitação do fornecedor</td>
														<td class="textonormal">
														<?php
															if ($HabilitacaoTipo == "D"){
																echo "COMPRA DIRETA";
															}else if ($HabilitacaoTipo == "L"){
																echo "LICITAÇÃO";
															}else if ($HabilitacaoTipo == "E"){
																echo "MÓDULO DE ESTOQUES";
															}
														?>
														</td>
									  			</tr>
												</table>
						  				</td>
						  			</tr>
									</table>
								</td>
		        	</tr>
							<tr>
								<td>
									<table cellpadding="0" cellspacing="2" border="0" bordercolor="#75ADE6" width="100%" summary="">
			          		<!-- HABLITAÇÃO JURÍDICA -->
			          		<tr>
	              			<td bgcolor="#75ADE6" colspan="2" class="textoabasoff" align="center" height="20">HABLITAÇÃO JURÍDICA</td>
	              		</tr>
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7">
												<?php if( $CNPJ <> 0 ){ echo "CNPJ\n"; } else { echo "CPF\n"; }?>
          	    			</td>
											<td class="textonormal" height="20">
		          	    		<?php
												if( $CNPJ <> 0 ){
    												$CNPJCPFForm	= substr($CNPJ,0,2).".".substr($CNPJ,2,3).".".substr($CNPJ,5,3)."/".substr($CNPJ,8,4)."-".substr($CNPJ,12,2);
			          	    			echo $CNPJCPFForm;
  											}else{
	    											$CNPJCPFForm  = substr($CPF,0,3).".".substr($CPF,3,3).".".substr($CPF,6,3)."-".substr($CPF,9,2);
			          	    			echo $CNPJCPFForm;
    										}
												?>
	          	    		</td>
	            			</tr>
										<?php if( $CNPJ <> 0 ){ ?>
	            				</tr>
												<td class="textonormal" bgcolor="#DCEDF7"><?php echo getDescPorteEmpresaTitulo(); ?></td>
												<td class="textonormal" height="20"><?php echo getDescPorteEmpresa($MicroEmpresa) ?></td>
												
	            				</tr>
										<?php	} ?>
										<?php if( $Identidade <> "" ){ ?>
											<tr>
											<?php if( $CNPJ <> 0 ){ ?>
															<td class="textonormal" bgcolor="#DCEDF7">Identidade do Representante Legal</td>
											<?php	} else { ?>
															<td class="textonormal" bgcolor="#DCEDF7">Identidade</td>
											<?php	} ?>
											<td class="textonormal" height="20"><?php echo $Identidade;?>	</td>
	            				</tr>
											<tr>
												<td class="textonormal" bgcolor="#DCEDF7">Órgao Emissor/UF</td>
												<td class="textonormal" height="20"><?php echo $OrgaoEmissorUF; ?></td>
	            				</tr>
	          	    	<?php }?>

				            <tr>
												<td class="textonormal" bgcolor="#DCEDF7" height="20">
					              	<?php if( $CNPJ <> 0 ){ echo "Razão Social\n"; }else{ echo "Nome\n"; } ?>
					              </td>
				              <td class="textonormal" height="20"><?php echo $RazaoSocial;?></td>
				            </tr>
				            <tr>
				              <td class="textonormal" bgcolor="#DCEDF7">Nome Fantasia</td>
				              <td class="textonormal" height="20">
				              	<?php if( $NomeFantasia != "" ){ echo $NomeFantasia; } else { echo "NÃO INFORMADO";} ?>
				              </td>
				            </tr>
				            <tr>
				              <td class="textonormal" bgcolor="#DCEDF7">CEP</td>
											<td class="textonormal" height="20"><?php echo $CEP; ?></td>
				            </tr>
										<tr>
				              <td class="textonormal" bgcolor="#DCEDF7">Logradouro</td>
				              <td class="textonormal" height="20"><?php echo $Logradouro; ?></td>
				            </tr>
										<tr>
				              <td class="textonormal" bgcolor="#DCEDF7">Número</td>
				              <td class="textonormal" height="20">
				              	<?php if( $Numero != "" ){ echo $Numero; } else { echo "NÃO INFORMADO";} ?>
				              </td>
				            </tr>
										<tr>
				              <td class="textonormal" bgcolor="#DCEDF7">Complemento</td>
				              <td class="textonormal" height="20">
				              	<?php if( $Complemento != "" ){ echo $Complemento; } else { echo "NÃO INFORMADO";} ?>
				              </td>
				            </tr>
										<tr>
				              <td class="textonormal" bgcolor="#DCEDF7">Bairro</td>
				              <td class="textonormal" height="20"><?php echo $Bairro; ?></td>
				            </tr>
										<tr>
				              <td class="textonormal" bgcolor="#DCEDF7">Cidade</td>
				              <td class="textonormal" height="20"><?php echo $Cidade; ?></td>
				            </tr>
				            <tr>
				              <td class="textonormal" bgcolor="#DCEDF7">UF</td>
		    	      			<td class="textonormal" height="20"><?php echo $UF; ?></td>
				            </tr>
				            <tr>
				              <td class="textonormal" bgcolor="#DCEDF7">DDD</td>
				              <td class="textonormal" height="20">
				              	<?php if( $DDD != "" ){ echo $DDD; } else { echo "NÃO INFORMADO";} ?>
				              </td>
				            </tr>
				            <tr>
				              <td class="textonormal" bgcolor="#DCEDF7">Telefone(s)</td>
											<td class="textonormal" height="20">
												<?php if( $Telefone != "" ){ echo $Telefone; } else { echo "NÃO INFORMADO";} ?>
											</td>
				            </tr>
				            <tr>
				              <td class="textonormal" bgcolor="#DCEDF7">E-mail 1</td>
						<td class="textonormal" height="20">
							<?php if( $Email != "" ){ echo $Email; } else { echo "NÃO INFORMADO";} ?>
						</td>
				            </tr>
				            <tr>
				              <td class="textonormal" bgcolor="#DCEDF7">E-mail 2</td>
						<td class="textonormal" height="20">
							<?php if( $Email2 != "" ){ echo $Email2; } else { echo "NÃO INFORMADO";} ?>
						</td>
				            </tr>
				            <tr>
				              <td class="textonormal" bgcolor="#DCEDF7">Fax</td>
											<td class="textonormal" height="20">
												<?php if( $Fax != "" ){ echo $Fax; } else { echo "NÃO INFORMADO";} ?>
											</td>
				            </tr>
										<tr>
				              <td class="textonormal" bgcolor="#DCEDF7">Registro Junta Comercial ou Cartório</td>
											<td class="textonormal" height="20"><?php echo $RegistroJunta;?></td>
				            </tr>
				            <tr>
				              <td class="textonormal" bgcolor="#DCEDF7" width="45%">Data Reg. Junta Comercial ou Cartório</td>
				              <td class="textonormal" height="20"><?php echo $DataRegistro;?></td>
				            </tr>
										<tr>
				              <td class="textonormal" bgcolor="#DCEDF7">Nome do Contato</td>
											<td class="textonormal" height="20">
												<?php if( $NomeContato != "" ){ echo $NomeContato; } else { echo "NÃO INFORMADO";} ?>
											</td>
				            </tr>
										<tr>
				              <td class="textonormal" bgcolor="#DCEDF7">CPF do Contato</td>
											<td class="textonormal" height="20">

												<?php if( $CPFContato != "" ){ echo $CPFContato; } else { echo "NÃO INFORMADO";} ?>
											</td>
				            </tr>
										<tr>
				              <td class="textonormal" bgcolor="#DCEDF7">Cargo do Contato</td>
											<td class="textonormal" height="20">
												<?php if( $CargoContato != "" ){ echo $CargoContato; } else { echo "NÃO INFORMADO";} ?>
											</td>
				            </tr>
										<tr>
				              <td class="textonormal" bgcolor="#DCEDF7">DDD do Contato</td>
											<td class="textonormal" height="20">
												<?php if( $DDDContato != "" ){ echo $DDDContato; } else { echo "NÃO INFORMADO";} ?>
											</td>
				            </tr>
				            <tr>
				              <td class="textonormal" bgcolor="#DCEDF7">Telefone do Contato</td>
											<td class="textonormal" height="20">
												<?php if( $TelefoneContato != "" ){ echo $TelefoneContato; } else { echo "NÃO INFORMADO";} ?>
											</td>
				            </tr>
									</table>
								</td>
							</tr>

							<tr>
								<td>
									<table cellpadding="0" cellspacing="2" border="0" bordercolor="#75ADE6" width="100%" summary="">
										<!-- SÓCIOS -->
			          		<tr>
	              			<td bgcolor="#75ADE6" colspan="2" class="textoabasoff" align="center" height="20">SÓCIOS</td>
	              		</tr>
	              		<tr>
	              			<td>

					            	<table align="center" border="1" cellpadding="3" cellspacing="0" bgcolor="#DCEDF7" bordercolor="#75ADE6" class="textonormal" width="100%" summary="">
					              	<?php
					              	if( $CNPJ <> 0 ){
														# Pega os Dados dos sócios #
														$sql  = "
															SELECT
																asoprecada, nsoprenome
															FROM SFPC.TBsocioprefornecedor
															WHERE aprefosequ = ".$Sequencial."
														";
													  $res = $db->query($sql);

														if( PEAR::isError($res) ){
															EmailErroSQL('Erro ao obter sócios de fornecedor', __FILE__, __LINE__, 'Erro ao obter sócios de fornecedor', $sql, $res);
														}	else{
															$Rows = $res->numRows();
															if($Rows==0){
					              	?>
								              	<tr>
								              		<td align="center" class="textonormal" bgcolor="#FFFFFF" colspan="2">NENHUM CADASTRADO</td>
								              	</tr>
					              	<?php
															}else {
					              	?>
								              	<tr>
								              		<td class="textonormal" bgcolor="#DCEDF7" align="center">Nome</td>
								              		<td class="textonormal" bgcolor="#DCEDF7" align="center" width="150px">CPF/CNPJ</td>
								              	</tr>
					              	<?php

																for ($itr=0; $itr<$Rows; $itr++){
																$Linha = $res->fetchRow();
																$socioCPF = $Linha[0];
																$socioNome = $Linha[1];
					              	?>
								              	<tr>
								              		<td class="textonormal" bgcolor="#FFFFFF"><?php echo $socioNome;?></td>
								              		<td class="textonormal" bgcolor="#FFFFFF"><?php echo $socioCPF;?></td>
								              	</tr>
					              	<?php
																}
															}
														}
					              	}
					              	?>
					              </table>

	              			</td>
	              		</tr>
									</table>
								</td>
							</tr>

							<tr>
								<td>
									<table cellpadding="0" cellspacing="2" border="0" bordercolor="#75ADE6" width="100%" summary="">
										<!-- REGULARIDADE FISCAL -->
			          		<tr>
	              			<td bgcolor="#75ADE6" colspan="2" class="textoabasoff" align="center" height="20">REGULARIDADE FISCAL</td>
	              		</tr>
										<tr>
				              <td class="textonormal" bgcolor="#DCEDF7">Inscrição Mercantil</td>
				              <td class="textonormal" height="20">
				              	<?php if( $InscMercantil != "" ) { echo $InscMercantil; }else{ echo "-"; } ?>
				              </td>
				            </tr>
				            <tr>
				              <td class="textonormal" bgcolor="#DCEDF7" width="45%">Inscrição Outro Município</td>
											<td class="textonormal" height="20">
												<?php if( $InscOMunic != "" ) { echo $InscOMunic; }else{ echo "-"; } ?>
											</td>
				            </tr>
				            <tr>
				              <td class="textonormal" bgcolor="#DCEDF7">Inscrição Estadual</td>
				              <td class="textonormal" height="20">
				              	<?php if( $InscEstadual != "" ) { echo $InscEstadual; }else{ echo "NÃO INFORMADO"; } ?>
				              </td>
				            </tr>
										<?php
										if($TipoHabilitacao == "L"){
										?>
											<tr>
												<td class="textonormal" colspan="2">
													<table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" width="100%">
														<tr>
															<td bgcolor="#bfdaf2" class="textoabason" colspan="2" align="center">CERTIDÃO FISCAL</td>
														</tr>
														<tr>
															<td bgcolor="#FFFFFF" class="textoabason"  colspan="2" align="center">OBRIGATÓRIAS</td>
														</tr>
														<tr>
															<td bgcolor="#DCEDF7" class="textonormal">Nome da Certidão</td>
															<td bgcolor="#DCEDF7" class="textonormal">Data de Validade</td>
														</tr>
														<?php
														# Mostra a lista de certidões obrigatórias com datas vazias #
														$sql = "SELECT CTIPCECODI, ETIPCEDESC FROM SFPC.TBTIPOCERTIDAO WHERE FTIPCEOBRI = 'S' ORDER BY 1";
														$res = $db->query($sql);
														if( PEAR::isError($res) ){
																ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
														}else{
																$Rows = $res->numRows();
																for( $i=0; $i<$Rows;$i++ ){
																		$Linha = $res->fetchRow();
																		$DescricaoOb = substr($Linha[1],0,75);
																		$CertidaoOb  = $Linha[0];


																		# Verifica se existem certidões obrigatórias cadastradas para o Inscrito #
																		$sqlData  = "SELECT DPREFCVALI FROM SFPC.TBPREFORNCERTIDAO ";
																		$sqlData .= " WHERE APREFOSEQU = $Sequencial AND CTIPCECODI = $CertidaoOb";
																		$resData = $db->query($sqlData);
																		if( PEAR::isError($resData) ){
																				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																		}else{
																				$LinhaData = $resData->fetchRow();
																				if( $LinhaData[0] <> 0 ){
																						$DataCertidaoOb[$ob-1] = substr($LinhaData[0],8,2)."/".substr($LinhaData[0],5,2)."/".substr($LinhaData[0],0,4);
																				}else{
																						$DataCertidaoOb[$ob-1] = null;
																				}
																		}
																		if( $LinhaData[0] < date("Y-m-d") ){
																				$Validade = "titulo1";
																		}else{
																				$Validade = "textonormal";
																		}
																		echo "<tr>\n";
																		echo "	<td class=\"$Validade\" width=\"*\">$DescricaoOb</td>\n";
																		echo "	<td class=\"textonormal\" width=\"22%\" align=\"center\">\n";
																		if(is_null($DataCertidaoOb[$ob-1])){
																			echo "NÃO INFORMADO";
																		}else{
																			echo $DataCertidaoOb[$ob-1];
																		}
																		echo "	</td>\n";
																		echo "</tr>\n";
																}
														}
														# Verifica se existem certidões complementares cadastradas para o Inscrito #
														$sql  = "SELECT A.DPREFCVALI, B.CTIPCECODI, B.ETIPCEDESC  ";
														$sql .= "  FROM SFPC.TBPREFORNCERTIDAO A, SFPC.TBTIPOCERTIDAO B ";
														$sql .= " WHERE A.APREFOSEQU = $Sequencial AND A.CTIPCECODI = B.CTIPCECODI";
														$sql .= "   AND B.FTIPCEOBRI = 'N' ORDER BY 2";
														$res = $db->query($sql);
														if( PEAR::isError($res) ){
																ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
														}else{
																$Rows = $res->numRows();
																echo "<tr>\n";
																echo "	<td bgcolor=\"#FFFFFF\" class=\"textoabason\"  colspan=\"2\" align=\"center\">COMPLEMENTARES</td>\n";
																echo "</tr>\n";
																if( $Rows != 0 ){
																		# Mostra as certidões complementares cadastradas #
																		for( $i=0; $i<$Rows;$i++ ){
																				$Linha = $res->fetchRow();
																				$DescricaoOp					= substr($Linha[2],0,75);
																				$CertidaoOpCodigo			= $Linha[1];
																				$CertidaoOpcional[$i] = $Linha[1];
																				$DataCertidaoOp[$i]		= substr($Linha[0],8,2)."/".substr($Linha[0],5,2)."/".substr($Linha[0],0,4);
																				if( $Linha[0] < date("Y-m-d") ){
																						$Validade = "titulo1";
																				}else{
																						$Validade = "textonormal";
																				}
																				if( $i == 0 ){
																						echo "<tr>\n";
																						echo "	<td bgcolor=\"#DCEDF7\" class=\"textonormal\">Nome da Certidão</td>\n";
																						echo "	<td bgcolor=\"#DCEDF7\" class=\"textonormal\">Data de Validade</td>\n";
																						echo "</tr>\n";
																				}
																				echo "		<tr>\n";
																				echo "			<td class=\"$Validade\" width=\"*\">$DescricaoOp</td>\n";
																				echo "			<td class=\"textonormal\" width=\"22%\" align=\"center\">".$DataCertidaoOp[$i]."</td>\n";
																				echo "		</tr>\n";
																		}
																}else{
																		echo "		<tr>\n";
																		echo "			<td class=\"textonormal\" bgcolor=\"#FFFFFF\" align=\"center\" colspan=\"2\">NÃO INFORMADO</td>\n";
																		echo "		</tr>\n";
																}
														}
														?>
													</table>
												</td>
											</tr>
										<?php
										}
										?>
									</table>
								</td>
							</tr>
							<tr>
								<td>
									<table cellpadding="0" cellspacing="2" border="0" bordercolor="#75ADE6" width="100%" summary="">
										<!-- QUALIFICAÇÃO ECONÔMICA E FINANCEIRA -->
          		      <tr>
	              			<td bgcolor="#75ADE6" class="textoabasoff" colspan="2" align="center" height="20">QUALIFICAÇÃO ECONÔMICA E FINANCEIRA</td>
	              		</tr>
			          		<?php if ( $CNPJ <> 0) { ?>
											<tr>
					              <td class="textonormal" bgcolor="#DCEDF7">Capital Social Subscrito</td>
					              <td class="textonormal" height="20"><?php echo $CapSocial;?></td>
					            </tr>
											<tr>
					              <td class="textonormal" bgcolor="#DCEDF7">Capital Integralizado</td>
					              <td class="textonormal" height="20">
					              	<?php if( $CapIntegralizado != "" ){ echo $CapIntegralizado; } else { echo "NÃO INFORMADO";} ?>
					              </td>
					            </tr>
					            <tr>
					              <td class="textonormal" bgcolor="#DCEDF7">Patrimônio Líquido</td>
					              <td class="textonormal" height="20"><?php echo $Patrimonio;?></td>
					            </tr>
											<tr>
					              <td class="textonormal" bgcolor="#DCEDF7" width="45%">Índice de Liquidez Corrente</td>
					              <td class="textonormal" height="20">
					              	<?php if( $IndLiqCorrente != "" ){ echo $IndLiqCorrente; } else { echo "NÃO INFORMADO";} ?>
					              </td>
					            </tr>
					            <tr>
					              <td class="textonormal" bgcolor="#DCEDF7">Índice de Liquidez Geral</td>
												<td class="textonormal" height="20">
													<?php if( $IndLiqGeral != "" ){ echo $IndLiqGeral; } else { echo "NÃO INFORMADO";} ?>
												</td>
					            </tr>
					            <tr>
					              <td class="textonormal" bgcolor="#DCEDF7">Índice de Endividamento</td>
												<td class="textonormal" height="20">
													<?php if( $IndEndividamento != "" ){ echo $IndEndividamento; } else { echo "NÃO INFORMADO";} ?>
												</td>
					            </tr>
					            <tr>
					              <td class="textonormal" bgcolor="#DCEDF7">Índice de Solvência</td>
												<td class="textonormal" height="20">
													<?php if( $IndSolvencia != "" ){ echo $IndSolvencia; } else { echo "NÃO INFORMADO";} ?>
												</td>
					            </tr>
					            <tr>
					              <td class="textonormal" bgcolor="#DCEDF7" width="45%">Data validade do balanço</td>
												<td class="textonormal" height="20">
													<?php if( $DataUltBalanco != "" ) { echo $DataUltBalanco; }else{ echo "NÃO INFORMADO"; } ?>
												</td>
					            </tr>
					            <tr>
					              <td class="textonormal" bgcolor="#DCEDF7" width="45%">Data Certidão Negativa de Falência e Concordata</td>
												<td class="textonormal" height="20">
													<?php if( $DataCertidaoNeg != "" ) { echo $DataCertidaoNeg; }else{ echo "NÃO INFORMADO"; } ?>
												</td>
					            </tr>
					            <tr>
					              <td class="textonormal" bgcolor="#DCEDF7" width="45%">Data de última alteração de contrato ou estatuto</td>
												<td class="textonormal" height="20">
													<?php if( $DataContratoEstatuto != "" ) { echo $DataContratoEstatuto; }else{ echo "NÃO INFORMADO"; } ?>
												</td>
					            </tr>
			          		<?php } ?>
										<tr>
				            	<td colspan="2">
					            	<table align="center" border="1" cellpadding="3" cellspacing="0" bgcolor="#DCEDF7" bordercolor="#75ADE6" class="textonormal" width="100%" summary="">
					              	<tr>
					              		<td class="textonormal" bgcolor="#DCEDF7" align="center">Banco</td>
					              		<td class="textonormal" bgcolor="#DCEDF7" align="center">Agência </td>
					              		<td class="textonormal" bgcolor="#DCEDF7" align="center">Conta Corrente</td>
					              	</tr>
					              	<tr>
					              	<?php if( $Banco1 != "" ) { ?>
					              		<td class="textonormal" bgcolor="#FFFFFF" align="center"><?php echo $Banco1;?></td>
					            	  	<td class="textonormal" bgcolor="#FFFFFF" align="center"><?php echo $Agencia1;?></td>
							              <td class="textonormal" bgcolor="#FFFFFF" align="center"><?php echo $ContaCorrente1;?></td>
							            </tr>
								          <?php } ?>
								          <?php if( $Banco2 != "" ) {	?>
								          <tr>
								          	<td class="textonormal" bgcolor="#FFFFFF" align="center"><?php echo $Banco2;?></td>
								            <td class="textonormal" bgcolor="#FFFFFF" align="center"><?php echo $Agencia2; ?></td>
								            <td class="textonormal" bgcolor="#FFFFFF" align="center"><?php echo $ContaCorrente2;?></td>
								          <?php } ?>
							            <?php if (( $Banco1 == "" ) and ( $Banco2 == "" )) {?>
							            	<td class="textonormal" bgcolor="#FFFFFF" align="center" colspan="3"><?php echo "NÃO INFORMADO";?></td>
							            <?php } ?>
					            		</tr>
					            	</table>
					            </td>
				            </tr>
									</table>
								</td>
							</tr>
							<tr>
								<td>
								  <table cellpadding="0" cellspacing="2" border="0" bordercolor="#75ADE6" width="100%" summary="">
										<!-- QUALIFICAÇÃO TÉCNICA -->
							      <tr>
	              					<td bgcolor="#75ADE6" class="textoabasoff" colspan="2" align="center" height="20">QUALIFICAÇÃO TÉCNICA</td>
	              				  </tr>
										<?php
										if($TipoHabilitacao == "L"){
										?>
											<tr>
												<td class="textonormal" bgcolor="#DCEDF7">Nome da Entidade</td>
												<td class="textonormal" height="20">
													<?php if( $NomeEntidade != "" ){ echo "$NomeEntidade"; } else { echo "NÃO INFORMADO"; } ?>
												</td>
											</tr>
											<tr>
												<td class="textonormal" bgcolor="#DCEDF7" width="45%">Registro ou Inscrição </td>
												<td class="textonormal" height="20">
													<?php if( $RegistroEntidade != "" ){ echo "$RegistroEntidade"; } else { echo "NÃO INFORMADO"; } ?>
												</td>
											</tr>
											<tr>
												<td class="textonormal" bgcolor="#DCEDF7">Data da Vigência</td>
												<td class="textonormal" height="20">
													<?php if( $DataVigencia != "" ){ echo "$DataVigencia"; } else { echo "NÃO INFORMADO"; } ?>
												</td>
											</tr>
											<tr>
												<td class="textonormal" bgcolor="#DCEDF7">Registro ou Inscrição do Técnico</td>
												<td class="textonormal" height="20">
													<?php if( $TecnicoEntidade != "" ){ echo "$TecnicoEntidade"; } else { echo "NÃO INFORMADO"; } ?>
												</td>
											</tr>
											<tr>
												<td colspan="2">
													<table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" width="100%">
													<tr>
														<td bgcolor="#bfdaf2" class="textoabason"  colspan="6" align="center" height="20">AUTORIZAÇÃO ESPECÍFICA </td>
													</tr>
													<?php
													# Mostra as autorizações específicas do Inscrito cadatradas #
													$sql  = "SELECT APREFANUMA, NPREFANOMA, DPREFAVIGE FROM SFPC.TBPREFORNAUTORIZACAOESPECIFICA ";
													$sql .= " WHERE APREFOSEQU = $Sequencial";
													$res = $db->query($sql);
													if( PEAR::isError($res) ){
															ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
													}else{
															$Rows = $res->numRows();
															if( $Rows <> 0 ){
																	echo "<tr>\n";
																	echo "	<td bgcolor=\"#DCEDF7\" class=\"textonormal\" colspan=\"2\">Nome da Entidade Emissora</td>\n";
																	echo "	<td bgcolor=\"#DCEDF7\" class=\"textonormal\" colspan=\"2\">Registro ou Inscrição</td>\n";
																	echo "	<td bgcolor=\"#DCEDF7\" class=\"textonormal\" colspan=\"2\" align=\"center\">Data de Vigência</td>\n";
																	echo "	</td>\n";
																	echo "</tr>\n";
																	for( $i=0; $i<$Rows;$i++ ){
																			$Linha				= $res->fetchRow();
																			$RegistroAutor= $Linha[0];
																			$NomeAutor		= $Linha[1];
																			$DataVigAutor	= substr($Linha[2],8,2)."/".substr($Linha[2],5,2)."/".substr($Linha[2],0,4);
																			echo "<tr>\n";
																			echo "	<td class=\"textonormal\" bgcolor=\"#FFFFFF\" height=\"20\" colspan=\"2\">\n";
																			if( $NomeAutor != "" ){ echo "$NomeAutor"; } else { echo "NÃO INFORMADO"; }
																			echo "	</td>\n";
																			echo "	<td class=\"textonormal\" bgcolor=\"#FFFFFF\" height=\"20\" colspan=\"2\">\n";
																			if( $RegistroAutor != "" ){ echo "$RegistroAutor"; } else { echo "NÃO INFORMADO"; }
																			echo "	</td>\n";
																			echo "	<td class=\"textonormal\" bgcolor=\"#FFFFFF\" height=\"20\" align=\"center\">\n";
																			if( $DataVigAutor != "" ){ echo "$DataVigAutor"; } else { echo "NÃO INFORMADO"; }
																			echo "	</td>\n";
																			echo "</tr>\n";
																	}
															}else{
																	echo "<tr>\n";
																	echo "	<td bgcolor=\"#FFFFFF\" class=\"textonormal\" align=\"center\" colspan=\"6\">NÃO INFORMADO</td>\n";
																	echo "</tr>\n";
															}
													}
													?>
													</table>
												</td>
											</tr>
										<?php
										}
										?>
	              		<tr>
	              			<td colspan="2">
	              				<table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" width="100%">
					          		<tr>
			              			<td bgcolor="#bfdaf2" class="textoabason" colspan="2" align="center" height="20">GRUPOS DE FORNECIMENTO (OBJETO SOCIAL) </td>
			              		</tr>
	              				<?php
			              		# Mostra os grupos de materiais já cadastrados do Inscrito #
												$sql  = "SELECT A.APREFOSEQU, B.CGRUMSCODI, B.EGRUMSDESC ";
												$sql .= "  FROM SFPC.TBGRUPOPREFORNECEDOR A, SFPC.TBGRUPOMATERIALSERVICO B ";
												$sql .= " WHERE A.APREFOSEQU = $Sequencial AND A.CGRUMSCODI = B.CGRUMSCODI ";
												$sql .= "   AND B.FGRUMSTIPO = 'M' ORDER BY 1,3";
												$res = $db->query($sql);
											  if( PEAR::isError($res) ){
														ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
												}else{
														$Rows = $res->numRows();
														if( $Rows <> 0 ){
				              					# Mostra os grupos de materiais cadastrados #
							              		echo "<tr>\n";
							              		echo "	<td bgcolor=\"#DDECF9\" class=\"textoabason\"  colspan=\"2\" align=\"center\">MATERIAIS</td>\n";
							              		echo "</tr>\n";
							              		$DescricaoGrupoAntes = "";
							              		for( $i=0; $i<$Rows;$i++ ){
																		$Linha										= $res->fetchRow();
						          	      			$DescricaoGrupo   				= substr($Linha[2],0,75);
									    	      			$Materiais[$i]= "M#".$Linha[1];
									    	      			if( $DescricaoGrupoAntes <> $DescricaoGrupo ){
									              				echo "			<tr bgcolor=\"#FFFFFF\">\n";
										              				echo "				<td class=\"textonormal\" width=\"*\">$DescricaoGrupo</td>\n";
										              				echo "			</tr>\n";
							              				}
									    	      			$DescricaoGrupoAntes = $DescricaoGrupo;
								    	      		}
							    	      	}
					            	}

		                		# Mostra os grupos de materiais já cadastrados do Inscrito #
												$sql  = "SELECT A.APREFOSEQU, B.CGRUMSCODI, B.EGRUMSDESC ";
												$sql .= "  FROM SFPC.TBGRUPOPREFORNECEDOR A, SFPC.TBGRUPOMATERIALSERVICO B  ";
												$sql .= " WHERE A.APREFOSEQU = $Sequencial AND A.CGRUMSCODI = B.CGRUMSCODI ";
												$sql .= "   AND B.FGRUMSTIPO = 'S' ORDER BY 1,3";
												$res = $db->query($sql);
											  if( PEAR::isError($res) ){
													  ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
												}else{
														$Rows = $res->numRows();
														if ($Rows <> 0) {
				              					# Mostra os grupos de serviços cadastrados #
							              		echo "<tr>\n";
							              		echo "	<td bgcolor=\"#DDECF9\" class=\"textoabason\"  colspan=\"2\" align=\"center\" height=\"20\">SERVIÇOS</td>\n";
							              		echo "</tr>\n";
							    	      			$DescricaoGrupoAntes = "";
							              		for( $i=0; $i<$Rows;$i++ ){
																	$Linha = $res->fetchRow();
					          	      			$DescricaoGrupo   = substr($Linha[2],0,75);
								    	      			$Servicos[$i]= "S#".$Linha[1];
								    	      			if( $DescricaoGrupo <> $DescricaoGrupoAntes ){
								              				echo "			<tr bgcolor=\"#FFFFFF\">\n";
									              				echo "				<td class=\"textonormal\" colspan=\"2\" height=\"18\">$DescricaoGrupo</td>\n";
									              				echo "			</tr>\n";
						              				}
									    	      		$DescricaoGrupoAntes = $DescricaoGrupo;
								    	      		}
					              		}
						            }
						            ?>
			              		</table>
			              	</td>
			            	</tr>
				          </table>
				        </td>
							</tr>



									 <?php
									 $db = Conexao();
									 $sql = "  SELECT doc.cfdocusequ, doc.aprefosequ, doc.aforcrsequ, doc.afdocuanoa, 
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
											WHERE aprefosequ = " . $Sequencial . " AND ffdocusitu = 'A' order by  tfdoctulat DESC";

									
									



									$result = $db->query($sql);
									if (PEAR::isError($result)) {
										ExibeErroBD($ErroPrograma . "\nLinha: " . __LINE__ . "\nSql: $sql");
									} else {
										if($result->numRows() > 0){
										?>
										<tr>
											<td>
											<table cellpadding="3" cellspacing="0" border="1" bordercolor="#75ADE6" width="100%" summary="">
												<!-- DOCUMENTOS-->
											<tr>
												<td bgcolor="#75ADE6" class="textoabasoff" bordercolor="#75ADE6" colspan="8" align="center" height="20">DOCUMENTOS</td>
											</tr>
											<tr>
												<td bgcolor="#DCEDF7" class="textonormal" colspan="8" align="left" height="20">
												Ano da anexação: <select name="pesqAnoDoc" id="pesqAnoDoc" class="tamanho_campo textonormal" onChange="javascript:enviar('PesquisaAnoDoc');">
													<?php
														$arr = array();
														$anos = array();
														$arrDocs = array();

														$resultAno = $result;
														while ($linha = $resultAno->fetchRow()) {
															$arrDocs[] = $linha;
															$anos[] = $linha[3];
														}

														for($j = date('Y'); $j > 2000; $j--){
															if(in_array($j, $anos)){
																$arr[] = $j;
															}
														}
														
														foreach ($arr as $value) {
															if( $value == $pesqAnoDoc ){
																echo '<option value="'.$value.'" selected>'.$value.'</option>';
															}else{
																echo '<option value="'.$value.'">'.$value.'</option>';
															}
														}
													?>
													</select>
												</td>
											</tr>
											<tr>
											
												<td bgcolor="#DCEDF7" align="center" class="textonormal"> Tipo do documento</td>
												<td bgcolor="#DCEDF7" align="center" class="textonormal"> Anexo</td>
												<td bgcolor="#DCEDF7" align="center" class="textonormal"> Responsável anexação</td>
												<td bgcolor="#DCEDF7" align="center" class="textonormal"> Data/Hora Anexação</td>
												<td bgcolor="#DCEDF7" align="center" class="textonormal"> Situação</td>
												<td bgcolor="#DCEDF7" align="center" class="textonormal"> Responsável última alteração</td>
												<td bgcolor="#DCEDF7" align="center" class="textonormal"> Data/Hora última alteração</td>
												<td bgcolor="#DCEDF7" align="center" class="textonormal"> Observação</td>
											</tr> 
										<?php //$resultado = $result->fetchRow();
										//var_dump( $arrDocs);
										//die();
										foreach ($arrDocs as $linha) {
											
											
											if($arr[0]){
												$pesqAnoDoc = $arr[0];
											}else{
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

												?>

												<tr>
												<td class="textonormal"><?php echo $linha[14]?></td>
												<td class="textonormal"><a href='javascript: baixarArquivo(<?php echo $linha[0]?>);'><?php echo $linha[5]?></a></td>
												<td class="textonormal" align="center"><?php echo $nomeUsuAnex?></td>
												<td class="textonormal" align="center"><?php echo formatarDataHora($linha[8]) ?></td>
												<td class="textonormal"><?php echo $linha[19]?></td>
												<td class="textonormal" align="center"><?php echo $nomeUsuUltAlt?></td>
												<td class="textonormal" align="center"><?php echo formatarDataHora($linha[17])?></td>
												<td class="textonormal"><?php echo $linha[13]?></td>
												</tr>

												
												<?php
											}//else{
											//	echo 'ANO: '.$linha[3];
											//}
										}
										?></table>
											</td>
										</tr>
									<?php }

									}
									?>

									

	          	<tr>
								<td align="right">
									<input type="hidden" name="Sequencial" value="<?php echo $Sequencial; ?>">
									<input type="hidden" name="Mensagem" value="<?php echo $Mensagem; ?>">
									<input type="hidden" name="codDownload" id="codDownload">

                                    <input type="hidden" name="Retorno" id="Retorno" value="<?php echo $Retorno ?>">
                                    <input type="hidden" name="docSituacao" id="docSituacao" value="<?php echo $docSituacao ?>">
                                    <input type="hidden" name="docInicio" id="docInicio" value="<?php echo $docInicio ?>">
                                    <input type="hidden" name="docFim" id="docFim" value="<?php echo $docFim ?>">

									<input type="button" value="Imprimir" class="botao" onclick="javascript:enviar('Imprimir');">
									<?php
									if( $_SESSION['_cperficodi_'] <> 0 ) { ?>
										<input type="button" value="Voltar" class="botao" onclick="javascript:enviar('Voltar');">
								  <?php }?>
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
</form>
</body>
</html>
<?php $db->disconnect();?>

<?php
#-----------------------------------------------------------------------------
# Portal da DGCO
# Programa: ConsMaterialSelecionar.php
# Autor:    Altamiro Pedrosa
# Data:     29/08/2005
# Objetivo: Programa de Inclusão de Itens da Requisição de Material
# OBS.:     Tabulação 2 espaços
#-----------------------------------------------------------------------------
# Alterado: Álvaro Faria
# Data:     29/08/2006 - Opção de pesquisa de material com descrição "iniciada por"
#-----------------------------------------------------------------------------
# Alterado: Wagner Barros
# Data:     02/10/2006 - Exibir o código reduzido do material ao lado da descrição
#-----------------------------------------------------------------------------
# Alterado: Ariston Cordeiro
# Data:     05/01/2009 - permitir que descrição procure por textos dentro de palavras
#-----------------------------------------------------------------------------
# Alterado: Ariston Cordeiro
# Data:     03/03/2009 - CR1418- Procurar descrição dos materiais em descrição completa, ao invés de resumida
#-----------------------------------------------------------------------------
# Alterado: Ariston Cordeiro
# Data:     05/05/2009 	- CR783- Não mostrar materiais inativos, apenas se for selecionado um checkbox apropriado.
#													Este checkbox apenas será acessado pelos administradores geral e dglc.
#-----------------------------------------------------------------------------
# Alterado: Rodrigo Melo
# Data:     21/09/2009 	- Alteração para inserir o cadastro de serviços
#-----------------------------------------------------------------------------
# Alterado:	Lucas Baracho
# Data:		04/07/2018
# Objetivo:	Tarefa Redmine 77802
#-----------------------------------------------------------------------------
# Alterado:	Lucas Baracho
# Data:		13/08/2018
# Objetivo:	Tarefa Redmine 95882
#-----------------------------------------------------------------------------
# Alterado:	Lucas Baracho
# Data:		21/08/2018
# Objetivo:	Tarefa Redmine 201733
#-----------------------------------------------------------------------------
# Alterado: Lucas Baracho
# Data:		24/10/2018
# Objetivo: Tarefa Redmine 73662
#-----------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança	#
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/estoques/CadItemDetalhe.php' );

# Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD'] == "POST") {
	$ProgramaOrigem            		 = $_POST['ProgramaOrigem'];
	$Botao                     		 = $_POST['Botao'];
	$TipoMaterial              		 = $_POST['TipoMaterial'];
	$TipoGrupo                 		 = $_POST['TipoGrupo'];
	$Grupo                     		 = $_POST['Grupo'];
	$Classe                    		 = $_POST['Classe'];
	$Subclasse                 		 = $_POST['Subclasse'];
	$MaterialServicoDescricaoFamilia = strtoupper2(trim($_POST['$MaterialServicoDescricaoFamilia']));

	# Pesquisa direta #
	$OpcaoPesquisaMaterial     = $_POST['OpcaoPesquisaMaterial'];
	$OpcaoPesquisaSubClasse    = $_POST['OpcaoPesquisaSubClasse'];
	$OpcaoPesquisaServico      = $_POST['OpcaoPesquisaServico'];
	$SubclasseDescricaoDireta  = strtoupper2(trim($_POST['SubclasseDescricaoDireta']));
	$MaterialDescricaoDireta   = strtoupper2(trim($_POST['MaterialDescricaoDireta']));
	$ServicoDescricaoDireta    = strtoupper2(trim($_POST['ServicoDescricaoDireta']));
	$Inativos                  = $_POST['Inativos'];
	$Sustentaveis              = $_POST['Sustentaveis'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

# Máximo tamanho de um texto que aparece numa caixa de texto ou combo box
$maxTamanhoTexto = 90;

# Monta o sql para montagem dinâmica da grade a partir da pesquisa #
$sql  = " SELECT DISTINCT(GRU.CGRUMSCODI), GRU.EGRUMSDESC, CLA.CCLAMSCODI, CLA.ECLAMSDESC, GRU.FGRUMSTIPO, ";

if (($TipoGrupo == "M" or $MaterialDescricaoDireta != "" or $SubclasseDescricaoDireta != "") and $ServicoDescricaoDireta == "") {
	$sql .= " MAT.CMATEPSEQU, MAT.EMATEPDESC, SUB.CSUBCLSEQU, SUB.ESUBCLDESC, UND.EUNIDMSIGL, GRU.FGRUMSTIPM, MAT.CMATEPSITU ";
} else {
	$sql .= " SERV.CSERVPSEQU, SERV.ESERVPDESC, SERV.CSERVPSITU ";
}

$from = " FROM SFPC.TBGRUPOMATERIALSERVICO GRU INNER JOIN SFPC.TBCLASSEMATERIALSERVICO CLA ON CLA.FCLAMSSITU = 'A' AND CLA.CGRUMSCODI = GRU.CGRUMSCODI AND GRU.FGRUMSSITU = 'A' ";

if (($TipoGrupo == "M" or $MaterialDescricaoDireta != "" or $SubclasseDescricaoDireta != "") and $ServicoDescricaoDireta == "") {
	$from .= " INNER JOIN SFPC.TBSUBCLASSEMATERIAL SUB ON SUB.FSUBCLSITU = 'A' AND SUB.CCLAMSCODI = CLA.CCLAMSCODI AND SUB.CGRUMSCODI = CLA.CGRUMSCODI ";
    $from .= " INNER JOIN SFPC.TBMATERIALPORTAL MAT ON MAT.CSUBCLSEQU = SUB.CSUBCLSEQU ";
    $from .= " INNER JOIN SFPC.TBUNIDADEDEMEDIDA UND ON MAT.CUNIDMCODI = UND.CUNIDMCODI ";
} else {
	$from .= " INNER JOIN SFPC.TBSERVICOPORTAL SERV ON SERV.CCLAMSCODI = CLA.CCLAMSCODI AND SERV.CGRUMSCODI = CLA.CGRUMSCODI ";
}

$where  = " WHERE 1 = 1 "; //Artificio utilizado para colocar o 'AND' na clausula WHERE sem se preocupar.

if (($TipoGrupo == "M" or $MaterialDescricaoDireta != "" or $SubclasseDescricaoDireta != "") and $ServicoDescricaoDireta == "") {
	$order  = " ORDER BY GRU.FGRUMSTIPM, GRU.EGRUMSDESC, CLA.ECLAMSDESC, SUB.ESUBCLDESC, MAT.EMATEPDESC ";
} else {
	$order  = " ORDER BY GRU.EGRUMSDESC, CLA.ECLAMSDESC, SERV.ESERVPDESC ";
}

# Verifica se o Tipo de Material foi escolhido #
if ($TipoGrupo == "M" and $TipoMaterial != "" and $MaterialDescricaoDireta == "" and $SubclasseDescricaoDireta == "") {
	$where .= " AND GRU.FGRUMSTIPM = '$TipoMaterial' ";
}

# Verifica se o Grupo foi escolhido #
if ($Grupo != "" and $MaterialDescricaoDireta == "" and $SubclasseDescricaoDireta == "" and $ServicoDescricaoDireta == "") {
	$where .= " AND GRU.CGRUMSCODI = $Grupo ";
}

# Verifica se a Classe foi escolhida #
if ($Classe != "" and $MaterialDescricaoDireta == "" and $SubclasseDescricaoDireta == "" and $ServicoDescricaoDireta == "") {
	$where .= " AND CLA.CGRUMSCODI = $Grupo AND CLA.CCLAMSCODI = $Classe ";
}

# Verifica se a SubClasse foi escolhida #
if ($Subclasse != "" and $MaterialDescricaoDireta == "" and $SubclasseDescricaoDireta == "" and $ServicoDescricaoDireta == "") {
	// echo "AQUI Madson!";exit;
	$where .= " AND SUB.CSUBCLSEQU = $Subclasse ";
}

# Caso se deve ignorar sustentaveis
if ($Sustentaveis == 'S') {
	if ($TipoGrupo == "M" or $MaterialDescricaoDireta != "" or $SubclasseDescricaoDireta != "") {
		$where .= " AND MAT.FMATEPSUST = 'S' ";
	}
}

# Caso se deve ignorar inativos
if ($Inativos != 'S') {
	if (($TipoGrupo == "M" or $MaterialDescricaoDireta != "" or $SubclasseDescricaoDireta != "") and $ServicoDescricaoDireta == "") {
		$where .= " AND MAT.CMATEPSITU = 'A' ";
	} else {
		$where .= " AND SERV.CSERVPSITU = 'A' ";
	}
}

# Se foi digitado algo na caixa de texto da subclasse em pesquisa familia #
if ($MaterialServicoDescricaoFamilia != "" and $MaterialDescricaoDireta == "" and $SubclasseDescricaoDireta == "" and $ServicoDescricaoDireta == "") {
		$where .= " AND (TRANSLATE(SUB.ESUBCLDESC,'ÃÕÁÉÍÓÚÀÂÊÔÜÇ','AOAEIOUAAEOUC') ILIKE '%".strtoupper2(RetiraAcentos($MaterialServicoDescricaoFamilia))."%') ";
}

# Se foi digitado algo na caixa de texto da subclasse em pesquisa direta #
if ($SubclasseDescricaoDireta != "" and $MaterialDescricaoDireta == "" and $ServicoDescricaoDireta == "") {
	if ($OpcaoPesquisaSubClasse == 0) {
		if (SoNumeros($SubclasseDescricaoDireta)) {
			$where .= " AND SUB.CSUBCLSEQU = $SubclasseDescricaoDireta ";
		}
	} elseif ($OpcaoPesquisaSubClasse == 1) {
		$where .= " AND TRANSLATE(SUB.ESUBCLDESC,'ÃÕÁÉÍÓÚÀÂÊÔÜÇ','AOAEIOUAAEOUC') ILIKE '%".strtoupper2(RetiraAcentos($SubclasseDescricaoDireta))."%' ";
	} else {
		$where .= " AND TRANSLATE(SUB.ESUBCLDESC,'ÃÕÁÉÍÓÚÀÂÊÔÜÇ','AOAEIOUAAEOUC') ILIKE '".strtoupper2(RetiraAcentos($SubclasseDescricaoDireta))."%' ";
	}
}

# Se foi digitado algo na caixa de texto do material em pesquisa direta #
if ($MaterialDescricaoDireta != "" and $SubclasseDescricaoDireta == "" and $ServicoDescricaoDireta == "") {
	if ($OpcaoPesquisaMaterial == 0 ) {
		if (SoNumeros($MaterialDescricaoDireta)) {
			$where .= " AND MAT.CMATEPSEQU = $MaterialDescricaoDireta ";
		}
	} elseif ($OpcaoPesquisaMaterial == 1) {
		$where .= " AND TRANSLATE(MAT.EMATEPCOMP,'ÃÕÁÉÍÓÚÀÂÊÔÜÇ','AOAEIOUAAEOUC') ILIKE '%".strtoupper2(RetiraAcentos($MaterialDescricaoDireta))."%' ";
	} else {
		$where .= " AND TRANSLATE(MAT.EMATEPCOMP,'ÃÕÁÉÍÓÚÀÂÊÔÜÇ','AOAEIOUAAEOUC') ILIKE '".strtoupper2(RetiraAcentos($MaterialDescricaoDireta))."%' ";
	}
}

# Se foi digitado algo na caixa de texto do serviço em pesquisa direta #
if ($ServicoDescricaoDireta != "" and $SubclasseDescricaoDireta == "" and $MaterialDescricaoDireta == "") {
	if ($OpcaoPesquisaServico == 0) {
		if (SoNumeros($ServicoDescricaoDireta)) {
			$where .= " AND SERV.CSERVPSEQU = $ServicoDescricaoDireta ";
		}
	} elseif ($OpcaoPesquisaServico == 1) {
		$where .= " AND TRANSLATE(SERV.ESERVPDESC,'ÃÕÁÉÍÓÚÀÂÊÔÜÇ','AOAEIOUAAEOUC') ILIKE '%".strtoupper2(RetiraAcentos($ServicoDescricaoDireta))."%' ";
	} else {
		$where .= " AND TRANSLATE(SERV.ESERVPDESC,'ÃÕÁÉÍÓÚÀÂÊÔÜÇ','AOAEIOUAAEOUC') ILIKE '".strtoupper2(RetiraAcentos($ServicoDescricaoDireta))."%' ";
	}
}

# Gera o SQL com a concatenação das variaveis $sql,$from,$where,$order #
$sqlgeral = $sql.$from.$where.$order;

if ($Botao == "Limpar") {
	header("location: ConsMaterialSelecionar.php");
	exit;
} elseif ($Botao == "Validar") {
	# Critica dos Campos #
	$Mens     = 0;
	$Mensagem = "Informe: ";
	
	if ($SubclasseDescricaoDireta != "" and $OpcaoPesquisaSubClasse == 0 and ! SoNumeros($SubclasseDescricaoDireta)) {
		if ($Mens == 1) {
			$Mensagem .= ", ";
		}
		
		$Mens = 1;
		$Tipo = 2;
		$Mensagem .= "<a href=\"javascript:document.ConsMaterialSelecionar.SubclasseDescricaoDireta.focus();\" class=\"titulo2\">Código reduzido da Subclasse</a>";
	} elseif ($SubclasseDescricaoDireta != "" and ($OpcaoPesquisaSubClasse == 1 or $OpcaoPesquisaSubClasse == 2) and strlen($SubclasseDescricaoDireta)< 2) {
		if ($Mens == 1) {
			$Mensagem .= ", ";
		}
		
		$Mens = 1;
		$Tipo = 2;
		$Mensagem .= "<a href=\"javascript:document.ConsMaterialSelecionar.SubclasseDescricaoDireta.focus();\" class=\"titulo2\">Descrição com no mínimo 2 caracteres</a>";
	}
	
	if ($MaterialDescricaoDireta != "" and $OpcaoPesquisaMaterial == 0 and ! SoNumeros($MaterialDescricaoDireta)) {
		if ($Mens == 1) {
			$Mensagem .= ", ";
		}
		
		$Mens = 1;
		$Tipo = 2;
		$Mensagem .= "<a href=\"javascript:document.ConsMaterialSelecionar.MaterialDescricaoDireta.focus();\" class=\"titulo2\">Código reduzido do Material</a>";
	} elseif ($MaterialDescricaoDireta != "" and ($OpcaoPesquisaMaterial == 1 or $OpcaoPesquisaMaterial == 2) and strlen($MaterialDescricaoDireta)< 2) {
		if ($Mens == 1) {
			$Mensagem .= ", ";
		}
		
		$Mens = 1;
		$Tipo = 2;
		$Mensagem .= "<a href=\"javascript:document.ConsMaterialSelecionar.MaterialDescricaoDireta.focus();\" class=\"titulo2\">Descrição com no mínimo 2 caracteres</a>";
	}
}

?>
<html>
	<?php	# Carrega o layout padrão #
			layout(); ?>
<head>
	<title>Portal de Compras - Consulta Material</title>
	<script language="javascript" type="">
	function enviar (valor) {
		document.ConsMaterialSelecionar.Botao.value = valor;
		document.ConsMaterialSelecionar.submit();
	}

	function validapesquisa() {
		if ((document.ConsMaterialSelecionar.MaterialDescricaoDireta.value != '') || (document.ConsMaterialSelecionar.SubclasseDescricaoDireta.value != '')) {
			if (document.ConsMaterialSelecionar.Grupo) {
				document.ConsMaterialSelecionar.Grupo.value = '';
			}
		
			if (document.ConsMaterialSelecionar.Classe) {
				document.ConsMaterialSelecionar.Classe.value = '';
			}
			document.ConsMaterialSelecionar.Botao.value = 'Validar';
		}

		if (document.ConsMaterialSelecionar.Subclasse) {
			if (document.ConsMaterialSelecionar.MaterialServicoDescricaoFamilia.value != "") {
				document.ConsMaterialSelecionar.Subclasse.value = 0;
			}
		}
		document.ConsMaterialSelecionar.submit();
	}

	function AbreJanela(url,largura,altura) {
		window.open(url,'paginadetalhe','status=no,scrollbars=yes,left=45,top=150,width='+largura+',height='+altura);
	}

	function remeter(){
		document.ConsMaterialSelecionar.submit();
	}
	<?php MenuAcesso(); ?>
	//-->
	</script>
	<link rel="stylesheet" type="text/css" href="../estilo.css">
</head>
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
	<script language="JavaScript" src="../menu.js"></script>
	<script language="JavaScript">Init();</script>
	<form action="ConsMaterialSelecionar.php" method="post" name="ConsMaterialSelecionar">
		<br><br><br><br><br>
		<table cellpadding="3" border="0" summary="">
		<!-- Caminho -->
		<tr>
			<td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
			<td align="left" class="textonormal" colspan="2">
				<font class="titulo2">|</font>
				<a href="../index.php"><font color="#000000">Página Principal</font></a> > Materiais/Serv > Cadastro > Consulta
			</td>
		</tr>
		<!-- Fim do Caminho-->
		<!-- Erro -->
		<?php if($Mens == 1){?>
		<tr>
			<td width="100"></td>
			<td align="left" colspan="2">
				<?php if ( $Mens == 1 ) { ExibeMens($Mensagem,$Tipo,1); } ?>
			</td>
		</tr>
		<?php } ?>
		<!-- Fim do Erro -->
		<!-- Corpo -->
		<tr>
			<td width="100"></td>
			<td class="textonormal">
				<table border="0" width="100%" cellspacing="0" cellpadding="3" summary="">
					<tr>
						<td class="textonormal">
							<table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" bgcolor="#FFFFFF">
								<input type="hidden" name="ProgramaOrigem" value="<?php echo $ProgramaOrigem; ?>">
								<tr>
									<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3" colspan="5">
										CONSULTA - MATERIAIS/SERVIÇOS
									</td>
								</tr>
								<tr>
									<td class="textonormal" colspan="5">
										<p align="justify">
											Para pesquisar um item já cadastrado, preencha o argumento da pesquisa e clique no botão "Pesquisar".
											Depois, clique no material/serviço desejado.
										</p>
									</td>
								</tr>
								<tr>
									<td align="center" bgcolor="#DCEDF7" class="titulo3" colspan="5">PESQUISA DIRETA DE MATERIAL/SERVIÇO</td>
								</tr>
								<tr>
									<td colspan="5">
										<table border="0" width="100%" summary="">
											<tr>
												<td class="textonormal" bgcolor="#DCEDF7" width="31%">Subclasse</td>
												<td class="textonormal" colspan="2">
													<select name="OpcaoPesquisaSubClasse" class="textonormal">
														<option value="0">Código Reduzido</option>
														<option value="1">Descrição contendo</option>
														<option value="2">Descrição iniciada por</option>
													</select>
													<input type="text" name="SubclasseDescricaoDireta" size="10" maxlength="10" class="textonormal" onFocus="javascript:document.ConsMaterialSelecionar.ServicoDescricaoDireta.value = '';document.ConsMaterialSelecionar.MaterialDescricaoDireta.value = '';document.ConsMaterialSelecionar.TipoGrupo.value = 'M';">
													<a href="javascript:validapesquisa();"><img src="../midia/lupa.gif" border="0"></a>
												</td>
											</tr>
											<tr>
												<td class="textonormal" bgcolor="#DCEDF7">Material</td>
												<td class="textonormal" colspan="2">
													<select name="OpcaoPesquisaMaterial" class="textonormal">
														<option value="0">Código Reduzido</option>
														<option value="1">Descrição contendo</option>
														<option value="2">Descrição iniciada por</option>
													</select>
													<input type="text" name="MaterialDescricaoDireta" size="10" maxlength="10" class="textonormal" onFocus="javascript:document.ConsMaterialSelecionar.ServicoDescricaoDireta.value = '';document.ConsMaterialSelecionar.SubclasseDescricaoDireta.value = '';document.ConsMaterialSelecionar.TipoGrupo.value = 'M';">
													<a href="javascript:validapesquisa();"><img src="../midia/lupa.gif" border="0"></a>
												</td>
											</tr>
											<tr>
								              	<td class="textonormal" bgcolor="#DCEDF7" width="34%">Serviço</td>
								              	<td class="textonormal" colspan="2">
								              		<select name="OpcaoPesquisaServico" class="textonormal">
								              			<option value="0">Código Reduzido</option>
								              			<option value="1">Descrição contendo</option>
								              			<option value="2">Descrição iniciada por</option>
								              		</select>
							         	        	<input type="text" name="ServicoDescricaoDireta" size="10" maxlength="10" class="textonormal" onFocus="javascript:document.ConsMaterialSelecionar.MaterialDescricaoDireta.value = '';document.ConsMaterialSelecionar.SubclasseDescricaoDireta.value = '';document.ConsMaterialSelecionar.TipoGrupo.value = 'S';">
							           	      		<a href="javascript:validapesquisa();"><img src="../midia/lupa.gif" border="0" alt="0"></a>
								              	</td>
								            </tr>
										</table>
									</td>
								</tr>
								<tr>
									<td align="center" bgcolor="#DCEDF7" class="titulo3" colspan="5">PESQUISA POR FAMILIA - MATERIAL/SERVIÇO</td>
								</tr>
								<tr>
									<td colspan="5">
										<table border="0" cellpadding="0" cellspacing="0" bordercolor="#75ADE6" width="100%" summary="">
											<tr>
												<td colspan="5">
													<table class="textonormal" border="0" width="100%" summary="">
														<tr>
												          	<td class="textonormal" bgcolor="#DCEDF7" width="34%">Tipo de Grupo</td>
												          	<td class="textonormal">
												          		<input type="radio" name="TipoGrupo" value="M" onClick="javascript:document.ConsMaterialSelecionar.MaterialDescricaoDireta.value='';document.ConsMaterialSelecionar.SubclasseDescricaoDireta.value='';document.ConsMaterialSelecionar.ServicoDescricaoDireta.value='';document.ConsMaterialSelecionar.submit();" <?php if( $TipoGrupo == "M" ){ echo "checked"; }?> > Material
												          		<input type="radio" name="TipoGrupo" value="S" onClick="javascript:document.ConsMaterialSelecionar.MaterialDescricaoDireta.value='';document.ConsMaterialSelecionar.SubclasseDescricaoDireta.value='';document.ConsMaterialSelecionar.ServicoDescricaoDireta.value='';document.ConsMaterialSelecionar.submit();" <?php if( $TipoGrupo == "S" ){ echo "checked"; }?> > Serviço
												          	</td>
												        </tr>
												        <?php if ($TipoGrupo == "M") { ?>
														<tr>
															<td class="textonormal" bgcolor="#DCEDF7" height="20">Tipo de Material</td>
															<td class="textonormal">
																<input type="radio" name="TipoMaterial" value="C" onClick="javascript:document.ConsMaterialSelecionar.submit();" <?php if( $TipoMaterial == "C" ){ echo "checked"; } ?> /> Consumo
																<input type="radio" name="TipoMaterial" value="P" onClick="javascript:document.ConsMaterialSelecionar.submit();" <?php if( $TipoMaterial == "P" ){ echo "checked"; } ?> /> Permanente
															</td>
														</tr>
														<?php } ?>
														<?php if ($TipoGrupo != "") { ?>
														<tr>
															<td class="textonormal" bgcolor="#DCEDF7" height="20">Grupo</td>
															<td class="textonormal">
																<select name="Grupo" onChange="javascript:remeter();" class="textonormal">
																	<option value="">Selecione um Grupo...</option>
																	<?php	$db = Conexao();
											                	  			# Mostra os grupos cadastrados #
																			if (($TipoGrupo == "M" and ($TipoMaterial == "C" or $TipoMaterial == "P")) or $TipoGrupo == "S") {
																				$sql = "SELECT	CGRUMSCODI,EGRUMSDESC
																						FROM	SFPC.TBGRUPOMATERIALSERVICO
																						WHERE	FGRUMSTIPO = '$TipoGrupo'
																								AND FGRUMSSITU = 'A' ";
																				if ($TipoGrupo == "M" and $TipoMaterial != "") {
																					$sql .= " AND FGRUMSTIPM = '$TipoMaterial' ";
																				}

																				$sql .= " ORDER BY EGRUMSDESC";

													                			$res = $db->query($sql);
																			
																				if (db::isError($res)) {
																					ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																				} else {
																					while ($Linha = $res->fetchRow()) {
															          	      			$Descricao = truncarTexto($Linha[1],$maxTamanhoTexto);
																						
																						if ($Linha[0] == $Grupo) {
																				    		echo"<option value=\"$Linha[0]\" selected>$Descricao</option>\n";
																		      	    	} else {
																				    		echo"<option value=\"$Linha[0]\">$Descricao</option>\n";
																		      	    	}
																	      	    	}
																            	}
													  	            		} ?>
																</select>
															</td>
														</tr>
														<?php	}
																if ($Grupo != "") { ?>
														<tr>
															<td class="textonormal" bgcolor="#DCEDF7">Classe </td>
															<td class="textonormal">
																<select name="Classe" class="textonormal" onChange="javascript:remeter();">
																	<option value="">Selecione uma Classe...</option>
																		<?php	if ($Grupo != "") {
																					$db   = Conexao();
																					$sql = "SELECT	CCLAMSCODI, ECLAMSDESC
																							FROM	SFPC.TBCLASSEMATERIALSERVICO
																							WHERE	CGRUMSCODI = $Grupo
																									AND FCLAMSSITU = 'A' 
																							ORDER BY ECLAMSDESC";
																				
																					$res  = $db->query($sql);
																					
																					if (db::isError($res)) {
																						ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																					} else {
																						while ($Linha = $res->fetchRow()) {
																							$Descricao = substr($Linha[1],0,75);
																								
																							if ($Linha[0] == $Classe) {
																								echo"<option value=\"$Linha[0]\" selected>$Descricao</option>\n";
																							} else {
																								echo"<option value=\"$Linha[0]\">$Descricao</option>\n";
																							}
																						}
																					}
																					$db->disconnect();
																				} ?>
																</select>
															</td>
														</tr>
														<?php	}
																if ($Grupo != "" and $Classe != "" and $TipoGrupo == "M") { //Apenas para Material ?>
														<tr>
															<td class="textonormal" bgcolor="#DCEDF7" height="20">Subclasse</td>
															<td class="textonormal">
																<select name="Subclasse" onChange="javascript:remeter();" class="textonormal">
																	<option value="0">Selecione uma Subclasse...</option>
																	<?	$db  = Conexao();
																		$sql = "SELECT	CSUBCLSEQU,ESUBCLDESC
																				FROM	SFPC.TBSUBCLASSEMATERIAL
																				WHERE	CGRUMSCODI = '$Grupo'
																						AND CCLAMSCODI = '$Classe'
																						AND FSUBCLSITU = 'A' 
																				ORDER BY ESUBCLDESC ";
																	
																		$result = $db->query($sql);
																	
																		if ( db::isError($result)) {
																			ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																		} else {
																			while ($Linha = $result->fetchRow()) {
																				$Descricao   = substr($Linha[1],0,75);
																				
																				if ($Linha[0] == $Subclasse) {
																					echo"<option value=\"$Linha[0]\" selected>$Descricao</option>\n";
																				} else {
																					echo"<option value=\"$Linha[0]\">$Descricao</option>\n";
																				}
																			}
																		} ?>
																</select>
																<input type="text" name="MaterialServicoDescricaoFamilia" size="10" maxlength="10" class="textonormal">
																<a href="javascript:validapesquisa();"><img src="../midia/lupa.gif" border="0"></a>
															</td>
														</tr>
														<?php } ?>
													</table>
												</td>
											</tr>
										</table>
									</td>
								</tr>
								<tr>
									<td class="textonormal" colspan="5">
										<table width="100%">
											<tr>
												<td class="textonormal">
													<input type="checkbox" class="textonormal" name="Inativos" value="S" <?php if( $Inativos == 'S' ){ echo "checked";}?> /> Mostrar materiais/serviços inativos
												</td>
											</tr>
											<tr>
												<td class="textonormal">
													<input type="checkbox" class="textonormal" name="Sustentaveis" value="S" <?php if( $Sustentaveis == 'S' ){ echo "checked";}?> /> Só exibir itens sustentáveis
												</td>
												<td class="textonormal" align="right">
													<input type="button" name="Limpar" value="Limpar" onClick="javascript:enviar('Limpar');" class="botao">
													<input type="hidden" name="Botao" value="">
												</td>
											</tr>
										</table>
									</td>
								</tr>
								<?php	if ($MaterialDescricaoDireta != "") {
											if ($OpcaoPesquisaMaterial == 0) {
												if (!SoNumeros($MaterialDescricaoDireta)) {
													$sqlgeral = "";
												}
											}
										}
										
										if ($SubclasseDescricaoDireta != "") {
											if ($OpcaoPesquisaSubClasse == 0) {
												if (!SoNumeros($SubclasseDescricaoDireta)) {
													$sqlgeral = "";
												}
											}
										}
								
										if ($sqlgeral != "" and $Mens == 0) {
											if ((($MaterialDescricaoDireta != "" or $SubclasseDescricaoDireta != "") or ($Subclasse != "") or ($MaterialServicoDescricaoFamilia != "") or ($ChkSubclasse != ""))//Validação para Material
						   					or ( $ServicoDescricaoDireta != "" or ($TipoGrupo == 'S' and ($Classe != 0 or $ChkClasse != "")) ) //Validação para Serviço
											) {
												$db     = Conexao();

												$res    = $db->query($sqlgeral);
												
												if (db::isError($res)) {
													ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlgeral");
												} else {
													$qtdres = $res->numRows();
													
													echo "<tr>\n";
													echo "  <td align=\"center\" bgcolor=\"#75ADE6\" colspan=\"5\" class=\"titulo3\">RESULTADO DA PESQUISA</td>\n";
													echo "</tr>\n";
													
													if ($qtdres > 0) {
														$TipoMaterialAntes  = "";
														$GrupoAntes         = "";
														$ClasseAntes        = "";
														$SubClasseAntes     = "";
														$SubClasseSequAntes = "";
														$irow               = 1;
														
														while ($row = $res->fetchRow()) {
															$GrupoCodigo                   = $row[0];
															$GrupoDescricao                = $row[1];
															$ClasseCodigo                  = $row[2];
															$ClasseDescricao               = $row[3];
															$TipoGrupoBanco                = $row[4];
															$CodRedMaterialServicoBanco    = $row[5];
															$DescricaoMaterialServicoBanco = $row[6];

															if (($TipoGrupo == "M" or $MaterialDescricaoDireta != "" or $SubclasseDescricaoDireta != "") and $ServicoDescricaoDireta == "") {
																$SubClasseSequ      = $row[7];
																$SubClasseDescricao = $row[8];
																$UndMedidaSigla     = $row[9];
																$TipoMaterialCodigo = $row[10];
																$Situacao           = $row[11];
															} else {
																$Situacao = $row[7];
															}

															if ($TipoGrupoBanco == "M" and $TipoMaterialAntes != $TipoMaterialCodigo) {
																echo "<tr>\n";
																echo "  <td class=\"textoabason\" bgcolor=\"#BFDAF2\" colspan=\"5\" align=\"center\">";
																	
																if ($TipoMaterialCodigo == "C") {
																	echo "CONSUMO";
																} else {
																	echo "PERMANENTE";
																}
																	
																echo "  </td>\n";
																echo "</tr>\n";
															}
															
															if ($GrupoAntes != $GrupoDescricao) {
																if ($ClasseAntes != $ClasseDescricao) {
																	echo "<tr>\n";
																	echo "  <td class=\"textoabason\" bgcolor=\"#DDECF9\" colspan=\"5\" align=\"center\">$GrupoDescricao / $ClasseDescricao</td>\n";
																	echo "</tr>\n";
																}
															} else {
																if ($ClasseAntes != $ClasseDescricao) {
																	echo "<tr>\n";
																	echo "  <td class=\"textoabason\" bgcolor=\"#DDECF9\" colspan=\"5\" align=\"center\">$GrupoDescricao / $ClasseDescricao</td>\n";
																	echo "</tr>\n";
																}
															}

															if ($TipoGrupoBanco == "M") {
																$Descricao = "Material";
															} else {
																$Descricao = "Serviço";
															}

															if ($ClasseAntes != $ClasseDescricao) {
																echo "<tr>\n";

																if ($TipoGrupoBanco == "M") {
																	echo "  <td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"35%\">SUBCLASSE</td>\n";
																}

																echo "  <td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"50%\">DESCRIÇÃO DO ".strtoupper2($Descricao)."</td>\n";
																echo "  <td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"5%\" align=\"center\">CÓD.RED.</td>\n";

																if ($TipoGrupoBanco == "M") {
																	echo "  <td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"10%\" align=\"center\">UNIDADE</td>\n";
																}
																echo "  <td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"5%\" align=\"center\">SITUAÇÃO</td>\n";
																echo "</tr>\n";
															}
															echo "<tr>\n";
															
															if ($TipoGrupoBanco == "M" and ($SubClasseAntes != $SubClasseDescricao or $SubClasseSequAntes != $SubClasseSequ)) {
																$flg = "S";
																
																echo "  <td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" width=\"30%\">\n";
																echo "    $SubClasseDescricao";
																echo "  </td>\n";
															}

															if ($flg == "S") {
																echo "	<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" width=\"60%\">\n";
																
																$Url = "../estoques/CadItemDetalhe.php?Material=$CodRedMaterialServicoBanco&TipoGrupo=$TipoGrupoBanco&ProgramaOrigem=ConsMaterialSelecionar";
																
																echo "		<a href=\"javascript:AbreJanela('$Url',700,340);\"><font color=\"#000000\">$DescricaoMaterialServicoBanco</font></a>";
																echo "	</td>\n";
																echo "  <td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" align=\"center\" width=\"30%\">\n";
																echo "    $CodRedMaterialServicoBanco";
																echo "  </td>\n";
																echo "	<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" align=\"center\"  width=\"10%\">\n";
																echo "		$UndMedidaSigla";
																echo "	</td>\n";
																
																$flg = "";
															} else {
																if ($TipoGrupoBanco == "M") {
																	echo "	<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" width=\"30%\">\n";
																	echo "		&nbsp;";
																	echo "	</td>\n";
																}

																echo "	<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" width=\"60%\">\n";
																
																$Url = "../estoques/CadItemDetalhe.php?Material=$CodRedMaterialServicoBanco&TipoGrupo=$TipoGrupoBanco&ProgramaOrigem=ConsMaterialSelecionar";
																
																echo "		<a href=\"javascript:AbreJanela('$Url',700,340);\"><font color=\"#000000\">$DescricaoMaterialServicoBanco</font></a>";
																echo "	</td>\n";
																echo "  <td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\"  align=\"center\" width=\"30%\">\n";
																echo "    $CodRedMaterialServicoBanco";
																echo "  </td>\n";

																if ($TipoGrupoBanco == "M") {
																	echo "	<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" align=\"center\"  width=\"10%\">\n";
																	echo "	  $UndMedidaSigla";
																	echo "	</td>\n";
																}
															}

															if ($Situacao == 'A') {
																$DescSituacao = 'ATIVO';
															} else {
																$DescSituacao = 'INATIVO';
															}

															echo "  <td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\"  align=\"center\" width=\"30%\">\n";
															echo "    $DescSituacao";
															echo "  </td>\n";

															if (!in_array($Url,$_SESSION['GetUrl'])) {
																$_SESSION['GetUrl'][] = $Url;
															}
															
															echo "</tr>\n";
															
															$TipoMaterialAntes  = $TipoMaterialCodigo;
															$GrupoAntes         = $GrupoDescricao;
															$ClasseAntes        = $ClasseDescricao;
															$SubClasseAntes     = $SubClasseDescricao;
															$SubClasseSequAntes = $SubClasseSequ;
															$SituacaoAntes      = $Situacao;
														}
														$db->disconnect();
													} else {
														echo "<tr>\n";
														echo "	<td valign=\"top\" colspan=\"5\" class=\"textonormal\" bgcolor=\"FFFFFF\">\n";
														echo "		Pesquisa sem Ocorrências.\n";
														echo "	</td>\n";
														echo "</tr>\n";
													}
												}
											}
										} ?>
							</table>
						</td>
					</tr>
				</table>
			</td>
		</tr>
		<!-- Fim do Corpo -->
	</table>
</form>
<script language="javascript" type="">
window.focus();
//-->
</script>
</body>
</html>

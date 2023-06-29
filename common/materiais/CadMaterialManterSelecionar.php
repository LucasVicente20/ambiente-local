<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadMaterialManterSelecionar.php
# Autor:    Roberta Costa/Altamiro
# Data:     04/08/2005
# Objetivo: Programa de Manutenção de Cadastro de Material
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------
# Alterado: Álvaro Faria
# Data:     29/08/2006 - Opção de pesquisa de material com descrição "iniciada por"
#-------------------------------------------------------------------------
# Alterado: Wagner Barros
# Data:     02/10/2006 - Exibir o código reduzido do material ao lado da descrição"
#-------------------------------------------------------------------------
# Alterado: Ariston Cordeiro
# Data:     02/01/2009 - permitir que descrição procure por textos dentro de palavras
#-------------------------------------------------------------------------
# Alterado: Ariston Cordeiro
# Data:     02/04/2009 - CR1418- procurar pela descrição detalhada, ao invés da descrição resumida
#-------------------------------------------------------------------------
# Alterado: Rodrigo Melo
# Data:     03/09/2009 - Alteração para inserir o cadastro de serviços
#-------------------------------------------------------------------------
# Alterado: Lucas Baracho
# Data:     13/08/2018
# Objetivo: Tarefa Redmine 95882
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/materiais/CadMaterialAlterar.php' );

# Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD'] == "POST") {
	$Botao                           = $_POST['Botao'];
	$TipoMaterial                    = $_POST['TipoMaterial'];
	$TipoGrupo	                     = $_POST['TipoGrupo'];
	$Grupo                           = $_POST['Grupo'];
	$Classe                          = $_POST['Classe'];
	$Subclasse                       = $_POST['Subclasse'];
	$MaterialServico                 = $_POST['MaterialServico'];
	$ChkMaterial                     = $_POST['chkmaterial'];
	$MaterialServicoDescricaoFamilia = strtoupper2(trim($_POST['MaterialServicoDescricaoFamilia']));
	$OpcaoPesquisaMaterial           = $_POST['OpcaoPesquisaMaterial'];
	$OpcaoPesquisaServico            = $_POST['OpcaoPesquisaServico'];
	$OpcaoPesquisaSubClasse          = $_POST['OpcaoPesquisaSubClasse'];
	$SubclasseDescricaoDireta        = strtoupper2(trim($_POST['SubclasseDescricaoDireta']));
	$MaterialDescricaoDireta         = strtoupper2(trim($_POST['MaterialDescricaoDireta']));
	$ServicoDescricaoDireta          = strtoupper2(trim($_POST['ServicoDescricaoDireta']));
} else {
	$Mensagem = urldecode($_GET['Mensagem']);
	$Mens     = $_GET['Mens'];
	$Tipo     = $_GET['Tipo'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

# Máximo tamanho de um texto que aparece numa caixa de texto ou combo box
$maxTamanhoTexto = 90;

# Sql para montagem da janela #
if (($TipoGrupo == "M" or $MaterialDescricaoDireta != "" or $SubclasseDescricaoDireta != "") and $ServicoDescricaoDireta == "") {
	$sql = "SELECT MAT.CMATEPSEQU, MAT.EMATEPDESC, GRU.FGRUMSTIPO, MAT.CMATEPSITU "; //PARA MATERIAL
} else {
	$sql = "SELECT SERV.CSERVPSEQU, SERV.ESERVPDESC, GRU.FGRUMSTIPO, SERV.CSERVPSITU "; //PARA SERVIÇO
}

$from  = " FROM SFPC.TBGRUPOMATERIALSERVICO GRU ";
$from .= " INNER JOIN SFPC.TBCLASSEMATERIALSERVICO CLA ON CLA.FCLAMSSITU = 'A' AND CLA.CGRUMSCODI = GRU.CGRUMSCODI AND GRU.FGRUMSSITU = 'A' ";

if (($TipoGrupo == "M" or $MaterialDescricaoDireta != "" or $SubclasseDescricaoDireta != "") and $ServicoDescricaoDireta == "") { //PARA MATERIAL
	$from .= " INNER JOIN SFPC.TBSUBCLASSEMATERIAL SUB ON SUB.FSUBCLSITU = 'A' AND SUB.CCLAMSCODI = CLA.CCLAMSCODI AND SUB.CGRUMSCODI = CLA.CGRUMSCODI ";
    $from .= " INNER JOIN SFPC.TBMATERIALPORTAL MAT ON MAT.CSUBCLSEQU = SUB.CSUBCLSEQU ";
} else { //PARA SERVIÇO
	$from   .= " INNER JOIN SFPC.TBSERVICOPORTAL SERV ON SERV.CCLAMSCODI = CLA.CCLAMSCODI AND SERV.CGRUMSCODI = CLA.CGRUMSCODI ";
}

$where = " WHERE 1 = 1 ";

if (($TipoGrupo == "M" or $MaterialDescricaoDireta != "" or $SubclasseDescricaoDireta != "") and $ServicoDescricaoDireta == "") { //PARA MATERIAL
	$order  = " ORDER BY SUB.ESUBCLDESC, TRANSLATE(MAT.EMATEPDESC,'ÃÕÁÉÍÓÚÀÂÊÔÜÇ','AOAEIOUAAEOUC') ";
} else { //PARA SERVIÇO
	$order  = " ORDER BY CLA.ECLAMSDESC, TRANSLATE(SERV.ESERVPDESC,'ÃÕÁÉÍÓÚÀÂÊÔÜÇ','AOAEIOUAAEOUC') ";
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
	$where .= " AND SUB.CSUBCLSEQU = $Subclasse ";
}

# Verifica se o Material foi escolhido #
if ($MaterialServico != "" and $MaterialServico != 0 and $MaterialDescricaoDireta == "" and $SubclasseDescricaoDireta == "" and $ServicoDescricaoDireta == "") {
	if ($TipoGrupo == "M") { //Material
		$where .= " AND MAT.CMATEPSEQU = $MaterialServico ";
	} else {
		$where .= " AND SERV.CSERVPSEQU = $MaterialServico ";
	}
}

# Se foi digitado algo na caixa de texto do material em pesquisa familia #
if ($MaterialServicoDescricaoFamilia != "" and $MaterialDescricaoDireta == "" and $SubclasseDescricaoDireta == "" and $ServicoDescricaoDireta == "") {
	$where .= " AND ( ";

	if ($TipoGrupo == "M") {
		$where .= "      TRANSLATE(MAT.EMATEPDESC,'ÃÕÁÉÍÓÚÀÂÊÔÜÇ','AOAEIOUAAEOUC') ILIKE '%".strtoupper2(RetiraAcentos($MaterialServicoDescricaoFamilia))."%' ";
	} else {
		$where .= "      TRANSLATE(SERV.ESERVPDESC,'ÃÕÁÉÍÓÚÀÂÊÔÜÇ','AOAEIOUAAEOUC') ILIKE '%".strtoupper2(RetiraAcentos($MaterialServicoDescricaoFamilia))."%' ";
	}

	$where .= "     )";
}

# Se foi digitado algo na caixa de texto do material em pesquisa direta #
if ($MaterialDescricaoDireta != "" and $SubclasseDescricaoDireta == "" and $ServicoDescricaoDireta == "") {
	if ($OpcaoPesquisaMaterial == 0) {
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

# Se foi digitado algo na caixa de texto da subclasse em pesquisa direta #
if ($SubclasseDescricaoDireta != "" and $MaterialDescricaoDireta == "" and $ServicoDescricaoDireta == "") {
	if ($OpcaoPesquisaSubClasse == 0) {
		if (SoNumeros($SubclasseDescricaoDireta)) {
			$where .= " AND SUB.CSUBCLSEQU = '$SubclasseDescricaoDireta' ";
		}
	} elseif ($OpcaoPesquisaSubClasse == 1) {
		$where .= " AND TRANSLATE(SUB.ESUBCLDESC,'ÃÕÁÉÍÓÚÀÂÊÔÜÇ','AOAEIOUAAEOUC') ILIKE '%".strtoupper2(RetiraAcentos($SubclasseDescricaoDireta))."%' ";
	} else {
		$where .= " AND TRANSLATE(SUB.ESUBCLDESC,'ÃÕÁÉÍÓÚÀÂÊÔÜÇ','AOAEIOUAAEOUC') ILIKE '".strtoupper2(RetiraAcentos($SubclasseDescricaoDireta))."%' ";
	}
}

# Gera o SQL com a concatenação das variaveis $sql,$from,$where #
$sqlgeral = $sql.$from.$where.$order;

if ($Botao == "Limpar") {
	header("location: CadMaterialManterSelecionar.php");
	exit;
} elseif ($Botao == "Validar") {
	# Critica dos Campos #
	$Mens     = 0;
	$Mensagem = "Informe: ";
	
	if ($MaterialDescricaoDireta != "" and $OpcaoPesquisaMaterial == 0 and ! SoNumeros($MaterialDescricaoDireta)) {
		if ($Mens == 1) {
			$Mensagem .= ", ";
		}
		
		$Mens = 1;
		$Tipo = 2;
		$Mensagem .= "<a href=\"javascript:document.CadMaterialManterSelecionar.MaterialDescricaoDireta.focus();\" class=\"titulo2\">O código reduzido do Material</a>";
	} elseif ($MaterialDescricaoDireta != "" and ($OpcaoPesquisaMaterial == 1 or $OpcaoPesquisaMaterial == 2) and strlen($MaterialDescricaoDireta)< 2) {
		if ($Mens == 1) {
			$Mensagem .= ", ";
		}
		
		$Mens = 1;
		$Tipo = 2;
		$Mensagem .= "<a href=\"javascript:document.CadMaterialManterSelecionar.MaterialDescricaoDireta.focus();\" class=\"titulo2\">Descrição com no mínimo 2 caracteres</a>";
	}
	
	if ($SubclasseDescricaoDireta != "" and $OpcaoPesquisaSubClasse == 0 and ! SoNumeros($SubclasseDescricaoDireta)) {
		if ($Mens == 1) {
			$Mensagem .= ", ";
		}
		
		$Mens = 1;
		$Tipo = 2;
		$Mensagem .= "<a href=\"javascript:document.CadMaterialManterSelecionar.SubclasseDescricaoDireta.focus();\" class=\"titulo2\">O código reduzido da Subclasse</a>";
	} elseif ($SubclasseDescricaoDireta != "" and ($OpcaoPesquisaSubClasse == 1 or $OpcaoPesquisaSubClasse == 2) and strlen($SubclasseDescricaoDireta)< 2) {
		if ($Mens == 1) {
			$Mensagem .= ", ";
		}
		
		$Mens = 1;
		$Tipo = 2;
		$Mensagem .= "<a href=\"javascript:document.CadMaterialManterSelecionar.SubclasseDescricaoDireta.focus();\" class=\"titulo2\">Descrição com no mínimo 2 caracteres</a>";
	}

	if ($ServicoDescricaoDireta != "" and $OpcaoPesquisaServico == 0 and ! SoNumeros($ServicoDescricaoDireta)) {
		if ($Mens == 1) {
			$Mensagem .= ", ";
		}
		
		$Mens = 1;
		$Tipo = 2;
		$Mensagem .= "<a href=\"javascript:document.CadMaterialManterSelecionar.ServicoDescricaoDireta.focus();\" class=\"titulo2\">O código reduzido do Serviço</a>";
	} elseif ($ServicoDescricaoDireta != "" and ($OpcaoPesquisaServico == 1 or $OpcaoPesquisaServico == 2) and strlen($ServicoDescricaoDireta)< 2) {
		if ($Mens == 1) {
			$Mensagem .= ", ";
		}
		
		$Mens = 1;
		$Tipo = 2;
		$Mensagem .= "<a href=\"javascript:document.CadMaterialManterSelecionar.MaterialDescricaoDireta.focus();\" class=\"titulo2\">Descrição com no mínimo 2 caracteres</a>";
	}
}

?>
<html>
<?php 	# Carrega o layout padrão #
	layout();?>
<script language="javascript" type="">
	<!--
	function enviar(valor) {
		document.CadMaterialManterSelecionar.Botao.value = valor;
		document.CadMaterialManterSelecionar.submit();
	}

	function checktodos() {
		document.CadMaterialManterSelecionar.MaterialServico.value = '';
		document.CadMaterialManterSelecionar.submit();
	}
	
	function remeter() {
		document.CadMaterialManterSelecionar.submit();
	}
	
	function validapesquisa() {
		if ((document.CadMaterialManterSelecionar.MaterialDescricaoDireta.value != '') || (document.CadMaterialManterSelecionar.ServicoDescricaoDireta.value != '') || (document.CadMaterialManterSelecionar.SubclasseDescricaoDireta.value != '')) {
			if (document.CadMaterialManterSelecionar.Grupo) {
	   			document.CadMaterialManterSelecionar.Grupo.value = '';
	 		}
			
			if (document.CadMaterialManterSelecionar.Classe) {
	 	  		document.CadMaterialManterSelecionar.Classe.value = '';
	 		}
			
			if (document.CadMaterialManterSelecionar.Botao) {
  		 		document.CadMaterialManterSelecionar.Botao.value = 'Validar';
  			}
		}

		if (document.CadMaterialManterSelecionar.MaterialServicoDescricaoFamilia) {
	  		if (document.CadMaterialManterSelecionar.MaterialServicoDescricaoFamilia.value != '') {
   				document.CadMaterialManterSelecionar.MaterialServico.value = '';
  			}
		}
		document.CadMaterialManterSelecionar.submit();
	}
	<?php  MenuAcesso(); ?>
	//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
	<script language="JavaScript" src="../menu.js"></script>
	<script language="JavaScript">Init();</script>
	<form action="CadMaterialManterSelecionar.php" method="post" name="CadMaterialManterSelecionar">
		<br><br><br><br><br>
		<table cellpadding="3" border="0" width="100%" summary="">
  		<!-- Caminho -->
  		<tr>
    		<td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
    		<td align="left" class="textonormal" colspan="3">
      			<font class="titulo2">|</font>
      			<a href="../index.php"><font color="#000000">Página Principal</font></a> > Materiais/Serv > Cadastro > Manter
    		</td>
  		</tr>
  		<!-- Fim do Caminho-->
		<!-- Erro -->
		<?php  if ( $Mens == 1 ) {?>
		<tr>
	  		<td width="150"></td>
	  		<td align="left" colspan="3"><?php  ExibeMens($Mensagem,$Tipo,1); ?></td>
		</tr>
		<?php  } ?>
		<!-- Fim do Erro -->
		<!-- Corpo -->
		<tr>
			<td width="150"></td>
			<td class="textonormal">
				<table width="100%" border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" class="textonormal"  bgcolor="#FFFFFF" summary="">
	        <tr>
    	      	<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3" colspan="3">
	    	       MANTER - CADASTRO DE MATERIAIS/SERVIÇOS
          		</td>
        	</tr>
        	<tr>
          		<td class="textonormal" colspan="3">
             		<p align="justify">
             			Para atualizar/excluir um Material/Serviço já cadastrado, selecione os dados abaixo para efetuar a pesquisa.<br/><br/>
             			Observação: A descrição de material a ser procurada será a descrição completa do material, e não a descrição resumida.
             		</p>
          		</td>
        	</tr>
        	<tr>
           		<td align="center" bgcolor="#DCEDF7" class="titulo3" colspan="3">PESQUISA DIRETA DE MATERIAL/SERVIÇO</td>
        	</tr>
        	<tr>
          		<td colspan="3">
            		<table border="0" width="100%" summary="">
      					<tr>
        					<td class="textonormal" bgcolor="#DCEDF7" width="31%">Subclasse</td>
        					<td class="textonormal" colspan="2">
        						<select name="OpcaoPesquisaSubClasse" class="textonormal">
        							<option value="0">Código Reduzido</option>
        							<option value="1">Descrição contendo</option>
        							<option value="2">Descrição iniciada por</option>
        						</select>
 	        					<input type="text" name="SubclasseDescricaoDireta" size="10" maxlength="10" class="textonormal" onFocus="javascript:document.CadMaterialManterSelecionar.ServicoDescricaoDireta.value = '';document.CadMaterialManterSelecionar.MaterialDescricaoDireta.value = '';">
           	      				<a href="javascript:validapesquisa();"><img src="../midia/lupa.gif" border="0" alt="0"></a>
        					</td>
        				</tr>
	            		<tr>
	              			<td class="textonormal" bgcolor="#DCEDF7" width="34%">Material</td>
	              			<td class="textonormal" colspan="2">
	              				<select name="OpcaoPesquisaMaterial" class="textonormal">
	              					<option value="0">Código Reduzido</option>
	              					<option value="1">Descrição contendo</option>
	              					<option value="2">Descrição iniciada por</option>
	              				</select>
         	        			<input type="text" name="MaterialDescricaoDireta" size="10" maxlength="10" class="textonormal" onFocus="javascript:document.CadMaterialManterSelecionar.ServicoDescricaoDireta.value = '';document.CadMaterialManterSelecionar.SubclasseDescricaoDireta.value = '';">
           	      				<a href="javascript:validapesquisa();"><img src="../midia/lupa.gif" border="0" alt="0"></a>
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
         	        			<input type="text" name="ServicoDescricaoDireta" size="10" maxlength="10" class="textonormal" onFocus="javascript:document.CadMaterialManterSelecionar.MaterialDescricaoDireta.value = '';document.CadMaterialManterSelecionar.SubclasseDescricaoDireta.value = '';document.CadMaterialManterSelecionar.TipoGrupo.value = 'S';">
           	      				<a href="javascript:validapesquisa();"><img src="../midia/lupa.gif" border="0" alt="0"></a>
	              			</td>
	            		</tr>
		            </table>
          		</td>
        	</tr>
        	<tr>
           		<td align="center" bgcolor="#DCEDF7" class="titulo3" colspan="3">PESQUISA POR FAMILIA - MATERIAL/SERVIÇO</td>
        	</tr>
		 		<td colspan="3">
        			<table border="0" width="100%" summary="">
		        		<tr>
		          			<td class="textonormal" bgcolor="#DCEDF7" width="34%">Tipo de Grupo</td>
		          			<td class="textonormal">
					          	<input type="radio" name="TipoGrupo" value="M" onClick="javascript:document.CadMaterialManterSelecionar.Grupo.value='';document.CadMaterialManterSelecionar.Classe.value='';document.CadMaterialManterSelecionar.MaterialDescricaoDireta.value='';document.CadMaterialManterSelecionar.SubclasseDescricaoDireta.value='';document.CadMaterialManterSelecionar.ServicoDescricaoDireta.value='';document.CadMaterialManterSelecionar.submit();" <?php if( $TipoGrupo == "M" ){ echo "checked"; }?> > Material
		          				<input type="radio" name="TipoGrupo" value="S" onClick="javascript:document.CadMaterialManterSelecionar.Grupo.value='';document.CadMaterialManterSelecionar.Classe.value='';document.CadMaterialManterSelecionar.MaterialDescricaoDireta.value='';document.CadMaterialManterSelecionar.SubclasseDescricaoDireta.value='';document.CadMaterialManterSelecionar.ServicoDescricaoDireta.value='';document.CadMaterialManterSelecionar.submit();" <?php if( $TipoGrupo == "S" ){ echo "checked"; }?> > Serviço
		          			</td>
		        		</tr>
				        <?php if ($TipoGrupo == "M") { ?>
		          		<tr>
		            		<td class="textonormal" bgcolor="#DCEDF7" width="25%">Tipo de Material</td>
		            		<td class="textonormal">
		            			<input type="radio" name="TipoMaterial" value="C" onClick="javascript:document.CadMaterialManterSelecionar.Grupo.value='';javascript:document.CadMaterialManterSelecionar.Classe.value='';document.CadMaterialManterSelecionar.submit();" <?php if( $TipoMaterial == "C" ){ echo "checked"; } ?> > Consumo
		            			<input type="radio" name="TipoMaterial" value="P" onClick="javascript:document.CadMaterialManterSelecionar.Grupo.value='';javascript:document.CadMaterialManterSelecionar.Classe.value='';document.CadMaterialManterSelecionar.submit();" <?php if( $TipoMaterial == "P" ){ echo "checked"; }?> > Permanente
		            		</td>
		          		</tr>
		        		<?php } ?>
	            		<tr>
	              			<td class="textonormal" bgcolor="#DCEDF7">Grupo </td>
	              			<td class="textonormal">
	              				<select name="Grupo" class="textonormal" onChange="javascript:remeter();">
	              					<option value="">Selecione um Grupo...</option>
	              					<?php	# Mostra os grupos cadastrados #
											if (($TipoGrupo == "M" and ($TipoMaterial == "C" or $TipoMaterial == "P")) or $TipoGrupo == "S") {
											
												$db = Conexao();
											
												$sql = "SELECT	CGRUMSCODI,EGRUMSDESC
														FROM	SFPC.TBGRUPOMATERIALSERVICO
														WHERE	FGRUMSTIPO = '$TipoGrupo'
																AND FGRUMSSITU = 'A' ";

												if ($TipoGrupo == "M" and $TipoMaterial != "") {
													$sql .= " AND FGRUMSTIPM = '$TipoMaterial' ";
												}

												$sql .= " ORDER BY EGRUMSDESC ";

		                						$res = $db->query($sql);
								
												if (PEAR::isError($res)) {
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
				  	            				$db->disconnect();
		  	            					} ?>
	              				</select>
	              			</td>
	            		</tr>
	            		<tr>
	              			<td class="textonormal" bgcolor="#DCEDF7">Classe</td>
	              			<td class="textonormal">
	              				<select name="Classe" class="textonormal" onChange="javascript:remeter();">
	              					<option value="">Selecione uma Classe...</option>
	              					<?php	if ($Grupo != "") {
			              						$db = Conexao();
												
												$sql = "SELECT	CCLAMSCODI, ECLAMSDESC
														FROM	SFPC.TBCLASSEMATERIALSERVICO
														WHERE	CGRUMSCODI = $Grupo
																AND FCLAMSSITU = 'A'
														ORDER BY ECLAMSDESC ";
												
												$res = $db->query($sql);
											  
												if (PEAR::isError($res)) {
													ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
												} else {
													while ($Linha = $res->fetchRow()) {
												
														$Descricao = truncarTexto($Linha[1],$maxTamanhoTexto);
															
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
           				<?php if( $Grupo != "" and $Classe != "" and $TipoGrupo == "M"){ ?>
              			<tr>
                			<td class="textonormal" bgcolor="#DCEDF7">Subclasse </td>
                			<td class="textonormal">
            	    			<select name="Subclasse" class="textonormal" onChange="javascript:remeter();">
            		    			<option value="">Selecione uma Subclasse...</option>
            		    				<?php	$db = Conexao();
												
												$sql = "SELECT	CSUBCLSEQU, ESUBCLDESC
														FROM	SFPC.TBSUBCLASSEMATERIAL
														WHERE	CGRUMSCODI = '$Grupo'
																AND CCLAMSCODI = '$Classe'
																AND FSUBCLSITU = 'A'
														ORDER BY ESUBCLDESC";
												
												$res = $db->query($sql);
												  
												if (PEAR::isError($res)) {
									 		 		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
												} else {
													while ($Linha = $res->fetchRow()) {
          	      										$Descricao = truncarTexto($Linha[1],$maxTamanhoTexto);
															
														if ($Linha[0] == $Subclasse) {
					    	      							echo"<option value=\"$Linha[0]\" selected>$Descricao</option>\n";
			      	      								} else {
															echo"<option value=\"$Linha[0]\">$Descricao</option>\n";
			      	      								}
	                								}
												}
              									$db->disconnect(); ?>
            					</select>
                			</td>
              			</tr>
	            		<?php	}
              					if ($Grupo != "" and $Classe != "" and (($Subclasse != "" and $TipoGrupo == "M") or $TipoGrupo == "S")) {
              						//Variáveis dinâmicas para colocar as informações para material ou serviço.
									if ($TipoGrupo == 'M') {
				   						$DescricaoMaterialServico = "Material";
									} else {
										$DescricaoMaterialServico = "Serviço";
									} ?>
         				<tr>
                			<td class="textonormal" bgcolor="#DCEDF7"><?php echo $DescricaoMaterialServico; ?> </td>
                			<td class="textonormal">
                 				<select name="MaterialServico" class="textonormal" onChange="javascript:remeter();">
             	    				<option value="">Selecione um <?php echo $DescricaoMaterialServico; ?>...</option>
            		    				<?php	$db = Conexao();
												
												if ($TipoGrupo == 'M') {
									   				$sql = "SELECT	CMATEPSEQU, EMATEPDESC
													   		FROM	SFPC.TBMATERIALPORTAL
															WHERE	CSUBCLSEQU = $Subclasse
															ORDER BY TRANSLATE(EMATEPDESC,'ÃÕÁÉÍÓÚÀÂÊÔÜÇ','AOAEIOUAAEOUC') ";
												} else {
													$sql = "SELECT	CSERVPSEQU, ESERVPDESC
															FROM	SFPC.TBSERVICOPORTAL
															WHERE	CGRUMSCODI = $Grupo
																	AND CCLAMSCODI = $Classe
															ORDER BY TRANSLATE(ESERVPDESC,'ÃÕÁÉÍÓÚÀÂÊÔÜÇ','AOAEIOUAAEOUC')";
												}

												$res = $db->query($sql);
												  
												if (PEAR::isError($res)) {
									  				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
												} else {
													while ($Linha = $res->fetchRow()) {
          	      										$Descricao = substr($Linha[1],0,40);
															
														if ($Linha[0] == $MaterialServico) {
					    	      							echo"<option value=\"$Linha[0]\" selected>$Descricao</option>\n";
			      	      								} else {
															echo"<option value=\"$Linha[0]\">$Descricao</option>\n";
			      	      								}
	                								}
												}
              									$db->disconnect(); ?>
            					</select>
         	      				<input type="text" name="MaterialServicoDescricaoFamilia" size="10" maxlength="10" onChange="javascript:remeter();" class="textonormal">
           	      					<a href="javascript:validapesquisa();"><img src="../midia/lupa.gif" border="0" alt="0"></a>
						    	<input type="checkbox" name="chkmaterial" onClick="javascript:checktodos();">Todos
            				</td>
          				</tr>
              			<?php } ?>
            		</table>
          		</td>
        	</tr>
     		<tr>
        		<td colspan="3" align="right">
	       			<input type="button" value="Limpar" class="botao" onclick="javascript:enviar('Limpar');">
					<input type="hidden" name="Botao" value="">
				</td>
      		</tr>
 			<?php	if ($MaterialDescricaoDireta != "") {
				    	if ($OpcaoPesquisaMaterial == 0) {
						    if (!SoNumeros($MaterialDescricaoDireta)) {
								$sqlgeral = "";
							}
				    	}
			  		}

			  		if ($ServicoDescricaoDireta != "") {
				    	if ($OpcaoPesquisaServico == 0) {
						    if (!SoNumeros($ServicoDescricaoDireta)) {
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
						if (($MaterialDescricaoDireta != "" or $SubclasseDescricaoDireta != "" or $ServicoDescricaoDireta != "") or ($MaterialServico != "" or $MaterialServicoDescricaoFamilia != "" or $ChkMaterial != "" )) {
	            	        $db = Conexao();
							
							$res = $db->query($sqlgeral);
							 	
	  						$qtdres = $res->numRows();
							
							if (PEAR::isError($res)) {
		  					  	ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
							} else {
								echo "			   <tr>\n";
								echo "				   <td align=\"center\" bgcolor=\"#75ADE6\" colspan=\"3\" class=\"titulo3\">RESULTADO DA PESQUISA</td>\n";
								echo "			   </tr>\n";

	  						    $qtdres      = 0;
				                $primeiravez = true;
				                                
								while ($row	= $res->fetchRow()) {
                  					$qtdres++;			  
									 
									if ($primeiravez) {
 										$primeiravez = false;
										    echo "	       <tr>\n";
									        echo "	         <td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"80%\">DESCRIÇÃO RESUMIDA DO MATERIAL</td>\n";
											echo "	         <td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"10%\" align=\"center\">CÓD.RED.</td>\n";
											echo "	         <td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"10%\" align=\"center\">SITUAÇÃO</td>\n";
											echo "	       </tr>\n";
 									}
												  
									$MaterialSequ      = $row[0];
									$MaterialDescricao = $row[1];
									$TipoGrupoBanco    = $row[2];
									$Situacao          = $row[3];
									
									echo "<tr>\n";
									echo "	<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" width=\"90%\">\n";
									
									$Url = "CadMaterialAlterar.php?MaterialServico=$MaterialSequ&TipoGrupo=$TipoGrupoBanco";
									
									if (!in_array($Url,$_SESSION['GetUrl'])) {
										$_SESSION['GetUrl'][] = $Url;
									}

									if ($Situacao == "A") {
										$DescSituacao = 'ATIVO';
									} else {
										$DescSituacao = 'INATIVO';
									}
									
									echo "		<a href=\"$Url\"><font color=\"#000000\">$MaterialDescricao</font></a>";
									echo "	</td>\n";
									echo "	<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" align=\"center\" width=\"10%\">\n";
									echo "		$MaterialSequ";
									echo "	</td>\n";
									echo "	<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" align=\"center\" width=\"10%\">\n";
									echo "		$DescSituacao";
									echo "	</td>\n";
									echo "</tr>\n";
								}
								
								$db->disconnect();
				
				            	if ($qtdres==0) {
									echo "<tr>\n";
									echo "	<td valign=\"top\" colspan=\"3\" class=\"textonormal\" bgcolor=\"FFFFFF\">\n";
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
		<!-- Fim do Corpo -->
		</table>
	</form>
</body>
</html>

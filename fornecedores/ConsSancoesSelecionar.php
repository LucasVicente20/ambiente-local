<?php
/**
 * Portal de Compras
 * 
 * Programa: ConsSancoesSelecionar.php
 * Autor:    Ariston Cordeiro
 * Data:     04/09/2008
 * Objetivo: Programa que lista fornecedores com sanções no SICREF
 * ----------------------------------------------------------------------------------------------
 * Alterado: Lucas Baracho
 * Data:     20/05/2019
 * Objetivo: Tarefa Redmine 210696
 * ----------------------------------------------------------------------------------------------
 * Alterado: Daniel Augusto
 * Data:		16/05/2023
 * Objetivo: Tarefa Redmine 282903
 * -----------------------------------------------------------------------------------------------------------------------------------------------
 */

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso ('/fornecedores/ConsSancoesSelecionar.php');
AddMenuAcesso ('/fornecedores/ConsAcompFornecedor.php');

# Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD'] == "POST") {
	$Botao        = $_POST['Botao'];
	$Todos        = $_POST['Todos'];
	$ItemPesquisa = $_POST['ItemPesquisa'];
	$Argumento	  = strtoupper2(trim($_POST['Argumento']));
	$Palavra	  = $_POST['Palavra'];
} else {
	$Mens     = $_GET['Mens'];
	$Mensagem = $_GET['Mensagem'];
	$Tipo	  = $_GET['Tipo'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

if ($Botao == "Limpar") {
	header("location: ConsSancoesSelecionar.php");
	exit;
}

if ($Botao == "Pesquisar") {
	$Mens	  = 0;
	$Mensagem = "Informe: ";
	
	if (($Argumento != "") || ($Todos)) {
		if (!$Todos) {
			if ($ItemPesquisa == "CNPJ" and ! SoNumeros($Argumento)) {
			    $Mens 	   = 1;
			    $Tipo      = 2;
				$Mensagem .= "<a href='javascript:document.CadAcompFornecedor.Argumento.focus();' class='titulo2'>CNPJ Válido</a>";
			} else {
				if (($ItemPesquisa == "CPF") and (!SoNumeros($Argumento))) {
					$Mens 	   = 1;
					$Tipo      = 2;
					$Mensagem .= "<a href='javascript:document.CadAcompFornecedor.Argumento.focus();' class='titulo2'>CPF Válido</a>";
				}
			}
		}

		$db	= Conexao();

		$result = listaFornecedoresComSancoes($db, $Argumento, $Todos, $Palavra, $ItemPesquisa);

		$db->disconnect();
	} else {
		$Mens 	   = 1;
		$Tipo      = 2;
		$Mensagem .= "<a href='javascript:document.CadAcompFornecedor.Argumento.focus();' class='titulo2'>Argumento da Pesquisa</a>";
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
		document.CadAcompFornecedor.Botao.value=valor;
		document.CadAcompFornecedor.submit();
	}
	<?php MenuAcesso(); ?>
	//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
	<script language="JavaScript" src="../menu.js"></script>
	<script language="JavaScript">Init();</script>
	<form action="ConsSancoesSelecionar.php" method="post" name="CadAcompFornecedor">
		<br><br><br><br><br>
		<table cellpadding="3" border="0" summary="">
  			<!-- Caminho -->
  			<tr>
    			<td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
    			<td align="left" class="textonormal">
      				<font class="titulo2">|</font>
      				<a href="../index.php"><font color="#000000">Página Principal</font></a> > Fornecedores > Sanções
    			</td>
  			</tr>
  			<!-- Fim do Caminho-->
			<!-- Erro -->
			<?php
			if ($Mens == 1) {
				?>
				<tr>
	  				<td width="100"></td>
	  				<td align="left">
						<?php
						if ($Mens == 1) {
							ExibeMens($Mensagem,$Tipo,$Virgula);
						}
						?>
	  				</td>
				</tr>
				<?php
			}
			?>
			<!-- Fim do Erro -->
			<!-- Corpo -->
			<tr>
				<td width="100"></td>
				<td class="textonormal">
      				<table border="0" cellspacing="0" cellpadding="3" bgcolor="#ffffff" summary="">
        				<tr>
	      					<td class="textonormal">
	        					<table border="1" cellpadding="3" cellspacing="0"  bordercolor="#75ADE6" summary="" class="textonormal" width="100%">
	          						<tr>
	            						<td align="center" bgcolor="#75ADE6" valign="middle" colspan="5" class="titulo3">
		    								ACOMPANHAMENTO DE FORNECEDORES COM SANÇÕES NO SICREF
		          						</td>
		        					</tr>
	  	      						<tr>
	    	      						<td class="textonormal" colspan="5">
	      	    							<p align="justify">
	        	    						Marque o tipo de fornecedor desejado, preencha o argumento da pesquisa e clique no botão "Pesquisar".
											Depois, clique no fornecedor desejado para proceder o acompanhamento.<br>
	        	    						Para limpar a pesquisa, clique no botão "Limpar".
	          	   							</p>
	          							</td>
	          						</tr>
 	    							<?php
									echo "<tr>\n";
									echo "	<td class='textonormal' colspan='5'>\n";
									echo "		<table border='0' cellpadding='0' cellspacing='2' class='textonormal' width='100%' summary=''>\n";
									echo "			<tr>\n";
									echo "				<td class='textonormal' bgcolor='#DCEDF7' width='30%'>Pesquisa<span style='color: red;'>*</span></td>\n";
									echo "				<td class='textonormal'>\n";
									echo "					<select name='ItemPesquisa' class='textonormal'>\n";
									echo "						<option value='RAZAO'\n";
								
									if ($ItemPesquisa == "CNPJ" or $ItemPesquisa == "") {
										echo "selected";
									}
									
									echo ">Razão Social/Nome</option>\n";
									echo "						<option value='CNPJ'\n";
									
									if ($ItemPesquisa == "CNPJ") {
										echo "selected";
									}
									
									echo ">CNPJ</option>\n";
									echo "						<option value='CPF'\n";
									
									if ($ItemPesquisa == "CPF") {
										echo "selected";
									}
									
									echo ">CPF</option>\n";
									echo "					</select>\n";
									echo "				</td>\n";
									echo "			</tr>\n";
									echo "			<tr>\n";
									echo "				<td class='textonormal' bgcolor='#DCEDF7'>Argumento<span style='color: red;'>*</span></td>\n";
									echo "				<td>\n";
									echo "					<input type='text' class='textonormal' name='Argumento' size='40' maxlength='60' value='$Argumento'>\n";
									echo "					<input type='checkbox' class='textonormal' name='Palavra' value='1'\n";
									
									if ($Palavra == 1) {
										echo "checked";
									}
									
									echo "					> Palavra Exata\n";
									echo "					<input type='checkbox' class='textonormal' name='Todos' value='1'\n";
								
									if ($Todos == 1) {
										echo "checked";
									}
									
									echo "					> Todos\n";
									echo "				</td>\n";
									echo "			</tr>\n";
									echo "		</table>\n";
									echo "	</td>\n";
									echo "</tr>\n";
									echo "<tr>\n";
									echo "	<td align='right' colspan='5'>\n";
									echo "		<input type='button' value='Pesquisar' class='botao' onclick=\"javascript:enviar('Pesquisar');\">\n";
									echo "		<input type='button' value='Limpar' class='botao' onclick=\"javascript:enviar('Limpar');\">\n";
									echo "		<input type='hidden' name='Botao' value=''>\n";
									echo "	</td>\n";
									echo "	</tr>\n";
									
									if ($Botao == "Pesquisar" and $Mens == 0) {
										# Busca os Dados da Tabela de Fornecedor de Acordo com o argumento da pesquisa #
										$Qtd = $result->numRows();
										
										Resultado($Qtd,$result,$TipoForn);
									}
									?>
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
<?php
function Resultado($Qtd,$result,$TipoForn) {
	global $ItemPesquisa, $Argumento, $Palavra;
	
	echo "<tr>\n";
	echo "	<td align='center' bgcolor='#75ADE6' colspan='5' class='titulo3'>RESULTADO DA PESQUISA</td>\n";
	echo "</tr>\n";

	if ($Qtd > 0) {
		$Sequencial	   = "";
		$SituacaoVelha ="";

		while ($Linha = $result->fetchRow()) {
			$itr++;

			$Cadastro       = $Linha[0]; // cpf ou cnpj
			$Razao	        = $Linha[1];
			$Situacao       = $Linha[2];
			$DataSituacao	= substr($Linha[3],8,2)."/".substr($Linha[3],5,2)."/".substr($Linha[3],0,4);
			$MotivoSituacao = $Linha[4];
			$DataExpiracao  = substr($Linha[5],8,2)."/".substr($Linha[5],5,2)."/".substr($Linha[5],0,4);
			$Sequencial     = $Linha[6];

			if ($Situacao != $SituacaoVelha) {
				$SituacaoVelha =$Situacao;

				echo "	<tr>\n";
				echo "	  <td valign='top' class='textoabason' bgcolor='#BFDAF2' align='center' colspan='5' class='textonormal'>$Situacao</td>\n";
				echo "	</tr>\n";
				echo "	<tr>\n";
				echo "	<td class='textoabason' bgcolor='#DCEDF7' width='1'>CNPJ/CPF</td>\n";
				echo "			<td class='textoabason' bgcolor='#DCEDF7'>RAZÃO SOCIAL/NOME</td>\n";
				echo "			<td class='textoabason' bgcolor='#DCEDF7' align='center' width='1'>DATA DA<BR/>SITUAÇÃO</td>\n";
				echo "			<td class='textoabason' bgcolor='#DCEDF7' align='center' >MOTIVO DA<BR/>SITUAÇÃO</td>\n";
				echo "			<td class='textoabason' bgcolor='#DCEDF7' align='center' width='1'>DATA DA<BR/>EXPIRAÇÃO</td>\n";
				echo "		</tr>\n";
			}

			echo "	<tr>\n";
			echo "	  <td valign='top' bgcolor='#F7F7F7' class='textonormal'><a href='ConsAcompFornecedor.php?Sequencial=$Sequencial'>$Cadastro</a></td>\n";
			echo "	  <td valign='top' bgcolor='#F7F7F7' class='textonormal'>$Razao</td>\n";
			echo "		<td valign='top' bgcolor='#F7F7F7' class='textonormal' align='center'>$DataSituacao</td>\n";
			echo "		<td valign='top' bgcolor='#F7F7F7' class='textonormal' align='left'>";

			if ($MotivoSituacao=="" or is_null($MotivoSituacao)) {
				echo "&nbsp;";
			} else {
				echo $MotivoSituacao;
			}

			echo "</td>\n";
			echo "		<td valign='top' bgcolor='#F7F7F7' class='textonormal' align='center'>";
			
			if ($DataExpiracao !=0 and $DataExpiracao != "") {
				echo $DataExpiracao;
			} else {
				echo "&nbsp;";
			}

			echo "		</td>\n";
			echo "	</tr>\n";
		}
	}

	if ($itr == 0) {
		echo "	<tr>\n";
		echo "		<td valign='top' colspan='5' class='textonormal' bgcolor='FFFFFF'>\n";
		echo "		Pesquisa sem Ocorrências.\n";
		echo "		</td>\n";
		echo "	</tr>\n";
	}

	echo "</table>\n";
}
?>
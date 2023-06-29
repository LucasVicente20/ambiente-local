<?php
// ----------------------------------------------------------------------------
// Portal da DGCO
// Programa: CadMaterialTRPAceiteExpurgoManter.php
// Objetivo: Programa de Gerenciamento de preços de licitação da TRP
// possibilitando alterar os status de validação dos preços realizados na licitação
// Autor: Igor Duarte
// Data: 06/09/2012
// -------------------------------------------------------------------------
// Alterado: Pitang Agile TI
// Data: 08/06/2015
// Objetivo: CR 73626 - Materiais > TRP > Aceite/ Expurgo - Incluir
// -------------------------------------------------------------------------
// Alterado: Pitang Agile TI
// Data: 14/03/2016
// Objetivo: Bug 126514 - Aceite / Expurgo Manter
//
require_once ("../licitacoes/funcoesComplementaresLicitacao.php");

// Acesso ao arquivo de funções #
require_once "../funcoes.php";

// Executa o controle de segurança #
session_start();
Seguranca();

// Adiciona páginas no MenuAcesso #
// Criar o submenu TRP no menu Material#
AddMenuAcesso('/materiais/CadMaterialTRPAceiteExpurgoManterDetalhe.php');

if ($_SERVER['REQUEST_METHOD'] == "GET") {
    $Mensagem = urldecode($_GET['Mensagem']);
    $Mens = $_GET['Mens'];
    $Tipo = $_GET['Tipo'];
    $Critica = $_GET['Critica'];
}

$dataHomolIni = "01/01/" . date("Y");
$dataHomolFim = date("d/m/Y");

// Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;
?>
<html>
<?php
// Carrega o layout padrão #
layout();
?>
<script language="javascript" type="">
<!--
<?php MenuAcesso(); ?>
//-->
</script>
$dataHomolIni
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
	<script language="JavaScript" src="../menu.js"></script>
	<script type="text/javascript">


</script>
	<script language="JavaScript">Init();</script>
	<form action="CadMaterialTRPAceiteExpurgoManter.php" method="post"
		name="CadMaterialTRPAceiteExpurgoSelecionar">
		<br> <br> <br> <br> <br>
		<table cellpadding="3" border="0" summary="" width="100%">
			<!-- Caminho -->
			<tr>
				<td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
				<td align="left" class="textonormal" colspan="2"><font
					class="titulo2">|</font> <a href="../index.php"><font
						color="#000000">Página Principal</font></a> > Materiais/Serviços >
					TRP > Aceite/Expurgo > Manter</td>
			</tr>
			<!-- Fim do Caminho-->

			<!-- Erro -->
	<?php if ( $Mens == 1 ) {?>
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
								<table  width="50%" border="1" cellspacing="0" cellpadding="0"
									summary="" bordercolor="#75ADE6">
									<tr>
										<td class="textonormal ">
											<table width="100%" border="0" cellpadding="3"
												cellspacing="1" summary=""
												class="textonormal">
												<tr class="">
													<td align="center" bgcolor="#75ADE6" valign="middle"
														class="titulo3" colspan="4">MANTER ACEITE/EXPURGO PREÇOS
														TRP</td>
												</tr>
												<tr class="">
													<td class="textonormal" bgcolor="#DCEDF7" height="20"
														width="15%" align="left">PERÍODO HOMOLOGAÇÃO:</td>
													<td class="textonormal " bgcolor="#ffffff">
									        DE
									              <?php $URL = "../calendario.php?Formulario=CadMaterialTRPAceiteExpurgoSelecionar&Campo=DataHomolIni";?>
																<input type="text" name="DataHomolIni" size="10"
														maxlength="10" value="<?php echo $dataHomolIni?>"
														class="textonormal"> <a
														href="javascript:janela('<?php echo $URL ?>','Calendario',220,170,1,0)"><img
															src="../midia/calendario.gif" border="0" alt=""></a>
											&nbsp; ATÉ
											       <?php $URL = "../calendario.php?Formulario=CadMaterialTRPAceiteExpurgoSelecionar&Campo=DataHomolFim";?>
																<input type="text" name="DataHomolFim" size="10"
														maxlength="10" value="<?php echo $dataHomolFim?>"
														class="textonormal"> <a
														href="javascript:janela('<?php echo $URL ?>','Calendario',220,170,1,0)"><img
															src="../midia/calendario.gif" border="0" alt=""></a>
													</td>
												</tr>
												<tr class="">
													<td class="textonormal" bgcolor="#DCEDF7" height="20"
														width="15%" align="left">TIPO VALIDAÇÃO:</td>
													<td class="textonormal" width="85%" bgcolor="#ffffff"><select
														name="tipovalidacao">
															<option value="A">ACEITE</option>
															<option value="E">EXPURGADO</option>
															<option value="T">TODOS</option>
													</select>
                                                    </td>
												</tr>
											</table>
										</td>
                                    </tr>
                                    <tr class="bgBrancoBotoes ">
													<td class="textonormal" align="right" colspan="5"><input
														class="botao" type="submit" value="Pesquisar"> <input
														class="botao" name="Botao" type="hidden" value="">
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

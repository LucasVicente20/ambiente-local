<?php
#-------------------------------------------------------------------------
# Portal da DGCO teste
# Programa: CadFiscalSelecionar.php
# Autor:    João Madson
# Data:     20/10/2021
# Objetivo: Programa de selecionar Fiscal CR#254713
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";
require_once "./funcoesContrato.php";

$Objfuncoes = new funcoesContrato();

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/contratos/CadFiscalSelecionar' );
$arrayTirar  = array('.',',','-','/');

# Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD'] == "POST") {
	$tipoFiscal = $_POST['tipofiscal'];

	// Verifica e prepara mensagem caso retorne sucesso da tela de Manter
	if ($_POST['mensagem'] == "1" && !empty($_POST['mensErro'])) {
		$mensagemSucesso = $_POST['mensErro'];
	}

	$UsuarioCodigo = $_POST;

	if (!empty($_POST['CPF'])) {
		$CPF = str_replace($arrayTirar,'',$_POST['CPF']);
	}

	//Busca os dados do fiscal
	$fiscais  = $Objfuncoes->GetFiscal($CPF, $tipoFiscal, true);

	//Caso haja busca direta por cpf o tipo pode ser diferente do marcado fazendo o fiscal vir vazio, a checagem evita isso.
	if (empty($fiscais)) {
		$fiscais  = $Objfuncoes->GetFiscal($CPF, '', true);
		$tipoFiscal = $fiscais[0]->nfiscdtipo;
	}
}

if (empty($fiscais)) {
	$fiscais  = $Objfuncoes->GetFiscal($CPF, 'INTERNO', true);
}
?>
<html>
	<?php
	# Carrega o layout padrão #
	layout();
	?>
	<script language="javascript" type="">
		<!--
		<?php MenuAcesso(); ?>
		//-->
	</script>
	<link rel="stylesheet" type="text/css" href="../estilo.css">
	<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
		<script language="JavaScript" src="../menu.js"></script>
		<script language="JavaScript">
			Init();

			$(document).ready(function() {
				$('#CPF').mask('999.999.999-99');

				$('[name=tipofiscal]').on('click', function() {
					$('#formSelectFiscal').attr('action', 'CadFiscalSelecionar.php');
					$('#formSelectFiscal').submit();
				});
		
				$('#CPF').change( function() {
					$('#formSelectFiscal').attr('action', 'CadFiscalSelecionar.php');
					$('#formSelectFiscal').submit();
				});

				$('#btnSelecFiscal').on('click', function() {
					var fiscSelec = $("[name=FiscalSelecionar]").val();
					// console.log(!empty(fiscSelec));

					if (fiscSelec == "") {
						$('#formSelectFiscal').attr('action', 'CadFiscalSelecionar.php');
						$('#formSelectFiscal').submit();
					} else {
						$('#formSelectFiscal').attr('action', 'CadFiscalManter.php');
						$('#formSelectFiscal').submit();
					}
				});
			});
		</script>
		<form action="" id="formSelectFiscal" method="post" name="fiscal">
			<br><br><br><br><br>
			<table cellpadding="3" border="0" summary="">
  				<!-- Caminho -->
  				<tr>
    				<td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
    				<td align="left" class="textonormal" colspan="2">
      					<font class="titulo2">|</font>
      					<a href="../index.php">
							<font color="#000000">Página Principal</font>
						</a> > Contratos > Fiscal > Selecionar
    				</td>
  				</tr>
  				<!-- Fim do Caminho-->
				<!-- Erro -->
				<tr>
					<td width="150"></td>
					<td align="left" colspan="2" id="tdmensagem" <?php echo !empty($mensagemSucesso)? 'style="display:block;"': "hidden";?>>
						<div class="mensagem">
							<div class="error"></div>
							<span class="mensagem-texto" <?php echo !empty($mensStylo)? $mensStylo : "";?>>
								<?php echo !empty($mensagemSucesso)? $mensagemSucesso : "";?>
							</span>
						</div>
					</td>
				</tr>
				<!-- Fim do Erro -->
				<!-- Corpo -->
				<tr>
					<td width="150"></td>
					<table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal"  bgcolor="#FFFFFF" style="margin-left: 200px;">
						<tr>
          					<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
	        					SELECIONAR - FISCAL
          					</td>
        				</tr>
        				<tr>
          					<td class="textonormal">
								<p align="justify">
									Para alterar/excluir um Fiscal já cadastrado, selecione o Fiscal e clique no botão "Alterar".
								</p>
          					</td>
        				</tr>
          				<td>
            				<table border="0" summary="">
								<tr>
									<td class="textonormal" bgcolor="#DCEDF7" >Tipo de fiscal*</td>
									<td class="textonormal">
										<input type="radio" name="tipofiscal" id="Interno" value="INTERNO" size="10" maxlength="10" class="textonormal" style="text-transform: none;" required <?php if($tipoFiscal == "INTERNO" || empty($tipoFiscal)){ echo "checked";}?> >
										<label for="Interno">Interno</label>
										<input type="radio" name="tipofiscal" id="Externo" value="EXTERNO" size="10" maxlength="10" class="textonormal" style="text-transform: none;" required <?php if($tipoFiscal == "EXTERNO"){ echo "checked";}?> >
										<label for="Externo">Externo</label>
									</td>
								</tr>
								<tr>
									<td class="textonormal" bgcolor="#DCEDF7" width="30%">CPF</td>
									<td class="textonormal">
										<input type="text" name="CPF" id="CPF" value="<?php echo !empty($CPF) ? $CPF: ""; ?>" size="12"  class="textonormal">
									</td>
								</tr>
                				<tr>
									<td class="textonormal" bgcolor="#DCEDF7">Fiscal </td>
									<td class="textonormal" bgcolor="#FFFFFF">
                  						<select name="FiscalSelecionar" class="textonormal">
				  							<?php
											if (empty($fiscais)) {
												echo '<option value="">Pesquisar Fiscal</option>';
											} elseif (count($fiscais) > 1) {
												echo '<option value="">Selecione um Fiscal...</option>';
											}

											# Mostra os usuários cadastrados #				  
					  						for ($i=0; $i<count($fiscais); $i++) {
												$cpfFiscal  = $Objfuncoes->MascarasCPFCNPJ($fiscais[$i]->cfiscdcpff);
												echo '<option value="'.$fiscais[$i]->cfiscdcpff.'">'.$fiscais[$i]->nfiscdnmfs.' - CPF: '.$cpfFiscal.'</option>\n';
											}
      	            						?>
                  						</select>
                  						<input type="hidden" name="Critica" value="1">
                					</td>
              					</tr>
            				</table>
          				</td>
        				<tr>
 	        				<td class="textonormal" align="right">
	          					<input type="button" id="btnSelecFiscal" value="Selecionar" class="botao">
          					</td>
        				</tr>
      				</table>
				</td>
				<!-- Fim do Corpo -->
			</table>
		</form>
	</body>
</html>
<script language="javascript" type="">
	<!--
	document.Usuario.UsuarioCodigo.focus();
	//-->
</script>
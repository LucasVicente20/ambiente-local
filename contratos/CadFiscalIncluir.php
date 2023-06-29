<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadFiscalIncluir.php
# Autor:   João Madson
# Data:     19/10/2021
# Objetivo: Programa de incluir Fiscal CR#254713
#-------------------------------------------------------------------------
require_once dirname(__FILE__) . '/../funcoes.php';
session_start();
    
?>
<html>
    <?php
	# Carrega o layout padrão #
    layout();
    ?>
	<link rel="stylesheet" type="text/css" href="../estilo.css?v=<?php echo time();?>">
    <script language="javascript" src="../janela.js" type="text/javascript"></script>
    <script language="javascript" src="../import/jquery/jquery-1.7.2.min.js" type="text/javascript"></script>
    <script language="javascript" src="../import/jquery/jquery.maskmoney.js" type="text/javascript"></script>
    <script language="javascript" src="../import/jquery/jquery.maskedinput.js" type="text/javascript"></script>​
    <script language="javascript" type=""> 

        <?php MenuAcesso(); ?>
        
    </script>

    <body background="../midia/bg.gif" marginwidth="0" marginheight="0">
    <script language="JavaScript" src="../menu.js"></script>
    <script language="JavaScript">
        Init();
		function aviso(mensagem){
                 $("#tdmensagem").show();
                $('html, body').animate({scrollTop:0}, 'slow');
				 $(".mensagem-texto").html(mensagem);
        }
		function limpaMensagem(){
            $("#tdmensagem").hide();
            $("#tdmensagemM").hide();
        }

		$(document).ready(function() {
			$('#CPF').mask('999.999.999-99');

			$('#btSalvarFiscal').on('click', function(){
				aviso("Salvando dados! Aguarde...");
				$.post("postDadosFiscal.php",$("#FiscalIncluir").serialize(), function(data){ 
                                const response = JSON.parse(data);
								
                                if(!response.status){
									// limpaMensagem();
                                    $('html, body').animate({scrollTop:0}, 'slow');
                                    $(".mensagem-texto").html(response.msm);
                                    $(".error").html("Erro!");
                                    $("#tdmensagem").show();
                                }else{
                                    $('html, body').animate({scrollTop:0}, 'slow');
                                    $(".mensagem-texto").html(response.msm);
                                    $(".error").html("Atenção!");
                                    $(".error").css("color","#007fff");
                                    $("#tdmensagem").show();
                                    $("#CPF").val("");
                                    $("[name=Nome]").val("");
                                    $("[name=Entidade]").val("");
                                    $("[name=RegInsc]").val("");
                                    $("[name=Email]").val("");
                                    $("[name=Fone]").val("");
                                }
                    });
			});
		});
    </script> 
	<br>
	<br>
	<br>
	<br>
	<table cellpadding="3" border="0" summary="">
		<!-- Caminho -->
		<tr>
			<td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
			<td align="left" class="textonormal" colspan="2">
				<font class="titulo2">|</font>
				<a href="../index.php"><font color="#000000">Página Principal</font></a> > Contratos > Fiscal > Incluir
			</td>
		</tr>
		<!-- Fim do Caminho-->
		<br>
		<!-- Erro -->
			<tr>
				<td width="150"></td>
				<td align="left" colspan="2" id="tdmensagem" hidden>
					<div class="mensagem">
						<div class="error">
						</div>
						<span class="mensagem-texto">
						</span>
					</div>
				</td>
			</tr>
		<!-- Fim do Erro -->

		<!-- Corpo -->
		<form action="CadFiscalIncluir.php" method="post" name="FiscalIncluir" id="FiscalIncluir">
			<input type="hidden" name="op" value="incluir">
			<table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" bgcolor="#FFFFFF" style="margin-left: 200px;">
				<tr>
					<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
						INCLUIR - FISCAL
					</td>
				</tr>
				<tr>
					<td class="textonormal">
						<p align="justify">
							Para incluir um novo fiscal, informe os dados abaixo e clique no botão "Incluir". Os itens obrigatórios estão com *.
						</p>
					</td>
				</tr>
				<tr>
					<td>
						<table class="textonormal" border="0" align="left" summary="">
							<tr>
								<td class="textonormal" bgcolor="#DCEDF7" width="30%">Tipo de fiscal*</td>
								<td class="textonormal">
									<input type="radio" name="tipofiscal" id="Interno" value="INTERNO" size="10" maxlength="10" class="textonormal" style="text-transform: none;" required checked>
									<label for="Interno">Interno</label>
									<input type="radio" name="tipofiscal" id="Externo" value="EXTERNO" size="10" maxlength="10" class="textonormal" style="text-transform: none;" required >
									<label for="Externo">Externo</label>
								</td>
							</tr>
							<tr>
								<td class="textonormal" bgcolor="#DCEDF7">Nome* </td>
								<td class="textonormal">
										<input type="text" text-transform="uppercase"  name="Nome" id="Nome" value="<?php echo !empty($dados["Nome"]) ? $dados["Nome"]: ""; ?>" size="45" maxlength="60" class="textonormal">
								</td>
							</tr>
							<tr>
								<td class="textonormal" bgcolor="#DCEDF7" width="30%">CPF* </td>
								<td class="textonormal">
									<input type="text" name="CPF" id="CPF" value="<?php echo !empty($dados["CPF"]) ? $dados["CPF"]: ""; ?>" size="11"  class="textonormal">
								</td>
							</tr>
							<tr>
								<td class="textonormal" bgcolor="#DCEDF7" width="30%">Entidade competente </td>
								<td class="textonormal">
									<input type="text" name="Entidade" value="<?php echo !empty($dados["Entidade"]) ? $dados["Entidade"]: ""; ?>" size="11" class="textonormal">
								</td>
							</tr>
							<tr>
								<td class="textonormal" bgcolor="#DCEDF7" width="30%">Registro ou Inscrição </td>
								<td class="textonormal">
									<input type="text" name="RegInsc" value="<?php echo !empty($dados["RegInsc"]) ? $dados["RegInsc"]: ""; ?>" size="11" class="textonormal">
								</td>
							</tr>
							<tr>
								<td class="textonormal" bgcolor="#DCEDF7">E-mail* </td>
								<td class="textonormal">
									<input type="text" name="Email" value="<?php echo !empty($dados["Email"]) ? $dados["Email"]: ""; ?>" size="45" maxlength="60" class="textonormal" style="text-transform: none;" />
										<!-- @recife.pe.gov.br -->
								</td>
							</tr>
							<tr>
								<td class="textonormal" bgcolor="#DCEDF7">Telefone* </td>
								<td class="textonormal">
									<input type="text" name="Fone" value="<?php echo !empty($dados["Fone"]) ? $dados["Fone"]: ""; ?>" size="11" maxlength="25" class="textonormal">
								</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td class="textonormal" align="right">
						<input type="button" id="btSalvarFiscal" name="Incluir" value="Incluir" class="botao">
					</td>
				</tr>
			</table>
		</form>
	</table>
	
	<!-- <script language="javascript" type="">
		<!--
			document.Usuario.Login.focus();
		//-->
	</script> -->
    </body>
</html>


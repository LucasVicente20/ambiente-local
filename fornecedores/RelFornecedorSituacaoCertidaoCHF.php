<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: RelFornecedorSituacaoCertidaoCHF.php
# Autor:    Rodrigo Melo
# Data:     27/04/2011
# Objetivo: Programa de Relatório dos Fornecedores Inscritos com as seguintes informações:
#   - Razão Social/Nome; CPF/CNPJ; Endereço; Telefone(s); email(s); Validade CHF; Situação;
#
# E os seguintes filtros:
#    - Data de Balanço Expirada; CHF Válidos; Certidões em dia; Período de emissão de CHF (data inicial e data final);
#
# Tarefa do Redmine: 2244
#
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------
# Alterado: Lucas André e Daniel Augusto
# Data:		16/05/2023
# Objetivo: Tarefa Redmine 282898
# -----------------------------------------------------------------------------------------------------------------------------------------------
# Alterado: Daniel Augusto
# Data:		16/05/2023
# Objetivo: Tarefa Redmine 282903
# -----------------------------------------------------------------------------------------------------------------------------------------------
# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/fornecedores/RelFornecedorSituacaoCertidaoCHF.php' );
AddMenuAcesso( '/fornecedores/RelFornecedorSituacaoCertidaoCHFPdf.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST"){
		
		$Botao          = $_POST['Botao'];
		$DataBalanco    = $_POST['DataBalanco'];
		$CHFValido      = $_POST['CHFValido'];
		$CertidoesValidas = $_POST['CertidoesValidas'];
		$DataIni        = $_POST['DataIni'];
		$DataFim        = $_POST['DataFim']; 
		
}else{
		$Mens     = $_GET['Mens'];
		$Mensagem = urldecode($_GET['Mensagem']);
		$Tipo			= $_GET['Tipo'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

if( $Botao == "Limpar" ){
	  header("location: RelFornecedorSituacaoCertidaoCHF.php");
	  exit;
}elseif( $Botao == "Imprimir" ){
	$DataEntradaIniCheck = explode("-",$DataIni);
	$DataEntradaFimCheck = explode("-",$DataFim);


		$Mens				= 0;
		$Mensagem 	= "Informe: ";
		
		if( $DataBalanco == "" ){
			if( $Mens == 1 ){ $Mensagem .= ", "; }
			$Mens 		 = 1;
		  	$Tipo      = 2;
			$Mensagem .= "<a href=\"javascript:document.RelFornecedorSituacaoCertidaoCHF.DataBalanco.focus();\" class=\"titulo2\">Se a data de Validade do Balanço deve estar expirada</a>";
		}
		if( $CHFValido == "" ){
			if( $Mens == 1 ){ $Mensagem .= ", "; }
			$Mens 		 = 1;
		  	$Tipo      = 2;
			$Mensagem .= "<a href=\"javascript:document.RelFornecedorSituacaoCertidaoCHF.CHFValido.focus();\" class=\"titulo2\">Se o CHF deve estar válido</a>";
		}
		if( $CertidoesValidas == "" ){
			if( $Mens == 1 ){ $Mensagem .= ", "; }
			$Mens 		 = 1;
		  	$Tipo      = 2;
			$Mensagem .= "<a href=\"javascript:document.RelFornecedorSituacaoCertidaoCHF.CertidoesValidas.focus();\" class=\"titulo2\">Se todas as certidões obrigatórias devem estar dia</a>";
		}
		
		
		if( $DataIni == "" ){
		  	$Mens 		 = 1;
		  	$Tipo       = 2;
				$Mensagem .= "<a href=\"javascript:document.RelFornecedorSituacaoCertidaoCHF.DataIni.focus();\" class=\"titulo2\">Data Inicial do Período de emissão de CHF</a>";
		}else{
				if(!checkdate($DataEntradaIniCheck[1],$DataEntradaIniCheck[2],$DataEntradaIniCheck[0])){
						$Mens      = 1;
				  	$Tipo      = 2;
						$Mensagem .= "<a href=\"javascript:document.RelFornecedorSituacaoCertidaoCHF.DataIni.focus();\" class=\"titulo2\">Data Inicial do Período de emissão de CHF Válida</a>";
				}
		}
		if( $DataFim == "" ){
				if( $Mens == 1 ){ $Mensagem .= ", "; }
				$Mens 		 = 1;
		  	$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.RelFornecedorSituacaoCertidaoCHF.DataIni.focus();\" class=\"titulo2\">Data Final do Período de emissão de CHF</a>";
		}else{
			
				if(!checkdate($DataEntradaFimCheck[1],$DataEntradaFimCheck[2],$DataEntradaFimCheck[0])){
						if( $Mens == 1 ){ $Mensagem .= ", "; }
						$Mens      = 1;
				  	$Tipo      = 2;
						$Mensagem .= "<a href=\"javascript:document.RelFornecedorSituacaoCertidaoCHF.DataFim.focus();\" class=\"titulo2\">Data Final do Período de emissão de CHF Válida</a>";
				}
		}
		
		
		if( $Mens == 0 ){
				$Url = "RelFornecedorSituacaoCertidaoCHFPdf.php?DataBalanco=$DataBalanco&CHFValido=$CHFValido&CertidoesValidas=$CertidoesValidas&DataIni=$DataIni&DataFim=$DataFim";
				if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
				header("location: ".$Url);
				exit;
		}
}
?>
<html>
<?php
# Carrega o layout padrão #
layout();
?>
<script language="javascript" src="../janela.js" type="text/javascript"></script>
<script language="javascript" type="">
function enviar(valor){
	document.RelFornecedorSituacaoCertidaoCHF.Botao.value=valor;
	document.RelFornecedorSituacaoCertidaoCHF.submit();
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="RelFornecedorSituacaoCertidaoCHF.php" method="post" name="RelFornecedorSituacaoCertidaoCHF">
<br><br><br><br><br>
<table cellpadding="3" border="0" summary="">
  <!-- Caminho -->
  <tr>
    <td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
    <td align="left" class="textonormal" colspan="2">
      <font class="titulo2">|</font>
      <a href="../index.php"><font color="#000000">Página Principal</font></a> > Fornecedores > Relatórios > Por situação de documentos/CHF
    </td>
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
      <table  border="0" cellspacing="0" cellpadding="3" bgcolor="#ffffff" summary="">
        <tr>
	      	<td class="textonormal">
	        	<table border="1" cellpadding="3" cellspacing="0"  bordercolor="#75ADE6" summary="" class="textonormal" width="100%">
	          	<tr>
	            	<td align="center" bgcolor="#75ADE6" valign="middle" colspan="4" class="titulo3">
		    					RELATÓRIO DE FORNECEDORES POR SITUAÇÃO DO BALANÇO, CERTIDÕES E CHF
		          	</td>
		        	</tr>
	  	      	<tr>
	    	      	<td class="textonormal">
	      	    		<p align="justify">
	        	    		Para imprimir os Fornecedores por Situação das Certidões e CHF, preencha os campos abaixo e clique no botão "Imprimir".
										Para limpar os campos, clique no botão "Limpar". Os campos obrigatórios estão com *.<br><br>
						        Se você não possui o Acrobat Reader, clique <a href="javascript:janela('../pdf.php','Relatorio',400,400,1,0)" class="titulo2">AQUI</a> para fazer o download.
	          	   	</p>
	          		</td>
	          	</tr>
		        	<tr>
								<td class="textonormal">
									<table class="textonormal" border="0" align="left" summary="" width="100%">
													            
				            <tr>
				              <td class="textonormal" bgcolor="#DCEDF7" width="30%">Data de validade do balanço Válida<span style="color: red;">*</span></td>
				              <td class="textonormal">
				              	<input type="radio" name="DataBalanco" value="S" <?php if( $DataBalanco == "" or $DataBalanco == "S" ){ echo "checked"; }?> > Sim
				              	<input type="radio" name="DataBalanco" value="N" <?php if( $DataBalanco == "N" ){ echo "checked"; }?> > Não
				              </td>
				            </tr>
				            
				            <tr>
				              <td class="textonormal" bgcolor="#DCEDF7" width="30%">CHF Válido<span style="color: red;">*</span></td>
				              <td class="textonormal">
				              	<input type="radio" name="CHFValido" value="S" <?php if( $CHFValido == "" or $CHFValido == "S" ){ echo "checked"; }?> > Sim
				              	<input type="radio" name="CHFValido" value="N" <?php if( $CHFValido == "N" ){ echo "checked"; }?> > Não
				              </td>
				            </tr>
				            
				            <tr>
				              <td class="textonormal" bgcolor="#DCEDF7" width="30%">Certidões Obrigatórias Válidas<span style="color: red;">*</span></td>
				              <td class="textonormal">
				              	<input type="radio" name="CertidoesValidas" value="S" <?php if( $CertidoesValidas == "" or $CertidoesValidas == "S" ){ echo "checked"; }?> > Sim
				              	<input type="radio" name="CertidoesValidas" value="N" <?php if( $CertidoesValidas == "N" ){ echo "checked"; }?> > Não
				              </td>
				            </tr>
				            
				            <tr>
								<td class="textonormal" bgcolor="#DCEDF7" height="20" width="30%">Período de emissão de CHF<span style="color: red;">*</span></td>
	          					<td class="textonormal">
								  <?php
                                    $DataMes = DataMes();

                                    if ($DataIni == "" || is_null($DataIni)) {
                                        //$DataIni = $DataMes[0];
                                    	$DataIni = "";
                                    }

                                    if ($DataFim == "" || is_null($DataFim)) {
                                        //$DataFim = $DataMes[1];
                                        $DataFim = "";
                                    }

                                    $URLIni = "../calendario.php?Formulario=ConsPesquisarDFD&Campo=DataIni";
                                    $URLFim = "../calendario.php?Formulario=ConsPesquisarDFD&Campo=DataFim";
                                ?>

                                <input id="docInicio" class="textonormal" type="date"
                                name="DataIni" size="10"
                                maxlength="10" value="<?php echo $DataIni; ?>">
                                                                    
                                &nbsp;a&nbsp;
                                <input id="docFim" class="textonormal" type="date"
                                name="DataFim" size="10"
                                maxlength="10" value="<?php echo $DataFim; ?>">
								</td>
				          	</tr>
				            
									</table>
								</td>
        			</tr>
				      <tr>
			      		<td align="right">
			    				<input type="button" name="Imprimir" value="Imprimir" class="botao" onclick="javascript:enviar('Imprimir');">
			    				<input type="button" name="Limpar" value="Limpar" class="botao" onclick="javascript:enviar('Limpar');">
			      			<input type="hidden" name="Botao" value="">
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

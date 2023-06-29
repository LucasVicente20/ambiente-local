<?php
#-----------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadIncluirEmpenho.php
# Objetivo: Programa de Inclusão de Empenhos para a Nota Fiscal
# Data:     24/07/2006
# Autor:    Álvaro Faria
# OBS.:     Tabulação 2 espaços
# -----------------------------------------------------------------------------
# Alterado: Carlos Abreu
# Data:     22/03/2007 - Colocado filtro no ano do empenho para restringir a utilizacao apenas do ano atual e o ano anterior
# -----------------------------------------------------------------------------
# Alterado: Rodrigo Melo
# Data:     28/03/2008 - Correção para que o filtro do ano atual e o ano anterior do empenho seja utilizado, pois, havia o filtro, mas não estava sendo utilizado.
# -----------------------------------------------------------------------------
# Alterado: Rodrigo Melo
# Data:      10/07/2008 - Alteração para o obter empenhos válidos, ou seja, não nulos e que não sejam subempenhos. Além de obter o valor do empenho - valor anulado do empenho, caso este seja > 0.
# -----------------------------------------------------------------------------
# Alterado: Lucas Baracho
# Data:		24/10/2018
# Objetivo: Tarefa Redmine 73662
# -----------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança	#
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/oracle/estoques/RotValidaEmpenho.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST" ){
		$ProgramaOrigem	   = $_POST['ProgramaOrigem'];
		$Botao    			   = $_POST['Botao'];
		$AnoEmpenho        = $_POST['AnoEmpenho'];
		$OrgaoEmpenho      = $_POST['OrgaoEmpenho'];
		$UnidadeEmpenho    = $_POST['UnidadeEmpenho'];
		$SequencialEmpenho = $_POST['SequencialEmpenho'];
		$ParcelaEmpenho    = $_POST['ParcelaEmpenho'];
    $DataEmissao       = $_POST['DataEmissao'];
}else{
		$ProgramaOrigem	   = $_GET['ProgramaOrigem'];
		$Empenho           = $_GET['Empenho'];
		$EmpenhoChk        = $_GET['EmpenhoChk'];
		$EmpenhoOK         = $_GET['EmpenhoOK'];
    $DataEmissao       = $_GET['DataEmissao'];
    $Valor             = $_GET['Valor'];
		$Botao             = $_GET['Botao'];
		$Mens              = $_GET['Mens'];
		$Tipo              = $_GET['Tipo'];
		$Mensagem          = $_GET['Mensagem'];
		# Divide o Empenho em partes se for uma devolução do RotValidaEmpenho #
		if($Empenho){
				$Emp               = explode(".",$Empenho);				        
        $AnoEmpenho        = $Emp[0];
				$OrgaoEmpenho      = $Emp[1];
				$UnidadeEmpenho    = $Emp[2];
				$SequencialEmpenho = $Emp[3];
				$ParcelaEmpenho    = $Emp[4];
		}
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

if( $Botao == "Incluir" ){
		# Faz as validações do Empenho #
		$Mens = 0;
		$Mensagem = "Informe: ";
		if(!$AnoEmpenho){
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.CadIncluirEmpenho.AnoEmpenho.focus();\" class=\"titulo2\">Ano Empenho</a>";
		} elseif(preg_match('/^(\d){4}$/',$AnoEmpenho)==0){ // ano que nao tenha 4 numeros
		    $Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.CadIncluirEmpenho.AnoEmpenho.focus();\" class=\"titulo2\">Ano Empenho Válido</a>";
	  } elseif($AnoEmpenho<date(Y)-1){
		    $Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.CadIncluirEmpenho.AnoEmpenho.focus();\" class=\"titulo2\">Ano Empenho Posterior ou Igual ao Ano Anterior</a>";
		} elseif($AnoEmpenho>date(Y)){
		    $Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.CadIncluirEmpenho.AnoEmpenho.focus();\" class=\"titulo2\">Ano Empenho Anterior ou Igual ao Ano Atual</a>";
		}
		
		if(!$OrgaoEmpenho){
				if ( $Mens == 1 ) { $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.CadIncluirEmpenho.OrgaoEmpenho.focus();\" class=\"titulo2\">Órgão Empenho</a>";
		}
		if(!$UnidadeEmpenho){
				if ( $Mens == 1 ) { $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.CadIncluirEmpenho.UnidadeEmpenho.focus();\" class=\"titulo2\">Unidade Empenho</a>";
		}
		if(!$SequencialEmpenho){
				if ( $Mens == 1 ) { $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.CadIncluirEmpenho.SequencialEmpenho.focus();\" class=\"titulo2\">Sequencial Empenho</a>";
		}
		if( $AnoEmpenho && $OrgaoEmpenho && $UnidadeEmpenho && $SequencialEmpenho && $Mens == 0){
				if( !SoNumeros($AnoEmpenho) or !SoNumeros($OrgaoEmpenho) or !SoNumeros($UnidadeEmpenho) or !SoNumeros($SequencialEmpenho) or (!SoNumeros($ParcelaEmpenho) and ($ParcelaEmpenho))) {
						if ( $Mens == 1 ) { $Mensagem .= ", "; }
						$Mens      = 1;
						$Tipo      = 2;
						$Mensagem .= "<a href=\"javascript:document.CadIncluirEmpenho.AnoEmpenho.focus();\" class=\"titulo2\">Apenas números para Empenho</a>";
				}else{
						# Validar Empenho no Oracle. $EmpenhoChk==0 --> Não foi checado, $EmpenhoChk==1 --> Foi checado #
												
						if(!$EmpenhoChk){
								# Cria a variável de com o número completo do Empenho #
								$Empenho = $AnoEmpenho.".".$OrgaoEmpenho.".".$UnidadeEmpenho.".".$SequencialEmpenho;
								# Se hover parcela, adiciona a parcela ao número completo do Empenho #
								if ($ParcelaEmpenho) {
										$Empenho .= ".".$ParcelaEmpenho;
								}
								# Envia para rotina de validação de EMPENHO no Banco ORACLE #
								$Url = "estoques/RotValidaEmpenho.php?ProgramaOrigem=$ProgramaOrigem&Empenho=$Empenho&Botao=$Botao";
								if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
								Redireciona($Url);
								exit;
						}

						# $EmpenhoChk==1 --> Já foi checado no Oracle. !$ParcelaEmpenho --> é Empenho #
						if( (!$ParcelaEmpenho) && ($EmpenhoChk==1) && ($EmpenhoOK==0) ){
								if ( $Mens == 1 ) { $Mensagem .= ", "; }
								$Mens      = 1;
								$Tipo      = 2;
								$Mensagem .= "<a href=\"javascript:document.CadIncluirEmpenho.AnoEmpenho.focus();\" class=\"titulo2\">Número de Empenho Válido</a>";
						}
						# $EmpenhoChk==1 --> Já foi checado no Oracle. $ParcelaEmpenho --> é Subempenho #
						if( ($ParcelaEmpenho) && ($EmpenhoChk==1) && ($EmpenhoOK==0) ){
								if( $Mens == 1 ){ $Mensagem .= ", "; }
								$Mens      = 1;
								$Tipo      = 2;
								$Mensagem .= "<a href=\"javascript:document.CadIncluirEmpenho.AnoEmpenho.focus();\" class=\"titulo2\">Número de Subempenho Válido</a>";
						}
						# Se já foi checado e está OK, grava o Empenho na sessão e volta para o programa de Nota Fiscal #
						if( ($EmpenhoChk==1) && ($EmpenhoOK==1) ){                
                $Valor = str_replace(".",",",$Valor); //Necessário para enviar a parte decimal através da URL
                $Empenho = $Valor.".".$DataEmissao.".".$Empenho;               
                $_SESSION['Empenho'] = $Empenho;                
								echo "<script>opener.document.$ProgramaOrigem.InicioPrograma.value=1</script>";
								echo "<script>opener.document.$ProgramaOrigem.submit()</script>";
								echo "<script>self.close()</script>";
						}
				}
		}
}
?>

<html>
<head>
<title>Portal de Compras - Incluir Empenhos</title>
<script language="javascript" type="">
<!--
function enviar(valor){
	document.CadIncluirEmpenho.Botao.value = valor;
	document.CadIncluirEmpenho.submit();
}
function voltar(){
	self.close();
}
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
</head>
<body background="../midia/bg.jpg" marginwidth="0" marginheight="0">
<form action="CadIncluirEmpenho.php" method="post" name="CadIncluirEmpenho">
	<table cellpadding="0" border="0" summary="">
		<!-- Erro -->
		<tr>
			<td align="left" colspan="1">
				<?php if( $Mens != 0 ){ ExibeMens($Mensagem,$Tipo,1);	}?>
			</td>
		</tr>
		<!-- Fim do Erro -->

		<!-- Corpo -->
		<tr>
			<td class="textonormal">
				<table border="0" cellspacing="0" cellpadding="3" summary="">
					<tr>
						<td class="textonormal">
							<table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" bgcolor="#FFFFFF">
								<input type="hidden" name="ProgramaOrigem" value="<?php echo $ProgramaOrigem; ?>">
								<tr>
									<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3" colspan="1">
										INCLUIR - EMPENHO
									</td>
								</tr>
								<tr>
									<td class="textonormal" colspan="1">
										<p align="justify">
											Para incluir um Empenho, preencha os campos Ano, Órgão, Unidade, Sequêncial e, se houver, Parcela.
											Depois, clique no botão "Incluir".
											Para voltar para a tela anterior, clique no botão "Voltar".
										</p>
									</td>
								</tr>
								<tr>
									<td colspan="1">
										<table border="0" width="100%" summary="">
											<tr>
												<td class="textonormal" bgcolor="#DCEDF7" height="20" width="31%">Ano*</td>
												<td class="textonormal" height="20">
													<input type=text name="AnoEmpenho" class="textonormal" value="<?php echo $AnoEmpenho; ?>" size="4" maxlength="4">
												</td>
											</tr>
											<tr>
												<td class="textonormal" bgcolor="#DCEDF7" height="20" width="31%">Órgão*</td>
												<td class="textonormal" height="20">
													<input type=text name="OrgaoEmpenho" class="textonormal" value="<?php echo $OrgaoEmpenho; ?>" size="2" maxlength="2">
												</td>
											</tr>
											<tr>
												<td class="textonormal" bgcolor="#DCEDF7" height="20" width="31%">Unidade*</td>
												<td class="textonormal" height="20">
													<input type=text name="UnidadeEmpenho" class="textonormal" value="<?php echo $UnidadeEmpenho; ?>" size="2" maxlength="2">
												</td>
											</tr>
											<tr>
												<td class="textonormal" bgcolor="#DCEDF7" height="20" width="31%">Sequencial*</td>
												<td class="textonormal" height="20">
													<input type=text name="SequencialEmpenho" class="textonormal" value="<?php echo $SequencialEmpenho; ?>" size="5" maxlength="5">
												</td>
											</tr>
											<tr>
												<td class="textonormal" bgcolor="#DCEDF7" height="20" width="31%">Parcela</td>
												<td class="textonormal" height="20">
													<input type=text name="ParcelaEmpenho" class="textonormal" value="<?php echo $ParcelaEmpenho; ?>" size="3" maxlength="3">
												</td>
											</tr>
										</table>
									</td>
								</tr>
								<tr>
									<td colspan="4" align="right">
										<input type="button" value="Incluir" class="botao" onclick="javascript:enviar('Incluir');">
										<input type="button" value="Voltar" class="botao" onclick="javascript:voltar();">
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

<?php
#-----------------------------------------------------------------------------
# Portal da DGCO
# Programa: ConsInscritoExcluido.php
# Autor:    Roberta Costa
# Data:     26/11/04
# Objetivo: Programa que Exibe os Dados do Fornecedor Inscrito Exluído
# Alterado: Rossana Lira
# Data:     29/05/2007 - Alteração para voltar direto pelo ConsInscritoSelecionar, pois
#                        antes também era utilizado pelo ConsAcompanhamento 
# OBS.:     Tabulação 2 espaços
#-----------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança	#
session_start();
Seguranca();

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD']	== "GET" ){
		$Sequencial = $_GET['Sequencial'];
}else{
		$Botao      = $_POST['Botao'];
		$Sequencial = $_POST['Sequencial'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

# Redireciona opara a Consulta de Inscrito de acordo com o botão voltar #
if( $Botao == "Voltar" ){
		$Url = "ConsInscritoSelecionar.php";
		if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
  	header("location: ".$Url);
		exit;
}

$db	= Conexao();
if( $Botao == "" ){
		# Busca os Dados da Tabela de fornecedor de Acordo com o sequencial do fornecedor  #
		$sql    = " SELECT APREFOCCGC, APREFOCCPF, NPREFORAZS, EPREFOMOTI, DPREFOGERA ";
		$sql   .= "   FROM SFPC.TBPREFORNECEDOR ";
		$sql   .= "  WHERE APREFOSEQU = $Sequencial";
	  $result = $db->query($sql);
		if( PEAR::isError($result) ){
		    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
				$Linha = $result->fetchRow();
				$CNPJ					 = $Linha[0];
				$CPF					 = $Linha[1];
				$RazaoSocial   = $Linha[2];
				$Motivo        = $Linha[3];
				$DataInscricao = substr($Linha[4],8,2)."/".substr($Linha[4],5,2)."/".substr($Linha[4],0,4);
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
	document.ConsInscritoExcluido.Botao.value = valor;
	document.ConsInscritoExcluido.submit();
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="Stylesheet" type="Text/Css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="ConsInscritoExcluido.php" method="post" name="ConsInscritoExcluido">
<br><br><br><br><br>
<table cellpadding="3" border="0" summary="">
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
			<table  border="0" cellspacing="0" cellpadding="3" summary="">
				<tr>
	      	<td class="textonormal">
	        	<table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" bgcolor="#ffffff">
	          	<tr>
	            	<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">CONSULTA DE FORNECEDOR - EXCLUÍDO </td>
		        	</tr>
	  	      	<tr>
	    	      	<td class="textonormal">
									<p align="justify">
										Os dados abaixo referem-se ao fornecedor inscrito que foi excluído. Para voltar a tela anterior clique no botão "Voltar".
	          	   	</p>
	          		</td>
		        	</tr>
        	    <tr>
								<td>
									<table cellpadding="0" cellspacing="0" border="0" bordercolor="#75ADE6" width="100%" summary="">
										<tr>
											<td>
						  					<table class="textonormal" border="0" cellpadding="1" cellspacing="3" summary="">
													<tr>
														<td class="textonormal" bgcolor="#DCEDF7" height="20">
															<?php  if( $CNPJ != 0 ){ echo "CNPJ\n"; }else{ echo "CPF\n"; } ?>
			          	    			</td>
				          	    		<td class="textonormal">
					          	    		<?php
															if( $CNPJ != 0 ){
																	echo substr($CNPJ,0,2).".".substr($CNPJ,2,3).".".substr($CNPJ,5,3)."/".substr($CNPJ,8,4)."-".substr($CNPJ,12,2);
															}else{
																	echo substr($CPF,0,3).".".substr($CPF,3,3).".".substr($CPF,6,3)."-".substr($CPF,9,2);
														  }
															?>
				          	    		</td>
				            			</tr>
													<tr>
														<td class="textonormal" bgcolor="#DCEDF7">Razão Social/Nome</td>
														<td class="textonormal" height="20"><?php echo $RazaoSocial; ?></td>
									  			</tr>
									  			<tr>
														<td class="textonormal" bgcolor="#DCEDF7">Motivo da Exclusão</td>
														<td class="textonormal" height="20"><?php echo $Motivo; ?></td>
									  			</tr>
									  			<tr>
														<td class="textonormal" bgcolor="#DCEDF7">Data de Cadastramento</td>
														<td class="textonormal" height="20"><?php echo $DataInscricao; ?></td>
									  			</tr>
												</table>
						  				</td>
						  			</tr>
									</table>
								</td>
		        	</tr>
            	<tr>
								<td align="right">
									<input type="hidden" name="Sequencial" value="<?php echo $Sequencial; ?>">
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
</form>
</body>
</html>
<?php $db->disconnect();?>

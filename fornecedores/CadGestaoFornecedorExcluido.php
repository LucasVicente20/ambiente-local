<?php
#-----------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadGestaoFornecedorExcluido.php
# Autor:    Roberta Costa
# Data:     26/08/04
# Objetivo: Programa que Exibe os Dados do Fornecedor Exluído
# Alterado: Rossana Lira
# Data:     15/05/2007 - Exibir data de última alteração
# OBS.:     Tabulação 2 espaços
#-----------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança	#
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/fornecedores/ConsAcompFornecedorSelecionar.php' );
AddMenuAcesso( '/fornecedores/CadGestaoFornecedorSelecionar.php' );
AddMenuAcesso( '/fornecedores/CadGestaoFornecedorHistorico.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD']	== "GET" ){
		$Sequencial = $_GET['Sequencial'];
		$Programa   = urldecode($_GET['Programa']);
}else{
		$Botao      = $_POST['Botao'];
		$Sequencial = $_POST['Sequencial'];
		$Programa   = $_POST['Programa'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

# Redireciona o programa de acordo com o botão voltar #
if( $Botao == "Voltar" ){
		if( $Programa == "ConsAcompFornecedor" ){
				header("location: ConsAcompFornecedorSelecionar.php");
				exit;
		}else{
				header("location: CadGestaoFornecedorSelecionar.php");
				exit;
		}
}elseif( $Botao == "Historico" ){
		$Url = "CadGestaoFornecedorHistorico.php?Sequencial=$Sequencial&NomePrograma=".urlencode("CadGestaoFornecedorExcluido.php")."";
		if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
		header("location: ".$Url);
		exit;
}elseif( $Botao == "" ){
		# Busca os Dados da Tabela de fornecedor de Acordo com o sequencial do fornecedor  #
		$db   	= Conexao();
		$sql    = " SELECT AFORCRCCGC, AFORCRCCPF, NFORCRRAZS, DFORCRGERA, TFORCRULAT ";
		$sql   .= "   FROM SFPC.TBFORNECEDORCREDENCIADO ";
		$sql   .= "  WHERE AFORCRSEQU = $Sequencial";
	  $result = $db->query($sql);
		if( PEAR::isError($result) ){
		    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
				$Linha = $result->fetchRow();
				$CNPJ					 = $Linha[0];
				$CPF					 = $Linha[1];
				$RazaoSocial   = $Linha[2];
				$DataInscricao = substr($Linha[3],8,2)."/".substr($Linha[3],5,2)."/".substr($Linha[3],0,4);
				$DataAlteracao = substr($Linha[4],8,2)."/".substr($Linha[4],5,2)."/".substr($Linha[4],0,4);				
		}

		# Busca os Dados da Tabela de Situação de acordo com o sequencial do Fornecedor #
		$sql    = "SELECT A.DFORSISITU, B.CFORTSCODI, A.EFORSIMOTI, B.EFORTSDESC ";
		$sql   .= "  FROM SFPC.TBFORNSITUACAO A, SFPC.TBFORNECEDORTIPOSITUACAO B ";
		$sql   .= " WHERE A.AFORCRSEQU = $Sequencial AND A.CFORTSCODI = B.CFORTSCODI";
		$sql   .= "   AND A.CFORTSCODI = 5 ";
		$result = $db->query($sql);
		if( PEAR::isError($result) ){
		    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
				for( $i=0;$i<1;$i++ ){
						$Linha 	      = $result->fetchRow();
						$DataSituacao = substr($Linha[0],8,2)."/".substr($Linha[0],5,2)."/".substr($Linha[0],0,4);
						$Situacao	    = $Linha[1];
						$Motivo		    = strtoupper2($Linha[2]);
						$DescSituacao = $Linha[3];
				}
		}
		$db->disconnect();
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
	document.CadGestaoFornecedorExcluido.Botao.value = valor;
	document.CadGestaoFornecedorExcluido.submit();
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="Stylesheet" type="Text/Css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="CadGestaoFornecedorExcluido.php" method="post" name="CadGestaoFornecedorExcluido">
<br><br><br><br>
<table cellpadding="3" border="0" summary="">
	<!-- Caminho -->
	<tr>
		<td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
		<td align="left" class="textonormal" colspan="2"><br>
			<font class="titulo2">|</font><a href="../index.php"><font color="#000000">Página Principal</font></a> > Fornecedores >
			<?php
			if( $Programa == "ConsAcompFornecedor" ){
					echo "Acompanhamento";
			}else{
					echo "Cadastro e Gestão";
			}
			?>
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
	        	<table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" bgcolor="#FFFFFF">
	          	<tr>
	            	<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
    							<?php
									if( $Programa == "ConsAcompFornecedor" ){
											echo "ACOMPANHAMENTO DE FORNECEDORES - FORNECEDOR EXCLUÍDO";
									}else{
											echo "CADASTRO E GESTÃO DE FORNECEDOR - EXCLUÍDO";
									}
									?>
		          	</td>
		        	</tr>
	  	      	<tr>
	    	      	<td class="textonormal">
									<p align="justify">
										Os dados abaixo referem-se ao fornecedor que foi excluído. Para voltar a tela anterior clique no botão "Voltar".
	          	   	</p>
	          		</td>
		        	</tr>
        	    <tr>
								<td>
									<table cellpadding="0" cellspacing="0" border="0" bordercolor="#75ADE6" width="100%" summary="">
										<tr>
											<td>
						  					<table class="textonormal" border="0" cellpadding="0" cellspacing="2" summary="">
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
														<td class="textonormal" bgcolor="#DCEDF7">Situação</td>
														<td class="textonormal" height="20"><?php echo $DescSituacao; ?></td>
									  			</tr>
													<tr>
														<td class="textonormal" bgcolor="#DCEDF7">Data da Situação</td>
														<td class="textonormal" height="20"><?php echo $DataSituacao; ?></td>
									  			</tr>
													<tr>
														<td class="textonormal" bgcolor="#DCEDF7">Motivo</td>
														<td class="textonormal" height="20"><?php echo $Motivo; ?></td>
									  			</tr>
									  			<tr>
														<td class="textonormal" bgcolor="#DCEDF7">Data de Cadastramento</td>
														<td class="textonormal" height="20"><?php echo $DataInscricao; ?></td>
									  			</tr>
									  			<tr>
														<td class="textonormal" bgcolor="#DCEDF7">Data de Alteração</td>
														<td class="textonormal" height="20"><?php echo $DataAlteracao; ?></td>
									  			</tr>
												</table>
						  				</td>
						  			</tr>
									</table>
								</td>
		        	</tr>
            	<tr>
								<td align="right">
									<input type="hidden" name="Programa" value="<?php echo $Programa; ?>">
									<input type="hidden" name="Sequencial" value="<?php echo $Sequencial; ?>">
									<?php if( $Programa != "ConsAcompFornecedor" ){ ?>
									<input type="button" value="Histórico" class="botao" onclick="javascript:enviar('Historico');">
									<?php } ?>
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

<?php
#-----------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadGestaoFornecedorHistorico.php
# Autor:    Roberta Costa
# Data:     19/10/04
# Objetivo: Programa que Exibe os Dados da Situação do Fornecedor
# OBS.:     Tabulação 2 espaços
#-----------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança	#
session_start();
Seguranca();

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD']	== "GET" ){
		$Sequencial   = $_GET['Sequencial'];
		$NomePrograma = urldecode($_GET['NomePrograma']);
}else{
		$Botao           = $_POST['Botao'];
		$Sequencial      = $_POST['Sequencial'];
		$NomePrograma    = $_POST['NomePrograma'];
		$CheckSituacao   = $_POST['CheckSituacao'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

if( $Botao == "Voltar" ){
		$Url = "$NomePrograma?Sequencial=$Sequencial";
		if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
		header("location: ".$Url);
		exit;
}elseif( $Botao == "" ){
		# Busca os Dados da Tabela de fornecedor de Acordo com o sequencial do fornecedor  #
		$db	    = Conexao();
		$sql    = " SELECT AFORCRCCGC, AFORCRCCPF, NFORCRRAZS ";
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
	document.CadGestaoFornecedorHistorico.Botao.value = valor;
	document.CadGestaoFornecedorHistorico.submit();
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="Stylesheet" type="Text/Css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="CadGestaoFornecedorHistorico.php" method="post" name="CadGestaoFornecedorHistorico">
<br><br><br><br><br>
<table cellpadding="3" border="0" summary="">
	<!-- Caminho -->
	<tr>
		<td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
		<td align="left" class="textonormal" colspan="2">
			<font class="titulo2">|</font><a href="../index.php"><font color="#000000">Página Principal</font></a> > Fornecedor > Cadastro e Gestão
		</td>
	</tr>
	<!-- Fim do Caminho-->

	<!-- Erro -->
	<tr>
	  <td width="100"></td>
	  <td align="left" colspan="2">
			<?php if( $Mens != 0 ){ ExibeMens($Mensagem,$Tipo,$Virgula);	}?>
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
		    					CADASTRO E GESTÃO DE FORNECEDOR - HISTÓRICO
		          	</td>
		        	</tr>
	  	      	<tr>
	    	      	<td class="textonormal">
									<p align="justify">
										Os dados abaixo referem-se ao histórico do fornecedor.
										Para voltar a tela de cadastro e gestão clique no botão "Voltar".<br>
	          	   	</p>
	          		</td>
		        	</tr>
        	    <tr>
								<td>
									<table cellpadding="0" cellspacing="0" border="0" bordercolor="#75ADE6" width="100%" summary="">
										<tr>
											<td>
						  					<table class="textonormal" border="0" cellpadding="0" cellspacing="2" width="100%" summary="">
													<tr>
														<td class="textonormal" bgcolor="#DCEDF7" height="20">
															<?php  if( $CNPJ != 0 ){ echo "CNPJ\n"; }else{ echo "CPF\n"; } ?>
			          	    			</td>
				          	    		<td class="textonormal">
					          	    		<?php if( $CNPJ != 0 ){ echo FormataCNPJ($CNPJ); }else{ echo FormataCPF($CPF); } ?>
				          	    		</td>
				            			</tr>
													<tr>
														<td class="textonormal" bgcolor="#DCEDF7" width="23%">Razão Social/Nome</td>
														<td class="textonormal" height="20"><?php echo $RazaoSocial; ?></td>
									  			</tr>
													<tr>
														<td class="textonormal" colspan="2">
															<table border="1" cellpadding="3" cellspacing="0"  bordercolor="#75ADE6" summary="" class="textonormal" width="100%">
																<?php
																# Busca os Dados da Tabela de Situação do Fornecedor #
																$db	    = Conexao();
																$sql    = " SELECT B.EFORTSDESC, A.DFORSISITU, A.EFORSIMOTI, ";
																$sql   .= "        A.TFORSIULAT, A.CFORTSCODI ";
																$sql   .= "   FROM SFPC.TBFORNSITUACAO A, SFPC.TBFORNECEDORTIPOSITUACAO B ";
																$sql   .= "  WHERE A.CFORTSCODI = B.CFORTSCODI AND A.AFORCRSEQU = $Sequencial";
																$sql   .= "  ORDER BY A.DFORSISITU, B.EFORTSDESC, A.EFORSIMOTI ";
															  $result = $db->query($sql);
																if( PEAR::isError($result) ){
																    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																}else{
																		$rows = $result->numRows();
																		echo "<tr>\n";
																		echo "	<td align=\"center\" bgcolor=\"#DCEDF7\" class=\"titulo3\">SITUAÇÃO</td>\n";
																		echo "	<td align=\"center\" bgcolor=\"#DCEDF7\" class=\"titulo3\">MOTIVO</td>\n";
																		echo "	<td align=\"center\" bgcolor=\"#DCEDF7\" class=\"titulo3\">DATA</td>\n";
																		echo "	<td align=\"center\" bgcolor=\"#DCEDF7\" class=\"titulo3\">ÚLTIMA ALTERAÇÃO</td>\n";
														  			echo "</tr>\n";
														  			for( $i=0;$i<$rows;$i++ ){
																				$Linha     = $result->fetchRow();
																				$Descricao = $Linha[0];
																				$Data  	   = DataBarra($Linha[1]);
																				$Motivo    = strtoupper2($Linha[2]);
																				$Ultima    = DataBarra($Linha[3]);
																				$Situacao  = $Linha[4];
																				echo "			<td class=\"textonormal\" bgcolor=\"#F7F7F7\" width=\"24%\">$Descricao</td>\n";
																				if( $Motivo == "" ){ $Motivo = "-"; $Alinha = "center"; }else{ $Alinha = "left"; }
																				echo "			<td class=\"textonormal\" bgcolor=\"#F7F7F7\" align=\"$Alinha\">$Motivo</td>\n";
																				echo "			<td class=\"textonormal\" bgcolor=\"#F7F7F7\" width=\"18%\" align=\"center\">$Data</td>\n";
																				echo "			<td class=\"textonormal\" bgcolor=\"#F7F7F7\" width=\"26%\" align=\"center\">$Ultima</td>\n";
																				echo "		</tr>\n";
																		}
																}
																$db->disconnect();
												  			?>
															</table>
									  				</td>
									  			</tr>
												</table>
						  				</td>
						  			</tr>
									</table>
								</td>
		        	</tr>
            	<tr>
								<td align="right">
									<input type="hidden" name="NomePrograma" value="<?php echo $NomePrograma; ?>">
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

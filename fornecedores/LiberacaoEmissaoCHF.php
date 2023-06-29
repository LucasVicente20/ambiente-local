<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: LiberacaoEmissaoCHF.php
# Autor:    Roberta Costa
# Data:     21/09/04
# Objetivo: Programa que Exibe os dados do CHF do Fornecedor Cadastrado
#--------------------------
# Alterado: Rossana Lira
# Data:     16/05/07 - Troca do nome fornecedor para firma
# Alterado: Everton Lino
# Data:     06/08/2010 - Verificação de data de balanço anual se está no prazo
# Alterado: Everton Lino
# Data:     14/10/2010 - Correção
# Alterado: Ariston Cordeiro
# Data:     05/11/2010 - Alterando prazos de balanço anual e certidão negativa
#------------------------
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
require_once( "funcoesFornecedores.php");


# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/fornecedores/LiberacaoEmissaoCHFSelecionar.php' );
AddMenuAcesso( '/fornecedores/RelEmissaoCHFPdf.php' );
AddMenuAcesso( '/fornecedores/EmissaoCHFSenha.php' );
AddMenuAcesso( '/fornecedores/EmissaoCHFSelecionar.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD']	== "GET" ){
		$Sequencial     = $_GET['Sequencial'];
		$Irregularidade = $_GET['Irregularidade'];
    $CPF_CNPJ       = $_GET['CPF_CNPJ'];
    if( strlen($CPF_CNPJ) == 14 ){
    		$TipoCnpjCpf = "CNPJ";
    }elseif( strlen($CPF_CNPJ) == 11 ){
  			$TipoCnpjCpf = "CPF";
    }
}else{
		$Botao      = $_POST['Botao'];
		$Sequencial = $_POST['Sequencial'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

# Redireciona o programa de acordo com o botão voltar #
if( $Botao == "Voltar" ){
		header("location: LiberacaoEmissaoCHFSelecionar.php");
		exit;
}elseif( $Botao == "Imprimir" ){
		$Url = "RelEmissaoCHFPdf.php?Sequencial=$Sequencial&Mensagem=".urlencode($Mensagem)."";
		if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
		header("location: ".$Url);
		exit;
}
$db	= Conexao();
if( $Critica == "" ){
		$Mens     = 0;
		$Mensagem = "";

		# Pega os Dados do Fornecedor Cadastrado #
		// inserir as colunas DFORCRULTB, FFORCRMEPP (Heraldo) 
		$sql  = " SELECT AFORCRSEQU, A.APREFOSEQU, AFORCRCCGC, AFORCRCCPF, NFORCRRAZS ";
		$sql .= " 			 ,DFORCRULTB, DFORCRCNFC, DFORCRULTB, FFORCRMEPP, B.DPREFOGERA ";
		$sql .= "   FROM SFPC.TBFORNECEDORCREDENCIADO A";
		$sql .= "  LEFT JOIN  SFPC.TBPREFORNECEDOR AS B ON B.APREFOSEQU = A.APREFOSEQU";
		$sql .= "  WHERE AFORCRSEQU = $Sequencial";
	  $result = $db->query($sql);
		if( PEAR::isError($result) ){
		    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
				$Linha = $result->fetchRow();
				$Sequencial		    	= $Linha[0];
				$PreInscricao   		= $Linha[1];
				$CNPJ					= $Linha[2];
				$CPF					= $Linha[3];
				$RazaoSocial  			= $Linha[4];
				$DataNovaUltBalanco  	= $Linha[5];
				$DataNovaCertidaoNeg 	= $Linha[6];
				$DataBalanco 			= $Linha[7];
				$MicroEmpresa 			= $Linha[8];
				$DataInscSicref 		= ($Linha[9] != "") ? substr($Linha[9], 8, 2).'/'.substr($Linha[9], 5, 2).'/'.substr($Linha[9], 0, 4).' '.substr($Linha[9], 11, 9) : ' - ';

				# Pega os Dados da Tabela de Situação #
				$sql    = "SELECT A.DFORSISITU, B.CFORTSCODI, A.EFORSIMOTI, A.DFORSIEXPI ";
				$sql   .= "  FROM SFPC.TBFORNSITUACAO A, SFPC.TBFORNECEDORTIPOSITUACAO B ";
				$sql   .= " WHERE A.AFORCRSEQU = $Sequencial ";
				$sql   .= "   AND A.CFORTSCODI = B.CFORTSCODI ";
				$sql   .= " ORDER BY A.DFORSISITU DESC";
				$result = $db->query($sql);
				if( PEAR::isError($result) ){
				    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
				}else{
						for( $i=0;$i<1;$i++ ){
								$Linha 	 									 = $result->fetchRow();
								if( $Linha[0] != "" ){
										$DataSituacao  = substr($Linha[0],8,2)."/".substr($Linha[0],5,2)."/".substr($Linha[0],0,4);
								}else{
										$DataSituacao  = "";
								}
								if( $Linha[1] == 3 ){
										$Mens     = 1;
										$Tipo     = 1;
										$Mensagem = "Fornecedor $RazaoSocial - Suspenso";
										$Url = "LiberacaoEmissaoCHFSelecionar.php?Mens=$Mens&Tipo=$Tipo&Mensagem=".urlencode($Mensagem)."";
										if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
										header("location: ".$Url);
										exit;
								}elseif( $Linha[1] == 4 ){
										$Mens     = 1;
										$Tipo     = 1;
										$Mensagem = "Fornecedor $RazaoSocial - Cancelado";
										$Url = "LiberacaoEmissaoCHFSelecionar.php?Mens=$Mens&Tipo=$Tipo&Mensagem=".urlencode($Mensagem)."";
										if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
										header("location: ".$Url);
										exit;
								}elseif( $Linha[1] == 5 ){
										$Mens     = 1;
										$Tipo     = 1;
										$Mensagem = "Fornecedor $RazaoSocial - Excluído";
										$Url = "LiberacaoEmissaoCHFSelecionar.php?Mens=$Mens&Tipo=$Tipo&Mensagem=".urlencode($Mensagem)."";
										if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
										header("location: ".$Url);
										exit;
								}else{
										$Situacao	= $Linha[1];
								}
								$Motivo = strtoupper2($Linha[2]);
								if( $Linha[3] != "" ){
										$DataSuspensao = substr($Linha[3],8,2)."/".substr($Linha[3],5,2)."/".substr($Linha[3],0,4);
								}else{
										$DataSuspensao = "";
								}
						}
				}

	    	$Cadastrado = "HABILITADO";
	    	$InabilitacaoCertidaoObrigatoria = false;
				$InabilitacaoUltBalanco = false;
				$InabilitacaoCertidaoNeg = false;

					//Everton  - correção de erro
	    		#Operação de Data#
					$data_menos_1 = new DateTime();
					$data_menos_1->setDate($data_menos_1->format("Y")-1,$data_menos_1->format("m"),$data_menos_1->format("d"));

					# Verifica também se a data de balanço anual está no prazo #
					if ( !empty($DataNovaUltBalanco) and !empty($MicroEmpresa ) ) {
						if( $DataNovaUltBalanco < prazoUltimoBalanço()->format('Y-m-d') )
						{
							//$Cadastrado = "INABILITADO";
							$InabilitacaoUltBalanco = true;
						}
					}
					
					
					if( $DataNovaCertidaoNeg < prazoCertidaoNegDeFalencia()->format('Y-m-d') )
					{
						$Cadastrado = "INABILITADO";
						$InabilitacaoCertidaoNeg = true;
					}

					# Verifica a Validação das Certidões do Fornecedor #
			 		$sql  = "SELECT A.CTIPCECODI, A.ETIPCEDESC, B.DFORCEVALI ";
					$sql .= "  FROM SFPC.TBTIPOCERTIDAO A, SFPC.TBFORNECEDORCERTIDAO B ";
					$sql .= " WHERE A.CTIPCECODI = B.CTIPCECODI AND A.FTIPCEOBRI = 'S' ";
					$sql .= "   AND B.AFORCRSEQU = $Sequencial";
					$sql .= " ORDER BY 2";
					$result = $db->query($sql);
								if( PEAR::isError($result) ){
								    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
								}else{
										$Rows = $result->numRows();
										for( $i=0; $i<$Rows;$i++ ){
												$DataHoje = date("Y-m-d");
												$Linha 	  = $result->fetchRow();
												//if( $i == 0 ){
														if( $Linha[2] < $DataHoje ){
																$Cadastrado = "INABILITADO";
																$InabilitacaoCertidaoObrigatoria = true;
														}
												//}
										}
								}

				# Verifica se já Existe Data de CHF #
				$sql    = "SELECT DFORCHGERA, DFORCHVALI, AFORCHNEMF, DFORCHULEF, ";
				$sql   .= "       AFORCHNEMU, CGREMPCOD1, CUSUPOCOD1, DFORCHULEU ";
				$sql   .= " FROM SFPC.TBFORNECEDORCHF WHERE AFORCRSEQU = $Sequencial ";
				$result = $db->query($sql);
				if( PEAR::isError($result) ){
				    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
				}else{
						$Rows = $result->numRows();
						if( $Rows != 0 ){
								$Linha 	        = $result->fetchRow();
								$DataGeracaoCHF = DataBarra($Linha[0]);
								$DataValidade   = DataBarra($Linha[1]);
								$NumFornecedor  = $Linha[2];
								if( $Linha[3] != "" ){ $DataFornecedor = DataBarra($Linha[3]); }
								$NumPrefeitura  = $Linha[4];
								$Grupo          = $Linha[5];
								$Usuario        = $Linha[6];
								if( $Linha[7] != "" ){ $DataPrefeitura = DataBarra($Linha[7]); }
						}else{
								$Mens     = 1;
								$Tipo     = 2;
								$Mensagem = "Data de Validade do CHF não informado";
								if( $_SESSION['_cperficodi_'] == 0 ){
										$Url = "EmissaoCHFSenha.php?Mens=$Mens&Tipo=$Tipo&Mensagem=".urlencode($Mensagem)."";
										if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
										header("location: ".$Url);
										exit;
								}else{
										$Url = "EmissaoCHFSelecionar.php?Mens=$Mens&Tipo=$Tipo&Mensagem=".urlencode($Mensagem)."";
										if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
										header("location: ".$Url);
										exit;
								}
						}
				}

				if( $NumPrefeitura != 0 ){
						# Pega o Nome do Responsável #
						$sql    = "SELECT EUSUPORESP FROM SFPC.TBUSUARIOPORTAL";
						$sql   .= " WHERE CGREMPCODI = $Grupo AND CUSUPOCODI = $Usuario";
						$result = $db->query($sql);
						if( PEAR::isError($result) ){
						    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
						}else{
								$Linha 	     = $result->fetchRow();
								$Responsavel = $Linha[0];
						}
				}
		}

		# Mensagem para Fornecedor Inabilitado #

		$bloquearFornecedor = false;
		if($InabilitacaoCertidaoObrigatoria and $Cadastrado == "INABILITADO"){
			$Mens     = 1;
			$Tipo     = 1;
			$Mensagem = "Certidão(ões) fora do prazo de validade";
			$bloquearFornecedor = true;
		}
		if( $Cadastrado == "INABILITADO" and $InabilitacaoUltBalanco ){
			if( $Mens == 1 ){ $Mensagem .=", "; }
			$Mens     = 1;
			$Tipo     = 1;
			$Mensagem.= "Data de Validade do Balanço expirada";
 			$bloquearFornecedor = true;
		 }
		 
		 if( $Cadastrado == "INABILITADO" and $InabilitacaoCertidaoNeg ){
			if( $Mens == 1 ){ $Mensagem .=", "; }
			$Mens     = 1;
			$Tipo     = 1;
			$Mensagem.= "Data de Certidão Negativa expirada";
 			$bloquearFornecedor = true;
		 }
		 
		 // Inserido por Heraldo (23/01/2014)
		 if (  !empty($MicroEmpresa)  and empty($DataBalanco) ) {
		 	if( $Mens == 1 ){	$Mensagem .=", ";
		 	}
		 	$Mens     = 1;
		 	$Tipo     = 1;
		 	$Mensagem .= "CHF simplificado sem demonstrações contábeis";
		 	$bloquearFornecedor = true;
		 }
		 	
		 
		 
		 
		 
		 if($bloquearFornecedor){
		 	$Mensagem = "Fornecedor Inabilitado com ".$Mensagem;
			$Url = "LiberacaoEmissaoCHFSelecionar.php?Mens=$Mens&Tipo=$Tipo&Mensagem=".urlencode($Mensagem)."";
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
<script language="javascript" type="">
<!--
function enviar(valor){
	document.LiberacaoEmissaoCHF.Botao.value = valor;
	document.LiberacaoEmissaoCHF.submit();
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="LiberacaoEmissaoCHF.php" method="post" name="LiberacaoEmissaoCHF">
<br><br><br><br><br>
<table cellpadding="3" border="0" summary="">
	<!-- Caminho -->
	<tr>
		<td width="100"><img border="0" src="../midia/linha.gif"></td>
		<td align="left" class="textonormal" colspan="2">
			<font class="titulo2">|</font><a href="../index.php"><font color="#000000">Página Principal</font></a> > Fornecedores > CHF > Liberação de Emissão de CHF
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
		    					LIBERAÇÃO DE EMISSÃO DO CERTIFICADO DE HABILITAÇÃO DE FIRMAS
		          	</td>
		        	</tr>
	  	      	<tr>
	    	      	<td class="textonormal">
									<p align="justify">
										Para emitir o Certificado de Habilitação de Firmas, clique no botão "Imprimir". Para retornar a página anterior clique no botão "Voltar".<br>
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
							              <td class="textonormal" bgcolor="#DCEDF7">Razão Social/Nome</td>
							              <td class="textonormal" height="20"><?php echo $RazaoSocial;?></td>
							            </tr>
													<tr>
														<td class="textonormal" bgcolor="#DCEDF7">
															<?php if( $CNPJ != "" ){ echo "CNPJ\n"; } else { echo "CPF\n"; }?>
			          	    			</td>
														<td class="textonormal" height="20">
					          	    		<?php
															if( $CNPJ <> 0 ){
			    												echo FormataCNPJ($CNPJ);
			  											}else{
			    												echo FormataCPF($CPF);
			    										}
															?>
				          	    		</td>
				            			</tr>
									  			<tr>
														<td class="textonormal" bgcolor="#DCEDF7" height="20">Data de Geração do CHF</td>
														<td class="textonormal"><?php echo $DataGeracaoCHF;?></td>
												  </tr>
												  <tr>
														<td class="textonormal" bgcolor="#DCEDF7" height="20">Data de Inscrição do SICREF</td>
														<td class="textonormal"><?php echo $DataInscSicref;?></td>
									  			</tr>
									  			<tr>
														<td class="textonormal" bgcolor="#DCEDF7" height="20">Número de Emissões do Fornecedor</td>
														<td class="textonormal">
															<?php if( $NumFornecedor != 0 ){ echo $NumFornecedor; }else{ echo "NENHUM CHF EMITIDO"; }?>
														</td>
									  			</tr>
									  			<tr>
														<td class="textonormal" bgcolor="#DCEDF7" height="20">Última Data de Emissão do Fornecedor</td>
														<td class="textonormal">
															<?php if( $DataFornecedor != 0 ){ echo $DataFornecedor; }else{ echo "-"; }?>
														</td>
									  			</tr>
									  			<tr>
														<td class="textonormal" bgcolor="#DCEDF7" height="20">Número de Emissões do Prefeitura</td>
														<td class="textonormal">
															<?php if( $NumPrefeitura != 0 ){ echo $NumPrefeitura; }else{ echo "NENHUM CHF EMITIDO"; }?>
														</td>
									  			</tr>
									  			<tr>
														<td class="textonormal" bgcolor="#DCEDF7" height="20" width="45%">Última Data de Emissão da Prefeitura</td>
														<td class="textonormal">
															<?php if( $DataPrefeitura != "" ){ echo $DataPrefeitura; }else{ echo "-"; }?>
														</td>
									  			</tr>
									  			<tr>
														<td class="textonormal" bgcolor="#DCEDF7" height="20">Responsável pela Emissão da Prefeitura</td>
														<td class="textonormal">
															<?php if( $Responsavel != "" ){ echo $Responsavel; }else{ echo "-"; }?>
														</td>
									  			</tr>
												</table>
						  				</td>
						  			</tr>
									</table>
								</td>
		        	</tr>
  						<tr>
								<td class="textonormal" align="right">
									<input type="hidden" name="Grupo" value="<?php echo $Grupo; ?>">
									<input type="hidden" name="Usuario" value="<?php echo $Usuario; ?>">
									<input type="hidden" name="Sequencial" value="<?php echo $Sequencial; ?>">
									<input type="hidden" name="Mensagem" value="<?php echo $Mensagem; ?>">
									<input type="button" value="Imprimir" class="botao" onclick="javascript:enviar('Imprimir');">
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

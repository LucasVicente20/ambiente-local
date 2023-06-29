<?php
#-----------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadItemValorTRPDetalhe.php
# Autor:    Rodrigo Melo
# Data:     05/08/2011
# Objetivo: Programa de Detalhamento de Itens do valor TRP da Solicitação de Compra
#-----------------------------------------
# OBS.:			- Tabulação 2 espaços
#-----------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança	#
session_start();
Seguranca();

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD']	== "POST" ){
	$Botao    			= $_POST['Botao'];
	$TipoMaterial 	    = $_POST['TipoMaterial'];
	$TipoGrupo        	= $_POST['TipoGrupo']; // M ou NULL para Material e S para serviço
	$MaterialServico  	= $_POST['MaterialServico']; // Material ou Serviço
}else{
	$TipoGrupo            = $_GET['TipoGrupo']; // M ou NULL para Material e S para serviço
	$MaterialServico      = $_GET['MaterialServico'];
	$SeqItem              = $_GET['SeqItem'];
	$SeqSolicitacaoCompra = $_GET['SeqSolicitacaoCompra'];
	$SeqFornecedorTRP     = $_GET['SeqFornecedorTRP'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

if( $Botao == "Voltar" ){
		$Enviar = "S";
}

if($TipoGrupo == "S"){
	$Descricao = "Serviço";
} else {
	$TipoGrupo = "M"; // Tipo do grupo é material por padrão
	$Descricao = "Material";
}


?>
<html>
<head>
<title>Portal de Compras - Detalhes do <?php echo $Descricao; ?></title>
<script language="javascript" type="">
function enviar(valor){
	document.CadItemDetalhe.Botao.value = valor;
	document.CadItemDetalhe.submit();
}
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
</head>
<body background="../midia/bg.jpg" marginwidth="0" marginheight="0">
<form action="CadItemDetalhe.php" method="post" name="CadItemDetalhe">
	<table cellpadding="0" border="0" summary="">
		<!-- Erro -->
		<tr>
		  <td align="left" colspan="2">
				<?php if( $Mens != 0 ){ ExibeMens($Mensagem,$Tipo,1);	}?>
		 	</td>
		</tr>
		<!-- Fim do Erro -->

		<!-- Corpo -->
		<tr>
			<td class="textonormal">
				<table border="0" cellspacing="0" cellpadding="3" summary="" width="100%">
					<tr>
		      	<td class="textonormal">
		        	<table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" width="100%" class="textonormal" bgcolor="#FFFFFF" summary="">
		          	<tr>
		            	<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3" colspan="3">
			    			TABELA REFERENCIAL DE PREÇOS (TRP) - DETALHAMENTO
			          	</td>
			        	</tr>
		  	      	<tr>
		    	      	<td class="textonormal" colspan="3">
										<p align="justify">
											Para fechar a janela clique no no botão "Voltar".
											&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
											&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
											&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
											&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
											&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
											&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
											&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
											&nbsp;&nbsp;
		          	   	</p>
		          		</td>
			        	</tr>
			        	<?php
									# Pega os dados do Material/Serviço de acordo com o código #
									$db   = Conexao();

									if($TipoGrupo == "M"){
										$colunaMaterialServico = "cmatepsequ";
									} else {
										$colunaMaterialServico = "cservpsequ";
									}


									#Informações de itens da solicitação de compra

									$sql = "
									select forn.NFORCRRAZS,

									forn.AFORCRCCGC, forn.AFORCRCCPF,

									forn.CCEPPOCODI, forn.CCELOCCODI,

									forn.EFORCRLOGR, forn.AFORCRNUME, forn.EFORCRCOMP, forn.EFORCRBAIR, forn.NFORCRCIDA, forn.CFORCRESTA,

									forn.AFORCRCDDD, forn.AFORCRTELS, forn.AFORCRNFAX,

									forn.NFORCRMAIL, forn.NFORCRMAI2, item.vitescunit

									from sfpc.tbitemsolicitacaocompra item
									inner join sfpc.tbfornecedorcredenciado forn on item.aforcrsequ = forn.aforcrsequ
									where
									item.citescsequ = $SeqItem
									and item.csolcosequ = $SeqSolicitacaoCompra
									and item.$colunaMaterialServico = $MaterialServico
									and forn.aforcrsequ = $SeqFornecedorTRP
									";

									$res  = $db->query($sql);

									if( PEAR::isError($res) ){
										ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
									}else{
										$Linha = $res->fetchRow();

										$RazaoSocial   = $Linha[0];
										$CNPJ = $Linha [1];
										$CPF  = $Linha [2];
										$Logradouro = $Linha[5];
										$Numero = $Linha[6];
										$Complemento = $Linha[7];
										$Bairro = $Linha[8];
										$Cidade = $Linha[9];
										$Estado = $Linha[10];
										$DDD = $Linha[11];
										$Telefone = $Linha[12];
										$Fax = $Linha[13];
										$Email = $Linha[14];
										$Email2 = $Linha[15];
										$ValorTRP   = $Linha[16];

										# Verificando qual documento está válido (CPF ou CNPJ) para ser colocado na tela #
										if(!is_null($CNPJ)){
					                      $CNPJ_CPF = FormataCNPJ($CNPJ);
					                    } else {
					                      $CNPJ_CPF = FormataCPF($CPF);
					                    }

					                    # Verificando qual CEP está válido para ser colocado na tela #
										if( $Linha [3] != "" ){
											$CEP = $Linha[3];
										}else{
											$CEP = $Linha[4];
										}

										# Colocando o Endereço Agrupado #
										if( $Logradouro != "" ){
											if( $Numero == ""){ $Numero = "S/N"; }
											if( $Complemento != "" ){
												$Endereco = $Logradouro.", ".$Numero." ".$Compemento." - ".$Bairro." ".$Cidade."/".$Estado;
											}else{
												$Endereco = $Logradouro.", ".$Numero." - ".$Bairro." ".$Cidade."/".$Estado;
											}
										}else{
											$Endereco = "";
										}

										# Colocando o E-mail Agrupado #
										if(!is_null($Email2)){
											$Email = $Email . ", ". $Email2;
										}
										# Colocando os telefones/faxes Agrupados #
										if(!is_null($DDD)){
											$Telefone = "(". $DDD. ") ". $Telefone;
											$Fax = "(". $DDD. ") ". $Fax;
										}
									}

									if($TipoGrupo == "M"){
										$sql  = "
											SELECT DISTINCT
												E.FGRUMSTIPO, A.EMATEPDESC, B.EUNIDMDESC, E.FGRUMSTIPM
											FROM
												SFPC.TBMATERIALPORTAL A
													LEFT OUTER JOIN SFPC.TBCORRECAOMATERIAL CM ON CM.CCORMAMATE =  A.CMATEPSEQU
													LEFT OUTER JOIN	SFPC.TBMATERIALPORTAL A2 ON A2.CMATEPSEQU = CM.CCORMAMAT1,
												SFPC.TBUNIDADEDEMEDIDA B,
												SFPC.TBSUBCLASSEMATERIAL C,
												SFPC.TBCLASSEMATERIALSERVICO D,
												SFPC.TBGRUPOMATERIALSERVICO E
											WHERE
												A.CUNIDMCODI = B.CUNIDMCODI AND A.CSUBCLSEQU = C.CSUBCLSEQU
												AND C.CGRUMSCODI = D.CGRUMSCODI AND C.CCLAMSCODI = D.CCLAMSCODI
												AND D.CGRUMSCODI = E.CGRUMSCODI AND A.CMATEPSEQU = $MaterialServico
												AND E.FGRUMSTIPO = '$TipoGrupo'
												AND ( CM.TCORMAULAT IS NULL OR CM.TCORMAULAT = (SELECT MAX(TCORMAULAT) FROM SFPC.TBCORRECAOMATERIAL WHERE CCORMAMATE = $MaterialServico) )
										";
									} else {
										$sql  = "
											SELECT DISTINCT
												C.FGRUMSTIPO,
												A.ESERVPDESC

											FROM
												SFPC.TBSERVICOPORTAL A,
												SFPC.TBCLASSEMATERIALSERVICO B,
												SFPC.TBGRUPOMATERIALSERVICO C
											WHERE
												A.CGRUMSCODI = B.CGRUMSCODI AND A.CCLAMSCODI = B.CCLAMSCODI
												AND B.CGRUMSCODI = C.CGRUMSCODI AND A.CSERVPSEQU = $MaterialServico
												AND C.FGRUMSTIPO = '$TipoGrupo'
										";
									}

									$res  = $db->query($sql);

								  if( PEAR::isError($res) ){
										  ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
									}else{
										$Linha         = $res->fetchRow();
								    	$TipoGrupoBanco   = $Linha[0];
										$DescMaterialServico = $Linha[1];

										if($TipoGrupo == "M"){
											$DescUnidade      = $Linha[2];
											$TipoMaterial     = $Linha[3];
										}
									}
									$db->disconnect();
									?>

				        			<tr>
										<td colspan="3">
											<table border="0" cellpadding="0" cellspacing="0" bordercolor="#75ADE6" width="100%" summary="">
												<tr>
													<td colspan="2">
								      	    <table class="textonormal" border="0" width="100%" summary="">
												<tr>
										        	<td class="textonormal" bgcolor="#DCEDF7" height="20" width="20%">Tipo do Grupo</td>
										            <td class="textonormal">
										              	<?php if( $TipoGrupoBanco == "M" ){ echo "MATERIAL"; }else{ echo "SERVIÇO"; } ?>
									              	</td>
									            </tr>
									            <?php if($TipoGrupo == "M"){ ?>
										            <tr>
										              <td class="textonormal" bgcolor="#DCEDF7" height="20" width="20%">Tipo de Material</td>
										              <td class="textonormal">
										              	<?php if( $TipoMaterial == "C" ){ echo "CONSUMO"; }else{ echo "PERMANENTE"; } ?>
									              	</td>
									            	</tr>
								            	<?php } ?>

									        		<tr>
									            		<td class="textonormal" bgcolor="#DCEDF7" height="20">Código Reduzido do <?php echo $Descricao; ?></td>
								  	        			<td class="textonormal"><?php echo $MaterialServico;?></td>
								  	        		</tr>

									        		<tr>
									            		<td class="textonormal" bgcolor="#DCEDF7" height="20"><?php echo $Descricao; ?></td>
								  	        			<td class="textonormal"><?php echo $DescMaterialServico;?></td>
									        		</tr>

									        		<?php if($TipoGrupo == "M"){ ?>
										        		<tr>
											            	<td class="textonormal" bgcolor="#DCEDF7" height="20">Unidade de Medida</td>
									  	        			<td class="textonormal"><?php echo $DescUnidade;?></td>
											        	</tr>
									        		<?php } ?>


									        		<tr>
									            	<td class="textonormal" bgcolor="#DCEDF7" height="20">Valor Referencial (R$)</td>
								  	        		<td class="textonormal"><?php echo $ValorTRP;?></td>
									        	  </tr>

                              <tr>
									            	<td class="textonormal" bgcolor="#DCEDF7" height="20">Razão Social</td>
								  	        		<td class="textonormal"><?php echo $RazaoSocial;?></td>
									        	  </tr>

                              <tr>
									            	<td class="textonormal" bgcolor="#DCEDF7" height="20">CPF/CNPJ</td>
								  	        		<td class="textonormal"><?php echo $CNPJ_CPF;?></td>
									        	  </tr>

                              <tr>
									            	<td class="textonormal" bgcolor="#DCEDF7" height="20">Endereço</td>
								  	        		<td class="textonormal"><?php echo $Endereco;?></td>
									        	  </tr>

                              <tr>
									            	<td class="textonormal" bgcolor="#DCEDF7" height="20">Telefone(s)</td>
								  	        		<td class="textonormal"><?php echo $Telefone;?></td>
									        	  </tr>

                              <tr>
									            	<td class="textonormal" bgcolor="#DCEDF7" height="20">Fax</td>
								  	        		<td class="textonormal"><?php echo $Fax;?></td>
									        	  </tr>

                              <tr>
									            	<td class="textonormal" bgcolor="#DCEDF7" height="20">Email(s)</td>
								  	        		<td class="textonormal"><?php echo $Email;?></td>
									        	  </tr>
								          	</table>
													</td>
												</tr>
											</table>
										</td>
									</tr>
		          		<tr>
			            	<td colspan="3" align="right">
		              			<input type="hidden" name="Enviar" value="<?php echo $Enviar; ?>">
		              			<input type="hidden" name="TipoGrupo" value="<?php echo $TipoGrupo; ?>">
		              			<input type="hidden" name="TipoMaterial" value="<?php echo $TipoMaterial; ?>">
		              			<input type="hidden" name="Material" value="<?php echo $MaterialServico; ?>">
		              			<input type="hidden" name="DescMaterialServico" value="<?php echo $DescMaterialServico; ?>">
		              			<input type="hidden" name="ValorTRP" value="<?php echo $ValorTRP; ?>">
						    	<input type="button" value="Voltar" class="botao" onclick="javascript:self.close();">
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
<script language="javascript" type="">
window.focus();
//-->
</script>
</body>
</html>

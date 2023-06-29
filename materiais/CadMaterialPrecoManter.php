<?php
#----------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadMaterialPrecoManter.php
# Objetivo: Programa de Manutenção de Preços de Material
# Data:     06/06/2007
# Alterado: Carlos Abreu
# Data:     11/06/2007 - Correção no update para selecionar por material e data
# Alterado: Rodrigo Melo
# Data:     29/10/2007 - Correção da gravação na tabela de preço de material que está com
#                        problema na hora da gravação (UPDATE), pois está passando o Novo Preço
#                        com vírgula ao invés de ponto
# Alterado: Marcos Túlio
# Data: 20/09/2011 Adição do campo observação, adição do campo última observação
# OBS.:     Tabulação 2 espaços
#----------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança#
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/materiais/CadMaterialExcluir.php' );
AddMenuAcesso( '/materiais/CadMaterialPrecoHistorico.php' );
AddMenuAcesso( '/materiais/CadMaterialPrecoManterSelecionar.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST" ){
		$Botao            = $_POST['Botao'];
		$TipoMaterial     = $_POST['TipoMaterial'];
		$NovoPreco		  = $_POST['NovoPreco'];
		$Grupo            = $_POST['Grupo'];
		$DescGrupo        = $_POST['DescGrupo'];
		$Classe           = $_POST['Classe'];
		$DescClasse       = $_POST['DescClasse'];
		$Subclasse        = $_POST['Subclasse'];
		$DescSubclasse    = $_POST['DescSubclasse'];
		$DescUnidade      = $_POST['DescUnidade'];
		$Material         = $_POST['Material'];
		$NCaracteresM     = $_POST['NCaracteresM'];
		$NCaracteresC     = $_POST['NCaracteresC'];
		$NCaracteresO     = $_POST['NCaracteresO'];
		$DescMaterial     = $_POST['DescMaterial'];
		$DescMaterialComp = $_POST['DescMaterialComp'];
		$Observacao       = $_POST['Observacao'];
		$Obs              = strtoupper2(trim($_POST['Obs']));
		$NCaracteresOBS   = $_POST['NCaracteresOBS'];
}else{
		$Grupo            = $_GET['Grupo'];
		$Classe           = $_GET['Classe'];
		$Subclasse        = $_GET['Subclasse'];
		$Material         = $_GET['Material'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

if( $Botao == "Voltar" ){
		header("location: CadMaterialPrecoManterSelecionar.php");
		exit;
}elseif( $Botao == "Historico" ){
		$Url = "CadMaterialPrecoHistorico.php?Material=$Material";
		if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
		header("location: ".$Url);
		exit;
}elseif( $Botao == "Confirmar" ){
		$Mens     = 0;
		$Mensagem = "Informe: ";
		if( str_replace(",",".",$NovoPreco) == 0 ){
				if( $Mens == 1 ){ $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.CadMaterialPrecoManter.NovoPreco.focus();\" class=\"titulo2\">Novo Preço</a>";
		} else {
				if( (! SoNumVirg($NovoPreco) or (! Decimal($NovoPreco)))){
                                                if( $Mens == 1 ){ $Mensagem .= ", "; }
						$Mens      = 1;
						$Tipo      = 2;
						$Mensagem .= "<a href=\"javascript:document.CadMaterialPrecoManter.NovoPreco.focus();\" class=\"titulo2\">Novo Preço Válido</a>";
				}
		}
        if($NCaracteresOBS > "300"){
				if($Mens == 1 ){ $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.CadMaterialPrecoManter.Obs.focus();\" class=\"titulo2\">Observação menor que 300 caracteres</a>";
		}
		if( $Mens == 0 ){
				$db   = Conexao();
				$DataHora = date("Y-m-d H:i:s");
				$Data     = date("Y-m-d");
				# Verifica se foi cadastrado o preço do material no dia corrente
				$sql  = "SELECT COUNT(*) FROM SFPC.TBPRECOMATERIAL WHERE CMATEPSEQU = $Material AND DPRECMCADA = '$Data'";
				$result = $db->query($sql);
				if (PEAR::isError($result)) {
						ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
				}else{
						$Linha    = $result->fetchRow();
						$NovoPreco = str_replace(",",".",$NovoPreco);  //ALTERADO
						if ($Linha[0] > 0) {
								# Altere o preço na Tabela de Materiais #
								$db   = Conexao();
								$sql  = " UPDATE SFPC.TBPRECOMATERIAL
								 SET  VPRECMPREC = $NovoPreco, TPRECMULAT = '$DataHora', EPRECMOBSE = '$Obs'
								 WHERE CMATEPSEQU = $Material AND DPRECMCADA = '$Data' ";
								$res  = $db->query($sql);
								if( PEAR::isError($res) ){
										ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
								}else{
										# Redireciona para a tela de seleção #
										$Mensagem = urlencode("Preço de Material Informado com Sucesso");
										$Url = "CadMaterialPrecoManterSelecionar.php?Mens=1&Tipo=1&Mensagem=$Mensagem";
										if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
										header("location: ".$Url);
										exit;
								}

						} else {
								# Inclui na Tabela de Materiais #
								$db   = Conexao();
								$sql  = "INSERT INTO SFPC.TBPRECOMATERIAL (CMATEPSEQU, DPRECMCADA, VPRECMPREC, TPRECMULAT,EPRECMOBSE)";
								$sql .= "   VALUES ($Material, '$Data', $NovoPreco,'$DataHora','$Obs')";
								$res  = $db->query($sql);
								if( PEAR::isError($res) ){
										ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
								}else{
										# Redireciona para a tela de seleção #
										$Mensagem = urlencode("Preço de Material Informado com Sucesso");
										$Url = "CadMaterialPrecoManterSelecionar.php?Mens=1&Tipo=1&Mensagem=$Mensagem";
										if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
										header("location: ".$Url);
										exit;
								}
						}
				}
				$db->disconnect();
		}
}
if( $Botao == "" ){
		# Pega os dados do Pré-Material de acordo com o código #
		$db   = Conexao();
		$sql = " SELECT DISTINCT GRU.FGRUMSTIPM, GRU.EGRUMSDESC, CLA.ECLAMSDESC, SUB.ESUBCLDESC,
                 MAT.EMATEPDESC, MAT.EMATEPCOMP, MAT.CUNIDMCODI, UND.EUNIDMDESC, UND.CUNIDMCODI,MAT.EMATEPOBSE,
                 (SELECT EPRECMOBSE FROM SFPC.TBPRECOMATERIAL WHERE CMATEPSEQU = $Material AND TPRECMULAT IN (SELECT MAX(TPRECMULAT)
                 FROM SFPC.TBPRECOMATERIAL PREMAT WHERE PREMAT.CMATEPSEQU = MAT.CMATEPSEQU)) AS OBSPRECO
                 FROM SFPC.TBMATERIALPORTAL MAT, SFPC.TBGRUPOMATERIALSERVICO GRU, SFPC.TBCLASSEMATERIALSERVICO CLA,
                 SFPC.TBSUBCLASSEMATERIAL SUB, SFPC.TBUNIDADEDEMEDIDA UND
                 WHERE SUB.CGRUMSCODI = CLA.CGRUMSCODI AND SUB.CCLAMSCODI = CLA.CCLAMSCODI
                 AND CLA.CGRUMSCODI = GRU.CGRUMSCODI AND MAT.CSUBCLSEQU = SUB.CSUBCLSEQU
                 AND MAT.CUNIDMCODI = UND.CUNIDMCODI  AND MAT.CMATEPSEQU = $Material  ";
		$res  = $db->query($sql);
		if( PEAR::isError($res) ){
				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
				$Linha         		= $res->fetchRow();
				$TipoMaterial  		= $Linha[0];
				$DescGrupo     		= $Linha[1];
				$DescClasse    		= $Linha[2];
				$DescSubclasse 		= $Linha[3];
				$DescMaterial  		= $Linha[4];
				$NCaracteresM       = strlen($DescMaterial);
				$DescMaterialComp   = $Linha[5];
				$NCaracteresC       = strlen($DescMaterialComp);
				$Unidade       		= $Linha[6];
				$DescUnidade   		= $Linha[7];
				$Unidade       		= $Linha[8];
				$Observacao         = $Linha[9];
				$NCaracteresO       = strlen($Observacao);
				$UltimaObs          = $Linha[10];
				$Descricao          = substr($Linha[10],0,100);
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
	document.CadMaterialPrecoManter.Botao.value=valor;
	document.CadMaterialPrecoManter.submit();
}
function ncaracteresobs(valor){
	document.CadMaterialPrecoManter.NCaracteresOBS.value = '' +  document.CadMaterialPrecoManter.Obs.value.length;
	/* if( navigator.appName == 'Netscape' && valor ){ //Netscape Only
		document.CadMaterialPrecoManter.Obs.focus();
	}*/
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="CadMaterialPrecoManter.php" method="post" name="CadMaterialPrecoManter">
<br><br><br><br><br>
<table cellpadding="3" border="0" summary="">
	<!-- Caminho -->
	<tr>
		<td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
		<td align="left" class="textonormal" colspan="2">
			<font class="titulo2">|</font>
			<a href="../index.php"><font color="#000000">Página Principal</font></a> > Materiais > Preço > Manter
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
			<table border="0" cellspacing="0" cellpadding="3" bgcolor="#ffffff" summary="">
				<tr>
					<td class="textonormal">
						<table border="0" cellspacing="0" cellpadding="0" summary="">
							<tr>
								<td class="textonormal">
									<table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" bgcolor="#FFFFFF">
										<tr>
											<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
												MANUTENÇÃO DE PREÇOS DE MATERIAIS
											</td>
										</tr>
										<tr>
											<td class="textonormal">
												<p align="justify">
													 Para incluir/atualizar um Preço de Material, preencha os dados abaixo e clique no botão "Confirmar".
												</p>
											</td>
										</tr>
										<tr>
											<td>
												<table border="0" cellpadding="0" cellspacing="0" bordercolor="#75ADE6" width="100%" summary="">
													<tr>
														<td colspan="2">
															<table class="textonormal" border="0" width="100%" summary="">
																<tr>
																	<td class="textonormal" bgcolor="#DCEDF7" height="20">Tipo de Material</td>
																	<td class="textonormal">
																		<?php if( $TipoMaterial == "C" ){ echo "CONSUMO"; }else{ echo "PERMANENTE"; } ?>
																	</td>
																</tr>
																<tr>
																	<td class="textonormal" bgcolor="#DCEDF7" height="20">Grupo</td>
																	<td class="textonormal"><?php echo $DescGrupo; ?></td>
																</tr>
																<tr>
																	<td class="textonormal" bgcolor="#DCEDF7" height="20">Classe</td>
																	<td class="textonormal"><?php echo $DescClasse; ?></td>
																</tr>
																<tr>
																	<td class="textonormal" bgcolor="#DCEDF7" height="20">Subclasse</td>
																	<td class="textonormal"><?php echo $DescSubclasse; ?></td>
																</tr>
																<tr>
																	<td class="textonormal" bgcolor="#DCEDF7" height="20">Código do Material</td>
																	<td class="textonormal"><?php echo $Material;?></td>
																</tr>
																<tr>
																	<td class="textonormal" bgcolor="#DCEDF7" height="20">Material</td>
																	<td class="textonormal"><?php echo $DescMaterial;?></td>
																</tr>
																<tr>
																	<td class="textonormal" bgcolor="#DCEDF7" height="20">Unidade de Medida</td>
																	<td class="textonormal"><?php echo $DescUnidade;?></td>
																</tr>
																<tr>
																	<td class="textonormal" bgcolor="#DCEDF7" height="20">Descrição Completa</td>
																	<td class="textonormal"><?php echo $DescMaterialComp;?></td>
																</tr>
																<tr>
																	<td class="textonormal" bgcolor="#DCEDF7" width="20%" height="20">Último Preço</td>
																			<?
																			$db   = Conexao();
																			$sql  = "SELECT VPRECMPREC ";
																			$sql .= "  FROM SFPC.TBPRECOMATERIAL ";
																			$sql .= " WHERE CMATEPSEQU = $Material ";
																			$sql .= "   AND DPRECMCADA = (SELECT MAX(DPRECMCADA) FROM SFPC.TBPRECOMATERIAL WHERE CMATEPSEQU = $Material )";
																			$result = $db->query($sql);
																			if (PEAR::isError($result)) {
																					ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																			}else{
																					$Linha    = $result->fetchRow();
																					$UltValor = str_replace(".",",",$Linha[0]);
																			}
																			$db->disconnect();
																			?>
																	<td class="textonormal" align="left"><?php echo $UltValor;?></td>
                                                                </tr>
                                                                    </tr>
																	<tr>
																		<td class="textonormal" bgcolor="#DCEDF7" height="20">Última Observação</td>
																		<td class="textonormal"><?php echo $UltimaObs;?></td>
																    </tr>
																	<tr>
																		<td class="textonormal" bgcolor="#DCEDF7">Novo Preço</td>
																		<td>
																			<input type="text" class="textonormal" name="NovoPreco" value="<?php echo $NovoPreco;?>" size="11" maxlength="11">
																		</td>
																	<tr>
				                                                        <td class="textonormal" bgcolor="#DCEDF7" height="20">Observação</td>
				                                                            <td class="textonormal">
				                                                                 <font class="textonormal">máximo de 300 caracteres</font>
																			     <input type="text" name="NCaracteresOBS" disabled size="3" value="<?php echo $NCaracteresOBS ?>"  class="textonormal"><br>
																			     <textarea name="Obs" cols="50" rows="5" OnKeyUp="javascript:ncaracteresobs(1)" OnBlur="javascript:ncaracteresobs(0)" OnSelect="javascript:ncaracteresobs(1)" class="textonormal"><?php echo $Obs; ?></textarea>
																	         </td>

																   </tr>
																</tr>
															</table>
														</td>
													</tr>
												</table>
											</td>
										</tr>
										<tr>
											<td colspan="2" align="right">
												<input type="hidden" name="TipoMaterial" value="<?php echo $TipoMaterial; ?>">
												<input type="hidden" name="Grupo" value="<?php echo $Grupo; ?>">
												<input type="hidden" name="DescGrupo" value="<?php echo $DescGrupo; ?>">
												<input type="hidden" name="Classe" value="<?php echo $Classe; ?>">
												<input type="hidden" name="DescClasse" value="<?php echo $DescClasse; ?>">
												<input type="hidden" name="Subclasse" value="<?php echo $Subclasse; ?>">
												<input type="hidden" name="DescSubclasse" value="<?php echo $DescSubclasse; ?>">
												<input type="hidden" name="Material" value="<?php echo $Material; ?>">
												<input type="hidden" name="DescUnidade" value="<?php echo $DescUnidade; ?>">
												<input type="hidden" name="DescMaterial" value="<?php echo $DescMaterial; ?>">
												<input type="hidden" name="UltimaObs" value="<?php echo $UltimaObs; ?>">
												<input type="hidden" name="DescMaterialComp" value="<?php echo $DescMaterialComp; ?>">
												<input type="hidden" name="Observacao" value="<?php echo $Observacao; ?>">
											    <input type="button" value="Confirmar" class="botao" onclick="javascript:enviar('Confirmar');">
												<input type="button" value="Histórico" class="botao" onclick="javascript:enviar('Historico');">
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
		</td>
	</tr>
	<!-- Fim do Corpo -->
</table>
</form>
</body>
</html>
<script language="JavaScript">
<!--
document.CadMaterialPrecoManter.NovoPreco.focus();
//-->
</script>

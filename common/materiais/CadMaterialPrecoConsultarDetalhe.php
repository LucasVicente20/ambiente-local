<?php
#----------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadMaterialPrecoConsultarDetalhe.php
# Objetivo: Programa de Detalhamento da Consulta de Preços de Material
# Autor:    Rossana Lira
# Data:     03/07/2007
# Alterado: Marcos Túlio
# Data: 22/09/2011 Adição do campo observação
# OBS.:     Tabulação 2 espaços
#----------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança  #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/materiais/CadMaterialPrecoConsultar.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST" ){
		$Botao            = $_POST['Botao'];
		$TipoMaterial     = $_POST['TipoMaterial'];
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
		$PrecoData        = $_POST['PrecoData'];
	    $Obs              = $_POST['Obs'];
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
		$Url = "CadMaterialPrecoConsultar.php";
		if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
		header("location: ".$Url);
		exit;
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
				$Obs                = $Linha[10];
				$Descricao   = substr($Linha[10],0,100);


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
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="CadMaterialPrecoConsultarDetalhe.php" method="post" name="CadMaterialPrecoConsultarDetalhe">
<br><br><br><br><br>
<table cellpadding="3" border="0" summary="">
	<!-- Caminho -->
	<tr>
		<td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
		<td align="left" class="textonormal" colspan="2">
			<font class="titulo2">|</font>
			<a href="../index.php"><font color="#000000">Página Principal</font></a> > Materiais > Preço > Consultar
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
												TABELA REFERENCIAL DE PREÇOS DE MATERIAIS - DETALHAMENTO
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
																	<td class="textonormal" bgcolor="#DCEDF7" height="20">Código Reduzido Material</td>
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
																	<td class="textonormal" bgcolor="#DCEDF7" height="20">Valor Referencial (R$)</td>
																	<?
																	$db   = Conexao();
																	$sql  = "SELECT VPRECMPREC FROM SFPC.TBPRECOMATERIAL ";
																	$sql .= " WHERE CMATEPSEQU = $Material ";
																	$sql .= "   AND DPRECMCADA = (SELECT MAX(DPRECMCADA) FROM SFPC.TBPRECOMATERIAL WHERE CMATEPSEQU = $Material )";
																	$result = $db->query($sql);
																	if (PEAR::isError($result)) {
																			ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																	}else{
																			$Linha = $result->fetchRow();
																			$Valor = str_replace(".",",",$Linha[0]);
																	}
																	$db->disconnect();
																	?>
																	<td class="textonormal" align="left"><?php echo $Valor;?></td>
																</tr>
															<tr>
																	<td class="textonormal" bgcolor="#DCEDF7" height="20">Observação</td>
																	<td class="textonormal"><?php echo $Descricao;?></td>
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
												<input type="hidden" name="DescMaterialComp" value="<?php echo $DescMaterialComp; ?>">
												<input type="hidden" name="Observacao" value="<?php echo $Observacao; ?>">
                                                <input type="hidden" name="Obs" value="<?php echo $Obs; ?>">
												<input type="submit" value="Voltar" class="botao">
												<input type="hidden" name="Botao" value="Voltar">
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

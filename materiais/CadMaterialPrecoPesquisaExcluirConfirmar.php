<?php
#----------------------------------------------------------------------------
# Portal da DGCO
# Programa..: Excluir registro de pesquisa de mercado 
# Autor.....: Heraldo Botelho
# Data......: 
# OBS.......:      
#----------------------------------------------------------------------------



# Acesso ao arquivo de funções #
include "../funcoes.php";




# Executa o controle de segurança#
session_start();
Seguranca();



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

		//----------- Valores de campos editados
		$ValorUnitario    = $_POST['ValorUnitario'] ;
		$Marca   		  = trim($_POST['Marca']);
		$Modelo           = trim($_POST['Modelo']);
		$CodFornecedor    = $_POST['CodFornecedor'];
		$CNPJFornecedor   = trim($_POST['CNPJFornecedor']);
		$RazaoSocial      = trim($_POST['RazaoSocial']);
		$DDD              = trim($_POST['DDD']);
		$Telefone		  = trim($_POST['Telefone']);
		$DataReferencia	  = trim($_POST['DataReferencia']);
		$Observacao2      = trim($_POST['Observacao2']);
		$chaveDoMaterial  = $_POST['chaveDoMaterial']; 

		$ValorUnitario    = $_POST['ValorUnitario'];
		$Modelo    		  = $_POST['Modelo'];
		$Marca    		  = $_POST['Marca'];
		$RazaoSocial      = $_POST['RazaoSocial'];
		$CNPJFornecedoro  = $_POST['CNPJFornecedor'];
		$DDD              = $_POST['DDD'];
		$Telefone         = $_POST['Telefone'];
		$Data             = $_POST['Data'];
		$Observacao2      = $_POST['Observacao2'];
		
}else{
		$Grupo            = $_GET['Grupo'];
		$Classe           = $_GET['Classe'];
		$Subclasse        = $_GET['Subclasse'];
		$Material         = $_GET['Material'];
}


//---------------------------
// Retirar mascara do CNPJ
//---------------------------
$vetorAux= array(".","/","-");
$cnpjAux = str_replace($vetorAux,"",$CNPJFornecedor);
$valorAux = str_replace(".","",$ValorUnitario);
$valorAux = str_replace(",",".",$valorAux);


# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;


if ( $Botao == "Voltar" ){
		header("location: CadMaterialPrecoPesquisaSetarSelecionar2.php");
		exit;
}		
if( $Botao == "Confirmar" ){

//--------------------------------------------------------
//  Excluir TRP
//--------------------------------------------------------	
	$sql  = " delete from sfpc.tbtabelareferencialprecos ";
	$sql .= " where "; 
	$sql .= " cpesqmsequ=$chaveDoMaterial ";
	$db   = Conexao();
	$result  = $db->query($sql);

//--------------------------------------------------------
//  Excluir tabela de pesquisa de mercado
//--------------------------------------------------------	
	$sql  = " delete from sfpc.tbpesquisaprecomercado ";
	$sql .= " where "; 
	$sql .= " cpesqmsequ=$chaveDoMaterial ";
	$db   = Conexao();
	$result  = $db->query($sql);

 
	if( PEAR::isError($result) ) {
		$Mensagem="Exlusão Impossibilitada! Pesquisa sendo usado um registro de preço";
		$Tipo=2;
		$Mens =1; 
	} else {	
		$db->disconnect();
		header("location: CadMaterialPrecoPesquisaSetarSelecionar2.php?ExcluidoComSucesso=1");
		exit;
	}
	$db->disconnect();
	
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

		# carregar dados da pesquisa de mercado
		$sql  = " select ";
		$sql .= " pesq.cpesqmvalo as valor,";
		$sql .= " pesq.cpesqmmarc as marca,";
        $sql .= " pesq.cpesqmmode as modelo,";
		$sql .= " pesq.aforcrsequ as seqcnpj,";
		$sql .= " pesq.cpesqmcnpj as cnpj,";
		$sql .= " pesq.npesqmrazs as razao,";
		$sql .= " pesq.npesqmtels as fones,";
		$sql .= " pesq.epesqmobse as observacao,";
		$sql .= " to_char(pesq.dpesqmrefe,'dd/mm/yyyy') as data,";
		
		$sql .= " forn.aforcrccgc as cnpjforn,";
		$sql .= " forn.nforcrrazs as razaoforn,";
		$sql .= " forn.aforcrcddd as dddforn, ";
		$sql .= " forn.aforcrtels as fonesforn,";
		$sql .= " forn.aforcrdddc as dddfornc, ";
		$sql .= " forn.aforcrtelc as fonesfornc";
		$sql .= " from ";
		$sql .= " sfpc.tbpesquisaprecomercado pesq left join ";
		$sql .= " sfpc.tbfornecedorcredenciado forn on ";
		$sql .= " ( pesq.aforcrsequ = forn.aforcrsequ )";
		$sql .= " where ";
		$sql .= " pesq.cpesqmsequ = $chaveDoMaterial";
		
	 
 
		
		$result = executarSQL($db, $sql);
		$row = $result->fetchRow(DB_FETCHMODE_OBJECT);
		
		$ValorUnitario=$row->valor;
		$Modelo=$row->modelo;
		$Marca=$row->marca;
		$Data=$row->data;
		 
		 
		if ( empty( $row->seqcnpj)) {
			$RazaoSocial=$row->razao;					
			$CNPJFornecedor=$row->cnpj;
		    //$DDD=;// ta faltando
			$Telefone=$row->fones;
		} else {
			$RazaoSocial=$row->razaoforn;					
			$CNPJFornecedor=$row->cnpjforn;
			if ( !empty($row->fonesforn) ) { 
			    $DDD=$row->dddforn;
				$Telefone=$row->fonesforn;
			}
			else {
			    $DDD=$row->dddfornc;
				$Telefone=$row->fonesfornc;
			}
		}
		
		
		$Observacao2=$row->observacao;
		
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
	document.CadMaterialPrecoPesquisaExcluirConfirmar.Botao.value=valor;
	document.CadMaterialPrecoPesquisaExcluirConfirmar.submit();
}


<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="CadMaterialPrecoPesquisaExcluirConfirmar.php" method="post" name="CadMaterialPrecoPesquisaExcluirConfirmar">
<br><br><br><br><br>
<table cellpadding="3" border="0" summary="">
	<!-- Caminho -->
	<tr>
		<td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
		<td align="left" class="textonormal" colspan="2">
			<font class="titulo2">|</font>
			<a href="../index.php"><font color="#000000">Página Principal</font></a> > Materiais > Preço > Pesquisa de Mercado > Excluir
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
													 Para excluir clique no botão "Confirmar".
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
																<!-- 
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
                                                                 
                                                                     
																	<tr>
																		<td class="textonormal" bgcolor="#DCEDF7" height="20">Última Observação</td>
																		<td class="textonormal"><?php echo $UltimaObs;?></td>
																    </tr>
																    -->
																	<tr>
																		<td class="textonormal" bgcolor="#DCEDF7">Valor Unitário*</td>
																			 <?php echo "<td><span class=\"dinheiro\">$ValorUnitario</span> </td>";  ?>
																	</tr>
																	
																	<tr>
																		<td class="textonormal" bgcolor="#DCEDF7">Marca*</td>
																			 <?php echo "<td><span class=\"textonormal\">$Marca</span> </td>";  ?>
																	</tr>
																	
																	<tr>
																		<td class="textonormal" bgcolor="#DCEDF7">Modelo*</td>
																			 <?php echo "<td><span class=\"textonormal\">$Modelo</span> </td>";  ?>
																	</tr>
																	
																	<tr>
																		<td class="textonormal" bgcolor="#DCEDF7">CNPJ Fornecedor*</td>
																		
																		<?php  $CNPJFornecedorAux = substr($CNPJFornecedor,0,2).".".substr($CNPJFornecedor,2,3).".".substr($CNPJFornecedor,5,3)."/".substr($CNPJFornecedor,8,4)."-".substr($CNPJFornecedor,12,2) ; ?>
																		 <?php echo "<td><span class=\"cnpj\">$CNPJFornecedorAux</span> </td>";  ?>
																	</tr>
																	
																	<tr>
																		<td class="textonormal" bgcolor="#DCEDF7">Razão Social*</td>
																			 <?php echo "<td><span class=\"textonormal\">$RazaoSocial</span> </td>";  ?>
																	</tr>
																	
																	<?php 	if (empty($codAux) ) {  ?>
																	<tr>
																		<td class="textonormal" bgcolor="#DCEDF7">DDD</td>
																			 <?php echo "<td><span class=\"textonormal\">$DDD</span> </td>";  ?>
																	</tr>
																	<?php  } ?>
																	
																	<tr>
																		<td class="textonormal" bgcolor="#DCEDF7">Telefone</td>
																			 <?php echo "<td><span class=\"textonormal\">$Telefone</span> </td>";  ?>
																	</tr>
																	
																	<tr>
																		<td class="textonormal" bgcolor="#DCEDF7">Data de Referência*</td>
																		 
					 														<?php echo "<td><span class=\"textonormal\">$Data</span> </td>";  ?>
																		 
																	</tr>
				
																	<tr>
																		<td class="textonormal" bgcolor="#DCEDF7">Observação</td>
																		 
																		    <?php echo "<td><div class=\"caixatexto\">$Observacao2</div> </td>";  ?>
																		 
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
												<input type="hidden" name="CodFornecedor" value="<?php echo $CodFornecedor; ?>">
												<input type="hidden" name="chaveDoMaterial" value="<?php echo $chaveDoMaterial; ?>">
												<input type="hidden" name="exclusaoEfetuada" >
												
												<input type="hidden" name="ValorUnitario" value="<?php echo $ValorUnitario; ?>">
												<input type="hidden" name="Modelo" value="<?php echo $Modelo; ?>">
												<input type="hidden" name="Marca" value="<?php echo $Marca; ?>">
												<input type="hidden" name="RazaoSocial" value="<?php echo $RazaoSocial; ?>">
												<input type="hidden" name="CNPJFornecedor" value="<?php echo $CNPJFornecedor; ?>">
												<input type="hidden" name="DDD" value="<?php echo $DDD; ?>">
												<input type="hidden" name="Telefone" value="<?php echo $Telefone; ?>">
												<input type="hidden" name="Data" value="<?php echo $Data; ?>">
												<input type="hidden" name="Observacao2" value="<?php echo $Observacao2; ?>">
												
											    <input type="button" value="Excluir" class="botao" onclick="javascript:enviar('Confirmar');">
											     
											    
												<input type="button" value="Voltar" class="botao" onclick="javascript:enviar('Voltar');">
												<input type="hidden" name="Botao" value="">
												
												</form>
												
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

</body>
</html>
<script language="JavaScript">
 
</script>

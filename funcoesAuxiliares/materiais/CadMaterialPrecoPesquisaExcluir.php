<?php

#----------------------------------------------------------------------------
# Portal da DGCO
# Programa:  
# Objetivo:  
# Data:      
# Alterado:  
# Data:      
# Alterado:  
# Data:     29/10/2007 - Correção da gravação na tabela de preço de material que está com
#                        problema na hora da gravação (UPDATE), pois está passando o Novo Preço
#                        com vírgula ao invés de ponto
# Alterado:  
# Data:  
# OBS.:      
#----------------------------------------------------------------------------



# Acesso ao arquivo de funções #
include "../funcoes.php";




# Executa o controle de segurança#
session_start();
Seguranca();

AddMenuAcesso( '/materiais/CadMaterialPrecoPesquisaExcluirConfirmar.php' );

  

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
		$chaveDoMaterial  = $_POST['chaveDoMaterial'];

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
		$Observacao2       = trim($_POST['Observacao2']);
		 
		
}else{
		$Grupo              = 	$_GET['Grupo'];
		$Classe             = 	$_GET['Classe'];
		$Subclasse          = 	$_GET['Subclasse'];
		$Material           = 	$_GET['Material'];
	
}




//----------------------------------------- 
// Verificar a Quantidade de Linhas 
//----------------------------------------- 
$sql =  " select count(*) as qtdlinhas"; 
$sql .= " from "; 											
$sql .= " sfpc.tbpesquisaprecomercado pesq,  sfpc.tbmaterialportal mat ";											
$sql .= " where "; 											
$sql .= " pesq.cmatepsequ = mat.cmatepsequ and "; 											
$sql .= " pesq.cmatepsequ = $Material ";											
$db   = Conexao();
$result = executarSQL($db, $sql);
$row = $result->fetchRow(DB_FETCHMODE_OBJECT);
$qtdLinhas=$row->qtdlinhas;
$db->disconnect();
 


if ( $qtdLinhas==0) {
	$Mens = 1;
	$Tipo= 1;
    $Mensagem = "Não foi realizada nenhuma Pesquisa de Peço para o material selecionado";	
}




# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

if ( $Botao == "Voltar" ){
		header("location: CadMaterialPrecoPesquisaSetarSelecionar2.php");
		exit;
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
	document.CadMaterialPrecoPesquisaExcluir.Botao.value=valor;
	document.CadMaterialPrecoPesquisaExcluir.submit();
}

function chamarConfirmacao(chave){
	document.CadMaterialPrecoPesquisaExcluir.chaveDoMaterial.value=chave;
	document.CadMaterialPrecoPesquisaExcluir.submit();
}


<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="CadMaterialPrecoPesquisaExcluirConfirmar.php" method="post" name="CadMaterialPrecoPesquisaExcluir" >
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
											<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3" colspan=10>
												MANUTENÇÃO DE PREÇOS DE MATERIAIS
											</td>
										</tr>
										<tr>
											<td class="textonormal" colspan=10>
												<p align="justify">
													 Para selecionar uma Pesquisa de Mercado de material já cadastrado, clique na descrição do material desejado.												</p>
											</td>
										</tr>
										<tr>
											<td class="titulo3" bgcolor="#DCEDF7" align="center" colspan=10 >PESQUISA POR FAMILIA</td>
										</tr>
										<!--   cabecalho     -->
										<tr class="titulo3" >
										 <td>DESCRIÇÃO DO MATERIAL</td><td>MARCA</td><td>MODELO</td><td>FORNECEDOR</td><td>DATA DE REFENCIA</td><td>DATA ÚLTIMA ATUALIZAÇÃO</td>
										<?php  
											//------------------------------
											//-  Executar a Query 
											//-----------------------------
											$sql =  " select"; 
											$sql .= " mat.ematepdesc as descricao, ";
											$sql .= " pesq.cpesqmmarc  as marca, ";
											$sql .= " pesq.cpesqmmode as modelo, ";
											$sql .= " pesq.aforcrsequ as codforn, ";
											$sql .= " pesq.npesqmrazs as razao, ";
											$sql .= " to_char(pesq.dpesqmrefe,'dd/mm/yyyy') as referencia, ";
											$sql .= " to_char(pesq.tpesqmulat,'dd/mm/yyyy') as atualizacao, ";
											$sql .= " pesq.cpesqmsequ as chave  ";											
											$sql .= " from "; 
											$sql .= " sfpc.tbpesquisaprecomercado pesq,  sfpc.tbmaterialportal mat ";
											$sql .= " where "; 
											$sql .= " pesq.cmatepsequ = mat.cmatepsequ and "; 
											$sql .= " pesq.cmatepsequ = $Material ";
											$sql .= " order by pesq.tpesqmulat desc";
											
											$db   = Conexao();
											$result = executarSQL($db, $sql);
											 
							 				while  ( $row = $result->fetchRow(DB_FETCHMODE_OBJECT)  ) {
							 					 
												$descricao = $row->descricao ; $marca=$row->marca ;
												$modelo=$row->modelo;
												$razao=$row->razao;
												$referencia=$row->referencia;
												$codforn=$row->codforn;
												$atualizacao= $row->atualizacao;
												$chave=$row->chave;
												echo "<tr>";
												echo "<td><a href=\"javascript:chamarConfirmacao($chave)\" ><font color=\"#000000\">$descricao</font></a> </td>";
												echo "<td>$marca</td>";
												echo "<td>$modelo</td>";
												
												if ( empty($razao)) {
													$sql =	" select  nforcrrazs as razao ";
													$sql .= " from sfpc.tbfornecedorcredenciado ";  
													$sql .= " where aforcrsequ=$codforn ";
													$result2 = executarSQL($db, $sql);
													$row2 = $result2->fetchRow(DB_FETCHMODE_OBJECT);
													$razao =$row2->razao;
												}	
												
												echo "<td>$razao</td>";
												echo "<td>$referencia</td>";
												echo "<td>$atualizacao</td>";
												echo "</tr>";
											}
											$db->disconnect();
										
										?>
										 
										
										</tr>
										
										
										
										<tr>
											<td colspan="10" align="right"   >
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
												<input type="hidden" name="chaveDoMaterial" >
												
											
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
 
</script>

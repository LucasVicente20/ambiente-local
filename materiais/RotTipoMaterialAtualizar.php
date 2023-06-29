<?php
#--------------------------------------------------------------------------------------
# Portal da DGCO
# Programa: RotTipoMaterialAtualizar.php
# Autor:    Rossana Lira
# Data:     09/06/05
# Objetivo: Programa que atualiza a tabela de grupo de material
# OBS.:     Tabulação 2 espaços
#--------------------------------------------------------------------------------------
# Acesso ao arquivo de funções #
include "../funcoes.php";
# Executa o controle de segurança #
session_start();
Seguranca();
# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST"){
		$Botao        = $_POST['Botao'];
		$TipoMaterial	= $_POST['TipoMaterial'];
		$Grupo	 			= $_POST['Grupo'];
		$Total	 			= $_POST['Total'];
}
# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;
if( $Botao == "Atualizar" ){
	  $Mens = 0;
    if( $Total != count($TipoMaterial) ){
    		$Mens     = 1;
    		$Tipo     = 2;
		    $Mensagem = "Todos os Grupo Material devem ser informados o Tipo de Material para efetuar a Atualização";
    }
		if( $Mens == 0 ){
				# Atualiza a tabela de Classe com o Tipo de Fornecimento #
				$db = Conexao();
				$db->query("BEGIN TRANSACTION");
				for( $i=0;$i<$Total;$i++ ){
						$sql    = "UPDATE SFPC.TBGRUPOMATERIALSERVICO ";
						$sql   .= "   SET FGRUMSTIPM = '$TipoMaterial[$i]', TGRUMSULAT = '".date("Y-m-d H:i:s")."' ";
						$sql   .= " WHERE CGRUMSCODI = $Grupo[$i] AND FGRUMSTIPO = 'M' ";
						$result = $db->query($sql);
						if( PEAR::isError($result) ){
								$db->query("ROLLBACK");
						    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
						}
				}
				$db->query("COMMIT");
				$db->query("END TRANSACTION");
				$db->disconnect();
				$TipoMaterial	= "";
				$Mens         = 1;
    		$Tipo         = 1;
		    $Mensagem     = "Atualização Realizada com Sucesso";
		}
		$Botao = "";
}
if ( $Botao == "" ){
	# Verifica se há grupo com tipo de material com P ou C #
	$db 	  = Conexao();
	$sql    = "SELECT COUNT(CGRUMSCODI) FROM SFPC.TBGRUPOMATERIALSERVICO ";
	$sql   .= "WHERE FGRUMSTIPM = 'P' OR FGRUMSTIPM = 'M' ";
	$result = $db->query($sql);
	if( PEAR::isError($result) ){
			ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
	}else{
			$Linha 	= $result->fetchRow();
			$Existe = $Linha[0];
	}
	$db->disconnect();
}
?>
<html>
<?php
# Carrega o layout padrão #
layout();
?>
<script language="javascript" type="" >
<!--
function enviar(valor){
	document.Atualiza.Botao.value=valor;
	document.Atualiza.submit();
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css" >
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="RotTipoMaterialAtualizar.php" method="post" name="Atualiza" >
<br><br><br><br><br>
<table cellpadding="3" border="0" summary="" >
  <!-- Caminho -->
  <tr>
    <td width="100" ><img border="0" src="../midia/linha.gif" alt="" ></td>
    <td align="left" class="textonormal" colspan="2" >
      <font class="titulo2" >|</font>
      <a href="../index.php" ><font color="#000000" >Página Principal</font></a> > Materiais > Atualização Material
    </td>
  </tr>
  <!-- Fim do Caminho-->
	<!-- Erro -->
	<?php if ( $Mens == 1 ) {?>
  <tr>
  	<td width="150" ></td>
		<td align="left" colspan="2" ><?php ExibeMens($Mensagem,$Tipo,1); ?></td>
	</tr>
	<?php } ?>
	<!-- Fim do Erro -->
	<!-- Corpo -->
	<tr>
		<td width="100" ></td>
		<td class="textonormal" >
      <table  border="0" cellspacing="0" cellpadding="3" bgcolor="#ffffff" >
        <tr>
	      	<td class="textonormal" >
	        	<table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" class="textonormal" summary="" >
	          	<tr>
	            	<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
		    					ATUALIZAÇÃO DA TABELA DE GRUPO PARA O TIPO DE MATERIAL
		          	</td>
		        	</tr>
		        	<tr>
	    	      	<td class="textonormal" >
	      	    		<p align="justify" >
	        	    		Para fazer a alteração da tabela de Grupo Material só aparecerão os materiais cujo o tipo de material não foi definido,
	        	    		para escolher o tipo de material escolha a opção consumo ou permanente e clique no botão "Atualizar".
	          	   	</p>
	          		</td>
		        	</tr>
		        	<tr>
	  	        	<td>
	    	      		<table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" width="100%" >
							    	<?php
                  	# Mostra as classes com tipos de fornecimentos integrados #
                		$db   = Conexao();
										$sql  = "SELECT CGRUMSCODI, EGRUMSDESC FROM SFPC.TBGRUPOMATERIALSERVICO ";
										$sql .= " WHERE FGRUMSTIPO = 'M' AND FGRUMSTIPM = ''";
										//$sql .= " WHERE FGRUMSTIPO = 'M' AND FGRUMSTIPM IS NULL";
										//$sql .= " WHERE FGRUMSTIPO = 'M' AND FGRUMSTIPM <> 'P' AND FGRUMSTIPM <> 'C'";
										$sql .= " ORDER BY EGRUMSDESC";
                		$result = $db->query($sql);
                		if( PEAR::isError($result) ){
										    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
										}else{
												$Rows = $result->numRows();
												if( $Rows == 0 ){
  													echo "<tr>\n";
		          	      			echo "	<td class=\"textonormal\" height=\"20\">\n";
		          	      			echo "		Todos o Grupos Materiais possuem Tipo de Material Definido\n";
		          	      			echo "	</td>\n";
		          	      			echo "</tr>\n";
												}else{
  													echo "<tr>\n";
														echo "<td class=\"titulo3\" bgcolor=\"#DCEDF7\" height=\"20\">GRUPO MATERIAL</td>\n";
														echo "<td class=\"titulo3\" bgcolor=\"#DCEDF7\" height=\"20\" width=\"10%\">TIPO DE MATERIAL</td>\n";
		          	      			echo "</tr>\n";
		          	      			$Total = 0;
														for( $i=0; $i< $Rows;$i++ ){
																$Linha = $result->fetchRow();
				          	      			echo "<tr>\n";
				          	      			echo "	<td class=\"textonormal\" bgcolor=\"#F7F7F7\" height=\"20\" valign=\"top\">\n";
				          	      			echo "		$Linha[1]\n";
																echo "		<input type=\"hidden\" name=\"Grupo[$i]\" value=\"$Linha[0]\">\n";
																echo "	</td>\n";
				          	      			echo "	<td class=\"textonormal\" bgcolor=\"#F7F7F7\" height=\"20\" width=\"36%\" valign=\"top\">\n";
																echo "		<input type=\"radio\" name=\"TipoMaterial[$i]\" value=\"C\"> Consumo\n";
																echo "		<input type=\"radio\" name=\"TipoMaterial[$i]\" value=\"P\"> Permanente\n";
																echo "	</td>\n";
				          	      			echo "</tr>\n";
				          	      			$Total++;
					                	}
					              }
		  	    						echo "	<input type=\"hidden\" name=\"Total\" value=\"$Total\">\n";
			              }
				          	$db->disconnect();
      	            ?>
									</table>
								</td>
		        	</tr>
	  	      	<tr>
   	  	  			<td class="textonormal" align="right" >
									<input type="hidden" name="Existe" value="<?php echo $Existe; ?>" >
			            <input type="button" value="Atualizar" class="botao" onclick="javascript:enviar('Atualizar');" >
			            <input type="hidden" name="Botao" value="" >
		          	</td>
		        	</tr>
		        	<?php if( $Existe != 0 ){ ?>
		        	<tr>
	  	        	<td>
	    	      		<table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" width="100%">
										<tr>
											<td class="titulo3" bgcolor="#75ADE6" height="20" align="center">GRUPO MATERIAL</td>
										</tr>
							    	<?php
                  	# Mostra as classes com tipos de fornecimentos integrados #
                		$db   = Conexao();
										$sql  = "SELECT CGRUMSCODI, EGRUMSDESC, FGRUMSTIPM FROM SFPC.TBGRUPOMATERIALSERVICO ";
										$sql .= " WHERE FGRUMSTIPO = 'M' AND ( FGRUMSTIPM = 'P' OR FGRUMSTIPM = 'C' ) ";
										$sql .= " ORDER BY FGRUMSTIPM, EGRUMSDESC";
                		$result = $db->query($sql);
                		if( PEAR::isError($result) ){
										    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
										}else{
												$Rows = $result->numRows();
												for( $i=0; $i< $Rows;$i++ ){
														$Linha = $result->fetchRow();
		          	      			if( $Linha[2] == "C" ){ $DescTipoMaterial = "CONSUMO"; }else{ $DescTipoMaterial = "PERMANENTE"; }
														if( $DescTipoMaterial != $DescTipoMaterialAntes ){
				          	      			echo "<tr>\n";
				          	      			echo "<td class=\"textoabason\" bgcolor=\"#DCEDF7\" height=\"20\" valign=\"top\">$DescTipoMaterial</td>\n";
				          	      			echo "</tr>\n";
				          	      	}
		          	      			echo "<tr>\n";
		          	      			echo "<td class=\"textonormal\" height=\"20\" valign=\"top\">&nbsp;&nbsp;&nbsp;".substr(trim($Linha[1]),0,60)."</td>\n";
		          	      			echo "</tr>\n";
		          	      			$DescTipoMaterialAntes = $DescTipoMaterial;
			                	}
			              }
  	              	$db->disconnect();
      	            ?>
									</table>
								</td>
		        	</tr>
		        	<?php } ?>
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

<?php
#------------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadGestaoFornecedorOcorrencias.php
# Autor:    Roberta Costa
# Data:     26/08/04
# Objetivo: Programa que Exibe as Ocorrências do Fornecedor
# OBS.:     Tabulação 2 espaços
#------------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança	#
session_start();
Seguranca();

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD']	== "GET" ){
		$Sequencial	    = $_GET['Sequencial'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;
?>
<html>
<head>
<title>Portal de Compras - Ocorrências de Fornecedores</title>
<link rel="Stylesheet" type="Text/Css" href="../estilo.css">
</head>
<body background="../midia/bg.jpg" marginwidth="0" marginheight="0">
<form action="CadGestaoFornecedorOcorrencias.php" method="post" name="CadGestaoFornecedor">
<table cellpadding="3" border="0" width="100%" summary="">
	<!-- Corpo -->
	<tr>
		<td class="textonormal">
			<table border="0" cellspacing="0" cellpadding="3" summary="" width="100%">
				<tr>
	      	<td class="textonormal">
	        	<table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" class="textonormal" bgcolor="#FFFFFF" width="100%" summary="">
	          	<tr>
	            	<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3" colspan="3">
		    					CADASTRO E GESTÃO DE FORNECEDOR - OCORRÊNCIAS
		          	</td>
		        	</tr>
	          	<?php
          		$db   = Conexao();
							$sql  = "SELECT A.CFORTOCODI, A.EFOROCDETA, A.DFOROCDATA, B.EFORTODESC ";
							$sql .= "  FROM SFPC.TBFORNECEDOROCORRENCIA A, SFPC.TBFORNTIPOOCORRENCIA B";
							$sql .= " WHERE A.CFORTOCODI = B.CFORTOCODI AND A.AFORCRSEQU = $Sequencial ORDER BY 3,1";
							$res  = $db->query($sql);
						  if( PEAR::isError($res) ){
								  ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
							}else{
									$Rows = $res->numRows();
									if( $Rows == 0 ){
					            echo "Nenhuma Ocorrência Encontrada.\n";
									}else{
          						for( $i=0;$i<$Rows;$i++ ){
													$Linha     = $res->fetchRow();
	          	      			$Codigo    = $Linha[0];
	          	      			$Detalhe   = $Linha[1];
	          	      			$Data      = $Linha[2];
	          	      			$Descricao = $Linha[3];
	          	      			if( $i == 0 ){
									            echo "			<tr>\n";
															echo "				<td align=\"center\" bgcolor=\"#DCEDF7\" colspan=\"3\" class=\"titulo3\">OCORRÊNCIAS</td>\n";
															echo "			</tr>\n";
									            echo "<tr>\n";
								        	    echo "  <td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"11%\" align=\"center\">DATA</td>\n";
									            echo "  <td class=\"titulo3\" bgcolor=\"#F7F7F7\">TIPO DE OCORRÊNCIA</td>\n";
								        	    echo "  <td class=\"titulo3\" bgcolor=\"#F7F7F7\">DETALHAMENTO</td>\n";
								        	    echo "</tr>\n";
						        	  	}
							            echo "<tr>\n";
						        	    echo "  <td class=\"textonormal\" bgcolor=\"#F7F7F7\" align=\"center\" valign=\"top\">".substr($Data,8,2)."/".substr($Data,5,2)."/".substr($Data,0,4)."</td>\n";
							            echo "  <td class=\"textonormal\" bgcolor=\"#F7F7F7\" valign=\"top\">".strtoupper2($Descricao)."</td>\n";
						        	    echo "  <td class=\"textonormal\" bgcolor=\"#F7F7F7\" valign=\"top\">$Detalhe</td>\n";
						        	    echo "</tr>\n";
		                	}
		              }
							}
            	$db->disconnect();
          		?>
			      	<tr>
			        	<td colspan="3" align="right">
			          	<input type="button" value="Voltar" class="botao" onclick="javascript:voltar();">
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
function voltar(){
	self.close();
}
//-->
</script>
</body>
</html>

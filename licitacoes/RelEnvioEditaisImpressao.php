<?php  
#----------------------------------------------------------------------------
# Portal da DGCO
# Programa: RelEnvioEditaisImpressao.php
# Autor:    Roberta Costa
# Data:     24/05/03
# Objetivo: Programa de Relatório de Envio de Editais Via Correio Eletrônico
# OBS.:     Tabulação 2 espaços
#----------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST"){
		$Espaco         = $_POST['Espaco'];
}else{
		$Processo       = $_GET['Processo'];
		$AnoProcesso    = $_GET['AnoProcesso'];
		$GrupoCodigo    = $_GET['GrupoCodigo'];
		$ComissaoCodigo = $_GET['ComissaoCodigo'];
		$OrgaoCodigo    = $_GET['OrgaoCodigo'];
		$ModalCodigo    = $_GET['ModalCodigo'];  
		$ListaCodigo    = $_GET['ListaCodigo'];  
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "RelEnvioEditaisImpressao.php";
?>
<html>
<body marginwidth="0" marginheight="0">
<link rel="stylesheet" type="text/css" href="../estilo.css">
<form action="RelEnvioEditaisImpressao.php" method="post" name="Relatorio">
<p class="titulo3" align="center">
  Prefeitura da Cidade do Recife<br><br>
  RELATÓRIO DE ENVIO DE EDITAIS<br><br>
  <a href="javascript:Fecha()"><img src="../midia/brasao.jpg" width="50" height="40" border="0"></a>
<p class="titulo3" align="right">
	Data: <?phpecho date("d/m/Y H:i");?>
</p>
<hr>
<table border="0" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal">
	<tr>
  	<td>
  		<table class="textonormal" border="0" align="left" class="caixa">
    		<tr>
      		<td class="textonormal" bgcolor="#DCEDF7">Comissão</td>
      		<?php
          	$db     = Conexao();
          	$sql    = "SELECT ECOMLIDESC FROM SFPC.TBCOMISSAOLICITACAO ";
						$sql   .= "WHERE CCOMLICODI = $ComissaoCodigo"; 
						$result = $db->query($sql);
						if( PEAR::isError($result) ){
						    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
						}
						while( $Linha = $result->fetchRow() ){
					?>		
          <td class="textonormal"><?php echo $Linha[0]; ?></td>
		  <?php } ?>
  			</tr>
    		<tr>
      		<td class="textonormal" bgcolor="#DCEDF7">Processo</td>
          <td class="textonormal"><?php echo substr($Processo + 10000,1);?></td>
  			</tr>
    		<tr>
      		<td class="textonormal" bgcolor="#DCEDF7">Ano</td>
          <td class="textonormal"><?php echo $AnoProcesso?></td>
  			</tr>
    		<tr>
      		<td class="textonormal" bgcolor="#DCEDF7">Participantes</td>
      	</tr>	
      	<tr>
      	  <td><input type="hidden" name="espaço" size="45"></td>
          <td class="textonormal">
					<?php
						# Mostra os participantes #
						$sql    = "SELECT ELISOLNOME, CLISOLCNPJ, CLISOLCCPF ";  
						$sql   .= "FROM SFPC.TBLISTASOLICITAN ";
						$sql   .= "WHERE CLICPOPROC = $Processo AND ALICPOANOP = $AnoProcesso ";
						$sql   .= "AND CGREMPCODI = $GrupoCodigo AND CCOMLICODI = $ComissaoCodigo ";
						$sql   .= "AND CORGLICODI = $OrgaoCodigo AND FLISOLENVI = 'S' ";
						$sql   .= "ORDER BY ELISOLNOME";
						$result = $db->query($sql);
						if( PEAR::isError($result) ){
						    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
						}
						$Rows = $result->numRows();
						if( $Rows == 0 ){
							  $Valor = 1;
					  }else{	 
								while( $Linha = $result->fetchRow() ){
										 echo $Linha[0]." - ";
										 if( $Linha[2] == "" ){
 		                     echo "CNPJ: ".$Linha[1]."<br>\n";
 		                 }else{	
 		                     echo "CPF: ".$Linha[2]."<br>\n";
 		                 }
								} 
						}		
						$db->disconnect();
					?>
          </td>
  			</tr>
      </table>  	      	
	  </td>
	</tr>
</table>
</form>
</body>
</html>

<script language="javascript">
<!--
self.print();
function Fecha(){
	window.close();
}
//-->
</script>

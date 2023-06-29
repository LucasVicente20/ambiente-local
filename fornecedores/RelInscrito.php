<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: RelInscrito.php
# Autor:    Roberta Costa
# Data:     26/10/04
# Objetivo: Programa de Impressão dos Fornecedores Inscritos
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------
# Alterado: Daniel Augusto
# Data:		16/05/2023
# Objetivo: Tarefa Redmine 282903
# -----------------------------------------------------------------------------------------------------------------------------------------------
# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/fornecedores/RelInscrito.php' );
AddMenuAcesso( '/fornecedores/RelInscritoPdf.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST"){
		$Botao         = $_POST['Botao'];
		$DataIni       = $_POST['DataIni'];
		$DataFim       = $_POST['DataFim'];
		$Situacao      = $_POST['Situacao'];
		$TodasSituacao = $_POST['TodasSituacao'];
}else{
		$Mens     = $_GET['Mens'];
		$Mensagem = urldecode($_GET['Mensagem']);
		$Tipo			= $_GET['Tipo'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

if( $Botao == "Limpar" ){
	  header("location: RelInscrito.php");
	  exit;
}elseif( $Botao == "Imprimir" ){
		$Mens				= 0;
		$Mensagem 	= "Informe: ";
		if( $DataIni == "" ){
		  	$Mens 		 = 1;
		  	$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.RelInscrito.DataIni.focus();\" class=\"titulo2\">Data Inicial do Período</a>";
		}else{
				$MensErro = ValidaData($DataIni);
				if( $MensErro != "" ){
						$Mens      = 1;
				  	$Tipo      = 2;
						$Mensagem .= "<a href=\"javascript:document.RelInscrito.DataIni.focus();\" class=\"titulo2\">Data Inicial Válida</a>";
				}
		}
		if( $DataFim == "" ){
				if( $Mens == 1 ){ $Mensagem .= ", "; }
				$Mens 		 = 1;
		  	$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.RelInscrito.DataFim.focus();\" class=\"titulo2\">Data Final do Período</a>";
		}else{
				$MensErro = ValidaData($DataFim);
				if( $MensErro != "" ){
						if( $Mens == 1 ){ $Mensagem .= ", "; }
						$Mens      = 1;
				  	$Tipo      = 2;
						$Mensagem .= "<a href=\"javascript:document.RelInscrito.DataFim.focus();\" class=\"titulo2\">Data Final Válida</a>";
				}
		}
		if( ( $DataFim < $DataIni ) and ( $DataFim != "" and $DataIni != "" ) ){
				if( $Mens == 1 ){ $Mensagem .= ", "; }
				$Mens      = 1;
		  	$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.RelInscrito.DataIni.focus();\" class=\"titulo2\">Data Inicial menor que a Data Final</a>";
		}

		if( $TodasSituacao == "" and $Situacao == "" ){
				if( $Mens == 1 ){ $Mensagem .= ", "; }
				$Mens      = 1;
		  	$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.RelInscrito.Situacao.focus();\" class=\"titulo2\">Uma Situação ou Marque Todas as Situações</a>";
		}else{
				if( $TodasSituacao != "" and $Situacao != "" ){
						if( $Mens == 1 ){ $Mensagem .= ", "; }
						$Mens      = 1;
				  	$Tipo      = 2;
						$Mensagem .= "<a href=\"javascript:document.RelInscrito.Situacao.focus();\" class=\"titulo2\">Uma Situação ou Marque Todas as Situações</a>";
				}
		}
		if( $Mens == 0 ){
				if( $TodasSituacao == 1 ){ $Situacao = "T"; }
				$Url = "RelInscritoPdf.php?DataIni=$DataIni&DataFim=$DataFim&Situacao=$Situacao&".mktime();
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
<script language="javascript" src="../janela.js" type="text/javascript"></script>
<script language="javascript" type="">
function enviar(valor){
	document.RelInscrito.Botao.value=valor;
	document.RelInscrito.submit();
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="RelInscrito.php" method="post" name="RelInscrito">
<br><br><br><br><br>
<table cellpadding="3" border="0" summary="">
  <!-- Caminho -->
  <tr>
    <td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
    <td align="left" class="textonormal" colspan="2">
      <font class="titulo2">|</font>
      <a href="../index.php"><font color="#000000">Página Principal</font></a> > Fornecedores > Inscrição > Listagem
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
      <table  border="0" cellspacing="0" cellpadding="3" bgcolor="#ffffff" summary="">
        <tr>
	      	<td class="textonormal">
	        	<table border="1" cellpadding="3" cellspacing="0"  bordercolor="#75ADE6" summary="" class="textonormal" width="100%">
	          	<tr>
	            	<td align="center" bgcolor="#75ADE6" valign="middle" colspan="4" class="titulo3">
		    					LISTAGEM DOS FORNECEDORES INSCRITOS
		          	</td>
		        	</tr>
	  	      	<tr>
	    	      	<td class="textonormal">
	      	    		<p align="justify">
	        	    		Digite o período desejado a ser consultado e clique no botão "Imprimir".
										Para limpar os campos, clique no botão "Limpar".<br><br>
						        Se você não possui o Acrobat Reader, clique <a href="javascript:janela('../pdf.php','Relatorio',400,400,1,0)" class="titulo2">AQUI</a> para fazer o download.
	          	   	</p>
	          		</td>
	          	</tr>
		        	<tr>
								<td class="textonormal">
									<table class="textonormal" border="0" align="left" summary="" width="100%">
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" height="20" width="30%">Período<span style="color: red;">*</span></td>
			    	      		<td class="textonormal">
			    	      			<?php
			    	      			$DataMes = DataMes();
			    	      			if( $DataIni == "" ){ $DataIni = $DataMes[0]; }
												if( $DataFim == "" ){ $DataFim = $DataMes[1]; }
												$URLIni = "../calendario.php?Formulario=RelInscrito&Campo=DataIni";
												$URLFim = "../calendario.php?Formulario=RelInscrito&Campo=DataFim";
												?>
												<input type="text" name="DataIni" size="10" maxlength="10" value="<?php echo $DataIni;?>" class="textonormal">
												<a href="javascript:janela('<?php echo $URLIni ?>','Calendario',220,170,1,0)"><img src="../midia/calendario.gif" border="0" alt=""></a>
												&nbsp;a&nbsp;
												<input type="text" name="DataFim" size="10" maxlength="10" value="<?php echo $DataFim;?>" class="textonormal">
												<a href="javascript:janela('<?php echo $URLFim ?>','Calendario',220,170,1,0)"><img src="../midia/calendario.gif" border="0" alt=""></a>
											</td>
				          	</tr>
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" height="20">Situação</td>
			    	      		<td class="textonormal">
				              	<select name="Situacao" class="textonormal">
				              		<option value="">Selecione uma Situação...</option>
				              		<?php
				              		$db  = Conexao();
  												$sql = "SELECT CPREFSCODI,EPREFSDESC FROM SFPC.TBPREFORNTIPOSITUACAO ORDER BY EPREFSDESC";
  												$res = $db->query($sql);
												  if( PEAR::isError($res) ){
														  ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
													}else{
															while( $Linha = $res->fetchRow() ){
					          	      			$Descricao = $Linha[1];
					          	      			if( $Linha[0] == $Situacao ){
										    	      			echo"<option value=\"$Linha[0]\" selected>$Descricao</option>\n";
								      	      		}else{
																			echo"<option value=\"$Linha[0]\">$Descricao</option>\n";
								      	      		}
						                	}
													}
			  	              	$db->disconnect();
				              		?>
				              	</select>
												<input type="checkbox" class="textonormal" name="TodasSituacao" value="1" <?php if( $TodasSituacao == 1 ){ echo "checked";}?>> Todas as Situações
											</td>
										</tr>
									</table>
								</td>
        			</tr>
				      <tr>
			      		<td align="right" class="textonormal">
			    				<input type="button" name="Imprimir" value="Imprimir" class="botao" onclick="javascript:enviar('Imprimir');">
			    				<input type="button" name="Limpar" value="Limpar" class="botao" onclick="javascript:enviar('Limpar');">
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
</body>
</html>

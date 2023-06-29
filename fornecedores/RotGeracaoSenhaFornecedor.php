<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: RotGeracaoSenhaFornecedor.php
# Autor:    Rossana Lira
# Data:     27/07/04
# Objetivo: Programa de Geração de Senha de Fornecedor
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/fornecedores/RelGeracaoSenhaFornecedorPdf.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST"){
		$Botao        	= $_POST['Botao'];
		$Critica      	= $_POST['Critica'];
		$Sequencial    	= $_POST['Sequencial'];
		$TipoForn    		= $_POST['TipoForn'];
		$Impressao			=	$_POST['Impresssao'];
		$CNPJCPF				= $_POST['CNPJCPF'];
		$CNPJCPFForm		= $_POST['CNPJCPFForm'];
		$Razao					=	$_POST['Razao'];
		$Email					= $_POST['Email'];
}else{
		$Mens         	= $_GET['Mens'];
		$Mensagem     	= $_GET['Mensagem'];
		$Tipo			     	= $_GET['Tipo'];
		$Sequencial			= $_GET['Sequencial'];
		$TipoForn				=	$_GET['TipoForn'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

if( $Botao == "Voltar" ){
	  header("location: RotGeracaoSenhaFornecedorSelecionar.php");
	  exit;
}

if( $Critica != 0 ){
		# Atualiza a senha do Usuário #
		$Senha				   = CriaSenha();
		$SenhaCript		   = crypt($Senha,"P");
		$Data   			   = date("Y-m-d H:i:s");
		$DataExpSenhaInv    = substr($Data, 0, 10);
		$DataExpSenhaInv = date("Y-m-d", strtotime("-1 day", strtotime($DataExpSenhaInv)));
		//var_dump($DataExpSenhaInv);exit;
		//$DataExpSenhaInv = DataInvertida($DataExpSenha,6,4);

		$db = Conexao();
		$db->query("BEGIN TRANSACTION");
		if( $TipoForn == "INSC" ){
				$sql  = "UPDATE SFPC.TBPREFORNECEDOR ";
				$sql .= "   SET NPREFOSENH = '$SenhaCript', CGREMPCODI =  ".$_SESSION['_cgrempcodi_'].", ";
				$sql .= "       CUSUPOCODI =  ".$_SESSION['_cusupocodi_'].", DPREFOEXPS = '$DataExpSenhaInv', ";
				$sql .= "       APREFONTEN = 0, TPREFOULAT = '$Data'  ";
				$sql .= " WHERE APREFOSEQU = $Sequencial";
		}else{
				$sql  = "UPDATE SFPC.TBFORNECEDORCREDENCIADO ";
				$sql .= "   SET NFORCRSENH = '$SenhaCript', CGREMPCODI = ".$_SESSION['_cgrempcodi_'].",";
				$sql .= "       CUSUPOCODI =  ".$_SESSION['_cusupocodi_'].", DFORCREXPS = '$DataExpSenhaInv', ";
				$sql .= "       AFORCRNTEN = 0, TFORCRULAT = '$Data' ";
				$sql .= " WHERE AFORCRSEQU = $Sequencial";
		}
		$result = $db->query($sql);
		if( PEAR::isError($result) ){
				$db->query("ROLLBACK");
		    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}
		$db->query("COMMIT");
		$db->query("END TRANSACTION");
		$db->disconnect();

		# Envia a senha pelo e-mail do usuário #
    if( strlen($CNPJCPFForm) == 14 ){ $CpfCgcMail = "CPF"; }else{ $CpfCgcMail = "CNPJ"; }
		EnviaEmail("$Email","Senha temporária de inscrição no Portal de Compras da Prefeitura do Recife","\t Nome/Razão Social: $Razao\n\t $CpfCgcMail: $CNPJCPFForm\n\t Senha: $Senha ","from: portalcompras@recife.pe.gov.br");
		$Mens      = 1;
		$Tipo      = 1;
		$Mensagem  = "A senha temporária do Fornecedor ou Inscrito gerada foi: ".$Senha.".<br>";
		$Url = "RelGeracaoSenhaFornecedorPdf.php?CNPJCPF=$CNPJCPF&Razao=$Razao&Senha=$Senha";
		if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
		$Mensagem .= " Para imprimir a senha gerada clique <a href=\"$Url\" class=\"titulo1\">AQUI</a>";
}

if( $Critica == 0 ){
		$db	= Conexao();
		# Busca os Dados da Tabela de Inscritos ou de Fornecedor #
		if( $TipoForn == "INSC" ){
				$sql  = " SELECT APREFOCCGC, APREFOCCPF, NPREFORAZS, NPREFOMAIL FROM SFPC.TBPREFORNECEDOR ";
				$sql .= " WHERE  APREFOSEQU = $Sequencial ";
		}else{
				$sql  = " SELECT AFORCRCCGC, AFORCRCCPF, NFORCRRAZS, NFORCRMAIL FROM SFPC.TBFORNECEDORCREDENCIADO ";
				$sql .= " WHERE  AFORCRSEQU = $Sequencial ";
		}
 		$result 	= $db->query($sql);
		if( PEAR::isError($result) ){
	    	ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
				$linha = $result->fetchRow();
				if( $linha[0] <> 0 ){
						$CNPJCPF     = $linha[0];
						$CNPJCPFForm = FormataCNPJ($linha[0]);

				}else{
						$CNPJCPF     = $linha[1];
						$CNPJCPFForm = FormataCPF($linha[1]);
				}

				$Razao		 = $linha[2];
				$Email		 = $linha[3];
				if( ($Email == "NULL") or ($Email == "null") or ($Email == null) ){
						$Email	 = "";
				}
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
<script language="javascript" src="../janela.js" type="text/javascript"></script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="RotGeracaoSenhaFornecedor.php" method="post" name="Geracao">
<br><br><br><br><br>
<table cellpadding="3" border="0">
  <!-- Caminho -->
  <tr>
    <td width="100"><img border="0" src="../midia/linha.gif"></td>
    <td align="left" class="textonormal" colspan="2">
      <font class="titulo2">|</font>
      <a href="../index.php"><font color="#000000">Página Principal</font></a> > Fornecedores > Geração Senha
    </td>
	  <td></td>
  </tr>
  <!-- Fim do Caminho-->

	<!-- Erro -->
	<?php if ( $Mens == 1 ) {?>
	<tr>
	  <td width="100"></td>
	  <td align="left" colspan="2"><?php if ( $Mens == 1 ) { ExibeMens($Mensagem,$Tipo,1); } ?></td>
	</tr>
	<?php } ?>
	<!-- Fim do Erro -->

	<!-- Corpo -->
	<tr>
		<td width="100"></td>
		<td class="textonormal">
      <table border="0" cellspacing="0" cellpadding="3" bgcolor="#ffffff">
        <tr>
	      	<td class="textonormal">
	        	<table border="1" cellpadding="3" cellspacing="0"  bordercolor="#75ADE6" class="textonormal" width="100%" summary="">
	          	<tr>
	            	<td align="center" bgcolor="#75ADE6" valign="middle" colspan="3" class="titulo3">
		    					GERAÇÃO SENHA - FORNECEDOR
		          	</td>
		        	</tr>
	  	      	<tr>
	    	      	<td class="textonormal" colspan="3">
	      	    		<p align="justify">
	        	    		Para confirmar a geração da senha do Fornecedor, clique no botão "Gerar". Para retornar a tela anterior clique no botão "Voltar".<br>
	        	    		Se o fornecedor possuir e-mail, a senha também será enviada para o seu e-mail.<br><br>
	        	    		Se você não possui o Acrobat Reader, clique <a href="javascript:janela('../pdf.php','Relatorio',400,400,1,0)" class="titulo2">AQUI</a> para fazer o download.
	          	   	</p>
	          		</td>
	          	</tr>
		        	<tr>
	  	        	<td>
	    	      		<table class="textonormal" cellpadding="0" cellspacing="2" border="0" align="left" width="100%">
	      	      		<tr>
	        	      		<td class="textonormal" bgcolor="#DCEDF7" height="20">CNPJ/CPF</td>
	          	    		<td class="textonormal"><?php echo $CNPJCPFForm ?></td>
	            	  		<td class="textonormal">
	            	  			<input type="hidden" name="Critica" value="1">
            						<input type="hidden" name="CNPJCPF" value="<?php echo $CNPJCPF ?>">
            						<input type="hidden" name="CNPJCPFForm" value="<?php echo $CNPJCPFForm ?>">
	            	  		</td>
	            			</tr>
										<tr>
	        	      		<td class="textonormal" bgcolor="#DCEDF7" width="30%" height="20"">Razão Social/Nome</td>
	          	    		<td class="textonormal">
	          	    			 <?php echo $Razao ?>
            						<input type="hidden" name="Razao" value="<?php echo $Razao ?>">
	          	    		</td>
										</tr>
										<tr>
        	      			<td class="textonormal" bgcolor="#DCEDF7" height="20">E-mail</td>
	          	    		<td class="textonormal">
	          	    			<?php if( $Email != "" ){ echo $Email; }else{ echo "NÃO INFORMADO"; } ?>
            						<input type="hidden" name="Email" value="<?php echo $Email ?>">
	          	    		</td>
	          	    	</tr>
	            		</table>
		          	</td>
		        	</tr>
      	      <tr>
    	      		<td align="right" colspan="3">
            			<input type="hidden" name="Sequencial" value="<?php echo $Sequencial ?>">
            			<input type="hidden" name="TipoForn" value="<?php echo $TipoForn ?>">
  	      				<input type="button" name="Gerar" value="Gerar" class="botao" onclick="javascript:enviar('Gerar');">
  	      				<input type="button" name="Voltar" value="Voltar" class="botao" onclick="javascript:enviar('Voltar');">
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

<script language="javascript" type="">
<!--
function enviar(valor){
	document.Geracao.Botao.value=valor;
	document.Geracao.submit();
}

<?php MenuAcesso(); ?>
//-->
</script> 

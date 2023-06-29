<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: ConsAvisosDownloaD.php
# Autor:    Roberta Costa
# Data:     06/05/2003
# Objetivo: Programa de Pesquisa de Avisos de Licitação
# OBS.:     Tabulação 2 espaços
# -------------------------------------------------------------------------
# Alterado: Carlos Abreu
# Data:     25/08/2006 - Mudança de Variáveis GET para POST
# -------------------------------------------------------------------------
# Alterado: Ariston Cordeiro
# Data:     29/08/2008 - Checar caso alguma variável em post requerida não esteja sendo fornecida
# -------------------------------------------------------------------------
# Alterado: Lucas Baracho
# Data:		24/10/2018
# Objetivo: Tarefa Redmine 73662
# -------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

$_SESSION['ValidaArquivoDownload'] = "ValidaArquivoDownload";

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/licitacoes/ConsAvisosDocumentos.php' );
AddMenuAcesso( '/licitacoes/ConsAvisosArquivo.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST"){
		$Botao                = $_POST['Botao'];
		$Critica              = $_POST['Critica'];
		$RazaoSocial          = strtoupper2(trim($_POST['RazaoSocial']));
		$FornCad              = $_POST['FornCad'];
		$CPF_CNPJ             = $_POST['CPF_CNPJ'];
		$CnpjCpf              = $_POST['CnpjCpf'];
		$Endereco             = strtoupper2(trim($_POST['Endereco']));
		$Email                = trim($_POST['Email']);
		$Telefone             = trim($_POST['Telefone']);
		$Fax                  = trim($_POST['Fax']);
		$NomeContato          = strtoupper2($_POST['NomeContato']);
		$Participacao         = $_POST['Participacao'];
		$Objeto               = $_POST['Objeto'];
		$OrgaoLicitanteCodigo = $_POST['OrgaoLicitanteCodigo'];
		$ComissaoCodigo       = $_POST['ComissaoCodigo'];
		$ModalidadeCodigo     = $_POST['ModalidadeCodigo'];
		$GrupoCodigo          = $_POST['GrupoCodigo'];
		$LicitacaoProcesso    = $_POST['LicitacaoProcesso'];
		$LicitacaoAno         = $_POST['LicitacaoAno'];
		$DocumentoCodigo      = $_POST['DocumentoCodigo'];
		$VerificaCPF          = $_POST['VerificaCPF'];
		$DocumentoCodigo      = $_POST['DocumentoCodigo'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

$Mens              = 0;
$Mensagem          = "Informe: ";
$FornCad		       = "N";
$SolicitanteCodigo = 0;

if( (is_null($LicitacaoProcesso)) && (is_null($LicitacaoAno)) ){
		header("location: /portalcompras/licitacoes/ConsAvisosPesquisar.php");
		exit;
}

if( $Botao == "Confirmar" ){
	  if( $CnpjCpf == "CPF" ){
	  	  $CPF_CNPJ = FormataCPF_CNPJ($CPF_CNPJ,"CPF");
	  	  $CnpjCpf  = $CPF;
	  	  $Qtd      = strlen($CPF_CNPJ);
   		  if( ($Qtd != 11) and ($Qtd != 0) ){
   		      if( $Mens == 1 ){ $Mensagem .= ", "; }
						$Mens      = 1;
						$Tipo      = 2;
  					$Mensagem .= "<a href=\"javascript:document.Avisos.CPF_CNPJ.focus();\" class=\"titulo2\">CPF com 11 números</a>";
				}elseif( $CPF_CNPJ == "" ){
						if( $Mens == 1 ){ $Mensagem .= ", "; }
						$Mens      = 1;
						$Tipo      = 2;
  				  $Mensagem .= "<a href=\"javascript:document.Avisos.CPF_CNPJ.focus();\" class=\"titulo2\">CPF Válido</a>";
				}else{
					  if( $Mens == 1 ){ $Mensagem .= ", "; }
						$valor = valida_CPF($CPF_CNPJ);
						if( $valor == false ){
							  $Mens      = 1;
							  $Tipo      = 2;
		  					$Mensagem .= "<a href=\"javascript:document.Avisos.CPF_CNPJ.focus();\" class=\"titulo2\">CPF Válido</a>";
		  			}
  		  }
  			$CnpjCpf = "CPF";
		}elseif($CnpjCpf == "CNPJ"){
				$CPF_CNPJ  = FormataCPF_CNPJ($CPF_CNPJ,"CNPJ");
				$CnpjCpf   = $CNPJ;
				$Qtd       = strlen($CPF_CNPJ);
				if( ($Qtd != 14) and ($Qtd != 0)  ){
						if( $Mens == 1 ){ $Mensagem .= ", "; }
						$Mens      = 1;
						$Tipo      = 2;
					  $Mensagem .= "<a href=\"javascript:document.Avisos.CPF_CNPJ.focus();\" class=\"titulo2\">CNPJ com 14 números</a>";
				}elseif( $CPF_CNPJ == "" ){
						if( $Mens == 1 ){ $Mensagem .= ", "; }
						$Mens      = 1;
						$Tipo      = 2;
					  $Mensagem .= "<a href=\"javascript:document.Avisos.CPF_CNPJ.focus();\" class=\"titulo2\">CNPJ Válido</a>";
				}else{
					  if( $Mens == 1 ){ $Mensagem .= ", "; }
						$valor = valida_CNPJ($CPF_CNPJ);
						if( $valor == false ){
							  $Mens      = 1;
							  $Tipo      = 2;
								$Mensagem .= "<a href=\"javascript:document.Avisos.CPF_CNPJ.focus();\" class=\"titulo2\">CNPJ Válido</a>";
						}
				}
				$CnpjCpf = "CNPJ";
    }
	  if( $valor === true ){
				if( 
						(is_null($LicitacaoProcesso))&&
						(is_null($LicitacaoAno))&&
						(is_null($GrupoCodigo))&&
						(is_null($ComissaoCodigo))&&
						(is_null($OrgaoLicitanteCodigo)) 
				){
					header("Location: ConsAvisosPesquisar.php");
					exit;
				}
		  	# Verifica a existência do CPF/CNPJ #
		  	$db   = Conexao();
		  	$sql 	= "SELECT CLISOLCODI, ELISOLNOME, ELISOLMAIL, ELISOLENDE, ALISOLFONE, ";
				$sql .= "  			ALISOLNFAX, NLISOLCONT, ALISOLNUME, ELISOLCOMP, ELISOLBAIR, ";
				$sql .= "  			NLISOLCIDA, CLISOLESTA, FLISOLPART ";
		  	$sql .= "  FROM SFPC.TBLISTASOLICITAN ";
		    $sql .= " WHERE CLICPOPROC = $LicitacaoProcesso AND ALICPOANOP = $LicitacaoAno ";
		  	$sql .= "   AND CGREMPCODI = $GrupoCodigo AND CCOMLICODI = $ComissaoCodigo ";
		  	$sql .= "   AND CORGLICODI = $OrgaoLicitanteCodigo AND ";
			  if( $CnpjCpf == "CPF" ){
				  	$sql .= " CLISOLCCPF = '$CPF_CNPJ' ";
		  	}elseif( $CnpjCpf == "CNPJ" ){
				  	$sql .= " CLISOLCNPJ = '$CPF_CNPJ' ";
		  	}
		  	$result = $db->query($sql);
			  if( PEAR::isError($result) ){
				    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
				}else{
						$Rows = $result->numRows();
						if( $Rows != 0 ){
								while( $Linha = $result->fetchRow() ){
									  $SolicitanteCodigo = $Linha[0];
									  $RazaoSocial       = $Linha[1];
									  $Email             = $Linha[2];
									  $Endereco          = $Linha[3];
									  $Telefone          = trim($Linha[4]);
									  $Fax               = trim($Linha[5]);
									  $NomeContato       = $Linha[6];
									  $Numero            = $Linha[7];
									  $Complemento       = $Linha[8];
									  $Bairro            = $Linha[9];
									  $Cidade            = $Linha[10];
									  $Estado            = $Linha[11];
									  $Participacao      = $Linha[12];
								}
						}else{
						  	$sql  = "SELECT NFORCRRAZS, CCEPPOCODI, EFORCRLOGR, AFORCRNUME, ";
						  	$sql .= "       EFORCRCOMP, EFORCRBAIR, NFORCRCIDA, CFORCRESTA, ";
						  	$sql .= "  			AFORCRCDDD, AFORCRTELS, AFORCRNFAX, NFORCRMAIL, ";
						  	$sql .= "  			NFORCRCONT ";
						  	$sql .= "  FROM SFPC.TBFORNECEDORCREDENCIADO WHERE ";
						  	if( $CnpjCpf == "CNPJ" ){
						  			$sql .= " AFORCRCCGC = '$CPF_CNPJ'";
						  	}elseif( $CnpjCpf == "CPF" ){
						  			$sql .= " AFORCRCCPF = '$CPF_CNPJ'";
						  	}
						  	$result = $db->query($sql);
							  if( PEAR::isError($result) ){
								    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
								}
								while( $Linha = $result->fetchRow() ){
									  $RazaoSocial = $Linha[0];
									  $CEP         = $Linha[1];
									  $Endereco    = $Linha[2];
									  $Numero      = $Linha[3];
									  $Complemento = $Linha[4];
									  $Bairro      = $Linha[5];
									  $Cidade      = $Linha[6];
									  $Estado      = $Linha[7];
									  $DDD         = $Linha[8];
									  if( $Linha[9]  != "" ){ $Telefone = substr($DDD." ".$Linha[9],0,25); }
									  if( $Linha[10] != "" ){ $Fax      = substr($DDD." ".$Linha[10],0,25); }
									  $Email       = $Linha[11];
									  $NomeContato = $Linha[12];
								}
						}
				}
				$VerificaCPF = "S";
				$db->disconnect();
		}
}elseif( $Botao == "Enviar" ){
	  # Se não existe o CPF/CNPJ cadastrado executa as críticas #
		if( $Rows == 0 ){
				if( $RazaoSocial == "" ) {
						if( $Mens == 1 ){ $Mensagem .= ", "; }
				    $Mens      = 1;
				    $Tipo      = 2;
		  			$Mensagem .= "<a href=\"javascript:document.Avisos.RazaoSocial.focus();\" class=\"titulo2\">Razão Social</a>";
			  }
				if( $Endereco == "" ) {
						if( $Mens == 1 ){ $Mensagem .= ", "; }
				    $Mens      = 1;
				    $Tipo      = 2;
		  			$Mensagem .= "<a href=\"javascript:document.Avisos.Endereco.focus();\" class=\"titulo2\">Endereço</a>";
		  	}
			  if( $Email == "" ){
				    if( $Mens == 1 ){ $Mensagem .= ", "; }
				    $Mens      = 1;
				    $Tipo      = 2;
		    		$Mensagem .= "<a href=\"javascript:document.Avisos.Email.focus();\" class=\"titulo2\">E-mail</a>";
				}elseif(! strchr($Email, "@")){
				    if( $Mens == 1 ){ $Mensagem .= ", "; }
				    $Mens      = 1;
				    $Tipo      = 2;
		    		$Mensagem .= "<a href=\"javascript:document.Avisos.Email.focus();\" class=\"titulo2\">E-mail Válido</a>";
				}
				if( $Telefone == "" ) {
				    if( $Mens == 1 ){ $Mensagem .= ", "; }
				    $Mens      = 1;
				    $Tipo      = 2;
		  			$Mensagem .= "<a href=\"javascript:document.Avisos.Telefone.focus();\" class=\"titulo2\">Telefone</a>";
			  }
			  if( $Fax == "" ) {
						if( $Mens == 1 ){ $Mensagem .= ", "; }
				    $Mens      = 1;
				    $Tipo      = 2;
		    		$Mensagem .= "<a href=\"javascript:document.Avisos.Fax.focus();\" class=\"titulo2\">Fax</a>";
			  }
			  if( $NomeContato == "" ) {
						if( $Mens == 1 ){ $Mensagem .= ", "; }
				    $Mens      = 1;
				    $Tipo      = 2;
		  			$Mensagem .= "<a href=\"javascript:document.Avisos.NomeContato.focus();\" class=\"titulo2\">Nome de Contato</a>";
			  }
		}else{
				$FornCad = "S";
		}
		$Data = date("Y-m-d G:i:s");
		if( $Mens == 0 ){
				$db = Conexao();
				$db->query("BEGIN TRANSACTION");
				if( $FornCad == "S" ){
						$sql 	  = "UPDATE SFPC.TBLISTASOLICITAN ";
						$sql   .= "   SET FLISOLPART = '$Participacao', TLISOLDREC = '$Data', ";
						if( $CnpjCpf == "CPF" ){
								$sql   .= " CLISOLCCPF = '$CPF_CNPJ' ";
						}elseif($CnpjCpf == "CNPJ"){
								$sql   .= " CLISOLCNPJ = '$CPF_CNPJ' ";
				    }
						$sql   .= " WHERE CLICPOPROC = $LicitacaoProcesso AND ALICPOANOP = $LicitacaoAno";
						$sql   .= "   AND CGREMPCODI = $GrupoCodigo AND CCOMLICODI = $ComissaoCodigo ";
						$sql   .= "   AND CORGLICODI = $OrgaoLicitanteCodigo AND CLISOLCODI = $SolicitanteCodigo ";
						$result = $db->query($sql);
						if( PEAR::isError($result) ){
								$db->query("ROLLBACK");
						    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
						}
				}else{
						$sql   = "SELECT CLISOLCODI FROM SFPC.TBLISTASOLICITAN ";
						$sql  .= " WHERE CLICPOPROC = $LicitacaoProcesso AND ALICPOANOP = $LicitacaoAno";
						$sql  .= "   AND CGREMPCODI = $GrupoCodigo AND CCOMLICODI = $ComissaoCodigo ";
						$sql  .= "   AND CORGLICODI = $OrgaoLicitanteCodigo AND ";
						if( $CnpjCpf == "CPF" ){
								$sql  .= " CLISOLCCPF = '$CPF_CNPJ' ";
						}elseif($CnpjCpf == "CNPJ"){
								$sql  .= " CLISOLCNPJ = '$CPF_CNPJ'";
						}
						$result = $db->query($sql);
						if( PEAR::isError($result) ){
						    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
						}else{
								$Linha             = $result->fetchRow();
								$SolicitanteCodigo = $Linha[0];
								if( $SolicitanteCodigo == "" ){
										$sql   = "SELECT MAX(CLISOLCODI) FROM SFPC.TBLISTASOLICITAN ";
										$sql  .= " WHERE CLICPOPROC = $LicitacaoProcesso AND ALICPOANOP = $LicitacaoAno";
										$sql  .= "   AND CGREMPCODI = $GrupoCodigo AND CCOMLICODI = $ComissaoCodigo ";
										$sql  .= "   AND CORGLICODI = $OrgaoLicitanteCodigo";
										$result = $db->query($sql);
										if( PEAR::isError($result) ){
										    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
										}else{
												while( $Linha = $result->fetchRow() ){
														$SolicitanteCodigoMax = $Linha[0];
												}
												if( $SolicitanteCodigoMax == "" ){
														$SolicitanteCodigo = 1;
												}else{
														$SolicitanteCodigo = $SolicitanteCodigoMax + 1;
												}

												# Insere na tabela TLISTASOLICITAN #
												if( $CnpjCpf == "CPF" ){
														$CPF  = "'$CPF_CNPJ'";
														$CNPJ = "NULL";
												}elseif($CnpjCpf == "CNPJ"){
														$CNPJ = "'$CPF_CNPJ'";
														$CPF  = "NULL";
												}
												if( $Numero      == "" ){ $Numero      = "NULL"; }
												if( $Complemento == "" ){ $Complemento = "NULL"; }else{ $Complemento = "'".substr($Complemento,0,20)."'"; }
												if( $Bairro      == "" ){ $Bairro      = "NULL"; }else{ $Bairro      = "'".substr($Bairro,0,60)."'"; }
												if( $Cidade      == "" ){ $Cidade      = "NULL"; }else{ $Cidade      = "'".substr($Cidade,0,30)."'"; }
												if( $Estado      == "" ){ $Estado      = "NULL"; }else{ $Estado      = "'".substr($Estado,0,2)."'"; }

												$RazaoSocial  = substr($RazaoSocial,0,60);
												$Email        = substr($Email,0,255);
												$Endereco     = substr($Endereco,0,60);
												$Telefone			= substr($Telefone,0,25);
												$Fax			    = substr($Fax,0,25);
												$NomeContato  = substr($NomeContato,0,60);

												$sql  = "INSERT INTO SFPC.TBLISTASOLICITAN ( ";
												$sql .= "clicpoproc, alicpoanop, cgrempcodi, ccomlicodi, ";
												$sql .= "corglicodi, clisolcodi, elisolnome, clisolcnpj, ";
												$sql .= "clisolccpf, elisolmail, elisolende, alisolfone, ";
												$sql .= "alisolnfax, nlisolcont, flisolpart, tlisoldrec, ";
												$sql .= "flisolenvi, tlisolulat, alisolnume, elisolcomp, ";
												$sql .= "elisolbair, nlisolcida, clisolesta ";
												$sql .= " ) VALUES ( ";
												$sql .= "$LicitacaoProcesso, $LicitacaoAno, $GrupoCodigo, $ComissaoCodigo, ";
												$sql .= "$OrgaoLicitanteCodigo, $SolicitanteCodigo, '$RazaoSocial', $CNPJ,";
												$sql .= "$CPF, '$Email', '$Endereco', '$Telefone', ";
												$sql .= "'$Fax', '$NomeContato', '$Participacao', '$Data', ";
												$sql .= "'N', '$Data', $Numero, $Complemento, ";
												$sql .= "$Bairro, $Cidade, $Estado )";
												$result = $db->query($sql);
												if( PEAR::isError($result) ){
												    $db->query("ROLLBACK");
												    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
												}else{
														$db->query("COMMIT");
														$db->query("END TRANSACTION");
														$db->disconnect();

														# Redirecionar para página de seleção #
														echo "<html>\n";
														echo "<body>\n";
														echo "<form method=\"post\" action=\"ConsAvisosArquivo.php\" name=\"Arquivo\">\n";
														echo "<input type=\"hidden\" name=\"Objeto\" value=\"$Objeto\">\n";
														echo "<input type=\"hidden\" name=\"OrgaoLicitanteCodigo\" value=\"$OrgaoLicitanteCodigo\">\n";
														echo "<input type=\"hidden\" name=\"ComissaoCodigo\" value=\"$ComissaoCodigo\">\n";
														echo "<input type=\"hidden\" name=\"ModalidadeCodigo\" value=\"$ModalidadeCodigo\">\n";
														echo "<input type=\"hidden\" name=\"GrupoCodigo\" value=\"$GrupoCodigo\">\n";
														echo "<input type=\"hidden\" name=\"LicitacaoProcesso\" value=\"$LicitacaoProcesso\">\n";
														echo "<input type=\"hidden\" name=\"LicitacaoAno\" value=\"$LicitacaoAno\">\n";
														echo "<input type=\"hidden\" name=\"DocumentoCodigo\" value=\"$DocumentoCodigo\">\n";
														echo "<input type=\"hidden\" name=\"SolicitanteCodigo\" value=\"$SolicitanteCodigo\">\n";
														echo "</form>\n";
														echo "</body>\n";
														echo "<script language=\"javascript\">";
														echo "document.Arquivo.submit();";
														echo "</script>";
														echo "</html>\n";
										    		exit;
												}
										}
								}else{
										$sql 	  = "UPDATE SFPC.TBLISTASOLICITAN ";
										$sql   .= "   SET FLISOLPART = '$Participacao', TLISOLDREC = '$Data', ";
										if( $CnpjCpf == "CPF" ){
												$sql   .= " CLISOLCCPF = '$CPF_CNPJ' ";
										}elseif($CnpjCpf == "CNPJ"){
												$sql   .= " CLISOLCNPJ = '$CPF_CNPJ' ";
								    }
										$sql   .= " WHERE CLICPOPROC = $LicitacaoProcesso AND ALICPOANOP = $LicitacaoAno";
										$sql   .= "   AND CGREMPCODI = $GrupoCodigo AND CCOMLICODI = $ComissaoCodigo ";
										$sql   .= "   AND CORGLICODI = $OrgaoLicitanteCodigo AND CLISOLCODI = $SolicitanteCodigo ";
		 							  $result = $db->query($sql);
										if( PEAR::isError($result) ){
										    $db->query("ROLLBACK");
										    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
										}else{
												$db->query("COMMIT");
												$db->query("END TRANSACTION");
												$db->disconnect();

												# Redirecionar para página de seleção #
												echo "<html>\n";
												echo "<body>\n";
												echo "<form method=\"post\" action=\"ConsAvisosArquivo.php\" name=\"Arquivo\">\n";
												echo "<input type=\"hidden\" name=\"Objeto\" value=\"$Objeto\">\n";
												echo "<input type=\"hidden\" name=\"OrgaoLicitanteCodigo\" value=\"$OrgaoLicitanteCodigo\">\n";
												echo "<input type=\"hidden\" name=\"ComissaoCodigo\" value=\"$ComissaoCodigo\">\n";
												echo "<input type=\"hidden\" name=\"ModalidadeCodigo\" value=\"$ModalidadeCodigo\">\n";
												echo "<input type=\"hidden\" name=\"GrupoCodigo\" value=\"$GrupoCodigo\">\n";
												echo "<input type=\"hidden\" name=\"LicitacaoProcesso\" value=\"$LicitacaoProcesso\">\n";
												echo "<input type=\"hidden\" name=\"LicitacaoAno\" value=\"$LicitacaoAno\">\n";
												echo "<input type=\"hidden\" name=\"DocumentoCodigo\" value=\"$DocumentoCodigo\">\n";
												echo "<input type=\"hidden\" name=\"SolicitanteCodigo\" value=\"$SolicitanteCodigo\">\n";
												echo "</form>\n";
												echo "</body>\n";
												echo "<script language=\"javascript\">";
												echo "document.Arquivo.submit();";
												echo "</script>";
												echo "</html>\n";
								    		exit;
										}
				  			}
								$db->query("COMMIT");
								$db->query("END TRANSACTION");
								$db->disconnect();
						}
				}
		}
}
?>
<html>
<?
# Carrega o layout padrão #
layout();
?>
<script language="javascript" type="">
<!--
function enviar(valor){
	document.Avisos.Botao.value=valor;
	document.Avisos.submit();
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" bgcolor="#FFFFFF" text="#000000" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="ConsAvisosDownload.php" method="post" name="Avisos">
<br><br><br><br>
<table cellpadding="3" border="0" summary="">
  <!-- Caminho -->
  <tr>
    <td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
    <td align="left" class="textonormal" colspan="2"><br>
      <font class="titulo2">|</font>
      <a href="../index.php"><font color="#000000">Página Principal</font></a> > Licitações > Avisos
    </td>
  </tr>
  <!-- Fim do Caminho-->

	<!-- Erro -->
	<?php if ( $Mens == 1 ) {?>
	<tr>
	  <td width="100"></td>
	  <td align="left" colspan="2"><?php if ( $Mens == 1 ) { ExibeMens($Mensagem,$Tipo,$Virgula); } ?></td>
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
	        	<table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal">
	          	<tr>
	            	<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
		    					AVISOS DE LICITAÇÕES - PROTOCOLO DE ENTREGA
		          	</td>
		        	</tr>
	  	      	<tr>
	    	      	<td class="textonormal">
	      	    		<p align="justify">
	        	    		<?php
	        	    		if( $VerificaCPF == "S" ){
	        	    				echo "Caso seja a primeira vez que está  visualizando/executando o download do ";
	        	    				echo "documento informe os dados seguintes para o Protocolo de Entrega e clique ";
	        	    				echo "no botão \"Enviar\". Os campos com * são de preenchimento obrigatório.<br><br>";
	        	    				echo "Se já foi executado o download para este processo ou o fornecedor já é cadastrado, ";
			        	    		echo "informe apenas os campos solicitados e clique no botão \"Enviar\".";
	        	    		}else{
	        	    				echo "Para visualizar/executar o download do documento informe o CPF/CNPJ e clique no botão \"Confirmar\". Os campos com * são de preenchimento obrigatório.";
	        	    		}
	        	    		?>
	          	   	</p>
	          		</td>
		        	<tr>
	  	        	<td>
	    	      		<table class="textonormal" border="0" width="100%" summary="">
		    	      		<tr>
	              			<td class="textonormal" bgcolor="#DCEDF7" height="20" width="35%">Processo </td>
	      	    				<td class="textonormal"><?php echo $LicitacaoProcesso; ?></td>
	        	  			</tr>
										<tr>
	              			<td class="textonormal" bgcolor="#DCEDF7" height="20">Ano </td>
	      	    				<td class="textonormal"><?php echo $LicitacaoAno; ?></td>
		        	  		</tr>
										<tr>
		        	    		<?php
	          	    		if( $CPF_CNPJ != "" and $VerificaCPF == "S" ){
			          	   			echo "<td class=\"textonormal\" bgcolor=\"#DCEDF7\" height=\"20\">CPF/CNPJ</td>\n";
			          	    		echo "<td class=\"textonormal\">\n";
	          	    				if( strlen($CPF_CNPJ)==14 ){ echo FormataCNPJ($CPF_CNPJ); }else{ echo FormataCPF($CPF_CNPJ); }
	          	    				echo "<input type=\"hidden\" name=\"CPF_CNPJ\" value=\"$CPF_CNPJ\">\n";
	          	    				echo "<input type=\"hidden\" name=\"CnpjCpf\" value=\"$CnpjCpf\">\n";
	            	  		}else{
			          	   			echo "<td class=\"textonormal\" bgcolor=\"#DCEDF7\" height=\"20\">\n";
													echo "	<input type=\"radio\" name=\"CnpjCpf\" value=\"CPF\"\n";
													if( $CnpjCpf == "CPF" or $CnpjCpf == "" ){ echo "checked"; }
													echo ">CPF*\n";
		          	    			echo "	<input type=\"radio\" name=\"CnpjCpf\" value=\"CNPJ\"\n";
		          	    			if( $CnpjCpf == "CNPJ" ){ echo "checked"; }
		          	    			echo ">CNPJ*\n";
			          	    		echo "</td>\n";
			          	    		echo "<td class=\"textonormal\">\n";
				         					echo "<input type=\"text\" name=\"CPF_CNPJ\" size=\"15\" maxlength=\"14\" value=\"$CPF_CNPJ\" class=\"textonormal\">\n";
	            	  		}
	            	  		?>
    	      	    		</td>
	            			</tr>
										<?php if( $VerificaCPF != "" ){ ?>
	      	      		<tr>
	          	    		<?php
	          	    		if( $RazaoSocial != "" and $VerificaCPF == "S" ){
			        	      		echo "<td class=\"textonormal\" bgcolor=\"#DCEDF7\" height=\"20\">Razão Social</td>\n";
			          	    		echo "<td class=\"textonormal\">$RazaoSocial\n";
	          	    				echo "<input type=\"hidden\" name=\"RazaoSocial\" value=\"$RazaoSocial\">\n";
	            	  		}else{
			        	      		echo "<td class=\"textonormal\" bgcolor=\"#DCEDF7\" height=\"20\">Razão Social*</td>\n";
			          	    		echo "<td class=\"textonormal\">\n";
	          	    				echo "<input type=\"text\" name=\"RazaoSocial\" size=\"40\" maxlength=\"60\" value=\"$RazaoSocial\" class=\"textonormal\">\n";
	            	  		}
	            	  		?>
	            	  		</td>
	            			</tr>
	            			<tr>
	          	    		<?php
	          	    		if( $Endereco != "" and $VerificaCPF == "S" ){
	          	    				$FormEnd = $Endereco;
	          	    				if( $Numero != "" ){
	          	    						$FormEnd .=	", $Numero";
	          	    				}
	          	    				if( $Complemento != "" ){
	          	    						$FormEnd .=	" $Complemento";
	          	    				}
	          	    				if( $Bairro != "" ){
	          	    						$FormEnd .=	" - $Bairro";
	          	    				}
	          	    				if( $Estado != "" ){
	          	    						$FormEnd .=	"/$Estado";
	          	    				}
			        	      		echo "<td class=\"textonormal\" bgcolor=\"#DCEDF7\" height=\"20\">Endereço</td>\n";
			          	    		echo "<td class=\"textonormal\">$FormEnd\n";
	          	    				echo "<input type=\"hidden\" name=\"Endereco\" maxlength=\"60\" value=\"$Endereco\">\n";
	            	  		}else{
			        	      		echo "<td class=\"textonormal\" bgcolor=\"#DCEDF7\" height=\"20\">Endereço*</td>\n";
			          	    		echo "<td class=\"textonormal\">\n";
	          	    				echo "<input type=\"text\" name=\"Endereco\" size=\"40\" maxlength=\"60\" value=\"$Endereco\" class=\"textonormal\">\n";
	            	  		}
        	    				echo "<input type=\"hidden\" name=\"Numero\" value=\"$Numero\">\n";
        	    				echo "<input type=\"hidden\" name=\"Complemento\" value=\"$Complemento\">\n";
        	    				echo "<input type=\"hidden\" name=\"Bairro\" value=\"$Bairro\">\n";
        	    				echo "<input type=\"hidden\" name=\"Cidade\" value=\"$Cidade\">\n";
        	    				echo "<input type=\"hidden\" name=\"Estado\" value=\"$Estado\">\n";
        	    				echo "<input type=\"hidden\" name=\"CEP\" value=\"$CEP\">\n";
	            	  		?>
	          	    		</td>
	            			</tr>
	            			<tr>
	          	    		<?php
	          	    		if( $Email != "" and $VerificaCPF == "S" ){
			        	      		echo "<td class=\"textonormal\" bgcolor=\"#DCEDF7\" height=\"20\">E-mail</td>\n";
			          	    		echo "<td class=\"textonormal\">$Email\n";
	          	    				echo "<input type=\"hidden\" name=\"Email\" value=\"$Email\">\n";
	            	  		}else{
			        	      		echo "<td class=\"textonormal\" bgcolor=\"#DCEDF7\" height=\"20\">E-mail*</td>\n";
			          	    		echo "<td class=\"textonormal\">\n";
	          	    				echo "<input type=\"text\" name=\"Email\" size=\"40\" maxlength=\"60\" value=\"$Email\" class=\"textonormal\">\n";
	            	  		}
	            	  		?>
	          	    		</td>
	            			</tr>
	            			<tr>
	          	    		<?php
	          	    		if( $Telefone != "" and $VerificaCPF == "S" ){
			        	      		echo "<td class=\"textonormal\" bgcolor=\"#DCEDF7\" height=\"20\">Telefone</td>\n";
			          	    		echo "<td class=\"textonormal\">$Telefone\n";
	          	    				echo "<input type=\"hidden\" name=\"Telefone\" value=\"$Telefone\">\n";
	            	  		}else{
			        	      		echo "<td class=\"textonormal\" bgcolor=\"#DCEDF7\" height=\"20\">Telefone*</td>\n";
			          	    		echo "<td class=\"textonormal\">\n";
													echo "<input type=\"text\" name=\"Telefone\" size=\"20\" maxlength=\"25\" value=\"$Telefone\" class=\"textonormal\">\n";
	            	  		}
	            	  		?>
	          	    		</td>
	            			</tr>
	            			<tr>
	          	    		<?php
	          	    		if( trim($Fax) != "" and $VerificaCPF == "S" ){
			        	      		echo "<td class=\"textonormal\" bgcolor=\"#DCEDF7\" height=\"20\">Fax</td>\n";
			          	    		echo "<td class=\"textonormal\">$Fax\n";
	          	    				echo "<input type=\"hidden\" name=\"Fax\" value=\"$Fax\">\n";
	            	  		}else{
			        	      		echo "<td class=\"textonormal\" bgcolor=\"#DCEDF7\" height=\"20\">Fax*</td>\n";
			          	    		echo "<td class=\"textonormal\">\n";
	          	    				echo "<input type=\"text\" name=\"Fax\" size=\"20\" maxlength=\"25\" value=\"$Fax\" class=\"textonormal\">\n";
	            	  		}
	            	  		?>
	          	    		</td>
	            			</tr>
	            			<tr>
	          	    		<?php
	          	    		if( $NomeContato != "" and $VerificaCPF == "S" ){
			        	      		echo "<td class=\"textonormal\" bgcolor=\"#DCEDF7\" height=\"20\">Nome do Contato</td>\n";
			          	    		echo "<td class=\"textonormal\">$NomeContato\n";
	          	    				echo "<input type=\"hidden\" name=\"NomeContato\" value=\"$NomeContato\">\n";
	            	  		}else{
			        	      		echo "<td class=\"textonormal\" bgcolor=\"#DCEDF7\" height=\"20\">Nome do Contato*</td>\n";
			          	    		echo "<td class=\"textonormal\">\n";
	          	    				echo "<input type=\"text\" name=\"NomeContato\" size=\"40\" maxlength=\"60\" value=\"$NomeContato\" class=\"textonormal\">\n";
	            	  		}
	            	  		?>
	          	    		</td>
	            			</tr>
	            			<tr>
	        	      		<td class="textonormal" bgcolor="#DCEDF7" height="20">Deseja participar da licitação*</td>
	          	    		<td class="textonormal">
	    	    			  	  <input type="radio" name="Participacao" value="S" <?php if( $Participacao == "S" or $Participacao == "" ){ echo "checked"; }?>> Sim
	    	    			  	  <input type="radio" name="Participacao" value="N" <?php if( $Participacao == "N" ){ echo "checked"; }?>>Não
	          	    		</td>
	            			</tr>
	            			<?php } ?>
	        	      </table>
	        	    </td>
		        	</tr>
	  	      	<tr>
   	  	  			<td class="textonormal" align="right">
   	  	  				<table border="0">
   	  	  					<tr><td>
        	  			<input type="hidden" name="Critica" value="1">
        	  			<input type="hidden" name="FornCad" value="">
        	      	<input type="hidden" name="VerificaCPF" value="<?echo $VerificaCPF;?>">
        	      	<input type="hidden" name="Objeto" value="<?echo $Objeto?>">
        	      	<input type="hidden" name="OrgaoLicitanteCodigo" value="<?echo $OrgaoLicitanteCodigo?>">
        	      	<input type="hidden" name="ComissaoCodigo" value="<?echo $ComissaoCodigo?>">
        	      	<input type="hidden" name="ModalidadeCodigo" value="<?echo $ModalidadeCodigo?>">
        	      	<input type="hidden" name="LicitacaoProcesso" value="<?echo $LicitacaoProcesso?>">
        	      	<input type="hidden" name="LicitacaoAno" value="<?echo $LicitacaoAno?>">
        	      	<input type="hidden" name="GrupoCodigo" value="<?echo $GrupoCodigo?>">
        	      	<input type="hidden" name="DocumentoCodigo" value="<?echo $DocumentoCodigo?>">
									<?php if( $VerificaCPF == "" ){ ?>
									<input type="button" value="Confirmar" class="botao" onclick="javascript:enviar('Confirmar');">
									<?php }else{ ?>
									<input type="button" value="Enviar" class="botao" onclick="javascript:enviar('Enviar');">
									<?php } ?>
 	                <input type="hidden" name="Botao" value="">
 	                </td>
 	                </form>
 	                <form action="ConsAvisosDocumentos.php" method="post">
 	                <td>
 	                <input type="hidden" name="Objeto" value="<?echo $Objeto?>">
									<input type="hidden" name="OrgaoLicitanteCodigo" value="<?echo $OrgaoLicitanteCodigo?>">
									<input type="hidden" name="ComissaoCodigo" value="<?echo $ComissaoCodigo?>">
									<input type="hidden" name="ModalidadeCodigo" value="<?echo $ModalidadeCodigo?>">
									<input type="hidden" name="Botao" value="">
									<input type="hidden" name="GrupoCodigo" value="<?=$GrupoCodigo;?>">
									<input type="hidden" name="LicitacaoProcesso" value="<?=$LicitacaoProcesso;?>">
									<input type="hidden" name="LicitacaoAno" value="<?=$LicitacaoAno;?>">
									<input type="hidden" name="DocumentoCodigo" value="<?=$DocumentoCodigo;?>">
									<input type="submit" name="Voltar" value="Voltar" class="botao">
								</td>
								</form>
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
<script language="javascript" type="">
<!--
<?php if( $CPF_CNPJ == "" ){ ?>
document.Avisos.CPF_CNPJ.focus();
<?php } ?>
//-->
</script>

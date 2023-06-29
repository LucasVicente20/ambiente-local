<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: EnviarEmail.php
# Autor:    Rodrigo Melo
# Data:     05/03/2009
# Objetivo: Programa de Envio de Email para os fornecedores cadastrados.
# Autor:    Everton Lino
# Data:     17/09/2009
# Objetivo: Programa de Envio de Email para fornecedor específico.
# Alterado: Rodrigo Melo
# Data:     24/05/2011	- Tarefa Redmine: 2209 - Mandar envio de emails para os 2 emails do fornecedor
# Alterado: Rodrigo Melo
# Data:     03/06/2011  - Tarefa Redmine: 2203 - Fornecedores devem estar associados a grupos de materiais
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

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST"){
		$Botao        	  = $_POST['Botao'];
		$Titulo     	  = $_POST['Titulo'];
		$CorpoEmail	      = $_POST['CorpoEmail'];
		$NCaracteresC     = $_POST['NCaracteresC'];
		$Situacao	      = $_POST['Situacao']; #Situação pode ser: "CADASTRADO", "INABILITADO POR MOTIVO ESPECÍFICO", "SUSPENSO", "CANCELADO", "EXCLUÍDO", "INIDÔNEO", "CADASTRADO INABILITADO", "CADASTRADO HABILITADO"
		$TipoHabilitacao  = $_POST['TipoHabilitacao']; #Habilitação pode ser: E - Estoque, L - Licitação ou D - Compra Direta

		$TipoGrupo 			= $_POST['TipoGrupo'];
		$Grupo   				= $_POST['Grupo'];
		$ProgramaOrigem = $_POST['ProgramaOrigem'];
}else{
		$Mens     = $_GET['Mens'];
		$Mensagem = $_GET['Mensagem'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

if( $Botao == "Limpar" ){
	  header("location: EnviarEmail.php");
	  exit;
} elseif($Botao == "Enviar"){

	//validações

	//Validando tamanho do corpo do e-mail
	$Mens     = 0;
	$Mensagem = "Informe: ";

	if( $TipoHabilitacao == "" ){
		if( $Mens == 1 ){ $Mensagem .= ", "; }
		$Mens      = 1;
		$Tipo      = 2;
		$Mensagem .= "<a href=\"javascript:document.EnviarEmail.TipoHabilitacao.focus();\" class=\"titulo2\">Habilitação de fornecedor</a>";
	}

	if( $Situacao == "" ){
		if( $Mens == 1 ){ $Mensagem .= ", "; }
		$Mens      = 1;
		$Tipo      = 2;
		$Mensagem .= "<a href=\"javascript:document.EnviarEmail.Situacao.focus();\" class=\"titulo2\">Situacao</a>";
	}

	if( $TipoGrupo == "" )
	{
				if( $Mens == 1 ){ $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.EnviarEmail.TipoGrupo.focus();\" class=\"titulo2\">Tipo de grupo</a>";
	}
	if( $Grupo == "" )
	{
		if( $Mens == 1 ){ $Mensagem .= ", "; }
		$Mens      = 1;
		$Tipo      = 2;
		$Mensagem .= "<a href=\"javascript:document.EnviarEmail.Grupo.focus();\" class=\"titulo2\">Grupo</a>";
  }

	if( $Titulo == "" ){
			if( $Mens == 1 ){ $Mensagem .= ", "; }
			$Mens      = 1;
			$Tipo      = 2;
			$Mensagem .= "<a href=\"javascript:document.EnviarEmail.Titulo.focus();\" class=\"titulo2\">Título</a>";
	}else{
			if( strlen($Titulo) > 100 ){
					if( $Mens == 1 ){ $Mensagem .= ", "; }
					$Mens      = 1;
					$Tipo      = 2;
					$Mensagem .= "<a href=\"javascript:document.EnviarEmail.Titulo.focus();\" class=\"titulo2\">Titulo no Máximo com 100 Caracteres</a>";
			}
	}

	if( $CorpoEmail == "" ){
			if( $Mens == 1 ){ $Mensagem .= ", "; }
			$Mens      = 1;
			$Tipo      = 2;
			$Mensagem .= "<a href=\"javascript:document.EnviarEmail.CorpoEmail.focus();\" class=\"titulo2\">Corpo do E-mail</a>";
	}else{
			if( strlen($CorpoEmail) > 1000 ){
					if( $Mens == 1 ){ $Mensagem .= ", "; }
					$Mens      = 1;
					$Tipo      = 2;
					$Mensagem .= "<a href=\"javascript:document.EnviarEmail.CorpoEmail.focus();\" class=\"titulo2\">Corpo do E-mail no Máximo com 1000 Caracteres</a>";
			}
	}


	if( $Mens == 0 ){
		$Botao = "Verificado";
	}
} elseif($Botao == "Confirmar"){

	//OBTER OS FORNECEDORES SELECIONADOS E ENVIAR O E-MAIL
    $db   = Conexao();

    $sql  = " SELECT DISTINCT FCRE.AFORCRSEQU, FCRE.NFORCRMAIL, FCRE.NFORCRMAI2 FROM ";
    $sql .=	" SFPC.TBFORNECEDORCREDENCIADO FCRE, SFPC.TBFORNSITUACAO FSIT, ";
    $sql .=	" SFPC.TBGRUPOFORNECEDOR GFCR, SFPC.TBGRUPOMATERIALSERVICO GRMS ";
    $sql .= " WHERE FCRE.AFORCRSEQU = FSIT.AFORCRSEQU ";
    $sql .= " AND FCRE.AFORCRSEQU = GFCR.AFORCRSEQU ";
    $sql .= " AND GFCR.CGRUMSCODI = GRMS.CGRUMSCODI ";

    if($TipoHabilitacao != 'T'){ // Caso não seja para enviar para todos os fornecedores deve-se realizar a pesquisa por situação e habilitação.
      $sql .= " AND FCRE.FFORCRTIPO = '$TipoHabilitacao' "; # Habilitação do fornecedor (E-Estoque, D-Compra Direta, L-Licitação)
      $sql .= " AND FSIT.CFORTSCODI = ".substr($Situacao, 0, 1)." "; #Situação do fornecedor (1-CADASTRADO, 2-INABILITADO POR MOTIVO ESPECÍFICO, 3-SUSPENSO, 4-CANCELADO, 5-EXCLUÍDO, 6-INIDÔNEO)
    }
    if($Grupo != "T"){
	    $sql .= " AND GRMS.CGRUMSCODI = $Grupo  ";
	  }


    $sql .= " AND FSIT.DFORSISITU = (SELECT MAX(DFORSISITU) FROM SFPC.TBFORNSITUACAO WHERE AFORCRSEQU = FCRE.AFORCRSEQU) ";
    $sql .= " AND FCRE.NFORCRMAIL IS NOT NULL ";
    $sql .= " ORDER BY FCRE.NFORCRMAIL ";

		//echo "[".$sql."]\n";

    $res  = $db->query($sql);

	if( PEAR::isError($res) ){
		EmailErroSQL("Erro ao obter os fornecedores", $ErroPrograma, __LINE__, "Erro ao obter os fornecedores para enviar e-mail", $sql, $res);
	}else{
       $QtdRows = $res->numRows();
       $MailRemetente = $GLOBALS["EMAIL_FROM"];

       if($QtdRows > 0){
         $QtdeEmailsEnviados = 0;
         while( $Linha = $res->fetchRow() ){

         	$SequencialFornecedor = $Linha[0];

         	if ($TipoHabilitacao  == "L"){ # Caso o tipo da habilitação seja Licitação (L).

				if(strlen($Situacao) > 1){
					# Verifica a Validação das Certidões do Fornecedor #
					$sql  = "SELECT B.DFORCEVALI ";
					$sql .= "  FROM SFPC.TBTIPOCERTIDAO A, SFPC.TBFORNECEDORCERTIDAO B ";
					$sql .= " WHERE A.CTIPCECODI = B.CTIPCECODI ";
					$sql .= " AND B.AFORCRSEQU = ".$SequencialFornecedor." ";
					$sql .= " AND FTIPCEOBRI = 'S' ";
					$sql .= " ORDER BY B.DFORCEVALI DESC";
					$result = $db->query($sql);
					if( PEAR::isError($result) ){
							EmailErroSQL("Erro ao verificar a validação das certidões do fornecedor", $ErroPrograma, __LINE__, "Erro ao verificar a validação das certidões do fornecedor para enviar e-mail", $sql, $result);
					}else{
						$Rows = $result->numRows();
						$TipoCadastrado = "H";
						for( $i=0; $i< $Rows;$i++ ){
							$LinhaCertidao  = $result->fetchRow();
							if( $LinhaCertidao[0] < date("Y-m-d") ){ # Fornecedores CADASTRADOS INABILITADOS
		                    	$TipoCadastrado = "I";
		                    	break;
							}
						}

						if(substr($Situacao, 2, 1) == $TipoCadastrado){
							$EmailFornecedor = $Linha[1];
							$EmailFornecedor2 = $Linha[2];

							EnviaEmail($EmailFornecedor, $Titulo, $CorpoEmail, $MailRemetente); //ORIGINAL
							if($EmailFornecedor2!="" and !is_null($EmailFornecedor2) ){
								EnviaEmail($EmailFornecedor2, $Titulo, $CorpoEmail, $MailRemetente);
							}
				            $QtdeEmailsEnviados++;
						}
					}
         	    } else {
         	        $EmailFornecedor = $Linha[1];
         	    }

			} else {
				$EmailFornecedor = $Linha[1];
				$EmailFornecedor2 = $Linha[2];

				EnviaEmail($EmailFornecedor, $Titulo, $CorpoEmail, $MailRemetente); //ORIGINAL
				if($EmailFornecedor2!="" and !is_null($EmailFornecedor2) ){
					EnviaEmail($EmailFornecedor2, $Titulo, $CorpoEmail, $MailRemetente);
				}
	            $QtdeEmailsEnviados++;

			}
         }

         if($QtdeEmailsEnviados > 0){
	         $Mens      = 1;
			 $Tipo      = 1;
		     $Mensagem  = "E-mail enviado com sucesso";
         } else {
         	$Mens      = 1;
			$Tipo      = 1;
		    $Mensagem  = "Não existem fornecedores para o critério de pesquisa";
         }

	   } else {
	   	$Mens      = 1;
		$Tipo      = 1;
	    $Mensagem  = "Não existem fornecedores para o critério de pesquisa";
	   }
	   $db->disconnect();
	}

	$Situacao = "";
	$TipoHabilitacao = "";
	$TipoGrupo = "";
	$Grupo = "";
	$Botao = "";
	$Titulo = "";
	$CorpoEmail = "";
	$NCaracteresC = "";

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
	document.EnviarEmail.Botao.value=valor;
	document.EnviarEmail.submit();
}

function remeter(){
	document.EnviarEmail.Grupo.value = '';
	document.EnviarEmail.submit();
}

<?php MenuAcesso(); ?>

function ncaracteresC(valor){
	document.EnviarEmail.NCaracteresC.value = '' +  document.EnviarEmail.CorpoEmail.value.length;
	if( navigator.appName == 'Netscape' && valor ){ //Netscape Only
		document.EnviarEmail.NCaracteresC.focus();
	}
}

//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="EnviarEmail.php" method="post" name="EnviarEmail">
<br><br><br><br><br>
<table cellpadding="3" border="0" summary="">
  <!-- Caminho -->
  <tr>
    <td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
    <td align="left" class="textonormal" colspan="2">
      <font class="titulo2">|</font>
	    <a href="../index.php"><font color="#000000">Página Principal</font></a> > Fornecedores > Enviar E-mail
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
		    					ENVIO DE E-MAIL PARA FORNECEDORES
		          	</td>
		        	</tr>
	  	      	<tr>
	    	      	<td class="textonormal" colspan="4">

	      	    		<?php if($Botao == "Verificado") {?>

	      	    		<p align="justify">
	        	    		Clique no botão "Confirmar" para confirmar e enviar o e-mail aos fornecedores.<br>
	        	    		Para voltar e reeditar o e-mail, clique no botão "Voltar".
	          	   	    </p>

	      	    		<?php } else { ?>

	      	    		<p align="justify">
	        	    		Para criar um e-mail para os fornecedores, informe os dados abaixo e clique no botão "Enviar". Os itens obrigatórios estão com *.<br><br>
	        	    		Para limpar, clique no botão "Limpar".<br><br>
	          	   	    </p>

	          	   	    <?php } ?>

	          		</td>
	          	</tr>
		        	<tr>
    	      		<td class="textonormal" colspan="4">
									<table border="0" cellpadding="0" cellspacing="2" summary="" class="textonormal" width="100%">
					        	<tr>
			  	      			<td class="textonormal" bgcolor="#DCEDF7">Habilitação de fornecedor<span style="color: red;">*</span></td>
								<td class="textonormal" colspan="2"	>
									<select name="TipoHabilitacao" class="textonormal" onchange="document.EnviarEmail.Situacao.value=''; document.EnviarEmail.submit()" <?php if($Botao == "Verificado") { echo "disabled=\"disabled\""; }?> >
                    <option value=""  <?php if( $TipoHabilitacao == null || $TipoHabilitacao == "" ){ echo "selected"; }?>>Selecione uma habilitação</option>
                    <option value="T" <?php if( $TipoHabilitacao == "T" ){ echo "selected"; }?>>TODOS</option>
										<option value="E" <?php if( $TipoHabilitacao == "E" ){ echo "selected"; }?>>ESTOQUES</option>
										<option value="D" <?php if( $TipoHabilitacao == "D" ){ echo "selected"; }?>>COMPRA DIRETA</option>
										<option value="L" <?php if( $TipoHabilitacao == "L" ){ echo "selected"; }?>>LICITAÇÃO</option>
									</select>
								</td>
			        	    </tr>
			        	    <tr>
			    	      		<td class="textonormal" bgcolor="#DCEDF7" width="30%">Situação<span style="color: red;">*</span></td>
											<td class="textonormal" colspan="2"	>
 	                      <select name="Situacao" class="textonormal" <?php if($Botao == "Verificado") { echo "disabled=\"disabled\""; }?> >
 	                      <option value="" <?php if( $Situacao == null || $Situacao == "" ){ echo "selected"; }?>>Selecione uma situação</option>

	<?php

	if($TipoHabilitacao != '' and $TipoHabilitacao != null){
		if($TipoHabilitacao == 'T'){
			echo "<option value=\"T\" selected>TODOS</option>\n";
		} else {
			# Mostra Tabela de Situação #
			$db	    = Conexao();
			$sql    = "SELECT CFORTSCODI, EFORTSDESC FROM SFPC.TBFORNECEDORTIPOSITUACAO WHERE CFORTSCODI <> 5 ORDER BY EFORTSDESC";
			$result = $db->query($sql);
			if( PEAR::isError($result) ){
			   EmailErroSQL('ERRO NO ENVIO DE E-MAIL PARA O FORNECEDOR', __FILE__, __LINE__, 'ERRO AO TENTAR ENVIAR E-MAIL AO FORNECEDOR', $sql, $result);
			}else{
				$Rows = $result->numRows();

				for( $i=0;$i<$Rows;$i++ ){
					$Linha = $result->fetchRow();

					if($Linha[0] == 1 && $TipoHabilitacao == "L" ){
				    	if( $Situacao == '1_H' ){
				    	  echo "<option value=\"$Linha[0]_H\" selected>$Linha[1] HABILITADO</option>\n"; //CADASTRADO HABILITADO
				    	  echo "<option value=\"$Linha[0]_I\">$Linha[1] INABILITADO</option>\n"; //CADASTRADO INABILITADO
				    	}else{
				    	  if( $Situacao == '1_I' ){
				    	  	echo "<option value=\"$Linha[0]_I\" selected>$Linha[1] INABILITADO</option>\n"; //CADASTRADO INABILITADO
				    	  	echo "<option value=\"$Linha[0]_H\">$Linha[1] HABILITADO</option>\n"; //CADASTRADO HABILITADO
				    	  }	else{
				    	  	echo "<option value=\"$Linha[0]_H\">$Linha[1] HABILITADO</option>\n"; //CADASTRADO HABILITADO
				    	  	echo "<option value=\"$Linha[0]_I\">$Linha[1] INABILITADO</option>\n"; //CADASTRADO INABILITADO
				    	  }
				    	}

					} else {
						if( $Linha[0] == $Situacao ){
								echo "<option value=\"$Linha[0]\" selected>$Linha[1]</option>\n";
						}else{
								echo "<option value=\"$Linha[0]\">$Linha[1]</option>\n";
						}
					}
				}
			}
			$db->disconnect();
		}
	}
	?>
	</select>
	</td>
			        	    </tr>
	        	          <tr>
					              <td class="textonormal" bgcolor="#DCEDF7" width="15%">Tipo de Grupo<span style="color: red;">*</span></td>
					              <td class="textonormal">
													<select name="TipoGrupo" class="textonormal" onchange="remeter();" <?php if($Botao == "Verificado") { echo "disabled=\"disabled\""; }?> >
				                    <option value=""  <?php if( $TipoGrupo == null || $TipoGrupo == "" ){ echo "selected"; }?>>Selecione um tipo de grupo</option>
														<option value="T" <?php if( $TipoGrupo == "T" ){ echo "selected"; }?>>TODOS</option>
				                    <option value="M" <?php if( $TipoGrupo == "M" ){ echo "selected"; }?>>MATERIAL</option>
														<option value="S" <?php if( $TipoGrupo == "S" ){ echo "selected"; }?>>SERVIÇO</option>
													</select>
					              </td>
					            </tr>
						 		       <tr>
							              <td class="textonormal" bgcolor="#DCEDF7" width="15%">Grupo<span style="color: red;">*</span></td>
							              <td class="textonormal">
							              	<select name="Grupo" class="textonormal" onChange="submit();" <?php if($Botao == "Verificado") { echo "disabled=\"disabled\""; }?>>
							              		<option value="" <?php if( $Grupo == null || $Grupo == "" ){ echo "selected"; }?>>Selecione um Grupo...</option>
							              		<option value="T" <?php if( $Grupo == "T" ){ echo "selected"; }?>>TODOS</option>
							              		<?php
							              		if( !is_null($TipoGrupo) and $TipoGrupo != "" ){
									              		$db   = Conexao();
					  												$sql  = "SELECT CGRUMSCODI,EGRUMSDESC FROM SFPC.TBGRUPOMATERIALSERVICO WHERE FGRUMSSITU = 'A'";
					  												if( $TipoGrupo == "M" ){
					  														$sql .= "AND FGRUMSTIPO = 'M'";
					  												}else if( $TipoGrupo == "S" ){
					  														$sql .= "AND FGRUMSTIPO = 'S'";
					  												}
					  												$sql .= "ORDER BY 2";
					  												$res  = $db->query($sql);
																	  if( PEAR::isError($res) ){
																			  ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																		}else{
																				while( $Linha = $res->fetchRow() ){
										          	      			$Descricao   = substr($Linha[1],0,75);
										          	      			if( $Linha[0] == $Grupo ){
															    	      			echo"<option value=\"$Linha[0]\" selected>$Descricao</option>\n";
													      	      		}else{
															    	      			echo"<option value=\"$Linha[0]\">$Descricao</option>\n";
													      	      		}
										                  	}
																		}
								  	              	$db->disconnect();
								  	            }
							              		?>
							              	</select>
							              </td>
							            </tr>
			        	    <tr>
			    	      		<td class="textonormal" bgcolor="#DCEDF7" width="30%">Título<span style="color: red;">*</span></td>
			    	      		<td class="textonormal">
			      	    		<input type="text" class="textonormal" name="Titulo" size="103" maxlength="200" value="<?php echo $Titulo;?>" <?php if($Botao == "Verificado") { echo "readonly"; }?>  >
								      </td>
							    </tr>
							 <tr>
								<td class="textonormal" bgcolor="#DCEDF7">Corpo do E-mail<span style="color: red;">*</span></td>
								<td class="textonormal">
									<font class="textonormal">máximo de 1000 caracteres</font>
									<input type="text" name="NCaracteresC" disabled size="3" value="<?php echo $NCaracteresC ?>" class="textonormal"><br>
									<textarea name="CorpoEmail" cols="100" rows="6" OnKeyUp="javascript:ncaracteresC(1)" OnBlur="javascript:ncaracteresC(0)" OnSelect="javascript:ncaracteresC(1)" class="textonormal" <?php if($Botao == "Verificado") { echo "readonly"; }?> ><?php echo $CorpoEmail; ?></textarea>
								</td>
							</tr>
									</table>

								</td>
	        		</tr>
      	      <tr>
	      		<td align="right" colspan="4">

	      		<?php if($Botao == "Verificado") { ?>
      				<input type="button" value="Confirmar" class="botao" onclick="javascript:enviar('Confirmar');">
      				<input type="button" value="Voltar" class="botao" onclick="javascript:enviar('Voltar');">
      				<input type="hidden" name="Situacao" value="<?php echo "$Situacao";?>">
        			<input type="hidden" name="TipoHabilitacao" value="<?php echo "$TipoHabilitacao";?>">
        			<input type="hidden" name="TipoGrupo" value="<?php echo "$TipoGrupo";?>">
        			<input type="hidden" name="Grupo" value="<?php echo "$Grupo";?>">
        			<input type="hidden" name="Classe" value="<?php echo "$Classe";?>">
      			<?php  } else { ?>
      			    <input type="button" value="Enviar" class="botao" onclick="javascript:enviar('Enviar');">
      				<input type="button" value="Limpar" class="botao" onclick="javascript:enviar('Limpar');">
      			<?php  } ?>

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

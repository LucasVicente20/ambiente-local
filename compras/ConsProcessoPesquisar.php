<?php
# -------------------------------------------------------------------------
# Portal da DGCO
# Programa: ConsProcessoPesquisar.php
# Autor:    Rodrigo Melo
# Data:     24/04/2011
# Objetivo: Programa de Pesquisa de Processo de Licitação
# -------------------------------------------------------------------------
# Alterado: Luiz Alves
# Data: 29/08/2011 - Manutenção no Sql da janela pop-up.
# -------------------------------------------------------------------------
# Alterado: Pitang Agile TI
# Data:     07/07/2015
# Objetivo: CR Redmine 93100 - Solicitação Incluir - erro na pesquisa de processo licitatório quando tipo de compra for SARP
# -------------------------------------------------------------------------
# Alterado: Lucas Baracho
# Data:     24/08/2018
# Objetivo: Tarefa Redmine 202309
# -------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Variáveis com o global off #
if($_SERVER['REQUEST_METHOD'] == "POST"){
	$Botao                         = $_POST['Botao'];
	$Objeto                        = $_POST['Objeto'];
	$OrgaoLicitanteCodigo          = $_POST['OrgaoLicitanteCodigo'];
	$ComissaoCodigo                = $_POST['ComissaoCodigo'];
	$ModalidadeCodigo              = $_POST['ModalidadeCodigo'];
	$LicitacaoAno                  = $_POST['LicitacaoAno'];
	$Programa                      = $_POST['Programa'];
	$CampoProcessoSARP             = $_POST['CampoProcessoSARP'];
	$CampoAnoSARP                  = $_POST['CampoAnoSARP'];

	$CampoComissaoCodigoSARP       = $_POST['CampoComissaoCodigoSARP'];
	$CampoOrgaoLicitanteCodigoSARP = $_POST['CampoOrgaoLicitanteCodigoSARP'];
	$CampoGrupoEmpresaCodigoSARP   = $_POST['CampoGrupoEmpresaCodigoSARP'];
	$CampoCarregaProcessoSARP      = $_POST['CampoCarregaProcessoSARP'];

}else{
	$Programa             = $_GET['Programa'];
	$CampoProcessoSARP        = $_GET['CampoProcessoSARP'];
	$CampoAnoSARP             = $_GET['CampoAnoSARP'];
	$CampoComissaoCodigoSARP = $_GET['CampoComissaoCodigoSARP'];
	$CampoOrgaoLicitanteCodigoSARP = $_GET['CampoOrgaoLicitanteCodigoSARP'];
	$CampoGrupoEmpresaCodigoSARP = $_GET['CampoGrupoEmpresaCodigoSARP'];
	$CampoCarregaProcessoSARP = $_GET['CampoCarregaProcessoSARP'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "ConsProcessoPesquisar.php";
?>

<html>
<head>
<title>Portal de Compras - Detalhes do <?php echo $Descricao; ?></title>
<script language="javascript" type="">
<!--
function enviar(valor){
	document.ConsProcessoPesquisar.Botao.value = valor;
	document.ConsProcessoPesquisar.submit();
}
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
</head>

<body background="../midia/bg.jpg" marginwidth="0" marginheight="0">
<table cellpadding="0" border="0" summary="">


	<!-- Corpo -->
	<tr>
		<td class="textonormal">
      <table  border="0" cellspacing="0" cellpadding="0" bgcolor="#ffffff" summary="">
        <tr>
	      	<td class="textonormal">
	      		<form action="ConsProcessoPesquisar.php" method="post" name="ConsProcessoPesquisar">
	        	<table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal">
	          	<tr>
	            	<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
		    					PROCESSOS LICITATÓRIOS HOMOLOGADOS
		          	</td>
		        	</tr>
	  	      	<tr>
	    	      	<td class="textonormal">
	      	    		<p align="justify">
	        	    		Para consultar os processos licitatórios homologados, selecione o item de pesquisa e  clique no botão "Pesquisar". Para limpar a pesquisa, clique no botão "Limpar".
	          	   	</p>
	          		</td>
		        	<tr>
	  	        	<td>
	    	      		<table class="textonormal" border="0" summary="">
	      	      		<tr>
	        	      		<td class="textonormal" bgcolor="#DCEDF7">Objeto</td>
	          	    		<td class="textonormal">
	          	    			<input type="text" name="Objeto" id="Objeto" size="45" maxlength="60" value="<?php echo $Objeto;?>" class="textonormal">
	          	    		</td>
	            			</tr>
										<tr>
	        	      		<td class="textonormal" bgcolor="#DCEDF7">Órgão Licitante</td>
	          	    		<td class="textonormal">
		  				  	      <select name="OrgaoLicitanteCodigo" class="textonormal">
													<option value="">Todos os Órgãos Licitantes...</option>
													<?php
													$db     = Conexao();
													$sql    = "SELECT CORGLICODI, EORGLIDESC FROM SFPC.TBORGAOLICITANTE ORDER BY EORGLIDESC";
		                  		                    $result = $db->query($sql);
													if( PEAR::isError($result) ){
													  	ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
													}else{
															while( $Linha = $result->fetchRow() ){
															   	if( $Linha[0] == $OrgaoLicitanteCodigo ){
															    		echo "<option value=\"$Linha[0]\" selected>$Linha[1]</option>\n";
															   	}else{
															      	echo "<option value=\"$Linha[0]\">$Linha[1]</option>\n";
															   	}
											     		}
											    }
		    	              	$db->disconnect();
													?>
											  </select>
										  </td>
	            			</tr>
	            			<tr>
		              		<td class="textonormal"  bgcolor="#DCEDF7">Comissão </td>
		              		<td class="textonormal">
		  				  	      <select name="ComissaoCodigo" class="textonormal">
													<option value="">Todas as Comissões...</option>
													<?php
													$db     = Conexao();
													$sql    = "SELECT CCOMLICODI, ECOMLIDESC, CGREMPCODI ";
													$sql   .= "FROM SFPC.TBCOMISSAOLICITACAO ORDER BY CGREMPCODI,ECOMLIDESC";
		                  		$result = $db->query($sql);
													if( PEAR::isError($result) ){
														  ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
													}else{
															while( $Linha = $result->fetchRow() ){
															   	if( $Linha[0] == $ComissaoCodigo ){
															   			echo "<option value=\"$Linha[0]\" selected>$Linha[1]</option>\n";
															   	}else{
															     	  echo "<option value=\"$Linha[0]\">$Linha[1]</option>\n";
															   	}
											     		}
											    }
		    	              	$db->disconnect();
													?>
											  </select>
										  </td>
	            			</tr>
	            			<tr>
	        	      		<td class="textonormal" bgcolor="#DCEDF7">Modalidade</td>
											<td class="textonormal">
		  				  	      <select name="ModalidadeCodigo" class="textonormal">
													<option value="">Todas as Modalidades...</option>
													<?php 
											    $db     = Conexao();
													$sql    = "SELECT CMODLICODI, EMODLIDESC FROM SFPC.TBMODALIDADELICITACAO ORDER BY AMODLIORDE";
		                  		$result = $db->query($sql);
													if( PEAR::isError($result) ){
													  	ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
													}else{
															while( $Linha = $result->fetchRow() ){
															   	if( $Linha[0] == $ModalidadeCodigo ){
															    		echo "<option value=\"$Linha[0]\" selected>$Linha[1]</option>\n";
															   	}else{
															      	echo "<option value=\"$Linha[0]\">$Linha[1]</option>\n";
															   	}
											     		}
											    }
									     		$db->disconnect();
													?>
									  </select>
							</td>
							</tr>

							<tr>



	        	      			<td class="textonormal" bgcolor="#DCEDF7">Ano</td>
								<td class="textonormal">
		  				  	    	<select name="LicitacaoAno" class="textonormal">
									<?php 
										$db     = Conexao();
										$sql    = "SELECT DISTINCT TO_CHAR(TLICPODHAB,'YYYY') ";
										$sql   .= "  FROM SFPC.TBLICITACAOPORTAL ";
										$sql   .= " WHERE TO_CHAR(TLICPODHAB,'YYYY') <= '".date('Y')."' ";
										$sql   .= " ORDER BY TO_CHAR(TLICPODHAB,'YYYY') DESC";
										$result = $db->query($sql);

										if( PEAR::isError($result) ){
											ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
										}

										while( $Linha = $result->fetchRow() ){
											if( $Linha[0] == $LicitacaoAno ){
												echo "<option value=\"$Linha[0]\" selected>$Linha[0]</option>\n";
											}else{
												echo "<option value=\"$Linha[0]\">$Linha[0]</option>\n";
											}
									    }
							     		$db->disconnect();
									?>
									</select>
								</td>



							</tr>

	          			</table>
		          	</td>
		        	</tr>
	  	      	<tr>
   	  	  			<td class="textonormal" align="right">
        	      	<input type="button" name="Pesquisar" value="Pesquisar" class="botao" onclick="javascript:enviar('Pesquisar');">
        	      	<input type="button" name="Limpar" value="Limpar" class="botao" onclick="javascript:enviar('Limpar');">
	                <input type="hidden" name="Botao" value="">
	                <input type="hidden" name="Programa" value="<?php echo $Programa; ?>">
	                <input type="hidden" name="CampoProcessoSARP" value="<?php echo $CampoProcessoSARP; ?>">
	                <input type="hidden" name="CampoAnoSARP" value="<?php echo $CampoAnoSARP; ?>">
	                <input type="hidden" name="CampoComissaoCodigoSARP" value="<?php echo $CampoComissaoCodigoSARP; ?>">
	                <input type="hidden" name="CampoOrgaoLicitanteCodigoSARP" value="<?php echo $CampoOrgaoLicitanteCodigoSARP; ?>">
	                <input type="hidden" name="CampoGrupoEmpresaCodigoSARP" value="<?php echo $CampoGrupoEmpresaCodigoSARP; ?>">
	                <input type="hidden" name="CampoCarregaProcessoSARP" value="<?php echo $CampoCarregaProcessoSARP; ?>">

		          	</td>
		        	</tr>
    	  	  <!--
    	  	  </table>
    	  	  </form>




					</td>
				</tr>
				<tr>
				-->

<?php
//-- Início do conteúdo do arquivo ConsAvisosResultado --------------------------
// EM DESENVOLVIMENTO ESSA PÁGINA, A CONSULTA ABAIXO, NÃO TRAZ RESULTADO, VERIFICAR A DATA - O ANO - PARA TRAZER RESULTADO.


if( $Botao == "Pesquisar" ) {
		$db   = Conexao();
		$sql  = "SELECT C.EGREMPDESC, E.EMODLIDESC, D.ECOMLIDESC, A.CLICPOPROC, A.ALICPOANOP, ";  //A.ALICPOANOP // Correção 2 ult campos trocados posição
		$sql .= "       A.CLICPOCODL, A.ALICPOANOL, A.XLICPOOBJE, A.TLICPODHAB, B.EORGLIDESC, ";
		$sql .= "       A.CGREMPCODI, A.CCOMLICODI, A.CORGLICODI, D.ECOMLILOCA, D.ACOMLIFONE, D.ACOMLINFAX"; // UPDATE HERE ! 3 ULTIMOS
		$sql .= "  FROM SFPC.TBLICITACAOPORTAL A, SFPC.TBORGAOLICITANTE B, SFPC.TBGRUPOEMPRESA C, ";
		$sql .= "       SFPC.TBCOMISSAOLICITACAO D,SFPC.TBMODALIDADELICITACAO E, SFPC.TBFASELICITACAO F ";
		$sql .= " WHERE A.CORGLICODI = B.CORGLICODI AND A.FLICPOSTAT = 'A' ";
		$sql .= "   AND A.CGREMPCODI = C.CGREMPCODI AND A.CCOMLICODI = D.CCOMLICODI ";
		$sql .= "   AND A.CMODLICODI = E.CMODLICODI ";

		//Inner join da tabela de fases com a licitação
		$sql .= "   AND F.CLICPOPROC = A.CLICPOPROC AND F.ALICPOANOP = A.ALICPOANOP AND F.CGREMPCODI = A.CGREMPCODI AND F.CCOMLICODI = A.CCOMLICODI AND F.CORGLICODI = A.CORGLICODI ";

		$sql .= "   AND A.FLICPOREGP = 'S' AND F.CFASESCODI in (13, 26) "; // 13 - FASE DE HOMOLOGAÇÃO e 26 - HOMOLOGAÇÃO PARCIAL

		if( $Objeto != "" ){ $sql .= " AND ( A.XLICPOOBJE LIKE '%".strtoupper2($Objeto)."%')";}
		if ( $ComissaoCodigo !="" ){ $sql .= " AND A.CCOMLICODI = $ComissaoCodigo "; }
		if ( $ModalidadeCodigo != "" ){ $sql .= " AND A.CMODLICODI = $ModalidadeCodigo "; }
		if ( $OrgaoLicitanteCodigo != "" ){ $sql .= " AND A.CORGLICODI = $OrgaoLicitanteCodigo "; }
		if ( $LicitacaoAno != "" ){ $sql .= " AND A.ALICPOANOP = $LicitacaoAno "; }

		$sql .= " ORDER BY C.EGREMPDESC, E.EMODLIDESC, D.ECOMLIDESC,  A.ALICPOANOP, A.CLICPOPROC";

		$result = $db->query($sql);

		if( PEAR::isError($result) ){
		    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
				$Rows = $result->numRows();
		}

		$GrupoDescricaoricao = "";
		if( $Rows != 0 ){

				echo "<tr>";
				echo "		<td class=\"textonormal\">\n";

				echo "<table width='100%' cellpadding=\"0\" border=\"0\">\n";
				echo "	<tr>\n";
				echo "		<td class=\"textonormal\">\n";
				echo "      <table  width='100%' border=\"0\" cellspacing=\"0\" cellpadding=\"0\" bgcolor=\"#ffffff\" summary=\"\">\n";
				echo "        <tr>\n";
				echo "	      	<td class=\"textonormal\">\n";
				echo "	        	<table width='100%' border=\"1\" cellpadding=\"3\" cellspacing=\"0\" bordercolor=\"#75ADE6\" class=\"textonormal\" summary=\"\">\n";
				echo "	          	<tr>\n";
				echo "	            	<td align=\"center\" bgcolor=\"#75ADE6\" valign=\"middle\" colspan=\"5\" class=\"titulo3\">\n";
				echo "		    					RESULTADO DA PESQUISA\n";
				echo "		          	</td>\n";
				echo "		        	</tr>\n";
				while( $Linha = $result->fetchRow() ){
						if( $GrupoDescricaoricao != $Linha[0] ){
								$GrupoDescricaoricao = $Linha[0];
								echo "<tr><td align=\"center\" class=\"titulo3\" colspan=\"5\" bgcolor=\"#DCEDF7\">$GrupoDescricaoricao</td></tr>\n";
								$ModalidadeDescricaoricao = "";
						}
						if( $ModalidadeDescricaoricao != $Linha[1] ){
								$ModalidadeDescricaoricao = $Linha[1];
								echo "<tr><td align=\"center\" class=\"titulo3\" colspan=\"5\">$ModalidadeDescricaoricao</td></tr>\n";
								$ComissaoDescricaoSARPBanco = "";
						}
						if( $ComissaoDescricaoSARPBanco != $Linha[2] ){
								$ComissaoDescricaoSARPBanco = $Linha[2];
								echo "<tr><td class=\"titulo2\" colspan=\"5\" color=\"#000000\">$ComissaoDescricaoSARPBanco</tr></td>\n";
								echo "<tr><td class=\"textonormal\" colspan=\"5\" color=\"#000000\">$Linha[13] - TEL: $Linha[14] - FAX: $Linha[15]</tr></td>\n";
								echo "<tr><td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"titulo3\">PROCESSO</td>\n";
								echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"titulo3\">LICITAÇÃO</td>\n";
								echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"titulo3\">OBJETO</td>\n";
								echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"titulo3\">DATA/HORA ABERTURA</td>\n";
								echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"titulo3\">ÓRGÃO LICITANTE</td>\n";
						}
						$NProcesso 	           = substr($Linha[3] + 10000,1);
						$NLicitacao            = substr($Linha[5] + 10000,1); //Código da Licitação de acordo com a Comissão de Licitação e Modalidade do Processo Licitatório

						$LicitacaoAnoBanco = $Linha[6]; //Ano da Licitação de acordo com a Comissão de Licitação e Modalidade do Processo Licitatório
						$ObjetoBanco = $Linha[7];
						$OrgaoLicitanteDescricaoSARPBanco = $Linha[9];

						$LicitacaoDtAbertura   = substr($Linha[8],8,2) ."/". substr($Linha[8],5,2) ."/". substr($Linha[8],0,4);
						$LicitacaoHoraAbertura = substr($Linha[8],11,5);
						$GrupoCodigo          = $Linha[10];
						$ProcessoAno          = $Linha[4];
						$ComissaoCodigoBanco       = $Linha[11];
						$OrgaoLicitanteCodigoBanco = $Linha[12];

						echo "<tr>\n";
						echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\"><a href=\"javascript:retorna('$NProcesso$SimboloConcatenacaoDesc$ProcessoAno$SimboloConcatenacaoDesc$ComissaoDescricaoSARPBanco$SimboloConcatenacaoDesc$OrgaoLicitanteDescricaoSARPBanco$SimboloConcatenacaoDesc$ComissaoCodigoBanco$SimboloConcatenacaoDesc$OrgaoLicitanteCodigoBanco$SimboloConcatenacaoDesc$GrupoCodigo');\" class=\"textonormal\"><u>$NProcesso/$ProcessoAno</u></a></td>\n";
						echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\">$NLicitacao/$LicitacaoAnoBanco</td>\n";
						echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\">$ObjetoBanco</td>\n";
						echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\">$LicitacaoDtAbertura<br>$LicitacaoHoraAbertura&nbsp;h</td>\n";
						echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\">$OrgaoLicitanteDescricaoSARPBanco</td>\n";
						echo "</tr>\n";
				}
		}else{
			echo "<tr>\n";
			echo "	<td valign=\"top\" colspan=\"4\" class=\"textonormal\" bgcolor=\"FFFFFF\">\n";
			echo "	Nenhuma ocorrência foi encontrada.\n";
			echo "	</td>\n";
			echo "</tr>\n";
		}

		echo "    	  	  </table>\n";
		echo "					</td>\n";
		echo "				</tr>\n";
		echo "      </table>\n";
		echo "		</td>\n";
		echo "	</tr>\n";
		echo "	<!-- Fim do Corpo -->\n";
		echo "</table>\n";


		echo "		</td>\n";
		echo "</tr>\n";

		$db->disconnect();
}

//-- Fim do conteúdo do arquivo ConsAvisosResultado --------------------------
?>
					</td>
				</tr>
			</table>
			</form>
		</td>
	</tr>
</table>
</body>
</html>
<script language="JavaScript">

window.focus();
function retorna(Valor){
	var str = Valor;
	array = str.split("<?php echo $SimboloConcatenacaoDesc; ?>");
	Processo = array[0];
	Ano = array[1];
	ComissaoDescricao = array[2];
	OrgaoDescricao = array[3];
	ComissaoCodigo = array[4];
	OrgaoCodigo = array[5];
	GrupoEmpresa = array[6];

	//Escrevendo nos campos Processo e Ano do form do programa CadSolicitacaoCompraIncluir.php
	opener.document.getElementById('<?php echo $CampoProcessoSARP?>').value=Processo;
	opener.document.getElementById('<?php echo $CampoAnoSARP?>').value=Ano;
	opener.document.getElementById('<?php echo $CampoComissaoCodigoSARP?>').value=ComissaoCodigo;
	opener.document.getElementById('<?php echo $CampoOrgaoLicitanteCodigoSARP?>').value=OrgaoCodigo;
	opener.document.getElementById('<?php echo $CampoGrupoEmpresaCodigoSARP?>').value=GrupoEmpresa;
	opener.document.getElementById('<?php echo $CampoCarregaProcessoSARP?>').value=1; //Utilizado para carregar o processo na tela principal quando o pop-up for chamado.

	opener.document.forms[0].submit(); // atualizando formulário da janela que abriu este popup

	window.close();
}

document.ConsProcessoPesquisar.Objeto.focus();

//-->
</script>

<?php
#----------------------------------------------------------------------------
# Portal da DGCO
# Programa: RelDocumentoLicitacaoDetalhesImpressao.php
# Autor:    Rodrigo Melo
# Data:     20/03/11
# Objetivo: Programa de Relatório das documentações dos 
#           processos licitatórios postados no portal
# OBS.:     Tabulação 2 espaços
#----------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Obtendo as variáveis da sessão 
$Selecao              = $_SESSION['Selecao'];
$GrupoCodigo          = $_SESSION['GrupoCodigoDet'];
$Processo             = $_SESSION['ProcessoDet'];
$ProcessoAno          = $_SESSION['ProcessoAnoDet'];
$ComissaoCodigo       = $_SESSION['ComissaoCodigoDet'];
$OrgaoLicitanteCodigo = $_SESSION['OrgaoLicitanteCodigoDet'];

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "RelDocumentoLicitacaoDetalhesImpressao.php";
?>
<html>
<body marginwidth="0" marginheight="0">
<link rel="stylesheet" type="text/css" href="../estilo.css">
<form action="RelDocumentoLicitacaoDetalhesImpressao.php" method="post" name="Relatorio">
<p class="titulo3" align="center">
  Prefeitura da Cidade do Recife<br><br>
  RELATÓRIO DA CONSULTA DE DOCUMENTOS DE LICITAÇÃO (AUDITORIA) <br><br>
  <a href="javascript:Fecha()"><img src="../midia/brasao.jpg" width="50" height="40" border="0"></a>
<p class="titulo3" align="right">
	Data: <?echo date("d/m/Y H:i");?>
</p>

<table border="0" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal">
	<tr>
  	<td>
  	    <hr>
  		<table class="textonormal" border="0" align="left" class="caixa">
    		
    		<tr>
					<?php
					# Resgata as informações da licitação #
					$db     = Conexao();
					$sql    = "SELECT A.EGREMPDESC, B.EMODLIDESC, C.ECOMLIDESC, D.XLICPOOBJE, ";
					$sql   .= "       E.EORGLIDESC, D.TLICPODHAB, D.CLICPOCODL, D.ALICPOANOP, ";
					$sql   .= "       D.FLICPOREGP, B.CMODLICODI, D.VLICPOVALE, D.VLICPOVALH, ";
					$sql   .= "       D.VLICPOTGES ";
					$sql   .= "  FROM SFPC.TBGRUPOEMPRESA A, SFPC.TBMODALIDADELICITACAO B, SFPC.TBCOMISSAOLICITACAO C, ";
					$sql   .= "       SFPC.TBLICITACAOPORTAL D, SFPC.TBORGAOLICITANTE E ";
					$sql   .= " WHERE A.CGREMPCODI = D.CGREMPCODI AND D.CGREMPCODI = $GrupoCodigo ";
					$sql   .= "   AND D.CMODLICODI = B.CMODLICODI AND C.CCOMLICODI = D.CCOMLICODI ";
					$sql   .= "   AND D.CCOMLICODI = $ComissaoCodigo AND D.ALICPOANOP = $ProcessoAno ";
					$sql   .= "   AND D.CLICPOPROC = $Processo AND E.CORGLICODI = D.CORGLICODI ";
					$sql   .= "   AND D.CORGLICODI = $OrgaoLicitanteCodigo";
					$result = $db->query($sql);
					if( PEAR::isError($result) ){
						ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
					}else{
						$Rows = $result->numRows();
						while( $Linha = $result->fetchRow() ){
							$GrupoDesc             = $Linha[0];
							$ModalidadeDesc        = $Linha[1];
							$ComissaoDesc          = $Linha[2];
							$OrgaoLicitacao        = $Linha[4];
							$ObjetoLicitacao       = $Linha[3];
							$Licitacao             = substr($Linha[6] + 10000,1);
							$AnoLicitacao          = $Linha[7];
							$LicitacaoDtAbertura   = substr($Linha[5],8,2) ."/". substr($Linha[5],5,2) ."/". substr($Linha[5],0,4);
							$LicitacaoHoraAbertura = substr($Linha[5],11,5);
							if ($Linha[8] == "S") {
								$RegistroPreco	     = "SIM";
							} else {
								$RegistroPreco	     = "NÃO";
							}
							$ModalidadeCodigo = $Linha[9];
							$ValorEstimado         = converte_valor($Linha[10]);
							$ValorHomologado       = converte_valor($Linha[11]);
							$TotalGeralEstimado    = converte_valor($Linha[12]);
						}
					}
					echo "			<td class=\"textonegrito\" bgcolor=\"#DCEDF7\" colspan=\"4\">\n";
					echo "$GrupoDesc<br><br>$ModalidadeDesc<br><br>$ComissaoDesc<br>";
					echo "			</td>\n";
					$Processo = substr($Processo+10000,1);
					echo "			<tr>\n";
					echo "				<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonegrito\" colspan=\"2\">PROCESSO</td>\n";
					echo "				<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" colspan=\"2\">$Processo/$ProcessoAno</td>\n";
					echo "			</tr>\n";
					echo "			<tr>\n";
					echo "				<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonegrito\" colspan=\"2\">LICITAÇÃO</td>\n";
					echo "				<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" colspan=\"2\">$Licitacao/$AnoLicitacao</td>\n";
					echo "			</tr>\n";
					echo "			<tr>\n";
					echo "				<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonegrito\" colspan=\"2\">REGISTRO DE PREÇO";
					# Caso a modalidade seja concorrência ou tomada de preços apareça nome Permissão Remunerada de Uso
					if( $ModalidadeCodigo == 3 or $ModalidadeCodigo == 2 ){ echo "/PERMISSÃO REMUNERADA DE USO"; }
					echo "				</td>\n";
					echo "				<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" colspan=\"2\">$RegistroPreco</td>\n";
					echo "			</tr>\n";
					echo "			<tr>\n";
					echo "				<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonegrito\" colspan=\"2\">OBJETO</td>\n";
					echo "				<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" colspan=\"2\">$ObjetoLicitacao</td>\n";
					echo "			</tr>\n";
					echo "			<tr>\n";
					echo "				<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonegrito\" colspan=\"2\">DATA/HORA DE ABERTURA</td>\n";
					echo "				<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" colspan=\"2\">$LicitacaoDtAbertura $LicitacaoHoraAbertura h</b></td>\n";
					echo "			</tr>\n";
					echo "			<tr>\n";
					echo "				<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonegrito\" colspan=\"2\">ÓRGÃO LICITANTE</td>\n";
					echo "				<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" colspan=\"2\">$OrgaoLicitacao</td>\n";
					echo "			</tr>\n";
					if( $ValorHomologado != "0,00" ){
						if( $ValorEstimado == "" ) {$ValorEstimado = "NÃO INFORMADO"; }
						echo "			<tr>\n";
						echo "				<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonegrito\" colspan=\"2\">VALOR ESTIMADO</td>\n";
						echo "				<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" colspan=\"2\">$ValorEstimado</td>\n";
						echo "			</tr>\n";
					}
					if( $TotalGeralEstimado != "0,00" ){
						echo "			<tr>\n";
						echo "				<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonegrito\" colspan=\"2\">TOTAL GERAL ESTIMADO<br>(Itens que Lograram Êxito)</td>\n";
						echo "				<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" colspan=\"2\">$TotalGeralEstimado</td>\n";
						echo "			</tr>\n";
					}
					if( $ValorHomologado != "0,00" ){
						echo "			<tr>\n";
						echo "				<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonegrito\" colspan=\"2\">VALOR HOMOLOGADO<br>(Itens que Lograram Êxito)</td>\n";
						echo "				<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" colspan=\"2\">$ValorHomologado</td>\n";
						echo "			</tr>\n";
					}

					# Pega os Dados dos do Bloqueio #
					$sql    = "SELECT TUNIDOEXER, CUNIDOORGA, CUNIDOCODI, ALICBLSEQU, ";
					$sql   .= "       CLICBLFUNC, CLICBLSUBF, CLICBLPROG, CLICBLTIPA, ";
					$sql   .= "       ALICBLORDT, CLICBLELE1, CLICBLELE2, CLICBLELE3, ";
					$sql   .= "       CLICBLELE4, CLICBLFONT ";
					$sql   .= "  FROM SFPC.TBLICITACAOBLOQUEIOORCAMENT";
					$sql   .= " WHERE CLICPOPROC = $Processo AND ALICPOANOP = $ProcessoAno ";
					$sql   .= "   AND CCOMLICODI = $ComissaoCodigo ";
					$sql   .= "   AND CGREMPCODI = $GrupoCodigo";
					$sql   .= " ORDER BY ALICBLSEQU";
					$result = $db->query($sql);
					if( PEAR::isError($result) ){
						ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
					}else{
						$Rows = $result->numRows();
						for( $i=0; $i < $Rows;$i++ ){
							$Linha             = $result->fetchRow();
							$ExercicioBloq[$i] = $Linha[0];
							$Orgao[$i]         = $Linha[1];
							$Unidade[$i]       = $Linha[2];
							$Bloqueios[$i]     = $Linha[3];
							$Funcao[$i]        = $Linha[4];
							$Subfuncao[$i]     = $Linha[5];
							$Programa[$i]      = $Linha[6];
							$TipoProjAtiv[$i]  = $Linha[7];
							$ProjAtividade[$i] = $Linha[8];
							$Elemento1[$i]     = $Linha[9];
							$Elemento2[$i]     = $Linha[10];
							$Elemento3[$i]     = $Linha[11];
							$Elemento4[$i]     = $Linha[12];
							$Fonte[$i]         = $Linha[13];
							$Dotacao[$i]       = NumeroDotacao($Funcao[$i],$Subfuncao[$i],$Programa[$i],$Orgao[$i],$Unidade[$i],$TipoProjAtiv[$i],$ProjAtividade[$i],$Elemento1[$i],$Elemento2[$i],$Elemento3[$i],$Elemento4[$i],$Fonte[$i]);
						}
					}
					
					//Participantes-Interessados da Licitação.
					echo "<tr>\n";
					echo "	<td class=\"textonegrito\" bgcolor=\"#DCEDF7\" colspan=\"4\">PARTICIPANTES / INTERESSADOS </td>\n";
					echo "</tr>\n";
					
					echo "<tr>\n";
					
					# Mostra os participantes #
					$sql    = "SELECT ELISOLNOME, CLISOLCNPJ, CLISOLCCPF ";  
					$sql   .= "FROM SFPC.TBLISTASOLICITAN ";
					$sql   .= "WHERE CLICPOPROC = $Processo AND ALICPOANOP = $ProcessoAno ";
					$sql   .= "AND CGREMPCODI = $GrupoCodigo AND CCOMLICODI = $ComissaoCodigo ";
					$sql   .= "AND CORGLICODI = $OrgaoLicitanteCodigo AND FLISOLENVI = 'S' ";
					$sql   .= "ORDER BY ELISOLNOME";
					$result = $db->query($sql);
					if( PEAR::isError($result) ){
					    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
					}
					
					$Rows = $result->numRows();
					
					if( $Rows == 0 ){						  
						  echo "	<td class=\"textonegrito\" colspan='4'>Nenhum Participante/Interessado Relacionado.</td>\n";
				    }else{
				    	echo "	<td class=\"textonormal\" bgcolor=\"#F7F7F7\" colspan='4'>";	 
				    	
						while( $Linha = $result->fetchRow() ){										 
						
							if( $Linha[2] == "" ){
                      			echo "CNPJ: ".FormataCNPJ($Linha[1]);
                  			}else{	
                      			echo "CPF: ".FormataCPF($Linha[2]);
                  			}
                  				echo " - ".$Linha[0]."<br>\n";
						}  
						echo " </td>\n";
					}
										
					
					echo "</tr>\n";
					
					//Bloqueios
					echo "<tr>\n";
					echo "	<td class=\"textonegrito\" bgcolor=\"#DCEDF7\" colspan=\"4\">BLOQUEIOS</td>\n";
					echo "</tr>\n";
					if( count($Bloqueios) != 0 ){
						echo "			<tr>\n";
						echo "				<td bgcolor=\"#F7F7F7\" class=\"textonegrito\">EXERCÍCIO</td>\n";
						echo "				<td bgcolor=\"#F7F7F7\" class=\"textonegrito\">NÚMERO</td>\n";
						echo "				<td bgcolor=\"#F7F7F7\" class=\"textonegrito\">UNIDADE ORÇAMENTÁRIA</td>\n";
						echo "				<td bgcolor=\"#F7F7F7\" class=\"textonegrito\">DOTAÇÃO</td>\n";
						echo "			</tr>\n";
						for( $i=0; $i< count($Bloqueios);$i++ ){
							echo "			<tr>\n";
							echo "				<td class=\"textonormal\" bgcolor=\"#F7F7F7\">$ExercicioBloq[$i]</td>\n";
							echo "				<td class=\"textonormal\" bgcolor=\"#F7F7F7\">\n";
							echo "					".$Orgao[$i].".".sprintf("%02d",$Unidade[$i]).".1.".$Bloqueios[$i]."\n";
							echo "					<input type=\"hidden\" name=\"Bloqueios[$i]\" value=\"$Bloqueios[$i]\">\n";
							echo "				</td>\n";
							echo "				<td class=\"textonormal\" bgcolor=\"#F7F7F7\">\n";

							# Busca a descrição da Unidade Orçamentaria #
							$sql    = "SELECT EUNIDODESC FROM SFPC.TBUNIDADEORCAMENTPORTAL ";
							$sql   .= " WHERE TUNIDOEXER = $ExercicioBloq[$i] AND CUNIDOORGA = $Orgao[$i] ";
							$sql   .= "   AND CUNIDOCODI = $Unidade[$i]";
							$result = $db->query($sql);
							if( PEAR::isError($result) ){
								ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
							}else{
								$Linha               = $result->fetchRow();
								$UnidadeOrcament[$i] = $Linha[0];
							}
							echo "					$UnidadeOrcament[$i]\n";
							echo "				</td>\n";
							echo "				<td class=\"textonormal\" bgcolor=\"#F7F7F7\">\n";
							echo "					$Dotacao[$i]\n";
							echo "				</td>\n";
							echo "			</tr>\n";
						}
					}else{
						echo "<tr>\n";
						echo "	<td class=\"textonegrito\" colspan=\"4\">Nenhum Bloqueio Informado.</td>\n";
						echo "</tr>\n";
					}
					echo "<tr>\n";
					echo "	<td class=\"textonegrito\" bgcolor=\"#DCEDF7\" colspan=\"4\">DOCUMENTOS RELACIONADOS</td>\n";
	    	      	echo "</tr>\n";
								?>
							<tr>								
								<td class="textonormal" colspan="4"  style="padding: 0; border:0px;" >
									<?php
									if ( $Mens2 == 1 ) { ExibeMens($Mensagem,$Tipo); }
									# Pega os documentos da Licitação #
									$sql  = "SELECT CDOCLICODI, EDOCLINOME, EDOCLIOBSE, FDOCLIEXCL, U.EUSUPORESP, D.TDOCLIDATA, D.TDOCLIULAT ";
									$sql .= "  FROM SFPC.TBDOCUMENTOLICITACAO D, SFPC.TBUSUARIOPORTAL U";
									$sql .= " WHERE CLICPOPROC = $Processo AND ALICPOANOP = $ProcessoAno ";
									$sql .= "   AND CCOMLICODI = $ComissaoCodigo AND D.CGREMPCODI = $GrupoCodigo AND D.CUSUPOCODI = U.CUSUPOCODI";


									# Exibir as planilhas ORCAMENTO_9999_99_99_99_9999.XLS <ANO+CODORGÃO+CODUNIDADE+CODCOMISSAO+CODPROCESSO>
									# e RESULTADO_9999_99_99_99_9999.XLS <ANO+CODORGÃO+CODUNIDADE+CODCOMISSAO+CODPROCESSO> APENAS
									# para os usuários que possuem os perfis COMISSAO LICITACAO (7) ou COMISS LICITACAO-REQUISITANTE (18)

									# VER ALTERAÇÃO: 01/09/2010 - CR: 5210

									#Em caso de dúvidas na expressão regular consultar o seguinte site:
									#http://www.postgresql.org/docs/8.1/interactive/functions-matching.html#FUNCTIONS-POSIX-REGEXP

									if ( $_SESSION['_cperficodi_'] == null or ($_SESSION['_cperficodi_'] != 7 and $_SESSION['_cperficodi_'] != 18) ) {
										$sql .= " AND ( NOT ( (edoclinome ~* '^RESULTADO_') OR (edoclinome ~* '^ORCAMENTO_') ) 	) ";
									}

									$result = $db->query($sql);
									if( PEAR::isError($result) ){
									  	ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
									}else{

											$Rows = $result->numRows();
											while( $cols = $result->fetchRow() ){
												$cont++;
												$dados[$cont-1] = "$cols[0];$cols[1];$cols[2];$cols[3];$cols[4];$cols[5];$cols[6]";
											}
											
											# Mostra os Documentos relacionados com a Licitação #
											if( $Rows > 0 ){
												?>
												<table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" style="width:100%;  border:1px;">
													<tr style="text-align:center;">
														<td valign="middle" bgcolor="#F7F7F7" class="textonegrito">&nbsp;</td>
														<td valign="middle" bgcolor="#F7F7F7" class="textonegrito">DOCUMENTO</td>
														<td valign="middle" bgcolor="#F7F7F7" class="textonegrito">TAMANHO</td>
														<td valign="middle" bgcolor="#F7F7F7" class="textonegrito">RESPONSÁVEL</td>
														<td valign="middle" bgcolor="#F7F7F7" class="textonegrito">DATA DE GERAÇÃO DO DOCUMENTO</td>
														<td valign="middle" bgcolor="#F7F7F7" class="textonegrito">DATA DE EXCLUSÃO DO DOCUMENTO</td>
														<td valign="middle" bgcolor="#F7F7F7" class="textonegrito">OBSERVAÇÃO/<br/>JUSTIFICATIVA</td>
													</tr>
												<?php

													for($Row = 0 ; $Row < $Rows ; $Row++){
															$Linha = explode(";",$dados[$Row]);
															$Arq = $GLOBALS["CAMINHO_UPLOADS"]."licitacoes/"."DOC".$GrupoCodigo."_".$Processo."_".$ProcessoAno."_".$ComissaoCodigo."_".$OrgaoLicitanteCodigo."_".$Linha[0];
															$itemNome = $Linha[1];
															$itemObservacao = $Linha[2]."&nbsp;";
															$itemExcluido = $Linha[3];
															$itemAutor = $Linha[4];
															$DataGeracaoDocumento = DataBarra($Linha[5]);
															$DataExclusaoDocumento = DataBarra($Linha[6]);
															
															if ( file_exists($Arq) ){
																	$tamanho = filesize($Arq)/1024;
																	$Url = "ConsAcompDownloadDoc.php?GrupoCodigo=$GrupoCodigo&Processo=$Processo&ProcessoAno=$ProcessoAno&ComissaoCodigo=$ComissaoCodigo&OrgaoLicitanteCodigo=$OrgaoLicitanteCodigo&DocCodigo=$Linha[0]";
																	
																if($itemExcluido=="S"){
																	$itemNome = "<s style='text-decoration:line-through;'>".$itemNome."</s> <b>(excluído)</b>";															
																}else{
																	$itemNome = "".$itemNome;
																}
																
															} else {
																
																if($itemExcluido=="S"){
																	$itemNome = "<s style='text-decoration:line-through;'>".$itemNome."</s> <b>(excluído) (arquivo não armazenado)</b>";																																
																}else{
																	$itemNome = "".$itemNome." <b>(arquivo não armazenado)</b>";
																}
																
															}
																														
//															if (!file_exists($Arq)){
//																$itemNome = "".$itemNome." <b>(arquivo não armazenado)</b>";
//															}
															
															?>
																<tr>
																	<td valign="top" bgcolor="#F7F7F7" class="textonegrito">
																		<?php
																			if(file_exists($Arq)){
																		?>
																				<img src="../midia/disquete.gif" border="0">
																		<?php
																			}else{
																		?>
																				<img src="../midia/disqueteInexistente.gif" border="0">
																		<?php
																			}
																		?>
																	</td>
																	<td valign="top" bgcolor="#F7F7F7" class="textonormal"><?=$itemNome?></td>
																	<td valign="top" bgcolor="#F7F7F7" class="textonormal">
																		<?
																			if ( file_exists($Arq) ){
																				echo printf("%01.1f",$tamanho);
																			}else{
																				echo "&nbsp;";
																			}
																		?>
																	</td>
																	<td valign="top" bgcolor="#F7F7F7" class="textonormal"><?=$itemAutor?></td>
																	<td valign="top" bgcolor="#F7F7F7" class="textonormal"><?=$DataGeracaoDocumento?></td>
																	<td valign="top" bgcolor="#F7F7F7" class="textonormal"><?=$DataExclusaoDocumento?></td>
																	<td valign="top" bgcolor="#F7F7F7" class="textonormal"><?=$itemObservacao?></td>
																</tr>
															<?php															
													}
												echo "</table>";
											}else{
													echo "<font class=\"textonegrito\">Nenhum Documento Relacionado!</font><br>&nbsp;\n";
											}



									}
									?>
								
								<table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" style="width:100%;  border:1px;">
								
								<?php
				    	      	# Pega as Fases da Licitação #
				    	      	$sql  	= "SELECT A.EFASESDESC, A.AFASESORDE, B.CLICPOPROC, B.ALICPOANOP, ";
				    	      	$sql   .= "       B.CFASESCODI, B.EFASELDETA, B.TFASELDATA, C.CATASFCODI, ";
				    	      	$sql   .= "       C.EATASFNOME, C.eatasfobse, C.fatasfexcl, U.EUSUPORESP";
								$sql   .= "  FROM SFPC.TBUSUARIOPORTAL U, SFPC.TBFASES A, SFPC.TBFASELICITACAO B LEFT OUTER JOIN SFPC.TBATASFASE C ";
								$sql   .= "    ON B.CLICPOPROC = C.CLICPOPROC AND B.ALICPOANOP = C.ALICPOANOP ";
								$sql   .= "   AND B.CCOMLICODI = C.CCOMLICODI AND B.CGREMPCODI = C.CGREMPCODI ";
								$sql   .= "   AND B.CORGLICODI = C.CORGLICODI AND B.CFASESCODI = C.CFASESCODI ";
								$sql   .= " WHERE B.CLICPOPROC = $Processo AND B.ALICPOANOP = $ProcessoAno ";
								$sql   .= "   AND B.CCOMLICODI = $ComissaoCodigo AND B.CGREMPCODI = $GrupoCodigo ";
								$sql   .= "   AND B.CFASESCODI = A.CFASESCODI AND A.CFASESCODI <> 1 "; //Menos a fase Interna
								$sql   .= "   AND C.CUSUPOCODI = U.CUSUPOCODI ";
								$sql   .= " ORDER BY A.AFASESORDE";
								$result = $db->query($sql);
								if( PEAR::isError($result) ){
								    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
								}
								$Rows = $result->numRows();
								if ($Rows > 0) {
										echo "<tr>\n";
										echo "	<td class=\"textonegrito\" bgcolor=\"#DCEDF7\" colspan=\"4\"> HISTÓRICO </td>\n";
			    	      				echo "</tr>\n";
										echo "<tr>\n";
										echo "	<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonegrito\">FASE</td>\n";
										echo "	<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonegrito\">DATA</td>\n";
										echo "	<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonegrito\">DETALHE</td>\n";
										echo "	<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonegrito\">ATA(S) DA FASE</td>\n";
										echo "</tr>\n";
										
									 	 while( $Linha = $result->fetchRow() ){
												$FaseCodigo = $Linha[4];
												$DataFase = substr($Linha[6],8,2) ."/". substr($Linha[6],5,2) ."/". substr($Linha[6],0,4);
												$FaseDetalhamento = $Linha[5];
												$nomeAta = $Linha[8];
												$itemObservacao = "<b>Observação/ Justificativa:</b> \"".$Linha[9]."\"";
												$itemExcluido = $Linha[10];
												$itemAutor = "<b>Responsável:</b> \"".$Linha[11]."\"";
	
												if(($CodFaseAnterior != "") and ($Linha[4] != $CodFaseAnterior)){
														echo "</td>\n</tr>\n";
												}
												if( $Linha[4] == $CodFaseAnterior ){
														$Arquivo = $GLOBALS["CAMINHO_UPLOADS"]."licitacoes/"."ATASFASE".$GrupoCodigo."_".$Processo."_".$ProcessoAno."_".$ComissaoCodigo."_".$OrgaoLicitanteCodigo."_".$FaseCodigo."_".$Linha[7];
														if (file_exists($Arquivo)){
																$Url = "ConsAcompDownloadAtas.php?GrupoCodigo=$GrupoCodigo&Processo=$Processo&ProcessoAno=$ProcessoAno&ComissaoCodigo=$ComissaoCodigo&OrgaoLicitanteCodigo=$OrgaoLicitanteCodigo&FaseCodigo=$FaseCodigo&AtaCodigo=$Linha[7]";														
																																
																if ($itemExcluido == "S") {
																	echo  "<br><a href='$Url'><img src=../midia/disquete.gif border=0><s style='text-decoration:line-through;'><font color=\"#000000\"> $nomeAta </font></s></a>  - $itemAutor - $itemObservacao <b>(excluído)</b><br/>";
																} else {
																	echo  "<br><a href='$Url'><img src=../midia/disquete.gif border=0> <font color='#000000'> $nomeAta </font></a> - $itemAutor - $itemObservacao<br/>";
																}
																
																
																														
														} else {
															
															if ($itemExcluido == "S") {
																echo  "<br><img src='../midia/disqueteInexistente.gif' border='0'><s style='text-decoration:line-through;'><font color=\"#000000\"> $nomeAta </font></s>  - $itemAutor - $itemObservacao <b> (excluído) (arquivo não armazenado)</b><br/>";
															} else {
																echo  "<br><img src='../midia/disqueteInexistente.gif' border='0'><font color=\"#000000\"> $nomeAta </font>  - $itemAutor - $itemObservacao <b>(arquivo não armazenado)</b><br/>";
															}
															
																
														}
												}else{
														echo "<tr>\n";
														$DataFase = substr($Linha[6],8,2) ."/". substr($Linha[6],5,2) ."/". substr($Linha[6],0,4);
														echo "<td valign=\"middle\" bgcolor=\"#F7F7F7\" class=\"textonormal\">$Linha[0]</td>\n";
														echo "<td valign=\"middle\" bgcolor=\"#F7F7F7\" class=\"textonormal\">$DataFase</td>\n";
														echo "<td valign=\"middle\" bgcolor=\"#F7F7F7\" class=\"textonormal\">$Linha[5]&nbsp;</td>\n";
														if( $Linha[7] != 0 ){
																$Arquivo = $GLOBALS["CAMINHO_UPLOADS"]."licitacoes/"."ATASFASE".$GrupoCodigo."_".$Processo."_".$ProcessoAno."_".$ComissaoCodigo."_".$OrgaoLicitanteCodigo."_".$FaseCodigo."_".$Linha[7];
																if (file_exists($Arquivo)){
																	
																		$Url = "ConsAcompDownloadAtas.php?GrupoCodigo=$GrupoCodigo&Processo=$Processo&ProcessoAno=$ProcessoAno&ComissaoCodigo=$ComissaoCodigo&OrgaoLicitanteCodigo=$OrgaoLicitanteCodigo&FaseCodigo=$FaseCodigo&AtaCodigo=$Linha[7]";																	
																		
																		if ($itemExcluido == "S") {
																			echo "<td valign=\"middle\" bgcolor=\"#F7F7F7\" class=\"textonormal\"><a href=$Url><img src='../midia/disquete.gif' border=0><s style='text-decoration:line-through;'><font color=\"#000000\"> $nomeAta</font></s></a> - $itemAutor - $itemObservacao <b>(excluído)</b><br/>";
																		} else {
																			echo "<td valign=\"middle\" bgcolor=\"#F7F7F7\" class=\"textonormal\"><a href=$Url><img src='../midia/disquete.gif' border=0> <font color=\"#000000\"> $nomeAta </font></a> - $itemAutor - $itemObservacao<br/>";
																		}
																																		 
																} else {
																	if ($itemExcluido == "S") {
																		echo "<td valign=\"middle\" bgcolor=\"#F7F7F7\" class=\"textonormal\"><img src='../midia/disqueteInexistente.gif' border='0'><s style='text-decoration:line-through;'><font color=\"#000000\"> $nomeAta</font></s>  - $itemAutor - $itemObservacao <b>(excluído) (arquivo não armazenado)</b><br/>";
																	} else {
																		echo "<td valign=\"middle\" bgcolor=\"#F7F7F7\" class=\"textonormal\"><img src='../midia/disqueteInexistente.gif' border='0'><font color=\"#000000\"> $nomeAta</font>  - $itemAutor - $itemObservacao <b>(arquivo não armazenado)</b><br/>";
																	}
																	
																}
														}else{
																echo "<td valign=\"middle\" bgcolor=\"#F7F7F7\" class=\"textonormal\">&nbsp;</td>";
														}
												}
												$CodFaseAnterior = $Linha[4];
										}
										echo "</td>\n</tr>\n";
								}
	
								# Busca o(s) resultado(s) da Licitação #
								$sql    = "SELECT ERESLIHABI, ERESLIINAB, ERESLIJULG, ERESLIREVO, ERESLIANUL ";
								$sql   .= "  FROM SFPC.TBRESULTADOLICITACAO ";
								$sql   .= " WHERE CLICPOPROC = $Processo AND ALICPOANOP = $ProcessoAno ";
								$sql   .= "   AND CCOMLICODI = $ComissaoCodigo AND CORGLICODI = $OrgaoLicitanteCodigo";
								$sql   .= "   AND CGREMPCODI = $GrupoCodigo";
								$result = $db->query($sql);
								if( PEAR::isError($result) ){
								    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
								}
								$Rows = $result->numRows();
								if( $Rows == 1 ){
										while( $Linha = $result->fetchRow() ){
												$Resultados    = 1;
												$ResultadoHabi = $Linha[0];
												$ResultadoInab = $Linha[1];
												$ResultadoJulg = $Linha[2];
												$ResultadoRevo = $Linha[3];
												$ResultadoAnul = $Linha[4];
										}
								}else{
										$Resultados = 0;
								}
								$db->disconnect();
								if( ($ResultadoHabi != "")or($ResultadoInab != "")or($ResultadoJulg != "")or($ResultadoRevo != "")or ($ResultadoAnul != "") ){
										echo "<tr>\n";
										echo "<td class=\"textonegrito\" bgcolor=\"#DCEDF7\" colspan=\"4\">RESULTADOS</td>\n";
		    	      		echo "</tr>\n";
								}
								if( $ResultadoHabi != "" ){
										echo "<tr>\n";
				            echo "  <td class=\"textonegrito\" bgcolor=\"#F7F7F7\" colspan=\"4\" align=\"center\" >EMPRESAS HABILITADAS </td>\n";
				            echo "  <tr>\n";
				            echo "  	<td class=\"textonormal\" colspan=\"4\">$ResultadoHabi</td>\n";
				            echo "  </tr>\n";
				            echo "</tr>\n";
		          	}
								if( $ResultadoInab != "" ){
										echo "<tr>\n";
				            echo "  <td class=\"textonegrito\" bgcolor=\"#F7F7F7\" colspan=\"4\" align=\"center\" >EMPRESAS INABILITADAS </td>\n";
				            echo "  <tr>\n";
				            echo "  	<td class=\"textonormal\" colspan=\"4\">$ResultadoInab</td>\n";
				            echo "  </tr>\n";
				            echo "</tr>\n";
		          	}
								if( $ResultadoJulg != "" ){
										echo "<tr>\n";
				            echo "  <td class=\"textonegrito\" bgcolor=\"#F7F7F7\" colspan=\"4\" align=\"center\" > JULGAMENTO </td>\n";
				            echo "  <tr>\n";
				            echo "  	<td class=\"textonormal\" colspan=\"4\">$ResultadoJulg</td>\n";
				            echo "  </tr>\n";
				            echo "</tr>\n";
		          	}
		          	if( $ResultadoRevo != "" ){
										echo "<tr>\n";
				            echo "  <td class=\"textonegrito\" bgcolor=\"#F7F7F7\" colspan=\"4\" align=\"center\" >REVOGAÇÃO </td>\n";
				            echo "  <tr>\n";
				            echo "  	<td class=\"textonormal\" colspan=\"4\">$ResultadoRevo</td>\n";
				            echo "  </tr>\n";
				            echo "</tr>\n";
		          	}
		          	if( $ResultadoAnul != "" ){
										echo "<tr>\n";
				            echo "  <td class=\"textonegrito\" bgcolor=\"#F7F7F7\" colspan=\"4\" align=\"center\" >ANULAÇÃO </td>\n";
				            echo "  <tr>\n";
				            echo "  	<td class=\"textonormal\" colspan=\"4\">$ResultadoAnul</td>\n";
				            echo "  </tr>\n";
				            echo "</tr>\n";
		          	}
		          	?>
						<tr>															
							</tr>
						</table>
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

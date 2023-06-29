<?php
#---------------------------------------------------------------------------------------
# Portal da DGCO
# Programa: RotPregaoRecebimentoConcluidosBB.php
# Autor:    Carlos Abreu
# Data:     16/08/2006
# Objetivo: Receber arquivos via FTP do Banco do Brasil e alimentar tabelas do sistema (APO715)
# OBS.:     Tabulação 2 espaços
#---------------------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";


// Dados para Conexão com FTP do Banco do Brasil
$Host    = "ftp.recife.pe.gov.br";
$Usuario = "dgco";
$Senha   = "lr-36";

// Pastas de Trabalho
$Pasta = "RetornoBB/";
$PastaRemota = "/";

// estabelece o conexão
$conn_id = ftp_connect($Host);

// login com nome de usuário e senha
$login_result = ftp_login($conn_id, $Usuario, $Senha);

//echo "<pre>";

if ((!$conn_id) || (!$login_result)) {
//    echo "A conexão FTP falhou!\n";
//    echo "Tentou conectar ao servidor $Host para o usuário $Usuario";
    exit;
} else {
//    echo "Conectado ao servidor $Host, para o usuário $Usuario...\n\n";
}

// ativa o modo passivo
ftp_pasv($conn_id, true);

// obtém o conteúdo do diretório atual
ftp_chdir($conn_id,$PastaRemota);
$ListaArquivos = ftp_nlist($conn_id, ".");

// Fecha a conexão
ftp_close($conn_id);

// mostra $contents
if ($ListaArquivos){
		$NumArquivos = count($ListaArquivos);
		//echo "Lendo...\n\n";
		for($i=0;$i<$NumArquivos;$i++){
				if ( $Conteudo[$i] = file_get_contents ("ftp://".$Usuario.":".$Senha."@ftp.recife.pe.gov.br".$PastaRemota.$ListaArquivos[$i], "r") ) {
				    //echo ">> ".$ListaArquivos[$i]." - OK\n";
				} else {
				    //echo ">> ".$ListaArquivos[$i]." - ERRO\n";
				}
		}
		//echo "\nConcluído!\n\n";
} else {
		//echo "Nenhum Arquivo Recebido!\n\n";
}

// Trata os arquivos
for ($i=0;$i<$NumArquivos;$i++){
		if ( substr( $Conteudo[$i], 11, 6 ) == "APO715" ){
				$Registros = explode("\n",$Conteudo[$i]);
				for ($ii=0;$ii<count($Registros);$ii++){
						switch(substr($Registros[$ii],0,2)){
								case "00":
										$DiaArquivo            = substr($Registros[$ii],19,2);
										$MesArquivo            = substr($Registros[$ii],21,2);
										$AnoArquivo            = substr($Registros[$ii],23,4);
										$NumeroRemessa         = substr($Registros[$ii],181,9);
										
										# Registra data e hora de recebimento da confirmação de envio do Banco do Brasil #
										$db     = Conexao();
										$sql    = "SELECT COUNT(*) ";
										$sql   .= "  FROM SFPC.TBPREGAOLICITACAOPORTAL ";
										$sql   .= " WHERE CPRELINREM = $NumeroRemessa ";
										$result = $db->query($sql);
										if( PEAR::isError($result) ){
										    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
										}else{
												$Linha = $result->fetchRow();
												if ($Linha[0]==1){
														$sql  = "UPDATE SFPC.TBPREGAOLICITACAOPORTAL ";
														$sql .= "   SET TPRELIDREC = '".$AnoArquivo."-".$MesArquivo."-".$DiaArquivo." 00:00:00' ";
														$sql .= " WHERE CPRELINREM = $NumeroRemessa ";
														$result = $db->query($sql);
														if( PEAR::isError($result) ){
														    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
														}
												}
										}		
					      		$db->disconnect();
										
										break;
								case "01":
										$CodigoLicitacao       = substr($Registros[$ii],2,9);
										$UnidadeOrganizacional = substr($Registros[$ii],70,9);
										$NumeroProcesso        = substr($Registros[$ii],301,20);
										//echo $CodigoLicitacao."\n";
										//echo $UnidadeOrganizacional."\n";
										//echo $NumeroProcesso."\n";
										break;
								case "03":
										$CodigoLicitacaoBB     = substr($Registros[$ii],11,4);
										$ValorLote             = substr($Registros[$ii],15,17);
										$CNPJFornecedor        = substr($Registros[$ii],49,14);
										//echo $CodigoLicitacaoBB."\n";
										//echo $ValorLote."\n";
										//echo $CNPJFornecedor."\n";
										break;
								case "07":
										$DtEntregaProposta     = substr($Registros[$ii],70,26);
										$CNPJFornecedor        = substr($Registros[$ii],96,14);
										//echo $DtEntregaProposta."\n";
										//echo $CNPJFornecedor."\n";
										break;
								case "09":
										$CodigoLoteLicitacaoBB = substr($Registros[$ii],11,4);
										$CodigoItemLicitacaoBB = substr($Registros[$ii],15,4);
										$CNPJFornecedor        = substr($Registros[$ii],28,14);
										$ValorItem             = substr($Registros[$ii],122,17);
										//echo $CodigoLoteLicitacaoBB."\n";
										//echo $CodigoItemLicitacaoBB."\n";
										//echo $CNPJFornecedor."\n";
										//echo $ValorItem."\n";
										break;
								case "10":
										$CodigoLoteLicitacaoBB = substr($Registros[$ii],11,4);
										$CNPJFornecedor        = substr($Registros[$ii],24,14);
										$DtAceitacaoFornecedor = substr($Registros[$ii],122,26);
										$DtBloqueioFornecedor  = substr($Registros[$ii],148,26);
										//echo $CodigoLoteLicitacaoBB."\n";
										//echo $CNPJFornecedor."\n";
										//echo $DtAceitacaoFornecedor."\n";
										//echo $DtBloqueioFornecedor."\n";
										break;
								case "11":
										$Licitacao             = substr($Registros[$ii],2,9);
										$Lote                  = substr($Registros[$ii],11,4);
										$ValorLance            = substr($Registros[$ii],20,17);
										//$LanceVencedor[$Licitacao][$Lote] = substr($Registros[$ii],20,17);
										$DtLanceVencedor[$Licitacao][$Lote] = substr($Registros[$ii],37,26);
										//echo $ValorLance."\n";
										break;
								case "12":
										$CodigoLoteLicitacaoBB = substr($Registros[$ii],11,4);
										$DtEntregaLance        = substr($Registros[$ii],19,26);
										$CNPJFornecedor        = substr($Registros[$ii],54,14);
										$ValorLance            = substr($Registros[$ii],148,15).".".substr($Registros[$ii],163,2);

										$Licitacao             = substr($Registros[$ii],2,9);
										$Lote                  = substr($Registros[$ii],11,4);
										$CodigoFornecedor      = substr($Registros[$ii],45,9);
										$NomeFornecedor        = substr($Registros[$ii],98,50);
										if ( $DtLanceVencedor[$Licitacao][$Lote] == $DtEntregaLance ){
											
												$db   = Conexao();
												$sql  = "
													SELECT AFORCRSEQU 
													FROM SFPC.TBFORNECEDORCREDENCIADO 
													WHERE 
														AFORCRCCGC = '$CNPJFornecedor' 
														AND FFORCRTIPO = 'L'
												";
												$result = $db->query($sql);
												if( PEAR::isError($result) ){
												    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
												}	else {
														while( $Linha = $result->fetchRow() ){
																$CodigoFornecedor = $Linha[0];
														}
												}
												$sql  = "SELECT CGREMPCODI, CLICPOPROC, ALICPOANOP, CCOMLICODI, CORGLICODI ";
												$sql .= "  FROM SFPC.TBPREGAOLICITACAOPORTAL ";
												$sql .= " WHERE CPRELINREM = $NumeroRemessa ";
												$result = $db->query($sql);
												if( PEAR::isError($result) ){
												    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
												}	else {
														$LinhaPregao = $result->fetchRow();
														$sql  = "SELECT COUNT(*) ";
														$sql .= "  FROM SFPC.TBPREGAORESULTADOBB ";
														$sql .= " WHERE CGREMPCODI = $LinhaPregao[0] ";
														$sql .= "   AND CLICPOPROC = $LinhaPregao[1] ";
														$sql .= "   AND ALICPOANOP = $LinhaPregao[2] ";
														$sql .= "   AND CCOMLICODI = $LinhaPregao[3] ";
														$sql .= "   AND CORGLICODI = $LinhaPregao[4] ";
														$sql .= "   AND CLOTLICODI = $Lote ";
														$result = $db->query($sql);
														if( PEAR::isError($result) ){
														    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
														}	else {
																$Linha = $result->fetchRow();
																if ($Linha[0] > 0){
																		$sql  = "UPDATE SFPC.TBPREGAORESULTADOBB SET ";
																		$sql .= "       AFORCRSEQU = $CodigoFornecedor, NRESBBFORN = '$NomeFornecedor', ARESBBCCGC = '$CNPJFornecedor', VRESBBVALO = '$ValorLance', ";
																		$sql .= "       TRESBBULAT = '".date("Y-m-d H:i:s")."'";
																		$sql .= " WHERE CGREMPCODI = $LinhaPregao[0] ";
																		$sql .= "   AND CLICPOPROC = $LinhaPregao[1] ";
																		$sql .= "   AND ALICPOANOP = $LinhaPregao[2] ";
																		$sql .= "   AND CCOMLICODI = $LinhaPregao[3] ";
																		$sql .= "   AND CORGLICODI = $LinhaPregao[4] ";
																		$sql .= "   AND CLOTLICODI = $Lote ";
																		$result = $db->query($sql);
																		if( PEAR::isError($result) ){
																		    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																		}
																} else {
																    $sql  = "INSERT INTO SFPC.TBPREGAORESULTADOBB ";
																    $sql .= "       (CGREMPCODI, CLICPOPROC, ALICPOANOP, CCOMLICODI, CORGLICODI, ";
																    $sql .= "        CLOTLICODI, AFORCRSEQU, NRESBBFORN, ARESBBCCGC, VRESBBVALO, ";
																    $sql .= "        TRESBBULAT ) VALUES ( ";
																    $sql .= "        $LinhaPregao[0], $LinhaPregao[1], $LinhaPregao[2], $LinhaPregao[3], $LinhaPregao[4], ";
																    $sql .= "        $Lote, $CodigoFornecedor, '$NomeFornecedor', '$CNPJFornecedor', '$ValorLance', ";
																    $sql .= "        '".date("Y-m-d H:i:s")."'";
																    $sql .= " )";
																		$result = $db->query($sql);
																		if( PEAR::isError($result) ){
																		    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																		}
																}
														}
												}
							      		$db->disconnect();
												
										}
										
										//echo $CodigoLoteLicitacaoBB."\n";
										//echo $DtEntregaLance."\n";
										//echo $CNPJFornecedor."\n";
										//echo $ValorLance."\n";
										
										break;
								case "15":
										$CNPJFornecedor        = substr($Registros[$ii],20,14);
										$TextoOcorrencia       = substr($Registros[$ii],114,26);
										//echo $CNPJFornecedor."\n";
										//echo $TextoOcorrencia."\n";
										break;
						}
						
				}
				$db   = Conexao();
				$sql    = "SELECT CUSUPOCODI ";
				$sql   .= "  FROM SFPC.TBLICITACAOPORTAL ";
				$sql .= " WHERE CGREMPCODI = $LinhaPregao[0] ";
				$sql .= "   AND CLICPOPROC = $LinhaPregao[1] ";
				$sql .= "   AND ALICPOANOP = $LinhaPregao[2] ";
				$sql .= "   AND CCOMLICODI = $LinhaPregao[3] ";
				$sql .= "   AND CORGLICODI = $LinhaPregao[4] ";
				$result = $db->query($sql);
				if( PEAR::isError($result) ){
				    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
				}else{
						$Linha = $result->fetchRow();
						$UsuarioResponsavel = $Linha[0];
				}
				$sql    = "SELECT SUM(VRESBBVALO) ";
				$sql   .= "  FROM SFPC.TBPREGAORESULTADOBB ";
				$sql .= " WHERE CGREMPCODI = $LinhaPregao[0] ";
				$sql .= "   AND CLICPOPROC = $LinhaPregao[1] ";
				$sql .= "   AND ALICPOANOP = $LinhaPregao[2] ";
				$sql .= "   AND CCOMLICODI = $LinhaPregao[3] ";
				$sql .= "   AND CORGLICODI = $LinhaPregao[4] ";
				$result = $db->query($sql);
				if( PEAR::isError($result) ){
				    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
				}else{
						$Linha = $result->fetchRow();
						$Valor = $Linha[0];

						$sql  = "SELECT COUNT(*) FROM SFPC.TBFASELICITACAO ";
						$sql .= " WHERE CGREMPCODI = $LinhaPregao[0] ";
						$sql .= "   AND CLICPOPROC = $LinhaPregao[1] ";
						$sql .= "   AND ALICPOANOP = $LinhaPregao[2] ";
						$sql .= "   AND CCOMLICODI = $LinhaPregao[3] ";
						$sql .= "   AND CORGLICODI = $LinhaPregao[4] ";
						$sql .= "   AND CFASESCODI = 13";
						$result = $db->query($sql);
						if( PEAR::isError($result) ){
							   ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
						} else {
								$Linha = $result->fetchRow();
								if ($Linha[0]!=1){
										$db->query("BEGIN TRANSACTION");
										$sql  = "INSERT INTO SFPC.TBFASELICITACAO ( ";
										$sql .= "       CGREMPCODI, CFASESCODI, CLICPOPROC, ALICPOANOP, CCOMLICODI, CORGLICODI, ";
										$sql .= "       EFASELDETA, TFASELDATA, CUSUPOCODI, TFASELULAT )";
										$sql .= "VALUES (";
										$sql .= "       $LinhaPregao[0], 13, $LinhaPregao[1], $LinhaPregao[2], $LinhaPregao[3], $LinhaPregao[4], ";
										$sql .= "       'RETORNO DO BANCO DO BRASIL', '".date("Y-m-d")."', $UsuarioResponsavel, '".date("Y-m-d H:i:s")."' )";
										$result = $db->query($sql);
										if( PEAR::isError($result) ){
												$Rollback = 1;
												$db->query("ROLLBACK");
										    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
										}
										$sql     = "UPDATE SFPC.TBLICITACAOPORTAL ";
										$sql    .= "   SET VLICPOVALH = $Valor, VLICPOTGES = $Valor, ";
										$sql    .= "       TLICPOULAT = '".date("Y-m-d H:i:s")."' ";
										$sql    .= " WHERE CLICPOPROC = $LinhaPregao[1] AND ALICPOANOP = $LinhaPregao[2] ";
										$sql    .= "   AND CCOMLICODI = $LinhaPregao[3] AND CGREMPCODI = $LinhaPregao[0] ";
										$sql    .= "   AND CORGLICODI = $LinhaPregao[4] ";
								    $result  = $db->query($sql);
										if( PEAR::isError($result) ){
												$Rollback = 1;
												$db->query("ROLLBACK");
										    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
										}
										if( $Rollback == "" ){ $db->query("COMMIT"); }
										$db->query("END TRANSACTION");
								}
						}
				}
				$db->disconnect();
				//var_dump( $Registros );
		}
}
?> 

<?php
# -------------------------------------------------------------------------
# Portal da DGCO
# Programa: ConsAvisosArquivo.php
# Autor:    Roberta Costa
# Data:     09/05/2003
# Objetivo: Programa de Download de Avisos de Licitação
# OBS.:     Tabulação 2 espaços
# -------------------------------------------------------------------------
# Alterado: Álvaro Faria / Carlos Abreu
# Data:     23/08/2006 - Apaga arquivo temporário anterior apenas se ele
#                        foi criado a mais de 10 minutos
# -------------------------------------------------------------------------
# Alterado: Carlos Abreu
# Data:     25/08/2006 - Mudança de Variáveis GET para POST
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

if ($_SESSION['ValidaArquivoDownload']!="ValidaArquivoDownload"){
		TiraSeguranca();
		header("location: /portalcompras/");
		exit;
}

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/licitacoes/ConsAvisosDocumentos.php' );

# Variáveis com o global off #
if($_SERVER['REQUEST_METHOD'] == "POST"){
		$Botao                = $_POST['Botao'];
		$Critica              = $_POST['Critica'];
		$Objeto               = $_POST['Objeto'];
		$OrgaoLicitanteCodigo = $_POST['OrgaoLicitanteCodigo'];
		$ComissaoCodigo       = $_POST['ComissaoCodigo'];
		$ModalidadeCodigo     = $_POST['ModalidadeCodigo'];
		$GrupoCodigo          = $_POST['GrupoCodigo'];
		$LicitacaoProcesso    = $_POST['LicitacaoProcesso'];
		$LicitacaoAno         = $_POST['LicitacaoAno'];
		$DocumentoCodigo      = $_POST['DocumentoCodigo'];
		$SolicitanteCodigo    = $_POST['SolicitanteCodigo'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "ConsAvisosArquivo.php";

# Abre o arquivo para donwload #
if($Botao == "Download"){
		if( ( $GrupoCodigo != "" ) && ( $ComissaoCodigo != "" ) && ( $LicitacaoProcesso != "" ) && ( $LicitacaoAno != "" ) && ( $DocumentoCodigo != "" ) ){

				# Procura o nome do arquivo na tabela de documentos #
				$db     = Conexao();
				$sql    = "SELECT EDOCLINOME FROM SFPC.TBDOCUMENTOLICITACAO ";
				$sql   .= " WHERE CLICPOPROC = $LicitacaoProcesso ";
				$sql   .= "   AND ALICPOANOP = $LicitacaoAno AND CCOMLICODI = $ComissaoCodigo ";
				$sql   .= "   AND CGREMPCODI = $GrupoCodigo AND CDOCLICODI = $DocumentoCodigo";
				$result = $db->query($sql);
				if( PEAR::isError($result) ){
						ExibeErroBD("$ErroPrograma\nLinha: 68\nSql: $sql");
				}else{
						while( $Linha = $result->fetchRow() ){
								$NomeArquivo = $Linha[0];
						}
				}
				$db->disconnect();

				# Copia arquivo para dentro do diretório tmp #
				$ArquivoNomeServidor = "licitacoes/DOC".$GrupoCodigo."_".$LicitacaoProcesso."_".$LicitacaoAno."_".$ComissaoCodigo."_".$OrgaoLicitanteCodigo."_".$DocumentoCodigo;
				$Arq = $GLOBALS["CAMINHO_UPLOADS"].$ArquivoNomeServidor;
				if( file_exists($Arq) ){

						session_unregister( "ValidaArquivoDownload" );
						addArquivoAcesso($ArquivoNomeServidor);
						$url = "../carregarArquivo.php?arq=".urlencode($ArquivoNomeServidor)."&arq_nome=".urlencode($NomeArquivo);
						header("location: $url ");
						exit();
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
<form action="ConsAvisosArquivo.php" method="post" name="Avisos">
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
								<td align="center" bgcolor="#75ADE6" valign="middle" colspan="9" class="titulo3">
									AVISOS DE LICITAÇÕES - PROTOCOLO DE ENTREGA
								</td>
							</tr>
							<tr>
								<td class="textonormal" colspan="9">
									<p align="justify">
										O Protocolo de Entrega foi gerado de acordo com as informações abaixo.
										O Documento está liberado para visualização ou download. <br>
										Para retornar para a tela de Resultado clique no botão "Voltar".
									</p>
								</td>
							</tr>
							<tr>
								<td colspan="9">
								<?
									$db     = Conexao();
									$sql    = "SELECT ELISOLNOME, CLISOLCNPJ, CLISOLCCPF, ELISOLMAIL, ";
									$sql   .= "       ELISOLENDE, ALISOLFONE, ALISOLNFAX, NLISOLCONT, ";
									$sql   .= "       FLISOLPART ";
									$sql   .= "  FROM SFPC.TBLISTASOLICITAN ";
									$sql   .= " WHERE CLICPOPROC = $LicitacaoProcesso AND ALICPOANOP = $LicitacaoAno ";
									$sql   .= "   AND CGREMPCODI = $GrupoCodigo AND CCOMLICODI = $ComissaoCodigo ";
									$sql   .= "   AND CORGLICODI = $OrgaoLicitanteCodigo AND CLISOLCODI = $SolicitanteCodigo";
									$result = $db->query($sql);
									if( PEAR::isError($result) ){
											ExibeErroBD("$ErroPrograma\nLinha: 157\nSql: $sql");
									}
									while( $Linha = $result->fetchRow() ){
											$RazaoSocial  = $Linha[0];
											$CNPJ         = $Linha[1];
											$CPF          = $Linha[2];
											$Email        = $Linha[3];
											$Endereco     = $Linha[4];
											$Telefone     = $Linha[5];
											$Fax          = $Linha[6];
											$Contato      = $Linha[7];
											$Participacao = $Linha[8];
									?>
									<table class="textonormal" border="0" summary="">
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" height="20">Processo </td>
											<td class="textonormal"><?php echo $LicitacaoProcesso; ?></td>
										</tr>
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" height="20">Ano </td>
											<td class="textonormal"><?php echo $LicitacaoAno; ?></td>
										</tr>
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" height="20">Razão Social</td>
											<td class="textonormal"><?echo $RazaoSocial; ?></td>
										</tr>
										<tr>
											<?if( $CNPJ == "" ){?>
											<td class="textonormal" bgcolor="#DCEDF7" height="20">CPF</td>
											<td class="textonormal"><?php echo FormataCPF($CPF); ?></td>
											<?}else{?>
											<td class="textonormal" bgcolor="#DCEDF7" height="20">CNPJ</td>
											<td class="textonormal"><?php echo FormataCNPJ($CNPJ); ?></td>
											<?}?>
										</tr>
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" height="20">Endereço</td>
											<td class="textonormal"><?echo $Endereco; ?></td>
										</tr>
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" height="20">E-mail</td>
											<td class="textonormal"><?echo $Email; ?></td>
										</tr>
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" height="20">Telefone</td>
											<td class="textonormal"><?echo $Telefone; ?></td>
										</tr>
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" height="20">Fax</td>
											<td class="textonormal"><?echo $Fax; ?></td>
										</tr>
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" height="20">Nome do Contato</td>
											<td class="textonormal"><?echo $Contato; ?></td>
										</tr>
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" height="20">Deseja Participar da Licitação</td>
											<td class="textonormal">
												<?if( $Participacao == "S" ){ echo "SIM"; }else{ echo "NÃO"; }?>
											</td>
										</tr>
									</table>
									<?php }
									$db->disconnect(); ?>
								</td>
							</tr>
							<tr>
								<td class="textonormal" align="right">
									<input type="hidden" name="Objeto" value="<?echo $Objeto?>">
									<input type="hidden" name="OrgaoLicitanteCodigo" value="<?echo $OrgaoLicitanteCodigo?>">
									<input type="hidden" name="ComissaoCodigo" value="<?echo $ComissaoCodigo?>">
									<input type="hidden" name="ModalidadeCodigo" value="<?echo $ModalidadeCodigo?>">
									<input type="hidden" name="GrupoCodigo" value="<?echo $GrupoCodigo?>">
									<input type="hidden" name="LicitacaoProcesso" value="<?echo $LicitacaoProcesso?>">
									<input type="hidden" name="LicitacaoAno" value="<?echo $LicitacaoAno?>">
									<input type="hidden" name="DocumentoCodigo" value="<?echo $DocumentoCodigo?>">
									<input type="hidden" name="SolicitanteCodigo" value="<?echo $SolicitanteCodigo?>">
									<input type="hidden" name="Botao" value="">
									<table border=0>
									<tr><td>
									<input type="button" name="Download" value="Download" class="botao" onClick="javascript:enviar('Download');">
									</td>
									</form>
									<form action="ConsAvisosDocumentos.php" method="post" name="Voltar">
									<td>
									<input type="hidden" name="Objeto" value="<?echo $Objeto?>">
									<input type="hidden" name="OrgaoLicitanteCodigo" value="<?echo $OrgaoLicitanteCodigo?>">
									<input type="hidden" name="ComissaoCodigo" value="<?echo $ComissaoCodigo?>">
									<input type="hidden" name="ModalidadeCodigo" value="<?echo $ModalidadeCodigo?>">
									<input type="hidden" name="GrupoCodigo" value="<?echo $GrupoCodigo?>">
									<input type="hidden" name="LicitacaoProcesso" value="<?echo $LicitacaoProcesso?>">
									<input type="hidden" name="LicitacaoAno" value="<?echo $LicitacaoAno?>">
									<input type="hidden" name="DocumentoCodigo" value="<?echo $DocumentoCodigo?>">
									<input type="hidden" name="SolicitanteCodigo" value="<?echo $SolicitanteCodigo?>">
									<input type="button" name="Voltar" value="Voltar" class="botao" onClick="document.Voltar.submit();">
									</td></tr>
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
</form>
</body>
</html>

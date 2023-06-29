<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadLicitacaoIncluir.php
# Autor:    Gladstone Barbosa
# Data:     09/02/2012
# Objetivo: Incluir Licitacoes de Compra do sistema
#-------------------------------------------------------------------------
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------

$programa = "CadLicitacaoConsultarSolicitacao.php";
# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

# Acesso ao arquivo de funções #
require_once("../compras/funcoesCompras.php");

#incluindo funcoes de ajuda
require_once("funcoesComplementaresLicitacao.php");


# Executa o controle de segurança #
session_start();

Seguranca();

AddMenuAcesso('/compras/ConsAcompSolicitacaoCompra.php' );

# Abrindo Conexão
$db = Conexao();

$intCodUsuario 					= $_SESSION['_cusupocodi_'];
$perfilCorporativo  			= $_SESSION['_fperficorp_'];
$GrupoUsuario					= $_SESSION['_cgrempcodi_'];

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST" ){
	

	$Botao        					= $_POST['Botao'];
	$DataIni      					= $_POST['DataIni'];
	$Orgao		  					= $_POST['Orgao'];
	$Situacao     					= $_POST['Situacao'];
	$DataFim      					= $_POST['DataFim'];
	$tipoSelecao 					= $_POST['tipoSelecao'];
	$idSolicitacao 					= $_POST['idSolicitacao'];
	$CodSolicitacaoPesquisaDireta 	= $_POST['CodSolicitacaoPesquisaDireta'];
	$ProgramaReferencia				= $_POST['ProgramaReferencia'];
	
	
	/*Dados que vem da tela de alterar*/
	$Processo = $_POST['Processo'];
	$ProcessoAno = $_POST['ProcessoAno'];
	$OrgaoLicitanteCodigo = $_POST['OrgaoLicitanteCodigo'];
	$ComissaoCodigo = $_POST['ComissaoCodigo'];
	
	if((!isset($_POST['Processo']))||(!isset($_POST['ProcessoAno']))||(!isset( $_POST['ComissaoCodigo']))||(!isset( $_POST['OrgaoLicitanteCodigo']))){
		$erro = true;
	}
	
}else{
	$erro = true;
}

if($erro){
	$Mensagem = urlencode("Tela de selecção com parametros inválidos.");
	$Url = "CadResultadoSelecionar.php?Mensagem=$Mensagem&Mens=1&Tipo=2&Critica=0";
	if (!in_array($Url,$_SESSION['GetUrl'])){
		$_SESSION['GetUrl'][] = $Url;
	}
	header("location: ".$Url);
	exit;
}


if($acao==""){
	$acao = "Pesquisar";
}
if( $Botao == "PesquisaGeral" ){
	# Critica dos Campos #
	$pesquisa = true;
$CodSolicitacaoPesquisaDireta = "";
$MensErro = ValidaPeriodo($DataIni,$DataFim,$Mens,"formulario");
if( $MensErro != "" ){
	adicionarMensagem("<a href='javascript:formulario.Justificativa.focus();' class='titulo2'>$MensErro</a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
	$pesquisa = false;
}else{
			if($DataIni==""){
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.".$Programa.".DataIni.focus();\" class=\"titulo2\">Data Inicial inválida.</a><br>";
				$pesquisa = false;
			}
			if($DataFim==""){
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.".$Programa.".DataFim.focus();\" class=\"titulo2\">Data Final inválida.</a><br>";
				$pesquisa = false;
			}
			
			if ( (DataInvertida($DataIni) > DataAtual()) && $Mens ==0 ){
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.".$Programa.".DataIni.focus();\" class=\"titulo2\">Data Inicial maior que a Data Atual</a>";
				$pesquisa = false;
			}
			
}

if( $Situacao == "" ){
	$Mens      = 1;
	$Tipo      = 2;
	if ( $Mensagem != ""){
		$Mensagem .= "e informe o ";
	}else{
		$Mensagem .= "Informe: ";
	}
	$Mensagem .= "<a href=\"javascript:document.formulario.Situacao.focus();\" class=\"titulo2\">Situação</a>";
	$pesquisa = false;
}

if( $Orgao == "" ){
	$Mens      = 1;
	$Tipo      = 2;
	if ( $Mensagem != ""){
		$Mensagem .= "e  informe o ";
	}else{
		$Mensagem .= "Informe: ";
	}
	$Mensagem .= "<a href=\"javascript:document.formulario.Orgao.focus();\" class=\"titulo2\">Orgão</a>";
	$pesquisa = false;
}

if ( $pesquisa ){
	$arrLinhas = listarIndividual($Situacao, $Orgao, $DataIni, $DataFim, $strSolicitacao , true , true);
	$arrLinhasGrupo = listarGrupo($Situacao,$Orgao,$DataIni,$DataFim,$strSolicitacao,"",true);
	$acao = "Pesquisar";
	}
}

if ( $Botao == "PesquisaDireta"){
	$problemas = 0;
	if ( $CodSolicitacaoPesquisaDireta != "") {
		if ( isNumeroSCCValido($CodSolicitacaoPesquisaDireta) ){
			$strSolicitacao = getSequencialSolicitacaoCompra($db, $CodSolicitacaoPesquisaDireta);
			
			if ( $strSolicitacao != null){
				$arrLinhas = listarIndividual("8","TODOS","", "",$strSolicitacao, true , true);
				
				if ( count($arrLinhas) > 0 ){
					$pesquisa = true;
					$acao = "Pesquisar";
				}else{
                    $msg = "Solicitação com situação diferente de encaminhada ou não pertence a comissão do usuário logado";
					$problemas = 1;
				}
			} else {
                $msg = "Código de solicitação inválido";
				$problemas = 1;
            }
        } else {
            $msg = "Código de solicitação inválido";
			$problemas = 1;
        }
    } else {
        $msg = "Informe: Número da SCC";
		$problemas = 1;
    }	

    if ($problemas > 0) {
        $Mens = 1;
        $Tipo = 2;
		$Mensagem .= "<a href=\"javascript:document.".$Programa.".CodSolicitacaoPesquisaDireta.focus();\" class=\"titulo2\">$msg</a>";
	}
}
?>

<html>
<?php
# Carrega o layout padrão #
layout();
?>

<script language="javascript" src="../janela.js" type="text/javascript"></script>
<script type="text/javascript">

function enviar(valor){
	if(valor=="Selecionar"){
		document.formulario.action = '<?php echo $ProgramaReferencia;?>';
	}
	document.formulario.Botao.value = valor;
	document.formulario.submit();
}
function AbreJanela(url,largura,altura){
	window.open(url,'paginadetalhe','status=no,scrollbars=yes,left=15,top=15,width='+largura+',height='+altura);
}

<?php MenuAcesso(); ?>

</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<script language="JavaScript">
$(document).ready(function(){
	$(".detalhar").live("click", function() {
		var seq = $(this).attr("id");
		var valAtual = $(this).html();
		if(valAtual=="+"){
				$(this).html("-");
				$(".opdetalhe."+seq).show();
		}else{
				$(this).html("+");
				$(".opdetalhe."+seq).hide();
		}
	});
});
</script>
<form action="<?=$programa?>" method="post" name="formulario">
<input type="hidden" name="Processo" value="<?php echo $Processo; ?>"/>
<input type="hidden" name="ProcessoAno" value="<?php echo $ProcessoAno; ?>"/>
<input type="hidden" name="OrgaoLicitanteCodigo" value="<?php echo $OrgaoLicitanteCodigo; ?>"/>
<input type="hidden" name="ComissaoCodigo" value="<?php echo $ComissaoCodigo; ?>"/>
<input type="hidden" name="ProgramaReferencia" value="<?php echo $ProgramaReferencia;?>" />

<input type="hidden" name="Botao" id="Botao"value=""/>
<input type="hidden" id="limiteCompra" name="limiteCompra" value="<?php if(($CodigoOrgaoLicitante!="")&&($ModalidadeCodigo!="")&&(count($arrCodMaterialServico)>0)){ echo converte_valor(calculaLimiteCompra($CodigoOrgaoLicitante, $ModalidadeCodigo, $arrCodMaterialServico, $arrTipoItens));}else{echo 0;}?>"/>
<br><br><br><br><br>
<table width="100%" cellpadding="3" border="0" summary="">
	<!-- Caminho -->
	<tr>
		<td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
		<td align="left" class="textonormal" colspan="2">
			<font class="titulo2">|</font>
			<a href="../index.php"><font color="#000000">Página Principal</font></a> > Licitações > Licitação > Incluir
		</td>
	</tr>
	<!-- Fim do Caminho-->
	<!-- Erro -->
	<?php if($Mens == 1){?>
	<tr>
		<td width="150"></td>
		<td align="left" colspan="2"><?php ExibeMens($Mensagem,$Tipo,1); ?></td>
	</tr>
	<?php } ?>
	<!-- Fim do Erro -->
	<!-- Corpo -->
	<tr>
		<td width="150"></td>
		<td class="textonormal">
			<table width="100%" border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal"  bgcolor="#FFFFFF">
				<tr>
					<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3" colspan="4">
						 INCLUIR - LICITAÇÃO
					</td>
				</tr>
			
				<tr>
					<td align="left" valign="middle" colspan="4">
						Para pesquisar uma SCC individual, digite o número da SCC simples ou agrupada e clique na lupa.<br />
						Para pesquisar de formar geral digite as informações a baixo e clique no botão Pesquisar.
					</td>
				</tr>
				<tr>
					<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3" colspan="4">
						 PESQUISAR DIRETA - SOLICITAÇÃO DE COMPRA E CONTRATAÇÃO (SCC)
					</td>
				</tr>
				<tr>
					<td width="30%" align="left" bgcolor="#DCEDF7" class="textonormal" colspan="1">
						SCC*
					</td>
					<td aling="left" colspan="3">
						<input type="text" value="<?php if(isset($CodSolicitacaoPesquisaDireta))echo $CodSolicitacaoPesquisaDireta;?>" name="CodSolicitacaoPesquisaDireta" maxlength="14" class="solicitacao"/>
						<input type="button" name="PesquisaDireta" value="Pesquisar" class="botao" onClick="javascript:enviar('PesquisaDireta')">
					</td>
				</tr>
				<tr>
					<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3" colspan="4">
						 PESQUISAR GERAL - SOLICITAÇÃO DE COMPRA E CONTRATAÇÃO (SCC)
					</td>
				</tr>
				<tr>
					<td colspan="4">
						<table border="0" width="100%" summary="">
							<tr>
								<td class="textonormal" bgcolor="#DCEDF7" width="30%" height="20">Órgão*</td>
								<td class="textonormal">
									<select name="Orgao" class="textonormal">
										
										<option value="">Selecione um Órgao...</option>
										<option <?php if($Orgao=="TODOS"){echo "selected='selected'";}?> value="TODOS">Todos</option>
										<?php
										
										$sql  = "SELECT ORG.CORGLICODI, ORG.EORGLIDESC
										FROM  SFPC.TBORGAOLICITANTE ORG
										WHERE ORG.FORGLISITU = 'A'
										AND ORG.CORGLICODI IN (SELECT distinct(SOL.CORGLICODI) FROM SFPC.TBSOLICITACAOCOMPRA SOL )
										ORDER BY ORG.EORGLIDESC
										";
						
										$res = $db->query($sql);
										if( PEAR::isError($res) ){
											$CodErroEmail  = $res->getCode();
											$DescErroEmail = $res->getMessage();
											var_export($DescErroEmail);
											ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
										}else{
											while( $Linha = $res->fetchRow() ){
												if($Linha[0]==$Orgao){
													echo "<option selected='selected' value=\"$Linha[0]\">$Linha[1]</option>\n";
												}else{
													echo "<option value=\"$Linha[0]\">$Linha[1]</option>\n";
												}
											}
										}
										?>
									</select>
								</td>
							</tr>
							<tr>
								<td class="textonormal" bgcolor="#DCEDF7" width="30%" height="20">Situação*</td>
								<td class="textonormal">
									<input name="Situacao" type="hidden" value="8">ENCAMINHADA
								</td>
							</tr>
							<tr>
								<td class="textonormal" bgcolor="#DCEDF7" width="30%" height="20">Período*</td>
								<td class="textonormal">
									<?php
									$DataMes = DataMes();
									if( $DataIni == "" ){ $DataIni = $DataMes[0]; }
									if( $DataFim == "" ){ $DataFim = $DataMes[1]; }									$URLIni = "../calendario.php?Formulario=formulario&Campo=DataIni";
									$URLFim = "../calendario.php?Formulario=formulario&Campo=DataFim";
									?>
									<input type="text" name="DataIni" size="10" maxlength="10" value="<?php echo $DataIni;?>" class="textonormal">
									<a href="javascript:janela('<?php echo $URLIni ?>','Calendario',220,170,1,0)"><img src="../midia/calendario.gif" border="0" alt=""></a>
									&nbsp;a&nbsp;
									<input type="text" name="DataFim" size="10" maxlength="10" value="<?php echo $DataFim;?>" class="textonormal">
									<a href="javascript:janela('<?php echo $URLFim ?>','Calendario',220,170,1,0)"><img src="../midia/calendario.gif" border="0" alt=""></a>
								</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td class="textonormal" align="right" colspan="4">
						<input type="button" name="PesquisaGeral" value="Pesquisar" class="botao" onClick="javascript:enviar('PesquisaGeral')">
						<input type="button" name="Limpar" value="Limpar" class="botao" onClick="javascript:enviar('Limpar')">
						
					</td>
				</tr>
			</table>
			<table width="100%" border="1" summary="" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" bgcolor="#FFFFFF">
			<?php
				if( $pesquisa ){
			?>
					<tr>
						<td style="border-top: 0px;" align="center" bgcolor="#75ADE6" colspan="4" class="titulo3">RESULTADO DA PESQUISA</td>
					</tr>
					<?php
					$QtdRegistros = count($arrLinhas);
					$QtdRegistrosGrupo = count($arrLinhasGrupo);
					if ( $QtdRegistros > 0 || $QtdRegistrosGrupo > 0){
						$DescricaoOrgao = "";
						$DescricaoCentroCusto = "";
						if ( $QtdRegistros > 0){
						foreach ( $arrLinhas as $linhas ){
					?>
							<!-- INÍCIO SOLICITAÇÃO INDIVIDUAL -->
							<?php 
								if ( $DescricaoOrgao != $linhas['DescOrgao'] ){
							?>
								<tr class="linhaorgao">
									<td align="center" bgcolor="#BFDAF2" colspan="5" class="titulo3"><?php echo $linhas['DescOrgao'];?></td>
								</tr>
							<?php 
									$DescricaoOrgao = $linhas['DescOrgao'];
								}
								
								if ( $DescricaoCentroCusto !=  $linhas['DescCentroCusto'] ){ 
							?>
								<tr class="linhacentro">
									<td align="center" bgcolor="#DDECF9" colspan="5" class="titulo3"><?php echo $linhas['DescCentroCusto'];?></td>
								</tr>
								<tr class="linhainfo">
									<td class="titulo3" bgcolor="#F7F7F7">SOLICITAÇÃO</td>
									<td class="titulo3" bgcolor="#F7F7F7">DETALHAMENTO</td>
									<td class="titulo3" bgcolor="#F7F7F7">DATA</td>
									<td class="titulo3" bgcolor="#F7F7F7">SITUAÇÃO</td>
								</tr>
							<?php 
									$DescricaoCentroCusto = $linhas['DescCentroCusto'];
								}
								$programaSelecao =  $GLOBALS["DNS_SISTEMA"]."compras/ConsAcompSolicitacaoCompra.php";								$Url = $programaSelecao."?SeqSolicitacao=".$linhas['SeqSolicitacao']."&programa=".$programa;
								$strSolicitacaoCodigo = getNumeroSolicitacaoCompra($db, $linhas['SeqSolicitacao']); 
							?>
								<tr class="linhasol">
									<td valign="top" bgcolor="#F7F7F7" class="textonormal">
										<input type="radio" class="idSolicitacao soli" name="idSolicitacao" value="<?php echo $linhas['SeqSolicitacao'].'-I';?>" />
										<a href="<?php echo $Url;?>">
											<font color="#000000"><?php echo $strSolicitacaoCodigo;?></font>
										</a>
										<span style="cursor:pointer;margin-left:5px;margin-right:10px;" id="<?php echo $linhas['SeqSolicitacao'];?>" class="detalhar" onclick="">+</span>
									</td>
									<td valign="top" bgcolor="#F7F7F7" class="textonormal"><?php echo $linhas['DetaCentroCusto'];?></td>
									<td valign="top" bgcolor="#F7F7F7" class="textonormal"><?php echo $linhas['DataSolicitacao'];?></td>
									<td valign="top" bgcolor="#F7F7F7" class="textonormal"><?php echo $linhas['DescSolicitacao'];?></td>
								</tr>
							<!-- FIM SOLICITAÇÃO INDIVIDUAL -->
					<?php
							exibeDetalhamento($linhas['SeqSolicitacao']);
						}//Fim do Foreach Individual
						}
						$contagemGrupo = 0;
						$DescricaoOrgao = "";
						if ( $QtdRegistrosGrupo > 0 ){
						foreach ( $arrLinhasGrupo as $linhas){
							if ( $DescricaoOrgao != $linhas['DescOrgao'] & $linhas['FlagGrupo'] == "S" ){ 
						?>
						<tr class="linhaorgao">
							<td align="center" bgcolor="#BFDAF2" colspan="5" class="titulo3"><?php echo $linhas['DescOrgao'];?></td>
						</tr>
							<!-- INÍCIO SOLICITAÇÕES AGRUPADAS -->
							<?php 
							}
							if ($linhas['FlagGrupo'] == "S" ){ 
								$contagemGrupo++;
							?>
								
								<tr>
									<td align="left" bgcolor="#BFDAF2" colspan="5" class="titulo3">
										<input type="radio" class="idSolicitacao" name="idSolicitacao" value="<?php echo $linhas['CodGrupo'].'-G';?>" />
										<?php echo $contagemGrupo;?> - Agrupamento - DATA: <?php echo $linhas['DataAgrupamento'];?>
									</td>
								</tr>
								<tr>
									<td class="titulo3" bgcolor="#F7F7F7">SOLICITAÇÃO</td>
									<td colspan="2" class="titulo3" bgcolor="#F7F7F7">ORGÃO</td>
									<td class="titulo3" bgcolor="#F7F7F7">DATA</td>
								</tr>
							<?php
								$DescricaoOrgao = $linhas['DescOrgao'];
							} 
							$programaSelecao =  $GLOBALS["DNS_SISTEMA"]."/compras/ConsAcompSolicitacaoCompra.php";							$Url = $programaSelecao."?SeqSolicitacao=".$linhas['SeqSolicitacao']."&programa=".$programa;
							$strSolicitacaoCodigo = getNumeroSolicitacaoCompra($db, $linhas['SeqSolicitacao']);
							?>
							<tr>
								<td valign="top" bgcolor="#F7F7F7" class="textonormal">
									<a href="<?php echo $Url;?>">
										<font color="#000000"><?php echo $strSolicitacaoCodigo;?></font>
									</a>
									<span style="cursor:pointer;margin-left:5px;margin-right:10px;" id="<?php echo $linhas['SeqSolicitacao'];?>" class="detalhar" onclick="">+</span>
								</td>
								<td colspan="2" valign="top" bgcolor="#F7F7F7" class="textonormal"><?php echo $linhas['DescOrgao'];?></td>
								<td valign="top" bgcolor="#F7F7F7" class="textonormal"><?php echo $linhas['DataSolicitacao'];?></td>
							</tr>
							<!-- FIM SOLICITAÇÕES AGRUPADAS -->
				<?php	
							exibeDetalhamento($linhas['SeqSolicitacao']);
						}//Fim do Foreach Grupo
						} 	
				?>
						<tr>
							<td class="textonormal" align="right" colspan="4">
								<input type="button" name="Selecionar" value="Selecionar" class="botao" onClick="javascript:enviar('Selecionar')">
							</td>
						</tr>
				<?php
					} else {
				?>
						<tr>
							<td align="left" colspan="4" class="textonormal">Pesquisa sem Ocorrências.</td>
						</tr>
				<?php
					}//Fim do if QtdRegistros
				}//Fim do if boolean pesquisar
				
			
			?>
			</table>
		</td>
	</tr>
	<!-- Fim do Corpo -->
</table>
</form>
</body>
<?php $db->disconnect(); ?>
</html>

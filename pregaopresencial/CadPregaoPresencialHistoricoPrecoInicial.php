<?php
# -------------------------------------------------------------------------
# Portal da DGCO teste
# Programa: CadPregaoPresencialHistoricoPrecoInicial.php
# Autor:    Hélio Miranda
# Data:     29/07/2016
# Objetivo: Exibir histórico do Preço Inicial do Pregão Presencial
# -------------------------------------------------------------------------
# Alterado: Lucas Baracho
# Data:		24/10/2018
# Objetivo: Tarefa Redmine 73662
# -------------------------------------------------------------------------

header("Content-Type: text/html; charset=UTF-8",true);


# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/tabelasbasicas/TabUsuarioAlterar.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST"){
		$UsuarioCodigo = $_POST['UsuarioCodigo'];
		$Critica       = $_POST['Critica'];
		
		$_SESSION['Botao']						= $_POST['Botao'];
		$_SESSION['CodSituacaoClassificacao']	= $_POST['CodSituacaoClassificacao'];
		$_SESSION['MotivoSituacao']				= strtoupper ( $_POST['MotivoSituacao']);

		
}else{
		$Critica       							= $_GET['Critica'];
		$Mensagem      							= urldecode($_GET['Mensagem']);
		$Mens          							= $_GET['Mens'];
		$Tipo          							= $_GET['Tipo'];
		$_SESSION['CodFornecedorSelecionado']	= $_GET['CodFornecedorSelecionado'];

}


if(($_POST['MotivoSituacao'] == null or $_POST['MotivoSituacao'] == "") and $_POST['CodSituacaoClassificacao'] <> 1)
{
	$_SESSION['MotivoSituacao'] = $Linha[0];
}

$TamanhoMaximoMotivo = 500;

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "CadPregaoPresencialHistoricoPrecoInicial.php";

?>
<html>
<head>
<title>Portal de Compras - Incluir Fornecedor</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<script language="javascript" type="">
<!--
function checktodos(){
	document.CadPregaoPresencialHistoricoPrecoInicial.Subclasse.value = '';
	document.CadPregaoPresencialHistoricoPrecoInicial.submit();
}
function enviar(valor){
	document.CadPregaoPresencialHistoricoPrecoInicial.Botao.value = valor;
	document.CadPregaoPresencialHistoricoPrecoInicial.submit();
}
function validapesquisa(){
	if( ( document.CadPregaoPresencialHistoricoPrecoInicial.MaterialDescricaoDireta.value != '' ) || ( document.CadPregaoPresencialHistoricoPrecoInicial.SubclasseDescricaoDireta.value != '') ) {
		if(document.CadPregaoPresencialHistoricoPrecoInicial.Grupo){
			document.CadPregaoPresencialHistoricoPrecoInicial.Grupo.value = '';
		}
		if(document.CadPregaoPresencialHistoricoPrecoInicial.Classe){
			document.CadPregaoPresencialHistoricoPrecoInicial.Classe.value = '';
		}
		document.CadPregaoPresencialHistoricoPrecoInicial.Botao.value = 'Validar';
	}
	if(document.CadPregaoPresencialHistoricoPrecoInicial.Subclasse){
		if(document.CadPregaoPresencialHistoricoPrecoInicial.SubclasseDescricaoFamilia.value != "") {
			document.CadPregaoPresencialHistoricoPrecoInicial.Subclasse.value = '';
		}
	}
	document.CadPregaoPresencialHistoricoPrecoInicial.submit();
}
function AbreJanela(url,largura,altura) {
	window.open(url,'paginadetalhe','status=no,scrollbars=yes,left=45,top=150,width='+largura+',height='+altura);
}
function voltar(){
	self.close();
}
function remeter(){
	document.CadPregaoPresencialHistoricoPrecoInicial.submit();
}

//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">

</head>
<body background="../midia/bg.jpg" marginwidth="0" marginheight="0">
<form action="CadPregaoPresencialHistoricoPrecoInicial.php" method="post" name="CadPregaoPresencialHistoricoPrecoInicial">
<table cellpadding="3" border="0" summary="" width="100%">
	<!-- Erro -->
	<tr>
		<td>
			<?php if( $_SESSION['Mens'] != 0 ){ ExibeMens($_SESSION['Mensagem'],$_SESSION['Tipo'],$_SESSION['Virgula']); }
			
			$_SESSION['Mens'] = null;
			$_SESSION['Tipo'] = null;
			$_SESSION['Mensagem'] = null	
			
			?>
		</td>
	</tr>
	<!-- Fim do Erro -->

	<!-- Corpo -->
	<tr>
		<td class="textonormal">
			<table width="100%" border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal"  bgcolor="#FFFFFF" >
        <tr>
          <td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
	        	PREÇO INICIAL - FORNECEDORES
				
				
				
							<?
							
								if($_SESSION['CodLoteSelecionado'] <> null)
								{
									$Processo 				= $_SESSION['Processo'];
									$ProcessoAno 			= $_SESSION['ProcessoAno'];
									$ComissaoCodigo 		= $_SESSION['ComissaoCodigo'];
									$OrgaoLicitanteCodigo 	= $_SESSION['OrgaoLicitanteCodigo'];								
									$NumeroLoteSelecionado 	= $_SESSION['NumeroLoteSelecionado'];
									
									$db     = Conexao();
										
//Fornecedores - Início
									
									if(isset($_SESSION['CodLoteSelecionado']))
									{
										$PregaoCod 			= $_SESSION['PregaoCod'];
										$CodLoteSelecionado = $_SESSION['CodLoteSelecionado'];
										
																				//Verificando se existe licitações ligadas a o processo , para ver qual programa devo chamar
										$sqlMinMax = "SELECT		MIN(pi.vpregpvali), MAX(pi.vpregpvali)
															FROM 		sfpc.tbpregaopresencialfornecedor fn,
																		sfpc.tbpregaopresencialclassificacao cl,
																		sfpc.tbpregaopresencialsituacaofornecedor sf,
																		sfpc.tbpregaopresenciallote lt,
																		sfpc.tbpregaopresencialprecoinicial pi
															WHERE		lt.cpregtsequ  = $CodLoteSelecionado
																AND 	sf.cpresfsequ  = 1
																AND 	fn.cpregfsequ  = cl.cpregfsequ
																AND		lt.cpregtsequ  = cl.cpregtsequ
																AND 	sf.cpresfsequ  = cl.cpresfsequ
																AND 	cl.cpregfsequ  = pi.cpregfsequ
																AND		cl.cpregtsequ  = pi.cpregtsequ
																AND		pi.vpregpvali > 0"; 
																
										$resultMinMax = $db->query($sqlMinMax);	
										$LinhaMinMax = $resultMinMax->fetchRow();
										
										if($_SESSION['PregaoTipo'] == 'N')
										{
											$tipoOrdenacao = "ASC";
										}
										else
										{
											$tipoOrdenacao = "DESC";
										}										
										
										//Verificando se existe licitações ligadas a o processo , para ver qual programa devo chamar
										$sqlFornecedores = "SELECT		fn.apregfccgc, fn.apregfccpf, fn.npregfrazs, fn.npregfnomr, fn.apregfnurg, 
																		fn.epregfsitu, fn.cpregfsequ, npregforgu, sf.epresfnome, sf.cpresfsequ, lt.cpregtsequ,
																		pi.vpregpvali, pi.cpregpsequ, fn.npregfnomr, fn.fpregfmepp, pi.fpregpalan
															FROM 		sfpc.tbpregaopresencialfornecedor fn,
																		sfpc.tbpregaopresencialclassificacao cl,
																		sfpc.tbpregaopresencialsituacaofornecedor sf,
																		sfpc.tbpregaopresenciallote lt,
																		sfpc.tbpregaopresencialprecoinicial pi
															WHERE		lt.cpregtsequ  = $CodLoteSelecionado
																AND 	fn.cpregfsequ  = cl.cpregfsequ
																AND		lt.cpregtsequ  = cl.cpregtsequ
																AND 	sf.cpresfsequ  = cl.cpresfsequ
																AND 	cl.cpregfsequ  = pi.cpregfsequ
																AND		cl.cpregtsequ  = pi.cpregtsequ																
															ORDER BY	CASE WHEN(sf.cpresfsequ = 1) THEN  0
																			 WHEN(sf.cpresfsequ <> 1) THEN  1
																			 ELSE 2
																		END,
																		CASE WHEN(pi.vpregpvali > 0) THEN  0
																			 WHEN(pi.vpregpvali <= 0) THEN  1
																			 ELSE 2
																		END,
																		pi.vpregpvali $tipoOrdenacao, pi.cpregpoemp ASC, fn.npregfrazs ASC,
																		fn.npregfnomr ASC"; 
											
											
										$resultFornecedores = $db->query($sqlFornecedores);

										if( PEAR::isError($resultFornecedores) ){
											ExibeErroBD("$ErroPrograma\nLinhaPrecoInicial: ".__LINE__."\nSql: $sqlSolicitacoes");
										}
										
										$ValorReferencia = 0;
										
										$LinhaPrecoInicial = $resultFornecedores->fetchRow();
										
										$QuantidadeFornecedores = 0;
										
										$QuantidadeFornecedores = $resultFornecedores->numRows();	
										
										if($_SESSION['PregaoTipo'] == 'N')
										{
											$ValorReferencia = $LinhaMinMax[0];
										}
										else
										{
											$ValorReferencia = $LinhaMinMax[1];
										}
										
									}
//Fornecedores - Fim									
									$db->disconnect();
								}								
							?>				
          </td>
        </tr>
        <tr>
          <td class="textonormal">
             <p align="justify">
             Histórico de Preços Iniciais dos Fornecedores.
             </p>
          </td>
        </tr>
        <tr>
          <td>
            <table border="0" summary="" width="100%">
              <tr>
                <td class="textonormal" bgcolor="#FFFFFF">
					<table border="0" width="100%" summary="">
					  <tr>
							<td align="left" bgcolor="#DCEDF7" class="textonormal"  style="font-weight: bold;">Nº Lote: </td>
							<td align="left" class="textonormal">
							  <label><?php echo $_SESSION['NumeroLoteSelecionado']; ?></label>
							</td>							
					  </tr>
					  
					  <tr>
							<td align="left" bgcolor="#DCEDF7" class="textonormal"  style="font-weight: bold;">Descrição Lote: </td>
							<td align="left" class="textonormal"  >
							  <label><?php echo $_SESSION['DescricaoLoteSelecionado']; ?></label>
							</td>							
					  </tr>	

					  <tr>
							<td align="left" bgcolor="#DCEDF7" class="textonormal"  style="font-weight: bold;">Tipo de Classificação: </td>
							<td align="left" class="textonormal" >
							  <label><?php echo ($_SESSION['PregaoTipo'] == 'N' ? 'MENOR PREÇO' : 'MAIOR OFERTA'); ?></label>
							</td>							
					  </tr>						  
					  
					  <tr >
						<td colspan="2">
						
										<table cellpadding="0" cellspacing="0" border="0" bordercolor="#75ADE6" width="100%" summary="">
											<tr bgcolor="#bfdaf2">
												<td >
												
												
												
													<table
														id="scc_material"
														summary=""
														bgcolor="#bfdaf2"
														border="1"
														bordercolor="#75ADE6"
														width="100%"
													>
														<tbody>
															<tr>
																<td
																	colspan="17"
																	class="titulo3 itens_material"
																	align="center"
																	bgcolor="#75ADE6"
																	valign="middle"
																>FORNECEDORES CREDENCIADOS - PREÇOS INICIAIS</td>
															</tr>
															
															
															
															<!-- Headers ITENS DA SOLICITAÇÃO DE MATERIAL  -->
												<tr class="head_principal">

													<?php // <!--  Coluna 1 = ORD--> ?>
													<td
																	class="textoabason"
																	align="center"
																	bgcolor="#DCEDF7"
																	width="6%"
																><br /> ORD </td>
													
													<?php // <!--  Coluna 2 = CNPJ/CPF--> ?>
													<td
																	class="textoabason"
																	align="center"
																	bgcolor="#DCEDF7"
																	width="15%"
																><img
																	src="../midia/linha.gif"
																	alt=""
																	border="0"
																	height="1px"
																/> <br /> CNPJ/CPF </td>													
													
													<?php // <!--  Coluna 3 = RAZÃO SOCIAL/NOME--> ?>
													<td
																	class="textoabason"
																	align="center"
																	bgcolor="#DCEDF7"
																	width="34%"
																><img
																	src="../midia/linha.gif"
																	alt=""
																	border="0"
																	height="1px"
																/> <br /> RAZÃO SOCIAL/NOME </td>
																
													<?php // <!--  Coluna 4 = TIPO -> ?>
													<td
																	class="textoabason"
																	align="center"
																	bgcolor="#DCEDF7"
																	width="5%"
																><img
																	src="../midia/linha.gif"
																	alt=""
																	border="0"
																	height="1px"
																/> <br /> TIPO </td>																
																
																
													<?php // <!--  Coluna 3 = REPRESENTADO--> ?>
													<td
																	class="textoabason"
																	align="center"
																	bgcolor="#DCEDF7"
																	width="12%"
																><img
																	src="../midia/linha.gif"
																	alt=""
																	border="0"
																	height="1px"
																/> <br /> REPRESENTADO </td>																
																
															
													<?php // <!--  Coluna 4 = REPRESENTANTE--> ?>
													<td
																	class="textoabason"
																	align="center"
																	bgcolor="#DCEDF7"
																	width="8%"
																><img
																	src="../midia/linha.gif"
																	alt=""
																	border="0"
																	height="1px"
																/> <br /> (%) </td>
																
													<?php // <!--  Coluna 5 = R.G.--> ?>
													<td
																	class="textoabason"
																	align="center"
																	bgcolor="#DCEDF7"
																	width="12%"
																	style="cursor: help;"
																	title = "<?php echo ($_SESSION['PregaoTipo'] == 'N' ? 'R$' : '%'); ?>"
																><img
																	src="../midia/linha.gif"
																	alt=""
																	border="0"
																	height="1px"
																/> <br /> <?php echo ($_SESSION['PregaoTipo'] == 'N' ? 'PREÇO INICIAL' : 'OFERTA INICIAL'); ?>  </td>																
															
													<?php // <!--  Coluna 6 = SITUAÇÃO--> ?>
													<td
																	class="textoabason"
																	align="center"
																	bgcolor="#DCEDF7"
																	width="25%"
																><img
																	src="../midia/linha.gif"
																	alt=""
																	border="0"
																	height="1px"
																/> <br /> SITUAÇÃO </td>
																
													<td
																	class="textoabason"
																	align="center"
																	bgcolor="#DCEDF7"
																	width="5%"
																><img
																	src="../midia/linha.gif"
																	alt=""
																	border="0"
																	height="1px"
																/> <br /> APTO </td>																

											<?php

											// Membros do POST-----------------------------------
											
											$UltimoPreco = 0;
											$ContadorPrecosParticipantes = 0;
											
											for ($itr = 0; $itr < $QuantidadeFornecedores; ++ $itr) {
												
											// Membros do POST-----------------------------------
												
												//Início: Tipo de Empresa
												$TipoEmpresaOrigem	= (($LinhaPrecoInicial[14] == 0 or $LinhaPrecoInicial[14] == '' or $LinhaPrecoInicial[14] == null) ? 0 : $LinhaPrecoInicial[14]); 
												
												switch($TipoEmpresaOrigem)
												{
													case 0:
													$TipoEmpresa 		= 'OE';
													$DescTipoEmpresa 	= 'Outras Empresas';
													break;
													case 1:
													$TipoEmpresa 		= 'ME';
													$DescTipoEmpresa 	= 'Micro Empresa';
													break;
													case 2:
													$TipoEmpresa 		= 'EPP';
													$DescTipoEmpresa 	= 'Empresa de Pequeno Porte';
													break;
													case 3:
													$TipoEmpresa 		= 'MEI';
													$DescTipoEmpresa 	= 'Micro Empreendedor Individual';
													break;
												}	

												//Fim: Tipo de Empresa													
											?>
											
											<!-- Dados MEMBRO DE COMISSÃO  -->
															<tr>
																<!--  Coluna 1 = Codido-->
																<td
																	class="textonormal"
																	align="center"
																	style="text-align: center"
																>
																<?= ($itr + 1)?>
														</td>
														
																<!--  Coluna 2  = CPF/CNPJ -->
																<td class="textonormal" align="center">																
																	
																	<?= ($LinhaPrecoInicial[1] == "" 
																	?
																	(substr($LinhaPrecoInicial[0], 0, 2).'.'.substr($LinhaPrecoInicial[0], 2, 3).'.'.substr($LinhaPrecoInicial[0], 5, 3).'/'.substr($LinhaPrecoInicial[0], 8, 4).'-'.substr($LinhaPrecoInicial[0], 12, 2)) 
																	: 
																	(substr($LinhaPrecoInicial[1], 0, 3).'.'.substr($LinhaPrecoInicial[1], 3, 3).'.'.substr($LinhaPrecoInicial[1], 6, 3).'-'.substr($LinhaPrecoInicial[1], 9, 2)));?>																	
																	
																</td>
																
																<!--  Coluna 3  = Razão Social -->
																<td align="center" class="textonormal">																	
																	
																	<?= $LinhaPrecoInicial[2] ?>																	
																	
																</td>
																
																<!--  Coluna 3  = Tipo de Empresa -->
																<td class="textonormal" align="center" title="<?=$DescTipoEmpresa?>" style="cursor: help">																	
																	
																	<?= $TipoEmpresa?>																	
																	
																</td>																	
																
																<!--  Coluna 3  = REPRESENTADO -->
																<td align="center" class="textonormal"
																style="color: 
																<?
																	if($LinhaPrecoInicial[13] == '')
																	{
																		echo "red";
																	}
																	else
																	{
																		echo "blue";
																	}
																?>
																">																	
																	
																	<?= ($LinhaPrecoInicial[13] == '' ? "NÃO" : "SIM") ?>																	
																	
																</td>																

																<!--  Coluna 4  = % -->
																<td align="center" class="textonormal" style="color: 
																<?
																	if($LinhaPrecoInicial[11] == $ValorReferencia)
																	{
																		echo "blue;font-weight: bold";
																	}
																	else if(($LinhaPrecoInicial[11] > 0) and ($LinhaPrecoInicial[11] < $ValorReferencia))
																	{
																		echo "red;";
																	}
																	else
																	{
																		echo "black;";
																	}
																?>">																	
																	<?
																	
																		if($LinhaPrecoInicial[11] > 0)
																		{
																			if($ValorReferencia > 0)
																			{
																				if($_SESSION['PregaoTipo'] == 'N')
																				{
																					$Percentual = (($LinhaPrecoInicial[11] - $ValorReferencia) / $ValorReferencia) * 100;
																					
																					$Percentual = number_format($Percentual, 3, ',', '');
																				}
																				else
																				{
																					$Percentual = (($ValorReferencia - $LinhaPrecoInicial[11]) / $ValorReferencia) * 100;
																				}
																			}																			
																		}
																	?>
																	<?= ($LinhaPrecoInicial[11] > 0 and $ValorReferencia > 0)  ? $Percentual."%" : "-" ?>																	
																	
																</td>

																<!--  Coluna 5  = Preço inicial -->
																<td class="textonormal" style="text-align: center">																
																	<?= $LinhaPrecoInicial[11] > 0 ?  number_format($LinhaPrecoInicial[11], 4, ',', '.')  : "NÃO COTOU" ?>	
																</td>															
																
																
																<!--  Coluna 6 = Situação-->
																<td
																	class="textonormal"
																	style="text-align: center !important; color: <?
																	
																		if($LinhaPrecoInicial[9] == 1)
																		{
																			echo "blue";
																		}
																		else
																		{
																			echo "red";
																		}																	
																	
																	?>;"
																>
																	<?= $LinhaPrecoInicial[8] ?>
																</td>
																
																
																<!--  Coluna 7 = Apto-->																																

																<td
																	class="textonormal"
																	style="text-align: center !important; color: <?
																	
																		if($LinhaPrecoInicial[15] == 1)
																		{
																			echo "blue";
																		}
																		else
																		{
																			echo "red"; 
																		}																	
																	
																		$Apto = ($LinhaPrecoInicial[15] == 1 ? 'SIM' : 'NÃO')
																	
																	?>;">
																	<?= ($LinhaPrecoInicial[11] > 0 or $LinhaPrecoInicial[9] < 1) ? $Apto : " NÃO "  ?>
																	
																	
																</td>																

											<?php
												$LinhaPrecoInicial = $resultFornecedores->fetchRow();
											}
											?>																
																


											<?php

											if ($QuantidadeFornecedores <= 0) {
												?>
											<tr>
																<td
																	class="textonormal itens_material"
																	colspan="8"
																	style="color: red"
																>Nenhum Fornecedor inscrito</td>
															</tr>
															<!-- FIM Dados ITENS DA SOLICITAÇÃO DE MATERIAL   -->

											<?php

											}
											?>

											<?php

											if ($QuantidadeFornecedores > 0) {
												?>												
											
													<tr>
																<td
																	colspan="8"
																	class="titulo3 itens_material menosum"
																	width="95%"
																>TOTAL DE FORNECEDORES:</td>
																
																<td
																	class="textonormal"
																	align="center"
																	width="5%"
																>
																	<div id="MaterialTotal" style="font-weight: bold;"><?= $QuantidadeFornecedores ?></div>
																</td>
															</tr>
															
											<?php

											}
											?>																
															
														</tbody>
													</table>						
						
						</td>				  
					  </tr>
					</table>				
                </td>
              </tr>
            </table>
          </td>
        </tr>
        <tr>
 	        <td class="textonormal" align="right">
			<input type="button" value="Voltar" class="botao" onclick="javascript:voltar();">
			<input type="hidden" name="Botao" value="">			

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
document.Usuario.UsuarioCodigo.focus();
//-->
</script>

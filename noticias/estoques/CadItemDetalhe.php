<?php
# ------------------------------------------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadItemDetalhe.php
# Autor:    Roberta Costa
# Data:     09/06/2005
# Objetivo: Programa de Detalhamento de Itens da Requisição de Material
# Observação: $TipoGrupo, se não for informado, é por padrão "M" (material). 
#             Se a ferramenta trabalha que chama esta também com serviços, lembre-se de enviar o valor correto de "TipoGrupo".
# ------------------------------------------------------------------------------------------------------------
# Alterado: Carlos Abreu
# Data:     14/06/2006
# ------------------------------------------------------------------------------------------------------------
# Alterado: Ariston Cordeiro
# Data:     25/08/2008
# Objetivo: Se o material for substituido por outro, mostra um link para o substituto
# 			Agora lista todas as solicitações de compra que o material está relacionado
# ------------------------------------------------------------------------------------------------------------
# Alterado: Rodrigo Melo
# Data:     21/09/2009
# Objetivo: Alteração para inserir o cadastro de serviços
# ------------------------------------------------------------------------------------------------------------
# Alterado: Ariston Cordeiro
# Data:     26/05/2010
# Objetivo: Alteração para que o tipo de grupo material ("M") seja o padrão, para permitir
#			compatibilidade com ferramentas antigas que só trabalham com material (e não
#			infomrmam o tipo do grupo).
# ------------------------------------------------------------------------------------------------------------
# Alterado: Luiz Alves
# Data:     01/07/2011
# Objetivo: Demanda Redmine: #427, #428
#			Alteração para os almoxarifados, agora somente os gestores de almoxarifados são visualizados.
# ------------------------------------------------------------------------------------------------------------
# Alterado: Heraldo
# Data:     30/05/2012
# Objetivo: Alterar Valor da TRP
# ------------------------------------------------------------------------------------------------------------
# Alterado: Pitang Agile TI
# Data:     06/08/2015
# Objetivo: CR Redmine 73653 - Materiais > TRP - Diversas funcionalidades
# Versão:   v1.23.0-6-ga19f938
# ------------------------------------------------------------------------------------------------------------
# Alterado: Lucas Baracho
# Data:		09/07/2018
# Objetivo: Tarefa Redmine 165579
# ------------------------------------------------------------------------------------------------------------
# Alterado: Lucas Baracho
# Data:		11/07/2018
# Objetivo: Tarefa Redmine 196914
# ------------------------------------------------------------------------------------------------------------
# Alterado: Lucas Baracho
# Data:		26/07/2018
# Objetivo: Tarefa Redmine 77803
# ------------------------------------------------------------------------------------------------------------
# Alterado: Lucas Baracho
# Data:		26/07/2018
# Objetivo: Tarefa Redmine 159717
# ------------------------------------------------------------------------------------------------------------
# Alterado: Lucas Baracho
# Data:		27/07/2018
# Objetivo: Tarefa Redmine 199967
# ------------------------------------------------------------------------------------------------------------
# Alterado: Lucas Baracho
# Data:     27/11/2018
# Objetivo: Tarefa Redmine 207348
# ------------------------------------------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";
include "../compras/funcoesCompras.php";

# Executa o controle de segurança	#
session_start();
Seguranca();

# Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD']	== "POST") {
	$Botao    			= $_POST['Botao'];
	$TipoMaterial 	    = $_POST['TipoMaterial'];
	$Grupo   			= $_POST['Grupo'];
	$GrupoDescricao     = strtoupper2(trim($_POST['GrupoDescricao']));
	$Classe    			= $_POST['Classe'];
	$ClasseDescricao    = strtoupper2(trim($_POST['ClasseDescricao']));
	$Subclasse    	    = $_POST['Subclasse'];
	$SubclasseDescricao = strtoupper2(trim($_POST['SubclasseDescricao']));
	$Material  			= $_POST['Material']; // Material ou Serviço
	$MaterialDescricao  = strtoupper2(trim($_POST['MaterialDescricao']));
	$Pesquisa           = $_POST['Pesquisa'];
	$Palavra            = $_POST['Palavra'];
	$Resultado          = $_POST['Resultado'];
	$ProgramaOrigem     = $_POST['ProgramaOrigem'];
} else {
	$ProgramaOrigem	= $_GET['ProgramaOrigem'];
	$Material       = $_GET['Material'];
	$TipoGrupo      = $_GET['TipoGrupo']; // M ou NULL para Material e S para serviço
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

if ($Botao == "Voltar") {
	$Enviar = "S";
}

if ($TipoGrupo == "S") {
	$Descricao = "Serviço";
} else {
	$TipoGrupo = "M"; // Tipo do grupo é material por padrão
	$Descricao = "Material";
}
?>
<html>
<head>
<title>Portal de Compras - Detalhes do <?php echo $Descricao; ?></title>
<script language="javascript" type="">
	function enviar(valor){
		document.CadItemDetalhe.Botao.value = valor;
		document.CadItemDetalhe.submit();
	}
	//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
</head>
<body background="../midia/bg.jpg" marginwidth="0" marginheight="0">
	<form action="CadItemDetalhe.php" method="post" name="CadItemDetalhe">
		<table cellpadding="0" border="0" summary="">
			<!-- Erro -->
			<tr>
		  		<td align="left" colspan="2">
					<?php if ($Mens != 0) { ExibeMens($Mensagem,$Tipo,1); } ?>
		 		</td>
			</tr>
			<!-- Fim do Erro -->
			<!-- Corpo -->
			<tr>
				<td class="textonormal">
					<table border="0" cellspacing="0" cellpadding="3" summary="" width="100%">
						<tr>
		      				<td class="textonormal">
		        				<table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" width="100%" class="textonormal" bgcolor="#FFFFFF" summary="">
             						<input type="hidden" name="ProgramaOrigem" value="<?php echo $ProgramaOrigem; ?>">
		          					<tr>
		            					<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3" colspan="5">
			    							DETALHAMENTO DO <?php echo strtoupper2($Descricao); ?>
			          					</td>
			        				</tr>
		  	      					<tr>
		    	      					<td class="textonormal" colspan="5">
											<p align="justify">
												Para fechar a janela clique no no botão "Voltar".
												&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
												&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
												&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
												&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
												&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
												&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
												&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
												&nbsp;&nbsp;
		          	   						</p>
		          						</td>
			        				</tr>

									<?php
									# Pega os dados do Material/Serviço de acordo com o código #
									$db = Conexao();

									if ($TipoGrupo == "M") {
										$sql = "SELECT	DISTINCT E.CGRUMSCODI, E.EGRUMSDESC, E.FGRUMSTIPO, D.CCLAMSCODI, D.ECLAMSDESC,
											 			A.EMATEPDESC, A.EMATEPOBSE, A.CMATEPSITU, A.EMATEPCOMP, B.EUNIDMDESC,  
											 			B.EUNIDMSIGL,  C.CSUBCLCODI,  C.CSUBCLSEQU,  C.ESUBCLDESC, E.FGRUMSTIPM,  
														CM.TCORMAULAT, A2.EMATEPDESC, A2.CMATEPSEQU,  A.FMATEPNTRP, A.FMATEPGENE, A.FMATEPSUST,
														F.CGRUSESUBE, F.NGRUSENOMS, MAX(F.AGRUSEANOI), F.CGRUSEELE1, F.CGRUSEELE2, F.CGRUSEELE3, F.CGRUSEELE4
												FROM	SFPC.TBMATERIALPORTAL A
														LEFT OUTER JOIN SFPC.TBCORRECAOMATERIAL CM ON CM.CCORMAMATE =  A.CMATEPSEQU
														LEFT OUTER JOIN	SFPC.TBMATERIALPORTAL A2 ON A2.CMATEPSEQU = CM.CCORMAMAT1,
														SFPC.TBUNIDADEDEMEDIDA B,
														SFPC.TBSUBCLASSEMATERIAL C,
														SFPC.TBCLASSEMATERIALSERVICO D,
														SFPC.TBGRUPOMATERIALSERVICO E,
														SFPC.TBGRUPOSUBELEMENTODESPESA F
												WHERE	A.CUNIDMCODI = B.CUNIDMCODI
														AND A.CSUBCLSEQU = C.CSUBCLSEQU
														AND C.CGRUMSCODI = D.CGRUMSCODI
														AND C.CCLAMSCODI = D.CCLAMSCODI
														AND D.CGRUMSCODI = E.CGRUMSCODI
														AND A.CMATEPSEQU = $Material
														AND E.FGRUMSTIPO = '$TipoGrupo'
														AND F.CGRUMSCODI = C.CGRUMSCODI
														AND (CM.TCORMAULAT IS NULL OR CM.TCORMAULAT = (SELECT MAX(TCORMAULAT) FROM SFPC.TBCORRECAOMATERIAL WHERE CCORMAMATE = $Material))
												GROUP BY 	E.CGRUMSCODI,  E.EGRUMSDESC, E.FGRUMSTIPO, D.CCLAMSCODI, D.ECLAMSDESC,
															A.EMATEPDESC, A.EMATEPOBSE, A.CMATEPSITU, A.EMATEPCOMP, B.EUNIDMDESC,  
															B.EUNIDMSIGL,  C.CSUBCLCODI,  C.CSUBCLSEQU,  C.ESUBCLDESC, E.FGRUMSTIPM,  
															CM.TCORMAULAT, A2.EMATEPDESC, A2.CMATEPSEQU,  A.FMATEPNTRP, A.FMATEPGENE, A.FMATEPSUST,
															F.CGRUSESUBE, F.NGRUSENOMS, F.CGRUSEELE1, F.CGRUSEELE2, F.CGRUSEELE3, F.CGRUSEELE4
												ORDER BY MAX(F.AGRUSEANOI) DESC";
									} else {
												$sql = "SELECT	DISTINCT C.CGRUMSCODI, C.EGRUMSDESC, C.FGRUMSTIPO, B.CCLAMSCODI, B.ECLAMSDESC, A.ESERVPDESC,
														A.ESERVPOBSE, A.CSERVPSITU, D.CGRUSESUBE, D.NGRUSENOMS, MAX(D.AGRUSEANOI),
														D.CGRUSEELE1, D.CGRUSEELE2, D.CGRUSEELE3, D.CGRUSEELE4
												FROM	SFPC.TBSERVICOPORTAL A,
														SFPC.TBCLASSEMATERIALSERVICO B,
														SFPC.TBGRUPOMATERIALSERVICO C,
														SFPC.TBGRUPOSUBELEMENTODESPESA D
												WHERE	A.CGRUMSCODI = B.CGRUMSCODI
														AND A.CCLAMSCODI = B.CCLAMSCODI
														AND B.CGRUMSCODI = C.CGRUMSCODI
														AND A.CSERVPSEQU = $Material
														AND C.FGRUMSTIPO = '$TipoGrupo'
														AND A.CGRUMSCODI = D.CGRUMSCODI
												GROUP BY	C.CGRUMSCODI, C.EGRUMSDESC, C.FGRUMSTIPO, B.CCLAMSCODI, B.ECLAMSDESC, A.ESERVPDESC, A.ESERVPOBSE, A.CSERVPSITU, D.CGRUSESUBE, D.NGRUSENOMS, D.CGRUSEELE1, D.CGRUSEELE2, D.CGRUSEELE3, D.CGRUSEELE4
												ORDER BY MAX(D.AGRUSEANOI) DESC";
									}

									$res  = $db->query($sql);
					
									if (db::isError($res)) {
										ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
									} else {
										$Linha = $res->fetchRow();
										$CodGrupo        = $Linha[0];
										$DescGrupo       = $Linha[1];
										$TipoGrupoBanco	 = $Linha[2];
										$CodClasse       = $Linha[3];
										$DescClasse      = $Linha[4];
										$DescMaterial    = $Linha[5];
										$Observacao      = $Linha[6];
										$Situacao        = $Linha[7];
										$CodSubElemento  = $Linha[8];
										$DescSubElemento = $Linha[9];
										$ElementoDesp1   = $Linha[11];
										$ElementoDesp2   = $Linha[12];
										$ElementoDesp3   = $Linha[13];
										$ElementoDesp4   = $Linha[14];

										if ($TipoGrupo == "M") {
											$DescCompleta     = $Linha[8];
											$DescUnidade      = $Linha[9];
											$Sigla            = $Linha[10];
											$CodSubclasse     = $Linha[11];
											$SequSubclasse    = $Linha[12];
											$DescSubclasse    = $Linha[13];
											$TipoMaterial     = $Linha[14];
											$DataSubstituicao = $Linha[15];
											$SubstitutoDesc   = $Linha[16];
											$SubstitutoCod    = $Linha[17];
											$naoGravaTRP      = $Linha[18];
											$materialGenerico = $Linha[19];
											$itemsustentavel  = $Linha[20];
											$CodSubElemento   = $Linha[21];
											$DescSubElemento  = $Linha[22];
											$ElementoDesp1    = $Linha[24];
											$ElementoDesp2    = $Linha[25];
											$ElementoDesp3    = $Linha[26];
											$ElementoDesp4    = $Linha[27];
										}
									}					

									// Calcular valores da TRP
									//$trpCompraDireta = calcularValorTrp($db, TIPO_COMPRA_DIRETA, $Material);
								/*		
									if (empty($trpCompraDireta)) {
										$trpCompraDireta="nenhum";
									} else {
										$trpCompraDireta = number_format($trpCompraDireta, 2, ',', '.') ;
									}

									$trpOutras=calcularValorTrp($db, TIPO_COMPRA_LICITACAO, $Material);

									if (empty($trpOutras)) {
										$trpOutras="nenhum";
									} else {
										$trpOutras = number_format($trpOutras, 2, ',', '.') ;
									}
								*/

									$db->disconnect();
									?>
				        			
									<tr>
										<td colspan="5">
											<table border="0" cellpadding="0" cellspacing="0" bordercolor="#75ADE6" width="100%" summary="">
												<tr>
													<td colspan="2">
								     	    				<table class="textonormal" border="0" width="100%" summary="">
															<tr>
									        					<td class="textonormal" bgcolor="#DCEDF7" height="20" width="20%">Tipo do Grupo</td>
									            				<td class="textonormal">
									              					<?php if ($TipoGrupoBanco == "M") { echo "MATERIAL"; } else { echo "SERVIÇO"; } ?>
									             					</td>
									           				</tr>
									           				
															<?php if ($TipoGrupo == "M") { ?>
									            				<tr>
									              					<td class="textonormal" bgcolor="#DCEDF7" height="20" width="20%">Tipo de Material</td>
									              					<td class="textonormal">
									              						<?php if ($TipoMaterial == "C") { echo "CONSUMO"; } else { echo "PERMANENTE"; } ?>
									             						</td>
									           					</tr>
								           					<?php } ?>

								           					<tr>
								             						<td class="textonormal" bgcolor="#DCEDF7" height="20">Código do Grupo</td>
								             						<td class="textonormal"><?php echo $CodGrupo; ?></td>
								           					</tr>
								           					<tr>
								             						<td class="textonormal" bgcolor="#DCEDF7" height="20">Grupo</td>
								             						<td class="textonormal"><?php echo $DescGrupo; ?></td>
								           					</tr>
									        				<tr>
									            				<td class="textonormal" bgcolor="#DCEDF7" height="20">Código da Classe</td>
									 	        					<td class="textonormal"><?php echo $CodClasse; ?></td>
									        				</tr>
									        				<tr>
									            				<td class="textonormal" bgcolor="#DCEDF7" height="20">Classe</td>
									 	        					<td class="textonormal"><?php echo $DescClasse; ?></td>
									        				</tr>

															<?php if ($TipoGrupo == "M") { ?>
									        					<tr>
									            					<td class="textonormal" bgcolor="#DCEDF7" height="20">Código Reduzido da Subclasse</td>
									 	        						<td class="textonormal"><?php echo $SequSubclasse;?></td>
									 	        					</tr>
									        					<tr>
									            					<td class="textonormal" bgcolor="#DCEDF7" height="20">Código da Subclasse</td>
									 	        						<td class="textonormal"><?php echo $CodSubclasse;?></td>
									 	        					</tr>
									        					<tr>
										           					<td class="textonormal" bgcolor="#DCEDF7" height="20">Subclasse</td>
									  	        					<td class="textonormal"><?php echo $DescSubclasse; ?></td>
																</tr>													
									        				<?php } ?>

									       					<tr>
									           					<td class="textonormal" bgcolor="#DCEDF7" height="20">Código Reduzido do <?php echo $Descricao; ?></td>
								  	       						<td class="textonormal"><?php echo $Material;?></td>
								  	       					</tr>

									       					<tr>
									           					<td class="textonormal" bgcolor="#DCEDF7" height="20"><?php echo $Descricao; ?></td>
								  	       						<td class="textonormal"><?php echo $DescMaterial;?></td> <!--Mudar para $DescMaterialServico -->
									       					</tr>

									       					<?php if ($TipoGrupo == "M") { ?>
									        					<tr>
										            				<td class="textonormal" bgcolor="#DCEDF7" height="20">Unidade de Medida</td>
									 	        						<td class="textonormal"><?php echo $DescUnidade;?></td>
										        				</tr>
									        					<tr>
									            					<td class="textonormal" bgcolor="#DCEDF7" height="20">Descrição Completa do Material</td>
									 	        						<td class="textonormal"><?php echo $DescCompleta;?></td>
									        					</tr>
									       					<?php } ?>
									       		
															<tr>
									           					<td class="textonormal" bgcolor="#DCEDF7" height="20">Observação</td>
								  	       						<td class="textonormal"><?php echo $Observacao;?></td>
									       	  				</tr>
									       					<tr>
									           					<td class="textonormal" bgcolor="#DCEDF7" height="20">Situação</td>
								  	       						<td class="textonormal">
								  	       							<?php if ($Situacao == "A") { echo "ATIVO"; } else { echo "INATIVO"; } ?>
								  	       						</td>
									       	  				</tr>
															<tr>
																<td class="textonormal" bgcolor="#DCEDF7" height="20">Preço TRP </td>
																<td class="textonormal">
																	<?php
																	if ($trpCompraDireta == "0,00" ) {
																		$trpCompraDireta = "...";
																	}
																	echo  "Compra Direta:$trpCompraDireta &nbsp;&nbsp Compra Licitação:$trpOutras"
																	?>
																</td>
															</tr>
									       	    			<tr>
																<td class="textonormal" bgcolor="#DCEDF7" height="20">Não grava na TRP </td>
																<td class="textonormal"> <?php echo ($naoGravaTRP == 'S') ? 'Sim' : 'Não'; ?></td>
															</tr>
												
															<?php if ($TipoGrupo == "M") { ?>
																<tr>
										           					<td class="textonormal" bgcolor="#DCEDF7" height="20">Material Genérico</td>
									  	        					<td class="textonormal"><?php echo ($materialGenerico == 'S') ? 'Sim' : 'Não'; ?></td>
																</tr>
															<?php } ?>
												
															<?php if ($TipoGrupo == "M") { ?>
																<tr>
																	<td class="textonormal" bgcolor="#DCEDF7" height="20">Item Sustentável </td>
																	<td class="textonormal"> <?php echo ($itemsustentavel == 'S') ? 'Sim' : 'Não' ; ?> </td>
																</tr>
															<?php } ?>

															<tr>
																<td class="textonormal" bgcolor="#DCEDF7" height="20">Sub-elemento da despesa </td>
																<td class="textonormal"> <?php echo "$ElementoDesp1.$ElementoDesp2.$ElementoDesp3.$ElementoDesp4.$CodSubElemento - $DescSubElemento"  ?> </td>
															</tr>
								         					</table>
													</td>
												</tr>
											</table>
										</td>
									</tr>
		      	    				
									<?php
		      	    				if ($ProgramaOrigem == "ConsMaterialSelecionar" && $TipoGrupo == "M") { //Apenas para Material
										# Mostrar ítem que o substituiu, caso ele tenha sido substituído
										if (($Situacao=="I") && ($DataSubstituicao!="") && ($DataSubstituicao!=0)) {
											?>
											
											<tr>
												<td class="textonormal" bgcolor="#BFDAF2" height="20" colspan="3">
													Este material foi substituído pelo seguinte:
												</td>
											</tr>
											<tr>
												<td class="textonormal" colspan='2' bgcolor="#DCEDF7" height="20">Nome do material</td>
												<td class="textonormal" bgcolor="#DCEDF7" height="20">Cód. Red.</td>
											</tr>
											<tr>
												<td class="textonormal" colspan='2' height="20"><a href="CadItemDetalhe.php?Material=<?=$SubstitutoCod?>&TipoGrupo=<?=$TipoGrupoBanco?>&ProgramaOrigem=CadItemDetalhe"><?=$SubstitutoDesc?></a></td>
												<td class="textonormal" height="20"><?=$SubstitutoCod?></td>
											</tr>
											
											<?php
										}

				      	    			$db = Conexao();
										
										# Pega os dados do Material de acordo com o código #
										$sql = "SELECT	DISTINCT ON (C.EALMPODESC) C.EALMPODESC, C.AALMPOFONE, G.EUSUPORESP, A.VARMATULTC, A.AARMATQTDE
												FROM	SFPC.TBARMAZENAMENTOMATERIAL A,
														SFPC.TBLOCALIZACAOMATERIAL B,
														SFPC.TBALMOXARIFADOPORTAL C,
														SFPC.TBALMOXARIFADOORGAO D,
														SFPC.TBCENTROCUSTOPORTAL E,
														SFPC.TBUSUARIOCENTROCUSTO F,
														SFPC.TBUSUARIOPORTAL G,
														SFPC.TBUSUARIOPERFIL H
												WHERE	A.CMATEPSEQU = $Material
														AND F.FUSUCCTIPO IN ('T','R')
														AND A.CLOCMACODI = B.CLOCMACODI
														AND B.CALMPOCODI = C.CALMPOCODI
														AND C.CALMPOCODI = D.CALMPOCODI
														AND D.CORGLICODI = E.CORGLICODI
														AND E.CCENPOSEQU = F.CCENPOSEQU
														AND F.FUSUCCTIPO = 'T'
														AND F.CGREMPCODI = G.CGREMPCODI
														AND F.CUSUPOCODI = G.CUSUPOCODI
														AND G.CGREMPCODI = H.CGREMPCODI
                                                       	AND G.CUSUPOCODI = H.CUSUPOCODI
	                                                    AND H.CPERFICODI = 13
												ORDER BY C.EALMPODESC ";

										$res  = $db->query($sql);
										
										if (db::isError($res)) {
											ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
										} else {
											if ($res->numRows() > 0) {
												echo "<tr>\n";
									            echo "  <td class=\"textonormal\" bgcolor=\"#BFDAF2\" height=\"20\" colspan=5>O Material está cadastrado no(s) seguinte(s) almoxarifado(s):</td>\n";
									           	echo "</tr>\n";
												echo "<tr>\n";
									            echo "  <td class=\"textonormal\" bgcolor=\"#DCEDF7\" height=\"20\">Almoxarifado</td>\n";
									            echo "  <td class=\"textonormal\" bgcolor=\"#DCEDF7\" height=\"20\">Telefone</td>\n";
									            echo "  <td class=\"textonormal\" bgcolor=\"#DCEDF7\" height=\"20\">Preço Unitário</td>\n";
												echo "  <td class=\"textonormal\" bgcolor=\"#DCEDF7\" height=\"20\">Quantidade em Estoque</td>\n";
									            echo "  <td class=\"textonormal\" bgcolor=\"#DCEDF7\" height=\"20\">Gestores de Almoxarifado</td>\n";
									           	echo "</tr>\n";
												
												$AlmoxarifadoDesc = "";
									           	$Telefone         = "";
												$Preco            = "";
												$qtdeEstoque      = "";
												$gestorAlmox      = "";
												
												while ($Linha = $res->fetchRow()) {
													echo "<tr>\n";
												
													if ($Linha[0] != $AlmoxarifadoDesc) {
														$AlmoxarifadoDesc = $Linha[1];
										              	echo "	<td class=\"textonormal\" height=\"20\">$Linha[0]</td>\n";
										            } else {
										              	echo "	<td class=\"textonormal\" height=\"20\">&nbsp;</td>\n";
													}
												
										            if ($Linha[1] != $Telefone) {
														$Telefone = $Linha[2];
										              	echo "	<td class=\"textonormal\" height=\"20\">$Linha[1]</td>\n";
										            } else {
										              	echo "	<td class=\"textonormal\" height=\"20\">&nbsp;</td>\n";
										            }
													
													if ($Linha[3 != $Preco]) {
														$Preco = $Linha[4];
														echo "	<td class=\"textonormal\" align=\"right\" height=\"20\">" . number_format($Linha[3], '4', ',', '.') . "</td>\n";
													} else {
														echo "	<td class=\"textonormal\" height=\"20\">&nbsp;</td>\n";
													}
													
													if ($Linha[4] != $qtdeEstoque) {
														$qtdeEstoque = $Linha[5];
														echo "	<td class=\"textonormal\" align=\"right\" height=\"20\">" . number_format($Linha[4], '4', ',', '.') . "</td>\n";
													} else {
														echo "	<td class=\"textonormal\" height=\"20\">&nbsp;</td>\n";
													}
														
													if ( $Linha[2] != $gestorAlmox ) {
														$gestorAlmox = $Linha[3];
										              	echo "	<td class=\"textonormal\" height=\"20\">$Linha[2]</td>\n";
										            } else {
										              	echo "	<td class=\"textonormal\" height=\"20\">&nbsp;</td>\n";
													}
													echo "</tr>\n";
												}
											} else {
												echo "<tr>\n";
									            echo "  <td class=\"textonormal\" bgcolor=\"#BFDAF2\" height=\"20\" colspan=\"5\">O Material não está cadastrado em nenhum almoxarifado.</td>\n";
									           	echo "</tr>\n";
											}
										}
										
										if ($_SESSION['_cgrempcodi_'] == 0 or $_SESSION['_fperficorp_'] == "S") { // apenas visível a administradores
											# Pega os dados de todas solicitações de compra vinculados ao material
											$sql = "SELECT	DISTINCT OL.EORGLIDESC, U.AUSUPOFONE, U.EUSUPORESP
													FROM	SFPC.TBSOLICITACAOCOMPRA SC,
															SFPC.TBITEMSOLICITACAOCOMPRA ISC,
															SFPC.TBORGAOLICITANTE OL,
															SFPC.TBUSUARIOPORTAL U
													WHERE	ISC.CMATEPSEQU = '".$Material."'
															AND SC.CSOLCOSEQU = ISC.CSOLCOSEQU
															AND SC.CORGLICODI = OL.CORGLICODI
															AND SC.CUSUPOCODI = U.CUSUPOCODI
													ORDER BY OL.EORGLIDESC, U.EUSUPORESP ";
											
											$res = $db->query($sql);
											
											if (db::isError($res)) {
												ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
												exit(0);
											}
											
											if ($res->numRows() > 0) {
												?>
												
												<tr>
													<td class="textonormal" bgcolor="#BFDAF2" height="20" colspan="5">O Material está cadastrado na(s) seguinte(s) solicitação(ões) de compra:</td>
												</tr>
												<tr>
													<td class="textonormal" bgcolor="#DCEDF7" height="20">Órgão Licitante</td>
													<td class="textonormal" bgcolor="#DCEDF7" height="20">Telefone do Solicitante</td>
													<td class="textonormal" bgcolor="#DCEDF7" height="20">Solicitante</td>
												</tr>
												
												<?
												$orgaoanterior = "";
												while ($Linha = $res->fetchRow()) {
													?>
													
													<tr>
														<td class="textonormal" height="20">
															<?php
															if ($Linha[0] != $orgaoanterior){
																echo $Linha[0];
															} else {
																echo "&nbsp;";
															}
															?>
														</td>
														<td class="textonormal" height="20"><?=$Linha[1]?></td>
														<td class="textonormal" height="20"><?=$Linha[2]?></td>
													</tr>
													
													<?php
													$orgaoanterior = $Linha[0];
												}
											}
										}
										$db->disconnect();
									}
									?>
		          					
									<tr>
			            				<td colspan="5" align="right">
		              						<input type="hidden" name="Enviar" value="<?php echo $Enviar; ?>">
		              						<input type="hidden" name="Resultado" value="<?php echo $Resultado; ?>">
		              						<input type="hidden" name="TipoMaterial" value="<?php echo $TipoMaterial; ?>">
		              						<input type="hidden" name="DescGrupo" value="<?php echo $DescGrupo; ?>">
		              						<input type="hidden" name="DescClasse" value="<?php echo $DescClasse; ?>">
		              						<input type="hidden" name="DescSubclasse" value="<?php echo $DescSubclasse; ?>">
		              						<input type="hidden" name="Material" value="<?php echo $Material; ?>">
		              						<input type="hidden" name="DescMaterial" value="<?php echo $DescMaterial; ?>">
		              						<input type="hidden" name="Observacao" value="<?php echo $Observacao; ?>">
						       				<input type="button" value="Voltar" class="botao" onclick="javascript:self.close();">
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
	<script language="javascript" type="">
		window.focus();
		//-->
	</script>
</body>
</html>	<input type="hidden" name="DescMaterial" value="<?php echo $DescMaterial; ?>">
		              						<input type="hidden" name="Observacao" value="<?php echo $Observacao; ?>">
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
	<script language="javascript" type="">
		window.focus();
		//-->
	</script>
</body>
</html>
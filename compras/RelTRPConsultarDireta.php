<?php
#----------------------------------------------------------------------------
# Portal da DGCO
# Programa: RelTRPConsultar.php
# Objetivo: Programa de Detalhamento da Consulta de TRP (janela popup)
# Autor:    Ariston Cordeiro
# Data:     19/11/2012
# OBS.:     Tabulação 2 espaços
#----------------------------------------------------------------------------


# Acesso ao arquivo de funções #
include "../funcoes.php";
include "../compras/funcoesCompras.php";

# Executa o controle de segurança	#
session_start();
Seguranca();



# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD']	== "POST" ){

}else{
		$ProgramaOrigem	= $_GET['ProgramaOrigem'];
		$Material       = $_GET['Material'];
		//$Solicitacao       = $_GET['Solicitacao'];
}


# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;




$TipoGrupo = "M"; // Tipo do grupo é material por padrão
$Descricao = "Material";

$db   = Conexao();


/** Recupera dados da SCC mais recente com o valor TRP compra direta */
function getSccDeTrp($db, $tipoCompra, $codMaterial){
	assercao(!is_null($db), "Variável do banco de dados Oracle não foi inicializada");
	assercao(!is_null($tipoCompra), "Variável 'tipoCompra' é necessária");
	assercao(!is_null($codMaterial), "Variável 'codMaterial' é necessária");
	assercao($tipoCompra==TIPO_COMPRA_DIRETA, "TRP apenas pode estar diretamente associada com SCCs do tipo COMPRA DIRETA");
	
	$trp = null;
	
	$dataMinimaValidaTrp = prazoValidadeTrp($db, $tipoCompra)->format('Y-m-d');
	$valorTrp = calcularValorTrp($db, $tipoCompra, $codMaterial);
	
	if(!is_null($valorTrp)){
	
		$sql = "
			select csolcosequ, citescsequ
			from sfpc.tbtabelareferencialprecos
			where 
				cmatepsequ = ".$codMaterial."
				and CTRPREULAT >= '".$dataMinimaValidaTrp."'
				and CSOLCOSEQU is not null
				and vtrprevalo = ".$valorTrp."
			order by ctrpreulat desc
		";
		$obj = resultObjetoUnico( executarSQL($db, $sql ), true);
		
		if(!is_null($obj)){
			$trp = array();
			$trp['material'] = $codMaterial;
			$trp['valor'] = $valorTrp;
			$trp['solicitacao'] = $obj->csolcosequ;
			$trp['itemSolicitacao'] = $obj->citescsequ;

			$sql = "
				select scc.tsolcodata, o.eorglidesc
				from 
					sfpc.tbsolicitacaocompra scc, sfpc.tborgaolicitante o 
				where
					scc.corglicodi = o.corglicodi
					and csolcosequ = ".$trp['solicitacao']."

		  ";
			
			$objItem = resultObjetoUnico( executarSQL($db, $sql ));

			$trp['solicitacaoData'] = $objItem->tsolcodata;
			$trp['solicitacaoOrgao'] = $objItem->eorglidesc;
			
			$sql = "
				select aforcrsequ 
				from sfpc.tbitemsolicitacaocompra
				where 
					csolcosequ = ".$trp['solicitacao']."
					and citescsequ = ".$trp['itemSolicitacao']."
		  ";
			$objItem = resultObjetoUnico( executarSQL($db, $sql ));

			$fornecedorSeq = $objItem->aforcrsequ;
			$fornecedor = getFornecedor($db, $fornecedorSeq);
			$trp['fornecedorRazaoSocial'] = $fornecedor['razaoSocial'];
			$trp['fornecedorCpfCnpj'] = $fornecedor['cpfCnpj'];
			$trp['fornecedorTelefoneFax'] = $fornecedor['telefoneFax'];
			$trp['fornecedorEndereço'] = $fornecedor['endereço'];
		}
	}
	return $trp;
}


$trp = getSccDeTrp($db, TIPO_COMPRA_DIRETA, $Material);



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
				<?php if( $Mens != 0 ){ ExibeMens($Mensagem,$Tipo,1);	}?>
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
			    					TABELA REFERENCIAL DE PREÇOS DE MATERIAIS - COMPRA DIRETA
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
									

									if($TipoGrupo == "M"){
										$sql  = "
											SELECT DISTINCT
												E.CGRUMSCODI,  E.EGRUMSDESC, E.FGRUMSTIPO, D.CCLAMSCODI, D.ECLAMSDESC,
												A.EMATEPDESC, A.EMATEPOBSE, A.CMATEPSITU, A.EMATEPCOMP,
												B.EUNIDMDESC,  B.EUNIDMSIGL,  C.CSUBCLCODI,  C.CSUBCLSEQU,  C.ESUBCLDESC,
												E.FGRUMSTIPM,  CM.TCORMAULAT, A2.EMATEPDESC, A2.CMATEPSEQU ,  A.FMATEPNTRP 

											FROM
												SFPC.TBMATERIALPORTAL A
													LEFT OUTER JOIN SFPC.TBCORRECAOMATERIAL CM ON CM.CCORMAMATE =  A.CMATEPSEQU
													LEFT OUTER JOIN	SFPC.TBMATERIALPORTAL A2 ON A2.CMATEPSEQU = CM.CCORMAMAT1,
												SFPC.TBUNIDADEDEMEDIDA B,
												SFPC.TBSUBCLASSEMATERIAL C,
												SFPC.TBCLASSEMATERIALSERVICO D,
												SFPC.TBGRUPOMATERIALSERVICO E
											WHERE
												A.CUNIDMCODI = B.CUNIDMCODI AND A.CSUBCLSEQU = C.CSUBCLSEQU
												AND C.CGRUMSCODI = D.CGRUMSCODI AND C.CCLAMSCODI = D.CCLAMSCODI
												AND D.CGRUMSCODI = E.CGRUMSCODI AND A.CMATEPSEQU = $Material
												AND E.FGRUMSTIPO = '$TipoGrupo'
												AND ( CM.TCORMAULAT IS NULL OR CM.TCORMAULAT = (SELECT MAX(TCORMAULAT) FROM SFPC.TBCORRECAOMATERIAL WHERE CCORMAMATE = $Material) )
										";
									} else {
										$sql  = "
											SELECT DISTINCT
												C.CGRUMSCODI,  C.EGRUMSDESC, C.FGRUMSTIPO, B.CCLAMSCODI, B.ECLAMSDESC,
												A.ESERVPDESC, A.ESERVPOBSE, A.CSERVPSITU

											FROM
												SFPC.TBSERVICOPORTAL A,
												SFPC.TBCLASSEMATERIALSERVICO B,
												SFPC.TBGRUPOMATERIALSERVICO C
											WHERE
												A.CGRUMSCODI = B.CGRUMSCODI AND A.CCLAMSCODI = B.CCLAMSCODI
												AND B.CGRUMSCODI = C.CGRUMSCODI AND A.CSERVPSEQU = $Material
												AND C.FGRUMSTIPO = '$TipoGrupo'
										";
									}
									$res  = $db->query($sql);
								  if( PEAR::isError($res) ){
										  ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
									}else{
										$Linha         = $res->fetchRow();
										$CodGrupo         = $Linha[0];
										$DescGrupo        = $Linha[1];
								      	$TipoGrupoBanco   = $Linha[2];
								      	$CodClasse        = $Linha[3];
										$DescClasse       = $Linha[4];
										$DescMaterial     = $Linha[5];
										$Observacao       = $Linha[6];
										$Situacao         = $Linha[7];

										if($TipoGrupo == "M"){
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
										 	$naoGravaTRP      =  $Linha[18];
										}
									}
									
									//--------------------------
									// Calcular valores da TRP
									//--------------------------
									$trpCompraDireta=calcularValorTrp($db, TIPO_COMPRA_DIRETA, $Material);
									if (empty($trpCompraDireta)) $trpCompraDireta="nenhum";
									else $trpCompraDireta = number_format($trpCompraDireta, 2, ',', '.') ;				

									$trpOutras=calcularValorTrp($db, TIPO_COMPRA_LICITACAO, $Material);	
													
									if (empty($trpOutras)) $trpOutras="nenhum";
									else $trpOutras = number_format($trpOutras, 2, ',', '.') ;								
									
									?>
				        	<tr>
										<td colspan="5">
											<table border="0" cellpadding="0" cellspacing="0" bordercolor="#75ADE6" width="100%" summary="">
												<tr>
													<td colspan="2">
								      	    <table class="textonormal" border="0" width="100%" summary="">
												<tr>
										        	<td class="textonormal" bgcolor="#DCEDF7" height="20" width="20%">Código do CADUM</td>
										            <td class="textonormal">
										              	<?php echo $Material;?>
									              	</td>
									            </tr>
								            	<tr>
								              		<td class="textonormal" bgcolor="#DCEDF7" height="20">Descrição do Material</td>
								              		<td class="textonormal"><?php echo $DescMaterial;?></td>
								            	</tr>
								            	<tr>
								              		<td class="textonormal" bgcolor="#DCEDF7" height="20">Unidade</td>
								              		<td class="textonormal"><?php echo $DescUnidade;?></td>
								            	</tr>
										        <tr>
										            <td class="textonormal" bgcolor="#DCEDF7" height="20">Tipo do preço</td>
									  	        	<td class="textonormal">COMPRA DIRETA</td>
										        </tr>
										        <tr>
										            <td class="textonormal" bgcolor="#DCEDF7" height="20">Preço TRP</td>
									  	        	<td class="textonormal"><?=converte_valor_estoques($trp['valor'])?></td>
										        </tr>
										        <tr>
										            <td class="textonormal" bgcolor="#DCEDF7" height="20">Número da Solicitação</td>
									  	        	<td class="textonormal"><?=getNumeroSolicitacaoCompra($db,$trp['solicitacao'])?></td>
										        </tr>
										        <tr>
										            <td class="textonormal" bgcolor="#DCEDF7" height="20">Data da Solicitação</td>
									  	        	<td class="textonormal"><?=DataBarra($trp['solicitacaoData'])?></td>
										        </tr>
										        <tr>
										            <td class="textonormal" bgcolor="#DCEDF7" height="20">Órgão licitante</td>
									  	        	<td class="textonormal"><?=$trp['solicitacaoOrgao']?></td>
										        </tr>
										        <tr>
										            <td class="textonormal" bgcolor="#DCEDF7" height="20">Fornecedor</td>
									  	        	<td class="textonormal"><?=$trp['fornecedorRazaoSocial']?></td>
										        </tr>
										        <tr>
										            <td class="textonormal" bgcolor="#DCEDF7" height="20">CPF/CNPJ</td>
									  	        	<td class="textonormal"><?=FormataCpfCnpj($trp['fornecedorCpfCnpj'])?></td>
										        </tr>										        
										        <tr>
										            <td class="textonormal" bgcolor="#DCEDF7" height="20">Endereço</td>
									  	        	<td class="textonormal"><?=$trp['fornecedorEndereço']?></td>
										        </tr>
										        <tr>
										            <td class="textonormal" bgcolor="#DCEDF7" height="20">Telefone</td>
									  	        	<td class="textonormal"><?=$trp['fornecedorTelefoneFax']?></td>
										        </tr>


								          	</table>
													</td>
												</tr>
											</table>
										</td>
									</tr>
									
		          		<tr>
			            	<td colspan="5" align="right">
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
</html>
<?
$db->disconnect();
?>
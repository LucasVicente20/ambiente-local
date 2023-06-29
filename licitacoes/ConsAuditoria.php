<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: ConsAuditoria.php
# Autor:    Ariston Cordeiro
# Data:     05/05/11
# Objetivo: Relatório de Auditoria Licitação
#--------------------------------
# Alterado:
# Data:
#---------------------------------
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "GET"){
		$Grupo                = $_GET['Grupo'];
		$Processo             = $_GET['Processo'];
		$Ano 									= $_GET['Ano'];
		$Comissao			        = $_GET['Comissao'];
		$Orgao					      = $_GET['Orgao'];
		$CodDelete						= $_GET['CodDelete']; //Caso a licitação foi excluída, recebe o código com o registro do delete, no log
}

$LicitacaoProcesso = substr($Processo+10000,1);

# Função que recebe os dados de uma tabela de log em String e retorna um array com os dados de cada campo
function explodirValores($strValores){
	global $SimboloConcatenacaoArray;
	$Valores = substr($strValores, 0, -1);//remove o fecha parenteses
	$Valores = substr($Valores, 1);//remove o abre parenteses
	$tamanhoString = strlen($Valores);
	$disable=FALSE;
	for($itf=0;$itf<$tamanhoString;$itf++){
		if($Valores[$itf]=="\""){
			//desabilitar checagem de virgula se está dentro de uma string
			if($disable){
				$disable=FALSE;
			}else{
				$disable=TRUE;
			}
		}else if($Valores[$itf]=="," and !$disable){
			//marcar virgulas que definem separação de valores
			$Valores[$itf]=$SimboloConcatenacaoArray;
		}
	}
	$Valores = explode($SimboloConcatenacaoArray,$Valores);//remove o fecha parenteses
	#Removendo aspas
	$noValores = count($Valores);
	for($itr=0;$itr<$noValores;$itr++){

	}
	return $Valores;
}

if( ($Botao == "Voltar") or (is_null($Processo) or $Processo=="") ){
		header("location: ConsAuditoriaSelecionar.php");
		exit;
}

$db     = Conexao();
if(is_null($CodDelete) or $CodDelete == ""){
	# Resgata as informções da licitação existente #

	$sql    = "SELECT A.EGREMPDESC, B.EMODLIDESC, C.ECOMLIDESC, D.CLICPOCODL, ";
	$sql   .= "       D.ALICPOANOL, D.XLICPOOBJE, E.EORGLIDESC, D.TLICPODHAB, ";
	$sql   .= "       D.VLICPOVALE, D.VLICPOVALH,	D.FLICPOREGP, B.CMODLICODI, ";
	$sql   .= "       D.VLICPOTGES ";
	$sql   .= "  FROM SFPC.TBGRUPOEMPRESA A, SFPC.TBMODALIDADELICITACAO B, ";
	$sql   .= "       SFPC.TBCOMISSAOLICITACAO C, SFPC.TBLICITACAOPORTAL D, SFPC.TBORGAOLICITANTE E ";
	$sql   .= " WHERE A.CGREMPCODI = D.CGREMPCODI AND D.CGREMPCODI = $Grupo ";
	$sql   .= "   AND D.CMODLICODI = B.CMODLICODI AND C.CCOMLICODI = D.CCOMLICODI ";
	$sql   .= "   AND D.CCOMLICODI = $Comissao AND D.CLICPOPROC = $Processo ";
	$sql   .= "   AND D.ALICPOANOP = $Ano AND E.CORGLICODI = D.CORGLICODI ";
	$sql   .= "   AND D.CORGLICODI = $Orgao";
	$result = $db->query($sql);
	if( PEAR::isError($result) ){
			EmailErroSQL("Erro no SQL", __FILE__, __LINE__, "Erro no SQL", $sql, $result);
	}else{
			while( $Linha = $result->fetchRow() ){
					$GrupoDesc             = $Linha[0];
					$ModalidadeDesc        = $Linha[1];
					$ComissaoDesc          = $Linha[2];
					$NLicitacao            = substr($Linha[3] + 10000,1);
					$AnoLicitacao          = $Linha[4];
					$ObjetoLicitacao       = $Linha[5];
					$OrgaoLicitacao        = $Linha[6];
					$LicitacaoDtAbertura   = substr($Linha[7],8,2) ."/". substr($Linha[7],5,2) ."/". substr($Linha[7],0,4);
					$LicitacaoHoraAbertura = substr($Linha[7],11,5);
					$ValorEstimado         = converte_valor($Linha[8]);
					$ValorHomologado       = converte_valor($Linha[9]);
					if( $Linha[10] == "S" ){
							$RegistroPreco = "SIM";
					}else{
							$RegistroPreco = "NÃO";
					}
					$ModalidadeCodigo      = $Linha[11];
					$TotalGeralEstimado    = converte_valor($Linha[12]);
			}
	}
}else{
	# Resgata as informções da licitação excluída #
	$sql    = "
		Select
			ll.xlplogprmt
		from
			sfpc.tblicitacao_log ll
		where
			ll.clicpoproc = $Processo and
			ll.alicpoanop = $Ano and
			ll.cgrempcodi = $Grupo and
			ll.ccomlicodi = $Comissao and
			ll.corglicodi = $Orgao and
			ll.XLPLOGTABL = 'tblicitacaoportal' AND
			ll.XLPLOGCMND = 'DELETE' AND
			ll.clplogcodi = $CodDelete
	";

	$result = $db->query($sql);
	if( PEAR::isError($result) ){
			EmailErroSQL("Erro no SQL", __FILE__, __LINE__, "Erro no SQL", $sql, $result);
	}
	$Linha = $result->fetchRow();

	$Valores = explodirValores($Linha[0]);

	$NLicitacao            = substr($Valores[0] + 10000,1);
	$AnoLicitacao          = $Valores[1];
	$ObjetoLicitacao       = $Valores[9];
	$LicitacaoDtAbertura   = substr($Valores[8],8,2) ."/". substr($Valores[8],5,2) ."/". substr($Valores[8],0,4);
	$LicitacaoHoraAbertura = substr($Valores[8],11,5);
	$ValorEstimado         = converte_valor($Valores[10]);
	$ValorHomologado       = converte_valor($Valores[11]);
	if( $Valores[13] == "S" ){
			$RegistroPreco = "SIM";
	}else{
			$RegistroPreco = "NÃO";
	}
	$ModalidadeCodigo      = $Valores[5];

	$TotalGeralEstimado    = converte_valor($Valores[14]);

	$sql    = "
		Select
			g.egrempdesc, o.eorglidesc, c.ecomlidesc, ml.emodlidesc
		from
			sfpc.tbgrupoempresa g, sfpc.tborgaolicitante o, sfpc.tbmodalidadelicitacao ml, sfpc.tbcomissaolicitacao c
		where
			g.cgrempcodi = $Grupo and
			o.corglicodi = $Orgao and
			c.ccomlicodi = $Comissao and
			ml.cmodlicodi = $ModalidadeCodigo
	";
	$result = $db->query($sql);
	if( PEAR::isError($result) ){
			EmailErroSQL("Erro no SQL", __FILE__, __LINE__, "Erro no SQL", $sql, $result);
	}
	$Linha = $result->fetchRow();

	$GrupoDesc             = $Linha[0];
	$ModalidadeDesc        = $Linha[3];
	$ComissaoDesc          = $Linha[2];
	$OrgaoLicitacao        = $Linha[1];

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
	document.Historico.Botao.value = valor;
	document.Historico.submit();
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="ConsAuditoria.php" method="post" name="Historico">
<br><br><br><br><br>
<table cellpadding="3" border="0">
  <!-- Caminho -->
  <tr>
    <td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
    <td align="left" class="textonormal" colspan="2">
      <font class="titulo2">|</font>
      <a href="../index.php"><font color="#000000">Página Principal</font></a> > Licitações > Licitação > Auditoria
		</td>
  </tr>
  <!-- Fim do Caminho-->

	<!-- Erro -->
	<?php if ( $Mens == 1 ) {?>
	<tr>
	  <td width="100"></td>
	  <td align="left" colspan="2"><?php if ( $Mens == 1 ) { ExibeMens($Mensagem,$Tipo,1); } ?></td>
	</tr>
	<?php } ?>
	<!-- Fim do Erro -->

	<!-- Corpo -->
	<tr>
		<td width="100"></td>
		<td class="textonormal">
      <table  border="0" cellspacing="0" cellpadding="3" bgcolor="#FFFFFF">
        <tr>
	      	<td class="textonormal">
	        	<table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" width="100%">
	          	<tr width="100">
	            	<td align="center" bgcolor="#75ADE6" valign="middle" colspan="4" class="titulo3">
		    					LOG DE AUDITORIA DE LICITAÇÕES
		          	</td>
		        	</tr>
	  	      	<tr>
	    	      	<td class="textonormal" colspan="4">
	      	    		<p align="justify">
	        	    		Para pesquisar outra licitação, clique no botão "Voltar".
	          	   	</p>
	          		</td>
							</tr>
		        	<tr>
								<td class="textonegrito" bgcolor="#DCEDF7" colspan="4">
	    	     				<?php echo "$GrupoDesc <br><br> $ModalidadeDesc <br><br> $ComissaoDesc<br>";?>
	    	     		</td>
	      	   	</tr>
    	      	<tr>
    	      		<td valign="top" bgcolor="#F7F7F7" class="textonegrito" colspan="2">PROCESSO</td>
								<td valign="top" bgcolor="#F7F7F7" class="textonormal" colspan="2"><?=$LicitacaoProcesso?>/<?=$Ano?></td>
							</tr>
							<tr>
								<td valign="top" bgcolor="#F7F7F7" class="textonegrito" colspan="2">LICITAÇÃO</td>
								<td valign="top" bgcolor="#F7F7F7" class="textonormal" colspan="2"><?=$NLicitacao?>/<?=$AnoLicitacao?></td>
							</tr>
							<tr>
								<td valign="top" bgcolor="#F7F7F7" class="textonegrito" colspan="2">OBJETO</td>
								<td valign="top" bgcolor="#F7F7F7" class="textonormal" colspan="2"><?=$ObjetoLicitacao?></td>
							</tr>
		        	<tr>
								<td class="textonegrito" bgcolor="#DCEDF7" colspan="4">
	    	     				LOG DE AUDITORIA DA LICITAÇÃO
	    	     		</td>
	      	   	</tr>
							<tr>
								<table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" width="100%">
									<tr>
										<td valign="top" bgcolor="#F7F7F7" class="textonegrito" width="1%">CODIGO</td>
										<td valign="top" bgcolor="#F7F7F7" class="textonegrito" width="1%">DATA</td>
										<td valign="top" bgcolor="#F7F7F7" class="textonegrito" width="1%">OBJETO</td>
										<td valign="top" bgcolor="#F7F7F7" class="textonegrito" width="1%">COMANDO</td>
										<td valign="top" bgcolor="#F7F7F7" class="textonegrito" width="1%">USUÁRIO</td>
										<td valign="top" bgcolor="#F7F7F7" class="textonegrito">DADOS</td>
									</tr>
	      	   	<?php
								$db     = Conexao();
								$sql    = "
									Select
										ll.clplogcodi, ll.xlplogtabl, ll.xlplogcmnd, ll.xlplogqndo, ll.xlplogprmt,
										u.eusupologi,  ll.dlplogulat
									from
										sfpc.tblicitacao_log ll
											LEFT OUTER JOIN sfpc.tbusuarioportal u ON ll.cusupocodi = u.cusupocodi
									where
										ll.clicpoproc = $Processo and
										ll.alicpoanop = $Ano and
										ll.cgrempcodi = $Grupo and
										ll.ccomlicodi = $Comissao and
										ll.corglicodi = $Orgao";
								if(is_null($CodDelete) or $CodDelete == ""){
									// Ignorando registros de licitações anteriores com mesma chave primária
									$sql .="
										and ll.clplogcodi > (
											select MAX(clplogcodi) from
												(
													Select ll2.clplogcodi
													from sfpc.tblicitacao_log ll2
													where
														ll2.clicpoproc = $Processo and
														ll2.alicpoanop = $Ano and
														ll2.cgrempcodi = $Grupo and
														ll2.ccomlicodi = $Comissao and
														ll2.corglicodi = $Orgao and
														ll2.XLPLOGTABL = 'tblicitacaoportal' AND
														ll2.XLPLOGCMND = 'DELETE'
													UNION
														-- '-1' está como valor mínimo para nunca ocorrer NULL
														Select '-1' as clplogcodi
												) as ultima_licitacao_deletada
										)
									";
								}else{
									// Pegando o log entre o registro $CodDelete e o registro de delete de licitações anteriores com mesma chave
									$sql .="
										and ll.clplogcodi <= $CodDelete
										and ll.clplogcodi > (
											select MAX(clplogcodi) from
												(
													Select ll2.clplogcodi
													from sfpc.tblicitacao_log ll2
													where
														ll2.clicpoproc = $Processo and
														ll2.alicpoanop = $Ano and
														ll2.cgrempcodi = $Grupo and
														ll2.ccomlicodi = $Comissao and
														ll2.corglicodi = $Orgao and
														ll2.XLPLOGTABL = 'tblicitacaoportal' AND
														ll2.XLPLOGCMND = 'DELETE' AND
														ll2.clplogcodi < $CodDelete
													UNION
														-- '-1' está como valor mínimo para nunca ocorrer NULL
														Select '-1' as clplogcodi
												) as ultima_licitacao_deletada
										)
									";
								}
								$sql .="
									order by ll.clplogcodi
								";
								$result = $db->query($sql);
								if( PEAR::isError($result) ){
										EmailErroSQL("Erro no SQL", __FILE__, __LINE__, "Erro no SQL", $sql, $result);
								}
								$Rows = $result->numRows();

								if($Rows==0){
									?>
									<tr>
										<td valign="top" bgcolor="#F7F7F7" class="textonormal" colspan = 6 >Nenhum registro encontrado.</td>
									</tr>
									<?
								}else{
									for ($itr=0;$itr<$Rows;$itr++){
										$Linha = $result->fetchRow();
										$ItemCodigo = $Linha[0];
										$ItemTabela = $Linha[1];
										$ItemComando = $Linha[2];
										$ItemQuando = $Linha[3];
										$ItemValores = $Linha[4];
										$ItemUsuario = $Linha[5];
										if(is_null($ItemUsuario)){
											$ItemUsuario = "---";
										}
										$ItemData = DataBarra($Linha[6]);
										$Objeto = "";
										$Acao = "";
										$Valores = explodirValores($ItemValores);
										if ($ItemComando == "UPDATE"){
											$Acao = "Alteração";
										} else if ($ItemComando == "INSERT"){
											$Acao = "Inclusão";
										} else if ($ItemComando == "DELETE"){
											$Acao = "Exclusão";
										}
										if ($ItemTabela == "tblicitacaoportal"){
											$Objeto = "Licitação";
										} else if ($ItemTabela == "tbfaselicitacao"){
											$Objeto = "Fase";
										} else if ($ItemTabela == "tbatasfase"){
											$Objeto = "Ata de fase";
										} else if ($ItemTabela == "tbdocumentolicitacao"){
											$Objeto = "Documento";
										} else if ($ItemTabela == "tbresultadolicitacao"){
											$Objeto = "Resultado";
										} else if ($ItemTabela == "tblicitacaobloqueioorcament"){
											$Objeto = "Bloqueio";
										}
										if($ItemComando == "UPDATE" AND $ItemQuando=="ANTES"){
											$ValoresAntes= $Valores; //guardando os valores para comparar as modificaçoes posteriores
											$ItemCodigoAntes = $ItemCodigo;
										}else{
											$ValoresAlterados = array(); //guarda os itens que foram alterados
											$ValoresAlteradosStr = "";
											$ptr=0;
											$quantidadeValores= count($Valores);
											if($ItemComando == "UPDATE" AND $ItemQuando=="DEPOIS"){
												$quantidadeValores= count($ValoresAntes);
												//$ItemCodigo=$ItemCodigoAntes; //no caso de update 1 linha mostra 2 registros. Então apenas imprimir o 1o.
												for($itr2=0;$itr2<$quantidadeValores; $itr2++){
													# levantando os itens que foram alterados
													if($Valores[$itr2] != $ValoresAntes[$itr2]){
														$ValoresAlterados[$itr2] = TRUE;
														$ptr++;
													}else{
														$ValoresAlterados[$itr2] = FALSE;
													}
												}
											}else if($ItemComando == "INSERT" ){
												for($itr2=0;$itr2<$quantidadeValores; $itr2++){
													$ValoresAlterados[$itr2] = TRUE;
												}
											}else if($ItemComando == "DELETE" ){
												for($itr2=0;$itr2<$quantidadeValores; $itr2++){
													$ValoresAlterados[$itr2] = TRUE;
												}
											}
											//if($ItemComando == "INSERT" OR $ItemComando == "UPDATE"){
												if ($ItemTabela == "tblicitacaoportal"){
													if($ValoresAlterados[0] or $ValoresAlterados[1]){
														$ValoresAlteradosStr.="Processo = ".$Valores[0]."/".$Valores[1]."; ";
													}
													if($ValoresAlterados[6] or $ValoresAlterados[7]){
														$ValoresAlteradosStr.="licitação = ".$Valores[6]."/".$Valores[7]."; ";
													}
													if($ValoresAlterados[2]){
														$ValoresAlteradosStr.="Grupo de empresa = ".$Valores[2]."; ";
													}
													if($ValoresAlterados[3]){
														$ValoresAlteradosStr.="Comissão = ".$Valores[3]."; ";
													}
													if($ValoresAlterados[4]){
														$ValoresAlteradosStr.="Órgão = ".$Valores[4]."; ";
													}
													if($ValoresAlterados[5]){
														$ValoresAlteradosStr.="Modalidade = ".$Valores[5]."; ";
													}
													if($ValoresAlterados[8]){
														$ValoresAlteradosStr.="Data de abertura = ".$Valores[8]."; ";
													}
													if($ValoresAlterados[9]){
														$ValoresAlteradosStr.="Objeto = ".$Valores[9]."; ";
													}
													if($ValoresAlterados[10]){
														$ValoresAlteradosStr.="Valor Estimado = ".$Valores[10]."; ";
													}
													if($ValoresAlterados[11]){
														$ValoresAlteradosStr.="Valor Homologado = ".$Valores[11]."; ";
													}
													if($ValoresAlterados[12]){
														$ValoresAlteradosStr.="Habilitado para internet = ".$Valores[12]."; ";
													}
													if($ValoresAlterados[15]){
														$ValoresAlteradosStr.="Registro de preço = ".$Valores[13]."; ";
													}
													if($ValoresAlterados[16]){
														$ValoresAlteradosStr.="Valor geral Estimado = ".$Valores[14]."; ";
													}
												} else if ($ItemTabela == "tbfaselicitacao"){
													if($ValoresAlterados[0]){
														$ValoresAlteradosStr.="Código da fase= ".$Valores[0]."; ";
													}
													if($ValoresAlterados[1] or $ValoresAlterados[2]){
														$ValoresAlteradosStr.="Processo = ".$Valores[1]."/".$Valores[2]."; ";
													}
													if($ValoresAlterados[3]){
														$ValoresAlteradosStr.="Grupo de empresa = ".$Valores[3]."; ";
													}
													if($ValoresAlterados[4]){
														$ValoresAlteradosStr.="Comissão = ".$Valores[4]."; ";
													}
													if($ValoresAlterados[5]){
														$ValoresAlteradosStr.="Òrgão = ".$Valores[5]."; ";
													}
													if($ValoresAlterados[6]){
														$ValoresAlteradosStr.="Detalhamento = ".$Valores[6]."; ";
													}
													if($ValoresAlterados[7]){
														$ValoresAlteradosStr.="Data = ".$Valores[7]."; ";
													}
												} else if ($ItemTabela == "tbatasfase"){
													if($ValoresAlterados[0]){
														$ValoresAlteradosStr.="Código da fase= ".$Valores[0]."; ";
													}
													if($ValoresAlterados[1] or $ValoresAlterados[2]){
														$ValoresAlteradosStr.="Processo = ".$Valores[1]."/".$Valores[2]."; ";
													}
													if($ValoresAlterados[3]){
														$ValoresAlteradosStr.="Grupo de empresa = ".$Valores[3]."; ";
													}
													if($ValoresAlterados[4]){
														$ValoresAlteradosStr.="Comissão = ".$Valores[4]."; ";
													}
													if($ValoresAlterados[5]){
														$ValoresAlteradosStr.="Òrgão = ".$Valores[5]."; ";
													}
													if($ValoresAlterados[6]){
														$ValoresAlteradosStr.="Código ata = ".$Valores[6]."; ";
													}
													if($ValoresAlterados[7]){
														$ValoresAlteradosStr.="Nome ata = ".$Valores[7]."; ";
													}
													if($ValoresAlterados[8]){
														$ValoresAlteradosStr.="Data = ".$Valores[8]."; ";
													}
													if($ValoresAlterados[11]){
														$ValoresAlteradosStr.="Ata excluída = ".$Valores[11]."; ";
													}
													if($ValoresAlterados[12]){
														$ValoresAlteradosStr.="Observação/Justificativa = ".$Valores[12]."; ";
													}
												} else if ($ItemTabela == "tbdocumentolicitacao"){
													if($ValoresAlterados[0] or $ValoresAlterados[1]){
														$ValoresAlteradosStr.="Processo = ".$Valores[0]."/".$Valores[1]."; ";
													}
													if($ValoresAlterados[2]){
														$ValoresAlteradosStr.="Grupo de empresa = ".$Valores[2]."; ";
													}
													if($ValoresAlterados[3]){
														$ValoresAlteradosStr.="Comissão = ".$Valores[3]."; ";
													}
													if($ValoresAlterados[4]){
														$ValoresAlteradosStr.="Òrgão = ".$Valores[4]."; ";
													}
													if($ValoresAlterados[5]){
														$ValoresAlteradosStr.="Código doc = ".$Valores[5]."; ";
													}
													if($ValoresAlterados[6]){
														$ValoresAlteradosStr.="Nome doc = ".$Valores[6]."; ";
													}
													if($ValoresAlterados[7]){
														$ValoresAlteradosStr.="Data = ".$Valores[7]."; ";
													}
													if($ValoresAlterados[8]){
														$ValoresAlteradosStr.="Observação/Justificativa = ".$Valores[8]."; ";
													}
													if($ValoresAlterados[11]){
														$ValoresAlteradosStr.="Doc excluido = ".$Valores[11]."; ";
													}
												} else if ($ItemTabela == "tbresultadolicitacao"){
													if($ValoresAlterados[0] or $ValoresAlterados[1]){
														$ValoresAlteradosStr.="Processo = ".$Valores[0]."/".$Valores[1]."; ";
													}
													if($ValoresAlterados[2]){
														$ValoresAlteradosStr.="Grupo de empresa = ".$Valores[2]."; ";
													}
													if($ValoresAlterados[3]){
														$ValoresAlteradosStr.="Comissão = ".$Valores[3]."; ";
													}
													if($ValoresAlterados[4]){
														$ValoresAlteradosStr.="Òrgão = ".$Valores[4]."; ";
													}
												} else if ($ItemTabela == "tblicitacaobloqueioorcament"){
													if($ValoresAlterados[0] or $ValoresAlterados[1]){
														$ValoresAlteradosStr.="Processo = ".$Valores[0]."/".$Valores[1]."; ";
													}
													if($ValoresAlterados[2]){
														$ValoresAlteradosStr.="Grupo de empresa = ".$Valores[2]."; ";
													}
													if($ValoresAlterados[3]){
														$ValoresAlteradosStr.="Comissão = ".$Valores[3]."; ";
													}
													if($ValoresAlterados[4]){
														$ValoresAlteradosStr.="Òrgão = ".$Valores[4]."; ";
													}
													if($ValoresAlterados[5]){
														$ValoresAlteradosStr.="Unid. Orçament. Ano = ".$Valores[5]."; ";
													}
													if($ValoresAlterados[6]){
														$ValoresAlteradosStr.="Unid. Orçament. Orgão = ".$Valores[6]."; ";
													}
													if($ValoresAlterados[7]){
														$ValoresAlteradosStr.="Unid. Orçament. Código = ".$Valores[7]."; ";
													}
													if($ValoresAlterados[8]){
														$ValoresAlteradosStr.="Bloqu. Sequencial = ".$Valores[8]."; ";
													}
													if($ValoresAlterados[9]){
														$ValoresAlteradosStr.="Bloqu. função = ".$Valores[9]."; ";
													}
													if($ValoresAlterados[10]){
														$ValoresAlteradosStr.="Bloqu. sub-função = ".$Valores[10]."; ";
													}
													if($ValoresAlterados[11]){
														$ValoresAlteradosStr.="Bloqu. programa = ".$Valores[11]."; ";
													}
													if($ValoresAlterados[12]){
														$ValoresAlteradosStr.="Bloqu. tipo = ".$Valores[12]."; ";
													}
													if($ValoresAlterados[13]){
														$ValoresAlteradosStr.="Bloqu. ordem = ".$Valores[13]."; ";
													}
													if($ValoresAlterados[14] or $ValoresAlterados[15] or $ValoresAlterados[16] or $ValoresAlterados[17]){
														$ValoresAlteradosStr.="Elemento de despesa = ".$Valores[14].".".$Valores[15].".".$Valores[16].".".$Valores[17]."; ";
													}
													if($ValoresAlterados[18]){
														$ValoresAlteradosStr.="Fonte de recurso = ".$Valores[18]."; ";
													}
												}
											//}
											$ValoresAlteradosStr.="&nbsp;";

											?>
											 <tr>
												<td valign="top" bgcolor="#F7F7F7" class="textonormal"><?=$ItemCodigo?></td>
												<td valign="top" bgcolor="#F7F7F7" class="textonormal"><?=$ItemData?></td>
												<td valign="top" bgcolor="#F7F7F7" class="textonormal"><?=$Objeto?></td>
												<td valign="top" bgcolor="#F7F7F7" class="textonormal"><?=$Acao?></td>
												<td valign="top" bgcolor="#F7F7F7" class="textonormal"><?=$ItemUsuario?></td>
												<td valign="top" bgcolor="#F7F7F7" class="textonormal"><?=$ValoresAlteradosStr?></td>
											 </tr>
											<?php
										}
									}
								}
	      	   	?>
	      	   	</tr>
	      	   	<tr class="textonormal" align="right">
	      	   	  <td colspan=6>
	      	   	  	<input type="button" name="Voltar" value="Voltar" class="botao" onClick="history.go(-1);return true;"/>
	      	   	  	<input type="hidden" name="Botao" value=""/>
	      	   	  </td>
	      	   	</tr>
						</table>
					</td>
				</tr>
			</table>
		</td>
	</tr>

</table>
</body>
</html>

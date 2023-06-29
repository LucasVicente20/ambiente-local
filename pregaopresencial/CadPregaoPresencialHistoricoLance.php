<?php
# -------------------------------------------------------------------------
# Portal da DGCO teste
# Programa: CadPregaoPresencialHistoricoLance.php
# Autor:    Hélio Miranda 
# Data:     29/07/2016
# Objetivo: PVisualizar e Manter o Histórico de Lances
# OBS.:     Tabulação 2 espaços 
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

		
}else{
		$Critica       							= $_GET['Critica'];
		$Mensagem      							= urldecode($_GET['Mensagem']);
		$Mens          							= $_GET['Mens'];
		$Tipo          							= $_GET['Tipo'];
		$_SESSION['CodFornecedorSelecionado']	= $_GET['CodFornecedorSelecionado'];

}

if($_SESSION['Botao'] == "DesfazerUltimaRodada") 
{
	$_SESSION['Botao'] = null;
	$UltimaRodada 						= ($_POST['RodadaAtual'] - 1); 
	$TotalParticipantes					= $_POST['TotalParticipantes'];
	$CodLoteSelecionado					= $_SESSION['CodLoteSelecionado'];
	$CodSituacaoClassificacao			= $_SESSION['CodSituacaoClassificacao'];
	
	if($UltimaRodada > 0)
	{
		$db     = Conexao();
		$_SESSION['UltimaSessaoDesfeita'] 	= True; 
		
		$sql 			= "SELECT la.vpreglvall FROM sfpc.tbpregaopresenciallance la WHERE cpreglnumr = $UltimaRodada AND cpregtsequ = $CodLoteSelecionado";
		$resUltimoPreco = $db->query($sql);
		
		if (PEAR::isError($resUltimoPreco)) {
			ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
			$LinhaUltimoValorDeLance  		= $resUltimoPreco->fetchRow();
		}		
		
		for($itr = 0; $itr < $TotalParticipantes; ++ $itr)
		{
			$_SESSION['UltimoPreco_'.$itr] 	= $LinhaUltimoValorDeLance[0];
			
			$LinhaUltimoValorDeLance  		= $resUltimoPreco->fetchRow();
		}
		
		$sql = "DELETE FROM sfpc.tbpregaopresenciallance WHERE cpreglnumr = $UltimaRodada AND cpregtsequ = $CodLoteSelecionado";
		$res = $db->query($sql);
		if (PEAR::isError($res)) {
			ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}		
		$sql = "UPDATE sfpc.tbpregaopresenciallote SET cpregfsequ = null, vpregtvalv = 0.00, cpreslsequ = 1 WHERE cpregtsequ = $CodLoteSelecionado";		
		$res = $db->query($sql);
		if (PEAR::isError($res)) {
			ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}		

		$db->disconnect();
		
					
		echo "<script>opener.document.CadPregaoPresencialSessaoPublica.Destino.value = 'E';</script>";
		echo "<script>opener.document.CadPregaoPresencialSessaoPublica.CodLoteSelecionado.value = $CodLoteSelecionado;</script>";
		echo "<script>opener.document.CadPregaoPresencialSessaoPublica.Botao.value = 'SelecionarLote';</script>";
		echo "<script>opener.document.CadPregaoPresencialSessaoPublica.submit()</script>";			
		
		$_SESSION['Mens'] = 1;
		$_SESSION['Tipo'] = 1;
		$_SESSION['Mensagem'] .= "- Ultima Rodada de Lances removida com sucesso!";

		echo "<script>self.close()</script>";		
	}
}

if($_SESSION['Botao'] == "MantemPrecoVencedor")
{
	$_SESSION['Botao'] = null;
	$RodadaAtual 						= $_POST['RodadaAtual'];
	$TotalParticipantes					= $_POST['TotalParticipantes'];
	$PrecoVencedor 						= $_POST['PrecoVencedor'];
	$CodLoteSelecionado					= $_SESSION['CodLoteSelecionado'];
	$CodSituacaoClassificacao			= $_SESSION['CodSituacaoClassificacao'];
	
	$db     = Conexao();
	
	$SqlLances 			= "SELECT  		COUNT(pl.cpreglsequ)
							FROM 		sfpc.tbpregaopresenciallance pl 
							WHERE 		pl.cpregtsequ = $CodLoteSelecionado
								AND		pl.fpreglrpfn = 1"; 		

	$ResultLances = $db->query($SqlLances);
	if( PEAR::isError($ResultLotes) )
	{
		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $SqlLances");
	}

	$TotalLancesRegistro = $ResultLances->fetchRow();
	
	if($TotalLancesRegistro[0] <= 0)
	{
		for ($itr = 0; $itr < $TotalParticipantes; ++ $itr) 
		{	
			$_SESSION['ValorParaRegistro_'.$itr]	= null;
			$CodFornecedor							= $_POST['CodFornecedor_'.$itr];
			$CodPrecoInicial						= $_POST['CodPrecoInicial_'.$itr];
			//Insere preços de registro com valor zero
			
			$sqlCod = "SELECT MAX(cpreglsequ) FROM sfpc.tbpregaopresenciallance";
			$resCod = $db->query($sqlCod);
			
			if (PEAR::isError($resCod)) {
				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlCod");
			}else{
					$LinhaLance  			= $resCod->fetchRow();
					$CodigoLance			= $LinhaLance[0] + 1;
			}						
			
			$sql  = "INSERT INTO sfpc.tbpregaopresenciallance( ";
			$sql .= "cpreglsequ, cpregfsequ, cpregtsequ, cpregpsequ, cpreglnumr, fpreglurod, vpreglvall, fpregllven, epregldesc, fpreglmpre, fpreglefic, fpreglrpfn,";
			$sql .= "dpreglcada, ";
			$sql .= "tpreglulat ";
			$sql .= " ) VALUES ( ";
			$sql .= "$CodigoLance, $CodFornecedor, $CodLoteSelecionado, $CodPrecoInicial, $RodadaAtual, 0, 0.00, 0, '', 0, 0, 1, ";
			$sql .= "'".date("Y-m-d")."', ";
			$sql .= "'".date("Y-m-d H:i:s")."' );";	
			
			$res  = $db->query($sql);						
			/*
			if( PEAR::isError($res) ){
				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
			}	*/		
		}
	}
	
	for ($itrB = 0; $itrB < $TotalParticipantes; ++ $itrB) 
	{
		$ValorParaRegistro 						= $_POST['ValorParaRegistro_'.$itrB];
		$CodFornecedor							= $_POST['CodFornecedor_'.$itrB];
		$CodPrecoInicial						= $_POST['CodPrecoInicial_'.$itrB];
		$_SESSION['ValorParaRegistro_'.$itrB]	= $ValorParaRegistro;
		
		if($ValorParaRegistro == '' or $ValorParaRegistro == null)
		{
			$ValorParaRegistro = 0.00;
		}
		
		$ValorParaRegistro  		= str_replace(".", "", $ValorParaRegistro);			
		$ValorParaRegistro  		= str_replace(",", ".", $ValorParaRegistro);		
		
		$sql = "UPDATE sfpc.tbpregaopresenciallance SET vpreglvall = $ValorParaRegistro WHERE cpregtsequ = $CodLoteSelecionado AND cpregfsequ = $CodFornecedor AND fpreglrpfn = 1";		
		$res = $db->query($sql);
		
		if (PEAR::isError($res)) {
			ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}	
	}
	
	if(!empty($_POST['mantem_preco']))
	{	
		foreach($_POST['mantem_preco'] as $selected)
		{
			$sql = "UPDATE sfpc.tbpregaopresenciallance SET fpreglmpre = 1, vpreglvall = $PrecoVencedor WHERE cpreglsequ = $selected AND fpreglrpfn = 1";	
			
			$res = $db->query($sql);
			if (PEAR::isError($res)) {
				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
			}		
		}
	}

	$_SESSION['Mens'] = 1;
	$_SESSION['Tipo'] = 1;
	$_SESSION['Mensagem'] .= "- Alterações realizadas com sucesso!";	
	
	$db->disconnect();
}

if($_SESSION['Botao'] == "LimparManterPrecoVencedor")
{
	$_SESSION['Botao'] = null;
	$CodLoteSelecionado					= $_SESSION['CodLoteSelecionado'];
	$CodSituacaoClassificacao			= $_SESSION['CodSituacaoClassificacao'];
	$TotalParticipantes					= $_POST['TotalParticipantes'];
	

	$db     = Conexao();
	
	for ($itrB = 0; $itrB < $TotalParticipantes; ++ $itrB) 
	{
		$sql = "UPDATE sfpc.tbpregaopresenciallance SET fpreglmpre = 0, vpreglvall = 0 WHERE cpregtsequ = $CodLoteSelecionado AND fpreglrpfn = 1";		
		$res = $db->query($sql);
		if (PEAR::isError($res)) {
			ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}		
	}
	
	$db->disconnect();
	

	$_SESSION['Mens'] = 1;
	$_SESSION['Tipo'] = 1;
	$_SESSION['Mensagem'] .= "- Alterações Removidas com sucesso!";		
				
}

?>
<html>
<head>
<title>Portal de Compras - Incluir Fornecedor</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<script language="javascript" type="">
<!--
function checktodos(){
	document.CadPregaoPresencialHistoricoLance.Subclasse.value = '';
	document.CadPregaoPresencialHistoricoLance.submit();
}
function enviar(valor){
	document.CadPregaoPresencialHistoricoLance.Botao.value = valor;
	document.CadPregaoPresencialHistoricoLance.submit();
}
function validapesquisa(){
	if( ( document.CadPregaoPresencialHistoricoLance.MaterialDescricaoDireta.value != '' ) || ( document.CadPregaoPresencialHistoricoLance.SubclasseDescricaoDireta.value != '') ) {
		if(document.CadPregaoPresencialHistoricoLance.Grupo){
			document.CadPregaoPresencialHistoricoLance.Grupo.value = '';
		}
		if(document.CadPregaoPresencialHistoricoLance.Classe){
			document.CadPregaoPresencialHistoricoLance.Classe.value = '';
		}
		document.CadPregaoPresencialHistoricoLance.Botao.value = 'Validar';
	}
	if(document.CadPregaoPresencialHistoricoLance.Subclasse){
		if(document.CadPregaoPresencialHistoricoLance.SubclasseDescricaoFamilia.value != "") {
			document.CadPregaoPresencialHistoricoLance.Subclasse.value = '';
		}
	}
	document.CadPregaoPresencialHistoricoLance.submit();
}
function AbreJanela(url,largura,altura) {
	window.open(url,'paginadetalhe','status=no,scrollbars=yes,left=45,top=150,width='+largura+',height='+altura);
}
function voltar(){
	self.close();
}
function remeter(){
	document.CadPregaoPresencialHistoricoLance.submit();
}

//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">

</head>
<body background="../midia/bg.jpg" marginwidth="0" marginheight="0">
<form action="CadPregaoPresencialHistoricoLance.php" method="post" name="CadPregaoPresencialHistoricoLance">
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
	        	HISTÓRICO DE LANCES - PREGÃO PRESENCIAL
				
				
				
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
											$tipoOrdenacao = "DESC";
										}
										else
										{
											$tipoOrdenacao = "ASC";
										}										
										
										//Verificando se existe licitações ligadas a o processo , para ver qual programa devo chamar
										$sqlFornecedores = "SELECT		fn.apregfccgc, fn.apregfccpf, fn.npregfrazs, fn.npregfnomr, fn.apregfnurg, 
																		fn.epregfsitu, fn.cpregfsequ, npregforgu, sf.epresfnome, sf.cpresfsequ, lt.cpregtsequ,
																		pi.vpregpvali, pi.cpregpsequ, fn.npregfnomr, fn.fpregfmepp, pi.fpregpalan, fn.fpregfmepp
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
																AND		pi.fpregpalan  = 1
															ORDER BY	pi.vpregpvali $tipoOrdenacao, fn.npregfrazs ASC";  
											
											
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
										
								//Pegar a última rodada de lances - Se não houver o valor passado será zero
								
										$sqlUltimaRodadaLances = "SELECT			MAX(la.cpreglnumr)
																	FROM 			sfpc.tbpregaopresenciallance la,
																					sfpc.tbpregaopresenciallote lt,
																					sfpc.tbpregaopresencialprecoinicial pi,
																					sfpc.tbpregaopresencialfornecedor fn
																	WHERE			lt.cpregtsequ  = $CodLoteSelecionado
																		AND			lt.cpregtsequ  = la.cpregtsequ
																		AND			lt.cpregtsequ  = pi.cpregtsequ
																		AND			la.cpregpsequ  = pi.cpregpsequ
																		AND 		pi.cpregfsequ  = fn.cpregfsequ
																		AND			pi.fpregpalan  = 1
																		AND         la.fpreglrpfn  = 0"; 
																
										$resultUltimaRodadaLances 	= $db->query($sqlUltimaRodadaLances);
										
										if( PEAR::isError($resultUltimaRodadaLances) ){
											ExibeErroBD("$ErroPrograma\nLinhaPrecoInicial: ".__LINE__."\nSql: $sqlSolicitacoes");
										}										
										
										$LinhaUltimaRodadaLances 	= $resultUltimaRodadaLances->fetchRow();								
										$UltimaRodadaLances 		= $LinhaUltimaRodadaLances[0];
										
										if($UltimaRodadaLances == '' or $UltimaRodadaLances == null)
										{
											$UltimaRodadaLances = 0;
										}
										else if ($UltimaRodadaLances > 0)
										{
											$sqlValUltimaRodadaLances = "SELECT				la.vpreglvall, la.fpreglurod, la.fpregllven, la.cpreglsequ
																			FROM 			sfpc.tbpregaopresenciallance la,
																							sfpc.tbpregaopresenciallote lt,
																							sfpc.tbpregaopresencialprecoinicial pi,
																							sfpc.tbpregaopresencialfornecedor fn
																			WHERE			lt.cpregtsequ  = $CodLoteSelecionado
																				AND			lt.cpregtsequ  = la.cpregtsequ
																				AND			lt.cpregtsequ  = pi.cpregtsequ
																				AND			la.cpregpsequ  = pi.cpregpsequ
																				AND 		pi.cpregfsequ  = fn.cpregfsequ
																				AND			pi.fpregpalan  = 1
																				AND			la.cpreglnumr  = $UltimaRodadaLances
																				AND         la.fpreglrpfn  = 0
																			ORDER BY		pi.vpregpvali $tipoOrdenacao, fn.npregfrazs ASC"; 
																	
											$resultValUltimaRodadaLances 	= $db->query($sqlValUltimaRodadaLances);	
											
											if( PEAR::isError($resultValUltimaRodadaLances) ){
												ExibeErroBD("$ErroPrograma\nLinhaPrecoInicial: ".__LINE__."\nSql: $sqlSolicitacoes");
											}												
											
											$LinhaValUltimaRodadaLances 	= $resultValUltimaRodadaLances->fetchRow();	
											$PregaoFinalizado = 0;
											
											//Verifica se já existe o lance de registro
											
											//se não insere Lance de Registro vazio para todos os fornecedores do lote										
											
											$SqlValRegistroPreco = "SELECT				la.vpreglvall, la.cpreglsequ, la.fpreglmpre
																			FROM 			sfpc.tbpregaopresenciallance la,
																							sfpc.tbpregaopresenciallote lt,
																							sfpc.tbpregaopresencialprecoinicial pi,
																							sfpc.tbpregaopresencialfornecedor fn
																			WHERE			lt.cpregtsequ  = $CodLoteSelecionado
																				AND			lt.cpregtsequ  = la.cpregtsequ
																				AND			lt.cpregtsequ  = pi.cpregtsequ
																				AND			la.cpregpsequ  = pi.cpregpsequ
																				AND 		pi.cpregfsequ  = fn.cpregfsequ
																				AND			pi.fpregpalan  = 1
																				AND         la.fpreglrpfn  = 1
																			ORDER BY		pi.vpregpvali $tipoOrdenacao, fn.npregfrazs ASC"; 
																	
											$resultValRegistroPreco 	= $db->query($SqlValRegistroPreco);	
											
											if( PEAR::isError($resultValRegistroPreco) ){
												ExibeErroBD("$ErroPrograma\nLinhaPrecoInicial: ".__LINE__."\nSql: $sqlSolicitacoes");
											}												
											
											$LinhaValRegistroPreco 	= $resultValRegistroPreco->fetchRow();												
											
										}
										
										$TotalColunasFixas = 8;
										$TotalColunasDinamicas = 0;											
									}
//Fornecedores - Fim									
								}								
							?>				
          </td>
        </tr>
        <tr>
          <td class="textonormal">
             <p align="justify">
				Para incluir lances deve-se informar o valor correspondente ao lance do Fornecedor, caso queira encerrar a sequência de Lances de um Fornecedor, deve-se deixar o valor
				"0.00" e o mesmo ficará como "S/L" (Sem Lance). Para avançar para a próxima rodada deve-se clicar no botão "Próxima Rodada". Para redigitar um valor incorreto numa rodada 
				já encerrada, deve-se clicar sobre o título da rodada (Rodada 01, Rodada 02...), e a mesma, ficará editável novamente. Para encerrar as Rodadas de Lances deve-se clicar 
				no botão "Finalizar Lances"
             </p>
          </td>
        </tr>
        <tr>
          <td>
            <table border="0" summary="" width="<?=(900 + ($UltimaRodadaLances * 60))?>">
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
							<td align="left" bgcolor="#DCEDF7" class="textonormal"  style="font-weight: bold;">Tipo de Licitação: </td>
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
																>FORNECEDORES PARTICIPANTES</td>
															</tr>
															
															
															
															<!-- Headers ITENS DA SOLICITAÇÃO DE MATERIAL  -->
												<tr class="head_principal">

													<?php // <!--  Coluna 1 = ORD--> ?>
													<td
																	class="textoabason"
																	align="center"
																	bgcolor="#DCEDF7"
																	width="5%"
																><br /> ORD </td>
																
													<?php // <!--  Coluna 1 = Checkbox--> ?>
													
													<td
																	class="textoabason"
																	align="center"
																	bgcolor="#DCEDF7"
																	width="5%"
																><br /> <label style="cursor: help;" title="Mantém o preço do 'Vencedor da Disputa'"> M.P.V.</label> </td>	

													<?php // <!--  Coluna 1 = Checkbox--> ?>
													<td
																	class="textoabason"
																	align="center"
																	bgcolor="#DCEDF7"
																	width="5%"
																><br /> <label style="cursor: help;" title="Registrar o ultimo preço do Fornecedor, para o caso do vencedor da disputa ser inabilitado"> Registrar Preço</label> </td>																	
													
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
																	width="25%"
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
																	width="10%"
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
																	width="10%"
																><img
																	src="../midia/linha.gif"
																	alt=""
																	border="0"
																	height="1px"
																/> <br /> REPRESENTADO </td>																
																
													<?php // <!--  Coluna 5 = R.G.--> ?>
													<td
																	class="textoabason"
																	align="center"
																	bgcolor="#DCEDF7"
																	width="10%"
																	style="cursor: help;"
																	title = "<?php echo ($_SESSION['PregaoTipo'] == 'N' ? 'R$' : '%'); ?>"																		
																><img
																	src="../midia/linha.gif"
																	alt=""
																	border="0"
																	height="1px"
																/> <br /> <?php echo ($_SESSION['PregaoTipo'] == 'N' ? 'PREÇO INICIAL' : 'OFERTA INICIAL'); ?> </td>																
															
													<?php // <!--  Coluna 6 = SITUAÇÃO--> ?>															

											
											<?
														
													if($UltimaRodadaLances > 0)
													{
														for ($itr = $UltimaRodadaLances; $itr > 0; -- $itr) 
														{
															
															$sqlLances 		= "SELECT 	fpreglefic, fpreglurod
																					FROM 	sfpc.tbpregaopresenciallance 
																					WHERE 	cpreglnumr = $itr 
																						AND cpregtsequ = $CodLoteSelecionado 
																					LIMIT 1";
															
															$resultLances 	= $db->query($sqlLances);
															
															
															if (PEAR::isError($resultLances)) {
																ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
															}else{
																$LinhaLances  = $resultLances->fetchRow();
															}															
															
															$TotalColunasDinamicas ++;
											?>
															<td
																class="textoabason"
																align="center"
																bgcolor="#DCEDF7"
																width="50px"
															><img
																src="../midia/linha.gif"
																alt=""
																border="0"
																height="1px"
															/> <br /> <?=(($LinhaLances[1] == 1) ? ("ÚLTIMA RODADA") : (($LinhaLances[0] == 1) ? ("EMPATE FÍCTIO") : ($itr . "ª RODADA")))?></td>											
											<?
														}
													}	
											?>
											
											<?php
											// Membros do POST-----------------------------------
											
											$UltimoPreco = 0;
											$ContadorPrecosParticipantes = 0;
											
											for ($itr = 0; $itr < $QuantidadeFornecedores; ++ $itr) {
												
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

												if($LinhaValUltimaRodadaLances[1] == 1 and $LinhaValUltimaRodadaLances[2] == 1)
												{
													$PregaoFinalizado = 1;
												}												
											?>
											
											<?
												if($LinhaValUltimaRodadaLances[1] == 1 and $LinhaValUltimaRodadaLances[2] == 1)
												{
											?>
													<input type="hidden" name="PrecoVencedor" value="<?=$LinhaValUltimaRodadaLances[0]?>">											
											<?
												}
											?>
											
											<!-- Dados MEMBRO DE COMISSÃO  -->
															<tr>
																<!--  Coluna 1 = Codido-->
																<td
																	class="textonormal"
																	align="center"
																	style="text-align: center; <?=(($LinhaValUltimaRodadaLances[1] == 1 and $LinhaValUltimaRodadaLances[2] == 1) ? "background-color: yellow;" : "")?>"
																	
																>
																<?= ($itr + 1)?>
														</td>
														
																<!--  Coluna 1 = Checkbox-->
																<td
																	class="textonormal"
																	align="center"
																	style="text-align: center; <?=(($LinhaValUltimaRodadaLances[1] == 1 and $LinhaValUltimaRodadaLances[2] == 1) ? "background-color: yellow;" : "")?>"
																	
																>
																<?
																if($LinhaValUltimaRodadaLances[1] == 1 and $LinhaValUltimaRodadaLances[2] == 0)
																{
																?>
																	
																	<input type="checkbox" name="mantem_preco[]" value="<?=$LinhaValRegistroPreco[1]?>" <?=(($LinhaValRegistroPreco[2] == 1) ? ("checked") : (""))?> ></input>
																<?
																}
																?>
														</td>
																<td
																	class="textonormal"
																	align="center"
																	style="text-align: center; <?=(($LinhaValUltimaRodadaLances[1] == 1 and $LinhaValUltimaRodadaLances[2] == 1) ? "background-color: yellow;" : "")?>"
																	
																>
																
																
																<!-- Registrar Preço-->
																
																
																<?
																if($LinhaValRegistroPreco[0] == null)
																{
																	$PrecoRegistro = 0.000;
																}
																else
																{
																	$PrecoRegistro = $LinhaValRegistroPreco[0];
																	$_SESSION['ValorParaRegistro_'.$itr] = $PrecoRegistro;
																}
																
																if($LinhaValUltimaRodadaLances[1] == 1 and $LinhaValUltimaRodadaLances[2] == 0)
																{
																	$Valor = number_format($_SESSION['ValorParaRegistro_'.$itr], 4, ',', '.');
																?>
																	<input type="text" name="ValorParaRegistro_<?= $itr?>" size="15" maxlength="15" 
																	value="<?=(($_SESSION['ValorParaRegistro_'.$itr] > 0) ? ($Valor) : ("0,0000"));?>" 
																	class="textonormal"/> 																
																<?
																}
																?>
																</td>														
														
																<!--  Coluna 2  = CPF/CNPJ -->
																<td class="textonormal" align="center"
																style="<?=(($LinhaValUltimaRodadaLances[1] == 1 and $LinhaValUltimaRodadaLances[2] == 1) ? "background-color: yellow;" : "")?>">																
																	
																	<?= ($LinhaPrecoInicial[1] == "" 
																	?
																	(substr($LinhaPrecoInicial[0], 0, 2).'.'.substr($LinhaPrecoInicial[0], 2, 3).'.'.substr($LinhaPrecoInicial[0], 5, 3).'/'.substr($LinhaPrecoInicial[0], 8, 4).'-'.substr($LinhaPrecoInicial[0], 12, 2)) 
																	: 
																	(substr($LinhaPrecoInicial[1], 0, 3).'.'.substr($LinhaPrecoInicial[1], 3, 3).'.'.substr($LinhaPrecoInicial[1], 6, 3).'-'.substr($LinhaPrecoInicial[1], 9, 2)));?>																	
																	
																</td>
																
																<!--  Coluna 3  = Razão Social -->
																<td align="center" class="textonormal"
																style="<?=(($LinhaValUltimaRodadaLances[1] == 1 and $LinhaValUltimaRodadaLances[2] == 1) ? "background-color: yellow;" : "")?>">																	
																	
																	<?= $LinhaPrecoInicial[2] ?>																	
																	
																</td>
																
																<!--  Coluna 3  = Tipo de Empresa -->
																<td class="textonormal" align="center" title="<?=$DescTipoEmpresa?>" style="cursor: help; <?=(($LinhaValUltimaRodadaLances[1] == 1 and $LinhaValUltimaRodadaLances[2] == 1) ? "background-color: yellow;" : "")?>">																	
																	
																	<?= $TipoEmpresa?>																	
																	
																</td>																	
																
																<!--  Coluna 3  = REPRESENTADO -->
																<td align="center" class="textonormal" title="
																<?
																	if($LinhaPrecoInicial[13] == '')
																	{
																		echo "SEM REPRESENTANTE!";
																	}
																	else
																	{
																		echo $LinhaPrecoInicial[13];
																	}																
																?>" style="color:
																
																<?
																	
																	if($LinhaPrecoInicial[13] == '')
																	{
																		$FornecedorRepresentado = 0;
																		echo "red";
																	}
																	else
																	{
																		$FornecedorRepresentado = 1;
																		echo "blue";
																	}
																?> ; cursor: help; <?=(($LinhaValUltimaRodadaLances[1] == 1 and $LinhaValUltimaRodadaLances[2] == 1) ? "background-color: yellow;" : "")?>"
																>																	
																	
																	<?= ($LinhaPrecoInicial[13] == '' ? "NÃO" : "SIM") ?>																	
																	
																</td>																

																<!-- Início do cálculo % -->
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
																	
																<!-- Fim do cálculo % -->
																
																<!--  Coluna 5  = Preço inicial -->
																<td class="textonormal" style="text-align: center; cursor: help;<?=($itr + 1 == $QuantidadeFornecedores ? 'font-weight: bold;' : '')?> <?=(($LinhaValUltimaRodadaLances[1] == 1 and $LinhaValUltimaRodadaLances[2] == 1) ? "background-color: yellow;" : "")?>" title="<?=$Percentual."%"?>">																																		
																	
																	
																	<?= number_format($LinhaPrecoInicial[11], 4, ',', '.')?>
																	
																	<input type="hidden" name="TotalParticipantes" value="<?=$QuantidadeFornecedores?>">
																	<input type="hidden" name="RodadaAtual" value="<?=($UltimaRodadaLances + 1)?>">
																	<input type="hidden" name="CodFornecedor_<?= $itr?>" value="<?=$LinhaPrecoInicial[6]?>">
																	<input type="hidden" name="CodPrecoInicial_<?= $itr?>" value="<?=$LinhaPrecoInicial[12]?>">																	
																</td>	

																<!--  Coluna 6  = Lance inicial -->

															<?
																	if($UltimaRodadaLances > 0)
																	{
																		for ($itrD = $UltimaRodadaLances; $itrD > 0; -- $itrD) 
																		{
																			
																			
																			$sqlLancesAnteriores = "SELECT 	vpreglvall, fpregllven
																									FROM 	sfpc.tbpregaopresenciallance 
																									WHERE 	cpreglnumr = $itrD 
																										AND cpregtsequ = $CodLoteSelecionado 
																										AND	cpregfsequ = $LinhaPrecoInicial[6]";
																			
																			$resultLancesAnteriores = $db->query($sqlLancesAnteriores);
																			
																			
																			if (PEAR::isError($resultLancesAnteriores)) {
																				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																			}else{
																				$LinhaLancesAnteriores  = $resultLancesAnteriores->fetchRow();
																			}																		

																		
															?>
																<td class="textonormal" style="text-align: center; <?=($LinhaLancesAnteriores[1] == 1 ? 'cursor: help; font-weight: bold;' : '')?> <?=(($LinhaValUltimaRodadaLances[1] == 1 and $LinhaValUltimaRodadaLances[2] == 1) ? "background-color: yellow;" : "")?>" title="<?=($LinhaLancesAnteriores[1] == 1 ? 'Lance Vencedor da Rodada' : '')?>">																																		
																	
																	
																	<label title="<?=(($LinhaLancesAnteriores[0] > 0) ? ("") : ("Sem Lance"))?>" style="<?=(($LinhaLancesAnteriores[0] > 0) ? ("") : ("cursor: help; color: red;"))?>"><?= (($LinhaLancesAnteriores[0] > 0) ? (number_format($LinhaLancesAnteriores[0], 4, ',', '.')) : ("S/L"))?></label>
																</td>										
															<?
																		}
																	}	
															?>																	

											<?php
												$LinhaPrecoInicial = $resultFornecedores->fetchRow();
												$LinhaValRegistroPreco 	= $resultValRegistroPreco->fetchRow();
												
												if($UltimaRodadaLances > 0)
												{
													$LinhaValUltimaRodadaLances = $resultValUltimaRodadaLances->fetchRow();
												}
												
												for($itrB = 0; $itrB < $QuantidadeFornecedores; ++ $itrB)
												{			
													$_SESSION['ValLance_'.$itr]	= null;
												}
											}
											
											$db->disconnect();
											?>																
																


											<?php

											if ($QuantidadeFornecedores <= 0) {
												?>
											<tr>
																<td
																	class="textonormal itens_material"
																	colspan="<?=$TotalColunasFixas + $TotalColunasDinamicas?>"
																	style="color: red"
																>Nenhum Fornecedor Participante do Pregão</td>
															</tr>
															<!-- FIM Dados ITENS DA SOLICITAÇÃO DE MATERIAL  -->

											<?php

											}
											?>

											<?php

											if ($QuantidadeFornecedores > 0) {
												?>												
											
													<tr>
																<td
																	colspan="<?=(($TotalColunasFixas + $TotalColunasDinamicas) - 1)?>"
																	class="titulo3 itens_material menosum"
																	width="95%"
																>TOTAL DE FORNECEDORES: </td>
																
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
 	        <td class="textonormal" align="left">
				<input type="button" value="Voltar" class="botao" onclick="javascript:voltar();">
				<input type="submit" value="Aplicar Alterações" class="botao" onclick="javascript:enviar('MantemPrecoVencedor');">
				<input type="submit" value="Limpar Alterações" class="botao" onclick="javascript:enviar('LimparManterPrecoVencedor');">
				<input type="submit" value="Desfazer Última Rodada" class="botao" onclick="javascript:enviar('DesfazerUltimaRodada');">
				<input type="hidden" name="Botao" value="">			
          </td>
        </tr>
      </table>
	  
		  <?
		  if($PregaoFinalizado == 1)
		  {
		  ?>
			<tr>
				<td>
					* O Fornecedor destacado em 'AMARELO' é o Arrematante da Disputa.
				</td>
			</tr>
		  <?
		  }
		  ?>	  
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

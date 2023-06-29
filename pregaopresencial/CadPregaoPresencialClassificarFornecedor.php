<?php
# -------------------------------------------------------------------------
# Portal da DGCO teste
# Programa: CadPregaoPresencialClassificarFornecedor.php
# Autor:    Hélio Miranda
# Data:     29/07/2016
# Objetivo: Gestão da situação classificatória do Pregão Presencial
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
		$NCaracteresO        					= $_POST['NCaracteresO'];

		
}else{
		$Critica       							= $_GET['Critica'];
		$Mensagem      							= urldecode($_GET['Mensagem']);
		$Mens          							= $_GET['Mens'];
		$Tipo          							= $_GET['Tipo'];
		$_SESSION['CodFornecedorSelecionado']	= $_GET['CodFornecedorSelecionado'];
		$_SESSION['CodLoteSelecionado']			= $_GET['CodLoteSelecionado'];
		$_SESSION['CodSituacaoClassificacao']	= $_GET['CodSituacaoClassificacao'];
}

$db     = Conexao();


$sqlSolicitacoes = "SELECT		pc.epregcmoti, pc.cpresfsequ, pf.npregfrazs, pt.cpregtnuml, pt.epregtdesc
					FROM 		sfpc.tbpregaopresencialclassificacao pc,
								sfpc.tbpregaopresencialfornecedor pf,
								sfpc.tbpregaopresenciallote pt
					WHERE 		pc.cpregfsequ	= ".$_SESSION['CodFornecedorSelecionado']."
						AND		pc.cpregtsequ	=".$_SESSION['CodLoteSelecionado']."
						AND		pc.cpregfsequ	= pf.cpregfsequ
						AND		pc.cpregtsequ	= pt.cpregtsequ";

			
$result = $db->query($sqlSolicitacoes);
		
if( PEAR::isError($resultSoli) ){
	ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlSolicitacoes");
}

$Linha = $result->fetchRow();

$_SESSION['NumeroLoteClassificacao'] 			= $Linha[3];
$_SESSION['DescricaoLoteClassificacao'] 		= $Linha[4];
$_SESSION['RazaoSocialFornecedorClassificacao'] = $Linha[2];

if(($_POST['MotivoSituacao'] == null or $_POST['MotivoSituacao'] == "") and $_POST['CodSituacaoClassificacao'] <> 1)
{
	$_SESSION['MotivoSituacao'] = $Linha[0];
}

$TamanhoMaximoMotivo = 500;

# Identifica o Programa para Erro de Banco de Dados # 
$ErroPrograma = "CadPregaoPresencialClassificarFornecedor.php";

if($Critica == 1){
		# Critica dos Campos #
		$Mens     = 0;
		$Mensagem = "Informe: ";	
}

if ($_SESSION['Botao'] == "Limpar"){
	$_SESSION['Botao']					= null;
	$_SESSION['RazaoSocial']			= null;
	$_SESSION['CpfCnpj']				= null;
	$_SESSION['RepresentanteNome']		= null;
	$_SESSION['RepresentanteRG']		= null;		
	$_SESSION['RepresentanteOrgaoUF']	= null;
	$_SESSION['MotivoSituacao']	= null;
	
}	

if($_SESSION['Botao'] == "SalvarClassificacaoFornecedor")
{
	$_SESSION['Botao'] = null;
	$_SESSION['MotivoSituacao']			= strtoupper($_POST['MotivoSituacao']);
	$CodFornecedorSelecionado 			= $_SESSION['CodFornecedorSelecionado'];
	$CodLoteSelecionado					= $_SESSION['CodLoteSelecionado'];
	$MotivoSituacao 					= $_SESSION['MotivoSituacao'];
	$CodSituacaoClassificacao			= $_SESSION['CodSituacaoClassificacao'];
	$PreenchimentoCorreto 				= True;
	

	if($CodSituacaoClassificacao == null)
	{
		$PreenchimentoCorreto = False;
		
		$_SESSION['Mens'] = 1;
		$_SESSION['Tipo'] = 2;
		$_SESSION['Mensagem'] .= "- Campo obrigatório [<a href=\"javascript:document.CadPregaoPresencialClassificarFornecedor.CodSituacaoClassificacao.focus();\" class=\"titulo2\" style=\"color: red;\">Situação*</a>] não preenchido!<br />";			
	}
	
	else if($CodSituacaoClassificacao > 1 and $MotivoSituacao == "")
	{
		$PreenchimentoCorreto = False;
			
		$_SESSION['Mens'] = 1;
		$_SESSION['Tipo'] = 2;
		$_SESSION['Mensagem'] .= "- O Campo [<a href=\"javascript:document.CadPregaoPresencialClassificarFornecedor.MotivoSituacao.focus();\" class=\"titulo2\" style=\"color: red;\">Motivo*</a>] é obrigatório para situações classificatórias NEGATIVAS!<br />";			

	}
	
	//VALIDAR CPF/CNPJ

	if($PreenchimentoCorreto == True)
	{
		$db     = Conexao();
		

		$sqlSolicitacoes = "SELECT		cpresfsequ
							FROM 		sfpc.tbpregaopresencialclassificacao 
							WHERE 		cpregfsequ	= $CodFornecedorSelecionado
								AND		cpregtsequ	= $CodLoteSelecionado";

					
		$result = $db->query($sqlSolicitacoes);
				
		if( PEAR::isError($resultSoli) ){
			ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlSolicitacoes");
		}
		
		$Linha = $result->fetchRow();
		
		$intQuantidade = 0;
		
		$intQuantidade = $result->numRows();		
		
		
		if($intQuantidade > 0){	
		
		
			# Altera a Classificação#
			$sql  = "UPDATE 		sfpc.tbpregaopresencialclassificacao";
			$sql .= "	SET 		epregcmoti = '$MotivoSituacao', ";
			$sql .= "				cpresfsequ = $CodSituacaoClassificacao";
			$sql .= " 	WHERE 		cpregfsequ = $CodFornecedorSelecionado";
			$sql .= " 		AND 	cpregtsequ = $CodLoteSelecionado";
			
			$res  = $db->query($sql);
			
			
			if( PEAR::isError($res) ){
				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
			}  
			
			$_SESSION['Mens'] = 1;
			$_SESSION['Tipo'] = 1;
			$_SESSION['Mensagem'] .= "- Classificação do Fornecedor alterada com sucesso!";			
			
			$NumLote = $_SESSION['CodLoteSelecionado'];
			
			if($CodSituacaoClassificacao > 1)
			{
				//Revelida os lances...
				
				$sqlFornecedorVencedor = "SELECT 			lt.cpregfsequ
												FROM		sfpc.tbpregaopresenciallote lt
												WHERE		lt.cpregtsequ = $CodLoteSelecionado";
										
				$resFornecedorVencedor  = $db->query($sqlFornecedorVencedor);
				
				// echo "<pre>";
				// print_r($sqlFornecedorVencedor);
				// die();

				if( PEAR::isError($resFornecedorVencedor) ){
					ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
				}
				$LinhaFornecedorVencedor = $resFornecedorVencedor->fetchRow();
				$CodFornecedorVencedor 	= $LinhaFornecedorVencedor[0];
				
				if($_SESSION['PregaoTipo'] == 'N')
				{
					$TipoBusca 		= "MIN";
					$TipoOrdenacao 	= "ASC";
				}
				else
				{
					$TipoBusca 		= "MAX";
					$TipoOrdenacao 	= "DESC";
				}
				
				if($CodFornecedorVencedor > 0)
				{
					if($CodFornecedorVencedor == $CodFornecedorSelecionado)
					{
						//Busca 01
						//Verificação pelo maior oferta/Manter o preço (Lembrar);
						$sqlProximoFornecedor = "SELECT 			laB.cpregfsequ, laB.vpreglvall
														FROM		sfpc.tbpregaopresenciallance laB,
														sfpc.tbpregaopresencialclassificacao fc,
														sfpc.tbpregaopresencialprecoinicial pi
														WHERE		laB.cpregtsequ = $CodLoteSelecionado
																AND	laB.cpregfsequ = fc.cpregfsequ
																AND	fc.cpresfsequ  = 1
																AND laB.cpregfsequ NOT IN ($CodFornecedorVencedor)
																AND	laB.cpregpsequ = pi.cpregpsequ
																AND	pi.vpregpvali  > 0
																AND laB.vpreglvall = (SELECT $TipoBusca(laA.vpreglvall) FROM sfpc.tbpregaopresenciallance laA INNER JOIN sfpc.tbpregaopresencialclassificacao fcB ON laA.cpregfsequ = fcB.cpregfsequ AND laA.cpregtsequ = fcB.cpregtsequ AND fcB.cpresfsequ = 1 WHERE laA.cpregtsequ = $CodLoteSelecionado AND laA.vpreglvall > 0 AND laA.cpregfsequ NOT IN ($CodFornecedorVencedor))
														ORDER BY	laB.vpreglvall $TipoOrdenacao
														LIMIT		1";
												
						$resProximoFornecedor  = $db->query($sqlProximoFornecedor);
						
						// echo "<pre>";
						// print_r($sqlProximoFornecedor);
						// die();

						if( PEAR::isError($resProximoFornecedor) ){
							ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
						}
						$LinhaProximoFornecedor 		= $resProximoFornecedor->fetchRow();
						$CodProximoFornecedor			= $LinhaProximoFornecedor[0];
						$ValProximoFornecedor			= $LinhaProximoFornecedor[1];
						
						if($ValProximoFornecedor == "" or $ValProximoFornecedor == null)
						{
							$ValProximoFornecedor = 0;
						}
						
						if($CodProximoFornecedor > 0 and $ValProximoFornecedor > 0)
						{	
							$sql = "UPDATE sfpc.tbpregaopresenciallote SET cpregfsequ = $CodProximoFornecedor, cpreslsequ = 2, vpregtvalv = $ValProximoFornecedor WHERE cpregtsequ = $CodLoteSelecionado";
							$res = $db->query($sql);
							if( PEAR::isError($res) ){
								ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
							}

							$DescDesclassificacao = "O FORNECEDOR, PROVISORIAMENTE VENCEDOR, FOI DESCLASSIFICADO NA ANÁLISE DE DOCUMENTAÇÃO, APÓS A DISPUTA";
							
							$sql = "UPDATE sfpc.tbpregaopresenciallance SET fpregllven = 0, epregldesc = '$DescDesclassificacao' WHERE cpregtsequ = $CodLoteSelecionado AND cpregfsequ = $CodFornecedorVencedor AND fpreglurod = 1 AND fpregllven = 1";
							$res = $db->query($sql);
							if( PEAR::isError($res) ){
								ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
							}

							$Desc = "TORNOU-SE VENCEDOR PORQUE O FORNECEDOR ANTERIOR FOI DESCLASSIFICADO";
							
							$sql = "UPDATE sfpc.tbpregaopresenciallance SET fpregllven = 1, epregldesc = '$Desc' WHERE cpregtsequ = $CodLoteSelecionado AND cpregfsequ = $CodProximoFornecedor AND fpreglurod = 1 AND fpregllven = 0";
							$res = $db->query($sql);
							if( PEAR::isError($res) ){
								ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
							}							
						}
						else
						{
							//Busca 02
							//Verificação Preço Inicial
							$sqlProximoFornecedor = "SELECT 		laB.cpregfsequ, piA.vpregpvali
														FROM		sfpc.tbpregaopresenciallance laB,
														sfpc.tbpregaopresencialclassificacao fc,
														sfpc.tbpregaopresencialprecoinicial piA
														WHERE		laB.cpregtsequ = $CodLoteSelecionado
																AND	laB.cpregfsequ = fc.cpregfsequ
																AND	fc.cpresfsequ  = 1
																AND laB.cpregfsequ NOT IN ($CodFornecedorVencedor)
																AND	laB.cpregpsequ = piA.cpregpsequ
																AND laB.vpreglvall = 0
																AND piA.vpregpvali = (SELECT $TipoBusca(piB.vpregpvali) FROM sfpc.tbpregaopresenciallance laA INNER JOIN sfpc.tbpregaopresencialclassificacao fcB ON laA.cpregfsequ = fcB.cpregfsequ AND laA.cpregtsequ = fcB.cpregtsequ AND fcB.cpresfsequ = 1 INNER JOIN tbpregaopresencialprecoinicial piB ON laA.cpregpsequ = piB.cpregpsequ WHERE laA.cpregtsequ = $CodLoteSelecionado AND laA.vpreglvall = 0 AND piB.vpregpvali > 0 AND laA.cpregfsequ NOT IN ($CodFornecedorVencedor))
														ORDER BY	laB.vpreglvall $TipoOrdenacao
														LIMIT		1";
											
							$resProximoFornecedor  = $db->query($sqlProximoFornecedor);
							
							if( PEAR::isError($resProximoFornecedor) ){
								ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
							}
							$LinhaProximoFornecedor 		= $resProximoFornecedor->fetchRow();
							$CodProximoFornecedor			= $LinhaProximoFornecedor[0];
							$ValProximoFornecedor			= $LinhaProximoFornecedor[1];

							if($ValProximoFornecedor == "" or $ValProximoFornecedor == null)
							{
								$ValProximoFornecedor = 0;
							}							
							
							if($CodProximoFornecedor > 0 and $ValProximoFornecedor > 0)
							{	
								$sql = "UPDATE sfpc.tbpregaopresenciallote SET cpregfsequ = $CodProximoFornecedor, vpregtvalv = $ValProximoFornecedor, cpreslsequ = 2, vpregtvalr = 0.00 WHERE cpregtsequ = $CodLoteSelecionado";
								$res = $db->query($sql);								
							}
							else
							{
								$sql = "UPDATE sfpc.tbpregaopresenciallote SET cpreslsequ = 5 WHERE cpregtsequ = $CodLoteSelecionado";
								$res = $db->query($sql);								
							}
						}
					}
				}
			}
			else if ($CodSituacaoClassificacao == 1)
			{
				$sqlFornecedorVencedor = "SELECT 			lt.cpregfsequ
												FROM		sfpc.tbpregaopresenciallote lt
												WHERE		lt.cpregtsequ = $CodLoteSelecionado";
										
				$resFornecedorVencedor  = $db->query($sqlFornecedorVencedor);
				
				if( PEAR::isError($resFornecedorVencedor) ){
					ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
				}
				$LinhaFornecedorVencedor = $resFornecedorVencedor->fetchRow();
				$CodFornecedorVencedor 	= $LinhaFornecedorVencedor[0];
				
				if($_SESSION['PregaoTipo'] == 'N')
				{
					$TipoBusca 		= "MIN";
					$TipoOrdenacao 	= "ASC";
				}
				else
				{
					$TipoBusca 		= "MAX";
					$TipoOrdenacao 	= "DESC";
				}				
				
				if($CodFornecedorVencedor > 0)
				{
					if($CodFornecedorVencedor != $CodFornecedorSelecionado)
					{
							$sqlProximoFornecedor = "SELECT 			laB.cpregfsequ, laB.vpreglvall
															FROM		sfpc.tbpregaopresenciallance laB,
															sfpc.tbpregaopresencialclassificacao fc,
															sfpc.tbpregaopresencialprecoinicial pi
															WHERE		laB.cpregtsequ = $CodLoteSelecionado
																	AND	laB.cpregfsequ = fc.cpregfsequ
																	AND	fc.cpresfsequ  = 1
																	AND	laB.cpregpsequ = pi.cpregpsequ
																	AND	pi.vpregpvali  > 0
																	AND laB.vpreglvall = (SELECT $TipoBusca(laA.vpreglvall) FROM sfpc.tbpregaopresenciallance laA WHERE  laA.cpregtsequ = $CodLoteSelecionado AND laA.vpreglvall > 0 AND laA.cpregfsequ NOT IN ($CodFornecedorVencedor))																
															ORDER BY	laB.vpreglvall $TipoOrdenacao
															LIMIT		1";
													
							$resProximoFornecedor  = $db->query($sqlProximoFornecedor);
							
							if( PEAR::isError($resProximoFornecedor) ){
								ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
							}
							$LinhaProximoFornecedor 		= $resProximoFornecedor->fetchRow();
							$CodProximoFornecedor			= $LinhaProximoFornecedor[0];
							$ValProximoFornecedor			= $LinhaProximoFornecedor[1];

							if($CodProximoFornecedor > 0)
							{	
								$sql = "UPDATE sfpc.tbpregaopresenciallote SET cpregfsequ = $CodProximoFornecedor, vpregtvalv = $ValProximoFornecedor, cpreslsequ = 2, vpregtvalr = 0.00 WHERE cpregtsequ = $CodLoteSelecionado";
								$res = $db->query($sql);								
							}
							else
							{
								$sql = "UPDATE sfpc.tbpregaopresenciallote SET cpreslsequ = 5 WHERE cpregtsequ = $CodLoteSelecionado";
								$res = $db->query($sql);								
							}						
					}
				}
			}
			
			$NumLote 										= $_SESSION['CodLoteSelecionado'];
			
			$_SESSION['MotivoSituacao']						= null;
			$_SESSION['CodSituacaoClassificacao']			= null;
			$_SESSION['CodFornecedorSelecionado']			= null;
			$_SESSION['CodLoteSelecionado']					= null;
			$_SESSION['NumeroLoteClassificacao'] 			= null;
			$_SESSION['DescricaoLoteClassificacao'] 		= null;
			$_SESSION['RazaoSocialFornecedorClassificacao'] = null;			
			
			echo "<script>opener.document.CadPregaoPresencialSessaoPublica.Destino.value = 'D';</script>";	
			echo "<script>opener.document.CadPregaoPresencialSessaoPublica.CodLoteSelecionado.value = $NumLote;</script>";
			echo "<script>opener.document.CadPregaoPresencialSessaoPublica.Botao.value = 'SelecionarLote';</script>";
			echo "<script>opener.document.CadPregaoPresencialSessaoPublica.submit()</script>";
			echo "<script>self.close()</script>";			
		
		}
		else
		{
			$_SESSION['Mens'] = 1;
			$_SESSION['Tipo'] = 2;
			$_SESSION['Mensagem'] .= "- O Fornecedor não está associado ao Lote, sendo assim, não poderá classificá-lo!";	
		}						
		$db->disconnect();			
	}
}

?>
<html>
<head>
<title>Portal de Compras - Incluir Fornecedor</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<script language="javascript" type="">
<!--
function checktodos(){
	document.CadPregaoPresencialClassificarFornecedor.Subclasse.value = '';
	document.CadPregaoPresencialClassificarFornecedor.submit();
}
function enviar(valor){
	document.CadPregaoPresencialClassificarFornecedor.Botao.value = valor;
	document.CadPregaoPresencialClassificarFornecedor.submit();
}
function validapesquisa(){
	if( ( document.CadPregaoPresencialClassificarFornecedor.MaterialDescricaoDireta.value != '' ) || ( document.CadPregaoPresencialClassificarFornecedor.SubclasseDescricaoDireta.value != '') ) {
		if(document.CadPregaoPresencialClassificarFornecedor.Grupo){
			document.CadPregaoPresencialClassificarFornecedor.Grupo.value = '';
		}
		if(document.CadPregaoPresencialClassificarFornecedor.Classe){
			document.CadPregaoPresencialClassificarFornecedor.Classe.value = '';
		}
		document.CadPregaoPresencialClassificarFornecedor.Botao.value = 'Validar';
	}
	if(document.CadPregaoPresencialClassificarFornecedor.Subclasse){
		if(document.CadPregaoPresencialClassificarFornecedor.SubclasseDescricaoFamilia.value != "") {
			document.CadPregaoPresencialClassificarFornecedor.Subclasse.value = '';
		}
	}
	document.CadPregaoPresencialClassificarFornecedor.submit();
}
function AbreJanela(url,largura,altura) {
	window.open(url,'paginadetalhe','status=no,scrollbars=yes,left=45,top=150,width='+largura+',height='+altura);
}
function voltar(){
	self.close();
}
function remeter(){
	document.CadPregaoPresencialClassificarFornecedor.submit();
}

function ncaracteresO(valor){
	document.CadPregaoPresencialClassificarFornecedor.NCaracteresO.value = '' +  (document.CadPregaoPresencialClassificarFornecedor.TamanhoMaximoMotivo.value - document.CadPregaoPresencialClassificarFornecedor.MotivoSituacao.value.length);
	if( navigator.appName == 'Netscape' && valor ){ //Netscape Only
		document.CadPregaoPresencialClassificarFornecedor.NCaracteresO.focus();
	}
}
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">

</head>
<body background="../midia/bg.jpg" marginwidth="0" marginheight="0">
<form action="CadPregaoPresencialClassificarFornecedor.php" method="post" name="CadPregaoPresencialClassificarFornecedor">
<table cellpadding="3" border="0" summary="" width="100%">
	<!-- Erro -->
	<tr>
		<td>
			<?php 
				
				if( $_SESSION['Mens'] != 0 ){ 
				
					ExibeMens($_SESSION['Mensagem'],$_SESSION['Tipo'],1);
					if($_SESSION['CodSituacaoClassificacao'] <> null)
					{
						$_SESSION['Mens'] = null;
						$_SESSION['Tipo'] = null;
						$_SESSION['Mensagem'] = null;
					}
				
				}	
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
	        	CLASSIFICAÇÃO - FORNECEDOR
          </td>
        </tr>
        <tr>
          <td class="textonormal">
             <p align="justify">
             Para alterar a Situação Classificatória de um Fornecedor referente ao Lote, preencha todas as informações e clique no botão "Alplicar Situação".
             </p>
          </td>
        </tr>
        <?php
		
		
				# Pega a descrição do Perfil do usuário logado #
				if( $_SESSION['_cperficodi_'] != 2 and $_SESSION['_cperficodi_'] != 0 ){
						$db  = Conexao();
						$sqlusuario = "SELECT CPERFICODI, EPERFIDESC FROM SFPC.TBPERFIL ";
						$sqlusuario .= "WHERE CPERFICODI = ".$_SESSION['_cperficodi_']." ";
						$resultUsuario = $db->query($sqlusuario);
						if( PEAR::isError($result) ){
						    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlusuario");
						}else{
            		$PerfilUsuario = $resultUsuario->fetchRow();
            		$PerfilUsuarioDesc = $PerfilUsuario[1];
						}
				}
				?>
        <tr>
          <td>
            <table border="0" summary="" width="100%">
              <tr>
                <td class="textonormal" bgcolor="#FFFFFF">
					<table border="0" width="100%" summary="">
					  <tr>
							<td align="left" bgcolor="#DCEDF7" class="textonormal" colspan="1" style="font-weight: bold;">Nº Lote: </td>
							<td align="left" class="textonormal" colspan="3" >
							  <label><?php echo $_SESSION['NumeroLoteClassificacao']; ?></label>
							</td>							
					  </tr>
					  
					  <tr>
							<td align="left" bgcolor="#DCEDF7" class="textonormal" colspan="1" style="font-weight: bold;">Descrição Lote: </td>
							<td align="left" class="textonormal" colspan="3" >
							  <label><?php echo $_SESSION['DescricaoLoteClassificacao']; ?></label>
							</td>							
					  </tr>					  
					  
					  <tr>
							<td align="left" bgcolor="#DCEDF7" class="textonormal" colspan="1" style="font-weight: bold;">Fornecedor: </td>
							<td align="left" class="textonormal" colspan="3" >
							  <label><?php echo $_SESSION['RazaoSocialFornecedorClassificacao']; ?></label>
							</td>							
					  </tr>
					  
					  <tr>
						<td align="left" bgcolor="#DCEDF7" class="textonormal" colspan="1" style="font-weight: bold;">Situação:*</td>
						<td align="left" class="textonormal" colspan="3" >
							  <select name="CodSituacaoClassificacao" class="textonormal">
								<!-- Mostra as licitações cadastradas -->
								<?php
													$db     = Conexao();
													
													$CodLoteSelecionado = $_SESSION['CodLoteSelecionado'];
													
													if($CodLoteSelecionado > 0)
													{
														$sqlFornecedorVencedor = "SELECT 			lt.cpregfsequ
																						FROM		sfpc.tbpregaopresenciallote lt
																						WHERE		lt.cpregtsequ = $CodLoteSelecionado";
																				
														$resFornecedorVencedor  = $db->query($sqlFornecedorVencedor);
														
														if( PEAR::isError($resFornecedorVencedor) ){
															ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
														}
														
														$LinhaFornecedorVencedor = $resFornecedorVencedor->fetchRow();
														$CodFornecedorVencedor 	= $LinhaFornecedorVencedor[0];

														if($CodFornecedorVencedor > 0)
														{
															$CondicaoClassificacao = " WHERE sf.cpresfsequ NOT IN (2)";
														}
														else
														{
															$CondicaoClassificacao = " WHERE sf.cpresfsequ NOT IN (3, 4)";
														}
														
														
														$sql    = "SELECT			sf.cpresfsequ, sf.epresfnome ";
														$sql   .= "		FROM 		sfpc.tbpregaopresencialsituacaofornecedor sf";
														$sql   .= $CondicaoClassificacao;
														$sql   .= "  	ORDER BY 	sf.epresfnome";
														
														print " - ".$sql;
														
														$result = $db->query($sql);
														if( PEAR::isError($result) ){
															ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
														}else{
																$ComissaoCodigoAnt = "";
																while( $Linha = $result->fetchRow() ){
																	
																	echo "<option style=color:".(($Linha[0] == 1) ? "blue" : "red")." value=\"$Linha[0]\" ".(($Linha[0] == $_SESSION['CodSituacaoClassificacao']) ? ("selected") : ("")).">$Linha[1]"."</option>\n" ;
																}
														}
													}
													$db->disconnect();
													
													?>
							  </select>								
						</td>
					  </tr>
					  
					  <tr>
						<td align="left" bgcolor="#DCEDF7" class="textonormal" colspan="1" style="font-weight: bold;">Motivo:*</td>
						<td align="left" class="textonormal" colspan="3" >
							<input type="hidden" name="TamanhoMaximoMotivo" value="<?=$TamanhoMaximoMotivo?>" /> 
							
							<textarea
								name="MotivoSituacao"
								cols="60"
								rows="6"
								maxlength="500"
								OnKeyUp="javascript:ncaracteresO(1)"
								OnBlur="javascript:ncaracteresO(0)"
								OnSelect="javascript:ncaracteresO(1)"
								class="textonormal"
								style="text-transform: uppercase;"><?= $_SESSION['MotivoSituacao'] ?></textarea>							
							
							<br /> 
							
							<font class="textonormal">máximo de <?=$TamanhoMaximoMotivo?> caracteres (Restantes: </font>
							
							<input
								disabled
								type="text"
								name="NCaracteresO"
								OnFocus="javascript:document.CadPregaoPresencialClassificarFornecedor.MotivoSituacao.focus();"
								size="3"
								value="<?php echo $NCaracteresO ?>"
								class="textonormal" />							
							)
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
          	<input type="submit" value="Salvar Classificação" class="botao" onclick="javascript:enviar('SalvarClassificacaoFornecedor');">
			<input type="button" value="Limpar Dados" class="botao" onclick="javascript:enviar('Limpar');">
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

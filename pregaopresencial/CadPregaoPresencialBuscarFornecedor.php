<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadPregaoPresencialBuscarFornecedor.php
# Autor:    Roberta Costa
# Data:     23/08/2016
# Objetivo: Inclusão, no Pregão Presencial, de Fornecedores já cadastrados 
#           no Portal de Compras.
#-------------------------------------------------------------------------
# Alterado: Pitang Agile Ti - Caio coutinho
# Data:		23/01/2019
# Objetivo: Tarefa Redmine 208468
# -------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/fornecedores/CadGestaoFornecedor.php' );
AddMenuAcesso( '/fornecedores/CadGestaoFornecedorExcluido.php' );
AddMenuAcesso( '/fornecedores/CadGestaoFornecedorIncluir.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST"){
		$Botao        	= $_POST['Botao'];
		$ItemPesquisa 	= $_POST['ItemPesquisa'];
		$Argumento			= strtoupper2(trim($_POST['Argumento']));
		$Palavra				= $_POST['Palavra'];
		
		$_SESSION['RepresentanteNome']		= $_POST['RepresentanteNome'];
		$_SESSION['RepresentanteRG']		= $_POST['RepresentanteRG'];
		$_SESSION['RepresentanteOrgaoUF']	= $_POST['RepresentanteOrgaoUF'];
		$_SESSION['CodFornecedor']			= $_POST['CodFornecedor'];
}else{
		$Mens     = $_GET['Mens'];
		$Mensagem = $_GET['Mensagem'];
		$Tipo			= $_GET['Tipo'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

if( $Botao == "Limpar" ){
	  header("location: CadPregaoPresencialBuscarFornecedor.php");
	  exit;
}

if( $Botao == "Pesquisar" ){
		$Mens				= 0;
		$Mensagem 	= "Informe: ";
		if( $Argumento != "" ){
				if( ($ItemPesquisa == "CNPJ") and (!SoNumeros($Argumento)) ){
			    	$Mens 		 = 1;$Tipo = 2;
						$Mensagem .= "<a href=\"javascript:document.CadPregaoPresencialBuscarFornecedor.Argumento.focus();\" class=\"titulo2\">CNPJ Válido</a>";
				}else{
						if( ($ItemPesquisa == "CPF") and (!SoNumeros($Argumento)) ){
					    	$Mens 		 = 1;$Tipo = 2;
								$Mensagem .= "<a href=\"javascript:document.CadPregaoPresencialBuscarFornecedor.Argumento.focus();\" class=\"titulo2\">CPF Válido</a>";
						}
				}
		}else{
		  	$Mens 		 = 1;
		  	$Tipo      = 2;
			$Mensagem .= "<a href=\"javascript:document.CadPregaoPresencialBuscarFornecedor.Argumento.focus();\" class=\"titulo2\">Argumento da Pesquisa</a>";
		}
}

if($Botao == "IncluirFornecedor")
{
	if($_SESSION['CodFornecedor'] != "")
	{
		$CodFornecedor = $_SESSION['CodFornecedor'];
		
		$db     = Conexao();
		
		$sqlSolicitacoes = "SELECT		aforcrccgc, aforcrccpf, nforcrrazs, fforcrmepp
							FROM 		sfpc.tbfornecedorcredenciado 
							WHERE 		aforcrsequ	= $CodFornecedor";
					
		$result = $db->query($sqlSolicitacoes);
			
		if( PEAR::isError($result) ){
			ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlSolicitacoes");
		}
		
		$Linha = $result->fetchRow();	
		
		$Encoding = 'UTF-8';
		
		$RazaoSocial 				= mb_strtoupper ($Linha[2], $Encoding);
		$CpfCnpj 					= ($Linha[0] == "" ? $Linha[1] : $Linha[0]);
		$RepresentanteNome 			= mb_strtoupper ( $_SESSION['RepresentanteNome'], $Encoding);
		$RepresentanteRG 			= mb_strtoupper ( $_SESSION['RepresentanteRG'], $Encoding);	
		$RepresentanteOrgaoUF 		= mb_strtoupper ( $_SESSION['RepresentanteOrgaoUF'], $Encoding);
		$TipoFornecedor				= ((($Linha[3] == 0) or ($Linha[3] == '') or ($Linha[3] == null)) ? 0 : $Linha[3]);
		$PregaoCod 					= $_SESSION['PregaoCod'];
		$TipoCnpjCpf				= $_SESSION['TipoCnpjCpf'];
		$PreenchimentoCorreto 		= True;
		$SemRepresentante			= True;
		
		
		if($RepresentanteNome <> '')
		{		
			$SemRepresentante = False;
			
			if($RepresentanteRG == '')
			{
				$db->disconnect();
				$PreenchimentoCorreto = False;
				
				$_SESSION['Mens'] = 1;
				$_SESSION['Tipo'] = 2;
				$_SESSION['Mensagem'] .= "- Campo obrigatório [<a href=\"javascript:document.CadPregaoPresencialBuscarFornecedor.RepresentanteRG.focus();\" class=\"titulo2\" style=\"color: red;\">Repr. R.G.*</a>] não preenchido!<br />";			
			}
			
			if($RepresentanteOrgaoUF == '')
			{
				$db->disconnect();
				$PreenchimentoCorreto = False;
				
				$_SESSION['Mens'] = 1;
				$_SESSION['Tipo'] = 2;
				$_SESSION['Mensagem'] .= "- Campo obrigatório [<a href=\"javascript:document.CadPregaoPresencialBuscarFornecedor.RepresentanteOrgaoUF.focus();\" class=\"titulo2\" style=\"color: red;\">Órgão Emissor/UF *</a>] não preenchido!<br />";			
			}	
		}
		else
		{	
			$SemRepresentante = True;

			$_SESSION['Mens'] = 1;
			$_SESSION['Tipo'] = 2;
			$_SESSION['Mensagem'] .= "- Fornecedor SEM REPRESENTANTE. Não poderá dar lances!<br />";
		}		
	}
	else
	{	
		$PreenchimentoCorreto = False;
		
		$_SESSION['Mens'] = 1;
		$_SESSION['Tipo'] = 2;
		$_SESSION['Mensagem'] .= "- Nenhum Fornecedor está selecionado!";
	}
	
	//VALIDAR CPF/CNPJ

	if($PreenchimentoCorreto == True)
	{	
		if(strlen($CpfCnpj)  == 14)
		{
			$sqlSolicitacoes = "SELECT		cpregfsequ
								FROM 		sfpc.tbpregaopresencialfornecedor 
								WHERE 		apregfccgc	= '$CpfCnpj'
									AND		cpregasequ	= $PregaoCod";

						
			$result = $db->query($sqlSolicitacoes);
				
				if( PEAR::isError($resultSoli) ){
					ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlSolicitacoes");
				}
				
				$Linha = $result->fetchRow();
				
				$intQuantidade = 0;
				
				$intQuantidade = $result->numRows();		
				
				
				if($intQuantidade == 0){
					$sql = "SELECT MAX(cpregfsequ) FROM sfpc.tbpregaopresencialfornecedor";
					$res = $db->query($sql);
					
					if (PEAR::isError($res)) {
						ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
					}else{
							$Linha  = $res->fetchRow();
							$Codigo = $Linha[0] + 1;
					}			
				
				
					# Insere Fornecedor #
					$sql  = "INSERT INTO sfpc.tbpregaopresencialfornecedor( ";
					$sql .= "cpregfsequ, cpregasequ, fpregfmepp, apregfccgc, apregfccpf, npregfrazs, npregfnomr, apregfnurg, npregforgu, epregfsitu, ";
					$sql .= "dpregfcada, ";
					$sql .= "tpregfulat ";
					$sql .= " ) VALUES ( ";
					$sql .= "$Codigo, $PregaoCod, $TipoFornecedor, '$CpfCnpj', '', '$RazaoSocial', '$RepresentanteNome', '$RepresentanteRG', '$RepresentanteOrgaoUF','".($SemRepresentante ? 'S' : 'C')."',";
					$sql .= "'".date("Y-m-d")."', ";
					$sql .= "'".date("Y-m-d H:i:s")."' )";
					
					$res  = $db->query($sql);
					
					
					if( PEAR::isError($res) ){
						ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
					}  
					
					$_SESSION['Mens'] = 1;
					$_SESSION['Tipo'] = 1;
					$_SESSION['Mensagem'] .= "- Fornecedor incluído com sucesso";	
					
					//Início Classificação e Preço Inicial
					
						$sqlLote    = "SELECT 		 	pl.cpregtsequ ";
						$sqlLote   .= "  FROM 		   	sfpc.tbpregaopresenciallote pl";
						$sqlLote   .= "  WHERE 			pl.cpregasequ = ".$_SESSION['PregaoCod']." ";
						$sqlLote   .= "  ORDER BY 		pl.cpregtnuml";
						
						$result = $db->query($sqlLote);
						$Linha = $result->fetchRow();
						$intQuantidade = $result->numRows();
						
						if($intQuantidade > 0)
						{
							for ($itr = 0; $itr < $intQuantidade; ++ $itr) 
							{						
								# Insere Classificação #
								$sql  = "INSERT INTO sfpc.tbpregaopresencialclassificacao( ";
								$sql .= "cpregfsequ, cpregtsequ, cpresfsequ, cusupocodi, epregcmoti,";
								$sql .= "dpregccada, ";
								$sql .= "tpregculat ";
								$sql .= " ) VALUES ( ";
								$sql .= "$Codigo, $Linha[0], 1, ".$_SESSION['_cusupocodi_'].", '',";
								$sql .= "'".date("Y-m-d")."', ";
								$sql .= "'".date("Y-m-d H:i:s")."' )";
								
								$res  	= $db->query($sql);	

								if (PEAR::isError($res)) {
									ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
								}								
								
								#Recebe o último código de Preço Inicial#
								$sql = "SELECT MAX(cpregpsequ) FROM sfpc.tbpregaopresencialprecoinicial";
								$res = $db->query($sql);
								
								if (PEAR::isError($res)) {
									ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
								}else{
										$LinhaPrecoInicial  			= $res->fetchRow();
										$CodigoPrecoInicial 			= $LinhaPrecoInicial[0] + 1;
								}							
								
								#Insere Preço Inicial#
								$sql  = "INSERT INTO sfpc.tbpregaopresencialprecoinicial( ";
								$sql .= "cpregpsequ, cpregfsequ, cpregtsequ, vpregpvali, fpregpalan, cpregpoemp, ";
								$sql .= "dpregpcada, ";
								$sql .= "tpregpulat ";
								$sql .= " ) VALUES ( ";
								$sql .= "$CodigoPrecoInicial, $Codigo, $Linha[0], 0.00, 0, 0,";
								$sql .= "'".date("Y-m-d")."', ";
								$sql .= "'".date("Y-m-d H:i:s")."' )";
								
								$res  = $db->query($sql);							
								
								if (PEAR::isError($res)) {
									ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
								}								
								
								#Avança uma linha nas buscas#
								$Linha = $result->fetchRow();
									
								$sql = "UPDATE sfpc.tbpregaopresencialprecoinicial SET fpregpalan = 0, vpregpvali = 0.00 WHERE cpregfsequ IN (SELECT DISTINCT cpregfsequ FROM sfpc.tbpregaopresencialfornecedor fr, sfpc.tbpregaopresencial pp WHERE pp.cpregasequ = fr.cpregasequ AND pp.cpregasequ = $PregaoCod)";
								$res = $db->query($sql);
								if( PEAR::isError($res) ){
									ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlSolicitacoes");
								}									
							}
						}					
					
					//Fim Classificação e Preço Inicial

					$_SESSION['Botao']					= null;
					$_SESSION['RazaoSocial']			= null;
					$_SESSION['CpfCnpj']				= null;
					$_SESSION['RepresentanteNome']		= null;
					$_SESSION['RepresentanteRG']		= null;	
					$_SESSION['RepresentanteOrgaoUF']	= null;	

					echo "<script>opener.document.CadPregaoPresencialSessaoPublica.Destino.value = 'B';</script>";	
					echo "<script>opener.document.CadPregaoPresencialSessaoPublica.submit()</script>";					
				}
				else
				{
					$_SESSION['Mens'] = 1;
					$_SESSION['Tipo'] = 2;
					$_SESSION['Mensagem'] .= "O Fornecedor [Pessoa Jurícica] já está vinculado ao Pregão Presencial selecionado";	
				}
				
				$db->disconnect();							
		}
		else if(strlen($CpfCnpj)  == 11)
		{
			$sqlSolicitacoes = "SELECT		cpregfsequ
								FROM 		sfpc.tbpregaopresencialfornecedor
								WHERE 		apregfccpf	= '$CpfCnpj'
									AND		cpregasequ	= $PregaoCod";

			$result = $db->query($sqlSolicitacoes);
				
				if( PEAR::isError($resultSoli) ){
					ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlSolicitacoes");
				}
				
				$Linha = $result->fetchRow();
				
				$intQuantidade = 0;
				
				$intQuantidade = $result->numRows();		
				
				
				if($intQuantidade == 0){
					$sql = "SELECT MAX(cpregfsequ) FROM sfpc.tbpregaopresencialfornecedor";
					$res = $db->query($sql);
					
					if (PEAR::isError($res)) {
						ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
					}else{
							$Linha  = $res->fetchRow();
							$Codigo = $Linha[0] + 1;
					}			
				
				
					# Insere Fornecedor #
					$sql  = "INSERT INTO sfpc.tbpregaopresencialfornecedor( ";
					$sql .= "cpregfsequ, cpregasequ, fpregfmepp, apregfccgc, apregfccpf, npregfrazs, npregfnomr, apregfnurg, npregforgu, epregfsitu, ";
					$sql .= "dpregfcada, ";
					$sql .= "tpregfulat ";
					$sql .= " ) VALUES ( ";
					$sql .= "$Codigo, $PregaoCod, $TipoFornecedor, '', '$CpfCnpj', '$RazaoSocial', '$RepresentanteNome', '$RepresentanteRG', '$RepresentanteOrgaoUF', 'C',";
					$sql .= "'".date("Y-m-d")."', ";
					$sql .= "'".date("Y-m-d H:i:s")."' )";
					
					
					$res  = $db->query($sql);
					
					
					if( PEAR::isError($res) ){
						ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
					}  
					
					$_SESSION['Mens'] = 1;
					$_SESSION['Tipo'] = 1;
					$_SESSION['Mensagem'] .= "Fornecedor incluído com sucesso";	
					
					
					
					
					//Início Classificação e Preço Inicial
					
						$sqlLote    = "SELECT 		 	pl.cpregtsequ ";
						$sqlLote   .= "  FROM 		   	sfpc.tbpregaopresenciallote pl";
						$sqlLote   .= "  WHERE 			pl.cpregasequ = ".$_SESSION['PregaoCod']." ";
						$sqlLote   .= "  ORDER BY 		pl.cpregtnuml";
						
						$result = $db->query($sqlLote);
						$Linha = $result->fetchRow();
						$intQuantidade = $result->numRows();
						
						if($intQuantidade > 0)
						{
							for ($itr = 0; $itr < $intQuantidade; ++ $itr) 
							{						
								# Insere Classificação #
								$sql  = "INSERT INTO sfpc.tbpregaopresencialclassificacao( ";
								$sql .= "cpregfsequ, cpregtsequ, cpresfsequ, cusupocodi, epregcmoti,";
								$sql .= "dpregccada, ";
								$sql .= "tpregculat ";
								$sql .= " ) VALUES ( ";
								$sql .= "$Codigo, $Linha[0], 1, ".$_SESSION['_cusupocodi_'].", '',";
								$sql .= "'".date("Y-m-d")."', ";
								$sql .= "'".date("Y-m-d H:i:s")."' )";
								
								$res  	= $db->query($sql);	

								if (PEAR::isError($res)) {
									ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
								}								
								
								#Recebe o último código de Preço Inicial#
								$sql = "SELECT MAX(cpregpsequ) FROM sfpc.tbpregaopresencialprecoinicial";
								$res = $db->query($sql);
								
								if (PEAR::isError($res)) {
									ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
								}else{
										$LinhaPrecoInicial  			= $res->fetchRow();
										$CodigoPrecoInicial 			= $LinhaPrecoInicial[0] + 1;
								}							
								
								#Insere Preço Inicial#
								$sql  = "INSERT INTO sfpc.tbpregaopresencialprecoinicial( ";
								$sql .= "cpregpsequ, cpregfsequ, cpregtsequ, vpregpvali, fpregpalan, cpregpoemp, ";
								$sql .= "dpregpcada, ";
								$sql .= "tpregpulat ";
								$sql .= " ) VALUES ( ";
								$sql .= "$CodigoPrecoInicial, $Codigo, $Linha[0], 0.00, 0, 0,";
								$sql .= "'".date("Y-m-d")."', ";
								$sql .= "'".date("Y-m-d H:i:s")."' )";
								
								$res  = $db->query($sql);							
								
								if (PEAR::isError($res)) {
									ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
								}								
								
								#Avança uma linha nas buscas#
								$Linha = $result->fetchRow();
								
								$sql = "UPDATE sfpc.tbpregaopresencialprecoinicial SET fpregpalan = 0, vpregpvali = 0.00 WHERE cpregfsequ IN (SELECT DISTINCT cpregfsequ FROM tbpregaopresencialfornecedor fr, tbpregaopresencial pp WHERE pp.cpregasequ = fr.cpregasequ AND pp.cpregasequ = $PregaoCod)";
								$res = $db->query($sql);
								if( PEAR::isError($res) ){
									ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlSolicitacoes");
								}									
							}
						}					
					
					//Fim Classificação e Preço Inicial					
					
					

					$_SESSION['Botao']					= null;
					$_SESSION['RazaoSocial']			= null;
					$_SESSION['CpfCnpj']				= null;
					$_SESSION['RepresentanteNome']		= null;
					$_SESSION['RepresentanteRG']		= null;
					$_SESSION['RepresentanteOrgaoUF']	= null;


					echo "<script>opener.document.CadPregaoPresencialSessaoPublica.Destino.value = 'B';</script>";	
					echo "<script>opener.document.CadPregaoPresencialSessaoPublica.submit()</script>";				
				}
				else
				{
					$_SESSION['Mens'] = 1;
					$_SESSION['Tipo'] = 2;
					$_SESSION['Mensagem'] .= "O Fornecedor [Pessoa Física] já está vinculado ao Pregão Presencial selecionado";	
				}
		}
		else
		{
					$_SESSION['Mens'] = 1;
					$_SESSION['Tipo'] = 2;
					$_SESSION['Mensagem'] .= "Erro";	
		}
	}
}


?>


<html>
<script language="javascript" type="">
<!--
	function enviar(valor){
		document.CadPregaoPresencialBuscarFornecedor.Botao.value=valor;
		document.CadPregaoPresencialBuscarFornecedor.submit();
	}

	function Submete(Destino) {
	 	document.CadPregaoPresencialBuscarFornecedor.Destino.value = Destino;
	 	document.CadPregaoPresencialBuscarFornecedor.submit();
	}

	function AbreJanela(url,largura,altura) {
		window.open(url,'pagina','status=no,scrollbars=no,left=20,top=150,width='+largura+',height='+altura);
	}
	function enviarDestino(valor, Destino){
		document.CadPregaoPresencialBuscarFornecedor.Destino.value = Destino;
		document.CadPregaoPresencialBuscarFornecedor.Botao.value = valor;
		document.CadPregaoPresencialBuscarFornecedor.submit();
	}
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="CadPregaoPresencialBuscarFornecedor.php" method="post" name="CadPregaoPresencialBuscarFornecedor">
<table cellpadding="3" border="0" summary="">

	<!-- Erro -->
	<tr>
	  <td align="left" colspan="2">
			<?php 
				if( $_SESSION['Mens'] != 0 ){ ExibeMens($_SESSION['Mensagem'],$_SESSION['Tipo'],1);}
				
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
      <table  border="0" cellspacing="0" cellpadding="3" bgcolor="#ffffff" summary="">
        <tr>
	      	<td class="textonormal">
	        	<table border="1" cellpadding="3" cellspacing="0"  bordercolor="#75ADE6" summary="" class="textonormal" width="100%">
	          	<tr>
	            	<td align="center" bgcolor="#75ADE6" valign="middle" colspan="4" class="titulo3">
		    					PREGÃO PRESENCIAL - BUSCA DE FORNECEDORES
		          	</td>
		        	</tr>
	  	      	<tr>
	    	      	<td class="textonormal" colspan="4">
	      	    		<p align="justify">
	        	    		-Selecione o item de pesquisa desejado, preencha o argumento da pesquisa e
	        	    		clique no botão "Pesquisar". Depois, marque o fornecedor desejado, e, para
							incluí-lo ao Pregão Presencial, clique no Botão "Incluir Fornecedor".<br /><br />
	        	    		-Para limpar a pesquisa, clique no botão "Limpar".<br /><br />
	        	    		-Para incluir um Fornecedor ainda não cadastrado, clique no botão "Incluir".
	          	   	</p>
	          		</td>
	          	</tr>
		        	<tr>
    	      		<td class="textonormal" colspan="4">
						<table border="0" cellpadding="0" cellspacing="2" summary="" class="textonormal" width="100%">
				  	      	<tr>
			    	      		<td class="textonormal" bgcolor="#DCEDF7" width="30%">Pesquisa*</td>
			    	      		<td class="textonormal">
			 									<select name="ItemPesquisa" class="textonormal">
									  			<option value="RAZAO" <?php if( $ItemPesquisa == "RAZAO" or $ItemPesquisa == "" ){ echo "selected"; }?> >Razão Social/Nome
									  			<option value="CNPJ" <?php if( $ItemPesquisa == "CNPJ" ){ echo "selected"; }?> >CNPJ
									  			<option value="CPF" <?php if( $ItemPesquisa == "CPF" ){ echo "selected"; }?> >CPF
												</select>
											</td>
			        	    </tr>
					        	<tr>
			  	      			<td class="textonormal" bgcolor="#DCEDF7">Argumento*</td>
			    	      		<td class="textonormal">
			      	    			<input type="text" class="textonormal" name="Argumento" size="40" maxlength="60" value="<?php echo $Argumento;?>">
												<input type="checkbox" class="textonormal" name="Palavra" value="1" <?php if( $Palavra == 1 ){ echo "checked";}?>> Palavra Exata
											</td>
			        	    </tr>
									</table>
								</td>
	        		</tr>
      	      <tr>
    	      		<td align="right" colspan="4">
  	      				<input type="button" value="Pesquisar" class="botao" onclick="javascript:enviar('Pesquisar');">
  	      				
						<input type="button" value="Limpar" class="botao" onclick="javascript:enviar('Limpar');">
						
						<input type="button" value="Cadastrar Novo Fornecedor" class="botao" 
						onclick="javascript:AbreJanela('../pregaopresencial/CadPregaoPresencialIncluirFornecedor.php?ProgramaOrigem=CadPregaoPresencialSessaoPublica&amp;PesqApenas=C', 900, 350);">
            			
						<input type="hidden" name="Botao" value="">
								</td>
        			</tr>
		        	<?php
							if( $Botao == "Pesquisar" and $Mens == 0 ){
									$Argumento = strtoupper2($Argumento);
									# Busca os Dados da Tabela de Fornecedor de Acordo com o argumento da pesquisa #
									$db	  = Conexao();
									$sql  = "SELECT A.DFORCRGERA, A.AFORCRSEQU, A.AFORCRCCGC, A.AFORCRCCPF, ";
									$sql .= "        A.NFORCRRAZS, B.CFORTSCODI, B.DFORSISITU, A.fforcrmepp ";
									$sql .= "  FROM SFPC.TBFORNECEDORCREDENCIADO A, SFPC.TBFORNSITUACAO B ";
									$sql .= " WHERE A.AFORCRSEQU = B.AFORCRSEQU AND ";
									if( $Palavra == 1 ){ //palavra exata
											if( $ItemPesquisa == "CNPJ" ){
							   					$sql .= "A.AFORCRCCGC  LIKE '%$Argumento' ";
											}elseif( $ItemPesquisa == "CPF" ){
								   				$sql .= "A.AFORCRCCPF LIKE '%$Argumento' ";
											}elseif( $ItemPesquisa == "RAZAO" ){
												$sql .= "
													( A.NFORCRRAZS LIKE '$Argumento %'
													OR A.NFORCRRAZS LIKE '% $Argumento %'
													OR A.NFORCRRAZS LIKE '% $Argumento' )
												";
						   						//$sql .= SQL_ExpReg("A.NFORCRRAZS",$Argumento)." ";
											}
									}else{
											if( $ItemPesquisa == "CNPJ" ){
							   					$sql .= "A.AFORCRCCGC LIKE '%$Argumento' ";
											}elseif( $ItemPesquisa == "CPF" ){
								   				$sql .= "A.AFORCRCCPF LIKE '%$Argumento' ";
											}elseif( $ItemPesquisa == "RAZAO" ){
									   			$sql .= "A.NFORCRRAZS LIKE '%$Argumento%' ";
											}
									}
									$sql .= "	      AND B.CFORTSCODI = ( SELECT SIT1.CFORTSCODI FROM SFPC.TBFORNSITUACAO SIT1 ";
									$sql .= "       WHERE A.AFORCRSEQU = SIT1.AFORCRSEQU AND SIT1.TFORSIULAT = ";
									$sql .= "							(SELECT MAX(TFORSIULAT) FROM SFPC.TBFORNSITUACAO SIT2 ";
									$sql .= "              WHERE A.AFORCRSEQU = SIT2.AFORCRSEQU) )";
						 			$sql .= "ORDER BY A.NFORCRRAZS ASC, B.DFORSISITU DESC";
						 			$result 	= $db->query($sql);
									if( PEAR::isError($result) ){
							    		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
									}else{
											$Qtd = $result->numRows();
											echo "			<tr>\n";
											echo "				<td align=\"center\" bgcolor=\"#DCEDF7\" colspan=\"4\" class=\"titulo3\">RESULTADO DA PESQUISA</td>\n";
											echo "			</tr>\n";
											if( $Qtd > 0 ){
													echo "		<tr>\n";
													if( $ItemPesquisa == "CNPJ" ){
															echo "	<td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"24%\" align=\"center\">CNPJ</td>\n";
													}elseif( $ItemPesquisa == "CPF" ){
															echo "	<td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"24%\" align=\"center\">CPF</td>\n";
													}else{
															echo "	<td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"24%\" align=\"center\">CNPJ/CPF</td>\n";
													}
													echo "			<td class=\"titulo3\" bgcolor=\"#F7F7F7\" align=\"center\">RAZÃO/NOME</td>\n";
													echo "			<td class=\"titulo3\" bgcolor=\"#F7F7F7\" align=\"center\">TIPO DE EMPRESA</td>\n";
													echo "			<td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"20%\" align=\"center\">DATA INSCRIÇÃO</td>\n";
													echo "		</tr>\n";
													$Sequencial	     = "";
													$SequencialAntes = "";
													while( $Linha	= $result->fetchRow() ){
															$DataInscricao 		= substr($Linha[0],8,2)."/".substr($Linha[0],5,2)."/".substr($Linha[0],0,4);
															$Sequencial	   		= $Linha[1];
															$CNPJ    			= $Linha[2];
															$CPF				= $Linha[3];
															$Razao         		= $Linha[4];
															$Situacao     		= $Linha[5];
															
															$TipoEmpresaOrigem	= (($Linha[7] == 0 or $Linha[7] == '' or $Linha[7] == null) ? 0 : $Linha[7]); 
															
															//Tipo de Empresa
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
															
															if( $Sequencial != $SequencialAntes ){
																	echo "	<tr>\n";
																	if( $CNPJ <> 0 ){
									             				$CNPJForm = FormataCNPJ($CNPJ);
																	}else{
									             				$CPFForm = FormataCPF($CPF);
																	}


																	if( $CNPJ <> 0 )
																	{
																		echo "	<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\"><input name=\"CodFornecedor\" value=\"$Sequencial\" type=\"radio\" /><font color=\"#000000\">$CNPJForm</font></td>\n";
																	}
																	else
																	{
																		echo "	<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\"><input name=\"CodFornecedor\" value=\"$Sequencial\" type=\"radio\" /><font color=\"#000000\">$CPFForm</font></td>\n";
																	}
																	echo "		<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\">$Razao</td>\n";
																	echo "		<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" title=\"$DescTipoEmpresa\" style=\"cursor: help\" align=\"center\">$TipoEmpresa</td>\n";
																	echo "		<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" align=\"center\">$DataInscricao</td>\n";
																	$SequencialAntes = $Sequencial;
															}
													}
													
													echo "</tr>\n";
													echo "</table>";
													echo "<br /><br /><br /><br />";
													
													echo "<div style=\"width:100%; height: 25px; position:fixed; bottom: 0px; left: 0px; background-color: #BFDAF2; box-shadow: 0 -4px 8px 0 rgba(0, 0, 0, 0.4); padding: 5px 5px 5px 5px; border-top: 1px solid white;\"> \n";
													
													echo "	<table width=\"100%\">";
													echo "		<tr>";
													echo "			<td class=\"textonormal\" style=\"font-weight: bold; align: left; \">";
													echo "				Repr. Nome: ";
													echo "			</td>";
													
													echo "			<td>";
													echo "				<input type=\"text\" name=\"RepresentanteNome\" size=\"30\" maxlength=\"60\" class=\"textonormal\">";
													echo "			</td>";
													
													echo "			<td class=\"textonormal\" style=\"font-weight: bold; align: left;\">";
													echo "				Repr. R.G.: ";
													echo "			</td>";	

													echo "			<td>";
													echo "				<input type=\"text\" name=\"RepresentanteRG\" size=\"10\" maxlength=\"15\" class=\"textonormal\">";
													echo "			</td>";

													echo "			<td class=\"textonormal\" style=\"font-weight: bold; align: left;\">";
													echo "				Órgão Emissor/UF: ";
													echo "			</td>";	

													echo "			<td>";
													echo "				<input type=\"text\" name=\"RepresentanteOrgaoUF\" size=\"12\" maxlength=\"20\" class=\"textonormal\">";
													echo "			</td>";													
													
													echo "			<td width=\"15%\" align=\"right\" style=\"padding-right: 20px;\">";
													echo "				<input type=\"submit\" value=\"Incluir Fornecedor\" style=\"position: relative;\" class=\"botao\" onclick=\"javascript:enviar('IncluirFornecedor');\">\n";													
													echo "			</td>";
													
													echo "		</tr>";
													echo "	</table>";
													echo "</div>";
													
									    		$db->disconnect();
											}
											else
											{
													echo "	<tr>\n";
													echo "		<td valign=\"top\" colspan=\"4\" class=\"textonormal\" bgcolor=\"FFFFFF\">\n";
													echo "		Pesquisa sem Ocorrências.\n";
													echo "		</td>\n";
													echo "	</tr>\n";
													echo "</table>";
											}
									}
							}
							?>
						
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
